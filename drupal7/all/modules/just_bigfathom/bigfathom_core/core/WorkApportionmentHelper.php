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
 * POSSIBLE CANDIATE FOR DEPRECATOIN -- USE LOGIC FROM AUTOFILL MODULE INSTEAD
 * This class provides help figuring out work apportionment
 * 
 * APPORTIONMENT APPROACH: Assume the work happens as a constant % of available time
 *                         over entire period from start to end on the days that the
 *                         person is available for work.
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class WorkApportionmentHelper
{
    
    public function __construct()
    {
      
    }
    
    public static function getAssessmentOfInterval($interval_info)
    {
        $bundle = [];
        if(!empty($interval_info))
        {
            $af = round($interval_info['af'],2);
            $bundle['is_ok'] = ($af <= 1);
            if($af < .001)
            {
                $bundle['is_gap'] = TRUE;
                $busyword = 'zero';
            } else {
                $bundle['is_gap'] = FALSE;
                if($af <= .25)
                {
                    $busyword = "very low";
                } else
                if($af <= .5)
                {
                    $busyword = "low";
                } else
                if($af <= .75)
                {
                    $busyword = "moderate";
                } else
                if($af <= .90)
                {
                    $busyword = "high";
                } else {
                    $busyword = "very high";
                }
            }
            $bundle['busyword'] = $busyword;
        }
        return $bundle;
    }
   
    
    public function getMergedIntervalUtilizationByPerson($personid_ar)
    {
        try
        {
            if(empty($personid_ar) || !is_array($personid_ar))
            {
                throw new \Exception("Missing expected personid map!");
            }
            $input['personid_list'] = $personid_ar;
            
            $personid2pui = [];
            $people2keys = [];
            foreach($personid_ar as $personid)
            {
                $pui = new \bigfathom\DEPRECATED_OLD_PersonUtilizationInsight($personid);
                $personid2pui[$personid] = $pui;
                $pug = $pui->getUtilizationAndGapsDataBundleOfPerson();
                $by_workitem = $pug['lookup']['workitem'];
                foreach($by_workitem as $wid=>$wdetail)
                {
                    $projectid = $wdetail['owner_projectid'];
                    $personid = $wdetail['owner_personid'];
                    $people2keys[$personid]['map']['wid'][$wid] = $wid;
                    $people2keys[$personid]['map']['projectid'][$projectid] = $projectid;
                }
            }
            
            foreach($people2keys as $personid=>$relevant_keys)
            {
                //$utilization4one_person = $by_personid[$personid]['smartbucket']->getComputedNumberData();
                $pui = $personid2pui[$personid];
                $smartbucketinfo = $pui->getSmartBucketInfo();
                $pug = $pui->getUtilizationAndGapsDataBundleOfPerson();
                $by_workitem = $pug['lookup']['workitem'];
                $oneperson_intervals_mashup = [];
                foreach($smartbucketinfo['intervals'] as $oneintervalset)
                {
                    $wid2interval = $oneintervalset['plain']['intervals'];
                    foreach($wid2interval as $wid=>$i_info)
                    {
                        $wdetail = $by_workitem[$wid];
                        $projectid = $wdetail['owner_projectid'];
                        $isdt = $i_info['sdt'];
                        if(!isset($oneperson_intervals_mashup[$isdt]['af']))
                        {
                            $oneperson_intervals_mashup[$isdt]['sdt'] = $isdt;
                            $oneperson_intervals_mashup[$isdt]['edt'] = $i_info['edt'];
                            $oneperson_intervals_mashup[$isdt]['idx'] = $i_info['idx'];
                            $oneperson_intervals_mashup[$isdt]['twd'] = $i_info['twd']; //TOTAL WORK DAYS
                            $oneperson_intervals_mashup[$isdt]['twh'] = $i_info['twh'];
                            $oneperson_intervals_mashup[$isdt]['af'] = 0;
                            $oneperson_intervals_mashup[$isdt]['reh'] = 0;
                            $oneperson_intervals_mashup[$isdt]['maps']['workitem'] = [];
                            $oneperson_intervals_mashup[$isdt]['maps']['project'] = [];
                        }
                        $oneperson_intervals_mashup[$isdt]['af'] += $i_info['af'];
                        $oneperson_intervals_mashup[$isdt]['reh'] += $i_info['reh'];
                        $oneperson_intervals_mashup[$isdt]['maps']['workitem'][$wid] = $wid;
                        $oneperson_intervals_mashup[$isdt]['maps']['project'][$projectid] = $projectid;
                    }
                    //Clean up the floating point mess and fill in gaps
                    foreach($oneperson_intervals_mashup as $isdt=>$i_info)
                    {
                        if($i_info['reh'] < .01)
                        {
                            //Just zero it out
                            $i_info['af'] = 0;
                            $i_info['reh'] = 0;
                        }
                        $people2keys[$personid]['map']['interval_smash'][$isdt] = $i_info;
                    }
                }
            }
            
            $bundle = [];
            $bundle['metadata']['input'] = $input;
            $bundle['by_person'] = $people2keys;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Just the available hours per day without factoring in if they are busy
     */
    public function getDailyDetailForPerson($starting_dt, $ending_dt, $personid)
    {
        $include_daily_detail = TRUE;
        $seeking_effort_hours = NULL;
        $ignore_seeking = TRUE;
        $direction = 1;
        $min_hours_to_start_work = NULL;
        $maxdays = NULL;
        $today_dt = $starting_dt;
        $existing_utilization_bundle = NULL;
        $has_locked_start_dt = TRUE;
        $has_locked_end_dt = TRUE;
        $min_pct_buffer = NULL;
        $strict_min_pct = FALSE;
        $map_wid_allocations2ignore = NULL;
        $dailydetailbundle 
                = UtilityGeneralFormulas::getWorkEffortComputedDateBundle($starting_dt, $ending_dt, $personid
                        , $include_daily_detail
                        , $seeking_effort_hours, $ignore_seeking
                        , $direction
                        , $min_hours_to_start_work
                        , $maxdays
                        , $today_dt
                        , $existing_utilization_bundle
                        , $has_locked_start_dt
                        , $has_locked_end_dt
                        , $min_pct_buffer
                        , $strict_min_pct
                        , $map_wid_allocations2ignore);
        return $dailydetailbundle;
    }
    
    /**
     * Provide some utilization percentages we should consider using
     * Computes % with assumption that person will work on one project at a time
     * - static = elements that cannot be changed
     * - editable = elements that can be changed
     */
    public function getUtilizationInfoBundle($utilization_planning_bundle, $movable_workitems_map)
    {
        try
        {
            if(empty($utilization_planning_bundle))
            {
                throw new \Exception("Missing required utilization_planning_bundle!!!");
            }
            if($movable_workitems_map === NULL || !is_array($movable_workitems_map))
            {
                throw new \Exception("Missing required movable_workitems_map!!!");
            }
            
            $reference_start_dt = $utilization_planning_bundle['metadata']['reference_dt'];
            $shift_days = 1994;
            $reference_end_dt= UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($reference_start_dt, $shift_days);
            $proposal_bundle = [];
            
            $dayinfo4person = [];
            
            $domain = $utilization_planning_bundle['by_workitem'];
            $dom_by_person = $utilization_planning_bundle['by_person'];
            $dom_by_project = $utilization_planning_bundle['by_project'];
            
            $static_utilpct = [];
            $editable_utilpct = [];
            $static_workitems_map = [];
            
            $sorted_by_person_moveable_workitems = [];

            $maxmin4person = [];
            foreach($domain as $wid=>$detail)
            {
                $personid = $detail['owner_personid'];
                $effective_start_dt = $detail['effective_start_dt'];
                $effective_end_dt = $detail['effective_end_dt'];
                
                if(empty($effective_start_dt))
                {
                    $maxmin4person[$personid]['min_dt'] = $reference_start_dt;
                } else {
                    if(empty($maxmin4person[$personid]['min_dt']) || $maxmin4person[$personid]['min_dt'] > $effective_start_dt)
                    {
                        $maxmin4person[$personid]['min_dt'] = $effective_start_dt;
                    }
                }
                if(empty($effective_end_dt))
                {
                    $maxmin4person[$personid]['max_dt'] = $reference_end_dt;
                } else {
                    if(empty($maxmin4person[$personid]['max_dt']) || $maxmin4person[$personid]['max_dt'] < $effective_end_dt)
                    {
                        $maxmin4person[$personid]['max_dt'] = $effective_end_dt;
                    }
                }
            }
            $total_static_utilpct = [];
            foreach($domain as $wid=>$detail)
            {
                $personid = $detail['owner_personid'];
                $projectid = $detail['owner_projectid'];
                if(empty($total_static_utilpct[$personid]))
                {
                    $total_static_utilpct[$personid]['dumb_sum_pct'] = 0;
                    $total_static_utilpct[$personid]['remaining_effort_hours']  = 0;
                    $total_static_utilpct[$personid]['work_hours'] = 0;
                }
                if(empty($total_static_utilpct[$personid]['by_project'][$projectid]))
                {
                    $total_static_utilpct[$personid]['by_project'][$projectid]['dumb_sum_pct'] = 0;
                    $total_static_utilpct[$personid]['by_project'][$projectid]['remaining_effort_hours'] = 0;
                    $total_static_utilpct[$personid]['by_project'][$projectid]['work_hours'] = 0;
                }
                if(empty($dayinfo4person[$personid]))
                {
                    $min_dt = $maxmin4person[$personid]['min_dt'];
                    $max_dt = $maxmin4person[$personid]['max_dt'];
                    $ddb = $this->getDailyDetailForPerson($min_dt, $max_dt, $personid);
                    $dd_first_dt = $ddb['first_dt'];
                    $dd_last_dt = $ddb['last_dt'];
                    $daily_detail = $ddb['daily_detail'];
                    $dd_info = array('first_dt'=>$dd_first_dt,'last_dt'=>$dd_last_dt,'daily_detail'=>$daily_detail);
                    $dayinfo4person[$personid] = $dd_info;
                }
                if(!array_key_exists($wid, $movable_workitems_map))
                {
                    $ddb = $dayinfo4person[$personid];
                    $daily_detail = $ddb['daily_detail'];
                    $static_workitems_map[$wid] = $wid;
                    $effective_start_dt = $detail['effective_start_dt'];
                    $effective_end_dt = $detail['effective_end_dt'];
                    if(empty($effective_start_dt))
                    {
                        $effective_start_dt = $maxmin4person[$personid]['min_dt'];
                    }
                    if(empty($effective_end_dt))
                    {
                        $effective_end_dt = $maxmin4person[$personid]['max_dt'];
                    }
                    $nsdt = NULL;
                    $nedt = NULL;
                    if($effective_start_dt > $ddb['first_dt'])
                    {
                        $nsdt = $effective_start_dt;
                        $ddb = $this->getDailyDetailForPerson($min_dt, $max_dt, $personid);
                        $daily_detail = $ddb['daily_detail'];
                    }
                    if($effective_end_dt < $ddb['last_dt'])
                    {
                        $nedt = $effective_end_dt;
                    }
                    if(!empty($nsdt) || !empty($nedt))
                    {
                        $min_dt = empty($nsdt) ? $effective_start_dt : $nsdt;
                        $max_dt = empty($nedt) ? $effective_end_dt : $nedt;
                        $ddb = $this->getDailyDetailForPerson($min_dt, $max_dt, $personid);
                        $daily_detail = $ddb['daily_detail'];
                    }
                    $swsb = $this->getSimpleWorkhoursSummationBundle($effective_start_dt,$effective_end_dt,$daily_detail,FALSE);
                    $total_work_hours_in_period = $swsb['work_hours'];
                    $remaining_effort_hours = $detail['remaining_effort_hours'];
                    $static_utilpct[$wid] = $this->getWorkhoursApportionmentFactor($remaining_effort_hours, $total_work_hours_in_period);
                    $total_static_utilpct[$personid]['sdt'] = $effective_start_dt;
                    $total_static_utilpct[$personid]['edt'] = $effective_end_dt;
                    $total_static_utilpct[$personid]['remaining_effort_hours'] += $remaining_effort_hours;
                    $total_static_utilpct[$personid]['work_hours'] += $total_work_hours_in_period;
                    $total_static_utilpct[$personid]['dumb_sum_pct'] += $static_utilpct[$wid];
                    $total_static_utilpct[$personid]['by_project'][$projectid]['remaining_effort_hours'] += $remaining_effort_hours;
                    $total_static_utilpct[$personid]['by_project'][$projectid]['work_hours'] += $total_work_hours_in_period;
                    $total_static_utilpct[$personid]['by_project'][$projectid]['dumb_sum_pct'] += $static_utilpct[$wid];
                }
            }
            
            $editable_utilpct['all_workitems']['dumb_sum_pct'] = 0;
            $editable_utilpct['all_workitems']['each_pct'] = [];
            $editable_utilpct['by_person'] = [];
            foreach($dom_by_project as $projectid=>$projectbundle)
            {
                foreach($dom_by_person as $personid=>$personbundle)
                {
                    if(!empty($personbundle['map']['projid2wid'][$projectid]))
                    {
                        if(empty($editable_utilpct['by_person'][$personid]))
                        {
                            $editable_utilpct['by_person'][$personid] = [];
                            $editable_utilpct['by_person'][$personid]['dumb_sum_pct'] = 0;
                            $editable_utilpct['by_person'][$personid]['each_pct'] = [];
                        }
                        $workitems_of_person = $personbundle['map']['projid2wid'][$projectid];
                        $sorted_moveable_workitems_of_person = [];
                        $root_wid = !empty($personbundle['map']['project_root_wids'][$projectid]) ? $personbundle['map']['project_root_wids'][$projectid] : NULL;
                        foreach($workitems_of_person as $wid)
                        {
                            if(array_key_exists($wid, $movable_workitems_map))
                            {
                                $sorted_moveable_workitems_of_person[$wid] = $wid;
                                $sorted_by_person_moveable_workitems[$personid][$wid] = $wid;
                            }
                        }
                        //We add the root wid last to shift it last.
                        if(!empty($root_wid) && array_key_exists($root_wid, $movable_workitems_map))
                        {
                            $sorted_moveable_workitems_of_person[$root_wid] = $root_wid;
                            $sorted_by_person_moveable_workitems[$personid][$root_wid] = $root_wid;
                        }
                        $moveable_workitem_count = count($sorted_moveable_workitems_of_person);
                        if($moveable_workitem_count > 0)
                        {
                            $total_avail_utilpct = 0.9995; //Ignore the static allocations for this logic step here BECAUSE can be 100% for interval! - $total_static_utilpct[$personid]['by_project'][$projectid]['dumb_sum_pct'];
                            //$total_avail_utilpct = 0.9995 - $total_static_utilpct[$personid]['by_project'][$projectid]['dumb_sum_pct'];
                            if($moveable_workitem_count === 1)
                            {
                                //Only one workitem, allocate all available time.
                                $apportion = $total_avail_utilpct;
                                $root_apportion = $apportion;
                            } else {
                                //Share available time across all items.
                                $apportion = $total_avail_utilpct / $moveable_workitem_count;
                                if($apportion > .1 && !empty($root_wid))
                                {
                                    //The root workitem gets a smaller allocation that other work.
                                    $root_apportion = $apportion / 2;
                                    $realocate = $apportion - $root_apportion;
                                    $apportion = $apportion + ($realocate / ($moveable_workitem_count - 1));
                                } else {
                                    //Treat same as another other workitem in the project
                                    $root_apportion = $apportion;
                                }
                            }
                            foreach($sorted_moveable_workitems_of_person as $wid)
                            {
                                if($wid == $root_wid)
                                {
                                    $editable_utilpct['all_workitems']['dumb_sum_pct'] += $root_apportion;
                                    $editable_utilpct['all_workitems']['each_pct'][$wid] = $root_apportion;
                                    $editable_utilpct['by_person'][$personid]['dumb_sum_pct'] += $root_apportion;
                                    $editable_utilpct['by_person'][$personid]['each_pct'][$wid] = $root_apportion;
                                } else {
                                    $editable_utilpct['all_workitems']['dumb_sum_pct'] += $apportion;
                                    $editable_utilpct['all_workitems']['each_pct'][$wid] = $apportion;
                                    $editable_utilpct['by_person'][$personid]['dumb_sum_pct'] += $apportion;
                                    $editable_utilpct['by_person'][$personid]['each_pct'][$wid] = $apportion;
                                }
                                if($editable_utilpct['all_workitems']['each_pct'][$wid] <= 0)
                                {
                                    if($wid == $root_wid)
                                    {
                                        $errtopic = "Detected zero/negative apportionment for moveable ROOT wid=$wid of personid=$personid! (root_apportion=$root_apportion)";
                                    } else {
                                        $errtopic = "Detected zero/negative apportionment for moveable wid=$wid of personid=$personid! (apportion=$apportion)";
                                    }
                                    $st = DebugHelper::getStackTraceMarkup();
                                    DebugHelper::debugPrintNeatly(
                                            array('##$moveable_workitems_of_person'=>$sorted_moveable_workitems_of_person
                                            , '##$total_static_utilpct'=>$total_static_utilpct
                                            , '##$editable_utilpct'=>$editable_utilpct
                                            , '##stacktrace'=>$st), FALSE
                                            , "ERROR DETAIL $errtopic .....","..... ERROR DETAIL",'error');
                                    throw new \Exception("$errtopic detail=" . print_r($editable_utilpct,TRUE) 
                                            . " moveable_workitems_of_person=" . print_r($sorted_moveable_workitems_of_person,TRUE));
                                }
                            }
                        }
                    }
                }
            }
            
            $proposal_bundle['metadata']['movable'] = $movable_workitems_map;
            $proposal_bundle['metadata']['static'] = $static_workitems_map;
            $proposal_bundle['metadata']['moveable_workitems4person'] = $sorted_by_person_moveable_workitems;
            $proposal_bundle['utilization']['static']['by_workitem'] = $static_utilpct;
            $proposal_bundle['utilization']['editable'] = $editable_utilpct;
            $proposal_bundle['utilization']['static']['total_by_person'] = $total_static_utilpct;
//DebugHelper::debugPrintNeatly($proposal_bundle,FALSE,"LOOK debugPrintNeatly .....", "...... debugPrintNeatly");          
            return $proposal_bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Bundle of work still not completed as of the reference date which is owned by relevant people
     */
    public function getPersonUtilizationPlanningBundle($relevant_personid_ar, $reference_dt=NULL, $active_yn=1)
    {
        $sql = NULL;
        try
        {
            $bundle = [];
            $metadata = [];
            $by_workitem = [];
            $by_person = [];
            $by_project = [];
            $just_root_wids = [];
            
            if(empty($relevant_personid_ar))
            {
                throw new \Exception("Missing required personid list!");
            }
            if(empty($reference_dt))
            {
                $now_timestamp = time();
                $today_dt = gmdate("Y-m-d",$now_timestamp);
                $reference_dt = $today_dt;
            }
            $metadata['reference_dt'] = $reference_dt;
            $metadata['relevant_personid_list'] = array_keys($relevant_personid_ar);
            
            $personid_list_tx = implode(',', $relevant_personid_ar);
            
            $sql = "SELECT w.id as wid, w.owner_projectid, w.owner_personid, w.remaining_effort_hours"
                    . " , w.planned_start_dt, w.actual_start_dt"
                    . " , w.planned_end_dt, w.actual_end_dt"
                    . " , w.importance, proj.id as root_of_projectid"
                    . " , w.status_cd, ws.workstarted_yn, ws.happy_yn "
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " w "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename . " ws ON w.status_cd=ws.code"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " proj ON w.id=proj.root_goalid"
                    . " WHERE w.owner_personid IN ($personid_list_tx) "
                    . " AND w.remaining_effort_hours > 0"
                    . " AND ("
                    . "  (w.planned_end_dt IS NULL AND w.actual_end_dt IS NULL) "
                    . "  OR (w.actual_end_dt IS NOT NULL AND w.actual_end_dt >= '$reference_dt')"
                    . "  OR (w.actual_end_dt IS NULL AND w.planned_end_dt IS NOT NULL and w.planned_end_dt >= '$reference_dt')"
                    . " )";
            if($active_yn != NULL)
            {
                $sql .= " AND w.active_yn=$active_yn";    
            }
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $wid = $record['wid'];
                $personid = $record['owner_personid'];
                $projectid = $record['owner_projectid'];
                $root_of_projectid = $record['root_of_projectid'];
                $is_project_root_yn = ($root_of_projectid == $projectid) ? 1 : 0;
                $record['is_project_root_yn'] = $is_project_root_yn;
                
                $record['effective_start_dt'] = !empty($record['actual_start_dt']) ? $record['actual_start_dt'] : $record['planned_start_dt'];
                $record['effective_end_dt'] = !empty($record['actual_end_dt']) ? $record['actual_end_dt'] : $record['planned_end_dt'];

                $by_workitem[$wid] = $record;
                $by_person[$personid]['map']['all'][$wid] = $wid;
                if(empty($by_person[$personid]['map']['projid2wid'][$projectid]))
                {
                    $by_person[$personid]['map']['projid2wid'][$projectid] = [];
                }
                $by_person[$personid]['map']['projid2wid'][$projectid][] = $wid;
                $by_project[$projectid]['map']['all'][$wid] = $wid;
                if($is_project_root_yn)
                {
                    $just_root_wids['all'][$wid] = $wid;
                    $just_root_wids['by_project'][$projectid] = $wid;
                    $by_person[$personid]['map']['project_root_wids'][$projectid] = $wid;
                    $by_project[$projectid]['root_goalid'] = $wid;
                }
            }            
            
            foreach($by_person as $personid=>$detail)
            {
                $workitem_count = count($detail['map']);
                $by_person[$personid]['count'] = $workitem_count;
                if(empty($by_person[$personid]['map']['project_root_wids']))
                {
                    $by_person[$personid]['map']['project_root_wids'] = [];
                }
            }
            
            foreach($by_project as $projectid=>$detail)
            {
                $by_project[$projectid]['count'] = count($detail['map']);
            }
            
            $bundle['metadata'] = $metadata;
            $bundle['by_workitem'] = $by_workitem;
            $bundle['by_person'] = $by_person;
            $bundle['by_project'] = $by_project;
            $bundle['just_root_wids'] = $just_root_wids;
            
            return $bundle;
            
        } catch (\Exception $ex) {
            drupal_set_message("DEBUG LOOK sql=$sql",'error');
            throw new \Exception("Failed getting owned workitems because " . $ex, 551255, $ex);
        }
    }
    
    private function getDailyDetailKeyBundle($daily_detail)
    {
        $keys = array_keys($daily_detail);
        $max = max($keys);
        $min = min($keys);
        return array('min'=>$min,'max'=>$max);
    }
    
    private function getSimpleWorkhoursSummationBundle($start_dt,$end_dt,$daily_detail,$return_day_detail=TRUE)
    {
        $warning_messages = [];
        if(empty($start_dt))
        {
            throw new \Exception("Missing required start date!");
        }
        if(empty($end_dt))
        {
            throw new \Exception("Missing required end date!");
        }
        if(empty($daily_detail[$start_dt]))
        {
            $corefailmsg = "Did NOT find start_dt [$start_dt] in daily_detail";
            if(BIGFATHOM_VERBOSE_DEBUG != 1)
            {
                throw new \Exception("$corefailmsg");
            } else {
                DebugHelper::showStackTrace($corefailmsg, $msgcat='error') ;           
                throw new \Exception("$corefailmsg >> " . print_r($daily_detail,TRUE));
            }
        }
        if(empty($daily_detail[$end_dt]))
        {
            $dd_maxmin = $this->getDailyDetailKeyBundle($daily_detail);
            $max_dt = $dd_maxmin['max'];
            //drupal_set_message("Too many days so computing utilization through $max_dt",'info');
            $warning_messages[] = "Too many days so computing utilization through $max_dt";
            $end_dt = $dd_maxmin['max'];    //Simply use the max date
        }
            
        $total_days = 0;
        $total_hours = 0;
        $counted_days_detail = [];
        $work_hours_history = [];
        $found_start_dt = FALSE;
        $found_end_dt = FALSE;
        foreach($daily_detail as $one_date=>$detail)
        {
            if($one_date >= $start_dt)
            {
                $found_start_dt = TRUE;
                if($detail['isworkday'])
                {
                    $total_days++;
                    $work_hours_history[] = $detail['workhoursinday'];
                    $total_hours += $detail['workhoursinday'];
                    if($return_day_detail)
                    {
                        $counted_days_detail[$one_date] = $detail;
                    }
                }
            }
            if($one_date == $end_dt)
            {
                $found_end_dt = TRUE;
                break;
            }
        }
        $bundle = [];
        $bundle['start_dt'] = $start_dt;
        $bundle['end_dt'] = $end_dt;
        $bundle['work_days'] = $total_days;
        $bundle['work_hours'] = $total_hours;
        $bundle['work_hours_history'] = $work_hours_history;
        $bundle['warning_messages'] = $warning_messages;
        if($return_day_detail)
        {
            $bundle['relevant_days_detail'] = $counted_days_detail;
        }
        if(!$found_start_dt || !$found_end_dt)
        {
            $errinfo = DebugHelper::debugPrintNeatly(array('##bundle'=>$bundle,'##$daily_detail'=>$daily_detail,'##m_start_dt_idx'=>$this->m_start_dt_idx),TRUE,"LOOK daily_detail TROUBLE $start_dt,$end_dt .......",".................. $start_dt,$end_dt");                
            drupal_set_message("LOOK FAILED TO FIND ONE OF OUR DATES IN THE DETAIL! $errinfo","error");
        }
        return $bundle;
    }

    /**
     * We multiply the factor by the raw-work-hours in the day to get the number of hours the person will
     * work in that day on item.
     */
    public function getWorkhoursApportionmentFactor($remaining_effort_hours, $total_work_hours_in_period)
    {
        if($total_work_hours_in_period < 1)
        {
            return NULL;
        } else {
            return $remaining_effort_hours / $total_work_hours_in_period;
        }
    }

    private function setIntervalApportionmentsFromSummationMapBundle(&$all_intervals_info, $interval_sum_map_bundle)
    {
        try
        {
            $count_updated_segments = 0;
            $interval_sum_map = $interval_sum_map_bundle['sum_map'];
            foreach($all_intervals_info['by_wid'] as $wid=>$one_wid_intervalinfo)
            {
                foreach($interval_sum_map as $i_offset=>$i_info)
                {
                    if(isset($all_intervals_info['by_wid'][$wid]['lookup']['intervals']['idx2local_offset'][$i_offset]))
                    {
                        $local_offset = $all_intervals_info['by_wid'][$wid]['lookup']['intervals']['idx2local_offset'][$i_offset];
                        if(empty($interval_sum_map[$i_offset]['detail']['reh'][$wid]))
                        {
                            $reh = 0;
                            $af = 0;
                        } else {
                            $reh = $interval_sum_map[$i_offset]['detail']['reh'][$wid];
                            $af = $interval_sum_map[$i_offset]['detail']['af'][$wid];
                        }
                        $count_updated_segments += 1;
                        $all_intervals_info['by_wid'][$wid]['intervals'][$local_offset]['idebuglog'][] = "updated in setIntervalApportionmentsFromSummationMapBundle i_offset=$i_offset local_offset=$local_offset reh=[$reh] af=[$af]";
                        $all_intervals_info['by_wid'][$wid]['intervals'][$local_offset]['status'] = "ready";
                        $all_intervals_info['by_wid'][$wid]['intervals'][$local_offset]['reh'] = $reh;
                        $all_intervals_info['by_wid'][$wid]['intervals'][$local_offset]['af'] = $af;
                    }
                }
            }
            $all_intervals_info['metadata']['set_best_interval_apportionments']['count']['updated_segments'] = $count_updated_segments;
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
    
    /**
     * Get summation of all effort factors for each interval 
     * Assumes all provided interval information is for one person
     */
    private function getIntervalSummationMapBundle($all_intervals_info)
    {
        try
        {
            $sorted_date_pairs = $all_intervals_info['sorted_date_pairs'];
            $sdp_keys = array_keys($sorted_date_pairs);
            $by_workitem = $all_intervals_info['by_wid'];
            
            //Find the overages using baselines
            $sum_map = [];
            $sdpkeys = array_keys($sorted_date_pairs);
            
            for($offset = 0; $offset < count($sdpkeys) ; $offset++)
            {
                $sdp_key = $sdp_keys[$offset];
                $one_pairinfo = $sorted_date_pairs[$sdp_key];
                
                //Initialize ALL the intervals at zero
                $sum_map[$offset]['metadata'] = $one_pairinfo;
                $sum_map[$offset]['summary']['total_reh'] = 0;
                $sum_map[$offset]['summary']['total_af'] = 0;    
                $sum_map[$offset]['summary']['twd'] = 0;
                $sum_map[$offset]['summary']['twh'] = 0;
            }
            
            $map_wid2i_offset = [];
            foreach($by_workitem as $wid=>$one_wid_infochunk)
            {
                $baseline = $one_wid_infochunk['baseline'];
                $baseline_reh = $baseline['reh'];
                if($baseline_reh > 0)
                {
                    //Collect info from this one
                    $intervals = $one_wid_infochunk['intervals'];
                    $map_wid2i_offset[$wid] = [];
                    foreach($intervals as $oneinterval)
                    {
                        $i_offset = $oneinterval['idx'];
                        $map_wid2i_offset[$wid][$i_offset] = $i_offset;
                        $offset_sorted_date_pairs = $oneinterval['idx'];
                        $reh = $oneinterval['reh'];
                        $af = $oneinterval['af'];
                        if(!isset($sum_map[$offset_sorted_date_pairs]['detail']))
                        {
                            $sdp_key = $sdp_keys[$offset_sorted_date_pairs];
                            $one_pairinfo = $sorted_date_pairs[$sdp_key];
                            $twd = $oneinterval['twd'];
                            $twh = $oneinterval['twh'];
                            $sum_map[$offset_sorted_date_pairs] = [];
                            $sum_map[$offset_sorted_date_pairs]['metadata'] = $one_pairinfo;
                            $sum_map[$offset_sorted_date_pairs]['summary'] = [];
                            $sum_map[$offset_sorted_date_pairs]['detail'] = [];
                            $sum_map[$offset_sorted_date_pairs]['summary']['total_reh'] = 0;
                            $sum_map[$offset_sorted_date_pairs]['summary']['total_af'] = 0;
                            $sum_map[$offset_sorted_date_pairs]['summary']['twd'] = $twd;
                            $sum_map[$offset_sorted_date_pairs]['summary']['twh'] = $twh;
                            $sum_map[$offset_sorted_date_pairs]['detail']['reh'] = [];
                            $sum_map[$offset_sorted_date_pairs]['detail']['af'] = [];
                        }
                        $sum_map[$offset_sorted_date_pairs]['summary']['total_reh'] += $reh;
                        $sum_map[$offset_sorted_date_pairs]['summary']['total_af'] += $af;
                        $sum_map[$offset_sorted_date_pairs]['detail']['reh'][$wid] = $reh;
                        $sum_map[$offset_sorted_date_pairs]['detail']['af'][$wid] = $af;
                    }
                }
            }
            
            $bundle = [];
            $bundle['wid2i_offset'] = $map_wid2i_offset;
            $bundle['sum_map'] = $sum_map;
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getFirstAvailableRightTargetIntervalOffset($interval_sum_map, $first_candidate_offset, $last_candidate_offset=NULL, $af_smaller_than=.999)
    {
        $found_offset = -1;
        $check_offset = $first_candidate_offset;
        if($last_candidate_offset === NULL)
        {
            $last_candidate_offset = count($interval_sum_map)-1;
        }
        while($check_offset <= $last_candidate_offset)
        {
            if(!empty($interval_sum_map[$check_offset]))
            {
                //We hit a defined interval, check it.
                $interval_info = $interval_sum_map[$check_offset];
                if($interval_info['summary']['twh'] > 0 && $interval_info['summary']['total_af'] < $af_smaller_than)
                {
                    //This interval has some space!
                    $found_offset = $check_offset;
                    break;
                }
            }
            $check_offset++;   //Keep looking for an interval with room
        }
        return $found_offset;
    }

    /**
     * Shift work effort hours from one workitem interval into another interval
     * of the same workitem.
     * GRANULARITY: Existing intervals
     */
    public function shiftHoursFromSourceToTargetInterval(&$interval_sum_map_bundle, $onetargetwid, $source_i_offset, $target_i_offset, $only_shift_overage_yn=0)
    {
        try
        {
            $shifted_yn = 0;
            $interval_sum_map = &$interval_sum_map_bundle['sum_map'];
            $current_interval_info = &$interval_sum_map[$source_i_offset];
            $target_interval_info = &$interval_sum_map[$target_i_offset];
            $all_current_wid_reh = $current_interval_info['detail']['reh'][$onetargetwid];
            $debuginfo = [];
            if($only_shift_overage_yn)
            {
                //We will try to shift only the overage
                $debuginfo[]='only_overage';
                $current_total_reh = $current_interval_info['summary']['total_reh'];
                $current_hours_overage = $current_total_reh - $current_interval_info['summary']['twh'];
                $reh_available2shift = min($current_hours_overage, $all_current_wid_reh);
                $debuginfo[]="$reh_available2shift = min($current_hours_overage, $all_current_wid_reh)";
            } else {
                //We will try to shift it all
                $reh_available2shift = $all_current_wid_reh;
            }
            $target_capacity = max(0,$target_interval_info['summary']['twh'] - $target_interval_info['summary']['total_reh']);
            $reh2shift = min($target_capacity,$reh_available2shift);
            $debuginfo[]="target_capacity = $target_capacity = max(0,{$target_interval_info['summary']['twh']} - {$target_interval_info['summary']['total_reh']})";
            $debuginfo[]="reh2shift = $reh2shift = min($target_capacity,$reh_available2shift)";

            if($reh2shift > 0)
            {
                $existing_cur_summary_total_reh = $current_interval_info['summary']['total_reh'];
                $existing_cur_summary_total_af = $current_interval_info['summary']['total_af'];
                $existing_cur_summary_twh = $current_interval_info['summary']['twh'];
                $existing_cur_detail_reh = $current_interval_info['detail']['reh'][$onetargetwid];

                $existing_tar_summary_total_reh = $target_interval_info['summary']['total_reh'];
                $existing_tar_summary_total_af = $target_interval_info['summary']['total_af'];
                $existing_tar_summary_twh = $target_interval_info['summary']['twh'];
                $existing_tar_detail_reh = $target_interval_info['detail']['reh'][$onetargetwid];

                $new_cur_summary_reh = $existing_cur_summary_total_reh - $reh2shift; //round($existing_cur_summary_total_reh - $reh2shift, 2);
                $new_cur_summary_af = $this->getWorkhoursApportionmentFactor($new_cur_summary_reh, $existing_cur_summary_twh);

                $interval_sum_map_bundle['sum_map'][$source_i_offset]['summary']['total_reh'] = $new_cur_summary_reh;
                $interval_sum_map_bundle['sum_map'][$source_i_offset]['summary']['total_af'] = $new_cur_summary_af;

                $new_cur_detail_reh = $existing_cur_detail_reh - $reh2shift; //round($existing_cur_detail_reh - $reh2shift, 2);
                $new_cur_detail_af = $this->getWorkhoursApportionmentFactor($new_cur_detail_reh, $existing_cur_summary_twh);

                $interval_sum_map_bundle['sum_map'][$source_i_offset]['detail']['reh'][$onetargetwid] = $new_cur_detail_reh;
                $interval_sum_map_bundle['sum_map'][$source_i_offset]['detail']['af'][$onetargetwid] = $new_cur_detail_af;

                $new_tar_summary_reh = $existing_tar_summary_total_reh + $reh2shift; //round($existing_tar_summary_total_reh + $reh2shift, 2);
                $new_tar_summary_af = $this->getWorkhoursApportionmentFactor($new_tar_summary_reh, $existing_tar_summary_twh);

                $interval_sum_map_bundle['sum_map'][$target_i_offset]['summary']['total_reh'] = $new_tar_summary_reh;
                $interval_sum_map_bundle['sum_map'][$target_i_offset]['summary']['total_af'] = $new_tar_summary_af;

                $new_tar_detail_reh = $existing_tar_detail_reh + $reh2shift; //round($existing_tar_detail_reh + $reh2shift, 2);
                $new_tar_detail_af = $this->getWorkhoursApportionmentFactor($new_tar_detail_reh, $existing_tar_summary_twh);

                $interval_sum_map_bundle['sum_map'][$target_i_offset]['detail']['reh'][$onetargetwid] = $new_tar_detail_reh;
                $interval_sum_map_bundle['sum_map'][$target_i_offset]['detail']['af'][$onetargetwid] = $new_tar_detail_af;

                $shifted_yn = 1;
            }
            
            $bundle['shifted_yn'] = $shifted_yn;
            $bundle['reh2shift'] = $reh2shift;
            $bundle['tar_capacity'] = $target_capacity;
            if($reh2shift > 0)
            {
                $bundle['new_cur_detail_af'] = $new_cur_detail_af;
                $bundle['new_tar_detail_af'] = $new_tar_detail_af;
                $bundle['new_cur_summary_af'] = $new_cur_summary_af;
                $bundle['new_tar_summary_af'] = $new_tar_summary_af;
            }
            $bundle['debuginfo'] = $debuginfo;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * Moves all effort from SOURCE INTERVAL to TARGET LEFT-MOST intervals possible
     * GRANULARITY: Existing intervals
     */
    private function shiftEffortLeft(&$interval_sum_map_bundle, $start_source_i_offset=1)
    {
        try
        {
            $interval_sum_map = &$interval_sum_map_bundle['sum_map'];
            $wid2i_offset = &$interval_sum_map_bundle['wid2i_offset'];
            $iteration_counter=0;
            $interval_count = count(array_keys($interval_sum_map));
            $wid_count = count(array_keys($wid2i_offset));
            
            //Create a sorted map of wids and intervals
            $map_i_to_wids = []; //Sorted lowest i to highest with collection of wids at each i
            foreach($wid2i_offset as $potential_target_wid=>$map_intervals_of_the_wid)
            {
                $map_intervals_of_the_wid = $wid2i_offset[$potential_target_wid];
                $lowest_i_of_the_wid = reset($map_intervals_of_the_wid);
                $highest_i_of_the_wid = end($map_intervals_of_the_wid);

                if(!isset($map_i_to_wids[$lowest_i_of_the_wid]))
                {
                    $map_i_to_wids[$lowest_i_of_the_wid] = [];
                }
                $map_i_to_wids[$lowest_i_of_the_wid][$potential_target_wid] = $potential_target_wid;
            }
            $shift_wid_track = [];
            if(count($map_i_to_wids) > 0)
            {
                //Okay, we can try to move some, lets do it.
                $interval_sum_map_bundle['metadata']['shiftEffortLeft']['log'][] = "sorted_map_i_to_wids=" . DebugHelper::debugPrintNeatly($map_i_to_wids, TRUE);
                ksort($map_i_to_wids);
                
                //Now loop through all the source intervals
                for($source_i_offset=$start_source_i_offset; $source_i_offset<$interval_count; $source_i_offset++)
                {
                    //Process this interval into targets until empty
                    $iteration_counter++;
                    $current_interval_info = &$interval_sum_map[$source_i_offset];
                    for($pti1=0; $pti1<$source_i_offset; $pti1++)
                    {
                        if(isset($map_i_to_wids[$pti1]))
                        {
                            
                            //We have wids in this target, process the ones also in current offset
                            $potential_wids = $map_i_to_wids[$pti1];
                            foreach($potential_wids as $onetargetwid)
                            {
                                //Now run with this one all the way to the current interval or as close as we can get
                                for($potential_target_i=$pti1; $potential_target_i<$source_i_offset; $potential_target_i++)
                                {
                                    if(!isset($wid2i_offset[$onetargetwid][$potential_target_i]))
                                    {
                                        //Move onto the next source interval, there are no more targets for this wid
                                        break;
                                    }
                                    
                                    if(isset($current_interval_info['detail']['reh'][$onetargetwid]))
                                    {
                                        
                                        $shfs2t = $this->shiftHoursFromSourceToTargetInterval($interval_sum_map_bundle, $onetargetwid, $source_i_offset, $potential_target_i);
                                        if($shfs2t['shifted_yn'])
                                        {
                                            $shift_wid_track[$onetargetwid][] = "$source_i_offset into $potential_target_i";
                                            $interval_sum_map_bundle['metadata']['shiftEffortLeft']['log'][] = "Shifted hours from i#{$source_i_offset} to i#{$potential_target_i} " . print_r($shfs2t,TRUE);
                                            if($shfs2t['new_cur_detail_af'] <= .00001)
                                            {
                                                //Move onto the next wid, this one is empty
                                                $interval_sum_map_bundle['metadata']['shiftEffortLeft']['log'][] = "Moved enough hours from i#{$source_i_offset} to i#{$potential_target_i}";
                                                break;
                                            }
                                        }
                                    }
                                } //END LOOP TARGET INTERVAL OFFSETS
                            } //END LOOP TARGET WIDS
                        }
                    }
                }
            }
            $interval_sum_map_bundle['metadata']['shiftEffortLeft']['log'][] = "Done! shift_wid_track=" . print_r($shift_wid_track,TRUE);
            $result_bundle = array('shift_wid_track'=>$shift_wid_track);
            return $result_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * SHIFT as far right as possible
     * GRANULARITY: Existing intervals
     */
    private function shiftEffortRight(&$interval_sum_map_bundle)
    {
        try
        {
            $interval_sum_map = &$interval_sum_map_bundle['sum_map'];
            $wid2i_offset = &$interval_sum_map_bundle['wid2i_offset'];
            $iteration_counter=0;
            $interval_count = count(array_keys($interval_sum_map));
            $wid_count = count(array_keys($wid2i_offset));
            
            //Create a sorted map of wids and intervals
            $map_lowest_i_to_wids = []; //Sorted keys
            $map_highest_i_to_wids = []; //Sorted keys
            $map_wid2isactive = [];
            foreach($wid2i_offset as $potential_target_wid=>$map_intervals_of_the_wid)
            {
                $map_wid2isactive[$potential_target_wid] = FALSE;
                $map_intervals_of_the_wid = $wid2i_offset[$potential_target_wid];
                $lowest_i_of_the_wid = reset($map_intervals_of_the_wid);
                $highest_i_of_the_wid = end($map_intervals_of_the_wid); 

                if(!isset($map_lowest_i_to_wids[$lowest_i_of_the_wid]))
                {
                    $map_lowest_i_to_wids[$lowest_i_of_the_wid] = [];
                }
                $map_lowest_i_to_wids[$lowest_i_of_the_wid][$potential_target_wid] = $potential_target_wid;
                if(!isset($map_highest_i_to_wids[$highest_i_of_the_wid]))
                {
                    $map_highest_i_to_wids[$highest_i_of_the_wid] = [];
                }
                $map_highest_i_to_wids[$highest_i_of_the_wid][$potential_target_wid] = $potential_target_wid;
            }
            $sorted_shift_pref = [];
            ksort($map_highest_i_to_wids);  //Ones ending first will appear first
            if($interval_count > 1)
            {
                //Okay, we can try to move some, lets do it.
                $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "wid2i_offset=" . DebugHelper::debugPrintNeatly($wid2i_offset, TRUE);
                foreach($map_highest_i_to_wids as $i=>$wid_ar)
                {
                    sort($wid_ar);  //So the results are repeatable, sort on ID
                    foreach($wid_ar as $wid)
                    {
                        $sorted_shift_pref[] = $wid;
                    }
                }
            }            
            $shift_wid_track = [];
            if(count($sorted_shift_pref) > 0 && $interval_count > 1)
            {
                //Okay, we can try to move some, lets do it.
                $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "sorted_shift_pref=" . DebugHelper::debugPrintNeatly($sorted_shift_pref, TRUE);
                
                //Now loop through all the source intervals
                for($source_i_offset=$interval_count-2; $source_i_offset>=0; $source_i_offset--)
                {
                    //Process this interval into targets until empty
                    $iteration_counter++;
                    $current_interval_info = &$interval_sum_map[$source_i_offset];
                    
                    for($target_i_offset=$interval_count-1; $target_i_offset>$source_i_offset; $target_i_offset--)
                    {
                        //Loop through all the wids
                        foreach($sorted_shift_pref as $onetargetwid)
                        {
                            $map_intervals_of_the_wid = $wid2i_offset[$onetargetwid];
                            if(isset($map_intervals_of_the_wid[$source_i_offset]) && isset($map_intervals_of_the_wid[$target_i_offset]))
                            {
                                //This wid has footprint in both intervals
                                $current_interval_info = &$interval_sum_map[$source_i_offset];
                                $cur_summary_af = $current_interval_info['summary']['total_af'];
                                $tar_summary_af = &$interval_sum_map[$target_i_offset]['summary']['total_af'];
                                $tar_detail_af = &$interval_sum_map[$target_i_offset]['detail']['af'][$onetargetwid];
      if($source_i_offset == 0 || $source_i_offset == 1)// || $target_i_offset=2)
        if($target_i_offset > 2)// || $target_i_offset=2)
            $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "BBBB_OTHER LOOK wid#$onetargetwid from i#{$source_i_offset} to i#{$target_i_offset} CURSUM_AF=$cur_summary_af TARSUM_AF=$tar_summary_af ................................." ;
        else
            $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "BBBB_TO{$target_i_offset} LOOK wid#$onetargetwid from i#{$source_i_offset} to i#{$target_i_offset} CURSUM_AF=$cur_summary_af TARSUM_AF=$tar_summary_af ................................." ;
                                if($cur_summary_af > 0 && $tar_summary_af < 1)
                                {
                                    $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "LOOK wid#$onetargetwid  TARDETAF=$tar_detail_af from i#{$source_i_offset}(af=$cur_summary_af) to i#{$target_i_offset}(af=$tar_summary_af) ........" ;
                                    $shfs2t = $this->shiftHoursFromSourceToTargetInterval($interval_sum_map_bundle, $onetargetwid, $source_i_offset, $target_i_offset);
                                    if($shfs2t['shifted_yn'])
                                    {
                                        $shift_wid_track[$onetargetwid][] = "$source_i_offset into $target_i_offset";
                                        $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "Shifted hours from i#{$source_i_offset} to i#{$target_i_offset} " . print_r($shfs2t,TRUE);
                                        if($shfs2t['new_cur_detail_af'] <= .00001)
                                        {
                                            //Move onto the next wid, this one is empty
                                            $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "Moved enough hours from i#{$source_i_offset} to i#{$target_i_offset}";
                                            break;
                                        }
                                    }
                                }                                
                            }
                        }
                    }
                }
            }
            $interval_sum_map_bundle['metadata']['shiftEffortRight']['log'][] = "Done! shift_wid_track=" . print_r($shift_wid_track,TRUE);
            $result_bundle = array('shift_wid_track'=>$shift_wid_track);
            return $result_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Set the apportionments within the interval data structure so that there
     * is no utilization overage where possible to do so
     */
    public function setBestIntervalUtilizationApportionmentsOnePerson(&$all_intervals_info)
    {
        try
        {
            $all_intervals_info['metadata']['set_best_interval_apportionments']['timstamp']['started'] = microtime(TRUE);
            $interval_sum_map_bundle = $this->getIntervalSummationMapBundle($all_intervals_info);
            $interval_sum_map = &$interval_sum_map_bundle['sum_map'];
      
            // shift right then left to settle work into the available space
            $this->shiftEffortRight($interval_sum_map_bundle);
            $this->shiftEffortLeft($interval_sum_map_bundle);
            
            //Now scan for remaining overages
            $overloaded_interval_map = [];
            for($i_offset=0; $i_offset<count($interval_sum_map); $i_offset++)
            {
                $i_info = &$interval_sum_map_bundle['sum_map'][$i_offset];   //So we get by reference
                if($i_info['summary']['total_af'] > 1)
                {
                    $overloaded_interval_map[$i_offset] = array('summary'=>$i_info['summary'],'detail'=>$i_info['detail']);
                }
            }

            //Apply all the new apportionments to the $all_intervals_info
            $this->setIntervalApportionmentsFromSummationMapBundle($all_intervals_info,$interval_sum_map_bundle);
            $all_intervals_info['metadata']['set_best_interval_apportionments']['timstamp']['finished'] = microtime(TRUE);
            $all_intervals_info['metadata']['status'] = 'ready';
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Tell us important things about the fit.
     * Allow empty input dates to compute non-zero result so that we bubble helpful up possibilities
     * If start date is bigger than end date then assumes daily detail is in reverse too.
     * The function will only consider days where you have provided daily detail!
     */
    public function getFitFeedback($remaining_effort_hours, $daily_detail
            , $subset_start_dt=NULL, $subset_end_dt=NULL, $today_dt=NULL
            , $deprecated_min_pct_buffer = 0, $deprecated_strict_min_pct=TRUE)
    {
        
        $outside_known_date_range_yn = 0;   //Assume okay
        if(!empty($subset_start_dt) && !isset($daily_detail[$subset_start_dt]))
        {
            $outside_known_date_range_yn = 1;
            //DebugHelper::debugPrintNeatly(array('##$daily_detail'=>$daily_detail), FALSE, "DEBUG did NOT find  $subset_start_dt in the array!!!! ...........","...... info","error");
            //DebugHelper::showStackTrace("DEBUG subset start date $subset_start_dt issues");
            //throw new \Exception("Missing subset_start_dt=$subset_start_dt in " . print_r($daily_detail,TRUE));
        } else
        if(!empty($subset_end_dt) && !isset($daily_detail[$subset_end_dt]))
        {
            $outside_known_date_range_yn = 1;
            //DebugHelper::debugPrintNeatly(array('##$daily_detail'=>$daily_detail), FALSE, "DEBUG did NOT find  $subset_end_dt in the array!!!! ...........","...... info","error");
            //DebugHelper::showStackTrace("DEBUG subset end date $subset_end_dt issues");
            //throw new \Exception("Missing subset_end_dt=$subset_end_dt in " . print_r($daily_detail,TRUE));
        }
        
        if(empty($today_dt))
        {
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d",$now_timestamp);
        }
        
        $date_keys = array_keys($daily_detail);
        if(!empty($subset_start_dt) && (empty($subset_end_dt) || $subset_start_dt <= $subset_end_dt))
        {
            $direction = 1;
            if($subset_start_dt < $today_dt)
            {
                $prune_start_dt = $today_dt;
            } else {
                $prune_start_dt = $subset_start_dt;
            }
            $start_idx = array_search($prune_start_dt, $date_keys);
            $pruned_date_keys = array_slice($date_keys, $start_idx);
        } else {
            if((empty($subset_start_dt) && (!empty($subset_end_dt)) || $subset_start_dt > $subset_end_dt))
            {
                $direction = -1;
                $start_idx = array_search($subset_start_dt, $date_keys);
                $end_idx = array_search($today_dt, $date_keys);
                if($end_idx === FALSE)
                {
                    $pruned_date_keys = array_slice($date_keys, $start_idx);
                } else {
                    $pruned_date_keys = array_slice($date_keys, $start_idx, $end_idx-$start_idx);
                }
            } else {
                throw new \Exception("Impossible subset date interval [$subset_start_dt,$subset_end_dt]");
            }
        }
        $metadata = [];
        $metadata['input']['remaining_effort_hours'] = $remaining_effort_hours;
        $metadata['input']['subset_start_dt'] = $subset_start_dt;
        $metadata['input']['subset_end_dt'] = $subset_end_dt;
        $metadata['input']['min_pct_buffer']['value'] = $deprecated_min_pct_buffer;
        $metadata['input']['min_pct_buffer']['is_strict'] = $deprecated_strict_min_pct ? 1 : 0;
        $metadata['input']['direction'] = $direction;
        $feedback = [];
        $is_done = ($remaining_effort_hours == 0);
        $is_okay = TRUE;
        $reason = NULL;
        $fit_quality = [];
        $suggestions = [];
        $min_date = NULL;
        $max_date = NULL;
        $min_pct_capacity = [];
        $min_pct_capacity['date'] = NULL;
        $max_pct_capacity = [];
        $max_pct_capacity['date'] = NULL;
        $debug_info = [];
        $fit_quality['outside_known_date_range_yn'] = $outside_known_date_range_yn;
        if(count($daily_detail) == 0 && $remaining_effort_hours > 0)
        {
            $is_okay = FALSE;
            $reason = "No days provided! ($remaining_effort_hours hours for zero days)";
            $fit_quality['avg_time_buffer'] = 0;
        } else if($remaining_effort_hours == 0) {
            $is_okay = TRUE;
            $reason = "Zero declared remaining effort";
            $fit_quality['attention'] = NULL;
            $fit_quality['avg_time_buffer'] = NULL;
            if($subset_start_dt > $today_dt)
            {
                $suggestions['new_start_dt'] = $today_dt;
            }
            if($subset_end_dt > $subset_start_dt)
            {
                $suggestions['new_end_dt'] = $subset_start_dt;
            }
        } else {
            if($outside_known_date_range_yn)
            {
                //Just write some edge values.
                $total_work_hours_in_period = $remaining_effort_hours;
                $total_daycount = round($remaining_effort_hours/5,0);
                $max_pct_capacity['date'] = NULL;
                $max_pct_capacity['capacity'] = NULL;
                $max_pct_capacity['effective_capacity'] = NULL;
                $min_pct_capacity['date'] = NULL;
                $min_pct_capacity['capacity'] = NULL;
                $min_pct_capacity['effective_capacity'] = NULL;
            } else {
                //Take a closer look
                $total_daycount = 0;
                $total_work_hours_in_period = 0;
                $max_dt_with_capacity = NULL;
                $first_workday_on_or_after_today = NULL;
                //foreach($daily_detail as $onedate=>$onedetail)
                foreach($pruned_date_keys as $onedate)
                {
                    $onedetail = $daily_detail[$onedate];
                    $total_daycount++;
                    if($onedetail['isworkday'])
                    {
                        $workhoursinday = $onedetail['workhoursinday'];
                        if(empty($first_workday_on_or_after_today) && $workhoursinday > 0)
                        {
                            $first_workday_on_or_after_today = $onedate;
                        }
                        $today_busy_hours = $onedetail['today_busy_hours'];
                        $available_for_work_today = $workhoursinday - $today_busy_hours;
                        $pct_available_for_work_today = $available_for_work_today / $workhoursinday;
                        if($min_date === NULL)
                        {
                            $min_date = $onedate;
                        }
                        $max_date = $onedate;
                        if(empty($min_pct_capacity['date']) || $pct_available_for_work_today <= $min_pct_capacity['capacity'])
                        {
                            //Grab the latest date meeting criteria!
                            $min_pct_capacity['date'] = $onedate;
                            $min_pct_capacity['capacity'] = $pct_available_for_work_today;
                            $min_pct_capacity['effective_capacity'] = $pct_available_for_work_today - $deprecated_min_pct_buffer;
                        }
                        if(empty($max_pct_capacity['date']) || $pct_available_for_work_today >= $max_pct_capacity['capacity'])
                        {
                            //Grab the latest date meeting criteria!
                            $max_pct_capacity['date'] = $onedate;
                            $max_pct_capacity['capacity'] = $pct_available_for_work_today;
                            $max_pct_capacity['effective_capacity'] = $pct_available_for_work_today - $deprecated_min_pct_buffer;
                        }
                        if(empty($max_dt_with_capacity) || $available_for_work_today > 0)
                        {
                            //Grab the latest date meeting criteria!
                            $max_dt_with_capacity = $onedate;
                        }
                        $total_work_hours_in_period += $onedetail['workhoursinday'];
                    }
                    if(($direction>0 && $subset_end_dt <= $onedate) || ($direction<0 && $subset_end_dt >= $onedate))
                    {
                        break;
                    }
            
                }
            }
            $fit_quality['unboxed']['min_pct'] = $min_pct_capacity;            
            $fit_quality['unboxed']['max_pct'] = $max_pct_capacity;            
            if(empty($first_workday_on_or_after_today))
            {
                //Just take today.
                $first_workday_on_or_after_today = $today_dt;
            }
            if($total_work_hours_in_period < $remaining_effort_hours)
            {
                $is_okay = FALSE;
                if(empty($subset_start_dt) && empty($subset_end_dt))
                {
                    $reason = "Too few work hours in $total_daycount day period where no bounding dates were provided! ($total_work_hours_in_period hours < $remaining_effort_hours hours)";
                } else if(empty($subset_start_dt)) {
                    $reason = "Too few work hours in $total_daycount day for end date $subset_end_dt! ($total_work_hours_in_period hours < $remaining_effort_hours hours)";
                } else if(empty($subset_end_dt)) {
                    $reason = "Too few work hours in $total_daycount day for start date $subset_start_dt! ($total_work_hours_in_period hours < $remaining_effort_hours hours)";
                } else {
                    $reason = "Too few work hours in $total_daycount day period from $min_date to $max_date! ($total_work_hours_in_period hours < $remaining_effort_hours hours)";
                }
                $fit_quality['avg_time_buffer'] = 0;
            } else {
                $m = $this->getWorkhoursApportionmentFactor($remaining_effort_hours, $total_work_hours_in_period);
                $debug_info[] = "$m = getWorkhoursApportionmentFactor($remaining_effort_hours, $total_work_hours_in_period)";
                //$m = round($m,1000);
                if($deprecated_strict_min_pct)
                {
                    $solution_available_pct_capacity = $min_pct_capacity['effective_capacity'];
                    $debug_info[] = "strict_min_pct=[$deprecated_strict_min_pct] ::: $solution_available_pct_capacity";
                } else {
                    $solution_available_pct_capacity = $min_pct_capacity['effective_capacity'] >= $m ? $min_pct_capacity['effective_capacity'] : $min_pct_capacity['capacity'];
                    $debug_info[] = "strict_min_pct=[$deprecated_strict_min_pct] ::: [{$min_pct_capacity['effective_capacity']}] vs [{$min_pct_capacity['capacity']}] ";
                }
                $solution_available_pct_capacity = round($solution_available_pct_capacity,1000);
                $metadata['computed']['min']['capacity']['available'] = $min_pct_capacity['capacity'];
                $metadata['computed']['min']['capacity']['effective'] = $min_pct_capacity['effective_capacity'];
                //$metadata['computed']['min_pct_buffer'] = 1 - $max_pct_capacity['capacity'];    //Remainder
                $pct_diff = $solution_available_pct_capacity - $m;
                $debug_info[] = "pct_diff ::: $pct_diff = $solution_available_pct_capacity - $m";
                $is_pct_enough = $pct_diff >= -.0001; //$solution_available_pct_capacity >= $m;  //STRANGE BUG IN PHP -- had to switch to subtraction check!!!!
                $soonest_possible_end_dt = NULL;
                if($is_pct_enough)
                {
                    //Fits, lets see how well.
                    $total_workhoursindays=0;
                    $total_remaining_open_hours = 0;
                    foreach($daily_detail as $onedate=>$onedetail)
                    {
                        if($today_dt <= $onedate && (empty($subset_start_dt) || $subset_start_dt <= $onedate))
                        {
                            $workhoursinday = $onedetail['workhoursinday'];
                            $today_busy_hours = $onedetail['today_busy_hours'];
                            $workonthisday = $m * $workhoursinday;
                            $remaining_open_hours = $workhoursinday - $workonthisday - $today_busy_hours;
                            $total_remaining_open_hours += $remaining_open_hours;
                            $total_workhoursindays += $workhoursinday;
                            if($soonest_possible_end_dt === NULL && $remaining_effort_hours <= $total_workhoursindays)
                            {
                                $soonest_possible_end_dt = $onedate;
                            }
                        }
                        if(!empty($subset_end_dt) && $subset_end_dt <= $onedate)
                        {
                            break;
                        }
                    }
                    $fit_quality['boxed']['soonest_possible_end_dt'] = $soonest_possible_end_dt;    //DOES NOT FACTOR IN UTILIZATION!
                    $fit_quality['boxed']['attention_per_day'] = $m;   //Possible metric for judging the fit, fixed range is [0,1]
                    $fit_quality['boxed']['buffer_pct'] = $total_remaining_open_hours/$total_workhoursindays; //range [0,1)
                    $fit_quality['unboxed']['total_workhoursindays'] = $total_workhoursindays;
                    $fit_quality['unboxed']['total_remaining_open_hours'] = $total_remaining_open_hours;
                    $fit_quality['unboxed']['total_daycount'] = $total_daycount;
                    $fit_quality['unboxed']['avg_time_buffer'] = $total_remaining_open_hours / $total_daycount;
                } else {
                    //Not enough room, lets try to start the work after this date.
                    if($deprecated_strict_min_pct)
                    {
                        $use_m = $solution_available_pct_capacity;
                        $use_wording = "are allowed for work on this item";
                    } else {
                        $use_m = $m;
                        $use_wording = "are available";
                    }
                    $mincapacitydate = $outside_known_date_range_yn ? NULL : $min_pct_capacity['date'];
                    $workhoursinday = $outside_known_date_range_yn ? NULL : $daily_detail[$mincapacitydate]['workhoursinday'];
                    $today_busy_hours = $outside_known_date_range_yn ? NULL : $daily_detail[$mincapacitydate]['today_busy_hours'];
                    $available_for_work_today = $outside_known_date_range_yn ? NULL : $workhoursinday - $today_busy_hours;
                    $pct_available_for_work_today = $outside_known_date_range_yn ? NULL : $available_for_work_today / $workhoursinday;
                    $workonthisday = $outside_known_date_range_yn ? NULL : $use_m * $workhoursinday;
                    $is_okay = FALSE;
                    $fit_quality['avg_time_buffer'] = 0;
                    if($workonthisday <= $available_for_work_today)
                    {
                        //Failed because of percent issue
                        $reason = "Too few available hours in period from $subset_start_dt to $subset_end_dt [[[k use_m=$use_m strict_min_pct=[$deprecated_strict_min_pct]  m=$m ($workonthisday <= $available_for_work_today  ]]]]"
                                . "(pct_diff=$pct_diff is_pct_enough=[$is_pct_enough] $solution_available_pct_capacity >= $m ???  seeking at least $m availability but only found $solution_available_pct_capacity)";
                        $suggestions['new_start_dt'] = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($mincapacitydate, 1);
                    } else {
                        //Failed because of few hours issue
                        $reason = "Too few available hours on $mincapacitydate (need to work $workonthisday hours but only $available_for_work_today hours $use_wording) pct_diff=$pct_diff";
                        $suggestions['new_start_dt'] = UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($mincapacitydate, 1);
                    }
                }
                if(!$outside_known_date_range_yn && $max_dt_with_capacity < $subset_end_dt)
                {
                    $suggestions['new_end_dt'] = $max_dt_with_capacity;
                }
            }
            if($remaining_effort_hours > 0 && empty($suggestions['new_start_dt']) && !empty($subset_start_dt))
            {
                if(!isset($daily_detail[$subset_start_dt]))
                {
                    DebugHelper::debugPrintNeatly(array('##$data_ar' => $data_ar,'##$daily_detail'=>$daily_detail), FALSE, "did NOT find  $subset_start_dt in the array!!!! ...........","...... info","error");
                    DebugHelper::showStackTrace();
                }
                $onedetail = $daily_detail[$subset_start_dt];
                if(!$onedetail['isworkday'])
                {
                    $suggestions['new_start_dt'] = $first_workday_on_or_after_today;
                }
            }
        }
        $feedback['metadata'] = $metadata;
        $feedback['debug_info'] = $debug_info;
        $feedback['is_done'] = $is_done;
        $feedback['is_okay'] = $is_okay;
        $feedback['fit_quality'] = $fit_quality;
        $feedback['reason'] = $reason;
        $feedback['suggestions'] = $suggestions;
        return $feedback;
    }
    
    /**
     * Get information useful for apportioning remaining effort of ONE workitem across available days of a period.
     * CAUTION: This function does NOT factor in any interval apportionment that may be relevant to the workitem.
     *          Only use this to compute BASELINE relevant values.
     */
    public function get_NON_INTERVAL_BALANCE_AWARE_WorkhoursApportionmentTotalBundle($data_ar,$daycount_detailinfo,$interval_start_dt,$interval_end_dt
            ,$workitem_start_dt, $workitem_end_dt
            ,$today_dt=NULL)
    {
        try
        {
            $insight_ar = [];
            $input_ar = [];
            $input_ar['interval_start_dt'] = $interval_start_dt;
            $input_ar['interval_end_dt'] = $interval_end_dt;
            $input_ar['workitem_start_dt'] = $workitem_start_dt;
            $input_ar['workitem_end_dt'] = $workitem_end_dt;
            $input_ar['today_dt'] = $today_dt;
            $daily_detail = $daycount_detailinfo['daily_detail'];
            if(empty($daily_detail))
            {
                throw new \Exception("Did NOT find daily_detail in " . print_r($daycount_detailinfo,TRUE));
            }
            
            if(!empty($data_ar['wid']))
            {
                //This is a workitem interval
                $wid = $data_ar['wid'];
                if(empty($workitem_start_dt))
                {
                    DebugHelper::showStackTrace("STACK DUMP FOR Missing workitem_start_dt!", "warning", 5);
                    throw new \Exception("Missing workitem_start_dt!");
                }
                if(empty($workitem_end_dt))
                {
                    throw new \Exception("Missing workitem_end_dt!");
                }

                if(!empty($today_dt) && $interval_start_dt < $today_dt)
                {
                    //No remaining work can be done in the past
                    $effective_interval_start_dt = $today_dt;
                    $insight_ar[] = "Shifted intertval start from $effective_interval_start_dt to today $today_dt";
                } else {
                    $effective_interval_start_dt = $interval_start_dt;
                }

                if(!empty($today_dt) && $workitem_start_dt < $today_dt)
                {
                    //No remaining work can be done in the past
                    $effective_workitem_start_dt = $today_dt;
                } else {
                    $effective_workitem_start_dt = $workitem_start_dt;
                }
                $effective_workitem_end_dt = $workitem_end_dt;

                if(!empty($effective_workitem_start_dt) && $effective_workitem_start_dt > $effective_interval_start_dt)
                {
                    //Shrink the interval
                    $insight_ar[] = "Shrank interval start from $effective_interval_start_dt to work start $effective_workitem_start_dt";
                    $effective_interval_start_dt = $effective_workitem_start_dt;
                }

                if(!empty($workitem_end_dt) && $workitem_end_dt < $interval_end_dt)
                {
                    //Shrink the interval of the period to the end of the workitem
                    $effective_interval_end_dt = $workitem_end_dt;
                } else {
                    //End the period on the declared interval date
                    $effective_interval_end_dt = $interval_end_dt;
                }

                $has_work_overlap_with_period = $effective_workitem_start_dt <= $effective_interval_end_dt && $effective_workitem_end_dt >= $effective_interval_start_dt;
                $insight_ar[] = "has_work_overlap_with_period=[$has_work_overlap_with_period]";

                $remaining_effort_hours = $data_ar['remaining_effort_hours'];
                if(!$has_work_overlap_with_period)
                {
                    $m = NULL;
                    $awh = NULL;
                    $fsb = NULL;
                    $ssb = NULL;
                    $total_work_days_in_subset = NULL;
                    $total_work_hours_in_subset = NULL;
                    $calc_comment = "No overlap";

                } else {

                    $fsb = $this->getSimpleWorkhoursSummationBundle($effective_workitem_start_dt,$workitem_end_dt,$daily_detail);
                    $total_work_hours_in_period = $fsb['work_hours'];

                    //$total_work_hours_in_period = $daycount_detailinfo['available_work_hours'];
                    $m = $this->getWorkhoursApportionmentFactor($remaining_effort_hours, $total_work_hours_in_period);

                    $ssb = $this->getSimpleWorkhoursSummationBundle($effective_interval_start_dt,$effective_interval_end_dt,$daily_detail);
                    $total_work_days_in_subset = $ssb['work_days'];
                    $total_work_hours_in_subset = $ssb['work_hours'];
                    if(empty($m))
                    {
                        $awh = $remaining_effort_hours; //Just return the whole thing because trouble!
                        $calc_comment = "uncut effort because empty factor";
                    } else {
                        $awh = $m * $total_work_hours_in_subset;
                        $calc_comment = "apportioned $awh b/c $m x $total_work_hours_in_subset at interval [$effective_interval_start_dt,$effective_interval_end_dt] ::: workitem reh=$remaining_effort_hours [$effective_workitem_start_dt,$effective_workitem_end_dt] has whip=$total_work_hours_in_period";
                    }
                }

                $bundle = [];
                $bundle['wid'] = $wid;
                $fpb = [];
                $fpb['remaining_effort_hours'] = $remaining_effort_hours;

                $bundle['metadata']['input'] = $input_ar;
                $bundle['metadata']['insight'] = $insight_ar;
                $bundle['metadata']['has_work_overlap_with_period'] = $has_work_overlap_with_period;
                if(FALSE && empty($m))
                {
                    DebugHelper::debugPrintNeatly($bundle,FALSE,"LOOK insight for wid=$wid ............");
                    DebugHelper::showStackTrace();
                    throw new \Exception("LOOK JUST STOP HERE");
                }

                $bundle['full_work_period'] = $fpb;
            }
            
            $bundle['apportionment']['factor'] = $m;
            $bundle['apportionment']['remaining_effort_hours'] = $awh;
            $bundle['apportionment']['total_work_days_in_subset'] = $total_work_days_in_subset;
            $bundle['apportionment']['total_work_hours_in_subset'] = $total_work_hours_in_subset;
            $bundle['apportionment']['calc_comment'] = $calc_comment;
            $bundle['dayfactors']['full'] = $fsb;
            $bundle['dayfactors']['subset'] = $ssb;
            return $bundle;
        } catch (\Exception $ex) {
            
            error_log("INTERNAL ERROR COMPUTING UTILIZATION");
            error_log("...ERROR:$ex");
            if(BIGFATHOM_VERBOSE_DEBUG == 1)
            {
                drupal_set_message("Internal error computing utilization!","error");
                $inputdetail = DebugHelper::debugPrintNeatly(array('##$data_ar' => $data_ar,'##daycount_detailinfo'=>$daycount_detailinfo), TRUE, "getWorkhoursApportionmentTotalBundle $interval_start_dt to $interval_end_dt .....", "..............getWorkhoursApportionmentTotalBundle");
                drupal_set_message("LOOK FAILED because $ex","error");
                drupal_set_message("LOOK FAILED inputdetail = $inputdetail","error");
            }
            throw $ex;
        }
    }
}

