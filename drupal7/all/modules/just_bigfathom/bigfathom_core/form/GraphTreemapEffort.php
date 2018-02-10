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

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class GraphTreemapEffort extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid = NULL;
    protected $m_urls_arr = NULL;
    
    public function __construct($projectid, $urls_arr=NULL)
    {
        if($projectid == NULL)
        {
            throw new \Exception("Cannot create graph without specifying a project!");
        }
        $this->m_projectid = $projectid;
        if($urls_arr == NULL)
        {
            $urls_arr = array();
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
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
        
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/js/d3.v4.js");
            drupal_add_js("$base_url/$theme_path/js/spinner.js");
            drupal_add_js("$base_url/$module_path/visualization/util_svg.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/visualization/util_nodes.js");
            drupal_add_js("$base_url/$module_path/visualization/util_treemap.js");
            
            global $base_url;
            $form["formarea1"] = array(
                '#prefix' => "\n<section>\n",
                '#suffix' => "\n</section>\n",
            );
                
            $form["formarea1"]['dashboard'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="dashboard">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $sAddButtonMarkup = "<button id='btn_addsprint' type='button'>Add Sprint</button>";
            $checkbox_markup = '<label><input type="checkbox" id="chk_enablezoompan" value="tasks" checked="checked" >Enable zoom &amp; pan</label>';
            $sResetScaleButtonMarkup = "<button id='btn_reset_scale' type='button'>Reset Display</button>";
            $sSaveAllEditsButtonMarkup = "<button id='btn_save_all_data' type='button'>Save All Edits</button>";
            $form["formarea1"]['dashboard']['controls'] = array('#type' => 'item',
                     '#markup' => ""
                . "<span class='inlinecontrols' style='white-space:nowrap; display:inline'>"
                . "<label for='sprintfilter'>Sprints to Show Here</label><select name='sprintfilter'>"
                . "<option value='1'>Most Recent in Unfinished Status</option>"
                . "<option value='2'>Most Recent in Any Status</option>"
                . "</select>"
                . "$sAddButtonMarkup"
                . "$checkbox_markup"
                . "$sResetScaleButtonMarkup"
                . "$sSaveAllEditsButtonMarkup"
                . "</span>"
                );            
                
            //Map the library action name to our element IDs
            $field_map = array(
                'sprint_item_name'=>'new_sprint_item_name',
                'sprint_item_purpose'=>'new_sprint_item_purpose',
                'sprint_item_type'=>'new_sprint_item_type'
                );
            $popup_map = array(
                'context'=>'sprint',
                'dlg_container_id'=>'dlg_create_sprint_item_container',
                'dlg_form_id'=>'dlg_create_sprint_item_form',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'save_dlg_btn_id'=>'btn_create_sprint_item',
                'close_dlg_btn_id'=>'btn_close',
                'field_ids_map'=>$field_map,
                );
            $popups = array('add_sprint' => $popup_map);
            $action_map = array(
                'popups'=>$popups,
                'reset_scale_btn_id'=>'btn_reset_scale',
                'save_all_data_btn_id'=>'btn_save_all_data',
                'enablezoompan_chk_id'=>'chk_enablezoompan'
                );

            
            //Draw our canvas
            $form['mainvisual'] = array(
                '#type' => 'item', 
                '#prefix' => '<div style="width:100%; height:100vh" class="visualizationbox" id="visualization1" /">',
            );

            //Run our script now
            $json_action_map = json_encode($action_map);
            $json_field_map = json_encode($field_map);
            $form["myscripts"] = array('#type' => 'item',
                     '#markup' => ""
                        . "<script>"
                        . "\nvar my_action_map = $json_action_map"
                        . "\nvar my_field_map = $json_field_map"
                        . "\nvar projectid = {$this->m_projectid}"
                        . "\nvar manager = bigfathom_util.treemap.createEverything('visualization1', my_action_map, my_field_map, projectid);\n"
                        . "</script>"
                        . "");
            

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
