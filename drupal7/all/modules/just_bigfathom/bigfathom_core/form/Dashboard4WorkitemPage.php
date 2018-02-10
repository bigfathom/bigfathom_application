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
 * Prioritized information for a goal
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Dashboard4WorkitemPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oDashHelp = NULL;
    protected $m_personid = NULL;
    protected $m_currentappuser_personid = NULL;
    protected $m_workitemid = NULL;
    protected $m_oap_tableid = "oap_table";
    
    public function __construct($workitemid = NULL, $urls_override_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/DashboardHelper');

        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oDashHelp = new \bigfathom\DashboardHelper();
        global $user;
        $this->m_currentappuser_personid = $user->uid;
        if(empty($workitemid))
        {
            throw new \Exception("Missing required workitemid!!!!!");
        }
        $this->m_workitemid = $workitemid;
        
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) 
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $cmi = $this->m_oContext->getCurrentMenuItem();
        if(empty($cmi['link_path']))
        {
            $this->m_cmi = array('link_path'=> $urls_arr['return']);
        } else {
            $this->m_cmi = $cmi;    //['link_path'] = $urls_arr['return'];
        }
    }
    
    private function getActionRequestMarkup($keyname, $thingid, $summary_info, $comment_baseurl)
    {
        $total = 0;
        $breakdown=array();
        $highlevelword = NULL;
        $highwatermark=0;
        if(empty($summary_info))
        {
            $highlevelword = 'none';
        } else {
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
        }
        
        $ar_goalcomment_link = l($total, $comment_baseurl, array('query' => array($keyname => $thingid, 'return' => $this->m_cmi['link_path'])));
        if(count($breakdown)===0)
        {
            $markup = $ar_goalcomment_link;
        } else {
            $markup = "<span class='concern-{$highlevelword}' title='" .  implode(", ", $breakdown) . "'>$ar_goalcomment_link</span>";
        }
        return $markup;
    }
    
    private function getDashboardMarkup($thingname, $keyname, $thingid, $baseurl)
    {
        $ar_goalcomment_link = l($thingid, $baseurl, array('query' => array($keyname => $thingid, 'return' => $this->m_cmi['link_path'])));
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
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            if(empty($this->m_urls_arr['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($this->m_urls_arr['rparams']));
            }

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
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
            
            $coreview_urls_arr['project_communications'] = 'bigfathom/project/mng_comments';//&projectid=5            
            $coreview_urls_arr['workitem_communications'] = 'bigfathom/workitem/mng_comments';//&workitemid=5            
            $coreview_urls_arr['sprint_communications'] = 'bigfathom/sprint/mng_comments';//&sprintid=5            
            
            $coreview_urls_arr['project_dashboard'] = 'bigfathom/dashboards/oneproject'; //&projectid=58';
            $coreview_urls_arr['workitem_dashboard'] = 'bigfathom/dashboards/workitem'; //&workitemid=58';
            $coreview_urls_arr['sprint_dashboard'] = 'bigfathom/dashboards/sprint'; //&sprintid=58';

            $dashdata = $this->m_oDashHelp->getWorkitemDashboardDataBundle($this->m_workitemid);
            $projectid = $dashdata['workiteminfo']['owner_projectid'];
            
            $dd_analyzed_bundle = $dashdata['analyzed_bundle'];
            $dd_detail = $dd_analyzed_bundle['detail'];
            $dd_flat_wi2wi_map = $dd_detail['flat_wi2wi_map'];
            $dd_flat_allthings_map = $dd_detail['flat_allthings_map'];
            $dd_node_details_ar = $dd_detail['node_details'];

            $widlist = [];
            $gidlist = [];
            $tidlist = [];
            if(isset($dd_flat_allthings_map['workitem']))
            {
                $allworkitemid2type_map = $dd_flat_allthings_map['workitem'];
                foreach($allworkitemid2type_map as $id=>$relationshiptype)
                {
                    $widlist[] = $id;
                }
                $all_winfo = $this->m_oMapHelper->getWorkitemInfoForListOfIDs($widlist);
                foreach($allworkitemid2type_map as $id=>$relationshiptype)
                {
                    $winfo = $all_winfo[$id];
                    if($winfo['workitem_basetype'] == 'G')
                    {
                        $gidlist[] = $id;
                    } else
                    if($winfo['workitem_basetype'] == 'T')
                    {
                        $tidlist[] = $id;
                    }
                }            
            }

            $relevant_branch_comids=NULL;
            $relevant_personids=NULL; 
            $workitem_communicationbundle = $this->m_oMapHelper->getCommentSummaryBundleByWorkitem($tidlist,$only_active,$relevant_branch_comids,$relevant_personids);
            $map_open_request_summary_by_goalid = $workitem_communicationbundle['map_open_request_summary'];
            
            
            drupal_set_title("Workitem Open Items Dashboard");// for $project_nm Project");
            $main_workitem_info = $dashdata['workiteminfo'];
            $workitem_nm = $main_workitem_info['workitem_nm'];
            
            $intro_markup = "<table class='context-dashboard'>"
                    . "<tr><th>Workitem Name:</th><td>$workitem_nm</td></tr>"
                    . "<tr><th>Workitem ID:</th><td>$this->m_workitemid</td></tr>"
                    . "</table>"
                    . "<br>";
            
            $form["data_entry_area1"]['intro_container']['main'] = array('#type' => 'item',
                     '#markup' => $intro_markup);            

            //Open workitems
            $goals_maintable_markup = $this->getOpenWorkitemsMarkup($projectid, $dd_flat_wi2wi_map
                    ,$all_winfo,$dd_node_details_ar
                    ,$map_open_request_summary_by_goalid
                    ,$coreview_urls_arr);
            $form["data_entry_area1"]['table_container']['workitems_tablemarkup'] = array('#type' => 'item',
                     '#markup' => $goals_maintable_markup);
            
            //Open projects
            $projects_maintable_markup = $this->getOpenAntecedentProjectsMarkup($projectid, $dd_flat_wi2wi_map
                    ,$all_winfo,$dd_node_details_ar
                    ,$map_open_request_summary_by_goalid
                    ,$coreview_urls_arr);
            $form["data_entry_area1"]['table_container']['projects_maintable'] = array('#type' => 'item',
                     '#markup' => $projects_maintable_markup);
            
            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                                , array('query' => $rparams_ar, 'attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                        , '#markup' => $exit_link_markup);
            }
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getOpenAntecedentProjectsMarkup($root_projectid, $dd_flat_wi2wi_map
                    ,$all_goal_names,$dd_node_details_ar
                    ,$map_open_request_summary_by_goalid
                    ,$coreview_urls_arr)
    {
        
        $project_communication_baseurl = $coreview_urls_arr['project_communications'];
        $project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        
        $maintable_row_ar = array();
        foreach($all_goal_names as $workitemid=>$mainwinfo)
        {
            $main_basetype = $mainwinfo['workitem_basetype'];
            if($main_basetype == 'G' && $this->m_workitemid != $workitemid)
            {
                $wdetail = $dd_node_details_ar['workitem'][$workitemid];
                if($wdetail['is_project_yn'] === 1)
                {
                    $projectid = $all_goal_names[$workitemid]['owner_projectid'];
                    $workitem_nm = $all_goal_names[$workitemid]['workitem_nm'];

                    $start_dt = $wdetail['start_dt'];
                    $end_dt = $wdetail['end_dt'];

                    if(!isset($map_open_request_summary_by_goalid[$workitemid]))
                    {
                        $action_request_summary_info = NULL;
                    } else {
                        $action_request_summary_info = $map_open_request_summary_by_goalid[$workitemid];
                    }
                    $ar_info_markup 
                            = $this->getActionRequestMarkup('projectid',$projectid, $action_request_summary_info, $project_communication_baseurl);

                    $projectid_markup 
                            = $this->getDashboardMarkup('project','projectid',$projectid, $project_dashboard_baseurl);
                    
                    
                    $status_cd_factors = $wdetail['status_cd_factors'];
                    $status_workstarted_yn = $status_cd_factors['workstarted_yn'];
                    $status_cd = $status_cd_factors['code'];
                    $status_title_tx = $status_cd_factors['title_tx'];
                    $inherited_status_factors = $wdetail['branchstats']['inherited_status_factors'];
                    $implied_workstarted_yn = $inherited_status_factors['workstarted_yn'];
                    $implied_status_cd = $inherited_status_factors['implied_status_cd'];

                    $owner_markup = $wdetail['owner_personid'];
                    $most_recent_activity_dt = $wdetail['most_recent_activity_dt'];
                    $success_forecast_p = $wdetail['branchstats']['success_forecast_p'];

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

                    $maintable_row_ar[] = "<td>$projectid_markup</td>"
                            . "<td>$workitemid</td>"
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
        
        $maintable_markup = "<h2>Open Antecedent Projects</h2>";
        $maintable_markup .= '<table id="' . $this->m_oap_tableid . '" class="browserGrid">';
        $maintable_markup .= "<thead>"
                . "<tr>"
                . "<th><span title='The unique project ID number of the project'>ID</span></th>"
                . "<th><span title='The unique goal ID number of the project'>G#</span></th>"
                . "<th><span title='The name of this project'>Name</span></th>"
                . "<th><span title='Current status of the project'>Status</span></th>"
                . "<th><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this project owner'>Owner</span></th>"
                . "<th><span title='Date and time of most recent activity in the goal'>Any Activity</span></th>"
                . "<th><span title='Start date of the project'>Start Date</span></th>"
                . "<th><span title='End date of the projecct'>End Date</span></th>"
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

    
    private function getOpenWorkitemsMarkup($root_projectid, $dd_flat_wi2wi_map
                    ,$all_winfo
                    ,$dd_node_details_ar
                    ,$map_open_request_summary_by_goalid
                    ,$coreview_urls_arr)
    {
        $project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        $workitem_dashboard_baseurl = $coreview_urls_arr['workitem_dashboard'];
        $workitem_communication_baseurl = $coreview_urls_arr['workitem_communications'];
        $maintable_row_map = [];
        $maintable_row_map['G'] = [];
        $maintable_row_map['T'] = [];
        foreach($all_winfo as $workitemid=>$mainwinfo)
        {
            $main_basetype = $mainwinfo['workitem_basetype'];
            if($this->m_workitemid != $workitemid)
            {
                $workitem_detail = $dd_node_details_ar['workitem'][$workitemid];
                if($workitem_detail['is_project_yn'] !== 1)
                {
                    $workitem_nm = $all_winfo[$workitemid]['workitem_nm'];

                    //Yes, this is not just a task in the branh, it is contained by our key goal
                    $start_dt = $workitem_detail['start_dt'];
                    $end_dt = $workitem_detail['end_dt'];

                    if(isset($map_open_request_summary_by_goalid[$workitemid]))
                    {
                        $action_request_summary_info = $map_open_request_summary_by_goalid[$workitemid];
                    } else {
                        $action_request_summary_info = NULL;
                    }
                    $ar_info_markup 
                            = $this->getActionRequestMarkup('workitemid',$workitemid
                                    , $action_request_summary_info
                                    , $workitem_communication_baseurl);
                    
		    $workitemid_markup 
                            = $this->getDashboardMarkup('workitem','workitemid',$workitemid, $workitem_dashboard_baseurl);
                    
                    $status_cd_factors = $workitem_detail['status_cd_factors'];
                    $status_workstarted_yn = $status_cd_factors['workstarted_yn'];
                    $status_cd = $status_cd_factors['code'];
                    $status_title_tx = $status_cd_factors['title_tx'];
                    $inherited_status_factors = $workitem_detail['branchstats']['inherited_status_factors'];
                    $implied_workstarted_yn = $inherited_status_factors['workstarted_yn'];
                    $implied_status_cd = $inherited_status_factors['implied_status_cd'];

                    $goal_count_markup = $workitem_detail['branchstats']['total_antecedent_goal_count'];
                    $task_count_markup = $workitem_detail['branchstats']['total_antecedent_task_count'];

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

                    $antecedent_tasks_ar = [];
                    $antecedent_goals_ar = [];
                    $antecedent_projects_ar = [];
                    if(isset($dd_flat_wi2wi_map[$workitemid]))
                    {
                        foreach($dd_flat_wi2wi_map[$workitemid] as $ant_wid)
                        {
                            $winfo = $all_winfo[$ant_wid];
                            if($winfo['workitem_basetype'] === 'G')
                            {
                                $ant_goalid_markup 
                                    = $this->getDashboardMarkup('goal','workitemid',$ant_wid, $workitem_dashboard_baseurl);
                                $antecedent_goals_ar[] = $ant_goalid_markup;   
                                if($winfo['owner_projectid'] !== $root_projectid)
                                {
                                    $dap_projectid = $winfo['owner_projectid'];
                                    if(!array_key_exists($dap_projectid, $antecedent_projects_ar))
                                    {
                                        $ant_projectid_markup 
                                            = $this->getDashboardMarkup('project','projectid',$dap_projectid, $project_dashboard_baseurl);
                                        $antecedent_projects_ar[$dap_projectid] = $ant_projectid_markup;   
                                    }
                                }
                            }else
                            if($winfo['workitem_basetype'] === 'T')
                            {
                                $ant_goalid_markup 
                                    = $this->getDashboardMarkup('task','workitemid',$ant_wid, $workitem_dashboard_baseurl);
                                $antecedent_tasks_ar[] = $ant_goalid_markup;   
                            }
                        }
                    }
                    $antecedent_tasks_markup = implode(', ', $antecedent_tasks_ar);
                    $antecedent_goals_markup = implode(', ', $antecedent_goals_ar);
                    $antecedent_projects_markup = implode(', ', $antecedent_projects_ar);

                    $success_forecast_p_markup = UtilityGeneralFormulas::getRoundSigDigs($success_forecast_p);
                    
                    if(!$this->m_oMapHelper->isTerminalWorkitemStatus($status_cd))
                    {
                        $maintable_row_map[$main_basetype][] = "<td>$workitemid_markup</td>"
                                . "<td>$workitem_nm</td>"
                                . "<td>$antecedent_tasks_markup</td>"
                                . "<td>$antecedent_goals_markup</td>"
                                . "<td>$antecedent_projects_markup</td>"
                                . "<td>$status_markup</td>"
                                . "<td>$success_forecast_p_markup</td>"
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
            }
        }
        $allmarkup = "\n" . $this->getWorkitemTableMarkup("table-open-ant-tasks","Open Antecedent Tasks", $maintable_row_map['T']);
        $allmarkup .="\n" . $this->getWorkitemTableMarkup("table-open-ant-goals","Open Antecedent Goals", $maintable_row_map['G']);
        return $allmarkup;
    }
    
    private function getWorkitemTableMarkup($tableid, $title, $row_ar)
    {
        $maintable_markup = "<h2>$title</h2>";
        $maintable_markup .= '<table id="' . $tableid . '" class="browserGrid">';
        $maintable_markup .= "<thead>"
                . "<tr>"
                . "<th><span title='The unique ID number of the workitem'>ID</span></th>"
                . "<th><span title='The name of this goal'>Name</span></th>"
                . "<th><span title='The OPEN Direct Antecedent Tasks'>DATs</span></th>"
                . "<th><span title='The OPEN Direct Antecedent Goals'>DAGs</span></th>"
                . "<th><span title='The OPEN Direct Antecedent Projects'>DAPs</span></th>"
                . "<th><span title='Current status of the goal'>Status</span></th>"
                . "<th><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this goal owner'>Owner</span></th>"
                . "<th><span title='Count of all the OPEN antecedent goals'>Goals</span></th>"
                . "<th><span title='Count of all the OPEN antecedent tasks'>Tasks</span></th>"
                . "<th><span title='Date and time of most recent activity in the workitem'>Any Activity</span></th>"
                . "<th><span title='Start date of the task'>Start Date</span></th>"
                . "<th><span title='End date of the task'>End Date</span></th>"
                . "<th><span title='Open action requests'>AR</span></th>"
                . "</tr>"
                . "</thead>";
        if(count($row_ar)==0)
        {
            $maintable_markup .= "<tbody></tbody>";
        } else {
            $maintable_markup .= "<tbody><tr>".implode("</tr><tr>", $row_ar)."</tr></tbody>";
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
            $maintable_markup = "<h2>Highlights for Items where $person_markup role is {$roledetail['role_nm']}</h2>";
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
                            $colmarkup = l($colmarkup, $url, array('query' => array('todo' => $colnum, 'return' => $this->m_cmi['link_path'])));
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
