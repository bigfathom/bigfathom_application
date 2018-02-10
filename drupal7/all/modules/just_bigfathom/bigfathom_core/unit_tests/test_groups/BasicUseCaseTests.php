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
 * Collection of some basic use case tests for the core module
 *
 * @author Frank Font
 */
class BasicUseCaseTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    private $m_oProjectHelper = NULL;
    
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
    
    
    function testCreateUseCases()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $uc_count=5;
            for($ucnum=1;$ucnum<=$uc_count;$ucnum++)
            {
                $ucvalues = array(
                  'owner_projectid' => $projectid,
                  'status_cd' => 'B',
                  'usecase_nm' => "__UNITTEST_UC$ucnum",
                  'blurb_tx' => 'Created by automated test',
                  'precondition_tx' => 'Precondition test',
                  'postcondition_tx' => 'Postcondition test',
                  'steps_tx' => 'Steps test',
                  'references_tx' => 'Ref test',
                  'active_yn' => 1,
                  'owner_personid' => 1,
                  'importance' => 55,
                );
                $uc1bundle = $this->m_oWriteHelper->createUsecase($projectid, $ucvalues);
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
    
    private function checkUseCaseBundleMapping($usecasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi)
    {
        try
        {
            if(!isset($usecasesbundle['analysis']))
            {
                $justkeys = array_keys($usecasesbundle);
                throw new \Exception("Missing analysis section in "
                            . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
            $analysis_map = $usecasesbundle['analysis'];//['pct_complete_by_ucid'];
            $mapped_map = $analysis_map['mapped'];//['pct_complete_by_ucid'];
            $unmapped_map = $analysis_map['unmapped'];//['pct_complete_by_ucid'];
            $mapped_direct_wi_map = $mapped_map['workitem']['direct']['workitems'];
            $mapped_indirect_wi_map = $mapped_map['workitem']['indirect']['workitems'];
            $unmapped_wi_map = $unmapped_map['workitem']['workitems'];
            
            if(count($mapped_direct_wi_map) != $expected_direct_wi)
            {
                throw new \Exception("Expected direct mapped count $expected_direct_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            if(count($mapped_indirect_wi_map) != $expected_indirect_wi)
            {
                throw new \Exception("Expected indirect mapped count $expected_indirect_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_indirect_wi_map'=>$mapped_indirect_wi_map, 'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            if(count($unmapped_wi_map) != $expected_unmapped_wi)
            {
                throw new \Exception("Expected unmapped count $expected_unmapped_wi in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$unmapped_wi_map'=>$unmapped_wi_map, 'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function checkUseCaseBundleCompletion($usecasesbundle, $expected_total_wi, $expected_total_done_wi)
    {
        try
        {
            if(!isset($usecasesbundle['analysis']))
            {
                $justkeys = array_keys($usecasesbundle);
                throw new \Exception("Missing analysis section in "
                            . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
            $analysis_map = $usecasesbundle['analysis'];//['pct_complete_by_ucid'];
            $pct_complete_by_ucid_map = $analysis_map['pct_complete_by_ucid'];
            
            $found_total_wids_count = 0;
            $found_total_done_count = 0;
            foreach($pct_complete_by_ucid_map as $ucid=>$detail)
            {
                $found_total_wids_count += $detail['total_wids'];
                $found_total_done_count += $detail['total_done'];
                if($detail['total_wids'] > 0)
                {
                    $computed_pct = round(100*$detail['total_done']/$detail['total_wids']);
                    if($computed_pct != round($detail['pct_done']))
                    {
                        $justkeys = array_keys($usecasesbundle);
                        throw new \Exception("Expected {$computed_pct}% pct done for ucid#$ucid in "
                                    . \bigfathom\DebugHelper::getNeatMarkup(
                                            array('justkeys'=>$justkeys
                                                ,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                            )
                                );
                    }
                }
            }
            
            if($found_total_wids_count != $expected_total_wi)
            {
                throw new \Exception("Expected total wid count $expected_total_wi but instead found $found_total_wids_count in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            if($found_total_done_count != $expected_total_done_wi)
            {
                throw new \Exception("Expected total done count $expected_total_done_wi but instead found $found_total_done_count in " 
                        . \bigfathom\DebugHelper::getNeatMarkup(
                                    array('justkeys'=>$justkeys,'$mapped_direct_wi_map'=>$mapped_direct_wi_map,'entirebundle'=>$usecasesbundle,"USECASEBUNDLE")
                                    )
                        );
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    function testQueryUseCases()
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
                $result = $this->m_oMapHelper->getUseCasesBundle(NULL);
            } catch (\Exception $ex) {
                $test_missing_projectid = TRUE;
            }
            if(!$test_missing_projectid)
            {
                throw new \Exception("Expected an exception for missing projectid!");
            }
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $usecasebundle = $this->m_oMapHelper->getUseCasesBundle($projectid);
            $usecases_map = $usecasesbundle['lookup']['usecases'];
            if(count($usecases_map) > 0)
            {
                throw new \Exeption("Expected zero use cases for project#$projectid but instead found " . print_r($usecases_map,TRUE));
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
    
    function testMapWorkitemsToUseCases()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $usecasesbundle = $this->m_oMapHelper->getUseCasesBundle($projectid);
            $workitems_map = $usecasesbundle['lookup']['workitems'];
            $usecases_map = $usecasesbundle['lookup']['usecases'];
            
            $expected_direct_wi=0;
            $expected_indirect_wi=0;
            $expected_unmapped_wi=count($workitems_map);
            $this->checkUseCaseBundleMapping($usecasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi);
            
            $ucid_ar = array_keys($usecases_map);
            $uc_count = count($ucid_ar);
            $map_workitem2usecase_ar = [];
            $i=0;
            foreach($workitems_map as $wid=>$detail)
            {
                $i++;
                $ucid = $ucid_ar[$i % $uc_count];
                $map_usecase2wids[$ucid][$wid] = $wid;
            }
            foreach($ucid_ar as $ucid)
            {
                $myvalues = [];
                $myvalues['maps']['workitems'] = $map_usecase2wids[$ucid];
                $this->m_oWriteHelper->updateUsecase($ucid, $myvalues);
            }
            
            $after_usecasesbundle = $this->m_oMapHelper->getUseCasesBundle($projectid);
            $expected_direct_wi=count($workitems_map);
            $expected_indirect_wi=0;
            $expected_unmapped_wi=0;
            $this->checkUseCaseBundleMapping($after_usecasesbundle, $expected_direct_wi, $expected_indirect_wi, $expected_unmapped_wi);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testUseCaseCompletions()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
         
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $usecasesbundle = $this->m_oMapHelper->getUseCasesBundle($projectid);
            $workitems_map = $usecasesbundle['lookup']['workitems'];
            $usecases_map = $usecasesbundle['lookup']['usecases'];
            
            $expected_total_done_wi=0;
            $expected_total_wi=count($workitems_map);
            $this->checkUseCaseBundleCompletion($usecasesbundle, $expected_total_wi, $expected_total_done_wi);
            
            $ucid_ar = array_keys($usecases_map);
            $uc_count = count($ucid_ar);
            $map_workitem2usecase_ar = [];
            $i=0;
            foreach($workitems_map as $wid=>$detail)
            {
                $myvalues = [];
                $myvalues['status_cd'] = 'SC';
                $this->m_oWriteHelper->updateWorkitem($wid, $myvalues);
            }
            
            $after_usecasesbundle = $this->m_oMapHelper->getUseCasesBundle($projectid);
            $expected_total_done_wi=count($workitems_map);
            $expected_total_wi=count($workitems_map);
            $this->checkUseCaseBundleCompletion($after_usecasesbundle, $expected_total_wi, $expected_total_done_wi);
            
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
