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
class ShowGanttReport extends \bigfathom\ASimpleFormPage
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
        $urls_arr['return'] = 'bigfathom/sitemanage';
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
            $main_tablename = 'gantt-report';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageWorkitemDurationsTable.js");
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
            
            if($this->m_urls_arr['main_visualization'] > '')
            {
                if(substr($this->m_urls_arr['main_visualization'],0,4) == 'http')
                {
                    $visualization_url = $this->m_urls_arr['main_visualization'];
                } else {
                    $visualization_url = $base_url.'/'.$this->m_urls_arr['main_visualization'];
                }
                $form['data_entry_area1']['main_visual'] = array(
                    '#type' => 'item', 
                    '#prefix' => '<iframe width="100%" height="200" scrolling=yes class="'.$html_classname_overrides['visualization-container'].'" src="'.$visualization_url.'">',
                    '#suffix' => '</iframe>', 
                    '#tree' => TRUE,
                );
            }
                
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];

            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            //$old_all = $this->m_oMapHelper->getWorkitemsInProjectByID($this->m_parent_projectid);
            
            $bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);
            //DebugHelper::debugPrintNeatly($newtest);
            $all = $bundle['workitems'];
            $today = $updated_dt = date("Y-m-d", time());
            $min_date = $bundle['dates']['min_dt'];
            $max_date = $bundle['dates']['max_dt'];
            
//drupal_set_message("LOOK dates === " . print_r($bundle['dates'],TRUE));            
            $gantt_width=400;
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
            
            foreach($all as $workitemid=>$record)
            {
                $nativeid = $record['nativeid'];
                $itemtype = $record['type'];
                $workitemid_markup = $nativeid;
                $is_project_root = !empty($record['root_of_projectid']);
                $typeletter = $record['typeletter'];
                if($is_project_root)
                {
                    $typeletter = 'P';    
                }
                $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                $typeletter_markup = $typeletter . " <img src='$typeiconurl' /></span>";
                $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
                $status_cd = $record['status_cd'];
                $owner_personid = $record['owner_personid'];
                $map_delegate_owner = $record['maps']['delegate_owner'];
                if($is_project_root)
                {
                    $typename4gantt = 'proj';
                } else {
                    $typename4gantt = $itembasetype;
                }
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
                $daw_ar = $record['maps']['daw'];
                $ddw_ar = $record['maps']['ddw'];
                
                $workitem_nm = $record['workitem_nm'];
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

                $allowrowedit_yn = $isowner || $is_systemadmin ? 1 : 0;
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
                    $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid)));
                    $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";
                    
                    if(count($daw_ar) == 0)
                    {
                        $sViewDashboardMarkup = "<span title='There are no open antecedents to this workitem'><img src='$no_dashboard_icon_url'/></span>";
                    } else {
                        $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('workitemid'=>$workitemid)));
                        $sViewDashboardMarkup = "<a title='jump to dashboard for #{$workitemid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";
                    }
                    
                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                            , array('query'=>array('projectid'=>($this->m_parent_projectid), 'jump2workitemid'=>$workitemid)));
                    
                }
                
                if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('equipmentid'=>$equipmentid)));
                    $view_page_url = url($this->m_urls_arr['view'][$itembasetype], array('query'=>array('workitemid'=>$nativeid)));
                    $sViewMarkup = "<a title='view {$workitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'][$itembasetype], array('query'=>array('workitemid'=>$nativeid)));
                    $sEditMarkup = "<a title='edit {$workitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'][$itembasetype], array('query'=>array('workitemid'=>$nativeid)));
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
                $gantt_sortnum = strtotime($start_dt);
                $gantt_markup = 'calculating'; // Let the grid calculate it on the client side
                $gantt_markup = "[SORTNUM:" . $gantt_sortnum . "]" 
                        . $this->m_oGanttChartHelper
                            ->getGanttBarMarkup($typename4gantt, $start_dt, $end_dt, $est_flags, $pin_flags);
                $htmlfriendly_workitemid = substr($workitemid,0,1) . '-' . $nativeid;
                if($typeletter == 'G')
                {
                    $branch_classname = 'canedit';
                } else {
                    $branch_classname = 'notapplicable';
                }
                $rows   .= "\n"
                        . "<tr allowrowedit='$allowrowedit_yn' id='$htmlfriendly_workitemid'>"
                        
                        . "<td>"
                        . $workitemid_markup.'</td>'
                        
                        . '<td>'
                        . $typeletter_markup.'</td>'
                        
                        . '<td>'
                        . $workitem_nm.'</td>'
                        
                        . '<td>'
                        . $owner_sortstr.$owner_markup.'</td>'
                        
                        . '<td>'
                        . $status_cd.'</td>'
                        
                        
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
                        . $min_fte_markup . '</td>'
                        
                        . "<td>"
                        . $gantt_markup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead>'
                    . '<th class="nowrap" colname="id" datatype="numid">'
                        . '<span title="Unique ID of this work item">'.t('ID').'</span></th>'
                    . '<th class="nowrap" colname="typeletter">'
                        . '<span title="G=Goal, P=Goal which is root of a project, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span></th>'
                    . '<th class="nowrap" colname="name">'.t('Name').'</th>'
                    . '<th class="nowrap" colname="owner" datatype="sortablehtml">'
                        . '<span title="Owner of the work item">'.t('Owner').'</span></th>'
                    . '<th class="nowrap" colname="status_cd">'
                        . '<span title="The status code of the workitem">'.t('Status').'</span></th>'
                    . '<th class="nowrap" colname="branch_effort_hours_est" editable="1" datatype="double" named_validator="GTEZ">'
                        . '<span title="Branch Effort Hours Limit">'.t('BEHL').'</span></th>'
                    . '<th width="' . $gantt_width . 'px" class="nowrap" colname="calc_gantt" datatype="formula">'
                        . '<span title="Work timing visualization">'.t('Gantt').'</span></th>'
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

            if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aWorkitemsRights,'A') !== FALSE)
                {
                    $add_link_markup = l('Create New Workitem'
                            , $this->m_urls_arr['add']['workitem']
                            , array('query' => array(
                                'projectid' => $this->m_parent_projectid,
                                'basetype' => 'G')
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['addworkitem'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
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
