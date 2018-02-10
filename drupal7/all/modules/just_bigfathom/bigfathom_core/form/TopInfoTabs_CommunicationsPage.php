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
class TopInfoTabs_CommunicationsPage extends \bigfathom\TopInfoTabsPage
{

    private $m_selected_tab = "communications";
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($this->m_selected_tab);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $selected_content_markup = $this->getSelectedBodyContentMarkup();
            $form = $this->getFormBodyContent($form, $html_classname_overrides, $selected_content_markup);
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
