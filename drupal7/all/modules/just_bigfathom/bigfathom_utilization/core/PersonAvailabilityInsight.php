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

namespace bigfathom_utilization;

/**
 * Smart object for quickly checking person availability
 *
 * @author Frank
 */
class PersonAvailabilityInsight
{
    private $m_objectid = NULL;
    
    protected $m_personid = NULL;
    protected $m_projects2ignore = NULL;
    protected $m_initialized_yn = FALSE;
    
    protected $m_detail_ar = NULL;
    protected $m_relevant_work_busy_mapper = NULL;
    protected $m_static_work_busy_mapper = NULL;
    protected $m_uniqueid_relevant_work_busy_mapper = NULL;
    protected $m_uniqueid_static_work_busy_mapper = NULL;
    
    protected $m_oMasterDailyDetail = NULL;
    protected $m_local_dailydetail_overrides = NULL;    //Just the local stuff
    protected $m_local_relevant_workitem_cache = NULL;  //Just work that this person owns
    protected $m_local_static_workitem_cache = NULL;    //Just work that this person owns
    
    protected $m_computed_static_busy_yn = NULL;
    protected $m_computed_relevant_busy_yn = NULL;
    protected $m_computed_relevant_wid2range = NULL;
    
    protected $m_computed_relevant_overages = NULL;
    protected $m_embed_overages_yn = NULL;
    
    protected $m_today_dt = NULL;
    protected $m_tomorrow_dt = NULL;
    protected $m_min_checked_dt = NULL;
    protected $m_max_checked_dt = NULL;
    
    protected $m_static_min_dt = NULL;
    protected $m_static_max_dt = NULL;
    
    protected $m_computing_relevant_busy_yn = NULL;
    protected $m_computing_static_busy_yn = NULL;
    
    public function __construct($personid, $oMasterDailyDetail, $flags=NULL, $embed_overages_yn=1)
    {
        if(empty($personid))
        {
            throw new \Exception("Missing required personid!");
        }
        if(empty($oMasterDailyDetail))
        {
            throw new \Exception("Missing required oMasterDailyDetail!");
        }
        
        $this->m_objectid = "PAI" . (10000 - mt_rand (1000,9999)). "::" . microtime(TRUE);
        
        $this->m_uniqueid_relevant_work_busy_mapper = 0;
        $this->m_uniqueid_static_work_busy_mapper = 0;
        
        $this->m_oMasterDailyDetail = $oMasterDailyDetail;
        $this->m_personid = $personid;
        $this->m_projects2ignore = !empty($flags['projects2ignore']) ? $flags['projects2ignore'] : [];

        $this->m_local_dailydetail_overrides = [];
        $this->m_local_relevant_workitem_cache = [];
        $this->m_local_static_workitem_cache = [];
        
        $this->m_detail_ar = [];
        
        $this->m_static_work_busy_mapper = [];
        $this->m_relevant_work_busy_mapper = [];
        
        $this->m_computed_relevant_wid2range = [];
        $this->m_computed_relevant_overages = [];
        
        $this->m_computed_static_busy_yn = 0;
        $this->m_computed_relevant_busy_yn = 0;
        
        $this->m_today_dt = $this->m_oMasterDailyDetail->getTodayDate();
        $this->m_tomorrow_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_today_dt, 1);
        
        $this->m_embed_overages_yn = $embed_overages_yn;
        
