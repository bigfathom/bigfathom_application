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
require_once 'helper/ProjectPageHelper.php';

/**
 * This class implements a page to manage workitem type conversions
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageWorkTypeConversionsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_oWorkitemPageHelper      = NULL;
    protected $m_oProjectPageHelper = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_parent_projectid   = NULL;
    protected $m_oSnippetHelper     = NULL;
    
    public function __construct($projectid=NULL, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        module_load_include('php','bigfathom_core','core/ProjectInsight');
        if(empty($projectid))
        {
            $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        } else {
            $this->m_parent_projectid = $projectid;
        }

        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');

        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/project/mng_comments';
        $urls_arr['visual_show_dependencies'] = 'bigfathom/viewproject_dependencies';
        $urls_arr['visual_edit_dependencies'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['lock_membership'] = 'bigfathom/project/lock_membership';
        $urls_arr['unlock_membership'] = 'bigfathom/project/unlock_membership';
        $urls_arr['add']['project'] = 'bigfathom/projects/addsub';
        $urls_arr['edit']['project'] = 'bigfathom/editproject';
        $urls_arr['view']['project'] = 'bigfathom/viewproject';
        $urls_arr['delete']['project'] = 'bigfathom/deleteproject';
        $urls_arr['convert_intoproject'] = 'bigfathom/convert_intoproject';
        $urls_arr['convert_intogoal'] = 'bigfathom/convert_intogoal';
        $urls_arr['create_template'] = 'bigfathom/createtemplatefromproject';
        $urls_arr['create_snippet'] = 'bigfathom/createtemplatefromworkitem';
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
        $this->m_oProjectPageHelper = new \bigfathom\ProjectPageHelper($urls_arr, NULL, $this->m_parent_projectid);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'grid-subproject-members';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkTypeConversionsTable.js");
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
            global $base_url;

            $project_record = $this->m_oMapHelper->getProjectRecord($this->m_parent_projectid);
            $top_root_goalid = $project_record['root_goalid'];
    //        DebugHelper::debugPrintNeatly($project_record);       
            $project_status_terminal_yn = $project_record['status_terminal_yn'];
            if(empty($project_status_terminal_yn) || $project_status_terminal_yn == '0')
            {
                $project_editable = TRUE;
            } else {
                $project_editable = FALSE;
            }
            if(!empty($project_record["actual_end_dt"]))
            {
                $project_end_dt = $project_record["actual_end_dt"];
            } else {
                $project_end_dt = $project_record["planned_end_dt"];
            }
            $dashboard_elems = $this->m_oProjectPageHelper->getContextDashboardElements($this->m_parent_projectid, FALSE);
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

            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];

            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_parent_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $convert_intogoal_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intogoal');
            $convert_intoproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intoproject');
            $no_convert_intogoal_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_intogoal');
            $no_convert_intoproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_intoproject');
            $create_template_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intotemplate');
            $create_snippet_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intosnippet');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $subproj_and_proj_map_bundle = $this->m_oMapHelper->getSubprojects2ProjectMapBundle($this->m_parent_projectid);
            if(array_key_exists($this->m_parent_projectid, $subproj_and_proj_map_bundle['subproject2project']))
            {
                $subproject2project = $subproj_and_proj_map_bundle['subproject2project'][$this->m_parent_projectid];
            } else {
                $subproject2project = [];
            }
            
            $active_yn=1;
            $bundle = $this->m_oMapHelper->getAllWorkitemsInProjectBundle($this->m_parent_projectid, $active_yn);
            $all = $bundle['all_workitems'];
            $today = $updated_dt = date("Y-m-d", time());
            $status_lookup = $bundle['status_lookup'];
            
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            if($project_editable)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            
            $oPI = new \bigfathom\ProjectInsight($this->m_parent_projectid);
            $pi_bundle = $oPI->getAllInsightsBundle();
            $sp2p = $pi_bundle['relationships_map']['projects']['all_bare_maps']['sp2p'];
//DebugHelper::showNeatMarkup($pi_bundle,"LOOK PROJECT INSIGHT");
            $prunable_branch_map = $pi_bundle['prunable_branch_map'];
            foreach($all as $wid=>$record)
            {
                $owner_projectid = $record["owner_projectid"];
//DebugHelper::showNeatMarkup($record,"LOOK record here now wid#$wid");                
                if(empty($owner_projectid))
                {
                    drupal_set_message("LOOK ERROR owner_projectid!!!!! wid=$wid record=" . print_r($record,TRUE),"error");
                }
                if($owner_projectid != $this->m_parent_projectid)
                {
                    //subproject
                    $root_goalid = $wid;
                    $itemtype = "proj";
                    $typeletter = "P";
                    $all_ant_workitem_count = $this->m_oMapHelper->getWorkitemCountInProject($owner_projectid) - 1;
                } else if($record["typeletter"] == 'G'){
                    //normal goal
                    $itemtype = "goal";
                    $typeletter = "G";
                    $all_ant_wids_map = isset($pi_bundle['relationships_map']['workitems'][$wid]['ants']) ? $pi_bundle['relationships_map']['workitems'][$wid]['ants'] : [];
                    $all_ant_workitem_count = count($all_ant_wids_map);
                } else {
                    //Do not show this one
                    $typeletter = NULL;
                }
                $allow_actions = $typeletter == 'P' || ($typeletter == 'G' && isset($prunable_branch_map[$wid]));
                if($typeletter != NULL && $allow_actions && $project_editable && $top_root_goalid != $wid)
                {

                    $wid_markup = $wid;
                    $ddw_ar = $record['maps']['ddw'];

                    $status_cd = $record['status_cd'];
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

                    if(array_key_exists($wid, $subproject2project))
                    {
                        $is_member = TRUE;
                        $is_member_markup = 1;
                        $member_scf = $subproject2project[$wid]["ot_scf"];
                        $is_connected = $subproject2project[$wid]["is_connected"];
                    } else {
                        $is_member = FALSE;
                        $is_member_markup = 0;
                        $member_scf = NULL;
                        $is_connected = FALSE;
                    }
                    
                    $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                    $typeletter_markup = $typeletter . " <img src='$typeiconurl' /></span>";
                    //$itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
                    $owner_personid = $record['owner_personid'];
                    $map_delegate_owner = $record['maps']['delegate_owner'];

                    $isowner = $user->uid == $owner_personid;
                    $owner_persondetail = $people[$owner_personid];
                    $owner_personname = $owner_persondetail['first_nm'] . " " . $owner_persondetail['last_nm'];
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
                            }
                            $delegateowner_persondetail = $people[$delegate_ownerid];
                            $delegateowner_personname = $delegateowner_persondetail['first_nm'] . " " . $delegateowner_persondetail['last_nm'];
                            $delgates[] = "{$delegateowner_personname}";
                        }
                        $doc = count($map_delegate_owner);
                        if($doc < 2)
                        {
                            $owner_txt .= "1 delegate owner " . implode(" and ", $delgates);
                        } else {
                            $owner_txt .= count($map_delegate_owner) . " delegate owners: " . implode(" and ", $delgates);
                        }
                        $owner_personname .= "+" . count($delgates);
                    }
                    $owner_markup = "<span title='$owner_txt'>".$owner_personname."</span>";

                    $workitem_nm = $record['workitem_nm'];

                    $planned_start_dt = $record['planned_start_dt'];
                    $planned_end_dt = $record['planned_end_dt'];

                    $actual_start_dt = $record['actual_start_dt'];
                    $actual_end_dt = $record['actual_end_dt'];

                    if(!empty($actual_start_dt))
                    {
                        $start_dt = $actual_start_dt;
                        $start_dt_type_cd = 'A';
                    } else {
                        $start_dt = $planned_start_dt;
                        $start_dt_type_cd = 'E';
                    }
                    if(!empty($actual_end_dt))
                    {
                        $end_dt = $actual_end_dt;
                        $end_dt_type_cd = 'A';
                    } else {
                        $end_dt = $planned_end_dt;
                        $end_dt_type_cd = 'E';
                    }

                    $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($start_dt);
                    $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($end_dt);

                    $allowrowedit_yn = ($isowner || $is_systemadmin) ? 1 : 0;
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
                    } else {
                        $communicate_page_url = url($this->m_urls_arr['comments']
                                , array('query'=>array('projectid'=>$owner_projectid
                                        , 'return' => $cmi['link_path']
                                        , 'rparams' => $rparams_encoded)));
                        $sCommentsMarkup = "<a title='jump to communications for #{$wid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";
                        if($owner_projectid === $this->m_parent_projectid)
                        {
                            $hierarchy_page_url = url($this->m_urls_arr['visual_edit_dependencies']
                                    , array('query'=>array('projectid'=>($owner_projectid), 'jump2workitemid'=>$wid)));
                            $sHierarchyMarkup = "<a "
                                . " title='visualization of dependencies for goal#{$wid} in project#{$owner_projectid}' "
                                . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                        } else {
                            $hierarchy_page_url = url($this->m_urls_arr['visual_show_dependencies']
                                    , array('query'=>array('projectid'=>($owner_projectid), 'jump2workitemid'=>$wid)));
                            $sHierarchyMarkup = "<a "
                                . " title='visualization of dependencies for goal#{$wid} in project#{$owner_projectid}' "
                                . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                        }
                    }

                    if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                    {
                        $sViewMarkup = '';
                    } else {
                        $view_page_url = url($this->m_urls_arr['view']['project'], array('query'=>array('projectid'=>$owner_projectid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)));
                        $sViewMarkup = "<a title='view {$wid}' href='$view_page_url'><img alt='view data icon' src='$view_icon_url'/></a>";
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                    {
                        $sEditMarkup = '';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit']['project'], array('query'=>array('projectid'=>$owner_projectid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)));
                        $sEditMarkup = "<a title='edit {$wid}' href='$edit_page_url'><img alt='edit data icon' src='$edit_icon_url'/></a>";
                    }
                    $convertkey = NULL;
                    if($typeletter == "P")
                    {
                        if(isset($sp2p[$owner_projectid]) && count($sp2p[$owner_projectid]) > 1)
                        {
                            $convert_icon_url = $no_convert_intogoal_icon_url;
                            $all_dep_pids_tx = implode(', ',$sp2p[$owner_projectid]);
                            $converttooltip = "cannot convert to goal because multiple projects (IDs $all_dep_pids_tx) depend on project#$owner_projectid";
                            $convert_page_url = NULL;
                        } else {
                            $convert_icon_url = $convert_intogoal_icon_url;
                            $converttooltip = "convert by discarding project#{$owner_projectid} wrapping of goal#$root_goalid";
                            $convertkey = "convert_intogoal";
                            $convert_page_url = url($this->m_urls_arr[$convertkey]
                                    , array('query'=>array('parent_projectid'=>$this->m_parent_projectid, 'this_projectid'=>$owner_projectid
                                    , 'return' => $cmi['link_path']
                                    , 'rparams' => $rparams_encoded)));
                        }
                        $creatething_icon_url = $create_template_icon_url;
                        $creatething_tooltip = "create a template based on project#{$owner_projectid}";
                        $creatething_key = "create_template";
                        $creatething_page_url = url($this->m_urls_arr[$creatething_key]
                                , array('query'=>array('source_projectid'=>$owner_projectid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)));
                    } else if($typeletter == "G") {
                        $convert_icon_url = $convert_intoproject_icon_url;
                        $converttooltip = "convert goal#{$wid} into a project";
                        $convertkey = "convert_intoproject";
                        $convert_page_url = url($this->m_urls_arr[$convertkey]
                                , array('query'=>array('root_goalid'=>$wid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)));
                        
                        $creatething_icon_url = $create_snippet_icon_url;
                        $creatething_tooltip = "create a reusable named snippet based on workitem#{$wid}";
                        $creatething_key = "create_snippet";
                        $creatething_page_url = url($this->m_urls_arr[$creatething_key]
                                , array('query'=>array('source_workitem'=>$wid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)));
                        
                    } else {
                        throw new \Exception("No support for typeletter=$typeletter");
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || empty($convertkey) || !isset($this->m_urls_arr[$convertkey]))
                    {
                        if(empty($converttooltip))
                        {
                            $sConvertMarkup = '';
                        } else {
                            $sConvertMarkup = "<span title='{$converttooltip}'><img alt='conversion icon' src='$convert_icon_url'/></span>";
                        }
                    } else {
                        //$sConvertMarkup = "<span title='CURRENTLY DISABLED {$converttooltip}' ><img src='$convert_icon_url'/></span>";// "<a title='{$converttooltip}' href='$convert_page_url'><img src='$convert_icon_url'/></a>";
                        $sConvertMarkup = "<a title='{$converttooltip}' href='$convert_page_url'><img alt='conversion icon' src='$convert_icon_url'/></a>";
                    }

                    if(strpos($this->m_aWorkitemsRights,'V') === FALSE)
                    {
                        $sCreateThingMarkup = '';
                    } else {
                        $sCreateThingMarkup = "<span title='CURRENTLY DISABLED {$creatething_tooltip}' ><img src='$creatething_icon_url'/></span>"; //"<a title='{$creatething_tooltip}' href='$creatething_page_url'><img src='$creatething_icon_url'/></a>";
                    }
                    
                    $days_until_project_end = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($end_dt, $project_end_dt);
                    if($typeletter == 'P')
                    {
                        $awc_tip = 'antecedents of the project root';
                    } else {
                        $awc_tip = 'antecedents of the branch root';
                    }
                    $awc_markup = "[SORTNUM:{$all_ant_workitem_count}]<span title='{$awc_tip}'>{$all_ant_workitem_count}</span>";

                    if($is_connected)
                    {
                        $is_member_classname_markup = "noedit";
                    } else {
                        $is_member_classname_markup = $canedit_classname_markup;
                    }
                    
                    if(count($ddw_ar) == 0)
                    {
                        $ddw_markup = '';
                    } else {
                        asort($ddw_ar);
                        $count_ddw = count($ddw_ar);
                        if($count_ddw == 0)
                        {
                            $count_ddw_tip = "nothing depends on this";
                        } else
                        if($count_ddw == 1)
                        {
                            $count_ddw_tip = "1 link";
                        } else {
                            $count_ddw_tip = "{$count_ddw} links";
                        }
                        $ddw_markup = "[SORTNUM:{$count_ddw}]<span title='{$count_ddw_tip}'>" . implode(', ', $ddw_ar) . "</span>";                
                    }
                    
                    $est_flags = [];
                    $est_flags['startdate'] = ($start_dt_type_cd == 'E');
                    $est_flags['enddate'] = ($end_dt_type_cd == 'E');
                    $est_flags['middle'] = ($effort_hours_type_cd = 'E');
                    $rows   .= "\n"
                            . "<tr allowrowedit='$allowrowedit_yn' id='$wid' data_parent_projectid='{$this->m_parent_projectid}' typeletter='$typeletter'>"

                            . "<td>" . $wid_markup.'</td>'

                            . '<td>' . $typeletter_markup.'</td>'

                            . '<td>' . $workitem_nm.'</td>'

                            . '<td>' . $owner_sortstr.$owner_markup.'</td>'

                            . '<td>' . $status_markup.'</td>'
                                    
                            . '<td>'
                            . $start_dt_markup . '</td>'
                            . '<td>'
                            . $start_dt_type_cd . '</td>'

                            . '<td>'
                            . $end_dt_markup . '</td>'
                            . '<td>'
                            . $end_dt_type_cd . '</td>'
                            . "<td>"
                            . $ddw_markup.'</td>'
                                    
                            . '<td>'
                            . $awc_markup.'</td>'

                            .'<td class="action-options">'
                                . $sCommentsMarkup.' '
                                . $sHierarchyMarkup . ' '
                                . $sViewMarkup.' '
                                . $sEditMarkup.' '
                                . $sConvertMarkup.' '
                                . $sCreateThingMarkup.' '
                            . "</td>"
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
                    . '<th class="" colname="start_dt" datatype="date">'
                        . '<span title="The start date for the work item">'.t('Start Date').'</span></th>'
                    . '<th class="nowrap" colname="start_dt_type_cd" datatype="string">'
                        . '<span title="Start Date Type Code (A=Actual, E=Estimated)">'.t('SDT').'</span></th>'
                    . '<th colname="end_dt" datatype="date">'
                        . '<span title="The end date for the work item">'.t('End Date').'</span></th>'
                    . '<th class="nowrap" colname="end_dt_type_cd" datatype="string">'
                        . '<span title="End Date Type Code (A=Actual, E=Estimated)">'.t('EDT').'</span></th>'
                    . '<th class="nowrap" colname="ddw" datatype="formula" >'
                        . '<span title="Direct Dependent Workitems">'.t('DDW').'</span></th>'
                    . '<th class="nowrap" colname="all_ant_workitem_count" datatype="formula" >'
                        . '<span title="Antecedent Workitem Count">'.t('AWC').'</span></th>'
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

            if($project_editable)
            {
                if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
                {
                    if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                    {
                        $initial_button_markup = l('ICON_ADD Create New Antecedent Project'
                                , $this->m_urls_arr['add']['project']
                                , array('query' => array(
                                    'projectid' => $this->m_parent_projectid,
                                    'basetype' => 'G', 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)
                                    , 'attributes'=>array('class'=>'action-button')
                                    ));
                        $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                        $form['data_entry_area1']['action_buttons']['addsubproject'] = array('#type' => 'item'
                                , '#markup' => $final_button_markup);
                    }
                }
            }
            
            if(isset($this->m_urls_arr['create_template']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aWorkitemsRights,'V') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Create New Template from Project'
                            , $this->m_urls_arr['create_template']
                            , array('query' => array(
                                'source_projectid' => $this->m_parent_projectid
                                , 'return' => $cmi['link_path']
                                , 'rparams' => $rparams_encoded)
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['createtemplate'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
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

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
