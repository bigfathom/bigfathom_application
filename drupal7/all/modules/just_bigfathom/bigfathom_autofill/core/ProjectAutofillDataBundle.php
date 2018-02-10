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

namespace bigfathom_autofill;

/**
 * A bundle of information for autofill of a project
 *
 * @author Frank
 */
class ProjectAutofillDataBundle
{
    protected $m_projectid = NULL;
    protected $m_max_duration_allowed = BIGFATHOM_MAX_AUTOFILL_SECONDS;
    protected $m_max_allowed_passes = BIGFATHOM_MAX_AUTOFILL_ITERATIONS;
    protected $m_started_dt = NULL;
    protected $m_completed_dt = NULL;
    protected $m_created_by_personid = NULL;
    protected $m_action_status = NULL;
    protected $m_workitems_to_process_ct = NULL;
    protected $m_total_updated_workitems = NULL;
    
    protected $m_initialized_yn = NULL;
    
    protected $m_completed_passes = 0;
    
    protected $m_timestamp_class_created = NULL;
    protected $m_timestamp_start_autofill = NULL;
    protected $m_timestamp_completed_passes_ar = NULL;
    protected $m_timestamp_done_autofill = NULL;
    
    protected $m_oMasterDailyDetail = NULL;
    protected $m_oSmartProjectForecaster = NULL;
    
    protected $m_networkbundle = NULL;
    protected $m_workitem_changes_cache = NULL;
    
    protected $m_all_workitem_date_and_effort = NULL;
    protected $m_all_workitem_maps = NULL;
    
    protected $m_locked_root_start_dt = NULL;
    protected $m_locked_root_end_dt = NULL;
            
    protected $m_oBUI = NULL;
    
    public function getLockedRootStartDate()
    {
        return $this->m_locked_root_start_dt;
    }
    
    public function getLockedRootEndDate()
    {
        return $this->m_locked_root_end_dt;
    }
    
    public function __toString()
    {
        $nice_text_ar = [];
        $nice_text_ar[] = "Databundle for project#{$this->m_projectid} initialized_yn={$this->m_initialized_yn}";
        
        $nice_text_ar[] = "" . $this->m_oMasterDailyDetail;
        
        $nice_text = implode("\n<br>>>>",$nice_text_ar);
        return $nice_text;
    } 
    
    public function __construct($projectid)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        $this->m_projectid = $projectid;
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        global $user;
        $this->m_created_by_personid = $user->uid;
        
        $this->m_timestamp_class_created = microtime(TRUE);
        $this->m_timestamp_completed_passes_ar = [];
        
        $loaded_masterdd = module_load_include('php','bigfathom_utilization','core/MasterDailyDetail');
        if(!$loaded_masterdd)
        {
            throw new \Exception('Failed to load the bigfathom_utilization MasterDailyDetail class');
        }

        $this->m_all_workitem_date_and_effort = [];
        $this->m_all_workitem_maps = [];
        
