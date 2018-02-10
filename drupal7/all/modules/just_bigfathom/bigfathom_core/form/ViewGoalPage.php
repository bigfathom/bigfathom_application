<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 */


namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'GoalPageHelper.php';

/**
 * View details of a Goal
 *
 * @author Frank Font
 */
class ViewGoalPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid   = NULL;
    protected $m_goal_id        = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($goal_id, $projectid=NULL, $urls_arr=NULL)
    {
        if (!isset($goal_id) || !is_numeric($goal_id)) {
            throw new \Exception("Missing or invalid goal_id value = " . $goal_id);
        }
        $this->m_goal_id = $goal_id;
        if($projectid != NULL)
        {
            $this->m_projectid = $projectid;
        } else {
            //Lookup the project containing the goal
            $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
            $oMapHelper = new \bigfathom\MapHelper();
            //$goalrecord = $oMapHelper->getGoalRecord($this->m_goal_id);
            $goalrecord = $oMapHelper->getOneRichWorkitemRecord($this->m_goal_id);
            $this->m_projectid = $goalrecord['owner_projectid'];
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
        return $this->m_oPageHelper->getFieldValues($this->m_goal_id);
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
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('V',$base_form
                , $form_state
                , $disabled
                , $myvalues
                , $html_classname_overrides);
        
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Exit',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        return $form;
    }
}
