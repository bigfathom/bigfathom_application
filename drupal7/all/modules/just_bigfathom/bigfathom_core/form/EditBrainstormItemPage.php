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
 * Edit brainstorm item
 *
 * @author Frank Font
 */
class EditBrainstormItemPage extends \bigfathom\ASimpleFormPage
{
    protected $m_brainstormitemid   = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_oWriteHelper       = NULL;
    
    function __construct($brainstormitemid, $projectid=NULL, $urls_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        if (!isset($brainstormitemid) || !is_numeric($brainstormitemid)) 
        {
            throw new \Exception("Missing or invalid brainstormitemid value = " . $brainstormitemid);
        }
        $this->m_brainstormitemid = $brainstormitemid;
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        if($urls_arr == NULL)
        {
            $urls_arr = array();
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\BrainstormItemsPageHelper($urls_arr);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_brainstormitemid);
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
            $projectid = $myvalues['projectid'];
            $brainstormitemid = $myvalues['id'];
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
                );
            
            if( $myvalues['parkinglot_level'] < 1)
            {
                $fields['into_parkinglot_dt'] = NULL;
            } else {
                if(empty($myvalues['into_parkinglot_dt']))
                {
                    $fields['into_parkinglot_dt'] = $updated_dt;
                }
            }
            if($myvalues['active_yn'] == 1)
            {
                $fields['into_trash_dt'] = NULL;
            } else {
                $fields['into_trash_dt'] = $updated_dt;
            }
            db_update(DatabaseNamesHelper::$m_brainstorm_item_tablename)->fields($fields)
                    ->condition('id', $brainstormitemid,'=')
                    ->execute(); 
            //If we are here then we had success.
            $this->m_oWriteHelper->markProjectUpdatedForBraintormTopic($brainstormitemid, "updated brainstorm item#$brainstormitemid");
            //$this->m_oWriteHelper->markProjectUpdated($projectid, "updated brainstorm item#$brainstormitemid");
            $msg = 'Saved update for ' . $myvalues['item_nm'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['brainstormitemid']
                      . ' braintorm item because ' . $ex->getMessage());
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
            '#value' => t('Save Candidate Topic Updates'),
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
