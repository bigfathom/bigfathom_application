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
module_load_include('php','bigfathom_core','form/TopInfoTabsPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_DashboardPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_OneProjectPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_SiteAdminPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_CommunicationsPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_RemoteProjectsPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_YourAccountPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_ReportsPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_TemplatesPage');
module_load_include('php','bigfathom_core','form/TopInfoTabs_HelpPage');



module_load_include('php','bigfathom_core','form/TopInfoDesignPage');
module_load_include('php','bigfathom_core','form/TopInfoManageDetailsPage');
module_load_include('php','bigfathom_core','form/TopInfoScorePage');
module_load_include('php','bigfathom_core','form/TopInfoForecastPage');

function bigfathom_core_topinfo_form()
{
    //Use dashboards as the default tab
    return drupal_get_form('bigfathom_core_topinfo_dashboards_form_builder');
}

function bigfathom_core_topinfo_dashboards_form()
{
    return drupal_get_form('bigfathom_core_topinfo_dashboards_form_builder');
}
function bigfathom_core_topinfo_dashboards_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_DashboardPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_oneproject_form()
{
    return drupal_get_form('bigfathom_core_topinfo_oneproject_form_builder');
}
function bigfathom_core_topinfo_oneproject_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_OneProjectPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_help_form()
{
    return drupal_get_form('bigfathom_core_topinfo_help_form_builder');
}
function bigfathom_core_topinfo_help_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_HelpPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_reports_form()
{
    return drupal_get_form('bigfathom_core_topinfo_reports_form_builder');
}
function bigfathom_core_topinfo_reports_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_ReportsPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_youraccount_form()
{
    return drupal_get_form('bigfathom_core_topinfo_youraccount_form_builder');
}
function bigfathom_core_topinfo_youraccount_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_YourAccountPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_templates_form()
{
    return drupal_get_form('bigfathom_core_topinfo_templates_form_builder');
}
function bigfathom_core_topinfo_templates_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_TemplatesPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_remoteprojects_form()
{
    return drupal_get_form('bigfathom_core_topinfo_remoteprojects_form_builder');
}
function bigfathom_core_topinfo_remoteprojects_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_RemoteProjectsPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_sitemanage_form()
{
    return drupal_get_form('bigfathom_core_topinfo_sitemanage_form_builder');
}
function bigfathom_core_topinfo_sitemanage_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_SiteAdminPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topinfo_communications_form()
{
    return drupal_get_form('bigfathom_core_topinfo_communications_form_builder');
}
function bigfathom_core_topinfo_communications_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoTabs_CommunicationsPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_topmanagedetails_form()
{
    return drupal_get_form('bigfathom_core_topmanagedetails_form_builder');
}
function bigfathom_core_topmanagedetails_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoManageDetailsPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_topinfodesign_form() 
{
    return drupal_get_form('bigfathom_core_topinfodesign_form_builder');
}
function bigfathom_core_topinfodesign_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoDesignPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_topinfoscore_form() 
{
    return drupal_get_form('bigfathom_core_topinfoscore_form_builder');
}
function bigfathom_core_topinfoscore_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['projectid']))
        {
            $projectid = NULL;
        } else {
            $projectid = $_GET['projectid'];
        }
        if(!empty($projectid) && !is_numeric($projectid))
        {
            throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\TopInfoScorePage($projectid);
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_topinfo_forecast_form() 
{
    return drupal_get_form('bigfathom_core_topinfo_forecast_form_builder');
}
function bigfathom_core_topinfo_forecast_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TopInfoForecastPage();
        return bigfathom_core_master_simple_form_builder($oPI, $form, $form_state);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
