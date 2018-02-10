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
 * Help with Groups
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class GroupPageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_group_tablename = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_group_tablename = DatabaseNamesHelper::$m_group_tablename;
        
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

    public function getInUseDetails($groupid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfGroup($groupid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($groupid=NULL)
    {
        try
        {
            if($groupid != NULL)
            {
                //Get the core values 
                $myvalues = db_select($this->m_group_tablename, 'n')
                  ->fields('n')
                  ->condition('id', $groupid, '=')
                  ->execute()
                  ->fetchAssoc();
                if(empty($myvalues))
                {
                    throw new \Exception("There is no data for groupid=$groupid");
                }
                $myvalues['member_list'] = $this->m_oMapHelper->getGroupMembers($groupid);
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['group_nm'] = NULL;
                $myvalues['leader_personid'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                $myvalues['member_list'] = array();
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
                    form_set_error('group_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $groupid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($groupid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $users_clean = array();
                        foreach($connection_details['urg_person_list'] as $id=>$onerec)
                        {
                            $users_clean[$id] = $id;
                        }
                        foreach($connection_details['usrg_person_list'] as $id=>$onerec)
                        {
                            $users_clean[$id] = $id;
                        }
                        
                        $urg_role_list = $connection_details['urg_role_list'];
                        $usrg_role_list = $connection_details['usrg_role_list'];
                        
                        $total_persons = count($users_clean);
                        $total_roles = count($urg_role_list) + count($usrg_role_list);
                        $help_detail = "($total_persons users, $total_roles roles)";
                        form_set_error('group_nm',"Cannot delete because critical connections were found. $help_detail");
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['group_nm']) == '')
            {
                form_set_error('group_nm','The group name cannot be empty');
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
                    $result = db_select($this->m_group_tablename,'p')
                        ->fields('p')
                        ->condition('group_nm', $myvalues['group_nm'],'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('group_nm', 'Already have a group with this name');
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
    
    public function getGroupLeaderOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getGroupLeaders();
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

            $thelimit = BIGFATHOM_MAX_GROUPS_IN_SYSTEM;
            if($formType=='A' && $this->m_oMapHelper->getCountGroups() >= $thelimit)
            {
                drupal_set_message("Cannot add another group because your system already has the configuration allowed limit of $thelimit",'error');
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
                $is_special = ($id == Context::$SPECIALGROUPID_DEFAULT_PRIVCOLABS);
            } else {
                $id = '';
                $is_special = FALSE;
            }
            if(isset($myvalues['group_nm']))
            {
                $group_nm = $myvalues['group_nm'];
            } else {
                $group_nm = '';
            }
            if(isset($myvalues['leader_personid']))
            {
                $leader_personid = $myvalues['leader_personid'];
            } else {
                $leader_personid = $this_uid;
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            if(isset($myvalues['member_list']))
            {
                $member_list = $myvalues['member_list'];
            } else {
                $member_list = array();
            }

            if($formType == 'D')
            {
                $connection_details = $this->getInUseDetails($id);
                $critical_connections_found = $connection_details['critical_connections_found'];
                if($critical_connections_found > 0)
                {
                    drupal_set_message("This group cannot be deleted because it is in use by $critical_connections_found entities of the application.  Consider marking it retired instead.","warning");
                }
            }
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_group_nm'] 
                = array('#type' => 'hidden', '#value' => $group_nm, '#disabled' => FALSE);        
            
            $showcolname_group_nm = 'group_nm';
            $disable_group_nm = $disabled || $id==1 || $id==10 || $is_special;
            
            $options_groupleaders = $this->getGroupLeaderOptions();
            
            if($disable_group_nm)
            {
                $form['hiddenthings']['group_nm'] 
                    = array('#type' => 'hidden', '#value' => $group_nm, '#disabled' => FALSE);        
                $showcolname_group_nm = 'show_group_nm';
            }
            
            $form['data_entry_area1'][$showcolname_group_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Group Name'),
                '#default_value' => $group_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this group'),
                '#disabled' => $disable_group_nm
            );
            
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose Text'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('Explanation of the group purpose'),
                '#disabled' => $disabled || $is_special
            );

            $form['data_entry_area1']['leader_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Group Leader'),
                '#default_value' => $leader_personid,
                '#options' => $options_groupleaders,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful progress of this group?'),
                '#disabled' => $disabled
            );
            
            if($id==1)  //Special EVERYONE group
            {
                $form["data_entry_area1"]['membership'] = array('#type' => 'fieldset',
                        '#title' => t('Other Members'),
                    );
                $form["data_entry_area1"]['membership']['members'] = array('#type' => 'item',
                    '#markup' => "<p>Every person in the application is automatically a member of this group</p>");
            } else {
                if($formType != 'A')
                {
                    $form["data_entry_area1"]['membership'] = array('#type' => 'fieldset',
                            '#title' => t('Other Members'),
                        );
                    if(count($member_list) < 1)
                    {
                        $membership_markup = "No other group members are currently defined";
                    } else {
                        $rows = '';
                        foreach($member_list as $onemember)
                        {
                            $person_markup = "<span title='#{$onemember['personid']}'>{$onemember['first_nm']} {$onemember['last_nm']}</span>";
                            $role_markup = "<span title='#{$onemember['roleid']}'>{$onemember['role_nm']}</span>";
                            $rows   .= "\n"
                                    . '<tr><td>'
                                    . $person_markup
                                    . '</td><td>'
                                    . $role_markup.'</td>'
                                    . '</tr>';
                        }
                        $membership_markup = '<table id="my-dialog-table" class="dataTable">'
                                            . '<thead>'
                                            . '<tr>'
                                            . '<th>'.t('Person').'</th>'
                                            . '<th>'.t('Role').'</th>'
                                            . '</tr>'
                                            . '</thead>'
                                            . '<tbody>'
                                            . $rows
                                            .  '</tbody>'
                                            . '</table>';
                    }
                    $form["data_entry_area1"]['membership']['members'] = array('#type' => 'item',
                        '#markup' => $membership_markup);
                }
            }
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if group is active, else no.'),
                '#disabled' => $disabled || $is_special
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
