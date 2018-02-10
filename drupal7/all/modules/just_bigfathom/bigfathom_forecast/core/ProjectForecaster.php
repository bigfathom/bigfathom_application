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
 */

namespace bigfathom_forecast;

require_once 'ConfidenceScores.php';

/**
 * This class helps forecast outcomes
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectForecaster
{
    private $m_projectid = NULL;
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oLLH = NULL;
    private $m_oCScores = NULL;
    private $m_iteration_counter = 0;
    private $m_project_bundle = NULL;
    private $m_history_stack = NULL;
    private $m_now_timestamp = NULL;
    private $m_ant_projectforecasters = NULL;
    private $m_flags_ar = NULL;
    
    private $m_today_dt = NULL; //Because today may be bigger than our min date
    private $m_min_reference_dt = NULL;
    private $m_max_reference_dt = NULL;
    
    private $m_flag_availability_type_BY_OWNER = NULL;
    
    private $m_overrides_ar = NULL;
    private $m_limit_end_dt = NULL;

    private $m_bottomup_calcs = NULL;
    private $m_wid2forecast = NULL;
    
    private $m_projects2exclude;   //Ignores existing utilization assignments for these
    private $m_map_wid_allocations2ignore;   //Ignores existing utilization assignments for these
    private $m_workitem2compute;    //Stop after computing this one
    private $m_just_evaluate_yn;
    
    public function __construct($projectid, $flags_ar=NULL, $projectid_history_stack=NULL, $overrides_ar=NULL)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }

        $loaded_dbh = module_load_include('php','bigfathom_core','core/DatabaseNamesHelper');
        if(!$loaded_dbh)
        {
            throw new \Exception('Failed to load the core DatabaseNamesHelper class');
        }
        $loaded_llh = module_load_include('php','bigfathom_core','core/LinkLogicHelper');
        if(!$loaded_llh)
        {
            throw new \Exception('Failed to load the core LinkLogicHelper class');
        }
        $loaded_pui = module_load_include('php','bigfathom_core','core/PersonUtilizationInsight');
        if(!$loaded_pui)
        {
            throw new \Exception('Failed to load the core DEPRECATED PersonUtilizationInsight class');
        }
        $loaded_mh = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded_mh)
        {
            throw new \Exception('Failed to load the core MapHelper class');
        }
        
        $this->m_instance_createded_ts = time();
        //drupal_set_message("LOOK FORECASTER create start {$this->m_instance_createded_ts}");
        
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oLLH = new \bigfathom\LinkLogicHelper();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oCScores = new ConfidenceScores();
        
        if(empty($flags_ar) || !is_array($flags_ar))
        {
            $flags_ar = [];
        }
        $this->m_flags_ar = $flags_ar;
        $this->m_just_evaluate_yn = isset($flags_ar['just_evaluate_yn']) ? $flags_ar['just_evaluate_yn'] : 0;
        $this->m_flag_availability_type_BY_OWNER = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_availability_type','BY_OWNER',TRUE);
        $this->m_min_reference_dt = isset($flags_ar['min_initial_reference_dt']) ? $flags_ar['min_initial_reference_dt'] : NULL;
        $this->m_max_reference_dt = isset($flags_ar['max_initial_reference_dt']) ? $flags_ar['max_initial_reference_dt'] : NULL;
        $this->m_today_dt = isset($flags_ar['today_dt']) ? $flags_ar['today_dt'] : NULL;
        
        $this->m_map_wid_allocations2ignore = isset($flags_ar['workitems2exclude']) ? $flags_ar['workitems2exclude'] : [];
        $this->m_workitem2compute = isset($flags_ar['workitem2compute']) ? $flags_ar['workitem2compute'] : NULL;

        $this->m_min_pct_buffer = isset($flags_ar['min_pct_buffer']) ? $flags_ar['min_pct_buffer'] : 0;
        $this->m_strict_min_pct = isset($flags_ar['strict_min_pct']) ? $flags_ar['strict_min_pct'] : FALSE;
        $this->m_force_lock_project_start_dt = isset($flags_ar['force_lock_project_start_dt']) ? $flags_ar['force_lock_project_start_dt'] : FALSE;

        $this->m_utilization_planning_bundle = isset($flags_ar['utilization_planning_bundle']) ? $flags_ar['utilization_planning_bundle'] : NULL;
        $this->m_candidate_pct_bundle = isset($flags_ar['candidate_pct_bundle']) ? $flags_ar['candidate_pct_bundle'] : NULL;
        
        $this->m_projects2exclude = $this->m_oMapHelper->getBrainstormProjectIDs(); //TODO factor this in!!!!!!
        
        if(empty($this->m_today_dt))
        {
            $now_timestamp = time();
            $this->m_today_dt = gmdate("Y-m-d",$now_timestamp);
        }
        if(empty($this->m_min_reference_dt))
        {
            $this->m_min_reference_dt = $this->m_today_dt;
        }
        if(empty($this->m_max_reference_dt))
        {
            $this->m_max_reference_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_today_dt, 444);
        } 
        if($this->m_max_reference_dt < $this->m_today_dt)
        {
            $this->m_max_reference_dt = $this->m_today_dt;
        }
        if($this->m_min_reference_dt > $this->m_max_reference_dt)   
        {
            throw new \Exception("Error with reference dates! [{$this->m_min_reference_dt}] > [{$this->m_max_reference_dt}]");
        }
        if(empty($overrides_ar) || !is_array($overrides_ar))
        {
            $overrides_ar = [];
        }
        $this->m_overrides_ar = $overrides_ar;
        
        if(!empty($overrides_ar["now_timestamp"]))
        {
            $this->m_now_timestamp = $overrides_ar["now_timestamp"];
        } else {
            $this->m_now_timestamp = time();
        }
        if(!empty($overrides_ar['end_dt']))
        {
            $this->m_limit_end_dt = $overrides_ar['end_dt'];
        } else {
            $this->m_limit_end_dt = NULL;
        }
        
        $this->m_now_dt = gmdate("Y-m-d", $this->m_now_timestamp);
        
        $this->m_ant_projectforecasters = [];
        $this->m_wid2forecast = [];
        
        if($projectid_history_stack === NULL)
        {
            $this->m_history_stack = [];
        } else {
            $this->m_history_stack = $projectid_history_stack;
        }
        $this->m_history_stack[] = $projectid;
        
        $this->m_iteration_counter = 0;
        
        $this->m_projectid = $projectid;
        
        $this->computeAllForecasts();
        
        $this->m_instance_create_finish_ts = time();
        //$debugdiff = $this->m_instance_create_finish_ts - $this->m_instance_create_finish_ts;
        //drupal_set_message("LOOK FORECASTER create finished {$this->m_instance_createded_ts} total time {$debugdiff}");
        
    }
    
    /**
     * Propose a reasonable solution
     */
    private function getReasonableSolutionDateBundle($seeking_effort_hours, $owner_personid=NULL
            , $search_start_dt=NULL, $search_start_dt_locked=FALSE
            , $search_end_dt=NULL, $search_end_dt_locked=FALSE
            , $map_wid_allocations2ignore=NULL
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL
            , $max_ant_proj_end_dt=NULL)
    {
        $this->m_instance_start_grsdb_ts = time();
        //drupal_set_message("LOOK FORECASTER started GRSDB {$this->$this->m_instance_start_grsdb_ts}");
        try
        {
            if($map_wid_allocations2ignore === NULL)
            {
                $map_wid_allocations2ignore = $this->m_map_wid_allocations2ignore;
            }
            $ignore_seeking=FALSE;
            if(!empty($search_end_dt) && $search_start_dt > $search_end_dt)
            {
                DebugHelper::showStackTrace('Invalid dates stack trace', 'error', 8);
                throw new \Exception("Invalid dates: end date larger than start date! [$search_start_dt > $search_end_dt] (hours=$seeking_effort_hours)");
            }
            $bundle = $this->getFitMetricsBundle($seeking_effort_hours
                    , $ignore_seeking, $owner_personid
                    , $search_start_dt, $search_start_dt_locked
                    , $search_end_dt, $search_end_dt_locked
                    , $map_wid_allocations2ignore
                    , $min_pct_buffer, $strict_min_pct, $allocation_pct
                    , $max_ant_proj_end_dt);
        $this->m_instance_finish_grsdb_ts = time();
        //drupal_set_message("LOOK FORECASTER finished GRSDB {$this->m_instance_finish_grsdb_ts}");
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the existing solution insight metrics
     */
    private function getExistingSolutionDateBundle($remaining_effort_hours
            , $owner_personid=NULL
            , $start_dt=NULL
            , $end_dt=NULL
            , $map_wid_allocations2ignore=NULL
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL)
    {
        try
        {
            $this->m_instance_start_gesdb_ts = time();
            //drupal_set_message("LOOK FORECASTER started GESDB {$this->m_instance_start_gesdb_ts}");
            if($map_wid_allocations2ignore === NULL)
            {
                $map_wid_allocations2ignore = $this->m_map_wid_allocations2ignore;
            }
            $ignore_seeking=TRUE;
            $search_start_dt_locked=1;
            $search_end_dt_locked=1;
            $bundle = $this->getFitMetricsBundle($remaining_effort_hours,$ignore_seeking,$owner_personid
                , $start_dt, $search_start_dt_locked
                , $end_dt, $search_end_dt_locked
                , $map_wid_allocations2ignore
                , $min_pct_buffer, $strict_min_pct, $allocation_pct);
            $this->m_instance_finish_gesdb_ts = time();
            //drupal_set_message("LOOK FORECASTER finished GESDB {this->m_instance_finish_gesdb_ts}");
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the existing solution insight metrics
     */
    private function getFitMetricsBundle($seeking_effort_hours,$ignore_seeking,$owner_personid=NULL
            , $search_start_dt=NULL, $search_start_dt_locked=FALSE
            , $search_end_dt=NULL, $search_end_dt_locked=FALSE
            , $map_wid_allocations2ignore=NULL
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL
            , $max_ant_proj_end_dt=NULL)
    {
        try
        {
            if($map_wid_allocations2ignore === NULL)
            {
                $map_wid_allocations2ignore = $this->m_map_wid_allocations2ignore;
            }
            if(!is_array($map_wid_allocations2ignore))
            {
                $map_wid_allocations2ignore = [];
            }
            if(!empty($this->m_workitem2compute) && !array_key_exists($this->m_workitem2compute, $map_wid_allocations2ignore))
            {
                //Exclude existing allocations, if any, for the wid we are currently computing
                $map_wid_allocations2ignore[$this->m_workitem2compute] = $this->m_workitem2compute;
            }
            
            $bundle = NULL;
            if(!empty($search_start_dt))
            {
                //Search forward
                $pui = $this->m_people_maps[$owner_personid]['pui'];
                if($search_end_dt_locked)
                {
                    $edt = $search_end_dt;
                } else {
                    if($search_end_dt < $this->m_max_reference_dt)
                    {
                        $edt = $this->m_max_reference_dt;
                    } else {
                        $edt = $search_end_dt;
                    }
                }
                ///////////////////$bundle = $pui->getUtilizationDerivedInsights($search_start_dt,$search_end_dt);
                if(!empty($edt) && $search_start_dt > $edt)
                {
                    //Simply set it to the same day.
                    $local_edt = $search_start_dt;
                } else {
                    $local_edt = $edt;
                }
                if($search_start_dt < $max_ant_proj_end_dt)
                {
                    //Do NOT start before the antecedent project ends
                    $search_start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($max_ant_proj_end_dt, 1);
                }
                $bundle = $pui->getWorkEffortForwardComputedDateBundleOfPerson(
                            $search_start_dt
                            , $local_edt
                            , $seeking_effort_hours
                            , $ignore_seeking
                            , $map_wid_allocations2ignore
                            , $this->m_today_dt, $search_start_dt_locked
                            , $min_pct_buffer, $strict_min_pct, $allocation_pct);
            } else if(!empty($search_end_dt)){
                //Search backward
                $pui = $this->m_people_maps[$owner_personid]['pui'];
                $bundle = $pui->getWorkEffortBackwardComputedDateBundleOfPerson(
                            $search_end_dt
                            , $this->m_min_reference_dt
                            , $seeking_effort_hours
                            , $ignore_seeking
                            , $map_wid_allocations2ignore
                            , $this->m_today_dt, $search_end_dt_locked
                            , $min_pct_buffer, $strict_min_pct, $allocation_pct);
            } else {
                throw new \Exception("Missing both start search date and end search date!");
            }
            
            $start_dt_changed_yn = ($search_start_dt != $bundle['solution']['start_dt']) ? 1 : 0;
            $bundle['solution']['start_dt_changed_yn'] = $start_dt_changed_yn;
            $bundle['solution']['use_new_start_dt_yn'] = (!$search_start_dt_locked && $start_dt_changed_yn) ? 1 : 0;
            
            $end_dt_changed_yn = ($search_end_dt != $bundle['solution']['end_dt']) ? 1 : 0;
            $bundle['solution']['end_dt_changed_yn'] = $end_dt_changed_yn;
            $bundle['solution']['use_new_end_dt_yn'] = (!$search_end_dt_locked && $end_dt_changed_yn) ? 1 : 0;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function computeAllForecasts()
    {
        try
        {
            $this->m_project_bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_projectid);
            $this->m_ant_projectforecasters = [];
            $ant_proj_bundle = $this->m_oMapHelper->getSubprojects2ProjectMapBundle($this->m_projectid);
            $subproject2root_goalid = $ant_proj_bundle['subproject2root_goalid'];
            foreach($subproject2root_goalid as $opid=>$wid)
            {
                //Create a forecaster instance for the ant proj
                if(!$this->m_oMapHelper->isProjectAvailable($opid))
                {
                    drupal_set_message("Antecedent projectid#$opid is no longer available",'warning');
                    //\bigfathom\DebugHelper::showNeatMarkup($ant_proj_bundle,"LOOK At the suproject mapping on $opid");
                } else {
                    //drupal_set_message("Antecedent projectid#$opid IS available",'status');
                    $forecaster = new \bigfathom_forecast\ProjectForecaster($opid);
                    $this->m_ant_projectforecasters[$opid] = $forecaster;
                    $ant_projectinfo = $forecaster->getForecastSummaryInfo();
                    $this->m_wid2forecast[$wid] =  $ant_projectinfo['forecast'];
                }
            }
            if(!empty($this->m_project_bundle['dates']['min_dt']) && $this->m_project_bundle['dates']['min_dt'] < $this->m_min_reference_dt)
            {
                //Bump down our min
                $this->m_min_reference_dt = $this->m_project_bundle['dates']['min_dt'];
            }
            if(!empty($this->m_project_bundle['dates']['max_dt']) && $this->m_project_bundle['dates']['max_dt'] > $this->m_max_reference_dt)
            {
                //Bump up our max
                $this->m_max_reference_dt = $this->m_project_bundle['dates']['max_dt'];
            }

            $this->m_status_lookup = $this->m_project_bundle['status_lookup'];
            $this->m_sprint_lookup = $this->m_project_bundle['sprint_lookup'];
            $this->m_people_maps = $this->m_project_bundle['people_maps'];

            if(empty($this->m_project_bundle['metadata']['root_goalid']))
            {
                DebugHelper::showStackTrace('MISSING root goal id in the metadata','error');
                DebugHelper::showNeatMarkup($this->m_project_bundle,'MISSING root goal id in the metadata','error');
                throw new \Exception("Missing root goalid in metadata! >>> " . print_r($this->m_project_bundle['metadata'],TRUE));
            }
            
            $root_goalid = $this->m_project_bundle['metadata']['root_goalid'];
            $this->root_goalid = $root_goalid;
            
            //Compute all the existing person obligations and availabilities
            foreach($this->m_people_maps as $personid=>$detail)
            {
                if(!isset($detail['pui']))
                {
                    $this->m_people_maps[$personid]['pui'] = new \bigfathom\DEPRECATED_OLD_PersonUtilizationInsight(
                             $personid,$this->m_min_reference_dt
                            ,$this->m_max_reference_dt);
                }
            }
            
            //Compute all the top-down info
            $this->computeTopDownWorkitemStats($root_goalid);
            foreach($this->m_project_bundle['workitems'] as $wid=>$detail)
            {
                if(!isset($this->m_project_bundle['workitems'][$wid]['computed_bounds']['topdown']))
                {
                    //Missed this one (happens for disconnected items)
                    $this->computeTopDownWorkitemStats($wid);
                }
            }
            
            //Now compute the bottom-up info starting from the leaves
            foreach($this->m_project_bundle['leafmap'] as $wid)
            {
                $this->computeBottomUpWorkitemStats($wid);
            }

            //Now compute all the OTSP
            $this->computeAllOTSP();
            
            //Write results to the main object
            $this->m_ant_projectforecasters = [];
            foreach($this->m_project_bundle['workitems'] as $wid=>$detail)
            {
                $opid = $detail['owner_projectid'];
                /*
                if($opid != $this->m_projectid)
                {
                   $this->m_ant_projectforecasters[$opid] = new \bigfathom_forecast\ProjectForecaster($opid);
                }
                */
                if(!isset($this->m_project_bundle['workitems'][$wid]['computed_bounds']['bottomup']))
                {
                    $reducedinfo = [];
                    if(!empty($this->m_bottomup_calcs[$wid]))   //Because we shortcircut on purpose
                    {
                        foreach($this->m_bottomup_calcs[$wid] as $key=>$value)
                        {
                            if($key != 'inbounds')
                            {
                                $reducedinfo[$key] = $value;
                            }
                        }
                    }
                    $this->m_project_bundle['workitems'][$wid]['computed_bounds']['bottomup'] = $reducedinfo;                   
                }   
            }
        } 
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
    
    /**
     * Call this after all the local position values have been set.
     */
    private function computeAllOTSP($currentwid=NULL)
    {
        try
        {
            if(empty($currentwid))
            {
                foreach($this->m_project_bundle['leafmap'] as $wid)
                {
                    $wdetail = $this->m_project_bundle['workitems'][$wid];
                    $opid = $wdetail['owner_projectid'];
                    if(!empty($this->m_limit_end_dt))
                    {
                        $wdetail['limit_edt'] = $this->m_limit_end_dt;
                    }
                    $status_cd = $wdetail['status_cd'];
                    $status_detail = $this->m_status_lookup[$status_cd];
                    $owner_personid = $wdetail['owner_personid'];
                    $self_allow_dep_overlap_hours = $wdetail['self_allow_dep_overlap_hours'];
                    $self_allow_dep_overlap_pct = $wdetail['self_allow_dep_overlap_pct'];
                    $ant_sequence_allow_overlap_hours = $wdetail['ant_sequence_allow_overlap_hours'];
                    $ant_sequence_allow_overlap_pct = $wdetail['ant_sequence_allow_overlap_pct'];
                    $dep_info = [];
                    $dep_info['settings']['self_allow_dep_overlap_hours'] = $self_allow_dep_overlap_hours;
                    $dep_info['settings']['self_allow_dep_overlap_pct'] = $self_allow_dep_overlap_pct;
                    $dep_info['settings']['ant_sequence_allow_overlap_hours'] = $ant_sequence_allow_overlap_hours;
                    $dep_info['settings']['ant_sequence_allow_overlap_pct'] = $ant_sequence_allow_overlap_pct;
                    $local_scf_logic = [];
                    $ant_scf_logic = [];
                    $pui = $this->m_people_maps[$owner_personid]['pui'];
                    
                    $ant_info = [];
                    $zero_otsp_ants_ar = [];
                    $ant_effective_otsp = 1;
                    $ant_count = 0;
                    $ant_min_end_dt = NULL;
                    if(count($wdetail['maps']['dap']) > 0)
                    {
                        //Has subproject antecendent(s)
                        foreach($wdetail['maps']['dap'] as $ant_wid=>$ant_opid)
                        {
                            $ant_count++;
                            //$ant_wdetail = $this->m_project_bundle['workitems'][$ant_wid];
                            if(empty($this->m_bottomup_calcs[$ant_wid]['forecast']))
                            {
                                if(empty($this->m_wid2forecast[$ant_wid]))
                                {
                                    DebugHelper::showNeatMarkup($this->m_bottomup_calcs,"SEE BOTTOMUP Missing forecast for wid#$ant_wid of project#$ant_opid");
                                    throw new \Exception("Missing forecast for wid#$ant_wid of project#$ant_opid");
                                }
                                $ant_wdetail = $this->m_wid2forecast[$ant_wid];
                                $ant_effective_otsp *= $ant_wdetail['fordep']['otsp'];
                            } else {
                                $ant_wdetail = $this->m_bottomup_calcs[$ant_wid]['forecast'];
                                $ant_effective_otsp *= $ant_wdetail['forecast']['fordep']['otsp'];
                            }
                        }
                    }
                    $ant_info['effective_otsp'] = $ant_effective_otsp;
                    $ant_info['zero_otsp_wids'] = $zero_otsp_ants_ar;
                    if($ant_count > 0)
                    {
                        $local_scf = $this->m_oCScores->getSCF4NonLeaf($wdetail, $pui, $ant_info, $status_detail, $local_scf_logic);
                    } else {
                        $local_scf = $this->m_oCScores->getSCF4Leaf($wdetail, $pui, $status_detail, $local_scf_logic);
                    }
                    $ant_scf = $this->m_oCScores->getSCF2FeedDependents($wdetail, $pui, $ant_info, $status_detail, $ant_scf_logic);
                    
                    $this->m_bottomup_calcs[$wid]['forecast']['local']['otsp'] = $local_scf;
                    $this->m_bottomup_calcs[$wid]['forecast']['fordep']['otsp'] = $ant_scf;
                    
                    $thisresult = [];
                    $thisresult['leaf_yn'] = 1;
                    $thisresult['local']['otsp'] = $local_scf;
                    $thisresult['local']['logic'] = $local_scf_logic;
                    $thisresult['fordep']['otsp'] = $ant_scf;
                    $thisresult['fordep']['logic'] = $ant_scf_logic;
                    $this->m_project_bundle['workitems'][$wid]['forecast'] = $thisresult;  
                    
                    $ddw = $wdetail['maps']['ddw'];
                    $ddp = $wdetail['maps']['ddp'];
                    foreach($ddw as $depwid)
                    {
                        //Make sure we only compute for dependents that are within the current project
                        if($ddp[$depwid] == $this->m_projectid)
                        {
                            $this->computeAllOTSP($depwid);
                        }
                    }
                }
            } else {
                //Compute the currentwid
                $wdetail = $this->m_project_bundle['workitems'][$currentwid];
                $opid = $wdetail['owner_projectid'];
                if(!empty($this->m_limit_end_dt))
                {
                    $wdetail['limit_edt'] = $this->m_limit_end_dt;
                }
                $status_cd = $wdetail['status_cd'];
                $status_detail = $this->m_status_lookup[$status_cd];
                $daw = $wdetail['maps']['daw'];
                $dap = $wdetail['maps']['dap'];
                $isready = TRUE;
                foreach($daw as $antwid)
                {
                    if($dap[$antwid] == $opid)
                    {
                        if(!isset($this->m_project_bundle['workitems'][$antwid]['forecast']))
                        {
                            //All the ants are not computed yet, don't compute this one yet.
                            $isready = FALSE;
                            break;
                        }
                    }
                }
                if($isready)
                {
                   
                    $owner_personid = $wdetail['owner_personid'];
                    $self_allow_dep_overlap_hours = $wdetail['self_allow_dep_overlap_hours'];
                    $self_allow_dep_overlap_pct = $wdetail['self_allow_dep_overlap_pct'];
                    $ant_sequence_allow_overlap_hours = $wdetail['ant_sequence_allow_overlap_hours'];
                    $ant_sequence_allow_overlap_pct = $wdetail['ant_sequence_allow_overlap_pct'];
                    $dep_info = [];
                    $dep_info['settings']['self_allow_dep_overlap_hours'] = $self_allow_dep_overlap_hours;
                    $dep_info['settings']['self_allow_dep_overlap_pct'] = $self_allow_dep_overlap_pct;
                    $dep_info['settings']['ant_sequence_allow_overlap_hours'] = $ant_sequence_allow_overlap_hours;
                    $dep_info['settings']['ant_sequence_allow_overlap_pct'] = $ant_sequence_allow_overlap_pct;
                    
                    $pui = $this->m_people_maps[$owner_personid]['pui'];            
                    $sumantp = 0;
                    $product_antp = 1;
                    $zero_otsp_ants_ar = [];
                    $ant_min_edt = NULL;
                    foreach($wdetail['maps']['daw'] as $antwid)
                    {
                        if(!isset($this->m_bottomup_calcs[$antwid]['forecast']))
                        {
                            if(empty($this->m_wid2forecast[$antwid]))
                            {
                                DebugHelper::showNeatMarkup($this->m_bottomup_calcs,"SEE BOTTOMUP Missing forecast for wid#$antwid");
                                throw new \Exception("Missing forecast for wid#$antwid");
                            }
                            $antresult = $this->m_wid2forecast[$antwid];
                            $theantp = $antresult['fordep']['otsp'];
                        } else {
                            $antresult = $this->m_bottomup_calcs[$antwid]['forecast'];
                            $theantp = $antresult['fordep']['otsp'];
                        }
                        if($dap[$antwid] == $opid)
                        {
                            $ant_binfo = $this->m_bottomup_calcs[$antwid];
                        } else {
                            //TODO get from other project!!!!!
                            $ant_binfo = [];    //TODO
                        }
                        if(!empty($ant_binfo['effective_end_dt']))
                        {
                            if(empty($ant_min_edt) || $ant_min_edt > $ant_binfo['effective_end_dt'])
                            {
                                $ant_min_edt = $ant_binfo['effective_end_dt'];
                            }
                        }
                        if($theantp == 0)
                        {
                            $zero_otsp_ants_ar[] = $antwid;
                        }
                        $sumantp += $theantp;
                        $product_antp *= $theantp;
                    }
                    $ant_info = [];
                    $ant_info['zero_otsp_wids'] = $zero_otsp_ants_ar;
                    $ant_info['effective_otsp'] = $product_antp;
                    $ant_info['min_end_dt'] = $ant_min_edt;
                    $local_scf = $this->m_oCScores->getSCF4NonLeaf($wdetail, $pui, $ant_info, $status_detail, $local_scf_logic);
                    $ant_scf = $this->m_oCScores->getSCF2FeedDependents($wdetail, $pui, $ant_info, $status_detail, $ant_scf_logic);
                    $this->m_bottomup_calcs[$currentwid]['forecast']['local']['otsp'] = $local_scf;
                    $this->m_bottomup_calcs[$currentwid]['forecast']['fordep']['otsp'] = $ant_scf;

                    $thisresult = [];
                    $thisresult['leaf_yn'] = 0;
                    $thisresult['local']['otsp'] = $local_scf;
                    $thisresult['local']['logic'] = $local_scf_logic;
                    $thisresult['fordep']['otsp'] = $ant_scf;
                    $thisresult['fordep']['logic'] = $ant_scf_logic;
                    $this->m_project_bundle['workitems'][$currentwid]['forecast'] = $thisresult;                    

                    //Now try to process the dependents
                    $ddw = $wdetail['maps']['ddw'];
                    $ddp = $wdetail['maps']['ddp'];
                    foreach($ddw as $depwid)
                    {
                        //Make sure we only compute for dependents that are within the current project
                        if($ddp[$depwid] == $opid)
                        {
                            //Yes, this one is the project we are computing
                            $this->computeAllOTSP($depwid);
                        //} else {
                        //    drupal_set_message("LOOK DEBUGGING MESSAGE we will NOT call computeAllOTSP($depwid) because {$ddp[$depwid]} !== $opid","error");
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getForecastSummaryInfo()
    {
        $root_goalid = $this->m_project_bundle['metadata']['root_goalid'];
        $summaryinfo["metadata"] =  $this->m_project_bundle['metadata'];
        $summaryinfo["root_workitem"] =  $this->m_project_bundle['workitems'][$root_goalid];
        $summaryinfo["forecast"] = $this->m_project_bundle['workitems'][$root_goalid]['forecast'];
        return $summaryinfo;
    }
    
    public function getDetail($recompute=FALSE)
    {
        if($recompute)
        {
            $this->computeAllForecasts();
        }
        
        $bundle = [];
        $bundle['main_project_detail'] = $this->m_project_bundle;
        $bundle['computed_calcs'] = $this->m_bottomup_calcs;
        return $bundle;
    }
    
    private function computeTopDownWorkitemStats($wid,$inbounds=NULL)
    {
        try
        {
            $is_project_root = ($this->root_goalid == $wid);
            if(empty($inbounds))
            {
                $inbounds = [];
		if(!$is_project_root)
                {
                    $inbounds['min_start_dt'] = NULL;
                } else {
                    $inbounds['min_start_dt'] = $this->m_min_reference_dt;
                }
                $inbounds['max_start_dt'] = NULL;
                $inbounds['min_locked_start_dt'] = NULL;
                $inbounds['max_locked_start_dt'] = NULL;
				
                $inbounds['min_end_dt'] = NULL;
                $inbounds['max_end_dt'] = NULL;
                $inbounds['min_locked_end_dt'] = NULL;
                $inbounds['max_locked_end_dt'] = NULL;

                $inbounds['cannot_exceed_branch_effort_hours'] = NULL;
            }

            //Get all the local values	
            $wdetail = $this->m_project_bundle['workitems'][$wid];
            $this_start_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($wdetail['actual_start_dt'],$wdetail['planned_start_dt']);
            $this_end_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($wdetail['actual_end_dt'],$wdetail['planned_end_dt']);
            $this_start_dt_locked_yn = ($wdetail['planned_start_dt_locked_yn'] == 1 || !empty($wdetail['actual_start_dt']));
            $this_end_dt_locked_yn = ($wdetail['planned_end_dt_locked_yn'] == 1 || !empty($wdetail['actual_end_dt']));
            $this_cannot_exceed_branch_effort_hours = $wdetail['limit_branch_effort_hours_cd'] != 'L' ? NULL : $wdetail['branch_effort_hours_est'];
            $daw_count = isset($wdetail['maps']['daw']) ? count($wdetail['maps']['daw']) : 0;
            $antwid_ar = $daw_count > 0 ? $wdetail['maps']['daw'] : [];
            if($daw_count == 0)
            {
                $antwid_ar = [];
                //$this->m_project_bundle['leafmap'][$wid] = $wid;
            } else {
                $antwid_ar = $wdetail['maps']['daw'];
            }
            
            //Get all the existing constraints
            if(isset($this->m_project_bundle['workitems'][$wid]['computed_bounds']['topdown']))
            {
                
                $existingbounds = $this->m_project_bundle['workitems'][$wid]['computed_bounds']['topdown'];
                
            } else {
        	//There is no existing yet
                $existingbounds = [];
                $existingbounds['min_start_dt'] = NULL;
                $existingbounds['max_start_dt'] = NULL;
                $existingbounds['min_locked_start_dt'] = NULL;
                $existingbounds['max_locked_start_dt'] = NULL;
                $existingbounds['min_end_dt'] = NULL;
                $existingbounds['max_end_dt'] = NULL;
                $existingbounds['min_locked_end_dt'] = NULL;
                $existingbounds['max_locked_end_dt'] = NULL;
                $existingbounds['cannot_exceed_branch_effort_hours'] = NULL;
            }
            
            //Collect all the new values
            $outbound = [];
            if($is_project_root)
            {
                
                $outbound['min_start_dt'] = NULL;
                $outbound['max_start_dt'] = NULL;
                $outbound['min_end_dt'] = NULL;
                $outbound['max_end_dt'] = NULL;

                $outbound['min_locked_start_dt'] = NULL;
                $outbound['max_locked_start_dt'] = NULL;
                $outbound['min_locked_end_dt'] = NULL;
                $outbound['max_locked_end_dt'] = NULL;
                
            } else {

                $outbound['min_start_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($inbounds['min_start_dt'],$existingbounds['min_start_dt'],$this_start_dt);
                $outbound['max_start_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($inbounds['max_start_dt'],$existingbounds['max_start_dt'],$this_start_dt);
                $outbound['min_end_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($inbounds['min_end_dt'],$existingbounds['min_end_dt'],$this_end_dt);
                $outbound['max_end_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($inbounds['max_end_dt'],$existingbounds['max_end_dt'],$this_end_dt);

                if($this_start_dt_locked_yn)
                {
                    $outbound['min_locked_start_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($inbounds['min_locked_start_dt'],$existingbounds['min_locked_start_dt'],$this_start_dt);
                    $outbound['max_locked_start_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($inbounds['max_locked_start_dt'],$existingbounds['max_locked_start_dt'],$this_start_dt);
                } else {
                    $outbound['min_locked_start_dt'] = NULL;
                    $outbound['max_locked_start_dt'] = NULL;
                }

                if($this_end_dt_locked_yn)
                {
                    $outbound['min_locked_end_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($inbounds['min_locked_end_dt'],$existingbounds['min_locked_end_dt'],$this_end_dt);
                    $outbound['max_locked_end_dt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($inbounds['max_locked_end_dt'],$existingbounds['max_locked_end_dt'],$this_end_dt);
                } else {
                    $outbound['min_locked_end_dt'] = NULL;
                    $outbound['max_locked_end_dt'] = NULL;
                }
            }
			
            $outbound['cannot_exceed_branch_effort_hours'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($inbounds['cannot_exceed_branch_effort_hours'],$existingbounds['cannot_exceed_branch_effort_hours'],$this_cannot_exceed_branch_effort_hours);
			
            $this->m_project_bundle['workitems'][$wid]['computed_bounds']['topdown'] = $outbound;
            
            foreach($antwid_ar as $antwid)
            {
                $this->computeTopDownWorkitemStats($antwid, $outbound);
            }
			
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
	
    /**
     * Get some work related predictions that factor in when the person is busy
     * TODO REFACTOR TO USE PRECOMPUTED PCT OR DEPRECATE!!!!!!!!!!!!!!!!!!!!
     * @deprecated
     */
    private function getPredictionsBundle( $wid, $our_start_dt
                                           ,$our_end_dt
                                           ,$owner_personid_for_daycount
                                           ,$planned_fte_count
                                           ,$remaining_effort_hours
                                           ,$discard_past_days_yn=TRUE)
    {
        try
        {
            $map_wid_allocations2ignore = $this->m_map_wid_allocations2ignore;
            $map_wid_allocations2ignore[$wid] = $wid;
            $bundle = [];
            $input['metadata'] = $discard_past_days_yn ? "calc from today" : "calc from start";
            $input['start_dt'] = $our_start_dt;
            $input['end_dt'] = $our_end_dt;
            $input['personid'] = $owner_personid_for_daycount;
            $input['planned_fte_count'] = $planned_fte_count;
            $input['remaining_effort_hours'] = $remaining_effort_hours;
            $input['today_dt'] = $this->m_today_dt;
            
            $pui = $this->m_people_maps[$owner_personid_for_daycount]['pui'];            

            if (!empty($our_end_dt) && $our_start_dt > $our_end_dt)
            {
                $errmsg = "Direction conflict cannot go from {$our_start_dt} to {$our_end_dt}!";
                DebugHelper::showStackTrace($errmsg);
                throw new \Exception($errmsg);
            }
            
            if(!$discard_past_days_yn)
            {
                $use_start_dt = $our_start_dt;
            } else {
                if($our_start_dt < $this->m_today_dt)
                {
                    $use_start_dt = $this->m_today_dt;
                } else {
                    $use_start_dt = $our_start_dt;
                }
            }
            if($our_end_dt < $use_start_dt)
            {
                $our_end_dt = $use_start_dt;
            }

            $daycount = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($use_start_dt, $our_end_dt);
            $cutoffdaycount = 999;
            if($daycount <= $cutoffdaycount)
            {
                $safe_end_dt = $our_end_dt;
            } else {
                $safe_end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($use_start_dt, $cutoffdaycount);
                $wmsg = "Computing utilization for shorter range [$use_start_dt,$safe_end_dt] instead of [$use_start_dt, $our_end_dt] because $daycount > $cutoffdaycount days";
                error_log("WARNING: $wmsg");
                drupal_set_message($wmsg,'warning');
            }
            $web = $pui->getWorkEffortBundleOfPerson($use_start_dt, $safe_end_dt, $map_wid_allocations2ignore);                

            $busy_hours_until_due_date = $web['already_busy_hours'];
            $work_hours_until_due_date = $web['work_hours'];
            $work_days_until_due_date = $web['work_days'];

            $fte_overage = $this->m_oCScores->getFTEOverageApproximatedBySimpleHourCount(
                    $work_hours_until_due_date
                    , $work_days_until_due_date
                    , $planned_fte_count, $remaining_effort_hours);

            $output['busy_hours_until_due_date'] = $busy_hours_until_due_date;
            $output['work_hours_until_due_date'] = $work_hours_until_due_date;
            $output['work_days_until_due_date'] = $work_days_until_due_date;
            $output['fte_overage'] = $fte_overage;
            
            $bundle['input'] = $input;
            $bundle['output'] = $output;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Works its way up to the root
     * Assumes top-down was already computed
     */
    private function computeBottomUpWorkitemStats($wid,$inbounds=NULL)
    {
        try
        {
            if(empty($inbounds))
            {
                $inbounds = [];
            }
            $is_project_root_yn = ($this->root_goalid == $wid) ? 1 : 0;
            
            //Grab all the self detail first
            if(empty($this->m_project_bundle['workitems'][$wid]))
            {
                DebugHelper::showStackTrace();
                DebugHelper::showNeatMarkup($this->m_project_bundle['workitems'], "LOOK MISSING wid#$wid !!!!");
            }
                
            $wdetail = $this->m_project_bundle['workitems'][$wid];
            $remaining_effort_hours = !empty($wdetail['remaining_effort_hours']) ? $wdetail['remaining_effort_hours'] : 0;
            $effective_start_dt = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
            $effective_end_dt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
            $status_cd = $wdetail['status_cd'];
            $status_detail = $this->m_status_lookup[$status_cd];
            //$is_terminal_status = $status_detail['terminal_yn'] == 1;
            //$is_happy_status = $status_detail['happy_yn'] == 1;
            //$status_scf = $status_detail['ot_scf'];
            //$node_scf = $wdetail['ot_scf'];
                
            //Grab info from top
            $topdowninfo = $this->m_project_bundle['workitems'][$wid]['computed_bounds']['topdown'];
            
            //Factor in info from ants now
            if(empty($this->m_bottomup_calcs))
            {
                //Create the structure
                $this->m_bottomup_calcs = [];
            }
            if(empty($this->m_bottomup_calcs[$wid]))
            {
                //Create the structure for this wid
                $this->m_bottomup_calcs[$wid] = [];
                $this->m_bottomup_calcs[$wid]['warnings'] = [];
                $this->m_bottomup_calcs[$wid]['warnings']['type_map'] = [];
                $this->m_bottomup_calcs[$wid]['warnings']['detail'] = [];
                $this->m_bottomup_calcs[$wid]['remaining_effort_hours'] = $remaining_effort_hours;
                $this->m_bottomup_calcs[$wid]['effective_start_dt'] = $effective_start_dt;
                $this->m_bottomup_calcs[$wid]['effective_end_dt'] = $effective_end_dt;
                $this->m_bottomup_calcs[$wid]['daw'] = [];  //To track which have reported
                $this->m_bottomup_calcs[$wid]['all_ants'] = [];
                $this->m_bottomup_calcs[$wid]['status']['local'] = $this->m_status_lookup[$status_cd];
                $this->m_bottomup_calcs[$wid]['status']['ants']['map']['terminal'] = [];
                $this->m_bottomup_calcs[$wid]['status']['ants']['map']['workstarted'] = [];
                $this->m_bottomup_calcs[$wid]['status']['ants']['map']['happy'] = [];
                $this->m_bottomup_calcs[$wid]['status']['ants']['map']['needstesting'] = [];
                $this->m_bottomup_calcs[$wid]['total_branch_remaining_effort_hours'] = $remaining_effort_hours;
                $ant_project_count = 0;
                foreach($wdetail['maps']['dap'] as $ant_wid=>$ant_opid)
                {
                    if($ant_opid != $this->m_projectid)
                    {
                        $ant_wdetail = $this->m_project_bundle['workitems'][$ant_wid];
                        $ant_project_count++;
                        $this->m_bottomup_calcs[$wid]['daw'][] = $ant_wid;
                        $this->m_bottomup_calcs[$wid]['all_ants'][$ant_wid] = $ant_wid;
                        $this->m_bottomup_calcs[$ant_wid] = $ant_wdetail;
                        $ant_effective_end_dt = empty($ant_wdetail['actual_end_dt']) ? $ant_wdetail['planned_end_dt'] : $ant_wdetail['actual_end_dt'];
                        $ant_effective_start_dt = empty($ant_wdetail['actual_start_dt']) ? $ant_wdetail['planned_start_dt'] : $ant_wdetail['actual_start_dt'];
                        $this->m_bottomup_calcs[$ant_wid]['effective_end_dt'] = $ant_effective_end_dt;
                        $this->m_bottomup_calcs[$ant_wid]['effective_start_dt'] = $ant_effective_start_dt;
                    }
                }
                $this->m_bottomup_calcs[$wid]['ant_project_count'] = $ant_project_count;
            }
            if(!empty($inbounds['source_wid']))
            {
                //Factor in the total from direct antecedent
                $source_wid = $inbounds['source_wid'];
                $this->m_bottomup_calcs[$wid]['inbounds'][$source_wid] = $inbounds;
                if(empty($this->m_bottomup_calcs[$wid]['daw'][$source_wid]))
                {
                    //We do this once per source.
                    $this->m_bottomup_calcs[$wid]['all_ants'][$source_wid] = $source_wid;
                    foreach($this->m_bottomup_calcs[$source_wid]['all_ants'] as $oneantwid)
                    {
                        $this->m_bottomup_calcs[$wid]['all_ants'][$oneantwid] = $oneantwid;
                    }
                    $this->m_bottomup_calcs[$wid]['daw'][$source_wid] = $source_wid;
                    //MOVED $this->m_bottomup_calcs[$wid]['total_branch_remaining_effort_hours'] += $this->m_bottomup_calcs[$source_wid]['total_branch_remaining_effort_hours'];    //TODO FIX DOUBLECOUNTING!!!! use all_ants???
                    $source_status_local = $this->m_bottomup_calcs[$source_wid]['status']['local'];
                    if($source_status_local['workstarted_yn'])
                    {
                        $this->m_bottomup_calcs[$wid]['status']['ants']['map']['workstarted'][$source_wid] = $source_wid;
                    }
                    if($source_status_local['terminal_yn'])
                    {
                        $this->m_bottomup_calcs[$wid]['status']['ants']['map']['terminal'][$source_wid] = $source_wid;
                    }
                    if($source_status_local['happy_yn'])
                    {
                        $this->m_bottomup_calcs[$wid]['status']['ants']['map']['happy'][$source_wid] = $source_wid;
                    }
                    if($source_status_local['needstesting_yn'])
                    {
                        $this->m_bottomup_calcs[$wid]['status']['ants']['map']['needstesting'][$source_wid] = $source_wid;
                    }
                    $source_status_ants = $this->m_bottomup_calcs[$source_wid]['status']['ants'];
                    foreach($source_status_ants['map'] as $mapname=>$mapcontent)
                    {
                        $themappedwids = array_keys($mapcontent);
                        foreach($themappedwids as $omwid)
                        {
                            $this->m_bottomup_calcs[$wid]['status']['ants']['map'][$mapname][$omwid] = $omwid;
                        }
                    }
                }
                
                $date_ar = [];
                if(!empty($this->m_bottomup_calcs[$wid]['min_start_dt']) && strlen($this->m_bottomup_calcs[$wid]['min_start_dt']) == 10)
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['min_start_dt'];
                }
                if(!empty($this->m_bottomup_calcs[$source_wid]['min_start_dt_including_current_value']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$source_wid]['min_start_dt_including_current_value'];
                }
                if(count($date_ar) > 0)
                {
                    $this->m_bottomup_calcs[$wid]['min_start_dt'] = count($date_ar) > 1 ? min($date_ar) : $date_ar[0];
                }
               
                $date_ar = [];
                if(!empty($this->m_bottomup_calcs[$wid]['min_end_dt']) && strlen($this->m_bottomup_calcs[$wid]['min_end_dt']) == 10)
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['min_end_dt'];
                }
                if(!empty($this->m_bottomup_calcs[$source_wid]['min_end_dt_including_current_value']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$source_wid]['min_end_dt_including_current_value'];
                }
                if(count($date_ar) > 0)
                {
                    $this->m_bottomup_calcs[$wid]['min_end_dt'] = count($date_ar) > 1 ? min($date_ar) : $date_ar[0];
                }
                
                $date_ar = [];
                if(!empty($this->m_bottomup_calcs[$wid]['max_start_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['max_start_dt'];
                }
                if(!empty($this->m_bottomup_calcs[$source_wid]['max_start_dt_including_current_value']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$source_wid]['max_start_dt_including_current_value'];
                }
                if(count($date_ar) > 0)
                {
                    $this->m_bottomup_calcs[$wid]['max_start_dt'] = count($date_ar) > 1 ? max($date_ar) : $date_ar[0];
                }
                
                $date_ar = [];
                if(!empty($this->m_bottomup_calcs[$wid]['max_end_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['max_end_dt'];
                }
                if(!empty($this->m_bottomup_calcs[$source_wid]['max_end_dt_including_current_value']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$source_wid]['max_end_dt_including_current_value'];
                }
                if(count($date_ar) > 0)
                {
                    $this->m_bottomup_calcs[$wid]['max_end_dt'] = count($date_ar) > 1 ? max($date_ar) : $date_ar[0];
                }
            }
            $computed_solution = FALSE;
            $recommended_fit = NULL;
            $reported_daw_count = count($this->m_bottomup_calcs[$wid]['daw']);
            $unprocessed_daw_count = count($wdetail['maps']['daw']) - $reported_daw_count;
            if($unprocessed_daw_count == 0)
            {
                //All ants have been visited, now total the branch info (this way we do NOT double count the multi-dep links)
                foreach($this->m_bottomup_calcs[$wid]['all_ants'] as $oneantwid)
                {
                    $this->m_bottomup_calcs[$wid]['total_branch_remaining_effort_hours'] += $this->m_bottomup_calcs[$oneantwid]['remaining_effort_hours'];
                }
                
                //All the antecedents have completed, now compute local forecasts
                if($this->m_flag_availability_type_BY_OWNER)
                {
                    $owner_personid_for_daycount = $wdetail['owner_personid'];
                } else {
                    $owner_personid_for_daycount = NULL;
                }  

                if(empty($topdowninfo['min_start_dt']))
                {
                    $search_end_dt = NULL;
                } else {
                    //We cannot end after this date and still fit with dependency setting
                    $search_end_dt = $topdowninfo['min_start_dt'];
                }
                
                if(empty($this->m_bottomup_calcs[$wid]['max_end_dt']))
                {
                    $search_start_dt = NULL;
                } else {
                    //We cannot start before this date and still fit with dependency setting
                    $ant_max_end_dt = $this->m_bottomup_calcs[$wid]['max_end_dt'];
                    $search_start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($ant_max_end_dt, 1);
                }

                $planned_fte_count = $wdetail['planned_fte_count'];            
                $branch_effort_hours_est = $wdetail['limit_branch_effort_hours_cd'] == 'I' ? NULL : $wdetail['branch_effort_hours_est'];
                $constrain_branch_effort_hours_yn = ($branch_effort_hours_est !== NULL && $wdetail['limit_branch_effort_hours_cd'] === 'L') ? 1 : 0;
                $total_branch_remaining_effort_hours = $this->m_bottomup_calcs[$wid]['total_branch_remaining_effort_hours'];
                
                $self_allow_dep_overlap_hours = $wdetail['self_allow_dep_overlap_hours'];
                $self_allow_dep_overlap_pct = $wdetail['self_allow_dep_overlap_pct'];
                $ant_sequence_allow_overlap_hours = $wdetail['ant_sequence_allow_overlap_hours'];
                $ant_sequence_allow_overlap_pct = $wdetail['ant_sequence_allow_overlap_pct'];
                $dep_info = [];
                $dep_info['settings']['self_allow_dep_overlap_hours'] = $self_allow_dep_overlap_hours;
                $dep_info['settings']['self_allow_dep_overlap_pct'] = $self_allow_dep_overlap_pct;
                $dep_info['settings']['ant_sequence_allow_overlap_hours'] = $ant_sequence_allow_overlap_hours;
                $dep_info['settings']['ant_sequence_allow_overlap_pct'] = $ant_sequence_allow_overlap_pct;

                $planned_start_dt_locked_yn = $wdetail['planned_start_dt_locked_yn'];
                $planned_start_dt = $wdetail['planned_start_dt'];
                $actual_start_dt = $wdetail['actual_start_dt'];
                $declared_effective_start_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($actual_start_dt,$planned_start_dt);
                if($is_project_root_yn && $this->m_force_lock_project_start_dt)
                {
                    $start_dt_locked_yn = 1;
                } else {
                    $start_dt_locked_yn = (!empty($actual_start_dt) || (!empty($planned_start_dt) && $planned_start_dt_locked_yn == 1)) ? 1 : 0;
                }
                $declared_start_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($actual_start_dt,$planned_start_dt);
                $has_declared_start_dt = !empty($declared_start_dt);
                $effective_start_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($declared_start_dt,$this->m_today_dt);

                $planned_end_dt_locked_yn = $wdetail['planned_end_dt_locked_yn'];
                $planned_end_dt = $wdetail['planned_end_dt'];
                $actual_end_dt = $wdetail['actual_end_dt'];
                $declared_effective_end_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($actual_end_dt,$planned_end_dt);
                $effective_end_dt = \bigfathom\UtilityGeneralFormulas::getFirstNonEmptyValue($declared_effective_end_dt, $this->m_today_dt);
                $end_dt_locked_yn = (!empty($actual_end_dt) || (!empty($planned_end_dt) && $planned_end_dt_locked_yn == 1)) ? 1 : 0;

                $from_bottom_min_end_dt = !empty($this->m_bottomup_calcs[$wid]['min_end_dt']) ? $this->m_bottomup_calcs[$wid]['min_end_dt'] : NULL;
                $from_bottom_max_end_dt = !empty($this->m_bottomup_calcs[$wid]['max_end_dt']) ? $this->m_bottomup_calcs[$wid]['max_end_dt'] : NULL;
                
                if($start_dt_locked_yn)
                {
                    $this->m_bottomup_calcs[$wid]['locked_dates']['start_dt'][$effective_start_dt] = $effective_start_dt;
                    $search_start_dt = $effective_start_dt;
                } else {
                    if(empty($from_bottom_max_end_dt) && empty($search_start_dt))
                    {
                        $search_start_dt = $this->m_min_reference_dt;
                    } else {
                        $search_start_dt = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($from_bottom_max_end_dt,$search_start_dt);
                    }
                }
                if($end_dt_locked_yn)
                {
                    $this->m_bottomup_calcs[$wid]['locked_dates']['end_dt'][$effective_end_dt] = $effective_end_dt;
                    $search_end_dt = $effective_end_dt;
                } else {
                    if(empty($from_bottom_min_end_dt) && empty($search_end_dt))
                    {
                        $search_end_dt = $this->m_max_reference_dt;
                    } else {
                        $search_end_dt = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($from_bottom_min_end_dt,$search_end_dt);
                    }
                }               

                if(!empty($declared_effective_end_dt) && $remaining_effort_hours > 0 && $declared_effective_end_dt < $this->m_today_dt)
                {
                    //drupal_set_message("WARNING remaining effort hours up through workitem#$wid exceeds the LOCKED BRANCH HOURS LIMIT $total_branch_remaining_effort_hours > $branch_effort_hours_est", "warning");
                    $warntype = 'OVERDUE';
                    $warntext = "workitem#$wid has $remaining_effort_hours hours remaining effort but deadline passed ($declared_effective_end_dt < {$this->m_today_dt})";
                    $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext);
                    $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
                }
                
                //All ants have been visited, now total the branch info (this way we do NOT double count the multi-dep links
                $debug_crumbs = [];
                $dependency_timing_problems = [];
$debug_crumbs[] = "LOOK at wid=$wid about to ANT loop FOR " . print_r($this->m_bottomup_calcs[$wid]['all_ants'],TRUE); 
                $max_ant_wi_end_dt = NULL;
                $max_ant_proj_end_dt = NULL;
                foreach($this->m_bottomup_calcs[$wid]['all_ants'] as $oneantwid)
                {
                    $ant_wdetail = $this->m_project_bundle['workitems'][$oneantwid];
                    $ant_opid = $ant_wdetail['owner_projectid'];
                    $ant_effective_start_dt = $this->m_bottomup_calcs[$oneantwid]['effective_start_dt'];
                    $ant_effective_end_dt = $this->m_bottomup_calcs[$oneantwid]['effective_end_dt'];
                    if(empty($max_ant_wi_end_dt) || $max_ant_wi_end_dt < $ant_effective_end_dt)
                    {
                        $max_ant_wi_end_dt = $ant_effective_end_dt;
                    }
                    if($ant_wdetail['owner_projectid'] != $this->m_projectid)
                    {
$debug_crumbs[] = "LOOK at wid=$wid IN LOOP oneantwid=$oneantwid owner_projectid::{$ant_wdetail['owner_projectid']} != $this->m_projectid"; 
                        if(empty($max_ant_proj_end_dt) || $max_ant_proj_end_dt < $ant_effective_end_dt)
                        {
                            $max_ant_proj_end_dt = $ant_effective_end_dt;
                        }
                    }
$debug_crumbs[] = "LOOK at wid=$wid IN LOOP oneantwid=$oneantwid ant_effective_start_dt=$ant_effective_start_dt ant_effective_end_dt=$ant_effective_end_dt (max_ant_wi_end_dt=$max_ant_wi_end_dt)"; 
                    
                    if($is_project_root_yn)
                    {
                        //Check ROOT ONLY issues
                        if($ant_opid == $this->m_projectid)
                        {
                            //Check for member work issues
                            if($declared_effective_end_dt < $ant_effective_end_dt)
                            {
                                //Ant is not finished before this one finishes, that is a problem!
                                $warntype = 'PROJ_END_TOO_SOON';
                                $warntext = "project ends before workitem#{$oneantwid}";
                                $dependency_timing_problems[$oneantwid] = array('comment'=>'no overlap','severity'=>90);
                                $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                                $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
                            }
                            if($declared_effective_start_dt > $ant_effective_start_dt)
                            {
                                if(!empty($ant_effective_start_dt))
                                {
                                    //Ant is not finished before this one finishes, that is a problem!
                                    $warntype = 'PROJ_START_TOO_LATE';
                                    $warntext = "project starts after workitem#{$oneantwid}";
                                    $dependency_timing_problems[$oneantwid] = array('comment'=>'no overlap','severity'=>50);
                                } else {
                                    $warntype = 'ANT_NO_START_DATE';
                                    $warntext = "no start date for workitem#{$oneantwid}";
                                    $dependency_timing_problems[$oneantwid] = array('comment'=>'no overlap','severity'=>20);
                                }
                                $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                                $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
                            }
                        }
                    } else {
                        //Check NON-ROOT issues
                        if($effective_start_dt < $ant_effective_start_dt)
                        {
                            //Ant is not started before this one starts, that is a problem!
                            $warntype = 'DEP_TOO_SOON';
                            $warntext = "workitem#$wid starts before workitem#{$oneantwid}";
                            $dependency_timing_problems[$oneantwid] = array('comment'=>'not done','severity'=>25);
                            $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                            $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
$debug_crumbs[] = "LOOK at wid=$wid IN LOOP oneantwid=$oneantwid ant_effective_start_dt=$ant_effective_start_dt ant_effective_end_dt=$ant_effective_end_dt $warntype"; 
                        } else
                        if($effective_end_dt == $ant_effective_end_dt)
                        {
                            //Ant is not finished before this one finishes, that is a problem!
                            $warntype = 'ANT_LATE';
                            $warntext = "workitem#$wid completes same day as workitem#{$oneantwid}";
                            $dependency_timing_problems[$oneantwid] = array('comment'=>'one day overlap','severity'=>50);
                            $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                            $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
$debug_crumbs[] = "LOOK at wid=$wid IN LOOP oneantwid=$oneantwid ant_effective_start_dt=$ant_effective_start_dt ant_effective_end_dt=$ant_effective_end_dt $warntype"; 
                        }
                    }
                    //Check UNIVERSAL issues
                    if($effective_end_dt < $ant_effective_end_dt)
                    {
                        //Ant is not finished before this one finishes, that is a problem!
                        $warntype = 'ANT_TOO_LATE';
                        $warntext = "workitem#$wid completes before workitem#{$oneantwid}";
                        $dependency_timing_problems[$oneantwid] = array('comment'=>'no overlap','severity'=>100);
                        $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                        $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
$debug_crumbs[] = "LOOK at wid=$wid IN LOOP oneantwid=$oneantwid ant_effective_start_dt=$ant_effective_start_dt ant_effective_end_dt=$ant_effective_end_dt $warntype"; 
                    }
                }
                $this->m_bottomup_calcs[$wid]['dependency_timing_problems'] = $dependency_timing_problems;
$debug_crumbs[] = "LOOK at wid=$wid effective_start_dt=$effective_start_dt and effective_end_dt=$effective_end_dt"; 

                $status_info_local = $this->m_bottomup_calcs[$wid]['status']['local'];
                $status_info_ants = $this->m_bottomup_calcs[$wid]['status']['ants'];
                if($is_project_root_yn && !$status_info_local['workstarted_yn'])
                {
                    $count_workstarted = count($status_info_ants['map']['workstarted']);
                    if($count_workstarted>0)
                    {
                        $mapped_widslist_ar = array_keys($status_info_ants['map']['workstarted']);
                        $mapped_widslist_tx = implode(', ', $mapped_widslist_ar);
                        $warntype = 'WRONG_STATUS';
                        if($count_workstarted == 1)
                        {
                            $warntext = "work started for 1 workitem but project status is still marked as {$status_info_local['code']} (see #$mapped_widslist_tx)";
                        } else {
                            $warntext = "work started for $count_workstarted workitems but project status is still marked as {$status_info_local['code']} (see #s $mapped_widslist_tx)";
                        }
                        $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext,'context'=>'status');
                        $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
                    }
                }
                
                $computed_start_dt = NULL;
                $computed_end_dt = NULL;

                if($this->m_workitem2compute == $wid)
                {
                    if(!isset($this->m_candidate_pct_bundle['utilization']['editable']['all_workitems']['each_pct'][$wid]))
                    {
                        $allocation_pct = NULL;
                    } else {
                        $allocation_pct = $this->m_candidate_pct_bundle['utilization']['editable']['all_workitems']['each_pct'][$wid];
                    }
                    //Compute the best solution for this workitem
                    if($start_dt_locked_yn || !$end_dt_locked_yn)
                    {
                        $local_search_start_dt = $search_start_dt;
$debug_crumbs[] = "LOOK LOCKED start dt";                        
                    } else {
                        //By NULLing this out we trigger searching from the end date
                        $local_search_start_dt = NULL;
$debug_crumbs[] = "LOOK NULL OUT local search start dt";                        
                    }
                    if(!empty($local_search_start_dt) && $local_search_start_dt > $search_end_dt)
                    {
                        if(!$end_dt_locked_yn)
                        {
$debug_crumbs[] = "LOOK NULL OUT local search end dt intead of using $search_end_dt";                        
                            $search_end_dt = NULL;
                        } else if(!$start_dt_locked_yn) {
$debug_crumbs[] = "LOOK NULL OUT local search start dt instead of using $local_search_start_dt";                        
                            $local_search_start_dt = NULL;
                        } else {
                            DebugHelper::showStackTrace("Invalid dates [$local_search_start_dt > $search_end_dt] stack trace", 'error', 8);
                            throw new \Exception("Invalid dates: end date larger than start date! [$local_search_start_dt > $search_end_dt] (hours=$remaining_effort_hours)");
                        }
                    }
                    $fdb = $this->getReasonableSolutionDateBundle($remaining_effort_hours
                            , $owner_personid_for_daycount
                            , $local_search_start_dt, $start_dt_locked_yn
                            , $search_end_dt, $end_dt_locked_yn
                            , $this->m_map_wid_allocations2ignore
                            , $this->m_min_pct_buffer
                            , $this->m_strict_min_pct
                            , $allocation_pct
                            , $max_ant_proj_end_dt);
                    
                    $computed_solution = TRUE;  //Trigger to stop
$debug_crumbs[] ="LOOK 000a @@@@@@@ wid=$wid SRCH_START[$search_start_dt ($local_search_start_dt), yn=$start_dt_locked_yn] SRCH_END[$search_end_dt, yn=$end_dt_locked_yn] computed_solution=[$computed_solution]";            
                } else {
                    //if(!empty($this->m_workitem2compute) || empty($effective_end_dt) || $effective_start_dt > $effective_end_dt || $effective_end_dt < $this->m_today_dt)
                    if(empty($effective_end_dt) || $effective_start_dt > $effective_end_dt || $effective_end_dt < $this->m_today_dt)
                    {
                        //Not constrained to where we can offer feedback
                        $fdb = NULL;
$debug_crumbs[] ="LOOK 000b wid=$wid $computed_solution=[$computed_solution] this->m_workitem2compute=" . print_r($this->m_workitem2compute,TRUE);            
                    } else {
                        //Get the insight
                        $map_wid_allocations2ignore = array($wid=>$wid);
                        $fdb = $this->getExistingSolutionDateBundle($remaining_effort_hours
                                , $owner_personid_for_daycount
                                , $effective_start_dt
                                , $effective_end_dt
                                , $map_wid_allocations2ignore );
$debug_crumbs[] ="LOOK 000c wid=$wid effective_start_dt=$effective_start_dt effective_end_dt=$effective_end_dt $computed_solution=[$computed_solution] " . print_r($fdb,TRUE);            
                    }
                }
                
                if(empty($fdb))
                {
                    //Just use the existing actual declaration based values
                    $selected_start_dt = $effective_start_dt;
                    if($selected_start_dt <= $effective_end_dt)
                    {
                        $selected_end_dt = $effective_end_dt;
                    } else {
                        $selected_end_dt = NULL;
                    }
$debug_crumbs[] ="LOOK 1111 wid=$wid sdt=$selected_start_dt and edt=$selected_end_dt";            
                } else {
                    //Because project cannot start AFTER the dependents start
                    if(!empty($fdb['solution']['start_dt']) && !empty($constrain_from_deps['min_effective_start_dt']) 
                            && ($fdb['solution']['start_dt'] > $constrain_from_deps['min_effective_start_dt']))
                    {
                        $fdb['solution']['start_dt'] = $constrain_from_deps['min_effective_start_dt'];
$debug_crumbs[] ="LOOK 2222a wid=$wid aaa";            
                    }

                    //Because project cannot end BEFORE the dependents end
                    if(!empty($fdb['solution']['end_dt']) && !empty($constrain_from_deps['max_effective_end_dt']) 
                            && ($fdb['solution']['end_dt'] > $constrain_from_deps['max_effective_end_dt']))
                    {
                        $fdb['solution']['end_dt'] = $constrain_from_deps['max_effective_end_dt'];
$debug_crumbs[] ="LOOK 2222b wid=$wid bbb";            
                    }

                    if($start_dt_locked_yn)
                    {
                        $selected_start_dt = $effective_start_dt;
                    } else {
                        $selected_start_dt = !empty( $fdb['solution']['start_dt']) ? $fdb['solution']['start_dt'] : $effective_start_dt;
                    }
                    if($end_dt_locked_yn)
                    {
                        $selected_end_dt = $effective_end_dt;
                    } else {
                        $selected_end_dt = !empty($fdb['solution']['end_dt']) ? $fdb['solution']['end_dt'] : $effective_end_dt;
                    }
                    $show_constrain_from_deps = empty($constrain_from_deps) ? 'no-deps-found' : $constrain_from_deps;
$debug_crumbs[] ="LOOK REFACTOR ALL THIS TO USE UTILIZTIONP LOGIC!!! 2222c wid=$wid sdt=$selected_start_dt and edt=$selected_end_dt";            
                }

                if (!empty($selected_end_dt) && $selected_start_dt > $selected_end_dt)
                {
                    $errmsg = "Direction conflict at wid=$wid cannot go from {$selected_start_dt} to {$selected_end_dt}! debug_crumbs=" . print_r($debug_crumbs,TRUE);
                    DebugHelper::showStackTrace($errmsg);
                    DebugHelper::showNeatMarkup($fdb,"LOOK what we forecast",'error');
                    throw new \Exception($errmsg);
                }

                if($constrain_branch_effort_hours_yn)
                {
                    //total_branch_remaining_effort_hours
                    if($total_branch_remaining_effort_hours > $branch_effort_hours_est)
                    {
                        //drupal_set_message("WARNING remaining effort hours up through workitem#$wid exceeds the LOCKED BRANCH HOURS LIMIT $total_branch_remaining_effort_hours > $branch_effort_hours_est", "warning");
                        $warntype = 'BEHC';
                        $warntext = "remaining effort hours up through workitem#$wid exceeds the LOCKED BRANCH HOURS LIMIT $total_branch_remaining_effort_hours > $branch_effort_hours_est";
                        $this->m_bottomup_calcs[$wid]['warnings']['detail'][] = array('type'=>$warntype,'message'=>$warntext);
                        $this->m_bottomup_calcs[$wid]['warnings']['type_map'][$warntype] = $warntype;
                    }
                }

//TODO --------------------- TODO REFACTOR TO USE UTILIZATION INSTEAD OF OLD TOTAL HOURS THING!!!!!!!!!!!!!!!!!!
                
                $fdb_prediction_bundle = $this->getPredictionsBundle($wid, $selected_start_dt
                                            , $selected_end_dt
                                            , $owner_personid_for_daycount
                                            , $planned_fte_count
                                            , $remaining_effort_hours);  

                $busy_hours_until_due_date = $fdb_prediction_bundle['output']['busy_hours_until_due_date'];
                $work_hours_until_due_date = $fdb_prediction_bundle['output']['work_hours_until_due_date'];
                $work_days_until_due_date = $fdb_prediction_bundle['output']['work_days_until_due_date'];
                $fte_overage = $fdb_prediction_bundle['output']['fte_overage'];

                $new_busy_hours_until_due_date = $busy_hours_until_due_date + $remaining_effort_hours;

                $assessment_highlights = [];
                $assessment_highlights['new_busy_hours_until_due_date'] = $new_busy_hours_until_due_date;
                $assessment_highlights['busy_hours_until_due_date'] = $busy_hours_until_due_date;
                $assessment_highlights['work_hours_until_due_date'] = $work_hours_until_due_date;
                $assessment_highlights['work_days_until_due_date'] = $work_days_until_due_date;
                $assessment_highlights['fte_overage'] = $fte_overage;

                if($fte_overage == 0)
                {
                    //This one fits
                    $computed_end_dt = $fdb['solution']['end_dt'];
                    if($fdb['solution']['use_new_start_dt_yn'])
                    {
                        $computed_start_dt = $fdb['solution']['start_dt'];
                    }
                }
                $recommended_fit['solution'] = $fdb['solution'];
                $recommended_fit['solution']['assessment'] = $assessment_highlights;
                if(empty($recommended_fit['solution']['feedback']))
                {
                    $fit_feedback = [];
                    $fit_feedback['metadata'] = array('message'=>'nothing found','$debug_crumbs'=>$debug_crumbs);
                } else {
                    $fit_feedback = $recommended_fit['solution']['feedback'];
                    $fit_feedback['metadata']['$debug_crumbs'] = $debug_crumbs;
                }
                $date_ar = [];
                if(!empty($computed_start_dt))
                {
                    $date_ar[] = $computed_start_dt;
                }
                if(!empty($this->m_bottomup_calcs[$wid]['min_start_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['min_start_dt'];
                }
                $min_start_dt = count($date_ar) > 0 ? min($date_ar) : NULL;
                $date_ar[] = $effective_start_dt;
                $min_start_dt_inc_current = min($date_ar);
                
                $date_ar = [];
                if(!empty($computed_start_dt) && !$is_project_root_yn)
                {
                    $date_ar[] = $computed_start_dt;
                }
                if(!empty($this->m_bottomup_calcs[$wid]['max_start_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['max_start_dt'];
                }
                $max_start_dt = count($date_ar) > 0 ? max($date_ar) : NULL;
                $date_ar[] = $effective_start_dt;
                $max_start_dt_inc_current = max($date_ar);

                $date_ar = [];
                if(!empty($computed_end_dt) && !$is_project_root_yn)
                {
                    $date_ar[] = $computed_end_dt;
                }
                if(!empty($this->m_bottomup_calcs[$wid]['min_end_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['min_end_dt'];
                }
                $min_end_dt = count($date_ar) > 0 ? min($date_ar) : NULL;
                $date_ar[] = $effective_end_dt;
                $min_end_dt_inc_current = min($date_ar);
                
                $date_ar = [];
                if(!empty($computed_end_dt))
                {
                    $date_ar[] = $computed_end_dt;
                }
                if(!empty($this->m_bottomup_calcs[$wid]['max_end_dt']))
                {
                    $date_ar[] = $this->m_bottomup_calcs[$wid]['max_end_dt'];
                }
                $max_end_dt = count($date_ar) > 0 ? max($date_ar) : NULL;
                $date_ar[] = $effective_end_dt;
                $max_end_dt_inc_current = max($date_ar);
                
                $this->m_bottomup_calcs[$wid]['min_start_dt'] = $min_start_dt;
                $this->m_bottomup_calcs[$wid]['max_start_dt'] = $max_start_dt;
                $this->m_bottomup_calcs[$wid]['min_start_dt_including_current_value'] = $min_start_dt_inc_current;
                $this->m_bottomup_calcs[$wid]['max_start_dt_including_current_value'] = $max_start_dt_inc_current;
                $this->m_bottomup_calcs[$wid]['min_end_dt'] = $min_end_dt;
                $this->m_bottomup_calcs[$wid]['max_end_dt'] = $max_end_dt;
                $this->m_bottomup_calcs[$wid]['min_end_dt_including_current_value'] = $min_end_dt_inc_current;
                $this->m_bottomup_calcs[$wid]['max_end_dt_including_current_value'] = $max_end_dt_inc_current;
                
                //Create the outbound info
                $outbound = [];
                $outbound['source_wid'] = $wid;
                $outbound['recommended_fit'] = $recommended_fit;
                $this->m_bottomup_calcs[$wid]['recommended_fit'] = $recommended_fit;
                  
                $impossible = empty($recommended_fit);
                $dawcount = count($wdetail['maps']['daw']);
                $local_scf_logic = [];
                $ant_scf_logic = [];
                $owner_personid = $wdetail['owner_personid'];
                $pui = $this->m_people_maps[$owner_personid]['pui'];
                $alerts_ar = [];
                $alerts_ar['type_map']['warning'] = $this->m_bottomup_calcs[$wid]['warnings']['type_map'];
                $alerts_ar['detail']['warning'] = $this->m_bottomup_calcs[$wid]['warnings']['detail'];
                $wdetail['alerts'] = $alerts_ar;
                $wdetail['fit_feedback'] = $fit_feedback;
                if($impossible)
                {
                    $logic_summary = "impossible to fit constraints";
                    $logic_detail = "no fit found for declared constraints";
                    $local_scf = 0;
                    $local_scf_logic[] = array('summary'=>$logic_summary,'detail'=>$logic_detail); 
                    $ant_scf = 0;
                    $ant_scf_logic[] = array('summary'=>$logic_summary,'detail'=>$logic_detail); 
                } else if(isset($this->m_bottomup_calcs[$wid]['warnings']['type_map']['OVERDUE']))
                {
                    $logic_summary = "work overdue";
                    $logic_detail = "due date for the work has passed";
                    $local_scf = 0;
                    $local_scf_logic[] = array('summary'=>$logic_summary,'detail'=>$logic_detail); 
                    $ant_scf = 0;
                    $ant_scf_logic[] = array('summary'=>$logic_summary,'detail'=>$logic_detail); 
                }  
                //Have we gone as far as we care to go?
                if(!$is_project_root_yn)
                {
                    if(!$computed_solution)
                    {
                        //Now process all the dependent workitems
                        foreach($wdetail['maps']['ddw'] as $depwid)
                        {
                            $this->computeBottomUpWorkitemStats($depwid, $outbound);
                        }                
                    }
                }
            } //END COUNT CHECK
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
}