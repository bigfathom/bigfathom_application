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
 * Help with Goals
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class QuadrantDisplayPageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_brainstorm_item_tablename = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $projectid=NULL)
    {
        $this->m_projectid = $projectid;
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_brainstorm_item_tablename = DatabaseNamesHelper::$m_brainstorm_item_tablename;
        
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
    
    public function getInUseDetails($brainstormid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            //$oConnectionChecker = new \bigfathom\ConnectionChecker();
            //return $oConnectionChecker->getConnectionsOfGoal($goalid);
            return FALSE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues($brainstorm_id='')
    {
        try
        {
            if($brainstorm_id != NULL)
            {
                //Get the core values 
                $myvalues['id'] = $brainstorm_id;
                $myvalues = db_select($this->m_brainstorm_item_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $brainstorm_id, '=')
                  ->execute()
                  ->fetchAssoc();
                $brainstorm_id = $myvalues['id'];
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['projectid'] = NULL;
                $myvalues['context_nm'] = NULL;
                $myvalues['item_nm'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['parkinglot_level'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                $myvalues['owner_personid'] = NULL;
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    public function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            if($formMode == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('brainstormid','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $brainstormid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($brainstormid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                    }
                }
            }
            
            if(trim($myvalues['item_nm']) == '')
            {
                form_set_error('item_nm','The item name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A')
                {
                    //Check for project ID
                    if(!isset($myvalues['projectid']) || trim($myvalues['projectid']) == '')
                    {
                        throw new \Exception('A project must be declared!');
                    }
                }
                if($bGood && ($formMode == 'A' || $formMode == 'E'))
                {
                    //Check for project ID
                    if(!isset($myvalues['projectid']) || trim($myvalues['projectid']) == '')
                    {
                            form_set_error('item_nm', 'Already have an item with this name');
                            $bGood = FALSE;
                    }
                    //Check for duplicate keys too
                    $result = db_select($this->m_brainstorm_item_tablename,'p')
                        ->fields('p')
                        ->condition('item_nm', $myvalues['item_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('item_nm', 'Already have an item with this name');
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
    
    public function getProjectOptions()
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getProjectsByID();
            foreach($all as $code=>$record)
            {
                $title_tx = $record['root_workitem_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getTypeOptions()
    {
            $myoptions = array();
            $myoptions[''] = 'Uncategorized';
            $myoptions['G'] = 'Goal';
            $myoptions['T'] = 'Task';
            return $myoptions;
    }
    
    public function getPersonOptions()
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getPersonsByID();
            foreach($all as $code=>$record)
            {
                $title_tx = $record['last_nm'] . ', ' . $record['first_nm'];
                $myoptions[$code] = $title_tx;
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
            if(isset($myvalues['projectid']))
            {
                $projectid = $myvalues['projectid'];
            } else {
                $projectid = $this->m_projectid;
                $myvalues['projectid'] = $this->m_projectid;
            }
            if(isset($myvalues['context_nm']))
            {
                $context_nm = $myvalues['context_nm'];
            } else {
                $context_nm = '';
            }
            if(isset($myvalues['item_nm']))
            {
                $item_nm = $myvalues['item_nm'];
            } else {
                $item_nm = '';
            }
            if(isset($myvalues['candidate_type']))
            {
                $candidate_type = $myvalues['candidate_type'];
            } else {
                $candidate_type = '';
            }
            
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 40;
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            
            $options_projects = $this->getProjectOptions();
            $options_people = $this->getPersonOptions();
            $options_type = $this->getTypeOptions();

            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_item_nm'] 
                = array('#type' => 'hidden', '#value' => $item_nm, '#disabled' => FALSE);        
            
            if($this->m_projectid == NULL)
            {
                $form['data_entry_area1']['projectid'] = array(
                    '#type' => 'select', 
                    '#title' => t('Dependent Project'),
                    '#default_value' => $projectid, 
                    '#options' => $options_projects, 
                    '#description' => t('A project that depends on outcome of this brainstorm item'),
                    '#multiple' => FALSE, 
                    '#disabled' => $disabled
                );
            } else {
                $form['hiddenthings']['projectid'] 
                    = array('#type' => 'hidden', '#value' => $this->m_projectid, '#disabled' => FALSE); 
            }
            
            $form['data_entry_area1']['item_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Item Name'),
                '#default_value' => $item_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this item'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['candidate_type'] = array(
                '#type' => 'select', 
                '#title' => t('Topic Type'),
                '#default_value' => $candidate_type, 
                '#options' => $options_type, 
                '#description' => t('The actionable categorization of this topic, if any.'),
                '#required' => FALSE,
                '#multiple' => FALSE, 
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['importance'] = array(
                '#type' => 'textfield',
                '#title' => t('Importance'),
                '#default_value' => $importance,
                '#size' => 3,
                '#maxlength' => 3,
                '#required' => TRUE,
                '#description' => t('Current importance for meeting this goal.  Scale is [0,100] with 0 being no importance whatsoever and 100 being nothing is more important.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['context_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Context Name'),
                '#default_value' => $context_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => FALSE,
                '#description' => t('An optional context for this brainstorm item'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose Text'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('Explanation of the goal purpose'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select', 
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Owner Person ID'),
                '#default_value' => $owner_personid, 
                '#options' => $options_people, 
                '#description' => t('Who is directly responsible for the successful Achievement of this goal?'),
                '#required' => TRUE,
                '#multiple' => FALSE, 
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if goal is active, else no.')
            );
            $form['data_entry_area1']['parkinglot_level'] = array(
                '#type' => 'radios',
                '#title' => t('Parking Lot'),
                '#default_value' => isset($myvalues['parkinglot_level']) ? $myvalues['parkinglot_level'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if item is moved into the parkinglot, else no.')
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
