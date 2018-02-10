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
 * Help with holiday information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class HolidayPageHelper
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

    /**
     * Get the values to populate the form.
     */
    function getFieldValues($holidayid=NULL)
    {
        try
        {
            if($holidayid != NULL)
            {
                //Get the core values 
                $myvalues = db_select(DatabaseNamesHelper::$m_holiday_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $holidayid, '=')
                  ->execute()
                  ->fetchAssoc();
                //$myvalues['project_count'] = $this->m_oMapHelper->getGroupMembers($holidayid);
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['holiday_dt'] = NULL;
                $myvalues['holiday_nm'] = NULL;
                $myvalues['countryid'] = NULL;
                $myvalues['stateid'] = NULL;
                $myvalues['comment_tx'] = NULL;
                $myvalues['apply_to_all_users_yn'] = NULL;
                $myvalues['created_by_personid'] = NULL;
                $myvalues['updated_by_personid'] = NULL;
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
                    form_set_error('holiday_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                }
            }

            if(trim($myvalues['holiday_dt']) == '')
            {
                form_set_error('holiday_dt','The holiday date cannot be empty');
                $bGood = FALSE;
            }
            
            if(trim($myvalues['holiday_nm']) == '')
            {
                form_set_error('holiday_nm','The holiday name cannot be empty');
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
                    $result = db_select(DatabaseNamesHelper::$m_holiday_tablename,'p')
                        ->fields('p')
                        ->condition('holiday_nm', $myvalues['holiday_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('holiday_nm', 'Already have a holiday with this name');
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
            if(isset($myvalues['holiday_dt']))
            {
                $holiday_dt = $myvalues['holiday_dt'];
            } else {
                $holiday_dt = '';
            }
            if(isset($myvalues['holiday_nm']))
            {
                $holiday_nm = $myvalues['holiday_nm'];
            } else {
                $holiday_nm = '';
            }
            if(isset($myvalues['countryid']))
            {
                $countryid = $myvalues['countryid'];
            } else {
                $countryid = NULL;
            }
            if(isset($myvalues['stateid']))
            {
                $stateid = $myvalues['stateid'];
            } else {
                $stateid = NULL;
            }
            if(isset($myvalues['apply_to_all_users_yn']))
            {
                $apply_to_all_users_yn = $myvalues['apply_to_all_users_yn'];
            } else {
                $apply_to_all_users_yn = 0;
            }
            if(isset($myvalues['updated_by_personid']))
            {
                $updated_by_personid = $myvalues['updated_by_personid'];
            } else {
                $updated_by_personid = $this_uid;
            }
            if(isset($myvalues['comment_tx']))
            {
                $comment_tx = $myvalues['comment_tx'];
            } else {
                $comment_tx = '';
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_holiday_nm'] 
                = array('#type' => 'hidden', '#value' => $holiday_nm, '#disabled' => FALSE);        
            
            $showcolname_holiday_nm = 'holiday_nm';
            $disable_holiday_nm = $disabled || $id==1 || $id==10;
            
            $options_countries = $this->getCountryOptions();
            $options_states = $this->getStateOptions();
            
            if($disable_holiday_nm)
            {
                $form['hiddenthings']['holiday_nm'] 
                    = array('#type' => 'hidden', '#value' => $holiday_nm, '#disabled' => FALSE);        
                $showcolname_holiday_nm = 'show_holiday_nm';
            }
            
            $form['data_entry_area1'][$showcolname_holiday_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Holiday Name'),
                '#default_value' => $holiday_nm,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('The unique convenient name for this holiday.'),
                '#disabled' => $disable_holiday_nm
            );

            $form['data_entry_area1']['countryid'] = array(
                '#type' => 'select',
                '#title' => t('Country'),
                '#default_value' => $countryid,
                '#options' => $options_countries,
                '#required' => FALSE,
                '#description' => t('The country in which this holiday occurs'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['stateid'] = array(
                '#type' => 'select',
                '#title' => t('State'),
                '#default_value' => $stateid,
                '#options' => $options_states,
                '#required' => FALSE,
                '#description' => t('The state in which this holiday occurs'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['holiday_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Occurance'),
                '#default_value' => $holiday_dt,
                '#required' => TRUE,
                '#description' => t('When this holiday occurs'),
                '#disabled' => $disabled
            );            
            $form['data_entry_area1']['comment_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Notes'),
                '#default_value' => $comment_tx,
                '#size' => 512,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t("Notes, if any, about this holiday."),
                '#disabled' => $disabled
            );


            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['apply_to_all_users_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Apply to all users'),
                '#default_value' => $apply_to_all_users_yn,
                '#options' => $ynoptions,
                '#required' => TRUE,
                '#description' => t('If yes, then the system assumes all users of the system observe this holiday')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