        $this->m_computing_relevant_busy_yn=0;
        $this->m_computing_static_busy_yn=0;
    }
    
    public function getThisInstanceID()
    {
        return $this->m_objectid;
    }
    
    /**
     * Load workitems that intersect or are contained by these dates and are
     * not in the m_projects2ignore list.
     */
    public function loadStaticWorkitems($start_dt, $end_dt)
    {
        try
        {
            if($start_dt > $end_dt)
            {
                throw new \Exception("We have start date larger than end date! ([$start_dt, $end_dt] for personid={$this->m_personid})");
            }
            $daycount = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt)+1;
            
            //First load the available workdays detail in the range
            $include_daily_detail=TRUE;
            $today_dt = $this->m_oMasterDailyDetail->getTodayDate();
            
            //$raw = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($start_dt, $end_dt, $this->m_personid, $include_daily_detail, $today_dt);
            //detail_ar = $raw['daily_detail'];
            
            $detail_ar = [];
            $attempt_count = 0;
            $local_load_start_dt=$start_dt;
            $local_load_end_dt=$end_dt;
            $fetch_detail = empty($this->m_detail_ar[$local_load_start_dt]) || empty($this->m_detail_ar[$local_load_end_dt]);
            while($fetch_detail)
            {
                //Work around odd bug by running query until we get all the dates
                $raw = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($local_load_start_dt, $local_load_end_dt, $this->m_personid, $include_daily_detail, $today_dt);
                $detail_ar = $raw['daily_detail'];
                foreach($detail_ar as $dt=>$detail)
                {
                    $this->m_detail_ar[$dt] = $detail;
                }
                if($attempt_count > 10)
                {
                    throw new \Exception("Attempted to loadStaticWorkitems($start_dt, $end_dt) person#{$this->m_personid} more than $attempt_count times!");
                }
                end($this->m_detail_ar);         // move the internal pointer to the end of the array
                $reference_dt = key($this->m_detail_ar);  // fetches the key of the element pointed to by the internal pointer
                if($reference_dt < $local_load_end_dt)
                {
                    //Continue with a new chunk
                    $local_load_start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($reference_dt, 1);
                    if($attempt_count>1)
                    {
                        //OLDTry bumping the end date too because there may be issue getting current end date
                        //OLD$local_load_end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($local_load_end_dt, 1);
                        error_log("WARNING: looping loadStaticWorkitems($start_dt, $end_dt) person#{$this->m_personid} attempt_count=$attempt_count now at [$local_load_start_dt,$local_load_end_dt]");
                    }
                }
                $attempt_count++;
                $fetch_detail = empty($this->m_detail_ar[$local_load_start_dt]) || empty($this->m_detail_ar[$local_load_end_dt]);
            }
                
            /*
            foreach($detail_ar as $dt=>$detail)
            {
                $this->m_detail_ar[$dt] = $detail;
                $daycount++;
            }
            */
            if($daycount > 0)
            {
                ksort($this->m_detail_ar);
                $justkeys = array_keys($this->m_detail_ar);
                $this->m_min_detail_date = $justkeys[0];
                $lastidx = count($justkeys)-1;
                $this->m_max_detail_date = $justkeys[$lastidx];
            } else {
                drupal_set_message("Did not find any work days for date range [$start_dt, $end_dt] for person#{$this->m_personid}",'warning');
                $this->m_min_detail_date = NULL;
                $this->m_max_detail_date = NULL;
            }
            //error_log("INFO: INSIGHT loadStaticWorkitems($start_dt, $end_dt) MINMAX=[{$this->m_min_detail_date},{$this->m_max_detail_date}] for personid={$this->m_personid}");
            
            //Load the workitems in the range
            $sql = "SELECT id as wid, owner_projectid,"
                    . " COALESCE(w.actual_start_dt, w.planned_start_dt) as sdt,"
                    . " COALESCE(w.actual_end_dt, w.planned_end_dt) as edt,"
                    . " remaining_effort_hours as reh"
                    . " FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . ' w'
                    . " WHERE owner_personid={$this->m_personid} AND active_yn=1";
            $sql .= " AND (('$start_dt' >= COALESCE(w.actual_start_dt, w.planned_start_dt) and '$start_dt' <= COALESCE(w.actual_end_dt, w.planned_end_dt))";
            $sql .= "   or ('$end_dt' >= COALESCE(w.actual_start_dt, w.planned_start_dt) and '$end_dt' <= COALESCE(w.actual_end_dt, w.planned_end_dt))";
            $sql .= "   or ('$start_dt' <= COALESCE(w.actual_start_dt, w.planned_start_dt) and '$end_dt' >= COALESCE(w.actual_end_dt, w.planned_end_dt))";
            $sql .= "   )";
            if(!empty($this->m_projects2ignore))
            {
                if(is_array($this->m_projects2ignore) && count($this->m_projects2ignore) > 0)
                {
                    $sNOT_IN_IDS_TXT = implode(",", $this->m_projects2ignore);
                    $sql .= " AND owner_projectid NOT IN ($sNOT_IN_IDS_TXT)";
                } else {
                    $pidclean = trim($this->m_projects2ignore);
                    $sql .= " AND owner_projectid<>$pidclean";
                }
            }
            $result = db_query($sql);
            $min_dt = NULL;
            $max_dt = NULL;
            while($record = $result->fetchAssoc())
            {
                //$wid = $record['wid'];
                $start_dt = $record['sdt'];
                $end_dt = $record['edt'];
                if($min_dt == NULL || $min_dt > $start_dt)
                {
                    $min_dt = $start_dt;
                }
                if($max_dt == NULL || $max_dt < $end_dt)
                {
                    $max_dt = $end_dt;
                }
                if(!empty($start_dt) && $start_dt <= $end_dt)
                {
                    $record['day_count'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
                } else {
                    $record['day_count'] = NULL;
                }
                $this->addStaticWorkitem($record);
                //$this->m_local_static_workitem_cache[$wid] = $record;
            }
            if($this->m_static_min_dt > $min_dt)
            {
                $this->m_static_min_dt = $min_dt;
            }
            if(empty($this->m_static_max_dt) || $this->m_static_max_dt < $max_dt)
            {
                $this->m_static_max_dt = $max_dt;
            }
            //error_log("INFO: Finished loadStaticWorkitems($start_dt, $end_dt) work range=[$min_dt,$max_dt]");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Sooner and smaller date ranges have lower rank value
     */
    private function getLayerRankScore($winfo)
    {
        $first_dt = $this->m_oMasterDailyDetail->getFirstDate();
        $sdt = $winfo['sdt'];
        $edt = $winfo['edt'];
        //\bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($sdt, $edt)
        if($first_dt <= $edt && $winfo['day_count'] !== NULL)// !empty($sdt) && !empty($edt))
        {
            $simple_day_count = 1 + \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($first_dt, $edt);
            $score = 1 + $simple_day_count 
                     * (1 + (1+$winfo['day_count']));
        } else {
            $score = 0;
        }
        return $score;
    }
    
    public function getAllLoadedStaticWorkitems()
    {
        try
        {
            return $this->m_local_static_workitem_cache;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function addStaticWorkitem($winfo)
    {
        $this->m_computed_static_busy_yn = 0;
        $wid = $winfo['wid'];
        if(!isset($winfo['day_count']))
        {
            $start_dt = $winfo['sdt'];
            $end_dt = $winfo['edt'];
            if(!empty($start_dt) && $start_dt <= $end_dt)
            {
                $winfo['day_count'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
            } else {
                $winfo['day_count'] = NULL;
            }
        }
        $this->m_local_static_workitem_cache[$wid] = $winfo;   
        //error_log("INFO: Finished addStaticWorkitem for wid#$wid");
    }
    
    public function getRelevantWorkitemRecordIDs()
    {
        return array_keys($this->m_local_relevant_workitem_cache);
    }
    
    public function setRelevantWorkitemRecord($wid, $winfo)
    {
        try
        {
            $this->m_computed_relevant_busy_yn = 0;
            $this->m_computed_static_busy_yn = 0;   //TODO -- embed some smarts to clear only if needed!
            if(!array_key_exists('sdt',$winfo))
            {
                throw new \Exception("Missing required sdt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!array_key_exists('edt',$winfo))
            {
                throw new \Exception("Missing required edt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!empty($winfo['sdt']) && strlen($winfo['sdt']) !== 10)
            {
                throw new \Exception("Bad format for required sdt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!empty($winfo['edt']) && strlen($winfo['edt']) !== 10)
            {
                throw new \Exception("Bad format for required edt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!array_key_exists('reh',$winfo))
            {
                throw new \Exception("Missing required reh in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(empty($winfo['owner_personid']))
            {
                throw new \Exception("Missing owner_personid in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!isset($winfo['day_count']))
            {
                $start_dt = $winfo['sdt'];
                $end_dt = $winfo['edt'];
                if(!empty($start_dt) && $start_dt <= $end_dt)
                {
                    $winfo['day_count'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
                } else {
                    $winfo['day_count'] = NULL;
                }
            }
            $this->m_local_relevant_workitem_cache[$wid] = $winfo;
        //drupal_set_message("LOOK <strong>CCC PUT wid#$wid</strong> for autofill stuff --- CLEAN=" . print_r($winfo,TRUE));
        
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function clearRelevantWorkitemCache()
    {
        $this->m_local_relevant_workitem_cache = [];
    }
    
    public function __toString()
    {
        $nice_text_ar = [];
        $nice_text_ar[] = "PersonAvailabilityInsight person#{$this->m_personid}";
        if(is_array($this->m_local_static_workitem_cache))
        {
            $static_wids = implode(", ", array_keys($this->m_local_static_workitem_cache));
            $nice_text_ar[] = "STATIC WIDS: " . $static_wids;
            $nice_text_ar[] = \bigfathom\DebugHelper::getNeatMarkup($this->m_local_static_workitem_cache,"STATIC workitems");
        }
        if(is_array($this->m_local_relevant_workitem_cache))
        {
            $relevant_wids = implode(", ", array_keys($this->m_local_relevant_workitem_cache));
            $nice_text_ar[] = "RELEVANT WIDS: " . $relevant_wids;
            $nice_text_ar[] = \bigfathom\DebugHelper::getNeatMarkup($this->m_local_relevant_workitem_cache,"RELEVANT workitems");
        }
        if(is_array($this->m_detail_ar))
        {
            $count_days = count($this->m_detail_ar);
            reset($this->m_detail_ar);
            $sdt = key($this->m_detail_ar);
            end($this->m_detail_ar);
            $edt = key($this->m_detail_ar);
            $nice_text_ar[] = "Daily date detail count=$count_days with sdt={$sdt} edt={$edt}";
        }
        
        $this->getAvailabilityDetailBundle();
        //$this->computeBusy();
        
        $ddb = $this->getTemplateDailyDetailBundle();
        //$nice_text_ar[] = \bigfathom\DebugHelper::getNeatMarkup($ddb,'Daily Detail of person#'.$this->m_personid);
        
        $nice_text = implode("\n<br>>>>", $nice_text_ar);
                
        return $nice_text;
    }    
    
    public function getRelevantWorkitemRecord($wid)
    {
        try
        {
            $this->computeBusy();
            if(!isset($this->m_local_relevant_workitem_cache[$wid]))
            {
                throw new \Exception("Did not find wid#$wid in relevant collection of person#{$this->m_personid}");
            }
            $winfo = $this->m_local_relevant_workitem_cache[$wid];
            $overage_hours = isset($this->m_computed_relevant_overages[$wid]) ? $this->m_computed_relevant_overages[$wid] : 0;
            $winfo['overage_yn'] = $overage_hours > 0 ? 1 : 0;
            $winfo['overage_hours'] = $overage_hours;
            return $winfo;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getLayeringSequence()
    {
        try
        {
            $sequence_ar = [];
            foreach($this->m_local_static_workitem_cache as $winfo)
            {
                $wid = $winfo['wid'];
                if(empty($wid))
                {
                    throw new \Exception("Missing required wid value in " . print_r($winfo,TRUE));
                }
                $lrscore = $this->getLayerRankScore($winfo);
                if(!isset($sequence_ar[$lrscore]))
                {
                    $sequence_ar[$lrscore] = [];
                }
                $sequence_ar[$lrscore][$wid] = 'S';
            }
            foreach($this->m_local_relevant_workitem_cache as $winfo)
            {
                $wid = $winfo['wid'];
                if(empty($wid))
                {
                    throw new \Exception("Cannot produce layering sequence because missing wid in " . print_r($winfo,TRUE));
                }
                $lrscore = $this->getLayerRankScore($winfo);
                if(!isset($sequence_ar[$lrscore]))
                {
                    $sequence_ar[$lrscore] = [];
                }
                $sequence_ar[$lrscore][$wid] = 'C';
            }
            ksort($sequence_ar);
            return $sequence_ar;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    //We compute this one and never clear it
    private function computeStaticBusy()
    {
        try
        {
            if($this->m_computing_static_busy_yn)
            {
                error_log("INFO: Attempt to restart computeStaticBusy for personid#{$this->m_personid}");
                return;
            }
            $this->m_computing_static_busy_yn = 1;
            $this->m_static_work_busy_mapper = [];
            $wid_nofit_reh_mapper = [];
            $sequence_ar = $this->getLayeringSequence();
            $dd_keys = array_keys($this->m_detail_ar);

            $found_time_tracker = 0;
            $totalbusy = 0;
            $wid2offsets = [];
            $wid2days_mapper = [];
            foreach($sequence_ar as $lrscore=>$members)
            {
                if($lrscore > 0)
                {
                    foreach($members as $wid=>$type_code)
                    {
                        //Only process static ones
                        if($type_code == 'S')
                        {
                            $winfo = $this->m_local_static_workitem_cache[$wid];
                            $reh = $winfo['reh'];
                            $real_reh = $winfo['reh'] > .001 ? $winfo['reh'] : 0;
                            //if(!isset($wid2days_mapper[$wid]))
                            //{
                            //    $wid2days_mapper[$wid]['days'] = [];
                            //}

                            $totalbusy+=$real_reh;
                            if($real_reh > 0)
                            {
                                $sdt = $winfo['sdt'];
                                $edt = $winfo['edt'];
                                if($sdt < $this->m_min_detail_date || $edt > $this->m_max_detail_date)
                                {
                                    //Beyond our insight, assume the work is 100 utilizing during the dates.
                                    $beyond_insight_yn = 1;
                                    if($sdt < $this->m_min_detail_date)
                                    {
                                        $offset_sdt = array_search($this->m_min_detail_date, $dd_keys);
                                    } else {
                                        $offset_sdt = array_search($sdt, $dd_keys);
                                    }
                                    if($edt > $this->m_max_detail_date)
                                    {
                                        $offset_edt = array_search($this->m_max_detail_date, $dd_keys);
                                    } else {
                                        $offset_edt = array_search($edt, $dd_keys);
                                    }
                                } else {
                                    $beyond_insight_yn = 0;
                                    $offset_sdt = array_search($sdt, $dd_keys);
                                    $offset_edt = array_search($edt, $dd_keys);
                                }
                                if($offset_sdt === FALSE || $offset_edt === FALSE)
                                {
                                    throw new \Exception("Did not find offsets [$offset_sdt,$offset_edt] for workitem#$wid in our loaded detail! (SDT:$sdt vs {$this->m_min_detail_date} and EDT: $edt vs {$this->m_max_detail_date}) winfo=" . print_r($winfo,TRUE));
                                }
                                $wid2offsets[$wid] = array(
                                    'offset_sdt'=>$offset_sdt,'offset_edt'=>$offset_edt
                                );
                                for($i=$offset_sdt; $i<=$offset_edt; $i++)
                                {
                                    $dt_key = $dd_keys[$i];
                                    if(!isset($this->m_static_work_busy_mapper[$dt_key]))
                                    {
                                        $this->m_static_work_busy_mapper[$dt_key] = [];
                                        $this->m_static_work_busy_mapper[$dt_key]['busy'] = 0;
                                        $this->m_static_work_busy_mapper[$dt_key]['wids'] = [];
                                        $this->m_static_work_busy_mapper[$dt_key]['debuging'] = [];
                                        $this->m_static_work_busy_mapper[$dt_key]['debuging'][] = "Initialized at wid#$wid";
                                    }
                                    $today_dd = $this->m_detail_ar[$dt_key];
                                    $available_hours_today = $today_dd['workhoursinday'] - $this->m_static_work_busy_mapper[$dt_key]['busy'];
                                    if($reh < $available_hours_today)
                                    {
                                        $workitem_busy_today = max($reh,0);
                                        $reh = 0;
                                    } else {
                                        $workitem_busy_today = $available_hours_today;
                                        $reh -= $workitem_busy_today;
                                    }
                                    if($workitem_busy_today > 0)
                                    {
                                        $this->m_static_work_busy_mapper[$dt_key]['busy'] += $workitem_busy_today;
                                        $this->m_static_work_busy_mapper[$dt_key]['wids'][$wid] = $workitem_busy_today;
                                        //$wid2days_mapper[$wid]['days'][$dt_key] = $workitem_busy_today;
                                        $this->m_static_work_busy_mapper[$dt_key]['debuging'][] = "added $workitem_busy_today from wid#$wid (availtoday=$available_hours_today reh=$reh)";
                                        if(!isset($wid2days_mapper[$wid]))
                                        {
                                            $wid2days_mapper[$wid] = [];
                                            $wid2days_mapper[$wid]['total_hours_mapped'] = 0;
                                            $wid2days_mapper[$wid]['days'] = [];
                                            $wid2days_mapper[$wid]['total_hours_mapped'] += $workitem_busy_today;
                                        }
                                        $wid2days_mapper[$wid]['total_hours_mapped'] += $workitem_busy_today;
                                        $wid2days_mapper[$wid]['days'][$dt_key] = $workitem_busy_today;
                                        $found_time_tracker += $workitem_busy_today;
                                    }
                                    if($reh <= 0)
                                    {
                                        break;
                                    }
                                }
                                if($beyond_insight_yn)
                                {
                                    $reh = 0;
                                }
                                if($reh > 0)
                                {
                                    $wid_nofit_reh_mapper[$wid] = $reh;
                                }
                            }
                        }
                    }
                }
            }
            ksort($this->m_static_work_busy_mapper);
            $this->m_computed_static_busy_yn = 1;
            $this->m_computing_static_busy_yn = 0;
            $this->m_computed_static_overages = $wid_nofit_reh_mapper;
            $this->m_uniqueid_static_work_busy_mapper++;
            
            if($this->m_embed_overages_yn && count($wid_nofit_reh_mapper)>0)
            {
                $this->embedStaticOverages($dd_keys, $wid_nofit_reh_mapper, $wid2days_mapper, $wid2offsets);
            }
            
        } catch (\Exception $ex) {
            $this->m_computing_static_busy_yn = 0;
            //drupal_set_message("ERROR Unable to compute static work busy map for personid#{$this->m_personid} because " . $ex, 'error');
            error_log("FAILED PAI computeStaticBusy for personid#{$this->m_personid} because " . $ex);
            throw $ex;
        }
    }
    
    //We need to be able to clear and recompute this one
    //TODO --- Enhance so that we can ADD to existing instead of recompute!
    private function computeRelevantBusy()
    {
        if($this->m_computing_relevant_busy_yn)
        {
            //Already computing it!
            error_log("INFO: Attempted to RESTART computeRelevantBusy for userid#{$this->m_personid}");            
            return;
        }
        $this->m_computing_relevant_busy_yn=1;
        try
        {
            if(!$this->m_computed_static_busy_yn)
            {
                $this->computeStaticBusy();
            }
            $this->m_relevant_work_busy_mapper = [];
            $wid_nofit_reh_mapper = [];
            $sequence_ar = $this->getLayeringSequence();
            $dd_keys = array_keys($this->m_detail_ar);
            
            $wid2range = [];
            if(count($this->m_local_relevant_workitem_cache)>0)
            {
                if(empty($sequence_ar) || count($sequence_ar)==0)
                {
                    $just_ids = array_keys($this->m_local_relevant_workitem_cache);
                    throw new \Exception("Empty layering sequence but expected content for " . print_r($just_ids,TRUE));           
                }
            }

            $totalbusy = 0;
            $wid2offsets = [];
            $wid2days_mapper = [];
            foreach($sequence_ar as $lrscore=>$members)
            {
                if($lrscore > 0)
                {
                    foreach($members as $wid=>$type_code)
                    {
                        //Only process relevant ones
                        if($type_code !== 'S')
                        {
                            $first_dt = NULL;
                            $last_dt = NULL;
                            $winfo = $this->m_local_relevant_workitem_cache[$wid];
                            if(empty($winfo))
                            {
                                \bigfathom\DebugHelper::showNeatMarkup($this->m_local_relevant_workitem_cache,"LOOK MISSING wid#$wid",'error');
                                throw new \Exception("Missing local_relevant_workitem for wid#$wid!");
                            }
                            $real_reh = $winfo['reh'] > .001 ? $winfo['reh'] : 0;

                            $totalbusy+=$real_reh;
                            if($real_reh > 0)
                            {
                                $reh_padding = max(4,(4 * min(20,1+round($real_reh/40))));   //Work around the floating point issues this way for now
                                $reh = $real_reh + $reh_padding;
                                $found_time_tracker = 0;
                                $sdt = $winfo['sdt'];
                                $edt = $winfo['edt'];
                                
                                $offset_sdt = array_search($sdt, $dd_keys);
                                if($offset_sdt === FALSE)
                                {
                                    throw new \Exception("Did NOT find sdt=$sdt for wid#$wid in dd_keys! detail=".print_r($dd_keys,TRUE));
                                }
                                
                                $offset_edt = array_search($edt, $dd_keys);
                                if($offset_edt === FALSE)
                                {
                                    throw new \Exception("Did NOT find edt=$edt for wid#$wid in dd_keys! detail=".print_r($dd_keys,TRUE));
                                }
                                
                                $wid2offsets[$wid] = array(
                                    'offset_sdt'=>$offset_sdt,'offset_edt'=>$offset_edt
                                );

                                for($i=$offset_sdt; $i<=$offset_edt; $i++)
                                {
                                    $dt_key = $dd_keys[$i];
                                    if(!isset($this->m_relevant_work_busy_mapper[$dt_key]))
                                    {
                                        $this->m_relevant_work_busy_mapper[$dt_key] = [];
                                        $this->m_relevant_work_busy_mapper[$dt_key]['busy'] = 0;
                                        $this->m_relevant_work_busy_mapper[$dt_key]['wids'] = [];
                                    }
                                    if(!isset($this->m_static_work_busy_mapper[$dt_key]['busy']))
                                    {
                                        $static_busy = 0;
                                    } else {
                                        $static_busy = $this->m_static_work_busy_mapper[$dt_key]['busy'];
                                    }
                                    
                                    $today_dd = $this->m_detail_ar[$dt_key];
                                    $available_hours_today = $today_dd['workhoursinday'] - $this->m_relevant_work_busy_mapper[$dt_key]['busy'] - $static_busy;
                                    if($reh < $available_hours_today)
                                    {
                                        //Pull all the time in today
                                        $workitem_busy_today = max($reh,0);
                                        $reh = 0;
                                    } else {
                                        if($available_hours_today > .001)
                                        {
                                            //Okay, there is some availabe time today
                                            $workitem_busy_today = $available_hours_today;
                                            $reh -= $workitem_busy_today;
                                        } else {
                                            $workitem_busy_today = 0;
                                        }
                                    }
                                    if($workitem_busy_today > 0)
                                    {
                                        $last_dt = $dt_key;
                                        if(empty($first_dt))
                                        {
                                            $first_dt = $dt_key;
                                        }
                                        $this->m_relevant_work_busy_mapper[$dt_key]['busy'] += $workitem_busy_today;
                                        $this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid] = $workitem_busy_today;
                                        if(!isset($wid2days_mapper[$wid]))
                                        {
                                            $wid2days_mapper[$wid] = [];
                                            $wid2days_mapper[$wid]['total_hours_mapped'] = 0;
                                            $wid2days_mapper[$wid]['days'] = [];
                                            $wid2days_mapper[$wid]['total_hours_mapped'] += $workitem_busy_today;
                                        }
                                        $wid2days_mapper[$wid]['total_hours_mapped'] += $workitem_busy_today;
                                        $wid2days_mapper[$wid]['days'][$dt_key] = $workitem_busy_today;
                                        $found_time_tracker += $workitem_busy_today;
                                    }
                                    if($reh <= 0)
                                    {
                                        break;
                                    }
                                }
                                $wid2range[$wid] = array('sdt'=>$first_dt,'edt'=>$last_dt);
                                if($reh > 0 && $found_time_tracker < $real_reh)
                                {
                                    $wid_nofit_reh_mapper[$wid] = $reh;
                                }
                            }
                        }
                    }
                }
            }
            ksort($this->m_relevant_work_busy_mapper);
            $this->m_computed_relevant_wid2range = $wid2range;
            $this->m_computed_relevant_overages = $wid_nofit_reh_mapper;
            $this->m_uniqueid_relevant_work_busy_mapper++;
            
            if($this->m_embed_overages_yn && count($wid_nofit_reh_mapper)>0)
            {
                $this->embedRelevantOverages($dd_keys, $wid_nofit_reh_mapper, $wid2days_mapper, $wid2offsets);
            }
            
            $this->m_computed_relevant_busy_yn = 1;
            
            $this->m_computing_relevant_busy_yn = 0;
            
        } catch (\Exception $ex) {
            $this->m_computing_relevant_busy_yn = 0;
            error_log("FAILED PAI computeRelevantBusy for personid#{$this->m_personid} because " . $ex);
            throw $ex;
        }
    }
    
    /**
     * Embed all the overages into the persons busy map
     */
    private function embedRelevantOverages($dd_keys, $wid_nofit_reh_mapper, $wid2days_mapper, $wid2offsets)
    {
        try
        {
            $nofit_wids = array_keys($wid_nofit_reh_mapper);
            foreach($nofit_wids as $wid)
            {
                $winfo = $this->m_local_relevant_workitem_cache[$wid];
                $mapped_daysinfo = $wid2days_mapper[$wid];

                //Undo existing mapping
                foreach($mapped_daysinfo['days'] as $dt_key=>$mappedhours)
                {
                    $this->m_relevant_work_busy_mapper[$dt_key]['busy'] -= $mappedhours;
                    $this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid] = 0;
                }

                //Count all target days
                $workday_count = 0;
                $allday_count = 0;
                $offsets = $wid2offsets[$wid];
                $offset_sdt = $offsets['offset_sdt'];
                $offset_edt = $offsets['offset_edt'];
                $workday_list = [];
                for($i=$offset_sdt; $i<=$offset_edt; $i++)  
                {
                    $dt_key = $dd_keys[$i];
                    $allday_count++;
                    $today_dd = $this->m_detail_ar[$dt_key];
                    if($today_dd['workhoursinday']>.25)
                    {
                        $workday_count++;
                        $workday_list[] = $dt_key;
                    }
                }
                $sdt = $winfo['sdt'];
                $edt = $winfo['edt'];
                $reh = $winfo['reh'];

                if($allday_count == 0)
                {
                    //This should never happen!
                    throw new \Exception("Did not find ANY days between $sdt and $edt!");
                }
                $first_dt = NULL;
                $last_dt = NULL;
                if($workday_count == 0)
                {
                    //No workdays, simply put over all the available days
                    $busy_per_day = ($allday_count>1) ? ceil($reh/$allday_count) : $reh;
                    $remaining = $reh;
                    for($i=$offset_sdt; $i<=$offset_edt; $i++)  
                    {
                        $dt_key = $dd_keys[$i];
                        if($first_dt === NULL)
                        {
                            $first_dt = $dt_key;
                        }
                        if($remaining > $busy_per_day)
                        {
                            $busy_today = $busy_per_day;
                        } else {
                            $busy_today = $remaining;
                        }
                        $this->m_relevant_work_busy_mapper[$dt_key]['busy'] += $busy_today;
                        $this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid] = $busy_today;                            
                        $remaining -= $busy_today;
                        if($remaining < .001)
                        {
                            break;
                        } else {
                            $last_dt = $dt_key;
                        }
                    }
                } else {
                    //Distribute the overage over all the workdays
                    $busy_per_day = ($workday_count>1) ? ceil($reh/$workday_count) : $reh;
                    $remaining = $reh;
                    foreach($workday_list as $dt_key)
                    {
                        if($first_dt === NULL)
                        {
                            $first_dt = $dt_key;
                        }
                        if($remaining > $busy_per_day)
                        {
                            $busy_today = $busy_per_day;
                        } else {
                            $busy_today = $remaining;
                        }
                        $this->m_relevant_work_busy_mapper[$dt_key]['busy'] += $busy_today;
                        $this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid] = $busy_today;                           
                        $remaining -= $busy_today;
                        if($remaining < .001)
                        {
                            break;
                        } else {
                            $last_dt = $dt_key;
                        }
                    }
                }
                $this->m_computed_relevant_wid2range[$wid] = array('sdt'=>$first_dt,'edt'=>$last_dt);
            }
            ksort($this->m_relevant_work_busy_mapper);
            $this->m_uniqueid_relevant_work_busy_mapper++;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Embed all the overages into the persons busy map
     */
    private function embedStaticOverages($dd_keys, $wid_nofit_reh_mapper, $wid2days_mapper, $wid2offsets)
    {
        try
        {
            $nofit_wids = array_keys($wid_nofit_reh_mapper);
            foreach($nofit_wids as $wid)
            {
                $winfo = $this->m_local_static_workitem_cache[$wid];
                $mapped_daysinfo = $wid2days_mapper[$wid];

                //Undo existing mapping
                foreach($mapped_daysinfo['days'] as $dt_key=>$mappedhours)
                {
                    $this->m_static_work_busy_mapper[$dt_key]['busy'] -= $mappedhours;
                    $this->m_static_work_busy_mapper[$dt_key]['wids'][$wid] = 0;
                    $this->m_static_work_busy_mapper[$dt_key]['debuging'][] = "removed $mappedhours of wid#$wid at $dt_key::new busy=" . $this->m_static_work_busy_mapper[$dt_key]['busy'];
                }

                //Count all target days
                $workday_count = 0;
                $allday_count = 0;
                $offsets = $wid2offsets[$wid];
                $offset_sdt = $offsets['offset_sdt'];
                $offset_edt = $offsets['offset_edt'];
                $workday_list = [];
                for($i=$offset_sdt; $i<=$offset_edt; $i++)  
                {
                    $dt_key = $dd_keys[$i];
                    $allday_count++;
                    $today_dd = $this->m_detail_ar[$dt_key];
                    if($today_dd['workhoursinday']>.25)
                    {
                        $workday_count++;
                        $workday_list[] = $dt_key;
                    }
                }
                $sdt = $winfo['sdt'];
                $edt = $winfo['edt'];
                $reh = $winfo['reh'];
                if($allday_count == 0)
                {
                    //This should never happen!
                    throw new \Exception("Did not find ANY days between $sdt and $edt!");
                }
                $first_dt = NULL;
                $last_dt = NULL;
                if($workday_count == 0)
                {
                    //No workdays, simply put over all the available days
                    $busy_per_day = ($allday_count>1) ? ceil($reh/$allday_count) : $reh;
                    $remaining = $reh;
                    for($i=$offset_sdt; $i<=$offset_edt; $i++)  
                    {
                        $dt_key = $dd_keys[$i];
                        if($first_dt === NULL)
                        {
                            $first_dt = $dt_key;
                        }
                        if($remaining > $busy_per_day)
                        {
                            $busy_today = $busy_per_day;
                        } else {
                            $busy_today = $remaining;
                        }
                        $this->m_static_work_busy_mapper[$dt_key]['busy'] += $busy_today;
                        $this->m_static_work_busy_mapper[$dt_key]['wids'][$wid] = $busy_today;                            
                        $remaining -= $busy_today;
                        $this->m_static_work_busy_mapper[$dt_key]['debuging'][] = "embedded on simple day $busy_today of wid#$wid (remaining $remaining allday_count=$allday_count)";
                        if($remaining < .001)
                        {
                            break;
                        } else {
                            $last_dt = $dt_key;
                        }
                    }
                } else {
                    //Distribute the overage over all the workdays
                    $busy_per_day = ($workday_count>1) ? ceil($reh/$workday_count) : $reh;
                    $remaining = $reh;
                    foreach($workday_list as $dt_key)
                    {
                        if($first_dt === NULL)
                        {
                            $first_dt = $dt_key;
                        }
                        if($remaining > $busy_per_day)
                        {
                            $busy_today = $busy_per_day;
                        } else {
                            $busy_today = $remaining;
                        }
                        $this->m_static_work_busy_mapper[$dt_key]['busy'] += $busy_today;
                        $this->m_static_work_busy_mapper[$dt_key]['wids'][$wid] = $busy_today;                           
                        $remaining -= $busy_today;
                        $this->m_static_work_busy_mapper[$dt_key]['debuging'][] = "embedded on workday $busy_today of wid#$wid (remaining $remaining allday_count=$allday_count)";
                        if($remaining < .001)
                        {
                            break;
                        } else {
                            $last_dt = $dt_key;
                        }
                    }
                }
                $this->m_computed_static_wid2range[$wid] = array('sdt'=>$first_dt,'edt'=>$last_dt);
            }
            ksort($this->m_static_work_busy_mapper);
            $this->m_uniqueid_static_work_busy_mapper++;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Computes a 'what-if' result without changing global members
     */
    public function getOneWorkFitInsightBundle($winfo, $continue_until_reh_is_zero=TRUE
            , $earliest_allowed_start_dt=NULL
            , $earliest_allowed_end_dt=NULL)
    {
        $throw_exceptions_yn = 0;
        try
        {
            $this->computeBusy();
            $bundle = [];

            $wid = $winfo['wid'];
            $our_busy_mapper = [];
            $dd_keys = array_keys($this->m_detail_ar);

            $first_dt = NULL;
            $last_dt = NULL;

            if(!isset($winfo['reh']) && isset($winfo['remaining_effort_hours']))
            {
                $throw_exceptions_yn = 1;
                throw new \Exception("Expected reh but instead found remaining_effort_hours for winfo=" . print_r($winfo,TRUE));
            }
            if(!isset($winfo['sdt']) && isset($winfo['planned_start_dt']))
            {
                $throw_exceptions_yn = 1;
                throw new \Exception("Expected sdt but instead found planned_start_dt for winfo=" . print_r($winfo,TRUE));
            }
            if(!isset($winfo['edt']) && isset($winfo['planned_end_dt']))
            {
                $throw_exceptions_yn = 1;
                throw new \Exception("Expected edt but instead found planned_end_dt for winfo=" . print_r($winfo,TRUE));
            }
            
            $input_sdt = $winfo['sdt'];
            $input_edt = $winfo['edt'];
            if(isset($winfo['sdt_locked_yn']))
            {
                $sdt_locked_yn = $winfo['sdt_locked_yn'];
            } else {
                $sdt_locked_yn = isset($winfo['planned_start_dt_locked_yn']) ? $winfo['planned_start_dt_locked_yn'] : (!empty($winfo['actual_start_dt']) ? 1 : 0);
            }
            if(isset($winfo['edt_locked_yn']))
            {
                $edt_locked_yn = $winfo['edt_locked_yn'];
            } else {
                $edt_locked_yn = isset($winfo['planned_end_dt_locked_yn']) ? $winfo['planned_end_dt_locked_yn'] : (!empty($winfo['actual_end_dt']) ? 1 : 0);
            }
            
            $latest_ant_edt = $winfo['latest_ant_edt'];
            $min_allowed_sdt = !empty($winfo['min_allowed_sdt']) ? $winfo['min_allowed_sdt'] : $earliest_allowed_start_dt;
            $max_allowed_sdt = !empty($winfo['max_allowed_sdt']) ? $winfo['max_allowed_sdt'] : NULL;
            
            if(!empty($input_sdt) && $input_sdt < $min_allowed_sdt)
            {
                $existing_starts_too_early = 1;
            } else {
                $existing_starts_too_early = 0;
            }

            if(!empty($max_allowed_sdt) && $input_edt > $max_allowed_sdt)
            {
                $existing_ends_too_early = 1;
            } else {
                $existing_ends_too_early = 0;
            }
            
            $prev_blc = 0;
            $prev_reh = 0;
            $prev_days = 0;
            $blc_had_progress = 0;
            $days_since_progress = 0;
            $raw_reh = isset($winfo['reh']) ? $winfo['reh'] : $winfo['remaining_effort_hours'];
            $reh = ceil($raw_reh);
            $metadata = array('input_sdt'=>$input_sdt, 'input_edt'=>$input_edt, 'input_reh'=>$reh);
            $metadata['continue_until_reh_is_zero_yn'] = $continue_until_reh_is_zero ? 1 : 0;
            $metadata['existing_starts_too_early_yn'] = $existing_starts_too_early;
            $metadata['existing_ends_too_early_yn'] = $existing_ends_too_early;
                    
            if($sdt_locked_yn)
            { 
                $count_start_dt = $input_sdt;
            } else {
                if(!empty($input_sdt))
                {
                    if($input_sdt > $min_allowed_sdt)
                    {
                        //See if we can start sooner
                        $count_start_dt = max($this->m_tomorrow_dt, $min_allowed_sdt);
                    } else {
                        //Just dont start before tomorrow
                        $count_start_dt = max($input_sdt, $this->m_tomorrow_dt);
                    }
                } else {
                    if(empty($this->m_max_checked_dt))
                    {
                        $this->m_min_checked_dt = $this->m_tomorrow_dt;
                        $this->m_max_checked_dt = $this->m_tomorrow_dt;
                    } else {
                        $this->m_max_checked_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_max_checked_dt, 1);
                    }
                    $count_start_dt = $this->m_max_checked_dt;
                }
                if($count_start_dt < $min_allowed_sdt)
                {
                    $count_start_dt = $min_allowed_sdt;
                }
                if($count_start_dt <= $latest_ant_edt)
                {
                    //Start the day after!
                    $count_start_dt = $this->m_max_checked_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($latest_ant_edt, 1);
                }
            }
            if($edt_locked_yn)
            {
                $count_end_dt = $input_edt;
            } else {
                if(empty($input_edt) || $count_start_dt > $input_edt)
                {
                    $count_end_dt = $count_start_dt;
                } else {
                    $count_end_dt = $input_edt;
                }
            }
            
            $bigloopcounter = 0;
            $sdt = $count_start_dt;
            $edt = $count_end_dt;
            if($reh == 0)
            {
                $first_dt = $count_start_dt;
                $last_dt = $first_dt;
                $continued_beyond_original_edt_yn = ($last_dt > $input_edt) ? 1 : 0;
            } else {
                while($bigloopcounter === 0 || ($continue_until_reh_is_zero && $reh>0))
                {
                    $offset_sdt = array_search($sdt, $dd_keys);
                    $offset_edt = array_search($edt, $dd_keys);

                    if($offset_sdt === FALSE || $offset_edt === FALSE)
                    {
                        if(empty($edt) || $sdt > $edt)
                        {
                            throw new \Exception("Invalid date range [$sdt,$edt] for wid#$wid at loop iteration#$bigloopcounter!");
                        }                        
                        $debug_info = $this->m_oMasterDailyDetail->updateLocalCache($sdt,$edt);
                        $dd_keys = array_keys($this->m_detail_ar);
                        $offset_sdt = array_search($sdt, $dd_keys);
                        $offset_edt = array_search($edt, $dd_keys);
                        if($offset_sdt === FALSE || $offset_edt === FALSE)
                        {
                            \bigfathom\DebugHelper::showNeatMarkup($debug_info,"BETA TESTING DEBUG INFO Cannot find offsets for date range [$sdt,$edt] for wid#$wid",'error');
                            \bigfathom\DebugHelper::showNeatMarkup($dd_keys,"BETA TESTING Cannot find offsets for date range [$sdt,$edt] for wid#$wid in this detail ($offset_sdt,$offset_edt) winfo=".print_r($winfo,TRUE),'error');
                            throw new \Exception("Cannot find offsets for date range [$sdt,$edt] for wid#$wid! winfo=".print_r($winfo,TRUE));
                        }
                    }

                    for($i=$offset_sdt; $i<=$offset_edt; $i++)
                    {
                        $dt_key = $dd_keys[$i];
                        $existing_busy = 0;
                        if(isset($this->m_static_work_busy_mapper[$dt_key]['busy']))
                        {
                            $existing_busy += $this->m_static_work_busy_mapper[$dt_key]['busy'];
                        }
                        if(isset($this->m_relevant_work_busy_mapper[$dt_key]))
                        {
                            $existing_busy += $this->m_relevant_work_busy_mapper[$dt_key]['busy'];
                        }

                        $today_dd = $this->m_detail_ar[$dt_key];
                        $available_workhours_today = $today_dd['workhoursinday'] - $existing_busy;
                        if(isset($this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid]))
                        {
                            //Ignore anything that was already mapped from this wid
                            $available_workhours_today += $this->m_relevant_work_busy_mapper[$dt_key]['wids'][$wid];
                        }

                        if($available_workhours_today > 0)
                        {
                            if($reh < $available_workhours_today)
                            {
                                //Spend it all today
                                $busy_today = $reh;
                                $reh = 0;
                            } else {
                                //Fit what we can today
                                $busy_today = $available_workhours_today;
                                $reh -= $busy_today;
                            }
                        } else {
                            $busy_today = 0;
                        }
                        if($busy_today > 0)
                        {
                            if(empty($first_dt))
                            {
                                $first_dt = $dt_key;
                            }
                            $last_dt = $dt_key;
                            $our_busy_mapper[$dt_key] = $busy_today;
                        }
                        if($reh <= .001)
                        {
                            $reh = 0;   //Clean it up
                            break;
                        }
                    }
                    if($reh<$prev_reh)
                    {
                        //Good, we made progress
                        $blc_had_progress = $bigloopcounter;
                        $days_since_progress = 0;
                    } else {
                        $days_since_progress += $prev_days;
                    }
                    if($continue_until_reh_is_zero && $reh>0)
                    {

                        if($days_since_progress > 1000)
                        {
                            //Give up on our attempt!
                            //\bigfathom\DebugHelper::showNeatMarkup($our_busy_mapper,"FAILED mapping busy for wid#$wid because $days_since_progress days of no progress on $reh hours!");
                            break;
                        }
                        
                        //Get some more days
                        if($days_since_progress == 0)
                        {
                            $shift_days = round(1 + $reh/4);    //Just a heuristic
                        } else {
                            $shift_days = max($days_since_progress, 365);
                        }
                        $new_sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($edt, 1);
                        $new_edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($new_sdt, $shift_days);
                        $this->m_oMasterDailyDetail->updateLocalCache($new_sdt, $new_edt);
                        
                        $sdt = $new_sdt;
                        $edt = $new_edt;
                        
                        //Track these actions
                        $prev_blc = $bigloopcounter;
                        $prev_reh = $reh;
                        $prev_days = $shift_days;
                    }
                    $bigloopcounter++;
                }
                if($reh > 0)
                {
                    //drupal_set_message("Overage of $reh hours for workitem#$wid",'info');
                }
                $continued_beyond_original_edt_yn = $bigloopcounter > 1 ? 1 : 0;
            }
            $wid2range = array('sdt'=>$first_dt, 'edt'=>$last_dt);
            
            if(!empty($wid2range['sdt']) && !empty($wid2range['edt']))
            {
                $found_fit_yn = $reh > 0 ? 0 : 1;
            } else {
                $found_fit_yn = 0;
            }
            $bundle['metadata'] = $metadata;
            $bundle['found_fit_yn'] = $found_fit_yn;
            $bundle['continued_beyond_original_edt_yn'] = $continued_beyond_original_edt_yn;
            $bundle['daterange'] = $wid2range;
            $bundle['date2hours'] = $our_busy_mapper;
            $bundle['overage_yn'] = $reh > 0 ? 1 : 0;
            $bundle['overage_hours'] = $reh;

            return $bundle;
            
        } catch (\Exception $ex) {
            if(!$throw_exceptions_yn)
            {
                drupal_set_message("Unable to getOneRelevantBusyBundle for personid#{$this->m_personid} because " . $ex, 'error');
                return;
            } else {
                throw $ex;
            }
        }
    }
    
    private function computeBusy()
    {
        try
        {
            if(!$this->m_computed_static_busy_yn)
            {
                $this->computeStaticBusy();
            }
            if(!$this->m_computed_relevant_busy_yn)
            {
                $this->computeRelevantBusy();
            }
            
        } catch (\Exception $ex) {
            error_log("ERROR failed computeBusy for personid#{$this->m_personid} because " . $ex, 'error');
            throw $ex;
        }
    }
    
    /**
     * The returned daily detail does NOT contain any 'busy' hours.
     * Use this result only as a base on which you overlay the actual
     * hours worked by the person.
     */    
    public function getTemplateDailyDetailBundle()
    {
        $dd = $this->m_detail_ar;
        $bundle = [];
        $bundle['daily_detail'] = $dd;
        return $bundle;
    }
    
    public function getAvailabilityDetailBundle($start_dt=NULL, $end_dt=NULL, $include_busy_detail_yn=0)
    {
        $this->computeBusy();
        
        $all_wids_found = [];
        
        if(empty($start_dt))
        {
            $start_dt = $this->m_oMasterDailyDetail->getFirstDate();
        } 
        if(empty($end_dt))
        {
            $end_dt = $this->m_oMasterDailyDetail->getLastDate();
        }
        
        $avail_overlay = [];
        $dd_keys = array_keys($this->m_detail_ar);

        $offset_sdt = array_search($start_dt, $dd_keys);
        if($offset_sdt === FALSE)
        {
            //Just get our first one
            $offset_sdt = 0;
            $start_dt = $dd_keys[0];
        }
        $offset_edt = array_search($end_dt, $dd_keys);
        if($offset_edt === FALSE)
        {
            //Just get our last one
            $offset_edt = count($dd_keys) - 1;
            $end_dt = $dd_keys[$offset_edt];
        }
        $debug_busy = [];
        for($i=$offset_sdt; $i<=$offset_edt; $i++)
        {
            $dt_key = $dd_keys[$i];
            $today_dd = $this->m_detail_ar[$dt_key];
            
            $oneday_busy = 0;
            $oneday_static_wids = [];
            $oneday_relevant_wids = [];
            if(isset($this->m_static_work_busy_mapper[$dt_key]))
            {
                $oneday_busy += $this->m_static_work_busy_mapper[$dt_key]['busy'];
                $debug_busy[$dt_key]['static'] = array('hours'=>$this->m_static_work_busy_mapper[$dt_key]['busy'],'wids'=>[]);
                $add_static_wids = $this->m_static_work_busy_mapper[$dt_key]['wids'];
                foreach($add_static_wids as $onewid=>$hours)
                {
                    $all_wids_found[$onewid] = $onewid;
                    $oneday_static_wids[$onewid] = $hours;
                    $debug_busy[$dt_key]['static']['wids'][$onewid] = $hours;
                }
            }
            if(isset($this->m_relevant_work_busy_mapper[$dt_key]))
            {            
                $oneday_busy += $this->m_relevant_work_busy_mapper[$dt_key]['busy'];
                $debug_busy[$dt_key]['relevant'] = array('hours'=>$this->m_relevant_work_busy_mapper[$dt_key]['busy'],'wids'=>[]);
                $add_relevant_wids = $this->m_relevant_work_busy_mapper[$dt_key]['wids'];
                foreach($add_relevant_wids as $onewid=>$hours)
                {
                    $all_wids_found[$onewid] = $onewid;
                    $oneday_relevant_wids[$onewid] = $hours;
                    $debug_busy[$dt_key]['relevant']['wids'][$onewid] = $hours;
                }
            }
            if($oneday_busy > 0 || $today_dd['workhoursinday'] > 0)
            {
                $avail_overlay[$dt_key] = [];
                $avail_overlay[$dt_key]['workhoursinday'] = $today_dd['workhoursinday'];
                $avail_overlay[$dt_key]['free'] = $today_dd['workhoursinday'] - $oneday_busy;
                $avail_overlay[$dt_key]['busy'] = $oneday_busy;
                if(empty($avail_overlay[$dt_key]['wids']['static']))
                {
                    $avail_overlay[$dt_key]['wids']['static'] = [];
                }
                $sum_static_hours = 0;
                foreach($oneday_static_wids as $onewid=>$hours)
                {
                    $all_wids_found[$onewid] = $onewid;
                    $avail_overlay[$dt_key]['wids']['static'][$onewid] = $hours;
                    $sum_static_hours += $hours;
                }
                if(empty($avail_overlay[$dt_key]['wids']['relevant']))
                {
                    $avail_overlay[$dt_key]['wids']['relevant'] = [];
                }
                $sum_relevant_hours = 0;
                foreach($oneday_relevant_wids as $onewid=>$hours)
                {
                    $all_wids_found[$onewid] = $onewid;
                    $avail_overlay[$dt_key]['wids']['relevant'][$onewid] = $hours;
                    $sum_relevant_hours += $hours;
                }
                $oneday_parts_total = $sum_static_hours + $sum_relevant_hours;
                if($oneday_parts_total != $oneday_busy)
                {
                    //This should NEVER happen
                    throw new \Exception("Hours total mismatch at $dt_key for person#{$this->m_personid} [$oneday_parts_total(sum of static:$sum_static_hours and relevant:$sum_relevant_hours) != $oneday_busy] in range [$start_dt,$end_dt]" 
                            . "<BR>m_static_work_busy_mapper=" . \bigfathom\DebugHelper::getNeatMarkup($this->m_static_work_busy_mapper,"STATIC BUSY MAPPER")
                            . "<BR>m_relevant_work_busy_mapper=" . \bigfathom\DebugHelper::getNeatMarkup($this->m_relevant_work_busy_mapper,"RELEVANT BUSY MAPPER")
                            . "<BR>today_dd[$dt_key]=" . \bigfathom\DebugHelper::getNeatMarkup($today_dd,"TODAY DD")
                            . "<BR>avail_overlay[$dt_key]=" . \bigfathom\DebugHelper::getNeatMarkup($avail_overlay,"AVAIL OVERLAY")
                            . "<BR>debug_busy=" . \bigfathom\DebugHelper::getNeatMarkup($debug_busy,"DEBUG BUSY")
                            );
                }
            }
        }
        
        $metadata = [];
        $metadata['pai_objectid'] = $this->m_objectid;
        $metadata['embed_overages_yn'] = $this->m_embed_overages_yn;
        $metadata['include_busy_detail_yn'] = $include_busy_detail_yn ? 1 : 0;
        $metadata['count_wids_found'] = count($all_wids_found);
        $metadata['all_wids_found'] = $all_wids_found;
        $metadata['uniqueid_static_work_busy_mapper'] = $this->m_uniqueid_static_work_busy_mapper;
        $metadata['uniqueid_relevant_work_busy_mapper'] = $this->m_uniqueid_relevant_work_busy_mapper;
        
        $bundle = [];
        $bundle['metadata'] = $metadata;
        $bundle['start_dt'] = $start_dt;
        $bundle['end_dt'] = $end_dt;
        $bundle['overages']['static'] = $this->m_computed_static_overages;
        $bundle['overages']['relevant'] = $this->m_computed_relevant_overages;
        if($include_busy_detail_yn)
        {
            $bundle['busy_map']['static'] = $this->m_static_work_busy_mapper;
            $bundle['busy_map']['relevant'] = $this->m_relevant_work_busy_mapper;
        }
        $bundle['hours_overlay'] = $avail_overlay;
        return $bundle;
    }
    
    public function getUtilizationRanges()
    {
        drupal_set_message("TODO getUtilizationRanges()",'error');
        return [];
    }

    public function getAvailableHoursInRange($start_dt, $end_dt)
    {
        drupal_set_message("TODO getAvailableHoursInRange($start_dt, $end_dt)",'error');
        
        return 500;
    }

    public function getEndDateForWorkHours($start_dt, $hours)
    {
        drupal_set_message("TODO getEndDateForWorkHours($start_dt, $hours)",'error');
        return $start_dt;
    }

}
