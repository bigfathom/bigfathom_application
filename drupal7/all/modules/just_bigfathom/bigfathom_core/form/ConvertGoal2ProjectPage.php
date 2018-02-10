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
require_once 'helper/ConversionPageHelper.php';

/**
 * Convert a goal into the root of a new project; and bring its wholly owned ants along.
 * By default, the project is saved as an antecedent of the goal's owner project.
 * 
 * @author Frank Font
 */
class ConvertGoal2ProjectPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oWriteHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_dependent_projectid = NULL;
   
    public function __construct($urls_override_arr=NULL,$dependent_projectid=NULL, $root_goalid=NULL)
    {
        if(empty($root_goalid))
        {
            throw new Exception("Missing required root_goalid parameter!");
        }
        
        $this->m_oContext = \bigfathom\Context::getInstance();
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_root_goalid = $root_goalid;
        if(!empty($dependent_projectid))
        {
            $this->m_dependent_projectid = $dependent_projectid;
        } else {
            $wrec = $this->m_oMapHelper->getOneBareWorkitemRecord($root_goalid);
            $this->m_dependent_projectid = $wrec['owner_projectid'];
        }
        
        $this->m_oPageHelper = new \bigfathom\ConversionPageHelper($urls_override_arr,NULL,$this->m_dependent_projectid,NULL,$root_goalid);
        
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
            if(empty($myvalues['root_goalid']))
            {
                throw new \Exception("Missing root_goalid!");
            }
            $workitemid = $myvalues['root_goalid'];
            $result_bundle = $this->m_oWriteHelper->createProjectFromGoal($workitemid, $myvalues);
            $new_projectid = $result_bundle['projectid'];
            if(!empty($this->m_dependent_projectid))
            {
                $this->m_oWriteHelper->declareProjectAsSubproject($this->m_dependent_projectid, $new_projectid);
            }
            //If we are here then we had success.
            $msg = "Created project#$new_projectid from workitem#$workitemid";
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $workitemid = $myvalues['root_goalid'];
            $msg = t("Failed to create project from workitem#{$workitemid}"
                      . ' project because ' . $ex->getMessage());
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
        $new_form = $this->m_oPageHelper->getGoal2ProjectForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $new_form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Convert this Goal into a Project'));
 
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
