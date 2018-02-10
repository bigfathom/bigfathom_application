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
require_once 'helper/URIDomainPageHelper.php';

/**
 * This class returns the list of available domains
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageURIDomainPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aDataRights        = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_oTextHelper        = NULL;
    
    public function __construct()
    {
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_oMarkupHelper = new \bigfathom\MarkupHelper();
        $urls_arr = array();
        $urls_arr['add'] = 'bigfathom/adduridomain';
        $urls_arr['edit'] = 'bigfathom/edituridomain';
        $urls_arr['view'] = 'bigfathom/viewuridomain';
        $urls_arr['delete'] = 'bigfathom/deleteuridomain';
        $urls_arr['return'] = 'bigfathom/sitemanage';
        $urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapLocationGoals.html';
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($this->m_is_systemadmin)
        {
            $aDataRights='VAED';
        } else {
            $aDataRights='V';
        }
        $this->m_aDataRights  = $aDataRights;
        
        $this->m_urls_arr     = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\URIDomainPageHelper($urls_arr);
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
            $main_tablename = 'location-table';
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
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $domain_bundle = $this->m_oMapHelper->getAllRichRemoteURIBundle();
            //DebugHelper::showNeatMarkup($domain_bundle,"LOOK DOMAIN BUNDLE");
            $all = $domain_bundle['whitelist_domains'];
            foreach($all as $domain_tx=>$record)
            {
                
                $uri_domain_markup = "[SORTSTR:$domain_tx]<span>$domain_tx</span>";
                $person_name = $record['person_name'];
                $person_markup = "[SORTSTR:$person_name]<span>$person_name</span>";
                $created_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['created_dt']);
                
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('uridomain'=>$uridomain)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('uridomain'=>$domain_tx)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view {$domain_tx}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('uridomain'=>$uridomain)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('uridomain'=>$domain_tx)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit {$domain_tx}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('uridomain'=>$uridomain)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('uridomain'=>$domain_tx)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete {$domain_tx}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $rows   .= "\n".'<tr><td>'
                        .$uri_domain_markup.'</td><td>'
                        .$person_markup.'</td><td>'
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
                                . '<th datatype="formula" title="The domain component of a URI">'.t('Domain').'</th>'
                                . '<th datatype="formula" title="Who created this entry">'.t('Created By').'</th>'
                                . '<th datatype="date" title="When this entry was created or last updated">'.t('Created Date').'</th>'
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
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Add Domain',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addlocation'] = array('#type' => 'item'
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
