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
class SetPersonAvailabilityPage extends \bigfathom\ASimpleHelpPage
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
        $link2youraccount = l('Your Account','bigfathom/topinfo/youraccount');
        $link2edityourprofile = l('Edit Your Profile','bigfathom/youraccount/edityourprofile');
        $link2definenewavailabilityperiod = l('Edit Your Profile','bigfathom/addperson_availability');
        $link2personconsole = l('Person Information Console','bigfathom/sitemanage/people');
        
        
        $link2baselineavailconsole = l('Baseline Availability Information Console','bigfathom/sitemanage/baseline_availability');
        
        $createtemplate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intotemplate');
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');

        $calendar_icon_markup = "<i class='fa fa-calendar'></i>";
        $youraccount_icon_markup ="<i class='fa fa-user-circle'></i>";
        $view_icon_markup = "<img alt='icon' src='$view_icon_url'>";
        $edit_icon_markup = "<img alt='the icon' src='$edit_icon_url' />";
        
        $url_person_avail_other_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_person_avail_other_1');
        $url_persondef_avail_you_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_persondef_avail_you_1');
        $url_persondef_avail_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_persondef_avail_2');
        $url_persondef_avail_other_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_persondef_avail_other_3');

        //$url_personavail_you_0 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_you_0');
        $url_personavail_you_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_you_1');
        $url_personavail_you_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_you_2');
        $url_personavail_you_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_you_3');
        $url_personavail_you_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_you_4');

        $url_editdefavaillist_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdefavaillist_1');
        $url_editdefavaillist_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_editdefavaillist_2');

        $url_personavail_other_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_other_1');
        $url_personavail_other_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_other_2');
        $url_personavail_other_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_personavail_other_3');
        
        $help_page_title = 'How to Declare Person Availability in Bigfathom';
        $help_page_blurb = "Every user can self-manage their own availabilty declarations in the $link2youraccount tab."
            . "  Specially privileged users can also edit the availability declarations for other people via the $link2settings tab.";
        
        $main_body_ar = [];
        
        $main_body_ar[] = "<h3>How to Declare Your Default Availability</h3>";
        $main_body_ar[] = "<p>Each user of the application as the ability to manage their own default availability declaration.  The default availability is an attribute of your user profile."
                . "  The default is what the system uses as your availability when an overriding custom availability does not exist.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_personavail_you_1'></td>"
                . "<td>Click on the $link2youraccount tab.  This is the circled-person icon $youraccount_icon_markup also available from the top right of some pages.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_persondef_avail_you_1'></td>"
                . "<td>Click on the $link2edityourprofile option shown in the selected tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_persondef_avail_2'></td>"
                . "<td>Select the preferred default availability from the options in the dropdown list and then save your edited profile record."
                . " (The options presented for your selection in the list are <a href='#CONFIG_THE_LIST'>configured by the application administrator</a>.)<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_persondef_avail_other_3'></td>"
                . "<td>Save your changes.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>How to Declare Your Custom Availability</h3>";
        $main_body_ar[] = "<p>Each user of the application has the ability to manage their own custom availability declarations.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_personavail_you_1'></td>"
                . "<td>Click on the $link2youraccount tab.  This is the circled-person icon $youraccount_icon_markup also available from the top right of some pages.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_personavail_you_4'></td>"
                . "<td>Click on the Action Option column pencil icon $edit_icon_markup on the row of any existing custom availability you want to edit, or click the Define New Availability Period button at the bottom of the page to create a new one.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing list in the form' src='$url_personavail_you_2'></td>"
                . "<td>Fill in the form starting with the type of custom availability you are declaring.  Your choice here determines what additional fields you will be required to fill in.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_personavail_you_3'></td>"
                . "<td>To save your new declaration, click the 'Save Personal Availability Period' button at the bottom of the form.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_ar[] = "<h3>How to Declare Default Availability of Other People</h3>";
        $main_body_ar[] = "<p>Some user accounts are given the privilege of editing the availability of other user accounts in the application."
                . "  The default is what the system uses as a person's availability when an overriding custom availability does not exist.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_personavail_other_1'></td>"
                . "<td>Click on the $link2settings tab.  This is the cog icon <i class='fa fa-cog'></i> also available from the top right of some pages."
                . "  Click on the $link2personconsole option shown in that tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_personavail_other_2'></td>"
                . "<td>Click on the Action Option pencil icon $edit_icon_markup on the row of the existing person profile you want to edit. (To learn more about the profile before picking it, click the eye icon <img alt='the icon' src='$view_icon_url' />  to see some information about it.)<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_ar[] = "<h3>How to Declare Custom Availability of Other People</h3>";
        $main_body_ar[] = "<p>Some user accounts are given the privilege of editing the custom availability of other user accounts in the application.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_personavail_other_1'></td>"
                . "<td>Click on the $link2settings tab.  This is the cog icon <i class='fa fa-cog'></i> also available from the top right of some pages."
                . "  Click on the $link2personconsole option shown in that tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_personavail_other_2'></td>"
                . "<td>Click on the Action Option calendar icon $calendar_icon_markup on the row of the existing person custom availability you want to edit.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing top of form' src='$url_personavail_other_3'></td>"
                . "<td>Click on the 'Define New Availability Period' button at the bottom of the page to create a new declaration"
                . ", or edit an existing declaration by clicking the pencil icon $edit_icon_markup on the relevant row.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_ar[] = "<h3 id='CONFIG_THE_LIST'>How to Create Default Availability Options Available to Everyone</h3>";
        $main_body_ar[] = "<p>The application administrator can edit the list of default availability options available to users from their dropdown lists.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_editdefavaillist_1'></td><td>Click on the $link2settings tab.  This is the cog icon <i class='fa fa-cog'></i> also available from the top right of some pages.  Click on the $link2baselineavailconsole option shown in that tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_editdefavaillist_2'></td>"
                . "<td>Click on the Action Option pencil icon $edit_icon_markup on the row of the existing baseline availability you want to edit.  Alternatively, click the 'Add New Baseline Availability' button at the bottom of the page if you wish to create a new one.<td></tr>";
        $main_body_ar[] = "</table>";
        
        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
