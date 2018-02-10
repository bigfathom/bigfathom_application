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
require_once 'GoalPageHelper.php';

/**
 * Delete Goal Communication
 *
 * @author Frank Font
 */
class DeleteGoalCommentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_goalid             = NULL;
    protected $m_comid          = NULL;
    protected $m_comment_type       = NULL;
    protected $m_parent_goalid      = NULL;
    protected $m_parent_comid   = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    //protected $m_workitem_tablename     = 'bigfathom_workitem';
    //protected $m_map_wi2wi_tablename = 'bigfathom_map_wi2wi';
    //protected $m_map_tag2workitem_tablename = 'bigfathom_map_tag2workitem';
    //protected $m_map_projectrole2wi_tablename = 'bigfathom_map_role2wi';
    
    function __construct($comid, $urls_arr=NULL)
    {
        if (!isset($comid) || !is_numeric($comid)) {
            throw new \Exception("Missing or invalid comid value = " . $comid);
        }
        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_comid = $comid;
        $record = $this->m_oMapHelper->getWorkitemCommunicationContext($this->m_comid);
        $this->m_projectid = $record['projectid'];
        $this->m_goalid = $record['goalid'];
        $this->m_parent_comid = $record['parent_comid'];
        if(empty($this->m_parent_comid))
        {
            $this->m_comment_type = 'THREAD_ROOT';
        } else {
            $this->m_comment_type = 'REPLY';
        }
        if($urls_arr == NULL)
        {
            $urls_arr = array();
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\GoalPageHelper($urls_arr, NULL, $this->m_projectid);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getCommentFieldValues($this->m_comid);
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
        $step=0;
        try
        {
            global $user;
            $this_uid = $user->uid;
            $matchcomid = $myvalues['id'];
            $resultbundle = $this->m_oPageHelper->deleteWorkitemCommunication($matchcomid,$this_uid);
            drupal_set_message($resultbundle['message']);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['workitem_nm']
                      . " goal at step $step because " . $ex->getMessage());
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
        
        $myvalues['goalid'] = $this->m_goalid;
        $myvalues['comid'] = $this->m_comid;
        $myvalues['comment_type'] = $this->m_comment_type;
        $myvalues['parent_comid'] = $this->m_parent_comid;
        $new_form = $this->m_oPageHelper->getCommentForm('D',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $buttontext = 'Delete Goal Communication From System';
        $new_form['data_entry_area1']['action_buttons']['delete'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
                );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL, 
                        array(
                            'query' => array('goalid' => $this->m_goalid),
                            'attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $new_form;
    }
}
