<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 */

namespace bigfathom_core;

/**
 * Collection of some project handling helper methods
 *
 * @author Frank Font
 */
class ProjectHelper
{
    const UNIT_TEST_ROOT_NAME = '__UNIT_TESTING';
    
    
    private $m_default_test_root_name = NULL;
    private $m_special_suffix = NULL;
    
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;

    public function getUnitTestRootName()
    {
        return $this->m_default_test_root_name;
    }
    
    public function __construct($oContext, $oMapHelper, $oUAH, $oWriteHelper
            , $default_special_suffix=NULL
            , $default_test_root_name=NULL)
    {
        if(empty($default_test_root_name))
        {
            $default_test_root_name = self::UNIT_TEST_ROOT_NAME;
        }
        $this->m_default_test_root_name = $default_test_root_name;
        if(empty($default_special_suffix))
        {
            $default_special_suffix = '_GENERIC';
        }
        $this->m_special_suffix = $default_special_suffix;
        
        if(strlen($this->m_default_test_root_name)<10)
        {
            throw new \Exception("Match template root name is too short! test_project_root_name=$this->m_default_test_root_name");
        }
        
        module_load_include('php','bigfathom_core','core/DatabaseNamesHelper');
        
        $this->m_oContext = $oContext;
        $this->m_oMapHelper = $oMapHelper;
        $this->m_oUAH = $oUAH;
        $this->m_oWriteHelper = $oWriteHelper;
    }

    public function createTestProject($owner_personid, $start_dt=NULL, $end_dt=NULL
            , $planned_start_dt_locked_yn=0, $planned_end_dt_locked_yn=0)
    {
        try
        {
            
            $realprojcontexts = $this->m_oMapHelper->getProjectContextsByID();
            $realpcids = array_keys($realprojcontexts);
            $real_project_contextid = $realpcids[0]; //Just take the first one
         
            $myvalues = [];
            $myvalues['owner_personid'] = $owner_personid;
            $myvalues['root_workitem_nm'] = "{$this->m_default_test_root_name}{$this->m_special_suffix}";
            $myvalues['importance'] = 11;
            $myvalues['active_yn'] = 1;
            $myvalues['purpose_tx'] = 'Dummy project for application unit testing';
            $myvalues['status_cd'] = 'WNS';
            $myvalues['source_type'] = 'local';
            $myvalues['surrogate_yn'] = 0;
            $myvalues['project_contextid'] = $real_project_contextid;
            $myvalues['mission_tx'] = 'Unit testing the application';
            $myvalues['allow_status_publish_yn'] = 0;
            $myvalues['allow_refresh_from_remote_yn'] = 0;
            
            if(!empty($start_dt))
            {
                $myvalues['planned_start_dt'] = $start_dt;
                $myvalues['planned_start_dt_locked_yn'] = $planned_start_dt_locked_yn;
            }
            if(!empty($end_dt))
            {
                $myvalues['planned_end_dt'] = $end_dt;
                $myvalues['planned_end_dt_locked_yn'] = $planned_end_dt_locked_yn;
            }
            
            $resultbundle = $this->m_oWriteHelper->createNewProject($myvalues);
            if(empty($resultbundle['newid']))
            {
                throw new \Exception('Missing newid:' . print_r($resultbundle,TRUE));
            }
            if(empty($resultbundle['root_goalid']))
            {
                throw new \Exception('Missing root_goalid]:' . print_r($resultbundle,TRUE));
            }
            if(empty($resultbundle['message']))
            {
                throw new \Exception('Missing message]:' . print_r($resultbundle,TRUE));
            }
            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function addWorkitemsToOurTestProject($projectid, $workitem_basetype='G', $myvalues=NULL, $dep_wid=NULL)
    {
        try
        {
            $updated_dt = date("Y-m-d H:i", time());
            
            if(empty($myvalues))
            {
                $myvalues = [];
            }
            
            $unit_test_root_name = $this->getUnitTestRootName();
            
            $myvalues['workitem_basetype'] = $workitem_basetype;
            
            if(!isset($myvalues['workitem_nm']))
            {
                $myvalues['workitem_nm'] = $unit_test_root_name . "_" . $workitem_basetype;
            }
            if(!isset($myvalues['importance']))
            {
                $myvalues['importance'] = 11;
            }
            if(!isset($myvalues['owner_personid']))
            {
                $myvalues['owner_personid'] = 1;
            }
            if(!isset($myvalues['purpose_tx']))
            {
                $myvalues['purpose_tx'] = 'Automated testing';
            }

            $resultbundle = $this->m_oWriteHelper->createWorkitem($projectid, $myvalues);
            if(empty($resultbundle['workitemid']))
            {
                throw new \Exception("Missing workitemid for basetype=$workitem_basetype:" . print_r($resultbundle,TRUE));
            }
            if(empty($resultbundle['message']))
            {
                throw new \Exception("Missing message for basetype=$workitem_basetype:" . print_r($resultbundle,TRUE));
            }
            $wid = $resultbundle['workitemid'];
            
            if(!empty($dep_wid))
            {
                //Now add the link
                $key = array(
                            'depwiid' => $dep_wid,
                            'antwiid' => $wid);
                $fields = array(
                            'created_by_personid' => $myvalues['owner_personid'],
                            'created_dt' => $updated_dt);                    
                db_merge(\bigfathom\DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->key($key)
                      ->fields($fields)
                      ->execute();
            }
            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function deleteOneProject($projectid)
    {
        try
        {
            $this->m_oWriteHelper->deleteProject($projectid);
            $all_projectids = $this->m_oMapHelper->getProjectsIDs(FALSE);
            foreach($all_projectids as $existing_projectid)
            {
                if($existing_projectid == $projectid)
                {
                    //Failed to delete this one!
                    throw new \Exception("Failed to delete project#$projectid");
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectNameTemplate($ignore_special_suffix=FALSE)
    {
        if($ignore_special_suffix)
        {
            $test_project_name_template = "{$this->m_default_test_root_name}";
        } else {
            $test_project_name_template = "{$this->m_default_test_root_name}{$this->m_special_suffix}";
        }
        return $test_project_name_template;
    }
    
    public function deleteAllTestProjects($ignore_special_suffix=FALSE)
    {
        try
        {
            $ids2delete = [];
            $all_projects = $this->m_oMapHelper->getProjectsByID(NULL, FALSE);
            $test_project_name_template = $this->getProjectNameTemplate($ignore_special_suffix);
            
            if(strlen($test_project_name_template)<10)
            {
                throw new \Exception("Match template is too short! test_project_name_template=$test_project_name_template");
            }
            foreach($all_projects as $projectid=>$detail)
            {
                $root_workitem_nm = $detail['root_workitem_nm'];
                $relevant_namepart = substr($root_workitem_nm, 0, strlen($test_project_name_template));
                if($relevant_namepart == $test_project_name_template)
                {
                    $ids2delete[$projectid] = $projectid;
                    $this->m_oWriteHelper->deleteProject($projectid);
                }
            }
            $all_projectids = $this->m_oMapHelper->getProjectsIDs(FALSE);
            $existing_map = [];
            foreach($all_projectids as $existing_projectid)
            {
                $existing_map[$existing_projectid] = $existing_projectid;
            }
            foreach($ids2delete as $checkid)
            {
                if(isset($existing_map[$checkid]))
                {
                    //Failed to delete this one!
                    throw new \Exception("Failed to delete unit test project#$checkid");
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
