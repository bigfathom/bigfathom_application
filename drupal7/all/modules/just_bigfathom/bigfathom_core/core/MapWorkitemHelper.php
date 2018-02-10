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
 *
 */

namespace bigfathom;

require_once 'DatabaseNamesHelper.php';
require_once 'MapCommunicationHelper.php';


/**
 * This class tells us about fundamental mappings.
 * Try to keep this file small because it is loaded every time.
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapWorkitemHelper extends \bigfathom\MapCommunicationHelper
{

    /**
     * Return array of workitems from the list taht are not in the project
     */
    public function getWorkitemsNotInProject($projectid, $wid_ar)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $bad_wids = [];
            if(count($wid_ar) > 0)
            {
                $wmap = [];
                $clean_wid_ar = [];
                foreach($wid_ar as $wid)
                {
                    $trimmed = trim($wid);
                    if($trimmed > "" && is_numeric($trimmed))
                    {
                        $clean_wid_ar[] = $trimmed;
                        $key = "$trimmed";
                        $wmap[$key] = 0;
                    }
                }
                $widlist =  implode(",",$clean_wid_ar);
                $sSQL = "SELECT id as workitemid"
                        . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                        . " WHERE owner_projectid = $projectid and id IN ($widlist)";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $wid = $record['workitemid'];
                    $key = "$wid";
                    $wmap[$key] = 1;
                }
                foreach($wmap as $wid=>$flag)
                {
                    if($flag == 0)
                    {
                        $bad_wids[] = $wid;
                    }
                }
            }
            return $bad_wids;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getWorkitemStatusByCode()
    {
        try
        {
            $map_status = [];

            $sSQL = "select code, title_tx, "
                    . " description_tx, workstarted_yn, terminal_yn, happy_yn, sort_position, "
                    . " ot_scf, created_dt  "
                    . " from " . DatabaseNamesHelper::$m_workitem_status_tablename
                    . " where active_yn=1 "
                    . " order by code";
            $tagResult = db_query($sSQL);
            while($record = $tagResult->fetchAssoc()) 
            {
                $id = $record['code'];
                $map_status[$id][] = $record;
            }
            
            return $map_status;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getAutoFillRelevantWorkitemValueMap($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $map = [];

            $sSQL = "SELECT id, owner_personid, limit_branch_effort_hours_cd, branch_effort_hours_est, planned_start_dt_locked_yn, planned_end_dt_locked_yn, planned_start_dt, planned_end_dt, actual_start_dt, actual_end_dt, remaining_effort_hours"
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename
                    . " WHERE owner_projectid=$projectid and active_yn=1";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $map[$id] = $record;
            }
            
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a bare map of workitem detail for one sprint
     * The w2s_scf value tells us the confidence in the sprint.
     */
    public function getOneBareSprintWorkitemRecord($sprintid, $workitemid)
    {
        try
        {
            $sSQL = "SELECT"
                    . " w.*, w2s.sprintid, w2s.ot_scf as w2s_scf"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename." w2s on w2s.workitemid=w.id"
                    . " WHERE w2s.sprintid=$sprintid AND w.id=$workitemid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a bare map of use case detail for one row
     */
    public function getOneBareUsecaseRecord($usecaseid)
    {
        try
        {
            if(!is_numeric($usecaseid))
            {
                throw new \Exception("Expected an integer id instead got value='$usecaseid'");
            }
            $myvalues = db_select(DatabaseNamesHelper::$m_usecase_tablename, 'n')
              ->fields('n')
              ->condition('id', $usecaseid, '=')
              ->execute()
              ->fetchAssoc();
            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bare map of test case detail for one row
     */
    public function getOneBareTestcaseRecord($testcaseid)
    {
        try
        {
            if(!is_numeric($testcaseid))
            {
                throw new \Exception("Expected an integer id instead got value='$testcaseid'");
            }
            $myvalues = db_select(DatabaseNamesHelper::$m_testcase_tablename, 'n')
              ->fields('n')
              ->condition('id', $testcaseid, '=')
              ->execute()
              ->fetchAssoc();
            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bare map of project baseline detail for one row
     */
    public function getOneBareProjectBaselineRecord($project_baselineid)
    {
        try
        {
            if(!is_numeric($project_baselineid))
            {
                throw new \Exception("Expected an integer id instead got value='$project_baselineid'");
            }
            $myvalues = db_select(DatabaseNamesHelper::$m_project_baseline_tablename, 'n')
              ->fields('n')
              ->condition('id', $project_baselineid, '=')
              ->execute()
              ->fetchAssoc();
            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bare map of workitem detail for one row
     */
    public function getOneBareWorkitemRecord($workitemid)
    {
        if(!is_numeric($workitemid))
        {
            //$stacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);
            //DebugHelper::showMyValues($stacktrace);
            $errmsg = "Expected an integer id instead got value='$workitemid'";
            DebugHelper::showStackTrace($errmsg);
            throw new \Exception($errmsg);
        }
        $filter = array("w.id"=>$workitemid);
        $themap = $this->getBareWorkitemsByID(NULL, $filter, NULL, NULL);
        return $themap[$workitemid];
    }
    
    /**
     * Return a bare map of workitem detail for one row
     */
    public function getOneBareTWRecord($template_workitemid)
    {
        if(!is_numeric($template_workitemid))
        {
            //$stacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 5);
            //DebugHelper::showMyValues($stacktrace);
            throw new \Exception("Expected an integer id instead got value='$template_workitemid'");
        }
        $filter = array("w.id"=>$template_workitemid);
        $themap = $this->getBareTWsByID(NULL, $filter);
        return $themap[$template_workitemid];
    }
    
    public function getWorkitemIDsForBlankBranchEffort($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $from = DatabaseNamesHelper::$m_workitem_tablename." w";
            $themap = [];
            $sSQL = "SELECT"
                    . " w.id, w.status_cd"
                    . " FROM $from";
            $sSQL .= " WHERE w.owner_projectid=$projectid";
            $sSQL .= " ORDER BY w.id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id] = $record;
            }
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectID2RootWorkitemIDMap($projectid_ar)
    {
        try
        {
            if(empty($projectid_ar))
            {
                throw new \Exception("Missing required projectid array!");
            }
            if(!is_array($projectid_ar))
            {
                throw new \Exception("Expected a projectid array intsead of '$projectid_ar'!");
            }
            
            $sINTXT = " in (" . implode(',', $projectid_ar) . ")";
            
            $from = DatabaseNamesHelper::$m_project_tablename." p";
            $themap = [];
            $sSQL = "SELECT"
                    . " id, root_goalid"
                    . " FROM $from";
            $sSQL .= " WHERE id $sINTXT";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $root_goalid = $record['root_goalid'];
                $themap[$id] = $root_goalid;
            }
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bare map of workitem detail for many rows
     */
    public function getBareWorkitemsByID($projectid=NULL, $filter_ar=NULL, $active_yn=1, $named_filter_ar=NULL)
    {
        try
        {
            $key_fieldname='id';
            $aMORE_JOINS = [];
            $aWHERE = array();
            if(!empty($projectid))
            {
                $aWHERE[] = "w.owner_projectid = $projectid";
            }
            if($named_filter_ar !== NULL)
            {
                foreach($named_filter_ar as $name=>$value)
                {
                    $cleanname = strtoupper($name);
                    if($cleanname == 'ONLY_ORPHANS')
                    {
                        if(!array_key_exists('m', $aMORE_JOINS))
                        {
                            $aMORE_JOINS['m'] = DatabaseNamesHelper::$m_map_wi2wi_tablename." m on w.id=m.antwiid";
                        }
                        $filter_ar['m.antwiid '] = NULL;
                        $filter_ar['prj.id'] = NULL;    //Excludes the root node
                    } else
                    if($cleanname == 'MANY_PROJECTS')
                    {
                        $filter_ar['prj.id'] = $value;
                    }
                }
            }
            if($filter_ar !== NULL)
            {
                if(!is_array($filter_ar))
                {
                    throw new \Exception("The filter must be an array!! Instead we have filter=$filter_ar with detail=" . print_r($filter_ar,TRUE));
                }
                foreach($filter_ar as $fieldname=>$valuemarkup)
                {
                    if($valuemarkup === NULL || $valuemarkup === 'NULL')
                    {
                        $aWHERE[] = "$fieldname IS NULL";    
                    } else if(is_array($valuemarkup)) {
                        if(count($valuemarkup) > 0)
                        {
                            $aWHERE[] = "$fieldname IN (" . implode(",",$valuemarkup) . ")";
                        }
                    } else {
                        $aWHERE[] = "$fieldname = $valuemarkup";    
                    }
                }
            }
            if($active_yn !== NULL)
            {
                $aWHERE[] = "w.active_yn = $active_yn";
            }
            $from = DatabaseNamesHelper::$m_workitem_tablename." w"
                . " LEFT JOIN ".DatabaseNamesHelper::$m_project_tablename." prj on w.id=prj.root_goalid and prj.active_yn=1"
                . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." s on w.status_cd=s.code";
            foreach($aMORE_JOINS as $key=>$joininfo)
            {
                $from .= " LEFT JOIN ".$joininfo;
            }
            $themap = array();
            $sSQL = "SELECT"
                    . " w.id, w.workitem_nm, w.workitem_basetype, w.owner_projectid, "
                    . " w.purpose_tx, w.owner_personid, w.active_yn, "
                    . " w.externally_billable_yn, w.client_deliverable_yn, w.equipmentid, w.external_resourceid, w.planned_fte_count, "
                    . " w.branch_effort_hours_est, w.branch_effort_hours_est_p, "
                    . " w.effort_hours_est, w.effort_hours_est_p, w.remaining_effort_hours, "
                    . " w.effort_hours_worked_est, w.effort_hours_worked_act, "
                    . " w.importance, w.status_cd, s.title_tx as status_title_tx, w.status_set_dt, w.ot_scf, "
                    . " w.planned_start_dt, w.actual_start_dt, w.planned_end_dt, w.actual_end_dt, "
                    . " w.effort_hours_est_locked_yn, w.planned_start_dt_locked_yn, w.planned_end_dt_locked_yn, "
                    . " w.tester_personid, "
                    . " w.updated_dt, w.created_dt, "
                    . " prj.id as root_of_projectid, "
                    . " w.chargecode, w.limit_branch_effort_hours_cd, w.ignore_branch_cost_yn, "
                    . " w.self_allow_dep_overlap_hours, w.self_allow_dep_overlap_pct, "
                    . " w.ant_sequence_allow_overlap_hours, w.ant_sequence_allow_overlap_pct"
                    . " FROM $from";
            $sWHERE = implode(" and ", $aWHERE);
            $sSQL .= " WHERE $sWHERE";
            $sSQL .= " ORDER BY w.{$key_fieldname}";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $record['nativeid'] = $id;
                if($record['workitem_basetype'] === 'G')
                {
                    $record['typeletter'] = 'G';
                    $record['type'] = 'goal';
                } else {
                    if(!empty($record['equipmentid']))
                    {
                        $record['typeletter'] = 'Q';    
                        $record['type'] = 'equjb';
                    } else {
                        if(!empty($record['external_resourceid']))
                        {
                            $record['typeletter'] = 'X';    
                            $record['type'] = 'xrcjb';
                        } else {
                            $record['typeletter'] = 'T'; 
                            $record['type'] = 'task';
                        }
                    }
                }
                $themap[$id] = $record;
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bare map of template workitem detail for many rows
     */
    public function getBareTWsByID($template_projectid=NULL, $filter_ar=NULL, $named_filter_ar=NULL)
    {
        try
        {
            $key_fieldname='id';
            $aMORE_JOINS = [];
            $aWHERE = array();
            if(!empty($template_projectid))
            {
                if(is_array($template_projectid))
                {
                    $in = implode(',', $template_projectid);
                    $aWHERE[] = "w.owner_template_projectid in ($in)";
                } else {
                    $aWHERE[] = "w.owner_template_projectid = $template_projectid";
                }
            }
            if($named_filter_ar !== NULL)
            {
                foreach($named_filter_ar as $name=>$value)
                {
                    $cleanname = strtoupper($name);
                    if($cleanname == 'ONLY_ORPHANS')
                    {
                        if(!array_key_exists('m', $aMORE_JOINS))
                        {
                            $aMORE_JOINS['m'] = DatabaseNamesHelper::$m_map_tw2tw_tablename." m on w.id=m.antwiid";
                        }
                        $filter_ar['m.antwiid '] = NULL;
                        $filter_ar['prj.id'] = NULL;    //Excludes the root node
                    } else
                    if($cleanname == 'MANY_PROJECTS')
                    {
                        $filter_ar['prj.id'] = $value;
                    }
                }
            }
            if($filter_ar !== NULL)
            {
                if(!is_array($filter_ar))
                {
                    throw new \Exception("The filter must be an array!! Instead we have filter=$filter_ar with detail=" . print_r($filter_ar,TRUE));
                }
                foreach($filter_ar as $fieldname=>$valuemarkup)
                {
                    if($valuemarkup === NULL || $valuemarkup === 'NULL')
                    {
                        $aWHERE[] = "$fieldname IS NULL";    
                    } else if(is_array($valuemarkup)) {
                        if(count($valuemarkup) > 0)
                        {
                            $aWHERE[] = "$fieldname IN (" . implode(",",$valuemarkup) . ")";
                        }
                    } else {
                        $aWHERE[] = "$fieldname = $valuemarkup";    
                    }
                }
            }

//DatabaseNamesHelper::$m_template_project_library_tablename            
            $from = DatabaseNamesHelper::$m_template_workitem_tablename." w"
                . " LEFT JOIN ".DatabaseNamesHelper::$m_template_project_library_tablename." prj on w.id=prj.root_template_workitemid"
                . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." s on w.status_cd=s.code";
            foreach($aMORE_JOINS as $key=>$joininfo)
            {
                $from .= " LEFT JOIN ".$joininfo;
            }
            $themap = array();
            $sSQL = "SELECT"
                    . " w.id, w.workitem_nm, w.workitem_basetype, w.owner_template_projectid, "
                    . " w.purpose_tx, w.owner_personid, "
                    . " w.externally_billable_yn, w.client_deliverable_yn, w.equipmentid, w.external_resourceid, w.planned_fte_count, "
                    . " w.branch_effort_hours_est, w.branch_effort_hours_est_p, "
                    . " w.effort_hours_est, w.effort_hours_est_p, w.remaining_effort_hours, "
                    . " w.importance, w.status_cd, s.title_tx as status_title_tx, w.ot_scf, "
                    . " w.effort_hours_est_locked_yn, "
                    . " w.tester_personid, "
                    . " w.updated_dt, w.created_dt, "
                    . " prj.id as root_of_tpid, "
                    . " w.chargecode, w.limit_branch_effort_hours_cd, w.ignore_branch_cost_yn "
                    . " FROM $from";
            $sWHERE = implode(" and ", $aWHERE);
            $sSQL .= " WHERE $sWHERE";
            $sSQL .= " ORDER BY w.{$key_fieldname}";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $record['nativeid'] = $id;
                if($record['workitem_basetype'] === 'G')
                {
                    $record['typeletter'] = 'G';
                    $record['type'] = 'goal';
                } else {
                    if(!empty($record['equipmentid']))
                    {
                        $record['typeletter'] = 'Q';    
                        $record['type'] = 'equjb';
                    } else {
                        if(!empty($record['external_resourceid']))
                        {
                            $record['typeletter'] = 'X';    
                            $record['type'] = 'xrcjb';
                        } else {
                            $record['typeletter'] = 'T'; 
                            $record['type'] = 'task';
                        }
                    }
                }
                $themap[$id] = $record;
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getUsecaseWorkitemMap($inTxt, $active_yn=1)
    {
        try
        {
            //There can be more than one member
            $member_workitems_list = [];
            $member_workitems_sql = "SELECT workitemid, usecaseid"
                    . " FROM ".DatabaseNamesHelper::$m_map_workitem2usecase_tablename
                    . " WHERE usecaseid $inTxt order by usecaseid";
            $member_workitems_result = db_query($member_workitems_sql);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $usecaseid = $record['usecaseid'];
                $workitemid = $record['workitemid'];
                $member_workitems_list[$usecaseid][] = $workitemid; 
            }            
            return $member_workitems_list;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getUsecaseTagMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_tags = [];

            $tagSQL = "select tag_tx, usecaseid from " 
                    . DatabaseNamesHelper::$m_map_tag2usecase_tablename
                    . " where usecaseid $inTxt order by usecaseid";
            $tagResult = db_query($tagSQL);
            while($record = $tagResult->fetchAssoc()) 
            {
                $id = $record['usecaseid'];
                $map_tags[$id][] = $record['tag_tx'];
            }
            
            return $map_tags;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getUsecaseDelegateMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_delegate_owner = [];

            $delegateSql = "select personid, usecaseid from " 
                    . DatabaseNamesHelper::$m_map_delegate_usecaseowner_tablename
                    . " where usecaseid $inTxt order by usecaseid";
            $delegateResult = db_query($delegateSql);
            while($record = $delegateResult->fetchAssoc()) 
            {
                $id = $record['usecaseid'];
                if(!empty($id))
                {
                    $map_delegate_owner[$id][] = $record['personid'];
                }
            }            
            
            return $map_delegate_owner;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getTestcaseWorkitemMap($inTxt, $active_yn=1)
    {
        try
        {
            //There can be more than one member
            $member_workitems_list = [];
            $member_workitems_sql = "SELECT workitemid, testcaseid"
                    . " FROM ".DatabaseNamesHelper::$m_map_workitem2testcase_tablename
                    . " WHERE testcaseid $inTxt order by testcaseid";
            $member_workitems_result = db_query($member_workitems_sql);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $testcaseid = $record['testcaseid'];
                $workitemid = $record['workitemid'];
                $member_workitems_list[$testcaseid][] = $workitemid; 
            }            
            return $member_workitems_list;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getTestcaseStepMap($inTxt)
    {
        try
        {
            //There can be more than one member
            $member_workitems_list = [];
            $member_workitems_sql = "SELECT id, testcaseid, step_num, instruction_tx, expectation_tx, status_cd, executed_dt, updated_dt, created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_testcasestep_tablename
                    . " WHERE testcaseid $inTxt order by testcaseid,step_num";
            $member_workitems_result = db_query($member_workitems_sql);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $testcaseid = $record['testcaseid'];
                $member_workitems_list[$testcaseid][] = $record;
            }            
            return $member_workitems_list;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getTestcaseTagMap($inTxt)
    {
        try
        {
            
            $map_tags = [];

            $tagSQL = "select tag_tx, testcaseid from " 
                    . DatabaseNamesHelper::$m_map_tag2testcase_tablename
                    . " where testcaseid $inTxt order by testcaseid";
            $tagResult = db_query($tagSQL);
            while($record = $tagResult->fetchAssoc()) 
            {
                $id = $record['testcaseid'];
                $tag = trim($record['tag_tx']);
                if(!empty($tag))
                {
                    $map_tags[$id][] = $tag;
                }
            }
            
            return $map_tags;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getTestcaseDelegateMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_delegate_owner = [];

            $delegateSql = "select personid, testcaseid from " 
                    . DatabaseNamesHelper::$m_map_delegate_testcaseowner_tablename
                    . " where testcaseid $inTxt order by testcaseid";
            $delegateResult = db_query($delegateSql);
            while($record = $delegateResult->fetchAssoc()) 
            {
                $id = $record['testcaseid'];
                if(!empty($id))
                {
                    $map_delegate_owner[$id][] = $record['personid'];
                }
            }            
            
            return $map_delegate_owner;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getWorkitemTagMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_tags = [];

            $tagSQL = "select tag_tx, workitemid from " 
                    . DatabaseNamesHelper::$m_map_tag2workitem_tablename
                    . " where workitemid $inTxt order by workitemid";
            $tagResult = db_query($tagSQL);
            while($record = $tagResult->fetchAssoc()) 
            {
                $id = $record['workitemid'];
                $tag = trim($record['tag_tx']);
                if(!empty($tag))
                {
                    $map_tags[$id][] = $tag;
                }
            }
            
            return $map_tags;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getTWTagMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_tags = [];

            $tagSQL = "select tag_tx, template_workitemid from " 
                    . DatabaseNamesHelper::$m_map_tag2tw_tablename
                    . " where template_workitemid $inTxt order by template_workitemid";
            $tagResult = db_query($tagSQL);
            while($record = $tagResult->fetchAssoc()) 
            {
                $id = $record['template_workitemid'];
                $tag = trim($record['tag_tx']);
                if(!empty($tag))
                {
                    $map_tags[$id][] = $tag;
                }
            }
            
            return $map_tags;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return all the owners of the workitems in the array
     */
    public function getWorkitemOwners($wid_ar)
    {
        try
        {
            $map = [];
            if(count($wid_ar) > 0)
            {
                $inTxt = 'IN ('.implode(',',$wid_ar) . ')';
                $delegate_owners = $this->getWorkitemDelegateMap($inTxt);
                foreach($delegate_owners as $wid=>$people)
                {
                    $map[$wid] = $people;
                }
                $sql = "select id, owner_personid"
                        . " from " . DatabaseNamesHelper::$m_workitem_tablename
                        . " where id $inTxt order by id";
                $delegateResult = db_query($sql);
                while($record = $delegateResult->fetchAssoc()) 
                {
                    $wid = $record['id'];
                    if(!empty($wid))
                    {
                        $map[$wid][] = $record['owner_personid'];
                    }
                }            
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getWorkitemDelegateMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map_delegate_owner = [];

            $delegateSql = "select personid, workitemid from " 
                    . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename
                    . " where workitemid $inTxt order by workitemid";
            $delegateResult = db_query($delegateSql);
            while($record = $delegateResult->fetchAssoc()) 
            {
                $id = $record['workitemid'];
                if(!empty($id))
                {
                    $map_delegate_owner[$id][] = $record['personid'];
                }
            }            
            
            return $map_delegate_owner;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getWorkitemRoleMap($inTxt, $richmap=TRUE)
    {
        try
        {
            
            $map = [];

            if(!empty($inTxt) && strlen(trim($inTxt)) > 0)
            {
                if(!$richmap)
                {
                    $roleSql = "select roleid, workitemid from " 
                            . DatabaseNamesHelper::$m_map_prole2wi_tablename
                            . " where workitemid $inTxt order by workitemid";
                    $delegateResult = db_query($roleSql);
                    while($record = $delegateResult->fetchAssoc()) 
                    {
                        $id = $record['workitemid'];
                        $map[$id][] = $record['roleid'];
                    }            
                } else {
                    $roleSql = "select roleid, workitemid, expected_cardinality, ot_scf, ob_scf from " 
                            . DatabaseNamesHelper::$m_map_prole2wi_tablename
                            . " where workitemid $inTxt order by workitemid";
                    $delegateResult = db_query($roleSql);
                    while($record = $delegateResult->fetchAssoc()) 
                    {
                        $id = $record['workitemid'];
                        $roleid = $record['roleid'];
                        $map[$id][$roleid] = array(
                            'expected_cardinality'=>$record['expected_cardinality']
                            ,'ot_scf'=>$record['ot_scf']
                            ,'ob_scf'=>$record['ob_scf']
                        );
                    }            
                }
            }
            
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getTWRoleMap($inTxt, $richmap=TRUE)
    {
        try
        {
            
            $map = [];

            if(!empty($inTxt) && strlen(trim($inTxt)) > 0)
            {
                if(!$richmap)
                {
                    $roleSql = "select roleid, template_workitemid from " 
                            . DatabaseNamesHelper::$m_map_prole2tw_tablename
                            . " where template_workitemid $inTxt order by workitemid";
                    $delegateResult = db_query($roleSql);
                    while($record = $delegateResult->fetchAssoc()) 
                    {
                        $id = $record['template_workitemid'];
                        $map[$id][] = $record['roleid'];
                    }            
                } else {
                    $roleSql = "select roleid, template_workitemid, expected_cardinality, ot_scf, ob_scf from " 
                            . DatabaseNamesHelper::$m_map_prole2tw_tablename
                            . " where template_workitemid $inTxt order by template_workitemid";
                    $delegateResult = db_query($roleSql);
                    while($record = $delegateResult->fetchAssoc()) 
                    {
                        $id = $record['template_workitemid'];
                        $roleid = $record['roleid'];
                        $map[$id][$roleid] = array(
                            'expected_cardinality'=>$record['expected_cardinality']
                            ,'ot_scf'=>$record['ot_scf']
                            ,'ob_scf'=>$record['ob_scf']
                        );
                    }            
                }
            }
            
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return simple list of all workitem members in a branch.  The first param
     * is either one workitemid or an array of workitemids.
     */
    public function getBranchAntWorkitemMembersListBundle($in, $active_yn=1, $level=0)
    {
        try
        {
            $level++;
            $map = [];
            if(is_array($in))
            {
                $in_ar = $in;
            } else {
                $in_ar = array($in);
            }
            if(count($in_ar) > 0)
            {
                $inTxt = "IN (" . implode(",", $in_ar) . ")";
                $daws = $this->getWorkitemDirectAntMap($inTxt, $active_yn);
                $nextset = [];
                foreach($in_ar as $id)
                {
                    if(!empty($id))
                    {
                        $map[$id] = array('id'=>$id,'level'=>$level);
                        if(!empty($daws[$id]))
                        {
                            foreach($daws[$id] as $antwiid)
                            {
                                $nextset[] = $antwiid;
                            }
                        }
                    }
                }
                $submap = $this->getBranchAntWorkitemMembersListBundle($nextset, $active_yn, $level);
                foreach($submap as $id=>$info)
                {
                    if(!empty($id))
                    {
                        $map[$id] = $info;    
                    }
                }
            }
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return simple list of all workitem members in a branch.  The first param
     * is either one workitemid or an array of workitemids.
     */
    public function getBranchDepWorkitemMembersListBundle($in, $active_yn=1, $level=0)
    {
        try
        {
            $level--;
            $map = [];
            if(is_array($in))
            {
                $in_ar = $in;
            } else {
                $in_ar = array($in);
            }
            if(count($in_ar) > 0)
            {
                $inTxt = "IN (" . implode(",", $in_ar) . ")";
                $daws = $this->getWorkitemDirectDepMap($inTxt, $active_yn);
                $nextset = [];
                foreach($in_ar as $id)
                {
                    if(!empty($id))
                    {
                        $map[$id] = array('id'=>$id,'level'=>$level);
                        if(!empty($daws[$id]))
                        {
                            foreach($daws[$id] as $antwiid)
                            {
                                $nextset[] = $antwiid;
                            }
                        }
                    }
                }
                $submap = $this->getBranchDepWorkitemMembersListBundle($nextset, $active_yn, $level);
                foreach($submap as $id=>$info)
                {
                    if(!empty($id))
                    {
                        $map[$id] = $info;    
                    }
                }
            }
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getWorkitem2ProjectMapBundle($wid_ar)
    {
        try
        {
            $map = [];
            $projectids = [];
            $inTxt = "IN (" . implode(",", $wid_ar) . ")";
            $sql = "SELECT id,owner_projectid,active_yn "
                . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " wi "
                . " WHERE wi.id $inTxt";
            $result = db_query($sql);
            while($record = $result->fetchAssoc())
            {
                $id = $record['id'];
                $owner_projectid = $record['owner_projectid'];
                $map[$id] = $record;
                $projectids[$owner_projectid] = $owner_projectid;
                $real_wids[$id] = $id;
            } 
            
            $fake_wids = [];
            if(count($real_wids) != count($wid_ar))
            {
                foreach($wid_ar as $wid)
                {
                    if(!isset($real_wids[$wid]))
                    {
                        $fake_wids[$wid] = $wid;
                    }
                }
            }
            
            $map_subproject2project = [];
            $map_project2subproject = [];
            if(count($projectids) > 0)
            {
                $project2project_in_txt = "IN (" . implode(",", $projectids) . ")";
                $project2project_sql = "SELECT subprojectid, projectid "
                    . " FROM " . DatabaseNamesHelper::$m_map_subproject2project_tablename . " wi "
                    . " WHERE wi.subprojectid $project2project_in_txt OR wi.projectid $project2project_in_txt";
                $project2project_result = db_query($project2project_sql);
                while($record = $project2project_result->fetchAssoc())
                {
                    $subprojectid = $record['subprojectid'];
                    $projectid = $record['projectid'];
                    if(empty($map_subproject2project[$subprojectid]))
                    {
                        $map_subproject2project[$subprojectid] = [];
                    }
                    $map_subproject2project[$subprojectid][$projectid] = $projectid;
                    if(empty($map_project2subproject[$projectid]))
                    {
                        $map_project2subproject[$projectid] = [];
                    }
                    $map_project2subproject[$projectid][$subprojectid] = $subprojectid;
                } 
            }
            
            $bundle = [];
            $bundle['workitemid2projectid'] = $map;
            $bundle['found']['workitemids'] = $real_wids;
            $bundle['found']['projectids'] = $projectids;
            $bundle['found']['subproject2project'] = $map_subproject2project;
            $bundle['found']['project2subproject'] = $map_project2subproject;
            $bundle['fake']['workitemids'] = $fake_wids;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getSpeculativeBranchWorkitemMembersBundle($workitemid, $new_candidate_deps, $new_candidate_ants, $active_yn=1)
    {
        try
        {
            $summary_msg = 'OK';
            $core_bundle = $this->getBranchWorkitemMembersBundle($workitemid, $active_yn);
            $ant_map = $core_bundle['maps']['ant']['wids'];
            $dep_map = $core_bundle['maps']['dep']['wids'];
            $ant_bundle = [];
            $dep_bundle = [];
            
            $debug = [];
            $debug['initial_input'] = array('$workitemid'=>$workitemid, '$new_candidate_deps'=>$new_candidate_deps, '$new_candidate_ants'=>$new_candidate_ants);
            
            $problem_detail = [];
            $cycles_detail = [];
            
            $all_new_input_wids = array_merge_recursive($new_candidate_deps,$new_candidate_ants);
            $has_self_ref = in_array($workitemid,$all_new_input_wids);
            if($has_self_ref)
            {
                //Cannot ref itself
               $problem_detail['selfref'][] = 'self reference';
               $summary_msg = 'Relationship declared to self';
            } else {
                $all_input_wids = array_merge_recursive(array($workitemid),$all_new_input_wids);
                $debug['$all_input_wids'] = $all_input_wids;
                $w2p_bundle = $this->getWorkitem2ProjectMapBundle($all_input_wids);
                $main_projectid = $w2p_bundle['workitemid2projectid'][$workitemid]['owner_projectid'];
                $debug['$w2p_bundle'] = $w2p_bundle;
                if(count($w2p_bundle['fake']['workitemids']) > 0)
                {
                    //Some were note real wids
                   $problem_detail['fakewids'][] = $w2p_bundle['fake']['workitemids'];
                   $summary_msg = 'Bad #s ' . implode(', ',$w2p_bundle['fake']['workitemids']);

                } else
                if(count($w2p_bundle['found']['projectids'])>1)
                {
                    $ant_projects = isset($w2p_bundle['found']['project2subproject'][$main_projectid]) ? $w2p_bundle['found']['project2subproject'][$main_projectid] : [];
                    foreach($new_candidate_ants as $oneant)
                    {
                        if($main_projectid != $w2p_bundle['workitemid2projectid'][$oneant]['owner_projectid'])
                        {
                            //Only allowed if main is a subproject of the dependent item, not other way.
                            $oneprojectid = $w2p_bundle['workitemid2projectid'][$oneant]['owner_projectid'];
                            if(!isset($ant_projects[$oneprojectid]))
                            {
                                //Not okay for an ANT project to be a dependent of a main project workitem!!!!
                                $problem_detail['multiproject'][] = $w2p_bundle['found']['projectids'];
                                $summary_msg = "wid#$workitemid of project#$main_projectid declared antecedent wid#$oneant of project#$oneprojectid!";
                                break;
                            }
                        }
                    }
                    
                    $dep_projects = isset($w2p_bundle['found']['subproject2project'][$main_projectid]) ? $w2p_bundle['found']['subproject2project'][$main_projectid] : [];
                    foreach($new_candidate_deps as $onedep)
                    {
                        if($main_projectid != $w2p_bundle['workitemid2projectid'][$onedep]['owner_projectid'])
                        {
                            //Only allowed if main is a subproject of the dependent item, not other way.
                            $oneprojectid = $w2p_bundle['workitemid2projectid'][$onedep]['owner_projectid'];
                            if(!isset($dep_projects[$oneprojectid]))
                            {
                                //Not okay for an ANT project to be a dependent of a main project workitem!!!!
                                $problem_detail['multiproject'][] = $w2p_bundle['found']['projectids'];
                                $summary_msg = "wid#$workitemid of project#$main_projectid declared dependent wid#$onedep of project#$oneprojectid!";
                                break;
                            }
                        }
                    }

                } else {

                    $dep_bundle = [];
                    foreach($new_candidate_deps as $wid)
                    {
                        $one_bundle = $this->getBranchWorkitemMembersBundle($wid, $active_yn);
                        $dep_bundle[$wid] = $one_bundle;
                        if(!isset($dep_map[$wid]))
                        {
                            $dep_map[$wid] = [];
                            $dep_map[$wid]['id'] = $wid;
                        }
                        $dep_map[$wid]['new_refids'][$wid] = $wid;
                    }

                    $ant_bundle = [];
                    foreach($new_candidate_ants as $wid)
                    {
                        $one_bundle = $this->getBranchWorkitemMembersBundle($wid, $active_yn);
                        $ant_bundle[$wid] = $one_bundle;
                        if(!isset($ant_map[$wid]))
                        {
                            $ant_map[$wid] = [];
                            $ant_map[$wid]['id'] = $wid;
                        }
                        $ant_map[$wid]['new_refids'][$wid] = $wid;
                    }

                    $dep_map_keys = array_keys($dep_map);
                    foreach($dep_map_keys as $wid)
                    {
                        if($wid != $workitemid)
                        {
                            if(isset($ant_map[$wid]))
                            {
                                $problem_detail['cycle'][] = ["cycle at $wid"];
                                $cycles_detail[] = array(
                                    'msg'=>"possible cycle at $wid",
                                    'dep_refids'=>$dep_map[$wid]['new_refids'],
                                    'ant_refids'=>$ant_map[$wid]['new_refids'],
                                );
                            }
                        }
                    }
                    $ant_map_keys = array_keys($ant_map);
                    foreach($ant_map_keys as $wid)
                    {
                        if($wid != $workitemid)
                        {
                            if(isset($dep_map[$wid]))
                            {
                                $problem_detail['cycle'][] = ["cycle at $wid"];
                                $cycles_detail[] = array(
                                    'msg'=>"possible cycle at $wid",
                                    'ant_refids'=>$ant_map[$wid]['new_refids'],
                                    'dep_refids'=>$dep_map[$wid]['new_refids'],
                                );
                            } else
                            if($wid == $core_bundle['root_goalid'])
                            {
                                $problem_detail['cycle'][] = ["cycle to root at $wid"];
                                $cycles_detail[] = array(
                                    'msg'=>"root cycle at $wid",
                                    'ant_refids'=>$ant_map[$wid]['new_refids'],
                                    'dep_refids'=>$dep_map[$wid]['new_refids'],
                                );
                            }
                        }
                    }
                    if(count($cycles_detail) > 0)
                    {
                        $summary_msg = 'Cycles detected!';
                    }
                }
            }
            
            $analysis['summary']['msg'] = $summary_msg;
            $analysis['has_problems'] = count($problem_detail) > 0;
            $analysis['has_cycles'] = count($cycles_detail) > 0;
            $analysis['problem_detail'] = $problem_detail;
            $analysis['cycle_insight'] = $cycles_detail;
            $analysis['debug'] = $debug;
            
            $bundle['maps']['ant']['wids'] = $ant_map;
            $bundle['maps']['dep']['wids'] = $dep_map;
            $bundle['analysis'] = $analysis;
            $bundle['bundles']['core'] = $core_bundle;
            $bundle['bundles']['ant'] = $ant_bundle;
            $bundle['bundles']['dep'] = $dep_bundle;
//DebugHelper::showNeatMarkup($bundle,'LOOK the bundle thing');            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return information bundle for of all workitem members in a branch.
     */
    public function getBranchWorkitemMembersBundle($workitemid, $active_yn=1, $include_record_detail=FALSE)
    {
        try
        {
            $bundle = [];
            
            $sql = "SELECT p.root_goalid, wi.* "
                . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " wi "
                . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p ON p.id=wi.owner_projectid"
                . " WHERE wi.id=$workitemid";
            $main_result = db_query($sql);
            $main_record = $main_result->fetchAssoc();
                            
            $ant_map = $this->getBranchAntWorkitemMembersListBundle($workitemid, $active_yn);
            $ant_map[$workitemid] = array('id'=>$workitemid,'level'=>0);
            if($include_record_detail)
            {
                $ant_rec_map = [];
                if(count($ant_map) > 0)
                {
                    $ant_map_keys = array_keys($ant_map);
                    $inTxt = "IN (" . implode(",", $ant_map_keys) . ")";
                    $sql = "SELECT wi.* "
                        . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " wi "
                        . " WHERE wi.id $inTxt";
                    if(!empty($active_yn))
                    {
                        $sql .= " and active_yn=$active_yn ";
                    }
                    $result = db_query($sql);
                    while($record = $result->fetchAssoc()) 
                    {
                        $id = $record['id'];
                        $ant_rec_map[$id] = $record;
                    }            
                }
            }

            $dep_map = $this->getBranchDepWorkitemMembersListBundle($workitemid, $active_yn);
            $dep_map[$workitemid] = array('id'=>$workitemid,'level'=>0);
            if($include_record_detail)
            {
                $dep_rec_map = [];
                if(count($dep_map) > 0)
                {
                    $dep_map_keys = array_keys($dep_map);
                    $inTxt = "IN (" . implode(",", $dep_map_keys) . ")";
                    $sql = "SELECT wi.* "
                        . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " wi "
                        . " WHERE wi.id $inTxt";
                    if(!empty($active_yn))
                    {
                        $sql .= " and active_yn=$active_yn ";
                    }
                    $result = db_query($sql);
                    while($record = $result->fetchAssoc()) 
                    {
                        $id = $record['id'];
                        $dep_rec_map[$id] = $record;
                    }            
                }
            }
            
            $bundle['root_goalid'] = $main_record['root_goalid'];
            unset($main_record['root_goalid']);
            $bundle['main_record'] = $main_record;
            $bundle['maps']['ant']['wids'] = $ant_map;
            $bundle['maps']['dep']['wids'] = $dep_map;
            if($include_record_detail)
            {
                $bundle['maps']['ant']['records'] = $ant_rec_map;
                $bundle['maps']['dep']['records'] = $dep_rec_map;
            }
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getWorkitemAntRecordsHavingID($in_ar, $active_yn=1)
    {
        try
        {
            
            $records = [];
            if(count($in_ar) > 0)
            {
                $inTxt = "IN (" . implode(",", $in_ar) . ")";

                $sql = "select wi2wi.* , wi.active_yn "
                        . "from "
                        . DatabaseNamesHelper::$m_map_wi2wi_tablename . " wi2wi"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wi on wi.id=antwiid"
                        . " where depwiid $inTxt or antwiid $inTxt";
                if(!empty($active_yn))
                {
                    $sql .= " and wi.active_yn=$active_yn ";
                }
                $result = db_query($sql);
                while($record = $result->fetchAssoc()) 
                {
                    $records[] = $record;
                }            
            }
            
            return $records;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getTWAntRecordsHavingID($in_ar)
    {
        try
        {
            
            $records = [];
            if(count($in_ar) > 0)
            {
                $inTxt = "IN (" . implode(",", $in_ar) . ")";

                $sql = "select tw2tw.* "
                        . " FROM "
                        . DatabaseNamesHelper::$m_map_tw2tw_tablename . " tw2tw"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_template_workitem_tablename . " tw on tw.id=antwiid"
                        . " WHERE depwiid $inTxt or antwiid $inTxt";
                $result = db_query($sql);
                while($record = $result->fetchAssoc()) 
                {
                    $records[] = $record;
                }            
            }
            
            return $records;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns daw and a dap map
     */
    protected function getWorkitemDirectRichAntMap($inTxt, $active_yn=1)
    {
        try
        {
            $maps = [];

            $sql = "select depwiid, antwiid, wi.active_yn, wi.owner_projectid "
                    . "from "
                    . DatabaseNamesHelper::$m_map_wi2wi_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wi on wi.id=antwiid"
                    . " where depwiid $inTxt";
            if(!empty($active_yn))
            {
                $sql .= " and active_yn=$active_yn ";
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['depwiid'];
                $antwiid = $record['antwiid'];
                $owner_projectid = $record['owner_projectid'];
                $maps[$id]['daw'][$antwiid] = $antwiid;
                $maps[$id]['dap'][$antwiid] = $owner_projectid;
            }            
            return $maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns daw and a dap map
     */
    protected function getTWDirectRichAntMap($inTxt)
    {
        try
        {
            $maps = [];

            $sql = "select depwiid, antwiid, wi.owner_template_projectid "
                    . "from "
                    . DatabaseNamesHelper::$m_map_tw2tw_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_template_workitem_tablename . " wi on wi.id=antwiid"
                    . " where depwiid $inTxt";
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['depwiid'];
                $antwiid = $record['antwiid'];
                $owner_projectid = $record['owner_template_projectid'];
                $maps[$id]['daw'][$antwiid] =$antwiid;
                $maps[$id]['dap'][$antwiid] = $owner_projectid;
            }            
            
            return $maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getWorkitemDirectAntMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map = [];

            $sql = "select depwiid, antwiid, wi.active_yn "
                    . "from "
                    . DatabaseNamesHelper::$m_map_wi2wi_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wi on wi.id=antwiid"
                    . " where depwiid $inTxt";
            if(!empty($active_yn))
            {
                $sql .= " and active_yn=$active_yn ";
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['depwiid'];
                $map[$id][] = $record['antwiid'];
            }            
            
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @deprecated
     */
    protected function getWorkitemDirectDepMap($inTxt, $active_yn=1)
    {
        try
        {
            
            $map = [];
            $sql = "select depwiid, antwiid, wi.active_yn "
                    . "from "
                    . DatabaseNamesHelper::$m_map_wi2wi_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wi on wi.id=depwiid"
                    . " where antwiid $inTxt";
            if(!empty($active_yn))
            {
                $sql .= " and active_yn=$active_yn";
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['antwiid'];
                $map[$id][] = $record['depwiid'];
            }            
            
            return $map;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getWorkitemDirectDepMapBundle($inTxt, $active_yn=1)
    {
        try
        {
            if(is_array($inTxt))
            {
                if(count($inTxt > 0))
                {
                    $inTxt = " IN (" . implode(',', $inTxt) . ")";
                } else {
                    $inTxt = " IS NULL";    //AN ERROR REALLY?
                }
            }
            $bundle = [];
            $sql = "select depwiid, antwiid, wi.active_yn, wi.owner_projectid "
                    . "from "
                    . DatabaseNamesHelper::$m_map_wi2wi_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wi on wi.id=depwiid"
                    . " where antwiid $inTxt";
            if(!empty($active_yn))
            {
                $sql .= " and active_yn=$active_yn";
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['antwiid'];
                $dwid = $record['depwiid'];
                $bundle[$id]['ddw'][$dwid] = $dwid; //make it a real map
                $bundle[$id]['ddp'][$dwid] = $record['owner_projectid'];
            }            
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getTWDirectDepMapBundle($inTxt)
    {
        try
        {
            $bundle = [];
            
            $sql = "select depwiid, antwiid, wi.owner_template_projectid "
                    . "from "
                    . DatabaseNamesHelper::$m_map_tw2tw_tablename
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_template_workitem_tablename . " wi on wi.id=depwiid"
                    . " where antwiid $inTxt";
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['antwiid'];
                $dwid = $record['depwiid'];
                $bundle[$id]['ddw'][] = $dwid;
                $bundle[$id]['ddp'][$dwid] = $record['owner_template_projectid'];
            }            
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Returns a new array of keys if the candidate is array of key value pairs.
     */
    public function getAsArrayOfKeys($candidate_array)
    {
        if(empty($candidate_array) || !is_array($candidate_array))
        {
            $justids = [];
        } else {
            foreach($candidate_array as $k=>$v)
            {
                if(!empty($k) && $k != 0)
                {
                    $justids = array_keys($candidate_array);
                } else {
                    //Already just a list of keys
                    $justids = $candidate_array;
                }
                break;  //We only needed one iteration to figure this out.
            }
        }
        return $justids;
    }

    public function getOwnedWorkitemIDs($owner_projectid, $active_yn=1)
    {
        try
        {
            $list = [];
            $sql = "SELECT id "
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename
                    . " WHERE owner_projectid=$owner_projectid";
            if($active_yn != NULL)
            {
                $sql .= " AND active_yn=$active_yn";    
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $list[] = $record['id'];
            }            
            
            return $list;
            
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting owned workitems because " . $ex, 551234, $ex);
        }
    }

    public function getOwnedWorkitemDatesAndEffortMap($owner_projectid, $active_yn=1)
    {
        try
        {
            
            $map = [];
            $sql = "SELECT id, remaining_effort_hours, owner_personid, status_cd, planned_start_dt, actual_start_dt, planned_end_dt, actual_end_dt "
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename
                    . " WHERE owner_projectid=$owner_projectid";
            if($active_yn != NULL)
            {
                $sql .= " AND active_yn=$active_yn";    
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $map[$id] = $record;
            }            
            return $map;
            
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting owned workitems because " . $ex, 551234, $ex);
        }
    }

    public function getOwnedTWIDs($owner_template_projectid)
    {
        try
        {
            $list = [];
            $sql = "SELECT id "
                    . " FROM " . DatabaseNamesHelper::$m_template_workitem_tablename
                    . " WHERE owner_template_projectid=$owner_template_projectid";
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $list[] = $record['id'];
            }            
            
            return $list;
            
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting owned template workitems because " . $ex, 551234, $ex);
        }
    }

    public function getSubprojectWorkitemIDs($parent_projectid, $active_yn=1)
    {
        try
        {
            $list = [];
            $sql = "SELECT subprojectid, p.root_goalid "
                    . " FROM " . DatabaseNamesHelper::$m_map_subproject2project_tablename . " link "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p ON link.subprojectid=p.id"
                    . " WHERE projectid=$parent_projectid";
            
            if($active_yn != NULL)
            {
                $sql .= " AND p.active_yn=$active_yn";    
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $list[] = $record['root_goalid'];
            }            
            
            return $list;
            
        } catch (\Exception $ex) {
            throw new \Exception("Failed getting subprojects because " . $ex, 551234, $ex);
        }
    }
    
    /**
     * Get all the workitems
     */
    public function getAllWorkitemsInAllProjectsBundle($active_yn=1)
    {
        $bundle = [];
        $goalprojectmap = $this->getProjectRootGoalIds();
        foreach($goalprojectmap as $key=>$projectid)
        {
            $bundle['projects'][$projectid] = $this->getAllWorkitemsInProjectBundle($projectid, $active_yn);
        }
        return $bundle;
    }
    
    /**
     * Include workitems connected as ants of the the project root
     */
    public function getAllWorkitemsInProjectBundle($projectid, $active_yn=1)
    {
        try
        {
            $bundle = [];

            $owned_wids = $this->getOwnedWorkitemIDs($projectid, $active_yn);
            $just_subproject_wids = $this->getSubprojectWorkitemIDs($projectid, $active_yn);
            $fastlookup = $this->getFastHierarchyWorkitemIDLookup($owned_wids, $active_yn, $projectid);
            $all_map_ant2deps = $fastlookup['antwiid'];
            $all_map_dep2ants = $fastlookup['depwiid'];
            $all_tree_wids = $fastlookup['all_wids'];
            $notwowned_wids = [];
            $connected_subproject_wids = [];
            $disconnected_subproject_wids = [];
            foreach($all_tree_wids as $wid)
            {
                if(!in_array($wid, $owned_wids))
                {
                    $notwowned_wids[] = $wid;    
                }
            }
            foreach($just_subproject_wids as $wid)
            {
                if(!array_key_exists($wid, $all_tree_wids))
                {
                    $disconnected_subproject_wids[] = $wid;    
                } else {
                    $connected_subproject_wids[] = $wid;
                }
            }
            $all_wids = array_merge($owned_wids, $notwowned_wids, $disconnected_subproject_wids);
            $filter_ar['w.id'] = $all_wids;
            $workitems = $this->getBareWorkitemsByID(NULL, $filter_ar, $active_yn);
            $dates = [];
            $min_dt = NULL;
            $max_dt = NULL;
            foreach($workitems as $id=>$detail)
            {
                if($detail['root_of_projectid'] == $projectid)
                {
                    $bundle['root_goalid'] = $id;
                    //break;
                }
                $planned_start_dt = $detail['planned_start_dt'];
                $planned_end_dt = $detail['planned_end_dt'];
                $actual_start_dt = $detail['actual_start_dt'];
                $actual_end_dt = $detail['actual_end_dt'];
                if($min_dt == NULL || (!empty($planned_start_dt) && $min_dt > $planned_start_dt))
                {
                    $min_dt = $planned_start_dt;
                }
                if(!empty($actual_start_dt) && $min_dt > $actual_start_dt)
                {
                    $min_dt = $actual_start_dt;
                }
                if($max_dt == NULL || (!empty($planned_end_dt) && $max_dt < $planned_end_dt))
                {
                    $max_dt = $planned_end_dt;
                }
                if(!empty($actual_end_dt) && $max_dt < $actual_end_dt)
                {
                    $max_dt = $actual_end_dt;
                }
            }
            if(empty($min_dt) || $min_dt > $max_dt)
            {
                $min_dt = $max_dt;
            } else if(empty($max_dt) || $max_dt < $min_dt) {
                $max_dt = $min_dt;
            }
            $dates['min_dt'] = $min_dt;
            $dates['max_dt'] = $max_dt;
            $mapinfo = $this->getWorkitemMaps($all_wids, $active_yn);
            foreach($mapinfo as $wid=>$mapdetails)
            {
                $workitems[$wid]['maps'] = $mapdetails;
            }

            $bundle['root_projectid'] = $projectid;
            $bundle['lists']['owned_wids'] = $owned_wids;
            $bundle['lists']['notowned_wids'] = $notwowned_wids;
            $bundle['lists']['disconnected_subproject_wids'] = $disconnected_subproject_wids;
            $bundle['lists']['connected_subproject_wids'] = $connected_subproject_wids;
            $bundle['lists']['all_subproject_wids'] = $just_subproject_wids;
            $bundle['maps']['all_wids'] = $all_wids;
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['all_workitems'] = $workitems;
            $bundle['dates'] = $dates;
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllWorkitemsInProjectBundle($projectid, $active_yn) because " . $ex, 99876, $ex);
        }
    }
    
    /**
     * Include workitems connected to the project which are owned by the project
     */
    public function getAllTWInTPBundle($template_projectid)
    {
        try
        {
            $bundle = [];

            $owned_twids = $this->getOwnedTWIDs($template_projectid);
            $just_subproject_wids = [];// $this->getSubprojectWorkitemIDs($projectid, $active_yn);
            $fastlookup = $this->getFastHierarchyTWIDLookup($owned_twids);
            $all_tree_wids = $fastlookup['all_wids'];
            $notwowned_wids = [];
            $connected_subproject_wids = [];
            $disconnected_subproject_wids = [];
            foreach($all_tree_wids as $wid)
            {
                if(!in_array($wid, $owned_twids))
                {
                    $notwowned_wids[] = $wid;    
                }
            }
            foreach($just_subproject_wids as $wid)
            {
                if(!array_key_exists($wid, $all_tree_wids))
                {
                    $disconnected_subproject_wids[] = $wid;    
                } else {
                    $connected_subproject_wids[] = $wid;
                }
            }
            $all_wids = array_merge($owned_twids, $notwowned_wids, $disconnected_subproject_wids);
            $filter_ar['w.id'] = $all_wids;
            $workitems = $this->getBareTWsByID(NULL, $filter_ar);
            foreach($workitems as $id=>$detail)
            {
                if($detail['root_of_tpid'] == $template_projectid)
                {
                    $bundle['root_goalid'] = $id;
                    break;
                }
            }
            $mapinfo = $this->getTWMaps($all_wids);
            foreach($mapinfo as $wid=>$mapdetails)
            {
                $workitems[$wid]['maps'] = $mapdetails;
            }

            $bundle['root_tpid'] = $template_projectid;
            $bundle['root_projectid'] = $template_projectid;    //For compatability
            $bundle['lists']['owned_wids'] = $owned_twids;
            $bundle['lists']['notowned_wids'] = $notwowned_wids;
            $bundle['lists']['disconnected_subproject_wids'] = $disconnected_subproject_wids;
            $bundle['lists']['connected_subproject_wids'] = $connected_subproject_wids;
            $bundle['lists']['all_subproject_wids'] = $just_subproject_wids;
            $bundle['maps']['all_wids'] = $all_wids;
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['all_workitems'] = $workitems;
            if(empty($bundle['root_goalid']))
            {
                DebugHelper::showNeatMarkup($bundle,"Failed to find root_goalid for template#$template_projectid",'error');
                throw new \Exception("Failed to find root_goalid for template#$template_projectid");
            }
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllTWInTPBundle($template_projectid) because " . $ex, 99876, $ex);
        }
    }
    
    /**
     * Include workitems connected to the template project which are owned by the template
     * @deprecated
     */
    public function getAllWorkitemsInTemplateBundle($templateid, $active_yn=1)
    {
        try
        {
            $bundle = [];

            $owned_wids = $this->getOwnedTWIDs($templateid);
            $just_subproject_wids = [];//$this->getSubprojectWorkitemIDs($templateid, $active_yn);
            $fastlookup = $this->getFastHierarchyTWIDLookup($owned_wids);
            $all_tree_wids = $fastlookup['all_wids'];
            $notwowned_wids = [];
            $connected_subproject_wids = [];
            $disconnected_subproject_wids = [];
            foreach($all_tree_wids as $wid)
            {
                if(!in_array($wid, $owned_wids))
                {
                    $notwowned_wids[] = $wid;    
                }
            }
            foreach($just_subproject_wids as $wid)
            {
                if(!array_key_exists($wid, $all_tree_wids))
                {
                    $disconnected_subproject_wids[] = $wid;    
                } else {
                    $connected_subproject_wids[] = $wid;
                }
            }
            $all_wids = array_merge($owned_wids, $notwowned_wids, $disconnected_subproject_wids);
            $filter_ar['w.id'] = $all_wids;
            $workitems = $this->getBareTWsByID(NULL, $filter_ar);
            foreach($workitems as $id=>$detail)
            {
                if($detail['root_of_tpid'] == $templateid)
                {
                    $bundle['root_goalid'] = $id;
                    break;
                }
            }
            $mapinfo = $this->getTWMaps($all_wids);
            foreach($mapinfo as $wid=>$mapdetails)
            {
                $workitems[$wid]['maps'] = $mapdetails;
            }

            $bundle['root_templateid'] = $templateid;
            $bundle['lists']['owned_wids'] = $owned_wids;
            $bundle['lists']['notowned_wids'] = $notwowned_wids;
            $bundle['lists']['disconnected_subproject_wids'] = $disconnected_subproject_wids;
            $bundle['lists']['connected_subproject_wids'] = $connected_subproject_wids;
            $bundle['lists']['all_subproject_wids'] = $just_subproject_wids;
            $bundle['maps']['all_wids'] = $all_wids;
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['all_workitems'] = $workitems;
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllWorkitemsInTemplateBundle($templateid, $active_yn) because " . $ex, 99876, $ex);
        }
    }
    
    /**
     * Return a fast lookup map of a workitem relationships for a set of wids.
     */
    public function getFastHierarchyWorkitemIDLookup($wid_ar, $active_yn=1, $umbrella_projectid=NULL)
    {
        try
        {
            $just_wids = $this->getAsArrayOfKeys($wid_ar);
            $fast_lookup_map = [];
            $fast_lookup_map['depwiid'] = []; 
            $fast_lookup_map['antwiid'] = []; 
            $fast_lookup_map['all_wids'] = [];  //Simply a flat map of all the IDs 
            $aCoreWHERE = [];
            $aCoreJOIN = [];
            $sCoreSelect = "SELECT depwiid, antwiid"
                    . ", wa.owner_projectid as ant_projectid, wd.owner_projectid as dep_projectid "
                    . "FROM "
                    . DatabaseNamesHelper::$m_map_wi2wi_tablename . " w2w";
            if(!empty($active_yn))
            {
                $aCoreJOIN[] = " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wa on wa.id=w2w.antwiid";
                $aCoreJOIN[] = " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " wd on wd.id=w2w.depwiid";
                $aCoreWHERE[] = "wa.active_yn=$active_yn";
                $aCoreWHERE[] = "wd.active_yn=$active_yn";
            }
            if(!empty($umbrella_projectid))
            {
                //Only include workitems in the project and their direct ants
                $aCoreWHERE[] = "wd.owner_projectid=$umbrella_projectid";
            }
            $sCoreSQL = $sCoreSelect . implode(" ", $aCoreJOIN);
            if(count($aCoreWHERE) > 0)
            {
                $sCoreWHERE = implode(" and ", $aCoreWHERE) . " and ";
            } else {
                $sCoreWHERE = "";
            }
            //Now get the hierarchy one chunk at a time
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $justwlist_chunks = array_chunk($just_wids, $MAX_INCLAUSE_CHUNK_SIZE);
            foreach($justwlist_chunks as $onechunk)
            {
                $sIDTXT = implode(',',$onechunk); 
                $sIN = "(depwiid IN ($sIDTXT) or antwiid IN ($sIDTXT))";
                $sWHERE = " where " . $sCoreWHERE . $sIN;
                $sSQL = $sCoreSQL . $sWHERE;
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $depwiid = $record['depwiid'];
                    $antwiid = $record['antwiid'];
                    $dep_projectid = $record['dep_projectid'];
                    $ant_projectid = $record['ant_projectid'];
                    $fast_lookup_map['depwiid'][$depwiid][] = $antwiid;     //TODO REMOVE DEPRECATED
                    $fast_lookup_map['antwiid'][$antwiid][] = $depwiid;     //TODO REMOVE DEPRECATED
                    $fast_lookup_map['dep2ants'][$depwiid][] = $antwiid; 
                    $fast_lookup_map['ant2deps'][$antwiid][] = $depwiid; 
                    $fast_lookup_map['all_wids'][$depwiid] = $depwiid; 
                    $fast_lookup_map['all_wids'][$antwiid] = $antwiid; 
                }            
            }   
            return $fast_lookup_map;
        } catch (\Exception $ex) {
            $msg = "Failed getFastHierarchyWorkitemIDLookup for wid_ar=" . print_r($wid_ar,TRUE) 
                    . " because " . $ex;
            throw new \Exception($msg, 55123, $ex);
        }
        
    }

    /**
     * Return a fast lookup map of a template workitem relationships for a set of twids.
     */
    public function getFastHierarchyTWIDLookup($twid_ar)
    {
        try
        {
            $just_wids = $this->getAsArrayOfKeys($twid_ar);
            $fast_lookup_map = [];
            $fast_lookup_map['depwiid'] = []; 
            $fast_lookup_map['antwiid'] = []; 
            $fast_lookup_map['all_wids'] = [];  //Simply a flat map of all the IDs 
            $aCoreWHERE = [];
            $aCoreJOIN = [];
            $sCoreSelect = "select depwiid, antwiid "
                    . "from "
                    . DatabaseNamesHelper::$m_map_tw2tw_tablename . " w2w";
            $sCoreSQL = $sCoreSelect . implode(" ", $aCoreJOIN);
            if(count($aCoreWHERE) > 0)
            {
                $sCoreWHERE = implode(" and ", $aCoreWHERE) . " and ";
            } else {
                $sCoreWHERE = "";
            }
            //Now get the hierarchy one chunk at a time
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $justwlist_chunks = array_chunk($just_wids, $MAX_INCLAUSE_CHUNK_SIZE);
            foreach($justwlist_chunks as $onechunk)
            {
                $sIDTXT = implode(',',$onechunk); 
                $sIN = "depwiid IN ($sIDTXT) or antwiid IN ($sIDTXT)";
                $sWHERE = " where " . $sCoreWHERE . $sIN;
                $sSQL = $sCoreSQL . $sWHERE;
                
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $depwiid = $record['depwiid'];
                    $antwiid = $record['antwiid'];
                    $fast_lookup_map['depwiid'][$depwiid][] = $antwiid; 
                    $fast_lookup_map['antwiid'][$antwiid][] = $depwiid; 
                    $fast_lookup_map['all_wids'][$depwiid] = $depwiid; 
                    $fast_lookup_map['all_wids'][$antwiid] = $antwiid; 
                }            
            }   
            
            return $fast_lookup_map;
        } catch (\Exception $ex) {
            $msg = "Failed getFastHierarchyTWIDLookup for wid_ar=" . print_r($twid_ar,TRUE) 
                    . " because " . $ex;
            throw new \Exception($msg, 55123, $ex);
        }
        
    }
    
    public function getWorkitemAntInfoBundle($just_widmap)
    {
        try
        {
            $bundle = [];
            $bundle['metadata']['input'] = $just_widmap;
            $bundle['ant_map'] = $this->getAllWorkitemAnts($just_widmap);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getAllWorkitemAnts($ids_ar)
    {
        try
        {
            $all_maps = [];
            if(!empty($ids_ar))
            {
                if(is_array($ids_ar))
                {
                    $inTxt = 'in ('.implode(",", $ids_ar).')';
                } else {
                    //Assume it is one value
                    $inTxt = "in ($ids_ar)";
                }

                $rich_map_ants = $this->getWorkitemDirectRichAntMap($inTxt);

                foreach($ids_ar as $id)
                {
                    if(!key_exists($id, $rich_map_ants))
                    {
                        $directants_map = []; //Empty
                    } else {
                        $directants_map = $rich_map_ants[$id]['daw'];    
                    }
                    $all_maps[$id]['ants'] = $directants_map;
                    if(count($directants_map) > 0)
                    {
                        $submap = $this->getAllWorkitemAnts($directants_map);
                        foreach($submap as $parent_wid=>$antmap)
                        {
                            foreach($antmap['ants'] as $oneantid)
                            {
                                $all_maps[$id]['ants'][$oneantid] = $oneantid;
                            }
                        }
                    }
                }
            }
            
            return $all_maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getWorkitemMaps($ids_ar, $active_yn=1)
    {
        try
        {
            $all_maps = [];
            if(!empty($ids_ar) && count($ids_ar) > 0)
            {
                $inTxt = 'in ('.implode(",", $ids_ar).')';

                $map_roles = $this->getWorkitemRoleMap($inTxt);
                $map_tags = $this->getWorkitemTagMap($inTxt, $active_yn);
                $map_delegate_owner = $this->getWorkitemDelegateMap($inTxt, $active_yn);
                $map_deps = $this->getWorkitemDirectDepMapBundle($inTxt, $active_yn);
                $rich_map_ants = $this->getWorkitemDirectRichAntMap($inTxt, $active_yn);
                $map_comm_summary = $this->getCommThreadSummaryMap("workitem", $ids_ar);

                foreach($ids_ar as $id)
                {
                    if(!key_exists($id, $map_comm_summary))
                    {
                        $all_maps[$id]['comm_summary'] = []; //Empty
                    } else {
                        $all_maps[$id]['comm_summary'] = $map_comm_summary[$id];    
                    }

                    if(!key_exists($id, $map_roles))
                    {
                        $all_maps[$id]['roles'] = []; //Empty
                    } else {
                        $all_maps[$id]['roles'] = $map_roles[$id];    
                    }

                    if(!key_exists($id, $map_tags))
                    {
                        $all_maps[$id]['tags'] = []; //Empty
                    } else {
                        $all_maps[$id]['tags'] = $map_tags[$id];    
                    }

                    if(!key_exists($id, $map_delegate_owner))
                    {
                        $all_maps[$id]['delegate_owner'] = []; //Empty
                    } else {
                        $all_maps[$id]['delegate_owner'] =  $map_delegate_owner[$id];    
                    }

                    if(!key_exists($id, $map_deps))
                    {
                        $all_maps[$id]['ddw'] = []; //Empty
                        $all_maps[$id]['ddp'] = []; //Empty
                    } else {
                        $all_maps[$id]['ddw'] = $map_deps[$id]['ddw'];    
                        $all_maps[$id]['ddp'] = $map_deps[$id]['ddp'];    
                    }

                    if(!key_exists($id, $rich_map_ants))
                    {
                        $all_maps[$id]['daw'] = []; //Empty
                        $all_maps[$id]['dap'] = []; //Empty
                    } else {
                        //$daw = array_keys($rich_map_ants[$id]['daw']);
                        $all_maps[$id]['daw'] = $rich_map_ants[$id]['daw'];
                        $all_maps[$id]['dap'] = $rich_map_ants[$id]['dap'];   
                    }
                }
            }
            
            return $all_maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getTWMaps($ids_ar)
    {
        try
        {
            $all_maps = [];
            if(!empty($ids_ar) && count($ids_ar) > 0)
            {
                $inTxt = 'in ('.implode(",", $ids_ar).')';

                $map_roles = $this->getTWRoleMap($inTxt);
                $map_tags = $this->getTWTagMap($inTxt);
                $map_deps = $this->getTWDirectDepMapBundle($inTxt);
                $rich_map_ants = $this->getTWDirectRichAntMap($inTxt);

                foreach($ids_ar as $id)
                {
                    if(!key_exists($id, $map_roles))
                    {
                        $all_maps[$id]['roles'] = []; //Empty
                    } else {
                        $all_maps[$id]['roles'] = $map_roles[$id];    
                    }

                    if(!key_exists($id, $map_tags))
                    {
                        $all_maps[$id]['tags'] = []; //Empty
                    } else {
                        $all_maps[$id]['tags'] = $map_tags[$id];    
                    }

                    if(!key_exists($id, $map_deps))
                    {
                        $all_maps[$id]['ddw'] = []; //Empty
                        $all_maps[$id]['ddp'] = []; //Empty
                    } else {
                        $all_maps[$id]['ddw'] = $map_deps[$id]['ddw'];    
                        $all_maps[$id]['ddp'] = $map_deps[$id]['ddp'];    
                    }

                    if(!key_exists($id, $rich_map_ants))
                    {
                        $all_maps[$id]['daw'] = []; //Empty
                        $all_maps[$id]['dap'] = []; //Empty
                    } else {
                        $all_maps[$id]['daw'] = $rich_map_ants[$id]['daw'];    
                        $all_maps[$id]['dap'] = $rich_map_ants[$id]['dap'];   
                    }
                }
            }
            
            return $all_maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    protected function getUsecaseMaps($ids_ar, $active_yn=1)
    {
        try
        {
            $all_maps = [];
            if(!empty($ids_ar) && count($ids_ar) > 0)
            {
                $inTxt = 'in ('.implode(",", $ids_ar).')';

                $map_tags = $this->getUsecaseTagMap($inTxt, $active_yn);
                $map_delegate_owner = $this->getUsecaseDelegateMap($inTxt, $active_yn);
                $map_workitems = $this->getUsecaseWorkitemMap($inTxt, $active_yn);
                
                foreach($ids_ar as $id)
                {

                    if(!key_exists($id, $map_tags))
                    {
                        $all_maps[$id]['tags'] = []; //Empty
                    } else {
                        $all_maps[$id]['tags'] = $map_tags[$id];    
                    }

                    if(!key_exists($id, $map_delegate_owner))
                    {
                        $all_maps[$id]['delegate_owner'] = []; //Empty
                    } else {
                        $all_maps[$id]['delegate_owner'] =  $map_delegate_owner[$id];    
                    }

                    if(!key_exists($id, $map_workitems))
                    {
                        $all_maps[$id]['workitems'] = []; //Empty
                    } else {
                        $all_maps[$id]['workitems'] =  $map_workitems[$id];    
                    }
                }
            }
            
            return $all_maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getTestcaseMaps($ids_ar, $active_yn=1)
    {
        try
        {
            $all_maps = [];
            if(!empty($ids_ar) && count($ids_ar) > 0)
            {
                $inTxt = 'in ('.implode(",", $ids_ar).')';

                $map_tags = $this->getTestcaseTagMap($inTxt, $active_yn);
                $map_delegate_owner = $this->getTestcaseDelegateMap($inTxt, $active_yn);
                $map_workitems = $this->getTestcaseWorkitemMap($inTxt, $active_yn);
                $map_steps = $this->getTestcaseStepMap($inTxt, $active_yn);
                
                foreach($ids_ar as $id)
                {

                    if(!key_exists($id, $map_tags))
                    {
                        $all_maps[$id]['tags'] = []; //Empty
                    } else {
                        $all_maps[$id]['tags'] = $map_tags[$id];    
                    }

                    if(!key_exists($id, $map_delegate_owner))
                    {
                        $all_maps[$id]['delegate_owner'] = []; //Empty
                    } else {
                        $all_maps[$id]['delegate_owner'] =  $map_delegate_owner[$id];    
                    }

                    if(!key_exists($id, $map_workitems))
                    {
                        $all_maps[$id]['workitems'] = []; //Empty
                    } else {
                        $all_maps[$id]['workitems'] =  $map_workitems[$id];    
                    }
                    
                    if(!key_exists($id, $map_steps))
                    {
                        $all_maps[$id]['steps'] = []; //Empty
                    } else {
                        $all_maps[$id]['steps'] =  $map_steps[$id];    
                    }
                }
            }
            
            return $all_maps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of project detail
     */
    public function getRichProjectsByIDBundle($exclude_projectid=NULL, $active_yn=1)
    {
        try
        {
            $min_dt = NULL;
            $max_dt = NULL;
            $min_start_dt = NULL;
            $max_start_dt = NULL;
            $min_end_dt = NULL;
            $max_end_dt = NULL;
            $bundle = [];
            
            if($active_yn == 1)
            {
                $only_active = TRUE;    
            } else {
                $only_active = FALSE;
            }
            if($exclude_projectid)
            {
                $filter = "prj.id <> $exclude_projectid";
            } else {
                $filter = NULL;   
            }
            $order_by_ar=NULL;
            $key_fieldname = NULL;
            $themap = $this->getProjectsData($order_by_ar,$key_fieldname,$only_active,$filter);
            //$pids_ar = array_keys($themap);
            foreach($themap as $projectid=>$record)
            {
                $planned_start_dt = $record['planned_start_dt'];
                $actual_start_dt = $record['actual_start_dt'];
                $planned_end_dt = $record['planned_end_dt'];
                $actual_end_dt = $record['actual_end_dt'];
                if(!empty($actual_start_dt))
                {
                    if(empty($min_start_dt) || $actual_start_dt < $min_start_dt)
                    {
                        $min_start_dt = $actual_start_dt;
                    }
                    if(empty($max_start_dt) || $actual_start_dt > $max_start_dt)
                    {
                        $max_start_dt = $actual_start_dt;
                    }
                } else {
                    if(!empty($planned_start_dt))
                    {
                        if(empty($min_start_dt) || $planned_start_dt < $min_start_dt)
                        {
                            $min_start_dt = $planned_start_dt;
                        }
                        if(empty($max_start_dt) || $planned_start_dt > $max_start_dt)
                        {
                            $max_start_dt = $planned_start_dt;
                        }
                    }
                }
                if(!empty($actual_end_dt))
                {
                    if(empty($min_end_dt) || $actual_end_dt < $min_end_dt)
                    {
                        $min_end_dt = $actual_end_dt;
                    }
                    if(empty($max_end_dt) || $actual_end_dt > $max_end_dt)
                    {
                        $max_end_dt = $actual_end_dt;
                    }
                } else {
                    if(!empty($planned_end_dt))
                    {
                        if(empty($min_end_dt) || $planned_end_dt < $min_end_dt)
                        {
                            $min_end_dt = $planned_end_dt;
                        }
                        if(empty($max_end_dt) || $planned_end_dt > $max_end_dt)
                        {
                            $max_end_dt = $planned_end_dt;
                        }
                    }
                }
                if(empty($min_dt) || $min_start_dt < $min_dt)
                {
                    $min_dt = $min_start_dt;
                }
                if(empty($max_dt) || $max_start_dt > $max_dt)
                {
                    $max_dt = $max_start_dt;
                }
                if(empty($min_dt) || $min_end_dt < $min_dt)
                {
                    $min_dt = $min_end_dt;
                }
                if(empty($max_dt) || $max_end_dt > $max_dt)
                {
                    $max_dt = $max_end_dt;
                }
            }
            $bundle['dates']['min_dt'] = $min_dt;
            $bundle['dates']['max_dt'] = $max_dt;
            $bundle['dates']['min_start_dt'] = $min_start_dt;
            $bundle['dates']['max_start_dt'] = $max_start_dt;
            $bundle['dates']['min_end_dt'] = $min_end_dt;
            $bundle['dates']['max_end_dt'] = $max_end_dt;
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['projects'] = $themap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * Return a rich map of workitem detail
     */
    public function getRichWorkitemsByIDBundle($projectid=NULL, $filter_ar=NULL, $active_yn=1, $named_filter_ar=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $expecting_atleast_one_wid_result = ($projectid != NULL && $filter_ar==NULL && $active_yn==1 && $named_filter_ar==NULL);
            $min_dt = NULL;
            $max_dt = NULL;
            $min_start_dt = NULL;
            $max_start_dt = NULL;
            $min_end_dt = NULL;
            $max_end_dt = NULL;
            $bundle = [];
            $people_maps = [];
            $themap = $this->getBareWorkitemsByID($projectid, $filter_ar, $active_yn, $named_filter_ar);
            $thesprintbundle_lookupmap = $this->getWorkitem2SprintMapBundle($projectid);
            $max_query_chunk_size = 4;
            $wids_ar = array_keys($themap);
            $wid_count = count($wids_ar);
            
            if($expecting_atleast_one_wid_result && $wid_count < 1)
            {
                //DebugHelper::showNeatMarkup(array('$themap'=>$themap),"LOOK expected at least one workitem in project#$projectid!",'error');
                throw new \Exception("Did NOT find any workitems in project#$projectid");
            }
            
            $widIdx = 0;
            $ids_ar = [];
            $allw2p = [];
            $leafmap = [];
            $subprojectid2workitemid = [];
            $just_subprojectwids = [];
            $chunkSizeSofar = -1;
            while($widIdx < $wid_count)
            {
                $wid = $wids_ar[$widIdx];
                $ids_ar[] = $wid;
                $wbareitem = $themap[$wid];
                $planned_start_dt = $wbareitem['planned_start_dt'];
                $actual_start_dt = $wbareitem['actual_start_dt'];
                $planned_end_dt = $wbareitem['planned_end_dt'];
                $actual_end_dt = $wbareitem['actual_end_dt'];
                $owner_projectid = $wbareitem['owner_projectid'];
                $owner_personid = $wbareitem['owner_personid'];
                if(!isset($people_maps[$owner_personid]))
                {
                    $people_maps[$owner_personid] = [];
                    $people_maps[$owner_personid]['widmap']['owner'] = [];
                }
                $people_maps[$owner_personid]['widmap']['owner'][$wid] = $wid;
                if($wbareitem['root_of_projectid'] == $projectid)
                {
                    $root_goalid = $wid;
                }
                if(!empty($actual_start_dt))
                {
                    if(empty($min_start_dt) || $actual_start_dt < $min_start_dt)
                    {
                        $min_start_dt = $actual_start_dt;
                    }
                    if(empty($max_start_dt) || $actual_start_dt > $max_start_dt)
                    {
                        $max_start_dt = $actual_start_dt;
                    }
                } else {
                    if(!empty($planned_start_dt))
                    {
                        if(empty($min_start_dt) || $planned_start_dt < $min_start_dt)
                        {
                            $min_start_dt = $planned_start_dt;
                        }
                        if(empty($max_start_dt) || $planned_start_dt > $max_start_dt)
                        {
                            $max_start_dt = $planned_start_dt;
                        }
                    }
                }
                if(!empty($actual_end_dt))
                {
                    if(empty($min_end_dt) || $actual_end_dt < $min_end_dt)
                    {
                        $min_end_dt = $actual_end_dt;
                    }
                    if(empty($max_end_dt) || $actual_end_dt > $max_end_dt)
                    {
                        $max_end_dt = $actual_end_dt;
                    }
                } else {
                    if(!empty($planned_end_dt))
                    {
                        if(empty($min_end_dt) || $planned_end_dt < $min_end_dt)
                        {
                            $min_end_dt = $planned_end_dt;
                        }
                        if(empty($max_end_dt) || $planned_end_dt > $max_end_dt)
                        {
                            $max_end_dt = $planned_end_dt;
                        }
                    }
                }
                if(empty($min_dt) || $min_start_dt < $min_dt)
                {
                    $min_dt = $min_start_dt;
                }
                if(empty($max_dt) || $max_start_dt > $max_dt)
                {
                    $max_dt = $max_start_dt;
                }
                if(empty($min_dt) || $min_end_dt < $min_dt)
                {
                    $min_dt = $min_end_dt;
                }
                if(empty($max_dt) || $max_end_dt > $max_dt)
                {
                    $max_dt = $max_end_dt;
                }
                if($chunkSizeSofar === $max_query_chunk_size || $widIdx === ($wid_count-1))
                {
                    //Run queries on this
                    $submaps = $this->getWorkitemMaps($ids_ar, $active_yn);
                    foreach($submaps as $wid=>$mapdetails)
                    {
                        $themap[$wid]['maps'] = $mapdetails;
                        $antwid_in = [];
                        $antwid_out = [];
                        foreach($themap[$wid]['maps']['dap'] as $antwid=>$pid)
                        {
                            $allw2p[$antwid] = $pid;
                            if($projectid != $pid)
                            {
                                $subprojectid2workitemid[$pid] = $antwid;
                                $just_subprojectwids[] = $antwid;
                                $antwid_out[$antwid] = $antwid;
                            } else {
                                $antwid_in[$antwid] = $antwid;
                            }
                        }
                        $antwid_in_count = count($antwid_in);
                        if($antwid_in_count == 0)
                        {
                            $leafmap[$wid] = $wid;
                        }
                    }
                    $ids_ar = [];
                    $chunkSizeSofar=0;
                }
                $allw2p[$wid] = $owner_projectid;   //Othewise we will miss orphans
                $chunkSizeSofar++;
                $widIdx++;
            }
            if(count($ids_ar) > 0)
            {
                $submaps = $this->getWorkitemMaps($ids_ar, $active_yn);
                foreach($submaps as $wid=>$mapdetails)
                {
                    $themap[$wid]['maps'] = $mapdetails;
                    foreach($themap[$wid]['maps']['dap'] as $wid=>$pid)
                    {
                        $allw2p[$wid] = $pid;
                        if($projectid != $pid)
                        {
                            $subprojectid2workitemid[$pid] = $wid;
                            $just_subprojectwids[] = $wid;
                        }
                    }
                }
            }
            
            if(count($just_subprojectwids)>0)
            {
                //Add relevant detail for the root of the subprojects
                $inTxt = "";
                $ant_proj_ddw_bundle = $this->getWorkitemDirectDepMapBundle($inTxt);
                $filter_ar = array('w.id'=>$just_subprojectwids);
                $the_spw_map = $this->getBareWorkitemsByID(NULL, $filter_ar, NULL);
                foreach($the_spw_map as $wid=>$detail)
                {
                    $themap[$wid] = $detail;
                    $themap[$wid]['maps'] = array('ddw'=>[],'daw'=>[],'dap'=>[],'delegate_owner'=>[]);
                    if(isset($ant_proj_ddw_bundle[$wid]))
                    {
                        $themap[$wid]['maps']['ddw'] = $ant_proj_ddw_bundle[$wid]['ddw'];
                        //$themap[$wid]['maps']['ddp'] = $ant_proj_ddw_bundle[$wid]['dpp'];
                    }
                    //TODO!!!!  FIX THISZ PART!!!!!!!!!!!!!!!!
                }  
            }
            
            $bundle['metadata']['projectid'] = $projectid;
            $bundle['workitems'] = $themap;
            $bundle['workitem2project'] = $allw2p;
            $bundle['subprojectid2workitemid'] = $subprojectid2workitemid;
            //$bundle['metadata']['rgid_tracker'] = 'method0';
            $bundle['metadata']['wid_count'] = $wid_count;
            if($wid_count > 0)
            {
                if(!empty($root_goalid))
                {
                    //Because the root was not already added!
                    $allw2p[$root_goalid] = $projectid; 
                    $bundle['metadata']['root_goalid'] = $root_goalid;
                    //$bundle['metadata']['rgid_tracker'] = 'method1';
                } else {
                    //This will happen when the query did not include the project root.
                    $project_record = $this->getOneProjectDetailData($projectid);
                    if(empty($project_record) || empty($project_record['root_goalid']))
                    {
                        //This will happen if the project has been deleted.
                        drupal_set_message("The antecedent project#$projectid is no longer available",'warning');
                    }
                    $bundle['metadata']['root_goalid'] = $project_record['root_goalid'];
                    //$bundle['metadata']['rgid_tracker'] = 'method2';
                    //$bundle['metadata']['debug_$project_record'] = $project_record;
                }
                $bundle['dates']['min_dt'] = $min_dt;
                $bundle['dates']['max_dt'] = $max_dt;
                $bundle['dates']['min_start_dt'] = $min_start_dt;
                $bundle['dates']['max_start_dt'] = $max_start_dt;
                $bundle['dates']['min_end_dt'] = $min_end_dt;
                $bundle['dates']['max_end_dt'] = $max_end_dt;
            }
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['sprint_lookup'] = $thesprintbundle_lookupmap;
            $bundle['people_maps'] = $people_maps;
            $bundle['leafmap'] = $leafmap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of template workitem detail
     */
    public function getRichTWsByIDBundle($template_projectid=NULL, $filter_ar=NULL, $named_filter_ar=NULL)
    {
        try
        {
            if(empty($template_projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $bundle = [];
            $people_maps = [];
            
            $themap = $this->getBareTWsByID($template_projectid, $filter_ar, $named_filter_ar);
            $max_query_chunk_size = 4;
            $wids_ar = array_keys($themap);
            $widCount = count($wids_ar);
            $widIdx = 0;
            $ids_ar = [];
            $allw2p = [];
            $leafmap = [];
            $subprojectid2workitemid = [];
            $just_subprojectwids = [];
            $chunkSizeSofar = -1;
            while($widIdx < $widCount)
            {
                $twid = $wids_ar[$widIdx];
                $ids_ar[] = $twid;
                $wbareitem = $themap[$twid];
                $owner_projectid = $wbareitem['owner_template_projectid'];
                $owner_personid = $wbareitem['owner_personid'];
                if(!isset($people_maps[$owner_personid]))
                {
                    $people_maps[$owner_personid] = [];
                    $people_maps[$owner_personid]['widmap']['owner'] = [];
                }
                $people_maps[$owner_personid]['widmap']['owner'][$twid] = $twid;
//drupal_set_message("LOOK barestyff #$wid " . print_r($wbareitem,TRUE) )  ;             
                if($wbareitem['root_of_tpid'] == $template_projectid)
                {
                    $root_template_workitemid = $twid;
                }
                if($chunkSizeSofar === $max_query_chunk_size || $widIdx === ($widCount-1))
                {
                    //Run queries on this
                    $submaps = $this->getTWMaps($ids_ar);
                    foreach($submaps as $twid=>$mapdetails)
                    {
                        $themap[$twid]['maps'] = $mapdetails;
                        $antwid_in = [];
                        $antwid_out = [];
                        foreach($themap[$twid]['maps']['dap'] as $antwid=>$pid)
                        {
                            $allw2p[$antwid] = $pid;
                            if($template_projectid != $pid)
                            {
                                $subprojectid2workitemid[$pid] = $antwid;
                                $just_subprojectwids[] = $antwid;
                                $antwid_out[$antwid] = $antwid;
                            } else {
                                $antwid_in[$antwid] = $antwid;
                            }
                        }
                        $antwid_in_count = count($antwid_in);
                        if($antwid_in_count == 0)
                        {
                            $leafmap[$twid] = $twid;
                        }
                    }
                    $ids_ar = [];
                    $chunkSizeSofar=0;
                }
                $allw2p[$twid] = $owner_projectid;   //Othewise we will miss orphans
                $chunkSizeSofar++;
                $widIdx++;
            }
            if(count($ids_ar) > 0)
            {
                $submaps = $this->getTWMaps($ids_ar, $active_yn);
                foreach($submaps as $twid=>$mapdetails)
                {
                    $themap[$twid]['maps'] = $mapdetails;
                    foreach($themap[$twid]['maps']['dap'] as $twid=>$pid)
                    {
                        $allw2p[$twid] = $pid;
                        if($template_projectid != $pid)
                        {
                            $subprojectid2workitemid[$pid] = $twid;
                            $just_subprojectwids[] = $twid;
                        }
                    }
                }
            }
            
            if(count($just_subprojectwids)>0)
            {
                //Add relevant detail for the root of the subprojects
                $filter_ar = array('w.id'=>$just_subprojectwids);
                $the_spw_map = $this->getBareTWsByID(NULL, $filter_ar, NULL);
                foreach($the_spw_map as $twid=>$detail)
                {
                    $themap[$twid] = $detail;
                    $themap[$twid]['maps'] = array('ddw'=>[],'daw'=>[],'dap'=>[],'delegate_owner'=>[]);
                }  
            }
            
            $bundle['metadata']['tpid'] = $template_projectid;
            $bundle['tws'] = $themap;
            $bundle['tw2tp'] = $allw2p;
            //$bundle['subprojectid2twid'] = $subprojectid2workitemid;
            if($widCount > 0)
            {
                if(!empty($root_template_workitemid))
                {
                    //Because the root was not already added!
                    $allw2p[$root_template_workitemid] = $template_projectid; 
                    $bundle['metadata']['root_template_workitemid'] = $root_template_workitemid;
                } else {
                    //This will happen when the query did not include the project root.
                    $project_record = $this->getOneTPDetailData($template_projectid);
                    $bundle['metadata']['root_template_workitemid'] = $project_record['root_template_workitemid'];
                }
            }
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();
            $bundle['people_maps'] = $people_maps;
            $bundle['leafmap'] = $leafmap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of workitem detail
     * @deprecated use getRichWorkitemsByID instead
     */
    public function getRichGoalsByID($projectid, $filter_ar=NULL, $active_yn=1)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        if(empty($filter_ar))
        {
            $filter_ar = [];
        }
        $filter_ar['w.workitem_basetype'] = "'G'";
        $bundle = $this->getRichWorkitemsByIDBundle($projectid, $filter_ar, $active_yn);
        return $bundle['workitems'];
    }

    /**
     * Return a rich map of workitem detail
     */
    public function getRichWorkitemsByID($projectid, $filter=NULL, $active_yn=1)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        $bundle = $this->getRichWorkitemsByIDBundle($projectid, $filter, $active_yn);
        return $bundle['workitems'];
    }

    /**
     * Return a rich map of workitem template detail
     */
    public function getRichTWsByID($template_projectid, $filter=NULL)
    {
        if(empty($template_projectid))
        {
            throw new \Exception("Missing required template projectid!");
        }
        $bundle = $this->getRichTWsByIDBundle($template_projectid, $filter);
        return $bundle['tws'];
    }

    /**
     * Return a rich map of workitem detail
     */
    public function getOneRichWorkitemRecord($workitemid)
    {
        if(empty($workitemid))
        {
            throw new \Exception("Missing required workitemid!");
        }
        try
        {
            $record = $this->getOneBareWorkitemRecord($workitemid);
            $ids_ar = array($workitemid);
            $submaps = $this->getWorkitemMaps($ids_ar, TRUE);
            foreach($submaps as $wid=>$mapdetails)
            {
                $record['maps'] = $mapdetails;
            }
            
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of workitem detail
     */
    public function getOneRichTWRecord($template_workitemid)
    {
        if(empty($template_workitemid))
        {
            throw new \Exception("Missing required template workitemid!");
        }
        try
        {
            $record = $this->getOneBareTWRecord($template_workitemid);
            $ids_ar = array($template_workitemid);
            $submaps = $this->getTWMaps($ids_ar, TRUE);
            foreach($submaps as $twid=>$mapdetails)
            {
                $record['maps'] = $mapdetails;
            }
            
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of use case detail
     */
    public function getOneRichUsecaseRecord($usecaseid)
    {
        if(empty($usecaseid))
        {
            throw new \Exception("Missing required usecaseid!");
        }
        try
        {
            $record = $this->getOneBareUsecaseRecord($usecaseid);
            $ids_ar = array($usecaseid);
            $submaps = $this->getUsecaseMaps($ids_ar, TRUE);
            foreach($submaps as $ids=>$mapdetails)
            {
                $record['maps'] = $mapdetails;
            }
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of use case detail
     */
    public function getOneRichTestcaseRecord($testcaseid, $include_people_info=TRUE)
    {
        if(empty($testcaseid))
        {
            throw new \Exception("Missing required testcaseid!");
        }
        try
        {
            $record = $this->getOneBareTestcaseRecord($testcaseid);
            $ids_ar = array($testcaseid);
            $submaps = $this->getTestcaseMaps($ids_ar, TRUE);
            if(!isset($submaps[$testcaseid]))
            {
                $record['maps'] = [];   //Nothing found
            } else {
                //There should only be one ID key
                $record['maps'] = $submaps[$testcaseid];
            }
            
            if($include_people_info)
            {
                $owner_projectid = $record['owner_projectid'];
                $relevant_personid_ar = $this->getAllPersonIDsInProject($owner_projectid);
                $record['maps']['people']['project_members'] = $relevant_personid_ar;
                
                $relevant_wi_ar = $record['maps']['workitems'];

                $project_owner_personid_ar = $this->getProjectOwnerPersonIDs($owner_projectid);
                $record['maps']['people']['project_owners'] = $project_owner_personid_ar;
                
                $workitem_owner_personid_ar = $this->getWorkitemOwners($relevant_wi_ar);
                $record['maps']['people']['workitem_owners'] = $workitem_owner_personid_ar;
                
                $peopleinfo = $this->getPeopleDetailData($relevant_personid_ar);
                
                $lookups['people'] = $peopleinfo;
                $record['lookups'] = $lookups;
            }
            
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectOwnerPersonIDs($projectid)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }

        //TODO -- Enhance with the DELEGATE OWNERS of the ROOT_GOALID!!!!
        
        $personids_map = [];
        
        $sql = "SELECT owner_personid FROM " . DatabaseNamesHelper::$m_project_tablename . " WHERE id=$projectid";
        $result = db_query($sql);
        while($record = $result->fetchAssoc()) 
        {
            $owner_personid = $record['owner_personid'];
            $personids_map[$owner_personid] = $owner_personid;
        }            
        return $personids_map;
    }
    
    
    
    
    /**
     * Return a rich map of workitem detail
     */
    public function getOneRichWorkitemRecordWithLookupInfo($workitemid)
    {
        if(empty($workitemid))
        {
            throw new \Exception("Missing required workitemid!");
        }
        try
        {
            $record = $this->getOneBareWorkitemRecord($workitemid);
            $owners = [];
            $ids_ar = array($workitemid);
            $submaps = $this->getWorkitemMaps($ids_ar, TRUE);
            $lookups = [];
            foreach($submaps as $wid=>$mapdetails)
            {
                $record['maps'] = $mapdetails;
                $owners = $mapdetails['delegate_owner'];
            }
            $owners[] = $record['owner_personid'];
            $peopleinfo = $this->getPeopleDetailData($owners);
            $lookups['people'] = $peopleinfo;
            $record['lookups'] = $lookups;
            
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of workitem detail
     */
    public function getOneRichSprintWorkitemRecord($sprintid, $workitemid)
    {
        if(empty($sprintid))
        {
            throw new \Exception("Missing required sprintid!");
        }
        if(empty($workitemid))
        {
            throw new \Exception("Missing required workitemid!");
        }
        try
        {
            $record = $this->getOneBareSprintWorkitemRecord($sprintid, $workitemid);
            $ids_ar = array($workitemid);
            $submaps = $this->getWorkitemMaps($ids_ar, TRUE);
            foreach($submaps as $wid=>$mapdetails)
            {
                $record['maps'] = $mapdetails;
            }
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a bundle of date information
     */    
    public function getMaxMinDates($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $sSQL = "select p.root_goalid, w.planned_start_dt, w.actual_start_dt, w.planned_end_dt, w.actual_end_dt "
                    . " from " . DatabaseNamesHelper::$m_workitem_tablename . " w "
                    . " left join " . DatabaseNamesHelper::$m_project_tablename . " p on p.id=w.owner_projectid and w.id=p.root_goalid"
                    . " where w.owner_projectid=$projectid and w.active_yn=1 ";
            $result = db_query($sSQL);

            $root_info = [];
            $min_psdt = NULL;
            $min_asdt = NULL;
            $max_pedt = NULL;
            $max_aedt = NULL;
            while($record = $result->fetchAssoc()) 
            {
                $root_goalid = $record['root_goalid'];
                if($root_goalid !== NULL)
                {
                    $root_info = $record;
                } else {
                    $planned_start_dt = $record['planned_start_dt'];
                    $actual_start_dt = $record['actual_start_dt'];
                    $planned_end_dt = $record['planned_end_dt'];
                    $actual_end_dt = $record['actual_end_dt'];
                    if(empty($min_psdt) || $min_psdt > $planned_start_dt)
                    {
                        $min_psdt = $planned_start_dt;
                    }
                    if(empty($min_asdt) || $min_asdt > $actual_start_dt)
                    {
                        $min_asdt = $actual_start_dt;
                    }
                    if($max_pedt < $planned_end_dt)
                    {
                        $max_pedt = $planned_end_dt;
                    }
                    if($max_aedt < $actual_end_dt)
                    {
                        $max_aedt = $actual_end_dt;
                    }
                }
            }
            
            $root_info['edt'] = !empty($root_info['actual_end_dt']) ? $root_info['actual_end_dt'] : $root_info['planned_end_dt'];
            
            $member_maxmin = [];
            $member_maxmin['min']['planned'] = $min_psdt;
            $member_maxmin['min']['actual'] = $min_asdt;
            $member_maxmin['max']['planned'] = $max_pedt;
            $member_maxmin['max']['actual'] = $max_aedt;
            $member_maxmin['min']['any'] = max($min_psdt, $min_asdt);
            $member_maxmin['max']['any'] = max($max_pedt, $max_aedt);
            
            $bundle = [];
            $bundle['root_info'] = $root_info;
            $bundle['member_maxmin'] = $member_maxmin;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
}

