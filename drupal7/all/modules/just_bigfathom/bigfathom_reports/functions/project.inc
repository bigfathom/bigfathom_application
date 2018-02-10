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

module_load_include('php','bigfathom_reports','form/StatusAllProjectsPage');
module_load_include('php','bigfathom_reports','form/UserLoginHistoryPage');
module_load_include('php','bigfathom_reports','form/GanttOneProjectPage');
module_load_include('php','bigfathom_reports','form/PathAnalysisOneProjectPage');
module_load_include('php','bigfathom_reports','form/AllProject2ProjectPathsPage');
module_load_include('php','bigfathom_reports','form/ChangeHistoryOneProjectPage');
module_load_include('php','bigfathom_reports','form/UseCaseMappingOneProjectPage');
module_load_include('php','bigfathom_reports','form/TestCaseMappingOneProjectPage');
module_load_include('php','bigfathom_reports','form/TagMapOneProjectPage');


function bigfathom_reports_statusallprojects_form() 
{
    return drupal_get_form('bigfathom_reports_statusallprojects_form_builder');
}
function bigfathom_reports_statusallprojects_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\StatusAllProjectsPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_userloginhistory_form() 
{
    return drupal_get_form('bigfathom_reports_userloginhistory_form_builder');
}
function bigfathom_reports_userloginhistory_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\UserLoginHistoryPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_pathsoneproject_form() 
{
    return drupal_get_form('bigfathom_reports_pathsoneproject_form_builder');
}
function bigfathom_reports_pathsoneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\PathAnalysisOneProjectPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_project2projectpaths_form() 
{
    return drupal_get_form('bigfathom_reports_project2projectpaths_form_builder');
}
function bigfathom_reports_project2projectpaths_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\AllProject2ProjectPathsPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_changehistoryoneproject_form() 
{
    return drupal_get_form('bigfathom_reports_changehistoryoneproject_form_builder');
}
function bigfathom_reports_changehistoryoneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ChangeHistoryOneProjectPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_usecasesoneproject_form() 
{
    return drupal_get_form('bigfathom_reports_usecasesoneproject_form_builder');
}
function bigfathom_reports_usecasesoneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\UseCaseMappingOneProjectPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_testcasesoneproject_form() 
{
    return drupal_get_form('bigfathom_reports_testcasesoneproject_form_builder');
}
function bigfathom_reports_testcasesoneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TestCaseMappingOneProjectPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_reports_tagmaponeproject_form() 
{
    return drupal_get_form('bigfathom_reports_tagmaponeproject_form_builder');
}
function bigfathom_reports_tagmaponeproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TagMapOneProjectPage();
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_ganttoneproject_form() 
{
    return drupal_get_form('bigfathom_reports_ganttoneproject_form_builder');
}
function bigfathom_reports_ganttoneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\GanttOneProjectPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_reports_topinfoscore_form() 
{
    return drupal_get_form('bigfathom_reports_topinfoscore_form_builder');
}
function bigfathom_reports_topinfoscore_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoScorePage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
