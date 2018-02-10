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

module_load_include('php','bigfathom_core','form/ManageEquipmentPage');
module_load_include('php','bigfathom_core','form/ViewEquipmentPage');
module_load_include('php','bigfathom_core','form/AddEquipmentPage');
module_load_include('php','bigfathom_core','form/EditEquipmentPage');
module_load_include('php','bigfathom_core','form/DeleteEquipmentPage');

function bigfathom_core_mng_equipment_form() 
{
    return drupal_get_form('bigfathom_core_mng_equipment_form_builder');
}
function bigfathom_core_mng_equipment_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageEquipmentPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewequipment_form() 
{
    return drupal_get_form('bigfathom_core_viewequipment_form_builder');
}
function bigfathom_core_addequipment_form() 
{
    return drupal_get_form('bigfathom_core_addequipment_form_builder');
}
function bigfathom_core_editequipment_form() 
{
    return drupal_get_form('bigfathom_core_editequipment_form_builder');
}
function bigfathom_core_deleteequipment_form() 
{
    return drupal_get_form('bigfathom_core_deleteequipment_form_builder');
}

function bigfathom_core_addequipment_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        
        $oPI = new \bigfathom\AddEquipmentPage($urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addequipment_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddEquipmentPage($urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addequipment_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddEquipmentPage($urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
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

function bigfathom_core_viewequipment_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewEquipmentPage($equipmentid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteequipment_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteEquipmentPage($equipmentid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteequipment_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteEquipmentPage($equipmentid,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_deleteequipment_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteEquipmentPage($equipmentid,$urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
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
function bigfathom_core_editequipment_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditEquipmentPage($equipmentid,NULL);//,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editequipment_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditEquipmentPage($equipmentid,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editequipment_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['equipmentid']))
        {
            throw new \Exception("Missing required equipmentid url parameter!");
        }
        $equipmentid = $_GET['equipmentid'];
        if(!is_numeric($equipmentid))
        {
            throw new \Exception("Invalid equipmentid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditEquipmentPage($equipmentid,$urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
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
