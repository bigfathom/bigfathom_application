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

namespace bigfathom_utilization;

require_once 'Utility.php';

/**
 * Collection of some basic tests for the utilization module
 *
 * @author Frank Font
 */
class BasicQueryTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oUtility = NULL;
    private $m_oMapHelper = NULL;
    private $m_all_active_projectids = NULL;
    
    function getVersionNumber()
    {
        return '20170921.1';
    }
    
    function getNiceName()
    {
        $classname = $this->getClassName(FALSE);
        return $this->shortcutGetNiceName($classname);
    }
    
    function getClassName()
    {
        $fullname = get_class();
        return $this->shortcutGetClassNameWithoutNamespace($fullname);
    }
    
    function runAllTests()
    {
        $all_methods = $this->getAllTestMethods();
        return $this->shortcutRunAllTests($all_methods);
    }

    function setUp()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        
        $warning_ar = [];
        
        try
        {
            
            $loaded_context = module_load_include('php','bigfathom_core','core/Context');
            if(!$loaded_context)
            {
                throw new \Exception('Failed to load the Context class');
            }
            $this->m_oContext = \bigfathom\Context::getInstance();
            $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the MapHelper class');
            }
            $this->m_oMapHelper = new \bigfathom\MapHelper();
            
            $loaded_masterdd = module_load_include('php','bigfathom_utilization','core/MasterDailyDetail');
            if(!$loaded_masterdd)
            {
                throw new \Exception('Failed to load the bigfathom_utilization MasterDailyDetail class');
            }
            
            $this->m_oUtility = new \bigfathom_utilization\Utility();
            
            $this->m_all_active_projectids = $this->m_oMapHelper->getProjectsIDs(TRUE);
            $this->m_all_active_personids = array_keys($this->m_oMapHelper->getPersonsByID());
            
            if(count($this->m_all_active_projectids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_projectids) . ' projects available for test.';
            }
            if(count($this->m_all_active_personids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_personids) . ' people available for test';
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        $warning_msg = implode(' and ', $warning_ar);
        return array('warning_msg'=>$warning_msg, 'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    function tearDown()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            //Perform the TEARDOWN here
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    function isExclusiveRequired()
    {
        return FALSE;
    }

    function getAllTestMethods()
    {
        return $this->shortcutGetAllTestMethods($this);
    }
    
    private function getProjects2IgnoreInputSets()
    {
        $param_ar = [];
        $param_ar[] = NULL;
        $counter = 0;
        $combined = [];
        foreach($this->m_all_active_projectids as $projectid)
        {
            $combined[] = $projectid;
            $counter++;
            if($counter > 3)
            {
                //Thats enough
                break;
            }
        }
        if(count($combined) > 0)
        {
            $param_ar[] = $combined[0];
            $param_ar[] = $combined;
        }
        return $param_ar;
    }
    
    private function getPeopleInputMapSets()
    {
        $param_ar = [];
        $combined = [];
        $counter = 0;
        $prev_combined = [];
        foreach($this->m_all_active_personids as $personid)
        {
            $combined = $prev_combined;
            $combined[] = $personid;
            $counter++;
            if($counter > 3)
            {
                //Thats enough
                break;
            }
            $prev_combined = $combined;
            $param_ar[] = $combined;
        }
        return $param_ar;
    }
    
    private function checkPersonUtilization($oMasterDailyDetail, $winfo, $personid_override, $continue_to_zero_reh=TRUE)
    {
        try
        {
            $fit_bundle = $oMasterDailyDetail->getOnePersonWorkFitInsightBundle($winfo, $personid_override, $continue_to_zero_reh);
            $this->m_oUtility->checkFitInsightBundleContent($fit_bundle, $winfo, $personid_override);
                
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function checkDailyDetail($oMasterDailyDetail,$start_dt,$end_dt,$projects2ignore,$map_personid)
    {
        try
        {
            $daily_detail_bundle = $oMasterDailyDetail->getAllDetailBundle();
            $this->m_oUtility->checkDailyDetail($daily_detail_bundle, $start_dt, $end_dt, $projects2ignore, $map_personid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testSimpleInvalidUtilization()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            
            $param_ar = $this->getProjects2IgnoreInputSets();
            $people_maps_ar = $this->getPeopleInputMapSets();
            $insight['maps'] = array(
                'projects'=>$param_ar
                    ,'people'=>$people_maps_ar
            );
            
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = -2;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = $start_shift_days-30;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $end_shift_days);
            $insight['date_range'] = "[$start_dt,$end_dt]";
            foreach($param_ar as $projects2ignore)
            {
                foreach($people_maps_ar as $map_personid)
                {
                    $is_okay = FALSE;
                    $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
                    try
                    {
                        $oMasterDailyDetail->initialize($map_personid, $start_dt, $end_dt);
                    } catch (\Exception $ex) {
                        $is_okay = TRUE;
                        $ex = NULL;
                    }
                    if(!$is_okay)
                    {
                        throw new \Exception("Expected exception for [$start_dt, $end_dt]@projects2ignore=".print_r($projects2ignore,TRUE));
                    }
                }
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testSimpleFutureUtilization()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $param_ar = $this->getProjects2IgnoreInputSets();
            $people_maps_ar = $this->getPeopleInputMapSets();
            $insight['maps'] = array(
                'projects'=>$param_ar
                    ,'people'=>$people_maps_ar
            );
            
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = 2;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = 32;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $end_shift_days);
            $insight['date_range'] = "[$start_dt,$end_dt]";
            foreach($param_ar as $projects2ignore)
            {
                foreach($people_maps_ar as $personid_ar)
                {
                    $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
                    $oMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt);
                    $this->checkDailyDetail($oMasterDailyDetail,$start_dt,$end_dt,$projects2ignore,$personid_ar);
                    $winfo = [];
                    $winfo['reh'] = 44;
                    $winfo['sdt'] = $start_dt;
                    $winfo['edt'] = NULL;
                    foreach($personid_ar as $personid)
                    {
                        if(empty($personid))
                        {
                            throw new \Exception("BAD TEST has empty personid=$personid from map_personid=" . print_r($personid_ar,TRUE));
                        }
                        $this->checkPersonUtilization($oMasterDailyDetail, $winfo, $personid);
                    }
                }
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testSimplePastUtilization()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_ar = [];
        
        try
        {
            
            $param_ar = $this->getProjects2IgnoreInputSets();
            $people_maps_ar = $this->getPeopleInputMapSets();
            $insight['maps'] = array(
                'projects'=>$param_ar
                    ,'people'=>$people_maps_ar
            );
            
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = -32;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = -2;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $end_shift_days);
            $insight['date_range'] = "[$start_dt,$end_dt]";
            foreach($param_ar as $projects2ignore)
            {
                foreach($people_maps_ar as $personid_ar)
                {
                    $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
                    $oMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt);
                    $this->checkDailyDetail($oMasterDailyDetail,$start_dt,$end_dt,$projects2ignore,$personid_ar);
                    $winfo = [];
                    $winfo['reh'] = 44;
                    $winfo['sdt'] = $start_dt;
                    $winfo['edt'] = NULL;
                    foreach($personid_ar as $personid)
                    {
                        if(empty($personid))
                        {
                            throw new \Exception("BAD TEST has empty personid=$personid from map_personid=" . print_r($personid_ar,TRUE));
                        }
                        $this->checkPersonUtilization($oMasterDailyDetail, $winfo, $personid);
                    }
                }
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        $warning_msg = implode(' and ', $warning_ar);
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testSimpleWideUtilization()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            
            $param_ar = $this->getProjects2IgnoreInputSets();
            $people_maps_ar = $this->getPeopleInputMapSets();
            $insight['maps'] = array(
                'projects'=>$param_ar
                    ,'people'=>$people_maps_ar
            );
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = -32;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = 32;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $end_shift_days);
            $insight['date_range'] = "[$start_dt,$end_dt]";
            foreach($param_ar as $projects2ignore)
            {
                foreach($people_maps_ar as $personid_ar)
                {
                    $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
                    $oMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt);
                    $this->checkDailyDetail($oMasterDailyDetail,$start_dt,$end_dt,$projects2ignore,$personid_ar);
                    $winfo = [];
                    $winfo['reh'] = 44;
                    $winfo['sdt'] = $start_dt;
                    $winfo['edt'] = NULL;
                    foreach($personid_ar as $personid)
                    {
                        if(empty($personid))
                        {
                            throw new \Exception("BAD TEST has empty personid=$personid from map_personid=" . print_r($personid_ar,TRUE));
                        }
                        $this->checkPersonUtilization($oMasterDailyDetail, $winfo, $personid);
                    }
                }
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
}
