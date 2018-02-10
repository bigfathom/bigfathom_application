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

require_once 'DatabaseNamesHelper.php';
require_once 'MapHelper.php';
require_once 'WorkApportionmentHelper.php';

/**
 * 
 * IMPORTANT NOTE THIS WILL BE DEPRECATED WHEN new module PersonAvailabilityInsight IS READY!!!!!!!!!!!!!!
 * 
 * This class helps get utilization information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class DEPRECATED_OLD_PersonUtilizationInsight
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oWAH = NULL;
    private $m_flags_ar = NULL;
    
    private $m_today_dt = NULL;
    private $m_personid = NULL;
    private $m_default_start_dt = NULL;
    private $m_default_end_dt = NULL;
    
    private $m_included_project_ar = NULL;
    private $m_included_project_status_attrib_ar = NULL;
    private $m_included_workitem_status_attrib_ar = NULL;
        
    private $m_excluded_project_ar = [];
    private $m_excluded_project_status_attrib_ar = [];
    private $m_excluded_workitem_status_attrib_ar = [];
    
    private $m_busy_period_ar = [];    

    private $m_cached_utilization = NULL;
    private $m_cached_start_dt = NULL;
    private $m_cached_end_dt = NULL;
    
    private $MIN_HOURS_TO_START_WORK = 1;
    private $MAX_SEARCH_DAYS = 2000;
                
    public function __construct($personid, $default_start_dt=NULL, $default_end_dt=NULL, $flags_ar=NULL, $today_dt=NULL)
    {
        if(empty($personid))
        {
            throw new \Exception("Missing required personid!");
        }
        
        if(empty($flags_ar) || !is_array($flags_ar))
        {
            $flags_ar = [];
        }
        if(empty($today_dt))
        {
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d",$now_timestamp);
        }
        $this->m_today_dt = $today_dt;
        if(empty($default_start_dt))
        {
            $default_start_dt = $this->m_today_dt;
        }
        $this->m_default_start_dt = $default_start_dt;
        if(empty($default_end_dt))
        {
            $default_end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($default_start_dt, 444);
        }        
        $this->m_default_end_dt = $default_end_dt;
        
        $this->m_personid = $personid;
        $this->m_flags_ar = $flags_ar;
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
        
        $this->m_included_project_ar = NULL;
        $this->m_included_project_status_attrib_ar = NULL;
        $this->m_included_workitem_status_attrib_ar = NULL;
        
        $this->m_excluded_project_ar = [];
        $this->m_excluded_project_status_attrib_ar = [];
        $this->m_excluded_workitem_status_attrib_ar = [];
        $this->m_busy_period_ar = [];
        
        $this->m_cached_track = [];
        $this->m_cached_track['utilization'] = [];
        $this->m_cached_track['utilization']['hit'] = 0;
        $this->m_cached_track['utilization']['miss'] = 0;
        $this->m_cached_track['utilization']['dump'] = 0;
        $this->m_cached_track['utilization']['populate'] = 0;
    }

    /**
     * Erase all local busy periods
     */
    public function clearAllCustomizations()
    {
        try
        {
            $this->m_cached_utilization = NULL;
            
            $this->m_included_project_ar = NULL;
            $this->m_included_project_status_attrib_ar = NULL;
            $this->m_included_workitem_status_attrib_ar = NULL;
        
            $this->m_excluded_project_ar = [];
            $this->m_excluded_project_status_attrib_ar = [];
            $this->m_excluded_workitem_status_attrib_ar = [];
            
            $this->m_busy_period_ar = [];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Define a busy range for local computation purposes
     */
    public function setBusyPeriod($start_dt,$end_dt,$busy_hours_per_day,$working_days_ar=NULL)
    {
        try
        {
            $this->m_busy_period_ar[$start_dt] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt,'hours_per_day'=>$busy_hours_per_day);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Count only some projects
     */
    public function setIncludedProjects($projects_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_included_project_ar = $projects_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Count some projects
     */
    public function setIncludedProjectStatuses($status_attrib_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_included_project_status_attrib_ar = $status_attrib_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Count some workitems
     */
    public function setIncludedWorkitemStatuses($status_attrib_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_included_workitem_status_attrib_ar = $status_attrib_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Ignore some projects
     */
    public function setExcludedProjects($projects_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_excluded_project_ar = $projects_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Ignore some projects
     */
    public function setExcludedProjectStatuses($status_attrib_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_excluded_project_status_attrib_ar = $status_attrib_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Ignore some workitems
     */
    public function setExcludedWorkitemStatuses($status_attrib_ar)
    {
        try
        {
            $this->m_cached_utilization = NULL;
            $this->m_excluded_workitem_status_attrib_ar = $status_attrib_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Give us all the intervals for the requested workitem
     */
    public function getIntervals4OneWorkitem($widfilter, $limit_edt=NULL)
    {
        if(empty($widfilter))
        {
            throw new \Exception("Missing required wid!");
        }
        
        $output = [];
        $summary = [];
        
        $sum_twh = 0;
        $sum_twd = 0;
        $sum_reh = 0;
        $min_dt = NULL;
        $max_dt = NULL;

        $sum_other_reh = 0;
        
        $wid_intervals = [];
        $sbi = $this->getSmartBucketInfo();
        if(!isset($sbi['intervals']))
        {
            //Simply means the workitems have no effort assoocatedxc with them
            //\bigfathom\DebugHelper::showNeatMarkup($sbi,"NOTE did not find intervals in sbi for widfilter=$widfilter and limit_edt=[$limit_edt]!");
            $outer_intervals = [];
        } else {
            $outer_intervals = $sbi['intervals'];
        }
        foreach($outer_intervals as $offset=>$detail)
        {
            $plain = $detail['plain'];
            $inner_intervals = $plain['intervals'];
            $other_af = 0;
            $wid_i_info = NULL;
            $local_sum_other_reh = 0;
            foreach($inner_intervals as $wid=>$i_info)
            {
                $this_edt = $i_info['edt'];
                if($widfilter == $wid)
                {
                    if(empty($limit_edt) || (!empty($limit_edt) && $limit_edt >= $this_edt))
                    {
                        $sum_twh += $i_info['twh'];
                        $sum_twd += $i_info['twd'];
                    }
                    $sum_reh += $i_info['reh'];
                    $sdt = $i_info['sdt'];
                    $wid_i_info = $i_info;
//DebugHelper::showNeatMarkup($i_info,"LOOK at wid#$wid edt=$this_edt  limit_edt=[$limit_edt]");                    
                } else {
                    $local_sum_other_reh += $i_info['reh'];
                    $other_af += $i_info['af'];
                }
            }
            if(!empty($wid_i_info))
            {
//drupal_set_message("LOOK end of innerloop thing for $widfilter limit_edt=[$limit_edt] @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@","info");
                //Count this interval
                $sum_other_reh += $local_sum_other_reh;
                $wid_i_info['other_af'] = $other_af;
                $wid_intervals[$sdt] = $wid_i_info;
                if($min_dt == NULL || $min_dt > $i_info['sdt'])
                {
                    $min_dt = $i_info['sdt'];
                }
                if($max_dt == NULL || $max_dt < $i_info['edt'])
                {
                    $max_dt = $i_info['edt'];
                }
            }
        }
        if($sum_twh > 0)
        {
            $af = $sum_reh/$sum_twh;
            $other_af = $sum_other_reh/$sum_twh;
            $total_af = ($sum_reh + $sum_other_reh)/$sum_twh;
        } else {
            $af = NULL;
            $other_af = NULL;
            $total_af = NULL;
        }
        $summary['sdt'] = $min_dt;
        $summary['edt'] = $max_dt;
        $summary['limit_edt'] = $limit_edt;
        $summary['total']['twd'] = $sum_twd;
        $summary['total']['twh'] = $sum_twh;
        $summary['total']['reh'] = $sum_reh;
        $summary['upct']['thiswid'] = $af;
        $summary['upct']['otherwids'] = "$other_af";
        $summary['upct']['total'] = $total_af;  //How busy are they in the period
        $output['summary'] = $summary;
        $output['intervals'] = $wid_intervals;
        return $output;
    }
    
    public function getSmartBucketInfo()
    {
        try
        {
            $personid = $this->m_personid;
            $bundle = $this->getUtilizationAndGapsDataBundleOfPerson();
            $all_smartbucketinfo = [];
            if(!empty($bundle['smartbucketobject']['by_personid'][$this->m_personid]))
            {
                $smartbucket = $bundle['smartbucketobject']['by_personid'][$this->m_personid];
                $computed_bundle = $smartbucket->getComputedNumberData();
                $all_interval_info = $computed_bundle['all_intervals_info'];
                $sorted_date_pairs = $all_interval_info['sorted_date_pairs'];
                $by_wid = $all_interval_info['by_wid'];

                $master_i_offset = 0;
                $allrowdata = [];
                foreach($sorted_date_pairs as $one_date_pair_info)
                {
                    $onerowdata = NULL;
                    $start_dt = $one_date_pair_info['start_dt'];
                    $end_dt = $one_date_pair_info['end_dt'];
                    $wid_map = $one_date_pair_info['wid_map'];
                    foreach($wid_map as $wid)
                    {
                        if(!isset($by_wid[$wid]['intervals']))
                        {
                            error_log("ERROR getSmartBucketInfo MISSING by_wid[$wid]['intervals']");
                        } else {
                            $wintervals = $by_wid[$wid]['intervals'];
                            if(!isset( $by_wid[$wid]['lookup']['intervals']['idx2local_offset'][$master_i_offset]))
                            {
                                error_log("ERROR getSmartBucketInfo MISSING by_wid[$wid]['lookup']['intervals']['idx2local_offset'][$master_i_offset]");
                            } else {
                                $local_i_offset = $by_wid[$wid]['lookup']['intervals']['idx2local_offset'][$master_i_offset];
                                $one_winterval = $wintervals[$local_i_offset];
                                $one_winterval['wid'] = $wid;
                                $onerowdata['intervals'][$wid] = $one_winterval;    //Assumes wid occurs at most once per interval
                            }
                        }
                    }
                    if(!empty($onerowdata))
                    {
                        $onerowfinal['metadata']['source'] = "maphelper>>>getUtilizationAndGapsDataBundle for person#{$personid}";
                        $onerowdata['start_dt'] = $start_dt;
                        $onerowdata['end_dt'] = $end_dt;
                        $onerowfinal['plain'] = $onerowdata;
                        $allrowdata[] = $onerowfinal;
                    }
                    $master_i_offset++;
                }
                $all_smartbucketinfo['intervals'] = $allrowdata;
            }
//DebugHelper::debugPrintNeatly(array('##$bundle'=>$bundle), "LOOK getSmartBucketInfo DONE");                
            return $all_smartbucketinfo;
                
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Give us utilization/availability bundle for a date range
     * Uses cached result if available
     * Normalizes the dates to allow for reverse requests
     */
    public function getUtilizationAndGapsDataBundleOfPerson($interval_start_dt=NULL, $interval_end_dt=NULL, $map_wid_allocations2ignore=NULL)
    {
        try
        {
            if(!empty($interval_start_dt) && !empty($interval_end_dt))
            {
                if($interval_start_dt > $interval_end_dt)
                {
                    //Normalize the order
                    $swapper = $interval_end_dt;
                    $interval_end_dt = $interval_start_dt;
                    $interval_start_dt = $swapper;
                }
            }
            if(empty($map_wid_allocations2ignore))
            {
                //Important for our cache checking
                $map_wid_allocations2ignore = [];
            }
            if($this->m_cached_utilization == NULL)
            {
                $cachewasblank = TRUE;
            } else {
                //If dates are provided, make sure cache covers them
                $cachewasblank = FALSE;
                $dumpreasons = [];
                if(!empty($interval_start_dt) && $interval_start_dt < $this->m_cached_start_dt)
                {
                    //Need to reload the cache
                    $this->m_cached_utilization = NULL;
                    $dumpreasons[] = "R1";
                } else
                if(!empty($interval_end_dt) && $interval_end_dt > $this->m_cached_end_dt)
                {
                    //Need to reload the cache
                    $this->m_cached_utilization = NULL;
                    $dumpreasons[] = "R2";
                } else
                if(!empty($this->m_cached_utilization['excluded_workitems']))
                {
                    //Not expected so reload
                    $this->m_cached_utilization = NULL;
                    $dumpreasons[] = "R3";
                } else {
                    //Lets only use exact match cache
                    if(count($this->m_cached_utilization['excluded_workitems']) != count($map_wid_allocations2ignore))
                    {
                        //Need to reload the cache
                        $this->m_cached_utilization = NULL;
                        $dumpreasons[] = "R4";
                    } else {
                        //Compare each ID
                        foreach($this->m_cached_utilization['excluded_workitems'] as $wid)
                        {
                            if(!isset($map_wid_allocations2ignore[$wid]))
                            {
                                //Need to reload the cache
                                $this->m_cached_utilization = NULL;
                                $dumpreasons[] = "R5";
                                break;
                            }
                        }
                    }
                }
                if($this->m_cached_utilization == NULL)
                {
                    $this->m_cached_track['utilization']['cache_dump_reasons'] = $dumpreasons;
                }
            }
            
            if($this->m_cached_utilization !== NULL)
            {
                $this->m_cached_track['utilization']['hit'] += 1;
            } else {
                $this->m_cached_track['utilization']['populate'] += 1;
                if($cachewasblank)
                {
                    $this->m_cached_track['utilization']['miss'] += 1;
                    $this->m_cached_track['utilization']['dump'] += 1;
                }
                
                //Load up the cache with new info
                if(empty($interval_start_dt))
                {
                    $interval_start_dt = $this->m_default_start_dt;
                }
                if(empty($interval_end_dt))
                {
                    $interval_end_dt = $this->m_default_end_dt;
                }
                $this->m_cached_daycounts = UtilityGeneralFormulas::getDayCountBundleBetweenDates($interval_start_dt, $interval_end_dt, $this->m_personid, TRUE);
                $through_future_days_count = NULL;
                $bundle = $this->m_oMapHelper->
                        getUtilizationAndGapsDataBundle($this->m_personid
                                ,$this->m_included_workitem_status_attrib_ar
                                ,$interval_start_dt,$interval_end_dt
                                ,$this->m_excluded_project_ar
                                ,$through_future_days_count,$map_wid_allocations2ignore);
                $bundle['excluded_workitems'] = $map_wid_allocations2ignore;
                $this->m_cached_utilization = $bundle;
                
            }
            return $this->m_cached_utilization;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Declare an important date range for this person
     */
    public function setSprintDateRange($sdt,$edt,$sprint_data_ar)
    {
        if(empty($sdt) || empty($edt))
        {
            throw new \Exception("Missing required date!");
        }
        if(empty($sprint_data_ar))
        {
            throw new \Exception("Missing reqwuired sprint_data_ar!");
        }
        $bundle = $this->getUtilizationAndGapsDataBundleOfPerson();
        if(!empty($bundle['smartbucketobject']['by_personid'][$this->m_personid]))
        {
            $sbo = $bundle['smartbucketobject']['by_personid'][$this->m_personid];
            $sbo->addSprintData($sdt, $edt, $sprint_data_ar);
        }
    }
    
    /**
     * Return the summary showing total busy time and total open time in the date range
     * NOTE: Does NOT try to fit any existing effort.
     */
    public function getWorkEffortBundleOfPerson($starting_dt=NULL, $ending_dt=NULL, $map_wid_allocations2ignore=NULL)
    {
        try
        {
            if(empty($starting_dt))
            {
                $starting_dt = $this->m_default_start_dt;
            } else {
                if($starting_dt < $this->m_default_start_dt)
                {
                    $this->m_default_start_dt = $starting_dt;
                }
            }
            if(empty($ending_dt))
            {
                $ending_dt = $this->m_default_end_dt;
            } else {
                if($ending_dt > $this->m_default_end_dt)
                {
                    $this->m_default_end_dt = $ending_dt;
                }
            }
            if(empty($map_wid_allocations2ignore))
            {
                $map_wid_allocations2ignore = [];
            }
            $seeking_effort_hours = 0;
            $ignore_seeking = TRUE;
            
            if (!empty($ending_dt) && $starting_dt > $ending_dt)
            {
                $errmsg = "Direction conflict cannot go from {$starting_dt} to {$ending_dt}!";
                DebugHelper::showStackTrace($errmsg);
                throw new \Exception($errmsg);
            }
            
            $bundle = $this->getWorkEffortForwardComputedDateBundleOfPerson($starting_dt, $ending_dt
                    , $seeking_effort_hours, $ignore_seeking
                    , $map_wid_allocations2ignore);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }   

    
    /**
     * Return the solution dates if there are any where the seeking_effort_hours can be worked by the person
     * after factoring in all the work they are already assigned to complete.
     */
    public function getWorkEffortBackwardComputedDateBundleOfPerson($filter_starting_dt, $filter_min_seek_dt
            , $seeking_effort_hours, $ignore_seeking=FALSE
            , $map_wid_allocations2ignore=NULL, $today_dt=NULL, $has_locked_end_dt=FALSE
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL)
    {
        try
        {   
            if(empty($filter_starting_dt))
            {
                throw new \Exception("Missing required search start date!");
            }
            if(empty($filter_min_seek_dt))
            {
                throw new \Exception("Missing required min seek date!");
            }            

            $has_locked_start_dt = FALSE;
            $bundle = $this->getWorkEffortComputedDateBundleOfPerson(-1, $filter_starting_dt, $filter_min_seek_dt
                    , $seeking_effort_hours, $ignore_seeking
                    , $map_wid_allocations2ignore
                    , $today_dt
                    , $has_locked_start_dt, $has_locked_end_dt
                    , $min_pct_buffer, $strict_min_pct, $allocation_pct);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    } 

    
    /**
     * Return the solution dates if there are any where the seeking_effort_hours can be worked by the person
     * after factoring in all the work they are already assigned to complete.
     */
    public function getWorkEffortForwardComputedDateBundleOfPerson($filter_starting_dt, $filter_max_ending_dt
            , $seeking_effort_hours, $ignore_seeking=FALSE
            , $map_wid_allocations2ignore=NULL, $today_dt=NULL, $has_locked_start_dt=FALSE
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL)
    {
        try
        {   
            if(empty($filter_starting_dt))
            {
                throw new \Exception("Missing required search start date!");
            }
            if(empty($filter_starting_dt))
            {
                return NULL;
            }
            if(empty($map_wid_allocations2ignore))
            {
                //We are not excluding any workitems from our utilization check
                $map_wid_allocations2ignore = [];
            }

            if (!empty($filter_max_ending_dt) && $filter_starting_dt > $filter_max_ending_dt)
            {
                $errmsg = "Direction conflict cannot go from {$filter_starting_dt} to {$filter_max_ending_dt}!";
                DebugHelper::showStackTrace($errmsg);
                throw new \Exception($errmsg);
            }
            
            $has_locked_end_dt = FALSE;
            $bundle = $this->getWorkEffortComputedDateBundleOfPerson(1, $filter_starting_dt, $filter_max_ending_dt
                    , $seeking_effort_hours, $ignore_seeking
                    , $map_wid_allocations2ignore
                    , $today_dt
                    , $has_locked_start_dt, $has_locked_end_dt
                    , $min_pct_buffer, $strict_min_pct, $allocation_pct);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    public function getWorkEffortComputedDateBundleOfPerson(
              $direction
            , $filter_starting_dt, $filter_max_ending_dt
            , $seeking_effort_hours, $ignore_seeking=FALSE
            , $map_wid_allocations2ignore=NULL
            , $today_dt=NULL
            , $has_locked_start_dt=FALSE, $has_locked_end_dt=FALSE
            , $min_pct_buffer=0, $strict_min_pct=FALSE, $allocation_pct=NULL)
    {
        try
        {
            $min_hours_to_start_work = 1;
            $maxdays = 1994;
            if(empty($today_dt))
            {
                $today_dt = $this->m_today_dt;
            }

            if ($direction == 1)
            {
                if (!empty($filter_max_ending_dt) && $filter_starting_dt > $filter_max_ending_dt)
                {
                    $errmsg = "Direction conflict cannot go from {$filter_starting_dt} to {$filter_max_ending_dt}!";
                    DebugHelper::showStackTrace($errmsg);
                    throw new \Exception($errmsg);
                }
            }
            else if ($direction == -1)
            {
                if ($filter_starting_dt < $filter_max_ending_dt)
                {
                    $errmsg = "Direction conflict cannot go from {$filter_starting_dt} to {$filter_max_ending_dt}!";
                    DebugHelper::showStackTrace($errmsg);
                    throw new \Exception($errmsg);
                }
            }
            else
            {
                throw new \Exception("Missing valid direction!");
            }

            if(empty($filter_max_ending_dt))
            {
                if(FALSE && $ignore_seeking)
                {
                    //Makes no sense to not provide an end date if not seeking a fit!
                    $errmsg = "Missing expected end date for start of {$filter_starting_dt}!";
                    DebugHelper::showStackTrace($errmsg);
                    throw new \Exception($errmsg);
                }
                $filter_max_ending_dt = $this->m_default_end_dt;    //TODO make this max of smartbucket?
            }

            $existing_utilization_bundle = $this->getUtilizationAndGapsDataBundleOfPerson($filter_starting_dt, $filter_max_ending_dt, $map_wid_allocations2ignore);
//'##$existing_utilization_bundle'=>$existing_utilization_bundle,
        
       $eub_keys = array_keys($existing_utilization_bundle)  ;   
       $eub_metadata = ($existing_utilization_bundle['metadata'])  ;   
       $eub_summary = ($existing_utilization_bundle['summary']);
       
       /*
       DebugHelper::debugPrintNeatly(
               array('##$eub_keys'=>$eub_keys
                , '##$eub_metadata'=>$eub_metadata
                , '##$eub_summary'=>$eub_summary
                , '##$workitems2exclude'=>$map_wid_allocations2ignore)
        ,FALSE,"LOOK before seek existing_utilization_bundle..........","........LOOK"
        ,"info");        
       */
       
            $bundle = UtilityGeneralFormulas::getWorkEffortComputedDateBundle(
                                                    $filter_starting_dt, $filter_max_ending_dt
                                                    , $this->m_personid
                                                    , FALSE
                                                    , $seeking_effort_hours, $ignore_seeking
                                                    , $direction
                                                    , $min_hours_to_start_work
                                                    , $maxdays
                                                    , $today_dt
                                                    , $existing_utilization_bundle
                                                    , $has_locked_start_dt, $has_locked_end_dt
                                                    , $min_pct_buffer, $strict_min_pct, $allocation_pct
                                                    , $map_wid_allocations2ignore);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}