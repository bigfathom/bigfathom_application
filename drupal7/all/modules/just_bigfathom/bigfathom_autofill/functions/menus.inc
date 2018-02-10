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

function bigfathom_autofill_menu_core_callbacks(&$items)
{
    $page_arguments_multiproj = array('layout_name'=>'maxcontent_multiproject');
    $items['bigfathom/autofill/projectdata'] = array(
      'title' => 'Autofill Project Data',
      'page callback' => 'bigfathom_autofill_projectdata_form',
      'page arguments' => $page_arguments_multiproj,
      'access arguments' => array('access content'),
      'access callback' => TRUE,        
      'description' => 'Automatically fill in reasonable values fitting declared constraints of a project',
      'type' => MENU_CALLBACK,
      'file' => 'projects.inc',
      'file path' => BIGFATHOM_AUTOFILL_MODULE_PATH.'/functions/',
    );
}
