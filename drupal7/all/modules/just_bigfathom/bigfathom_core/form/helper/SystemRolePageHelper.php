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

/**
 * Help with SystemRoles
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class SystemRolePageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_systemrole_tablename = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_systemrole_tablename = DatabaseNamesHelper::$m_systemrole_tablename;
        
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    public function getInUseDetails($systemroleid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfSystemRole($systemroleid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($systemrole_id=NULL)
    {
        try
        {
            if($systemrole_id != NULL)
            {
                //Get the core values 
                $myvalues = db_select($this->m_systemrole_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $systemrole_id, '=')
                  ->execute()
                  ->fetchAssoc();
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['role_nm'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                $myvalues['parent_systemrole_id'] = NULL;
                $myvalues['parent_goal_id'] = NULL;
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
    function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            if($formMode == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('role_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $myid = $myvalues['id'];
                    $connection_details = $this->getInUseDetails($myid);
                    if($connection_details['connections_found'])
                    {
                        //TODO --- enhance the message to say what connections
                        form_set_error('role_nm','Cannot delete because connections were found.  Consider marking inactive instead.');
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['role_nm']) == '')
            {
                form_set_error('role_nm','The systemrole name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    //Check for duplicate keys too
                    $result = db_select($this->m_systemrole_tablename,'p')
                        ->fields('p')
                        ->condition('role_nm', $myvalues['role_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('role_nm', 'Already have a systemrole with this name');
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
            if(isset($myvalues['role_nm']))
            {
                $role_nm = $myvalues['role_nm'];
            } else {
                $role_nm = '';
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_role_nm'] 
                = array('#type' => 'hidden', '#value' => $role_nm, '#disabled' => FALSE);        
            
            $showcolname_importance = 'importance';
            $showcolname_role_nm = 'role_nm';
            $disable_role_nm = $disabled;       //Default behavior
            if($disable_role_nm)
            {
                $form['hiddenthings']['role_nm'] 
                    = array('#type' => 'hidden', '#value' => $role_nm, '#disabled' => FALSE);        
                $showcolname_role_nm = 'show_role_nm';
            }
            
            $form['data_entry_area1'][$showcolname_role_nm] = array(
                '#type' => 'textfield',
                '#title' => t('SystemRole Name'),
                '#default_value' => $role_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this systemrole'),
                '#disabled' => $disable_role_nm
            );
            
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose Text'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('Explanation of the systemrole purpose'),
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
                '#description' => t('Yes if systemrole is active, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
