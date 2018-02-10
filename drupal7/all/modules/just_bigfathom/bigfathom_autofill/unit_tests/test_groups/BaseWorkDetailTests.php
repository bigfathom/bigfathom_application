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

namespace bigfathom_autofill;

/**
 * Collection of some work detail tests for the utilization module
 *
 * @author Frank Font
 */
abstract class BaseWorkDetailTests extends \bigfathom\AbstractActionTestGroup
{
    
    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oUAH = NULL;
    protected $m_oWriteHelper = NULL;
    
    protected $m_oProjectHelper = NULL;
    
    protected $m_all_active_projectids = NULL;
    
    protected $m_oUtilizationHelper = NULL;
    
    private $m_date_mode_keyword = NULL;
    private $m_forced_project_start_dt = NULL;
    
    protected $m_discard_test_projects_on_teardown = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_utilization','unit_tests/utilities/UtilizationHelper');
        $this->m_oUtilizationHelper = new \bigfathom_utilization\UtilizationHelper();
        if(empty($this->m_oUtilizationHelper))
        {
            throw new \Exception("Could not initialize the utilization helper object!");
        }
        $this->m_discard_test_projects_on_teardown = TRUE;
    }
    
    public function setMode($date_mode, $forced_project_start_dt=NULL)
    {
        if(empty($date_mode))
        {
            throw new \Exception("Empty mode!");
        }
        $this->m_date_mode_keyword = $date_mode;
        $this->m_forced_project_start_dt = $forced_project_start_dt;
    }
    
    protected function getModeText()
    {
        if(empty($this->m_forced_project_start_dt))
        {
            return "{$this->m_date_mode_keyword}";
        } else {
            return "{$this->m_date_mode_keyword} forced_project_start_dt={$this->m_forced_project_start_dt}";
        }
    }

    function getVersionNumber()
    {
        return '20170930.1';
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
            
            $loaded_dga = module_load_include('php','bigfathom_core','core/DirectedGraphAnalysis');
            if(!$loaded_dga)
            {
                throw new \Exception('Failed to load the bigfathom_core DirectedGraphAnalysis class');
            }
            
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
            $special_suffix = "_AUTOFILL_" . strtoupper($this->m_date_mode_keyword);
            $this->m_oProjectHelper = new \bigfathom_core\ProjectHelper($this->m_oContext, $this->m_oMapHelper, $this->m_oUAH, $this->m_oWriteHelper
                    , $special_suffix);
            
            $loaded_engine = module_load_include('php','bigfathom_autofill','core/Engine');
            if(!$loaded_engine)
            {
                throw new \Exception('Failed to load the Engine class');
            }
            
            $this->m_all_active_projectids = $this->m_oMapHelper->getProjectsIDs(TRUE);
            $this->m_all_active_personids = array_keys($this->m_oMapHelper->getPersonsByID());
            
            /*
            if(count($this->m_all_active_projectids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_projectids) . ' existing projects available for test.';
            }
            if(count($this->m_all_active_personids)<3)
            {
                $warning_ar[] = 'Only ' . count($this->m_all_active_personids) . ' existing people available for test';
            }
            */
            
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
        $warning_msg = NULL;
        $insight = [];
        try
        {
            if($this->m_discard_test_projects_on_teardown)
            {
                $this->m_oProjectHelper->deleteAllTestProjects();
                $insight = "Normal test projects deleted";
            } else {
                $template_name = $this->m_oProjectHelper->getProjectNameTemplate();
                $warning_msg = "Did NOT remove the $template_name test project.";
                $insight = "Test projects not deleted so you can debug";
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
            if(count($combined) >= 3)
            {
                //Thats enough
                break;
            }
        }
        return $combined;
    }
    
    private function createPersonWorkitem($personid, $projectid, $workitem_basetype='G', $myvalues=NULL, $dep_wid=NULL)
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
            $result = $this->m_oProjectHelper->addWorkitemsToOurTestProject($projectid, $workitem_basetype, $myvalues, $dep_wid);
            return $result;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function createProject($project_owner_personid)
    {
        if(empty($this->m_forced_project_start_dt))
        {
            $test_project_bundle = $this->m_oProjectHelper->createTestProject($project_owner_personid);
        } else {
            $test_project_bundle = $this->m_oProjectHelper->createTestProject($project_owner_personid,$this->m_forced_project_start_dt,NULL,1);
        }
        
        return $test_project_bundle;
    }
    
    function testFitFutureWorkOverage()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $insight = [];
        $warning_msg = NULL;
        
        try
        {
            $project_owner_personid = 1;
            $test_project_bundle = $this->createProject($project_owner_personid);
            $projectid = $test_project_bundle['newid'];
            $root_goalid = $test_project_bundle['root_goalid'];
            
            $projects2ignore = $projectid;
            $personid_ar = $this->getPeopleIDList();
            
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d", $now_timestamp);
            $start_shift_days = 2;
            $start_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($today_dt, $start_shift_days);
            $end_shift_days = 1;
            $end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($start_dt, $end_shift_days);
            $remaining_effort_hours = 25;
            
            $oSetupMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($projects2ignore);
            $embed_overages = 1;
            $oSetupMasterDailyDetail->initialize($personid_ar, $start_dt, $end_dt, $embed_overages);
            
            $insight['maps'] = array(
                'projectid'=>$projectid
                , 'projects2ignore'=>$projects2ignore
                , 'people'=>$personid_ar
                , 'runmode'=>$this->getModeText()
            );
            $insight['initial_daterange'] = array('sdt'=>$start_dt, 'edt'=>$end_dt);
                    
            //Create overage scenario and confirm the user is over allocated
            $total_workitems = 0;
            $last_dt = $end_dt;
            $first_dt = $start_dt;
            $person2wids_map = [];
            $all_created_workitems = [];
            $wid2daw_list = [];
            $wid2ddw_list = [];
            
            $winfo = [];
            $winfo['wid'] = $root_goalid;
            $winfo['root_of_projectid'] = $projectid;
            $winfo['owner_personid'] = $project_owner_personid;
            $winfo['owner_projectid'] = $projectid;
            if(empty($this->m_forced_project_start_dt))
            {
                $winfo['sdt'] = NULL;
            } else {
                $winfo['sdt'] = $this->m_forced_project_start_dt;
                $winfo['sdt_locked_yn'] = 1;
            }
            $winfo['edt'] = NULL;
            $winfo['reh'] = NULL;
            $wid2daw_list[$root_goalid] = [];
            $wid2ddw_list[$root_goalid] = [];
            $all_created_workitems[$root_goalid] = $winfo;
            
            $create_foreach_person = round(max(1,10 / count($personid_ar)));
            for($i=0;$i<$create_foreach_person;$i++)
            {
                $workitem_basetype = (($i % 2) == 0) ? 'G' : 'T';
                $myvalues = [];
                if($this->m_date_mode_keyword == 'unlocked_dates')
                {
                    $myvalues['planned_start_dt'] = $start_dt;
                    $myvalues['planned_end_dt'] = $end_dt;
                } else if($this->m_date_mode_keyword == 'locked_dates') {
                    $myvalues['planned_start_dt'] = $start_dt;
                    $myvalues['planned_end_dt'] = $end_dt;
                    $myvalues['planned_start_dt_locked_yn'] = 1;
                    $myvalues['planned_end_dt_locked_yn'] = 1;
                } else if($this->m_date_mode_keyword == 'actual_dates') {
                    $myvalues['actual_start_dt'] = $start_dt;
                    $myvalues['actual_end_dt'] = $end_dt;
                } else {
                    throw new \Exception("No support for mode={$this->m_date_mode_keyword}");
                }
                
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
                    if($this->m_date_mode_keyword == 'locked_dates' || $this->m_date_mode_keyword == 'actual_dates')
                    {
                        $winfo['sdt_locked_yn'] = 1;
                        $winfo['edt_locked_yn'] = 1;
                    }
                    $all_created_workitems[$wid] = $winfo;
                    $oSetupMasterDailyDetail->setRelevantWorkitemRecord($wid, $winfo);
                }
                $total_workitems++;
                if($end_dt > $last_dt)
                {
                    $last_dt = $end_dt;
                }
            }
            
            //Now examine the utilization details
            $personid_map = [];
            foreach($personid_ar as $personid)
            {
                $personid_map[$personid] = $personid;
            }
            $check_personid_ar = $oSetupMasterDailyDetail->getAllPersonIDs();
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
                $oPAI = $oSetupMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
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
                
                //Check the utilization insight before we run the autofill
                $padb = $oPAI->getAvailabilityDetailBundle();
                $before_autofill_pdd_insight = $this->m_oUtilizationHelper->getPersonDailyDetailBundleInsight($padb);
                $found_wids = $before_autofill_pdd_insight['wids'];
                $found_relevant_wids = $found_wids['relevant'];
                $overage_relevant_wids_map = $padb['overages']['relevant'];
                foreach($all_wids as $onewid)
                {
                    if(!isset($found_relevant_wids[$onewid]) && !($overage_relevant_wids_map[$onewid]))
                    {
                        $overages_wids = array_keys($overage_relevant_wids_map);
                        $found_tx = "found=" . print_r(array_keys($found_relevant_wids),TRUE);
                        $expected_tx = "expected=" . print_r($all_wids,TRUE);
                        $pdd_insight_tx = print_r($before_autofill_pdd_insight,TRUE);
                        throw new \Exception("Did not find wid#$onewid in daily detail! ($found_tx vs $expected_tx)"
                                . "<br>analysis=$pdd_insight_tx person2wids_map[$personid]=" . print_r($person2wids_map[$personid],TRUE)
                                . " <br>overages_wids=" . print_r($overages_wids,TRUE));
                    }
                }
                $busy_bad_days = $before_autofill_pdd_insight['busy_bad_days'];
                if($busy_bad_days < 1)
                {
                    $created_tx = "created=" . print_r(array_keys($created_wids_map),TRUE);
                    $found_tx = "found=" . print_r($all_wids,TRUE);
                    throw new \Exception("Expected NON ZERO busy_bad_days for personid#$personid ($created_tx) in " . print_r($before_autofill_pdd_insight,TRUE));
                }
            }

            $person2all_before_winfo_ar = [];
            foreach($personid_ar as $personid)
            {
                $oPAI = $oSetupMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                $person2all_before_winfo_ar[$personid] = [];
                foreach($all_wids as $wid)
                {
                    $person2all_before_winfo_ar[$personid][$wid] = $oPAI->getRelevantWorkitemRecord($wid);
                }
            }
            
            //Now, auto fit so that overages disappear
            $autofill_flags_ar = [];
            $autofill_flags_ar['flag_scope'] = 'ALL_WORK';
            $autofill_flags_ar['flag_replace_blank_dates'] = 1;
            $autofill_flags_ar['flag_replace_blank_effort'] = 1;
            $autofill_flags_ar['flag_replace_unlocked_dates'] = 1;
            $autofill_flags_ar['flag_replace_unlocked_effort'] = 1;
            $autofill_flags_ar['flag_availability_type'] = 'BY_OWNER';
            $oEngine = new \bigfathom_autofill\Engine($projectid, $autofill_flags_ar);

            //Set the relationship fields for our network map to pickup
            $all_created_workitems[$root_goalid]['maps']['daw'] = $wid2daw_list[$root_goalid];
            $all_created_workitems[$root_goalid]['maps']['ddw'] = $wid2ddw_list[$root_goalid];
            foreach($all_created_workitems as $wid=>$winfo)
            {
                $all_created_workitems[$wid]['maps']['daw'] = $wid2daw_list[$wid];
                $all_created_workitems[$wid]['maps']['ddw'] = $wid2ddw_list[$wid];
            }

            $sprint_maps = [];
            $oDGA = new \bigfathom\DirectedGraphAnalysis();
            $networkbundle = $oDGA->getNetworkMapBundle($projectid, $all_created_workitems, $sprint_maps);
            
            //throw new \Exception("LOOK networkbundle=" . print_r($networkbundle,TRUE));
            
            $oEngine->initialize($networkbundle, $all_created_workitems);
            $pdb = $oEngine->getDataBundle();
            if(empty($pdb))
            {
                throw new \Exception("Empty data bundle from engine on project#$projectid");
            }
            $rootwid = $pdb->getRootWorkitemID();
            if(empty($rootwid))
            {
                throw new \Exception("Missing root wid from engine on project#$projectid pdb=" . print_r($pdb,TRUE));
            }
            $initial_projinfo1 = $this->m_oMapHelper->getProjectRecord($projectid);
            $autofill_result = $oEngine->getSolution(1, $initial_projinfo1, $all_created_workitems, $networkbundle);
            $after_autofill_projinfo1 = $this->m_oMapHelper->getProjectRecord($projectid);
            $after_autofill_project_sdt = !empty($after_autofill_projinfo1['actual_start_dt']) ? $after_autofill_projinfo1['actual_start_dt'] : $after_autofill_projinfo1['planned_start_dt'];
            $after_autofill_project_edt = !empty($after_autofill_projinfo1['actual_end_dt']) ? $after_autofill_projinfo1['actual_end_dt'] : $after_autofill_projinfo1['planned_end_dt'];
            
            if(empty($after_autofill_project_sdt) || empty($after_autofill_project_edt))
            {
                throw new \Exception("Expected project with dates [start,end] but instead have [$after_autofill_project_sdt,$after_autofill_project_edt]"
                        . "<BR<BR>after=". print_r($after_autofill_projinfo1,TRUE) 
                        . "<BR><BR>before=". print_r($initial_projinfo1,TRUE));
            }
            
            if($this->m_date_mode_keyword == 'unlocked_dates' && $after_autofill_project_sdt == $after_autofill_project_edt)
            {
                throw new \Exception("Expected project with more than 1 day but instead have [$after_autofill_project_sdt,$after_autofill_project_edt]"
                        . "<BR<BR>after=". print_r($after_autofill_projinfo1,TRUE) 
                        . "<BR><BR>before=". print_r($initial_projinfo1,TRUE));
            }
            
            if(!array_key_exists('aborted_yn',$autofill_result))
            {
                throw new \Exception("Missing aborted_yn flag in autofill_result=".print_r($autofill_result,TRUE));
            }
            if($autofill_result['aborted_yn'])
            {
                throw new \Exception("Auto fill was aborted!"
                        . " See result=".print_r($autofill_result,TRUE) 
                        . "<br>networkbundle=".print_r($networkbundle,TRUE));
            }
            $candidate_workitem_count = $autofill_result['num_candidates'];
            if($candidate_workitem_count < 1)
            {
                throw new \Exception("The autofill did NOT find any candidate workitems!"
                        . " See result=".print_r($autofill_result,TRUE) 
                        . "<br>networkbundle=".print_r($networkbundle,TRUE));
            }
