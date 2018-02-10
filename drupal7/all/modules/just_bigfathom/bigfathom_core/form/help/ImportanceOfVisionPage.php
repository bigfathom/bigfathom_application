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
 * View Help Project Planning Page
 *
 * @author Frank Font
 */
class ImportanceOfVisionPage extends \bigfathom\ASimpleHelpPage
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

        $link2visonlist = l('Vision Statement Information Console','bigfathom/sitemanage/visionstatements');
        $link2projectwork = l('Project Work','bigfathom/topinfo/projects');
        
        $link2vision2project = $this->getProjectDependentMarkup('Vision to Project Mapping Console','bigfathom/projects/map/vision2project');
        
        $link2brainstormconsole =  $this->getProjectDependentMarkup('Brainstorm Topics Console','bigfathom/projects/brainstormitems');
        $link2brainstormvisual = $this->getProjectDependentMarkup('Visual Topic Proposals','bigfathom/projects/design/brainstormcapture');
        $link2visualdeps = $this->getProjectDependentMarkup('Visual Workitem Dependencies','bigfathom/projects/design/mapprojectcontent');
        $link2duration = $this->getProjectDependentMarkup('Workitems Effort and Duration Grid','bigfathom/projects/workitems/duration');
        $link2forecast = $this->getProjectDependentMarkup('Workitems Forecast Console','bigfathom/projects/workitems/forecast');
        
        $link2help_visaldeps = l('How to Declare Workitem Dependencies','bigfathom/help/setworkdeps');
        $link2help_sprint = l('How to Declare Sprints','bigfathom/help/newsprint');
        $link2help_comms = l('How to Communicate-In-Context','bigfathom/help/comms');
        $link2help_usecases = l('How to Create and Manage Use Cases','bigfathom/help/usecases');
        
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');

        $url_star_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_star_1');
        $star_icon_markup = "<img alt='star icon' src='$url_star_1'>";
        
        $view_icon_markup = "<img alt='icon' src='$view_icon_url'>";
        $edit_icon_markup = "<img alt='the icon' src='$edit_icon_url' />";
        
        $dashicon_markup = "<i class='fa fa-line-chart'></i>";
        
        $url_flowchart_projectwork_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('thoughts2destiny_1');
        $flowchart_projectwork_1_pic = "<img class='click-zoom' title='Lou Tzhou insight diagram with overlayed influence lines' alt='' src='$url_flowchart_projectwork_1'>";
        
        $help_page_title = 'The Big Picture';
        $help_page_blurb = "In several popular books <span title='See Seven Habits book for his discussion of the idea'>Stephen Covey shared that all things people intentionally create, they first create in their minds.</span>"
                . "  Significantly, philosophers and social scientists have known for many generations that"
                . " <span title='Parapharasing of the concept recorded by Lao Tzu 550BC'>thoughts become words, words become actions, actions become habits, habits create your character; the momentum reveals your destiny.</span>"
                . "  Bigfathom is designed to naturally fit into good habit building practices.";

        $thoughts_tx = "Lay a fertile foundation for yourself and your team to develop constructive perspectives that are meaningful to you;"
                . " creating and sharing vision statements can help."
                . "  All users can see and should probably be encouraged to become familiar with the vision statements created in the application instance by viewing them at the $link2visonlist."
                . "  Properly privileged application users can also create and edit vision statements at the  $link2visonlist."
                . "  Project creators should be encouraged to map all vision statements that their project is in alignment with at the $link2vision2project.";
        
        $words_tx = "Broad ideas start morphing into actionable component ideas and the words that describe them as your team collaborates in the $link2brainstormvisual "
                . " area. The words then start meaningfully connecting with each other when you collaborate in the $link2visualdeps area."
                . "  By collaborating on transitioning from ideas into actionable work everyone has a rich opportunity to contribute to that transformative process"
                . " with their unique perspectives and insights.  Assign ownership to actionable workitems as you go."
                . " Identify and declare dependencies between workitems (see $link2help_visaldeps)."
                . "  Workitem owners can continue decomposing and refining their insights for the work they own even outside of team collaboration sessions.";
        
        $actions_tx = "The workitem owners estimate the level of effort remaining and when they can complete their work using the $link2duration."
                . "  The team can collectively chunk some portion of the work into a span of days (e.g., a week or two) as a sprint (see $link2help_sprint)"
                . ", Team members update the status of the workitems periodically (e.g., WS=Work Started, RT=Ready for Test, SC=Successful Completion)"
                . " so that owners of dependent work have clear insights into when they can start their efforts."
                . "  Team members can create prioritized Action Items for the team to resolve as issues become apparent (see $link2help_comms)."
                . "  Use Case owners can map workitems to their use cases at any time (See $link2help_usecases).";
        
        $habits_tx = "By consistently leveraging the non-linear collaboration experiences, labor saving features, and forecasting insights of the application "
                . " -- users will eventually internalize a powerful shift in how they envision shared goal setting, work planning, and execution.  This shift in thinking will increase"
                . " effective-communication agility of goal relevant insights within projects for greater outcomes."
                . "  The paradigm shifts of the application amplify the outcome of the equation "
                . "<span class='equation' title='See the book Flourish by Martin Seligman for the background on this equation'><strong>ACHIEVEMENT = SKILL x EFFORT</strong></span> by increasing the individual's skill to communicate"
                . " and envision insights and simultaneously reducing the effort required to get constructive results."
                . "  The reward of experiencing greater achievement is a powerful attractor in the development of habits that produce them.";
                
        $main_body_ar[] = "<table>";
        $main_body_ar[] = "<tr>";
        $main_body_ar[] = "<td style='width:30%'><div>$flowchart_projectwork_1_pic</div></td>";
        $main_body_ar[] = "<td><div>";
        $main_body_ar[] = "<ul>";
        $main_body_ar[] = "<li><strong style='width:10em'>Thoughts</strong><p>$thoughts_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Words</strong><p>$words_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Actions</strong><p>$actions_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Habits</strong><p>$habits_tx</p>";
        $main_body_ar[] = "</ul>";
        $main_body_ar[] = "</div>";
        $main_body_ar[] = "</td>";
        $main_body_ar[] = "</tr>";
        $main_body_ar[] = "</table>";
        $main_body_ar[] = "<p>The intention of Bigfathom is to foster good habits"
                . " in identifying and working toward constructive goals with minimal clutter"
                . " and maximum focus."
                . "  The Habits you build, the character you create, and the destiny "
                . "you discover are all in your hands.</p>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
        
    }
}
