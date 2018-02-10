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
 * Help with URI domains
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class URIDomainPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
            
    public function __construct($urls_arr, $my_classname=NULL)
    {
        try
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

    public function getInUseDetails($remote_uri_domain)
    {
        try
        {
            //module_load_include('php','bigfathom_core','core/ConnectionChecker');
            //$oConnectionChecker = new \bigfathom\ConnectionChecker();
            return [];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($uridomain=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($uridomain))
            {
                //Just get the one
                $record = $this->m_oMapHelper->getOneRichRemoteURIRecord($uridomain);
                $myvalues = $record;
            } else {
                //Initialize all the values to NULL
                $myvalues['remote_uri_domain'] = NULL;
                $myvalues['created_by_personid'] = NULL;
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
                if(!isset($myvalues['remote_uri_domain']))
                {
                    form_set_error('remote_uri_domain','Cannot delete without a domain!');
                    $bGood = FALSE;
                }
            }
            
            $remote_uri_domain = $myvalues['remote_uri_domain'];
            if(empty(trim($myvalues['remote_uri_domain'])))
            {
                form_set_error('remote_uri_domain','The domain name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    $cleanval = trim($myvalues['remote_uri_domain']);
                    if(FALSE !== strpos($cleanval,' '))
                    {
                        form_set_error('remote_uri_domain', "The domain text cannot contain spaces!");
                        $bGood = FALSE;
                    }
                    if(FALSE !== strpos($cleanval,'/'))
                    {
                        form_set_error('remote_uri_domain', "The domain text cannot contain / forward slashes!");
                        $bGood = FALSE;
                    }
                    if(FALSE !== strpos($cleanval,'\\'))
                    {
                        form_set_error('remote_uri_domain', "The domain text cannot contain \\ backslashes!");
                        $bGood = FALSE;
                    }
                    //Check for duplicate keys too
                    $foundrec = $this->m_oMapHelper->getOneRichRemoteURIRecord($remote_uri_domain,FALSE);
                    if($formMode == 'E')
                    {
                        $created_dt = $myvalues['created_dt'];
                    } else {
                        $created_dt = '0000';
                    }
                    if(!empty($foundrec) && $foundrec['created_dt'] != $created_dt)
                    {
                        $list_type = $foundrec['list_type'];
                        form_set_error('remote_uri_domain', "Already have a $list_type listed domain with this name");
                        $bGood = FALSE;
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
                
            if(isset($myvalues['remote_uri_domain']))
            {
                $remote_uri_domain = $myvalues['remote_uri_domain'];
            } else {
                $remote_uri_domain = '';
            }
            if(isset($myvalues['person_name']))
            {
                $person_name = $myvalues['person_name'];
            } else {
                $person_name = '';
            }
            if(isset($myvalues['created_dt']))
            {
                $created_dt = $myvalues['created_dt'];
            } else {
                $created_dt = '';
            }
            if(isset($myvalues['list_type']))
            {
                $list_type = $myvalues['list_type'];
            } else {
                $list_type = 'MISSING';
            }
            
            if($formType == 'A' || $formType == 'E')
            {
                $hidden_remote_uri_domain_field_name = 'original_remote_uri_domain';
                $show_remote_uri_domain_field_name = 'remote_uri_domain';
            } else {
                $hidden_remote_uri_domain_field_name = 'remote_uri_domain';
                $show_remote_uri_domain_field_name = 'show_remote_uri_domain';
            }
            
            $form['hiddenthings'][$hidden_remote_uri_domain_field_name] 
                = array('#type' => 'hidden', '#value' => $remote_uri_domain, '#disabled' => FALSE); 
            $form['hiddenthings']['created_dt'] 
                = array('#type' => 'hidden', '#value' => $created_dt, '#disabled' => FALSE); 
            $form['hiddenthings']['list_type'] 
                = array('#type' => 'hidden', '#value' => $list_type, '#disabled' => FALSE); 
            
            $form['data_entry_area1'][$show_remote_uri_domain_field_name] = array(
                '#type' => 'textfield',
                '#title' => t('Domain'),
                '#default_value' => $remote_uri_domain,
                '#size' => 60,
                '#maxlength' => 128,
                '#required' => TRUE,
                '#description' => t('The domain component of a valid URI'),
                '#disabled' => $disabled
            );

            if($formType != 'A')
            {
                $form['data_entry_area1']['show_person_nm'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Created By'),
                    '#default_value' => $person_name,
                    '#size' => 40,
                    '#description' => t('Who created or last updated this domain entry in the system'),
                    '#disabled' => TRUE
                );
                $form['data_entry_area1']['show_created_dt'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Created Date'),
                    '#default_value' => $created_dt,
                    '#size' => 20,
                    '#description' => t('When this domain was created or last updated in the system'),
                    '#disabled' => TRUE
                );
            }
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
