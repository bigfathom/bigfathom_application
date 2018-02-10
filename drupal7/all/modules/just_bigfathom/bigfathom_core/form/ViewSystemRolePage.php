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
require_once 'helper/SystemRolePageHelper.php';

/**
 * View details of a SystemRole
 *
 * @author Frank Font
 */
class ViewSystemRolePage extends \bigfathom\ASimpleFormPage
{
    protected $m_systemrole_id     = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_systemrole_tablename = 'bigfathom_systemrole';
    protected $m_map_goal2role_tablename = 'bigfathom_map_goal2role';
    protected $m_map_systemrole2role_tablename = 'bigfathom_map_systemrole2role';
    
    function __construct($systemrole_id)
    {
        if (!isset($systemrole_id) || !is_numeric($systemrole_id)) {
            throw new \Exception("Missing or invalid systemrole_id value = " . $systemrole_id);
        }
        $this->m_systemrole_id = $systemrole_id;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/sysroles';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\SystemRolePageHelper($urls_arr);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_systemrole_id);
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
