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

require_once 'PersonAvailabilityInsight.php';

/**
 * Object that has the daily generic detail on which all others build
 *
 * @author Frank
 */
class MasterDailyDetail
{
    private $m_objectid = NULL;
    
    protected $m_placeholder_wid = NULL;
    
    protected $m_detail_ar = NULL;
    protected $m_last_dt = NULL;
    protected $m_first_dt = NULL;
    protected $m_today_dt = NULL;
    
    protected $m_projects2ignore = NULL;    //These projects are ignore for availability blocking
    
    protected $m_map_personid2pai = NULL;
    
    protected $m_relevant_workitem_cache = NULL;
    
    public function __construct($projects2ignore=NULL, $today_dt=NULL)
    {
        $this->m_objectid = "MDD" . (10000 - mt_rand (1000,9999)). "::" . microtime(TRUE);
        $this->initialized_yn = 0;
        $this->m_placeholder_wid = 0;
        $this->m_projects2ignore = $projects2ignore;        
        $this->m_detail_ar = [];
        if(empty($today_dt))
        {
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
        }
        $this->m_today_dt = $today_dt;
        $this->m_map_personid2pai = [];
        $this->m_relevant_workitem_cache = [];
    }
    
    public function getThisInstanceID()
    {
        return $this->m_objectid;
    }
    
