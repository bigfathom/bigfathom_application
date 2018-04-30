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

require_once 'helper/VisualDependenciesBasepage.php';

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapTemplateDependenciesPage extends \bigfathom\VisualDependenciesBasepage
{
    
    public function __construct($projectid, $urls_override_arr=NULL, $page_parambundle=NULL)
    {
        parent::__construct("template",$projectid, $urls_override_arr, $page_parambundle);
    }
    
    
    //protected $m_templateid = NULL;
    //protected $m_urls_arr = NULL;
    //protected $m_page_parambundle = NULL;
    //protected $m_pagemode;
    
    public function XXX__construct($projectid, $urls_arr=NULL, $page_parambundle=NULL)
    {
        if($projectid == NULL)
        {
            throw new \Exception("Cannot map hierarchy without specifying a project!");
        }
        $this->m_templateid = $projectid;
        if($urls_arr == NULL)
        {
            $urls_arr = array();
        }
        $this->m_urls_arr = $urls_arr;
        if($page_parambundle == NULL)
        {
            $page_parambundle = array();
        }
        $this->m_page_parambundle = $page_parambundle;
        if(empty($this->m_page_parambundle['goalid']))
        {
            $this->m_pagemode = 'showgoals';   
        } else {
            $this->m_pagemode = 'showtasks';   
        }
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function XXXgetForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $base_url;
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/js/d3.v3.js");
            drupal_add_js("$base_url/$theme_path/js/spinner.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_svg.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/visualization/util_nodes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_shapes_lib.js");
            drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_popup.js");
            drupal_add_js("$base_url/$module_path/visualization/d3v3_util_env_multilevelhierarchy.js");
            
            if($this->m_pagemode === 'showgoals')
            {
                drupal_add_js("$base_url/$module_path/visualization/d3v3_util_hierarchy_data.js");
                drupal_add_js("$base_url/$module_path/visualization/d3v3_util_hierarchy.js");
            } else {
                drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_task_hierarchy_data.js");
                drupal_add_js("$base_url/$module_path/visualization/help_d3v3_util_task_hierarchy.js");
            }
            
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

            $findbox_markup = '<span title="Provide ID number of the node you want to find"><label for="txt_find">Search:</label><input type="text" id="txt_find" value="" maxlength="20" size="5"></label></span>';
            $checkbox_zoom_markup = '<label title="Enable ability to zoom and move the display canvas" ><input type="checkbox" id="chk_enablezoompan" value="tasks" checked="checked" >Enable zoom &amp; pan</label>';
            $resetscale_markup = "<button title='Resets the display back to its original zoom and position' id='btn_reset_scale' type='button'>Reset Display</button>";
            $bar_markup = " | ";
            $form["formarea1"]['dashboard']['controls'] = array('#type' => 'item',
                     '#markup' => ""
                . "<span class='inlinecontrols' style='white-space:nowrap; display:inline'>"
                . $findbox_markup
                . $bar_markup
                . $checkbox_zoom_markup
                . $resetscale_markup
                . "</span>"    
                );            


            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            
            
            global $user;
            $userinfo_map =  $this->m_oUAH->getAllRolesBundle($user->uid);
            
            //Map the library action name to our element IDs
            $field_map = array(
                'brainstom_item_name'=>'new_brainstom_item_name',
                'brainstom_item_purpose'=>'new_brainstom_item_purpose',
                'brainstom_item_type'=>'new_brainstom_item_type'
                );
            $popup_map = array(
                'context'=>'brainstorm',
                'dlg_container_id'=>'dlg_create_brainstorm_item_container',
                'dlg_form_id'=>'dlg_create_brainstorm_item_form',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'save_dlg_btn_id'=>'btn_create_brainstorm_item',
                'saveandaddmore_dlg_btn_id'=>'btn_create_brainstorm_and_addmore',
                'close_dlg_btn_id'=>'btn_close',
                'field_ids_map'=>$field_map,
                );
            $popups = array('todo' => $popup_map);
            $action_map = array(
                'popups'=>$popups,
                'txt_find_id'=>'txt_find',
                'reset_scale_btn_id'=>'btn_reset_scale',
                'enablezoompan_chk_id'=>'chk_enablezoompan',
                );
            
            //Draw our canvas
            $form['mainvisual'] = array(
                '#type' => 'item', 
                '#prefix' => '<div style="width:100%; height:100vh" class="visualizationbox" id="visualization1" /">',
            );

            //Run our script now
            $json_userinfo_map = json_encode($userinfo_map);
            $json_action_map = json_encode($action_map);
            $json_field_map = json_encode($field_map);
            $json_commands = json_encode($this->m_page_parambundle);
            
            $form["myscripts"] = array('#type' => 'item',
                     '#markup' => ""
                        . "<script>"
                        . "\nvar my_userinfo_map = $json_userinfo_map;"
                        . "\nvar my_action_map = $json_action_map;"
                        . "\nvar my_field_map = $json_field_map;"
                        . "\nvar tpid = {$this->m_templateid};"
                        . "\nvar template_projectid = {$this->m_templateid};"
                        . "\nvar commands = $json_commands;"
                        . "\nvar manager = bigfathom_util.hierarchy.createEverything('visualization1', 'template', my_userinfo_map, my_action_map, my_field_map, tpid, commands);\n"
                        . "</script>"
                        . "");

            //Spinner markup
            $form["spinnermarkup"] = array('#type' => 'item',
                     '#markup' => ""
                        . '<div id="loader" class="overlay-loader">'
                        . '<div class="loader-background color-flip"></div>'
                        . "<img class='loader-icon spinning-cog' src='/$theme_path/css/cogs/cog01.svg' data-cog='cog01'>"
                        . '</div>'
                        );
                            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
