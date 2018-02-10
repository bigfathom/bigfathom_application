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

module_load_include('php','bigfathom_core','form/ManageTestCasesPage');
module_load_include('php','bigfathom_core','form/ViewTestCasePage');
module_load_include('php','bigfathom_core','form/AddTestCasePage');
module_load_include('php','bigfathom_core','form/EditTestCasePage');
module_load_include('php','bigfathom_core','form/ExecuteTestCasePage');
module_load_include('php','bigfathom_core','form/DeleteTestCasePage');

module_load_include('php','bigfathom_core','form/SendNotificationTestcasePage');

module_load_include('php','bigfathom_core','form/ManageTestcaseCommentsPage');
module_load_include('php','bigfathom_core','form/ViewTestcaseCommentPage');
module_load_include('php','bigfathom_core','form/AddTestcaseCommentPage');
module_load_include('php','bigfathom_core','form/EditTestcaseCommentPage');
module_load_include('php','bigfathom_core','form/DeleteTestcaseCommentPage');

function bigfathom_core_mng_testcases_form() 
{
    return drupal_get_form('bigfathom_core_mng_testcases_form_builder');
}
function bigfathom_core_mng_testcases_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageTestCasesPage();
        $form = [];
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewtestcase_form() 
{
    return drupal_get_form('bigfathom_core_viewtestcase_form_builder');
}
function bigfathom_core_addtestcase_form() 
{
    return drupal_get_form('bigfathom_core_addtestcase_form_builder');
}
function bigfathom_core_edittestcase_form() 
{
    return drupal_get_form('bigfathom_core_edittestcase_form_builder');
}
function bigfathom_core_executetestcase_form() 
{
    return drupal_get_form('bigfathom_core_executetestcase_form_builder');
}
function bigfathom_core_deletetestcase_form() 
{
    return drupal_get_form('bigfathom_core_deletetestcase_form_builder');
}

