<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';

/**
 * Prioritized information for a project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Dashboard4GroupPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oDashHelp = NULL;
    protected $m_personid = NULL;
    protected $m_currentappuser_personid = NULL;
    protected $m_projectid = NULL;
    
    public function __construct($projectid = NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/DashboardHelper');

        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oDashHelp = new \bigfathom\DashboardHelper();
        global $user;
        $this->m_currentappuser_personid = $user->uid;
        if(empty($projectid))
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $projectid = $this->m_oContext->getSelectedProjectID();
        }
        $this->m_projectid = $projectid;
        
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) 
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }
    
    private function getActionRequestMarkup($keyname, $thingid, $summary_info, $workitem_communication_baseurl)
    {
        $total = 0;
        $breakdown=array();
        $highlevelword = NULL;
        $highwatermark=0;
        foreach($summary_info as $levelnum=>$detail)
        {
            $levelcount = $detail['count'];
            $levelword = UtilityGeneralFormulas::getKeywordForConcernLevel($levelnum);
            $breakdown[] = "$levelword=$levelcount";
            $total += $levelcount;
            if($highwatermark < $levelnum)
            {
                $highwatermark = $levelnum;  
                $highlevelword = strtolower($levelword);
            }
        }
        
        $ar_goalcomment_link = l($total, $workitem_communication_baseurl, array('query' => array($keyname => $thingid)));
        if(count($breakdown)===0)
        {
            $markup = $ar_goalcomment_link;
        } else {
            $markup = "<span class='concern-{$highlevelword}' title='" .  implode(", ", $breakdown) . "'>$ar_goalcomment_link</span>";
        }
        return $markup;
    }
    
    private function getDashboardMarkup($thingname,$keyname, $thingid, $baseurl)
    {
        $ar_goalcomment_link = l($thingid, $baseurl, array('query' => array($keyname => $thingid)));
        $markup = "<span title='Jump to $thingname dashboard of #$thingid'>$ar_goalcomment_link</span>";
        return $markup;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $only_active=TRUE;
            drupal_set_message("TODO create form for group dashboard");
            return array();
            
            $naturalsort_imgurl = file_create_url(path_to_theme().'/images/icon_sorting.png');
            $imgurl_ar['Q1'] = file_create_url(path_to_theme().'/images/icon_q1.png');
            $imgurl_ar['Q2'] = file_create_url(path_to_theme().'/images/icon_q2.png');
            $imgurl_ar['Q3'] = file_create_url(path_to_theme().'/images/icon_q3.png');
            $imgurl_ar['Q4'] = file_create_url(path_to_theme().'/images/icon_q4.png');
            $imgurl_ar['Q1_dim'] = file_create_url(path_to_theme().'/images/icon_q1dim.png');
            $imgurl_ar['Q2_dim'] = file_create_url(path_to_theme().'/images/icon_q2dim.png');
            $imgurl_ar['Q3_dim'] = file_create_url(path_to_theme().'/images/icon_q3dim.png');
            $imgurl_ar['Q4_dim'] = file_create_url(path_to_theme().'/images/icon_q4dim.png');
            
            $coreview_urls_arr = array();
            $coreview_urls_arr['project_dashboard'] = 'bigfathom/dashboards/oneproject'; //&projectid=58';
            $coreview_urls_arr['workitem_dashboard'] = 'bigfathom/dashboards/workitem'; //&workitemid=58';
            $coreview_urls_arr['goal_dashboard'] = 'bigfathom/dashboards/workitem'; //&goalid=58';
            $coreview_urls_arr['task_dashboard'] = 'bigfathom/dashboards/workitem'; //&goalid=58'; same on purpose!
            $coreview_urls_arr['sprint_dashboard'] = 'bigfathom/dashboards/sprint'; //&goalid=58';
            $coreview_urls_arr['workitem_communications'] = 'bigfathom/workitem/mng_comments';//&goalid=5            
            
            $all_goals_in_project = $this->m_oMapHelper->getGoalsInGroupByID($this->m_projectid);
            $dashdata = $this->m_oDashHelp->getGroupDashboardDataBundle($this->m_projectid);

            $dd_analyzed_bundle = $dashdata['analyzed_bundle'];
            $dd_root = $dd_analyzed_bundle['root'];
            $dd_detail = $dd_analyzed_bundle['detail'];
            $dd_flat_wi2wi_map = $dd_detail['flat_wi2wi_map'];
            $dd_flat_allthings_map = $dd_detail['flat_allthings_map'];
            $dd_goal_list_ar = $dd_flat_allthings_map['goal'];
            $dd_node_details_ar = $dd_detail['node_details'];

            $relevant_goalids = array();
            foreach($dd_goal_list_ar as $goalid=>$extra)
            {
                $relevant_goalids[] = $goalid;
            }
            $relevant_branch_comids=NULL;
            $relevant_personids=NULL; 
            $workitem_communicationbundle = $this->m_oMapHelper->getCommentSummaryBundleByWorkitem($relevant_goalids,$only_active,$relevant_branch_comids,$relevant_personids);
            $map_open_request_summary_by_goalid = $workitem_communicationbundle['map_open_request_summary'];
//drupal_set_message("SHOW dd_flat_wi2wi_map >>> " . print_r($dd_flat_wi2wi_map,TRUE));

            $widlist = [];
            if(isset($dd_flat_allthings_map['workitem']))
            {
                $allworkitemid2type_map = $dd_flat_allthings_map['workitem'];
                foreach($allworkitemid2type_map as $id=>$relationshiptype)
                {
                    $widlist[] = $id;
                }
            }
            $all_winfo = $this->m_oMapHelper->getWorkitemInfoForListOfIDs($widlist);
            
            drupal_set_title("Group Open Items Dashboard");// for $project_nm Group");
            
            $projectinfo = $dashdata['projectinfo'];
            $project_nm = $projectinfo['root_workitem_nm'];
            $project_mission_tx = $projectinfo['mission_tx'];
            $root_goalid = $projectinfo['root_goalid'];
            
            $intro_markup = "<table>"
                    . "<tr><td>Group Name:</td><td>$project_nm</td></tr>"
                    . "</table>"
                    . "<br>";
            
            $form["data_entry_area1"]['intro_container']['main'] = array('#type' => 'item',
                     '#markup' => $intro_markup);            

            $maintable_markup = $this->getOpenWorkitemsMarkup($dd_goal_list_ar, $dd_node_details_ar, $all_winfo
                , $map_open_request_summary_by_goalid, $coreview_urls_arr
                , $dd_flat_wi2wi_map);
            $form["data_entry_area1"]['table_container']['goal_table'] = array('#type' => 'item',
                     '#markup' => $maintable_markup);
            
            $maintable_markup = $this->getOpenAntecedentGroupsMarkup($dd_goal_list_ar, $dd_node_details_ar, $all_winfo
                , $map_open_request_summary_by_goalid, $coreview_urls_arr
                , $dd_flat_wi2wi_map);
            $form["data_entry_area1"]['table_container']['project_table'] = array('#type' => 'item',
                     '#markup' => $maintable_markup);
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getOpenWorkitemsMarkup($dd_workitem_list_ar, $dd_node_details_ar, $all_winfo
            , $map_open_request_summary_by_workitemid, $coreview_urls_arr
            , $dd_flat_wi2wi_map)
    {
        $project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        $workitem_dashboard_baseurl = $coreview_urls_arr['workitem_dashboard'];
        $goal_dashboard_baseurl = $coreview_urls_arr['goal_dashboard'];
        $task_dashboard_baseurl = $coreview_urls_arr['task_dashboard'];
        $workitem_communication_baseurl = $coreview_urls_arr['workitem_communications'];
        
        $maintable_row_ar = array();
        foreach($dd_workitem_list_ar as $workitemid=>$hierarchy_nodetype)
        {
            $owner_projectid = $all_winfo[$workitemid]['owner_projectid'];
            if($this->m_projectid == $owner_projectid)
            {
                $workitem_detail = $dd_node_details_ar['workitem'][$workitemid];
                $workitem_nm = $all_winfo[$workitemid]['workitem_nm'];
                $start_dt = $workitem_detail['start_dt'];
                $end_dt = $workitem_detail['end_dt'];

                $action_request_summary_info = $map_open_request_summary_by_workitemid[$workitemid];
                $ar_info_markup = $this->getActionRequestMarkup('workitemid',$workitemid, $action_request_summary_info, $workitem_communication_baseurl);

                $goalid_markup 
                                = $this->getDashboardMarkup('workitem','workitemid',$workitemid, $workitem_dashboard_baseurl);
                
                $status_cd_factors = $workitem_detail['status_cd_factors'];
                $status_workstarted_yn = $status_cd_factors['workstarted_yn'];
                $status_cd = $status_cd_factors['code'];
                $status_title_tx = $status_cd_factors['title_tx'];
                $inherited_status_factors = $workitem_detail['branchstats']['inherited_status_factors'];
                $implied_workstarted_yn = $inherited_status_factors['workstarted_yn'];
                $implied_status_cd = $inherited_status_factors['implied_status_cd'];

                $workitem_count = $workitem_detail['branchstats']['total_antecedent_workitem_count'];
                $workitem_count_markup = l($workitem_count, $workitem_dashboard_baseurl, array('query' => array('workitemid' => $workitemid)));
                
                $goal_count = $workitem_detail['branchstats']['total_antecedent_goal_count'];
                $goal_count_markup = l($goal_count, $goal_dashboard_baseurl, array('query' => array('goalid' => $workitemid)));

                $task_count = $workitem_detail['branchstats']['total_antecedent_task_count'];
                $task_count_markup = l($task_count, $task_dashboard_baseurl, array('query' => array('goalid' => $workitemid)));

                $owner_markup = $workitem_detail['owner_personid'];
                $most_recent_activity_dt = $workitem_detail['most_recent_activity_dt'];
                $success_forecast_p = $workitem_detail['branchstats']['success_forecast_p'];

                if($status_workstarted_yn == $implied_workstarted_yn)
                {
                    $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                } else {
                    if(!empty($inherited_status_factors['warning_tx']))
                    {
                        $warnstatus_tx = '; '.$inherited_status_factors['warning_tx'];
                        $status_classname = 'warn-text';
                    } else {
                        $warnstatus_tx = '';
                        $status_classname = 'normal-text';
                    }
                    $status_markup = "<span class='{$status_classname}' title='Antecedent status implies project work has started{$warnstatus_tx}'>$implied_status_cd</span>";
                }

                $antecedent_workitems_ar = [];
                $antecedent_goals_ar = [];
                $antecedent_tasks_ar = [];
                $antecedent_projects_ar = [];
                if(isset($dd_flat_wi2wi_map[$workitemid]))
                {
                    foreach($dd_flat_wi2wi_map[$workitemid] as $ant_workitemid)
                    {
                        $winfo = $all_winfo[$ant_workitemid];
                        if($winfo['workitem_basetype'] == 'G')
                        {
                            $ant_goals_markup 
                                = $this->getDashboardMarkup('workitem','workitemid',$ant_workitemid, $goal_dashboard_baseurl);
                            $antecedent_goals_ar[] = $ant_goals_markup; 
                            if($winfo['owner_projectid'] !== $this->m_projectid)
                            {
                                $dap_projectid = $winfo['owner_projectid'];
                                if(!array_key_exists($dap_projectid, $antecedent_projects_ar))
                                {
                                    $ant_projectid_markup 
                                        = $this->getDashboardMarkup('project','projectid',$dap_projectid, $project_dashboard_baseurl);
                                    $antecedent_projects_ar[$dap_projectid] = $ant_projectid_markup;   
                                }
                            }
                        } else
                        if($winfo['workitem_basetype'] == 'T')
                        {
                            $ant_tasks_markup 
                                = $this->getDashboardMarkup('workitem','workitemid',$ant_workitemid, $goal_dashboard_baseurl);
                            $antecedent_tasks_ar[] = $ant_tasks_markup;   
                        }
                    }
                }
                $antecedent_workitems_markup = implode(', ', $antecedent_workitems_ar);
                $antecedent_goals_markup = implode(', ', $antecedent_goals_ar);
                $antecedent_tasks_markup = implode(', ', $antecedent_tasks_ar);
                $antecedent_projects_markup = implode(', ', $antecedent_projects_ar);

                $maintable_row_ar[] = "<td>$goalid_markup</td>"
                        . "<td>$workitem_nm</td>"
                        . "<td>$antecedent_tasks_markup</td>"
                        . "<td>$antecedent_goals_markup</td>"
                        . "<td>$antecedent_projects_markup</td>"
                        . "<td>$status_markup</td>"
                        . "<td>$success_forecast_p</td>"
                        . "<td>$owner_markup</td>"
                        . "<td>$goal_count_markup</td>"
                        . "<td>$task_count_markup</td>"
                        . "<td>$most_recent_activity_dt</td>"
                        . "<td>$start_dt</td>"
                        . "<td>$end_dt</td>"
                        . "<td>$ar_info_markup</td>"
                    ;
            }
        }

        $maintable_markup = "<h2>Open Workitems of Group</h2>";
        $maintable_markup .= '<table id="my-main-table" class="browserGrid">';
        $maintable_markup .= "<thead>"
                . "<tr>"
                . "<th><span title='The unique ID number of the goal'>ID</span></th>"
                . "<th><span title='The name of this goal'>Name</span></th>"
                . "<th><span title='Direct Antecedant Tasks'>DATs</span></th>"
                . "<th><span title='Direct Antecedant Goals'>DAGs</span></th>"
                . "<th><span title='Direct Antecedant Groups'>DAPs</span></th>"
                . "<th><span title='Current status of the workitem'>Status</span></th>"
                . "<th><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this workitem owner'>Owner</span></th>"
                . "<th><span title='Count of all the OPEN antecedent goals'>Goals</span></th>"
                . "<th><span title='Count of all the OPEN tasks for the goal'>Tasks</span></th>"
                . "<th><span title='Date and time of most recent activity in the goal'>Any Activity</span></th>"
                . "<th><span title='Start date of the goal'>Start Date</span></th>"
                . "<th><span title='End date of the goal'>End Date</span></th>"
                . "<th><span title='Open action requests'>AR</span></th>"
                . "</tr>"
                . "</thead>";
        if(count($maintable_row_ar)==0)
        {
            $maintable_markup .= "<tbody></tbody>";
        } else {
            $maintable_markup .= "<tbody><tr>".implode("</tr><tr>", $maintable_row_ar)."</tr></tbody>";
        }
        $maintable_markup .= "</table>";            
        $maintable_markup .= "</br>";
        return $maintable_markup;
    }

    /**
     * Groups are GOAL nodes.
     */
    private function getOpenAntecedentGroupsMarkup($dd_goal_list_ar, $dd_node_details_ar, $all_goal_names
            , $map_open_request_summary_by_goalid, $coreview_urls_arr
            , $dd_flat_wi2wi_map)
    {
        $project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        $goal_dashboard_baseurl = $coreview_urls_arr['goal_dashboard'];
        $task_dashboard_baseurl = $coreview_urls_arr['task_dashboard'];
        $workitem_communication_baseurl = $coreview_urls_arr['workitem_communications'];

        $maintable_row_ar = array();
        foreach($dd_goal_list_ar as $workitemid=>$hierarchy_nodetype)
        {
            $owner_projectid = $all_goal_names[$workitemid]['owner_projectid'];
            if($this->m_projectid !== $owner_projectid)
            {
                $goal_detail = $dd_node_details_ar['goal'][$workitemid];
                if($goal_detail['is_project_yn'] === 1)
                {
                    $workitem_nm = $all_goal_names[$workitemid]['workitem_nm'];
                    $start_dt = $goal_detail['start_dt'];
                    $end_dt = $goal_detail['end_dt'];

                    $action_request_summary_info = $map_open_request_summary_by_goalid[$workitemid];
                    $ar_info_markup = $this->getActionRequestMarkup('workitemid',$workitemid, $action_request_summary_info, $workitem_communication_baseurl);

                    $projectid_markup 
                                = $this->getDashboardMarkup('project','projectid',$owner_projectid, $project_dashboard_baseurl);

                    $status_cd_factors = $goal_detail['status_cd_factors'];
                    $status_workstarted_yn = $status_cd_factors['workstarted_yn'];
                    $status_cd = $status_cd_factors['code'];
                    $status_title_tx = $status_cd_factors['title_tx'];
                    $inherited_status_factors = $goal_detail['branchstats']['inherited_status_factors'];
                    $implied_workstarted_yn = $inherited_status_factors['workstarted_yn'];
                    $implied_status_cd = $inherited_status_factors['implied_status_cd'];

                    $owner_markup = $goal_detail['owner_personid'];
                    $most_recent_activity_dt = $goal_detail['most_recent_activity_dt'];
                    $success_forecast_p = $goal_detail['branchstats']['success_forecast_p'];

                    if($status_workstarted_yn == $implied_workstarted_yn)
                    {
                        $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                    } else {
                        if(!empty($inherited_status_factors['warning_tx']))
                        {
                            $warnstatus_tx = '; '.$inherited_status_factors['warning_tx'];
                            $status_classname = 'warn-text';
                        } else {
                            $warnstatus_tx = '';
                            $status_classname = 'normal-text';
                        }
                        $status_markup = "<span class='{$status_classname}' title='Antecedent status implies project work has started{$warnstatus_tx}'>$implied_status_cd</span>";
                    }

                    if(!array_key_exists($workitemid, $dd_flat_wi2wi_map))
                    {
                        $antecedents_markup = '';
                    } else {
                        $antecedents_markup = implode(', ', $dd_flat_wi2wi_map[$workitemid]);
                    }

                    $maintable_row_ar[] = "<td>$projectid_markup</td>"
                            . "<td>$workitem_nm</td>"
                            . "<td>$status_markup</td>"
                            . "<td>$success_forecast_p</td>"
                            . "<td>$owner_markup</td>"
                            . "<td>$most_recent_activity_dt</td>"
                            . "<td>$start_dt</td>"
                            . "<td>$end_dt</td>"
                            . "<td>$ar_info_markup</td>"
                        ;
                }
            }
        }

        $maintable_markup = "<h2>Open Antecedent Groups of this Group</h2>";
        $maintable_markup .= '<table id="my-main-table" class="browserGrid">';
        $maintable_markup .= "<thead>"
                . "<tr>"
                . "<th><span title='The unique ID number of the project'>ID</span></th>"
                . "<th><span title='The project name'>Name</span></th>"
                . "<th><span title='Current status of the project'>Status</span></th>"
                . "<th><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this project owner'>Owner</span></th>"
                . "<th><span title='Date and time of most recent activity in the project'>Any Activity</span></th>"
                . "<th><span title='Start date of the goal'>Start Date</span></th>"
                . "<th><span title='End date of the goal'>End Date</span></th>"
                . "<th><span title='Open action requests'>AR</span></th>"
                . "</tr>"
                . "</thead>";
        if(count($maintable_row_ar)==0)
        {
            $maintable_markup .= "<tbody></tbody>";
        } else {
            $maintable_markup .= "<tbody><tr>".implode("</tr><tr>", $maintable_row_ar)."</tr></tbody>";
        }
        $maintable_markup .= "</table>";            
        $maintable_markup .= "</br>";
        return $maintable_markup;
    }
    
    
    private function getActionTableHeadingMarkup($format, $normal, $imgurl_ar, $dimcols_ar = array())
    {
        try
        {
            $maintable_headingrow_markup = '';
            foreach($format as $one_headingformat_row)
            {
                $tagname = $one_headingformat_row['tagname'];
                $tag_attribs = '';
                if(array_key_exists('span',$one_headingformat_row))
                {
                    $tag_attribs .= " span='{$one_headingformat_row['span']}'";
                }                
                $maintable_headingrow_markup .= "\n<{$tagname}{$tag_attribs}></$tagname>";
            }            
            $maintable_headingrow_markup .= "\n<thead>";
            foreach($normal as $one_heading_row)
            {
                $maintable_row_cols = array();
                foreach($one_heading_row as $items)
                {
                    $th_attribs = '';
                    if(array_key_exists('colspan',$items))
                    {
                        $th_attribs .= " colspan='{$items['colspan']}'";
                    }
                    if(array_key_exists('rowspan',$items))
                    {
                        $th_attribs .= " rowspan='{$items['rowspan']}'";
                    }
                    if(array_key_exists('scope',$items))
                    {
                        $th_attribs .= " scope='{$items['scope']}'";
                    }
                    if(array_key_exists('classhint',$items))
                    {
                        $th_attribs .= " class='align-{$items['classhint']}'";
                    }
                    $heading_value = $items['heading'];
                    if(isset($imgurl_ar[$heading_value]))
                    {
                        if(isset($dimcols_ar[$heading_value]) && isset($imgurl_ar[$heading_value.'_dim']))
                        {
                            $heading_value_markup = "<img alt='attached files count' src='{$imgurl_ar[$heading_value.'_dim']}' />$heading_value";
                        } else {
                            $heading_value_markup = "<img alt='attached files count' src='{$imgurl_ar[$heading_value]}' />$heading_value";
                        }
                    } else {
                        $heading_value_markup = $heading_value;
                    }
                    if(!array_key_exists('blurb',$items))
                    {
                        $displayvalue = $items['heading'];
                    } else {
                        $displayvalue = "<span title='{$items['blurb']}'>{$heading_value_markup}</span>";
                    }
                    $maintable_row_cols[] = "<th{$th_attribs}>$displayvalue</th>";
                }
                $maintable_headingrow_markup .= "\n\t<tr>".implode("\n\t",$maintable_row_cols).'</tr>';
            }
            $maintable_headingrow_markup .= "\n</thead>";
            return $maintable_headingrow_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getRoleBasedTableMarkup($person_markup,$roledetail, $roleactiondetail
            , $heading_format, $heading_normal, $imgurl_ar
            , $col_meaning
            , $actionitem_urls_arr)
    {
        try
        {
            $roleid = $roledetail['id'];
            $tableid = "table4roleid" . $roleid;
            $q1total = 0;
            $q2total = 0;
            $q3total = 0;
            $q4total = 0;
            $maintable_markup = "<h2>Highlights for Open Items where $person_markup role is {$roledetail['role_nm']}</h2>";
            $maintable_row_ar = array();
            foreach($roleactiondetail['data'] as $detail)
            {
                $maintable_row_cols = array();
                $colnum=0;
                foreach($detail as $items)
                {
                    $meaning = $col_meaning[$colnum];
                    $value = $items['value'];
                    if($colnum===0)
                    {
                        $contextname = $value;
                    }
                    if(!array_key_exists('classhint',$items))
                    {
                        $colmarkup = $value;
                    } else {
                        $colmarkup = "<span class='{$items['classhint']}'>{$value}</span>";
                    }
                    if($colnum > 0 && $value != 0)
                    {
                        if(array_key_exists($meaning, $actionitem_urls_arr[$contextname]))
                        {
                            $url = $actionitem_urls_arr[$contextname][$meaning];
                            $colmarkup = l($colmarkup, $url, array('query' => array('todo' => $colnum)));
                        }
                    }
                    $maintable_row_cols[] = $colmarkup;
                    if($colnum == 4)
                    {
                        $q1total+=$value;
                    }
                    if($colnum == 5)
                    {
                        $q2total+=$value;
                    }
                    if($colnum == 6)
                    {
                        $q3total+=$value;
                    }
                    if($colnum == 7)
                    {
                        $q4total+=$value;
                    }
                    $colnum++;
                }
                $maintable_row_ar[] = '<td>'.implode('</td><td>',$maintable_row_cols).'</td>';
            }
            $dim_ar = array();
            if($q1total < 1)
            {
                $dim_ar['Q1'] = 'dim';   
            }
            if($q2total < 1)
            {
                $dim_ar['Q2'] = 'dim';   
            }
            if($q3total < 1)
            {
                $dim_ar['Q3'] = 'dim';   
            }
            if($q4total < 1)
            {
                $dim_ar['Q4'] = 'dim';   
            }
            $maintable_headingrow_markup = $this->getActionTableHeadingMarkup($heading_format, $heading_normal, $imgurl_ar, $dim_ar);
            $maintable_markup .= "<table id='$tableid' class='browserGrid'>";
            $maintable_markup .= $maintable_headingrow_markup;
            $maintable_markup .= "<tr>".implode("</tr><tr>", $maintable_row_ar)."</tr>";
            $maintable_markup .= "</table>";
            $maintable_markup .= "<br>";
            return $maintable_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
