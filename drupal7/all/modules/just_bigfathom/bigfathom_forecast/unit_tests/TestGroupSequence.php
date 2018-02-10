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

namespace bigfathom_forecast;

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
        $names_ar[] = 'BasicForecastTests';
        return $names_ar;
    }
}
