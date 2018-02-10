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
//require_once 'helper/SprintPageHelper.php';

/**
 * This class returns the list of available work items
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageWorkitems2RolesPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_oWorkitemPageHelper      = NULL;
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
        if(!empty($projectid))
        {
            $this->m_parent_projectid = $projectid;
        } else {
            $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        }
        if(empty($this->m_parent_projectid))
        {
            throw new \Exception("Missing required projectid!");
        }

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
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'grid-role2workitem';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkitems2RolesTable.js");
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

            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "In this grid you can fine-tune the relevant roles of the project down to the workitem level of detail.  Place a checkmark in the columns of the roles that are relevant for each workitem.  Changes are saved as you make them."
                . "</p>",
                '#suffix' => '</div>',
            );
            
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
            $is_project_owner = isset($uprm['detail'][$this->m_parent_projectid]);
            
            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_parent_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);
            $all_workitems = $bundle['workitems'];
            $today = $updated_dt = date("Y-m-d", time());
            $min_date = $bundle['dates']['min_dt'];
            $max_date = $bundle['dates']['max_dt'];
            $status_lookup = $bundle['status_lookup'];
            
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
            $cmi = $this->m_oContext->getCurrentMenuItem();

            $all_roles_in_project = $this->m_oMapHelper->getRolesByID($this->m_parent_projectid);
            $roles_by_name = [];
            $roles_sorter = [];
            foreach($all_roles_in_project as $id=>$v)
            {
                if($id > 1)
                {
                    //We ignore the owner role for this display
                    $name = $v['role_nm'] . "#" . $id;
                    $roles_by_name[$name] = $id;
                    $roles_sorter[] = $name;
                }
            }
            sort($roles_sorter);
            $sorted_roleids = [];
            $role_table_th_ar = []; //The columns
            foreach($roles_sorter as $name)
            {
                $id = $roles_by_name[$name];
                $sorted_roleids[] = $id;
                $role_table_th_ar[] = $all_roles_in_project[$id];
            }
            
            $editable_wi_count = 0;
            foreach($all_workitems as $workitemid=>$record)
            {
                
                $nativeid = $record['nativeid'];
                $itemtype = $record['type'];
                $wi_roles =  isset($record['maps']['roles']) ? $record['maps']['roles'] : [];
                $is_project_root = !empty($record['root_of_projectid']);
                
                if(!$is_project_root)
                {
                    $wi_role_colvalues = [];
                    $fastcheck = [];
                    //DebugHelper::debugPrintNeatly($wi_roles)                ;
                    foreach($wi_roles as $hasid=>$roleinfo)
                    {
                        $fastcheck[$hasid] = $roleinfo;
                    }
                    foreach($sorted_roleids as $idx=>$checkid)
                    {
                        $wi_role_colvalues[$checkid] = isset($fastcheck[$checkid]) ? 1 : 0;
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
                    
                    $workitemid_markup = $nativeid;
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

                    $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                    $typeletter_markup = "[SORTNUM:{$type_ordernum}]" . $typeletter . " <img src='$typeiconurl' /></span>";
                    $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
                    $owner_personid = $record['owner_personid'];
                    $map_delegate_owner = $record['maps']['delegate_owner'];
                    if($is_project_root)
                    {
                        $typename4gantt = 'proj';
                    } else {
                        $typename4gantt = $itembasetype;
                    }

                    $is_workitem_owner = $user->uid == $owner_personid;
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
                            if(!$is_workitem_owner && $user->uid === $delegate_ownerid)
                            {
                                $is_workitem_owner = TRUE;    
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
                    $daw_ar = $record['maps']['daw'];
                    $ddw_ar = $record['maps']['ddw'];

                    $workitem_nm = $record['workitem_nm'];
                    $workitem_scf = $record['ot_scf'];
                    $branch_effort_hours_est = $record['branch_effort_hours_est'];
                    $limit_branch_effort_hours_cd = $record['limit_branch_effort_hours_cd'];
                    if($limit_branch_effort_hours_cd == 'L')
                    {
                        $branch_effort_hours_locked_markup = TRUE;
                    } else {
                        $branch_effort_hours_locked_markup = FALSE;
                    }


                    $effort_hours_est = $record['effort_hours_est'];
                    $effort_hours_worked_act = $record['effort_hours_worked_act'];

                    $planned_start_dt = $record['planned_start_dt'];
                    $planned_end_dt = $record['planned_end_dt'];

                    $actual_start_dt = $record['actual_start_dt'];
                    $actual_end_dt = $record['actual_end_dt'];

                    if(!empty($effort_hours_worked_act))
                    {
                        $effort_hours = $effort_hours_worked_act;
                        $effort_hours_type_cd = 'A';
                        $effort_hours_locked_markup = true;
                    } else {
                        $effort_hours = $effort_hours_est;
                        $effort_hours_type_cd = 'E';
                        if($record['effort_hours_est_locked_yn'] == 1)
                        {
                            $effort_hours_locked_markup = true;
                        } else {
                            $effort_hours_locked_markup = false;
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
                    $branch_effort_hours_markup = $branch_effort_hours_est;
                    if(empty($effort_hours))
                    {
                        $effort_hours = 0;
                    }
                    $effort_hours_markup = $effort_hours; //"<span title='TODO days'>$effort_hours</span>";
                    $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($start_dt);
                    $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($end_dt);

                    $durationdays = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt) + 1;
                    if($durationdays > 0)
                    {
                        $hoursperfte = 8;
                        $hoursperday = $effort_hours / $hoursperfte;
                        $min_fte_markup = $hoursperday / $hoursperfte;
                    } else {
                        $min_fte_markup = "";   //<span title='Insufficient data to compute'>-</span>";
                    }

                    $allowrowedit_yn = ($is_workitem_owner || $is_systemadmin) ? 1 : 0;
                    if($allowrowedit_yn)
                    {
                        $editable_wi_count++;
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

                    $est_flags = [];
                    $est_flags['startdate'] = ($start_dt_type_cd == 'E');
                    $est_flags['enddate'] = ($end_dt_type_cd == 'E');
                    $est_flags['middle'] = ($effort_hours_type_cd = 'E');
                    $pin_flags = [];
                    $pin_flags['startdate'] = ($start_dt_locked_markup == 1);
                    $pin_flags['enddate'] = ($end_dt_locked_markup == 1);
                    if($typeletter == 'G' || $typeletter == 'P')
                    {
                        $branch_classname = 'readonly';
                    } else {
                        $branch_classname = 'notapplicable';
                    }
                    $rows   .= "\n"
                            . "<tr allowrowedit='$allowrowedit_yn' id='$nativeid' data_projectid='{$this->m_parent_projectid}' typeletter='$typeletter'>"
                            
                            . "<td>" . $workitemid_markup.'</td>'
                            . '<td>' . $typeletter_markup.'</td>'

                            . '<td>' . $workitem_nm.'</td>'

                            . '<td>' . $owner_sortstr.$owner_markup.'</td>'

                            . '<td>' . $status_markup.'</td>';

                    $canedit_classname_markup = '';
                    foreach($wi_role_colvalues as $hasrole)
                    {
                        $rows   .='<td class="' . $canedit_classname_markup . '">'
                                . $hasrole.'</td>';
                    }
                            
                    $rows   .='<td class="' . $branch_classname . '">'
                            . $branch_effort_hours_markup.'</td>'

                            . '<td>'
                            . $effort_hours_markup.'</td>'

                            . '<td>'
                            . $start_dt_markup . '</td>'
                            . '<td>'
                            . $end_dt_markup . '</td>'
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
                
            }

            $th_ar = [];
            $th_ar[] = 'class="nowrap" colname="id" datatype="numid">'
                        . '<span title="Unique ID of this work item">'.t('ID').'</span>';
            
            $th_ar[] = 'class="nowrap" colname="typeletter" datatype="formula">'
                        . '<span title="G=Goal, P=Goal which is root of a project, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span>';
            $th_ar[] = 'class="nowrap" colname="name">'.t('Name');
            $th_ar[] = 'class="nowrap" colname="owner" datatype="sortablehtml">'
                                    . '<span title="Owner of the work item">'.t('Owner').'</span>';
            $th_ar[] = 'class="nowrap" colname="status_cd">'
                                    . '<span title="The status code of the workitem">'.t('Status').'</span>';

            $allow_row_edit = TRUE;
            if($allow_row_edit)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            foreach($role_table_th_ar as $detail)
            {
                $colname = "role_" . $detail['id'];
                $label = $detail['role_nm'];
                $help = $detail['purpose_tx'];
                $th_ar[] = 'class="nowrap" colname="' . $colname . '" ' . $editable_col_flag . ' datatype="boolean"">'
                                        . '<span title="'.$help.'">'.t($label).'</span>';
            }
            
            $th_ar[] = 'class="nowrap" colname="branch_effort_hours_est" datatype="double" named_validator="GTEZ">'
                                    . '<span title="Branch Effort Hours Limit">'.t('BEHL').'</span>';
            $th_ar[] = 'class="nowrap" colname="effort_hours" datatype="double" named_validator="GTEZ">'
                                    . '<span title="Direct Effort Hours are the hours that are NOT part of the the total assigned to other branch members">'.t('DEH').'</span>';
            $th_ar[] = 'class="nowrap" colname="start_dt" datatype="date">'
                                    . '<span title="The start date for the work item">'.t('Start Date').'</span>';
            $th_ar[] = 'colname="end_dt" datatype="date">'
                                    . '<span title="The end date for the work item">'.t('End Date').'</span>';
            $th_ar[] = 'class="nowrap" colname="ddw" datatype="formula" title="Direct Dependent Workitems">' . t('DDW').'';
            $th_ar[] = 'class="nowrap" colname="daw" datatype="formula" title="Direct Antecedent Workitems">' . t('DAW').'';
            $th_ar[] = 'datatype="html" class="action-options">' . t('Action Options').'';

            $th_markup = "<th " . implode("</th><th ",$th_ar) . "</th>" ;
            $form["data_entry_area1"]['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead>' . $th_markup .'</thead>'
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

            $can_add_workitem = $is_systemadmin || $is_project_owner || $editable_wi_count > 0;
            
            if($can_add_workitem)
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
