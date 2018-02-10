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

module_load_include('php','bigfathom_core','form/ManageUseCasesPage');
module_load_include('php','bigfathom_core','form/ViewUseCasePage');
module_load_include('php','bigfathom_core','form/AddUseCasePage');
module_load_include('php','bigfathom_core','form/EditUseCasePage');
module_load_include('php','bigfathom_core','form/DeleteUseCasePage');


function bigfathom_core_mng_usecases_form() 
{
    return drupal_get_form('bigfathom_core_mng_usecases_form_builder');
}
function bigfathom_core_mng_usecases_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageUseCasesPage();
        $form = [];
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewusecase_form() 
{
    return drupal_get_form('bigfathom_core_viewusecase_form_builder');
}
function bigfathom_core_addusecase_form() 
{
    return drupal_get_form('bigfathom_core_addusecase_form_builder');
}
function bigfathom_core_editusecase_form() 
{
    return drupal_get_form('bigfathom_core_editusecase_form_builder');
}
function bigfathom_core_deleteusecase_form() 
{
    return drupal_get_form('bigfathom_core_deleteusecase_form_builder');
}

function bigfathom_core_addusecase_form_builder($form, &$form_state)
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddUseCasePage(NULL,$projectid);
    $form = [];
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addusecase_form_builder_validate($form, &$form_state) 
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddUseCasePage(NULL,$projectid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addusecase_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oContext = \bigfathom\Context::getInstance();
        $projectid = $oContext->getSelectedProjectID();
        $oPI = new \bigfathom\AddUseCasePage(NULL,$projectid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/usecases',
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

function bigfathom_core_viewusecase_form_builder($form, &$form_state)
{
    if(!isset($_GET['usecaseid']))
    {
        throw new \Exception("Missing required usecaseid url parameter!");
    }
    $usecaseid = $_GET['usecaseid'];
    if(!is_numeric($usecaseid))
    {
        throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewUseCasePage($usecaseid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteusecase_form_builder($form, &$form_state)
{
    if(!isset($_GET['usecaseid']))
    {
        throw new \Exception("Missing required usecaseid url parameter!");
    }
    $usecaseid = $_GET['usecaseid'];
    if(!is_numeric($usecaseid))
    {
        throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteUseCasePage($usecaseid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteusecase_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['usecaseid']))
    {
        throw new \Exception("Missing required usecaseid url parameter!");
    }
    $usecaseid = $_GET['usecaseid'];
    if(!is_numeric($usecaseid))
    {
        throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteUseCasePage($usecaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteusecase_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['usecaseid']))
        {
            throw new \Exception("Missing required usecaseid url parameter!");
        }
        $usecaseid = $_GET['usecaseid'];
        if(!is_numeric($usecaseid))
        {
            throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteUseCasePage($usecaseid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/usecases',
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
function bigfathom_core_editusecase_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['usecaseid']))
        {
            throw new \Exception("Missing required usecaseid url parameter!");
        }
        $usecaseid = $_GET['usecaseid'];
        if(!is_numeric($usecaseid))
        {
            throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditUseCasePage($usecaseid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editusecase_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['usecaseid']))
    {
        throw new \Exception("Missing required usecaseid url parameter!");
    }
    $usecaseid = $_GET['usecaseid'];
    if(!is_numeric($usecaseid))
    {
        throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditUseCasePage($usecaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editusecase_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['usecaseid']))
        {
            throw new \Exception("Missing required usecaseid url parameter!");
        }
        $usecaseid = $_GET['usecaseid'];
        if(!is_numeric($usecaseid))
        {
            throw new \Exception("Invalid usecaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditUseCasePage($usecaseid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/usecases',
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

