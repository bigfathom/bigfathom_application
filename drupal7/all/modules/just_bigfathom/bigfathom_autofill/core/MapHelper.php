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

namespace bigfathom_autofill;

require_once 'DatabaseNamesHelper.php';

/**
 * This class tells us about mappings
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapHelper
{
    
    /**
     * Returns the active record or NULL if there is no active record for the project
     */
    public function getProjectAutofillTrackingRecord($projectid)
    {
        try
        {
            $sql = "SELECT *"
                    . " FROM " . \bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_tablename . " p"
                    . " WHERE projectid=$projectid" ;
            
            $result = db_query($sql);
            $record = $result->fetchAssoc();
            if(empty($record))
            {
                $record = NULL;
            }
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}

