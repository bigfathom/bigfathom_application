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

namespace bigfathom_template_library;   //Each module has its own namespace for schema

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
        $schema['bigfathom_template_project_library'] = array(
          'description' => 'A project template definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the template',
            ),
            'template_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => TRUE,
              'description' => 'Static text name of this template',
            ),
            'submitter_blurb_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text general overview blurb from the submitter of this template',
            ),
            'mission_tx' => array(
              'type' => 'varchar',
              'length' => 1024,
              'not null' => TRUE,
              'description' => 'Static text mission blurb for this template',
            ),
            'project_contextid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The primary project context',
            ),
            'root_template_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'The root template workitem of this project template',
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

            'template_author_nm' => array(
              'type' => 'varchar',
              'length' => 80,
              'not null' => FALSE,
              'description' => 'This has the raw text name of the person identified as the author, if any',
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
            'importance' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 70,
              'description' => 'General scale is 0 least important, 100 most important',
            ),
            'owner_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the owning person',
            ),
            'show_cd' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 0,
              'description' => '0=all members can see the template, 1=only privileged collaborators can see the template, 2=only owner can see the template, 3=only primary owner can see the template',
            ),
            'active_yn' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'default' => 1,
              'description' => 'If 0 then this template is not available for new uses',
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
          //'unique keys' => array('rootgoal'=>array('root_goalid')),
        );
        
        $schema['bigfathom_template_workitem'] = array(
          'description' => 'A project template workitem definition',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the template workitem',
            ),
            'owner_template_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project template that owns this workitem',
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
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the owning person if the template has one',
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
              'description' => 'Current status of this workitem template',
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
            'otsu' => array(
              'type' => 'numeric',
              'size' => 'normal',
              'default' => 0.01,
              'precision' => 10,
              'scale' => 9,
              'not null' => TRUE,
              'description' => 'Uncertainty in range [0,1] applied to the on-time success probability',
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
            'tester_personid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => FALSE,
              'description' => 'Unique numeric ID of the person that owns testing for this workitem if there is one',
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
          'unique keys' => array('bytemplate'=>array('owner_template_projectid','workitem_basetype','id')),
        );
        
        $schema['bigfathom_map_group2tp'] = array(
          'description' => 'Map a group to a project template',
          'fields' => array(
            'groupid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the group',
            ),
            'template_projectid' => array(
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
          'primary key' => array('groupid','template_projectid'),
        );

        $schema['bigfathom_map_role2tw'] = array(
          'description' => 'Declare project roles as particularly relevant to a workitem template',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project role',
            ),
            'template_workitemid' => array(
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
          'primary key' => array('roleid','template_workitemid'),
        );
        
        $schema['bigfathom_map_role2tp'] = array(
          'description' => 'Declare roles as particularly relevant to a project template',
          'fields' => array(
            'roleid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project role',
            ),
            'template_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project template',
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
          'primary key' => array('roleid','template_projectid'),
        );
        
        $schema['bigfathom_map_tw2tw'] = array(
          'description' => 'Map template workitem as antecedent of template workitem',
          'fields' => array(
            'depwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the dependent workitem template',
            ),
            'antwiid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the antecedent workitem template',
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
        
        $schema['bigfathom_map_publishedrefname2tp'] = array(
          'description' => 'Map an externally shareable reference name to a template project instance',
          'fields' => array(
            'publishedrefname' => array(
              'type' => 'varchar',
              'length' => 128,
              'not null' => TRUE,
              'description' => 'The published reference name',
            ),
            'template_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the template project',
            ),
            'remote_uri' => array(
              'type' => 'varchar',
              'length' => 512,
              'not null' => FALSE,
              'description' => 'The URI from which latest status information for this template project can be retrieved',
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
              'byprojectid'=>array('template_projectid'),
              ),          
        );
        
    }

    private static function addMetadataTables(&$schema)
    {
        $schema['bigfathom_template_project_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to project template data; this table should be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'template_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The project template identifier',
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
          'indexes' => array('mostrecent'=>array('template_projectid','id')),
        );
        
        $schema['bigfathom_template_workitem_recent_data_updates'] = array(
          'description' => 'Indicate recent changes to workitem template data; this table should be periodically trimmed',
          'fields' => array(
            'id' => array(
              'type' => 'serial',
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the record',
            ),
            'template_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'The workitem template identifier',
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
            'updated_dt' => array(
              'type' => 'datetime',
              'mysql_type' => 'datetime',  
              'not null' => TRUE,
              'description' => 'When the workitem data was updated',
            ),
          ),
          'primary key' => array('id'),
          'indexes' => array('mostrecent'=>array('template_workitemid','id')),
        );
    }
    
    private static function addTagMapTables(&$schema)
    {
        $schema['bigfathom_map_tag2tp'] = array(
          'description' => 'Map a tag to a project template',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'template_projectid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the project template',
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
          'primary key' => array('tag_tx','template_projectid'),
          'unique keys' => array('templatetags'=>array('template_projectid','tag_tx')),
        );
        
        $schema['bigfathom_map_tag2tw'] = array(
          'description' => 'Map a tag to a workitem template',
          'fields' => array(
            'tag_tx' => array(
              'type' => 'varchar',
              'length' => 20,
              'not null' => TRUE,
              'description' => 'The tag text',
            ),
            'template_workitemid' => array(
              'type' => 'int',
              'unsigned' => TRUE,
              'not null' => TRUE,
              'description' => 'Unique numeric ID of the workitem template',
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
          'primary key' => array('tag_tx','template_workitemid'),
          'unique keys' => array('templatetags'=>array('template_workitemid','tag_tx')),
        );
    }
}
    