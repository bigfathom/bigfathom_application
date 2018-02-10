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
require_once 'helper/SprintPageHelper.php';

/**
 * This class returns the list of available sprints
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageSprintsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper    = NULL;
    protected $m_urls_arr      = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oPageHelper   = NULL;
    protected $m_oContext      = NULL;
    protected $m_hasSelectedProject = NULL;
    protected $m_selectedProjectSummary = NULL;
    protected $m_oTextHelper = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_hasSelectedProject = $this->m_oContext->hasSelectedProject();
        $this->m_selectedProjectSummary = $this->m_oContext->getSelectedProjectSummary();
        
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/sprint';
        $urls_arr['add'] = 'bigfathom/sprint/add';
        $urls_arr['edit'] = 'bigfathom/sprint/edit';
        $urls_arr['view'] = 'bigfathom/sprint/view';
        $urls_arr['delete'] = 'bigfathom/sprint/delete';
        $urls_arr['comments'] = 'bigfathom/sprint/mng_comments';
        $urls_arr['sprint_membership'] = 'bigfathom/projects/workitems/map2sprint';//&projectid=5
        $urls_arr['main_visualization'] = ''; // '/sites/all/modules/bigfathom_core/visualization/MapSprintGoal.html';
        $urls_arr['durationconsole'] = 'bigfathom/projects/workitems/duration';
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $aPersonRights='VAED';
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aPersonRights    = $aPersonRights;
        
        $this->m_oPageHelper = new \bigfathom\SprintPageHelper($urls_arr, NULL, $this->m_selectedProjectSummary['projectid']);
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
            $main_tablename = 'sprints-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
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

            //Add context action buttons.
            $form['data_entry_area1']['context_action_buttons'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            
            $show_add_button = FALSE;
            if(!$this->m_hasSelectedProject)
            {
                
                $form['data_entry_area1']['usermessage'] = array('#type' => 'item',
                         '#markup' => '<h2>No project selected</h2>');
                
            } else {
                $owner_projectid = $this->m_selectedProjectSummary['projectid'];
                $show_add_button = TRUE;
                $visualization_url = $this->m_urls_arr['main_visualization'];
                if($visualization_url > '')
                {
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

                $sprint_status_by_code = $this->m_oMapHelper->getSprintStatusByCode();
                $project_sprint_overview = $this->m_oMapHelper->getProjectSprintOverview($owner_projectid);
                $workitem2sprint_counts = $this->m_oMapHelper->getSprintWorkitemMembershipCountMap($owner_projectid);
                
                if(isset($project_sprint_overview['projects'][$owner_projectid]['sprints']))
                {
                    $alliters = $project_sprint_overview['projects'][$owner_projectid]['sprints'];
                    $last_sprint_number = $project_sprint_overview['projects'][$owner_projectid]['last_sprint_number'];
                } else {
                    $alliters = NULL;
                    $last_sprint_number = NULL;
                }

                $form["data_entry_area1"]['debugstuff'] = array(
                    '#type' => 'item', 
                    '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                    '#suffix' => '</div>', 
                    '#tree' => TRUE,
                );

                $rows = "\n";
                $cmi = $this->m_oContext->getCurrentMenuItem();
                if($alliters != NULL)
                {
                    $all = $alliters;
                    $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
                    $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
                    $sprint_membership_icon_url = \bigfathom\UtilityGeneralFormulas::getActiveIconURLForPurposeName('sprint_membership');
                    $sprint_membership_dim_icon_url = \bigfathom\UtilityGeneralFormulas::getInactiveIconURLForPurposeName('sprint_membership');
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    foreach($all as $iterid=>$record)
                    {
                        $sprintid = $record['id'];
                        $sprint_membership_locked_yn = $record['membership_locked_yn'];
                        $iteration_ct = $record['iteration_ct'];
                        $title_tx = $record['title_tx'];
                        $status_cd = $record['status_cd'];
                        $official_score = $record['official_score'];
                        if(empty($official_score))
                        {
                            $score_markup = '<span title="not evaluated">-</span>';
                        } else {
                            $scoreval = intval($official_score);
                            if($scoreval >= 90)
                            {
                                $score_classname = 'good-score';
                                $score_description = 'this is a good score';
                            } else
                            if($scoreval >= 80)
                            {
                                $score_classname = 'okay-score';
                                $score_description = 'okay but can improve';
                            } else
                            if($scoreval >= 70)
                            {
                                $score_classname = 'caution-score';
                                $score_description = 'barely passing score';
                            } else {
                                $score_classname = 'bad-score';
                                $score_description = 'this is a bad score';
                            }
                            $score_markup = "<span class='{$score_classname}' title='{$score_description}'>{$official_score}</span>";
                        }
                        if(isset($workitem2sprint_counts[$sprintid]))
                        {
                            $workitem_counts = $workitem2sprint_counts[$sprintid];
                        } else {
                            $workitem_counts = 0;
                        }
                        
                        $status_terminal_yn = $record['terminal_yn'];
                        $terminalyesno_markup = ($status_terminal_yn == 1 ? 'Yes' : '<span class="colorful-available">No</span>');
                        
                        //$activeyesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                        $story_tx = $record['story_tx'];
                        if($status_cd != NULL)
                        {
                            $status_title_tx = $sprint_status_by_code[$status_cd]['title_tx'];
                            $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                        } else {
                            $status_markup = "";
                        }
                        if(strlen($story_tx) > 80)
                        {
                            $story_tx = substr($story_tx, 0,80) . '...';
                        }
                        
                        $start_dt = $record['start_dt'];
                        $end_dt = $record['end_dt'];
                        
                        $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTime($start_dt);
                        $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTime($end_dt);
                        if(empty($end_dt) || empty($start_dt))
                        {
                            $duration_days_markup = '<span title="the date range is not yet set">NA</span>';
                        } else {
                            
                            $raw_diff = strtotime($end_dt) - strtotime($start_dt);
                            $duration_days = floor($raw_diff/(60*60*24));
                            $duration_days_markup = $duration_days;
                        }
                                
                        $scf_markup = UtilityGeneralFormulas::getRoundSigDigs($record['ot_scf']);
                        
                        $status_set_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['status_set_dt']);
                        $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['updated_dt']);
                        //$created_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['created_dt']);
                        
                        if(strpos($this->m_aPersonRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                        {
                            $sCommentsMarkup = '';
                            $sViewMarkup = '';
                        } else {
                            //$sCommentsMarkup = l('Comments', $this->m_urls_arr['comments'], array('query' => array('sprintid' => $sprintid)));
                            //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('sprintid'=>$sprintid)));
                            
                            $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            $sCommentsMarkup = "<a title='jump to communications for #{$sprintid}'href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                            $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            $sViewDashboardMarkup = "<a title='jump to dashboard for #{$sprintid}'href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";

                            $sprint_membership_page_url = url($this->m_urls_arr['sprint_membership'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            if($status_terminal_yn == 1 || $sprint_membership_locked_yn == 1)
                            {
                                $sViewSprintVisualMarkup = "<a title='jump to sprint membership for #{$iteration_ct}' href='$sprint_membership_page_url'><img src='$sprint_membership_dim_icon_url'/></a>";
                            } else {
                                $sViewSprintVisualMarkup = "<a title='jump to sprint membership for #{$iteration_ct}' href='$sprint_membership_page_url'><img src='$sprint_membership_icon_url'/></a>";
                            }
                            
                            $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            $sViewMarkup = "<a title='view details of #{$sprintid}' href='$view_page_url'><img src='$view_icon_url'/></a>";                            
                    
                        }
                        if(strpos($this->m_aPersonRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                        {
                            $sEditMarkup = '';
                        } else {
                            //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('sprintid'=>$sprintid)));
                            $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                            $sEditMarkup = "<a title='edit #{$sprintid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";

                        }
                        if(($last_sprint_number > $iteration_ct) || strpos($this->m_aPersonRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                        {
                            $sDeleteMarkup = '';
                        } else {
                            //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('sprintid'=>$sprintid)));
                            $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('sprintid'=>$sprintid, 'return' => $cmi['link_path'])));
                            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                            $sDeleteMarkup = "<a title='delete sprint#{$sprintid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                        }
                        $name = "Sprint#$iteration_ct";
                        $story_len = strlen($story_tx);
                        if($story_len > 256)
                        {
                            $fitted_story_tx = "<span title='Partial display here of the $story_len characters story text'>".substr($story_tx,0,256).'...</span>';
                        } else {
                            $fitted_story_tx = $story_tx;
                        }
                        if(empty($title_tx))
                        {
                            $story_markup = $fitted_story_tx;
                        } else {
                            $story_markup = "<span class='comment-title' title='title for this story'>$title_tx</span><br>$fitted_story_tx";
                        }
                        if($sprint_membership_locked_yn == 1)
                        {
                            $membership_locked_markup = "Yes";
                        } else {
                            $membership_locked_markup = "No";
                        }
                        $rows   .= "\n".'<tr><td>'
                                . $sprintid.'</td><td>'
                                . $name.'</td><td>'
                                . $workitem_counts.'</td><td>'
                                . $start_dt_markup.'</td><td>'
                                . $end_dt_markup.'</td><td>'
                                . $duration_days_markup.'</td><td>'
                                . $status_markup.'</td><td>'
                                . $membership_locked_markup.'</td><td>'
                                . $score_markup.'</td><td>'
                                . $story_markup.'</td><td>'
                                . $terminalyesno_markup.'</td><td>'
                                . $status_set_dt_markup . '</td>'
                                . '<td>' . $updated_dt_markup . '</td>'
                                . '<td class="action-options">'    
                                    . $sCommentsMarkup . ' '
                                    //. $sViewDashboardMarkup . ' '
                                    . $sViewSprintVisualMarkup . ' '
                                    . $sViewMarkup . ' '
                                    . $sEditMarkup . ' '
                                    . $sDeleteMarkup . '</td>'
                                . '</tr>';
                    }
                }

                $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                         '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                            . '<thead>'
                            . '<tr>'
                            . '<th><span title="The system unique ID of each sprint">' . t('ID') . '</span></th>'
                            . '<th><span title="The name always shows the iteration number">' . t('Name') .'</span></th>'
                            . '<th><span title="Member workitem count">' . t('MWC') .'</span></th>'
                            . '<th><span title="The official start date of the sprint">' . t('Start Date') .'</span></th>'
                            . '<th><span title="The official end date of the sprint">' . t('End Date') .'</span></th>'
                            . '<th><span title="The number of days in the sprint">' . t('Duration') .'</span></th>'
                            . '<th><span title="The status of the sprint">' . t('Status') .'</span></th>'
                            . '<th><span title="Is membership frozen for the sprint?">' . t('Locked') .'</span></th>'
                            . '<th><span title="The official score for this sprint on a scale from 0 to 100 (100 is perfect)">' . t('Score') .'</span></th>'
                            . '<th><span title="An optional theme for this sprint">' . t('Theme') .'</span></th>'
                            . '<th><span title="Yes if no further work is expected for this">' . t('Done') . '</span></th>'
                            . '<th><span title="Date of most recent status update">' . t('Status Date') . '</span></th>'
                            . '<th><span title="Date of most recent change">' . t('Updated') . '</span></th>'
                            . '<th class="action-options">' . t('Action Options').'</th>'
                            . '</tr>'
                            . '</thead>'
                            . '<tbody>'
                            . $rows
                            .  '</tbody>'
                            . '</table>'
                            . '<br>');
            }
            
            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            if($show_add_button)
            {
                if(isset($this->m_urls_arr['add']))
                {
                    if(strpos($this->m_aPersonRights,'A') !== FALSE)
                    {
                        $initial_button_markup = l('ICON_ADD Add Sprint',$this->m_urls_arr['add'],array('query'=>array('projectid'=>$owner_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                            ));
                        $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                        $form['data_entry_area1']['action_buttons']['addsprint'] = array('#type' => 'item'
                                , '#markup' => $final_button_markup);
                    }
                }
            }

            if (isset($this->m_urls_arr['durationconsole'])) 
            {
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

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
