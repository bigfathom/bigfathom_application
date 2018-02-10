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
 * Help with External Resource
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ExternalResourcePageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_external_resource_tablename = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
            
    public function __construct($urls_arr, $my_classname=NULL)
    {
        try
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_external_resource_tablename = DatabaseNamesHelper::$m_external_resource_tablename;

            //module_load_include('php','bigfathom_core','core/Context');
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
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getInUseDetails($external_resourceid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfExternalResource($external_resourceid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($external_resourceid=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($external_resourceid))
            {
                $myvalues = $this->m_oMapHelper->getExternalResourceByID($external_resourceid);
            } else {
                //Initialize all the values to NULL
                $myvalues['id'] = NULL;
                $myvalues['shortname'] = NULL;
                $myvalues['name'] = NULL;
                $myvalues['description_tx'] = NULL;
                $myvalues['primary_locationid'] = NULL;
                $myvalues['ot_scf'] = NULL;
                $myvalues['ob_scf'] = NULL;
                $myvalues['condition_cd'] = NULL;
                $myvalues['condition_set_dt'] = NULL;
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
                    form_set_error('shortname','Cannot delete without an ID!');
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
                        form_set_error('shortname',"Cannot delete because critical connections were found. "
                                . "$help_detail  Consider retiring this external resource instead of removing it.");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['shortname']) == '')
            {
                form_set_error('shortname','The external_resource name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    //Check for duplicate keys too
                    $result = db_select($this->m_external_resource_tablename,'p')
                        ->fields('p')
                        ->condition('shortname', $myvalues['shortname'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('shortname', 'Already have a external_resource with this name');
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
    
    public function getLocationOptions($show_none_option=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getLocationsByID();
            $options = array();
            if($show_none_option!==FALSE)
            {
                $options[0] = 'None';
            }
            foreach($all as $id=>$record)
            {
                $show_tx = $record['shortname'] . ' | ' . $record['city_tx'] . ' ' . $record['state_abbr'];
                $options[$id] = $show_tx;
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getExternalResourceConditionOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getExternalResourceConditionByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_condition_state'];
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

            $thelimit = BIGFATHOM_MAX_EXTERNALRESOURCE_IN_SYSTEM;
            if($formType=='A' && $this->m_oMapHelper->getCountExternalResources() >= $thelimit)
            {
                drupal_set_message("Cannot add another external resource because your system already has the configuration allowed limit of $thelimit",'error');
                $disabled = TRUE;
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
            if(isset($myvalues['shortname']))
            {
                $shortname = $myvalues['shortname'];
            } else {
                $shortname = '';
            }
            if(isset($myvalues['name']))
            {
                $name = $myvalues['name'];
            } else {
                $name = '';
            }
            if(isset($myvalues['description_tx']))
            {
                $description_tx = $myvalues['description_tx'];
            } else {
                $description_tx = '';
            }
            if(isset($myvalues['primary_locationid']))
            {
                $primary_locationid = $myvalues['primary_locationid'];
            } else {
                $primary_locationid = NULL;
            }
            $options_location = $this->getLocationOptions(); 

            if(isset($myvalues['condition_cd']))
            {
                $condition_cd = $myvalues['condition_cd'];
            } else {
                $condition_cd = NULL;
            }
            $options_condition = $this->getExternalResourceConditionOptions();

            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This external resource cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            
            $form['data_entry_area1']['shortname'] = array(
                '#type' => 'textfield',
                '#title' => t('External Resource Short Name'),
                '#default_value' => $shortname,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique short name for this external_resource that the system can use as a shorthand reference'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['name'] = array(
                '#type' => 'textfield',
                '#title' => t('Full Name'),
                '#default_value' => $name,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('The full descriptive name of the external resource'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['description_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Relevant Description'),
                '#default_value' => $description_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => TRUE,
                '#description' => t("A relevant description of the external resource"),
                '#disabled' => $disabled
            );
            $form['data_entry_area1']['primary_locationid'] = array(
                '#type' => 'select',
                '#title' => t('Location'),
                '#default_value' => $primary_locationid,
                '#options' => $options_location,
                '#required' => FALSE,
                '#description' => t('Primary location of this external resource'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['condition_cd'] = array(
                '#type' => 'select',
                '#title' => t('Condition'),
                '#default_value' => $condition_cd,
                '#options' => $options_condition,
                '#required' => FALSE,
                '#description' => t('Condition of this external resource'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Authorized for Work'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if external resource is available for work, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
