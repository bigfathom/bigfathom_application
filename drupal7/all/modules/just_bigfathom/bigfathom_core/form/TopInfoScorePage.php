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
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoScorePage extends \bigfathom\ASimpleFormPage
{

    public function __construct($projectid=NULL,$urls_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        if($projectid == NULL)
        {
            $projectid = $this->m_oContext->getSelectedProjectID();
        }
        if(empty($projectid))
        {
            throw new \Exception("No project has been selected!");
        }
        $this->m_projectid = $projectid;
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }
        $this->m_urls_arr = $urls_arr;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;
                
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => 'Evaluating actual results is essential for continuous improvement.');

            $form["data_entry_area1"]['table_container']['underconstruction'] = array('#type' => 'item',
                     '#markup' => 'This page is under construction.');
            
            $loaded = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the ProjectForecaster class');
            }
            $oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_projectid);  
            $projectforecast = $oProjectForecaster->getDetail();
            
            $root_goalid = $projectforecast['main_project_detail']['metadata']['root_goalid'];
            $root_nodedetail = $projectforecast['main_project_detail']['workitems'][$root_goalid];
            $root_forecast = $root_nodedetail['forecast'];
            $root_otsp_value = $root_forecast['local']['otsp'];
            $root_otsp_reason = $root_forecast['local']['logic'];
            drupal_set_message("LOOK otsp value=" . $root_otsp_value);
            drupal_set_message("LOOK otsp reason=" . print_r($root_otsp_reason,TRUE));

            DebugHelper::debugPrintNeatly($projectforecast, FALSE, "DEBUGGING Project forecast information...");
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
