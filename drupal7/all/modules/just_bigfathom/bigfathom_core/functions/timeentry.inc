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

module_load_include('php','bigfathom_core','form/TimeEntryPage');


function bigfathom_core_timeentry_form($myargs) 
{
    return drupal_get_form('bigfathom_core_timeentry_form_builder');
}
function bigfathom_core_timeentry_form_builder($form, &$form_state)
{
    try
    {
        $oPI = new \bigfathom\TimeEntryPage();
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        if(isset($_GET['projectid']))
        {
            $selected_projectid = trim($_GET['projectid']);
            $myvalues['selected_projectid'] = $selected_projectid;
            $oContext = \bigfathom\Context::getInstance();
            $oContext->setSelectedProject($selected_projectid);
        }
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}

