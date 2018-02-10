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

module_load_include('php','bigfathom_core','core/Context');
module_load_include('php','bigfathom_core','form/Dashboard4UserPage');
module_load_include('php','bigfathom_core','form/Dashboard4ProjectPage');
module_load_include('php','bigfathom_core','form/Dashboard4WorkitemPage');
module_load_include('php','bigfathom_core','form/Dashboard4GroupPage');
module_load_include('php','bigfathom_core','form/Dashboard4AllYourProjectsPage');


module_load_include('php','bigfathom_core','form/QuadrantDisplayPage');


function bigfathom_core_quadrantdisplay_form() 
{
    return drupal_get_form('bigfathom_core_quadrantdisplay_form_builder');
}
function bigfathom_core_quadrantdisplay_form_builder($form, &$form_state)
{
    try
    {
        $oContext = \bigfathom\Context::getInstance();
        $projectid = $oContext->getSelectedProjectID();
        $oPI = new \bigfathom\QuadrantDisplayPage($projectid);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_dashboard4user_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4user_form_builder');
}
function bigfathom_core_dashboard4user_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['personid']))
        {
            $selected_projectid = trim($_GET['personid']);
        } else {
            global $user;
            $selected_projectid = $user->uid;
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4UserPage($selected_projectid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_dashboard4project_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4project_form_builder');
}
function bigfathom_core_dashboard4project_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['projectid']))
        {
            $selected_projectid = trim($_GET['projectid']);
        } else {
            $oContext = \bigfathom\Context::getInstance();
            $selected_projectid = $oContext->getSelectedProjectID();
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4ProjectPage($selected_projectid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_dashboard4all_yourprojects_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4all_yourprojects_form_builder');
}
function bigfathom_core_dashboard4all_yourprojects_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4AllYourProjectsPage($urls_arr);
        $form = [];
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_dashboard4group_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4group_form_builder');
}
function bigfathom_core_dashboard4group_form_builder($form, &$form_state)
{
    try
    {
        if(empty($_GET['groupid']))
        {
            $selected_groupid = NULL;
        } else {
            $selected_groupid = trim($_GET['groupid']);
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4GroupPage($selected_groupid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_dashboard4workitem_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4workitem_form_builder');
}
function bigfathom_core_dashboard4workitem_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['workitemid']))
        {
            $selected_goalid = trim($_GET['workitemid']);
        } else {
            throw new \Exception("Must provide the workitemid!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4WorkitemPage($selected_goalid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_dashboard4sprint_form() 
{
    return drupal_get_form('bigfathom_core_dashboard4sprint_form_builder');
}
function bigfathom_core_dashboard4sprint_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['sprintid']))
        {
            $selected_sprintid = trim($_GET['sprintid']);
        } else {
            throw new \Exception("Must provide the id!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\Dashboard4SprintPage($selected_sprintid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


