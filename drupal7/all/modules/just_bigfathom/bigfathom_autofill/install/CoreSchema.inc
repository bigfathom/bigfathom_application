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

namespace bigfathom_autofill;   //Each module has its own namespace for schema

/**
 * This class is for database schema content
 *
 * @author Frank Font
 */
class CoreSchema
{
    /**
     * Add all the schema content for the tables
     */
    public function addToSchema(&$schema)
    {
        try
        {
            self::addMetadataTables($schema);
            self::addPrimaryTables($schema);
            self::addTagMapTables($schema);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
    
    private static function addPrimaryTables(&$schema)
    {
        $schema['bigfathom_project_autofill_action'] = array(
          'description' => 'Track details of an autofill operation on a project',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the action record',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project being auto-filled',
            ),
            'max_duration_allowed' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The maximum number of seconds this operation should be allowed to run (assume it failed if time is exceeded +15 second buffer)',
            ),
            'workitems_to_process_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of workitems we expect to process',
            ),
            'completed_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'The number of passes completed (every iteration after the first is a refinement iteration)',
            ),
            'max_allowed_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 5,
              'description' => 'The max number of passes we will allow',
            ),
            'expected_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of passes we expect to have',
            ),
            'duration_pass1' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the first pass',
            ),
            'updated_workitems_pass1' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the first pass',
            ),
            'duration_pass2' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the second pass',
            ),
            'updated_workitems_pass2' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the second pass',
            ),
            'duration_pass3' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the third pass',
            ),
            'updated_workitems_pass3' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the third pass',
            ),
            'duration_last_pass' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the most recent pass',
            ),
            'updated_workitems_last_pass' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the most recently completed pass',
            ),
            'estimated_remaining_duration' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'An estimate on remaining duration to complete the autofill',
            ),
            'total_duration' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The total number of seconds it took to complete',
            ),
            'total_updated_workitems' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The total number of updated workitems',
            ),
            'action_status' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => '1=preparing to start autofill operation, 2=started autofill, 5=successful autofill completion, 7=failed autofill operation',
            ),
            'started_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the autofill started',
            ),
            'completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the autofill completed',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('project'=>array('projectid')),
        );
        
        $schema['bigfathom_project_autofill_action_history'] = array(
          'description' => 'Track details of completed autofill operations on a projects',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the action record',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project being auto-filled',
            ),
            'max_duration_allowed' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The maximum number of seconds this operation should be allowed to run (assume it failed if time is exceeded +15 second buffer)',
            ),
            'workitems_to_process_ct' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of workitems we expect to process',
            ),
            'completed_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of passes completed (every iteration after the first is a refinement iteration)',
            ),
            'max_allowed_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The max number of passes we will allow',
            ),
            'expected_passes' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of passes we expect to have',
            ),
            'duration_pass1' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the first pass',
            ),
            'updated_workitems_pass1' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the first pass',
            ),
            'duration_pass2' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the second pass',
            ),
            'updated_workitems_pass2' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the second pass',
            ),
            'duration_pass3' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the third pass',
            ),
            'updated_workitems_pass3' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the third pass',
            ),
            'duration_last_pass' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of seconds it took to complete the most recent pass',
            ),
            'updated_workitems_last_pass' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The number of updated workitems on the most recently completed pass',
            ),
            'total_duration' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The total number of seconds it took to complete',
            ),
            'total_updated_workitems' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The total number of updated workitems',
            ),
            'action_status' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => '5=successful autofill completion, 7=failed autofill operation',
            ),
            'started_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the autofill started',
            ),
            'completed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the autofill completed',
            ),
            'error_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Error detail if the autofill operation failed',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was last updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When original record was created',
            ),
            'original_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person who created the original record',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this history record was created',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('project'=>array('projectid','id')),
        );
        
    }

    private static function addMetadataTables(&$schema)
    {
    }
    
    private static function addTagMapTables(&$schema)
    {
    }
}
    