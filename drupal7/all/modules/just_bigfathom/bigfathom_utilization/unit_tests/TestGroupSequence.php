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

namespace bigfathom_utilization;

/**
 * Provide sequence of test groups for this module
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TestGroupSequence
{
    public function __construct()
    {
    }

    public function getAllTestGroupRootNames()
    {
        $names_ar = [];
        $names_ar[] = 'BasicQueryTests';
        $names_ar[] = 'WorkDetailTests';
        
        $names_ar[] = 'NonStaticUtilizationTests';
        $names_ar[] = 'StaticUtilizationTests';
        
        $names_ar[] = 'MixedUnsavedUtilizationTests1';
        $names_ar[] = 'MixedUnsavedUtilizationTests2';
        $names_ar[] = 'MixedUnsavedUtilizationTests3';
        
        $names_ar[] = 'MixedSavedUtilizationTests1';
        $names_ar[] = 'MixedSavedUtilizationTests2';
        $names_ar[] = 'MixedSavedUtilizationTests3';
        
        return $names_ar;
    }
}
