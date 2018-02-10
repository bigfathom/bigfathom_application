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
 * Edit Workitem
 *
 * @author Frank Font
 */
class ViewWorkitemCommentPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_workitemid         = NULL;
    protected $m_comid              = NULL;
    protected $m_comment_type       = NULL;
    protected $m_parent_workitemid  = NULL;
    protected $m_parent_comid       = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_custom_page_key    = NULL;
    
    function __construct($comid, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
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
        $this->m_workitemid = $record['workitemid'];
        $this->m_parent_comid = $record['parent_comid'];
        if(empty($this->m_parent_comid))
        {
            $this->m_comment_type = 'THREAD_ROOT';
        } else {
            $this->m_comment_type = 'REPLY';
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
        if(!empty($urls_arr['cpk']))
        {
            $this->m_custom_page_key = $urls_arr['cpk'];
        }
        $this->m_oPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_projectid);
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
        throw new \Exception("Cannot update from view page!");
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
        
        $myvalues['workitemid'] = $this->m_workitemid;
        $myvalues['comid'] = $this->m_comid;
        $myvalues['comment_type'] = $this->m_comment_type;
        $myvalues['parent_comid'] = $this->m_parent_comid;
        $new_form = $this->m_oPageHelper->getCommentForm('V',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Exit',$returnURL, 
                        array(
                            'query' => array('workitemid' => $this->m_workitemid,'cpk'=>$this->m_custom_page_key),
                            'attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        } else {
            $cancel_button_markup = '<input type=button value="Exit" title="Leave this form" onClick="history.go(-1)">';
            $new_form['data_entry_area1']['action_buttons']['cancel'] = array('#type' => 'item'
                , '#markup' => $cancel_button_markup);
        }
        
        return $new_form;
    }
}