    public function initialize($personid_ar, $start_dt, $end_dt, $embed_overages_yn=1)
    {
        try
        {
            if(empty($start_dt))
            {
                $start_dt = $this->m_today_dt;
            }
            if(empty($end_dt) || $end_dt == $start_dt)
            {
                //Lets go for a few days instead of one
                $shift_days = 15;
                $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $shift_days);
            }
            if($start_dt > $end_dt)
            {
                throw new \Exception("Cannot initialize with backward range date [$start_dt,$end_dt] for map_personid=".print_r($personid_ar,TRUE));
            }
            $this->m_map_personid2pai = [];
            $flags = array('projects2ignore'=>$this->m_projects2ignore);
            foreach($personid_ar as $personid)
            {
                if(empty($personid))
                {
                    throw new \Exception("Invalid empty personid provided in array " . print_r($personid_ar,TRUE));
                }
                $this->m_map_personid2pai[$personid] = new \bigfathom_utilization\PersonAvailabilityInsight($personid, $this, $flags, $embed_overages_yn);
            }
            $this->updateLocalCache($start_dt, $end_dt);
            $this->initialized_yn = 1;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function __toString()
    {
        $nice_text_ar = [];
        $nice_text_ar[] = "MasterDailyDetail first_dt={$this->m_first_dt} last_dt={$this->m_last_dt}";
        
        $pai_info = empty($this->m_map_personid2pai) ? 'MISSING PAI' : 'We have ' . count($this->m_map_personid2pai) . ' PAI';
        $nice_text_ar[] = "PAI info: [$pai_info] initialized_yn={$this->initialized_yn}";

        if(!empty($this->m_map_personid2pai) && is_array($this->m_map_personid2pai))
        {
            foreach($this->m_map_personid2pai as $personid=>$pai)
            {
                $nice_text_ar[] = "personid=$personid ::: " . $pai;
            }
        }
        
        $nice_text = implode("\n<br>>>>", $nice_text_ar);
        return $nice_text;
    } 

    public function clearRelevantWorkitemCache()
    {
        foreach($this->m_map_personid2pai as $pai)
        {
            $pai->clearRelevantWorkitemCache();
        }
    }
    
    public function getAllPersonIDs()
    {
        return array_keys($this->m_map_personid2pai);
    }
    
    public function getOnePersonAvailabilityInsightInstance($personid)
    {
        if(!isset($this->m_map_personid2pai[$personid]))
        {
            throw new \Exception("Did NOT find person#$personid!");
        }
        return $this->m_map_personid2pai[$personid];
    }
    
    public function getOnePersonWorkFitInsightBundle($winfo, $personid_override=NULL, $continue_to_zero_reh=TRUE)
    {
        try
        {
            if(empty($personid_override))
            {
                $wid = isset($winfo['wid']) ? $winfo['wid'] : NULL;
                if(isset($this->m_relevant_workitem_cache[$wid]['owner_personid']))
                {
                    $personid = $this->m_relevant_workitem_cache[$wid]['owner_personid'];
                } else {
                    $personid = $winfo['owner_personid'];
                }
            } else {
                $personid = $personid_override;
            }
            if(empty($personid))
            {
                $errmsg = "Unable to determine person owner of workinfo=" . print_r($winfo,TRUE);
                \bigfathom\DebugHelper::showStackTrace($errmsg);
                throw new \Exception($errmsg);
            }
            if(!isset($this->m_map_personid2pai[$personid]))
            {
                throw new \Exception("No existing work found for person#$personid");
            }
            $first_allowed_dt = $this->getFirstDate();
            $bundle = $this->m_map_personid2pai[$personid]->getOneWorkFitInsightBundle($winfo, $continue_to_zero_reh, $first_allowed_dt);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getRelevantWorkitemRecordIDs()
    {
        try
        {
            $final_list = [];
            
            foreach($this->m_map_personid2pai as $personid=>$pai)
            {
                $ids = $pai->getRelevantWorkitemRecordIDs();
                foreach($ids as $wid)
                {
                    $final_list[] = $wid;
                }
            }

            return $final_list;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function hasRelevantWorkitemRecord($wid)
    {
        return isset($this->m_relevant_workitem_cache[$wid]);
    }
    
    public function getRelevantWorkitemRecord($wid)
    {
        try
        {
            $winfo = NULL;
            if(!isset($this->m_relevant_workitem_cache[$wid]))
            {
                $err_msg = "Did NOT find wid#$wid in relevant collection!";
                \bigfathom\DebugHelper::showNeatMarkup($this->m_relevant_workitem_cache,$err_msg,'error');
                \bigfathom\DebugHelper::showStackTrace($err_msg,'error');
                throw new \Exception($err_msg);
            }
            $personid = $this->m_relevant_workitem_cache[$wid]['owner_personid'];
            if(isset($this->m_map_personid2pai[$personid]))
            {
                $winfo = $this->m_map_personid2pai[$personid]->getRelevantWorkitemRecord($wid);
            }
            return $winfo;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function setRelevantWorkitemRecord($wid, $winfo)
    {
        try
        {
            if(empty($wid))
            {
                throw new \Exception("Missing required wid parameter for winfo=" . print_r($winfo,TRUE));
            }
            if(!array_key_exists('sdt',$winfo))
            {
                throw new \Exception("Missing required sdt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!array_key_exists('edt',$winfo))
            {
                throw new \Exception("Missing required edt in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(!array_key_exists('reh',$winfo))
            {
                throw new \Exception("Missing required reh in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(empty($winfo['owner_personid']))
            {
                throw new \Exception("Missing owner_personid in wid#$wid winfo=" . print_r($winfo,TRUE));
            }
            if(empty($winfo['wid']))
            {
                $winfo['wid'] = $wid;
            }
            if($winfo['wid'] != $wid)
            {
                throw new \Exception("Mismatched wid param wid#$wid vs winfo=" . print_r($winfo,TRUE));
            }
            $sdt = $winfo['sdt'];
            $edt = $winfo['edt'];
            $personid = $winfo['owner_personid'];

            $grow = (!empty($sdt) && $sdt < $this->getFirstDate()) || (!empty($edt) && $edt > $this->getLastDate());
            if($grow)
            {
                $this->updateLocalCache($sdt, $edt);
            }
            $this->m_relevant_workitem_cache[$wid] = $winfo;
            if(!isset($this->m_map_personid2pai[$personid]))
            {
                if(empty($this->m_map_personid2pai))
                {
                    $map_tx = "EMPTY map!";
                } else {
                    $just_keys = array_keys($this->m_map_personid2pai);
                    $map_tx = "map has keys " . implode(',',$just_keys);
                }
                throw new \Exception("Did NOT find person2pai for person#$personid! ($map_tx)");
            }
            $this->m_map_personid2pai[$personid]->setRelevantWorkitemRecord($wid, $winfo);

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getFirstDate()
    {
        if(empty($this->m_first_dt) && !empty($this->m_detail_ar))
        {
            $all_dates = array_keys($this->m_detail_ar);
            $this->m_first_dt = $all_dates[0];
        }
        return !empty($this->m_first_dt) ? $this->m_first_dt : $this->m_today_dt;
    }

    public function getLastDate()
    {
        return $this->m_last_dt;
    }
    
    public function getTodayDate()
    {
        return $this->m_today_dt;
    }
    
    public function updateLocalCache($start_dt, $end_dt)
    {
        $action_info = [];
        $action_info[] = "INPUT [$start_dt, $end_dt]";
        if(empty($start_dt))
        {
            //throw new \Exception("Missing required start date!");
            if(empty($end_dt) || ($this->m_today_dt < $end_dt))
            {
                $start_dt = $this->m_today_dt;
            } else {
                $start_dt = $end_dt;
            }
        }

        if(empty($end_dt))
        {
            $end_dt = $start_dt;
            //throw new \Exception("Missing required end date!");
        }

        $action_info[] = "USING DATES [$start_dt, $end_dt]";
        
        $empty_fetch_start_dt = empty($this->m_detail_ar[$start_dt]);
        $empty_fetch_end_dt = empty($this->m_detail_ar[$end_dt]);
        
        $action_info[] = "MAP EMPTY IN ARRAY [$empty_fetch_start_dt, $empty_fetch_end_dt]";
        
        if($empty_fetch_start_dt || $empty_fetch_end_dt)
        {
            //Load the missing stuff
            if(empty($this->m_first_dt))
            {
        $action_info[] = "EMPTY FIRST DT";
                //We have no data, load all this now.
                $load_start_dt = $start_dt;
                $load_end_dt = $end_dt;
                if(empty($load_start_dt))
                {
                    $load_start_dt = $this->m_today_dt;
                }
                if($load_start_dt <= $load_end_dt)
                {
                    $this->growCache($load_start_dt, $load_end_dt);
                }
            } else {
                //Figure out what part(s) to load
                if($start_dt < $this->m_first_dt)
                {
        $action_info[] = "LOAD BEFORE bc $start_dt < {$this->m_first_dt}";
                    //Load before
                    $load_start_dt = $start_dt;
                    $load_end_dt = $this->m_first_dt;
                    if(empty($load_start_dt))
                    {
                        $load_start_dt = $this->m_today_dt;
                    }
                    if($load_start_dt <= $load_end_dt)
                    {
        $action_info[] = "growCache($load_start_dt, $load_end_dt)";
                        $this->growCache($load_start_dt, $load_end_dt);
                    }
                }
                if($end_dt > $this->m_last_dt)
                {
        $action_info[] = "LOAD AFTER bc $end_dt > {$this->m_last_dt}";
                    //Load after
                    $load_start_dt =$this->m_last_dt;
                    $load_end_dt = $end_dt;
                    if(empty($load_start_dt))
                    {
                        $load_start_dt = $this->m_today_dt;
                    }
                    if($load_start_dt <= $load_end_dt)
                    {
        $action_info[] = "growCache($load_start_dt, $load_end_dt)";
                        $this->growCache($load_start_dt, $load_end_dt);
                    }
                }
            }
        }
        $action_info[] = "DONE!";
        return $action_info;
    }

    private function growCache($load_start_dt, $load_end_dt)
    {
        try
        {
            if($load_start_dt > $load_end_dt)
            {
                throw new \Exception("The load start date is greater than the load end date ($load_start_dt > $load_end_dt)");
            }

            error_log("INFO: Started master growCache($load_start_dt, $load_end_dt)");
            $just_keys_ar = array_keys($this->m_detail_ar);
            $just_keys_count = count($just_keys_ar);
            $keys_tx = implode(",",$just_keys_ar);
            error_log("INFO: master level starting [$load_start_dt, $load_end_dt] with detail count=$just_keys_count keys=$keys_tx");

            $personid=NULL;
            $include_daily_detail=TRUE;
            $today_dt=NULL;

            $detail_ar = [];
            $attempt_count = 0;
            $local_load_start_dt=$load_start_dt;
            $local_load_end_dt=$load_end_dt;
            $fetch_detail = empty($this->m_detail_ar[$local_load_start_dt]) || empty($this->m_detail_ar[$local_load_end_dt]);
            while($fetch_detail)
            {
                //Work around odd bug by running query until we get all the dates
                $raw = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($local_load_start_dt, $local_load_end_dt, $personid, $include_daily_detail, $today_dt);
                $detail_ar = $raw['daily_detail'];
                foreach($detail_ar as $dt=>$detail)
                {
                    //Add this detail to our cache
                    $this->m_detail_ar[$dt] = $detail;
                }
                if($attempt_count > 10)
                {
                    error_log("ERROR: Failed master because attempted to growCache($load_start_dt, $load_end_dt) more than $attempt_count times!");
                    throw new \Exception("Attempted to growCache($load_start_dt, $load_end_dt) more than $attempt_count times!");
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
                        error_log("WARNING: looping growCache($load_start_dt, $load_end_dt) attempt_count=$attempt_count now at [$local_load_start_dt,$local_load_end_dt]");
                    }
                }
                $attempt_count++;
                $fetch_detail = empty($this->m_detail_ar[$local_load_start_dt]) || empty($this->m_detail_ar[$local_load_end_dt]);
            }
            error_log("INFO: master growCache($load_start_dt, $load_end_dt) done with detail loop after $attempt_count iterations [$local_load_start_dt,$local_load_end_dt]");
            
            if(count($this->m_detail_ar) == 0)
            {
                //This is now our data
                throw new \Exception("Did NOT find any date detail for [$load_start_dt,$load_end_dt]");
                //$this->m_first_dt = $load_start_dt;
                //$this->m_last_dt = $load_end_dt;
                //$this->m_detail_ar = $raw['daily_detail'];
            }
            
            if($this->m_first_dt > $load_start_dt)
            {
                $this->m_first_dt = $load_start_dt;
            }
            
            if($this->m_last_dt < $load_end_dt)
            {
                $this->m_last_dt = $load_end_dt;
            }
            
            if(empty($this->m_detail_ar[$load_end_dt]))
            {
                reset($this->m_detail_ar);
                $first_member_key = key($this->m_detail_ar);

                end($this->m_detail_ar);         // move the internal pointer to the end of the array
                $last_member_key = key($this->m_detail_ar);  // fetches the key of the element pointed to by the internal pointer

                end($detail_ar);         // move the internal pointer to the end of the array
                $last_local_key = key($this->m_detail_ar);  // fetches the key of the element pointed to by the internal pointer

                $usererrormsg = "Notify support that master detail is missing '$load_end_dt' in detail (member:[$first_member_key,$last_member_key] and last local=$last_local_key). Try again.";
                error_log('ERROR (USER INFORMED):'.$usererrormsg);
                foreach($raw as $key=>$detail)
                {
                    error_log("ERROR see RAW[$key] growCache($load_start_dt, $load_end_dt) MISSING end date '$load_end_dt' in the local detail) TEXT CHUNKS START...");
                    $logmsg1 = \bigfathom\DebugHelper::getNeatTextMarkup($detail,"TEXT DETAIL",'error');
                    $chunks = \bigfathom\DebugHelper::getLargeTextInChunks($logmsg1);
                    foreach($chunks as $offset=>$chunk)
                    {
                        error_log("RAW[$key] CHUNK#$offset>>> $chunk");
                    }
                    error_log("ERROR see RAW[$key] growCache($load_start_dt, $load_end_dt) MISSING end date '$load_end_dt' in the local detail) TEXT CHUNKS DONE!");
                }
                $logmsg2 = \bigfathom\DebugHelper::getNeatTextMarkup($this->m_detail_ar,"ERROR member DETAIL growCache($load_start_dt, $load_end_dt) MISSING end date '$load_end_dt'",'error');
                error_log($logmsg2);
                $logmsg3 = \bigfathom\DebugHelper::getStackTraceMarkup("ERROR growCache($load_start_dt, $load_end_dt) missing load_end_dt");
                error_log($logmsg3);
                throw new \Exception($usererrormsg);
            }
            ksort($this->m_detail_ar);
            //\bigfathom\DebugHelper::showNeatMarkup($this->m_detail_ar,"growCache($load_start_dt, $load_end_dt) LOOK",'status');

            foreach($this->m_map_personid2pai as $personid=>$pai)
            {
                error_log("INFO: master growCache($load_start_dt, $load_end_dt) about to launch PAI loadStaticWorkitems($load_start_dt, $load_end_dt)");
                $pai->loadStaticWorkitems($load_start_dt, $load_end_dt);
            }
            if(!isset($this->m_detail_ar[$load_start_dt]))
            {
                $just_keys_ar = array_keys($this->m_detail_ar);
                $just_keys_count = count($just_keys_ar);
                $keys_tx = implode(",",$just_keys_ar);
                $err_tx = "Missing master level load start date from [$load_start_dt, $load_end_dt] in detail array! detail count=$just_keys_count keys=$keys_tx";
                error_log("ERROR: $err_tx");
                throw new \Exception($err_tx);
            }
            if(!isset($this->m_detail_ar[$load_end_dt]))
            {
                $just_keys_ar = array_keys($this->m_detail_ar);
                $just_keys_count = count($just_keys_ar);
                $keys_tx = implode(",",$just_keys_ar);
                $err_tx = "Missing master level load end date from [$load_start_dt, $load_end_dt] in detail array! detail count=$just_keys_count keys=$keys_tx";
                error_log("ERROR: $err_tx");
                throw new \Exception($err_tx);
            }
            error_log("INFO: Finished master growCache($load_start_dt, $load_end_dt)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllLoadedStaticWorkitemsByPersonID()
    {
        try
        {
            $bundle = [];
            foreach($this->m_map_personid2pai as $personid=>$pai)
            {
                $bundle[$personid] = $pai->getAllLoadedStaticWorkitems();
            }
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllDetailBundle($include_start_dt=NULL, $include_end_dt=NULL)
    {
        $effective_first_dt = $this->getFirstDate();
        $effective_last_dt = $this->getLastDate();
        if(empty($include_start_dt))
        {
            $include_start_dt = $effective_first_dt;
        }
        if(empty($include_end_dt))
        {
            $include_end_dt = $effective_last_dt;
        }
        if($include_start_dt > $include_end_dt)
        {
            throw new \Exception("Cannot process backward range date [$include_start_dt,$include_end_dt]!");
        }
        $this->updateLocalCache($include_start_dt, $include_end_dt);
        $bundle = array('first_dt'=>$effective_first_dt, 'last_dt'=>$effective_last_dt, 'daily_detail'=>$this->m_detail_ar);
        return $bundle;
    }
}
