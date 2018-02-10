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
 * Edit Sprint
 *
 * @author Frank Font
 */
class EditSprintPage extends \bigfathom\ASimpleFormPage
{
    protected $m_sprintid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_sprint_tablename = 'bigfathom_sprint';
    protected $m_map_workitem2sprint_tablename = 'bigfathom_map_workitem2sprint';
    
    function __construct($sprintid, $projectid=NULL, $urls_arr=NULL)
    {
        if (!isset($sprintid) || !is_numeric($sprintid)) {
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
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'E');
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
            
            $start_dt = $myvalues['start_dt'];
            $end_dt = $myvalues['end_dt'];
            $sprintid = $myvalues['id'];
            
            $myfields = array(
                  'iteration_ct' => $myvalues['iteration_ct'],
                  'title_tx' => strtoupper($myvalues['title_tx']),
                  'story_tx' => $myvalues['story_tx'],
                  'start_dt' => $start_dt,
                  'end_dt' => $end_dt,
                  'owner_personid' => $myvalues['owner_personid'],
                  'membership_locked_yn' => $myvalues['membership_locked_yn'],
                  'active_yn' => $myvalues['active_yn'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            if($myvalues['ot_scf'] > '')
            {
                $myfields['ot_scf'] = $myvalues['ot_scf'];
            }
            if($myvalues['status_cd'] > '')
            {
                $myfields['status_cd'] = $myvalues['status_cd'];
                $myfields['status_set_dt'] = $updated_dt;
            }
            if($myvalues['official_score'] > '')
            {
                $myfields['official_score'] = $myvalues['official_score'];
                $myfields['score_body_tx'] = $myvalues['score_body_tx'];
            }
            
            //Update the main record
            db_update(DatabaseNamesHelper::$m_sprint_tablename)
                ->fields($myfields)
                ->condition('id', $sprintid,'=')
                ->execute(); 
            
            if(!empty($myvalues['map_workitem2sprint']) && is_array($myvalues['map_workitem2sprint']))
            {
                //Delete existing goal members
                db_delete(DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
                    ->condition('sprintid', $sprintid)
                    ->execute(); 
                //Now add goal members, if any are defined
                if(is_array($myvalues['map_workitem2sprint']))
                {
                    foreach($myvalues['map_workitem2sprint'] as $member_workitemid)
                    {
                        //Update or insert
                        db_insert(DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
                          ->fields(array(
                                'workitemid' => $member_workitemid,
                                'sprintid' => $sprintid,
                                'created_by_personid' => $myvalues['owner_personid'],
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }
            }
            
            //If we are here then we had success.
            $this->m_oWriteHelper->markProjectUpdatedForSprintID($sprintid, "updated sprint item#$sprintid");
            $fullname = $myvalues['title_tx'] . '#' . $myvalues['iteration_ct'];
            $msg = 'Saved update for ' . $fullname;
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $fullname = $myvalues['title_tx'] . '#' . $myvalues['iteration_ct'];
            $msg = t('Failed to update ' . $fullname
                      . ' because ' . $ex->getMessage());
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
        
        $form = $this->m_oPageHelper->getForm('E',$form
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
            '#value' => t('Save Sprint Updates'),
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
