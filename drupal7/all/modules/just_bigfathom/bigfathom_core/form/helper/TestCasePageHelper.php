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
 * Help with test case information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TestCasePageHelper
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

    public function createTestcaseComment($myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createTestcaseCommunication($myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function deleteTestcaseComment($matchcomid, $uid)
    {
        try
        {
            return $this->m_oWriteHelper->deleteTestcaseCommunication($matchcomid, $uid);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function updateTestcaseComment($matchcomid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateTestcaseCommunication($matchcomid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function getInUseDetails($testcaseid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfTestcase($testcaseid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the values to populate the form.
     */
    function getCommentFieldValues($comid=NULL,$parent_comid=NULL,$testcaseid=NULL,$default_stepnum=NULL)
    {
        try
        {
            $myvalues['projectid'] = $this->m_projectid;
            if(!empty($comid))
            {
                $myvalues = $this->m_oMapHelper->getOneTestcaseComment($comid);
                $myvalues['original_owner_personid'] = $myvalues['owner_personid'];
                $myvalues['original_first_nm'] = $myvalues['first_nm'];
                $myvalues['original_last_nm'] = $myvalues['last_nm'];
                $myvalues['original_shortname'] = $myvalues['shortname'];
                $myvalues['original_updated_dt'] = $myvalues['updated_dt'];
                $myvalues['original_created_dt'] = $myvalues['created_dt'];
                $myvalues['edit_history'] = $this->m_oMapHelper->getTestcaseCommunicationHistory($comid);
            } else {
                if(empty($parent_comid) && empty($testcaseid))
                {
                    throw new \Exception("Cannot get comment fields without at least a testcaseid!");
                }
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['parent_comid'] = $parent_comid;
                $myvalues['testcaseid'] = $testcaseid;
                $myvalues['default_stepnum'] = $default_stepnum;
                $myvalues['stepnum_list_tx'] = NULL;
                $myvalues['status_cd_at_time_of_com'] = NULL;
                $myvalues['title_tx'] = NULL;
                $myvalues['body_tx'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['action_requested_concern'] = NULL;
                $myvalues['action_reply_cd'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['first_nm'] = NULL;
                $myvalues['last_nm'] = NULL;
                $myvalues['shortname'] = NULL;
                $myvalues['updated_dt'] = NULL;
                $myvalues['created_dt'] = NULL;
                $myvalues['original_first_nm'] = NULL;
                $myvalues['original_last_nm'] = NULL;
                $myvalues['original_shortname'] = NULL;
                $myvalues['original_updated_dt'] = NULL;
                $myvalues['original_created_dt'] = NULL;
                $myvalues['edit_history'] = array();
            }
            $myvalues['id'] = $comid;

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
    public function formIsValidComment($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            if($formMode != 'D')
            {
                $testcaseid = $myvalues['testcaseid'];
                if(empty($testcaseid))
                {
                    throw new \Exception("Missing expected testcaseid!");
                }
                if(!empty($myvalues['stepnum_list_tx']))
                {
                    if(is_array($myvalues['stepnum_list_tx']))
                    {
                        $stepnum_list_ar = $myvalues['stepnum_list_tx'];
                    } else {
                        $stepnum_list_ar = explode(',',$myvalues['stepnum_list_tx']);
                    }
                } else {
                    $stepnum_list_ar = [];
                }
                $clean_stepnum_list_ar = [];
                foreach($stepnum_list_ar as $stepnum)
                {
                    if(!empty(trim($stepnum)))
                    {
                        if(is_numeric($stepnum))
                        {
                            $clean_stepnum_list_ar[$stepnum] = $stepnum;
                        } else {
                            form_set_error('stepnum_list_tx', "The entry '$stepnum' is not a number!");
                            $bGood = FALSE;
                            break;
                        }
                        if($stepnum < 1)
                        {
                            form_set_error('stepnum_list_tx', "The entry '$stepnum' is NOT a valid step number!");
                            $bGood = FALSE;
                            break;
                        }
                    }
                }
                if(count($clean_stepnum_list_ar)>0)
                {
                    $step_count = $this->m_oMapHelper->getStepCountInTestcase($testcaseid);
                    $bad_stepnum_ar = [];
                    foreach($clean_stepnum_list_ar as $stepnum)
                    {
                        if($step_count < $stepnum)
                        {
                            $bad_stepnum_ar[] = $stepnum;
                        }
                    }
                    if(count($bad_stepnum_ar) > 0)
                    {
                        $badcount = count($bad_stepnum_ar);
                        $bad_stepnum_txt = implode(", ", $bad_stepnum_ar);
                        form_set_error('stepnum_list_tx', "The following $badcount entries are NOT valid step numbers for testcase#{$testcaseid}: $bad_stepnum_txt");
                        $bGood = FALSE;
                    }
                }
            }
            
            return $bGood;
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
            if($testcaseid != NULL)
            {
                
                $myvalues = $this->m_oMapHelper->getOneRichTestcaseRecord($testcaseid);
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
                
                $map_workitem2testcase = [];
                if(!empty($myvalues['maps']['workitems']))
                {
                    foreach($myvalues['maps']['workitems'] as $wid)
                    {
                        $map_workitem2testcase[] = $wid;
                    }
                    unset($myvalues['maps']['workitems']);
                }
                $myvalues['map_workitem2testcase'] = $map_workitem2testcase;
                
                $map_step2testcase = [];
                if(!empty($myvalues['maps']['steps']))
                {
                    foreach($myvalues['maps']['steps'] as $wid)
                    {
                        $map_step2testcase[] = $wid;
                    }
                    unset($myvalues['maps']['steps']);
                }
                $myvalues['map_step2testcase'] = $map_step2testcase;

            } else {
                
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['testcase_dt'] = NULL;
                $myvalues['testcase_nm'] = NULL;
                
                $myvalues['map_workitem2testcase'] = [];
                $myvalues['map_tag2testcase'] = [];
                $myvalues['map_delegate_owner'] = [];
                $myvalues['map_step2testcase'] = [];
                
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
                    form_set_error('testcase_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                }
            } else {
                if(empty($myvalues['steps_encoded_tx']))
                {
                    throw new \Exception("Missing required steps_encoded_tx!");
                }
                $steps_info = json_decode($myvalues['steps_encoded_tx']);
                $steps_sequence_ar = $steps_info->sequence;
                $clean_steps_sequence_ar = [];
                foreach($steps_sequence_ar as $one_step)
                {
                    $d = $one_step->d;
                    $e = $one_step->e;
                    if(strlen(trim($d)) > 0)
                    {
                        $clean_steps_sequence_ar[] = array('d'=>trim($d),'e'=>trim($e));
                    }
                }
                if(count($clean_steps_sequence_ar) == 0)
                {
                    form_set_error('steps_encoded_tx','Must have at least one step instruction!');
                    $bGood = FALSE;
                }
            }
            
            if(trim($myvalues['testcase_nm']) == '')
            {
                form_set_error('testcase_nm','The testcase name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    if(!is_numeric($myvalues['importance']) || trim($myvalues['importance']) == '')
                    {
                        form_set_error('importance','The importance must be a number in range [0,100]');
                        $bGood = FALSE;
                    } else if($myvalues['importance'] < 0 || $myvalues['importance'] > 100)
                    {
                        form_set_error('importance','The importance must be a number in range [0,100]');
                        $bGood = FALSE;
                    }
                    
                    if($formMode == 'A')
                    {
                        $allowed_count = 0;
                    } else {
                        $allowed_count = 1;
                    }
                    //Check for duplicate keys too
                    $result = db_select(DatabaseNamesHelper::$m_testcase_tablename,'p')
                        ->fields('p')
                        ->condition('testcase_nm', $myvalues['testcase_nm'],'=')
                        ->condition('owner_projectid', $this->m_projectid,'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('testcase_nm', 'Already have a testcase with this name');
                            $bGood = FALSE;
                        }
                    }
                    
                    if(!empty($myvalues['map_workitem2testcase_tx']))
                    {
                        if(is_array($myvalues['map_workitem2testcase_tx']))
                        {
                            $map_workitem2testcase_ar = $myvalues['map_workitem2testcase_tx'];
                        } else {
                            $map_workitem2testcase_ar = explode(',',$myvalues['map_workitem2testcase_tx']);
                        }
                    } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                        $map_workitem2testcase_ar = $myvalues['maps']['workitems'];
                    } else {
                        $map_workitem2testcase_ar = [];
                    }
                    $clean_map_workitem2testcase_ar = [];
                    foreach($map_workitem2testcase_ar as $candidate_wid)
                    {
                        if(!empty(trim($candidate_wid)))
                        {
                            if(is_numeric($candidate_wid))
                            {
                                $clean_map_workitem2testcase_ar[$candidate_wid] = $candidate_wid;
                            } else {
                                form_set_error('map_workitem2testcase_tx', "The entry '$candidate_wid' is not a numeric ID!");
                                $bGood = FALSE;
                                break;
                            }
                        }
                    }
                    if(count($clean_map_workitem2testcase_ar)>0)
                    {
                        $bad_wids_ar = $this->m_oMapHelper->getWorkitemsNotInProject($this->m_projectid, $clean_map_workitem2testcase_ar);
                        if(count($bad_wids_ar) > 0)
                        {
                            $badcount = count($bad_wids_ar);
                            $bad_wids_txt = implode(", ", $bad_wids_ar);
                            form_set_error('map_workitem2testcase_tx', "The following $badcount workitems are NOT in project#{$this->m_projectid}: $bad_wids_txt");
                            $bGood = FALSE;
                        }
                    }
                    if(!empty($myvalues['effort_tracking_workitemid']))
                    {
                        $effort_tracking_workitemid = $myvalues['effort_tracking_workitemid'];
                        if(!is_numeric($effort_tracking_workitemid))
                        {
                            form_set_error('effort_tracking_workitemid', "The value '$effort_tracking_workitemid' is not numeric!");
                            $bGood = FALSE;
                        }
                        $opid = $this->m_oMapHelper->getProjectIDForWorkitem($effort_tracking_workitemid, FALSE);
                        if($this->m_projectid != $opid)
                        {
                            form_set_error('effort_tracking_workitemid', "The value $effort_tracking_workitemid is not a workitemid in the project!");
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
    
    public function getTestCaseCreatorOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateTestCaseCreators($this->m_projectid);
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
    
    public function getTestCaseStatusOptions($formType=NULL)
    {
        try
        {
            $include_terminal_yn = $formType != 'A' ? 1 : 0;
            $include_happy_terminal_yn = ($include_terminal_yn && $formType != 'E') ? 1 : 0;
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getTestCaseStatusByCode();
            foreach($all as $code=>$record)
            {
                if($include_terminal_yn || !$record['terminal_yn'])
                {
                    if($include_happy_terminal_yn || !$record['happy_yn'])
                    {
                        $myoptions[$code] = $record['wordy_status_state'];
                    }
                }
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getTestCaseStepStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getTestCaseStepStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getTestCasePerspectiveOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $myoptions['U'] = 'User';
            $myoptions['T'] = 'Technical';
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateTestcase($testcaseid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateTestcase($testcaseid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function createTestcase($projectid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createTestcase($projectid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }

    function deleteTestcase($testcase)
    {
        $this->m_oWriteHelper->deleteTestcase($testcase);
    }

    private function getStepsListFromEncodedText($steps_encoded_tx)
    {
        try
        {
            $the_list = [];
            $the_list[] = array('description'=>'hoho','status_cd'=>'abc','comms'=>'commstuff');
            //$the_list[] = "<tr><td>2</td><td>do stuff yaya</td><td>dropdown</td><td>actions here</td></tr>";
            //$the_list[] = "<tr><td>3</td><td>do stuff yaya</td><td>dropdown</td><td>actions here</td></tr>";
            return $the_list;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getStepsRowsFromList($steps_list)
    {
        try
        {
            $rows_ar = [];
            $step_num = 0;
            foreach($steps_list as $detail)
            {
                $step_num++;
                $description = $detail['description'];
                $status_cd = $detail['status_cd'];
                $rows_ar[] = "<tr><td>$step_num</td><td>$description</td><td>dropdown@$status_cd</td><td>artifacts here</td><td>actions here</td></tr>";
            }
            return $rows_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return DRUPAL form API of the context dashboard for a goal
     */
    public function getContextDashboardElements($testcaseid)
    {
        $myvalues = $this->getFieldValues($testcaseid);
        $elements = array();
        $elements['dashboard'] = array(
            '#type' => 'item',
            '#prefix' => '<table class="context-dashboard">',
            '#suffix' => '</table>',
            '#tree' => TRUE
        );
        $active_yn = $myvalues["active_yn"];
        $owner_personid = $myvalues["owner_personid"];
        $uah = new UserAccountHelper();
        $ownername = $uah->getExistingPersonFullName($owner_personid);
        $active_yn_markup = $active_yn == 1 ? '<span>Yes</span>' : '<span class="colorful-no">No</span>';

        if(isset($myvalues['map_tag2workitem']))
        {
            $tags_tx = implode(', ', $myvalues['map_tag2workitem']);
        } else {
            $tags_tx = '';
        }
        
        $basetypelabel = 'Test Case';    
        $status_cd = $myvalues['status_cd'];
        $status_lookup = $this->m_oMapHelper->getTestcaseStatusByCode(TRUE);
        $status_info = $status_lookup[$status_cd];
        $status_wordy_status_state = $status_info['wordy_status_state'];
        $status_description = $status_info['description_tx'];
        $elements['dashboard']['details']['row1'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td width='180px'><label for='goalname'>$basetypelabel Name</label></td>"
                . "<td colspan=13><span id='goalname' title='#$testcaseid'>{$myvalues['testcase_nm']}</span></td>"
                . "<td><label for='isactive' title='Setting active to No is a type of soft delete'>Is Active</label></td>"
                . "<td><span id='isactive'>{$active_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='statuscode'>Status</label></td>"
                . "<td colspan=1><span id='statuscode' title='$status_description'>{$status_cd} - {$status_wordy_status_state}</span></td>"
                . "<td width='180px' colspan=1><label>Owner</label></td>"
                . "<td colspan=13><span title='#{$owner_personid}'>$ownername</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row4b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='tags' title='Text labels associated with this test case'>Tags</label></td>"
                . "<td colspan=15><span id='tags'>{$tags_tx}</span></td>"
                . "</tr>");
        return $elements;    
    }
    
    public function getActionRequestOptions($include_inactive=TRUE)
    {
        try
        {
            $myoptions = array();
            $all = $this->m_oMapHelper->getActionImportanceCategories();
            if($include_inactive)
            {
                $myoptions[0] = "No";
            }
            foreach($all as $code=>$name)
            {
                $myoptions[$code] = "Yes ($name concern)";
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getActionReplyOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getActionStatusByCode();
            foreach($all as $code=>$record)
            {
                $title_tx = $record['title_tx'];
                $myoptions[$code] = $title_tx;
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
            
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            global $user;
            global $base_url;
            $send_urls = [];
            $send_urls['base_url'] = $base_url;
            $send_urls['page_keys'] = [];
            $send_urls['page_keys']['comments'] = 'bigfathom/testcase/mng_comments';
            $send_urls['page_keys']['send_notifications'] = 'bigfathom/testcase/send_notifications';
            //$images_url = $base_url .'/'. $theme_path.'/images';
            
            $myicons_names_ar = array('communicate_empty'
                                    ,'communicate_hascontent'
                                    ,'communicate_action_high'
                                    ,'communicate_action_medium'
                                    ,'communicate_action_low'
                                    ,'communicate_action_closed');
            $imgurls_by_purposename = [];
            foreach($myicons_names_ar as $purpose_name)
            {
                $imgurls_by_purposename[$purpose_name] = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName($purpose_name);
            }
            
            $send_urls['imgurls_by_purposename'] = $imgurls_by_purposename;

            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
            }
            
            $map_comm_summary = $this->m_oMapHelper->getCommThreadSummaryMap("testcasestep", $id);
//DebugHelper::showNeatMarkup($map_comm_summary,"LOOK here is summary of comms");            
            drupal_add_js(array('testcaseid'=>$id,'personid'=>$user->uid,'formtype'=>$formType,'myurls' => $send_urls), 'setting');            
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/TestCaseUIToolkit.js");
            
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
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>"
                . "\n<div id='dialog-anchor'></div>",
                '#suffix' => "\n</section>\n",
                '#disabled' => $disabled,
            );     
                
            if(isset($myvalues['owner_projectid']))
            {
                $owner_projectid = $myvalues['owner_projectid'];
            } else {
                $owner_projectid = '';
            }
            if(isset($myvalues['testcase_nm']))
            {
                $testcase_nm = $myvalues['testcase_nm'];
            } else {
                $testcase_nm = '';
            }
            if(isset($myvalues['blurb_tx']))
            {
                $blurb_tx = $myvalues['blurb_tx'];
            } else {
                $blurb_tx = '';
            }
            if(isset($myvalues['perspective_cd']))
            {
                $perspective_cd = $myvalues['perspective_cd'];
            } else {
                $perspective_cd = '';
            }
            if(isset($myvalues['precondition_tx']))
            {
                $precondition_tx = $myvalues['precondition_tx'];
            } else {
                $precondition_tx = '';
            }
            if(isset($myvalues['postcondition_tx']))
            {
                $postcondition_tx = $myvalues['postcondition_tx'];
            } else {
                $postcondition_tx = '';
            }

            if(isset($myvalues['steps_encoded_tx']))
            {
                $steps_encoded_tx = $myvalues['steps_protected_tx'];
            } else {
                $min_step_rows = 5;
                $map_step2testcase = isset($myvalues['map_step2testcase']) ? $myvalues['map_step2testcase'] : [];
                $sequence_ar = [];
                foreach($map_step2testcase as $detail)
                {
                    $sequence_ar[] = array('id'=>$detail['id']
                            ,'d'=>$detail['instruction_tx']
                            ,'e'=>$detail['expectation_tx']
                            ,'cd'=>$detail['status_cd']);
                }
                $step_count = count($sequence_ar);
                if($step_count<$min_step_rows && $formType === 'A')
                {
                    for($i=$step_count; $i<$min_step_rows; $i++)
                    {
                        $sequence_ar[] = array('id'=>'','d'=>'','e'=>'','cd'=>'');
                    }
                }
                $bucket = (object) ['sequence' => $sequence_ar];
                $steps_encoded_tx = json_encode($bucket);
            }
            
            if(isset($myvalues['references_tx']))
            {
                $references_tx = $myvalues['references_tx'];
            } else {
                $references_tx = '';
            }
            if(isset($myvalues['active_yn']))
            {
                $active_yn = $myvalues['active_yn'];
            } else {
                $active_yn = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            
            if(isset($myvalues['effort_tracking_workitemid']))
            {
                $effort_tracking_workitemid = $myvalues['effort_tracking_workitemid'];
            } else {
                $effort_tracking_workitemid = NULL;
            }
            
            if(isset($myvalues['status_cd']))
            {
                $status_cd = $myvalues['status_cd'];
            } else {
                $status_cd = NULL;
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 95;
            }

            $options_testcase_status = $this->getTestCaseStatusOptions($formType);
            //$options_delegate_owner = $this->getWorkitemDelegateOwnerOptions();
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_testcase_nm'] 
                = array('#type' => 'hidden', '#value' => $testcase_nm, '#disabled' => FALSE);        
            $form['hiddenthings']['steps_encoded_tx'] 
                = array('#type' => 'hidden', '#default_value' => $steps_encoded_tx, '#disabled' => FALSE);        
            
            $showcolname_testcase_nm = 'testcase_nm';
            $disable_testcase_nm = $disabled || $id==1 || $id==10;
            
            $options_testcaseowners = $this->getTestCaseCreatorOptions();
            $options_perspective = $this->getTestCasePerspectiveOptions();

            $form['data_entry_area1']['perspective_cd'] = array(
                '#type' => 'select',
                '#title' => t('Perspective'),
                '#default_value' => $perspective_cd,
                '#options' => $options_perspective,
                '#required' => TRUE,
                '#description' => t('From what perspective is this test case written?  User implies the test case is from the external user perspective and technical implies it is from the programmer/implementor perspective.  Another way to think of this is that "User" is whitebox testing and "Technical" is blackbox testing.'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['testcase_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Test Case Name'),
                '#default_value' => $testcase_nm,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('The unique convenient name for this test case'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['importance'] = array(
                '#type' => 'textfield',
                '#title' => t('Importance'),
                '#default_value' => $importance,
                '#size' => 3,
                '#maxlength' => 3,
                '#required' => TRUE,
                '#description' => t('Current importance for implementing this test case in the context of the project.  Scale is [0,100] with 0 being no importance whatsoever and 100 being nothing is more important.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_testcase_status,
                '#required' => TRUE,
                '#description' => t('The current status of this test case'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['blurb_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Blurb'),
                '#default_value' => $blurb_tx,
                '#size' => 256,
                '#maxlength' => 256,
                '#required' => TRUE,
                '#description' => t("A short description of this test case"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['precondition_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Preconditions'),
                '#default_value' => $precondition_tx,
                '#size' => 512,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t("A short description of the conditions that exist for this test case to begin"),
                '#disabled' => $disabled
            );
            
            $steps_tablename = 'testcase-steps-table';
            $steps_tablebottomactionname = 'testcase-steps-table-actionbuttons';
            //$steps_list = $this->getStepsListFromEncodedText($steps_encoded_tx);
            $rows_ar = [];//$this->getStepsRowsFromList($steps_list);
            $rows_markup = implode("\n",$rows_ar);

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="steps-container">',
                '#suffix' => '</div>', 
                '#description' => t("The test steps that take us from the preconditions state into the postconditions state of the system being tested."),
                '#tree' => TRUE,
            );
            
            $th_markup = '<th class="nowrap" width="2em">'.'<span title="Step number">'.t('No.').'</span></th>'
                . '<th class="nowrap">'.'<span title="Describes the detailed actions of a step">'.t('Instruction').'</span></th>'
                . '<th class="nowrap">'.'<span title="Describes the expected result of executing the instruction">'.t('Expectation').'</span></th>';
            if($formType == 'A')
            {
                $steps_label_markup = '<label>Steps <span class="form-required" title="This field is required.">*</span></label>';
                $th_markup .= '<th class="action-options" width="10em">' . t('Action Options').'</th>';
            } else if($formType == 'V') {
                $steps_label_markup = '<label>Steps</label>';
                $th_markup .= '<th class="nowrap">'
                        . '<span title="The tester assessment of the expected result from the step">'.t('Evaluation').'</span></th>';
                $th_markup .= '<th class="action-options" width="10em">' . t('Action Options').'</th>';
            } else if($formType == 'E') {
                $steps_label_markup = '<label>Steps <span class="form-required" title="This field is required.">*</span></label>';
                $th_markup .= '<th class="nowrap">'
                        . '<span title="The tester assessment of the expected result from the step">'.t('Evaluation').'</span></th>'
                        . '<th class="action-options">' . t('Action Options').'</th>';
            } else {
                $steps_label_markup = '<label>Steps</label>';
                $th_markup .= '<th class="nowrap">'
                        . '<span title="The tester assessment of the expected result from the step">'.t('Evaluation').'</span></th>'
                        . '<th class="action-options">' . t('Action Options').'</th>';
            } 
            
            $form["data_entry_area1"]['table_container']['steps'] = array(
                    '#type' => 'item',
                    '#prefix' => $steps_label_markup,
                     '#markup' => 
                    '<table id="' . $steps_tablename . '" class="dynamic-step-rows">'
                    . '<thead>'
                    . '<tr>'
                    . $th_markup
                    . '</tr>'
                    . '</thead>'
                    . '<tbody>'
                    . $rows_markup
                    .  '</tbody>'
                    . '</table>'
                    . '<br><div id="' . $steps_tablebottomactionname . '" ></div>');

            $form['data_entry_area1']['postcondition_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Postconditions'),
                '#default_value' => $postcondition_tx,
                '#size' => 512,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t("A short description of the conditions that exist once all the test case steps are completed"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['references_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('References'),
                '#default_value' => $references_tx,
                '#size' => 512,
                '#maxlength' => 2000,
                '#required' => FALSE,
                '#description' => t("Bibliographic style references, if any, that are relevant to this test case"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => t('Owned By'),
                '#default_value' => $owner_personid,
                '#options' => $options_testcaseowners,
                '#required' => TRUE,
                '#description' => t('The user that owns this test case'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['effort_tracking_workitemid'] = array(
                '#type' => 'textfield',
                '#title' => t('Effort Tracking Workitem'),
                '#default_value' => $effort_tracking_workitemid,
                '#size' => 10,
                '#maxlength' => 20,
                '#required' => FALSE,
                '#description' => t('Optionally provide the workitem ID of the workitem record which tracks the level of effort expected/remaining to complete refinement and execution of this test case'),
                '#disabled' => $disabled
            );
            
            if(isset($myvalues['map_workitem2testcase']))
            {
                $default_parent_workitem_text = implode(', ', $myvalues['map_workitem2testcase']);
            } else {
                $default_parent_workitem_text = '';
            }
            $form['data_entry_area1']['map_workitem2testcase_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('Directly Tested Workitems'),
                '#default_value' => $default_parent_workitem_text,
                '#size' => 80,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#description' => t('Optional comma delimited list of workitem IDs that are directly tested by executing this test case'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Live'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('No if the test case has been retired.')
            );

            if(isset($myvalues['map_tag2testcase']))
            {
                $default_parent_tag_text = implode(', ', $myvalues['map_tag2testcase']);
            } else {
                $default_parent_tag_text = '';
            }
            $form['data_entry_area1']['map_tag2testcase_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('Tags'),
                '#default_value' => $default_parent_tag_text,
                '#size' => 80,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#description' => t('Optional comma delimited text tags to associate with this test case'),
                '#disabled' => $disabled
            );
            
            return $form;
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
    function getCommentForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            if($formType == 'V' || $formType == 'D')
            {
                $disabled = TRUE;
            }
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
            
            if($formType == 'A' || $formType == 'E')
            {
                $myvalues['owner_personid'] = $this_uid;
            }
            $owner_personid = $myvalues['owner_personid'];
            if(!empty($myvalues['active_yn']))
            {
                $active_yn = $myvalues['active_yn'];
            } else {
                $active_yn = 1;
            }
                
            if(!empty($myvalues['testcaseid']))
            {
                $testcaseid = $myvalues['testcaseid'];
            } else {
                throw new \Exception("Cannot get comment form without a testcaseid!");
            }
            $testcase_record = $this->m_oMapHelper->getTestcaseRecord($testcaseid);
            if(!empty($myvalues['status_cd_at_time_of_com']))
            {
                $status_cd_at_time_of_com = $myvalues['status_cd_at_time_of_com'];
            } else {
                $status_cd_at_time_of_com = $testcase_record['status_cd'];
            }
            if(isset($myvalues['parent_comid']))
            {
                $parent_comid = $myvalues['parent_comid'];
            } else {
                $parent_comid = NULL;
            }
            if(isset($myvalues['comment_type']))
            {
                $comment_type = $myvalues['comment_type'];
            } else {
                $comment_type = NULL;
            }
            if(isset($myvalues['parent_page']))
            {
                $parent_page = $myvalues['parent_page'];
            } else {
                $parent_page = NULL;
            }
            if(isset($myvalues['title_tx']))
            {
                $title_tx = $myvalues['title_tx'];
            } else {
                $title_tx = NULL;
            }
            if(isset($myvalues['body_tx']))
            {
                $body_tx = $myvalues['body_tx'];
            } else {
                $body_tx = NULL;
            }
            if(isset($myvalues['original_first_nm']))
            {
                $original_first_nm = $myvalues['original_first_nm'];
            } else {
                $original_first_nm = NULL;
            }
            if(isset($myvalues['original_last_nm']))
            {
                $original_last_nm = $myvalues['original_last_nm'];
            } else {
                $original_last_nm = NULL;
            }
            if(isset($myvalues['original_owner_personid']))
            {
                $original_owner_personid = $myvalues['original_owner_personid'];
            } else {
                $original_owner_personid = NULL;
            }
            if(isset($myvalues['original_updated_dt']))
            {
                $original_updated_dt = $myvalues['original_updated_dt'];
            } else {
                $original_updated_dt = NULL;
            }

            if(isset($myvalues['updated_dt']))
            {
                $updated_dt = $myvalues['updated_dt'];
            } else {
                $updated_dt = NULL;
            }
            
            if(isset($myvalues['created_dt']))
            {
                $created_dt = $myvalues['created_dt'];
            } else {
                $created_dt = NULL;
            }
            
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
            }
            
            if(isset($myvalues['action_requested_concern']))
            {
                $action_requested_concern = $myvalues['action_requested_concern'];
            } else {
                $action_requested_concern = NULL;
            }
            if(isset($myvalues['action_reply_cd']))
            {
                $action_reply_cd = $myvalues['action_reply_cd'];
            } else {
                $action_reply_cd = NULL;
            }
            
            $options_action_status = $this->getActionReplyOptions();
            $options_action_request = $this->getActionRequestOptions();

            $form['hiddenthings']['parent_page'] 
                = array('#type' => 'hidden', '#value' => $parent_page, '#disabled' => FALSE); 
            
            $form['hiddenthings']['owner_personid'] 
                = array('#type' => 'hidden', '#value' => $owner_personid, '#disabled' => FALSE); 

            $form['hiddenthings']['active_yn'] 
                = array('#type' => 'hidden', '#value' => $active_yn, '#disabled' => FALSE); 
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            
            $form['hiddenthings']['parent_comid'] 
                = array('#type' => 'hidden', '#value' => $parent_comid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['testcaseid'] 
                = array('#type' => 'hidden', '#value' => $testcaseid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['status_cd_at_time_of_com'] 
                = array('#type' => 'hidden', '#value' => $status_cd_at_time_of_com, '#disabled' => FALSE); 

            $form['hiddenthings']['updated_dt'] 
                = array('#type' => 'hidden', '#value' => $updated_dt, '#disabled' => FALSE); 

            $form['hiddenthings']['created_dt'] 
                = array('#type' => 'hidden', '#value' => $created_dt, '#disabled' => FALSE); 
            
            $dashboard = $this->getContextDashboardElements($testcaseid);
            $form['data_entry_area1']['context_dashboard'] = $dashboard;   
            
            if($formType == 'A')
            {
                $ftword = "Create";
            } else
            if($formType == 'E')
            {
                $ftword = "Edit";
            } else
            if($formType == 'V')
            {
                $ftword = "View";
            } else
            if($formType == 'D')
            {
                $ftword = "Delete";
            } 
            $comment_type_prefix = "";
            if($myvalues['comment_type'] == 'REPLY')
            {
                
                $comment_type_prefix = "Reply ";
                $parent_comment_record = $this->m_oMapHelper->getOneTestcaseComment($parent_comid);
                $parent_action_requested_concern = $parent_comment_record['action_requested_concern'];
                $parent_requests_action = (!empty($parent_action_requested_concern) && $parent_action_requested_concern > 0);
                drupal_set_title("$ftword a Reply to Testcase Communication #$parent_comid");
                $editable_step_map = FALSE;
            } else {
                $parent_comment_record = NULL;
                $parent_requests_action = FALSE;
                $parent_action_requested_concern = 0;
                drupal_set_title("$ftword a Root Testcase Comment");
                $editable_step_map = TRUE;
            }

            if(isset($myvalues['stepnum_list_tx']))
            {
                $stepnum_list_tx = $myvalues['stepnum_list_tx'];
            } else {
                $stepnum_list_tx = '';
            }
            if(empty($stepnum_list_tx) && !empty($myvalues['default_stepnum']))
            {
                $stepnum_list_tx = $myvalues['default_stepnum'];
            }

            if($parent_comment_record != NULL)
            {
                $parent_first_name = $parent_comment_record['first_nm'];
                $parent_last_name = $parent_comment_record['last_nm'];
                $parent_owner_personid = $parent_comment_record['owner_personid'];
                $parent_owner_markup = "<li>Author: <span title='#$parent_owner_personid'>$parent_first_name $parent_last_name</span>";
                if(empty($stepnum_list_tx))
                {
                    $show_stepnum_tx = 'None declared';
                } else {
                    $show_stepnum_tx = $stepnum_list_tx;
                }
                $parent_title_tx = $parent_comment_record['title_tx'];
                $parent_body_tx = $parent_comment_record['body_tx'];
                $parent_comment_markup = "<ul>";
                $parent_comment_markup .= $parent_owner_markup;
                //$parent_comment_markup .= "<li>Relevant Steps: <span title='The step numbers that are directly relevant to comments in this thread'>$show_stepnum_tx</span>";
                if($parent_requests_action)
                {
                    $pcword = $options_action_request[$parent_action_requested_concern];
                    $parent_comment_markup .= "<li>Action Requested: <span class='comment-info' title='action is requested to resolve this comment'>$pcword</span>";
                } else {
                    $parent_comment_markup .= "<li>Action Requested: <span class='comment-info' title='No action has been requested in this comment'>No</span>";
                }
                if(!empty($parent_title_tx))
                {
                    $parent_comment_markup .= "<li>Title: <span class='comment-title' title='the comment title'>".$parent_title_tx."</span>";
                }
                $parent_comment_markup .= "<li>Comment: <span class='comment-blurb'>".$parent_body_tx."</span>";
                $parent_comment_markup .= "</ul>";
                $form['data_entry_area1']['parent_comment_group'] = array(
                  '#type' => 'fieldset',
                  '#title' => t('Summary of communication#' . $parent_comid),
                  '#collapsible' => FALSE,
                  '#collapsed' => FALSE,  
                );                
                $form['data_entry_area1']['parent_comment_group']['parent_comment_summary'] 
                        = array('#type' => 'item',
                                '#markup' => $parent_comment_markup);
            }
            
            if($formType != 'A')
            {
                $headinglabel = "Detail of communication#$id";
            } else {
                $headinglabel = "Detail of new comment";
            }
            
            $form['data_entry_area1']['comment_type_heading'] = array('#type' => 'item',
                '#markup' => ""
                . "<h2>$headinglabel...</h2>");
            
            if($formType != 'A')
            {
                $edit_history = $myvalues['edit_history'];
                $count_changed = count($edit_history);
                if($count_changed > 0)
                {
                    $oldestinfo = reset($edit_history);
                    if($count_changed == 1)
                    {
                        $edit_times_language = "$count_changed time";
                    } else {
                        $edit_times_language = "$count_changed times";
                    }
                    $form['data_entry_area1']['original_author_info_group'] = array(
                      '#type' => 'fieldset',
                      '#title' => t('History of communication#' . $id 
                              . " edited " . $edit_times_language 
                              . " since " . $oldestinfo['original_updated_dt']),
                      '#collapsible' => TRUE,
                      '#collapsed' => TRUE,  
                    );     
                    $history_author_markup = "<ul>";
                    foreach($edit_history as $oneedit)
                    {
                        $history_owner_personid = $oneedit['owner_personid'];
                        $history_updated_dt = $oneedit['original_updated_dt'];
                        $replaced_dt = $oneedit['replaced_dt'];
                        $history_first_nm = $oneedit['first_nm'];
                        $history_last_nm = $oneedit['last_nm'];
                        $num_attachments_added = $oneedit['num_attachments_added'];
                        $num_attachments_removed = $oneedit['num_attachments_removed'];

                        $changes = array();
                        if($num_attachments_added > 0)
                        {
                            if($num_attachments_added == 1)
                            {
                                $changes[] = '1 attachment added';
                            } else {
                                $changes[] = $num_attachments_added.' attachments added';
                            }
                        }
                        if($num_attachments_removed > 0)
                        {
                            if($num_attachments_removed == 1)
                            {
                                $changes[] = '1 attachment removed';
                            } else {
                                $changes[] = $num_attachments_removed.' attachments removed';
                            }
                        }
                        if($oneedit['changed_title_tx'] == 1)
                        {
                            if(trim($oneedit['title_tx']) == '')
                            {
                                $changes[] = 'title changed from being BLANK';
                            } else {
                                $changes[] = 'title changed from "'.$oneedit['title_tx'].'"';
                            }
                        }
                        if($oneedit['changed_body_tx'] == 1)
                        {
                            $changes[] = 'comment changed';
                        }
                        if($oneedit['changed_action_requested_concern'] == 1)
                        {
                            $keyword = UtilityGeneralFormulas::getKeywordForConcernLevel($oneedit['action_requested_concern']);
                            $changes[] = 'action request concern level changed from '.$keyword;
                        }
                        if($oneedit['changed_action_reply_cd'] == 1)
                        {
                            if(trim($oneedit['action_reply_cd']) == '')
                            {
                                $changes[] = 'action reply status changed from being BLANK';
                            } else {
                                $changes[] = 'action reply status changed from "'.$oneedit['action_reply_cd'].'"';
                            }
                        }
                        if($oneedit['changed_active_yn'] == 1)
                        {
                            $changes[] = 'active status changed from '.$oneedit['changed_active_yn'];
                        }
                        $change_count = count($changes);
                        if($change_count == 0)
                        {
                            $changes_markup = 'Saved with no changes';
                        } else {
                            $changes_tx = implode(", ", $changes);
                            $changes_markup = "<b>$change_count Changes:</b> $changes_tx";
                        }

                        $history_author_markup .= "<li>"
                                . " <b>Replaced:</b>$replaced_dt "
                                . " <b>Original Author:</b> <span title='#$history_owner_personid'>$history_first_nm $history_last_nm</span>"
                                . " <span>$changes_markup</span>"
                                . "</li>";
                    }
                    $history_author_markup .= "</ul>";
                    $form['data_entry_area1']['original_author_info_group']['most_recent_auth_summary'] 
                            = array('#type' => 'item',
                                    '#markup' => $history_author_markup);
                }
            }
            
            if($editable_step_map)
            {
                $form['data_entry_area1']['stepnum_list_tx'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Relevant Steps'),
                    '#default_value' => $stepnum_list_tx,
                    '#size' => 40,
                    '#maxlength' => 40,
                    '#required' => FALSE,
                    '#description' => t('Optional comma delimited list of step numbers that are directly relevant to this communication thread'),
                    '#disabled' => $disabled
                );
            }
            
            $form['data_entry_area1']['title_tx'] = array(
                '#type' => 'textfield',
                '#title' => t($comment_type_prefix . 'Title'),
                '#default_value' => $title_tx,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => FALSE,
                '#description' => t('A short comment title'),
                '#disabled' => $disabled
            );
            $comment_rows = max(5, substr_count($body_tx, "\n"));
            $form['data_entry_area1']['body_tx'] = array(
                '#type' => 'textarea',
                '#title' => t($comment_type_prefix . 'Comment Text'),
                '#default_value' => $body_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#rows' => $comment_rows,
                '#required' => TRUE,
                '#description' => t('The comment detail'),
                '#disabled' => $disabled
            );

            if(!$parent_requests_action)
            {
                //Allow this one to request an action because parent is not already requesting
                $form['data_entry_area1']['action_requested_concern'] = array(
                    '#type' => 'select',
                    '#title' => t('Group Member Action Requested'),
                    '#default_value' => $action_requested_concern,
                    '#options' => $options_action_request,
                    '#required' => TRUE,
                    '#description' => t('Yes if this comment requires an action from a group member, else no.'),
                    '#disabled' => $disabled
                );
                $form['hiddenthings']['action_reply_cd'] 
                    = array('#type' => 'hidden', '#value' => $action_reply_cd, '#disabled' => FALSE); 
            } else {
                //Parent already requested an action, are we resolving it here?
                $form['data_entry_area1']['action_reply_cd'] = array(
                    '#type' => 'select',
                    '#title' => t('Action Resolution Status'),
                    '#default_value' => $action_reply_cd,
                    '#options' => $options_action_status,
                    '#required' => FALSE,
                    '#description' => t('The new action status as of this comment'),
                    '#disabled' => $disabled
                );
                $form['hiddenthings']['action_requested_concern'] 
                    = array('#type' => 'hidden', '#value' => 0, '#disabled' => FALSE); 
            }
            
            if(isset($myvalues['attachments']) && is_array($myvalues['attachments']))
            {
                $attached_count = count($myvalues['attachments']);
                if($attached_count > 0)
                {
                    if($attached_count == 1)
                    {
                        $desc_markup = "This file is currently attached to this communication.";
                    } else {
                        $desc_markup = "These $attached_count files are currently attached to this communication.";
                    }
                    $form['data_entry_area1']['existing_attachment'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('Existing File Attachments'),
                        '#collapsible' => TRUE,
                        '#collapsed' => FALSE,  
                        '#description' => t($desc_markup),
                    );     
                    $existing_markup_ar = array();
                    if($disabled || $formType != 'E')
                    {
                        $existing_markup_ar[] = "<th>Name</th>"
                                        . "<th>Size</th>"
                                        . "<th>Uploaded Date</th>"
                                        . "<th>Uploaded By</th>";
                    } else {
                        $existing_markup_ar[] = "<th>Name</th>"
                                        . "<th>Size</th>"
                                        . "<th>Uploaded Date</th>"
                                        . "<th>Uploaded By</th>"
                                        . "<th>Action Options</th>";
                    }
                    $form['hiddenthings']['fileremovals']
                        = array('#tree' => TRUE); 
                    foreach($myvalues['attachments'] as $k=>$one_existing_attachment)
                    {
                        $aid = $one_existing_attachment['attachmentid'];
                        $filename = $one_existing_attachment['filename'];
                        
                        $trigger_colnameroot = "file{$aid}";
                        $trigger_colname = "{$trigger_colnameroot}_removalflag";
                        $trigger_userclicker = "{$trigger_colnameroot}_removalclicker";
                        $trigger_filelink = "{$trigger_colnameroot}_filelink";
                        $form['hiddenthings']['fileremovals'][$trigger_colname]
                            = array('#type' => 'hidden', 
                                '#tree' => TRUE,
                                '#default_value' => '', 
                                '#disabled' => FALSE); 

                        $showicon_url = \bigfathom\UtilityGeneralFormulas::getFileIconURL($filename);
                        $showfile_markup = l($filename
                                , "bigfathom/attachments/download"
                                , array('query' => array('aid' => $aid)
                                , 'attributes'=>array('id'=>$trigger_filelink, 'title'=>'click to download')));
                        if($disabled || $formType != 'E')
                        {
                            $action_markup = "";
                        } else {
                            $action_markup = "<td> <a id='$trigger_userclicker' href='#' "
                                            . " onclick='toggleRemove(\"{$trigger_colnameroot}\",$aid,\"$filename\");"
                                            . "return false;' "
                                            . " title='Click this to mark $filename for removal'>Click to Remove File</a></td>";
                        }
                        $filesize_text = \bigfathom\UtilityGeneralFormulas::getFriendlyFilesizeText($one_existing_attachment['filesize']);
                        $uploader_name_markup = '<span title="#' . $one_existing_attachment['uploaded_by_uid'] . '">'
                                                .$one_existing_attachment['first_nm']
                                                .' '
                                                .$one_existing_attachment['last_nm']
                                                ."</span>";
                        $existing_markup_ar[] = "<td><img alt='visual icon for file' src='$showicon_url'/> " . $showfile_markup . "</td>"
                                        . "<td> " . $filesize_text . " </td>"
                                        . "<td> " . $one_existing_attachment['uploaded_dt'] . " </td>"
                                        . "<td> " . $uploader_name_markup . " </td>"
                                        . $action_markup;
                    }
                    $existing_markup = "<table width='100%'><tr>" 
                            . implode("</tr><tr>", $existing_markup_ar) 
                            . "</tr></table>";
                    $form['data_entry_area1']['existing_attachment']['details'] 
                            = array('#type' => 'item',
                                '#prefix' => '<script>'
                                . 'function toggleRemove(colnameroot,togglevalue,filename){'
                                . 'var colname="fileremovals["+colnameroot+"_removalflag]";'
                                . 'var idclicker=colnameroot+"_removalclicker";'
                                . 'var idfilelink=colnameroot+"_filelink";' . "\n"
                                . "//alert('Hello toggle ' + colname + ' #' + togglevalue + ' n=' + filename);\n"
                                . 'var lf = document.getElementById(idfilelink);'
                                . 'var cf = document.getElementById(idclicker);'
                                . 'var tf = document.getElementsByName(colname)[0];'
                                . "if(tf.value == togglevalue) {\n"
                                . '  tf.value = "";'
                                . '  cf.innerHTML = "Click to Remove File";'
                                . '  cf.title = "Click this to mark the "+filename+" file for removal";'
                                . '  lf.className = "attachment-keep";' . "\n"
                                . "} else {\n"
                                . '  tf.value = togglevalue;'
                                . '  cf.innerHTML = "Click to Keep File";'
                                . '  cf.title = "Click this to keep the "+filename+" file attached";'
                                . '  lf.className = "attachment-remove";'
                                . "\n}\n"
                                . "//alert('Goodbye toggle ' + colname);\n"
                                . '}'
                                . '</script>',
                                    '#markup' => $existing_markup 
                                    );
                }
            }
            
            //START FILE STUFF
            if(!$disabled && ($formType == 'E' || $formType == 'A'))
            {
                $oContext = \bigfathom\Context::getInstance();
                $allowed_filetypes = \bigfathom\UtilityGeneralFormulas::getAllowedAttachmentFileUploadTypes();
                $showattach = 3;
        
                //$form['#attributes'] = array('enctype' => "multipart/form-data");
                $form['data_entry_area1']['attachment'] = array(
                    '#type' => 'fieldset',
                    '#title' => t('Add New File Attachments'),
                    '#collapsible' => TRUE,
                    '#collapsed' => TRUE,  
                    '#description' => t("You can select and attach up to $showattach relevant files per save "
                                    . 'with any of the following extensions: '
                                    . $allowed_filetypes),
                );     
                for($i=0; $i < $showattach; $i++)
                {
                    $attachmentcount=$i+1;
                    $form['data_entry_area1']['attachment']["newfile{$attachmentcount}"] = array(
                        '#type' => 'file',
                        '#name' => "files[attachment_{$attachmentcount}]",
                        //'#title' => t("Attachment"),
                        '#required' => FALSE,
                        '#disabled' => $disabled,
                        );
                }
            }
            //END FILE STUFF
            
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
