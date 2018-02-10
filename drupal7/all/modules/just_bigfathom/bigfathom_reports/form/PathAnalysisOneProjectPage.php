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
 * Run report about project status
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class PathAnalysisOneProjectPage extends \bigfathom\ASimpleFormPage
{

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
            
            $main_tablename = 'grid-project-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $oPI = new \bigfathom\ProjectInsight($this->m_projectid);
            $pi_bundle = $oPI->getAllInsightsBundle();
            $root_goalid = $pi_bundle['metadata']['root_goalid'];
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
            $tableheader[] = array("U OTSP","Upper bound estimate for On-Time Successful Completion (OTSP) for the collection of workitems on the path","formula");
            $tableheader[] = array("L OTSP","Lower bound estimate for On-Time Successful Completion (OTSP) for the collection of workitems on the path","formula");
            $tableheader[] = array("WC","The number of workitems on the path","formula");
            $tableheader[] = array("TEH","Total effort hours for the collection of workitems in the path","formula");
            $tableheader[] = array("PSW","The starting non-completed workitem of the path","formula");
            $tableheader[] = array("OC","Number of different workitem owners on this path","formula");
            $tableheader[] = array("SC","Status codes appearing for workitems on the path","formula");
            $tableheader[] = array("WNS","Count of workitems where work has not started","formula");
            $tableheader[] = array("Path","The non-completed workitems in the path","formula");
            $tableheader[] = array("Start","The earliest non-root workitem start date in the path","formula");
            $tableheader[] = array("End","The latest non-root workitem end date in the path","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $trows_ar = []; 
            foreach($pi_bundle['paths2root'] as $one_path_info)
            {
                $leafwid = $one_path_info['leafwid'];
                $nodecount = $one_path_info['nodecount'];
                $leaf2root_path_ar = $one_path_info['path'];
                $path_ar = array_reverse($leaf2root_path_ar);
                $otsp_lower = $one_path_info['otsp']['lower'];
                $otsp_upper = $one_path_info['otsp']['upper'];
                $total_reh = $one_path_info['total_reh'];
                $owner_map = $one_path_info['maps']['owner'];
                $owner_count = count($owner_map);
                $status_code_map = $one_path_info['maps']['status']['code'];
                $status_code_map_count = count($status_code_map);
                $status_wns_count = $one_path_info['maps']['status']['count']['not']['workstarted'];
                
                $sdt = $one_path_info['dates']['sdt'];
                $edt = $one_path_info['dates']['edt'];
                
                $sort_otsp_lower = round($otsp_lower * 1000);
                $sort_otsp_upper = round($otsp_upper * 1000);

                $otsp_lower_round = round($otsp_lower,4);
                $otsp_lower_classname = \bigfathom\UtilityGeneralFormulas::getClassname4OTSP($otsp_lower_round);
                $otsp_lower_markup = "[SORTNUM:{$sort_otsp_lower}]<span class='$otsp_lower_classname'>$otsp_lower_round</span>";
                $otsp_upper_round = round($otsp_upper,4);
                $otsp_upper_classname = \bigfathom\UtilityGeneralFormulas::getClassname4OTSP($otsp_upper_round);
                $otsp_upper_markup = "[SORTNUM:{$sort_otsp_upper}]<span class='$otsp_upper_classname'>$otsp_upper_round</span>";

                $sort_total_reh = round(10*$total_reh);
                $total_reh_markup = "[SORTNUM:{$sort_total_reh}]<span>$total_reh</span>";
                
                $status_code_tx = implode(',', $status_code_map);
                $status_code_markup = "[SORTNUM:{$status_code_map_count}]<span>$status_code_tx</span>";
                
                $path_tx = implode(' <i class="fa fa-arrow-left" title="influence direction" aria-hidden="true"></i> ',$path_ar);
                $path_markup = "[SORTNUM:{$nodecount}]<span>$path_tx</span>";
                
                $trows_ar[] = "\n<td>$otsp_upper_markup</td>"
                        . "<td>$otsp_lower_markup</td>"
                        . "<td>$nodecount</td>"
                        . "<td>$total_reh_markup</td>"
                        . "<td>$leafwid</td>"
                        . "<td>$owner_count</td>"
                        . "<td>$status_code_markup</td>"
                        . "<td>$status_wns_count</td>"
                        . "<td>$path_markup</td>"
                        . "<td>$sdt</td>"
                        . "<td>$edt</td>"
                        ;
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_section_title_markup = "<h2>Path Analysis of Remaining Work for Project with Root Workitem#$root_goalid</h2>";
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
