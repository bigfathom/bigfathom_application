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

require_once('WorkApportionmentHelper.php');

/**
 * POSSIBLE CANDIATE FOR DEPRECATOIN -- USE LOGIC FROM AUTOFILL MODULE INSTEAD
 * This class manages number data based on potentially overlapping date ranges
 * It can return insight into slices of date ranges.
 *
 * @author Frank Font of Room4me.com Software LLC
 * @deprecated since version 2017
 */
class DateRangeSmartNumberBucket
{
    private $m_personid;
    private $m_start_dt_idx;
    private $m_end_dt_idx;
    private $m_all_dates;
    private $m_map_by_sdt_all_date_pairs;
    private $m_map_by_edt_all_date_pairs;
    private $m_raw_data;
    private $m_aComputedNumberData = NULL;
    private $m_aComputedNumberKeyName = NULL;
    private $m_today_dt = NULL;
    private $m_oWAH = NULL;
    private $m_min_dt=NULL;
    private $m_max_dt=NULL;
    private $m_wid_idx=NULL;
    private $m_sprint_idx=NULL;
    
    public function __construct($personid_filter=NULL, $today_dt=NULL)
    {
        if(!empty($today_dt))
        {
            $this->m_reftime_ar['now'] = $today_dt;
            $this->m_today_dt = $today_dt;
        } else {
            $now = time();
            $this->m_reftime_ar['now'] = $now;
            $this->m_today_dt = gmdate("Y-m-d",$now);
        }
        $this->m_raw_data = [];
        $this->m_all_dates = [];
        $this->m_map_by_sdt_all_date_pairs = [];
        $this->m_map_by_edt_all_date_pairs = [];
        $this->m_end_dt_idx = [];
        $this->m_start_dt_idx = [];
        $this->m_personid = $personid_filter;
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
    }
    
    /**
     * Add a SPRINT date range with number information into our smart bucket
     * @param type $start_dt our start date
     * @param type $end_dt our end date
     * @param type $data_ar contains the number data and 'sprintid',effective_start_dt,effective_end_dt
     */
    public function addSprintData($start_dt, $end_dt, $data_ar)
    {
        if(strlen($start_dt) !== 10 || substr($start_dt, 4, 1) !== '-')
        {
            throw new \Exception("Invalid format for start_dt in [$start_dt, $end_dt]; expected YYYY-MM-DD");
        }
        if(strlen($end_dt) !== 10 || substr($end_dt, 4, 1) !== '-')
        {
            throw new \Exception("Invalid format for end_dt in [$start_dt, $end_dt]; expected YYYY-MM-DD");
        }
        $this->m_aComputedNumberData = NULL;
        $data_ar['start_dt'] = $start_dt;
        $data_ar['end_dt'] = $end_dt;
        $this->m_raw_data[] = $data_ar;
        
        $idx = count($this->m_raw_data) - 1;
        
        if(!isset($this->m_start_dt_idx[$start_dt]))
        {
            $this->m_start_dt_idx[$start_dt] = [];
        }
        $this->m_start_dt_idx[$start_dt][] = $idx;
        
        if(!isset($this->m_end_dt_idx[$end_dt]))
        {
            $this->m_end_dt_idx[$end_dt] = [];
        }
        $this->m_end_dt_idx[$end_dt][] = $idx;

        $sprintid = $data_ar['sprintid'];
        $this->m_sprint_idx[$sprintid] = $idx;
        
        //Now write both dates to our boundary tracking array
        $this->m_all_dates[$start_dt] = $start_dt;
        $this->m_all_dates[$end_dt] = $end_dt;
        if(!isset($this->m_map_by_sdt_all_date_pairs[$start_dt]))
        {
            $this->m_map_by_sdt_all_date_pairs[$start_dt] = [];
            $this->m_map_by_edt_all_date_pairs[$end_dt] = [];
        }
        $this->m_map_by_sdt_all_date_pairs[$start_dt][] = array('sdt'=>$start_dt,'edt'=>$end_dt);
        $this->m_map_by_edt_all_date_pairs[$end_dt][] = array('sdt'=>$start_dt,'edt'=>$end_dt);
        
        if(empty($this->m_min_dt) || $start_dt < $this->m_min_dt)
        {
            $this->m_min_dt = $start_dt;
        }
        if(empty($this->m_min_dt) || $end_dt < $this->m_min_dt)
        {
            //Can happen if start was blank
            $this->m_min_dt = $end_dt;
        }
        
        if(empty($this->m_max_dt) || $end_dt > $this->m_max_dt)
        {
            $this->m_max_dt = $end_dt;
        }
        if(empty($this->m_max_dt) || $start_dt > $this->m_max_dt)
        {
            //Can happen if end was blank
            $this->m_max_dt = $start_dt;
        }
    }
    
