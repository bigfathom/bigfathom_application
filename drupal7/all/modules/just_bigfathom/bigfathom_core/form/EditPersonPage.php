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

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/PersonPageHelper.php';

/**
 * Edit Person
 *
 * @author Frank Font
 */
class EditPersonPage extends \bigfathom\ASimpleFormPage
{
    protected $m_personid    = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    protected $m_is_systemdatatrustee_yn = NULL;
    
    function __construct($personid, $showgroupmembershiproles=TRUE, $urls_override_arr=NULL)
    {
        $this->showgroupmembershiproles = $showgroupmembershiproles;
        if (!isset($personid) || !is_numeric($personid)) {
            throw new \Exception("Missing or invalid $personid value = " . $personid);
        }
        
        $this->m_personid = $personid;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        //DebugHelper::debugPrintNeatly($upb);
        $this->m_is_systemdatatrustee_yn = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];        
        $this->m_is_systemadmin_yn = $upb['roles']['systemroles']['summary']['is_systemadmin'];        
        if($upb['core']['id'] != $personid && !$this->m_is_systemdatatrustee_yn)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit personid=$personid!!!");
            throw new \Exception("Illegal access attempt!");
        }
        
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = array();
        $pmi = $this->m_oContext->getParentMenuItem();
        if(!empty($pmi['link_path']))
        {
            $urls_arr['return'] = $pmi['link_path'];
        } else {
            $urls_arr['return'] = 'bigfathom/sitemanage/people';
        }
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\PersonPageHelper($urls_arr);
        $this->m_oPageHelper->setShowGroupMembershipRoles($showgroupmembershiproles);
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_personid);
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'E');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            global $user;
            $this_uid = $user->uid;
            $personid = $myvalues['id'];
            $fields = array(
                  'shortname' => $myvalues['shortname'],
                  'first_nm' => $myvalues['first_nm'],
                  'last_nm' => $myvalues['last_nm'],
                  'primary_phone' => $myvalues['primary_phone'],
                  'secondary_phone' => $myvalues['secondary_phone'],
                  'secondary_email' => $myvalues['secondary_email'],
                  'updated_dt' => $updated_dt,
                );
            if(array_key_exists('active_yn', $myvalues))
            {
                $fields['active_yn'] = $myvalues['active_yn'];
            }
            if(array_key_exists('primary_locationid', $myvalues))
            {
                $fields['primary_locationid'] = $myvalues['primary_locationid'];
            }
            if(array_key_exists('baseline_availabilityid', $myvalues))
            {
                $fields['baseline_availabilityid'] = $myvalues['baseline_availabilityid'];
            }

            db_update(DatabaseNamesHelper::$m_person_tablename)->fields($fields)
                    ->condition('id', $personid,'=')
                    ->execute();
            
            //Check for changes to drupal user record too
            $changes = array();
            if($myvalues['original_can_login_yn'] !== $myvalues['can_login_yn'])
            {
                $changes['status'] = $myvalues['can_login_yn'];
            }
            if($myvalues['original_email'] !== $myvalues['email'])
            {
                $changes['mail'] = $myvalues['email'];
            }
            if(count($changes) > 0)
            {
                $this->m_oPageHelper->saveDrupalAccountChanges($personid, $changes);
            }
            
            //Delete any existing role mappings first
            db_delete(DatabaseNamesHelper::$m_map_person2role_tablename)  
              ->condition('personid', $personid)
              ->execute(); 
            //Add any new role mappings next
            if(is_array($myvalues['map_person2role']))
            {
                foreach($myvalues['map_person2role'] as $roleid)
                {
                    db_insert(DatabaseNamesHelper::$m_map_person2role_tablename)
                      ->fields(array(
                            'personid' => $personid,
                            'roleid' => $roleid,
                            'importance' => UserAccountHelper::$DEFAULT_ROLE_IMPORTANCE,
                            'created_by_personid' => $this_uid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
            }

            if($this->showgroupmembershiproles)
            {
                //Delete any existing group mappings first
                db_delete(DatabaseNamesHelper::$m_map_person2role_in_group_tablename)
                  ->condition('personid', $personid)
                  ->execute(); 
                //Add any new group mappings next
                if(is_array($myvalues['map_group_membership_roles']))
                {
                    foreach($myvalues['map_group_membership_roles'] as $groupid=>$roleid_list)
                    {
                        foreach($roleid_list['projectrole'] as $roleid)
                        {
                            if($roleid > 0)
                            {
                                db_insert(DatabaseNamesHelper::$m_map_person2role_in_group_tablename)
                                  ->fields(array(
                                        'personid' => $personid,
                                        'roleid' => $roleid,
                                        'groupid' => $groupid,
                                        'importance' => UserAccountHelper::$DEFAULT_ROLE_IMPORTANCE,
                                        'created_by_personid' => $this_uid,
                                        'updated_dt' => $updated_dt,
                                        'created_dt' => $updated_dt,
                                    ))
                                      ->execute(); 
                            }
                        }
                    }
                }
                
                //Delete any existing group mappings first
                db_delete(DatabaseNamesHelper::$m_map_person2systemrole_in_group_tablename)
                  ->condition('personid', $personid)
                  ->execute(); 
                //Add any new group mappings next
                if(isset($myvalues['map_group_membership_roles']) 
                        && is_array($myvalues['map_group_membership_roles']))
                {
                    foreach($myvalues['map_group_membership_roles'] as $groupid=>$roleid_list)
                    {
                        foreach($roleid_list['systemrole'] as $roleid)
                        {
                            if($roleid > 0)
                            {
                                db_insert(DatabaseNamesHelper::$m_map_person2systemrole_in_group_tablename)
                                  ->fields(array(
                                        'personid' => $personid,
                                        'systemroleid' => $roleid,
                                        'groupid' => $groupid,
                                        'created_dt' => $updated_dt,
                                        'created_by_personid' => $this_uid,
                                    ))
                                      ->execute(); 
                            }
                        }
                    }
                }
            }
            
            if($this->m_oPageHelper->canChangeSAFlags())
            {
                //Set the system role bits
                $uah = new \bigfathom\UserAccountHelper();

                if(array_key_exists('is_systemadmin_yn', $myvalues))
                {
                    $is_systemadmin_yn = $myvalues['is_systemadmin_yn'];
                    $uah->setUserSystemAdministratorAttribute($personid, $is_systemadmin_yn == 1);            
                }
                if(array_key_exists('is_systemdatatrustee_yn', $myvalues))
                {
                    $is_systemdatatrustee_yn = $myvalues['is_systemdatatrustee_yn'];
                    $uah->setUserSystemDataTrusteeAttribute($personid, $is_systemdatatrustee_yn == 1);            
                }
                if(array_key_exists('is_systemwriter_yn', $myvalues))
                {
                    $is_systemwriter_yn = $myvalues['is_systemwriter_yn'];
                    $uah->setUserSystemItemOwnerAttribute($personid, $is_systemwriter_yn == 1);            
                }
            }
            
            //If we are here then we had success.
            $msg = 'Saved update for ' . $myvalues['shortname'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['shortname']
                      . ' person because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {
        if($html_classname_overrides == NULL)
        {
            //Set the default values.
            $html_classname_overrides = array();
            $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            $html_classname_overrides['container-inline'] = 'container-inline';
            $html_classname_overrides['action-button'] = 'action-button';
        }
        
        $form = $this->m_oPageHelper->getForm('E',$form
                , $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        $form['data_entry_area1']['action_buttons']['create'] = array(
            '#type' => 'submit',
            '#attributes' => array(
                'class' => array($html_classname_overrides['action-button'])
            ),
            '#value' => t('Save Person Updates'),
            '#disabled' => FALSE
        );

        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $form;
    }
}
