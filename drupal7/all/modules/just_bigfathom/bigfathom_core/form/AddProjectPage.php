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
require_once 'helper/ProjectPageHelper.php';

/**
 * Add a Project
 *
 * @author Frank Font
 */
class AddProjectPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oWriteHelper = NULL;
    protected $m_parent_projectid = NULL;
   
    public function __construct($urls_override_arr=NULL,$parent_projectid=NULL,$root_goalid=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $parent_projectid;
        $this->m_root_goalid = $root_goalid;
        
        $this->m_oPageHelper = new \bigfathom\ProjectPageHelper($urls_override_arr,NULL,$parent_projectid,NULL,$root_goalid);
        
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
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
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues();
    }
    
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $resultbundle = $this->m_oWriteHelper->createNewProject($myvalues);
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
            throw $ex;
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
        if(!empty($this->m_parent_projectid) && (!isset($myvalues['parent_projectid']) || $myvalues['parent_projectid'] == NULL))
        {
            $myvalues['parent_projectid'] = $this->m_parent_projectid;
        }
        if(!empty($this->m_root_goalid) && (!isset($myvalues['root_goalid']) || $myvalues['root_goalid'] == NULL))
        {
            $myvalues['root_goalid'] = $this->m_root_goalid;
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
                , '#value' => t('Save New Project'));
 
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
