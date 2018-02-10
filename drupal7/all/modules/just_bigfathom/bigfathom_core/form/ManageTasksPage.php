<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
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
require_once 'helper/WorkitemPageHelper.php';

/**
 * This class returns the list of available workitems
 *
 * @author Frank Font of Room4me.com Software LLC
 * @deprecated 2017
 */
class ManageTasksPage extends \bigfathom\ASimpleFormPage
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aPersonRights    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oContext = NULL;
    protected $m_parent_projectid = NULL;
    protected $m_oTextHelper = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['add'] = 'bigfathom/workitem/add';
        $urls_arr['edit'] = 'bigfathom/workitem/edit';
        $urls_arr['view'] = 'bigfathom/workitem/view';
        $urls_arr['delete'] = 'bigfathom/workitem/delete';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';//&projectid=5
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['main_visualization'] = '';
        $aPersonRights='VAED';
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aPersonRights  = $aPersonRights;
        
        $this->m_oPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr);
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
            $main_tablename = 'workitems-table';
            $main_table_containername = "container4{$main_tablename}";
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
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            //$goals_lookup = $this->m_oPageHelper->getGoalOptions(NULL,NULL);
            //$workitems_lookup = $this->m_oPageHelper->getTaskOptions(NULL,NULL);
            $workitem_status_by_code = $this->m_oMapHelper->getWorkitemStatusByCode();
            
            $rows = "\n";
            //$all = $this->m_oMapHelper->getTasksInProjectByID($this->m_parent_projectid);
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            
            global $user;
            $all = $this->m_oMapHelper->getRichWorkitemsByID($this->m_parent_projectid,array('workitem_basetype'=>"'T'"));
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            
            $cmi = $this->m_oContext->getCurrentMenuItem();
            foreach($all as $text_workitem_id=>$record)
            {
                $workitemid = $record['id'];
                $workitem_nm = $record['workitem_nm'];
                $equipmentid = $record['equipmentid'];
                $external_resourceid = $record['external_resourceid'];
                
                if(!empty($equipmentid))
                {
                    $resource_type_code = 'Q';
                } else
                if(!empty($external_resourceid))
                {
                    $resource_type_code = 'X';
                } else {
                    $resource_type_code = 'I';
                }
                
                $effort_est_p = $record['effort_hours_est_p'];
                if($effort_est_p === NULL)
                {
                    $effort_est_markup = "<span>".$record['effort_hours_est']."</span>";
                } else {
                    $effort_est_markup = "<span class='has-more-info' title='$effort_est_p'>".$record['effort_hours_est']."</span>";
                }
                $effort_act_markup = $record['effort_hours_worked_act'];
                
                $activeyesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                $purpose_tx = $record['purpose_tx'];
                if(strlen($purpose_tx) > 80)
                {
                    $purpose_tx = substr($purpose_tx, 0,80) . '...';
                }
                
                $status_cd = $record['status_cd'];
                if($status_cd != NULL)
                {
                    $status_record = $workitem_status_by_code[$status_cd];
                    $status_title_tx = $status_record['title_tx'];
                    $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                    $status_terminal_yn = $status_record['terminal_yn'];
                    $terminalyesno = ($status_terminal_yn == 1 ? 'Yes' : '<span class="colorful-available">No</span>');
                } else {
                    $status_markup = "";
                    $terminalyesno = "";
                }
                
                $owner_personid = $record['owner_personid'];
                $owner_persondetail = $people[$owner_personid];
                $owner_personname = $owner_persondetail['first_nm'] . " " . $owner_persondetail['last_nm'];
                $owner_markup = "<span title='#$owner_personid'>$owner_personname</span>";
                
                $status_set_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['status_set_dt']);
                $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['updated_dt']);
                //$created_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['created_dt']);
                
                if(strpos($this->m_aPersonRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                } else {
                    $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}'href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('workitemid'=>$workitemid)));
                    $sViewDashboardMarkup = "<a title='jump to dashboard for #{$workitemid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";

                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                            , array('query'=>array('projectid'=>($this->m_parent_projectid)
                                , 'jump2workitemid'=>$workitemid, 'return' => $cmi['link_path']
                            )));
                    $sHierarchyMarkup = "<a "
                        . " title='view dependencies for workitem#{$workitemid} in project#{$this->m_parent_projectid}' "
                        . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view details of #{$workitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aPersonRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('workitemid'=>$workitemid)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$workitemid}'href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($this->m_aPersonRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('workitemid'=>$workitemid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='jump to delete for #{$workitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";                }
                    
                //$ddw_markup = implode(', ', $record['maps']['ddw']);
                $ddw_ar = $record['maps']['ddw'];
                if(count($ddw_ar) == 0)
                {
                    $ddw_markup = '';
                } else {
                    asort($ddw_ar);
                    $ddw_markup = "[SORTNUM:" . count($ddw_ar) . "]<span title='" . count($ddw_ar) . " items'>" . implode(', ', $ddw_ar) . "</span>";                
                }
                    
                $workitemname_markup = "<span title='#$workitemid'>$workitem_nm</span>";
                $rows   .= "\n".'<tr><td>'
                        . $workitemid.'</td><td>'
                        . $resource_type_code.'</td><td>'
                        . $ddw_markup.'</td><td>'
                        . $workitemname_markup.'</td><td>'
                        . $purpose_tx.'</td><td>'
                        . $owner_markup.'</td><td>'
                        . $record['importance'].'</td><td>'
                        . $status_markup.'</td>'
                        . '<td class="number">'
                        . $effort_est_markup
                        . '</td>'
                        . '<td class="number">'
                        . $effort_act_markup
                        . '</td>'
                        . '<td>'
                        . $terminalyesno.'</td><td>'
                        . $status_set_dt_markup . '</td><td>'
                        . $updated_dt_markup.'</td>'
                        . '<td class="action-options">'    
                                . $sCommentsMarkup . ' '
                                //. $sViewDashboardMarkup . ' '
                                . $sHierarchyMarkup . ' '
                                . $sViewMarkup . ' '
                                . $sEditMarkup . ' '
                                . $sDeleteMarkup . '</td>'
                        . '</tr>';
            }

            $form["data_entry_area1"]['table_container']['maintable'] = array('#type' => 'item',
                     '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th colname="id" datatype="formula"><span title="The system unique ID of each workitem">' . t('ID') . '</span></th>'
                                . '<th colname="rt" datatype="string">'
                                . '<span title="The resource type for the workitem; I=internal human resource, X=external human resource, Q=non-human resource">' . t('RT') . '</span></th>'
                                . '<th colname="ddw" datatype="formula"><span title="Directly dependent workitems">' . t('DDW').'</span></th>'
                                . '<th>' . t('Task Name').'</th>'
                                . '<th>' . t('Purpose').'</th>'
                                . '<th>' . t('Owner').'</th>'
                                . '<th>' . t('Importance').'</th>'
                                . '<th>' . t('Status') . '</th>'
                                . '<th class="number"><span title="Estimated Effort Hours">' . t('EE') . '</span></th>'
                                . '<th class="number"><span title="Actual Effort Hours">' . t('AE') . '</span></th>'
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


            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $add_link_markup = l('Add Task'
                            , $this->m_urls_arr['add']
                            , array('query' => array(
                                'projectid' => $this->m_parent_projectid,
                                'basetype' => 'T', 'return' => $cmi['link_path'])
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
