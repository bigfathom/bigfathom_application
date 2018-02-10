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
 * Add a Person
 *
 * @author Frank Font
 */
class AddPersonPage extends \bigfathom\ASimpleFormPage
{
    protected $m_urls_arr = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_person_tablename = 'bigfathom_person';
    protected $m_map_person2role_tablename = 'bigfathom_map_person2role';
    protected $m_map_person2role_in_group = 'bigfathom_map_person2role_in_group';
   
    public function __construct($urls_override_arr=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = array();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = 'bigfathom/sitemanage/people';
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\PersonPageHelper($urls_arr);
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        if(!$upb['roles']['systemroles']['summary']['is_systemadmin'])
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to add a person!!!");
            throw new \Exception("Illegal access attempt!");
        }
        
    }

    /**
     * Get the values to populate the form.
     * @return type result of the queries as an array
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues();
    }
    
    function looksValid($form, $myvalues)
    {
        return $this->m_oPageHelper->formIsValid($form, $myvalues, 'A');
    }
    
    /**
     * Write the values into the database.
     */
    function updateDatabase($form, $myvalues)
    {
        try
        {
            $oUAH = $this->m_oPageHelper->getUAH();
            $shortname = strtoupper($myvalues['shortname']);
            $firstname = $myvalues['first_nm'];
            $lastname = $myvalues['last_nm'];
            $locationid = $myvalues['primary_locationid'];
            $baseline_availabilityid = $myvalues['baseline_availabilityid'];
            $role_maps = array();
            $proles = array();
            if(is_array($myvalues['map_person2role']))
            {
                foreach($myvalues['map_person2role'] as $roleid)
                {
                    $proles[$roleid] = UserAccountHelper::$DEFAULT_ROLE_IMPORTANCE;
                }
            }
            $role_maps['proles'] = $proles;
            $role_maps['sroles'] = array(5=>77);
            $subrole_maps = array();
            if(is_array($myvalues['map_group_membership_roles']))
            {
                $roles_in_groups = array();
                foreach($myvalues['map_group_membership_roles'] as $groupid=>$roleid_list)
                {
                    foreach($roleid_list['projectrole'] as $roleid)
                    {
                        if($roleid > 0)
                        {
                            $roles_in_groups[] = array(
                                'roleid'=>$roleid, 
                                'groupid'=>$groupid, 
                                'importance'=>  UserAccountHelper::$DEFAULT_ROLE_IMPORTANCE, 
                                'updated_dt'=>'NOW'
                            );
                        }
                    }
                }
                $subrole_maps['role_in_group'] = $roles_in_groups;
            }
            if(isset($myvalues['map_group_membership_roles']) 
                    && is_array($myvalues['map_group_membership_roles']))
            {
                $systemroles_in_groups = array();
                foreach($myvalues['map_group_membership_roles'] as $groupid=>$roleid_list)
                {
                    foreach($roleid_list['systemrole'] as $systemroleid)
                    {
                        if($systemroleid > 0)
                        {
                            $systemroles_in_groups[] = array(
                                'systemroleid'=>$systemroleid, 
                                'groupid'=>$groupid, 
                            );
                        }
                    }
                }
                $subrole_maps['systemrole_in_group'] = $systemroles_in_groups;
            }
            
            $role_maps['subrole_maps'] = $subrole_maps;
            $email = $myvalues['email'];
            $password = user_password();// "{$shortname}2016";
            $active_for_work_yn = empty($myvalues['active_yn']) ? 0 : $myvalues['active_yn'];
            $can_login_yn = empty($myvalues['can_login_yn']) ? 0 : $myvalues['can_login_yn'];
            $personid = $oUAH->createUserAccount($shortname, $firstname, $lastname
                    , $baseline_availabilityid, $locationid
                    , $role_maps, $email, $password
                    , $active_for_work_yn, $can_login_yn);
            
            if($this->m_oPageHelper->canChangeSAFlags())
            {
                //Set the system role bits
                $uah = new \bigfathom\UserAccountHelper();
                
                if(array_key_exists('is_systemadmin_yn', $myvalues))
                {
                    $is_systemadmin_yn = $myvalues['is_systemadmin_yn'];
                    $oUAH->setUserSystemAdministratorAttribute($personid, $is_systemadmin_yn == 1);            
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
            $msg = 'Added ' . $myvalues['shortname'];
            drupal_set_message($msg);
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to add ' . $myvalues['shortname']
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
    function getForm($form, &$form_state, $disabled, $myvalues_override)
    {
        if(!isset($form_state['values']))
        {
            $myvalues = array();
        } else {
            $myvalues = $form_state['values'];
        }
        $html_classname_overrides = array();
        $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
        $html_classname_overrides['container-inline'] = 'container-inline';
        $html_classname_overrides['action-button'] = 'action-button';
        $new_form = $this->m_oPageHelper->getForm('A',$form, $form_state, $disabled, $myvalues, $html_classname_overrides);

        //Add the action buttons.
        $new_form['data_entry_area1']['action_buttons'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );
        $new_form['data_entry_area1']['action_buttons']['create'] = array('#type' => 'submit'
                , '#attributes' => array('class' => array($html_classname_overrides['action-button']))
                , '#value' => t('Add Person'));
 
        if(isset($this->m_urls_arr['return']))
        {
            $returnURL = $this->m_urls_arr['return'];
            $sReturnMarkup = l('Cancel',$returnURL
                    ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            $new_form['data_entry_area1']['action_buttons']['manage'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $new_form;
    }
}
