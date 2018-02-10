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
module_load_include('php','bigfathom_core','core/WorkApportionmentHelper');

/**
 * Run report about person utilization
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class PersonUtilizationPage extends \bigfathom\ASimpleFormPage
{

    private $m_reftime_ar;
    private $m_oMapHelper;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/UtilityGeneralFormulas');
        module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
        module_load_include('php','bigfathom_core','core/MapHelper');
        module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');

        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();  
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
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
                
            global $user;
            global $base_url;
            
            $today_dt = date("Y-m-d", time());
            $now_dttm = date("Y-m-d H:i", time());
            $include_today = "$today_dt 10:00" > $now_dttm; //Only include if we are running in the morning

            $main_tablename = 'grid-workitem-duration';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    , 'myurls' => array('images' => $base_url .'/'. $theme_path.'/images'))
                    , 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $form["data_entry_area1"]['context_info'] = array(
                '#type' => 'item',
                '#markup' => "<div class='pagetop-blurb'><p>Results computed as of $now_dttm</p></div>",
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );
            
            //Create the static table
            $tableheader = [];
            $tableheader[] = array("Classification","A summary interpretation of utilization for the bounded period","formula");
            $tableheader[] = array("Person","The person to which the bounded period applies","formula");
            $tableheader[] = array("Start Date","The start date of the bounded period","formula");
            $tableheader[] = array("End Date","The end date of the bounded period","formula");
            $tableheader[] = array("All Days","Count all days in the bounded period","formula");
            $tableheader[] = array("A.Days","Count only the days the person is available for work in the bounded period","formula");
            $tableheader[] = array("R.Hours","Hours of work required in the bounded period","formula");
            $tableheader[] = array("A.Hours","Hours available to work in the bounded period","formula");
            $tableheader[] = array("R.Hours/Day","Average hours per workday the person would need to work","formula");
            $tableheader[] = array("A.Hours/Day","Average hours per workday the person is available to work","formula");
            $tableheader[] = array("U%","Utilization percent computed as (hours required for period/hours available for period)","formula");
            $tableheader[] = array("Projects","IDs of the projects overlapping the bounded period","formula");
            $tableheader[] = array("Workitems","IDs of the workitems overlapping the bounded period","formula");
            $tableheader[] = array("Comment","Relevant comment, if any, about availability in the bounded period","formula");

            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th class='nowrap' title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $personid_ar = $this->m_oMapHelper->getGroupMembersByPersonID();
            $util_bundle = $this->m_oMapHelper->getShortcutUtilizationAndGapsDataBundle($personid_ar);
            $summary_cannot_compute = $util_bundle['summary']['cannot_compute'];
            if($summary_cannot_compute['count'] > 0)
            {
                $reasons = $summary_cannot_compute['reasons'];
                $reason_count = count($reasons);
                $workitems = $summary_cannot_compute['workitems'];
                if(count($workitems)>1)
                {
                    $wplural = "s";
                } else {
                    $wplural = "";
                }
                if($reason_count > 1)
                {
                    $markup = "Could not compute true utilization for " 
                            . count($workitems) . " workitem$wplural (id#$wplural " . implode(", ", $workitems) . ") for "
                            . count($reasons) . " reasons (" . implode(" and ", $reasons) . ")";
                } else {
                    $markup = "Could not compute true utilization for " 
                            . count($workitems) . " workitem$wplural (id#$wplural " . implode(", ", $workitems) . ") because " . implode(" ", $reasons);
                }
                drupal_set_message($markup,"error");
            }

            $merged_bundle =  $this->m_oWAH->getMergedIntervalUtilizationByPerson($personid_ar);
            //DebugHelper::showNeatMarkup(array('##$merged_bundle'=>$merged_bundle),"LOOK merged_bundle????");
            
            $map_personid2merged_util_rows = $merged_bundle['by_person'];
            
            $trows_ar = []; 
            foreach($map_personid2merged_util_rows as $personid=>$merged_util_rows)
            {
                //DebugHelper::showNeatMarkup($merged_util_rows,"LOOK ROWS FOR personid=$personid");
                $person_markup = $personid;
                $ismash_ar = isset($merged_util_rows['map']['interval_smash']) ? $merged_util_rows['map']['interval_smash'] : [];
                foreach($ismash_ar as $interval_smash)
                {
                    $formatted_rowcontent = \bigfathom\UtilityFormatUtilizationData::getFormattedRowCells4PersonInterval($interval_smash);
                    //DebugHelper::showNeatMarkup($formatted_rowcontent,"LOOK formatted_row");
                    
                    $end_dt = $interval_smash['edt'];
                    
                    if($end_dt > $today_dt || ($include_today && $end_dt == $today_dt))
                    {
                        $sdt_markup = $formatted_rowcontent['start_dt'];
                        $edt_markup = $formatted_rowcontent['end_dt'];
                        $assessment_markup = $formatted_rowcontent['assessment_tx'];
                        $totaldaycount_markup = $formatted_rowcontent['totaldaycount'];			
                        $availabledaycount_markup = $formatted_rowcontent['availabledaycount'];		
                        $remaining_effort_hours_markup = $formatted_rowcontent['remaining_effort_hours'];
                        $available_hours_markup = $formatted_rowcontent['available_hours'];			
                        $need_hoursperday_markup = $formatted_rowcontent['need_hoursperday'];			
                        $available_hoursperday_markup = $formatted_rowcontent['available_hoursperday'];	
                        $upct_markup = $formatted_rowcontent['upct'];						
                        $pids_markup = $formatted_rowcontent['pids'];						
                        $wids_markup = $formatted_rowcontent['wids'];						
                        $comment_markup = $formatted_rowcontent['comment'];			

                        $trows_ar[] = "\n<td>$assessment_markup</td>"
                                . "<td>$person_markup</td>"
                                . "<td>$sdt_markup</td>"
                                . "<td>$edt_markup</td>"
                                . "<td>$totaldaycount_markup</td>"
                                . "<td>$availabledaycount_markup</td>"
                                . "<td>$remaining_effort_hours_markup</td>"
                                . "<td>$available_hours_markup</td>"
                                . "<td>$need_hoursperday_markup</td>"
                                . "<td>$available_hoursperday_markup</td>"
                                . "<td>$upct_markup</td>"
                                . "<td>$pids_markup</td>"
                                . "<td>$wids_markup</td>"
                                . "<td>$comment_markup</td>";
                    }
                }
            }
            
            /*
            $all = $bundle['smartbucketinfo']['by_personid'];//$bundle['by_personid'];
            foreach($all as $personid=>$utilization)
            {
                $persondetail = $all_utilrec_bypersonid[$personid];
                $fullname = $persondetail['fullname'];
                $person_markup = "<span title='#$personid'>$fullname</span>";
                
                DebugHelper::showNeatMarkup($utilization, "LOOK utilization of personid=$personid");
                
                foreach($utilization as $composite_utilrec)
                {
                    $plain = $composite_utilrec['plain'];
                    $formatted_rowcontent = $composite_utilrec['formatted'];
                    $assessment_markup = $formatted_rowcontent['assessment_tx'];			
                    $start_dt = $plain['start_dt'];				
                    $end_dt = $plain['end_dt'];
                    if($end_dt > $today_dt || ($include_today && $end_dt == $today_dt))
                    {
                        $totaldaycount_markup = $formatted_rowcontent['totaldaycount'];			
                        $normalworkdaycount_markup =$formatted_rowcontent['normalworkdaycount'];		
                        $availabledaycount_markup = $formatted_rowcontent['availabledaycount'];		
                        $remaining_effort_hours_markup = $formatted_rowcontent['remaining_effort_hours'];
                        $available_hours_markup = $formatted_rowcontent['available_hours'];			
                        $need_hoursperday_markup = $formatted_rowcontent['need_hoursperday'];			
                        $available_hoursperday_markup = $formatted_rowcontent['available_hoursperday'];	
                        $upct_markup = $formatted_rowcontent['upct'];						
                        $pids_markup = $formatted_rowcontent['pids'];						
                        $wids_markup = $formatted_rowcontent['wids'];						
                        $comment_markup = $formatted_rowcontent['comment'];			

                        $trows_ar[] = "\n<td>$assessment_markup</td>"
                                . "<td>$person_markup</td>"
                                . "<td>$start_dt</td>"
                                . "<td>$end_dt</td>"
                                . "<td>$totaldaycount_markup</td>"
                                . "<td>$normalworkdaycount_markup</td>"
                                . "<td>$availabledaycount_markup</td>"
                                . "<td>$remaining_effort_hours_markup</td>"
                                . "<td>$available_hours_markup</td>"
                                . "<td>$need_hoursperday_markup</td>"
                                . "<td>$available_hoursperday_markup</td>"
                                . "<td>$upct_markup</td>"
                                . "<td>$pids_markup</td>"
                                . "<td>$wids_markup</td>"
                                . "<td>$comment_markup</td>";
                    }
                }
            }
             * 
             */
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid"><thead>' 
                    . $th_markup 
                    . '</thead><tbody>'
                    . $trows_markup 
                    . '</tbody></table>';
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_markup);

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
