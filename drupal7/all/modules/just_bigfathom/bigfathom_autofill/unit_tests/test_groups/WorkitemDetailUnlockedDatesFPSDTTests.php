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

require_once 'BaseWorkDetailTests.php';

/**
 * Collection of some work detail tests for the utilization module
 *
 * @author Frank Font
 */
class WorkitemDetailUnlockedDatesFPSDTTests extends \bigfathom_autofill\BaseWorkDetailTests
{
    public function __construct()
    {
        parent::__construct();
        $forced_project_start_dt =  $this->m_oUtilizationHelper->getDateAfterAllExistingWork(NULL,30);
        $this->setMode('unlocked_dates',$forced_project_start_dt);
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
