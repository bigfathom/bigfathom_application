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
 */

module_load_include('php','bigfathom_core','form/MapProjectDependenciesPage');
module_load_include('php','bigfathom_core','form/MapTemplateDependenciesPage');

function bigfathom_core_map_templatedependencies_form() 
{
    return drupal_get_form('bigfathom_core_map_templatedependencies_form_builder');
}

function bigfathom_core_map_projectdependencies_form() 
{
    return drupal_get_form('bigfathom_core_map_projectdependencies_form_builder');
}

function bigfathom_core_map_projectdependencies_form_builder($form, &$form_state)
{
    try
    {
        $oContext = \bigfathom\Context::getInstance();
        $projectid = isset($_GET['projectid']) ? $_GET['projectid'] : $oContext->getSelectedProjectID();
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $page_parambundle = isset($myvalues['page_parambundle']) ? $myvalues['page_parambundle'] : array();
        if(!isset($page_parambundle['goalid']))
        {
            if(isset($_GET['goalid']))
            {
                $page_parambundle['goalid'] = $_GET['goalid'];
            }
        }
        foreach($_GET as $k=>$v)
        {
            if(substr($k,0,5) == 'jump2')
            {
                $page_parambundle[$k] = $v;    
            }
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\MapProjectDependenciesPage($projectid, $urls_arr, $page_parambundle);
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_map_templatedependencies_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['templateid']))
        {
            throw new \Exception("Missing required templateid paramter!");
        }
        $oContext = \bigfathom\Context::getInstance();
        $templateid = $_GET['templateid'];
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $page_parambundle = isset($myvalues['page_parambundle']) ? $myvalues['page_parambundle'] : array();
        if(!isset($page_parambundle['goalid']))
        {
            if(isset($_GET['goalid']))
            {
                $page_parambundle['goalid'] = $_GET['goalid'];
            }
        }
        foreach($_GET as $k=>$v)
        {
            if(substr($k,0,5) == 'jump2')
            {
                $page_parambundle[$k] = $v;    
            }
        }
        $oPI = new \bigfathom\MapTemplateDependenciesPage($templateid, null, $page_parambundle);
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

