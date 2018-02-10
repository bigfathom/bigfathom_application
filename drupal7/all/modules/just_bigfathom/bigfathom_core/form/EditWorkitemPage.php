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
require_once 'helper/WorkitemPageHelper.php';

/**
 * Edit Workitem details
 *
 * @author Frank Font
 */
class EditWorkitemPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid   = NULL;
    protected $m_workitemid  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    //protected $m_workitem_tablename = NULL;
    //protected $m_map_wi2wi_tablename = NULL;
    //protected $m_map_tag2workitem_tablename = NULL;
    //protected $m_map_projectrole2wi_tablename = NULL;
    //protected $m_map_delegate_workitemowner_tablename = NULL;
    
    function __construct($workitemid, $projectid=NULL, $urls_override_arr=NULL)
    {
        if (!isset($workitemid) || !is_numeric($workitemid)) {
            throw new \Exception("Missing or invalid goal_id value = " . $workitemid);
        }
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_workitemid = $workitemid;
        if($projectid != NULL)
        {
            $this->m_projectid = $projectid;
        } else {
            //Lookup the project containing the goal
            module_load_include('php','bigfathom_core','core/MapHelper');
            $oMapHelper = new \bigfathom\MapHelper();
            $goalrecord = $oMapHelper->getOneRichWorkitemRecord($this->m_workitemid);
            $this->m_projectid = $goalrecord['owner_projectid'];
        }
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\WorkitemPageHelper($urls_override_arr, NULL, $this->m_projectid);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_workitemid);
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
        try
        {
            $workitemid = $myvalues['id'];
            $resultbundle = $this->m_oPageHelper->updateWorkitem($workitemid, $myvalues);
            drupal_set_message($resultbundle['message']);
            
            $bundle = [];
            $bundle['redirect'] = $this->m_urls_arr['return'];
            if(array_key_exists('rparams', $this->m_urls_arr))
            {
                $bundle['rparams'] = $this->m_urls_arr['rparams'];
            }
            return $bundle;
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['workitem_nm'] ." because " . $ex->getMessage());
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
            '#value' => t('Save Workitem Updates'),
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
