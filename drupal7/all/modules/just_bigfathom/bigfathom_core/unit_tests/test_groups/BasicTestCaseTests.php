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

namespace bigfathom_core;

/**
 * Collection of some basic test case tests for the core module
 *
 * @author Frank Font
 */
class BasicTestCaseTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    private $m_oProjectHelper = NULL;
    
    function getVersionNumber()
    {
        return '20171029.1';
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
        try
        {
            
            $loaded_dbnh = module_load_include('php','bigfathom_core','core/DatabaseNamesHelper');
            if(!$loaded_dbnh)
            {
                throw new \Exception('Failed to load the DatabaseNamesHelper class');
            }
            
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
            
            $loaded_helper_projects = module_load_include('php','bigfathom_core','unit_tests/utilities/ProjectHelper');
            if(!$loaded_helper_projects)
            {
                throw new \Exception('Failed to load the ProjectHelper class');
            }
            $this->m_oProjectHelper = new \bigfathom_core\ProjectHelper($this->m_oContext, $this->m_oMapHelper, $this->m_oUAH, $this->m_oWriteHelper);
            
            $this->createOurTestProject();
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
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

    private function createOurTestProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $wi_count = 10;
            $this->m_ourtestprojectbundle = $this->m_oProjectHelper->createTestProject(1);
            $workitem_basetype_ar = array('G','T');
            $projectid = $this->m_ourtestprojectbundle['newid'];
            for($i=0;$i<$wi_count;$i++)
            {
                $workitem_basetype = $workitem_basetype_ar[$i % 2];
                $this->m_oProjectHelper->addWorkitemsToOurTestProject($projectid, $workitem_basetype);
            }

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function createStepsContentBucket($textprefix="", $min_step_rows = 5)
    {
        $sequence_ar = [];
        $step_count = 0;
        for($i=$step_count; $i<$min_step_rows; $i++)
        {
            $sequence_ar[] = array('id'=>'','d'=>"$textprefix instruction@".$step_count,'e'=>"$textprefix expectation@".$step_count,'cd'=>'NONE');
        }
        $bucket = (object) ['sequence' => $sequence_ar];
        return $bucket;
    }
    
    function testCreateTestCases()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $field_values = [];
            
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $uc_count=5;
            for($tcnum=1;$tcnum<=$uc_count;$tcnum++)
            {
                $steps_content_bucket = $this->createStepsContentBucket("TCNUM$tcnum");
                $steps_encoded_tx = json_encode($steps_content_bucket);  
                
                $field_values = array(
                  'owner_projectid' => $projectid,
                  'status_cd' => 'B',
                  'testcase_nm' => "__UNITTEST_UC$tcnum",
                  'blurb_tx' => 'Created by automated test',
                  'precondition_tx' => 'Precondition test',
                  'postcondition_tx' => 'Postcondition test',
                  'steps_encoded_tx' => $steps_encoded_tx,
                  'references_tx' => 'Ref test',
                  'active_yn' => 1,
                  'owner_personid' => 1,
                  'importance' => 55,
                );
                $uc1bundle = $this->m_oWriteHelper->createTestcase($projectid, $field_values);
            }
            
        } catch (\Exception $ex) {
            
            $has_error = 1;
            if(count($field_values) > 0)
            {
                $error_msg = $ex->getMessage() . " MYVALUES=".print_r($field_values,TRUE);
            } else {
                $error_msg = $ex->getMessage();
            }
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    private function checkTestCaseBundleMapping($testcasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi)
    {
        try
        {
            if(!isset($testcasesbundle['analysis']))
            {
                $justkeys = array_keys($testcasesbundle);
                throw new \Exception("Missing analysis section in "
                            . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'entirebundle'=>$testcasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
            $analysis_map = $testcasesbundle['analysis'];//['pct_complete_by_ucid'];
            $mapped_map = $analysis_map['mapped'];//['pct_complete_by_ucid'];
            $unmapped_map = $analysis_map['unmapped'];//['pct_complete_by_ucid'];
            $mapped_direct_wi_map = $mapped_map['workitem']['direct']['workitems'];
            $mapped_indirect_wi_map = $mapped_map['workitem']['indirect']['workitems'];
            $unmapped_wi_map = $unmapped_map['workitem']['workitems'];
            
            if(count($mapped_direct_wi_map) != $expected_direct_wi)
            {
                throw new \Exception("Expected direct mapped count $expected_direct_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$testcasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            if(count($mapped_indirect_wi_map) != $expected_indirect_wi)
            {
                throw new \Exception("Expected indirect mapped count $expected_indirect_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_indirect_wi_map'=>$mapped_indirect_wi_map, 'entirebundle'=>$testcasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            if(count($unmapped_wi_map) != $expected_unmapped_wi)
            {
                throw new \Exception("Expected unmapped count $expected_unmapped_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$unmapped_wi_map'=>$unmapped_wi_map, 'entirebundle'=>$testcasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function checkTestCaseBundleCompletion($testcasesbundle, $expected_total_wi, $expected_total_done_untested_wi)
    {
        try
        {
            if(!isset($testcasesbundle['analysis']))
            {
                $justkeys = array_keys($testcasesbundle);
                throw new \Exception("Missing analysis section in "
                            . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'entirebundle'=>$testcasesbundle,"TESTCASEBUNDLE")
                                    )
                        );
            }
            
            $analysis_map = $testcasesbundle['analysis'];//['pct_complete_by_ucid'];
            $pct_complete_by_ucid_map = $analysis_map['pct_complete_by_tcid'];
            
            $found_total_wids_count = 0;
            $found_total_done_untested_count = 0;
            foreach($pct_complete_by_ucid_map as $tcid=>$detail)
            {
                $found_total_wids_count += $detail['total_wids'];
                $found_total_done_untested_count += $detail['total_done_untested'];
                if($detail['total_wids'] > 0)
                {
                    $computed_pct = round(100*$detail['total_done']/$detail['total_wids']);
                    if($computed_pct != round($detail['pct_done']))
                    {
                        $justkeys = array_keys($testcasesbundle);
                        throw new \Exception("Expected {$computed_pct}% pct done for tcid#$tcid in "
                                    . \bigfathom\DebugHelper::getNeatMarkup(
                                            array('justkeys'=>$justkeys
                                                ,'entirebundle'=>$testcasesbundle,"TESTCASEBUNDLE")
                                            )
                                );
                    }
                }
            }
            
            if($found_total_wids_count != $expected_total_wi)
            {
                throw new \Exception("Expected total wid count $expected_total_wi but instead found $found_total_wids_count in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$testcasesbundle,"TESTCASEBUNDLE")
                                    )
                        );
            }
            if($found_total_done_untested_count != $expected_total_done_untested_wi)
            {
                throw new \Exception("Expected total done count $expected_total_done_untested_wi but instead found $found_total_done_untested_count in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$testcasesbundle,"TESTCASEBUNDLE")
                                    )
                        );
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testQueryTestCases()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
         
            $test_missing_projectid = FALSE;
            try
            {
                $result = $this->m_oMapHelper->getTestCasesBundle(NULL);
            } catch (\Exception $ex) {
                $test_missing_projectid = TRUE;
            }
            if(!$test_missing_projectid)
            {
                throw new \Exception("Expected an exception for missing projectid!");
            }
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $testcasebundle = $this->m_oMapHelper->getTestCasesBundle($projectid);
            $testcases_map = $testcasesbundle['lookup']['testcases'];
            if(count($testcases_map) > 0)
            {
                throw new \Exeption("Expected zero test cases for project#$projectid but instead found " . print_r($testcases_map,TRUE));
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testMapWorkitemsToTestCases()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $testcasesbundle = $this->m_oMapHelper->getTestCasesBundle($projectid);
            $workitems_map = $testcasesbundle['lookup']['workitems'];
            $testcases_map = $testcasesbundle['lookup']['testcases'];
            
            $expected_direct_wi=0;
            $expected_indirect_wi=0;
            $expected_unmapped_wi=count($workitems_map);
            $this->checkTestCaseBundleMapping($testcasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi);
            
            $tcid_ar = array_keys($testcases_map);
            $tc_count = count($tcid_ar);
            if($tc_count < 1)
            {
                $justkeys = array_keys($testcasesbundle['lookup']);
                throw new \Exception("Expected at least one test case but instead have zero! See detail:" . print_r($justkeys['lookup'],TRUE));
            }
            $map_workitem2testcase_ar = [];
            $i=0;
            foreach($workitems_map as $wid=>$detail)
            {
                $i++;
                $tcid = $tcid_ar[$i % $tc_count];
                $map_testcase2wids[$tcid][$wid] = $wid;
            }
            foreach($tcid_ar as $tcid)
            {
                $myvalues = [];
                $myvalues['maps']['workitems'] = $map_testcase2wids[$tcid];
                $this->m_oWriteHelper->updateTestcase($tcid, $myvalues);
            }
            
            $after_testcasesbundle = $this->m_oMapHelper->getTestCasesBundle($projectid);
            $expected_direct_wi=count($workitems_map);
            $expected_indirect_wi=0;
            $expected_unmapped_wi=0;
            $this->checkTestCaseBundleMapping($after_testcasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testTestCaseCompletions()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
         
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $testcasesbundle = $this->m_oMapHelper->getTestCasesBundle($projectid);
            $workitems_map = $testcasesbundle['lookup']['workitems'];
            $testcases_map = $testcasesbundle['lookup']['testcases'];
            
            $expected_total_done_wi=0;
            $expected_total_wi=count($workitems_map);
            $this->checkTestCaseBundleCompletion($testcasesbundle, $expected_total_wi, $expected_total_done_wi);
            
            $ucid_ar = array_keys($testcases_map);
            $uc_count = count($ucid_ar);
            $map_workitem2testcase_ar = [];
            $i=0;
            foreach($workitems_map as $wid=>$detail)
            {
                $myvalues = [];
                $myvalues['status_cd'] = 'SC';
                $this->m_oWriteHelper->updateWorkitem($wid, $myvalues);
            }
            
            $after_testcasesbundle = $this->m_oMapHelper->getTestCasesBundle($projectid);
            $expected_total_done_wi=count($workitems_map);
            $expected_total_wi=count($workitems_map);
            $this->checkTestCaseBundleCompletion($after_testcasesbundle, $expected_total_wi, $expected_total_done_wi);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
}
