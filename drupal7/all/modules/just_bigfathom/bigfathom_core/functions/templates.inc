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

module_load_include('php','bigfathom_core','form/ManageTemplatesPage');
module_load_include('php','bigfathom_core','form/EditTemplatePage');
module_load_include('php','bigfathom_core','form/ViewTemplatePage');
module_load_include('php','bigfathom_core','form/DeleteTemplatePage');
module_load_include('php','bigfathom_core','form/ImportTemplatePage');

module_load_include('php','bigfathom_core','form/CreateProjectFromTemplatePage');
module_load_include('php','bigfathom_core','form/CreateTemplateFromProjectPage');
//module_load_include('php','bigfathom_core','form/CreateTemplateFromWorkitemPage');

//module_load_include('php','bigfathom_core','form/ManageTemplateCommentsPage');
//module_load_include('php','bigfathom_core','form/ViewTemplateCommentPage');
//module_load_include('php','bigfathom_core','form/AddTemplateCommentPage');
//module_load_include('php','bigfathom_core','form/EditTemplateCommentPage');
//module_load_include('php','bigfathom_core','form/DeleteTemplateCommentPage');


//Template detail from here on
function bigfathom_core_mng_templates_form() 
{
    return drupal_get_form('bigfathom_core_mng_templates_form_builder');
}
function bigfathom_core_mng_templates_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\ManageTemplatesPage();
        $form = array();
        $disabled = FALSE;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($_GET['templateid']))
        {
            $myvalues['selected_templateid'] = $_GET['templateid'];
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_mng_templates_form_builder_validate($form, &$form_state) 
{
    return TRUE;
}
function bigfathom_core_mng_templates_form_builder_submit($form, &$form_state) 
{
    try
    {
        $myvalues = $form_state['values'];
        if(isset($myvalues['selected_templateid']) && $myvalues['selected_templateid'] != NULL)
        {
            $selected_templateid = $myvalues['selected_templateid'];
            //$_SESSION['selected_templateid'] = $selected_templateid;
            $oContext = \bigfathom\Context::getInstance();
            $oContext->setSelectedTemplate($selected_templateid);
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_viewtemplate_form() 
{
    return drupal_get_form('bigfathom_core_viewtemplate_form_builder');
}
function bigfathom_core_addtemplatemain_form() 
{
    return drupal_get_form('bigfathom_core_addtemplatemain_form_builder');
}
function bigfathom_core_addtemplate_form() 
{
    return drupal_get_form('bigfathom_core_addtemplate_form_builder');
}
function bigfathom_core_edittemplate_form() 
{
    return drupal_get_form('bigfathom_core_edittemplate_form_builder');
}
function bigfathom_core_deletetemplate_form() 
{
    return drupal_get_form('bigfathom_core_deletetemplate_form_builder');
}

function bigfathom_core_publishtemplateinfo_form() 
{
    return drupal_get_form('bigfathom_core_publishtemplateinfo_form_builder');
}

function bigfathom_core_import_template_form() 
{
    return drupal_get_form('bigfathom_core_import_template_form_builder');
}
function bigfathom_core_addtemplatefromfile_form() 
{
    return drupal_get_form('bigfathom_core_addtemplatefromfile_form_builder');
}

function bigfathom_core_create_projectfromtemplate_form() 
{
    return drupal_get_form('bigfathom_core_create_projectfromtemplate_form_builder');
}

function bigfathom_core_create_templatefromproject_form() 
{
    return drupal_get_form('bigfathom_core_create_templatefromproject_form_builder');
}
function bigfathom_core_create_templatefromworkitem_form() 
{
    return drupal_get_form('bigfathom_core_create_templatefromworkitem_form_builder');
}
function bigfathom_core_convert_templateintoproject_form() 
{
    return drupal_get_form('bigfathom_core_convert_templateintoproject_form_builder');
}

function bigfathom_core_import_template_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_templateid']))
    {
        $parent_templateid = $_GET['parent_templateid'];
    } else {
        $parent_templateid = NULL;
    }
    $urls_arr = array();
    $urls_arr['return'] = 'bigfathom/sitemanage/templates';
    $oPI = new \bigfathom\ImportTemplatePage($urls_arr,$parent_templateid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_import_template_form_builder_validate($form, &$form_state) 
{
    $urls_arr = array();
    $urls_arr['return'] = 'bigfathom/sitemanage/templates';
    
    //////////////////////////////////
    
    module_load_include('php','bigfathom_core','core/Context');
    $oContext = \bigfathom\Context::getInstance();
    $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

    //$page_args = bigfathom_core_get_menu_page_arguments();
    //$bundle = bigfathom_core_params_workitem_communication_page($form, $form_state);
    //$urls_arr = $bundle['urls_arr'];
    //$myvalues = $form_state['values'];

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
    //$oPI = new \bigfathom\AddWorkitemCommentPage($bundle['parentkey'], $urls_arr);
    //return $oPI->looksValid($form, $myvalues);

    ///////////////////////////////////
    
    $oPI = new \bigfathom\ImportTemplatePage($urls_arr);
    return $oPI->looksValidFormState($form, $form_state);
    
}
function bigfathom_core_import_template_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/templates';
        $oPI = new \bigfathom\ImportTemplatePage($urls_arr);

        $oPI->updateDatabaseFormState($form, $form_state);
        
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
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

function bigfathom_core_addtemplatefromfile_form_builder($form, &$form_state)
{
    if(isset($_GET['parent_templateid']))
    {
        $parent_templateid = $_GET['parent_templateid'];
    } else {
        $parent_templateid = NULL;
    }
    $urls_arr = array();
    $urls_arr['return'] = 'bigfathom/sitemanage/templates';
    $oPI = new \bigfathom\AddTemplateFromFilePage($urls_arr,$parent_templateid);
    $form = array();
    $disabled = false;
    $myvalues = $oPI->getFieldValues();
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_addtemplatefromfile_form_builder_validate($form, &$form_state) 
{
    $urls_arr = array();
    $urls_arr['return'] = 'bigfathom/sitemanage/templates';
    $myvalues = $form_state['values'];
    $parent_templateid = $myvalues['parent_templateid'];
    $oPI = new \bigfathom\AddTemplateFromFilePage($urls_arr,$parent_templateid);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_addtemplatefromfile_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/templates';
        $myvalues = $form_state['values'];
        $parent_templateid = $myvalues['parent_templateid'];
        $oPI = new \bigfathom\AddTemplateFromFilePage($urls_arr,$parent_templateid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
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

function bigfathom_core_convert_templateintoproject_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['parent_templateid']))
        {
            $parent_templateid = $_GET['parent_templateid'];
        } else {
            $parent_templateid = NULL;
        }
        if(isset($_GET['this_templateid']))
        {
            $this_templateid = $_GET['this_templateid'];
        } else {
            $this_templateid = NULL;
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
            $urls_arr['return'] = 'bigfathom/templates';
        }
        $oPI = new \bigfathom\ConvertTemplate2GoalPage($urls_arr,$parent_templateid,$this_templateid);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_convert_templateintoproject_form_builder_validate($form, &$form_state) 
{
    try
    {
        $urls_arr = array();

        $myvalues = $form_state['values'];
        $parent_templateid = $myvalues['parent_templateid'];
        $this_templateid = $myvalues['this_templateid'];

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
        $oPI = new \bigfathom\ConvertTemplate2GoalPage($urls_arr,$parent_templateid,$this_templateid);
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_convert_templateintoproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array();
        
        $myvalues = $form_state['values'];
        $parent_templateid = $myvalues['parent_templateid'];
        $this_templateid = $myvalues['this_templateid'];
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
        $oPI = new \bigfathom\ConvertTemplate2GoalPage($urls_arr,$parent_templateid,$this_templateid);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $urls_arr['return'],
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

//=====

function bigfathom_core_viewtemplate_form_builder($form, &$form_state)
{
    if(!isset($_GET['templateid']))
    {
        throw new \Exception("Missing required templateid url parameter!");
    }
    $templateid = $_GET['templateid'];
    if(!is_numeric($templateid))
    {
        throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
    }

    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\ViewTemplatePage($templateid, $urls_arr);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$templateid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletetemplate_form_builder($form, &$form_state)
{
    if(!isset($_GET['templateid']))
    {
        throw new \Exception("Missing required templateid url parameter!");
    }
    $templateid = $_GET['templateid'];
    if(!is_numeric($templateid))
    {
        throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
    }
    
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\DeleteTemplatePage($templateid, $urls_arr);
    $form = array();
    $disabled = FALSE;
    $myvalues = $oPI->getFieldValues();//$templateid);
    return $oPI->getForm($form, $form_state, $disabled, $myvalues);
}
function bigfathom_core_deletetemplate_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['templateid']))
    {
        throw new \Exception("Missing required templateid url parameter!");
    }
    $templateid = $_GET['templateid'];
    if(!is_numeric($templateid))
    {
        throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
    }
    
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\DeleteTemplatePage($templateid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_deletetemplate_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['templateid']))
        {
            throw new \Exception("Missing required templateid url parameter!");
        }
        $templateid = $_GET['templateid'];
        if(!is_numeric($templateid))
        {
            throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\DeleteTemplatePage($templateid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/templates',
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

function bigfathom_core_edittemplate_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['templateid']))
        {
            throw new \Exception("Missing required templateid url parameter!");
        }
        $templateid = $_GET['templateid'];
        if(!is_numeric($templateid))
        {
            throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\EditTemplatePage($templateid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();//$templateid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edittemplate_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['templateid']))
    {
        throw new \Exception("Missing required templateid url parameter!");
    }
    $templateid = $_GET['templateid'];
    if(!is_numeric($templateid))
    {
        throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
    }
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\EditTemplatePage($templateid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_edittemplate_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['templateid']))
        {
            throw new \Exception("Missing required templateid url parameter!");
        }
        $templateid = $_GET['templateid'];
        if(!is_numeric($templateid))
        {
            throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\EditTemplatePage($templateid, $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            'bigfathom/sitemanage/templates',
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

//The project from template functions
function bigfathom_core_create_projectfromtemplate_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['source_templateid']))
        {
            throw new \Exception("Missing required source_templateid url parameter!");
        }
        $projectid = $_GET['source_templateid'];
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid source_templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateProjectFromTemplatePage($projectid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_create_projectfromtemplate_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['source_templateid']))
    {
        throw new \Exception("Missing required source_templateid url parameter!");
    }
    $projectid = $_GET['source_templateid'];
    if(!is_numeric($projectid))
    {
        throw new \Exception("Invalid source_templateid url parameter: that value MUST be numeric!");
    }
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\CreateProjectFromTemplatePage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_create_projectfromtemplate_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['source_templateid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $source_templateid = $_GET['source_templateid'];
        if(!is_numeric($source_templateid))
        {
            throw new \Exception("Invalid source_templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateProjectFromTemplatePage($source_templateid, $urls_arr);
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
            $rparams_ar['action'] = 'created_template';
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

//The template creation functions
function bigfathom_core_create_templatefromproject_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['source_projectid']))
        {
            throw new \Exception("Missing required source_projectid url parameter!");
        }
        $projectid = $_GET['source_projectid'];
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid source_projectid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateTemplateFromProjectPage($projectid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();//$projectid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_create_templatefromproject_form_builder_validate($form, &$form_state) 
{
    if(!isset($_GET['source_projectid']))
    {
        throw new \Exception("Missing required source_projectid url parameter!");
    }
    $projectid = $_GET['source_projectid'];
    if(!is_numeric($projectid))
    {
        throw new \Exception("Invalid source_projectid url parameter: that value MUST be numeric!");
    }
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\CreateTemplateFromProjectPage($projectid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_create_templatefromproject_form_builder_submit($form, &$form_state) 
{
    try
    {
        if(!isset($_GET['source_projectid']))
        {
            throw new \Exception("Missing required projectid url parameter!");
        }
        $projectid = $_GET['source_projectid'];
        if(!is_numeric($projectid))
        {
            throw new \Exception("Invalid source_projectid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateTemplateFromProjectPage($projectid, $urls_arr);
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
            $rparams_ar['action'] = 'created_template';
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

function bigfathom_core_create_templatefromworkitem_form_builder($form, &$form_state)
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
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateTemplateFromWorkitemPage($workitemid,$urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = $oPI->getFieldValues();//$workitemid);
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_create_templatefromworkitem_form_builder_validate($form, &$form_state) 
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
    if(!isset($_GET['return']))
    {
        $urls_arr = [];
    } else {
        $urls_arr['return'] = $_GET['return'];
    }
    $oPI = new \bigfathom\CreateTemplateFromWorkitemPage($workitemid, $urls_arr);
    return $oPI->looksValid($form, $form_state['values']);
}
function bigfathom_core_create_templatefromworkitem_form_builder_submit($form, &$form_state) 
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
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\CreateTemplateFromWorkitemPage($workitemid, $urls_arr);
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
            $rparams_ar['action'] = 'created_template';
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
function bigfathom_core_mng_template_communications_form() 
{
    return drupal_get_form('bigfathom_core_mng_template_communications_form_builder');
}
function bigfathom_core_mng_template_communications_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        
        if(!isset($_GET['templateid']))
        {
            throw new \Exception("Missing required templateid url parameter!");
        }
        $templateid = $_GET['templateid'];
        if(!is_numeric($templateid))
        {
            throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
        }
        if(!isset($_GET['return']))
        {
            $urls_arr = [];
        } else {
            $urls_arr['return'] = $_GET['return'];
        }
        $oPI = new \bigfathom\ManageTemplateCommentsPage($templateid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_viewtemplate_communication_form() 
{
    return drupal_get_form('bigfathom_core_viewtemplate_communication_form_builder');
}
function bigfathom_core_addtemplate_communication_form() 
{
    return drupal_get_form('bigfathom_core_addtemplate_communication_form_builder');
}
function bigfathom_core_edittemplate_communication_form() 
{
    return drupal_get_form('bigfathom_core_edittemplate_communication_form_builder');
}
function bigfathom_core_deletetemplate_communication_form() 
{
    return drupal_get_form('bigfathom_core_deletetemplate_communication_form_builder');
}

function bigfathom_core_params_template_communication_page($form, &$form_state)
{
    $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
    $comid = NULL;
    $parent_comid = NULL;
    $templateid = NULL;
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
    if(isset($myvalues['templateid']))
    {
        $templateid = $myvalues['templateid'];
    } else {
        if(isset($_GET['templateid']))
        {
            $templateid = $_GET['templateid'];
        }
    }
    if(!empty($templateid))
    {
        if(!is_numeric($templateid))
        {
            throw new \Exception("Invalid templateid url parameter: that value MUST be numeric!");
        }
        $myvalues['templateid'] = $templateid;
        $parentkey['templateid'] = $templateid;
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

function bigfathom_core_addtemplate_communication_form_builder($form, &$form_state)
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_template_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $oPI = new \bigfathom\AddTemplateCommentPage($bundle['parentkey'], $urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addtemplate_communication_form_builder_validate($form, &$form_state) 
{
    try
    {
        module_load_include('php','bigfathom_core','core/Context');
        $oContext = \bigfathom\Context::getInstance();
        $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();

        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_template_communication_page($form, $form_state);
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
        $oPI = new \bigfathom\AddTemplateCommentPage($bundle['parentkey'], $urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_addtemplate_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_template_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $bundle['myvalues'];
        $oPI = new \bigfathom\AddTemplateCommentPage($bundle['parentkey'], $urls_arr);
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'added',
                'templateid' => $myvalues['templateid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_viewtemplate_communication_form_builder($form, &$form_state)
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

        $oPI = new \bigfathom\ViewTemplateCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletetemplate_communication_form_builder($form, &$form_state)
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

        $oPI = new \bigfathom\DeleteTemplateCommentPage($comid,$urls_arr);
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_deletetemplate_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_template_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        $oPI = new \bigfathom\DeleteTemplateCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'deleted',
                'templateid' => $myvalues['templateid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_core_edittemplate_communication_form_builder($form, &$form_state)
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
        $oPI = new \bigfathom\EditTemplateCommentPage($comid,$urls_arr);
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

function bigfathom_core_edittemplate_communication_form_builder_validate($form, &$form_state) 
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
        
        $oPI = new \bigfathom\EditTemplateCommentPage($comid,$urls_arr);
        return $oPI->looksValid($form, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_edittemplate_communication_form_builder_submit($form, &$form_state) 
{
    try
    {
        $page_args = bigfathom_core_get_menu_page_arguments();
        $bundle = bigfathom_core_params_template_communication_page($form, $form_state);
        $urls_arr = $bundle['urls_arr'];
        $myvalues = $form_state['values'];
        $comid = $myvalues['id'];
        
        $oPI = new \bigfathom\EditTemplateCommentPage($comid, $urls_arr);
        $oPI->updateDatabase($form, $myvalues);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'edited',
                'templateid' => $myvalues['templateid'],
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}


//Publish template detail from here on
function bigfathom_core_mng_publishtemplateinfo_form() 
{
    return drupal_get_form('bigfathom_core_mng_publishtemplateinfo_form_builder');
}
function bigfathom_core_mng_publishtemplateinfo_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = [];
        if(!isset($_GET['templateid']))
        {
            $templateid = NULL;
        } else {
            $templateid = $_GET['templateid'];
        }
        if(isset($_GET['return']))
        {
            $urls_arr['return'] = $_GET['return'];
            if(isset($_GET['rparams']))
            {
                $urls_arr['rparams'] = $_GET['rparams'];
            }
        }
        $oPI = new \bigfathom\ManageTemplatePublishingPage($templateid, $urls_arr);
        $form = array();
        $disabled = false;
        $myvalues = array();    //No values for this one.
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

