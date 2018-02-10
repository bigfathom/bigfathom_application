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
require_once 'helper/BaselineAvailabilityPageHelper.php';

/**
 * This page presents the baseline availability available in the system
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageBaselineAvailabilityPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights    = NULL;
    protected $m_oPageHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addbaseline_availability';
        $urls_arr['edit'] = 'bigfathom/editbaseline_availability';
        $urls_arr['view'] = 'bigfathom/viewbaseline_availability';
        $urls_arr['delete'] = 'bigfathom/deletebaseline_availability';
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($this->m_is_systemdatatrustee)
        {
            $aDataRights='VAED';
        } else {
            $aDataRights='V';
        }
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aDataRights    = $aDataRights;
        
        $this->m_oPageHelper = new \bigfathom\BaselineAvailabilityPageHelper($urls_arr);
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
            $main_tablename = 'baseline_availability-table';
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

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all = $this->m_oMapHelper->getBaselineAvailabilityByID();
            foreach($all as $baseline_availabilityid=>$record)
            {
                $is_planning_default_yn = $record['is_planning_default_yn'];
                $shortname = $record['shortname'];
                $hours_per_day = $record['hours_per_day'];
                $work_saturday_yn = $record['work_saturday_yn'];
                $work_sunday_yn = $record['work_sunday_yn'];
                $work_monday_yn = $record['work_monday_yn'];
                $work_tuesday_yn = $record['work_tuesday_yn'];
                $work_wednesday_yn = $record['work_wednesday_yn'];
                $work_thursday_yn = $record['work_thursday_yn'];
                $work_friday_yn = $record['work_friday_yn'];
                $work_holidays_yn = $record['work_holidays_yn'];
                $comment_tx = $record['comment_tx'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];
                
                $is_planning_default_yn_markup = $is_planning_default_yn ? "<span class='text-bolder'>Yes</span>" : "No";
                $hours_per_day_markup = "$hours_per_day";
                $work_saturday_yn_markup = $work_saturday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_sunday_yn_markup = $work_sunday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_monday_yn_markup = $work_monday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_tuesday_yn_markup = $work_tuesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_wednesday_yn_markup = $work_wednesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_thursday_yn_markup = $work_thursday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_friday_yn_markup = $work_friday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_holidays_yn_markup = $work_holidays_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                
                if($updated_dt !== $created_dt)
                {
                    $updated_markup = "<span title='Originally created $created_dt'>$updated_dt</span>";
                } else {
                    $updated_markup = "<span title='Never edited'>$updated_dt</span>";
                }
                
                $comment_len = strlen($comment_tx);
                if($comment_len > 256)
                {
                    $comment_markup = substr($comment_tx, 0,256) . '...';
                } else {
                    $comment_markup = $comment_tx;
                }
                
                if($is_planning_default_yn == 1)
                {
                    $shortname_markup = "<span class='text-bolder' title='This availability rule is used by default where an override is not declared'>$shortname</span>";
                } else {
                    $shortname_markup = "$shortname";
                }
                
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('baseline_availabilityid'=>$baseline_availability_id)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('baseline_availabilityid'=>$baseline_availabilityid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view #{$baseline_availabilityid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('baseline_availabilityid'=>$baseline_availability_id)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('baseline_availabilityid'=>$baseline_availabilityid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$baseline_availabilityid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if($is_planning_default_yn || strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('baseline_availabilityid'=>$baseline_availability_id)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('baseline_availabilityid'=>$baseline_availabilityid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete #{$baseline_availabilityid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $rows   .= "\n".'<tr><td>'
                        .$shortname_markup.'</td><td>'
                        .$is_planning_default_yn_markup.'</td><td>'
                        .$hours_per_day_markup.'</td><td>'
                        .$work_saturday_yn_markup.'</td><td>'
                        .$work_sunday_yn_markup.'</td><td>'
                        .$work_monday_yn_markup.'</td><td>'
                        .$work_tuesday_yn_markup.'</td><td>'
                        .$work_wednesday_yn_markup.'</td><td>'
                        .$work_thursday_yn_markup.'</td><td>'
                        .$work_friday_yn_markup.'</td><td>'
                        .$work_holidays_yn_markup.'</td><td>'
                        .$comment_markup.'</td><td>'
                        .$updated_markup.'</td>'
                        .'<td class="action-options">'
                        . $sViewMarkup.' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th datatype="text" class="nowrap" title="Name for this default availability">'.t('Name').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Apply this criteria by default when projecting availability">'.t('Default').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Average number of hours per day for each declared working day">'.t('H/D').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Saturday">'.t('Sat').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Sunday">'.t('Sun').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Monday">'.t('Mon').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Tuesday">'.t('Tue').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Wednesday">'.t('Wed').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Thursday">'.t('Thu').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on Friday">'.t('Fri').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Work on declared Holidays">'.t('Hol').'</th>'
                                . '<th datatype="text">'.t('Notes').'</th>'
                                . '<th datatype="datetime" title="When this record was last updated">'.t('Updated').'</th>'
                                . '<th datatype="html" class="action-options">' . t('Action Options') . '</th>'
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

            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Add New Baseline Availability',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addbaseline_availability'] = array('#type' => 'item'
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
