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

require_once 'Utility.php';
require_once 'BaseMixedUtilizationTests.php';

/**
 * Collection of some work detail tests for the utilization module
 *
 * @author Frank Font
 */
class MixedSavedUtilizationTests1 extends \bigfathom_utilization\BaseMixedUtilizationTests
{

    public function __construct()
    {
        $this->setMode(1, TRUE);
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
    
}
