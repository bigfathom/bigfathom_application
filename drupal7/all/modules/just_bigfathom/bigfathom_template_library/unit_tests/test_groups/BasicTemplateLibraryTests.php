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

namespace bigfathom_template_library;

/**
 * Collection of some basic tests for the templates module
 *
 * @author Frank Font
 */
class BasicTemplateLibraryTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    
    function getVersionNumber()
    {
        return '20170919.1';
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
    
    function testTablesExist()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        $sql = NULL;
        try
        {
            
            $db_tables = array(
                'bigfathom_template_project_library'=>array('id','owner_personid','active_yn','updated_dt')
                ,'bigfathom_map_tag2tp'=>array('tag_tx','template_projectid')
                ,'bigfathom_map_tag2tw'=>array('tag_tx','template_workitemid')
                ,'bigfathom_template_project_recent_data_updates'=>array('template_projectid','id','updated_dt')
                ,'bigfathom_template_workitem_recent_data_updates'=>array('template_workitemid','id','updated_dt')
            );
            
            foreach($db_tables as $one_db_table=>$fields_ar)
            {
                $fields_tx = implode(',', $fields_ar);
                $sql = "select $fields_tx from $one_db_table";
                $result = db_query($sql);
                while($record = $result->fetchAssoc()) 
                {
                    break;  //We don't need to read them
                }
            }
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage() . " [SQL:$sql]";
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
}
