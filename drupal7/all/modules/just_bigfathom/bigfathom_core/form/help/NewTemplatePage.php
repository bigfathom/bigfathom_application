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
class NewTemplatePage extends \bigfathom\ASimpleHelpPage
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

        $url_cnt_1 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnt_1');
        $url_cnt_2 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnt_2');
        $url_cnt_3 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnt_3');
        $url_cnt_4 = \bigfathom\UtilityGeneralFormulas::getHelpArtURLFromName('how2_cnt_4');
        
        $help_page_title = 'How to Create a New Template in Bigfathom';
        $help_page_blurb = "If your application administrator has given you the right to create templates'
            . ', you can create them at any time by following the steps illustrated here.";
        
        $main_body_ar = [];
        
        $link2settings = l('Settings','bigfathom/topinfo/sitemanage');
        $link2projects = l('Project Console','bigfathom/sitemanage/projects');
        $createtemplate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intotemplate');
        $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
        
        $main_body_ar[] = "<h3>How to Create a New Template from an existing Project</h3>";
        $main_body_ar[] = "<p>When you create a new template from an existing project, the template contains all the existing goals, tasks, and declared dependencies of the project.</p>";
        $main_body_ar[] = "<table class='how2steps'>";
        $main_body_ar[] = "<tr><th>Step 1</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing tab and link' src='$url_cnt_1'></td><td>Click on the $link2settings tab.  This is the cog icon <i class='fa fa-cog'></i> also available from the top right of some pages.  Click on the $link2projects option shown in that tab area.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 2</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing button' src='$url_cnt_2'></td><td>Click on the Action Option symbol of an arrow pointing to a circle <img alt='the icon' src='$createtemplate_icon_url' /> on the row of the template from which you wan to create a new project. (To learn more about the template before picking it, click the eye icon <img alt='the icon' src='$view_icon_url' />  to see some information about it.)<td></tr>";
        $main_body_ar[] = "<tr><th>Step 3</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing top of form' src='$url_cnt_3'></td><td>Fill in the form starting with the name you would like to give the new template.<td></tr>";
        $main_body_ar[] = "<tr><th>Step 4</th><td class='how2screenshot'><img class='click-zoom' alt='example screenshot showing save button' src='$url_cnt_4'></td><td>To save your new template, click the 'Create Project Template' button at the bottom of the form.<td></tr>";
        $main_body_ar[] = "</table>";

        $main_body_markup = implode("\n",$main_body_ar);
        
        $this->populateFormElements($form,$help_page_title,$help_page_blurb,$main_body_markup);
        return $form;
    }
}
