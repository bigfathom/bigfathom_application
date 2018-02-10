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
 * Help with Persons
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class PersonPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_person_tablename = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    protected $m_oUAH = NULL;
    protected $m_oUPB = NULL;
    protected $m_is_systemadmin = FALSE;
        
    public function __construct($urls_arr, $my_classname=NULL)
    {
        try
        {
            $this->m_show_group_membership_roles = TRUE;
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_person_tablename = DatabaseNamesHelper::$m_person_tablename;

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
            
            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                    throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            $this->m_aUPB = $this->m_oUAH->getUserProfileBundle();
            if($this->m_aUPB['roles']['systemroles']['summary']['is_systemadmin'])
            {
                $this->m_is_systemadmin = TRUE;
            } else {
                $this->m_is_systemadmin = FALSE;
            }
            if($this->m_aUPB['roles']['systemroles']['summary']['is_systemwriter'])
            {
                $this->m_is_systemwriter = TRUE;
            } else {
                $this->m_is_systemwriter = FALSE;
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function canChangeSAFlags()
    {
        return $this->m_is_systemadmin;
    }
    
    public function setShowGroupMembershipRoles($setting=TRUE)
    {
        $this->m_show_group_membership_roles = $setting;
    }
    
    public function getInUseDetails($personid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfPerson($personid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($personid=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($personid))
            {
                $myfilter = array($personid);
                $oneuser = $this->m_oMapHelper->getOnePersonDetailData($personid);
                foreach($oneuser as $colname=>$content)
                {
                    if($colname == 'maps')
                    {
                        foreach($content as $mapname=>$mapcontent)
                        {
                            $myvalues['map_'.$mapname] = $mapcontent;
                        }
                    } else {
                        $myvalues[$colname] = $content;
                    }
                }
                
            } else {
                //Initialize all the values to NULL
                $myvalues['id'] = NULL;
                $myvalues['shortname'] = NULL;
                $myvalues['first_nm'] = NULL;
                $myvalues['last_nm'] = NULL;
                $myvalues['updated_dt'] = NULL;
                $myvalues['created_dt'] = NULL;
                $myvalues['primary_phone'] = NULL;
                $myvalues['secondary_phone'] = NULL;
                $myvalues['secondary_email'] = NULL;
                $myvalues['primary_locationid'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['can_login_yn'] = NULL;
                $myvalues['systemadmin_yn'] = NULL;
                $myvalues['email'] = NULL;
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Validate the proposed values.
     */
    function formIsValidChangePassword($form, &$myvalues)
    {
        try
        {
            $bGood = TRUE;
            
            $newpass1 = $myvalues['newpass1'];
            $newpass2 = $myvalues['newpass2'];
            
            if(trim($newpass1) !== $newpass1)
            {
                form_set_error('newpass1','Passwords cannot have leading or trailing whitespace!');
                $bGood = FALSE;
            }
            if(trim($newpass2) !== $newpass2)
            {
                form_set_error('newpass2','Passwords cannot have leading or trailing whitespace!');
                $bGood = FALSE;
            }
            if($newpass1 !== $newpass2)
            {
                form_set_error('newpass2','The passwords do not match!');
                $bGood = FALSE;
            }
            $evaluated = $this->m_oUAH->evaluateCandidatePassword($newpass1);
            if($evaluated['tooweak'])
            {
                if($evaluated['tooshort'])
                {
                    form_set_error('newpass1',"The password must be at least {$evaluated['minlen']} characters in length");
                } else {
                    form_set_error('newpass1','The password is too weak!');
                }
                $bGood = FALSE;
            }
            
            return $bGood;
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
                    if($connection_details['connections_found'])
                    {
                        //TODO --- enhance the message to say what connections
                        form_set_error('shortname','Cannot delete because connections were found.  Consider marking inactive instead.');
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['shortname']) == '')
            {
                form_set_error('shortname','The person name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    //Check for duplicate keys too
                    $result = db_select($this->m_person_tablename,'p')
                        ->fields('p')
                        ->condition('shortname', $myvalues['shortname'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('shortname', 'Already have a person with this name');
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

    public function getBaselineAvailabilityOptionsBundle($show_none_option=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getBaselineAvailabilityByID();
            $options = array();
            if($show_none_option!==FALSE)
            {
                $options[0] = 'None';
            }
            foreach($all as $id=>$record)
            {
                if($record['is_planning_default_yn'])
                {
                    $defaultid = $id;
                }
                //$days_info=$this->getDaysAbbr($record); 
                $days_info= \bigfathom\TextHelper::getAvailabilityDaysAbbr($record); 
                if($record['hours_per_day'] > 0)
                {
                    $hpd_markup = $record['hours_per_day'] . " h/d";
                } else {
                    $hpd_markup = "ZERO HOURS";

                }
                $show_tx = $record['shortname'] . ' | ' . $hpd_markup . ' | ' . $days_info['count'] . ' days(' . $days_info['abbr'] . ')';
                $options[$id] = $show_tx;
            }
            $bundle = [];
            $bundle['options'] = $options;
            $bundle['defaultid'] = $defaultid;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getRoleOptions($skip_this_role_nm='',$show_none_option=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $order_by_ar = array('role_nm');
            $all = $this->m_oMapHelper->getRolesByID($this->m_projectid, $order_by_ar);
            $options = array();
            if($show_none_option!==FALSE)
            {
                if(is_numeric($show_none_option))
                {
                    $options[0] = 'None';
                } else {
                    $options[0] = $show_none_option;
                }
            }
            foreach($all as $id=>$record)
            {
                $role_nm = $record['role_nm'];
                $options[$id] = $role_nm;
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getSystemRoleOptions($skip_this_role_nm='',$show_none_option=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $order_by_ar = array('role_nm');
            $all = $this->m_oMapHelper->getSystemRolesByID($order_by_ar);
            $options = array();
            if($show_none_option!==FALSE)
            {
                if(is_numeric($show_none_option))
                {
                    $options[0] = 'None';
                } else {
                    $options[0] = $show_none_option;
                }
            }
            foreach($all as $id=>$record)
            {
                $role_nm = $record['role_nm'];
                $options[$id] = $role_nm;
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getGroupOptions($skip_this_group_nm='')
    {
        try
        {
            //Get all the relevant select options
            $order_by_ar = array('group_nm');
            $all = $this->m_oMapHelper->getGroupsByIDCustomOrder($order_by_ar);
            $options = array();
            foreach($all as $id=>$record)
            {
                if($id != Context::$SPECIALGROUPID_NOBODY)
                {
                    $group_nm = $record['group_nm'];
                    $options[$id] = $group_nm;
                }
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getUAH()
    {
        return $this->m_oUAH;
    }
    
    public function saveDrupalAccountChanges($person_id, $changes)
    {
        try
        {
            $this->m_oUAH->setDrupalUserFields($person_id, $changes);
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

            $thelimit = BIGFATHOM_MAX_PEOPLE_IN_SYSTEM;
            if($formType=='A' && $this->m_oMapHelper->getCountLocations() >= $thelimit)
            {
                drupal_set_message("Cannot add another person because your system already has the configuration allowed limit of $thelimit",'error');
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
            $canneverlogin = (strpos(strtolower($shortname), 'staff') === 0);
            
            $uah = new \bigfathom\UserAccountHelper($id);
            $upb = $uah->getUserProfileBundle();
            if(isset($myvalues['is_systemadmin_yn']))
            {
                $is_systemadmin_yn = $myvalues['is_systemadmin_yn'];
            } else {
                if(empty($id))
                {
                    $is_systemadmin_yn = 0;
                } else {
                    $is_systemadmin_yn = ($upb['roles']['systemroles']['summary']['is_systemadmin'] 
                            || $upb['core']['master_systemadmin_yn'] == 1) ? 1 : 0;
                }
            }

            if(isset($myvalues['is_systemdatatrustee_yn']))
            {
                $is_systemwriter_yn = $myvalues['is_systemdatatrustee_yn'];
            } else {
                if(empty($id))
                {
                    $is_systemdatatrustee_yn = 0;
                } else {
                    $is_systemdatatrustee_yn = ($upb['roles']['systemroles']['summary']['is_systemdatatrustee'] 
                            || $is_systemadmin_yn == 1
                            || $upb['core']['master_systemadmin_yn'] == 1) ? 1 : 0;
                }
            }

            if(isset($myvalues['is_systemwriter_yn']))
            {
                $is_systemwriter_yn = $myvalues['is_systemwriter_yn'];
            } else {
                if(empty($id))
                {
                    $is_systemwriter_yn = 0;
                } else {
                    $is_systemwriter_yn = ($upb['roles']['systemroles']['summary']['is_systemwriter'] 
                            || $is_systemadmin_yn == 1
                            || $is_systemdatatrustee_yn == 1
                            || $upb['core']['master_systemadmin_yn'] == 1) ? 1 : 0;
                }
            }
            
            if(isset($myvalues['first_nm']))
            {
                $first_nm = $myvalues['first_nm'];
            } else {
                $first_nm = '';
            }
            if(isset($myvalues['last_nm']))
            {
                $last_nm = $myvalues['last_nm'];
            } else {
                $last_nm = '';
            }
            
            if(isset($myvalues['baseline_availabilityid']))
            {
                $baseline_availabilityid = $myvalues['baseline_availabilityid'];
            } else {
                $baseline_availabilityid = NULL;
            }
            
            if(isset($myvalues['primary_locationid']))
            {
                $primary_locationid = $myvalues['primary_locationid'];
            } else {
                $primary_locationid = '';
            }

            if(isset($myvalues['email']))
            {
                $email = $myvalues['email'];
            } else {
                $email = '';
            }

            if(isset($myvalues['secondary_email']))
            {
                $secondary_email = $myvalues['secondary_email'];
            } else {
                $secondary_email = '';
            }

            if(isset($myvalues['primary_phone']))
            {
                $primary_phone = $myvalues['primary_phone'];
            } else {
                $primary_phone = '';
            }
            
            if(isset($myvalues['secondary_phone']))
            {
                $secondary_phone = $myvalues['secondary_phone'];
            } else {
                $secondary_phone = '';
            }
            
            $can_login_yn = !empty($myvalues['can_login_yn']) ? $myvalues['can_login_yn'] : 0;
            
            if(isset($myvalues['map_person2role']))
            {
                $default_preferred_roles = $myvalues['map_person2role'];
            } else {
                $default_preferred_roles = array();
            }
            if(isset($myvalues['map_person2role_in_group']))
            {
                $default_map_person2role_in_group = $myvalues['map_person2role_in_group'];
            } else {
                $default_map_person2role_in_group = array();
            }
            if(isset($myvalues['map_person2systemrole_in_group']))
            {
                $default_map_person2systemrole_in_group = $myvalues['map_person2systemrole_in_group'];
            } else {
                $default_map_person2systemrole_in_group = array();
            }
            
            $bundle_baseline_availability = $this->getBaselineAvailabilityOptionsBundle();
            $options_baseline_availability = $bundle_baseline_availability['options'];
            $options_location = $this->getLocationOptions(); 
            $all_role_options = $this->getRoleOptions(NULL,FALSE);
            $all_systemrole_options = $this->getSystemRoleOptions(NULL,FALSE);
            $all_group_options = $this->getGroupOptions();
            
            if(empty($baseline_availabilityid))
            {
                //Select the system default as the default value here
                $baseline_availabilityid = $bundle_baseline_availability['defaultid'];
            }
            
            if($formType == 'V' || $formType == 'D')
            {
                //Only display what is selected
                $display_role_options = [];
                foreach($all_role_options as $rid=>$content)
                {
                    if(in_array($rid, $default_preferred_roles))
                    {
                        $display_role_options[$rid] = $content;
                    }
                }
                $display_group_options = [];
                foreach($all_group_options as $gid=>$content)
                {
                    if(isset($default_map_person2role_in_group[$gid]))
                    {
                        $display_group_options[$gid] = $content;
                    }
                }
                $display_group_systemrole_options = array();
                foreach($all_group_options as $gid=>$content)
                {
                    if(isset($default_map_person2systemrole_in_group[$gid]))
                    {
                        $display_group_systemrole_options[$gid] = $content;
                    }
                }
            } else {
                //Display them all
                $display_role_options = $this->getRoleOptions(NULL,FALSE);
                $display_group_options = $all_group_options;
                $display_group_systemrole_options = $all_group_options;
            }
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            $form['hiddenthings']['original_email'] 
                = array('#type' => 'hidden', '#value' => $email, '#disabled' => FALSE);        
            $form['hiddenthings']['original_can_login_yn'] 
                = array('#type' => 'hidden', '#value' => $can_login_yn, '#disabled' => FALSE);        

            if($formType == 'D')
            {
                $form['data_entry_area1']['personid'] = array(
                    '#type' => 'textfield',
                    '#title' => t('personid'),
                    '#default_value' => $id,
                    '#size' => 10,
                    '#maxlength' => 10,
                    '#description' => t('The unique ID for this person account'),
                    '#disabled' => TRUE
                );
            }
            
            $showcolname_shortname = 'shortname';
            $disable_namefields = $disabled;       //Default behavior
            $can_edit_shortname = ($this_uid == UserAccountHelper::$MASTER_SYSTEMADMIN_UID);
            if($disabled || !$can_edit_shortname)
            {
                $form['hiddenthings']['shortname'] 
                    = array('#type' => 'hidden'
                        , '#value' => $shortname
                        , '#disabled' => FALSE);        
                $showcolname_shortname = 'show_shortname';
            }
            
            if($this->m_is_systemadmin || $this_uid == $id)
            {
                $form['data_entry_area1'][$showcolname_shortname] = array(
                    '#type' => 'textfield',
                    '#title' => t('Login Name'),
                    '#default_value' => $shortname,
                    '#size' => 40,
                    '#maxlength' => 40,
                    '#required' => TRUE,
                    '#description' => t('The unique login name for this person'),
                    '#disabled' => $disabled || !$can_edit_shortname
                );
            }
            
            $form['data_entry_area1']['first_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('First Name'),
                '#default_value' => $first_nm,
                '#size' => 50,
                '#maxlength' => 50,
                '#required' => TRUE,
                '#description' => t('First name of the person'),
                '#disabled' => $disable_namefields
            );
            
            $form['data_entry_area1']['last_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Last Name'),
                '#default_value' => $last_nm,
                '#size' => 50,
                '#maxlength' => 50,
                '#required' => TRUE,
                '#description' => t('Last name of the person'),
                '#disabled' => $disable_namefields
            );

            $form['data_entry_area1']['primary_phone'] = array(
                '#type' => 'textfield',
                '#title' => t('Primary Phone Number'),
                '#default_value' => $primary_phone,
                '#size' => 16,
                '#maxlength' => 20,
                '#required' => FALSE,
                '#description' => t('Primary phone number'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['secondary_phone'] = array(
                '#type' => 'textfield',
                '#title' => t('Secondary Phone Number'),
                '#default_value' => $secondary_phone,
                '#size' => 16,
                '#maxlength' => 20,
                '#required' => FALSE,
                '#description' => t('Secondary phone number'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['email'] = array(
                '#type' => 'textfield',
                '#title' => t('Primary Email Address'),
                '#default_value' => $email,
                '#size' => 80,
                '#maxlength' => 250,
                '#required' => FALSE,
                '#description' => t('Primary email address of the person'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['secondary_email'] = array(
                '#type' => 'textfield',
                '#title' => t('Secondary Email Address'),
                '#default_value' => $secondary_email,
                '#size' => 80,
                '#maxlength' => 250,
                '#required' => FALSE,
                '#description' => t('Secondary email address of the person'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['map_person2role'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Preferred Project Roles'
                    , $default_preferred_roles
                    , $display_role_options
                    , TRUE
                    , 'The roles that this person generally plays in projects'
                );
            
            if($this->m_show_group_membership_roles)
            {
                $form['data_entry_area1']['map_group_membership_roles'] = array(
                    '#type' => 'fieldset', 
                    '#title' => 'Group Memberships',
                    '#collapsible' => FALSE,
                    '#collapsed' => FALSE, 
                    '#tree' => TRUE,
                    '#disabled' => $disabled
                );            
                foreach($display_group_options as $groupid=>$onegroup)
                {
                    if(isset($default_map_person2role_in_group[$groupid]))
                    {
                        $default_pr_value = $default_map_person2role_in_group[$groupid];
                    } else {
                        $default_pr_value = NULL;
                    }
                    $prtitle = 'Project Role(s) in the "' . $display_group_options[$groupid] . '" group';
                    $form['data_entry_area1']['map_group_membership_roles'][$groupid]['projectrole'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                            , $prtitle
                            , $default_pr_value
                            , $all_role_options
                            , FALSE
                            , NULL
                        );
                    if(isset($default_map_person2systemrole_in_group[$groupid]))
                    {
                        $default_sr_value = $default_map_person2systemrole_in_group[$groupid];
                    } else {
                        $default_sr_value = NULL;
                    }
                    $srtitle = 'System Role(s) in the "' . $display_group_options[$groupid] . '" group';
                    $form['data_entry_area1']['map_group_membership_roles'][$groupid]['systemrole'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                            , $srtitle
                            , $default_sr_value
                            , $all_systemrole_options
                            , FALSE
                            , NULL
                        );
                }
            }

            $form['data_entry_area1']['baseline_availabilityid'] = array(
                '#type' => 'select',
                '#title' => t('Baseline Availability'),
                '#default_value' => $baseline_availabilityid,
                '#options' => $options_baseline_availability,
                '#required' => FALSE,
                '#description' => t('The default work availability of this person when no other overrides apply'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['primary_locationid'] = array(
                '#type' => 'select',
                '#title' => t('Location'),
                '#default_value' => $primary_locationid,
                '#options' => $options_location,
                '#required' => FALSE,
                '#description' => t('Primary location of this person'),
                '#disabled' => $disabled
            );

            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $active_description_tx = 'Yes if person is available for new work, else not available.';
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Available for New Work'),
                '#default_value' => !empty($myvalues['active_yn']) ? $myvalues['active_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t($active_description_tx),
                '#disabled' => $disabled,
            );

            $canlogin_disabled = $disabled || ($id ==  $this_uid) || $canneverlogin;
            if($can_login_yn == 0 && !$canneverlogin)
            {
                //See if we have a missing drupal account issue
                $this->m_oUAH = new \bigfathom\UserAccountHelper();
                if(NULL === $this->m_oUAH->getDrupalUidFromShortname($shortname, FALSE))
                {
                    $canlogin_disabled = TRUE;
                    drupal_set_message("There is no AUTHENTICATION SUBSYSTEM USER ACCOUNT for this user.  Contact your system administrator to correct this.", "error");
                }
            }
            $canlogin_description_tx = 'Yes if person has an active account to use the application, else no.';
            $form['data_entry_area1']['can_login_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Can Login'),
                '#default_value' => $can_login_yn,
                '#options' => $ynoptions,
                '#description' => t($canlogin_description_tx),
                '#disabled' => $disabled || $canlogin_disabled || $canneverlogin,
            );

            $is_systemwriter_yn_description_tx = 'If no, then a user cannot save any changes in the application.  Users that cannot write changes, can still read data.';
            $can_edit_systemitemownerrole = $this->m_is_systemadmin;
            $form['data_entry_area1']['is_systemwriter_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Is Data Writer'),
                '#default_value' => $is_systemwriter_yn,
                '#options' => $ynoptions,
                '#description' => t($is_systemwriter_yn_description_tx),
                '#disabled' => $disabled || !$can_edit_systemitemownerrole || $canneverlogin,
            );

            $is_systemdatatrustee_yn_description_tx = 'Yes if this account has site-wide data edit capabilities, else no.';
            $can_edit_systemdatatrusteerole = $this->m_is_systemadmin;
            $form['data_entry_area1']['is_systemdatatrustee_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Is Site Data Trustee'),
                '#default_value' => $is_systemdatatrustee_yn,
                '#options' => $ynoptions,
                '#description' => t($is_systemdatatrustee_yn_description_tx),
                '#disabled' => $disabled || !$can_edit_systemdatatrusteerole || $canneverlogin,
            );
            
            $is_systemadmin_yn_description_tx = 'Yes if this account has system administration capabilities, else no.';
            $can_edit_sysadminrole = $id != $this_uid && $this->m_is_systemadmin;
            $form['data_entry_area1']['is_systemadmin_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Is System Administrator'),
                '#default_value' => $is_systemadmin_yn,
                '#options' => $ynoptions,
                '#description' => t($is_systemadmin_yn_description_tx),
                '#disabled' => $disabled || !$can_edit_sysadminrole || $canneverlogin,
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * Get all the form contents for rendering
     * @return drupal renderable array
     * @throws \Exception
     */
    function getChangePasswordForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
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
                $personid = $myvalues['id'];
            } else {
                $personid = '';
            }
            if($personid !== $this_uid)
            {
                throw new \Exception("The person ID is not properly set!");
            }
            if(isset($myvalues['shortname']))
            {
                $shortname = $myvalues['shortname'];
            } else {
                $shortname = '';
            }
            if(isset($myvalues['can_login_yn']))
            {
                $can_login_yn = $myvalues['can_login_yn'];
            } else {
                $can_login_yn = '';
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $personid, '#disabled' => FALSE); 
            $form['hiddenthings']['personid'] 
                = array('#type' => 'hidden', '#value' => $personid, '#disabled' => FALSE); 
            $form['hiddenthings']['shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            $form['hiddenthings']['original_can_login_yn'] 
                = array('#type' => 'hidden', '#value' => $can_login_yn, '#disabled' => FALSE);        
            
            $form['data_entry_area1']['newpass1'] = array(
              '#type' => 'password',
              '#title' => t('New Password'),
              '#size' => 60,
              '#required' => TRUE,
            );            
            
            $form['data_entry_area1']['newpass2'] = array(
              '#type' => 'password',
              '#title' => t('Confirm Password'),
              '#size' => 60,
              '#required' => TRUE,
            );            
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
