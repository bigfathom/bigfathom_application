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
 */

function bigfathom_template_library_menu_core_callbacks(&$items)
{
    $items['bigfathom/templates/exportjson'] = array(
      'title' => 'JSON Format Export',
      'page callback' => 'bigfathom_template_library_exportjson_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Export data in a JSON format',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_TEMPLATE_LIBRARY_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/templates/importjson'] = array(
      'title' => 'JSON Format Import',
      'page callback' => 'bigfathom_template_library_importjson_form',
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Import data from a JSON format',
      'type' => MENU_CALLBACK,
      'file' => 'io.inc',
      'file path' => BIGFATHOM_TEMPLATE_LIBRARY_MODULE_PATH.'/functions/',
    );

    $items['bigfathom/templates/select'] = array(
      'title' => 'Explore Templates',
      'page callback' => 'bigfathom_template_library_select_form',
      'page arguments' => array('menupage'),
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Explore the library of templates',
      'type' => MENU_NORMAL_ITEM,
      'expanded' => TRUE,
      'file' => 'templates.inc',
      'file path' => BIGFATHOM_TEMPLATE_LIBRARY_MODULE_PATH.'/functions/',
      'weight' => 800,
    );
}
