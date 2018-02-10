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
 * All the functions for menus are in this file
 * 
 */

/**
 * Build a simple form
 */
function bigfathom_core_master_simple_form_builder($oPI, $form, &$form_state)
{
    try
    {
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

function bigfathom_core_menu_topinfo(&$items, $weight=1, $menu_base='bigfathom/topinfo')
{
    $page_arguments_topmenu_oneproject = array('layout_name'=>'bftopmenu_oneproject');
    $page_arguments_topmenu_multiproject = array('layout_name'=>'bftopmenu_multiproject');
    
    $items["$menu_base"] = array(
      'title' => 'Top Menu Options',
      'page callback' => 'bigfathom_core_topinfo_form',
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Top menu options',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
    
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('dashboard');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'page callback' => 'bigfathom_core_topinfo_dashboards_form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
    
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('projects');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'page callback' => 'bigfathom_core_topinfo_oneproject_form',
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );

    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('sitemanage');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'page callback' => 'bigfathom_core_topinfo_sitemanage_form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
    
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('reports');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'page callback' => 'bigfathom_core_topinfo_reports_form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
    
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('youraccount');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'page callback' => 'bigfathom_core_topinfo_youraccount_form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
    
    $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('help');
    $menu_key = $info_bundle['menu_key'];
    $items[$menu_key] = array(
      'title' => $info_bundle['label'],
      'page arguments' => $page_arguments_topmenu_oneproject,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => $info_bundle['description'],
      'page callback' => 'bigfathom_core_topinfo_help_form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
      'menu_name' => 'navigation',
    );
}

function bigfathom_core_menu_core_callbacks(&$items, $menu_base='bigfathom/topinfo')
{
    $page_arguments_multiproj = array('layout_name'=>'maxcontent_multiproject');
    $page_arguments_oneproj = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    
    $items['bigfathom/exportjson'] = array(
      'title' => 'JSON Format Export',
      'page callback' => 'bigfathom_core_exportjson_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Export data in a JSON format',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/importjson'] = array(
      'title' => 'JSON Format Import',
      'page callback' => 'bigfathom_core_importjson_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Import data from a JSON format',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/viewpublishedprojectinfo'] = array(
      'title' => 'View Published Project Information',
      'page callback' => 'bigfathom_core_viewpublishedprojectinfo_form',
      'page arguments' => $page_arguments_oneproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View one published project information record',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/deletepublishedprojectinfo'] = array(
      'title' => 'Delete Published Project Information',
      'page arguments' => $page_arguments_oneproj,
      'page callback' => 'bigfathom_core_deletepublishedprojectinfo_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete one published project information record',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/downloadprojectinfo'] = array(
      'title' => 'Download Project Information',
      'page arguments' => $page_arguments_oneproj,
      'page callback' => 'bigfathom_core_downloadprojectinfo_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Download published project information',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/downloadtemplate'] = array(
      'title' => 'Download Template',
      'page callback' => 'bigfathom_core_downloadtemplate_form',
      'page arguments' => $page_arguments_oneproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Download a template',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/createprojectfromtemplate'] = array(
      'title' => 'Create a Project from a Template',
      'page callback' => 'bigfathom_core_create_projectfromtemplate_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Create a project from a template',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/createtemplatefromproject'] = array(
      'title' => 'Create a Project Template',
      'page callback' => 'bigfathom_core_create_templatefromproject_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Create a project template from a project',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/createtemplatefromworkitem'] = array(
      'title' => 'Create a Parts Template',
      'page callback' => 'bigfathom_core_create_templatefromworkitem_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Create a parts template from an existing workitem',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    //Deprecated????
    $items['bigfathom/projects/overviewproject'] = array(
      'title' => 'Overview of Project',
      'page callback' => 'bigfathom_core_overviewproject_form',
      'page arguments' => array('maxcontentpage'),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Overview Project Form',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/projects/select'] = array(
      'title' => 'Select Project Programmatically',
      'page callback' => 'bigfathom_core_selectproject_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Set one project selection programmatically',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/projects/clearselection'] = array(
      'title' => 'Clear Project Selection',
      'page callback' => 'bigfathom_core_clearprojectselection_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Clear the current project selection programmatically',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/project/brainstormtopics/move_all_trashcan2parkinglot'] = array(
      'title' => 'Move all Trashed Brainstorm Topics to Parkinglot',
      'page callback' => 'bigfathom_core_project_brainstorm_move_trashcan2parkinglot_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Move all trashed brainstorm topics to parking lot',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/project/brainstormtopics/move_all_parkinglot2trashcan'] = array(
      'title' => 'Move all Parked Brainstorm Topics to Trashcan',
      'page callback' => 'bigfathom_core_project_brainstorm_move_parkinglot2trashcan_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Move all parked brainstorm topics into the trashcan',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/project/brainstormtopics/restore_all_parkinglot'] = array(
      'title' => 'Restore all Parked Brainstorm Topics',
      'page callback' => 'bigfathom_core_project_brainstorm_restore_all_parkinglot_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Restore all parked brainstorm topics',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/project/brainstormtopics/empty_the_trashcan'] = array(
      'title' => 'Empty Brainstorm Topics Trashcan',
      'page callback' => 'bigfathom_core_project_brainstorm_empty_the_trashcan_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Empty the brainstorm topics trashcan now',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/project/lock_all_estimates'] = array(
      'title' => 'Project Lock All Constraints',
      'page callback' => 'bigfathom_core_project_lock_all_estimates_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Automatically mark all the effort and dates locked',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/project/unlock_all_estimates'] = array(
      'title' => 'Project Unlock All Constraints',
      'page callback' => 'bigfathom_core_project_unlock_all_estimates_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Automatically mark all the effort and dates unlocked',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    /*
    $items['bigfathom/project/autofill_workestimates'] = array(
      'title' => 'Project Autofill Blanks',
      'page callback' => 'bigfathom_core_project_autofill_workestimates_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Automatically fill in blank duration fields from bounds',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    */
    
    $items['bigfathom/project/autofill_unlocked'] = array(
      'title' => 'Project Autofill Unlocked Constraints',
      'page callback' => 'bigfathom_core_project_autofill_unlocked_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Automatically fill in unlocked effort and date fields',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/projects/addtop'] = array(
      'title' => 'Add Top Level Project',
      'page callback' => 'bigfathom_core_addtopprojectmain_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Top Level Project Form',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/projects/addsub'] = array(
      'title' => 'Add Antecedent Project',
      'page callback' => 'bigfathom_core_addsubprojectmain_form',
      'page arguments' => array('maxcontentpage'),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add an antecedent project (AKA "sub-project") to the system',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/convert_intoproject'] = array(
      'title' => 'Convert Goal into a Project',
      'page callback' => 'bigfathom_core_convert_goalintoproject_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Convert goal into a project',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/convert_intogoal'] = array(
      'title' => 'Convert Project into a Goal',
      'page callback' => 'bigfathom_core_convert_projectintogoal_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Convert project into a goal',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/attachments/view'] = array(
      'title' => t('View Attached File'),
      'page callback' => 'bigfathom_core_viewattachment_form',
      'page arguments' => array('maxcontentpage'),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => t('View an attached file'),
      'type' => MENU_CALLBACK,
      'file' => 'attachments.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/attachments/download'] = array(
      'page callback' => 'bigfathom_core_download_attachment_noform',
      'access arguments' => array('access content'),
      'type' => MENU_CALLBACK,
      'file' => 'attachments.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_menu_sitewide_options(&$items,$weight=300, $menu_base='bigfathom')
{
    try
    {
        $page_arguments_topmenu_multiproject = array('layout_name'=>'bftopmenu_multiproject','menu_base'=>$menu_base);
        
        //Top level navigation menu for details
        $items["$menu_base/sitemanage"] = array(
          'title' => 'Settings Menu Options',
          'page callback' => 'bigfathom_core_topinfo_sitemanage_form',
          'page arguments' => $page_arguments_topmenu_multiproject,
          'access callback' => TRUE,        
          'description' => 'Adjust settings of sitewide content',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );

        $weight += 5;
        bigfathom_core_submenu_sitewide_visionstatements($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_projects($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_templates($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_people($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_project_roles($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_project_portfolio($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_system_roles($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_groups($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_locations($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_equipment($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_external_resource($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_project_context($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_holidays($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_baseline_availability($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_uridomains($items,$weight);
        $weight += 5;
        bigfathom_core_submenu_sitewide_apptesting($items,$weight);
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_menu_root_options(&$items, $weight=100, $menu_base='bigfathom')
{
    try
    {
        //Items that are only for authenticated users
        bigfathom_core_menu_dashboard_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_oneproject_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_communication_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_sitewide_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_youraccount_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_report_options($items, $weight);
        $weight += 200;
        bigfathom_core_menu_help_options($items,$weight);

        //And add all the public items
        bigfathom_core_callback_public_options($items);
        
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_menu_oneproject_options(&$items, $weight=200, $menu_base='bigfathom/projects')
{
    try
    {
        
        $page_arguments_topmenu_multiproject = array('layout_name'=>'bftopmenu_multiproject','menu_base'=>$menu_base);
        $page_arguments_multiproject = array('layout_name'=>'maxcontent_multiproject');
        
        $items["bigfathom/projects"] = array(
            'title' => 'Project Menu Options',
            'page callback' => 'bigfathom_core_topinfo_oneproject_form',
            'page arguments' => $page_arguments_topmenu_multiproject,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Project options',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'topinfo.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
            'menu_name' => 'navigation',
        );
        $items['bigfathom/projects/userselectone'] = array(
          'title' => 'Select or Create a Locally Managed Project',
          'page callback' => 'bigfathom_core_selectproject_form',
          'page arguments' => $page_arguments_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Select a project where workitem details are maintained locally',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'projects.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        
        bigfathom_core_submenu_oneproject_visualcollaboration($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_brainstorm($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_workitems($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_communication($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_usecases($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_testcases($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_comment_workitems($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_comment_testcases($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_sprints($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_subprojectsandpublishing($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_score($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_graph_treemap($items, $weight);
        $weight += 5;
        bigfathom_core_submenu_oneproject_projbaselines($items, $weight);
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_submenu_oneproject_subprojectsandpublishing(&$items, $weight=100, $menu_base='bigfathom/projects')
{
    try
    {
        //'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_oneproj_finished,array('context-high'=>'idea','context-low'=>'plan')),
        
        $page_arguments_topmenu_multiproject = array('layout_name'=>'bftopmenu_multiproject','menu_base'=>$menu_base);
        $page_arguments_multiproject = array('layout_name'=>'maxcontent_multiproject');
        $page_arguments_oneproj_finished = array(
            'layout_name'=>'maxcontent'
            ,'menu_base'=>$menu_base
            ,'requires_project'=>TRUE
        );
        $page_arguments_oneproj_unfinished = array(
            'layout_name'=>'maxcontent'
            ,'menu_base'=>$menu_base
            ,'requires_project'=>TRUE
            ,'unfinished'=>TRUE
        );
        
        $items['bigfathom/projects/declaresubprojects'] = array(
          'title' => 'Manage Antecedent Projects Console',
          'page callback' => 'bigfathom_core_mng_subprojects_form',
          'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_oneproj_finished,array('context-high'=>'plan','context-low'=>'exec')),
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Map other projects into this one',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'workitems.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        
        $items['bigfathom/projects/managepublishinfo'] = array(
          'title' => 'Manage Project Information Publishing',
          'page callback' => 'bigfathom_core_mng_publishprojectinfo_form',
          'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_oneproj_finished,array('context-high'=>'plan','context-low'=>'exec')),
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Manage what is published for the selected project',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'projects.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        
        $items['bigfathom/publishprojectinfo'] = array(
          'title' => 'Publish Project Information',
          'page callback' => 'bigfathom_core_publishprojectinfo_form',
          'page arguments' => $page_arguments_oneproj_finished,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Publish current project information',
          'type' => MENU_CALLBACK,
          'file' => 'projects.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_menu_communication_options(&$items, $weight=400, $menu_base='bigfathom/communications')
{
    try
    {
        $page_arguments_topmenu_multiproject = array('layout_name'=>'bftopmenu_multiproject','menu_base'=>$menu_base);
        $page_arguments_multiproject = array('layout_name'=>'maxcontent_multiproject');
        
        $items["$menu_base/communications"] = array(
          'title' => 'Communications',
          'page callback' => 'bigfathom_core_topinfo_communications_form',
          'page arguments' => $page_arguments_topmenu_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Communication options',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
          'menu_name' => 'navigation',
        );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}


/**
 * Most of the reports are defined by the reports module
 */
function bigfathom_core_menu_report_options(&$items, $weight=700, $menu_base='bigfathom/reports')
{
    try
    {
        $page_arguments_multiproject = array('layout_name'=>'maxcontent_multiproject');
        
        $items["$menu_base"] = array(
          'title' => 'Report Menu Options',
          'page callback' => 'bigfathom_core_topinfo_reports_form',
          'page arguments' => $page_arguments_multiproject,
          'access callback' => TRUE,        
          'description' => 'Select and run available reports',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => 700,
        );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_menu_dashboard_options(&$items, $weight=100, $menu_base='bigfathom/dashboards')
{
    try
    {
        $page_arguments_multiproj = array('layout_name'=>'maxcontent_multiproject');
        $page_arguments_top = array('layout_name'=>'bftopmenu_multiproject');
        $page_arguments_oneproj = array(
            'layout_name'=>'maxcontent'
            ,'menu_base'=>$menu_base
            ,'requires_project'=>TRUE
        );
        
        $items["$menu_base"] = array(
            'title' => 'Dashboard Menu Options',
            'page callback' => 'bigfathom_core_topinfo_dashboards_form',
            'page arguments' => $page_arguments_top,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard options',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'topinfo.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
            'menu_name' => 'navigation',
        );
/*
        $items["$menu_base/prioritizedworkitems"] = array(
            'title' => 'Prioritized Workitems Listing',
            'page callback' => 'bigfathom_core_viewabout_form',
            'page arguments' => $page_arguments_multiproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Lists all your workitems with prioritized metric',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );

        $items["$menu_base/importanceworkitems"] = array(
          'title' => 'Your Workitem Importance Declarations',
          'page callback' => 'bigfathom_core_mng_importance_person2wi_form',
          'page arguments' => $page_arguments_multiproj,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Declare the importance of each workitem of all projects',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'workitems.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );

        $items["$menu_base/influenceworkitems"] = array(
          'title' => 'Your Workitem Influence Declarations',
          'page callback' => 'bigfathom_core_mng_influence_person2wi_form',
          'page arguments' => $page_arguments_multiproj,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Declare your influence on workitems of all projects',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'workitems.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        
        $items["$menu_base/allyourprojects"] = array(
            'title' => 'Overview of All Your Projects',
            'page callback' => 'bigfathom_core_dashboard4all_yourprojects_form',
            'page arguments' => $page_arguments_multiproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard showing information for all your projects',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );
        
        $items["$menu_base/workitem_timequadrant"] = array(
            'title' => 'Workitem Time Management Insight',
            'page callback' => 'bigfathom_core_quadrantdisplay_form',
            'page arguments' => $page_arguments_oneproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Time management insight for workitems',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );
        
        $items["$menu_base/user"] = array(
            'title' => 'Personal Dashboard',
            'page callback' => 'bigfathom_core_dashboard4user_form',
            'page arguments' => $page_arguments_multiproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard specific to a user',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );

        $items["$menu_base/oneproject"] = array(
            'title' => 'Open Items Current Project Dashboard',
            'page callback' => 'bigfathom_core_dashboard4project_form',
            'page arguments' => $page_arguments_oneproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard specific to a project',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );

        $items["$menu_base/group"] = array(
            'title' => 'Group Dashboard',
            'page callback' => 'bigfathom_core_dashboard4group_form',
            'page arguments' => $page_arguments_multiproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard specific to a group',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' =>  $weight++,
        );
        
        $items["$menu_base/sprint"] = array(
            'title' => 'Sprint Dashboard',
            'page callback' => 'bigfathom_core_dashboard4sprint_form',
            'page arguments' => $page_arguments_oneproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard specific to a sprint',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' =>  $weight++,
        );
        
        $items["$menu_base/workitem"] = array(
            'title' => 'Workitem Dashboard',
            'page callback' => 'bigfathom_core_dashboard4workitem_form',
            'page arguments' => $page_arguments_oneproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Dashboard specific to a workitem',
            'type' => MENU_CALLBACK,
            'expanded' => TRUE,
            'file' => 'dashboards.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' =>  $weight++,
            'menu_name' => 'navigation',
        );
         * 
         */
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_menu_help_options(&$items, $weight=900, $menu_base='bigfathom/help')
{
    $page_arguments_maxcontent_multiproject = array('layout_name'=>'maxcontent_multiproject');
    $page_arguments_top = array('layout_name'=>'bftopmenu_multiproject');
    try
    {
        
        $items["$menu_base"] = array(
          'title' => 'Help Menu Options',
          'page callback' => 'bigfathom_core_topinfo_help_form',
          'page arguments' => $page_arguments_top,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Help menu options',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );

        $items["bigfathom/help/navigation"] = array(
            'title' => 'How to Navigate in this Application',
            'page callback' => 'bigfathom_core_showhelp_navigation_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to navigate within the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'NavigationPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/selectproject"] = array(
            'title' => 'How to Select an Existing Project',
            'page callback' => 'bigfathom_core_showhelp_selectproject_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to select an existing project',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'SelectExistingProjectPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/newproject"] = array(
            'title' => 'How to Create a New Project',
            'page callback' => 'bigfathom_core_showhelp_newproject_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to create a new project',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'NewProjectPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );

        $items["bigfathom/help/antproject"] = array(
            'title' => 'How to Declare Antecedent Projects',
            'page callback' => 'bigfathom_core_showhelp_antproject_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to declare antecedent projects (AKA "subprojects")',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'DeclareAntProjectsPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/setworkdeps"] = array(
            'title' => 'How to Declare Workitem Dependencies',
            'page callback' => 'bigfathom_core_showhelp_setworkdeps_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to set the dependency relationships between workitems of a project',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'SetWorkitemDependencies.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/newsprint"] = array(
            'title' => 'How to Declare Sprints',
            'page callback' => 'bigfathom_core_showhelp_newsprint_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to declare sprints in a project',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'NewSprintPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/comms"] = array(
            'title' => 'How to Communicate-In-Context',
            'page callback' => 'bigfathom_core_showhelp_comms_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to declare action items and create comments associated with specific project content',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'InContextCommunicationsPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/newtemplate"] = array(
            'title' => 'How to Create a New Template',
            'page callback' => 'bigfathom_core_showhelp_newtemplate_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to create a new template',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'NewTemplatePage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        
        $items["bigfathom/help/setpersonavail"] = array(
            'title' => 'How to Set Availability of People',
            'page callback' => 'bigfathom_core_showhelp_setpersonavail_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to set the availabiltity of people in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'SetPersonAvailabilityPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/usecases"] = array(
            'title' => 'How to Create and Manage Use Cases',
            'page callback' => 'bigfathom_core_showhelp_usecases_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to create and manage use cases in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'UseCasesPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/testcases"] = array(
            'title' => 'How to Create and Manage Test Cases',
            'page callback' => 'bigfathom_core_showhelp_testcases_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'how2', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information on how to create and manage use cases in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'TestCasesPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/useragileleader"] = array(
            'title' => 'Agile Leader Users',
            'page callback' => 'bigfathom_core_showhelp_user_agileleader_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'insight4usertype', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information to help Agile Leaders, such as Scrum Masters, get started',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'HelpUserAgileLeaderPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/userba"] = array(
            'title' => 'Business Analyst Users',
            'page callback' => 'bigfathom_core_showhelp_user_ba_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'insight4usertype', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information to help Business Analysts users get started',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'HelpUserBAPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/userqa"] = array(
            'title' => 'Quality Assurance Users',
            'page callback' => 'bigfathom_core_showhelp_user_qa_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'insight4usertype', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information to help Quality Assurance users get started',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'HelpUserQAPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/usersoftwaredev"] = array(
            'title' => 'Software Developer Users',
            'page callback' => 'bigfathom_core_showhelp_user_softdev_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'insight4usertype', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information to help Software Developer users get started',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'HelpUserSoftwareDevPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/userclassicpm"] = array(
            'title' => 'Classical Project Management Users',
            'page callback' => 'bigfathom_core_showhelp_user_classicpm_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'insight4usertype', 'context-high'=>'how2','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information to help classically trained Project Management users get started',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'HelpUserClassicPMPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/symbols"] = array(
            'title' => 'View Symbols Help',
            'page callback' => 'bigfathom_core_showhelp_symbols_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'concept', 'context-high'=>'concept','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information about the symbols used in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'SymbolsPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/tmm"] = array(
            'title' => 'View Time Management Concepts',
            'page callback' => 'bigfathom_core_showhelp_tmm_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'concept', 'context-high'=>'concept','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information about the time management matrix concept used in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'TimeManagementPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/collaboration"] = array(
            'title' => 'View Collaboration Concepts',
            'page callback' => 'bigfathom_core_showhelp_collaboration_form',
            'page arguments' => $page_arguments_maxcontent_multiproject,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information about the collaboration concept used in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'CollaborationPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/projectplanning"] = array(
            'title' => 'View Project Planning and Execution Concepts',
            'page callback' => 'bigfathom_core_showhelp_projectplanning_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'concept', 'context-high'=>'concept','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View help information about project planning and execution in the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'ProjectPlanningPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["bigfathom/help/vision"] = array(
            'title' => 'The Big Picture',
            'page callback' => 'bigfathom_core_showhelp_vision_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'concept', 'context-high'=>'concept','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View an explanation of how some features in the application map to larger concepts',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'ImportanceOfVisionPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => $weight++,
        );
        
        $items["$menu_base/privacystatement"] = array(
            'title' => 'View Privacy Statement',
            'page callback' => 'bigfathom_core_privacystatement_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'dull', 'context-high'=>'dull','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View the application privacy statement',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'ViewPrivacyStatementPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => 100 + ($weight++),
        );

        $items["$menu_base/termsofservicestatement"] = array(
            'title' => 'View Terms of Service Statement',
            'page callback' => 'bigfathom_core_termsofservice_statement_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'dull', 'context-high'=>'dull','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View the application terms of service statement',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'ViewTermsOfServiceStatementPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => 100 + ($weight++),
        );

        $items["$menu_base/viewaboutinfo"] = array(
            'title' => 'View About',
            'page callback' => 'bigfathom_core_viewabout_form',
            'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_maxcontent_multiproject,array('in-div'=>'dull', 'context-high'=>'dull','context-low'=>'')),
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View information about the application',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'ViewAboutPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
            'weight' => 100 + ($weight++),
        );

        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_callback_public_options(&$items, $weight=1000, $menu_base='bigfathom/public')
{
    $page_arguments_maxcontent_multiproject = array('layout_name'=>'maxcontent_multiproject');
    try
    {
        
        $items["$menu_base/privacystatement"] = array(
            'title' => 'View Privacy Statement',
            'page callback' => 'bigfathom_core_privacystatement_form',
            'page arguments' => $page_arguments_maxcontent_multiproject,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'View the application privacy statement',
            'type' => MENU_CALLBACK,
            'file' => 'ViewPrivacyStatementPage.php',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/form/help/',
        );
        
        $items["$menu_base/downloadprojectinfo"] = array(
          'title' => 'Download Project Information',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'page callback' => 'bigfathom_core_downloadprojectinfo_form',
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Download public published project information',
          'type' => MENU_CALLBACK,
          'file' => 'io.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        
        $items["$menu_base/downloadprojectinfonamelist"] = array(
          'title' => 'Download Project Information Names',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'page callback' => 'bigfathom_core_downloadprojectinfonamelist_form',
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Download public published project information names',
          'type' => MENU_CALLBACK,
          'file' => 'io.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_submenu_oneproject_graph_treemap(&$items, $weight=280, $menu_base='bigfathom/projects/graph')
{
    try
    {
        
        $page_arguments_oneproj = array(
            'layout_name'=>'maxcontent'
            ,'menu_base'=>$menu_base
            ,'requires_project'=>TRUE
        );
        /*
        $items["$menu_base/treemap4effort"] = array(
            'title' => 'Treemap of Effort',
            'page callback' => 'bigfathom_core_graph_treemap_effort_form',
            'page arguments' => $page_arguments_oneproj,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Treemap graph specific to a project',
            'type' => MENU_NORMAL_ITEM,
            'expanded' => TRUE,
            'file' => 'graph_treemap.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );
        */
        
    } catch (\Exception $ex) {
        throw $ex;
    }
}


function bigfathom_core_submenu_oneproject_comment_workitems(&$items, $weight=250, $menu_base='bigfathom/projects/goals')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );

    //Workitems Communications Console
    $page_goalcomment_arguments = array('layout_name'=>'maxcontent','menu_base'=>'bigfathom/workitem/mng_comments');
    $items["bigfathom/workitem/mng_comments"] = array(
      'title' => 'Workitem Communications Console',
      'page callback' => 'bigfathom_core_mng_workitem_communications_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Workitem communications console',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/workitem/addcomment'] = array(
      'title' => 'Add Workitem Communication',
      'page callback' => 'bigfathom_core_addworkitem_communication_form',
      'page arguments' => $page_goalcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add workitem comment',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/viewcomment'] = array(
      'title' => 'View Workitem Communication',
      'page callback' => 'bigfathom_core_viewworkitem_communication_form',
      'page arguments' => $page_goalcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View workitem comment',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/editcomment'] = array(
      'title' => 'Edit Workitem Communication',
      'page callback' => 'bigfathom_core_editworkitem_communication_form',
      'page arguments' => $page_goalcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit workitem comment',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/deletecomment'] = array(
      'title' => 'Delete Workitem Communication',
      'page callback' => 'bigfathom_core_deleteworkitem_communication_form',
      'page arguments' => $page_goalcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete workitem comment',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_get_multi_page($input_items, $page_settings_ar)
{
    $final_items = [];
    foreach($input_items as $basekey=>$oneitem)
    {
        $menu_base = $oneitem['menu_base'];
        if(empty($menu_base))
        {
            drupal_set_message("Menu construction error: missing menu_base attribute for $basekey item!");
        }
        foreach($page_settings_ar as $one_page_setting)
        {
            $page_arguments = $one_page_setting['page_arguments'];
            $page_arguments['menu_base'] = $menu_base;
            $page_key_tail = $one_page_setting['page_key_tail'];
            $item_key = $basekey . $page_key_tail;
            $final_items[$item_key] = $oneitem;
            $final_items[$item_key]['page arguments'] = $page_arguments;
        }
    }
    return $final_items;
}

function bigfathom_core_submenu_oneproject_comment_testcases(&$items, $weight=250, $menu_base='bigfathom/projects/testcases')
{
    $page_arguments_normal = array(
        'layout_name'=>'maxcontent'
        ,'requires_project'=>TRUE
        ,'in_dialog'=>FALSE
    );

    $page_arguments_indialog = array(
        'layout_name'=>'maxcontent_indialog'
        ,'requires_project'=>TRUE
        ,'in_dialog'=>TRUE
    );
    
    $page_settings_ar = [];
    $page_settings_ar[] = array(
        'page_arguments' => $page_arguments_normal,
        'page_key_tail' => ''
    );
    $page_settings_ar[] = array(
        'page_arguments' => $page_arguments_indialog,
        'page_key_tail' => '_indialog'
    );
    
    //Define template of our items
    $template_items = [];
    $template_items["bigfathom/testcase/send_notifications"] = array(
      'title' => 'Test Case Send Notification Console',
      'page callback' => 'bigfathom_core_mng_testcase_send_notifications_form',
      'menu_base'=>$menu_base,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Testcase send notifications console',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $template_items["bigfathom/testcase/mng_comments"] = array(
      'title' => 'Test Case Communications Console',
      'page callback' => 'bigfathom_core_mng_testcase_communications_form',
      'menu_base'=>$menu_base,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Testcase communications console',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $template_items['bigfathom/testcase/addcomment'] = array(
      'title' => 'Add Test Case Communication',
      'page callback' => 'bigfathom_core_addtestcase_communication_form',
      'menu_base'=>'bigfathom/testcase/mng_comments',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add test case comment',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $template_items['bigfathom/testcase/viewcomment'] = array(
      'title' => 'View Test Case Communication',
      'page callback' => 'bigfathom_core_viewtestcase_communication_form',
      'menu_base'=>'bigfathom/testcase/mng_comments',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View test case comment',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $template_items['bigfathom/testcase/editcomment'] = array(
      'title' => 'Edit Test Case Communication',
      'page callback' => 'bigfathom_core_edittestcase_communication_form',
      'menu_base'=>'bigfathom/testcase/mng_comments',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit test case comment',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $template_items['bigfathom/testcase/deletecomment'] = array(
      'title' => 'Delete Test Case Communication',
      'page callback' => 'bigfathom_core_deletetestcase_communication_form',
      'menu_base'=>'bigfathom/testcase/mng_comments',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete test case comment',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    //Now, construct all our items from the template settings
    $ready_items = bigfathom_core_submenu_get_multi_page($template_items, $page_settings_ar);
    foreach($ready_items as $key => $one_item)
    {
        $items[$key] = $one_item;
    }
    
    //Testcases Communications Console
    $page_itemcomment_arguments = array('layout_name'=>'maxcontent','menu_base'=>'bigfathom/testcase/mng_comments');
    
}

function bigfathom_core_submenu_sitewide_system_roles(&$items, $weight=370, $menu_base='bigfathom/sitemanage/sysroles')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //System Roles
    $template_items[$menu_base] = array(
      'title' => 'System Roles Console',
      'page callback' => 'bigfathom_core_managesysroles_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage System Roles Form',
      'type' => MENU_NORMAL_ITEM,
      'file' => 'systemroles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    $template_items['bigfathom/viewsysrole'] = array(
      'title' => 'View System Role',
      'page callback' => 'bigfathom_core_viewsysrole_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View System Role Form',
      'type' => MENU_CALLBACK,
      'file' => 'systemroles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_project_roles(&$items, $weight=360, $menu_base='bigfathom/sitemanage/roles')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Project Roles
    $items[$menu_base] = array(
      'title' => 'Project Roles Console',
      'page callback' => 'bigfathom_core_manageroles_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage Project Roles Form',
      'type' => MENU_NORMAL_ITEM,
      'file' => 'roles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/addrole'] = array(
      'title' => 'Add Project Role',
      'page callback' => 'bigfathom_core_addrole_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Project Role Form',
      'type' => MENU_CALLBACK,
      'file' => 'roles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewrole'] = array(
      'title' => 'View Project Role',
      'page callback' => 'bigfathom_core_viewrole_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Project Role Form',
      'type' => MENU_CALLBACK,
      'file' => 'roles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editrole'] = array(
      'title' => 'Edit Project Role',
      'page callback' => 'bigfathom_core_editrole_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Project Role Form',
      'type' => MENU_CALLBACK,
      'file' => 'roles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleterole'] = array(
      'title' => 'Delete Project Role',
      'page callback' => 'bigfathom_core_deleterole_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Project Role Form',
      'type' => MENU_CALLBACK,
      'file' => 'roles.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_sprints(&$items, $weight=290, $menu_base='bigfathom/projects/sprints')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    
    $items[$menu_base] = array(
      'title' => 'Sprint Console',
      'page callback' => 'bigfathom_core_managesprints_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'exec','context-low'=>'eval')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Sprint Console Form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/sprint/add'] = array(
      'title' => 'Add Sprint',
      'page callback' => 'bigfathom_core_addsprint_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Sprint Form',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/view'] = array(
      'title' => 'View Sprint',
      'page callback' => 'bigfathom_core_viewsprint_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Sprint Form',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/edit'] = array(
      'title' => 'Edit Sprint',
      'page callback' => 'bigfathom_core_editsprint_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Sprint Form',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/delete'] = array(
      'title' => 'Delete Sprint',
      'page callback' => 'bigfathom_core_deletesprint_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Sprint Form',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/sprint/lock_membership'] = array(
      'title' => 'Lock Sprint Membership',
      'page callback' => 'bigfathom_core_sprint_lock_membership_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Freeze the sprint membership',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/unlock_membership'] = array(
      'title' => 'Lock Sprint Membership',
      'page callback' => 'bigfathom_core_sprint_unlock_membership_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Unfreeze the sprint membership',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    //Sprint Communications Console
    $page_sprintcomment_arguments = array('layout_name'=>'maxcontent','menu_base'=>'bigfathom/sprint/mng_comments');
    $items["bigfathom/sprint/mng_comments"] = array(
      'title' => 'Sprint Communications Console',
      'page callback' => 'bigfathom_core_mng_sprint_communications_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage sprint comments',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/sprint/addcomment'] = array(
      'title' => 'Add Sprint Communication',
      'page callback' => 'bigfathom_core_addsprint_communication_form',
      'page arguments' => $page_sprintcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add sprint comment',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/viewcomment'] = array(
      'title' => 'View Sprint Communication',
      'page callback' => 'bigfathom_core_viewsprint_communication_form',
      'page arguments' => $page_sprintcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View sprint comment',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/editcomment'] = array(
      'title' => 'Edit Sprint Communication',
      'page callback' => 'bigfathom_core_editsprint_communication_form',
      'page arguments' => $page_sprintcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit sprint comment',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/sprint/deletecomment'] = array(
      'title' => 'Delete Sprint Communication',
      'page callback' => 'bigfathom_core_deletesprint_communication_form',
      'page arguments' => $page_sprintcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete sprint comment',
      'type' => MENU_CALLBACK,
      'file' => 'sprints.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_getPageArgumentArray($core_ar, $extra_attribs=NULL)
{
    $copy_ar = $core_ar;
    if(!empty($extra_attribs))
    {
        $copy_ar['extra_attribs'] = $extra_attribs;
    }
    return $copy_ar;
}

function bigfathom_core_submenu_oneproject_brainstorm(&$items, $weight=220, $menu_base='bigfathom/projects/brainstormitems')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
   
    //Manage Brainstorm Topics
    $items["$menu_base"] = array(
      'title' => 'Brainstorm Topics Console',
      'page callback' => 'bigfathom_core_managebrainstormitems_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'idea','context-low'=>'plan')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage brainstorm topics',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/addbrainstormitem'] = array(
      'title' => 'Add Brainstorm Item',
      'page callback' => 'bigfathom_core_addbrainstormitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add brainstorm item',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewbrainstormitem'] = array(
      'title' => 'View Brainstorm Item',
      'page callback' => 'bigfathom_core_viewbrainstormitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View brainstorm item',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editbrainstormitem'] = array(
      'title' => 'Edit Brainstorm Item',
      'page callback' => 'bigfathom_core_editbrainstormitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit brainstorm item',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletebrainstormitem'] = array(
      'title' => 'Delete Brainstorm Item',
      'page callback' => 'bigfathom_core_deletebrainstormitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete brainstorm item',
      'type' => MENU_CALLBACK,
      'file' => 'brainstormitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_usecases(&$items, $weight=322, $menu_base='bigfathom/projects/usecases')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    
    //Manage UseCases
    $items[$menu_base] = array(
      'title' => 'Use Cases',
      'page callback' => 'bigfathom_core_mng_usecases_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'idea plan','context-low'=>'exec')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about use cases',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'usecases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addusecase'] = array(
      'title' => 'Add Use Case',
      'page callback' => 'bigfathom_core_addusecase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new use case',
      'type' => MENU_CALLBACK,
      'file' => 'usecases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewusecase'] = array(
      'title' => 'View Use Case',
      'page callback' => 'bigfathom_core_viewusecase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the use case information',
      'type' => MENU_CALLBACK,
      'file' => 'usecases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editusecase'] = array(
      'title' => 'Edit Use Case',
      'page callback' => 'bigfathom_core_editusecase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the use case information',
      'type' => MENU_CALLBACK,
      'file' => 'usecases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteusecase'] = array(
      'title' => 'Delete Use Case',
      'page callback' => 'bigfathom_core_deleteusecase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the use case information',
      'type' => MENU_CALLBACK,
      'file' => 'usecases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_testcases(&$items, $weight=322, $menu_base='bigfathom/projects/testcases')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
        ,'unfinished'=>FALSE
    );
    
    //Manage Test Cases
    $items[$menu_base] = array(
      'title' => 'Test Cases',
      'page callback' => 'bigfathom_core_mng_testcases_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'idea plan','context-low'=>'exec')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about test cases',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addtestcase'] = array(
      'title' => 'Add Test Case',
      'page callback' => 'bigfathom_core_addtestcase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new test case',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewtestcase'] = array(
      'title' => 'View Test Case',
      'page callback' => 'bigfathom_core_viewtestcase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the test case information',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/edittestcase'] = array(
      'title' => 'Edit Test Case',
      'page callback' => 'bigfathom_core_edittestcase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the test case information',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/executetestcase'] = array(
      'title' => 'Execute Test Case Steps',
      'page callback' => 'bigfathom_core_executetestcase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Execute the test case steps',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletetestcase'] = array(
      'title' => 'Delete Test Case',
      'page callback' => 'bigfathom_core_deletetestcase_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the test case information',
      'type' => MENU_CALLBACK,
      'file' => 'testcases.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_projbaselines(&$items, $weight=322, $menu_base='bigfathom/projects/projbaselines')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    
    //Manage UseCases
    $items[$menu_base] = array(
      'title' => 'Project Baselines',
      'page callback' => 'bigfathom_core_mng_projbaselines_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'eval','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about project baselines',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'project_baselines.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addprojbaseline'] = array(
      'title' => 'Add Project Baseline Snapshot',
      'page callback' => 'bigfathom_core_addprojbaseline_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new project baseline',
      'type' => MENU_CALLBACK,
      'file' => 'project_baselines.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewprojbaseline'] = array(
      'title' => 'View Project Baseline Snapshot',
      'page callback' => 'bigfathom_core_viewprojbaseline_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the project baseline information',
      'type' => MENU_CALLBACK,
      'file' => 'project_baselines.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editprojbaseline'] = array(
      'title' => 'Edit Project Baseline Snapshot',
      'page callback' => 'bigfathom_core_editprojbaseline_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the project baseline information',
      'type' => MENU_CALLBACK,
      'file' => 'project_baselines.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteprojbaseline'] = array(
      'title' => 'Delete Project Baseline Snapshot',
      'page callback' => 'bigfathom_core_deleteprojbaseline_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the project baseline information',
      'type' => MENU_CALLBACK,
      'file' => 'project_baselines.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_communication(&$items, $weight=222, $menu_base='bigfathom/projects/communicationitems')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    
    //Manage Communication Topics
    $items["$menu_base/actionrequestoveriew"] = array(
      'title' => 'Action Request Overview Console',
      'page callback' => 'bigfathom_core_managecommunicationitems_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'exec eval','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'See open action requests across all workitems of the project',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items["$menu_base/search"] = array(
      'title' => 'Communications Search Console',
      'page callback' => 'bigfathom_core_managecommsearch_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'exec eval','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Search communication topics across all workitems of the project for various criteria',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    
    $items['bigfathom/addcommunicationitem'] = array(
      'title' => 'Add Communication Item',
      'page callback' => 'bigfathom_core_addcommunicationitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add communication item',
      'type' => MENU_CALLBACK,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewcommunicationitem'] = array(
      'title' => 'View Communication Item',
      'page callback' => 'bigfathom_core_viewcommunicationitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View communication item',
      'type' => MENU_CALLBACK,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editcommunicationitem'] = array(
      'title' => 'Edit Communication Item',
      'page callback' => 'bigfathom_core_editcommunicationitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit communication item',
      'type' => MENU_CALLBACK,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletecommunicationitem'] = array(
      'title' => 'Delete Communication Item',
      'page callback' => 'bigfathom_core_deletecommunicationitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete communication item',
      'type' => MENU_CALLBACK,
      'file' => 'communicationitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_visualcollaboration(&$items, $weight=120, $menu_base='bigfathom/projects/design')
{
    try
    {
        $page_arguments_menu = array(
            'layout_name'=>'maxcontent'
            ,'menu_base'=>$menu_base
            ,'requires_project'=>TRUE
        );
        $page_arguments_oneproject = array('layout_name'=>'maxcontent_squeezetop','menu_base'=>$menu_base);
        $page_arguments_multiproject = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
        
        //Top level navigation menu for design
        $items[$menu_base] = array(
          'title' => 'Visual Collaboration',
          'page callback' => 'bigfathom_core_topinfodesign_form',
          'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_menu,array('context-high'=>'idea plan','context-low'=>'')),
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Collaborate on ideas and organize them into actionable elements',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );

        $items["{$menu_base}/brainstormcapture"] = array(
          'title' => 'Topic Proposals',
          'page callback' => 'bigfathom_core_brainstormcapture_form',
          'page arguments' => $page_arguments_oneproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Create candidate brainstorm workitem topics',
          'type' => MENU_LOCAL_TASK,
          'expanded' => TRUE,
          'file' => 'brainstorm.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        $items["{$menu_base}/mapprojectcontent"] = array(
          'title' => 'Workitem Dependencies',
          'page callback' => 'bigfathom_core_map_projectdependencies_form',
          'page arguments' => $page_arguments_oneproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Organize the workitems by their dependencies',
          'type' => MENU_LOCAL_TASK,
          'expanded' => TRUE,
          'file' => 'design.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
    } catch (\Exception $ex) {
        throw $ex;
    }
}

function bigfathom_core_submenu_sitewide_people(&$items, $weight=310, $menu_base='bigfathom/sitemanage/people')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Persons
    $items[$menu_base] = array(
      'title' => 'Person Information Console',
      'page callback' => 'bigfathom_core_mng_people_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage person information',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'users.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    $items['bigfathom/addperson'] = array(
      'title' => 'Add Person',
      'page callback' => 'bigfathom_core_addperson_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Person Form',
      'type' => MENU_CALLBACK,
      'file' => 'users.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewperson'] = array(
      'title' => 'View Person',
      'page callback' => 'bigfathom_core_viewperson_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Person Form',
      'type' => MENU_CALLBACK,
      'file' => 'users.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editperson'] = array(
      'title' => 'Edit Person',
      'page callback' => 'bigfathom_core_editperson_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Person Form',
      'type' => MENU_CALLBACK,
      'file' => 'users.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteperson'] = array(
      'title' => 'Delete Person',
      'page callback' => 'bigfathom_core_deleteperson_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Person Form',
      'type' => MENU_CALLBACK,
      'file' => 'users.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/mng_person_availability'] = array(
      'title' => 'Person Availability Console',
      'page callback' => 'bigfathom_core_mng_person_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage person availability information',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'person_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
}

function bigfathom_core_menu_youraccount_options(&$items, $weight=600, $menu_base='bigfathom/youraccount')
{
    try
    {
        $page_arguments_maxcontent_multiproject = array('layout_name'=>'maxcontent_multiproject');
        $page_arguments_top = array('layout_name'=>'bftopmenu_multiproject');
        
        $items["$menu_base"] = array(
          'title' => 'Your Account Menu Options',
          'page callback' => 'bigfathom_core_topinfo_youraccount_form',
          'page arguments' => $page_arguments_top,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Your account menu options',
          'type' => MENU_NORMAL_ITEM,
          'expanded' => TRUE,
          'file' => 'topinfo.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
          'weight' => $weight++,
        );
        
        $items["$menu_base/edityourprofile"] = array(
            'title' => 'Edit Your Profile',
            'page callback' => 'bigfathom_core_edit_your_profile_form',
            'page arguments' => $page_arguments_maxcontent_multiproject,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
            'description' => 'Edit your profile information',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'users.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );
        
        $items["$menu_base/changeyourpassword"] = array(
            'title' => 'Change Your Password',
            'page callback' => 'bigfathom_core_change_your_password_form',
            'page arguments' => $page_arguments_maxcontent_multiproject,
            'access arguments' => array('access content'),
            'access callback' => TRUE,        
              'description' => 'Change your account password',
            'type' => MENU_NORMAL_ITEM,
            'file' => 'users.inc',
            'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
            'weight' => $weight++,
        );
        
        $items['bigfathom/addperson_availability'] = array(
          'title' => 'Add Person Availability',
          'page callback' => 'bigfathom_core_addperson_availability_form',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Add a new availability declaration',
          'type' => MENU_CALLBACK,
          'file' => 'person_availability.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        $items['bigfathom/viewperson_availability'] = array(
          'title' => 'View Person Availability',
          'page callback' => 'bigfathom_core_viewperson_availability_form',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'View the person availability information',
          'type' => MENU_CALLBACK,
          'file' => 'person_availability.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        $items['bigfathom/editperson_availability'] = array(
          'title' => 'Edit Person Availability',
          'page callback' => 'bigfathom_core_editperson_availability_form',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Edit the person availability information',
          'type' => MENU_CALLBACK,
          'file' => 'person_availability.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        $items['bigfathom/deleteperson_availability'] = array(
          'title' => 'Delete Person Availability',
          'page callback' => 'bigfathom_core_deleteperson_availability_form',
          'page arguments' => $page_arguments_maxcontent_multiproject,
          'access arguments' => array('access content'),
          'access callback' => TRUE,        
          'description' => 'Delete the person availability information',
          'type' => MENU_CALLBACK,
          'file' => 'person_availability.inc',
          'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
        );
        
    } catch (\Exception $ex) {
        throw new \Exception("Failed to create the account menu options because " . ex, 99876, $ex);
    }
}

function bigfathom_core_submenu_oneproject_workitems(&$items, $weight=215, $menu_base='bigfathom/projects/workitems')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );

    $items["bigfathom/projects/map/vision2project"] = array(
      'title' => 'Vision to Project Mapping Console',
      'page callback' => 'bigfathom_core_mng_visionstatement2project_in_project_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'idea plan','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Declare vision statements that this project works toward',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    
    $items["bigfathom/projects/map/groups2project"] = array(
      'title' => 'Relevant Groups Console',
      'page callback' => 'bigfathom_core_mng_groups2project_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'plan','context-low'=>'idea')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Map groups to the project',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );

    $items["bigfathom/projects/map/people2pcg"] = array(
      'title' => 'Collaborator Control Panel',
      'page callback' => 'bigfathom_core_mng_person2pcg_in_project_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'','context-low'=>'idea exec')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Lock down the number of collaborators and declare people as privileged collaborators of the project',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );

    $items['bigfathom/projects/persondeclaredimportance'] = array(
      'title' => 'Importance of Workitems Console',
      'page callback' => 'bigfathom_core_mng_importance_person2wi_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'','context-low'=>'plan eval')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Declare the importance of each workitem of the project',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    $items['bigfathom/projects/persondeclaredinfluence'] = array(
      'title' => 'Influence on Workitems Console',
      'page callback' => 'bigfathom_core_mng_influence_person2wi_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'','context-low'=>'plan eval')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Declare your influence on workitems of the project',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    //Manage Workitems Effort and Duration
    $items["$menu_base/duration"] = array(
      'title' => 'Workitems Effort and Duration Grid',
      'page callback' => 'bigfathom_core_mng_workitem_duration_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'plan exec','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,
      'description' => 'Manage information about work items durations',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    //Manage Workitems Forecast
    $items["$menu_base/forecast"] = array(
      'title' => 'Workitems Forecast Console',
      'page callback' => 'bigfathom_core_mng_workitem_forecast_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'plan exec','context-low'=>'eval')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,
      'description' => 'Manage information about work item forecasts',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    //Manage roles
    $items["$menu_base/map2roles"] = array(
      'title' => 'Workitem Roles Grid',
      'page callback' => 'bigfathom_core_mng_workitem2roles_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'','context-low'=>'plan')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Map workitems to roles',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );

    //Manage sprint membership
    $items["$menu_base/map2sprint"] = array(
      'title' => 'Workitems Sprint Membership Grid',
      'page callback' => 'bigfathom_core_mng_workitem2sprint_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'plan exec','context-low'=>'eval')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Map workitems to sprints',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    //Manage transitions from one type to another
    $items["$menu_base/changeworkitemtype"] = array(
      'title' => 'Workitem Type Conversions',
      'page callback' => 'bigfathom_core_mng_workitem_type_conversions_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Change the type of a workitem into another type',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    $items['bigfathom/workitem/add'] = array(
      'title' => 'Add Workitem',
      'page callback' => 'bigfathom_core_add_workitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Workitem Form',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/view'] = array(
      'title' => 'View Workitem',
      'page callback' => 'bigfathom_core_view_workitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the content of an existing workitem',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/edit'] = array(
      'title' => 'Edit Workitem',
      'page callback' => 'bigfathom_core_edit_workitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Workitem Form',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/duplicate'] = array(
      'title' => 'Duplicate Workitem',
      'page callback' => 'bigfathom_core_duplicate_workitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Create a copy of an existing workitem',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/workitem/delete'] = array(
      'title' => 'Delete Workitem',
      'page callback' => 'bigfathom_core_delete_workitem_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete an existing workitem',
      'type' => MENU_CALLBACK,
      'file' => 'workitems.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_equipment(&$items, $weight=360, $menu_base='bigfathom/sitemanage/equipment')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Equipment
    $items[$menu_base] = array(
      'title' => 'Non-Human Resource Information Console',
      'page callback' => 'bigfathom_core_mng_equipment_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about non-human resources',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'equipment.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/addequipment'] = array(
      'title' => 'Add Non-Human Resource',
      'page callback' => 'bigfathom_core_addequipment_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add non-human Resource form',
      'type' => MENU_CALLBACK,
      'file' => 'equipment.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'menu_name' => 'navigation',
    );
    $items['bigfathom/viewequipment'] = array(
      'title' => 'View Non-Human Resource',
      'page callback' => 'bigfathom_core_viewequipment_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View non-human resource form',
      'type' => MENU_CALLBACK,
      'file' => 'equipment.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editequipment'] = array(
      'title' => 'Edit Non-Human Resource',
      'page callback' => 'bigfathom_core_editequipment_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit non-human resource form',
      'type' => MENU_CALLBACK,
      'file' => 'equipment.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteequipment'] = array(
      'title' => 'Delete Non-Human Resource',
      'page callback' => 'bigfathom_core_deleteequipment_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete non-human resource form',
      'type' => MENU_CALLBACK,
      'file' => 'equipment.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_project_context(&$items, $weight=390, $menu_base='bigfathom/sitemanage/project_context')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage ProjectContext
    $items[$menu_base] = array(
      'title' => 'Project Context Console',
      'page callback' => 'bigfathom_core_mng_project_context_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage Project Context Form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'project_context.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/addproject_context'] = array(
      'title' => 'Add Project Context',
      'page callback' => 'bigfathom_core_addproject_context_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Project Context Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_context.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewproject_context'] = array(
      'title' => 'View Project Context',
      'page callback' => 'bigfathom_core_viewproject_context_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Project Context Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_context.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editproject_context'] = array(
      'title' => 'Edit Project Context',
      'page callback' => 'bigfathom_core_editproject_context_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Project Context Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_context.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteproject_context'] = array(
      'title' => 'Delete Project Context',
      'page callback' => 'bigfathom_core_deleteproject_context_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Project Context Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_context.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items["bigfathom/viewproject_dependencies"] = array(
      'title' => 'View Workitem Dependencies',
      'page callback' => 'bigfathom_core_map_projectdependencies_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the workitems by their dependencies',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'design.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
}

function bigfathom_core_submenu_sitewide_project_portfolio(&$items, $weight=390, $menu_base='bigfathom/sitemanage/project_portfolio')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage ProjectPortfolio
    $items[$menu_base] = array(
      'title' => 'Project Portfolio Console',
      'page callback' => 'bigfathom_core_mng_project_portfolio_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage Project Portfolio Form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'project_portfolio.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );
    $items['bigfathom/addproject_portfolio'] = array(
      'title' => 'Add Project Portfolio',
      'page callback' => 'bigfathom_core_addproject_portfolio_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Project Portfolio Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_portfolio.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewproject_portfolio'] = array(
      'title' => 'View Project Portfolio',
      'page callback' => 'bigfathom_core_viewproject_portfolio_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Project Portfolio Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_portfolio.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editproject_portfolio'] = array(
      'title' => 'Edit Project Portfolio',
      'page callback' => 'bigfathom_core_editproject_portfolio_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Project Portfolio Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_portfolio.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteproject_portfolio'] = array(
      'title' => 'Delete Project Portfolio',
      'page callback' => 'bigfathom_core_deleteproject_portfolio_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Project Portfolio Form',
      'type' => MENU_CALLBACK,
      'file' => 'project_portfolio.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items["bigfathom/viewproject_dependencies"] = array(
      'title' => 'View Workitem Dependencies',
      'page callback' => 'bigfathom_core_map_projectdependencies_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the workitems by their dependencies',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'design.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
}

function bigfathom_core_submenu_sitewide_external_resource(&$items, $weight=330, $menu_base='bigfathom/sitemanage/externalresource')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage External Resource
    $items[$menu_base] = array(
      'title' => 'External Resource Console',
      'page callback' => 'bigfathom_core_mng_external_resource_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage External Resource Form',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'external_resource.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addexternalresource'] = array(
      'title' => 'Add External Resource',
      'page callback' => 'bigfathom_core_addexternal_resource_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new external resource',
      'type' => MENU_CALLBACK,
      'file' => 'external_resource.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewexternalresource'] = array(
      'title' => 'View External Resource',
      'page callback' => 'bigfathom_core_viewexternal_resource_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View an existing external resource',
      'type' => MENU_CALLBACK,
      'file' => 'external_resource.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editexternalresource'] = array(
      'title' => 'Edit External Resource',
      'page callback' => 'bigfathom_core_editexternal_resource_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit an existing external resource',
      'type' => MENU_CALLBACK,
      'file' => 'external_resource.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteexternalresource'] = array(
      'title' => 'Delete External Resource',
      'page callback' => 'bigfathom_core_deleteexternal_resource_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete an existing external resource',
      'type' => MENU_CALLBACK,
      'file' => 'external_resource.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_locations(&$items, $weight=392, $menu_base='bigfathom/sitemanage/location')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Locations
    $items[$menu_base] = array(
      'title' => 'Location Information Console',
      'page callback' => 'bigfathom_core_mng_location_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about locations',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'location.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addlocation'] = array(
      'title' => 'Add Location',
      'page callback' => 'bigfathom_core_addlocation_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Location Form',
      'type' => MENU_CALLBACK,
      'file' => 'location.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewlocation'] = array(
      'title' => 'View Location',
      'page callback' => 'bigfathom_core_viewlocation_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Location Form',
      'type' => MENU_CALLBACK,
      'file' => 'location.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editlocation'] = array(
      'title' => 'Edit Location',
      'page callback' => 'bigfathom_core_editlocation_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Location Form',
      'type' => MENU_CALLBACK,
      'file' => 'location.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletelocation'] = array(
      'title' => 'Delete Location',
      'page callback' => 'bigfathom_core_deletelocation_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Location Form',
      'type' => MENU_CALLBACK,
      'file' => 'location.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_uridomains(&$items, $weight=398, $menu_base='bigfathom/sitemanage/uridomain')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage URI Domains
    $items[$menu_base] = array(
      'title' => 'URI Domain Information Console',
      'page callback' => 'bigfathom_core_mng_uridomain_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about uri domains',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'uridomain.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/adduridomain'] = array(
      'title' => 'Add URI Domain',
      'page callback' => 'bigfathom_core_adduridomain_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add URI Domain Form',
      'type' => MENU_CALLBACK,
      'file' => 'uridomain.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewuridomain'] = array(
      'title' => 'View URI Domain',
      'page callback' => 'bigfathom_core_viewuridomain_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View URI Domain Form',
      'type' => MENU_CALLBACK,
      'file' => 'uridomain.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/edituridomain'] = array(
      'title' => 'Edit URI Domain',
      'page callback' => 'bigfathom_core_edituridomain_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit URI Domain Form',
      'type' => MENU_CALLBACK,
      'file' => 'uridomain.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteuridomain'] = array(
      'title' => 'Delete URI Domain',
      'page callback' => 'bigfathom_core_deleteuridomain_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete URI Domain Form',
      'type' => MENU_CALLBACK,
      'file' => 'uridomain.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_apptesting(&$items, $weight=398, $menu_base='bigfathom/sitemanage/apptesting')
{
    $page_arguments = array(
        'layout_name'=>'maxcontent_multiproject'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>FALSE
        ,'sysadmin_only'=>TRUE
    );
    
    $items[$menu_base] = array(
      'title' => 'Application Testing Information Console',
      'page callback' => 'bigfathom_core_mng_apptesting_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage the application tests',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'app_testing.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    
    $items["$menu_base/launch"] = array(
      'title' => 'Launch Application Tests',
      'page callback' => 'bigfathom_core_launch_apptesting_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Launch the application tests',
      'type' => MENU_CALLBACK,
      'file' => 'app_testing.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items["$menu_base/cancel"] = array(
      'title' => 'Cancel Application Tests',
      'page callback' => 'bigfathom_core_cancel_apptesting_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Cancel any running application tests',
      'type' => MENU_CALLBACK,
      'file' => 'app_testing.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_groups(&$items, $weight=322, $menu_base='bigfathom/sitemanage/groups')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Groups
    $items[$menu_base] = array(
      'title' => 'Group Information Console',
      'page callback' => 'bigfathom_core_mng_groups_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about groups',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addgroup'] = array(
      'title' => 'Add Group',
      'page callback' => 'bigfathom_core_addgroup_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Group Form',
      'type' => MENU_CALLBACK,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewgroup'] = array(
      'title' => 'View Group',
      'page callback' => 'bigfathom_core_viewgroup_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View Group Form',
      'type' => MENU_CALLBACK,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editgroup'] = array(
      'title' => 'Edit Group',
      'page callback' => 'bigfathom_core_editgroup_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit Group Form',
      'type' => MENU_CALLBACK,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletegroup'] = array(
      'title' => 'Delete Group',
      'page callback' => 'bigfathom_core_deletegroup_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Group Form',
      'type' => MENU_CALLBACK,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items["bigfathom/groups/membership"] = array(
      'title' => 'Manage Group Membership',
      'page callback' => 'bigfathom_core_mng_person2group_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments,array('context-high'=>'plan','context-low'=>'idea')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Map people to groups',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'groups.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
}

function bigfathom_core_submenu_sitewide_visionstatements(&$items, $weight=322, $menu_base='bigfathom/sitemanage/visionstatements')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage VisionStatements
    $items[$menu_base] = array(
      'title' => 'Vision Statement Information Console',
      'page callback' => 'bigfathom_core_mng_visionstatements_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about vision statements',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'visionstatements.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addvisionstatement'] = array(
      'title' => 'Add Vision Statement',
      'page callback' => 'bigfathom_core_addvisionstatement_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new vision statemement',
      'type' => MENU_CALLBACK,
      'file' => 'visionstatements.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewvisionstatement'] = array(
      'title' => 'View Vision Statement',
      'page callback' => 'bigfathom_core_viewvisionstatement_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View te vision statemement',
      'type' => MENU_CALLBACK,
      'file' => 'visionstatements.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editvisionstatement'] = array(
      'title' => 'Edit Vision Statement',
      'page callback' => 'bigfathom_core_editvisionstatement_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the vision statement',
      'type' => MENU_CALLBACK,
      'file' => 'visionstatements.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletevisionstatement'] = array(
      'title' => 'Delete Vision Statement',
      'page callback' => 'bigfathom_core_deletevisionstatement_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the vision statement',
      'type' => MENU_CALLBACK,
      'file' => 'visionstatements.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_holidays(&$items, $weight=322, $menu_base='bigfathom/sitemanage/holidays')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Holidays
    $items[$menu_base] = array(
      'title' => 'Holiday Information Console',
      'page callback' => 'bigfathom_core_mng_holidays_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about holidays',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'holidays.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addholiday'] = array(
      'title' => 'Add Holiday',
      'page callback' => 'bigfathom_core_addholiday_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new holiday entry',
      'type' => MENU_CALLBACK,
      'file' => 'holidays.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewholiday'] = array(
      'title' => 'View Holiday',
      'page callback' => 'bigfathom_core_viewholiday_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View the holiday information',
      'type' => MENU_CALLBACK,
      'file' => 'holidays.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editholiday'] = array(
      'title' => 'Edit Holiday',
      'page callback' => 'bigfathom_core_editholiday_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the holiday information',
      'type' => MENU_CALLBACK,
      'file' => 'holidays.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteholiday'] = array(
      'title' => 'Delete Holiday',
      'page callback' => 'bigfathom_core_deleteholiday_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the holiday information',
      'type' => MENU_CALLBACK,
      'file' => 'holidays.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_baseline_availability(&$items, $weight=322, $menu_base='bigfathom/sitemanage/baseline_availability')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Manage Holidays
    $items[$menu_base] = array(
      'title' => 'Baseline Availability Information Console',
      'page callback' => 'bigfathom_core_mng_baseline_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage information about baseline availability',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'baseline_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
      'menu_name' => 'navigation',
    );
    $items['bigfathom/addbaseline_availability'] = array(
      'title' => 'Add Baseline Availability',
      'page callback' => 'bigfathom_core_addbaseline_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add a new baseline availability statemement',
      'type' => MENU_CALLBACK,
      'file' => 'baseline_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewbaseline_availability'] = array(
      'title' => 'View Baseline Availability',
      'page callback' => 'bigfathom_core_viewbaseline_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View te vision statemement',
      'type' => MENU_CALLBACK,
      'file' => 'baseline_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editbaseline_availability'] = array(
      'title' => 'Edit Baseline Availability',
      'page callback' => 'bigfathom_core_editbaseline_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit the baseline availability',
      'type' => MENU_CALLBACK,
      'file' => 'baseline_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deletebaseline_availability'] = array(
      'title' => 'Delete Baseline Availability',
      'page callback' => 'bigfathom_core_deletebaseline_availability_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete the baseline availability',
      'type' => MENU_CALLBACK,
      'file' => 'baseline_availability.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_oneproject_score(&$items, $weight=205, $menu_base='bigfathom/projects/score')
{
    $page_arguments_finished = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
    );
    $page_arguments_unfinished = array(
        'layout_name'=>'maxcontent'
        ,'menu_base'=>$menu_base
        ,'requires_project'=>TRUE
        ,'unfinished'=>TRUE
    );
    
    
    $items[$menu_base] = array(
      'title' => 'Score and Audit',
      'page callback' => 'bigfathom_core_topinfoscore_form',
      'page arguments' => bigfathom_core_getPageArgumentArray($page_arguments_unfinished,array('context-high'=>'eval','context-low'=>'')),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Score Content',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'topinfo.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight,
    );

    $items["{$menu_base}/managetesterscore"] = array(
      'title' => 'Test Work Products',
      'page callback' => 'bigfathom_core_managetesterscore_form',
      'page arguments' => $page_arguments_unfinished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Test and Score Project Content',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'scoring.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items["{$menu_base}/managegoalscore"] = array(
      'title' => 'Assess Goal Status',
      'page callback' => 'bigfathom_core_managegoalscore_form',
      'page arguments' => $page_arguments_unfinished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Evaluate goal progress/completion quality',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'scoring.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items["{$menu_base}/managetaskscore"] = array(
      'title' => 'Assess Task Status',
      'page callback' => 'bigfathom_core_managetaskscore_form',
      'page arguments' => $page_arguments_unfinished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Evaluate task progress/completion quality',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'scoring.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items["{$menu_base}/manageauditscore"] = array(
      'title' => 'Audit Content',
      'page callback' => 'bigfathom_core_manageauditscore_form',
      'page arguments' => $page_arguments_unfinished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Audit Project Content',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'scoring.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_projects(&$items, $weight=301, $menu_base='bigfathom/sitemanage/projects')
{
    $page_arguments = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    
    //Project Console
    $items["{$menu_base}"] = array(
      'title' => 'Project Console',
      'page callback' => 'bigfathom_core_mng_projects_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Project information console',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );

    $items['bigfathom/addproject'] = array(
      'title' => 'Add Project',
      'page callback' => 'bigfathom_core_addproject_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add Project Form',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewproject'] = array(
      'title' => 'View Project',
      'page callback' => 'bigfathom_core_viewproject_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View project information',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/duplicateproject'] = array(
      'title' => 'Duplicate Project',
      'page callback' => 'bigfathom_core_duplicateproject_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Create a duplicate of a project',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/editproject'] = array(
      'title' => 'Edit Project',
      'page callback' => 'bigfathom_core_editproject_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit project information',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/deleteproject'] = array(
      'title' => 'Delete Project',
      'page callback' => 'bigfathom_core_deleteproject_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Project Form',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    //Project Communications Console
    $page_projectcomment_arguments = array('layout_name'=>'maxcontent','menu_base'=>'bigfathom/project/mng_comments');
    $items["bigfathom/project/mng_comments"] = array(
      'title' => 'Project Communications Console',
      'page callback' => 'bigfathom_core_mng_project_communications_form',
      'page arguments' => $page_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage project comments',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/project/addcomment'] = array(
      'title' => 'Add Project Communication',
      'page callback' => 'bigfathom_core_addproject_communication_form',
      'page arguments' => $page_projectcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add project comment',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/project/viewcomment'] = array(
      'title' => 'View Project Communication',
      'page callback' => 'bigfathom_core_viewproject_communication_form',
      'page arguments' => $page_projectcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View project comment',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/project/editcomment'] = array(
      'title' => 'Edit Project Communication',
      'page callback' => 'bigfathom_core_editproject_communication_form',
      'page arguments' => $page_projectcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit project comment',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/project/deletecomment'] = array(
      'title' => 'Delete Project Communication',
      'page callback' => 'bigfathom_core_deleteproject_communication_form',
      'page arguments' => $page_projectcomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete project comment',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}

function bigfathom_core_submenu_sitewide_templates(&$items, $weight=301, $menu_base='bigfathom/sitemanage/templates')
{
    $page_arguments_finished = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base);
    $page_arguments_unfinished = array('layout_name'=>'maxcontent_multiproject','menu_base'=>$menu_base,"unfinished"=>TRUE);
    
    //Template Console
    $items["{$menu_base}"] = array(
      'title' => 'Template Console',
      'page callback' => 'bigfathom_core_mng_templates_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Template information console',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );

    $items["bigfathom/sitemanage/maptemplatecontent"] = array(
      'title' => 'Template Content Workitem Dependencies',
      'page callback' => 'bigfathom_core_map_templatedependencies_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Show the template workitems by their dependencies',
      'type' => MENU_CALLBACK,
      'expanded' => TRUE,
      'file' => 'design.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
      'weight' => $weight++,
    );
    
    $items['bigfathom/importtemplate'] = array(
      'title' => 'Import Template',
      'page callback' => 'bigfathom_core_import_template_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Import a template from local or remote resource',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/viewtemplate'] = array(
      'title' => 'View Template',
      'page callback' => 'bigfathom_core_viewtemplate_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View template information',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/edittemplate'] = array(
      'title' => 'Edit Template',
      'page callback' => 'bigfathom_core_edittemplate_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit template information',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    $items['bigfathom/deletetemplate'] = array(
      'title' => 'Delete Template',
      'page callback' => 'bigfathom_core_deletetemplate_form',
      'page arguments' => $page_arguments_finished,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete Template Form',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    
    //Template Communications Console
    $page_templatecomment_arguments = array('layout_name'=>'maxcontent','menu_base'=>'bigfathom/template/mng_comments');
    $items["bigfathom/template/mng_comments"] = array(
      'title' => 'Template Communications Console',
      'page callback' => 'bigfathom_core_mng_template_communications_form',
      'page arguments' => $page_templatecomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Manage template comments',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/template/addcomment'] = array(
      'title' => 'Add Template Communication',
      'page callback' => 'bigfathom_core_addtemplate_communication_form',
      'page arguments' => $page_templatecomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Add template comment',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/template/viewcomment'] = array(
      'title' => 'View Template Communication',
      'page callback' => 'bigfathom_core_viewtemplate_communication_form',
      'page arguments' => $page_templatecomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'View template comment',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/template/editcomment'] = array(
      'title' => 'Edit Template Communication',
      'page callback' => 'bigfathom_core_edittemplate_communication_form',
      'page arguments' => $page_templatecomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Edit template comment',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
    $items['bigfathom/template/deletecomment'] = array(
      'title' => 'Delete Template Communication',
      'page callback' => 'bigfathom_core_deletetemplate_communication_form',
      'page arguments' => $page_templatecomment_arguments,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Delete template comment',
      'type' => MENU_CALLBACK,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_CORE_MODULE_PATH.'/functions/',
    );
}
