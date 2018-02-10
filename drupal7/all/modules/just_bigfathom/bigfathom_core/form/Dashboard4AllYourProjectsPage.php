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
require_once 'helper/WorkitemPageHelper.php';

/**
 * This class returns the list of available goals
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Dashboard4AllYourProjectsPage extends \bigfathom\ASimpleFormPage {

    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oContext = NULL;
    protected $m_oTextHelper = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/oneproject';
        $urls_arr['edit'] = 'bigfathom/editproject';
        $urls_arr['view'] = 'bigfathom/viewproject';
        $urls_arr['delete'] = 'bigfathom/deleteproject';
        $urls_arr['archive'] = 'bigfathom/archiveproject';
        $urls_arr['comments'] = 'bigfathom/project/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';//&projectid=5
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['main_visualization'] = '';   // '/sites/all/modules/bigfathom_core/visualization/bigTree.html';
        $aPersonRights = 'VAED';

        $this->m_urls_arr = $urls_arr;
        $this->m_aPersonRights = $aPersonRights;

        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) 
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides = NULL) {
        try 
        {
            $main_tablename = 'goals-table';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
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

            if ($this->m_urls_arr['main_visualization'] > '') {
                if (substr($this->m_urls_arr['main_visualization'], 0, 4) == 'http') {
                    $visualization_url = $this->m_urls_arr['main_visualization'];
                } else {
                    $visualization_url = $base_url . '/' . $this->m_urls_arr['main_visualization'];
                }
                $form['data_entry_area1']['main_visual'] = array(
                    '#type' => 'item',
                    '#prefix' => 'HELLO <iframe width="100%" height="750" scrolling=yes class="' 
                    . $html_classname_overrides['visualization-container'] . '" src="' . $visualization_url . '">',
                    '#suffix' => '</iframe>',
                    '#tree' => TRUE,
                );
            }

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            $rows = "\n";
            
            $goal_status_by_code = $this->m_oMapHelper->getWorkitemStatusByCode();
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            
            global $user;
            
            $bundle = $this->m_oMapHelper->getDashboardMultiProjectBundle($user->uid);
            $all = $bundle['byproject'];
            $all_owner_personids = array_values($bundle['all_owner_personids']);
            
            $people = $this->m_oMapHelper->getPersonsByID(NULL, $all_owner_personids);
            
            $cmi = $this->m_oContext->getCurrentMenuItem();
            foreach ($all as $projectid => $record) 
            {
                
                $root_goalid = $record['id'];
                $project_name = $record['workitem_nm'];
                $mission_tx = $record['purpose_tx'];
                $importance = $record['importance'];

                $owner_personid = $record['owner_personid'];
                $map_delegate_owner = $record['maps']['delegate_owner'];
                $isowner = $user->uid == $owner_personid;
                $owner_persondetail = $people[$owner_personid];
                $owner_personname = $owner_persondetail['first_nm'] . " " . $owner_persondetail['last_nm'];
                $owner_txt = "#{$owner_personid} and ";
                if(count($map_delegate_owner) == 0)
                {
                    $owner_txt .= "no delegate owners";
                } else {
                    $delgates = [];
                    foreach($map_delegate_owner as $delegate_ownerid)
                    {
                        if(!$isowner && $user->uid === $delegate_ownerid)
                        {
                            $isowner = TRUE;    
                        }
                        $delegateowner_persondetail = $people[$delegate_ownerid];
                        $delegateowner_personname = $delegateowner_persondetail['first_nm'] . " " . $delegateowner_persondetail['last_nm'];
                        $delgates[] = "{$delegateowner_personname}";
                    }
                    $doc = count($map_delegate_owner);
                    if($doc < 2)
                    {
                        $owner_txt .= "1 delegate owner " . implode(" and ", $delgates);
                    } else {
                        $owner_txt .= count($map_delegate_owner) . " delegate owners: " . implode(" and ", $delgates);
                    }
                    $owner_personname .= "+";
                }
                $owner_markup = "<span title='$owner_txt'>".$owner_personname."</span>";
                
                $ddw_markup = '';
                $workitem_nm = $record['workitem_nm'];
                
                $parent_workitem_nm_ar = array();

                $branch_effort_est_p = $record['branch_effort_hours_est_p'];
                if($branch_effort_est_p === NULL)
                {
                    $branch_effort_est_markup = "<span>".$record['branch_effort_hours_est']."</span>";
                } else {
                    $branch_effort_est_markup = "<span class='has-more-info' title='$branch_effort_est_p'>".$record['branch_effort_hours_est']."</span>";
                }
                
                $effort_est_p = $record['effort_hours_est_p'];
                if($effort_est_p === NULL)
                {
                    $effort_est_markup = "<span>".$record['effort_hours_est']."</span>";
                } else {
                    $effort_est_markup = "<span class='has-more-info' title='$effort_est_p'>".$record['effort_hours_est']."</span>";
                }
                $effort_act_markup = $record['effort_hours_worked_act'];
                
                $ddw_ar = $record['maps']['ddw'];
                if(count($ddw_ar) == 0)
                {
                    $ddw_markup = '';
                } else {
                    asort($ddw_ar);
                    $ddw_markup = "[SORTNUM:" . count($ddw_ar) . "]<span title='" . count($ddw_ar) . " items'>" . implode(', ', $ddw_ar) . "</span>";                
                }

                if(empty($record['root_of_projectid']))
                {
                   $projectyesno = "No"; 
                } else {
                   $projectyesno = "<span title='#" . $record['root_of_projectid'] . "'>Yes</span>";
                }
                
                $purpose_tx = $record['purpose_tx'];
                if (strlen($purpose_tx) > 80) {
                    $purpose_tx = substr($purpose_tx, 0, 80) . '...';
                }
                
                $status_cd = $record['status_cd'];
                if($status_cd != NULL)
                {
                    $status_record = $goal_status_by_code[$status_cd];
                    $status_title_tx = $status_record['title_tx'];
                    $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                    $status_terminal_yn = $status_record['terminal_yn'];
                    $terminalyesno = ($status_terminal_yn == 1 ? 'Yes' : '<span class="colorful-available">No</span>');
                } else {
                    $status_markup = "";
                    $terminalyesno = "";
                }
                

                
                $status_set_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['status_set_dt']);
                $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['updated_dt']);
                
                if (strpos($this->m_aPersonRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                    $sViewDashboardMarkup = '';
                } else {
                    $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('projectid'=>$projectid)));
                    $sCommentsMarkup = "<a title='jump to communications for project#{$projectid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('projectid'=>$projectid)));
                    $sViewDashboardMarkup = "<a title='jump to dashboard for project#{$projectid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";
                    
                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                            , array('query'=>array('projectid'=>($projectid))));
                    $sHierarchyMarkup = "<a "
                        . " title='view dependencies for project#{$projectid}' "
                        . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('projectid'=>$projectid)));
                    $sViewMarkup = "<a title='view details of #{$projectid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if (strpos($this->m_aPersonRights, 'E') === FALSE || !isset($this->m_urls_arr['edit'])) 
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit', $this->m_urls_arr['edit'], array('query' => array('workitemid' => $workitemid)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('projectid'=>$projectid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$projectid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if (strpos($this->m_aPersonRights, 'D') === FALSE || !isset($this->m_urls_arr['delete'])) 
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete', $this->m_urls_arr['delete'], array('query' => array('workitemid' => $workitemid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('projectid'=>$projectid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='jump to delete for project#{$projectid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $goalname_markup = "<span title='project#$projectid with root workitem#$root_goalid'>$project_name</span>";
                $rows .= "\n" 
                        . '<tr id="' .$projectid. '">'
                        . '<td>'
                        . $projectid . '</td><td>'
                        . $ddw_markup . '</td><td>'
                        . $goalname_markup . '</td><td>'
                        . $purpose_tx . '</td><td>'
                        . $owner_markup . '</td><td>'
                        . $record['importance'] . '</td><td>'
                        . $status_markup.'</td>'
                        . '<td class="number">'
                        . $effort_est_markup
                        . '</td>'
                        . '<td class="number">'
                        . $effort_act_markup
                        . '</td><td>'
                        . $terminalyesno.'</td><td>'
                        . $status_set_dt_markup . '</td><td>'
                        . $updated_dt_markup . '</td>'
                        . '<td class="action-options">'    
                                    . $sCommentsMarkup . ' '
                                    . $sViewDashboardMarkup . ' '
                                    . $sHierarchyMarkup . ' '
                                    . $sViewMarkup . ' '
                                    . $sEditMarkup . ' '
                                    . $sDeleteMarkup . '</td>'
                        . '</tr>';
            }

            $form["data_entry_area1"]['table_container']['maintable'] = array('#type' => 'item',
                '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                . '<thead>'
                . '<tr>'
                . '<th colname="id" datatype="numid"><span title="The system unique ID of each project">' . t('ID') . '</span></th>'
                . '<th colname="ddw" datatype="formula"><span title="Open projects that are directly dependent on this project">' . t('DDP').'</span></th>'
                . '<th>' . t('Project Name') . '</th>'
                . '<th>' . t('Mission') . '</th>'
                . '<th>' . t('Owner') . '</th>'
                . '<th>' . t('Importance') . '</th>'
                . '<th>' . t('Status') . '</th>'
                . '<th class="number"><span title="Estimated Effort Hours">' . t('EE') . '</span></th>'
                . '<th class="number"><span title="Actual Effort Hours">' . t('AE') . '</span></th>'
                . '<th><span title="Yes if no further work is expected for this">' . t('Done') . '</span></th>'
                . '<th><span title="Date of most recent status update">' . t('Status Date') . '</span></th>'
                . '<th><span title="Date of most recent change">' . t('Updated') . '</span></th>'
                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody>'
                . $rows
                . '</tbody>'
                . '</table>'
                . '<br>');


            $form["data_entry_area1"]['action_buttons'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['container-inline'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

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
