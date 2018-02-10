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

module_load_include('php','bigfathom_core','form/ManageSprintsPage');
module_load_include('php','bigfathom_core','form/ViewSprintPage');
module_load_include('php','bigfathom_core','form/AddSprintPage');
module_load_include('php','bigfathom_core','form/EditSprintPage');
module_load_include('php','bigfathom_core','form/DeleteSprintPage');
module_load_include('php','bigfathom_core','form/ManageSprintCommentsPage');
module_load_include('php','bigfathom_core','form/ViewSprintCommentPage');
module_load_include('php','bigfathom_core','form/AddSprintCommentPage');
module_load_include('php','bigfathom_core','form/EditSprintCommentPage');
module_load_include('php','bigfathom_core','form/DeleteSprintCommentPage');

function bigfathom_core_managesprints_form() 
{
    return drupal_get_form('bigfathom_core_managesprints_form_builder');
}
function bigfathom_core_managesprints_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageSprintsPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($_GET['projectid']))
        {
            $myvalues['owner_projectid'] = $_GET['projectid'];
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_managesprints_form_builder_validate($form, &$form_state) 
{
    return TRUE;
}
function bigfathom_core_managesprints_form_builder_submit($form, &$form_state) 
{
    try
    {
        $myvalues = $form_state['values'];
        if(isset($myvalues['owner_projectid']) && $myvalues['owner_projectid'] != NULL)
        {
            $owner_projectid = $myvalues['owner_projectid'];
            $_SESSION['selected_projectid'] = $owner_projectid;
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_sprint_lock_membership_form() 
{
    return drupal_get_form('bigfathom_core_sprint_lock_membership_form_builder');
}
function bigfathom_core_sprint_unlock_membership_form() 
{
    return drupal_get_form('bigfathom_core_sprint_unlock_membership_form_builder');
}

function bigfathom_core_sprint_lock_membership_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required sprintid!!!!");
        }
        $sprintid = $_GET['sprintid'];
        $oContext = \bigfathom\Context::getInstance();
        $oContext->lockSprintMembership($sprintid);
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
            $rparams_ar['action'] = 'locked_membership';
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

function bigfathom_core_sprint_unlock_membership_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required sprintid!!!!");
        }
        $sprintid = $_GET['sprintid'];
        $oContext = \bigfathom\Context::getInstance();
        $oContext->unlockSprintMembership($sprintid);
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
            $rparams_ar['action'] = 'unlocked_membership';
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


function bigfathom_core_viewsprint_form() 
{
    return drupal_get_form('bigfathom_core_viewsprint_form_builder');
}
function bigfathom_core_addsprint_form() 
{
    return drupal_get_form('bigfathom_core_addsprint_form_builder');
}
function bigfathom_core_editsprint_form() 
{
    return drupal_get_form('bigfathom_core_editsprint_form_builder');
}
function bigfathom_core_deletesprint_form() 
{
    return drupal_get_form('bigfathom_core_deletesprint_form_builder');
}

function bigfathom_core_addsprint_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\AddSprintPage($projectid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        $myvalues['owner_projectid'] = $projectid;
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addsprint_form_builder_validate($form, &$form_state) 
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
        
        $oPI = new \bigfathom\AddSprintPage($projectid,$urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_addsprint_form_builder_submit($form, &$form_state) 
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
        
        $oPI = new \bigfathom\AddSprintPage($projectid, $urls_arr);
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

function bigfathom_core_viewsprint_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);

        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\ViewSprintPage($sprintid, NULL, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($sprintid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_deletesprint_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteSprintPage($sprintid, NULL, $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues($sprintid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletesprint_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(isset($_GET['cpk']))
        {
            $urls_arr['cpk'] = $_GET['cpk'];
        }
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        $oPI = new \bigfathom\DeleteSprintPage($sprintid, NULL, $urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletesprint_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteSprintPage($sprintid, NULL, $urls_arr);
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
function bigfathom_core_editsprint_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditSprintPage($sprintid, NULL, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues($sprintid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editsprint_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        $oPI = new \bigfathom\EditSprintPage($sprintid, NULL, $urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editsprint_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required gn url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid gn url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditSprintPage($sprintid, NULL, $urls_arr);
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

//The comment functions
function bigfathom_core_mng_sprint_communications_form() 
{
    return drupal_get_form('bigfathom_core_mng_sprint_communications_form_builder');
}
function bigfathom_core_mng_sprint_communications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['sprintid']))
        {
            throw new \Exception("Missing required sprintid url parameter!");
        }
        $sprintid = $_GET['sprintid'];
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid sprintid url parameter: that value MUST be numeric!");
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
        $oPI = new \bigfathom\ManageSprintCommentsPage($sprintid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewsprint_communication_form() 
{
    return drupal_get_form('bigfathom_core_viewsprint_communication_form_builder');
}
function bigfathom_core_addsprint_communication_form() 
{
    return drupal_get_form('bigfathom_core_addsprint_communication_form_builder');
}
function bigfathom_core_editsprint_communication_form() 
{
    return drupal_get_form('bigfathom_core_editsprint_communication_form_builder');
}
function bigfathom_core_deletesprint_communication_form() 
{
    return drupal_get_form('bigfathom_core_deletesprint_communication_form_builder');
}

function bigfathom_core_params_sprint_communication_page($form, &$form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    $comid = NULL;
    $parent_comid = NULL;
    $sprintid = NULL;
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
    if(isset($myvalues['sprintid']))
    {
        $sprintid = $myvalues['sprintid'];
    } else {
        if(isset($_GET['sprintid']))
        {
            $sprintid = $_GET['sprintid'];
        }
    }
    if(!empty($sprintid))
    {
        if(!is_numeric($sprintid))
        {
            throw new \Exception("Invalid sprintid url parameter: that value MUST be numeric!");
        }
        $myvalues['sprintid'] = $sprintid;
        $parentkey['sprintid'] = $sprintid;
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

function bigfathom_core_addsprint_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddSprintCommentPage($bundle['parentkey'], $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addsprint_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
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
        $oPI = new \bigfathom\AddSprintCommentPage($bundle['parentkey'], $urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

/*
function bigfathom_core_addsprint_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddSprintCommentPage($bundle['parentkey'], $urls_arr);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
 */
function bigfathom_core_addsprint_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $bundle['myvalues'];
        $oPI = new \bigfathom\AddSprintCommentPage($bundle['parentkey'], $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $qparms = array(
                'action' => 'added',
                'sprintid' => $myvalues['sprintid'],
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

function bigfathom_core_viewsprint_communication_form_builder($form, &$form_state)
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

        $oPI = new \bigfathom\ViewSprintCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletesprint_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
        if(!isset($_GET['comid']))
        {
            throw new \Exception("Missing required comid url parameter!");
        }
        $comid = $_GET['comid'];
        if(!is_numeric($comid))
        {
            throw new \Exception("Invalid comid url parameter: that value MUST be numeric!");
        }

        $oPI = new \bigfathom\DeleteSprintCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletesprint_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        $oPI = new \bigfathom\DeleteSprintCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $qparms = array(
                'action' => 'deleted',
                'sprintid' => $myvalues['sprintid'],
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
function bigfathom_core_editsprint_communication_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\EditSprintCommentPage($comid,$urls_arr);
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

function bigfathom_core_editsprint_communication_form_builder_validate($form, &$form_state) 
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
        
        $oPI = new \bigfathom\EditSprintCommentPage($comid,$urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_editsprint_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_sprint_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        $oPI = new \bigfathom\EditSprintCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $qparms = array(
                'action' => 'edited',
                'sprintid' => $myvalues['sprintid'],
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

