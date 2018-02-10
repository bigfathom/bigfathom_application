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

module_load_include('php','bigfathom_core','form/ManageGroupsPage');
module_load_include('php','bigfathom_core','form/ViewGroupPage');
module_load_include('php','bigfathom_core','form/AddGroupPage');
module_load_include('php','bigfathom_core','form/EditGroupPage');
module_load_include('php','bigfathom_core','form/DeleteGroupPage');

module_load_include('php','bigfathom_core','form/ManageMemberGroupsPage');
module_load_include('php','bigfathom_core','form/ManagePCMembershipPage');
module_load_include('php','bigfathom_core','form/ManageGroupMembershipPage');


function bigfathom_core_mng_groups_form() 
{
    return drupal_get_form('bigfathom_core_mng_groups_form_builder');
}
function bigfathom_core_mng_groups_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageGroupsPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewgroup_form() 
{
    return drupal_get_form('bigfathom_core_viewgroup_form_builder');
}
function bigfathom_core_addgroup_form() 
{
    return drupal_get_form('bigfathom_core_addgroup_form_builder');
}
function bigfathom_core_editgroup_form() 
{
    return drupal_get_form('bigfathom_core_editgroup_form_builder');
}
function bigfathom_core_deletegroup_form() 
{
    return drupal_get_form('bigfathom_core_deletegroup_form_builder');
}

function bigfathom_core_addgroup_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddGroupPage();
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addgroup_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddGroupPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addgroup_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddGroupPage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/groups',
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

function bigfathom_core_viewgroup_form_builder($form, &$form_state)
{
    if(!isset($_GET['groupid']))
    {
        throw new \Exception("Missing required groupid url parameter!");
    }
    $groupid = $_GET['groupid'];
    if(!is_numeric($groupid))
    {
        throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewGroupPage($groupid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletegroup_form_builder($form, &$form_state)
{
    if(!isset($_GET['groupid']))
    {
        throw new \Exception("Missing required groupid url parameter!");
    }
    $groupid = $_GET['groupid'];
    if(!is_numeric($groupid))
    {
        throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteGroupPage($groupid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletegroup_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['groupid']))
    {
        throw new \Exception("Missing required groupid url parameter!");
    }
    $groupid = $_GET['groupid'];
    if(!is_numeric($groupid))
    {
        throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteGroupPage($groupid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletegroup_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['groupid']))
        {
            throw new \Exception("Missing required groupid url parameter!");
        }
        $groupid = $_GET['groupid'];
        if(!is_numeric($groupid))
        {
            throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteGroupPage($groupid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/groups',
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
function bigfathom_core_editgroup_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['groupid']))
        {
            throw new \Exception("Missing required groupid url parameter!");
        }
        $groupid = $_GET['groupid'];
        if(!is_numeric($groupid))
        {
            throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditGroupPage($groupid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editgroup_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['groupid']))
    {
        throw new \Exception("Missing required groupid url parameter!");
    }
    $groupid = $_GET['groupid'];
    if(!is_numeric($groupid))
    {
        throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditGroupPage($groupid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editgroup_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['groupid']))
        {
            throw new \Exception("Missing required groupid url parameter!");
        }
        $groupid = $_GET['groupid'];
        if(!is_numeric($groupid))
        {
            throw new \Exception("Invalid groupid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditGroupPage($groupid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/groups',
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

//==========================

function bigfathom_core_mng_groups2project_form() 
{
    return drupal_get_form('bigfathom_core_mng_groups2project_form_builder');
}
function bigfathom_core_mng_groups2project_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['projectid']))
        {
            $projectid = NULL;
        } else {
            $projectid = $_GET['projectid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageMemberGroupsPage($projectid, $urls_arr);
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_person2pcg_in_project_form() 
{
    return drupal_get_form('bigfathom_core_mng_person2pcg_in_project_form_builder');
}
function bigfathom_core_mng_person2pcg_in_project_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['projectid']))
        {
            $projectid = NULL;
        } else {
            $projectid = $_GET['projectid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManagePCMembershipPage($projectid, $urls_arr);
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_person2group_form() 
{
    return drupal_get_form('bigfathom_core_mng_person2group_form_builder');
}
function bigfathom_core_mng_person2group_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['groupid']))
        {
            throw new \Exception("Missing required groupid!");
        } else {
            $groupid = $_GET['groupid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageGroupMembershipPage($groupid, $urls_arr);
        if(empty($form))
        {
            $form = [];
        }
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
