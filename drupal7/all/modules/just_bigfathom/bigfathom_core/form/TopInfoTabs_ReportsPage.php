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
 
 */

namespace bigfathom;

require_once 'TopInfoTabsPage.php';

/**
 * Reports information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoTabs_ReportsPage extends \bigfathom\TopInfoTabsPage
{

    private $m_selected_tab = "reports";
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($this->m_selected_tab);
    }

    private function getPageMarkup()
    {
        $img_purpose_name = "report_seethru";
        $img_url = UtilityGeneralFormulas::getArtURLForPurposeName($img_purpose_name);
        $img_markup = "<img style='width:100%;height:100%;' src='$img_url' alt='' />";
        $markup = "<div class='dash-normal-fonts'>"
                . "\n<div class='option-group option-group-clear'><div style='width:50em;'>[EMBED_MENU_HERE]</div></div>"
                . "\n<div class='option-group div.option-group-mood-graphic'>$img_markup</div>"
                . "\n<div class='option-group-last'></div>"
                . "\n</div>";        
        return $markup;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $page_markup = $this->getPageMarkup();
            $selected_content_markup = $this->getSelectedBodyContentMarkup(FALSE, FALSE, $page_markup, NULL, "[EMBED_MENU_HERE]");
            
            //$selected_content_markup = $this->getSelectedBodyContentMarkup();
            return $this->getFormBodyContent($form, $html_classname_overrides, $selected_content_markup);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
