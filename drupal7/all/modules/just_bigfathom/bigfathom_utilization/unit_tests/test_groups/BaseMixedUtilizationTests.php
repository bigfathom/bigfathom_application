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
abstract class BaseMixedUtilizationTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;
    
    private $m_oUtilizationHelper = NULL;
    
    private $m_oUtility = NULL;
    private $m_oProjectHelper = NULL;
    
    private $m_all_active_projectids = NULL;
    
    private $m_our_test_project1_bundle = NULL;
    private $m_our_test_project2_bundle = NULL;
    
    private $m_mode_number = NULL;
    private $m_setup_start_ts = NULL;
    private $m_save_newwork = NULL;
    
    public function setMode($mode, $save_newwork=FALSE)
    {
        if(empty($mode))
        {
            throw new \Exception("Empty mode!");
        }
        $this->m_mode_number = $mode;
        $this->m_save_newwork = $save_newwork;
    }
    
    protected function getModeText()
    {
        return "mode#{$this->m_mode_number} save:" . ($this->m_save_newwork ? 'Yes' : 'No');
    }

    function getVersionNumber()
    {
        return '20170927.4';
    }
    
    function runAllTests()
    {
        $all_methods = $this->getAllTestMethods();
        return $this->shortcutRunAllTests($all_methods);
    }

    function setUp()
    {
        $mode_tx = $this->getModeText();
        error_log("UNIT TEST ($mode_tx) starting setUp " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_ar = [];
        $this->m_setup_start_ts = $start_ts;
        
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
            
            //Create projects
            $project_owner_personid = 1;
            $this->createOurTestProjects($project_owner_personid);
            
            $insight['maps'] = array(
                'project1'=>$this->m_our_test_project1_bundle
                , 'project2'=>$this->m_our_test_project2_bundle
            );
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
            error_log("UNIT TEST ($mode_tx) ERROR setUp MSG=" . $error_msg);
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        $warning_msg = implode(' and ', $warning_ar);
        error_log("UNIT TEST ($mode_tx) completed setUp " . get_class($this));
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    private function createOurTestProjects($project_owner_personid, $project_length=55, $reference_dt=NULL)
    {
        try
        {
            if(!empty($reference_dt))
            {
                $p1sdt = $reference_dt;
            } else {
                $p1sdt = $this->m_oUtilizationHelper->getDateAfterAllExistingWork();
            }
            
            $p1edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1sdt, $project_length);
            $this->m_our_test_project1_bundle = [];
            $this->m_our_test_project1_bundle['created'] = $this->m_oProjectHelper->createTestProject($project_owner_personid, $p1sdt, $p1edt);
            $this->m_our_test_project1_bundle['created_daterange'] = array('sdt'=>$p1sdt, 'edt'=>$p1edt);
            
            $p2sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1edt, 11);
            $p2edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p2sdt, $project_length);
            $this->m_our_test_project2_bundle = [];
            $this->m_our_test_project2_bundle['created'] = $this->m_oProjectHelper->createTestProject($project_owner_personid, $p2sdt, $p2edt);
            $this->m_our_test_project2_bundle['created_daterange'] = array('sdt'=>$p2sdt, 'edt'=>$p2edt);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function tearDown()
    {
        error_log("UNIT TEST (mode={$this->m_mode_number}) starting tearDown " . get_class($this));
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
        error_log("UNIT TEST (mode={$this->m_mode_number}) completed tearDown " . get_class($this));
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
    
    private function getPeopleIDList($enough=3)
    {
        $combined = [];
        $combined[1] = 1;
        foreach($this->m_all_active_personids as $personid)
        {
            $combined[$personid] = $personid;
            if(count($combined) >= $enough)
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
    
    private function createOurProjectWorkitems($owner_personid, $projectid, $dep_wid=NULL, $workitem_basetype_ar=NULL, $quantity=10, $reh_ar=NULL, $dates_ar=NULL)
    {
        try
        {
            $created_workitems = [];
            $myvalues = [];
            if(empty($workitem_basetype_ar))
            {
                $workitem_basetype_ar = array('G','T');
            }
            $wbt_modbase = count($workitem_basetype_ar);
            if(empty($reh_ar))
            {
                $reh_modbase = NULL;
            } else {
                $reh_modbase = count($reh_ar);
            }
            if(empty($dates_ar))
            {
                $dates_modbase = NULL;
            } else {
                $dates_modbase = count($dates_ar);
            }
            for($i=0; $i<$quantity; $i++)
            {
                $wbi = $i % $wbt_modbase;
                $workitem_basetype = $workitem_basetype_ar[$wbi];
                if(!empty($reh_ar))
                {
                    $rehi = $i % $reh_modbase;
                    $myvalues['remaining_effort_hours'] = $reh_ar[$rehi];
                }
                if(!empty($dates_ar))
                {
                    $rehi = $i % $dates_modbase;
                    $myvalues['planned_start_dt'] = $dates_ar[$rehi]['sdt'];
                    $myvalues['planned_end_dt'] = $dates_ar[$rehi]['edt'];
                }
                $result = $this->createPersonWorkitem($owner_personid, $projectid, $workitem_basetype, $myvalues, $dep_wid);
                $wid = $result['workitemid'];
                if(empty($wid))
                {
                    throw new \Exception("Did NOT get workitemid from result=" 
                            . print_r($result,TRUE) . " on createPersonWorkitem($owner_personid, $projectid, $workitem_basetype, *myvalues*, $dep_wid)");
                }
                $created_workitems[$wid] = $myvalues;
            }
            if(empty($created_workitems))
            {
                throw new \Exception("Did NOT create an workitems!");
            }
            return $created_workitems;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function populateOneTestProject($project_bundle, $workitem_count=10)
    {
        try
        {
            $project1_id = $project_bundle['created']['newid'];
            $project1_root_goalid = $project_bundle['created']['root_goalid'];
            $project1_daterange = $project_bundle['created_daterange'];

            $quantity = $workitem_count;
            $workitem_basetype_ar=NULL;
            $owner_personid = 1;
            $project_bundle['owner_personid_map'] = array($owner_personid=>$owner_personid);
            $reh_ar = [];
            $dates_ar = [];
            $min_dt = $project1_daterange['sdt'];
            $max_dt = $project1_daterange['edt'];
            $projdaycount = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($min_dt, $max_dt)-1;
            if($projdaycount < 1)
            {
                $projdaycount = 1;
            }
            $days_per_workitem = floor($projdaycount/$quantity);
            $sdt = $min_dt;
            $edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($sdt, $days_per_workitem);
            for($i=0;$i<$quantity;$i++)
            {
                $reh_ar[] = (1+$i*2);
                $dates_ar[] = array('sdt'=>$sdt, 'edt'=>$edt);
                $sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($edt, 1);
                $edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($sdt, $days_per_workitem);
            }
            $created_workitems = $this->createOurProjectWorkitems($owner_personid, $project1_id
                    , $project1_root_goalid, $workitem_basetype_ar
                    , $quantity, $reh_ar, $dates_ar);
            
            //Add the root workitem
            $created_workitems[$project1_root_goalid] = array(
                'remaining_effort_hours'=>0
                ,'planned_start_dt'=>$min_dt
                ,'planned_end_dt'=>$max_dt
            );
            
            $project_bundle['created_workitems'] = $created_workitems;
            
            return $project_bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testProjectContentSetupForOurTests()
    {
        error_log("UNIT TEST (mode={$this->m_mode_number}) starting testProjectContentSetupForOurTests " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {

            $this->m_our_test_project1_bundle = $this->populateOneTestProject($this->m_our_test_project1_bundle);
            $this->m_our_test_project2_bundle = $this->populateOneTestProject($this->m_our_test_project2_bundle);
            
            $insight['maps'] = array(
                'project1'=>$this->m_our_test_project1_bundle
                , 'project2'=>$this->m_our_test_project2_bundle
            );
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    private function checkExpectedVsLoaded($raw_projectid2bundle, $projects2ignorestatic, $start_dt, $end_dt, $new_work_winfo_ar=NULL, $scenario_name=NULL)
    {
        try
        {
            $badthings_ar = [];
            
            if(empty($start_dt) || empty($end_dt))
            {
                throw new \Exception("Missing required date on call to checkExpectedVsLoaded [$start_dt, $end_dt]");
            }
            if(empty($new_work_winfo_ar))
            {
                $new_work_winfo_ar = [];
            }

            $lookup_ignorestatic_projectid = [];
            foreach($projects2ignorestatic as $projectid)
            {
                $lookup_ignorestatic_projectid[$projectid] = $projectid;
            }
            
            if(count($lookup_ignorestatic_projectid) < 1)
            {
                throw new \Exception("There are no declared projectids for the static ignore!");
            }
            
            $process_projectid2bundle = [];
            $ignored_projectid2bundle = [];
            $owner_personid_map = [];
            foreach($raw_projectid2bundle as $projectid=>$project_bundle)
            {
                if(!isset($lookup_ignorestatic_projectid[$projectid]))
                {
                    $process_projectid2bundle[$projectid] = $project_bundle;
                } else {
                    $ignored_projectid2bundle[$projectid] = $project_bundle;
                }
                if(!array_key_exists('owner_personid_map', $project_bundle))
                {
                    throw new \Exception("Missing key owner_personid_map in ".print_r($project_bundle,TRUE));
                }
                foreach($project_bundle['owner_personid_map'] as $personid)
                {
                    $owner_personid_map[$personid] = $personid;
                }
            }
            
            if(count($owner_personid_map) < 1)
            {
                throw new \Exception("Expected at least one person in owner map!");
            }
            
            $embed_overages = 1;
            $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignorestatic);
            $oMasterDailyDetail->initialize($owner_personid_map, $start_dt, $end_dt, $embed_overages);
            
            //Now load all the new work
            $temp_wid = -1000;
            $expected_temp_wid_map = [];
            $original_total_newwork_map = [];
            foreach($new_work_winfo_ar as $winfo)
            {
                $temp_wid--;
                $owner_projectid = $winfo['owner_projectid'];
                if(!isset($lookup_ignorestatic_projectid[$owner_projectid]))
                {
                    throw new \Exception("Cannot create relevant wid#$temp_wid because projectid#$owner_projectid not on ignored list " . print_r($lookup_ignorestatic_projectid,TRUE));
                }
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $expected_temp_wid_map[$temp_wid] = $temp_wid;
                $original_total_newwork_map[$temp_wid] = $winfo;
            }
            $count_expected_temp_wid_map = count($expected_temp_wid_map);
                    
            $loaded_relevant_wids = $oMasterDailyDetail->getRelevantWorkitemRecordIDs();
            $count_loaded_relevant_wids = count($loaded_relevant_wids);
            if($count_expected_temp_wid_map != $count_loaded_relevant_wids)
            {
                throw new \Exception("Expected wids are " . print_r($expected_temp_wid_map,TRUE) . "<br> but we loaded " . print_r($loaded_relevant_wids,TRUE) );
            }
            foreach($loaded_relevant_wids as $wid)
            {
                if(!isset($expected_temp_wid_map[$wid]))
                {
                    throw new \Exception("Did NOT expect wid#$wid; Expected wids are " . print_r($expected_temp_wid_map,TRUE) . "<br> but we loaded " . print_r($loaded_relevant_wids,TRUE) );
                }
            }
            
            $loaded_staticworkbundle = $oMasterDailyDetail->getAllLoadedStaticWorkitemsByPersonID();

            $loaded_static_records = [];
            foreach($loaded_staticworkbundle as $personid=>$records_ar)
            {
                foreach($records_ar as $record)
                {
                    $wid = $record['wid'];
                    if(!empty($loaded_static_records[$wid]))
                    {
                        $badthings_ar[] = "Found wid#$wid more than once! FIRST=".print_r($loaded_static_records[$wid],TRUE)." SECOND=".print_r($loaded_static_records[$wid],TRUE);
                    }
                    $owner_projectid = $record['owner_projectid'];
                    if(empty($owner_projectid))
                    {
                        throw new \Exception("Missing owner_projectid in winfo of loaded wid#$wid! winfo=" . print_r($winfo,TRUE));
                    }
                    if(isset($lookup_ignorestatic_projectid[$owner_projectid]))
                    {
                        throw new \Exception("Did NOT expect to static load wid#$wid because it is owned by an ignored project#$owner_projectid! ignorestatic_projectids=".print_r($lookup_ignorestatic_projectid,TRUE));
                    }
                    
                    //Ignore anything outside are range of concern
                    $sdt = $record['sdt'];
                    $edt = $record['edt'];
                    if($start_dt <= $edt && $end_dt >= $sdt)
                    {
                        $loaded_static_records[$wid] = $record;
                    }
                }
            }   
            
            $expected_static_records = [];
            
            foreach($process_projectid2bundle as $projectid=>$project_bundle)
            {
                $created_workitems = $project_bundle['created_workitems'];
                foreach($created_workitems as $wid=>$detail)
                {
                    $sdt = $detail['planned_start_dt'];
                    $edt = $detail['planned_end_dt'];
                    if($start_dt <= $edt && $end_dt >= $sdt)
                    {
                        $reh = $detail['remaining_effort_hours'];
                        if(empty($loaded_static_records[$wid]))
                        {
                            $badthings_ar[] = "Missing expected wid#$wid detail=".print_r($detail[$wid],TRUE);
                        } else {
                            if($reh != $loaded_static_records[$wid]['reh'])
                            {
                                $badthings_ar[] = "Expected wid#$wid reh=$reh but loaded reh=".$loaded_static_records[$wid]['reh'];
                            }
                            if($sdt != $loaded_static_records[$wid]['sdt'])
                            {
                                $badthings_ar[] = "Expected wid#$wid sdt=$sdt but loaded sdt=".$loaded_static_records[$wid]['sdt'];
                            }
                            if($edt != $loaded_static_records[$wid]['edt'])
                            {
                                $badthings_ar[] = "Expected wid#$wid edt=$sdt but loaded edt=".$loaded_static_records[$wid]['edt'];
                            }
                        }
                        $expected_static_records[$wid] = $detail;
                    }
                }
            }
            $count_expected_records = count($expected_static_records);
            $count_loaded_records = count($loaded_static_records);
            if($count_expected_records != $count_loaded_records)
            {
                $expected_wids = array_keys($expected_static_records);
                $expected_wids_tx = implode(",", $expected_wids);
                $loaded_wids = array_keys($loaded_static_records);
                $loaded_wids_tx = implode(",", $loaded_wids);
                $badthings_ar[] = "The count_expected_records did not match the count_loaded_records in range [$start_dt, $end_dt]! ($count_expected_records vs $count_loaded_records)"
                        . " WID DETAIL ($expected_wids_tx) vs ($loaded_wids_tx)"
                        . "<br>ignorestatic_projectids=".print_r($lookup_ignorestatic_projectid,TRUE);
            }
            //Now change the REH and see if we get expected result
            $revised_newwork_map = [];
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                $winfo['reh'] += 5;
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $revised_newwork_map[$temp_wid] = $winfo;
            }
            
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                if($winfo['reh'] != $revised_newwork_map[$temp_wid]['reh'])
                {
                    throw new \Exception("Changed reh for wid#$temp_wid but did not come back in later query! NEWQUERY=" . print_r($winfo,TRUE));
                }
            }
            
/*
 * 
 * throw new \Exception("LOOK revised_newwork_map=".print_r($revised_newwork_map,TRUE) 
        . "<br> VS winfo=".print_r($winfo,TRUE) . "<br> VS original=".print_r($original_total_newwork_map,TRUE));            

 * 
            //Now change the DATES and see if we get expected results
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                $winfo['sdt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($winfo['sdt'], -22);
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $revised_newwork_map[$temp_wid] = $winfo;
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                if($winfo['sdt'] != $revised_newwork_map[$temp_wid]['sdt'])
                {
                    throw new \Exception("Changed sdt for wid#$temp_wid but did not come back in later query! NEWQUERY=" . print_r($winfo,TRUE));
                }
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                $winfo['edt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($winfo['edt'], 100);
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $revised_newwork_map[$temp_wid] = $winfo;
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                if($winfo['edt'] != $revised_newwork_map[$temp_wid]['edt'])
                {
                    throw new \Exception("Changed edt for wid#$temp_wid but did not come back in later query! NEWQUERY=" . print_r($winfo,TRUE));
                }
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                $winfo['edt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($winfo['edt'], 100);
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $revised_newwork_map[$temp_wid] = $winfo;
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                if($winfo['edt'] != $revised_newwork_map[$temp_wid]['edt'])
                {
                    throw new \Exception("Changed edt for wid#$temp_wid but did not come back in later query! NEWQUERY=" . print_r($winfo,TRUE));
                }
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                $winfo['sdt'] = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($winfo['sdt'], 100);
                $oMasterDailyDetail->setRelevantWorkitemRecord($temp_wid, $winfo);
                $revised_newwork_map[$temp_wid] = $winfo;
            }
            foreach($expected_temp_wid_map as $temp_wid)
            {
                $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($temp_wid);
                if($winfo['sdt'] != $revised_newwork_map[$temp_wid]['sdt'])
                {
                    throw new \Exception("Changed sdt for wid#$temp_wid but did not come back in later query! NEWQUERY=" . print_r($winfo,TRUE));
                }
            }
*/            
            if(count($badthings_ar)>0)
            {
                $badthings_ar[] = "PROCESSING_BUNDLE=".print_r($process_projectid2bundle,TRUE);
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
            }
            
            //throw new \Exception("LOOK AT LEAST ONE SUCCESS!!!!");
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testConfirmWorkitemsWithNewWork()
    {
        $mode_tx = $this->getModeText();
        error_log("UNIT TEST ($mode_tx) starting testConfirmWorkitemsWithUnsavedNew " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $badthings_ar = [];

            /*
            $adder = 2;
            $future_saturday = date("Y-m-d", strtotime("+{$adder} saturday"));
            $future_sunday = date("Y-m-d", strtotime("+{$adder} sunday"));
            if($future_saturday > $future_sunday)
            {
                $adder++;
                $future_sunday = date("Y-m-d", strtotime("+{$adder} sunday"));
            }
            $future_monday = date("Y-m-d", strtotime("+{$adder} monday"));
            if($future_sunday > $future_monday)
            {
                $adder++;
                $future_monday = date("Y-m-d", strtotime("+{$adder} monday"));
            }
            $future_wednesday = date("Y-m-d", strtotime("+{$adder} wednesday"));
            if($future_monday > $future_wednesday)
            {
                $adder++;
                $future_wednesday = date("Y-m-d", strtotime("+{$adder} wednesday"));
            }
            */
            
            $unworked_date = $this->m_oUtilizationHelper->getDateAfterAllExistingWork();
            $future_saturday = $this->m_oUtilizationHelper->getSaturdayAfterDate($unworked_date);
            
            $p1_daterange = $this->m_our_test_project1_bundle['created_daterange'];
            $p2_daterange = $this->m_our_test_project2_bundle['created_daterange'];
            
            $p1_workitems = $this->m_our_test_project1_bundle['created_workitems'];
            $p2_workitems = $this->m_our_test_project2_bundle['created_workitems'];
            
            $p1_wids = array_keys($p1_workitems);
            $p2_wids = array_keys($p2_workitems);
            
            sort($p1_wids);
            sort($p2_wids);
            
            $projectid2bundle = [];
            $p1id = $this->m_our_test_project1_bundle['created']['newid'];
            $p2id = $this->m_our_test_project2_bundle['created']['newid'];
            $p1root_goalid = $this->m_our_test_project1_bundle['created']['root_goalid'];
            $p2root_goalid = $this->m_our_test_project2_bundle['created']['root_goalid'];
            $projectid2bundle[$p1id] = $this->m_our_test_project1_bundle;
            $projectid2bundle[$p2id] = $this->m_our_test_project2_bundle;
            
            $insight['mode_info'] = array(
                'mode'=>$this->m_mode_number
            );
            $insight['maps'] = [];
            
            //Check we load all interesting dates
            $date_combos = [];
            
            //Interesting days of the week
            $date_combos[] = array('sdt'=>$future_saturday,'edt'=>$future_saturday);
            
            //Interesting project days
            $date_combos[] = array('sdt'=>$p1_daterange['sdt'],'edt'=>$p1_daterange['edt']);
            $date_combos[] = array('sdt'=>$p2_daterange['sdt'],'edt'=>$p2_daterange['edt']);
            $date_combos[] = array('sdt'=>$p1_daterange['sdt'],'edt'=>$p2_daterange['edt']);
            $date_combos[] = array('sdt'=>$p1_daterange['edt'],'edt'=>$p2_daterange['sdt']);

            $p1inside_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1_daterange['sdt'], 22);
            $p2inside_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p2_daterange['sdt'], 22);
            $date_combos[] = array('sdt'=>$p1inside_dt,'edt'=>$p2inside_dt);

            $before_sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1_daterange['sdt'], -14);
            $before_edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1_daterange['sdt'], -5);
            
            $after_sdt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p2_daterange['edt'], 5);
            $after_edt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p2_daterange['edt'], 14);
            
            $owner_personid = 1;
            $new_work_winfo_ar = [];
            $just_projectids = array_keys($projectid2bundle);
            foreach($just_projectids as $owner_projectid)
            {
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$future_saturday, 'edt'=>$future_saturday, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$future_saturday, 'edt'=>$future_sunday, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$future_sunday, 'edt'=>$future_monday, 'reh'=>12);
                
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$before_sdt, 'edt'=>$p1inside_dt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$p2inside_dt, 'edt'=>$p1inside_dt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$before_sdt, 'edt'=>$before_edt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$before_sdt, 'edt'=>$after_edt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$after_sdt, 'edt'=>$after_edt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$before_sdt, 'edt'=>$before_sdt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$after_edt, 'edt'=>$after_edt, 'reh'=>12);
                $new_work_winfo_ar[] = array('owner_personid'=>$owner_personid, 'owner_projectid'=>$owner_projectid, 'sdt'=>$p1inside_dt, 'edt'=>$p1inside_dt, 'reh'=>12);
            }
            
            $workitem_basetype='G';
            $created_newwork = [];
            if($this->m_save_newwork)
            {
                foreach($new_work_winfo_ar as $myvalues)
                {
                    //Save to the project now
                    $owner_personid = $myvalues['owner_personid'];
                    $owner_projectid = $myvalues['owner_projectid'];
                    unset($myvalues['owner_personid']);
                    unset($myvalues['owner_projectid']);
                    $result = $this->createPersonWorkitem($owner_personid, $owner_projectid, $workitem_basetype, $myvalues);
                    $wid = $result['workitemid'];                
                    $created_newwork[$wid] = $myvalues;
                }
            }
            
            $projects2ignorestatic_ar = [];
            if($this->m_mode_number==1)
            {
                $projects2ignorestatic_ar[] = array($p1id);
            } else if($this->m_mode_number==2) {
                $projects2ignorestatic_ar[] = array($p2id);
            } else if($this->m_mode_number==3) {
                $projects2ignorestatic_ar[] = array($p1id, $p2id);
            } else {
                throw new \Exception("There is not handler for $mode_tx");
            }
            
            $insight['mode_info']['projects2ignorestatic'] = $projects2ignorestatic_ar;
            $insight['maps']['date_combos'] = $date_combos;
            $insight['maps']['project1']=array('id'=>$p1id, 'range'=>$p1_daterange, 'root_goalid'=>$p1root_goalid, 'wids'=>$p1_wids);
            $insight['maps']['project2']=array('id'=>$p2id, 'range'=>$p2_daterange, 'root_goalid'=>$p2root_goalid, 'wids'=>$p2_wids);
            
            //Compose all our scenario conditions
            $scenarios = [];
            foreach($date_combos as $onecombo)
            {
                $start_dt = $onecombo['sdt'];
                $end_dt = $onecombo['edt'];
                
                foreach($projects2ignorestatic_ar as $projects2ignorestatic)
                {
                    $filtered_new_work_winfo_ar = $this->getNewWorkFiltedArray($new_work_winfo_ar, $projects2ignorestatic);
                    foreach($filtered_new_work_winfo_ar as $onenewwork)
                    {
                        $scenarios[] = array(
                            'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>$projects2ignorestatic
                            ,'new_work_winfo_ar'=>array($onenewwork)
                        );
                    }

                    //And create scenarios with all the new work
                    $scenarios[] = array(
                        'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>$projects2ignorestatic
                        ,'new_work_winfo_ar'=>$filtered_new_work_winfo_ar
                    );
                }
            }
            
            //Now execute all the scenario checks
            $executed_scenarios = 0;
            $count_scenarios = count($scenarios);
            $insight['mode_info']['count_scenarios'] = $count_scenarios;
            foreach($scenarios as $settings)
            {
                $sdt = $settings['sdt'];
                $edt = $settings['edt'];
                $projects2ignorestatic = $settings['projects2ignore'];
                
                $new_work_winfo_ar = $settings['new_work_winfo_ar'];
                $count_newwork = count($new_work_winfo_ar);
                
                if($count_newwork > 0)
                {
                    $executed_scenarios++;
                    if(is_array($projects2ignorestatic))
                    {
                        $insight['maps']['scenarios'][] = "$start_dt to $end_dt ignoring projects " . implode(" and ", $projects2ignorestatic) . "; new_work_winfo_ar size is $count_newwork";
                    } else {
                        $insight['maps']['scenarios'][] = "$start_dt to $end_dt NOT ignoring projects; new_work_winfo_ar size is $count_newwork";       
                    }
                    $scenario_name = "S#$executed_scenarios";
                    $start_scenario_ts = microtime(TRUE);
                    error_log("UNIT TEST ($mode_tx) starting scenario $scenario_name of total $count_scenarios planned scenarios at $start_scenario_ts");
                    $this->checkExpectedVsLoaded($projectid2bundle, $projects2ignorestatic, $sdt, $edt, $new_work_winfo_ar, $scenario_name);
                    $completed_scenario_ts = microtime(TRUE);
                    $total_time = $completed_scenario_ts - $start_scenario_ts;
                    error_log("UNIT TEST ($mode_tx) completed scenario $scenario_name of total $count_scenarios planned scenarios at $completed_scenario_ts (total time was $total_time microseconds)");
                    $unit_test_sofar_time = $completed_scenario_ts - $start_ts;
                    $group_sofar_time = $completed_scenario_ts - $this->m_setup_start_ts;
                    error_log("UNIT TEST ($mode_tx) test runtime so far is $unit_test_sofar_time microseconds and group runtime so far is $group_sofar_time microseconds");
                }
            }
            $insight['counts']['executed_scenarios'] = $executed_scenarios;
            error_log("UNIT TEST ($mode_tx) completed all scenarios!");
            
            if(count($badthings_ar)>0)
            {
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
            error_log("UNIT TEST ($mode_tx) ERROR testConfirmWorkitemsWithUnsavedNew " . get_class($this) . " MSG=$error_msg");
            error_log("UNIT TEST ($mode_tx) ERROR testConfirmWorkitemsWithUnsavedNew INSIGHT BUNDLE=" . print_r($insight,TRUE));
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        error_log("UNIT TEST completed (duration $duration_mus microseconds) testConfirmWorkitemsWithUnsavedNew " . get_class($this));
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus
                ,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    /**
     * Must be on the static ignore list to be included
     */
    private function getNewWorkFiltedArray($new_work_winfo_ar, $projects2ignorestatic)
    {
        $map = [];
        foreach($projects2ignorestatic as $projectid)
        {
            $map[$projectid] = $projectid;
        }
        
        $final = [];
        foreach($new_work_winfo_ar as $detail)
        {
            $owner_projectid = $detail['owner_projectid'];
            if(isset($map[$owner_projectid]))
            {
                $final[] = $detail;
            }
        }        
        
        return $final;
    }
    
}
