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

module_load_include('php','bigfathom_core','form/ManageWorkitemDurationsPage');
module_load_include('php','bigfathom_core','form/ManageWorkitemForecastsPage');
module_load_include('php','bigfathom_core','form/ManageSubprojectsPage');
module_load_include('php','bigfathom_core','form/ManageWorkitems2SprintPage');
module_load_include('php','bigfathom_core','form/ManageWorkTypeConversionsPage');
module_load_include('php','bigfathom_core','form/ManageWorkitems2RolesPage');
module_load_include('php','bigfathom_core','form/ViewWorkitemPage');
module_load_include('php','bigfathom_core','form/AddWorkitemPage');
module_load_include('php','bigfathom_core','form/DuplicateWorkitemPage');
module_load_include('php','bigfathom_core','form/AddTaskPage');
module_load_include('php','bigfathom_core','form/AddGoalPage');
module_load_include('php','bigfathom_core','form/EditWorkitemPage');
module_load_include('php','bigfathom_core','form/DeleteWorkitemPage');

module_load_include('php','bigfathom_core','form/ManageWorkitemCommentsPage');
module_load_include('php','bigfathom_core','form/ViewWorkitemCommentPage');
module_load_include('php','bigfathom_core','form/AddWorkitemCommentPage');
module_load_include('php','bigfathom_core','form/EditWorkitemCommentPage');
module_load_include('php','bigfathom_core','form/DeleteWorkitemCommentPage');

module_load_include('php','bigfathom_core','form/ManagePersonInfluencePage');
module_load_include('php','bigfathom_core','form/ManageImportanceOfWorkPage');

module_load_include('php','bigfathom_core','form/ManageVision2ProjectPage');


