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
 * Collection of some orphan tests for the core module
 *
 * @author Frank Font
 */
class CorruptedMapTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    
    function getVersionNumber()
    {
        return '20171016.2';
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

    function testMapGroupToProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_group2project_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_group_tablename . " g ON g.id=a.groupid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_tablename . " p ON p.id=a.projectid"
                        . " WHERE p.id IS NULL OR g.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt group maps!");
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
    
    function testMapWorkitemToWorkitem()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_wi2wi_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " dep ON dep.id=a.depwiid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " ant ON ant.id=a.antwiid"
                        . " WHERE dep.id IS NULL OR ant.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem maps!");
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
    
    function testMapWorkitemToUsecase()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2usecase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename . " u ON u.id=a.usecaseid"
                        . " WHERE w.id IS NULL OR u.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem to usecase maps!");
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
    
    function testMapWorkitemToTestcase()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2testcase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename . " u ON u.id=a.testcaseid"
                        . " WHERE w.id IS NULL OR u.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem to testcase maps!");
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
    
    function testMapWorkitemToDelegateOwner()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_person_tablename . " p ON p.id=a.personid"
                        . " WHERE w.id IS NULL OR p.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem to delegate owner maps!");
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
    
    function testMapUsecaseToDelegateOwner()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_delegate_usecaseowner_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename . " u ON u.id=a.usecaseid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_person_tablename . " p ON p.id=a.personid"
                        . " WHERE u.id IS NULL OR p.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt usecase to delegate owner maps!");
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
    
    function testMapTestcaseToDelegateOwner()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_delegate_testcaseowner_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename . " u ON u.id=a.testcaseid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_person_tablename . " p ON p.id=a.personid"
                        . " WHERE u.id IS NULL OR p.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt testcase to delegate owner maps!");
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
    
    function testMapSprintToDelegateOwner()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_delegate_sprintowner_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_sprint_tablename . " s ON s.id=a.sprintid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_person_tablename . " p ON p.id=a.personid"
                        . " WHERE s.id IS NULL OR p.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt usecase to delegate owner maps!");
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
    
    function testMapTmplwiToTmplwi()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tw2tw_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_template_workitem_tablename . " dep ON dep.id=a.depwiid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_template_workitem_tablename . " ant ON ant.id=a.antwiid"
                        . " WHERE dep.id IS NULL OR ant.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt template workitem maps!");
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
    
    function testMapTagToProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2project_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_tablename . " p ON p.id=a.projectid"
                        . " WHERE p.id";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt tag2project maps!");
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
    
    function testMapTagToWorkitem()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2workitem_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " WHERE w.id";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt tag2workitem maps!");
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
    
    function testMapTagToSprint()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2sprint_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_sprint_tablename . " s ON s.id=a.sprintid"
                        . " WHERE s.id";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt tag2project maps!");
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

    function testMapTagToUsecase()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2usecase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename . " s ON s.id=a.usecaseid"
                        . " WHERE s.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt tag2usecase maps!");
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
    
    function testMapTagToTestcase()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2testcase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename . " s ON s.id=a.testcaseid"
                        . " WHERE s.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt tag2testcase maps!");
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
