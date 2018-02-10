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

module_load_include('php','bigfathom_core','form/ASimpleFormPage');

/**
 * Run report about person availability
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class PersonAvailabilityPage extends \bigfathom\ASimpleFormPage
{

    private $m_reftime_ar;
    private $m_oMapHelper;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/UtilityGeneralFormulas');
        module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
        module_load_include('php','bigfathom_core','core/MapHelper');
        
        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();        
    }
    
    private function getUserAvailabilityDashinfoMarkup()
    {
        try
        {
            global $user;
            //$bundle = $this->m_oMapHelper->getPersonAvailabilityBundle($user->uid);
            
            $personid_ar = $this->m_oMapHelper->getGroupMembersByPersonID();
            $bundle = $this->m_oMapHelper->getPersonAvailabilityBundle($personid_ar);
            
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
                $personid = $record['personid'];
                $fullname = $record['first_nm'] . " " . $record['last_nm'];
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
                
                $person_markup = "[SORTSTR:{$fullname}]<span title='#$personid'>$fullname</span>";
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
                $type_markup = "[SORTNUM:".$type_info['sort']."]<span class='$date_classname' title='".$type_info['tooltip']."'>".$type_info['name']."</span>";
                
                $personcols_markup = "<td>$person_markup</td>";
                $datecols_markup = "<td>$start_dt_markup</td>"
                        . "<td>$end_dt_markup</td>";
                $rows[] = "<tr>"
                        . $personcols_markup
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
                        . "<td>$updated_markup</td>"
                        . "<td>$comment_markup</td>"
                        . "</tr>";
            }
            
            $tableheader = [];
            $tableheader[] = array("Person","Who has this declared availability","formula");
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
            $tableheader[] = array("Updated","Date of most recent update","formula");
            $tableheader[] = array("Notes","Comment, if any, for the bounded period.","formula");

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
                    $add_link_markup = l('Define New Availability Period',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'small-action-button', 'title'=>'Create a new custom availability entry for your profile'))
                            );
                    $action_buttons_markup = $add_link_markup;
                }
            }    
            
            $all_markup = "<div class='dash-normal-fonts'>$table_markup $action_buttons_markup</div>";
            
            return $all_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
        //return "<table><tr><th>test</th><th>more</th></tr><tr><td>abc</td><td>123</td></tr></table>";
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;
                
            global $user;
            global $base_url;
            
            $main_tablename = 'grid-workitem-duration';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $table_markup = $this->getUserAvailabilityDashinfoMarkup();
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_markup);

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
