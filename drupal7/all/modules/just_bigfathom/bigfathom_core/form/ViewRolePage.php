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
require_once 'helper/RolePageHelper.php';

/**
 * View details of a Role
 *
 * @author Frank Font
 */
class ViewRolePage extends \bigfathom\ASimpleFormPage
{
    protected $m_role_nm     = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_role_tablename = 'bigfathom_role';
    protected $m_map_goal2role_tablename = 'bigfathom_map_goal2role';
    protected $m_map_role2role_tablename = 'bigfathom_map_role2role';
    
    function __construct($role_nm, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        if (!isset($role_nm) || is_numeric($role_nm)) {
            throw new \Exception("Missing or invalid $role_nm value = " . $role_nm);
        }
        $this->m_role_nm = $role_nm;
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = 'bigfathom/sitemanage/roles'; //$pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\RolePageHelper($urls_arr);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_role_nm);
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
