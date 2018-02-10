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
 *
 */

namespace bigfathom;

require_once 'ASimpleHelpPage.php';

/**
 * View inforamtion about the application
 *
 * @author Frank Font
 */
class ViewAboutPage extends \bigfathom\ASimpleHelpPage
{
    function __construct($urls_arr=NULL)
    {
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }   
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($urls_arr);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return array();
    }
    
    /**
     * Return rows of display information for one module
     */
    function getInfoRowsForModule($module_drupal_info)
    {
        $rows = array();
        $module_name = $module_drupal_info->name;
        if(module_exists($module_name))
        {
            $function_name = "{$module_name}_info";
            $info = $function_name();
            $shown = array();
            $keynames = array('version'=>'Version', 'site_config'=>'Site Config');
            foreach($keynames as $k=>$v)
            {
                if(array_key_exists($k, $info))
                {
                    $shown[$k] = $k;
                    $rows[]  = "<td>$v</td><td>" . $info[$k] . "</td>";
                }
            }
            foreach($info as $k=>$v)
            {
                if(!array_key_exists($k, $shown))
                {
                    $rows[]  = "<td>$k</td><td>" . $v . "</td>";
                }
            }
        }
        return $rows;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {
        $module_count = 0;
        
        $app_context = "<div class='about-app'>"
                . "<h1>About This Bigfathom Application Instance</h1>"
                . "<p>The Bigfathom application is a tool to help people think bigger through clear understanding and communication and realistic assessments for continuous improvement.<p>"
                . "<p>This tool is currently only marketed to users in the USA and its territories where US laws apply.  It is NOT currently intended for use outside of US laws and jurisdictions.  Please contact Room4me.com Software LLC for licensing information outside of the current supported areas.<p>"
                . "<p>By using this application, you are agreeing to the jurisdiction of US laws in all aspects of its use and that all legal questions to the extent allowed by law will be addressed within the state of Maryland under its laws and legal protocols.</p>"
                . "<p>Do NOT use this application if you are subject to laws governing websites, web services, website data collection polices, software and software product and software services outside of exclusive US governance.<p>"
                . "</div>";

        $form['data_entry_area1']["app_context"]    = array(
            '#type' => 'item',
            '#markup' => $app_context,
        );

        $start_ms = microtime(TRUE);
        $start_timestamp = time();
        $start_show = gmdate("Y-m-d\TH:i:s\Z", $start_timestamp);
        
        $server_status = "<div class='about-app'>"
                . "<h3>Application Server Information</h3>"
                . "<table>"
                . "<tr><td>Status... </td><td>OK</td></tr>"
                . "<tr><td>Your Server Current Time... </td><td>$start_show</td></tr>"
                . "</table>";
        
        $form['data_entry_area1']["server_status"]    = array(
            '#type' => 'item',
            '#markup' => $server_status,
        );
        
        global $user;
        $modules_enabled_status = [];
        $rows = [];
        $modules = system_rebuild_module_data();
        foreach($modules as $k=>$module_drupal_info)
        {
            $module_count++;
            $module_basicinfo = $module_drupal_info->info;
            if($module_basicinfo['package'] == 'Bigfathom')
            {
                $module_name = $module_drupal_info->name;
                $module_basicinfo = $module_drupal_info->info;
                $rows = $this->getInfoRowsForModule($module_drupal_info);
                $rows_markup = "<tr>" . implode("</tr><tr>", $rows) . "</tr>";
                $module_enabled = count($rows) > 0;
                $modules_enabled_status[$module_name] = $module_enabled;
                $show_name = $module_basicinfo['name'];
                $show_description = $module_basicinfo['description'];
                
                $form['data_entry_area1']["title_$module_name"]    = array(
                    '#type' => 'item',
                    '#prefix' => 
                        "<h2 title='$show_description'>",
                    '#markup' => $show_name,
                    '#suffix' => '</h2>',
                );
                if(!$module_enabled)
                {
                    $form['data_entry_area1']["table_$module_name"]    = array(
                        '#type' => 'item',
                        '#prefix' => 
                            "<p>",
                        '#markup' => "Files are installed but module is not enabled.",
                        '#suffix' => '</p>',
                    );
                } else {
                    $form['data_entry_area1']["table_$module_name"]    = array(
                        '#type' => 'item',
                        '#prefix' => 
                            "<table id='info_about_{$module_name}'>",
                        '#markup' => $rows_markup,
                        '#suffix' => '</table>',
                    );
                }
            }
        }
        /*
        if($modules_enabled_status['bigfathom_dev'] && $user->uid == 1)
        {
            ob_start();
            phpinfo();
            $info = ob_get_contents();
            ob_end_clean();
            $form['data_entry_area1']["phpinfo"]    = array(
                '#type' => 'item',
                '#prefix' => 
                    "<div>",
                '#markup' => $info,
                '#suffix' => '</div>',
            );
        }
        */
        
        $this->populateFormBottomElements($form);
        return $form;
        
    }
}
