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
class TestCasesPage extends \bigfathom\ASimpleHelpPage
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
        $link2testcases = $this->getProjectDependentMarkup('Test Cases','bigfathom/projects/testcases');

        $link2reports = l('Reports','bigfathom/topinfo/reports');
        $link2testcasesreport = $this->getProjectDependentMarkup('Test Case Mapping in Current Project','bigfathom/reports/testcasesoneproject');
        
        $help_page_title = 'Test Cases in Bigfathom';
        $help_page_blurb = "Test cases are a popular and useful mechanism for organizing thoughts and sharing them with a team on the subject of validating the implementation of expected functionality."
                . "  The application has significant support for the creation, managment, mapping, and tracking of test cases within projects."
                . "  (The $link2testcases option is available from the $link2projectwork tab once a project has been selected.)";

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        
        $sample_testcase_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_1');
        $sample_testcase_2_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_2');
        $sample_testcase_3_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_3');
        $sample_testcase_4a_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_4a');
        $sample_testcase_4b_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_4b');
        $sample_testcase_4c_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_4c');
        $sample_testcase_4d_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_4d');
        $sample_testcase_perspective_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('concept_testcase_perspective_1');
        $sample_testcase_rep_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_testcase_rep_1');
        
        $sample_testcase_rep_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_rep_1_url' />";
        $sample_testcase_rep_1_tx = "Click on the $link2testcasesreport report on the $link2reports tab"
                . " to see a mapping of workitems to each test case of the currently selected project.";

        $sample_testcase_perspective_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_perspective_1_url' />";

        $sample_testcase_1_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_1_url' />";
        $sample_testcase_1_tx = "Click on the $link2testcases option from the $link2projectwork tab.  This option is available once a project has been selected.";

        $sample_testcase_2_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_2_url' />";
        $sample_testcase_2_tx = "On the $link2testcases page you will see a listing of all existing test cases"
                . ", have the option to edit them via the Action Options column, and the option to create a new one.";

        $sample_testcase_3_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_3_url' />";
        $sample_testcase_3_tx = "Click the Add New Test Case button at the bottom of the $link2testcases page.";

        $sample_testcase_4a_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_4a_url' />";
        $sample_testcase_4a_tx = "You will see a form where you provide all the information for the test case.";

        $sample_testcase_4b_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_4b_url' />";
        $sample_testcase_4b_tx = "The first input of the form is for you to select the 'perspective' of the test case."
                . "  The application tracks two different perspectives,"
                . " the one from the user of the project deliverable(s) and one from the perspective of those creating the deliverables."
                . "  Pick the one that best describes the intended audience of the test case you are about to create."
                . "<div>$sample_testcase_perspective_1_markup</div>"
                . "  The 'User Story' perspective is the classical story from the perspective of a user that only goes into those details that a user would be aware of or care about. "
                . "In contrast, a 'Technical Story' goes beyond what a user might be aware of and is free to go into the"
                . " architectural weeds of the implementation to the extent that different"
                . " team members working on seperate pieces are 'users' of the work products"
                . " (e.g., components, objects, or services) produced by others on the team.";

        $sample_testcase_4c_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_4c_url' />";
        $sample_testcase_4c_tx = "If you already have some workitems in mind to link to this test case"
                . ", as in those workitems are supporting delivery of features required by this test case"
                . ", type the IDs of those workitems into the form using a comma to delimit each one."
                . "  Don't worry if you don't know what they are (or they do not exist) yet."
                . "  You can update the the test case with workitem IDs at a later time.";

        $sample_testcase_4d_markup = "<img class='click-zoom' alt='the sample image' src='$sample_testcase_4d_url' />";
        $sample_testcase_4d_tx = "Once you have provide enough detail for this version of the test case, save it by clicking the Add This Test Case button at the bottom of the form.";

        $main_body_ar[] = "<h3>Creating a Test Case</h3>";
        $main_body_ar[] = "<p>Users with appropriate privileges in a project can create a test case at any time following the steps shown here.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tbody>";
        $main_body_ar[] = "<tr><tr><th>Step 1</th>"
                . "<th class='how2screenshot'>$sample_testcase_1_markup</th>"
                . "<td>$sample_testcase_1_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 2</th>"
                . "<th class='how2screenshot'>$sample_testcase_2_markup</th>"
                . "<td>$sample_testcase_2_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 3</th>"
                . "<th class='how2screenshot'>$sample_testcase_3_markup</th>"
                . "<td>$sample_testcase_3_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(a)</th>"
                . "<th class='how2screenshot'>$sample_testcase_4a_markup</th>"
                . "<td>$sample_testcase_4a_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(b)</th>"
                . "<th class='how2screenshot'>$sample_testcase_4b_markup</th>"
                . "<td>$sample_testcase_4b_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(c)</th>"
                . "<th class='how2screenshot'>$sample_testcase_4c_markup</th>"
                . "<td>$sample_testcase_4c_tx</td></tr>";
        $main_body_ar[] = "<tr><tr><th>Step 4(d)</th>"
                . "<th class='how2screenshot'>$sample_testcase_4d_markup</th>"
                . "<td>$sample_testcase_4d_tx</td></tr>";
        $main_body_ar[] = "</tbody>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>Checking Mappings of Workitems to Test Cases</h3>";
        $main_body_ar[] = "<p>A properly vetted project will have stakeholder accepted test cases and a clear mapping for workitems that deliver the features of the test cases.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tbody>";
        $main_body_ar[] = "<tr><tr><th>Step 1</th>"
                . "<th class='how2screenshot'>$sample_testcase_rep_1_markup</th>"
                . "<td>$sample_testcase_rep_1_tx</td></tr>";
        $main_body_ar[] = "</tbody>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
