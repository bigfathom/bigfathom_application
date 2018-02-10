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
require_once 'helper/ProjectContextPageHelper.php';

/**
 * Edit project_context
 *
 * @author Frank Font
 */
class EditProjectContextPage extends \bigfathom\ASimpleFormPage
{
    protected $m_project_contextid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($project_contextid)
    {
        if (!isset($project_contextid) || !is_numeric($project_contextid)) {
            throw new \Exception("Missing or invalid project_contextid value = " . $project_contextid);
        }
        
        //Check for illegal access attempt
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        if(!$upb['roles']['systemroles']['summary']['is_systemadmin'])
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit project_contextid=$project_contextid!!!");
            throw new \Exception("Illegal access attempt!");
        }        
        
        $this->m_project_contextid = $project_contextid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/project_context';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\ProjectContextPageHelper($urls_arr);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_project_contextid);
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'E');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $project_contextid = $myvalues['id'];
            db_update(DatabaseNamesHelper::$m_project_context_tablename)->fields(array(
                  'shortname' => $myvalues['shortname'],
                  'description_tx' => $myvalues['description_tx'],
                  'active_yn' => $myvalues['active_yn'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
                ))
                    ->condition('id', $project_contextid,'=')
                    ->execute(); 
            //If we are here then we had success.
            $msg = 'Saved update for ' . $myvalues['shortname'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['name']
                      . ' project context because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
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
        
        $form = $this->m_oPageHelper->getForm('E',$base_form
                , $form_state, $disabled, $myvalues, $html_classname_overrides);
        

        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array($html_classname_overrides['action-button'])
            ),
            '#value' => t('Save Project Context Updates'),
            '#disabled' => FALSE
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
