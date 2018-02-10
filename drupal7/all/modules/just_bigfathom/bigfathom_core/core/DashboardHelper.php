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

require_once 'DatabaseNamesHelper.php';
require_once 'MapHelper.php';
//require_once 'ProjectForecaster.php';

/**
 * This class provides data for specialized dashboards
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class DashboardHelper 
{

    protected $m_oMapHelper = NULL;
    protected $m_oProjectForecaster = NULL;
    protected $m_oContext = NULL;
    protected $MAX_INCLAUSE_CHUNK_SIZE = 100;
    
    public function __construct()
    {
        $loaded_pf = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
        if(!$loaded_pf)
        {
            throw new \Exception('Failed to load the core ProjectForecaster class');
        }
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    private function getAllNonTerminalWorkitemConnected2Person($personid, $status_filter_ar=array())
    {
        try
        {
            if(!array_key_exists('terminal_yn', $status_filter_ar))
            {
                $status_filter_ar['terminal_yn'] = array('=',0);
            }
            return $this->getAllWorkitemsConnected2Person($personid, $status_filter_ar);
        } catch (\Exception $ex) {
            throw $ex;
        }
 
    }

    private function getAllNonTerminalSprintsConnected2Person($personid, $status_filter_ar=NULL, $relevant_goalids=NULL)
    {
        try
        {
            if($status_filter_ar == NULL)
            {
                $status_filter_ar=array();
            }
            if(!array_key_exists('terminal_yn', $status_filter_ar))
            {
                $status_filter_ar['terminal_yn'] = array('=',0);
            }
            return $this->getAllSprintsConnected2Person($personid, $status_filter_ar, $relevant_goalids);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getAllNonTerminalProjectsConnected2Person($personid, $status_filter_ar=NULL, $relevant_goalids=NULL)
    {
        try
        {
            if($status_filter_ar == NULL)
            {
                $status_filter_ar=array();
            }
            if(!array_key_exists('terminal_yn', $status_filter_ar))
            {
                $status_filter_ar['terminal_yn'] = array('=',0);
            }
            return $this->getAllProjectsConnected2Person($personid, $status_filter_ar, $relevant_goalids);
        } catch (\Exception $ex) {
            throw $ex;
        }
     }
    
    /**
     * Return connected workitems and sort into quadrants
     */
    private function getAllWorkitemsConnected2Person($personid, $status_filter_ar=NULL, $basetypefilter=NULL)
    {
        try
        {
            $now_dt = date("Y-m-d H:i", time());
            $status_filter_txt = '';
            if(is_array($status_filter_ar))
            {
                foreach($status_filter_ar as $fieldname=>$criteria)
                {
                    $op = $criteria[0];
                    $literal = $criteria[1];
                    $status_filter_txt .= " and s.{$fieldname}{$op}{$literal}";
                }
            }
            $themap = array();
            $sSQL_P1 = "SELECT 'owner' as role_nm, 0 as roleid, g.* "
                . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " g "
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename . " s on s.code=g.status_cd"
                . " WHERE g.owner_personid=$personid and g.active_yn=1 $status_filter_txt";
            $sSQL_P2 = "SELECT r.role_nm as role_nm, rig.roleid as roleid, g.* "
                . " FROM " . DatabaseNamesHelper::$m_map_person2role_in_workitem_tablename . " rig"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " g on rig.workitemid=g.id"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_role_tablename . " r on r.id=rig.roleid"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename . " s on s.code=g.status_cd"
                . " WHERE rig.personid=$personid and g.active_yn=1 and r.active_yn=1 $status_filter_txt";
            if($basetypefilter !== NULL)
            {
                $sSQL_P2 .= " and g.workitem_basetype='$basetypefilter'";
            }
            $sSQL = "$sSQL_P1 UNION $sSQL_P2";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $wid = $record['id'];
                if(!empty($record['actual_start_dt']))
                {
                    $start_dt = $record['actual_start_dt'];
                    $record['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $now_dt);
                } else if(!empty($record['planned_start_dt'])) {
                    $start_dt = $record['planned_start_dt'];
                    $record['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $now_dt);
                } else {
                    $start_dt = NULL;
                }
                if(!empty($record['actual_end_dt']))
                {
                    $end_dt = $record['actual_end_dt'];
                    $days_until_deadline =\bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($now_dt, $end_dt);
                    $record['remaining_days'] = $days_until_deadline;
                } else if(!empty($record['planned_end_dt'])) {
                    $end_dt = $record['planned_end_dt'];
                    $days_until_deadline = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($now_dt, $end_dt);
                    $record['remaining_days'] = $days_until_deadline;
                } else {
                    $end_dt = NULL;
                    $days_until_deadline = NULL;
                }
                if(!empty($end_dt) && !empty($start_dt))
                {
                    $record['duration_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
                }
                $importance_value = $record['importance'];
                $record['derived_quadrant'] = \bigfathom\UtilityGeneralFormulas::getTimeManagementMatrixQuadrantFromRawValues($importance_value, $days_until_deadline);
                $themap[$wid] = $record; 
            }            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return connected goal comments
     */
    private function getAllGoalCommentsConnected2Person($personid, $status_filter_ar=NULL)
    {
        try
        {
            $status_filter_txt = '';
            if(is_array($status_filter_ar))
            {
                foreach($status_filter_ar as $fieldname=>$criteria)
                {
                    $op = $criteria[0];
                    $literal = $criteria[1];
                    $status_filter_txt .= " and s.{$fieldname}{$op}{$literal}";
                }
            }
            $themap = array();
            $sSQL_P1 = "SELECT 'owner' as role_nm, 0 as roleid, g.* "
                . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " g "
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename . " s on s.code=g.status_cd"
                . " WHERE g.owner_personid=$personid and g.workitem_basetype='G' and g.active_yn=1 $status_filter_txt";
            $sSQL_P2 = "SELECT r.role_nm as role_nm, rig.roleid as roleid, g.* "
                . " FROM " . DatabaseNamesHelper::$m_map_person2role_in_workitem_tablename . " rig"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " g on rig.workitemid=g.id"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_role_tablename . " r on r.id=rig.roleid"
                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename . " s on s.code=g.status_cd"
                . " WHERE rig.personid=$personid and g.workitem_basetype='G' and g.active_yn=1 and r.active_yn=1 $status_filter_txt";
            $sSQL = "$sSQL_P1 UNION $sSQL_P2";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id] = $record; 
            }            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return connected sprints
     */
    private function getAllSprintsConnected2Person($personid, $status_filter_ar=NULL, $relevant_goalids=NULL)
    {
        try
        {
            $now_dt = date("Y-m-d H:i", time());
            $status_filter_txt = '';
            if(is_array($status_filter_ar))
            {
                foreach($status_filter_ar as $fieldname=>$criteria)
                {
                    $op = $criteria[0];
                    $literal = $criteria[1];
                    $status_filter_txt .= " and s.{$fieldname}{$op}{$literal}";
                }
            }
            $themap = array();
            $sSQL_OWN = "SELECT 'owner' as role_nm, 0 as roleid, t.* "
                . " FROM " . DatabaseNamesHelper::$m_sprint_tablename . " t "
                . " LEFT JOIN " . DatabaseNamesHelper::$m_sprint_status_tablename . " s on s.code=t.status_cd"
                . " WHERE t.owner_personid=$personid and t.active_yn=1 $status_filter_txt";
            $result_own = db_query($sSQL_OWN);
            while($record = $result_own->fetchAssoc()) 
            {
                $id = $record['id'];
                if(!empty($record['start_dt']))
                {
                    $start_dt = $record['start_dt'];
                    $record['age_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $now_dt);
                } else {
                    $start_dt = NULL;
                }
                if(!empty($record['end_dt']))
                {
                    $end_dt = $record['end_dt'];
                    $days_until_deadline =\bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($now_dt, $end_dt);
                    $record['remaining_days'] = $days_until_deadline;
                } else {
                    $end_dt = NULL;
                }
                if(!empty($end_dt) && !empty($start_dt))
                {
                    $record['duration_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
                }
                $importance_value = 70;
                $record['derived_quadrant'] = \bigfathom\UtilityGeneralFormulas::getTimeManagementMatrixQuadrantFromRawValues($importance_value, $days_until_deadline);
                $themap[$id] = $record; 
            }     
            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @Deprecated
     */
    private function getCoreThingStatsByProject($thingname, $personid=NULL)
    {
        try
        {
            //Yes allow GOAL and TASK in addition to WORKITEM!!!!
            $where1_ar = [];
            $where2_ar = [];
            if(!empty($personid))
            {
                $where1_ar[] ="g.owner_personid=$personid";
                $where2_ar[] ="gm.personid=$personid and roleid<>1";
            }
            if($thingname == 'goal')
            {
                $namefordb = 'workitem';
                $where1_ar[] ="g.workitem_basetype='G'";
                $where2_ar[] ="g.workitem_basetype='G'";
            } else
            if($thingname == 'task')
            {
                $namefordb = 'workitem';
                $where1_ar[] ="g.workitem_basetype='T'";
                $where2_ar[] ="g.workitem_basetype='T'";
            } else {
                $namefordb = $thingname;
            }
            $themap = array();
            if(count($where1_ar)>0)
            {
                $sWHERE1=' WHERE ' . implode(" and ", $where1_ar);
            } else {
                $sWHERE1 = '';
            }
            if(count($where2_ar)>0)
            {
                $sWHERE2=' WHERE ' . implode(" and ", $where2_ar);
            } else {
                $sWHERE2 = '';
            }
            $sSQL = "
                SELECT g.owner_projectid as projectid, 
                        1 as roleid,
                        'Owner' as role_nm, 
                        max(g.updated_dt) as most_recent_edit,
                        count(g.id) as your_count
                FROM bigfathom_workitem g 
                $sWHERE1
                GROUP BY g.owner_projectid, roleid, role_nm

                UNION

                SELECT g.owner_projectid as projectid, 
                        gm.roleid, 
                        r.role_nm, 
                        max(g.updated_dt) as most_recent_edit,
                        count(g.id) as your_count 
                FROM bigfathom_map_person2role_in_{$namefordb} gm
                LEFT JOIN bigfathom_workitem g ON g.id=gm.{$namefordb}id
                LEFT JOIN bigfathom_role r ON r.id=gm.roleid
                $sWHERE2
                GROUP BY g.owner_projectid, gm.roleid, r.role_nm
                ";
            $result_roles = db_query($sSQL);
            while($record = $result_roles->fetchAssoc()) 
            {
                $projectid = $record['projectid'];
                $roleid = $record['roleid'];
                $role_nm = $record['role_nm'];
                $your_count = $record['your_count'];
                $most_recent_edit = $record['most_recent_edit'];
                if(!array_key_exists($projectid, $themap))
                {
                    $themap[$projectid] = array('roles'=>array());  
                }
                $themap[$projectid]['roles'][$roleid] 
                        = array('name'=>$role_nm, 
                            'count'=>$your_count, 
                            'most_recent_edit'=>$most_recent_edit);
            }
            foreach($themap as $projectid=>$roleset)
            {
                $total_most_recent_edit = NULL;
                $all_rolename_ar = array();
                $all_counts = 0;
                foreach($roleset['roles'] as $roleid=>$detail)
                {
                    $all_counts += $detail['count'];
                    $all_rolename_ar[$roleid] = $detail['name'];
                    if($total_most_recent_edit < $most_recent_edit)
                    {
                        $total_most_recent_edit = $most_recent_edit;  
                    }
                }
                if(count($all_rolename_ar) == 1 || !isset($all_rolename_ar[1]))
                {
                    $all_roles_tx = implode(', ', $all_rolename_ar);
                } else {
                    unset($all_rolename_ar[1]);
                    $all_roles_tx = 'Owner, '.implode(', ', $all_rolename_ar);
                }
                $themap[$projectid]['summary']['count'] = $all_counts;
                $themap[$projectid]['summary']['all_role_names'] = $all_roles_tx;
                $themap[$projectid]['summary']['most_recent_edit'] = $total_most_recent_edit;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return connected projects
     * REDUNDANT WITH maphelper->getDashboardMultiProjectBundle??????????????
     * @deprecated
     */
    private function getAllProjectsConnected2Person($personid, $status_filter_ar=NULL, $relevant_goalids=NULL)
    {
        try
        {
            //TODO refactor to use getDashboardMultiProjectBundle!!!!!!
            drupal_set_message("LOOK TODO refactor to use getDashboardMultiProjectBundle!!!!");
            return [];
            
            $now_dt = date("Y-m-d H:i", time());
            $themap = array();
            $things_ar = array('workitem'); //,'sprint','project'); //TODO ,'task','sprint');
            foreach($things_ar as $thingname)
            {
                $person_thing_stats_by_project = $this->getCoreThingStatsByProject($thingname,$personid);
                $thing_stats_by_project = $this->getCoreThingStatsByProject($thingname);
                $all_projects_by_id = $this->m_oMapHelper->getProjectsByID();
                foreach($person_thing_stats_by_project as $projectid=>$detail)
                {
                    $projectdetail = $all_projects_by_id[$projectid];
                    
                    if(!isset($themap[$projectid]['summary']))
                    {
                        //Initialize
                        $themap[$projectid]['summary']['total_goal_count'] = 0; 
                        $themap[$projectid]['summary']['total_task_count'] = 0; 
                        $themap[$projectid]['summary']['total_sprint_count'] = 0; 
                        $themap[$projectid]['summary']['your_sprint_count'] = 0; 
                        $themap[$projectid]['summary']['any_recent_activity_date'] = NULL; 
                        $themap[$projectid]['summary']['your_recent_activity_date'] = NULL; 
                        $themap[$projectid]['summary']['any_recent_goal_activity_date'] = NULL;
                        $themap[$projectid]['summary']['your_recent_goal_activity_date'] = NULL;
                        $themap[$projectid]['summary']['any_recent_task_activity_date'] = NULL;
                        $themap[$projectid]['summary']['your_recent_task_activity_date'] = NULL;
                        $themap[$projectid]['summary']['any_recent_sprint_activity_date'] = NULL;
                        $themap[$projectid]['summary']['your_recent_sprint_activity_date'] = NULL;
                    }

                    $open_workitems = $this->m_oMapHelper->getIDMapOfWorkitemsInProject($projectid, $personid, 0);
                    $workitemid_maps4personInProject = $open_workitems['widsbytype'];
                    
                    $open_workitems = $this->m_oMapHelper->getIDMapOfWorkitemsInProject($projectid, NULL, 0);
                    $workitemid_maps4anybodyInProject = $open_workitems['widsbytype'];
                    
                    $everyone_detail = $thing_stats_by_project[$projectid];
                    $themap[$projectid]['summary']['id'] = $projectid; 
                    $root_goalid = $projectdetail['root_goalid'];
                    $themap[$projectid]['summary']['root_goalid'] = $root_goalid; 
                    
                    $importantbundle = $this->m_oProjectForecaster->getBranchInfoSummaryFromRootThing('goal', $root_goalid, 0, array($personid), TRUE);
                    $compositedetail = $importantbundle['compositedetail'];
                    if(!isset($compositedetail["connected_people"]["by_personid"][$personid]))
                    {
                        $themap[$projectid]['summary']["your_roles"] = array();   
                    } else {
                        $themap[$projectid]['summary']["your_roles"] = $compositedetail["connected_people"]["by_personid"][$personid]["roles"];
                    }
                    $all_role_names = array();
                    foreach($themap[$projectid]['summary']["your_roles"] as $roleid=>$detail)
                    {
                        if(!isset($detail['role_nm']))
                        {
                            drupal_set_message("ERROR missing role_nm look " . print_r($detail, TRUE));
                        }
                        $all_role_names[] = $detail['role_nm'];
                    }
                    $themap[$projectid]['summary']['all_role_names'] = implode(", ", $all_role_names);
                    $themap[$projectid]['summary']['your_goal_count'] = isset($workitemid_maps4personInProject['G']) ? count($workitemid_maps4personInProject['G']) : 0;
                    $themap[$projectid]['summary']['your_task_count'] = isset($workitemid_maps4personInProject['T']) ? count($workitemid_maps4personInProject['T']) : 0;
                    $themap[$projectid]['summary']['total_goal_count'] = isset($workitemid_maps4anybodyInProject['G']) ? count($workitemid_maps4anybodyInProject['G']) : 0;
                    $themap[$projectid]['summary']['total_task_count'] = isset($workitemid_maps4anybodyInProject['T']) ? count($workitemid_maps4anybodyInProject['T']) : 0;
                    
                    $themap[$projectid]['summary']['any_recent_activity_date'] = $compositedetail["most_recent_activity_dt"];;

                    if(!isset($compositedetail["connected_people"]["by_personid"][$personid]["summary"]["most_recent_activity_dt"]))
                    {
                        $person_most_recent_activity_dt 
                                = '';
                    } else {
                        $person_most_recent_activity_dt 
                                = $compositedetail["connected_people"]["by_personid"][$personid]["summary"]["most_recent_activity_dt"];
                    }
        
                    if(!isset($everyone_detail['summary']['most_recent_edit']))
                    {
                        $everyone_most_recent_edit = '';
                    } else {
                        $everyone_most_recent_edit = $everyone_detail['summary']['most_recent_edit'];
                    }
                    
                    $themap[$projectid]['person_in_project'][$thingname] = $detail;
                    $themap[$projectid]['everyone_in_project'][$thingname] = $thing_stats_by_project[$projectid];
                    $themap[$projectid]['workitemid_maps4person'] = $workitemid_maps4personInProject;
                    
                    if(!isset($themap[$projectid]))
                    {
                        $themap[$projectid] = array();
                    }
                    $start_dt = empty($projectdetail['actual_start_dt']) ? $projectdetail['planned_start_dt'] : $projectdetail['actual_start_dt'];
                    $end_dt = empty($projectdetail['actual_end_dt']) ? $projectdetail['planned_end_dt'] : $projectdetail['actual_end_dt'];
                    $themap[$projectid]['summary']['root_workitem_nm'] = $projectdetail['root_workitem_nm'];
                    $themap[$projectid]['summary']['root_goalid'] = $root_goalid;
                    
                    
                    $themap[$projectid]['summary']['status_title_tx'] = $projectdetail['status_title_tx'];
                    $themap[$projectid]['summary']['status_workstarted_yn'] = $projectdetail['status_workstarted_yn'];
                    $themap[$projectid]['summary']['status_terminal_yn'] = $projectdetail['status_terminal_yn'];
                    $themap[$projectid]['summary']['status_happy_yn'] = $projectdetail['status_happy_yn'];
                    $themap[$projectid]['summary']['status_cd'] = $projectdetail['status_cd'];
                    
                    $themap[$projectid]['summary']['inherited_status_factors'] = $compositedetail['inherited_status_factors'];
                    
                    $age_days = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $now_dt);
                    $duration_days = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($start_dt, $end_dt);
                    $themap[$projectid]['summary']['start_dt'] = $start_dt;
                    $themap[$projectid]['summary']['end_dt'] = $end_dt;
                    $themap[$projectid]['summary']['age_days'] = $age_days;
                    $themap[$projectid]['summary']['duration_days'] = $duration_days;
                    
                    //$confidence_bundle = $this->m_oForecastHelper->getConfidenceFromThisRootThing('goal', $root_goalid);
                    
                    $themap[$projectid]['summary']['composite_success_forecast_p'] 
                            = $importantbundle['composite_success_forecast_p'];
                    $themap[$projectid]['summary']['composite_success_forecast_sample_size'] 
                            = $importantbundle['composite_success_forecast_sample_size'];
                    
                    $themap[$projectid]['summary']['your_recent_activity_date'] = $person_most_recent_activity_dt;

                    if(!isset($themap[$projectid]['summary']['any_recent_goal_activity_date']))
                    {
                        $themap[$projectid]['summary']['any_recent_goal_activity_date'] = '';
                    }
                    if(!isset($themap[$projectid]['summary']['any_recent_task_activity_date']))
                    {
                        $themap[$projectid]['summary']['any_recent_task_activity_date'] = '';
                    }
                    if(!isset($themap[$projectid]['summary']['your_recent_goal_activity_date']))
                    {
                        $themap[$projectid]['summary']['your_recent_goal_activity_date'] = '';
                    }
                    if(!isset($themap[$projectid]['summary']['your_recent_task_activity_date']))
                    {
                        $themap[$projectid]['summary']['your_recent_task_activity_date'] = '';
                    }

                }
            }
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @deprecated ????????
     */
    public function getPersonalDashboardDataBundle($personid, $only_active=TRUE)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Must provide a personid!");
            }
            
            $bundle = [];
            $persondetail = $this->m_oMapHelper->getOnePersonDetailData($personid);
            $bundle['personinfo'] = $persondetail;
            //TODO refactor to use getDashboardMultiProjectBundle!!!!!!
            drupal_set_message("LOOK TODO refactor to use getDashboardMultiProjectBundle!!!!");
            return $bundle;
            
            $relevant_wids = [];
            $count_project_member_goals = [];
            $count_project_member_tasks = [];
            $list_client_deliverable_goals = [];
            $list_externally_billable_goals = [];
            $list_client_deliverable_tasks = [];
            $list_externally_billable_tasks = [];
                    
            $relevant_goalids = [];
            $relevant_taskids = [];
            $relevant_sprintids = [];
            
            $relevant_goal_status_counts = [];
            $relevant_task_status_counts = [];
            
            $map_goals_by_role = [];
            $map_sprints_by_role = [];
            $map_tasks_by_role = [];
            
            $connected_open_workitems = $this->getAllNonTerminalWorkitemConnected2Person($personid);
            foreach($connected_open_workitems as $wid=>$winfo)
            {
                $relevant_wids[$wid] = $wid;
                $roleid = $winfo['roleid'];
                $pid = $winfo['owner_projectid'];
                $wbasetype = $winfo['workitem_basetype'];
                $status_cd = $winfo['status_cd'];
                $branchinfo = $this->m_oProjectForecaster->getBranchInfoSummaryFromRootThing('workitem', $wid);
                $connected_open_workitems[$wid]['branchinfo'] = $branchinfo;
                if(!isset($map_goals_by_role[$roleid]))
                {
                    $map_goals_by_role[$roleid] = array();
                }
                if($wbasetype == 'G')
                {
                    $map_goals_by_role[$roleid][$wid] = $wid;
                    $relevant_goalids[$wid] = $wid;
                    if(!array_key_exists($pid, $count_project_member_goals))
                    {
                        $count_project_member_goals[$pid] = array('goals'=>1,'deliverables'=>0);
                    } else {
                        $count_project_member_goals[$pid]['goals'] += 1;
                    }
                    if($winfo['externally_billable_yn'] == 1)
                    {
                        $list_externally_billable_goals[] = $wid;
                        $count_project_member_goals[$pid]['billable'] += 1;
                    }
                    if($winfo['client_deliverable_yn'] == 1)
                    {
                        $list_client_deliverable_goals[] = $wid;
                        $count_project_member_goals[$pid]['deliverables'] += 1;
                    }
                    if(!array_key_exists($status_cd, $relevant_goal_status_counts))
                    {
                        $relevant_goal_status_counts[$status_cd] = 1;
                    } else {
                        $relevant_goal_status_counts[$status_cd] += 1;
                    }
                } else
                if($wbasetype == 'T')
                {
                    $map_tasks_by_role[$roleid][$wid] = $wid;
                    $relevant_taskids[$wid] = $wid;
                    if(!array_key_exists($pid, $count_project_member_tasks))
                    {
                        $count_project_member_tasks[$pid] = array('tasks'=>1,'deliverables'=>0);
                    } else {
                        $count_project_member_tasks[$pid]['tasks'] += 1;
                    }
                    if($winfo['externally_billable_yn'] == 1)
                    {
                        $list_externally_billable_tasks[] = $wid;
                        $count_project_member_tasks[$pid]['billable'] += 1;
                    }
                    if($winfo['client_deliverable_yn'] == 1)
                    {
                        $list_client_deliverable_tasks[] = $wid;
                        $count_project_member_tasks[$pid]['deliverables'] += 1;
                    }
                    if(!array_key_exists($status_cd, $relevant_task_status_counts))
                    {
                        $relevant_task_status_counts[$status_cd] = 1;
                    } else {
                        $relevant_task_status_counts[$status_cd] += 1;
                    }
                }
            }
            //$connected_tasks = $this->getAllNonTerminalTasksConnected2Person($personid);
            $connected_sprints = $this->getAllNonTerminalSprintsConnected2Person($personid,NULL,$relevant_wids);
            $projects = $this->getAllNonTerminalProjectsConnected2Person($personid,NULL,$relevant_wids);
            
            foreach($connected_sprints as $sprintid=>$sprintinfo)
            {
                $relevant_sprintids[$sprintid] = $sprintid;
                $roleid = $winfo['roleid'];
                if(!isset($map_sprints_by_role[$roleid]))
                {
                    $map_sprints_by_role[$roleid] = array();
                }
                $map_sprints_by_role[$roleid][$sprintid] = $sprintid;
            }            
            
            $relevant_role_maps = [];
            $relevant_branch_comids = [];
            $relevant_personids = array($personid=>$personid);  //Start with just the one user
            
            $goal_communications = $this->m_oMapHelper->getWorkitemCommunicationSummaryBundle($relevant_goalids, $only_active
                    , $relevant_branch_comids, $relevant_personids);
            $relevant_role_maps['goals'] = $goal_communications['role_maps'];
            
            $task_communications = $this->m_oMapHelper->getWorkitemCommunicationSummaryBundle($relevant_taskids, $only_active
                    , $relevant_branch_comids, $relevant_personids);
            $relevant_role_maps['tasks'] = $task_communications['role_maps'];
            
            $sprint_communications = $this->m_oMapHelper->getSprintCommunicationSummaryBundle($relevant_sprintids, $only_active
                    , $relevant_branch_comids, $relevant_personids);
            $relevant_role_maps['sprints'] = $sprint_communications['role_maps'];
            
            $goal_map_open_request = $goal_communications['map_open_request'];
            $task_map_open_request = $task_communications['map_open_request'];
            $sprint_map_open_request = $sprint_communications['map_open_request'];
            
            $goals_roles_map_ar = $this->getCountsByRoleAndConcern($goal_map_open_request, $map_goals_by_role, $connected_open_workitems);
            $tasks_roles_map_ar = $this->getCountsByRoleAndConcern($task_map_open_request, $map_tasks_by_role, $connected_open_workitems);
            $sprints_roles_map_ar = $this->getCountsByRoleAndConcern($sprint_map_open_request, $map_sprints_by_role, $connected_sprints);
            
            $consolidated_role_list = array();
            foreach($relevant_role_maps as $category=>$roleids)
            {
                foreach($roleids as $roleid=>$mappings)
                {
                    $consolidated_role_list[$roleid] = $roleid;
                }
            }
            $relevant_role_maps['consolidated_role_list'] = $consolidated_role_list;
            
            $bundle['relevant_role_maps'] = $relevant_role_maps;
            $bundle['connected_projects'] = $projects;
            $bundle['count_project_member_goals'] = $count_project_member_goals;
            $bundle['relevant_goal_status_counts'] = $relevant_goal_status_counts;
            $bundle['relevant_task_status_counts'] = $relevant_task_status_counts;
            $bundle['list_client_deliverable_goals'] = $list_client_deliverable_goals;
            $bundle['connected_open_workitems'] = $connected_open_workitems;
            
            $headinginfo_normal = array();
            $headinginfo_meaning = array(
                'context',
                'actionreq_lc',
                'actionreq_mc',
                'actionreq_hc',
                'Q1',
                'Q2',
                'Q3',
                'Q4',
                'composite_success_p',
                'size',
                'STD',
                'success_p_lowest',
                'success_p_highest',
                );
            $headinginfo_format = array(
                    array('tagname'=>'col'),
                    array('tagname'=>'colgroup','span'=>3,'classname'=>'heading-actionrequests'),
                    array('tagname'=>'colgroup','span'=>4,'classname'=>'heading-timemanagement'),
                    array('tagname'=>'colgroup','span'=>3,'classname'=>'heading-forecasting'),
                );
            $headinginfo_normal[] = array(
                    array('heading'=>'','colspan'=>1),
                    array('heading'=>'Open Action Requests','colspan'=>3,'classhint'=>'center','classname'=>'heading-actionrequests'),
                    array('heading'=>'Open Items by Time Management Category','colspan'=>4,'classhint'=>'center','classname'=>'heading-timemanagement'),
                    array('heading'=>'Success Forecasts','colspan'=>5,'classhint'=>'center','classname'=>'heading-forecasting'),
                );
            $headinginfo_normal[] = array(
                    array('heading'=>'Context','scope'=>'col','blurb'=>'Context of the statistics'),
                    array('heading'=>'LC','scope'=>'col','blurb'=>'Open action requests of low concern','classname'=>'heading-actionrequests'),
                    array('heading'=>'MC','scope'=>'col','blurb'=>'Open action requests of medium concern','classname'=>'heading-actionrequests'),
                    array('heading'=>'HC','scope'=>'col','blurb'=>'Open action requests of high concern','classname'=>'heading-actionrequests'),
                    array('heading'=>'Q1','scope'=>'col','blurb'=>'Open items that have slipped into IMPORTANT AND URGENT quadrant','classname'=>'heading-timemanagement'),
                    array('heading'=>'Q2','scope'=>'col','blurb'=>'Open items that are still in the IMPORTANT AND NOT URGENT quadrant','classname'=>'heading-timemanagement'),
                    array('heading'=>'Q3','scope'=>'col','blurb'=>'Open items that are NOT important but are now URGENT','classname'=>'heading-timemanagement'),
                    array('heading'=>'Q4','scope'=>'col','blurb'=>'Open items that are NOT important and also NOT urgent','classname'=>'heading-timemanagement'),
                    array('heading'=>'OTSP','scope'=>'col','blurb'=>'On-Time Success Probability [0,1]','classname'=>'heading-forecasting'),
                    array('heading'=>'Size','scope'=>'col','blurb'=>'Number of values factored into the OTSP','classname'=>'heading-forecasting'),
                    array('heading'=>'STD','scope'=>'col','blurb'=>'Standard deviation of forecast','classname'=>'heading-forecasting'),
                    array('heading'=>'LSF','scope'=>'col','blurb'=>'Lowest success forecast value','classname'=>'heading-forecasting'),
                    array('heading'=>'HSF','scope'=>'col','blurb'=>'Highest success forecast value','classname'=>'heading-forecasting'),
                );
            $bundle['actionitems']['headinginfo']['meaning'] = $headinginfo_meaning;
            $bundle['actionitems']['headinginfo']['format'] = $headinginfo_format;
            $bundle['actionitems']['headinginfo']['normal'] = $headinginfo_normal;
            
            foreach($consolidated_role_list as $roleid)
            {
                //Collect rows for this role
                $roleaction_rows = array();
                if(array_key_exists($roleid, $goals_roles_map_ar))
                {
                    $wids_map = $map_goals_by_role[$roleid];
                    $roleaction_rows[] = $this->getNewWorkitemActionRow($roleid, 'goals', $goals_roles_map_ar, $wids_map, $connected_open_workitems);
                }
                if(array_key_exists($roleid, $tasks_roles_map_ar))
                {
                    $wids_map = $map_tasks_by_role[$roleid];
                    $roleaction_rows[] = $this->getNewWorkitemActionRow($roleid, 'tasks', $tasks_roles_map_ar, $wids_map, $connected_open_workitems);
                }
                if(array_key_exists($roleid, $sprints_roles_map_ar))
                {
                    $roleaction_rows[] = $this->getNewSprintActionRow($roleid, 'sprints', $sprints_roles_map_ar);
                }
                if(count($roleaction_rows) > 0)
                {
                    $bundle['actionitems']['byroles'][$roleid] = array(
                        'data' => $roleaction_rows,
                    );
                }
            }
            if(!isset($bundle['actionitems']['byroles']))
            {
                $bundle['actionitems']['byroles'] = array();
            }
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getForecastFromCollection($wids_map, $connected_open_workitems)
    {
        $bundle = [];
        $p = 1; //Start here
        $size = 0;
        $std = 0;
        $lsf = 1;
        $hsf = 0;
        $weighted_sum = 0;
        $p_sum = 0;
        $devsqr_sum = 0;
        foreach($wids_map as $wid)
        {
            $winfo = $connected_open_workitems[$wid];
            $branchinfo = $winfo['branchinfo'];
            $composite_success_forecast_p = $branchinfo['composite_success_forecast_p'];
            $composite_success_forecast_sample_size = $branchinfo['composite_success_forecast_sample_size'];
            if($lsf > $composite_success_forecast_p)
            {
                $lsf = $composite_success_forecast_p;
            }
            if($hsf < $composite_success_forecast_p)
            {
                $hsf = $composite_success_forecast_p;
            }
            $size += $composite_success_forecast_sample_size;
            $weighted_sum += $composite_success_forecast_sample_size * $composite_success_forecast_p;
            $p_sum += $composite_success_forecast_p;
        }
        if($size == 0)
        {
            $std = 0;
            $p = -123;
        } else {
            $mean_p = $p_sum / $size;
            foreach($wids_map as $wid)
            {
                $winfo = $connected_open_workitems[$wid];
                $composite_success_forecast_p = $branchinfo = $winfo['branchinfo']['composite_success_forecast_p'];
                $devsqr_sum += pow($composite_success_forecast_p - $mean_p, 2);
            }
            $std = sqrt($devsqr_sum/$size);
            $p = $weighted_sum/$size;
        }
        $bundle['OTSP'] = UtilityGeneralFormulas::getRoundSigDigs($p);
        $bundle['SIZE'] = $size;
        $bundle['STD'] = UtilityGeneralFormulas::getRoundSigDigs($std);
        $bundle['LSF'] = UtilityGeneralFormulas::getRoundSigDigs($lsf);
        $bundle['HSF'] = UtilityGeneralFormulas::getRoundSigDigs($hsf);
        return $bundle;
    }
    
    private function getNewWorkitemActionRow($roleid, $contextname, $goals_roles_map_ar, $wids_map, $connected_open_workitems)
    {
        
        $forecastinfo = $this->getForecastFromCollection($wids_map, $connected_open_workitems);
        
        if(!isset($goals_roles_map_ar[$roleid]['CL']))
        {
            $low_concern = 0;
            $med_concern = 0;
            $high_concern = 0;
        } else {
            $counts_ar = $goals_roles_map_ar[$roleid]['CL'];
            $low_concern = array_key_exists(10,$counts_ar) ? $counts_ar[10] : 0;
            $med_concern = array_key_exists(20,$counts_ar) ? $counts_ar[20] : 0;
            $high_concern = array_key_exists(30,$counts_ar) ? $counts_ar[30] : 0;
        }

        if(!isset($goals_roles_map_ar[$roleid]['Q']))
        {
            $q1 = 0;
            $q2 = 0;
            $q3 = 0;
            $q4 = 0;
        } else {
            $quadrants_ar = $goals_roles_map_ar[$roleid]['Q'];
            $q1 = array_key_exists(1,$quadrants_ar) ? $quadrants_ar[1] : 0;
            $q2 = array_key_exists(2,$quadrants_ar) ? $quadrants_ar[2] : 0;
            $q3 = array_key_exists(3,$quadrants_ar) ? $quadrants_ar[3] : 0;
            $q4 = array_key_exists(4,$quadrants_ar) ? $quadrants_ar[4] : 0;
        }

        $roleaction_row = array(
                array('value'=>$contextname),
                array('value'=>$low_concern),
                array('value'=>$med_concern),
                array('value'=>$high_concern),
                array('value'=>$q1),
                array('value'=>$q2),
                array('value'=>$q3),
                array('value'=>$q4),
                array('value'=>$forecastinfo['OTSP']),
                array('value'=>$forecastinfo['SIZE']),
                array('value'=>$forecastinfo['STD']),
                array('value'=>$forecastinfo['LSF']),
                array('value'=>$forecastinfo['HSF']),
            );

        return $roleaction_row;
    }

    private function getNewSprintActionRow($roleid, $contextname, $goals_roles_map_ar)
    {
        if(!isset($goals_roles_map_ar[$roleid]['CL']))
        {
            $low_concern = 0;
            $med_concern = 0;
            $high_concern = 0;
        } else {
            $counts_ar = $goals_roles_map_ar[$roleid]['CL'];
            $low_concern = array_key_exists(10,$counts_ar) ? $counts_ar[10] : 0;
            $med_concern = array_key_exists(20,$counts_ar) ? $counts_ar[20] : 0;
            $high_concern = array_key_exists(30,$counts_ar) ? $counts_ar[30] : 0;
        }

        if(!isset($goals_roles_map_ar[$roleid]['Q']))
        {
            $q1 = 0;
            $q2 = 0;
            $q3 = 0;
            $q4 = 0;
        } else {
            $quadrants_ar = $goals_roles_map_ar[$roleid]['Q'];
            $q1 = array_key_exists(1,$quadrants_ar) ? $quadrants_ar[1] : 0;
            $q2 = array_key_exists(2,$quadrants_ar) ? $quadrants_ar[2] : 0;
            $q3 = array_key_exists(3,$quadrants_ar) ? $quadrants_ar[3] : 0;
            $q4 = array_key_exists(4,$quadrants_ar) ? $quadrants_ar[4] : 0;
        }

        $roleaction_row = array(
                array('value'=>$contextname),
                array('value'=>$low_concern),
                array('value'=>$med_concern),
                array('value'=>$high_concern),
                array('value'=>$q1),
                array('value'=>$q2),
                array('value'=>$q3),
                array('value'=>$q4),
                array('value'=>0.95),
                array('value'=>5),
                array('value'=>.023),
                array('value'=>0.72),
                array('value'=>0.98),
            );

        return $roleaction_row;
    }
    
    /**
     * Looks for roles key in map members to add into the result
     * The result is multi DIM STRUCTURE: 
     *  roleid,'CL',concernlevel#,count
     *  roleid,'Q',quadrant#,count
     */
    private function getCountsByRoleAndConcern($map_open_request, $map_things_by_role, $connected_things)
    {
        $goals_roles_map_ar = array();
        //Factor in the thing counts
        foreach($map_things_by_role as $roleid=>$things_ar)
        {
            foreach($things_ar as $thingid)
            {
                $thingdetail = $connected_things[$thingid];
                if(isset($thingdetail['derived_quadrant']))
                {
                    $q = $thingdetail['derived_quadrant'];
                    if(!isset($goals_roles_map_ar[$roleid]['Q'][$q]))
                    {
                        $goals_roles_map_ar[$roleid]['Q'][$q] = 1;
                    } else {
                        $goals_roles_map_ar[$roleid]['Q'][$q] += 1;
                    }
                }
            }
        }
        //Factor in the comment counts
        foreach($map_open_request as $concernlevel=>$comments)
        {
            foreach($comments as $onecomment)
            {
                if(array_key_exists('roles', $onecomment))
                {
                    foreach($onecomment['roles'] as $roleid)
                    {
                        if(array_key_exists($roleid, $map_things_by_role))
                        {
                            $things_ar = $map_things_by_role[$roleid];
                            foreach($things_ar as $thingid)
                            {
                                $thingdetail = $connected_things[$thingid];
                                if(isset($thingdetail['derived_quadrant']))
                                {
                                    $q = $thingdetail['derived_quadrant'];
                                    if(!isset($goals_roles_map_ar[$roleid]['Q'][$q]))
                                    {
                                        $goals_roles_map_ar[$roleid]['Q'][$q] = 1;
                                    } else {
                                        $goals_roles_map_ar[$roleid]['Q'][$q] += 1;
                                    }
                                }
                            }
                        }
                        if(!isset($goals_roles_map_ar[$roleid]['CL'][$concernlevel]))
                        {
                            $goals_roles_map_ar[$roleid]['CL'][$concernlevel] = 1;
                        } else {
                            $goals_roles_map_ar[$roleid]['CL'][$concernlevel] += 1;
                        }
                    }
                }
            }
        }
        return $goals_roles_map_ar;
    }

    public function getGroupDashboardDataBundle($projectid=NULL, $only_active=TRUE)
    {
        try
        {
            $bundle = [];
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
        
    
    public function getProjectDashboardDataBundle($projectid=NULL, $only_active=TRUE)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_oContext->getSelectedProjectID();
                if(empty($projectid))
                {
                    throw new \Exception("Cannot get project dashboard without a projectid!");
                }
            }
            $bundle = array();
            $bundle['projectid'] = $projectid;
            $projectdetail = $this->m_oMapHelper->getOneProjectDetailData($projectid);
            $bundle['projectinfo'] = $projectdetail;
            
            //$root_goalid = $projectdetail['root_goalid'];   
            $this->m_oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($projectid);
            //$importantbundle = $this->m_oProjectForecaster->getBranchInfoDetailFromRootThing('goal', $root_goalid, 0);
            
            $bundle['analyzed_bundle'] = $this->m_oProjectForecaster->getDetail();
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @deprecated
     */
    public function getWorkitemDashboardDataBundle($workitemid, $only_active=TRUE)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Cannot get workitem dashboard without a goalid!");
            }
            $bundle = array();
            $bundle['workitemid'] = $workitemid;
            $workitem_detail = $this->m_oMapHelper->getOneBareWorkitemRecord($workitemid);
            $bundle['workiteminfo'] = $workitem_detail;
            
            //TODO refactor to use getDashboardMultiProjectBundle!!!!!!
            drupal_set_message("LOOK TODO refactor to use getDashboardMultiProjectBundle!!!!");
            return $bundle;
            
            
            
            $importantbundle = $this->m_oProjectForecaster->getBranchInfoDetailFromRootThing('workitem', $workitemid, 0);
            $bundle['analyzed_bundle'] = $importantbundle;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}

