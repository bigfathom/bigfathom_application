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

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/ProjectBaselinePageHelper.php';

/**
 * This page presents the project baselines available in the system
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageProjectBaselinesPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights    = NULL;
    protected $m_oPageHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addprojbaseline';
        $urls_arr['edit'] = 'bigfathom/editprojbaseline';
        $urls_arr['view'] = 'bigfathom/viewprojbaseline';
        $urls_arr['delete'] = 'bigfathom/deleteprojbaseline';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['durationconsole'] = 'bigfathom/projects/workitems/duration';
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($this->m_is_systemdatatrustee)
        {
            $aDataRights='VAED';
        } else {
            $aDataRights='V';
        }
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aDataRights    = $aDataRights;
        
        $this->m_oPageHelper = new \bigfathom\ProjectBaselinePageHelper($urls_arr);
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * TODO REFACTOR THIS ONE SIGNIFICANTLY FOR MAINTAINABILITY
     */
    private function getOurFormattedDataBundle()
    {
        $bundle = $this->m_oMapHelper->getProjectBaselinesBundle($this->m_parent_projectid);
        
        $chart_stacknames = [];
        
        $people_lookup = $bundle['lookup']['people'];
        $project_baseline_lookup = $bundle['lookup']['project_baseline'];

        $chart_bundles_eachraw=[];
        
        $chart_data_ar = [];
        $count_chart_data_ar = [];
        $hours_chart_data_ar = [];
        $rows_ar = [];
        
        $datetime = new \DateTime();
        $TODAY_iso8601_created_dttm = $datetime->format(\DateTime::ATOM); // Updated ISO8601        
        
        $iso8601_created_dttm = NULL;
        $MIN_iso8601_created_dttm = NULL;
        $MAX_iso8601_created_dttm = NULL;
      
        //Initialize these now otherwise errors later on blank data situation
        $count_unstarted_workitems = 0;
        $count_started_open_workitems = 0;
        $count_closed_workitems = 0;
        $ere_unstarted_workitems = 0;
        $ere_started_open_workitems = 0;
        $ere_closed_workitems = 0;
        $act_worked_started_open_workitems = 0;
        $act_worked_closed_workitems = 0;
        $est_worked_started_open_workitems = 0;
        $est_worked_closed_workitems = 0;
        
        drupal_set_message("LOOK 33333 DEBUGING");

        foreach($project_baseline_lookup as $project_baselineid=>$record)
        {

            $shortname = $record['shortname'];
            $blurb_tx = $record['comment_tx'];
            $member_workitems = $record['maps']['workitems'];
            $summary_maps = $member_workitems['summary']['maps'];

            $wids_list = array_keys($member_workitems['detail']);
            $created_by_personid = $record['created_by_personid'];
            $updated_dt = $record['updated_dt'];
            $created_dt = $record['created_dt'];

            $total_estimated_effort_remaining = $member_workitems['summary']['total_remaining_effort_hours'];
            $count_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['count'] : 0;
            $count_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['count'] : 0;
            $count_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['count'] : 0;

            $ere_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['remaining_effort_hours'] : 0;
            $ere_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['remaining_effort_hours'] : 0;
            $ere_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['remaining_effort_hours'] : 0;

            //$act_worked_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['effort_hours_worked_act'] : 0;
            $act_worked_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['effort_hours_worked_act'] : 0;
            $act_worked_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['effort_hours_worked_act'] : 0;
            
            //$est_worked_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['effort_hours_worked_est'] : 0;
            $est_worked_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['effort_hours_worked_est'] : 0;
            $est_worked_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['effort_hours_worked_est'] : 0;
            
            $created_by_person_markup = $people_lookup[$created_by_personid]['fullname'];

            $widcount = count($wids_list);
            sort($wids_list);
            $wids_tx = implode(", ", $wids_list);

            $wids_markup = "[SORTNUM:$widcount]<span title='Total $widcount workitems'>$wids_tx</span>";

            if($updated_dt !== $created_dt)
            {
                $created_markup = "<span title='Edited $updated_dt'>$created_dt</span>";
            } else {
                $created_markup = "<span title='Never edited'>$created_dt</span>";
            }

            $blurb_tx_len = strlen($blurb_tx);
            if($blurb_tx_len > 256)
            {
                $blurb_tx_markup = substr($blurb_tx, 0,256) . '...';
            } else {
                $blurb_tx_markup = $blurb_tx;
            }

            $shortname_markup = "$shortname";

            if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
            {
                $sViewMarkup = '';
            } else {
                //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('projbaselineid'=>$projbaseline_id)));
                $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('projbaselineid'=>$project_baselineid)));
                $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                $sViewMarkup = "<a title='view #{$project_baselineid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
            }
            if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
            {
                $sEditMarkup = '';
            } else {
                $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('projbaselineid'=>$project_baselineid)));
                $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                $sEditMarkup = "<a title='edit #{$project_baselineid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
            }
            if(strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
            {
                $sDeleteMarkup = '';
            } else {
                //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('projbaselineid'=>$projbaseline_id)));
                $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('projbaselineid'=>$project_baselineid)));
                $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                $sDeleteMarkup = "<a title='delete #{$project_baselineid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
            }

            $datetime = new \DateTime($created_dt);
            $iso8601_created_dttm = $datetime->format(\DateTime::ATOM); // Updated ISO8601
            $iso8601_created_JUSTDATE = substr($iso8601_created_dttm,0,10);
            
            $count_chart_data_ar['unstarted'][] = array('x'=>$iso8601_created_dttm, 'y'=>$count_unstarted_workitems);
            $count_chart_data_ar['started'][] = array('x'=>$iso8601_created_dttm, 'y'=>$count_started_open_workitems);
            $count_chart_data_ar['closed'][] = array('x'=>$iso8601_created_dttm, 'y'=>$count_closed_workitems);
            
            $hours_chart_data_ar['unstarted_ere'][] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_unstarted_workitems);
            $hours_chart_data_ar['started_ere'][] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_started_open_workitems);
            $hours_chart_data_ar['started_act_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_started_open_workitems);
            $hours_chart_data_ar['started_est_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_started_open_workitems);
            $hours_chart_data_ar['closed_act_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_closed_workitems);
            $hours_chart_data_ar['closed_est_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_closed_workitems);
            
            $chart_stacknames[] = $shortname_markup;
            
            //START EACHRAW
            $count_chart_data_eachraw['unstarted'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_unstarted_workitems);
            $count_chart_data_eachraw['started'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_started_open_workitems);
            $count_chart_data_eachraw['closed'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_closed_workitems);
            
            $hours_chart_data_eachraw['unstarted_ere'] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_unstarted_workitems);
            $hours_chart_data_eachraw['started_ere'] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_started_open_workitems);
            $hours_chart_data_eachraw['started_act_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_started_open_workitems);
            $hours_chart_data_eachraw['started_est_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_started_open_workitems);
            $hours_chart_data_eachraw['closed_act_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_closed_workitems);
            $hours_chart_data_eachraw['closed_est_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_closed_workitems);

            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm] = [];
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['chart_stackname'] = $shortname_markup;
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['count_chart_data_eachraw'] = $count_chart_data_eachraw;
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['hours_chart_data_eachraw'] = $hours_chart_data_eachraw;
            //END EACH RAW        
            
            $unstarted_markup = "[SORTNUM:$count_unstarted_workitems]<span title='estimated remaining effort $ere_unstarted_workitems hours'>$count_unstarted_workitems</span>";
            $started_markup = "[SORTNUM:$count_started_open_workitems]<span title='estimated remaining effort $ere_started_open_workitems hours'>$count_started_open_workitems</span>";
            $closed_markup = "[SORTNUM:$count_closed_workitems]<span title='estimated remaining effort $ere_closed_workitems hours'>$count_closed_workitems</span>";
         
            if(empty($MIN_iso8601_created_dttm) || $MIN_iso8601_created_dttm > $iso8601_created_dttm)
            {
                $MIN_iso8601_created_dttm = $iso8601_created_dttm;
            }
            
            $rows_ar[] = '<tr><td>'
                    .$project_baselineid.'</td><td>'
                    .$shortname_markup.'</td><td>'
                    .$blurb_tx_markup.'</td><td>'
                    .$created_by_person_markup.'</td><td>'
                    .$total_estimated_effort_remaining.'</td><td>'
                    .$unstarted_markup.'</td><td>'
                    .$started_markup.'</td><td>'
                    .$closed_markup.'</td><td>'
                    .$wids_markup.'</td><td>'
                    .$created_markup.'</td>'
                    .'<td class="action-options">'
                    . $sViewMarkup.' '
                    . $sEditMarkup.' '
                    . $sDeleteMarkup.'</td>'
                    .'</tr>';
        }
        if(empty($MAX_iso8601_created_dttm) || $MAX_iso8601_created_dttm < $iso8601_created_dttm)
        {
            $MAX_iso8601_created_dttm = $iso8601_created_dttm;
            $MAX_iso8601_created_JUSTDATE = substr($MAX_iso8601_created_dttm,0,10);
        }
        $MIN_iso8601_created_JUSTDATE = substr($MIN_iso8601_created_dttm,0,10);
        
        //Add the current values to the data series now IF we do not already have a baseline today
        $TODAY_iso8601_created_JUSTDATE = substr($TODAY_iso8601_created_dttm,0,10);
        if(empty($MAX_iso8601_created_dttm) || $MAX_iso8601_created_JUSTDATE < $TODAY_iso8601_created_JUSTDATE)
        {
            $iso8601_created_dttm = $TODAY_iso8601_created_dttm;
            $iso8601_created_JUSTDATE = substr($iso8601_created_dttm,0,10);
            
            $current_values = $bundle['lookup']['current_values'];
            $member_workitems = $current_values['baseline']['maps']['workitems'];
            $summary_maps = $member_workitems['summary']['maps'];
            $cv_total_estimated_effort_remaining = $member_workitems['summary']['total_remaining_effort_hours'];
            $cv_count_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['count'] : 0;
            $cv_count_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['count'] : 0;
            $cv_count_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['count'] : 0;

            $cv_ere_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['remaining_effort_hours'] : 0;
            $cv_ere_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['remaining_effort_hours'] : 0;
            //$cv_ere_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['remaining_effort_hours'] : 0;

            //$act_worked_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['effort_hours_worked_act'] : 0;
            $cv_act_worked_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['effort_hours_worked_act'] : 0;
            $cv_act_worked_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['effort_hours_worked_act'] : 0;

            //$cv_est_worked_unstarted_workitems = isset($summary_maps['workstarted_yn2info'][0]) ? $summary_maps['workstarted_yn2info'][0]['effort_hours_worked_est'] : 0;
            $cv_est_worked_started_open_workitems = isset($summary_maps['started_and_open_yn2info'][1]) ? $summary_maps['started_and_open_yn2info'][1]['effort_hours_worked_est'] : 0;
            $cv_est_worked_closed_workitems = isset($summary_maps['terminal_yn2info'][1]) ? $summary_maps['terminal_yn2info'][1]['effort_hours_worked_est'] : 0;

            $count_chart_data_ar['unstarted'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_count_unstarted_workitems);
            $count_chart_data_ar['started'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_count_started_open_workitems);
            $count_chart_data_ar['closed'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_count_closed_workitems);

            $hours_chart_data_ar['unstarted_ere'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_ere_unstarted_workitems);
            $hours_chart_data_ar['started_ere'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_ere_started_open_workitems);
            $hours_chart_data_ar['started_act_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_act_worked_started_open_workitems);
            $hours_chart_data_ar['started_est_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_est_worked_started_open_workitems);
            $hours_chart_data_ar['closed_act_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_act_worked_closed_workitems);
            $hours_chart_data_ar['closed_est_worked'][] = array('x'=>$iso8601_created_dttm, 'y'=>$cv_est_worked_closed_workitems);

            $chart_stacknames[] = 'CURRENT VALUES';
            
            //START EACHRAW
            $count_chart_data_eachraw['unstarted'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_unstarted_workitems);
            $count_chart_data_eachraw['started'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_started_open_workitems);
            $count_chart_data_eachraw['closed'] = array('x'=>$iso8601_created_dttm, 'y'=>$count_closed_workitems);
            
            $hours_chart_data_eachraw['unstarted_ere'] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_unstarted_workitems);
            $hours_chart_data_eachraw['started_ere'] = array('x'=>$iso8601_created_dttm, 'y'=>$ere_started_open_workitems);
            $hours_chart_data_eachraw['started_act_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_started_open_workitems);
            $hours_chart_data_eachraw['started_est_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_started_open_workitems);
            $hours_chart_data_eachraw['closed_act_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$act_worked_closed_workitems);
            $hours_chart_data_eachraw['closed_est_worked'] = array('x'=>$iso8601_created_dttm, 'y'=>$est_worked_closed_workitems);

            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm] = [];
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['chart_stackname'] = 'CURRENT VALUES';
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['count_chart_data_eachraw'] = $count_chart_data_eachraw;
            $chart_bundles_eachraw[$iso8601_created_JUSTDATE][$iso8601_created_dttm]['hours_chart_data_eachraw'] = $hours_chart_data_eachraw;
            //END EACH RAW               
            
        }
        
        //Create chart_data_ar
        $chart_stacknames = [];
        $count_chart_data_ar = [];
        $hours_chart_data_ar = [];
        foreach($chart_bundles_eachraw as $iso8601_created_JUSTDATE=>$baselines_in_day)
        {
            $count_baselines_in_day = count($baselines_in_day);
            if($count_baselines_in_day == 1)
            {
                //Simple no average needed
                foreach($baselines_in_day as $iso8601_created_dttm=>$one_day_bundle)
                {
                    $chart_stacknames[] = $one_day_bundle['chart_stackname'];
                    $count_chart_data_eachraw = $one_day_bundle['count_chart_data_eachraw'];
                    $hours_chart_data_eachraw = $one_day_bundle['hours_chart_data_eachraw'];
                    
                    foreach($count_chart_data_eachraw as $key=>$value)
                    {
                        $count_chart_data_ar[$key][] = $value;
                    }
                    foreach($hours_chart_data_eachraw as $key=>$value)
                    {
                        $hours_chart_data_ar[$key][] = $value;
                    }
                }
            } else {
                //Average the baselines withing one day together
                $ar_chart_stackname = [];
                $ar_sumy_count_chart_data = [];
                $ar_sumy_hours_chart_data = [];
                
                $just_oneday_keys = array_keys($baselines_in_day);
                foreach($just_oneday_keys as $oneday_key)
                {
                    $one_day_bundle = $baselines_in_day[$oneday_key];
                    $ar_chart_stackname[] = $one_day_bundle["chart_stackname"];
                    $count_chart_data_eachraw = $one_day_bundle['count_chart_data_eachraw'];
                    $hours_chart_data_eachraw = $one_day_bundle['hours_chart_data_eachraw'];
                    
                    foreach($count_chart_data_eachraw as $key=>$value)
                    {
                        if(!isset($ar_sumy_count_chart_data[$key]))
                        {
                            $ar_sumy_count_chart_data[$key] = $value;
                        } else {
                            $ar_sumy_count_chart_data[$key]['y'] += $value['y'];
                        }
                    }
                    foreach($hours_chart_data_eachraw as $key=>$value)
                    {
                        if(!isset($ar_sumy_hours_chart_data[$key]))
                        {
                            $ar_sumy_hours_chart_data[$key] = $value;
                        } else {
                            $ar_sumy_hours_chart_data[$key]['y'] += $value['y'];
                        }
                    }
                }

                //Now create final records for plot
                $stack_name = "Average of " . implode(" and ", $ar_chart_stackname);
                $chart_stacknames[] = $stack_name;
                foreach($ar_sumy_count_chart_data as $key=>$value)
                {
                    $sumy = $value['y'];
                    $avgy = $sumy / $count_baselines_in_day;
                    $value['y'] = $avgy;
                    $count_chart_data_ar[$key][] = $value;
                }
                
                foreach($ar_sumy_hours_chart_data as $key=>$value)
                {
                    $sumy = $value['y'];
                    $avgy = $sumy / $count_baselines_in_day;
                    $value['y'] = $avgy;
                    $hours_chart_data_ar[$key][] = $value;
                }

            }
        }
        
        $chart_data_ar['count'] = $count_chart_data_ar;
        $chart_data_ar['hours'] = $hours_chart_data_ar;

        $bundle['table_rows'] = $rows_ar;
        $bundle['chart_data'] = $chart_data_ar;
        $bundle['chart_stacknames'] = $chart_stacknames;
    //\bigfathom\DebugHelper::showNeatMarkup($bundle,"LOOK our bundle");
        return $bundle;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'projbaseline-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            $formatted_bundle = $this->getOurFormattedDataBundle();
            $rows_ar = $formatted_bundle['table_rows'];

            $rows_markup = implode("\n", $rows_ar);
            
            $mychartdata_raw = $formatted_bundle['chart_data'];
            
            $mychartdata_json = json_encode($mychartdata_raw);
            $mychart_stacknames = $formatted_bundle['chart_stacknames'];
            
            //Now, construct the content
            global $user;
            global $base_url;
            drupal_add_js(array('personid'=>$user->uid
                    , 'mychartdata'=>$mychartdata_json
                    , 'mychart_stacknames'=>$mychart_stacknames
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images'))
                    , 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/node_modules/moment/moment.js");
            drupal_add_js("$base_url/$theme_path/node_modules/chart.js/dist/Chart.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageProjectBaselineHelper.js");
            
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['visualization-container']))
            {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if(!isset($html_classname_overrides['table-container']))
            {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if(!isset($html_classname_overrides['container-inline']))
            {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if(!isset($html_classname_overrides['action-button']))
            {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            
            $row_count = count($rows_ar);    
            
            module_load_include('php','bigfathom_core','snippets/SnippetHelper');
            $oSnippetHelper = new \bigfathom\SnippetHelper();
            $chart_markup = $oSnippetHelper->getHtmlSnippet("baseline_charts_panel"); 
            $form['data_entry_area1']['overview_info']['matrix'] = array('#type' => 'item'
                    , '#prefix' => '<div class="chart_area">'
                    , '#suffix' => '</div>'
                    , '#markup' => $chart_markup);  
            

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th datatype="integer" class="nowrap" title="Unique identifier of this baseline">'.t('ID').'</th>'
                                . '<th datatype="text" class="nowrap" title="Name of this baseline">'.t('Name').'</th>'
                                . '<th datatype="text" class="nowrap" title="A short description of the baseline">'.t('Description').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Person that created this baseline">'.t('Creator').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Total hours Estimated Remaining Effort">'.t('ERE').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Number of Unstarted Workitems">'.t('UW').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Number of Started Workitems">'.t('SW').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Number of Closed Workitems">'.t('CW').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Workitems in this baseline">'.t('Workitems').'</th>'
                                . '<th datatype="datetime" class="nowrap" title="When this baseline snapshot was created">'.t('Created').'</th>'
                                . '<th datatype="html" class="nowrap action-options">' . t('Action Options') . '</th>'
                                . '</tr>'
                                . '</thead>'
                                . '<tbody>'
                                . $rows_markup
                                .  '</tbody>'
                                . '</table>'
                                . '<br>');

            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    //<i class="fa fa-camera" aria-hidden="true"></i> 
                    $initial_button_markup = l('CAMERA_ICON Create New Baseline Snapshot',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button','title'=>'Create a baseline snapshot of current project data'))
                            );
                    $final_button_markup = str_replace('CAMERA_ICON', '<i class="fa fa-camera" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addprojbaseline'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }

            if (isset($this->m_urls_arr['durationconsole'])) {
                //fa-calendar-o
                $initial_button_markup = l('ICON_DURATION Jump to Duration Table', $this->m_urls_arr['durationconsole']
                                , array('attributes'=>array('class'=>'action-button','title'=>'See the current time and duration detail for all the workitems'))
                        );
                $final_button_markup = str_replace('ICON_DURATION', '<i class="fa fa-table" aria-hidden="true"></i>', $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['durationconsole'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                        , '#markup' => $exit_link_markup);
            }

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
