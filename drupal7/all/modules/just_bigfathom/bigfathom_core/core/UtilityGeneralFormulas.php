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
 * This class provides utiliy formulas
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UtilityGeneralFormulas 
{
    
    public static function getClassname4OTSP($otsp_value)
    {
        if($otsp_value > 0.9)
        {
            $classname = "otsp-verygood";
        } else
        if($otsp_value > 0.8)
        {
            $classname = "otsp-good";
        } else
        if($otsp_value < 0.4)
        {
            $classname = "otsp-veryugly";
        } else
        if($otsp_value < 0.5)
        {
            $classname = "otsp-ugly";
        } else
        if($otsp_value < 0.7)
        {
            $classname = "otsp-bad";
        } else {
            $classname = "otsp-ambiguous";
        }
        
        return $classname;
        //return $otsp_value > 0.9 ? "otsp-good" : ($otsp_value  < 0.4 ? "otsp-veryugly" : ($otsp_value  < 0.5 ? "otsp-ugly" : ($otsp_value  < 0.70 ? "otsp-bad" : "otsp-ambiguous")));
    }
    
    public static function getAsKeyValuePairs($labels, $fields)
    {
        try
        {
            $kvp = [];
            for($i=0; $i<count($labels); $i++)
            {
                $label = $labels[$i];
                $value = $fields[$i];
                $kvp[$label] = $value;
            }
            return $kvp;
        } catch (\Exception $ex) {
            DebugHelper::showNeatMarkup(array('labels'=>$labels,'fields'=>$fields),"Failed getAsKeyValuePairs",'error');
            throw $ex;
        }
    }
    
    public static function getFirstNonEmptyValue($value1,$value2,$value3=NULL,$value4=NULL,$value5=NULL)
    {
        $firstfilled = NULL;
        if(!empty($value1))
        {
            $firstfilled = $value1;
        } else
        if(!empty($value2))
        {
            $firstfilled = $value2;
        } else
        if(!empty($value3))
        {
            $firstfilled = $value3;
        } else
        if(!empty($value4))
        {
            $firstfilled = $value4;
        } else
        if(!empty($value5))
        {
            $firstfilled = $value5;
        } 
        return $firstfilled;
    }
 
    public static function getFirstNonNullValue($value1,$value2,$value3=NULL,$value4=NULL,$value5=NULL)
    {
        $firstfilled = NULL;
        if(NULL !== $value1)
        {
            $firstfilled = $value1;
        } else
        if(NULL !== $value2)
        {
            $firstfilled = $value2;
        } else
        if(NULL !== $value3)
        {
            $firstfilled = $value3;
        } else
        if(NULL !== $value4)
        {
            $firstfilled = $value4;
        } else
        if(NULL !== $value5)
        {
            $firstfilled = $value5;
        } 
        return $firstfilled;
    }

    
    public static function getNotEmptyMin($value1,$value2=NULL,$value3=NULL,$value4=NULL)
    {
            $result = NULL;
            $allvalues = [];
            if(!empty($value1))
            {
                    $allvalues[] = $value1;
            }
            if(!empty($value2))
            {
                    $allvalues[] = $value2;
            }
            if(!empty($value3))
            {
                    $allvalues[] = $value3;
            }
            if(!empty($value4))
            {
                    $allvalues[] = $value4;
            }
            if(count($allvalues) > 0)
            {
                    $result = Min($allvalues);
            }
            return $result;
    }

    public static function getNotEmptyMax($value1,$value2=NULL,$value3=NULL,$value4=NULL)
    {
            $result = NULL;
            $allvalues = [];
            if(!empty($value1))
            {
                    $allvalues[] = $value1;
            }
            if(!empty($value2))
            {
                    $allvalues[] = $value2;
            }
            if(!empty($value3))
            {
                    $allvalues[] = $value3;
            }
            if(!empty($value4))
            {
                    $allvalues[] = $value4;
            }
            if(count($allvalues) > 0)
            {
                    $result = Max($allvalues);
            }
            return $result;
    }
     
    
    public static function getSQLFieldTextFromArray($fields_ar)
    {
        $terms = [];
        foreach($fields_ar as $k=>$v)
        {
            if(!empty($k))
            {
                $terms[] = "$k as $v";
            } else {
                $terms[] = $v;
            }
        }
        return implode(",", $terms);
    }

    public static function getRecordSubsetFromArray($superset, $fields_ar)
    {
        $subset = [];
        foreach($fields_ar as $k=>$v)
        {
            $subset[$v] = $superset[$v];
        }
        return $subset;
    }
    
    /**
     * Round to significant digits
     */
    public static function getRoundSigDigs($number, $sigdigs=3) {
        $txt = "" . $number;
        $decimalpos = strpos($txt,".");
        if($decimalpos < 0)
        {
            //TODO -- round whole numbers
            return $txt;
        }
        $char = 'x';
        for($lastzeropos=$decimalpos+1; $lastzeropos < strlen($txt); $lastzeropos++)
        {
            $char = substr($txt,$lastzeropos,1);
            if($char != '0')
            {
                break;
            }
        }
        //TODO - Round instead of truncate!!
        $smallertxt = substr($txt, 0, $lastzeropos+$sigdigs);
        return $smallertxt;
        
        /*
        //TODO fix this because TOO SLOW!!!
        $multiplier = 1;
        while ($number < 0.1) 
        {
            $number *= 10;
            $multiplier /= 10;
        }
        while ($number >= 1) 
        {
            $number /= 10;
            $multiplier *= 10;
        }
        return round($number, $sigdigs) * $multiplier;
         */
    } 

    /**
     * Ignores the time of day and returns new date as a timestamp
     * The shift days are added from the reference date.
     */
    public static function getDayShiftedDateAsTimestamp($reference_dt, $shift_days)
    {
        try
        {
            if(empty($reference_dt))
            {
                return NULL;
            }
            $oReference_dt = new \DateTime($reference_dt);
            $clean_reference_dt = $oReference_dt->format('Y-m-d');
            $new_date = strtotime($clean_reference_dt) + (60*60*24)*$shift_days;
            return floor($new_date);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Ignores the time of day and returns new date as YYYY-MM-DD
     * The shift days are added from the reference date.
     */
    public static function getDayShiftedDateAsISO8601DateText($reference_dt, $shift_days)
    {
        try
        {
            $new_date = self::getDayShiftedDateAsTimestamp($reference_dt, $shift_days);
            $YMD = gmdate("Y-m-d", $new_date);
            return $YMD;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return an array showing the date gaps
     */
    public static function getGapDateRanges($all_active_ranges, $through_future_days_count=30)
    {
        try
        {
            $gap_ranges = [];
            if($through_future_days_count > 0)
            {
                ksort($all_active_ranges);
                $next_day_dt = NULL;
                $active_end_dt = NULL;
                foreach($all_active_ranges as $active_start_dt=>$info)
                {
                    if($next_day_dt !== NULL)
                    {
                        if($next_day_dt < $active_start_dt)
                        {
                            $gap_end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($active_start_dt, -1);
                            $gap_ranges[$next_day_dt] = array('start_dt'=>$next_day_dt,'end_dt'=>$gap_end_dt);
                        }
                    }
                    $active_end_dt = $info['end_dt'];

                    $next_day_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($active_end_dt, 1);
                }

                if(!empty($through_future_days_count))
                {
                    //Treat anything left until the future date as a gap
                    $now_timestamp = time();
                    $now_dt = gmdate("Y-m-d",$now_timestamp);
                    $last_day_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($now_dt, $through_future_days_count);
                    if(empty($active_end_dt))
                    {
                        $last_start_dt = $now_dt;
                    } else {
                        $last_start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($active_end_dt, 1);
                    }
                    if(empty($active_end_dt) || $last_day_dt > $active_end_dt)
                    {
                        $gap_ranges[$last_start_dt] = array('start_dt'=>$last_start_dt,'end_dt'=>$last_day_dt);
                    }
                }
            }
            return $gap_ranges;
        } catch (\Exception $ex) {
            throw $ex;
        }                    
    }
    
    public static function getItemActionRequestUrgencyScoreBundle($item_importance, $high_arq_stats, $med_arq_stats, $low_arq_stats)
    {
        try
        {
            $score = 0;
            $reason = [];
            
            if($item_importance > 50)
            {
                $add = $item_importance / 5;
                $score += $add;
                $reason[] = "Add $add because important rating of $item_importance";
            } else {
                $add = $item_importance / 10;
                $score += $add;
                $reason[] = "Add $add because not important rating of $item_importance";
            }
            
            if(!empty($high_arq_stats['count']))
            {
                $count = $high_arq_stats['count'];
                $add = $count * 100;
                $score += $add;
                $reason[] = "Add $add because $count high concern";
            }
            if(!empty($high_arq_stats['oldest_days']))
            {
                $days = $high_arq_stats['oldest_days'];
                $add = $days;
                $score += $add;
                $reason[] = "Add $add because $days days age high concern";
            }
            
            if(!empty($med_arq_stats['count']))
            {
                $count = $med_arq_stats['count'];
                $add = $count * 10;
                $score += $add;
                $reason[] = "Add $add because $count medium concern";
            }
            
            if(!empty($low_arq_stats['count']))
            {
                $count = $low_arq_stats['count'];
                $add = $count;
                $score += $add;
                $reason[] = "Add $add because $count low concern";
            }
            
            $bundle = [];
            $bundle['score'] = $score;
            $bundle['reason'] = $reason;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the baseline availability for one person or in general
     */
    public static function getBaselineAvailabilityRecord($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $from = DatabaseNamesHelper::$m_baseline_availability_tablename . " da";
                $filter = "da.is_planning_default_yn=1";
            } else {
                $from = DatabaseNamesHelper::$m_baseline_availability_tablename 
                        . " da left join bigfathom_person p on p.id=$personid";
                $filter = "da.id=p.baseline_availabilityid";
            }
            $sSQL = "select * "
                    . " from $from "
                    . " where $filter";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            if(empty($record))
            {
                throw new \Exception("No result for personid=[$personid] where sql=$sSQL");
            }
            $record['start_dt'] = "1980-01-01";
            $record['end_dt'] = "2999-01-01";
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a map of holiday dates
     */
    public static function getHolidays($reference_dt, $direction=1)
    {
        try
        {
            if(empty($reference_dt))
            {
                throw new \Exception("Missing reference date!");
            }
            $sSQL1 = "select holiday_dt "
                    . " from bigfathom_holiday "
                    . " where apply_to_all_users_yn=1 ";
            if ($direction == 1)
            {
                $sSQL1 .= "   and holiday_dt >= '$reference_dt' "
                        . " order by holiday_dt ASC";
            }
            else if($direction == -1)
            {
                $sSQL1 .= "   and holiday_dt <= '$reference_dt' "
                        . " order by holiday_dt DESC";
            } else {
                throw new \Exception("Missing direction=[$direction] is not valid!");
            }
            $result = db_query($sSQL1);
            $holidays = [];
            while ($record = $result->fetchAssoc())
            {
                $hts = strtotime($record['holiday_dt']);
                $holidays[$hts] = $record['holiday_dt'];
            }
            return $holidays;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return records describing the availability of a person
     */
    public static function getPersonAvailabilityBundle($personid, $reference_dt, $direction=1)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing personid!");
            }
            if(empty($reference_dt))
            {
                throw new \Exception("Missing reference date!");
            }
            $bundle = [];
            $baseline_availability_periods = [];
            $override_availability_periods = [];

            //Get all the custom availabilities for this person
            $sSQL2 = "select start_dt, end_dt, type_cd, hours_per_day"
                            . " , work_saturday_yn, work_sunday_yn"
                            . " , work_monday_yn, work_tuesday_yn, work_wednesday_yn, work_thursday_yn, work_friday_yn"
                            . " , work_holidays_yn "
                            . " from " . DatabaseNamesHelper::$m_map_person2availability_tablename;
            if ($direction == 1)
            {
                    //Compare to end date
                    $sSQL2 .= " where personid=$personid and ("
                                    . "     ( end_dt >= '$reference_dt' )"
                                    . "   )"
                                    . " order by start_dt ASC, type_cd";
            }
            else
            {
                    //Compare to start date
                    $sSQL2 .= " where personid=$personid and ("
                                    . "     ( start_dt <= '$reference_dt' )"
                                    . "   )"
                                    . " order by start_dt DESC, type_cd";
            }
            $result = db_query($sSQL2);
            while ($record = $result->fetchAssoc())
            {
                    $type_cd = $record['type_cd'];
                    $start_as_YMD = $record['start_dt'];
                    if ($type_cd == 'B')
                    {
                            $baseline_availability_periods[$start_as_YMD] = $record;
                    }
                    else
                    {
                            $override_availability_periods[$start_as_YMD] = $record;
                    }
            }
            $bundle['B'] = $baseline_availability_periods;		
            $bundle['O'] = $override_availability_periods;		
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public static function getAllAvailabilityTypeCodeInfo()
    {
        $all = [];
        $codes = array('B','C','V','T','P','D','O');
        foreach($codes as $type_cd)
        {
            $all[$type_cd] = self::getInfoForAvailabilityTypeCode($type_cd);
        }
        return $all;
    }
    
    public static function getInfoForAvailabilityTypeCode($type_cd)
    {
        switch($type_cd)
        {
            case 'B':
                $typename = 'Personal Baseline';
                $sort = 1;
                $tooltip = 'A personal default availability';
                break;
            case 'C':
                $typename = 'Crunch';
                $sort = 2;
                $tooltip = 'A period of extra work availability';
                break;
            case 'V':
                $typename = 'Vacation';
                $sort = 3;
                $tooltip = 'A period of time away from work';
                break;
            case 'T':
                $typename = 'Travel';
                $sort = 4;
                $tooltip = 'Interrupted schedule due to travel';
                break;
            case 'P':
                $typename = 'Part-time';
                $sort = 5;
                $tooltip = 'Less than full-time hours';
                break;
            case 'D':
                $typename = 'Disability';
                $sort = 6;
                $tooltip = 'Interrupted or altered schedule for rehabilitation';
                break;
            case 'O':
                $typename = 'Other';
                $sort = 9;
                $tooltip = 'Explained in the notes';
                break;
            default:
                throw new \Exception("Unregonized availability type code [$type_cd]!");
        }
        $result = array('name' => $typename, 'sort'=>$sort, 'tooltip'=>$tooltip);
        return $result;
    }
    
    /**
     * Return TRUE or FALSE depending on content of array key value pair comparison
     */
    public static function getArrayMemberHasTextMatch($flags_ar,$keyname,$textmatch,$default_value)
    {
        if(!array_key_exists($keyname, $flags_ar))
        {
            $value = $default_value;
        } else {
            $value = ($flags_ar[$keyname] == $textmatch);
        }
        return $value;
    }
    
    /**
     * Return TRUE or FALSE depending on content of array key value pair comparison
     */
    public static function getArrayMemberHasBooleanMatch($flags_ar,$keyname,$boolean_match,$default_value)
    {
        if(!array_key_exists($keyname, $flags_ar))
        {
            $value = $default_value;
        } else {
            $value = ($flags_ar[$keyname] == $boolean_match);
        }
        return $value;
    }
    
    /**
     * Return the number of workdays between two dates provided as timestamps
     */
    public static function getDayCountBundleBetweenSerializedDates($start_timestamp, $end_timestamp, $personid=NULL)
    {
        try
        {
            if(empty($start_timestamp) || empty($end_timestamp))
            {
                return NULL;
            }             
            if(!is_numeric($start_timestamp) || !is_numeric($end_timestamp))
            {
                throw new \Exception("Expected start and end timestamps instead got [$start_timestamp, $end_timestamp]!");
            }
            
            $start_dt = gmdate("Y-m-d", $start_timestamp);
            $end_dt = gmdate("Y-m-d", $end_timestamp);
            if($start_dt>$end_dt)
            {
                throw new \Exception("startdate ($start_dt timestamp=$start_timestamp) is after enddate ($end_dt timestamp=$end_timestamp)!");
            }
            return self::getDayCountBundleBetweenDates($start_dt, $end_dt, $personid);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return the number of workdays bundle between two dates
     */
    public static function getDayCountBundleBetweenDatesFromExistingDetail($existing_daycount_detail, $start_dt, $end_dt, $include_daily_detail=FALSE, $today_dt=NULL)
    {
        try
        {
            if(empty($start_dt))
            {
                throw new \Exception("Missing start date!");
            }
            
            if(empty($end_dt))
            {
                throw new \Exception("Missing end date!");
            }
            
            if (empty($today_dt))
            {
                $now_timestamp = time();
                $today_dt = gmdate("Y-m-d", $now_timestamp);
            }
            
            $holiday_count=0;
            $weekend_count=0;
            $dayoff_count=0;
            $weekday_count=0;
            $allday_count=0;
            $working_days_count=0;
        
            $workhours = 0;
            
            $from_today_working_days=0;
            $from_today_workhours=0;
            
            $segment_custom_availability_type_codes = [];
            $segment_custom_availability_types = [];
            
            $bundle['first_dt'] = $start_dt;
            $bundle['last_dt'] = $end_dt;
            $bundle['next_dt'] = self::getDayShiftedDateAsISO8601DateText($end_dt, 1);
            
            $superset_daily_detail = $existing_daycount_detail['daily_detail'];
            $subset_daily_detail = [];
            $loop_counter=0;
            foreach($superset_daily_detail as $one_dt=>$one_detail)
            {
                $loop_counter++;
                if($one_dt >= $start_dt)
                {
                    if($one_dt > $end_dt)
                    {
                        //Done!
                        break;
                    }
                    $allday_count++;
                    if($include_daily_detail)
                    {
                        $subset_daily_detail[$one_dt] = $one_detail;
                    }
                    
                    $isholiday = $one_detail['isholiday'];
                    $isweekday = $one_detail['isweekday'];
                    $isweekend = $one_detail['isweekend'];
                    $isdayoff = $one_detail['isdayoff'];
                    $isworkday = $one_detail['isworkday'];
                    
                    $workhoursinday = $one_detail['workhoursinday'];
                    $avail_type_cd = $one_detail['avail_type_cd'];
                    $today_custom_availability_types = $one_detail['availability']['custom_availability_types'];

                    if($isholiday)
                    {
                        $holiday_count++;
                    }
                    if($isweekday)
                    {
                        $weekday_count++;
                    }
                    if($isweekend)
                    {
                        $weekend_count++;
                    }
                    if($isdayoff)
                    {
                        $dayoff_count++;
                    }
                    if($isworkday)
                    {
                        $working_days_count++;
                    }
                    
                    $workhours += $workhoursinday;
                    $segment_custom_availability_type_codes[$avail_type_cd] = $avail_type_cd;
                    foreach($today_custom_availability_types as $cat)
                    {
                        $segment_custom_availability_types[$cat] = $cat;
                    }
                    
                    if($one_dt >= $today_dt)
                    {
                        $from_today_working_days++;
                        $from_today_workhours += $workhoursinday;
                    }
                }
            }
            
            $bundle['custom_availability_types'] = $segment_custom_availability_types;
            $bundle['custom_availability_type_codes'] = $segment_custom_availability_type_codes;
    //available_work_hours        
            $bundle['holiday'] = $holiday_count;
            $bundle['weekendday'] = $weekend_count;
            $bundle['dayoff'] = $dayoff_count;
            $bundle['weekday'] = $weekday_count;
            
            $bundle['workday'] = $working_days_count;
            $bundle['total'] = $allday_count;
            
            $bundle['work_days'] = $working_days_count;
            $bundle['work_hours'] = $workhours;
            
            $bundle['from_today']['today_dt'] = $today_dt;
            $bundle['from_today']['work_hours'] = $from_today_workhours;
            $bundle['from_today']['work_days'] = $from_today_working_days;
            
            if($include_daily_detail)
            {
                $bundle['daily_detail'] = $subset_daily_detail;
            }
            $bundle['loop_counter'] = $loop_counter;
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return the number of workdays between two dates
     */
    public static function getDayCountBundleBetweenDates($start_dt, $end_dt, $personid=NULL, $include_daily_detail=TRUE, $today_dt=NULL)
    {
        try
        {
            $seeking_effort_hours=NULL;
            $ignore_seeking = TRUE;
            $direction = 1;
            $min_hours_to_start_work = NULL;
            $maxdays = NULL;
            $existing_utilization_bundle = NULL;
            
            if(empty($start_dt))
            {
                throw new \Exception("Missing required start date!");
            }
            
            if(empty($end_dt))
            {
                throw new \Exception("Missing required end date!");
            }
            
            $bundle = self::getWorkEffortComputedDateBundle(
                    $start_dt, $end_dt
                    , $personid
                    , $include_daily_detail
                    , $seeking_effort_hours, $ignore_seeking
                    , $direction
                    , $min_hours_to_start_work
                    , $maxdays
                    , $today_dt
                    , $existing_utilization_bundle
                    );
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return the simple calendar number of days between two dates
     * and include the start date AND the end date in the count
     */
    public static function getDayCountInDateRange($start_dt, $end_dt)
    {
        try
        {
            if(empty($start_dt) || empty($end_dt))
            {
                return NULL;
            }
            return self::getSimpleDayCountBetweenDates($start_dt, $end_dt) + 1;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return the simple calendar number of days between two dates
     * Does NOT count the last date.
     * WARNING: DOES NOT FACTOR IN DAYLIGHT SAVINGS!!!!
     */
    public static function getSimpleDayCountBetweenDates($start_dt, $end_dt)
    {
        try
        {
            if(empty($start_dt) || empty($end_dt))
            {
                return NULL;
            }
            $startdt = new \DateTime($start_dt);
            $clean_start_dt = $startdt->format('Y-m-d');
            $enddt = new \DateTime($end_dt);
            $clean_end_dt = $enddt->format('Y-m-d');
            $raw_diff = strtotime($clean_end_dt) - strtotime($clean_start_dt);
            return floor($raw_diff/(60*60*24));
        } catch (\Exception $ex) {
            throw new \Exception("Failed getSimpleDayCountBetweenDates('$start_dt', '$end_dt')",99876,$ex);
        }
    }

    /**
     * Ignores the time of day
     */
    public static function getSimpleDayCountBetweenSerializedDates($start_timestamp, $end_timestamp)
    {
        try
        {
            if(empty($start_timestamp) || empty($end_timestamp))
            {
                return NULL;
            }
            $clean_start_dt = date('Y-m-d', $start_timestamp);
            $clean_end_dt = date('Y-m-d', $end_timestamp);
            $raw_diff = strtotime($clean_end_dt) - strtotime($clean_start_dt);
            return floor($raw_diff/(60*60*24));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getInsightLevelFromRawValue($rawvalue)
    {
        if($rawvalue > 75)
        {
            return 4;   //Most insight
        } else
        if($rawvalue > 50)
        {
            return 3;
        } else
        if($rawvalue > 25)
        {
            return 2;
        } else
        if($rawvalue > 0)
        {
            return 1;
        }
        return 0;   //No insight
    }
    
    public static function getInfluenceLevelFromRawValue($rawvalue)
    {
        if($rawvalue > 75)
        {
            return 4;   //Most influence
        } else
        if($rawvalue > 50)
        {
            return 3;
        } else
        if($rawvalue > 25)
        {
            return 2;
        } else
        if($rawvalue > 0)
        {
            return 1;
        }
        return 0;   //No influence
    }

    public static function getImportanceLevelFromRawValue($rawvalue)
    {
        if($rawvalue > 75)
        {
            return 4;   //Most importance
        } else
        if($rawvalue > 50)
        {
            return 3;
        } else
        if($rawvalue > 25)
        {
            return 2;
        } else
        if($rawvalue > 0)
        {
            return 1;
        }
        return 0;   //No importance
    }
    
    /**
     * Infer the time management matrix quadrant number from raw inputs
     * TODO REFACTOR THE LOGIC!!!!!!!!!!!!!!!!!!
     * deprecated????????????
     */
    public static function getTimeManagementMatrixQuadrantFromRawValues($importance_value
            , $days_until_deadline=NULL
            , $days_urgent_threshhold=7
            , $success_forecast=.5
            , $forecast_urgent_threshhold=.4)
    {
        if($days_until_deadline === NULL)
        {
            //Make all our inferences from the importance value
            if($importance_value > 75)
            {
                $q = 1;   //Most importance
            } else
            if($importance_value > 50)
            {
                $q = 2;   //Ideal importance
            } else
            if($importance_value > 25)
            {
                $q = 3;
            } else {
                $q = 4;  //not important and not urgent
            }
        } else {
            $urgent = $days_until_deadline < $days_urgent_threshhold 
                    || $success_forecast < $forecast_urgent_threshhold
                    || ($success_forecast < .50 && $days_until_deadline < 2*$days_urgent_threshhold)
                    || ($success_forecast < .45 && $days_until_deadline < 3*$days_urgent_threshhold);
            $important = $importance_value > 50;
            if($urgent)
            {
                if($important)
                {
                    $q = 1;
                } else {
                    $q = 2;
                }
            } else {
                if($important)
                {
                    $q = 3;
                } else {
                    $q = 4;
                }
            }
        }
        return $q;
    }
    
    public static function getKeywordForConcernLevel($levelnum)
    {
        if($levelnum >= 30)
        {
            return 'High';
        } else
        if($levelnum >= 20)
        {
            return 'Medium';
        } else
        if($levelnum >= 10)
        {
            return 'Low';
        }
        return 'None';
    }
    
    public static function getAllUIPageBundles()
    {
        $names = array(
            'dashboard','projects','sitemanage','reports','youraccount','help'
        );
        $bundles = [];
        foreach($names as $onename)
        {
            $bundles[] = self::getUIContextBundle($onename);
        }
        return $bundles;
    }
    

    public static function getUIContextBundle($raw_contextname)
    {
        $bundle = [];
        $clean_contextname = strtolower($raw_contextname);
        switch($clean_contextname)
        {
            case 'dashboard':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Dashboard";
                $bundle['font_awesome_class'] = "fa fa-line-chart";
                $bundle['description'] = 'Get overview insight into various aspects of active projects';
                break;        
            case 'projects':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Project Work";
                $bundle['font_awesome_class'] = "fa fa-rocket";
                $bundle['description'] = 'Project specific interaction activities';
                break;        
            case 'sitemanage':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Settings";
                $bundle['font_awesome_class'] = "fa fa-cog";
                $bundle['description'] = 'Site and cross-project administration settings';
                break;
            case 'reports':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Reports";
                $bundle['font_awesome_class'] = "fa fa-map-o";
                $bundle['description'] = 'Available reports';
                break;        
            case 'youraccount':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Your Account";
                $bundle['font_awesome_class'] = "fa fa-user-circle-o";
                $bundle['description'] = 'Information about your user account in the application';
                break;        
            case 'help':
                $bundle['menu_key'] = "bigfathom/topinfo/$clean_contextname";
                $bundle['label'] = "Help";
                $bundle['font_awesome_class'] = "fa fa-book";
                $bundle['description'] = 'Helpful information about using the application';
                break;        
            default:
                throw new \Exception("No UI context bundle for '$clean_contextname'");
        }   
        return $bundle;
    }

    public static function getUIPageBundle($raw_pagename)
    {
        $bundle = [];
        $clean_contextname = strtolower($raw_pagename);
        switch($clean_contextname)
        {
            case 'visual_brainstorm':
                $bundle['menu_key'] = "bigfathom/projects/design/brainstormcapture";
                $bundle['label'] = "Topic Proposals";
                $bundle['font_awesome_class'] = "fa fa-cart-plus";
                $bundle['description'] = 'Visual collaboration on potential topics';
                break;        
            case 'tabular_brainstorm':
                $bundle['menu_key'] = "bigfathom/projects/brainstormitems";
                $bundle['label'] = "Topics Console";
                $bundle['font_awesome_class'] = "fa fa-cart-arrow-down";
                $bundle['description'] = 'Tabular display of potential topics';
                break;        
            case 'visual_dependencies':
                $bundle['menu_key'] = "bigfathom/projects/design/mapprojectcontent";
                $bundle['label'] = "Workitem Dependencies";
                $bundle['font_awesome_class'] = "fa fa-bullseye";
                $bundle['description'] = 'Visual representation of dependencies in the project';
                break;        
            default:
                throw new \Exception("No UI page bundle for '$clean_contextname'");
        }   
        return $bundle;
    }

    public static function getIconURLForWorkitemTypeCode($typecode, $small=FALSE, $dim=FALSE, $is_antecedent=FALSE)
    {
        switch($typecode)
        {
            case 'P':
                if($is_antecedent)
                {
                    $purpose_name = 'ant_project';
                } else {
                    $purpose_name = 'project';
                }
                break;
            case 'G':
                $purpose_name = 'goal';
                break;        
            case 'T':
                $purpose_name = 'task';
                break;        
            case 'X':
                $purpose_name = 'vendor';
                break;        
            case 'Q':
                $purpose_name = 'equipment';
                break;        
            default:
                throw new \Exception("No icon for purpose typecode '$typecode'");
        }   
        return self::getIconURLForPurposeName($purpose_name, $small, $dim);
    }

    /**
     * Get a dim icon URL from a purpose name
     */
    public static function getInactiveIconURLForPurposeName($purpose_name, $small=FALSE)
    {
        return self::getIconURLForPurposeName($purpose_name, $small, TRUE);
    }
    
    /**
     * Get a dim icon URL from a purpose name
     */
    public static function getActiveIconURLForPurposeName($purpose_name, $small=FALSE)
    {
        return self::getIconURLForPurposeName($purpose_name, $small, FALSE);
    }
    
    /**
     * Get an icon URL from a purpose name
     */
    public static function getIconURLForPurposeName($purpose_name, $small=FALSE, $dim=FALSE)
    {
        //TODO -- make the filenames configurable
        switch($purpose_name)
        {
            case 'communicate':
            case 'communicate0':
                $file_name = 'icon_replyballoons';
                break;
            case 'communicate1':
                $file_name = 'icon_boxed_replyballoons';
                break;
            case 'communicate_empty':
                $file_name = 'icon_replyballoons_empty';
                break;
            case 'communicate_hascontent':
                $file_name = 'icon_replyballoons_hascontent';
                break;
            case 'communicate_action_high':
                $file_name = 'icon_replyballoons_action_high';
                break;
            case 'communicate_action_medium':
                $file_name = 'icon_replyballoons_action_medium';
                break;
            case 'communicate_action_low':
                $file_name = 'icon_replyballoons_action_low';
                break;
            case 'communicate_action_closed':
                $file_name = 'icon_replyballoons_action_closed';
                break;
            case 'test_notready':
                $file_name = 'icon_test_notready';
                break;
            case 'test_ready':
                $file_name = 'icon_test_ready';
                break;
            case 'test_passed':
                $file_name = 'icon_test_passed';
                break;
            case 'test_failed':
                $file_name = 'icon_test_failed';
                break;
            case 'dashboard':
                $file_name = 'icon_airplane';
                break;
            case 'no_dashboard':
                $file_name = 'icon_no_airplane';
                break;
            case 'view':
                $file_name = 'icon_eye';
                break;
            case 'cell_edit':
                $file_name = 'icon_cell_pencil';
                break;
            case 'edit':
                $file_name = 'icon_pencil';
                break;
            case 'no_edit':
                $file_name = 'icon_no_pencil';
                break;
            case 'delete':
                $file_name = 'icon_redx';
                break;
            case 'hierarchy':
                $file_name = 'icon_dependencies';
                break;
            case 'reply':
                $file_name = 'icon_replyballoons';
                break;
            case 'reports':
                $file_name = 'icon_csv';
                break;
            case 'goal':
                $file_name = 'icon_goal';
                break;
            case 'task':
                $file_name = 'icon_task';
                break;
            case 'project':
                $file_name = 'icon_proj';
                break;
            case 'ant_project':
                $file_name = 'icon_ant_proj';
                break;
            case 'group':
                $file_name = 'icon_people';
                break;
            case 'projectroles':
                $file_name = 'icon_projectroles';
                break;
            case 'systemroles':
                $file_name = 'icon_systemroles';
                break;
            case 'forecast':
                $file_name = 'icon_forecast';
                break;
            case 'megaphone':
                $file_name = 'icon_megaphone';
                break;
            case 'sprint_visual':
                if($dim)
                {
                    $file_name = 'icon_goals_in_cup_dim';
                } else {
                    $file_name = 'icon_goals_in_cup';
                }
                break;
            case 'sprint_membership':
                if($dim)
                {
                    $file_name = 'icon_goals_in_cup_dim';
                } else {
                    $file_name = 'icon_goals_in_cup';
                }
                break;
            case 'sprint':
                $file_name = 'icon_sprint';
                break;
            case 'pin':
                $file_name = 'icon_pin';
                break;
            case 'pinned_project':
                $file_name = 'icon_pinned_project';
                break;
            case 'vendor':
                $file_name = 'icon_vendor';
                break;
            case 'equipment':
                $file_name = 'icon_equipment';
                break;
            case 'template':
                $file_name = 'icon_template';
                break;
            case 'snapshot':
                $file_name = 'icon_camera';
                break;
            case 'cloudadmin':
                $file_name = 'icon_cloudadmin';
                break;
            case 'intogoal':
                $file_name = 'icon_intogoal';
                break;
            case 'no_intogoal':
                $file_name = 'icon_no_intogoal';
                break;
            case 'intoproject':
                $file_name = 'icon_intoproj';
                break;
            case 'no_intoproject':
                $file_name = 'icon_no_intoproj';
                break;
            case 'intotemplate':
                $file_name = 'icon_intotemplate';
                break;
            case 'intosnippet':
                $file_name = 'icon_intosnippet';
                break;
            case 'createprojectfromtemplate':
                $file_name = 'icon_createprojectfromtemplate';
                break;
            case 'duplicate':
                $file_name = 'icon_duplicate';
                break;
            case 'no_duplicate':
                $file_name = 'icon_no_duplicate';
                break;
            case 'download':
                $file_name = 'icon_download';
                break;
            case 'no_download':
                $file_name = 'icon_no_download';
                break;
            case 'download_template':
                $file_name = 'icon_download_template';
                break;
            case 'no_download_template':
                $file_name = 'icon_no_download_template';
                break;
            case 'nothing2download':
                $file_name = 'icon_nothing2download';
                break;
            case 'refresh':
                $file_name = 'icon_refresh';
                break;
            case 'group_membership':
                if($dim)
                {
                    $file_name = 'icon_goals_in_cup_dim';
                } else {
                    $file_name = 'icon_goals_in_cup';
                }
                break;
            case 'download_tabledata':
                $file_name = 'icon_download_tabledata';
                break;
            default:
                throw new \Exception("No icon for purpose name '$purpose_name'");
        } 
        if($small)
        {
            $file_name .= "_small";
        }
        $path2theme = path_to_theme();
        $icon_imgurl = file_create_url($path2theme."/images/{$file_name}.png");
        return $icon_imgurl;
    }

    /**
     * Get an art URL from a purpose name
     */
    public static function getArtURLForPurposeName($purpose_name, $small=FALSE)
    {
        //TODO -- make the filenames configurable
        switch($purpose_name)
        {
            case 'cloudadmin':
                $file_name = 'art_cloudadmin';
                break;
            case 'cloudadmin_seethru':
                $file_name = 'art_cloudadmin_seethru';
                break;
            case 'report':
                $file_name = 'art_report';
                break;
            case 'report_seethru':
                $file_name = 'art_report_seethru';
                break;
            default:
                throw new \Exception("No art for purpose name '$purpose_name'");
        } 
        if($small)
        {
            $file_name .= "_small";
        }
        $path2theme = path_to_theme();
        $icon_imgurl = file_create_url($path2theme."/images/{$file_name}.png");
        return $icon_imgurl;
    }
    
    /**
     * Get an art URL from a purpose name
     */
    public static function getHelpArtURLFromName($name)
    {
        $path2theme = path_to_theme();
        $icon_imgurl = file_create_url($path2theme."/images/help/{$name}.png");
        return $icon_imgurl;
    }
    
    public static function getAllowedAttachmentFileUploadTypes()
    {
        //TODO pull from database
        return 'txt pdf png gif jpg jpeg doc docx odt rtf csv xls xlsx ods ppt odp zip';
    }
    
    public static function getAllowedTemplateFileUploadTypes()
    {
        return 'json jsonp bftemplate bfpt.txt';
    }
    
    public static function getAllowedStatusFileUploadTypes()
    {
        return 'json jsonp bfstatus bfp.txt';
    }
    
    public static function getFileIconURL($filename,$clipped=TRUE)
    {
        $fileinfo = pathinfo($filename);
        if(isset($fileinfo['extension']))
        {
            $ext = strtolower(trim($fileinfo['extension']));
        } else {
            $ext = '';
        }
        $path2theme = path_to_theme();
        $img_suffix = NULL;
        switch($ext)
        {
            case 'txt':
            case 'pdf':
            case 'csv':
            case 'doc':
            case 'xls':
            case 'ppt':
            case 'zip':
                $img_suffix = $ext;
                break;
            case 'png':
            case 'gif':
            case 'jpg':
            case 'jpeg':
                $img_suffix = 'img';
                break;
            case 'rtf':
            case 'docx':
            case 'odt':
                $img_suffix = 'doc';
                break;
            case 'ods':
            case 'xlsx':
                $img_suffix = 'xls';
                break;
            case 'pptx':
            case 'odp':
                $img_suffix = 'ppt';
                break;
            case 'tab':
                $img_suffix = 'csv';
                break;
            default:
                $img_suffix = 'generic';
        }
        if($clipped)
        {
            $icon_imgurl = file_create_url($path2theme."/images/icon_paperclipped_$img_suffix.png");
        } else {
            $icon_imgurl = file_create_url($path2theme."/images/icon_$img_suffix.png");
        }
        return $icon_imgurl;
    }
    
    public static function getFriendlyFilesizeText($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
    
    public static function getTypeLetterFromRecordInfo($record)
    {
        $basetype = $record['workitem_basetype'];
        $equip = $record['equipmentid'];
        $extrc = $record['external_resourceid'];
        if($basetype == 'G')
        {
            $typeletter = 'G';
        } else {
            if(!empty($equip))
            {
                $typeletter = 'Q';
            } else if(!empty($extrc)) {
                $typeletter = 'X';
            } else {
                $typeletter = 'T';
            }
        }
        return $typeletter;
    }
    
    public static function setArrayValueIfEmpty(&$thearray, $thekey, $defaultvalue)
    {
        if(empty($thearray[$thekey]))
        {
            $thearray[$thekey] = $defaultvalue; 
        }
    }

    private static function canWorkToday($what_day, $isholiday, $effective_availability)
    {
        if($isholiday && !$effective_availability['work_holidays_yn'])
        {
            $worktoday = FALSE;
        } else
        if( $what_day == 1 && !$effective_availability['work_monday_yn']  
                || $what_day == 2 && !$effective_availability['work_tuesday_yn'] 
                || $what_day == 3 && !$effective_availability['work_wednesday_yn'] 
                || $what_day == 4 && !$effective_availability['work_thursday_yn'] 
                || $what_day == 5 && !$effective_availability['work_friday_yn']
                || $what_day == 6 && !$effective_availability['work_saturday_yn']
                || $what_day == 7 && !$effective_availability['work_sunday_yn']
           )
        {
            $worktoday = FALSE;
        } else {
            $worktoday = TRUE;
        }
        return $worktoday;
    }
    
    /**
     * Return the solution dates if there are any where the seeking_effort_hours can be worked by the person
     * after factoring in all the work they are already assigned to complete.
     */
    public static function getWorkEffortComputedDateBundle(
            $filter_starting_dt, $filter_max_ending_dt
            , $personid = NULL
            , $include_daily_detail=TRUE
            , $seeking_effort_hours, $ignore_seeking = FALSE
            , $direction = 1
            , $min_hours_to_start_work = 1
            , $maxdays = 1994
            , $today_dt = NULL
            , $existing_utilization_bundle = NULL
            , $has_locked_start_dt=FALSE
            , $has_locked_end_dt=FALSE
            , $deprecated_min_pct_buffer = 0
            , $deprecated_strict_min_pct=FALSE
            , $allocation_pct = NULL
            , $map_wid_allocations2ignore=NULL)
    {
        
        try
        {
            $MAX_COMPUTABLE_DAYS = 4000;
            if($maxdays > $MAX_COMPUTABLE_DAYS)
            {
                throw new \Exception("Requesting $maxdays days but max is $MAX_COMPUTABLE_DAYS");
            }
            $bundle = [];
            $solution_start_dt = NULL;
            if(empty($personid) && !empty($seeking_effort_hours))
            {
                throw new \Exception("Cannot seek hours without specifying a personid!");
            }
            
            if(empty($map_wid_allocations2ignore))
            {
                $map_wid_allocations2ignore = [];
            } else {
                if(!is_array($map_wid_allocations2ignore))
                {
                    //Assume only one wid, turn it into a map
                    $wid2ignore = $map_wid_allocations2ignore;
                    $map_wid_allocations2ignore = array($wid2ignore=>$wid2ignore);
                }
            }
            if(isset($map_wid_allocations2ignore[0]))
            {
                //We know there is never a wid with value zero this is always an error!
                DebugHelper::showStackTrace("ERROR with allocations2ignore not being a map", 'error', 8);
                throw new \Exception("The allocations2ignore is expected to be a map, not just a list but instead we got map_wid_allocations2ignore=" . print_r($map_wid_allocations2ignore,TRUE));
            }

            $deprecated_min_pct_buffer = 0;    //TODO --- KILL THIS PARAM!!!!!
            if($allocation_pct === NULL)
            {
                //Allow caller to pass in NULL, turn that into 100%
                $allocation_pct = 1;
            }
            if($allocation_pct <= 0)
            {
                DebugHelper::showStackTrace();
                throw new \Exception("Illegal value of allocation_pct=$allocation_pct");
            }
            
            if (empty($filter_starting_dt))
            {
                throw new \Exception("Missing required search start date!");
            }
            
            if (empty($today_dt))
            {
                $now_timestamp = time();
                $today_dt = gmdate("Y-m-d", $now_timestamp);
            }

            if(empty($maxdays))
            {
                //Allow for caller to pass NULL
                $maxdays = $MAX_COMPUTABLE_DAYS;
            }
            if (empty($filter_max_ending_dt))
            {
                if($ignore_seeking)
                {
                    $errmsg = "Not searching for solution and no end date was provided! (filter_starting_dt=$filter_starting_dt)";
                    DebugHelper::showStackTrace($errmsg);
                    throw new \Exception($errmsg);
                } else {
                    $filter_max_ending_dt = self::getDayShiftedDateAsISO8601DateText($today_dt, $maxdays);
                }
            }
            
            if(empty($min_hours_to_start_work))
            {
                //Allow caller to pass NULL
                $min_hours_to_start_work = 1;
            }
            
            if ($direction == 1)
            {
                if ($filter_starting_dt > $filter_max_ending_dt)
                {
                    return NULL;    //IMPOSSIBLE
                    //$errmsg = "Direction conflict cannot go from {$filter_starting_dt} to {$filter_max_ending_dt}!";
                    //DebugHelper::showStackTrace($errmsg);
                    //throw new \Exception($errmsg);
                }
            }
            else if ($direction == -1)
            {
                if($filter_max_ending_dt < $today_dt)
                {
                    //No sense seaching for solution that starts in the past!
                    $filter_max_ending_dt = $today_dt;
                }
                if ($filter_starting_dt < $filter_max_ending_dt)
                {
                    return NULL;    //IMPOSSIBLE
                    //$errmsg = "Direction conflict cannot go from {$filter_starting_dt} to {$filter_max_ending_dt}!";
                    //DebugHelper::showStackTrace($errmsg);
                    //throw new \Exception($errmsg);
                }
            }
            else
            {
                throw new \Exception("Missing valid direction!");
            }

            if (empty($existing_utilization_bundle))
            {
                $existing_utilization_bundle = [];
            }

            if ($direction == 1)
            {
                $simple_day_count_between_sande = 1+UtilityGeneralFormulas::getSimpleDayCountBetweenDates($filter_starting_dt, $filter_max_ending_dt);
            }
            else
            {
                $simple_day_count_between_sande = 1+UtilityGeneralFormulas::getSimpleDayCountBetweenDates($filter_max_ending_dt, $filter_starting_dt);
            }
            if (strlen($filter_starting_dt) !== 10)
            {
                throw new \Exception("Expected start in YYYY-MM-DD format instead got [$filter_starting_dt]!");
            }
            
            $bigloop_counter = 0;
            $bigloop_history = [];
            $restart_dt = NULL;
            while($bigloop_counter < 1 || !empty($restart_dt))
            {
                $bigloop_counter++;
                $bigloop_history[] = array('restart_dt'=>$restart_dt);
                if($bigloop_counter > 5)
                {
                    throw new \Exception("ERROR TOO MANY BIGLOOPS! bigloop_counter=$bigloop_counter restart_dt=$restart_dt " . print_r($bigloop_history,TRUE));
                }
                
                $candidate_working_days_from_start = 0;
                $candidate_working_hours_from_start = 0;
                $candidate_working_days_from_today = 0;
                $candidate_working_hours_from_today = 0;

                $solution_working_days = 0;
                $allday_count = 0;
                $weekend_count = 0;
                $weekday_count = 0;
                $holiday_count = 0;
                $dayoff_count = 0;

                $working_days = 0;

                $workhours_from_start = 0;
                $busy_hours_from_start = 0;
                $workhours_from_today = 0;
                $busy_hours_from_today = 0;

                $busy_hours_from_start_wid_map = [];
                $busy_hours_from_today_wid_map = [];

                $segment_custom_availability_types = [];
                $segment_custom_availability_type_codes = [];

                $workhours = 0;
                $daily_detail = [];
                $workdays_info_ar = [];
                $active_utilization_ranges = [];    //Collection of utilizations relevant to current day
                $fit_feedback = NULL;

                module_load_include('php', 'bigfathom_core', 'core/WorkApportionmentHelper');
                $oWAH = new \bigfathom\WorkApportionmentHelper();

                if(!empty($restart_dt))
                {
                    $first_utilization_relevant_dt = $restart_dt;
                    $restart_dt=NULL;
                } else {
                    if($today_dt > $filter_starting_dt)
                    {
                        //Cannot start working before today so fix that.
                        $first_utilization_relevant_dt = $today_dt;
                        if($include_daily_detail)
                        {
                            $startbundle = self::getDayCountBundleBetweenDates($filter_starting_dt, $today_dt, $personid, $include_daily_detail, $filter_starting_dt);
                            $daily_detail = $startbundle['daily_detail'];
                        }
                        $filter_starting_dt = $today_dt;
                        $has_past_start_dt = 1;
                        $has_skipped_days = 0;
                    } else {
                        //We are ignoring some days or starting today
                        $first_utilization_relevant_dt = $filter_starting_dt;
                        $has_past_start_dt = 0;
                        if($today_dt === $filter_starting_dt)
                        {
                            $has_skipped_days = 0;
                        } else {
                            $has_skipped_days = 1;
                        }
                    }
                }
                $begin_as_YMD = $first_utilization_relevant_dt;
                
                $prev_as_YMD = NULL;
                $next_as_YMD = NULL;
                $begin = strtotime($begin_as_YMD) + 300;    //MUST ADD SOME SECONDS ELSE PHP BUG ON ADD LATER!!!
                $solution_start_dt = $begin_as_YMD;
                $solution_end_dt = $begin_as_YMD;

                $existing_utilization_ranges = [];
                $existing_utilization_idx = [];
                if (empty($personid) || $existing_utilization_bundle == NULL || empty($existing_utilization_bundle['smartbucketinfo']['by_personid'][$personid]))
                {
                    //Dont associate this with any person.
                    $all_smartbucket_objects = [];
                }
                else
                {
                    $all_smartbucket_objects = $existing_utilization_bundle['smartbucketobject']['by_personid'];
                    $smartbucket = $all_smartbucket_objects[$personid];
                    $awids = $smartbucket->getWorkitemIDs();
                    foreach ($awids as $onewid)
                    {
                        $onewiddata = $smartbucket->getWorkitemData($onewid);
                    }
                    $localdaycountranges = $existing_utilization_bundle['smartbucketinfo']['by_personid'][$personid];
                    foreach ($localdaycountranges as $onelocaldc)
                    {
                        $localplain = $onelocaldc['plain'];
                        $existing_utilization_ranges[] = $localplain;
                    }
                    $idx = 0;
                    foreach ($existing_utilization_ranges as $localplain)
                    {
                        $utilized_start_dt = $localplain['start_dt'];
                        $utilized_end_dt = $localplain['end_dt'];
                        if (!isset($existing_utilization_idx['start_dt'][$utilized_start_dt]))
                        {
                            $existing_utilization_idx['start_dt'][$utilized_start_dt] = [];
                        }
                        $existing_utilization_idx['start_dt'][$utilized_start_dt][] = $idx;
                        if (!isset($existing_utilization_idx['end_dt'][$utilized_end_dt]))
                        {
                            $existing_utilization_idx['end_dt'][$utilized_end_dt] = [];
                        }
                        $existing_utilization_idx['end_dt'][$utilized_end_dt][] = $idx;
                        $idx++;
                    }
                    ksort($existing_utilization_idx['start_dt']);
                    ksort($existing_utilization_idx['end_dt']);
                }

                $holidays = UtilityGeneralFormulas::getHolidays($begin_as_YMD, $direction);
                
                if(empty($personid))
                {
                    $default_baseline_availability = [];
                    $override_availability_bundle = [];
                    $baseline_availability_periods = [];
                    $override_availability_periods = [];
                } else {
                    $default_baseline_availability = UtilityGeneralFormulas::getBaselineAvailabilityRecord($personid);
                    $override_availability_bundle = UtilityGeneralFormulas::getPersonAvailabilityBundle($personid,$begin_as_YMD,$direction);
                    $baseline_availability_periods = $override_availability_bundle['B'];
                    $override_availability_periods = $override_availability_bundle['O'];
                }
                
                //Set the availability now in case we are starting INSIDE of one!
                $baseline_availability_until = $default_baseline_availability;
                $override_availability_until = NULL;
                foreach ($override_availability_periods as $oneavailperioddef)
                {
                    $availperiod_start_dt = $oneavailperioddef['start_dt'];
                    $availperiod_end_dt = $oneavailperioddef['end_dt'];
                    if ($begin_as_YMD >= $availperiod_start_dt && $begin_as_YMD <= $availperiod_end_dt)
                    {
                        //We are in one!
                        $override_availability_until = $oneavailperioddef;
                        break;
                    }
                }
                //Also set the baseline if we are already starting INSIDE one!
                foreach ($baseline_availability_periods as $oneavailperioddef)
                {
                    $availperiod_start_dt = $oneavailperioddef['start_dt'];
                    $availperiod_end_dt = $oneavailperioddef['end_dt'];
                    if ($begin_as_YMD >= $availperiod_start_dt && $begin_as_YMD <= $availperiod_end_dt)
                    {
                        //We are in one!
                        $baseline_availability_until = $oneavailperioddef;
                        break;
                    }
                }

                //Loop through all the days now
                if ($maxdays < $simple_day_count_between_sande)
                {
                    //drupal_set_message("Date range $simple_day_count_between_sande days is too big for utilization calculation.  We will only process date ranges up to $maxdays days.  Computed result may not be complete.", "warning");
                    error_log("Date range $simple_day_count_between_sande days is too big for utilization calculation.  We will only process date ranges up to $maxdays days.  Computed result may not be complete.");
                }

                //while($begin_as_YMD <= $filter_max_ending_dt && ($ignore_seeking || $seeking_effort_hours>$workhours) && $allday_count < $maxdays)
                $debug_show = FALSE;
                $goodfit = FALSE;
                $iteration_counter = 0;
                while ($iteration_counter<1 
                        || ($allday_count < $simple_day_count_between_sande 
                            && ($ignore_seeking || !$goodfit)
                            && $allday_count <= $maxdays))
                {
                    $iteration_counter++;
                    $today_custom_availability_types = [];
                    $today_custom_availability_type_codes = [];
                    
                    $isholiday = FALSE;
                    $isweekday = FALSE;
                    $isweekend = FALSE;
                    $isdayoff = FALSE;
                    $isworkday = FALSE;
                    $workweekends = FALSE;
                    $workholidays = FALSE;
                    $workhoursinday = 8;
                    $allday_count++; // no of days in the given interval
                    $what_day = date("N", $begin);

                    $avail_type_cd = NULL;

                    $daily_detail[$begin_as_YMD] = [];

                    if ($what_day > 5)
                    {
                        // 6 and 7 are weekend days
                        $isweekend = TRUE;
                        $weekend_count++;
                    }
                    else
                    {
                        $isweekday = TRUE;
                        $weekday_count++;
                    }

                    if (isset($holidays[$begin]))
                    {
                        $holiday_count++;
                        $isholiday = TRUE;
                    }

                    if (isset($baseline_availability_periods[$begin_as_YMD]))
                    {
                        //Starting point of a custom baseline availability period
                        $baseline_availability_until = $baseline_availability_periods[$begin_as_YMD];
                    }
                    
                    if (isset($override_availability_periods[$begin_as_YMD]))
                    {
                        //Starting point of a custom availability period
                        $override_availability_until = $override_availability_periods[$begin_as_YMD];
                    }

                    if (!empty($override_availability_until))
                    {
                        $using_override_availability = TRUE;
                        $effective_availability = $override_availability_until;
                        $avail_type_cd = $effective_availability['type_cd'];
                        $today_custom_availability_type_codes[$avail_type_cd] = $avail_type_cd;
                        $segment_custom_availability_type_codes[$avail_type_cd] = $avail_type_cd;
                    }
                    else
                    {
                        $using_override_availability = FALSE;
                        $effective_availability = $baseline_availability_until;
                    }
                    
                    //Remove from our active utilizations?
                    $tokeep = [];
                    if ($direction == 1)
                    {
                        foreach ($active_utilization_ranges as $idx)
                        {
                            $localplain = $existing_utilization_ranges[$idx];
                            if ($localplain['end_dt'] >= $begin_as_YMD)
                            {
                                $tokeep[$idx] = $idx;
                            }
                        }
                    }
                    else
                    {
                        foreach ($active_utilization_ranges as $idx)
                        {
                            $localplain = $existing_utilization_ranges[$idx];
                            if ($localplain['end_dt'] <= $begin_as_YMD)
                            {
                                $tokeep[$idx] = $idx;
                            }
                        }
                    }

                    if (count($tokeep) < count($active_utilization_ranges))
                    {
                        $active_utilization_ranges = $tokeep;
                    }
                    //Add to our active utilizations?
                    for ($idx = 0; $idx < count($existing_utilization_ranges); $idx++)
                    {
                        if (!isset($active_utilization_ranges[$idx]))
                        {
                            //See if we should add this one
                            $localplain = $existing_utilization_ranges[$idx];
                            if ($localplain['start_dt'] <= $begin_as_YMD && $localplain['end_dt'] >= $begin_as_YMD)
                            {
                                //Add this one because it overlaps current date
                                $active_utilization_ranges[$idx] = $idx;
                            }
                        }
                    }

                    $today_busy_hours = 0;
                    $today_busy_hours_wid_map = [];
                    $debug_today_busy_hours_wid_map = [];
                    $quitnow = FALSE;
                    if(!empty($personid))
                    {
                        //NOTE ---- WE SHOULD REPLACE ALL THIS LOGIC WITH UTILIZTAION MODULE INSTEAD!!!!!
                        //TODO SECTION START ----- MOVE TO DEDICATED FUNCTION 
                        //Compute existing utilization for today
                        foreach ($active_utilization_ranges as $idx)
                        {
                            $localplain = $existing_utilization_ranges[$idx];
                            $workthisday = ($isholiday && $effective_availability['work_holidays_yn'] == 1) 
                                    || ($what_day == 1 && $effective_availability['work_monday_yn'] == 1)
                                    || ($what_day == 2 && $effective_availability['work_tuesday_yn'] == 1)
                                    || ($what_day == 3 && $effective_availability['work_wednesday_yn'] == 1)
                                    || ($what_day == 4 && $effective_availability['work_thursday_yn'] == 1)
                                    || ($what_day == 5 && $effective_availability['work_friday_yn'] == 1)
                                    || ($what_day == 6 && $effective_availability['work_saturday_yn'] == 1) 
                                    || ($what_day == 7 && $effective_availability['work_sunday_yn'] == 1);
                            if ($workthisday)
                            {
                                if ($begin_as_YMD >= $today_dt)
                                {
                                    //TODO ---- Get the information from the INTERVALS instead of computing bad approximation here!!!!
                                    $lp_isdt = $localplain['start_dt'];
                                    $lp_iedt = $localplain['end_dt'];
                                    $lp_intervals_by_wid = $localplain['intervals'];
                                    foreach($lp_intervals_by_wid as $onewid=>$plaininfo4onewid)
                                    {
                                        //DebugHelper::debugPrintNeatly($plaininfo4onewid, FALSE, "LOOK at wid=$onewid ....",'.....','error');
                                        if(!array_key_exists($onewid, $map_wid_allocations2ignore))
                                        {
                                            $twd = $plaininfo4onewid['twd'];
                                            $twh = $plaininfo4onewid['twh'];
                                            $af = $plaininfo4onewid['af'];
                                            $wahc = $twh * $af;
                                            $rounded_wid_apportioned_hours = round($wahc / 10, 1);
                                            if($rounded_wid_apportioned_hours > 0)
                                            {
                                                $today_busy_hours_wid_map[$onewid] = $rounded_wid_apportioned_hours;
                                                $ab = 'STRUCT_TODO';
                                                $debug_today_busy_hours_wid_map[$onewid] = array('rwah'=>$rounded_wid_apportioned_hours, 'adetail'=>$ab);
                                                $today_busy_hours += $rounded_wid_apportioned_hours;                                
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        //TODO SECTION END ----- MOVE TO DEDICATED FUNCTION FROM UTILIZATION MODULE
                        
                        //Apply the availability rules
                        if ($effective_availability['hours_per_day'] == 0)
                        {
                            //We know they cannot work any hours in entire range
                            $isdayoff = TRUE;
                            if ($isweekday && !$isholiday)
                            {
                                $dayoff_count++;
                            }
                            $workhoursinday = 0;
                            $today_custom_availability_types['dayoff'] = 'dayoff';
                            $segment_custom_availability_types['dayoff'] = 'dayoff';
                        } else 
                        if ($effective_availability['hours_per_day'] > 0)
                        {
                            //Somewhere in this range they can work more than zero hours, still not sure what days
                            $workhoursinday = $effective_availability['hours_per_day'];
                            $wdc = $effective_availability['work_monday_yn'] 
                                    + $effective_availability['work_tuesday_yn'] 
                                    + $effective_availability['work_wednesday_yn'] 
                                    + $effective_availability['work_thursday_yn'] 
                                    + $effective_availability['work_friday_yn'];
                            $wedc = $effective_availability['work_saturday_yn'] 
                                    + $effective_availability['work_sunday_yn'];
                            if ($wdc == 5)
                            {
                                if ($using_override_availability)
                                {
                                    //Dont bother with this marker unless it is in an override
                                    $marker = 'hours/day(' . $workhoursinday . ')';
                                }
                                else
                                {
                                    $marker = NULL;
                                }
                            }
                            else
                            {
                                if ($wdc == 0)
                                {
                                    if ($wedc == 0)
                                    {
                                        //Indicates an irrational record
                                        $marker = 'hours/day(' . $workhoursinday . "@ZERO DAYS?)";
                                    }
                                    else
                                    {
                                        $marker = 'hours/day(' . $workhoursinday . "@only weekends)";
                                    }
                                }
                                else
                                {
                                    $marker = 'hours/day(' . $workhoursinday . "@$wdc weekdays)";
                                }
                            }
                            if (!empty($marker))
                            {
                                $today_custom_availability_types[$marker] = $marker;
                                $segment_custom_availability_types[$marker] = $marker;
                            }
                        }

                        if ($effective_availability['work_saturday_yn'] == 1 || $effective_availability['work_sunday_yn'] == 1)
                        {
                            $workweekends = TRUE;
                            if ($isweekend)
                            {
                                $today_custom_availability_types['workweekends'] = 'workweekends';
                                $segment_custom_availability_types['workweekends'] = 'workweekends';
                            }
                        }

                        if ($effective_availability['work_holidays_yn'] == 1)
                        {
                            $workholidays = TRUE;
                            if ($isholiday)
                            {
                                $today_custom_availability_types['workholidays'] = 'workholidays';
                                $segment_custom_availability_types['workholidays'] = 'workholidays';
                            }
                        }

                        if(!self::canWorkToday($what_day, $isholiday, $effective_availability))
                        {
                            $workhoursinday = 0;
                            $isdayoff = TRUE;
                        }

                        if ($override_availability_until != NULL && $override_availability_until['end_dt'] == $begin_as_YMD)
                        {
                            //We are done with this custom availability declaration
                            $override_availability_until = NULL;
                        }
                        
                        if ($baseline_availability_until['end_dt'] == $begin_as_YMD)
                        {
                            //We are done with this custom baseline availability declaration
                            $baseline_availability_until = $default_baseline_availability;
                        }
                        
                        $busy_hours_from_start += $today_busy_hours;
                        foreach($today_busy_hours_wid_map as $onewid=>$onebusy)
                        {
                            if(!isset($busy_hours_from_start_wid_map[$onewid]))
                            {
                                $busy_hours_from_start_wid_map[$onewid] = 0;
                            }
                            $busy_hours_from_start_wid_map[$onewid] += $onebusy;
                        }
                        if ($begin_as_YMD >= $today_dt)
                        {
                            $busy_hours_from_today += $today_busy_hours;
                            foreach($today_busy_hours_wid_map as $onewid=>$onebusy)
                            {
                                if(!isset($busy_hours_from_today_wid_map[$onewid]))
                                {
                                    $busy_hours_from_today_wid_map[$onewid] = 0;
                                }
                                $busy_hours_from_today_wid_map[$onewid] += $onebusy;
                            }
                        }

                        if ($isweekday && !$isdayoff || $isweekend && $workweekends || $isholiday && $workholidays)
                        {
                            if ($workhoursinday > 0)
                            {
                                //This is a workday for the person
                                $available_workhoursinday = $workhoursinday * $allocation_pct;
                                $workdays_info_ar[$begin_as_YMD]['workhoursinday'] = $workhoursinday;
                                $workdays_info_ar[$begin_as_YMD]['available_workhoursinday'] = $available_workhoursinday;
                                $candidate_working_days_from_start++;
                                $candidate_working_hours_from_start += $workhoursinday;
                                if ($begin_as_YMD >= $today_dt)
                                {
                                    $candidate_working_days_from_today++;
                                    $candidate_working_hours_from_today += $workhoursinday;
                                }
                                $today_unallocated_workhours = $workhoursinday - $today_busy_hours; //Allowing negative is intentional!
                                $isworkday = 1; //$effective_availability['hours_per_day'];
                                $workhours += $available_workhoursinday;
                                $working_days++;

                                if ($workhours_from_start > 0 || $today_unallocated_workhours >= $min_hours_to_start_work)
                                {
                                    $solution_working_days++;
                                    $workhours_from_start += $today_unallocated_workhours; //Negative is intentional!
                                    if ($begin_as_YMD >= $today_dt)
                                    {
                                        $workhours_from_today += $today_unallocated_workhours; //Negative is intentional!
                                    }
                                    if (empty($solution_start_dt))
                                    {
                                        $solution_start_dt = $begin_as_YMD;
                                    }
                                    if (!$ignore_seeking)
                                    {
                                        if ($seeking_effort_hours <= $workhours_from_today)
                                        {
                                            $quitnow = TRUE;
                                        }
                                    }
                                }
                            }
                        }
                        $daily_detail[$begin_as_YMD]['isweekend'] = $isweekend;
                        $daily_detail[$begin_as_YMD]['isdayoff'] = $isdayoff;
                        $daily_detail[$begin_as_YMD]['isworkday'] = $isworkday;
                        $daily_detail[$begin_as_YMD]['workhoursinday'] = $workhoursinday;
                        $daily_detail[$begin_as_YMD]['today_busy_hours'] = $today_busy_hours;
                        $daily_detail[$begin_as_YMD]['avail_type_cd'] = $avail_type_cd;
                        $daily_detail[$begin_as_YMD]['availability']['hasworkweekends'] = $workweekends;
                        $daily_detail[$begin_as_YMD]['availability']['hasworkholidays'] = $workholidays;
                        $daily_detail[$begin_as_YMD]['availability']['custom_availability_types'] = $today_custom_availability_types;
                    }

                    //ALWAYS TRACK THIS FOR OUR METRICS!!!
                    $daily_detail[$begin_as_YMD]['isholiday'] = $isholiday;
                    $daily_detail[$begin_as_YMD]['isweekday'] = $isweekday;
                    
                    if ($direction == 1)
                    {
                        $begin = strtotime('+1 days', $begin);
                    }
                    else
                    {
                        $begin = strtotime('-1 days', $begin);
                    }

                    $prev_as_YMD = $begin_as_YMD;
                    $begin_as_YMD = gmdate("Y-m-d", $begin);
                    $next_as_YMD = $begin_as_YMD;
                    if($quitnow)
                    {
                        $solution_end_dt = $prev_as_YMD;
                        if($ignore_seeking)
                        {
                           break; 
                        } else {
                            //Have we found enough?
                            if($workhours < $seeking_effort_hours)
                            {
                                $goodfit = FALSE;
                            } else {
                                $fit_feedback = $oWAH->getFitFeedback($seeking_effort_hours, $daily_detail, $solution_start_dt, $prev_as_YMD, $today_dt, $deprecated_min_pct_buffer, $deprecated_strict_min_pct);
                                $fit_feedback['metadata']['context'] = 'computed in loop';
                                $goodfit = $fit_feedback['is_okay']; //$seeking_effort_hours > $workhours_from_today           
                            }
                            if($goodfit)
                            {
                                //We are here because OK and there was no suggested new date
                                break;
                            } else {
                                if($has_locked_start_dt)
                                {
                                    $debug_show=TRUE;
                                    $quitnow = FALSE;
                                } else {
                                    if(!empty($fit_feedback['suggestions']['new_start_dt']))
                                    {
                                        //Change start date and subtract all hours until new date
                                        $solution_start_dt = $fit_feedback['suggestions']['new_start_dt'];
                                        if($solution_start_dt < $begin_as_YMD)
                                        {
                                            //Sliding the start date back, need to compute if not already computed
                                            $restart_dt = $solution_start_dt;
                                            break;
                                        } else {
                                            //Sliding the start date forward
                                            $discardtotal = 0;
                                            foreach($daily_detail as $onedate=>$onedaydetail)
                                            {
                                                if($onedate < $solution_start_dt)
                                                {
                                                    $workhoursinday = $onedaydetail['workhoursinday'];
                                                    $today_busy_hours = $onedaydetail['today_busy_hours'];
                                                    $today_unallocated_workhours = $workhoursinday - $today_busy_hours;
                                                    $discardtotal += $today_unallocated_workhours;
                                                }
                                            }
                                            $workhours_from_today -= $discardtotal;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } //WHILE !EMPTY restart_dt
            
            $bundle['looklastthingstuff'] = "$prev_as_YMD  $seeking_effort_hours > $workhours_from_start";
            if ($seeking_effort_hours > $workhours_from_start)
            {
                //Failed to find a solution!
                $prev_as_YMD = NULL;
            }
            $solution_end_dt = $prev_as_YMD;
            if($direction > 0)
            {
                //Direction is already normal
                if(!empty($solution_start_dt) && !empty($solution_end_dt) && $solution_start_dt > $solution_end_dt)
                {
                    //Special case same day
                    $solution_end_dt = $solution_start_dt;
                }
            } else {
                //Now normalize the result information as forward directed
                $swapper = $solution_start_dt;
                $solution_start_dt = $solution_end_dt;
                $solution_end_dt = $swapper;
                $daily_detail = array_reverse($daily_detail);
            }

            $bundle['first_dt'] = $first_utilization_relevant_dt;
            $bundle['last_dt'] = $solution_end_dt;
            $bundle['next_dt'] = $next_as_YMD;
            $bundle['custom_availability_types'] = $segment_custom_availability_types;
            $bundle['already_busy_hours'] = $busy_hours_from_start;
            $bundle['already_busy_hours_wid_map'] = $busy_hours_from_start_wid_map;
            $bundle['open_hours'] = $workhours_from_start;
            
            $bundle['holiday'] = $holiday_count;
            $bundle['weekendday'] = $weekend_count;
            $bundle['dayoff'] = $dayoff_count;
            $bundle['weekday'] = $weekday_count;
            
            $bundle['workday'] = $working_days;
            $bundle['total'] = $allday_count;
            
            $bundle['work_days'] = $candidate_working_days_from_start;
            $bundle['work_hours'] = $candidate_working_hours_from_start;
            $bundle['available_work_hours'] = $workhours;
            $bundle['seeking_effort_hours_yn'] = $ignore_seeking ? 0 : 1;
            
            $bundle['from_today']['today_dt'] = $today_dt;
            $bundle['from_today']['computation_start_dt'] = $first_utilization_relevant_dt;
            $bundle['from_today']['found_effort_hours'] = $workhours_from_today;
            $bundle['from_today']['already_busy_hours'] = $busy_hours_from_today;
            $bundle['from_today']['already_busy_hours_wid_map'] = $busy_hours_from_today_wid_map;
            $bundle['from_today']['work_hours'] = $candidate_working_hours_from_today;
            $bundle['from_today']['work_days'] = $candidate_working_days_from_today;
            
            $solution = [];
            $solution['start_dt'] = $solution_start_dt;
            if(!empty($solution_start_dt) && empty($solution_end_dt) || $solution_end_dt < $solution_start_dt)
            {
                //Simply return a date without considering availability
                $shift_days = $seeking_effort_hours / 5;  //5 hours per day every day
                //$solution_end_dt = $solution_start_dt;// self::getDayShiftedDateAsISO8601DateText($solution_start_dt, $shift_days);
                $solution_end_dt = self::getDayShiftedDateAsISO8601DateText($solution_start_dt, $shift_days);
            }
            $solution['end_dt'] = $solution_end_dt;
            
            $solution['found_effort_hours'] = $workhours_from_start;
            $solution['candidate_working_days'] = $candidate_working_days_from_start;
            $solution['found_working_days'] = $solution_working_days;
            if (!$ignore_seeking)
            {
                $solution['seeking_effort_hours'] = $seeking_effort_hours;
            }
            
            if(!empty($solution_start_dt) && !isset($daily_detail[$solution_start_dt])
             || (!empty($solution_end_dt) && !isset($daily_detail[$solution_end_dt])))
            {
                //This means we came up with a date that did not factor in real utilization
                //DebugHelper::debugPrintNeatly(array('##$daily_detail'=>$daily_detail), FALSE, "ERROR for direction=$direction seeking_effort_hours=$seeking_effort_hours Missing bound dates [$solution_start_dt,$solution_end_dt] in the daily_detail ...........","...... daily_detail info","error");
                //DebugHelper::showStackTrace();
                //throw new \Exception("Missing bound dates [$solution_start_dt,$solution_end_dt] in the daily_detail " . print_r($daily_detail,TRUE));
                $outside_known_date_range_yn = 1;
            } else {
                $outside_known_date_range_yn = 0;
            }
            
            if(empty($fit_feedback))
            {
                //Provide feedback about the solution even if it already existed if fit not already computed
                $fit_feedback = $oWAH->getFitFeedback($seeking_effort_hours, $daily_detail, $solution_start_dt, $solution_end_dt, $today_dt, $deprecated_min_pct_buffer, $deprecated_strict_min_pct);
                $fit_feedback['metadata']['context'] = 'computed at the end';
            }
            $solution['feedback'] = $fit_feedback;
            $bundle['solution'] = $solution;
            
            if(isset($segment_custom_availability_types['dayoff']))
            {
                unset($segment_custom_availability_types['dayoff']);
                $segment_custom_availability_types['daysoff(' . $dayoff_count . ')'] = $dayoff_count;
            }
            $bundle['custom_availability_types'] = array_keys($segment_custom_availability_types);
            $bundle['custom_availability_type_codes'] = array_keys($segment_custom_availability_type_codes);
            if($include_daily_detail)
            {
                $bundle['daily_detail'] = $daily_detail;
                $bundle['daily_workdays_info'] = $workdays_info_ar;
            }
            $metadata = [];
            $metadata['outside_known_date_range_yn'] = $outside_known_date_range_yn;
            $metadata['blurb'] = "Computed from $first_utilization_relevant_dt to $solution_end_dt for personid#$personid";
            $metadata['has_past_start_dt'] = $has_past_start_dt;
            $metadata['has_skipped_days'] = $has_skipped_days;
            $metadata['seeking_effort_hours'] = $seeking_effort_hours;
            $bundle['metadata'] = $metadata;

            if (!empty($solution_end_dt) && $solution_start_dt > $solution_end_dt)
            {
                $errmsg = "ERROR COMPUTED SOLUTION Direction conflict cannot go from {$solution_start_dt} to {$solution_end_dt}!";
                DebugHelper::debugPrintNeatly(array('##$bundle'=>$bundle),FALSE,"DEBUG $errmsg ....",".............. info");                
                DebugHelper::showStackTrace("DEBUG $errmsg");
                throw new \Exception($errmsg);
            }
            return $bundle;
        }
        catch (\Exception $ex)
        {
            DebugHelper::debugPrintNeatly(array('##$existing_utilization_bundle'=>$existing_utilization_bundle,'##exceptionmessage'=>$ex->getMessage()),FALSE,"DEBUG exception stuff  ....",".............. exception stuff","error");                
            throw $ex;
        }
    }
    
    
    /**
     * Round it up to avoid subtle utilization rounding overages down-stream
     */
    public static function getNeededHoursPerDay($remaining_effort_hours, $availabledaycount)
    {
        $need_hoursperday = ceil(100 * ($remaining_effort_hours / $availabledaycount))/100;
    }

    /**
     * Return the declared start and end dates as derived from the workitem detail record
     */
    public static function getWorkitemEffectiveDeclaredDateBounds($wdetail)
    {
        $effective_start_dt = NULL;
        if(isset($wdetail['actual_start_dt']))
        {
            $effective_start_dt = $wdetail['actual_start_dt'];
        } else
        if(isset($wdetail['planned_start_dt']))
        {
            $effective_start_dt = $wdetail['planned_start_dt'];
        }
        $effective_end_dt = NULL;
        if(isset($wdetail['actual_end_dt']))
        {
            $effective_end_dt = $wdetail['actual_end_dt'];
        } else
        if(isset($wdetail['planned_end_dt']))
        {
            $effective_end_dt = $wdetail['planned_end_dt'];
        }
        $bundle = array('start_dt'=>$effective_start_dt,'end_dt'=>$effective_end_dt);
        return $bundle;
    }
    
    public static function getDelimitedTextAsCleanArray($list_tx, $delimiter=",")
    {
        $list_ar = explode($delimiter, $list_tx);
        $clean_list_ar = [];
        foreach($list_ar as $item)
        {
            $clean = trim($item);
            if(!empty($clean))
            {
                $clean_list_ar[$item] = $clean;
            }
        }
        return $clean_list_ar;
    }
    
    public static function getDelimitedNumbersAsCleanArray($list_tx, $delimiter=",")
    {
        $list_ar = explode($delimiter, $list_tx);
        $clean_list_ar = [];
        foreach($list_ar as $item)
        {
            $clean = trim($item);
            if(!empty($clean))
            {
                if(!is_numeric($clean))
                {
                    throw new \Exception("Bad item '$clean' in number list $list_tx");
                }
                $clean_list_ar[$item] = $clean;
            }
        }
        return $clean_list_ar;
    }
    
    /**
     * Tell us if Drupal already has the exact same message in the queue
     * @param type $msg the exact message we are checking
     * @param type $typename info, warning, or error
     * @return boolean TRUE if already in the message queue, else FALSE
     */
    public static function hasExistingDrupalMessageMatch($msg, $typename='info')
    {
        if(isset($_SESSION['messages'][$typename]))
        {
            foreach($_SESSION['messages'][$typename] as $existingmsg)
            {
                if($existingmsg == $msg)
                {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }    
}

