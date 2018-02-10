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
 * Run report about test cases in one project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TestCaseMappingOneProjectPage extends \bigfathom\ASimpleFormPage
{

    private $m_oMapHelper = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/UtilityGeneralFormulas');
        module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
        module_load_include('php','bigfathom_core','core/MapHelper');
        module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
        module_load_include('php','bigfathom_core','core/ProjectInsight');
        
        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        $urls_arr['view'] = 'bigfathom/viewtestcase';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        
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
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        if(empty($this->m_projectid))
        {
            throw new \Exception("Must already have a project selected!");
        }
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
            
            $now_dttm = date("Y-m-d H:i", time());
            
            global $user;
            global $base_url;
            
            $main_tablename = 'grid-project-testcase-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $test_notready_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('test_notready');
            $test_ready_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('test_ready');
            $test_passed_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('test_passed');
            $test_failed_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('test_failed');
                
            $bundle = $this->m_oMapHelper->getTestCasesBundle($this->m_projectid);
//DebugHelper::debugPrintNeatly(array('##$bundle'=>$bundle),FALSE,"LOOK MAIN BUNDLE");                
            
            $tracker_map_wid2testcaseid = $bundle['tracker_map_wid2testcaseid'];
            $tracker_map_testcaseid2wid = $bundle['tracker_map_testcaseid2wid'];
            sort($tracker_map_testcaseid2wid);
            
            $tc_status_lookup = $bundle['lookup']['tc_statuses'];
            $wi_status_lookup = $bundle['lookup']['wi_statuses'];
            
            $testcase_lookup = $bundle['lookup']['testcases'];
            $workitem_lookup = $bundle['lookup']['workitems'];
            
            $analysis =  $bundle['analysis'];

            $ar_mapped_workitem_direct = $analysis['mapped']['workitem']['direct']['workitems'];
            $ar_mapped_workitem_indirect = $analysis['mapped']['workitem']['indirect']['workitems'];
            $ar_unmapped_workitem = $analysis['unmapped']['workitem']['workitems'];
            
            $count_mapped_workitem_direct_done = $analysis['mapped']['workitem']['direct']['done']['count'];
            $count_mapped_workitem_indirect_done = $analysis['mapped']['workitem']['indirect']['done']['count'];
            $count_unmapped_workitem_done = $analysis['unmapped']['workitem']['done']['count'];
            $count_mapped_workitem_direct_notdone = $analysis['mapped']['workitem']['direct']['notdone']['count'];
            $count_mapped_workitem_indirect_notdone = $analysis['mapped']['workitem']['indirect']['notdone']['count'];
            $count_unmapped_workitem_notdone = $analysis['unmapped']['workitem']['notdone']['count'];
            $pct_complete_by_tcid = $analysis['pct_complete_by_tcid'];

            $heirarchy_link_by_wid = [];
            $ar_markup_mapped_workitem_direct = [];
            $ar_markup_mapped_workitem_indirect = [];
            $ar_markup_unmapped_workitem = [];
            $ar_markup_tracker_workitem = [];
            foreach($workitem_lookup as $workitemid=>$winfo)
            {
                if(isset($ar_mapped_workitem_tracker[$workitemid]))
                {
                    $ar_markup_tracker_workitem[$workitemid] = $workitemid;
                }
                $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                        , array('query'=>array('projectid'=>($this->m_projectid), 'jump2workitemid'=>$workitemid)));
                $sHierarchyMarkup = "<a "
                    . " title='view dependencies for workitem#{$workitemid} in project#{$this->m_projectid}' "
                    . " href='$hierarchy_page_url'>$workitemid</a>";
                $heirarchy_link_by_wid[$workitemid]['markup'] = $sHierarchyMarkup;
                if(isset($ar_mapped_workitem_direct[$workitemid]))
                {
                    $ar_markup_mapped_workitem_direct[$workitemid] = $sHierarchyMarkup;
                }
                if(isset($ar_mapped_workitem_indirect[$workitemid]))
                {
                    $ar_markup_mapped_workitem_indirect[$workitemid] = $sHierarchyMarkup;
                }
                if(isset($ar_unmapped_workitem[$workitemid]))
                {
                    $ar_markup_unmapped_workitem[$workitemid] = $sHierarchyMarkup;
                }
            }
            
            $markup_mapped_workitem_direct = implode(', ', $ar_markup_mapped_workitem_direct);
            $markup_mapped_workitem_indirect = implode(', ', $ar_markup_mapped_workitem_indirect);
            $markup_unmapped_workitem = implode(', ', $ar_markup_unmapped_workitem);
            $markup_tracker_workitem = implode(', ', $tracker_map_testcaseid2wid);
            
            if(count($ar_unmapped_workitem) > 0)
            {
                $classname_possible_concern = 'possible-concern';
            } else {
                $classname_possible_concern = '';
            }
            
            $count_tracker_workitem_done = NULL;  //TODO
            $count_tracker_workitem_notdone = NULL;  //TODO
            
            $overview_info = "<table class='simple-rows'>"
                    . "<tr><td class='empty-cell' colspan='2'></td>"
                        . "<th title='Count of workitems that are in a terminal state'>Done</th>"
                        . "<th title='Count of workitems that are not in a terminal state'>Not Done</th>"
                        . "<th title='The ID(s) of the workitems counted in the row'>Workitem ID(s)</th></tr>"
                    . "<tr><th rowspan='2' title='These workitems have been mapped to one or more test cases'>Mapped Workitems</th>"
                        . "<th title='Workitems in this row have been directly mapped to one or more test cases'>Direct</th>"
                        . "<td>$count_mapped_workitem_direct_done</td>"
                        . "<td>$count_mapped_workitem_direct_notdone</td>"
                        . "<td>$markup_mapped_workitem_direct</td></tr>"
                    . "<tr><th title='Workitems in this row have not been directly mapped to any test case but are an ancestor of one or more mapped workitems'>Indirect</th>"
                        . "<td>$count_mapped_workitem_indirect_done</td>"
                        . "<td>$count_mapped_workitem_indirect_notdone</td>"
                        . "<td>$markup_mapped_workitem_indirect</td></tr>"
                    . "<tr><th class='' title='These workitems track the test case effort' colspan=2'>Tracker Workitems</th>"
                        . "<td class=''>$count_tracker_workitem_done</td>"
                        . "<td class=''>$count_tracker_workitem_notdone</td>"
                        . "<td class=''>$markup_tracker_workitem</td></tr>"
                    . "<tr><th class='$classname_possible_concern' title='These workitems have not yet been mapped to any test cases' colspan=2'>Unmapped Workitems</th>"
                        . "<td class='$classname_possible_concern'>$count_unmapped_workitem_done</td>"
                        . "<td class='$classname_possible_concern'>$count_unmapped_workitem_notdone</td>"
                        . "<td class='$classname_possible_concern'>$markup_unmapped_workitem</td></tr>"
                    . "</table>";
            
            $form["data_entry_area1"]['overview_info'] = array(
                '#type' => 'item', 
                '#prefix' => "<div class='pagetop-blurb'>",
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            $form['data_entry_area1']['overview_info']['matrix'] = array('#type' => 'item'
                    , '#markup' => $overview_info);  
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );
            //Create the static table
            $tableheader = [];
            $tableheader[] = array("ID","ID of the test case","integer");
            $tableheader[] = array("Name","Name of the test case","text");
            $tableheader[] = array("Description","A description of the test case","text");
            $tableheader[] = array("Perspective","Identify the intended audience","formula");
            $tableheader[] = array("Status","The status code of the test case","formula");
            $tableheader[] = array("Precondition","Conditions before test case starts","text");
            $tableheader[] = array("Postcondition","Conditions when test case ends","text");
            $tableheader[] = array("Tracker","ID of the workitem tracking the effort remaining to refine/complete the test case","formula");
            $tableheader[] = array("Direct","The list of directly mapped workitems","formula");
            $tableheader[] = array("Indirect","The list of indirectly mapped workitems","formula");
            $tableheader[] = array("Importance","The importance of this test case to the project success","formula");
            $tableheader[] = array("%Ready","Percentage of mapped workitems that are marked done, or ready for test","formula");
            $tableheader[] = array("Updated","When this test case was last updated","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";

            $trows_ar = [];
            $testcase2workitems = $analysis['mapped']['testcase2workitems'];

            foreach($testcase_lookup as $testcaseid=>$record)
            {
                
                //$owner_projectid = $record['owner_projectid'];
                $shortname = $record['testcase_nm'];
                $blurb_tx = $record['blurb_tx'];
                $effort_tracking_workitemid = $record['effort_tracking_workitemid'];
                $perspective_cd = $record['perspective_cd'];
                $precondition_tx = $record['precondition_tx'];
                $postcondition_tx = $record['postcondition_tx'];
                $wids_list = $record['maps']['workitems'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];
                
                $direct_wids_map = isset($testcase2workitems[$testcaseid]['direct']) ? $testcase2workitems[$testcaseid]['direct'] : [];
                $indirect_wids_map = [];
                $all_wids_one_tc = isset($testcase2workitems[$testcaseid]['all']) ? $testcase2workitems[$testcaseid]['all'] : [];
                if(count($all_wids_one_tc) > 0)
                {
                    foreach($all_wids_one_tc as $oneantwid)
                    {
                        if(!isset($direct_wids_map[$oneantwid]))
                        {
                            $indirect_wids_map[$oneantwid] = $oneantwid;
                        }
                    }
                }
                $indirect_widcount = count($indirect_wids_map);
                sort($indirect_wids_map);
                $ind_jump_wid_ar = [];
                foreach($indirect_wids_map as $sortedwid)
                {
                    //Make sure it is really in the project
                    if(isset($heirarchy_link_by_wid[$sortedwid]))
                    {
                        $ind_jump_wid_ar[] = $heirarchy_link_by_wid[$sortedwid]['markup'];
                    }
                }
                $indirect_wids_tx = implode(", ", $ind_jump_wid_ar);
                $indirect_wids_markup = "[SORTNUM:$indirect_widcount]$indirect_wids_tx";
                
                //$owner_projectid_markup = "$owner_projectid";
                $perspective_markup = $perspective_cd == 'U' ? 'User' : 'Technical';
                $precondition_tx_markup = "$precondition_tx";
                $postcondition_tx_markup = "$postcondition_tx";
                
                $widcount = count($wids_list);
                sort($wids_list);
                $jump_wid_ar = [];
                foreach($wids_list as $sortedwid)
                {
                    $wstatus_detail = $workitem_lookup[$sortedwid];
                    $sc = $wstatus_detail['status_cd'];
                    $sattr = $wi_status_lookup[$sc];
                    if(!isset($sattr['happy_yn']))
                    {
                        $cn = "";
                    } else {
                        if($sattr['happy_yn'] == 1)
                        {
                            $cn = "colorful-good";
                        } else {
                            $cn = "colorful-bad";
                        }
                    }
                    $jump_wid_ar[] = "<span class='$cn' title='status=$sc'>" .  $heirarchy_link_by_wid[$sortedwid]['markup'] . "</span>";
                }
                $wids_tx = implode(", ", $jump_wid_ar);
                
                $direct_wids_markup = "[SORTNUM:$widcount]$wids_tx";
                
                if($updated_dt !== $created_dt)
                {
                    $updated_markup = "<span title='Created $created_dt'>$updated_dt</span>";
                } else {
                    $updated_markup = "<span title='Never edited'>$updated_dt</span>";
                }
                
                $blurb_tx_len = strlen($blurb_tx);
                if($blurb_tx_len > 256)
                {
                    $blurb_tx_markup = substr($blurb_tx, 0,256) . '...';
                } else {
                    $blurb_tx_markup = $blurb_tx;
                }
                
                $shortname_markup = "$shortname";
                
                if(empty($effort_tracking_workitemid))
                {
                    $effort_tracking_workitemid_markup = '-';
                } else {
                    $effort_tracking_workitemid_markup = $heirarchy_link_by_wid[$effort_tracking_workitemid]['markup'];
                }
                
                $total_wids = $pct_complete_by_tcid[$testcaseid]['total_wids'];
                $allow_test_execution = TRUE;
                $test_exec_tip = "";
                if($total_wids == 0)
                {
                    $test_exec_tip = "Zero workitems are directly tested by this test case";
                    $pct_complete_markup = "[SORTNUM:0]<span title='Zero workitems are mapped to this test case'>NA</span>";
                    $test_icon_url = $test_ready_icon_url;
                } else {
                    $total_done = $pct_complete_by_tcid[$testcaseid]['total_done_untested'];
                    $pct_complete = $pct_complete_by_tcid[$testcaseid]['pct_done_untested'];
                    
                    $total_done_or_testable = $pct_complete_by_tcid[$testcaseid]['total_done_or_testable'];
                    $pct_done_or_testable = $pct_complete_by_tcid[$testcaseid]['pct_done_or_testable'];
                    
                    $tip_tx = "$total_done_or_testable/$total_wids";
                    $pct_complete_markup = "[SORTNUM:$pct_complete]" . \bigfathom\MarkupHelper::getPercentTestableMarkup($total_done,$total_done_or_testable,$total_wids);//,$tip_tx);
                    if($pct_done_or_testable < 100)
                    {
                        $allow_test_execution = FALSE;
                        $test_icon_url = $test_notready_icon_url;
                        $test_exec_tip = "Workitems are not yet ready for testing";
                    } else {
                        $failed_test = $pct_complete_by_tcid[$testcaseid]['total_failed_test'];
                        $passed_test = $pct_complete_by_tcid[$testcaseid]['total_passed_test'];
                        if($pct_complete_by_tcid[$testcaseid]['total_failed_test']>0)
                        {
                            $test_icon_url = $test_failed_icon_url;
                            $test_exec_tip = "$failed_test workitem(s) have failed testing";
                        } else if($pct_complete_by_tcid[$testcaseid]['total_passed_test']>0) {
                            $test_icon_url = $test_passed_icon_url;
                            $test_exec_tip = "$passed_test workitem(s) have passed testing";
                        } else {
                            $test_icon_url = $test_ready_icon_url;
                            $test_exec_tip = "No workitems are marked as tested";
                        }
                    }
                }                

                //$allow_test_execution
                
                $status_cd = $record['status_cd'];
                if($status_cd != NULL)
                {
                    $status_record = $tc_status_lookup[$status_cd];
                    $status_terminal_yn = $status_record['terminal_yn'];
                    $mb = \bigfathom\MarkupHelper::getStatusCodeMarkupBundle($status_record);
                    $status_markup = $mb['status_code'];
                    $terminalyesno = $mb['terminal_yesno'];
                } else {
                    $status_markup = "";
                    $terminalyesno = "";
                }
                
                $importance = $record['importance'];
                $importance_markup = "[SORTNUM:$importance]" . \bigfathom\MarkupHelper::getImportanceValueMarkup($importance);

                $trows_ar[] = "\n".'<td>'
                        . $testcaseid.'</td><td>'
                        . $shortname_markup.'</td><td>'
                        . $blurb_tx_markup.'</td><td>'
                        . $perspective_markup.'</td><td>'
                        . $status_markup.'</td><td>'
                        . $precondition_tx_markup.'</td><td>'
                        . $postcondition_tx_markup.'</td><td>'
                        . $effort_tracking_workitemid_markup.'</td><td>'
                        . $direct_wids_markup.'</td><td>'
                        . $indirect_wids_markup.'</td><td>'
                        . $importance_markup.'</td><td>'
                        . $pct_complete_markup.'</td><td>'
                        . $updated_markup.'</td>';
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_section_title_markup = "<h2>Test Case Mapping Insight for Project#{$this->m_projectid}</h2>";
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid"><thead>' 
                    . $th_markup 
                    . '</thead><tbody>'
                    . $trows_markup 
                    . '</tbody></table>';
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_section_title_markup.$table_markup);

            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
