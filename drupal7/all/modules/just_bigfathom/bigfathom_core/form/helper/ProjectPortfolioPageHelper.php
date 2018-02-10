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

/**
 * Help with Project Portfolio data
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectPortfolioPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        //module_load_include('php','bigfathom_core','core/Portfolio');
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $loaded2 = module_load_include('php','bigfathom_core','core/FormHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the FormHelper class');
        }
        $this->m_oFormHelper = new \bigfathom\FormHelper();
    }

    public function getInUseDetails($project_contextid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfProjectPortfolio($project_contextid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($project_portfolioid=NULL)
    {
        try
        {
            if($project_portfolioid != NULL)
            {
                //Get the core values 
                $myvalues = db_select(DatabaseNamesHelper::$m_portfolio_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $project_portfolioid, '=')
                  ->execute()
                  ->fetchAssoc();
                
                $id = $myvalues['id'];
                $members_ar = [];
                $sMemberSQL = "SELECT projectid FROM " . DatabaseNamesHelper::$m_map_project2portfolio_tablename 
                        . " WHERE portfolioid=$id";
                $result = db_query($sMemberSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['projectid'];
                    $members_ar[] = $id;
                }
                $myvalues['map_project2portfolio'] = $members_ar;
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['portfolio_nm'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['map_project2portfolio'] = [];
                $myvalues['updated_dt'] = NULL;
                $myvalues['created_dt'] = NULL;
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Validate the proposed values.
     */
    function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            if($formMode == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('portfolio_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $project_contextid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($project_contextid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $project_list = $connection_details['project_list'];
                        $total_projects = count($project_list);
                        $help_detail = "($total_projects projects)";
                        form_set_error('statement_nm',"Cannot delete because critical connections were found. $help_detail  Consider disabling this project portfolio instead of removing it.");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['portfolio_nm']) == '')
            {
                form_set_error('portfolio_nm','The project context name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    if($formMode == 'A')
                    {
                        $allowed_count = 0;
                    } else {
                        $allowed_count = 1;
                    }
                    //Check for duplicate keys too
                    $result = db_select(DatabaseNamesHelper::$m_portfolio_tablename,'p')
                        ->fields('p')
                        ->condition('portfolio_nm', $myvalues['portfolio_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('portfolio_nm', 'Already have a project context with this name');
                            $bGood = FALSE;
                        }
                    }
                }
            }

            //Done with all validations.
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getOwnerOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidatePortfolioOwners();
            foreach($all as $id=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$id] = $title_tx;
            }
            if($include_sysadmin && !array_key_exists(1, $myoptions))
            {
                $myoptions[1] = "System Admin";
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCandidateMemberOptions()
    {
        try
        {
            //Get all the relevant select options
            $order_by_ar = array('role_nm');
            $filter_ar = [];
            $only_active=TRUE;
            $all = $this->m_oMapHelper->getSmallProjectInfoForFilter($filter_ar, $only_active);
            $options = [];
            foreach($all as $id=>$record)
            {
                $role_nm = $record['name'];
                $options[$id] = $role_nm;
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @param letter $formType valid values are A, E, D, and V
     * @return drupal renderable array
     * @throws \Exception
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['selectable-text']))
            {
                $html_classname_overrides['selectable-text'] = 'selectable-text';
            }

            $form['data_entry_area1'] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
                '#disabled' => $disabled,
            );     
                
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
            }
            if(isset($myvalues['portfolio_nm']))
            {
                $portfolio_nm = $myvalues['portfolio_nm'];
            } else {
                $portfolio_nm = '';
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['active_yn']))
            {
                $active_yn = $myvalues['active_yn'];
            } else {
                $active_yn = 1;
            }

            if(isset($myvalues['map_project2portfolio']))
            {
                $default_members = $myvalues['map_project2portfolio'];
            } else {
                $default_members = array();
            }
            
            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This project context cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
          
            $all_candidate_member_options = $this->getCandidateMemberOptions();
            if($formType == 'V' || $formType == 'D')
            {
                //Only display what is selected
                $display_member_options = [];
                foreach($all_candidate_member_options as $projectid=>$content)
                {
                    if(in_array($projectid, $default_members))
                    {
                        $display_member_options[$projectid] = $content;
                    }
                }
            } else {
                //Display them all
                $display_member_options =$all_candidate_member_options;
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            if(TRUE || $formType == 'A')
            {
                $form['hiddenthings']['active_yn'] 
                    = array('#type' => 'hidden', '#value' => $active_yn, '#disabled' => FALSE); 
            }
            
            $options_candidate_owners = $this->getOwnerOptions();
            
            $form['data_entry_area1']['portfolio_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Name'),
                '#default_value' => $portfolio_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this project portfolio.'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => FALSE,
                '#description' => t('Briefly explain the purpose of grouping projects together into this portfolio'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => t('Portfolio Owner'),
                '#default_value' => $owner_personid,
                '#options' => $options_candidate_owners,
                '#required' => TRUE,
                '#description' => t('Who controls the definition and membership of this portfolio?'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            if(FALSE && $formType !== 'A')
            {
                $form['data_entry_area1']['active_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Available'),
                    '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                    '#options' => $ynoptions,
                    '#description' => t('No if the project portfolio has been retired.')
                );
            }
            
            $form['data_entry_area1']['map_project2portfolio'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Member Projects'
                    , $default_members
                    , $display_member_options
                    , TRUE
                    , 'The member projects of this portfolio'
                );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
