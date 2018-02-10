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
 *
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';

/**
 * Prioritized information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Dashboard4UserPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oDashHelp = NULL;
    protected $m_personid = NULL;
    protected $m_personrolebundle = NULL;
    protected $m_currentappuser_personid = NULL;
    protected $m_urls_arr = NULL;
    
    public function __construct($personid = NULL)
    {
        module_load_include('php','bigfathom_core','core/DashboardHelper');
        module_load_include('php','bigfathom_core','form/TopInfoManageDetailsPage');

        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oDashHelp = new \bigfathom\DashboardHelper();
        global $user;
        $this->m_currentappuser_personid = $user->uid;
        if(empty($personid))
        {
            $personid = $user->uid;
        }
        $this->m_personid = $personid;
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) 
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
        if(!$loaded_uah)
        {
            throw new \Exception('Failed to load the UserAccountHelper class');
        }
        $this->m_oUAH = new \bigfathom\UserAccountHelper();
        $this->m_personrolebundle = $this->m_oUAH->getPersonSystemRoleBundle($this->m_personid);
        
        //Define the URLs
        $urls_arr = array();
        $urls_arr['project_communicate'] = 'bigfathom/project/mng_comments';//&projectid=5
        $urls_arr['project_dashboard'] = 'bigfathom/dashboards/oneproject';//&projectid=5
        $urls_arr['hierarchy'] = 'bigfathom/projects/design/mapprojectcontent';//&projectid=5
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $this->m_urls_arr = $urls_arr;
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
            
            $naturalsort_imgurl = file_create_url(path_to_theme().'/images/icon_sorting.png');
            $imgurl_ar['Q1'] = file_create_url(path_to_theme().'/images/icon_q1.png');
            $imgurl_ar['Q2'] = file_create_url(path_to_theme().'/images/icon_q2.png');
            $imgurl_ar['Q3'] = file_create_url(path_to_theme().'/images/icon_q3.png');
            $imgurl_ar['Q4'] = file_create_url(path_to_theme().'/images/icon_q4.png');
            $imgurl_ar['Q1_dim'] = file_create_url(path_to_theme().'/images/icon_q1dim.png');
            $imgurl_ar['Q2_dim'] = file_create_url(path_to_theme().'/images/icon_q2dim.png');
            $imgurl_ar['Q3_dim'] = file_create_url(path_to_theme().'/images/icon_q3dim.png');
            $imgurl_ar['Q4_dim'] = file_create_url(path_to_theme().'/images/icon_q4dim.png');
            
            $all_proles = $this->m_oMapHelper->getRolesByID(NULL,array('role_nm'));
            $dashdata = $this->m_oDashHelp->getPersonalDashboardDataBundle($this->m_personid);
            $persondetail = $dashdata['personinfo'];

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

            $actionitem_urls_arr = array();
            $actionitem_urls_arr['goals']['actionreq_lc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['goals']['actionreq_mc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['goals']['actionreq_hc'] = 'bigfathom/workitem/mng_comments';

            $actionitem_urls_arr['goals']['Q1'] = 'bigfathom/projects/goals';
            $actionitem_urls_arr['goals']['Q2'] = 'bigfathom/projects/goals';
            
            $actionitem_urls_arr['tasks']['actionreq_lc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['tasks']['actionreq_mc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['tasks']['actionreq_hc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['sprints']['actionreq_lc'] = 'bigfathom/sprint/mng_comments';
            $actionitem_urls_arr['sprints']['actionreq_mc'] = 'bigfathom/sprint/mng_comments';
            $actionitem_urls_arr['sprints']['actionreq_hc'] = 'bigfathom/sprint/mng_comments';
            
            $actionitem_urls_arr['projects']['actionreq_lc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['projects']['actionreq_mc'] = 'bigfathom/workitem/mng_comments';
            $actionitem_urls_arr['projects']['actionreq_hc'] = 'bigfathom/workitem/mng_comments';
            
            $coreview_urls_arr = array();
            $coreview_urls_arr['goals'] = 'urltodoforgoal';
            $coreview_urls_arr['tasks'] = 'todotask';
            $coreview_urls_arr['sprints'] = 'todosprint';
            
            if(!isset($dashdata['actionitems']['headinginfo']['meaning']))
            {
                $col_meaning = [];
            } else {
                $col_meaning = $dashdata['actionitems']['headinginfo']['meaning'];
            }
            
            $maintable_markup = '';
            if($this->m_currentappuser_personid == $this->m_personid)
            {
                $person_markup = "your";
                $short_person_pronoun = "Your";
                $maintable_markup .= "\n<h2>Active Projects Directly Relevant To You</h2>";
            } else {
                $fullname = $persondetail['first_nm'] . " " . $persondetail['last_nm'];
                $person_markup = "<span title='#{$this->m_personid}'>$fullname</span>";
                $short_person_pronoun = "{$persondetail['first_nm']}&apos;s";
                $maintable_markup .= "\n<h2>Active Projects Directly Relevant To $person_markup</h2>";
            }
            $table_row_cols = array();
            $maintable_row_ar = array();
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $project_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            if(!isset($dashdata['connected_projects']))
            {
                $connected_projects = [];
            } else {
                $connected_projects = $dashdata['connected_projects'];
            }
            if($this->m_personrolebundle['summary']['is_systemadmin'] && count($connected_projects) == 0)
            {
                //Show some admin relevant content
                $intro = "<p>You are logged in as a system administrator and are participating in no projects.</p>";
                $form["data_entry_area1"]['table_container']['blurb'] = array('#type' => 'item',
                         '#markup' => $intro);
                
                $subform = [];
                $timdp = new \bigfathom\TopInfoManageDetailsPage();
                $subform = $timdp->getFormBodyContent($subform,NULL,NULL);
                $form["data_entry_area1"]['subform'] = $subform;
                
            } else {
                //Show all the project summary information
                foreach($connected_projects as $oneprojectdetail)
                {
                    $oneproject = $oneprojectdetail['summary'];
                    $workitemid = $oneproject["root_goalid"];
                    $projectid = $oneproject['id'];
                    $projectid_link = l($projectid, $this->m_urls_arr['project_dashboard'], array('query' => array('projectid' => $projectid)));
                    $projectid_markup = $projectid_link;

                    $name = $oneproject['root_workitem_nm'];
                    $name_markup = "<span title='#$projectid'>$name</span>";
                    $all_role_names = $oneproject['all_role_names'];

                    $status_cd = $oneproject['status_cd'];
                    $status_title_tx = $oneproject['status_title_tx'];
                    $status_workstarted_yn = $oneproject['status_workstarted_yn'];
                    $inherited_status_factors = $oneproject['inherited_status_factors'];

                    $implied_status_cd = $inherited_status_factors['implied_status_cd'];
                    $implied_workstarted_yn = $inherited_status_factors['workstarted_yn'];
                    $inherited_status_factors_markup = print_r($inherited_status_factors,TRUE);

                    if($status_workstarted_yn == $implied_workstarted_yn)
                    {
                        $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                    } else {
                        if(!empty($inherited_status_factors['warning_tx']))
                        {
                            $warnstatus_tx = '; '.$inherited_status_factors['warning_tx'];
                            $status_classname = 'warn-text';
                        } else {
                            $warnstatus_tx = '';
                            $status_classname = 'normal-text';
                        }
                        $status_markup = "<span class='{$status_classname}' title='Antecedent status implies project work has started{$warnstatus_tx}'>$implied_status_cd</span>";
                    }

                    $composite_success_forecast_p = UtilityGeneralFormulas::getRoundSigDigs($oneproject['composite_success_forecast_p']);
                    $composite_success_forecast_sample_size = $oneproject['composite_success_forecast_sample_size'];
                    $otsp_markup = "<span title='sample size $composite_success_forecast_sample_size'>$composite_success_forecast_p</span>";

                    $total_goal_count = $oneproject['total_goal_count'];
                    $your_goal_count = $oneproject['your_goal_count'];
                    $total_task_count = $oneproject['total_task_count'];
                    $your_task_count = $oneproject['your_task_count'];

                    $any_recent_goal_activity_date = $oneproject['any_recent_goal_activity_date'];
                    $your_recent_goal_activity_date = $oneproject['your_recent_goal_activity_date'];

                    $any_recent_task_activity_date = $oneproject['any_recent_task_activity_date'];
                    $your_recent_task_activity_date = $oneproject['your_recent_task_activity_date'];
                    $any_recent_activity_date = $oneproject['any_recent_activity_date'];
                    $your_recent_activity_date = $oneproject['your_recent_activity_date'];
                    $start_date_markup = $oneproject['start_dt'];
                    if(empty($oneproject['end_dt']) || empty($oneproject['duration_days']))
                    {
                        $end_date_markup = $oneproject['end_dt'];
                    } else {
                        $duration_days = $oneproject['duration_days'];
                        $end_date_markup = "<span title='$duration_days days duration'>" . $oneproject['end_dt'] . "</span>";
                    }
                    $age_days = $oneproject['age_days'];
                    if($age_days <= 0)
                    {
                        $age_markup = '';
                    } else {
                        $age_markup = $oneproject['age_days'];
                    }

                    if($total_goal_count == 0)
                    {
                        $total_goal_count_markup = 0;
                    } else {
                        $total_goal_count_markup = "[SORTNUM:$total_goal_count]<span title='Updated $any_recent_goal_activity_date'>$total_goal_count</span>";
                    }
                    if($your_goal_count == 0)
                    {
                        $your_goal_count_markup = 0;
                    } else {
                        $your_goal_count_markup = "[SORTNUM:$total_goal_count]<span title='Updated $your_recent_goal_activity_date'>$your_goal_count</span>";
                    }
                    if($total_task_count == 0)
                    {
                        $total_task_count_markup = 0;
                    } else {
                        $total_task_count_markup = "[SORTNUM:$total_task_count]<span title='Updated $any_recent_task_activity_date'>$total_task_count</span>";
                    }
                    if($your_task_count == 0)
                    {
                        $your_task_count_markup = 0;
                    } else {
                        $your_task_count_markup = "[SORTNUM:$total_task_count]<span title='Updated $your_recent_task_activity_date'>$your_task_count</span>";
                    }

                    //Action options
                    $communicate_page_url = url($this->m_urls_arr['project_communicate'], array('query'=>array('projectid'=>$projectid)));
                    $sCommunicationMarkup = "<a title='jump to communications for #{$projectid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy'], array('query'=>array('projectid'=>$projectid)));
                    $sHierarchyMarkup = "<a title='view dependencies for project#{$projectid}' href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";

                    $project_page_url = url($this->m_urls_arr['project_dashboard'], array('query'=>array('projectid'=>$projectid)));
                    $sProjectOverviewMarkup = "<a title='view overview for project#{$projectid}' href='$project_page_url'><img src='$project_icon_url'/></a>";

                    $maintable_row_ar[] = "<td>$projectid_markup</td>"
                            . "<td>$workitemid</td>"
                            . "<td>$name_markup</td>"
                            . "<td>$all_role_names</td>"
                            . "<td>$status_markup</td>"
                            . "<td>$otsp_markup</td>"
                            . "<td>$total_goal_count_markup</td>"
                            . "<td>$your_goal_count_markup</td>"
                            . "<td>$total_task_count_markup</td>"
                            . "<td>$your_task_count_markup</td>"
                            . "<td>$any_recent_activity_date</td>"
                            . "<td>$your_recent_activity_date</td>"
                            . "<td>$start_date_markup</td>"
                            . "<td>$end_date_markup</td>"
                            . "<td>$age_markup</td>"
                            . "<td class='action-options'>$sCommunicationMarkup $sProjectOverviewMarkup $sHierarchyMarkup</td>";
                }

                $maintable_markup .= "\n".'<table id="table-of-open-projects" class="browserGrid">';
                $maintable_markup .= "\n".'<thead>'
                        . "\n<tr>"
                        . "<th class='nowrap'><span title='The unique ID number of the project (click to see project dashboard)'>ID</span></th>"
                        . "<th class='nowrap'><span title='The unique workitem ID of the goal at the root of this project'>W#</span></th>"
                        . "<th class='nowrap'><span title='The name of this project'>Name</span></th>"
                        . "<th class='nowrap'><span title='$short_person_pronoun role(s) in the project'>$short_person_pronoun Role(s)</span></th>"
                        . "<th class='nowrap'><span title='Current status of the project'>Status</span></th>"
                        . "<th class='nowrap'><span title='On-Time Success Probability [0,1]'>OTSP</span></th>"
                        . "<th datatype='formula'><span title='Count of all the OPEN goals in the project'>Total Goals</span></th>"
                        . "<th datatype='formula'><span title='Count of all $short_person_pronoun OPEN goals in the project'>$short_person_pronoun Goals</span></th>"
                        . "<th datatype='formula'><span title='Count of all the OPEN tasks in the project'>Total Tasks</span></th>"
                        . "<th datatype='formula'><span title='Count of $short_person_pronoun OPEN tasks in the project'>$short_person_pronoun Tasks</span></th>"
                        . "<th><span title='Date and time of most recent activity in the project'>Any Activity</span></th>"
                        . "<th><span title='Date and time of most recent activity on $short_person_pronoun owned items of the project'>$short_person_pronoun Activity</span></th>"
                        . "<th><span title='Start date of the project'>Start Date</span></th>"
                        . "<th><span title='End date of the project'>End Date</span></th>"
                        . "<th class='nowrap' datatype='integer'><span title='The age in days of the project'>Age</span></th>"
                        . "<th class='action-options'><span title='Jump to other interfaces'>Action Options</span></th>"
                        . "\n</tr>"
                        . "\n</thead>";
                if(count($maintable_row_ar)==0)
                {
                    $maintable_markup .= "<tbody></tbody>";
                } else {
                    $maintable_markup .= "<tbody><tr>".implode("</tr><tr>", $maintable_row_ar)."</tr></tbody>";
                }
                $maintable_markup .= "</table>";
                $maintable_markup .= "<br>";

                if(isset($dashdata['actionitems']))
                {
                    if(!isset($dashdata['actionitems']['headinginfo']['format']))
                    {
                        $heading_format = [];
                    } else {
                        $heading_format = $dashdata['actionitems']['headinginfo']['format'];
                    }
                    if(!isset($dashdata['actionitems']['headinginfo']['normal']))
                    {
                        $heading_normal = [];
                    } else {
                        $heading_normal = $dashdata['actionitems']['headinginfo']['normal'];
                    }

                    //Create all the role item count tables now too
                    $roleid = 1;    //Start with owner role
                    $roledetail = $all_proles[$roleid];
                    if(array_key_exists($roleid, $dashdata['actionitems']['byroles']))
                    {
                        //Show table for the owner
                        $roleactiondetail = $dashdata['actionitems']['byroles'][$roleid];
                        $maintable_markup .= $this->getRoleBasedTableMarkup($person_markup, $roledetail
                                , $roleactiondetail
                                , $heading_format, $heading_normal, $imgurl_ar
                                , $col_meaning
                                , $actionitem_urls_arr);
                    } else {
                        //$maintable_markup .= "<h3>NOTHING FOR roleid=$roleid rn={$roledetail['role_nm']}</h3>";
                    }
                    foreach($all_proles as $roleid=>$roledetail)
                    {
                        if($roleid != 1)
                        {
                            if(array_key_exists($roleid, $dashdata['actionitems']['byroles']))
                            {
                                //Show table for this one
                                $roleactiondetail = $dashdata['actionitems']['byroles'][$roleid];
                                $maintable_markup .= $this->getRoleBasedTableMarkup($person_markup
                                        , $roledetail
                                        , $roleactiondetail
                                        , $heading_format, $heading_normal, $imgurl_ar
                                        , $col_meaning
                                        , $actionitem_urls_arr);
                            } else {
                                //$maintable_markup .= "<h3>NOTHING FOR roleid=$roleid rn={$roledetail['role_nm']}</h3>";
                            }
                        }
                    }
                }

                $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                         '#markup' => $maintable_markup);
                
            }

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getActionTableHeadingMarkup($format, $normal, $imgurl_ar, $dimcols_ar = array())
    {
        try
        {
            $maintable_headingrow_markup = '';
            foreach($format as $one_headingformat_row)
            {
                $tagname = $one_headingformat_row['tagname'];
                $tag_attribs = '';
                if(array_key_exists('span',$one_headingformat_row))
                {
                    $tag_attribs .= " span='{$one_headingformat_row['span']}'";
                }                
                $classnames = [];
                if(array_key_exists('classhint',$one_headingformat_row))
                {
                    $classnames[] = "align-{$one_headingformat_row['classhint']}";
                }
                if(array_key_exists('classname',$one_headingformat_row))
                {
                    $classnames[] = $one_headingformat_row['classname'];
                }
                if(count($classnames) > 0)
                {
                    $classnamestr = implode(" ", $classnames);
                    $tag_attribs .= " class='{$classnamestr}'";
                }
                $maintable_headingrow_markup .= "\n<{$tagname}{$tag_attribs}></$tagname>";
            }            
            $maintable_headingrow_markup .= "\n<thead>";
            foreach($normal as $one_heading_row)
            {
                $maintable_row_cols = array();
                foreach($one_heading_row as $items)
                {
                    $th_attribs = '';
                    if(array_key_exists('colspan',$items))
                    {
                        $th_attribs .= " colspan='{$items['colspan']}'";
                    }
                    if(array_key_exists('rowspan',$items))
                    {
                        $th_attribs .= " rowspan='{$items['rowspan']}'";
                    }
                    if(array_key_exists('scope',$items))
                    {
                        $th_attribs .= " scope='{$items['scope']}'";
                    }
                    $classnames = [];
                    if(array_key_exists('classhint',$items))
                    {
                        $classnames[] = "align-{$items['classhint']}";
                    }
                    if(array_key_exists('classname',$items))
                    {
                         $classnames[] = $items['classname'];
                    }
                    if(count($classnames) > 0)
                    {
                        $classnamestr = implode(" ", $classnames);
                        $th_attribs .= " class='{$classnamestr}'";
                    }
                    $heading_value = $items['heading'];
                    if(isset($imgurl_ar[$heading_value]))
                    {
                        if(isset($dimcols_ar[$heading_value]) && isset($imgurl_ar[$heading_value.'_dim']))
                        {
                            $heading_value_markup = "<img alt='attached files count' src='{$imgurl_ar[$heading_value.'_dim']}' />$heading_value";
                        } else {
                            $heading_value_markup = "<img alt='attached files count' src='{$imgurl_ar[$heading_value]}' />$heading_value";
                        }
                    } else {
                        $heading_value_markup = $heading_value;
                    }
                    if(!array_key_exists('blurb',$items))
                    {
                        $displayvalue = $items['heading'];
                    } else {
                        $displayvalue = "<span title='{$items['blurb']}'>{$heading_value_markup}</span>";
                    }
                    $maintable_row_cols[] = "<th{$th_attribs}>$displayvalue</th>";
                }
                $maintable_headingrow_markup .= "\n\t<tr>".implode("\n\t",$maintable_row_cols).'</tr>';
            }
            $maintable_headingrow_markup .= "\n</thead>";
            return $maintable_headingrow_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getRoleBasedTableMarkup($person_markup, $roledetail, $roleactiondetail
            , $heading_format, $heading_normal, $imgurl_ar
            , $col_meaning
            , $actionitem_urls_arr)
    {
        try
        {
            $roleid = $roledetail['id'];
            $tableid = "table4roleid" . $roleid;
            $q1total = 0;
            $q2total = 0;
            $q3total = 0;
            $q4total = 0;
            $roletable_markup = "\n<h2>Highlights for Items where $person_markup role is {$roledetail['role_nm']}</h2>";
            $roletable_row_ar = array();
            foreach($roleactiondetail['data'] as $detail)
            {
                $maintable_row_cols = array();
                $colnum=0;
                foreach($detail as $items)
                {
                    $meaning = $col_meaning[$colnum];
                    $value = $items['value'];
                    if($colnum===0)
                    {
                        $contextname = $value;
                    }
                    if(!array_key_exists('classhint',$items))
                    {
                        $colmarkup = $value;
                    } else {
                        $colmarkup = "<span class='{$items['classhint']}'>{$value}</span>";
                    }
                    if($colnum > 0 && $value != 0)
                    {
                        if(array_key_exists($meaning, $actionitem_urls_arr[$contextname]))
                        {
                            $url = $actionitem_urls_arr[$contextname][$meaning];
                            $colmarkup = l($colmarkup, $url, array('query' => array('todo' => $colnum)));
                        }
                    }
                    $maintable_row_cols[] = $colmarkup;
                    if($colnum == 4)
                    {
                        $q1total+=$value;
                    }
                    if($colnum == 5)
                    {
                        $q2total+=$value;
                    }
                    if($colnum == 6)
                    {
                        $q3total+=$value;
                    }
                    if($colnum == 7)
                    {
                        $q4total+=$value;
                    }
                    $colnum++;
                }
                $roletable_row_ar[] = '<td>'.implode('</td><td>',$maintable_row_cols).'</td>';
            }
            $dim_ar = array();
            if($q1total < 1)
            {
                $dim_ar['Q1'] = 'dim';   
            }
            if($q2total < 1)
            {
                $dim_ar['Q2'] = 'dim';   
            }
            if($q3total < 1)
            {
                $dim_ar['Q3'] = 'dim';   
            }
            if($q4total < 1)
            {
                $dim_ar['Q4'] = 'dim';   
            }
            $roletable_headingrow_markup = $this->getActionTableHeadingMarkup($heading_format, $heading_normal, $imgurl_ar, $dim_ar);
            $roletable_markup .= "\n<table id='{$tableid}' class='browserGrid'>";
            $roletable_markup .= $roletable_headingrow_markup;
            $roletable_markup .= "\n<tr>".implode("</tr>\n<tr>", $roletable_row_ar)."</tr>";
            $roletable_markup .= "\n</table>";
            $roletable_markup .= "\n<br>";
            return $roletable_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}
