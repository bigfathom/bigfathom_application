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

module_load_include('php','bigfathom_core','form/ManageSystemRolesPage');
module_load_include('php','bigfathom_core','form/ViewSystemRolePage');
module_load_include('php','bigfathom_core','form/AddSystemRolePage');
module_load_include('php','bigfathom_core','form/EditSystemRolePage');
module_load_include('php','bigfathom_core','form/DeleteSystemRolePage');

function bigfathom_core_managesysroles_form() 
{
    return drupal_get_form('bigfathom_core_managesysroles_form_builder');
}
function bigfathom_core_managesysroles_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageSystemRolesPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewsysrole_form() 
{
    return drupal_get_form('bigfathom_core_viewsysrole_form_builder');
}
function bigfathom_core_addsysrole_form() 
{
    return drupal_get_form('bigfathom_core_addsysrole_form_builder');
}
function bigfathom_core_editsysrole_form() 
{
    return drupal_get_form('bigfathom_core_editsysrole_form_builder');
}
function bigfathom_core_deletesysrole_form() 
{
    return drupal_get_form('bigfathom_core_deletesysrole_form_builder');
}

function bigfathom_core_addsysrole_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddSystemRolePage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addsysrole_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddSystemRolePage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addsysrole_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddSystemRolePage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/sysroles',
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

function bigfathom_core_viewsysrole_form_builder($form, &$form_state)
{
    if(!isset($_GET['sysrole_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $sysrole_id = $_GET['sysrole_id'];
    if(!is_numeric($sysrole_id))
    {
        throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewSystemRolePage($sysrole_id);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletesysrole_form_builder($form, &$form_state)
{
    if(!isset($_GET['sysrole_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $sysrole_id = $_GET['sysrole_id'];
    if(!is_numeric($sysrole_id))
    {
        throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteSystemRolePage($sysrole_id);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletesysrole_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['sysrole_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sysrole_id = $_GET['sysrole_id'];
        if(!is_numeric($sysrole_id))
        {
            throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteSystemRolePage($sysrole_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/sysroles',
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
function bigfathom_core_editsysrole_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['sysrole_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sysrole_id = $_GET['sysrole_id'];
        if(!is_numeric($sysrole_id))
        {
            throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditSystemRolePage($sysrole_id);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editsysrole_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['sysrole_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $sysrole_id = $_GET['sysrole_id'];
    if(!is_numeric($sysrole_id))
    {
        throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditSystemRolePage($sysrole_id);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editsysrole_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['sysrole_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sysrole_id = $_GET['sysrole_id'];
        if(!is_numeric($sysrole_id))
        {
            throw new \Exception("Invalid sysrole_id url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditSystemRolePage($sysrole_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/sysroles',
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
