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
class SelectExistingProjectPage extends \bigfathom\ASimpleHelpPage
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

        $url_sepfdash_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_sepfdash_1');
        
        $url_sepfpw_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_sepfpw_1');
        $url_sepfpw_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_sepfpw_2');

        $selectproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('pin');
        $selectproject_current_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('pinned_project');
        $unselected_pin_img_markup = "<img src='$selectproject_icon_url' title='unselected pin icon'>";    
        $selected_pin_img_markup = "<img src='$selectproject_current_icon_url' title='selected pin icon'>"; 
        
        $app_context = "<div class='about-app'>"
                . "<h1>How to Select an Existing Project in Bigfathom</h1>"
                . "</div>";
        
        $form['data_entry_area1']["app_context"]    = array(
            '#type' => 'item',
            '#markup' => $app_context,
        );

        $form['data_entry_area1']["main_intro"]    = array(
            '#type' => 'item',
            '#markup' => '<p class="helpblurb">To work with project content, you must first select a project.'
            . '  You can tell what project is selected from most interfaces of the application by looking at the top of the page'
            . ' where you will normally see the name and a short mission statement displayed for the selected project.'
            . '  There are two ways to select an existing project; one way is to select one "from dashboard"'
            . ' and the other is to "select from Project Work tab".</p>',
        );
        
        $link2dashboard = l('Dashboard','bigfathom/topinfo/dashboard');
        $link2projectwork = l('Project Work','bigfathom/topinfo/projects');

        $link2selectproject = $this->getProjectDependentMarkup('Select or Create a Locally Managed Project','bigfathom/projects/userselectone');
        
        
        $help_page_title = "How to Select an Existing Project from Dashboard";
        $help_page_blurb = "For convenience, existing projects can simply be selected by clicking options directly on the dashboard panel of the desired project.";
        
        $main_body_ar = [];
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_sepfdash_1'></td><td>Click on the $link2dashboard tab."
                . "  This is the up-trend-chart icon <i class='fa fa-line-chart'></i> also available from the top right of some pages."
                . "   Click on any of the available buttons in the panel of your choice to both select the project and jump to the indicated activity area."
                . "<ul>"
                . "<li>brainstorm -- Selects the project and jumps to the brainstorming interface of the project"
                . "<li>activity menu -- Selects the project and jumps to the Activity Menu with no console selected"
                . "<li>times and durations -- Selects the project and jumps to the Work Effort and Duration Grid for the project"
                . "</ul>"
                . "<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>How to Select an Existing Project from Project Work Tab</h3>";
        $main_body_ar[] = "<p>Existing projects can be selected from the same console that is used to create them on the Project Work tab.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_sepfpw_1'></td>"
                . "<td>Click on the $link2selectproject option on the $link2projectwork tab.  This tab has the rocket icon <i class='fa fa-rocket'></i> also available from the top right of some pages.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_sepfpw_2'></td>"
                . "<td>Click on the pin icon $unselected_pin_img_markup from the Action Options column on the row of the project you want to select.  (The selected project, if any, will have a pin icon in a circle $selected_pin_img_markup in the Action Options column.)<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;

    }
}
