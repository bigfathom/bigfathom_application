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
class InContextCommunicationsPage extends \bigfathom\ASimpleHelpPage
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
        $link2allopenactions = $this->getProjectDependentMarkup('Action Request Overview Console','bigfathom/projects/communicationitems/actrionrequestoveriew');
        $link2search = $this->getProjectDependentMarkup('Communications Search Console','bigfathom/projects/communicationitems/search');
        
        $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        $communicate_icon_markup = "<img alt='the icon' src='$communicate_icon_url' />";
        
        $url_comm_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_1');
        $url_comm_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_2');
        $url_comm_3a = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_3a');
        $url_comm_3b = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_3b');
        $url_comm_3c = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_3c');
        $url_comm_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_4');
        $url_comm_5 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_5');

        $url_comm_all_open_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_all_open_1');
        $url_comm_all_open_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_all_open_2');

        $url_comm_search_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_search_1');
        $url_comm_search_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_comm_search_2');
        
        $help_page_title = 'How to Communicate in Context';
        $help_page_blurb = "You can create comments, action items (with or without attached documents) and associate them with specific workitems or sprints.  Look for the $communicate_icon_markup icon to create or respond to communication threads.";
        
        $main_body_ar = [];
        
        $main_body_ar[] = "<h3>How to Create a new Thread on a Workitem</h3>";
        $main_body_ar[] = "<p>Jump to any console that has the  $communicate_icon_markup icon, for example the Effort and Duration Console.  There you can create communications linked to specific workitems.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_comm_1'></td>"
                . "<td>Click on the communication icon $communicate_icon_markup in the Action Options column for the workitem you want to create a communication topic in.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_comm_2'></td>"
                . "<td>Click on the Add New Workitem Comment Thread button at the bottom of the page.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3a</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_comm_3a'></td>"
                . "<td>Fill in the form comment text, and optionally, a short title.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3b</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_comm_3b'></td>"
                . "<td>Categorize the communication by selecting an option from the Group Member Action Requested dropdown."
                . "  If action is requested, then the communication is considered an Action Request and will be tracked until closure."
                . "  If no action is requested, the communication is treated as a simple comment.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3c</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot' src='$url_comm_3c'></td>"
                . "<td>If you want to attach documents, clock on the Add New File Attachement link.  You can attach as many files as you like, but only 3 at a time.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_comm_4'></td>"
                . "<td>Save your communication content.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_comm_5'></td>"
                . "<td>Notice, the communication now appears in the table listing.  People can respond directly to it by clicking the communication icon $communicate_icon_markup in the Action Options column of its row.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>How to List all Open Action Requests</h3>";
        $main_body_ar[] = "<p>An action request is a communication with a non-zero concern level.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing action option icon' src='$url_comm_all_open_1'></td>"
                . "<td>Click on the $link2allopenactions option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_comm_all_open_2'></td>"
                . "<td>All unresolved action requests are listed here.  Sort them and filter them as you would content in any table of Bigfathom.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_ar[] = "<h3>How to Search All Communications</h3>";
        $main_body_ar[] = "<p>You can search through all the existing commuication records whether they are open or not.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing action option icon' src='$url_comm_search_1'></td>"
                . "<td>Click on the $link2search option.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_comm_search_2'></td>"
                . "<td>Provide your searchg criteria and click the fetch button.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
