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
 * View HOW2 help information
 *
 * @author Frank Font
 */
class NewSprintPage extends \bigfathom\ASimpleHelpPage
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

        $link2settings = l('Settings','bigfathom/topinfo/sitemanage');
        $link2projects = l('Project Work','bigfathom/topinfo/projects');
        $link2sprints = $this->getProjectDependentMarkup('Sprint Console','bigfathom/projects/sprints');
        
        
        $sprint_membership_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('sprint_membership');

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        $sprint_membership_icon_markup = "<img alt='the icon' src='$sprint_membership_icon_url' />";
        
        $url_cnsprint_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnsprint_1');
        $url_cnsprint_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnsprint_2');
        $url_cnsprint_3a = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnsprint_3a');
        $url_cnsprint_3b = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnsprint_3b');


        $url_editsprint_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editsprint_1');
        $url_editsprint_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editsprint_members_1');
        $url_editsprint_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editsprint_members_2');
        $url_editsprint_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editsprint_members_3');
        $url_editsprint_5 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editsprint_members_4');
        
        $help_page_title = 'How to Declare Sprints in Bigfathom';
        $help_page_blurb = "The workitems of any project can be 'chunked' together in a collective effort period known as a 'sprint'.";
        
        $main_body_ar = [];
        
        $main_body_ar[] = "<h3>How to Create a New Sprint</h3>";
        $main_body_ar[] = "<p>You can create a sprint at any time once a project has been selected.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnsprint_1'></td>"
                . "<td>Click on the $link2projects tab.  This is the rocket icon $rocket_icon_markup also available from the top right of some pages.  On that page, click the $link2sprints option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnsprint_2'></td>"
                . "<td>Click on the Add Sprint button shown under the table area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_cnsprint_3a'></td>"
                . "<td>Fill in the form.  If you don't provide a custom sprint name, the application will automatically call the sprint 'Sprint #' where # is the Iteration number.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_cnsprint_3b'></td>"
                . "<td>Save your changes to return to the table console of existing sprints.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>How to Manage Content of a Sprint</h3>";
        $main_body_ar[] = "<p>You can then populate any created sprint with workitems to be completed by clicking the membership icon on the Sprint Console page.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing action option icon' src='$url_editsprint_1'></td>"
                . "<td>Click on the membership icon icon $sprint_membership_icon_markup in the Action Options column of the row of the sprint you want to edit.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_editsprint_2'></td>"
                . "<td>Notice there are two probability columns: OTSP is the On-Time Success Probability of the workitem in the project, ISCP is the IN-SPRINT Confidence Probability that we can complete the work within the sprint period."
                . "  You should only select workitems that you are confident have a high probability of completion during the sprint period.  The ISCP value helps you choose what to include.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing list in the form' src='$url_editsprint_3'></td>"
                . "<td>Select sprint members by marking them with a checkmark in the Member column.  Your selections (and un-selections) are saved as you make them.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_editsprint_4'></td>"
                . "<td>Notice that summary information about your selections is displayed in the panel above the table area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 5</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_editsprint_5'></td>"
                . "<td>You can freeze membership changes by clicking the 'Lock Membership' button at the bottom of the page.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
