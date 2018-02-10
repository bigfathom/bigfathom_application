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
class BasicUserAccountTests extends \bigfathom\AbstractActionTestGroup
{
    
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    
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
            
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            
            $this->m_aPeople = $this->m_oMapHelper->getPersonsByID();
            
            
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
    
    function testRealUserProfileBundle()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $param_ar = [];
            $param_ar[] = NULL;
            foreach($this->m_aPeople as $realid=>$detail)
            {
                $param_ar[] = $realid;
            }
            foreach($param_ar as $oneparam)
            {
                if(empty($oneparam))
                {
                    //Default param test
                    $upb = $this->m_oUAH->getUserProfileBundle();
                } else {
                    //Explicit user test
                    $upb = $this->m_oUAH->getUserProfileBundle($oneparam);
                }
                if(empty($upb))
                {
                    throw new \Exception("Empty result for getUserProfileBundle@id=$oneparam!");
                }
                if(empty($upb['roles']['systemroles']['summary']))
                {
                    throw new \Exception("Empty result for upb['roles']['systemroles']['summary']@id=$oneparam!");
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
   
    function testInvalidUserProfileBundle()
    {
        $has_error = 0;
        $error_msg = NULL;
        $error_detail = NULL;
        $start_ts = microtime(TRUE);
        try
        {
            $param_ar = [];
            $param_ar[] = -1;
            $maxid = 0;
            foreach($this->m_aPeople as $realid=>$detail)
            {
                if($realid > $maxid)
                {
                    $maxid = $realid;
                }
            }
            $param_ar[] = $maxid + 1;
            $id_good = [];
            $id_bad = [];
            foreach($param_ar as $oneparam)
            {
                try
                {
                    $this->m_oUAH->getUserProfileBundle($oneparam);
                    $id_bad[] = $oneparam;
                } catch (\Exception $ex) {
                    $id_good[] = $oneparam;
                }
            }
            if(count($id_bad)>0 || count($id_good) == 0)
            {
                throw new \Exception("Did not get expected exception for personids " . implode(' and ', $id_bad) . ' (okay for #s' . implode(' and ', $id_good) . ')');
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
