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
 * Help with Roles
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class RolePageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_role_tablename = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_role_tablename = DatabaseNamesHelper::$m_role_tablename;
        
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    public function getInUseDetails($roleid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfRole($roleid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($role_nm='')
    {
        try
        {
            if($role_nm != NULL)
            {
                //Get the core values 
                $myvalues['role_nm'] = $role_nm;
                $myvalues = db_select($this->m_role_tablename, 'n')
                  ->fields('n')
                  ->condition('role_nm', $role_nm, '=')
                  ->execute()
                  ->fetchAssoc();
                $role_id = $myvalues['id'];
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['role_nm'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['groupleader_yn'] = NULL;
                $myvalues['projectleader_yn'] = NULL;
                $myvalues['sprintleader_yn'] = NULL;
                $myvalues['tester_yn'] = NULL;
                $myvalues['workitemcreator_yn'] = NULL;
                $myvalues['workitemowner_yn'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                $myvalues['parent_role_id'] = NULL;
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
                    $connection_infobundle = $this->getInUseDetails($myid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $connection_markup = "<ul>";
                        foreach($connection_details as $key=>$details)
                        {
                            $refcount = count($details);
                            if($refcount > 0)
                            {
                                $connection_markup .= "\n<li>$key has " . $refcount . " references"; 
                            }
                        }
                        $connection_markup .="\n</ul>";
                        $help_detail = $connection_markup;
                        form_set_error('role_nm',"Cannot delete because critical connections were found. "
                                . "$help_detail  Consider disabling this project role instead of removing it.");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['role_nm']) == '')
            {
                form_set_error('role_nm','The role name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    if($myvalues['workitemcreator_yn'] == 1 && $myvalues['workitemowner_yn'] == 0)
                    {
                        form_set_error('workitemowner_yn','A workitem creator must also be marked as an owner');
                        $bGood = FALSE;
                    }
                    
                    //Check for duplicate keys too
                    $result = db_select($this->m_role_tablename,'p')
                        ->fields('p')
                        ->condition('role_nm', $myvalues['role_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('role_nm', 'Already have a role with this name');
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
            
            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This project role cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_role_nm'] 
                = array('#type' => 'hidden', '#value' => $role_nm, '#disabled' => FALSE);        
            
            $showcolname_role_nm = 'role_nm';
            $disable_role_nm = $disabled || $formType == 'E';
            if($disable_role_nm)
            {
                $form['hiddenthings']['role_nm'] 
                    = array('#type' => 'hidden', '#value' => $role_nm, '#disabled' => FALSE);        
                $showcolname_role_nm = 'show_role_nm';
            }
            
            $form['data_entry_area1'][$showcolname_role_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Role Name'),
                '#default_value' => $role_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this role'),
                '#disabled' => $disable_role_nm
            );
            
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose Text'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('Explanation of the role purpose'),
                '#disabled' => $disabled
            );

            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['roleflags'] = array(
                '#type' => 'fieldset', 
                '#title' => 'Capability Flags',
                '#collapsible' => FALSE,
                '#collapsed' => FALSE, 
                '#tree' => FALSE,
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['roleflags']['groupleader_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Group Manager'),
                '#default_value' => isset($myvalues['groupleader_yn']) ? $myvalues['groupleader_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a group leader role, else no.')
            );
            $form['data_entry_area1']['roleflags']['projectleader_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Project Leader'),
                '#default_value' => isset($myvalues['projectleader_yn']) ? $myvalues['projectleader_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a project manager role, else no.')
            );
            $form['data_entry_area1']['roleflags']['sprintleader_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Sprint Leader'),
                '#default_value' => isset($myvalues['sprintleader_yn']) ? $myvalues['sprintleader_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a potential sprint leader role, else no.')
            );
            $form['data_entry_area1']['roleflags']['workitemcreator_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Workitem Creator'),
                '#default_value' => isset($myvalues['workitemcreator_yn']) ? $myvalues['workitemcreator_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a potential workitem creator role, else no.')
            );
            $form['data_entry_area1']['roleflags']['workitemowner_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Workitem Owner'),
                '#default_value' => isset($myvalues['workitemowner_yn']) ? $myvalues['workitemowner_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a potential workitem owner role, else no.')
            );
            $form['data_entry_area1']['roleflags']['tester_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Tester'),
                '#default_value' => isset($myvalues['tester_yn']) ? $myvalues['tester_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this is considered a potential tester role, else no.')
            );

            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Enabled'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if role is available for use in the application, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
