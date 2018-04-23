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

namespace bigfathom_template_library;

require_once 'helper/ASimpleFormPage.php';

/**
 * Administrative configuration of the module
 *
 * @author Frank Font
 */
class ConfigureModulePage extends \bigfathom_template_library\ASimpleFormPage
{

    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    protected $m_action = NULL;
    
    function __construct($action=NULL)
    {
        
        $this->m_action = $action;
        
        $urls_arr = [];
        $urls_arr['return'] = 'admin/modules';
        $this->m_urls_arr = $urls_arr;
        
        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee && !$this->m_is_systemdatatrustee)
        {
            error_log("HACKING WARNING: uid#{$user->uid} attempted to manage the template library module configuration!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return [];
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return TRUE;
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        $transaction = db_transaction();
        try
        {
            //TODO
        }
        catch(\Exception $ex)
        {
            $msg = "Failed configuration of module because " . $ex;
            $transaction->rollback();
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    private function getCurrentTemplateFilesBundle()
    {
        $bundle = [];
        
        $install_realpath = dirname(__FILE__)."/../install";
        $install_templates_realpath = "$install_realpath/templates";

        $bundle['real_path'] = $install_templates_realpath;
        
        $files = [];
        
        $templates_ar = scandir($install_templates_realpath);
        $core_template_count = 0;
        foreach($templates_ar as $filename)
        {
            try
            {
                if(strlen($filename)>2)
                {
                    $core_template_count++;
                    $file_path = "$install_templates_realpath/$filename";
                    $files[] = $filename;
                }
            } catch (\Exception $ex) {
                drupal_set_message("Failed to list $filename",'error');
            }
        }
        
        asort($files);
        
        $bundle['count'] = $core_template_count;
        $bundle['files'] = $files;
        return $bundle;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($base_form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        
        //TODO
        $form = [];
        
        $tfb = $this->getCurrentTemplateFilesBundle();
        $files_ar = $tfb['files'];
        $files_markup = "<h2>Core Template Files</h2>"
                . "<table>";
        $filecount=0;
        foreach($files_ar as $filename)
        {
            $filecount++;
            $files_markup .= "<tr><td>$filecount</td><td>$filename</td></tr>";
        }
        
        $files_markup .= "</table>";
        
        if($this->m_action == "RELOAD")
        {
            module_load_include('inc','bigfathom_template_library','install/DemoData');
            
            $demodata = new \bigfathom_template_library\DemoData();
            $demodata->load();
            $files_markup = "<p>RELOADED</p>";
            
        }
        
        $form['data_entry_area1']['body']['files_info'] = array('#type' => 'item'
                    , '#markup' => $files_markup);

        
        $RELOADURL="?q=admin/config/system/bigfathom_template_library&action=RELOAD";
        $CLICKMARKUP="<a href='{$RELOADURL}'>here</a>";
        
        $reload_templates_markup = "<p>To reload the database from template files listed above, click {$CLICKMARKUP}</p>";
        $form['data_entry_area1']['body']['action'] = array('#type' => 'item'
            , '#markup' => $reload_templates_markup);
        
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Close',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $form;
    }
}
