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
 * This class selects the current project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class SelectProjectPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oContext  = NULL;
    protected $m_oMapHelper  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_aDataRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_currently_selected_projectid = NULL;
    protected $m_hasSelectedProject = NULL;
    protected $m_is_systemadmin = FALSE;
    protected $m_UPB = NULL;
    
    public function __construct($urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_hasSelectedProject = $this->m_oContext->hasSelectedProject();
        $this->m_currently_selected_projectid = $this->m_oContext->getSelectedProjectID();
        $urls_arr = [];
        $urls_arr['dashboard'] = 'bigfathom/dashboards/oneproject';
        $urls_arr['thispage'] = 'bigfathom/projects/userselectone';
        $urls_arr['clearselection'] = 'bigfathom/projects/clearselection';
        $urls_arr['selectproject'] = 'bigfathom/projects/select';
        $urls_arr['addtop'] = 'bigfathom/projects/addtop';
        $urls_arr['addsub'] = 'bigfathom/projects/addsub';
        $urls_arr['view'] = 'bigfathom/viewproject';
        $urls_arr['brainstorm'] = 'bigfathom/projects/design';
        $urls_arr['update'] = 'bigfathom/editproject';
        $urls_arr['evaluate'] = 'bigfathom/score';
        $urls_arr['redirect_on_select'] = 'bigfathom/projects/workitems/duration';// 'bigfathom/projects';
        $urls_arr['communicate'] = 'bigfathom/project/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';//&projectid=5
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }

        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($this->m_is_systemwriter)
        {
            //The specifics are controled by project in later logic of this page!
            $aDataRights='VAED';
        } else {
            $aDataRights='V';
        }
        $this->m_aDataRights  = $aDataRights;
        $this->m_UPB = $upb;
        
        $this->m_urls_arr = $urls_arr;
        $this->m_oPageHelper = new \bigfathom\ProjectPageHelper($urls_arr);
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
            $main_tablename = 'projects-table';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

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

            global $user;
            $is_systemadmin = $this->m_is_systemadmin; //$usrm['summary']['is_systemadmin'];

            $selectproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('pin');
            $selectproject_current_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('pinned_project');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                
            $rows = "\n";
            $all_people = $this->m_oMapHelper->getPersonsByID();
            $all_pcbyid = $this->m_oMapHelper->getProjectContextsByID(NULL, FALSE, FALSE);
            //$all = $this->m_oMapHelper->getProjectsByID();
            
            $cmi = $this->m_oContext->getCurrentMenuItem();
            $projectinfo_bundle = $this->m_oMapHelper->getAllProjectOverviewBundlesForOneUser($user->uid); 
            $new_allprojects = $projectinfo_bundle['byproject'];
            $project_dep_map = $projectinfo_bundle['project_dep_map'];
            if(empty($project_dep_map))
            {
                $project_d2a_map = [];
            } else {
                $project_d2a_map = $project_dep_map['d2a'];
            }
            $all_relevant_projects = $projectinfo_bundle['byproject'];
            
            $add_project_button_label = "Create New Top Level Project";
            
            $unselected_pin_img_markup = "<img src='$selectproject_icon_url' title='unselected pin icon'>";    
            $selected_pin_img_markup = "<img src='$selectproject_current_icon_url' title='selected pin icon'>"; 
            $pcount = count($all_relevant_projects);
            if($pcount == 0)
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $blurbtext = "No projects are currently available to your account.";
                } else {
                    $blurbtext = "No projects are currently available to your account.  Contact the owner of an existing project if you feel you should be added to their project.";
                }
            } else {
                if(!$this->m_hasSelectedProject)
                {
                    $blurbtext = "No project is currently selected.  Select one by clicking the 'pin' icon $unselected_pin_img_markup on the row of the project you want to work with.";
                } else {
                    if($pcount > 1)
                    {
                        $blurbtext = "A project is already selected (see $selected_pin_img_markup) but you can select a different one by clicking the 'pin' icon $unselected_pin_img_markup on the row of the project you want to work with.";
                    } else {
                        $blurbtext = "A project is already selected (see $selected_pin_img_markup).";
                    }
                }
            }
            if(strpos($this->m_aDataRights,'A') !== FALSE)
            {
                $blurbtext .= "  You can create new projects by clicking the '$add_project_button_label' button at the bottom of this page.";
                $blurbtext = trim($blurbtext);
            }
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . $blurbtext
                . "</p>",
                '#suffix' => '</div>',
            );
                
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );
            
            foreach($all_relevant_projects as $projectid=>$record)
            {
                $projectid_markup = $projectid;
                $surrogate_yn = $record['surrogate_yn'];
                if($surrogate_yn != 1)
                {
                    $root_goalid = $record['root_goalid'];
                    $ddw = $record['maps']['ddw'];
                    $dependent_projectids = $record['maps']['dependent_projectids'];
                    $open_workitems = $record['maps']['open_workitems'];
                    if(!isset($open_workitems['T']))
                    {
                        $open_taskcount = 0;
                    } else {
                        $open_taskcount = count($open_workitems['T']);
                    }
                    if(!isset($open_workitems['G']))
                    {
                        $open_goalcount = 0;
                    } else {
                        $open_goalcount = count($open_workitems['G']);
                    }
                    $deliverable_count = count($record['maps']['deliverablewids']);
                    $proj_name_markup = $record['workitem_nm'];
                    $dep_proj_markup_ar=[];
                    $dep_proj_count = count($dependent_projectids);
                    foreach($dependent_projectids as $deppid)
                    {
                        $odp = $new_allprojects[$deppid];
                        $dep_proj_markup_ar[] = "<span title='" . $odp['workitem_nm'] . "'>$deppid</span>";
                    }
                    if($dep_proj_count > 0)
                    {
                        $dpc_tx = implode(", ", $dep_proj_markup_ar);
                    } else {
                        $dpc_tx = "none";
                    }
                    $dep_proj_markup = "[SORTNUM:$dep_proj_count]$dpc_tx";

                    $ant_proj_markup_ar=[];
                    if(isset($project_d2a_map[$projectid]))
                    {
                        $ants = $project_d2a_map[$projectid];
                        $ant_proj_count = count($ants);
                        foreach($ants as $antpid)
                        {
                            $odp = $new_allprojects[$antpid];
                            $ant_proj_markup_ar[] = "<span title='" . $odp['workitem_nm'] . "'>$antpid</span>";
                        }
                    } else {
                        $ant_proj_count = 0;
                    }
                    if($ant_proj_count > 0)
                    {
                        $apc_tx = implode(", ", $ant_proj_markup_ar);
                    } else {
                        $apc_tx = "none";
                    }
                    $ant_proj_markup = "[SORTNUM:$ant_proj_count]$apc_tx";

                    $map_delegate_owner = $record['maps']['delegate_owner'];

                    $owner_personid = $record['owner_personid'];
                    $isowner = $user->uid == $owner_personid;
                    $owner_persondetail = $all_people[$owner_personid];
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
                            $delegateowner_persondetail = $all_people[$delegate_ownerid];
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

                    $activeyesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                    $mission_tx = $record['mission_tx'];

                    $status_cd = $record['status_cd'];
                    $status_title_tx = $record['status_title_tx'];
                    $status_markup = "<span title='$status_title_tx'>$status_cd</span>";

                    $project_contextid = $record['project_contextid'];
                    $pc_rec = $all_pcbyid[$project_contextid];
                    $pc_shortname = $pc_rec['shortname'];
                    $pc_description_tx = $pc_rec['description_tx'];
                    $project_context_markup = "<span title='$pc_description_tx'>$pc_shortname</span>";

                    if(strlen($mission_tx) > 80)
                    {
                        $mission_tx = substr($mission_tx, 0,80) . '...';
                    }
                    
                    
                    $allowrowedit_yn = $isowner || $is_systemadmin ? 1 : 0;
                    if($allowrowedit_yn)
                    {
                        $owner_sortstr = "<span sorttext='canedit:{$owner_personname}'></span>";
                    } else {
                        $owner_sortstr = "<span sorttext='locked:{$owner_personname}'></span>";
                    }
                    
                    if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                    {
                        $sCommentsMarkup = '';
                        $sViewMarkup = '';
                        $sViewDashboardMarkup = '';
                        $sHierarchyMarkup = '';

                    } else {
                        $communicate_page_url = url($this->m_urls_arr['communicate'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $sCommentsMarkup = "<a title='jump to communications for #{$root_goalid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                        $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $sViewDashboardMarkup = "<a title='jump to dashboard for project#{$projectid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";

                        $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $sViewMarkup = "<a title='view details of project#{$projectid}' href='$view_page_url'><img src='$view_icon_url'/></a>";

                        $hierarchy_page_url = url($this->m_urls_arr['hierarchy'], array('query'=>array('projectid'=>$projectid)));
                        $sHierarchyMarkup = "<a title='view dependencies for project#{$projectid}' href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    }
                    if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['brainstorm']))
                    {
                        $sBrainstormMarkup = '';
                    } else {
                        $sBrainstormMarkup = l('Brainstorm',$this->m_urls_arr['brainstorm'],array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    }
                    if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['selectproject']))
                    {
                        $sSelectProjectMarkup = '';
                    } else {

                        if($this->m_hasSelectedProject)
                        {
                            $cspid_tx = " (currently selected project is #{$this->m_currently_selected_projectid})";
                        } else {
                            $cspid_tx = "";
                        }
                        if( $this->m_currently_selected_projectid != $projectid)
                        {
                            $show_icon_url = $selectproject_icon_url;
                            $show_title = "mark project#{$projectid} as your default selection{$cspid_tx}";
                        } else {
                            $show_icon_url = $selectproject_current_icon_url;
                            $show_title = "already selected project#{$projectid} as your default selection";
                        }

                        $selectproject_page_url = url($this->m_urls_arr['selectproject']
                                , array('query'=>array(
                                    'projectid'=>$projectid
                                    ,'redirect'=>$this->m_urls_arr['redirect_on_select']
                                )));
                        $sSelectProjectMarkup = "<a title='{$show_title}' "
                        . " href='$selectproject_page_url'><img src='$show_icon_url'/></a>";
                    }
                    if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['update']))
                    {
                        $sEditMarkup = '';
                    } else {
                        if(!$allowrowedit_yn)
                        {
                            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_edit');
                            $sEditMarkup = "<span title='only owners can edit attributes of project#{$projectid}'><img src='$edit_icon_url'/></a>";
                        } else {
                            $edit_page_url = url($this->m_urls_arr['update'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                            $sEditMarkup = "<a title='edit attributes of project#{$projectid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                        }
                    }

                    $rows   .= "\n".'<tr>'
                            . '<td>'
                            . $projectid_markup.'</td><td>'
                            . $proj_name_markup.'</td><td>'
                            . $owner_markup.'</td><td>'
                            . $project_context_markup.'</td><td>'
                            . $mission_tx.'</td><td>'
                            . $open_taskcount.'</td><td>'
                            . $open_goalcount.'</td><td>'
                            . $deliverable_count.'</td><td>'
                            . $dep_proj_markup.'</td><td>'
                            . $ant_proj_markup.'</td><td>'
                            . $status_markup.'</td><td>'
                            . $record['status_set_dt'] . '</td><td>'
                            . $record['updated_dt'].'</td>'
                            . '<td class="action-options">'
                                . $sSelectProjectMarkup . ' '
                                . $sCommentsMarkup . ' '
                                //. $sViewDashboardMarkup.' '
                                . $sHierarchyMarkup.' '
                                . $sViewMarkup.' '
                                . $sEditMarkup.' '
                            . '</tr>';
                }
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                     '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th datatype="numid" class="nowrap">'
                                    . '<span title="Unique ID number of the project">'.t('ID').'</span></th>'
                                . '<th>'
                                    . '<span title="Name of the root goal of the project">'.t('Project Name').'</span></th>'
                                . '<th>'.t('Project Leader').'</th>'
                                . '<th>'.t('Context').'</th>'
                                . '<th>'
                                    . '<span title="The mission of this project">'.t('Mission').'</span></th>'
                                . '<th datatype="integer">'
                                    . '<span title="Count of open tasks in the project">'.t('OTC').'</span></th>'
                                . '<th datatype="integer">'
                                    . '<span title="Count of open goals in the project">'.t('OGC').'</span></th>'
                                . '<th datatype="integer">'
                                    . '<span title="Count of open workitems representing one or more deliverables">'.t('ODC').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Dependent Projects: Projects that depend on outcome of this project">'.t('DP').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Antecedent Projects: Projects that impact the outcome of this project">'.t('AP').'</span></th>'
                                . '<th>'.t('Status') . '</th>'
                                . '<th>'.t('Status Date') . '</th>'
                                . '<th>'.t('Updated').'</th>'
                                . '<th datatype="html" class="action-options">'.t('Action Options').'</th>'
                                . '</tr>'
                                . '</thead>'
                                . '<tbody>'
                                . $rows
                                .  '</tbody>'
                                . '</table>'
                                . '<br>');

            $form['data_entry_area1']['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['clearselection']))
            {
                $initial_button_markup = l('ICON_UNPIN Clear Project Selection',$this->m_urls_arr['clearselection']
                                , array('attributes'=>array('class'=>'action-button','title'=>'Unselect the project'))
                        );
                $final_button_markup = str_replace('ICON_UNPIN', '<i class="fa fa-unlink" aria-hidden="true"></i>', $initial_button_markup);
                $form['data_entry_area1']['action_buttons']['clearselection'] = array('#type' => 'item'
                        , '#markup' => $final_button_markup);
            }
            
            if(strpos($this->m_aDataRights,'A') !== FALSE)
            {
                if(isset($this->m_urls_arr['addtop']))
                {
                    /*
                    $add_link_markup = l('Create New Top Level Project',$this->m_urls_arr['addtop']
                                ,array('attributes'=>array('class'=>$html_classname_overrides['action-button']))
                            );
                    $form['data_entry_area1']['action_buttons']['addproject'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
                    */
                    
                    $initial_button_markup = l('ICON_ADD ' . $add_project_button_label
                            , $this->m_urls_arr['addtop']
                            , array('query' => array(
                                'return' => $cmi['link_path']),
                                'attributes'=>array('class'=>'action-button','title'=>'Create a new project in this application')
                                ));
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addproject'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
                /*
                if(isset($this->m_urls_arr['addsub']))
                {
                    if($this->m_oContext->hasSelectedProject())
                    {
                        $parent_projectid = $this->m_oContext->getSelectedProjectID();
                        $parent_projectnm = $this->m_oContext->getSelectedProjectName();
                        $add_link_markup = l("Create Dependent Project of $parent_projectnm",$this->m_urls_arr['addsub']
                                ,array('query'=>array('parent_projectid'=>$parent_projectid, 'return' => $cmi['link_path'])
                                ,'attributes'=>array('class'=>$html_classname_overrides['action-button'])
                                    ));
                        $form['data_entry_area1']['action_buttons']['addsubproject'] = array('#type' => 'item'
                                , '#markup' => $add_link_markup);
                    }
                }
                */
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
