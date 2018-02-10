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
 * SPECIAL CREDIT: Initial database schema brainstorming with Austin Font May/June 2015
 */


namespace bigfathom_core;   //Each module has its own namespace for schema


/**
 * This class is for database schema content
 *
 * @author Frank Font
 */
class CoreSchema
{
    /**
     * Add all the schema content for the contra indication tables
     */
    public function addToSchema(&$schema)
    {
        try
        {
            self::addMetadataTables($schema);
            self::addProcessTables($schema);
            self::addPrimaryTables($schema);
            self::addAttachmentTables($schema);
            self::addCommunicationTables($schema);
            self::addSystemRoleMapTables($schema);
            self::addTopProjectMapTables($schema);
            self::addSubMapTables($schema);
            self::addSuggestionTables($schema);
            self::addForecastTables($schema);
            self::addTagMapTables($schema);
            //self::addQATables($schema);
            self::addDelegateTables($schema);
            self::addHistoryTables($schema);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    

    private static function addDelegateTables(&$schema)
    {
        $schema['bigfathom_map_delegate_workitemowner'] = array(
          'description' => 'Declare a user as a delegated workitem owner',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the delegate',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid'),
          'unique keys' => array(
              'byworkitemid'=>array('workitemid','personid'),
              ),          
        );
        
        $schema['bigfathom_map_delegate_sprintowner'] = array(
          'description' => 'Declare a user as a delegated sprint owner',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the delegate',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','sprintid'),
        );

        $schema['bigfathom_map_delegate_usecaseowner'] = array(
          'description' => 'Declare a user as a delegated test case owner',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the delegate',
            ),
            'usecaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','usecaseid'),
        );
        
        $schema['bigfathom_map_delegate_testcaseowner'] = array(
          'description' => 'Declare a user as a delegated use case owner',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the delegate',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the use case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','testcaseid'),
        );
    }
    
    private static function addProcessTables(&$schema)
    {
        $schema['bigfathom_process_workflow'] = array(
          'description' => 'A process workflow definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the process workflow',
            ),
            'workflow_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The process workflow name',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text overview blurb for this process workflow',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this process workflow is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );
        
        $schema['bigfathom_process_cycle_type'] = array(
          'description' => 'A process cycle type definition',
          'fields' => array(
            'cycle_type_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The process cycle type name',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text overview blurb for this process cycle type',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this process cycle type is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('cycle_type_nm'),
        );
        
        $schema['bigfathom_process_cycle'] = array(
          'description' => 'A process cycle definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the process cycle',
            ),
            'cycle_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The process cycle name',
            ),
            'cycle_type_nm' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The process cycle type name',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text overview blurb for this process cycle',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this process cycle is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );
        
        $schema['bigfathom_process_state'] = array(
          'description' => 'A process state definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the process state',
            ),
            'state_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The process state name',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text overview blurb for this process state',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this process state is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );
        
        $schema['bigfathom_map_cycle_process_state2process_state'] = array(
          'description' => 'Map a process_state to a process_state in a cycle',
          'fields' => array(
            'workflowid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the cycle',
            ),
            'cycleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the cycle',
            ),
            'sourceid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the source process state',
            ),
            'targetid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the target process state',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workflowid','cycleid','sourceid','targetid'),
          'unique keys' => array('targets'=>array('targetid', 'sourceid', 'cycleid', 'workflowid')),
        );

        $schema['bigfathom_map_linear_process_state2process_state'] = array(
          'description' => 'Map a process_state to a process_state in a non-cycle',
          'fields' => array(
            'workflowid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the cycle',
            ),
            'sourceid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the source process state',
            ),
            'targetid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the target process state',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workflowid','sourceid','targetid'),
          'unique keys' => array('targets'=>array('targetid', 'sourceid', 'workflowid')),
        );
        
    }    

    /**
     * Return the schemas for one communication context
     */
    private static function getCommunicationTableSchema($raw_thingname,$suffix="communication",$map_steps=FALSE)
    {
        $schema = array();
        $thingname = strtolower($raw_thingname);
        
        $schema["bigfathom_{$thingname}_{$suffix}"] = array(
          'description' => "A $thingname $suffix",
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => "Unique numeric ID of the $thingname $suffix",
            ),
            'parent_comid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => "If this is a reply to an existing $suffix, then this field is ID of that parent comment",
            ),
            "{$thingname}id" => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => "Unique numeric ID of the $thingname to which this $suffix applies",
            ),
            'status_cd_at_time_of_com' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => "Current status of this $thingname",
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => "Optional title for the $suffix",
            ),
            'body_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => "The $suffix text",
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'action_requested_concern' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Level of concern for resolving the action request (0=no action requested,10=low,20=medium,30=high)',
            ),
            'action_reply_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => "If not empty, then this $suffix is directly associated with the action resolution indicated by this code",
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => "If 0 then this $suffix has been soft-deleted",
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array(
              'main'=>array("{$thingname}id",'parent_comid','created_dt'),
              'recent'=>array('updated_dt'),
              ),
        );
            
        $schema["bigfathom_{$thingname}_{$suffix}_history"] = array(
          'description' => "A $thingname $suffix historical record",
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => "Unique numeric ID of the historical $thingname $suffix",
            ),
            'original_comid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => "The ID of the replaced $suffix",
            ),
            'parent_comid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => "If this is a reply to an existing comment, then this field is ID of that parent $suffix",
            ),
            "{$thingname}id" => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => "Unique numeric ID of the $thingname to which this $suffix applies",
            ),
            'status_cd_at_time_of_com' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => "Current status of this $thingname",
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => "Optional title for the $suffix",
            ),
            'body_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => "The $suffix text",
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'action_requested_concern' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Level of concern for resolving the action request (0=no action requested,10=low,20=medium,30=high)',
            ),
            'action_reply_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => "If not empty, then this $suffix is directly associated with the action resolution indicated by this code",
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => "If 0 then this $suffix has been soft-deleted",
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was replaced',
            ),
                    
            'changed_status_cd_at_time_of_com' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=changed from original record, 0=no change, NULL=not known',
            ),
            'changed_title_tx' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=changed from original record, 0=no change, NULL=not known',
            ),
            'changed_body_tx' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => '1 if changed from original record, else 0',
            ),
            'changed_action_requested_concern' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=changed from original record, 0=no change, NULL=not known',
            ),
            'changed_action_reply_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=changed from original record, 0=no change, NULL=not known',
            ),
            'changed_active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=changed from original record, 0=no change, NULL=not known',
            ),
            'record_removed_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=record was removed, 0=not removed',
            ),
            'change_owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of person that owns the change',
            ),
            'num_attachments_added' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Number of attachments added',
            ),
            'num_attachments_removed' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Number of attachments removed',
            ),
          ),
                    
          'primary key' => array('id'),
          'indexes' => array(
              'main'=>array('original_comid','parent_comid','replaced_dt'),
              'native'=>array("{$thingname}id",'parent_comid','original_comid'),
              'replaced'=>array('replaced_dt'),
              ),
        );
              
        if($map_steps)
        {
            $schema["bigfathom_map_{$thingname}_{$suffix}2{$thingname}step"] = array(
              'description' => "Map one {$thingname} $suffix to a step",
              'fields' => array(
                'comid' => array(
                  'type' => 'int',
                  'unsigned' => TRUE,
                  'not null' => TRUE,
                  'description' => "The $suffix to which this is mapped",
                ),
                "{$thingname}stepid" => array(
                  'type' => 'int',
                  'unsigned' => TRUE,
                  'not null' => TRUE,
                  'description' => 'The step that is mapped',
                ),
              ),
              'primary key' => array('comid',"{$thingname}stepid"),
            );
            $schema["bigfathom_map_{$thingname}_communication2{$thingname}step_history"] = array(
              'description' => "Map {$thingname} comment to a step",
              'fields' => array(
                'comid' => array(
                  'type' => 'int',
                  'unsigned' => TRUE,
                  'not null' => TRUE,
                  'description' => 'The comment to which this is was mapped',
                ),
                "{$thingname}stepid" => array(
                  'type' => 'int',
                  'unsigned' => TRUE,
                  'not null' => TRUE,
                  'description' => 'The step that was mapped',
                ),
                'replaced_dt' => array(
                  'type' => 'datetime',
                  'mysql_type' => 'datetime',  
                  'not null' => TRUE,
                  'description' => 'When the original record was replaced',
                ),
              ),
              'indexes' => array(
                  'main'=>array('comid',"{$thingname}stepid",'replaced_dt'),
                  'replaced'=>array('replaced_dt'),
                  ),
            );
        }
              
        $schema["bigfathom_map_{$thingname}_{$suffix}2attachment"] = array(
          'description' => "Map one {$thingname} $suffix to an uploaded file",
          'fields' => array(
            'comid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => "The $suffix to which this is mapped",
            ),
            'attachmentid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The attachment that is mapped',
            ),
          ),
          'primary key' => array('comid','attachmentid'),
        );

        $schema["bigfathom_map_{$thingname}_communication2attachment_history"] = array(
          'description' => "Map {$thingname} comment to an uploaded file",
          'fields' => array(
            'comid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The comment to which this is was mapped',
            ),
            'attachmentid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The attachment that was mapped',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was replaced',
            ),
          ),
          'indexes' => array(
              'main'=>array('comid','attachmentid','replaced_dt'),
              'replaced'=>array('replaced_dt'),
              ),
        );
          
        return $schema;
    }
    
    private static function addCommunicationTables(&$schema)
    {
        $projectschemas = self::getCommunicationTableSchema('project', 'communication');
        foreach($projectschemas as $tablename=>$schemadetail)
        {
            $schema[$tablename] = $schemadetail;
        }
        $workitemschemas = self::getCommunicationTableSchema('workitem', 'communication');
        foreach($workitemschemas as $tablename=>$schemadetail)
        {
            $schema[$tablename] = $schemadetail;
        }
        $sprintschemas = self::getCommunicationTableSchema('sprint', 'communication');
        foreach($sprintschemas as $tablename=>$schemadetail)
        {
            $schema[$tablename] = $schemadetail;
        }
        $usecaseschemas = self::getCommunicationTableSchema('usecase', 'communication',TRUE);
        foreach($usecaseschemas as $tablename=>$schemadetail)
        {
            $schema[$tablename] = $schemadetail;
        }
        $testschemas = self::getCommunicationTableSchema('testcase', 'communication',TRUE);
        foreach($testschemas as $tablename=>$schemadetail)
        {
            $schema[$tablename] = $schemadetail;
        }
    }

    private static function addPrimaryTables(&$schema)
    {
        $schema['bigfathom_visionstatement'] = array(
          'description' => 'A vision statement',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the vision statement',
            ),
            'statement_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'A short name for the vision statement',
            ),
            'statement_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'The vision statement detail',
            ),
            'references_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Relevant references in support of ideas in the vision statement',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this statement is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the primary statement owner',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('statement_nm')),
        );
        
        $schema['bigfathom_usecase'] = array(
          'description' => 'A use case description',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the use case',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project that owns this use case',
            ),
            'usecase_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'A short name for the use case statement',
            ),
            'blurb_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'The short introduction blurb',
            ),
            'perspective_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'description' => 'U=External user, T=Technical user',
            ),
            'precondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The state of the system at the start of this use case',
            ),
            'postcondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The state of the system at the end of this use case',
            ),
            'steps_tx' => array(
              'type' => 'varchar',
              'length' => 8192,
              'not null' => FALSE,
              'description' => 'The steps of the use case as a chunk of text',
            ),
            'references_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Relevant references in support of ideas in the use case',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this use case',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this use case is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the primary use case owner',
            ),
            'effort_tracking_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the workitem where we track the effort of this use case development',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('owner_projectid','usecase_nm')),
        );
        
        $schema['bigfathom_testcase'] = array(
          'description' => 'A test case description',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project that owns this test case',
            ),
            'testcase_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'A short name for the test case statement',
            ),
            'blurb_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'The short introduction blurb',
            ),
            'perspective_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'description' => 'U=External user, T=Technical user',
            ),
            'precondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The state of the system at the start of this test case',
            ),
            'postcondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The state of the system at the end of this test case',
            ),
            'references_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Relevant references in support of ideas in the test case',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this test case',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this test case is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the primary test case owner',
            ),
            'effort_tracking_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the workitem where we track the effort of this test case execution',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('owner_projectid','testcase_nm')),
        );
        
        $schema["bigfathom_testcasestep"] = array(
          'description' => "Each record is a step for a test case",
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => "Unique numeric ID of this test case step",
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => "Unique numeric ID of the test case record owning this step",
            ),
            'step_num' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'First step should be number 1',
            ),
            'instruction_tx' => array(
              'type' => 'varchar',
              'length' => 5000,
              'not null' => FALSE,
              'description' => 'Describe the step exection detail',
            ),
            'expectation_tx' => array(
              'type' => 'varchar',
              'length' => 5000,
              'not null' => FALSE,
              'description' => 'Describe the expected result of executing the step',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'Current status of this step',
            ),
            'executed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this item was tested',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array(
              'main'=>array('testcaseid','status_cd'),
              'tested'=>array('executed_dt'),
              'recent'=>array('updated_dt'),
              ),
        );
        
        $schema['bigfathom_portfolio'] = array(
          'description' => 'A portfolio definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the portfolio',
            ),
            'portfolio_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The group name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this portfolio',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this portfolio is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the portfolio owner',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('portfolio_nm')),
        );
        
        $schema['bigfathom_group'] = array(
          'description' => 'A group definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'group_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The group name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this group',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this group is not available for new uses',
            ),
            'leader_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the group leader',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('group_nm')),
        );

        $schema['bigfathom_sprint'] = array(
          'description' => 'A sprint definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project that owns this sprint',
            ),
            'iteration_ct' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'The sprint number (name of a sprint is Sprint and this number)',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => FALSE,
              'description' => 'A short optional title for this sprint',
            ),
            'story_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'An optional story for this sprint',
            ),
            'start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this sprint starts',
            ),
            'end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this sprint ends',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person that owns this sprint',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this sprint',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'official_score' => array(
              'type' => 'int',
              'not null' => FALSE,
              'description' => 'Score on range 0 to 100 where 100 is perfect',
            ),
            'score_body_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'An optional comment for the score of this sprint',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] that all sprint members will be completed in within the sprint period.  Freeze this value when the status reaches a terminal state.',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] the the sprint will complete on-budget',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-budget success probability value',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-time success probability value',
            ),
            'membership_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then no one can edit the membership',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this sprint is not active',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('owner_projectid','title_tx','iteration_ct')),
        );
        
        $schema['bigfathom_person'] = array(
          'description' => 'Person account',
          'fields' => array(
            'id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Unique text ID of the person',
            ),
            'first_nm' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Actual first name of the person',
            ),
            'last_nm' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => TRUE,
              'description' => 'Actual last name of the person',
            ),
            'primary_locationid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary location',
            ),
            'primary_phone' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'description' => 'A primary email address for this person',
            ),
            'secondary_phone' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'description' => 'A backup phone address for this person',
            ),
            'secondary_email' => array(
              'type' => 'varchar',
              'length' => 254,
              'not null' => FALSE,
              'description' => 'A backup email address for this person',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'can_create_local_project_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot create local projects',
            ),
            'can_create_remote_project_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot create proxy projects',
            ),
            'can_archive_project_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot archive projects',
            ),
            'can_delete_project_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot delete projects',
            ),
            'can_create_template_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot create templates',
            ),
            'can_delete_template_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot delete templates',
            ),
            'can_own_visionstatement_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person cannot own vision statements',
            ),
            'baseline_availabilityid' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Reference to a baseline availability definition',
            ),
            'show_elements' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'comma delmited list of codewords for the app to show features (e.g., screens)',
            ),
            'hide_elements' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'comma delmited list of codewords for the app to hide features (e.g., screens)',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this person is not available for new work',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname')),
        );

        $schema['bigfathom_availability_reservation'] = array(
          'description' => 'Declare a period of time that is reserved for specific named uses',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Unique text ID of the record',
            ),
            'start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period starts',
            ),
            'end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period ends',
            ),
            'hours_per_day' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 2,
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Use zero if person is taking time off during the period',
            ),
            'work_saturday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_sunday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_monday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_tuesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_wednesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_thursday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_friday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_holidays_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on holidays too.  Ignored if hours is ZERO.',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'A comment about this availability period',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person saving this record',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname','start_dt','end_dt')),
        );
        
        $schema['bigfathom_map_person2availability'] = array(
          'description' => 'Map a person to an availability declaration within a set period of time',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period starts',
            ),
            'end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period ends',
            ),
            'type_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'B=Baseline, V=Vacation period, P=Part-time period, T=Travel period, C=Crunch period, D=Disability period, O=Other period type',
            ),
            'hours_per_day' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 2,
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Use zero if person is taking time off during the period',
            ),
            'work_saturday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_sunday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_monday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_tuesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_wednesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_thursday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_friday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_holidays_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on holidays too.  Ignored if hours is ZERO.',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'A comment about this availability period',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person saving this record',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('personid','start_dt','end_dt')),
        );
        
        $schema['bigfathom_baseline_availability'] = array(
          'description' => 'A named availability declaration within a set period of time',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the item',
            ),
            'is_planning_default_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then this the default used for general estimating',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'A unique short name for this',
            ),
            'hours_per_day' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 2,
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Use zero if person is taking time off during the period',
            ),
            'work_saturday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_sunday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_monday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_tuesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_wednesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_thursday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_friday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_holidays_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1, then working on holidays too.  Ignored if hours is ZERO.',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'A comment about this availability period',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is no longer available for new use',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person saving this record',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname')),
        );
        
        $schema['bigfathom_holiday'] = array(
          'description' => 'Declare holidays',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the holiday record',
            ),
            'holiday_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',
              'not null' => TRUE,
              'description' => 'The day of the holiday',
            ),
            'holiday_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'The name of this holiday',
            ),
            'countryid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The country to which this holiday applies if only for one',
            ),
            'stateid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The state in the country to which this holiday applies if only for one',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'A comment about this holiday',
            ),
            'apply_to_all_users_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this holiday is not automatically applied to users of the system. Holidays can be overriden for individual users even when marked as 1 here.',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person that created this record',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person that last updated this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('mainlookup'=>array('holiday_dt','holiday_nm')),
        );
        
        $schema['bigfathom_equipment'] = array(
          'description' => 'Equipment definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the equipment',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Unique text ID of the equipment',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Name of the equipment',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'Static text describing the equipment',
            ),
            'primary_locationid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary location',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'condition_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current condition of this equipment',
            ),
            'condition_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the condition was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is no longer available for new use',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname')),
        );

        $schema['bigfathom_external_resource'] = array(
          'description' => 'External resource definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the external resource',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Unique text ID of the external resource',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Name of the external resource',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'Static text describing the external resource',
            ),
            'primary_locationid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary location',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'condition_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current condition of this external resource',
            ),
            'condition_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the condition was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is no longer available for new use',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname')),
        );

        $schema['bigfathom_location'] = array(
          'description' => 'Location definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the location',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Unique text ID of the location',
            ),
            'address_line1' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => FALSE,
              'description' => 'Line 1 of address',
            ),
            'address_line2' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => FALSE,
              'description' => 'Line 2 of address',
            ),
            'city_tx' => array(
              'type' => 'varchar',
              'length' => 60,
              'not null' => FALSE,
              'description' => 'City',
            ),
            'stateid' => array(
              'type' => 'int',
              'not null' => FALSE,
              'description' => 'State in the country',
            ),
            'countryid' => array(
              'type' => 'int',
              'not null' => FALSE,
              'description' => 'Country',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'Static text describing the location',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is not active',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('shortname')),
        );

        $schema['bigfathom_country'] = array(
          'description' => 'Country definition',
          'fields' => array(
            'id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the country',
            ),
            'abbr' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Unique text ID of the country',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Name of the country',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is not active',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('abbr')),
        );
        
        $schema['bigfathom_state'] = array(
          'description' => 'A state in a country',
          'fields' => array(
            'id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the state',
            ),
            'countryid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The country to which this state belongs',
            ),
            'abbr' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Unique text ID of the state',
            ),
            'name' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Name of the state',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is not active',
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
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('abbr')),
        );
        
        $schema['bigfathom_role'] = array(
          'description' => 'A project role definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'role_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The role name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this role',
            ),
            'groupleader_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then this role is not considered for group leader functions',
            ),
            'projectleader_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then this role is not considered for project managment functions',
            ),
            'sprintleader_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then this role is not considered for sprint leadership functions',
            ),
            'workitemcreator_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this role is not considered for workitem creation functions',
            ),
            'workitemowner_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this role is not considered for workitem ownership functions',
            ),
            'tester_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 0 then this role is not considered for test functions',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this role is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('role_nm')),
        );
        
        $schema['bigfathom_systemrole'] = array(
          'description' => 'A system role metadata definition',
          'fields' => array(
            'id' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the system role',
            ),
            'role_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The role name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this system role',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this role is not available for new uses',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('role_nm')),
        );

        $schema['bigfathom_project'] = array(
          'description' => 'A project or surrogate project definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'project_contextid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary project context',
            ),
            'mission_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text mission statement for this project; capture the bigger picture here.',
            ),
            'root_goalid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The root goal of this project, see it for deadline and other project attributes',
            ),
            'surrogate_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this is a project surrogate',
            ),
            'source_type' => array(
              'type' => 'varchar',
              'length' => 10,
              'not null' => TRUE,
              'default' => 'local',
              'description' => "Where the project detail originates local=in this app instance, remote=outside this app instance",
            ),
            'allow_refresh_from_remote_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the surrogate project can be refreshed from a remote URI, if 0 then refresh is blocked.',
            ),
              
            'allow_status_publish_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this this project can publish status information for sharing outside the application',
            ),
              
            'allow_detail_publish_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this this project can publish detailed information for sharing outside the application',
            ),
              
            'allow_publish_item_dependency_diagram_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then this this item is shareable when publishing, else not sharable without an override.',
            ),
            'allow_publish_item_owner_name_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then this this item is shareable when publishing, else not sharable without an override.',
            ),
            'allow_publish_item_onbudget_p_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this this item is shareable when publishing, else not sharable without an override.',
            ),
            'allow_publish_item_actual_start_dt_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then this this item is shareable when publishing, else not sharable without an override.',
            ),
            'allow_publish_item_actual_end_dt_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then this this item is shareable when publishing, else not sharable without an override.',
            ),
              
            'snippet_bundle_head_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this is the head of a reusable snippet bundle',
            ),
              
            'template_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'DEPRECATE If 1 then this is a reusable project template',
            ),
              
            'template_author_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => FALSE,
              'description' => 'DEPRECATE If this is is a template, then this has the name of the person identified as the author',
            ),
              
            'archive_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this is a project is flagged for archiving',
            ),
              
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to start this project',
            ),
            'actual_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually started this project',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to close this project',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually closed this project',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-budget success probability value',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-time success probability value',
            ),
            'surrogate_ob_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Probability for completing on-budget from surrogate info source',
            ),
            'surrogate_ot_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Probability for completing on-time from surrogate info source',
            ),
            'surrogate_obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-budget success probability value from surrogate info source',
            ),
            'surrogate_otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-time success probability value from surrogate info source',
            ),

            'original_source_templateid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If this project was created from a template, then this is the original templateid',
            ),

            'original_source_template_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'If this project was created from a template, then this is the last updated date of that template when it was used',
            ),
            
            'original_source_template_refname' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'If this project was created from a template, then this is the refname of that template',
            ),
              
            'original_source_template_uri' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'The URI from which the template information was originally retrieved',
            ),
              
            'project_edit_lock_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => '0=all members can edit, 1=only edit by privileged collaborators, 2=only edit by owner, 3=only edit by primary owner, 99=nobody can edit',
            ),
              
            'show_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => '0=all members can see the project, 1=only privileged collaborators can see the project, 2=only owner can see the project, 3=only primary owner can see the project',
            ),
              
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this goal is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('rootgoal'=>array('root_goalid')),
        );

        $schema['bigfathom_published_project_info'] = array(
          'description' => 'Published project information',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'publishedrefname' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'The published reference name that this information is shared under',
            ),
            'project_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The common project name; generally the name of the root goal for locally tracked projects',
            ),
            'project_contextid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary project context at time of publication',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The id of the project instance to which this published information applies',
            ),
            'root_goalid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The root goal of this project at time of publication',
            ),
            'mission_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Description of the project at time of publication',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person at time of publication',
            ),
            'project_manager_override_tx' => array(
              'type' => 'varchar',
              'length' => 50,
              'not null' => FALSE,
              'description' => 'Optional override of the name of the project manager for publishing purposes',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to start this project at time of publication',
            ),
            'actual_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually started this project at time of publication',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to complete this project at time of publication',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually completed this project at time of publication',
            ),
            'onbudget_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Computed probability in range [0,1] of on-budget success at time of publication',
            ),
            'ontime_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Computed probability in range [0,1] of on-time success at time of publication',
            ),
            'onbudget_u' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Declared uncertainty in range [0,1] of on-budget success at time of publication',
            ),
            'ontime_u' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Declared uncertainty in range [0,1] of on-time success at time of publication',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment about this status',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'Current project status at time of publication',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this record is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array(
                 'byrefname'=>array('publishedrefname','projectid','updated_dt','id'),
                 'byprojectid'=>array('projectid','updated_dt','id'),
                 'byroot_goalid'=>array('root_goalid','updated_dt','id'),
              ),
        );
        
        $schema['bigfathom_remote_project_info'] = array(
          'description' => 'Imported project information from remote URIs',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'remote_uri' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'The URI from which status information was retrieved',
            ),
            'fetched_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this information was retrieved from remote URI',
            ),
            'publishedrefname' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'The published reference name that this information is shared under',
            ),
            'project_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The common project name; generally the name of the root goal for locally tracked projects',
            ),
            'project_contextid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary project context at time of import',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The id of the project instance to which this published information applies',
            ),
            'mission_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'Description of the project at time of publication',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person at time of publication',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'The date when we plan to start this project at time of publication',
            ),
            'actual_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'The date when we actually started this project at time of publication',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'The date when we plan to complete this project at time of publication',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'The date when we actually completed this project at time of publication',
            ),
            'onbudget_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'Computed probability in range [0,1] of on-budget success at time of publication',
            ),
            'ontime_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'default' => NULL,
              'description' => 'Computed probability in range [0,1] of on-time success at time of publication',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment about this status',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'Current project status at time of publication',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this record is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array(
                 'byrefname'=>array('publishedrefname','projectid','updated_dt','id'),
                 'byprojectid'=>array('projectid','updated_dt','id'),
              ),
        );

        $schema['bigfathom_remote_uri_scheme_whitelist'] = array(
          'description' => 'Allowed remote uri scheme (e.g., https, http, file)',
          'fields' => array(
            'remote_uri_scheme' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'Domain of the remote_uri',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('remote_uri_scheme'),
        );
        
        $schema['bigfathom_remote_uri_domain_whitelist'] = array(
          'description' => 'Allowed remote uri',
          'fields' => array(
            'remote_uri_domain' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'Domain of the remote_uri',
            ),
            'allow_pull_proj_status_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then pulling project status from this domain is okay, else blocked.',
            ),
            'allow_pull_proj_detail_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then pulling project detail from this domain is okay, else blocked.',
            ),
            'allow_pull_proj_template_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then pulling project template from this domain is okay, else blocked.',
            ),
            'allow_pull_proj_namelist_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then pulling project namelist from this domain is okay, else blocked.',
            ),
            'allow_pull_proj_template_namelist_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 1 then pulling project template namelist from this domain is okay, else blocked.',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('remote_uri_domain'),
        );

        $schema['bigfathom_remote_uri_domain_blacklist'] = array(
          'description' => 'Disallowed remote uri',
          'fields' => array(
            'remote_uri_domain' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'Domain of the remote_uri',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('remote_uri_domain'),
        );

        $schema['bigfathom_workitem_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to workitem data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The workitem identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('workitemid','id')),
        );
        
        $schema['bigfathom_workitem_communication_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to workitem communication data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The workitem identifier',
            ),
            'commid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The workitem communication record identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('workitemid','id')),
        );
        
        $schema['bigfathom_testcase_communication_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to testcase communication data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The testcase identifier',
            ),
            'commid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The testcase communication record identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('testcaseid','id')),
        );
        
        $schema['bigfathom_usecase_communication_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to usecase communication data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'usecaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The usecase identifier',
            ),
            'commid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The usecase communication record identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('usecaseid','id')),
        );
        
        $schema['bigfathom_project_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to project data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('projectid','id')),
        );

        $schema['bigfathom_group_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to group data; this table should be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The group identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'metadata_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a metadata change',
            ),
            'membership_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a membership change',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the group data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('groupid','id')),
        );

        $schema['bigfathom_sprint_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to sprint data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The sprint identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'metadata_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a metadata change',
            ),
            'membership_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a membership change',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the sprint data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('sprintid','id')),
        );

        $schema['bigfathom_sprint_communication_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to sprint communications data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The sprint identifier of the affected sprint',
            ),
            'commid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The communication identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('sprintid','id')),
        );
        
        $schema['bigfathom_testcase_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to testcase data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The testcase identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'metadata_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a metadata change',
            ),
            'step_widlist_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a workitem list change',
            ),
            'step_content_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a step information change',
            ),
            'step_status_updated_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then the update was a step status change',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the testcase data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('testcaseid','id')),
        );

        $schema['bigfathom_project_recent_selection_by_user'] = array(
          'description' => 'Indicate most recent user project selections',
          'fields' => array(
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project identifier',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'selected_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who selected the project',
            ),
            'selected_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project was selected',
            ),
            'most_recent_read_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When project data was most recently examined',
            ),
            'most_recent_edit_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the project data was updated',
            ),
          ),
          'indexes' => array(
               'mostrecent_selection'=>array('projectid','selected_by_uid','selected_dt','sessionid')
              ,'mostrecent_read'=>array('projectid','selected_by_uid','most_recent_read_dt','sessionid')
              ,'mostrecent_edit'=>array('projectid','selected_by_uid','most_recent_edit_dt','sessionid')
              ,'mostrecent_session'=>array('projectid','selected_by_uid','sessionid','selected_dt')
          ),
        );
        
        $schema['bigfathom_brainstorm_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to project brainstorm data; this table will be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project identifier',
            ),
            'brainstormid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The brainstorm identifier of the changed record',
            ),
            'sessionid' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => TRUE,
              'description' => 'Each user session has a unique session identifier',
            ),
            'changed_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Who changed the data',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'An optional comment about the change',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the project data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('projectid','id')),
        );
        
        $schema['bigfathom_workitem'] = array(
          'description' => 'A workitem definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project that owns this workitem',
            ),
            'workitem_basetype' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'G=Goal, T=Task',
            ),
            'workitem_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The workitem name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this workitem',
            ),
            'chargecode' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'Optional chargecode for financial tracking',
            ),
            'client_deliverable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this workitem represents a client deliverable',
            ),
            'externally_billable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this workitem represents effort that is externally billable',
            ),
            'external_resourceid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the external resource that will execute this workitem; only for tasks',
            ),
            'equipmentid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the equipment that will execute this workitem; only for tasks',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'planned_fte_count' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'default' => 1,
              'description' => 'Expected number of full time equivalents that will work on this item',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to start working on this goal',
            ),
            'planned_start_dt_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'actual_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually started working on this goal',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The planned date after which the goal is considered late',
            ),
            'planned_end_dt_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The actual date after which the goal is considered late',
            ),
            'self_allow_dep_overlap_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain number of hours this workitem can overlap its dependent(s)',
            ),
            'self_allow_dep_overlap_pct' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain percent of its effort this workitem can overlap its dependent(s)',
            ),
            'ant_sequence_allow_overlap_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain number of hours an ant workitem can overlap its dependent',
            ),
            'ant_sequence_allow_overlap_pct' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain percent of its effort a workitem can overlap its dependent',
            ),
            'limit_branch_effort_hours_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => TRUE,
              'default' => 'I',  
              'description' => 'U=we loosly apply the constraint (Unlocked), L=we rigidly apply the contraint (Locked), I=ignore the constraint value',
            ),              
            'branch_effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem and any antecedent workitems leading up to it',
            ),
            'branch_effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'effort_hours_worked_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours worked so far to complete this workitem',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours worked so far to complete this workitem',
            ),
            'remaining_effort_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated remaining hours of effort to complete this workitem',
            ),
            'ignore_branch_cost_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => '1=we ignore the contraint, 0=respect the constraint',
            ),
            'branch_cost_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated budget to complete this workitem and any antecedent workitems leading up to it',
            ),
            'branch_cost_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Branch cost estimate confidence probability [0,1]',
            ),
            'branch_cost_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'cost_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated cost of effort to complete this workitem',
            ),
            'cost_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Cost estimate confidence probability [0,1]',
            ),
            'cost_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual moneys of effort to complete this workitem',
            ),
            'cost_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this workitem',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the status was last updated',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-budget success probability value',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-time success probability value',
            ),
            'tester_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person that owns testing for this workitem',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this workitem is not available for new uses',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person updating this record',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('byproject'=>array('owner_projectid','workitem_basetype','id')),
        );
        
        $schema['bigfathom_brainstorm_item'] = array(
          'description' => 'An uncategorized topic item from a brainstorming session',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the item',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project to which this brainstorming content applies',
            ),
            'context_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Simply for grouping content of a brainstorming session',
            ),
            'item_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The item name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this item',
            ),
            'candidate_type' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'description' => 'G=Goal, T=Task',
            ),
            'parkinglot_level' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If greater than 0 then this item is put aside for future discussion',
            ),
            'into_parkinglot_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'The date when this was placed into a parkinglot',
            ),
            'into_trash_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'The date when this was placed into the trash',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this item is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to start working on this item',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The planned date after which the goal is considered late',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The actual date after which the goal is considered late',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this goal',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          //'unique keys' => array('maintext'=>array('context_nm','item_nm')),
        );
    }

    private static function addHistoryTables(&$schema)
    {
        $schema['bigfathom_visionstatement_history'] = array(
          'description' => 'The replaced vision statement records',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of this history record',
            ),
            'originalid' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the original vision statement',
            ),
            'statement_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'A short name for the vision statement',
            ),
            'statement_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => TRUE,
              'description' => 'The vision statement detail',
            ),
            'references_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Relevant references in support of ideas in the vision statement',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 0 then this statement is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the primary statement owner',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was created',
            ),
            'history_comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Short comment about how this record was created, if any',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );

        $schema['bigfathom_map_person2availability_history'] = array(
          'description' => 'Map a person to an availability declaration within a set period of time',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'originalid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period starts',
            ),
            'end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => TRUE,
              'description' => 'When this declaration period ends',
            ),
            'type_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'B=Baseline, V=Vacation period, P=Part-time period, T=Travel period, C=Crunch period, D=Disability period, O=Other period type',
            ),
            'hours_per_day' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 2,
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Use zero if person is taking time off during the period',
            ),
            'work_saturday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_sunday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_monday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_tuesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_wednesday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_thursday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_friday_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on this day.  Ignored if hours is ZERO.',
            ),
            'work_holidays_yn' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'If 1, then working on holidays too.  Ignored if hours is ZERO.',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'A comment about this availability period',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person saving this record',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was created',
            ),
            'history_comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Short comment about how this record was created, if any',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
         'primary key' => array('id'),
        );
        
        $schema['bigfathom_project_baseline'] = array(
          'description' => 'A named project baseline',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the item',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the project that this record baselines',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'A unique short name for this baseline',
            ),
            'comment_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'A comment about this baseline',
            ),
            'mark_deleted_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'If 1 then this baseline has been deleted from the user perspective',
            ),
            'mark_deleted_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this baseline was marked for deletion',
            ),
            'updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person saving this record',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('projectid','shortname')),
        );
        
        $schema['bigfathom_workitem_history'] = array(
          'description' => 'A workitem history record',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the original workitem',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the project that owns this workitem',
            ),
            'workitem_basetype' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'description' => 'G=Goal, T=Task',
            ),
            'workitem_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => FALSE,
              'description' => 'The workitem name',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'description' => 'Static text describing purpose of this workitem',
            ),
            'chargecode' => array(
              'type' => 'varchar',
              'length' => 100,
              'not null' => FALSE,
              'description' => 'Optional chargecode for financial tracking',
            ),
            'client_deliverable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => 'If 1 then this workitem represents a client deliverable',
            ),
            'externally_billable_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => 'If 1 then this workitem represents effort that is externally billable',
            ),
            'external_resourceid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the external resource that will execute this workitem; only for tasks',
            ),
            'equipmentid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the equipment that will execute this workitem; only for tasks',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'planned_fte_count' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'default' => 1,
              'description' => 'Expected number of full time equivalents that will work on this item',
            ),
            'planned_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we plan to start working on this goal',
            ),
            'planned_start_dt_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'actual_start_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The date when we actually started working on this goal',
            ),
            'planned_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The planned date after which the goal is considered late',
            ),
            'planned_end_dt_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'actual_end_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'date',  
              'not null' => FALSE,
              'description' => 'The actual date after which the goal is considered late',
            ),
            'self_allow_dep_overlap_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain number of hours this workitem can overlap its dependent(s)',
            ),
            'self_allow_dep_overlap_pct' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain percent of its effort this workitem can overlap its dependent(s)',
            ),
            'ant_sequence_allow_overlap_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain number of hours an ant workitem can overlap its dependent',
            ),
            'ant_sequence_allow_overlap_pct' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 4,
              'scale' => 2,
              'description' => 'Constrain percent of its effort a workitem can overlap its dependent',
            ),
            'limit_branch_effort_hours_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'default' => 'I',  
              'description' => 'U=we loosly apply the constraint (Unlocked), L=we rigidly apply the contraint (Locked), I=ignore the constraint value',
            ),              
            'branch_effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem and any antecedent workitems leading up to it',
            ),
            'branch_effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'effort_hours_worked_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours worked so far to complete this workitem',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours worked so far to complete this workitem',
            ),
            'remaining_effort_hours' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated remaining hours of effort to complete this workitem',
            ),
            'ignore_branch_cost_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 1,
              'description' => '1=we ignore the contraint, 0=respect the constraint',
            ),
            'branch_cost_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated budget to complete this workitem and any antecedent workitems leading up to it',
            ),
            'branch_cost_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Branch cost estimate confidence probability [0,1]',
            ),
            'branch_cost_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'cost_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated cost of effort to complete this workitem',
            ),
            'cost_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Cost estimate confidence probability [0,1]',
            ),
            'cost_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual moneys of effort to complete this workitem',
            ),
            'cost_est_locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=does not allow changes, 0=allows changes',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this workitem',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the status was last updated',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-budget success probability value',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .01,
              'precision' => 10,
              'scale' => 9,
              'not null' => FALSE,
              'description' => 'Uncertainty in range [0,1] of the on-time success probability value',
            ),
            'tester_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person that owns testing for this workitem',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 1,
              'description' => 'If 0 then this workitem is not available for new uses',
            ),
            'original_updated_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person who last updated this record',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was updated',
            ),
            'original_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person who created this record',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'project_baselineid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The baseline, if any, to which this record belongs',
            ),
            'history_comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Short comment about how this record was created, if any',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('byproject'=>array('owner_projectid','workitem_basetype','id')),
        );

        $schema['bigfathom_map_wi2wi_history'] = array(
          'description' => 'A workitem relationship history record',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the wi2wi history record',
            ),
            'depwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the dependent workitem',
            ),
            'antwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecedent workitem',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 55,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] of contributing to the overall on-time success',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'original_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'project_baselineid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The baseline, if any, to which this record belongs',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
            'history_created_cd' => array(
              'type' => 'char',
              'length' => 1,
              'not null' => TRUE,
              'description' => 'A=Added relationship, D=Deleted relationship, B=Baseline existing relationship',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array(
              'byprojectbaseline'=>array('project_baselineid'),
              ),
        );

        $schema['bigfathom_testcase_history'] = array(
          'description' => 'A test case history record',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case',
            ),
            'originalid' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the original test case',
            ),
            'owner_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the project that owns this test case',
            ),
            'testcase_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => FALSE,
              'description' => 'A short name for the test case statement',
            ),
            'blurb_tx' => array(
              'type' => 'varchar',
              'length' => 256,
              'not null' => FALSE,
              'description' => 'The short introduction blurb',
            ),
            'perspective_cd' => array(
              'type' => 'varchar',
              'length' => 1,
              'not null' => FALSE,
              'description' => 'U=External user, T=Technical user',
            ),
            'precondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'The state of the system at the start of this test case',
            ),
            'postcondition_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'The state of the system at the end of this test case',
            ),
            'references_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Relevant references in support of ideas in the test case',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => 'Current status of this test case',
            ),
            'status_set_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the status was last updated',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 1,
              'description' => 'If 0 then this test case is not available for new uses',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the primary test case owner',
            ),
            'effort_tracking_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the workitem where we track the effort of this test case execution',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this record was created',
            ),
            'history_comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Short comment about how this record was created, if any',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this history record was created',
            ),
          ),
          'primary key' => array('id'),
        );
        
        $schema["bigfathom_testcasestep_history"] = array(
          'description' => "A test case step history record",
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => "Unique numeric ID of this history record",
            ),
            'originalid' => array(
              'type' => 'int',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the original test case step record',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => "Unique numeric ID of the test case owning this step",
            ),
            'step_num' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'First step should be number 1',
            ),
            'instruction_tx' => array(
              'type' => 'varchar',
              'length' => 2048,
              'not null' => FALSE,
              'description' => 'Describe the step exection detail',
            ),
            'status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => FALSE,
              'description' => "Status from testing the step",
            ),
            'executed_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When this item was tested',
            ),
            'original_updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the original record was updated',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => FALSE,
              'description' => 'When the original record was created',
            ),
            'history_comment_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'Short comment about how this record was created, if any',
            ),
            'history_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person creating this history record',
            ),
            'history_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );
        
    }
    
    private static function addMetadataTables(&$schema)
    {
        $schema['bigfathom_project_context'] = array(
          'description' => 'A context for a project',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the context',
            ),
            'shortname' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The context name',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text overview blurb for this context',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this context is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('shortname'=>array('shortname')),
        );
        
        $schema['bigfathom_workitem_status'] = array(
          'description' => 'Metadata of workitem status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'workstarted_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then this state implies work has started',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'needstesting_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 0 then this is not requiring testing, 1 means needs testing, null means no opinion',
            ),
            'default_ignore_effort_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1 then work effort should NOT be factored into formal estimates and insights by default',
            ),
            'soft_delete_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1 then work has essentially been deleted, do not factor it into any computations',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display this status relative to others',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_sprint_status'] = array(
          'description' => 'Metadata of sprint status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'workstarted_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then this state implies work has started',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'locked_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'default' => 0,
              'description' => '1=status does not allow changes to goals of the sprint, 0=allows membership changes',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these statuses',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );

        $schema['bigfathom_action_status'] = array(
          'description' => 'Metadata of action status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'workstarted_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then this state implies work has started',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these statuses',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_usecase_status'] = array(
          'description' => 'Metadata of usecase status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'workstarted_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'If 1 then this state implies work has started',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'soft_delete_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1 then work has essentially been deleted, do not factor it into any computations',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these statuses',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_testcase_status'] = array(
          'description' => 'Metadata of test case status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'soft_delete_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1 then work has essentially been deleted, do not factor it into any computations',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these statuses',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_testcasestep_status'] = array(
          'description' => 'Metadata of testcase step status codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this status item',
            ),
            'terminal_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this is not a terminal workflow state',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'soft_delete_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'If 1 then work has essentially been deleted, do not factor it into any computations',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these statuses',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_tag'] = array(
          'description' => 'A simple text tag dictionary',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the tag',
            ),
            'parent_tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => FALSE,
              'description' => 'The parent tag if any',
            ),
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'default' => '',
              'description' => 'Text for tagging content',
            ),
            'purpose_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text describing purpose of this tag',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person that owns this tag',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this role is not available for new uses',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array('maintext'=>array('tag_tx')),
        );
        
        $schema['bigfathom_equipment_condition'] = array(
          'description' => 'Metadata of equipment condition codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this condition item',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these conditiones',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
        
        $schema['bigfathom_external_resource_condition'] = array(
          'description' => 'Metadata of external resource condition codes',
          'fields' => array(
            'code' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => 'The unique code',
            ),
            'title_tx' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'Text title',
            ),
            'description_tx' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => TRUE,
              'description' => 'Description of this condition item',
            ),
            'happy_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => '1=happy state, 0=unhappy state, null=no inference of emotional state',
            ),
            'sort_position' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => 'Natural order in which to display these conditiones',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'obsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('code'),
        );
    }
    
    /**
     * The system roles are hard-wired behavior of the application
     */
    private static function addSystemRoleMapTables(&$schema)
    {
        $schema['bigfathom_map_person2systemrole'] = array(
          'description' => 'Map a person to a system role',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'systemroleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the system role',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','systemroleid'),
        );
        
        $schema['bigfathom_map_person2systemrole_in_group'] = array(
          'description' => 'Map a person to a system role within a group',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'systemroleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the system role',
            ),
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','systemroleid','groupid'),
        );

        $schema['bigfathom_map_person2systemrole_in_workitem'] = array(
          'description' => 'Map a person to a system role within a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'systemroleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the system role',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','systemroleid','workitemid'),
        );
    }
    
    private static function addTagMapTables(&$schema)
    {
        $schema['bigfathom_map_tag2sprint'] = array(
          'description' => 'Map a tag to a sprint',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','sprintid'),
        );

        $schema['bigfathom_map_tag2usecase'] = array(
          'description' => 'Map a tag to a usecase',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'usecaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the use case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','usecaseid'),
        );
        
        $schema['bigfathom_map_tag2testcase'] = array(
          'description' => 'Map a tag to a testcase',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','testcaseid'),
        );
        
        $schema['bigfathom_map_tag2project'] = array(
          'description' => 'Map a tag to a project',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','projectid'),
        );

        $schema['bigfathom_map_tag2workitem'] = array(
          'description' => 'Map a tag to a workitem',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the goal',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','tag_tx'),
          'indexes' => array(
              'tags'=>array('tag_tx'),
              'recent'=>array('created_dt'),
              ),
        );
        
        $schema['bigfathom_map_tag2role'] = array(
          'description' => 'Map a tag to a role',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','roleid'),
        );
        
        $schema['bigfathom_map_tag2group'] = array(
          'description' => 'Map a tag to a group',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('tag_tx','groupid'),
        );
    }
    
    private static function addTopProjectMapTables(&$schema)
    {
        $schema['bigfathom_map_workitem2sprint'] = array(
          'description' => 'Map a workitem to a sprint',
          'fields' => array(
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','sprintid'),
        );
        
        $schema['bigfathom_map_workitem2usecase'] = array(
          'description' => 'Map a workitem to a use case',
          'fields' => array(
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'usecaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the use case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','usecaseid'),
        );
        
        $schema['bigfathom_map_workitem2testcase'] = array(
          'description' => 'Map a workitem to a test case',
          'fields' => array(
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'testcaseid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','testcaseid'),
        );
        
        $schema['bigfathom_map_workitem2testcasestep'] = array(
          'description' => 'Map a workitem to a test case',
          'fields' => array(
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'testcasestepid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the test case step',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','testcasestepid'),
        );
        
        $schema['bigfathom_map_person2role'] = array(
          'description' => 'Map a person to a project role',
          'fields' => array(
            'personid' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important role to the person',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'success_boost_factor' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 1,
              'precision' => 10,
              'scale' => 8,
              'not null' => TRUE,
              'description' => 'A multiplier in range [1,infinite] used boost the success confidence factor (e.g., influence)',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid'),
        );

        $schema['bigfathom_map_project2portfolio'] = array(
          'description' => 'Map a project to a portfolio',
          'fields' => array(
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'portfolioid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the portfolio',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('portfolioid','projectid'),
        );
        
        $schema['bigfathom_map_group2wi'] = array(
          'description' => 'Map a group to a workitem',
          'fields' => array(
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('groupid','workitemid'),
        );
        
        $schema['bigfathom_map_group2project'] = array(
          'description' => 'Map a group to a project',
          'fields' => array(
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('groupid','projectid'),
        );

        $schema['bigfathom_map_role2wi'] = array(
          'description' => 'Declare project roles as particularly relevant to a workitem',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project role',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'expected_cardinality' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'Number of people expected in this role',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('roleid','workitemid'),
        );
        
        $schema['bigfathom_map_role2project'] = array(
          'description' => 'Declare roles as particularly relevant to a project',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project role',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('roleid','projectid'),
        );

        $schema['bigfathom_map_subproject2project'] = array(
          'description' => 'Declare projects as antecedent projects to another project',
          'fields' => array(
            'subprojectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecedent project',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('subprojectid','projectid'),
        );

        $schema['bigfathom_map_visionstatement2project'] = array(
          'description' => 'Declare which projects are in alignment with which vision statements',
          'fields' => array(
            'visionstatementid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecedent project',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('visionstatementid','projectid'),
        );
        
        $schema['bigfathom_map_publishedrefname2project'] = array(
          'description' => 'Map an externally shareable reference name to a project instance',
          'fields' => array(
            'publishedrefname' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'The published reference name',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'remote_uri' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'The URI from which latest status information for this project can be retrieved',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('publishedrefname'),
          'unique keys' => array(
              'byprojectid'=>array('projectid'),
              ),          
        );

        $schema['bigfathom_map_role2sprint'] = array(
          'description' => 'Declare roles as particularly relevant to a sprint',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint role',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('roleid','sprintid'),
          'unique keys' => array(
              'bysprintid'=>array('sprintid','roleid'),
              ),          
          'indexes' => array(
              'recent'=>array('created_dt'),
              ),
        );

        $schema['bigfathom_map_wi2wi'] = array(
          'description' => 'Map workitem as antecedent of workitem',
          'fields' => array(
            'depwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the dependent workitem',
            ),
            'antwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecedent workitem',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 55,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] of contributing to the overall on-time success',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('depwiid','antwiid'),
          'unique keys' => array(
              'byantwiid'=>array('antwiid','depwiid'),
              ),          
          'indexes' => array(
              'recent'=>array('created_dt'),
              ),
        );
        
        $schema['bigfathom_map_brainstormid2wid'] = array(
          'description' => 'Map the original brainstormid to the new workitemid after conversion',
          'fields' => array(
            'brainstormid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the original brainstorm topic',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the new workitem',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('brainstormid'),
          'indexes' => array(
              'recent'=>array('created_dt'),
              ),
        );
        
        $schema['bigfathom_map_external_role2ours'] = array(
          'description' => 'Map some externally appearing role names to the roles we choose to use in our local application instance',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'external_role_nm' => array(
              'type' => 'varchar',
              'length' => 40,
              'not null' => TRUE,
              'description' => 'The role name in the external application',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role in our application instance',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array(
              'themap'=>array('external_role_nm','roleid'),
              ),          
          'indexes' => array(
              'recent'=>array('created_dt'),
              ),
        );

        $schema['bigfathom_map_external_status_cd2ours'] = array(
          'description' => 'Map some externally appearing status codes to the codes we choose to use in our local application instance',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'external_status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => "External sourced status code",
            ),
            'our_status_cd' => array(
              'type' => 'varchar',
              'length' => 5,
              'not null' => TRUE,
              'description' => "Our status code",
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
          'unique keys' => array(
              'themap'=>array('external_status_cd','our_status_cd'),
              ),          
          'indexes' => array(
              'recent'=>array('created_dt'),
              ),
        );
        
    }
    
    private static function addSubMapTables(&$schema)
    {
        
        $schema['bigfathom_general_person_insight2wi'] = array(
          'description' => 'General insight of a person to a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0=no insight, 1=least insight, 100=most insight',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_general_person_influence2wi'] = array(
          'description' => 'General influence of a person to a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least influence, 100 most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_general_person_importance2wi'] = array(
          'description' => 'Capture general importance assessment of a workitem by a person',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0=no importance, 1=least important, 100=most important',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_map_person2pcg_in_project'] = array(
          'description' => 'Map a person to the privileged collaborator group of a project',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','projectid'),
        );

        $schema['bigfathom_map_person2role_in_group'] = array(
          'description' => 'Map a person to a role within a group',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important role to the person',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'success_boost_factor' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 1,
              'precision' => 10,
              'scale' => 8,
              'not null' => TRUE,
              'description' => 'A multiplier in range [1,infinite] used boost the success confidence factor (e.g., influence)',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid','groupid'),
        );

        $schema['bigfathom_map_person2role_in_workitem'] = array(
          'description' => 'Map a person to a role within a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours of effort to complete this workitem',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least insight, 100 most insight',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least influence, 100 most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'success_boost_factor' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 1,
              'precision' => 10,
              'scale' => 8,
              'not null' => TRUE,
              'description' => 'A multiplier in range [1,infinite] used boost the success confidence factor (e.g., influence)',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid','workitemid'),
        );

        $schema['bigfathom_map_person2role_in_sprint'] = array(
          'description' => 'Map a person to a role within a sprint',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this sprint',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours of effort to complete this sprint',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least insight, 100 most insight',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least influence, 100 most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'success_boost_factor' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 1,
              'precision' => 10,
              'scale' => 8,
              'not null' => TRUE,
              'description' => 'A multiplier in range [1,infinite] used boost the success confidence factor (e.g., influence)',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid','sprintid'),
        );
        
    }
    
    private static function addConfidenceLookupTableSchema(&$schema,$context_name)
    {
        $context_name = strtolower($context_name);
        $schema["bigfathom_lookup_confidence_factor_{$context_name}_remaining_days"] = array(
          'description' => "Get confidence factor based on remaining days and known effort for a {$context_name}",
          'fields' => array(
            'forecast_approachid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The forecasting approach this record belongs to',
            ),
            'buffer_days' => array(
              'type' => 'int',
              'unsigned' => FALSE,
              'not null' => TRUE,
              'description' => 'Number of days until deadline after factoring in effort',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('forecast_approachid', 'buffer_days'),
        );
        $schema["bigfathom_lookup_confidence_factor_{$context_name}_remaining_days_history"] = array(
          'description' => "Historical confidence factor based on remaining days and known effort for a {$context_name}",
          'fields' => array(
            'forecast_approachid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The forecasting approach this record belongs to',
            ),
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the uploaded file',
            ),
            'buffer_days' => array(
              'type' => 'int',
              'unsigned' => FALSE,
              'not null' => TRUE,
              'description' => 'Number of days until deadline after factoring in effort',
            ),
            'ot_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-time success probability',
            ),
            'ob_scf' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => .95,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'A probability in range [0,1] contributing to the overall on-budget success probability',
            ),
            'original_created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person who created the original record',
            ),
            'original_created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was created',
            ),
            'replaced_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person who replaced the original record',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('id'),
        );
    }

    /**
     * These tables enable project members to forecast outcomes
     * in an incremental manner allowing for Brier score evaluation later.
     */
    private static function addForecastTables(&$schema)
    {
        self::addConfidenceLookupTableSchema($schema,'goal');
        self::addConfidenceLookupTableSchema($schema,'task');
        self::addConfidenceLookupTableSchema($schema,'sprint');

        $schema['bigfathom_forecast_workitemsuccess'] = array(
          'description' => 'Forecast probability of workitem successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid','created_dt'),
        );

        $schema['bigfathom_forecast_workitemsuccess_replaced'] = array(
          'description' => 'Replaced forecast probability of workitem successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'indexes' => array('main'=>
                array('personid','workitemid','created_by_personid','created_dt')
              ),
        );

        /* DEPRECATED
        $schema['bigfathom_forecast_goalsuccess'] = array(
          'description' => 'Forecast probability of goal successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'goalid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the goal',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','goalid','created_by_personid','created_dt'),
        );

        $schema['bigfathom_forecast_goalsuccess_replaced'] = array(
          'description' => 'Replaced forecast probability of goal successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'goalid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the goal',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'indexes' => array('main'=>
                array('personid','goalid','created_by_personid','created_dt')
              ),
        );
        
        $schema['bigfathom_forecast_tasksuccess'] = array(
          'description' => 'Forecast probability of task successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'taskid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the task',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','taskid','created_dt'),
        );

        $schema['bigfathom_forecast_tasksuccess_replaced'] = array(
          'description' => 'Replaced forecast probability of task successful completion',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'taskid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the task',
            ),
            'success_p_early' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] before deadline',
            ),
            'success_p_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] on time',
            ),
            'success_u_ontime' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Uncertainty of success [0,1] on time',
            ),
            'success_p_notmorethan7dayslate' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] not more than 7 days late',
            ),
            'success_p_someday' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Probability of success [0,1] ever happening',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the original record was created',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'indexes' => array('main'=>
                array('personid','taskid','created_dt')
              ),
        );
         * 
         */
    }
    
    /**
     * These tables enable project members to suggest mappings
     */
    private static function addSuggestionTables(&$schema)
    {
        
        $schema['bigfathom_suggest_person_insight2wi'] = array(
          'description' => 'Suggest general insight of a person to a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0=no insight,1=least insight, 100=most insight',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        //DEPRECATE bigfathom_suggest_person_influence2wi!!!!!!!!!!!!!!!!!
        $schema['bigfathom_suggest_person_influence2wi'] = array(
          'description' => 'Suggest general influence of a person to a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0=no influence,1=least influence, 100=most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_person_importance2wi'] = array(
          'description' => 'Capture general importance assessment of a workitem by a person',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0=no importance,1=least important, 100=most important',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_person2own_workitem'] = array(
          'description' => 'Suggest person to own a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_map_person2role_in_workitem'] = array(
          'description' => 'Suggest map of a person to a workitem',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this workitem',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours of effort to complete this goal',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least insight, 100 most insight',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least influence, 100 most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid','workitemid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_map_person2role_in_sprint'] = array(
          'description' => 'Suggest map of a person to a sprint',
          'fields' => array(
            'personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person',
            ),
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the role',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'effort_hours_est' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Estimated hours of effort to complete this sprint',
            ),
            'effort_hours_est_p' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 5,
              'scale' => 4,
              'description' => 'Estimate confidence probability [0,1]',
            ),
            'effort_hours_worked_act' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'precision' => 10,
              'scale' => 2,
              'description' => 'Actual hours of effort to complete this sprint',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'insight' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least insight, 100 most insight',
            ),
            'influence' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'General scale is 0 least influence, 100 most influence',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('personid','roleid','sprintid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_map_workitem2sprint'] = array(
          'description' => 'Suggest map of a workitem to a sprint',
          'fields' => array(
            'workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem',
            ),
            'sprintid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the sprint',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important role to the person',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('workitemid','sprintid','created_by_personid'),
        );
        
        $schema['bigfathom_suggest_map_wi2wi'] = array(
          'description' => 'Suggest map of a parent-goal to a sub-goal',
          'fields' => array(
            'depwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the dependent goal',
            ),
            'antwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecede4nt goal',
            ),
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important role to the person',
            ),
            'created_by_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the person creating this record',
            ),
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was updated',
            ),
            'created_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was created',
            ),
          ),
          'primary key' => array('depwiid','antwiid','created_by_personid'),
        );
    }
    
    
    private static function addAttachmentTables(&$schema)
    {
        $schema['bigfathom_attachment'] = array(
          'description' => 'Uploaded attachment',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the uploaded file',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Filename of uploaded file',
            ),
            'filetype' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'The uppercase file extension of the file',
            ),
            'file_blob' => array(
              'type' => 'blob',
              'mysql_type' => 'mediumblob',
              'not null' => TRUE,
              'description' => 'The uploaded file as a blob',
            ),
            'filesize' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Size of the file in bytes',
            ),
            'uploaded_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'body_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment about this file',
            ),
            'uploaded_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
          ),
          'primary key' => array('id'),
        );
        
        $schema['bigfathom_attachment_history'] = array(
          'description' => 'Uploaded attachment that has been replaced',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of this record',
            ),
            'original_attachmentid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The ID of the original attachment record',
            ),
            'filename' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => FALSE,
              'description' => 'Original name of uploaded file',
            ),
            'filetype' => array(
              'type' => 'varchar',
              'length' => 8,
              'not null' => TRUE,
              'description' => 'The uppercase file extension of the file',
            ),
            'file_blob' => array(
              'type' => 'blob',
              'mysql_type' => 'mediumblob',
              'not null' => TRUE,
              'description' => 'The uploaded file as a blob',
            ),
            'filesize' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Size of the file in bytes',
            ),
            'uploaded_by_uid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Who uploaded the file',
            ),
            'body_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => FALSE,
              'default' => '',
              'description' => 'Comment about this file',
            ),
            'uploaded_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was last updated',
            ),
            'replaced_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When this record was replaced',
            ),
          ),
          'primary key' => array('id'),
        );
    }
}
    