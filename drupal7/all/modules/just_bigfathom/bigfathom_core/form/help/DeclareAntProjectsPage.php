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
class DeclareAntProjectsPage extends \bigfathom\ASimpleHelpPage
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
        $link2antprojconsole = $this->getProjectDependentMarkup('Manage Antecedent Projects Console','bigfathom/projects/declaresubprojects');
        $link2convert = $this->getProjectDependentMarkup('Workitem Type Conversions','bigfathom/projects/workitems/changeworkitemtype');
        

        $icon_intoproj_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intoproject');

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        $icon_intoproj_markup = "<img alt='the conversion icon' src='$icon_intoproj_url' />";
        
        $url_cnsprint_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_ant_proj_declare_0');
        $url_cnsprint_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_ant_proj_declare_1');
        $url_cnsprint_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_ant_proj_declare_2');

        $url_editsprint_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_workitem_type_convert_0');
        $url_editsprint_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_workitem_type_convert_1');
        $url_editsprint_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_proj_workitem_type_convert_2');
        
        $help_page_title = 'How to Declare Antecedent Projects in Bigfathom';
        $help_page_blurb = "An antecedent project can be thought of as a 'subproject' although there is no limitation on the number of projects that"
                . " can depend on an antecedent project.  There are three ways to declare an antecedent project in Bigfathom."
                . "<ul>"
                . "<li><a href='#HELP_SECTION_DEPEXISTING'>Declare a dependency on an existing project</a>"
                . "<li><a href='#HELP_SECTION_SCRATCH'>Create an antecedent project from scratch</a>"
                . "<li><a href='#HELP_SECTION_CONVERT'>Convert the goal (a branch) of an existing project into an antecedent project</a>"
                . "</ul>"
                . "Consider breaking overly complex projects into collections of linked projects where doing so is possible."
                . "  It can bring clarity and insight that might otherwise be lost in a tangle of detail.";
        
        $main_body_ar = [];
        
        $main_body_ar[] = "<h3 id='HELP_SECTION_DEPEXISTING'>Declare a Dependency on an Existing Project</h3>";
        $main_body_ar[] = "<p>You can create a sprint at any time once a project has been selected.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnsprint_1'></td>"
                . "<td>Click on the $link2projects tab.  This is the rocket icon $rocket_icon_markup also available from the top right of some pages.  On that page, click the $link2antprojconsole option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing checkboxes' src='$url_cnsprint_2'></td>"
                . "<td>The existing projects in the application are listed in the table."
                . "  This interface offers checkboxes for linking existing projects and a button for creating a new project.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing checked box' src='$url_cnsprint_3'></td>"
                . "<td>Click on the checkbox in the Antecedent column for the project(s) you want to add as antecedents to the current project."
                . "  Afterwards, you will be able to declare a dependency to that project from whatever workitems you choose.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3 id='HELP_SECTION_SCRATCH'>Create an Antecedent Project from Scratch</h3>";
        $main_body_ar[] = "<p>You can create antecedent projects from buttons on either the $link2antprojconsole interface or the $link2convert."
                . "  In this example, we will use the $link2convert interface.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_editsprint_1'></td>"
                . "<td>Click on the $link2projects tab.  This is the rocket icon $rocket_icon_markup also available from the top right of some pages.  On that page, click the $link2convert option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_editsprint_2'></td>"
                . "<td>Look for the <strong>Create New Antecedent Project</strong> button at the bottom of the page.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_ar[] = "<h3 id='HELP_SECTION_CONVERT'>Convert the goal (a branch) of an existing project into an antecedent project</h3>";
        $main_body_ar[] = "<p>You can convert existing goals (branches) of your project into connected antecedent projects via the $link2convert interface."
                . "  You can also reverse the project if you later change your mind and would rather manage all the detail in one project."
                . "  The process can also be used to merge multiple projects into a single project.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_editsprint_1'></td>"
                . "<td>Click on the $link2projects tab.  This is the rocket icon $rocket_icon_markup also available from the top right of some pages.  On that page, click the $link2convert option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_editsprint_2'></td>"
                . "<td>Notice the icons in the Action Options column at the far right of the table.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_editsprint_3'></td>"
                . "<td>Click on the conversion icon $icon_intoproj_markup of the goal you want to convert into an antecedent project."
                . "  You will then be prompted for some additional metadata, such as who will be the project leader, before the conversion takes place.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
