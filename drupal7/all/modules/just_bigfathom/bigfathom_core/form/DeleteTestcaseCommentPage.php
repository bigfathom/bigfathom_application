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
require_once 'helper/TestCasePageHelper.php';

/**
 * Delete Test case Communication
 *
 * @author Frank Font
 */
class DeleteTestcaseCommentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_testcaseid           = NULL;
    protected $m_comid              = NULL;
    protected $m_comment_type       = NULL;
    protected $m_parent_testcaseid    = NULL;
    protected $m_parent_comid       = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_custom_page_key    = NULL;
    
    //protected $m_testcase_tablename     = 'bigfathom_testcase';
    //protected $m_map_tag2testcase_tablename = 'bigfathom_map_tag2testcase';
    //protected $m_map_role2testcase_tablename = 'bigfathom_map_role2testcase';
    
    function __construct($comid, $urls_arr=NULL, $in_dialog)
    {
        $this->m_in_dialog = $in_dialog;
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
        $record = $this->m_oMapHelper->getTestcaseCommunicationContext($this->m_comid);
        $this->m_projectid = $record['projectid'];
        $this->m_testcaseid = $record['testcaseid'];
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
        
        $this->m_parent_page = 'bigfathom/testcase/mng_comments';
        if(!empty($urls_arr['parent_page']))
        {
            $urls_arr['parent_page'] = 'bigfathom/testcase/mng_comments';
        }
        if($this->m_in_dialog)
        {
            $this->m_parent_page .= "_indialog";
            foreach($urls_arr as $k=>$v)
            {
                $urls_arr[$k] = "{$v}_indialog";
            }
        }
        if($this->m_in_dialog)
        {
            unset($urls_arr['return']);
        }
        $this->m_urls_arr = $urls_arr;
        if(!empty($urls_arr['cpk']))
        {
            $this->m_custom_page_key = $urls_arr['cpk'];
        }
        $this->m_oPageHelper = new \bigfathom\TestcasePageHelper($urls_arr, NULL, $this->m_projectid);
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
            $resultbundle = $this->m_oPageHelper->deleteTestcaseComment($matchcomid,$this_uid);
            drupal_set_message($resultbundle['message']);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['testcase_nm']
                      . " testcase at step $step because " . $ex->getMessage());
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
        
        $myvalues['parent_page'] = $this->m_parent_page;
        $myvalues['testcaseid'] = $this->m_testcaseid;
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
        $buttontext = 'Delete Test Case Communication from System';
        $new_form['data_entry_area1']['action_buttons']['delete'] = array('#type' => 'submit'
                , '#attributes' => array('title'=>'Click to erase the data', 'class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
                );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL, 
                        array(
                            'query' => array('testcaseid' => $this->m_testcaseid,'cpk'=>$this->m_custom_page_key),
                            'attributes'=>array('title'=>'Leave this form without saving', 'class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        } else {
            $cancel_button_markup = '<input type=button value="Cancel" title="Leave this form without saving" onClick="history.go(-1)">';
            $new_form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => $cancel_button_markup);
        }
        
        return $new_form;
    }
}
