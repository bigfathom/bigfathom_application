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
require_once 'helper/BrainstormItemsPageHelper.php';


/**
 * Brainstorming topics page
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class BrainstormTopicsPage extends \bigfathom\ASimpleFormPage
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
            $urls_arr = [];
        }
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['tableconsole'] = 'bigfathom/projects/brainstormitems';
        $urls_arr['return'] = $pmi['link_path'];
        $this->m_urls_arr = $urls_arr;
        
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $this->m_oPageHelper = new \bigfathom\BrainstormItemsPageHelper($this->m_urls_arr);
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
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/js/d3.v3.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_svg.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/visualization/util_nodes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes_lib.js");
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_popup.js");
            
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_env_brainstormlanes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_brainstorm_data.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_brainstorm.js");

            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['visualization-container']))
            {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if(!isset($html_classname_overrides['table-container']))
            {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if(!isset($html_classname_overrides['container-inline']))
            {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if(!isset($html_classname_overrides['action-button']))
            {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            
            $form["formarea1"] = array(
                '#prefix' => "\n<section>\n",
                '#suffix' => "\n</section>\n",
            );
            //global $base_url;

            $snippet_popup_divs = [];
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_loading");            
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_add_brainstorm");            
            
            $form["formarea1"]['popupdefs'] = array('#type' => 'item'
                    , '#markup' => implode("\n", $snippet_popup_divs)
                );

            //Map the library action name to our element IDs
            $add_field_map = array(
                'brainstom_item_name'=>'new_brainstom_item_name',
                'brainstom_item_purpose'=>'new_brainstom_item_purpose',
                'brainstom_item_type'=>'new_brainstom_item_type'
                );
            $add_popup_map = array(
                'context'=>'brainstorm',
                'subcontext'=>'add',
                'dlg_container_id'=>'dlg_create_brainstorm_item_container',
                'dlg_form_id'=>'dlg_create_brainstorm_item_form',
                'dlg_form_statusinfo'=>'dlg_add_brainstorm_statusinfo',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'save_dlg_btn_id'=>'btn_create_brainstorm_item',
                'saveandaddmore_dlg_btn_id'=>'btn_create_brainstorm_and_addmore',
                'close_dlg_btn_id'=>'btn_close',
                'field_ids_map'=>$add_field_map,
                );
            $popups = array('new_brainstorm_topic' => $add_popup_map);
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
            
            if (isset($this->m_urls_arr['tableconsole'])) {
                $initial_button_markup = l('ICON_TABLE Jump to Brainstorm Table', $this->m_urls_arr['tableconsole']
                                , array('attributes'=>array('class'=>'action-button','title'=>'Click this to jump to the brainstorm detail grid'))
                        );
                $final_jump_button_markup = str_replace('ICON_TABLE', '<i class="fa fa-table" aria-hidden="true"></i>', $initial_button_markup);
            } else {
                $final_jump_button_markup = "";
            }
            

            $checkbox_markup = '';// '<label><input type="checkbox" id="chk_enablezoompan" value="tasks" checked="checked" >Enable zoom &amp; pan</label>';
            $sResetScaleButtonMarkup = '';// "<button id='btn_reset_scale' type='button'>Reset Display</button>";
            $bar_markup = " | ";
            $sNewTopicButtonMarkup = "<button title='A new topic may eventually become a workitem' id='" . $action_map['popups']['new_brainstorm_topic']['show_dlg_btn_id'] . "' type='button'>Create New Candidate Topic Item</button>";
            $form["formarea1"]['dashboard']['controls'] = array('#type' => 'item',
                     '#markup' => ""
                . "<span class='inlinecontrols' style='white-space:nowrap; display:inline'>"
                . "$checkbox_markup"
                . "$sResetScaleButtonMarkup"
                . "$sNewTopicButtonMarkup"
                . $bar_markup 
                . $final_jump_button_markup
                . "</span>"
                );            
            
            //Draw our canvas
            $form["formarea1"]['mainvisual'] = array(
                '#type' => 'item', 
                '#markup' => '<div style="width:100%; height:100vh" class="visualizationbox" id="visualization1" /">',
            );

            //Run our script now
            $json_action_map = json_encode($action_map);
            $json_field_map = json_encode($add_field_map);
            $form["myscripts"] = array('#type' => 'item',
                     '#markup' => "" 
                        //. $snippet_popup 
                        . "<script>"
                        . "\nvar my_action_map = $json_action_map"
                        . "\nvar my_field_map = $json_field_map"
                        . "\nvar projectid = {$this->m_projectid}"
                        . "\nvar manager = bigfathom_util.brainstorm.createEverything('visualization1', my_action_map, my_field_map, projectid);\n"
                        . "</script>"
                        . "");

            $form["formarea1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            if (isset($this->m_urls_arr['tableconsole'])) {
                $form["formarea1"]['action_buttons']['tableconsole'] = array('#type' => 'item'
                    , '#markup' => $final_jump_button_markup);
            }
                        
            if (isset($this->m_urls_arr['return'])) {
                $exit_link_markup = l('Exit', $this->m_urls_arr['return']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $form["formarea1"]['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $exit_link_markup);
            }
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
