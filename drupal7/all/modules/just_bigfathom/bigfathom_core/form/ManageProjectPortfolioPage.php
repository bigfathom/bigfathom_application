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
require_once 'helper/ProjectPortfolioPageHelper.php';

/**
 * This class returns the list of available project_portfolio
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageProjectPortfolioPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aElementRights  = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_oTextHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['add'] = 'bigfathom/addproject_portfolio';
        $urls_arr['edit'] = 'bigfathom/editproject_portfolio';
        $urls_arr['view'] = 'bigfathom/viewproject_portfolio';
        $urls_arr['delete'] = 'bigfathom/deleteproject_portfolio';
        $urls_arr['return'] = 'bigfathom/sitemanage';
        $urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapEquipmentGoals.html';

        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        //if($upb['roles']['systemroles']['summary']['is_systemadmin'])
        if($is_systemdatatrustee)
        {
            $aElementRights='VAED';
        } else {
            $aElementRights='V';
        }
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aElementRights  = $aElementRights;
        
        $this->m_oPageHelper = new \bigfathom\ProjectPortfolioPageHelper($urls_arr);
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
            $main_tablename = 'projectcontext-table';
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
            
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "A portfolio is a named collection of projects.  You can examine the membership of each portfolio via this interface."
                . "</p>",
                '#suffix' => '</div>',
            );

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all = $this->m_oMapHelper->getProjectPortfoliosByID();
            foreach($all as $project_portfolioid=>$record)
            {
                $portfolio_nm = $record['portfolio_nm'];
                $purpose_tx = $record['purpose_tx'];
                $owner_personid = $record['owner_personid'];
                $owner_tx = $record['first_nm'] . ' ' . $record['last_nm'];
                
                $owner_markup ="<span title='#$owner_personid'>$owner_tx</span>";
                $name_markup = "<span title='#$project_portfolioid'>$portfolio_nm</span>";
                if(strlen($purpose_tx) > 100)
                {
                    $purpose_markup = substr($purpose_tx, 0, 100) . "...";
                } else {
                    $purpose_markup = $purpose_tx;
                }
                $member_count = $record['member_count'];
                $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['updated_dt']);
                $created_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['created_dt']);
                
                if(strpos($this->m_aElementRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('project_portfolioid'=>$project_portfolioid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view #{$project_portfolioid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aElementRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('project_portfolioid'=>$project_portfolioid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$project_portfolioid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($this->m_aElementRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('project_portfolioid'=>$project_portfolioid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete #{$project_portfolioid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $rows   .= "\n".'<tr><td>'
                        . $name_markup.'</td><td>'
                        . $member_count.'</td><td>'
                        . $purpose_markup.'</td><td>'
                        . $owner_markup.'</td><td>'
                        . $updated_dt_markup . '</td><td>'
                        . $created_dt_markup . '</td>'
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
                                . '<th class="nowrap" title="Name of this portfolio">'.t('Name').'</th>'
                                . '<th class="nowrap" title="Number of projects in this portfolio">'.t('Projects').'</th>'
                                . '<th title="Purpose of this portfolio">'.t('Purpose').'</th>'
                                . '<th class="nowrap" title="The owner of this portfolio">'.t('Owner').'</th>'
                                . '<th>'.t('Updated').'</th>'
                                . '<th>'.t('Created').'</th>'
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

            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aElementRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Create New Project Portfolio',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addproject_portfolio'] = array('#type' => 'item'
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
