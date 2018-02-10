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
require_once 'helper/BrainstormItemsPageHelper.php';

/**
 * Add a BrainstormItem
 *
 * @author Frank Font
 */
class AddBrainstormItemPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid      = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_oWriteHelper   = NULL;
    //protected $m_brainstorm_item_tablename = 'bigfathom_brainstorm_item';
            
    public function __construct($projectid, $urls_arr=NULL)
    {
        if($projectid == NULL)
        {
            throw new \Exception("Cannot add a brainstormitem without specifying a project!");
        }
        $this->m_projectid = $projectid;
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oPageHelper = new \bigfathom\BrainstormItemsPageHelper($urls_arr,NULL,$projectid);
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
            $projectid = $myvalues['projectid'];
            $fields = array(
                  'projectid' => $projectid,
                  'item_nm' => $myvalues['item_nm'],
                  'context_nm' => $myvalues['context_nm'],
                  'candidate_type' => $myvalues['candidate_type'],
                  'importance' => $myvalues['importance'],
                  'active_yn' => $myvalues['active_yn'],
                  'parkinglot_level' => $myvalues['parkinglot_level'],
                  'purpose_tx' => $myvalues['purpose_tx'],
                  'owner_personid' => $myvalues['owner_personid'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->fields($fields);
            $newid = $main_qry->execute(); 
            
            //If we are here then we had success.
            $this->m_oWriteHelper->markProjectUpdatedForBraintormTopic($newid, "created brainstorm item#$newid");
            //$this->m_oWriteHelper->markProjectUpdated($projectid, "created brainstorm item#$newid");
            $msg = 'Added ' . $myvalues['item_nm'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add ' . $myvalues['item_nm'] . " because " . $ex->getMessage());
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
                , '#value' => t('Add Candidate Topic'));
 
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
