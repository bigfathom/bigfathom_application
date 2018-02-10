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
require_once 'helper/PersonAvailabilityPageHelper.php';

/**
 * Delete one person availability
 *
 * @author Frank Font
 */
class DeletePersonAvailabilityPage extends \bigfathom\ASimpleFormPage
{
    protected $m_person_availabilityid     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    
    function __construct($person_availabilityid, $urls_override_arr=NULL)
    {
        if (!isset($person_availabilityid) || !is_numeric($person_availabilityid)) {
            throw new \Exception("Missing or invalid person_availabilityid value = " . $person_availabilityid);
        }
        $this->m_person_availabilityid = $person_availabilityid;
        
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_personid = $this->m_oMapHelper->getPersonIDForPersonAvailabilityID($person_availabilityid);        
        
        $urls_arr = [];
        $urls_arr['return'] = 'bigfathom/topinfo/youraccount';
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\PersonAvailabilityPageHelper($urls_arr,NULL,$this->m_personid);
        
        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($user->uid != $this->m_personid && !$this->m_is_systemdatatrustee)
        {
            error_log("HACKING WARNING: uid#{$user->uid} attempted to delete person_availability#$person_availabilityid!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_person_availabilityid);
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
            $person_availabilityid = $this->m_person_availabilityid;
            db_delete(DatabaseNamesHelper::$m_map_person2availability_tablename)
              ->condition('id', $person_availabilityid)
              ->execute(); 

            if(isset($myvalues['hours_per_day']))
            {
                $hpdb = ' with ' 
                        . $myvalues['hours_per_day'] 
                        . ' hours/day';
            } else {
                $hpdb = " ";
            }
            
            //If we are here then we had success.
            $msg = "Deleted custom availabilty{$hpdb}"
                    . 'for period ' 
                    . $myvalues['start_dt'] 
                    . " to " 
                    . $myvalues['end_dt'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to delete ' . $myvalues['start_dt'] . " to " . $myvalues['end_dt']
                      . ' period because ' . $ex->getMessage());
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
        $buttontext = 'Delete this Personal Availability Entry from the System';
        $form['data_entry_area1']['action_buttons']['delete'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
                );

        if(isset($this->m_urls_arr['return']))
        {
            $base_url = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$base_url
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])
                        ,'query'=>array('personid'=>$this->m_personid)
                        )
                    );
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }

        return $form;
    }
}
