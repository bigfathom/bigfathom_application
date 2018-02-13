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
 * Add a Test case Comment
 *
 * @author Frank Font
 */
class AddTestcaseCommentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_testcaseid         = NULL;
    protected $m_default_stepnum    = NULL;
    protected $m_comid              = NULL;
    protected $m_comment_type       = NULL;
    protected $m_parent_testcaseid  = NULL;
    protected $m_parent_comid       = NULL;
    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_custom_page_key    = NULL;
    
    protected $m_in_dialog = NULL;
    
    public function __construct($parentkey, $urls_arr=NULL, $in_dialog=FALSE)
    {
        $this->m_in_dialog = $in_dialog;

        if($parentkey == NULL || !is_array($parentkey))
        {
            throw new \Exception("Cannot add a testcase comment without specifying a parent key!");
        }
        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }

        $this->m_default_stepnum = isset($urls_arr['default_stepnum']) ? $urls_arr['default_stepnum'] : NULL;
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        if(array_key_exists('parent_comid', $parentkey))
        {
            $this->m_parent_comid = $parentkey['parent_comid'];
            $record = $this->m_oMapHelper->getTestcaseCommunicationContext($this->m_parent_comid);
            $this->m_projectid = $record['projectid'];
            $this->m_testcaseid = $record['testcaseid'];
            $this->m_comment_type = 'REPLY';
        } else
        if(array_key_exists('testcaseid', $parentkey))
        {
            $this->m_comid = NULL;
            $this->m_testcaseid = $parentkey['testcaseid'];
            $this->m_projectid = $this->m_oMapHelper->getProjectIDForTestcase($this->m_testcaseid);
            $this->m_parent_comid = NULL;
            $this->m_comment_type = 'THREAD_ROOT';
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
        $this->m_oPageHelper = new \bigfathom\TestCasePageHelper($urls_arr, NULL, $this->m_projectid);
    }
    
    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $values = $this->m_oPageHelper->getCommentFieldValues(NULL,$this->m_parent_comid,$this->m_testcaseid,$this->m_default_stepnum);
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
            $resultbundle = $this->m_oPageHelper->createTestcaseComment($myvalues);
            drupal_set_message($resultbundle['message']);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add testcase comment because ' . $ex->getMessage());
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
        $myvalues['parent_page'] = $this->m_parent_page;
        $myvalues['testcaseid'] = $this->m_testcaseid;
        $myvalues['default_stepnum'] = $this->m_default_stepnum;
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
                , '#attributes' => array('title'=>'Save the form', 'class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Save Test Case Communication'));
 
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL, 
                        array(
                            'query' => array('testcaseid' => $this->m_testcaseid,'cpk'=>$this->m_custom_page_key),
                            'attributes'=>array('class'=>$html_classname_overrides['action-button'])));
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
