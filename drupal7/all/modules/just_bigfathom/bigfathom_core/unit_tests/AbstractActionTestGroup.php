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

/**
 * Extend this to implement action unit tests
 *
 * @author Frank Font of Room4me.com Software LLC
 */
abstract class AbstractActionTestGroup
{
    /**
     * Return a number (like a build ID) in the following format...
     * YYYYMMDD.#, where # is 1 for the first on that day, 2 for second 
     * version created on that day, etc.  Essentially, the # is the 
     * version integer value for a given day.
     */
    abstract function getVersionNumber();
    
    /**
     * Return nice name of this test group
     */
    abstract function getNiceName();
    
    /**
     * Call this from your implementation of getNiceName.
     */
    protected function shortcutGetNiceName($camel_case_text)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $camel_case_text, $matches);
        $ret = $matches[0];
        return implode(' ', $ret);
    }
    
    /**
     * Return the actual name of the class, this must also be the 
     * filename root.
     */
    abstract function getClassName();
    
    /**
     * Call this from your implementation of getClassName.
     */
    protected function shortcutGetClassNameWithoutNamespace($fullname)
    {
        //$fullname = get_class();
        $strpos = strrpos($fullname, '\\');
        if($strpos === FALSE)
        {
            $justclassname = $fullname;
        } else {
            $justclassname = substr($fullname, $strpos+1);
        }
        return $justclassname;
    }
    
    /**
     * Return all the test names in the group in execution sequence
     */
    abstract function getAllTestMethods();
    
    /**
     * Call this from your implementation of getClassName.
     */
    protected function shortcutGetAllTestMethods($classinstance)
    {
        $sequencenum=0;
        $methods = get_class_methods($classinstance);
        $tests = [];
        foreach($methods as $name)
        {
            if(strpos($name,'test') === 0)
            {
                $testname = substr($name,4);
                if(strlen($testname) > 0)
                {
                    $sequencenum++;
                    $onemethod = [];
                    $onemethod['nice_name'] = $this->shortcutGetNiceName($testname);
                    $onemethod['real_name'] = $name;
                    $onemethod['sequencenum'] = $sequencenum;
                    $tests[] = $onemethod;
                }
            }
        }
        return $tests;
    }
    
    /**
     * Return TRUE if this test requires all users out of the system
     */
    abstract function isExclusiveRequired();
    
    /**
     * Setup whatever this test needs
     */
    abstract function setUp();
    
    /**
     * Run a test and return the result.
     * Keep calling this until all tests in the group have run.
     * returns NULL if there are no more test to run.
     */
    abstract function runAllTests();

    protected function shortcutRunAllTests($all_methods)
    {
        $bundle = [];
        
        $method2result = [];
        $error_count = 0;
        $success_count = 0;
        
        foreach($all_methods as $onemethod)
        {
            $real_name = $onemethod['real_name'];
            $result = $this->$real_name();
            if($result['has_error'])
            {
                $error_count++;
            } else {
                $success_count++;
            }
            $onemethod['result'] = $result; 
            $method2result[$real_name] = $onemethod;
        }
        
        $summary['success_count'] = $success_count;
        $summary['error_count'] = $error_count;
        
        $bundle['summary'] = $summary;
        $bundle['method2result'] = $method2result;
        
        return $bundle;
    }
    
    /**
     * Remove anything we setup for the test group
     */    
    abstract function tearDown();
}
