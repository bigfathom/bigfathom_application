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
 * Help with Workitems
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class WorkitemPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oWriteHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    
    public function __construct($urls_override_arr, $my_classname=NULL, $projectid=NULL)
    {
        $this->m_projectid = $projectid;
        $this->m_oContext = \bigfathom\Context::getInstance();

        $this->m_urls_arr = $urls_override_arr;
        $this->m_my_classname = $my_classname;

        $loaded0 = module_load_include('inc','bigfathom_core','functions/goals');
        if(!$loaded0)
        {
            throw new \Exception('Failed to load the functions for ajax');
        }
        
        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
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
    }

    public function updateWorkitem($workitemid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateWorkitem($workitemid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function createWorkitem($projectid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createWorkitem($projectid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }

    function deleteWorkitem($workitem)
    {
        $this->m_oWriteHelper->deleteWorkitem($workitem);
    }

    public function createWorkitemCommunication($myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createWorkitemCommunication($myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function deleteWorkitemCommunication($matchcomid, $uid)
    {
        try
        {
            return $this->m_oWriteHelper->deleteWorkitemCommunication($matchcomid, $uid);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function updateWorkitemCommunication($matchcomid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateWorkitemCommunication($matchcomid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function getInUseDetails($workitemid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfWorkitem($workitemid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    function getWorkitemOwnersMapBundle($wid_ar,$checkfor_personid=NULL)
    {
        $bundle = [];
        $bundle['metadata']['wid_ar'] = $wid_ar;
        $bundle['metadata']['checkfor_personid'] = $checkfor_personid;
        $rawmap = $this->m_oMapHelper->getWorkitemOwners($wid_ar);
        if(!empty($checkfor_personid))
        {
            $uah = new \bigfathom\UserAccountHelper();
            $upb = $uah->getUserProfileBundle();
            $is_superowner = $upb['roles']['systemroles']['summary']['is_systemadmin'];
            if(!$is_superowner)
            {
                $bundle = $this->m_oMapHelper->getProjectOwnersBundle($this->m_projectid);                
                $all_owner_map = $bundle['all'];
                $is_superowner = isset($all_owner_map[$checkfor_personid]);
            }
            $okay_map = [];
            foreach($rawmap as $wid=>$people)
            {
                foreach($people as $personid)
                {
                    if($is_superowner || $personid == $checkfor_personid)
                    {
                        $okay_map[$wid] = $people;
                    }
                }
            }
            $notokay_map = [];
            foreach($rawmap as $wid=>$people)
            {
                if(!isset($okay_map[$wid]))
                {
                    $notokay_map[$wid] = $people;
                }
            }
            $bundle['owned_wid2people'] = $okay_map;
            $bundle['notowned_wid2people'] = $notokay_map;
        }
        $bundle['rawmap'] = $rawmap;
        return $bundle;
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($workitemid=NULL)
    {
        try
        {
            $myvalues['projectid'] = $this->m_projectid;
            if($workitemid != NULL)
            {
                $myvalues = $this->m_oMapHelper->getOneRichWorkitemRecord($workitemid);
                $map_delegate_owner = [];
                foreach($myvalues['maps']['delegate_owner'] as $personid)
                {
                    $map_delegate_owner[$personid]=$personid;
                }
                $myvalues['map_delegate_owner'] = $map_delegate_owner;
                unset($myvalues['maps']['delegate_owner']);

                //There can be more than one parent
                $parent_goals_sql = "SELECT depwiid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_wi2wi_tablename
                        . " WHERE antwiid=$workitemid";
                $parent_goals_result = db_query($parent_goals_sql);
                $parent_goals_list = array();
                while($record = $parent_goals_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $parent_goals_list[$id] = $id; 
                }            
                $myvalues['map_ddw'] = $parent_goals_list;
                
                //There can be more than one ant
                $ant_goals_sql = "SELECT antwiid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_wi2wi_tablename
                        . " WHERE depwiid=$workitemid";
                $ant_goals_result = db_query($ant_goals_sql);
                $ant_goals_list = array();
                while($record = $ant_goals_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $ant_goals_list[$id] = $id; 
                }            
                $myvalues['map_daw'] = $ant_goals_list;
                
                //There can be more than one member
                $member_roles_sql = "SELECT roleid as id"
                        . " FROM ".DatabaseNamesHelper::$m_map_prole2wi_tablename
                        . " WHERE workitemid=$workitemid";
                $member_roles_result = db_query($member_roles_sql);
                $member_roles_list = array();
                while($record = $member_roles_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $member_roles_list[$id] = $id; 
                }            
                $myvalues['map_prole2workitem'] = $member_roles_list;
                
                //There can be many tags
                $tag_sql = "SELECT tag_tx"
                        . " FROM " . DatabaseNamesHelper::$m_map_tag2workitem_tablename
                        . " WHERE workitemid=$workitemid";
                $tag_result = db_query($tag_sql);
                $tag_list = array();
                while($record = $tag_result->fetchAssoc()) 
                {
                    $id = $record['tag_tx'];
                    $tag_list[$id] = $id; 
                }            
                $myvalues['map_tag2workitem'] = $tag_list;
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['workitem_basetype'] = NULL;
                $myvalues['workitem_nm'] = NULL;
                $myvalues['limit_branch_effort_hours_cd'] = NULL;
                $myvalues['branch_effort_hours_est'] = NULL;
                $myvalues['branch_effort_hours_est_p'] = NULL;
                $myvalues['effort_hours_est'] = NULL;
                $myvalues['effort_hours_est_p'] = NULL;
                $myvalues['effort_hours_worked_act'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['externally_billable_yn'] = NULL;
                $myvalues['client_deliverable_yn'] = NULL;
                $myvalues['planned_fte_count'] = NULL;
                $myvalues['status_cd'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['purpose_tx'] = NULL;
                
                $myvalues['map_ddw'] = [];
                $myvalues['map_daw'] = [];
                $myvalues['map_prole2workitem'] = [];
                $myvalues['map_tag2workitem'] = [];
                $myvalues['map_delegate_owner'] = [];
                
                $myvalues['planned_start_dt'] = NULL;
                $myvalues['planned_start_dt_locked_yn'] = 0;
                $myvalues['actual_start_dt'] = NULL;
                $myvalues['planned_end_dt'] = NULL;
                $myvalues['planned_end_dt_locked_yn'] = 0;
                $myvalues['actual_end_dt'] = NULL;
                
                $myvalues['chargecode'] = NULL;
                
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the values to populate the form.
     */
    function getCommentFieldValues($comid=NULL,$parent_comid=NULL,$workitemid=NULL)
    {
        try
        {
            $myvalues['projectid'] = $this->m_projectid;
            if(!empty($comid))
            {
                $myvalues = $this->m_oMapHelper->getOneWorkitemCommunication($comid);
                $myvalues['original_owner_personid'] = $myvalues['owner_personid'];
                $myvalues['original_first_nm'] = $myvalues['first_nm'];
                $myvalues['original_last_nm'] = $myvalues['last_nm'];
                $myvalues['original_shortname'] = $myvalues['shortname'];
                $myvalues['original_updated_dt'] = $myvalues['updated_dt'];
                $myvalues['original_created_dt'] = $myvalues['created_dt'];
                $myvalues['edit_history'] = $this->m_oMapHelper->getWorkitemCommunicationHistory($comid);
            } else {
                if(empty($parent_comid) && empty($workitemid))
                {
                    throw new \Exception("Cannot get comment fields without at least a workitemid!");
                }
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['parent_comid'] = $parent_comid;
                $myvalues['workitemid'] = $workitemid;
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
            
            $map_ddw = isset($myvalues['map_ddw']) ? $myvalues['map_ddw'] : [];
            $map_daw = isset($myvalues['map_daw']) ? $myvalues['map_daw'] : [];
            $map_delegate_owner = isset($myvalues['map_delegate_owner']) ? $myvalues['map_delegate_owner'] : [];
            
            if($formMode == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('workitem_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $workitemid = $myvalues['id'];
                    $connection_infobundle = $this->getInUseDetails($workitemid);
                    if($connection_infobundle['critical_connections_found'])
                    {
                        $connection_details = $connection_infobundle['details'];
                        $total_pofg = count($connection_details['ddw_list']);
                        $total_poft = count($connection_details['parent_of_tasks_list']);
                        $total_cofg = count($connection_details['daw_list']);
                        $help_detail = "(parent of $total_pofg goals, parent of $total_poft tasks, child of $total_cofg goals)";
                        $is_parent_count = $total_pofg + $total_poft;
                        if($is_parent_count > 0)
                        {
                            form_set_error('workitem_nm',"Cannot delete because critical connections were found. $help_detail");
                            $bGood = FALSE;
                        }
                    }
                }
            }
            
            if(is_array($map_ddw) && count($map_ddw) > 0)
            {
                $sibling_ids = $this->m_oMapHelper->getChildWIDSofWorkitemsByID($map_ddw);
            } else {
                $sibling_ids = [];
            }
            if(trim($myvalues['workitem_nm']) == '')
            {
                form_set_error('workitem_nm','The workitem name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    $status_cd = $myvalues['status_cd'];
                    if(!empty($status_cd))
                    {
                        $status_lookup = $this->m_oMapHelper->getWorkitemStatusByCode(TRUE);
                        $status_info = $status_lookup[$status_cd];
                        if(!empty($status_info['needstesting_yn']) && $status_info['needstesting_yn'] == 1)
                        {
                            if(empty($myvalues['tester_personid']))
                            {
                                $wordy_status_state = $status_info['wordy_status_state'];
                                form_set_error('tester_personid', "A tester selection is required when status code is '$wordy_status_state'");
                                $bGood = FALSE; 
                            }
                        }
                        if(!empty($status_info['terminal_yn']) && $status_info['terminal_yn'] == 1)
                        {
                            if(is_numeric($myvalues['remaining_effort_hours']))
                            {
                                $remaining_effort_hours = $myvalues['remaining_effort_hours'];
                                if($remaining_effort_hours > 0)
                                {
                                    form_set_error('remaining_effort_hours','The remaining effort hours must be ZERO for completed work!');
                                    $bGood = FALSE;
                                }
                            }
                        }
                    }
                    
                    $badthings = TextHelper::getBasicNamingErrors($myvalues['workitem_nm']);
                    foreach($badthings as $onebad)
                    {
                        form_set_error('workitem_nm', 'The name "' . $myvalues['workitem_nm'] . '"' . $onebad);
                        $bGood = FALSE;
                    }

                    if($myvalues['branch_effort_hours_est'] != NULL && !is_numeric($myvalues['branch_effort_hours_est']))
                    {
                        form_set_error('branch_effort_hours_est','The estimated effort hours must be numeric');
                        $bGood = FALSE;
                    } else {
                        $branch_effort_hours_est = $myvalues['branch_effort_hours_est'];
                    }
                    if($myvalues['branch_effort_hours_est_p'] != NULL)
                    {
                        if(!is_numeric($myvalues['branch_effort_hours_est_p']))
                        {
                            form_set_error('branch_effort_hours_est_p','The branch estimated effort hours confidence must be numeric');
                            $bGood = FALSE;
                        }
                        if($myvalues['branch_effort_hours_est_p'] < 0 || $myvalues['branch_effort_hours_est_p'] > 1)
                        {
                            form_set_error('branch_effort_hours_est_p','The branch estimated effort hours confidence must be in range [0,1]');
                            $bGood = FALSE;
                        }
                    }

                    if($myvalues['effort_hours_est'] != NULL && !is_numeric($myvalues['effort_hours_est']))
                    {
                        form_set_error('effort_hours_est','The estimated effort hours must be numeric');
                        $bGood = FALSE;
                    } else {
                        $effort_hours_est = $myvalues['effort_hours_est'];
                    }
                    if($myvalues['effort_hours_est_p'] != NULL)
                    {
                        if(!is_numeric($myvalues['effort_hours_est_p']))
                        {
                            form_set_error('effort_hours_est_p','The estimated effort hours confidence must be numeric');
                            $bGood = FALSE;
                        }
                        if($myvalues['effort_hours_est_p'] < 0 || $myvalues['effort_hours_est_p'] > 1)
                        {
                            form_set_error('effort_hours_est_p','The estimated effort hours confidence must be in range [0,1]');
                            $bGood = FALSE;
                        }
                    }
                    
                    if($myvalues['effort_hours_worked_est'] != NULL && !is_numeric($myvalues['effort_hours_worked_est']))
                    {
                        form_set_error('effort_hours_worked_est','The estimated worked effort hours must be numeric');
                        $bGood = FALSE;
                    }
                    if($myvalues['effort_hours_worked_act'] != NULL && !is_numeric($myvalues['effort_hours_worked_act']))
                    {
                        form_set_error('effort_hours_worked_act','The actual worked effort hours must be numeric');
                        $bGood = FALSE;
                    }
                    if($myvalues['remaining_effort_hours'] != NULL && !is_numeric($myvalues['remaining_effort_hours']))
                    {
                        form_set_error('remaining_effort_hours','The remaining effort hours must be numeric');
                        $bGood = FALSE;
                    } else {
                        $remaining_effort_hours = $myvalues['remaining_effort_hours'];
                    }                

                    //Check some relationships
                    if($bGood)
                    {
                        $limit_branch_effort_hours_cd = $myvalues['limit_branch_effort_hours_cd'];
                        if($limit_branch_effort_hours_cd == 'L')
                        {
                            if($branch_effort_hours_est < $effort_hours_est)
                            {
                                form_set_error('branch_effort_hours_est',"The direct estimated effort hours ($effort_hours_est) cannot exceed the estimated total hours expected for the entire branch of work ($branch_effort_hours_est)!");
                                $bGood = FALSE;
                            }
                            if($branch_effort_hours_est < $remaining_effort_hours)
                            {
                                form_set_error('branch_effort_hours_est',"The remaining effort hours ($remaining_effort_hours) cannot exceed the estimated total hours expected for the entire branch of work ($branch_effort_hours_est)!");
                                $bGood = FALSE;
                            }
                        }
                    }
                }
            }
            
            if(isset($myvalues['map_tag2workitem_tx']))
            {
                $map_tag2workitem_ar = explode(',',$myvalues['map_tag2workitem_tx']);
                foreach($map_tag2workitem_ar as $tag_tx)
                {
                    $clean_tag_tx = strtoupper(trim($tag_tx));
                    $clean_tag_len = (strlen($clean_tag_tx));
                    $over = $clean_tag_len - 20;
                    if($over > 0)
                    {
                        form_set_error('map_tag2workitem_tx', "Tag '$clean_tag_tx' is too big by $over chars (limit is 20 chars)");
                        $bGood = FALSE;
                        break;
                    }
                }
            }
             
            //Done with all validations.
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectRoleOptions()
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getRolesByID($this->m_projectid);
            $parent_options = array();
            $include_inactive = FALSE;
            foreach($all as $id=>$record)
            {
                if($include_inactive || $record['active_yn'] == 1)
                {
                    $workitem_nm = $record['role_nm'];
                    $parent_options[$id] = $workitem_nm;
                }
            }
            return $parent_options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getWorkitemTesterOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateWorkitemTesters($this->m_projectid);
            foreach($all as $code=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$code] = $title_tx;
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

    public function getWorkitemOwnerOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateWorkitemOwners($this->m_projectid);
            foreach($all as $code=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$code] = $title_tx;
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

    public function getWorkitemDelegateOwnerOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateWorkitemOwners($this->m_projectid);
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
    
    public function getWorkitemOptions($skip_this_workitem_nm='',$include_inactive=TRUE,$only_in_tree=TRUE)
    {
        try
        {
            
            $only_active=!$include_inactive;
            if($include_inactive)
            {
                $active_yn = NULL;
            } else {
                $active_yn = $only_active ? 1 : 0;
            }
            $all = $this->m_oMapHelper->getBareWorkitemsByID($this->m_projectid, NULL, $active_yn);
            //$all = $this->m_oMapHelper->getWorkitemsInEntireProjectTreeByID($this->m_projectid,$only_active,$only_in_tree);
            $parent_options = array();
            foreach($all as $id=>$record)
            {
                if($include_inactive || $record['active_yn'] == 1)
                {
                    $workitem_nm = $record['workitem_nm'];
                    if($workitem_nm != $skip_this_workitem_nm)
                    {
                        if($record['active_yn'] == 1)
                        {
                            $parent_options[$id] = "$workitem_nm (#{$id})";
                        } else {
                            $parent_options[$id] = "INACTIVE - $workitem_nm (#{$id})";
                        }
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
    
    public function getWorkitemStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getWorkitemStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getWorkitemBasetypeOptions()
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $myoptions['G'] = "Goal";
            $myoptions['T'] = "Task";
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getBranchEffortHoursTypeOptions()
    {
        try
        {
            $options = [];
            $options['I'] = 'Ignored';
            $options['U'] = 'Unlocked (a fluid value)';
            $options['L'] = 'Locked (a firm value)';
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getResourceTypeOptions()
    {
        try
        {
            $options = [];
            $options['internal'] = 'Internal resource';
            $options['extrc'] = 'External resource';
            $options['equip'] = 'Non-human';
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getEquipmentOptions()
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getEquipmentByID();
            $options = array();
            $options[0] = "- Select -";
            $include_inactive = FALSE;
            foreach($all as $id=>$record)
            {
                if($include_inactive || $record['active_yn'] == 1)
                {
                    $showname = $record['name'];
                    $options[$id] = $showname;
                }
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getExternalResourceOptions()
    {
        try
        {
            //Get all the relevant select options
            $all = $this->m_oMapHelper->getExternalResourceByID();
            $options = array();
            $options[0] = "- Select -";
            $include_inactive = FALSE;
            foreach($all as $id=>$record)
            {
                if($include_inactive || $record['active_yn'] == 1)
                {
                    $showname = $record['name'];
                    $options[$id] = $showname;
                }
            }
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return DRUPAL form API of the context dashboard for a goal
     */
    public function getContextDashboardElements($workitemid)
    {
        $myvalues = $this->getFieldValues($workitemid);
        $elements = array();
        $elements['dashboard'] = array(
            '#type' => 'item',
            '#prefix' => '<table class="context-dashboard">',
            '#suffix' => '</table>',
            '#tree' => TRUE
        );
        $owner_personid = $myvalues["owner_personid"];
        $uah = new UserAccountHelper();
        $ownername = $uah->getExistingPersonFullName($owner_personid);
        //$ownername = $myvalues['first_nm'] . " " . $myvalues['last_nm'];
        $active_yn_markup = $myvalues['active_yn'] == 1 ? '<span>Yes</span>' : '<span class="colorful-no">No</span>';
        if(empty($myvalues['root_of_projectid']))
        {
            $is_project_root_yn_markup = '<span class="projectroot-no">No</span>';
        } else {
            $is_project_root_yn_markup = '<span class="projectroot-yes" title="P#' . $myvalues['root_of_projectid'] . '">Yes</span>';
        }
        //$is_project_root_yn_markup = $myvalues['is_project_root_yn'] == 1 ? '<span class="projectroot-yes">Yes</span>' : '<span class="projectroot-no">No</span>';
        $externally_billable_yn_markup = $myvalues['externally_billable_yn'] == 1 ? '<span class="billable-yes">Yes</span>' : '<span class="billable-no">No</span>';
        $client_deliverable_yn_markup = $myvalues['client_deliverable_yn'] == 1 ? '<span class="deliverable-yes">Yes</span>' : '<span class="deliverable-no">No</span>';
        $planned_fte_count = $myvalues['planned_fte_count'];
        $purpose_tx = $myvalues['purpose_tx'];
        if(isset($myvalues['map_tag2workitem']))
        {
            $tags_tx = implode(', ', $myvalues['map_tag2workitem']);
        } else {
            $tags_tx = '';
        }
        if($myvalues['workitem_basetype'] == 'G')
        {
            $basetypelabel = 'Goal';    
        } else {
            $basetypelabel = 'Task';    
        }
        $status_cd = $myvalues['status_cd'];
        $status_lookup = $this->m_oMapHelper->getWorkitemStatusByCode(TRUE);
        $status_info = $status_lookup[$status_cd];
        $status_wordy_status_state = $status_info['wordy_status_state'];
        $status_description = $status_info['description_tx'];
        $elements['dashboard']['details']['row1'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td width='180px'><label for='goalname'>$basetypelabel Name</label></td>"
                . "<td colspan=13><span id='goalname' title='#$workitemid'>{$myvalues['workitem_nm']}</span></td>"
                . "<td><label for='isactive' title='Setting active to No is a type of soft delete'>Is Active</label></td>"
                . "<td><span id='isactive'>{$active_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label for='goalpurpose'>Purpose</label></td>"
                . "<td colspan=15><span id='goalpurpose'>{$purpose_tx}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='statuscode'>Status</label></td>"
                . "<td colspan=1><span id='statuscode' title='$status_description'>{$status_cd} - {$status_wordy_status_state}</span></td>"
                . "<td width='180px' colspan=1><label>Owner</label></td>"
                . "<td colspan=13><span title='#{$owner_personid}'>$ownername</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row3'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label for='importance' title='Values in range of [75,100] are important activities which have been delayed too long; [50,74] are important and still categorized as on time.  Lower values are not categorized as important.'>Importance</label></td>"
                . "<td colspan=1><span id='importance'>{$myvalues['importance']}</span></td>"
                . "<td><label for='effort_hours_est' title='Estimated number of hours effort required in total by this goal'>Estimated Effort</label></td>"
                . "<td><span id='effort_hours_est'>{$myvalues['effort_hours_est']}</span></td>"
                . "<td><label for='effort_hours_est_p' title='Confidence of the estimate expressed as a probablity in range [0,1]'>Estimate Confidence</label></td>"
                . "<td><span id='effort_hours_est_p'>{$myvalues['effort_hours_est_p']}</span></td>"
                . "<td><label for='effort_hours_worked_act' title='Actual number of hours effort that were invested in this workitem to complete it'>Actual Effort</label></td>"
                . "<td><span id='effort_hours_worked_act'>{$myvalues['effort_hours_worked_act']}</span></td>"
                . "<td><label for='planned_fte_count' title='Expected number of full-time-equivalents working on this item'>Declared FTE Count</label></td>"
                . "<td><span id='planned_fte_count'>{$myvalues['planned_fte_count']}</span></td>"
                . "<td colspan='6'></td>"
                . "</tr>");
        $elements['dashboard']['details']['row4'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label title='There are one or more external recipients of goal work product(s)'>Has Client Deliverable</label></td>"
                . "<td><span >{$client_deliverable_yn_markup}</span></td>"
                . "<td><label title='Is this goal also the root of a project?'>Is Project Root</label></td>"
                . "<td colspan=12><span >{$is_project_root_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row4b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='tags' title='Text labels associated with this goal'>Tags</label></td>"
                . "<td colspan=15><span id='tags'>{$tags_tx}</span></td>"
                . "</tr>");
        return $elements;    
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

            $thelimit = BIGFATHOM_MAX_WORKITEMS_IN_PROJECT;
            if($formType=='A' && $this->m_oMapHelper->getCountWorkitemsInProject($this->m_projectid) >= $thelimit)
            {
                drupal_set_message("Cannot add another workitem to this project because it already has the configuration allowed limit of $thelimit per project",'error');
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
            if(isset($myvalues['workitem_nm']))
            {
                $workitem_nm = $myvalues['workitem_nm'];
            } else {
                $workitem_nm = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['tester_personid']))
            {
                $tester_personid = $myvalues['tester_personid'];
            } else {
                $tester_personid = NULL;
            }
            
            if(isset($myvalues['planned_fte_count']))
            {
                $planned_fte_count = $myvalues['planned_fte_count'];
            } else {
                $planned_fte_count = 1;
            }
            
                       
            if(isset($myvalues['branch_effort_hours_est']))
            {
                $branch_effort_hours_est = $myvalues['branch_effort_hours_est'];
            } else {
                $branch_effort_hours_est = '';
            }
            if(isset($myvalues['branch_effort_hours_est_p']))
            {
                $branch_effort_hours_est_p = $myvalues['branch_effort_hours_est_p'];
            } else {
                $branch_effort_hours_est_p = '';
            }
            if(isset($myvalues['limit_branch_effort_hours_cd']))
            {
                $limit_branch_effort_hours_cd = $myvalues['limit_branch_effort_hours_cd'];
            } else {
                $limit_branch_effort_hours_cd = '';
            }
            
            if(isset($myvalues['effort_hours_est']))
            {
                $effort_hours_est = $myvalues['effort_hours_est'];
            } else {
                $effort_hours_est = '';
            }
            if(isset($myvalues['effort_hours_est_p']))
            {
                $effort_hours_est_p = $myvalues['effort_hours_est_p'];
            } else {
                $effort_hours_est_p = '';
            }
            if(isset($myvalues['effort_hours_worked_est']))
            {
                $effort_hours_worked_est = $myvalues['effort_hours_worked_est'];
            } else {
                $effort_hours_worked_est = '';
            }
            if(isset($myvalues['effort_hours_worked_act']))
            {
                $effort_hours_worked_act = $myvalues['effort_hours_worked_act'];
            } else {
                $effort_hours_worked_act = '';
            }
            if(isset($myvalues['remaining_effort_hours']))
            {
                $remaining_effort_hours = $myvalues['remaining_effort_hours'];
            } else {
                $remaining_effort_hours = '';
            }
            
            if(!empty($myvalues['planned_start_dt']))
            {
                    $planned_start_dt = $myvalues['planned_start_dt'];
            } else {
                    $planned_start_dt = '';
            }

            if(!empty($myvalues['planned_start_dt_locked_yn']))
            {
                    $planned_start_dt_locked_yn = $myvalues['planned_start_dt_locked_yn'];
            } else {
                    $planned_start_dt_locked_yn = 0;
            }

            if(!empty($myvalues['actual_start_dt']))
            {
                    $actual_start_dt = $myvalues['actual_start_dt'];
            } else {
                    $actual_start_dt = '';
            }

            if(!empty($myvalues['planned_end_dt']))
            {
                    $planned_end_dt = $myvalues['planned_end_dt'];
            } else {
                    $planned_end_dt = '';
            }

            if(!empty($myvalues['planned_end_dt_locked_yn']))
            {
                    $planned_end_dt_locked_yn = $myvalues['planned_end_dt_locked_yn'];
            } else {
                    $planned_end_dt_locked_yn = 0;
            }

            if(!empty($myvalues['actual_end_dt']))
            {
                    $actual_end_dt = $myvalues['actual_end_dt'];
            } else {
                    $actual_end_dt = '';
            }
            
            if(isset($myvalues['status_cd']))
            {
                $status_cd = $myvalues['status_cd'];
            } else {
                $status_cd = 'WNS';
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 70;
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            
            if(isset($myvalues['parent_goal_id']))
            {
                $parent_goal_id = $myvalues['parent_goal_id'];
            } else {
                $parent_goal_id = '';
            }
            
            if(isset($myvalues['workitem_basetype']))
            {
                $workitem_basetype = $myvalues['workitem_basetype'];
            } else {
                if(isset($myvalues['default_basetype']))
                {
                    $workitem_basetype = $myvalues['default_basetype'];
                } else {
                    $workitem_basetype = 'G';
                }
            }

            if(isset($myvalues['equipmentid']))
            {
                $equipmentid = $myvalues['equipmentid'];
            } else {
                $equipmentid = NULL;
            }
            if(isset($myvalues['external_resourceid']))
            {
                $external_resourceid = $myvalues['external_resourceid'];
            } else {
                $external_resourceid = NULL;
            }
            if(empty($equipmentid) && empty($external_resourceid))
            {
                $task_resource_type = 'internal';
            } else
            if(!empty($equipmentid))
            {
                $task_resource_type = 'equip';
            } else
            if(!empty($external_resourceid))
            {
                $task_resource_type = 'extrc';
            }
            if(isset($myvalues['chargecode']))
            {
                $chargecode = $myvalues['chargecode'];
            } else {
                $chargecode = '';
            } 
            
            $include_inactive_goals = ($formType != 'A');
            $options_ddw = $this->getWorkitemOptions($workitem_nm,$include_inactive_goals);
            $options_projectroles = $this->getProjectRoleOptions();
            $options_workitem_status = $this->getWorkitemStatusOptions();
            $options_delegate_owner = $this->getWorkitemDelegateOwnerOptions();
            $options_workitem_owner = $this->getWorkitemOwnerOptions();
            $options_workitem_tester = $this->getWorkitemTesterOptions();
            $options_workitem_basetype = $this->getWorkitemBasetypeOptions();
            $options_limit_branch_effort_hours_cd = $this->getBranchEffortHoursTypeOptions();
            $options_task_resource_type = $this->getResourceTypeOptions();
            $options_equipment = $this->getEquipmentOptions();
            $options_external_resource = $this->getExternalResourceOptions();
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );

            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_workitem_nm'] 
                = array('#type' => 'hidden', '#value' => $workitem_nm, '#disabled' => FALSE);        
            
            $showcolname_importance = 'importance';
            $showcolname_workitem_nm = 'workitem_nm';
            $disable_workitem_nm = $disabled;       //Default behavior
            if($disable_workitem_nm)
            {
                $form['hiddenthings']['workitem_nm'] 
                    = array('#type' => 'hidden', '#value' => $workitem_nm, '#disabled' => FALSE);        
                $showcolname_workitem_nm = 'show_workitem_nm';
            }
            
            if(isset($myvalues['map_ddw']))
            {
                $default_ddw_list = $myvalues['map_ddw'];
            } else {
                $default_ddw_list = [];
            }
            
            if(isset($myvalues['map_daw']))
            {
                $default_daw_list = $myvalues['map_daw'];
            } else {
                $default_daw_list = [];
            }
            $form['hiddenthings']['map_daw'] 
                = array('#type' => 'hidden', '#value' => $default_daw_list, '#disabled' => FALSE);        
            $form['hiddenthings']['original_map_ddw'] 
                = array('#type' => 'hidden', '#value' => $default_ddw_list, '#disabled' => FALSE);        
            
            $form['data_entry_area1'][$showcolname_workitem_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Workitem Name'),
                '#default_value' => $workitem_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The name for this workitem'),
                '#disabled' => $disable_workitem_nm
            );
            
            $form['data_entry_area1']['workitem_basetype'] = array(
                '#type' => 'select',
                '#title' => t('Base Type'),
                '#default_value' => $workitem_basetype,
                '#options' => $options_workitem_basetype,
                '#required' => TRUE,
                '#description' => t('The basetype of this workitem'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1'][$showcolname_importance] = array(
                '#type' => 'textfield',
                '#title' => t('Importance'),
                '#default_value' => $importance,
                '#size' => 3,
                '#maxlength' => 3,
                '#required' => TRUE,
                '#description' => t('Current importance for completing this workitem.  Scale is [0,100] with 0 being no importance whatsoever and 100 being nothing is more important.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_workitem_status,
                '#required' => TRUE,
                '#description' => t('The current status of this workitem'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Purpose Text'),
                '#default_value' => $purpose_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('Explanation of the workitem purpose'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['planned_fte_count'] = array(
                '#type' => 'textfield',
                '#title' => t('Planned FTE Count'),
                '#default_value' => $planned_fte_count,
                '#size' => 4,
                '#maxlength' => 5,
                '#required' => TRUE,
                '#description' => t('Planned number full time equivalents working on this item.'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_hours_area'] = array(
                '#type' => 'fieldset',
                '#title' => t('Effort Hours'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['effort_hours_area']['future'] = array(
                '#type' => 'fieldset',
                '#title' => t('Future Looking Effort Predictions'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['branch'] = array(
                '#type' => 'fieldset',
                '#title' => t('Branch Totals'),
                '#description' => t('A branch is the tree where this workitem is at the root'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
                //'#states' => array(
                //    'visible' => array(
                //      ':input[name="workitem_basetype"]' => array('value' => 'G'),
                //    ),
                //),
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['branch']['branch_effort_hours_est'] = array(
                '#type' => 'textfield',
                '#title' => t('Branch Estimated Effort'),
                '#default_value' => $branch_effort_hours_est,
                '#size' => 8,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Total hours estimated for completion of this workitem including all time spent on antecedent workitems'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['branch']['branch_effort_hours_est_p'] = array(
                '#type' => 'textfield',
                '#title' => t('Branch Estimated Effort Confidence'),
                '#default_value' => $branch_effort_hours_est_p,
                '#size' => 6,
                '#maxlength' => 6,
                '#required' => FALSE,
                '#description' => t('Confidence in the branch hours estimated for completion of this workitem as a probability in range [0.00,1.00]'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['effort_hours_area']['future']['branch']['limit_branch_effort_hours_cd'] = array(
                '#type' => 'select',
                '#title' => t('Branch Effort Type'),
                '#default_value' => $limit_branch_effort_hours_cd,
                '#options' => $options_limit_branch_effort_hours_cd,
                '#required' => TRUE,
                '#description' => t('Effect of the branch effort estimate field, if any.'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['direct'] = array(
                '#type' => 'fieldset',
                '#title' => t('Direct Totals'),
                '#description' => t('Effort hour predictions that are only for the effort on this workitem not including the effort, if any, to complete antecedent workitems.'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['direct']['remaining_effort_hours'] = array(
                '#type' => 'textfield',
                '#title' => t('Direct Estimated Remaining Effort Hours'),
                '#default_value' => $remaining_effort_hours,
                '#size' => 8,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Declared hours of effort remaining to complete the workitem not counting time spent on antecedent workitems.  This value should be periodically updated to always reflect the latest thinking on how much effort remains to complete the workitem.'),
                '#prefix' => "<div class='highlighted-form-input-area'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );        
            
            $form['data_entry_area1']['effort_hours_area']['future']['direct']['effort_hours_est'] = array(
                '#type' => 'textfield',
                '#title' => t('Initial Direct Estimated Effort Total from Start to Finish'),
                '#default_value' => $effort_hours_est,
                '#size' => 8,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Hours estimated from original start date to finish date for completion of this workitem not counting time needed for completing antecedent workitems.  This is intended to be a static value that can be examined at project end to see how accurate the original estimate was.'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_hours_area']['future']['direct']['effort_hours_est_p'] = array(
                '#type' => 'textfield',
                '#title' => t('Confidence of Initial Direct Estimated Effort Total'),
                '#default_value' => $effort_hours_est_p,
                '#size' => 6,
                '#maxlength' => 6,
                '#required' => FALSE,
                '#description' => t('Confidence in the direct start to finish total hours estimated for completion of this workitem as a probability in range [0.00,1.00]'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );
            
            
            $form['data_entry_area1']['effort_hours_area']['past'] = array(
                '#type' => 'fieldset',
                '#title' => t('Completed Direct Effort'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['effort_hours_area']['past']['effort_hours_worked_est'] = array(
                '#type' => 'textfield',
                '#title' => t('Direct Worked Effort Estimate'),
                '#default_value' => $effort_hours_worked_est,
                '#size' => 8,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Estimated number of hours expended on this workitem not counting time spent on antecedent workitems'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_hours_area']['past']['effort_hours_worked_act'] = array(
                '#type' => 'textfield',
                '#title' => t('Direct Worked Effort Actual'),
                '#default_value' => $effort_hours_worked_act,
                '#size' => 8,
                '#maxlength' => 8,
                '#required' => FALSE,
                '#description' => t('Hours actually expended to complete this workitem not counting time spent on antecedent workitems'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['daterange1'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_row'>",            
                            '#suffix' => "</div>");  
            
            $form['data_entry_area1']['daterange1']['starting_date_info'] = array(
                '#type' => 'fieldset',
                '#title' => t('When the Work Starts'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );

            $form['data_entry_area1']['daterange1']['starting_date_info']['col1'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_col'>",            
                            '#suffix' => "</div>"); 
            
            $form['data_entry_area1']['daterange1']['starting_date_info']['col1']['planned_start_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Planned Start'),
                '#default_value' => $planned_start_dt,
                '#required' => FALSE,
                '#description' => t('Planned date for this work to start'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['daterange1']['starting_date_info']['col1']['planned_start_dt_locked_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Planned Start Date Locked'),
                '#default_value' => isset($myvalues['planned_start_dt_locked_yn']) ? $myvalues['planned_start_dt_locked_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if planned start date should be considered firm; otherwise program is free to recommend adjustments.'),
            );
			
            $form['data_entry_area1']['daterange1']['starting_date_info']['actual_start_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Actual Start'),
                '#default_value' => $actual_start_dt,
                '#required' => FALSE,
                '#description' => t('Actual date on which this work started'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['daterange2'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_row'>",            
                            '#suffix' => "</div>");  
            
            $form['data_entry_area1']['daterange2']['ending_date_info'] = array(
                '#type' => 'fieldset',
                '#title' => t('When the Work Ends'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
			
            $form['data_entry_area1']['daterange2']['ending_date_info']['col1'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_col'>",            
                            '#suffix' => "</div>"); 
            
            $form['data_entry_area1']['daterange2']['ending_date_info']['col1']['planned_end_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Planned End'),
                '#default_value' => $planned_end_dt,
                '#required' => FALSE,
                '#description' => t('Planned date for this work to end'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['daterange2']['ending_date_info']['col1']['planned_end_dt_locked_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Planned End Date Locked'),
                '#default_value' => isset($myvalues['planned_end_dt_locked_yn']) ? $myvalues['planned_end_dt_locked_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if planned end date should be considered firm; otherwise program is free to recommend adjustments.')
            );
			
            $form['data_entry_area1']['daterange2']['ending_date_info']['actual_end_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Actual End'),
                '#default_value' => $actual_end_dt,
                '#required' => FALSE,
                '#description' => t('Actual date on which this work ended'),
                '#prefix' => "<div class='simulate_table_col'>",            
                '#suffix' => "</div>",
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['tasksubtype'] = array(
                '#type' => 'fieldset',
                '#title' => t('Task Options'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
                '#states' => array(
                    'visible' => array(
                      ':input[name="workitem_basetype"]' => array('value' => 'T'),
                    ),
                ),
            );
            
            $form['data_entry_area1']['tasksubtype']['task_resource_type'] = array(
                '#type' => 'select',
                '#title' => t('Task Resource Type'),
                '#default_value' => $task_resource_type,
                '#options' => $options_task_resource_type,
                '#required' => TRUE,
                '#description' => t('What type of resource will execute this task?'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['tasksubtype']['equipmentid'] = array(
                '#type' => 'select',
                '#title' => t('Non-Human Resource'),
                '#default_value' => $equipmentid,
                '#options' => $options_equipment,
                '#required' => FALSE,
                '#description' => t('What is the non-human resource that will execute this task? (e.g., equipment)'),
                '#disabled' => $disabled,
                '#states' => array(
                    'visible'=>array(
                        ':input[name="task_resource_type"]' => array('value' => 'equip'),
                        ),
                    ),
            );

            $form['data_entry_area1']['tasksubtype']['external_resourceid'] = array(
                '#type' => 'select',
                '#title' => t('External Resource'),
                '#default_value' => $external_resourceid,
                '#options' => $options_external_resource,
                '#required' => FALSE,
                '#description' => t('What is the external resource that will carry out this task?'),
                '#disabled' => $disabled,
                '#states' => array(
                    'visible'=>array(
                        ':input[name="task_resource_type"]' => array('value' => 'extrc'),
                        ),
                    ),
            );
            
            if(isset($myvalues['map_prole2workitem']))
            {
                $default_projectroles_list = $myvalues['map_prole2workitem'];
            } else {
                $default_projectroles_list = array();
            }
            
            if(isset($myvalues['map_delegate_owner']))
            {
                $default_delegate_owner_list = $myvalues['map_delegate_owner'];
            } else {
                $default_delegate_owner_list = [];
            }

            $form['data_entry_area1']['map_prole2workitem'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Relevant Workitem Roles'
                    , $default_projectroles_list
                    , $options_projectroles
                    , FALSE
                    , 'Identify roles relevant to the successful achievement of this workitem');
            
            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Workitem Owner'),
                '#default_value' => $owner_personid,
                '#options' => $options_workitem_owner,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful completion of this workitem?'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['map_delegate_owner'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Delegate Owner(s)'
                    , $default_delegate_owner_list
                    , $options_delegate_owner
                    , FALSE
                    , 'Identify other persons that are delegated to also have ownership rights to this workitem');
            
            //TODO remove active_yn as a user input!!!!!!!!!!!!!!!!!!!
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'hidden',
                '#title' => t('Active'),
                '#default_value' => 1, //isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if workitem is active, else no.')
            );
            
            $form['data_entry_area1']['map_ddw'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Directly Dependent Workitems(s)'
                    , $default_ddw_list
                    , $options_ddw
                    , FALSE
                    , 'A dependent workitem is a workitem that depends on successful completion of this workitem for its success'
                );

            $form['data_entry_area1']['tester_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Workitem Tester'),
                '#default_value' => $tester_personid,
                '#options' => $options_workitem_tester,
                '#required' => FALSE,
                '#description' => t('Who is directly responsible for confirming the successful completion of this workitem once it is ready for testing?'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['client_deliverable_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Client Deliverable'),
                '#default_value' => isset($myvalues['client_deliverable_yn']) ? $myvalues['client_deliverable_yn'] : 0,
                '#options' => $ynoptions,
                '#description' => t('Yes if this workitem produces one or more client deliverables, else no.')
            );
            
            $form['data_entry_area1']['externally_billable_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Client Billable'),
                '#default_value' => isset($myvalues['externally_billable_yn']) ? $myvalues['externally_billable_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if this workitem effort can be billed to a client, else no.')
            );
            
            $form['data_entry_area1']['chargecode'] = array(
                '#type' => 'textfield',
                '#title' => "<i class='fa fa-credit-card' aria-hidden='true'></i> " . t('Charge Code'),
                '#default_value' => $chargecode,
                '#size' => 40,
                '#maxlength' => 100,
                '#required' => FALSE,
                '#description' => t('To facilitate associating work with your financial tracking systems'),
                '#disabled' => $disabled
            );
            
            if(isset($myvalues['map_tag2workitem']))
            {
                $default_parent_tag_text = implode(', ', $myvalues['map_tag2workitem']);
            } else {
                $default_parent_tag_text = '';
            }
            $form['data_entry_area1']['map_tag2workitem_tx'] = array(
                '#type' => 'textfield',
                '#title' => "<i class='fa fa-tags' aria-hidden='true'></i> " . t('Tags'),
                '#default_value' => $default_parent_tag_text,
                '#size' => 80,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#description' => t('Optional comma delimited text tags to associate with this workitem'),
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
                
            if(!empty($myvalues['workitemid']))
            {
                $workitemid = $myvalues['workitemid'];
            } else {
                throw new \Exception("Cannot get comment form without a workitemid!");
            }
            $workitem_record = $this->m_oMapHelper->getOneRichWorkitemRecord($workitemid);
            $workitem_basetype = $workitem_record['workitem_basetype'];
            if(!empty($myvalues['status_cd_at_time_of_com']))
            {
                $status_cd_at_time_of_com = $myvalues['status_cd_at_time_of_com'];
            } else {
                $status_cd_at_time_of_com = $workitem_record['status_cd'];
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
            
            if($workitem_basetype == 'G')
            {
                $basetypelabel = "Goal";
            } else {
                $basetypelabel = "Task";
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
            
            $form['hiddenthings']['workitemid'] 
                = array('#type' => 'hidden', '#value' => $workitemid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['status_cd_at_time_of_com'] 
                = array('#type' => 'hidden', '#value' => $status_cd_at_time_of_com, '#disabled' => FALSE); 

            $form['hiddenthings']['updated_dt'] 
                = array('#type' => 'hidden', '#value' => $updated_dt, '#disabled' => FALSE); 

            $form['hiddenthings']['created_dt'] 
                = array('#type' => 'hidden', '#value' => $created_dt, '#disabled' => FALSE); 
            
            $dashboard = $this->getContextDashboardElements($workitemid);
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
                $parent_comment_record = $this->m_oMapHelper->getOneWorkitemCommunication($parent_comid);
                $parent_action_requested_concern = $parent_comment_record['action_requested_concern'];
                $parent_requests_action = (!empty($parent_action_requested_concern) && $parent_action_requested_concern > 0);
                drupal_set_title("$ftword a Reply to $basetypelabel Communication #$parent_comid");
            } else {
                $parent_comment_record = NULL;
                $parent_requests_action = FALSE;
                $parent_action_requested_concern = 0;
                drupal_set_title("$ftword a Root $basetypelabel Comment");
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
                $parent_comment_markup .= "<li>Message: <span class='comment-blurb'>".$parent_body_tx."</span>";
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
