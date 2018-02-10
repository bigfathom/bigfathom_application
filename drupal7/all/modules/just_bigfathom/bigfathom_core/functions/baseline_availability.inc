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

module_load_include('php','bigfathom_core','form/ManageBaselineAvailabilityPage');
module_load_include('php','bigfathom_core','form/ViewBaselineAvailabilityPage');
module_load_include('php','bigfathom_core','form/AddBaselineAvailabilityPage');
module_load_include('php','bigfathom_core','form/EditBaselineAvailabilityPage');
module_load_include('php','bigfathom_core','form/DeleteBaselineAvailabilityPage');


function bigfathom_core_mng_baseline_availability_form() 
{
    return drupal_get_form('bigfathom_core_mng_baseline_availability_form_builder');
}
function bigfathom_core_mng_baseline_availability_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageBaselineAvailabilityPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewbaseline_availability_form() 
{
    return drupal_get_form('bigfathom_core_viewbaseline_availability_form_builder');
}
function bigfathom_core_addbaseline_availability_form() 
{
    return drupal_get_form('bigfathom_core_addbaseline_availability_form_builder');
}
function bigfathom_core_editbaseline_availability_form() 
{
    return drupal_get_form('bigfathom_core_editbaseline_availability_form_builder');
}
function bigfathom_core_deletebaseline_availability_form() 
{
    return drupal_get_form('bigfathom_core_deletebaseline_availability_form_builder');
}

function bigfathom_core_addbaseline_availability_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddBaselineAvailabilityPage();
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addbaseline_availability_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddBaselineAvailabilityPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addbaseline_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddBaselineAvailabilityPage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/baseline_availability',
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

function bigfathom_core_viewbaseline_availability_form_builder($form, &$form_state)
{
    if(!isset($_GET['baseline_availabilityid']))
    {
        throw new \Exception("Missing required baseline_availabilityid url parameter!");
    }
    $baseline_availabilityid = $_GET['baseline_availabilityid'];
    if(!is_numeric($baseline_availabilityid))
    {
        throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewBaselineAvailabilityPage($baseline_availabilityid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletebaseline_availability_form_builder($form, &$form_state)
{
    if(!isset($_GET['baseline_availabilityid']))
    {
        throw new \Exception("Missing required baseline_availabilityid url parameter!");
    }
    $baseline_availabilityid = $_GET['baseline_availabilityid'];
    if(!is_numeric($baseline_availabilityid))
    {
        throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteBaselineAvailabilityPage($baseline_availabilityid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletebaseline_availability_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['baseline_availabilityid']))
    {
        throw new \Exception("Missing required baseline_availabilityid url parameter!");
    }
    $baseline_availabilityid = $_GET['baseline_availabilityid'];
    if(!is_numeric($baseline_availabilityid))
    {
        throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteBaselineAvailabilityPage($baseline_availabilityid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletebaseline_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['baseline_availabilityid']))
        {
            throw new \Exception("Missing required baseline_availabilityid url parameter!");
        }
        $baseline_availabilityid = $_GET['baseline_availabilityid'];
        if(!is_numeric($baseline_availabilityid))
        {
            throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteBaselineAvailabilityPage($baseline_availabilityid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/baseline_availability',
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
function bigfathom_core_editbaseline_availability_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['baseline_availabilityid']))
        {
            throw new \Exception("Missing required baseline_availabilityid url parameter!");
        }
        $baseline_availabilityid = $_GET['baseline_availabilityid'];
        if(!is_numeric($baseline_availabilityid))
        {
            throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditBaselineAvailabilityPage($baseline_availabilityid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editbaseline_availability_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['baseline_availabilityid']))
    {
        throw new \Exception("Missing required baseline_availabilityid url parameter!");
    }
    $baseline_availabilityid = $_GET['baseline_availabilityid'];
    if(!is_numeric($baseline_availabilityid))
    {
        throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditBaselineAvailabilityPage($baseline_availabilityid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editbaseline_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['baseline_availabilityid']))
        {
            throw new \Exception("Missing required baseline_availabilityid url parameter!");
        }
        $baseline_availabilityid = $_GET['baseline_availabilityid'];
        if(!is_numeric($baseline_availabilityid))
        {
            throw new \Exception("Invalid baseline_availabilityid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditBaselineAvailabilityPage($baseline_availabilityid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/baseline_availability',
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

