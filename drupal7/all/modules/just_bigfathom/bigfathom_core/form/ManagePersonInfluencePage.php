<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/WorkitemPageHelper.php';
require_once 'helper/ProjectPageHelper.php';

/**
 * This enables a user to declare their influence on workitems
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManagePersonInfluencePage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_oWorkitemPageHelper      = NULL;
    protected $m_oProjectPageHelper = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_parent_projectid   = NULL;
    protected $m_oSnippetHelper     = NULL;
    
    public function __construct($projectid=NULL, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        if(empty($projectid))
        {
            $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        } else {
            $this->m_parent_projectid = $projectid;
        }

        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');

        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = [];
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['add']['workitem'] = 'bigfathom/workitem/add';
        $urls_arr['edit']['workitem'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['workitem'] = 'bigfathom/workitem/delete';
        $urls_arr['edit']['goal'] = 'bigfathom/workitem/edit';
        $urls_arr['edit']['task'] = 'bigfathom/workitem/edit';
        $urls_arr['view']['goal'] = 'bigfathom/workitem/view';
        $urls_arr['view']['task'] = 'bigfathom/workitem/view';
        $urls_arr['delete']['goal'] = 'bigfathom/workitem/delete';
        $urls_arr['delete']['task'] = 'bigfathom/workitem/delete';
        $urls_arr['lock_all_estimates'] = 'bigfathom/project/lock_all_estimates';
        $urls_arr['unlock_all_estimates'] = 'bigfathom/project/unlock_all_estimates';
        
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        $this->m_urls_arr = $urls_arr;

        $cmi = $this->m_oContext->getCurrentMenuItem();
        if(empty($cmi['link_path']))
        {
            $this->m_cmi = array('link_path'=> $urls_arr['return']);
        } else {
            $this->m_cmi = $cmi;    //['link_path'] = $urls_arr['return'];
        }
        $aWorkitemsRights='VAED';
        $this->m_aWorkitemsRights  = $aWorkitemsRights;
        
        $this->m_oWorkitemPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_parent_projectid);
        $this->m_oProjectPageHelper = new \bigfathom\ProjectPageHelper($urls_arr, NULL, $this->m_parent_projectid);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'grid-subproject-members';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManagePersonInfluenceTable.js");
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
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "Assess your potential influence on each workitem.  Disregard whether you are currently a participant in the workitem. Score by applying your personal opinion of the workitem's utility and of your ability to contribute to the successful completion of each item.  Changes are saved as you make them."
                . "</p>",
                '#suffix' => '</div>',
            );
            

            $project_record = $this->m_oMapHelper->getProjectRecord($this->m_parent_projectid);
            $project_status_terminal_yn = $project_record['status_terminal_yn'];
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
            
            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];

            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_parent_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_parent_projectid);
            $subproj_and_proj_map_bundle = $this->m_oMapHelper->getSubprojects2ProjectMapBundle($this->m_parent_projectid);
            if(array_key_exists($this->m_parent_projectid, $subproj_and_proj_map_bundle['subproject2project']))
            {
                $subproject2project = $subproj_and_proj_map_bundle['subproject2project'][$this->m_parent_projectid];
            } else {
                $subproject2project = [];
            }
            
            $newbundle = $this->m_oMapHelper->getWorkitem2InfluenceList($this->m_parent_projectid, $user->uid, $user->uid);//, $about_personid=NULL, $created_by_personid=NULL);
            $self_assessment = $newbundle['assessment_map']['self'];
            $today = $updated_dt = date("Y-m-d", time());
            
            if(empty($min_date))
            {
                if(!empty($max_date))
                {
                    $min_date = $max_date;
                } else {
                    $min_date = $today;
                }
            }
            if(empty($max_date))
            {
                if(!empty($min_date))
                {
                    $max_date = $min_date;
                } else {
                    $max_date = $today;
                }
            }
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            if($project_editable)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            
            $bundle = $this->m_oMapHelper->getRichWorkitemsByIDBundle($this->m_parent_projectid);

            $all = $bundle['workitems'];
            $min_date = $bundle['dates']['min_dt'];
            $max_date = $bundle['dates']['max_dt'];
            $status_lookup = $bundle['status_lookup'];
            
            $root_goalid = $bundle['metadata']['root_goalid'];
            $root_workitem = $all[$root_goalid];
            $primary_owner = $root_workitem['owner_personid'];
            $project_owner_idlookup[$primary_owner] = $primary_owner;
            if(isset($root_workitem['maps']['delegate_owner']))
            {
                foreach($root_workitem['maps']['delegate_owner'] as $dopid)
                {
                    $project_owner_idlookup[$dopid] = $dopid;
                }
            }
            
            $has_project_owner_rights = isset($project_owner_idlookup[$user->uid]);
            
            
            foreach($all as $workitemid=>$record)
            {
                
                $status_cd = $record['status_cd'];
                $status_record = $status_lookup[$status_cd];
                $status_terminal_yn = $status_record['terminal_yn'];
                if($status_terminal_yn != 1)
                {
                    $is_level[4] = FALSE;
                    $is_level[3] = FALSE;
                    $is_level[2] = FALSE;
                    $is_level[1] = FALSE;
                    $is_level[0] = FALSE;
                    $noidea = FALSE;
                    $influence_score = NULL;
                    
                    if(!array_key_exists($workitemid, $self_assessment))
                    {
                        $noidea = TRUE;
                    } else {
                        $existing_influence = $self_assessment[$workitemid];
                        $influence_score = $existing_influence['influence'];
                        $influence_level = $existing_influence['influence_level'];
                        $is_level[$influence_level] = TRUE;
                    }
                    
                    $nativeid = $record['nativeid'];
                    $workitem_nm = $record['workitem_nm'];
                    $purpose_tx = $record['purpose_tx'];
                    $itemtype = $record['type'];
                    $workitemid_markup = $nativeid;
                    $is_project_root = !empty($record['root_of_projectid']);
                    $typeletter = $record['typeletter'];
                    if($is_project_root)
                    {
                        $typeletter = 'P';    
                    }
                    if($typeletter == 'P')
                    {
                        $type_ordernum = 0;
                    } else 
                    if($typeletter == 'G')
                    {
                        $type_ordernum = 1;
                    } else 
                    if($typeletter == 'T')
                    {
                        $type_ordernum = 2;
                    } else 
                    if($typeletter == 'Q')
                    {
                        $type_ordernum = 3;
                    } else 
                    if($typeletter == 'X')
                    {
                        $type_ordernum = 4;
                    } else {
                        //New?
                        $type_ordernum = 99;
                    }

                    $typeiconurl = \bigfathom\UtilityGeneralFormulas::getIconURLForWorkitemTypeCode($typeletter, true);
                    $typeletter_markup = "[SORTNUM:{$type_ordernum}]" . $typeletter . " <img src='$typeiconurl' /></span>";
                    $itembasetype = ($itemtype == 'goal' ? 'goal' : 'task');
                    if($status_cd != NULL)
                    {
                        
                        $status_record = $status_lookup[$status_cd];
                        $status_terminal_yn = $status_record['terminal_yn'];
                        $mb = \bigfathom\MarkupHelper::getStatusCodeMarkupBundle($status_record);
                        $status_markup = $mb['status_code'];
                        $terminalyesno = $mb['terminal_yesno'];

                    } else {
                        $status_markup = "";
                        $terminalyesno = "";
                    }
                    $owner_personid = $record['owner_personid'];
                    $map_delegate_owner = $record['maps']['delegate_owner'];
                    if($is_project_root)
                    {
                        $typename4gantt = 'proj';
                    } else {
                        $typename4gantt = $itembasetype;
                    }
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
                        $owner_personname .= "+" . count($delgates);
                    }
                    $owner_markup = "<span title='$owner_txt'>".$owner_personname."</span>";

                    $planned_start_dt = $record['planned_start_dt'];
                    $planned_end_dt = $record['planned_end_dt'];

                    $actual_start_dt = $record['actual_start_dt'];
                    $actual_end_dt = $record['actual_end_dt'];


                    $allowrowedit_yn = TRUE;    //everyone can edit their own assessment!!!! $has_project_owner_rights || $isowner || $is_systemadmin ? 1 : 0;
                    if($allowrowedit_yn)
                    {
                        $owner_sortstr = "<span sorttext='canedit:{$owner_personname}'></span>";
                    } else {
                        $owner_sortstr = "<span sorttext='locked:{$owner_personname}'></span>";
                    }

                    if (strpos($this->m_aWorkitemsRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                    {
                        $sCommentsMarkup = '';
                        $sViewMarkup = '';
                    } else {
                        $communicate_page_url = url($this->m_urls_arr['comments'], array('query'=>array('workitemid'=>$workitemid, 'return' => $cmi['link_path'])));
                        $sCommentsMarkup = "<a title='jump to communications for #{$workitemid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                        $hierarchy_page_url = url($this->m_urls_arr['hierarchy']
                                , array('query'=>array('projectid'=>($this->m_parent_projectid), 'jump2workitemid'=>$workitemid)));
                        $sHierarchyMarkup = "<a "
                            . " title='view dependencies for goal#{$workitemid} in project#{$this->m_parent_projectid}' "
                            . " href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";

                    }

                    if(strpos($this->m_aWorkitemsRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                    {
                        $sViewMarkup = '';
                    } else {
                        //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('equipmentid'=>$equipmentid)));
                        $view_page_url = url($this->m_urls_arr['view'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                        $sViewMarkup = "<a title='view {$workitemid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                    {
                        $sEditMarkup = '';
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                        $sEditMarkup = "<a title='edit {$workitemid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                    }
                    if(!$allowrowedit_yn || strpos($this->m_aWorkitemsRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                    {
                        $sDeleteMarkup = '';
                    } else {
                        $delete_page_url = url($this->m_urls_arr['delete'][$itembasetype], array('query'=>array('workitemid'=>$nativeid, 'return' => $cmi['link_path'])));
                        $sDeleteMarkup = "<a title='delete {$workitemid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                    }

                    if(!empty($actual_start_dt))
                    {
                        $start_dt = $actual_start_dt;
                        $start_dt_type_cd = 'A';
                        $start_dt_locked_markup = 1;
                    } else {
                        $start_dt = $planned_start_dt;
                        $start_dt_type_cd = 'E';
                        if($record['planned_start_dt_locked_yn'] == 1)
                        {
                            $start_dt_locked_markup = 1;
                        } else {
                            $start_dt_locked_markup = 0;
                        }
                    }
                    if(!empty($actual_end_dt))
                    {
                        $end_dt = $actual_end_dt;
                        $end_dt_type_cd = 'A';
                        $end_dt_locked_markup = 1;
                    } else {
                        $end_dt = $planned_end_dt;
                        $end_dt_type_cd = 'E';
                        if($record['planned_end_dt_locked_yn'] == 1)
                        {
                            $end_dt_locked_markup = 1;
                        } else {
                            $end_dt_locked_markup = 0;
                        }
                    }

                    $start_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($start_dt);
                    $end_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($end_dt);


                    $rows   .= "\n"
                            . "<tr allowrowedit='$allowrowedit_yn' id='$workitemid' data_personid='{$user->uid}' data_parent_projectid='{$this->m_parent_projectid}'>"

                            . "<td>" . $workitemid_markup.'</td>'

                            . '<td>' . $typeletter_markup.'</td>'

                            . '<td>' . $workitem_nm.'</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $is_level[4] . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $is_level[3] . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $is_level[2] . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $is_level[1] . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $is_level[0] . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $noidea . '</td>'

                            . '<td class="' . $canedit_classname_markup . '">'
                            . $influence_score . '</td>'

                            . '<td>' . $purpose_tx.'</td>'

                            . '<td>' . $owner_sortstr.$owner_markup.'</td>'

                            . '<td>' . $status_markup.'</td>'

                            . '<td>'
                            . $start_dt_markup . '</td>'

                            . '<td>'
                            . $end_dt_markup . '</td>'

                            .'<td class="action-options">'
                                . $sCommentsMarkup.' '
                                . $sHierarchyMarkup . ' '
                                . $sViewMarkup.' '
                                . $sEditMarkup.' '
                            .'</tr>';
                }
            }
            
            $form["data_entry_area1"]['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead><tr>'
                    . '<th class="nowrap" colname="id" datatype="numid">'
                        . '<span title="Unique ID of this work item">'.t('ID').'</span></th>'
                    . '<th class="nowrap" colname="typeletter" datatype="formula">'
                        . '<span title="G=Goal, P=Goal which is root of a project, T=Task dependent on an internal resource, X=Task dependent on an external resource, Q=Task dependent on a non-human resource">'.t('Type').'</span></th>'
                    . '<th class="nowrap" colname="name">'
                        . '<span title="Name of this work item">'.t('Name').'</span></th>'


                
                    . '<th class="nowrap" colname="is_level4" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You care about this workitem and can have DIRECT influence in contributing to its successful completion">'
                        .t('Direct').'</span></th>'
                
                    . '<th class="nowrap" colname="is_level3" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You care about this workitem and can have INDIRECT influence in contributing to its successful completion">'
                        .t('Indirect').'</span></th>'
                
                    . '<th class="nowrap" colname="is_level2" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You CARE about this workitem but have NO INFLUENCE in contributing to its successful completion">'
                        .t('No Influence').'</span></th>'
                
                    . '<th class="nowrap" colname="is_level1" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You DO NOT SEE ANY VALUE in completing this workitem BUT MIGHT have some INFLUENCE on its outcome">'
                        .t('No Interest').'</span></th>'
                
                    . '<th class="nowrap" colname="is_level0" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You do not see any value in completing this workitem AND do not have any influence on its outcome">'
                        .t('None!').'</span></th>'
                
                    . '<th class="nowrap" colname="is_unknown" datatype="boolean" ' . $editable_col_flag . ' >'
                        . '<span title="You have not considered your influence for this workitem">'
                        .t('Unknown').'</span></th>'
                
                    . '<th class="nowrap" colname="influence" datatype="double" ' . $editable_col_flag . ' named_validator="[0,100]">'
                        . '<span title="Influence score in range [0,100] where 0=no influence, 100=maximal influence">'
                        .t('IS').'</span></th>'
                
                    
                    . '<th class="nowrap" colname="purpose_tx">'
                        . '<span title="The declared purpose of the workitem">'.t('Purpose').'</span></th>'
                    . '<th class="nowrap" colname="owner" datatype="sortablehtml">'
                        . '<span title="Owner of the work item">'.t('Owner').'</span></th>'
                    . '<th class="nowrap" colname="status_cd">'
                        . '<span title="The status code of the workitem">'.t('Status').'</span></th>'
                    . '<th class="nowrap" colname="start_dt" datatype="date">'
                        . '<span title="The start date for the work item">'.t('Start Date').'</span></th>'
                    . '<th colname="end_dt" datatype="date">'
                        . '<span title="The end date for the work item">'.t('End Date').'</span></th>'
                    . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
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
