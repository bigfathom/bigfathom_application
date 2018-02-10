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
 * Collection of some basic tests for the utilization module
 *
 * @author Frank Font
 */
class BasicAutofillTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    private $m_oUtilizationHelper = NULL;
    
    function getVersionNumber()
    {
        return '20170928.1';
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

            $loaded_engine = module_load_include('php','bigfathom_autofill','core/Engine');
            if(!$loaded_engine)
            {
                throw new \Exception('Failed to load the Engine class');
            }

            module_load_include('php','bigfathom_utilization','unit_tests/utilities/UtilizationHelper');
            $this->m_oUtilizationHelper = new \bigfathom_utilization\UtilizationHelper();

            
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
    
    function testCreateValidEngineInstance()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            
            $networkbundle_sets = [];
            $networkbundle_sets[] = array(
                'metadata'=>array('in_project'=>[]),
                'root' => array('wid'=>1));
            $networkbundle_sets[] = array(
                'metadata'=>array('in_project'=>[]),
                'root' => array('wid'=>12345));
            
            $all_changeable_workitems = [];
            
            $all_projects = $this->m_oMapHelper->getProjectsByID(NULL, TRUE);
            foreach($networkbundle_sets as $networkbundle)
            {
                $counter = 0;
                foreach($all_projects as $projectid=>$detail)
                {
                    $oEngine = new \bigfathom_autofill\Engine($projectid);
                    $all_changeable_workitems = [];
                    $oEngine->initialize($networkbundle, $all_changeable_workitems);
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
                    $counter++;
                    if($counter > 5)
                    {
                        //Thats enough testing
                        break;
                    }
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
    
    function testInvalidEngineInitialization()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $networkbundle_sets = [];
            $networkbundle_sets[] = array('root'=>array());
            $networkbundle_sets[] = array(
                'root' => array('wid'=>NULL));
            $networkbundle_sets[] = array(
                'metadata'=>[],
                'root' => array('wid'=>NULL));
            $networkbundle_sets[] = array(
                'metadata'=>array('in_project'=>[]),
                'root' => array('wid'=>NULL));
            
            $all_changeable_workitems = [];
                    
            $all_projects = $this->m_oMapHelper->getProjectsByID(NULL, TRUE);
            foreach($networkbundle_sets as $networkbundle)
            {
                $counter = 0;
                foreach($all_projects as $projectid=>$detail)
                {
                    $is_good = FALSE;
                    try
                    {
                        $oEngine = new \bigfathom_autofill\Engine($projectid);
                        $oEngine->initialize($networkbundle, $all_changeable_workitems);
                    } catch (\Exception $ex) {
                        $is_good = TRUE;
                    }
                    if(!$is_good)
                    {
                        throw new \Exception("Did not fail for invalid networkbundle=" . print_r($networkbundle,TRUE));
                    }
                    $counter++;
                    if($counter>3)
                    {
                        //Thats enough
                        break;
                    }
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
    
}
