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
require_once 'helper/PersonPageHelper.php';

/**
 * Change Password
 *
 * @author Frank Font
 */
class ChangePasswordPage extends \bigfathom\ASimpleFormPage
{
    protected $m_personid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_person_tablename = 'bigfathom_person';
    protected $m_map_person2role_tablename = 'bigfathom_map_person2role';
    protected $m_map_person2role_in_group_tablename = 'bigfathom_map_person2role_in_group';
    protected $m_map_person2systemrole_in_group_tablename = 'bigfathom_map_person2systemrole_in_group';
    
    function __construct($personid)
    {
        module_load_include('php','bigfathom_core','core/Context');
        if (!isset($personid) || !is_numeric($personid)) {
            throw new \Exception("Missing or invalid $personid value = " . $personid);
        }
        $this->m_personid = $personid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/youraccount';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\PersonPageHelper($urls_arr);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_personid);
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValidChangePassword($form, $myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            //TODO
            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                    throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            
            $shortname = $myvalues['shortname'];
            $personid = $myvalues['personid'];
            $newpassword = $myvalues['newpass1'];
            
            $drupaluid = $this->m_oUAH->getDrupalUidFromShortname($shortname);
            if($drupaluid !== $personid)
            {
                throw new \Exception("Data corruption personid#$personid is not same as drupal#$drupaluid found for shortname=$shortname");
            }
            
            $this->m_oUAH->setUserPassword($personid,$newpassword);
            
            //If we are here then we had success.
            $msg = 'Saved new password for ' . $shortname;
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to change password for ' . $myvalues['shortname']
                      . ' person because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
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
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        
        $form = $this->m_oPageHelper->getChangePasswordForm('E',$form
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
            '#value' => t('Save Change'),
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
