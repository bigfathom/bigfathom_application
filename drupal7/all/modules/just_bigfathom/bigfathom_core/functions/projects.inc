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
 */

module_load_include('php','bigfathom_core','form/SelectProjectPage');
module_load_include('php','bigfathom_core','form/OverviewProjectPage');
module_load_include('php','bigfathom_core','form/ManageProjectsPage');
module_load_include('php','bigfathom_core','form/ViewProjectPage');
module_load_include('php','bigfathom_core','form/AddProjectPage');
module_load_include('php','bigfathom_core','form/AddTopProjectPage');
module_load_include('php','bigfathom_core','form/AddSubProjectPage');
module_load_include('php','bigfathom_core','form/EditProjectPage');
module_load_include('php','bigfathom_core','form/DeleteProjectPage');

module_load_include('php','bigfathom_core','form/DuplicateProjectPage');
module_load_include('php','bigfathom_core','form/ConvertGoal2ProjectPage');
module_load_include('php','bigfathom_core','form/ConvertProject2GoalPage');

module_load_include('php','bigfathom_core','form/ManageProjectCommentsPage');
module_load_include('php','bigfathom_core','form/ViewProjectCommentPage');
module_load_include('php','bigfathom_core','form/AddProjectCommentPage');
module_load_include('php','bigfathom_core','form/EditProjectCommentPage');
module_load_include('php','bigfathom_core','form/DeleteProjectCommentPage');

module_load_include('php','bigfathom_core','form/ManageProjectPublishingPage');
module_load_include('php','bigfathom_core','form/EditProjectInfoForPublishPage');

module_load_include('php','bigfathom_core','form/ViewPublishedProjectInfoPage');
module_load_include('php','bigfathom_core','form/DeletePublishedProjectInfoPage');



function bigfathom_core_viewpublishedprojectinfo_form() 
{
    return drupal_get_form('bigfathom_core_viewpublishedprojectinfo_form_builder');
}

function bigfathom_core_deletepublishedprojectinfo_form() 
{
    return drupal_get_form('bigfathom_core_deletepublishedprojectinfo_form_builder');
}



function bigfathom_core_project_lock_all_estimates_form() 
{
    return drupal_get_form('bigfathom_core_project_lock_all_estimates_form_builder');
}
function bigfathom_core_project_lock_all_estimates_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid!!!!");
        }
        $projectid = $_GET['projectid'];
        $oContext = \bigfathom\Context::getInstance();
        $oContext->lockAllProjectEstimates($projectid);
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
            $rparams_ar['action'] = 'locked_all_estimates';
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

function bigfathom_core_project_unlock_all_estimates_form() 
{
    return drupal_get_form('bigfathom_core_project_unlock_all_estimates_form_builder');
}
function bigfathom_core_project_unlock_all_estimates_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid!!!!");
        }
        $projectid = $_GET['projectid'];
        $oContext = \bigfathom\Context::getInstance();
        $oContext->unlockAllProjectEstimates($projectid);
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
            $rparams_ar['action'] = 'unlocked_all_estimates';
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

/*
function bigfathom_core_project_autofill_workestimates_form() 
{
    return drupal_get_form('bigfathom_core_project_autofill_workestimates_form_builder');
}
function bigfathom_core_project_autofill_workestimates_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid!!!!");
        }
        $projectid = $_GET['projectid'];
        $flags = [];
        foreach($_GET as $name=>$value)
        {
            if(strlen($name)>5)
            {
                $lname = strtolower($name);
                if(substr($lname,0,5) == 'flag_')
                {
                    $flags[$lname] = $value;
                }
            }
        }
        if(isset($_GET['return']))
        {
            $redirect_url=$_GET['return'];
        } else
        if(isset($_GET['redirect']))
        {
            $redirect_url=$_GET['redirect'];
        }
        
        $start_ms = microtime(TRUE);
        $start_timestamp = time();
        $start_show = gmdate("Y-m-d\TH:i:s\Z", $start_timestamp);

        if(empty($redirect_url))
        {
            drupal_set_message("Started autofill project#$projectid at $start_show ($start_ms)");
        }
    
        $oContext = \bigfathom\Context::getInstance();
        $oContext->autofillProjectPlan($projectid,$flags);
        
        $done_ms = microtime(TRUE);
        $done_timestamp = time();
        $done_show = gmdate("Y-m-d\TH:i:s\Z", $done_timestamp);
        $duration_ts = $done_timestamp - $start_timestamp;
        
        if(empty($redirect_url))
        {
            drupal_set_message("Finished autofill project#$projectid at $done_show with duration $duration_ts seconds ($done_ms - $start_ms)");
        } else {
            if(empty($_GET['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($_GET['rparams']));
            }
            $rparams_ar['action'] = 'autofilled';
            $rparams_ar['affectedproject'] = $projectid;
            $rparams_ar['duration'] = $duration_ts;
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
*/

