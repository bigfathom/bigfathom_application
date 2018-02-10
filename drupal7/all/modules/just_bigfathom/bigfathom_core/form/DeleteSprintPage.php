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
require_once 'helper/SprintPageHelper.php';

/**
 * Delete one Sprint
 *
 * @author Frank Font
 */
class DeleteSprintPage extends \bigfathom\ASimpleFormPage
{
    protected $m_sprintid    = NULL;
    protected $m_projectid   = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oMapHelper  = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($sprintid, $projectid=NULL, $urls_arr=NULL)
    {
        if (!isset($sprintid) || !is_numeric($sprintid)) 
        {
            throw new \Exception("Missing or invalid sprintid value = " . $sprintid);
        }
        $this->m_sprintid = $sprintid;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        if (!isset($projectid) || !is_numeric($projectid)) 
        {
            $projectid = $this->m_oMapHelper->getProjectIDForSprint($sprintid);
        }
        $this->m_projectid = $projectid;
        if($urls_arr == NULL)
        {
            $urls_arr = array();
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\SprintPageHelper($urls_arr,NULL,$this->m_projectid);
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_sprintid);
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
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $sprintid = $myvalues['id'];
            db_delete(DatabaseNamesHelper::$m_sprint_tablename)
              ->condition('id', $sprintid)
              ->execute(); 
            
            //If we are here then we had success.
            $this->m_oWriteHelper->markProjectUpdated($this->m_projectid, "deleted sprint item#$sprintid");
            $msg = 'Deleted sprint iteration ' . $myvalues['iteration_ct'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to delete sprint iteration ' . $myvalues['iteration_ct']
                      . ' sprint because ' . $ex->getMessage());
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
        $buttontext = 'Delete Sprint From System';
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
