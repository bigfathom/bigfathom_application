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
require_once 'helper/BaselineAvailabilityPageHelper.php';

/**
 * Delete one person availability
 *
 * @author Frank Font
 */
class DeleteBaselineAvailabilityPage extends \bigfathom\ASimpleFormPage
{
    protected $m_baseline_availabilityid     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    
    function __construct($baseline_availabilityid)
    {
        if (!isset($baseline_availabilityid) || !is_numeric($baseline_availabilityid)) {
            throw new \Exception("Missing or invalid baseline_availabilityid value = " . $baseline_availabilityid);
        }
        $this->m_baseline_availabilityid = $baseline_availabilityid;
        
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
       
        
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/baseline_availability';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\BaselineAvailabilityPageHelper($urls_arr);
        
        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee && !$this->m_is_systemadmin)
        {
            error_log("HACKING WARNING: uid#{$user->uid} attempted to delete baseline_availability#$baseline_availabilityid!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_baseline_availabilityid);
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
            $baseline_availabilityid = $this->m_baseline_availabilityid;
            db_delete(DatabaseNamesHelper::$m_baseline_availability_tablename)
              ->condition('id', $baseline_availabilityid)
              ->execute(); 

            //If we are here then we had success.
            $msg = 'Deleted baseline availabilty "' 
                    . $myvalues['shortname'] 
                    . '" with ' 
                    . $myvalues['hours_per_day']
                    . ' hours/day';
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to delete baseline availabilty "' 
                    . $myvalues['shortname'] 
                    . '" with ' 
                    . $myvalues['hours_per_day']
                    . ' hours/day because ' . $ex->getMessage());
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
        $buttontext = 'Delete this Baseline Availability Entry from the System';
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