function bigfathom_core_mng_subprojects_form() 
{
    return drupal_get_form('bigfathom_core_mng_subprojects_form_builder');
}
function bigfathom_core_mng_subprojects_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManageSubprojectsPage($urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_influence_person2wi_form() 
{
    return drupal_get_form('bigfathom_core_mng_influence_person2wi_form_builder');
}
function bigfathom_core_mng_influence_person2wi_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManagePersonInfluencePage($urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_importance_person2wi_form() 
{
    if(bigfathom_core_enforce_project_required())
    return drupal_get_form('bigfathom_core_mng_importance_person2wi_form_builder');
}
function bigfathom_core_mng_importance_person2wi_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManageImportanceOfWorkPage($urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_workitem_duration_form() 
{
    if(bigfathom_core_enforce_project_required())
    return drupal_get_form('bigfathom_core_mng_workitem_duration_form_builder');
}
function bigfathom_core_mng_workitem_duration_form_builder($form, &$form_state)
{
    try
    {
        
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManageWorkitemDurationsPage($urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_workitem_forecast_form() 
{
    if(bigfathom_core_enforce_project_required())
    return drupal_get_form('bigfathom_core_mng_workitem_forecast_form_builder');
}
function bigfathom_core_mng_workitem_forecast_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManageWorkitemForecastsPage($urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_workitem_type_conversions_form() 
{
    return drupal_get_form('bigfathom_core_mng_workitem_type_conversions_form_builder');
}
function bigfathom_core_mng_workitem_type_conversions_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['sprintid']))
        {
            $sprintid = NULL;
        } else {
            $sprintid = $_GET['sprintid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageWorkTypeConversionsPage($sprintid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_workitem2sprint_form() 
{
    if(bigfathom_core_enforce_project_required())
    return drupal_get_form('bigfathom_core_mng_workitem2sprint_form_builder');
}
function bigfathom_core_mng_workitem2sprint_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['sprintid']))
        {
            $sprintid = NULL;
        } else {
            $sprintid = $_GET['sprintid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageWorkitems2SprintPage($sprintid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_mng_workitem2roles_form() 
{
    if(bigfathom_core_enforce_project_required())
    return drupal_get_form('bigfathom_core_mng_workitem2roles_form_builder');
}
function bigfathom_core_mng_workitem2roles_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['projectid']))
        {
            $projectid = NULL;
        } else {
            $projectid = $_GET['projectid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageWorkitems2RolesPage($projectid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_view_workitem_form() 
{
    return drupal_get_form('bigfathom_core_view_workitem_form_builder');
}
function bigfathom_core_add_workitem_form() 
{
    return drupal_get_form('bigfathom_core_add_workitem_form_builder');
}
function bigfathom_core_edit_workitem_form() 
{
    return drupal_get_form('bigfathom_core_edit_workitem_form_builder');
}
function bigfathom_core_duplicate_workitem_form() 
{
    return drupal_get_form('bigfathom_core_duplicate_workitem_form_builder');
}
function bigfathom_core_delete_workitem_form() 
{
    return drupal_get_form('bigfathom_core_delete_workitem_form_builder');
}

function bigfathom_core_add_workitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
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
        if(isset($myvalues['basetype']))
        {
            $workitem_basetype = $myvalues['basetype'];
        } else {
            if(!isset($_GET['basetype']))
            {
                $workitem_basetype = 'G';
            } else {
                $workitem_basetype = $_GET['basetype'];
            }
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        $oPI = new \bigfathom\AddWorkitemPage($workitem_basetype, $projectid, $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        $myvalues['owner_projectid'] = $projectid;
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_add_workitem_form_builder_validate($form, &$form_state) 
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
        if(isset($myvalues['basetype']))
        {
            $workitem_basetype = $myvalues['basetype'];
        } else {
            if(!isset($_GET['basetype']))
            {
                $workitem_basetype = 'G';
            } else {
                $workitem_basetype = $_GET['basetype'];
            }
        }
        
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        $oPI = new \bigfathom\AddWorkitemPage($workitem_basetype, $projectid, $urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_add_workitem_form_builder_submit($form, &$form_state) 
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
        if(isset($myvalues['basetype']))
        {
            $workitem_basetype = $myvalues['basetype'];
        } else {
            if(!isset($_GET['basetype']))
            {
                $workitem_basetype = 'G';
            } else {
                $workitem_basetype = $_GET['basetype'];
            }
        }
        
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddWorkitemPage($workitem_basetype, $projectid, $urls_arr);
        $bundle = $oPI->updateDatabase($form, $myvalues);
        if(!empty($bundle['redirect']))
        {
            $redirect = $bundle['redirect'];
        } else {
            $redirect = $page_args['menu_base'];
        }
        $form_state['redirect'] = array(
            $redirect,
            array(
              'query' => array(
                'action' => 'added_workitem',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_view_workitem_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\ViewWorkitemPage($workitemid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_delete_workitem_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\DeleteWorkitemPage($workitemid,NULL,$urls_arr);
        $form = [];
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_delete_workitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\DeleteWorkitemPage($workitemid,NULL,$urls_arr);
        $result = $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(array_key_exists('redirect',$result))
        {
            $redirect_url = $result['redirect'];
            if(empty($result['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($result['rparams']));
            }
        } else {
            $redirect_url = $page_args['menu_base'];
        }
        $rparams_ar['action'] = 'deleted';
        if(!empty($urls_arr['cpk']))
        {
            $rparams_ar['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $redirect_url,
            array(
              'query' => $rparams_ar,
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_helper_get_workitem_urls()
{
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
        if(isset($_GET['rparams']))
        {
            $urls_arr['rparams'] = $_GET['rparams'];
        }
    }
    if(isset($_GET['cpk']))
    {
        $urls_arr['cpk'] = $_GET['cpk'];
    }
    return $urls_arr;
}

function bigfathom_core_edit_workitem_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\EditWorkitemPage($workitemid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edit_workitem_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\EditWorkitemPage($workitemid,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_edit_workitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\EditWorkitemPage($workitemid,NULL,$urls_arr);
        $result = $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(array_key_exists('redirect',$result))
        {
            $redirect_url = $result['redirect'];
            if(empty($result['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($result['rparams']));
            }
        } else {
            $redirect_url = $page_args['menu_base'];
        }
        $rparams_ar['action'] = 'edited';
        if(!empty($urls_arr['cpk']))
        {
            $rparams_ar['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $redirect_url,
            array(
              'query' => $rparams_ar,
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_duplicate_workitem_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\DuplicateWorkitemPage($workitemid,NULL,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_duplicate_workitem_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\DuplicateWorkitemPage($workitemid,NULL,$urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_core_duplicate_workitem_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $urls_arr = bigfathom_core_helper_get_workitem_urls();
        $oPI = new \bigfathom\DuplicateWorkitemPage($workitemid,NULL,$urls_arr);
        $result = $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        if(array_key_exists('redirect',$result))
        {
            $redirect_url = $result['redirect'];
            if(empty($result['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($result['rparams']));
            }
        } else {
            $redirect_url = $page_args['menu_base'];
        }
        $rparams_ar['action'] = 'edited';
        if(!empty($urls_arr['cpk']))
        {
            $rparams_ar['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $redirect_url,
            array(
              'query' => $rparams_ar,
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

//The comment functions
function bigfathom_core_mng_workitem_communications_form() 
{
    return drupal_get_form('bigfathom_core_mng_workitem_communications_form_builder');
}
function bigfathom_core_mng_workitem_communications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = [];
        
        if(!isset($_GET['workitemid']))
        {
            throw new \Exception("Missing required workitemid url parameter!");
        }
        $workitemid = $_GET['workitemid'];
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        $oPI = new \bigfathom\ManageWorkitemCommentsPage($workitemid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewworkitem_communication_form() 
{
    return drupal_get_form('bigfathom_core_viewworkitem_communication_form_builder');
}
function bigfathom_core_addworkitem_communication_form() 
{
    return drupal_get_form('bigfathom_core_addworkitem_communication_form_builder');
}
function bigfathom_core_editworkitem_communication_form() 
{
    return drupal_get_form('bigfathom_core_editworkitem_communication_form_builder');
}
function bigfathom_core_deleteworkitem_communication_form() 
{
    return drupal_get_form('bigfathom_core_deleteworkitem_communication_form_builder');
}

function bigfathom_core_params_workitem_communication_page($form, &$form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    $comid = NULL;
    $parent_comid = NULL;
    $workitemid = NULL;
    $parentkey = array();
    $foundkeys = 0;
    $urls_arr = [];
    if(isset($myvalues['comid']))
    {
        $comid = $myvalues['comid'];
    } else {
        if(isset($_GET['comid']))
        {
            $comid = $_GET['comid'];
        }
    }
    if(!empty($comid))
    {
        if(!is_numeric($comid))
        {
            throw new \Exception("Invalid comid url parameter: that value MUST be numeric!");
        }
        $myvalues['comid'] = $comid;
        $parentkey['comid'] = $comid;
        $foundkeys++;
    }
    
    if(isset($myvalues['parent_comid']))
    {
        $parent_comid = $myvalues['parent_comid'];
    } else {
        if(isset($_GET['parent_comid']))
        {
            $parent_comid = $_GET['parent_comid'];
        }
    }
    if(!empty($parent_comid))
    {
        if(!is_numeric($parent_comid))
        {
            throw new \Exception("Invalid parent_comid url parameter: that value MUST be numeric!");
        }
        $myvalues['parent_comid'] = $parent_comid;
        $parentkey['parent_comid'] = $parent_comid;
        $foundkeys++;
    }
    if(isset($myvalues['workitemid']))
    {
        $workitemid = $myvalues['workitemid'];
    } else {
        if(isset($_GET['workitemid']))
        {
            $workitemid = $_GET['workitemid'];
        }
    }
    if(!empty($workitemid))
    {
        if(!is_numeric($workitemid))
        {
            throw new \Exception("Invalid workitemid url parameter: that value MUST be numeric!");
        }
        $myvalues['workitemid'] = $workitemid;
        $parentkey['workitemid'] = $workitemid;
        $foundkeys++;
    }
    if(isset($myvalues['urls_arr']))
    {
        $urls_arr = $myvalues['urls_arr'];
    } else {
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
    }
    if(empty($urls_arr['return']))
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $returnURL = $page_args['menu_base'];
        $urls_arr['return'] = $returnURL;
    }
    if(isset($_GET['cpk']))
    {
        $urls_arr['cpk'] = $_GET['cpk'];
    }
    if($foundkeys == 0)
    {
        throw new \Exception("Did NOT find any key URL parameters!");
    }
    $bundle['urls_arr'] = $urls_arr;
    $bundle['parentkey'] = $parentkey;
    $bundle['myvalues'] = $myvalues;
    return $bundle;
}

function bigfathom_core_addworkitem_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddWorkitemCommentPage($bundle['parentkey'], $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addworkitem_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        
        //https://api.drupal.org/api/drupal/includes%21file.inc/function/file_save_upload/7
        //https://www.drupal.org/node/1537950
        
        //$num_files = count($_FILES['files']['name']);
        //$destination = "temporary://";
        $i=0;
        if(empty($form_state['values']['attachments']))
        {
            $form_state['values']['attachments'] = array();
        }
        foreach($_FILES['files']['name'] as $fieldname=>$filename)
        {
            if(!empty($filename))
            {
                if(empty($allowed_filetypes))
                {
                    form_set_error('file', t("Rejected $filename because no file uploades are allowed!"));
                } else {
                    $file = file_save_upload($fieldname, array('file_validate_extensions' => array($allowed_filetypes)));

                    if ($file) 
                    {
                        if ($file = file_move($file, 'public://')) 
                        {
                            $form_state['values']['attachments'][$i] = $file;
                        } else {
                            form_set_error('file', t('Failed to write the uploaded file the site\'s file folder.'));
                        }
                    } 
                    $i++;
                }
            }
        }
        $oPI = new \bigfathom\AddWorkitemCommentPage($bundle['parentkey'], $urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addworkitem_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $bundle['myvalues'];
        $oPI = new \bigfathom\AddWorkitemCommentPage($bundle['parentkey'], $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $qparms = array(
                'action' => 'added',
                'workitemid' => $myvalues['workitemid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => $qparms,
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewworkitem_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        if(!isset($_GET['comid']))
        {
            throw new \Exception("Missing required comid url parameter!");
        }
        $comid = $_GET['comid'];
        if(!is_numeric($comid))
        {
            throw new \Exception("Invalid comid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewWorkitemCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteworkitem_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        if(!isset($_GET['comid']))
        {
            throw new \Exception("Missing required comid url parameter!");
        }
        $comid = $_GET['comid'];
        if(!is_numeric($comid))
        {
            throw new \Exception("Invalid comid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteWorkitemCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteworkitem_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        $oPI = new \bigfathom\DeleteWorkitemCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $qparms = array(
                'action' => 'deleted',
                'workitemid' => $myvalues['workitemid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => $qparms,
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editworkitem_communication_form_builder($form, &$form_state)
{
    try
    {
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($myvalues['my_page_args']))
        {
            $page_args = $myvalues['my_page_args'];
        } else {
            $page_args = bigfathom_core_get_menu_page_arguments();
            $form_state['values']['my_page_args'] = $page_args;
        }
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        if(isset($myvalues['id']))
        {
            $comid = $myvalues['id'];
            $gotfieldvalues = TRUE;
        } else {
            $gotfieldvalues = FALSE;
            if(!isset($_GET['comid']))
            {
                throw new \Exception("Missing required comid url parameter!");
            }
            $comid = $_GET['comid'];
        }
        if(!is_numeric($comid))
        {
            throw new \Exception("Invalid comid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditWorkitemCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        if(!$gotfieldvalues)
        {
            $myvalues = $oPI->getFieldValues();
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_editworkitem_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        //https://api.drupal.org/api/drupal/includes%21file.inc/function/file_save_upload/7
        //https://www.drupal.org/node/1537950
        
        //$num_files = count($_FILES['files']['name']);
        //$destination = "temporary://";
        $i=0;
        if(empty($form_state['values']['attachments']))
        {
            $form_state['values']['attachments'] = array();
        }
        foreach($_FILES['files']['name'] as $fieldname=>$filename)
        {
            if(!empty($filename))
            {
                if(empty($allowed_filetypes))
                {
                    form_set_error('file', t("Rejected $filename because no file uploades are allowed!"));
                } else {
                    $file = file_save_upload($fieldname, array('file_validate_extensions' => array($allowed_filetypes)));

                    if ($file) 
                    {
                        if ($file = file_move($file, 'public://')) 
                        {
                            $form_state['values']['attachments'][$i] = $file;
                        } else {
                            form_set_error('file', t('Failed to write the uploaded file the site\'s file folder.'));
                        }
                    } 
                    $i++;
                }
            }
        }
        
        $oPI = new \bigfathom\EditWorkitemCommentPage($comid,$urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_editworkitem_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        $oPI = new \bigfathom\EditWorkitemCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $qparms = array(
                'action' => 'edited',
                'workitemid' => $myvalues['workitemid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => $qparms,
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}



function bigfathom_core_mng_visionstatement2project_in_project_form() 
{
    return drupal_get_form('bigfathom_core_mng_visionstatement2project_in_project_form_builder');
}
function bigfathom_core_mng_visionstatement2project_in_project_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['projectid']))
        {
            $projectid = NULL;
        } else {
            $projectid = $_GET['projectid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        $oPI = new \bigfathom\ManageVision2ProjectPage($projectid, $urls_arr);
        $form = [];
        $disabled = FALSE;
        $myvalues = [];    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
