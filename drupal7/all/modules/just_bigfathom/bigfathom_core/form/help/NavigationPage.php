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
 * View privacy statement about the application
 *
 * @author Frank Font
 */
class NavigationPage extends \bigfathom\ASimpleHelpPage
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
        $link2projectwork = l('Project Work','bigfathom/topinfo/projects');
        $link2reports = l('Reports','bigfathom/topinfo/reports');

        $link2brainstorm = $this->getProjectDependentMarkup('Topic Proposals','bigfathom/projects/design/brainstormcapture');
        $link2effortandduration = $this->getProjectDependentMarkup('Workitems Effort and Duration Grid','bigfathom/projects/workitems/duration');
        $link2usecasesreport = $this->getProjectDependentMarkup('Use Case Mapping in Current Project','bigfathom/reports/usecasesoneproject');
        $link2testcasesreport = $this->getProjectDependentMarkup('Test Case Mapping in Current Project','bigfathom/reports/testcasesoneproject');

        
        $link2help_selectproject = l('How to Select an Existing Project from Dashboard','bigfathom/help/selectproject');
        $link2help_comms = l('How to Communicate-In-Context','bigfathom/help/comms');
        
        $link2help_tmm = l('Time Management Concepts in Bigfathom','bigfathom/help/tmm');
        $link2help_deps = l('Workitem Dependencies in Bigfathom','bigfathom/help/setworkdeps');
        
        $help_page_title = 'Navigation in Bigfathom';
        $help_page_blurb = "The application has two general formats in which it displays content"
                . ", a wide format with navigation icons at the top right and a narrow content format with navigation tabs on the left."
                . "  The icons in both the narrow and the wide presentations are the same; the icons presented in the tabs include the names to their right"
                . ", the icons of the wide format presented at the top of the page have helpful text displayed when you hover over them."
                . "  (This page is a wide format page with the icons shown at the top right of the page, hover over them now to see the helpful text.)";

        $dash_icon_markup = "<i class='fa fa-line-chart'></i>";
        $projectwork_icon_markup = "<i class='fa fa-rocket'></i>";
        $settings_icon_markup = "<i class='fa fa-cog'></i>";
        $reports_icon_markup = "<i class='fa fa-map-o'></i>";
        $youraccount_icon_markup = "<i class='fa fa-user-circle-o'></i>";
        $help_icon_markup = "<i class='fa fa-book'></i>";

        $sample_nav_leftbar_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_leftbar_1');
        $sample_nav_top_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_top_1');
        $sample_nav_topbar_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_topbar_1');
        
        $sample_nav_dash_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_dash_1');
        $sample_nav_dash_2_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_dash_2');
        $sample_nav_dash_1_leftnav_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_dash_1_leftnav');

        $sample_nav_dash_1_subnav_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_dash_1_subnav');
        $sample_nav_dash_1_dashjump_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_dash_1_dashjump');

        $sample_nav_dash_1_noproject_selected_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_dash_1_noproject_selected');
        $sample_nav_dash_1_yesproject_selected_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_dash_1_yesproject_selected');
        
        $sample_visualbrainstorm_subtab_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_visualbrainstorm_subtab_1');
        $sample_visualbrainstorm_subtab_2_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_visualbrainstorm_subtab_2');
        $sample_leftnavtab_1_projectwork_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_leftnavtab_1_projectwork');
        
        $sample_nav_reports_1_topjump_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_reports_1_topjump');

        $layout_skeleton_nav_narrow_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('layout_skeleton_nav_narrow_1');
        $layout_skeleton_nav_wide_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('layout_skeleton_nav_wide_1');

        $sample_nav_dash_1_noproject_selected_markup = "<img class='click-zoom' alt='sample with no project selected' src='$sample_nav_dash_1_noproject_selected_url' />";
        $sample_nav_dash_1_noproject_selected_tx = "<strong>Indication that NO PROJECT has yet been selected:</strong>"
                . " Look at the top of the page between the Bigfathom logo on the left and the logout link at the right."
                . " When that area at the top of the page simply displays a gray panel inside a light blue panel, no project has been selected yet."
                . " (You can quickly select a project there simply by clicking one of the three buttons in the project panel of your choice."
                . "  See $link2help_selectproject for more details.)"
                . "<p>NOTE: Some options only appear in the Project Work and Reports tabs when a project is selected.</p>";
        
        $sample_nav_dash_1_yesproject_selected_markup = "<img class='click-zoom' alt='sample with project selected' src='$sample_nav_dash_1_yesproject_selected_url' />";
        $sample_nav_dash_1_yesproject_selected_tx = "Alternatively, notice that when on the dashboard tab AND a project has"
                . " been selected you have a blue panel inside the light blue outer panel.  The outer panel area shows the name of the"
                . " selected project and the smaller panel contains a short description of that project."
                . "  (The Project Work tab shows the same panel area when a project is selected.)";
        
        
        $layout_skeleton_nav_narrow_1_markup = "<img class='click-zoom' alt='the subtab' src='$layout_skeleton_nav_narrow_1_url' />";
        $layout_skeleton_nav_narrow_1_tx = "This diagram illustrates the fundamental areas of a narrow-format page in the application."
                . "<ul>"
                . "<li>TABS<p>These are the primary navigation tabs found along the left vertically organized.<p>"
                . "<li>LOGOUT<p>There is always a logout icon in the upper right."
                . "  You should click that to exit the application so that others cannot use the application using your credentials.</p>"
                . "<li>Content Area<p>The content of a narrow-format page appears to the right of the navigation tabs."
                . "  The content area contains options and information relevant to the selected topic tab selected.<p>"
                . "</ul>";
        
        $layout_skeleton_nav_wide_1_markup = "<img class='click-zoom' alt='the subtab' src='$layout_skeleton_nav_wide_1_url' />";
        $layout_skeleton_nav_wide_1_tx = "This diagram illustrates the fundamental areas of a wide-format page in the application."
                . "<ul>"
                . "<li>BREADCRUMB AREA<p>These are clickable links that navigate you back to earlier visited areas of the application."
                . "  The first link is always the keyword 'Home'.  (Tip: Clicking the logo at the top left of the page takes you to the same place.)<p>"
                . "<li>LOGOUT<p>There is always a logout icon in the upper right."
                . "  You should click that to exit the application so that others cannot use the application using your credentials.</p>"
                . "<li>ICON AREA<p>These are the primary navigation links found just under the logout icon at the top right of the page."
                . "  The icons displayed here are identical to the icons used in the narrow-format navigation tabs.<p>"
                . "<li>Content Area<p>The content of a wide-format page covers all the available screen space from the left margin to the right margin of your display."
                . "  The content area contains options and information relevant to the selected topic.<p>"
                . "</ul>";
        
        $sample_visualbrainstorm_subtab_1_markup = "<img class='click-zoom' alt='the subtab' src='$sample_visualbrainstorm_subtab_1_url' />";
        $sample_visualbrainstorm_subtab_2_markup = "<img class='click-zoom' alt='the subtab' src='$sample_visualbrainstorm_subtab_2_url' />";
        $sample_leftnavtab_1_projectwork_markup = "<img class='click-zoom' alt='the subtab' src='$sample_leftnavtab_1_projectwork_url' />";
        
        $sample_nav_leftbar_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_leftbar_1_url' />";
        $sample_nav_leftbar_1_tx = "<ul>"
                . "<li>Dashboard<p>This is where you find some summaries of current project status, your worklists, and your utilization summary information.</p>"
                . "<li>Project Work<p>This is where you find options to create and collaborate on project specific activities.</p>"
                . "<li>Settings<p>This is where you find options to mange site-wide information such as group memberships, role definitions, etc.</p>"
                . "<li>Reports<p>This is where you find reports that provide insight into information available from the application.</p>"
                . "<li>Your Account<p>Manage your user profile here.</p>"
                . "<li>Help<p>The available help pages are accessible from this tab.</p>"
                . "</ul>";

        $sample_nav_topbar_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_topbar_1_url' />";
        $sample_nav_topbar_1_tx = "<ul>"
                . "<li>$dash_icon_markup : Dashboard<p>This is where you find some summaries of current project status, your worklists, and your utilization summary information.</p>"
                . "<li>$projectwork_icon_markup : Project Work<p>This is where you find options to create and collaborate on project specific activities.</p>"
                . "<li>$settings_icon_markup : Settings<p>This is where you find options to mange site-wide information such as group memberships, role definitions, etc.</p>"
                . "<li>$reports_icon_markup : Reports<p>This is where you find reports that provide insight into information available from the application.</p>"
                . "<li>$youraccount_icon_markup : Your Account<p>Manage your user profile here.</p>"
                . "<li>$help_icon_markup : Help<p>The available help pages are accessible from this tab.</p>"
                . "</ul>";

        $sample_nav_dash_1_subnav_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_dash_1_subnav_url' />";
        $sample_nav_dash_1_subnav_tx = "<ul>"
                . "<li>Time Management<p>This sub-topic area shows a"
                . " breakdown of workitems and action requests organized into meaningfully categorized aggregations, such as Time Management Matrices (TMM - see $link2help_tmm)"
                . "; and shows summary information panels of each project to which you are currently associated.</p>"
                . "<li>Sorted Worklist<p>This sub-topic area shows a"
                . " sortable table of non-terminal status workitems asociated with you."
                . "  Each workitem is shown with a computed 'Urgency Score' such that a higher value implies a greater urgency in resolving the item.</p>"
                . "<li>Utilization<p>This sub-topic area shows a"
                . " sortable table of of time-periods in which you are scheduled to perform some work and an assessment of how utilized you are during that period.</p>"
                . "</ul>";

        $sample_nav_dash_1_dashjump_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_dash_1_dashjump_url' />";
        $sample_nav_dash_1_dashjump_tx = "<ul>"
                . "<p>Each panel has three buttons, any of those buttons will select the project"
                . " identified by the title of the panel and will jump out of the dashboard page into the interface corresponding to the button clicked.</p>"
                . "<li>Brainstorm"
                . " <p>Clicking this button selects the project of the panel and jumps to the $link2brainstorm visual brainstorming area. "
                . "<br>$sample_visualbrainstorm_subtab_1_markup</p>"
                . "<li>Activity Menu"
                . " <p>Clicking this button selects the project of the panel and jumps to the $link2projectwork topic area."
                . "<br>$sample_leftnavtab_1_projectwork_markup"
                . "<li>Times and Duration"
                . " <p>Clicking this button selects the project of the panel and jumps to the $link2effortandduration page.</p>"
                . "</ul>";

        //$sample_visualbrainstorm_subtab_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_visualbrainstorm_subtab_1');
        //$sample_visualbrainstorm_subtab_1_markup = "<img alt='the icon' src='$sample_visualbrainstorm_subtab_1_url' />";
        $sample_visualbrainstorm_subtab_1_tx = "When the Topic Proposals sub-tab is selected you are in the mode to visually collaborate on"
                . " the identification of topic items for your project.";

        $sample_nav_topics_1_allnav_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_topics_1_allnav');
        $sample_nav_topics_1_allnav_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_topics_1_allnav_url' />";
        $sample_nav_topics_1_allnav_tx = "The Topic Proposal collaboration area has the standard breadcrumbs above the subtabs and at the top right the standard logout and topic navigation icons."
                . "  At the bottom of the the page you will also find two additional navigation buttons: Jump to Table Console and Exit."
                . "<ul>"
                . "<li>Jump to Table Console"
                . " <p>This takes you to a tabular version of the information displayed in the visual collaboration area."
                . "  That page also has a Jump button to return you back to the visual collaboration page.</p>"
                . "<li>Exit"
                . " <p>This exits the Topic Proposal visual collaboration page.</p>"
                . "</ul>";

        //$sample_visualbrainstorm_subtab_2_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_visualbrainstorm_subtab_2');
        //$sample_visualbrainstorm_subtab_2_markup = "<img alt='the icon' src='$sample_visualbrainstorm_subtab_2_url' />";
        $sample_visualbrainstorm_subtab_2_tx = "When the Workitem Dependencies sub-tab is selected you are in the mode to visually collaborate on"
                . " the identification and mangement of workitem dependencies for your project. (See $link2help_deps for additional information on this subject.)";

        $sample_nav_deps_1_allnav_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_nav_deps_1_allnav');
        $sample_nav_deps_1_allnav_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_deps_1_allnav_url' />";
        $sample_nav_deps_1_allnav_tx = "The Workitem Dependencies collaboration area has the standard breadcrumbs above the subtabs and at the top right the standard logout and topic navigation icons."
                . "  At the bottom of the the page you will also find two additional navigation buttons: Jump to Duration Table and Exit."
                . "<ul>"
                . "<li>Jump to Duration Table"
                . " <p>This takes you to a tabular version of the information displayed in the visual collaboration area."
                . "  That page also has a Jump button to return you back to the visual collaboration page.</p>"
                . "<li>Exit"
                . " <p>This exits the Topic Proposal visual collaboration page.</p>"
                . "</ul>";

        $sample_nav_dash_1_leftnav_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_dash_1_leftnav_url' />";
        $sample_nav_dash_1_leftnav_tx = "In the sample narrow format page shown here everything has been dimmed except for the icons and their labels visible at the left of the page."
                . "  When you are on a narrow format page with tabs like that, you can jump to any of the topic areas simply by clicking on the relevant icon or its label."
                . "  The currently selected tab in this sample is the Dashboard.";

        $sample_nav_reports_1_topjump_markup = "<img class='click-zoom' alt='the icon' src='$sample_nav_reports_1_topjump_url' />";
        $sample_nav_reports_1_topjump_tx = "In the sample wide format page shown here everything has been dimmed except for the topic navigation icons"
                . " visible at the top right of the page."
                . "  When you are on a wide format page, you can jump to any of the topic areas simply by clicking on the icon."
                . "  Hover over any one of them to see a the name as a hover-tip.";

        //NARROW
        $main_body_ar[] = "<h3>Navigating Narrow Format Pages</h3>";
        $main_body_ar[] = "<p>The narrow format pages have navigation icons and their labels displayed vertically along the left side of the page.</p>";
        $main_body_ar[] = "<table class='how2steps-bigpic'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_dash_1_leftnav_markup</th>"
                . "<td>$sample_nav_dash_1_leftnav_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_leftbar_1_markup</th>"
                . "<td>$sample_nav_leftbar_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$layout_skeleton_nav_narrow_1_markup</th>"
                . "<td>$layout_skeleton_nav_narrow_1_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        //WIDE
        $main_body_ar[] = "<h3>Navigating Wide Format Pages</h3>";
        $main_body_ar[] = "<p>The wide format pages have navigation icons, without labels, displayed horizontally along the top right of the page, just under the Logout link.</p>";
        $main_body_ar[] = "<table class='how2steps-bigpic'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_reports_1_topjump_markup</th>"
                . "<td>$sample_nav_reports_1_topjump_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_topbar_1_markup</th>"
                . "<td>$sample_nav_topbar_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$layout_skeleton_nav_wide_1_markup</th>"
                . "<td>$layout_skeleton_nav_wide_1_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        //DASHBOARD
        $main_body_ar[] = "<h3>Navigating the Dashboard Topic Area</h3>";
        $main_body_ar[] = "<p>The dashboard topic area is presented as a narrow format page containing three sub-areas.</p>";
        $main_body_ar[] = "<table class='how2steps-bigpic'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_dash_1_subnav_markup</th>"
                . "<td>$sample_nav_dash_1_subnav_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_dash_1_dashjump_markup</th>"
                . "<td>$sample_nav_dash_1_dashjump_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_dash_1_noproject_selected_markup</th>"
                . "<td>$sample_nav_dash_1_noproject_selected_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_dash_1_yesproject_selected_markup</th>"
                . "<td>$sample_nav_dash_1_yesproject_selected_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        //VISUAL COLLAB
        $main_body_ar[] = "<h3>Navigating the Visual Collaboration Area</h3>";
        $main_body_ar[] = "<p>This is a wide-format layout with two sub-topics displayed as tabs just under the"
                . " breadcrumbs of the page top.  One sub-tab is Topic Proposals and the other is Workitem Dependencies.</p>";
        $main_body_ar[] = "<table class='how2steps-bigpic'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_visualbrainstorm_subtab_1_markup</th>"
                . "<td>$sample_visualbrainstorm_subtab_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_topics_1_allnav_markup</th>"
                . "<td>$sample_nav_topics_1_allnav_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_visualbrainstorm_subtab_2_markup</th>"
                . "<td>$sample_visualbrainstorm_subtab_2_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_nav_deps_1_allnav_markup</th>"
                . "<td>$sample_nav_deps_1_allnav_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
