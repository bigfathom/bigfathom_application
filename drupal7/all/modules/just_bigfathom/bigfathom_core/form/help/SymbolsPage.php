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
class SymbolsPage extends \bigfathom\ASimpleHelpPage
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

        $help_page_title = 'Symbology Conventions in Bigfathom';
        $help_page_blurb = "There a number of symbols that have a consistent meanings within the application.  Some are clickable to perform an action.";

        $rocket_icon_markup = "<i class='fa fa-rocket'></i>";
        
        $link2visualdeps = l('Visual Workitem Dependencies','bigfathom/projects/design/mapprojectcontent');
        $link2helpvisaldeps = l('How to Declare Workitem Dependencies','bigfathom/help/setworkdeps');
        
        $sample_proj1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_proj1');
        $sample_goal1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_goal1');
        $sample_task1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_task1');
        $sample_lines1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_lines1');

        $sample_tablefilter_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_tablefilter_1');
        $sample_tablerowcount_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_tablerowcount_1');
        $sample_tablehelp_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_tablehelp_1');
        $sample_tablesort_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_tablesort_1');

        
        $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
        $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $duplicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('duplicate');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
        $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

        
        $download_tabledata_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('download_tabledata');


        $art_sample_dependency_1_url = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('art_sample_dependency_1');
        
        
        $sample_tablefilter_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_tablefilter_1_url' />";
        $sample_tablefilter_1_tx = "Hover over the Filter keyword found at the top left of most tables to get a total rowcount of data in the table."
                . "  Type into the input box to the right of the keyword to globally filter rows of the table such that only rows with matching text remain displayed in the table."
                . "  The count is shown as NUMBER OF ROWS MATCHING FILTER of TOTAL ROWS IN THE TABLE.";
        $sample_tablerowcount_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_tablerowcount_1_url' />";
        $sample_tablerowcount_1_tx = "The row limiter control is at the top right of most tables in the application.  Select the number of rows you want displayed in the table."
                . "  For example, if the number is 10 but there are 25 rows of data, then only 10 of the 25 will be displayed at any one time.";
        $sample_tablehelp_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_tablehelp_1_url' />";
        $sample_tablehelp_1_tx = "Hover over any table column label that has a small 'i' icon to the right of it to get helpful text explaining the purpose of the column."
                . "  Alternatively, click on the 'i' icon to the right of the label to see the same text in a modal popup.";
        $sample_tablesort_1_markup = "<img class='click-zoom' alt='the icon' src='$sample_tablesort_1_url' />";
        $sample_tablesort_1_tx = "Most columns in the tables of the application are sortable simply by clicking on the column label."
                . "  If the column is sortable, an arrow will appear to the right of the label showing the current state of the sorting."
                . "  Click it again to reverse the sort, and click it one more time to remove the sort.";
        
        
        
        $download_tabledata_icon_markup = "<img alt='the icon' src='$download_tabledata_icon_url' />";
        $download_tabledata_tx = 'Click this icon, found at the bottom right of most table displays, to download all the data of the table into a local Excel compatible tab delimited file.';
        
        
        $communicate_icon_markup = "<img alt='the icon' src='$communicate_icon_url' />";
        $comm_tx = 'Click this to see or participate in communcation threads.  Communication threads are where action items are created and where their responses can be found.'
                . '  These threads are also where document attachements are created.';

        $heir_icon_markup = "<img alt='the dependency heirarchy icon' src='$hierarchy_icon_url' />";
        $heir_tx = 'Click this to see the dependency tree.';
        
        $view_icon_markup = "<img alt='the view icon' src='$view_icon_url' />";
        $view_tx = 'Click this to see details of an item without being able to edit them.';
        
        $edit_icon_markup = "<img alt='the edit icon' src='$edit_icon_url' />";
        $edit_tx = 'Click this to edit details of an item.';
        
        $duplicate_icon_markup = "<img alt='the duplication icon' src='$duplicate_icon_url' />";
        $duplicate_tx = 'Click this to create a duplicate of the selected item.';
        
        $del_icon_markup = "<img alt='the deletion icon' src='$delete_icon_url' />";
        $del_tx = 'Click this to delete an item.';

        $goal_icon_markup = "<img alt='the icon' src='$sample_goal1_url' />";
        $goal_tx = 'A goal is a type of workitem where achieving it is either not clear yet or suspected of being non-trivial.  The symbol is a circle, usually green in color.  Think of these as aspirations or as deep-work.';

        $task_icon_markup = "<img alt='the icon' src='$sample_task1_url' />";
        $task_tx = 'A task is a type of workitem where achieving it is suspected of being straight-forward/easy.  The symbol is a hammer, usually grayish in color.  Think of these as simple actions or as shallow-work.';

        $proj_icon_markup = "<img alt='the icon' src='$sample_proj1_url' />";
        $proj_tx = 'A project is symbolized by a workitem where completion of that workitem equals completion of the project.  The symbol is a circle, usually blue or green in color.';

        $lines_icon_markup = "<img class='click-zoom' alt='sample of lines in drag-drop-display' src='$art_sample_dependency_1_url' />";
        $lines_tx = "All dependency lines in the application are <strong>'lines of influence'</strong> where each item points to the things that depend on it."
                . "  We have superimposed yellow callouts into the sample image shown here.  The goal 'Create Initial Project Groups'"
                . " depends on the successful completion of task 'Install Module' and the goal 'Train Key Users' for its own success."
                . "  In the visualization, when you zoom in"
                . ", you will see an arrow at the connection point where lines meet con"
                . "necting <span title='occurs before another'>antecedent workitems</span>"
                . " to their <span title='depends on others'>dependent workitems</span>."
                . "  (See $link2helpvisaldeps for additional information on creating and managing dependencies.)";

        $main_body_ar[] = "<h3>Table Row Action Options</h3>";
        $main_body_ar[] = "<p>Many of the tabular displays in the application have a column at the far right labeled 'Action Options'."
                . "  Clicking on the icons in that column initiates an action on the item of the row to which the icon belongs."
                . "  The set of icons available will generally be some subset of the ones shown here.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$communicate_icon_markup</th>"
                . "<td>$comm_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$heir_icon_markup</th>"
                . "<td>$heir_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$view_icon_markup</th>"
                . "<td>$view_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$edit_icon_markup</th>"
                . "<td>$edit_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$duplicate_icon_markup</th>"
                . "<td>$duplicate_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$del_icon_markup</th>"
                . "<td>$del_tx</td></tr>";
        $main_body_ar[] = "</table>";

        
        $main_body_ar[] = "<h3>Table Wide Action Options</h3>";
        $main_body_ar[] = "<p>Almost every table displayed in the application can be filtered, sorted, and have all its content downloaded into an Excel compatible local file.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_tablefilter_1_markup</th>"
                . "<td>$sample_tablefilter_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_tablerowcount_1_markup</th>"
                . "<td>$sample_tablerowcount_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_tablehelp_1_markup</th>"
                . "<td>$sample_tablehelp_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$sample_tablesort_1_markup</th>"
                . "<td>$sample_tablesort_1_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$download_tabledata_icon_markup</th>"
                . "<td>$download_tabledata_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>Work Breakdown Elements</h3>";
        $main_body_ar[] = "<p>Work type representation conventions are consistently employed throughout the application.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$task_icon_markup</th>"
                . "<td>$task_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$goal_icon_markup</th>"
                . "<td>$goal_tx</td></tr>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$proj_icon_markup</th>"
                . "<td>$proj_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>Work Dependency Visualization</h3>";
        $main_body_ar[] = "<table class='how2steps-bigpic'>";
        $main_body_ar[] = "<tr><th class='how2screenshot'>$lines_icon_markup</th>"
                . "<td>$lines_tx</td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
