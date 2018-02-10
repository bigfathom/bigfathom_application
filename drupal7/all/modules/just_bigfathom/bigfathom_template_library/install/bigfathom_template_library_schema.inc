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

require_once 'CoreSchema.inc';

/**
 * Create the tables
 */
function bigfathom_template_library_update_7101() 
{
    $t = get_t();
    drupal_set_message($t('Created tables for bigfathom_template_library'));
}

/**
 * Create all the tables of the database 
 * @return schema array
 */
function bigfathom_template_library_schema()
{
    try
    {
        $schema = array();

        $oSH = new \bigfathom_template_library\CoreSchema();
        $oSH->addToSchema($schema);

        return $schema;
    } catch (\Exception $ex) {
        throw $ex;
    }
}
    