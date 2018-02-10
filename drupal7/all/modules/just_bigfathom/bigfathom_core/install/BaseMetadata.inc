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

/**
 * This class is for creation of database base metadata content
 *
 * @author Frank Font
 */
class BaseMetadata
{
    
    public function __construct()
    {
    }
    
    public function load()
    {
        try
        {
            $this->loadCountryData();
            $this->loadStateData();

            $this->loadStandardDefaultWhitelistData();
            
            $this->loadSystemRoleData();
            $this->loadBaseProjectRoleData();
         
            $this->loadProjectContextData();
            
            $this->loadActionStatusData();
            $this->loadUseCaseStatusData();
            $this->loadWorkitemStatusData();
            $this->loadExternalResourceStatusData();
            $this->loadEquipmentStatusData();
            $this->loadSprintStatusData();
            $this->loadStandardDefaultAvailabilityData();
            
            $this->loadTestCaseStatusData();
            $this->loadTestCaseStepStatusData();
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    

    private function loadStandardDefaultWhitelistData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_remote_uri_scheme_whitelist')->fields(array(
                'remote_uri_scheme' => 'http',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_remote_uri_scheme_whitelist')->fields(array(
                'remote_uri_scheme' => 'https',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'localhost',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => '127.0.0.1',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'bigfathom.com',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'bigfathom.net',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'bigfathom.org',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'theanalysiscloud.com',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'theanalysiscloud.net',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_remote_uri_domain_whitelist')->fields(array(
                'remote_uri_domain' => 'theanalysiscloud.org',
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            drupal_set_message($t('Loaded whitelist data at ' . $updated_dt));
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    

    private function loadStandardDefaultAvailabilityData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_baseline_availability')->fields(array(
                'id' => 1,
                'is_planning_default_yn' => 1,
                'shortname' => 'STANDARD FULL-TIME',
                'hours_per_day' => 8,
                'comment_tx' => 'Standard full-time employment hours',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 1,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();             
            
            db_insert('bigfathom_baseline_availability')->fields(array(
                'id' => 2,
                'is_planning_default_yn' => 0,
                'shortname' => 'STANDARD PART-TIME',
                'hours_per_day' => 4,
                'comment_tx' => 'Standard part-time employment hours',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 1,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();             
    
            drupal_set_message($t('Loaded baseline availability data'));
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
    
    private function loadProjectContextData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 1,
                'shortname' => 'Portfolio',
                'description_tx' => 'This is a project simply created to group together projects over which someone has responsiblity.',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 8,
                'shortname' => 'Physical Product Development',
                'description_tx' => 'This project is primarily about developing a physical product',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 9,
                'shortname' => 'Physical Product Deployment',
                'description_tx' => 'This project is primarily about deploying a physical product',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 10,
                'shortname' => 'Application Development',
                'description_tx' => 'This project is primarily about developing a software application',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 15,
                'shortname' => 'Application Deployment',
                'description_tx' => 'This project is primarily about deploying a software application',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 20,
                'shortname' => 'Service Development',
                'description_tx' => 'This project is primarily about developing a service',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 25,
                'shortname' => 'Service Deployment',
                'description_tx' => 'This project is primarily about deploying a service',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 30,
                'shortname' => 'Remediation',
                'description_tx' => 'This project is primarily about remediating an existing issue',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 40,
                'shortname' => 'Education Curriculum',
                'description_tx' => 'This project is a grouping of several education projects',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 42,
                'shortname' => 'Education Outcome',
                'description_tx' => 'This project is focused on delivering an education outcome',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 44,
                'shortname' => 'Skill Training',
                'description_tx' => 'This project is focused on delivering a trained skill outcome',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 46,
                'shortname' => 'Personal Development',
                'description_tx' => 'This project is focused on a personal improvement outcome',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 48,
                'shortname' => 'Organizational Development',
                'description_tx' => 'This project is focused on an organizational improvement outcome',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 60,
                'shortname' => 'Research Collaboration',
                'description_tx' => 'This project is a collaboration between science researchers in an area of study',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 70,
                'shortname' => 'Medical Intervention',
                'description_tx' => 'This project is a treatment plan',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 80,
                'shortname' => 'Budget Tracking',
                'description_tx' => 'This project is primarily about tracking a budget',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_project_context')->fields(array(
                'id' => 84,
                'shortname' => 'Building Construction',
                'description_tx' => 'This project is primarily about building a physical structure',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 86,
                'shortname' => 'Machine Construction',
                'description_tx' => 'This project is primarily about building a physical machine',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 90,
                'shortname' => 'Event Forecast',
                'description_tx' => 'This project computes the probability of an event occuring',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 104,
                'shortname' => 'Cyber-Security Audit',
                'description_tx' => 'This is a cyber-security audit project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 105,
                'shortname' => 'Cyber-Security Remediation',
                'description_tx' => 'This is a cyber-security remediation project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 111,
                'shortname' => 'Financial Planning',
                'description_tx' => 'This is a financial planning project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 112,
                'shortname' => 'Financial Audit',
                'description_tx' => 'This is a financial audit',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 114,
                'shortname' => 'Tax Planning',
                'description_tx' => 'This is a tax planning project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 115,
                'shortname' => 'Tax Preparation',
                'description_tx' => 'This is a tax preparation project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_project_context')->fields(array(
                'id' => 120,
                'shortname' => 'Requirements Gathering',
                'description_tx' => 'This is a requirements gathering project',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base project context data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadSystemRoleData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 2,
                'role_nm' => 'Group Reader',
                'purpose_tx' => 'This type of person can only read the content of assigned groups',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 4,
                'role_nm' => 'Person Account Reader',
                'purpose_tx' => 'This type of person can read the content of any person account',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 5,
                'role_nm' => 'Group Member',
                'purpose_tx' => 'This person may read and write project data on a group by group basis',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 7,
                'role_nm' => 'Group Admin',
                'purpose_tx' => 'This person may read and write all application data in assigned groups',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 9,
                'role_nm' => 'Project Reader',
                'purpose_tx' => 'View all items in projects',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 1,
                'role_nm' => 'Writer',
                'purpose_tx' => 'Having this role enables an account to write changes.  Without it, a user cannot change anything.',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 88,
                'role_nm' => 'Data Trustee',
                'purpose_tx' => 'This elevated system role allows a user to edit and create application data that is shared across the entire site.',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_systemrole')->fields(array(
                'id' => 99,
                'role_nm' => 'System Admin',
                'purpose_tx' => 'This person may read and write all application data',
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            
            drupal_set_message($t('Loaded base system role data at ') . $updated_dt);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadBaseProjectRoleData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_role')->fields(array(
                'id' => 1,
                'role_nm' => 'Owner',
                'purpose_tx' => 'The owner has ultimate responsiblity for success of what they own',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 2,
                'role_nm' => 'Director',
                'purpose_tx' => 'Ensure that multiple program managers and their teams are working toward success of their projects',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 3,
                'role_nm' => 'Program Manager',
                'purpose_tx' => 'Ensure that multiple project leaders and their teams are working toward success of their projects',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 5,
                'role_nm' => 'Project Leader',
                'purpose_tx' => 'Ensure the project team is working toward success of the project',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 6,
                'role_nm' => 'Sprint Leader',
                'purpose_tx' => 'Ensure the project team is working toward success of the sprint',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 7,
                'role_nm' => 'Workitem Creator',
                'purpose_tx' => 'Can create and own workitems',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 8,
                'role_nm' => 'Workitem Owner',
                'purpose_tx' => 'Can own workitems',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 0,
                'workitemowner_yn' => 1,
                'tester_yn' => 0,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_role')->fields(array(
                'id' => 9,
                'role_nm' => 'Workitem Tester',
                'purpose_tx' => 'Responsible for success audit and testing of workitems',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 21,
                'role_nm' => 'Computer Programmer',
                'purpose_tx' => 'Responsible for creating and maintaining computer programs',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 22,
                'role_nm' => 'Requirements Analyst',
                'purpose_tx' => 'Responsible for creating and maintaining requirement artifacts',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 24,
                'role_nm' => 'Networking Technician',
                'purpose_tx' => 'Responsible for creating and maintaining computer networks and related equipment and products',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 25,
                'role_nm' => 'DBA',
                'purpose_tx' => 'Responsible for creating and supporting database infrastructure',
                'groupleader_yn' => 0,
                'projectleader_yn' => 0,
                'sprintleader_yn' => 0,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 31,
                'role_nm' => 'Tax Analyst',
                'purpose_tx' => 'Responsible for completing and filing taxes',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 32,
                'role_nm' => 'Financial Auditor',
                'purpose_tx' => 'Responsible for auditing business finances',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 34,
                'role_nm' => 'Marketing Specialist',
                'purpose_tx' => 'Responsible for planning advertising and outreach campaigns',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_role')->fields(array(
                'id' => 35,
                'role_nm' => 'Sales Specialist',
                'purpose_tx' => 'Responsible for creating channels to deliver solutions to existing and new customers',
                'groupleader_yn' => 1,
                'projectleader_yn' => 1,
                'sprintleader_yn' => 1,
                'workitemcreator_yn' => 1,
                'workitemowner_yn' => 1,
                'sprintleader_yn' => 0,
                'tester_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base project role data at ') . $updated_dt);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadActionStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_action_status')->fields(array(
                'code' => 'PARTR',
                'workstarted_yn' => 1,
                'terminal_yn' => 0,
                'title_tx' => 'Partial Resolution',
                'description_tx' => 'A part of the action item has been resolved',
                'sort_position' => 5,
                'ot_scf' => 0.85,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_action_status')->fields(array(
                'code' => 'RSVLD',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'happy_yn' => 1,
                'title_tx' => 'Resolved',
                'description_tx' => 'The action item has been resolved',
                'sort_position' => 10,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_action_status')->fields(array(
                'code' => 'CLOSD',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'title_tx' => 'Closed',
                'description_tx' => 'The action item has been closed',
                'sort_position' => 20,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_action_status')->fields(array(
                'code' => 'ABAND',
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'title_tx' => 'Abandoned',
                'description_tx' => 'The action item has been abandoned',
                'sort_position' => 90,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_action_status')->fields(array(
                'code' => 'CNCLD',
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'title_tx' => 'Cancelled',
                'description_tx' => 'The action item has been cancelled',
                'sort_position' => 99,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            
            drupal_set_message($t('Loaded base action status data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadUseCaseStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'B',
                'workstarted_yn' => 0,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Brainstorming',
                'description_tx' => 'The use case is currently under development',
                'sort_position' => 10,
                'ot_scf' => 0.99,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'SREVW',
                'workstarted_yn' => 0,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Ready for Review',
                'description_tx' => 'The use case is under review by stakeholders',
                'sort_position' => 20,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'RFI',
                'happy_yn' => 1,
                'workstarted_yn' => 0,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Ready for Implementation',
                'description_tx' => 'The use case is approved and ready for implementation work to progress',
                'sort_position' => 30,
                'ot_scf' => 0.98,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'SC',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'soft_delete_yn' => 0,
                'title_tx' => 'Successful Completion',
                'description_tx' => 'The use case has been successfuly implemented',
                'sort_position' => 60,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'CWC',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'soft_delete_yn' => 0,
                'title_tx' => 'Completed With Concerns',
                'description_tx' => 'The use case has been implemented with some concerns that are non-fatal',
                'sort_position' => 70,
                'ot_scf' => 0.78,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'RBO',
                'happy_yn' => NULL,
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Replaced By Other use case(s)',
                'description_tx' => 'This use case has been replaced by one or more other use cases',
                'sort_position' => 80,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'ABAND',
                'happy_yn' => NULL,
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Abandoned',
                'description_tx' => 'The use case has been abandoned',
                'sort_position' => 90,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_usecase_status')->fields(array(
                'code' => 'CNCLD',
                'happy_yn' => NULL,
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Cancelled',
                'description_tx' => 'The use case has been cancelled',
                'sort_position' => 99,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base use case status data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadTestCaseStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'B',
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Brainstorming',
                'description_tx' => 'The test case is currently under development',
                'sort_position' => 10,
                'ot_scf' => 0.99,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'R4R',
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Ready for Review',
                'description_tx' => 'The test case is under review by stakeholders',
                'sort_position' => 20,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'FR',
                'happy_yn' => 0,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Failed Review',
                'description_tx' => 'The test case is under review by stakeholders',
                'sort_position' => 30,
                'ot_scf' => 0.50,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'AFU',
                'happy_yn' => 1,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Approved For Use',
                'description_tx' => 'The test case is approved and ready for use to test the work products',
                'sort_position' => 40,
                'ot_scf' => 0.88,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'FT',
                'happy_yn' => 0,
                'terminal_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Failed Testing',
                'description_tx' => 'The test case steps have been executed at least one has failed',
                'sort_position' => 43,
                'ot_scf' => 0.44,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'STC',
                'happy_yn' => 1,
                'terminal_yn' => 1,
                'soft_delete_yn' => 0,
                'title_tx' => 'Successful Test Completion',
                'description_tx' => 'The test case steps have been executed and none failed',
                'sort_position' => 45,
                'ot_scf' => 0.98,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'RBO',
                'happy_yn' => NULL,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Replaced By Other test case(s)',
                'description_tx' => 'This test case has been replaced by one or more other test cases',
                'sort_position' => 80,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'ABAND',
                'happy_yn' => NULL,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Abandoned',
                'description_tx' => 'The test case has been abandoned',
                'sort_position' => 90,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcase_status')->fields(array(
                'code' => 'CNCLD',
                'happy_yn' => NULL,
                'terminal_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Cancelled',
                'description_tx' => 'The test case has been cancelled',
                'sort_position' => 99,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base test case status data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadTestCaseStepStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_testcasestep_status')->fields(array(
                'code' => 'NONE',
                'soft_delete_yn' => 0,
                'title_tx' => 'Not yet executed',
                'description_tx' => 'This step has not yet been executed',
                'sort_position' => 10,
                'ot_scf' => 0.90,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcasestep_status')->fields(array(
                'code' => 'PASS',
                'terminal_yn' => 1,
                'soft_delete_yn' => 0,
                'happy_yn' => 1,
                'title_tx' => 'Successfully completed',
                'description_tx' => 'The step was successfully executed',
                'sort_position' => 20,
                'ot_scf' => 0.99,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_testcasestep_status')->fields(array(
                'code' => 'FAIL',
                'terminal_yn' => 1,
                'happy_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Failed on execution',
                'description_tx' => 'The step was NOT successfully executed',
                'sort_position' => 20,
                'ot_scf' => 0.50,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base test case step status data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadWorkitemStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'B',
                'workstarted_yn' => 0,
                'terminal_yn' => 0,
                'default_ignore_effort_yn' => 1,
                'soft_delete_yn' => 0,
                'title_tx' => 'Brainstorming',
                'description_tx' => 'This is in very preliminary and should not yet be factored effort totals',
                'sort_position' => 99,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'WNS',
                'workstarted_yn' => 0,
                'terminal_yn' => 0,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Work Not Started',
                'description_tx' => 'Work on this workitem has not yet started',
                'sort_position' => 10,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'WS',
                'workstarted_yn' => 1,
                'terminal_yn' => 0,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Work has Started',
                'description_tx' => 'The work on this workitem has started',
                'happy_yn' => 1,
                'sort_position' => 20,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'RT',
                'workstarted_yn' => 1,
                'terminal_yn' => 0,
                'needstesting_yn' => 1,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Ready For Testing',
                'description_tx' => 'This work product is ready for testing',
                'happy_yn' => 1,
                'sort_position' => 35,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'FT',
                'workstarted_yn' => 1,
                'terminal_yn' => 0,
                'needstesting_yn' => NULL,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Failed Testing',
                'description_tx' => 'This work product has failed testing',
                'happy_yn' => 0,
                'sort_position' => 40,
                'ot_scf' => 0.65,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'STC',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'needstesting_yn' => 0,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Successful Test Completion',
                'description_tx' => 'Testing of this work product output has been successfully completed',
                'happy_yn' => 1,
                'sort_position' => 45,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'SC',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'needstesting_yn' => 0,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Successful Completion',
                'description_tx' => 'This workitem has been successfully completed',
                'happy_yn' => 1,
                'sort_position' => 50,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'CWC',
                'workstarted_yn' => 1,
                'terminal_yn' => 1,
                'needstesting_yn' => 0,
                'default_ignore_effort_yn' => 0,
                'soft_delete_yn' => 0,
                'title_tx' => 'Completed With Concerns',
                'description_tx' => 'This has been completed with concerns that can be addressed by other activities',
                'happy_yn' => 0,
                'sort_position' => 60,
                'ot_scf' => 0.78,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'RBO',
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'needstesting_yn' => 0,
                'default_ignore_effort_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Replaced By Other workitem(s)',
                'description_tx' => 'This workitem has been replaced by one or more other workitems',
                'sort_position' => 80,
                'ot_scf' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_workitem_status')->fields(array(
                'code' => 'AB',
                'workstarted_yn' => 0,
                'terminal_yn' => 1,
                'needstesting_yn' => 0,
                'default_ignore_effort_yn' => 1,
                'soft_delete_yn' => 1,
                'title_tx' => 'Abandoned',
                'description_tx' => 'This has been abandoned',
                'sort_position' => 90,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base workitem data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadSprintStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'NRFW',
            'workstarted_yn' => 0,
            'terminal_yn' => 0,
            'title_tx' => 'Not Ready For Work',
            'description_tx' => 'This sprint has not yet been reviewed for completeness',
            'sort_position' => 10,
            'ot_scf' => 0.95,
            'created_dt' => $updated_dt,
            ))->execute();
        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'RFW',
            'workstarted_yn' => 1,
            'terminal_yn' => 0,
            'title_tx' => 'Ready For Work',
            'description_tx' => 'The work on this sprint can start and may have already started',
            'happy_yn' => 1,
            'sort_position' => 20,
            'ot_scf' => 0.95,
            'created_dt' => $updated_dt,
            ))->execute();
        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'AB',
            'workstarted_yn' => 0,
            'terminal_yn' => 1,
            'title_tx' => 'Abandoned',
            'description_tx' => 'This has been abandoned',
            'happy_yn' => 0,
            'sort_position' => 30,
            'ot_scf' => 0.75,
            'created_dt' => $updated_dt,
            ))->execute();
        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'WNR',
            'workstarted_yn' => 1,
            'terminal_yn' => 1,
            'title_tx' => 'Will Not Review',
            'description_tx' => 'Sprint is complete but it will not be reviewed and scored',
            'sort_position' => 40,
            'ot_scf' => 0.95,
            'created_dt' => $updated_dt,
            ))->execute();
        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'MRC',
            'workstarted_yn' => 1,
            'terminal_yn' => 1,
            'title_tx' => 'Marginal Review Complete',
            'description_tx' => 'Sprint is complete and has only been marginally reviewed and scored',
            'sort_position' => 50,
            'ot_scf' => 0.96,
            'created_dt' => $updated_dt,
            ))->execute();
        db_insert('bigfathom_sprint_status')->fields(array(
            'code' => 'FRC',
            'workstarted_yn' => 1,
            'terminal_yn' => 1,
            'title_tx' => 'Final Review Complete',
            'description_tx' => 'Sprint is complete and has been reviewed and scored',
            'sort_position' => 60,
            'ot_scf' => 1,
            'created_dt' => $updated_dt,
            ))->execute();
            
            drupal_set_message($t('Loaded base sprint data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadEquipmentStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_equipment_condition')->fields(array(
                'code' => 'OP',
                'title_tx' => 'Operational',
                'description_tx' => 'The equipment is operational without known issues',
                'sort_position' => 10,
                'happy_yn' => 1,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_equipment_condition')->fields(array(
                'code' => 'OPNM',
                'title_tx' => 'Operational Needs Repair',
                'description_tx' => 'The equipment is operational but needs maintenance',
                'sort_position' => 20,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_equipment_condition')->fields(array(
                'code' => 'NOP',
                'title_tx' => 'Not Operational',
                'description_tx' => 'The equipment is not currently operational',
                'sort_position' => 30,
                'happy_yn' => 0,
                'ot_scf' => 0.50,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_equipment_condition')->fields(array(
                'code' => 'AB',
                'title_tx' => 'Abandoned',
                'description_tx' => 'The equipment is not operational and will not be repaired',
                'sort_position' => 99,
                'happy_yn' => 0,
                'ot_scf' => 0,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base equipment data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadExternalResourceStatusData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_external_resource_condition')->fields(array(
                'code' => 'ATWR',
                'title_tx' => 'Able to Work and Responsive',
                'description_tx' => 'The external_resource is able to work without known delays in service delivery',
                'sort_position' => 10,
                'happy_yn' => 1,
                'ot_scf' => 0.95,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_external_resource_condition')->fields(array(
                'code' => 'ATWBL',
                'title_tx' => 'Able to Work but Backlogged',
                'description_tx' => 'The external resource is experiencing delays in service delivery',
                'sort_position' => 20,
                'ot_scf' => 0.75,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_external_resource_condition')->fields(array(
                'code' => 'TNATW',
                'title_tx' => 'Temporarily Not Able to Work',
                'description_tx' => 'The external resource is temporarily unable to deliver services',
                'sort_position' => 30,
                'ot_scf' => 0.5,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_external_resource_condition')->fields(array(
                'code' => 'PWS',
                'title_tx' => 'Permanent Work Stoppage',
                'description_tx' => 'The external resource is permanently unable to deliver services',
                'sort_position' => 99,
                'happy_yn' => 0,
                'ot_scf' => 0,
                'created_dt' => $updated_dt,
                ))->execute();

            
            drupal_set_message($t('Loaded base external resource data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadCountryData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());
            
            db_insert('bigfathom_country')->fields(array(
                'id' => 840,
                'abbr' => 'USA',
                'name' => 'United States of America',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_country')->fields(array(
                'id' => 124,
                'abbr' => 'CAN',
                'name' => 'Canada',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base country data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadStateData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());
            
            $allstates = array(
                "AL"=>"Alabama",
                "AK"=>"Alaska",
                "AZ"=>"Arizona",
                "AR"=>"Arkansas",
                "CA"=>"California",
                "CO"=>"Colorado",
                "CT"=>"Connecticut",
                "DE"=>"Delaware",
                "DC"=>"District of Columbia",
                "FL"=>"Florida",
                "GA"=>"Georgia",
                "HI"=>"Hawaii",
                "ID"=>"Idaho",
                "IL"=>"Illinois",
                "IN"=>"Indiana",
                "IA"=>"Iowa",
                "KS"=>"Kansas",
                "KY"=>"Kentucky",
                "LA"=>"Louisiana",
                "ME"=>"Maine",
                "MD"=>"Maryland",
                "MA"=>"Massachusetts",
                "MI"=>"Michigan",
                "MN"=>"Minnesota",
                "MS"=>"Mississippi",
                "MO"=>"Missouri",
                "MT"=>"Montana",
                "NE"=>"Nebraska",
                "NV"=>"Nevada",
                "NH"=>"New Hampshire",
                "NJ"=>"New Jersey",
                "NM"=>"New Mexico",
                "NY"=>"New York",
                "NC"=>"North Carolina",
                "ND"=>"North Dakota",
                "OH"=>"Ohio",
                "OK"=>"Oklahoma",
                "OR"=>"Oregon",
                "PA"=>"Pennsylvania",
                "RI"=>"Rhode Island",
                "SC"=>"South Carolina",
                "SD"=>"South Dakota",
                "TN"=>"Tennessee",
                "TX"=>"Texas",
                "UT"=>"Utah",
                "VT"=>"Vermont",
                "VA"=>"Virginia",
                "WA"=>"Washington",
                "WV"=>"West Virginia",
                "WI"=>"Wisconsin",
                "WY"=>"Wyoming",
            );

            $id = 0;
            foreach($allstates as $abbr=>$name)
            {
                $id++;
                db_insert('bigfathom_state')->fields(array(
                    'id' => $id,
                    'abbr' => $abbr,
                    'name' => $name,
                    'countryid' => 840,
                    'active_yn' => 1,
                    'updated_dt' => $updated_dt,
                    'created_dt' => $updated_dt,
                    ))->execute();
            }
            
            drupal_set_message($t('Loaded base state data'));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    
}
    