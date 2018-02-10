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
require_once 'helper/QuadrantDisplayPageHelper.php';


/**
 * Quadrant display page
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class QuadrantDisplayPage extends \bigfathom\ASimpleFormPage
{
    protected $m_projectid          = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_oPageHelper        = NULL;
    protected $m_oSnippetHelper     = NULL;

    public function __construct($projectid, $urls_arr=NULL)
    {
        if($projectid == NULL)
        {
            throw new \Exception("Cannot map a goal without specifying a project!");
        }
        $this->m_projectid = $projectid;
        if($urls_arr == NULL)
        {
            $urls_arr = array();
        }
        $this->m_urls_arr = $urls_arr;
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $this->m_oPageHelper = new \bigfathom\QuadrantDisplayPageHelper($this->m_urls_arr);
        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     * 
     * 
     *         <script src="http://d3js.org/d3.v3.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.3.min.js"></script>
        <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">

     * 
     * 
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $base_url;
                        
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/js/d3.v3.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_svg.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/visualization/util_nodes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes_lib.js");
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_popup.js");
            
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_env_quadrant.js");
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_timequadrant_data.js");
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_timequadrant.js");

            $form["formarea1"] = array(
                '#prefix' => "\n<section>\n",
                '#suffix' => "\n</section>\n",
            );
            //global $base_url;

//$snippet_popup = 'HELLO POPUP';    //$this->m_oSnippetHelper->getHtmlSnippet("popup_timequadrant");            
//$snippet_button = 'HELLO BUTTTON';   //$this->m_oSnippetHelper->getHtmlSnippet("button_createtimequadrant");            
//$snippet_handle_button = 'HELLO HANDLER';    //$this->m_oSnippetHelper->getJavascriptSnippet("handle_button_createtimequadrant");            
/*            $snippet_popup_divs = $this->m_oSnippetHelper->getHtmlSnippet("popup_timequadrant");            
            $form["formarea1"]['popupdefs'] = array('#type' => 'item'
                    , '#markup' => $snippet_popup_divs);
*/
            //Map the library action name to our element IDs
            $field_map = array(
                'brainstom_item_name'=>'new_brainstom_item_name',
                'brainstom_item_purpose'=>'new_brainstom_item_purpose',
                'brainstom_item_type'=>'new_brainstom_item_type'
                );
            $popup_map = array(
                'context'=>'timequadrant',
                'dlg_container_id'=>'dlg_create_timequadrant_item_container',
                'dlg_form_id'=>'dlg_create_timequadrant_item_form',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'save_dlg_btn_id'=>'btn_create_timequadrant_item',
                'close_dlg_btn_id'=>'btn_close',
                'field_ids_map'=>$field_map,
                );
            $popups = array('new_timequadrant_topic' => $popup_map);
            $action_map = array(
                'popups'=>$popups,
                'save_all_data_btn_id'=>'btn_save_all_data'
                );
            
            $form["formarea1"]['dashboard'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="dashboard">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $checkbox_zoompan_markup = '<label><input type="checkbox" id="chk_enablezoompan" value="tasks" checked="checked" >Enable zoom &amp; pan</label>';
            $checkbox_showgoals_markup = '<label><input type="checkbox" id="chk_showgoals" value="showgoals" checked="checked" >Show goals</label>';
            $checkbox_showtasks_markup = '<label><input type="checkbox" id="chk_showtasks" value="showtasks" checked="checked" >Show tasks</label>';
            $sResetScaleButtonMarkup = "<button id='btn_reset_scale' type='button'>Reset Display</button>";
            $form["formarea1"]['dashboard']['controls'] = array('#type' => 'item',
                     '#markup' => ""
                . "<span class='inlinecontrols' style='white-space:nowrap; display:inline'>"
                . "$checkbox_zoompan_markup"
                . "$sResetScaleButtonMarkup"
                . "$checkbox_showgoals_markup"
                . "$checkbox_showtasks_markup"
                . "</span>"
                );            
            
            
            //Declare the buttons of dashboard
            $sButtonMarkup = "<button id='" . $action_map['popups']['new_timequadrant_topic']['show_dlg_btn_id'] . "' type='button'>Create New Item</button>";
            $form["formarea1"]['dashboard']['controls']['additem'] = array('#type' => 'item'
                    , '#markup' => $sButtonMarkup);
            $sButtonMarkup = "<button id='" . $action_map['save_all_data_btn_id'] . "' type='button'>Add Goals and Tasks to Project</button>";
            $form["formarea1"]['dashboard']['controls']['saveall'] = array('#type' => 'item'
                    , '#markup' => $sButtonMarkup);
            
            //Draw our canvas
            $form['mainvisual'] = array(
                '#type' => 'item', 
                '#prefix' => '<div style="width:100%; height:100vh" class="visualizationbox" id="visualization1" /">',
            );

            //Run our script now
            //$snippet_handle_button = "'TODO PUT BUTTON SNIPPT CODE HERE'";
            $json_action_map = json_encode($action_map);
            $json_field_map = json_encode($field_map);
            $form["myscripts"] = array('#type' => 'item',
                     '#markup' => "" 
                        //. $snippet_popup 
                        . "<script>"
                        . "\nvar my_action_map = $json_action_map"
                        . "\nvar my_field_map = $json_field_map"
                        . "\nvar projectid = {$this->m_projectid}"
                        . "\nvar manager = bigfathom_util.timequadrant.createEverything('visualization1', my_action_map, my_field_map, projectid);\n"
                        . "</script>"
                        . "");
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
