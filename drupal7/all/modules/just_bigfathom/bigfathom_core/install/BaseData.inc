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
class BaseData
{
    private $m_oUAH = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/UserAccountHelper');
        $this->m_oUAH = new \bigfathom\UserAccountHelper();
    }
    
    public function load()
    {
        try
        {
            
            $this->loadLocationData();
            $this->loadCoreUserData();
            $this->loadCoreGroupData();
            $this->loadVisionStatementData();
            //$this->loadTemplateData();
            $this->loadProjectData();
            $this->loadHolidayData();
            $this->loadUseCaseData();
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    

    private function loadHolidayData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-01-02',
                'holiday_nm' => 'New Year Observed',
                'comment_tx' => 'Generally observed as a holiday around the world',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-01-16',
                'holiday_nm' => 'Martin Luther King Jr. Day',
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-02-20',
                'holiday_nm' => "Presidents' Day",
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-05-29',
                'holiday_nm' => "Memorial Day",
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-07-04',
                'holiday_nm' => "Independence Day",
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-09-04',
                'holiday_nm' => "Labor Day",
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-09-21',
                'holiday_nm' => "Rosh Hashana",
                'comment_tx' => '',
                'apply_to_all_users_yn' => 0,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-10-18',
                'holiday_nm' => "Diwali/Deepavali",
                'comment_tx' => '',
                'apply_to_all_users_yn' => 0,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-11-10',
                'holiday_nm' => "Veterans Day",
                'countryid' => 840,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-11-23',
                'holiday_nm' => "Thanksgiving Day",
                'countryid' => 840,
                'comment_tx' => 'Observed in all the states',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-12-25',
                'holiday_nm' => "Christmas Day",
                'comment_tx' => '',
                'apply_to_all_users_yn' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-03-17',
                'holiday_nm' => "Maryland Day",
                'countryid' => 840,
                'stateid' => 21,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 0,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
             
            db_insert('bigfathom_holiday')->fields(array(
                'holiday_dt' => '2017-11-24',
                'holiday_nm' => "American Indian Heritage Day",
                'countryid' => 840,
                'stateid' => 21,
                'comment_tx' => '',
                'apply_to_all_users_yn' => 0,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base holiday data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
    
    private function loadCoreUserData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_person')->fields(array(
                'id' => 1,
                'shortname' => 'admin',
                'first_nm' => 'System',
                'last_nm' => 'Administrator',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_person2systemrole')->fields(array(
                'personid' => 1,
                'systemroleid' => 9,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_person')->fields(array(
                'id' => 100,
                'shortname' => 'staff',
                'first_nm' => 'Staff',
                'last_nm' => 'Placeholder',
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_person2role')->fields(array(
                'personid' => 100,
                'roleid' => 8,  //Workitem Owner
                'importance' => 55,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            //TODO REMOVE THE FOLLOWING ACOUNTS BEFORE MOVING TO PRODUCTION!!!!!!!!
            $shortname = 'janedoe';
            $firstname = 'Jane';
            $lastname = 'Doe';
            $locationid = NULL;
            $role_maps = [];
            $role_maps['proles'] = [];
            $role_maps['proles'][1] = 55;
            $role_maps['proles'][2] = 55;
            $role_maps['proles'][3] = 55;
            $role_maps['sroles'] = [];
            $subrole_maps = [];
            $subrole_maps['systemrole_in_group'] = [];
            $subrole_maps['role_in_group'] = [];
            $role_maps['subrole_maps'] = $subrole_maps;
            $email = "$shortname@bigfathom.com";
            $password = "{$shortname}2017";
            $new_uid = $this->m_oUAH->createUserAccount($shortname, $firstname, $lastname, 1, $locationid, $role_maps, $email, $password);
            error_log("DEBUG Created test user uid=$new_uid with shortname=$shortname");

            $vacation_start_dt = date("Y-m-d", strtotime('+15 day', time()));
            $vacation_end_dt = date("Y-m-d", strtotime('+22 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $vacation_start_dt,
                'end_dt' => $vacation_end_dt,
                'type_cd' => 'V',
                'hours_per_day' => 0,
                'comment_tx' => 'Vacation',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 0,
                'work_tuesday_yn' => 0,
                'work_wednesday_yn' => 0,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();

            $baseline_start_dt = date("Y-m-d", strtotime('-30 day', time()));
            $baseline_end_dt = date("Y-m-d", strtotime('-2 day', time()));   
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $baseline_start_dt,
                'end_dt' => $baseline_end_dt,
                'type_cd' => 'B',
                'hours_per_day' => 8,
                'comment_tx' => 'Custom Baseline',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();   

            $parttime_start_dt = date("Y-m-d", strtotime('-1 day', time()));
            $parttime_end_dt = date("Y-m-d", strtotime('+10 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $parttime_start_dt,
                'end_dt' => $parttime_end_dt,
                'type_cd' => 'P',
                'hours_per_day' => 4,
                'comment_tx' => 'Part-time',
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
            
            $baseline_start_dt = date("Y-m-d", strtotime('+30 day', time()));
            $baseline_end_dt = date("Y-m-d", strtotime('+100 day', time()));   
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $baseline_start_dt,
                'end_dt' => $baseline_end_dt,
                'type_cd' => 'B',
                'hours_per_day' => 8,
                'comment_tx' => 'Custom Baseline',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();   
            
            $parttime_start_dt = date("Y-m-d", strtotime('+30 day', time()));
            $parttime_end_dt = date("Y-m-d", strtotime('+60 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $parttime_start_dt,
                'end_dt' => $parttime_end_dt,
                'type_cd' => 'P',
                'hours_per_day' => 4,
                'comment_tx' => 'Part-time',
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
            
            $shortname = 'johndoe';
            $firstname = 'John';
            $lastname = 'Doe';
            $locationid = 110;
            $role_maps = [];
            $role_maps['proles'] = [];
            $role_maps['proles'][1] = 75;
            $role_maps['proles'][3] = 55;
            $role_maps['proles'][5] = 55;
            $role_maps['proles'][6] = 55;
            $role_maps['proles'][7] = 25;
            $role_maps['sroles'] = [];
            $subrole_maps = [];
            $subrole_maps['systemrole_in_group'] = [];
            $subrole_maps['role_in_group'] = [];
            $role_maps['subrole_maps'] = $subrole_maps;
            $email = "$shortname@user.bigfathom.com";
            $password = "{$shortname}2017";
            $new_uid = $this->m_oUAH->createUserAccount($shortname, $firstname, $lastname, 2, $locationid, $role_maps, $email, $password);
            error_log("DEBUG Created test user uid=$new_uid with shortname=$shortname");

            $parttime_start_dt = date("Y-m-d", strtotime('-21 day', time()));
            $parttime_end_dt = date("Y-m-d", strtotime('+1 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $parttime_start_dt,
                'end_dt' => $parttime_end_dt,
                'type_cd' => 'P',
                'hours_per_day' => 4,
                'comment_tx' => 'Part-time',
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
            
            $vacation_start_dt = date("Y-m-d", strtotime('+2 day', time()));
            $vacation_end_dt = date("Y-m-d", strtotime('+10 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $vacation_start_dt,
                'end_dt' => $vacation_end_dt,
                'type_cd' => 'V',
                'hours_per_day' => 0,
                'comment_tx' => 'Vacation',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 0,
                'work_tuesday_yn' => 0,
                'work_wednesday_yn' => 0,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            $crunch_start_dt = date("Y-m-d", strtotime('+11 day', time()));
            $crunch_end_dt = date("Y-m-d", strtotime('+22 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $crunch_start_dt,
                'end_dt' => $crunch_end_dt,
                'type_cd' => 'C',
                'hours_per_day' => 9,
                'comment_tx' => 'Extra effort to get things done',
                'work_saturday_yn' => 1,
                'work_sunday_yn' => 1,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 1,
                'work_holidays_yn' => 1,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();

            $special_start_dt = date("Y-m-d", strtotime('+23 day', time()));
            $special_end_dt = date("Y-m-d", strtotime('+33 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $special_start_dt,
                'end_dt' => $special_end_dt,
                'type_cd' => 'O',
                'hours_per_day' => 8,
                'comment_tx' => 'Only on weekends',
                'work_saturday_yn' => 1,
                'work_sunday_yn' => 1,
                'work_monday_yn' => 0,
                'work_tuesday_yn' => 0,
                'work_wednesday_yn' => 0,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 1,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            $baseline_start_dt = date("Y-m-d", strtotime('-5 day', time()));
            $baseline_end_dt = date("Y-m-d", strtotime('+100 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $baseline_start_dt,
                'end_dt' => $baseline_end_dt,
                'type_cd' => 'B',
                'hours_per_day' => 5,
                'comment_tx' => 'Custom baseline',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 1,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            $shortname = 'smitysmith';
            $firstname = 'Smity';
            $lastname = 'Smith';
            $locationid = 100;
            $role_maps = [];
            $role_maps['proles'] = [];
            $role_maps['proles'][1] = 55;
            $role_maps['proles'][2] = 44;
            $role_maps['proles'][3] = 33;
            $role_maps['sroles'] = [];
            $subrole_maps = [];
            $subrole_maps['systemrole_in_group'] = [];
            $subrole_maps['role_in_group'] = [];
            $role_maps['subrole_maps'] = $subrole_maps;
            $email = "$shortname@bigfathom.com";
            $password = "{$shortname}2017";
            $new_uid = $this->m_oUAH->createUserAccount($shortname, $firstname, $lastname, 1, $locationid, $role_maps, $email, $password);
            error_log("DEBUG Created test user uid=$new_uid with shortname=$shortname");

            $vacation_start_dt = date("Y-m-d", strtotime('+20 day', time()));
            $vacation_end_dt = date("Y-m-d", strtotime('+25 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $vacation_start_dt,
                'end_dt' => $vacation_end_dt,
                'type_cd' => 'V',
                'hours_per_day' => 0,
                'comment_tx' => 'Vacation',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 0,
                'work_tuesday_yn' => 0,
                'work_wednesday_yn' => 0,
                'work_thursday_yn' => 0,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();

            $baseline_start_dt = date("Y-m-d", strtotime('-40 day', time()));
            $baseline_end_dt = date("Y-m-d", strtotime('+55 day', time()));   
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $baseline_start_dt,
                'end_dt' => $baseline_end_dt,
                'type_cd' => 'B',
                'hours_per_day' => 8,
                'comment_tx' => 'Custom Baseline',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();   

            $parttime_start_dt = date("Y-m-d", strtotime('+5 day', time()));
            $parttime_end_dt = date("Y-m-d", strtotime('+20 day', time()));            
            db_insert('bigfathom_map_person2availability')->fields(array(
                'personid' => $new_uid,
                'start_dt' => $parttime_start_dt,
                'end_dt' => $parttime_end_dt,
                'type_cd' => 'P',
                'hours_per_day' => 4,
                'comment_tx' => 'Part-time',
                'work_saturday_yn' => 0,
                'work_sunday_yn' => 0,
                'work_monday_yn' => 1,
                'work_tuesday_yn' => 1,
                'work_wednesday_yn' => 1,
                'work_thursday_yn' => 1,
                'work_friday_yn' => 0,
                'work_holidays_yn' => 0,
                'updated_by_personid' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();            
            
            drupal_set_message($t('Loaded base user data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadCoreGroupData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_group')->fields(array(
                'id' => 1,
                'group_nm' => 'Everyone',
                'purpose_tx' => 'This group automatically contains every person declared in the application.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_group')->fields(array(
                'id' => 10,
                'group_nm' => 'Default Privileged Collaborators',
                'purpose_tx' => 'This is the default list of users who are given exceptional allowances for editing project content.  When a new project is created, it inherits this list as its initial membership of privileged collaborators.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_group')->fields(array(
                'id' => 11,
                'group_nm' => 'Nobody',
                'purpose_tx' => 'This group never contains anyone.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            /*
            db_insert('bigfathom_group')->fields(array(
                'id' => 2,
                'group_nm' => 'Subproject Creators',
                'purpose_tx' => 'Users who are able to create subprojects.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_group')->fields(array(
                'id' => 3,
                'group_nm' => 'Workitem Creators',
                'purpose_tx' => 'Users who are able to create new workitems.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_group')->fields(array(
                'id' => 4,
                'group_nm' => 'Sprint Leaders',
                'purpose_tx' => 'Users who are able to create and modify sprints.',
                'leader_personid' => 1,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            */
            
            drupal_set_message($t('Loaded base group data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function loadVisionStatementData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_visionstatement')->fields(array(
                'statement_nm' => "See more",
                'statement_tx' => "When people see more clearly they are equiped to make better choices.  And what we see depends on what we are prepared to see.  Equip people with mental models that enable them to see more clearly and further than they could before.  We produce tools to help people understand there is more constructive wonder within their reach than they might think.",
                'owner_personid' => 1,
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_visionstatement')->fields(array(
                'statement_nm' => "Power of resiliance",
                'statement_tx' => "A big difference between the beginner and the master is that the master has failed many more times.  When we understand the significance of being resiliant and have a constructive goal we are capable of greater things.  We produce tools that help people realize that failure is part of the growth process and they are capable of more when they get back up after a setback.",
                'owner_personid' => 1,
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_visionstatement')->fields(array(
                'statement_nm' => "Value people over things",
                'statement_tx' => "People are the most valuable powerful living creatures on this planet.  When we encourage the full person to collaborate on common goals, bigger creative surprising, unexpected opportunities can reveal themselves.  We provide tools to help people collaborate on as many dimensions as possible with the least distracting constraints.",
                'owner_personid' => 1,
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_visionstatement')->fields(array(
                'statement_nm' => "Power of diversity",
                'statement_tx' => "A concept that is familiar and naturally comfortable can also be wrong and limiting to growth.  Judge the value of concepts on their objective merit not on fear of rocking a strongly held comfortable group idea.  New ideas sometimes come from new people.  We produce tools that help people with diverse life experiences connect and contribute in goal relevant ways.",
                'owner_personid' => 1,
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_visionstatement')->fields(array(
                'statement_nm' => "Measuring for improvement",
                'statement_tx' => "When we have meaningful standards that we can measure and objectively compare then we are positioned to assess if we are moving forward or slipping back.  With this knowledge we can position ourselves to repeat what moves us forward and constructively rethink what slips us away from our goals.  We produce tools that help people measure what matters.",
                'owner_personid' => 1,
                'active_yn' => 1,
                'created_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t("Loaded core vision statement data at $updated_dt"));
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function loadTemplateData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            //DEPRECATED --- loaded from template_libraray module instead
            
            drupal_set_message($t('Loaded base template data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
 
    private function loadLocationData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_location')->fields(array(
                'id' => 100,
                'shortname' => 'NY Office',
                'address_line1' => '12345 Apple Road',
                'address_line2' => '',
                'city_tx' => 'New York',
                'stateid' => 33,
                'countryid' => 840,
                'description_tx' => "This is the office in New York",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_location')->fields(array(
                'id' => 101,
                'shortname' => 'MD Office',
                'address_line1' => '9876 Raven Street',
                'address_line2' => '',
                'city_tx' => 'Baltimore',
                'stateid' => 21,
                'countryid' => 840,
                'description_tx' => "This is the office in Baltimore",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_location')->fields(array(
                'id' => 103,
                'shortname' => 'VA Office',
                'address_line1' => '765 Jefferson Street',
                'address_line2' => '',
                'city_tx' => 'Richmond',
                'stateid' => 47,
                'countryid' => 840,
                'description_tx' => "This is the office in Richmond",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_location')->fields(array(
                'id' => 105,
                'shortname' => 'DC Office',
                'address_line1' => '54321 Capitol Street',
                'address_line2' => '',
                'city_tx' => 'Washington',
                'stateid' => 9,
                'countryid' => 840,
                'description_tx' => "This is the office in DC",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_location')->fields(array(
                'id' => 110,
                'shortname' => 'Dallas Office',
                'address_line1' => '555 Cattle Boulevard',
                'address_line2' => '',
                'city_tx' => 'Dallas',
                'stateid' => 44,
                'countryid' => 840,
                'description_tx' => "This is the office in TX",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_location')->fields(array(
                'id' => 115,
                'shortname' => 'Los Angeles Office',
                'address_line1' => '4321 Cinema Boulevard',
                'address_line2' => '',
                'city_tx' => 'Los Angeles',
                'stateid' => 5,
                'countryid' => 840,
                'description_tx' => "This is the office in CA",
                'ot_scf' => 0.99,
                'ob_scf' => 0.99,
                'active_yn' => 1,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base location data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
 
    private function loadUseCaseData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            db_insert('bigfathom_usecase')->fields(array(
                'id' => 1,
                'owner_projectid' => 1,
                'usecase_nm' => 'Brainstorm Topics',
                'blurb_tx' => 'Facilitate collaboration when brainstorming possible topics',
                'perspective_cd' => 'U',
                'precondition_tx' => 'Two users are logged into bigfathom and have created an empty project',
                'postcondition_tx' => 'Several potential workitems have been identified and qualitatively assessed as simple or difficult work',
                'steps_tx' => "1. Go to the Topics tab of the Visual Collaboration area\n2. Create topics and drag them to be Task or Goal\n3. Click on the Dependency tab and link them",
                'references_tx' => '',
                'active_yn' => 1,
                'owner_personid' => 1,
                'importance' => 100,
                'status_cd' => "RFI",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_usecase')->fields(array(
                'id' => 2,
                'owner_projectid' => 1,
                'usecase_nm' => 'Prioritized Worklist',
                'blurb_tx' => 'Facilitate prioritizing what to work on when',
                'perspective_cd' => 'U',
                'precondition_tx' => 'User is logged in and is owner of several workitems in one or more active projects',
                'postcondition_tx' => 'Sees what the top 3 workitems for today are',
                'steps_tx' => "1. Go to the Worklist tab of the Dashboard area\n2. Sort display on the urgency column\n3. See the top three items shown",
                'references_tx' => '',
                'active_yn' => 1,
                'owner_personid' => 1,
                'importance' => 100,
                'status_cd' => "RFI",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base usecase data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
 
    private function createWorkitemHistoryRecord($workitem_fields,$comment_tx,$workitemid_override=NULL)
    {
        try
        {
            global $user;
            $created_by_personid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            $history_fields = $workitem_fields;
            $history_fields['original_updated_dt'] = $workitem_fields['updated_dt'];
            $history_fields['original_created_dt'] = $workitem_fields['created_dt'];
            $history_fields['original_updated_by_personid'] = isset($workitem_fields['updated_by_personid']) ? $workitem_fields['updated_by_personid'] : NULL;
            $history_fields['original_created_by_personid'] = $workitem_fields['created_by_personid'];
            unset($history_fields['updated_dt']);
            unset($history_fields['created_dt']);
            unset($history_fields['updated_by_personid']);
            unset($history_fields['created_by_personid']);

            if(!empty($workitemid_override))
            {
                $history_fields['workitemid'] = $workitemid_override;  
            } else {
                $history_fields['workitemid'] = isset($workitem_fields['workitemid']) ? $workitem_fields['workitemid'] : $workitem_fields['id'];
            }
            $history_fields['history_comment_tx'] = $comment_tx;
            $history_fields['history_created_dt'] = $updated_dt;  
            $history_fields['history_created_by_personid'] = $created_by_personid;  
            
            $history_qry = db_insert(DatabaseNamesHelper::$m_workitem_history_tablename)
                ->fields($history_fields);
            
            return $history_qry->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function createWorkitemRecord($workitem_fields)
    {
        if(empty($workitem_fields['created_by_personid']))
        {
            $workitem_fields['created_by_personid'] = 1;
        }
        $newid = db_insert('bigfathom_workitem')->fields($workitem_fields)->execute();
        $this->createWorkitemHistoryRecord($workitem_fields,'new workitem',$newid);
        return $newid;
    }
    
    private function loadProjectData()
    {
        try
        {
            $t = get_t();
            $updated_dt = date("Y-m-d H:i", time());

            $local_owner_personid = 1;
            
            db_insert('bigfathom_project')->fields(array(
                'id' => 1,
                'project_contextid' => 10,
                'mission_tx' => 'Improve collaboration and progress toward important goals',
                'root_goalid' => 1,
                'owner_personid' => $local_owner_personid,
                'active_yn' => 1,
                'importance' => 100,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();

            db_insert('bigfathom_map_visionstatement2project')->fields(array(
                'visionstatementid' => 1,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_visionstatement2project')->fields(array(
                'visionstatementid' => 2,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_visionstatement2project')->fields(array(
                'visionstatementid' => 3,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_visionstatement2project')->fields(array(
                'visionstatementid' => 4,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_visionstatement2project')->fields(array(
                'visionstatementid' => 5,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            
            db_insert('bigfathom_map_group2project')->fields(array(
                'groupid' => 1,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_role2project')->fields(array(
                'roleid' => 1,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_role2project')->fields(array(
                'roleid' => 2,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            db_insert('bigfathom_map_role2project')->fields(array(
                'roleid' => 3,
                'projectid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();

            $workitem_fields = array(
                'id' => 1,
                'workitem_basetype' => 'G',
                'workitem_nm' => 'Bigfathom Application Ready for Use',
                'purpose_tx' => 'Configure the Bigfathom application so it is ready for the user community to start collaborating and working toward shared goals!',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 16,
                'effort_hours_est' => 1,
                'remaining_effort_hours' => 2,
                'externally_billable_yn' => 1,
                'client_deliverable_yn' => 1,
                'active_yn' => 1,
                'importance' => 100,
                'status_cd' => "WS",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid1 = $this->createWorkitemRecord($workitem_fields);
            
            db_insert('bigfathom_map_workitem2usecase')->fields(array(
                'workitemid' => $new_wid1,
                'usecaseid' => 1,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_workitem2usecase')->fields(array(
                'workitemid' => $new_wid1,
                'usecaseid' => 2,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            $workitem_fields = array(
                'id' => 2,
                'workitem_basetype' => 'G',
                'workitem_nm' => 'Create Key User Accounts',
                'purpose_tx' => 'Create accounts for the initial key users of the system',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 8,
                'remaining_effort_hours' => 2,
                'active_yn' => 1,
                'importance' => 100,
                'status_cd' => "WNS",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid2 = $this->createWorkitemRecord($workitem_fields);
            
            $workitem_fields = array(
                'id' => 3,
                'workitem_basetype' => 'G',
                'workitem_nm' => 'Train Key Users',
                'purpose_tx' => 'Train the users that will train other users',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 8,
                'remaining_effort_hours' => 8,
                'active_yn' => 1,
                'importance' => 90,
                'status_cd' => "WNS",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid3 = $this->createWorkitemRecord($workitem_fields);
            
            $workitem_fields = array(
                'id' => 4,
                'workitem_basetype' => 'G',
                'workitem_nm' => 'Create Initial Project Groups',
                'purpose_tx' => 'Define initial small set of project groups to which new user accounts can be added.',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 8,
                'remaining_effort_hours' => 2,
                'active_yn' => 1,
                'importance' => 90,
                'status_cd' => "WNS",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid4 = $this->createWorkitemRecord($workitem_fields);
            
            $workitem_fields = array(
                'id' => 5,
                'workitem_basetype' => 'G',
                'workitem_nm' => 'Install Bigfathom_Core',
                'purpose_tx' => 'Install the core module of the application',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 1,
                'remaining_effort_hours' => 1,
                'active_yn' => 1,
                'importance' => 100,
                'status_cd' => "RT",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid5 = $this->createWorkitemRecord($workitem_fields);
            
            $workitem_fields = array(
                'id' => 6,
                'workitem_basetype' => 'T',
                'workitem_nm' => 'Select Environment for Bigfathom',
                'purpose_tx' => 'Select a computing resource on which to install the application',
                'owner_personid' => $local_owner_personid,
                'owner_projectid' => 1,
                'branch_effort_hours_est' => 4,
                'remaining_effort_hours' => 4,
                'active_yn' => 1,
                'importance' => 100,
                'status_cd' => "RT",
                'status_set_dt' => $updated_dt,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            $new_wid6 = $this->createWorkitemRecord($workitem_fields);

            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid4,
                'antwiid' => $new_wid5,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid2,
                'antwiid' => $new_wid5,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid5,
                'antwiid' => $new_wid6,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid1,
                'antwiid' => $new_wid2,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid1,
                'antwiid' => $new_wid3,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            db_insert('bigfathom_map_wi2wi')->fields(array(
                'depwiid' => $new_wid1,
                'antwiid' => $new_wid4,
                'created_by_personid' => 1,
                'created_dt' => $updated_dt,
                ))->execute();
            
            drupal_set_message($t('Loaded base project data') . " at $updated_dt");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }}
    