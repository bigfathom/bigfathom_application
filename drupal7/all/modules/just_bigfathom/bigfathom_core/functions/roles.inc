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

module_load_include('php','bigfathom_core','form/ManageRolesPage');
module_load_include('php','bigfathom_core','form/ViewRolePage');
module_load_include('php','bigfathom_core','form/AddRolePage');
module_load_include('php','bigfathom_core','form/EditRolePage');
module_load_include('php','bigfathom_core','form/DeleteRolePage');

function bigfathom_core_manageroles_form() 
{
    return drupal_get_form('bigfathom_core_manageroles_form_builder');
}
function bigfathom_core_manageroles_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageRolesPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewrole_form() 
{
    return drupal_get_form('bigfathom_core_viewrole_form_builder');
}
function bigfathom_core_addrole_form() 
{
    return drupal_get_form('bigfathom_core_addrole_form_builder');
}
function bigfathom_core_editrole_form() 
{
    return drupal_get_form('bigfathom_core_editrole_form_builder');
}
function bigfathom_core_deleterole_form() 
{
    return drupal_get_form('bigfathom_core_deleterole_form_builder');
}

function bigfathom_core_addrole_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddRolePage();
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addrole_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddRolePage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addrole_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddRolePage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/roles',
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

function bigfathom_core_viewrole_form_builder($form, &$form_state)
{
    if(!isset($_GET['role_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $role_nm = $_GET['role_id'];
    if(is_numeric($role_nm))
    {
        throw new \Exception("Invalid gn url parameter: that value MUST NOT be numeric!");
    }

    $oPI = new \bigfathom\ViewRolePage($role_nm);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($role_nm);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleterole_form_builder($form, &$form_state)
{
    if(!isset($_GET['role_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $role_nm = $_GET['role_id'];
    if(is_numeric($role_nm))
    {
        throw new \Exception("Invalid gn url parameter: that value MUST NOT be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteRolePage($role_nm);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($role_nm);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleterole_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['role_id']))
    {
        throw new \Exception("Missing required tn url parameter!");
    }
    $role_nm = $_GET['role_id'];
    $oPI = new \bigfathom\DeleteRolePage($role_nm);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleterole_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['role_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $role_nm = $_GET['role_id'];
        if(is_numeric($role_nm))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\DeleteRolePage($role_nm);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/roles',
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
function bigfathom_core_editrole_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['role_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $role_nm = $_GET['role_id'];
        if(is_numeric($role_nm))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\EditRolePage($role_nm);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($role_nm);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editrole_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['role_id']))
    {
        throw new \Exception("Missing required gn url parameter!");
    }
    $role_nm = $_GET['role_id'];
    $oPI = new \bigfathom\EditRolePage($role_nm);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editrole_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['role_id']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $role_nm = $_GET['role_id'];
        if(is_numeric($role_nm))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\EditRolePage($role_nm);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/roles',
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
