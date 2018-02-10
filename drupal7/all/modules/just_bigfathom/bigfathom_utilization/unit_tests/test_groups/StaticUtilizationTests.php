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
class StaticUtilizationTests extends \bigfathom\AbstractActionTestGroup
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
        $insight = [];
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

            //Create projects
            $this->createOurTestProjects(1);
            
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
        $warning_msg = implode(' and ', $warning_ar);
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    private function createOurTestProjects($project_owner_personid, $project_length=100, $reference_dt=NULL)
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
        error_log("UNIT TEST starting testProjectContentSetupForOurTests " . get_class($this));
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
    
    private function checkExpectedVsLoaded($raw_projectid2bundle,$projects2ignore,$start_dt, $end_dt)
    {
        try
        {
            $badthings_ar = [];
            
            if(empty($start_dt) || empty($end_dt))
            {
                throw new \Exception("Missing required date on call to checkExpectedVsLoaded [$start_dt, $end_dt]");
            }
            
            $process_projectid2bundle = [];
            $ignored_projectid2bundle = [];
            $owner_personid_map = [];
            foreach($raw_projectid2bundle as $projectid=>$project_bundle)
            {
                $offset = array_search($projectid,$projects2ignore);
                if($offset === FALSE)
                {
                    $process_projectid2bundle[$projectid] = $project_bundle;
                    if(!array_key_exists('owner_personid_map', $project_bundle))
                    {
                        throw new \Exception("Missing key owner_personid_map in ".print_r($project_bundle,TRUE));
                    }
                    foreach($project_bundle['owner_personid_map'] as $personid)
                    {
                        $owner_personid_map[$personid] = $personid;
                    }
                } else {
                    $ignored_projectid2bundle[$projectid] = $project_bundle;
                }
            }
            
            $embed_overages = 1;
            $oMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
            $oMasterDailyDetail->initialize($owner_personid_map, $start_dt, $end_dt, $embed_overages);
            $loaded_staticworkbundle = $oMasterDailyDetail->getAllLoadedStaticWorkitemsByPersonID();

            $loaded_records = [];
            foreach($loaded_staticworkbundle as $personid=>$records_ar)
            {
                foreach($records_ar as $record)
                {
                    $wid = $record['wid'];
                    if(!empty($loaded_records[$wid]))
                    {
                        $badthings_ar[] = "Found wid#$wid more than once! FIRST=".print_r($loaded_records[$wid],TRUE)." SECOND=".print_r($loaded_records[$wid],TRUE);
                    }
                    $loaded_records[$wid] = $record;
                }
            }            
            $expected_records = [];
            
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
                        if(empty($loaded_records[$wid]))
                        {
                            $badthings_ar[] = "Missing expected wid#$wid detail=".print_r($detail[$wid],TRUE);
                        } else {
                            if($reh != $loaded_records[$wid]['reh'])
                            {
                                $badthings_ar[] = "Expected wid#$wid reh=$reh but loaded reh=".$loaded_records[$wid]['reh'];
                            }
                            if($sdt != $loaded_records[$wid]['sdt'])
                            {
                                $badthings_ar[] = "Expected wid#$wid sdt=$sdt but loaded sdt=".$loaded_records[$wid]['sdt'];
                            }
                            if($edt != $loaded_records[$wid]['edt'])
                            {
                                $badthings_ar[] = "Expected wid#$wid edt=$sdt but loaded edt=".$loaded_records[$wid]['edt'];
                            }
                        }
                        $expected_records[$wid] = $detail;
                    }
                }
            }
            $count_expected_records = count($expected_records);
            $count_loaded_records = count($loaded_records);
            if($count_expected_records != $count_loaded_records)
            {
                $expected_wids = array_keys($expected_records);
                $expected_wids_tx = implode(",", $expected_wids);
                $loaded_wids = array_keys($loaded_records);
                $loaded_wids_tx = implode(",", $loaded_wids);
                $badthings_ar[] = "The count_expected_records did not match the count_loaded_records in range [$start_dt, $end_dt]! ($count_expected_records vs $count_loaded_records)"
                        . " WID DETAIL ($expected_wids_tx) vs ($loaded_wids_tx)";
            }
            
            if(count($badthings_ar)>0)
            {
                $badthings_ar[] = "PROCESSING_BUNDLE=".print_r($process_projectid2bundle,TRUE);
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testConfirmWorkitemsLoaded()
    {
        error_log("UNIT TEST starting testConfirmWorkitemsLoaded " . get_class($this));
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $badthings_ar = [];

            $p1_daterange = $this->m_our_test_project1_bundle['created_daterange'];
            $p2_daterange = $this->m_our_test_project2_bundle['created_daterange'];
            
            $projectid2bundle = [];
            $p1id = $this->m_our_test_project1_bundle['created']['newid'];
            $p2id = $this->m_our_test_project2_bundle['created']['newid'];
            $projectid2bundle[$p1id] = $this->m_our_test_project1_bundle;
            $projectid2bundle[$p2id] = $this->m_our_test_project2_bundle;
            
            $insight['maps'] = array(
                'project1'=>array('id'=>$p1id,'range'=>$p1_daterange)
                , 'project2'=>array('id'=>$p2id,'range'=>$p2_daterange)
            );
            
            //Check we load all of project1 properly
            $date_combos = [];
            
            $date_combos[] = array('sdt'=>$p1_daterange['sdt'],'edt'=>$p1_daterange['edt']);
            $date_combos[] = array('sdt'=>$p2_daterange['sdt'],'edt'=>$p2_daterange['edt']);
            $date_combos[] = array('sdt'=>$p1_daterange['sdt'],'edt'=>$p2_daterange['edt']);
            $date_combos[] = array('sdt'=>$p1_daterange['edt'],'edt'=>$p2_daterange['sdt']);

            $p1inside_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p1_daterange['sdt'], 22);
            $p2inside_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($p2_daterange['sdt'], 22);
            $date_combos[] = array('sdt'=>$p1inside_dt,'edt'=>$p2inside_dt);

            //Compose all our scenario conditions
            $scenarios = [];
            foreach($date_combos as $onecombo)
            {
                $start_dt = $onecombo['sdt'];
                $end_dt = $onecombo['edt'];
                $scenarios[] = array(
                    'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>NULL
                );
                $scenarios[] = array(
                    'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>array($p1id)
                );
                $scenarios[] = array(
                    'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>array($p2id)
                );
                $scenarios[] = array(
                    'sdt'=>$start_dt,'edt'=>$end_dt,'projects2ignore'=>array($p1id, $p2id)
                );
            }
            
            //Now execute all the scenario checks
            foreach($scenarios as $settings)
            {
                $sdt = $settings['sdt'];
                $edt = $settings['edt'];
                $projects2ignore = $settings['projects2ignore'];
                if(is_array($projects2ignore))
                {
                    $insight['maps']['scenarios'][] = "$start_dt to $end_dt ignoring projects " . implode(" and ", $projects2ignore);       
                } else {
                    $insight['maps']['scenarios'][] = "$start_dt to $end_dt NOT ignoring projects";       
                }
                $this->checkExpectedVsLoaded($projectid2bundle, $projects2ignore, $sdt, $edt);
            }
            
            $insight['counts']['completed_scenarios'] = count($insight['maps']['scenarios']);
            
            if(count($badthings_ar)>0)
            {
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
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
