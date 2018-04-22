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

module_load_include('php','bigfathom_template_library','form/ConfigureModulePage');


function bigfathom_template_library_configure_form() 
{
    return drupal_get_form('bigfathom_template_library_configure_form_builder');
}
function bigfathom_template_library_configure_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = array('return'=>'admin/modules');
        
        $oPI = new \bigfathom_template_library\ConfigureModulePage();
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);

    } catch (\Exception $ex) {
        throw $ex;
    }
}
function bigfathom_template_library_configure_form_builder_validate($form, &$form_state) 
{
    try
    {
        $urls_arr = array('return'=>'admin/modules');
        
        $oPI = new \bigfathom_template_library\ConfigureModulePage();
        return $oPI->looksValid($form, $form_state['values']);
    } catch (\Exception $ex) {
        throw $ex;
    }
        
}
function bigfathom_template_library_configure_form_builder_submit($form, &$form_state) 
{
    try
    {
        $urls_arr = array('return'=>'admin/modules');
        
        $oPI = new \bigfathom_template_library\ConfigureModulePage();
        $oPI->updateDatabase($form, $form_state['values']);
        //Success!
        $form_state['redirect'] = array(
            $page_args['menu_base'],
            array(
              'query' => array(
                'action' => 'configured',
              ),
            ),
          );
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_template_library_reload_form() 
{
    return drupal_get_form('bigfathom_template_library_reload_form_builder');
}
function bigfathom_template_library_reload_form_builder($form, &$form_state)
{
    try
    {
        $urls_arr = array('return'=>'admin/modules');
        
        $oPI = new \bigfathom_template_library\ConfigureModulePage("RELOAD");
        $form = array();
        $disabled = FALSE;
        $myvalues = $oPI->getFieldValues();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);

    } catch (\Exception $ex) {
        throw $ex;
    }
}
