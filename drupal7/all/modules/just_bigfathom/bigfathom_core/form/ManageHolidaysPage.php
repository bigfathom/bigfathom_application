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
require_once 'helper/HolidayPageHelper.php';

/**
 * This page presents the holidays available in the system
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageHolidaysPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights  = NULL;
    protected $m_oPageHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addholiday';
        $urls_arr['edit'] = 'bigfathom/editholiday';
        $urls_arr['view'] = 'bigfathom/viewholiday';
        $urls_arr['delete'] = 'bigfathom/deleteholiday';
        
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
        
        $this->m_oPageHelper = new \bigfathom\HolidayPageHelper($urls_arr);
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
            $main_tablename = 'holiday-table';
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
            $all = $this->m_oMapHelper->getHolidaysByID();
            foreach($all as $holidayid=>$record)
            {
                $holiday_dt = $record['holiday_dt'];
                $shortname = $record['holiday_nm'];
                $countryid = $record['countryid'];
                $stateid = $record['stateid'];
                $country_name = $record['country_name'];
                $state_name = $record['state_name'];
                $country_abbr = $record['country_abbr'];
                $state_abbr = $record['state_abbr'];
                $apply_to_all_users_yn = $record['apply_to_all_users_yn'];
                $comment_tx = $record['comment_tx'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];
                
                $holiday_dt_markup = "$holiday_dt";
                
                if($updated_dt !== $created_dt)
                {
                    $updated_markup = "<span title='Created $created_dt'>$updated_dt</span>";
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
                
                $shortname_markup = "$shortname";
                if(empty($countryid))
                {
                    $country_markup = "[SORTNUM:0]-";
                } else {
                    $country_markup = "[SORTNUM:$countryid]<span title='$country_abbr (code#$countryid)'>$country_name</span>";
                }
                if(empty($stateid))
                {
                    $state_markup = "[SORTNUM:0]-";
                } else {
                    $state_markup = "[SORTNUM:$stateid]<span title='$state_abbr'>$state_name</span>";
                }
                $apply_to_all_users_yn_markup = ($apply_to_all_users_yn == 1 ? 'Yes' : 'No');
                
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('holidayid'=>$holiday_id)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('holidayid'=>$holidayid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view #{$holidayid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('holidayid'=>$holiday_id)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('holidayid'=>$holidayid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$holidayid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('holidayid'=>$holiday_id)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('holidayid'=>$holidayid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete #{$holidayid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $rows   .= "\n".'<tr><td>'
                        .$holiday_dt_markup.'</td><td>'
                        .$shortname_markup.'</td><td>'
                        .$country_markup.'</td><td>'
                        .$state_markup.'</td><td>'
                        .$apply_to_all_users_yn_markup.'</td><td>'
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
                                . '<th datatype="date" title="When this holiday applies">'.t('Date').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Name for this holiday">'.t('Name').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Country to which this holiday applies if country specific">'.t('Country').'</th>'
                                . '<th datatype="formula" class="nowrap" title="State to which this holiday applies if not national">'.t('State').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Apply this holiday by default to all users in matching country/state when projecting availability">'.t('Default').'</th>'
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
                    $initial_button_markup = l('ICON_ADD Add New Holiday',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addholiday'] = array('#type' => 'item'
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
