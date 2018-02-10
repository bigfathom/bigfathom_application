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
 * Edit project_portfolio
 *
 * @author Frank Font
 */
class EditProjectPortfolioPage extends \bigfathom\ASimpleFormPage
{
    protected $m_project_portfolioid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    function __construct($project_portfolioid)
    {
        if (!isset($project_portfolioid) || !is_numeric($project_portfolioid)) 
        {
            throw new \Exception("Missing or invalid project_portfolioid value = " . $project_portfolioid);
        }
        
        //Check for illegal access attempt
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        if(!$upb['roles']['systemroles']['summary']['is_systemdatatrustee'])
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit project_portfolioid=$project_portfolioid!!!");
            throw new \Exception("Illegal access attempt!");
        }        
        
        $this->m_project_portfolioid = $project_portfolioid;
        $urls_arr = array();
        $urls_arr['return'] = 'bigfathom/sitemanage/project_portfolio';
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\ProjectPortfolioPageHelper($urls_arr);
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
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit project_portfolioid={$this->m_project_portfolioid}!!!");
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
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'E');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $project_portfolioid = $myvalues['id'];
            db_update(DatabaseNamesHelper::$m_portfolio_tablename)->fields(array(
                  'portfolio_nm' => $myvalues['portfolio_nm'],
                  'purpose_tx' => $myvalues['purpose_tx'],
                  'owner_personid' => $myvalues['owner_personid'],
                  'active_yn' => $myvalues['active_yn'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
                ))
                    ->condition('id', $project_portfolioid,'=')
                    ->execute();
            
            $members_ar = $myvalues['map_project2portfolio'];
            
            //Delete existing selections and then add the current selections
            $delete_qry = db_delete(DatabaseNamesHelper::$m_map_project2portfolio_tablename);
            $delete_qry->condition('portfolioid',$project_portfolioid,'=');
            $delete_qry->execute();
            foreach($members_ar as $projectid)
            {
                $member_qry = db_insert(DatabaseNamesHelper::$m_map_project2portfolio_tablename)->fields(array(
                      'portfolioid' => $project_portfolioid,
                      'projectid' => $projectid,
                      'created_dt' => $updated_dt
                  ));
                $member_qry->execute(); 
            }
            
            //If we are here then we had success.
            $msg = 'Saved update for ' . $myvalues['portfolio_nm'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['portfolio_nm']
                      . ' project portfolio because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
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
        
        $form = $this->m_oPageHelper->getForm('E',$base_form
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
            '#value' => t('Save Project Portfolio Updates'),
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
