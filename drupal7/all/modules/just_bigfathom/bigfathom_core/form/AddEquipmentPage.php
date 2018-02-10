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
require_once 'helper/EquipmentPageHelper.php';

/**
 * Add a new equipment item
 *
 * @author Frank Font
 */
class AddEquipmentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
   
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/equipment';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\EquipmentPageHelper($urls_arr);
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to add a equipment resource!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues();
    }
    
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $main_qry = db_insert(DatabaseNamesHelper::$m_equipment_tablename)->fields(array(
                  'shortname' => $myvalues['shortname'],
                  'name' => $myvalues['name'],
                  'description_tx' => $myvalues['description_tx'],
                  'primary_locationid' => $myvalues['primary_locationid'],
                  'condition_cd' => $myvalues['condition_cd'],
                  'condition_set_dt' => $updated_dt,
                  'active_yn' => $myvalues['active_yn'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              ));
            $newid = $main_qry->execute(); 
            //If we are here then we had success.
            $msg = 'Added non-human resource ' . $myvalues['name'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add ' . $myvalues['name']
                      . ' non-human resource because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues_override)
    {
        if(!isset($form_state['values']))
        {
            $myvalues = array();
        } else {
            $myvalues = $form_state['values'];
        }
        $html_classname_overrides = array();
        $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        $html_classname_overrides['container-inline'] = 'container-inline';
        $html_classname_overrides['action-button'] = 'action-button';
        $new_form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $new_form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Add This Non-Human Resource'));
 
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $new_form;
    }
}
