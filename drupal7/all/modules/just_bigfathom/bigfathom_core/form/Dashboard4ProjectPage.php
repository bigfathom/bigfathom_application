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
 *
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';

/**
 * Prioritized information for a project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Dashboard4ProjectPage extends \bigfathom\ASimpleFormPage
{

    protected $m_urls_arr   = NULL;
    protected $m_oContext   = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oDashHelp  = NULL;
    protected $m_personid   = NULL;
    protected $m_currentappuser_personid = NULL;
    protected $m_projectid          = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_aProjectRights     = NULL;

    public function __construct($projectid=NULL, $urls_override_arr=NULL)
    {
        $this->m_aWorkitemsRights = "VAED";
        $this->m_aProjectRights = "VAED";
        
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
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['add']['workitem'] = 'bigfathom/workitem/add';
        $urls_arr['edit']['workitem'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['workitem'] = 'bigfathom/workitem/delete';
        $urls_arr['edit']['goal'] = 'bigfathom/workitem/edit';
        $urls_arr['edit']['task'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['goal'] = 'bigfathom/workitem/view';
        $urls_arr['view']['task'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['goal'] = 'bigfathom/workitem/delete';
        $urls_arr['delete']['task'] = 'bigfathom/workitem/delete';
        $urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapWorkitemsGoals.html';
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        
        $this->m_urls_arr       = $urls_arr;
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
    
    private function getDashboardMarkup($thingname, $cmi, $keyname, $thingid, $baseurl)
    {
        $ar_goalcomment_link = l($thingid, $baseurl, array('query' => array($keyname => $thingid, 'return' => $cmi['link_path'])));
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
            $coreview_urls_arr['project_dashboard'] = 'bigfathom/dashboards/oneproject'; //&projectid=58';
            $coreview_urls_arr['workitem_dashboard'] = 'bigfathom/dashboards/workitem'; //&workitemid=58';
            $coreview_urls_arr['goal_dashboard'] = 'bigfathom/dashboards/workitem'; //&goalid=58';
            $coreview_urls_arr['task_dashboard'] = 'bigfathom/dashboards/workitem'; //&goalid=58'; same on purpose!
            $coreview_urls_arr['sprint_dashboard'] = 'bigfathom/dashboards/sprint'; //&goalid=58';
            $coreview_urls_arr['workitem_communications'] = 'bigfathom/workitem/mng_comments';//&goalid=5            
            
            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];

            $this->m_icon_url = [];
            $this->m_icon_url['hierarchy'] = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $this->m_icon_url['communicate']  = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $this->m_icon_url['dashboard']  = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $this->m_icon_url['view']  = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $this->m_icon_url['edit']  = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $this->m_icon_url['delete']  = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $dashdata = $this->m_oDashHelp->getProjectDashboardDataBundle($this->m_projectid);
            
            $dd_analyzed_bundle = $dashdata['analyzed_bundle'];
            //DebugHelper::debugPrintNeatly($dd_analyzed_bundle, FALSE, "LOOK dash dd_analyzed_bundle......");
            $dd_workitem_list_ar = $dd_analyzed_bundle['main_project_detail']['workitems'];
            //$ant_projectcoreinfo = $dd_analyzed_bundle['ant_projectcoreinfo'];
            //$workitem2project = $dd_analyzed_bundle['main_project_detail']['workitem2project'];

            $relevant_wids = array_keys($dd_workitem_list_ar);
            
            $relevant_branch_comids=NULL;
            $relevant_personids=NULL; 
            $workitem_communicationbundle = $this->m_oMapHelper->getCommentSummaryBundleByWorkitem($relevant_wids,$only_active,$relevant_branch_comids,$relevant_personids);
            $map_open_request_summary_by_wid = $workitem_communicationbundle['map_open_request_summary'];
            
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            drupal_set_title("Project Open Items Dashboard");// for $project_nm Project");
            
            $projectinfo = $dashdata['projectinfo'];
            $project_nm = $projectinfo['root_workitem_nm'];
            $project_mission_tx = $projectinfo['mission_tx'];
            $root_goalid = $projectinfo['root_goalid'];
            
            $intro_markup = "<div class='context-dashboard'><table>"
                    . "<tr><th>Project Name:</th><td>$project_nm</td></tr>"
                    . "<tr><th>Mission:</th><td>$project_mission_tx</td></tr>"
                    . "<tr><th>Project ID:</th><td>{$this->m_projectid}</td></tr>"
                    . "<tr><th>Root Goal ID:</th><td>$root_goalid</td></tr>"
                    . "</table></div>"
                    . "<br>";
            
            $form["data_entry_area1"]['intro_container']['main'] = array('#type' => 'item',
                     '#markup' => $intro_markup);            

            $maintable1_markup = $this->getOpenWorkitemsMarkup("table-ant-workitems",$cmi, $dd_analyzed_bundle
                , $map_open_request_summary_by_wid, $coreview_urls_arr);
            $form["data_entry_area1"]['table_container']['goal_table'] = array('#type' => 'item',
                     '#markup' => $maintable1_markup);
            
            $maintable2_markup = $this->getOpenAntecedentProjectsMarkup("table-ant-projects",$cmi, $dd_analyzed_bundle
                , $map_open_request_summary_by_wid, $coreview_urls_arr);
            $form["data_entry_area1"]['table_container']['project_table'] = array('#type' => 'item',
                     '#markup' => $maintable2_markup);
            
            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                        , '#markup' => $exit_link_markup);
            }

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getOpenWorkitemsMarkup($tableid, $cmi, $dd_analyzed_bundle
            , $map_open_request_summary_by_workitemid, $coreview_urls_arr)
    {
        
        $main_projectid = $dd_analyzed_bundle['main_project_detail']['metadata']['projectid'];
        $dd_workitem_list_ar = $dd_analyzed_bundle['main_project_detail']['workitems'];
        //$ant_projectcoreinfo = $dd_analyzed_bundle['ant_projectcoreinfo'];
        $workitem2project = $dd_analyzed_bundle['main_project_detail']['workitem2project'];
        $status_lookup = $dd_analyzed_bundle['main_project_detail']['status_lookup'];
                
        //$project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        $workitem_dashboard_baseurl = $coreview_urls_arr['workitem_dashboard'];
        $goal_dashboard_baseurl = $coreview_urls_arr['goal_dashboard'];
        $task_dashboard_baseurl = $coreview_urls_arr['task_dashboard'];
        $workitem_communication_baseurl = $coreview_urls_arr['workitem_communications'];
        
        $maintable_row_ar = array();
        foreach($dd_workitem_list_ar as $workitemid=>$workitem_detail)
        {
            if(isset($workitem_detail['owner_projectid']))
            {
                $owner_projectid = $workitem_detail['owner_projectid'];
                if($this->m_projectid == $owner_projectid)
                {
                    $workitem_nm = $workitem_detail['workitem_nm'];
                    //$basetype = $workitem_detail['workitem_basetype'];
                    $typeletter = $workitem_detail['typeletter'];
                    $typeletter_markup = $typeletter;

                    $start_dt = empty($workitem_detail['actual_start_dt']) ? $workitem_detail['planned_start_dt'] : $workitem_detail['actual_start_dt'];
                    $end_dt = empty($workitem_detail['actual_end_dt']) ? $workitem_detail['planned_end_dt'] : $workitem_detail['actual_end_dt'];

                    if(!key_exists($workitemid, $map_open_request_summary_by_workitemid))
                    {
                        $action_request_summary_info = [];
                    } else {
                        $action_request_summary_info = $map_open_request_summary_by_workitemid[$workitemid];
                    }
                    $ar_info_markup = $this->getActionRequestMarkup('workitemid',$workitemid, $action_request_summary_info, $workitem_communication_baseurl);

                    $wid_markup = $this->getDashboardMarkup('workitem', $cmi, 'workitemid', $workitemid, $workitem_dashboard_baseurl);


                    $workitem_daw = $workitem_detail['maps']['daw'];
                    //$workitem_count_markup = l($workitem_count, $workitem_dashboard_baseurl, array('query' => array('workitemid' => $workitemid, 'return' => $cmi['link_path'])));

                    $antecedent_goals_ar = [];
                    $antecedent_tasks_ar = [];
                    $antecedent_projects_ar = [];
                    foreach($workitem_daw as $wid)
                    {
                        $antworkitem = $dd_workitem_list_ar[$wid];
                        if($main_projectid != $workitem2project[$wid])
                        {
                            //Has a different project as the owner
                            $pid = $workitem2project[$wid];
                            $antecedent_projects_ar[$pid] = $pid;
                        } else {
                            $basetype = $antworkitem['workitem_basetype'];
                            if($basetype == 'G')
                            {
                                $antecedent_goals_ar[$wid] = $wid;
                            } else {
                                $antecedent_tasks_ar[$wid] = $wid;
                            }
                        }
                    }
                    $goal_count=count($antecedent_goals_ar);
                    $task_count=count($antecedent_tasks_ar);
                    $proj_count=count($antecedent_projects_ar);

                    $goal_count_markup = l($goal_count, $goal_dashboard_baseurl, array('query' => array('workitemid' => $workitemid, 'return' => $cmi['link_path'])));

                    $task_count_markup = l($task_count, $task_dashboard_baseurl, array('query' => array('workitemid' => $workitemid, 'return' => $cmi['link_path'])));

                    $owner_markup = $workitem_detail['owner_personid'];
                    $most_recent_activity_dt = $workitem_detail['updated_dt'];

                    $status_cd = $workitem_detail['status_cd'];
                    $status_title = $status_lookup[$status_cd]['title_tx'];

                    $status_markup = "<span title='{$status_title}'>$status_cd</span>";                

                    $workitem_forecast = $workitem_detail['forecast'];

                    $otsp = $workitem_forecast['local']['otsp'];

                    $antecedent_goals_markup = implode(', ', $antecedent_goals_ar);
                    $antecedent_tasks_markup = implode(', ', $antecedent_tasks_ar);
                    $antecedent_projects_markup = implode(', ', $antecedent_projects_ar);

                    $success_forecast_p_markup = UtilityGeneralFormulas::getRoundSigDigs($otsp);

                    if (strpos($this->m_aWorkitemsRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                    {
                        $sCommentsMarkup = '';
                        $sViewMarkup = '-';
                    } else {
                        $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}' href='$communicate_page_url'><img src='{$this->m_icon_url['communicate']}'/></a>";
                    }

                    if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                    {
                        $sViewMarkup = '-';
                    } else {
                        //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('equipmentid'=>$equipmentid)));
                        $view_page_url = url($this->m_urls_arr['view']['workitem'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sViewMarkup = "<a title='view {$workitemid}' href='$view_page_url'><img src='{$this->m_icon_url['view']}'/></a>";
                    }
                    if(strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                    {
                        $sEditMarkup = '-';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit']['workitem'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sEditMarkup = "<a title='edit {$workitemid}' href='$edit_page_url'><img src='{$this->m_icon_url['edit']}'/></a>";
                    }
                    if(strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                    {
                        $sDeleteMarkup = '-';
                    } else {
                        $delete_page_url = url($this->m_urls_arr['delete']['workitem'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sDeleteMarkup = "<a title='delete {$workitemid}' href='$delete_page_url'><img src='{$this->m_icon_url['delete']}'/></a>";
                    }

                    $action_markup = $sCommentsMarkup.' '
                                    . $sViewMarkup.' '
                                    . $sEditMarkup.' '
                                    . $sDeleteMarkup;

                    $maintable_row_ar[] = "<td>$wid_markup</td>"
                            . '<td>'.$typeletter_markup.'</td>'
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
                            . "<td class='action-options'>$action_markup</td>"
                        ;
                }
            }
        }

        $maintable_markup = "\n<h2>Open Workitems of Project</h2>";
        $maintable_markup .= "\n".'<table id="' . $tableid . '" class="browserGrid">';
        $maintable_markup .= "\n<thead>"
                . "\n<tr>"
                . "<th datatype='numid'>"
                    . "<span title='The unique ID number of the workitem'>ID</span></th>"
                . '<th colname="typeletter">'
                    . '<span title="G=Goal, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span></th>'
                . '<th colname="name">'.t('Name').'</th>'
                . "<th>"
                    . "<span title='The OPEN Direct Antecedant Tasks'>DATs</span></th>"
                . "<th>"
                    . "<span title='The OPEN Direct Antecedant Goals'>DAGs</span></th>"
                . "<th>"
                    . "<span title='The OPEN Direct Antecedant Projects'>DAPs</span></th>"
                . "<th>"
                    . "<span title='Current status of the workitem'>Status</span></th>"
                . "<th>"
                    . "<span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this workitem owner'>Owner</span></th>"
                . "<th><span title='Count of all the OPEN antecedent goals'>TAGs</span></th>"
                . "<th><span title='Count of all the OPEN tasks for the goal'>TATs</span></th>"
                . "<th><span title='Date and time of most recent activity in the workitem'>Any Activity</span></th>"
                . "<th><span title='Start date of the goal'>Start Date</span></th>"
                . "<th><span title='End date of the goal'>End Date</span></th>"
                . "<th><span title='Open action requests'>AR</span></th>"
                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                . "</tr>"
                . "\n</thead>";
        if(count($maintable_row_ar)==0)
        {
            $maintable_markup .= "<tbody></tbody>";
        } else {
            $maintable_markup .= "\n<tbody><tr>".implode("</tr><tr>", $maintable_row_ar)."</tr>\n</tbody>";
        }
        $maintable_markup .= "\n</table>";            
        $maintable_markup .= "</br>";
        return $maintable_markup;
    }

    /**
     * Projects are ANT nodes.
     */
    private function getOpenAntecedentProjectsMarkup($tableid, $cmi, $dd_analyzed_bundle
            , $map_open_request_summary_by_wid, $coreview_urls_arr)
    {

        //$main_projectid = $dd_analyzed_bundle['main_project_detail']['metadata']['projectid'];
        //$dd_workitem_list_ar = $dd_analyzed_bundle['main_project_detail']['workitems'];
        $ant_projectcoreinfo = $dd_analyzed_bundle['ant_projectcoreinfo'];
        //$workitem2project = $dd_analyzed_bundle['main_project_detail']['workitem2project'];
        $status_lookup = $dd_analyzed_bundle['main_project_detail']['status_lookup'];        
        
        $project_dashboard_baseurl = $coreview_urls_arr['project_dashboard'];
        $workitem_communication_baseurl = $coreview_urls_arr['workitem_communications'];
        $maintable_row_ar = array();
        foreach($ant_projectcoreinfo as $projectid=>$coreinfo)
        {
            $workitem_detail = $coreinfo['root_workitem'];
            $forecast = $coreinfo['forecast'];
            $workitem_nm = $workitem_detail['workitem_nm'];
            $workitemid = $workitem_detail['id'];
            
            $start_dt = empty($workitem_detail['actual_start_dt']) ? $workitem_detail['planned_start_dt'] : $workitem_detail['actual_start_dt'];
            $end_dt = empty($workitem_detail['actual_end_dt']) ? $workitem_detail['planned_end_dt'] : $workitem_detail['actual_end_dt'];

            $action_request_summary_info = $map_open_request_summary_by_wid[$workitemid];
            $ar_info_markup = $this->getActionRequestMarkup('workitemid',$workitemid, $action_request_summary_info, $workitem_communication_baseurl);

            $projectid_markup = $projectid;
                        //= $this->getDashboardMarkup('project',$cmi,'projectid',$owner_projectid, $project_dashboard_baseurl);

            $status_cd = $workitem_detail['status_cd'];
            $status_detail = $status_lookup[$status_cd];
            $status_title_tx = $status_detail['title_tx'];
            $status_markup = "<span title='{$status_title_tx}'>$status_cd</span>";

            $owner_markup = $workitem_detail['owner_personid'];
            $most_recent_activity_dt = $workitem_detail['updated_dt'];
            $otsp = $forecast['local']['otsp'];
            $otsp_markup = UtilityGeneralFormulas::getRoundSigDigs($otsp);

            $action_markup = "";

            $maintable_row_ar[] = "<td>$projectid_markup</td>"
                    . "<td>$workitemid</td>"
                    . "<td>$workitem_nm</td>"
                    . "<td>$status_markup</td>"
                    . "<td>$otsp_markup</td>"
                    . "<td>$owner_markup</td>"
                    . "<td>$most_recent_activity_dt</td>"
                    . "<td>$start_dt</td>"
                    . "<td>$end_dt</td>"
                    . "<td>$ar_info_markup</td>"
                    . "<td>$action_markup</td>"
                ;
        }

        $maintable_markup = "\n<h2>Open Antecedent Projects of this Project</h2>";
        $maintable_markup .= '<table id="' . $tableid . '" class="browserGrid">';
        $maintable_markup .= "\n<thead>"
                . "<tr>"
                . "<th datatype='numid'><span title='The unique project ID'>ID</span></th>"
                . "<th datatype='numid'><span title='The unique goal ID of the goal at the root of this project'>G#</span></th>"
                . "<th><span title='The project name'>Name</span></th>"
                . "<th><span title='Current status of the project'>Status</span></th>"
                . "<th><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                . "<th><span title='The person ID of this project owner'>Owner</span></th>"
                . "<th><span title='Date and time of most recent activity in the project'>Any Activity</span></th>"
                . "<th><span title='Start date of the goal'>Start Date</span></th>"
                . "<th><span title='End date of the goal'>End Date</span></th>"
                . "<th><span title='Open action requests'>AR</span></th>"
                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                . "</tr>"
                . "\n</thead>";
        if(count($maintable_row_ar)==0)
        {
            $maintable_markup .= "\n<tbody></tbody>";
        } else {
            $maintable_markup .= "\n<tbody><tr>".implode("</tr><tr>", $maintable_row_ar)."</tr></tbody>";
        }
        $maintable_markup .= "\n</table>";            
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
                            $colmarkup = l($colmarkup, $url, array('query' => array('todo' => $colnum, 'return' => $cmi['link_path'])));
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
