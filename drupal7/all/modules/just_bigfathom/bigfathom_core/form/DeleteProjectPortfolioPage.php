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
require_once 'helper/ProjectPortfolioPageHelper.php';

/**
 * Delete one project_portfolio
 *
 * @author Frank Font
 */
class DeleteProjectPortfolioPage extends \bigfathom\ASimpleFormPage
{
    protected $m_project_portfolioid       = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oPageHelper    = NULL;
    
    function __construct($project_portfolioid)
    {
        if (!isset($project_portfolioid) || !is_numeric($project_portfolioid)) {
            throw new \Exception("Missing or invalid project_portfolioid value = " . $project_portfolioid);
        }
        
        $this->m_project_portfolioid = $project_portfolioid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/project_portfolio';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\ProjectPortfolioPageHelper($urls_arr);
        
        //Check for illegal access attempt
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        if(!$upb['roles']['systemroles']['summary']['is_systemdatatrustee'])
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to delete project_portfolioid!!!");
            throw new \Exception("Illegal access attempt!");
        }        
        
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        $myvalues = $this->m_oPageHelper->getFieldValues($this->m_project_portfolioid);
        
        //Check for illegal access attempt
        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        if(!$upb['roles']['systemroles']['summary']['is_systemadmin']  && $user->uid != $myvalues['owner_personid'])
        {
            error_log("HACKING WARNING: uid#{$user->uid} attempted to delete project_portfolioid={$this->m_project_portfolioid}!!!");
            throw new \Exception("Illegal access attempt!");
        }    
        return $myvalues;
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'D');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $project_portfolioid = $myvalues['id'];
            db_delete(DatabaseNamesHelper::$m_portfolio_tablename)
              ->condition('id', $project_portfolioid)
              ->execute();
            
            //If we are here then we had success.
            $msg = 'Deleted project portfolio item#' . $project_portfolioid . " having name of '" . $myvalues['portfolio_nm'] . "'";
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to delete ' . $myvalues['portfolio_nm']
                      . ' project context because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        $disabled = TRUE;
        $form = $this->m_oPageHelper->getForm('D',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        
        $buttontext = 'Delete Project Portfolio From System';
        $form['data_entry_area1']['action_buttons']['delete'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t($buttontext)
                , '#disabled' => FALSE
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
