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
class CorruptedCommunications extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    
    function getVersionNumber()
    {
        return '20171011.1';
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


    function testWorkitemThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_communication_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_communication_tablename . " b ON b.id=a.parent_comid"
                        . " WHERE a.parent_comid IS NOT NULL AND b.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem communication thread records!");
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

    function testWorkitemAttachmentMaps()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            //map_workitem_communication2attachment
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename . " m"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_communication_tablename . " c ON c.id=m.comid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_attachment_tablename . " a ON c.id=m.attachmentid"
                        . " WHERE c.id IS NULL OR a.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt workitem attachment map records!");
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
    
    function testProjectAttachmentMaps()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            //map_project_communication2attachment
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_project_communication2attachment_tablename . " m"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_communication_tablename . " c ON c.id=m.comid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_attachment_tablename . " a ON c.id=m.attachmentid"
                        . " WHERE c.id IS NULL OR a.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt project attachment map records!");
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
    
    function testSprintAttachmentMaps()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            //map_sprint_communication2attachment
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename . " m"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_sprint_communication_tablename . " c ON c.id=m.comid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_attachment_tablename . " a ON c.id=m.attachmentid"
                        . " WHERE c.id IS NULL OR a.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt sprint attachment map records!");
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
    
    function testProjectThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_project_communication_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_communication_tablename . " b ON b.id=a.parent_comid"
                        . " WHERE a.parent_comid IS NOT NULL AND b.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt project communication thread records!");
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
    
    function testSprintThreads()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $warning_msg=NULL;
        $insight=NULL;
        try
        {
            $core_sql = " FROM " . \bigfathom\DatabaseNamesHelper::$m_sprint_communication_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_sprint_communication_tablename . " b ON b.id=a.parent_comid"
                        . " WHERE a.parent_comid IS NOT NULL AND b.id IS NULL";
            $insight = "$core_sql";
            $result = db_query("SELECT count(1) $core_sql");
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                //$delete_sql = "DELETE FROM TBD";
                //$warning_msg = "DELETE ALL SQL=$delete_sql";
                throw new \Exception("Found $thecount corrupt sprint communication thread records!");
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