function bigfathom_core_addtestcase_form_builder($form, &$form_state)
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddTestCasePage(NULL,$projectid);
    $form = [];
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addtestcase_form_builder_validate($form, &$form_state) 
{
    $oContext = \bigfathom\Context::getInstance();
    $projectid = $oContext->getSelectedProjectID();
    $oPI = new \bigfathom\AddTestCasePage(NULL,$projectid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addtestcase_form_builder_submit($form, &$form_state) 
{
    try
    {
        $oContext = \bigfathom\Context::getInstance();
        $projectid = $oContext->getSelectedProjectID();
        $oPI = new \bigfathom\AddTestCasePage(NULL,$projectid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/testcases',
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

function bigfathom_core_viewtestcase_form_builder($form, &$form_state)
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\ViewTestCasePage($testcaseid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletetestcase_form_builder($form, &$form_state)
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }
    
    $oPI = new \bigfathom\DeleteTestCasePage($testcaseid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletetestcase_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\DeleteTestCasePage($testcaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletetestcase_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\DeleteTestCasePage($testcaseid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/testcases',
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
function bigfathom_core_edittestcase_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditTestCasePage($testcaseid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edittestcase_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\EditTestCasePage($testcaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_edittestcase_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\EditTestCasePage($testcaseid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/testcases',
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

//==========================================
function bigfathom_core_executetestcase_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\ExecuteTestCasePage($testcaseid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_executetestcase_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\ExecuteTestCasePage($testcaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_executetestcase_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $oPI = new \bigfathom\ExecuteTestCasePage($testcaseid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/testcases',
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

//NOTIFICATIONS
function bigfathom_core_mng_testcase_send_notifications_form() 
{
    return drupal_get_form('bigfathom_core_mng_testcase_send_notifications_form_builder');
}
function bigfathom_core_mng_testcase_send_notifications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $in_dialog = $page_args['in_dialog'];
        $urls_arr = [];
        
        if(!isset($_GET['action']))
        {
            $action = NULL;
        } else {
            $action = strtolower($_GET['action']);
        }
        if($action == 'sent')
        {
            return \bigfathom\SendNotificationTestcasePage::getSentForm($form, $form_state);
        } else {
            if(!isset($_GET['testcaseid']))
            {
                throw new \Exception("Missing required testcaseid url parameter!");
            }
            $testcaseid = $_GET['testcaseid'];
            if(!is_numeric($testcaseid))
            {
                throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
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
            $oPI = new \bigfathom\SendNotificationTestcasePage($testcaseid,$urls_arr,$in_dialog);
            $form = [];
            $disabled = FALSE;
            $myvalues = $oPI->getFieldValues($testcaseid);
            return $oPI->getForm($form, $form_state, $disabled, $myvalues);
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_mng_testcase_send_notifications_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['testcaseid']))
    {
        throw new \Exception("Missing required testcaseid url parameter!");
    }
    $testcaseid = $_GET['testcaseid'];
    if(!is_numeric($testcaseid))
    {
        throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
    }
    $oPI = new \bigfathom\SendNotificationTestcasePage($testcaseid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_mng_testcase_send_notifications_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $in_dialog = $page_args['in_dialog'];
        $urls_arr = [];
        
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        
        //SEND THE MESSAGE NOW
        $oPI = new \bigfathom\SendNotificationTestcasePage($testcaseid);
        $oPI->updateDatabase($form, $form_state['values']);

        //Success!
        if($in_dialog)
        {
            $gotourl = 'bigfathom/testcase/send_notifications_indialog';
        } else {
            $gotourl = 'bigfathom/testcase/send_notifications';
        }
        $form_state['redirect'] = array(
            $gotourl,
            array(
              'query' => array(
                'action' => 'sent',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

//COMMUNICATIONS 
//The comment functions
function bigfathom_core_mng_testcase_communications_form() 
{
    return drupal_get_form('bigfathom_core_mng_testcase_communications_form_builder');
}
function bigfathom_core_mng_testcase_communications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $in_dialog = $page_args['in_dialog'];
        $urls_arr = [];
        
        if(!isset($_GET['testcaseid']))
        {
            throw new \Exception("Missing required testcaseid url parameter!");
        }
        $testcaseid = $_GET['testcaseid'];
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['testcasestepid']))
        {
            $testcasestepid = NULL;
        } else {
            $testcasestepid = $_GET['testcasestepid'];
            if(!is_numeric($testcasestepid))
            {
                throw new \Exception("Invalid testcasestepid url parameter: that value MUST be numeric!");
            }
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
        $oPI = new \bigfathom\ManageTestcaseCommentsPage($testcaseid,$testcasestepid,$urls_arr,$in_dialog);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewtestcase_communication_form() 
{
    return drupal_get_form('bigfathom_core_viewtestcase_communication_form_builder');
}
function bigfathom_core_addtestcase_communication_form() 
{
    return drupal_get_form('bigfathom_core_addtestcase_communication_form_builder');
}
function bigfathom_core_edittestcase_communication_form() 
{
    return drupal_get_form('bigfathom_core_edittestcase_communication_form_builder');
}
function bigfathom_core_deletetestcase_communication_form() 
{
    return drupal_get_form('bigfathom_core_deletetestcase_communication_form_builder');
}

function bigfathom_core_params_testcase_communication_page($form, &$form_state)
{
    $page_args = bigfathom_core_get_menu_page_arguments();
    $in_dialog = $page_args['in_dialog'];
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    $comid = NULL;
    $parent_comid = NULL;
    $testcaseid = NULL;
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
    if(isset($myvalues['testcaseid']))
    {
        $testcaseid = $myvalues['testcaseid'];
    } else {
        if(isset($_GET['testcaseid']))
        {
            $testcaseid = $_GET['testcaseid'];
        }
    }
    if(!isset($_GET['testcasestepid']))
    {
        $testcasestepid = NULL;
    } else {
        $testcasestepid = $_GET['testcasestepid'];
        if(!empty($testcasestepid) && !is_numeric($testcasestepid))
        {
            throw new \Exception("Invalid testcasestepid ($testcasestepid) url parameter: that value MUST be numeric!");
        }
    }
    if(!isset($_GET['default_stepnum']))
    {
        $default_stepnum = NULL;
    } else {
        $default_stepnum = trim($_GET['default_stepnum']);
        if(!empty($default_stepnum) && !is_numeric($default_stepnum))
        {
            throw new \Exception("Invalid default_stepnum ($default_stepnum) url parameter: that value MUST be numeric!");
        }
    }
    if(!empty($testcaseid))
    {
        if(!is_numeric($testcaseid))
        {
            throw new \Exception("Invalid testcaseid url parameter: that value MUST be numeric!");
        }
        $myvalues['testcaseid'] = $testcaseid;
        $parentkey['testcaseid'] = $testcaseid;
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
    $urls_arr['default_stepnum'] = $default_stepnum;    //Easy way to pass into the exiting form
    
    $bundle['in_dialog'] = $in_dialog;
    $bundle['urls_arr'] = $urls_arr;
    $bundle['parentkey'] = $parentkey;
    $bundle['testcasestepid'] = $testcasestepid;
    $bundle['myvalues'] = $myvalues;
    return $bundle;
}

function bigfathom_core_addtestcase_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddTestcaseCommentPage($bundle['parentkey'], $urls_arr, $in_dialog);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        if(isset($_GET['default_stepnum']))
        {
            $myvalues['default_stepnum'] = $_GET['default_stepnum'];
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addtestcase_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $in_dialog = $page_args['in_dialog'];
        
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
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
        $oPI = new \bigfathom\AddTestcaseCommentPage($bundle['parentkey'], $urls_arr, $in_dialog);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addtestcase_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $bundle['myvalues'];
        $oPI = new \bigfathom\AddTestcaseCommentPage($bundle['parentkey'], $urls_arr, $in_dialog);
        $oPI->updateDatabase($form, $form_state['values']);
        
        //Success!
        if(!empty($myvalues['parent_page']))
        {
            $page_key = $myvalues['parent_page'];
        } else {
            $page_key = $page_args['menu_base'];
        }
        
        $qparms = array(
                'action' => 'added',
                'testcaseid' => $myvalues['testcaseid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_key,
            array(
              'query' => $qparms,
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewtestcase_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
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

        $oPI = new \bigfathom\ViewTestcaseCommentPage($comid,$urls_arr,$in_dialog);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletetestcase_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
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

        $oPI = new \bigfathom\DeleteTestcaseCommentPage($comid,$urls_arr,$in_dialog);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletetestcase_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        $oPI = new \bigfathom\DeleteTestcaseCommentPage($comid, $urls_arr, $in_dialog);
        $oPI->updateDatabase($form, $myvalues);
        
        //Success!
        if(!empty($myvalues['parent_page']))
        {
            $page_key = $myvalues['parent_page'];
        } else {
            $page_key = $page_args['menu_base'];
        }
        
        $qparms = array(
                'action' => 'deleted',
                'testcaseid' => $myvalues['testcaseid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_key,
            array(
              'query' => $qparms,
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edittestcase_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
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
        $oPI = new \bigfathom\EditTestcaseCommentPage($comid,$urls_arr,$in_dialog);
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

function bigfathom_core_edittestcase_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
        
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

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
        
        $oPI = new \bigfathom\EditTestcaseCommentPage($comid,$urls_arr,$in_dialog);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_edittestcase_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_testcase_communication_page($form, $form_state);
        $in_dialog = $bundle['in_dialog'];
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        $oPI = new \bigfathom\EditTestcaseCommentPage($comid, $urls_arr, $in_dialog);
        $oPI->updateDatabase($form, $myvalues);
        
        //Success!
        if(!empty($myvalues['parent_page']))
        {
            $page_key = $myvalues['parent_page'];
        } else {
            $page_key = $page_args['menu_base'];
        }
        
        $qparms = array(
                'action' => 'edited',
                'testcaseid' => $myvalues['testcaseid'],
              );
        if(!empty($urls_arr['cpk']))
        {
            $qparms['cpk'] = $urls_arr['cpk'];
        }
        $form_state['redirect'] = array(
            $page_key,
            array(
              'query' => $qparms,
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}



