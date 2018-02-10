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

module_load_include('php','bigfathom_core','form/ManageProjectBaselinesPage');
module_load_include('php','bigfathom_core','form/AddProjectBaselinePage');
module_load_include('php','bigfathom_core','form/ViewProjectBaselinePage');
module_load_include('php','bigfathom_core','form/EditProjectBaselinePage');
module_load_include('php','bigfathom_core','form/DeleteProjectBaselinePage');


function bigfathom_core_mng_projbaselines_form() 
{
    return drupal_get_form('bigfathom_core_mng_projbaselines_form_builder');
}
function bigfathom_core_mng_projbaselines_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageProjectBaselinesPage();
        $form = [];
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewprojbaseline_form() 
{
    return drupal_get_form('bigfathom_core_viewprojbaseline_form_builder');
}
function bigfathom_core_addprojbaseline_form() 
{
    return drupal_get_form('bigfathom_core_addprojbaseline_form_builder');
}
function bigfathom_core_editprojbaseline_form() 
{
    return drupal_get_form('bigfathom_core_editprojbaseline_form_builder');
}
function bigfathom_core_deleteprojbaseline_form() 
{
    return drupal_get_form('bigfathom_core_deleteprojbaseline_form_builder');
}

function bigfathom_core_addprojbaseline_form_builder($form, &$form_state)
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddProjectBaselinePage(NULL,$projectid);
    $form = [];
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addprojbaseline_form_builder_validate($form, &$form_state) 
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddProjectBaselinePage(NULL,$projectid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addprojbaseline_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oContext = \bigfathom\Context::getInstance();
        $projectid = $oContext->getSelectedProjectID();
        $oPI = new \bigfathom\AddProjectBaselinePage(NULL,$projectid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/projbaselines',
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

function bigfathom_core_viewprojbaseline_form_builder($form, &$form_state)
{
    if(!isset($_GET['projbaselineid']))
    {
        throw new \Exception("Missing required projbaselineid url parameter!");
    }
    $projbaselineid = $_GET['projbaselineid'];
    if(!is_numeric($projbaselineid))
    {
        throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewProjectBaselinePage($projbaselineid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteprojbaseline_form_builder($form, &$form_state)
{
    if(!isset($_GET['projbaselineid']))
    {
        throw new \Exception("Missing required projbaselineid url parameter!");
    }
    $projbaselineid = $_GET['projbaselineid'];
    if(!is_numeric($projbaselineid))
    {
        throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteProjectBaselinePage($projbaselineid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteprojbaseline_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projbaselineid']))
    {
        throw new \Exception("Missing required projbaselineid url parameter!");
    }
    $projbaselineid = $_GET['projbaselineid'];
    if(!is_numeric($projbaselineid))
    {
        throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteProjectBaselinePage($projbaselineid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteprojbaseline_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projbaselineid']))
        {
            throw new \Exception("Missing required projbaselineid url parameter!");
        }
        $projbaselineid = $_GET['projbaselineid'];
        if(!is_numeric($projbaselineid))
        {
            throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteProjectBaselinePage($projbaselineid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/projbaselines',
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
function bigfathom_core_editprojbaseline_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['projbaselineid']))
        {
            throw new \Exception("Missing required projbaselineid url parameter!");
        }
        $projbaselineid = $_GET['projbaselineid'];
        if(!is_numeric($projbaselineid))
        {
            throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditProjectBaselinePage($projbaselineid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editprojbaseline_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projbaselineid']))
    {
        throw new \Exception("Missing required projbaselineid url parameter!");
    }
    $projbaselineid = $_GET['projbaselineid'];
    if(!is_numeric($projbaselineid))
    {
        throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditProjectBaselinePage($projbaselineid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editprojbaseline_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projbaselineid']))
        {
            throw new \Exception("Missing required projbaselineid url parameter!");
        }
        $projbaselineid = $_GET['projbaselineid'];
        if(!is_numeric($projbaselineid))
        {
            throw new \Exception("Invalid projbaselineid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditProjectBaselinePage($projbaselineid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/projbaselines',
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

