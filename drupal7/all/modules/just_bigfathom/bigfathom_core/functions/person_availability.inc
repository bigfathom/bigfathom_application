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

module_load_include('php','bigfathom_core','form/ManagePersonAvailabilityPage');
module_load_include('php','bigfathom_core','form/ViewPersonAvailabilityPage');
module_load_include('php','bigfathom_core','form/AddPersonAvailabilityPage');
module_load_include('php','bigfathom_core','form/EditPersonAvailabilityPage');
module_load_include('php','bigfathom_core','form/DeletePersonAvailabilityPage');


function bigfathom_core_mng_person_availability_form() 
{
    return drupal_get_form('bigfathom_core_mng_person_availability_form_builder');
}
function bigfathom_core_mng_person_availability_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\ManagePersonAvailabilityPage($personid);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewperson_availability_form() 
{
    return drupal_get_form('bigfathom_core_viewperson_availability_form_builder');
}
function bigfathom_core_addperson_availability_form() 
{
    return drupal_get_form('bigfathom_core_addperson_availability_form_builder');
}
function bigfathom_core_editperson_availability_form() 
{
    return drupal_get_form('bigfathom_core_editperson_availability_form_builder');
}
function bigfathom_core_deleteperson_availability_form() 
{
    return drupal_get_form('bigfathom_core_deleteperson_availability_form_builder');
}

function bigfathom_core_addperson_availability_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['personid']))
        {
            $personid = $_GET['personid'];
        } else {
            global $user;
            $personid = $user->uid; 
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\AddPersonAvailabilityPage($urls, $personid);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }    
}
function bigfathom_core_addperson_availability_form_builder_validate($form, &$form_state) 
{
    try
    {
        if(isset($_GET['personid']))
        {
            $personid = $_GET['personid'];
        } else {
            global $user;
            $personid = $user->uid; 
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\AddPersonAvailabilityPage($urls, $personid);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addperson_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(isset($_GET['personid']))
        {
            $personid = $_GET['personid'];
        } else {
            global $user;
            $personid = $user->uid; 
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\AddPersonAvailabilityPage($urls, $personid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(empty($urls['return']))
        {
            $form_state['redirect'] = array(
                'bigfathom/topinfo/youraccount',
                array(
                  'query' => array(
                    'action' => 'added',
                  ),
                ),
              );
        } else {
            $urls['rparams']['action'] = 'added';
            $form_state['redirect'] = array(
                $urls['return'],
                    array(
                      'query' => $urls['rparams'],
                      ),
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewperson_availability_form_builder($form, &$form_state)
{
    if(!isset($_GET['person_availabilityid']))
    {
        throw new \Exception("Missing required person_availabilityid url parameter!");
    }
    $person_availabilityid = $_GET['person_availabilityid'];
    if(!is_numeric($person_availabilityid))
    {
        throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
    }

    $urls = [];
    if(isset($_GET['return']))
    {
        $urls['return'] = $_GET['return'];
    }
    if(isset($_GET['rparams']))
    {
        $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
    }
    $oPI = new \bigfathom\ViewPersonAvailabilityPage($person_availabilityid, $urls);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}

function bigfathom_core_deleteperson_availability_form_builder($form, &$form_state)
{
    if(!isset($_GET['person_availabilityid']))
    {
        throw new \Exception("Missing required person_availabilityid url parameter!");
    }
    $person_availabilityid = $_GET['person_availabilityid'];
    if(!is_numeric($person_availabilityid))
    {
        throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
    }
    $urls = [];
    if(isset($_GET['return']))
    {
        $urls['return'] = $_GET['return'];
    }
    if(isset($_GET['rparams']))
    {
        $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
    }
    $oPI = new \bigfathom\DeletePersonAvailabilityPage($person_availabilityid, $urls);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteperson_availability_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['person_availabilityid']))
    {
        throw new \Exception("Missing required person_availabilityid url parameter!");
    }
    $person_availabilityid = $_GET['person_availabilityid'];
    if(!is_numeric($person_availabilityid))
    {
        throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
    }
    $urls = [];
    if(isset($_GET['return']))
    {
        $urls['return'] = $_GET['return'];
    }
    if(isset($_GET['rparams']))
    {
        $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
    }
    $oPI = new \bigfathom\DeletePersonAvailabilityPage($person_availabilityid, $urls);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteperson_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['person_availabilityid']))
        {
            throw new \Exception("Missing required person_availabilityid url parameter!");
        }
        $person_availabilityid = $_GET['person_availabilityid'];
        if(!is_numeric($person_availabilityid))
        {
            throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\DeletePersonAvailabilityPage($person_availabilityid, $urls);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(empty($urls['return']))
        {
            $form_state['redirect'] = array(
                'bigfathom/topinfo/youraccount',
                array(
                  'query' => array(
                    'action' => 'deleted',
                  ),
                ),
              );
        } else {
            $urls['rparams']['action'] = 'deleted';
            $form_state['redirect'] = array(
                $urls['return'],
                    array(
                      'query' => $urls['rparams'],
                      ),
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editperson_availability_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['person_availabilityid']))
        {
            throw new \Exception("Missing required person_availabilityid url parameter!");
        }
        $person_availabilityid = $_GET['person_availabilityid'];
        if(!is_numeric($person_availabilityid))
        {
            throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\EditPersonAvailabilityPage($person_availabilityid, $urls);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editperson_availability_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['person_availabilityid']))
    {
        throw new \Exception("Missing required person_availabilityid url parameter!");
    }
    $person_availabilityid = $_GET['person_availabilityid'];
    if(!is_numeric($person_availabilityid))
    {
        throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
    }
    $urls = [];
    if(isset($_GET['return']))
    {
        $urls['return'] = $_GET['return'];
    }
    if(isset($_GET['rparams']))
    {
        $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
    }
    $oPI = new \bigfathom\EditPersonAvailabilityPage($person_availabilityid, $urls);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editperson_availability_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['person_availabilityid']))
        {
            throw new \Exception("Missing required person_availabilityid url parameter!");
        }
        $person_availabilityid = $_GET['person_availabilityid'];
        if(!is_numeric($person_availabilityid))
        {
            throw new \Exception("Invalid person_availabilityid url parameter: that value MUST be numeric!");
        }
        $urls = [];
        if(isset($_GET['return']))
        {
            $urls['return'] = $_GET['return'];
        }
        if(isset($_GET['rparams']))
        {
            $urls['rparams'] = unserialize(urldecode($_GET['rparams']));
        }
        $oPI = new \bigfathom\EditPersonAvailabilityPage($person_availabilityid, $urls);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(empty($urls['return']))
        {
            $form_state['redirect'] = array(
                'bigfathom/topinfo/youraccount',
                array(
                  'query' => array(
                    'action' => 'edited',
                  ),
                ),
              );
        } else {
            $urls['rparams']['action'] = 'edited';
            $form_state['redirect'] = array(
                $urls['return'],
                    array(
                      'query' => $urls['rparams'],
                      ),
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

