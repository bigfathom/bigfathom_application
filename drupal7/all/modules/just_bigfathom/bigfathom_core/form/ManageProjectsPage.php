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
class ManageProjectsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_aDataRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oContext    = NULL;
    protected $m_hasSelectedProject = NULL;
    protected $m_selectedProjectSummary = NULL;
    protected $m_is_systemadmin = FALSE;
    protected $m_UPB = NULL;
    
    public function __construct($urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_hasSelectedProject = $this->m_oContext->hasSelectedProject();
        $this->m_selectedProjectSummary = $this->m_oContext->getSelectedProjectSummary();
        
        $urls_arr = [];
        $urls_arr['thispage'] = 'bigfathom/sitemanage/projects';
        $urls_arr['dashboard'] = 'bigfathom/dashboards/oneproject';
        $urls_arr['createprojectfromtemplate'] = 'bigfathom/createprojectfromtemplate';
        $urls_arr['createprojectwithouttemplate'] = 'bigfathom/projects/addtop';
        $urls_arr['duplicate'] = 'bigfathom/duplicateproject';
        $urls_arr['download'] = 'bigfathom/downloadprojectinfo';
        $urls_arr['edit'] = 'bigfathom/editproject';
        $urls_arr['view'] = 'bigfathom/viewproject';
        $urls_arr['delete'] = 'bigfathom/deleteproject';
        $urls_arr['communicate'] = 'bigfathom/project/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';//&projectid=5
        $urls_arr['refresh_from_all_remote_uri'] = 'bigfathom/sitemanage/refresh_from_all_remote_uri';
        $urls_arr['create_template'] = 'bigfathom/createtemplatefromproject';
        
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

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all_people = $this->m_oMapHelper->getPersonsByID();
            $all_pcbyid = $this->m_oMapHelper->getProjectContextsByID(NULL, FALSE, FALSE);

            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $create_template_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('intotemplate');

            global $user;
            $is_systemadmin = $this->m_is_systemadmin; //$usrm['summary']['is_systemadmin'];
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            $active_yn = 1;
            if($is_systemadmin)
            {
                $project_filter = NULL;
            } else {
                $relevant_project_map = $this->m_oMapHelper->getRelevantProjectsMap($user->uid);
                $project_filter = array_keys($relevant_project_map);
            }
            $projectinfo_bundle = $this->m_oMapHelper->getAllProjectOverviewBundlesForAllUsers($project_filter, $active_yn); 
            $project_dep_map = $projectinfo_bundle['project_dep_map'];
            if(empty($project_dep_map))
            {
                $project_d2a_map = [];
            } else {
                $project_d2a_map = $project_dep_map['d2a'];
            }
            $all_relevant_projects = $projectinfo_bundle['byproject'];
            foreach($all_relevant_projects as $projectid=>$record)
            {
                
                $projectid_markup = $projectid;
                $root_goalid = $record['root_goalid'];
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
                    $odp = $all_relevant_projects[$deppid];
                    $dep_proj_markup_ar[] = "<span title='" . $odp['workitem_nm'] . "'>$deppid</span>";
                }
                $dep_proj_markup = "[SORTNUM:$dep_proj_count]" . implode(", ", $dep_proj_markup_ar);

                $ant_proj_markup_ar=[];
                if(isset($project_d2a_map[$projectid]))
                {
                    $ants = $project_d2a_map[$projectid];
                    $ant_proj_count = count($ants);
                    foreach($ants as $antpid)
                    {
                        $odp = $all_relevant_projects[$antpid];
                        $ant_proj_markup_ar[] = "<span title='" . $odp['workitem_nm'] . "'>$antpid</span>";
                    }
                } else {
                    $ant_proj_count = 0;
                }

                //$owner_personid = $record['owner_personid'];
                //$project_mgr = $all_people[$owner_personid];
                //$project_mgr_nm = $project_mgr['first_nm'] . " " . $project_mgr['last_nm'];
                
                $project_active_yn = $record['project_active_yn'];
                $activeyesno = ($project_active_yn == 1 ? 'Yes' : 'No');
                $mission_tx = $record['mission_tx'];
                
                $status_cd = $record['status_cd'];
                $status_title_tx = $record['status_title_tx'];
                $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                
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
                
                $project_contextid = $record['project_contextid'];
                $pc_rec = $all_pcbyid[$project_contextid];
                $pc_shortname = $pc_rec['shortname'];
                $pc_description_tx = $pc_rec['description_tx'];
                $project_context_markup = "<span title='$pc_description_tx'>$pc_shortname</span>";

                $publishedrefname  = $record["publishedrefname"];
                $canpublish_yn  = $record["allow_status_publish_yn"];
                $surrogate_yn = $record['surrogate_yn'];
                $allow_refresh_from_remote_yn  = $record["allow_refresh_from_remote_yn"];
                $remote_uri  = trim($record["remote_uri"]);

                $publishedrefname_markup = $publishedrefname;
                
                if(empty($remote_uri))
                {
                    $allow_refresh_from_remote_yn_markup = "";
                    $remote_uri_markup = "";
                } else {
                    if($allow_refresh_from_remote_yn != 1)
                    {
                        $allow_refresh_from_remote_yn_markup = "<span title='The status of project#$projectid is blocked from remote refresh'>No</span>";
                    } else {
                        $allow_refresh_from_remote_yn_markup = "<span class='colorful-available' title='The status of project#$projectid can be refreshed from remote URI'>Yes</span>";
                    }
                    $remote_uri_markup = "<a href='$remote_uri'>$remote_uri</a>";
                }
                
                if($canpublish_yn != 1)
                {
                    $canpublish_yesno_markup = "<span title='The status of project#$projectid is blocked from publishing'>No</span>";
                } else {
                    $canpublish_yesno_markup = "<span class='colorful-available' title='The status of project#$projectid can be published'>Yes</span>";
                }
                if($surrogate_yn != 1)
                {
                    $surrogate_yn_markup = "<span title='Project#$projectid detail is maintained in this application instance'>No</span>";
                    $open_taskcount_markup = "[SORTNUM:$open_taskcount]$open_taskcount";
                    $open_goalcount_markup = "[SORTNUM:$open_goalcount]$open_goalcount";
                    $deliverable_count_markup = $deliverable_count;
                    $ant_proj_markup = "[SORTNUM:$ant_proj_count]" . implode(", ", $ant_proj_markup_ar);
                } else {
                    $surrogate_yn_markup = "<span class='colorful-available' title='Project#$projectid is a surrogate for a project outside this application instance'>Yes</span>";
                    $open_taskcount_markup = "<span title='Unknown for surrogate projects'>NA</span>";
                    $open_goalcount_markup = "<span title='Unknown for surrogate projects'>NA</span>";
                    $deliverable_count_markup = "<span title='Unknown for surrogate projects'>NA</span>";
                    $ant_proj_markup = "<span title='Unknown for surrogate projects'>NA</span>";
                }
                
                if(strlen($mission_tx) > 80)
                {
                    $mission_tx = substr($mission_tx, 0,80) . '...';
                }
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                    $sViewDashboardMarkup = '';
                    $sDownloadMarkup = '';
                } else {
                    //$communicate_page_url = url($this->m_urls_arr['communicate'], array('query'=>array('projectid'=>$projectid)));
                    //$sCommentsMarkup = "<a title='jump to communications for workitem#{$root_goalid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    $communicate_page_url = url($this->m_urls_arr['communicate'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    $sCommentsMarkup = "<a title='jump to communications for #{$root_goalid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";
                    
                    $dashboard_page_url = url($this->m_urls_arr['dashboard'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    $sViewDashboardMarkup = "<a title='jump to dashboard for project#{$projectid}' href='$dashboard_page_url'><img src='$dashboard_icon_url'/></a>";
                    
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view details of project#{$projectid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    
                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    $sHierarchyMarkup = "<a title='view dependencies for project#{$projectid}' href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    
                    if(!empty($publishedrefname))
                    {
                        if(empty($remote_uri_markup))
                        {
                            $download_page_url = url($this->m_urls_arr['download'], array('query'=>array('publishedrefname'=>$publishedrefname, 'return' => $cmi['link_path'])));
                            $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('download');
                            $sDownloadMarkup = "<a title='download latest published status for project#{$projectid}' href='$download_page_url'><img src='$download_icon_url'/></a>";
                        } else {
                            $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('nothing2download');
                            $sDownloadMarkup = "<span title='Download latest status for project#{$projectid} from {$remote_uri}'><img src='$download_icon_url'/></span>";
                        }
                    } else {
                        $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('nothing2download');
                        $sDownloadMarkup = "<span title='There is no reference name for project#{$projectid}'><img src='$download_icon_url'/></span>";
                    }
                }
                if(!$is_systemadmin && (strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit'])))
                {
                    $sDuplicateMarkup = '';
                } else {
                    if(!$is_systemadmin && ($project_active_yn != 1 || !$isowner))
                    {
                        $duplicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_duplicate');
                        if($project_active_yn != 1)
                        {
                            $sDuplicateMarkup = "<span title='cannot duplicate project#{$projectid} because not active'><img src='$duplicate_icon_url'/></span>";
                        } else {
                            $sDuplicateMarkup = "<span title='only owners can duplicate project#{$projectid}'><img src='$duplicate_icon_url'/></span>";
                        }
                    } else {
                        $duplicate_page_url = url($this->m_urls_arr['duplicate'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $duplicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('duplicate');
                        $sDuplicateMarkup = "<a title='duplicate project#{$projectid}' href='$duplicate_page_url'><img src='$duplicate_icon_url'/></a>";
                    }
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                    $sCreateThingMarkup = '';
                } else {
                    if(!$is_systemadmin && !$isowner)
                    {
                        $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_edit');
                        $sEditMarkup = "<span title='only owners can edit #{$projectid}' href='$edit_page_url'><img src='$edit_icon_url'/></span>";
                        $sCreateThingMarkup = '';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                        $sEditMarkup = "<a title='edit #{$projectid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                        
                        $creatething_icon_url = $create_template_icon_url;
                        $creatething_tooltip = "create a template based on project#{$projectid}";
                        $creatething_key = "create_template";
                        $creatething_page_url = url($this->m_urls_arr[$creatething_key]
                                , array('query'=>array('source_projectid'=>$projectid
                                , 'return' => $cmi['link_path'])));
                                //, 'rparams' => $rparams_encoded)));
                        $sCreateThingMarkup = "<a title='{$creatething_tooltip}' href='$creatething_page_url'><img src='$creatething_icon_url'/></a>";
                    }
                }
                if($project_active_yn == 1 || strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('projectid'=>$projectid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('projectid'=>$projectid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='jump to delete for #{$projectid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
                $rows   .= "\n".'<tr>'
                        . '<td>'
                        . $projectid_markup.'</td><td>'
                        . $proj_name_markup.'</td><td>'
                        . $owner_markup.'</td><td>'
                        . $project_context_markup.'</td><td>'
                        . $mission_tx.'</td><td>'
                        . $open_goalcount_markup.'</td><td>'
                        . $open_taskcount_markup.'</td><td>'
                        . $deliverable_count_markup.'</td><td>'
                        . $dep_proj_markup.'</td><td>'
                        . $ant_proj_markup.'</td><td>'
                        . $publishedrefname_markup . '</td><td>'
                        . $canpublish_yesno_markup . '</td><td>'
                        . $surrogate_yn_markup.'</td><td>'                        
                        . $allow_refresh_from_remote_yn_markup . '</td><td>'
                        . $remote_uri_markup . '</td><td>'
                        . $activeyesno . '</td><td>'
                        . $status_markup.'</td><td>'
                        . $record['status_set_dt'] . '</td><td>'
                        . $record['updated_dt'].'</td>'
                        . '<td class="action-options">'
                                    . $sCommentsMarkup . ' '
                                    //. $sViewDashboardMarkup . ' '
                                    . $sHierarchyMarkup . ' '
                                    . $sDownloadMarkup . ' ' 
                                    . $sCreateThingMarkup . ' '
                                    . $sViewMarkup . ' '
                                    . $sDuplicateMarkup . ' '
                                    . $sEditMarkup . ' '
                                    . $sDeleteMarkup 
                        . '</td>'
                        . '</tr>';
                
            }
            
            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                     '#markup' => '<table id="table-selectproject" class="browserGrid">'
                                . '<thead class="nowrap">'
                                . '<tr>'
                                . '<th datatype="numid" class="nowrap">'
                                    . '<span title="Unique ID number of the project">'.t('ID').'</span></th>'
                                . '<th>'
                                    . '<span title="Name of the root goal of the project">'.t('Project Name').'</span></th>'
                                . '<th>'.t('Project Leader').'</th>'
                                . '<th>'.t('Context').'</th>'
                                . '<th>'
                                    . '<span title="The mission of this project">'.t('Mission').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Count of open goals in the project">'.t('OGC').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Count of open tasks in the project">'.t('OTC').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Count of open workitems representing one or more deliverables">'.t('ODC').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Project IDs of projects that directly depend on outcome of this project">'.t('DDP').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Project IDs of projects that directly impact the outcome of this project">'.t('DAP').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="A unique reference name by which users can find this project">'.t('RefName').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="Status publishing allowed">'.t('SPA').'</span></th>'
                                . '<th>'
                                    . '<span title="Is this project a surrogate that is only tracking status instead of detail?">'.t('SP').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="Update from Remote Resource Allowed">'.t('RRA').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="The remote resource from which latest project status can be pulled">'.t('Remote URI').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="Only active projects are available for use">'.t('Active').'</span></th>'
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

            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            /*
            if(isset($this->m_urls_arr['createprojectfromtemplate']))
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $add1_link_markup = l('Create New Project from Template',$this->m_urls_arr['createprojectfromtemplate']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $form['data_entry_area1']['action_buttons']['createprojectfromtemplate'] = array('#type' => 'item'
                            , '#markup' => $add1_link_markup);
                }
            }
             */
            if(isset($this->m_urls_arr['createprojectwithouttemplate']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    /*
                    $add2_link_markup = l('Create New Project',$this->m_urls_arr['createprojectwithouttemplate']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $form['data_entry_area1']['action_buttons']['createprojectwithouttemplate'] = array('#type' => 'item'
                            , '#markup' => $add2_link_markup);
                    */
                    
                    $add_project_button_label = 'ICON_ADD Create New Project';
                    $initial_button_markup = l($add_project_button_label
                            , $this->m_urls_arr['createprojectwithouttemplate']
                            , array(
                                    'query' => array('return' => $cmi['link_path']),
                                    'attributes'=>array('class'=>'action-button',
                                        'title'=>'Create a new project in this application')
                                ));
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['createprojectwithouttemplate'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
            
                }
            }

            
            if(isset($this->m_urls_arr['refresh_from_all_remote_uri']))
            {
                if(strpos($this->m_aDataRights,'E') !== FALSE)
                {
                    //Jumps to a dedicated page
                    $refresh_link_markup = l('Refresh from All Allowed Remote URI'
                                ,$this->m_urls_arr['refresh_from_all_remote_uri']
                                , array(
                                        'query' => array('return' => $cmi['link_path']),
                                        'attributes'=>array('class'=>'action-button',
                                            'title'=>'Will attempt to update all allowed projects from their declared remote URIs')
                                        ));
                    $form['data_entry_area1']['action_buttons']['refresh_from_all_remote_uri'] = array('#type' => 'item'
                            , '#markup' => $refresh_link_markup);
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
