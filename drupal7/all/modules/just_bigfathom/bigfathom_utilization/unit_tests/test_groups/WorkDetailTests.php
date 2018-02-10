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
 * Collection of some work detail tests for the utilization module
 *
 * @author Frank Font
 */
class WorkDetailTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;
    
    private $m_oUtility = NULL;
    private $m_oProjectHelper = NULL;
    private $m_oUtilizationHelper = NULL;
    
    private $m_all_active_projectids = NULL;
    
    function getVersionNumber()
    {
        return '20171010.1';
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
            
            $loaded_wh = module_load_include('php','bigfathom_core','core/WriteHelper');
            if(!$loaded_wh)
            {
                throw new \Exception('Failed to load the WriteHelper class');
            }
            $this->m_oWriteHelper = new \bigfathom\WriteHelper();
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            
            $loaded_masterdd = module_load_include('php','bigfathom_utilization','core/MasterDailyDetail');
            if(!$loaded_masterdd)
            {
                throw new \Exception('Failed to load the bigfathom_utilization MasterDailyDetail class');
            }
            
            $loaded_helper_projects = module_load_include('php','bigfathom_core','unit_tests/utilities/ProjectHelper');
            if(!$loaded_helper_projects)
            {
                throw new \Exception('Failed to load the ProjectHelper class');
            }
            $this->m_oProjectHelper = new \bigfathom_core\ProjectHelper($this->m_oContext, $this->m_oMapHelper, $this->m_oUAH, $this->m_oWriteHelper);
            
            module_load_include('php','bigfathom_utilization','unit_tests/utilities/UtilizationHelper');
            $this->m_oUtilizationHelper = new \bigfathom_utilization\UtilizationHelper();
            
            $this->m_oUtility = new \bigfathom_utilization\Utility();
            
            $this->m_all_active_projectids = $this->m_oMapHelper->getProjectsIDs(TRUE);
            $this->m_all_active_personids = array_keys($this->m_oMapHelper->getPersonsByID());
            
            if(count($this->m_all_active_projectids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_projectids) . ' existing projects available for test.';
            }
            if(count($this->m_all_active_personids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_personids) . ' existing people available for test';
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
            
            $this->m_oProjectHelper->deleteAllTestProjects();
            
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
    
    private function getPeopleIDList()
    {
        $combined = [];
        $combined[1] = 1;
        foreach($this->m_all_active_personids as $personid)
        {
            $combined[$personid] = $personid;
            if(count($combined) > 3)
            {
                //Thats enough
                break;
            }
        }
        return $combined;
    }
    
    private function createPersonWorkitem($personid, $projectid, $workitem_basetype='G', $myvalues=NULL)
    {
        try
        {
            if(empty($myvalues))
            {
                $myvalues = [];
            }
            if(empty($personid))
            {
                throw new \Exception("BAD INPUT MISSING personid!");
            }
            if(empty($projectid))
            {
                throw new \Exception("BAD INPUT MISSING projectid!");
            }
            $myvalues['owner_personid'] = $personid;
            $myvalues['owner_projectid'] = $projectid;
            $result = $this->m_oProjectHelper->addWorkitemsToOurTestProject($projectid, $workitem_basetype, $myvalues);
            return $result;
            
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
    
    function testFutureUtilizationWorkDetailOverage()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $workitem_sdt_gap_days = 0;
            $bundleinfo = $this->createTestUtilizationWorkDetai(4, 10, $workitem_sdt_gap_days, 10);
            $insight['core'] = $bundleinfo['insight'];
            $check_start_dt = $bundleinfo['checkrange']['sdt'];
            $check_end_dt = $bundleinfo['checkrange']['edt'];
            $personid_ar = $bundleinfo['personid_ar'];
            $person2wids_map = $bundleinfo['person2wids_map'];
            $total_workitems = $bundleinfo['total_workitems'];
            $oMasterDailyDetail = $bundleinfo['master_instance'];
           
            $insight['workitem_count'] = $total_workitems;
            $insight['busy_bad_days'] = 0;
            $insight['busy_good_days'] = 0;
            
            //Now examine the utilization details
            $personid_map = [];
            foreach($personid_ar as $personid)
            {
                $personid_map[$personid] = $personid;
            }
            $check_personid_ar = $oMasterDailyDetail->getAllPersonIDs();
            $check_personid_map = [];
            foreach($check_personid_ar as $personid)
            {
                $check_personid_map[$personid] = $personid;
                if(!isset($personid_map[$personid]))
                {
                    throw new \Exception("The master has a personid(#$personid) not in our original input " . print_r($personid_ar,TRUE));
                }
            }
            foreach($personid_ar as $personid)
            {
                $check_personid_map[$personid] = $personid;
                if(!isset($check_personid_map[$personid]))
                {
                    throw new \Exception("The master is missing a personid(#$personid) from our original input!  Master has " . print_r($check_personid_map,TRUE));
                }
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                if(empty($oPAI))
                {
                    throw new \Exception("Got NULL for PAI of person#$personid");
                }
                $created_wids_map = $person2wids_map[$personid];
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                if(count($created_wids_map) !== count($all_wids))
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Count mismatch between created workitems and found relevant workitems! $found_tx vs $created_tx");
                }
                foreach($all_wids as $onewid)
                {
                    if(!isset($created_wids_map[$onewid]))
                    {
                        $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                        $found_tx = "found=" . print_r($all_wids,TRUE);
                        throw new \Exception("Found unexpected wid#$onewid! $found_tx vs $created_tx");
                    }
                }
                
                //$person_dd_bundle = $oPAI->getTemplateDailyDetailBundle();
                $padb = $oPAI->getAvailabilityDetailBundle();
                //throw new \Exception("LOOK personid#$personid " . print_r($padb,TRUE));
                
                $pdd_insight = $this->m_oUtilizationHelper->getPersonDailyDetailBundleInsight($padb);
                
                $found_wids = $pdd_insight['wids'];
                $found_relevant_wids = $found_wids['relevant'];
                //$overage_static_wids_map = $padb['overages']['static'];
                $overage_relevant_wids_map = $padb['overages']['relevant'];
                foreach($all_wids as $onewid)
                {
                    if(!isset($found_relevant_wids[$onewid]) && !($overage_relevant_wids_map[$onewid]))
                    {
                        $overages_wids = array_keys($overage_relevant_wids_map);
                        $found_tx = "found=" . print_r(array_keys($found_relevant_wids),TRUE);
                        $expected_tx = "expected=" . print_r($all_wids,TRUE);
                        $pdd_insight_tx = print_r($pdd_insight,TRUE);
                        throw new \Exception("Did not find wid#$onewid in daily detail! ($found_tx vs $expected_tx)"
                                . "<br>analysis=$pdd_insight_tx person2wids_map[$personid]=" . print_r($person2wids_map[$personid],TRUE)
                                . " <br>overages_wids=" . print_r($overages_wids,TRUE));
                    }
                }
                
                $busy_bad_days = $pdd_insight['busy_bad_days'];
                $insight['workitem_count'] = $total_workitems;
                $insight['busy_bad_days'] += $busy_bad_days;
                $insight['busy_good_days'] += $busy_good_days;
                if($busy_bad_days < 1)
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Expected NON ZERO busy_bad_days for personid#$personid ($created_tx) in " . print_r($pdd_insight,TRUE));
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

    function testFutureUtilizationWorkDetailFits()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $workitem_sdt_gap_days = 12;    //So they do NOT overlap
            $bundleinfo = $this->createTestUtilizationWorkDetai(4, 10, $workitem_sdt_gap_days, 10);
            $insight['core'] = $bundleinfo['insight'];
            $check_start_dt = $bundleinfo['checkrange']['sdt'];
            $check_end_dt = $bundleinfo['checkrange']['edt'];
            $personid_ar = $bundleinfo['personid_ar'];
            $person2wids_map = $bundleinfo['person2wids_map'];
            $total_workitems = $bundleinfo['total_workitems'];
            $oMasterDailyDetail = $bundleinfo['master_instance'];
            
            $insight['workitem_count'] = $total_workitems;
            $insight['busy_bad_days'] = 0;
            $insight['busy_good_days'] = 0;
            
            //Now examine the utilization details
            $personid_map = [];
            foreach($personid_ar as $personid)
            {
                $personid_map[$personid] = $personid;
            }
            $check_personid_ar = $oMasterDailyDetail->getAllPersonIDs();
            $check_personid_map = [];
            foreach($check_personid_ar as $personid)
            {
                $check_personid_map[$personid] = $personid;
                if(!isset($personid_map[$personid]))
                {
                    throw new \Exception("The master has a personid(#$personid) not in our original input " . print_r($personid_ar,TRUE));
                }
            }
            foreach($personid_ar as $personid)
            {
                $check_personid_map[$personid] = $personid;
                if(!isset($check_personid_map[$personid]))
                {
                    throw new \Exception("The master is missing a personid(#$personid) from our original input!  Master has " . print_r($check_personid_map,TRUE));
                }
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                if(empty($oPAI))
                {
                    throw new \Exception("Got NULL for PAI of person#$personid");
                }
                $created_wids_map = $person2wids_map[$personid];
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                if(count($created_wids_map) !== count($all_wids))
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Count mismatch between created workitems and found relevant workitems! $found_tx vs $created_tx");
                }
                foreach($all_wids as $onewid)
                {
                    if(!isset($created_wids_map[$onewid]))
                    {
                        $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                        $found_tx = "found=" . print_r($all_wids,TRUE);
                        throw new \Exception("Found unexpected wid#$onewid! $found_tx vs $created_tx");
                    }
                }
                
                //$person_dd_bundle = $oPAI->getTemplateDailyDetailBundle();
                $padb = $oPAI->getAvailabilityDetailBundle($check_start_dt, $check_end_dt);
                $pdd_insight = $this->m_oUtilizationHelper->getPersonDailyDetailBundleInsight($padb);
                
                $found_wids = $pdd_insight['wids'];
                $found_relevant_wids = $found_wids['relevant'];
                $overage_static_wids_map = $padb['overages']['static'];
                $overage_relevant_wids_map = $padb['overages']['relevant'];
                foreach($all_wids as $onewid)
                {
                    if(!isset($found_relevant_wids[$onewid]) && !($overage_relevant_wids_map[$onewid]))
                    {
                        $overages_wids = array_keys($overage_relevant_wids_map);
                        $found_tx = "found=" . print_r(array_keys($found_relevant_wids),TRUE);
                        $expected_tx = "expected=" . print_r($all_wids,TRUE);
                        $pdd_insight_tx = print_r($pdd_insight,TRUE);
                        throw new \Exception("Did not find wid#$onewid in daily detail! ($found_tx vs $expected_tx)"
                                . "<br>analysis=$pdd_insight_tx person2wids_map[$personid]=" . print_r($person2wids_map[$personid],TRUE)
                                . " <br>overages_wids=" . print_r($overages_wids,TRUE));
                    }
                }
                
                $busy_bad_days = $pdd_insight['busy_bad_days'];
                $busy_good_days = $pdd_insight['busy_good_days'];
                $insight['busy_bad_days'] += $busy_bad_days;
                $insight['busy_good_days'] += $busy_good_days;
                if($busy_bad_days > 0 || $busy_good_days < $duration_days)
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Expected ZERO busy_bad_days and at least $duration_days busy_good_days for personid#$personid ($created_tx) in " 
                            . \bigfathom\DebugHelper::getNeatMarkup($pdd_insight,"PDD INSIGHTS")
                            );
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
    
    private function createTestUtilizationWorkDetai($workitem_reh=4, $workitem_duration=10
            , $workitem_sdt_offset_days=12
            , $workitem_createcount=10)
    {
        try
        {
            $bundleinfo = [];
            $insight_core = [];
            $project_owner_personid = 1;
            $open_calendar_start_dt =  $this->m_oUtilizationHelper->getDateAfterAllExistingWork(NULL,30);
            $test_project_bundle = $this->m_oProjectHelper->createTestProject($project_owner_personid,$open_calendar_start_dt,NULL,1);
            $projectid = $test_project_bundle['newid'];

            $projects2ignore = $projectid;
            $personid_ar = $this->getPeopleIDList();
            
            $insight_core['maps'] = array(
                'created_project'=>$test_project_bundle
                , 'projects2ignore'=>$projects2ignore
                , 'people'=>$personid_ar
            );
            
            $highest_start_dt = $open_calendar_start_dt;
            $person2open_start_dt = [];
            $person2prevworkdates_dt = [];
            foreach($personid_ar as $owner_personid)
            {
                $thisperson_first_open_dt = $this->m_oUtilizationHelper->getDateAfterAllExistingWork($owner_personid,10);
                $highest_start_dt = max($thisperson_first_open_dt,$open_calendar_start_dt);
            }
            foreach($personid_ar as $owner_personid)
            {
                $person2open_start_dt[$owner_personid] = $highest_start_dt;
            }
            $insight_core['maps']['person2open_start_dt'] = $person2open_start_dt;
            
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = 2;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = 32;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $end_shift_days);
            $remaining_effort_hours = $workitem_reh;
            $duration_days = $workitem_duration;
            
            $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
            $embed_overages = 1;
            $oMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt, $embed_overages);
            
            //Create overage scenario and confirm the user is over allocated
            $total_workitems = 0;
            $check_end_dt = NULL;
            $check_start_dt = $open_calendar_start_dt;
            $person2wids_map = [];
            for($i=0;$i<$workitem_createcount;$i++)
            {
                $workitem_basetype = (($i % 2) == 0) ? 'G' : 'T';
                $myvalues = [];
                foreach($personid_ar as $owner_personid)
                {
                    if(isset($person2prevworkdates_dt[$owner_personid]))
                    {
                        $pdr = $person2prevworkdates_dt[$owner_personid];
                        $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($pdr['sdt'], $workitem_sdt_offset_days);
                        $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $duration_days);
                    } else {
                        $start_dt = $person2open_start_dt[$owner_personid];
                        $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $duration_days);
                    }
                    $person2prevworkdates_dt[$owner_personid]['sdt'] = $start_dt;
                    $person2prevworkdates_dt[$owner_personid]['edt'] = $end_dt;
                    
                    $myvalues['planned_start_dt'] = $start_dt;
                    $myvalues['planned_end_dt'] = $end_dt;
                    $myvalues['remaining_effort_hours'] = $remaining_effort_hours;
                    
                    $result = $this->createPersonWorkitem($owner_personid, $projectid, $workitem_basetype, $myvalues);
                    $wid = $result['workitemid'];
                    if(empty($person2wids_map[$owner_personid]))
                    {
                        $person2wids_map[$owner_personid] = [];
                    }
                    $person2wids_map[$owner_personid][$wid]=$wid;
                    if(empty($wid))
                    {
                        throw new \Exception("Missing workitemid in " . print_r($result, TRUE));
                    }
                    $winfo = [];
                    $winfo['wid'] = $wid;
                    $winfo['owner_personid'] = $owner_personid;
                    $winfo['sdt'] = $start_dt;
                    $winfo['edt'] = $end_dt;
                    $winfo['reh'] = $remaining_effort_hours;
                    $oMasterDailyDetail->setRelevantWorkitemRecord($wid, $winfo);
                }
                $total_workitems++;
                if($end_dt > $check_end_dt)
                {
                    $check_end_dt = $end_dt;
                }
            }
            $insight_core['maps']['checkrange'] = array($check_start_dt, $check_end_dt);
            
            $bundleinfo['insight'] = $insight_core;
            $bundleinfo['checkrange']['sdt'] = $check_start_dt;
            $bundleinfo['checkrange']['edt'] = $check_end_dt;
            $bundleinfo['personid_ar'] = $personid_ar;
            $bundleinfo['person2wids_map'] = $person2wids_map;
            $bundleinfo['total_workitems'] = $total_workitems;
            $bundleinfo['master_instance'] = $oMasterDailyDetail;

            return $bundleinfo;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
