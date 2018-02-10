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
 * This class tells us about fundamental mappings.
 * Try to keep this file small because it is loaded every time.
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class DatabaseNamesHelper
{
    
    public static $m_baseline_availability_tablename = 'bigfathom_baseline_availability';
    public static $m_holiday_tablename = 'bigfathom_holiday';
    public static $m_map_person2availability_tablename = 'bigfathom_map_person2availability';

    public static $m_portfolio_tablename = 'bigfathom_portfolio';
    public static $m_map_project2portfolio_tablename = 'bigfathom_map_project2portfolio';
    
    public static $m_usecase_tablename = 'bigfathom_usecase';
    public static $m_map_tag2usecase_tablename = 'bigfathom_map_tag2usecase';
    public static $m_map_workitem2usecase_tablename = 'bigfathom_map_workitem2usecase';
    
    public static $m_testcase_tablename = 'bigfathom_testcase';
    public static $m_testcasestep_tablename = 'bigfathom_testcasestep';
    public static $m_map_tag2testcase_tablename = 'bigfathom_map_tag2testcase';
    public static $m_map_workitem2testcase_tablename = 'bigfathom_map_workitem2testcase';

    public static $m_testcase_history_tablename = 'bigfathom_testcase_history';
    public static $m_testcasestep_history_tablename = 'bigfathom_testcasestep_history';
    
    public static $m_project_tablename = 'bigfathom_project';
    public static $m_project_recent_data_updates_tablename = 'bigfathom_project_recent_data_updates';
    public static $m_project_baseline_tablename = 'bigfathom_project_baseline';
    
    public static $m_template_project_recent_data_updates_tablename = 'bigfathom_template_project_recent_data_updates';
    public static $m_template_project_library_tablename = 'bigfathom_template_project_library';
    public static $m_template_workitem_tablename = 'bigfathom_template_workitem';
    public static $m_map_tag2tp_tablename = 'bigfathom_map_tag2tp';
    public static $m_map_tag2tw_tablename = 'bigfathom_map_tag2tw';
    public static $m_map_prole2tp_tablename = 'bigfathom_map_role2tp';
    public static $m_map_prole2tw_tablename = 'bigfathom_map_role2tw';
    public static $m_map_tw2tw_tablename = 'bigfathom_map_tw2tw';
    public static $m_map_group2tp_tablename = 'bigfathom_map_group2tp';
    public static $m_map_publishedrefname2tp_tablename = 'bigfathom_map_publishedrefname2tp';
    
    public static $m_brainstorm_recent_data_updates_tablename = 'bigfathom_brainstorm_recent_data_updates';
    public static $m_brainstorm_communication_recent_data_updates_tablename = 'bigfathom_brainstorm_communication_recent_data_updates';
    public static $m_workitem_recent_data_updates_tablename = 'bigfathom_workitem_recent_data_updates';
    public static $m_workitem_communication_recent_data_updates_tablename = 'bigfathom_workitem_communication_recent_data_updates';
    public static $m_sprint_recent_data_updates_tablename = 'bigfathom_sprint_recent_data_updates';
    public static $m_sprint_communication_recent_data_updates_tablename = 'bigfathom_sprint_communication_recent_data_updates';
    
    public static $m_usecase_recent_data_updates_tablename = 'bigfathom_usecase_recent_data_updates';
    public static $m_usecase_communication_recent_data_updates_tablename = 'bigfathom_usecase_communication_recent_data_updates';
    public static $m_testcase_recent_data_updates_tablename = 'bigfathom_testcase_recent_data_updates';
    public static $m_testcase_communication_recent_data_updates_tablename = 'bigfathom_testcase_communication_recent_data_updates';
        
    public static $m_project_recent_selection_by_user_tablename = 'bigfathom_project_recent_selection_by_user';
    public static $m_published_project_info_tablename = 'bigfathom_published_project_info';

    public static $m_remote_uri_scheme_whitelist_tablename = 'bigfathom_remote_uri_scheme_whitelist';
    public static $m_remote_uri_domain_whitelist_tablename = 'bigfathom_remote_uri_domain_whitelist';
    public static $m_remote_uri_domain_blacklist_tablename = 'bigfathom_remote_uri_domain_blacklist';
    
    public static $m_remote_project_info_tablename = 'bigfathom_remote_project_info';
    
    public static $m_brainstorm_item_tablename = 'bigfathom_brainstorm_item';
    public static $m_workitem_tablename = 'bigfathom_workitem';
    public static $m_workitem_history_tablename = 'bigfathom_workitem_history';
    
    public static $m_role_tablename = 'bigfathom_role';
    public static $m_person_tablename = 'bigfathom_person';
    public static $m_group_tablename = 'bigfathom_group';
    public static $m_group_recent_data_updates_tablename = 'bigfathom_group_recent_data_updates';
    public static $m_visionstatement_tablename = 'bigfathom_visionstatement';
    public static $m_sprint_tablename = 'bigfathom_sprint';
    public static $m_systemrole_tablename = 'bigfathom_systemrole';
    public static $m_tag_tablename = 'bigfathom_tag';
    
    public static $m_equipment_tablename = 'bigfathom_equipment';
    public static $m_external_resource_tablename = 'bigfathom_external_resource';

    public static $m_location_tablename = 'bigfathom_location';
    public static $m_state_tablename = 'bigfathom_state';
    public static $m_country_tablename = 'bigfathom_country';
    
    public static $m_action_status_tablename = 'bigfathom_action_status';
    public static $m_project_context_tablename = 'bigfathom_project_context';
    public static $m_workitem_status_tablename = 'bigfathom_workitem_status';
    public static $m_sprint_status_tablename = 'bigfathom_sprint_status';
    public static $m_usecase_status_tablename = 'bigfathom_usecase_status';
    public static $m_testcase_status_tablename = 'bigfathom_testcase_status';
    
    public static $m_external_resource_condition_tablename = 'bigfathom_external_resource_condition';
    public static $m_equipment_condition_tablename = 'bigfathom_equipment_condition';
    
    public static $m_map_brainstormid2wid_tablename = 'bigfathom_map_brainstormid2wid';
    
    public static $m_map_group2project_tablename = 'bigfathom_map_group2project';
    
    public static $m_map_wi2wi_tablename = 'bigfathom_map_wi2wi';
    public static $m_map_workitem2sprint_tablename = 'bigfathom_map_workitem2sprint';

    public static $m_map_prole2wi_tablename = 'bigfathom_map_role2wi';

    public static $m_map_external_prole2ours_tablename = 'bigfathom_map_external_role2ours';
    
    
    public static $m_map_role2sprint_tablename = 'bigfathom_map_role2sprint';

    public static $m_map_subproject2project_tablename = 'bigfathom_map_subproject2project';

    public static $m_map_publishedrefname2project_tablename = 'bigfathom_map_publishedrefname2project';
    
    
    public static $m_map_visionstatement2project_tablename = 'bigfathom_map_visionstatement2project';
    
    public static $m_map_prole2project_tablename = 'bigfathom_map_role2project';
    
    public static $m_map_prole2sprint_tablename = 'bigfathom_map_role2sprint';
    
    public static $m_map_person2role_tablename = 'bigfathom_map_person2role';
    public static $m_map_person2role_in_group_tablename = 'bigfathom_map_person2role_in_group';
    public static $m_map_person2systemrole_in_group_tablename = 'bigfathom_map_person2systemrole_in_group';

    public static $m_map_person2pcg_in_project_tablename = 'bigfathom_map_person2pcg_in_project';
    
    public static $m_map_person2role_in_workitem_tablename = 'bigfathom_map_person2role_in_workitem';
    public static $m_map_person2systemrole_in_workitem_tablename = 'bigfathom_map_person2systemrole_in_workitem';

    public static $m_map_person2role_in_sprint_tablename = 'bigfathom_map_person2role_in_sprint';
    public static $m_map_person2systemrole_in_sprint_tablename = 'bigfathom_map_person2systemrole_in_sprint';
    
    public static $m_map_tag2workitem_tablename = 'bigfathom_map_tag2workitem';
    public static $m_map_tag2group_tablename = 'bigfathom_map_tag2group';
    public static $m_map_tag2role_tablename = 'bigfathom_map_tag2role';
    public static $m_map_tag2systemrole_tablename = 'bigfathom_map_tag2systemrole';
    public static $m_map_tag2project_tablename = 'bigfathom_map_tag2project';
    public static $m_map_tag2sprint_tablename = 'bigfathom_map_tag2sprint';
    
    public static $m_suggest_map_person2role_in_workitem_tablename = 'bigfathom_suggest_map_person2role_in_workitem';
    public static $m_suggest_map_workitem2sprint_tablename = 'bigfathom_suggest_map_workitem2sprint';

    public static $m_suggest_person2own_workitem_tablename = 'bigfathom_suggest_person2own_workitem';

    public static $m_suggest_person_insight2wi_tablename = 'bigfathom_suggest_person_insight2wi';
    public static $m_suggest_person_influence2wi_tablename = 'bigfathom_suggest_person_influence2wi';
    public static $m_suggest_person_importance2wi_tablename = 'bigfathom_suggest_person_importance2wi';
    
    public static $m_general_person_insight2wi_tablename = 'bigfathom_general_person_insight2wi';
    public static $m_general_person_influence2wi_tablename = 'bigfathom_general_person_influence2wi';
    public static $m_general_person_importance2wi_tablename = 'bigfathom_general_person_importance2wi';

    public static $m_project_communication_tablename = 'bigfathom_project_communication';
    public static $m_workitem_communication_tablename = 'bigfathom_workitem_communication';
    public static $m_sprint_communication_tablename = 'bigfathom_sprint_communication';
    public static $m_testcase_communication_tablename = 'bigfathom_testcase_communication';
    public static $m_usecase_communication_tablename = 'bigfathom_usecase_communication';

    public static $m_project_communication_history_tablename = 'bigfathom_project_communication_history';
    public static $m_workitem_communication_history_tablename = 'bigfathom_workitem_communication_history';
    public static $m_sprint_communication_history_tablename = 'bigfathom_sprint_communication_history';
    public static $m_usecase_communication_history_tablename = 'bigfathom_usecase_communication_history';
    public static $m_testcase_communication_history_tablename = 'bigfathom_testcase_communication_history';
    public static $m_map_wi2wi_history_tablename = 'bigfathom_map_wi2wi_history';

    public static $m_map_project_communication2attachment_tablename = 'bigfathom_map_project_communication2attachment';
    public static $m_map_workitem_communication2attachment_tablename = 'bigfathom_map_workitem_communication2attachment';
    public static $m_map_sprint_communication2attachment_tablename = 'bigfathom_map_sprint_communication2attachment';
    public static $m_map_usecase_communication2attachment_tablename = 'bigfathom_map_usecase_communication2attachment';
    public static $m_map_testcase_communication2attachment_tablename = 'bigfathom_map_testcase_communication2attachment';
    public static $m_map_testcase_communication2testcasestep_tablename = 'bigfathom_map_testcase_communication2testcasestep';

    public static $m_map_project_communication2attachment_history_tablename = 'bigfathom_map_project_communication2attachment_history';
    public static $m_map_workitem_communication2attachment_history_tablename = 'bigfathom_map_workitem_communication2attachment_history';
    public static $m_map_sprint_communication2attachment_history_tablename = 'bigfathom_map_sprint_communication2attachment_history';
    public static $m_map_usecase_communication2attachment_history_tablename = 'bigfathom_map_usecase_communication2attachment_history';
    public static $m_map_testcase_communication2attachment_history_tablename = 'bigfathom_map_testcase_communication2attachment_history';
    public static $m_map_testcase_communication2testcasestep_history_tablename = 'bigfathom_map_testcase_communication2testcasestep_history';
    
    public static $m_attachment_tablename = 'bigfathom_attachment';
    public static $m_attachment_history_tablename = 'bigfathom_attachment_history';

    public static $m_map_delegate_workitemowner_tablename = 'bigfathom_map_delegate_workitemowner';
    public static $m_map_delegate_sprintowner_tablename = 'bigfathom_map_delegate_sprintowner';
    public static $m_map_delegate_usecaseowner_tablename = 'bigfathom_map_delegate_usecaseowner';
    public static $m_map_delegate_testcaseowner_tablename = 'bigfathom_map_delegate_testcaseowner';
    
}

