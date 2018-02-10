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

namespace bigfathom_autofill;

require_once 'ProjectAutofillDataBundle.php';

/**
 * Compute reasonable schedule solutions
 *
 * @author Frank
 */
class Engine
{
    private $m_initialized = NULL;
    
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oCoreWriteHelper = NULL;
    private $m_oAutofillWriteHelper = NULL;
    
    private $m_oLLH = NULL;
    private $m_iteration_counter = 0;
    private $m_raw_flags_ar = NULL;
    
    private $m_today_dt = NULL; //Because today may be bigger than our min date
    private $m_tomorrow_dt = NULL;
    private $m_min_reference_dt = NULL;
    private $m_max_reference_dt = NULL;
    
    private $m_oProjectAutofillDataBundle = NULL;
    private $m_oBUI = NULL;
    
    private $m_flag_availability_type_BY_OWNER = NULL;
    private $m_flag_update_ALL_WORK = NULL;
    
    private $m_projectid = NULL;

    public function __toString()
    {
        $nice_text_ar = [];
        $nice_text_ar[] = "Engine for project#{$this->m_projectid}";
        if(!empty($this->m_oProjectAutofillDataBundle))
        {
            $nice_text_ar[] = "" . $this->m_oProjectAutofillDataBundle;
        }
        $nice_text = implode("\n<br>>>>", $nice_text_ar);
        return $nice_text;
    } 
    
    public function __construct($projectid, $flags_ar=NULL)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        $this->m_projectid = $projectid;
        
        if(empty($flags_ar) || !is_array($flags_ar))
        {
            $flags_ar = [];
        }

        $this->m_flags_ar = $flags_ar;
        $this->setFlagMembers($flags_ar);
        
