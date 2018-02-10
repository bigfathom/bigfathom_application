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

/**
 * This class manages application testing
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageAppTestingPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aDataRights        = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_oUnitTestHarness   = NULL;
    
    protected $m_launchmode = NULL;
    protected $m_module_launchfilter = NULL;
    
    public function __construct($launchmode=NULL,$module_launchfilter=NULL)
    {
        
        $this->m_launchmode=$launchmode;
        if($module_launchfilter === NULL)
        {
            $module_launchfilter = [];
        } else {
            if(!is_array($module_launchfilter))
            {
                $module_launchfilter = explode(',', $module_launchfilter);
            }
        }
        $this->m_module_launchfilter=[];
        foreach($module_launchfilter as $mname)
        {
            $clean = strtolower(trim($mname));
            $this->m_module_launchfilter[$clean] = $clean;
        }
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_oMarkupHelper = new \bigfathom\MarkupHelper();
        $urls_arr = array();
        $urls_arr['launch_tests'] = 'bigfathom/sitemanage/apptesting/launch';
        $urls_arr['cancel_tests'] = 'bigfathom/sitemanage/apptesting/cancel';
        $urls_arr['return'] = 'bigfathom/sitemanage';
        
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
        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $loaded2 = module_load_include('php','bigfathom_core','unit_tests/UnitTestHarness');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the UnitTestHarness class');
        }
        $this->m_oUnitTestHarness = new \bigfathom\UnitTestHarness();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $user;
            global $base_url;
            
            $main_tablename = 'apptesting-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            $testsequence_data_raw = [];
            
            $alltestgroups = $this->m_oUnitTestHarness->getAllAvailableTestGroups();
            //\bigfathom\DebugHelper::showNeatMarkup($alltestgroups,"LOOK all the test groups");
            $rows = "\n";
            $seqnum=0;
            $count_test_groups_to_launch = 0;
            foreach($alltestgroups as $modulename=>$module_content)
            {
                $clean_module_name = strtolower(trim($modulename));
                if(!empty($this->m_module_launchfilter))
                {
                    $allow_launch = isset($this->m_module_launchfilter[$clean_module_name]);
                } else {
                    $allow_launch = TRUE;
                }
                foreach($module_content as $classname=>$classinstance)
                {
                    $seqnum++;
                    $nicename = $classinstance->getNiceName();
                    $classname = $classinstance->getClassName();
                    $version_num = $classinstance->getVersionNumber();
                    $testmethods_ar = $classinstance->getAllTestMethods();
                    $testcount = count($testmethods_ar);

                    $idroot = "{$modulename}X{$classname}";
                    $setupmarkup = "<p id='status_{$idroot}XsetUp'><!-- setup info goes here --></p>";
                    $teardownmarkup = "<p id='status_{$idroot}XtearDown'><!-- teardown info goes here --></p>";
                    $summary_markup_id = "summary_markup_{$idroot}";
                    
                    if($allow_launch)
                    {
                        $count_test_groups_to_launch++;
                        $testsequence_data_raw[] = array(
                            'classname'=>$classname,
                            'modulename'=>$modulename
                        );
                        $modulename_markup = "<strong>$modulename</strong>";
                        $classname_markup = "<strong>$classname</strong>";
                        $nicename_markup = "<span title='$classname v$version_num'>$nicename</span>";
                        $summary_markup = "<span id='$summary_markup_id'>Not launched</span>";
                    } else {
                        $modulename_markup = "<span>$modulename</span>";
                        $classname_markup = "<span>$classname</span>";
                        $nicename_markup = "<span title='$classname v$version_num'>$nicename</span>";
                        $summary_markup = "<span class='colorful-notice' id='$summary_markup_id'>Will NOT launch</span>";
                    }
                    
                    if($testcount == 0)
                    {
                        $testnames_markup = "{$setupmarkup}Zero Tests Found!{$teardownmarkup}";
                    } else {
                        $item_markup_ar = [];
                        foreach($testmethods_ar as $onemethod)
                        {
                            $item_markup_ar[] = "{$onemethod['nice_name']}<span id='status_{$idroot}X{$onemethod['real_name']}'><!-- status here --></span>";
                        }
                        $testnames_markup = "{$setupmarkup}<ol><li>" 
                                . implode("</li><li>",$item_markup_ar) 
                                . "</li></ol>{$teardownmarkup}";
                    }
                    
                    $rows   .= "\n".'<tr><td>'
                            .$seqnum.'</td><td>'
                            .$modulename_markup.'</td><td>'
                            .$nicename_markup.'</td><td>'
                            .$testnames_markup.'</td><td>'
                            .$summary_markup.'</td>'
                            .'</tr>';
                }
            }

            $testsequence_data_json = json_encode($testsequence_data_raw);
            drupal_add_js(array('personid'=>$user->uid
                    , 'testsequence_data'=>$testsequence_data_json
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images'))
                    , 'setting');
            drupal_add_js("$base_url/$theme_path/node_modules/moment/moment.js");
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            if($this->m_launchmode == 'LAUNCH_NOW')
            {
                drupal_add_js("$base_url/$module_path/form/js/ManageAppTestingPage.js");
            }
            //DO NOT USE THIS ON THIS PAGE!!!! drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            if($count_test_groups_to_launch > 0)
            {
                $initial_top_status_tx = "No tests launched yet";
                $pagetop_tx = $count_test_groups_to_launch . " test groups available";
            } else {
                $initial_top_status_tx = "";
                $pagetop_tx = "ZERO test groups available";
            }
            
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
            $form["blurb_area"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            $form["blurb_area"]['available'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="pagetop-blurb-center">' . $pagetop_tx,
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            $form["blurb_area"]['status'] = array(
                '#type' => 'item', 
                '#prefix' => '<div id="top-test-status-message" class="top-test-status-message">'.$initial_top_status_tx,
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th datatype="formula" title="The execution order of the group relative to other groups">'.t('Order').'</th>'
                                . '<th datatype="formula" title="The module containing the tests">'.t('Module').'</th>'
                                . '<th datatype="formula" title="The nice name for the group of tests">'.t('Group Name').'</th>'
                                . '<th datatype="formula" title="All the individual tests in the group">'.t('Tests').'</th>'
                                . '<th datatype="formula" title="Summary run status">'.t('Status').'</th>'
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

            if($this->m_is_systemadmin)
            {
                if($this->m_launchmode !== 'LAUNCH_NOW')
                {
                    if(isset($this->m_urls_arr['launch_tests']))
                    {
                        if(strpos($this->m_aDataRights,'A') !== FALSE)
                        {
                            $initial_button_markup = l('ICON_LAUNCH Launch Tests',$this->m_urls_arr['launch_tests']
                                        , array('attributes'=>array('class'=>'action-button'))
                                    );
                            $final_button_markup = str_replace('ICON_LAUNCH', '<i class="fa fa-flask" aria-hidden="true"></i>', $initial_button_markup);
                            $form['data_entry_area1']['action_buttons']['launch_tests'] = array('#type' => 'item'
                                    , '#markup' => $final_button_markup);
                        }
                    }
                } else {
                    if($this->m_launchmode !== 'CANCEL_NOW')
                    {
                        if(isset($this->m_urls_arr['cancel_tests']))
                        {
                            $initial_button_markup = l('ICON_LAUNCH Cancel Tests',$this->m_urls_arr['cancel_tests']
                                        , array('attributes'=>array('class'=>'action-button'))
                                    );
                            $final_button_markup = str_replace('ICON_LAUNCH', '<i class="fa fa-stop-circle" aria-hidden="true"></i>', $initial_button_markup);
                            $form['data_entry_area1']['action_buttons']['cancel_tests'] = array('#type' => 'item'
                                , '#prefix'=>"<span id='remove_buttons_on_done'>"
                                , '#markup' => $final_button_markup
                                , '#suffix'=>"</span>"
                                );
                        }
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