        $this->m_oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($this->m_projectid);
        $this->m_networkbundle = NULL;
        $this->m_workitem_changes_cache = [];
        $this->m_initialized_yn = 0;
    }
    
    public function setBUI($oBUI)
    {
        $this->m_oBUI = $oBUI;
    }
    
    public function isReadyForComputations()
    {
        return $this->m_initialized_yn;
    }
    
    /**
     * Call this to initialize prior to using for computing results
     */
    public function initialize($networkbundle, $all_changeable_workitems)
    {
        try
        {
            if(empty($networkbundle['root']))
            {
                throw new \Exception("Network bundle is missing root information!");
            }
            if(empty($networkbundle['root']['wid']))
            {
                throw new \Exception("Network bundle is missing root wid value!");
            }
            if(empty($networkbundle['metadata']))
            {
                throw new \Exception("Network bundle is missing metadata!");
            }
            
            $this->m_all_workitem_date_and_effort = [];
            $this->m_all_workitem_maps = [];
            
            $this->m_networkbundle = $networkbundle;
            
            $map_personid = [];
            foreach($all_changeable_workitems as $wid=>$raw_winfo)
            {
                $winfo = array(
                    'wid' => !empty($raw_winfo['wid']) ? $raw_winfo['wid'] : $raw_winfo['id'],
                    'owner_personid' => $raw_winfo['owner_personid'],
                    'owner_projectid' => $raw_winfo['owner_projectid'],
                    'actual_start_dt' => $raw_winfo['actual_start_dt'],
                    'actual_end_dt' => $raw_winfo['actual_end_dt'],
                    'planned_start_dt' => $raw_winfo['planned_start_dt'],
                    'planned_end_dt' => $raw_winfo['planned_end_dt'],
                    'reh' => !empty($raw_winfo['reh']) ? $raw_winfo['reh'] : $raw_winfo['remaining_effort_hours'],
                    'sdt' => !empty($raw_winfo['sdt']) ? $raw_winfo['sdt'] : (!empty($raw_winfo['actual_start_dt']) ? $raw_winfo['actual_start_dt'] : $raw_winfo['planned_start_dt']),
                    'edt' => !empty($raw_winfo['edt']) ? $raw_winfo['edt'] : (!empty($raw_winfo['actual_end_dt']) ? $raw_winfo['actual_end_dt'] : $raw_winfo['planned_end_dt']),
                );
                $this->m_all_workitem_maps[$wid] = $raw_winfo['maps'];
                $this->m_all_workitem_date_and_effort[$wid] = $winfo;
                
                $personid = $winfo['owner_personid'];
                $map_personid[$personid] = $personid;
            }

            $this->m_locked_root_start_dt = $networkbundle['metadata']['in_project']['min_locked_root_start_dt'];
            $this->m_locked_root_end_dt = $networkbundle['metadata']['in_project']['max_locked_root_end_dt'];
            
            $min_locked_start_dt = $networkbundle['metadata']['in_project']['min_locked_start_dt'];
            $max_locked_end_dt = $networkbundle['metadata']['in_project']['max_locked_end_dt'];
            
            $root_sdt = $networkbundle['root']['sdt'];
            $root_edt = $networkbundle['root']['edt'];
            if(!empty($min_locked_start_dt) && !empty($root_sdt))
            {
                $sdt = min($min_locked_start_dt, $root_sdt);
            } else {
                if(!empty($min_locked_start_dt))
                {
                    $sdt = $min_locked_start_dt;
                } else {
                    $sdt = $root_sdt;
                }
            }
            
            if(!empty($max_locked_end_dt) && !empty($root_edt))
            {
                $edt = max($max_locked_end_dt, $root_edt);
            } else {
                if(!empty($max_locked_end_dt))
                {
                    $edt = $max_locked_end_dt;
                } else {
                    $edt = $root_edt;
                }
            }
            
            $this->m_oMasterDailyDetail->initialize($map_personid, $sdt, $edt);
            $this->m_initialized_yn = 1;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getRootWorkitemID()
    {
        return $this->m_networkbundle['root']['wid'];
    }
    
    public function getDateAndRemainingEffortHoursInfoOfWorkitem($wid)
    {
        try
        {
            $wdetail = $this->m_all_workitem_date_and_effort[$wid];
            
            $psdt = $wdetail['planned_start_dt'];
            $pedt = $wdetail['planned_end_dt'];
            $asdt = $wdetail['actual_start_dt'];
            $aedt = $wdetail['actual_end_dt'];
            $reh = isset($wdetail['remaining_effort_hours']) ? $wdetail['remaining_effort_hours'] : (isset($wdetail['reh']) ? $wdetail['reh'] : NULL);
            
            $winfo = [];
            $winfo['planned_start_dt'] = $psdt;
            $winfo['planned_end_dt'] = $pedt;
            $winfo['actual_start_dt'] = $asdt;
            $winfo['actual_end_dt'] = $aedt;
            $winfo['reh'] = $reh;
            $winfo['sdt'] = !empty($wdetail['sdt']) ? $wdetail['sdt'] : (!empty($asdt) ? $asdt : $psdt);
            $winfo['edt'] = !empty($wdetail['edt']) ? $wdetail['edt'] : (!empty($aedt) ? $aedt : $pedt);
            //$winfo['effective_sdt'] = $winfo['sdt'];    //Because old code still looks for effective_sdt instead of sdt
            //$winfo['effective_edt'] = $winfo['edt'];    //Because old code still looks for effective_edt instead of edt
            return $winfo;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return array of arrays with all paths to all the leafs of the tree
     * starting from the provided workitem
     */
    public function getPathsFromWorkitemToLeafAsTree($wid)
    {
        try
        {
            $path = array($wid=>[]);
            $daw = $this->m_all_workitem_maps[$wid]['daw'];
            if(is_array($daw) && count($daw) > 0)
            {
                foreach($daw as $ant_wid)
                {
                    $path[$wid][$ant_wid] = $this->getPathsFromWorkitemToLeafAsTree($ant_wid);
                }
            }
            return $path;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getPathsFromWorkitemToLeafAsFlatList($wid)
    {
        try
        {
            $path_list = [];
            $daw  = $this->m_all_workitem_maps[$wid]['daw'];
            if(is_array($daw) && count($daw) > 0)
            {
                foreach($daw as $ant_wid)
                {
                    $list_from_ant = $this->getPathsFromWorkitemToLeafAsFlatList($ant_wid);
                    foreach($list_from_ant as $one_antlist)
                    {
                        $path_list[] = array_merge(array($wid),$one_antlist);
                    }
                }
            } else {
                $path_list[] = array($wid);
            }
            return $path_list;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getPathsToWorkitemAsFlatList($wid)
    {
        try
        {
            $leaf2path_list = [];
            $path2leaf_list = $this->getPathsFromWorkitemToLeafAsFlatList($wid);
            foreach($path2leaf_list as $one_path)
            {
                $leaf2path_list[] = array_reverse($one_path);
            }
            return $leaf2path_list;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function clearRelevantWorkitemCache()
    {
        $this->m_oMasterDailyDetail->clearRelevantWorkitemCache();
    }
    
    public function getOneWorkFitInsightBundle($winfo, $personid_override=NULL)
    {
        return $this->m_oMasterDailyDetail->getOnePersonWorkFitInsightBundle($winfo, $personid_override);
    }
    
    public function getMasterDailyDetailInstance()
    {
        return $this->m_oMasterDailyDetail;
    }
    
    public function hasRelevantWorkitemRecord($wid)
    {
        return $this->m_oMasterDailyDetail->hasRelevantWorkitemRecord($wid);
    }
    
    public function getRelevantWorkitemRecord($wid)
    {
        //return $this->m_workitem_changes_cache[$wid];
        return $this->m_oMasterDailyDetail->getRelevantWorkitemRecord($wid);
    }

    public function markWorkitemRecordForDatabaseUpdate($wid, $cleanupdatefields=NULL)
    {
        if(empty($wid))
        {
            throw new \Exception("Missing value for required wid!");
        }
        $winfo = $this->m_oMasterDailyDetail->getRelevantWorkitemRecord($wid);
        $winfo['changed_fields'] = $cleanupdatefields != NULL ? $cleanupdatefields : [];
        if(count($cleanupdatefields) > 0)
        {
            $this->m_workitem_changes_cache[$wid] = $winfo; //Until we write the data to DB
        }
    }
    
    public function saveUpdatesToDatabase($count_threshhold=1)
    {
        $updatedcount = 0;
        try
        {
            if($count_threshhold <= count($this->m_workitem_changes_cache))
            {
                //Write them all now
                $updated_dt = date("Y-m-d H:i", time());
                foreach($this->m_workitem_changes_cache as $wid=>$winfo)
                {
                    $changed_fields = $winfo['changed_fields'];
                    $changed_fields['updated_dt'] = $updated_dt;
                    db_update(\bigfathom\DatabaseNamesHelper::$m_workitem_tablename)
                        ->fields($changed_fields)
                            ->condition('id', $wid)
                            ->execute();
                    $updatedcount++;
                }
                $this->m_workitem_changes_cache = [];
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function setRelevantWorkitemRecord($wid, $raw_winfo)
    {
        try
        {
            $winfo = []; 
            $winfo['wid'] = $wid;

            $winfo['owner_projectid'] = $raw_winfo['owner_projectid'];
            $winfo['owner_personid'] = $raw_winfo['owner_personid'];

            $winfo['planned_start_dt'] = isset($raw_winfo['planned_start_dt']) ? $raw_winfo['planned_start_dt'] : NULL;
            $winfo['planned_end_dt'] = isset($raw_winfo['planned_end_dt']) ? $raw_winfo['planned_end_dt'] : NULL;
            $winfo['actual_start_dt'] = isset($raw_winfo['actual_start_dt']) ? $raw_winfo['actual_start_dt'] : NULL;
            $winfo['actual_end_dt'] = isset($raw_winfo['actual_end_dt']) ? $raw_winfo['actual_end_dt'] : NULL;

            if(array_key_exists('remaining_effort_hours' , $winfo ))
            {
                $winfo['remaining_effort_hours'] = isset($raw_winfo['remaining_effort_hours']) ? $raw_winfo['remaining_effort_hours'] : NULL;
                $winfo['reh'] = $winfo['remaining_effort_hours'];
            } else {
                $winfo['reh'] = isset($raw_winfo['reh']) ? $raw_winfo['reh'] : NULL;
                $winfo['remaining_effort_hours'] = $winfo['reh'];
            }

            if(!empty($raw_winfo['sdt']))
            {
                $winfo['sdt'] = $raw_winfo['sdt'];
            } else {
                $winfo['sdt'] = !empty($raw_winfo['actual_start_dt']) ? $raw_winfo['actual_start_dt'] : !empty($raw_winfo['planned_start_dt']) ? $raw_winfo['planned_start_dt'] : NULL;
            }
            if(!empty($raw_winfo['edt']))
            {
                $winfo['edt'] = $raw_winfo['edt'];
            } else {
                $winfo['edt'] = !empty($raw_winfo['actual_end_dt']) ? $raw_winfo['actual_end_dt'] : !empty($raw_winfo['planned_end_dt']) ? $raw_winfo['planned_end_dt'] : NULL;
            }

            if(isset($raw_winfo['autofill_info']))
            {
                $winfo['autofill_info'] = $raw_winfo['autofill_info'];
            }

            $this->m_all_workitem_date_and_effort[$wid] = $winfo;
            if(!empty($raw_winfo['maps']['daw']))
            {
                $this->m_all_workitem_maps[$wid] = $raw_winfo['maps'];
            }
            $this->m_oMasterDailyDetail->setRelevantWorkitemRecord($wid, $winfo);
            $this->m_oBUI->declareRelevantWorkitemRecordChanged($wid);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getNetworkMapBundle()
    {
        return $this->m_networkbundle;
    }
    
    public function getValuesForTrackingRecordCreate($workitems_to_process_ct=NULL)
    {
        $values4record = [];
        
        $this->m_timestamp_start_autofill = microtime(TRUE);
        
        $this->m_action_status = 1;
        
        $this->m_workitems_to_process_ct = $workitems_to_process_ct;
        if($this->m_workitems_to_process_ct !== NULL)
        {
            $values4record['workitems_to_process_ct'] = $this->m_workitems_to_process_ct;
        }
        
        $values4record['projectid'] = $this->m_projectid;
        $values4record['max_duration_allowed'] = $this->m_max_duration_allowed;
        $values4record['action_status'] = $this->m_action_status;
        $values4record['created_by_personid'] = $this->m_created_by_personid;
        
        return $values4record;
    }
    
    private function getTimeSinceLastCompletion()
    {
        $cp = count($this->m_timestamp_completed_passes_ar);
        if($cp == 0)
        {
            throw new \Exception("No passes yet completed!");
        }
        if($cp == 1)
        {
            //Only one so far, take diff from start
            $started_ts = $this->m_timestamp_start_autofill;
            $ended_ts = $this->m_timestamp_completed_passes_ar[0];
        } else {
            $started_ts = $this->m_timestamp_completed_passes_ar[$cp-2];
            $ended_ts = $this->m_timestamp_completed_passes_ar[$cp-1];
        }
        return $ended_ts - $started_ts;
    }
    
    public function getValuesForTrackingRecordPassCompletedUpdate($mark_completion=TRUE)
    {
        $values4record = [];
        
        if($mark_completion)
        {
            //Treat this call as a new completion
            $this->m_timestamp_completed_passes_ar[] = microtime(TRUE);
            $this->m_completed_passes += 1;
        }
        $duration = $this->getTimeSinceLastCompletion();
        $values4record['completed_passes'] = $this->m_completed_passes;
        $values4record['duration_last_pass'] = $duration;
        if($this->m_completed_passes <= 3)
        {
            $values4record['duration_pass' . trim($this->m_completed_passes)] = $duration;
        }
        
        return $values4record;
    }
    
    public function getValuesForTrackingRecordSuccessfulCompletionUpdate()
    {
        $values4record = $this->getValuesForTrackingRecordPassCompletedUpdate();
        
        $cp = count($this->m_timestamp_completed_passes_ar);
        $this->m_timestamp_done_autofill = $this->m_timestamp_completed_passes_ar[$cp-1];
        $duration = $this->m_timestamp_done_autofill - $this->m_timestamp_start_autofill;
        
        $values4record['total_duration'] = $duration;
        $values4record['total_updated_workitems'] = $this->m_total_updated_workitems;
        
        return $values4record;
    }
    
    public function getAllCurrentValues()
    {
        $values4record = [];
        
        $this->m_timestamp_completed_passes_ar[] = microtime(TRUE);
        $this->m_completed_passes += 1;
        $duration = $this->getTimeSinceLastCompletion();
        
        $values4record['completed_passes'] = $this->m_completed_passes;
        $values4record['duration_last_pass'] = $duration;
        if($this->m_completed_passes <= 3)
        {
            $values4record['duration_pass' . trim($this->m_completed_passes)] = $duration;
        }
        
        return $values4record;
    }
    
    public function setUpdatedWorkitemCount($total_updated_workitems)
    {
        $this->m_total_updated_workitems = $total_updated_workitems;
    }
    
}
