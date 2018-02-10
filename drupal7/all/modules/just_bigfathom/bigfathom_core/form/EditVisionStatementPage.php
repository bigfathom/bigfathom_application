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
require_once 'helper/VisionStatementPageHelper.php';

/**
 * Edit vision statement
 *
 * @author Frank Font
 */
class EditVisionStatementPage extends \bigfathom\ASimpleFormPage
{
    protected $m_visionstatementid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($visionstatementid)
    {
        if (!isset($visionstatementid) || !is_numeric($visionstatementid)) {
            throw new \Exception("Missing or invalid visionstatementid value = " . $visionstatementid);
        }
        $this->m_visionstatementid = $visionstatementid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/visionstatements';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\VisionStatementPageHelper($urls_arr);
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit visionstatement#$visionstatementid!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_visionstatementid);
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
            $visionstatementid = $myvalues['id'];
            db_update(DatabaseNamesHelper::$m_visionstatement_tablename)->fields(array(
                  'statement_nm' => $myvalues['statement_nm'],
                  'statement_tx' => $myvalues['statement_tx'],
                  'references_tx' => $myvalues['references_tx'],
                  'active_yn' => $myvalues['active_yn'],
                  'owner_personid' => $myvalues['owner_personid'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
                ))
                    ->condition('id', $visionstatementid,'=')
                    ->execute(); 
            //If we are here then we had success.
            $msg = 'Saved update for ' . $myvalues['statement_nm'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['statement_nm']
                      . ' statement because ' . $ex->getMessage());
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
            '#value' => t('Save Statement Updates'),
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
