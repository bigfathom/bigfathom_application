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

namespace bigfathom_utilization;

/**
 * Walk the tree from bottom up
 *
 * @author Frank Font
 */
class BottomUpInsights
{
    private $m_iteration_counter = 0;
    private $m_now_timestamp = NULL;
    private $m_ant_projectforecasters = NULL;
    private $m_flags_ar = NULL;
    
    private $m_locked_root_start_dt = NULL; //Because we cannot start before this date
    private $m_locked_root_end_dt = NULL;   //Work should not end after this date
    private $m_today_dt = NULL; //Because today may be bigger than our min date
    private $m_min_reference_dt = NULL;
    private $m_max_reference_dt = NULL;
    
    private $m_flag_availability_type_BY_OWNER = NULL;
    
    private $m_overrides_ar = NULL;
    private $m_limit_end_dt = NULL;

    private $m_map_wid_allocations2ignore;   //Ignores existing utilization assignments for these
    private $m_just_evaluate_yn;

    private $m_bottomup_calcs = NULL;
    private $m_oPADB = NULL;
    
    private $m_dirty_wids_map = NULL;
    
    public function __construct($oPADB, $flags_ar=NULL, $overrides_ar=NULL)
    {
        if(empty($oPADB))
        {
            throw new \Exception("Missing required oPADB!");
        }
        
        $this->m_dirty_wids_map = [];
        $this->m_oPADB = $oPADB;

        $this->m_flags_ar = $flags_ar;
        $this->m_just_evaluate_yn = isset($flags_ar['just_evaluate_yn']) ? $flags_ar['just_evaluate_yn'] : 0;
        $this->m_flag_availability_type_BY_OWNER = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_availability_type','BY_OWNER',TRUE);
        $this->m_min_reference_dt = isset($flags_ar['min_initial_reference_dt']) ? $flags_ar['min_initial_reference_dt'] : NULL;
        $this->m_max_reference_dt = isset($flags_ar['max_initial_reference_dt']) ? $flags_ar['max_initial_reference_dt'] : NULL;
        $this->m_today_dt = isset($flags_ar['today_dt']) ? $flags_ar['today_dt'] : NULL;
        
        $this->m_map_wid_allocations2ignore = isset($flags_ar['workitems2exclude']) ? $flags_ar['workitems2exclude'] : [];

        $this->m_min_pct_buffer = isset($flags_ar['min_pct_buffer']) ? $flags_ar['min_pct_buffer'] : 0;
        $this->m_strict_min_pct = isset($flags_ar['strict_min_pct']) ? $flags_ar['strict_min_pct'] : FALSE;
        $this->m_force_lock_project_start_dt = isset($flags_ar['force_lock_project_start_dt']) ? $flags_ar['force_lock_project_start_dt'] : FALSE;

        $this->m_utilization_planning_bundle = isset($flags_ar['utilization_planning_bundle']) ? $flags_ar['utilization_planning_bundle'] : NULL;
        $this->m_candidate_pct_bundle = isset($flags_ar['candidate_pct_bundle']) ? $flags_ar['candidate_pct_bundle'] : NULL;
        
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
        
        $lrsdt = $this->m_oPADB->getLockedRootStartDate();
        $this->m_locked_root_start_dt = $lrsdt;
        if(!empty($lrsdt))
        {
            if($lrsdt > $this->m_min_reference_dt)
            {
                //Tighter constraint
                $this->m_min_reference_dt = $lrsdt;
            }
        }
        
        $lredt = $this->m_oPADB->getLockedRootEndDate();
        $this->m_locked_root_end_dt = $lredt;
        if(!empty($lredt))
        {
            /* DO NOT DO THIS
            if($lredt < $this->m_max_reference_dt)
            {
                //Tighter constraint
                $this->m_min_reference_dt = $lredt;
            }
            */
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
        
        $this->m_bottomup_calcs = [];
        $this->m_iteration_counter = 0;
    }
    
    private function getArrayNonEmpty($a,$b,$c=NULL,$d=NULL)
    {
        $candidates = [];
        if(!empty($a))
        {
            $candidates[] = $a;
        }
        if(!empty($b))
        {
            $candidates[] = $b;
        }
        if(!empty($c))
        {
            $candidates[] = $c;
        }
        if(!empty($d))
        {
            $candidates[] = $d;
        }
        return $candidates;
    }
    
    private function minNonEmpty($a,$b,$c=NULL,$d=NULL)
    {
        $candidates = $this->getArrayNonEmpty($a,$b,$c,$d);
        if(count($candidates)==0)
        {
            $min_dt = NULL;
        } else {
            $min_dt = min($candidates);
        }
        return $min_dt;
    }
    
    private function maxNonEmpty($a,$b,$c=NULL,$d=NULL)
    {
        $candidates = $this->getArrayNonEmpty($a,$b,$c,$d);
        if(count($candidates)==0)
        {
            $max_dt = NULL;
        } else {
            $max_dt = max($candidates);
        }
        return $max_dt;
    }
    
    public function declareRelevantWorkitemRecordChanged($wid)
    {
        $paths_flatlist = $this->m_oPADB->getPathsToWorkitemAsFlatList($wid);
        foreach($paths_flatlist as $onepath)
        {
            //All these are now dirty too
            foreach($onepath as $wid)
            {
                $this->m_dirty_wids_map[$wid] = $wid;
            }
        }
    }
    
    /**
     * return the insight for one workitem
     */
    public function getOneWorkitemInsightBundle($wid)
    {
        try
        {
            $paths_flatlist = $this->m_oPADB->getPathsFromWorkitemToLeafAsFlatList($wid);
            $checked_ant_map = [];
            $latest_ant_edt = NULL;
            $min_ant_dt = $this->m_min_reference_dt;
            $max_ant_dt = $min_ant_dt;
            $daw_map = [];

            foreach($paths_flatlist as $one_path)
            {
                if(isset($one_path[1]))
                {
                    $ant_wid = $one_path[1];
                    $daw_map[$ant_wid] = $ant_wid;
                    if(!isset($checked_ant_map[$ant_wid]))
                    {
                        $checked_ant_map[$ant_wid] = $ant_wid;
                        if(!isset($this->m_bottomup_calcs[$ant_wid]) || isset($this->m_dirty_wids_map[$ant_wid]))
                        {
                            $this->getOneWorkitemInsightBundle($ant_wid);
                        }
                        $ant_buc_info = $this->m_bottomup_calcs[$ant_wid];
                        $ant_buc_latest_ant_edt = $ant_buc_info['latest_ant_edt'];
                        $ant_edt = $ant_buc_info['edt'];
                        $min_ant_dt = \bigfathom\UtilityGeneralFormulas::getNotEmptyMin($min_ant_dt, $ant_buc_info['min_ant_dt'],$ant_buc_info['sdt'],$ant_buc_info['edt']);
                        $max_ant_dt = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($max_ant_dt, $ant_buc_info['max_ant_dt'],$ant_buc_info['sdt'],$ant_buc_info['edt']);

                        if(empty($ant_buc_latest_ant_edt) || empty($ant_edt))
                        {
                            $ant_ref_edt = !empty($ant_buc_latest_ant_edt) ? $ant_buc_latest_ant_edt : $ant_edt;
                        } else {
                            $ant_ref_edt = max($ant_buc_latest_ant_edt, $ant_edt);
                        }

                        if(!empty($ant_ref_edt) && (empty($latest_ant_edt) || $ant_ref_edt > $latest_ant_edt))
                        {
                            //Tighter constraint
                            $latest_ant_edt = $ant_ref_edt;
                        }
                    }
                }
            }
            
            //Compute the total_branch_remaining_effort_hours now
            $total_ant_branch_reh = 0;
            foreach($daw_map as $ant_wid)
            {
                $ant_reh = $this->m_bottomup_calcs[$ant_wid]['reh'];
                $ant_branch_reh = $this->m_bottomup_calcs[$ant_wid]['ant_branch_reh'];
                $total_ant_branch_reh += ($ant_reh + $ant_branch_reh);
            }
            
            //Set our assessment now
            $new_insight = $this->m_oPADB->getDateAndRemainingEffortHoursInfoOfWorkitem($wid);

            $new_insight['min_reference_dt'] = $this->m_min_reference_dt;
            $new_insight['max_reference_dt'] = $this->m_max_reference_dt;
            $new_insight['latest_ant_edt'] = $latest_ant_edt;
            $new_insight['min_ant_dt'] = $min_ant_dt;
            $new_insight['max_ant_dt'] = $max_ant_dt;
            $new_insight['ant_branch_reh'] = $total_ant_branch_reh;
            $this->m_bottomup_calcs[$wid] = $new_insight;   //Store the calcs
            unset($this->m_dirty_wids_map[$wid]);           //Make sure it is not on our dirty list
            $bundle = array('info'=>"computeOneWorkitem for wid#$wid" 
                    , 'paths2leaf_list'=>$paths_flatlist
                    , 'insight'=>$this->m_bottomup_calcs[$wid] );

            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
