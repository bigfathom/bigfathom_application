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
class SetWorkitemDependencies extends \bigfathom\ASimpleHelpPage
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

        $link2dash = l('Dashboard','bigfathom/topinfo/dashboard');
        $link2duration = $this->getProjectDependentMarkup('Workitems Effort and Duration Grid','bigfathom/projects/workitems/duration');
        
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');

        $dashboard_icon_markup ="<i class='fa fa-line-chart'></i>";
        $edit_icon_markup = "<img alt='the icon' src='$edit_icon_url' />";
        
        $url_editdeps_dash_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_1');
        $url_editdeps_dash_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_2');
        $url_editdeps_dash_3a = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_3a');
        $url_editdeps_dash_3b = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_3b');
        $url_editdeps_dash_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_4');
        $url_editdeps_dash_5 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_5');
        $url_editdeps_dash_6 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_dash_6');

        $url_editdeps_console_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_console_1');
        $url_editdeps_console_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_console_2');
        $url_editdeps_console_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdeps_console_3');

        
        $url_edit_ddw_dialog = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('sample_ddw_edit');
        
        
        $help_page_title = 'How to Declare Workitem Dependencies in Bigfathom';
        $help_page_blurb = "There are three ways to declare dependencies in the application; one way is to use the drag-and-drop interface, "
                . " another is to declare dependencies manually by editing the workitem attributes directly."
                . "  To edit the dependencies directly, you can either <a href='#HELP_SECTION_EDIT_RECORD'>edit the entire workitem record</a>"
                . " or <a href='#HELP_SECTION_EDIT_DEPS_IN_CELL'>edit just the DDW or DAW values in the relevant table cells</a> of the $link2duration interface.";
        
        $main_body_ar = [];
        
        $main_body_ar[] = "<h3>Visual Dependency Declarations</h3>";
        $main_body_ar[] = "<p>If you are using a compatible browser, you can declare dependencies between workitems simply by dragging and dropping."
                . "  You can remove depdendencies simply by selecting a line and pressing the delete key to remove it.  All edits are saved as you make them.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing dashboard' src='$url_editdeps_dash_1'></td>"
                . "<td>Click on the $link2dash tab.  This is the up-chart icon $dashboard_icon_markup also available from the top right of some pages."
                . "  Then, click the brainstorm button for the project you want to work on.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_editdeps_dash_2'></td>"
                . "<td>Click on the <strong>Workitem Dependencies</strong> top tab link shown in the"
                . " Visual Collaboration area to switch from the Topic Proposals page directly into the workitem dependency editing page.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing workitems' src='$url_editdeps_dash_3a'></td>"
                . "<td>Select a non-linked workitem from the candidate tray at the far right.  If there are no un-linked workitems, the tray will be minimized."
                . "  (You can alternatively grab workitems that are already linked to declare additional links.)<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing one workitem' src='$url_editdeps_dash_3b'></td>"
                . "<td>In this example, we see there is one non-linked workitem called 'Select Environment for Bigfathom'.  We will grab that one.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 5</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing workitems' src='$url_editdeps_dash_4'></td>"
                . "<td>Drag the grabbed workitem and drop it onto an existing workitem that depends on it.  In our example, we have declared that"
                . " the success of workitem 'Install Bigfathom_Core' depends on the successful completion of workitem 'Select Environment for Bigfathom'.<td></tr>";
        $main_body_ar[] = "</table>";
        $main_body_ar[] = "<h3>Visual Dependency Removal</h3>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing workitems' src='$url_editdeps_dash_5'></td>"
                . "<td>Select the dependency that you want to remove."
                . "  In our example, we will select the line showing a dependency on 'Install Bigfathom_Core' of the workitem 'Create Key User Accounts'<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing workitems' src='$url_editdeps_dash_6'></td>"
                . "<td>Press the DELETE key on your keyboard and the selected dependency is removed.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3 id='HELP_SECTION_EDIT_RECORD'>How to Declare Dependencies By Editing Workitem Detail</h3>";
        $main_body_ar[] = "<p>You can edit the dependencies from any console where workitems are created."
                . "  For this example, we will edit from the $link2duration.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing link icon' src='$url_editdeps_console_1'></td>"
                . "<td>Click on the Action Option pencil icon $edit_icon_markup on the row of the existing workitem where you want to edit the dependency declarations."
                . "  You can do this from any console where workitems are available for editing.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing list' src='$url_editdeps_console_2'></td>"
                . "<td>In the form, simply select the workitems that depend on the currently selected workitem.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_editdeps_console_3'></td>"
                . "<td>Save your changes when done editing the workitem content.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3 id='HELP_SECTION_EDIT_DEPS_IN_CELL'>How to Declare Dependencies By Editing DDW and DAW Cells Directly</h3>";
        $main_body_ar[] = "<p>You can edit the dependencies from the $link2duration interface by clicking directly on the desired DDW or DAW cell.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing edit dialog' src='$url_edit_ddw_dialog'></td>"
                . "<td>As shown in this example, click on the cell you wish to edit and you will see a dialog box containing the existing link references.  Add or remove IDs in this dialog and press the Save button."
                . "  If you have invalid values, the dialog will reject your input and allow you to resubmit after making corrections.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