    /**
     * Add a WORKITEM date range with number information into our smart bucket
     * @param type $start_dt our start date
     * @param type $end_dt our end date
     * @param type $data_ar contains the number data and 'wid',effective_start_dt,effective_end_dt
     */
    public function addWorkitemData($start_dt, $end_dt, $data_ar)
    {
        if(strlen($start_dt) !== 10 || substr($start_dt, 4, 1) !== '-')
        {
            throw new \Exception("Invalid format for start_dt in [$start_dt, $end_dt]; expected YYYY-MM-DD");
        }
        if(strlen($end_dt) !== 10 || substr($end_dt, 4, 1) !== '-')
        {
            throw new \Exception("Invalid format for end_dt in [$start_dt, $end_dt]; expected YYYY-MM-DD");
        }
        $this->m_aComputedNumberData = NULL;
        $data_ar['start_dt'] = $start_dt;
        $data_ar['end_dt'] = $end_dt;
        $this->m_raw_data[] = $data_ar;
        
        $idx = count($this->m_raw_data) - 1;
        
        if(!isset($this->m_start_dt_idx[$start_dt]))
        {
            $this->m_start_dt_idx[$start_dt] = [];
        }
        $this->m_start_dt_idx[$start_dt][] = $idx;
        
        if(!isset($this->m_end_dt_idx[$end_dt]))
        {
            $this->m_end_dt_idx[$end_dt] = [];
        }
        $this->m_end_dt_idx[$end_dt][] = $idx;

        $wid = $data_ar['wid'];
        $this->m_wid_idx[$wid] = $idx;
        
        //Now write both dates to our boundary tracking array
        $this->m_all_dates[$start_dt] = $start_dt;
        $this->m_all_dates[$end_dt] = $end_dt;
        if(!isset($this->m_map_by_sdt_all_date_pairs[$start_dt]))
        {
            $this->m_map_by_sdt_all_date_pairs[$start_dt] = [];
            $this->m_map_by_edt_all_date_pairs[$end_dt] = [];
        }
        $this->m_map_by_sdt_all_date_pairs[$start_dt][] = array('sdt'=>$start_dt,'edt'=>$end_dt);
        $this->m_map_by_edt_all_date_pairs[$end_dt][] = array('sdt'=>$start_dt,'edt'=>$end_dt);
        
        if(empty($this->m_min_dt) || $start_dt < $this->m_min_dt)
        {
            $this->m_min_dt = $start_dt;
        }
        if(empty($this->m_min_dt) || $end_dt < $this->m_min_dt)
        {
            //Can happen if start was blank
            $this->m_min_dt = $end_dt;
        }
        
        if(empty($this->m_max_dt) || $end_dt > $this->m_max_dt)
        {
            $this->m_max_dt = $end_dt;
        }
        if(empty($this->m_max_dt) || $start_dt > $this->m_max_dt)
        {
            //Can happen if end was blank
            $this->m_max_dt = $start_dt;
        }
    }
    
    /**
     * Get all the workitem IDs factored into the bucket
     */
    public function getWorkitemIDs()
    {
        if(isset($this->m_wid_idx))
        {
            return array_keys($this->m_wid_idx);
        } else {
            return [];
        }
    }
    
    /**
     * Get the underlying data for one workitem if we have it
     */
    public function getWorkitemData($wid)
    {
        if(isset($this->m_wid_idx[$wid]))
        {
            $idx = $this->m_wid_idx[$wid];
            return $this->m_raw_data[$idx];
        } else {
            return NULL;
        }
    }
    
    /**
     * @return type array of all the boundary dates
     */
    public function getAllBoundaryDates()
    {
        return $this->m_all_dates;
    }

    private function updateSortedDatePairs($interval_sdt, $interval_edt, &$sorted_date_pairs)
    {
        //Add all overlapping workitems
        $raw_data_offset = [];
        $wid_map = [];
        $sprintid_map = [];
        foreach($this->m_raw_data as $data_offset=>$data_ar)
        {
            $data_ar = $this->m_raw_data[$data_offset];
            if(!empty($data_ar['wid']))
            {
                //Must be a workitem
                $wid = $data_ar['wid'];            
                $effective_start_dt = $data_ar['effective_start_dt'];
                $effective_end_dt = $data_ar['effective_end_dt'];
                if($effective_start_dt <= $interval_edt && $effective_end_dt >= $interval_sdt)
                {
                    $raw_data_offset[$data_offset] = $data_offset;
                    $wid_map[$wid] = $wid;
                }
            } else {
                //Must be a sprint
                $sprintid = $data_ar['sprintid'];            
                $effective_start_dt = $data_ar['effective_start_dt'];
                $effective_end_dt = $data_ar['effective_end_dt'];
                if($effective_start_dt <= $interval_edt && $effective_end_dt >= $interval_sdt)
                {
                    $raw_data_offset[$data_offset] = $data_offset;
                    $sprintid_map[$sprintid] = $sprintid;
                }
            }
        }
        $daycount_detailinfo = $this->getPersonDayCountBundleBetweenDates($interval_sdt, $interval_edt);
        $dci = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDatesFromExistingDetail($daycount_detailinfo, $interval_sdt, $interval_edt);
        $newpair = array('start_dt'=>$interval_sdt,'end_dt'=>$interval_edt,'day_count_info'=>$dci,'raw_data_offset'=>$raw_data_offset,'wid_map'=>$wid_map,'sprintid_map'=>$sprintid_map);
        $sorted_date_pairs[$interval_sdt] = $newpair;
    }
    
    private function getAllSortedEdgeDatePairs()
    {
        $sorted_date_pairs = [];
        $just_edge_dates = array_keys($this->m_all_dates);
        sort($just_edge_dates);
        $prev_computed_sdt = NULL;
        $computed_edt = NULL;
        foreach($just_edge_dates as $edge_dt)
        {
            $computed_edt =UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($edge_dt, -1);
            if(!empty($prev_computed_sdt) && $prev_computed_sdt <= $computed_edt)
            {
                //We have a space interval
                $this->updateSortedDatePairs($prev_computed_sdt,$computed_edt,$sorted_date_pairs);
            }
            $this->updateSortedDatePairs($edge_dt,$edge_dt,$sorted_date_pairs);
            $prev_computed_sdt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($edge_dt, 1);
        }

        return $sorted_date_pairs;
    }
    
