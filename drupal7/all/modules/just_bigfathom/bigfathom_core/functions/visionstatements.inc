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

module_load_include('php','bigfathom_core','form/ManageVisionStatementsPage');
module_load_include('php','bigfathom_core','form/ViewVisionStatementPage');
module_load_include('php','bigfathom_core','form/AddVisionStatementPage');
module_load_include('php','bigfathom_core','form/EditVisionStatementPage');
module_load_include('php','bigfathom_core','form/DeleteVisionStatementPage');


function bigfathom_core_mng_visionstatements_form() 
{
    return drupal_get_form('bigfathom_core_mng_visionstatements_form_builder');
}
function bigfathom_core_mng_visionstatements_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageVisionStatementsPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewvisionstatement_form() 
{
    return drupal_get_form('bigfathom_core_viewvisionstatement_form_builder');
}
function bigfathom_core_addvisionstatement_form() 
{
    return drupal_get_form('bigfathom_core_addvisionstatement_form_builder');
}
function bigfathom_core_editvisionstatement_form() 
{
    return drupal_get_form('bigfathom_core_editvisionstatement_form_builder');
}
function bigfathom_core_deletevisionstatement_form() 
{
    return drupal_get_form('bigfathom_core_deletevisionstatement_form_builder');
}

function bigfathom_core_addvisionstatement_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddVisionStatementPage();
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addvisionstatement_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddVisionStatementPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addvisionstatement_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddVisionStatementPage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/visionstatements',
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

function bigfathom_core_viewvisionstatement_form_builder($form, &$form_state)
{
    if(!isset($_GET['visionstatementid']))
    {
        throw new \Exception("Missing required visionstatementid url parameter!");
    }
    $visionstatement_id = $_GET['visionstatementid'];
    if(!is_numeric($visionstatement_id))
    {
        throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewVisionStatementPage($visionstatement_id);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletevisionstatement_form_builder($form, &$form_state)
{
    if(!isset($_GET['visionstatementid']))
    {
        throw new \Exception("Missing required visionstatementid url parameter!");
    }
    $visionstatement_id = $_GET['visionstatementid'];
    if(!is_numeric($visionstatement_id))
    {
        throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteVisionStatementPage($visionstatement_id);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletevisionstatement_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['visionstatementid']))
    {
        throw new \Exception("Missing required visionstatementid url parameter!");
    }
    $visionstatement_id = $_GET['visionstatementid'];
    if(!is_numeric($visionstatement_id))
    {
        throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteVisionStatementPage($visionstatement_id);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletevisionstatement_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['visionstatementid']))
        {
            throw new \Exception("Missing required visionstatementid url parameter!");
        }
        $visionstatement_id = $_GET['visionstatementid'];
        if(!is_numeric($visionstatement_id))
        {
            throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteVisionStatementPage($visionstatement_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/visionstatements',
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
function bigfathom_core_editvisionstatement_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['visionstatementid']))
        {
            throw new \Exception("Missing required visionstatementid url parameter!");
        }
        $visionstatement_id = $_GET['visionstatementid'];
        if(!is_numeric($visionstatement_id))
        {
            throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditVisionStatementPage($visionstatement_id);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editvisionstatement_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['visionstatementid']))
    {
        throw new \Exception("Missing required visionstatementid url parameter!");
    }
    $visionstatement_id = $_GET['visionstatementid'];
    if(!is_numeric($visionstatement_id))
    {
        throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditVisionStatementPage($visionstatement_id);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editvisionstatement_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['visionstatementid']))
        {
            throw new \Exception("Missing required visionstatementid url parameter!");
        }
        $visionstatement_id = $_GET['visionstatementid'];
        if(!is_numeric($visionstatement_id))
        {
            throw new \Exception("Invalid visionstatementid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditVisionStatementPage($visionstatement_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/visionstatements',
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

