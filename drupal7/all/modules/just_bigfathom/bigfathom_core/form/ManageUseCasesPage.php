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
require_once 'helper/UseCasePageHelper.php';

/**
 * This page presents the usecases available in the system
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageUseCasesPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights  = NULL;
    protected $m_oPageHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addusecase';
        $urls_arr['edit'] = 'bigfathom/editusecase';
        $urls_arr['view'] = 'bigfathom/viewusecase';
        $urls_arr['delete'] = 'bigfathom/deleteusecase';
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
        
        $this->m_oPageHelper = new \bigfathom\UseCasePageHelper($urls_arr);
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
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
            $main_tablename = 'usecase-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            
            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
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
            
            $bundle = $this->m_oMapHelper->getUseCasesBundle($this->m_parent_projectid);
            $uc_status_lookup = $bundle['lookup']['uc_statuses'];
            $usecase_lookup = $bundle['lookup']['usecases'];
            $workitem_lookup = $bundle['lookup']['workitems'];
            $analysis =  $bundle['analysis'];
            $ar_mapped_workitem_direct = $analysis['mapped']['workitem']['direct']['workitems'];
            $ar_mapped_workitem_indirect = $analysis['mapped']['workitem']['indirect']['workitems'];
            $ar_unmapped_workitem = $analysis['unmapped']['workitem']['workitems'];
            
            $tracker_map_wid2usecaseid = $bundle['tracker_map_wid2usecaseid'];
            $tracker_map_usecaseid2wid = $bundle['tracker_map_usecaseid2wid'];
            sort($tracker_map_usecaseid2wid);
            
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
                        , array('query'=>array('projectid'=>($this->m_parent_projectid), 'jump2workitemid'=>$workitemid)));
                $sHierarchyMarkup = "<a "
                    . " title='view dependencies for workitem#{$workitemid} in project#{$this->m_parent_projectid}' "
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
            $markup_tracker_workitem = implode(', ', $tracker_map_usecaseid2wid);
            
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
                '#prefix' => '<div class="console-overview-info">',
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

            $rows = "\n";
            foreach($usecase_lookup as $usecaseid=>$record)
            {
//DebugHelper::debugPrintNeatly(array('##$record'=>$record),FALSE,"LOOK $usecaseid..........","........... $usecaseid");                
                
                //$owner_projectid = $record['owner_projectid'];
                $shortname = $record['usecase_nm'];
                $blurb_tx = $record['blurb_tx'];
                $effort_tracking_workitemid = $record['effort_tracking_workitemid'];
                $perspective_cd = $record['perspective_cd'];
                $precondition_tx = $record['precondition_tx'];
                $postcondition_tx = $record['postcondition_tx'];
                $wids_list = $record['maps']['workitems'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];
                
                //$owner_projectid_markup = "$owner_projectid";
                $perspective_markup = $perspective_cd == 'U' ? 'User' : 'Technical';
                $precondition_tx_markup = "$precondition_tx";
                $postcondition_tx_markup = "$postcondition_tx";
                $widcount = count($wids_list);
                sort($wids_list);
                $jump_wid_ar = [];
                foreach($wids_list as $sortedwid)
                {
                    $jump_wid_ar[] = $heirarchy_link_by_wid[$sortedwid]['markup'];
                }
                $wids_tx = implode(", ", $jump_wid_ar);
                
                //$heirarchy_link_by_wid[$workitemid]['markup'] = $sHierarchyMarkup
                
                $wids_markup = "[SORTNUM:$widcount]$wids_tx";
                
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
                
                $aDataRights = $this->m_oMapHelper->getUseCaseActionPrivsOfPerson($usecaseid, $user->uid);   
                
                if(strpos($aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('usecaseid'=>$usecase_id)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('usecaseid'=>$usecaseid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view #{$usecaseid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('usecaseid'=>$usecase_id)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('usecaseid'=>$usecaseid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$usecaseid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('usecaseid'=>$usecase_id)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('usecaseid'=>$usecaseid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete #{$usecaseid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
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

                if(empty($effort_tracking_workitemid))
                {
                    $effort_tracking_workitemid_markup = '-';
                } else {
                    $effort_tracking_workitemid_markup = $heirarchy_link_by_wid[$effort_tracking_workitemid]['markup'];
                }
                
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
                
                $rows   .= "\n".'<tr><td>'
                        .$usecaseid.'</td><td>'
                        .$shortname_markup.'</td><td>'
                        .$blurb_tx_markup.'</td><td>'
                        .$perspective_markup.'</td><td>'
                        .$status_markup.'</td><td>'
                        .$precondition_tx_markup.'</td><td>'
                        .$postcondition_tx_markup.'</td><td>'
                        .$effort_tracking_workitemid_markup.'</td><td>'
                        .$wids_markup.'</td><td>'
                        .$importance_markup.'</td><td>'
                        .$pct_complete_markup.'</td><td>'
                        .$updated_markup.'</td>'
                        .'<td class="action-options">'
                        . $sViewMarkup.' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th datatype="integer" class="nowrap" title="Unique identifier of this use case">'.t('ID').'</th>'
                                . '<th datatype="text" class="nowrap" title="Name of this use case">'.t('Name').'</th>'
                                . '<th datatype="text" class="nowrap" title="A short description of the use case">'.t('Description').'</th>'
                                . '<th datatype="text" class="nowrap" title="Does the use case primarily illuminate'
                                    . ' a user experience or a technical operation/integration insight?">'.t('Perspective').'</th>'
                                . '<th datatype="formula" class="nowrap" title="The declared status of the use case">'.t('Status').'</th>'
                                . '<th datatype="text" class="nowrap" title="Conditions that must exist at the start of this use case">'.t('Precondition').'</th>'
                                . '<th datatype="text" class="nowrap" title="Conditions that will exist at the end of this use case">'.t('Postcondition').'</th>'
                                . '<th datatype="formula" class="nowrap" title="ID of the workitem tracking the effort remaining to refine/complete the test case">'.t('Tracker').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Workitems, if any, directly mapped to the use case">'.t('Workitems').'</th>'
                                . '<th datatype="formula" class="nowrap" title="The declared importance of this use case to the over-all success'
                                    . ' of the project (higher value indicates greater importantance)">'.t('Importance').'</th>'
                                . '<th datatype="formula" class="nowrap" title="Implementation Completion is the percentage of mapped workitems that have been completed">'
                                    .t('%IC').'</th>'
                                . '<th datatype="datetime" class="nowrap" title="When this record was last updated">'.t('Updated').'</th>'
                                . '<th datatype="html" class="nowrap action-options">' . t('Action Options') . '</th>'
                                . '</tr>'
                                . '</thead>'
                                . '<tbody>'
                                . $rows
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
                $aDataRights = $this->m_oMapHelper->getUseCaseActionPrivsOfPerson(NULL, $user->uid);  
                if(strpos($aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Add New Use Case',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addusecase'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }

            if (isset($this->m_urls_arr['durationconsole'])) 
            {
                $initial_button_markup = l('ICON_DURATION Jump to Duration Table', $this->m_urls_arr['durationconsole']
                                , array('attributes'=>array('class'=>'action-button'))
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
