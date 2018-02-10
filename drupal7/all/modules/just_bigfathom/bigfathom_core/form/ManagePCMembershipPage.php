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

/**
 * This implements a page to manage the privileged collaborators group membership
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManagePCMembershipPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper         = NULL;
    protected $m_urls_arr           = NULL;
    protected $m_aWorkitemsRights   = NULL;
    protected $m_oWorkitemPageHelper      = NULL;
    protected $m_oTextHelper        = NULL;
    protected $m_projectid   = NULL;
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

        module_load_include('php','bigfathom_core','core/GanttChartHelper');
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');

        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $urls_arr = array();
        $urls_arr['dashboard'] = 'bigfathom/dashboards/workitem';
        $urls_arr['comments'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';
        $urls_arr['lock_membership'] = 'bigfathom/sprint/lock_membership';
        $urls_arr['unlock_membership'] = 'bigfathom/sprint/unlock_membership';
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
        $urls_arr['main_visualization'] = '';    // '/sites/all/modules/bigfathom_core/visualization/MapWorkitemsGoals.html';
        
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
        
        $this->m_oWorkitemPageHelper = new \bigfathom\WorkitemPageHelper($urls_arr, NULL, $this->m_projectid);
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'grid-person2pcg_in_project';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];
            $is_project_owner = isset($uprm['detail'][$this->m_projectid]);
            $projectownersbundle = $this->m_oMapHelper->getProjectOwnersBundle($this->m_projectid);         
            $has_project_owner_rights = isset($projectownersbundle['all'][$user->uid]);
            
            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $send_urls = $this->m_urls_arr;
            $send_urls['base_url'] = $base_url;
            $send_urls['images'] = $base_url .'/'. $theme_path.'/images';
            drupal_add_js(array('personid'=>$user->uid,'projectid'=>$this->m_projectid
                    ,'myurls' => $send_urls), 'setting');
            
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/js/jquery-ui.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManagePCMembership.js");
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
                . "Project owners and their delegates can use this page to limit the number of users that can edit project content."
                . "</p>",
                '#suffix' => '</div>',
            );
            
            $allow_mode_edit = $has_project_owner_rights || $is_systemadmin;
            if(!$allow_mode_edit)
            {
                $one_edit_mode_info = $this->m_oMapHelper->getProjectEditModeInfo($this->m_projectid);
                $emterm = $one_edit_mode_info['project_edit_lock_term'];
                $emlabel = $one_edit_mode_info['project_edit_lock_label'];
                $collaboration_mode_panel_markup = "<p><strong>Current Project Collaboration Mode:</strong> $emlabel</p>";
                if($emterm == 'pcgonly')
                {
                    $collaboration_mode_panel_markup .= "<p>The users with current collaboration access are listed below.</p>";
                }
                $form["data_entry_area1"]['bw']['collaboration_mode_panel_area'] = array(
                     '#type' => 'item', 
                     '#prefix' => '<div id="show_colab_mode" class="custom-panel-area">' . $collaboration_mode_panel_markup,
                     '#suffix' => '</div>', 
                     '#tree' => TRUE,
                );
            } else {
                module_load_include('php','bigfathom_core','snippets/SnippetHelper');
                $oSnippetHelper = new \bigfathom\SnippetHelper();
                $collaboration_mode_panel_markup = $oSnippetHelper->getHtmlSnippet("collaboration_mode_panel");            
                $mode_edit_areaid = "custom-query-area-" . $main_tablename;
                $form["data_entry_area1"]['bw']['collaboration_mode_panel_area'] = array(
                     '#type' => 'item', 
                     '#prefix' => '<div id="' . $mode_edit_areaid . '" class="custom-panel-area">' . $collaboration_mode_panel_markup,
                     '#suffix' => '</div>', 
                     '#tree' => TRUE,
                );
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
            
            $rows = "\n";
            $today = $updated_dt = date("Y-m-d", time());
            $cmi = $this->m_oContext->getCurrentMenuItem();

            $bundle = $this->m_oMapHelper->getPeopleInProjectBundle($this->m_projectid);
            $people = $bundle['lookup']['people'];
            $groups = $bundle['lookup']['groups'];
            $pcg_members = $bundle['in_pcg'];
            $in_relevant_groups = $bundle['in_relevant_groups'];
            $map_workitems = $bundle['workitems']['own'];
            $pm_id = $bundle['project']['owner_personid'];
            $delegate_owners = $bundle['project']['delegate_owners'];
            
            if(empty($groups[\bigfathom\Context::$SPECIALGROUPID_EVERYONE]))
            {
                $include_everyone_group = FALSE;
                $everyone_group_nm = "";
            } else {
                $include_everyone_group = TRUE;
                $everyone_group_nm = $groups[\bigfathom\Context::$SPECIALGROUPID_EVERYONE]['group_nm'];
            }
            foreach($people as $personid=>$record)
            {
                
                $is_owner = ($personid == $pm_id);
                $is_delegate_owner = array_key_exists($personid, $delegate_owners);
                $allowrowedit_yn = (!$is_owner && !$is_delegate_owner);
                $fullname = $record['first_nm'] . ' ' . $record['last_nm'];
                if($is_owner)
                {
                    $personinfo = " is the primary project owner";
                    $personmarkupclass = "owner";
                } else if($is_delegate_owner) {
                    $personinfo = " is a delegate project owner";
                    $personmarkupclass = "delegate-owner";
                } else {
                    $personinfo = "";
                    $personmarkupclass = "";
                }
                $person_markup = "<span class='$personmarkupclass' title='#{$personid}{$personinfo}'>$fullname</span>";
                $is_member = ($is_owner || $is_delegate_owner) || array_key_exists($personid, $pcg_members);
                $groups_ar = [];
                if($include_everyone_group)
                {
                    $groups_ar[$everyone_group_nm] = $everyone_group_nm;
                }
                if(array_key_exists($personid, $in_relevant_groups))
                {
                    $ingroups = $in_relevant_groups[$personid];
                    foreach($ingroups as $groupid)
                    {
                        $onegroup = $groups[$groupid];
                        $group_nm = $onegroup['group_nm'];
                        $groups_ar[$group_nm] = "". $group_nm;
                    }
                }
                if(count($groups_ar) == 0)
                {
                    $groups_markup = "";
                } else {
                    $groups_markup = "[SORTNUM:" . count($groups_ar) . "]<ul title='" . count($groups_ar) . " groups'><li>" . implode("<li>", $groups_ar) . "</ul>";
                }
                if(!array_key_exists($personid, $map_workitems))
                {
                    $wi_markup = "NONE";
                } else {
                    $wi_ar = $map_workitems[$personid];
                    sort($wi_ar);
                    $wi_markup = "[SORTNUM:" . count($wi_ar) . "]<span title='" . count($wi_ar) . " workitems'>" . implode(", ", $wi_ar) . "</span>";
                }

                $rows   .= "\n"
                        . "<tr allowrowedit='$allowrowedit_yn' id='$personid' data_projectid='{$this->m_projectid}'>"

                        . '<td>' . $person_markup.'</td>';

                $canedit_classname_markup = '';
                $rows   .='<td class="' . $canedit_classname_markup . '">'
                        . $is_member.'</td>';

                $rows   .=

                          '<td>' . $groups_markup.'</td>'
                        . '<td>' . $wi_markup . '</td>'
                        . '</tr>';
                
            }

            $th_ar = [];
            $th_ar[] = 'class="nowrap" colname="name" datatype="text">'
                        . '<span title="Name of the person">'.t('Name').'</span>';
            
            //$allow_row_edit = TRUE;
            $allow_row_edit = $has_project_owner_rights || $is_systemadmin;
            if($allow_row_edit)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            
            $colname = "ismember";
            $label = "Is PCG Member";
            $help = "Members have ability to edit project content at times that other users are locked out";
            $th_ar[] = 'class="nowrap" colname="' . $colname . '" ' . $editable_col_flag . ' datatype="boolean"">'
                                    . '<span title="'.$help.'">'.t($label).'</span>';
            
            $th_ar[] = 'class="nowrap" colname="groups" datatype="formula">'
                                    . '<span title="All the project-relevant groups in which this person is a member.  If only one group has access to this project, then that is the only group shown here.">'.t('Groups').'</span>';
            $th_ar[] = 'class="nowrap" colname="workitems" datatype="formula">'
                                    . '<span title="The ID values of unfinished workitems which this person owns">'.t('Workitems').'</span>';

            $th_markup = "<th " . implode("</th><th ",$th_ar) . "</th>" ;
            $form["data_entry_area1"]['table_container']['grid'] = array('#type' => 'item',
                     '#markup' => 
                    '<table id="' . $main_tablename . '" class="browserGrid">'
                    . '<thead>' . $th_markup .'</thead>'
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
