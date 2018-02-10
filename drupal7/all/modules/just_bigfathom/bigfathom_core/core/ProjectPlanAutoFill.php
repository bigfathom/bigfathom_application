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

namespace bigfathom;

require_once 'DatabaseNamesHelper.php';
require_once 'PersonUtilizationInsight.php';
require_once 'MapHelper.php';
require_once 'WorkApportionmentHelper.php';
require_once 'DirectedGraphAnalysis.php';

/**
 * This class helps project planning values 
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectPlanAutoFill
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oCoreWriteHelper = NULL;
    private $m_oAutofillWriteHelper = NULL;
    
    private $m_oLLH = NULL;
    private $m_iteration_counter = 0;
    private $m_raw_flags_ar = NULL;
    
    private $m_today_dt = NULL; //Because today may be bigger than our min date
    private $m_min_reference_dt = NULL;
    private $m_max_reference_dt = NULL;
    
    private $m_projectid = NULL;
    
    private $m_oAutofillEngine = NULL;
    
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
        
        $this->setFlagMembers($flags_ar);
        
        if(empty($this->m_today_dt))
        {
            $now_timestamp = time();
            $this->m_today_dt = gmdate("Y-m-d", $now_timestamp);
        }
        if(empty($this->m_min_reference_dt))
        {
            $this->m_min_reference_dt = $this->m_today_dt;
        }
        if(empty($this->m_max_reference_dt))
        {
            $this->m_max_reference_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($this->m_min_reference_dt, 444);
        } 
        if($this->m_min_reference_dt > $this->m_max_reference_dt)   
        {
            throw new \Exception("Error with reference dates! [{$this->m_min_reference_dt}] > [{$this->m_max_reference_dt}]");
        }
        
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oLLH = new \bigfathom\LinkLogicHelper();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
        $this->m_iteration_counter = 0;
        
        $loaded1a = module_load_include('php','bigfathom_autofill','core/WriteHelper');
        if(!$loaded1a)
        {
            throw new \Exception('Failed to load the autofill WriteHelper class');
        }
        $this->m_oAutofillWriteHelper = new \bigfathom_autofill\WriteHelper();
        
        $loaded1b = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded1b)
        {
            throw new \Exception('Failed to load the core WriteHelper class');
        }
        $this->m_oCoreWriteHelper = new \bigfathom\WriteHelper();

        $loaded2 = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the ProjectForecaster class');
        }
        
        $loaded3 = module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
        if(!$loaded3)
        {
            throw new \Exception('Failed to load the UtilityFormatUtilizationData class');
        }
        
        $loaded_engine = module_load_include('php','bigfathom_autofill','core/Engine');
        if(!$loaded_engine)
        {
            throw new \Exception('Failed to load the autofill Engine class');
        }
        $this->m_oAutofillEngine = new \bigfathom_autofill\Engine($projectid, $flags_ar);
    }

    /**
     * Give us date that is before the reference date and the highest of the daw end dates
     */
    private static function getLatestPriorEndDate($reference_dt, $workitem_detail_map, $cleanupdatefields, $map_daw)
    {
        $latest_dt = NULL;
        foreach($workitem_detail_map as $wid=>$wdetail)
        {
            if(!empty($cleanupdatefields[$wid]['planned_end_dt']))
            {
                //We have a better one that will be written to the DB
                $end_dt = $cleanupdatefields[$wid]['planned_end_dt'];
            } else {
                //Use the one already from the DB
                $end_dt = $wdetail['actual_end_dt'];
                if(empty($actual_end_dt))
                {
                    $end_dt = $wdetail['planned_end_dt'];
                }
            }
            if(empty($reference_dt) || $reference_dt > $end_dt || !empty($map_daw[$wid]))
            {
                if($latest_dt < $end_dt)
                {
                    $latest_dt = $end_dt;
                }
            }
        }
        return $latest_dt;
    }
    
    private function setFlagMembers($flags_ar)
    {
        try
        {
            $this->m_raw_flags_ar = $flags_ar;
            $this->m_min_reference_dt = isset($flags_ar['min_initial_reference_dt']) ? $flags_ar['min_initial_reference_dt'] : NULL;
            $this->m_max_reference_dt = isset($flags_ar['max_initial_reference_dt']) ? $flags_ar['max_initial_reference_dt'] : NULL;
            $this->m_today_dt = isset($flags_ar['today_dt']) ? $flags_ar['today_dt'] : NULL;
            
            //Read the flags to control behavior
            $this->m_flag_update_ALL_WORK = UtilityGeneralFormulas::getArrayMemberHasTextMatch($flags_ar,'flag_scope','ALL_WORK',FALSE);
            $this->m_update_replace_unlocked_dates = UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_unlocked_dates',TRUE,FALSE);
            if($this->m_update_replace_unlocked_dates)
            {
                $this->m_update_replace_blank_dates = TRUE;
            } else {
                $this->m_update_replace_blank_dates = UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_blank_dates',TRUE,FALSE);
            }
            $this->m_update_replace_unlocked_effort = UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_unlocked_effort',TRUE,FALSE);
            if($this->m_update_replace_unlocked_effort)
            {
                $this->m_update_replace_blank_effort = TRUE;
            } else {
                $this->m_update_replace_blank_effort = UtilityGeneralFormulas::getArrayMemberHasBooleanMatch($flags_ar,'flag_replace_blank_effort',TRUE,FALSE);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Compute and fill in reasonable work values
     * IMPORTANT DO NOT wrap this content in a TRANSACTION!
     */
    public function fillValues() //$effort_yn=1,$dates_yn=1,$cost_yn=1,$only_blanks_yn=1,$only_unlocked_yn=1)
    {
        try
        {
            //Make sure this user is alowed to change this project at this time.
            global $user;
            $this_uid = $user->uid;
            $projectid = $this->m_projectid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                error_log("User #$this_uid cannot edit project#$projectid content at this time!");                
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            error_log("INFO: Starting fillValues userid#$this_uid project#$projectid");

            //Compute the relative placements of the work
            $autofill_action_tracking = $this->m_oAutofillEngine->getValuesForTrackingRecordCreate();
            $autofill_result_bundle = $this->m_oAutofillWriteHelper->createProjectAutofillActionRecord($this->m_projectid,$autofill_action_tracking);
            if(!$autofill_result_bundle['okay_yn'])
            {
                //Cannot start
                $aborted_yn = 1;
                $aborted_msg = $autofill_result_bundle['aborted_msg'];
                $result['aborted_yn'] = $aborted_yn;
                $result['aborted_msg'] = $aborted_msg;
                $result['map_unprocessed_wids'] = NULL;
                $result['num_updated'] = NULL;
                $result['num_candidates'] = NULL;
                $result['num_failed'] = NULL;
                $result['updated_workitems'] = NULL;
                $result['failed_workitems'] = NULL;
                
            } else {
                //Okay, lets start
                $aborted_msg = NULL;
                $oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_projectid, $this->m_raw_flags_ar);
                $all_forecast_detail1 = $oProjectForecaster->getDetail();
                $main_project_detail1 = $all_forecast_detail1['main_project_detail'];
                $all_computed_workitems1 = $main_project_detail1['workitems'];  //TODO REPLACE THE FUNNY DATES WITH ACTUAL DATES!!!!!
                $sprint_maps = $main_project_detail1['sprint_lookup'];
                $map_workitem2sprint = $sprint_maps['workitem2sprint'];
                $initial_projinfo1 = $this->m_oMapHelper->getProjectRecord($this->m_projectid);
                
                $oDirectedGraphAnalysis = new \bigfathom\DirectedGraphAnalysis();
                $networkbundle = $oDirectedGraphAnalysis->getNetworkMapBundle($this->m_projectid, $all_computed_workitems1, $sprint_maps);
                
                $this->m_oAutofillEngine->initialize($networkbundle, $all_computed_workitems1);
                $autofill_result = $this->m_oAutofillEngine->getSolution($this_uid, $initial_projinfo1, $all_computed_workitems1, $networkbundle);
                
                $aborted_yn = $autofill_result['aborted_yn'];
                if(!$aborted_yn)
                {
                    $aborted_msg = NULL;
                } else {
                    $aborted_msg = !empty($autofill_result['aborted_msg']) ? $autofill_result['aborted_msg'] : (!empty($autofill_result['error_tx']) ? $autofill_result['error_tx'] : 'undefined error');
                }
                $result = $autofill_result;
                if($aborted_yn)
                {
                    $this->m_oAutofillWriteHelper->markProjectAutofillActionRecordFailedCompletion($this->m_projectid, $aborted_msg);
                } else {
                    $updated_workitem_count = $result['num_updated'];
                    $this->m_oAutofillEngine->setUpdatedWorkitemCount($updated_workitem_count);
                    $this->m_oAutofillWriteHelper->markProjectAutofillActionRecordSuccessfulCompletion($this->m_projectid,$this->m_oAutofillEngine);
                }
            }
            
            error_log("INFO: Finished fillValues userid#$this_uid project#$projectid");
            return $result;
            
        } catch (\Exception $ex) {
            error_log("ERROR: Failed fillValues because " . $ex);
            throw $ex;
        }
    }
}