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
 * Help with ProjectContext Statement
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectContextPageHelper
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

    public function getInUseDetails($project_contextid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfProjectContext($project_contextid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($project_contextid=NULL)
    {
        try
        {
            if($project_contextid != NULL)
            {
                //Get the core values 
                $myvalues = db_select(DatabaseNamesHelper::$m_project_context_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $project_contextid, '=')
                  ->execute()
                  ->fetchAssoc();
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['shortname'] = NULL;
                $myvalues['description_tx'] = NULL;
                $myvalues['ot_scf'] = NULL;
                $myvalues['ob_scf'] = NULL;
                $myvalues['active_yn'] = NULL;
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
                    form_set_error('shortname','Cannot delete without an ID!');
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
                        form_set_error('statement_nm',"Cannot delete because critical connections were found. $help_detail  Consider disabling this project context instead of removing it.");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['shortname']) == '')
            {
                form_set_error('shortname','The project context name cannot be empty');
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
                    $result = db_select(DatabaseNamesHelper::$m_project_context_tablename,'p')
                        ->fields('p')
                        ->condition('shortname', $myvalues['shortname'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('shortname', 'Already have a project context with this name');
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
            if(isset($myvalues['shortname']))
            {
                $shortname = $myvalues['shortname'];
            } else {
                $shortname = '';
            }
            if(isset($myvalues['description_tx']))
            {
                $description_tx = $myvalues['description_tx'];
            } else {
                $description_tx = '';
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
                    drupal_set_message("This project context cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_statement_nm'] 
                = array('#type' => 'hidden', '#value' => $statement_nm, '#disabled' => FALSE);        
            
            $form['data_entry_area1']['shortname'] = array(
                '#type' => 'textfield',
                '#title' => t('Name'),
                '#default_value' => $shortname,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this project context.'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['description_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Description'),
                '#default_value' => $description_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => FALSE,
                '#description' => t('Explain what this project context implies about the domain of a project'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Available'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('No if the project context has been retired.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
