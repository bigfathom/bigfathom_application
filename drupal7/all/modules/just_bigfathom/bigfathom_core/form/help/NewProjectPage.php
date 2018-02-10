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
class NewProjectPage extends \bigfathom\ASimpleHelpPage
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

        $url_cnp_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnp_1');
        $url_cnp_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnp_2');
        $url_cnp_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnp_3');
        $url_cnp_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnp_4');
        
        $url_cnpfromt_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnpfromt_1');
        $url_cnpfromt_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnpfromt_2');
        $url_cnpfromt_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnpfromt_3');
        $url_cnpfromt_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnpfromt_4');

        $link2projectwork = l('Project Work','bigfathom/topinfo/projects');
        $link2createproject = l('Select or Create a Locally Managed Project','bigfathom/projects/userselectone');
        $link2docreateproject = l('Create New Top Level Project','bigfathom/projects/addtop');
        $link2settings_tab = l('Settings Tab','bigfathom/topinfo/sitemanage');
        $link2systemwide_projects_console = l('Projects Console','bigfathom/sitemanage/projects');

        $help_page_title = 'How to Create a New Project in Bigfathom';
        $help_page_blurb = 'If your application administrator has given you the right to create projects'
            . ', you can create them at any time by following the steps illustrated here.'
            . '  There are two ways to create a new project; one way is to create'
                . ' one "from scratch" and the other is to create a project from an existing template.';
        
        $tracking_level_tx = "Fill in the form starting with the insight tracking level declaration at the top."
                . "  Your choice there will determine what additional data you will be asked to provide."
                . "<ul>"
                . "<li><strong>Deep</strong>"
                . "<p>This indicates you will track the work breakdown structure for the project here in your application instance.</p>"
                . "<li><strong>Superficial</strong>"
                . "<p>This indicates you will NOT track the work breakdown structure for the project here in your application instance."
                . "  Instead, you will simply track the status of the project."
                . "  This type of project can only be referenced in the system as a sub-project for dependency chaining purposes."
                . "(Look on the $link2systemwide_projects_console of the $link2settings_tab area for any existing superficial projects if you want to edit their content.)</p>"
                . "</ul>";
        
        $main_body_ar = [];
        $main_body_ar[] = "<h3>How to Create a New Project from Scratch</h3>";
        $main_body_ar[] = "<p>When you create a new project from scratch the contents of the project will be empty with the exception of one root goal.  The new project is ready for you to add all your goals and tasks.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr>"
                . "<th>Step 1</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnp_1'></td>"
                . "<td>Click on the $link2projectwork tab.  This is the rocket icon <i class='fa fa-rocket'></i> also available from the top right of some pages.  Click on the $link2createproject option shown at the top in that tab area.<td></tr>";
        $main_body_ar[] = "<tr>"
                . "<th>Step 2</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_cnp_2'></td>"
                . "<td>Click on the $link2docreateproject button from the 'Select or Create a Locally Managed Project' page.<td></tr>";
        $main_body_ar[] = "<tr>"
                . "<th>Step 3</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing top of form' src='$url_cnp_3'></td>"
                . "<td>$tracking_level_tx<td></tr>";
        $main_body_ar[] = "<tr>"
                . "<th>Step 4</th>"
                . "<td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_cnp_4'></td>"
                . "<td>To save your new project, click the 'Save New Project' button at the bottom of the form.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $link2settings = l('Settings','bigfathom/topinfo/sitemanage');
        $link2templates = l('Template Console','bigfathom/sitemanage/templates');
        $createproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('createprojectfromtemplate');
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        
        $main_body_ar[] = "<h3>How to Create a New Project from an existing Template</h3>";
        $main_body_ar[] = "<p>When you create a new project from an existing template the contents of the project contain all the existing goals, tasks, and declared dependencies of the template.  The new project is ready for you to then make all the edits you like with the advantage of a significant headstart in planning and insight.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnpfromt_1'></td><td>Click on the $link2settings tab.  This is the cog icon <i class='fa fa-cog'></i> also available from the top right of some pages.  Click on the $link2templates option shown in that tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_cnpfromt_2'></td><td>Click on the Action Option symbol of an arrow pointing to a circle <img alt='the icon' src='$createproject_icon_url' /> on the row of the template from which you wan to create a new project. (To learn more about the template before picking it, click the eye icon <img alt='the icon' src='$view_icon_url' />  to see some information about it.)<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing top of form' src='$url_cnpfromt_3'></td><td>Fill in the form starting with the name you would like to give the new project.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_cnpfromt_4'></td><td>To save your new project, click the 'Save Values to Create New Project' button at the bottom of the form.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
