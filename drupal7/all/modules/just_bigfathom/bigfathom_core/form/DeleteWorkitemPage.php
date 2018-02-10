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
 * Delete one Workitem
 *
 * @author Frank Font
 */
class DeleteWorkitemPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid   = NULL;
    protected $m_workitemid        = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_workitem_tablename = 'bigfathom_workitem';
    protected $m_map_wi2wi_tablename = 'bigfathom_map_wi2wi';
    
    function __construct($workitemid, $projectid=NULL, $urls_override_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        if (!isset($workitemid) || !is_numeric($workitemid)) {
            throw new \Exception("Missing or invalid workitemid value = " . $workitemid);
        }
        module_load_include('php','bigfathom_core','core/WriteHelper');
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        $this->m_workitemid = $workitemid;
        if($projectid != NULL)
        {
            $this->m_projectid = $projectid;
        } else {
            //Lookup the project containing the workitem
            module_load_include('php','bigfathom_core','core/MapHelper');
            $oMapHelper = new \bigfathom\MapHelper();
            //$workitemrecord = $oMapHelper->getWorkitemRecord($this->m_workitemid);
            $workitemrecord = $oMapHelper->getOneRichWorkitemRecord($this->m_workitemid);
            $this->m_projectid = $workitemrecord['owner_projectid'];
        }
        $urls_arr = [];
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
        $this->m_oPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_projectid);
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
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'D');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $workitemid = $myvalues['id'];
            $resultbundle = $this->m_oPageHelper->deleteWorkitem($workitemid);
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
            $msg = t('Failed to delete ' . $myvalues['workitem_nm']
                      . ' workitem because ' . $ex->getMessage());
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

        if(empty($this->m_urls_arr['rparams']))
        {
            $rparams_ar = [];
        } else {
            $rparams_ar = unserialize(urldecode($this->m_urls_arr['rparams']));
        }
        
        $disabled = TRUE;
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        $buttontext = 'Delete Workitem From System';
        $form["data_entry_area1"]['delete'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
                );

        if(isset($this->m_urls_arr['return']))
        {
            $exit_link_markup = l('Cancel',$this->m_urls_arr['return']
                            , array('query' => $rparams_ar, 'attributes'=>array('class'=>'action-button'))
                    );
            $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $exit_link_markup);
        }

        return $form;
    }
}
