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
 * Help with Sprints
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class SprintPageHelper
{
    public static $DEFAULT_SPRINT_DAYS = 10;
    
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_sprint_tablename = NULL;
    protected $m_sprint_status_tablename = NULL;
    protected $m_map_workitem2sprint_tablename = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    protected $m_oWriteHelper = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $projectid=NULL)
    {
        try
        {
            if(empty($projectid) || !is_numeric($projectid))
            {
                throw new \Exception("Missing valid projectid value for sprint!");
            }
            $this->m_projectid = $projectid;
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_sprint_tablename = DatabaseNamesHelper::$m_sprint_tablename;
            $this->m_sprint_status_tablename = DatabaseNamesHelper::$m_sprint_status_tablename;
            $this->m_map_workitem2sprint_tablename = DatabaseNamesHelper::$m_map_workitem2sprint_tablename;

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

            $loaded3 = module_load_include('php','bigfathom_core','core/FormHelper');
            if(!$loaded3)
            {
                throw new \Exception('Failed to load the FormHelper class');
            }
            $this->m_oFormHelper = new \bigfathom\FormHelper();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function createSprint($projectid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createNewSprint($projectid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }

    public function createSprintComment($myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createSprintCommunication($myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function deleteSprintComment($matchcomid, $uid)
    {
        try
        {
            return $this->m_oWriteHelper->deleteSprintCommunication($matchcomid, $uid);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function updateSprintComment($matchcomid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateSprintCommunication($matchcomid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function getInUseDetails($sprintid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfSprint($sprintid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the values to populate the form.
     */
    function getCommentFieldValues($comid=NULL,$parent_comid=NULL,$sprintid=NULL)
    {
        try
        {
            $myvalues['projectid'] = $this->m_projectid;
            if(!empty($comid))
            {
                $myvalues = $this->m_oMapHelper->getOneSprintComment($comid);
                $myvalues['original_owner_personid'] = $myvalues['owner_personid'];
                $myvalues['original_first_nm'] = $myvalues['first_nm'];
                $myvalues['original_last_nm'] = $myvalues['last_nm'];
                $myvalues['original_shortname'] = $myvalues['shortname'];
                $myvalues['original_updated_dt'] = $myvalues['updated_dt'];
                $myvalues['original_created_dt'] = $myvalues['created_dt'];
                $myvalues['edit_history'] = $this->m_oMapHelper->getSprintCommunicationHistory($comid);
            } else {
                if(empty($parent_comid) && empty($sprintid))
                {
                    throw new \Exception("Cannot get comment fields without at least a sprintid!");
                }
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['parent_comid'] = $parent_comid;
                $myvalues['sprintid'] = $sprintid;
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
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($sprintid=NULL)
    {
        try
        {
            if($sprintid != NULL)
            {
                //Get the core values 
                $myvalues = $this->m_oMapHelper->getSprintRecord($sprintid);
                
                //There can be more than one member
                $member_workitem_sql = "SELECT workitemid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_workitem2sprint_tablename  // {$this->m_map_workitem2sprint_tablename}"
                        . " WHERE sprintid=$sprintid";
                $member_goals_result = db_query($member_workitem_sql);
                $member_workitem_list = array();
                while($record = $member_goals_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $member_workitem_list[$id] = $id; 
                }            
                $myvalues['map_workitem2sprint'] = $member_workitem_list;
                $myvalues['sprint_nm'] = 'Sprint ' . $myvalues['iteration_ct'];
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['sprintid'] = NULL;
                $myvalues['sprint_nm'] = NULL;
                $myvalues['owner_projectid'] = NULL;
                $myvalues['title_tx'] = NULL;
                $myvalues['iteration_ct'] = NULL;
                $myvalues['start_dt'] = NULL;
                $myvalues['end_dt'] = NULL;
                $myvalues['status_cd'] = NULL;
                $myvalues['membership_locked_yn'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['story_tx'] = NULL;
                $myvalues['map_workitem2sprint'] = [];
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
    public function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            if($formMode == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('sprintid','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $sprintid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($sprintid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $total_pofg = count($connection_details['parent_of_sprints_list']);
                        $total_poft = count($connection_details['parent_of_tasks_list']);
                        $total_cofg = count($connection_details['child_of_sprints_list']);
                        $help_detail = "(parent of $total_pofg sprints, parent of $total_poft tasks, child of $total_cofg sprints)";
                        $is_parent_count = $total_pofg + $total_poft;
                        if($is_parent_count > 0)
                        {
                            form_set_error('sprintid',"Cannot delete because critical connections were found. $help_detail");
                            $bGood = FALSE;
                        }
                    }
                }
            }
            
            if($formMode == 'A' || $formMode == 'E')
            {
                if(!empty($myvalues['start_dt']) && !empty($myvalues['end_dt']) && !is_array($myvalues['start_dt']) && !is_array($myvalues['end_dt']))
                {
                    $start_dt_seconds = strtotime($myvalues['start_dt']);
                    $end_dt_seconds = strtotime($myvalues['end_dt']);
                    $days = ($end_dt_seconds - $start_dt_seconds) / 86400;
                    if($days < 1)
                    {
                        form_set_error('end_dt', 'The end date must be at least one day after the start date!');
                        $bGood = FALSE;
                    }
                    $and1 = db_and()->condition('start_dt', $myvalues['start_dt'], '>=')->condition('start_dt', $myvalues['end_dt'], '<=');
                    $and2 = db_and()->condition('end_dt', $myvalues['start_dt'], '>=')->condition('end_dt', $myvalues['end_dt'], '<=');
                    $overlap_query = db_select(DatabaseNamesHelper::$m_sprint_tablename,'p')
                        ->fields('p')
                        ->condition('owner_projectid', $myvalues['owner_projectid'],'=')
                        ->condition(db_or()->condition($and1)->condition($and2));
                    if($formMode == 'E')
                    {
                        $overlap_query->condition('iteration_ct', $myvalues['iteration_ct'],'<>');
                    }
                    $date_overlap_result = $overlap_query->execute();
                    if($date_overlap_result->rowCount() > 0)
                    {
                        $record = $date_overlap_result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('start_dt', 'Project '.$myvalues['owner_projectid'].' already has sprint#'
                                    .$record['iteration_ct'].' with overlapping date range [' . $record['start_dt'] . ' to ' . $record['end_dt'] . '] !');
                            $bGood = FALSE;
                        }
                    } else {
                        if($days > 15)
                        {
                            drupal_set_message("Your sprint length is $days days; consider keeping sprints short so collaborative assessments happen often", "warning");
                        }
                    }
                }

                if(!empty($myvalues['official_score']))
                {
                    $official_score = $myvalues['official_score'];
                    if(!is_numeric($official_score))
                    {
                        form_set_error('official_score', "The value '$official_score' is not a number!");
                        $bGood = FALSE;
                    } else
                    if($official_score < 0 || $official_score > 100)
                    {
                        form_set_error('official_score', "The value '$official_score' must be in range [0,100]!");
                        $bGood = FALSE;
                    }
                }
                
                if(!empty($myvalues['ot_scf']))
                {
                    $ptxt = trim($myvalues['ot_scf']);
                    if($ptxt != '0')
                    {
                        $p = floatval($ptxt);
                        if(empty($p) || $p === 0)
                        {
                            form_set_error('ot_scf', 'Cannot convert '.$myvalues['ot_scf'].' into a floating point number!');
                            $bGood = FALSE;
                        }
                        if($p < 0)
                        {
                            form_set_error('ot_scf', 'Cannot have a negative probability!  Range is [0,1]');
                            $bGood = FALSE;
                        }
                        if($p > 1)
                        {
                            form_set_error('ot_scf', 'Cannot have a probability over 1!  Range is [0,1]');
                            $bGood = FALSE;
                        }
                    }
                }
                
                //Check for duplicate keys too
                $result = db_select(DatabaseNamesHelper::$m_sprint_tablename,'p')
                    ->fields('p')
                    ->condition('owner_projectid', $myvalues['owner_projectid'],'=')
                    ->condition('iteration_ct', $myvalues['iteration_ct'],'=')
                    ->execute();
                if($result->rowCount() > 0)
                {
                    $record = $result->fetchAssoc();
                    $found_id = $record['id'];
                    if($found_id != $myvalues['id'])
                    {
                        form_set_error('iteration_ct', 'Project '.$myvalues['owner_projectid'].' already has a sprint#'.$record['iteration_ct'].'!');
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
    
    public function getSprintStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getSprintStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return DRUPAL form API of the context dashboard for a sprint
     */
    public function getContextDashboardElements($sprintid, $show_member_details=TRUE)
    {
        $myvalues = $this->getFieldValues($sprintid);
        $elements = array();
        $elements['dashboard'] = array(
            '#type' => 'item',
            '#prefix' => '<table class="context-dashboard">',
            '#suffix' => '</table>',
            '#tree' => TRUE
        );
        $owner_personid = $myvalues["owner_personid"];
        $ownername = $myvalues['first_nm'] . " " . $myvalues['last_nm'];
        $active_yn_markup = $myvalues['active_yn'] == 1 ? '<span>Yes</span>' : '<span class="colorful-no">No</span>';
        if(isset($myvalues['map_tag2sprint']))
        {
            $tags_tx = implode(', ', $myvalues['map_tag2sprint']);
        } else {
            $tags_tx = '';
        }
        
        $member_goals_markup = NULL;
        $member_count = 0;
        foreach($myvalues['map_workitem2sprint'] as $workitemid)
        {
            $member_count++;
            if($member_goals_markup !== NULL)
            {
                $member_goals_markup .= ', ';  
            }
            $member_goals_markup .= "<span title='status is TBD'>#$workitemid</span>";
        }
        $status_cd = $myvalues['status_cd'];
        $status_lookup = $this->m_oMapHelper->getSprintStatusByCode(TRUE);
        $status_info = $status_lookup[$status_cd];
        $status_wordy_status_state = $status_info['wordy_status_state'];
        $status_description = $status_info['description_tx'];

        $start_dt = $myvalues['start_dt'];
        $end_dt = $myvalues['end_dt'];
        $duration_days = UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
        $ot_scf = UtilityGeneralFormulas::getRoundSigDigs($myvalues['ot_scf']);
        $story_tx = $myvalues['story_tx'];
        $elements['dashboard']['details']['row1'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1 width='120px'><label for='sprintname'>Sprint Name</label></td>"
                . "<td colspan=1><span id='sprintname' title='#$sprintid'>{$myvalues['sprint_nm']}</span></td>"
                . "<td colspan=1><label for='statuscode'>Status</label></td>"
                . "<td colspan=1><span id='statuscode' title='{$status_description}'>{$status_cd} - {$status_wordy_status_state}</span></td>"
                . "<td colspan=1><label>Owner</label></td>"
                . "<td colspan=1><span title='#{$owner_personid}'>$ownername</span></td>"
                . "<td colspan=1><label for='isactive' title='Setting active to No is a type of soft delete'>Is Active</label></td>"
                . "<td colspan=10><span id='isactive'>{$active_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row1b1'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='member_count'>Start Date</label></td>"
                . "<td colspan=1><span id='member_count' title='The start date of the sprint'>{$start_dt}</span></td>"
                . "<td colspan=1><label for='members'>End Date</label></td>"
                . "<td colspan=1><span id='members' title='The end date of the sprint'>{$end_dt}</span></td>"
                . "<td colspan=1><label for='dash_duration_days'>Duration</label></td>"
                . "<td colspan=13><span id='dash_duration_days' title='Number of days between start and end date'>{$duration_days}</span></td>"
                . "</tr>");
        if($show_member_details)
        {
            $elements['dashboard']['details']['row2'] = array('#type' => 'item',
                    '#markup' => "<tr>"
                    . "<td colspan=1><label for='member_count'>Member Goal Count</label></td>"
                    . "<td colspan=1><span id='member_count' title='Number of goals in this sprint'>{$member_count}</span></td>"
                    . "<td colspan=1><label for='members'>Member Workitems</label></td>"
                    . "<td colspan=14><span id='members' title='The unique IDs of the member workitems, hover for statuses'>{$member_goals_markup}</span></td>"
                    . "</tr>");
        }
        if(!empty($myvalues['title_tx']))
        {
            $elements['dashboard']['details']['row2b1'] = array('#type' => 'item',
                    '#markup' => "<tr>"
                    . "<td colspan=1><label for='members'>Story Title</label></td>"
                    . "<td colspan=16><span id='title_tx' title='The story title'>{$myvalues['title_tx']}</span></td>"
                    . "</tr>");
        }
        $elements['dashboard']['details']['row2b2'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='members'>Story</label></td>"
                . "<td colspan=16><span id='story_tx' title='The story text'>{$story_tx}</span></td>"
                . "</tr>");
        $official_score = $myvalues['official_score'];
        if(!empty($official_score))
        {
            $score_body_tx = $myvalues['score_body_tx'];
            $elements['dashboard']['details']['row3'] = array('#type' => 'item',
                    '#markup' => "<tr>"
                    . "<td colspan=1><label for='official_score' title='Values in range of [0,100] '>Score</label></td>"
                    . "<td colspan=1><span id='official_score'>$official_score</span></td>"
                    . "<td colspan=1><label for='score_body_tx' title='The comment associated with the score'>Score Comment</label></td>"
                    . "<td colspan=13><span id='score_body_tx'>$score_body_tx</span></td>"
                    . "</tr>");
        }
        $elements['dashboard']['details']['row4'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='tags' title='Text labels associated with this sprint'>Tags</label></td>"
                . "<td colspan=15><span id='tags'>{$tags_tx}</span></td>"
                . "</tr>");
        return $elements;    
    }
    
    public function getSprintLeaderOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getSprintLeaders($this->m_projectid);
            foreach($all as $code=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectOptions()
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getProjectsByID();
            $status_lookup = $this->m_oMapHelper->getWorkitemStatusByCode();
            $parent_options = array();
            $parent_options[0] = "";
            foreach($all as $id=>$record)
            {
                $active_yn = $record['active_yn'];
                if($active_yn == 1)
                {
                    //Not inactive
                    $status_cd = $record['status_cd'];
                    if(!isset($status_lookup[$status_cd]))
                    {
                        $terminal_yn = 0;
                    } else {
                        $terminal_yn = $status_lookup[$status_cd]['terminal_yn'];
                    }
                    if($terminal_yn != 1)
                    {
                        //Not in a final state
                        $name = $record['root_workitem_nm'];
                        $parent_options[$id] = $name;
                    }
                }
            }
            return $parent_options;
        } catch (\Exception $ex) {
            throw $ex;
        }
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
    
    public function getAvailableWorkitemOptions($owner_projectid, $selected_sprintid)
    {
        try
        {
            $skip_this_workitem_nm='';
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getSprintNonMemberWorkitemsByWID($owner_projectid, $selected_sprintid);
            $status_lookup = $this->m_oMapHelper->getWorkitemStatusByCode();
            $parent_options = array();
            //$parent_options[0] = "None";
            foreach($all as $id=>$record)
            {
                $active_yn = $record['active_yn'];
                if($active_yn == 1)
                {
                    //Not inactive
                    $status_cd = $record['status_cd'];
                    if(!isset($status_lookup[$status_cd]))
                    {
                        $terminal_yn = 0;
                    } else {
                        $terminal_yn = $status_lookup[$status_cd]['terminal_yn'];
                    }
                    if($terminal_yn != 1)
                    {
                        //Not in a final state
                        $workitem_nm = $record['workitem_nm'];
                        $parent_options[$id] = $workitem_nm;
                    }
                }
            }
            return $parent_options;
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
            $sprintid = $id;

            if(isset($myvalues['owner_projectid']))
            {
                $owner_projectid = $myvalues['owner_projectid'];
            } else {
                $owner_projectid = '';
            }
            
            if(isset($myvalues['iteration_ct']))
            {
                $iteration_ct = $myvalues['iteration_ct'];
            } else {
                $project_sprint_overview = $this->m_oMapHelper->getProjectSprintOverview($owner_projectid);
                if(isset($project_sprint_overview['projects'][$owner_projectid]['last_sprint_number']))
                {
                    $last_sprint_number = $project_sprint_overview['projects'][$owner_projectid]['last_sprint_number'];
                    $iteration_ct = $last_sprint_number + 1;
                } else {
                    $iteration_ct = 1;
                }
            }
            
            if(isset($myvalues['original_sprintid']))
            {
                $original_sprintid = $myvalues['original_sprintid'];
            } else {
                $original_sprintid = '';
            }
            if(isset($myvalues['title_tx']))
            {
                $title_tx = $myvalues['title_tx'];
            } else {
                $title_tx = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['ot_scf']))
            {
                $ot_scf = UtilityGeneralFormulas::getRoundSigDigs($myvalues['ot_scf']);
            } else {
                $ot_scf = NULL;
            }
            if(isset($myvalues['status_cd']))
            {
                $status_cd = $myvalues['status_cd'];
            } else {
                $status_cd = NULL;
            }
            if(isset($myvalues['membership_locked_yn']))
            {
                $membership_locked_yn = $myvalues['membership_locked_yn'];
            } else {
                $membership_locked_yn = 0;
            }
            if(isset($myvalues['official_score']))
            {
                $official_score = $myvalues['official_score'];
            } else {
                $official_score = '';
            }
            if(isset($myvalues['score_body_tx']))
            {
                $score_body_tx = $myvalues['score_body_tx'];
            } else {
                $score_body_tx = '';
            }
            
            $end_dt = NULL; //Initialize as empty
            if(isset($myvalues['start_dt']))
            {
                $start_dt = $myvalues['start_dt'];
            } else {
                $date_query = db_select(DatabaseNamesHelper::$m_sprint_tablename,'p')
                    ->condition('owner_projectid', $owner_projectid,'=');
                $date_query->addExpression('MAX(end_dt)', 'max_end_dt');
                $date_query_result = $date_query->execute();
                if($date_query_result->rowCount() > 0)
                {
                    $record = $date_query_result->fetchAssoc();
                    $reference_dt = $record['max_end_dt'];
                    if(empty($reference_dt) || $reference_dt < 1)
                    {
                        $start_dt = date("Y-m-d", time());
                    } else {
                        $start_dt_timestamp = UtilityGeneralFormulas::getDayShiftedDateAsTimestamp($reference_dt, 1);
                        $start_dt = date("Y-m-d", $start_dt_timestamp);
                        $end_dt_timestamp = UtilityGeneralFormulas::getDayShiftedDateAsTimestamp($start_dt, self::$DEFAULT_SPRINT_DAYS);
                        $end_dt = date("Y-m-d", $end_dt_timestamp);
                    }
                } else {
                    $start_dt = date("Y-m-d", time());
                }
            }
            if(isset($myvalues['end_dt']))
            {
                //Overwrite the existing value if the array has one.
                $end_dt = $myvalues['end_dt'];
            }
            
            if(isset($myvalues['story_tx']))
            {
                $story_tx = $myvalues['story_tx'];
            } else {
                $story_tx = '';
            }
            
            $options_owner_projectid = $this->getProjectOptions();
            $options_sprintleaderid = $this->getSprintLeaderOptions();
            $options_member_workitem = $this->getAvailableWorkitemOptions($owner_projectid,$id);
            $options_sprint_status = $this->getSprintStatusOptions();

            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_sprintid'] 
                = array('#type' => 'hidden', '#value' => $original_sprintid, '#disabled' => FALSE); 
            $form['hiddenthings']['iteration_ct'] 
                = array('#type' => 'hidden', '#value' => $iteration_ct, '#disabled' => FALSE);        
            
            //$showcolname_iteration_ct = 'iteration_ct';
            $showcolname_sprintid = 'sprintid';
            $disable_sprintid = $disabled;       //Default behavior
            if($disable_sprintid)
            {
                $form['hiddenthings']['sprintid'] 
                    = array('#type' => 'hidden', '#value' => $sprintid, '#disabled' => FALSE);        
                $showcolname_sprintid = 'show_sprintid';
            }

            $form['hiddenthings']['owner_projectid'] 
                = array('#type' => 'hidden', '#value' => $owner_projectid, '#disabled' => FALSE);        
            $form['hiddenthings']['iteration_ct'] 
                = array('#type' => 'hidden', '#value' => $iteration_ct, '#disabled' => FALSE);        
            $form['hiddenthings']['ot_scf'] 
                = array('#type' => 'hidden', '#value' => $ot_scf, '#disabled' => FALSE);

            if(isset($myvalues['map_workitem2sprint']))
            {
                $default_parent_sprintid_list = $myvalues['map_workitem2sprint'];
            } else {
                $default_parent_sprintid_list = array();
            }
            
            $form['data_entry_area1']['owner_projectid'] = array(
                '#type' => 'select',
                '#title' => t('Project'),
                '#default_value' => $owner_projectid,
                '#options' => $options_owner_projectid,
                '#required' => TRUE,
                '#description' => t('A sprint belongs to one project'),
                '#disabled' => TRUE
            );
            
           $form['data_entry_area1']['show_iteration_ct'] = array(
                '#type' => 'textfield',
                '#title' => t('Iteration'),
                '#default_value' => $iteration_ct,
                '#size' => 3,
                '#maxlength' => 3,
                '#required' => TRUE,
                '#description' => t('The sprint number'),
                '#disabled' => TRUE
            );
           
            $form['data_entry_area1']['title_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('Sprint Title'),
                '#default_value' => $title_tx,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => FALSE,
                '#description' => t('Short optional title for this sprint'),
                '#disabled' => $disable_sprintid
            );
 
            $form['data_entry_area1']['story_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Theme Statement'),
                '#default_value' => $story_tx,
                '#size' => 80,
                '#maxlength' => 2048,
                '#required' => FALSE,
                '#description' => t('A short outcome-theme or mission statement for this sprint'),
                '#disabled' => $disabled
            );
            
            /* Not sure we really want this one to be editable
            $form['data_entry_area1']['ot_scf'] = array(
                '#type' => 'textfield',
                '#title' => t('Confidence'),
                '#default_value' => $ot_scf,
                '#size' => 4,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Probability that all the member work items will be completed within the sprint period'),
                '#disabled' => $disabled
            );
            */
            
            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_sprint_status,
                '#required' => TRUE,
                '#description' => t('The current status of this sprint'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['starting_date_info']['membership_locked_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Freeze Membership'),
                '#default_value' => $membership_locked_yn,
                '#options' => $ynoptions,
                '#description' => t('When yes, users cannot add or remove workitems from the sprint.')
            );

            if($formType == 'A')
            {
                $form['hiddenthings']['official_score'] 
                    = array('#type' => 'hidden', '#value' => $official_score, '#disabled' => FALSE);        
                $form['hiddenthings']['score_body_tx'] 
                    = array('#type' => 'hidden', '#value' => $score_body_tx, '#disabled' => FALSE);        
            } else {
                $form['data_entry_area1']['official_score'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Official Score'),
                    '#default_value' => $official_score,
                    '#size' => 3,
                    '#maxlength' => 3,
                    '#required' => FALSE,
                    '#description' => t('Value is on scale of 0 to 100; 0 is complete failure in every respect, 100 is complete success in every respect measured'),
                    '#disabled' => $disabled
                );

                $form['data_entry_area1']['score_body_tx'] = array(
                    '#type' => 'textarea',
                    '#title' => t('Score Comment'),
                    '#default_value' => $score_body_tx,
                    '#size' => 80,
                    '#maxlength' => 2048,
                    '#required' => FALSE,
                    '#description' => t('A comment about the official score'),
                    '#disabled' => $disabled
                );
            }
            
            $form['data_entry_area1']['daterange'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_row'>",            
                            '#suffix' => "</div>");            
            
            $form['data_entry_area1']['daterange']['start_dt'] = array(
              '#type' => 'date_popup', 
              '#date_format'   => 'Y-m-d',
              '#title' => 'Start Date', 
              '#default_value' => $start_dt, 
              '#description' => 'When this sprint begins', 
              '#required' => TRUE,
              '#prefix' => "<div class='simulate_table_col'>",            
              '#suffix' => "</div>"
            );            

            $form['data_entry_area1']['daterange']['end_dt'] = array(
              '#type' => 'date_popup', 
              '#date_format'   => 'Y-m-d',
              '#title' => 'End Date', 
              '#default_value' => $end_dt, 
              '#description' => 'When this sprint ends', 
              '#required' => TRUE,
              '#prefix' => "<div class='simulate_table_col'>",            
              '#suffix' => "</div>"
            );            
            
            if(isset($myvalues['map_workitem2sprint']))
            {
                $default_selected_workitemids = $myvalues['map_workitem2sprint'];
            } else {
                $default_selected_workitemids = array();
            }

            if($formType != 'A')
            {
                $disable_local_membership_edit = TRUE;
                $form['data_entry_area1']['map_workitem2sprint'] = $this->m_oFormHelper
                        ->getMultiSelectElement($disable_local_membership_edit
                        , 'Member Workitems'
                        , $default_selected_workitemids
                        , $options_member_workitem
                        , FALSE
                        , 'The workitems to complete for this sprint to be a success'
                    );
            }
            
            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Sprint Leader'),
                '#default_value' => $owner_personid,
                '#options' => $options_sprintleaderid,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful completion of this sprint?'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if sprint is active, else no.')
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
                
            if(!empty($myvalues['sprintid']))
            {
                $sprintid = $myvalues['sprintid'];
            } else {
                throw new \Exception("Cannot get comment form without a sprintid!");
            }
            $sprint_record = $this->m_oMapHelper->getSprintRecord($sprintid);
            if(!empty($myvalues['status_cd_at_time_of_com']))
            {
                $status_cd_at_time_of_com = $myvalues['status_cd_at_time_of_com'];
            } else {
                $status_cd_at_time_of_com = $sprint_record['status_cd'];
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

            $form['hiddenthings']['owner_personid'] 
                = array('#type' => 'hidden', '#value' => $owner_personid, '#disabled' => FALSE); 

            $form['hiddenthings']['active_yn'] 
                = array('#type' => 'hidden', '#value' => $active_yn, '#disabled' => FALSE); 
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            
            $form['hiddenthings']['parent_comid'] 
                = array('#type' => 'hidden', '#value' => $parent_comid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['sprintid'] 
                = array('#type' => 'hidden', '#value' => $sprintid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['status_cd_at_time_of_com'] 
                = array('#type' => 'hidden', '#value' => $status_cd_at_time_of_com, '#disabled' => FALSE); 

            $form['hiddenthings']['updated_dt'] 
                = array('#type' => 'hidden', '#value' => $updated_dt, '#disabled' => FALSE); 

            $form['hiddenthings']['created_dt'] 
                = array('#type' => 'hidden', '#value' => $created_dt, '#disabled' => FALSE); 
            
            $dashboard = $this->getContextDashboardElements($sprintid);
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
                $parent_comment_record = $this->m_oMapHelper->getOneSprintComment($parent_comid);
                $parent_action_requested_concern = $parent_comment_record['action_requested_concern'];
                $parent_requests_action = (!empty($parent_action_requested_concern) && $parent_action_requested_concern > 0);
                drupal_set_title("$ftword a Reply to Sprint Communication #$parent_comid");
            } else {
                $parent_comment_record = NULL;
                $parent_requests_action = FALSE;
                $parent_action_requested_concern = 0;
                drupal_set_title("$ftword a Root Sprint Comment");
            }

            if($parent_comment_record != NULL)
            {
                $parent_first_name = $parent_comment_record['first_nm'];
                $parent_last_name = $parent_comment_record['last_nm'];
                $parent_owner_personid = $parent_comment_record['owner_personid'];
                $parent_owner_markup = "<li>Author: <span title='#$parent_owner_personid'>$parent_first_name $parent_last_name</span>";
                $parent_title_tx = $parent_comment_record['title_tx'];
                $parent_body_tx = $parent_comment_record['body_tx'];
                $parent_comment_markup = "<ul>";
                $parent_comment_markup .= $parent_owner_markup;
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
