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

namespace bigfathom;

require 'AbstractActionTestGroup.php';
require 'TestGroupSequence.php';

/**
 * Use this to run all the unit tests
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UnitTestHarness
{
    private $m_map = NULL;
    private $m_aTGS = NULL;
    
    public function __construct($print_messages=TRUE)
    {
        $m_aTGS = [];
        $m_aTGS[] = array(
                'module_name' => 'bigfathom_core',
                'instance' => new \bigfathom_core\TestGroupSequence()
            );
        $module_sequence = array(
                'bigfathom_template_library', 
                'bigfathom_utilization', 
                'bigfathom_reports', 
                'bigfathom_forecast', 
                'bigfathom_autofill',
                'bigfathom_notify'
            );
        $testing_map = [];
        $testing_map['bigfathom_core'] = 'bigfathom_core';
        
        $enabled_modules_map = $this->getEnabledModuleNames();
        foreach($module_sequence as $module_name)
        {
            if(!empty($enabled_modules_map['bigfathom_utilization']))
            {
                $m_aTGS[] = array(
                    'module_name'=>$module_name,
                    'instance'=>$this->getTestGroupSequence($module_name)
                );
                $testing_map[$module_name] = $module_name;
            } else {
                if($print_messages)
                {
                    drupal_set_message("Module $module_name will not be unit tested becase not enabled", 'warning');
                }
            }
        }
        foreach($enabled_modules_map as $module_name)
        {
            if(empty($testing_map[$module_name]))
            {
                if($print_messages)
                {
                    drupal_set_message("No unit tests loaded for module $module_name",'warning');
                }
            }
        }
        $this->m_aTGS = $m_aTGS;
    }
    
    private function getTestGroupSequence($modulename)
    {
        try
        {
            $loaded = module_load_include('php',$modulename,'unit_tests/TestGroupSequence');
            if(!$loaded)
            {
                throw new \Exception("Failed to load the TestGroupSequence class for $modulename");
            }
            $fullyqualified = "\\" . $modulename . "\\TestGroupSequence";
            return new $fullyqualified();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getEnabledModuleNames()
    {
        $available_modules = [];
        $modules = system_rebuild_module_data();
        foreach($modules as $k=>$module_drupal_info)
        {
            $module_basicinfo = $module_drupal_info->info;
            if($module_basicinfo['package'] == 'Bigfathom')
            {
                $module_name = $module_drupal_info->name;
                $module_basicinfo = $module_drupal_info->info;
                if(module_exists($module_name))                
                {
                    $available_modules[$module_name] = $module_name;
                }
            }
        }
        return $available_modules;
    }

    /**
     * Run all the tests in a class and return a bundle with all the details.
     */
    public function getTestResultBundle($classname, $modulename)
    {
        try
        {
            if(empty($classname))
            {
                throw new \Exception("Missing required classname!");
            }
            if(empty($modulename))
            {
                throw new \Exception("Missing required modulename!");
            }
            if($this->m_map === NULL)
            {
                $this->loadAllTheTestGroups();
            }
            if(empty($this->m_map[$modulename][$classname]))
            {
                throw new \Exception("There is no testgroup at map[$modulename][$classname]!");
            }

            $bundle = [];
            
            $classinstance = $this->m_map[$modulename][$classname];
            
            $started_setup_ts = microtime(TRUE);
            $setup_result = $classinstance->setUp();
            $started_test_group_ts = microtime(TRUE);
            $test_group_result = $classinstance->runAllTests();
            $finished_test_group_ts = microtime(TRUE);
            $duration = $finished_test_group_ts - $started_test_group_ts;
            $teardown_result = $classinstance->tearDown();
            $alldone_ts = microtime(TRUE);
            
            $metadata = [];
            $metadata['setup_duration_mus'] = $started_test_group_ts - $started_setup_ts;
            $metadata['test_group_duration_mus'] = $duration;
            $metadata['teardown_duration_mus'] = $alldone_ts - $finished_test_group_ts;
            $metadata['total_duration_mus'] = $alldone_ts - $started_setup_ts;
                    
            $bundle['metadata'] = $metadata;
            $bundle['setup_result'] = $setup_result;
            $bundle['test_group_result'] = $test_group_result;
            $bundle['teardown_result'] = $teardown_result;
            
            return $bundle;
        } catch (\Exception $ex) {
            error_log("FAILED getTestResultBundle($classname, $modulename) because $ex");
            throw $ex;
        }
    }
    
    private function loadAllTheTestGroups()
    {
        try
        {
            $this->m_map = [];
            foreach($this->m_aTGS as $oTGS)
            {
                $module_name = $oTGS['module_name'];
                $names_ar = $oTGS['instance']->getAllTestGroupRootNames();
                foreach($names_ar as $class_name)
                {
                    $class = "\\$module_name\\$class_name";
                    //$file_name = $class_name . '.php';
                    //$filepath = drupal_get_path('module', $module_name).'/unit_tests/test_groups/'.$file_name; 
                    $class_file_shortpath = "/unit_tests/test_groups/$class_name";
                    $loaded = module_load_include('php',$module_name,"/unit_tests/test_groups/$class_name");
                    if(!$loaded)
                    {
                        throw new \Exception("Failed to load $class_file_shortpath");
                    }
                    //require_once $filepath;
                    $this->m_map[$module_name][$class_name] = new $class();
                }
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a map of all the available test groupings
     */
    public function getAllAvailableTestGroups()
    {
        if($this->m_map === NULL)
        {
            $this->loadAllTheTestGroups();
        }
        return $this->m_map;
    }        
}
