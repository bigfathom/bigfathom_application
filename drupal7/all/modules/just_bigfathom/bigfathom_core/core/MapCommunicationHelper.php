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
require_once 'BasicMapHelper.php';

/**
 * This class tells us about fundamental communication mappings.
 * Try to keep this file small because it is loaded every time.
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapCommunicationHelper extends \bigfathom\BasicMapHelper
{

    public function getCommThreadSummaryMap($contextname, $contextid_selector)
    {
        try
        {
            $map_comms = [];
            
            $link_steps = FALSE;
            $only_active = TRUE;
            if($contextname == 'project')
            {
                $idprefix = $contextname;
                $main_comm_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
            } else
            if($contextname == 'sprint')
            {
                $idprefix = $contextname;
                $main_comm_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
            } else
            if($contextname == 'workitem' || $contextname == 'goal' || $contextname == 'task')
            {
                $idprefix = 'workitem';
                $main_comm_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
            } else
            if($contextname == 'testcase')
            {
                $idprefix = 'testcase';
                $main_comm_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
            } else
            if($contextname == 'testcasestep')
            {
                $idprefix = 'testcasestep';
                $link_steps = TRUE;
                $main_comm_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $map_stepscom_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                $steps_tablename = DatabaseNamesHelper::$m_testcasestep_tablename;
            } else {
                throw new \Exception("Did not recognize context name of '$contextname'");
            }
            
            if(is_array($contextid_selector))
            {
                //Multiple values
                $inTxt = " IN (" . implode(",", $contextid_selector).")";
            } else {
                //Single value
                $inTxt = " = $contextid_selector";
            }

            $map_thread_root_comid2stepid = [];
            $map_thread_root_stepid2comid = [];
            
            //Construct the query
            $contexidkeyname = "{$idprefix}id";
            $where_ar = [];
            if(!$link_steps)
            {
                $contexidkey_sqlfieldname = "gc.$contexidkeyname";
                $where_ar[] = "$contexidkey_sqlfieldname $inTxt"; 
                $more_mainfields_txt = "";
                $groupbyfields = " gc.id, parent_comid ";
                $orderby_txt = "gc.id";
                $jointable_txt = " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                    . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                $mainfields = " gc.id as comid, parent_comid, $contexidkey_sqlfieldname, gc.status_cd_at_time_of_com,"
                        . " gc.title_tx, gc.body_tx, gc.owner_personid,"
                        . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                        . " gc.active_yn, "
                        . " gc.updated_dt, gc.created_dt $more_mainfields_txt";
                $where_txt = implode(" and ", $where_ar);
                $sSQL = "SELECT $mainfields,"
                        . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                        . " FROM $main_comm_tablename gc"
                        . " $jointable_txt";
                $sSQL .= " WHERE $where_txt";
                if($only_active)
                {
                    $sSQL .= " AND gc.active_yn=1";
                }
                $sSQL .= " GROUP BY $groupbyfields";
                $sSQL .= " ORDER BY $orderby_txt";

                $commResult = db_query($sSQL);
                $main_rows = [];
                while($record = $commResult->fetchAssoc()) 
                {
                    $main_rows[] = $record;
                }
            } else {
                if(!empty($contextid_selector))
                {
                    $thread_step_mainsql_bundle = $this->getCommThreadSummaryMapTestcaseStepMainSQLBundle($contextid_selector);
                    $map_thread_root_comid2stepid = $thread_step_mainsql_bundle['map_thread_root']['comid2stepid'];
                    $map_thread_root_stepid2comid = $thread_step_mainsql_bundle['map_thread_root']['stepid2comid'];
                    $main_rows = $thread_step_mainsql_bundle['main_rows'];
                } else {
                    $main_rows = [];
                }
            }
            
            //Process the query
            $map_comid2itemid = [];
            $max_indent = 0;
            $rootmap = [];
            $map_comid2levelkey = [];
            $nestingmap = [];
            $actionitems = [];
            //while($record = $commResult->fetchAssoc()) 
            foreach($main_rows as $record)
            {
                $comid = $record['comid'];
                $contexidkeyvalue = $record[$contexidkeyname];
                
                if(!isset($map_comid2itemid[$comid]))
                {
                    $map_comid2itemid[$comid] = [];
                }
                $map_comid2itemid[$comid][$contexidkeyvalue] = $contexidkeyvalue;
                
                $parent_comid = $record['parent_comid'];
                $action_reply_cd = $record['action_reply_cd'];
                $terminal_yn = $record['terminal_yn'];
                $happy_yn = $record['happy_yn'];
                if(!isset($map_comms[$contexidkeyvalue]))
                {
                    $map_comms[$contexidkeyvalue] = [];
                    $map_comms[$contexidkeyvalue]['all_active'] = 0;
                    $map_comms[$contexidkeyvalue]['thread_happy_yn'] = $happy_yn;
                }
                if(empty($parent_comid))
                {
                    $indentlevel = 0;
                    $rootmap[$comid] = $comid;
                } else {
                    if(array_key_exists($parent_comid, $nestingmap))
                    {
                        $indentlevel = $nestingmap[$parent_comid] + 1;
                    } else {
                        $indentlevel = 1;
                    }
                    $nestingmap[$comid] = $indentlevel;
                    if(array_key_exists($parent_comid, $rootmap))
                    {
                        $rootmap[$comid] = $rootmap[$parent_comid];    
                    } else {
                        //Parent is the root of a thread
                        $rootmap[$comid] = $parent_comid;    
                    }
                    if($max_indent < $indentlevel)
                    {
                        $max_indent = $indentlevel;
                    }
                }
                $root_comid = $rootmap[$comid];
                $map_comms[$contexidkeyvalue]['root_comid'] = $root_comid;
                if(!$happy_yn)
                {
                    //Mark the thread root as not happy
                    $map_comms[$contexidkeyvalue]['thread_happy_yn'] = FALSE;
                }
                $action_requested_concern = $record['action_requested_concern'];
                $arcd = $record['action_reply_cd'];
                $map_comms[$contexidkeyvalue]['all_active']++;
                $actionreq_levelkey = NULL;
                if($action_requested_concern == 0)
                {
                    if($indentlevel == 0)
                    {
                        $actionreq_levelkey = 'info';
                    } else {
                        $actionreq_levelkey = 'reply';
                    }
                } else {
                    if(empty($arcd))
                    {
                        if($action_requested_concern < 20)
                        {
                            $actionreq_levelkey = 'arcl';
                        } else
                        if($action_requested_concern < 30)
                        {
                            $actionreq_levelkey = 'arcm';
                        } else {
                            $actionreq_levelkey = 'arch';
                        }
                    }
                }
                $map_comid2levelkey[$comid] = $actionreq_levelkey;
                if($action_requested_concern > 0)
                {
                    $actionitems[$comid] = array();
                    $actionreq_levels[$comid] = array();
                }
                if(!empty($action_reply_cd) && $terminal_yn==1)
                {
                    //Mark parent as resolved
                    $actionitems[$parent_comid][$comid] = $action_reply_cd;
                }
            }

            //Mark each message with the thread summary status.
            foreach($map_comid2itemid as $comid=>$contexidkeyvalue_ar)
            {
                foreach($contexidkeyvalue_ar as $contexidkeyvalue)
                {
                    $root_comid = $map_comms[$contexidkeyvalue]['root_comid'];
                    $actionreq_levelkey = $map_comid2levelkey[$comid];

                    if($actionreq_levelkey == 'info')
                    {
                        //These never close
                        $summary_status = "open";
                    } else {
                        //These can close
                        if(empty($actionitems[$root_comid]))
                        {
                            $summary_status = "open";
                        } else {
                            $summary_status = "closed";
                        }
                    }

                    if(!isset($map_comms[$contexidkeyvalue]['count_by_thread_summary_status']))
                    {
                        $map_comms[$contexidkeyvalue]['total_info_count'] = 0;
                        $map_comms[$contexidkeyvalue]['total_open_action_request_count'] = 0;

                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['open']['arcl'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['open']['arcm'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['open']['arch'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['open']['reply'] = 0;

                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['closed']['arcl'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['closed']['arcm'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['closed']['arch'] = 0;
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status']['closed']['reply'] = 0;
                    }
                    if($actionreq_levelkey == 'info')
                    {
                        $map_comms[$contexidkeyvalue]['total_info_count']++;                
                    } else {
                        if($summary_status === 'open')
                        {
                            $map_comms[$contexidkeyvalue]['total_open_action_request_count']++;                
                        }
                        $map_comms[$contexidkeyvalue]['count_by_thread_summary_status'][$summary_status][$actionreq_levelkey]++;
                    }
                }
            }
            
            
//DebugHelper::showNeatMarkup(array('$contextid_selector'=>$contextid_selector, '$actionitems'=>$actionitems, '$map_comms'=>$map_comms),"LOOK comm summary for $contextname with keyname=$contexidkeyname");            

            return $map_comms;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getCommThreadSummaryMapTestcaseStepMainSQLBundle($contextid_selector, $only_active=TRUE)
    {
        try
        {
            $bundle = [];
            
            //Are we in any context?
            if(!empty($contextid_selector))
            {
                $idprefix = 'testcasestep';
                $link_steps = TRUE;
                $main_comm_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $map_stepscom_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                $steps_tablename = DatabaseNamesHelper::$m_testcasestep_tablename;

                if(is_array($contextid_selector))
                {
                    //Multiple values
                    $inTxt = " IN (" . implode(",", $contextid_selector).")";
                } else {
                    //Single value
                    $inTxt = " = $contextid_selector";
                }

                $map_thread_root_comid2stepid = [];
                $map_thread_root_stepid2comid = [];

                //Construct the query
                $where_ar = [];

                //First get all the root level step mappings
                $root_stepmap_sql = "SELECT comid, testcasestepid "
                        . "FROM $map_stepscom_tablename stepcom "
                        . "LEFT JOIN $steps_tablename stepdet ON stepdet.id=stepcom.testcasestepid "
                        . "LEFT JOIN $main_comm_tablename maincom ON maincom.testcaseid=stepdet.testcaseid "
                        . "WHERE maincom.testcaseid $inTxt and maincom.parent_comid IS NULL";
                //DebugHelper::showNeatMarkup($root_stepmap_sql);
                $root_stepmap_result = db_query($root_stepmap_sql);
                while($record = $root_stepmap_result->fetchAssoc()) 
                {
                    $comid = $record['comid'];
                    $testcasestepid = $record['testcasestepid'];
                    if(!isset($map_thread_root_comid2stepid[$comid]))
                    {
                        $map_thread_root_comid2stepid[$comid] = [];
                    }
                    if(!isset($map_thread_root_stepid2comid[$testcasestepid]))
                    {
                        $map_thread_root_stepid2comid[$testcasestepid] = [];
                    }
                    $map_thread_root_comid2stepid[$comid][$testcasestepid] = $testcasestepid;
                    $map_thread_root_stepid2comid[$testcasestepid][$comid] = $comid;
                }

                //Now, start the processing
                $where_ar[] = "gc.testcaseid $inTxt"; 
                $groupbyfields = " gc.id ";
                $orderby_txt = "gc.id ";
                $jointable_txt = " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                    . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";

                $mainfields = " gc.id as comid, gc.parent_comid, gc.testcaseid, gc.status_cd_at_time_of_com,"
                        . " gc.title_tx, gc.body_tx, gc.owner_personid,"
                        . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                        . " gc.active_yn, "
                        . " gc.updated_dt, gc.created_dt";

                $where_txt = implode(" and ", $where_ar);
                $main_sql = "SELECT $mainfields,"
                        . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                        . " FROM $main_comm_tablename gc"
                        . " $jointable_txt";
                $main_sql .= " WHERE $where_txt";
                if($only_active)
                {
                    $main_sql .= " AND gc.active_yn=1";
                }
                $main_sql .= " GROUP BY $groupbyfields";
                $main_sql .= " ORDER BY $orderby_txt";

                $rootmap = [];
                $raw_main_rows = [];
                $final_main_rows = [];
                $main_result = db_query($main_sql);
                while($record = $main_result->fetchAssoc()) 
                {
                    $comid = $record['comid'];
                    $parent_comid = $record['parent_comid'];
                    if(empty($parent_comid))
                    {
                        $rootmap[$comid] = $comid;
                    } else {
                        if(array_key_exists($parent_comid, $rootmap))
                        {
                            $rootmap[$comid] = $rootmap[$parent_comid];    
                        } else {
                            //Parent is the root of a thread
                            $rootmap[$comid] = $parent_comid;    
                        }
                    }
                    $root_comid = $rootmap[$comid];
                    $record['root_comid'] = $root_comid;
                    $raw_main_rows[$comid] = $record;
                }

                $nostep_rows = [];
                foreach($raw_main_rows as $comid=>$record)
                {
                    $comid = $record['comid'];
                    $root_comid = $record['root_comid'];
                    $stepid_ar = isset($map_thread_root_comid2stepid[$root_comid]) ? $map_thread_root_comid2stepid[$root_comid] : [];
                    if(count($stepid_ar) === 0)
                    {
                        $record['testcasestepid'] = NULL;
                        $nostep_rows[] = $record;
                    } else {
                        foreach($stepid_ar as $onestepid)
                        {
                            $record['testcasestepid'] = $onestepid;
                            $final_main_rows[] = $record;
                        }
                    }
                }
                foreach($nostep_rows as $record)
                {
                    $record['testcasestepid'] = NULL;
                    $final_main_rows[] = $record;
                }

                $bundle['main_sql'] = $main_sql;
                $bundle['main_rows'] = $final_main_rows;
                $bundle['map_thread_root']['comid2rootcomid'] = $rootmap;
                $bundle['map_thread_root']['comid2stepid'] = $map_thread_root_comid2stepid;
                $bundle['map_thread_root']['stepid2comid'] = $map_thread_root_stepid2comid;
            }
            
//DebugHelper::showNeatMarkup($bundle,"LOOK at STEP BUNDLE");
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return all comments for one project
     */
    public function getProjectCommunications($projectid,$only_active=TRUE)
    {
        return $this->getCommunications('project', $projectid, $only_active);
    }

    /**
     * Return all comments for one workitem
     */
    public function getWorkitemCommunications($goalid,$only_active=TRUE)
    {
        return $this->getCommunications('workitem', $goalid, $only_active);
    }

    /**
     * Return all comments for one testcase
     */
    public function getTestcaseCommunications($testcaseid,$only_active=TRUE)
    {
        return $this->getCommunications('testcase', $testcaseid, $only_active);
    }

    /**
     * Return all comments for one goal
     */
    public function getSprintCommunications($sprintid,$only_active=TRUE)
    {
        return $this->getCommunications('sprint', $sprintid, $only_active);
    }

    /**
     * Return all comments for one context item
     * @param $contextitemid can be an array of values
     */
    private function getCommunications($contextname,$contextitemid,$only_active=TRUE)
    {
        try
        {
            $link_steps = FALSE;
            if($contextname == 'project')
            {
                $idprefix = $contextname;
                $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
            } else
            if($contextname == 'sprint')
            {
                $idprefix = $contextname;
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
            } else
            if($contextname == 'workitem' || $contextname == 'goal' || $contextname == 'task')
            {
                $idprefix = 'workitem';
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
            } else
            if($contextname == 'testcase' || $contextname == 'goal' || $contextname == 'task')
            {
                $idprefix = 'testcase';
                $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                $link_steps = TRUE;
                //$steps_tablename = DatabaseNamesHelper::$m_testcasestep_tablename;
            } else {
                throw new \Exception("Did not recognize context name of '$contextname'");
            }
            
            $where = [];
            if(!is_array($contextitemid))
            {
                $where[] = "{$idprefix}id=$contextitemid";
            } else {
                $where[] = "{$idprefix}id IN (" . implode(",",$contextitemid) . ")";
            }
            $where_txt = implode(" and ", $where);
            $bundle = array();
            $mainfields = " gc.id, parent_comid, gc.{$idprefix}id, gc.status_cd_at_time_of_com,"
                    . " gc.title_tx, gc.body_tx, gc.owner_personid,"
                    . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn,a.happy_yn,"
                    . " gc.active_yn, "
                    . " p.first_nm, p.last_nm, p.shortname, "
                    . " gc.updated_dt, gc.created_dt";
            $groupbyfields = " gc.id, parent_comid, p.shortname ";
            $sSQL = "SELECT $mainfields,"
                    . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                    . " FROM $main_tablename gc"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=gc.owner_personid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                    . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
            $sSQL .= " WHERE $where_txt";
            if($only_active)
            {
                $sSQL .= " AND gc.active_yn=1";
            }
            $sSQL .= " GROUP BY $groupbyfields";
            $sSQL .= " ORDER BY id";
            
            $result = db_query($sSQL);
            
            $parentid=NULL;
            $action_requested_concern=0;
            $action_reply_cd=NULL;
            $actionitems = array(); //Actually a MAP of RESOLUTIONS to ACTIONREQUESTS
            $thelist = array();
            $nestingmap=array();
            $rootmap=array();
            $maxid = 0;
            $max_indent = 0;
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $parentid = $record['parent_comid'];
                $action_requested_concern = $record['action_requested_concern'];
                $action_reply_cd = $record['action_reply_cd'];
                $terminal_yn = $record['terminal_yn'];
                $happy_yn = $record['happy_yn'];
                $attachment_count = $record['attachment_count'];
                $attachment_most_recent_dt = $record['attachment_most_recent_dt'];
                $author_info = array(
                    'first_nm'=>$record['first_nm'],
                    'last_nm'=>$record['last_nm'],
                    'shortname'=>$record['shortname'],
                    'personid'=>$record['owner_personid'],
                );
                if(empty($parentid))
                {
                    $indentlevel = 0;
                    $rootmap[$id] = $id;
                } else {
                    if(array_key_exists($parentid, $nestingmap))
                    {
                        $indentlevel = $nestingmap[$parentid] + 1;
                    } else {
                        $indentlevel = 1;
                    }
                    $nestingmap[$id] = $indentlevel;
                    if(array_key_exists($parentid, $rootmap))
                    {
                        $rootmap[$id] = $rootmap[$parentid];    
                    } else {
                        //Parent is the root of a thread
                        $rootmap[$id] = $parentid;    
                    }
                    if($max_indent < $indentlevel)
                    {
                        $max_indent = $indentlevel;
                    }
                }
                $root_comid = $rootmap[$id];
                $onecomment = array();
                $onecomment["{$idprefix}id"] = $contextitemid;
                $onecomment['id'] = $id;
                $onecomment['root_comid'] = $root_comid;
                $onecomment['parent_comid'] = $parentid;
                $onecomment['author_info'] = $author_info;
                $onecomment['title_tx'] = $record['title_tx'];
                $onecomment['body_tx'] = $record['body_tx'];
                $onecomment['status_cd_at_time_of_com'] = $record['status_cd_at_time_of_com'];
                $onecomment['terminal_yn'] = $terminal_yn;
                $onecomment['happy_yn'] = $happy_yn;
                $onecomment['created_dt'] = $record['created_dt'];
                $onecomment['updated_dt'] = $record['updated_dt'];
                $onecomment['attachment_count'] = $attachment_count;
                $onecomment['attachment_most_recent_dt'] = $attachment_most_recent_dt;
                $onecomment['action_requested_concern'] = $action_requested_concern; 
                $onecomment['action_requested_concern_tx'] = $this->getActionImportanceCategoryName($action_requested_concern); 
                $onecomment['action_reply_cd'] = $action_reply_cd;
                $onecomment['indentlevel'] = $indentlevel;
                $onecomment['has_replies'] = FALSE; //Initialize as false
                if(empty($parentid))
                {
                    $onecomment['parent_chain'] = NULL;
                } else {
                    $parent_level = $indentlevel-1;
                    $parent_chain = array($parent_level=>$parentid);
                    while($parent_level > 0)
                    {
                        $parent_level-=1;
                        $parent_info = $thelist[$parentid];
                        $pi_id = $parent_info['parent_comid'];
                        $parent_chain[$parent_level] = $pi_id;
                    }
                    $onecomment['parent_chain'] = $parent_chain;
                }
                if($action_requested_concern > 0)
                {
                    $actionitems[$id] = array();
                }
                if(!empty($action_reply_cd) && $terminal_yn==1)
                {
                    //Mark parent as resolved
                    $actionitems[$parentid][$id] = $action_reply_cd;
                }
                $thelist[$id] = $onecomment;
                if(!empty($parentid))
                {
                    $thelist[$parentid]['has_replies'] = TRUE;
                }
                if($maxid < $id)
                {
                    $maxid = $id;
                }
            }
            
            //Mark each message with the thread summary status.
            foreach($thelist as $comid=>$detail)
            {
                $root_comid = $detail['root_comid'];
                if(empty($actionitems[$root_comid]))
                {
                    $summary_status = "open";
                } else {
                    $summary_status = "closed";
                }
                $thelist[$comid]['thread_summary_status'] = $summary_status;
            }
            
            //Figure out the natural ordering
            $naturalorder_ar = [];
            if(count($thelist)>0)
            {
                $temp_ar = [];
                foreach($thelist as $cid=>$detail)
                {
                    $onerow = array();
                    $pc = $detail['parent_chain'];
                    if(!empty($pc))
                    {
                        $parentlevels = count($pc);
                        for($i=0;$i<$parentlevels;$i++)
                        {
                            $onerow[] = $pc[$i];
                        }
                    }
                    $onerow[] = $cid;   //Least significant is last in sort cols
                    if(count($onerow) < $max_indent)
                    {
                        //Make sure the array is same size asl all the others
                        for($i=count($onerow);$i<$max_indent;$i++)
                        {
                            $onerow[] = 0;
                        }
                    }
                    $onerow[] = $cid;   //Put the CID at the end of each row too!!!
                    $temp_ar[] = $onerow;
                }
                $dynamic_params_ar = [];
                if($max_indent == 0)
                {
                    foreach($thelist as $cid=>$detail)
                    {
                        $naturalorder_ar[] = $cid;
                    }
                } else {
                    for($i=0;$i<$max_indent;$i++)
                    {
                        $dynamic_params_ar[] = '$sort_cols[' . $i .'], $sort_flags[1]';
                        //$dynamic_params_ar[] = '$sort_cols[' . $i .'], 1';
                    }
                    if(count($dynamic_params_ar)>0)
                    {
                        $dynparams_txt = implode(',',$dynamic_params_ar);
                        $sort_cols=array();
                        foreach($temp_ar as $k=>$v)
                        {
                            for($i=0;$i<$max_indent;$i++)
                            {
                                $sort_cols[$i][$k] = $v[$i];
                            }
                        }
                        $sort_flags = array(4=>SORT_ASC, 3=>SORT_DESC, 1=>SORT_NUMERIC);
                        $dynparams_premerge = explode(',', $dynparams_txt);
                        $dynparams4call = array_merge($dynparams_premerge, array($temp_ar));
                        $eval_sort = 'array_multisort(' .  $dynparams_txt . ',$temp_ar);';
                        try
                        {
                            eval($eval_sort);
                        } catch (\Exception $ex) {
                            throw new \Exception("Failed eval of eval_sort=[$eval_sort] because " . $ex->getMessage(),98765,$ex);
                        }
                        $cid_offset = $max_indent;
                        foreach($temp_ar as $detail)
                        {
                            $cid = $detail[$cid_offset];
                            $naturalorder_ar[] = $cid;
                        }
                    }
                }
            }

            if($link_steps)
            {
                $just_cids = array_keys($thelist);
                if(count($just_cids)==0)
                {
                    $bundle['stepnum2detail'] = [];
                } else {
                    $steps_bundle = $this->getCommunicationTestcaseStepsBundle($just_cids);
                    $map_comid2stepnum = $steps_bundle['map_comid2stepnum'];
                    foreach($just_cids as $cid)
                    {
                        if(!isset($map_comid2stepnum[$cid]))
                        {
                            $thelist[$cid]['linked_steps'] = [];
                        } else {
                            $thelist[$cid]['linked_steps'] = $map_comid2stepnum[$cid];
                        }
                    }
                    $bundle['stepnum2detail'] = $steps_bundle['map_stepnum2detail'];
                }
                
                //Copy the step mapping of the root to all the messages of the thread
                foreach($thelist as $comid=>$detail)
                {
                    $root_comid = $detail['root_comid'];
                    $thelist[$comid]['linked_steps'] = $thelist[$root_comid]['linked_steps'];
                }
            }
            
            //Complete the bundling
            $bundle['maxid'] = $maxid;
            $bundle['max_indent']=$max_indent;
            $bundle['comments'] = $thelist;
            $bundle['actionitems'] = $actionitems;
            $bundle['naturalorder'] = $naturalorder_ar;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get all the communications in a project after applying an optional filter
     * @param type $relevant_projectids this can be an array or a single value
     * @param type $filter_bundle
     * @return type
     * @throws \Exception
     */
    public function getCommunicationsInProjectsFilteredBundle($relevant_projectids, $filter_bundle=NULL)
    {
        try
        {
            $finalbundle = [];
            $contextname_ar = array('project','sprint','workitem','testcase');
            foreach($contextname_ar as $contextname)
            {
                $bundle = [];
                if($contextname == 'project')
                {
                    $idprefix = $contextname;
                    $mainidname = "projectid";
                    $idproject = 'id';
                    $itemnamefield = NULL;
                    $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                    $subject_tablename = DatabaseNamesHelper::$m_project_tablename;
                    $subject_tags_tablename = DatabaseNamesHelper::$m_map_tag2project_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
                } else
                if($contextname == 'sprint')
                {
                    $idprefix = $contextname;
                    $mainidname = "sprintid";
                    $idproject = 'owner_projectid';
                    $itemnamefield = "title_tx";
                    $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                    $subject_tablename = DatabaseNamesHelper::$m_sprint_tablename;
                    $subject_tags_tablename = DatabaseNamesHelper::$m_map_tag2sprint_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
                } else
                if($contextname == 'testcase')
                {
                    $idprefix = $contextname;
                    $mainidname = "testcaseid";
                    $idproject = 'owner_projectid';
                    $itemnamefield = "testcase_nm";
                    $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                    $subject_tablename = DatabaseNamesHelper::$m_testcase_tablename;
                    $subject_tags_tablename = DatabaseNamesHelper::$m_map_tag2testcase_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                } else
                if($contextname == 'workitem' || $contextname == 'goal' || $contextname == 'task')
                {
                    $idprefix = 'workitem';
                    $mainidname = "workitemid";
                    $idproject = 'owner_projectid';
                    $itemnamefield = "workitem_nm";
                    $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                    $subject_tablename = DatabaseNamesHelper::$m_workitem_tablename;
                    $subject_tags_tablename = DatabaseNamesHelper::$m_map_tag2workitem_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
                } else {
                    throw new \Exception("Did not recognize context name of '$contextname'");
                }

                $where = [];
                $where[] = "gc.active_yn=1";
                if(!is_array($relevant_projectids))
                {
                    $project_filter = "st.$idproject=$relevant_projectids";
                    $where[] = $project_filter;
                } else {
                    $project_filter = "st.$idproject IN (" . implode(",",$relevant_projectids) . ")";
                    $where[] = $project_filter;
                }
                
                $filter_map_comids = [];
                $filter_list_comterms = [];
                $filter_list_workitemname_terms = [];
                $filter_list_workitemname_tags = [];
                $filter_matchedbytag = NULL;   //Means no filter on this
                $context_personid = NULL;
                $filter_ownergroup = 'any';
                $filter_authorgroup = 'any';
                $filter_statusgroup = 'any';
                $tag_map_subjectids = NULL; //IMPORTANT MUST STAY NULL IF NOT FILTERING TAGS!
                if(is_array($filter_bundle))
                {
                    if(!empty($filter_bundle['start_dt']))
                    {
                        $thedate = trim($filter_bundle['start_dt']);
                        $where[] = "gc.updated_dt>='$thedate'";
                    }
                    if(!empty($filter_bundle['end_dt']))
                    {
                        $thedate = trim($filter_bundle['end_dt']);
                        $where[] = "gc.updated_dt<='$thedate'";
                    }
                    if(!empty($filter_bundle['statusgroup']))
                    {
                        $filter_statusgroup = $filter_bundle['statusgroup'];
                    }
                    if(!empty($filter_bundle['context_personid']))
                    {
                        $context_personid = $filter_bundle['context_personid'];
                    }
                    if(!empty($filter_bundle['ownergroup']))
                    {
                        $filter_ownergroup = $filter_bundle['ownergroup'];
                        if($filter_ownergroup == 'you')
                        {
                            $where[] = "st.owner_personid=$context_personid";
                        } else
                        if($filter_ownergroup == 'notyou')
                        {
                            $where[] = "st.owner_personid<>$context_personid";
                        }
                    }
                    if(!empty($filter_bundle['authorgroup']))
                    {
                        $filter_authorgroup = $filter_bundle['authorgroup'];
                        if($filter_authorgroup == 'you')
                        {
                            $where[] = "gc.owner_personid=$context_personid";
                        } else
                        if($filter_authorgroup == 'notyou')
                        {
                            $where[] = "gc.owner_personid<>$context_personid";
                        }
                    }
                    //gcq_workitem_tag_matchtext
                    if(!empty($filter_bundle['workitem_tag_matchtext']))
                    {
                        $match_tag_ar = explode(",", $filter_bundle['workitem_tag_matchtext']);
                        foreach($match_tag_ar as $tag)
                        {
                            if(!empty($tag))
                            {
                                $cleantag = trim($tag);
                                $filter_list_workitemname_tags[] = $cleantag;
                            }
                        }
                        if(count($filter_list_workitemname_tags)>0)
                        {
                            $allmatches = [];
                            foreach($filter_list_workitemname_tags as $cleantag)
                            {
                                $onematch = "stt.tag_tx like '%$cleantag%'";
                                $allmatches[] = $onematch;
                            }
                            $tag_where = implode(" or ", $allmatches);
                            $tag_sql = "SELECT DISTINCT $mainidname"
                                    . " FROM $subject_tags_tablename stt"
                                    . " LEFT JOIN $subject_tablename st ON st.id=stt.{$mainidname}"
                                    . " WHERE $project_filter AND ($tag_where)";
                            $result = db_query($tag_sql);
                            $filter_matchedbytag = $result->fetchCol();
                            if(empty($filter_matchedbytag) || count($filter_matchedbytag)<1)
                            {
                                //Don't bother with this one, there are no matching tags
                                continue;
                            }
                            $tag_map_subjectids=[];
                            foreach($filter_matchedbytag as $subjectid)
                            {
                                $tag_map_subjectids[$subjectid] = $subjectid;
                            }
                            $where[] = "(gc.{$mainidname} IN (".implode(",", $filter_matchedbytag)."))";
                        }
                    }
                    //gcq_workitem_namematchtext
                    if(!empty($filter_bundle['workitem_namematchtext']) && !empty($itemnamefield))
                    {
                        $namematchtext_ar = explode(",", $filter_bundle['workitem_namematchtext']);
                        foreach($namematchtext_ar as $term)
                        {
                            if(!empty($term))
                            {
                                $cleanterm = trim($term);
                                $filter_list_workitemname_terms[] = $cleanterm;
                            }
                        }
                        if(count($filter_list_workitemname_terms)>0)
                        {
                            $allmatches = [];
                            foreach($filter_list_workitemname_terms as $cleanterm)
                            {
                                $onematch = "st.{$itemnamefield} like '%$cleanterm%'";
                                $allmatches[] = $onematch;
                            }
                            $where[] = implode(" and ", $allmatches);
                        }
                    }
                    
                    //gcq_comm_matchtext
                    if(!empty($filter_bundle['comm_matchtext']))
                    {
                        $comtermsar = explode(",", $filter_bundle['comm_matchtext']);
                        foreach($comtermsar as $comterm)
                        {
                            if(!empty($comterm))
                            {
                                $cleanterm = trim($comterm);
                                $filter_list_comterms[$cleancomid] = $cleanterm;
                            }
                        }
                        if(count($filter_list_comterms)>0)
                        {
                            $allmatches = [];
                            foreach($filter_list_comterms as $cleanterm)
                            {
                                $onematch = "(gc.title_tx like '%$cleanterm%' or gc.body_tx like '%$cleanterm%')";
                                $allmatches[] = $onematch;
                            }
                            $where[] = implode(" and ", $allmatches);
                        }
                    }
                    
                    if(!empty($filter_bundle['comids']))
                    {
                        $comidsar = explode(",", $filter_bundle['comids']);
                        foreach($comidsar as $comid)
                        {
                            if(is_numeric($comid))
                            {
                                $cleancomid = intval($comid);
                                $filter_map_comids[$cleancomid] = $cleancomid;
                            }
                        }
                        $justids = array_keys($filter_map_comids);
                        if(count($justids)>0)
                        {
                            $where[] = "gc.id in (" . implode(",", $justids) . ")";
                        }
                    }
                }
                $where_txt = implode(" and ", $where);
                $mainfields = " gc.id as comid, parent_comid, gc.{$mainidname}, gc.status_cd_at_time_of_com,"
                        . " gc.owner_personid as authorid, st.owner_personid as subjectownerid, gc.title_tx, gc.body_tx,"
                        . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                        . " gc.active_yn, st.id,"
                        . " p.first_nm, p.last_nm, p.shortname, "
                        . " gc.updated_dt, gc.created_dt," . ($itemnamefield==NULL?"''":"st.$itemnamefield") . " as itemname";
                $groupfields = " gc.id, parent_comid, st.id, p.shortname";
                $sSQL = "SELECT $mainfields, 1 as mainquery,"
                        . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                        . " FROM $main_tablename gc"
                        . " LEFT JOIN $subject_tablename st on st.id=gc.{$mainidname}"
                        . " LEFT JOIN $subject_tags_tablename stt on stt.{$mainidname}=gc.{$mainidname}"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=gc.owner_personid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                        . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                $sSQL .= " WHERE $where_txt";
                $sSQL .= " GROUP BY $groupfields";
                $sSQL .= " ORDER BY gc.id";
//drupal_set_message("LOOK $contextname TOP sql=$sSQL");
                $result = db_query($sSQL);

                $raw_records = [];
                $parentid_set = [];
                $main_query_comids = [];
                while($record = $result->fetchAssoc()) 
                {
                    $comid = $record['comid'];
                    $main_query_comids[$comid] = $comid;
                    $parentid = $record['parent_comid'];
                    $parentid_set[$parentid] = $parentid;
                    $raw_records[$comid] = $record;
                }
                $missing_comids = [];
                foreach(array_keys($parentid_set) as $parentid)
                {
                    if(!empty($parentid) && !array_key_exists($parentid, $raw_records))
                    {
                        $missing_comids[$parentid] = $parentid;   
                    }
                }
                
                //Run queries until we have everything down to the thread start
                $extraqueryruns = 0;
                while(count($missing_comids)>0)
                {
                    //Now get any missing com records needed for computing statistics
                    $extraqueryruns++;
                    $where = [];
                    $justids = array_keys($missing_comids);
                    $where[] = "gc.id in (" . implode(",", $justids) . ")";
                    if(!is_array($relevant_projectids))
                    {
                        $where[] = "st.$idproject=$relevant_projectids";
                    } else {
                        $where[] = "st.$idproject IN (" . implode(",",$relevant_projectids) . ")";
                    }
                    $where_txt = implode(" and ", $where);
                    $sSQL = "SELECT $mainfields, 0 as mainquery,"
                            . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                            . " FROM $main_tablename gc"
                            . " LEFT JOIN $subject_tablename st on st.id=gc.{$mainidname}"
                            . " LEFT JOIN $subject_tags_tablename stt on stt.{$mainidname}=gc.{$mainidname}"
                            . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=gc.owner_personid"
                            . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                            . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                            . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                    $sSQL .= " WHERE $where_txt";
                    $sSQL .= " GROUP BY $groupfields";
                    $sSQL .= " ORDER BY gc.id";
    //drupal_set_message("LOOK $contextname FOLLOWUP QUERY " . print_r($justids,TRUE) . " >>>>>>>>>>>>> sql=$sSQL");
                    $result = db_query($sSQL);
                    $missing_comids = [];
                    $parentid_set = [];
                    while($record = $result->fetchAssoc()) 
                    {
                        $comid = $record['comid'];
                        $parentid = $record['parent_comid'];
                        $parentid_set[$parentid] = $parentid;
                        $raw_records[$comid] = $record;
                    }
                    foreach(array_keys($parentid_set) as $parentid)
                    {
                        if(!empty($parentid) && !array_key_exists($parentid, $raw_records))
                        {
                            $missing_comids[$parentid] = $parentid;   
                        }
                    }
                }
                
                $action_requested_concern=0;
                $action_reply_cd=NULL;
                $actionitems = array();
                $thelist = [];
                $nestingmap=array();
                $rootmap=array();
                $maxid = 0;
                $max_indent = 0;
                $resultloop=0;
                //while($record = $result->fetchAssoc()) 
                foreach($raw_records as $comid=>$record)
                {
                    $resultloop++;
                    $comid = $record['comid'];
                    $parentid = $record['parent_comid'];
                    $action_requested_concern = $record['action_requested_concern'];
                    $action_reply_cd = $record['action_reply_cd'];
                    $terminal_yn = $record['terminal_yn'];
                    $happy_yn = $record['happy_yn'];
                    $attachment_count = $record['attachment_count'];
                    $attachment_most_recent_dt = $record['attachment_most_recent_dt'];
                    $subjectownerid = $record['st.owner_personid'];
                    $author_info = array(
                        'first_nm'=>$record['first_nm'],
                        'last_nm'=>$record['last_nm'],
                        'shortname'=>$record['shortname'],
                        'personid'=>$record['authorid'],
                    );
                    if(empty($parentid))
                    {
                        $indentlevel = 0;
                        $rootmap[$comid] = $comid;
                    } else {
                        if(array_key_exists($parentid, $nestingmap))
                        {
                            $indentlevel = $nestingmap[$parentid] + 1;
                        } else {
                            $indentlevel = 1;
                        }
                        $nestingmap[$comid] = $indentlevel;
                        if(array_key_exists($parentid, $rootmap))
                        {
                            $rootmap[$comid] = $rootmap[$parentid];    
                        } else {
                            //Parent is the root of a thread
                            $rootmap[$comid] = $parentid;    
                        }
                        if($max_indent < $indentlevel)
                        {
                            $max_indent = $indentlevel;
                        }
                    }
                    $root_comid = $rootmap[$comid];
                    $onecomment = [];
                    $onecomment[$mainidname] = $record[$mainidname];
                    $onecomment['id'] = $comid;
                    $onecomment['root_comid'] = $root_comid;
                    $onecomment['parent_comid'] = $parentid;
                    $onecomment['subjectownerid'] = $subjectownerid;
                    $onecomment['author_info'] = $author_info;
                    $onecomment['title_tx'] = $record['title_tx'];
                    $onecomment['body_tx'] = $record['body_tx'];
                    $onecomment['status_cd_at_time_of_com'] = $record['status_cd_at_time_of_com'];
                    $onecomment['happy_yn'] = $happy_yn;
                    $onecomment['terminal_yn'] = $terminal_yn;
                    $onecomment['created_dt'] = $record['created_dt'];
                    $onecomment['updated_dt'] = $record['updated_dt'];
                    $onecomment['attachment_count'] = $attachment_count;
                    $onecomment['attachment_most_recent_dt'] = $attachment_most_recent_dt;
                    $onecomment['action_requested_concern'] = $action_requested_concern; 
                    $onecomment['action_requested_concern_tx'] = $this->getActionImportanceCategoryName($action_requested_concern); 
                    $onecomment['action_reply_cd'] = $action_reply_cd;
                    $onecomment['indentlevel'] = $indentlevel;
                    $onecomment['has_replies'] = FALSE; //Initialize as false
                    $onecomment['is_waiting'] = ($action_requested_concern > 0);
                    if(empty($parentid))
                    {
                        $onecomment['parent_chain'] = NULL;
                    } else {
                        $parent_level = $indentlevel-1;
                        $parent_chain = array($parent_level=>$parentid);
                        while($parent_level > 0)
                        {
                            $parent_level-=1;
                            //IMPORTANT --- This logic REQUIRES that parent ALWAYS has LOWER ID VALUE!!!!!
                            $parent_info = $thelist[$parentid];
                            $pi_id = $parent_info['parent_comid'];
                            $parent_chain[$parent_level] = $pi_id;
                        }
                        $onecomment['parent_chain'] = $parent_chain;
                    }
                    if($action_requested_concern > 0)
                    {
                        $actionitems[$comid] = [];
                    }
                    if(!empty($action_reply_cd) && $terminal_yn==1)
                    {
                        //Mark parent as resolved
                        $actionitems[$parentid][$comid] = $action_reply_cd;
                    }
                    $thelist[$comid] = $onecomment;
                    if(!empty($parentid))
                    {
                        $thelist[$parentid]['has_replies'] = TRUE;
                    }
                    if($maxid < $comid)
                    {
                        $maxid = $comid;
                    }
                }

                //Figure out the natural ordering and termination status
                $naturalorder_ar = [];
                if(count($thelist)>0)
                {
                    $temp_ar = [];
                    foreach($thelist as $cid=>$detail)
                    {
                        $onerow = [];
                        $root_comid = $detail['root_comid'];
                        $terminal_yn = $detail['terminal_yn'];
                        if($root_comid != $cid)
                        {
                            if($terminal_yn==1)
                            {
                                //Mark thread as resolved
                                $thelist[$root_comid]['is_waiting'] = FALSE;
                            }
                        }
                        $pc = !empty($detail['parent_chain']) ? $detail['parent_chain'] : [];
                        $parentlevels = count($pc);
                        for($i=0;$i<$parentlevels;$i++)
                        {
                            $onerow[] = $pc[$i];
                        }
                        $onerow[] = $cid;   //Least significant is last in sort cols
                        if(count($onerow) < $max_indent)
                        {
                            //Make sure the array is same size asl all the others
                            for($i=count($onerow);$i<$max_indent;$i++)
                            {
                                $onerow[] = 0;
                            }
                        }
                        $onerow[] = $cid;   //Put the CID at the end of each row too!!!
                        $temp_ar[] = $onerow;
                    }
                    $dynamic_params_ar = [];
                    if($max_indent == 0)
                    {
                        foreach($thelist as $cid=>$detail)
                        {
                            $naturalorder_ar[] = $cid;
                        }
                    } else {
                        for($i=0;$i<$max_indent;$i++)
                        {
                            $dynamic_params_ar[] = '$sort_cols[' . $i .'], $sort_flags[1]';
                            //$dynamic_params_ar[] = '$sort_cols[' . $i .'], 1';
                        }
                        if(count($dynamic_params_ar)>0)
                        {
                            $dynparams_txt = implode(',',$dynamic_params_ar);
                            $sort_cols=array();
                            foreach($temp_ar as $k=>$v)
                            {
                                for($i=0;$i<$max_indent;$i++)
                                {
                                    $sort_cols[$i][$k] = $v[$i];
                                }
                            }
                            $sort_flags = array(4=>SORT_ASC, 3=>SORT_DESC, 1=>SORT_NUMERIC);
                            $dynparams_premerge = explode(',', $dynparams_txt);
                            $dynparams4call = array_merge($dynparams_premerge, array($temp_ar));
                            $eval_sort = 'array_multisort(' .  $dynparams_txt . ',$temp_ar);';
                            try
                            {
                                eval($eval_sort);
                            } catch (\Exception $ex) {
                                throw new \Exception("Failed eval of eval_sort=[$eval_sort] because " . $ex->getMessage(),98765,$ex);
                            }
                            $cid_offset = $max_indent;
                            foreach($temp_ar as $detail)
                            {
                                $cid = $detail[$cid_offset];
                                $naturalorder_ar[] = $cid;
                            }
                        }
                    }
                }

                //Remove extra records if we have any.
                if($extraqueryruns>0 || $filter_statusgroup != 'any' || $filter_authorgroup != 'any' || $filter_ownergroup != 'any')
                {
                    //Only keep the ones that match our filter
                    $keepmax = 0;
                    $keeplist = [];
                    $keepnatorder = [];
                    foreach($naturalorder_ar as $comid)
                    {
                        $detail = $thelist[$comid];
                        $subjectid = $detail[$mainidname];
                        //drupal_set_message("LOOK CHECK $comid where context_personid=$context_personid and DETAIL=" . print_r($detail,TRUE));
                        if(!array_key_exists($comid, $main_query_comids))
                        {
                            //Ignore this record
                            continue;
                        }
                        if($tag_map_subjectids != NULL && !array_key_exists($subjectid, $tag_map_subjectids))
                        {
                            //Ignore this record
                            continue;
                        }
                        if($filter_authorgroup == 'you')
                        {
                            if($detail['author_info']['personid'] != $context_personid)
                            {
                                //Ignore this record
                                continue;
                            }
                        } 
                        else if($filter_authorgroup == 'notyou') 
                        {
                            if($detail['author_info']['personid'] == $context_personid)
                            {
                                //Ignore this record
                                continue;
                            }
                        }
                        if($filter_ownergroup == 'you')
                        {
                            if($detail['subjectownerid'] != $context_personid)
                            {
                                //Ignore this record
                                continue;
                            }
                        } 
                        else if($filter_ownergroup == 'notyou') 
                        {
                            if($detail['subjectownerid'] == $context_personid)
                            {
                                //Ignore this record
                                continue;
                            }
                        }
                        if($filter_statusgroup == 'open')
                        {
                            if($detail['is_waiting'] != 1)
                            {
                                //Ignore this record
                                continue;
                            }
                        } 
                        else if($filter_statusgroup == 'closed')
                        {
                            if($detail['is_waiting'] == 1)
                            {
                                //Ignore this record
                                continue;
                            }
                        }
                        $keepnatorder[] = $comid;
                        $keeplist[$comid] = $thelist[$comid];
                    }
                    $maxid = $keepmax;
                    $thelist = $keeplist;
                    $naturalorder_ar = $keepnatorder;
                }

                //Complete the bundling
                $bundle['maxid'] = $maxid;
                $bundle['max_indent']=$max_indent;
                $bundle['comments'] = $thelist;
                $bundle['actionitems'] = $actionitems;
                $bundle['naturalorder'] = $naturalorder_ar;
                $finalbundle[$contextname] = $bundle;
            }
            return $finalbundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return comment summary for workitem
     */
    public function getCommunicationSummaryBundleByWorkitemInProject($relevant_projectids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByThing('workitem', $relevant_projectids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }    
    
    /**
     * Return comment summary for workitem
     */
    public function getCommentSummaryBundleByWorkitem($relevant_wids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByThing('workitem', $relevant_wids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }    
    
    /**
     * Return comment summary for sprints
     */
    public function getCommentSummaryBundleBySprint($relevant_sprintids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByThing('sprint', $relevant_sprintids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }    

    /**
     * Return all comment counts for one thing type
     * organize by thingid
     */
    private function getCommunicationSummaryBundleByThing($thingname, $relevant_thingids
            ,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        try
        {
            if(!is_array($relevant_thingids))
            {
                throw new \Exception("The relevant {$thingname} ids must in in an array!");
            }
            if($thingname == 'workitem')
            {
                $thingid_fieldname="{$thingname}id";
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
            } else
            if($thingname == 'sprint')
            {
                $thingid_fieldname="{$thingname}id";
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
            } else
            if($thingname == 'project')
            {
                throw new \Exception("TODO project comments");
            } else {
                throw new \Exception("Did not recognize context name of '$thingname'");
            }
            
            $bundle = array();
            $map_open_request_summary = array(); 
            $map_open_request_detail = array(); 
            $actionitems = array();
            $thelist = array();
            $nestingmap=array();
            $rootmap=array();
            $maxid = 0;
            $max_indent = 0;
            $map_count_waiting_on_resolution = array();
            $count_direct_replies = 0;                  //Replies to relevant comments
            $count_direct_replies_waiting_on_resolution = 0;
            $count_replies_in_relevant_branches = 0;    //Root of a branch is a comid

            $relevant_owned_comids = array();
            if($relevant_branch_comids==NULL)
            {
                $relevant_branch_comids = array();
            }
            if($relevant_personids==NULL)
            {
                $relevant_personids = array();
            }
            $today_dt = date("Y-m-d", time());
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $relevant_thingids_chunks = array_chunk($relevant_thingids, $MAX_INCLAUSE_CHUNK_SIZE);
            $found_contextids = array();
            foreach($relevant_thingids_chunks as $onechunk)
            {
                $sIN = "$thingid_fieldname IN (".implode(',',$onechunk).')';
                $mainfields = " gc.id, parent_comid, gc.{$thingid_fieldname}, gc.status_cd_at_time_of_com,"
                        . " gc.owner_personid,"
                        . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                        . " gc.active_yn, "
                        . " gc.updated_dt, gc.created_dt";
                $sSQL = "SELECT $mainfields,"
                        . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                        . " FROM $main_tablename gc"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                        . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                $sSQL .= " WHERE $sIN";
                if($only_active)
                {
                    $sSQL .= " AND gc.active_yn=1";
                }
                $sSQL .= " GROUP BY $mainfields";
                $sSQL .= " ORDER BY id";

                $result = db_query($sSQL);

                $parentid=NULL;
                $action_requested_concern=0;
                $action_reply_cd=NULL;
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $contextitemid = $record[$thingid_fieldname];
                    $found_contextids[] = $contextitemid;
                    $parentid = $record['parent_comid'];
                    $action_requested_concern = $record['action_requested_concern'];
                    $action_reply_cd = $record['action_reply_cd'];
                    $terminal_yn = $record['terminal_yn'];
                    $happy_yn = $record['happy_yn'];
                    $owner_personid=$record['owner_personid'];
                    if(array_key_exists($parentid, $relevant_branch_comids))
                    {
                        $count_replies_in_relevant_branches++;
                        if(array_key_exists($parentid, $relevant_owned_comids))
                        {
                            $count_direct_replies++;
                        }
                    }
                    if(array_key_exists($owner_personid, $relevant_personids))
                    {
                        //This becomes a relevant branch now
                        $relevant_owned_comids[$id] = $id;
                        $relevant_branch_comids[$id] = $id;
                    }
                    //$active_yn = $record['active_yn'];
                    $attachment_count = $record['attachment_count'];
                    $attachment_most_recent_dt = $record['attachment_most_recent_dt'];
                    if(empty($parentid))
                    {
                        $indentlevel = 0;
                        $rootmap[$id] = $id;
                    } else {
                        if(array_key_exists($parentid, $nestingmap))
                        {
                            $indentlevel = $nestingmap[$parentid] + 1;
                        } else {
                            $indentlevel = 1;
                        }
                        $nestingmap[$id] = $indentlevel;
                        if(array_key_exists($parentid, $rootmap))
                        {
                            $rootmap[$id] = $rootmap[$parentid];    
                        } else {
                            //Parent is the root of a thread
                            $rootmap[$id] = $parentid;    
                        }
                        if($max_indent < $indentlevel)
                        {
                            $max_indent = $indentlevel;
                        }
                    }
                    $root_comid = $rootmap[$id];
                    $onecomment = array();
                    $onecomment[$thingid_fieldname] = $relevant_thingids;
                    $onecomment['id'] = $id;
                    $onecomment[$thingid_fieldname] = $contextitemid;
                    $onecomment['owner_personid'] = $owner_personid;
                    $onecomment['root_comid'] = $root_comid;
                    $onecomment['parent_comid'] = $parentid;
                    $onecomment['status_cd_at_time_of_com'] = $record['status_cd_at_time_of_com'];
                    $onecomment['happy_yn'] = $happy_yn;
                    $onecomment['created_dt'] = $record['created_dt'];
                    $onecomment['updated_dt'] = $record['updated_dt'];
                    $onecomment['attachment_count'] = $attachment_count;
                    $onecomment['attachment_most_recent_dt'] = $attachment_most_recent_dt;
                    $onecomment['action_requested_concern'] = $action_requested_concern; 
                    $onecomment['action_reply_cd'] = $action_reply_cd;
                    $onecomment['indentlevel'] = $indentlevel;
                    $onecomment['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($record['updated_dt'], $today_dt);

                    $onecomment['has_replies'] = FALSE; //Initialize as false
                    if($action_requested_concern > 0)
                    {
                        $actionitems[$id] = array();
                    }
                    if(!empty($action_reply_cd) && $terminal_yn==1)
                    {
                        //Mark parent as resolved
                        $actionitems[$parentid][$id] = $action_reply_cd;
                    }
                    $thelist[$id] = $onecomment;
                    if(!empty($parentid))
                    {
                        $thelist[$parentid]['has_replies'] = TRUE;
                    }
                    if($maxid < $id)
                    {
                        $maxid = $id;
                    }
                }

                //Figure the action item completion status
                $map_contextid2comid = array();
                foreach($thelist as $cid=>$onecomment)
                {
                    $action_requested_concern = $onecomment['action_requested_concern'];
                    $indentlevel = $onecomment['indentlevel'];
                    $action_requested_yn = ($action_requested_concern > 0) ? 1 : 0;
                    $owner_personid = $onecomment['owner_personid'];
                    $relevant_personids[$owner_personid]=$owner_personid;

                    if($action_requested_yn == 1)
                    {
                        if(!array_key_exists($cid, $actionitems))
                        {
                            $is_open = TRUE;
                        } else {
                            $resolutions = $actionitems[$cid];
                            if(count($resolutions) == 0)
                            {
                                $is_open = TRUE;
                            } else {
                                $is_open = FALSE;
                            }
                        }
                        if($is_open)
                        {
                            if(!array_key_exists($action_requested_concern, $map_count_waiting_on_resolution))
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern] 
                                        = array('new'=>0,
                                            'older_than2days'=>0,
                                            'older_than7days'=>0,
                                            'older_than14days'=>0,
                                            'older_than30days'=>0);   
                            }
                            $tmp = $onecomment[$thingid_fieldname];
                            $map_contextid2comid[$tmp] = $cid;
                            $thingid = $onecomment[$thingid_fieldname];
                            if(!array_key_exists($thingid, $map_open_request_detail))
                            {
                                $map_open_request_detail[$thingid] = array();   
                                $map_open_request_summary[$thingid] = array();                             } 
                            $map_open_request_detail[$thingid][$action_requested_concern][$cid] = array(
                                $thingid_fieldname=>$onecomment[$thingid_fieldname],
                                'owner_personid'=>$onecomment['owner_personid'],
                                'has_replies'=>$onecomment['has_replies'],
                                'created_dt'=>$onecomment['created_dt'],
                                'updated_dt'=>$onecomment['updated_dt'],
                                'age_days'=>$onecomment['age_days'],
                                );
                            if(empty($map_open_request_summary[$thingid][$action_requested_concern]['count']))
                            {
                                $map_open_request_summary[$thingid][$action_requested_concern]['count'] = 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than30days'] = 0;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than14days'] = 0;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than7days'] = 0;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than2days'] = 0;
                                $map_open_request_summary[$thingid][$action_requested_concern]['new'] = 0;
                            } else {
                                $map_open_request_summary[$thingid][$action_requested_concern]['count'] += 1;
                            }
                            if($onecomment['age_days'] > 30)
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than30days'] += 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than30days'] += 1;
                            } else if($onecomment['age_days'] > 14) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than14days'] += 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than14days'] += 1;
                            } else if($onecomment['age_days'] > 7) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than7days'] += 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than7days'] += 1;
                            } else if($onecomment['age_days'] > 2) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than2days'] += 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['older_than2days'] += 1;
                            } else {
                                $map_count_waiting_on_resolution[$action_requested_concern]['new'] += 1;
                                $map_open_request_summary[$thingid][$action_requested_concern]['new'] += 1;
                            }
                        }
                        $parentid = $onecomment['parent_comid'];
                        if(array_key_exists($parentid, $relevant_branch_comids))
                        {
                            if(array_key_exists($parentid, $relevant_owned_comids))
                            {
                                $count_direct_replies_waiting_on_resolution++;
                            }
                        }
                    }
                }
            }

            //Complete the bundling
            $bundle['maxid'] = $maxid;
            $bundle['max_indent']=$max_indent;
            $bundle['relevant_branch_comids'] = $relevant_branch_comids;
            $bundle['relevant_personids'] = $relevant_personids;
            $bundle['map_open_request_summary'] = $map_open_request_summary;
            $bundle['map_open_request_detail'] = $map_open_request_detail;
            $bundle['map_count_waiting_on_resolution'] = $map_count_waiting_on_resolution;
            $bundle['count_direct_replies']=$count_direct_replies;
            $bundle['count_direct_replies_waiting_on_resolution'] = $count_direct_replies_waiting_on_resolution;
            $bundle['count_replies_in_relevant_branches'] = $count_replies_in_relevant_branches;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a communication summary for one or more projects
     */
    public function getCommunicationSummaryBundleForProject($relevant_projectids
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        try
        {
            $only_active = 1;
            if(empty($relevant_projectids))
            {
                throw new \Exception("The relevant project ids must in in an array!");
            }
            if(!is_array($relevant_projectids))
            {
                //Assume we were provided just oneproject id
                $relevant_projectids = array($relevant_projectids);
            }
            $map_action_status_by_code = $this->getActionStatusByCode();
            $thingnames = array('project', 'workitem', 'sprint', 'testcase');
            $bundle = [];
            $map_open_request_summary = []; 
            $map_open_request_detail = []; 
            $actionitems = [];
            $nestingmap=array();
            $rootmap=array();
            $thelist_map = [];
            $itemname_lookup = [];
            foreach($thingnames as $thingname)
            {
                $thelist = [];
                $thelist_map[$thingname] = [];
                $map_open_request_summary[$thingname] = [];
                if($thingname == 'project')
                {
                    $thingid_fieldname="{$thingname}id";
                    $owner_projectidfieldname = "id";
                    $subject_tablename = DatabaseNamesHelper::$m_project_tablename;
                    $comm_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
                    $itemname_expr = "id";  //TODO
                } else
                if($thingname == 'workitem')
                {
                    $thingid_fieldname="{$thingname}id";
                    $owner_projectidfieldname="owner_projectid";
                    $subject_tablename = DatabaseNamesHelper::$m_workitem_tablename;
                    $comm_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
                    $itemname_expr = "m.workitem_nm";
                } else
                if($thingname == 'sprint')
                {
                    $thingid_fieldname="{$thingname}id";
                    $owner_projectidfieldname="owner_projectid";
                    $subject_tablename = DatabaseNamesHelper::$m_sprint_tablename;
                    $comm_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
                    $itemname_expr = "COALESCE(title_tx,m.iteration_ct)";
                } else 
                if($thingname == 'testcase')
                {
                    $thingid_fieldname="{$thingname}id";
                    $owner_projectidfieldname="owner_projectid";
                    $subject_tablename = DatabaseNamesHelper::$m_testcase_tablename;
                    $comm_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                    $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                    $itemname_expr = "testcase_nm";
                } else {
                    throw new \Exception("Did not recognize context name of '$thingname'");
                }
                $maxid = 0;
                $max_indent = 0;
                $map_count_waiting_on_resolution = array();
                $count_direct_replies = 0;                  //Replies to relevant comments
                $count_direct_replies_waiting_on_resolution = 0;
                $count_replies_in_relevant_branches = 0;    //Root of a branch is a comid

                $relevant_owned_comids = array();
                if($relevant_branch_comids==NULL)
                {
                    $relevant_branch_comids = array();
                }
                if($relevant_personids==NULL)
                {
                    $relevant_personids = array();
                }
                $today_dt = date("Y-m-d", time());
                $MAX_INCLAUSE_CHUNK_SIZE = 100;
                $relevant_thingids_chunks = array_chunk($relevant_projectids, $MAX_INCLAUSE_CHUNK_SIZE);
                $found_contextids = array();
                foreach($relevant_thingids_chunks as $onechunk)
                {
                    $sIN = "m.{$owner_projectidfieldname} IN (".implode(',',$onechunk).')';
                    $mainfields = " gc.id, parent_comid, gc.{$thingid_fieldname}, gc.status_cd_at_time_of_com,"
                            . " gc.owner_personid, "
                            . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                            . " gc.active_yn, "
                            . " gc.updated_dt, gc.created_dt";
                    $sSQL = "SELECT $mainfields,"
                            . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                            . " FROM $comm_tablename gc"
                            . " LEFT JOIN $subject_tablename m on m.id=gc.{$thingid_fieldname}"
                            . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                            . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                            . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                    $sSQL .= " WHERE $sIN";
                    if($only_active)
                    {
                        $sSQL .= " AND gc.active_yn=1 AND m.active_yn=1 ";
                    }
                    $sSQL .= " GROUP BY $mainfields";
                    $sSQL .= " ORDER BY id";
                    $result = db_query($sSQL);

                    $parentid=NULL;
                    $action_requested_concern_label=NULL;
                    $action_reply_cd=NULL;
                    while($record = $result->fetchAssoc()) 
                    {
                        $id = $record['id'];
                        $subjectitemid = $record[$thingid_fieldname];
                        $itemname_lookup[$thingname][$subjectitemid] = "$thingname # $subjectitemid";
                        $found_contextids[] = $subjectitemid;
                        $parentid = $record['parent_comid'];
                        $concernvalue = $record['action_requested_concern'];
                        $action_requested_concern_label = $this->getActionImportanceCategoryName($concernvalue);
                        $action_reply_cd = $record['action_reply_cd'];
                        $terminal_yn = $record['terminal_yn'];
                        $happy_yn = $record['happy_yn'];
                        $owner_personid=$record['owner_personid'];
                        if(array_key_exists($parentid, $relevant_branch_comids))
                        {
                            $count_replies_in_relevant_branches++;
                            if(array_key_exists($parentid, $relevant_owned_comids))
                            {
                                $count_direct_replies++;
                            }
                        }
                        if(array_key_exists($owner_personid, $relevant_personids))
                        {
                            //This becomes a relevant branch now
                            $relevant_owned_comids[$id] = $id;
                            $relevant_branch_comids[$id] = $id;
                        }
                        //$active_yn = $record['active_yn'];
                        $attachment_count = $record['attachment_count'];
                        $attachment_most_recent_dt = $record['attachment_most_recent_dt'];
                        if(empty($parentid))
                        {
                            $indentlevel = 0;
                            $rootmap[$id] = $id;
                        } else {
                            if(array_key_exists($parentid, $nestingmap))
                            {
                                $indentlevel = $nestingmap[$parentid] + 1;
                            } else {
                                $indentlevel = 1;
                            }
                            $nestingmap[$id] = $indentlevel;
                            if(array_key_exists($parentid, $rootmap))
                            {
                                $rootmap[$id] = $rootmap[$parentid];    
                            } else {
                                //Parent is the root of a thread
                                $rootmap[$id] = $parentid;    
                            }
                            if($max_indent < $indentlevel)
                            {
                                $max_indent = $indentlevel;
                            }
                        }
                        $root_comid = $rootmap[$id];
                        $onecomment = [];
                        $onecomment['id'] = $id;
                        $onecomment['itemid'] = $subjectitemid;
                        $onecomment['owner_personid'] = $owner_personid;
                        $onecomment['root_comid'] = $root_comid;
                        $onecomment['parent_comid'] = $parentid;
                        $onecomment['status_cd_at_time_of_com'] = $record['status_cd_at_time_of_com'];
                        $onecomment['happy_yn'] = $happy_yn;
                        $onecomment['created_dt'] = $record['created_dt'];
                        $onecomment['updated_dt'] = $record['updated_dt'];
                        $onecomment['attachment_count'] = $attachment_count;
                        $onecomment['attachment_most_recent_dt'] = $attachment_most_recent_dt;
                        $onecomment['action_requested_concern_label'] = $action_requested_concern_label; 
                        $onecomment['action_reply_cd'] = $action_reply_cd;
                        $onecomment['indentlevel'] = $indentlevel;
                        $onecomment['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($record['updated_dt'], $today_dt);

                        $onecomment['has_replies'] = FALSE; //Initialize as false
                        if($action_requested_concern_label > "")
                        {
                            $actionitems[$id] = [];
                        }
                        if(!empty($action_reply_cd) && $terminal_yn==1)
                        {
                            //Mark parent as resolved
                            $actionitems[$parentid][$id] = $action_reply_cd;
                        }
                        $thelist[$id] = $onecomment;
                        if(!empty($parentid))
                        {
                            $thelist[$parentid]['has_replies'] = TRUE;
                        }
                        if($maxid < $id)
                        {
                            $maxid = $id;
                        }
                    }
                }
            
                $thelist_map[$thingname] = $thelist;
                //Get all the names
                if(!empty($itemname_lookup[$thingname]) && count($itemname_lookup[$thingname]) > 0)
                {
                    $thingids = array_keys($itemname_lookup[$thingname]);
                    $sNameWhere = implode(",", $thingids);
                    if($thingname == 'sprint')
                    {
                        $sNameSQL = "select id, $itemname_expr as itemname, 100 as item_importance from $subject_tablename m WHERE id in ($sNameWhere)";
                    } else 
                    if($thingname == 'testcase')
                    {
                        $sNameSQL = "select id, $itemname_expr as itemname, importance as item_importance from $subject_tablename m WHERE id in ($sNameWhere)";
                    } else 
                    if($thingname == 'workitem')
                    {
                        $sNameSQL = "select id, $itemname_expr as itemname, importance as item_importance from $subject_tablename m WHERE id in ($sNameWhere)";
                    } else 
                    if($thingname == 'project')
                    {
                        $sNameSQL = "select id, 'Project' as itemname, 100 as item_importance from $subject_tablename m WHERE id in ($sNameWhere)";
                    }
                    $oNameResult = db_query($sNameSQL);
                    while($aNameRecord = $oNameResult->fetchAssoc()) 
                    {
                        $id = $aNameRecord['id'];
                        $itemname_lookup[$thingname][$id] = array('name'=>$aNameRecord['itemname']
                                ,'importance'=>$aNameRecord['item_importance']);
                    }
                }
            }
            //Figure the action item completion status
            $map_subjectid2comid = array();
            foreach($thelist_map as $thingname=>$thelist)
            {
                $thingid_fieldname = "{$thingname}id";
                foreach($thelist as $cid=>$onecomment)
                {
                    $action_requested_concern_label = $onecomment['action_requested_concern_label'];
                    $indentlevel = $onecomment['indentlevel'];
                    $action_requested_yn = ($action_requested_concern_label > "") ? 1 : 0;
                    $owner_personid = $onecomment['owner_personid'];
                    $relevant_personids[$owner_personid]=$owner_personid;

                    if($action_requested_yn == 1)
                    {
                        if(!array_key_exists($cid, $actionitems))
                        {
                            $is_open = TRUE;
                        } else {
                            $resolutions = $actionitems[$cid];
                            $is_open = TRUE;
                            foreach($resolutions as $code)
                            {
                                if($map_action_status_by_code[$code]['terminal_yn'] == 1)
                                {
                                    $is_open = FALSE;
                                    break;
                                }
                            }
                        }
                        if($is_open)
                        {
                            if(!array_key_exists($action_requested_concern_label, $map_count_waiting_on_resolution))
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern_label] 
                                        = array('new'=>0,
                                            'older_than2days'=>0,
                                            'older_than7days'=>0,
                                            'older_than14days'=>0,
                                            'older_than30days'=>0,
                                            'newest_dt'=>NULL,  
                                            'oldest_dt'=>NULL);   
                            }
                            $thingid = $onecomment['itemid'];
                            $map_subjectid2comid[$thingid][] = $cid;
                            if(empty($map_open_request_detail[$thingname]) || !array_key_exists($thingid, $map_open_request_detail[$thingname]))
                            {
                                $map_open_request_detail[$thingname][$thingid] = array();   
                                $map_open_request_summary[$thingname][$thingid] = array();                             
                            } 
                            $map_open_request_detail[$thingname][$thingid][$action_requested_concern_label][$cid] = array(
                                'itemid'=>$thingid,
                                'owner_personid'=>$onecomment['owner_personid'],
                                'has_replies'=>$onecomment['has_replies'],
                                'created_dt'=>$onecomment['created_dt'],
                                'updated_dt'=>$onecomment['updated_dt'],
                                'age_days'=>$onecomment['age_days'],
                                );
                            if(empty($map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['count']))
                            {
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['count'] = 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than30days'] = 0;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than14days'] = 0;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than7days'] = 0;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than2days'] = 0;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['new'] = 0;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['oldest_dt'] = $onecomment['created_dt'];
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['newest_dt'] = $onecomment['updated_dt'];
                            } else {
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['count'] += 1;
                                if($map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['oldest_dt'] > $onecomment['created_dt'])
                                {
                                    $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['oldest_dt'] = $onecomment['created_dt'];   
                                }
                                if($map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['newest_dt'] < $onecomment['updated_dt'])
                                {
                                    $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['newest_dt'] = $onecomment['updated_dt'];   
                                }
                            }
                            if($onecomment['age_days'] > 30)
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern_label]['older_than30days'] += 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than30days'] += 1;
                            } else if($onecomment['age_days'] > 14) {
                                $map_count_waiting_on_resolution[$action_requested_concern_label]['older_than14days'] += 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than14days'] += 1;
                            } else if($onecomment['age_days'] > 7) {
                                $map_count_waiting_on_resolution[$action_requested_concern_label]['older_than7days'] += 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than7days'] += 1;
                            } else if($onecomment['age_days'] > 2) {
                                $map_count_waiting_on_resolution[$action_requested_concern_label]['older_than2days'] += 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['older_than2days'] += 1;
                            } else {
                                $map_count_waiting_on_resolution[$action_requested_concern_label]['new'] += 1;
                                $map_open_request_summary[$thingname][$thingid][$action_requested_concern_label]['new'] += 1;
                            }
                        }
                        $parentid = $onecomment['parent_comid'];
                        if(array_key_exists($parentid, $relevant_branch_comids))
                        {
                            if(array_key_exists($parentid, $relevant_owned_comids))
                            {
                                $count_direct_replies_waiting_on_resolution++;
                            }
                        }
                    }
                }
            }

            //Complete the bundling
            $bundle['maxid'] = $maxid;
            $bundle['map_itemid2comid'] = $map_subjectid2comid;
            $bundle['map_action_status_by_code'] = $map_action_status_by_code;
            $bundle['relevant_branch_comids'] = $relevant_branch_comids;
            $bundle['relevant_personids'] = $relevant_personids;
            $bundle['map_open_request_summary'] = $map_open_request_summary;
            $bundle['map_open_request_detail'] = $map_open_request_detail;
            $bundle['map_count_waiting_on_resolution'] = $map_count_waiting_on_resolution;
            $bundle['count_direct_replies'] = $count_direct_replies;
            $bundle['count_direct_replies_waiting_on_resolution'] = $count_direct_replies_waiting_on_resolution;
            $bundle['count_replies_in_relevant_branches'] = $count_replies_in_relevant_branches;
            $bundle['itemname_lookup'] = $itemname_lookup;

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return comment summary for goals
     */
    public function getWorkitemCommunicationSummaryBundle($relevant_goalids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByRole('workitem', $relevant_goalids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }

    /**
     * Return comment summary for projects
     */
    public function getProjectCommunicationSummaryBundle($relevant_taskids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByRole('project', $relevant_taskids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }

    /**
     * Return comment summary for sprints
     */
    public function getSprintCommunicationSummaryBundle($relevant_sprintids,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        return $this->getCommunicationSummaryBundleByRole('sprint', $relevant_sprintids, $only_active
                ,$relevant_branch_comids
                ,$relevant_personids);
    }

    /**
     * Return all comment counts for one thing type
     * organize by role and concern level
     */
    private function getCommunicationSummaryBundleByRole($thingname, $relevant_thingids
            ,$only_active=TRUE
            ,$relevant_branch_comids=NULL
            ,$relevant_personids=NULL)
    {
        try
        {
            if(!is_array($relevant_thingids))
            {
                throw new \Exception("The relevant {$thingname}ids must in in an array!");
            }
            if($thingname == 'workitem')
            {
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
                $rolemap_tablename = DatabaseNamesHelper::$m_map_person2role_in_workitem_tablename;
            } else
            if($thingname == 'project')
            {
                $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
                $rolemap_tablename = DatabaseNamesHelper::$m_map_person2role_in_project_tablename;
            } else
            if($thingname == 'sprint')
            {
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
                $rolemap_tablename = DatabaseNamesHelper::$m_map_person2role_in_sprint_tablename;
            } else {
                throw new \Exception("Did not recognize context name of '$thingname'");
            }
            $thingid_fieldname="{$thingname}id";
            
            $bundle = array();
            $relevant_roleids = array();
            $map_open_request = array(); 
            $actionitems = array();
            $thelist = array();
            $nestingmap=array();
            $rootmap=array();
            $maxid = 0;
            $max_indent = 0;
            $map_count_waiting_on_resolution = array();
            $count_direct_replies = 0;                  //Replies to relevant comments
            $count_direct_replies_waiting_on_resolution = 0;
            $count_replies_in_relevant_branches = 0;    //Root of a branch is a comid

            $relevant_owned_comids = array();
            if($relevant_branch_comids==NULL)
            {
                $relevant_branch_comids = array();
            }
            if($relevant_personids==NULL)
            {
                $relevant_personids = array();
            }
            $today_dt = date("Y-m-d", time());
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $relevant_thingids_chunks = array_chunk($relevant_thingids, $MAX_INCLAUSE_CHUNK_SIZE);
            $found_contextids = array();
            foreach($relevant_thingids_chunks as $onechunk)
            {
                $sIN = "$thingid_fieldname IN (".implode(',',$onechunk).')';
                $mainfields = " gc.id, parent_comid, gc.{$thingid_fieldname}, gc.status_cd_at_time_of_com,"
                        . " gc.owner_personid,"
                        . " gc.action_requested_concern, gc.action_reply_cd, a.terminal_yn, a.happy_yn,"
                        . " gc.active_yn, "
                        . " gc.updated_dt, gc.created_dt";
                $sSQL = "SELECT $mainfields,"
                        . " count(fm.attachmentid) as attachment_count, max(fa.uploaded_dt) as attachment_most_recent_dt "
                        . " FROM $main_tablename gc"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_action_status_tablename." a on a.code=gc.action_reply_cd"
                        . " LEFT JOIN $attachmentmap_tablename fm on fm.comid=gc.id"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid";
                $sSQL .= " WHERE $sIN";
                if($only_active)
                {
                    $sSQL .= " AND gc.active_yn=1";
                }
                $sSQL .= " GROUP BY $mainfields";
                $sSQL .= " ORDER BY id";

                $result = db_query($sSQL);

                $parentid=NULL;
                $action_requested_concern=0;
                $action_reply_cd=NULL;
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $contextitemid = $record[$thingid_fieldname];
                    $found_contextids[] = $contextitemid;
                    $parentid = $record['parent_comid'];
                    $action_requested_concern = $record['action_requested_concern'];
                    $action_reply_cd = $record['action_reply_cd'];
                    $terminal_yn = $record['terminal_yn'];
                    $happy_yn = $record['happy_yn'];
                    $owner_personid=$record['owner_personid'];
                    if(array_key_exists($parentid, $relevant_branch_comids))
                    {
                        $count_replies_in_relevant_branches++;
                        if(array_key_exists($parentid, $relevant_owned_comids))
                        {
                            $count_direct_replies++;
                        }
                    }
                    if(array_key_exists($owner_personid, $relevant_personids))
                    {
                        //This becomes a relevant branch now
                        $relevant_owned_comids[$id] = $id;
                        $relevant_branch_comids[$id] = $id;
                    }
                    //$active_yn = $record['active_yn'];
                    $attachment_count = $record['attachment_count'];
                    $attachment_most_recent_dt = $record['attachment_most_recent_dt'];
                    if(empty($parentid))
                    {
                        $indentlevel = 0;
                        $rootmap[$id] = $id;
                    } else {
                        if(array_key_exists($parentid, $nestingmap))
                        {
                            $indentlevel = $nestingmap[$parentid] + 1;
                        } else {
                            $indentlevel = 1;
                        }
                        $nestingmap[$id] = $indentlevel;
                        if(array_key_exists($parentid, $rootmap))
                        {
                            $rootmap[$id] = $rootmap[$parentid];    
                        } else {
                            //Parent is the root of a thread
                            $rootmap[$id] = $parentid;    
                        }
                        if($max_indent < $indentlevel)
                        {
                            $max_indent = $indentlevel;
                        }
                    }
                    $root_comid = $rootmap[$id];
                    $onecomment = array();
                    $onecomment[$thingid_fieldname] = $relevant_thingids;
                    $onecomment['id'] = $id;
                    $onecomment[$thingid_fieldname] = $contextitemid;
                    $onecomment['owner_personid'] = $owner_personid;
                    $onecomment['root_comid'] = $root_comid;
                    $onecomment['parent_comid'] = $parentid;
                    $onecomment['status_cd_at_time_of_com'] = $record['status_cd_at_time_of_com'];
                    $onecomment['happy_yn'] = $happy_yn;
                    $onecomment['created_dt'] = $record['created_dt'];
                    $onecomment['updated_dt'] = $record['updated_dt'];
                    $onecomment['attachment_count'] = $attachment_count;
                    $onecomment['attachment_most_recent_dt'] = $attachment_most_recent_dt;
                    $onecomment['action_requested_concern'] = $action_requested_concern; 
                    $onecomment['action_reply_cd'] = $action_reply_cd;
                    $onecomment['indentlevel'] = $indentlevel;
                    $onecomment['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($record['updated_dt'], $today_dt);

                    $onecomment['has_replies'] = FALSE; //Initialize as false
                    if($action_requested_concern > 0)
                    {
                        $actionitems[$id] = array();
                    }
                    if(!empty($action_reply_cd) && $terminal_yn==1)
                    {
                        //Mark parent as resolved
                        $actionitems[$parentid][$id] = $action_reply_cd;
                    }
                    $thelist[$id] = $onecomment;
                    if(!empty($parentid))
                    {
                        $thelist[$parentid]['has_replies'] = TRUE;
                    }
                    if($maxid < $id)
                    {
                        $maxid = $id;
                    }
                }

                //Figure the action item completion status
                $map_contextid2comid = array();
                foreach($thelist as $cid=>$onecomment)
                {
                    $action_requested_concern = $onecomment['action_requested_concern'];
                    $indentlevel = $onecomment['indentlevel'];
                    $action_requested_yn = ($action_requested_concern > 0) ? 1 : 0;

                    if($action_requested_yn == 1)
                    {
                        if(!array_key_exists($cid, $actionitems))
                        {
                            $is_open = TRUE;
                        } else {
                            $resolutions = $actionitems[$cid];
                            drupal_set_message("LOOK fix the is open logic!","warning");
                            if(count($resolutions) == 0)    //TODO THIS IS  NOT ENOUGH!!!!!!
                            {
                                $is_open = TRUE;
                            } else {
                                $is_open = FALSE;
                            }
                        }
                        if($is_open)
                        {
                            if(!array_key_exists($action_requested_concern, $map_count_waiting_on_resolution))
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern] 
                                        = array('new'=>0,
                                            'older_than2days'=>0,
                                            'older_than7days'=>0,
                                            'older_than14days'=>0,
                                            'older_than30days'=>0);   
                            }
                            if(!array_key_exists($action_requested_concern, $map_open_request))
                            {
                                $map_open_request[$action_requested_concern] = array();   
                            }
                            $tmp = $onecomment[$thingid_fieldname];
                            $map_contextid2comid[$tmp] = $cid;
                            $map_open_request[$action_requested_concern][$cid] = array(
                                $thingid_fieldname=>$onecomment[$thingid_fieldname],
                                'owner_personid'=>$onecomment['owner_personid'],
                                'has_replies'=>$onecomment['has_replies'],
                                'created_dt'=>$onecomment['created_dt'],
                                'updated_dt'=>$onecomment['updated_dt'],
                                'age_days'=>$onecomment['age_days'],
                                );
                            if($onecomment['age_days'] > 30)
                            {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than30days'] += 1;
                            } else if($onecomment['age_days'] > 14) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than14days'] += 1;
                            } else if($onecomment['age_days'] > 7) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than7days'] += 1;
                            } else if($onecomment['age_days'] > 2) {
                                $map_count_waiting_on_resolution[$action_requested_concern]['older_than2days'] += 1;
                            } else {
                                $map_count_waiting_on_resolution[$action_requested_concern]['new'] += 1;
                            }
                        }
                        $parentid = $onecomment['parent_comid'];
                        if(array_key_exists($parentid, $relevant_branch_comids))
                        {
                            if(array_key_exists($parentid, $relevant_owned_comids))
                            {
                                $count_direct_replies_waiting_on_resolution++;
                            }
                        }
                    }
                }
            }

            //Organize the role segmentations
            $role_maps = array();
            if(count($relevant_personids) > 0)
            {
                $relevant_thingids_chunks = array_chunk($found_contextids, $MAX_INCLAUSE_CHUNK_SIZE);
                $sPERSONIN = "rm.personid IN (".implode(',',$relevant_personids).')';
                if(count($relevant_thingids_chunks) > 0)
                {
                    foreach($relevant_thingids_chunks as $onechunk)
                    {
                        $sIN = "rm.$thingid_fieldname IN (".implode(',',$onechunk).')';
                        $mainfields = " rm.roleid, rm.personid, rm.{$thingid_fieldname}, ci.active_yn";
                        $sSQL = "SELECT distinct $mainfields "
                                . " FROM $rolemap_tablename rm "
                                . " LEFT JOIN $main_tablename ci on ci.id=rm.{$thingid_fieldname}";
                        $sSQL .= " WHERE $sIN and $sPERSONIN";
                        if($only_active)
                        {
                            $sSQL .= " AND ci.active_yn=1";
                        }
                        $sSQL .= " ORDER BY rm.{$thingid_fieldname}";
                        $result = db_query($sSQL);
                        while($record = $result->fetchAssoc()) 
                        {
                            $roleid = $record['roleid'];
                            $relevant_roleids[$roleid] = $roleid;
                            $id = $record[$thingid_fieldname];
                            if(!array_key_exists($roleid, $role_maps))
                            {
                                $role_maps[$roleid] = array();
                            }
                            $role_maps[$roleid][$id] = $id;
                        }
                    }
                    //Now map the found roles into our comment summaries
                    foreach($map_open_request as $lc=>$comments)
                    {
                        foreach($role_maps as $roleid=>$itemids)
                        {
                            foreach($itemids as $contextitemid)
                            {
                                $cid = $map_contextid2comid[$contextitemid];

                                if(array_key_exists($cid, $comments))
                                {
                                    if(!isset($map_open_request[$lc][$cid]['roles']))
                                    {
                                        $map_open_request[$lc][$cid]['roles'] = array();
                                    }
                                    $map_open_request[$lc][$cid]['roles'][] = $roleid;
                                }
                            }
                        }
                    }
                }
            }
            
            //Complete the bundling
            $bundle['maxid'] = $maxid;
            $bundle['max_indent']=$max_indent;
            $bundle['role_maps']=$role_maps;
            $bundle['relevant_roleids']=$relevant_roleids;
            $bundle['relevant_branch_comids'] = $relevant_branch_comids;
            $bundle['relevant_personids'] = $relevant_personids;
            $bundle['map_open_request'] = $map_open_request;
            $bundle['map_count_waiting_on_resolution'] = $map_count_waiting_on_resolution;
            $bundle['count_direct_replies']=$count_direct_replies;
            $bundle['count_direct_replies_waiting_on_resolution'] = $count_direct_replies_waiting_on_resolution;
            $bundle['count_replies_in_relevant_branches'] = $count_replies_in_relevant_branches;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return one communication
     */
    public function getOneProjectCommunication($comid)
    {
        return $this->getOneCommunication('project', $comid);
    }

    /**
     * Return one communication
     */
    public function getOneWorkitemCommunication($comid)
    {
        return $this->getOneCommunication('workitem', $comid);
    }

    /**
     * Return one comment
     */
    public function getOneSprintComment($comid)
    {
        return $this->getOneCommunication('sprint', $comid);
    }
    
    /**
     * Return one comment
     */
    public function getOneTestcaseComment($comid)
    {
        return $this->getOneCommunication('testcase', $comid);
    }
    
    /**
     * Return one comment
     */
    private function getOneCommunication($contextname, $comid)
    {
        try
        {
            $steps_tablename = NULL;
            if($contextname == 'workitem')
            {
                $keyfieldname = 'workitemid';
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
            } else
            if($contextname == 'project')
            {
                $keyfieldname = 'projectid';
                $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
            } else
            if($contextname == 'sprint')
            {
                $keyfieldname = 'sprintid';
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
            } else
            if($contextname == 'testcase')
            {
                $keyfieldname = 'testcaseid';
                $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $attachmentmap_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
                $steps_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
            } else {
                throw new \Exception("Did not recognize context name of '$contextname' to get the comment");
            }
            if(empty($comid))
            {
                throw new \Exception("Cannot get comment without an id!");
            }
            $sSQL = "SELECT"
                    . " gc.id, parent_comid, gc.{$keyfieldname}, gc.status_cd_at_time_of_com,"
                    . " gc.title_tx, gc.body_tx, gc.owner_personid,"
                    . " gc.action_requested_concern, gc.action_reply_cd,"
                    . " gc.active_yn, "
                    . " p.first_nm, p.last_nm, p.shortname, "
                    . " gc.updated_dt, gc.created_dt "
                    . " FROM $main_tablename gc"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=gc.owner_personid";
            $sSQL .= " WHERE gc.id=$comid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $subject_itemid = $record[$keyfieldname];
            
            if(!empty($steps_tablename))
            {
                $steps_detail = $this->getCommunicationTestcaseStepNumbers($comid);
                $stepnum_list_ar= [];
                foreach($steps_detail as $step_num=>$detail)
                {
                    $stepnum_list_ar[] = $step_num;
                }
                sort($stepnum_list_ar);
                $record['stepnum_list_tx'] = implode(', ', $stepnum_list_ar);
            }
            
            $sSQL2 = "SELECT"
                    . " fa.id as attachmentid, fa.filename, fa.filesize, fa.uploaded_dt, fa.uploaded_by_uid, "
                    . " p.first_nm, p.last_nm, p.shortname"
                    . " FROM $attachmentmap_tablename fm"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_attachment_tablename." fa on fa.id=fm.attachmentid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=fa.uploaded_by_uid";
            $sSQL2 .= " WHERE fm.comid=$comid";
            $sSQL2 .= " ORDER BY fa.filename";
            $result2 = db_query($sSQL2);
            $attachments = array();
            while($a2 = $result2->fetchAssoc()) 
            {
                $aid = $a2['attachmentid'];
                $attachments[] = $a2;   
            }
            $record['attachments'] = $attachments;
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return history records for one workitem communication
     */
    public function getWorkitemCommunicationHistory($comid)
    {
        return $this->getCommunicationHistory('workitem', $comid);
    }

    /**
     * Return history records for one task comment
     */
    public function getSprintCommunicationHistory($comid)
    {
        return $this->getCommunicationHistory('sprint', $comid);
    }
    
    /**
     * Return history records for one task comment
     */
    public function getTestcaseCommunicationHistory($comid)
    {
        return $this->getCommunicationHistory('testcase', $comid);
    }
    
    /**
     * Return history records for one communication item
     */
    private function getCommunicationHistory($contextname, $comid)
    {
        try
        {
            if(empty($comid))
            {
                throw new \Exception("Cannot get communication history without an id!");
            }
            if($contextname == 'workitem' || $contextname == 'goal' || $contextname == 'task')
            {
                $keyfieldname = 'workitemid';
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_history_tablename;
            } else
            if($contextname == 'sprint')
            {
                $keyfieldname = 'sprintid';
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_history_tablename;
            } else
            if($contextname == 'testcase')
            {
                $keyfieldname = 'testcaseid';
                $main_tablename = DatabaseNamesHelper::$m_testcase_communication_history_tablename;
            } else
            if($contextname == 'project')
            {
                $keyfieldname = 'projectid';
                $main_tablename = DatabaseNamesHelper::$m_project_communication_history_tablename;
            } else {
                throw new \Exception("Did not recognize context name of '$contextname'");
            }
            $sSQL = "SELECT"
                    . " gc.id, parent_comid, $keyfieldname, status_cd_at_time_of_com,"
                    . " title_tx, body_tx, owner_personid,"
                    . " gc.action_requested_concern, gc.action_reply_cd,"
                    . " gc.active_yn, "
                    . " p.first_nm, p.last_nm, p.shortname, "
                    . " gc.original_updated_dt, gc.original_created_dt, gc.replaced_dt,"
                    . " changed_status_cd_at_time_of_com, changed_title_tx, changed_body_tx, "
                    . " changed_action_requested_concern, changed_action_reply_cd, changed_active_yn,"
                    . " num_attachments_added, num_attachments_removed "
                    . " FROM $main_tablename gc"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=gc.owner_personid";
            $sSQL .= " WHERE gc.original_comid=$comid";
            $sSQL .= " ORDER BY gc.replaced_dt";
            $result = db_query($sSQL);
            $records = $result->fetchAllAssoc('id',\PDO::FETCH_ASSOC);
            return $records;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    
    /**
     * Return a rich result in the communication context
     */
    public function getCommunicationUpdatesMapBundle($relevant_projectids, $filter_bundle=NULL)
    {
        try
        {
            $bundle = [];
            if(empty($relevant_projectids))
            {
                throw new \Exception("The relevant project ids must in in an array!");
            }
            if(!is_array($relevant_projectids))
            {
                //Assume we were provided just oneproject id
                $relevant_projectids = array($relevant_projectids);
            }            
            if($filter_bundle === NULL)
            {
                $previous_bundle = [];
                $previous_bundle['most_recent_edit_key'] = NULL;
                $previous_bundle['most_recent_edit_timestamp'] = NULL;
            }
            
            $previous_project_edit_key = $previous_bundle['most_recent_edit_key'];
            $previous_project_edit_timestamp = $previous_bundle['most_recent_edit_timestamp'];

            $bundle['communications'] = $this->getCommunicationsInProjectsFilteredBundle($relevant_projectids, $filter_bundle);
            $bundle['input_filters'] = $filter_bundle;
            $bundle['projectid']=$relevant_projectids;
            $bundle['previous_project_edit_timestamp']=$previous_project_edit_timestamp;
            $bundle['previous_project_edit_key']=$previous_project_edit_key;

            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}

