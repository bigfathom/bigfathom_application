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
 * Run report showing gantt for all workitems
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class GanttAcrossAllProjectsPage extends \bigfathom\ASimpleFormPage
{

    private $m_reftime_ar;
    private $m_oMapHelper;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/UtilityGeneralFormulas');
        module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
        module_load_include('php','bigfathom_core','core/MapHelper');
        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
        
        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();        
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
                
            $today_dt = date("Y-m-d", time());
            $now_dttm = date("Y-m-d H:i", time());
            
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
            $tableheader[] = array("OK","Is the owner utilization fitting within planned constraints?","formula");
            $tableheader[] = array("PID","ID of the project","formula");
            $tableheader[] = array("WID","ID of the workitem","formula");
            $tableheader[] = array("OID","ID of the person owning this workitem","formula");
            $tableheader[] = array("WSC","Workitem Status Code","formula");
            $tableheader[] = array("Start Date","The start date of the workitem or today, whichever is later","formula");
            $tableheader[] = array("End Date","The end date of the workitem","formula");
            $tableheader[] = array("All Days","Count all days in the bounded period","formula");
            $tableheader[] = array("R.Hours","Hours of work required in the bounded period to complete the workitem","formula");
            $tableheader[] = array("Average U%","Average utilization percent computed as weighted sum of (hours required for a work period)/(hours available for a work period) within the bounded date range","formula");
            $tableheader[] = array("Gantt","Work timing visualization","formula");

            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th class='nowrap' title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $personid_ar = $this->m_oMapHelper->getGroupMembersByPersonID();
            $bundle = $this->m_oMapHelper->getUtilizationDataBundle($personid_ar);

            $summary = $bundle['summary'];
            $summary_cannot_compute = $summary['cannot_compute'];
            if($summary_cannot_compute['count'] > 0)
            {
                $count = $summary_cannot_compute['count'];
                $reasons = $summary_cannot_compute['reasons'];
                $reason_count = count($reasons);
                $people = $summary_cannot_compute['people'];
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
            $all_utilrec_bypersonid = $bundle['by_personid'];
            $all_workitems = $bundle['lookup']['workitem'];
            $trows_ar = []; 
            
            $min_date = $summary['min_dt'];
            $max_date = $summary['max_dt'];
            $gantt_width = 500;
            $max_height = 20;
            if(!empty($max_date) && !empty($min_date))
            {
                $this->m_oGanttChartHelper = new \bigfathom\GanttChartHelper($min_date, $max_date, $gantt_width, $max_height);
            } else {
                $this->m_oGanttChartHelper = NULL;
            }

            foreach($all_workitems as $wid=>$wdetail)
            {
                
                //$projectid = $wdetail['owner_projectid'];
                $personid = $wdetail['owner_personid'];
                $projectid = $wdetail['owner_projectid'];
                $person_utilrec_ar = $all_utilrec_bypersonid[$personid];
                $project_root_yn =  $wdetail['project_root_yn'];
                $workitem_basetype = $wdetail['workitem_basetype'];

                $effective_start_dt = $wdetail['effective_start_dt'];
                $effective_end_dt = $wdetail['effective_end_dt'];
                
                //$daycount_detailinfo = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($min_date, $max_date, $personid, TRUE);
                $utilization4one_person = $person_utilrec_ar['smartbucket']->getComputedNumberData();
                $rowbundle = \bigfathom\UtilityFormatUtilizationData::getFormattedRowCellsBundle4WorkitemBoundedPeriod($wdetail, $utilization4one_person);

                $plain = $rowbundle['plain'];
                $formatted = $rowbundle['formatted'];
                $is_ok = $plain['is_ok'];

                $remaining_effort_hours = $plain['remaining_effort_hours'];
                $problem_periods_ar = $plain['problem_periods_ar'];
                
                $utilsortfactor = $wid;
                        
                //Gantt bar markup
                if($this->m_oGanttChartHelper == NULL)
                {
                    $typename = "none";
                    $moreclassnames = "";
                } else {
                    $typename = $this->m_oGanttChartHelper->getTypeNameForChart($workitem_basetype, $project_root_yn);
                    if(!$is_ok)
                    {
                        $moreclassnames = "utilization-toomuch";
                    } else {
                        $moreclassnames = "";
                    }
                }
                
                $effort_hours_est_locked_yn = $wdetail['effort_hours_est_locked_yn'];
                $actual_start_dt = $wdetail['actual_start_dt'];
                $actual_end_dt = $wdetail['actual_start_dt'];
                $planned_start_dt_locked_yn = $wdetail['planned_start_dt_locked_yn'];
                $planned_end_dt_locked_yn = $wdetail['planned_end_dt_locked_yn'];
                
                $est_flags=[];
                $est_flags['startdate'] = empty($actual_start_dt);
                $est_flags['enddate'] = empty($actual_end_dt);
                $est_flags['middle'] = !$effort_hours_est_locked_yn;
                $pin_flags=[];
                $pin_flags['startdate'] = $planned_start_dt_locked_yn || !empty($actual_start_dt);
                $pin_flags['enddate'] = $planned_end_dt_locked_yn || !empty($actual_end_dt);
                $pin_flags['middle'] = $effort_hours_est_locked_yn || (!empty($actual_start_dt) && !empty($actual_end_dt));
                if($this->m_oGanttChartHelper == NULL)
                {
                    $gantt_visual = "-- Missing Dates --";
                } else {
                    if($remaining_effort_hours == 0)
                    {
                        //Do not show problem areas in bars where there is no work remaining
                        $show_problem_periods_ar = [];
                    } else {
                        //Show the problem areas if any
                        $show_problem_periods_ar = $problem_periods_ar;
                    }
                    $gantt_visual = $this->m_oGanttChartHelper->getGanttBarMarkup($typename, $effective_start_dt, $effective_end_dt
                                                , $est_flags, $pin_flags, $moreclassnames, $show_problem_periods_ar);
                }
                //DebugHelper::debugPrintNeatly(array('$problem_periods_ar'=>$problem_periods_ar),FALSE,"LOOK wid=$wid $effective_start_dt prb stuff....",".... prb stuff");
                $startdtts = strtotime($effective_start_dt) * 1000 + $utilsortfactor;
                $gantt_markup = "[SORTNUM:$startdtts]$gantt_visual";

                $is_ok_markup = $formatted['is_ok_markup'];
                $pid_markup = "[SORTNUM:$projectid]".$formatted['project'];
                $wid_markup = "[SORTNUM:$wid]".$formatted['workitem'];
                $person_markup = "[SORTNUM:$personid]".$formatted['person'];
                $status_cd_markup = $formatted['wstatus'];
                $start_dt_markup = $formatted['start_dt'];
                $end_dt_markup = $formatted['end_dt'];
                $totaldaycount_markup = $formatted['totaldaycount'];
                $remaining_effort_hours_markup = $formatted['remaining_effort_hours'];
                $upct_markup = $formatted['avg_upct'];
                $justplainstuff[] = $plain;
                
                //Build the row
                $trows_ar[] = "\n<td>$is_ok_markup</td>"
                        . "<td>$pid_markup</td>"
                        . "<td>$wid_markup</td>"
                        . "<td>$person_markup</td>"
                        . "<td>$status_cd_markup</td>"
                        . "<td>$start_dt_markup</td>"
                        . "<td>$end_dt_markup</td>"
                        . "<td>$totaldaycount_markup</td>"
                        . "<td>$remaining_effort_hours_markup</td>"
                        . "<td>$upct_markup</td>"
                        . "<td>$gantt_markup</td>";
            }
            
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
