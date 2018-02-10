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
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoTabs_OneProjectPage extends \bigfathom\TopInfoTabsPage
{

    private $m_selected_tab = "projects";
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($this->m_selected_tab);
    }

    private function getPageMarkup()
    {
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $oSnippetHelper = new \bigfathom\SnippetHelper();
        $dashboard_TCCI_markup = $oSnippetHelper->getHtmlSnippet("dashboard_TCCI");            
        $markup = "<div class='dash-normal-fonts'>"
                . "\n<div class='dash-80pct'><div style='width:50em;'>[EMBED_MENU_HERE]</div></div>"
                . "\n<div class='dash-action-graphic'><div class='context-center-in-container'>$dashboard_TCCI_markup</div></div>"
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

            return $this->getFormBodyContent($form, $html_classname_overrides, $selected_content_markup);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
