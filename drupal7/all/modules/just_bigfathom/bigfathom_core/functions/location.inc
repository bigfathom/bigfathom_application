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

module_load_include('php','bigfathom_core','form/ManageLocationPage');
module_load_include('php','bigfathom_core','form/ViewLocationPage');
module_load_include('php','bigfathom_core','form/AddLocationPage');
module_load_include('php','bigfathom_core','form/EditLocationPage');
module_load_include('php','bigfathom_core','form/DeleteLocationPage');

function bigfathom_core_mng_location_form() 
{
    return drupal_get_form('bigfathom_core_mng_location_form_builder');
}
function bigfathom_core_mng_location_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageLocationPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewlocation_form() 
{
    return drupal_get_form('bigfathom_core_viewlocation_form_builder');
}
function bigfathom_core_addlocation_form() 
{
    return drupal_get_form('bigfathom_core_addlocation_form_builder');
}
function bigfathom_core_editlocation_form() 
{
    return drupal_get_form('bigfathom_core_editlocation_form_builder');
}
function bigfathom_core_deletelocation_form() 
{
    return drupal_get_form('bigfathom_core_deletelocation_form_builder');
}

function bigfathom_core_addlocation_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddLocationPage($urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addlocation_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddLocationPage($urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addlocation_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddLocationPage($urls_arr);
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

function bigfathom_core_viewlocation_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid locationid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewLocationPage($location_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletelocation_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid locationid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteLocationPage($location_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletelocation_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid location_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteLocationPage($location_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletelocation_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid location_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteLocationPage($location_id,NULL,$urls_arr);
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
function bigfathom_core_editlocation_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid locationid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditLocationPage($location_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editlocation_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid locationid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditLocationPage($location_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_editlocation_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['locationid']))
        {
            throw new \Exception("Missing required locationid url parameter!");
        }
        $location_id = $_GET['locationid'];
        if(!is_numeric($location_id))
        {
            throw new \Exception("Invalid locationid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditLocationPage($location_id,NULL,$urls_arr);
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
