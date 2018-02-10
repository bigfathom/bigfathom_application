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
require_once 'helper/BrainstormItemsPageHelper.php';

/**
 * This class returns the list of available work topics
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageBrainstormItemsPage extends \bigfathom\ASimpleFormPage {

    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_parent_projectid = NULL;

    public function __construct() 
    {
        module_load_include('php', 'bigfathom_core', 'core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        $urls_arr = array();
        $urls_arr['add'] = 'bigfathom/addbrainstormitem';
        $urls_arr['edit'] = 'bigfathom/editbrainstormitem';
        $urls_arr['view'] = 'bigfathom/viewbrainstormitem';
        $urls_arr['delete'] = 'bigfathom/deletebrainstormitem';
        $urls_arr['restore_all_parked'] = 'bigfathom/project/brainstormtopics/restore_all_parkinglot';
        $urls_arr['trashcan2parked'] = 'bigfathom/project/brainstormtopics/move_all_trashcan2parkinglot';
        $urls_arr['parked2trashcan'] = 'bigfathom/project/brainstormtopics/move_all_parkinglot2trashcan';
        $urls_arr['emptytrashcan'] = 'bigfathom/project/brainstormtopics/empty_the_trashcan';
        $urls_arr['visualconsole'] = 'bigfathom/projects/design/brainstormcapture';

        $cmi = $this->m_oContext->getCurrentMenuItem();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['this_page_path'] = $cmi['link_path'];        
        
        $aPersonRights = 'VAED';

        $this->m_urls_arr = $urls_arr;
        $this->m_aPersonRights = $aPersonRights;

        $this->m_oPageHelper = new \bigfathom\BrainstormItemsPageHelper($urls_arr);
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides = NULL) 
    {
        try 
        {
            $main_tablename = 'brainstorm-table';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            $send_urls = $this->m_urls_arr;
            $send_urls['base_url'] = $base_url;
            $send_urls['images'] = $base_url .'/'. $theme_path.'/images';
            drupal_add_js(array('personid'=>$user->uid,'projectid'=>$this->m_parent_projectid
                    ,'myurls' => $send_urls), 'setting');            
            //drupal_add_js(array('personid'=>$user->uid,'projectid'=>$this->m_parent_projectid
            //        ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageBrainstormItemsTable.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            if ($html_classname_overrides == NULL) {
                $html_classname_overrides = array();
            }
            if (!isset($html_classname_overrides['data-entry-area1'])) {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if (!isset($html_classname_overrides['visualization-container'])) {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if (!isset($html_classname_overrides['table-container'])) {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if (!isset($html_classname_overrides['container-inline'])) {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if (!isset($html_classname_overrides['action-button'])) {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;

            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "Use this console to manage the brainstorm inventory of this project.  Topics listed here have not yet been materialized into project workitems."
                . "</p>",
                '#suffix' => '</div>',
            );

            $bigwrapperid = "bigwrapper-" . $main_tablename;
            $form["data_entry_area1"]['bw'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $bigwrapperid . '" class="bigwrapper-tablestuff">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['bw']['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $userinputspanelid = "userinputs-" . $main_tablename;
            $form["data_entry_area1"]['bw']['userinputs'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $userinputspanelid . '" class="userinputs-panel-area">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['bw']['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            $rows = "\n";
            $allprojects = $this->m_oMapHelper->getProjectsByID();
            $allpeople = $this->m_oMapHelper->getPersonsByID();
            $show_parkinglot = TRUE;
            $show_trashed = TRUE;
            $allbrainstormitems = $this->m_oMapHelper->getBrainstormItemsByID($this->m_parent_projectid,NULL,$show_parkinglot,$show_trashed);
            $show_project_column = ($this->m_parent_projectid == NULL);
            $project_name_rowmarkup = '';
            $cmi = $this->m_oContext->getCurrentMenuItem();
            foreach ($allbrainstormitems as $brainstormitemid => $record) 
            {
                $projectid = $record['projectid'];
                if($projectid == NULL)
                {
                    $project_detail = array();
                    $root_workitem_nm = '';
                } else {
                    $project_detail = $allprojects[$projectid];
                    $root_workitem_nm = $project_detail['root_workitem_nm'];
                }
                $context_nm = $record['context_nm'];
                $item_nm = $record['item_nm'];
                $candidate_type = $record['candidate_type'];
                if($candidate_type == 'G')
                {
                    $type_nm = 'Goal';
                } else
                if($candidate_type == 'T')
                {
                    $type_nm = 'Task';
                } else {
                    $type_nm = 'Uncategorized';
                }
                $into_parkinglot_dt = $record['into_parkinglot_dt'];
                $into_trash_dt = $record['into_trash_dt'];
                if($record['parkinglot_level'] == 1 && !empty($into_parkinglot_dt))
                {
                    $parkinglotyesno_markup = '<span title="Since ' . $into_parkinglot_dt . '" class="colorful-notice">Yes</span>';
                    $pyn_cd = 'Y';
                } else {
                    $parkinglotyesno_markup = 'No';
                    $pyn_cd = 'N';
                }
                if(!empty($into_trash_dt) || $record['active_yn'] != 1)
                {
                    $intrash_yesno_markup = '<span title="Since ' . $into_trash_dt . '" class="colorful-warning">Yes</span>';
                    $tyn_cd = 'Y';
                } else {
                    $intrash_yesno_markup = 'No';
                    $tyn_cd = 'N';
                }
                $active_yesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                $purpose_tx = $record['purpose_tx'];
                $owner_personid = $record['owner_personid'];
                if($owner_personid == NULL)
                {
                    $person_nm = '';
                } else {
                    $person_detail = $allpeople[$owner_personid];
                    $person_nm = $person_detail['first_nm'];
                }
                $importance = $record['importance'];
                
                if (strlen($purpose_tx) > 120) 
                {
                    $suffixmarkup = "<span title='showing 120 of " . strlen($purpose_tx) . " total characters in the text'> ... more</span>";
                    $purpose_tx = substr($purpose_tx, 0, 120) . $suffixmarkup;
                }
                if (strpos($this->m_aPersonRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('brainstormitemid' => $brainstormitemid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view #{$brainstormitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if (strpos($this->m_aPersonRights, 'E') === FALSE || !isset($this->m_urls_arr['edit'])) {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('brainstormitemid' => $brainstormitemid, 'return' => $cmi['link_path'])));
                    $sEditMarkup = "<a title='edit #{$brainstormitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if (strpos($this->m_aPersonRights, 'D') === FALSE || !isset($this->m_urls_arr['delete'])) {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('brainstormitemid' => $brainstormitemid, 'return' => $cmi['link_path'])));
                    $sDeleteMarkup = "<a title='delete #{$brainstormitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                if($show_project_column)
                {
                    $project_name_rowmarkup = $root_workitem_nm . '</td><td>';
                }
                $rows .= "\n" 
                        . '<tr id="'.$brainstormitemid.'" pyn_cd="' . $pyn_cd .'" tyn_cd="' . $tyn_cd .'" >'
                        . '<td>'
                        . $project_name_rowmarkup
                        . $context_nm . '</td><td>'
                        . $type_nm . '</td><td>'
                        . $item_nm . '</td><td>'
                        . $parkinglotyesno_markup . '</td><td>'
                        . $intrash_yesno_markup . '</td><td>'
                        . $purpose_tx . '</td><td>'
                        . $person_nm . '</td><td>'
                        . $importance . '</td><td>'
                        . $record['updated_dt'] . '</td><td>'
                        . $record['created_dt'] . '</td>'
                        .'<td class="action-options">'
                            . $sViewMarkup.' '
                            . $sEditMarkup.' '
                            . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

           
            $form["data_entry_area1"]['bw']['table_container']['maintable'] = array('#type' => 'item',
                '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                . '<thead>'
                . '<tr>'
                . ($show_project_column ? '<th>' . t('Project') . '</th>' : '')
                . '<th colname="context_nm" title="An optional text label useful for grouping topic items together for discussion among collaborators">' . t('Context') . '</th>'
                . '<th colname="category_nm" title="Qualitative workitem categorization of this topic item.  Only categorized topics become candidates for linking to existing project workitems.">' . t('Item Type') . '</th>'
                . '<th colname="item_nm" title="Short name of this proposed topic item">' . t('Item Name') . '</th>'
                . '<th colname="isparked" title="Is this topic item in the parking lot?  (Clearing the parkinglot is up to the project collaborators.)">' . t('Parked') . '</th>'
                . '<th colname="istrashed" title="Is this topic item in the trashcan? (The trashcan is automatically emptied periodically.)">' . t('In Trash') . '</th>'
                . '<th colname="purpose_tx" title="The declared topic detail">' . t('Purpose') . '</th>'
                . '<th colname="created_by" title="Who created this topic item">' . t('Created By') . '</th>'
                . '<th colname="importance" title="The declared importance of this proposed topic.  1 is less important than 99.  Range is [0,100]">' . t('Importance') . '</th>'
                . '<th colname="updated_dt">' . t('Updated') . '</th>'
                . '<th colname="created_dt">' . t('Created') . '</th>'
                . '<th colname="actionoptions" datatype="html" class="action-options">' . t('Action Options').'</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody>'
                . $rows
                . '</tbody>'
                . '</table>');


            $form["data_entry_area1"]['action_buttons'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['container-inline'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Add New Topic Item',$this->m_urls_arr['add']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addtopic'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }

            if(isset($this->m_urls_arr['restore_all_parked']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADDPARKED Restore All Parked Topics Now'
                            , $this->m_urls_arr['restore_all_parked']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_ADDPARKED'
                            , '<i class="fa fa-car" aria-hidden="true"></i> <i class="fa fa-caret-right" aria-hidden="true"></i> <i class="fa fa-plus-square-o" aria-hidden="true"></i>'
                            , $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['restore_all_parked'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }
            
            if(isset($this->m_urls_arr['trashcan2parked']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_TRASH2PARKED Move All Trashcan Topics to Parkinglot Now'
                            , $this->m_urls_arr['trashcan2parked']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_TRASH2PARKED'
                            , '<i class="fa fa-trash" aria-hidden="true"></i> <i class="fa fa-caret-right" aria-hidden="true"></i> <i class="fa fa-car" aria-hidden="true"></i>'
                            , $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['trashcan2parked'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }
            
            if(isset($this->m_urls_arr['parked2trashcan']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $initial_button_markup = l('ICON_PARKED2TRASH Move All Parked Topics to Trashcan Now'
                            , $this->m_urls_arr['parked2trashcan']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_PARKED2TRASH'
                            , '<i class="fa fa-car" aria-hidden="true"></i> <i class="fa fa-caret-right" aria-hidden="true"></i> <i class="fa fa-trash" aria-hidden="true"></i>'
                            , $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['parked2trashcan'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }
            
            if(isset($this->m_urls_arr['emptytrashcan']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $initial_button_markup = l('ICON_EMPTYTRASH Empty Trashcan Now',$this->m_urls_arr['emptytrashcan']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $final_button_markup = str_replace('ICON_EMPTYTRASH'
                            , '<i class="fa fa-trash" aria-hidden="true"></i> <i class="fa fa-caret-right" aria-hidden="true"></i> <i class="fa fa-trash-o" aria-hidden="true"></i>'
                            , $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['emptytrashcan'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }
            
            if (isset($this->m_urls_arr['visualconsole'])) {
                $initial_button_markup = l('ICON_VISUAL Jump to Visual Console', $this->m_urls_arr['visualconsole']
                                , array('attributes'=>array('class'=>'action-button'))
                        );
                $final_button_markup = str_replace('ICON_VISUAL'
                        , '<i class="fa fa-mortar-board" aria-hidden="true"></i>'
                        , $initial_button_markup);
                $form["data_entry_area1"]['action_buttons']['visualconsole'] = array('#type' => 'item'
                    , '#markup' => $final_button_markup);
            }
            
            if (isset($this->m_urls_arr['return'])) {
                $exit_link_markup = l('Exit', $this->m_urls_arr['return']
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