function bigfathom_core_clearprojectselection_form($myargs) 
{
    return drupal_get_form('bigfathom_core_clearprojectselection_form_builder');
}
function bigfathom_core_clearprojectselection_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        $oContext = \bigfathom\Context::getInstance();
        $oContext->clearSelectedProject();
        return NULL;
    } catch (\Exception $ex) {
        throw $ex;
    }
}


//Select project
function bigfathom_core_selectproject_form() 
{
    return drupal_get_form('bigfathom_core_selectproject_form_builder');
}
function bigfathom_core_selectproject_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        $clearprojectselection = FALSE;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($_GET['clearprojectselection']))
        {
            $clearprojectselection = (strtoupper(trim($_GET['clearprojectselection'])) == 'YES');
            $oContext = \bigfathom\Context::getInstance();
            $oContext->clearSelectedProject();
            menu_rebuild();
        } else
        if(isset($_GET['projectid']))
        {
            $projectid = $_GET['projectid'];
            $myvalues['selected_projectid'] = $projectid;
            $oContext = \bigfathom\Context::getInstance();
            $oContext->setSelectedProject($projectid);
            menu_rebuild();
        }
        if(isset($_GET['redirect']))
        {
            $redirect_url=$_GET['redirect'];
            drupal_goto($redirect_url,
                array(
                  'query' => array(
                        'action' => 'selected_project',
                        ),
                    )
                );
        } else {
            $oPI = new \bigfathom\SelectProjectPage();
            $form = array();
            $disabled = FALSE;
            return $oPI->getForm($form, $form_state, $disabled, $myvalues);
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_selectproject_form_builder_validate($form, &$form_state) 
{
    return TRUE;
}
function bigfathom_core_selectproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        $myvalues = $form_state['values'];
        if(isset($myvalues['selected_projectid']) && $myvalues['selected_projectid'] != NULL)
        {
            $selected_projectid = $myvalues['selected_projectid'];
            $oContext = \bigfathom\Context::getInstance();
            $oContext->setSelectedProject($selected_projectid);
        }
        if(isset($_GET['redirect']))
        {
            $redirect_url=$_GET['redirect'];
            $form_state['redirect'] = array(
                $redirect_url,
                array(
                  'query' => array(
                    'action' => 'selected_project',
                  ),
                ),
              );
        }        
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_overviewproject_form() 
{
    return drupal_get_form('bigfathom_core_overviewproject_form_builder');
}
function bigfathom_core_overviewproject_form_builder($form, &$form_state)
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
    if(!is_numeric($projectid))
    {
        throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
    }

    $oPI = new \bigfathom\OverviewProjectPage($projectid);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$projectid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}


