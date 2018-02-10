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
 */

namespace bigfathom_help;

require_once 'ASimpleHelpPage.php';

/**
 * Help for specific user type
 *
 * @author Frank Font
 */
class HelpUserBAPage extends \bigfathom\ASimpleHelpPage
{
    function __construct($urls_arr=NULL)
    {
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }   
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($urls_arr);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return array();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {

        $help_page_title = 'Business Analysts: Getting started with Bigfathom';
        $help_page_blurb = "This will be home to Business Analysts to get started with the parts of the application that matter most to them.";
                
        $main_body_ar[] = "<ul>"
                        ."<li>How to document organizations business needs – Startegic planning"
                        ."<li>How to write/update down requirements"
                        ."<li>How to model requirements (knead) – with flow diagrams and mock-ups"
                        ."<li>How to review test cases"
                        ."<li>How to put together training materials"
                        ."<li>How to assign tasks (work items) or receive"
                        ."<li>How to generate reports from requirements (for requirements documentation for sign-offs)"
                        ."</ul>";

        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
        
    }
}