//throw new \Exception("LOOK did we change stuff??? autofill_result=" . print_r($autofill_result,TRUE) . '<br>VS all_created_workitems=' . print_r($all_created_workitems,TRUE));            
            $updated_workitem_count = $autofill_result['num_updated'];
            if($updated_workitem_count < 1)
            {
                throw new \Exception("The autofill did NOT update any workitems!"
                        . " See result=".print_r($autofill_result,TRUE) 
                        . "<br>networkbundle=".print_r($networkbundle,TRUE));
            }
            
            $autofill_reported_updated_workitems = $autofill_result['updated_workitems'];
            $count_updated_records = count($autofill_reported_updated_workitems);
            if($count_updated_records !== $updated_workitem_count)
            {
                throw new \Exception("The autofill reported $updated_workitem_count updates but we count $count_updated_records workitems!"
                        . " See result=".print_r($autofill_result,TRUE) 
                        . "<br>networkbundle=".print_r($networkbundle,TRUE));
            }
            
            $oAutoFilledMasterDailyDetail = $oEngine->getMasterDailyDetailInstance();

            $autofilled_masterdd_info = array(
                    'sdt'=>$oAutoFilledMasterDailyDetail->getFirstDate()
                    ,'edt'=>$oAutoFilledMasterDailyDetail->getLastDate()
                    );
            
            $orignal_masterdd_info = array(
                    'sdt'=>$oSetupMasterDailyDetail->getFirstDate()
                    ,'edt'=>$oSetupMasterDailyDetail->getLastDate()
                    );

            $our_sdt = $after_autofill_project_sdt;//min($autofilled_masterdd_info['sdt'],$orignal_masterdd_info['sdt']);
            $our_edt = $after_autofill_project_edt;//max($autofilled_masterdd_info['edt'],$orignal_masterdd_info['edt']);
            $insight['checked_daterange'] = array('sdt'=>$our_sdt, 'edt'=>$our_edt);
            
            $expected_wids = [];
            foreach($all_created_workitems as $onewid=>$detail)
            {
                $reh = isset($detail['reh']) ? $detail['reh'] : $detail['remaining_effort_hours'];
                if($reh > 0)
                {
                    $expected_wids[] = $onewid;
                }
            }

            $not_expected_wids = [];    //TODO
            
            //First check the original autofill master
            $this->m_oUtilizationHelper->checkAvailabilityDetailBundleHours($oAutoFilledMasterDailyDetail, $expected_wids, $not_expected_wids);

            //Next check using newly created master
            $finalprojects2ignore = NULL;
            $oFinalMasterDailyDetail = new \bigfathom_utilization\MasterDailyDetail($finalprojects2ignore);
            $oFinalMasterDailyDetail->initialize($personid_ar, $our_sdt, $our_edt, $embed_overages);
            $mdd_iid = $oFinalMasterDailyDetail->getThisInstanceID();
            error_log("LOOK before our check mdd_iid=$mdd_iid debug m_relevant_work_busy_mapper");
            $this->m_oUtilizationHelper->checkAvailabilityDetailBundleHours($oFinalMasterDailyDetail, $expected_wids, $not_expected_wids);

            /*
            $this->checkOverages('AUTOFILL_DAILYDETAIL',$personid_ar, $person2wids_map
                    , $oAutoFilledMasterDailyDetail
                    , $person2all_before_winfo_ar, $before_autofill_pdd_insight, $autofill_reported_updated_workitems);
            */
            $this->checkOverages('ALL_STATIC_LOADED',$personid_ar, $person2wids_map
                    , $oFinalMasterDailyDetail
                    , $person2all_before_winfo_ar, $before_autofill_pdd_insight, $autofill_reported_updated_workitems);
            
            //PersonAvailabilityInsight::
            $insight['workitem_count'] = $total_workitems;
            $insight['date_range'] = "[$first_dt,$last_dt]";
            
        } catch (\Exception $ex) {
            $this->m_discard_test_projects_on_teardown = FALSE;
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('warning_msg'=>$warning_msg,'insight'=>$insight,'duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }

    private function checkOverages($context_label,$personid_ar, $person2wids_map, $oMasterDailyDetail, $person2all_before_winfo_ar, $before_autofill_pdd_insight, $autofill_result)
    {
        try
        {
            foreach($personid_ar as $personid)
            {
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $padb = $oPAI->getAvailabilityDetailBundle(NULL,NULL,1);
                $has_overages = count($padb['overages']['static']) + count($padb['overages']['relevant']) > 0;
                
                if($this->m_date_mode_keyword == 'unlocked_dates')
                {
                    $is_bad = $has_overages;
                    $expected_tx = "$context_label check expected ZERO overages";
                } else if($this->m_date_mode_keyword == 'locked_dates' || $this->m_date_mode_keyword == 'actual_dates') {
                    $is_bad = !$has_overages;
                    $expected_tx = "$context_label check expected AT LEAST ONE overage";
                } else {
                    throw new \Exception("No support for mode={$this->m_date_mode_keyword}");
                }
                
                if($is_bad)
                {
                    throw new \Exception("$expected_tx for personid#$personid current padb=" . print_r($padb,TRUE)
                            . ";<br>NOTE autofill_result=".print_r($autofill_result,TRUE));
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
