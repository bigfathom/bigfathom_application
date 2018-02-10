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

module_load_include('php','bigfathom_reports','form/PersonAvailabilityPage');
module_load_include('php','bigfathom_reports','form/PersonUtilizationPage');


function bigfathom_reports_personutilization_form() 
{
    return drupal_get_form('bigfathom_reports_personutilization_form_builder');
}
function bigfathom_reports_personutilization_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\PersonUtilizationPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_reports_personavailability_form() 
{
    return drupal_get_form('bigfathom_reports_personavailability_form_builder');
}
function bigfathom_reports_personavailability_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\PersonAvailabilityPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