    /**
     * Go through the utilization records to compile a useful flat collection of values
     */
    public static function getFlatUtilizationInfoForDateRange($effective_start_dt, $effective_end_dt, $utilization_records, $map_wids2ignore=NULL, $remaining_effort_hours=0)
    {
        try
        {
            if(empty($effective_start_dt))
            {
                $title = "Missing start date!";
                DebugHelper::showStackTrace($title,'error',5);
                throw new \Exception($title);
            }
            if(empty($effective_end_dt))
            {
                throw new \Exception("Missing end date!");
            }
            if(empty($utilization_records) && !is_array($utilization_records))
            {
                throw new \Exception("Missing utilization information!");
            }
            if(empty($map_wids2ignore))
            {
                $map_wids2ignore = [];
            }
            if($effective_start_dt > $effective_end_dt)
            {
                //For adding purposes, reverse them here.
                $thedt = $effective_start_dt;
                $effective_start_dt = $effective_end_dt;
                $effective_end_dt = $thedt;
                /*
                $title = "Start date is bigger than end date! ($effective_start_dt > $effective_end_dt)";
                DebugHelper::showStackTrace($title,'error',5);
                throw new \Exception($title);
                 * 
                 */
            }
            
            //Gather up all the problem areas if any for the person in the work period
            $cuiBundle = [];
            $simple_total_days = UtilityGeneralFormulas::getDayCountInDateRange($effective_start_dt, $effective_end_dt);
            $overage_utildetail_ar = [];
            $over_count = 0;
            $maxutilpct = 0;
            $upct_factors = [];
            $is_ok = TRUE;
            $domain_twh = 0;
            $domain_twd = 0;
            $total_reh_map = [];
            $total_af_map = [];
            $twh_map = [];
            $twd_map = [];
            $last_utilized_edt = NULL;
            $last_utilized_interval = NULL;
            
            //Okay, we ahve some utilization information for this one, go through it.
            foreach($utilization_records as $all_intervals_info)
            {
                $oneinfo = [];
                if(empty($all_intervals_info['metadata']['min_dt']))
                {
                    //This means they have NO utilization history.
                    //DebugHelper::showNeatMarkup(array('metadata'=>$all_intervals_info['metadata'],'##urecs'=>$utilization_records),"DID NOT FIND METADATA in at least one interval of this set [$effective_start_dt, $effective_end_dt]");
                    break; //Simply continue outsie the loop
                    //DebugHelper::showStackTrace();
                    //throw new \Exception("Missing expected metadata in " . print_r($all_intervals_info,TRUE));
                }
                $start_dt = $all_intervals_info['metadata']['min_dt']; //$all_intervals_info['start_dt'];					
                $end_dt = $all_intervals_info['metadata']['max_dt'];   //$all_intervals_info['end_dt'];
                if(($end_dt >= $effective_start_dt && $start_dt <= $effective_end_dt))
                {
                    //There is overlap in some or all of the intervals - compute the percent utilization without regard to specific hour count
                    $sorted_date_pairs = $all_intervals_info['sorted_date_pairs'];
                    $intervals4workitems = $all_intervals_info['by_wid'];

                    //Get the relevant intervals
                    foreach($intervals4workitems as $onewid=>$intervalsinfo4oneworkitem)
                    {
                        //Get the relevant intervals
                        if(empty($map_wids2ignore[$onewid]))
                        {
                            $intervals4oneworkitem = $intervalsinfo4oneworkitem['intervals'];
                            foreach($intervals4oneworkitem as $oneinterval)
                            {
                                $isdt = $oneinterval['sdt'];
                                $iedt = $oneinterval['edt'];
                                if($effective_end_dt >= $isdt && $effective_start_dt <= $iedt)
                                {
                                    if(empty($sorted_date_pairs[$isdt]))
                                    {
                                        throw new \Exception("Only aligned intervals are supported at this time! (No date pair starts on '$isdt' for wid=$onewid)");
                                    }
                                    $onepairinfo = $sorted_date_pairs[$isdt];
                                    if($onepairinfo['end_dt'] != $iedt)
                                    {
                                        throw new \Exception("Only aligned intervals are supported at this time! (No date pair ends on '$iedt' with a start of '$isdt' for wid=$onewid)");
                                    }
                                    $reh = $oneinterval['reh'];
                                    $f = $oneinterval['af'];
                                    if(!isset($total_reh_map[$isdt]))
                                    {
                                        $total_reh_map[$isdt] = $reh;
                                    } else {
                                        $total_reh_map[$isdt] += $reh;
                                    }
                                    if(!isset($total_af_map[$isdt]))
                                    {
                                        $total_af_map[$isdt] = $f;
                                    } else {
                                        $total_af_map[$isdt] += $f;
                                    }
                                    $twh_map[$isdt] = $oneinterval['twh'];
                                    $twd_map[$isdt] = $oneinterval['twd'];
                                }
                            }
                        }
                    }
                }
            }
            $justkeys_twh = array_keys($twh_map);
            sort($justkeys_twh);
            $sum_reh_from_avail_days = 0;
            $reh_overage = 0;
            foreach($justkeys_twh as $isdt)
            {
                $onepairinfo = $sorted_date_pairs[$isdt];
                $iedt = $onepairinfo['end_dt'];
                $i_twh = $twh_map[$isdt];
                $i_twd = $twd_map[$isdt];
                $domain_twh += $i_twh;
                $domain_twd += $i_twd;
                $i_total_af = $total_af_map[$isdt];
                $i_total_reh = $total_reh_map[$isdt];
                $i_total_days = $onepairinfo['day_count_info']['total'];
                $sum_reh_from_avail_days += $i_total_reh;
                
                $oneupctfactor = array($i_total_af, $i_total_days, $i_total_reh, $i_twh, $i_twd, array($isdt, $iedt));
                $upct_factors[] = $oneupctfactor;
                
                if($i_total_reh > 0)
                {
                    $last_utilized_edt = $iedt; //End date of this interval
                    $last_utilized_interval = count($upct_factors)-1;
                    //TODO find the soonest date in the interval too where af < .95?
                }
                if($i_total_af > $maxutilpct)
                {
                    $maxutilpct = $i_total_af;
                }
                $supct = round(100 * $i_total_af,2);  //Check this otherwise rounding errors!!!!
                $reh_overage = round(100 * ($sum_reh_from_avail_days - $domain_twh), 0)/100; //Check otherwise rounding error!!!!
                if($supct > 100 || $reh_overage > 0)// $sum_reh > $domain_twh)
                {
                    $over_count++;
                    if($isdt == $iedt)
                    {
                        if($supct > 100)
                        {
                            $tooltip = "$supct% on $isdt";
                        } else {
                            $tooltip = "too few hours on $isdt (need $reh_overage more)";
                        }
                    } else {
                        if($supct > 100)
                        {
                            $tooltip = "$supct% from $isdt to $iedt";
                        } else {
                            $tooltip = "too few hours from $isdt to $iedt (need $reh_overage more)";
                        }
                    }
                    $oneinfo = array('start_dt'=>$isdt,'end_dt'=>$iedt,'tooltip'=>$tooltip);
                    $sortkey = "{$isdt}_{$over_count}";
                    $overage_utildetail_ar[$sortkey] = $oneinfo;
                }
            }
            ksort($overage_utildetail_ar);
            $terms = 0;
            $sumdays = 0;
            foreach($upct_factors as $onefactor)
            {
                $chunk_upct = $onefactor[0];
                $chunk_total_days = $onefactor[1];
                
                $terms += $chunk_total_days * $chunk_upct;
                $sumdays += $chunk_total_days;
                
            }
            if($terms > 0)
            {
                if($simple_total_days > 0)
                {
                    $weighted_avg_upct = $terms / $simple_total_days;
                } else {
                    if($terms > 0)
                    {
                        error_log("ERROR: Possible problem terms=$terms but (from '$effective_start_dt' to '$effective_end_dt') simple_total_days=0");
                    }
                    if($sumdays > 0)
                    {
                        $weighted_avg_upct = $terms / $sumdays;
                        $errinfo = "Found instance of zero days (from '$effective_start_dt' to '$effective_end_dt') to work $terms hours (using sumdays as $sumdays)";
                    } else {
                        $weighted_avg_upct = 100;
                        $errinfo = "Found instance of zero days (from '$effective_start_dt' to '$effective_end_dt') to work $terms hours (sumdays is ZERO)";
                    }
                    drupal_set_message($errinfo,'warning');
                    error_log("WARNING:" . $errinfo);
                }
            } else {
                $weighted_avg_upct = 0;
            }
            $weightedutilpct_rounded = round($weighted_avg_upct * 100,2);
            $weighted_avg_upct_sort = $weightedutilpct_rounded * 100;
            $clean_reh = round($remaining_effort_hours, 2);
            $clean_sum_reh = round($sum_reh_from_avail_days, 2);
            if($over_count > 0 || $reh_overage > 0 || $clean_sum_reh < $clean_reh)
            {
                $is_ok = FALSE;
            }
            $cuiBundle['metadata']['map_wids2ignore'] = $map_wids2ignore;
            $cuiBundle['is_ok'] = $is_ok;
            $cuiBundle['reh_overage'] = $reh_overage;
            $cuiBundle['domain_twh'] = $domain_twh;
            $cuiBundle['domain_twd'] = $domain_twd;
            $cuiBundle['total_reh'] = $sum_reh_from_avail_days; //THIS IS NOT THE DECLARED REH, ONLY WHAT WAS AVAILBLE IN DAYS!!!
            $cuiBundle['real_reh'] = $remaining_effort_hours;
            $cuiBundle['total_days'] = $simple_total_days;
            $cuiBundle['upct_factors'] = $upct_factors;
            $cuiBundle['overage_utildetail_ar'] = $overage_utildetail_ar;
            $cuiBundle['over_count'] = $over_count;
            $cuiBundle['maxutilpct'] = $maxutilpct;
            $cuiBundle['last_utilized_edt'] = $last_utilized_edt;
            $cuiBundle['last_utilized_interval'] = $last_utilized_interval;
            $cuiBundle['weightedutilpct_rounded'] = $weightedutilpct_rounded;
            $cuiBundle['weighted_avg_upct_sort'] = $weighted_avg_upct_sort;
            return $cuiBundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Tells us if there are overages etc.
     */
    public function getUtilizationDerivedInsights($recompute=FALSE)
    {
        try
        {
            $map_projectid2max_effective_end_dt = [];
            $intervaloffset2lasthijacked_date_map = [];
            $latest_smash_dt = NULL;
            $daycountinfo = $this->getPersonDayCountBundleBetweenDates();
            $utilization_records = $this->getComputedNumberData($recompute);
            if(empty($this->m_min_dt))
            {
                $effective_start_dt = $this->m_today_dt;
            } else {
                $effective_start_dt = $this->m_min_dt;
            }
            if(empty($this->m_max_dt))
            {
                $effective_end_dt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($effective_start_dt, 100);
            } else {
                $effective_end_dt = $this->m_max_dt;
            }
            $cuiBundle = self::getFlatUtilizationInfoForDateRange($effective_start_dt, $effective_end_dt, $utilization_records);
            $map_wid2sooner_edt = [];
            $map_hit_root = [];
            $map_wid2reh = [];
            $map_wid2existing_edt = [];
            $debug_widmap = [];
            $earliest_edt4person = NULL;
            foreach($utilization_records as $all_intervals_info)
            {
                $intervals4workitems = $all_intervals_info['by_wid'];

                //Get the relevant intervals
                foreach($intervals4workitems as $onewid=>$intervalsinfo4oneworkitem)
                {
                    $wdetail = $this->getWorkitemData($onewid);
                    $map_wid2reh[$onewid] = $wdetail['remaining_effort_hours'];
                    $map_wid2existing_edt[$onewid] = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];;
                    $owner_projectid = $wdetail['owner_projectid'];
                    $project_root_yn = $wdetail['project_root_yn'];
                    if($project_root_yn)
                    {
                        //We will assign a date AFTER processing everything else
                        $map_hit_root[$owner_projectid] = array('wid'=>$onewid,'i'=>$intervalsinfo4oneworkitem);
                    } else {
                        $current_end_dt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
                        $local_intervals = $intervalsinfo4oneworkitem['intervals'];
                        $last_nonempty_local_idx = NULL;
                        $worked_prior_to_last_nonempty = 0;
                        $prev_local_offset = NULL;
                        foreach($local_intervals as $local_offset=>$local_detail)
                        {
                            if($local_detail['reh']>0)
                            {
                                $last_nonempty_local_idx = $local_offset;
                                if($prev_local_offset !== NULL)
                                {
                                    $worked_prior_to_last_nonempty += $local_intervals[$prev_local_offset]['reh'];
                                }
                                $prev_local_offset = $local_offset;
                            }
                        }
$debug_widmap[$onewid] = "$onewid@1 ced=$current_end_dt";
                        if($last_nonempty_local_idx == NULL)
                        {
                            //This means there are no hours for this workitem
                            $current_start_dt = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
                            $map_wid2sooner_edt[$onewid] = $current_start_dt;
                        } else {
                            //Find the earliest day in this interval where we still need to work
                            $local_detail = $local_intervals[$last_nonempty_local_idx];
$debug_widmap[$onewid] = "$onewid@2";

                            $isdt = $local_detail['sdt'];
                            $iedt = $local_detail['edt'];
                            $ireh = $local_detail['reh'];
                            $itotal_worked_reh = 0;
                            if(!isset($intervaloffset2lasthijacked_date_map[$isdt]))
                            {
                                $sdt = $isdt;
                            } else {
                                $reference_dt = $intervaloffset2lasthijacked_date_map[$isdt];
                                $sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($reference_dt, 1);
                            }
                            $found_sooner = FALSE;
                            $last_visited_dt = NULL;
                            if(empty($latest_smash_dt) || $latest_smash_dt < $sdt)
                            {
                                //This one can be smashed so smash it
                                foreach($daycountinfo['daily_detail'] as $onedate=>$daydetail)
                                {
                                    if($onedate >= $sdt)
                                    {
                                        $workthisday = .99 * $daydetail['workhoursinday']; // <-- NEEDS TO FACTOR IN UTILIZTAION!!!???
                                        $itotal_worked_reh += $workthisday;
                                        $last_visited_dt = $onedate;
                                        if(($itotal_worked_reh >= $ireh + 1) || $onedate >= $iedt)
                                        {
                                            //Done!
                                            if($onedate < $iedt)
                                            {
                                                //We found a sooner end date
                                                $intervaloffset2lasthijacked_date_map[$isdt] = $onedate;
                                                $found_sooner = TRUE;
                                                $latest_smash_dt = $onedate;
                                            }
                                            break;
                                        }
                                    }
                                }
                            } else {
                                //Cannot smash it, but grab its stats
                                $current_endt_dt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
                                $last_visited_dt = $current_endt_dt;
                            }

                            if(empty($last_visited_dt))
                            {
                                $last_visited_dt = $iedt;
                            }
                            
                            if($found_sooner && (empty($current_end_dt) || $current_end_dt > $last_visited_dt))
                            {
                                //We will move the end date up.
                                $computed_edt = $last_visited_dt;
                                $map_wid2sooner_edt[$onewid] = $last_visited_dt;
                            } else {
                                //Leave it alone.
                                $computed_edt = $current_end_dt;
                            }
                            
                            if(empty($map_projectid2max_effective_end_dt[$owner_projectid]) || $computed_edt > $map_projectid2max_effective_end_dt[$owner_projectid])
                            {
                                $map_projectid2max_effective_end_dt[$owner_projectid] = $computed_edt;
                            }
                        }
                    }
                }
            }
            
            //Did we run into the project root workitems?
            foreach($map_projectid2max_effective_end_dt as $owner_projectid=>$max_effective_end_dt)
            {
                if(!empty($map_hit_root[$owner_projectid]) && !empty($max_effective_end_dt))
                {
                    //Assign a date to the root
                    //$hit_root = array('wid'=>$onewid,'i'=>$intervalsinfo4oneworkitem);
                    $onewid = $map_hit_root[$owner_projectid]['wid'];
                    $root_reh = $map_wid2reh[$onewid];
                    $root_existing_edt = $map_wid2existing_edt[$onewid];
                    if($root_reh < .001)
                    {
                        //Shortcircut case, just end at end of last workitem
                        if(empty($root_existing_edt) || $max_effective_end_dt < $root_existing_edt)
                        {
                            $map_wid2sooner_edt[$onewid] = $max_effective_end_dt; 
                        }
                    } else {
                        //Compute this the hard way because root has hours
                        $intervalsinfo4oneworkitem = $map_hit_root[$owner_projectid]['i'];
                        $local_intervals = $intervalsinfo4oneworkitem['intervals'];
                        $last_nonempty_local_idx = -999;    //This changes IF it gets set
                        $worked_prior_to_last_nonempty = 0;
                        $prev_local_offset = NULL;
                        
                        foreach($local_intervals as $local_offset=>$local_detail)
                        {
                            if($local_detail['reh']>0)
                            {
                                $last_nonempty_local_idx = $local_offset;
                                if($prev_local_offset !== NULL)
                                {
                                    $worked_prior_to_last_nonempty += $local_intervals[$prev_local_offset]['reh'];
                                }
                                $prev_local_offset = $local_offset;
                            }
                        }
                        
                        if($last_nonempty_local_idx < 0) //Do NOT check for NULL, check for never set like this instead!
                        {
                            //This should NEVER happen if there is reh for the workitem!
                            $title = "Corrupt interval settings for root workitem#$onewid";
                            DebugHelper::showNeatMarkup($local_intervals,$title,'error');
                            DebugHelper::showStackTrace($title, 'error');
                            throw new \Exception($title);
                        } 
                        
                        //Find the earliest day in this interval where we still need to work
                        $local_detail = $local_intervals[$last_nonempty_local_idx];

                        $isdt = $local_detail['sdt'];
                        $iedt = $local_detail['edt'];
                        if($iedt < $max_effective_end_dt)
                        {
                            //We can take the max date because we know it is safe
                            $map_wid2sooner_edt[$onewid] = $max_effective_end_dt;
                        } else {
                            //Compute a safe root end date in the interval
                            $ireh = $local_detail['reh'];
                            $itotal_worked_reh = 0;
                            if(!isset($intervaloffset2lasthijacked_date_map[$isdt]))
                            {
                                $sdt = $isdt;
                            } else {
                                $reference_dt = $intervaloffset2lasthijacked_date_map[$isdt];
                                $sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($reference_dt, 1);
                            }
                            foreach($daycountinfo['daily_detail'] as $onedate=>$daydetail)
                            {
                                if($onedate >= $sdt)
                                {
                                    $workthisday = .99 * $daydetail['workhoursinday'];  //Ignoring utilization???
                                    $itotal_worked_reh += $workthisday;
                                    if(($itotal_worked_reh >= $ireh + 1) || $onedate >= $iedt)
                                    {
                                        //Done!
                                        if($onedate < $root_existing_edt)
                                        {
                                            //We found a sooner end date
                                            if($max_effective_end_dt > $onedate)
                                            {
                                                //Cannot end before work in the project!
                                                $map_wid2sooner_edt[$onewid] = $max_effective_end_dt;
                                            } else {
                                                $map_wid2sooner_edt[$onewid] = $onedate;
                                            }
                                            //Mark the hijacking
                                            if($iedt > $onedate)
                                            {
                                                $intervaloffset2lasthijacked_date_map[$isdt] = $onedate;
                                            }
                                        }
                                        break;
                                    }
                                }
                            }
                           
                        }
                    }
                }
            }
            
            $map_sooner_dates = [];
            foreach($map_wid2sooner_edt as $wid=>$reference_dt)
            {
                //Add a 1 day buffer
                $sooner_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($reference_dt, 1);
                $map_sooner_dates[$wid]['sooner_edt'] = $sooner_dt;
            }
            $bundle = [];
            $bundle['map_projectid2max_effective_end_dt'] = $map_projectid2max_effective_end_dt;
            $bundle['map_sooner_dates'] = $map_sooner_dates;
            $bundle['cui'] = $cuiBundle;
            $bundle['daycountinfo'] = $daycountinfo;
            $bundle['debug:intervaloffset2lasthijacked_date_map'] = $intervaloffset2lasthijacked_date_map;
            $bundle['debug:hit_root'] = $map_hit_root;
            $bundle['debug:$debug_widmap'] = $debug_widmap;

            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function updateIntervalStatsForDateRange($apportion_start_dt,$apportion_end_dt,$edgedatepair,$sorted_date_pair_offset,&$all_baselines_by_wid,&$accumulator_buffer,&$intervals_by_workitem)
    {
        try
        {
            if(!is_array($all_baselines_by_wid))
            {
                throw new \Exception("Internal calling parameter error on all_baselines_by_wid!");
            }
            if(!is_array($accumulator_buffer))
            {
                throw new \Exception("Internal calling parameter error on accumulator_buffer!");
            }
            if(!is_array($intervals_by_workitem))
            {
                throw new \Exception("Internal calling parameter error on intervals_by_workitem!");
            }

            $edge_pair_raw_data_raw_data_offset = $edgedatepair['raw_data_offset'];
            $just_eprd_offsets = array_keys($edge_pair_raw_data_raw_data_offset);
            foreach($just_eprd_offsets as $edge_pair_raw_data_offset)
            {
                $data_ar = $this->m_raw_data[$edge_pair_raw_data_offset];
                if(!empty($data_ar['wid']))
                {
                    //This is a workitem data item
                    $wid = $data_ar['wid'];

                    $workitem_start_dt = $data_ar['actual_start_dt'] !== NULL ? $data_ar['actual_start_dt'] : $data_ar['planned_start_dt']; //effective is BROKEN here!!!! $data_ar['effective_start_dt'];
                    $workitem_end_dt = $data_ar['actual_end_dt'] !== NULL ? $data_ar['actual_end_dt'] : $data_ar['planned_end_dt']; //effective is BROKEN here!!!! $data_ar['effective_end_dt'];
                    $earliest_relevant_dt = UtilityGeneralFormulas::getNotEmptyMin($this->m_today_dt,$workitem_start_dt,$apportion_start_dt);
                    if(empty($workitem_start_dt))
                    {
                        //For counting purposes use this date
                        $workitem_start_dt = $earliest_relevant_dt;
                    }
                    if(empty($workitem_end_dt))
                    {
                        //For counting purposes use this date
                        $workitem_end_dt = $workitem_start_dt;
                    }
                    $daycount_detailinfo = $this->getPersonDayCountBundleBetweenDates($earliest_relevant_dt, $workitem_end_dt);
                    $ab = $this->m_oWAH->get_NON_INTERVAL_BALANCE_AWARE_WorkhoursApportionmentTotalBundle($data_ar, $daycount_detailinfo, $apportion_start_dt, $apportion_end_dt
                            , $workitem_start_dt, $workitem_end_dt
                            , $this->m_today_dt);
                    $wahc = ceil(10 * $ab['apportionment']['remaining_effort_hours']);
                    //$rounded_wid_apportioned_hours = round($wahc / 10, 1);

                    if(!isset($accumulator_buffer[$apportion_start_dt]))
                    {
                        $accumulator_buffer[$apportion_start_dt] = [];
                        $accumulator_buffer[$apportion_start_dt]['owner_projectid'] = [];
                        $accumulator_buffer[$apportion_start_dt]['wid'] = [];

                        $accumulator_buffer[$apportion_start_dt]['twh'] = 0;
                        $accumulator_buffer[$apportion_start_dt]['twd'] = 0;
                        $accumulator_buffer[$apportion_start_dt]['number'] = 0;
                        $accumulator_buffer[$apportion_start_dt]['factor'] = 0;
                        $accumulator_buffer[$apportion_start_dt]['number_history'] = [];
                    }
                    $apportionment_total_work_days_in_subset = $ab['apportionment']['total_work_days_in_subset'];
                    $apportionment_total_work_hours_in_subset = $ab['apportionment']['total_work_hours_in_subset'];
                    $apportionment_remaining_effort_hours = $ab['apportionment']['remaining_effort_hours'];
                    $apportionment_factor = $ab['apportionment']['factor'];
                    $accumulator_buffer[$apportion_start_dt]['twh'] = $apportionment_total_work_hours_in_subset;
                    $accumulator_buffer[$apportion_start_dt]['twd'] = $apportionment_total_work_days_in_subset;
                    $accumulator_buffer[$apportion_start_dt]['number'] += $apportionment_remaining_effort_hours;
                    $accumulator_buffer[$apportion_start_dt]['factor'] += $apportionment_factor;
                    $accumulator_buffer[$apportion_start_dt]['number_history'][] = array(
                            'sdt'=>$apportion_start_dt
                            ,'edt'=>$apportion_end_dt
                            ,'twd'=>$apportionment_total_work_days_in_subset
                            ,'twh'=>$apportionment_total_work_hours_in_subset
                            ,'reh'=>$apportionment_remaining_effort_hours
                            ,'af'=>$apportionment_factor);

                    if(!isset($all_baselines_by_wid[$wid]))
                    {
                        $all_baselines_by_wid[$wid] = array('sdt'=>$workitem_start_dt,'edt'=>$workitem_end_dt
                                ,'reh'=>$data_ar['remaining_effort_hours']
                                ,'af'=>$apportionment_factor);
                    }

                    if(!isset($intervals_by_workitem[$wid]))
                    {
                        //$remaining_effort_hours = $data_ar['remaining_effort_hours'];
                        $intervals_by_workitem[$wid] = [];

                        $intervals_by_workitem[$wid]['intervals'] = [];
                        $intervals_by_workitem[$wid]['lookup'] = [];
                        $intervals_by_workitem[$wid]['lookup']['intervals']['idx2local_offset'] = [];
                        $intervals_by_workitem[$wid]['debug_raw_data_offset'] = $edge_pair_raw_data_raw_data_offset;
                        $intervals_by_workitem[$wid]['debuginfo'] = [];
                    }
                    //$intervals_by_workitem[$wid]['debuginfo'][$debug_counter1] = $edge_pair_raw_data_offset;
                    $interval_local_offset = count($intervals_by_workitem[$wid]['intervals']);
                    $intervals_by_workitem[$wid]['intervals'][] 
                            = array(
                                 'idebuglog'=>array("created in getComputedNumberData $apportion_start_dt")
                                ,'idx'=>$sorted_date_pair_offset
                                ,'sdt'=>$apportion_start_dt
                                ,'edt'=>$apportion_end_dt
                                ,'twd'=>$apportionment_total_work_days_in_subset
                                ,'twh'=>$apportionment_total_work_hours_in_subset
                                ,'reh'=>$apportionment_remaining_effort_hours
                                ,'af'=>$apportionment_factor); //THIS IS THE APPORTIONMENT FACTOR THAT ALL PROGRAM MUST USE!!!!
                    $intervals_by_workitem[$wid]['lookup']['intervals']['idx2local_offset'][$sorted_date_pair_offset] = $interval_local_offset;
                    if(!isset($intervals_by_workitem[$wid]['lookup']['intervals']['by_sdt'][$apportion_start_dt]))
                    {
                        $intervals_by_workitem[$wid]['lookup']['intervals']['by_sdt'][$apportion_start_dt] = [];
                    }
                    $intervals_by_workitem[$wid]['lookup']['intervals']['by_sdt'][$apportion_start_dt][$wid] = $interval_local_offset;
                    if(!isset($intervals_by_workitem[$wid]['lookup']['intervals']['by_edt'][$apportion_end_dt]))
                    {
                        $intervals_by_workitem[$wid]['lookup']['intervals']['by_edt'][$apportion_end_dt] = [];
                    }
                    $intervals_by_workitem[$wid]['lookup']['intervals']['by_edt'][$apportion_end_dt][$wid] = $interval_local_offset;

                    $accumulator_buffer[$apportion_start_dt]['apportionment_details'] = $ab;
                    $accumulator_buffer[$apportion_start_dt]['apportionment_details']['wid'] = $wid;

                    if(isset($data_ar['wid']))
                    {
                        $accumulator_buffer[$apportion_start_dt]['wid'][$wid] = $wid;
                    }
                    if(isset($data_ar['owner_projectid']))
                    {
                        $owner_projectid = $data_ar['owner_projectid'];
                        $accumulator_buffer[$apportion_start_dt]['owner_projectid'][$owner_projectid] = $owner_projectid;
                    }
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a plottable dataset where the apportioned number is added from the 
     * date ranges that overlap.
     */
    public function getComputedNumberData($recompute=FALSE)//FALSE)//$number_keyname='remaining_effort_hours')//,$daycount_detailinfo=NULL)
    {
        try
        {
            $number_keyname='remaining_effort_hours';
            if($recompute)
            {
                $this->m_aComputedNumberData = NULL;
            }
            if($this->m_aComputedNumberData == NULL || $this->m_aComputedNumberKeyName != $number_keyname)
            {
                $sorted_date_pairs = $this->getAllSortedEdgeDatePairs();
                $this->m_aComputedNumberKeyName = $number_keyname;
                $this->m_aComputedNumberData = [];

                $all_baselines_by_wid = [];
                $all_other = [];
                $all_intervals_info = [];
                $all_intervals_info['sorted_date_pairs'] = $sorted_date_pairs;
                $all_intervals_info['by_wid'] = [];

                $min_dt = NULL;
                $max_dt = NULL;

                $accumulator_buffer = [];
                $intervals_by_workitem = [];
                $debug_counter1 = 0;
                $sorted_date_pair_offset = -1;
                foreach($sorted_date_pairs as $apportion_start_dt=>$edgedatepair)
                {
                    $debug_counter1++;
                    $sorted_date_pair_offset++;

                    $apportion_end_dt = $edgedatepair['end_dt'];
                    if($min_dt == NULL || $min_dt > $apportion_start_dt)
                    {
                        $min_dt=$apportion_start_dt;
                    }
                    if($max_dt == NULL || $max_dt < $apportion_end_dt)
                    {
                        $max_dt=$apportion_end_dt;
                    }

                    try
                    {
                        $this->updateIntervalStatsForDateRange($apportion_start_dt,$apportion_end_dt
                                ,$edgedatepair
                                ,$sorted_date_pair_offset
                                ,$all_baselines_by_wid
                                ,$accumulator_buffer
                                ,$intervals_by_workitem);
                    } catch (\Exception $ex) {
                        $errmsg = "Unable to accurately set interval stats for range [$apportion_start_dt,$apportion_end_dt]";
                        error_log($errmsg);
                        error_log("ERROR DETAIL>>>" . $ex);
                        //drupal_set_message($errmsg,'error');
                    }

                    $apportionment_total_work_days_in_subset = NULL;
                    $apportionment_total_work_hours_in_subset = NULL;
                    $reh_sum = 0;
                    $f_sum = 0;
                    //$debug_sum_history = [];
                    $wid_ar = [];
                    $owner_projectid_ar = [];
                    foreach($accumulator_buffer as $payload)
                    {
                        //$debug_sum_history[] = array($payload['number'],$payload);
                        if($apportionment_total_work_hours_in_subset === NULL)
                        {
                            //They are all the same because for the same person in same period!
                            $apportionment_total_work_days_in_subset = $payload['twd'];
                            $apportionment_total_work_hours_in_subset = $payload['twh'];
                        }
                        $reh_sum += $payload['number'];
                        $f_sum += $payload['factor'];
                        foreach($payload['wid'] as $wid)
                        {
                            $wid_ar[$wid] = $wid;
                        }
                        foreach($payload['owner_projectid'] as $owner_projectid)
                        {
                            $owner_projectid_ar[$owner_projectid] = $owner_projectid;
                        }
                    }

                    if(empty($apportion_start_dt))
                    {
                        throw new \Exception('Missing required $apportion_start_dt!');
                    }
                    if(empty($apportion_end_dt))
                    {
                        throw new \Exception('Missing required $apportion_end_dt!');
                    }

                    $dcb = $this->getPersonDayCountBundleBetweenDates($apportion_start_dt, $apportion_end_dt);

                    //Write the results to our final container
                    $all_other[] = array(
                             'start_dt'=>$apportion_start_dt
                            ,'end_dt'=>$apportion_end_dt
                            ,'daycount'=>$dcb
                            ,'wid_list'=>$wid_ar//array_keys($wid_ar)
                            ,'pid_list'=>$owner_projectid_ar// array_keys($owner_projectid_ar)
                        );
                    unset($accumulator_buffer[$apportion_start_dt]);
                } //Main INTERVAL LOOP

                $all_intervals_info['metadata']['min_dt'] = $min_dt;
                $all_intervals_info['metadata']['max_dt'] = $max_dt;
                $all_intervals_info['by_wid'] = $intervals_by_workitem;//[] = array($apportion_start_dt=>$intervals_by_workitem);
                $just_bwids = array_keys($all_baselines_by_wid);
                foreach($just_bwids as $bwid)
                {
                    $all_intervals_info['by_wid'][$bwid]['baseline'] = $all_baselines_by_wid[$bwid];
                }

                //Now move the reh around the intervals as needed to make the work fit
                $this->m_oWAH->setBestIntervalUtilizationApportionmentsOnePerson($all_intervals_info);

                //Store this result in our buffer
                if(!empty($min_dt) && (empty($this->m_min_dt) || $this->m_min_dt > $min_dt))
                {
                    $this->m_min_dt = $min_dt;
                }
                if(!empty($max_dt) && (empty($this->m_max_dt) || $this->m_max_dt > $max_dt))
                {
                    $this->m_max_dt = $max_dt;
                }
                $this->m_aComputedNumberData = array('all_intervals_info'=>$all_intervals_info);
            }
            return $this->m_aComputedNumberData;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Works with a local cache of bundles
     */
    public function getPersonDayCountBundleBetweenDates($start_dt=NULL, $end_dt=NULL)
    {
        if(empty($start_dt))
        {
            $start_dt = $this->m_min_dt;
            if(empty($start_dt))
            {
                //throw new \Exception("Missing required start_dt and found no min date!");
            }
        }
        
        if(empty($end_dt))
        {
            $end_dt = $this->m_max_dt;
            if(empty($end_dt))
            {
                //throw new \Exception("Missing required end_dt and found no max date!");
            }
        }
        
        if($start_dt > $end_dt)
        {
            DebugHelper::showStackTrace();
            throw new \Exception("The start_dt is greater than the end_dt! ($start_dt > $end_dt)");
        }
        if(empty($start_dt) || empty($end_dt))
        {
            //There is no data
            $dcb = [];
            $dcb['has_bundle_yn'] = 0;
            $dcb['source_info'] = "from_smartbucket_missing_dates  ([$start_dt] to [$end_dt])";
        } else {
            $key = "{$start_dt}_2_{$end_dt}";
            if(isset($this->m_daycountbundle_cache[$key]))
            {
                $dcb = $this->m_daycountbundle_cache[$key];
                if($dcb === NULL)
                {
                    throw new \Exception("ERROR 111 GOT NULL FOR getPersonDayCountBundleBetweenDates($start_dt, $end_dt)!!!!!!!!!!");
                }
                $dcb['has_bundle_yn'] = 1;
                $dcb['source_info'] = "from_smartbucket_cache ($start_dt to $end_dt)";
            } else {
                $include_daily_detail=TRUE;
                $dcb = UtilityGeneralFormulas::getDayCountBundleBetweenDates($start_dt, $end_dt, $this->m_personid, $include_daily_detail, $this->m_today_dt);    
                $this->m_daycountbundle_cache[$key] = $dcb;
                if($dcb === NULL)
                {
                    throw new \Exception("ERROR 222 GOT NULL FOR getPersonDayCountBundleBetweenDates($start_dt, $end_dt)!!!!!!!!!!");
                }
                $dcb['has_bundle_yn'] = 1;
                $dcb['source_info'] = "computed_in_smartbucket ($start_dt to $end_dt)";
            }
        }
        return $dcb;
    }
}
