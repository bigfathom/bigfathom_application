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
 * Help with Location
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class LocationPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_location_tablename = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
            
    public function __construct($urls_arr, $my_classname=NULL)
    {
        try
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_location_tablename = DatabaseNamesHelper::$m_location_tablename;

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
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            $this->m_aUPB = $this->m_oUAH->getUserProfileBundle();
            if($this->m_aUPB['roles']['systemroles']['summary']['is_systemadmin'])
            {
                $this->m_is_systemadmin = TRUE;
            } else {
                $this->m_is_systemadmin = FALSE;
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getInUseDetails($locationid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfLocation($locationid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($locationid=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($locationid))
            {
                //Just get the one
                $myvalues = $this->m_oMapHelper->getLocationsByID($locationid);
            } else {
                //Initialize all the values to NULL
                $myvalues['id'] = NULL;
                $myvalues['shortname'] = NULL;
                $myvalues['address_line1'] = NULL;
                $myvalues['address_line2'] = NULL;
                $myvalues['city_tx'] = NULL;
                $myvalues['stateid'] = NULL;
                $myvalues['countryid'] = NULL;
                $myvalues['description_tx'] = NULL;
                $myvalues['ot_scf'] = NULL;
                $myvalues['ob_scf'] = NULL;
                $myvalues['created_dt'] = NULL;
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getStateOptions($show_none_option=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getAmericanStatesByID();
            $options = [];
            if($show_none_option!==FALSE)
            {
                $options[0] = 'None';
            }
            foreach($all as $id=>$record)
            {
                $show_tx = $record['name'] . " (" . $record['abbr'] . ")";
                $options[$id] = $show_tx;
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getCountryOptions($show_none_option=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getCountriesByID();
            $options = [];
            if($show_none_option!==FALSE)
            {
                $options[0] = 'None';
            }
            foreach($all as $id=>$record)
            {
                $show_tx = $record['name'] . " (" . $record['abbr'] . ")";
                $options[$id] = $show_tx;
            }
            return $options;
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
                    $connection_details = $this->getInUseDetails($myid);
                    $critical_connections_found = $connection_details['critical_connections_found'];
                    if($connection_details['connections_found'])
                    {
                        form_set_error('shortname','Cannot delete because '.$critical_connections_found.' connections were found.  Consider marking inactive instead.');
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['shortname']) == '')
            {
                form_set_error('shortname','The location name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    //Check for duplicate keys too
                    $result = db_select($this->m_location_tablename,'p')
                        ->fields('p')
                        ->condition('shortname', $myvalues['shortname'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('shortname', 'Already have a location with this name');
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
            
            $thelimit = BIGFATHOM_MAX_LOCATIONS_IN_SYSTEM;
            if($formType=='A' && $this->m_oMapHelper->getCountLocations() >= $thelimit)
            {
                drupal_set_message("Cannot add another location because your system already has the configuration allowed limit of $thelimit",'error');
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
            if(isset($myvalues['description_tx']))
            {
                $description_tx = $myvalues['description_tx'];
            } else {
                $description_tx = '';
            }
            if(isset($myvalues['address_line1']))
            {
                $address_line1 = $myvalues['address_line1'];
            } else {
                $address_line1 = '';
            }
            if(isset($myvalues['address_line2']))
            {
                $address_line2 = $myvalues['address_line2'];
            } else {
                $address_line2 = '';
            }
            if(isset($myvalues['city_tx']))
            {
                $city_tx = $myvalues['city_tx'];
            } else {
                $city_tx = '';
            }
            if(isset($myvalues['stateid']))
            {
                $stateid = $myvalues['stateid'];
            } else {
                $stateid = NULL;
            }
            $options_state = $this->getStateOptions();
            
            if(isset($myvalues['countryid']))
            {
                $countryid = $myvalues['countryid'];
            } else {
                $countryid = NULL;
            }
            $options_country = $this->getCountryOptions();

            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This location cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            
            $form['data_entry_area1']['shortname'] = array(
                '#type' => 'textfield',
                '#title' => t('Shortname'),
                '#default_value' => $shortname,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this location'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['description_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Short Description'),
                '#default_value' => $description_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t("A short relevant description of the location"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['countryid'] = array(
                '#type' => 'select',
                '#title' => t('Country'),
                '#default_value' => $countryid,
                '#options' => $options_country,
                '#required' => FALSE,
                '#description' => t('State in the USA'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['address_line1'] = array(
                '#type' => 'textfield',
                '#title' => t('Address Line 1'),
                '#default_value' => $address_line1,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('First address line of the location'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['address_line2'] = array(
                '#type' => 'textfield',
                '#title' => t('Address Line 2'),
                '#default_value' => $address_line2,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => FALSE,
                '#description' => t('Second address line of the location'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['city_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('City'),
                '#default_value' => $city_tx,
                '#size' => 60,
                '#maxlength' => 60,
                '#required' => TRUE,
                '#description' => t('City of the location'),
                '#disabled' => $disabled
            );
            $form['data_entry_area1']['stateid'] = array(
                '#type' => 'select',
                '#title' => t('State'),
                '#default_value' => $stateid,
                '#options' => $options_state,
                '#required' => FALSE,
                '#description' => t('State in the USA'),
                '#disabled' => $disabled,
                '#states' => array(
                    'visible' => array(
                        ':input[name="countryid"]' => array('value'=>840)
                    ),
                )
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Is Accurate'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if location is still appropriate for new references, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
