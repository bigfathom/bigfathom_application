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

module_load_include('php','bigfathom_core','form/ManageProjectContextPage');
module_load_include('php','bigfathom_core','form/ViewProjectContextPage');
module_load_include('php','bigfathom_core','form/AddProjectContextPage');
module_load_include('php','bigfathom_core','form/EditProjectContextPage');
module_load_include('php','bigfathom_core','form/DeleteProjectContextPage');

function bigfathom_core_mng_project_context_form() 
{
    return drupal_get_form('bigfathom_core_mng_project_context_form_builder');
}
function bigfathom_core_mng_project_context_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageProjectContextPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewproject_context_form() 
{
    return drupal_get_form('bigfathom_core_viewproject_context_form_builder');
}
function bigfathom_core_addproject_context_form() 
{
    return drupal_get_form('bigfathom_core_addproject_context_form_builder');
}
function bigfathom_core_editproject_context_form() 
{
    return drupal_get_form('bigfathom_core_editproject_context_form_builder');
}
function bigfathom_core_deleteproject_context_form() 
{
    return drupal_get_form('bigfathom_core_deleteproject_context_form_builder');
}

function bigfathom_core_addproject_context_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        
        $oPI = new \bigfathom\AddProjectContextPage($urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addproject_context_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        
        $oPI = new \bigfathom\AddProjectContextPage($urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addproject_context_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        
        $oPI = new \bigfathom\AddProjectContextPage($urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'added',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewproject_context_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_contextid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewProjectContextPage($project_context_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteproject_context_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_contextid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteProjectContextPage($project_context_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_deleteproject_context_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_context_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteProjectContextPage($project_context_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_deleteproject_context_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_context_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteProjectContextPage($project_context_id,NULL,$urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'deleted',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_editproject_context_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_contextid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditProjectContextPage($project_context_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editproject_context_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_contextid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditProjectContextPage($project_context_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_editproject_context_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['project_contextid']))
        {
            throw new \Exception("Missing required project_contextid url parameter!");
        }
        $project_context_id = $_GET['project_contextid'];
        if(!is_numeric($project_context_id))
        {
            throw new \Exception("Invalid project_contextid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditProjectContextPage($project_context_id,NULL,$urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'edited',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}
