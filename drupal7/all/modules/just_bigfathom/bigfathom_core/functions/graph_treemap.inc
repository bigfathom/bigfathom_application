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

module_load_include('php','bigfathom_core','form/GraphTreemapEffort');

function bigfathom_core_graph_treemap_effort_form() 
{
    return drupal_get_form('bigfathom_core_graph_treemap_effort_form_builder');
}
function bigfathom_core_graph_treemap_effort_form_builder($form, &$form_state)
{
    try
    {
        if(isset($_GET['projectid']))
        {
            $selected_projectid = trim($_GET['projectid']);
        } else {
            $oContext = \bigfathom\Context::getInstance();
            $selected_projectid = $oContext->getSelectedProjectID();
        }
        $oPI = new \bigfathom\GraphTreemapEffort($selected_projectid);
        $form = array();
        $disabled = false;
        $myvalues = isset($form_state['values']) ? $form_state['values'] : array();
        return $oPI->getForm($form, $form_state, $disabled, $myvalues);
    } catch (\Exception $ex) {
        throw $ex;
    }
}
