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

module_load_include('php','bigfathom_core','form/ManageCommunicationOverviewPage');
module_load_include('php','bigfathom_core','form/ManageCommunicationSearchPage');
module_load_include('php','bigfathom_core','form/ViewCommunicationItemPage');
module_load_include('php','bigfathom_core','form/AddCommunicationItemPage');
module_load_include('php','bigfathom_core','form/EditCommunicationItemPage');
module_load_include('php','bigfathom_core','form/DeleteCommunicationItemPage');

function bigfathom_core_project_communication_restore_all_parkinglot_form() 
{
    return drupal_get_form('bigfathom_core_project_communication_restore_all_parkinglot_form_builder');
}
function bigfathom_core_project_communication_restore_all_parkinglot_form_builder($form, &$form_state)
{
    try
    {
        $actionname = 'restore_all_parkinglot';
        bigfathom_core_project_communication_modify_all_implementation($form, $form_state, $actionname);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_project_communication_move_trashcan2parkinglot_form() 
{
    return drupal_get_form('bigfathom_core_project_communication_move_trashcan2parkinglot_form_builder');
}
function bigfathom_core_project_communication_move_trashcan2parkinglot_form_builder($form, &$form_state)
{
    try
    {
        $actionname = 'move_trashcan2parkinglot';
        bigfathom_core_project_communication_modify_all_implementation($form, $form_state, $actionname);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_project_communication_move_parkinglot2trashcan_form() 
{
    return drupal_get_form('bigfathom_core_project_communication_move_parkinglot2trashcan_form_builder');
}
function bigfathom_core_project_communication_move_parkinglot2trashcan_form_builder($form, &$form_state)
{
    try
    {
        $actionname = 'move_parkinglot2trashcan';
        bigfathom_core_project_communication_modify_all_implementation($form, $form_state, $actionname);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_project_communication_empty_the_trashcan_form() 
{
    return drupal_get_form('bigfathom_core_project_communication_empty_the_trashcan_form_builder');
}
function bigfathom_core_project_communication_empty_the_trashcan_form_builder($form, &$form_state)
{
    try
    {
        $actionname = 'empty_the_trashcan';
        bigfathom_core_project_communication_modify_all_implementation($form, $form_state, $actionname);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_project_communication_modify_all_implementation($form, &$form_state, $actionname)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(empty($actionname))
        {
            throw new \Exception("Missing required actionname!!!!");
        }
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid!!!!");
        }
        $projectid = $_GET['projectid'];
        $oContext = \bigfathom\Context::getInstance();
        $oContext->modifyAllProjectBranstormTopics($projectid, $actionname);
        if(isset($_GET['return']))
        {
            $redirect_url=$_GET['return'];
        } else
        if(isset($_GET['redirect']))
        {
            $redirect_url=$_GET['redirect'];
        }
        if(!empty($redirect_url))
        {
            if(empty($_GET['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($_GET['rparams']));
            }
            $rparams_ar['action'] = $actionname;
            drupal_goto($redirect_url,
                array(
                  'query' => $rparams_ar,
                    )
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_managecommunicationitems_form() 
{
    return drupal_get_form('bigfathom_core_managecommunicationitems_form_builder');
}
function bigfathom_core_managecommunicationitems_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageCommunicationOverviewPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewcommunicationitem_form() 
{
    return drupal_get_form('bigfathom_core_viewcommunicationitem_form_builder');
}
function bigfathom_core_addcommunicationitem_form() 
{
    return drupal_get_form('bigfathom_core_addcommunicationitem_form_builder');
}
function bigfathom_core_editcommunicationitem_form() 
{
    return drupal_get_form('bigfathom_core_editcommunicationitem_form_builder');
}
function bigfathom_core_deletecommunicationitem_form() 
{
    return drupal_get_form('bigfathom_core_deletecommunicationitem_form_builder');
}

function bigfathom_core_addcommunicationitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($myvalues['owner_projectid']))
        {
            $projectid = $myvalues['owner_projectid'];
        } else {
            if(!isset($_GET['projectid']))
            {
                throw new \Exception("Missing required projectid url parameter!");
            }
            $projectid = $_GET['projectid'];
        }
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddCommunicationItemPage($projectid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        $myvalues['owner_projectid'] = $projectid;
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addcommunicationitem_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($myvalues['owner_projectid']))
        {
            $projectid = $myvalues['owner_projectid'];
        } else {
            if(!isset($_GET['projectid']))
            {
                throw new \Exception("Missing required projectid url parameter!");
            }
            $projectid = $_GET['projectid'];
        }
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
        }
        
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddCommunicationItemPage($projectid, $urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addcommunicationitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($myvalues['owner_projectid']))
        {
            $projectid = $myvalues['owner_projectid'];
        } else {
            if(!isset($_GET['projectid']))
            {
                throw new \Exception("Missing required projectid url parameter!");
            }
            $projectid = $_GET['projectid'];
        }
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
        }
        
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddCommunicationItemPage($projectid, $urls_arr);
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

function bigfathom_core_viewcommunicationitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }

        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ViewCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletecommunicationitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }

        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\DeleteCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletecommunicationitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\DeleteCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
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
function bigfathom_core_editcommunicationitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\EditCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editcommunicationitem_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\EditCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editcommunicationitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['communicationitemid']))
        {
            throw new \Exception("Missing required communicationitemid url parameter!");
        }
        $communicationitem_id = $_GET['communicationitemid'];
        if(!is_numeric($communicationitem_id))
        {
            throw new \Exception("Invalid communicationitemid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\EditCommunicationItemPage($communicationitem_id,NULL,$urls_arr);
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




function bigfathom_core_managecommsearch_form() 
{
    return drupal_get_form('bigfathom_core_managecommsearch_form_builder');
}
function bigfathom_core_managecommsearch_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageCommunicationSearchPage();
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}