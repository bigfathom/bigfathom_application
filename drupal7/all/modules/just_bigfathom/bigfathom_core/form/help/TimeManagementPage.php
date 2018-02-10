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
 * View help about time management
 *
 * @author Frank Font
 */
class TimeManagementPage extends \bigfathom\ASimpleHelpPage
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

        $help_page_title = 'Time Management Concepts in Bigfathom';
        $help_page_blurb = "The application plots workitems into a time management matrix having four quadrants (Q1,Q2,Q3,Q4)."
                . " The time management matrix conventions employed in the application are consistent"
                . " with Stephen Covey's 7 Habits presentation and you are encouraged to familiarize yourself with the wonderful ideas presented there.";
        
        $q1text = "<strong>IMPORTANT</strong> and <strong>URGENT</strong> too; if this is not completed now, then we fail."
                . "  If you find yourself here often, you risk burn-out.";
        $q2text = "<strong>IMPORTANT</strong> and not yet urgent.  We still have some time to complete this successfully."
                . "  This is where you want to live your life, all parts of it.  If you find yourself here often, you are doing something right!";
        $q3text = "Not important but must be completed now if we are going to complete this on time.";
        $q4text = "Not important and not urgent.  There is time to complete this and it is not very important to complete anyways.";
        
        $main_body_ar[] = "<table class='tmm'>";
        $main_body_ar[] = "<tr><td></td><th class='tmm nowrap'>URGENT</th><th class='tmm nowrap'>NOT URGENT</th></tr>";
        $main_body_ar[] = "<tr><th class='tmm'>IMPORTANT</th><td class='tmmq1'>Q1<p class='tmm'>$q1text</p></td>"
                . "<td class='tmmq2'>Q2<p class='tmm'>$q2text</p></td></tr>";
        $main_body_ar[] = "<tr><th class='tmm'>NOT IMPORTANT</th><td class='tmmq3'>Q3<p class='tmm'>$q3text</p></td>"
                . "<td class='tmmq4'>Q4<p class='tmm'>$q4text</p></td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
        
    }
}
