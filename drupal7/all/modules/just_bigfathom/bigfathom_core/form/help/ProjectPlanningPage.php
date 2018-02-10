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
class ProjectPlanningPage extends \bigfathom\ASimpleHelpPage
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
        
        $link2brainstormconsole = $this->getProjectDependentMarkup('Brainstorm Topics Console','bigfathom/projects/brainstormitems');
        $link2brainstormvisual = $this->getProjectDependentMarkup('Visual Topic Proposals','bigfathom/projects/design/brainstormcapture');
        $link2visualdeps = $this->getProjectDependentMarkup('Visual Workitem Dependencies','bigfathom/projects/design/mapprojectcontent');
        $link2duration = $this->getProjectDependentMarkup('Workitems Effort and Duration Grid','bigfathom/projects/workitems/duration');
        $link2forecast = $this->getProjectDependentMarkup('Workitems Forecast Console','bigfathom/projects/workitems/forecast');
        
        $link2help_visaldeps = l('How to Declare Workitem Dependencies','bigfathom/help/setworkdeps');
        $link2help_sprint = l('How to Declare Sprints','bigfathom/help/newsprint');
        $link2help_comms = l('How to Communicate-In-Context','bigfathom/help/comms');
        
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');

        $url_star_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_star_1');
        $star_icon_markup = "<img alt='star icon' src='$url_star_1'>";
        
        $url_flowchart_projectwork_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('flowchart_projectwork_1');
        $flowchart_projectwork_1_pic = "<img class='click-zoom' alt='example screenshot' src='$url_flowchart_projectwork_1'>";
        
        $help_page_title = 'Project Planning and Execution in Bigfathom';
        $help_page_blurb = "The application is engineered to support a multiplicity of project planning and execution strategies; it does not limit use to one linear approach."
                . "  The general principle advocated by the application is to iterate from ideation to planning to execution to evalatuation and continue until all your goals are achieved.";

        $ideation_tx = "Come up with ideas of what topics are relevent to examine and consider as goals and tasks for attention in the project."
                . "  Use the collaboration brainstorming interfaces (e.g., $link2brainstormconsole) of the application to help everyone on the team contribute to this activity."
                . "  Return to this activity as often as you like to help work through issues that are discovered as you do the work."
                . "  For example, when something turns out to be more complicated than expected, decompose the work into additional workitems and link them into the project.";
        $planning_tx = "Identify and declare dependencies between workitems (see $link2help_visaldeps)"
                . ", estimate work remaining for each un-finished workitem, balance resources, generate a plan (e.g., Auto-Fill from the $link2duration page).";
        $execution_tx = "Chunk some portion of the work into a span of days (e.g., a week or two) as a sprint (see $link2help_sprint)"
                . ", Update the status of the workitems periodically (e.g., WS=Work Started, RT=Ready for Test, SC=Successful Completion)"
                . ", Check on the probabilities of on time success using the $link2duration and check Critical Paths via the reports."
                . "  Create prioritized Action Items for the team to resolve as issues become apparent (see $link2help_comms).";
        $evaluation_tx = "Periodically assess progress and likelihood of on-time completion so you can make adjustments in a timely manner."
                . "  For example, have a formal assessment review at the end of each sprint period so that each team member can"
                . " take feedback on their planning and execution success up through that point and make adjustments for the next sprint iteration as needed.";
        
        $main_body_ar[] = "<table>";
        $main_body_ar[] = "<tr>";
        $main_body_ar[] = "<td style='width:30%'><div>$flowchart_projectwork_1_pic</div></td>";
        $main_body_ar[] = "<td><div>";
        $main_body_ar[] = "<ul>";
        $main_body_ar[] = "<li><strong style='width:10em'>Ideation</strong><p>$ideation_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Planning</strong><p>$planning_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Execution</strong><p>$execution_tx</p>";
        $main_body_ar[] = "<li><strong style='width:10em'>Evaluation</strong><p>$evaluation_tx</p>";
        $main_body_ar[] = "</ul>";
        $main_body_ar[] = "</div>";
        $main_body_ar[] = "</td>";
        $main_body_ar[] = "</tr>";
        $main_body_ar[] = "</table>";
        $main_body_ar[] = "<p>The above cycle-diagram has hover effects on the $link2projectwork tab to help you select features in each of the context areas."
                . "  When you hover over a context area, strongly associated options are 'stared' $star_icon_markup and weakly associated options are simply highlighted."
                . "  All others are dimmed.</p>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
        
    }
}
