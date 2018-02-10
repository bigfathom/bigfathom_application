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
 * Help with Vision Statement
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class VisionStatementPageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        //module_load_include('php','bigfathom_core','core/Context');
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    public function getInUseDetails($visionstatementid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfVisionStatement($visionstatementid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($visionstatementid=NULL)
    {
        try
        {
            if($visionstatementid != NULL)
            {
                //Get the core values 
                $myvalues = db_select(DatabaseNamesHelper::$m_visionstatement_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $visionstatementid, '=')
                  ->execute()
                  ->fetchAssoc();
                //$myvalues['project_count'] = $this->m_oMapHelper->getGroupMembers($visionstatementid);
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['statement_nm'] = NULL;
                $myvalues['statement_tx'] = NULL;
                $myvalues['references_tx'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['active_yn'] = NULL;
                //$myvalues['member_list'] = array();
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
                    form_set_error('statement_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $visionstatementid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($visionstatementid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $project_list = $connection_details['project_list'];
                        $total_projects = count($project_list);
                        $help_detail = "($total_projects projects)";
                        form_set_error('statement_nm',"Cannot delete because critical connections were found. "
                                . "$help_detail  Consider disabling this vision statement instead of removing it.");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['statement_nm']) == '')
            {
                form_set_error('statement_nm','The visionstatement name cannot be empty');
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
                    $result = db_select(DatabaseNamesHelper::$m_visionstatement_tablename,'p')
                        ->fields('p')
                        ->condition('statement_nm', $myvalues['statement_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('statement_nm', 'Already have a visionstatement with this name');
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
    
    public function getVisionStatementOwnerOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getVisionStatementOwners();
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
            if(isset($myvalues['statement_nm']))
            {
                $statement_nm = $myvalues['statement_nm'];
            } else {
                $statement_nm = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['statement_tx']))
            {
                $statement_tx = $myvalues['statement_tx'];
            } else {
                $statement_tx = '';
            }
            if(isset($myvalues['references_tx']))
            {
                $references_tx = $myvalues['references_tx'];
            } else {
                $references_tx = '';
            }
            
            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This vision statement cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_statement_nm'] 
                = array('#type' => 'hidden', '#value' => $statement_nm, '#disabled' => FALSE);        
            
            $showcolname_statement_nm = 'statement_nm';
            $disable_statement_nm = $disabled || $id==1 || $id==10;
            
            $options_visionstatementowners = $this->getVisionStatementOwnerOptions();
            
            if($disable_statement_nm)
            {
                $form['hiddenthings']['statement_nm'] 
                    = array('#type' => 'hidden', '#value' => $statement_nm, '#disabled' => FALSE);        
                $showcolname_statement_nm = 'show_statement_nm';
            }
            
            $form['data_entry_area1'][$showcolname_statement_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Statement Name'),
                '#default_value' => $statement_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique convenient name for this vision statement.'),
                '#disabled' => $disable_statement_nm
            );
            
            $form['data_entry_area1']['statement_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Statement Text'),
                '#default_value' => $statement_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => TRUE,
                '#description' => t("This statement clearly and concisely communicates your organization's overall goals, and can serve as a tool for strategic decision-making across teams."),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['references_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('References'),
                '#default_value' => $references_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => FALSE,
                '#description' => t('Optional references in support of the vision statement'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => t('Statement Owner'),
                '#default_value' => $owner_personid,
                '#options' => $options_visionstatementowners,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for explaining any quesitons about this vision statement?'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Live'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('No if the vision statement has been retired.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
