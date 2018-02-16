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
 * Help with Projects
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oWriteHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    protected $m_root_goalid = NULL;
    protected $m_default_parent_projectid = NULL;
    protected $m_default_parent_projectinfo = NULL;
    protected $m_is_toplevel_project = NULL;
    protected $m_oUAH = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $default_parent_projectid=NULL, $this_projectid=NULL, $root_goalid=NULL)
    {
        try
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            $this->m_root_goalid = $root_goalid;
            $this->m_projectid = $this_projectid;
            if($this_projectid != NULL)
            {
                //Compute the parent from the provided projectid
                $this->m_default_parent_projectid = NULL;   //TODO
            } else {
                //Use this as the filter.
                $this->m_default_parent_projectid = $default_parent_projectid;
            }
            $this->m_is_toplevel_project = ($default_parent_projectid == NULL);
            if(!$this->m_is_toplevel_project)
            {
                //This is a subproject; get the parent details
                $this->m_default_parent_projectinfo = $this->m_oContext->getProjectInfo($this->m_default_parent_projectid);
            }
            $this->m_root_goalid = $root_goalid;

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
            $this->m_all_pcbyid = $this->m_oMapHelper->getProjectContextsByID(NULL, FALSE, FALSE);

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getInUseDetails($projectid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfProject($projectid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function addToCheckboxArrayIfActive(&$allow_publish_items,$myvalues,$key)
    {
        if(isset($myvalues[$key]) && $myvalues[$key] == 1)
        {
            $allow_publish_items[$key] = $key;
        }
    }
    
    public function createProjectCommunication($myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createProjectCommunication($myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function deleteProjectCommunication($matchcomid, $uid)
    {
        try
        {
            return $this->m_oWriteHelper->deleteProjectCommunication($matchcomid, $uid);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function updateProjectCommunication($matchcomid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateProjectCommunication($matchcomid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_projectid;
                $myvalues['parent_projectid'] = $this->m_default_parent_projectid;
            } else {
                //TODO -- lookup parent for the provided projectid
                $myvalues['parent_projectid'] = NULL;
            }
            $myvalues['root_goalid'] = $this->m_root_goalid;
            if(!empty($projectid))
            {
                //Get the core values 
                $myvalues = $this->m_oMapHelper->getOneProjectDetailData($projectid);
                $myvalues['map_group2project'] = $this->m_oMapHelper->getIDListOfGroupsInProject($projectid);
                $myvalues['map_role2project'] = $this->m_oMapHelper->getIDListOfRolesInProject($projectid);

                $allow_publish_items = [];
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_owner_name_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_onbudget_p_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_actual_start_dt_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_actual_end_dt_yn');
                $myvalues['allow_publish_items'] = $allow_publish_items;
                
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['root_workitem_nm'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['project_contextid'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['mission_tx'] = NULL;
                $myvalues['map_group2project'] = NULL;
                $myvalues['map_role2project'] = NULL;
                
                $myvalues['surrogate_yn'] = NULL;
                $myvalues['source_type'] = NULL;
                
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
     * Get the values to populate the publication form.
     */
    function getPublicationFieldValues($projectid=NULL, $pubid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_projectid;
                $myvalues['parent_projectid'] = $this->m_default_parent_projectid;
            } else {
                //TODO -- lookup parent for the provided projectid
                $myvalues['parent_projectid'] = NULL;
            }
            $myvalues['root_goalid'] = $this->m_root_goalid;
            if(!empty($projectid))
            {
                //Get the core values 
                $myvalues = $this->m_oMapHelper->getOneProjectDetailData($projectid);
                $myvalues['projectid'] = $projectid;
                $myvalues['pubid'] = $pubid;
                $myvalues['map_group2project'] = $this->m_oMapHelper->getIDListOfGroupsInProject($projectid);
                $myvalues['map_role2project'] = $this->m_oMapHelper->getIDListOfRolesInProject($projectid);
                
                $fnug_content = $this->m_oMapHelper->getForecastNuggetsMapBundle($projectid);
                $ontime_p = $fnug_content['by_projectid'][$projectid]['root_otsp']['value'];
                if(empty($ontime_p))
                {
                    $ontime_p = 0;
                    $ontime_u = .01;
                } else {
                    $ontime_p = round($ontime_p,4);
                    $ontime_u = .01;
                }
                //DebugHelper::showNeatMarkup($fnug_content,"LOOK fnug_content fpr proj#$projectid ontime_p=[$ontime_p]!!!!!!!!!!!");   
                $onbudget_p = "";  //TODO COMPUTE!!!!!
                $onbudget_u = "";  //TODO COMPUTE!!!!!

                $allow_publish_items = [];
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_project_name_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_status_cd_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_project_contextid_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_mission_tx_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_ontime_p_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_planned_start_dt_yn');
                //$this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_planned_end_dt_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_owner_name_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_onbudget_p_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_actual_start_dt_yn');
                $this->addToCheckboxArrayIfActive($allow_publish_items,$myvalues,'allow_publish_item_actual_end_dt_yn');
                $myvalues['allow_publish_items'] = $allow_publish_items;

                $comment_tx = '';
                
                $myvalues["pub_id"] = $pubid;
                $myvalues["pub_publishedrefname"] = $myvalues['publishedrefname'];
                $myvalues["pub_project_contextid"] = $myvalues['project_contextid'];
                $myvalues["pub_project_nm"] = $myvalues['root_workitem_nm'];
                $myvalues["pub_projectid"] = $projectid;
                $myvalues["pub_root_goalid"] = $myvalues['root_goalid'];
                $myvalues["pub_mission_tx"] = $myvalues['mission_tx'];
                $myvalues["pub_owner_personid"] = $myvalues['owner_personid'];
                $myvalues["pub_planned_start_dt"] = $myvalues['planned_start_dt'];
                $myvalues["pub_actual_start_dt"] = $myvalues['actual_start_dt'];
                $myvalues["pub_planned_end_dt"] = $myvalues['planned_end_dt'];
                $myvalues["pub_actual_end_dt"] = $myvalues['actual_end_dt'];
                $myvalues["pub_onbudget_p"] = $onbudget_p;
                $myvalues["pub_onbudget_u"] = $onbudget_u;
                $myvalues["pub_ontime_p"] = $ontime_p;
                $myvalues["pub_ontime_u"] = $ontime_u;
                $myvalues["pub_comment_tx"] = $comment_tx;
                $myvalues["pub_status_cd"] = $myvalues['status_cd'];
                $myvalues["pub_status_set_dt"] = $myvalues['status_set_dt'];
                $myvalues["pub_updated_dt"] = $myvalues['updated_dt'];
                $myvalues["pub_created_dt"] = $myvalues['created_dt'];

            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['root_workitem_nm'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['project_contextid'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['mission_tx'] = NULL;
                $myvalues['map_group2project'] = NULL;
                $myvalues['map_role2project'] = NULL;
                
                $myvalues['surrogate_yn'] = NULL;
                $myvalues['source_type'] = NULL;
                
                $myvalues['planned_start_dt'] = NULL;
                $myvalues['planned_start_dt_locked_yn'] = 0;
                $myvalues['actual_start_dt'] = NULL;
                $myvalues['planned_end_dt'] = NULL;
                $myvalues['planned_end_dt_locked_yn'] = 0;
                $myvalues['actual_end_dt'] = NULL;

                $myvalues["pub_id"] = NULL;
                $myvalues["pub_publishedrefname"] = NULL;
                $myvalues["pub_project_contextid"] = NULL;
                $myvalues["pub_project_nm"] = NULL;
                $myvalues["pub_projectid"] = NULL;
                $myvalues["pub_root_goalid"] = NULL;
                $myvalues["pub_mission_tx"] = NULL;
                $myvalues["pub_owner_personid"] = NULL;
                $myvalues["pub_planned_start_dt"] = NULL;
                $myvalues["pub_actual_start_dt"] = NULL;
                $myvalues["pub_planned_end_dt"] = NULL;
                $myvalues["pub_actual_end_dt"] = NULL;
                $myvalues["pub_onbudget_p"] = NULL;
                $myvalues["pub_onbudget_u"] = NULL;
                $myvalues["pub_ontime_p"] = NULL;
                $myvalues["pub_ontime_u"] = NULL;
                $myvalues["pub_comment_tx"] = NULL;
                $myvalues["pub_status_cd"] = NULL;
                $myvalues["pub_status_set_dt"] = NULL;
                $myvalues["pub_updated_dt"] = NULL;
                $myvalues["pub_created_dt"] = NULL;
                
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getCommentFieldValues($comid=NULL,$parent_comid=NULL,$projectid=NULL)
    {
        try
        {
            $myvalues['projectid'] = $this->m_projectid;
            if(!empty($comid))
            {
                $myvalues = $this->m_oMapHelper->getOneProjectCommunication($comid);
                $myvalues['original_owner_personid'] = $myvalues['owner_personid'];
                $myvalues['original_first_nm'] = $myvalues['first_nm'];
                $myvalues['original_last_nm'] = $myvalues['last_nm'];
                $myvalues['original_shortname'] = $myvalues['shortname'];
                $myvalues['original_updated_dt'] = $myvalues['updated_dt'];
                $myvalues['original_created_dt'] = $myvalues['created_dt'];
                $myvalues['edit_history'] = $this->m_oMapHelper->getWorkitemCommunicationHistory($comid);
            } else {
                if(empty($parent_comid) && empty($projectid))
                {
                    throw new \Exception("Cannot get comment fields without at least a projectid!");
                }
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['parent_comid'] = $parent_comid;
                $myvalues['projectid'] = $projectid;
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
    function formIsValid($form, &$myvalues, $formType)
    {
        try
        {
            $bGood = TRUE;
            $this_id = isset($myvalues['id']) ? $myvalues['id'] : NULL;
            $surrogate_yn = isset($myvalues['surrogate_yn']) ? $myvalues['surrogate_yn'] : 0;
            $source_type = isset($myvalues['source_type']) ? $myvalues['source_type'] : 'local';
            $purpose_tx = isset($myvalues['purpose_tx']) ? $myvalues['purpose_tx'] : NULL;
            $publishedrefname = isset($myvalues['publishedrefname']) ? trim($myvalues['publishedrefname']) : NULL;
            $remote_uri = isset($myvalues['remote_uri']) ? trim($myvalues['remote_uri']) : NULL;
            
            if($surrogate_yn != 1 && empty($purpose_tx))
            {
                form_set_error('purpose_tx','Purpose for the root goal must be declared');
                $bGood = FALSE;
            }

            if(empty($publishedrefname) && !empty($remote_uri))
            {
                form_set_error('publishedrefname','A reference name must be provided if you are saving a remote URI');
                $bGood = FALSE;
            }
            
            if($formType == 'D')
            {
                if(!isset($myvalues['id']))
                {
                    form_set_error('mission_tx','Cannot delete without an ID!');
                    $bGood = FALSE;
                } else {
                    $myid = $myvalues['id'];
                    $connection_details = $this->getInUseDetails($myid);
                    if($connection_details['connections_found'])
                    {
                        //TODO --- enhance the message to say what connections
                        form_set_error('project_tx','Cannot delete because connections were found.  Consider marking this project inactive instead.');
                        $bGood = FALSE;
                    }
                }
            }
            if($this->m_default_parent_projectid != NULL)
            {
                //This is a subproject
                $require_root_workitem_nm = FALSE;
                $require_root_goalid = TRUE;
            } else {
                //This is a top-level project
                if($formType == 'A')
                {
                    $require_root_workitem_nm = TRUE;
                    $require_root_goalid = FALSE;
                } else {
                    $require_root_workitem_nm = FALSE;
                    $require_root_goalid = TRUE;
                }
            }
            
            if(!empty($myvalues['publishedrefname']))
            {
                $publishedrefname = trim($myvalues['publishedrefname']);
                if(strlen($publishedrefname)>0)
                {
                    $badthings = TextHelper::getBasicRefNamingErrors($publishedrefname);
                    foreach($badthings as $onebad)
                    {
                        form_set_error('publishedrefname', 'The reference name ' . $onebad);
                        $bGood = FALSE;
                    }
                    $found_pid = $this->m_oMapHelper->getProjectIDFromPublishedRefName($publishedrefname,FALSE);
                    if($formType == 'A')
                    {
                        if(!empty($found_pid))
                        {
                            form_set_error('publishedrefname','The reference name is already in use by project#' . $found_pid);
                            $bGood = FALSE;
                        }
                    } else if($formType == 'E') {
                        if(!empty($found_pid) && $this_id != $found_pid)
                        {
                            form_set_error('publishedrefname','The reference name is already in use by project#' . $found_pid);
                            $bGood = FALSE;
                        }
                    }
                }
            }
            
            if($require_root_workitem_nm)
            {
                if(trim($myvalues['root_workitem_nm']) == '')
                {
                    form_set_error('root_workitem_nm','The root name cannot be empty');
                    $bGood = FALSE;
                } else {
                    $badthings = TextHelper::getBasicNamingErrors($myvalues['root_workitem_nm']);
                    foreach($badthings as $onebad)
                    {
                        form_set_error('root_workitem_nm', 'The name "' . $myvalues['root_workitem_nm'] . '"' . $onebad);
                        $bGood = FALSE;
                    }
                    
                    if($formType == 'A')
                    {
                        //Check for duplicate goal name since we are going to create one
                        $result = db_select(DatabaseNamesHelper::$m_workitem_tablename,'p')
                            ->fields('p')
                            ->condition('workitem_nm', $myvalues['root_workitem_nm'],'=')
                            ->execute();
                        if($result->rowCount() > 0)
                        {
                            $record = $result->fetchAssoc();
                            $found_id = $record['id'];
                            if($found_id != $myvalues['id'])
                            {
                                form_set_error('root_workitem_nm', 'Already have a goal with this name');
                                $bGood = FALSE;
                            }
                        }
                    }
                }
            }
            $root_goalid = !empty($myvalues['root_goalid']) ? $myvalues['root_goalid'] : $myvalues['original_root_goalid'];
            if($require_root_goalid)
            {
                if(empty($root_goalid))
                {
                    form_set_error('root_goalid', 'Must declare a root goal for this project');
                    $bGood = FALSE;
                } else {
                    $detail = $this->m_oMapHelper->getOneRichWorkitemRecord($root_goalid);
                    $already_root_of_projectid = $detail['root_of_projectid'];
                    if($already_root_of_projectid != null && $already_root_of_projectid != $this_id)
                    {
                        form_set_error('root_goalid', 'The selected goal is already the root of project #' . $already_root_of_projectid);
                        $bGood = FALSE;
                    }
                    
                    if($this->m_oMapHelper->checkOutsideNodesDependOnBranch($root_goalid))
                    {
                        form_set_error('root_goalid', 'Projects are connected to one or more members of the selected branch');
                        $bGood = FALSE;
                    }
                }
            }
            
            if(!isset($myvalues['mission_tx']) || trim($myvalues['mission_tx']) == '')
            {
                form_set_error('mission_tx', 'Must provide a mission statement for this project');
                $bGood = FALSE;
            }
            if(!isset($myvalues['owner_personid']) || trim($myvalues['owner_personid']) == '')
            {
                if(!isset($myvalues['surrogate_owner_personid']) || trim($myvalues['surrogate_owner_personid']) == '')
                {
                    form_set_error('owner_personid', 'Must declare a project manager');
                    $bGood = FALSE;
                }
            }

            $d_sdt = !empty($myvalues['actual_start_dt']) ? $myvalues['actual_start_dt'] : !empty($myvalues['planned_start_dt']) ? $myvalues['planned_start_dt'] : NULL;
            $d_edt = !empty($myvalues['actual_end_dt']) ? $myvalues['actual_end_dt'] : !empty($myvalues['planned_end_dt']) ? $myvalues['planned_end_dt'] : NULL;
            
            if(!empty($d_sdt) && !empty($d_edt) && $d_sdt > $d_edt)
            {
                $fname = !empty($myvalues['actual_start_dt']) ? 'planned_start_dt' : 'planned_start_dt';
                form_set_error($fname, 'The start date cannot be later than the end date!');
                $bGood = FALSE;
            }
            
            //Done with all validations.
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Validate the proposed values.
     */
    function formIsValidPublication($form, &$myvalues, $formType)
    {
        try
        {
            $bGood = TRUE;
            
            if(!isset($myvalues['publishedrefname']) || empty(trim($myvalues['publishedrefname'])))
            {
                form_set_error('publishedrefname', 'Must provide a public reference name for this project');
                $bGood = FALSE;
            }
            
            if(!isset($myvalues['mission_tx']) || empty(trim($myvalues['mission_tx'])))
            {
                form_set_error('mission_tx', 'Must provide a mission statement for this project');
                $bGood = FALSE;
            }

            if(trim($myvalues['ontime_p'])>'')
            {
                $pub_ontime_p = $myvalues['ontime_p'];
                $pub_ontime_u = $myvalues['ontime_u'];
                if(!is_numeric($pub_ontime_p))
                {
                    form_set_error('ontime_p', "On-Time Probability must be numeric instead of '$pub_ontime_p'");
                    $bGood = FALSE;
                } else {
                    if($pub_ontime_p < 0 || $pub_ontime_p > 1)
                    {
                        form_set_error('ontime_p', 'On-Time Probability value must be in range [0.00,1.00]');
                        $bGood = FALSE;
                    }
                }
                if(!is_numeric($pub_ontime_u))
                {
                    form_set_error('ontime_u', 'On-Time Uncertainty must be numeric');
                    $bGood = FALSE;
                } else {
                    if($pub_ontime_u < 0 || $pub_ontime_u > 1)
                    {
                        form_set_error('ontime_u', 'On-Time Uncertainty value must be in range [0.00,1.00]');
                        $bGood = FALSE;
                    }
                }
            }
            
            if(trim($myvalues['onbudget_p'])>'')
            {
                $pub_onbudget_p = $myvalues['onbudget_p'];
                $pub_onbudget_u = $myvalues['onbudget_u'];
                if(!is_numeric($pub_onbudget_p))
                {
                    form_set_error('onbudget_p', "On-Budget Probability must be numeric instead of '$pub_onbudget_p'");
                    $bGood = FALSE;
                } else {
                    if($pub_onbudget_p < 0 || $pub_onbudget_p > 1)
                    {
                        form_set_error('onbudget_p', 'On-Budget Probability value must be in range [0.00,1.00]');
                        $bGood = FALSE;
                    }
                }
                if(!is_numeric($pub_onbudget_u))
                {
                    form_set_error('onbudget_u', 'On-Budget Uncertainty must be numeric');
                    $bGood = FALSE;
                } else {
                    if($pub_onbudget_u < 0 || $pub_onbudget_u > 1)
                    {
                        form_set_error('onbudget_u', 'On-Budget Uncertainty value must be in range [0.00,1.00]');
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
    
    public function getGoalStatusOptions($show_terminal_text=TRUE)
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
    
    public function getProjectStatusOptions($show_terminal_text=TRUE)
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
    
    /**
     * Return DRUPAL form API of the context dashboard for a goal
     */
    public function getContextDashboardElements($projectid)
    {
        $myvalues = $this->getFieldValues($projectid);
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
        $project_contextid = $myvalues['project_contextid'];
        
        if(empty($project_contextid))
        {
            $projectcontext_markup = "";
        } else {
            $pc_rec = $this->m_all_pcbyid[$project_contextid];
            $pc_shortname = $pc_rec['shortname'];
            $pc_description_tx = $pc_rec['description_tx'];
            $projectcontext_markup = "<span title='$pc_description_tx'>#$project_contextid - $pc_shortname</span>";
        }
        
        $mission_tx = $myvalues['mission_tx'];
        if(isset($myvalues['map_tag2workitem']))
        {
            $tags_tx = implode(', ', $myvalues['map_tag2workitem']);
        } else {
            $tags_tx = '';
        }
        $elements['dashboard']['details']['row1a'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td width='180px'><label for='project_name'>Project Name</label></td>"
                . "<td colspan=9><span id='project_name' title='root goal#{$myvalues['root_goalid']}'>{$myvalues['root_workitem_nm']}</span></td>"
                . "<td colspan=2><label for='projectcontext' title='Context of this project'>Project Context</label></td>"
                . "<td colspan=2><span id='projectcontext'>{$projectcontext_markup}</span></td>"
                . "<td colspan=1><label for='isactive' title='Setting active to No is a type of soft delete'>Is Active</label></td>"
                . "<td colspan=1><span id='isactive'>{$active_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label for='mission_tx'>Mission</label></td>"
                . "<td colspan=15><span id='mission_tx'>{$mission_tx}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='statuscode'>Status</label></td>"
                . "<td colspan=1><span id='statuscode'>{$myvalues['status_cd']}</span></td>"
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
                . "<td><label for='effort_hours_worked_act' title='Actual number of hours effort that were invested in this goal to complete it'>Actual Effort</label></td>"
                . "<td><span id='effort_hours_worked_act'>{$myvalues['effort_hours_worked_act']}</span></td>"
                . "<td colspan='8'></td>"
                . "</tr>");
        $elements['dashboard']['details']['row4'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='tags' title='Text labels associated with this project'>Tags</label></td>"
                . "<td colspan=15><span id='tags'>{$tags_tx}</span></td>"
                . "</tr>");
        return $elements;    
    }
    
    public function getProjectContextOptions($add_unknown=FALSE, $only_active=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getProjectContextsByID(NULL, $add_unknown, $only_active);
            foreach($all as $id=>$record)
            {
                $shortname = $record['shortname'];
                $myoptions[$id] = $shortname;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getMemberGroupOptions($parent_projectid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getGroupsByID(); //TODO ---- FILTER?????
            foreach($all as $code=>$record)
            {
                $title_tx = $record['group_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getMemberRoleOptions($parent_projectid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getRolesByID(); //TODO ---- FILTER!!!!!
            foreach($all as $code=>$record)
            {
                $title_tx = $record['role_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectLeaderOptions($includeblank=TRUE, $include_sysadmin=TRUE, $include_ids=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = [];
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            if(empty($include_ids))
            {
                $include_ids = [];    
            }
            if($include_sysadmin && !array_key_exists(1, $include_ids))
            {
                $include_ids[] = 1;
            }
            $all = $this->m_oMapHelper->getProjectLeaders(NULL, $include_ids);
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

    public function getRootGoalOptions($owning_projectid,$includeblank=TRUE,$include_goalid=NULL)
    {
        try
        {
            if(empty($owning_projectid))
            {
                throw new \Exception("Cannot get list of goals without an owning project!");
            }
            
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            
            $all = $this->m_oMapHelper->getRichGoalsByID($owning_projectid);
            
            //Exclude the ones already assigned as the root of a project
            $candidate_ids = array();
            foreach($all as $onegoal)
            {
                $candidate_ids[] = $onegoal['id'];
            }
            $existing_project_root_goals = $this->m_oMapHelper->getProjectRootGoalIdsMapFromGoalFilter($candidate_ids);
            foreach($all as $goalid=>$record)
            {
                if($include_goalid == $goalid || !key_exists($goalid, $existing_project_root_goals))
                {
                    $title_tx = $record['workitem_nm'];
                    $myoptions[$goalid] = $title_tx;
                }
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
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            
            $thelimit = BIGFATHOM_MAX_PROJECTS;
            if($formType=='A' && $this->m_oMapHelper->getCountProjects() >= $thelimit)
            {
                drupal_set_message("Cannot add another project because your system already has the configuration allowed limit of $thelimit",'error');
                $disabled = TRUE;
            }
            
            
            $root_goalid = NULL;
            $root_goal_info = NULL;
            if(!empty($this->m_root_goalid))
            {
                //We have locked down the root goal
                $is_conversion = TRUE;
                $myvalues['root_goalid'] = $this->m_root_goalid;    
                $root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($this->m_root_goalid);
                if(empty($myvalues['owner_personid']))
                {
                    $myvalues['owner_personid'] = $root_goal_info['owner_personid'];
                }
            } else {
                $is_conversion = FALSE;
                if(!empty($myvalues['root_workitem_nm']))
                {
                    $root_workitem_nm = $myvalues['root_workitem_nm'];
                } else {
                    $root_workitem_nm = '';
                }
                if(!empty($myvalues['root_goalid']))
                {
                    $root_goalid = $myvalues['root_goalid'];
                    if(empty($myvalues['root_workitem_nm']))
                    {
                        $root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($root_goalid);
                        $myvalues['root_workitem_nm'] = $root_goal_info['workitem_nm'];
                    }
                }
            }
            if($this->m_default_parent_projectid != NULL)
            {
                //This is a subproject
                $require_root_workitem_nm = FALSE;
                $require_root_goalid = TRUE;
                $include_goalid_option = $root_goalid;
            } else {
                //This is a top-level project
                if($formType == 'A')
                {
                    $require_root_workitem_nm = TRUE;
                    $require_root_goalid = FALSE;
                    $include_goalid_option = NULL;
                } else {
                    $require_root_workitem_nm = FALSE;
                    $require_root_goalid = TRUE;
                    $include_goalid_option = $root_goalid;
                }
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
                
            if(isset($myvalues['publishedrefname']))
            {
                $publishedrefname = $myvalues['publishedrefname'];
            } else {
                $publishedrefname = NULL;
            }
            if(isset($myvalues['allow_status_publish_yn']))
            {
                $allow_status_publish_yn = $myvalues['allow_status_publish_yn'];
            } else {
                $allow_status_publish_yn = 0;
            }
                
            if(isset($myvalues['parent_projectid']))
            {
                $parent_projectid = $myvalues['parent_projectid'];
            } else {
                $parent_projectid = NULL;
            }
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
            }
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
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
                $importance = 40;
            }
            if(isset($myvalues['mission_tx']))
            {
                $mission_tx = $myvalues['mission_tx'];
            } else {
                $mission_tx = '';
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            if(isset($myvalues['chargecode']))
            {
                $chargecode = $myvalues['chargecode'];
            } else {
                $chargecode = '';
            }            
            if(isset($myvalues['project_contextid']))
            {
                $project_contextid = $myvalues['project_contextid'];
            } else {
                $project_contextid = '';
            }

            if(isset($myvalues['source_type']))
            {
                $source_type = $myvalues['source_type'];
            } else {
                $source_type = '';
            }
            if(isset($myvalues['surrogate_yn']))
            {
                $surrogate_yn = $myvalues['surrogate_yn'];
            } else {
                $surrogate_yn = '';
            }
            if(isset($myvalues['allow_refresh_from_remote_yn']))
            {
                $allow_refresh_from_remote_yn = $myvalues['allow_refresh_from_remote_yn'];
            } else {
                $allow_refresh_from_remote_yn = '';
            }
            if(isset($myvalues['remote_uri']))
            {
                $remote_uri = $myvalues['remote_uri'];
            } else {
                $remote_uri = '';
            }

            if(isset($myvalues['surrogate_ob_p']))
            {
                $surrogate_ob_p = $myvalues['surrogate_ob_p'];
            } else {
                $surrogate_ob_p = '0.015';
            }
            if(isset($myvalues['surrogate_ot_p']))
            {
                $surrogate_ot_p = $myvalues['surrogate_ot_p'];
            } else {
                $surrogate_ot_p = '0.015';
            }
            
            
            if(isset($myvalues['map_group2project']))
            {
                $default_groupsinproject = $myvalues['map_group2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_groupsinproject = array();
                } else {
                    $default_groupsinproject = $this->m_oMapHelper->getIDListOfGroupsInProject($parent_projectid);
                }
            }

            if(isset($myvalues['map_role2project']))
            {
                $default_rolesinproject = $myvalues['map_role2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_rolesinproject = array();
                } else {
                    $default_rolesinproject = $this->m_oMapHelper->getIDListOfRolesInProject($parent_projectid);
                }
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
            
            
            $options_goal_status = $this->getGoalStatusOptions();
            $options_projmanagers = $this->getProjectLeaderOptions();
            if(!empty($this->m_projectid))
            {
                //Use a full dropdown
                $options_rootgoal = $this->getRootGoalOptions($this->m_projectid, FALSE, $include_goalid_option);
            } else {
                //Not a full dropdown
                $options_rootgoal[$root_goalid] = $root_workitem_nm;
            }
            $options_member_groups = $this->getMemberGroupOptions();
            $options_member_roles = $this->getMemberRoleOptions();

            $show_unknown_projectcontext = ($formType == 'A');
            $show_only_active_projectcontext = ($formType == 'A' || $formType == 'E');
            $options_projectcontext = $this->getProjectContextOptions($show_unknown_projectcontext, $show_only_active_projectcontext);
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_root_goalid']
                = array('#type' => 'hidden', '#value' => $root_goalid, '#disabled' => FALSE);        
            $form['hiddenthings']['parent_projectid']
                = array('#type' => 'hidden', '#value' => $parent_projectid, '#disabled' => FALSE);        
            if($formType !== 'A')
            {
                $form['hiddenthings']['source_type']
                    = array('#type' => 'hidden', '#value' => $source_type, '#disabled' => FALSE);        
            }
            
            $options_insight_type = array(
                '0' => t('Deep'),
                '1' => t('Superficial')
            );
            $options_source_type = array(
                'local' => t('Local'),
                'remote' => t('Remote')
            );
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $options_core_sharable_info = array(
                'allow_publish_item_project_name_yn' => t('Project Name'),
                'allow_publish_item_status_cd_yn' => t('Status'),
                'allow_publish_item_project_contextid_yn' => t('Project Context'),
                'allow_publish_item_mission_tx_yn' => t('Mission Text'),
                'allow_publish_item_ontime_p_yn' => t('On-Time Probability'),
                'allow_publish_item_planned_start_dt_yn' => t('Planned Start Date'),
                'allow_publish_item_planned_end_dt_yn' => t('Planned End Date'),
            );
            $options_sharable_info = array(
                'allow_publish_item_owner_name_yn' => t('Project Owner Name'),
                'allow_publish_item_onbudget_p_yn' => t('On-Budget Probability'),
                'allow_publish_item_actual_start_dt_yn' => t('Actual Start Date'),
                'allow_publish_item_actual_end_dt_yn' => t('Actual End Date'),
            );

            //Check most of them all by default
            $core_publish_items = [];
            foreach($options_core_sharable_info as $k=>$label)
            {
                $core_publish_items[$k] = $k;
            }
            
            if(isset($myvalues['allow_publish_items']))
            {
                $allow_publish_items = $myvalues['allow_publish_items'];
            } else {
                //Check most of them all by default
                $allow_publish_items = [];
                foreach($options_sharable_info as $k=>$label)
                {
                    $allow_publish_items[$k] = $k;
                }
                $allow_publish_items["onbudget_p"] = 0;
            }
            
            if($is_conversion)
            {
//drupal_set_message("LOOK WE ARE IN CONVERSION MODE");                
                //Converting a goal into a project
                $form['data_entry_area1']['root_workitem_nm'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Root Goal Name'),
                    '#default_value' => $root_goal_info["workitem_nm"],
                    '#size' => 40,
                    '#maxlength' => 40,
                    '#required' => TRUE,
                    '#description' => t('The root goal of this project'),
                    '#disabled' => TRUE
                );
                $form['data_entry_area1']['purpose_tx'] = array(
                    '#type' => 'textarea',
                    '#title' => t('Goal Purpose'),
                    '#default_value' => $root_goal_info["purpose_tx"],
                    '#size' => 80,
                    '#maxlength' => 1024,
                    '#required' => TRUE,
                    '#description' => t('Short description of the root goal purpose'),
                    '#disabled' => $disabled
                );
                //The status can be edited
                $form['data_entry_area1']['status_cd'] = array(
                    '#type' => 'select',
                    '#title' => t('Status Code'),
                    '#default_value' => $status_cd,
                    '#options' => $options_goal_status,
                    '#required' => TRUE,
                    '#description' => t('The current status of this root goal'),
                    '#disabled' => $disabled
                );
                
            } else {

                $disable_surrogate_yn = $formType !== 'A';
                $disable_source_type = $formType !== 'A';
                $disable_root_goal_change = $formType !== 'A';
                $disable_goal_purpose_edit = $formType !== 'A';
                
                $form['data_entry_area1']['surrogate_yn'] = array(
                    '#type' => 'select',
                    '#title' => t('Local Project Insight Tracking Level'),
                    '#default_value' => $surrogate_yn,
                    '#options' => $options_insight_type,
                    '#required' => FALSE,
                    '#description' => t('If the project workitem details will be in this application instance, select Deep, else select Superficial.'),
                    '#disabled' => $disable_surrogate_yn,
                );
                
                if($disable_surrogate_yn)
                {
                    $form['hiddenthings']['surrogate_yn']
                        = array('#type' => 'hidden', '#value' => $surrogate_yn, '#disabled' => FALSE);        
                }

                $form['data_entry_area1']['source_type'] = array(
                    '#type' => 'select',
                    '#title' => t('Information Source'),
                    '#default_value' => $source_type,
                    '#options' => $options_source_type,
                    '#required' => FALSE,
                    '#description' => t('Will the project tracking information be maintained here or will it be pulled from a remote URI?'),
                    '#disabled' => $disable_source_type,
                );
                if(!$disable_surrogate_yn)
                {
                    $form['data_entry_area1']['source_type']['#states'] = array(
                            'visible' => array(
                              ':input[name="surrogate_yn"]' => array('value' => '1'),
                            ),
                        );    
                }

                if($disable_source_type)
                {
                    $form['hiddenthings']['source_type']
                        = array('#type' => 'hidden', '#value' => $source_type, '#disabled' => FALSE);        
                }
                
                if($disable_surrogate_yn && $disable_source_type)
                {
                    //The client side show/hide will not work when disabled.
                    $show_remote_info_elems = ($source_type == 'remote');
                } else {
                    $show_remote_info_elems = TRUE;
                }
                if($show_remote_info_elems)
                {
                    $form['data_entry_area1']['remote_info'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('Status Importing Options'),
                        '#description' => t('Status for superficially tracked projects are sometimes shared by remote resources and available for periodic import.'),
                        '#collapsible' => FALSE, 
                        '#collapsed' => FALSE,
                        '#disabled' => $disabled,
                    );
                    if(!$disable_surrogate_yn)
                    {
                        $form['data_entry_area1']['remote_info']['#states'] = array(
                                'visible' => array(
                                  ':input[name="surrogate_yn"]' => array('value' => '1'),
                                  ':input[name="source_type"]' => array('value' => 'remote'),
                                ),
                        );
                    }

                    $form['data_entry_area1']['remote_info']['remote_uri'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Remote URI'),
                        '#default_value' => $remote_uri,
                        '#size' => 128,
                        '#maxlength' => 256,
                        '#required' => FALSE,
                        '#description' => t('The remote resource where the application can periodically find updated project information'),
                        '#disabled' => $disabled
                    );

                    $form['data_entry_area1']['remote_info']['allow_refresh_from_remote_yn'] = array(
                        '#type' => 'radios',
                        '#title' => t('Allow Refresh From Remote URI'),
                        '#default_value' => isset($myvalues['allow_refresh_from_remote_yn']) ? $myvalues['allow_refresh_from_remote_yn'] : 1,
                        '#options' => $ynoptions,
                        '#required' => FALSE,
                        '#description' => t('Is this project allowed to update itself directly from the provided URI?'),
                    );
                    if(!$disable_source_type)
                    {
                        $form['data_entry_area1']['remote_info']['allow_refresh_from_remote_yn']['#states'] = array(
                                'visible' => array(
                                  ':input[name="remote_uri"]' => array('filled' => TRUE),
                                ),
                            );
                    }

                    $all_remote_uri_filter_rules = $this->m_oMapHelper->getAllRemoteURIFilterRules();
                    $json_uri_rules = json_encode($all_remote_uri_filter_rules);

                    $form['data_entry_area1']['remote_info']['fetch_status_link'] = array(
                              '#type' => 'item'
                            , '#markup' => "<a href='#' onclick='fetchRemoteInfo();return false;'>Fetch Remote Information Now</a>"
                        . "\n<script>"
                        . "\nvar m_jsonData={};   //Initialize as empty"
                        . "\nvar m_whitelistbundle={$json_uri_rules};"
                        . "\n"
                        . "\nfunction fetchRemoteInfo(){"
                        . "\n  var remote_uri=getValueFromTextboxByName('remote_uri').trim();"
                        . "\n  if(remote_uri.length < 4){"
                        . "\n     alert('The URI is not valid!');"
                        . "\n     return 0;"
                        . "\n  }"
                        . "\n  if(!isURIInWhitelistBundle(remote_uri, m_whitelistbundle)){"
                        . "\n     console.log('LOOK m_whitelistbundle=' + JSON.stringify(m_whitelistbundle));"        
                        . "\n     alert('The provided URI is not allowed for fetching project information!');"
                        . "\n     return 0;"
                        . "\n  }"
                        . "\n  "
                        . "\n  //alert('look we are in the function for remote uri=' + remote_uri);"
                        . "\n  "
                        . "\n  var updateFields = function(callbackid, responseBundle){"
                        . "\n"
                        . "\n    console.log('LOOK !!!!! our result for ' + callbackid + ' is ' + JSON.stringify(responseBundle));"
                        . "\n    if(!responseBundle.hasOwnProperty('responseNum') || responseBundle.responseNum != 0)"
                        . "\n    { "
                        . "\n      if(!responseBundle.responseDetail.hasOwnProperty('message'))"
                        . "\n      { "
                        . "\n        alert('Failed to fetch usable project status information from ' + remote_uri);"
                        . "\n      } else { "
                        . "\n        alert('Failed to fetch usable project status information from ' + remote_uri + ' because ' + responseBundle.responseDetail.message);"
                        . "\n      } "
                        . "\n    } else {"
                        . "\n      m_jsonData = responseBundle.data.data;"
                        . "\n      console.log('LOOK >>>> we have this raw JSON data=' + m_jsonData);"
                        . "\n      console.log('LOOK >>>> we have this raw data=' + m_jsonData.data);"
                        . "\n      console.log('LOOK >>>> we have this stringify JSON data=' + JSON.stringify(m_jsonData));"
                        . "\n      console.log('LOOK >>>> we have publishedrefname=' + m_jsonData.publishedrefname);"
                        . "\n      console.log('LOOK >>>> we have data=' + JSON.stringify(m_jsonData.data));"
                        . "\n      if(typeof m_jsonData.publishedrefname == 'undefined' || m_jsonData.publishedrefname == null || m_jsonData.publishedrefname == '') "
                        . "\n      {"
                        . "\n        alert('Nothing downloaded from the remote uri!');"
                        . "\n      } else {"
                        . "\n        var tracker_refname = m_jsonData.publishedrefname + '.tracker';"
                        . "\n        var tracker_rootname = m_jsonData.data.project_nm + ' Tracker';"
                        . "\n        setValueForTextboxByName('publishedrefname', tracker_refname);"
                        . "\n        setValueForTextboxByName('root_workitem_nm', tracker_rootname);"
                        . "\n        setValueForTextboxByName('status_cd', m_jsonData.data.status_cd);"
                        . "\n        setValueForTextboxByName('mission_tx', m_jsonData.data.mission_tx);"
                        . "\n        setValueForTextboxByName('surrogate_ot_p', m_jsonData.data.ontime_p);"
                        . "\n        setValueForTextboxByName('surrogate_ob_p', m_jsonData.data.onbudget_p);"
                        . "\n        setValueForTextboxByName('actual_start_dt[date]', m_jsonData.data.actual_start_dt);"
                        . "\n        setValueForTextboxByName('actual_end_dt[date]', m_jsonData.data.actual_end_dt);"
                        . "\n        setValueForTextboxByName('planned_start_dt[date]', m_jsonData.data.planned_start_dt);"
                        . "\n        setValueForTextboxByName('planned_end_dt[date]', m_jsonData.data.planned_end_dt);"
                        . "\n        setValueForTextboxByName('status_cd', m_jsonData.data.status_cd);"
                        . "\n        setValueForTextboxByName('project_contextid', m_jsonData.data.project_contextid);"
                        . "\n        alert('Success! Fields have been updated with content from the remote uri');"
                        . "\n      }"
                        . "\n    };"
                        . "\n  };"
                        . "\n  "
                        . "\n  var input_filter = {'encoded_uri':encodeURIComponent(remote_uri)};"
                        . "\n  var grab_fullurl = bigfathom_util.data.getGrabDataUrl('project_status_from_uri',input_filter);"
                        . "\n  var sendbundle={};"
                        . "\n  var callbackid=123; "
                        . "\n  console.log('LOOK input_filter=' + JSON.stringify(input_filter));"
                        . "\n  console.log('LOOK grab_fullurl=' + grab_fullurl);"
                        . "\n  console.log('LOOK sendbundle=' + JSON.stringify(sendbundle));"
                        . "\n  bigfathom_util.data.getDataFromServer(grab_fullurl, sendbundle, updateFields, callbackid);"        
                        . "\n  //getProjectStatusFromServer(remote_uri, updateFields);"
                        . "\n  return 0;"
                        . "\n}"
                        . "\n</script>"
                    );
                    if(!$disable_source_type)
                    {
                        $form['data_entry_area1']['remote_info']['allow_refresh_from_remote_yn']['#states'] = array(
                                    'visible' => array(
                                        ':input[name="remote_uri"]' => array('filled' => TRUE),
                                        ':input[name="allow_refresh_from_remote_yn"]' => array('value' => 1),
                                    )    
                                );
                    }
                    
                }
                
                if($surrogate_yn == 1 || empty($root_goalid))
                {
                    $form['data_entry_area1']['root_workitem_nm'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Project Name'),
                        '#default_value' => $root_workitem_nm,
                        '#size' => 40,
                        '#maxlength' => 40,
                        '#required' => TRUE,
                        '#description' => t('The name of this project'),
                        '#disabled' => $disabled
                    );
                } else {
                    $form['data_entry_area1']['root_goalid'] = array(
                        '#type' => 'select',
                        '#title' => t('Root Goal'),
                        '#default_value' => $root_goalid,
                        '#options' => $options_rootgoal,
                        '#required' => TRUE,
                        '#description' => t('What is the root goal of this project?'),
                        '#disabled' => $disable_root_goal_change
                    );
                }
                
                if($disable_surrogate_yn)
                {
                    //The client side show/hide will not work when disabled.
                    $show_surrogate_elems = ($surrogate_yn == 1);
                } else {
                    //Let javascript work it out.
                    $show_surrogate_elems = TRUE;
                }
                if($show_surrogate_elems)
                {
                    $form['data_entry_area1']['surrogate_ot_p'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Successful Completion On-Time Probability'),
                        '#default_value' => $surrogate_ot_p,
                        '#size' => 11,
                        '#maxlength' => 11,
                        '#required' => FALSE,
                        '#description' => t('The probability of on-time successful completion of this project'),
                        '#disabled' => $disabled,
                        '#states' => array(
                            'visible' => array(
                              ':input[name="surrogate_yn"]' => array('value' => '1'),
                            ),
                        ),
                    );

                    $form['data_entry_area1']['surrogate_ob_p'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Successful Completion On-Budget Probability'),
                        '#default_value' => $surrogate_ob_p,
                        '#size' => 11,
                        '#maxlength' => 11,
                        '#required' => FALSE,
                        '#description' => t('The probability of on-budget successful completion of this project'),
                        '#disabled' => $disabled,
                        '#states' => array(
                            'visible' => array(
                              ':input[name="surrogate_yn"]' => array('value' => '1'),
                            ),
                        ),
                    );
                }
                
                if($disable_source_type)
                {
                    //The client side show/hide will not work when disabled.
                    $show_local_source_elems = ($source_type == 'local');
                    $show_local_publish_elems = ($source_type == 'local');
                } else {
                    //Let javascript work it out.
                    $show_local_source_elems = TRUE;
                    $show_local_publish_elems = TRUE;
                }
                
                if($show_local_source_elems)
                {
                    $form['data_entry_area1']['local'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('Deep Insight Project Tracking'),
                        '#description' => t('Locally managing project workitem details enables you to plan and collaborate at the richer level of detail.'),
                        '#collapsible' => FALSE, 
                        '#collapsed' => FALSE,
                        '#disabled' => $disabled,
                        );
                    
                    if(!$disable_source_type)
                    {
                        $form['data_entry_area1']['local']['#states'] = array(
                            'visible' => array(
                                ':input[name="surrogate_yn"]' => array('value' => '0'),
                                ),
                            );
                    }

                    $form['data_entry_area1']['local']['purpose_tx'] = array(
                        '#type' => 'textarea',
                        '#title' => t('Goal Purpose'),
                        '#default_value' => $purpose_tx,
                        '#size' => 80,
                        '#maxlength' => 1024,
                        '#required' => FALSE,
                        '#description' => t('Short description of the root goal purpose.  (Every project has a final goal at its root; completing that goal completes the project.)'),
                        '#disabled' => $disable_goal_purpose_edit
                    );
                    $form['data_entry_area1']['local']['map_group2project'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                            , "<i class='fa fa-users' aria-hidden='true'></i> " . t('Member Groups')
                            , $default_groupsinproject
                            , $options_member_groups
                            , FALSE
                            , 'The groups directly contributing to the successful completion of this project'
                        );
                    if(!$disable_source_type)
                    {
                        $form['data_entry_area1']['local']['map_group2project']['#states']  = array(
                                'visible' => array(
                                    ':input[name="surrogate_yn"]' => array('value' => '0'),
                                ),
                            );
                    }

                    $form['data_entry_area1']['local']['map_role2project'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                            , 'Relevant Team Member Roles'
                            , $default_rolesinproject
                            , $options_member_roles
                            , FALSE
                            , 'Roles directly relevant to the successful completion of this project'
                        );
                    if(!$disable_source_type)
                    {
                        $form['data_entry_area1']['local']['map_role2project']['#states']  = array(
                                'visible' => array(
                                    ':input[name="surrogate_yn"]' => array('value' => '0'),
                                ),
                            );
                    }
                }

                if($show_local_publish_elems)
                {
                    $form['data_entry_area1']['allow_status_publish_yn'] = array(
                        '#type' => 'radios',
                        '#title' => t('Publicly Sharable Project'),
                        '#default_value' => isset($myvalues['allow_status_publish_yn']) ? $myvalues['allow_status_publish_yn'] : 0,
                        '#options' => $ynoptions,
                        '#description' => t('Yes if some or all project information is to be shared publicly by the application.')
                    );
                    if(!$disable_surrogate_yn && !$disable_source_type)
                    {
                        $form['data_entry_area1']['allow_status_publish_yn']['#states']  = array(
                                'visible' => array(
                                    array(
                                        array(':input[name="surrogate_yn"]' => array('value' => '0')),
                                        'or',
                                        array(':input[name="source_type"]' => array('value' => 'local')),
                                    ),
                                ),
                            );
                    }
/*
                    $form['data_entry_area1']['core_publish_items'] = array(
                        '#type' => 'checkboxes',
                        '#title' => t('Core Publish Content'),
                        '#default_value' => $core_publish_items,
                        '#options' => $options_core_sharable_info,
                        '#description' => t('These core items will be shared when project information is published'),
                        '#disabled' => TRUE,
                    );
*/                  
                    
                    $core_publish_items_markup = implode(", ", $options_core_sharable_info);
                    $form['data_entry_area1']['core_publish_items'] = array(
                        '#type' => 'item',
                        '#title' => t('Core Publish Content'),
                        '#markup' => $core_publish_items_markup,
                        '#description' => t('These core items will be shared when project information is published'),
                        '#disabled' => TRUE,
                    );
                    
                    if(!$disable_surrogate_yn && !$disable_source_type)
                    {
                        $form['data_entry_area1']['core_publish_items']['#states']  = array(
                                'visible' => array(
                                    array(
                                        array(':input[name="surrogate_yn"]' => array('value' => '0')),
                                        'or',
                                        array(':input[name="source_type"]' => array('value' => 'local')),
                                    ),
                                ),
                            );
                    }
                    $form['data_entry_area1']['publish_filter'] = array(
                        '#type' => 'fieldset',
                        '#title' => t('Optional Publish Content'),
                        '#description' => t('The selected optional items will be publicly shared if project information sharing is enabled and information is published.'),
                        '#collapsible' => FALSE, 
                        '#collapsed' => FALSE,
                        '#disabled' => $disabled,
                    );
                    if(!$disable_surrogate_yn && !$disable_source_type)
                    {
                        $form['data_entry_area1']['publish_filter']['#states']  = array(
                                'visible' => array(
                                    array(
                                        array(':input[name="surrogate_yn"]' => array('value' => '0')),
                                        'or',
                                        array(':input[name="source_type"]' => array('value' => 'local')),
                                    ),
                                ),
                            );
                    }
                    
                    $form['data_entry_area1']['publish_filter']['allow_publish_items'] = array(
                        '#type' => 'checkboxes',
                        '#default_value' => $allow_publish_items,
                        '#options' => $options_sharable_info,
                        '#disabled' => $disabled,
                    );
                    

                }
                
            }
            
            $form['data_entry_area1']['publishedrefname'] = array(
                '#type' => 'textfield',
                '#title' => "<i class='fa fa-globe' aria-hidden='true'></i> " . t('Public Reference Name'),
                '#default_value' => $publishedrefname,
                '#size' => 80,
                '#maxlength' => 128,
                '#required' => FALSE,
                '#description' => t('Provide a unique name here by which your project will be found if some or all project information is to be shared publicly by the application.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Project Status'),
                '#default_value' => $status_cd,
                '#options' => $options_goal_status,
                '#required' => TRUE,
                '#description' => t('The current status of this project'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['project_contextid'] = array(
                '#type' => 'select',
                '#title' => t('Project Context'),
                '#default_value' => $project_contextid,
                '#options' => $options_projectcontext,
                '#required' => TRUE,
                '#description' => t('The broad category under-which people might look for this project'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['mission_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Mission Context Statement '),
                '#default_value' => $mission_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('The overall "mission vision" of the project.  A mission context statement should suggest a bigger aspiration area to which successfully completing the root goal of this project can contribute.'),
                '#disabled' => $disabled
            );

            if($disable_surrogate_yn)
            {
                //The client side show/hide will not work when disabled.
                $show_owner_personid = ($surrogate_yn == 0);
                $show_surrogate_owner_personid = !$show_owner_personid;
            } else {
                //Let javascript work it out.
                $show_owner_personid = TRUE;
                $show_surrogate_owner_personid = TRUE;
            }
            
            if($show_owner_personid)
            {
                $form['data_entry_area1']['owner_personid'] = array(
                    '#type' => 'select',
                    '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Project Leader'),
                    '#default_value' => $owner_personid,
                    '#options' => $options_projmanagers,
                    '#required' => FALSE,
                    '#description' => t('Who is directly responsible for the successful completion of this project?'),
                    '#disabled' => $disabled,
                    );
                if(!$disable_surrogate_yn)
                {
                    $form['data_entry_area1']['owner_personid']['#states'] = array(
                            'visible' => array(
                                ':input[name="surrogate_yn"]' => array('value' => '0'),
                            ),
                        );
                }
            }
            
            if($show_surrogate_owner_personid)
            {
                $form['data_entry_area1']['remote']['surrogate_owner_personid'] = array(
                    '#type' => 'select',
                    '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Project Tracking Owner'),
                    '#default_value' => $owner_personid,
                    '#options' => $options_projmanagers,
                    '#required' => FALSE,
                    '#description' => t('Who will own this project tracking record?'),
                    '#disabled' => $disabled,
                    );
                
                if(!$disable_surrogate_yn)
                {
                    $form['data_entry_area1']['remote']['surrogate_owner_personid']['#states'] = array(
                            'visible' => array(
                                ':input[name="surrogate_yn"]' => array('value' => '1'),
                            ),
                        );
                }
            }

            if($disable_source_type)
            {
                //The client side show/hide will not work when disabled.
                $show_lock_date_elems = ($source_type == 'local');
            } else {
                //Let javascript work it out.
                $show_lock_date_elems = TRUE;
            }
            
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
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Available'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if this project is available to users of the application, else no.')
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
    function getPublishForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $projectid = $myvalues['projectid'];
            if(empty($projectid))
            {
                    throw new \Exception("Missing required projectid!");
            }
            $pubid = isset($myvalues['pubid']) ? isset($myvalues['pubid']) : NULL;
			
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            
            $surrogate_yn = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'surrogate_yn', 0);
            
            $allow_publish_item_owner_name_yn = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'allow_publish_item_owner_name_yn', 1);
            $allow_publish_item_onbudget_p_yn = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'allow_publish_item_onbudget_p_yn', 1);
            $allow_publish_item_actual_start_dt_yn = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'allow_publish_item_actual_start_dt_yn', 1);
            $allow_publish_item_actual_end_dt_yn = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'allow_publish_item_actual_end_dt_yn', 1);
            
            $pub_publishedrefname = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_publishedrefname');
            $pub_project_contextid = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_project_contextid');
            $pub_project_nm = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_project_nm');
            $pub_owner_personid = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_owner_personid');
            $pub_planned_start_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_planned_start_dt');
            $pub_actual_start_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_actual_start_dt');
            $pub_planned_end_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_planned_end_dt');
            $pub_actual_end_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_actual_end_dt');
            $pub_onbudget_p = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_onbudget_p');
            $pub_onbudget_u = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_onbudget_u');
            $pub_ontime_p = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_ontime_p');
            $pub_ontime_u = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_ontime_u');
            $pub_comment_tx = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_comment_tx');
            $pub_status_cd = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_status_cd');
            $pub_status_set_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_status_set_dt');
            $pub_updated_dt = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_updated_dt');
            $pub_mission_tx = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_mission_tx');
            $pub_root_goalid = $this->m_oFormHelper->getValueFromArrayOrAlt($myvalues, 'pub_root_goalid');
                    
            $options_goal_status = $this->getGoalStatusOptions();
            $options_projmanagers = $this->getProjectLeaderOptions();
            if(!array_key_exists($pub_owner_personid, $options_projmanagers))
            {
                $options_projmanagers[$pub_owner_personid] = $this->m_oUAH->getExistingPersonFullName($pub_owner_personid);
            }
            
            $show_unknown_projectcontext = FALSE;
            $show_only_active_projectcontext = TRUE;
            $options_projectcontext = $this->getProjectContextOptions($show_unknown_projectcontext, $show_only_active_projectcontext);
            
            $form['hiddenthings']['projectid'] 
                = array('#type' => 'hidden', '#value' => $projectid, '#disabled' => FALSE); 
            $form['hiddenthings']['pubid'] 
                = array('#type' => 'hidden', '#value' => $pubid, '#disabled' => FALSE); 
            $form['hiddenthings']['original_root_goalid']
                = array('#type' => 'hidden', '#value' => $pub_root_goalid, '#disabled' => FALSE);        
            $form['hiddenthings']['status_set_dt']
                = array('#type' => 'hidden', '#value' => $pub_status_set_dt, '#disabled' => FALSE);        
            $form['hiddenthings']['updated_dt']
                = array('#type' => 'hidden', '#value' => $pub_updated_dt, '#disabled' => FALSE);        
            
            $disable_publishedrefname = $disabled || (!$disabled && !empty(trim($pub_publishedrefname)));
            if($disable_publishedrefname)
            {
                $form['hiddenthings']['publishedrefname']
                    = array('#type' => 'hidden', '#value' => $pub_publishedrefname, '#disabled' => FALSE);        
            }
            
            
            $options_insight_type = array(
                '0' => t('Deep'),
                '1' => t('Superficial')
            );
            $options_core_sharable_info = array(
                'allow_publish_item_project_name_yn' => t('Project Name'),
                'allow_publish_item_status_cd_yn' => t('Status'),
                'allow_publish_item_project_contextid_yn' => t('Project Context'),
                'allow_publish_item_mission_tx_yn' => t('Mission Text'),
                'allow_publish_item_ontime_p_yn' => t('On-Time Probability'),
                'allow_publish_item_planned_start_dt_yn' => t('Planned Start Date'),
                'allow_publish_item_planned_end_dt_yn' => t('Planned End Date'),
            );
            $options_sharable_info = [];
            if($allow_publish_item_owner_name_yn)
            {
                $options_sharable_info['allow_publish_item_owner_name_yn'] = t('Project Owner Name');
            }
            if($allow_publish_item_onbudget_p_yn)
            {
                $options_sharable_info['allow_publish_item_onbudget_p_yn'] = t('On-Budget Probability');
            }
            if($allow_publish_item_actual_start_dt_yn)
            {
                $options_sharable_info['allow_publish_item_actual_start_dt_yn'] = t('Actual Start Date');
            }
            if($allow_publish_item_actual_end_dt_yn)
            {
                $options_sharable_info['allow_publish_item_actual_end_dt_yn'] = t('Actual End Date');
            }

            //Check most of them all by default
            $core_publish_items = [];
            foreach($options_core_sharable_info as $k=>$label)
            {
                $core_publish_items[$k] = $k;
            }
            
            if(isset($myvalues['allow_publish_items']))
            {
                $allow_publish_items = $myvalues['allow_publish_items'];
            } else {
                //Check most of them all by default
                $allow_publish_items = [];
                foreach($options_sharable_info as $k=>$label)
                {
                    $allow_publish_items[$k] = $k;
                }
                $allow_publish_items["onbudget_p"] = 0;
            }

            $form['data_entry_area1']['surrogate_yn'] = array(
                '#type' => 'select',
                '#title' => t('Local Project Insight Tracking Level'),
                '#default_value' => $surrogate_yn,
                '#options' => $options_insight_type,
                '#required' => FALSE,
                '#description' => t('Deep insight indicates workitems details are maintained in this application instance; Superficial indicates only some information is tracked.'),
                '#disabled' => TRUE,
            );
            
            $form['data_entry_area1']['publishedrefname'] = array(
                '#type' => 'textfield',
                '#title' => t('Public Reference Name'),
                '#default_value' => $pub_publishedrefname,
                '#size' => 80,
                '#maxlength' => 128,
                '#required' => FALSE,
                '#description' => t('Provide a unique name here by which your project will be found if some or all project information is to be shared publicly by the application.'),
                '#disabled' => $disable_publishedrefname
            );

            $form['data_entry_area1']['information_to_publish'] = array(
                    '#type' => 'fieldset',
                    '#title' => t('Content to Publish'),
                    '#description' => t('The checked items in this section will be published'),
                    '#collapsible' => FALSE, 
                    '#collapsed' => FALSE,
                    '#disabled' => $disabled,
            );

            $form['data_entry_area1']['information_to_publish']['publish_filter'] = array(
                    '#type' => 'fieldset',
                    '#title' => t('Publish Content Selection'),
                    '#description' => t('The selected items will be published.'),
                    '#collapsible' => FALSE, 
                    '#collapsed' => FALSE,
                    '#disabled' => $disabled,
            );

            $form['data_entry_area1']['information_to_publish']['publish_filter']['core_publish_items'] = array(
                    '#type' => 'checkboxes',
                    '#title' => t('Core Publish Content'),
                    '#default_value' => $core_publish_items,
                    '#options' => $options_core_sharable_info,
                    '#disabled' => TRUE,
            );

            if(count($allow_publish_items) > 0)
            {
                $form['data_entry_area1']['information_to_publish']['publish_filter']['allow_publish_items'] = array(
                        '#type' => 'checkboxes',
                        '#title' => t('Optional Publish Content'),
                        '#default_value' => $allow_publish_items,
                        '#options' => $options_sharable_info,
                        '#disabled' => $disabled,
                );
            }

            if($allow_publish_item_owner_name_yn)
            {
                if(!$disabled)
                {
                    //The dropdown is redundant when disabled, the textbox is enough.
                    $form['data_entry_area1']['information_to_publish']['owner_personid'] = array(
                            '#type' => 'select',
                            '#title' => t('Project Tracking Owner'),
                            '#default_value' => $pub_owner_personid,
                            '#options' => $options_projmanagers,
                            '#required' => FALSE,
                            '#description' => t('Primary owner of this tracking information in the current project record'),
                            '#disabled' => TRUE,
                    );
                    $form['data_entry_area1']['information_to_publish']['owner_personid']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_owner_name_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                }
                $form['data_entry_area1']['information_to_publish']['project_manager_override_tx'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Project Leader Name to Publish'),
                        '#default_value' => $options_projmanagers[$pub_owner_personid],
                        '#size' => 50,
                        '#maxlength' => 50,
                        '#required' => FALSE,
                        '#description' => t('This is the literal name text that will appear in the published record'),
                        '#disabled' => $disabled
                );
                if(!$disabled)
                {
                    $form['data_entry_area1']['information_to_publish']['project_manager_override_tx']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_owner_name_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                }
            }

            $form['data_entry_area1']['information_to_publish']['root_workitem_nm'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Project Name'),
                    '#default_value' => $pub_project_nm,
                    '#size' => 40,
                    '#maxlength' => 40,
                    '#required' => TRUE,
                    '#description' => t('The name of this project'),
                    '#disabled' => TRUE
            );

            $form['data_entry_area1']['information_to_publish']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Project Status'),
                '#default_value' => $pub_status_cd,
                '#options' => $options_goal_status,
                '#required' => TRUE,
                '#description' => t('The current status of this project'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['information_to_publish']['project_contextid'] = array(
                '#type' => 'select',
                '#title' => t('Project Context'),
                '#default_value' => $pub_project_contextid,
                '#options' => $options_projectcontext,
                '#required' => TRUE,
                '#description' => t('The broad category under-which people might look for this project'),
                '#disabled' => TRUE
            );
            
            $form['data_entry_area1']['information_to_publish']['mission_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Mission Statement '),
                '#default_value' => $pub_mission_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('The overall mission vision of this project.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['information_to_publish']['starting_date_info'] = array(
                '#type' => 'fieldset',
                '#title' => t('When the Work Starts'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
            
            $form['data_entry_area1']['information_to_publish']['starting_date_info']['planned_start_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Planned Start'),
                '#default_value' => $pub_planned_start_dt,
                '#required' => FALSE,
                '#description' => t('Planned date for this work to start'),
                '#disabled' => $disabled,
            );
            if($allow_publish_item_actual_start_dt_yn)
            {

                $form['data_entry_area1']['information_to_publish']['starting_date_info']['actual_start_dt'] = array(
                    '#type' => 'date_popup',
                    '#date_format'   => 'Y-m-d',
                    '#title' => t('Actual Start'),
                    '#default_value' => $pub_actual_start_dt,
                    '#required' => FALSE,
                    '#description' => t('Actual date on which this work started'),
                    '#disabled' => $disabled
                );
                if(!$disabled)
                {
                    $form['data_entry_area1']['information_to_publish']['ending_date_info']['actual_start_dt']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_actual_start_dt_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                }
            }

            $form['data_entry_area1']['information_to_publish']['ending_date_info'] = array(
                '#type' => 'fieldset',
                '#title' => t('When the Work Ends'),
                '#collapsible' => FALSE, 
                '#collapsed' => FALSE,
                '#disabled' => $disabled,
            );
			
            $form['data_entry_area1']['information_to_publish']['ending_date_info']['planned_end_dt'] = array(
                '#type' => 'date_popup',
                '#date_format'   => 'Y-m-d',
                '#title' => t('Planned End'),
                '#default_value' => $pub_planned_end_dt,
                '#required' => FALSE,
                '#description' => t('Planned date for this work to end'),
                '#disabled' => $disabled
            );
            
            if($allow_publish_item_actual_end_dt_yn)
            {
                $form['data_entry_area1']['information_to_publish']['ending_date_info']['actual_end_dt'] = array(
                    '#type' => 'date_popup',
                    '#date_format'   => 'Y-m-d',
                    '#title' => t('Actual End'),
                    '#default_value' => $pub_actual_end_dt,
                    '#required' => FALSE,
                    '#description' => t('Actual date on which this work ended'),
                    '#disabled' => $disabled
                );
                if(!$disabled)
                {
                    $form['data_entry_area1']['information_to_publish']['ending_date_info']['actual_end_dt']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_actual_end_dt_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                }
            }
            
            $form['data_entry_area1']['information_to_publish']['ontime_p'] = array(
                '#type' => 'textfield',
                '#title' => t('Successful Completion On-Time Probability'),
                '#default_value' => $pub_ontime_p,
                '#size' => 11,
                '#maxlength' => 11,
                '#required' => FALSE,
                '#description' => t('The probability of on-time successful completion of this project'),
                '#disabled' => $disabled,
            );

            $form['data_entry_area1']['information_to_publish']['ontime_u'] = array(
                '#type' => 'textfield',
                '#title' => t('Uncertainty of the Successful Completion On-Time Probability Value'),
                '#default_value' => $pub_ontime_u,
                '#size' => 11,
                '#maxlength' => 11,
                '#required' => FALSE,
                '#description' => t('This is a +- amount applied to the probability where 0 implies absolute confidence in the declared probabilty'),
                '#disabled' => $disabled,
            );

            if($allow_publish_item_onbudget_p_yn)
            {
                $form['data_entry_area1']['information_to_publish']['onbudget_p'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Successful Completion On-Budget Probability'),
                    '#default_value' => $pub_onbudget_p,
                    '#size' => 11,
                    '#maxlength' => 11,
                    '#required' => FALSE,
                    '#description' => t('The probability of on-budget successful completion of this project'),
                    '#disabled' => $disabled,
                );
                $form['data_entry_area1']['information_to_publish']['onbudget_u'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Uncertainty of the On-Budget Probability Value'),
                    '#default_value' => $pub_onbudget_u,
                    '#size' => 11,
                    '#maxlength' => 11,
                    '#required' => FALSE,
                    '#description' => t('This is a +- amount applied to the probability where 0 implies absolute confidence in the declared probabilty'),
                    '#disabled' => $disabled,
                );
                if(!$disabled)
                {
                    $form['data_entry_area1']['information_to_publish']['onbudget_p']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_onbudget_p_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                    $form['data_entry_area1']['information_to_publish']['onbudget_u']['#states'] = array(
                        'visible' => array(
                            ':input[name="allow_publish_items[allow_publish_item_onbudget_p_yn]"]' => array('checked' => TRUE),
                        ),
                    );
                }
            }
            
            $form['data_entry_area1']['information_to_publish']['comment_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Publication Comment'),
                '#default_value' => $pub_comment_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => FALSE,
                '#description' => t('A comment to include with this published status record'),
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
                
            if(!empty($myvalues['projectid']))
            {
                $projectid = $myvalues['projectid'];
            } else {
                throw new \Exception("Cannot get comment form without a projectid!");
            }
            $project_record = $this->m_oMapHelper->getProjectRecord($projectid);
            if(!empty($myvalues['status_cd_at_time_of_com']))
            {
                $status_cd_at_time_of_com = $myvalues['status_cd_at_time_of_com'];
            } else {
                $status_cd_at_time_of_com = $project_record['status_cd'];
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
            
            $form['hiddenthings']['projectid'] 
                = array('#type' => 'hidden', '#value' => $projectid, '#disabled' => FALSE); 
            
            $form['hiddenthings']['status_cd_at_time_of_com'] 
                = array('#type' => 'hidden', '#value' => $status_cd_at_time_of_com, '#disabled' => FALSE); 

            $form['hiddenthings']['updated_dt'] 
                = array('#type' => 'hidden', '#value' => $updated_dt, '#disabled' => FALSE); 

            $form['hiddenthings']['created_dt'] 
                = array('#type' => 'hidden', '#value' => $created_dt, '#disabled' => FALSE); 

            $dashboard = $this->getContextDashboardElements($projectid);
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
                $parent_comment_record = $this->m_oMapHelper->getOneProjectCommunication($parent_comid);
                $parent_action_requested_concern = $parent_comment_record['action_requested_concern'];
                $parent_requests_action = (!empty($parent_action_requested_concern) && $parent_action_requested_concern > 0);
                drupal_set_title("$ftword a Reply to Project Communication #$parent_comid");
            } else {
                $parent_comment_record = NULL;
                $parent_requests_action = FALSE;
                $parent_action_requested_concern = 0;
                drupal_set_title("$ftword a Root Project Comment");
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
