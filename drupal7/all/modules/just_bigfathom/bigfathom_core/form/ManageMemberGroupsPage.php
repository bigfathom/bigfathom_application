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
 * This implements a page to manage the groups assigned to a project
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageMemberGroupsPage extends \bigfathom\ASimpleFormPage
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
            $main_tablename = 'grid-group2project';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageMemberGroups.js");
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

            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];
            $is_project_owner = isset($uprm['detail'][$this->m_projectid]);
            $projectownersbundle = $this->m_oMapHelper->getProjectOwnersBundle($this->m_projectid);         
            $has_project_owner_rights = isset($projectownersbundle['all'][$user->uid]);
            
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;

            if($has_project_owner_rights)
            {
                $blurbtext = "Select groups that are relevant to the selected project.  All people in the selected groups become collaborators on the current project.  Changes are saved as you make them.";
            } else {
                $blurbtext = "Only the project owner and their delegates can select groups for membership in the project.  All people in the selected groups become collaborators on the current project.";
            }
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . $blurbtext
                . "</p>",
                '#suffix' => '</div>',
            );
            
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

            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

            $rows = "\n";
            $people = $this->m_oMapHelper->getPersonsInProjectByID($this->m_projectid);
            $bundle = $this->m_oMapHelper->getGroupsInProjectBundle($this->m_projectid);
            $member_groups = $bundle['members'];
            $all_groups = $bundle['detail'];
            $today = $updated_dt = date("Y-m-d", time());
            
            $cmi = $this->m_oContext->getCurrentMenuItem();

            $editable_group_count = 0;
            foreach($all_groups as $groupid=>$record)
            {
                $is_member = !empty($record['mapped_dt']);
                if($has_project_owner_rights || $is_member)
                {

                    
                    $leader_personid = $record['leader_personid'];
                    $leader_fullname = $record['leader_fullname'];
                    $map_delegate_leader = [];   //TODO $record['maps']['delegate_owner'];

                    $is_group_leader = $user->uid == $leader_personid;
                    $leader_txt = "#{$leader_personid} and ";
                    if(count($map_delegate_leader) == 0)
                    {
                        $leader_txt .= "no delegate leaders";
                    } else {
                        $delgates = [];
                        foreach($map_delegate_leader as $delegate_ownerid)
                        {
                            if(!$is_group_leader && $user->uid === $delegate_ownerid)
                            {
                                $is_group_leader = TRUE;    
                            }
                            $delegateowner_persondetail = $people[$delegate_ownerid];
                            $delegateowner_personname = $delegateowner_persondetail['first_nm'] . " " . $delegateowner_persondetail['last_nm'];
                            $delgates[] = "{$delegateowner_personname}";
                        }
                        $doc = count($map_delegate_leader);
                        if($doc < 2)
                        {
                            $leader_txt .= "1 delegate leader " . implode(" and ", $delgates);
                        } else {
                            $leader_txt .= count($map_delegate_leader) . " delegate leaders: " . implode(" and ", $delgates);
                        }
                        $leader_fullname .= "+" . count($delgates);
                    }
                    $leader_markup = "<span title='$leader_txt'>".$leader_fullname."</span>";

                    $group_nm = $record['group_nm'];
                    $purpose_tx = $record['purpose_tx'];

                    $allowrowedit_yn = $has_project_owner_rights || $is_systemadmin ? 1 : 0;
                    
                    if($allowrowedit_yn)
                    {
                        $editable_group_count++;
                        $leader_sortstr = "<span sorttext='canedit:{$leader_fullname}'></span>";
                    } else {
                        $leader_sortstr = "<span sorttext='locked:{$leader_fullname}'></span>";
                    }

                    $rows   .= "\n"
                            . "<tr allowrowedit='$allowrowedit_yn' id='$groupid' data_projectid='{$this->m_projectid}'>"
                            
                            . '<td>' . $group_nm.'</td>'
                            . '<td>' . $purpose_tx.'</td>';

                    $canedit_classname_markup = '';
                    $rows   .='<td class="' . $canedit_classname_markup . '">'
                            . $is_member.'</td>';
                            
                    $rows   .=
                            
                             '<td>' . $leader_sortstr.$leader_markup.'</td>'
                            .'</tr>';
                    
                }
                
            }

            $th_ar = [];
            $th_ar[] = 'class="nowrap" colname="name" datatype="text">'
                        . '<span title="Name of the group">'.t('Name').'</span>';
            
            $th_ar[] = 'class="nowrap" colname="purpose" datatype="text">'
                        . '<span title="Purpose of the group">'.t('Purpose').'</span>';

            $allow_row_edit = TRUE;
            if($allow_row_edit)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            
            $colname = "ismember";
            $label = "Relevant";
            $help = "Members of relevant groups are eligible to participate in the project";
            $th_ar[] = 'class="nowrap" colname="' . $colname . '" ' . $editable_col_flag . ' datatype="boolean"">'
                                    . '<span title="'.$help.'">'.t($label).'</span>';
            
            $th_ar[] = 'class="nowrap" colname="leader" datatype="sortablehtml">'
                                    . '<span title="Leader of the group">'.t('Leader').'</span>';

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

            $can_add_workitem = $is_systemadmin || $is_project_owner || $editable_group_count > 0;
            
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
