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

/**
 * This class returns confidence scores
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ConfidenceScores
{
    const BUFFERED_DAYS_THRESHHOLD = 5;
    const BUFFERED_HOURS_RATIO_PLENTY_THRESHHOLD = 1.5;
    const BUFFERED_HOURS_RATIO_MIN_THRESHHOLD = .25;
    const BUFFERED_HOURSPERDAY_MAX_THRESHHOLD = 1;
    const TOO_MUCH_FTE_OVERAGE_THRESHHOLD = 2;
    
    /**
     * @deprecated start NOT using this!!!!!!!!!!!!!!
     */
    public function getFTEOverageApproximatedBySimpleHourCount($avail_hours_until_due_date, $days_until_due_date=-1, $planned_FTE=1
            , $remaining_effort_hours=NULL, $onefte_hours_per_day=8
            , &$logic=NULL)
    {
        if(-1 == $days_until_due_date)
        {
            throw new \Exception("Missing required days_until_due_date parameter!");
        }
        if($logic === NULL)
        {
            $logic = [];
        }
        if($avail_hours_until_due_date <= 0 || empty($remaining_effort_hours))
        {
            //Cannot compute
            $overage = NULL;
            $logic[] = 'cannot compute';
        } else {
            if(empty($days_until_due_date))
            {
                $overage = NULL;
                $logic[] = 'cannot compute';
            } else {
                $overhours = $remaining_effort_hours - $avail_hours_until_due_date;
                if($overhours <= 0)
                {
                    $overage = 0;
                    $logic[] = 'plenty FTE';    
                } else {
                    $needed_hoursperday = $remaining_effort_hours / $days_until_due_date;
                    $needed_fteperday = $needed_hoursperday / $onefte_hours_per_day;
                    $overage = $needed_fteperday - $planned_FTE;
                    $logic[] = 'computed';
                }
            }
        }
        return $overage;
    }
    
    private function getAlertImpactInsightFactor($alerts_ar, $keyword, &$logic_ar=NULL)
    {
        if($keyword == 'warning')
        {
            $impact_denom = 10;
            $mf = .1;
        } else
        if($keyword == 'error')
        {
            $impact_denom = 5;
            $mf = .5;
        } else {
            throw new \Exception("Missing valid keyword! (got '$keyword')");
        }
        if(!isset($alerts_ar['detail'][$keyword]))
        {
            $issue_count = 0;
        } else {
            $issue_count = count($alerts_ar['detail'][$keyword]);
        }
        if($issue_count == 0)
        {
            $issue_impact_insight_factor = 100;
        } else {
            if($logic_ar === NULL)
            {
                $logic_ar = [];
            }
            $local_logic_details = [];
            $local_impact_types = [];
            if(!empty($alerts_ar['type_map'][$keyword]))
            {
                $type_map = $alerts_ar['type_map'][$keyword];
                $local_logic_details[] = array('summary'=>'AAAAA','detail'=>"BBBBBBB testing at $keyword");
                $issue_impact_insight_factor = 100;
                foreach($type_map as $onetype)
                {
                    $local_impact_types[] = $onetype;
                    $issue_impact_insight_factor = $issue_impact_insight_factor - ($issue_impact_insight_factor / $impact_denom);
                    $summary_msg = "detected $keyword $onetype";
                    $detail_msg = "detected $keyword $onetype thus $issue_impact_insight_factor";
                    $local_logic_details[] = array('summary'=>$summary_msg,'detail'=>$detail_msg);
                    $local_logic_details[] = array('summary'=>'testingXXXXX','detailY'=>"TTTTTTT testing at $keyword");
                }
            }
            $issue_impact_insight_factor -= ($issue_count-1) * $mf;
            $issue_impact_insight_factor = max(1, $issue_impact_insight_factor);  //Dont let it go to zero here.
            $it_txt = implode(',', $local_impact_types);
            if($issue_count > 1)
            {
                $summary_msg = "$issue_count {$keyword}s";
            } else {
                $summary_msg = "1 $keyword";
            }
            $pf = $issue_impact_insight_factor / 100;
            $logic_ar[] = array('summary'=>$summary_msg,'detail'=>"Alert p factor of $pf because $summary_msg ($it_txt)");
        }
        $thisp = $issue_impact_insight_factor / 100;
        return $thisp;
        
    }
    
    private function getWarningsImpactFactor($alerts_ar, &$logic_ar=NULL)
    {
        return $this->getAlertImpactInsightFactor($alerts_ar,'warning',$logic_ar);
    }
    
    private function getErrorsImpactFactor($alerts_ar, &$logic_ar=NULL)
    {
        return $this->getAlertImpactInsightFactor($alerts_ar,'error',$logic_ar);
    }
    
    /**
     * Compute the scf we show for a leaf node
     */
    public function getSCF4Leaf($wdetail, $pui, $status_detail, &$logic_ar=NULL)
    {
        if(empty($status_detail))
        {
            throw new \Exception("Missing required status detail for winfo=" . print_r($wdetail,TRUE));
        }
        if($logic_ar === NULL)
        {
            $logic_ar = [];
        }
        $p = NULL;  //Declare before the TRY CATCH on purpose
        try
        {
            $wid = $wdetail['id'];
            $limit_edt = !empty($wdetail['limit_edt']) ? $wdetail['limit_edt'] : NULL;
            if($status_detail['terminal_yn'] == 1)
            {
                //For terminal the influence from native probability of completion is removed
                $native_scf = NULL;
            } else {
                $native_scf = $wdetail['ot_scf'];
            }
            $status_scf = $status_detail['ot_scf'];
            $wid_intervals = $pui->getIntervals4OneWorkitem($wid, $limit_edt);
            $wis = $wid_intervals['summary'];
            $sdt = $wis['sdt'];
            $edt = $wis['edt'];

            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d",$now_timestamp);
            $due_factor = $this->getDueDateFactor($today_dt, $sdt, $edt, $status_detail, $limit_edt);
            
            $fit_quality_score = $this->getFitQualScoreFactor($due_factor, $wis);
            $alerts_ar = empty($wdetail['alerts']) ? [] : $wdetail['alerts'];
            $warnings_impact_factor = $this->getWarningsImpactFactor($alerts_ar,$logic_ar);
            $errors_impact_factor = $this->getErrorsImpactFactor($alerts_ar,$logic_ar);

            $pbundle = $this->getComputedOTSPBundle($due_factor,$fit_quality_score,$native_scf,$status_scf,$warnings_impact_factor,$errors_impact_factor);
            $p = $pbundle['p'];
            $summary_msg = $pbundle['msg']['summary'];
            $detail_msg = $pbundle['msg']['detail'];
            $logic_ar[] = array('summary'=>$summary_msg,'detail'=>$detail_msg);
        } catch (\Exception $ex) {
            $p=0;
            $logic_ar[] = array('summary'=>"Error computing LEAF#$wid:" . $ex->getMessage(),'detail'=>$ex);
        }
        
        return $p;  //Return AFTER the TRY CATCH on purpose
    }

    private function getDueDateFactor($today_dt, $sdt, $edt, $status_detail, $limit_edt=NULL, $ant_min_end_dt=NULL)
    {
        if($status_detail['terminal_yn'] != 1 && $today_dt > $edt || (!empty($limit_edt) && $today_dt > $limit_edt))
        {
            //Due date is past.
            $due_factor = 0;
        } else {
            //Not past the due date.
            $due_factor = 1;
            if($status_detail['terminal_yn'] != 1)
            {
                //Not complete, so check dates
                if($sdt <= $today_dt && !$status_detail['workstarted_yn'])
                {
                    //Has not started yet, ding them a bit.
                    $due_factor -= .1;
                }
            }
            
            if($status_detail['happy_yn'] === 0)    //EXACT CHECK FOR ZERO!!!!
            {
                //Not a happy status, ding them significantly
                $due_factor -= .25;
            } else if($status_detail['happy_yn'] === 1) {
                //Happy bump
                $add = (1 - $due_factor) / 2;
                $due_factor += $add;
            }
            
            if(!empty($limit_edt))
            {
                if($sdt > $limit_edt)
                {
                    $due_factor *= .25;
                } else
                if($edt > $limit_edt)
                {
                    $due_factor *= .5;
                }
            }
            
            if(!empty($ant_min_end_dt))
            {
                if($edt < $ant_min_end_dt)
                {
                    //Ends before the ant ends!
                    $due_factor *= .01;
                } else
                if($sdt < $ant_min_end_dt)
                {
                    //Starts before the ant ends but end is okay!
                    $due_factor *= .90;
                } else
                if($sdt == $ant_min_end_dt)
                {
                    //Starts same day the ant ends, so give normal impact!
                    $due_factor *= .95;
                } else
                if($sdt > $ant_min_end_dt)
                {
                    //Starts after the ant ends, so give a little boost!
                    $due_factor *= .96;
                }
            }
        }
        return $due_factor;
    }
    
    /**
     * TODO -- Refactor this to use the UTILIZATION module instead
     */
    private function getFitQualScoreFactor($due_factor, $wis)
    {
        if($wis['total']['reh'] <= .001)
        {
            //TODO just check the dates
            $fit_quality_score = 1;
        } else {
            if($due_factor <= .001)
            {
                $fit_quality_score = 0;
            } else {
                //See how much wiggle room there is
                $total_busy = round($wis['upct']['total'],4);
                if($total_busy > 1)
                {
                    $factor = max(.1, 1.2 - $total_busy);
                } else {
                    if($total_busy < .5) 
                    {
                        $busy_factor = $total_busy/100;
                    } else {
                        $busy_factor = $total_busy/10;
                    }
                    $distraction_factor = 1 + (10 * ($wis['upct']['otherwids']));
                    $factor = 1 - $busy_factor * $distraction_factor;    
                }
                //$total_busy is currently flakey; do NOT make this zero just because of its value
                $fit_quality_score = max(.1, $factor);
            }
        }   
        return $fit_quality_score;
    }
    
    /**
     * Compute the scf we show for a non-leaf node
     */
    public function getSCF4NonLeaf($wdetail, $pui, $ant_info=NULL, $status_detail=NULL, &$logic_ar=NULL)
    {
        if(empty($status_detail))
        {
            throw new \Exception("Missing required status detail!");
        }
        if($logic_ar === NULL)
        {
            $logic_ar = [];
        }
        $p = NULL;  //Declare OUTSIDE the try catch on purpose!
        
        try
        {

            $wid = $wdetail['id'];
            $limit_edt = !empty($wdetail['limit_edt']) ? $wdetail['limit_edt'] : NULL;
            if($status_detail['terminal_yn'] == 1)
            {
                //For terminal the influence from native probability of completion is removed
                $native_scf = NULL;
            } else {
                $native_scf = $wdetail['ot_scf'];
            }
            $status_scf = $status_detail['ot_scf'];
            $wid_intervals = $pui->getIntervals4OneWorkitem($wid, $limit_edt);
            $wis = $wid_intervals['summary'];
            $sdt = $wis['sdt'];
            $edt = $wis['edt'];

            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d",$now_timestamp);
            $ant_min_end_dt = !empty($ant_info['min_end_dt']) ? $ant_info['min_end_dt'] : NULL;
            $due_factor = $this->getDueDateFactor($today_dt, $sdt, $edt, $status_detail, $limit_edt, $ant_min_end_dt);
            $fit_quality_score = $this->getFitQualScoreFactor($due_factor, $wis);
            //$importance_score = $this->getImportanceScoreFactor(TODO);

            $alerts_ar = empty($wdetail['alerts']) ? [] : $wdetail['alerts'];
            $warnings_impact_factor = $this->getWarningsImpactFactor($alerts_ar,$logic_ar);
            $errors_impact_factor = $this->getErrorsImpactFactor($alerts_ar,$logic_ar);
            if(empty($ant_info['zero_otsp_wids']))
            {
                $zero_otsp_ants_ar = [];
            } else {
                $zero_otsp_ants_ar = $ant_info['zero_otsp_wids'];
            }
            $ant_p = $ant_info['effective_otsp'];
            $count_zero_otsp_ants_ar = count($zero_otsp_ants_ar);
            if($count_zero_otsp_ants_ar == 0)
            {
                $zero_msg = "";
                $pbundle = $this->getComputedOTSPBundle($due_factor,$fit_quality_score,$native_scf,$status_scf,$warnings_impact_factor,$errors_impact_factor,$ant_p);
                $p = $pbundle['p'];
                $summary_msg = $pbundle['msg']['summary'];
                $detail_msg = $pbundle['msg']['detail'];
                
            } else {
                if($count_zero_otsp_ants_ar == 1)
                {
                    $zero_msg = "1 antecedant with ZERO OTSP value";
                } else {
                    $zero_msg = "there are $count_zero_otsp_ants_ar antecedants with ZERO OTSP value";
                }
                $p = 0;
                $summary_msg = "ZERO ANT";
                $detail_msg = "NONLEAF p=$p because $zero_msg";
            }
            $logic_ar[] = array('summary'=>$summary_msg,'detail'=>$detail_msg);
        } catch (\Exception $ex) {
            $p=0;
            $logic_ar[] = array('summary'=>"Error computing NONLEAF#$wid:" .$ex->getMessage(),'detail'=>$ex);
        }
        return $p;  //Return OUTSIDE the try catch on purpose
    }
    
    /**
     * Compute the ant_scf we feed into dependent node calculations
     */
    public function getSCF2FeedDependents($wdetail, $pui, $ant_info=NULL, $status_detail=NULL, &$logic_ar=NULL)
    {
        if(empty($status_detail))
        {
            throw new \Exception("Missing required status detail!");
        }
        $p = $this->getSCF4NonLeaf($wdetail, $pui, $ant_info, $status_detail, $logic_ar);
        if($p >= .0001)
        {
            $risk = 1 - $p;
            //Reduce the risk for dependent calculations
            $p = $p + ($risk / 2);
        }
        return $p;
    }
    
    private function getComputedOTSPBundle($due_factor,$fit_quality_score,$native_scf,$status_scf,$warnings_impact_factor,$errors_impact_factor,$ant_p=NULL)
    {
        $tipinfo_ar = [];
        $done_yn = 0;
        if($status_scf == 1)
        {
            //This means the work is completed successfully already.
            $tipinfo_ar[] = "successful completion status";
            $thisnode_p = 1;
            $done_yn = 1;
        } else if($status_scf == 0) {    
            $tipinfo_ar[] = "zero success status";
            $thisnode_p = 0;
            $done_yn = 1;
        } else {
            if($fit_quality_score == 0)
            {
                $tipinfo_ar[] = "check for resource utilization overages";
            }
            if($native_scf == 0)
            {
                $tipinfo_ar[] = "zero declared confidence of success";
            }
            if($due_factor == 0)
            {
                $tipinfo_ar[] = "due date timing";
            }
            
            if($fit_quality_score === 0 || $due_factor === 0 || $status_scf === 0 || $errors_impact_factor === 0)
            {
                $thisnode_p = 0;
                $fnm_ar = [];
                if($fit_quality_score === 0)
                {
                    $fnm_ar[] = 'fit score';
                }
                if($due_factor === 0)
                {
                    $fnm_ar[] = 'due factor';
                }
                if($status_scf === 0)
                {
                    $fnm_ar[] = 'status scf';
                }
                if($errors_impact_factor === 0)
                {
                    $fnm_ar[] = 'eif';
                }
                $tipinfo_ar[] = "zero p because zero " . implode(' and ', $fnm_ar);
            } else {
                if($native_scf == NULL)
                {
                    $zerocheck = $fit_quality_score * ($due_factor + $status_scf + $warnings_impact_factor + $errors_impact_factor);
                    if($zerocheck < .001)
                    {
                        $thisnode_p = 0;
                    } else {
                        $thisnode_p = $fit_quality_score * ($due_factor + $status_scf + $warnings_impact_factor + $errors_impact_factor)/4;
                    }
                } else {
                    $zerocheck = $fit_quality_score * ($due_factor + $native_scf + $status_scf + $warnings_impact_factor + $errors_impact_factor);
                    if($zerocheck < .001)
                    {
                        $thisnode_p = 0;
                    } else {
                        $thisnode_p = $fit_quality_score * ($due_factor + $native_scf + $status_scf + $warnings_impact_factor + $errors_impact_factor)/5;
                    }
                }
            }
        }
        
        if(count($tipinfo_ar) == 0)
        {
            $tipinfo_markup = '';
        } else {
            $tipinfo_markup = ' insight:' . implode(' and ', $tipinfo_ar);
        }
        
        if($ant_p === NULL)
        {
            $typename = 'LEAF';
            $p = $thisnode_p;
            if($done_yn)
            {
                $summary_msg = "done";
                $detail_msg = "p because of completion status";
            } else {
                $summary_msg = "$p = $thisnode_p";
                if($native_scf == NULL)
                {
                    $detail_msg = "$typename p=$p because $fit_quality_score x ($due_factor + $status_scf + $warnings_impact_factor + $errors_impact_factor)/4 $tipinfo_markup";
                } else {
                    $detail_msg = "$typename p=$p because $fit_quality_score x ($due_factor + $native_scf + $status_scf + $warnings_impact_factor + $errors_impact_factor)/5 $tipinfo_markup";
                }
            }
        } else {
            $typename = 'NONLEAF';
            if($done_yn)
            {
                $p = $thisnode_p;
                $summary_msg = "done";
                $detail_msg = "p because of completion status";
            } else {
                $p = $ant_p * $thisnode_p;
                $summary_msg = "$p = $ant_p * $thisnode_p";
                if($native_scf == NULL)
                {
                    $detail_msg = "$typename p=$p because ($ant_p x $thisnode_p) where local p is $fit_quality_score x ($due_factor + $status_scf + $warnings_impact_factor + $errors_impact_factor)/4 $tipinfo_markup";
                } else {
                    $detail_msg = "$typename p=$p because ($ant_p x $thisnode_p) where local p is $fit_quality_score x ($due_factor + $native_scf + $status_scf + $warnings_impact_factor + $errors_impact_factor)/5 $tipinfo_markup";
                }
            }
        }
        
        $bundle = [];
        $bundle['p'] = $p;
        $bundle['msg']['summary'] = $summary_msg;
        $bundle['msg']['detail'] = $detail_msg;
        return $bundle;
    }
}
