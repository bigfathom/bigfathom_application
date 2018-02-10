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
class ManageWorkitemForecastsPage extends \bigfathom\ASimpleFormPage
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
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['workitem'] = 'bigfathom/workitem/delete';
        $urls_arr['edit']['goal'] = 'bigfathom/workitem/edit';
        $urls_arr['edit']['task'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['goal'] = 'bigfathom/workitem/view';
        $urls_arr['view']['task'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['goal'] = 'bigfathom/workitem/delete';
        $urls_arr['delete']['task'] = 'bigfathom/workitem/delete';
        $urls_arr['lock_all_estimates'] = 'bigfathom/project/lock_all_estimates';
        $urls_arr['unlock_all_estimates'] = 'bigfathom/project/unlock_all_estimates';
        $urls_arr['visualconsole'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['durationconsole'] = 'bigfathom/projects/workitems/duration';
        
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
        
        $loaded_forecast = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
        if(!$loaded_forecast)
        {
            throw new \Exception('Failed to load the ProjectForecaster class');
        }
        $this->m_oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_parent_projectid);  
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
            
            $main_tablename = 'grid-workitem-forecast';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            $map_status = $this->m_oMapHelper->getWorkitemStatusByCode();
            $json_map_status = json_encode($map_status);
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $json_map_people = json_encode($people);    
            
            //drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js(array('personid'=>$user->uid
                    ,'map_status'=>$json_map_status
                    ,'map_people'=>$json_map_people
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkitemForecastsTable.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
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
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            
            $bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);
            $sprint_bundle = $this->m_oMapHelper->getWorkitem2SprintMapBundle($this->m_parent_projectid);
            $all = $bundle['workitems'];
            $today = $updated_dt = date("Y-m-d", time());
            $min_date = $bundle['dates']['min_dt'];
            $max_date = $bundle['dates']['max_dt'];
            $status_lookup = $bundle['status_lookup'];
            
            $root_goalid = $bundle['metadata']['root_goalid'];
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

            $projectforecast = $this->m_oProjectForecaster->getDetail();
            $root_goalid = $projectforecast['main_project_detail']['metadata']['root_goalid'];
            $root_nodedetail = $projectforecast['main_project_detail']['workitems'][$root_goalid];
            $root_forecast = $root_nodedetail['forecast'];
            
            //$root_otsp_value = $root_forecast['local']['otsp'];
            //$root_otsp_reason = $root_forecast['local']['logic'];

            $awbid_bundle = $this->m_oMapHelper->getAllWorkitemsInProjectBundle($this->m_parent_projectid);
            $all = $awbid_bundle['all_workitems'];
            
            $forecast_workitems = $projectforecast['main_project_detail']['workitems'];
            
            $sprintid2status = $sprint_bundle['sprintid2status'];
            $sprintid2iteration = $sprint_bundle['sprintid2iteration'];
            $sprint2workitem = $sprint_bundle['sprint2workitem'];
            //foreach($forecast_workitems as $workitemid=>$record)
            foreach($all as $workitemid=>$record)
            {
                if(empty($forecast_workitems[$workitemid]['forecast']))
                {
                    $is_ant_project = TRUE;
                    $subprojectid = $record['owner_projectid'];
                    $fnug_content = $this->m_oMapHelper->getForecastNuggetsMapBundle($subprojectid);
                    $forecast_info = $fnug_content['by_projectid'][$subprojectid];
                    $otsp_value = $fnug_content['by_projectid'][$subprojectid]['root_otsp']['value'];
                    $otsp_rounded_value = round($otsp_value,4);
                    $tooltip_markup = $fnug_content['by_projectid'][$subprojectid]['root_otsp']['logic'][0]['detail'];
                    //drupal_set_message("Computed forecast for wid#$workitemid otsp=$otsp_value",'warning');
                    //DebugHelper::showNeatMarkup($forecast_info,"LOOK forecast for $workitemid");
                } else {
                    $frec = $forecast_workitems[$workitemid];
                    $is_ant_project = FALSE;
                    $forecast_info = $frec['forecast'];
                    if(!isset($forecast_info['local']['otsp']))
                    {
                        $title = "Missing ['local']['otsp'] in forecast info of wid#$workitemid!";
                        DebugHelper::showNeatMarkup($forecast_info,$title,'error');
                        throw new \Exception($title);
                    }
                    $otsp_value = $forecast_info['local']['otsp'];
                    $otsp_rounded_value = round($otsp_value,4);
                    $otsp_logic = $forecast_info['local']['logic'];
                    $tooltip_items = [];
                    foreach($otsp_logic as $one_otsplogicitem)
                    {
                        if(!isset($one_otsplogicitem['detail']))
                        {
                            $tooltip_items[] = 'TODO fix missing detail:'.print_r($one_otsplogicitem,TRUE);
                        } else {
                            $tooltip_items[] = $one_otsplogicitem['detail'];
                        }
                    }
                    $tooltip_markup = implode(" and ", $tooltip_items);
                    $computed_bounds = $frec['computed_bounds'];
                    if(!empty($computed_bounds['bottomup']['warnings']['detail']))
                    {
                        $warning_details = $computed_bounds['bottomup']['warnings']['detail'];
                        foreach($warning_details as $onewarning)
                        {
                            $wmsg = $onewarning['message'];
                            drupal_set_message("$wmsg","warning");
                        }
                    }
                    if(!empty($computed_bounds['bottomup']['errors']['detail']))
                    {
                        $error_details = $computed_bounds['bottomup']['errors']['detail'];
                        foreach($error_details as $oneerror)
                        {
                            $emsg = $oneerror['message'];
                            drupal_set_message("$emsg","error");
                        }
                    }
                }
                $otsp_int_value = round($otsp_value * 100);
                $otsp_classname = UtilityGeneralFormulas::getClassname4OTSP($otsp_value);//  $otsp_value > 0.9 ? "otsp-good" : ($otsp_value  < 0.4 ? "otsp-veryugly" : ($otsp_value  < 0.5 ? "otsp-ugly" : ($otsp_value  < 0.70 ? "otsp-bad" : "otsp-ambiguous")));
                $otsp_markup = "[SORTNUM:{$otsp_int_value}]<span class='" . $otsp_classname . "' title='" . $tooltip_markup . "'>" . $otsp_rounded_value . "</span>";
                
                if(!array_key_exists($workitemid, $sprint2workitem))
                {
                    $sprintflag = 'neversprint';
                    $sprintiterations = '';
                    $sprintiterations_markup = "[SORTNUM:0]";
                } else {
                    $sprintiterations = $sprint2workitem[$workitemid];
                    $sprintiterations_clean = [];
                    $inopensprint = FALSE;
                    $maxiter = 0;
                    foreach($sprintiterations as $sprintid=>$otsp)
                    {
                        $sprintstatusinfo = $sprintid2status[$sprintid];
                        if($sprintstatusinfo['terminal_yn'] == 0)
                        {
                            $inopensprint = TRUE;  
                        }
                        $oneiter = $sprintid2iteration[$sprintid];
                        if($oneiter > $maxiter)
                        {
                            $maxiter = $oneiter;
                        }
                        $sprintiterations_clean[] = $oneiter;
                    }
                    $sprintcount = count($sprintiterations_clean);
                    $maxiter = ($maxiter * 100) + $sprintcount;
                    $sprintiterations_markup = "[SORTNUM:" . $maxiter . "]" . implode(",",$sprintiterations_clean);
                    if($sprintcount == 0)
                    {
                        $sprintflag = 'neversprint';
                    } else {
                        if($inopensprint)
                        {
                            $sprintflag = 'opensprint';
                        } else {
                            $sprintflag = 'completedsprint';
                        }
                    }
                }

                if(!array_key_exists('id',$record))
                {
                    continue;
                } 
                $nativeid = $record['id'];
                $itemtype = $record['type'];
                $workitemid_markup = $nativeid;
                $is_project_root = !empty($record['root_of_projectid']);
                $typeletter = $record['typeletter'];
                if($is_project_root)
                {
                    $typeletter = 'P';    
                }
                if($typeletter == 'P')
                {
                    $type_ordernum = 0;
                } else 
                if($typeletter == 'G')
                {
                    $type_ordernum = 1;
                } else 
                if($typeletter == 'T')
                {
                    $type_ordernum = 2;
                } else 
                if($typeletter == 'Q')
                {
                    $type_ordernum = 3;
                } else 
                if($typeletter == 'X')
                {
                    $type_ordernum = 4;
                } else {
                    //New?
                    $type_ordernum = 99;
                }

                //$typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, TRUE, FALSE, $is_ant_project);
                $typeletter_markup = "[SORTNUM:{$type_ordernum}]" . $typeletter . " <img src='$typeiconurl' /></span>";
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
                if($is_project_root)
                {
                    $typename4gantt = 'proj';
                } else {
                    $typename4gantt = $itembasetype;
                }
                $all_owners_ar = [];
                $all_owners_ar[] = $owner_personid;                
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
                    $delegates = [];
                    foreach($map_delegate_owner as $delegate_ownerid)
                    {
                        if(!$isowner && $user->uid === $delegate_ownerid)
                        {
                            $isowner = TRUE;  
                            $isyours = 1;
                        }
                        if($delegate_ownerid !== $owner_personid)
                        {
                            $all_owners_ar[] = $delegate_ownerid;
                            $delegateowner_persondetail = $people[$delegate_ownerid];
                            $delegateowner_personname = $delegateowner_persondetail['first_nm'] . " " . $delegateowner_persondetail['last_nm'];
                            $delegates[] = "{$delegateowner_personname}";
                        }
                    }
                    $doc = count($map_delegate_owner);
                    $ownercount+=$doc;
                    if($doc < 2)
                    {
                        $owner_txt .= "1 delegate owner " . implode(" and ", $delegates);
                    } else {
                        $owner_txt .= count($map_delegate_owner) . " delegate owners: " . implode(" and ", $delegates);
                    }
                    $owner_personname .= "+" . count($delegates);
                }
                $all_owners_markup = implode(",", $all_owners_ar);
                $owner_markup = "<span title='$owner_txt'>".$owner_personname."</span>";
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
                $branch_effort_hours_est = $record['branch_effort_hours_est'];
                $limit_branch_effort_hours_cd = $record['limit_branch_effort_hours_cd'];
                if($limit_branch_effort_hours_cd == 'L')
                {
                    $branch_effort_hours_locked_markup = TRUE;
                } else {
                    $branch_effort_hours_locked_markup = FALSE;
                }

                $planned_fte_count = $record['planned_fte_count'];
                $planned_fte_count_markup = $planned_fte_count;

                $effort_hours_est = $record['effort_hours_est'];
                $remaining_effort_hours = $record['remaining_effort_hours'];

                $effort_hours_worked_est = $record['effort_hours_worked_est'];
                $effort_hours_worked_act = $record['effort_hours_worked_act'];

                $planned_start_dt = $record['planned_start_dt'];
                $planned_end_dt = $record['planned_end_dt'];

                $actual_start_dt = $record['actual_start_dt'];
                $actual_end_dt = $record['actual_end_dt'];

                if(empty($planned_start_dt))
                {
                    $start_dt = $actual_start_dt;
                } else {
                    $start_dt = $planned_start_dt;
                }
                if(empty($planned_end_dt))
                {
                    $end_dt = $actual_end_dt;
                } else {
                    $end_dt = $planned_end_dt;
                }

                if(!empty($effort_hours_worked_act))
                {
                    $worked_hours = $effort_hours_worked_act;
                    $worked_hours_type_cd = 'A';
                    $effort_hours_locked_markup = true;
                } else {
                    $worked_hours = $effort_hours_worked_est;
                    $worked_hours_type_cd = 'E';
                    if($record['effort_hours_est_locked_yn'] == 1)
                    {
                        $effort_hours_locked_markup = true;
                    } else {
                        $effort_hours_locked_markup = false;
                    }
                }

                if(empty($effort_hours_est))
                {
                    $effort_hours_est = 0;
                }
                if(empty($worked_hours))
                {
                    $worked_hours = 0;
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

                $allowrowedit_yn = !$is_ant_project && ($has_project_owner_rights || $isowner || $is_systemadmin) ? 1 : 0;
                if($allowrowedit_yn)
                {
                    $owner_sortstr = "<span sorttext='canedit:{$owner_personname}'></span>";
                } else {
                    $owner_sortstr = "<span sorttext='locked:{$owner_personname}'></span>";
                }

                if (strpos($this->m_aWorkitemsRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                    $sViewDashboardMarkup = '';
                } else {
                    $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

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
                        . " title='view dependencies for goal#{$workitemid} in project#{$this->m_parent_projectid}' "
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
                if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                    $sEditMarkup = "<a title='edit #{$workitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                    $sDeleteMarkup = "<a title='delete #{$workitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }

                if(count($daw_ar) == 0)
                {
                    $daw_markup = '';
                } else {
                    asort($daw_ar);
                    $daw_markup = "[SORTNUM:" . count($daw_ar) . "]<span title='" . count($daw_ar) . " items'>" . implode(', ', $daw_ar) . "</span>";                
                }

                if(count($ddw_ar) == 0)
                {
                    $ddw_markup = '';
                } else {
                    asort($ddw_ar);
                    $ddw_markup = "[SORTNUM:" . count($ddw_ar) . "]<span title='" . count($ddw_ar) . " items'>" . implode(', ', $ddw_ar) . "</span>";                
                }

                $htmlfriendly_workitemid = $nativeid;
                if($typeletter == 'G' || $typeletter == 'P')
                {
                    $branch_classname = 'canedit';
                } else {
                    $branch_classname = 'notapplicable';
                }
                $rows   .= "\n"
                        . "<tr allowrowedit='$allowrowedit_yn' "
                            . " id='$htmlfriendly_workitemid' "
                            . " data_dfte='{$planned_fte_count}' "
                            . " typeletter='$typeletter' "
                            . " status_cd='$status_cd' "
                            . " isyours='$isyours' "
                            . " sprintflag='$sprintflag' "
                            . " data_primary_owner='{$owner_personid}' "
                            . " data_all_owners='{$all_owners_markup}' "                       
                            . " ownercount='$ownercount' >"

                        . "<td>"
                        . $workitemid_markup.'</td>'

                        . '<td>'
                        . $typeletter_markup.'</td>'

                        . '<td>'
                        . $workitem_nm_markup.'</td>'

                        . '<td>'
                        . $owner_sortstr.$owner_markup.'</td>'

                        . '<td>'
                        . $status_markup.'</td>'

                        . '<td>'
                        . $otsp_markup.'</td>'

                        . '<td>'
                        . $sprintiterations_markup.'</td>'

                        . '<td class="canedit">'
                        . $start_dt_markup . '</td>'

                        . '<td class="canedit">'
                        . $end_dt_markup . '</td>'

                        . '<td class="canedit">'
                        . $remaining_effort_hours . '</td>'

                        . '<td>'
                        . $planned_fte_count_markup . '</td>'

                        . "<td>"
                        . $ddw_markup.'</td>'
                        . "<td>"
                        . $daw_markup.'</td>'

                        .'<td class="action-options">'
                            . $sCommentsMarkup.' '
                            //. $sViewDashboardMarkup . ' '
                            . $sHierarchyMarkup . ' '
                            . $sViewMarkup.' '
                            . $sEditMarkup.' '
                            . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['bw']['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead>'
                    . '<th class="nowrap" colname="id" datatype="numid">'
                        . '<span title="Unique ID of this work item">'.t('ID').'</span></th>'
                    . '<th class="nowrap" colname="typeletter" datatype="formula">'
                        . '<span title="G=Goal, P=Goal which is root of a project, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span></th>'
                    . '<th class="nowrap" colname="name">'.t('Name').'</th>'
                    . '<th class="nowrap" colname="owner" datatype="sortablehtml">'
                        . '<span title="Owner of the work item">'.t('Owner').'</span></th>'
                    . '<th class="nowrap" colname="status_cd">'
                        . '<span title="The status code of the workitem">'.t('Status').'</span></th>'
                    . '<th class="nowrap" colname="otsp" datatype="formula" helpinfo="On-Time Success Probability">'.t('OTSP').'</th>'
                    . '<th class="nowrap" colname="sprints" datatype="formula" helpinfo="Sprints in which this workitem is a member">'.t('S#').'</th>'
                    . '<th class="" colname="planned_start_dt" editable="1" datatype="date">'
                        . '<span title="The planned start date for the work item">'.t('Start Date').'</span></th>'
                    . '<th colname="planned_end_dt" editable="1" datatype="date">'
                        . '<span title="The planned end date for the work item">'.t('End Date').'</span></th>'
                    . '<th colname="remaining_effort_hours" editable="1" datatype="integer">'
                        . '<span title="Estimated Remaining Effort as of today to complete the workitem">'.t('ERE').'</span></th>'
                    . '<th class="nowrap" colname="planned_fte_count" datatype="formula" helpinfo="Declared Full Time Equivalent">'.t('DFTE').'</th>'
                    . '<th class="nowrap" colname="ddw" datatype="formula" title="Direct Dependent Workitems">' . t('DDW').'</th>'
                    . '<th class="nowrap" colname="daw" datatype="formula" title="Direct Antecedent Workitems">' . t('DAW').'</th>'
                    . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
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

            if (isset($this->m_urls_arr['visualconsole'])) {
                $initial_button_markup = l('ICON_LINK Jump to Visual Console', $this->m_urls_arr['visualconsole']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $final_button_markup = str_replace('ICON_LINK', '<i class="fa fa-link" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['visualconsole'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if (isset($this->m_urls_arr['durationconsole'])) {
                //fa-calendar-o
                $initial_button_markup = l('ICON_DURATION Jump to Duration Table', $this->m_urls_arr['durationconsole']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $final_button_markup = str_replace('ICON_DURATION', '<i class="fa fa-table" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['durationconsole'] = array('#type' => 'item'
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

            $refresh_page_message = "<a href='#' class='hidden' onclick='location.reload();return0;' id='otsp-outofdate-message'>OTSP Values are Out Of Date -- Click Here to Re-calculate the OTSP Values</a>";
            $form['data_entry_area1']['action_buttons']['refreshpage'] = array('#type' => 'item'
                    , '#markup' => $refresh_page_message);
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
