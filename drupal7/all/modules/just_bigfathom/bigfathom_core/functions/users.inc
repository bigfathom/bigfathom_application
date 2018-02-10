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

module_load_include('php','bigfathom_core','form/ManagePersonsPage');
module_load_include('php','bigfathom_core','form/ViewPersonPage');
module_load_include('php','bigfathom_core','form/AddPersonPage');
module_load_include('php','bigfathom_core','form/EditPersonPage');
module_load_include('php','bigfathom_core','form/DeletePersonPage');
module_load_include('php','bigfathom_core','form/ChangePasswordPage');
//module_load_include('php','bigfathom_core','form/ManagePersonAvailabilityPage');

function bigfathom_core_mng_people_form() 
{
    return drupal_get_form('bigfathom_core_mng_people_form_builder');
}
function bigfathom_core_mng_people_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManagePersonsPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewperson_form() 
{
    return drupal_get_form('bigfathom_core_viewperson_form_builder');
}
function bigfathom_core_addperson_form() 
{
    return drupal_get_form('bigfathom_core_addperson_form_builder');
}
function bigfathom_core_editperson_form() 
{
    return drupal_get_form('bigfathom_core_editperson_form_builder');
}
function bigfathom_core_deleteperson_form() 
{
    return drupal_get_form('bigfathom_core_deleteperson_form_builder');
}

function bigfathom_core_edit_your_profile_form() 
{
    return drupal_get_form('bigfathom_core_edit_your_profile_form_builder');
}

function bigfathom_core_change_your_password_form() 
{
    return drupal_get_form('bigfathom_core_change_your_password_form_builder');
}

function bigfathom_core_mng_person_availability_form() 
{
    return drupal_get_form('bigfathom_core_editperson_form_builder');
}

function bigfathom_core_addperson_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\AddPersonPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addperson_form_builder_validate($form, &$form_state) 
{
    $oPI = new \bigfathom\AddPersonPage();
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addperson_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oPI = new \bigfathom\AddPersonPage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/people',
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

function bigfathom_core_viewperson_form_builder($form, &$form_state)
{
    if(!isset($_GET['personid']))
    {
        throw new \Exception("Missing required personid url parameter!");
    }
    $personid = $_GET['personid'];
    if(!is_numeric($personid))
    {
        throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewPersonPage($personid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($personid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteperson_form_builder($form, &$form_state)
{
    if(!isset($_GET['personid']))
    {
        throw new \Exception("Missing required personid url parameter!");
    }
    $personid = $_GET['personid'];
    if(!is_numeric($personid))
    {
        throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeletePersonPage($personid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues($personid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteperson_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['personid']))
    {
        throw new \Exception("Missing required personid url parameter!");
    }
    $personid = $_GET['personid'];
    if(!is_numeric($personid))
    {
        throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeletePersonPage($personid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteperson_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['personid']))
        {
            throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
            throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeletePersonPage($personid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/people',
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
function bigfathom_core_editperson_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['personid']))
        {
            throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
            throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditPersonPage($personid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($personid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editperson_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['personid']))
    {
        throw new \Exception("Missing required personid url parameter!");
    }
    $personid = $_GET['personid'];
    if(!is_numeric($personid))
    {
        throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditPersonPage($personid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editperson_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['personid']))
        {
            throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
            throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditPersonPage($personid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/people',
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


function bigfathom_core_edit_your_profile_form_builder($form, &$form_state)
{
    try
    {
        global $user;
        $personid = $user->uid;
        $oPI = new \bigfathom\EditPersonPage($personid, FALSE);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($personid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edit_your_profile_form_builder_validate($form, &$form_state) 
{
    global $user;
    $personid = $user->uid;
    $oPI = new \bigfathom\EditPersonPage($personid, FALSE);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_edit_your_profile_form_builder_submit($form, &$form_state) 
{
    try
    {
        global $user;
        $personid = $user->uid;
        $oPI = new \bigfathom\EditPersonPage($personid, FALSE);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/youraccount',
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


function bigfathom_core_change_your_password_form_builder($form, &$form_state)
{
    try
    {
        global $user;
        $personid = $user->uid;
        $oPI = new \bigfathom\ChangePasswordPage($personid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($personid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_change_your_password_form_builder_validate($form, &$form_state) 
{
    global $user;
    $personid = $user->uid;
    $oPI = new \bigfathom\ChangePasswordPage($personid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_change_your_password_form_builder_submit($form, &$form_state) 
{
    try
    {
        global $user;
        $personid = $user->uid;
        $oPI = new \bigfathom\ChangePasswordPage($personid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/youraccount',
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

/*
function bigfathom_core_mng_person_availability_form_builder($form, &$form_state)
{
    try
    {
	//TODO
        if(!isset($_GET['personid']))
        {
            throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
            throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\ManagePersonAvailabilityPage($personid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($personid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_mng_person_availability_form_builder_validate($form, &$form_state) 
{
    try
    {
        //TODO
        if(!isset($_GET['personid']))
        {
                throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
                throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\ManagePersonAvailabilityPage($personid);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_mng_person_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        //TODO
        if(!isset($_GET['personid']))
        {
            throw new \Exception("Missing required personid url parameter!");
        }
        $personid = $_GET['personid'];
        if(!is_numeric($personid))
        {
            throw new \Exception("Invalid shortname url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\ManagePersonAvailabilityPage($personid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/people',
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
*/
