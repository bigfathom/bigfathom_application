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
 *
 * All the Drupal functions for one data context are in this file
 */

module_load_include('php','bigfathom_core','form/ManageExternalResourcePage');
module_load_include('php','bigfathom_core','form/ViewExternalResourcePage');
module_load_include('php','bigfathom_core','form/AddExternalResourcePage');
module_load_include('php','bigfathom_core','form/EditExternalResourcePage');
module_load_include('php','bigfathom_core','form/DeleteExternalResourcePage');

function bigfathom_core_mng_external_resource_form() 
{
    return drupal_get_form('bigfathom_core_mng_external_resource_form_builder');
}
function bigfathom_core_mng_external_resource_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageExternalResourcePage();
        $form = [];
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewexternal_resource_form() 
{
    return drupal_get_form('bigfathom_core_viewexternal_resource_form_builder');
}
function bigfathom_core_addexternal_resource_form() 
{
    return drupal_get_form('bigfathom_core_addexternal_resource_form_builder');
}
function bigfathom_core_editexternal_resource_form() 
{
    return drupal_get_form('bigfathom_core_editexternal_resource_form_builder');
}
function bigfathom_core_deleteexternal_resource_form() 
{
    return drupal_get_form('bigfathom_core_deleteexternal_resource_form_builder');
}

function bigfathom_core_addexternal_resource_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddExternalResourcePage($urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addexternal_resource_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddExternalResourcePage($urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addexternal_resource_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddExternalResourcePage($urls_arr);
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

function bigfathom_core_viewexternal_resource_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewExternalResourcePage($externalresourceid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteexternal_resource_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteExternalResourcePage($externalresourceid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteexternal_resource_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteExternalResourcePage($externalresourceid,NULL,$urls_arr);
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
function bigfathom_core_editexternal_resource_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditExternalResourcePage($externalresourceid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editexternal_resource_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditExternalResourcePage($externalresourceid,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_editexternal_resource_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['externalresourceid']))
        {
            throw new \Exception("Missing required externalresourceid url parameter!");
        }
        $externalresourceid = $_GET['externalresourceid'];
        if(!is_numeric($externalresourceid))
        {
            throw new \Exception("Invalid externalresourceid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditExternalResourcePage($externalresourceid,NULL,$urls_arr);
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
