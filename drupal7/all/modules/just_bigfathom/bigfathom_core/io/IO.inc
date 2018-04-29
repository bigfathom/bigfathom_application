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
 * Helpful input output utilities.
 *
 * @author Frank Font
 */
class IO 
{
    private $m_current_user = NULL;
    private $m_oMapHelper = NULL;
    
    public function __construct()
    {
        global $user;
        $this->m_current_user = $user;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }
    
    private function getSimplePairs($allgoals_by_name,$parent_node,$main_node)
    {
        try
        {
            $pairs = array();
            if($main_node == NULL)
            {
                return NULL;
            }
            if($parent_node != null && $parent_node['workitem_nm'] > '')
            {
                $parent_workitem_nm = $parent_node['workitem_nm'];
                $parent_id = $allgoals_by_name[$parent_workitem_nm]['id'];
                $child_workitem_nm = $main_node['workitem_nm'];
                $child_id = $allgoals_by_name[$child_workitem_nm]['id'];
                $pairs = array(array('parent'=>$parent_id,'child'=>$child_id));
            } else {
                $pairs = array();
            }
            $children_ar = $main_node['children'];
            foreach($children_ar as $child)
            {
                $child_pairs = $this->getSimplePairs($allgoals_by_name,$main_node, $child);
                if($child_pairs != null)
                {
                    $pairs = array_merge($pairs, $child_pairs);
                }
            }
            return $pairs;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Import JSON data
     */
    public function importDataFromJSON($import_data_ar)
    {
        $resultbundle = array();
        try
        {
            if(empty($import_data_ar))
            {
                throw new \Exception('Expected array but instead got empty package!');
            } else
            if(!is_array($import_data_ar))
            {
                throw new \Exception('Expected array but instead got ' . $import_data_ar);
            } else
            if($import_data_ar === NULL || $import_data_ar === '')
            {
                throw new \Exception('NOTHING RECEIVED TO IMPORT!');
            } else {
                if(!isset($import_data_ar['dataname']))
                {
                    throw new \Exception("Did NOT find 'dataname' element in " . print_r($import_data_ar,TRUE));
                }
                if(!isset($import_data_ar['databundle']))
                {
                    throw new \Exception("Did NOT find 'databundle' element in " . print_r($import_data_ar,TRUE));
                }
                $dataname = $import_data_ar['dataname'];
                $databundle = $import_data_ar['databundle'];
                $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
                if(!$loaded)
                {
                    throw new \Exception('Failed to load the WriteHelper class');
                }
                $oWriteHelper = new \bigfathom\WriteHelper();
                
                if($dataname === 'create_one_brainstorm_item')
                {
                    $resultbundle = $oWriteHelper->updateOneBrainstormNodeData($databundle);
                } else
                if($dataname === 'update_one_brainstorm_item')
                {
                    $resultbundle = $oWriteHelper->updateOneBrainstormNodeData($databundle);
                } else
                if($dataname === 'update_one_workitem')
                {
                    $resultbundle = $oWriteHelper->updateOneWorkitemData($databundle);
                } else
                if($dataname === 'update_one_sprint_member')
                {
                    $resultbundle = $oWriteHelper->updateOneSprintMemberData($databundle);
                } else
                if($dataname === 'update_sprint_members')
                {
                    $resultbundle = $oWriteHelper->updateSprintMembersData($databundle);
                } else
                if($dataname === 'update_one_subproject_member')
                {
                    $resultbundle = $oWriteHelper->updateOneSubprojectMemberData($databundle);
                } else
                if($dataname === 'update_subproject_members')
                {
                    $resultbundle = $oWriteHelper->updateSubprojectMembersData($databundle);
                } else 
                if($dataname === 'hierarchy_changes')
                {
                    try
                    {
                        
                        $resultbundle = $oWriteHelper->updateWorkitemHierarchyData($databundle);
                        
                    } catch (\Exception $ex) {
                        
                        //Do NOT throw exception -- assume interaction changes are the cause
                        $msg = "Available content changed before your request was submitted";
                        $resultbundle = array(
                                      "message"=>$msg
                                    , "error_msg"=>$ex->getMessage()
                                    , "sent_bundle"=>$databundle
                                    , "changed_node_count"=>0
                                    , "added_link_count"=>0
                                    , "deleted_link_count"=>0
                                    , "map_brainstormid2wid"=>[]
                                );
                        
                    }
                } else 
                if($dataname === 'update_one_goal_item')
                {
                    $resultbundle = $oWriteHelper->updateOneGoalNodeData($databundle);
                } else 
                if($dataname === 'update_workitem_roles') {
                    $resultbundle = $oWriteHelper->updateWorkitemRoleData($databundle);
                } else
                if($dataname === 'update_group_membership')
                {
                    $resultbundle = $oWriteHelper->updateGroupMembershipData($databundle);
                } else
                if($dataname === 'update_testcasestep_status')
                {
                    $resultbundle = $oWriteHelper->updateTestcaseStepStatusData($databundle);
                } else
                if($dataname === 'update_project_group_membership') {
                    $resultbundle = $oWriteHelper->updateProjectGroupMembershipData($databundle);
                } else 
                if($dataname === 'update_project_edit_mode') {
                    $resultbundle = $oWriteHelper->updateProjectEditMode($databundle);
                } else 
                if($dataname === 'update_pcg_membership') {
                    $resultbundle = $oWriteHelper->updatePCGMembershipData($databundle);
                } else 
                if($dataname === 'update_person_influence2wi') {
                    $resultbundle = $oWriteHelper->updatePersonInfluence2WorkitemData($databundle);
                } else 
                if($dataname === 'update_person_importance2wi') {
                    $resultbundle = $oWriteHelper->updatePersonImportance2WorkitemData($databundle);
                } else 
                if($dataname === 'update_vision2project') {
                    $resultbundle = $oWriteHelper->updateVision2ProjectData($databundle);
                } else {
                    throw new \Exception("There is no IO handler for dataname='$dataname'");
                }
            }
            
            $resultbundle['dataname'] = $dataname;
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
	
    /**
     * Dump entire data into an JSON format.
     * @return JSON string
     */
    public function getDataAsJSON($projectid, $sDataName
            ,$aExtraTopLevelAttribs
            ,$sExtraContextNoteText=NULL
            )
    {
        try
        {
            $clean_dataname = strtolower($sDataName);
            $everything = [];
            $everything['dataname'] = $sDataName;
            $mymetadata = [];
            $dumpdate = date('c');
            $mymetadata['export_ts'] = $dumpdate;
            if($sExtraContextNoteText != '')
            {
                $mymetadata['context_note'] = $sExtraContextNoteText;
            }
            $mymetadata['dataname'] = $clean_dataname;
            if($clean_dataname === 'project_status_from_uri')
            {
                
                $theuri = urldecode($aExtraTopLevelAttribs['encoded_uri']);
                $raw_json_package= file_get_contents($theuri);
                if(empty($raw_json_package))
                {
                    $thepackage = array('nodata'=>1,'uri'=>$theuri);
                } else {
                    //Else we have encoded inside encoded!
                    $thepackage = json_decode($raw_json_package);
                }
                $everything['data'] = $thepackage;
                
            } else if($clean_dataname === 'brainstorm_mapping')
            {
                $brainstormcontent = $this->m_oMapHelper->getBrainstormItemsByID($projectid);
                $everything['data'] = $brainstormcontent;
            } else if($clean_dataname === 'subproject_mapping')
            {
                $subprojectcontent = $this->m_oMapHelper->getSubprojectContextBundle($projectid);
                $everything['data'] = $subprojectcontent;
            } else if($clean_dataname === 'timequadrant_mapping')
            {
                $timequadcontent = $this->m_oMapHelper->getTimeQuadrantContextBundle($projectid);
                $everything['data'] = $timequadcontent;
            } else if($clean_dataname === 'insight_mapping')
            {
                $insightcontent = $this->m_oMapHelper->getInsightContextBundle($projectid);
                $everything['data'] = $insightcontent;
            } else if($clean_dataname === 'wi2influence_counts')
            {
                $personid = $aExtraTopLevelAttribs['about_personid'];
                $influencecontent = $this->m_oMapHelper->getWorkitem2InfluenceCountsByCategory4Person(NULL, $personid, $personid, TRUE);
                $everything['data'] = $influencecontent;
            } else if($clean_dataname === 'wi2importance_counts')
            {
                $personid = $aExtraTopLevelAttribs['about_personid'];
                $importancecontent = $this->m_oMapHelper->getWorkitem2ImportanceCountsByCategory4Person(NULL, $personid, $personid, TRUE);
                $everything['data'] = $importancecontent;
            } else if($clean_dataname === 'dashnuggets_personal')
            {
                $personid = $aExtraTopLevelAttribs['about_personid'];
                $showalldetail_yn = $aExtraTopLevelAttribs['showalldetail_yn'];
                $importancecontent = $this->m_oMapHelper->getDashnuggetsPersonalTabBundle($personid, $showalldetail_yn);
                $everything['data'] = $importancecontent;
            } else if($clean_dataname === 'user_worklist_with_urgency')
            {
                $personid = $aExtraTopLevelAttribs['about_personid'];
                $showalldetail_yn = $aExtraTopLevelAttribs['showalldetail_yn'];
                $importantcontent = $this->m_oMapHelper->getSortedWorklistTabBundle($personid, $showalldetail_yn);
                $everything['data'] = $importantcontent;
            } else if($clean_dataname === 'person_utilization')
            {
                $personid = $aExtraTopLevelAttribs['about_personid'];
                $showalldetail_yn = $aExtraTopLevelAttribs['showalldetail_yn'];
                
                module_load_include('php','bigfathom_core','core/WorkApportionmentHelper');
                module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
                $oWAH = new \bigfathom\WorkApportionmentHelper();
                
                $personid_ar = array($personid);
                $merged_bundle =  $oWAH->getMergedIntervalUtilizationByPerson($personid_ar);
                $map_personid2merged_util_rows = $merged_bundle['by_person'];
                $formatted_rowcontent = []; 
                foreach($map_personid2merged_util_rows as $personid=>$merged_util_rows)
                {
                    foreach($merged_util_rows['map']['interval_smash'] as $interval_smash)
                    {
                        $formatted_rowcontent[] = \bigfathom\UtilityFormatUtilizationData::getFormattedRowCells4PersonInterval($interval_smash);
                    }
                }
                    
                //$utilcontent = $this->m_oMapHelper->getShortcutUtilizationAndGapsDataBundle($personid);
                $everything['data'] = $formatted_rowcontent;
            } else if($clean_dataname === 'forecast_nuggets')
            {
                $relevant_projectids = $aExtraTopLevelAttribs['relevant_projectids'];
                $fnug_content = $this->m_oMapHelper->getForecastNuggetsMapBundle($relevant_projectids);
                $everything['data'] = $fnug_content;
            } else if($clean_dataname === 'forecast_detail')
            {
                $relevant_projectids = $aExtraTopLevelAttribs['relevant_projectids'];
                $fbundle_content = $this->m_oMapHelper->getForecastDetailMapBundle($relevant_projectids);
                $everything['data'] = $fbundle_content;
            } else if($clean_dataname === 'apptest_results')
            {
                //Restrict this to ONLY the REAL SYSADMIN user!
                if($this->m_current_user->uid != 1)
                {
                    $errmsg = "User " . $this->m_current_user->uid . " attempted to execute IO apptest_results!";
                    error_log($errmsg);
                    throw new \Exception($errmsg);
                }
                
                $classname = $aExtraTopLevelAttribs['classname'];
                $modulename = $aExtraTopLevelAttribs['modulename'];
                
                $loaded_testharness = module_load_include('php','bigfathom_core','unit_tests/UnitTestHarness');
                if(!$loaded_testharness)
                {
                    throw new \Exception('Failed to load the UnitTestHarness class');
                }
                $oUnitTestHarness = new \bigfathom\UnitTestHarness(FALSE);
                
                $fbundle_content['metadata'] = array('classname'=>$classname, 'modulename'=>$modulename);
                $fbundle_content['testresult'] = $oUnitTestHarness->getTestResultBundle($classname, $modulename);
                
                $everything['data'] = $fbundle_content;
            } else if($clean_dataname === 'hierarchy_mapping')
            {
                $gth_content = $this->m_oMapHelper->getHierarchyMapBundle4Project($projectid);
                $everything['data'] = $gth_content;
            } else if($clean_dataname === 'template_hierarchy_mapping')
            {
                $gth_content = $this->m_oMapHelper->getHierarchyMapBundle4TP($projectid);
                $everything['data'] = $gth_content;
            } else if($clean_dataname === 'hierarchy_updates')
            {
                $previous_project_edit_key = $aExtraTopLevelAttribs['previous_project_edit_key'];
                $previous_project_edit_timestamp = $aExtraTopLevelAttribs['previous_project_edit_timestamp'];
                $previous_bundle = Array('most_recent_edit_key'=>$previous_project_edit_key,
                            'most_recent_edit_timestamp'=>$previous_project_edit_timestamp);
                $updated_content = $this->m_oMapHelper->getHierarchyUpdatesMapBundle($projectid, $previous_bundle);
                $everything['data'] = $updated_content;
            } else if($clean_dataname === 'brainstorm_updates')
            {
                $previous_project_edit_key = $aExtraTopLevelAttribs['previous_project_edit_key'];
                $previous_project_edit_timestamp = $aExtraTopLevelAttribs['previous_project_edit_timestamp'];
                if(!empty($aExtraTopLevelAttribs['include_trashed']))
                {
                    $include_trashed_yn = $aExtraTopLevelAttribs['include_trashed'] != 0;
                } else {
                    $include_trashed_yn = FALSE;
                }
                if(!empty($aExtraTopLevelAttribs['include_parked']))
                {
                    $include_parked_yn = $aExtraTopLevelAttribs['include_parked'] != 0;
                } else {
                    $include_parked_yn = FALSE;
                }
                $previous_bundle = Array('most_recent_edit_key'=>$previous_project_edit_key,
                            'most_recent_edit_timestamp'=>$previous_project_edit_timestamp);
                $updated_content = $this->m_oMapHelper->getBrainstormUpdatesMapBundle($projectid, $previous_bundle, $include_parked_yn, $include_trashed_yn);
                $everything['data'] = $updated_content;
            } else if($clean_dataname === 'communications_summary')
            {
                $contextname = $aExtraTopLevelAttribs['contextname'];
                $contextid_selector = $aExtraTopLevelAttribs['contextid_selector'];
                $summary_content = $this->m_oMapHelper->getCommThreadSummaryMap($contextname, $contextid_selector);
                $everything['data'] = $summary_content;
            } else if($clean_dataname === 'communications_finder_inproject')
            {
                $previous_project_edit_key = $aExtraTopLevelAttribs['previous_project_edit_key'];
                $previous_project_edit_timestamp = $aExtraTopLevelAttribs['previous_project_edit_timestamp'];
                $filter_bundle = [];
                if(!empty($aExtraTopLevelAttribs['start_dt']))
                {
                    $filter_bundle['start_dt'] = $aExtraTopLevelAttribs['start_dt'];
                } else {
                    $filter_bundle['start_dt'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['end_dt']))
                {
                    $filter_bundle['end_dt'] = $aExtraTopLevelAttribs['end_dt'];
                } else {
                    $filter_bundle['end_dt'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['context_personid']))
                {
                    $filter_bundle['context_personid'] = $aExtraTopLevelAttribs['context_personid'];
                } else {
                    $filter_bundle['context_personid'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['comm_matchtext']))
                {
                    $filter_bundle['comm_matchtext'] = $aExtraTopLevelAttribs['comm_matchtext'];
                } else {
                    $filter_bundle['comm_matchtext'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['comids']))
                {
                    $filter_bundle['comids'] = $aExtraTopLevelAttribs['comids'];
                } else {
                    $filter_bundle['comids'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['workitem_namematchtext']))
                {
                    $filter_bundle['workitem_namematchtext'] = $aExtraTopLevelAttribs['workitem_namematchtext'];
                } else {
                    $filter_bundle['workitem_namematchtext'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['workitem_tag_matchtext']))
                {
                    $filter_bundle['workitem_tag_matchtext'] = $aExtraTopLevelAttribs['workitem_tag_matchtext'];
                } else {
                    $filter_bundle['workitem_tag_matchtext'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['ownergroup']))
                {
                    if(empty($filter_bundle['context_personid']))
                    {
                        throw new \Exception("ERROR cannot filter on owner without knowing person context!");
                    }
                    $filter_bundle['ownergroup'] = $aExtraTopLevelAttribs['ownergroup'];
                } else {
                    $filter_bundle['ownergroup'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['authorgroup']))
                {
                    if(empty($filter_bundle['context_personid']))
                    {
                        throw new \Exception("ERROR cannot filter on author without knowing person context!");
                    }
                    $filter_bundle['authorgroup'] = $aExtraTopLevelAttribs['authorgroup'];
                } else {
                    $filter_bundle['authorgroup'] = NULL;
                }
                if(!empty($aExtraTopLevelAttribs['statusgroup']))
                {
                    $filter_bundle['statusgroup'] = $aExtraTopLevelAttribs['statusgroup'];
                } else {
                    $filter_bundle['statusgroup'] = NULL;
                }
                
                $previous_bundle = Array('most_recent_edit_key'=>$previous_project_edit_key,
                            'most_recent_edit_timestamp'=>$previous_project_edit_timestamp);
                $mymetadata['data_age'] = $previous_bundle;
                $updated_content = $this->m_oMapHelper->getCommunicationUpdatesMapBundle($projectid, $filter_bundle);
                $everything['data'] = $updated_content;
            } else if($clean_dataname === 'project_edit_mode_info')
            {
                $projectid = $aExtraTopLevelAttribs['projectid'];
                $one_edit_mode_info = $this->m_oMapHelper->getProjectEditModeInfo($projectid);
                $everything['data'] = $one_edit_mode_info;
            } else if($clean_dataname === 'one_workitem')
            {
                $workitemid = $aExtraTopLevelAttribs['nativeid'];
                $one_workitem = $this->m_oMapHelper->getOneRichWorkitemRecord($workitemid);
                $everything['data'] = $one_workitem;
            } else if($clean_dataname === 'one_workitem_with_lookupinfo')
            {
                $workitemid = $aExtraTopLevelAttribs['nativeid'];
                $one_workitem = $this->m_oMapHelper->getOneRichWorkitemRecordWithLookupInfo($workitemid);
                $everything['data'] = $one_workitem;
            } else if($clean_dataname === 'one_template_workitem_with_lookupinfo')
            {
                $workitemid = $aExtraTopLevelAttribs['nativeid'];
                $one_workitem = $this->m_oMapHelper->getOneRichTWRecordWithLookupInfo($workitemid);
                $everything['data'] = $one_workitem;
            } else if($clean_dataname === 'one_sprint_workitem')
            {
                $sprintid = $aExtraTopLevelAttribs['sprintid'];
                $workitemid = $aExtraTopLevelAttribs['workitemid'];
                $one_workitem = $this->m_oMapHelper->getOneRichSprintWorkitemRecord($sprintid, $workitemid);
                $everything['data'] = $one_workitem;
            } else if($clean_dataname === 'branch_members')
            {
                $workitemid = $aExtraTopLevelAttribs['workitemid'];
                $branchdata = $this->m_oMapHelper->getBranchWorkitemMembersBundle($workitemid);
                $everything['data'] = $branchdata;
            } else {
                throw new \Exception("No IO handler for clean_dataname=[$clean_dataname]");
            }
            $mymetadata['server_timestamp']=time();
            $everything['metadata'] = $mymetadata;
            
            //We are done!
            return json_encode($everything);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getPublisherInfo($this_username)
    {
        $info = [];
        if(empty($this_username))
        {
            $info['name'] = "anonymous";
        } else {
            $info['name'] = "$this_username";
        }
        //$info['uri'] = BIGFATHOM_INSTANCE_ID . "." . BIGFATHOM_INSTANCE_URL;
        $info['uri'] = url(NULL, array('absolute' => TRUE));
        return $info;
    }
    
    
    /**
     * Dump entire data into a JSON format.
     * @return JSON string
     */
    public function getPublishedProjectInfoAsJSON($publishedrefname
            ,$aExtraTopLevelAttribs
            ,$sExtraContextNoteText=NULL
            )
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $this_username = $user->name;
            
            $clean_publishedrefname = strtolower(trim($publishedrefname));
            if(empty($clean_publishedrefname))
            {
                throw new \Exception("Missing required reference name in template publish attempt by {$this_username}!");
            }
            
            $everything = array();
            $everything['publishedrefname'] = $clean_publishedrefname;
            $mymetadata = [];
            $mymetadata['format'] = 'bfps1';    //Bigfathom looks for this!
            $mymetadata['publisher'] = $this->getPublisherInfo($this_username);
            $mymetadata['type'] = 'project status';    //Bigfathom looks for this!
            $dumpdate = date('c');
            $mymetadata['published_ts'] = $dumpdate;
            if($sExtraContextNoteText != '')
            {
                $mymetadata['context_note'] = $sExtraContextNoteText;
            }
            
            $projectid = $this->m_oMapHelper->getProjectIDFromPublishedRefName($clean_publishedrefname);
            $published_projectinfo = $this->m_oMapHelper->getLatestBarePublishedProjectInfoRecord($projectid);
            if(empty($published_projectinfo) || !is_array($published_projectinfo) || count($published_projectinfo) == 0)
            {
                $mymetadata['error'] = [];
                $mymetadata['error']['code'] = ['404'];
                $mymetadata['error']['message'] = ['no published data found for ' . "#$projectid " . $clean_publishedrefname];
                $justdata = NULL;
            } else {
                $justdata = [];
                $candidatedatakeys = array(
                        'project_contextid', 
                        'project_nm', 
                        'mission_tx',
                        'planned_start_dt', 'actual_start_dt',
                        'planned_end_dt', 'actual_end_dt',
                        'onbudget_p', 'onbudget_u',
                        'ontime_p', 'ontime_u',
                        'status_cd', 'status_set_dt',
                        'comment_tx',
                        'updated_dt',
                    );
                foreach($candidatedatakeys as $key)
                {
                    $value = $published_projectinfo[$key];
                    if($value !== NULL)
                    {
                        $justdata[$key] = trim($value);
                    }
                }
                $project_manager_override_tx = $published_projectinfo['project_manager_override_tx'];
                if($project_manager_override_tx !== NULL)
                {
                    $justdata['project_manager'] = trim($project_manager_override_tx);
                }
            }
            
            $everything['metadata'] = $mymetadata;
            if(!empty($justdata))
            {
                $everything['data'] = $justdata;
            }
                
            //We are done!
            return json_encode($everything);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Dump entire data into a TEXT format.
     * @return JSON string
     */
    public function getPublishedProjectInfoNameListAsTEXT($aExtraTopLevelAttribs
                ,$sExtraContextNoteText=NULL
            )
    {
        try
        {

            //$thecols = "REFNAME\tCONTEXTID\tCONTEXT NAME\tPUBLISHED_DT\tSTATUS_CD\tDUE_DT\tOTSP";
            $thecols = "REFNAME\tCONTEXTID\tPUBLISHED_DT\tSTATUS_CD\tDUE_DT\tOTSP";
            $therows_map = $this->m_oMapHelper->getLatestPublishedProjectInfoNameMap();
            $therows_ar = [];
            foreach($therows_map as $record)
            {
                $publishedrefname = $record['publishedrefname'];
                $published_dt = $record['published_dt'];
                
                $project_contextid = $record['project_contextid'];
                //$context_nm = $record['project_context_name'];
                $status_cd = $record['status_cd'];
                $end_dt = $record['end_dt'];
                $otsp = $record['otsp'];
                //$therows_ar[] = "$publishedrefname\t$project_contextid\t$context_nm\t$published_dt\t$status_cd\t$end_dt\t$otsp";
                $therows_ar[] = "$publishedrefname\t$project_contextid\t$published_dt\t$status_cd\t$end_dt\t$otsp";
            }
            $thedata = $thecols . "\n" . implode("\n", $therows_ar) . "\n"; 

            return $thedata;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getTemplateProjectInfoMaps($publishedrefname
            ,$aExtraTopLevelAttribs
            ,$sExtraContextNoteText=NULL
            ,$filetype_tx=NULL
            ,$format_tx=NULL
            )
    {    
        try
        {
            global $user;
            //$this_uid = $user->uid;
            $this_username = $user->name;
            
            $clean_publishedrefname = strtolower(trim($publishedrefname));
            if(empty($clean_publishedrefname))
            {
                throw new \Exception("Missing required reference name in template publish attempt by {$this_username}!");
            }
            
            $everything = [];
            $everything['publishedrefname'] = $clean_publishedrefname;
            $mymetadata = [];
            if(!empty($filetype_tx))
            {
                $mymetadata['format'] = $filetype_tx;
            }
            if(!empty($format_tx))
            {
                if(!empty($mymetadata['format']))
                {
                    $mymetadata['format'] .= ":$format_tx";
                } else {
                    $mymetadata['format'] = $format_tx;
                }
            }
            
            $mymetadata['publisher'] = $this->getPublisherInfo($this_username);
            $mymetadata['type'] = 'project template';    //Bigfathom looks for this!
            $dumpdate = date('c');
            $mymetadata['published_ts'] = $dumpdate;
            if($sExtraContextNoteText != '')
            {
                $mymetadata['context_note'] = $sExtraContextNoteText;
            }

            $templateid = $this->m_oMapHelper->getTPIDFromPublishedRefName($clean_publishedrefname);
            $templateinfo = $this->m_oMapHelper->getOneTPDetailData($templateid);
            $mymetadata['template_nm'] = $templateinfo['template_nm'];
            $mymetadata['publishedrefname'] = $templateinfo['publishedrefname'];
            if(!empty($templateinfo['template_author_nm']))
            {
                $mymetadata['author_name'] = $templateinfo['template_author_nm'];
            }
            $mymetadata['project_contextid'] = $templateinfo['project_contextid'];
            $mymetadata['mission_tx'] = $templateinfo['mission_tx'];
            $mymetadata['submitter_blurb_tx'] = $templateinfo['submitter_blurb_tx'];
            
            $raw_workitems = [];
            $clean_workitems = [];
            $clean_workitemid2projectid = [];
            $maps = [];
            
            $roles_lookup = $this->m_oMapHelper->getRolesByID();
            $relevant_roles = [];
            $bundle = $this->m_oMapHelper->getAllWorkitemsInTemplateBundle($templateid);
            $all_workitems = $bundle['all_workitems'];
            $raw_workitemid2projectid = [];
            $just_raw_pidmap = [];
            //DebugHelper::showNeatMarkup(array('$templateinfo'=>$templateinfo,'$bundle'=>$bundle),'LOOK template stuff');            
            foreach($all_workitems as $wid=>$detail)
            {
                $oneworkitem = [];
                $oneworkitem['name'] = $detail['workitem_nm'];
                $oneworkitem['purpose_tx'] = $detail['purpose_tx'];
                $oneworkitem['owner_template_projectid'] = $detail['owner_template_projectid'];
                if(!empty($detail['root_of_tpid']))
                {
                    $oneworkitem['typeletter'] = 'P';
                } else {
                    $oneworkitem['typeletter'] = $detail['typeletter'];
                }
                foreach($detail['maps']['dap'] as $antwid=>$projectid)
                {
                    $raw_workitemid2projectid[$antwid] = $projectid;
                    $just_raw_pidmap[$projectid] = TRUE;
                }
                $oneworkitem['maps']['daw'] = $detail['maps']['daw'];
                $oneworkitem['maps']['roles'] = $detail['maps']['roles'];
                foreach($oneworkitem['maps']['roles'] as $roleid=>$detail)
                {
                    if(!isset($relevant_roles[$roleid]))
                    {
                        $relevant_roles[$roleid]['name'] = $roles_lookup[$roleid]['role_nm'];
                        $relevant_roles[$roleid]['purpose_tx'] = $roles_lookup[$roleid]['purpose_tx'];
                    }
                }
                $raw_workitems[$wid] = $oneworkitem;
            }

            //Normalize the ID mappings
            $raw_root_workitemid = $bundle['root_goalid'];
            $raw_root_projectid = $raw_workitems[$raw_root_workitemid]['owner_template_projectid'];
            $clean_map_wids = [];
            $clean_map_pids = [];
            $just_pids_list = array_keys($just_raw_pidmap);
            $just_wids_list = array_keys($raw_workitems);
            $pidcount = 1;
            foreach($just_pids_list as $raw_pid)
            {
                if($raw_root_projectid != $raw_pid) //Do NOT use ===!!!!!
                {
                    $pidcount++;
                    $clean_pid = $pidcount;
                    $clean_map_pids[$raw_pid] = $clean_pid;
                } else {
                    //The root project will always be #1!
                    $clean_map_pids[$raw_pid] = 1;
                }
            }
            $widcount = 1;
            foreach($just_wids_list as $raw_wid)
            {
                if($raw_root_workitemid != $raw_wid) //Do NOT use ===!!!!!
                {
                    $widcount++;
                    $clean_wid = $widcount;
                    $clean_map_wids[$raw_wid] = $clean_wid;
                } else {
                    //The root workitem will always be #1!
                    $clean_map_wids[$raw_wid] = 1;
                }
            }
            $clean_root_workitemid = $clean_map_wids[$raw_root_workitemid];
            $mymetadata['root_workitemid'] = $clean_root_workitemid;
            foreach($raw_workitems as $wid=>$detail)
            {
                $clean_wid = $clean_map_wids[$wid];
                $clean_detail = $detail;
                $raw_pid = $detail['owner_template_projectid'];
                $clean_detail['owner_template_projectid'] = $clean_map_pids[$raw_pid];
                $clean_daws = [];
                foreach($detail['maps']['daw'] as $raw_antwid)
                {
                    $clean_antwid = $clean_map_wids[$raw_antwid];
                    $clean_daws[] = $clean_antwid;
                }
                $clean_detail['maps']['daw'] = $clean_daws;
                $clean_workitems[$clean_wid] = $clean_detail;
            }
            foreach($raw_workitemid2projectid as $wid=>$pid)
            {
                $clean_wid = $clean_map_wids[$wid];
                $clean_pid = $clean_map_pids[$pid];
                $clean_workitemid2projectid[$clean_wid] = $clean_pid;
            }
            
            $maps['relevant_roles'] = $relevant_roles;
            $maps['workitemid2projectid'] = $clean_workitemid2projectid;
            
            $everything['metadata'] = $mymetadata;
            $everything['workitems'] = $clean_workitems;
            $everything['master_maps'] = $maps;

            return $everything;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Dump entire data into a JSON format.
     * @return JSON string
     */
    public function getTemplateProjectInfoAsJSON($publishedrefname
            ,$aExtraTopLevelAttribs
            ,$sExtraContextNoteText=NULL
            )
    {
        try
        {
            
            global $user;
            $this_uid = $user->uid;
            $this_username = $user->name;
            
            $filetype_tx = "JSON";
            $format_tx = 'BFTF1';
            $everything = $this->getTemplateProjectInfoMaps($publishedrefname,$aExtraTopLevelAttribs,$sExtraContextNoteText,$filetype_tx,$format_tx);
            
            //We are done!
            return json_encode($everything);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getText4JSONField($raw_value)
    {
        $clean1 = str_replace("\n", '\\n', trim($raw_value));
        $clean2 = str_replace("\t", '\\t', $clean1);
        return $clean2;
    }
    
    /**
     * Dump entire data into a TABTEXT format.
     * @return TABTEXT string
     */
    public function getTemplateProjectInfoAsTABTEXT($publishedrefname
            ,$aExtraTopLevelAttribs
            ,$sExtraContextNoteText=NULL
            )
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $this_username = $user->name;
            
            $clean_publishedrefname = strtolower(trim($publishedrefname));
            if(empty($clean_publishedrefname))
            {
                throw new \Exception("Missing required reference name in template publish attempt by {$this_username}!");
            }
            $filetype_tx = "TXT";
            $format_tx = 'BFTF1';
            $everything = $this->getTemplateProjectInfoMaps($clean_publishedrefname,$aExtraTopLevelAttribs,$sExtraContextNoteText,$filetype_tx,$format_tx);
            $tabtext_rows = [];

            //Declare our dedicated blocks collection
            $dedicated_blocks = [];
            $dedicated_blocks['MISSION_TX']=[];
            $dedicated_blocks['SUBMITTER_BLURB_TX']=[];
            
            //Format the top level project template info
            $metadata_map = $everything['metadata'];
            //ksort($metadata_map);
            $one_labelrow = array('PROJECT_TEMPLATE');
            $one_datarow = array('DATA ROW>>');
            foreach($metadata_map as $k=>$v)
            {
                $clean_k = strtoupper(trim($k));
                if(key_exists($clean_k, $dedicated_blocks))
                {
                    //Dedicated block entry
                    $dedicated_blocks[$clean_k] = trim($v);
                } else {
                    //Simple top level entry
                    if(!is_array($v))
                    {
                        $one_labelrow[] = $clean_k;
                        $one_datarow[] = $this->getText4JSONField($v);
                    } else {
                        foreach($v as $vk=>$vv)
                        {
                            $clean_vk = strtoupper(trim($vk));
                            $one_labelrow[] = "$clean_k:$clean_vk";
                            $one_datarow[] = $this->getText4JSONField($vv);
                        }
                    }
                }
            }
            $tabtext_rows[] = implode("\t", $one_labelrow);
            $tabtext_rows[] = implode("\t", $one_datarow);

            //Write the dedicated blocks
            foreach($dedicated_blocks as $k=>$v)
            {
                $tabtext_rows[] = "BEGIN\tMETADATA\t{$k}";
                if(!is_array($v))
                {
                    $tabtext_rows[] = "$v";
                } else {
                    $tabtext_rows[] = print_r($v,TRUE);
                }
                $tabtext_rows[] = "END\tMETADATA\t{$k}";
            }
            
            //Format all the workitem detail
            $workitem_submaps = [];
            $workitems_map = $everything['workitems'];
            $one_wlabelrow = array('WORKITEMS','WID');
            $wdata_rows = [];
            $workitemcount = 0;
            foreach($workitems_map as $wid=>$winfo)
            {
                $one_wdatarow = array('DATA ROW>>',$wid);
                $workitemcount++;
                foreach($winfo as $k=>$v)
                {
                    if(!is_array($v))
                    {
                        if(count($wdata_rows) == 0)
                        {
                            $clean_k = strtoupper(trim($k));
                            $one_wlabelrow[] = $clean_k;
                        }
                        $one_wdatarow[] = $this->getText4JSONField($v);
                    } else {
                        $workitem_submaps[$wid] = $v;
                    }
                }
                $wdata_rows[] = $one_wdatarow;
            }
            $tabtext_rows[] = implode("\t", $one_wlabelrow);
            foreach($wdata_rows as $one_datarow)
            {
                $tabtext_rows[] = implode("\t", $one_datarow);
            }
            
            //Smash submaps
            $smashed_submaps = [];
            foreach($workitem_submaps as $wid=>$submap)
            {
                foreach($submap as $submapname=>$submapcontent)
                {
                    $clean_name = strtoupper(trim($submapname));
                    if(!isset($smashed_submaps[$clean_name]) && !empty($submapcontent))
                    {
                        $smashed_submaps[$clean_name] = [];
                    }
                    //Assuming the workitem submaps are NOT key,value!  If that changes, change this!
                    foreach($submapcontent as $value)
                    {
                        $smashed_submaps[$clean_name][$wid][] = $value;
                    }
                }
            }
            
            //Format all the submaps
            $submap_out_rows = [];
            $dawcount=0;
            foreach($smashed_submaps as $submapname=>$pairs)
            {
                $clean_name = strtoupper(trim($submapname));
                $submap_out_rows[] = array("WORKITEM_SUBMAP_NAME:$clean_name");
                foreach($pairs as $k=>$v)
                {
                    if(!is_array($v))
                    {
                        $submap_out_rows[] = array(
                            'DATA ROW>>', $k, $v
                            );
                        if($clean_name === 'DAW')
                        {
                            $dawcount++;
                        }
                    } else {
                        foreach($v as $rv)
                        {
                            $submap_out_rows[] = array(
                                'DATA ROW>>', $k, $rv
                                );
                            if($clean_name === 'DAW')
                            {
                                $dawcount++;
                            }
                        }
                    }
                }
                foreach($submap_out_rows as $one_datarow)
                {
                    $tabtext_rows[] = implode("\t", $one_datarow);
                }
            }
            
            //Format all the master maps content
            $map_out_rows = [];
            $master_maps = $everything['master_maps'];
            foreach($master_maps as $mapname=>$mapinfo)
            {
                $map_out_rows[] = array('MAP_' . trim(strtoupper($mapname)));
                foreach($mapinfo as $k=>$v)
                {
                    $map_out_rows[] = array(
                        'DATA ROW>>', $k, $v
                        );
                }
            }
            foreach($map_out_rows as $one_datarow)
            {
                $tabtext_rows[] = implode("\t", $one_datarow);
            }
            
            $last_row = "PROJECT_TEMPLATE_END\tWORKITEM_COUNT=$workitemcount\tDAW_LINK_COUNT=$dawcount";
            $tabtext_rows[] = $last_row;
            
            $tabtext_tx = implode("\n", $tabtext_rows);
            //return print_r($everything,TRUE);
            //We are done!
            return $tabtext_tx;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
