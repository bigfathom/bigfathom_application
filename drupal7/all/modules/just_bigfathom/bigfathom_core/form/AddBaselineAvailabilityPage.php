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
 * Add a Person Availability
 *
 * @author Frank Font
 */
class AddBaselineAvailabilityPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_personid = NULL;
   
    public function __construct($urls_override_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = array();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = 'bigfathom/sitemanage/baseline_availability';
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\BaselineAvailabilityPageHelper($urls_arr,NULL);
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        global $user;
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemadmin && !$this->m_is_systemdatatrustee)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to add a baseline availabilty!!!");
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
        $transaction = db_transaction();
        try
        {
            global $user;
            if($myvalues['is_planning_default_yn'] == 1)
            {
                //Remove the setting from the other record
                db_update(DatabaseNamesHelper::$m_baseline_availability_tablename)
                    ->fields(array(
                      'is_planning_default_yn' => 0,
                    ))
                        ->execute();                
            }
            $fields = array(
                  'is_planning_default_yn' => $myvalues['is_planning_default_yn'],
                  'shortname' => $myvalues['shortname'],
                  'hours_per_day' => $myvalues['hours_per_day'],
                  'work_saturday_yn' => $myvalues['work_saturday_yn'],
                  'work_sunday_yn' => $myvalues['work_sunday_yn'],
                  'work_monday_yn' => $myvalues['work_monday_yn'],
                  'work_tuesday_yn' => $myvalues['work_tuesday_yn'],
                  'work_wednesday_yn' => $myvalues['work_wednesday_yn'],
                  'work_thursday_yn' => $myvalues['work_thursday_yn'],
                  'work_friday_yn' => $myvalues['work_friday_yn'],
                  'work_holidays_yn' => $myvalues['work_holidays_yn'],
                  'comment_tx' => $myvalues['comment_tx'],
                  'updated_by_personid' => $user->uid,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_baseline_availability_tablename)->fields($fields);
            $newid = $main_qry->execute();
            
            //If we are here then we had success.
            $msg = 'Added baseline availability "' 
                    . $myvalues['shortname'] 
                    . '" with ' 
                    . $myvalues['hours_per_day'] 
                    . ' hours/day';
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add "' 
                    . $myvalues['shortname'] 
                    . '" baseline period because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            $transaction->rollback();
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
                , '#value' => t('Add Baseline Availability Period Declaration'));
 
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
