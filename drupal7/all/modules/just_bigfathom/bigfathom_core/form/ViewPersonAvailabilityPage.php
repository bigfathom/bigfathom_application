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

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/PersonAvailabilityPageHelper.php';

/**
 * View details of a person_availability entry
 *
 * @author Frank Font
 */
class ViewPersonAvailabilityPage extends \bigfathom\ASimpleFormPage
{
    protected $m_person_availabilityid  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($person_availabilityid, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        if (!isset($person_availabilityid) || !is_numeric($person_availabilityid)) {
            throw new \Exception("Missing or invalid person_availabilityid value = " . $person_availabilityid);
        }
        $this->m_person_availabilityid = $person_availabilityid;
        
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_personid = $this->m_oMapHelper->getPersonIDForPersonAvailabilityID($person_availabilityid);
        
        $urls_arr = [];
        $urls_arr['return'] = 'bigfathom/topinfo/youraccount';
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\PersonAvailabilityPageHelper($urls_arr,NULL,$this->m_personid);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_person_availabilityid);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($base_form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        
        if(empty($this->m_urls_arr['rparams']))
        {
            $rparams_ar = [];
        } else {
            $rparams_ar = $this->m_urls_arr['rparams'];
        }
        
        $disabled = TRUE;   //Do not let them edit.
        $form = $this->m_oPageHelper->getForm('V',$base_form
                , $form_state
                , $disabled
                , $myvalues
                , $html_classname_overrides);
        
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        
        if(isset($this->m_urls_arr['return']))
        {
            $base_url = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$base_url
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])
                        ,'query'=>array('personid'=>$this->m_personid)
                        )
                    );
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }

        return $form;
    }
}
