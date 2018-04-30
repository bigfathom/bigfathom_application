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

require_once 'ASimpleFormPage.php';

/**
 * Visualization of process dependencies
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class VisualDependenciesBasepage extends \bigfathom\ASimpleFormPage
{
    protected $m_context_type = NULL;
    protected $m_projectid = NULL;
    protected $m_urls_arr = NULL;
    protected $m_page_parambundle = NULL;
    protected $m_pagemode = NULL;
    protected $m_oSnippetHelper     = NULL;
    protected $m_oUAH = NULL;
    
    public function __construct($context_type, $projectid, $urls_override_arr=NULL, $page_parambundle=NULL)
    {
        if($context_type != "project" && $context_type != "template")
        {
            throw new \Exception("Must declare a valid context_type!");
        }
        if($projectid == NULL)
        {
            throw new \Exception("Cannot map $context_type hierarchy without specifying a project!");
        }
        $this->m_context_type = $context_type;
        $this->m_projectid = $projectid;

        $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
        if(!$loaded_uah)
        {
            throw new \Exception('Failed to load the UserAccountHelper class');
        }
        $this->m_oUAH = new \bigfathom\UserAccountHelper();
        
        $urls_arr = [];
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['tableconsole'] = 'bigfathom/projects/workitems/duration';
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
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
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
    }

    private function addJS($base_url, $module_path, $theme_path)
    {
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
    }
    
    private function getVarNameMap()
    {
        /*
         *                         . "\nvar my_userinfo_map = $json_userinfo_map;"
                        . "\nvar my_action_map = $json_action_map;"
                        . "\nvar my_field_map = $json_field_map;"
                        . "\nvar tpid = {$this->m_templateid};"
                        . "\nvar template_projectid = {$this->m_templateid};"
                        . "\nvar commands = $json_commands;"
                        . "\nvar manager = bigfathom_util.hierarchy.createEverything('visualization1', 'template', my_userinfo_map, my_action_map, my_field_map, tpid, commands);\n"
                        . "</script>"
         * 
         * 
         *                         . "\nvar my_userinfo_map = $json_userinfo_map;"
                        . "\nvar my_action_map = $json_action_map;"
                        . "\nvar my_field_map = $json_field_map;"
                        . "\nvar projectid = {$this->m_projectid};"
                        . "\nvar commands = $json_commands;"
                        . "\nvar manager = bigfathom_util.hierarchy.createEverything('visualization1', 'project', my_userinfo_map, my_action_map, my_field_map, projectid, commands);\n"
                        . "</script>"

         * 
         */
        $map = [];
        if($this->m_context_type != 'template')
        {
            $map['m_projectid']='projectid';
        } else {
            $map['m_projectid']='tpid';
        }
        return $map;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $user;
            global $base_url;
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            $this->addJS($base_url, $module_path, $theme_path);
            
            $js_var_map = $this->getVarNameMap();

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
            
            $form["formarea1"]['dashboard'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="dashboard">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            if (isset($this->m_urls_arr['tableconsole'])) {
                $initial_button_markup = l('ICON_DURATION Jump to Duration Table', $this->m_urls_arr['tableconsole']
                                , array('attributes'=>array('class'=>'action-button','title'=>'Click this to jump to the duration and effort grid'))
                        );
                $final_jump_button_markup = str_replace('ICON_DURATION', '<i class="fa fa-table" aria-hidden="true"></i>', $initial_button_markup);
            } else {
                $final_jump_button_markup = "";
            }
            
            $findbox_markup = '<span title="Provide ID number of the node you want to find"><label for="txt_find">Search:</label><input type="text" id="txt_find" value="" maxlength="20" size="5"></label></span>';
            $checkbox_zoom_markup = '<label title="Enable ability to zoom and move the display canvas" ><input type="checkbox" id="chk_enablezoompan" value="tasks" checked="checked" >Enable zoom &amp; pan</label>';
            $checkbox_hide_completed_markup = '<span title="Hide all workitems that are already done"><label><input type="checkbox" id="chk_hide_completed_branches" value="tasks" checked="checked" >Hide completed branches</label></span>';
            $resetscale_markup = "<button title='Resets the display back to its original zoom and position' id='btn_reset_scale' type='button'>Reset Display</button>";
            $bar_markup = " | ";
            $sCreateNewWorkitemMarkup = "<button id='btn_create_new_workitem' type='button'>Create New Candidate Workitem</button>";
            $sImportPartsMarkup = "<button id='btn_import_parts' type='button'>Import Parts</button>";
            $form["formarea1"]['dashboard']['controls'] = array('#type' => 'item',
                     '#markup' => ""
                . "<span class='inlinecontrols' style='white-space:nowrap; display:inline'>"
                . $checkbox_hide_completed_markup
                . $bar_markup
                . $findbox_markup
                . $bar_markup
                . $checkbox_zoom_markup
                . $resetscale_markup
                . $bar_markup
                . $sCreateNewWorkitemMarkup
                . $sImportPartsMarkup
                . $bar_markup
                . $final_jump_button_markup
                . "</span>"    
                );            

            $userinfo_map =  $this->m_oUAH->getAllRolesBundle($user->uid);
            
            //Map the library action name to our element IDs
            $snippet_popup_divs = [];
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_loading");            
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_add_workitem");            
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_edit_workitem");            
            $snippet_popup_divs[] = $this->m_oSnippetHelper->getHtmlSnippet("popup_view_workitem");            
            $form["formarea1"]['popupdefs'] = array('#type' => 'item'
                    , '#markup' => implode("\n", $snippet_popup_divs)
                );

            //Map the library action name to our element IDs
            $loading_field_map = array();
            $loading_popup_map = array(
                'context'=>'workitem',
                'subcontext'=>'loading',
                'dlg_container_id'=>'dlg_loading_container',
                'dlg_form_id'=>'dlg_loading_form',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'dlg_form_topinfo'=>'dlg_add_workitem_topinfo',
                'dlg_form_statusinfo'=>'dlg_add_workitem_statusinfo',
                'close_dlg_btn_id'=>'btn_cancel',
                'field_ids_map'=>$loading_field_map,
                );
            
            $add_field_map = array(
                'workitem_nm'=>'new_workitem_nm',
                'purpose_tx'=>'new_purpose_tx',
                'workitem_basetype'=>'new_workitem_basetype',
                'branch_effort_hours_est'=>'new_branch_effort_hours_est',
                'remaining_effort_hours'=>'new_remaining_effort_hours'
                );
            $add_popup_map = array(
                'context'=>'workitem',
                'subcontext'=>'add',
                'dlg_container_id'=>'dlg_add_workitem_container',
                'dlg_form_id'=>'dlg_add_workitem_form',
                'show_dlg_btn_id'=>'btn_show_popup_create_new_item',
                'dlg_form_topinfo'=>'dlg_add_workitem_topinfo',
                'dlg_form_statusinfo'=>'dlg_add_workitem_statusinfo',
                'save_dlg_btn_id'=>'btn_save_new',
                'saveandaddmore_dlg_btn_id'=>'btn_create_workitem_and_addmore',
                'close_dlg_btn_id'=>'btn_add_close',
                'field_ids_map'=>$add_field_map,
                );
            
            $edit_field_map = array(
                'nativeid'=>'edit_nativeid',
                'workitem_nm'=>'edit_workitem_nm',
                'purpose_tx'=>'edit_purpose_tx',
                'workitem_basetype'=>'edit_workitem_basetype',
                'workitem_status_cd'=>'edit_workitem_status_cd',
                'branch_effort_hours_est'=>'edit_branch_effort_hours_est',
                'remaining_effort_hours'=>'edit_remaining_effort_hours'
                );
            $edit_popup_map = array(
                'context'=>'workitem',
                'subcontext'=>'edit',
                'dlg_container_id'=>'dlg_edit_workitem_container',
                'dlg_form_id'=>'dlg_edit_workitem_form',
                'dlg_form_topinfo'=>'dlg_edit_workitem_topinfo',
                'dlg_form_statusinfo'=>'dlg_edit_workitem_statusinfo',
                'save_dlg_btn_id'=>'btn_save_changes',
                'close_dlg_btn_id'=>'btn_edit_close',
                'field_ids_map'=>$edit_field_map,
                );
            
            $view_field_map = array(
                'nativeid'=>'view_nativeid',
                'workitem_nm'=>'view_workitem_nm',
                'purpose_tx'=>'view_purpose_tx',
                'workitem_basetype'=>'view_workitem_basetype',
                'workitem_status_cd'=>'view_workitem_status_cd',
                'branch_effort_hours_est'=>'view_branch_effort_hours_est',
                'remaining_effort_hours'=>'view_remaining_effort_hours'
                );
            $view_popup_map = array(
                'context'=>'workitem',
                'subcontext'=>'view',
                'dlg_container_id'=>'dlg_view_workitem_container',
                'dlg_form_id'=>'dlg_view_workitem_form',
                'dlg_form_topinfo'=>'dlg_view_workitem_topinfo',
                'dlg_form_statusinfo'=>'dlg_view_workitem_statusinfo',
                'close_dlg_btn_id'=>'btn_view_close',
                'field_ids_map'=>$view_field_map,
                );
            
            $popups = array('loading_workitem' => $loading_popup_map
                    ,'add_workitem' => $add_popup_map
                    ,'view_workitem' => $view_popup_map
                    ,'edit_workitem' => $edit_popup_map);

            $action_map = array(
                'popups'=>$popups,
                'txt_find_id'=>'txt_find',
                'reset_scale_btn_id'=>'btn_reset_scale',
                'create_new_workitem_btn_id'=>'btn_create_new_workitem',
                'import_parts_btn_id'=>'btn_import_parts',
                'hide_completed_branches_chk_id'=>'chk_hide_completed_branches',
                'enablezoompan_chk_id'=>'chk_enablezoompan',
                'edit_workitem_btn_id'=>'btn_edit_workitem',
                );
            
            //Draw our canvas
            $form["formarea1"]['mainvisual'] = array(
                '#type' => 'item', 
                '#markup' => '<div style="width:100%; height:100vh" class="visualizationbox" id="visualization1" /">',
            );

            $aggregate_field_map = array_merge($add_field_map, $edit_field_map);
            
            //Run our script now
            $json_userinfo_map = json_encode($userinfo_map);
            $json_action_map = json_encode($action_map);
            $json_field_map = json_encode($aggregate_field_map);
            $json_commands = json_encode($this->m_page_parambundle);
            
            $form["myscripts"] = array('#type' => 'item',
                     '#markup' => ""
                        . "<script>"
                        . "\nvar my_userinfo_map = $json_userinfo_map;"
                        . "\nvar my_action_map = $json_action_map;"
                        . "\nvar my_field_map = $json_field_map;"
                        . "\nvar main_id = {$this->m_projectid};"
                        . "\nvar context_type = '{$this->m_context_type}';"
                        . "\nvar xxxx{$js_var_map['m_projectid']} = {$this->m_projectid};"
                        . "\nvar commands = $json_commands;"
                        . "\nvar manager = bigfathom_util.hierarchy.createEverything('visualization1', context_type, my_userinfo_map, my_action_map, my_field_map, main_id, commands);\n"
                        . "</script>"
                        . "");

            //Spinner markup
            $form["spinnermarkup"] = array('#type' => 'item',
                     '#markup' => ""
                        . '<div id="loader" class="overlay-loader">'
                        . '<div class="loader-background color-flip"></div>'
                        . "<img class='loader-icon spinning-cog' src='$base_url/$theme_path/css/cogs/cog01.svg' data-cog='cog01'>"
                        . '</div>'
                        );
            
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
