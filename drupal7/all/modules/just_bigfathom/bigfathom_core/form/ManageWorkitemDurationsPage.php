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
require_once 'helper/WorkitemPageHelper.php';

/**
 * This class returns the list of available work items
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageWorkitemDurationsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_parent_projectid   = NULL;
    protected $m_oSnippetHelper     = NULL;
    
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();

        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['add']['workitem'] = 'bigfathom/workitem/add';
        $urls_arr['edit']['workitem'] = 'bigfathom/workitem/edit';
        $urls_arr['duplicate']['workitem'] = 'bigfathom/workitem/duplicate';
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['workitem'] = 'bigfathom/workitem/delete';
        $urls_arr['edit']['goal'] = 'bigfathom/workitem/edit';
        $urls_arr['edit']['task'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['goal'] = 'bigfathom/workitem/view';
        $urls_arr['view']['task'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['goal'] = 'bigfathom/workitem/delete';
        $urls_arr['delete']['task'] = 'bigfathom/workitem/delete';
        $urls_arr['autofill_workestimates'] = 'bigfathom/autofill/projectdata';
        $urls_arr['lock_all_estimates'] = 'bigfathom/project/lock_all_estimates';
        $urls_arr['unlock_all_estimates'] = 'bigfathom/project/unlock_all_estimates';
        $urls_arr['visualconsole'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['forecastdetail'] = 'bigfathom/projects/workitems/forecast';
        $urls_arr['projectbaseline'] = 'bigfathom/projects/projbaselines';
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapWorkitemsGoals.html';
        $aWorkitemsRights='VAED';
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aWorkitemsRights  = $aWorkitemsRights;
        
        $this->m_oPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_parent_projectid);
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $user;
            global $base_url;
            
            $main_tablename = 'grid-workitem-duration';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            $map_status = $this->m_oMapHelper->getWorkitemStatusByCode();
            $json_map_status = json_encode($map_status);
            
            //drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js(array('personid'=>$user->uid
                    , 'projectid'=>$this->m_parent_projectid
                    , 'map_status'=>$json_map_status
                    , 'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkitemDurationsTable.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            drupal_add_js("$base_url/$module_path/form/js/dialog/SimpleConfirmation.js");
            
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['visualization-container']))
            {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if(!isset($html_classname_overrides['table-container']))
            {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if(!isset($html_classname_overrides['container-inline']))
            {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if(!isset($html_classname_overrides['action-button']))
            {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );

            $my_error_divid = "insight-errors-" . $main_tablename;   //grid-workitem-duration
            $form["data_entry_area1"]['insights']['errors'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $my_error_divid . '">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            $my_warningdivid = "insight-warnings-" . $main_tablename;   //grid-workitem-duration
            $form["data_entry_area1"]['insights']['warnings'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $my_warningdivid . '">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            $my_status_divid = "insight-status-" . $main_tablename;   //grid-workitem-duration
            $form["data_entry_area1"]['insights']['status'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $my_status_divid . '">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $bigwrapperid = "bigwrapper-" . $main_tablename;
            $form["data_entry_area1"]['bw'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $bigwrapperid . '" class="bigwrapper-tablestuff">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['bw']['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['bw']['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];

            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            //$communicate0_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate0');
            //$communicate1_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate1');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            //$cell_edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('cell_edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
            $duplicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('duplicate');

            $comm_empty_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_empty');
            $comm_content_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_hascontent');
            $comm_action_high_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_high');
            $comm_action_medium_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_medium');
            $comm_action_low_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_low');
            $comm_action_closed_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_closed');
            
            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            
            $active_yn = 1;
            $bundle = $this->m_oMapHelper->getAllWorkitemsInProjectBundle($this->m_parent_projectid, $active_yn);
            $all = $bundle['all_workitems'];
            
            $today = $updated_dt = date("Y-m-d", time());
            $min_date = $bundle['dates']['min_dt'];
            $max_date = $bundle['dates']['max_dt'];
            $status_lookup = $bundle['status_lookup'];
            
            //$root_goalid = $bundle['metadata']['root_goalid'];
            $root_goalid = $bundle['root_goalid'];
            $root_workitem = $all[$root_goalid];
            $primary_owner = $root_workitem['owner_personid'];
            $project_owner_idlookup[$primary_owner] = $primary_owner;
            if(isset($root_workitem['maps']['delegate_owner']))
            {
                foreach($root_workitem['maps']['delegate_owner'] as $dopid)
                {
                    $project_owner_idlookup[$dopid] = $dopid;
                }
            }
            
            $gantt_width=200;
            $max_height=20;
            if(empty($min_date))
            {
                if(!empty($max_date))
                {
                    $min_date = $max_date;
                } else {
                    $min_date = $today;
                }
            }
            if(empty($max_date))
            {
                if(!empty($min_date))
                {
                    $max_date = $min_date;
                } else {
                    $max_date = $today;
                }
            }
            $this->m_oGanttChartHelper = new \bigfathom\GanttChartHelper($min_date, $max_date, $gantt_width, $max_height);
            $cmi = $this->m_oContext->getCurrentMenuItem();
            $has_project_owner_rights = isset($project_owner_idlookup[$user->uid]);

            //Now populate the grid
            foreach($all as $workitemid=>$record)
            {
                
                $nativeid = $record['nativeid'];
                $itemtype = $record['type'];
                $owner_projectid = $record['owner_projectid'];
                $workitemid_markup = "[SORTNUM:{$nativeid}]<span data='$nativeid' class='click-to-filter' title='Click to filter table contents to just branch containing #$nativeid'>$nativeid</span>";
                    
                $is_project_root = !empty($record['root_of_projectid']);
                $typeletter = $record['typeletter'];
                $is_ant_project = FALSE;
                if($is_project_root)
                {
                    $typeletter = 'P';    
                    $is_ant_project = ($owner_projectid != $this->m_parent_projectid);
                    if($is_ant_project)
                    {
                        $typeletter_tooltip = "Antecedent project#$owner_projectid of the selected project";
                    } else {
                        $typeletter_tooltip = "Root of the selected project#$owner_projectid";
                    }
                }
                if($typeletter == 'P')
                {
                    $type_ordernum = $is_ant_project ? 88 : 0;
                } else 
                if($typeletter == 'G')
                {
                    $type_ordernum = 1;
                    $typeletter_tooltip = 'Goal';
                } else 
                if($typeletter == 'T')
                {
                    $type_ordernum = 2;
                    $typeletter_tooltip = 'Task';
                } else 
                if($typeletter == 'Q')
                {
                    $type_ordernum = 3;
                    $typeletter_tooltip = 'Non-human activity';
                } else 
                if($typeletter == 'X')
                {
                    $type_ordernum = 4;
                    $typeletter_tooltip = 'External activity';
                } else {
                    //New?
                    $type_ordernum = 99;
                }
                
                $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, TRUE, FALSE, $is_ant_project);
                $typeletter_markup = "[SORTNUM:{$type_ordernum}]<span title='$typeletter_tooltip'>" . $typeletter . " <img alt='' src='$typeiconurl' /></span>";
                $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
                $status_cd = $record['status_cd'];
                if($status_cd != NULL)
                {
                    $status_record = $status_lookup[$status_cd];
                    $status_terminal_yn = $status_record['terminal_yn'];
                    $mb = \bigfathom\MarkupHelper::getStatusCodeMarkupBundle($status_record);
                    $status_markup = $mb['status_code'];
                    $terminalyesno = $mb['terminal_yesno'];
                } else {
                    $status_markup = "";
                    $terminalyesno = "";
                }
                $owner_personid = $record['owner_personid'];
                $map_delegate_owner = $record['maps']['delegate_owner'];
                $map_comm_summary = $record['maps']['comm_summary'];
                //drupal_set_message("LOOK comm summary $wid " . print_r($map_comm_summary,TRUE));
                if($is_project_root)
                {
                    $typename4gantt = 'proj';
                } else {
                    $typename4gantt = $itembasetype;
                }
                $isowner = $user->uid == $owner_personid;
                $owner_persondetail = $people[$owner_personid];
                $owner_personname = $owner_persondetail['first_nm'] . " " . $owner_persondetail['last_nm'];
                $isyours = ($owner_personid == $user->uid);
                $ownercount = 1;
                $owner_txt = "#{$owner_personid} and ";
                if(count($map_delegate_owner) == 0)
                {
                    $owner_txt .= "no delegate owners";
                } else {
                    $delgates = [];
                    foreach($map_delegate_owner as $delegate_ownerid)
                    {
                        if(!$isowner && $user->uid === $delegate_ownerid)
                        {
                            $isowner = TRUE;  
                            $isyours = 1;
                        }
                        $delegateowner_persondetail = $people[$delegate_ownerid];
                        $delegateowner_personname = $delegateowner_persondetail['first_nm'] . " " . $delegateowner_persondetail['last_nm'];
                        $delgates[] = "{$delegateowner_personname}";
                    }
                    $doc = count($map_delegate_owner);
                    $ownercount+=$doc;
                    if($doc < 2)
                    {
                        $owner_txt .= "1 delegate owner " . implode(" and ", $delgates);
                    } else {
                        $owner_txt .= count($map_delegate_owner) . " delegate owners: " . implode(" and ", $delgates);
                    }
                    $owner_personname .= "+" . count($delgates);
                }
                $daw_ar = $record['maps']['daw'];
                $ddw_ar = $record['maps']['ddw'];
                
                $workitem_nm = $record['workitem_nm'];
                $purpose_tx = $record['purpose_tx'];
                if(empty($purpose_tx))
                {
                    $trimmed_purpose_tx = "No purpose description available";
                } else {
                    $purpose_tx = str_replace("'","",trim($purpose_tx));
                    if(count($purpose_tx) > 250)
                    {
                        $trimmed_purpose_tx = substr($purpose_tx,0,250) . "...";
                    } else {
                        $trimmed_purpose_tx = $purpose_tx;
                    }
                }
                $workitem_nm_markup = "<span title='$trimmed_purpose_tx'>$workitem_nm</span>";                
                
                if($is_ant_project)
                {
                    $branch_effort_hours_est = 0;
                    $limit_branch_effort_hours_cd = 'I';
                    $limit_branch_effort_hours_cd_markup = $limit_branch_effort_hours_cd;
                } else {
                    $branch_effort_hours_est = $record['branch_effort_hours_est'];
                    $limit_branch_effort_hours_cd = $record['limit_branch_effort_hours_cd'];
                    $limit_branch_effort_hours_cd_markup = $limit_branch_effort_hours_cd;
                }
                
                $planned_fte_count_markup = $record['planned_fte_count'];
                
                $effort_hours_est = $record['effort_hours_est'];
                $effort_hours_worked_est = $record['effort_hours_worked_est'];
                $effort_hours_worked_act = $record['effort_hours_worked_act'];
                
                $planned_start_dt = $record['planned_start_dt'];
                $planned_end_dt = $record['planned_end_dt'];

                $actual_start_dt = $record['actual_start_dt'];
                $actual_end_dt = $record['actual_end_dt'];

                if(!empty($effort_hours_worked_act))
                {
                    $worked_hours = $effort_hours_worked_act;
                    $worked_hours_type_cd = 'A';
                    $effort_hours_locked_markup = true;
                    $effort_hours_locked_yn = 1;
                } else {
                    $worked_hours = $effort_hours_worked_est;
                    $worked_hours_type_cd = 'E';
                    if($record['effort_hours_est_locked_yn'] == 1)
                    {
                        $effort_hours_locked_markup = true;
                        $effort_hours_locked_yn = 1;
                    } else {
                        $effort_hours_locked_markup = false;
                        $effort_hours_locked_yn = 0;
                    }
                }
                if(!empty($actual_start_dt))
                {
                    $start_dt = $actual_start_dt;
                    $start_dt_type_cd = 'A';
                    $start_dt_locked_markup = 1;
                } else {
                    $start_dt = $planned_start_dt;
                    $start_dt_type_cd = 'E';
                    if($record['planned_start_dt_locked_yn'] == 1)
                    {
                        $start_dt_locked_markup = 1;
                    } else {
                        $start_dt_locked_markup = 0;
                    }
                }
                if(!empty($actual_end_dt))
                {
                    $end_dt = $actual_end_dt;
                    $end_dt_type_cd = 'A';
                    $end_dt_locked_markup = 1;
                } else {
                    $end_dt = $planned_end_dt;
                    $end_dt_type_cd = 'E';
                    if($record['planned_end_dt_locked_yn'] == 1)
                    {
                        $end_dt_locked_markup = 1;
                    } else {
                        $end_dt_locked_markup = 0;
                    }
                }
                
                if(empty($branch_effort_hours_est))
                {
                    $branch_effort_hours_est = 0;
                }
                
                if($is_ant_project)
                {
                    $branch_effort_hours_markup = '';
                } else {
                    $branch_effort_hours_markup = $branch_effort_hours_est;
                }
                
                if(empty($effort_hours_est))
                {
                    $effort_hours_est = 0;
                }
                if($is_ant_project)
                {
                    $effort_hours_est = "";
                }
                
                if(empty($worked_hours))
                {
                    $worked_hours = 0;
                }
                
                $effort_hours_markup = $effort_hours_est; //"<span title='TODO days'>$effort_hours</span>";
                $worked_hours_markup = $worked_hours; //"<span title='TODO days'>$effort_hours</span>";
                
                if($is_ant_project)
                {
                    $remaining_effort_hours = 0;
                } else {
                    $remaining_effort_hours = $record['remaining_effort_hours'];
                }

                $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($start_dt);
                $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($end_dt);

                $durationdays = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt) + 1;
                if($durationdays > 0)
                {
                    $hoursperfte = 8;
                    $hoursperday = $effort_hours_est / $hoursperfte;
                    $min_fte_markup = $hoursperday / $hoursperfte;
                } else {
                    $min_fte_markup = "";   //<span title='Insufficient data to compute'>-</span>";
                }

                $allowrowedit_yn = !$is_ant_project && ($has_project_owner_rights || $isowner || $is_systemadmin ? 1 : 0);

                $owner_markup = "[SORTSTR:{$owner_personname}_{$allowrowedit_yn}]<span title='$owner_txt'>".$owner_personname."</span>";
                
                if(strpos($this->m_aWorkitemsRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $comm_score = 0;
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                    $sViewDashboardMarkup = '';
                } else {
                    $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    //DebugHelper::showNeatMarkup($map_comm_summary,"DEBUG INFO FOR $workitemid")  ;                  
                    $comments_markup_bundle = MarkupHelper::getCommLinkMarkupBundle($communicate_page_url, $map_comm_summary, $workitemid);
                    $comm_score = $comments_markup_bundle['comm_score'];
                    $sCommentsMarkup = $comments_markup_bundle['markup'];
                    if(count($daw_ar) == 0)
                    {
                        $sViewDashboardMarkup = "<span title='There are no open antecedents to this workitem'><img src='$no_dashboard_icon_url'/></span>";
                    } else {
                        $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sViewDashboardMarkup = "<a title='jump to dashboard for #{$workitemid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";
                    }
                    
                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                            , array('query'=>array('projectid'=>($this->m_parent_projectid), 'jump2workitemid'=>$workitemid)));
                    $sHierarchyMarkup = "<a "
                        . " title='view dependencies for workitem#{$workitemid} in project#{$this->m_parent_projectid}' "
                        . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    
                }
                
                if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('equipmentid'=>$equipmentid)));
                    $view_page_url = url($this->m_urls_arr['view'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view #{$workitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(!$is_ant_project)
                {
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit'][$itembasetype]))
                    {
                        $sEditMarkup = '';
                        $daw_onclick = '';
                        $ddw_onclick = '';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                        $sEditMarkup = "<a title='edit #{$workitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                        $daw_onclick = "bigfathom_util.table.daw_edit(" . $nativeid . ")";
                        $ddw_onclick = "bigfathom_util.table.ddw_edit(" . $nativeid . ")";
                    }
                } else {
                    $sEditMarkup = '';
                    $daw_onclick = '';
                    $ddw_onclick = '';
                    $daw_onclick = '';
                    $ddw_onclick = "bigfathom_util.table.ddw_edit(" . $nativeid . ")";
                }
                if($is_project_root || strpos($this->m_aWorkitemsRights,'A') === FALSE || !isset($this->m_urls_arr['duplicate']))
                {
                    $sDuplicateMarkup = '';
                } else {
                    $duplicate_page_url = url($this->m_urls_arr['duplicate']['workitem'], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                    $sDuplicateMarkup = "<a title='duplicate #{$workitemid}' href='$duplicate_page_url'><img src='$duplicate_icon_url'/></a>";
                }
                if($is_project_root || !$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                    $sDeleteMarkup = "<a title='delete #{$workitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }

                $raw_daw_tx = "";
                if($is_ant_project)
                {
                    $daw_markup = "<span title='details contained within project#$owner_projectid with root workitem#$nativeid'>NA</span>";
                } else {
                    if(count($daw_ar) == 1)
                    {
                        $daw_tooltip = " 1 link";
                    } else {
                        $daw_tooltip = count($daw_ar) . " links";
                    }
                    if(count($daw_ar) == 0)
                    {
                        $daw_display = ' - ';
                    } else {
                        asort($daw_ar);
                        $raw_daw_tx = implode(',', $daw_ar);    //No space
                        $daw_display = implode(', ', $daw_ar);
                    }
                    $daw_markup = "[SORTNUM:" . count($daw_ar) . "]<span class='click-for-action' title='$daw_tooltip' onclick=".'"'.$daw_onclick.'"'.">$daw_display</span>";
                }

                $raw_ddw_tx = "";
                if($is_project_root && !$is_ant_project)
                {
                    $ddw_markup = "";
                } else {
                    if(count($ddw_ar) == 1)
                    {
                        $ddw_tooltip = " 1 link";
                    } else {
                        $ddw_tooltip = count($ddw_ar) . " links";
                    }
                    if(count($ddw_ar) == 0)
                    {
                        $ddw_display = ' - ';
                    } else {
                        asort($ddw_ar);
                        $raw_ddw_tx = implode(',', $ddw_ar); //No space
                        $ddw_display = implode(', ', $ddw_ar);
                    }
                    $ddw_markup = "[SORTNUM:" . count($ddw_ar) . "]<span class='click-for-action' title='$ddw_tooltip' onclick=".'"'.$ddw_onclick.'"'.">$ddw_display</span>";
                }
                
                $est_flags = [];
                $est_flags['startdate'] = ($start_dt_type_cd == 'E');
                $est_flags['enddate'] = ($end_dt_type_cd == 'E');
                $est_flags['middle'] = ($worked_hours_type_cd = 'E');
                $pin_flags = [];
                $pin_flags['startdate'] = ($start_dt_locked_markup == 1);
                $pin_flags['enddate'] = ($end_dt_locked_markup == 1);
                $gantt_sortnum = strtotime($start_dt);
                $gantt_markup = 'calculating'; // Let the grid calculate it on the client side
                /*
                $gantt_markup = "[SORTNUM:" . $gantt_sortnum . "]" 
                        . $this->m_oGanttChartHelper
                            ->getGanttBarMarkup($typename4gantt, $start_dt, $end_dt, $est_flags, $pin_flags);
                */
                $htmlfriendly_workitemid = $nativeid;
                if(!$is_ant_project && ($typeletter == 'G' || $typeletter == 'P'))
                {
                    $branch_classname = 'canedit';
                } else {
                    $branch_classname = 'notapplicable';
                }
                
                $action_sortnum = $is_project_root ? 0 : $workitemid;
                $action_option_markup = "[SORTNUM:$comm_score]" . $sCommentsMarkup . ' '
                            //. $sViewDashboardMarkup . ' '
                            . $sHierarchyMarkup . ' '
                            . $sViewMarkup . ' '
                            . $sEditMarkup . ' '
                            . $sDuplicateMarkup . ' '    
                            . $sDeleteMarkup;
                
                $is_ant_project_yn = $is_ant_project ? 1: 0;
                
                $rows   .= "\n"
                        . "<tr allowrowedit='$allowrowedit_yn' "
                        . " id='$htmlfriendly_workitemid' "
                        . " typeletter='$typeletter' "
                        . " status_cd='$status_cd' "
                        . " status_terminal_yn='$status_terminal_yn' "
                        . " isyours='$isyours' "
                        . " ownercount='$ownercount' "
                        . " data_is_ant_project_yn='$is_ant_project_yn' "
                        . " data_effort_hours_est='{$effort_hours_est}' "
                        . " data_effort_hours_est_locked_yn='{$effort_hours_locked_yn}' "
                        . " data_worked_hours='{$worked_hours}' "
                        . " data_worked_hours_type_cd='{$worked_hours_type_cd}' "
                        . " data_ddw='{$raw_ddw_tx}' "
                        . " data_daw='{$raw_daw_tx}' "
                        . " >"                        
                        . "<td>"
                        . $workitemid_markup.'</td>'
                        
                        . '<td>'
                        . $typeletter_markup.'</td>'
                        
                        . '<td>'
                        . $workitem_nm_markup.'</td>'
                        
                        . '<td>'
                        . $owner_markup.'</td>'
                        
                        . '<td>'
                        . $status_markup.'</td>'
                        
                        . '<td class="' . $branch_classname . '">'
                        . $branch_effort_hours_markup.'</td>'
                        
                        . '<td class="' . $branch_classname . '">'
                        . $limit_branch_effort_hours_cd_markup.'</td>'
                       . '<td class="canedit">'
                                
                        . $remaining_effort_hours . '</td>'
                        
                        . '<td class="canedit">'
                        . $start_dt_markup . '</td>'
                        . '<td class="canedit">'
                        . $start_dt_type_cd . '</td>'
                        . '<td class="canedit">'
                        . $start_dt_locked_markup . '</td>'
                        
                        . '<td class="canedit">'
                        . $end_dt_markup . '</td>'
                        . '<td class="canedit">'
                        . $end_dt_type_cd . '</td>'
                        . '<td class="canedit">'
                        . $end_dt_locked_markup . '</td>'
                        
                        . '<td>'
                        . $planned_fte_count_markup . '</td>'
                        
                        . '<td>'
                        . $min_fte_markup . '</td>'
                        
                        . "<td>"
                        . $gantt_markup.'</td>'
                        
                        . "<td>"
                        . $ddw_markup.'</td>'
                        . "<td>"
                        . $daw_markup.'</td>'
                        
                        .'<td class="action-options">'
                            . $action_option_markup . '</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['bw']['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead>'
                    . '<th class="nowrap" colname="id" datatype="formula">'
                        . '<span title="Unique ID of this work item">'.t('ID').'</span></th>'
                    . '<th class="nowrap" colname="typeletter" datatype="formula">'
                        . '<span title="G=Goal, P=Goal which is root of a project, T=Task directly dependent on an internal resource, X=Task directly dependent on an external resource, Q=Task directly dependent on a non-human resource">'.t('Type').'</span></th>'
                    . '<th class="nowrap" colname="name" datatype="string">'
                        . '<span title="How we commonly refer to this workitem in conversation">'.t('Name').'</span></th>'
                    . '<th class="nowrap" colname="owner" datatype="formula">'
                        . '<span title="Owner of the work item">'.t('Owner').'</span></th>'
                    . '<th class="nowrap" colname="status_cd">'
                        . '<span title="The status code of the workitem">'.t('Status').'</span></th>'
                    . '<th class="nowrap" colname="branch_effort_hours_est" editable="1" datatype="double" named_validator="GTEZ">'
                        . '<span title="Branch Effort Hours (the remaining effort for the work item and all its antecedents)">'.t('BEH').'</span></th>'
                    . '<th class="nowrap" colname="limit_branch_effort_hours_cd" editable="1" datatype="string">'
                        . '<span title="Branch Constraint Type : I=Ignored, U=Unlocked (a fluid value), L=Locked (a firm hours total the branch should not exceed)">'.t('BCT').'</span></th>'
                    . '<th class="nowrap" colname="remaining_effort_hours" editable="1" datatype="integer">'
                        . '<span title="Estimated Remaining Effort (in hours) as of today to complete the workitem">'.t('ERE').'</span></th>'
                    . '<th class="nowrap" colname="start_dt" editable="1" datatype="date">'
                        . '<span title="The start date for the work item">'.t('Start Date').'</span></th>'
                    . '<th class="nowrap" colname="start_dt_type_cd" editable="1" datatype="string">'
                        . '<span title="Start Date Type Code (A=Actual, E=Estimated)">'.t('SDT').'</span></th>'
                    . '<th class="nowrap" colname="planned_start_dt_locked_yn" editable="1" datatype="boolean">'
                        . '<span title="Start Date Locked">'.t('SDL').'</span></th>'
                    . '<th class="nowrap" colname="end_dt" editable="1" datatype="date">'
                        . '<span title="The end date for the work item">'.t('End Date').'</span></th>'
                    . '<th class="nowrap" colname="end_dt_type_cd" editable="1" datatype="string">'
                        . '<span title="End Date Type Code (A=Actual, E=Estimated)">'.t('EDT').'</span></th>'
                    . '<th class="nowrap" colname="planned_end_dt_locked_yn" editable="1" datatype="boolean">'
                        . '<span title="End Date Locked">'.t('EDL').'</span></th>'
                    . '<th class="nowrap" colname="planned_fte_count" datatype="formula" helpinfo="Declared Full Time Equivalent">'.t('DFTE').'</th>'
                    . '<th class="nowrap" colname="calc_mfte" datatype="formula" helpinfo="Computed Minimum Full Time Equivalent (computed from bounds as a person working 8 hours per day without regard to actual resource availability limitations)">'.t('CMFTE').'</th>'
                    . '<th width="' . $gantt_width . 'px" class="nowrap" colname="calc_gantt" datatype="formula">'
                        . '<span title="Work timing visualization">'.t('Gantt').'</span></th>'
                    . '<th class="nowrap" colname="ddw" datatype="formula" title="Direct Dependent Workitems">' . t('DDW').'</th>'
                    . '<th class="nowrap" colname="daw" datatype="formula" title="Direct Antecedent Workitems">' . t('DAW').'</th>'
                    . '<th datatype="formula" class="action-options">' . t('Action Options').'</th>'
                    . '</tr>'
                    . '</thead>'
                    . '<tbody>'
                    . $rows
                    .  '</tbody>'
                    . '</table>'
                    . '<br>');

            
            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            $allow_lockall = $has_project_owner_rights || $is_systemadmin;
            $allow_unlockall = $has_project_owner_rights || $is_systemadmin;
            $allow_autofill = module_exists('bigfathom_autofill') && ($has_project_owner_rights || $is_systemadmin);
            if(strpos($this->m_aWorkitemsRights,'E') !== FALSE)
            {
                if($allow_lockall && isset($this->m_urls_arr['lock_all_estimates']))
                {
                    $autofill_workestimates_lock_markup = l('LOCK_ICON  Lock All Estimates'
                            , $this->m_urls_arr['lock_all_estimates']
                            , array('query' => array(
                                'projectid' => $this->m_parent_projectid,
                                'return' => $cmi['link_path'], 'rparams' => $rparams_encoded),
                                'attributes'=>array('class'=>'action-button')
                                ));
                    $final_autofill_workestimates_lock_markup = str_replace('LOCK_ICON', '<i class="fa fa-lock" aria-hidden="true"></i>', $autofill_workestimates_lock_markup);
                    $form['data_entry_area1']['action_buttons']['lock_all_estimates'] = array('#type' => 'item'
                            , '#markup' => $final_autofill_workestimates_lock_markup);
                }

                if($allow_unlockall &&  isset($this->m_urls_arr['unlock_all_estimates']))
                {
                    $autofill_workestimates_unlock_markup = l('UNLOCK_ICON  Unlock All Estimates'
                            , $this->m_urls_arr['unlock_all_estimates']
                            , array('query' => array(
                                'projectid' => $this->m_parent_projectid,
                                'return' => $cmi['link_path'], 'rparams' => $rparams_encoded),
                                'attributes'=>array('class'=>'action-button')
                                ));
                    $final_autofill_workestimates_unlock_markup = str_replace('UNLOCK_ICON', '<i class="fa fa-unlock" aria-hidden="true"></i>', $autofill_workestimates_unlock_markup);
                    $form['data_entry_area1']['action_buttons']['unlock_all_estimates'] = array('#type' => 'item'
                            , '#markup' => $final_autofill_workestimates_unlock_markup);
                }

                if($allow_autofill && isset($this->m_urls_arr['autofill_workestimates']))
                {
                    $url = $this->m_urls_arr['autofill_workestimates'];
                    $onclick = "bigfathom_util.table.autofill_wbs('$url'," . $this->m_parent_projectid 
                            . ",'" . $cmi['link_path'] . "','" . $rparams_encoded . "')";
                    $autofill_workestimates_link_markup = "<span class='click-for-action'>"
                            . "<input title='Click to see what auto-fill options are available to you' type='button' value='Auto-fill' onclick=".'"'.$onclick.'"'.">"
                            . "</span>";
                    $form['data_entry_area1']['action_buttons']['autofill_workestimates'] = array('#type' => 'item'
                            , '#markup' => $autofill_workestimates_link_markup);
                }
            }
            
            if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                {
                    $url = $this->m_urls_arr['add']['workitem'];
                    $initial_button_markup = l('ICON_ADD Create New Workitem'
                            , $url
                            , array('query' => array(
                                'projectid' => $this->m_parent_projectid,
                                'basetype' => 'G', 
                                'return' => $cmi['link_path'], 'rparams' => $rparams_encoded),
                                'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addworkitem'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }

            if (isset($this->m_urls_arr['visualconsole'])) {
                $initial_button_markup = l('ICON_LINK Jump to Visual Console', $this->m_urls_arr['visualconsole']
                                , array('attributes'=>array('class'=>'action-button','title'=>'See visual influence relationship diagram for this project'))
                        );
                $final_button_markup = str_replace('ICON_LINK', '<i class="fa fa-link" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['visualconsole'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if (isset($this->m_urls_arr['forecastdetail'])) {
                $initial_button_markup = l('ICON_FORECAST Jump to Forecast Table', $this->m_urls_arr['forecastdetail']
                                , array('attributes'=>array('class'=>'action-button','title'=>'See current forecast details of this project'))
                        );
                $final_button_markup = str_replace('ICON_FORECAST', '<i class="fa fa-info-circle" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['forecastdetail'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if (isset($this->m_urls_arr['projectbaseline'])) {
                $initial_button_markup = l('ICON_BASELINE Jump to Baselines', $this->m_urls_arr['projectbaseline']
                                , array('attributes'=>array('class'=>'action-button','title'=>'See and create baselines of this project'))
                        );
                $final_button_markup = str_replace('ICON_BASELINE', '<i class="fa fa-camera" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['projectbaseline'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                        , '#markup' => $exit_link_markup);
            }

            $snippet_popup_divs = [];
            $snippet_popup_divs[] = array('dialog-confirm-autofill-wbs'
                ,'Auto-fill Workitem Values'
                ,$this->m_oSnippetHelper->getHtmlSnippet("popup_autofill_workitems"));            
            $snippet_popup_divs[] = array('dialog-edit-ddw'
                ,'Edit Direct Dependent Workitems List'
                ,$this->m_oSnippetHelper->getHtmlSnippet("popup_edit_ddw"));            
            $snippet_popup_divs[] = array('dialog-edit-daw'
                ,'Edit Direct Antecedent Workitems List'
                ,$this->m_oSnippetHelper->getHtmlSnippet("popup_edit_daw"));            
            $snippet_popup_divs[] = array('dialog-confirm-filtertable'
                ,'Filter Table Contents'
                ,$this->m_oSnippetHelper->getHtmlSnippet("popup_filter_on_branch"));    
            foreach($snippet_popup_divs as $detail)
            {
                $id = $detail[0];
                $title = $detail[1];
                $markup = $detail[2];
                $form["formarea1"]['popupdefs'][$id] = array('#type' => 'item'
                        , '#prefix' => '<div id="' . $id . '" title="' . $title . '" class="popupdef">'
                        , '#markup' => $markup
                        , '#suffix' => '</div>'
                    );            
            }
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
