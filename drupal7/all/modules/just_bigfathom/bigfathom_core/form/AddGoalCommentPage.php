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
 * Add a Goal Comment
 *
 * @author Frank Font
 * @deprecated
 */
class AddGoalCommentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_goalid             = NULL;
    protected $m_comid          = NULL;
    protected $m_comment_type       = NULL;
    protected $m_parent_goalid      = NULL;
    protected $m_parent_comid   = NULL;
    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_workitem_tablename = 'bigfathom_workitem';
    protected $m_map_wi2wi_tablename = 'bigfathom_map_wi2wi';
    protected $m_map_tag2workitem_tablename = 'bigfathom_map_wi2wi';
    protected $m_map_projectrole2wi_tablename = 'bigfathom_map_role2wi';
    
    public function __construct($parentkey, $urls_arr=NULL)
    {
        if($parentkey == NULL || !is_array($parentkey))
        {
            throw new \Exception("Cannot add a goal without specifying a parent key!");
        }
        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        if(array_key_exists('parent_comid', $parentkey))
        {
            $this->m_parent_comid = $parentkey['parent_comid'];
            $record = $this->m_oMapHelper->getWorkitemCommunicationContext($this->m_parent_comid);
            $this->m_projectid = $record['projectid'];
            $this->m_goalid = $record['goalid'];
            $this->m_comment_type = 'REPLY';
        } else
        if(array_key_exists('goalid', $parentkey))
        {
            $this->m_comid = NULL;
            $this->m_goalid = $parentkey['goalid'];
            $this->m_projectid = $this->m_oMapHelper->getProjectIDForWorkitem($this->m_goalid);
            $this->m_parent_comid = NULL;
            $this->m_comment_type = 'THREAD_ROOT';
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
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $values = $this->m_oPageHelper->getCommentFieldValues(NULL,$this->m_parent_comid,$this->m_goalid);
        return $values;
    }
    
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValidComment($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $resultbundle = $this->m_oPageHelper->createWorkitemCommunication($myvalues);
            drupal_set_message($resultbundle['message']);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add goal comment because ' . $ex->getMessage());
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
        $myvalues['goalid'] = $this->m_goalid;
        $myvalues['comid'] = $this->m_comid;
        $myvalues['comment_type'] = $this->m_comment_type;
        $myvalues['parent_comid'] = $this->m_parent_comid;
        $html_classname_overrides = array();
        $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        $html_classname_overrides['container-inline'] = 'container-inline';
        $html_classname_overrides['action-button'] = 'action-button';
        
        $new_form = $this->m_oPageHelper->getCommentForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $new_form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Add Goal Communication'));
 
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
