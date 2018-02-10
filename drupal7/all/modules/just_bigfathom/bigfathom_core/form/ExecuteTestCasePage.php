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
require_once 'helper/TestCasePageHelper.php';

/**
 * Execute steps of a test case
 *
 * @author Frank Font
 */
class ExecuteTestCasePage extends \bigfathom\ASimpleFormPage
{
    protected $m_testcaseid  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($testcaseid, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        if (!isset($testcaseid) || !is_numeric($testcaseid)) {
            throw new \Exception("Missing or invalid testcaseid value = " . $testcaseid);
        }
        $this->m_testcaseid = $testcaseid;
        
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_projectid = $this->m_oMapHelper->getProjectIDForTestCaseID($testcaseid); 
        
        $urls_arr = [];
        $urls_arr['return'] = 'bigfathom/projects/testcases';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\TestCasePageHelper($urls_arr,NULL,$this->m_projectid);
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        //if(!$this->m_is_systemwriter)
        
        global $user;    
        $aDataRights = $this->m_oMapHelper->getTestCaseActionPrivsOfPerson($testcaseid, $user->uid);       
        if(strpos($aDataRights,'T') === FALSE)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to execute testcase#$testcaseid!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_testcaseid);
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
        
        if(empty($this->m_urls_arr['rparams']))
        {
            $rparams_ar = [];
        } else {
            $rparams_ar = unserialize(urldecode($this->m_urls_arr['rparams']));
        }
        
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('T',$base_form
                , $form_state
                , $disabled
                , $myvalues
                , $html_classname_overrides);
        
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Exit',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['exit'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        $show_notify_button = TRUE;
        if($show_notify_button)
        {
            $onclick = "bigfathom_util.testcase_ui_toolkit.actionSendNotifications();";
            $sNotifyButtonMarkup = '<span id="btn_send_notifications" onclick="'.$onclick.'" class="'. $html_classname_overrides['action-button'] .'">Send Notifications</span>';
            $form['data_entry_area1']['action_buttons']['notify'] = array('#type' => 'item'
                    , '#markup' => $sNotifyButtonMarkup);
        }
        
        return $form;
    }
}
