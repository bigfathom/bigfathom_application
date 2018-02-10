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
 * This class returns the list of available work items
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageSubprojectsPage extends \bigfathom\ASimpleFormPage
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
            drupal_add_js("$base_url/$module_path/form/js/ManageSubprojects2ProjectTable.js");
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
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $subproj_and_proj_map_bundle = $this->m_oMapHelper->getSubprojects2ProjectMapBundle($this->m_parent_projectid);
            if(array_key_exists($this->m_parent_projectid, $subproj_and_proj_map_bundle['subproject2project']))
            {
                $subproject2project = $subproj_and_proj_map_bundle['subproject2project'][$this->m_parent_projectid];
            } else {
                $subproject2project = [];
            }
            
            //$bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);
            $bundle = $this->m_oMapHelper->getRichProjectsByIDBundle($this->m_parent_projectid);
            $all = $bundle['projects'];
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
            $all_pdeps = $pi_bundle['relationships_map']['projects']['deps'];
            $candidate_ant_projects = [];
            foreach($all as $subprojectid=>$record)
            {
                if(!isset($all_pdeps[$subprojectid]))
                {
                    $candidate_ant_projects[$subprojectid] = $subprojectid;
                }
            }   
                
            foreach($candidate_ant_projects as $subprojectid)
            {
                
                $record = $all[$subprojectid];
                $root_goalid = $record['root_goalid'];
                $itemtype = "goal";
                
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

                if(array_key_exists($subprojectid, $subproject2project))
                {
                    $is_member = TRUE;
                    $is_member_markup = 1;
                    $member_scf = $subproject2project[$subprojectid]["ot_scf"];
                    $is_connected = $subproject2project[$subprojectid]["is_connected"];
                } else {
                    $is_member = FALSE;
                    $is_member_markup = 0;
                    $member_scf = NULL;
                    $is_connected = FALSE;
                }
                
                if($is_member || ($project_editable && $status_terminal_yn == '0'))
                {

                    $projectid_markup = $subprojectid;
                    $typeletter = 'P';    
                    
                    $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                    $typeletter_markup = $typeletter . " <img src='$typeiconurl' /></span>";
                    $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
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

                    $workitem_nm = $record['root_workitem_nm'];
                    $workitem_scf = $record['ot_scf'];

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
                    $durationdays = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt) + 1;

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
                        $sViewDashboardMarkup = '';
                    } else {
                        $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('projectid'=>$subprojectid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                        $sCommentsMarkup = "<a title='jump to communications for #{$subprojectid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";
                        if($subprojectid === $this->m_parent_projectid)
                        {
                            $hierarchy_page_url = url($this->m_urls_arr['visual_edit_dependencies']
                                    , array('query'=>array('projectid'=>($subprojectid), 'jump2workitemid'=>$root_goalid)));
                        } else {
                            $hierarchy_page_url = url($this->m_urls_arr['visual_show_dependencies']
                                    , array('query'=>array('projectid'=>($subprojectid), 'jump2workitemid'=>$root_goalid)));
                        }
                        $sHierarchyMarkup = "<a "
                            . " title='view dependencies for subproject#{$subprojectid} in project#{$this->m_parent_projectid}' "
                            . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";

                    }

                    if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                    {
                        $sViewMarkup = '';
                    } else {
                        $view_page_url = url($this->m_urls_arr['view']['project'], array('query'=>array('projectid'=>$subprojectid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                        $sViewMarkup = "<a title='view {$subprojectid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                    {
                        $sEditMarkup = '';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit']['project'], array('query'=>array('projectid'=>$subprojectid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                        $sEditMarkup = "<a title='edit {$subprojectid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                    {
                        $sDeleteMarkup = '';
                    } else {
                        $delete_page_url = url($this->m_urls_arr['delete']['project'], array('query'=>array('projectid'=>$subprojectid, 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                        $sDeleteMarkup = "<a title='delete {$subprojectid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                    }
            
                    //TODO factor in todays date!!!!!!!!!!!!!!!!!!
                    $days_until_project_end = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($end_dt, $project_end_dt);
                    $cc_markup = 0; //Assume zero confidence to start
                    if($is_member || $days_until_project_end >= 0)
                    {
                        //Show the confidence of the workitem
                        if($member_scf !== NULL)
                        {
                            //We have an explicit value already available
                            $iscc_markup = $member_scf;
                            $default_scf = $member_scf;
                            $cc_markup = ($workitem_scf + $member_scf ) / 2;
                        } else {
                            //Start with the overall confidence for the workitem
                            $iscc_markup = "";
                            $default_scf = $workitem_scf;
                            $cc_markup = $workitem_scf;
                        }
                    } else {
                        if($days_until_project_end < -2)
                        {
                            //No opinion because too late
                            $iscc_markup = "";
                            $default_scf = "";
                            if($member_scf !== NULL)
                            {
                                $cc_markup = ($workitem_scf + $member_scf ) / 10;    
                            } else {
                                $cc_markup = ($workitem_scf) / 20;
                            }
                        } else {
                            //Only missing the deadline by a few days
                            if($member_scf !== NULL)
                            {
                                //We have an explicit value already available
                                $factor = $member_scf/4;
                            } else {
                                //Start with the overall confidence for the workitem
                                $factor = $workitem_scf/4;
                            }
                            $factor = max($factor, 0.25);
                            $iscc_markup = $member_scf;
                            $default_scf = $factor;
                            $cc_markup = $factor;
                        }
                    }

                    if($is_connected)
                    {
                        $is_member_classname_markup = "noedit";
                    } else {
                        $is_member_classname_markup = $canedit_classname_markup;
                    }
                    
                    $est_flags = [];
                    $est_flags['startdate'] = ($start_dt_type_cd == 'E');
                    $est_flags['enddate'] = ($end_dt_type_cd == 'E');
                    $est_flags['middle'] = ($effort_hours_type_cd = 'E');
                    $rows   .= "\n"
                            . "<tr allowrowedit='$allowrowedit_yn' default_scf='$iscc_markup' id='$subprojectid' data_parent_projectid='{$this->m_parent_projectid}'>"

                            . "<td>" . $projectid_markup.'</td>'

                            . '<td>' . $typeletter_markup.'</td>'

                            . '<td>' . $workitem_nm.'</td>'

                            . '<td>' . $owner_sortstr.$owner_markup.'</td>'

                            . '<td>' . $status_markup.'</td>'
                                    
                            . '<td>'
                            . $is_connected . '</td>'

                            . '<td class="' . $is_member_classname_markup . '">'
                            . $is_member_markup . '</td>'

                            //. '<td class="' . $canedit_classname_markup . '">'
                            //. $iscc_markup . '</td>'
                            //. '<td>'
                            //. $cc_markup.'</td>'

                            . '<td>'
                            . $start_dt_markup . '</td>'
                            . '<td>'
                            . $start_dt_type_cd . '</td>'

                            . '<td>'
                            . $end_dt_markup . '</td>'
                            . '<td>'
                            . $end_dt_type_cd . '</td>'

                            .'<td class="action-options">'
                                . $sCommentsMarkup.' '
                                . $sHierarchyMarkup . ' '
                                . $sViewMarkup.' '
                                . $sEditMarkup.' '
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
                    . '<th class="nowrap" colname="is_connected_yn" datatype="boolean">'
                        . '<span title="Indicates at least one specific dependency link has been declared">'.t('Connected').'</span></th>'
                    . '<th class="nowrap" colname="is_member_yn" ' . $editable_col_flag . ' datatype="boolean">'
                        . '<span title="Projects marked as antecedents of the current project are relevant to the success of the current project">'.t('Antecedent').'</span></th>'
                    //. '<th class="nowrap" colname="ot_scf" datatype="double" ' . $editable_col_flag . ' named_validator="PROBABILITY">'
                    //    . '<span title="Declared confidence in range [0,1] of completion by declared end date">'.t('DC').'</span></th>'
                    //. '<th class="nowrap" colname="computed_confidence" datatype="double" named_validator="GTEZ">'
                    //    . '<span title="Computed confidence of completion by the declared end date">'.t('CC').'</span></th>'
                    . '<th class="nowrap" colname="start_dt" datatype="date">'
                        . '<span title="The start date for the work item">'.t('Start Date').'</span></th>'
                    . '<th class="nowrap" colname="start_dt_type_cd" datatype="string">'
                        . '<span title="Start Date Type Code (A=Actual, E=Estimated)">'.t('SDT').'</span></th>'
                    . '<th colname="end_dt" datatype="date">'
                        . '<span title="The end date for the work item">'.t('End Date').'</span></th>'
                    . '<th class="nowrap" colname="end_dt_type_cd" datatype="string">'
                        . '<span title="End Date Type Code (A=Actual, E=Estimated)">'.t('EDT').'</span></th>'
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
