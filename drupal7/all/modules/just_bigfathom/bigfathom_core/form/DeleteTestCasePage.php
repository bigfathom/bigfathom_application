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
require_once 'helper/TestCasePageHelper.php';

/**
 * Delete one testcase
 *
 * @author Frank Font
 */
class DeleteTestCasePage extends \bigfathom\ASimpleFormPage
{
    protected $m_testcaseid     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    
    function __construct($testcaseid)
    {
        if (!isset($testcaseid) || !is_numeric($testcaseid)) {
            throw new \Exception("Missing or invalid testcaseid value = " . $testcaseid);
        }
        $this->m_testcaseid = $testcaseid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/projects/testcases';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\TestCasePageHelper($urls_arr);
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        //if(!$this->m_is_systemdatatrustee && !$this->m_is_systemadmin)
        
        global $user;    
        $aDataRights = $this->m_oMapHelper->getTestCaseActionPrivsOfPerson($testcaseid, $user->uid);       
        if(strpos($aDataRights,'D') === FALSE)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to delete testcase#$testcaseid!!!");
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
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'D');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            
            $testcaseid = $myvalues['id'];
            $resultbundle = $this->m_oPageHelper->deleteTestcase($testcaseid);
            drupal_set_message($resultbundle['message']);
            
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to delete ' . $myvalues['testcase_nm']
                      . ' test case because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        $disabled = TRUE;
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $buttontext = 'Delete Test Case From System';
        $form['data_entry_area1']['action_buttons']['delete'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
                );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }

        return $form;
    }
}
