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
 * Run report about use cases in one project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UseCaseMappingOneProjectPage extends \bigfathom\ASimpleFormPage
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
        
        $urls_arr['view'] = 'bigfathom/viewusecase';
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
            
            $main_tablename = 'grid-project-usecase-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $bundle = $this->m_oMapHelper->getUseCasesBundle($this->m_projectid);
            $uc_mapped_wids = $bundle['uc_mapped_wids'];
            $uc_status_lookup = $bundle['lookup']['uc_statuses'];
            $usecase_lookup = $bundle['lookup']['usecases'];
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
            $pct_complete_by_ucid = $analysis['pct_complete_by_ucid'];

            $heirarchy_link_by_wid = [];
            $ar_markup_mapped_workitem_direct = [];
            $ar_markup_mapped_workitem_indirect = [];
            $ar_markup_unmapped_workitem = [];
            foreach($workitem_lookup as $workitemid=>$winfo)
            {
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
            
            if(count($ar_unmapped_workitem) > 0)
            {
                $classname_possible_concern = 'possible-concern';
            } else {
                $classname_possible_concern = '';
            }
            $overview_info = "<table class='simple-rows'>"
                    . "<tr><td class='empty-cell' colspan='2'></td>"
                        . "<th title='Count of workitems that are in a terminal state'>Done</th>"
                        . "<th title='Count of workitems that are not in a terminal state'>Not Done</th>"
                        . "<th title='The ID(s) of the workitems counted in the row'>Workitem ID(s)</th></tr>"
                    . "<tr><th rowspan='2' title='These workitems have been mapped to one or more use cases'>Mapped Workitems</th>"
                        . "<th title='Workitems in this row have been directly mapped to one or more use cases'>Direct</th>"
                        . "<td>$count_mapped_workitem_direct_done</td>"
                        . "<td>$count_mapped_workitem_direct_notdone</td>"
                        . "<td>$markup_mapped_workitem_direct</td></tr>"
                    . "<tr><th title='Workitems in this row have not been directly mapped to any use case but are an ancestor of one or more mapped workitems'>Indirect</th>"
                        . "<td>$count_mapped_workitem_indirect_done</td>"
                        . "<td>$count_mapped_workitem_indirect_notdone</td>"
                        . "<td>$markup_mapped_workitem_indirect</td></tr>"
                    . "<tr><th class='$classname_possible_concern' title='These workitems have not yet been mapped to any use cases' colspan=2'>Unmapped Workitems</th>"
                        . "<td class='$classname_possible_concern'>$count_unmapped_workitem_done</td>"
                        . "<td class='$classname_possible_concern'>$count_unmapped_workitem_notdone</td>"
                        . "<td class='$classname_possible_concern'>$markup_unmapped_workitem</td></tr>"
                    . "</table>";
            
            $blurb_markup = $overview_info; //implode("\n",$blurb_ar);        
            $form["data_entry_area1"]['context_info'] = array(
                '#type' => 'item',
                '#markup' => "<div class='pagetop-blurb'>$blurb_markup</div>",
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );
            //Create the static table
            $tableheader = [];
            $tableheader[] = array("ID","ID of the use case","integer");
            $tableheader[] = array("Name","Name of the use case","text");
            $tableheader[] = array("Description","A description of the use case","text");
            $tableheader[] = array("Perspective","Identify the intended audience","formula");
            $tableheader[] = array("Status","The status code of the use case","formula");
            $tableheader[] = array("Precondition","Conditions before use case starts","text");
            $tableheader[] = array("Postcondition","Conditions when use case ends","text");
            $tableheader[] = array("Direct","The list of directly mapped workitems","formula");
            $tableheader[] = array("Indirect","The list of indirectly mapped workitems","formula");
            $tableheader[] = array("Importance","The importance of this use case to the project success","formula");
            $tableheader[] = array("%IC","The percent implementation completion of the use case","formula");
            $tableheader[] = array("Updated","When this use case was last updated","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $trows_ar = [];
            $usecase2workitems = $analysis['mapped']['usecase2workitems'];
            foreach($usecase_lookup as $usecaseid=>$record)
            {
                
                //$owner_projectid = $record['owner_projectid'];
                $shortname = $record['usecase_nm'];
                $blurb_tx = $record['blurb_tx'];
                $perspective_cd = $record['perspective_cd'];
                $precondition_tx = $record['precondition_tx'];
                $postcondition_tx = $record['postcondition_tx'];
                $direct_wids_map = isset($usecase2workitems[$usecaseid]['direct']) ? $usecase2workitems[$usecaseid]['direct'] : [];
                $indirect_wids_map = [];
                $all_wids_one_uc = isset($usecase2workitems[$usecaseid]['all']) ? $usecase2workitems[$usecaseid]['all'] : [];
                if(count($all_wids_one_uc) > 0)
                {
                    foreach($all_wids_one_uc as $oneantwid)
                    {
                        if(!isset($direct_wids_map[$oneantwid]))
                        {
                            $indirect_wids_map[$oneantwid] = $oneantwid;
                        }
                    }
                }
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];
                
                //$owner_projectid_markup = "$owner_projectid";
                $perspective_markup = $perspective_cd == 'U' ? 'User' : 'Technical';
                $precondition_tx_markup = "$precondition_tx";
                $postcondition_tx_markup = "$postcondition_tx";
                
                $direct_widcount = count($direct_wids_map);
                sort($direct_wids_map);
                $d_jump_wid_ar = [];
                foreach($direct_wids_map as $sortedwid)
                {
                    $d_jump_wid_ar[] = $heirarchy_link_by_wid[$sortedwid]['markup'];
                }
                $direct_wids_tx = implode(", ", $d_jump_wid_ar);
                $direct_wids_markup = "[SORTNUM:$direct_widcount]$direct_wids_tx";
                
                
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
                
                $status_cd = $record['status_cd'];
                if($status_cd != NULL)
                {
                    $status_record = $uc_status_lookup[$status_cd];
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

                $total_wids = $pct_complete_by_ucid[$usecaseid]['total_wids'];
                if($total_wids == 0)
                {
                    $pct_complete_markup = "[SORTNUM:0]<span title='Zero workitems are mapped to this use case'>NA</span>";
                } else {
                    $total_done = $pct_complete_by_ucid[$usecaseid]['total_done'];
                    $pct_complete = $pct_complete_by_ucid[$usecaseid]['pct_done'];
                    $tip_tx = "$total_done/$total_wids";
                    $pct_complete_markup = "[SORTNUM:$pct_complete]" . \bigfathom\MarkupHelper::getPercentCompleteMarkup($pct_complete,$tip_tx);
                }
                
                $trows_ar[]   .= "\n".'<td>'
                        .$usecaseid.'</td><td>'
                        .$shortname_markup.'</td><td>'
                        .$blurb_tx_markup.'</td><td>'
                        .$perspective_markup.'</td><td>'
                        .$status_markup.'</td><td>'
                        .$precondition_tx_markup.'</td><td>'
                        .$postcondition_tx_markup.'</td><td>'
                        .$direct_wids_markup.'</td><td>'
                        .$indirect_wids_markup.'</td><td>'
                        .$importance_markup.'</td><td>'
                        .$pct_complete_markup.'</td><td>'
                        .$updated_markup.'</td>';
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_section_title_markup = "<h2>Use Case Mapping Insight for Project#{$this->m_projectid}</h2>";
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
