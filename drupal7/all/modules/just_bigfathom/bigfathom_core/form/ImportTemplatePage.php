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

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/TemplatePageHelper.php';

/**
 * Add a template
 *
 * @author Frank Font
 */
class ImportTemplatePage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oWriteHelper = NULL;
    protected $m_parent_projectid = NULL;
   
    public function __construct($urls_override_arr=NULL,$parent_projectid=NULL,$root_goalid=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $parent_projectid;
        $this->m_root_goalid = $root_goalid;
        
        $this->m_oPageHelper = new \bigfathom\TemplatePageHelper($urls_override_arr,NULL,$parent_projectid,NULL,$root_goalid);
        
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to add a template!!!");
            throw new \Exception("Illegal access attempt!");
        }
        
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues();
    }
    
    function looksValidFormState($form, &$form_state)
    {
        if(empty($form_state))
        {
            throw new \Exception("Missing required form_state array!");
        }
        $myvalues = $form_state['values'];
        $good = $this->m_oPageHelper->formIsValidImport($form, $myvalues, 'A');

        if($good)
        {
            $fileblobs_ar = $this->m_oWriteHelper->getFileBlobsFromArray($myvalues, 'attachments');
            if($myvalues['source_type'] == 'local' && empty($fileblobs_ar))
            {
                form_set_error('edit-newfile1',"Expected an upload file!");
                $good = FALSE;
            }
            if($good)
            {
                $file_blob = $fileblobs_ar[0]['file_blob'];
                try
                {
                    $form_state['values']['parsed_file'] = \bigfathom\UtilityProjectTemplate::convertProjectTemplateTabText2Bundle($file_blob);
                } catch (\Exception $ex) {
                    form_set_error('edit-newfile1',"File parsing trouble:" . $ex->getMessage());
                    $good = FALSE;
                }
            }
        }
        
        return $good;
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabaseFormState($form, &$form_state)
    {
        try
        {
            if(empty($form_state))
            {
                throw new \Exception("Missing required form_state array!");
            }
            $myvalues = $form_state['values'];

            $raw_import_data = trim($myvalues['raw_import_data']);
            if(empty($raw_import_data))
            {
                throw new \Exception("Missing raw_import_data content!");
            }
            $imported_bundle = $myvalues['parsed_file'];
            $resultbundle = $this->m_oWriteHelper->createTemplateFromImport($imported_bundle, $myvalues);
            drupal_set_message($resultbundle['message']);
            
            $bundle = [];
            $bundle['redirect'] = $this->m_urls_arr['return'];
            if(array_key_exists('rparams', $this->m_urls_arr))
            {
                $bundle['rparams'] = $this->m_urls_arr['rparams'];
            }
            return $bundle;
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues_override)
    {
        if(!isset($form_state['values']))
        {
            $myvalues = array();
        } else {
            $myvalues = $form_state['values'];
        }
        if(!empty($this->m_parent_projectid) && (!isset($myvalues['parent_projectid']) || $myvalues['parent_projectid'] == NULL))
        {
            $myvalues['parent_projectid'] = $this->m_parent_projectid;
        }
        if(!empty($this->m_root_goalid) && (!isset($myvalues['root_goalid']) || $myvalues['root_goalid'] == NULL))
        {
            $myvalues['root_goalid'] = $this->m_root_goalid;
        }
        $html_classname_overrides = array();
        $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        $html_classname_overrides['container-inline'] = 'container-inline';
        $html_classname_overrides['action-button'] = 'action-button';
        $new_form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

 
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $new_form;
    }
}
