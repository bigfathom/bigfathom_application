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
 * Help with test case notification information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class SendNotificationTestcasePageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $projectid=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        if(!empty($projectid))
        {
            $this->m_projectid = $projectid;
        } else {
            $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        }
        
        //module_load_include('php','bigfathom_core','core/Context');
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        
    }

    function getMessageElementsBundle($myvalues)
    {
        $bundle = [];
        try
        {
            $failed_steps_ar = [];
            $testcase_nm = $myvalues['testcase_nm'];

            $stepinfo = $myvalues['maps']['steps'];
            $stepstats = [];
            foreach($stepinfo as $stepdetail)
            {
                $status_cd = $stepdetail['status_cd'];
                $step_num = $stepdetail['step_num'];
                if(!isset($stepstats[$status_cd]))
                {
                    $stepstats[$status_cd] = [];
                }
                $stepstats[$status_cd][$step_num]=$step_num;
                if($status_cd == 'FAIL')
                {
                    $failed_steps_ar[] = $stepdetail;
                }
            }

            if(isset($stepstats['FAIL']))
            {
                $failed = $stepstats['FAIL'];
                if(count($failed)>1)
                {
                    $summary_status_msg = "Test case '$testcase_nm' step numbers " . implode(" and ", $failed) . " have failed!";
                } else {
                    $fsnum_ar = array_keys($failed);
                    $fsnum = $fsnum_ar[0];
                    $summary_status_msg = "Test case '$testcase_nm' step number " . $fsnum . " has failed!";
                }
            } else {
                if(isset($stepstats['NONE']))
                {
                    if(!isset($stepstats['PASS']))
                    {
                        $summary_status_msg = "No '$testcase_nm' test case steps have yet been evaluated.";
                    } else {
                        $summary_status_msg = "Currently '$testcase_nm' test case";
                        $nottested = $stepstats['NONE'];
                        if(count($nottested)>1)
                        {
                            $summary_status_msg .= " step numbers " . implode(" and ", $nottested) . " are untested";
                        } else {
                            $fsnum_ar = array_keys($nottested);
                            $fsnum = $fsnum_ar[0];
                            $summary_status_msg .= " step number " . $fsnum . " is untested";
                        }
                        $summary_status_msg .= " and ";
                        $passed = $stepstats['PASS'];
                        if(count($passed)>1)
                        {
                            $summary_status_msg .= "step numbers " . implode(" and ", $passed) . " have passed.";
                        } else {
                            $fsnum_ar = array_keys($passed);
                            $fsnum = $fsnum_ar[0];
                            $summary_status_msg .= "step number " . $fsnum . " has passed.";
                        }
                    }
                } else {
                    if(isset($stepstats['PASS']))
                    {
                        $summary_status_msg = "All '$testcase_nm' test case steps have PASSED evaluation successfully!";
                    }
                }
            }
            
            $bundle['message_intro'] = $summary_status_msg;
            if(count($failed_steps_ar)==0)
            {
                $bundle['failed_steps']['text'] = NULL;
                $bundle['failed_steps']['html'] = NULL;
            } else {
                /*
                 *     id=[2]
                        testcaseid=[1]
                        step_num=[3]
                        instruction_tx=[bbbb]
                        expectation_tx=[]
                        status_cd=[FAIL]
                        executed_dt=[2017-12-07 01:36:00]
                        updated_dt=[2017-11-20 22:09:00]
                        created_dt=[2017-11-10 14:38:00]
                 * 
                 */
                $text_ar = [];
                foreach($failed_steps_ar as $failed_step)
                {
                    $text_ar[] = "\r\nSTEP#{$failed_step['step_num']}...\r\n"
                    . "INSTRUCTION: {$failed_step['instruction_tx']}\r\n"
                    . "EXPECTATION: {$failed_step['expectation_tx']}\r\n\r\n";
                }
                $text_markup = implode("\n", $text_ar);
                $bundle['failed_steps']['text'] = $text_markup;//\bigfathom\DebugHelper::getNeatTextMarkup($failed_steps_ar, "FAILED STEP DETAIL");
                
                $html_style="<style>td {border-right: 1px solid orange;} tr:nth-child(even) {background-color: #f2f2f2;};\n</style>\n";
                $html_table_ar = [];
                $html_table_ar[] = "\n<table style='border: 1px solid orange;'>";
                $html_table_ar[] = "<tr><th>Failed Step</th><th>Instruction</th><th>Expectation</th></tr>";
                foreach($failed_steps_ar as $failed_step)
                {
                    $html_table_ar[] = "<tr><td>{$failed_step['step_num']}</td><td>{$failed_step['instruction_tx']}</td><td>{$failed_step['expectation_tx']}</td></tr>";
                }
                $html_table_ar[] = "</table>";
                $html_table_markup = implode("\n", $html_table_ar);
                
                $bundle['failed_steps']['html'] = $html_style . $html_table_markup;//\bigfathom\DebugHelper::getNeatMarkup($failed_steps_ar, "FAILED STEP DETAIL");
            }
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($testcaseid=NULL)
    {
        try
        {
            $myvalues = [];
            
            //Initialize all the values to EMPTY
            $myvalues['testcase_dt'] = NULL;
            $myvalues['testcase_nm'] = NULL;

            $myvalues['map_workitem2testcase'] = [];
            $myvalues['map_tag2testcase'] = [];
            $myvalues['map_delegate_owner'] = [];
            $myvalues['map_step2testcase'] = [];
            $myvalues['map_personid2project'] = [];

            //Set the values
            $myvalues['id'] = $testcaseid;
            if(!empty($testcaseid))
            {
                
                $myvalues = $this->m_oMapHelper->getOneRichTestcaseRecord($testcaseid);

                $testcase_nm = $myvalues['testcase_nm'];
                
                $stepinfo = $myvalues['maps']['steps'];
                $stepstats = [];
                foreach($stepinfo as $stepdetail)
                {
                    $status_cd = $stepdetail['status_cd'];
                    $step_num = $stepdetail['step_num'];
                    if(!isset($stepstats[$status_cd]))
                    {
                        $stepstats[$status_cd] = [];
                    }
                    $stepstats[$status_cd][$step_num]=$step_num;
                }

                $me_bundle = $this->getMessageElementsBundle($myvalues);
                $myvalues['message_intro'] = $me_bundle['message_intro'];  
                
                $sortable_name2personid = [];
                $summary_lookup_personinfo = [];
                if(!empty($myvalues['lookups']['people']))
                {
                    foreach($myvalues['lookups']['people'] as $personid=>$detail)
                    {
                        $fullname = $detail['first_nm'] . " " . $detail['last_nm'];
                        $sortable_name2personid[$fullname] = $personid;
                                
                        $summary_info = [];
                        $summary_info['shortname'] = $detail['shortname'];
                        $summary_info['fullname'] = $fullname;
                        $summary_info['email'] = $detail['email'];
                        $summary_lookup_personinfo[$personid] = $summary_info;
                    }
                    unset($myvalues['maps']['people']['project_members']);
                }
                ksort($sortable_name2personid);
                $myvalues['sorted_name2personid'] = $sortable_name2personid;
                $myvalues['lookup_personinfo'] = $summary_lookup_personinfo;
                
                $map_personid2project = [];
                if(!empty($myvalues['maps']['people']['project_members']))
                {
                    foreach($myvalues['maps']['people']['project_members'] as $tag)
                    {
                        $map_personid2project[] = $tag;
                    }
                    unset($myvalues['maps']['people']['project_members']);
                }
                $myvalues['map_personid2project'] = $map_personid2project;
                $map_tag2testcase = [];
                if(!empty($myvalues['maps']['tags']))
                {
                    foreach($myvalues['maps']['tags'] as $tag)
                    {
                        $map_tag2testcase[] = $tag;
                    }
                    unset($myvalues['maps']['tags']);
                }
                $myvalues['map_tag2testcase'] = $map_tag2testcase;
            }            

            //\bigfathom\DebugHelper::showNeatMarkup($myvalues);
                
            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    function getRecipientsByType($potential_recipients)
    {
        $bytype = array('TO'=>[],'CC'=>[]);
        foreach($potential_recipients as $shortname=>$code)
        {
            if($code == 'TO')
            {
                $bytype['TO'][] = $shortname;
            } else
            if($code == 'CC')
            {
                $bytype['CC'][] = $shortname;
            }
        }
        return $bytype;
    }
    
    /**
     * Validate the proposed values.
     */
    function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            //DebugHelper::showNeatMarkup($myvalues,"LOOK MYVALUES");
            
            $recipients_by_type = $this->getRecipientsByType($myvalues['recipients']);
            if(count($recipients_by_type['TO'])<1)
            {
                form_set_error('recipients','Must have at least one "TO" recipient');
                $bGood = FALSE;
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
            
            $message_intro = $myvalues['message_intro'];
            $sorted_name2personid = $myvalues['sorted_name2personid'];
            
            if(isset($myvalues['lookup_personinfo']))
            {
                $lookup_personinfo = $myvalues['lookup_personinfo'];
            } else {
                if(!isset($myvalues['json_lookup_personinfo']))
                {
                    $lookup_personinfo = [];
                } else {
                    $json_lookup_personinfo = $myvalues['json_lookup_personinfo'];
                    $lookup_personinfo = json_decode($json_lookup_personinfo);
                }
            }
            
            if(isset($myvalues['sorted_name2personid']))
            {
                $sorted_name2personid = $myvalues['sorted_name2personid'];
            } else {
                if(!isset($myvalues['json_sorted_name2personid']))
                {
                    $sorted_name2personid = [];
                } else {
                    $json_sorted_name2personid = $myvalues['json_sorted_name2personid'];
                    $sorted_name2personid = json_decode($json_sorted_name2personid);
                }
            }
            
            $json_lookup_personinfo = json_encode($lookup_personinfo);
            $json_sorted_name2personid = json_encode($sorted_name2personid);
            
            $form['hiddenthings']['json_lookup_personinfo'] 
                = array('#type' => 'hidden', '#value' => $json_lookup_personinfo, '#disabled' => FALSE); 
            $form['hiddenthings']['json_sorted_name2personid'] 
                = array('#type' => 'hidden', '#value' => $json_sorted_name2personid, '#disabled' => FALSE); 
            
            $form['data_entry_area1']['recipients'] = array(
                '#type' => 'fieldset', 
                '#title' => t('Select Recipients ').'<i class="fa fa-envelope-o" aria-hidden="true"></i>', 
                '#tree' => TRUE,
            );
            
            $sorted_personids = $myvalues['sorted_name2personid'];
            $person_lookup = $myvalues['lookup_personinfo'];
            foreach($sorted_personids as $sortname=>$personid)
            {
                $person_info = $person_lookup[$personid];
                
                $shortname = $person_info['shortname'];
                $person_name = $person_info['fullname'];
                $person_email = $person_info['email'];

                $force_cc_selected = ($personid == $this_uid);
                if($force_cc_selected)
                {
                    $person_action = array('TO' => t('TO'), 'CC' => t('CC'),);
                } else {
                    $person_action = array('TO' => t('TO'), 'CC' => t('CC'), 'DNC' => t('Do not contact'),);
                }
                
                $form['data_entry_area1']['recipients'][$shortname] = array(
                 '#type' => 'radios',
                 '#prefix' => '<div>',
                 '#suffix' => '</div><br>', 
                 '#title' => t($person_name). " ($person_email)",
                 '#default_value' => $force_cc_selected ? 'CC' : 'DNC',
                 '#options' => $person_action,
                 '#disabled' => $disabled,   
                );
            }
            
            if(isset($myvalues['map_tag2testcase']))
            {
                $default_parent_tag_text = implode(', ', $myvalues['map_tag2testcase']);
            } else {
                $default_parent_tag_text = '';
            }
            
            $form['data_entry_area1']['message_intro'] = array(
                '#type' => 'textarea',
                '#title' => t('Optional custom message text'),
                '#default_value' => $message_intro,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#disabled' => $disabled
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
