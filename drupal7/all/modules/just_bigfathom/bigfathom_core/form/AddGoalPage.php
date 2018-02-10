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
 *
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'AddWorkitemPage.php';

/**
 * Add a Goal
 *
 * @author Frank Font
 */
class AddGoalPage extends \bigfathom\AddWorkitemPage
{
    public function __construct($projectid, $urls_arr=NULL)
    {
        parent::__construct('goal', $projectid, $urls_arr);
    }
}