        if(empty($this->m_today_dt))
        {
            $now_timestamp = time();
            $this->m_today_dt = gmdate("Y-m-d", $now_timestamp);
        }
        $this->m_tomorrow_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_today_dt, 1);
        if(empty($this->m_min_reference_dt))
        {
            $this->m_min_reference_dt = $this->m_today_dt;
        }
        if(empty($this->m_max_reference_dt))
        {
            $this->m_max_reference_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_min_reference_dt, 1994);
        } 
        if($this->m_min_reference_dt > $this->m_max_reference_dt)   
        {
            throw new \Exception("Error with reference dates! [{$this->m_min_reference_dt}] > [{$this->m_max_reference_dt}]");
        }
        
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        $loaded_llh = module_load_include('php','bigfathom_core','core/LinkLogicHelper');
        if(!$loaded_llh)
        {
            throw new \Exception('Failed to load the LinkLogicHelper class');
        }
        $this->m_oLLH = new \bigfathom\LinkLogicHelper();
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
        $this->m_iteration_counter = 0;
        
        $loaded_1a = module_load_include('php','bigfathom_autofill','core/WriteHelper');
        if(!$loaded_1a)
        {
            throw new \Exception('Failed to load the autofill WriteHelper class');
        }
        
        $loaded_1b = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded_1b)
        {
            throw new \Exception('Failed to load the core WriteHelper class');
        }

        $loaded_pai = module_load_include('php','bigfathom_utilization','core/PersonAvailabilityInsight');
        if(!$loaded_pai)
        {
            throw new \Exception('Failed to load the bigfathom_utilization PersonAvailabilityInsight class');
        }
        
        $loaded_bundle = module_load_include('php','bigfathom_autofill','core/ProjectAutofillDataBundle');
        if(!$loaded_bundle)
        {
            throw new \Exception('Failed to load the autofill ProjectAutofillDataBundle class');
        }

        /* NOT USED
        $loaded_forecast = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
        if(!$loaded_forecast)
        {
            throw new \Exception('Failed to load the ProjectForecaster class');
        }
         * 
         */
        
        $loaded3 = module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
        if(!$loaded3)
        {
            throw new \Exception('Failed to load the UtilityFormatUtilizationData class');
        }
        
        $loaded4 = module_load_include('php','bigfathom_utilization','core/BottomUpInsights');
        if(!$loaded4)
        {
            throw new \Exception('Failed to load the BottomUpInsights class');
        }
        
        $this->m_oAutofillWriteHelper = new \bigfathom_autofill\WriteHelper();
        $this->m_oCoreWriteHelper = new \bigfathom\WriteHelper();
        $this->m_oProjectAutofillDataBundle = new \bigfathom_autofill\ProjectAutofillDataBundle($this->m_projectid);
        
        $this->m_initialized = FALSE;
        
    }
    
    /**
     * Call this to initialize the engine
     */
    public function initialize($networkbundle, $all_changeable_workitems)
    {
        $this->m_oProjectAutofillDataBundle->initialize($networkbundle, $all_changeable_workitems);
        $this->m_oBUI = new \bigfathom_utilization\BottomUpInsights($this->m_oProjectAutofillDataBundle, $this->m_flags_ar);
        $this->m_oProjectAutofillDataBundle->setBUI($this->m_oBUI);
        $this->m_initialized = TRUE;
    }
    
    private function getScoreContentBundle4Root($pai_winsight, $buc_insight_bundle, $wdetail)
    {
        try
        {
            $new_sdt = NULL;
            $new_edt = NULL;
            $pai_daterange = $pai_winsight['daterange'];
            $pai_continued_beyond_original_edt_yn = $pai_winsight['continued_beyond_original_edt_yn'];
            $pai_sdt = $pai_daterange['sdt'];
            $pai_edt = $pai_daterange['edt'];
            //$insight_effective_sdt = $buc_insight_bundle['insight']['effective_sdt'];
            //$insight_effective_edt = $buc_insight_bundle['insight']['effective_edt'];
            $insight_effective_sdt = $buc_insight_bundle['insight']['sdt'];
            $insight_effective_edt = $buc_insight_bundle['insight']['edt'];
            $min_ant_dt = $buc_insight_bundle['insight']['min_ant_dt'];
            $max_ant_dt = $buc_insight_bundle['insight']['max_ant_dt'];
            $existing_sdt = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
            $existing_edt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
            if($pai_continued_beyond_original_edt_yn)
            {
                //Existing was not okay
                $cursc = 0;
                $newsc = 99;
                $new_sdt = $min_ant_dt;    //Force to start no later than this
                $new_edt = $pai_edt;
            } else {
                //Existing might be okay
                if(!empty($min_ant_dt) && !empty($max_ant_dt))
                {
                    //Factor in member workitems
                    $cursc = 50;
                    $newsc = 0;

                    if(empty($insight_effective_sdt) || $min_ant_dt < $insight_effective_sdt)
                    {
                        $cursc -= 25;
                        $newsc += 30;
                        $new_sdt = $min_ant_dt;
                    }
                    
                    $earliest_possible_edt = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax($max_ant_dt, $pai_edt);
                    if($insight_effective_edt != $earliest_possible_edt || empty($insight_effective_edt))
                    {
                        if($new_sdt <= $earliest_possible_edt)
                        {
                            //Simple OK case
                            $cursc -= 25;
                            $newsc += 30;
                            $new_edt = $earliest_possible_edt;
                        }
                    }
                    
                    $existing_fit_okay = ($cursc > 0) ? 1 : 0; 
                    $existing_fit_score = $cursc;
                    $new_score = $newsc;
                } else {
                    //No member workitems
                    $cursc = 90;
                    $newsc = 0;
                    if(!empty($pai_sdt) && $pai_sdt != $insight_effective_sdt)
                    {
                        //
                        $cursc -= 25;
                        $newsc += 25;
                        $new_sdt = $pai_sdt;
                    }
                    if(!empty($pai_edt) && $pai_edt != $insight_effective_edt)
                    {
                        $cursc -= 25;
                        $newsc += 25;
                        $new_edt = $pai_edt;
                    }

                }
            }
            if(empty($existing_sdt) || empty($existing_edt))
            {
                $cursc /= 2;
            }
            $existing_fit_okay = ($cursc > 0) ? 1 : 0; 
            $existing_fit_score = $cursc;
            $new_score = $newsc;
            
            if($existing_fit_okay)
            {
                $change_count=0;
                if(!empty($new_sdt) && $existing_sdt != $new_sdt)
                {
                    $change_count++;
                }
                if(!empty($new_edt) && $existing_edt != $new_edt)
                {
                    $change_count++;
                }
                if($change_count == 0)
                {
                    $new_score /= 2;
                }
            }
            
            $bundle = array(
                'existing_fit_score'=>$existing_fit_score,
                'new_fit_score'=>$new_score,
                'new_sdt'=>$new_sdt,
                'new_edt'=>$new_edt,
            );
            //\bigfathom\DebugHelper::showNeatMarkup(array('$bundle'=>$bundle,  '$pai_winsight'=>$pai_winsight,'$buc_insight_bundle'=>$buc_insight_bundle),'LOOK ROOT SCORE STUFF');           
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getScoreContentBundle4NonRoot($pai_winsight, $buc_insight_bundle, $wdetail)
    {
        try
        {
            $input_sdt = $pai_winsight['metadata']['input_sdt'];
            $input_edt = $pai_winsight['metadata']['input_edt'];
            $pai_daterange = $pai_winsight['daterange'];
            $new_sdt = $pai_daterange['sdt'];
            $new_edt = $pai_daterange['edt'];

            $pai_continued_beyond_original_edt_yn = $pai_winsight['continued_beyond_original_edt_yn'];
            $insight_ant_latest_edt = $buc_insight_bundle['insight']['latest_ant_edt'];
            
            $existing_sdt = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
            $existing_edt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
            
            if(empty($insight_ant_latest_edt))
            {
                //No ant constraints
                if(empty($new_sdt))
                {
                    $new_score = 0;
                    if(empty($input_sdt))
                    {
                        $existing_fit_okay = 0; 
                        $existing_fit_score = 0;
                    } else {
                        $existing_fit_okay = 1; 
                        $existing_fit_score = 50;
                    }
                } else {
                    if($new_sdt < $input_sdt)
                    {
                        //Sooner is better
                        $existing_fit_okay = 1; 
                        $existing_fit_score = 64;
                        $new_score = 84;
                    } else {
                        if($pai_continued_beyond_original_edt_yn)
                        {
                            //Existing did not fit
                            $existing_fit_okay = 0; 
                            $existing_fit_score = 0;
                            $new_score = 77;
                        } else {
                            if($input_sdt != $new_sdt || $input_edt != $new_edt)
                            {
                                //Existing did fit and later is not as good
                                $existing_fit_okay = 1; 
                                $existing_fit_score = 78;
                                $new_score = 88;
                            } else {
                                //Not different
                                $existing_fit_okay = 1; 
                                $existing_fit_score = 50;
                                $new_score = 50;
                            }
                        }
                    }
                }
            } else {
                //Factor in ant constraint
                if($insight_ant_latest_edt == $input_sdt)
                {
                    $existing_fit_okay = 1; 
                    $existing_fit_score = 88;
                } 
                else if($insight_ant_latest_edt < $input_sdt)
                {
                    $existing_fit_okay = 1; 
                    $existing_fit_score = 92;
                } else {
                    //Cannot start before ant finishes!
                    $existing_fit_okay = 0; 
                    $existing_fit_score = 0; 
                }
                $new_score = 95;
            }
            if(empty($new_sdt) || empty($new_edt))
            {
                $new_score = $new_score / 2;
            }
            if(empty($input_sdt) || empty($input_edt))
            {
                $existing_fit_score = $existing_fit_score / 2;
            }
            
            if(empty($existing_sdt) || empty($existing_edt))
            {
                $existing_fit_okay = 0;
                $existing_fit_score /= 2;
            }
            
            if($existing_fit_okay)
            {
                $change_count=0;
                if(!empty($new_sdt) && $existing_sdt != $new_sdt)
                {
                    $change_count++;
                }
                if(!empty($new_edt) && $existing_edt != $new_edt)
                {
                    $change_count++;
                }
                if($change_count == 0)
                {
                    $new_score /= 2;
                }
            }
            
            $bundle = array(
                'existing_fit_score'=>$existing_fit_score,
                'new_fit_score'=>$new_score,
                'new_sdt'=>$new_sdt,
                'new_edt'=>$new_edt,
            );
            //\bigfathom\DebugHelper::showNeatMarkup(array('$bundle'=>$bundle,  '$pai_winsight'=>$pai_winsight,'$buc_insight_bundle'=>$buc_insight_bundle),'LOOK nonroot SCORE STUFF');           
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getMasterDailyDetailInstance()
    {
        return $this->m_oProjectAutofillDataBundle->getMasterDailyDetailInstance();
    }
    
    /**
     * Return fit metrics of the specified workitem and suggested better fit
     * without changing the current settings in our caches
     */
    public function getFitFeedback($wid, $wdetail=NULL)
    {
        $metadata = [];
        $metadata['wid'] = $wid;
        $buc_insight_bundle = $this->m_oBUI->getOneWorkitemInsightBundle($wid);

        $new_start_dt = NULL;
        $new_end_dt = NULL;
        $new_score = NULL;
        $existing_fit_okay = NULL;
        $existing_fit_score = NULL;

        if($this->m_oProjectAutofillDataBundle->hasRelevantWorkitemRecord($wid))
        {
            $existing_winfo = $this->m_oProjectAutofillDataBundle->getRelevantWorkitemRecord($wid);
            $fit_input_winfo = $existing_winfo;
        } else {
            if(empty($wdetail))
            {
                throw new \Exception("Missing required wdetail for wid#$wid");
            }

            //Not yet set; populate this manually now
            $fit_input_winfo = $wdetail;
            $fit_input_winfo['wid'] = $wid;
            $fit_input_winfo['reh'] = array_key_exists('remaining_effort_hours',$wdetail) ? $wdetail['remaining_effort_hours'] : $wdetail['reh'];
            $fit_input_winfo['sdt'] = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
            $fit_input_winfo['edt'] = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];

        }
        if(empty($buc_insight_bundle['insight']['latest_ant_edt']))
        {
            $fit_input_winfo['latest_ant_edt'] = NULL;
        } else {
            $fit_input_winfo['latest_ant_edt'] = $buc_insight_bundle['insight']['latest_ant_edt'];
        }
        
        $check_the_fit = TRUE;
        if($wdetail['planned_start_dt_locked_yn'])
        {
            //Start is locked
            if($fit_input_winfo['sdt'] > $fit_input_winfo['edt'])
            {
                //End is not locked
                if(!$wdetail['planned_end_dt_locked_yn'])
                {
                    if(empty($fit_input_winfo['edt']) || $fit_input_winfo['edt'] < $fit_input_winfo['sdt'])
                    {
                        $future_days_guess = 1 + $fit_input_winfo['reh']/4;
                        $fit_input_winfo['edt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($fit_input_winfo['sdt'], $future_days_guess);
                    }
                }
            }
        } else {
            //Start is not locked
            if(empty($fit_input_winfo['sdt']) || $fit_input_winfo['sdt'] > $fit_input_winfo['edt'])
            {
                $fit_input_winfo['sdt'] = !empty($buc_insight_bundle['insight']['latest_ant_edt']) ? $buc_insight_bundle['insight']['latest_ant_edt'] : $this->m_tomorrow_dt;
                if(!$wdetail['planned_end_dt_locked_yn'])
                {
                    //End is not locked
                    if(empty($fit_input_winfo['edt']) || $fit_input_winfo['edt'] < $fit_input_winfo['sdt'])
                    {
                        $future_days_guess = 1 + $fit_input_winfo['reh']/4;
                        $fit_input_winfo['edt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($fit_input_winfo['sdt'], $future_days_guess);
                    }
                }
            }
        }
        $fit_input_winfo['min_allowed_sdt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax(NULL,$buc_insight_bundle['insight']['min_reference_dt'],$buc_insight_bundle['insight']['max_ant_dt']);
        $fit_input_winfo['max_allowed_edt'] = \bigfathom\UtilityGeneralFormulas::getNotEmptyMax(NULL,$buc_insight_bundle['insight']['max_reference_dt']);
        
        //Compute the fit
        if(empty($fit_input_winfo['sdt']) || empty($fit_input_winfo['edt']) || ($fit_input_winfo['sdt'] > $fit_input_winfo['edt']))
        {
            //This is not valid, we know it does not fit.
            $check_the_fit = FALSE;
        }
        
        $is_our_root = $this->m_oProjectAutofillDataBundle->getRootWorkitemID() == $wid;

        if(!$check_the_fit)
        {
            //We know without further checks.
            $pai_winsight = [];
            $existing_fit_score = 0;
            $existing_fit_okay = 0;
            $new_score = 0;
            $new_start_dt = NULL;
            $new_end_dt = NULL;
        } else {
            $pai_winsight = $this->m_oProjectAutofillDataBundle->getOneWorkFitInsightBundle($fit_input_winfo);
            $pai_sdt = $pai_winsight['daterange']['sdt'];
            $pai_edt = $pai_winsight['daterange']['edt'];
            $pai_overage_yn = $pai_winsight['overage_yn'];

            $insight_effective_sdt = $buc_insight_bundle['insight']['sdt'];
            $insight_effective_edt = $buc_insight_bundle['insight']['edt'];

            $new_start_dt = !empty($pai_sdt) && $pai_sdt != $insight_effective_sdt ? $pai_sdt : NULL;
            $new_end_dt = !empty($pai_edt) && $pai_edt != $insight_effective_edt ? $pai_edt : NULL;
            if($pai_overage_yn)
            {
                //Could not find a fit
                $existing_fit_okay = 0; 
                $existing_fit_score = 0; 
                $new_score = 0;
            } else {
                //We seem to have a fit
                if($is_our_root)
                {
                    $fit_scores = $this->getScoreContentBundle4Root($pai_winsight, $buc_insight_bundle, $wdetail);
                } else {
                    //Simple member of the project
                    $fit_scores = $this->getScoreContentBundle4NonRoot($pai_winsight, $buc_insight_bundle, $wdetail);
                }
                $existing_fit_score = $fit_scores['existing_fit_score'];
                $existing_fit_okay = ($existing_fit_score > 0) ? 1 : 0; 
                $new_score = $fit_scores['new_fit_score'];
                $new_start_dt = $fit_scores['new_sdt'];
                $new_end_dt = $fit_scores['new_edt'];
            }
        }

        $existing_fit = [];
        $existing_fit['isokay'] = $existing_fit_okay;
        $existing_fit['score'] = $existing_fit_score;

        $recommended_fit = [];
        $recommended_fit['score'] = $new_score;
        $recommended_fit['new_start_dt'] = $new_start_dt;
        $recommended_fit['new_end_dt'] = $new_end_dt;

        $bundle = [];
        $bundle['metadata'] = $metadata;
        $bundle['existing'] = $existing_fit;
        $bundle['alternative'] = $recommended_fit;

        $bundle['buc_info'] = $buc_insight_bundle;
        $bundle['pai_info'] = $pai_winsight;
        
        return $bundle;
    }
    
    public function getDataBundle()
    {
        if(!$this->m_initialized)
        {
            throw new \Exception("Not initialized!");
        }
        return $this->m_oProjectAutofillDataBundle;
    }
    
    public function getValuesForTrackingRecordCreate($workitems_to_process_ct=NULL)
    {
        return $this->m_oProjectAutofillDataBundle->getValuesForTrackingRecordCreate($workitems_to_process_ct);
    }
    
    public function setUpdatedWorkitemCount($updated_workitem_count=NULL)
    {
        return $this->m_oProjectAutofillDataBundle->setUpdatedWorkitemCount($updated_workitem_count);
    }
    
    /**
     * Returns multiple lists of wids in order of priority for processing
     */
    private function getPrioritySplitWidList($just_wids_to_consider, $wids_in_sprint_map)
    {
        $all_lists = [];
        $sprint_intersection = [];
        $list2 = [];
        
        foreach($just_wids_to_consider as $wid)
        {
            if(isset($wids_in_sprint_map[$wid]))
            {
                $sprint_intersection[] = $wid;
            } else {
                $list2[] = $wid;
            }
        }
        if(count($sprint_intersection)>0)
        {
            //Create lists each with only ONE top ranked workitem
            $score_map = [];
            foreach($sprint_intersection as $wid)
            {
                $sprint_info = $wids_in_sprint_map[$wid];
                $rank = $sprint_info['rank'];
                $score_map[$rank] = $wid;
            }
            ksort($score_map);
            foreach($score_map as $score=>$wid)
            {
                $all_lists[] = array($wid);
            }
        }
        $all_lists[] = $list2;
        return $all_lists;
    }
    
    /**
     * Remove the list from the bundle and return the new bundle
     */
    private function getPrunedRemainingBundle($remaining_bundle, $remove_these_now)
    {
        foreach($remove_these_now as $wid)
        {
            $keys = $remaining_bundle['all']['keys'][$wid];
            $k0 = $keys[0];
            $k1 = $keys[1];
            unset($remaining_bundle[$k0][$k1][$wid]);
        }
        return $remaining_bundle;
    }

    /**
     * Returns the next set of wids to process
     */
    private function getNextListOfCandidatesBundle($remaining_bundle, $all_computed_workitems, $done_map, $editable_wid_map=NULL)
    {
        try
        {
            $bundle = [];
            $possible_solution = [];
            $search_key_bundles = [];
            $search_key_bundles[] = array('leaf','connected','locked');
            $search_key_bundles[] = array('nonleaf','connected','locked');
            $search_key_bundles[] = array('leaf','disconnected','locked');
            $search_key_bundles[] = array('nonleaf','disconnected','locked');
            $search_key_bundles[] = array('leaf','connected','unlocked');
            $search_key_bundles[] = array('nonleaf','connected','unlocked');
            $search_key_bundles[] = array('leaf','disconnected','unlocked');
            $search_key_bundles[] = array('nonleaf','disconnected','unlocked');

            $remaining_map_workitem2sprint = $remaining_bundle['map']['workitem2sprint'];
            $ant_project_rootwid = $remaining_bundle['map']['ant_project_rootwid'];
            $sprint_keys = array_keys($remaining_map_workitem2sprint);
            sort($sprint_keys);
            
            $sprint_keys = [];  //DISABLE FOR NOW!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            
            $count_wids_in_sprint = 0;
            $wids_in_sprint_map = [];
            foreach($sprint_keys as $sprintid)
            {
                $count_wids_in_sprint = 0;  //count($remaining_map_workitem2sprint[$sprintid]);
                foreach($remaining_map_workitem2sprint[$sprintid] as $wid_in_sprint=>$sprint_info)
                {
                    if(!isset($done_map[$wid_in_sprint]) && !isset($wids_in_sprint_map[$wid_in_sprint]))
                    {
                        $count_wids_in_sprint++;
                        $rank = ($sprintid * 100) + $count_wids_in_sprint;
                        $wids_in_sprint_map[$wid_in_sprint] = array('rank'=>$rank,'wid'=>$wid_in_sprint);
                    }
                }
            }
            $has_sprint_wids = $count_wids_in_sprint > 0;
            $locked_counter = 0;
            $unlocked_counter = 0;
            $search_idx = 0;
            while(count($possible_solution) == 0 && $search_idx < count($search_key_bundles))
            {
                $k0 = $search_key_bundles[$search_idx][0];
                $k1 = $search_key_bundles[$search_idx][1];
                $category = $search_key_bundles[$search_idx][2];
                $only_allow_locked = $category == 'locked';
                $just_wids_to_consider = array_keys($remaining_bundle[$k0][$k1]);
                $split_wid_list = $this->getPrioritySplitWidList($just_wids_to_consider,$wids_in_sprint_map);
                foreach($split_wid_list as $wids_list)
                {
                    foreach($wids_list as $wid)
                    {
                        if($editable_wid_map === NULL || isset($editable_wid_map[$wid]))
                        {
                            if($only_allow_locked)
                            {
                                //Is this one locked?
                                $onewi = $all_computed_workitems[$wid];
                                $can_compute_now = (!empty($onewi['actual_start_dt']) || !empty($onewi['actual_end_dt']) 
                                        || $onewi['planned_start_dt_locked_yn'] || $onewi['planned_end_dt_locked_yn']);
                            } else {
                                //Start off by allowing this one.
                                $can_compute_now = TRUE;
                            }
                            if($can_compute_now)
                            {
                                //Assume this is allowed.
                                $daw_list = $all_computed_workitems[$wid]['maps']['daw'];
                                foreach($daw_list as $antwid)
                                {
                                    if(!isset($ant_project_rootwid[$antwid]))
                                    {
                                        if(!isset($done_map[$antwid]) && isset($editable_wid_map[$antwid]))
                                        {
                                            $can_compute_now = FALSE;
                                            break;
                                        }
                                    }
                                }
                                if($has_sprint_wids)
                                {
                                    if(!isset($wids_in_sprint_map[$wid]))
                                    {
                                        $can_compute_now = FALSE;
                                    }
                                }
                            }
                            if($can_compute_now)
                            {
                                $possible_solution[] = $wid;
                                if($only_allow_locked)
                                {
                                    $locked_counter++;
                                } else {
                                    $unlocked_counter++;
                                }
                            }
                        } else {
                        }
                    }
                }
                $search_idx++;
            }
            $count_wids_in_sprint = count($wids_in_sprint_map);
            if(count($possible_solution) == 0 && $count_wids_in_sprint>0)
            {
                //This should not happen.
                error_log("FAILED to get possible solutions for wids_in_sprint_map=" . print_r($wids_in_sprint_map,TRUE));
                drupal_set_message("Not currently sure about scheduling for $count_wids_in_sprint workitem(s) in an existing sprint: " . implode(", ", array_keys($wids_in_sprint_map)),'warning');
                //keep going for now throw new \Exception("Experienced a processing error on autofill -- please try again!");
            }
            
            if($locked_counter > 1)
            {
                //Now prune keeping the most impacting locked item
                $prune_factors = [];
                $min_start_dt = NULL;
                foreach($possible_solution as $wid)
                {
                    $onewi = $all_computed_workitems[$wid];
                    $has_locked_start_dt = !empty($onewi['actual_start_dt']) || $onewi['planned_start_dt_locked_yn'];
                    if($has_locked_start_dt)
                    {
                        $effective_start_dt = !empty($onewi['actual_start_dt']) ? $onewi['actual_start_dt'] : $onewi['planned_start_dt'];
                        $prune_factors[$effective_start_dt][] = $wid;
                        if(empty($min_start_dt) || $min_start_dt > $effective_start_dt)
                        {
                            $min_start_dt = $effective_start_dt;
                        }
                    }
                }
                $winner = NULL;
                if(!empty($min_start_dt))
                {
                    //Find the one with the longest duration from the earliest date
                    $earliest_locked_wids = $prune_factors[$min_start_dt];
                    $max_end_dt = NULL;
                    foreach($earliest_locked_wids as $wid)
                    {
                        $onewi = $all_computed_workitems[$wid];
                        $has_locked_end_dt = !empty($onewi['actual_end_dt']) || $onewi['planned_end_dt_locked_yn'];
                        if($has_locked_end_dt)
                        {
                            $effective_endt_dt = !empty($onewi['actual_end_dt']) ? $onewi['actual_end_dt'] : $onewi['planned_end_dt'];
                            if(empty($winner) || $effective_endt_dt > $max_end_dt)
                            {
                                $winner = $wid;
                                $max_end_dt = $effective_endt_dt;
                            }
                        }
                    }
                } else {
                    //Simply take the one with the earliest locked end date
                    $winner = NULL;
                    $min_end_dt = NULL;
                    foreach($possible_solution as $wid)
                    {
                        $onewi = $all_computed_workitems[$wid];
                        $has_locked_end_dt = !empty($onewi['actual_end_dt']) || $onewi['planned_end_dt_locked_yn'];
                        if($has_locked_end_dt)
                        {
                            $effective_endt_dt = !empty($onewi['actual_end_dt']) ? $onewi['actual_end_dt'] : $onewi['planned_end_dt'];
                            if(empty($winner) || (!empty($effective_endt_dt) && $effective_endt_dt < $min_end_dt))
                            {
                                $winner = $wid;
                                $min_end_dt = $effective_endt_dt;
                            }
                        }
                    }
                }
                if(!empty($winner))
                {
                    $possible_solution = array($winner);
                }
            }
            if(count($possible_solution) < 2)
            {
                //Good
                $final_list = $possible_solution;
            } else {
                //Just keep the first one then
                $keep = $possible_solution[0];
                $final_list = array($keep);
            }
            $bundle['process_now'] = $final_list;
            $bundle['process_soon'] = $possible_solution;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Factors importance(higher), effort(lower), and number of dependencies(higher)
     */
    private function getTimePrioritizedSortedList($all_computed_workitems, $sortthese)
    {
        $sorted = [];
        $buckets = [];
        foreach($sortthese as $wid)
        {
            $one_computed_workitem = $all_computed_workitems[$wid];
            $owner_personid = $one_computed_workitem['owner_personid']; //Fixed 20170629
            $importance = $one_computed_workitem['importance'];
            $remaining_effort_hours = !empty($one_computed_workitem['reh']) ? $one_computed_workitem['reh'] : $one_computed_workitem['remaining_effort_hours']; //OLD LOGIC?
            $ddw_count = count($one_computed_workitem['maps']['ddw']);
            $daw_count = count($one_computed_workitem['maps']['daw']);
            $locked_end_dt = $this->getLowestLockedEndDateOrNull($one_computed_workitem);
            $days_uled = $this->getDayCountOrAltValue($this->m_today_dt,$locked_end_dt,$owner_personid,NULL);
            $rankscore = $this->getRankScore($importance, $remaining_effort_hours, $ddw_count, $daw_count, $days_uled);
            if(isset($buckets[$rankscore]))
            {
                $buckets[$rankscore] = [];
            }
            $buckets[$rankscore][] = $wid;
        }       
        krsort($buckets);
        foreach($buckets as $rankscore=>$widlist)
        {
            foreach($widlist as $wid)
            {
                $sorted[] = $wid;
            }
        }
        return $sorted;
    }
    
    /**
     * Higher score should be scheduled first
     */
    private function getRankScore($importance, $remaining_effort_hours, $ddw_count, $daw_count, $days_until_locked_end_dt=NULL)
    {
        $effort_factor = min(100, $remaining_effort_hours);
        if($days_until_locked_end_dt === NULL)
        {
            //Not factored in
            $date_factor = 0;
        } else {
            //Factor this into our rank
            if($days_until_locked_end_dt < 0)
            {
                //Already too late!
                $days_until_locked_end_dt = 0;
            }
            $date_factor = 1994 - min(1994, $days_until_locked_end_dt);
        }
        return round($importance * 100 - $effort_factor + (2*$ddw_count) + ($daw_count/2) + $date_factor);
    }
    
    private function getLowestLockedEndDateOrNull($one_computed_workitem)
    {
        $result = NULL;
        if(!empty($one_computed_workitem['actual_end_dt']))
        {
            $result = $one_computed_workitem['actual_end_dt'];
        } else
        if($one_computed_workitem['planned_end_dt_locked_yn'])
        {
            $result = $one_computed_workitem['planned_end_dt'];
        }
        return $result;
    }

    private function getDayCountOrAltValue($from_dt,$until_dt,$personid=NULL,$alt_value=NULL)
    {
        if(!empty($from_dt) && !empty($until_dt))
        {
            $result = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($from_dt, $until_dt, $personid);
        } else {
            $result = $alt_value;
        }
        return $result;
    }
    
    /**
     * Find a solution
     */
    public function getSolution($this_uid, $initial_projinfo, $all_computed_workitems, $networkbundle)
    {
        try
        {
            if(!$this->m_oProjectAutofillDataBundle->isReadyForComputations())
            {
                throw new \Exception("The data bundle is NOT ready for computations yet!");
            }
            if(empty($this->m_projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $oPAFDB = $this->m_oProjectAutofillDataBundle;
            $dbvalues_at_start = $this->m_oMapHelper->getAutoFillRelevantWorkitemValueMap($this->m_projectid);

            $MAX_REH_ALLOWED = BIGFATHOM_MAX_ALLOWED_REH;
            $MAX_DURATION_SECONDS = BIGFATHOM_MAX_AUTOFILL_SECONDS;  //Quit if we exceed this 
            $track_wid2duration = [];
            $time_start = microtime(TRUE);
            $updated_dt = date("Y-m-d H:i", time());
            $aborted_yn = 0;
            $map_unprocessed_wids = [];
            $oPAFDB->clearRelevantWorkitemCache();
            
            //Read the flags to control behavior
            $update_ALL_WORK = $this->getFlagValue4ScopeUpdateAllWork();// UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_scope','ALL_WORK',FALSE);
            $update_replace_unlocked_dates = $this->getFlagValue4ReplaceUnlockedDates();// UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_unlocked_dates',TRUE,FALSE);
            $update_replace_blank_dates = $this->getFlagValue4ReplaceBlankDates();
            $update_replace_unlocked_effort = $this->getFlagValue4ReplaceUnlockedEffort();
            $update_replace_blank_effort = $this->getFlagValue4ReplaceBlankEffort();

            $bound_start_dt = NULL;
            if($initial_projinfo['actual_start_dt'] !== NULL)
            {
                $bound_start_dt = $initial_projinfo['actual_start_dt'];
            } else {
                if($initial_projinfo['planned_start_dt'] !== NULL && $initial_projinfo['planned_start_dt_locked_yn'])
                {
                    $bound_start_dt = $initial_projinfo['planned_start_dt'];
                }
            }
            if($bound_start_dt < $this->m_today_dt)
            {
                $bound_start_dt = $this->m_today_dt;
            }

            $planning_flags_ar = $this->m_raw_flags_ar;
            $planning_flags_ar['today_dt'] = $bound_start_dt;
            
            $failed_workitems = [];
            $special_comment_workitems = [];
            $updatedcount = 0;
            $num_candidates = 0;
            $num_failed = 0;
            $updated_workitems = [];
            $travel_map = [];

            $all_wid_map = [];
            $all_editable_map = [];
            $work_owner_personid_ar = [];

            $wid_already_set_map = [];
            
            $just_wids = array_keys($networkbundle['all']['keys']);

            foreach($just_wids as $wid)
            {
                $all_wid_map[$wid] = $wid;
                $one_computed_workitem = $all_computed_workitems[$wid];
                $owner_projectid = $one_computed_workitem['owner_projectid'];
                if($owner_projectid == $this->m_projectid)
                {
                    $locks_info = $networkbundle['map']['workitem2locks'][$wid];
                    $owner_personid = $one_computed_workitem['owner_personid'];
                    $is_locked_planned_start_dt_locked_yn = $one_computed_workitem['planned_start_dt_locked_yn'] == 1;
                    $is_locked_planned_end_dt_locked_yn = $one_computed_workitem['planned_end_dt_locked_yn'] == 1;
                    $both_dates_locked_yn = $is_locked_planned_start_dt_locked_yn && $is_locked_planned_end_dt_locked_yn;
                    $work_owner_personid_ar[$owner_personid] = $owner_personid;
                    $ok4project = !empty($one_computed_workitem['owner_projectid']) && $this->m_projectid == $one_computed_workitem['owner_projectid'];
                    $okay2edit = ($ok4project && !$both_dates_locked_yn && (($update_ALL_WORK || $owner_personid == $this_uid)) ? 1 : 0);
                    $all_computed_workitems[$wid]['okay2edit'] = $okay2edit;
                    $winfo = [];
                    $wdetail = $all_computed_workitems[$wid];
                    $winfo['wid'] = $wid;
                    $winfo['reh'] = !empty($all_computed_workitems[$wid]['reh']) ? $all_computed_workitems[$wid]['reh'] : $all_computed_workitems[$wid]['remaining_effort_hours']; //Old logic
                    $winfo['owner_personid'] = $all_computed_workitems[$wid]['owner_personid'];
                    $winfo['owner_projectid'] = $all_computed_workitems[$wid]['owner_projectid'];
                    //##### WARNING: strange bug in PHP when you compress too much logic into INLINE IF/THEN --- does NOT set sdt and edt to string, sets to number 1 instead!
                    if($locks_info['sdt_locked_yn'])
                    {
                        if(!empty($wdetail['sdt']))
                        {
                            $winfo['sdt'] = $wdetail['sdt'];
                        } else {
                            if(!empty($wdetail['actual_start_dt']))
                            {
                                $winfo['sdt'] = $wdetail['actual_start_dt'];
                            } else {
                                $winfo['sdt'] = $wdetail['planned_start_dt'];
                            }
                        }
                    } else {
                        if(!$is_locked_planned_start_dt_locked_yn)
                        {
                            $winfo['sdt'] = NULL;
                        } else {
                            if(!empty($wdetail['actual_start_dt']))
                            {
                                $winfo['sdt'] = $wdetail['actual_start_dt'];
                            } else {
                                $winfo['sdt'] = $wdetail['planned_start_dt'];
                            }
                        }
                    }
                    if($locks_info['edt_locked_yn'])
                    {
                        if(!empty($wdetail['edt']))
                        {
                            $winfo['edt'] = $wdetail['edt'];
                        } else {
                            if(!empty($wdetail['actual_end_dt']))
                            {
                                $winfo['edt'] = $wdetail['actual_end_dt'];
                            } else {
                                $winfo['edt'] = $wdetail['planned_end_dt'];
                            }
                        }
                    } else {
                        if(!$is_locked_planned_end_dt_locked_yn)
                        {
                            $winfo['edt'] = NULL;
                        } else {
                            if(!empty($wdetail['actual_end_dt']))
                            {
                                $winfo['edt'] = $wdetail['actual_end_dt'];
                            } else {
                                $winfo['edt'] = $wdetail['planned_end_dt'];
                            }
                        }
                    }
                    if($okay2edit && (!$locks_info['sdt_locked_yn'] || !$locks_info['edt_locked_yn']))
                    {
                        //This one can change we will compute dates later
                        $all_editable_map[$wid] = $wid;
                        $winfo['ok2edit_yn'] = 1;
                    } else {
                        //This one cannot change dates so write it now WITH DATES SET
                        $winfo['ok2edit_yn'] = 0;
                        $oPAFDB->setRelevantWorkitemRecord($wid, $winfo);
                        $wid_already_set_map[$wid] = $wid;
                    }
                }
            }
            
            $utilization_planning_bundle = $this->m_oWAH->getPersonUtilizationPlanningBundle($work_owner_personid_ar, $this->m_today_dt);
            $candidate_pct_bundle = $this->m_oWAH->getUtilizationInfoBundle($utilization_planning_bundle, $all_editable_map);
            $root_wid = $networkbundle['root']['wid'];
            $root_remaining_effort_hours = !empty($all_computed_workitems[$root_wid]['reh']) ? $all_computed_workitems[$root_wid]['reh'] : $all_computed_workitems[$root_wid]['remaining_effort_hours'];
            $planned_passes = 1;// BIGFATHOM_MAX_AUTOFILL_ITERATIONS;//5;
            $MAX_INNER_LOOPS = count($all_computed_workitems);
            $force_lock_project_start_dt = FALSE;   //TODO???????????
            $prev_iteration_updatedcount = -1;
            for($pass_counter=1; $pass_counter <= $planned_passes; $pass_counter++)
            {
                if(0 === $prev_iteration_updatedcount - $updatedcount)
                {
                    //Nothing changed, no need to keep refining.
                    break;
                }
                $prev_iteration_updatedcount = $updatedcount;
                $remaining_bundle = $networkbundle;
                $done_map = [];
                $not_done_map = $all_wid_map;
                $not_done_editable_map = $all_editable_map;
                
                $cb = $this->getNextListOfCandidatesBundle($remaining_bundle, $all_computed_workitems, $done_map, $not_done_editable_map);
                $process_these_now = $cb['process_now'];
                $process_these_soon = $cb['process_soon'];
                
                $master_loop_counter = 0;
                //$ptncount = count($process_these_now);
                //$ndecount = count($not_done_editable_map);
                while(!$aborted_yn && count($process_these_now) > 0)
                {
                    $master_loop_counter++;

                    if($master_loop_counter > $MAX_INNER_LOOPS)
                    {
                        drupal_set_message("TOO MANY ITERATIONS ($master_loop_counter) with process_these_now=" . print_r($process_these_now,TRUE), "error");
                        break;
                    }

                    $sorted_candidate_list = $this->getTimePrioritizedSortedList($all_computed_workitems, $process_these_now);

                    foreach($sorted_candidate_list as $wid)
                    {
                        $track_wid2duration[$wid] = [];
                        $track_wid2duration[$wid]['start_ts'] =  microtime(true);

                        $travel_map[$wid]['processing_status'] = 'started';
                        $map_wid_allocations2ignore = $not_done_editable_map;
                        $planning_flags_ar['workitems2exclude'] = $map_wid_allocations2ignore;
                        $planning_flags_ar['workitem2compute'] = $wid;
                        $planning_flags_ar['utilization_planning_bundle'] = $utilization_planning_bundle;
                        $planning_flags_ar['candidate_pct_bundle'] = $candidate_pct_bundle;
                        $planning_flags_ar['process_these_soon'] = $process_these_soon;
                        
                        $planning_flags_ar['force_lock_project_start_dt'] = $force_lock_project_start_dt;
                        
                        $one_wdetail = $dbvalues_at_start[$wid];
                        $reh = !empty($one_wdetail['reh']) ? $one_wdetail['reh'] : $one_wdetail['remaining_effort_hours'];
                        
                        if($reh > $MAX_REH_ALLOWED)
                        {
                            
                            //Too many hours for one work item
                            drupal_set_message("Skipping workitem#$wid because $reh effort hours exceeds maximum of $MAX_REH_ALLOWED",'warning');
                            $fit_feedback = [];
                            $fit_existing_score = 0;
                            $fit_alternative_score = 0;
                            $fit_has_solution = 0;
                            
                        } else {
                            
                            $fit_feedback = $this->getFitFeedback($wid, $one_wdetail);
                            $fit_existing_score = $fit_feedback['existing']['score'];
                            $fit_alternative_score = $fit_feedback['alternative']['score'];
                            $fit_has_solution = $fit_alternative_score > 0 || $fit_existing_score > 0;
                        }
                        
                        $one_computed_workitem = $all_computed_workitems[$wid];
                        $is_root_goal = !empty($one_computed_workitem['root_of_projectid']);
                        
                        $owner_personid = $one_computed_workitem['owner_personid'];
                        $ok4project = !empty($one_computed_workitem['owner_projectid']) && $this->m_projectid == $one_computed_workitem['owner_projectid'];
                        
                        $limit_branch_effort_hours_cd = $one_wdetail['limit_branch_effort_hours_cd'];
                        $is_blank_planned_start_dt = empty($one_wdetail['planned_start_dt']);
                        $is_blank_planned_end_dt = empty($one_wdetail['planned_end_dt']);
                        $is_blank_branch_effort_hours_est = empty($one_wdetail['branch_effort_hours_est']) || $one_wdetail['branch_effort_hours_est'] == 0;

                        $is_locked_branch_effort_hours_est_locked_yn = $limit_branch_effort_hours_cd == 'L';
                        $is_locked_planned_start_dt_locked_yn = $one_wdetail['planned_start_dt_locked_yn'] == 1;
                        $is_locked_planned_end_dt_locked_yn = $one_wdetail['planned_end_dt_locked_yn'] == 1;

                        $ok4start_date = ($update_replace_unlocked_dates && !$is_locked_planned_start_dt_locked_yn) 
                                || ($update_replace_blank_dates && $is_blank_planned_start_dt);
                        $ok4end_date = ($update_replace_unlocked_dates && !$is_locked_planned_end_dt_locked_yn) 
                                || ($update_replace_blank_dates && $is_blank_planned_end_dt);
                        $ok4branch_effort = ($update_replace_unlocked_effort && !$is_locked_branch_effort_hours_est_locked_yn) 
                                || ($update_replace_blank_effort && $is_blank_branch_effort_hours_est);

                        $is_change_candidate = FALSE;
                        $changecount = 0;
                        $fields_ar = [];
                        $failed_detail = [];
                        $special_detail = [];
                        $is_failed = FALSE;

                        $is_in_project = $one_computed_workitem['owner_projectid'] == $this->m_projectid;
                        
                        if(!$is_in_project)
                        {
                            $is_change_candidate = FALSE;
                            if(!isset($one_computed_workitem['owner_projectid']))
                            {
                                throw new \Exception("Missing required owner_projectid field in " . print_r($one_computed_workitem,TRUE));
                            }
                        } else {
                            if($ok4start_date || $ok4end_date || $ok4branch_effort)
                            {
                                $is_change_candidate = TRUE;
                                $num_candidates++;
                            }
                        }

                        $is_okay_fit = NULL;
                        $failed_fit = FALSE;
                        if($reh > 0 && $is_in_project && (!$fit_has_solution || (!$is_change_candidate && $fit_existing_score == 0)))
                        {
                            $is_okay_fit = FALSE;
                            $failed_fit = TRUE;
                            if($pass_counter > 1)
                            {
                                $failmsg = "Could not find a good fit for workitem#$wid at pass#$pass_counter";
                            } else {
                                $failmsg = "Could not find a good fit for workitem#$wid";
                            }
                            drupal_set_message($failmsg,"warning");
                        }
                        
                        if(!$is_change_candidate)
                        {

                            //We will not change this one
                            $winfo = [];
                            $winfo['wid'] = $wid;
                            $winfo['reh'] = !empty($all_computed_workitems[$wid]['reh']) ? $all_computed_workitems[$wid]['reh'] : $all_computed_workitems[$wid]['remaining_effort_hours'];
                            $winfo['owner_personid'] = $all_computed_workitems[$wid]['owner_personid'];
                            $winfo['owner_projectid'] = $all_computed_workitems[$wid]['owner_projectid'];
                            $winfo['actual_start_dt'] = $wdetail['actual_start_dt'];
                            $winfo['actual_end_dt'] = $wdetail['actual_end_dt'];
                            $winfo['planned_start_dt'] = $wdetail['planned_start_dt'];
                            $winfo['planned_end_dt'] = $wdetail['planned_end_dt'];
                            $oPAFDB->setRelevantWorkitemRecord($wid, $winfo);
                            $wid_already_set_map[$wid] = $wid;
                                
                        } else {
                            
                            if(empty($fit_feedback['buc_info']))
                            {
                                drupal_set_message("No bottom-up information computed for wid#$wid",'warning');
                            } else {
                                $buc_info = $fit_feedback['buc_info'];

                                $reh = isset($buc_info['insight']['reh']) ? $buc_info['insight']['reh'] : NULL;
                                $ant_branch_reh = isset($buc_info['insight']['ant_branch_reh']) ? $buc_info['insight']['ant_branch_reh'] : NULL;
                                $total_branch_remaining_effort_hours = $reh + $ant_branch_reh;
                                if($ok4branch_effort)
                                {
                                    $existing_branch_effort_hours_est = $one_wdetail['branch_effort_hours_est'];
                                    $beh_diff = abs($existing_branch_effort_hours_est - $total_branch_remaining_effort_hours);
                                    if($beh_diff >= .01)
                                    {
                                        $fields_ar['branch_effort_hours_est'] = $total_branch_remaining_effort_hours;
                                        $fields_ar['branch_effort_hours_est_p'] = NULL;
                                        $changecount++;
                                    }
                                }

                                $best_start_dt = NULL;
                                $best_end_dt = NULL;
                                if($failed_fit)
                                {
                                    $is_okay_fit = FALSE;
                                    $is_failed = TRUE;
                                    $failed_detail[] = "cannot compute bounds";
                                } else if($fit_alternative_score <= $fit_existing_score) {
                                    //Lets leave it alone.
                                    $is_okay_fit = TRUE;
                                } else {
                                    //Lets change it.
                                    $best_start_dt = $fit_feedback['alternative']['new_start_dt'];
                                    $best_end_dt = $fit_feedback['alternative']['new_end_dt'];
                                    if($ok4start_date && !empty($best_start_dt))
                                    {
                                        $fields_ar['planned_start_dt'] = $best_start_dt;
                                        $changecount++;
                                    }
                                    if($ok4end_date && !empty($best_end_dt))
                                    {
                                        $fields_ar['planned_end_dt'] = $best_end_dt;
                                        $changecount++;
                                    }
                                }

                                if(count($special_detail) > 0)
                                {
                                    $special_comment_workitems[$wid] = implode(" and ", $special_detail);
                                }
                                if($is_failed)
                                {
                                    //Capture the failed info
                                    $num_failed++;
                                    $failed_workitems[$wid] = implode(" and ", $failed_detail);
                                }

                                //Done with this workitem, update our in-memory collections with values
                                $winfo = [];
                                $cleanupdatefields = [];
                                foreach($fields_ar as $name=>$value)
                                {
                                    if(!empty($value))
                                    {
                                        $cleanupdatefields[$name] = $value;  
                                    } else {
                                        $cleanupdatefields[$name] = NULL;
                                    }
                                }
                                $winfo['wid'] = $wid;
                                $winfo['reh'] = !empty($all_computed_workitems[$wid]['reh']) ? $all_computed_workitems[$wid]['reh'] : $all_computed_workitems[$wid]['remaining_effort_hours'];
                                $winfo['owner_personid'] = $all_computed_workitems[$wid]['owner_personid'];
                                $winfo['owner_projectid'] = $all_computed_workitems[$wid]['owner_projectid'];
                                $winfo['actual_start_dt'] = $wdetail['actual_start_dt'];
                                $winfo['actual_end_dt'] = $wdetail['actual_end_dt'];
                                $winfo['planned_start_dt'] = !empty($cleanupdatefields['planned_start_dt']) ? $cleanupdatefields['planned_start_dt'] : $all_computed_workitems[$wid]['planned_start_dt'];
                                $winfo['planned_end_dt'] = !empty($cleanupdatefields['planned_end_dt']) ? $cleanupdatefields['planned_end_dt'] : $all_computed_workitems[$wid]['planned_end_dt'];
                                $winfo['autofill_info']['update_count'] = empty($winfo['autofill_info']['update_count']) ? 1 : $winfo['autofill_info']['update_count'] + 1;
                                $winfo['autofill_info']['updated_ts'] = microtime(TRUE);

                                $oPAFDB->setRelevantWorkitemRecord($wid, $winfo);

                                //Anything changed that needs to go to the database?    
                                if($changecount>0)
                                {
                                    $oPAFDB->markWorkitemRecordForDatabaseUpdate($wid, $cleanupdatefields);
                                    $updatedcount++;
                                    $updated_workitems[$wid] = $cleanupdatefields;
                                }
                            }
                        }

                        unset($not_done_map[$wid]);
                        unset($not_done_editable_map[$wid]);
                        $done_map[$wid] = $wid;
                        $travel_map[$wid]['processing_status'] = 'done';

                        $track_wid2duration[$wid]['end_ts'] =  microtime(true);
                        $exec_time = $track_wid2duration[$wid]['end_ts'] - $track_wid2duration[$wid]['start_ts'];
                        $track_wid2duration[$wid]['duration_seconds'] =  $exec_time;


                    }   //END LOOP CANDIDATES

                    //Save our changes so far, if any, to the database now if more than 10
                    $oPAFDB->saveUpdatesToDatabase(10);       
                    
                    $remaining_bundle = $this->getPrunedRemainingBundle($remaining_bundle, $process_these_now);
                    $time_end = microtime(TRUE);
                    $exec_time = $time_end - $time_start;
                    if($exec_time < $MAX_DURATION_SECONDS)
                    {
                        //OK so far
                        $cb = $this->getNextListOfCandidatesBundle($remaining_bundle, $all_computed_workitems, $done_map, $not_done_editable_map);
                        $process_these_now = $cb['process_now'];
                        $process_these_soon = $cb['process_soon'];
                        
                    } else {
                        
                        foreach($process_these_soon as $done_wid)   //Superset
                        {
                            $map_unprocessed_wids[$done_wid] = $done_wid;
                        }
                        foreach($process_these_now as $done_wid)    //Yes, LOOP the other array NOT the one we are unsetting!
                        {
                            unset($map_unprocessed_wids[$done_wid]);    //Remove from superset
                        }
                        $unprocessed_count = count($map_unprocessed_wids);
                        $rounded_exec_time = round($exec_time,2);
                        if($pass_counter > 1 || $unprocessed_count == 0)
                        {
                            //This is okay.
                            $refining_passes = $pass_counter - 1;
                            if($refining_passes > 0)
                            {
                                $abort_msg = "Quiting auto-fill early after completing initial pass and $refining_passes refining passes due to current machine configuration resource constraints (runtime $rounded_exec_time seconds)";
                            } else {
                                $abort_msg = "Quiting auto-fill early after completing one pass due to current machine configuration resource constraints (runtime $rounded_exec_time seconds)";
                            }
                            drupal_set_message($abort_msg,'info');
                        } else {
                            //This is not okay.
                            if($unprocessed_count == 1)
                            {
                                $abort_msg = "Quiting auto-fill early (did not process 1 workitem) due to current machine configuration resource constraints (runtime $rounded_exec_time seconds)";
                            } else {
                                $abort_msg = "Quiting auto-fill early (did not process $unprocessed_count workitems) due to current machine configuration resource constraints (runtime $rounded_exec_time seconds)";
                            }
                            drupal_set_message($abort_msg,'warning');
                        }
                        //Definitely quit the loop now!
                        $aborted_yn = 1;
                        break;
                    }
                    if($aborted_yn)
                    {
                        break;
                    }
                } //END WHILE WE HAVE CANDIDATES
                if($aborted_yn)
                {
                    break;
                }
            }   //END LOOP REFINEMENT PASSES
            
            //Write any remaining changes to the database now
            $oPAFDB->saveUpdatesToDatabase();
            
            $num_updated = $updatedcount;
            if($num_updated > 0)
            {
                $this->m_oCoreWriteHelper->markProjectUpdated($this->m_projectid, "updated $num_updated workitems");
            }
            $result_bundle = [];
            $result_bundle['aborted_yn'] = $aborted_yn;
            $result_bundle['pass_counter'] = $pass_counter;
            $result_bundle['map_unprocessed_wids'] = $map_unprocessed_wids;
            $result_bundle['track_wid2duration'] = $track_wid2duration;
            $result_bundle['num_updated'] = $num_updated;
            $result_bundle['num_candidates'] = $num_candidates;
            $result_bundle['num_failed'] = $num_failed;
            
            $result_bundle['updated_workitems'] = $updated_workitems;
            $result_bundle['failed_workitems'] = $failed_workitems;
            $result_bundle['special_comment_workitems'] = $special_comment_workitems;
            
            return $result_bundle;  //$num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function setFlagMembers($flags_ar)
    {
        try
        {
            $this->m_raw_flags_ar = $flags_ar;
            $this->m_flag_availability_type_BY_OWNER = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_availability_type','BY_OWNER',TRUE);
            $this->m_min_reference_dt = isset($flags_ar['min_initial_reference_dt']) ? $flags_ar['min_initial_reference_dt'] : NULL;
            $this->m_max_reference_dt = isset($flags_ar['max_initial_reference_dt']) ? $flags_ar['max_initial_reference_dt'] : NULL;
            $this->m_today_dt = isset($flags_ar['today_dt']) ? $flags_ar['today_dt'] : NULL;
            
            //Read the flags to control behavior
            $this->m_flag_update_ALL_WORK = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_scope','ALL_WORK',FALSE);
            $this->m_update_replace_unlocked_dates = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_unlocked_dates',TRUE,FALSE);
            if($this->m_update_replace_unlocked_dates)
            {
                $this->m_update_replace_blank_dates = TRUE;
            } else {
                $this->m_update_replace_blank_dates = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_blank_dates',TRUE,FALSE);
            }
            $this->m_update_replace_unlocked_effort = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_unlocked_effort',TRUE,FALSE);
            if($this->m_update_replace_unlocked_effort)
            {
                $this->m_update_replace_blank_effort = TRUE;
            } else {
                $this->m_update_replace_blank_effort = \bigfathom\UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_blank_effort',TRUE,FALSE);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getFlagValue4ScopeUpdateAllWork()
    {
        return $this->m_flag_update_ALL_WORK;
    }
    
    private function getFlagValue4ReplaceUnlockedDates()
    {
        return $this->m_update_replace_unlocked_dates;
    }
    
    private function getFlagValue4ReplaceBlankDates()
    {
        return $this->m_update_replace_blank_dates;
    }

    private function getFlagValue4ReplaceUnlockedEffort()
    {
        return $this->m_update_replace_blank_dates;
    }
    
    private function getFlagValue4ReplaceBlankEffort()
    {
        return $this->m_update_replace_blank_effort;
    }

}
