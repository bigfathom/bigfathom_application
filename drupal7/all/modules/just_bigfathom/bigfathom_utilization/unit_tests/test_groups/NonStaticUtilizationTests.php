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
class NonStaticUtilizationTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;
    
    private $m_oUtilizationHelper = NULL;
    
    private $m_oUtility = NULL;
    private $m_oProjectHelper = NULL;
    
    private $m_all_active_projectids = NULL;
    
    private $m_our_test_project_bundle = NULL;
    
    function getVersionNumber()
    {
        return '20170926.1';
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
        error_log("UNIT TEST starting setUp " . get_class($this));
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

            //Just create one project
            $project_owner_personid = 1;
            $this->m_our_test_project_bundle = [];
            $this->m_our_test_project_bundle['created'] = $this->m_oProjectHelper->createTestProject($project_owner_personid);
            
            
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
        error_log("UNIT TEST starting tearDown " . get_class($this));
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
    
    function testStep1RelevantRecordsSetup()
    {
        error_log("UNIT TEST starting testStep1RelevantRecordsSetup " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $badthings_ar = [];
            
            $project_owner_personid = 1;
            $test_project_bundle = $this->m_our_test_project_bundle;
            $projectid = $test_project_bundle['created']['newid'];
            $root_goalid = $test_project_bundle['created']['root_goalid'];
            
            $projects2ignore = $projectid;
            $personid_ar = $this->getPeopleIDList();
            
            $this->m_our_test_project_bundle['personid_ar'] = $personid_ar;
            
            $insight['maps'] = array(
                'projectid'=>$projectid
                , 'projects2ignore'=>$projects2ignore
                , 'people'=>$personid_ar
            );

            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = 2;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = 1;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $end_shift_days);
            $remaining_effort_hours = 25;
            
            $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
            
            $this->m_our_test_project_bundle['MasterDailyDetailInstance'] = $oMasterDailyDetail;
            
            $embed_overages = 1;
            $oMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt, $embed_overages);
            
            //Create overage scenario and confirm the user is over allocated
            $total_workitems = 0;
            $last_dt = $end_dt;
            $person2wids_map = [];
            $all_created_workitems = [];
            $wid2daw_list = [];
            $wid2ddw_list = [];
            
            $winfo = [];
            $winfo['wid'] = $root_goalid;
            $winfo['root_of_projectid'] = $projectid;
            $winfo['owner_personid'] = $project_owner_personid;
            $winfo['owner_projectid'] = $projectid;
            $winfo['sdt'] = NULL;
            $winfo['edt'] = NULL;
            $winfo['reh'] = NULL;
            $wid2daw_list[$root_goalid] = [];
            $wid2ddw_list[$root_goalid] = [];
            $winfo['is_relevant_yn'] = 0;
            $all_created_workitems[$root_goalid] = $winfo;
            
            $create_foreach_person = round(max(1,12 / count($personid_ar)));
            
            $test_project_bundle['workitems_per_person'] = $create_foreach_person;
            
            $expected_total_reh_per_person = $create_foreach_person * $remaining_effort_hours;
            
            for($i=0;$i<$create_foreach_person;$i++)
            {
                $workitem_basetype = (($i % 2) == 0) ? 'G' : 'T';
                $myvalues = [];
                $myvalues['planned_start_dt'] = $start_dt;
                $myvalues['planned_end_dt'] = $end_dt;
                $myvalues['remaining_effort_hours'] = $remaining_effort_hours;
                foreach($personid_ar as $owner_personid)
                {
                    $result = $this->createPersonWorkitem($owner_personid, $projectid, $workitem_basetype, $myvalues, $root_goalid);
                    $wid = $result['workitemid'];
                    
                    $wid2daw_list[$root_goalid][$wid] = $wid;
                    
                    $wid2daw_list[$wid] = [];
                    $wid2ddw_list[$wid][$root_goalid] = $root_goalid;
                    
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
                    $winfo['owner_projectid'] = $projectid;
                    $winfo['sdt'] = $start_dt;
                    $winfo['edt'] = $end_dt;
                    $winfo['reh'] = $remaining_effort_hours;
                    $winfo['is_relevant_yn'] = 1;
                    $all_created_workitems[$wid] = $winfo;
                    $oMasterDailyDetail->setRelevantWorkitemRecord($wid, $winfo);
                    
                }
                $total_workitems++;
                if($end_dt > $last_dt)
                {
                    $last_dt = $end_dt;
                }
            }
            $this->m_our_test_project_bundle['all_created_workitems'] = $all_created_workitems;
           
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
                
                //Check that all the expected workitems exist
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
                
                //Check the utilization insight
                $padb = $oPAI->getAvailabilityDetailBundle();
                $pdd_insight = $this->m_oUtilizationHelper->getPersonDailyDetailBundleInsight($padb);
                $found_wids = $pdd_insight['wids'];
                $found_relevant_wids = $found_wids['relevant'];
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
                if($busy_bad_days < 1)
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Expected NON ZERO busy_bad_days for personid#$personid ($created_tx) in " . print_r($pdd_insight,TRUE));
                }
            }
            
            //Compute summary information
            $person2all_winfo_ar = [];
            foreach($personid_ar as $personid)
            {
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                $person2all_winfo_ar[$personid] = [];
                $person2all_winfo_ar[$personid]['all_winfo'] = [];
                $person2all_winfo_ar[$personid]['summary'] = [];
                $person2all_winfo_ar[$personid]['summary']['total_reh'] = 0;
                foreach($all_wids as $wid)
                {
                    $record = $oMasterDailyDetail->getRelevantWorkitemRecord($wid);
                    $owner_personid = $record['owner_personid'];
                    if($owner_personid !== $personid)
                    {
                        $badthings_ar[] = "Expected owner of wid#$wid as person#$personid but instead owned by person#$owner_personid";
                    }
                    $person2all_winfo_ar[$personid]['all_winfo'][$wid] = $record;
                    $person2all_winfo_ar[$personid]['summary']['total_reh'] += $record['reh'];
                }
            }
            
            //Check for expected totals
            foreach($personid_ar as $personid)
            {
                if($expected_total_reh_per_person != $person2all_winfo_ar[$personid]['summary']['total_reh'])
                {
                    $badthings_ar[] = "Expected total reh of $expected_total_reh_per_person but for person#$personid we have " . $person2all_winfo_ar[$personid]['summary']['total_reh'];
                }
            }

            if(count($badthings_ar)>0)
            {
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
            }
           
            //Compare MASTER derived winfo to PERSON derived winfo
            $this->m_oUtility->checkAllRelevantWinfo($oMasterDailyDetail);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testStep2AlterRelevantRecordsByMaster()
    {
        error_log("UNIT TEST starting testStep2AlterRelevantRecordsByMaster " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $test_project_bundle = $this->m_our_test_project_bundle;
            $projectid = $test_project_bundle['created']['newid'];
            $all_created_workitems = $test_project_bundle['all_created_workitems'];
            $oMasterDailyDetail = $test_project_bundle['MasterDailyDetailInstance'];
            
            $projects2ignore = $projectid;
            
            $insight['maps'] = array(
                'projectid'=>$projectid
                , 'projects2ignore'=>$projects2ignore
                , 'workitems'=>$all_created_workitems
            );
            
            //Alter the records using the MASTER object
            $person2newinfo = [];
            $new_reh_per_workitem = 100;
            foreach($all_created_workitems as $wid=>$winfo)
            {
                if($winfo['is_relevant_yn'])
                {
                    $personid = $winfo['owner_personid'];
                    if(!isset($person2newinfo[$personid]))
                    {
                        $person2newinfo[$personid]['all_winfo'] = [];
                        $person2newinfo[$personid]['total_reh'] = 0;
                    }

                    $existing_sdt = $winfo['sdt'];
                    if(empty($existing_sdt))
                    {
                        throw new \Exception("Did NOT expect empty sdt in winfo " . print_r($winfo,TRUE));
                    }
                    $existing_edt = $winfo['edt'];
                    if(empty($existing_edt))
                    {
                        throw new \Exception("Did NOT expect empty edt in winfo " . print_r($winfo,TRUE));
                    }

                    $new_sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($existing_sdt, 5);
                    $new_edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($existing_edt, 12);

                    $winfo['reh'] = $new_reh_per_workitem;
                    $winfo['sdt'] = $new_sdt;
                    $winfo['edt'] = $new_edt;

                    $oMasterDailyDetail->setRelevantWorkitemRecord($wid, $winfo);
                    $person2newinfo[$personid]['all_winfo'][$wid] = $winfo;
                    $person2newinfo[$personid]['total_reh'] += $new_reh_per_workitem;
                }
            }
            $insight['maps']['person2newinfo'] = $person2newinfo;
                
             
            $this->m_oUtility->checkExpectedWinfoValues($person2newinfo, $oMasterDailyDetail);
            
            $this->m_oUtility->checkAllRelevantWinfo($oMasterDailyDetail);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testStep3AlterRelevantRecordsByPerson()
    {
        error_log("UNIT TEST starting testStep3AlterRelevantRecordsByPerson " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            
            $test_project_bundle = $this->m_our_test_project_bundle;
            $projectid = $test_project_bundle['newid'];
            $all_created_workitems = $test_project_bundle['all_created_workitems'];
            $oMasterDailyDetail = $test_project_bundle['MasterDailyDetailInstance'];
            
            $projects2ignore = $projectid;
            
            $insight['maps'] = array(
                'projectid'=>$projectid
                , 'projects2ignore'=>$projects2ignore
                , 'workitems'=>$all_created_workitems
            );
            
            //Alter the records using the PERSON object
            $person2newinfo = [];
            $new_reh_per_workitem = 55;
            foreach($all_created_workitems as $wid=>$winfo)
            {
                if($winfo['is_relevant_yn'])
                {
                    $personid = $winfo['owner_personid'];
                    $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                    if(!isset($person2newinfo[$personid]))
                    {
                        $person2newinfo[$personid]['all_winfo'] = [];
                        $person2newinfo[$personid]['total_reh'] = 0;
                    }

                    $existing_sdt = $winfo['sdt'];
                    if(empty($existing_sdt))
                    {
                        throw new \Exception("Did NOT expect empty sdt in winfo " . print_r($winfo,TRUE));
                    }
                    $existing_edt = $winfo['edt'];
                    if(empty($existing_edt))
                    {
                        throw new \Exception("Did NOT expect empty edt in winfo " . print_r($winfo,TRUE));
                    }

                    $new_sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($existing_sdt, 5);
                    $new_edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($existing_edt, 12);

                    $winfo['reh'] = $new_reh_per_workitem;
                    $winfo['sdt'] = $new_sdt;
                    $winfo['edt'] = $new_edt;

                    $oPAI->setRelevantWorkitemRecord($wid, $winfo);
                    $person2newinfo[$personid]['all_winfo'][$wid] = $winfo;
                    $person2newinfo[$personid]['total_reh'] += $new_reh_per_workitem;
                }
            }
            
            $this->m_oUtility->checkExpectedWinfoValues($person2newinfo, $oMasterDailyDetail);
            
            $this->m_oUtility->checkAllRelevantWinfo($oMasterDailyDetail);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testInternalUnitTestHelpers()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            
            $test_project_bundle = $this->m_our_test_project_bundle;
            $projectid = $test_project_bundle['created']['newid'];
            $all_created_workitems = $test_project_bundle['all_created_workitems'];
            $oMasterDailyDetail = $test_project_bundle['MasterDailyDetailInstance'];
            
            $projects2ignore = $projectid;
            
            $insight['maps'] = array(
                'projectid'=>$projectid
                , 'projects2ignore'=>$projects2ignore
                , 'workitems'=>$all_created_workitems
            );
            
            //Alter the records using the PERSON object
            $person2newinfo = [];
            $new_reh_per_workitem = 55;
            foreach($all_created_workitems as $wid=>$winfo)
            {
                $personid = $winfo['owner_personid'];
                if(!isset($person2newinfo[$personid]))
                {
                    $person2newinfo[$personid]['all_winfo'] = [];
                    $person2newinfo[$personid]['total_reh'] = 0;
                }
                
                $winfo['reh'] = 1;
                $winfo['sdt'] = 2;
                $winfo['edt'] = 3;
                
                $person2newinfo[$personid]['all_winfo'][$wid] = $winfo;
                $person2newinfo[$personid]['total_reh'] += $new_reh_per_workitem;
            }
            
            $good_yn = 0;
            try
            {
                $this->m_oUtility->checkExpectedWinfoValues($person2newinfo, $oMasterDailyDetail);
                throw new \Exception("Internal checkExpectedWinfoValues utility failed to detect intential difference!");
            } catch (\Exception $ex) {
                $good_yn = 1;
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
