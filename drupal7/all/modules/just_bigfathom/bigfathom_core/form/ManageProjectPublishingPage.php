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
require_once 'helper/ProjectPageHelper.php';

/**
 * This class returns the list of available projects
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageProjectPublishingPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oProjectPageHelper = NULL;
    protected $m_oContext = NULL;
    protected $m_hasSelectedProject = NULL;
    protected $m_selectedProjectSummary = NULL;
    protected $m_projectid = NULL;
    
    public function __construct($projectid,$urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_hasSelectedProject = $this->m_oContext->hasSelectedProject();
        $this->m_selectedProjectSummary = $this->m_oContext->getSelectedProjectSummary();

        $loaded1 = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded1)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        if(!empty($projectid))
        {
            $this->m_projectid = $projectid;
        } else {
            $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        }
        if(empty($this->m_projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        
        $urls_arr = array();
        $urls_arr['download'] = 'bigfathom/downloadprojectinfo';
        $urls_arr['publish'] = 'bigfathom/publishprojectinfo';
        $urls_arr['view'] = 'bigfathom/viewpublishedprojectinfo';
        $urls_arr['delete'] = 'bigfathom/deletepublishedprojectinfo';
        $aPersonRights='VAED';
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr       = $urls_arr;
        $this->m_aPersonRights    = $aPersonRights;
        
        $this->m_oProjectPageHelper = new \bigfathom\ProjectPageHelper($urls_arr);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'publishedprojectinfo-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            //drupal_add_js("$base_url/$module_path/form/js/ManageProjectPublishingTable.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
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
            global $base_url;

            $project_record = $this->m_oMapHelper->getProjectRecord($this->m_projectid);
            $project_status_terminal_yn = $project_record['status_terminal_yn'];
            $current_publishedrefname = $project_record['publishedrefname'];

            if(empty($project_status_terminal_yn) || $project_status_terminal_yn == '0')
            {
                $project_editable = TRUE;
            } else {
                $project_editable = FALSE;
            }
            if(!empty($project_record["actual_end_dt"]))
            {
                $project_end_dt = $project_record["actual_end_dt"];
            } else {
                $project_end_dt = $project_record["planned_end_dt"];
            }
            $dashboard_elems = $this->m_oProjectPageHelper->getContextDashboardElements($this->m_projectid, FALSE);
            $form['data_entry_area1']['context_dashboard'] = $dashboard_elems;
            
            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('download');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            
            $rows = "\n";
            $all_people = $this->m_oMapHelper->getPersonsByID();
            $all_pcbyid = $this->m_oMapHelper->getProjectContextsByID(NULL, FALSE, FALSE);
            $all_statusbycode = $this->m_oMapHelper->getWorkitemStatusByCode();
            
            $published_projectinfo_bundle = $this->m_oMapHelper->getPublishedProjectInfoBundle($this->m_projectid); 
            $all_published_info = $published_projectinfo_bundle['bypubid'];

            $cmi = $this->m_oContext->getCurrentMenuItem();
            foreach($all_published_info as $pubid=>$record)
            {
                
                $pubid_markup = $pubid;
                $publishedrefname = $record['publishedrefname'];
                $project_contextid = $record['project_contextid'];
                $project_nm = $record['project_nm'];
                $root_goalid = $record['root_goalid'];
                $mission_tx = $record['mission_tx'];
                $owner_personid = $record['owner_personid'];
                $planned_start_dt = $record['planned_start_dt'];
                $actual_start_dt = $record['actual_start_dt'];
                $planned_end_dt = $record['planned_end_dt'];
                $actual_end_dt = $record['actual_end_dt'];
                $onbudget_p = $record['onbudget_p'];
                $onbudget_u = $record['onbudget_u'];
                $ontime_p = $record['ontime_p'];
                $ontime_u = $record['ontime_u'];
                $comment_tx = $record['comment_tx'];
                $status_cd = $record['status_cd'];
                $status_set_dt = $record['status_set_dt'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];

                if(empty($root_goalid))
                {
                    $proj_name_markup = "<span title='No declared root_goalid'>$project_nm</span>";
                } else {
                    $proj_name_markup = "<span title='root_goalid=$root_goalid'>$project_nm</span>";
                }
                
                $project_mgr = $all_people[$owner_personid];
                $project_mgr_nm = $project_mgr['first_nm'] . " " . $project_mgr['last_nm'];
                
                $status_title_tx = $all_statusbycode[$status_cd]['wordy_status_state'];
                $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                
                if(empty($project_contextid))
                {
                    $project_context_markup = "";
                } else {
                    $pc_rec = $all_pcbyid[$project_contextid];
                    $pc_shortname = $pc_rec['shortname'];
                    $pc_description_tx = $pc_rec['description_tx'];
                    $project_context_markup = "<span title='$pc_description_tx'>#$project_contextid - $pc_shortname</span>";
                }

                $publishedrefname_markup = $publishedrefname;
                
                if(strlen($mission_tx) > 80)
                {
                    $mission_tx_markup = substr($mission_tx, 0,80) . '...';
                } else {
                    $mission_tx_markup = $mission_tx;
                }
                if(strlen($comment_tx) > 80)
                {
                    $comment_tx_markup = substr($comment_tx, 0,80) . '...';
                } else {
                    $comment_tx_markup = $comment_tx;
                }
                if(strpos($this->m_aPersonRights,'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sViewMarkup = "";
                    $sDownloadMarkup = '';
                } else {
                    $download_page_url = url($this->m_urls_arr['download'], array('query'=>array('publishedrefname'=>$publishedrefname, 'return' => $cmi['link_path'])));
                    $sDownloadMarkup = "<a title='download information in pubid#{$pubid}' href='$download_page_url'><img src='$download_icon_url'/></a>";
                    
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('pubid'=>$pubid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view details of pubid#{$pubid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    
                }
                if(strpos($this->m_aPersonRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('projectid'=>$projectid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('pubid'=>$pubid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='jump to delete for #{$pubid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }

                if($onbudget_u !== NULL)
                {
                    $onbudget_tip = "+/- $onbudget_u";
                } else {
                    $onbudget_tip = "No declared uncertainty range for this probability";
                }
                $onbudget_p_markup = "[SORTNUM:$onbudget_p]<span title='$onbudget_tip'>".UtilityGeneralFormulas::getRoundSigDigs($onbudget_p)."</span>";

                if($ontime_u !== NULL)
                {
                    $ontime_tip = "+/- $ontime_u";
                } else {
                    $ontime_tip = "No declared uncertainty range for this probability";
                }
                $ontime_p_markup = "[SORTNUM:$ontime_p]<span title='$ontime_tip'>".UtilityGeneralFormulas::getRoundSigDigs($ontime_p)."</span>";
                
                $rows   .= "\n".'<tr>'
                        . '<td>'
                        . $pubid_markup.'</td><td>'
                        . $proj_name_markup.'</td><td>'
                        . $project_mgr_nm.'</td><td>'
                        . $comment_tx_markup.'</td><td>'
                        . $planned_start_dt.'</td><td>'
                        . $actual_start_dt.'</td><td>'
                        . $planned_end_dt.'</td><td>'
                        . $actual_end_dt.'</td><td>'
                        . $publishedrefname_markup . '</td><td>'
                        . $onbudget_p_markup . '</td><td>'
                        . $ontime_p_markup.'</td><td>'                        
                        . $status_markup.'</td><td>'
                        . $status_set_dt . '</td><td>'
                        . $updated_dt . '</td><td>'
                        . $created_dt . '</td>'
                        . '<td class="action-options">'
                                    . $sDownloadMarkup . ' '
                                    . $sViewMarkup . ' '
                                    . $sDeleteMarkup 
                        . '</td>'
                        . '</tr>';
                
            }
            
            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                     '#markup' => '<table id="table-selectproject" class="browserGrid">'
                                . '<thead class="nowrap">'
                                . '<tr>'
                                . '<th datatype="numid" class="nowrap">'
                                    . '<span title="Unique ID number of the publication record">'.t('ID').'</span></th>'
                                . '<th>'
                                    . '<span title="Name of the project in the published record">'.t('Project Name').'</span></th>'
                                . '<th>'.t('Project Leader').'</th>'
                                
                                . '<th>'
                                    . '<span title="The published comment">'.t('Comment').'</span></th>'
                
                                . '<th datatype="date">'
                                    . '<span title="Planned start date">'.t('PSDT').'</span></th>'
                
                                . '<th datatype="date">'
                                    . '<span title="Actual start date">'.t('ASDT').'</span></th>'
                
                                . '<th datatype="date">'
                                    . '<span title="Planned end date">'.t('PEDT').'</span></th>'
                
                                . '<th datatype="date">'
                                    . '<span title="Actual end date">'.t('AEDT').'</span></th>'
                
                                . '<th datatype="text">'
                                    . '<span title="A unique reference name by which users can find this project">'.t('RefName').'</span></th>'
                
                                . '<th datatype="formula">'
                                    . '<span title="Probability of completing the project on budget">'.t('OBP').'</span></th>'
                
                                . '<th datatype="formula">'
                                    . '<span title="Probability of completing the project on time">'.t('OTP').'</span></th>'
                
                                . '<th>'.t('Status') . '</th>'
                
                                . '<th datatype="date">'.t('Status Date') . '</th>'
                                . '<th datatype="date">'.t('Updated') . '</th>'
                                . '<th datatype="date">'
                                    . '<span title="When this information was published">'.t('Created').'</span></th>'
                                . '<th datatype="html" class="action-options">'.t('Action Options').'</th>'
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

            if(isset($this->m_urls_arr['publish']))
            if(empty($current_publishedrefname))
            {
                $form['data_entry_area1']['publish'] = array('#type' => 'item'
                        , '#markup' => "<h3>NOTE: This project does not currently have a declared public reference name by which it can be published.</h3>");
            } else {
                if(strpos($this->m_aPersonRights,'E') !== FALSE)
                {
                    //Jumps to a dedicated page
                    $publish_link_url = url($this->m_urls_arr['publish'], array('query'=>array('projectid'=>$this->m_projectid, 'return' => $cmi['link_path'])));
                    $publish_link_markup = "<a class='action-button' title='Edit latest sharable information of project#{$this->m_projectid} and publish it' href='$publish_link_url'>Publish Current Project Information</a>";
                    $form['data_entry_area1']['action_buttons']['publish'] = array('#type' => 'item'
                            , '#markup' => $publish_link_markup);
                }
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
