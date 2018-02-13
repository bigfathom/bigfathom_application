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
require_once 'helper/SendNotificationTestcasePageHelper.php';

/**
 * Send notifications about test case
 *
 * @author Frank Font
 */
class SendNotificationTestcasePage extends \bigfathom\ASimpleFormPage
{
    protected $m_testcaseid  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_oPageHelper = NULL;
    
    protected $m_in_dialog = NULL;
    
    function __construct($testcaseid,$urls_override_ar=NULL,$in_dialog=FALSE)
    {
        $this->m_in_dialog = $in_dialog;
        if (!isset($testcaseid) || !is_numeric($testcaseid)) {
            throw new \Exception("Missing or invalid testcaseid value = " . $testcaseid);
        }
        $this->m_testcaseid = $testcaseid;
        
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_projectid = $this->m_oMapHelper->getProjectIDForTestCaseID($testcaseid); 
        
        $urls_arr = [];
        $urls_arr['return'] = 'bigfathom/projects/testcases';
        
        if(is_array($urls_override_ar))
        {
            foreach($urls_override_ar as $k=>$theval)
            {
                //if($k != 'return' || empty($urls_arr['return']))
                {
                    $urls_arr[$k] = $theval;
                }
            }
        }
        
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\SendNotificationTestcasePageHelper($urls_arr,NULL,$this->m_projectid);
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        
        
        //if(!$this->m_is_systemwriter)
        global $user;    
        $aDataRights = $this->m_oMapHelper->getTestCaseActionPrivsOfPerson($testcaseid, $user->uid);       
        if(strpos($aDataRights,'T') === FALSE && strpos($aDataRights,'E') === FALSE)
        {
            global $user;
            error_log("HACKING WARNING: uid#{$user->uid} attempted to edit testcase#$testcaseid!!!");
            throw new \Exception("Illegal access attempt!");
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return $this->m_oPageHelper->getFieldValues($this->m_testcaseid);
    }
    
    /**
     * Validate the proposed values.
     * @return TRUE if no validation errors detected
     */
    function looksValid($form, $myvalues)
    {
        $looks_good = $this->m_oPageHelper->formIsValid($form, $myvalues, 'E');
        return $looks_good;
    }
    
    /**
     * Write the values into the database and send the message.
     */
    function updateDatabase($form, $myvalues)
    {
        global $user;
        $this_uid = $user->uid;
        
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $loaded = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            $oUAH = new \bigfathom\UserAccountHelper();
            $this_person_detail = $oUAH->getUserProfileCore($this_uid);
            
            //DebugHelper::showNeatMarkup($this_person_detail);
            //throw new \Exception("STOP AND LOOK");

            $this_person_email = $this_person_detail['main_email'];
            $lower_this_person_email = strtolower($this_person_email);
            
            $testcaseid = $this->m_testcaseid;
            
            $loaded = module_load_include('php','bigfathom_notify','core/EmailHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the EmailHelper class');
            }
            $oEmailHelper = new \bigfathom_notify\EmailHelper();
            
            //Pull the email NOW from the databse to avoid TAMPERING from client post side!
            $fresh_value_pull = $this->getFieldValues($testcaseid);

            $post_fields = [];
            
            $me_bundle = $this->m_oPageHelper->getMessageElementsBundle($fresh_value_pull);
                
            if(empty($me_bundle['failed_steps']['text']))
            {
                $full_message_text = trim($myvalues['message_intro']);
                $full_message_html = trim($myvalues['message_intro']);
            } else {
                $full_message_text = trim($myvalues['message_intro'] . "\r\n" . $me_bundle['failed_steps']['text']);
                $full_message_html = trim($myvalues['message_intro'] . "<hr>" . $me_bundle['failed_steps']['html']);
            }

            $personinfo = $fresh_value_pull['lookup_personinfo'];
            $shortname2detail = [];
            foreach($personinfo as $id=>$detail)
            {
                $shortname = $detail['shortname'];
                $shortname2detail[$shortname] = $detail;
            }
            $recipients_by_type = $this->m_oPageHelper->getRecipientsByType($myvalues['recipients']);
            $mailto_count=0;
            $cc_count=0;
            $found_this_person_as_recipient = FALSE;
            foreach($recipients_by_type as $type=>$shortname_ar)
            {
                if($type == 'TO')
                {
                    $mailto_count=count($shortname_ar);
                    $postkey = "mailto";
                } else
                if($type == 'CC')
                {
                    $cc_count=count($shortname_ar);
                    $postkey = "cc";
                }
                if(!empty($postkey))
                {
                    $ar = [];
                    foreach($shortname_ar as $shortname)
                    {
                        $email = $shortname2detail[$shortname]['email'];
                        if($lower_this_person_email == strtolower($email))
                        {
                            $found_this_person_as_recipient = TRUE;
                        }
                        $ar[] = $email;
                    }
                    $post_fields[$postkey] = $ar;
                }
            }
            if(!$found_this_person_as_recipient)
            {
                //This can happen when a SUPERUSER elects to send the notification
                if(!isset($post_fields['cc']))
                {
                    $post_fields['cc'] = [];
                }
                $post_fields['cc'][] = $this_person_email;
            }
            
            $subject = "Bigfathom Testcaseid#$testcaseid Notification";
            $post_fields['subject'] = $subject;
            $post_fields['message'] = $full_message_text;
            $post_fields['message_html'] = $full_message_html;
            
            //DebugHelper::showNeatMarkup($myvalues,"LOOK POST THESE");
            
            $oEmailHelper->sendNotification($post_fields);
            
            drupal_set_message("Message sent to " . ($mailto_count + $cc_count) . " recipients");
        }
        catch(\Exception $ex)
        {
            $msg = t('Failed to update ' . $myvalues['testcase_nm']
                      . ' use case because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($base_form
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
        
        $form = $this->m_oPageHelper->getForm('E',$base_form, $form_state, $disabled, $myvalues, $html_classname_overrides);
        
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
            '#value' => t('Send Test Case Status Notification as Email'),
            '#disabled' => FALSE
        );

        if(isset($this->m_urls_arr['return']))
        {
            
            if($this->m_in_dialog)
            {
                //We are in dialog mode
                $sReturnMarkup = "<span class='action-button' onclick='parent.bigfathom_util.testcase_ui_toolkit.closeDialog(0);'>Cancel</span>";
            } else {
                //Full page mode
                $returnURL = $this->m_urls_arr['return'];
                $sReturnMarkup = l('Cancel',$returnURL
                        ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
            }
            
            $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $sReturnMarkup);
        }
        
        return $form;
    }
    
    /**
     * Just simple static message form markup
     */
    public static function getSentForm($form, &$form_state)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
           
            $form['data_entry_area1']['markup'] = array(
                '#type' => 'item',
                '#markup' => "<p>Message Sent!</p>"
                . "<script>parent.bigfathom_util.testcase_ui_toolkit.closeDialog(1);</script>"
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
