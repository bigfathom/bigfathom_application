<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 
 */

namespace bigfathom;

require_once 'TopInfoTabsPage.php';

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoTabs_YourAccountPage extends \bigfathom\TopInfoTabsPage
{

    private $m_selected_tab = "youraccount";
    private $m_urls_arr = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($this->m_selected_tab);
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();

        $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
        if(!$loaded_uah)
        {
            throw new \Exception('Failed to load the UserAccountHelper class');
        }
        $this->m_oUAH = new \bigfathom\UserAccountHelper();
        $loaded_mh = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded_mh) {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();        
        
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addperson_availability';
        $urls_arr['edit'] = 'bigfathom/editperson_availability';
        $urls_arr['view'] = 'bigfathom/viewperson_availability';
        $urls_arr['delete'] = 'bigfathom/deleteperson_availability';  
        
        $this->m_urls_arr = $urls_arr;
        $this->m_aDataRights = "VAED";
    }
    
    private function getUserAvailabilityDashinfoMarkup()
    {
        try
        {
            global $user;
            $bundle = $this->m_oMapHelper->getPersonAvailabilityBundle($user->uid);
            $custom_row_count = $bundle['summary']['custom_row_count'];
            $all_records = $bundle['detail'];
            
            $now_dt = date("Y-m-d", time());
            
            $rows = [];
            foreach($all_records as $record)
            {
                $is_default = $record['is_default'];
                if(empty($record['type_info']))
                {
                    $type_cd = $record['type_cd'];
                    $type_info = UtilityGeneralFormulas::getInfoForAvailabilityTypeCode($type_cd);
                } else {
                    $type_info = $record['type_info'];
                }
                $start_as_YMD = $record['start_dt'];
                $end_as_YMD = $record['end_dt'];
                
                if($record['is_default'])
                {
                    $person_availabilityid = NULL;
                    $baseline_availabilityid = $record['baseline_availabilityid'];
                } else {
                    $person_availabilityid = $record['id'];
                    $baseline_availabilityid = NULL;
                }
                $hours_per_day = $record['hours_per_day'];
                $work_monday_yn = $record['work_monday_yn'];
                $work_tuesday_yn = $record['work_tuesday_yn'];
                $work_wednesday_yn = $record['work_wednesday_yn'];
                $work_thursday_yn = $record['work_thursday_yn'];
                $work_friday_yn = $record['work_friday_yn'];
                $work_saturday_yn = $record['work_saturday_yn'];
                $work_sunday_yn = $record['work_sunday_yn'];
                $work_holidays_yn = $record['work_holidays_yn'];
                $comment_tx = $record['comment_tx'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];

                if($is_default)
                {
                    $date_classname = "";
                } else {
                    if($now_dt >= $start_as_YMD && $now_dt <= $end_as_YMD)
                    {
                        $date_classname = "text-bolder";
                    } else {

                        if($now_dt > $end_as_YMD)
                        {
                            $date_classname = "text-gray-lighter";
                        } else {
                            $date_classname = "";
                        }
                    }
                }

                $start_dt_ts = strtotime($start_as_YMD);
                $end_dt_ts = strtotime($end_as_YMD);
                
                $start_dt_markup = "[SORTNUM:{$start_dt_ts}]<span class='$date_classname'>$start_as_YMD</span>";
                $end_dt_markup = "[SORTNUM:{$end_dt_ts}]<span class='$date_classname'>$end_as_YMD</span>";
                $hours_per_day_markup = "<span class='$date_classname'>$hours_per_day</span>";
                $work_monday_yn_markup = $work_monday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_tuesday_yn_markup = $work_tuesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_wednesday_yn_markup = $work_wednesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_thursday_yn_markup = $work_thursday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_friday_yn_markup = $work_friday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_saturday_yn_markup = $work_saturday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_sunday_yn_markup = $work_sunday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_holidays_yn_markup = $work_holidays_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                
                if($updated_dt !== $created_dt)
                {
                    $updated_markup = "<span title='Originally created $created_dt'>$updated_dt</span>";
                } else {
                    $updated_markup = "<span title='Never edited'>$updated_dt</span>";
                }
                
                $comment_len = strlen($comment_tx);
                if($comment_len > 256)
                {
                    $comment_markup = substr($comment_tx, 0,256) . '...';
                } else {
                    $comment_markup = $comment_tx;
                }
                
                $has_availabilityid = !empty($person_availabilityid);
                $hover_suffix = " period [$start_as_YMD,$end_as_YMD]";
                if(!$has_availabilityid || strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('person_availabilityid'=>$person_availabilityid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view detail of {$hover_suffix}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(!$has_availabilityid || strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('person_availabilityid'=>$person_availabilityid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit detail of {$hover_suffix}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$has_availabilityid || strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('person_availabilityid'=>$person_availabilityid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete entry for {$hover_suffix}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
                $action_markup = $sViewMarkup.' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup;

                $type_markup = "[SORTNUM:".$type_info['sort']."]<span class='$date_classname' title='".$type_info['tooltip']."'>".$type_info['name']."</span>";
                
                if($custom_row_count > 0)
                {
                    $datecols_markup = "<td>$start_dt_markup</td>"
                        . "<td>$end_dt_markup</td>";
                } else {
                    $datecols_markup = "";
                }
                $rows[] = "<tr>"
                        . $datecols_markup
                        . "<td>$type_markup</td>"
                        . "<td>$hours_per_day_markup</td>"
                        . "<td>$work_monday_yn_markup</td>"
                        . "<td>$work_tuesday_yn_markup</td>"
                        . "<td>$work_wednesday_yn_markup</td>"
                        . "<td>$work_thursday_yn_markup</td>"
                        . "<td>$work_friday_yn_markup</td>"
                        . "<td>$work_saturday_yn_markup</td>"
                        . "<td>$work_sunday_yn_markup</td>"
                        . "<td>$work_holidays_yn_markup</td>"
                        . "<td>$comment_markup</td>"
                        . "<td>$updated_markup</td>"
                        . "<td class='action-options'>$action_markup</td>"
                        . "</tr>";
            }
            
            $tableheader = [];
            if($custom_row_count > 0)
            {
                $tableheader[] = array("Start Date","The start date of the bounded period","formula");
                $tableheader[] = array("End Date","The end date of the bounded period","formula");
            }
            $tableheader[] = array("Availability Type","Type of bounded period","formula");
            $tableheader[] = array("Hours/Day","Average hours per workday the person would need to work","formula");
            $tableheader[] = array("Mon","Work on Monday","formula");
            $tableheader[] = array("Tue","Work on Tuesday","formula");
            $tableheader[] = array("Wed","Work on Wednesday","formula");
            $tableheader[] = array("Thu","Work on Thursday","formula");
            $tableheader[] = array("Fri","Work on Friday","formula");
            $tableheader[] = array("Sat","Work on Saturday","formula");
            $tableheader[] = array("Sun","Work on Sunday","formula");
            $tableheader[] = array("Hol","Work on declared Holidays","formula");
            $tableheader[] = array("Notes","Comment, if any, for the bounded period.","formula");
            $tableheader[] = array("Updated","Date of most recent update","formula");
            $tableheader[] = array("Action Options","","formula");

            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th class='nowrap' title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";            
            
            $rows_markup = implode("",$rows);
            
            $table_markup = '<table id="person-availability-table" class="browserGrid">'
                                . '<thead>'
                                . $th_markup
                                . '</thead>'
                                . '<tbody>'
                                . $rows_markup
                                .  '</tbody>'
                                . '</table>'
                                . '';
            
    

            $action_buttons_markup = "";
            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Define New Availability Period',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'small-action-button', 'title'=>'Create a new custom availability entry for your profile'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $action_buttons_markup = $final_button_markup;
                }
            }    
            
            $all_markup = "<div class='dash-normal-fonts'>$table_markup $action_buttons_markup</div>";
            
            return $all_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
        //return "<table><tr><th>test</th><th>more</th></tr><tr><td>abc</td><td>123</td></tr></table>";
    }
    
    private function getUserProfileDashinfoMarkup()
    {
        try
        {
            global $user;
            $userprofile = $this->m_oUAH->getUserProfileBundle($user->uid);

            $core = $userprofile['core'];
            $srs = $userprofile['roles']['systemroles']['summary'];
            //$roles = $userprofile['roles'];
            
            $profile_markup = "";
            
            $profile_elems[] = "<div class='inline'><label for='first_nm'>First Name:</label><span id='first_nm' class='showvalue'>{$core['first_nm']}</span></div>";
            $profile_elems[] = "<div class='inline'><label for='last_nm'>Last Name:</label><span id='last_nm' class='showvalue'>{$core['last_nm']}</span></div>";
            $profile_elems[] = "<div class='inline'><label for='shortname'>Login Name:</label><span id='last_nm' class='showvalue'>{$core['shortname']}</span></div>";
            $profile_markup .= "<fieldset class='elem-inline'><legend>Who You Are</legend><div class='group-standard'>" 
                    . implode(" ", $profile_elems)
                    . "</div></fieldset>";            
            
            $contact_elems[] = "<div class='inline'><label for='email'>Email:</label><span id='email' class='showvalue'>{$core['main_email']}</span></div>";
            if(!empty($core['secondary_email']))
            {
                $contact_elems[] = "<div class='inline'><label for='secondary_email'>Alternate Email:</label><span id='secondary_email' class='showvalue'>{$core['secondary_email']}</span></div>";
            }
            if(!empty($core['primary_phone']))
            {
                $contact_elems[] = "<div class='inline'><label for='primary_phone'>Phone:</label><span id='primary_phone' class='showvalue'>{$core['primary_phone']}</span></div>";
            }
            if(!empty($core['secondary_phone']))
            {
                $contact_elems[] = "<div class='inline'><label for='secondary_phone'>Alternate Phone:</label><span id='secondary_phone' class='showvalue'>{$core['secondary_phone']}</span></div>";
            }
            $contact_elems[] = "<div class='inline'><label for='timezone'>Timezone:</label><span id='timezone' class='showvalue'>{$core['timezone']}</span></div>";
            $profile_markup .= "<fieldset class='elem-inline'><legend>Contact and Location Information</legend><div class='group-standard'>" 
                    . implode(" ", $contact_elems)
                    . "</div></fieldset>";    
            
            $roleandgroupinfo = $this->m_oMapHelper->getRolesAndGroupsBundle4Person($user->uid);
            $justgroupmarkupitems = [];
            foreach($roleandgroupinfo['groups'] as $groupid=>$groupinfo)
            {
                $groupname = $groupinfo['name'];
                $rolenames = [];
                foreach($groupinfo['roles'] as $roleid=>$rolename)
                {
                    $rolenames[] = $rolename;
                }
                $rolenames = implode(", ", $rolenames);
                //$rolenames = print_r($groupinfo['roles'],TRUE);
                $justgroupmarkupitems[] = "<span title='$rolenames'>$groupname</span>";
            }
            $groupmarkup = implode(", ", $justgroupmarkupitems);
            $profile_markup .= "<fieldset class='elem-inline'><legend title='The groups in which you are a member'>Group Membership Information</legend><div class='group-standard'>" 
                    . $groupmarkup
                    . "</div></fieldset>";
            
            if($srs['is_systemadmin'])
            {
                $rolet_markup = "System Admin";
            } else {
                $rolet_markup = "Normal";
            }
            $system_role_elems[] = "<div class='inline'><label for='accounttype'>Account Type:</label><span id='accounttype' class='showvalue'>$rolet_markup</span></div>";
            
            if($core['can_create_local_project_yn'] > 0)
            {
                $cclp_markup = "Yes";
            } else {
                $cclp_markup = "No";
            }
            $system_role_elems[] = "<div class='inline'><label for='can_create_local_project_yn'>Can create local projects:</label><span id='can_create_local_project_yn' class='showvalue'>$cclp_markup</span></div>";
            
            if($core['can_create_remote_project_yn'] > 0)
            {
                $ccrp_markup = "Yes";
            } else {
                $ccrp_markup = "No";
            }
            $system_role_elems[] = "<div class='inline'>"
                    . "<label for='can_create_remote_project_yn'>Can create proxy projects:</label>"
                    . "<span id='can_create_remote_project_yn' class='showvalue'>$ccrp_markup</span>"
                    . "</div>";
            
            $profile_markup .= "<fieldset class='elem-inline'><legend>Other Settings</legend><div class='group-standard'>" 
                    . implode(" ", $system_role_elems)
                    . "</div></fieldset>";            
            
            $markup = "<div class='dash-container dash-normal-fonts'>"
                    . "\n<div class='dash-80pct'><div class='dash-container'>" . $profile_markup . "</div></div>"
                    . "\n<div class='dash-action-buttons'><div class='dash-container'>[EMBED_MENU_HERE]</div></div>"
                    . "\n</div>";
            
            
            global $user;
            global $base_url;
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            $myurls = $this->m_urls_arr;
            $myurls['images'] = $base_url . '/' . $theme_path . '/images';
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => $myurls), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/node_modules/chart.js/dist/Chart.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            return $markup;
        } 
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $profile_markup = $this->getUserProfileDashinfoMarkup();
            $availability_markup = $this->getUserAvailabilityDashinfoMarkup();
            $selected_content_markup = $this->getSelectedBodyContentMarkup(FALSE, FALSE, $profile_markup, $availability_markup, "[EMBED_MENU_HERE]");
            return $this->getFormBodyContent($form, $html_classname_overrides, $selected_content_markup);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
