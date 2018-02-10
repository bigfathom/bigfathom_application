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
 * View help about collaboration approach
 *
 * @author Frank Font
 */
class CollaborationPage extends \bigfathom\ASimpleHelpPage
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
        
        
        $link2dashboard = l('Dashboard','bigfathom/topinfo/dashboard');
        $link2projectwork = l('Project Work','bigfathom/topinfo/projects');
        
        $link2brainstormconsole = $this->getProjectDependentMarkup('Brainstorm Topics Console','bigfathom/projects/brainstormitems');
        $link2brainstormvisual = $this->getProjectDependentMarkup('Visual Topic Proposals','bigfathom/projects/design/brainstormcapture');
        $link2visualdeps = $this->getProjectDependentMarkup('Visual Workitem Dependencies','bigfathom/projects/design/mapprojectcontent');
        
        $link2helpvisaldeps = l('How to Declare Workitem Dependencies','bigfathom/help/setworkdeps');
        
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');

        $view_icon_markup = "<img alt='icon' src='$view_icon_url'>";
        $edit_icon_markup = "<img alt='the icon' src='$edit_icon_url' />";
        
        $dashicon_markup = "<i class='fa fa-line-chart'></i>";
        
        $url_brainstorm_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_1');
        $url_brainstorm_2a = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_2a');
        $url_brainstorm_2b = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_2b');
        $url_brainstorm_2c = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_2c');
        $url_brainstorm_2d = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_2d');
        $url_brainstorm_2e = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_2e');
        $url_brainstorm_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_3');
        $url_brainstorm_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_4');
        $url_brainstorm_5 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_5');

        $url_brainstorm_table_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_table_1');
        $url_brainstorm_table_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_brainstorm_table_2');

        $help_page_title = 'Collaboration Concepts in Bigfathom';
        $help_page_blurb = "Teams are most productive when they share the same clear understanding of what needs to get done."
                . "  Sometimes different people arrive at that common understanding through different paths."
                . "  One way to bring different people with different skills together into a common vision"
                . " is to involve them as soon as as possible in the planning and to distribute ownership of goal achievment during that planning process.";
        
        $step1_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_1'>";
        $step1_tx ="Select a project to work on.  One way to do this is via the $link2dashboard ($dashicon_markup) tab."
                . "  Click on the 'brainstorm' button of your desired project's dashboard panel.";
        $step2a_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_2a'>";
        $step2a_tx ="The $link2brainstormvisual area enables you and authorized members of your team to brainstorm what the relevant work topics are for your project and to make initial assessments of their complexity."
                . "  Think of this area as a virtual Joint Application Design (JAD) marker-board where the content is intentionally focused on the 'what to do' rather then the 'how to do it' content."
                . " If it gets too cluttered, you can drag topics into the parking lot (car icon at the left) or into the trashcan (trashcan icon at the left).";
        $step2b_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_2b'>";
        $step2b_tx ="To create candidate topic items, click the 'Create new Candidate Topic Item' button visible just above the drag-and-drop area.";
        $step2c_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_2c'>";
        $step2c_tx ="Creating a topic item only requires that you supply a name at this point in the process, but you are free to provide more information if you like."
                . "  The Type you select will determine where the topic icon appears in the drag-and-drop area.";
        $step2d_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_2d'>";
        $step2d_tx ="You and authorized members of your team can re-categorize the complexity of a topic item simply by dragging it into the desired lane of the drag-and-drop area."
                . " In this example you see we are going to drag an uncategorized topic into the 'Goal' lane of the area.";
        $step2e_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_2e'>";
        $step2e_tx ="When you drop a topic icon into a lane, the icon changes into the shape appropriate for that category."
                . "  Circles are used to represent goals and hammers are used to represent tasks.";
        $step3_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_3'>";
        $step3_tx ="If you or anyone on your team prefers a <a href='#ALT-TABLECONSOLE'>tabular console interaction</a> rather than drag-and-drop visual interaction, you or they can click the 'Jump to Table Console' button at the bottom of the page at any time.";
        $step4_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_4'>";
        $step4_tx ="If at anytime you or any authorized team member is ready to convert candidate topic items into actual workitems, simply click the Workitem Dependencies sub tab at the top of the drag-and-drop area.";
        $step5_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_5'>";
        $step5_tx ="The $link2visualdeps area enables you to drag topics to declare their intended sequence of execution to complete the project."
                . "  More information on how to declare and manage workitem dependencies can be found on the $link2helpvisaldeps help page.<br><strong>NOTE:</strong> Uncategorized candidate topics are never available for drag-and-drop dependency declaration. ";
        
        $step1_table_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_table_1'>";
        $step1_table_tx ="The bottom of the $link2brainstormconsole has a quick link back to the $link2brainstormvisual along with buttons to manage existing candidate topics.";
        $step2_table_pic = "<img class='click-zoom' alt='example screenshot' src='$url_brainstorm_table_2'>";
        $step2_table_tx ="You can also edit the properties of any individual candidate topic, including ownership, by clicking the pencil icon $edit_icon_markup in the Action Options area of its row."
                . "  When you do that, you get a form like the one shown here.";
        
        $main_body_ar[] = "<h3>General Approach By Example</h3>";
        $main_body_ar[] = "<p>Create a team, assign that team to a new project and then invite them to collaborate on the planning for completing that project."
                . "  The steps below show how you and your team would collaborate on an existing project to which you all have access.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th>"
                . "<td class='how2screenshot'>$step1_pic</td>"
                . "<td>$step1_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2(a)</th>"
                . "<td class='how2screenshot'>$step2a_pic</td>"
                . "<td>$step2a_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2(b)</th>"
                . "<td class='how2screenshot'>$step2b_pic</td>"
                . "<td>$step2b_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2(c)</th>"
                . "<td class='how2screenshot'>$step2c_pic</td>"
                . "<td>$step2c_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2(d)</th>"
                . "<td class='how2screenshot'>$step2d_pic</td>"
                . "<td>$step2d_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2(e)</th>"
                . "<td class='how2screenshot'>$step2e_pic</td>"
                . "<td>$step2e_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th>"
                . "<td class='how2screenshot'>$step3_pic</td>"
                . "<td>$step3_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th>"
                . "<td class='how2screenshot'>$step4_pic</td>"
                . "<td>$step4_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 5</th>"
                . "<td class='how2screenshot'>$step5_pic</td>"
                . "<td>$step5_tx<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_ar[] = "<h3 id='ALT-TABLECONSOLE'>Alternate Interface for Brainstorming Candidate Topics</h3>";
        $main_body_ar[] = "<p>Candidate topics can be created, discarded, edited, moved into/out of the trashcan via the tabular interface of the $link2brainstormconsole.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'>$step1_table_pic</td>"
                . "<td>$step1_table_tx<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'>$step2_table_pic</td>"
                . "<td>$step2_table_tx<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
