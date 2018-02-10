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
 * All the Drupal functions for one data context are in this file
 */


namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/SprintPageHelper.php';

/**
 * View details of a Sprint
 *
 * @author Frank Font
 */
class ViewSprintPage extends \bigfathom\ASimpleFormPage
{
    protected $m_sprintid     = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_sprint_tablename = 'bigfathom_sprint';
    protected $m_map_workitem2sprint_tablename = 'bigfathom_map_workitem2sprint';
    
    function __construct($sprintid, $projectid=NULL, $urls_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        if (!isset($sprintid) || !is_numeric($sprintid)) {
            throw new \Exception("Missing or invalid $sprintid value = " . $sprintid);
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
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_sprintid);
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
