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
 * Collection of some basic tests for the core module
 *
 * @author Frank Font
 */
class BasicProjectTests extends \bigfathom\AbstractActionTestGroup
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;
    
    private $m_oProjectHelper = NULL;
    
    private $m_aExistingProjects = NULL;
    
    private $m_ourtestprojectbundle = NULL;

    function getVersionNumber()
    {
        return '20170921.3';
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
            
            $this->m_aExistingProjects = $this->m_oMapHelper->getProjectsByID();            
            
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
        $warning_msg = NULL;
        try
        {
            $this->m_oProjectHelper->deleteAllTestProjects();
            $info = $this->m_oWriteHelper->deleteOrphanDanglers();
            if($info['total'] > 0)
            {
                $warning_msg = "Deleted " . $info['total'] . " orphans/danglers from database";
            }
            $insight = "Deletions " . print_r($info,TRUE);
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

    function testSetSelectedProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            //Perform the tests here
            $i = 0;
            $just_projectids = array_keys($this->m_aExistingProjects);
            foreach($just_projectids as $projectid)
            {
                $this->m_oContext->setSelectedProject($projectid);
                $checkid = $this->m_oContext->getSelectedProjectID();
                if($checkid != $projectid)
                {
                    throw new \Exception("Failed to set projectid=$projectid");
                }
                if($i > 9)
                {
                    //Thats enough tests
                    break;
                }
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
    
    function testClearSelectedProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            //Perform the tests here
            $this->m_oContext->clearSelectedProject();
            $checkid = $this->m_oContext->getSelectedProjectID();
            if(!empty($checkid))
            {
                throw new \Exception("Failed to clear the projectid!");
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
    
    function testCreateOurTestProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $this->m_ourtestprojectbundle = $this->m_oProjectHelper->createTestProject(1);
            
        } catch (\Exception $ex) {
            $has_error = 1;
            $error_msg = $ex->getMessage();
            $error_detail = $ex;
        }
        $done_ts = microtime(TRUE);
        $duration_mus = $done_ts - $start_ts;
        return array('duration_mus'=>$duration_mus,'has_error'=>$has_error, 'error_msg'=>$error_msg, 'error_detail'=>$error_detail);
    }
    
    function testAddWorkitemsToOurTestProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            $workitem_basetype_ar = array('G','T');
            
            $projectid = $this->m_ourtestprojectbundle['newid'];
            foreach($workitem_basetype_ar as $workitem_basetype)
            {
                $this->m_oProjectHelper->addWorkitemsToOurTestProject($projectid, $workitem_basetype);
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
    
    function testDeactivateOurTestProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $this->m_oWriteHelper->deactivateProject($projectid);
            $all_active_projectids = $this->m_oMapHelper->getProjectsIDs(TRUE);
            foreach($all_active_projectids as $existing_projectid)
            {
                if($projectid == $existing_projectid)
                {
                    throw new \Exception("Failed to deactivate our unit test project#$projectid");
                }
            }
            $all_projectids = $this->m_oMapHelper->getProjectsIDs(FALSE);
            $foundit = FALSE;
            foreach($all_projectids as $existing_projectid)
            {
                if($projectid == $existing_projectid)
                {
                    $foundit = TRUE;
                    break;
                }
            }
            if(!$foundit)
            {
                throw new \Exception("Failed to find our deactivate unit test project#$projectid");
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

    function testDeleteOurTestProject()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $projectid = $this->m_ourtestprojectbundle['newid'];
            $this->m_oProjectHelper->deleteOneProject($projectid);
            
            //Make sure all the residual stuff was deleted too
            $sql = "SELECT count(1) "
                    . " FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename 
                    . " WHERE owner_projectid=$projectid";
            $result = db_query($sql);
            $simple_array = $result->fetchCol();
            $thecount = $simple_array[0];
            if($thecount > 0)
            {
                throw new \Exception("Failed to remove $thecount workitems owned by project#$projectid!");
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

}
