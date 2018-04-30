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

require_once 'helper/VisualDependenciesBasepage.php';

/**
 * Graphically interact with project workitem dependencies
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapProjectDependenciesPage extends \bigfathom\VisualDependenciesBasepage
{

    public function __construct($projectid, $urls_override_arr=NULL, $page_parambundle=NULL)
    {
        parent::__construct("project",$projectid, $urls_override_arr, $page_parambundle);
    }

}
