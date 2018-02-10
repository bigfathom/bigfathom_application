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
require_once 'helper/SprintPageHelper.php';

/**
 * This class returns the list of available work items
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageWorkitems2SprintPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper             = NULL;
    protected $m_urls_arr               = NULL;
    protected $m_aWorkitemsRights       = NULL;
    protected $m_oWorkitemPageHelper    = NULL;
    protected $m_oSprintPageHelper  = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_parent_projectid   = NULL;
    protected $m_oSnippetHelper     = NULL;
    protected $m_sprintid           = NULL;
    
    public function __construct($sprintid=NULL, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        if(empty($sprintid))
        {
            $sprintid = $this->m_oMapHelper->getDefaultSprintID($this->m_parent_projectid);
        }
        $this->m_sprintid = $sprintid;

        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');

        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['lock_membership'] = 'bigfathom/sprint/lock_membership';
        $urls_arr['unlock_membership'] = 'bigfathom/sprint/unlock_membership';
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
        //$urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapWorkitemsGoals.html';
        
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
        $aWorkitemsRights='VAED';
        $this->m_aWorkitemsRights  = $aWorkitemsRights;
        
        $this->m_oWorkitemPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_parent_projectid);
        $this->m_oSprintPageHelper = new \bigfathom\SprintPageHelper($urls_arr, NULL, $this->m_sprintid);
        
    }
    
    function getProcessedForecastBundle($forecast_info)
    {
        $bundle = [];
        $otsp_value = round($forecast_info['local']['otsp'],4);
        $otsp_logic = $forecast_info['local']['logic'];
        if(empty($otsp_logic) || !is_array($otsp_logic))
        {
            $otsp_logic = [];
        }
        $otsp_classname = $otsp_value > 0.9 ? "otsp-good" : ($otsp_value  < 0.4 ? "otsp-veryugly" : ($otsp_value  < 0.5 ? "otsp-ugly" : ($otsp_value  < 0.70 ? "otsp-bad" : "otsp-ambiguous")));
        $otsp_logic_txt_ar = [];
        foreach($otsp_logic as $onelogic)
        {
            $otsp_logic_txt_ar[] = print_r($onelogic,TRUE);
        }
        $tooltip_markup = implode(" and ", $otsp_logic_txt_ar);
        $otsp_int_value = round($otsp_value * 100);
        $otsp_markup = "[SORTNUM:{$otsp_int_value}]<span class='" . $otsp_classname . "' title='" . $tooltip_markup . "'>" . $otsp_value . "</span>";
        $bundle['otsp_value'] = $otsp_value;
        $bundle['otsp_markup'] = $otsp_markup;
        return $bundle;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'grid-sprint-members';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            
            if(empty($this->m_sprintid))
            {
                $sprint_record = NULL;
            } else {
                $sprint_record = $this->m_oMapHelper->getSprintRecord($this->m_sprintid);
            }
            
            $map_status = $this->m_oMapHelper->getWorkitemStatusByCode();
            $json_map_status = json_encode($map_status);
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $json_map_people = json_encode($people);
            $json_sprint_record = json_encode($sprint_record);
            
            //drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js(array('personid'=>$user->uid
                    ,'sprintid'=>$this->m_sprintid
                    ,'map_status'=>$json_map_status
                    ,'map_people'=>$json_map_people
                    ,'sprint_record'=>$json_sprint_record
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');            
            
            //drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkitems2SprintTable.js");
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
            global $base_url;

            if(empty($sprint_record))
            {
                $form['data_entry_area1']['info'] = array('#type' => 'item'
                        , '#markup' => "<h1>No sprints are currently declared for this project.</h1>");
            } else {
                $sprint_membership_locked_yn = $sprint_record['membership_locked_yn'];
                $sprint_status_terminal_yn = $sprint_record['terminal_yn'];
                if($sprint_membership_locked_yn == '0')
                {
                    if(empty($sprint_status_terminal_yn) || $sprint_status_terminal_yn == '0')
                    {
                        $sprint_editable = TRUE;
                    } else {
                        $sprint_editable = FALSE;
                    }
                } else {
                    $sprint_editable = FALSE;
                }
                $dashboard_elems = $this->m_oSprintPageHelper->getContextDashboardElements($this->m_sprintid, FALSE);
                $loaded_forecast = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
                if(!$loaded_forecast)
                {
                    throw new \Exception('Failed to load the ProjectForecaster class');
                }
                $oPlainProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_parent_projectid);
                $plain_projectforecast = $oPlainProjectForecaster->getDetail();
                $plain_forecast_workitems = $plain_projectforecast['main_project_detail']['workitems'];
                $overrides_ar = [];
                $overrides_ar['end_dt'] = $sprint_record['end_dt'];
                $flags = [];    //TODO
                $oSprintProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_parent_projectid, $flags, NULL, $overrides_ar);
                $sprint_projectforecast = $oSprintProjectForecaster->getDetail();
                $sprint_forecast_workitems = $sprint_projectforecast['main_project_detail']['workitems'];
                //DebugHelper::showNeatMarkup($sprint_forecast_workitems)    ;            
                //DebugHelper::debugPrintNeatly($plain_forecast_workitems,FALSE,"PLAIN");
                //DebugHelper::debugPrintNeatly($sprint_forecast_workitems,FALSE,"SPRINT");
                $form['data_entry_area1']['context_dashboard'] = $dashboard_elems;
                $totalsid = "totals-" . $main_tablename;
                $form["data_entry_area1"]['latest_stats'] = array(
                     '#type' => 'item', 
                     '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                     '#suffix' => '</div>', 
                     '#tree' => TRUE,
                );

                $form["data_entry_area1"]['table_container'] = array(
                    '#type' => 'item',
                    '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                    '#suffix' => '</div>',
                    '#tree' => TRUE,
                );

                $uah = new UserAccountHelper();
                $usrm = $uah->getPersonSystemRoleBundle($user->uid);
                //$uprm = $uah->getPersonProjectRoleBundle($user->uid);
                $is_systemadmin = $usrm['summary']['is_systemadmin'];

                $rparams_ar = [];
                $rparams_ar['sprintid'] = $this->m_sprintid;
                $rparams_encoded = urlencode(serialize($rparams_ar));

                $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
                $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
                $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
                $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
                $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

                $rows = "\n";
                $w_and_s_map_bundle = $this->m_oMapHelper->getWorkitem2SprintMapBundle($this->m_parent_projectid);
                if(array_key_exists($this->m_sprintid, $w_and_s_map_bundle['workitem2sprint']))
                {
                    $workitem2sprint = $w_and_s_map_bundle['workitem2sprint'][$this->m_sprintid];
                } else {
                    $workitem2sprint = [];
                }

                $bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);
                $all = $bundle['workitems'];
                $today = $updated_dt = date("Y-m-d", time());
                $min_date = $bundle['dates']['min_dt'];
                $max_date = $bundle['dates']['max_dt'];
                $status_lookup = $bundle['status_lookup'];

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
                //$this->m_oGanttChartHelper = new \bigfathom\GanttChartHelper($min_date, $max_date, $gantt_width, $max_height);
                $cmi = $this->m_oContext->getCurrentMenuItem();
                if($sprint_editable)
                {
                    $editable_col_flag = ' editable="1" ';
                    $canedit_classname_markup = "canedit";
                } else {
                    $editable_col_flag = "";
                    $canedit_classname_markup = "noedit";
                }

                foreach($all as $workitemid=>$record)
                {
                    $nativeid = $record['nativeid'];
                    $itemtype = $record['type'];
                    if(empty($plain_forecast_workitems[$workitemid]['forecast']))
                    {
                        //Not expected
                        $plain_forecast_info = [];
                        $plain_forecast_results = [];
                        $plain_otsp_value = 0;
                        $plain_otsp_markup = "<span title='NO INFORMATION AVAILABLE'>0</span>";
                    } else {
                        $plain_forecast_info = $plain_forecast_workitems[$workitemid]['forecast'];
                        $plain_forecast_results = $this->getProcessedForecastBundle($plain_forecast_info);
                        $plain_otsp_value = $plain_forecast_results['otsp_value'];
                        $plain_otsp_markup = $plain_forecast_results['otsp_markup'];
                    }
                    if(empty($sprint_forecast_workitems[$workitemid]['forecast']))
                    {
                        $sprint_forecast_info = [];
                        $sprint_forecast_results = [];
                        $sprint_otsp_value = 0;
                        $sprint_otsp_markup = "<span title='NO INFORMATION AVAILABLE'>0</span>";
                    } else {
                        $sprint_forecast_info = $sprint_forecast_workitems[$workitemid]['forecast'];
                        $sprint_forecast_results = $this->getProcessedForecastBundle($sprint_forecast_info);
                        $sprint_otsp_value = $sprint_forecast_results['otsp_value'];
                        $sprint_otsp_markup = $sprint_forecast_results['otsp_markup'];
                    }

                    $status_cd = $record['status_cd'];
                    if($status_cd != NULL)
                    {
                        $status_record = $status_lookup[$status_cd];
                        $status_title_tx = $status_record['title_tx'];
                        $status_happy_yn = $status_record['happy_yn'];
                        $status_terminal_yn = $status_record['terminal_yn'];
                        if($status_terminal_yn == '1')
                        {
                            if($status_happy_yn !== NULL)
                            {
                                if($status_happy_yn == '1')
                                {
                                    $status_classname = "status-terminal-happy-yes";
                                } else {
                                    $status_classname = "status-terminal-happy-no";
                                } 
                            } else {
                                $status_classname = "status-terminal";
                            }
                        } else {
                            if($status_happy_yn !== NULL)
                            {
                                if($status_happy_yn == '1')
                                {
                                    $status_classname = "status-happy-yes";
                                } else {
                                    $status_classname = "status-happy-no";
                                } 
                            } else {
                                $status_classname = "status-ambiguous";
                            }
                        }
                        $status_markup = "<span class='$status_classname' title='$status_title_tx'>$status_cd</span>";
                        $terminalyesno = ($status_terminal_yn == 1 ? 'Yes' : '<span class="colorful-available">No</span>');
                    } else {
                        $status_markup = "";
                        $terminalyesno = "";
                        $status_terminal_yn = '0';
                    }
                    if(array_key_exists($workitemid, $workitem2sprint))
                    {
                        $is_member = TRUE;
                        $is_member_markup = 1;
                        $member_scf = $workitem2sprint[$workitemid];
                    } else {
                        $is_member = FALSE;
                        $is_member_markup = 0;
                        $member_scf = NULL;
                    }
                    $owner_projectid = $record['owner_projectid'];
                    if($owner_projectid == $this->m_parent_projectid && ($is_member || ($sprint_editable && $status_terminal_yn == '0')))
                    {

                        $workitemid_markup = $nativeid;
                        $is_project_root = !empty($record['root_of_projectid']);
                        $typeletter = $record['typeletter'];
                        if($is_project_root)
                        {
                            $typeletter = 'P';    
                        }
                        $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                        $typeletter_markup = $typeletter . "<img src='$typeiconurl' /></span>";
                        $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
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
                        
                        
                        $limit_branch_effort_hours_cd = $record['limit_branch_effort_hours_cd'];
                        if($limit_branch_effort_hours_cd == 'L')
                        {
                            $branch_effort_hours_locked_markup = TRUE;
                        } else {
                            $branch_effort_hours_locked_markup = FALSE;
                        }

/////
                        $planned_fte_count = $record['planned_fte_count'];
                        $planned_fte_count_markup = $planned_fte_count;

                        $effort_hours_est = $record['effort_hours_est'];
                        $remaining_effort_hours = $record['remaining_effort_hours'];


                        $effort_hours_worked_act = $record['effort_hours_worked_act'];

                        if(empty($remaining_effort_hours))
                        {
                            if(empty($effort_hours_worked_act))
                            {
                                $remaining_effort_hours = $effort_hours_est - $effort_hours_worked_act;  
                            } else {
                                $remaining_effort_hours = $effort_hours_est;  
                            }
                        }

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
                        
/////

                        $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($start_dt);
                        $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($end_dt);

                        $allowrowedit_yn = ($sprint_editable || $is_systemadmin) ? 1 : 0;
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
                            $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                            $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                            if(count($daw_ar) == 0)
                            {
                                $sViewDashboardMarkup = "<span title='There are no open antecedents to this workitem'><img src='$no_dashboard_icon_url'/></span>";
                            } else {
                                $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
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
                            $view_page_url = url($this->m_urls_arr['view'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                            $sViewMarkup = "<a title='view {$workitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                        }
                        if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                        {
                            $sEditMarkup = '';
                        } else {
                            $edit_page_url = url($this->m_urls_arr['edit'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                            $sEditMarkup = "<a title='edit {$workitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                        }
                        if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                        {
                            $sDeleteMarkup = '';
                        } else {
                            $delete_page_url = url($this->m_urls_arr['delete'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                            $sDeleteMarkup = "<a title='delete {$workitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
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

                        $remaining_effort_hours_markup = round($remaining_effort_hours);
                        
                        $dci_markup = $record['importance'];
                        $iscc_markup = 0.95;

                        $rows   .= "\n"
                                . "<tr allowrowedit='$allowrowedit_yn' default_scf='$iscc_markup' "
                                            . " id='$nativeid' "
                                            . " data_otsp='{$plain_otsp_value}' "
                                            . " data_iscp='{$sprint_otsp_value}' "
                                            . " data_dfte='{$planned_fte_count}' "
                                            . " data_primary_owner='{$owner_personid}' "
                                            . " data_all_owners='{$all_owners_markup}' "
                                            . " data_sprintid='{$this->m_sprintid}' "
                                            . " typeletter='$typeletter'>"

                                . "<td>" . $workitemid_markup.'</td>'

                                . '<td>' . $typeletter_markup.'</td>'

                                . '<td>' . $workitem_nm_markup.'</td>'

                                . '<td>' . $owner_sortstr.$owner_markup.'</td>'

                                . '<td>' . $status_markup.'</td>'

                                . '<td>' . $plain_otsp_markup.'</td>'
                                                    
                                . '<td>' . $sprint_otsp_markup.'</td>'
                                                    
                                . '<td>' . $dci_markup.'</td>'

                                . '<td class="' . $canedit_classname_markup . '">'
                                . $is_member_markup . '</td>'
                                                    
                                . '<td>' . $start_dt_markup . '</td>'

                                . '<td>' . $end_dt_markup . '</td>'

                                . '<td>' . $remaining_effort_hours_markup . '</td>'

                                . '<td>' . $planned_fte_count_markup . '</td>'
                        
                                        
                                . "<td>"
                                . $ddw_markup.'</td>'
                                . "<td>"
                                . $daw_markup.'</td>'

                                .'<td class="action-options">'
                                    . $sCommentsMarkup.' '
                                    . $sHierarchyMarkup . ' '
                                    . $sViewMarkup.' '
                                    . $sEditMarkup.' '
                                    . $sDeleteMarkup.'</td>'
                                .'</tr>';
                    }
                }

                $form["data_entry_area1"]['table_container']['grid'] = array('#type' => 'item',
                         '#markup' => 
                        '<table id="' . $main_tablename . '" class="browserGrid">'
                        . '<thead><tr>'
                        . '<th class="nowrap" colname="id" datatype="numid">'
                            . '<span title="Unique ID of this work item">'.t('ID').'</span></th>'
                        . '<th class="nowrap" colname="typeletter">'
                            . '<span title="G=Goal, P=Goal which is root of a project, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span></th>'
                        . '<th class="nowrap" colname="name">'.t('Name').'</th>'
                        . '<th class="nowrap" colname="owner" datatype="sortablehtml">'
                            . '<span title="Owner of the work item">'.t('Owner').'</span></th>'
                        . '<th class="nowrap" colname="status_cd">'
                            . '<span title="The status code of the workitem">'.t('Status').'</span></th>'
                        . '<th class="nowrap" colname="otsp" datatype="formula" helpinfo="On-Time Success Probability for completion of this workitem by the declared end date of the work item">'.t('OTSP').'</th>'
                        . '<th class="nowrap" colname="iscp" datatype="formula" helpinfo="In-Sprint Completion Probability (how likely this workitem will be completed before the declared end date of this sprint)">'.t('ISCP').'</th>'
                        . '<th class="nowrap" colname="importance" datatype="double" named_validator="GTEZ">'
                            . '<span title="Declared importance of completing this workitem in the project">'.t('DCI').'</span></th>'
                        . '<th class="nowrap" colname="is_member_yn" ' . $editable_col_flag . ' datatype="boolean">'
                            . '<span title="Is sprint member">'.t('Member').'</span></th>'

                    
                        . '<th class="" colname="planned_start_dt" datatype="date">'
                            . '<span title="The planned start date for the work item">'.t('Start Date').'</span></th>'
                        . '<th colname="planned_end_dt" datatype="date">'
                            . '<span title="The planned end date for the work item">'.t('End Date').'</span></th>'
                        . '<th colname="remaining_effort_hours" datatype="formula">'
                            . '<span title="Estimated Remaining Effort to complete the workitem">'.t('ERE').'</span></th>'
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

                if($sprint_status_terminal_yn != '1')
                {
                    if($sprint_membership_locked_yn == '0')
                    {
                        if(isset($this->m_urls_arr['lock_membership']))
                        {
                            if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                            {
                                $lock_membership_link_markup = l('LOCK_ICON  Lock Membership'
                                        , $this->m_urls_arr['lock_membership']
                                        , array('query' => array(
                                            'sprintid' => $this->m_sprintid,
                                            'return' => $cmi['link_path'], 'rparams' => $rparams_encoded),
                                            'attributes'=>array('class'=>'action-button')
                                            ));
                                $final_lock_membership_link_markup = str_replace('LOCK_ICON', '<i class="fa fa-lock" aria-hidden="true"></i>', $lock_membership_link_markup);
                                $form['data_entry_area1']['action_buttons']['lock_membership'] = array('#type' => 'item'
                                        , '#markup' => $final_lock_membership_link_markup);
                            }
                        }
                    } else {
                        if(isset($this->m_urls_arr['unlock_membership']))
                        {
                            if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                            {
                                $unlock_membership_link_markup = l('UNLOCK_ICON  Unlock Membership'
                                        , $this->m_urls_arr['unlock_membership']
                                        , array('query' => array(
                                            'sprintid' => $this->m_sprintid,
                                            'return' => $cmi['link_path'], 'rparams' => $rparams_encoded),
                                            'attributes'=>array('class'=>'action-button')
                                            ));
                                $final_unlock_membership_link_markup = str_replace('UNLOCK_ICON', '<i class="fa fa-unlock" aria-hidden="true"></i>', $unlock_membership_link_markup);
                                $form['data_entry_area1']['action_buttons']['unlock_membership'] = array('#type' => 'item'
                                        , '#markup' => $final_unlock_membership_link_markup);
                            }
                        }
                    }
                }

                if($sprint_editable)
                {
                    if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
                    {
                        if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                        {
                            $add_link_markup = l('Create New Workitem'
                                    , $this->m_urls_arr['add']['workitem']
                                    , array('query' => array(
                                        'projectid' => $this->m_parent_projectid,
                                        'basetype' => 'G', 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)
                                        , 'attributes'=>array('class'=>'action-button')
                                        ));
                            $form['data_entry_area1']['action_buttons']['addworkitem'] = array('#type' => 'item'
                                    , '#markup' => $add_link_markup);
                        }
                    }
                }
            }

            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                        , '#markup' => $exit_link_markup);
            }
            
            $form["data_entry_area1"]['dialog_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="dialog-confirm-addmanymembers" title="Continue with Selection?" class="popupdef">',
                '#markup' => "<p id='blurb-confirm-addmanymembers'>Multiple workitems will be selected!<p>",
                '#suffix' => '</div>',
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
