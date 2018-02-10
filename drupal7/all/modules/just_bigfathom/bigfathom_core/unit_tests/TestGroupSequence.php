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
 * Provide sequence of test groups
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
        $names_ar[] = 'BasicUserAccountTests';
        $names_ar[] = 'BasicProjectTests';
        $names_ar[] = "BasicUseCaseTests";
        $names_ar[] = "BasicTestCaseTests";
        $names_ar[] = "OrphanCheckTests";
        $names_ar[] = "CorruptedMapTests";
        $names_ar[] = "CorruptedCommunications";
        $names_ar[] = "CommunicationActions";
        
        return $names_ar;
    }
}
