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
require_once 'helper/URIDomainPageHelper.php';

/**
 * Add a new uridomain item
 *
 * @author Frank Font
 */
class AddURIDomainPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_list_type = NULL;
   
    public function __construct($urls_arr_override=NULL, $list_type='WHITE')
    {
        
        if(empty($urls_arr_override))
        {
            $urls_arr_override = [];
        }
        $urls_arr = [];
        $urls_arr['return'] = 'bigfathom/sitemanage/uridomain';
        foreach($urls_arr_override as $k=>$v)
        {
            $urls_arr[$k] = $v;
        }
        
        if(empty($list_type))
        {
            throw new \Exception("Missing reqwuired list type!");
        }
        $this->m_list_type = $list_type;
        
        module_load_include('php','bigfathom_core','core/Context');
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        
        
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\URIDomainPageHelper($urls_arr);
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemadmin)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to add a uridomain!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues();
        return $myvalues;
    }
    
    function looksValid($form, $myvalues)
    {
        $myvalues['list_type'] = $this->m_list_type;
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $myvalues['list_type'] = $this->m_list_type;
            $result_bundle = $this->m_oWriteHelper->createURIDomainItem($myvalues);
            $msg = $result_bundle['message'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to create ' . $myvalues['remote_uri_domain']
                      . ' uridomain because ' . $ex->getMessage());
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
            $myvalues = [];
        } else {
            $myvalues = $form_state['values'];
        }
        $html_classname_overrides = array();
        $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        $html_classname_overrides['container-inline'] = 'container-inline';
        $html_classname_overrides['action-button'] = 'action-button';
        $new_form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $new_form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Add This Domain'));
 
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $new_form;
    }
}
