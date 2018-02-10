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
 * View help
 *
 * @author Frank Font
 */
class UseCasesPage extends \bigfathom\ASimpleHelpPage
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
        $link2usecases = $this->getProjectDependentMarkup('Use Cases','bigfathom/projects/usecases');

        $link2reports = l('Reports','bigfathom/topinfo/reports');
        $link2usecasesreport = $this->getProjectDependentMarkup('Use Case Mapping in Current Project','bigfathom/reports/usecasesoneproject');
        
        $help_page_title = 'Use Cases in Bigfathom';
        $help_page_blurb = "Use cases, sometimes known as a more detailed version of '<span title='user stories are generally short and very non-technical'>user stories</span>', are a popular and useful mechanism for organizing thoughts and sharing them with a team"
                . " so that all key goals of a project are properly addressed by the work topics of that project."
                . "  The application has significant support for the creation, managment, mapping, and tracking of use cases within projects."
                . "  (The $link2usecases option is available from the $link2projectwork tab once a project has been selected.)";

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        
        $sample_usecase_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_1');
        $sample_usecase_2_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_2');
        $sample_usecase_3_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_3');
        $sample_usecase_4a_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_4a');
        $sample_usecase_4b_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_4b');
        $sample_usecase_4c_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_4c');
        $sample_usecase_4d_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_4d');
        $sample_usecase_perspective_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('concept_usecase_perspective_1');
        $sample_usecase_rep_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_usecase_rep_1');
        
        $sample_usecase_rep_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_rep_1_url' />";
        $sample_usecase_rep_1_tx = "Click on the $link2usecasesreport report on the $link2reports tab"
                . " to see a mapping of workitems to each use case of the currently selected project.";

        $sample_usecase_perspective_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_perspective_1_url' />";

        $sample_usecase_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_1_url' />";
        $sample_usecase_1_tx = "Click on the $link2usecases option from the $link2projectwork tab.  This option is available once a project has been selected.";

        $sample_usecase_2_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_2_url' />";
        $sample_usecase_2_tx = "On the $link2usecases page you will see a listing of all existing use cases"
                . ", have the option to edit them via the Action Options column, and the option to create a new one.";

        $sample_usecase_3_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_3_url' />";
        $sample_usecase_3_tx = "Click the Add New Use Case button at the bottom of the $link2usecases page.";

        $sample_usecase_4a_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_4a_url' />";
        $sample_usecase_4a_tx = "You will see a form where you provide all the information for the use case.";

        $sample_usecase_4b_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_4b_url' />";
        $sample_usecase_4b_tx = "The first input of the form is for you to select the 'perspective' of the use case."
                . "  The application tracks two different perspectives,"
                . " the one from the user of the project deliverable(s) and one from the perspective of those creating the deliverables."
                . "  Pick the one that best describes the intended audience of the use case you are about to create."
                . "<div>$sample_usecase_perspective_1_markup</div>"
                . "  The 'User Story' perspective is the classical story from the perspective of a user that only goes into those details that a user would be aware of or care about. "
                . "In contrast, a 'Technical Story' goes beyond what a user might be aware of and is free to go into the"
                . " architectural weeds of the implementation to the extent that different"
                . " team members working on seperate pieces are 'users' of the work products"
                . " (e.g., components, objects, or services) produced by others on the team.";

        $sample_usecase_4c_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_4c_url' />";
        $sample_usecase_4c_tx = "If you already have some workitems in mind to link to this use case"
                . ", as in those workitems are supporting delivery of features required by this use case"
                . ", type the IDs of those workitems into the form using a comma to delimit each one."
                . "  Don't worry if you don't know what they are (or they do not exist) yet."
                . "  You can update the the use case with workitem IDs at a later time.";

        $sample_usecase_4d_markup = "<img class='click-zoom' alt='the sample image' src='$sample_usecase_4d_url' />";
        $sample_usecase_4d_tx = "Once you have provide enough detail for this version of the use case, save it by clicking the Add This Use Case button at the bottom of the form.";

        $main_body_ar[] = "<h3>Creating a Use Case</h3>";
        $main_body_ar[] = "<p>Users with appropriate privileges in a project can create a use case at any time following the steps shown here.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tbody>";
        $main_body_ar[] = "<tr><tr><th>Step 1</th>"
                . "<th class='how2screenshot'>$sample_usecase_1_markup</th>"
                . "<td>$sample_usecase_1_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 2</th>"
                . "<th class='how2screenshot'>$sample_usecase_2_markup</th>"
                . "<td>$sample_usecase_2_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 3</th>"
                . "<th class='how2screenshot'>$sample_usecase_3_markup</th>"
                . "<td>$sample_usecase_3_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(a)</th>"
                . "<th class='how2screenshot'>$sample_usecase_4a_markup</th>"
                . "<td>$sample_usecase_4a_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(b)</th>"
                . "<th class='how2screenshot'>$sample_usecase_4b_markup</th>"
                . "<td>$sample_usecase_4b_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(c)</th>"
                . "<th class='how2screenshot'>$sample_usecase_4c_markup</th>"
                . "<td>$sample_usecase_4c_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(d)</th>"
                . "<th class='how2screenshot'>$sample_usecase_4d_markup</th>"
                . "<td>$sample_usecase_4d_tx</td></tr>";
        $main_body_ar[] = "</tbody>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>Checking Mappings of Workitems to Use Cases</h3>";
        $main_body_ar[] = "<p>A properly vetted project will have stakeholder accepted use cases and a clear mapping for workitems that deliver the features of the use cases.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tbody>";
        $main_body_ar[] = "<tr><tr><th>Step 1</th>"
                . "<th class='how2screenshot'>$sample_usecase_rep_1_markup</th>"
                . "<td>$sample_usecase_rep_1_tx</td></tr>";
        $main_body_ar[] = "</tbody>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
