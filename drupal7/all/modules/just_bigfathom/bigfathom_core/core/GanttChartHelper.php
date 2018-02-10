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
 * This class provides help composing Gantt charts
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class GanttChartHelper
{
    private $m_daycount;
    private $m_px_per_day;
    
    private $m_path2theme;
    private $m_clearspacer_url;
    
    //private $m_datemarker_width = 22;
    private $m_datemarker_width = 11;   //hw
    
    public function __construct($min_date=NULL, $max_date=NULL, $max_width=200, $max_height=20, $normal_height=20)
    {
        $this->m_path2theme = path_to_theme();
        $this->m_clearspacer_url = file_create_url($this->m_path2theme."/images/gantt_clearspacer.png");
        if(empty($min_date))
        {
            throw new \Exception("Cannot add a workitem without specifying a min_date!");
        }
        if(empty($max_date))
        {
            throw new \Exception("Cannot add a workitem without specifying a max_date!");
        }
        $this->m_min_date=$min_date;
        $this->m_max_date=$max_date;
        $this->max_width=$max_width;
        $this->normal_height=$normal_height;
        $this->max_height=$max_height;
        $this->m_daycount = UtilityGeneralFormulas::getSimpleDayCountBetweenDates($min_date, $max_date);
        if($this->m_daycount < 1)
        {
            $this->m_px_per_day = 0;    //Undefined!
        } else {
            $this->m_px_per_day = $max_width / $this->m_daycount;
        }
//drupal_set_message("LOOK GanttChartHelper($min_date, $max_date, $max_width, $max_height)  pxperday={$this->m_px_per_day}");      
    }    

    public function getTypeNameForChart($basetype, $is_projectroot=FALSE)
    {
        $typename = "";
        if($is_projectroot)
        {
            $typename =  "proj";
        } else {
            switch(strtoupper($basetype))
            {
                case "G":
                    $typename =  "goal";
                    break;
                default :
                    $typename =  "task";
            }
        }
        return $typename;
    }
    
    public function getGanttIconURL($purpose_name, $typename, $is_estimated=FALSE, $is_pinned=FALSE, $is_bad=FALSE)
    {
        $extra_suffix = "";
        /*
        if($purpose_name !== 'middle' && $purpose_name !='singledate')
        {
            $extra_suffix = "_hw";
        } else {
            $extra_suffix = "";
        }
        */
        if($is_bad)
        {
            $typename = 'bad';
        }
        if($is_estimated)
        {
            if($is_pinned)
            {
                $file_name = "gantt_{$typename}_{$purpose_name}_est_pinned{$extra_suffix}";
            } else {
                $file_name = "gantt_{$typename}_{$purpose_name}_est{$extra_suffix}";
            }
        } else {
            if($is_pinned)
            {
                $file_name = "gantt_{$typename}_{$purpose_name}_pinned{$extra_suffix}";
            } else {
                $file_name = "gantt_{$typename}_{$purpose_name}{$extra_suffix}";
            }
        }
        $icon_imgurl = file_create_url($this->m_path2theme."/images/{$file_name}.png");
        return $icon_imgurl;
    }
    
    public function getGanttClearSpacerURL()
    {
        $icon_imgurl = file_create_url($this->m_path2theme."/images/gantt_clearspacer.png");
        return $icon_imgurl;
    }
    
    private function getBarStartSpacerPixelCount($startdate)
    {
        $days = UtilityGeneralFormulas::getSimpleDayCountBetweenDates($this->m_min_date, $startdate);
        $px = $days * $this->m_px_per_day;
        return $px;
    }

    private function getBarStartSpacerMarkup($startdate)
    {
        $px = $this->getBarStartSpacerPixelCount($startdate);
//drupal_set_message("LOOK maxw= $this->max_width getBarStartSpacerMarkup px=$px");
        
        return "<img src='{$this->m_clearspacer_url}' height='1px' width='{$px}px'/>";
    }

    private function getBarMiddleChunkPixelCount($startdate, $enddate)
    {
        $start_dt_px = $this->getBarStartSpacerPixelCount($startdate);
        $next_after_end_dt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($enddate, 1);
        $after_end_dt_px = $this->getBarStartSpacerPixelCount($next_after_end_dt);

        $middle_width = $after_end_dt_px - $start_dt_px;// - $this->m_datemarker_width;
        return $middle_width;
    }
    
    private function getBarMiddleMarkup($px, $durationdays, $typename, $is_estimated=FALSE, $is_pinned=FALSE, $problem_tooltip="")
    {
        if($px < 1)
        {
            if($durationdays == 1)
            {
                return "<span title='1 day with no space to display'></span>";
            } else {
                return "<span title='$durationdays days with no space to display'></span>";
            }
        }
        $imgurl = $this->getGanttIconURL("middle", $typename, $is_estimated, $is_pinned);
        if($durationdays == 1)
        {
            $daystxtblurb = "1 day";
        } else {
            $daystxtblurb = "$durationdays days";
        }
        if(empty($problem_tooltip))
        {
            $tooltip = "$daystxtblurb";
        } else {
            $tooltip = "$daystxtblurb - $problem_tooltip";
        }
        return "<img title='{$tooltip}' src='{$imgurl}' height='{$this->normal_height}px' width='{$px}px'/>";
    }

    private function getBarStartMarkup($startdate, $typename, $is_estimated=FALSE, $is_pinned=FALSE, $problem_tooltip="")
    {
        $imgurl = $this->getGanttIconURL("startdate", $typename, $is_estimated, $is_pinned);
        if(empty($problem_tooltip))
        {
            $tooltip = "start $startdate";
        } else {
            $tooltip = "start $startdate - $problem_tooltip";
        }
        return "<img title='{$tooltip}' src='{$imgurl}'/>";
    }
    
    private function getBarEndMarkup($enddate, $typename, $is_estimated=FALSE, $is_pinned=FALSE, $problem_tooltip="")
    {
        $imgurl = $this->getGanttIconURL("enddate", $typename, $is_estimated, $is_pinned);
        if(empty($problem_tooltip))
        {
            $tooltip = "end $enddate";
        } else {
            $tooltip = "end $enddate - $problem_tooltip";
        }
        return "<img title='{$tooltip}' src='{$imgurl}'/>";
    }
    
    public function getGanttBarMarkup($typename, $startdate, $enddate, $est_flags=NULL, $pin_flags=NULL, $moreclassnames="", $problem_periods_ar=NULL)
    {
        $final_tooltip = NULL;
        if(empty($problem_periods_ar))
        {
            $problem_periods_ar = [];
        }
        $parts = [];
        $middle_parts = [];
        if($est_flags === NULL)
        {
            $est_flags = [];
        }
        if($pin_flags === NULL)
        {
            $pin_flags = [];
        }
        $daydiff = UtilityGeneralFormulas::getDayCountInDateRange($startdate, $enddate);
        $px = $daydiff * $this->m_px_per_day;
        if($daydiff !== NULL)
        {
            if($daydiff < 0)
            {
                
                //Dates are in WRONG order
                $parts[] = "date error";
                
            } else {
                $parts[] = $this->getBarStartSpacerMarkup($startdate);

                if(!empty($moreclassnames))
                {
                    $parts[] = "<span class='$moreclassnames'>";
                }
                
                //Check for declared problem periods
                $start_typename = $typename;
                $middle_typename = $typename;
                $end_typename = $typename;
                $start_problem_tooltip = "";
                $middle_problem_tooltip = "";
                $end_problem_tooltip = "";
                $relevant_problem_periods = [];
                foreach($problem_periods_ar as $oneinfo)
                {
                    $prob_start_dt = $oneinfo['start_dt'];
                    $prob_end_dt = $oneinfo['end_dt'];
                    $relevant_problem_periods[$prob_start_dt] = $oneinfo;
                }
                ksort($relevant_problem_periods);
                
                //Now get the graphics markup
                $middle_startdate = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($startdate, 1);
                $middle_enddate = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($enddate, -1);
                $is_startdate_estimated = isset($est_flags['startdate']) ? $est_flags['startdate'] : TRUE;
                $is_startdate_pinned = isset($pin_flags['startdate']) ? $est_flags['startdate'] && $pin_flags['startdate'] : FALSE;
                $is_enddate_estimated = isset($est_flags['enddate']) ? $est_flags['enddate'] : TRUE;
                $is_enddate_pinned = isset($pin_flags['enddate']) ? $est_flags['enddate'] && $pin_flags['enddate'] : FALSE;
                if($daydiff <= 1) 
                { 
                    //No gap
                    $final_tooltip = "one day $enddate";
                    $is_estimated = $is_startdate_estimated || $is_enddate_estimated;
                    $is_pinned = $is_startdate_pinned || $is_enddate_pinned;
                    $is_bad = $start_typename == 'bad';
                    $singledate_icon = $this->getGanttIconURL("singledate", $start_typename, $is_estimated, $is_pinned, $is_bad);
                    $parts[] = "<img src='$singledate_icon'/>";
                } else {
                    $first_problem_period = NULL;
                    $last_problem_period = NULL;
                    $is_middle_estimated = isset($est_flags['middle']) ? $est_flags['middle'] : TRUE;
                    $is_middle_pinned = isset($pin_flags['middle']) ? $pin_flags['middle'] : FALSE;
                    $middle_bar_width = $this->getBarMiddleChunkPixelCount($middle_startdate, $middle_enddate);
                    if($middle_typename == 'bad' || count($relevant_problem_periods) == 0)
                    {
                        //Simple single image entire width
                        $middle = $this->getBarMiddleMarkup($middle_bar_width, $daydiff, $middle_typename, $is_middle_estimated, $is_middle_pinned, $middle_problem_tooltip);
                        if($middle !== NULL)
                        {
                            $middle_parts[] = $middle;
                        }
                    } else {
                        $prob_end_dt = NULL;
                        sort($relevant_problem_periods);    //Make sure this is sorted by start date!
                        $expected_start_dt = $middle_startdate;
                        foreach($relevant_problem_periods as $oneinfo)
                        {
                            $prob_start_dt = $oneinfo['start_dt'];
                            $prob_end_dt = $oneinfo['end_dt'];
                            if($prob_start_dt > $expected_start_dt)
                            {
                                //Create chunk that is okay
                                $chunk_start_dt = $expected_start_dt;
                                $chunk_end_dt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($prob_start_dt, -1);
                                $chunk_typename = $typename;
                                $chunk_days = UtilityGeneralFormulas::getDayCountInDateRange($chunk_start_dt, $chunk_end_dt);
                                if($chunk_start_dt == $chunk_end_dt)
                                {
                                    $chunk_tooltip = "OK on $chunk_start_dt";
                                } else {
                                    $chunk_tooltip = "OK from $chunk_start_dt to $chunk_end_dt";
                                }
                                $middle_bar_chunk_width = $this->getBarMiddleChunkPixelCount($chunk_start_dt, $chunk_end_dt);
                                $middle_chunk_markup = $this->getBarMiddleMarkup($middle_bar_chunk_width, $chunk_days, $chunk_typename, $is_middle_estimated, $is_middle_pinned, $chunk_tooltip);
                                $middle_parts[] = $middle_chunk_markup;
                            }
                            //Create chunk that is bad
                            if($prob_start_dt < $middle_startdate)
                            {
                                $chunk_start_dt = $middle_startdate;
                            } else {
                                $chunk_start_dt = $prob_start_dt;
                            }
                            if($prob_end_dt > $middle_enddate)
                            {
                                $chunk_end_dt = $middle_enddate;
                            } else {
                                $chunk_end_dt = $prob_end_dt;
                            }
                            if($chunk_end_dt >= $chunk_start_dt)
                            {
                                $chunk_typename = 'bad';
                                $chunk_tooltip = $oneinfo['tooltip'];
                                $chunk_days = UtilityGeneralFormulas::getDayCountInDateRange($chunk_start_dt, $chunk_end_dt);
                                $middle_bar_chunk_width = $this->getBarMiddleChunkPixelCount($chunk_start_dt, $chunk_end_dt);
                                $middle_chunk_markup = $this->getBarMiddleMarkup($middle_bar_chunk_width, $chunk_days, $chunk_typename, $is_middle_estimated, $is_middle_pinned, $chunk_tooltip);
                                $middle_parts[] = $middle_chunk_markup;
                                $expected_start_dt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($chunk_end_dt, 1);
                            }
                            if(empty($first_problem_period))
                            {
                                $first_problem_period = $oneinfo;
                            }
                            $last_problem_period = $oneinfo;
                        }
                        if($prob_end_dt < $middle_enddate)
                        {
                            //Create chunk that is okay
                            if(empty($prob_end_dt))
                            {
                                $chunk_start_dt = $middle_startdate;
                            } else {
                                $chunk_start_dt = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($prob_end_dt, 1);
                            }
                            $chunk_end_dt = $middle_enddate;
                            $chunk_typename = $typename;
                            $chunk_days = UtilityGeneralFormulas::getDayCountInDateRange($chunk_start_dt, $chunk_end_dt);
                            if($chunk_start_dt == $chunk_end_dt)
                            {
                                $chunk_tooltip = "OK on $chunk_start_dt";
                            } else {
                                $chunk_tooltip = "OK from $chunk_start_dt to $chunk_end_dt";
                            }
                            $middle_bar_chunk_width = $this->getBarMiddleChunkPixelCount($chunk_start_dt, $chunk_end_dt);
                            $middle_chunk_markup = $this->getBarMiddleMarkup($middle_bar_chunk_width, $chunk_days, $chunk_typename, $is_middle_estimated, $is_middle_pinned, $chunk_tooltip);
                            //$middle_chunks_ar[] = array('typename'=>$typename,'sdt'=>$chunk_start_dt,'edt'=>$chunk_end_dt,'tooltip'=>'','markup'=>$middle_chunk_markup);
                            $middle_parts[] = $middle_chunk_markup;
                        }
                    }
                    if(!empty($first_problem_period))
                    {
                        $oneinfo = $first_problem_period;
                        $prob_start_dt = $oneinfo['start_dt'];
                        $prob_end_dt = $oneinfo['end_dt'];
                        if($startdate >= $prob_start_dt && $startdate <= $prob_end_dt)
                        {
                            $start_problem_tooltip = trim("$start_problem_tooltip {$oneinfo['tooltip']}");
                            $start_typename = 'bad';
                        }
                    }
                    $parts[] = $this->getBarStartMarkup($startdate, $start_typename, $is_startdate_estimated, $is_startdate_pinned, $start_problem_tooltip);
                    foreach($middle_parts as $onepart)
                    {
                        $parts[] = $onepart;
                    }
                    if(!empty($last_problem_period))
                    {
                        $oneinfo = $last_problem_period;
                        $prob_start_dt = $oneinfo['start_dt'];
                        $prob_end_dt = $oneinfo['end_dt'];
                        if($enddate >= $prob_start_dt && $enddate <= $prob_end_dt)
                        {
                            $end_problem_tooltip = trim("$end_problem_tooltip {$oneinfo['tooltip']}");
                            $end_typename = 'bad';
                        }
                    }
                    if($middle_bar_width < 0)
                    {
                        $end_problem_tooltip = trim("$end_problem_tooltip (this icon is not in ideal position due to small display space)");
                    }
                    $parts[] = $this->getBarEndMarkup($enddate, $end_typename, $is_enddate_estimated, $is_enddate_pinned, $end_problem_tooltip);
                }
                if(!empty($moreclassnames))
                {
                    $parts[] = "</span>";
                }
            }
        }
        if(empty($final_tooltip))
        {
            if($daydiff == 1)
            {
                $final_tooltip = "1 day";
            } else {
                $final_tooltip = "$daydiff total days";
            }
        }
        return "<span class='gantt-bar' title='$final_tooltip'>" . implode("",$parts) . "</span>";
    }
}
