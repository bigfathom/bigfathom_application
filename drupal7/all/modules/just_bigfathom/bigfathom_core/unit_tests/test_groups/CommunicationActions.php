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
 * Collection of some communication action tests for the core module
 *
 * @author Frank Font
 */
class CommunicationActions extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    private $m_oWriteHelper = NULL;
    
    function getVersionNumber()
    {
        return '20171013.1';
    }
    
    function getNiceName()
    {
        $classname = $this->getClassName(FALSE);
        return $this->shortcutGetNiceName($classname);
    }
    
    function getClassName()
    {
        $fullname = get_class();
        return $this->shortcutGetClassNameWithoutNamespace($fullname);
    }
    
    function runAllTests()
    {
        $all_methods = $this->getAllTestMethods();
        return $this->shortcutRunAllTests($all_methods);
    }

    function setUp()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            $loaded_context = module_load_include('php','bigfathom_core','core/Context');
            if(!$loaded_context)
            {
                throw new \Exception('Failed to load the Context class');
            }
            $this->m_oContext = \bigfathom\Context::getInstance();
            $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the MapHelper class');
            }
            $this->m_oMapHelper = new \bigfathom\MapHelper();

            $loaded_wh = module_load_include('php','bigfathom_core','core/WriteHelper');
            if(!$loaded_wh)
            {
                throw new \Exception('Failed to load the WriteHelper class');
            }
            $this->m_oWriteHelper = new \bigfathom\WriteHelper();
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            
            $loaded_helper_projects = module_load_include('php','bigfathom_core','unit_tests/utilities/ProjectHelper');
            if(!$loaded_helper_projects)
            {
                throw new \Exception('Failed to load the ProjectHelper class');
            }
            $this->m_oProjectHelper = new \bigfathom_core\ProjectHelper($this->m_oContext, $this->m_oMapHelper, $this->m_oUAH, $this->m_oWriteHelper);
            
            $this->createTestProjectWithOurContent();
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    function tearDown()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            //Perform the TEARDOWN here
            $this->m_oProjectHelper->deleteAllTestProjects();
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    function isExclusiveRequired()
    {
        return FALSE;
    }

    function getAllTestMethods()
    {
        return $this->shortcutGetAllTestMethods($this);
    }


    private function createTestProjectWithOurContent()
    {
        try
        {
            $now_dt = date("Y-m-d H:i", time());
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($now_dt, 1);
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($now_dt, 15);
            $this->m_ourtestprojectbundle = $this->m_oProjectHelper->createTestProject(1);
            $projectid = $this->m_ourtestprojectbundle['newid'];
            
            $this->m_ourtestprojectbundle['workitems'] = [];
            $workitem_basetype_ar = array('G','T');
            for($i=0;$i<10;$i++)
            {
                $workitem_basetype = $workitem_basetype_ar[$i % 2];
                $wirb = $this->m_oProjectHelper->addWorkitemsToOurTestProject($projectid, $workitem_basetype);
                $wid = $wirb['workitemid'];
                $this->m_ourtestprojectbundle['workitems'][$wid] = $wirb;
            }
            
            $sprint_values = array(
                'owner_projectid' => $projectid,
                'story_tx' => "__UNIT_TESTING",
                'start_dt' => $start_dt,
                'end_dt' => $end_dt,
                'owner_personid' => 1,
                'status_cd' => 'RFW',
                'membership_locked_yn' => 0,
                'active_yn' => 1,
              );
            $sirb = $this->m_oWriteHelper->createNewSprint($projectid, $sprint_values);
            $sprintid = $sirb['newid'];
            $this->m_ourtestprojectbundle['sprints'][$sprintid] = $sirb;
            
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    
    function testCreateWorkitemThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $created_count = 0;
            foreach($this->m_ourtestprojectbundle['workitems'] as $wid=>$wirb)
            {
                $myvalues = array(
                    'workitemid' => $wid,
                    'status_cd_at_time_of_com' => 'WNS',
                    'title_tx' => "__UNIT TESTING TITLE4WID#$wid",
                    'body_tx' => "__UNIT TESTING BODY TEXT",
                    'owner_personid' => 1,
                    'active_yn' => 1,
                    );
                $rb = $this->m_oWriteHelper->createWorkitemCommunication($myvalues);
                $comid = $rb['comid'];
                if(empty($comid))
                {
                    throw new \Exception("Missing comid in " . print_r($rb,TRUE));
                }
                if(!isset($this->m_ourtestprojectbundle['workitems'][$wid]['comids']))
                {
                    $this->m_ourtestprojectbundle['workitems'][$wid]['comids'] = [];
                }
                $this->m_ourtestprojectbundle['workitems'][$wid]['comids'][$comid] = $comid;
                $created_count++;
            }
            $insight = "Created $created_count communications";
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    function testCreateProjectThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $myvalues = array(
                'projectid' => $projectid,
                'status_cd_at_time_of_com' => 'WNS',
                'title_tx' => "__UNIT TESTING TITLE4PROJECT#$projectid",
                'body_tx' => "__UNIT TESTING BODY TEXT",
                'owner_personid' => 1,
                'active_yn' => 1,
                );
            $rb = $this->m_oWriteHelper->createProjectCommunication($myvalues);
            $comid = $rb['comid'];
            if(empty($comid))
            {
                throw new \Exception("Missing comid in " . print_r($rb,TRUE));
            }
            if(!isset($this->m_ourtestprojectbundle['projects'][$projectid]['comids']))
            {
                $this->m_ourtestprojectbundle['projects'][$projectid]['comids'] = [];
            }
            $this->m_ourtestprojectbundle['projects'][$projectid]['comids'][$comid] = $comid;
            $insight = "Created 1 communications";
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testCreateSprintThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $created_count = 0;
            foreach($this->m_ourtestprojectbundle['sprints'] as $sprintid=>$sirb)
            {
                $myvalues = array(
                    'sprintid' => $sprintid,
                    'status_cd_at_time_of_com' => 'WNS',
                    'title_tx' => "__UNIT TESTING TITLE4SPRINT#$sprintid",
                    'body_tx' => "__UNIT TESTING BODY TEXT",
                    'owner_personid' => 1,
                    'active_yn' => 1,
                    );
                $rb = $this->m_oWriteHelper->createSprintCommunication($myvalues);
                $comid = $rb['comid'];
                if(empty($comid))
                {
                    throw new \Exception("Missing comid in " . print_r($rb,TRUE));
                }
                $sirb['comids'] = [];
                $sirb['comids'][$comid] = $comid;
                if(!isset($this->m_ourtestprojectbundle['sprints'][$sprintid]['comids']))
                {
                    $this->m_ourtestprojectbundle['sprints'][$sprintid]['comids'] = [];
                }
                $this->m_ourtestprojectbundle['sprints'][$sprintid]['comids'][$comid] = $comid;
                $created_count++;
            }
            $insight = "Created $created_count communications";

        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testUpdateWorkitemThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $updated_count = 0;
            foreach($this->m_ourtestprojectbundle['workitems'] as $wid=>$wirb)
            {
                $comid_ar = $wirb['comids'];
                foreach($comid_ar as $comid)
                {
                    $myvalues = array(
                        'status_cd_at_time_of_com' => 'WNS',
                        'title_tx' => "__UNIT TEST TITLE4WORKITEM#$wid",
                        'body_tx' => "__UNIT TESTING UPDATED BODY TEXT",
                        'active_yn' => 1,
                        );
                    $this->m_oWriteHelper->updateWorkitemCommunication($comid, $myvalues);
                    $updated_count++;
                }
            }
            if($updated_count == 0)
            {
                throw new \Exception("Found $updated_count communications!");
            }
            $insight = "Updated $updated_count communications";
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testUpdateSprintThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $updated_count = 0;
            foreach($this->m_ourtestprojectbundle['sprints'] as $sprintid=>$sirb)
            {
                $comid_ar = $sirb['comids'];
                foreach($comid_ar as $comid)
                {
                    $myvalues = array(
                        'status_cd_at_time_of_com' => 'WNS',
                        'title_tx' => "__UNIT TESTING UPDATED TITLE4SPRINT#$sprintid",
                        'body_tx' => "__UNIT TESTING UPDATED BODY TEXT",
                        'active_yn' => 1,
                        );
                    $this->m_oWriteHelper->updateSprintCommunication($comid, $myvalues);
                    $updated_count++;
                }
            }
            if($updated_count == 0)
            {
                throw new \Exception("Found $updated_count communications!");
            }
            $insight = "Updated $updated_count communications";
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testUpdateProjectThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $updated_count = 0;
            foreach($this->m_ourtestprojectbundle['projects'] as $projectid=>$sirb)
            {
                $comid_ar = $sirb['comids'];
                foreach($comid_ar as $comid)
                {
                    $myvalues = array(
                        'status_cd_at_time_of_com' => 'WNS',
                        'title_tx' => "__UNIT TESTING UPDATED TITLE4SPRINT#$projectid",
                        'body_tx' => "__UNIT TESTING UPDATED BODY TEXT",
                        'active_yn' => 1,
                        );
                    $this->m_oWriteHelper->updateSprintCommunication($comid, $myvalues);
                    $updated_count++;
                }
            }
            if($updated_count == 0)
            {
                throw new \Exception("Found $updated_count communications!");
            }
            $insight = "Updated $updated_count communications";
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
}
