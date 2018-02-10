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
 * Static functions to format rows of data for display in tables
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UtilityFormatUtilizationData
{
    public static $PCTUTIL_TOO_MUCH_WORK = 100;
    public static $PCTUTIL_VERY_GOOD = 95;
    public static $PCTUTIL_GOOD = 90;
    public static $PCTUTIL_OK = 80;
    public static $PCTUTIL_TOO_LITTLE_WORK = 50;
    public static $PCTUTIL_MUCH_TOO_LITTLE_WORK = 20;
    
    public static function getFormattedRowCells4PersonInterval($interval_smash)
    {
        try
        {
            $assessment_info = WorkApportionmentHelper::getAssessmentOfInterval($interval_smash);
            
            $rowcontent = [];
            
            $reh = $interval_smash['reh'];
            $twd = $interval_smash['twd'];
            $twh = $interval_smash['twh'];
            $af = $interval_smash['af'];
            $af_sortnum = round($af * 1000);
            $start_dt = $interval_smash['sdt'];
            $end_dt = $interval_smash['edt'];
            $totaldaycount = UtilityGeneralFormulas::getDayCountInDateRange($start_dt, $end_dt);
            
            if($assessment_info['is_gap'])
            {
                $hours_classname = "colorful-gapnotice";
                $rhours_tip = "Zero hours for this person!";
                $ahours_tip = "No work scheduled!";
                $assessment_class = $hours_classname;
            } else {
                if($assessment_info['is_ok'])
                {
                    $hours_classname = "";
                    $rhours_tip = "OK";
                    $ahours_tip = "OK";
                    if($af > .75)
                    {
                        $assessment_class = "colorful-okfit";  
                    } else {
                        if($af <= .25)
                        {
                            $assessment_class = "colorful-notice";
                        } else {
                            $assessment_class = "";
                        }
                    }
                } else {
                    $hours_classname = "colorful-warning";
                    $rhours_tip = "Too many hours for this person!";
                    $ahours_tip = "Too few hours available for the planned effort!";
                    $assessment_class = $hours_classname;
                }
            }

            $map_wids = $interval_smash['maps']['workitem'];
            $map_project = $interval_smash['maps']['project'];
            
            $assessment_tx = $assessment_info['busyword'];
            $assessment_sort = $af_sortnum;
            $assessment_markup = "[SORTNUM:$assessment_sort]<span class='$assessment_class'>$assessment_tx</span>";
            
            $upct_sortnum = $af_sortnum;
            $upct_tip = $ahours_tip;
            $upct_classname = $hours_classname;
            $rounded_upct = round(100 * $af,2);
            
            $upct_markup = "[SORTNUM:$upct_sortnum]<span title='$upct_tip' class='$upct_classname'>$rounded_upct</span>";            
            
            if($twd > .001)
            {
                $need_hoursperday = round($reh/$twd,2);
                $available_hoursperday = round($twh/$twd,2);
            } else {
                $need_hoursperday = "-NA-";
                $available_hoursperday = "-NA-";
            }
            $need_hoursperday_markup = "[SORTNUM:$need_hoursperday]<span title='$rhours_tip' class='$hours_classname'>$need_hoursperday</span>";
            $available_hoursperday_markup = "[SORTNUM:$available_hoursperday]<span title='$ahours_tip' class='$hours_classname'>$available_hoursperday</span>";

            $totaldaycount_markup = "[SORTNUM:$totaldaycount]$totaldaycount";
            $availabledaycount_markup = "[SORTNUM:$twd]$twd";
            
            asort($map_wids);
            asort($map_project);
            $wids_markup = implode(", ", $map_wids);
            $pids_markup = implode(", ", $map_project);
            
            $comment_markup = '';
            
            $reh_markup = round($reh,2);
            
            $rowcontent['assessment_tx'] = $assessment_markup;
            $rowcontent['start_dt'] = $start_dt;
            $rowcontent['end_dt'] = $end_dt;
            $rowcontent['totaldaycount'] = $totaldaycount_markup;
            $rowcontent['availabledaycount'] = $availabledaycount_markup;
            $rowcontent['remaining_effort_hours'] = $reh_markup;
            $rowcontent['available_hours'] = $twh;
            $rowcontent['need_hoursperday'] = $need_hoursperday_markup;
            $rowcontent['available_hoursperday'] = $available_hoursperday_markup;
            $rowcontent['upct'] = $upct_markup;
            $rowcontent['pids'] = $pids_markup;
            $rowcontent['wids'] = $wids_markup;
            $rowcontent['comment'] = $comment_markup;
            
            return $rowcontent;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Employs utilization SUM by the PERCENT ((remaining wid effort)/(available for wid period)) method.
     * Assumes all the utilization information is for the same person
     * Uses the utilization percent as provided in the intervals data structure
     * TODO SPLIT PLAIN FROM FORMAT!!!!
     */
    public static function getFormattedRowCellsBundle4WorkitemBoundedPeriod($wdetail, $utilization_records)
    {
        try
        {
            $bundle = [];
            $plain = [];
            $formatted = [];

            $today_dt = date("Y-m-d", time());
            $effective_start_dt = $wdetail['effective_start_dt'];   //SUSPECT FOR ROOT????
            $effective_end_dt = $wdetail['effective_end_dt'];
            $remaining_effort_hours = $wdetail['remaining_effort_hours'];
            
            $has_fake_date = FALSE;
            if(empty($effective_start_dt))
            {
                $effective_start_dt = $today_dt;
            }
            if(empty($effective_end_dt))
            {
                //Since we are giving utilization, assume same date as start so we have some way of showing effort!
                $effective_end_dt = $effective_start_dt;
                $has_fake_date = TRUE;
            }
            
            $wid = $wdetail['wid'];
            $projectid = $wdetail['owner_projectid'];
            $personid = $wdetail['owner_personid'];

            $work_daycount_bundle = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($effective_start_dt, $effective_end_dt, $personid);
            $workitem_total_daycount = $work_daycount_bundle['total'];
            
            //Compute utilization derived insights
            $cuiBundle = DateRangeSmartNumberBucket::getFlatUtilizationInfoForDateRange($effective_start_dt,$effective_end_dt,$utilization_records,NULL,$remaining_effort_hours);
            $is_ok = $cuiBundle['is_ok'];
            $upct_factors = $cuiBundle['upct_factors'];
            $overage_utildetail_ar = $cuiBundle['overage_utildetail_ar'];
            $sum_reh = $cuiBundle['total_reh'];
            $reh_overage = $cuiBundle['reh_overage'];
            $domain_twd = $cuiBundle['domain_twd'];
            $domain_twh = $cuiBundle['domain_twh'];
            $over_count = $cuiBundle['over_count'];
            $maxutilpct = $cuiBundle['maxutilpct'];
            $weightedutilpct_rounded = $cuiBundle['weightedutilpct_rounded'];
            $weighted_avg_upct_sort = $cuiBundle['weighted_avg_upct_sort'];

            $too_few_days = FALSE;
            $daycount_classname = "";
            $reh_classname = "";

            //Is this utilization for this period at least partially okay?
            if($is_ok)
            {
                if($over_count == 0)
                {
                    //Clearly okay
                    $is_ok_markup = "[SORTSTR:Y]<span class='colorful-yes'>Yes</span>";
                    $util_classname = "";
                } else {
                    //The average was okay but there are bad spots
                    $is_ok_markup = "[SORTSTR:N2]<span class='colorful-notice'>Partial</span>";
                    $util_classname = "colorful-notice";
                }
                if($maxutilpct == 0)
                {
                    $maxutilpct_rounded = 0;
                    $pctsort = 0;
                    if($reh_overage > 0)
                    {
                        $upct_tooltip = "Not available for work";
                    } else {
                        $upct_tooltip = "No utilization";
                    }
                } else {
                    $maxutilpct_rounded = round($maxutilpct * 100,2);
                    $pctsort = $maxutilpct_rounded * 100;
                    $upct_tooltip = "OK: No utilization overages detected";// ::: maxutilpct=$maxutilpct  upct_factors=" . print_r($upct_factors,TRUE);
                }
            } else {
                //Clearly not okay
                $is_ok_markup = "[SORTSTR:N1]<span class='colorful-no'>No</span>";
                $util_classname = "utilization-toomuch";
                if($maxutilpct == 0)
                {
                    $maxutilpct_rounded = 0;
                    $pctsort = 0;
                    if($reh_overage > 0)
                    {
                        $upct_tooltip = "Not available for work";
                    } else {
                        $upct_tooltip = "No utilization";
                    }
                } else {
                    $maxutilpct_rounded = round($maxutilpct * 100,2);
                    $pctsort = $maxutilpct_rounded * 100;
                    if($over_count > 0)
                    {
                        //We have overages
                        ksort($overage_utildetail_ar);
                        foreach($overage_utildetail_ar as $oneinfo)
                        {
                            $tooltips[] = $oneinfo['tooltip'];
                        }
                        $upct_tooltip = "Problem: Utilization overages detected in the work period! " . implode(" and ", $tooltips);
                    } else {
                        $clean_reh = round($remaining_effort_hours, 2);
                        $clean_sum_reh = round($sum_reh, 2);
                        if($clean_reh > $clean_sum_reh)
                        {
                            //This happens when there are not enough days
                            $too_few_days = TRUE;
                            $upct_tooltip = "Insufficient work days!";
                            $maxutilpct_rounded = 100;
                            $weightedutilpct_rounded = 100;
                            $pctsort = 999999;
                            $weighted_avg_upct_sort = 999999;
                            $daycount_classname = 'colorful-notice';                        
                            $reh_classname = 'colorful-notice';                        
                        } else {
                            $upct_tooltip = "Utilization issue detected";
                        }
                    }
                }
            }
            
            $total_workdaycount = $work_daycount_bundle['workday'];
            $available_work_hours = $work_daycount_bundle['available_work_hours'];
            
            if($too_few_days)
            {
                $daycount_tooltip = "$total_workdaycount workdays in this period: Need more!";
            } else {
                $daycount_tooltip = "$total_workdaycount workdays in this period";
            }
            
            $status_cd = $wdetail['status_cd'];
            $fullname = $wdetail['workowner']['fullname'];
            $person_markup = "<span title='$fullname'>$personid</span>";
            $wid_markup = $wid;
            $pid_markup = $projectid;
            
            $plain['wid'] = $wid;
            $plain['status_cd'] = $status_cd;
            $plain['daycount_bundle'] = $work_daycount_bundle;
            $plain['remaining_effort_hours'] = $remaining_effort_hours;
            $plain['is_ok'] = $is_ok;
            $plain['problem_periods_ar'] = $overage_utildetail_ar;
            $plain['upct_factors'] = $upct_factors;
            
            $mb = \bigfathom\MarkupHelper::getStatusCodeMarkupBundle($wdetail);
            $status_cd_markup = $mb['status_code'];
            
            //Now gather up all the markup
            $max_upct_markup = "[SORTNUM:$pctsort]<span class='$util_classname' title='$upct_tooltip'>$maxutilpct_rounded</span>";
            $weighted_avg_upct_markup = "[SORTNUM:$weighted_avg_upct_sort]<span class='$util_classname' title='$upct_tooltip'>$weightedutilpct_rounded</span>";
            $totaldaycount_markup = "[SORTNUM:$workitem_total_daycount]<span class='$daycount_classname' title='$daycount_tooltip'>$workitem_total_daycount</span>";
            $remaining_effort_hours_markup = "[SORTNUM:$remaining_effort_hours]<span class='$reh_classname' title='$available_work_hours available work hours in this period'>$remaining_effort_hours</span>";
            
            $formatted['is_ok_markup'] = $is_ok_markup;
            $formatted['project'] = $pid_markup;
            $formatted['workitem'] = $wid_markup;
            $formatted['person'] = $person_markup;
            $formatted['wstatus'] = $status_cd_markup;
            $formatted['start_dt'] = $effective_start_dt;
            $formatted['end_dt'] = $effective_end_dt;
            $formatted['totaldaycount'] = $totaldaycount_markup;
            $formatted['remaining_effort_hours'] = $remaining_effort_hours_markup;
            $formatted['max_upct'] = $max_upct_markup;
            $formatted['avg_upct'] = $weighted_avg_upct_markup;

            $bundle['formatted'] = $formatted;
            $bundle['plain'] = $plain;
            return $bundle;
        } catch (\Exception $ex)
        {
            throw $ex;
        }
    }
}
