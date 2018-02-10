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

module_load_include('php','bigfathom_core','form/ManageURIDomainPage');
module_load_include('php','bigfathom_core','form/ViewURIDomainPage');
module_load_include('php','bigfathom_core','form/AddURIDomainPage');
module_load_include('php','bigfathom_core','form/EditURIDomainPage');
module_load_include('php','bigfathom_core','form/DeleteURIDomainPage');

function bigfathom_core_mng_uridomain_form() 
{
    return drupal_get_form('bigfathom_core_mng_uridomain_form_builder');
}
function bigfathom_core_mng_uridomain_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageURIDomainPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewuridomain_form() 
{
    return drupal_get_form('bigfathom_core_viewuridomain_form_builder');
}
function bigfathom_core_adduridomain_form() 
{
    return drupal_get_form('bigfathom_core_adduridomain_form_builder');
}
function bigfathom_core_edituridomain_form() 
{
    return drupal_get_form('bigfathom_core_edituridomain_form_builder');
}
function bigfathom_core_deleteuridomain_form() 
{
    return drupal_get_form('bigfathom_core_deleteuridomain_form_builder');
}

function bigfathom_core_adduridomain_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddURIDomainPage($urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_adduridomain_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddURIDomainPage($urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_adduridomain_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        $oPI = new \bigfathom\AddURIDomainPage($urls_arr);
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

function bigfathom_core_viewuridomain_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain url parameter: that value MUST NOT be numeric!");
        }

        $oPI = new \bigfathom\ViewURIDomainPage($uridomain_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteuridomain_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain url parameter: that value MUST NOT be numeric!");
        }

        $oPI = new \bigfathom\DeleteURIDomainPage($uridomain_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteuridomain_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain_id url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\DeleteURIDomainPage($uridomain_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteuridomain_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain_id url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\DeleteURIDomainPage($uridomain_id,NULL,$urls_arr);
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
function bigfathom_core_edituridomain_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\EditURIDomainPage($uridomain_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edituridomain_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\EditURIDomainPage($uridomain_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_edituridomain_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['uridomain']))
        {
            throw new \Exception("Missing required uridomain url parameter!");
        }
        $uridomain_id = $_GET['uridomain'];
        if(is_numeric($uridomain_id))
        {
            throw new \Exception("Invalid uridomain url parameter: that value MUST NOT be numeric!");
        }
        $oPI = new \bigfathom\EditURIDomainPage($uridomain_id,NULL,$urls_arr);
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
