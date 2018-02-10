<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * --------------------------------------------------------------------------------------
 *
 */

namespace bigfathom;

/**
 * This class helps with workflow
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class WorkflowHelper
{
   public function getDefaultNewGoalStatusCode()
   {
       return 'B';
   }
   
   public function getDefaultNewTaskStatusCode()
   {
       return 'B';
   }
}

