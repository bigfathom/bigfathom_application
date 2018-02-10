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
 * Collection of some utilization handling helper methods
 *
 * @author Frank Font
 */
class UtilizationHelper
{
    
    public function getPersonDailyDetailBundleInsight($padb, $updated_workitems=NULL)
    {
        try
        {
            $diff_tracking_map = [];
            if($updated_workitems === NULL)
            {
                $updated_workitems = [];
                $diff_tracking_yn = 0;
            } else {
                $diff_tracking_yn = 1;
            }
            
            $busy_bad_days = 0;
            $busy_good_days = 0;
            $busy_partial_fill_days = 0;
            
            $total_worked_hours = 0;
            
            $first_dt = NULL;
            $last_dt = NULL;
            
            $daily_detail = $padb['hours_overlay'];
            $prev_dt = NULL;
            $wids_static_map = [];
            $wids_relevant_map = [];
            
            foreach($daily_detail as $onedate=>$detail)
            {
                if(empty($first_dt))
                {
                    $first_dt = $onedate;
                }
                $last_dt = $onedate;
                if($prev_dt >= $onedate)
                {
                    throw new \Exception("Out of sequence $prev_dt >= $onedate!");
                }
                $prev_dt = $onedate;
                
                $wids_bundle = $detail['wids'];
                $wids_static = $wids_bundle['static'];
                $wids_static_ar = array_keys($wids_static);
                foreach($wids_static_ar as $wid)
                {
                    $wids_static_map[$wid] = $wid;
                }
                $wids_relevant = $wids_bundle['relevant'];
                $wids_relevant_ar = array_keys($wids_relevant);
                foreach($wids_relevant_ar as $wid)
                {
                    $wids_relevant_map[$wid] = $wid;
                }
                
                $workhoursinday = $detail['workhoursinday'];
                $free = $detail['free'];
                $busy = $detail['busy'];
                
                if($busy > 0)
                {
                    $total_worked_hours += $busy;
                    if($busy > $workhoursinday)
                    {
                        $busy_bad_days++;
                    } else {
                        $busy_good_days++;
                        if($free > 0)
                        {
                            $busy_partial_fill_days++;
                        }
                    }
                }
            }
            
            $bundle = [];
            $bundle['daterange'] = array('first_dt'=>$first_dt, 'last_dt'=>$last_dt);
            $bundle['wids'] = array('static'=>$wids_static_map, 'relevant'=>$wids_relevant_map);
            $bundle['total_worked_hours'] = $total_worked_hours;
            $bundle['busy_bad_days'] = $busy_bad_days;
            $bundle['busy_good_days'] = $busy_good_days;
            $bundle['DEBUG_ALL_$daily_detail'] = $daily_detail;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getSaturdayAfterDate($reference_date)
    {
        try
        {
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $days = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($today_dt, $reference_date);
            $adder = 1 + ceil($days/7);
            return date("Y-m-d", strtotime("+{$adder} saturday"));
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getDateAfterAllExistingWork($owner_personid=NULL, $buffer_day_count=10)
    {
        try
        {
            if($buffer_day_count < 1)
            {
                throw new \Exception("Must have value greater than 0 for buffer!");
            }
            
            $sql = "SELECT max(planned_end_dt) as mp_edt, max(actual_end_dt) as ma_edt"
                    . " FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename;
            if(!empty($owner_personid))
            {
                $sql .= " WHERE owner_personid=$owner_personid";
            }
            
            $result = db_query($sql);
            $record = $result->fetchAssoc();
            
            $max_existing_dt = max($record['mp_edt'],$record['ma_edt']);
            if(empty($max_existing_dt))
            {
                //This will happen on a fresh install
                $now_timestamp = time();
                $max_existing_dt = gmdate("Y-m-d",$now_timestamp);
            }
            $new_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($max_existing_dt, $buffer_day_count);
            
            //throw new \Exception("LOOK date=$new_dt for max stuff thing $sql RESULT=" . print_r($record,TRUE));
            
            return $new_dt;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Checks the busy_map and the hours_overlay of the person bundles
     */
    public function checkAvailabilityDetailBundleHours($oMasterDailyDetail, $expected_wids=NULL, $not_expected_wids=NULL)
    {
        $mdd_iid = $oMasterDailyDetail->getThisInstanceID();
        error_log("LOOK about to run checkAvailabilityDetailBundleHours mdd_iid={$mdd_iid} (debug m_relevant_work_busy_mapper) for expected_wids=".implode(", ", $expected_wids));
        try
        {
            if(NULL === $expected_wids)
            {
                $expected_wids = [];
            }
            if(NULL === $not_expected_wids)
            {
                $not_expected_wids = [];
            }
            $categories = array('static','relevant');
            $all_wids_busy_map = [];
            $all_wids_hours_overlay = [];
            $personid_ar = $oMasterDailyDetail->getAllPersonIDs();
            $all_person2padb = [];
            foreach($personid_ar as $personid)
            {
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $padb = $oPAI->getAvailabilityDetailBundle(NULL,NULL,1);
                $all_person2padb[$personid] = $padb;
                $busy_map_total_hours = 0;
                $busy_map = $padb['busy_map'];
                $local_dt2busy2 = [];
                foreach($categories as $onecategory)
                {
                    $busy_total1 = 0;
                    $busy_total2 = 0;
                    $prev_dt = NULL;
                    $local_dt1busy1 = [];
                    $busymismatch_catches = [];
                    foreach($busy_map[$onecategory] as $dt_key=>$detail)
                    {
                        if($prev_dt >= $dt_key)
                        {
                            throw new \Exception("Dates for category=$onecategory are not in sequence [prev_dt:$prev_dt] >= [this_dt:$dt_key] in busy_map for person#$personid! " 
                                    . "<BR>detail=" . \bigfathom\DebugHelper::getNeatMarkup($detail,"DETAIL at $dt_key")
                                    . "<BR>busy_map=" . \bigfathom\DebugHelper::getNeatMarkup($busy_map,"BUSY MAP")
                                    );
                        }
                        $busy_total1 += $detail['busy'];
                        $local_busy1 = $detail['busy'];
                        if(!isset($local_dt1busy1[$dt_key]))
                        {
                            $local_dt1busy1[$dt_key] = $local_busy1;
                        } else {
                            $local_dt1busy1[$dt_key] += $local_busy1;
                        }
                        if(!isset($local_dt2busy2[$dt_key]))
                        {
                            $local_dt2busy2[$dt_key] = 0;
                        }
                        foreach($detail['wids'] as $onewid=>$hours)
                        {
                            if(!isset($all_wids_busy_map[$onewid]))
                            {
                                $all_wids_busy_map[$onewid] = 0;
                            }
                            $all_wids_busy_map[$onewid] += $hours;
                            $busy_total2 += $hours;
                            $local_dt2busy2[$dt_key] += $hours;
                        }
                        if($local_busy1 != $local_dt2busy2[$dt_key])
                        {
                            $busymismatch_catches[$dt_key] = "$local_busy1 vs {$local_dt2busy2[$dt_key]}";
                        }
                        $prev_dt = $dt_key;
                    }
                    $count_mismatch_catches = count($busymismatch_catches);
                    if($busy_total1 != $busy_total2 || $count_mismatch_catches>0)
                    {
                        throw new \Exception("The $onecategory wid level busy detail is different than the total busy value ($busy_total1 vs $busy_total2) and count_mismatch_catches=$count_mismatch_catches "
                                . "for busy_map[$onecategory] of person#$personid! " . print_r($busy_map,TRUE)
                                . "<BR><BR>CATCHES=".print_r($busymismatch_catches,TRUE)
                                . "<BR><BR>DETAIL local_dt1busy1=".print_r($local_dt1busy1,TRUE)
                                . "<BR><BR>DETAIL local_dt2busy2=".print_r($local_dt2busy2,TRUE)
                                . "<BR><BR>DETAIL padb=" . \bigfathom\DebugHelper::getNeatMarkup($padb,"PADB CONTENT"));
                    }
                    $busy_map_total_hours += $busy_total1;
                }

                $hours_overlay_total_hours = 0;
                $hours_overlay = $padb['hours_overlay'];
                foreach($hours_overlay as $dt_key=>$detail)
                {
                    $hours_overlay_total_hours += $detail['busy'];
                    $wids = $detail['wids'];
                    foreach($categories as $onecategory)
                    {
                        foreach($wids[$onecategory] as $onewid=>$hours)
                        {
                            if(!isset($all_wids_hours_overlay[$onewid]))
                            {
                                $all_wids_hours_overlay[$onewid] = 0;
                            }
                            $all_wids_hours_overlay[$onewid] += $hours;
                        }
                    }
                }
            }

            //Confirm the two sections have the same wid information
            if(count($all_wids_hours_overlay) != count($all_wids_busy_map))
            {
                throw new \Exception("Different WID content between busy_map and hours_overlay!"
                        . "<BR> all_wids_hours_overlay=".print_r($all_wids_hours_overlay,TRUE) 
                        . "<BR> all_wids_busy_map=".print_r($all_wids_busy_map,TRUE));
            }
            foreach($all_wids_hours_overlay as $onewid=>$hours)
            {
                if(!isset($all_wids_busy_map[$onewid]))
                {
                    throw new \Exception("Missing wid#$onewid in busy_map which we found in the hours_overlay!"
                            . "<BR> all_wids_hours_overlay=".print_r($all_wids_hours_overlay,TRUE) 
                            . "<BR> all_wids_busy_map=".print_r($all_wids_busy_map,TRUE));
                }
                if(abs($all_wids_busy_map[$onewid] - $hours) > .001)
                {
                    throw new \Exception("Different hours total at wid#$onewid ($hours hours) between busy_map and hours_overlay!"
                            . "<BR> all_wids_hours_overlay=".print_r($all_wids_hours_overlay,TRUE) 
                            . "<BR> all_wids_busy_map=".print_r($all_wids_busy_map,TRUE));
                }
            }
            
            //Now confirm we found all the expected wids
            foreach($expected_wids as $onewid)
            {
                if(!isset($all_wids_busy_map[$onewid]))
                {
                    throw new \Exception("Missing expected wid#$onewid in busy_maps!"
                            . "<BR>expected_wids=" . print_r($expected_wids,TRUE) 
                            . "<BR>we found=".print_r($all_wids_busy_map,TRUE)
                            . "<BR><BR>all_person2padb=".print_r($all_person2padb,TRUE));
                }
            }
            //And have none of the unexpected ones
            foreach($not_expected_wids as $onewid)
            {
                if(isset($all_wids_busy_map[$onewid]))
                {
                    throw new \Exception("Found UNEXPECTED wid#$onewid in busy_maps!"
                            . "<BR>not_expected_wids=" . print_r($not_expected_wids,TRUE) 
                            . "<BR>we found=".print_r($all_wids_busy_map,TRUE)
                            . "<BR><BR>all_person2padb=".print_r($all_person2padb,TRUE));
                }
            }
        } catch (\Exception $ex) {
        error_log("LOOK EXCEPTION IN checkAvailabilityDetailBundleHours (debug m_relevant_work_busy_mapper) $ex");
            throw $ex;
        }
    }
}
