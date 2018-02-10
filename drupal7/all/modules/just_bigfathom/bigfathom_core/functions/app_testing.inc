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

module_load_include('php','bigfathom_core','form/ManageAppTestingPage');

function bigfathom_core_mng_apptesting_form() 
{
    return drupal_get_form('bigfathom_core_mng_apptesting_form_builder');
}
function bigfathom_core_launch_apptesting_form() 
{
    return drupal_get_form('bigfathom_core_launch_apptesting_form_builder');
}
function bigfathom_core_cancel_apptesting_form() 
{
    return drupal_get_form('bigfathom_core_cancel_apptesting_form_builder');
}
function bigfathom_core_mng_apptesting_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['launchmode']))
        {
            $launchmode = $_GET['launchmode'];
        } else {
            $launchmode = NULL;
        }
        if(isset($_GET['launchfilter']))
        {
            $launchfilter = $_GET['launchfilter'];
        } else {
            $launchfilter = NULL;
        }
        $oPI = new \bigfathom\ManageAppTestingPage($launchmode,$launchfilter);
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_launch_apptesting_form_builder($form, &$form_state)
{
    try
    {
        $launchmode = 'LAUNCH_NOW';
        if(isset($_GET['launchfilter']))
        {
            $launchfilter = $_GET['launchfilter'];
        } else {
            $launchfilter = NULL;
        }
        $oPI = new \bigfathom\ManageAppTestingPage($launchmode,$launchfilter);
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_cancel_apptesting_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['launchfilter']))
        {
            $launchfilter = $_GET['launchfilter'];
        } else {
            $launchfilter = NULL;
        }
        $launchmode = 'CANCEL_NOW';
        $oPI = new \bigfathom\ManageAppTestingPage($launchmode,$launchfilter);
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