//Project detail from here on
function bigfathom_core_mng_projects_form() 
{
    return drupal_get_form('bigfathom_core_mng_projects_form_builder');
}
function bigfathom_core_mng_projects_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageProjectsPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($_GET['projectid']))
        {
            $myvalues['selected_projectid'] = $_GET['projectid'];
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_mng_projects_form_builder_validate($form, &$form_state) 
{
    return TRUE;
}
function bigfathom_core_mng_projects_form_builder_submit($form, &$form_state) 
{
    try
    {
        $myvalues = $form_state['values'];
        if(isset($myvalues['selected_projectid']) && $myvalues['selected_projectid'] != NULL)
        {
            $selected_projectid = $myvalues['selected_projectid'];
            //$_SESSION['selected_projectid'] = $selected_projectid;
            $oContext = \bigfathom\Context::getInstance();
            $oContext->setSelectedProject($selected_projectid);
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_viewproject_form() 
{
    return drupal_get_form('bigfathom_core_viewproject_form_builder');
}
/* deprecated
function bigfathom_core_addprojectmain_form() 
{
    return drupal_get_form('bigfathom_core_addprojectmain_form_builder');
}
function bigfathom_core_addproject_form() 
{
    return drupal_get_form('bigfathom_core_addproject_form_builder');
}
 */
function bigfathom_core_duplicateproject_form() 
{
    return drupal_get_form('bigfathom_core_duplicateproject_form_builder');
}
function bigfathom_core_editproject_form() 
{
    return drupal_get_form('bigfathom_core_editproject_form_builder');
}
function bigfathom_core_deleteproject_form() 
{
    return drupal_get_form('bigfathom_core_deleteproject_form_builder');
}

function bigfathom_core_publishprojectinfo_form() 
{
    return drupal_get_form('bigfathom_core_publishprojectinfo_form_builder');
}

function bigfathom_core_addtopproject_form() 
{
    return drupal_get_form('bigfathom_core_addtopproject_form_builder');
}
function bigfathom_core_addsubproject_form() 
{
    return drupal_get_form('bigfathom_core_addsubproject_form_builder');
}

function bigfathom_core_addtopprojectmain_form() 
{
    return drupal_get_form('bigfathom_core_addtopprojectmain_form_builder');
}
function bigfathom_core_addsubprojectmain_form() 
{
    return drupal_get_form('bigfathom_core_addsubprojectmain_form_builder');
}
function bigfathom_core_convert_goalintoproject_form() 
{
    return drupal_get_form('bigfathom_core_convert_goalintoproject_form_builder');
}
function bigfathom_core_convert_projectintogoal_form() 
{
    return drupal_get_form('bigfathom_core_convert_projectintogoal_form_builder');
}


function bigfathom_core_addtopproject_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_projectid']))
    {
        $parent_projectid = $_GET['parent_projectid'];
    } else {
        $parent_projectid = NULL;
    }
    $urls_arr = array();
    $urls_arr['return'] = 'bigfathom/projects/manage';
    $oPI = new \bigfathom\AddTopProjectPage($urls_arr,$parent_projectid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addtopproject_form_builder_validate($form, &$form_state) 
{
    $urls_arr = array();
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\AddTopProjectPage($urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addtopproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['return']))
        {
            $urls_arr['return'] = 'bigfathom/projects/manage';
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddTopProjectPage($urls_arr);
        $bundle = $oPI->updateDatabase($form, $form_state['values']);
        if(!empty($bundle['redirect']))
        {
            $redirect = $bundle['redirect'];
        } else {
            $redirect = $urls_arr['return'];
        }
        $form_state['redirect'] = array(
            $redirect,
            array(
              'query' => array(
                'action' => 'added_top_level_project',
              ),
            ),
          );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addsubproject_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_projectid']))
    {
        $parent_projectid = $_GET['parent_projectid'];
    } else {
        $parent_projectid = NULL;
    }
    $urls_arr = [];
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addsubproject_form_builder_validate($form, &$form_state) 
{
    $urls_arr = [];
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $myvalues = $form_state['values'];
    $parent_projectid = $myvalues['parent_projectid'];
    $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);
    return $oPI->looksValid($form, $form_state['values']);
}

function bigfathom_core_addsubproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
            array(
              'query' => array(
                'action' => 'added_subproject',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addtopprojectmain_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_projectid']))
    {
        $parent_projectid = $_GET['parent_projectid'];
    } else {
        $parent_projectid = NULL;
    }
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\AddTopProjectPage($urls_arr,$parent_projectid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addtopprojectmain_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $myvalues = $form_state['values'];
    $parent_projectid = $myvalues['parent_projectid'];
    $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addtopprojectmain_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);
        $bundle = $oPI->updateDatabase($form, $myvalues);
        if(!empty($bundle['redirect']))
        {
            $redirect = $bundle['redirect'];
        } else {
            if(!empty($urls_arr['return']))
            {
                $redirect = $urls_arr['return'];
            } else {
                $redirect = NULL;
            }
        }
        if(!empty($redirect))
        {
            $form_state['redirect'] = array(
                $redirect,
                array(
                  'query' => array(
                    'action' => 'added_toplevel_project',
                  ),
                ),
              );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}



function bigfathom_core_addsubprojectmain_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_projectid']))
    {
        $parent_projectid = $_GET['parent_projectid'];
    } else {
        $parent_projectid = NULL;
    }
    if(isset($_GET['root_goalid']))
    {
        $root_goalid = $_GET['root_goalid'];
    } else {
        $root_goalid = NULL;
    }
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid,$root_goalid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addsubprojectmain_form_builder_validate($form, &$form_state) 
{
    $urls_arr = array();
    $myvalues = $form_state['values'];
    $parent_projectid = $myvalues['parent_projectid'];
    //$root_goalid = $myvalues['root_goalid'];
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);//,$root_goalid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addsubprojectmain_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        //$root_goalid = $myvalues['root_goalid'];
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\AddSubProjectPage($urls_arr,$parent_projectid);//,$root_goalid);
        $bundle = $oPI->updateDatabase($form, $myvalues);
        if(!empty($bundle['redirect']))
        {
            $redirect = $bundle['redirect'];
        } else {
            if(!empty($urls_arr['return']))
            {
                $redirect = $urls_arr['return'];
            } else {
                $redirect = NULL;
            }
        }
        if(!empty($redirect))
        {
            $form_state['redirect'] = array(
                $redirect,
                array(
                  'query' => array(
                    'action' => 'added_subproject',
                  ),
                ),
              );
        }
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_convert_goalintoproject_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_projectid']))
    {
        $parent_projectid = $_GET['parent_projectid'];
    } else {
        $parent_projectid = NULL;
    }
    if(isset($_GET['root_goalid']))
    {
        $root_goalid = $_GET['root_goalid'];
    } else {
        $root_goalid = NULL;
    }
    
    $urls_arr = [];
    if(isset($_GET['return']))
    {
        $urls_arr['return'] = $_GET['return'];
        if(isset($_GET['rparams']))
        {
            $urls_arr['rparams'] = $_GET['rparams'];
        }
    } else {
        $urls_arr['return'] = 'bigfathom/projects';
    }
    $oPI = new \bigfathom\ConvertGoal2ProjectPage($urls_arr,$parent_projectid,$root_goalid);
    $form = [];
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_convert_goalintoproject_form_builder_validate($form, &$form_state) 
{
    $urls_arr = [];
    $myvalues = $form_state['values'];
    
    $parent_projectid = $myvalues['parent_projectid'];
    
    $root_goalid = $myvalues['root_goalid'];
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
    $oPI = new \bigfathom\ConvertGoal2ProjectPage($urls_arr,$parent_projectid,$root_goalid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_convert_goalintoproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = [];
        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        $root_goalid = $myvalues['root_goalid'];
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
        $oPI = new \bigfathom\ConvertGoal2ProjectPage($urls_arr,$parent_projectid,$root_goalid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
            array(
              'query' => array(
                'action' => 'convertedintoproject',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

//---------------------------------


function bigfathom_core_convert_projectintogoal_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['parent_projectid']))
        {
            $parent_projectid = $_GET['parent_projectid'];
        } else {
            $parent_projectid = NULL;
        }
        if(isset($_GET['this_projectid']))
        {
            $this_projectid = $_GET['this_projectid'];
        } else {
            $this_projectid = NULL;
        }

        $urls_arr = array();
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        } else {
            $urls_arr['return'] = 'bigfathom/projects';
        }
        $oPI = new \bigfathom\ConvertProject2GoalPage($urls_arr,$parent_projectid,$this_projectid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_convert_projectintogoal_form_builder_validate($form, &$form_state) 
{
    try
    {
        $urls_arr = array();

        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        $this_projectid = $myvalues['this_projectid'];

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
        $oPI = new \bigfathom\ConvertProject2GoalPage($urls_arr,$parent_projectid,$this_projectid);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_convert_projectintogoal_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        
        $myvalues = $form_state['values'];
        $parent_projectid = $myvalues['parent_projectid'];
        $this_projectid = $myvalues['this_projectid'];
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
        $oPI = new \bigfathom\ConvertProject2GoalPage($urls_arr,$parent_projectid,$this_projectid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
            array(
              'query' => array(
                'action' => 'convertedintogoal',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}



//=====

function bigfathom_core_viewproject_form_builder($form, &$form_state)
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\ViewProjectPage($projectid, $urls_arr);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$projectid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteproject_form_builder($form, &$form_state)
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\DeleteProjectPage($projectid, $urls_arr);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$projectid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deleteproject_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\DeleteProjectPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deleteproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\DeleteProjectPage($projectid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/manage',
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
function bigfathom_core_editproject_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\EditProjectPage($projectid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();//$projectid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editproject_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\EditProjectPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_editproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\EditProjectPage($projectid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
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
            $rparams_ar['action'] = 'saved_changes';
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

function bigfathom_core_duplicateproject_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\DuplicateProjectPage($projectid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();//$projectid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_duplicateproject_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\DuplicateProjectPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_duplicateproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\DuplicateProjectPage($projectid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
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
            $rparams_ar['action'] = 'duplicated_project';
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

//The comment functions
function bigfathom_core_mng_project_communications_form() 
{
    return drupal_get_form('bigfathom_core_mng_project_communications_form_builder');
}
function bigfathom_core_mng_project_communications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\ManageProjectCommentsPage($projectid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewproject_communication_form() 
{
    return drupal_get_form('bigfathom_core_viewproject_communication_form_builder');
}
function bigfathom_core_addproject_communication_form() 
{
    return drupal_get_form('bigfathom_core_addproject_communication_form_builder');
}
function bigfathom_core_editproject_communication_form() 
{
    return drupal_get_form('bigfathom_core_editproject_communication_form_builder');
}
function bigfathom_core_deleteproject_communication_form() 
{
    return drupal_get_form('bigfathom_core_deleteproject_communication_form_builder');
}

function bigfathom_core_params_project_communication_page($form, &$form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    $comid = NULL;
    $parent_comid = NULL;
    $projectid = NULL;
    $parentkey = array();
    $foundkeys = 0;
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
    if(isset($myvalues['projectid']))
    {
        $projectid = $myvalues['projectid'];
    } else {
        if(isset($_GET['projectid']))
        {
            $projectid = $_GET['projectid'];
        }
    }
    if(!empty($projectid))
    {
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid projectid url parameter: that value MUST be numeric!");
        }
        $myvalues['projectid'] = $projectid;
        $parentkey['projectid'] = $projectid;
        $foundkeys++;
    }
    if(isset($myvalues['urls_arr']))
    {
        $urls_arr = $myvalues['urls_arr'];
    } else {
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
    }
    if($foundkeys == 0)
    {
        throw new \Exception("Did NOT find any key URL parameters!");
    }
    $page_args = bigfathom_core_get_menu_page_arguments();
    $returnURL = $page_args['menu_base'];
    $urls_arr = array('return'=>$returnURL);
    $bundle['urls_arr'] = $urls_arr;
    $bundle['parentkey'] = $parentkey;
    $bundle['myvalues'] = $myvalues;
    return $bundle;
}

function bigfathom_core_addproject_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_project_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddProjectCommentPage($bundle['parentkey'], $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addproject_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_project_communication_page($form, $form_state);
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
        $oPI = new \bigfathom\AddProjectCommentPage($bundle['parentkey'], $urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addproject_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_project_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $bundle['myvalues'];
        $oPI = new \bigfathom\AddProjectCommentPage($bundle['parentkey'], $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'added_project_communication',
                'projectid' => $myvalues['projectid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewproject_communication_form_builder($form, &$form_state)
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

        $oPI = new \bigfathom\ViewProjectCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteproject_communication_form_builder($form, &$form_state)
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

        $oPI = new \bigfathom\DeleteProjectCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deleteproject_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_project_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        $oPI = new \bigfathom\DeleteProjectCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'deleted',
                'projectid' => $myvalues['projectid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_editproject_communication_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\EditProjectCommentPage($comid,$urls_arr);
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

function bigfathom_core_editproject_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();
        $page_args = bigfathom_core_get_menu_page_arguments();
        $urls_arr = array('return'=>$page_args['menu_base']);
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
        
        $oPI = new \bigfathom\EditProjectCommentPage($comid,$urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_editproject_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_project_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        $oPI = new \bigfathom\EditProjectCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'edited',
                'projectid' => $myvalues['projectid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}


//Publish project detail from here on
function bigfathom_core_mng_publishprojectinfo_form() 
{
    return drupal_get_form('bigfathom_core_mng_publishprojectinfo_form_builder');
}
function bigfathom_core_mng_publishprojectinfo_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\ManageProjectPublishingPage($projectid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_publishprojectinfo_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\EditProjectInfoForPublishPage($projectid,$urls_arr);
        $form = [];
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_publishprojectinfo_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['projectid']))
    {
        throw new \Exception("Missing required projectid url parameter!");
    }
    $projectid = $_GET['projectid'];
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
    $oPI = new \bigfathom\EditProjectInfoForPublishPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_publishprojectinfo_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['projectid'];
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
        $oPI = new \bigfathom\EditProjectInfoForPublishPage($projectid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
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
            $rparams_ar['action'] = 'locked_all_estimates';
            drupal_goto($redirect_url,
                array(
                  'query' => $rparams_ar,
                    )
                );
        } else {
                $form_state['redirect'] = array(
                        'bigfathom/projects/manage',
                        array(
                          'query' => array(
                                'action' => 'published',
                          ),
                        ),
                  );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewpublishedprojectinfo_form_builder($form, &$form_state)
{
    if(!isset($_GET['pubid']))
    {
        throw new \Exception("Missing required pubid url parameter!");
    }
    $pubid = $_GET['pubid'];
    if(!is_numeric($pubid))
    {
        throw new \Exception("Invalid pubid url parameter: that value MUST be numeric!");
    }

    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\ViewPublishedProjectInfoPage($pubid, $urls_arr);
    $form = [];
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletepublishedprojectinfo_form_builder($form, &$form_state)
{
    if(!isset($_GET['pubid']))
    {
        throw new \Exception("Missing required pubid url parameter!");
    }
    $projectid = $_GET['pubid'];
    if(!is_numeric($projectid))
    {
        throw new \Exception("Invalid pubid url parameter: that value MUST be numeric!");
    }
    
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\DeletePublishedProjectInfoPage($projectid, $urls_arr);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$projectid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletepublishedprojectinfo_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['pubid']))
    {
        throw new \Exception("Missing required pubid url parameter!");
    }
    $projectid = $_GET['pubid'];
    if(!is_numeric($projectid))
    {
        throw new \Exception("Invalid pubid url parameter: that value MUST be numeric!");
    }
    
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\DeletePublishedProjectInfoPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletepublishedprojectinfo_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['pubid']))
        {
            throw new \Exception("Missing required pubid url parameter!");
        }
        $pubid = $_GET['pubid'];
        if(!is_numeric($pubid))
        {
            throw new \Exception("Invalid pubid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\DeletePublishedProjectInfoPage($pubid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/projects/managepublishinfo',
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
