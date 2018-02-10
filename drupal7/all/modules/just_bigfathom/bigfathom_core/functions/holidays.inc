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

module_load_include('php','bigfathom_core','form/ManageHolidaysPage');
module_load_include('php','bigfathom_core','form/ViewHolidayPage');
module_load_include('php','bigfathom_core','form/AddHolidayPage');
module_load_include('php','bigfathom_core','form/EditHolidayPage');
module_load_include('php','bigfathom_core','form/DeleteHolidayPage');


function bigfathom_core_mng_holidays_form() 
{
    return drupal_get_form('bigfathom_core_mng_holidays_form_builder');
}
function bigfathom_core_mng_holidays_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageHolidaysPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewholiday_form() 
{
    return drupal_get_form('bigfathom_core_viewholiday_form_builder');
}
function bigfathom_core_addholiday_form() 
{
    return drupal_get_form('bigfathom_core_addholiday_form_builder');
}
function bigfathom_core_editholiday_form() 
{
    return drupal_get_form('bigfathom_core_editholiday_form_builder');
}
function bigfathom_core_deleteholiday_form() 
{
    return drupal_get_form('bigfathom_core_deleteholiday_form_builder');
}

function bigfathom_core_addholiday_form_builder($form, &$form_state)
{
    $oPI = new \bigfathom\AddHolidayPage();
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addholiday_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddHolidayPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addholiday_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddHolidayPage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/holidays',
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

function bigfathom_core_viewholiday_form_builder($form, &$form_state)
{
    if(!isset($_GET['holidayid']))
    {
        throw new \Exception("Missing required holidayid url parameter!");
    }
    $holiday_id = $_GET['holidayid'];
    if(!is_numeric($holiday_id))
    {
        throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewHolidayPage($holiday_id);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteholiday_form_builder($form, &$form_state)
{
    if(!isset($_GET['holidayid']))
    {
        throw new \Exception("Missing required holidayid url parameter!");
    }
    $holiday_id = $_GET['holidayid'];
    if(!is_numeric($holiday_id))
    {
        throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteHolidayPage($holiday_id);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteholiday_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['holidayid']))
    {
        throw new \Exception("Missing required holidayid url parameter!");
    }
    $holiday_id = $_GET['holidayid'];
    if(!is_numeric($holiday_id))
    {
        throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteHolidayPage($holiday_id);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteholiday_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['holidayid']))
        {
            throw new \Exception("Missing required holidayid url parameter!");
        }
        $holiday_id = $_GET['holidayid'];
        if(!is_numeric($holiday_id))
        {
            throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteHolidayPage($holiday_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/holidays',
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
function bigfathom_core_editholiday_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['holidayid']))
        {
            throw new \Exception("Missing required holidayid url parameter!");
        }
        $holiday_id = $_GET['holidayid'];
        if(!is_numeric($holiday_id))
        {
            throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditHolidayPage($holiday_id);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editholiday_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['holidayid']))
    {
        throw new \Exception("Missing required holidayid url parameter!");
    }
    $holiday_id = $_GET['holidayid'];
    if(!is_numeric($holiday_id))
    {
        throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditHolidayPage($holiday_id);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editholiday_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['holidayid']))
        {
            throw new \Exception("Missing required holidayid url parameter!");
        }
        $holiday_id = $_GET['holidayid'];
        if(!is_numeric($holiday_id))
        {
            throw new \Exception("Invalid holidayid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditHolidayPage($holiday_id);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/holidays',
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

