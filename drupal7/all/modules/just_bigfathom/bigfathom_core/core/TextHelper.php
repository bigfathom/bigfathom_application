<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * --------------------------------------------------------------------------------------
 *
 */

namespace bigfathom;

/**
 * This class helps us manipulate text
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TextHelper 
{
    protected $m_today = NULL;
    
    
    public static function getBasicNamingErrors($candidate, $maxlen=NULL)
    {
        $result = [];
        if(!empty($maxlen) && strlen($candidate) > $maxlen)
        {
            $result[]  = " too long.  Maxmimum allowed is $maxlen";
        }
        $doublespaced = strpos($candidate,"  ");
        if($doublespaced > 0)
        {
            $result[]  = " has double spacing between terms!  Remove the extra spaces starting at position " . $doublespaced;
        }
        return $result;
    }

    public static function getBasicRefNamingErrors($candidate, $maxlen=NULL)
    {
        $result = [];
        if(!empty($maxlen) && strlen($candidate) > $maxlen)
        {
            $result[]  = " too long.  Maxmimum allowed is $maxlen";
        }
        $pos = strpos($candidate," ");
        if($pos > 0)
        {
            $result[]  = " has spacing between terms!  Consider using underscores instead!";
        }
        if(strpos($candidate,"<") > 0 || strpos($candidate,">") > 0 
                || strpos($candidate,"?") > 0 || strpos($candidate,"!") > 0 
                || strpos($candidate,";") > 0 || strpos($candidate,":") > 0 
                || strpos($candidate,"'") > 0 || strpos($candidate,'"') > 0 
                || strpos($candidate,"~") > 0 || strpos($candidate,'`') > 0 
                || strpos($candidate,"#") > 0 || strpos($candidate,'@') > 0 
                || strpos($candidate,"%") > 0 || strpos($candidate,'$') > 0 
                || strpos($candidate,"+") > 0 || strpos($candidate,'-') > 0 
                || strpos($candidate,"{") > 0 || strpos($candidate,'}') > 0 
                || strpos($candidate,"(") > 0 || strpos($candidate,')') > 0 
                || strpos($candidate,"[") > 0 || strpos($candidate,']') > 0 
                || strpos($candidate,"*") > 0 || strpos($candidate,'^') > 0 
                || strpos($candidate,",") > 0 || strpos($candidate,'&') > 0 
                || strpos($candidate,'\\') > 0 )
        {
            $result[]  = " has illegal character(s)";
        }
        return $result;
    }
    
    public function getJustDateTextFromDateTime($datetime,$format="Y-m-d")
    {
        try
        {
            if(empty($datetime))
            {
                return NULL;
            }
            return date($format, strtotime($datetime));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getJustDateTextFromDateTimeUnlessToday($datetime,$format="Y-m-d")
    {
        try
        {
            if(empty($datetime))
            {
                return NULL;
            }
            if($this->m_today == NULL)
            {
                $this->m_today = $updated_dt = date("Y-m-d");;
            }
            $justdate = date($format, strtotime($datetime));
            if($this->m_today == $justdate)
            {
                //Include the time
                return $datetime;
            }
            return $justdate;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a shorthand abbreviation for availability
     */
    public static function getAvailabilityDaysAbbr($record)
    {
        $daycount = $record['work_saturday_yn'];
        $daycount += $record['work_saturday_yn'];
        $daycount += $record['work_sunday_yn'];
        $daycount += $record['work_monday_yn'];
        $daycount += $record['work_tuesday_yn'];
        $daycount += $record['work_wednesday_yn'];
        $daycount += $record['work_thursday_yn'];
        $daycount += $record['work_friday_yn'];
        
        $sat = $record['work_saturday_yn'] ? '+S' : '-s';
        $sun = $record['work_sunday_yn'] ? '+S' : '-s';
        $mon = $record['work_monday_yn'] ? '+M' : '-m';
        $tue = $record['work_tuesday_yn'] ? '+T' : '-t';
        $wed = $record['work_wednesday_yn'] ? '+W' : '-w';
        $thu = $record['work_thursday_yn'] ? '+T' : '-t';
        $fri = $record['work_friday_yn'] ? '+F' : '-f';
        $hol = $record['work_holidays_yn'] ? '+H' : '-h';
        
        $abbr = "[$mon$tue$wed$thu$fri][$sat$sun][$hol]";
        
        return array('count'=>$daycount, 'abbr'=>$abbr);
    }

}

