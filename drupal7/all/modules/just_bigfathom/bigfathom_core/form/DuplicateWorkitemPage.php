<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 *
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/WorkitemPageHelper.php';

/**
 * Add a Workitem based on an existing workitem
 *
 * @author Frank Font
 */
class DuplicateWorkitemPage extends \bigfathom\AddWorkitemPage
{
    function __construct($workitemid, $projectid=NULL, $urls_override_arr=NULL)
    {
        if (!isset($workitemid) || !is_numeric($workitemid)) {
            throw new \Exception("Missing or invalid goal_id value = " . $workitemid);
        }
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_workitemid = $workitemid;
        module_load_include('php','bigfathom_core','core/MapHelper');
        $oMapHelper = new \bigfathom\MapHelper();
        $workitem_record = $oMapHelper->getOneRichWorkitemRecord($this->m_workitemid);
        if(empty($projectid))
        {
            $projectid = $workitem_record['owner_projectid'];
        }
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        
        $basetype = $workitem_record['workitem_basetype']; 
        parent::__construct($basetype, $projectid, $urls_arr);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_workitemid);
        if(isset($myvalues['map_ddw']))
        {
            $map_ddw = $myvalues['map_ddw'];
            if(is_array($map_ddw) && count($map_ddw)>0)
            {
                global $user;
                $this_uid = $user->uid;
            
                //Remove any ddw that they do not have ownership for otherwise messing with other peoples declarations
                $ownership_bundle = $this->m_oPageHelper->getWorkitemOwnersMapBundle($map_ddw, $this_uid);
                $owned_wid2people = $ownership_bundle['owned_wid2people'];
                $notowned_wid2people = $ownership_bundle['notowned_wid2people'];
                $notowned_count = count($notowned_wid2people);
                if($notowned_count > 0)
                {
                    $wids_tx = implode(',', array_keys($notowned_wid2people));
                    if($notowned_count == 1)
                    {
                        $msg = "There is 1 workitem (#$wids_tx) that is not editable by "
                                . "your account which has declared a direct dependency on the workitem you are duplicating."
                                . "  Your duplicate will NOT be linked as an antecedent of this workitem.";
                    } else {
                        $msg = "There are $notowned_count workitems (ID numbers $wids_tx) that are not editable by "
                                . "your account which have declared a direct dependency on the workitem you are duplicating."
                                . "  Your duplicate will NOT be linked as an antecedent of those workitems.";
                    }
                    drupal_set_message($msg,'info');
                    $new_map_ddw = [];
                    foreach($owned_wid2people as $wid=>$people)
                    {
                        $new_map_ddw[$wid] = $wid;
                    }
                    $myvalues['map_ddw'] = $new_map_ddw;
                }
            }
        }
        return $myvalues;
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
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        
        $form = $this->m_oPageHelper->getForm('E',$form
                , $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array($html_classname_overrides['action-button'])
            ),
            '#value' => t('Save Workitem Updates'),
            '#disabled' => FALSE
        );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $form;
    }
    
}
