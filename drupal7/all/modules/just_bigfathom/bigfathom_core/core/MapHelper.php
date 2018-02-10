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
require_once 'MapWorkitemHelper.php';
require_once 'PersonUtilizationInsight.php';

/**
 * This class tells us about mappings
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MapHelper extends \bigfathom\MapWorkitemHelper
{
    
    public static $GROUPID_EVERYONE = 1;
    public static $GROUPID_DPC = 10;
    
    /**
     * Get the candidate workitems of the project
     */
    public function getCandidateWorkitemsInProjectByID($projectid=NULL, $exclude_unknowntype=TRUE)
    {
        try
        {
            $exclude_parked = TRUE;
            $exclude_trashed = TRUE;
            return $this->getBrainstormItemsInProjectByID(NULL, $projectid, $exclude_unknowntype, $exclude_parked, $exclude_trashed);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the candidate goals of the project
     * @deprecated
     */
    public function getCandidateGoalsInProjectByID($projectid=NULL)
    {
        try
        {
            return $this->getBrainstormItemsInProjectByID('goal', $projectid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * Get the candidate tasks of the project
     * @deprecated
     */
    public function getCandidateTasksInProjectByID($projectid=NULL)
    {
        try
        {
            return $this->getBrainstormItemsInProjectByID('task', $projectid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the brainstorm items from a project
     */
    public function getBrainstormItemsInProjectByID($typename=NULL, $projectid=NULL, $exclude_unknowntype=TRUE, $exclude_parked=FALSE, $exclude_trashed=FALSE)
    {
        try
        {
            $filter_criteria = "bi.into_trash_dt IS NULL";
            if($exclude_parked)
            {
                $filter_criteria .= " AND bi.parkinglot_level < 1";
            }
            if($typename === NULL)
            {
                if($projectid !== NULL)
                {
                    $filter_criteria .= " AND bi.projectid=$projectid";
                }
                if($exclude_unknowntype)
                {
                    if(empty($filter_criteria))
                    {
                        $filter_criteria .= " AND bi.candidate_type IS NOT NULL and bi.candidate_type > ''";
                    } else {
                        $filter_criteria .= " AND bi.candidate_type IS NOT NULL and bi.candidate_type > ''";
                    }
                }
            } else {
                if($typename === 'goal')
                {
                    $bitype = 'G';
                } else
                if($typename === 'task')
                {
                    $bitype = 'T';
                } else {
                    throw new \Exception("Unrecognized typename=$typename");
                }
                if($projectid===NULL)
                {
                    $filter_criteria .= " AND bi.candidate_type='$bitype'";
                } else {
                    $filter_criteria .= " AND bi.projectid=$projectid and bi.candidate_type='$bitype'";
                }
            }
            $themap = array();
            $sSQL = "SELECT"
                    . " bi.id,"
                    . " bi.candidate_type as typeletter,"
                    . " bi.projectid as owner_projectid,"
                    . " bi.item_nm as workitem_nm,"
                    . " bi.purpose_tx as purpose_tx,"
                    . " bi.owner_personid as owner_personid,"
                    . " bi.updated_dt,"
                    . " bi.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_brainstorm_item_tablename." bi";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY bi.id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $typeletter = $record['typeletter'];
                $record['brainstormid'] = $id;
                $record['type'] = empty($typeletter) ? NULL : ($typeletter == 'G' ? 'goal' : 'task');
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a flat map of ALL the external resources in the project
     */
    public function getExternalResourcesInProjectByID($owner_projectid=NULL, $only_active=TRUE)
    {
        try
        {
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot getExternalResourcesInProjectByID without a projectid!");
            }
            $themap = array();
            $sSQL = "SELECT"
                    . " eq.id, eq.shortname, t.owner_projectid, "
                    . " eq.name, eq.description_tx, eq.active_yn, "
                    . " eq.condition_cd, s.title_tx as condition_title_tx, eq.condition_set_dt, "
                    . " eq.updated_dt, eq.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." t"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_external_resource_tablename." eq on eq.id=t.external_resourceid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_external_resource_condition_tablename." s on eq.condition_cd=s.code";
            $sSQL .= " WHERE t.active_yn=1 and t.owner_projectid=$owner_projectid and t.external_resourceid IS NOT NULL";
            if($only_active)
            {
                $sSQL .= " AND eq.active_yn=1";
            }
            $sSQL .= " ORDER BY eq.id";

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
     * Return a flat map of ALL the equipment in the project
     */
    public function getEquipmentInProjectByID($owner_projectid=NULL, $only_active=TRUE)
    {
        try
        {
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot getEquipmentInProjectByID without a projectid!");
            }
            $themap = array();
            $sSQL = "SELECT"
                    . " eq.id, eq.shortname, t.owner_projectid, "
                    . " eq.name, eq.description_tx, eq.active_yn, "
                    . " eq.condition_cd, s.title_tx as condition_title_tx, eq.condition_set_dt, "
                    . " eq.updated_dt, eq.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." t"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_equipment_tablename." eq on eq.id=t.equipmentid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_equipment_condition_tablename." s on eq.condition_cd=s.code";
            $sSQL .= " WHERE t.active_yn=1 and t.owner_projectid=$owner_projectid and t.equipmentid IS NOT NULL";
            if($only_active)
            {
                $sSQL .= " AND eq.active_yn=1";
            }
            $sSQL .= " ORDER BY eq.id";

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
     * Return all the available published information for a project
     */
    public function getPublishedProjectInfoBundle($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $bundle = [];
            
            $bypubid = $this->getAllBarePublishedProjectInfoRecordsByPUBID($projectid);
            $maxpubid = -1;
            foreach($bypubid as $pubid=>$detail)
            {
                if($pubid > $maxpubid)
                {
                    $maxpubid = $pubid;
                }
            }
            
            $bundle['projectid'] = $projectid;
            $bundle['latestinfo']['pubid'] = $maxpubid;
            $bundle['bypubid'] = $bypubid;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    /**
     * Return bundle where person is connected to the project
     */
    public function getDashboardMultiProjectBundle($connected_personid)
    {
        try
        {
            $connected_projects = $this->getProjectsConnectedToPersonMap($connected_personid);
            $just_pids = array_keys($connected_projects);
            $all_owner_personids = [];
            $connected_project_count = count($just_pids);
            if($connected_project_count < 1)
            {
                //There are no connected projects for this user.
                $bundle['comment'] = "No connected projects for user#$connected_personid";
                $project_bundles = [];
                $project_bundles['byproject'] = [];
            } else {
                $project_bundles = $this->getAllProjectOverviewBundlesForAllUsers($just_pids);
                $bundle['comment'] = "Found $connected_project_count connected projects for user#$connected_personid";
                foreach($connected_projects as $pid=>$detail)
                {
                    $owner_personid = $project_bundles['byproject'][$pid]['owner_personid'];
                    $all_owner_personids[$owner_personid] = $owner_personid;     
                    if(isset($project_bundles['byproject'][$pid]['maps']['delegate_owner']))
                    {
                        $owners = $project_bundles['byproject'][$pid]['maps']['delegate_owner'];
                        if(is_array($owners))
                        {
                            foreach($owners as $personid)
                            {
                                $all_owner_personids[$personid] = $personid;     
                            }
                        }
                    }
                    $project_bundles['byproject'][$pid]['connection_map'] = $detail;
                }
            }
            
            $bundle = $project_bundles;
            $bundle['all_owner_personids'] = $all_owner_personids;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getDashboardMultiProjectBundle for user#$connected_personid because ".$ex, 98777, $ex);
        }
    }
    
    public function getOneRootGoalIdFromProjectId($projectid)
    {
        try
        {
            $sSQL = "SELECT"
                    . " id, root_goalid "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj";
            $sSQL  .= " WHERE id=$projectid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $root_goalid = $record['root_goalid'];
            return $root_goalid;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getOneRootGoalIdFromProjectId for projectid#$projectid because ".$ex, 98777, $ex);
        }
    }
    
    public function getOneRootTWFromTPID($template_projectid)
    {
        try
        {
            $sSQL = "SELECT"
                    . " id, root_template_workitemid "
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." prj";
            $sSQL  .= " WHERE id=$template_projectid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $root_template_workitemid = $record['root_template_workitemid'];
            return $root_template_workitemid;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getOneRootTWFromTPID for tpid#$template_projectid because ".$ex, 98777, $ex);
        }
    }
    
    /**
     * Return a map of all root goals for the desired projects
     */
    public function getProjectRootGoalIdsMapFromProjectFilter($domain_of_projectids=NULL)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT"
                    . " id, root_goalid "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj";
            if($domain_of_projectids != NULL)
            {
                if(!is_array($domain_of_projectids))
                {
                    throw new \Exception("Expected an array of projectids!");
                }
                $formatted_list = implode(',', $domain_of_projectids);
                $sSQL  .= " WHERE id in ($formatted_list)";
            }
            $sSQL .= " ORDER BY root_goalid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $root_goalid = $record['root_goalid'];
                $projectid = $record['id'];
                $themap[$root_goalid] = $projectid;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a map of all root goals for the desired projects
     */
    public function getTPRootTWsMapFromTPFilter($domain_of_tpids=NULL)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT"
                    . " id, root_template_workitemid "
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." prj";
            if($domain_of_tpids != NULL)
            {
                if(!is_array($domain_of_tpids))
                {
                    throw new \Exception("Expected an array of template_projectid!");
                }
                $formatted_list = implode(',', $domain_of_tpids);
                $sSQL  .= " WHERE id in ($formatted_list)";
            }
            $sSQL .= " ORDER BY root_template_workitemid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $root_template_workitemid = $record['root_template_workitemid'];
                $tpid = $record['id'];
                $themap[$root_template_workitemid] = $tpid;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return names and overview counts for multiple projects that are project-work 
     * relevant to the specific user.
     */
    public function getAllProjectOverviewBundlesForOneUser($personid, $active_yn=1)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $uah = new UserAccountHelper();
            $relevant_projectids_map = $this->getRelevantProjectsMap($personid);
            /*
            //Find simple leadership projects
            $sSQL1 = "SELECT p.id as projectid, p.root_goalid "
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w on p.root_goalid=w.id AND w.id=$personid"
                    . " WHERE p.owner_personid=$personid OR w.id IS NOT NULL";
            
            $sSQL1 = "SELECT p.id as pid, p.owner_personid, p.root_goalid, w.owner_personid " 
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w on p.root_goalid=w.id and w.owner_personid=2 " 
                    . " WHERE p.owner_personid=$personid or w.owner_personid=$personid";
            
            $result1 = db_query($sSQL1);
            while($record1 = $result1->fetchAssoc()) 
            {
                $projectid = $record1['pid'];
                $relevant_projectids_ar[$projectid] = $projectid;
            }
            //Determine what projects this user is a member of
            $ugm = $uah->getPersonGroupMembershipBundle($personid);
            $membership_groups_ar = array_keys($ugm['detail']);
            $sSQL2 = "SELECT id "
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_group2project_tablename . " g2p on p.id=g2p.projectid "
                    . " WHERE p.owner_personid=$personid";
            if(count($membership_groups_ar) > 0)
            {
                $sSQL2 .= " OR groupid IN (" . implode(",", $membership_groups_ar) . ")";
            }
            $result = db_query($sSQL2);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['id'];
                $relevant_projectids_ar[$projectid] = $projectid;
            }
            //query bigfathom_map_delegate_workitemowner
            $sSQL3 = "SELECT p.id, p.root_goalid "
                    . " FROM " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " d2w "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p on p.root_goalid=d2w.workitemid "
                    . " WHERE d2w.personid=$personid AND p.root_goalid IS NOT NULL";
            $result3 = db_query($sSQL3);
            while($record3 = $result3->fetchAssoc()) 
            {
                $projectid = $record3['id'];
                $relevant_projectids_ar[$projectid] = $projectid;
            }
            
            */
            
            $relevant_projectids_ar = array_keys($relevant_projectids_map);
            $bundle = $this->getAllProjectOverviewBundles($relevant_projectids_ar);
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
        
    /**
     * Return names and overview counts for multiple projects
     */
    public function getAllProjectOverviewBundlesForAllUsers($relevant_projectids_ar=NULL, $active_yn=1)
    {
        try
        {
            return $this->getAllProjectOverviewBundles($relevant_projectids_ar, $active_yn);
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllProjectOverviewBundles because $ex");
        }
    }

    protected function getSimpleListProjectIDs($active_yn=NULL)
    {
        try
        {
            $sql = "select ID from " . DatabaseNamesHelper::$m_project_tablename;
            if($active_yn !== NULL)
            {
                $sql .= " where active_yn=$active_yn ";
            }
            $sql .= " order by ID ";
            $result = db_query($sql);
            $simple_array = $result->fetchCol();
            return $simple_array;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    protected function getSimpleListTPIDs($active_yn=NULL)
    {
        try
        {
            $sql = "select ID from " . DatabaseNamesHelper::$m_template_project_library_tablename;
            if($active_yn !== NULL)
            {
                $sql .= " where active_yn=$active_yn ";
            }
            $sql .= " order by ID ";
            $result = db_query($sql);
            $simple_array = $result->fetchCol();
            return $simple_array;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return names and overview counts for multiple projects
     */
    protected function getAllProjectOverviewBundles($relevant_projectids_ar=NULL, $active_yn=1)
    {
        try
        {
            $bundle = [];
            $byproject = [];
            $master_project_dep_map = [];
            
            if($relevant_projectids_ar === NULL)
            {
                //Get all the project IDs when explicit NULL is provided
                $relevant_projectids_ar = $this->getSimpleListProjectIDs($active_yn);
            }
            if(!is_array($relevant_projectids_ar))
            {
                throw new \Exception("Expected an array for relevant_projectids_ar but got '''" . $relevant_projectids_ar . "''' instead!");
            }
            if(!empty($relevant_projectids_ar))
            {
                $root_goalids_map = $this->getProjectRootGoalIdsMapFromProjectFilter($relevant_projectids_ar);
                $root_goalids_ar = array_keys($root_goalids_map);
                $allprojects = $this->getAllBareProjectInfoRecordsByPID($root_goalids_ar);
                $wid2projid_map = [];
                $wids_list = [];
                foreach($allprojects as $pid=>$info)
                {
                    $wid = $info['root_goalid'];
                    $wids_list[] = $wid;
                    $wid2projid_map[$wid] = $pid;  
                }
                $filter_ar = [];
                $filter_ar['w.id'] = $wids_list;
                $projectrootgoal_workitems = [];
                foreach($relevant_projectids_ar as $pid)
                {
                    $pwids_ar = $this->getRichWorkitemsByID($pid, $filter_ar, $active_yn);
                    foreach($pwids_ar as $wid=>$record)
                    {
                        $projectrootgoal_workitems[$wid] = $record;
                    }
                }
                $need2query = [];
                foreach($wid2projid_map as $wid=>$projid)
                {
                    $detail = $projectrootgoal_workitems[$wid];
                    $open_workitems = $this->getIDMapOfWorkitemsInProject($projid, NULL, 0, 1);
                    $widsbytype = $open_workitems['widsbytype'];
                    $detail['maps']['open_workitems'] = $widsbytype;
                    if(count($detail['maps']['ddw']) > 0)
                    {
                        $checkworkitems = $detail['maps']['ddw'];
                        foreach($checkworkitems as $checkwid)
                        {
                            $need2query[] = $checkwid;
                        }
                    }
                    $detail['maps']['deliverablewids'] = $open_workitems['deliverablewids'];
                    $detail['surrogate_yn'] = $allprojects[$projid]['surrogate_yn'];
                    $detail['allow_refresh_from_remote_yn'] = $allprojects[$projid]['allow_refresh_from_remote_yn'];
                    $detail['project_active_yn'] = $allprojects[$projid]['active_yn'];
                    $detail['root_goal_active_yn'] = $detail['active_yn'];
                    $detail['publishedrefname'] = $allprojects[$projid]['publishedrefname'];
                    $detail['allow_status_publish_yn'] = $allprojects[$projid]['allow_status_publish_yn'];
                    $detail['remote_uri'] = $allprojects[$projid]['remote_uri'];
                    $byproject[$projid] = $detail;
                }
                if(count($need2query) > 0)
                {
                    $filter_ar['w.id'] = $need2query;
                    $lookup_workitems = $this->getBareWorkitemsByID(NULL, $filter_ar, $active_yn);
                }
                foreach($wid2projid_map as $wid=>$projid)
                {
                    $byproject[$projid]['root_goalid'] = $allprojects[$projid]['root_goalid'];
                    $byproject[$projid]['mission_tx'] = $allprojects[$projid]['mission_tx'];
                    $byproject[$projid]['project_contextid'] = $allprojects[$projid]['project_contextid'];
                    $detail = $projectrootgoal_workitems[$wid];
                    $dependent_projectids = [];
                    if(count($detail['maps']['ddw']) > 0)
                    {
                        $checkworkitems = $detail['maps']['ddw'];
                        foreach($checkworkitems as $checkwid)
                        {
                            if(array_key_exists($checkwid, $projectrootgoal_workitems))
                            {
                                $rec = $projectrootgoal_workitems[$checkwid];
                            } else {
                                $rec = $lookup_workitems[$checkwid];
                            }
                            $opid = $rec['owner_projectid'];
                            $dependent_projectids[$opid] = $opid;
                            if(!isset($master_project_dep_map['a2d'][$projid]))
                            {
                                $master_project_dep_map['a2d'][$projid] = [];
                            }
                            $master_project_dep_map['a2d'][$projid][] = $opid;
                            if(!isset($master_project_dep_map['d2a'][$opid]))
                            {
                                $master_project_dep_map['d2a'][$opid] = [];
                            }
                            $master_project_dep_map['d2a'][$opid][] = $projid;
                        }
                    }
                    $byproject[$projid]['maps']['dependent_projectids'] = $dependent_projectids;
                }
            }
            
            $bundle['project_dep_map'] = $master_project_dep_map;
            $bundle['byproject'] = $byproject;
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllProjectOverviewBundles because $ex");
        }
    }
    
    /**
     * Return names and overview counts for multiple template projects
     */
    public function getAllTPOverviewBundles($relevant_tpids_ar=NULL, $active_yn=1)
    {
        try
        {
            $bundle = [];
            $by_tpid = [];
            $master_project_dep_map = [];
            
            if($relevant_tpids_ar === NULL)
            {
                //Get all the project IDs when explicit NULL is provided
                $relevant_tpids_ar = $this->getSimpleListTPIDs($active_yn);
            }
            if(!is_array($relevant_tpids_ar))
            {
                throw new \Exception("Expected an array for relevant_tpids_ar but got '''" . $relevant_tpids_ar . "''' instead!");
            }
            if(!empty($relevant_tpids_ar))
            {
                $root_tpids_map = $this->getTPRootTWsMapFromTPFilter($relevant_tpids_ar);
                $root_twids_ar = array_keys($root_tpids_map);
                $all_tps = $this->getAllBareTPInfoRecordsByTPID($root_twids_ar);
                $twid2tpid_map = [];
                $twids_list = [];
                foreach($all_tps as $tpid=>$info)
                {
                    $twid = $info['root_template_workitemid'];
                    $twids_list[] = $twid;
                    $twid2tpid_map[$twid] = $tpid;  
                }
                $filter_ar = [];
                $filter_ar['w.id'] = $twids_list;
                $tp_root_tws = [];
                foreach($relevant_tpids_ar as $tpid)
                {
                    $pwids_ar = $this->getRichTWsByID($tpid, $filter_ar);
                    foreach($pwids_ar as $twid=>$record)
                    {
                        $tp_root_tws[$twid] = $record;
                    }
                }
                $need2query = [];
                foreach($twid2tpid_map as $twid=>$template_projid)
                {
                    $detail = $tp_root_tws[$twid];
                    $open_tws = $this->getIDMapOfTWsInTP($template_projid, NULL);
                    $widsbytype = $open_tws['twidsbytype'];
                    $detail['maps']['open_tws'] = $widsbytype;
                    if(count($detail['maps']['ddw']) > 0)
                    {
                        $checkworkitems = $detail['maps']['ddw'];
                        foreach($checkworkitems as $checkwid)
                        {
                            $need2query[] = $checkwid;
                        }
                    }
                    $detail['maps']['deliverabletwids'] = $open_tws['deliverabletwids'];
                    $detail['tp_active_yn'] = $all_tps[$template_projid]['active_yn'];
                    $detail['publishedrefname'] = $all_tps[$template_projid]['publishedrefname'];
                    $detail['remote_uri'] = $all_tps[$template_projid]['remote_uri'];
                    $by_tpid[$template_projid] = $detail;
                }
                if(count($need2query) > 0)
                {
                    $filter_ar['w.id'] = $need2query;
                    $lookup_workitems = $this->getBareTWsByID(NULL, $filter_ar, $active_yn);
                }
                foreach($twid2tpid_map as $twid=>$template_projid)
                {
                    $by_tpid[$template_projid]['root_template_workitemid'] = $all_tps[$template_projid]['root_template_workitemid'];
                    $by_tpid[$template_projid]['template_nm'] = $all_tps[$template_projid]['template_nm'];
                    $by_tpid[$template_projid]['submitter_blurb_tx'] = $all_tps[$template_projid]['submitter_blurb_tx'];
                    $by_tpid[$template_projid]['mission_tx'] = $all_tps[$template_projid]['mission_tx'];
                    $by_tpid[$template_projid]['allow_detail_publish_yn'] = $all_tps[$template_projid]['allow_detail_publish_yn'];
                    $by_tpid[$template_projid]['snippet_bundle_head_yn'] = $all_tps[$template_projid]['snippet_bundle_head_yn'];
                    $by_tpid[$template_projid]['project_contextid'] = $all_tps[$template_projid]['project_contextid'];
                    $detail = $tp_root_tws[$twid];
                    $dependent_projectids = [];
                    if(count($detail['maps']['ddw']) > 0)
                    {
                        $checkworkitems = $detail['maps']['ddw'];
                        foreach($checkworkitems as $checkwid)
                        {
                            if(array_key_exists($checkwid, $tp_root_tws))
                            {
                                $rec = $tp_root_tws[$checkwid];
                            } else {
                                $rec = $lookup_workitems[$checkwid];
                            }
                            $opid = $rec['owner_template_projectid'];
                            $dependent_projectids[$opid] = $opid;
                            if(!isset($master_project_dep_map['a2d'][$template_projid]))
                            {
                                $master_project_dep_map['a2d'][$template_projid] = [];
                            }
                            $master_project_dep_map['a2d'][$template_projid][] = $opid;
                            if(!isset($master_project_dep_map['d2a'][$opid]))
                            {
                                $master_project_dep_map['d2a'][$opid] = [];
                            }
                            $master_project_dep_map['d2a'][$opid][] = $template_projid;
                        }
                    }
                    $by_tpid[$template_projid]['maps']['dependent_tpids'] = $dependent_projectids;
                }
            }
            
            $bundle['tp_dep_map'] = $master_project_dep_map;
            $bundle['by_tpid'] = $by_tpid;
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllTPOverviewBundles because $ex");
        }
    }
    
    /**
     * Return names and overview counts for all surrogate projects
     */
    public function getAllSurrogateProjectOverviewBundles($active_yn=1)
    {
        try
        {
            $bundle = [];
            $master_project_dep_map = [];   //TODO!!!!!!!!!!!!!!!!!
            $by_surrogate_project = $this->getAllRichSurrogateProjectInfoRecordsBySPID();
            
            //TODO fill in the dependent projects
            foreach($by_surrogate_project as $id=>$detail)
            {
                //inloop $master_project_dep_map['a2d'][$surrogate_projectid][] = $opid;
                $by_surrogate_project[$id]['maps']['dependent_projectids'] = [];    //TODO
            }
            
            
            //$bundle['project_dep_map'] = $master_project_dep_map;
            $bundle['by_surrogate_project'] = $by_surrogate_project;
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllSurrogateProjectOverviewBundles because $ex");
        }
    }
    
    /**
     * Return a key value array of all goal IDs which are also roots of projects
     */
    public function getProjectRootGoalIdsMapFromGoalFilter($domain_of_goal_ids=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " id, root_goalid "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj";
            if($domain_of_goal_ids != NULL)
            {
                if(!is_array($domain_of_goal_ids))
                {
                    throw new \Exception("Expected an array of goalids!");
                }
                $formatted_list = implode(',', $domain_of_goal_ids);
                $sSQL  .= " WHERE root_goalid in ($formatted_list)";
            }
            $sSQL .= " ORDER BY root_goalid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $root_goalid = $record['root_goalid'];
                $projectid = $record['id'];
                $themap[$root_goalid] = $projectid;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a key value array of all goal IDs which are also roots of projects
     */
    public function getAllBarePublishedProjectInfoRecordsByPUBID($projectid=NULL,$only_allow_public=TRUE)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " pub.id, pub.publishedrefname, pub.project_contextid, pub.project_nm, pub.projectid, pub.root_goalid, "
                    . " pub.mission_tx, pub.owner_personid, pub.project_manager_override_tx, "
                    . " pub.planned_start_dt, pub.actual_start_dt, pub.planned_end_dt, pub.actual_end_dt, "
                    . " pub.onbudget_p, pub.onbudget_u, pub.ontime_p, pub.ontime_u, pub.comment_tx, pub.status_cd, "
                    . " pub.status_set_dt, pub.updated_dt, pub.created_dt, "
                    . " pub.active_yn "
                    . " FROM ".DatabaseNamesHelper::$m_published_project_info_tablename . " pub"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p ON p.id=pub.projectid ";
            $aWHERE = [];
            if($projectid != NULL)
            {
                $aWHERE[] = "projectid=$projectid";
            }
            if($only_allow_public)
            {
                $aWHERE[] = "p.allow_status_publish_yn=1";
            }
            if(count($aWHERE)>0)
            {
                $sSQL .= " WHERE " . implode(" AND ", $aWHERE);
            }
            $sSQL .= " ORDER BY id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $pubid = $record['id'];
                $themap[$pubid] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getLatestPublishedProjectInfoNameMap()
    {
        try
        {
            $themap = [];
            
            $sSQL = "SELECT ppi.publishedrefname, ppi.planned_end_dt, ppi.actual_end_dt, ppi.status_cd, ppi.ontime_p, ppi.updated_dt as published_dt"
                    . " , ppi.project_contextid, pc.shortname as project_context_name, p.allow_status_publish_yn"
                    . " FROM " . DatabaseNamesHelper::$m_published_project_info_tablename . " ppi "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_context_tablename . " pc ON pc.id=ppi.project_contextid "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p ON p.id=ppi.projectid "
                    . " WHERE ppi.active_yn=1 AND p.allow_status_publish_yn=1"
                    . " ORDER BY publishedrefname";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $publishedrefname = $record['publishedrefname'];
                $planned_end_dt = $record['planned_end_dt'];
                $actual_end_dt = $record['actual_end_dt'];
                $ontime_p = $record['ontime_p'];
                unset($record['planned_end_dt']);
                unset($record['actual_end_dt']);
                unset($record['ontime_p']);
                $end_dt = empty($actual_end_dt) ? $planned_end_dt : $actual_end_dt;
                $record['end_dt'] = $end_dt;
                $record['otsp'] = round($ontime_p,4);
                $themap[$publishedrefname] = $record;
            }            
                    
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a key value array of all goal IDs which are also roots of projects
     */
    public function getLatestBarePublishedProjectInfoRecord($projectid=NULL)
    {
        try
        {
            $sSQL = "SELECT"
                    . " pub.id, pub.publishedrefname, pub.project_contextid, pub.project_nm, pub.projectid, pub.root_goalid, "
                    . " pub.mission_tx, pub.owner_personid, pub.project_manager_override_tx, "
                    . " pub.planned_start_dt, pub.actual_start_dt, pub.planned_end_dt, pub.actual_end_dt, "
                    . " pub.onbudget_p, pub.onbudget_u, pub.ontime_p, pub.ontime_u, pub.comment_tx, pub.status_cd, "
                    . " pub.status_set_dt, pub.updated_dt, pub.created_dt, "
                    . " pub.active_yn "
                    . " FROM ".DatabaseNamesHelper::$m_published_project_info_tablename . " pub";
            if($projectid != NULL)
            {
                $sSQL  .= " WHERE projectid=$projectid";
            }
            $sSQL .= " ORDER BY id DESC limit 1";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc(); 
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a key value array of all project IDs which have specified root goals
     */
    public function getAllBareProjectInfoRecordsByPID($root_goalids_ar=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " id, project_contextid, prj.surrogate_yn, prj.allow_refresh_from_remote_yn, prj.allow_status_publish_yn, "
                    . " prj.surrogate_ob_p, prj.surrogate_ot_p, "
                    . " prj.ob_scf, prj.obsu, "
                    . " prj.ot_scf, prj.otsu, "
                    . " prj.snippet_bundle_head_yn, prj.archive_yn, "
                    . " prn2p.publishedrefname, prn2p.remote_uri, "
                    . " root_goalid, mission_tx, owner_personid, importance, active_yn "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2project_tablename." prn2p on prn2p.projectid=prj.id";
            $sSQL .= " WHERE template_yn=0";
            if($root_goalids_ar != NULL)
            {
                if(!is_array($root_goalids_ar))
                {
                    throw new \Exception("Expected an array of goalids!");
                }
                $formatted_list = implode(',', $root_goalids_ar);
                $sSQL  .= " AND root_goalid in ($formatted_list)";
            }
            $sSQL .= " ORDER BY id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['id'];
                $themap[$projectid] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a key value array of all template IDs
     */
    public function getAllBareTPInfoRecordsByTPID($filter_active_yn=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " id, project_contextid, "
                    . " prj.ob_scf, prj.obsu, "
                    . " prj.ot_scf, prj.otsu, "
                    . " prj.snippet_bundle_head_yn, prj.allow_detail_publish_yn, "
                    . " prn2p.publishedrefname, prn2p.remote_uri, "
                    . " root_template_workitemid, template_nm, submitter_blurb_tx, mission_tx, "
                    . " owner_personid, importance, active_yn "
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename." prn2p on prn2p.template_projectid=prj.id";
            if($filter_active_yn !== NULL)
            {
                $active_yn = $filter_active_yn ? 1 : 0;
                $sSQL .= " WHERE active_yn=$active_yn";
            }
            $sSQL .= " ORDER BY id";    
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['id'];
                $themap[$projectid] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return TRUE if nodes in the branch are connected to nodes outside the branch, else FALSE.
     */
    public function checkOutsideNodesDependOnBranch($rootgoalid)
    {
        //TODO -- check the goaltree for nodes that are connecting to a node OUTSIDE of the branch!
        return FALSE;
    }
    
    private function getValueAsArray($key, &$container)
    {
        if(array_key_exists($key, $container))
        {
            return $container[$key];
        }
        $container[$key] = array();
        return $container[$key];
    }

    private function addValueToValueArray($key, &$container, $value)
    {
        if(!array_key_exists($key, $container))
        {
            $container[$key] = array();
        }
        $values = &$container[$key];
        $values[] = $value;
    }
    
    /**
     * Provide a flat representation of the project tree content
     * @param type $node root node of the project tree
     * @param type $projectmap map where key is projectid and value is array of child projectid
     * @param type $projectchildtoparentmap map where key is child projectid and value is array of parent projectid
     * @param type $projecttocontained_goal_countsmap map where key is projectid and value is count of goals in project
     * @param type $projecttocontained_deliverables_countsmap map where key is projectid and value is count of deliverables in project
     */
    private function deriveFlatProjectMapping($node
            , &$projectmap
            , &$projectchildtoparentmap
            , &$projecttocontained_goal_countsmap
            , &$projecttocontained_deliverables_countsmap
            , &$projecttocontained_goal_done_countsmap
            , &$projecttocontained_deliverables_done_countsmap)
    {
        try
        {
            if(!is_array($node) || $node['type'] !== 'project')
            {
                throw new \Exception("Expected ONE project tree but got this instead = " . print_r($node,TRUE));
            }
            
            $projectid = $node['projectid'];
            $projecttocontained_goal_countsmap[$projectid] = $node['goalcount'];
            $projecttocontained_deliverables_countsmap[$projectid] = $node['deliverablecount'];
            $projecttocontained_goal_done_countsmap[$projectid] = $node['done_goalcount'];
            $projecttocontained_deliverables_done_countsmap[$projectid] = $node['done_deliverablecount'];
            if(isset($node['children']))
            {
                foreach($node['children'] as $childnode)
                {
                    $child_projectid=$childnode['projectid'];
                    $this->addValueToValueArray($child_projectid, $projectchildtoparentmap, $projectid);
                    $this->addValueToValueArray($projectid, $projectmap, $child_projectid);
                    $this->deriveFlatProjectMapping($childnode
                            , $projectmap
                            , $projectchildtoparentmap
                            , $projecttocontained_goal_countsmap
                            , $projecttocontained_deliverables_countsmap
                            , $projecttocontained_goal_done_countsmap
                            , $projecttocontained_deliverables_done_countsmap);
                }
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function isTerminalWorkitemStatus($status_cd)
    {
        try
        {
            $sSQL = "SELECT terminal_yn"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_status_tablename." gs"
                    . " WHERE gs.code='$status_cd'";
            $value = db_query($sSQL)->fetchField();
            return $value == 1;
        } catch (\Exception $ex) {
            throw $ex;
        }        
    }

    /**
     * Convert tree of GoalTask nodes into tree of Project nodes
     * looks deprecated ???
    private function getProjectTreeFromGoalTaskTree($root_node
                    , $goalprojectmap
                    , $hierarchy_level=0
                    , $depwiid=NULL)
    {
        try
        {
            $goalcount=0;
            $deliverablecount=0;
            $done_goalcount=0;
            $done_deliverablecount=0;
            $childgoalcount=0;
            $children = array();
            if(is_array($root_node))
            {
                if(count($root_node) !== 1)
                {
                    throw new \Exception("Expected one NODE but instead got " . print_r($root_node,TRUE));
                }
                $root_node = $root_node[0]; //Assume the one item is the root node
            }
            if(!isset($root_node->type) || $root_node->type !== 'goal')
            {
               throw new \Exception("Expected one GOAL NODE but instead got " . print_r($root_node,TRUE));
            }

            //Update our main counts for this node
            $goalcount++;
            if($root_node->detail['client_deliverable_yn'] > 0)
            {
                $deliverablecount++;
            }
            if($this->isTerminalWorkitemStatus($root_node->detail['status_cd']))
            {
                $done_goalcount++;
                if($root_node->detail['client_deliverable_yn'] > 0)
                {
                    $done_deliverablecount++;
                }            
            }
            
            if(key_exists($root_node->id, $goalprojectmap))
            {
                $root_is_project = TRUE;
                $hierarchy_level++;
            } else {
                $root_is_project = FALSE;
            }
            
            //Collect metrics about the kids
            foreach($root_node->children as $childnode)
            {
                $childgoalcount++;
                $subtree_result = $this->getProjectTreeFromGoalTaskTree(
                                            $childnode, 
                                            $goalprojectmap, 
                                            $hierarchy_level, 
                                            $root_node->id);
                $goalcount += $subtree_result['goalcount'];
                $deliverablecount += $subtree_result['deliverablecount'];
                $done_goalcount += $subtree_result['done_goalcount'];
                $done_deliverablecount += $subtree_result['done_deliverablecount'];
                if($subtree_result['type'] === 'project')
                {
                    $children[] = $subtree_result;
                } else {
                    foreach($subtree_result['children'] as $projectnode)
                    {
                        $children[] = $projectnode;
                    }
                }
            }
            
            if(key_exists($root_node->id, $goalprojectmap))
            {
                //This is a project node, so add it to our tree.
                $projectid = $goalprojectmap[$root_node->id];
                return array(                            
                        'projectid'=>$projectid,
                        'goalid'=>$root_node->id,
                        'type'=>'project',
                        'hierarchy_level'=>$hierarchy_level,
                        'goal_hierarchy_level'=>$root_node->detail['hierarchy_level'],
                        'name'=>$root_node->name,
                        'goalcount'=>$goalcount,
                        'deliverablecount'=>$deliverablecount,
                        'done_goalcount'=>$done_goalcount,
                        'done_deliverablecount'=>$done_deliverablecount,
                        'children'=>$children);
            }
            
            //Bubble these up to the nearest parent project
            return array(
                        'type'=>'INFOBUBBLE',
                        'goalcount'=>$goalcount,
                        'deliverablecount'=>$deliverablecount,
                        'done_goalcount'=>$done_goalcount,
                        'done_deliverablecount'=>$done_deliverablecount,
                        'children'=>$children
                    );
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    */
   
    
    /**
     * Returns a tree of goals and tasks for project
     * Tree is represented by the root node
     */
    public function getGoalTaskTreeOfProject($projectid, $allgoals_by_id=NULL, $alltasks_by_id=NULL, $include_tasks=TRUE)
    {
        try
        {
            $project_record = $this->getOneProjectDetailData($projectid);
            
            $root_goalid = $project_record['root_goalid'];
            if(empty($root_goalid))
            {
                throw new \Exception("Failed to get goalid from projectid=$projectid");
            }
            $result = $this->getOneWorkitemTree(NULL, NULL
                                    , $root_goalid
                                    , 0
                                    , $include_tasks);
            
            if(is_array($result))
            {
                if(count($result) > 1)
                {
                    throw new \Exception("A project can only have ONE root goal but we have " . count($result) . " in project $projectid!");
                }
                foreach($result as $node)
                {
                    //Just grab the first one
                    $result = $node;
                    break;
                }
            }
            
            return $result;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function convertMapIntoListUsingKey($map)
    {
        $list = [];
        foreach($map as $key=>$value)
        {
            $list[] = $key;
        }
        return $list;
    }

    /**
     * Returns a workitem tree
     * NOTE: Only returns the tree for the given root node!
     */
    public function getOneWorkitemTree($allworkitems_by_id=NULL
                                    , $fast_hierarchy_lookup=NULL
                                    , $root_goalid=NULL
                                    , $replace_parent_null=0
                                    , $include_child_goals=TRUE
                                    , $include_tasks=TRUE
                                    , $include_rootnode=TRUE
                                    , $hierarchy_level=0
                                    , $stop_at_root_of_subproject=TRUE
                                    , $owner_projectid=NULL
                                    )
    {
        try
        {
            $only_active=TRUE;
            if(empty($root_goalid))
            {
                throw new \Exception("Must have a root node! hierarchy_level=$hierarchy_level allworkitems_by_id=$allworkitems_by_id fast_hierarchy_lookup=$fast_hierarchy_lookup root_goalid=$root_goalid");
            }
            $hierarchy_level++;
            $children = array();
            
            //error_log("LOOK we are calling getOneWorkitemTree root_goalid=$root_goalid hierarchy_level=$hierarchy_level");
            
            if($allworkitems_by_id == NULL)
            {
                if(empty($owner_projectid))
                {
                    $proj_rec = $this->getOneBareWorkitemRecord($root_goalid);
                    $owner_projectid = $proj_rec['owner_projectid'];
                }
                $allworkitems_by_id = $this->getRichWorkitemsByID($owner_projectid);
            }
            $record = $allworkitems_by_id[$root_goalid];
            if($fast_hierarchy_lookup == NULL)
            {
                $fast_hierarchy_lookup = $this->getFastHierarchyWorkitemIDLookup($allworkitems_by_id);
            }
            $root_goal_projectid = $record['owner_projectid'];
            if(empty($owner_projectid))
            {
                $owner_projectid = $root_goal_projectid;
                if(empty($owner_projectid))
                {
                    throw new \Exception("Failed getOneWorkitemTree with empty parent_projectid!"
                            . "\n\troot_goalid=$root_goalid"
                            . "\n\tallworkitems_by_id=" . print_r($allworkitems_by_id,TRUE));
                }
            }
            
            $lookforkids = TRUE;
            if(!array_key_exists($root_goalid, $allworkitems_by_id))
            {
                throw new \Exception("Data error in getOneGoalTaskTree for parent_projectid=$owner_projectid at hl=$hierarchy_level"
                        . " did not find an active goal with id=[$root_goalid]"
                        . " count(allnodes)=" . count($allworkitems_by_id) 
                        . " detail=" . print_r($allworkitems_by_id,TRUE));
            }
            if($include_rootnode)
            {
                //Add the root node to our collection now
                $lookforkids = FALSE;
                $include_rootnode = FALSE;  //So we dont repeat this
                $record = $allworkitems_by_id[$root_goalid];
                $record['hierarchy_level'] = $hierarchy_level;
                $record['type'] = 'goal';
                $id = $record['id'];
                $node_key = "goal_{$id}";;
                $record['key'] = $node_key;
                $children[] = (object) array(
                        'id'=>$id,
                        'type'=>'goal',
                        'key'=>$node_key,
                        'name'=>$record['workitem_nm'],
                        'detail'=>$record,
                        'children'=>$this->getOneWorkitemTree( 
                                        $allworkitems_by_id, 
					$fast_hierarchy_lookup,
                                        $id,
                                        $replace_parent_null,
                                        $include_child_goals,
                                        $include_tasks, 
                                        $include_rootnode, 
                                        $hierarchy_level,
                                        $stop_at_root_of_subproject, 
                                        $owner_projectid
                                    )
                );

            }                
            
            if($lookforkids 
                    && !empty($root_goal_projectid) 
                    && (!$stop_at_root_of_subproject 
                            || $record['owner_projectid'] == $owner_projectid))
            {
                if(!empty($root_goalid) && $include_child_goals)
                {
                    //Look for all workitem nodes with matching parent
                    foreach($allworkitems_by_id as $id=>$record)
                    {
                        $map_ddw = $record['maps']['ddw'];
                        $basetype = $record['workitem_basetype'];
                        if($basetype === 'G' || $include_tasks)
                        {
                            if($basetype === 'G')
                            {
                                $basetypename = 'goal';
                            } else {
                                $basetypename = 'task';
                            }
                            $encoded_key = "{$basetypename}_{$id}";
                            if(isset($map_ddw[$root_goalid]))
                            {
                                //This node is a child of the parent
                                $record['depwiid'] = $root_goalid;
                                $record['hierarchy_level'] = $hierarchy_level;
                                $record['type'] = $basetypename;
                                $record['key'] = $encoded_key;
                                $children[] = (object) array(
                                        'id'=>$id,
                                        'type'=>$basetypename,
                                        'key'=>$encoded_key,
                                        'name'=>$record['workitem_nm'],
                                        'detail'=>$record,
                                        'children'=>$this->getOneWorkitemTree( 
                                                        $allworkitems_by_id, 
                                                        $fast_hierarchy_lookup,
                                                        $id,
                                                        $replace_parent_null,
                                                        $include_child_goals,
                                                        $include_tasks, 
                                                        FALSE, 
                                                        $hierarchy_level, 
                                                        $stop_at_root_of_subproject, 
                                                        $owner_projectid
                                                    )
                                    );
                            }
                        }
                    }
                }
            }
            return $children;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectsIDs($only_active=TRUE)
    {
        $projects = $this->getProjectsByID(NULL, $only_active);
        $keys = array_keys($projects);
        return $keys;
    }
    
    /**
     * Return a rich map of project detail with ID as the key
     */
    public function getProjectsByID($order_by_ar=NULL, $only_active=TRUE)
    {
        try
        {
            return $this->getProjectsData($order_by_ar,'id',$only_active);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of project detail with name as the key
     */
    public function getProjectsByName($order_by_ar=NULL,$only_active=TRUE)
    {
        try
        {
            return $this->getProjectsData($order_by_ar,'root_workitem_nm',$only_active);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of workitem detail with ID as the key
     */
    public function getProjectOrphanWorkitemsByID($owner_projectid=NULL, $only_active=TRUE)
    {
        try
        {
            $active_yn = $only_active ? 1 : 0;
            $named_filter_ar['ONLY_ORPHANS'] = true;
            $bundle = $this->getRichWorkitemsByIDBundle($owner_projectid, NULL, $active_yn, $named_filter_ar);
            $workitems = $bundle['workitems'];
            return $workitems;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }  
    
    /**
     * Return a rich map of template workitem detail with ID as the key
     */
    public function getTPOrphanTWsByID($owner_projectid=NULL)
    {
        try
        {
            $named_filter_ar['ONLY_ORPHANS'] = true;
            $bundle = $this->getRichTWsByIDBundle($owner_projectid, NULL, $named_filter_ar);
            $workitems = $bundle['tws'];
            return $workitems;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }  
    
    /**
     * Return all the goals that are in the tree with the specified goal root
     */
    private function getGoalsInTree($root_goalid=NULL)
    {
        try
        {
            $allgoals_by_id = NULL;
            $alltasks_by_id = NULL;
            $include_child_goals = TRUE;
            $include_tasks = FALSE;
            $goal_tree = $this->getOneWorkitemTree(NULL, NULL
                                    , $root_goalid
                                    , 0
                                    , $include_child_goals
                                    , $include_tasks);
            return $this->flattenGoalTree($goal_tree);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return all the goals of the tree(s) as a flat array
     * Change the hierarchy_level to largest number found.
     */
    private function flattenGoalTree($goal_tree,&$goals=NULL)
    {
        try
        {
            
            if($goals == NULL)
            {
                $goals = array();
            }

            //Allow that we might not have just one root node
            if(isset($goal_tree->detail))
            {
                //Only one root node provided
                $rootnodes[] = $goal_tree;
            } else {
                //Assume we have an array of root nodes
                $rootnodes = $goal_tree;
            }

            foreach($rootnodes as $branch_root_node)
            {
                $detail = $branch_root_node->detail;
                $id = $detail['id'];
                if(array_key_exists($id, $goals))
                {
                    //Just make sure hierarchy_level is largest value so far seen
                    $existing = $goals[$id];
                    if($existing['hierarchy_level'] < $detail['hierarchy_level'])
                    {
                        $existing['hierarchy_level'] = $detail['hierarchy_level'];   
                    }
                } else {
                    //We have not seen this one before.
                    $goals[$id] = $detail;
                }
                $children = $branch_root_node->children;

                foreach($children as $child_tree)
                {
                    $this->flattenGoalTree($child_tree,$goals);
                }
            }
            
            return $goals;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return all the tasks that are in the tree with the specified goal root
     */
    public function getRootGoalAndTasksInTree($root_goalid, $include_child_goals=TRUE)
    {
        try
        {
            $allgoals_by_id = NULL;
            $alltasks_by_id = NULL;
            $include_tasks = TRUE;
            $workitemtree = $this->getOneWorkitemTree(NULL, NULL
                                    , $root_goalid
                                    , 0
                                    , $include_child_goals
                                    , $include_tasks);
            return $this->flattenGoalTaskTree($workitemtree);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return all the goals/tasks of the tree(s) as a flat array
     */
    private function flattenGoalTaskTree($workitemtree,&$goals_and_tasks=NULL)
    {
        try
        {
            
            if($goals_and_tasks == NULL)
            {
                $goals_and_tasks = array();
            }

            //Allow that we might not have just one root node
            if(isset($workitemtree->detail))
            {
                //Only one root node provided
                $rootnodes[] = $workitemtree;
            } else {
                //Assume we have an array of root nodes
                $rootnodes = $workitemtree;
            }

            foreach($rootnodes as $branch_root_node)
            {
                $type = $branch_root_node->type;
                $detail = $branch_root_node->detail;
                $detail['type'] = $type;
                $id = $type.'_'.$detail['id'];
                $goals_and_tasks[$id] = $detail;
                $children = $branch_root_node->children;

                foreach($children as $child_tree)
                {
                    $this->flattenGoalTaskTree($child_tree,$goals_and_tasks);
                }
            }
            
            return $goals_and_tasks;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return one project record
     */
    public function getProjectRecord($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $themap = $this->getProjectsData(NULL,'id',FALSE,"prj.id=$projectid");
            $value = $themap[$projectid];
            if(empty($value))
            {
                throw new \Exception("Failed to find projectid#$projectid in map=".print_r($themap,TRUE));
            }
            return $value;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return one sprint record
     */
    public function getSprintRecord($sprintid)
    {
        try
        {
            if(empty($sprintid))
            {
                throw new \Exception("Missing required sprintid!");
            }
            $sSQL = "SELECT"
                    . " g.id, g.owner_projectid, g.iteration_ct, g.title_tx, "
                    . " g.story_tx, g.start_dt, g.end_dt, g.owner_personid, "
                    . " g.status_cd, g.membership_locked_yn, g.status_set_dt, g.official_score, g.score_body_tx, "
                    . " s.terminal_yn, s.happy_yn, "
                    . " g.active_yn, g.ot_scf, g.otsu, "
                    . " g.updated_dt, g.created_dt, "
                    . " t.tag_tx as oneofmany_tag_tx, "
                    . " p.first_nm, p.last_nm "
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_tag2sprint_tablename." t on g.id=t.sprintid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_status_tablename." s on g.status_cd=s.code"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=g.owner_personid"
                    . " WHERE g.id=$sprintid";
            $sprint_key=NULL;
            $result = db_query($sSQL);
            $prev_key_value = NULL;
            $map_tag2sprint = array();
            $core_record = NULL;
            while($record = $result->fetchAssoc()) 
            {
                $sprint_key = $sprintid;
                if($prev_key_value !== NULL && $sprint_key != $prev_key_value)
                {
                    //Write it out now
                    $core_record['map_tag2sprint'] = $map_tag2sprint;
                    //$themap[$prev_key_value] = $core_record;
                    $core_record = NULL;
                }
                if($core_record == NULL)
                {
                    //Start a new collection
                    $core_record = $record;
                    $core_record['oneofmany_tag_tx'] = NULL;
                }
                $oneofmany_tag_tx = $record['oneofmany_tag_tx'];
                if($oneofmany_tag_tx != NULL)
                {
                    $map_tag2sprint[$oneofmany_tag_tx] = $oneofmany_tag_tx;
                }
                unset($record['oneofmany_tag_tx']);  //Else programmer might think this one value is valid
                $prev_key_value = $sprint_key;
            }            
            if($prev_key_value != NULL)
            {
                //Write out the last one now.
                $core_record['map_tag2sprint'] = $map_tag2sprint;
            }
            return $core_record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return one testcase record
     */
    public function getTestcaseRecord($testcaseid)
    {
        try
        {
            if(empty($testcaseid))
            {
                throw new \Exception("Missing required testcaseid!");
            }
            
            //TODO add in the STEPS!!!!!!
            
            $sSQL = "SELECT"
                    . " g.id, g.owner_projectid, g.testcase_nm, "
                    . " g.blurb_tx, g.owner_personid, "
                    . " g.status_cd, g.status_set_dt, g.importance, "
                    . " s.terminal_yn, s.happy_yn, "
                    . " g.active_yn, "
                    . " g.updated_dt, g.created_dt, "
                    . " t.tag_tx as oneofmany_tag_tx, "
                    . " p.first_nm, p.last_nm "
                    . " FROM ".DatabaseNamesHelper::$m_testcase_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_tag2testcase_tablename." t on g.id=t.testcaseid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_testcase_status_tablename." s on g.status_cd=s.code"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id=g.owner_personid"
                    . " WHERE g.id=$testcaseid";
            $testcase_key=NULL;
            $result = db_query($sSQL);
            $prev_key_value = NULL;
            $map_tag2testcase = array();
            $core_record = NULL;
            while($record = $result->fetchAssoc()) 
            {
                $testcase_key = $testcaseid;
                if($prev_key_value !== NULL && $testcase_key != $prev_key_value)
                {
                    //Write it out now
                    $core_record['map_tag2testcase'] = $map_tag2testcase;
                    //$themap[$prev_key_value] = $core_record;
                    $core_record = NULL;
                }
                if($core_record == NULL)
                {
                    //Start a new collection
                    $core_record = $record;
                    $core_record['oneofmany_tag_tx'] = NULL;
                }
                $oneofmany_tag_tx = $record['oneofmany_tag_tx'];
                if($oneofmany_tag_tx != NULL)
                {
                    $map_tag2testcase[$oneofmany_tag_tx] = $oneofmany_tag_tx;
                }
                unset($record['oneofmany_tag_tx']);  //Else programmer might think this one value is valid
                $prev_key_value = $testcase_key;
            }            
            if($prev_key_value != NULL)
            {
                //Write out the last one now.
                $core_record['map_tag2testcase'] = $map_tag2testcase;
            }
            return $core_record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectPortfoliosByID($order_by_ar=NULL, $only_active=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $themap = [];
            $sSQL1 = "SELECT"
                    . " pp.id, pp.portfolio_nm, pp.purpose_tx, pp.active_yn, pp.owner_personid, pp.updated_dt, pp.created_dt, count(p2p.projectid) as member_count"
                    . " , person.shortname, person.first_nm, person.last_nm "
                    . " FROM " . DatabaseNamesHelper::$m_portfolio_tablename." pp "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_project2portfolio_tablename . " p2p ON pp.id=p2p.portfolioid"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_person_tablename . " person ON pp.owner_personid=person.id";
            if($only_active)
            {
                $sSQL1 .= " WHERE pp.active_yn=1 ";
            }
            $sSQL1 .= " GROUP BY pp.id ";
            if($order_by_ar == NULL)
            {
                $sSQL1 .= " ORDER BY portfolio_nm";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL1 .= " ORDER BY $fields";
            }
            
            $result1 = db_query($sSQL1);
            while($record1 = $result1->fetchAssoc())
            {
                $id = $record1['id'];
                $themap[$id] = $record1;
            }
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectContextsByID($order_by_ar=NULL, $add_unknown=FALSE, $only_active=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $themap = [];
            if($add_unknown)
            {
                $themap[NULL] = array('id'=>NULL,'shortname'=>'Unknown'
                    ,'description_tx'=>NULL,'active_yn'=>NULL
                    ,'updated_dt'=>NULL,'created_dt'=>NULL
                    ); 
            }
            $sSQL1 = "SELECT"
                    . " id, shortname, description_tx, active_yn, updated_dt, created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_project_context_tablename." ";
            if($only_active)
            {
                $sSQL1 .= " WHERE active_yn=1 ";
            }
            if($order_by_ar == NULL)
            {
                $sSQL1 .= " ORDER BY shortname";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL1 .= " ORDER BY $fields";
            }
            
            $sSQL2 = "SELECT project_contextid, count(id) as project_count "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." p";
            if($only_active)
            {
                $sSQL2 .= " WHERE active_yn=1 ";
            }
            $sSQL2 .= " GROUP BY project_contextid ";
            $allcounts = db_query($sSQL2)->fetchAllAssoc('project_contextid');
            $result1 = db_query($sSQL1);
            while($record1 = $result1->fetchAssoc())
            {
                $id = $record1['id'];
                if(!isset($allcounts[$id]))
                {
                    $record1['project_count'] = 0;
                } else {
                    $record1['project_count'] = $allcounts[$id]->project_count;
                }
                $themap[$id] = $record1;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getActionStatusByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, terminal_yn, happy_yn, sort_position, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_action_status_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, terminal_yn, happy_yn, sort_position "
                        . " FROM ".DatabaseNamesHelper::$m_action_status_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, terminal_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_status_state'] = $this->getWordyStatusState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getWorkitemStatusByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, needstesting_yn, default_ignore_effort_yn"
                        . ", sort_position, ot_scf, otsu, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_workitem_status_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, needstesting_yn, default_ignore_effort_yn"
                        . ", sort_position, ot_scf, otsu "
                        . " FROM ".DatabaseNamesHelper::$m_workitem_status_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, terminal_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_status_state'] = $this->getWordyStatusState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    public function getUseCaseStatusByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, soft_delete_yn"
                        . ", sort_position, ot_scf, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_usecase_status_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, soft_delete_yn"
                        . ", sort_position, ot_scf "
                        . " FROM ".DatabaseNamesHelper::$m_usecase_status_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, terminal_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_status_state'] = $this->getWordyStatusState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    public function getTestCaseStatusByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, terminal_yn, happy_yn, soft_delete_yn"
                        . ", sort_position, ot_scf, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_testcase_status_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, terminal_yn, happy_yn, soft_delete_yn"
                        . ", sort_position, ot_scf "
                        . " FROM ".DatabaseNamesHelper::$m_testcase_status_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, terminal_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_status_state'] = $this->getWordyStatusState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    public function getSprintStatusByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, sort_position, ot_scf, otsu, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_sprint_status_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, workstarted_yn, terminal_yn, happy_yn, sort_position, ot_scf, otsu "
                        . " FROM ".DatabaseNamesHelper::$m_sprint_status_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, terminal_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_status_state'] = $this->getWordyStatusState($record);
                $themap[$id] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getWordyStatusState($record)
    {
        $title_tx = $record['title_tx'];
        $happy_yn = trim($record['happy_yn']);
        if($record['terminal_yn'] == 1)
        {
            if($happy_yn == '1')
            {
                $wordy_status_state = "{$title_tx} (happy terminal state)";
            } else if($happy_yn == '0') {
                $wordy_status_state = "{$title_tx} (unhappy terminal state)";
            } else {
                $wordy_status_state = "{$title_tx} (terminal state)";
            }
        } else {
            $wordy_status_state = $title_tx;
        }
        return $wordy_status_state;
    }

    private function getWordyConditionState($record)
    {
        $title_tx = $record['title_tx'];
        $happy_yn = trim($record['happy_yn']);
        if($happy_yn == '1')
        {
            $wordy_condition_state = "{$title_tx} (happy state)";
        } else if($happy_yn == '0') {
            $wordy_condition_state = "{$title_tx} (unhappy state)";
        } else {
            $wordy_condition_state = $title_tx;
        }
        return $wordy_condition_state;
    }

    public function getEquipmentConditionByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, happy_yn, sort_position, ot_scf, ob_scf, otsu, obsu, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_equipment_condition_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, happy_yn, sort_position, ot_scf, ob_scf, otsu, obsu "
                        . " FROM ".DatabaseNamesHelper::$m_equipment_condition_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, happy_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_condition_state'] = $this->getWordyConditionState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    public function getExternalResourceConditionByCode($include_description_tx=FALSE)
    {
        try
        {
            //Get all the relevant select options
            $themap = array();
            if($include_description_tx)
            {
                $sSQL = "SELECT"
                        . " code, title_tx, happy_yn, sort_position, ot_scf, ob_scf, otsu, obsu, description_tx "
                        . " FROM ".DatabaseNamesHelper::$m_external_resource_condition_tablename." ";
            } else {
                $sSQL = "SELECT"
                        . " code, title_tx, happy_yn, sort_position, ot_scf, ob_scf, otsu, obsu  "
                        . " FROM ".DatabaseNamesHelper::$m_external_resource_condition_tablename." ";
            }
            $sSQL .= " ORDER BY sort_position, happy_yn";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['code'];
                $record['wordy_condition_state'] = $this->getWordyConditionState($record);
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    /**
     * Return a rich map of external resource detail with ID as the key
     */
    public function getExternalResourceByID($externalresourceid=NULL,$only_active=FALSE)
    {
        try
        {
            $order_by_ar = NULL;
            $themap = array();
            $sSQL = "SELECT"
                    . " e.id, e.shortname, e.name, e.description_tx, "
                    . " e.primary_locationid, e.ot_scf, e.ob_scf, e.condition_cd, e.condition_set_dt, "
                    . " loc.shortname as location_shortname, loc.address_line1, loc.address_line2, loc.city_tx, "
                    . " s.abbr as state_abbr, c.abbr as country_abbr, "
                    . " e.active_yn, e.updated_dt, e.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_external_resource_tablename." e "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_location_tablename." loc on e.primary_locationid = loc.id "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_state_tablename." s on loc.stateid = s.id "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_country_tablename." c on loc.countryid = c.id ";
            if(!empty($externalresourceid))
            {
                //Get just one record
                $sSQL .= " WHERE e.id=$externalresourceid";
                $result = db_query($sSQL);
                $themap = $result->fetchAssoc();
            } else {
                //Get all the records
                if($order_by_ar == NULL)
                {
                    $sSQL .= " ORDER BY e.shortname";
                } else {
                    $fields = implode(',', $order_by_ar);
                    $sSQL .= " ORDER BY $fields";
                }
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $themap[$id] = $record; 
                    }
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of equipment detail with ID as the key
     */
    public function getEquipmentByID($equipmentid=NULL, $only_active = FALSE)
    {
        try
        {
            $order_by_ar = NULL;
            $themap = array();
            $sSQL = "SELECT"
                    . " e.id, e.shortname, e.name, e.description_tx, "
                    . " e.primary_locationid, e.ot_scf, e.ob_scf, e.condition_cd, e.condition_set_dt, "
                    . " loc.shortname as location_shortname, loc.address_line1, loc.address_line2, loc.city_tx, "
                    . " s.abbr as state_abbr, c.abbr as country_abbr, "
                    . " e.active_yn, e.updated_dt, e.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_equipment_tablename." e "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_location_tablename." loc on e.primary_locationid = loc.id "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_state_tablename." s on loc.stateid = s.id "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_country_tablename." c on loc.countryid = c.id ";
            if(!empty($equipmentid))
            {
                //Return only one record
                $sSQL .= " WHERE e.id=$equipmentid";
                $result = db_query($sSQL);
                $themap = $result->fetchAssoc();
            } else {
                //Return all records
                if($order_by_ar == NULL)
                {
                    $sSQL .= " ORDER BY e.shortname";
                } else {
                    $fields = implode(',', $order_by_ar);
                    $sSQL .= " ORDER BY $fields";
                }
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $themap[$id] = $record; 
                    }
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of location detail with ID as the key
     */
    public function getLocationsByID($locationid=NULL)
    {
        try
        {
            $order_by_ar = NULL;
            $only_active = FALSE;
            $themap = array();
            $sSQL = "SELECT"
                    . " e.id, e.shortname, e.description_tx, "
                    . " e.address_line1, e.address_line2, e.countryid, e.stateid, e.city_tx, "
                    . " c.name as country_name, c.abbr as country_abbr, "
                    . " s.name as state_name, s.abbr as state_abbr, "
                    . " e.active_yn, e.updated_dt, e.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_location_tablename." e "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_country_tablename." c on c.id = e.countryid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_state_tablename." s on s.id = e.stateid ";
            if(!empty($locationid))
            {
                $sSQL .= " WHERE e.id=$locationid ";
                $result = db_query($sSQL);
                $themap = $result->fetchAssoc();
            } else {
                if($order_by_ar == NULL)
                {
                    $sSQL .= " ORDER BY e.shortname";
                } else {
                    $fields = implode(',', $order_by_ar);
                    $sSQL .= " ORDER BY $fields";
                }
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $themap[$id] = $record; 
                    }
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of state detail with ID as the key
     */
    public function getAmericanStatesByID($stateid=NULL)
    {
        try
        {
            $order_by_ar = NULL;
            $only_active = FALSE;
            $themap = array();
            $sSQL = "SELECT"
                    . " s.id, "
                    . " s.name, s.abbr, "
                    . " s.active_yn, s.updated_dt, s.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_state_tablename." s ";
            if(!empty($stateid))
            {
                $sSQL .= " WHERE s.id=$stateid ";
                $result = db_query($sSQL);
                $themap = $result->fetchAssoc();
            } else {
                if($order_by_ar == NULL)
                {
                    $sSQL .= " ORDER BY s.name";
                } else {
                    $fields = implode(',', $order_by_ar);
                    $sSQL .= " ORDER BY $fields";
                }
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $themap[$id] = $record; 
                    }
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getCountriesByID($countryid=NULL)
    {
        try
        {
            $order_by_ar = NULL;
            $only_active = FALSE;
            $themap = array();
            $sSQL = "SELECT"
                    . " s.id, "
                    . " s.name, s.abbr, "
                    . " s.active_yn, s.updated_dt, s.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_country_tablename." s ";
            if(!empty($countryid))
            {
                $sSQL .= " WHERE s.id=$countryid ";
                $result = db_query($sSQL);
                $themap = $result->fetchAssoc();
            } else {
                if($order_by_ar == NULL)
                {
                    $sSQL .= " ORDER BY s.name";
                } else {
                    $fields = implode(',', $order_by_ar);
                    $sSQL .= " ORDER BY $fields";
                }
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $themap[$id] = $record; 
                    }
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a list of child IDs
     */
    public function getChildWIDSofWorkitemsByID($parent_wids)
    {
        try
        {
            $parent_goalid_list = implode(',', $parent_wids);
            $themap = array();
            $sSQL = "SELECT"
                    . " antwiid as id"
                    . " FROM ".DatabaseNamesHelper::$m_map_wi2wi_tablename." gg ";
            if(is_array($parent_wids) && count($parent_wids) > 0)
            {
                $sSQL .= " WHERE depwiid in ($parent_goalid_list)";
            }
            $sSQL .= " ORDER BY antwiid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                    $id = $record['id'];
                    $themap[$id] = $id; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    
    /**
     * Return the workitems that are not already in a sprint
     */
    public function getSprintNonMemberWorkitemsByWID($projectid, $allowedsprintid=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT w.*"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename." w2s on w.id=w2s.workitemid ";
            if(!empty($allowedsprintid))
            {
                $sSQL .= " WHERE w.active_yn=1 and w.owner_projectid=$projectid and (w2s.workitemid IS NULL or w2s.sprintid=$allowedsprintid)";
            } else {
                $sSQL .= " WHERE w.active_yn=1 and w.owner_projectid=$projectid and w2s.workitemid IS NULL";
            }
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
    
    public function getParentGoalOptions($projectid, $active_yn_filter=1, $skip_this_goalid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $parent_options = array();
            $where_ar = array("g.workitem_basetype='G'");
            if(!empty($projectid))
            {
                $where_ar[] = "owner_projectid=$projectid";
            }
            if($active_yn_filter !== NULL)
            {
                $where_ar[] = "active_yn=$active_yn_filter"; 
            }
            if($skip_this_goalid !== NULL)
            {
                $where_ar[] = "id != $skip_this_goalid"; 
            }
            if(count($where_ar) > 0)
            {
                $where = "WHERE " . implode(' and ', $where_ar);   
            } else {
                $where = "";
            }
            $sSQL = "SELECT"
                    . " id, workitem_nm, active_yn, status_cd, externally_billable_yn, client_deliverable_yn "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " $where";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $parent_options[$id] = $record; 
            }            
            return $parent_options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return list of tasks that are candidates for task dependency
     */
    public function getParentTaskOptions($projectid=NULL, $active_yn_filter=1, $skip_this_taskid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $parent_options = array();
            $where_ar = array("g.workitem_basetype='T'");
            if(!empty($projectid))
            {
                $where_ar[] = "owner_projectid=$projectid";
            }
            if(!empty($active_yn_filter))
            {
                $where_ar[] = "active_yn=$active_yn_filter"; 
            }
            if(!empty($skip_this_taskid))
            {
                $where_ar[] = "id != $skip_this_taskid"; 
            }
            if(count($where_ar) > 0)
            {
                $where = "WHERE " . implode(' and ', $where_ar);   
            } else {
                $where = "";
            }
            $sSQL = "SELECT"
                    . " id, workitem_nm, active_yn, status_cd "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " $where";
            
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $parent_options[$id] = $record; 
            }            
            return $parent_options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just the list of IDs.
     */
    public function getIDListOfPeopleInProject($projectid=NULL, $only_work_owners=FALSE)
    {
        try
        {
            $themap = [];
            if($projectid===NULL)
            {
                throw new \Exception("You must declare a project id!");
            }
            
            //Get the project manager first
            $sSQL_PM = "SELECT"
                    . " owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." ";
            $sSQL_PM .= " WHERE id=$projectid";
            $result_pm = db_query($sSQL_PM);
            $record_pm = $result_pm->fetchAssoc();
            $pm_id = $record_pm['owner_personid'];
            $themap[$pm_id] = $pm_id;   //The owner is always a member!

            //Get all the people owning member workitems
            $sSQL = "SELECT distinct"
                    . " owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w";
            $sSQL .= " WHERE w.active_yn=1 and owner_projectid=$projectid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['owner_personid'];
                $themap[$id] = $id; 
            }            

            if(!$only_work_owners)
            {
                $allgroupsinproject = $this->getGroupsInProjectByID();
                if(array_key_exists(1, $allgroupsinproject))
                {
                    //We have the special everyone group, get all current people.
                    $sSQL = "SELECT "
                            . " id"
                            . " FROM ".DatabaseNamesHelper::$m_person_tablename ." p";
                    $sSQL .= " WHERE active_yn=1";
                    $pcg_result = db_query($sSQL);
                    while($record = $pcg_result->fetchAssoc()) 
                    {
                        $personid = $record['id'];
                        $themap[$personid] = $personid;
                    }            
                }

                //Get all the people in normal member groups
                $sSQL = "SELECT distinct"
                        . " prg.personid"
                        . " FROM ".DatabaseNamesHelper::$m_map_group2project_tablename." grp2prj"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." prg on grp2prj.groupid=prg.groupid";
                $sSQL .= " WHERE grp2prj.projectid=$projectid and NOT prg.personid IS NULL";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['personid'];
                    $themap[$id] = $id; 
                }            

                //Get all the people in the preferred collaborators group
                $sSQL = "SELECT distinct"
                        . " personid"
                        . " FROM ".DatabaseNamesHelper::$m_map_person2pcg_in_project_tablename ." w";
                $sSQL .= " WHERE projectid=$projectid";
                $pcg_result = db_query($sSQL);
                while($record = $pcg_result->fetchAssoc()) 
                {
                    $personid = $record['personid'];
                    $themap[$personid] = $personid;
                }            
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just the list of IDs.
     */
    public function getIDListOfPeopleInTP($template_projectid=NULL, $only_work_owners=FALSE)
    {
        try
        {
            $themap = [];
            if($template_projectid===NULL)
            {
                throw new \Exception("You must declare a template project id!");
            }
            
            //Get the project manager first
            $sSQL_PM = "SELECT"
                    . " owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." ";
            $sSQL_PM .= " WHERE id=$template_projectid";
            $result_pm = db_query($sSQL_PM);
            $record_pm = $result_pm->fetchAssoc();
            $pm_id = $record_pm['owner_personid'];
            $themap[$pm_id] = $pm_id;   //The owner is always a member!

            //Get all the people owning member workitems
            $sSQL = "SELECT distinct"
                    . " owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_template_workitem_tablename." w";
            $sSQL .= " WHERE owner_template_projectid=$template_projectid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['owner_personid'];
                $themap[$id] = $id; 
            }            

            if(!$only_work_owners)
            {
                $allgroupsinproject = $this->getGroupsInTPByID();
                if(array_key_exists(1, $allgroupsinproject))
                {
                    //We have the special everyone group, get all current people.
                    $sSQL = "SELECT "
                            . " id"
                            . " FROM ".DatabaseNamesHelper::$m_person_tablename ." p";
                    $sSQL .= " WHERE active_yn=1";
                    $pcg_result = db_query($sSQL);
                    while($record = $pcg_result->fetchAssoc()) 
                    {
                        $personid = $record['id'];
                        $themap[$personid] = $personid;
                    }            
                }

                //Get all the people in normal member groups
                $sSQL = "SELECT distinct"
                        . " prg.personid"
                        . " FROM ".DatabaseNamesHelper::$m_map_group2tp_tablename." grp2prj"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." prg on grp2prj.groupid=prg.groupid";
                $sSQL .= " WHERE grp2prj.template_projectid=$template_projectid and NOT prg.personid IS NULL";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['personid'];
                    $themap[$id] = $id; 
                }            
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just people associated with a project
     * Ignores completed workitems
     */
    public function getPeopleInProjectBundle($projectid=NULL)
    {
        try
        {
            $themap = array();
            if($projectid===NULL)
            {
                throw new \Exception("You must declare a project id!");
            }
            $personid_ar = [];
            
            //Get the project manager first
            $sSQL_PM = "SELECT"
                    . " owner_personid, root_goalid"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." ";
            $sSQL_PM .= " WHERE id=$projectid";
            $result_pm = db_query($sSQL_PM);
            $record_pm = $result_pm->fetchAssoc();
            $pm_id = $record_pm['owner_personid'];
            $root_goalid = $record_pm['root_goalid'];
            $themap['project']['root_goalid'] = $root_goalid;
            $themap['project']['owner_personid'] = $pm_id;
            $themap['project']['delegate_owners'] = [];
            
            //Get all the people in member groups
            $themap['in_relevant_groups'] = [];
            $sSQL = "SELECT distinct"
                    . " prg.personid, prg.groupid"
                    . " FROM " . DatabaseNamesHelper::$m_map_group2project_tablename . " grp2prj"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_person2role_in_group_tablename . " prg on grp2prj.groupid=prg.groupid";
            $sSQL .= " WHERE grp2prj.projectid=$projectid and NOT prg.personid IS NULL";
            $grp2prj_result = db_query($sSQL);
            while($record = $grp2prj_result->fetchAssoc()) 
            {
                $id = $record['personid'];
                $personid_ar[$id] = $id;
                $groupid = $record['groupid'];
                if(!isset($themap['in_relevant_groups'][$id]))
                {
                    $themap['in_relevant_groups'][$id] = []; 
                }
                $themap['in_relevant_groups'][$id][] = $groupid;
            }            

            //Get all the people in the preferred collaborators group
            $themap['in_pcg'] = [];
            $sSQL = "SELECT distinct"
                    . " personid"
                    . " FROM ".DatabaseNamesHelper::$m_map_person2pcg_in_project_tablename ." w";
            $sSQL .= " WHERE projectid=$projectid";
            $pcg_result = db_query($sSQL);
            while($record = $pcg_result->fetchAssoc()) 
            {
                $personid = $record['personid'];
                $personid_ar[$personid] = $personid;
                $themap['in_pcg'][$personid] = $personid;
            }            
            
            //Get all the people owning member workitems
            $themap['workitems']['own'] = [];
            $sSQLw1 = "SELECT distinct"
                    . " w.owner_projectid as projectid, w.id as wid, "
                    . " d2w.personid as owner_personid, 1 as is_delegate_owner "
                    . " FROM ".DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename." d2w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." w on d2w.workitemid=w.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." ws on w.status_cd=ws.code";
            $sSQLw1 .= " WHERE owner_projectid=$projectid and ws.terminal_yn!=1 and w.active_yn=1";
            $sSQLw2 = "SELECT distinct"
                    . " w.owner_projectid as projectid, w.id as wid, w.owner_personid as owner_personid, 0 as is_delegate_owner "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." ws on w.status_cd=ws.code";
            $sSQLw2 .= " WHERE owner_projectid=$projectid and ws.terminal_yn!=1 and w.active_yn=1";
            $wi_result = db_query("$sSQLw1 UNION $sSQLw2");
//DebugHelper::showNeatMarkup($sSQLw1)    ;        
            while($record = $wi_result->fetchAssoc()) 
            {
                $woperson_id = $record['owner_personid'];
                $is_delegate_owner = $record['is_delegate_owner'];
                $personid_ar[$woperson_id] = $woperson_id;
                //$delegate_owner_personid = $record['delegate_owner_personid'];
                $wid = $record['wid'];
                if(!isset($themap['workitems']['own'][$woperson_id]))
                {
                    $themap['workitems']['own'][$woperson_id] = []; 
                    $themap['workitems']['delegate_own'][$woperson_id] = []; 
                }
                if($is_delegate_owner)
                {
                    $themap['workitems']['delegate_own'][$woperson_id][] = $wid;
                } else {
                    $themap['workitems']['own'][$woperson_id][] = $wid;
                }
                /*
                if(!empty($delegate_owner_personid))
                {
                    $personid_ar[$delegate_owner_personid] = $delegate_owner_personid;
                    if(!isset($themap['workitems']['own'][$delegate_owner_personid]))
                    {
                        $themap['workitems']['own'][$delegate_owner_personid] = []; 
                    }
                    $themap['workitems']['own'][$delegate_owner_personid][] = $wid;
                    if($pm_id == $wid)
                    {
                        $themap['project']['delegate_owners'][$delegate_owner_personid] = $delegate_owner_personid;    
                    }
                }
                */
            }            
            
            $allgroupsinproject = $this->getGroupsInProjectByID();
            if(array_key_exists(1, $allgroupsinproject))
            {
                //We have the special everyone group, get all current people.
                $sSQL = "SELECT "
                        . " id"
                        . " FROM ".DatabaseNamesHelper::$m_person_tablename ." p";
                $sSQL .= " WHERE active_yn=1";
                $pcg_result = db_query($sSQL);
                while($record = $pcg_result->fetchAssoc()) 
                {
                    $personid = $record['id'];
                    $personid_ar[$personid] = $personid;
                }            
            }
            $themap['lookup']['groups'] = $allgroupsinproject;
            $themap['lookup']['people'] = $this->getPeopleDetailData($personid_ar);
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return just the map where key is the projectid and value is connection types
     */
    public function getProjectsConnectedToPersonMap($personid=NULL, $include_workitemids=FALSE)
    {
        try
        {
            if(empty($personid))
            {
                throw new Exception("Missing required personid!");
            }
            $themap = [];
            
            //Get all the people on member groups
            $sSQL = "SELECT p.id as pid, p2.owner_personid as project_owner_personid, prg.personid as prg_personid "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." p"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_project_tablename." p2 on p.id=p2.id and p2.owner_personid=$personid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_group2project_tablename." grp2prj on p.id=grp2prj.projectid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." prg on grp2prj.groupid=prg.groupid and prg.personid=$personid";

            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $pid = $record['pid'];
                $project_owner_personid = $record['project_owner_personid'];
                $prg_personid = $record['prg_personid'];
                $types = [];
                if(!empty($project_owner_personid))
                {
                    $types[] = 'project owner';
                }
                if(!empty($prg_personid))
                {
                    $types[] = 'group member';
                }
                if(count($types)>0)
                {
                    $themap[$pid] = $types;
                }
            }            
            
            //Get all the people owning member workitems
            $sSQLw = "SELECT distinct"
                    . " w.owner_projectid as projectid, w.id as wid, w.owner_personid as woperson_id, d2w.personid as delegate_owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename." d2w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." w on d2w.workitemid=w.id";
            $sSQLw .= " WHERE w.owner_personid=$personid OR d2w.personid=$personid AND w.active_yn=1";

            $wresult = db_query($sSQLw);
            while($record = $wresult->fetchAssoc()) 
            {
                $pid = $record['projectid'];
                $wid = $record['wid'];
                $owner = $record['woperson_id'];
                if($owner == $personid)
                {
                    if($include_workitemids)
                    {
                        $themap[$pid]["workitem_owner"][] = $wid;
                    } else {
                        $themap[$pid]["workitem_owner"] = "YES";
                    }
                }
                $delegate_owner_personid = $record['delegate_owner_personid'];
                if($delegate_owner_personid == $personid)
                {
                    if($include_workitemids)
                    {
                        $themap[$pid]["workitem_delegate"][] = $wid;
                    } else {
                        $themap[$pid]["workitem_delegate"] = "YES";
                    }
                }
            }            
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return simple map of template workitems in a project
     */
    public function getIDMapOfWorkitemsInProject($projectid, $owner_personid=NULL, $terminal_yn=NULL, $active_yn=1)
    {
        try
        {
            
            $themap = [];
            $deliverablewids = [];
            $billablewids = [];
            $widsbytype = [];
            $justwlist = [];
            if(empty($projectid))
            {
                throw new \Exception("You must declare a project id!");
            }

            $sCoreWhere = " WHERE owner_projectid=$projectid";
            if(!empty($active_yn))
            {
                $sCoreWhere .= " and active_yn=$active_yn";    
            }
            if(!empty($owner_personid))
            {
                $sCoreWhere .= " and owner_personid=$owner_personid";    
            }
            if(!empty($terminal_yn))
            {
                $sCoreWhere .= " and terminal_yn=$terminal_yn";    
            }
            $sCoreSQL = "SELECT id, workitem_basetype, owner_projectid, active_yn, owner_personid, terminal_yn"
                    . " , externally_billable_yn, client_deliverable_yn, planned_fte_count "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." gs on gs.code=w.status_cd"
                    . " $sCoreWhere";
    
            //Get all the people owning member workitems
            $result = db_query($sCoreSQL);
            while($record = $result->fetchAssoc()) 
            {
                $wid = $record['id'];
                $client_deliverable_yn = $record['client_deliverable_yn'];
                if($client_deliverable_yn === '1')
                {
                    $deliverablewids[$wid] = $wid;
                }
                $externally_billable_yn = $record['externally_billable_yn'];
                if($externally_billable_yn === '1')
                {
                    $billablewids[$wid] = $wid;
                }
                $wbt = $record['workitem_basetype'];
                $widsbytype[$wbt][$wid] = $wid; 
                $justwlist[$wid] = $wid;
            }            
            
            //Now get the leaf workitems that are the root of other projects.
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $justwlist_chunks = array_chunk($justwlist, $MAX_INCLAUSE_CHUNK_SIZE);
            foreach($justwlist_chunks as $onechunk)
            {
                $sIN = "id IN (".implode(',',$onechunk).')';
                $sSQL = $sCoreSQL . " and $sIN";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $wid = $record['id'];
                    $client_deliverable_yn = $record['client_deliverable_yn'];
                    if($client_deliverable_yn === '1')
                    {
                        $deliverablewids[$wid] = $wid;
                    }
                    $externally_billable_yn = $record['externally_billable_yn'];
                    if($externally_billable_yn === '1')
                    {
                        $billablewids[$wid] = $wid;
                    }
                    $wbt = $record['workitem_basetype'];
                    $widsbytype[$wbt][$wid] = $wid; 
                }            
            }   
            $themap['deliverablewids'] = $deliverablewids;
            $themap['billablewids'] = $billablewids;
            $themap['widsbytype'] = $widsbytype;
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return simple map of template workitems in a project
     */
    public function getIDMapOfTWsInTP($tpid, $owner_personid=NULL)
    {
        try
        {
            
            $themap = [];
            $deliverabletwids = [];
            $billablewids = [];
            $twidsbytype = [];
            $justwlist = [];
            
            if(empty($tpid))
            {
                throw new \Exception("You must declare a template project id!");
            }

            $sCoreWhere = " WHERE owner_template_projectid=$tpid";
            
            if(!empty($owner_personid))
            {
                $sCoreWhere .= " and owner_personid=$owner_personid";    
            }
            $sCoreSQL = "SELECT id, workitem_basetype, owner_template_projectid, owner_personid"
                    . " , externally_billable_yn, client_deliverable_yn, planned_fte_count "
                    . " FROM ".DatabaseNamesHelper::$m_template_workitem_tablename." w"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." gs on gs.code=w.status_cd"
                    . " $sCoreWhere";
    
            //Get all the people owning member workitems
            $result = db_query($sCoreSQL);
            while($record = $result->fetchAssoc()) 
            {
                $twid = $record['id'];
                $client_deliverable_yn = $record['client_deliverable_yn'];
                if($client_deliverable_yn === '1')
                {
                    $deliverabletwids[$twid] = $twid;
                }
                $externally_billable_yn = $record['externally_billable_yn'];
                if($externally_billable_yn === '1')
                {
                    $billablewids[$twid] = $twid;
                }
                $wbt = $record['workitem_basetype'];
                $twidsbytype[$wbt][$twid] = $twid; 
                $justwlist[$twid] = $twid;
            }            
            
            //Now get the leaf workitems that are the root of other projects.
            $MAX_INCLAUSE_CHUNK_SIZE = 100;
            $justwlist_chunks = array_chunk($justwlist, $MAX_INCLAUSE_CHUNK_SIZE);
            foreach($justwlist_chunks as $onechunk)
            {
                $sIN = "id IN (".implode(',',$onechunk).')';
                $sSQL = $sCoreSQL . " and $sIN";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $twid = $record['id'];
                    $client_deliverable_yn = $record['client_deliverable_yn'];
                    if($client_deliverable_yn === '1')
                    {
                        $deliverabletwids[$twid] = $twid;
                    }
                    $externally_billable_yn = $record['externally_billable_yn'];
                    if($externally_billable_yn === '1')
                    {
                        $billablewids[$twid] = $twid;
                    }
                    $wbt = $record['workitem_basetype'];
                    $twidsbytype[$wbt][$twid] = $twid; 
                }            
            }   
            $themap['deliverabletwids'] = $deliverabletwids;
            $themap['billabletwids'] = $billablewids;
            $themap['twidsbytype'] = $twidsbytype;
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return just the list of IDs.
     */
    public function getIDListOfGroupsInProject($projectid=NULL)
    {
        try
        {
            if($projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "grp2prj.projectid=$projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " grp2prj.groupid"
                    . " FROM ".DatabaseNamesHelper::$m_map_group2project_tablename." grp2prj";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY grp2prj.groupid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                $themap[$id] = $id; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just the list of IDs.
     */
    public function getIDListOfGroupsInTP($template_projectid=NULL)
    {
        try
        {
            if($template_projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "grp2prj.template_projectid=$template_projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " grp2prj.groupid"
                    . " FROM ".DatabaseNamesHelper::$m_map_group2tp_tablename." grp2prj";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY grp2prj.groupid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                $themap[$id] = $id; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just the list of IDs.
     */
    public function getIDListOfRolesInProject($projectid=NULL)
    {
        try
        {
            if($projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "r2prj.projectid=$projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " r2prj.roleid"
                    . " FROM ".DatabaseNamesHelper::$m_map_prole2project_tablename." r2prj";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY r2prj.roleid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['roleid'];
                $themap[$id] = $id; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of groups detail with ID as the key for all in the project
     * Does NOT include groups from sub-projects
     */
    public function getGroupsInProjectByID($projectid=NULL)
    {
        try
        {
            if($projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "grp2prj.projectid=$projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " grp2prj.groupid, g.group_nm,"
                    . " g.purpose_tx, g.active_yn, g.leader_personid,"
                    . " g.updated_dt, g.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_map_group2project_tablename." grp2prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_group_tablename." g on g.id=grp2prj.groupid";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY grp2prj.groupid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of groups detail with ID as the key for all in the project
     * Does NOT include groups from sub-projects
     */
    public function getGroupsInTPByID($template_projectid=NULL)
    {
        try
        {
            if($template_projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "grp2prj.template_projectid=$template_projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " grp2prj.groupid, g.group_nm,"
                    . " g.purpose_tx, g.active_yn, g.leader_personid,"
                    . " g.updated_dt, g.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_map_group2tp_tablename." grp2prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_group_tablename." g on g.id=grp2prj.groupid";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY grp2prj.groupid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                $themap[$id] = $record; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a simple map bundle 
     */
    public function getRolesAndGroupsBundle4Person($personid=NULL)
    {
        try
        {
            if($personid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "person2ring.personid=$personid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " person2ring.groupid, person2ring.roleid, g.group_nm, r.role_nm, "
                    . " g.purpose_tx, g.active_yn, g.leader_personid,"
                    . " g.updated_dt, g.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." person2ring"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_group_tablename." g on g.id=person2ring.groupid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_role_tablename." r on r.id=person2ring.roleid";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY person2ring.groupid, person2ring.roleid";
            $result = db_query($sSQL);
            $groups = [];
            $roles = [];
            
            //Everyone is in the everyone group
            $groups[1]['name'] = "Everyone";
            $groups[1]['roles'][UserAccountHelper::$PROJECTROLEID_ITEM_OWNER] = "Item Owner";
            $groups[UserAccountHelper::$PROJECTROLEID_ITEM_OWNER]['groups'][1] = "Everyone";
                    
            while($record = $result->fetchAssoc()) 
            {
                $groupid = $record['groupid'];
                $group_nm = $record['group_nm'];
                $roleid = $record['roleid'];
                $role_nm = $record['role_nm'];
                if(empty($role_nm))
                {
                    $role_nm = "role#" . $roleid;   //bad data causes this!
                }
                $leader_personid = $record['leader_personid'];
                if(!array_key_exists($groupid, $groups))
                {
                    $groups[$groupid] = [];   
                    $groups[$groupid]['name'] = $group_nm;   
                }
                if(!array_key_exists($roleid, $roles))
                {
                    $roles[$roleid] = [];
                    $roles[$roleid]['name'] = $role_nm;
                }
                $groups[$groupid]['roles'][$roleid] = $role_nm; 
                $roles[$roleid]['groups'][$groupid] = $group_nm;
                if($personid == $leader_personid)
                {
                    $groups[$groupid]['roles'][$roleid] = "Leader"; 
                    $roles[$roleid]['groups'][$groupid] = $group_nm;
                }
            }
            
            $themap['roles'] = $roles;
            $themap['groups'] = $groups;
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of groups detail with ID as the key
     * Does NOT include groups from sub-projects
     */
    public function getGroupsInProjectBundle($projectid=NULL, $include_pcg=FALSE)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $bundle = [];
            $themap = [];
            $sSQL = "SELECT"
                    . " g.id as groupid, g.group_nm,"
                    . " g.purpose_tx, g.active_yn, "
                    . " g.leader_personid, p.first_nm as leader_first_nm, p.last_nm as leader_last_nm, p.shortname as leader_shortname, "
                    . " g.updated_dt, g.created_dt, grp2prj.created_by_personid as assignedby_personid, grp2prj.created_dt as mapped_dt "
                    . " FROM ".DatabaseNamesHelper::$m_group_tablename." g "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename ." p on p.id=g.leader_personid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_group2project_tablename." grp2prj on g.id=grp2prj.groupid";
            $sSQL .= " ORDER BY grp2prj.groupid";
            $result = db_query($sSQL);
            $members = [];
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                if($include_pcg || $id != 10)
                {
                    $record['leader_fullname'] = $record['leader_first_nm'] . " " . $record['leader_last_nm'];
                    $themap[$id] = $record;
                    if(!empty($record['mapped_dt']))
                    {
                        $members[$id] = $id;
                    }
                }
            }            
            $bundle['members'] = $members;
            $bundle['detail'] = $themap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return just the list of IDs.
     */
    public function getIDListOfRolesInTP($template_projectid=NULL)
    {
        try
        {
            if($template_projectid===NULL)
            {
                $filter_criteria = NULL;
            } else {
                $filter_criteria = "r2prj.template_projectid=$template_projectid";
            }
            
            $themap = array();
            $sSQL = "SELECT"
                    . " r2prj.roleid"
                    . " FROM ".DatabaseNamesHelper::$m_map_prole2tp_tablename." r2prj";
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY r2prj.roleid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['roleid'];
                $themap[$id] = $id; 
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of vision statement detail with ID as the key
     */
    public function getVisionStatementsByID()
    {
        try
        {
            return $this->getVisionStatementsByIDComplex(NULL);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of groups detail with ID as the key for all in the template
     */
    public function getGroupsInTemplateByID($templateid=NULL)
    {
        try
        {
            return $this->getGroupsInProjectByID($templateid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return the list of people in the group
     */
    public function getGroupMembers($groupid)
    {
        try
        {
            $thelist = [];
            
            $sSQL_members = "SELECT"
                    . " p2rg.personid, p2rg.roleid, "
                    . " p.shortname, p.first_nm, p.last_nm, "
                    . " r.role_nm "
                    . " FROM ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." p2rg "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." p on p.id = p2rg.personid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_role_tablename." r on r.id = p2rg.roleid "
                    . " WHERE p2rg.groupid=$groupid";
                    
            $result_members = db_query($sSQL_members);
            while($record_members = $result_members->fetchAssoc())
            {
                $thelist[] = $record_members;
            }

            return $thelist;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of groups detail with ID as the key
     */
    public function getGroupsByIDCustomOrder($order_by_ar=NULL)
    {
        try
        {
            return $this->getGroupsByIDComplex($order_by_ar, FALSE);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of groups detail with ID as the key
     */
    public function getGroupsByID($include_dpcg=FALSE)
    {
        try
        {
            return $this->getGroupsByIDComplex(NULL, $include_dpcg);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of groups detail with ID as the key
     */
    private function getGroupsByIDComplex($order_by_ar=NULL, $include_dpcg=TRUE)
    {
        try
        {
            //Get count of all people
            $everyonecount = db_query("SELECT COUNT(1) AS count FROM " 
                    . DatabaseNamesHelper::$m_person_tablename 
                    . " WHERE active_yn = 1")->fetchField();
            
            //Get all the counts
            $non_leader_membership_count = [];
            $sSQL_members_counts = "SELECT count(p2rg.personid) as role_count, "
                    . " p2rg.groupid, p2rg.personid "
                    . " FROM ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." p2rg "
                    . " GROUP BY p2rg.groupid, p2rg.personid";
            $result_members_counts = db_query($sSQL_members_counts);
            $prev_id = NULL;
            while($record_members_counts = $result_members_counts->fetchAssoc())
            {
                $id = $record_members_counts['groupid'];
                if($prev_id == $id)
                {
                    $countsofar = $non_leader_membership_count[$id];
                    $non_leader_membership_count[$id] = $countsofar + 1;
                } else {
                    $non_leader_membership_count[$id] = 1;
                }
                $prev_id = $id;
            }

            //Get all the groups
            $themap = array();
            $sSQL = "SELECT"
                    . " g.id, g.group_nm,"
                    . " g.purpose_tx, g.active_yn, g.leader_personid,"
                    . " g.updated_dt, g.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_group_tablename." g";
            if(!$include_dpcg)
            {
                $sSQL .= "\n WHERE g.id != " . \bigfathom\Context::$SPECIALGROUPID_DEFAULT_PRIVCOLABS;
            }
            if($order_by_ar == NULL)
            {
                $sSQL .= "\n ORDER BY g.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= "\n ORDER BY $fields";
            }
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                //$leader_personid = $record['leader_personid'];
                //$leader_count = $leader_personid != NULL ? 1 : 0;
                if($id != \bigfathom\Context::$SPECIALGROUPID_EVERYONE)
                {
                    $thecount = empty($non_leader_membership_count[$id]) ? 0 : $non_leader_membership_count[$id];
                    $record['membership_count'] = $thecount;
                    /*
                    //Include the leader as a member in the count
                    if(!key_exists($id, $non_leader_membership_count))
                    {
                        $record['membership_count'] = $leader_count;
                    } else {
                        $record['membership_count'] = $non_leader_membership_count[$id] + $leader_count;
                    }
                    */
                } else {
                    //This is the special everyone group
                    $record['membership_count'] = $everyonecount;
                }
                $themap[$id] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a map of the vision statements that are mapped to a project
     */
    public function getVisionStatementsForProject($projectid)
    {
        try
        {
            $sSQL = "SELECT"
                    . " visionstatementid"
                    . " FROM ".DatabaseNamesHelper::$m_map_visionstatement2project_tablename." v2p"
                    . " WHERE v2p.projectid=$projectid";
            $themap = db_query($sSQL)->fetchAllAssoc('visionstatementid');
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of vision statements detail with ID as the key
     */
    private function getVisionStatementsByIDComplex($order_by_ar=NULL)
    {
        try
        {
            //Get all the visionstatements
            $themap = array();
            $sSQL1 = "SELECT"
                    . " g.id,"
                    . " g.statement_nm,"
                    . " g.statement_tx,"
                    . " g.references_tx,"
                    . " g.active_yn,"
                    . " g.owner_personid,"
                    . " g.updated_dt,"
                    . " g.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_visionstatement_tablename." g";
            if($order_by_ar == NULL)
            {
                $sSQL1 .= "\n ORDER BY g.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL1 .= "\n ORDER BY $fields";
            }

            $sSQL2 = "SELECT"
                    . " visionstatementid, count(v2p.projectid) project_count"
                    . " FROM ".DatabaseNamesHelper::$m_map_visionstatement2project_tablename." v2p"
                    . " GROUP BY visionstatementid";
            $allcounts = db_query($sSQL2)->fetchAllAssoc('visionstatementid');
            
            $result1 = db_query($sSQL1);
            while($record1 = $result1->fetchAssoc()) 
            {
                $id = $record1['id'];
                if(!isset($allcounts[$id]))
                {
                    $record1['project_count'] = 0;
                } else {
                    $record1['project_count'] = $allcounts[$id]->project_count;
                }
                $themap[$id] = $record1;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return one record
     */
    public function getOneBrainstormItemByID($id, $fail_if_missing=TRUE)
    {
        try
        {
            $sSQL = "SELECT"
                    . " u.id,"
                    . " u.projectid,"
                    . " u.context_nm,"
                    . " u.item_nm,"
                    . " u.purpose_tx,"
                    . " u.owner_personid,"
                    . " u.candidate_type,"
                    . " u.effort_hours_est,"
                    . " u.active_yn,"
                    . " u.parkinglot_level,"
                    . " u.into_parkinglot_dt,"
                    . " u.into_trash_dt,"
                    . " u.importance,"
                    . " u.updated_dt, u.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_brainstorm_item_tablename." u"
                    . " WHERE u.id=$id";
            $result = db_query($sSQL);
            if($fail_if_missing && $result->rowCount() !== 1)
            {
                throw new \Exception("Failed to find one brainstorm item where brainstorm_id=$id!");
            }
            return $result->fetchAssoc();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getBrainstorm2WorkitemConversionMap($brainstormid=NULL,$workitemid=NULL)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT brainstormid,workitemid,created_dt "
                    . " FROM " . DatabaseNamesHelper::$m_map_brainstormid2wid_tablename;
            if($brainstormid !== NULL)
            {
                $sSQL .= " WHERE brainstormid=$brainstormid";
            } else if($brainstormid !== NULL)
            {
                $sSQL .= " WHERE workitemid=$workitemid";
            } else {
                $sSQL .= " ORDER BY brainstormid DESC";
            }
            
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $workitemid = $record['workitemid'];
                $brainstormid = $record['brainstormid'];
                $themap[$brainstormid] = $workitemid; 
            }  
            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of brainstorm item detail with ID as the key
     */
    public function getBrainstormItemsByID($projectid=NULL
            , $newer_than_timestamp=NULL
            , $include_parkinglot=FALSE
            , $include_trash=FALSE)
    {
        try
        {
            $themap = array();
            $where_and_ar = [];
            $sSQL = "SELECT"
                    . " u.id,"
                    . " u.projectid,"
                    . " u.context_nm,"
                    . " u.item_nm,"
                    . " u.purpose_tx,"
                    . " u.owner_personid,"
                    . " u.candidate_type,"
                    . " u.active_yn,"
                    . " u.parkinglot_level,"
                    . " u.into_parkinglot_dt,"
                    . " u.into_trash_dt,"
                    . " u.importance,"
                    . " u.updated_dt,"
                    . " u.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_brainstorm_item_tablename." u";
            if($projectid != NULL)
            {
                $where_and_ar[] = "projectid=$projectid";
            }
            if($newer_than_timestamp !== NULL)
            {
                $newer_than_dt = date('Y-m-d H:i:s', $newer_than_timestamp);
                $where_and_ar[] = "updated_dt >= '$newer_than_dt'";  //Include equal because seconds are coarse.
            }
            if(!$include_parkinglot)
            {
                $where_and_ar[] = "into_parkinglot_dt IS NULL";
            }
            if(!$include_trash)
            {
                $where_and_ar[] = "into_trash_dt IS NULL";
            }
            if(count($where_and_ar) > 0)
            {
                $sSQL  .= " WHERE " . implode(" and ", $where_and_ar);
            }
            $sSQL .= " ORDER BY u.id";
            
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $typeletter = $record['candidate_type'];
                $record['brainstormid'] = $id;
                $record['type'] = empty($typeletter) ? NULL : ($typeletter == 'G' ? 'goal' : 'task');
                $themap[$id] = $record; 
            }  
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of trashed brainstorm item detail with ID as the key
     */
    public function getTrashedBrainstormItemsByID($projectid=NULL, $newer_than_timestamp=NULL)
    {
        try
        {
            $themap = array();
            $where_ar = [];
            $sSQL = "SELECT"
                    . " u.id,"
                    . " u.projectid,"
                    . " u.context_nm,"
                    . " u.item_nm,"
                    . " u.purpose_tx,"
                    . " u.owner_personid,"
                    . " u.candidate_type,"
                    . " u.active_yn,"
                    . " u.parkinglot_level,"
                    . " u.into_parkinglot_dt,"
                    . " u.into_trash_dt,"
                    . " u.importance,"
                    . " u.updated_dt,"
                    . " u.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_brainstorm_item_tablename." u";
            if($projectid != NULL)
            {
                $where_ar[] = "projectid=$projectid";
            }
            if($newer_than_timestamp !== NULL)
            {
                $newer_than_dt = date('Y-m-d H:i:s', $newer_than_timestamp);
                $where_ar[] = "updated_dt >= '$newer_than_dt'";  //Include equal because seconds are coarse.
            }
            $where_ar[] = "into_trash_dt IS NOT NULL";
            $sSQL  .= " WHERE " . implode(" and ", $where_ar);
            $sSQL .= " ORDER BY u.id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $typeletter = $record['candidate_type'];
                $record['brainstormid'] = $id;
                $record['type'] = empty($typeletter) ? NULL : ($typeletter == 'G' ? 'goal' : 'task');
                $themap[$id] = $record; 
            }  
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getListAsMap($list)
    {
        $themap = [];
        foreach($list as $value)
        {
            $themap[$value] = $value;
        }
        return $themap;
    }

    /**
     * Return info of most recent project data change
     */
    public function getMostRecentProjectEditInfo($owner_projectid)
    {
        try
        {
            $bundle = [];
            $thedate = NULL;
            $sSQL_members_counts = "SELECT id, updated_dt "
                    . " FROM ".DatabaseNamesHelper::$m_project_recent_data_updates_tablename." s "
                    . " WHERE s.projectid=$owner_projectid"
                    . " ORDER BY id DESC"
                    . " LIMIT 1";
            $result = db_query($sSQL_members_counts);
            if($result->rowCount() > 0)
            {
                $rec = $result->fetchObject();
                $theid =$rec->id;
                $thedate = strtotime($rec->updated_dt);
                $thekey = "{$theid}_{$thedate}";
            } else {
                $theid = NULL;
                $thedate = NULL;
                $thekey = NULL;
            }
            $bundle['key'] = $thekey;
            $bundle['id'] = $theid;
            $bundle['timestamp'] = $thedate;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return info of most recent project data change
     */
    public function getMostRecentTPEditInfo($owner_template_projectid)
    {
        try
        {
            $bundle = [];
            $thedate = NULL;
            $sSQL_members_counts = "SELECT id, updated_dt "
                    . " FROM ".DatabaseNamesHelper::$m_template_project_recent_data_updates_tablename." s "
                    . " WHERE s.template_projectid=$owner_template_projectid"
                    . " ORDER BY id DESC"
                    . " LIMIT 1";
            $result = db_query($sSQL_members_counts);
            if($result->rowCount() > 0)
            {
                $rec = $result->fetchObject();
                $theid =$rec->id;
                $thedate = strtotime($rec->updated_dt);
                $thekey = "{$theid}_{$thedate}";
            } else {
                $theid = NULL;
                $thedate = NULL;
                $thekey = NULL;
            }
            $bundle['key'] = $thekey;
            $bundle['id'] = $theid;
            $bundle['timestamp'] = $thedate;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the brainstorm context
     */
    public function getBrainstormUpdatesMapBundle($owner_projectid, $previous_bundle=NULL
            , $include_parkinglot=FALSE
            , $include_trash=FALSE)
    {
        try
        {
            $bundle = [];
            if($previous_bundle === NULL)
            {
                $previous_bundle = [];
                $previous_bundle['most_recent_edit_key'] = NULL;
                $previous_bundle['most_recent_edit_timestamp'] = NULL;
            }
            $previous_project_edit_key = $previous_bundle['most_recent_edit_key'];
            $previous_project_edit_timestamp = $previous_bundle['most_recent_edit_timestamp'];
            if(empty($owner_projectid))
            {
                throw new \Exception("Cannot get brainstorm bundle without a projectid value!");
            }

            $has_newdata = FALSE;
            $updateinfo = $this->getMostRecentProjectEditInfo($owner_projectid);
            $most_recent_edit_key = $updateinfo['key'];
            $most_recent_edit_timestamp = $updateinfo['timestamp'];
            if(!empty($most_recent_edit_timestamp))
            {
                if(empty($previous_project_edit_timestamp))
                {
                    $timediff = $most_recent_edit_timestamp;
                } else {
                    $timediff = $most_recent_edit_timestamp - $previous_project_edit_timestamp;
                }
                if($timediff > 0 || ($timediff == 0 && $most_recent_edit_key !== $previous_project_edit_key))
                {
                    //Data changed since we last refreshed our copy.
                    $bundle['newdata']['nodes'] = $this->getBrainstormItemsByID($owner_projectid
                                                            , NULL
                                                            , $include_parkinglot
                                                            , $include_trash);
                    $has_newdata = TRUE;
                }
            } else {
                $timediff = NULL;
            }
            
            $bundle['projectid']=$owner_projectid;
            $bundle['timediff']=$timediff;
            $bundle['previous_project_edit_timestamp']=$previous_project_edit_timestamp;
            $bundle['previous_project_edit_key']=$previous_project_edit_key;
            $bundle['most_recent_edit_timestamp']=$most_recent_edit_timestamp;
            $bundle['most_recent_edit_key']=$most_recent_edit_key;
            $bundle['has_newdata']=$has_newdata;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the hierarchy mapping context
     */
    public function getHierarchyUpdatesMapBundle($owner_projectid, $previous_bundle_factors=NULL, $only_active=TRUE)
    {
        try
        {
            $bundle = [];
            if($previous_bundle_factors === NULL)
            {
                $previous_bundle_factors = [];
                $previous_bundle_factors['most_recent_edit_key'] = NULL;
                $previous_bundle_factors['most_recent_edit_timestamp'] = NULL;
            }
            $previous_project_edit_key = $previous_bundle_factors['most_recent_edit_key'];
            $previous_project_edit_timestamp = $previous_bundle_factors['most_recent_edit_timestamp'];
            if(empty($owner_projectid))
            {
                throw new \Exception("Cannot get hierarchy map bundle without a projectid value!");
            }
            
            $has_newdata = FALSE;
            $updateinfo = $this->getMostRecentProjectEditInfo($owner_projectid);
            $most_recent_edit_key = $updateinfo['key'];
            $most_recent_edit_timestamp = $updateinfo['timestamp'];
            if(!empty($most_recent_edit_timestamp))
            {
                if(empty($previous_project_edit_timestamp))
                {
                    $timediff = $most_recent_edit_timestamp;
                } else {
                    $timediff = $most_recent_edit_timestamp - $previous_project_edit_timestamp;
                }
                if($timediff > 0 || ($timediff == 0 && $most_recent_edit_key !== $previous_project_edit_key))
                {
                    //Data changed since we last refreshed our copy.
                    $bundle['map_brainstormid2wid'] = $this->getBrainstorm2WorkitemConversionMap();
                    $bundle['newdata'] = $this->getHierarchyMapBundle4Project($owner_projectid, $only_active);
                    $has_newdata = TRUE;
                }
            } else {
                $timediff = NULL;
            }
            
            
            $bundle['projectid']=$owner_projectid;
            $bundle['timediff']=$timediff;
            $bundle['previous_project_edit_timestamp']=$previous_project_edit_timestamp;
            $bundle['previous_project_edit_key']=$previous_project_edit_key;
            $bundle['most_recent_edit_timestamp']=$most_recent_edit_timestamp;
            $bundle['most_recent_edit_key']=$most_recent_edit_key;
            $bundle['has_newdata']=$has_newdata;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the hierarchy mapping context
     * Returns brainstorm topics without including parked and trashed
     */
    public function getHierarchyMapBundle4Project($owner_projectid, $only_active=TRUE)
    {
        try
        {
            $bundle = array();
            
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot get hierarchy map bundle without a projectid value!");
            }
            
            $relevant_candidate_workitems = $this->getCandidateWorkitemsInProjectByID($owner_projectid);

            $relevant_orphan_workitems = $this->getProjectOrphanWorkitemsByID($owner_projectid);
            
            $all_workitems_bundle = $this->getAllWorkitemsInProjectBundle($owner_projectid);
            $all_relevant_workitems = $all_workitems_bundle['all_workitems'];
            $status_cd_lookup = $this->getWorkitemStatusByCode();
            $person_lookup = $this->getMinimalInfoPersonsInProjectByID($owner_projectid, NULL, NULL, TRUE);
            
            $bundle['root_projectid'] = $owner_projectid;
            $bundle['root_goalid'] = $all_workitems_bundle['root_goalid'];
            $bundle['status_cd_lookup'] = $status_cd_lookup;
            $bundle['person_lookup'] = $person_lookup;
            $bundle['just_connected_subproject_wids'] = $this->getListAsMap($all_workitems_bundle['lists']['connected_subproject_wids']);
            $bundle['just_disconnected_subproject_wids'] = $this->getListAsMap($all_workitems_bundle['lists']['connected_subproject_wids']);
            $bundle['just_owned_workitemids'] = $this->getListAsMap($all_workitems_bundle['lists']['owned_wids']);
            $bundle['just_notowned_workitemids'] = $this->getListAsMap($all_workitems_bundle['lists']['notowned_wids']);
            $map_ids_bytypeletter = array('G'=>[], 'T'=>[], 'X'=>[], 'Q'=>[]);
            $project_ids = [];
            foreach($all_relevant_workitems as $wid=>$detail)
            {
                $typeleter = $detail['typeletter'];
                $map_ids_bytypeletter[$typeleter][$wid] = $wid;
                $opid = $detail['owner_projectid'];
                $project_ids[$opid] = $opid;
            }

            $bundle['just_goal_ids'] = $map_ids_bytypeletter['G'];
            $bundle['just_task_ids'] = $map_ids_bytypeletter['T'];
            $bundle['just_equjb_ids'] = $map_ids_bytypeletter['Q'];
            $bundle['just_xrcjb_ids'] = $map_ids_bytypeletter['X'];
            
            $bundle['just_orphan_workitems'] = [];
            foreach($relevant_orphan_workitems as $wid=>$detail)
            {
                $bundle['just_orphan_workitems'][$wid] = $wid;
            }
            
            $bundle['candidate_workitems'] = $relevant_candidate_workitems;
            $bundle['workitems_detail_lookup'] = $all_relevant_workitems;
            
            $bundle['just_project_ids'] = $project_ids;
            $bundle['wi2wi'] = $this->getW2WList($owner_projectid);
            $updateinfo = $this->getMostRecentProjectEditInfo($owner_projectid);
            $bundle['most_recent_edit_key'] = $updateinfo['key'];
            $bundle['most_recent_edit_timestamp'] = $updateinfo['timestamp'];
            //DebugHelper::debugPrintNeatly($bundle,FALSE,"LOOK getHierarchyMapBundle($owner_projectid)...");            
            return $bundle;
        } catch (\Exception $ex) {
            error_log("ERROR FAILED getHierarchyMapBundle ex=" . $ex);      
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the hierarchy mapping context
     * Returns brainstorm topics without including parked and trashed
     */
    public function getHierarchyMapBundle4TP($owner_projectid)
    {
        try
        {
            $bundle = [];
            
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot get template hierarchy map bundle without a projectid value!");
            }
            
            $relevant_candidate_workitems = []; //$this->getCandidateTWInTPByID($owner_projectid);

            $relevant_orphan_workitems = $this->getTPOrphanTWsByID($owner_projectid);
            $all_workitems_bundle = $this->getAllTWInTPBundle($owner_projectid);
            
            $all_relevant_workitems = $all_workitems_bundle['all_workitems'];
            $status_cd_lookup = $this->getWorkitemStatusByCode();
            $person_lookup = $this->getMinimalInfoPersonsInTPByID($owner_projectid, NULL, NULL, TRUE);
            
            $bundle['root_projectid'] = $owner_projectid;
            $bundle['root_goalid'] = $all_workitems_bundle['root_goalid'];
            $bundle['status_cd_lookup'] = $status_cd_lookup;
            $bundle['person_lookup'] = $person_lookup;
            $bundle['just_connected_subproject_wids'] = $this->getListAsMap($all_workitems_bundle['lists']['connected_subproject_wids']);
            $bundle['just_disconnected_subproject_wids'] = $this->getListAsMap($all_workitems_bundle['lists']['connected_subproject_wids']);
            $bundle['just_owned_workitemids'] = $this->getListAsMap($all_workitems_bundle['lists']['owned_wids']);
            $bundle['just_notowned_workitemids'] = $this->getListAsMap($all_workitems_bundle['lists']['notowned_wids']);
            $map_ids_bytypeletter = array('G'=>[], 'T'=>[], 'X'=>[], 'Q'=>[]);
            $project_ids = [];
            foreach($all_relevant_workitems as $wid=>$detail)
            {
                $typeleter = $detail['typeletter'];
                $map_ids_bytypeletter[$typeleter][$wid] = $wid;
                $opid = $detail['owner_template_projectid'];
                $project_ids[$opid] = $opid;
            }

            $bundle['just_goal_ids'] = $map_ids_bytypeletter['G'];
            $bundle['just_task_ids'] = $map_ids_bytypeletter['T'];
            $bundle['just_equjb_ids'] = $map_ids_bytypeletter['Q'];
            $bundle['just_xrcjb_ids'] = $map_ids_bytypeletter['X'];
            
            $bundle['just_orphan_workitems'] = [];
            foreach($relevant_orphan_workitems as $wid=>$detail)
            {
                $bundle['just_orphan_workitems'][$wid] = $wid;
            }
            
            $bundle['candidate_workitems'] = $relevant_candidate_workitems;
            $bundle['workitems_detail_lookup'] = $all_relevant_workitems;
            
            $bundle['just_project_ids'] = $project_ids;
            $bundle['wi2wi'] = $this->getTW2TWList($owner_projectid);
            $updateinfo = $this->getMostRecentTPEditInfo($owner_projectid);
            $bundle['most_recent_edit_key'] = $updateinfo['key'];
            $bundle['most_recent_edit_timestamp'] = $updateinfo['timestamp'];
            //DebugHelper::debugPrintNeatly($bundle,FALSE,"LOOK getHierarchyMapBundle($owner_projectid)...");            
            return $bundle;
        } catch (\Exception $ex) {
            error_log("ERROR FAILED getTPHierarchyMapBundle ex=" . $ex);      
            throw $ex;
        }
    }
    
    /**
     * Return count of workitems in each sprint in a map with sprintid as key
     */
    public function getSprintWorkitemMembershipCountMap($owner_projectid)
    {
        try
        {
            $map_membership_count = array();
            $sSQL_members_counts = "SELECT"
                    . " s.id, count(g2s.workitemid) as members "
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." s "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename." g2s on g2s.sprintid=s.id"
                    . " WHERE s.owner_projectid=$owner_projectid"
                    . " GROUP BY s.id";
            $result_members_counts = db_query($sSQL_members_counts);
            while($record_members_counts = $result_members_counts->fetchAssoc())
            {
                $id = $record_members_counts['id'];
                $map_membership_count[$id] = $record_members_counts['members'];
            }
            return $map_membership_count;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of insight in goals where workitemid is the key
     */
    public function getWorkitem2InsightList($owner_projectid, $about_personid=NULL)
    {
        try
        {
            $map = array();
            $sWhere = "g.owner_projectid=$owner_projectid and g.id IS NOT NULL";
            if($about_personid !== NULL)
            {
                $sWhere .= " and personid=$about_personid";
            }
            $sSQL_insight = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.insight "
                    . " FROM ".DatabaseNamesHelper::$m_general_person_insight2wi_tablename." i2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on i2g.workitemid=g.id"
                    . " WHERE $sWhere";
            $result_insight = db_query($sSQL_insight);
            while($record_insight = $result_insight->fetchAssoc())
            {
                $workitemid = $record_insight['workitemid'];
                $rawinsightvalue = $record_insight['insight'];
                $record_insight['insight_level'] = \bigfathom\UtilityGeneralFormulas::getInsightLevelFromRawValue($rawinsightvalue);
                $map[$workitemid] = $record_insight;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getForecastNuggetsMapBundle($relevant_projectids)
    {
        try
        {   
            if(empty($relevant_projectids))
            {
                throw new \Exception("Missing required relevant_projectids!");
            }
            $loaded = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the ProjectForecaster class');
            }
            $bundle = [];
            if(!is_array($relevant_projectids))
            {
                $relevant_projectids = array($relevant_projectids);
            }
            $detail = [];
            $clean_relevant_projectids = [];
            foreach($relevant_projectids as $projectid)
            {
                if(!$this->isProjectAvailable($projectid))
                {
                    drupal_set_message("The projectid#$projectid is no longer available",'warning');
                } else {
                    //This one is available
                    $clean_relevant_projectids[$projectid] = $projectid;
                }
            }
            foreach($clean_relevant_projectids as $projectid)
            {
                $oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($projectid);  
                $projectforecast = $oProjectForecaster->getDetail();

                $root_goalid = $projectforecast['main_project_detail']['metadata']['root_goalid'];
                $forecast_workitems = $projectforecast['main_project_detail']['workitems'];
                $root_nodedetail = $forecast_workitems[$root_goalid];
                $root_forecast = $root_nodedetail['forecast'];
                $root_otsp_value = $root_forecast['local']['otsp'];
                $root_otsp_logic = $root_forecast['local']['logic'];
                $detail[$projectid] = [];
                $detail[$projectid]['root_otsp'] = [];
                $detail[$projectid]['root_otsp']['value'] = $root_otsp_value;
                $detail[$projectid]['root_otsp']['logic'] = $root_otsp_logic;

                $warnings = [];
                $errors = [];
                foreach($forecast_workitems as $workitemid=>$record)
                {
                    $computed_bounds = $record['computed_bounds'];
                    if(!empty($computed_bounds['bottomup']['warnings']))
                    {
                        $type_items = $computed_bounds['bottomup']['warnings']['type_map'];
                        $detail_items = $computed_bounds['bottomup']['warnings']['detail'];
                        foreach($type_items as $one_item)
                        {
                            $warnings['type_map'][$one_item] = $one_item;
                        }
                        foreach($detail_items as $one_item)
                        {
                            $warnings['detail'][] = $one_item;
                        }
                    }
                    if(!empty($computed_bounds['bottomup']['errors']))
                    {
                        $type_items = $computed_bounds['bottomup']['errors']['type_map'];
                        $detail_items = $computed_bounds['bottomup']['errors']['detail'];
                        foreach($type_items as $one_item)
                        {
                            $errors['type_map'][$one_item] = $one_item;
                        }
                        foreach($detail_items as $one_item)
                        {
                            $errors['detail'][] = $one_item;
                        }
                    }
                }
                $detail[$projectid]['warnings'] = $warnings;
                $detail[$projectid]['errors'] = $errors;
            }
            $bundle['by_projectid'] = $detail;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getForecastDetailMapBundle($relevant_projectids)
    {
        try
        {   
            if(empty($relevant_projectids))
            {
                throw new \Exception("Missing required relevant_projectids!");
            }
            $loaded = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the ProjectForecaster class');
            }
            $bundle = [];
            if(!is_array($relevant_projectids))
            {
                $relevant_projectids = array($relevant_projectids);
            }
            $detail = [];
            $clean_relevant_projectids = [];
            foreach($relevant_projectids as $projectid)
            {
                if(!$this->isProjectAvailable($projectid))
                {
                    drupal_set_message("The projectid#$projectid is no longer available",'warning');
                } else {
                    //This one is available
                    $clean_relevant_projectids[$projectid] = $projectid;
                }
            }
            
            $metadata = [];
            $metadata['projects'] = $clean_relevant_projectids;
            $project2workitems = [];
            $workitem2detail = [];
            foreach($clean_relevant_projectids as $projectid)
            {
                $project2workitems[$projectid] = [];
                $oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($projectid);  
                $projectforecast = $oProjectForecaster->getDetail();
                $forecast_workitems = $projectforecast['main_project_detail']['workitems'];
                foreach($forecast_workitems as $workitemid=>$record)
                {
                    $project2workitems[$projectid][] = [$workitemid];
                    $computed_bounds = $record['computed_bounds'];
                    $workitem2detail[$workitemid] = array(
                        'workitemid'=>$workitemid,
                        'forecast'=>$record['forecast'],
                        'warnings'=>$computed_bounds['bottomup']['warnings'],
                        'errors'=>$computed_bounds['bottomup']['errors']
                    );
                    
                    /*
                    $computed_bounds = $record['computed_bounds'];
                    if(!empty($computed_bounds['bottomup']['warnings']['detail']))
                    {
                        $warnings_detail = $computed_bounds['bottomup']['warnings']['detail'];
                        foreach($warnings_detail as $onewarning)
                        {
                            $wmsg = $onewarning['message'];
                            drupal_set_message("$wmsg","warning");
                        }
                    }
                    if(!empty($computed_bounds['bottomup']['errors']['detail']))
                    {
                        $errors = $computed_bounds['bottomup']['errors']['detail'];
                        foreach($errors as $oneerror)
                        {
                            $emsg = $oneerror['message'];
                            drupal_set_message("$emsg","error");
                        }
                    }                    
                    */
                    
                }
            }
            $metadata['project2workitem'] = $project2workitems;
            $bundle['metadata'] = $metadata;
            $bundle['workitem2detail'] = $workitem2detail;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Just return the list of projects that this user has some access to work with
     */
    public function getRelevantProjectsMap($personid)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            
            $uah = new \bigfathom\UserAccountHelper();
            $upb = $uah->getUserProfileBundle($personid);
            $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        
            $themap = [];
            $sSQL = "SELECT DISTINCT proj.id as projectid, proj.owner_personid, p2rg.groupid as groupid, grp2prj.groupid as pgroupid ";
            $sSQL .= " FROM " . DatabaseNamesHelper::$m_project_tablename . " proj ";
            $sSQL .= " LEFT JOIN " . DatabaseNamesHelper::$m_map_group2project_tablename . " grp2prj ON grp2prj.projectid=proj.id ";
            $sSQL .= " LEFT JOIN " . DatabaseNamesHelper::$m_map_person2role_in_group_tablename . " p2rg ON p2rg.groupid=grp2prj.groupid AND p2rg.personid=$personid ";
            $sSQL .= " ORDER BY proj.id";
            
            $result = db_query($sSQL);
            
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['projectid'];
                $owner_personid = $record['owner_personid'];
                $groupid = $record['groupid'];
                $pgroupid = $record['pgroupid'];
                $is_owner = $owner_personid == $personid;
                if($this->m_is_systemadmin || $is_owner || !empty($groupid) || self::$GROUPID_EVERYONE == $pgroupid)
                {
                    if(!array_key_exists($projectid, $themap))
                    {
                        $themap[$projectid]['intersecting_groups'] = [];
                        $themap[$projectid]['is_owner_yn'] = $is_owner ? 1 : 0;
                    }
                    if(!empty($groupid))
                    {
                        $themap[$projectid]['intersecting_groups'][$groupid] = $groupid;   
                    } else {
                        //Check for special groups
                        if(!empty($pgroupid))
                        {
                            $themap[$projectid]['intersecting_groups'][$groupid] = $pgroupid;   
                        }
                    }
                }
            }          
//drupal_set_message("LOOK relevant_projects=" . print_r($themap,TRUE));            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of data for the user dashboard
     */
    public function getDashnuggetsPersonalTabBundle($about_personid=NULL, $showdetail_yn=FALSE)
    {
        try
        {
            $bundle = [];
            if(empty($about_personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $relevant_projects_map = $this->getRelevantProjectsMap($about_personid);
            $bundle['relevant_projects_map'] = $relevant_projects_map; 
            $relevant_projectid_list = array_keys($relevant_projects_map);
            $bundle['importance'] = $this->getWorkitem2ImportanceCountsByCategory4Person($relevant_projectid_list, $about_personid, $about_personid, $showdetail_yn);
            $bundle['influence'] = $this->getWorkitem2InfluenceCountsByCategory4Person($relevant_projectid_list, $about_personid, $about_personid, $showdetail_yn);
            $bundle['tmm'] = $this->getWorkitemTMMCountsByCategory4Person($relevant_projectid_list, $about_personid, $about_personid, $showdetail_yn);
            $bundle['oartmm'] = $this->getOpenActionRequestTMMCountsByCategory4Person($relevant_projectid_list, $about_personid, $about_personid, $showdetail_yn);
            $bundle['sprinttmm'] = $this->getSprintTMMCountsByCategory4Person($relevant_projectid_list, $about_personid, $about_personid, $showdetail_yn);
            $pids = [];
            foreach($bundle['importance']['by_project'] as $pid=>$detail)
            {
                $pids[$pid] = $pid;
            }
            foreach($bundle['influence']['by_project'] as $pid=>$detail)
            {
                $pids[$pid] = $pid;
            }
            foreach($bundle['tmm']['by_project'] as $pid=>$detail)
            {
                $pids[$pid] = $pid;
            }
            foreach($bundle['oartmm']['by_project'] as $pid=>$detail)
            {
                $pids[$pid] = $pid;
            }
            foreach($bundle['sprinttmm']['by_project'] as $pid=>$detail)
            {
                $pids[$pid] = $pid;
            }
            //$pids_ar = array_keys($pids);
            $pmap = $this->getSmallProjectInfoForListOfIDs($pids);
            $bundle['project_lookup'] = $pmap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of data for the user dashboard
     */
    public function getSortedWorklistTabBundle($about_personid=NULL, $showdetail_yn=FALSE)
    {
        try
        {
            $bundle = [];
            if(empty($about_personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $wcb = $this->getWorkitemCategorizationBundle(NULL, $about_personid, NULL, TRUE);
            $worklist=[];
            //$naturalorder=[];
            $bundle['summary'] = $wcb['summary'];
            $by_project = $wcb['by_project'];
            $sortedkeys = [];
            foreach($by_project as $pid=>$oneproject)
            {
                $rows = $oneproject['workitems'];
                foreach($rows as $onerow)
                {
                    $wid = $onerow['workitemid'];
                    $scalar_tuple = $onerow['scalar_urgency'];
                    $justscore = $scalar_tuple['score'];
                    if(!isset($sortedkeys[$justscore])) //array_key_exists($justscore, $sortedkeys))
                    {
                        $sortedkeys[$justscore] = [];  
                    }
                    $sortedkeys[$justscore][] = $wid;
                    $worklist[$wid] = $onerow;
                }
            }
            
            krsort($sortedkeys);
            $naturalorder = [];
            foreach($sortedkeys as $tier_wids)
            {
                sort($tier_wids);
                foreach($tier_wids as $wid)
                {
                    $naturalorder[] = $wid;
                }
            }
            
            $bundle['naturalorder'] = $naturalorder;
            $bundle['worklist'] =  $worklist;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return detail for one record
     */
    public function getOneBaselineAvailabilityRecord($baseline_availabilityid=NULL)
    {
        try
        {
            if(empty($baseline_availabilityid))
            {
                throw new \Exception("Missing required baseline_availabilityid!");
            }
            
            $sSQL2 = "select * "
                    . " from " . DatabaseNamesHelper::$m_baseline_availability_tablename;
            $sSQL2 .= " where id=" . $baseline_availabilityid;
            $result2 = db_query($sSQL2);
            $record = $result2->fetchAssoc();
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return detail for one record
     */
    public function getOnePersonAvailabilityRecord($person_availabiltyid=NULL)
    {
        try
        {
            if(empty($person_availabiltyid))
            {
                throw new \Exception("Missing required person_availabiltyid!");
            }
            
            $sSQL2 = "select * "
                    . " from " . DatabaseNamesHelper::$m_map_person2availability_tablename;
            $sSQL2 .= " where id=" . $person_availabiltyid;
            $result2 = db_query($sSQL2);
            $record = $result2->fetchAssoc();
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return information about availability of one or more persons
     */
    public function getPersonAvailabilityBundle($personid_ar=NULL)
    {
        try
        {        

            $metadata = [];
            $summary = [];
            if(!empty($personid_ar) && !is_array($personid_ar))
            {
                if(!is_numeric($personid_ar))
                {
                    throw new \Exception("Expected a personid but instead got '$personid_ar'!");
                }
                //Assume we got one personid
                $metadata['insight'] = "Assuming '$personid_ar' is a personid!";
                $personid = $personid_ar;
                $personid_ar = [];
                $personid_ar[] = $personid;
            }
            if(empty($personid_ar) || count($personid_ar) < 1)
            {
                throw new \Exception("Missing required array of personid!");
            }
            $inlist_personid = implode(",", $personid_ar);
            
            $all_records = [];
            $sSQL1 = "select ba.*, p.baseline_availabilityid, p.id as personid, p.first_nm, p.last_nm "
                    . " from " . DatabaseNamesHelper::$m_person_tablename . " p "
                    . " left join " . DatabaseNamesHelper::$m_baseline_availability_tablename . " ba on ba.id=p.baseline_availabilityid";
            
            $where = [];
            $where[] = "p.id in ($inlist_personid)";
            $where_exp = implode(" and ", $where);
            
            $sSQL1 .= " where $where_exp";
            
            $result1 = db_query($sSQL1);
            while($default_record = $result1->fetchAssoc())
            {
                $shortname = $default_record['shortname'];
                $default_record['type_info'] = array('name' => 'Default Availability', 'sort'=>0, 'tooltip'=>$shortname, 'class'=>'text-bolder');
                $default_record['start_dt'] = "";
                $default_record['end_dt'] = "";
                $default_record['is_default'] = TRUE;
                $all_records[] = $default_record;
            }            
            
            $custom_row_count = 0;
            $sSQL2 = "select p2a.*, p.first_nm, p.last_nm "
                    . " from " . DatabaseNamesHelper::$m_map_person2availability_tablename . " p2a "
                    . " left join " . DatabaseNamesHelper::$m_person_tablename . " p on p.id=p2a.personid";
            if(!empty($personid))
            {
                $sSQL2 .= " where $where_exp";
            }
            $sSQL2 .= " order by start_dt, type_cd ";
            $result2 = db_query($sSQL2);
            while($record = $result2->fetchAssoc())
            {
                $record['is_default'] = FALSE;
                $all_records[] = $record;
                $custom_row_count++;
            }
            
            $bundle = [];
            $bundle['metadata'] = $metadata;
            $summary['custom_row_count'] = $custom_row_count;
            $bundle['summary'] = $summary;
            $bundle['detail'] =  $all_records;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Compute the priority factors for all open workitems fitting the filter criteria
     */
    public function getWorkitemCategorizationBundle($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            
            $today_dt = date("Y-m-d", time());
            $summary = [];
            $importance_expression = "";
            $joinstuff_txt = "";
            $bundle = [];
            $core_filters = [];
            $core_filters['includedetail'] = $includedetail;
            $core_filters['owner_projectid'] = $owner_projectid;
            $core_filters['about_personid'] = $about_personid;
            $where_ar=[];
            $order_ar=[];
            $group_ar=[];
            $morefieldnames = "";
            $morejoin = "";
            if($owner_projectid !== NULL)
            {
                if(!is_array($owner_projectid))
                {
                    $where_ar[] = "g.owner_projectid=$owner_projectid";
                } else {
                    if(count($owner_projectid) > 0)
                    {
                        $where_ar[] = "g.owner_projectid IN (" . implode(", ", $owner_projectid) . ")";
                    }
                }
                $order_ar[] = "g.owner_projectid";
            }
            
            $group_ar[]="g.id";
            if($about_personid !== NULL)
            {
                if(empty($created_by_personid))
                {
                    $created_by_personid = $about_personid;
                }
                $core_filters['created_by_personid'] = $created_by_personid;
                $joinstuff_txt = " LEFT JOIN ".DatabaseNamesHelper::$m_general_person_importance2wi_tablename
                                    ." i2g on i2g.workitemid=g.id and i2g.personid=$about_personid and i2g.created_by_personid=$created_by_personid"
                                . " LEFT JOIN " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename 
                                    . " d2w on d2w.workitemid=g.id and d2w.personid=$about_personid"
                                . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename 
                                    . " ws on ws.code=g.status_cd ";
                $group_ar[]="i2g.workitemid";
                $group_ar[]="d2w.workitemid";
                $group_ar[]="ws.code";
                $importance_expression = "COALESCE(i2g.importance,g.importance)";
                $custom_importance_value = "i2g.importance as custom_importance";
                $primary_owner_expression = "(g.owner_personid = $about_personid) as is_primary_owner";
            } else {
                $importance_expression = "importance";
                $custom_importance_value = "NULL as custom_importance";
                $primary_owner_expression = "NULL as is_primary_owner";
                $joinstuff_txt = " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename 
                                    . " ws on ws.code=g.status_cd ";
                $group_ar[]="ws.code";
            }
            
            $morejoin = [];
            if($includedetail)
            {
                $morefieldnames = "g.workitem_nm,g.workitem_basetype as typeletter,g.status_cd,w2s.sprintid,"
                                . "COALESCE(g.actual_start_dt, g.planned_start_dt, '') as effective_start_dt,"
                                . "sp.start_dt as sprint_start_dt, sp.end_dt as sprint_end_dt, "
                                . "datediff(sp.end_dt, '$today_dt') as sprint_remaining_days, ";
                $morejoin[] = " LEFT JOIN ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename
                                    ." w2s on w2s.workitemid=g.id ";
                $morejoin[] = " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_tablename
                                    ." sp on sp.id=w2s.sprintid ";
                $group_ar[]="w2s.sprintid";
                $group_ar[]="sp.id";
            }
            $joinstuff_txt .= "\n" . implode("\n", $morejoin);
            
            $order_ar[] = "g.id";
            $where_ar[] = "g.active_yn=1";
            $where_ar[] = "(ws.terminal_yn IS NULL or ws.terminal_yn=0)";
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sGroupByClause = " GROUP BY " . implode(", ", $group_ar);
            $sOrderByClause = " ORDER BY " . implode(", ", $order_ar);
            $sSQL = "SELECT 
                        g.owner_projectid, g.id as workitemid, $morefieldnames g.importance as default_importance,
                        ws.workstarted_yn, ws.terminal_yn, ws.happy_yn, ws.ot_scf, $custom_importance_value,
                        @importance := $importance_expression,
                        @importance as importance,
                        @is_primary_owner := $primary_owner_expression, 
                        @effort_hours := COALESCE(effort_hours_est,0), 
                        @effort_hours as effort_hours, 
                        @hours_worked := COALESCE(effort_hours_worked_act, effort_hours_worked_est, 0), 
                        @hours_worked as hours_worked,
                        @remaining_hours := @effort_hours - @hours_worked, 
                        @remaining_hours as remaining_hours,
                        @effective_end_dt := COALESCE(actual_end_dt, planned_end_dt, '$today_dt'), 
                        @effective_end_dt as effective_end_dt,
                        @remaining_days := datediff(@effective_end_dt, '$today_dt'), 
                        @remaining_days as remaining_days,
                        @fte_hours_per_day := 8, 
                        @fte_hours_per_day as fte_hours_per_day,
                        @min_hours_per_day := (@effort_hours - @hours_worked) / @remaining_days, 
                        @min_hours_per_day as min_hours_per_day,
                        @no_more_days := if(@remaining_days < 0,1,0), 
                        @no_more_days as no_more_days,
                        @computed_min_fte := if(@no_more_days, 100*planned_fte_count, @remaining_hours / @remaining_days / @fte_hours_per_day), 
                        @computed_min_fte as computed_min_fte,
                        planned_fte_count,
                        @fte_buffer := planned_fte_count - @computed_min_fte, 
                        @fte_buffer as fte_buffer,
                        @is_urgent := if(@no_more_days OR @fte_buffer < planned_fte_count, 1, 0), 
                        @is_urgent as is_urgent,
                        @tmm_quadrant := if(@importance > 50,if(@is_urgent, 1, 2),if(@is_urgent, 3, 4)), 
                        @tmm_quadrant as tmm_quadrant
                      "
                    . "\nFROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . "\n$joinstuff_txt"
                    . "\n$sWhereClause"
                    . "\n$sGroupByClause"
                    . "\n$sOrderByClause";
           
            $bundle['today_dt'] = $today_dt;
            $bundle['filters'] = $core_filters;
            $summary['tmm']['Q1'] = 0;
            $summary['tmm']['Q2'] = 0;
            $summary['tmm']['Q3'] = 0;
            $summary['tmm']['Q4'] = 0;
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc())
            {
                $projectid = $record['owner_projectid'];
                $workitemid = $record['workitemid'];
                $tmm_quadrant = $record['tmm_quadrant'];
                $quadcode = "Q$tmm_quadrant";
                $summary['tmm'][$quadcode] += 1;
                if(!array_key_exists($projectid, $by_project))
                {
                    $by_project[$projectid]['tmm']['Q1'] = 0;
                    $by_project[$projectid]['tmm']['Q2'] = 0;
                    $by_project[$projectid]['tmm']['Q3'] = 0;
                    $by_project[$projectid]['tmm']['Q4'] = 0;
                }
                $by_project[$projectid]['tmm'][$quadcode] += 1;
                if($includedetail)
                {
                    $by_project[$projectid]['workitems'][$workitemid] = $record;
                }
            }
            
            $relevant_projectids = array_keys($by_project);
            $project_communicationbundle 
                    = $this->getCommunicationSummaryBundleForProject($relevant_projectids);
            $ardetail_by_wid = $project_communicationbundle['map_open_request_detail']['workitem'];
            foreach($by_project as $projectid=>$detail)
            {
                
                foreach($by_project[$projectid]['workitems'] as $wid=>$record)
                {
                    $keytuple = array($projectid,$wid);
                    if(!array_key_exists($wid, $ardetail_by_wid))
                    {
                        $by_project[$projectid]['workitems'][$wid]['ar_factors'] = array('ar_count'=>0,'concern_total'=>0,'details'=>[]);
                    } else {
                        $ar_count=0;
                        $detail = $ardetail_by_wid[$wid];
                        $summary = [];
                        $ar_concerntotal = 0;
                        foreach($detail as $concernlevel=>$commap)
                        {
                            $ar_count+=count($commap);
                            $clletter=substr($concernlevel,0,1);
                            $score = $clletter=='H' ? 30 : $clletter == 'M' ? 20 : 10;
                            $ar_concerntotal += $score * count($commap);
                        }
                        $by_project[$projectid]['workitems'][$wid]['ar_factors'] = array('ar_count'=>$ar_count,'concern_total'=>$ar_concerntotal,'details'=>$detail);
                    }
                    $importance = $record['importance'];
                    $ar_concerntotal = $ar_concerntotal;
                    $status_happy_yn = $record['happy_yn'];
                    $fte_count = $record['planned_fte_count'];
                    $end_days_remaining = $record['remaining_days'];
                    $sprint_days_remaining = $record['sprint_remaining_days'];
                    $scalar_tuple = $this->getWorkitemComprehensiveUrgencyScoreBundle($importance,$ar_concerntotal,$status_happy_yn,$fte_count,$end_days_remaining,$sprint_days_remaining);
                    $by_project[$projectid]['workitems'][$wid]['scalar_urgency'] = $scalar_tuple;
                }
            }
            $bundle['summary'] = $summary;
            $bundle['by_project'] = $by_project;

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getWorkitemComprehensiveUrgencyScoreBundle($importance,$ar_concerntotal,$status_happy_yn,$fte_count,$end_days_remaining,$sprint_days_remaining)
    {
        $logic = [];
        if(empty($fte_count))
        {
            $fte_count = 1;
        }
        $days_worry = 7/$fte_count;
        $happy_factor = $status_happy_yn === 0 ? 2 : 1;
        $sprint_factor = $sprint_days_remaining === NULL ? 1 : $sprint_days_remaining < $days_worry ? 2 : 1.5;
        $remaining_days_factor = $end_days_remaining === NULL ? 1 : $end_days_remaining < $days_worry ? 2 : 1.5;
        $score = ceil(($importance + $ar_concerntotal) * $happy_factor * $sprint_factor * $remaining_days_factor);
        if($happy_factor > 1)
        {
            $logic['statusX'] = $happy_factor;
        }
        if($sprint_factor > 1)
        {
            $logic['sprintX'] = $sprint_factor;
        }
        if($remaining_days_factor > 1)
        {
            $logic['enddateX'] = $remaining_days_factor;
        }
        if($importance > 1)
        {
            $logic['importance'] = $importance;
        }
        if($ar_concerntotal > 1)
        {
            $logic['concern'] = $ar_concerntotal;
        }
        $bundle = array('score'=>$score, 'logic'=>$logic);
        return $bundle;
    }

    private function getOpenWorkitemActionRequestIDMap()
    {
        try
        {
            $sSQL = "SELECT 0 as is_reply, id, parent_comid, action_requested_concern, action_reply_cd "
                    . " FROM " . DatabaseNamesHelper::$m_workitem_communication_tablename
                    . " WHERE parent_comid IS NULL and action_requested_concern>0 and active_yn=1"
                    . " UNION"
                    . " SELECT 1 as is_reply, id, parent_comid, action_requested_concern, action_reply_cd "
                    . " FROM " . DatabaseNamesHelper::$m_workitem_communication_tablename
                    . " WHERE parent_comid IS NOT NULL and action_reply_cd<>'PARTR' and active_yn=1";

            $result = db_query($sSQL);
            $parent_chain = [];
            $request_map = [];
            while($record = $result->fetchAssoc())
            {
                $is_reply = $record['is_reply'];
                $action_reply_cd = $record['action_reply_cd'];
                $comid = $record['id'];
                $parent_comid = $record['parent_comid'];
                if($is_reply)
                {
                    if(!isset($parent_chain[$parent_comid]))
                    {
                        $parent_chain[$parent_comid] = [];
                    }
                    $parent_chain[$parent_comid][$comid] = $action_reply_cd;
                } else {
                    $request_map[$comid] = $comid;
                }
            }
            
            //Now build our list of open requests
            $open_requests = [];
            foreach($request_map as $comid)
            {
                if(empty($parent_chain[$comid]))
                {
                    //Has no closure replies!
                    $open_requests[$comid] = $comid;
                }
            }
            return $open_requests;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Compute the priority factors for all action requests fitting the filter criteria
     * TODO - ENHANCE SO THAT RESOLVED ACTION REQUESTS ARE NOT COUNTED!!!!
     */
    public function getActionRequestCategorizationBundle($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            
            $today_dt = date("Y-m-d", time());
            $summary = [];
            $importance_expression = "";
            $joinstuff_txt = "";
            $bundle = [];
            $core_filters = [];
            $core_filters['includedetail'] = $includedetail;
            $core_filters['owner_projectid'] = $owner_projectid;
            $core_filters['about_personid'] = $about_personid;
            $where_ar=[];
            $order_ar=[];
            
            if($owner_projectid !== NULL)
            {
                if(!is_array($owner_projectid))
                {
                    $where_ar[] = "g.owner_projectid=$owner_projectid";
                } else {
                    if(count($owner_projectid) > 0)
                    {
                        $where_ar[] = "g.owner_projectid IN (" . implode(", ", $owner_projectid) . ")";
                    }
                }
                $order_ar[] = "g.owner_projectid";
            }
            
            $bundle['today_dt'] = $today_dt;
            $bundle['filters'] = $core_filters;
            $by_project = [];
            $summary['tmm']['Q1'] = 0;
            $summary['tmm']['Q2'] = 0;
            $summary['tmm']['Q3'] = 0;
            $summary['tmm']['Q4'] = 0;
                
            $open_requests = $this->getOpenWorkitemActionRequestIDMap();
            if(count($open_requests)>0)
            {
                $open_comids_tx = " (" . implode(',',$open_requests) . ")";
                
                //Pull the details for the open requests
                if($about_personid !== NULL)
                {
                    if(empty($created_by_personid))
                    {
                        $created_by_personid = $about_personid;
                    }
                    $core_filters['created_by_personid'] = $created_by_personid;
                    $joinstuff_txt = " LEFT JOIN ".DatabaseNamesHelper::$m_general_person_importance2wi_tablename
                                        ." i2g on i2g.workitemid=g.id and i2g.personid=$about_personid and i2g.created_by_personid=$created_by_personid"
                                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename 
                                        . " d2w on d2w.workitemid=g.id and d2w.personid=$about_personid";
                    $importance_expression = "COALESCE(i2g.importance,g.importance) as importance";
                    $custom_importance_value = "i2g.importance as custom_importance";
                    $primary_owner_expression = "(g.owner_personid = $about_personid) as is_primary_owner";
                } else {
                    $importance_expression = "importance";
                    $custom_importance_value = "NULL as custom_importance";
                    $primary_owner_expression = "NULL as is_primary_owner";
                }
                
                $order_ar[] = "c.workitemid";
                
                $where_ar[] = "c.id IN $open_comids_tx";
                $where_ar[] = "g.active_yn=1";
                $where_ar[] = "g.id IS NOT NULL";
                $where_ar[] = "COALESCE(c.action_reply_cd,'') <> 'RSVLD'";
                if(count($where_ar)>0)
                {
                    $sWhereClause = " WHERE " . implode(" and ", $where_ar);
                } else {
                    $sWhereClause = "";
                }
                $sOrderByClause = " ORDER BY " . implode(", ", $order_ar);
                $sSQL = "SELECT 
                            owner_projectid, c.workitemid as workitemid, 
                            @arcc := COALESCE(pc.action_requested_concern,0) as action_requested_concern,
                            c.action_reply_cd, g.importance as default_importance, $custom_importance_value,
                            @importance := $importance_expression,
                            @is_primary_owner := $primary_owner_expression, 
                            @effort_hours := COALESCE(effort_hours_est,0) as effort_hours, 
                            @hours_worked := COALESCE(effort_hours_worked_act, effort_hours_worked_est, 0) as hours_worked,
                            @remaining_hours := @effort_hours - @hours_worked as remaining_hours,
                            @effective_end_dt := COALESCE(actual_end_dt, planned_end_dt, '$today_dt') as effective_end_dt,
                            @remaining_days := datediff(@effective_end_dt, '$today_dt') as remaining_days,
                            @fte_hours_per_day := 8 as fte_hours_per_day,
                            @min_hours_per_day := (COALESCE(effort_hours_est,0) - @hours_worked) / @remaining_days as min_hours_per_day,
                            @no_more_days := if(@remaining_days < 0,1,0) as no_more_days,
                            @computed_min_fte := if(@no_more_days, 100*planned_fte_count, @remaining_hours / @remaining_days / @fte_hours_per_day) as computed_min_fte,
                            planned_fte_count,
                            @fte_buffer := planned_fte_count - @computed_min_fte as fte_buffer,
                            @is_urgent := if(@no_more_days OR @fte_buffer < planned_fte_count, 1, 0) as is_urgent,
                            @effective_importance := if(@arcc<=10, 11, if(@arcc>=30, 88, @importance)) as effective_importance,
                            @tmm_quadrant := if(@effective_importance > 50,if(@is_urgent, 1, 2),if(@is_urgent, 3, 4)) as tmm_quadrant
                          "
                        . "\nFROM " . DatabaseNamesHelper::$m_workitem_communication_tablename . " c"
                        . "\nLEFT JOIN " . DatabaseNamesHelper::$m_workitem_communication_tablename . " pc on pc.id=c.parent_comid"
                        . "\nLEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on g.id=c.workitemid"
                        . "\n$joinstuff_txt"
                        . "\n$sWhereClause"
                        . "\n$sOrderByClause";

                //$bundle['sql'] = $sSQL;
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc())
                {
                    $projectid = $record['owner_projectid'];
                    $tmm_quadrant = $record['tmm_quadrant'];
                    $quadcode = "Q$tmm_quadrant";
                    $summary['tmm'][$quadcode] += 1;
                    if(!array_key_exists($projectid, $by_project))
                    {
                        $by_project[$projectid]['tmm']['Q1'] = 0;
                        $by_project[$projectid]['tmm']['Q2'] = 0;
                        $by_project[$projectid]['tmm']['Q3'] = 0;
                        $by_project[$projectid]['tmm']['Q4'] = 0;
                    }
                    $by_project[$projectid]['tmm'][$quadcode] += 1;
                    if($includedetail)
                    {
                        $by_project[$projectid]['detail'][] = $record;
                    }
                }
            }
            
            $bundle['summary'] = $summary;
            $bundle['by_project'] = $by_project;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Compute the priority factors for all action requests fitting the filter criteria
     */
    public function getSprintCategorizationBundle($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            
            $today_dt = date("Y-m-d", time());
            $summary = [];
            $importance_expression = "";
            $joinstuff_txt = "";
            $bundle = [];
            $core_filters = [];
            $core_filters['includedetail'] = $includedetail;
            $core_filters['owner_projectid'] = $owner_projectid;
            $core_filters['about_personid'] = $about_personid;
            $where_ar=[];
            $order_ar=[];
            
            if($owner_projectid !== NULL)
            {
                if(!is_array($owner_projectid))
                {
                    $where_ar[] = "g.owner_projectid=$owner_projectid";
                } else {
                    if(count($owner_projectid) > 0)
                    {
                        $where_ar[] = "g.owner_projectid IN (" . implode(", ", $owner_projectid) . ")";
                    }
                }
                $order_ar[] = "g.owner_projectid";
            }
            
            if($about_personid !== NULL)
            {
                if(empty($created_by_personid))
                {
                    $created_by_personid = $about_personid;
                }
                $core_filters['created_by_personid'] = $created_by_personid;
                $joinstuff_txt = " LEFT JOIN ".DatabaseNamesHelper::$m_general_person_importance2wi_tablename
                                    ." i2g on i2g.workitemid=g.id and i2g.personid=$about_personid and i2g.created_by_personid=$created_by_personid"
                                . " LEFT JOIN " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename 
                                    . " d2w on d2w.workitemid=g.id and d2w.personid=$about_personid";
                $importance_expression = "COALESCE(i2g.importance,g.importance) as importance";
                $custom_importance_value = "i2g.importance as custom_importance";
                $primary_owner_expression = "(g.owner_personid = $about_personid) as is_primary_owner";
            } else {
                $importance_expression = "importance as importance";
                $custom_importance_value = "NULL as custom_importance";
                $primary_owner_expression = "NULL as is_primary_owner";
            }
            $order_ar[] = "g.id";
            $where_ar[] = "g.active_yn=1";
            $where_ar[] = "COALESCE(ss.terminal_yn,0)<>1";
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sOrderByClause = " ORDER BY " . implode(", ", $order_ar);
            $sSQL = "SELECT 
                        g.owner_projectid, s.id as sprintid, s.end_dt, w2s.workitemid, g.importance as default_importance, $custom_importance_value,
                        @importance := $importance_expression,
                        @is_primary_owner := $primary_owner_expression, 
                        @effort_hours := COALESCE(effort_hours_est,0) as effort_hours, 
                        @hours_worked := COALESCE(effort_hours_worked_act, effort_hours_worked_est, 0) as hours_worked,
                        @remaining_hours := @effort_hours - @hours_worked as remaining_hours,
                        @candidate_end_dt := COALESCE(actual_end_dt, planned_end_dt, '$today_dt') as candidate_end_dt,
                        @effective_end_dt := if(@candidate_end_dt < s.end_dt, @candidate_end_dt, s.end_dt) as effective_end_dt,
                        @remaining_days := datediff(@effective_end_dt, '$today_dt') as remaining_days,
                        @fte_hours_per_day := 8 as fte_hours_per_day,
                        @min_hours_per_day := (COALESCE(effort_hours_est,0) - @hours_worked) / @remaining_days as min_hours_per_day,
                        @no_more_days := if(@remaining_days < 0,1,0) as no_more_days,
                        @computed_min_fte := if(@no_more_days, 100*planned_fte_count, @remaining_hours / @remaining_days / @fte_hours_per_day) as computed_min_fte,
                        planned_fte_count,
                        @fte_buffer := planned_fte_count - @computed_min_fte as fte_buffer,
                        @is_urgent := if(@no_more_days OR @fte_buffer < planned_fte_count, 1, 0) as is_urgent,
                        @tmm_quadrant := if(@importance > 50,if(@is_urgent, 1, 2),if(@is_urgent, 3, 4)) as tmm_quadrant
                      "
                    . "\nFROM ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename." w2s"
                    . "\nLEFT JOIN ".DatabaseNamesHelper::$m_sprint_tablename ." s on w2s.sprintid=s.id "
                    . "\nLEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." ss on ss.code=s.status_cd"
                    . "\nLEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on g.id=w2s.workitemid"
                    . "\n$joinstuff_txt"
                    . "\n$sWhereClause"
                    . "\n$sOrderByClause";
            
            //$bundle['sql'] = $sSQL;
            $bundle['today_dt'] = $today_dt;
            $bundle['filters'] = $core_filters;
            $by_project = [];
            $summary['tmm']['Q1'] = 0;
            $summary['tmm']['Q2'] = 0;
            $summary['tmm']['Q3'] = 0;
            $summary['tmm']['Q4'] = 0;
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc())
            {
                $projectid = $record['owner_projectid'];
                $tmm_quadrant = $record['tmm_quadrant'];
                $quadcode = "Q$tmm_quadrant";
                $summary['tmm'][$quadcode] += 1;
                if(!array_key_exists($projectid, $by_project))
                {
                    $by_project[$projectid]['tmm']['Q1'] = 0;
                    $by_project[$projectid]['tmm']['Q2'] = 0;
                    $by_project[$projectid]['tmm']['Q3'] = 0;
                    $by_project[$projectid]['tmm']['Q4'] = 0;
                }
                $by_project[$projectid]['tmm'][$quadcode] += 1;
                if($includedetail)
                {
                    $by_project[$projectid]['detail'][] = $record;
                }
            }
            $bundle['summary'] = $summary;
            $bundle['by_project'] = $by_project;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of Workitem Time Management Matrix
     */
    public function getWorkitemTMMCountsByCategory4Person($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            return $this->getWorkitemCategorizationBundle($owner_projectid, $about_personid, $created_by_personid, $includedetail);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of Workitem Time Management Matrix
     */
    public function getOpenActionRequestTMMCountsByCategory4Person($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            return $this->getActionRequestCategorizationBundle($owner_projectid, $about_personid, $created_by_personid, $includedetail);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of Workitem Time Management Matrix
     */
    public function getSprintTMMCountsByCategory4Person($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            return $this->getSprintCategorizationBundle($owner_projectid, $about_personid, $created_by_personid, $includedetail);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of influence in workitems where workitemid is the key
     */
    public function getWorkitem2ImportanceCountsByCategory4Person($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            if(empty($about_personid))
            {
                throw new \Exception("Must declare person!");
            }
            $bundle = [];
            $where_ar=[];
            $join_i2g_ar=[];
            $join_d2w_ar=[];
            $where_ar[] = "g.id IS NOT NULL";
            if($owner_projectid !== NULL)
            {
                if(!is_array($owner_projectid))
                {
                    $where_ar[] = "g.owner_projectid=$owner_projectid";
                } else {
                    if(count($owner_projectid) > 0)
                    {
                        $where_ar[] = "g.owner_projectid IN (" . implode(", ", $owner_projectid) . ")";
                    }
                }
            }
            if($created_by_personid !== NULL)
            {
                $join_i2g_ar[] = "i2g.created_by_personid=$created_by_personid";
            }
            if($about_personid !== NULL)
            {
                $where_ar[] = "g.owner_personid=$about_personid";
                $join_i2g_ar[] = "i2g.personid=$about_personid";
                $join_d2w_ar[] = "$about_personid=d2w.personid";
            }
            $join_i2g_txt = " and " . implode(" and ", $join_i2g_ar);
            $join_d2w_txt = " and " . implode(" and ", $join_d2w_ar);
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sSQL_importance = "SELECT g.owner_projectid, "
                    . " sum(if(i2g.importance > 75, 1, 0)) as level4, "
                    . " sum(if(i2g.importance > 50 and i2g.importance <= 75, 1, 0)) as level3, "
                    . " sum(if(i2g.importance > 25 and i2g.importance <= 50, 1, 0)) as level2, "
                    . " sum(if(i2g.importance > 0 and i2g.importance <= 25, 1, 0)) as level1, "
                    . " sum(if(i2g.importance IS NOT NULL and i2g.importance = 0, 1, 0)) as zero, "
                    . " sum(if(i2g.importance IS NULL, 1, 0)) as unknown "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_general_person_importance2wi_tablename." i2g on i2g.workitemid=g.id $join_i2g_txt"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " d2w on d2w.workitemid=g.id $join_d2w_txt"
                    . " $sWhereClause"
                    . " GROUP BY g.owner_projectid ";
            //$bundle['sql'] = $sSQL_importance;
//drupal_set_message("LOOK sSQL_importance=$sSQL_importance");            
            $by_project = [];
            $summary = [];
            $summary['level4'] = 0;
            $summary['level3'] = 0;
            $summary['level2'] = 0;
            $summary['level1'] = 0;
            $summary['zero'] = 0;
            $summary['unknown'] = 0;
            $result_importance = db_query($sSQL_importance);
            while($record_importance = $result_importance->fetchAssoc())
            {
                $projectid = $record_importance['owner_projectid'];
                $summary['level4'] += $record_importance['level4'];
                $summary['level3'] += $record_importance['level3'];
                $summary['level2'] += $record_importance['level2'];
                $summary['level1'] += $record_importance['level1'];
                $summary['zero'] += $record_importance['zero'];
                $summary['unknown'] += $record_importance['unknown'];
                if(!array_key_exists($projectid, $by_project))
                {
                    $by_project[$projectid]['levels']['level4'] = 0;
                    $by_project[$projectid]['levels']['level3'] = 0;
                    $by_project[$projectid]['levels']['level2'] = 0;
                    $by_project[$projectid]['levels']['level1'] = 0;
                    $by_project[$projectid]['levels']['zero'] = 0;
                    $by_project[$projectid]['levels']['unknown'] = 0;
                }
                $by_project[$projectid]['levels']['level4'] += $record_importance['level4'];
                $by_project[$projectid]['levels']['level3'] += $record_importance['level3'];
                $by_project[$projectid]['levels']['level2'] += $record_importance['level2'];
                $by_project[$projectid]['levels']['level1'] += $record_importance['level1'];
                $by_project[$projectid]['levels']['zero'] += $record_importance['zero'];
                $by_project[$projectid]['levels']['unknown'] += $record_importance['unknown'];
                if($includedetail)
                {
                    $by_project[$projectid]['detail'][] = $record_importance;
                }
            }
            $bundle['summary']['levels'] = $summary;
            $bundle['by_project'] = $by_project;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of influence in workitems where workitemid is the key
     */
    public function getWorkitem2InfluenceCountsByCategory4Person($owner_projectid=NULL
            , $about_personid=NULL, $created_by_personid=NULL, $includedetail=FALSE)
    {
        try
        {
            if(empty($about_personid))
            {
                throw new \Exception("Must declare person!");
            }
            $bundle = [];
            $where_ar=[];
            $join_i2g_ar=[];
            $join_d2w_ar=[];
            $where_ar[] = "g.id IS NOT NULL";
            if($owner_projectid !== NULL)
            {
                if(!is_array($owner_projectid))
                {
                    $where_ar[] = "g.owner_projectid=$owner_projectid";
                } else {
                    if(count($owner_projectid) > 0)
                    {
                        $where_ar[] = "g.owner_projectid IN (" . implode(", ", $owner_projectid) . ")";
                    }
                }
            }
            if($created_by_personid !== NULL)
            {
                $join_i2g_ar[] = "i2g.created_by_personid=$created_by_personid";
            }
            if($about_personid !== NULL)
            {
                $where_ar[] = "g.owner_personid=$about_personid";
                $join_i2g_ar[] = "i2g.personid=$about_personid";
                $join_d2w_ar[] = "$about_personid=d2w.personid";
            }
            $join_i2g_txt = " and " . implode(" and ", $join_i2g_ar);
            $join_d2w_txt = " and " . implode(" and ", $join_d2w_ar);
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sSQL_influence = "SELECT g.owner_projectid, "
                    . " sum(if(i2g.influence > 75, 1, 0)) as level4, "
                    . " sum(if(i2g.influence > 50 and i2g.influence <= 75, 1, 0)) as level3, "
                    . " sum(if(i2g.influence > 25 and i2g.influence <= 50, 1, 0)) as level2, "
                    . " sum(if(i2g.influence > 0 and i2g.influence <= 25, 1, 0)) as level1, "
                    . " sum(if(i2g.influence IS NOT NULL and i2g.influence = 0, 1, 0)) as zero, "
                    . " sum(if(i2g.influence IS NULL, 1, 0)) as unknown "
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_general_person_influence2wi_tablename." i2g on i2g.workitemid=g.id $join_i2g_txt"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " d2w on d2w.workitemid=g.id $join_d2w_txt"
                    . " $sWhereClause"
                    . " GROUP BY g.owner_projectid ";
            //$bundle['sql'] = $sSQL_influence;
            $by_project = [];
            $summary = [];
            $summary['level4'] = 0;
            $summary['level3'] = 0;
            $summary['level2'] = 0;
            $summary['level1'] = 0;
            $summary['zero'] = 0;
            $summary['unknown'] = 0;
            $result_influence = db_query($sSQL_influence);
            while($record_influence = $result_influence->fetchAssoc())
            {
                $projectid = $record_influence['owner_projectid'];
                $summary['level4'] += $record_influence['level4'];
                $summary['level3'] += $record_influence['level3'];
                $summary['level2'] += $record_influence['level2'];
                $summary['level1'] += $record_influence['level1'];
                $summary['zero'] += $record_influence['zero'];
                $summary['unknown'] += $record_influence['unknown'];
                if(!array_key_exists($projectid, $by_project))
                {
                    $by_project[$projectid]['levels']['level4'] = 0;
                    $by_project[$projectid]['levels']['level3'] = 0;
                    $by_project[$projectid]['levels']['level2'] = 0;
                    $by_project[$projectid]['levels']['level1'] = 0;
                    $by_project[$projectid]['levels']['zero'] = 0;
                    $by_project[$projectid]['levels']['unknown'] = 0;
                }
                $by_project[$projectid]['levels']['level4'] += $record_influence['level4'];
                $by_project[$projectid]['levels']['level3'] += $record_influence['level3'];
                $by_project[$projectid]['levels']['level2'] += $record_influence['level2'];
                $by_project[$projectid]['levels']['level1'] += $record_influence['level1'];
                $by_project[$projectid]['levels']['zero'] += $record_influence['zero'];
                $by_project[$projectid]['levels']['unknown'] += $record_influence['unknown'];
                if($includedetail)
                {
                    $by_project[$projectid]['detail'][] = $record_influence;
                }
            }
            $bundle['summary']['levels'] = $summary;
            $bundle['by_project'] = $by_project;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of influence in workitems where workitemid is the key
     */
    public function getWorkitem2InfluenceList($owner_projectid=NULL, $about_personid=NULL, $created_by_personid=NULL)
    {
        try
        {
            $bundle = [];
            $others_assess_map = [];
            $self_assess_map = [];
            $where_ar=[];
            $where_ar[] = "g.id IS NOT NULL";
            if($owner_projectid !== NULL)
            {
                $where_ar[] = "g.owner_projectid=$owner_projectid";
            }
            if($created_by_personid !== NULL)
            {
                $where_ar[] = "i2g.created_by_personid=$created_by_personid";
            }
            if($about_personid !== NULL)
            {
                $where_ar[] = "i2g.personid=$about_personid";
            }
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sSQL_influence = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.influence, i2g.created_by_personid "
                    . " FROM ".DatabaseNamesHelper::$m_general_person_influence2wi_tablename." i2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on i2g.workitemid=g.id"
                    . " $sWhereClause";
            $result_influence = db_query($sSQL_influence);
            while($record_influence = $result_influence->fetchAssoc())
            {
                $workitemid = $record_influence['workitemid'];
                $personid = $record_influence['personid'];
                $created_by_personid = $record_influence['created_by_personid'];
                $rawinfluencevalue = $record_influence['influence'];
                $record_influence['influence_level'] = \bigfathom\UtilityGeneralFormulas::getInfluenceLevelFromRawValue($rawinfluencevalue);
                if($personid == $created_by_personid)
                {
                    $self_assess_map[$workitemid] = $record_influence;
                } else {
                    if(!isset($others_assess_map[$workitemid]))
                    {
                        $others_assess_map[$workitemid] = [];
                        $others_assess_map[$workitemid]['detail'] = [];
                    }
                    $others_assess_map[$workitemid]['detail'][] = $record_influence;
                    if(!isset($others_assess_map[$workitemid]['sum']))
                    {
                        $others_assess_map[$workitemid]['sum'] = $rawinfluencevalue;
                        $others_assess_map[$workitemid]['count'] = 1;
                    } else {
                        $others_assess_map[$workitemid]['sum'] += $rawinfluencevalue;
                        $others_assess_map[$workitemid]['count'] += 1;
                    }
                }
            }
            foreach($others_assess_map as $workitemid=>$detail)
            {
                $sum = $others_assess_map[$workitemid]['sum'];
                $count = $others_assess_map[$workitemid]['count'];
                $mean = $sum / $count;
                $others_assess_map[$workitemid]['mean'] =$mean;
                $others_assess_map[$workitemid]['influence_level'] = \bigfathom\UtilityGeneralFormulas::getInfluenceLevelFromRawValue($mean);
            }
            $bundle['assessment_map']['self'] = $self_assess_map;
            $bundle['assessment_map']['others'] = $others_assess_map;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return map of importance in workitems where workitemid is the key
     */
    public function getWorkitem2ImportanceList($owner_projectid=NULL, $about_personid=NULL, $created_by_personid=NULL)
    {
        try
        {
            $bundle = [];
            $others_assess_map = [];
            $self_assess_map = [];
            $where_ar=[];
            $where_ar[] = "g.id IS NOT NULL";
            if($owner_projectid !== NULL)
            {
                $where_ar[] = "g.owner_projectid=$owner_projectid";
            }
            if($created_by_personid !== NULL)
            {
                $where_ar[] = "i2g.created_by_personid=$created_by_personid";
            }
            if($about_personid !== NULL)
            {
                $where_ar[] = "i2g.personid=$about_personid";
            }
            if(count($where_ar)>0)
            {
                $sWhereClause = " WHERE " . implode(" and ", $where_ar);
            } else {
                $sWhereClause = "";
            }
            $sSQL_importance = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.importance, i2g.created_by_personid "
                    . " FROM ".DatabaseNamesHelper::$m_general_person_importance2wi_tablename." i2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on i2g.workitemid=g.id"
                    . " $sWhereClause";
            $result_importance = db_query($sSQL_importance);
            while($record_importance = $result_importance->fetchAssoc())
            {
                $workitemid = $record_importance['workitemid'];
                $personid = $record_importance['personid'];
                $created_by_personid = $record_importance['created_by_personid'];
                $rawimportancevalue = $record_importance['importance'];
                $record_importance['importance_level'] = \bigfathom\UtilityGeneralFormulas::getImportanceLevelFromRawValue($rawimportancevalue);
                if($personid == $created_by_personid)
                {
                    $self_assess_map[$workitemid] = $record_importance;
                } else {
                    if(!isset($others_assess_map[$workitemid]))
                    {
                        $others_assess_map[$workitemid] = [];
                        $others_assess_map[$workitemid]['detail'] = [];
                    }
                    $others_assess_map[$workitemid]['detail'][] = $record_importance;
                    if(!isset($others_assess_map[$workitemid]['sum']))
                    {
                        $others_assess_map[$workitemid]['sum'] = $rawimportancevalue;
                        $others_assess_map[$workitemid]['count'] = 1;
                    } else {
                        $others_assess_map[$workitemid]['sum'] += $rawimportancevalue;
                        $others_assess_map[$workitemid]['count'] += 1;
                    }
                }
            }
            foreach($others_assess_map as $workitemid=>$detail)
            {
                $sum = $others_assess_map[$workitemid]['sum'];
                $count = $others_assess_map[$workitemid]['count'];
                $mean = $sum / $count;
                $others_assess_map[$workitemid]['mean'] =$mean;
                $others_assess_map[$workitemid]['importance_level'] = \bigfathom\UtilityGeneralFormulas::getImportanceLevelFromRawValue($mean);
            }
            $bundle['assessment_map']['self'] = $self_assess_map;
            $bundle['assessment_map']['others'] = $others_assess_map;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the time quadrant context
     */
    public function getTimeQuadrantContextBundle($owner_projectid, $active_yn=1)
    {
        try
        {
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot get sprint context bundle without a projectid value!");
            }

            $bundle = $this->getAllWorkitemsInProjectBundle($owner_projectid, $active_yn);
            $bundle['status_lookup'] = $this->getWorkitemStatusByCode();

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map 
     */
    public function getSubprojects2ProjectMapBundle($owner_projectid, $active_yn=1)
    {
        try
        {
            $all_workitembundle = $this->getAllWorkitemsInProjectBundle($owner_projectid, $active_yn);
            $map_connected_subproject_wids = $this->getListAsMap($all_workitembundle['lists']["connected_subproject_wids"]);
            $map_disconnected_subproject_wids = $this->getListAsMap($all_workitembundle['lists']["disconnected_subproject_wids"]);
            $map_p2sp = [];
            $map_sp2p = [];
            $map_sp2w = [];
            $sSQL_members = "SELECT"
                    . " sp2p.subprojectid, sp2p.projectid, sp2p.ot_scf, sp.root_goalid "
                    . " FROM ".DatabaseNamesHelper::$m_map_subproject2project_tablename." sp2p"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_project_tablename." p on sp2p.projectid=p.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_project_tablename." sp on sp2p.subprojectid=sp.id"
                    . " WHERE p.id=$owner_projectid and p.id IS NOT NULL";
            if($active_yn !== NULL)
            {
                $sSQL_members .= " and p.active_yn=$active_yn and sp.active_yn=$active_yn";
            }
            $result_members = db_query($sSQL_members);
            while($record_members = $result_members->fetchAssoc())
            {
                $projectid = $record_members['projectid'];
                $subprojectid = $record_members['subprojectid'];
                $root_goalid = $record_members['root_goalid'];
                $ot_scf = $record_members['ot_scf'];
                $is_connected = array_key_exists($root_goalid, $map_connected_subproject_wids);
                $detail = array(
                            "ot_scf"=>$ot_scf,
                            "is_connected"=>$is_connected
                        );
                if(!array_key_exists($subprojectid, $map_p2sp))
                {
                    $map_p2sp[$subprojectid] = [];
                }
                $map_p2sp[$subprojectid][$projectid] = $detail;
                if(!array_key_exists($projectid, $map_sp2p))
                {
                    $map_sp2p[$projectid] = [];
                }
                $map_sp2p[$projectid][$subprojectid] = $detail;
                $map_sp2w[$subprojectid] = $root_goalid;
            }
            $bundle = [];
            $bundle['project2subproject'] = $map_p2sp;
            $bundle['subproject2project'] = $map_sp2p;
            $bundle['subproject2root_goalid'] = $map_sp2w;
            $bundle['map_connected_subproject_wids'] = $map_connected_subproject_wids;
            $bundle['map_disconnected_subproject_wids'] = $map_disconnected_subproject_wids;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
   
    /**
     * Return map of workitems in each sprint
     */
    public function getSprint2WorkitemMapBundle($owner_projectid)
    {
        return $this->getWorkitem2SprintMapBundle($owner_projectid);
    }
    
    /**
     * Return map of workitems in each sprint
     * The mapping includes OTSP value
     */
    public function getWorkitem2SprintMapBundle($owner_projectid, $only_open_sprints=FALSE)
    {
        try
        {
            $sprintid2dates = [];
            $sprintid2status = [];
            $sprintid2iteration = [];
            $map_s2w = [];
            $map_w2s = [];
            $sSQL_members = "SELECT"
                    . " s.id as sprintid, s.iteration_ct, g2s.sprintid, g2s.workitemid, g2s.ot_scf, s.status_cd, ss.terminal_yn "
                    . " ,s.start_dt, s.end_dt "
                    . " ,w.planned_start_dt, w.actual_start_dt, w.planned_end_dt, w.actual_end_dt "
                    . " FROM ".DatabaseNamesHelper::$m_map_workitem2sprint_tablename." g2s"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_tablename." s on g2s.sprintid=s.id"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_status_tablename . " ss on ss.code=s.status_cd"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename . " w on w.id=g2s.workitemid"
                    . " WHERE s.owner_projectid=$owner_projectid and s.id IS NOT NULL";
            if($only_open_sprints)
            {
                $sSQL_members .= " and ss.terminal_yn<>1";
            }
            $result_members = db_query($sSQL_members);
            
            while($record_members = $result_members->fetchAssoc())
            {
                $sprintid = $record_members['sprintid'];
                $s_sdt = $record_members['start_dt'];
                $s_edt = $record_members['end_dt'];
                $iteration_ct = $record_members['iteration_ct'];;
                $workitemid = $record_members['workitemid'];
                $declared_in_sprint_otsp = $record_members['ot_scf'];
                $status_cd = $record_members['status_cd'];
                $terminal_yn = $record_members['terminal_yn'];
                if(!array_key_exists($workitemid, $map_s2w))
                {
                    $map_s2w[$workitemid] = [];
                }
                $witemdetail = [];
                $witemdetail['declarations'] = [];
                $witemdetail['declarations']['isotsp'] = $declared_in_sprint_otsp;
                $witemdetail['declarations']['sdt'] = empty($record_members['actual_start_dt']) ? $record_members['planned_start_dt'] : $record_members['actual_start_dt'];
                $witemdetail['declarations']['edt'] = empty($record_members['actual_end_dt']) ? $record_members['planned_end_dt'] : $record_members['actual_end_dt'];
                $map_s2w[$workitemid][$sprintid] = $witemdetail;
                if(!array_key_exists($sprintid, $map_w2s))
                {
                    $map_w2s[$sprintid] = [];
                }
                $map_w2s[$sprintid][$workitemid] = $witemdetail;
                $sprintid2iteration[$sprintid] = $iteration_ct;
                $sprintid2dates[$sprintid] = array('sdt'=>$s_sdt,'edt'=>$s_edt);
                $sprintid2status[$sprintid] = array('status_cd'=>$status_cd,'terminal_yn'=>$terminal_yn);
            }
            $bundle = [];
            $bundle['filter']['only_open_sprints'] = $only_open_sprints;
            $bundle['sprintid2dates'] = $sprintid2dates;
            $bundle['sprintid2iteration'] = $sprintid2iteration;
            $bundle['sprintid2status'] = $sprintid2status;
            $bundle['sprint2workitem'] = $map_s2w;
            $bundle['workitem2sprint'] = $map_w2s;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of workitem suggestions for each sprint
     */
    public function getWorkitem2SprintSuggestionList($owner_projectid, $suggested_by_uid)
    {
        try
        {
            $map = array();
            $sSQL_members = "SELECT"
                    . " s.id, g2s.sprintid, g2s.workitemid "
                    . " FROM ".DatabaseNamesHelper::$m_suggest_map_workitem2sprint_tablename." g2s"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_tablename." s on g2s.sprintid=s.id"
                    . " WHERE s.owner_projectid=$owner_projectid and g2s.created_by_personid=$suggested_by_uid and s.id IS NOT NULL";
            $result_members = db_query($sSQL_members);
            while($record_members = $result_members->fetchAssoc())
            {
                $sprintid = $record_members['sprintid'];
                $workitemid = $record_members['workitemid'];
                $map[$workitemid] = $sprintid;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of goals in each person insight
     */
    public function getWorkitem2InsightSuggestionList($owner_projectid, $suggested_by_uid, $about_personid=NULL)
    {
        try
        {
            $map = array();
            $sWhere = "g.owner_projectid=$owner_projectid and i2g.created_by_personid=$suggested_by_uid and g.id IS NOT NULL";
            if($about_personid !== NULL)
            {
                $sWhere .= " and personid=$about_personid";
            }
            $sSQL_insight = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.insight "
                    . " FROM ".DatabaseNamesHelper::$m_suggest_person_insight2wi_tablename." i2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on i2g.goalid=g.id and g.workitem_basetype='G'"
                    . " WHERE $sWhere";
            $result_insight = db_query($sSQL_insight);
            while($record_insight = $result_insight->fetchAssoc())
            {
                $workitemid = $record_insight['workitemid'];
                $rawinsightvalue = $record_insight['insight'];
                $record_insight['insight_level'] = \bigfathom\UtilityGeneralFormulas::getInsightLevelFromRawValue($rawinsightvalue);
                $map[$workitemid] = $record_insight;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of goals in each person influence
     */
    public function getWorkitem2InfluenceSuggestionList($owner_projectid, $suggested_by_uid, $about_personid=NULL)
    {
        try
        {
            $map = array();
            $sWhere = "g.owner_projectid=$owner_projectid and i2g.created_by_personid=$suggested_by_uid and g.id IS NOT NULL";
            if($about_personid !== NULL)
            {
                $sWhere .= " and personid=$about_personid";
            }
            $sSQL_influence = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.influence "
                    . " FROM " . DatabaseNamesHelper::$m_suggest_person_influence2wi_tablename . " i2g"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " g on i2g.workitemid=g.id"
                    . " WHERE $sWhere";
            $result_influence = db_query($sSQL_influence);
            while($record_influence = $result_influence->fetchAssoc())
            {
                $workitemid = $record_influence['workitemid'];
                $rawinfluencevalue = $record_influence['influence'];
                $record_influence['influence_level'] = \bigfathom\UtilityGeneralFormulas::getInfluenceLevelFromRawValue($rawinfluencevalue);
                $map[$workitemid] = $record_influence;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return map of goals in each person influence
     */
    public function getWorkitem2ImportanceSuggestionList($owner_projectid, $suggested_by_uid, $about_personid=NULL)
    {
        try
        {
            $map = array();
            $sWhere = "g.owner_projectid=$owner_projectid and i2g.created_by_personid=$suggested_by_uid and g.id IS NOT NULL";
            if($about_personid !== NULL)
            {
                $sWhere .= " and personid=$about_personid";
            }
            $sSQL_importance = "SELECT"
                    . " i2g.personid, i2g.workitemid, i2g.importance "
                    . " FROM ".DatabaseNamesHelper::$m_suggest_person_importance2wi_tablename." i2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on i2g.workitemid=g.id"
                    . " WHERE $sWhere";
            $result_importance = db_query($sSQL_importance);
            while($record_importance = $result_importance->fetchAssoc())
            {
                $workitemid = $record_importance['workitemid'];
                $rawimportancevalue = $record_importance['importance'];
                $record_importance['tmm_quadrant'] = UtilityGeneralFormulas::getTimeManagementMatrixQuadrantFromRawValues($rawimportancevalue);
                $map[$workitemid] = $record_importance;
            }
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich result in the insight context
     */
    public function getInsightContextBundle($owner_projectid, $uid_suggestion_filter=NULL, $about_personid=NULL)
    {
        try
        {
            $bundle = array();
            
            if($uid_suggestion_filter === NULL)
            {
                global $user;
                $uid_suggestion_filter = $user->uid;
            }
            if($about_personid === NULL)
            {
                $about_personid = $uid_suggestion_filter;
            }
            
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot get insight context bundle without a projectid value!");
            }

            $relevant_unmapped_goals = $this->getCandidateWorkitemsInProjectByID($owner_projectid);
            $bundle['candidate_goals'] = $relevant_unmapped_goals;
            
            $bundle['people'] = $this->getPersonsInProjectByID($owner_projectid);
            $relevant_workitems = $this->getAllWorkitemsInProjectBundle($owner_projectid);
            $bundle['workitems'] = $relevant_workitems;
            $bundle['workitem2insight'] = $this->getWorkitem2InsightList($owner_projectid, $about_personid);
            $bundle['suggested_workitem2insight'] = $this->getWorkitem2InsightSuggestionList($owner_projectid, $uid_suggestion_filter, $about_personid);

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich result in the influence context
     */
    public function getInfluenceContextBundle($owner_projectid, $uid_suggestion_filter=NULL, $about_personid=NULL)
    {
        try
        {
            $bundle = array();
            
            if($uid_suggestion_filter === NULL)
            {
                global $user;
                $uid_suggestion_filter = $user->uid;
            }
            if($about_personid === NULL)
            {
                $about_personid = $uid_suggestion_filter;
            }
            
            if($owner_projectid === NULL)
            {
                throw new \Exception("Cannot get influence context bundle without a projectid value!");
            }

            $relevant_unmapped_goals = $this->getCandidateWorkitemsInProjectByID($owner_projectid);
            $bundle['candidate_goals'] = $relevant_unmapped_goals;
            
            $bundle['people'] = $this->getPersonsInProjectByID($owner_projectid);
            $relevant_goals = $this->getAllWorkitemsInProjectBundle($owner_projectid);
            $bundle['goals'] = $relevant_goals;
            $bundle['goal2influence'] = $this->getWorkitem2InfluenceList($owner_projectid, $about_personid);
            $bundle['suggested_goal2influence'] = $this->getWorkitem2InfluenceSuggestionList($owner_projectid, $uid_suggestion_filter, $about_personid);

            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectSprintInfoByID($found_projectid_map)
    {
        try
        {
            $allsprints = [];
            
            foreach($found_projectid_map as $owner_projectid)
            {
                $ops = $this->getSprintsByID($owner_projectid);
                foreach($ops as $sprintid=>$detail)
                {
                    $allsprints[$sprintid] = $detail;
                }
            }
            
            return $allsprints;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return sprint item detail with ID as the key or NO KEY
     */
    public function getSprintsByID($owner_projectid=NULL,$order_by_ar=NULL,$include_key=TRUE)
    {
        try
        {
            $where_ar = [];
            $themap = array();
            $sSQL = "SELECT"
                    . " u.owner_projectid, u.id, u.iteration_ct, u.title_tx, u.story_tx,"
                    . " u.start_dt, u.end_dt,"
                    . " u.owner_personid,"
                    . " u.status_cd,"
                    . " u.status_set_dt,"
                    . " u.official_score, u.score_body_tx,"
                    . " u.active_yn, u.updated_dt, u.created_dt, "
                    . " ss.locked_yn, ss.terminal_yn, ss.happy_yn "
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." u"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_status_tablename." ss on ss.code=u.status_cd";
            if(!empty($owner_projectid))
            {
                $where_ar[] = "u.owner_projectid=$owner_projectid";
            }
            if(count($where_ar)>0)
            {
                $sSQL .= " WHERE " . implode(" and ", $where_ar);
            }
            if($order_by_ar == NULL)
            {
                $sSQL .= " ORDER BY u.iteration_ct, u.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
            
            $result = db_query($sSQL);
            if($include_key)
            {
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $record['start_dt'] = self::justDate($record['start_dt']);
                    $record['end_dt'] = self::justDate($record['end_dt']);
                    $record['name'] = 'Sprint#'.$record['iteration_ct'];
                    $record['duration_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($record['start_dt'], $record['end_dt']);
                    $themap[$id] = $record; 
                }            
            } else {
                //No key
                while($record = $result->fetchAssoc()) 
                {
                    $record['start_dt'] = self::justDate($record['start_dt']);
                    $record['end_dt'] = self::justDate($record['end_dt']);
                    $record['name'] = 'Sprint#'.$record['iteration_ct'];
                    $record['duration_days'] = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($record['start_dt'], $record['end_dt']);
                    $themap[] = $record; 
                }            
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return holiday item detail with ID as the key
     */
    public function getHolidaysByID($owner_countryid=NULL,$owner_stateid=NULL)
    {
        try
        {
            $where_ar = [];
            $themap = array();
            $sSQL = "SELECT "
                    . " h.id, h.holiday_dt, h.holiday_nm, h.countryid, h.stateid, h.comment_tx, h.apply_to_all_users_yn,"
                    . " h.created_by_personid, h.updated_by_personid, h.created_dt, h.updated_dt,"
                    . " c.abbr as country_abbr, c.name as country_name, "
                    . " s.abbr as state_abbr, s.name as state_name"
                     . " FROM ".DatabaseNamesHelper::$m_holiday_tablename." h"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_country_tablename." c on c.id=h.countryid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_state_tablename." s on s.id=h.stateid";
            if(!empty($owner_countryid))
            {
                $where_ar[] = "h.countryid=$owner_countryid";
            }
            if(!empty($owner_stateid))
            {
                $where_ar[] = "h.stateid=$owner_stateid";
            }
            if(count($where_ar)>0)
            {
                $sSQL .= " WHERE " . implode(" and ", $where_ar);
            }
            $sSQL .= " ORDER BY h.holiday_dt, h.holiday_nm, h.countryid, h.stateid";
            
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
     * Return baseline availability item detail with ID as the key
     */
    public function getBaselineAvailabilityByID()
    {
        try
        {
            $where_ar = [];
            $themap = array();
            $sSQL = "SELECT "
                    . " ba.id, ba.is_planning_default_yn, ba.shortname, ba.hours_per_day, "
                    . " work_saturday_yn, work_sunday_yn, work_monday_yn, work_tuesday_yn, work_wednesday_yn, work_thursday_yn, work_friday_yn, "
                    . " work_holidays_yn, comment_tx, updated_by_personid, updated_dt, created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_baseline_availability_tablename." ba";
            $sSQL .= " ORDER BY ba.shortname";
            
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
    
    private static function justDate($datetime)
    {
        $strpos = strpos($datetime, ' ');
        if($strpos > 0)
        {
            return substr($datetime, 0, $strpos);
        }
        return $datetime;
    }
    
    /**
     * Return wi2wi item detail with no key, sorted by depwiid, antwiid
     */
    public function getW2WList($owner_projectid, $goal_ids=NULL, $include_parent_any_project=FALSE, $active_yn=1)
    {
        try
        {
            $filter_criteria = NULL;
            if($owner_projectid !== NULL)
            {
                if($include_parent_any_project)
                {
                    $filter_criteria = "(pg.owner_projectid=$owner_projectid OR sg.owner_projectid=$owner_projectid)";
                } else {
                    $filter_criteria = "(pg.owner_projectid=$owner_projectid OR (pg.owner_projectid IS NULL AND sg.owner_projectid=$owner_projectid))";
                }
            }
            if($goal_ids !== NULL)
            {
                $list = implode(',', $goal_ids);
                if($filter_criteria !== NULL)
                {
                    $filter_criteria .= ' and ';
                }
                $filter_criteria .= "ggm.antwiid in ($list)";
            }
            if($active_yn !== NULL)
            {
                if($filter_criteria !== NULL)
                {
                    $filter_criteria .= ' and ';
                }
                $filter_criteria .= "pg.active_yn=$active_yn and sg.active_yn=$active_yn";
            }
            
            $thelist = array();
            $sSQL = "SELECT"
                    . " ggm.depwiid, ggm.antwiid,"
                    . " pg.owner_projectid, sg.owner_projectid,"
                    . " ggm.created_by_personid, ggm.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_wi2wi_tablename." ggm"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." pg ON pg.id=ggm.depwiid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." sg ON sg.id=ggm.antwiid ";
            if($filter_criteria != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY ggm.depwiid, ggm.antwiid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $thelist[] = $record; 
            }            
            return $thelist;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return template workitem to workitem item detail with no key, sorted by t2g.depwiid, t2g.antwiid
     */
    public function getTW2TWBundle($owner_projectid, $goal_ids=NULL)
    {
        try
        {
            $bundle = [];
            $filter_criteria = NULL;
            if($owner_projectid !== NULL)
            {
                $filter_criteria = "(wi.owner_projectid=$owner_projectid)";
            }
            if($goal_ids !== NULL)
            {
                $list = implode(',', $goal_ids);
                if($filter_criteria !== NULL)
                {
                    $filter_criteria .= ' and ';
                }
                $filter_criteria .= "t2g.depwiid in ($list)";
            }
            
            $sSQL = "SELECT"
                    . " t2g.antwiid, t2g.depwiid,"
                    . " wi.owner_template_projectid as dep_owner_template_projectid, t.owner_template_projectid as ant_owner_template_projectid,"
                    . " t.equipmentid, t.external_resourceid,"
                    . " t2g.created_by_personid, t2g.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_tw2tw_tablename." t2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." wi ON wi.id=t2g.goalid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." t ON t.id=t2g.taskid ";
            if($filter_criteria != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY t2g.depwiid, t2g.antwiid";
            $result = db_query($sSQL);
            
            $plain = [];
            $equjb = [];
            $xrcjb = [];
            while($record = $result->fetchAssoc()) 
            {
                if(!empty($record['equipmentid']))
                {
                    $equjb[] = $record;
                } else
                if(!empty($record['external_resourceid']))
                {
                    $xrcjb[] = $record;
                } else {
                    $plain[] = $record;
                }
            }
            $bundle['plain'] = $plain;
            $bundle['equjb'] = $equjb;
            $bundle['xrcjb'] = $xrcjb;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return wi2wi item detail with no key, sorted by depwiid, antwiid
     */
    public function getTW2TWList($owner_template_projectid, $goal_ids=NULL, $include_parent_any_project=FALSE)
    {
        try
        {
            $filter_criteria = NULL;
            if($owner_template_projectid !== NULL)
            {
                if($include_parent_any_project)
                {
                    $filter_criteria = "(pg.owner_template_projectid=$owner_template_projectid OR sg.owner_template_projectid=$owner_template_projectid)";
                } else {
                    $filter_criteria = "(pg.owner_template_projectid=$owner_template_projectid OR (pg.owner_template_projectid IS NULL AND sg.owner_template_projectid=$owner_template_projectid))";
                }
            }
            if($goal_ids !== NULL)
            {
                $list = implode(',', $goal_ids);
                if($filter_criteria !== NULL)
                {
                    $filter_criteria .= ' and ';
                }
                $filter_criteria .= "ggm.antwiid in ($list)";
            }
            
            $thelist = array();
            $sSQL = "SELECT"
                    . " ggm.depwiid, ggm.antwiid,"
                    . " pg.owner_template_projectid as dep_owner_template_projectid, sg.owner_template_projectid as ant_owner_template_projectid,"
                    . " ggm.created_by_personid, ggm.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_tw2tw_tablename." ggm"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." pg ON pg.id=ggm.depwiid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." sg ON sg.id=ggm.antwiid ";
            if($filter_criteria != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY ggm.depwiid, ggm.antwiid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $thelist[] = $record; 
            }            
            return $thelist;
        } catch (\Exception $ex) {
            DebugHelper::showNeatMarkup($sSQL,"Failed Query",'error');
            throw $ex;
        }
    }
    
    /**
     * Return workitem to workitem item detail with no key, sorted by t2g.depwiid, t2g.antwiid
     */
    public function getW2WBundle($owner_projectid, $goal_ids=NULL)
    {
        try
        {
            $bundle = [];
            $filter_criteria = NULL;
            if($owner_projectid !== NULL)
            {
                $filter_criteria = "(wi.owner_projectid=$owner_projectid)";
            }
            if($goal_ids !== NULL)
            {
                $list = implode(',', $goal_ids);
                if($filter_criteria !== NULL)
                {
                    $filter_criteria .= ' and ';
                }
                $filter_criteria .= "t2g.depwiid in ($list)";
            }
            
            $sSQL = "SELECT"
                    . " t2g.antwiid, t2g.depwiid,"
                    . " wi.owner_projectid as dep_owner_projectid, t.owner_projectid as ant_owner_projectid,"
                    . " t.equipmentid, t.external_resourceid,"
                    . " t2g.created_by_personid, t2g.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_wi2wi_tablename." t2g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." wi ON wi.id=t2g.goalid "
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." t ON t.id=t2g.taskid ";
            if($filter_criteria != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY t2g.depwiid, t2g.antwiid";
            $result = db_query($sSQL);
            
            $plain = [];
            $equjb = [];
            $xrcjb = [];
            while($record = $result->fetchAssoc()) 
            {
                if(!empty($record['equipmentid']))
                {
                    $equjb[] = $record;
                } else
                if(!empty($record['external_resourceid']))
                {
                    $xrcjb[] = $record;
                } else {
                    $plain[] = $record;
                }
            }
            $bundle['plain'] = $plain;
            $bundle['equjb'] = $equjb;
            $bundle['xrcjb'] = $xrcjb;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return role2wi item detail with no key, sorted by antwiid
     */
    public function getRole2WorkitemList($owner_projectid, $workitemid_ar=NULL)
    {
        try
        {
            $filter_criteria_ar = [];
            if($owner_projectid !== NULL)
            {
                $filter_criteria_ar[] = "g.owner_projectid=$owner_projectid";
            }
            $filter_criteria_ar[] = "rgm.active_yn=1";
            if($workitemid_ar !== NULL)
            {
                $list = implode(',', $workitemid_ar);
                $filter_criteria .= "rgm.goalid in ($list)";
                $filter_criteria_ar[] = $filter_criteria;
            }
            $filter_criteria = implode(" and ", $filter_criteria_ar);
            $thelist = array();
            $sSQL = "SELECT"
                    . " rgm.roleid, rgm.workitemid,"
                    . " g.owner_projectid,"
                    . " rgm.created_by_personid, rgm.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_prole2wi_tablename." rgm"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g ON g.id=rgm.workitemid ";
            if(!empty($filter_criteria))
            {
                $sSQL  .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY rgm.workitemid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $thelist[] = $record; 
            }            
            return $thelist;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of project role detail with ID as the key
     */
    public function getRolesByID($owner_projectid=NULL, $order_by_ar=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " r.id, r.role_nm, "
                    . " r.groupleader_yn, r.projectleader_yn, r.sprintleader_yn, "
                    . " r.workitemcreator_yn, r.workitemowner_yn, r.tester_yn, "
                    . " r.purpose_tx, r.active_yn, "
                    . " r.updated_dt, r.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_role_tablename." r";
            if($owner_projectid != NULL)
            {
                $sSQL .= " LEFT JOIN ".DatabaseNamesHelper::$m_map_prole2project_tablename." r2p on r2p.roleid=r.id ";
                $sSQL .= " WHERE r2p.projectid=$owner_projectid ";
            }
            if($order_by_ar == NULL)
            {
                $sSQL .= " ORDER BY r.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
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
     * Return a rich map of role detail with ID as the key
     */
    public function getSystemRolesByID($order_by_ar=NULL)
    {
        try
        {
            $themap = array();
            $sSQL = "SELECT"
                    . " r.id, r.role_nm,"
                    . " r.purpose_tx, r.active_yn, "
                    . " r.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_systemrole_tablename." r";
            if($order_by_ar == NULL)
            {
                $sSQL .= " ORDER BY r.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
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
     * Return a rich map of person detail with shortname as the key
     */
    public function getPersonsByPersonname($order_by_ar=NULL, $shortname_filter_ar=NULL)
    {
        if(!is_array($shortname_filter_ar))
        {
            $filter_criteria = NULL;
        } else {
            $filter_criteria = 'u.shortname in (\'' . implode('","', $shortname_filter_ar).'\')';
        }
        return $this->getPersonsData($order_by_ar,'shortname',$filter_criteria);
    }

    /**
     * Return JUST the ID values of people in a project with ID as the key
     */
    public function getMapOfPersonIDsInProject($projectid, $only_work_owners=TRUE, $personid_filter_map=NULL)
    {
        $people_in_project = $this->getIDListOfPeopleInProject($projectid, $only_work_owners);
        $justids_map = [];
        if(empty($personid_filter_map))
        {
            $justids_map = $people_in_project;
            foreach($people_in_project as $personid)
            {
                $justids_map[$personid] = $personid;
            }
        } else {
            foreach($people_in_project as $personid)
            {
                if(isset($personid_filter_map[$personid]))
                {
                    $justids_map[$personid] = $personid;
                }
            }
        }
        return $justids_map;
    }
    
    /**
     * Return JUST the ID values of people in a template project with ID as the key
     */
    public function getMapOfPersonIDsInTP($template_projectid, $only_work_owners=TRUE, $personid_filter_map=NULL)
    {
        $people_in_project = $this->getIDListOfPeopleInTP($template_projectid, $only_work_owners);
        $justids_map = [];
        if(empty($personid_filter_map))
        {
            $justids_map = $people_in_project;
            foreach($people_in_project as $personid)
            {
                $justids_map[$personid] = $personid;
            }
        } else {
            foreach($people_in_project as $personid)
            {
                if(isset($personid_filter_map[$personid]))
                {
                    $justids_map[$personid] = $personid;
                }
            }
        }
        return $justids_map;
    }
    
    /**
     * Return a minimal map of person info with ID as the key
     */
    public function getMinimalInfoPersonsInProjectByID($projectid=NULL, $order_by_ar=NULL, $personid_filter_map=NULL)
    {
        return $this->getPersonsInProjectByID($projectid, $order_by_ar, $personid_filter_map, TRUE);
    }
    
    /**
     * Return a minimal map of person info with ID as the key
     */
    public function getMinimalInfoPersonsInTPByID($template_projectid=NULL, $order_by_ar=NULL, $personid_filter_map=NULL)
    {
        return $this->getPersonsInTPByID($template_projectid, $order_by_ar, $personid_filter_map, TRUE);
    }
    
    /**
     * Return a rich map of person detail with ID as the key
     */
    public function getPersonsInProjectByID($projectid=NULL, $order_by_ar=NULL, $personid_filter_map=NULL, $only_minimal=FALSE)
    {
        $personid_map = $this->getMapOfPersonIDsInProject($projectid, $personid_filter_map);
        if(!is_array($personid_filter_map))
        {
            $filter_criteria = NULL;
        } else {
            $filter_criteria = 'u.id in (' . implode(',', $personid_filter_map).')';
        }
        $peopledetail_map = $this->getPersonsData($order_by_ar, 'id', $filter_criteria, $only_minimal);
        $removethese = [];
        $allpersonids = array_keys($peopledetail_map);
        foreach($allpersonids as $personid)
        {
            if(!isset($personid_map[$personid]))
            {
                $removethese[] = $personid;
            }
        }
        foreach($removethese as $personid)
        {
            unset($peopledetail_map[$personid]);
        }
        return $peopledetail_map;
    }
    
    /**
     * Return a rich map of person detail with ID as the key
     */
    public function getPersonsInTPByID($template_projectid=NULL, $order_by_ar=NULL, $personid_filter_map=NULL, $only_minimal=FALSE)
    {
        $personid_map = $this->getMapOfPersonIDsInTP($template_projectid, $personid_filter_map);
        if(!is_array($personid_filter_map))
        {
            $filter_criteria = NULL;
        } else {
            $filter_criteria = 'u.id in (' . implode(',', $personid_filter_map).')';
        }
        $peopledetail_map = $this->getPersonsData($order_by_ar, 'id', $filter_criteria, $only_minimal);
        $removethese = [];
        $allpersonids = array_keys($peopledetail_map);
        foreach($allpersonids as $personid)
        {
            if(!isset($personid_map[$personid]))
            {
                $removethese[] = $personid;
            }
        }
        foreach($removethese as $personid)
        {
            unset($peopledetail_map[$personid]);
        }
        return $peopledetail_map;
    }
    
    /**
     * Return a rich map of person detail with ID as the key
     */
    public function getPersonsByID($order_by_ar=NULL, $personid_filter_ar=NULL)
    {
        if(!is_array($personid_filter_ar) || count($personid_filter_ar) < 1)
        {
            $filter_criteria = NULL;
        } else {
            $filter_criteria = 'u.id in (' . implode(',', $personid_filter_ar).')';
        }
        return $this->getPersonsData($order_by_ar,'id',$filter_criteria);
    }

    public function getGroupLeaders($order_by_ar=NULL, $include_ids=NULL)
    {
        if($order_by_ar == NULL)
        {
            $order_by_ar = array('last_nm','first_nm');
        }
        $filter_criteria = 'urdetail.groupleader_yn = 1';
        if(!empty($include_ids) && count($include_ids) > 0)
        {
            $filter_criteria = "($filter_criteria OR u.id in (" . implode(",", $include_ids) . "))";
        }
        return $this->getPersonsData($order_by_ar,'id',$filter_criteria);
    }
    
    /**
     * Return the personid of everyone in a group.
     * @param type $groupid By default returns contents of the EVERYONE group
     * @return type members of the group
     */
    public function getGroupMembersByPersonID($groupid=1)
    {
        try
        {
            if($groupid==1)
            {
                $sSQL = "select id from " . DatabaseNamesHelper::$m_person_tablename . " where active_yn=1";
                $result = db_query($sSQL);
                $simple_array = $result->fetchCol();
            } else {
                //TODO --- no support yet!!!
                throw new \Exception("NOT IMPLEMENTED YET~!!!!!!");
            }
            return $simple_array;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getVisionStatementOwners($order_by_ar=NULL, $include_ids=NULL)
    {
        //FILTER TODO!!!!!!!!!!!!
        if($order_by_ar == NULL)
        {
            $order_by_ar = array('last_nm','first_nm');
        }
        $filter_criteria = 'urdetail.projectleader_yn = 1';
        if(!empty($include_ids) && count($include_ids) > 0)
        {
            $filter_criteria = "($filter_criteria OR u.id in (" . implode(",", $include_ids) . "))";
        }
        return $this->getPersonsData($order_by_ar,'id',$filter_criteria);
    }
    
    public function getCandidatePortfolioOwners($order_by_ar=NULL, $include_ids=NULL)
    {
        //FILTER TODO!!!!!!!!!!!!
        if($order_by_ar == NULL)
        {
            $order_by_ar = array('last_nm','first_nm');
        }
        $filter_criteria = 'urdetail.projectleader_yn = 1';
        if(!empty($include_ids) && count($include_ids) > 0)
        {
            $filter_criteria = "($filter_criteria OR u.id in (" . implode(",", $include_ids) . "))";
        }
        return $this->getPersonsData($order_by_ar,'id',$filter_criteria);
    }
    
    public function getProjectLeaders($order_by_ar=NULL, $include_ids=NULL)
    {
        if($order_by_ar == NULL)
        {
            $order_by_ar = array('last_nm','first_nm');
        }
        $filter_criteria = 'urdetail.projectleader_yn = 1';
        if(!empty($include_ids))
        {
            if(is_array($include_ids) && count($include_ids) > 0)
            {
                $filter_criteria = "($filter_criteria OR u.id in (" . implode(",", $include_ids) . "))";
            } else {
                $filter_criteria = "($filter_criteria OR u.id=$include_ids)";
            }
        }
        return $this->getPersonsData($order_by_ar,'id',$filter_criteria);
    }
    
    public function getSprintLeaders($projectid=NULL, $order_by_ar=NULL, $include_ids=NULL)
    {
        try
        {
            if($projectid == NULL)
            {
                throw new \Exception("You must specify a project!");
            }
            $themap = [];
            $people_in_project = $this->getIDListOfPeopleInProject($projectid);
            if($order_by_ar == NULL)
            {
                $order_by_ar = array('last_nm','first_nm');
            }
            $filter_criteria = '(urdetail.sprintleader_yn = 1)';
            $filter_criteria .= " and u.id in (" . implode(",",$people_in_project) . ")";   
            $allpeople =  $this->getPersonsData($order_by_ar,'id',$filter_criteria);
            
            foreach($allpeople as $personid=>$record)
            {
                $themap[$personid] = $record;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCandidateWorkitemTesters($projectid=NULL, $order_by_ar=NULL, $include_ids=NULL)
    {
        try
        {
            if($projectid == NULL)
            {
                throw new \Exception("You must specify a project!");
            }
            $themap = [];
            $people_in_project = $this->getIDListOfPeopleInProject($projectid);
            if($order_by_ar == NULL)
            {
                $order_by_ar = array('last_nm','first_nm');
            }
            $filter_criteria = '(urdetail.tester_yn = 1)';
            $filter_criteria .= " and u.id in (" . implode(",",$people_in_project) . ")";   
            $allpeople =  $this->getPersonsData($order_by_ar,'id',$filter_criteria);
            
            foreach($allpeople as $personid=>$record)
            {
                $themap[$personid] = $record;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCandidateUseCaseCreators($projectid=NULL, $order_by_ar=NULL)
    {
        return $this->getCandidateWorkitemCreators($projectid, $order_by_ar);
    }
    
    public function getCandidateTestCaseCreators($projectid=NULL, $order_by_ar=NULL)
    {
        return $this->getCandidateWorkitemCreators($projectid, $order_by_ar);
    }
    
    public function getCandidateWorkitemCreators($projectid=NULL, $order_by_ar=NULL)
    {
        try
        {
            if($projectid == NULL)
            {
                throw new \Exception("You must specify a project!");
            }
            $themap = [];
            $people_in_project = $this->getIDListOfPeopleInProject($projectid);
            if($order_by_ar == NULL)
            {
                $order_by_ar = array('last_nm','first_nm');
            }
            $filter_criteria = '(urdetail.workitemcreator_yn = 1)';
            $filter_criteria .= " and u.id in (" . implode(",",$people_in_project) . ")";   
            $allpeople =  $this->getPersonsData($order_by_ar,'id',$filter_criteria);
            
            foreach($allpeople as $personid=>$record)
            {
                $themap[$personid] = $record;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getCandidateProjectBaselineCreators($projectid=NULL)
    {
        return $this->getCandidateWorkitemOwners($projectid);
    }
    
    public function getCandidateWorkitemOwners($projectid=NULL, $order_by_ar=NULL)
    {
        try
        {
            if($projectid == NULL)
            {
                throw new \Exception("You must specify a project!");
            }
            $themap = [];
            $people_in_project = $this->getIDListOfPeopleInProject($projectid);
            if($order_by_ar == NULL)
            {
                $order_by_ar = array('last_nm','first_nm');
            }
            $filter_criteria = '(urdetail.workitemowner_yn = 1)';
            $filter_criteria .= " and u.id in (" . implode(",",$people_in_project) . ")";   
            $allpeople =  $this->getPersonsData($order_by_ar,'id',$filter_criteria);
            
            foreach($allpeople as $personid=>$record)
            {
                $themap[$personid] = $record;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getOnePersonDetailData($personid)
    {
        try
        {
            $rows = $this->getPersonsData(NULL,'id',"u.id=$personid");
            return $rows[$personid];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getPeopleDetailData($personid_ar)
    {
        try
        {
            if(count($personid_ar) == 0)
            {
                $rows = [];
            } else {
                $filter_criteria_txt = "u.id in (" . implode(",", $personid_ar) . ")";
                $rows = $this->getPersonsData(NULL,'id',$filter_criteria_txt);
            }
            return $rows;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of person detail with ID as the key
     */
    private function getPersonsData($order_by_ar=NULL,$key_fieldname='id'
            ,$filter_criteria_txt=NULL,$only_get_minimal=FALSE)
    {
        try
        {
            $themap = [];
            if($only_get_minimal)
            {
                $sSQL = "SELECT"
                        . " u.id as id, u.shortname, u.primary_locationid, "
                        . " u.first_nm, u.last_nm "
                        . " FROM ".DatabaseNamesHelper::$m_person_tablename." u";
            } else {
                $sSQL = "SELECT"
                        . " u.id as id, u.shortname, u.primary_locationid, "
                        . " u.first_nm, u.last_nm, "
                        . " dusers.timezone, "
                        . " u.can_create_local_project_yn, u.can_create_remote_project_yn, "
                        . " u.primary_phone, u.secondary_phone, u.secondary_email, "
                        . " u.ot_scf, u.ob_scf, u.can_own_visionstatement_yn, "
                        . " u.active_yn, dusers.status as can_login_yn, dusers.mail as email, "
                        . " u.updated_dt, u.created_dt, "
                        . " loc.shortname as location_shortname, "
                        . " ur.roleid as ur_roleid, "
                        . " urdetail.groupleader_yn as ur_groupleader_yn, "
                        . " urdetail.projectleader_yn as ur_projectleader_yn, "
                        . " urdetail.sprintleader_yn as ur_sprintleader_yn, "
                        . " urdetail.tester_yn as ur_tester_yn, "
                        . " urdetail.workitemcreator_yn as ur_workitemcreator_yn, "
                        . " urdetail.workitemowner_yn as ur_workitemowner_yn, "
                        . " urg.roleid as urg_roleid, urg.groupid as urg_groupid, "
                        . " usrg.systemroleid as usrg_roleid, usrg.groupid as usrg_groupid,"
                        . " ba.is_planning_default_yn as has_default_baseline_avail_yn, ba.shortname as baseline_avail_nm, ba.hours_per_day as baseliine_avail_hpd"
                        . " FROM ".DatabaseNamesHelper::$m_person_tablename." u";
                $sSQL  .= " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2role_tablename." ur on u.id=ur.personid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_role_tablename." urdetail on urdetail.id = ur.roleid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_location_tablename." loc on loc.id = u.primary_locationid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2role_in_group_tablename." urg on u.id=urg.personid"
                        . " LEFT JOIN users dusers on u.id=dusers.uid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_map_person2systemrole_in_group_tablename." usrg on u.id=usrg.personid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_baseline_availability_tablename." ba on ba.id=u.baseline_availabilityid";
            }
            
            $where = [];
            if($filter_criteria_txt != NULL)
            {
                $where[] = $filter_criteria_txt;
            }
            if(count($where) > 0)
            {
                $sSQL  .= " WHERE " . implode(" and ", $where);
            }
            if($order_by_ar == NULL)
            {
                $sSQL .= " ORDER BY u.id";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
            $result = db_query($sSQL);
            $previd = NULL;
            $map_person2role = array();
            $map_person2role_in_group = array();
            $map_person2systemrole_in_group = array();
            $core_record = NULL;
            $is_groupleader = FALSE;
            $is_projectmanager = FALSE;
            $is_sprintleader = FALSE;
            $is_tester = FALSE;
            $is_workitemcreator = FALSE;
            $is_workitemowner = FALSE;
            while($record = $result->fetchAssoc()) 
            {
                $id = $record[$key_fieldname];
                if($only_get_minimal)
                {
                    $themap[$id] = $record;    
                } else {
                    if($previd !== NULL && $id != $previd)
                    {
                        //Write it out now
                        if($is_groupleader)
                        {
                            $core_record['groupleader_yn'] = 1;
                        } else {
                            $core_record['groupleader_yn'] = 0;
                        }
                        if($is_projectmanager)
                        {
                            $core_record['projectleader_yn'] = 1;
                        } else {
                            $core_record['projectleader_yn'] = 0;
                        }
                        if($is_sprintleader)
                        {
                            $core_record['sprintleader_yn'] = 1;
                        } else {
                            $core_record['sprintleader_yn'] = 0;
                        }
                        if($is_tester)
                        {
                            $core_record['tester_yn'] = 1;
                        } else {
                            $core_record['tester_yn'] = 0;
                        }
                        if($is_workitemcreator)
                        {
                            $core_record['workitemcreator_yn'] = 1;
                        } else {
                            $core_record['workitemcreator_yn'] = 0;
                        }
                        if($is_workitemowner)
                        {
                            $core_record['workitemowner_yn'] = 1;
                        } else {
                            $core_record['workitemowner_yn'] = 0;
                        }
                        $core_record['maps']['person2role'] = $map_person2role;
                        $core_record['maps']['person2role_in_group'] = $map_person2role_in_group;
                        $core_record['maps']['person2systemrole_in_group'] = $map_person2systemrole_in_group;
                        $themap[$previd] = $core_record;
                        $core_record = NULL;
                        $is_projectmanager = FALSE; //reset
                    }
                    if($core_record == NULL)
                    {
                        //Start a new collection
                        $core_record = $record;
                        //$core_record['can_login_yn'] = ($record['can_login_yn'] === 1 ? 1 : 0);
                        $core_record['ur_roleid'] = NULL;
                        $core_record['urg_roleid'] = NULL;
                        $core_record['urg_groupdid'] = NULL;
                        $core_record['usrg_roleid'] = NULL;
                        $core_record['usrg_groupdid'] = NULL;
                        $map_person2role = array();
                        $map_person2role_in_group = array();
                        $map_person2systemrole_in_group = array();
                    }
                    if($record['ur_groupleader_yn'] == 1)
                    {
                        $is_groupleader = TRUE;
                    }
                    if($record['ur_projectleader_yn'] == 1)
                    {
                        $is_projectmanager = TRUE;
                    }
                    if($record['ur_sprintleader_yn'] == 1)
                    {
                        $is_sprintleader = TRUE;
                    }
                    if($record['ur_tester_yn'] == 1)
                    {
                        $is_tester = TRUE;
                    }
                    if($record['ur_workitemcreator_yn'] == 1)
                    {
                        $is_workitemcreator = TRUE;
                    }
                    if($record['ur_workitemowner_yn'] == 1)
                    {
                        $is_workitemowner = TRUE;
                    }
                    $ur_roleid = $record['ur_roleid'];
                    if($ur_roleid != NULL)
                    {
                        $map_person2role[$ur_roleid] = $ur_roleid;
                    }
                    $urg_roleid = $record['urg_roleid'];
                    $urg_groupid = $record['urg_groupid'];
                    $usrg_roleid = $record['usrg_roleid'];
                    $usrg_groupid = $record['usrg_groupid'];
                    if($urg_groupid != NULL)
                    {
                        if(!isset($map_person2role_in_group[$urg_groupid]))
                        {
                            $map_person2role_in_group[$urg_groupid] = array();
                        }
                        $map_person2role_in_group[$urg_groupid][$urg_roleid] = $urg_roleid;
                    }
                    if($usrg_groupid != NULL)
                    {
                        if(!isset($map_person2systemrole_in_group[$usrg_groupid]))
                        {
                            $map_person2systemrole_in_group[$usrg_groupid] = array();
                        }
                        $map_person2systemrole_in_group[$usrg_groupid][$usrg_roleid] = $usrg_roleid;
                    }
                    $previd = $id;
                }
            }
            if($previd != NULL)
            {
                //Write out the last one now.
                if($is_groupleader)
                {
                    $core_record['groupleader_yn'] = 1;
                } else {
                    $core_record['groupleader_yn'] = 0;
                }
                if($is_projectmanager)
                {
                    $core_record['projectleader_yn'] = 1;
                } else {
                    $core_record['projectleader_yn'] = 0;
                }
                if($is_sprintleader)
                {
                    $core_record['sprintleader_yn'] = 1;
                } else {
                    $core_record['sprintleader_yn'] = 0;
                }
                if($is_tester)
                {
                    $core_record['tester_yn'] = 1;
                } else {
                    $core_record['tester_yn'] = 0;
                }
                if($is_workitemcreator)
                {
                    $core_record['workitemcreator_yn'] = 1;
                } else {
                    $core_record['workitemcreator_yn'] = 0;
                }
                if($is_workitemowner)
                {
                    $core_record['workitemowner_yn'] = 1;
                } else {
                    $core_record['workitemowner_yn'] = 0;
                }
                $core_record['maps']['person2role'] = $map_person2role;
                $core_record['maps']['person2role_in_group'] = $map_person2role_in_group;
                $core_record['maps']['person2systemrole_in_group'] = $map_person2systemrole_in_group;
                $themap[$id] = $core_record;
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Looks for more current active sprint, else gets oldest closed.
     */
    public function getDefaultSprintID($owner_projectid)
    {
        try
        {
            $today_dt = date("Y-m-d", time());
            $sSQL = "SELECT s.id "
                        . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." s"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_status_tablename." ss on s.status_cd=ss.code";
            $sSQL .= " WHERE ss.terminal_yn IS NOT NULL";
            $sSQL .= " ORDER BY ss.terminal_yn, s.start_dt";
            $sSQL .= " LIMIT 1";
            $result = db_query($sSQL);
            if($result->rowCount() > 0)
            {
                $record = $result->fetchAssoc();
                return $record['id'];
            }
            return NULL;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Overview information for all the sprints of projects
     */
    public function getProjectSprintOverview($filter_owner_projectid=NULL
                , $more_filter_criteria=NULL)
    {
        try
        {
            $bundle = array();
            $sSQL = "SELECT"
                    . " s.owner_projectid, s.id, s.title_tx, s.iteration_ct, s.membership_locked_yn,"
                    . " s.status_cd, ss.terminal_yn, ss.happy_yn,"
                    . " s.story_tx, s.owner_personid, s.active_yn,"
                    . " s.start_dt, s.end_dt, s.owner_personid, s.status_set_dt,"
                    . " s.ot_scf,"
                    . " s.official_score, s.score_body_tx, s.updated_dt, s.created_dt "
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." s"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_status_tablename." ss on s.status_cd=ss.code";
            if(!empty($filter_owner_projectid))
            {
                $sSQL .= " WHERE s.owner_projectid=$filter_owner_projectid";
                if($more_filter_criteria != NULL)
                {
                    $sSQL  .= " and $more_filter_criteria";
                }
            } else {
                if($more_filter_criteria != NULL)
                {
                    $sSQL  .= " WHERE $more_filter_criteria";
                }
            }
            $sSQL .= " ORDER BY s.owner_projectid, s.iteration_ct";
            $result = db_query($sSQL);
            $iteration_num = NULL;
            $theprojects = array();
            $project_bundle = array();
            $prev_owner_projectid = NULL;
            $oneproject_sprintmap = NULL;
            $project_count = 0;
            while($record = $result->fetchAssoc()) 
            {
                $owner_projectid = $record['owner_projectid'];
                if($owner_projectid !== $prev_owner_projectid)
                {
                    if($prev_owner_projectid !== NULL)
                    {
                        $project_count++;
                        $project_bundle['projectid'] = $prev_owner_projectid;
                        $project_bundle['last_sprint_number'] = $iteration_num;
                        $project_bundle['sprints'] = $oneproject_sprintmap;
                        $theprojects[$prev_owner_projectid] = $project_bundle;
                    }
                    $oneproject_sprintmap = array();
                    $prev_owner_projectid = $owner_projectid;
                }
                $iteration_num = $record['iteration_ct'];
                $oneproject_sprintmap[$iteration_num] = $record;
            }
            
            //Check for the last project
            if($prev_owner_projectid !== NULL)
            {
                $project_count++;
                $project_bundle['projectid'] = $prev_owner_projectid;
                $project_bundle['last_sprint_number'] = $iteration_num;
                $project_bundle['sprints'] = $oneproject_sprintmap;
                $theprojects[$prev_owner_projectid] = $project_bundle;
            }
            
            //Complete the bundling
            $bundle['filter'] = $filter_owner_projectid;
            $bundle['project_count'] = $project_count;
            $bundle['projects'] = $theprojects;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getActionImportanceCategoryNameMap()
    {
        $map = array('High'=>array('min'=>30,'max'=>NULL)
            ,'Medium'=>array('min'=>20,'max'=>29)
            ,'Low'=>array('min'=>10,'max'=>19)
            ,'None'=>array('min'=>0,'max'=>9)
            );
        return $map;
    }
    
    public function getActionImportanceCategoryName($value)
    {
        if($value >= 30)
        {
            return 'High';
        } else
        if($value >= 20)
        {
            return 'Medium';
        } else
        if($value >= 10)
        {
            return 'Low';
        }
        return NULL;
    }
    
    public function getActionImportanceCategories()
    {
        return array(
            10=>'Low',
            20=>'Medium',
            30=>'High',
        );
    }
    
    public function getNewCopyProjectRootName($source_projectid, $candidate_name=NULL, &$map_existing_uppernames=NULL)
    {
        try
        {
            $active_yn = 1;
            
            if(empty($candidate_name) || empty($map_existing_uppernames))
            {
                $bundle = $this->getAllProjectRootGoalNameMapBundle($active_yn);
                $map_p2g = $bundle['map_p2g'];
                if(empty($candidate_name))
                {
                    $source_name = $map_p2g[$source_projectid]['root_workitem_nm'];
                    //$candidate_name = "Copy#1 of " . $source_name;
                    $candidate_name = $source_name; //Let the downstream code adjust the name
                }
                $map_existing_uppernames = [];
                foreach($map_p2g as $pid=>$detail)
                {
                    $uppername = trim(strtoupper($detail['root_workitem_nm']));
                    $map_existing_uppernames[$uppername] = $detail['root_workitem_nm'];
                }
            }

            $winner = FALSE;
            while(!$winner)
            {
                $uppername = trim(strtoupper($candidate_name));
                if(!array_key_exists($uppername, $map_existing_uppernames)) //WARNING DOUBLE SPACES IN NAME ARE HARD TO DEBUG!!!!!
                {
                    $winner = TRUE;
                } else {
                    //Try a different name
                    if(strpos($uppername, "COPY") === FALSE)
                    {
                        $candidate_name = "Copy of " . trim($candidate_name);
                    } else {
                        //Copy already exists, find an unused number
                        $core_start_pos = strpos($candidate_name," ");
                        if(strpos($uppername, "COPY#") === FALSE)
                        {
                            if($core_start_pos < 1)
                            {
                                $core_start_pos = strlen("COPY#");
                            }
                            $candidate_name = "Copy#1 " . trim(substr($candidate_name, $core_start_pos));
                        } else {
                            $prefixlen = strlen("COPY#");
                            $copynum = trim(substr($candidate_name,$prefixlen,$core_start_pos-$prefixlen));
                            $newcopynum = trim(intval($copynum) + 1);
                            $candidate_name = "Copy#{$newcopynum} " . trim(substr($candidate_name, $core_start_pos));
                        }
                    }
                }
            }
            return $candidate_name;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getNewTemplateProjectName($source_projectid, $candidate_name=NULL, &$map_existing_uppernames=NULL)
    {
        try
        {
            if(empty($candidate_name))
            {
                $candidate_name = "Template of p#$source_projectid";
            }
            return $candidate_name;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getNewProjectRootNameFromTP($source_template_projectid, $candidate_name=NULL, &$map_existing_uppernames=NULL)
    {
        try
        {
            $active_yn = 1;
            
            if(empty($candidate_name) || empty($map_existing_uppernames))
            {
                $bundle = $this->getAllTemplateRootGoalNameMapBundle($active_yn);
                $map_tp2tw = $bundle['map_tp2tw'];
                if(empty($candidate_name))
                {
                    $source_name = $map_tp2tw[$source_template_projectid]['root_workitem_nm'];
                    //$candidate_name = "Copy#1 of " . $source_name;
                    $candidate_name = $source_name; //Let the downstream code adjust the name
                }
                $map_existing_uppernames = [];
                foreach($map_tp2tw as $tpid=>$detail)
                {
                    $uppername = trim(strtoupper($detail['root_workitem_nm']));
                    $map_existing_uppernames[$uppername] = $detail['root_workitem_nm'];
                }
            }

            $winner = FALSE;
            while(!$winner)
            {
                $uppername = trim(strtoupper($candidate_name));
                if(!array_key_exists($uppername, $map_existing_uppernames)) //WARNING DOUBLE SPACES IN NAME ARE HARD TO DEBUG!!!!!
                {
                    $winner = TRUE;
                } else {
                    //Try a different name
                    if(strpos($uppername, "Instance") === FALSE)
                    {
                        $candidate_name = "Instance of " . trim($candidate_name);
                    } else {
                        //Copy already exists, find an unused number
                        $core_start_pos = strpos($candidate_name," ");
                        if(strpos($uppername, "INSTANCE#") === FALSE)
                        {
                            if($core_start_pos < 1)
                            {
                                $core_start_pos = strlen("INSTANCE#");
                            }
                            $candidate_name = "Instance#1 " . trim(substr($candidate_name, $core_start_pos));
                        } else {
                            $prefixlen = strlen("INSTANCE#");
                            $copynum = trim(substr($candidate_name,$prefixlen,$core_start_pos-$prefixlen));
                            $newcopynum = trim(intval($copynum) + 1);
                            $candidate_name = "Instance#{$newcopynum} " . trim(substr($candidate_name, $core_start_pos));
                        }
                    }
                }
            }
            return $candidate_name;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Provide the internal templateid given the shared reference name.
     */
    public function getTPIDFromPublishedRefName($publishedrefname, $error_on_missing=TRUE)
    {
        try
        {
            $sSQL = "SELECT template_projectid"
                    . " FROM ".DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename." g"
                    . " WHERE publishedrefname='$publishedrefname'";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $template_projectid = $record['template_projectid'];
            if(empty($template_projectid))
            {
                if($error_on_missing)
                {
                    throw new \Exception("There is no template project mapped to publishedrefname '$publishedrefname'");
                } else {
                    $template_projectid = NULL;    
                }
            }
            return $template_projectid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Provide the internal projectid given the shared reference name.
     */
    public function getProjectIDFromPublishedRefName($publishedrefname, $error_on_missing=TRUE)
    {
        try
        {
            $sSQL = "SELECT projectid"
                    . " FROM ".DatabaseNamesHelper::$m_map_publishedrefname2project_tablename." g"
                    . " WHERE publishedrefname='$publishedrefname'";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $projectid = $record['projectid'];
            if(empty($projectid))
            {
                if($error_on_missing)
                {
                    throw new \Exception("There is no project mapped to publishedrefname '$publishedrefname'");
                } else {
                    $projectid = NULL;    
                }
            }
            return $projectid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Provide the internal projectid given the shared reference name.
     */
    public function getProjectIDForProjectBaselineID($project_baselineid, $error_on_missing=TRUE)
    {
        try
        {
            $sSQL = "SELECT projectid"
                    . " FROM ".DatabaseNamesHelper::$m_project_baseline_tablename." g"
                    . " WHERE id='$project_baselineid'";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $projectid = $record['projectid'];
            if(empty($projectid))
            {
                if($error_on_missing)
                {
                    throw new \Exception("There is no project mapped to project_baselineid='$project_baselineid'");
                } else {
                    $projectid = NULL;    
                }
            }
            return $projectid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function createEmptyInfoTrackerNode($include_submap=FALSE)
    {
        $summary_maps_node = [];
        $summary_maps_node['count'] = 0;
        $summary_maps_node['remaining_effort_hours'] = 0;
        $summary_maps_node['effort_hours_worked_est'] = 0;
        $summary_maps_node['effort_hours_worked_act'] = 0;
        $summary_maps_node['wids'] = [];
        if($include_submap)
        {
            $summary_maps_node['submap']['terminal_yn'][0]['count'] = 0;
            $summary_maps_node['submap']['terminal_yn'][0]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['terminal_yn'][0]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['terminal_yn'][0]['effort_hours_worked_act'] = 0;
            $summary_maps_node['submap']['terminal_yn'][1]['count'] = 0;
            $summary_maps_node['submap']['terminal_yn'][1]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['terminal_yn'][1]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['terminal_yn'][1]['effort_hours_worked_act'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][0]['count'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][0]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][0]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][0]['effort_hours_worked_act'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][1]['count'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][1]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][1]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['started_and_open_yn'][1]['effort_hours_worked_act'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][0]['count'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][0]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][0]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][0]['effort_hours_worked_act'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][1]['count'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][1]['remaining_effort_hours'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][1]['effort_hours_worked_est'] = 0;
            $summary_maps_node['submap']['workstarted_yn'][1]['effort_hours_worked_act'] = 0;
        }
        return $summary_maps_node;
    }

    public function getProjectBaselineStyleBundle($projectid)
    {
        $bundle = [];
        $bundle['lookup'] = [];
        $map_workitems = $this->getProjectWorkitemsSimpleAnalysisBundle(NULL,$projectid);
        $workitem_people = $map_workitems['summary']['maps']['personid2info'];
        foreach($workitem_people as $owner_personid=>$info)
        {
            $people_map[$owner_personid] = array('fullname'=>$info['fullname']);
        }
        $bundle['lookup']['people'] = $people_map;
        $bundle['baseline'] = [];
        $bundle['baseline']['maps'] = [];
        $bundle['baseline']['maps']['workitems'] = $map_workitems;
        return $bundle;
    }

    public function getProjectBaselineWorkitems($project_baselineid)
    {
        return $this->getProjectWorkitemsSimpleAnalysisBundle($project_baselineid);
    }   
    
    private function getProjectWorkitemsSimpleAnalysisBundle($project_baselineid=NULL,$projectid=NULL)
    {
        try
        {
            if(empty($project_baselineid) && empty($projectid))
            {
                throw new \Exception("Missing required project_baselineid!");
            }
            
            $themaps = [];
            
            $summary = [];
            $summary_maps = [];
            $detail = [];
            
            if(!empty($project_baselineid))
            {
                //Pull history workitem data
                $sSQL = "SELECT wh.id, wh.workitemid, wh.status_cd, wh.workitem_basetype, wh.workitem_nm"
                        . " , wh.remaining_effort_hours, wh.effort_hours_worked_est, wh.effort_hours_worked_act"
                        . " , wh.owner_personid, p.first_nm, p.last_nm"
                        . " , s.terminal_yn, s.workstarted_yn, s.needstesting_yn"
                        . " FROM ".DatabaseNamesHelper::$m_workitem_history_tablename." wh"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename." s ON s.code=wh.status_cd"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_person_tablename." p ON p.id=wh.owner_personid";
                $sSQL .= " WHERE wh.project_baselineid=$project_baselineid";
                
                $rel_sql = "SELECT depwiid, antwiid "
                         . " FROM ".DatabaseNamesHelper::$m_map_wi2wi_history_tablename . " r"
                         . " WHERE project_baselineid=$project_baselineid";
            } else {
                //Pull current workitem data
                $sSQL = "SELECT wh.id as id, wh.id as workitemid, wh.status_cd, wh.workitem_basetype, wh.workitem_nm"
                        . " , wh.remaining_effort_hours, wh.effort_hours_worked_est, wh.effort_hours_worked_act"
                        . " , wh.owner_personid, p.first_nm, p.last_nm"
                        . " , s.terminal_yn, s.workstarted_yn, s.needstesting_yn"
                        . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." wh"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_status_tablename." s ON s.code=wh.status_cd"
                        . " LEFT JOIN " . DatabaseNamesHelper::$m_person_tablename." p ON p.id=wh.owner_personid";
                $sSQL .= " WHERE wh.owner_projectid=$projectid";
                
                $rel_sql = "SELECT depwiid, antwiid "
                         . " FROM ".DatabaseNamesHelper::$m_map_wi2wi_tablename . " r"
                         . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=r.depwiid"
                         . " WHERE w.owner_projectid=$projectid";
            }
            
            $wid2deps = [];
            $rel_result = db_query($rel_sql);
            while($record = $rel_result->fetchAssoc()) 
            {
                $depwiid = $record['depwiid'];
                $antwiid = $record['antwiid'];
                if(!isset($wid2deps[$antwiid]))
                {
                    $wid2deps[$antwiid] = [];
                }
                $wid2deps[$antwiid][$depwiid] = $depwiid;
            }
            
            $result = db_query($sSQL);
           
            $total_remaining_effort_hours = 0;
            $total_effort_hours_worked_est = 0;
            $total_effort_hours_worked_act = 0;
            $total_count = 0;
            
            while($record = $result->fetchAssoc()) 
            {
                $workitemid = $record['workitemid'];
                $status_cd = $record['status_cd'];
                $owner_personid = $record['owner_personid'];
                
                if(!isset($summary_maps['personid2info'][$owner_personid]))
                {
                    $fullname = trim($record['first_nm'] . ' ' . $record['last_nm']);
                    $summary_maps['personid2info'][$owner_personid] = array('fullname'=>$fullname);
                }
                
                $terminal_yn = $record['terminal_yn'];
                $workstarted_yn = $record['workstarted_yn'];
                $needstesting_yn = $record['needstesting_yn'];
                $started_and_open_yn = ($workstarted_yn && !$terminal_yn) ? 1 : 0;
                
                $workitem_basetype = $record['workitem_basetype'];
                $remaining_effort_hours = $record['remaining_effort_hours'];
                $effort_hours_worked_est = $record['effort_hours_worked_est'];
                $effort_hours_worked_act = $record['effort_hours_worked_act'];
                
                $record['maps']['deps'] = isset($wid2deps[$workitemid]) ? $wid2deps[$workitemid] : [];
                
                $detail[$workitemid] = $record;
                $total_remaining_effort_hours += $remaining_effort_hours;
                $total_effort_hours_worked_est += $effort_hours_worked_est;
                $total_effort_hours_worked_act += $effort_hours_worked_act;
                $total_count++;
                
                if(!isset($summary_maps['status_cd2info'][$status_cd]))
                {
                    $summary_maps['status_cd2info'][$status_cd] = $this->createEmptyInfoTrackerNode(TRUE);
                    if(!isset($summary_maps['terminal_yn2info'][$terminal_yn]))
                    {
                        $summary_maps['terminal_yn2info'][$terminal_yn] = $this->createEmptyInfoTrackerNode(FALSE);
                    }
                    if(!isset($summary_maps['workstarted_yn2info'][$workstarted_yn]))
                    {
                        $summary_maps['workstarted_yn2info'][$workstarted_yn] = $this->createEmptyInfoTrackerNode(FALSE);
                    }
                    if(!isset($summary_maps['needstesting_yn2info'][$needstesting_yn]))
                    {
                        $summary_maps['needstesting_yn2info'][$needstesting_yn] = $this->createEmptyInfoTrackerNode(FALSE);
                    }
                    if(!isset($summary_maps['started_and_open_yn2info'][$started_and_open_yn]))
                    {
                        $summary_maps['started_and_open_yn2info'][$started_and_open_yn] = $this->createEmptyInfoTrackerNode(FALSE);
                    }
                }
                if(!isset($summary_maps['workitem_basetype2info'][$workitem_basetype]))
                {
                    $summary_maps['workitem_basetype2info'][$workitem_basetype] = $this->createEmptyInfoTrackerNode(FALSE);
                }
                if(!isset($summary_maps['owner_personid2info'][$owner_personid]))
                {
                    $summary_maps['owner_personid2info'][$owner_personid] = $this->createEmptyInfoTrackerNode(TRUE);
                }
                
                $summary_maps['status_cd2info'][$status_cd]['count'] += 1;
                $summary_maps['status_cd2info'][$status_cd]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['status_cd2info'][$status_cd]['effort_hours_worked_est'] += $total_effort_hours_worked_est;
                $summary_maps['status_cd2info'][$status_cd]['effort_hours_worked_act'] += $total_effort_hours_worked_act;
                $summary_maps['status_cd2info'][$status_cd]['wids'][$workitemid] = $workitemid;
                $summary_maps['status_cd2info'][$status_cd]['submap']['terminal_yn'][$terminal_yn]['count'] += 1;
                $summary_maps['status_cd2info'][$status_cd]['submap']['terminal_yn'][$terminal_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['status_cd2info'][$status_cd]['submap']['terminal_yn'][$terminal_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['status_cd2info'][$status_cd]['submap']['terminal_yn'][$terminal_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['status_cd2info'][$status_cd]['submap']['started_and_open_yn'][$started_and_open_yn]['count'] += 1;
                $summary_maps['status_cd2info'][$status_cd]['submap']['started_and_open_yn'][$started_and_open_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['status_cd2info'][$status_cd]['submap']['started_and_open_yn'][$started_and_open_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['status_cd2info'][$status_cd]['submap']['started_and_open_yn'][$started_and_open_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['status_cd2info'][$status_cd]['submap']['workstarted_yn'][$workstarted_yn]['count'] += 1;
                $summary_maps['status_cd2info'][$status_cd]['submap']['workstarted_yn'][$workstarted_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['status_cd2info'][$status_cd]['submap']['workstarted_yn'][$workstarted_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['status_cd2info'][$status_cd]['submap']['workstarted_yn'][$workstarted_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                
                $summary_maps['terminal_yn2info'][$terminal_yn]['count'] += 1;
                $summary_maps['terminal_yn2info'][$terminal_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['terminal_yn2info'][$terminal_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['terminal_yn2info'][$terminal_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['terminal_yn2info'][$terminal_yn]['wids'][$workitemid] = $workitemid;
                
                $summary_maps['workstarted_yn2info'][$workstarted_yn]['count'] += 1;
                $summary_maps['workstarted_yn2info'][$workstarted_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['workstarted_yn2info'][$workstarted_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['workstarted_yn2info'][$workstarted_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['workstarted_yn2info'][$workstarted_yn]['wids'][$workitemid] = $workitemid;
                
                $summary_maps['needstesting_yn2info'][$needstesting_yn]['count'] += 1;
                $summary_maps['needstesting_yn2info'][$needstesting_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['needstesting_yn2info'][$needstesting_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['needstesting_yn2info'][$needstesting_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['needstesting_yn2info'][$needstesting_yn]['wids'][$workitemid] = $workitemid;
                
                $summary_maps['started_and_open_yn2info'][$started_and_open_yn]['count'] += 1;
                $summary_maps['started_and_open_yn2info'][$started_and_open_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['started_and_open_yn2info'][$started_and_open_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['started_and_open_yn2info'][$started_and_open_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['started_and_open_yn2info'][$started_and_open_yn]['wids'][$workitemid] = $workitemid;
                
                $summary_maps['workitem_basetype2info'][$workitem_basetype]['count'] += 1;
                $summary_maps['workitem_basetype2info'][$workitem_basetype]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['workitem_basetype2info'][$workitem_basetype]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['workitem_basetype2info'][$workitem_basetype]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['workitem_basetype2info'][$workitem_basetype]['wids'][$workitemid] = $workitemid;
                
                $summary_maps['owner_personid2info'][$owner_personid]['count'] += 1;
                $summary_maps['owner_personid2info'][$owner_personid]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['owner_personid2info'][$owner_personid]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['owner_personid2info'][$owner_personid]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['owner_personid2info'][$owner_personid]['wids'][$workitemid] = $workitemid;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['terminal_yn'][$terminal_yn]['count'] += 1;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['terminal_yn'][$terminal_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['terminal_yn'][$terminal_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['terminal_yn'][$terminal_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['started_and_open_yn'][$started_and_open_yn]['count'] += 1;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['started_and_open_yn'][$started_and_open_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['started_and_open_yn'][$started_and_open_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['started_and_open_yn'][$started_and_open_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['workstarted_yn'][$workstarted_yn]['count'] += 1;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['workstarted_yn'][$workstarted_yn]['remaining_effort_hours'] += $remaining_effort_hours;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['workstarted_yn'][$workstarted_yn]['effort_hours_worked_est'] += $effort_hours_worked_est;
                $summary_maps['owner_personid2info'][$owner_personid]['submap']['workstarted_yn'][$workstarted_yn]['effort_hours_worked_act'] += $effort_hours_worked_act;
                
            }
            
            $summary['total_remaining_effort_hours'] = $total_remaining_effort_hours;
            $summary['total_effort_hours_worked_est'] = $total_effort_hours_worked_est;
            $summary['total_effort_hours_worked_act'] = $total_effort_hours_worked_act;
            $summary['total_count'] = $total_count;
            $summary['maps'] = $summary_maps;
            
            $themaps['summary'] = $summary;
            $themaps['detail'] = $detail;
            
            return $themaps;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getOneProjectBaselineBundle($project_baselineid=NULL)
    {
        $bundle = $this->getProjectBaselinesBundle(NULL, $project_baselineid);
        reset($bundle['lookup']['project_baseline']);
        $first_key = key($bundle['lookup']['project_baseline']);
        $bundle['baseline'] = $bundle['lookup']['project_baseline'][$first_key];
        unset($bundle['lookup']['project_baseline']);
        return $bundle;
    }
    
    public function getProjectBaselinesBundle($projectid=NULL, $project_baselineid=NULL, $include_current=TRUE)
    {
        try
        {
            $bundle = [];
            if(empty($projectid) && empty($project_baselineid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $themap = [];
            $sSQL = "SELECT baseline.*, person.first_nm, person.last_nm "
                    . " FROM ".DatabaseNamesHelper::$m_project_baseline_tablename." baseline"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_person_tablename." person ON person.id=baseline.created_by_personid";
            if(!empty($project_baselineid))
            {
                $sSQL .= " WHERE baseline.id=$project_baselineid AND mark_deleted_yn<>1";
            } else {
                $sSQL .= " WHERE projectid=$projectid AND mark_deleted_yn<>1";
            }
            $sSQL .= " ORDER BY id";
            $result = db_query($sSQL);
           
            $people_map = [];
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id] = $record;
                $map_workitems = $this->getProjectBaselineWorkitems($id);
                $workitem_people = $map_workitems['summary']['maps']['personid2info'];
                foreach($workitem_people as $owner_personid=>$info)
                {
                    $people_map[$owner_personid] = array('fullname'=>$info['fullname']);
                }
                $themap[$id]['maps']['workitems'] = $map_workitems;
                $personid = $record['created_by_personid'];
                $person_name = trim($record['first_nm'] . ' ' . $record['last_nm']);
                $people_map[$personid] = array('fullname'=>$person_name);
            }
            
            $bundle['summary'] = [];
            $bundle['lookup'] = [];
            if($include_current && !empty($projectid))
            {
                $bundle['summary']['current_projectid'] = $projectid;
                $bundle['lookup']['current_values'] = $this->getProjectBaselineStyleBundle($projectid);
            }
            
            $ids_ar = array_keys($themap);
            $bundle['summary']['all_project_baselineids'] = $ids_ar;
            $bundle['lookup']['project_baseline'] = $themap;
            $bundle['lookup']['people'] = $people_map;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getUseCasesBundle($projectid=NULL)
    {
        try
        {
            $bundle = [];
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $from = DatabaseNamesHelper::$m_usecase_tablename." uc";
            $themap = [];
            $sSQL = "SELECT * "
                    . " FROM $from";
            $sSQL .= " WHERE owner_projectid=$projectid";
            $sSQL .= " ORDER BY id";
            $result = db_query($sSQL);
           
            $tracker_map_wid2usecaseid = [];
            $tracker_map_usecaseid2wid = [];
            
            $ids_ar = [];
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $effort_tracking_workitemid = $record['effort_tracking_workitemid'];
                $themap[$id] = $record;
                if(!empty($effort_tracking_workitemid))
                {
                    $tracker_map_wid2usecaseid[$effort_tracking_workitemid] = $id;
                    $tracker_map_usecaseid2wid[$id] = $effort_tracking_workitemid;
                }
            }
            $ids_ar = array_keys($themap);
            $submaps = $this->getUsecaseMaps($ids_ar, TRUE);
            $just_widmap = [];
            foreach($submaps as $id=>$mapdetails)
            {
                $themap[$id]['maps'] = $mapdetails;
                foreach($mapdetails['workitems'] as $wid)
                {
                    $just_widmap[$wid] = $wid;
                }
            }
            $all_wids_in_project = $this->getWorkitemIDsInProject($projectid);
            $bundle['wids_in_project'] = $all_wids_in_project;
            $bundle['lookup']['workitems'] = $this->getWorkitemInfoForListOfIDs($all_wids_in_project);
            $uc_mapped_wids = $this->getWorkitemAntInfoBundle($just_widmap);
            $all_mapped_wids = [];
            foreach($uc_mapped_wids['ant_map'] as $pwid=>$stuff)
            {
                $ants = $stuff['ants'];
                $all_mapped_wids[$pwid] = $pwid;
                foreach($ants as $cwid)
                {
                    $all_mapped_wids[$cwid] = $cwid;
                }
            }
            $bundle['uc_mapped_wids'] = $uc_mapped_wids;
            $uc_unmapped_wids = [];
            foreach($all_wids_in_project as $wid)
            {
                if(!isset($all_mapped_wids[$wid]))
                {
                    $uc_unmapped_wids[$wid] = $wid;
                }
            }
            $bundle['tracker_map_wid2usecaseid'] = $tracker_map_wid2usecaseid;
            $bundle['tracker_map_usecaseid2wid'] = $tracker_map_usecaseid2wid;
            $bundle['uc_unmapped_wids'] = $uc_unmapped_wids;
            $bundle['lookup']['usecases'] = $themap;
            $bundle['lookup']['uc_statuses'] = $this->getUseCaseStatusByCode();
            $bundle['lookup']['wi_statuses'] = $this->getWorkitemStatusByCode();
            $bundle['analysis'] = $this->getComputedUseCaseMappingAnalysisBundle($bundle);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getTestCasesBundle($projectid=NULL)
    {
        try
        {
            $bundle = [];
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $from = DatabaseNamesHelper::$m_testcase_tablename." uc";
            $themap = [];
            $sSQL = "SELECT * "
                    . " FROM $from";
            $sSQL .= " WHERE owner_projectid=$projectid";
            $sSQL .= " ORDER BY id";
            $result = db_query($sSQL);
           
            $ids_ar = [];
            $tracker_map_wid2testcaseid = [];
            $tracker_map_testcaseid2wid = [];
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $effort_tracking_workitemid = $record['effort_tracking_workitemid'];
                $themap[$id] = $record;
                if(!empty($effort_tracking_workitemid))
                {
                    $tracker_map_wid2testcaseid[$effort_tracking_workitemid] = $id;
                    $tracker_map_testcaseid2wid[$id] = $effort_tracking_workitemid;
                }
            }
            $ids_ar = array_keys($themap);
            $submaps = $this->getTestcaseMaps($ids_ar, TRUE);
            $just_widmap = [];
            foreach($submaps as $id=>$mapdetails)
            {
                $themap[$id]['maps'] = $mapdetails;
                foreach($mapdetails['workitems'] as $wid)
                {
                    $just_widmap[$wid] = $wid;
                }
            }
            $all_wids_in_project = $this->getWorkitemIDsInProject($projectid);
            $bundle['wids_in_project'] = $all_wids_in_project;
            $bundle['lookup']['workitems'] = $this->getWorkitemInfoForListOfIDs($all_wids_in_project);
            $tc_mapped_wids = $this->getWorkitemAntInfoBundle($just_widmap);
            $all_mapped_wids = [];
            foreach($tc_mapped_wids['ant_map'] as $pwid=>$stuff)
            {
                $ants = $stuff['ants'];
                $all_mapped_wids[$pwid] = $pwid;
                foreach($ants as $cwid)
                {
                    $all_mapped_wids[$cwid] = $cwid;
                }
            }
            $bundle['tc_mapped_wids'] = $tc_mapped_wids;
            $tc_unmapped_wids = [];
            foreach($all_wids_in_project as $wid)
            {
                if(!isset($all_mapped_wids[$wid]))
                {
                    $tc_unmapped_wids[$wid] = $wid;
                }
            }
            $bundle['tracker_map_wid2testcaseid'] = $tracker_map_wid2testcaseid;
            $bundle['tracker_map_testcaseid2wid'] = $tracker_map_testcaseid2wid;
            $bundle['tc_unmapped_wids'] = $tc_unmapped_wids;
            $bundle['lookup']['testcases'] = $themap;
            $bundle['lookup']['tc_statuses'] = $this->getTestCaseStatusByCode();
            $bundle['lookup']['wi_statuses'] = $this->getWorkitemStatusByCode();
            
            $bundle['analysis'] = $this->getComputedTestCaseMappingAnalysisBundle($bundle);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getComputedUseCaseMappingAnalysisBundle($bundle)
    {
        try
        {
            $result = [];
            
            $wids_in_project = $bundle['wids_in_project'];
            //$uc_status_lookup = $bundle['lookup']['uc_statuses'];
            $wi_status_lookup = $bundle['lookup']['wi_statuses'];
            $usecase_lookup = $bundle['lookup']['usecases'];
            $workitem_lookup = $bundle['lookup']['workitems'];
            $uc_mapped_wids = $bundle['uc_mapped_wids'];
            $ar_unmapped_workitem = $bundle['uc_unmapped_wids'];
            
            $ar_mapped_workitem_direct = [];
            $ar_mapped_workitem_indirect = [];
            $ar_all_mapped_wid_by_ucid = [];
            $ar_map_directwid2ucids = [];
            foreach($usecase_lookup as $ucid=>$detail)
            {
                foreach($detail['maps']['workitems'] as $directwid)
                {
                    if(!isset($ar_map_directwid2ucids[$directwid]))
                    {
                        $ar_map_directwid2ucids[$directwid] = [];
                    }
                    $ar_map_directwid2ucids[$directwid][$ucid] = $ucid;
                    $ar_all_mapped_wid_by_ucid[$ucid]['direct'][$directwid] = $directwid;
                    $ar_all_mapped_wid_by_ucid[$ucid]['all'][$directwid] = $directwid;
                }
            }
            $ar_candidate_mapped_workitem_indirect = [];
            foreach($uc_mapped_wids['ant_map'] as $direct_wid=>$detail)
            {
                $ar_mapped_workitem_direct[$direct_wid] = $direct_wid;
                $ants = $detail['ants'];
                foreach($ants as $indirect_wid)
                {
                    $ar_candidate_mapped_workitem_indirect[$indirect_wid] = $indirect_wid;
                    foreach($ar_map_directwid2ucids[$direct_wid] as $relevant_ucid)
                    {
                        $ar_all_mapped_wid_by_ucid[$relevant_ucid]['all'][$indirect_wid] = $indirect_wid;
                    }
                }
            }
            foreach($ar_candidate_mapped_workitem_indirect as $ciwid)
            {
                if(!isset($ar_mapped_workitem_direct[$ciwid]))
                {
                    //Skip anything that we do not have a lookup for
                    if(!empty($workitem_lookup[$ciwid]))
                    {
                        //Yes, this is ONLY indirectly mapped
                        $ar_mapped_workitem_indirect[$ciwid] = $ciwid;
                    }
                }
            }
            
            $done_wids = [];
            $pct_complete_by_ucid = [];
            $wids_computed = [];
            //foreach($ar_all_mapped_wid_by_ucid as $ucid=>$detail)
            foreach($usecase_lookup as $ucid=>$ucdetail)
            {
                if(!isset($ar_all_mapped_wid_by_ucid[$ucid]))
                {
                    //So that we always have this element
                    $pct_complete_by_ucid[$ucid]['total_wids'] = 0;
                } else {
                    $detail = $ar_all_mapped_wid_by_ucid[$ucid];
                    $pct_complete_by_ucid[$ucid] = [];
                    $all_wids = $detail['all'];
                    $total_wids = count($all_wids);
                    $pct_complete_by_ucid[$ucid]['total_wids'] = $total_wids;
                    if($total_wids > 0)
                    {
                        $total_done = 0;
                        foreach($all_wids as $wid)
                        {
                            //Skip anything that we do not have a lookup for
                            if(!empty($workitem_lookup[$wid]))
                            {
                                $wids_computed[$wid] = $wid;
                                $winfo = $workitem_lookup[$wid];
                                $wstatus_cd = $winfo['status_cd'];
                                $sinfo = $wi_status_lookup[$wstatus_cd];
                                if($sinfo['terminal_yn'] == 1)
                                {
                                    $total_done++;
                                    $done_wids[$wid] = $wid;
                                }
                            }
                        }
                        $pct_complete_by_ucid[$ucid]['total_done'] = $total_done;
                        $pct_complete_by_ucid[$ucid]['pct_done'] = round(100 * $total_done / $total_wids);
                    }
                }
            }
            foreach($wids_in_project as $wid)
            {
                if(!isset($wids_computed[$wid]))
                {
                    //This one was not already visited
                    $wids_computed[$wid] = $wid;
                    $winfo = $workitem_lookup[$wid];
                    $wstatus_cd = $winfo['status_cd'];
                    $sinfo = $wi_status_lookup[$wstatus_cd];
                    if($sinfo['terminal_yn'] == 1)
                    {
                        $done_wids[$wid] = $wid;
                    }
                }
            }

            //Now, compute the aggregate counts
            $count_mapped_workitem_direct_done = 0;
            $count_mapped_workitem_indirect_done = 0;
            $count_unmapped_workitem_done = 0;
            foreach($done_wids as $wid)
            {
                if(isset($ar_mapped_workitem_direct[$wid]))
                {
                    $count_mapped_workitem_direct_done++;
                } else if(isset($ar_mapped_workitem_indirect[$wid])) {
                    $count_mapped_workitem_indirect_done++;
                } else {
                    $count_unmapped_workitem_done++;
                }
            }
            $count_mapped_workitem_direct_notdone = count($ar_mapped_workitem_direct) - $count_mapped_workitem_direct_done;
            $count_mapped_workitem_indirect_notdone = count($ar_mapped_workitem_indirect) - $count_mapped_workitem_indirect_done;
            $count_unmapped_workitem_notdone = count($ar_unmapped_workitem) - $count_unmapped_workitem_done;

            $result['mapped']['workitem']['direct']['workitems'] = $ar_mapped_workitem_direct;
            $result['mapped']['workitem']['indirect']['workitems'] = $ar_mapped_workitem_indirect;
            $result['unmapped']['workitem']['workitems'] = $ar_unmapped_workitem;
            
            $result['mapped']['workitem']['direct']['done']['count'] = $count_mapped_workitem_direct_done;
            $result['mapped']['workitem']['indirect']['done']['count'] = $count_mapped_workitem_indirect_done;
            $result['unmapped']['workitem']['done']['count'] = $count_unmapped_workitem_done;
            $result['mapped']['workitem']['direct']['notdone']['count'] = $count_mapped_workitem_direct_notdone;
            $result['mapped']['workitem']['indirect']['notdone']['count'] = $count_mapped_workitem_indirect_notdone;
            $result['unmapped']['workitem']['notdone']['count'] = $count_unmapped_workitem_notdone;
            $result['pct_complete_by_ucid'] = $pct_complete_by_ucid;
            
            $result['mapped']['usecase2workitems'] = $ar_all_mapped_wid_by_ucid;
            
            return $result;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
      
    private function getComputedTestCaseMappingAnalysisBundle($bundle)
    {
        try
        {
            $result = [];
            
            $wids_in_project = $bundle['wids_in_project'];
            //$uc_status_lookup = $bundle['lookup']['uc_statuses'];
            $wi_status_lookup = $bundle['lookup']['wi_statuses'];
            $testcase_lookup = $bundle['lookup']['testcases'];
            $workitem_lookup = $bundle['lookup']['workitems'];
            $tc_mapped_wids = $bundle['tc_mapped_wids'];
            
            $tracker_map_wid2testcaseid = $bundle['tracker_map_wid2testcaseid'];
            $tracker_map_testcaseid2wid = $bundle['tracker_map_testcaseid2wid'];
            $raw_ar_unmapped_workitem = $bundle['tc_unmapped_wids'];
            $clean_ar_unmapped_workitem = [];
            foreach($raw_ar_unmapped_workitem as $wid)
            {
                if(!isset($tracker_map_wid2testcaseid[$wid]))
                {
                    //This one is relevant.
                    $clean_ar_unmapped_workitem[$wid] = $wid;
                }
            }
            
            $ar_mapped_workitem_direct = [];
            $ar_mapped_workitem_indirect = [];
            $ar_all_mapped_wid_by_tcid = [];
            $ar_map_directwid2tcids = [];
            foreach($testcase_lookup as $ucid=>$detail)
            {
                foreach($detail['maps']['workitems'] as $directwid)
                {
                    if(!isset($ar_map_directwid2tcids[$directwid]))
                    {
                        $ar_map_directwid2tcids[$directwid] = [];
                    }
                    $ar_map_directwid2tcids[$directwid][$ucid] = $ucid;
                    $ar_all_mapped_wid_by_tcid[$ucid]['direct'][$directwid] = $directwid;
                    $ar_all_mapped_wid_by_tcid[$ucid]['all'][$directwid] = $directwid;
                }
            }
            $ar_candidate_mapped_workitem_indirect = [];
            foreach($tc_mapped_wids['ant_map'] as $direct_wid=>$detail)
            {
                $ar_mapped_workitem_direct[$direct_wid] = $direct_wid;
                $ants = $detail['ants'];
                foreach($ants as $indirect_wid)
                {
                    $ar_candidate_mapped_workitem_indirect[$indirect_wid] = $indirect_wid;
                    foreach($ar_map_directwid2tcids[$direct_wid] as $relevant_ucid)
                    {
                        $ar_all_mapped_wid_by_tcid[$relevant_ucid]['all'][$indirect_wid] = $indirect_wid;
                    }
                }
            }
            foreach($ar_candidate_mapped_workitem_indirect as $ciwid)
            {
                if(!isset($ar_mapped_workitem_direct[$ciwid]))
                {
                    //Skip anything that we do not have a lookup for
                    if(!empty($workitem_lookup[$ciwid]))
                    {
                        //Yes, this is ONLY indirectly mapped
                        $ar_mapped_workitem_indirect[$ciwid] = $ciwid;
                    }
                }
            }
            
            $done_wids = [];
            $done_untested_wids = [];
            $tested_wids = [];
            $passed_test_wids = [];
            $failed_test_wids = [];
            $pct_complete_by_tcid = [];
            $pct_complete_by_tcid = [];
            $wids_computed = [];

            foreach($testcase_lookup as $ucid=>$ucdetail)
            {
                if(!isset($ar_all_mapped_wid_by_tcid[$ucid]))
                {
                    //So that we always have this element
                    $pct_complete_by_tcid[$ucid]['total_wids'] = 0;
                } else {
                    $detail = $ar_all_mapped_wid_by_tcid[$ucid];
                    $pct_complete_by_tcid[$ucid] = [];
                    $all_wids = $detail['all'];
                    $total_wids = count($all_wids);
                    $pct_complete_by_tcid[$ucid]['total_wids'] = $total_wids;
                    if($total_wids > 0)
                    {
                        $total_done = 0;
                        $total_done_untested = 0;
                        $total_done_or_testable = 0;
                        $total_tested = 0;
                        $total_passed_test = 0;
                        $total_failed_test = 0;
                        foreach($all_wids as $wid)
                        {
                            //Skip anything that we do not have a lookup for
                            if(!empty($workitem_lookup[$wid]))
                            {
                                $wids_computed[$wid] = $wid;
                                $winfo = $workitem_lookup[$wid];
                                $wstatus_cd = $winfo['status_cd'];
                                $sinfo = $wi_status_lookup[$wstatus_cd];
                                
                                $is_done_or_testable = $sinfo['terminal_yn'] == 1;
                                
                                if($sinfo['terminal_yn'] == 1)
                                {
                                    $total_done++;
                                }
                                
                                if($sinfo['terminal_yn'] == 1 || $sinfo['needstesting_yn'] == 1)
                                {
                                    $total_done_untested++;
                                    $is_done_or_testable = TRUE;
                                }
                                
                                if(isset($sinfo['needstesting_yn']) && $sinfo['needstesting_yn'] == 0)
                                {
                                    $total_tested++;
                                    $is_done_or_testable = TRUE;
                                }
                                
                                if($sinfo['terminal_yn'] == 1 && isset($sinfo['happy_yn']) && $sinfo['happy_yn'] == 1)
                                {
                                    $total_passed_test++;
                                }
                                
                                if(isset($sinfo['happy_yn']) && $sinfo['happy_yn'] == 0)
                                {
                                    $total_failed_test++;
                                    $is_done_or_testable = TRUE;
                                }
                                
                                if($is_done_or_testable)
                                {
                                    $total_done_or_testable++;
                                }
                            }
                        }
                        $pct_complete_by_tcid[$ucid]['total_done'] = $total_done;
                        $pct_complete_by_tcid[$ucid]['total_done_untested'] = $total_done_untested;
                        $pct_complete_by_tcid[$ucid]['total_tested'] = $total_tested;
                        $pct_complete_by_tcid[$ucid]['total_passed_test'] = $total_passed_test;
                        $pct_complete_by_tcid[$ucid]['total_failed_test'] = $total_failed_test;
                        $pct_complete_by_tcid[$ucid]['total_done_or_testable'] = $total_done_or_testable;
                        $pct_complete_by_tcid[$ucid]['pct_done'] = round(100 * $total_done / $total_wids);
                        $pct_complete_by_tcid[$ucid]['pct_done_untested'] = round(100 * $total_done_untested / $total_wids);
                        $pct_complete_by_tcid[$ucid]['pct_done_or_testable'] = round(100 * $total_done_or_testable / $total_wids);
                        $pct_complete_by_tcid[$ucid]['pct_tested'] = round(100 * $total_tested / $total_wids);
                        $pct_complete_by_tcid[$ucid]['pct_passed_test'] = round(100 * $total_passed_test / $total_wids);
                        $pct_complete_by_tcid[$ucid]['pct_failed_test'] = round(100 * $total_failed_test / $total_wids);
                    }
                }
            }
            $total_wids = count($wids_in_project);
            $total_done = 0;
            $total_done_untested = 0;
            $total_tested = 0;
            $total_passed_test = 0;
            $total_failed_test = 0;
            foreach($wids_in_project as $wid)
            {
                //This one was not already visited
                $wids_computed[$wid] = $wid;
                $winfo = $workitem_lookup[$wid];
                $wstatus_cd = $winfo['status_cd'];
                $sinfo = $wi_status_lookup[$wstatus_cd];
                if($sinfo['terminal_yn'] == 1)
                {
                    $total_done++;
                    $done_wids[$wid] = $wid;
                }
                if($sinfo['terminal_yn'] == 1 || $sinfo['needstesting_yn'] == 1)
                {
                    $total_done_untested++;
                    $done_untested_wids[$wid] = $wid;
                }
                if(isset($sinfo['needstesting_yn']) && $sinfo['needstesting_yn'] == 0)
                {
                    $total_tested++;
                    $tested_wids[$wid] = $wid;
                }
                if($sinfo['terminal_yn'] == 1 && isset($sinfo['happy_yn']) && $sinfo['happy_yn'] == 1)
                {
                    $total_passed_test++;
                    $passed_test_wids[$wid] = $wid;
                }
                if(isset($sinfo['happy_yn']) && $sinfo['happy_yn'] == 0)
                {
                    $total_failed_test++;
                    $failed_test_wids[$wid] = $wid;
                }
            }
            
            $wids_by_classification = [];
            $wids_by_classification['done'] = $done_wids;
            $wids_by_classification['done_untested'] = $done_untested_wids;
            $wids_by_classification['tested'] = $tested_wids;
            $wids_by_classification['passed_test'] = $passed_test_wids;
            $wids_by_classification['failed_test'] = $failed_test_wids;
            
            $pct_complete['total_done'] = $total_done;
            $pct_complete['total_done_untested'] = $total_done_untested;
            $pct_complete['total_tested'] = $total_tested;
            $pct_complete['total_passed_test'] = $total_passed_test;
            $pct_complete['total_failed_test'] = $total_failed_test;
            $pct_complete['pct_done'] = round(100 * $total_done / $total_wids);
            $pct_complete['pct_done_untested'] = round(100 * $total_done_untested / $total_wids);
            $pct_complete['pct_tested'] = round(100 * $total_tested / $total_wids);
            $pct_complete['pct_passed_test'] = round(100 * $total_passed_test / $total_wids);
            $pct_complete['pct_failed_test'] = round(100 * $total_failed_test / $total_wids);

            //Now, compute the aggregate counts
            $count_mapped_workitem_direct_done = 0;
            $count_mapped_workitem_indirect_done = 0;
            $count_unmapped_workitem_done = 0;
            foreach($done_wids as $wid)
            {
                if(isset($ar_mapped_workitem_direct[$wid]))
                {
                    $count_mapped_workitem_direct_done++;
                } else if(isset($ar_mapped_workitem_indirect[$wid])) {
                    $count_mapped_workitem_indirect_done++;
                } else {
                    $count_unmapped_workitem_done++;
                }
            }
            $count_mapped_workitem_direct_notdone = count($ar_mapped_workitem_direct) - $count_mapped_workitem_direct_done;
            $count_mapped_workitem_indirect_notdone = count($ar_mapped_workitem_indirect) - $count_mapped_workitem_indirect_done;
            $count_unmapped_workitem_notdone = count($clean_ar_unmapped_workitem) - $count_unmapped_workitem_done;

            $result['pct_completed'] = $pct_complete;
            
            $result['mapped']['workitems']['classification2wid'] = $wids_by_classification;
            
            $result['mapped']['workitem']['trackers']['wid2testcaseid'] = $tracker_map_wid2testcaseid;
            $result['mapped']['workitem']['trackers']['testcaseid2wid'] = $tracker_map_testcaseid2wid;
            
            $result['mapped']['workitem']['direct']['workitems'] = $ar_mapped_workitem_direct;
            $result['mapped']['workitem']['indirect']['workitems'] = $ar_mapped_workitem_indirect;
            $result['unmapped']['workitem']['workitems'] = $clean_ar_unmapped_workitem;
            
            $result['mapped']['workitem']['direct']['done']['count'] = $count_mapped_workitem_direct_done;
            $result['mapped']['workitem']['indirect']['done']['count'] = $count_mapped_workitem_indirect_done;
            $result['unmapped']['workitem']['done']['count'] = $count_unmapped_workitem_done;
            $result['mapped']['workitem']['direct']['notdone']['count'] = $count_mapped_workitem_direct_notdone;
            $result['mapped']['workitem']['indirect']['notdone']['count'] = $count_mapped_workitem_indirect_notdone;
            $result['unmapped']['workitem']['notdone']['count'] = $count_unmapped_workitem_notdone;
            $result['pct_complete_by_tcid'] = $pct_complete_by_tcid;    //DONE or READY FOR TESTING
            
            $result['mapped']['testcase2workitems'] = $ar_all_mapped_wid_by_tcid;
            
            return $result;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
      
    private function getBestDate($actual_dt, $planned_dt, $effort_hours=0, $reference_dt=NULL, $direction=1)
    {
        $best_dt = NULL;
        if(!empty($actual_dt))
        {
            $best_dt = $actual_dt;
        } else if(!empty($planned_dt)){
            $best_dt = $planned_dt;
        } else {
            if(!empty($reference_dt))
            {
                $shift_days = $direction * ($effort_hours / 24);
                $timestamp = UtilityGeneralFormulas::getDayShiftedDateAsTimestamp($reference_dt, $shift_days);
                $best_dt = gmdate("Y-m-d", $timestamp);
            }
        }
        return $best_dt;
    }
    
    /**
     * @return the projectid values where root goal status is brainstorm
     */
    public function getBrainstormProjectIDs()
    {
        try
        {
            $sSQL = "SELECT p.id as id "
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=p.root_goalid"
                    . " WHERE w.status_cd='B' and w.active_yn=1"
                    . " ORDER BY id";
            $result = db_query($sSQL);
            $map = $result->fetchAllAssoc('id');
            $list = array_keys($map);
            return $list;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a bundle that describes the utilization of a set of application person accounts
     * And include the gaps
     */
    public function getShortcutUtilizationAndGapsDataBundle($personid_ar=NULL)
    {
        try
        {
            $filter_status_attrib_ar=NULL;
            $filter_starting_dt=NULL;
            $filter_ending_dt=NULL;
            $excluded_projects_ar=NULL;
            $through_future_days_count=NULL;
            $map_wid_allocations2ignore=NULL;
            $answer = $this->getUtilizationAndGapsDataBundle($personid_ar
                ,$filter_status_attrib_ar
                ,$filter_starting_dt
                ,$filter_ending_dt
                ,$excluded_projects_ar
                ,$through_future_days_count
                ,$map_wid_allocations2ignore);
            return $answer;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a bundle that describes the utilization of a set of application person accounts
     * And include the gaps
     */
    public function getUtilizationAndGapsDataBundle($personid_ar=NULL
            ,$filter_status_attrib_ar=NULL
            ,$filter_starting_dt=NULL
            ,$filter_ending_dt=NULL
            ,$excluded_projects_ar=NULL
            ,$through_future_days_count=NULL
            ,$workitems2exclude=NULL)
    {
        try
        {
            if(empty($workitems2exclude))
            {
                $workitems2exclude = [];
            }
            
            $utilcontent = $this->getUtilizationDataBundle($personid_ar,$filter_status_attrib_ar,$filter_starting_dt
                    ,$filter_ending_dt,$excluded_projects_ar,$workitems2exclude);

            $all_smartinfo=[];
            $all_smartbuckets_by_personid=[];
            $all_by_personid = $utilcontent['by_personid'];
            $min_dt = $utilcontent['summary']['min_dt'];
            $max_dt = $utilcontent['summary']['max_dt'];
            if(empty($filter_starting_dt))
            {
                $filter_starting_dt = $min_dt;
            }
            if(empty($filter_ending_dt))
            {
                $filter_ending_dt = $max_dt;
            }
            foreach($all_by_personid as $personid=>$detail)
            {
                $all_active_ranges = [];
                $allrowdata = [];
                $smartbucket = $detail['smartbucket'];
                
                
                $all_smartbuckets_by_personid[$personid] = $smartbucket;
                $daycount_detailinfo = $smartbucket->getPersonDayCountBundleBetweenDates();
                $utilcontent['daycount']['by_personid'][$personid] = $daycount_detailinfo;
                if($daycount_detailinfo['has_bundle_yn'])
                {
                    //There is a bundle for this item
                    $computed_bundle = $smartbucket->getComputedNumberData();//'remaining_effort_hours');
                    $all_interval_info = $computed_bundle['all_intervals_info'];
                    $sorted_date_pairs = $all_interval_info['sorted_date_pairs'];
                    $by_wid = $all_interval_info['by_wid'];
                    
                    $master_i_offset = 0;
                    foreach($sorted_date_pairs as $one_date_pair_info)
                    {
                        $onerowdata = NULL;
                        $start_dt = $one_date_pair_info['start_dt'];
                        $end_dt = $one_date_pair_info['end_dt'];
                        
                        $subset_daycount_info = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDatesFromExistingDetail($daycount_detailinfo, $start_dt, $end_dt);
                        
                        $wid_map = $one_date_pair_info['wid_map'];
                        foreach($wid_map as $wid)
                        {
                            
                            if(!isset($by_wid[$wid]['lookup']['intervals']['idx2local_offset'][$master_i_offset]))
                            {
                                error_log("XXX POSSIBLE BUG at wid#$wid CONSIDER DEPRECATING THIS FUNCTION...");
                                if(isset($by_wid[$wid]['lookup']))
                                {
                                    error_log("XXX LOOK by_wid[$wid]['lookup']=".print_r($by_wid[$wid]['lookup'],TRUE));
                                }
                                error_log("XXX LOOK one_date_pair_info=".print_r($one_date_pair_info,TRUE));
                                error_log("XXX LOOK wid_map=".print_r($wid_map,TRUE));
                                error_log("XXX LOOK wid=$wid wintervals=".print_r($wintervals,TRUE));
                                error_log("XXX LOOK sorted_date_pairs=".print_r($sorted_date_pairs,TRUE));
                            } else {
                                $wintervals = $by_wid[$wid]['intervals'];
                                $local_i_offset = $by_wid[$wid]['lookup']['intervals']['idx2local_offset'][$master_i_offset];

                                $one_winterval = $wintervals[$local_i_offset];
                                $one_winterval['wid'] = $wid;
                                $onerowdata['intervals'][$wid] = $one_winterval;    //Assumes wid occurs at most once per interval
                            }
                            
                        }
                        if(!empty($onerowdata))
                        {
                            $onerowfinal['metadata']['source'] = "maphelper>>>getUtilizationAndGapsDataBundle for person#{$personid}";
                            $onerowdata['start_dt'] = $start_dt;
                            $onerowdata['end_dt'] = $end_dt;
                            $onerowfinal['plain'] = $onerowdata;
                            $all_active_ranges[$start_dt] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt,'day_count_info'=>$subset_daycount_info);
                            $allrowdata[] = $onerowfinal;
                        }
                        $master_i_offset++;
                    }
/** TODO RESTORE THIS CODE FOR GAP SUPPORT
                    //Now get the GAP records  TODO FIX THE STRUCTURE TO MATCH THE NEW CONFIG FROM 5/4/2017
                    if($through_future_days_count == NULL)
                    {
                        $gap_date_ranges = \bigfathom\UtilityGeneralFormulas::getGapDateRanges($all_active_ranges);
                    } else {
                        $gap_date_ranges = \bigfathom\UtilityGeneralFormulas::getGapDateRanges($all_active_ranges,$through_future_days_count);
                    }
                    foreach($gap_date_ranges as $start_dt=>$onerowdata)
                    {
                        $start_dt = $onerowdata['start_dt'];                    
                        $end_dt = $onerowdata['end_dt'];
                        $gap_daycount_detailinfo = \bigfathom\UtilityGeneralFormulas::getDayCountBundleBetweenDates($start_dt, $end_dt, $personid);
                        $intervals = [];
                        $intervals['sorted_date_pairs'] = [];
                        //$intervals['sorted_date_pairs'][$start_dt] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt,'idx_map'=>[],'wid_map'=>[]);
                        $intervals['sorted_date_pairs'][$start_dt] = array('start_dt'=>$start_dt,'end_dt'=>$end_dt,'day_count_info'=>$gap_daycount_detailinfo,'wid_map'=>[]);
                        $onerowdata['intervals'] = [];
                        //$onerowdata['daycount'] = $daycount_detailinfo;
                        $onerowfinal['plain'] = $onerowdata;
                        if($include_styled_content)
                        {
                            $onerowfinal['formatted'] = \bigfathom\UtilityFormatUtilizationData::getFormattedRowCells4PersonBoundedPeriod($gap_daycount_detailinfo, $onerowdata);
                        }
                        $allrowdata[] = $onerowfinal;
                    }
 * 
 */
                    $all_smartinfo[$personid] = $allrowdata;
                }
            }

            $utilcontent['smartbucketobject']['by_personid'] = $all_smartbuckets_by_personid; //TRY SWITCH TO PUI!!!!
            //$utilcontent['smartbucketinfo']['by_personid'] = $all_smartinfo; //DEPRECATE AS REDUNDANT????
            return $utilcontent;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a bundle that describes the utilization of a set of application person accounts
     */
    public function getUtilizationDataBundleWithSimpleExclusions($personid_ar=NULL
            ,$workitems2exclude=NULL)
    {
        if(empty($workitems2exclude))
        {
            throw new \Exception("Expected at least one exclusion!");
        }
        $filter_status_attribute_ar=NULL;
        $filter_starting_dt=NULL;
        $filter_ending_dt=NULL;
        $excluded_projects_ar=NULL;
        $bundle = $this->getUtilizationDataBundle($personid_ar
            ,$filter_status_attribute_ar
            ,$filter_starting_dt
            ,$filter_ending_dt
            ,$excluded_projects_ar
            ,$workitems2exclude);
        return $bundle;
    }
    
    /**
     * Return a bundle that describes the utilization of a set of application person accounts
     */
    public function getUtilizationDataBundle($personid_ar=NULL
            ,$filter_status_attribute_ar=NULL
            ,$filter_starting_dt=NULL
            ,$filter_ending_dt=NULL
            ,$excluded_projects_ar=NULL
            ,$workitems2exclude=NULL)
    {
        try
        {
            if($filter_starting_dt > $filter_ending_dt)
            {
                $errmsg = "LOOK DATE ERROR getUtilizationDataBundle filter_starting_dt=$filter_starting_dt filter_ending_dt=$filter_ending_dt  personid_ar=" . print_r($personid_ar,TRUE);
                drupal_set_message($errmsg);            
                DebugHelper::showStackTrace($errmsg);
                throw new \Exception("Start date '$filter_starting_dt' later than end date '$filter_ending_dt'!");
            }
            
            $metadata = [];
            $summary = [];
            $min_dt = NULL;
            $max_dt = NULL;
            if(!empty($personid_ar) && !is_array($personid_ar))
            {
                if(!is_numeric($personid_ar))
                {
                    throw new \Exception("Expected a personid but instead got '$personid_ar'!");
                }
                //Assume we got one personid
                $metadata['insight'] = "Assuming '$personid_ar' is a personid!";
                $personid = $personid_ar;
                $personid_ar = [];
                $personid_ar[] = $personid;
            }
            if(empty($personid_ar) || count($personid_ar) < 1)
            {
                throw new \Exception("Missing required array of personid!");
            }
            
            $loaded = module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
            if(!$loaded)
            {
                throw new \Exception("Unable to load DateRangeSmartNumberBucket!");
            }
            if(empty($excluded_projects_ar))
            {
                $excluded_projects_ar = [];
            }
            if(!is_array($excluded_projects_ar))
            {
                //Assume we received a single value
                $single_projectid = $excluded_projects_ar;
                $excluded_projects_ar = [];
                $excluded_projects_ar[] = $single_projectid;
            }
            if(empty($workitems2exclude))
            {
                $workitems2exclude = [];
            }
            if(!is_array($workitems2exclude))
            {
                //Assume we have ONE wid, put it into an array
                $workitems2exclude = array($workitems2exclude);
            }
            if(empty($filter_status_attribute_ar))
            {
                $filter_status_attribute_ar['terminal_yn'] = 0;   
            }
            if(empty($filter_starting_dt))
            {
                $startdt = new \DateTime();
                $filter_starting_dt = $startdt->format('Y-m-d');
            }
            $bundle = [];
            
            //$by_wid = [];
            $by_personid = [];
            $by_daterange = [];
            $by_workitem = [];
            $by_project = [];
            $found_personid_map = [];
            $found_projectid_map = [];
            
            $inlist_personid = implode(",", $personid_ar);
            
            $fields_exp = "w.id as wid, w.owner_projectid, w.owner_personid, w.workitem_basetype"
                    . ", IF(IsNull(rp.id),0,1) as project_root_yn"
                    . ", '$filter_starting_dt' as filter_starting_dt"
                    . ", w.planned_start_dt, w.planned_end_dt"
                    . ", w.actual_start_dt, w.actual_end_dt"
                    . ", w.planned_start_dt_locked_yn, w.planned_end_dt_locked_yn, w.effort_hours_est_locked_yn"
                    . ", w.remaining_effort_hours"
                    . ", w.effort_hours_est"
                    . ", w.effort_hours_worked_est, w.effort_hours_worked_act "
                    . ", w.status_cd, s.terminal_yn, s.workstarted_yn, s.happy_yn, s.needstesting_yn, s.default_ignore_effort_yn "
                    . ", p.first_nm, p.last_nm, 'work' as comment";
            $from_exp = DatabaseNamesHelper::$m_workitem_tablename . " w "
                        . " left join bigfathom_person p on w.owner_personid=p.id "
                        . " left join bigfathom_workitem_status s on w.status_cd=s.code"
                        . " left join bigfathom_project rp on rp.root_goalid=w.id";
            $where[] = "w.active_yn=1";
            $where[] = "('$filter_starting_dt' <= COALESCE(w.actual_end_dt,w.planned_end_dt,'$filter_starting_dt'))";
            if(!empty($filter_ending_dt))
            {
                $where[] = "('$filter_ending_dt' >= COALESCE(w.actual_start_dt,w.planned_start_dt,'$filter_ending_dt'))";
            }
            $where[] = "w.owner_personid in ($inlist_personid)";
            if(isset($filter_status_attribute_ar['terminal_yn']) && $filter_status_attribute_ar['terminal_yn'] !== NULL)
            {
                $where[] = "terminal_yn=" . $filter_status_attribute_ar['terminal_yn'];
            }
            if(isset($filter_status_attribute_ar['workstarted_yn']) && $filter_status_attribute_ar['workstarted_yn'] !== NULL)
            {
                $where[] = "workstarted_yn=" . $filter_status_attribute_ar['workstarted_yn'];
            }
            if(count($excluded_projects_ar) > 0)
            {
                $notin_projectid = implode(",", $excluded_projects_ar);
                $where[] = "w.owner_projectid not in (" . $notin_projectid . ")";
            }
            if(count($workitems2exclude) > 0)
            {
                $not_wid = implode(",", $workitems2exclude);
                $where[] = "w.id not in (" . $not_wid . ")";
            }
            $where_exp = implode(" and ", $where);
            $sSQL = "select $fields_exp "
                    . " from $from_exp "
                    . " where $where_exp"
                    . " order by w.owner_projectid";
            $result = db_query($sSQL);
            $cannot_compute_count = 0;
            $cannot_compute_reasons = [];
            $cannot_compute_people = [];
            $cannot_compute_workitems = [];
            while($record = $result->fetchAssoc())
            {
                $owner_projectid = $record['owner_projectid'];
                $wid = $record['wid'];
                $owner_personid = $record['owner_personid'];
                $remaining_effort_hours = $record['remaining_effort_hours'];
                $found_personid_map[$owner_personid] = $owner_personid;
                $found_projectid_map[$owner_projectid] = $owner_projectid;
                
                
                $start_dt = $this->getBestDate($record['actual_start_dt'], $record['planned_start_dt']);
                if(!empty($start_dt))
                {
                    //getBestDate($actual_dt, $planned_dt, $effort_hours=0, $reference_dt=NULL, $direction=1)
                    $end_dt = $this->getBestDate($record['actual_end_dt'], $record['planned_end_dt'], $remaining_effort_hours, $start_dt, 1);
                } else {
                    $end_dt = $this->getBestDate($record['actual_end_dt'], $record['planned_end_dt']);
                    if(!empty($end_dt))
                    {
                        $shift_days = -1 * $remaining_effort_hours/8;
                        $start_dt_timestamp = UtilityGeneralFormulas::getDayShiftedDateAsTimestamp($end_dt, $shift_days);
                        $start_dt = gmdate("Y-m-d", $start_dt_timestamp);
                    } else {
                        //Could not figure out any date constraints
                        $start_dt = NULL;
                        $end_dt = NULL;
                    }
                }
                $record['filter_starting_dt'] = $filter_starting_dt;
                if(empty($start_dt) || $start_dt < $filter_starting_dt)
                {
                    //Because we don't measure anything before the filter start date!
                    $start_dt = $filter_starting_dt;
                }
                $record['effective_start_dt'] = $start_dt;
                $record['effective_end_dt'] = $end_dt;
                if(empty($min_dt) || $min_dt > $start_dt)
                {
                    $min_dt = $start_dt;
                }
                if(empty($max_dt) || $max_dt < $end_dt)
                {
                    $max_dt = $end_dt;
                }
                
                if(!isset($by_workitem[$wid]))
                {
                    $by_workitem[$wid] = [];
                }
                $by_workitem[$wid] = $record;
                if(!isset($by_personid[$owner_personid]))
                {
                    $by_personid[$owner_personid] = [];
                    $by_personid[$owner_personid]['smartbucket'] = new \bigfathom\DateRangeSmartNumberBucket($owner_personid);
                    $by_personid[$owner_personid]['cannot_compute'] = [];
                }
                if(!empty($end_dt))
                {
                    $by_personid[$owner_personid]['smartbucket']->addWorkitemData($start_dt,$end_dt,$record);
                } else {
                    $reason = "missing end date";
                    $by_personid[$owner_personid]['cannot_compute'][] = array('reason'=>$reason, "record"=>$record);
                    $by_workitem[$wid]['cannot_compute'][] = array('reason'=>$reason, "record"=>$record);
                    $cannot_compute_reasons[$reason] = $reason;
                    $cannot_compute_count++;
                    $cannot_compute_people[$owner_personid] = $owner_personid;
                    $cannot_compute_workitems[$wid] = $wid;
                }
                $by_personid[$owner_personid]['fullname'] = $record['first_nm'] . ' ' . $record['last_nm'];
                $by_workitem[$wid]['workowner']['fullname'] = $record['first_nm'] . ' ' . $record['last_nm'];
                $by_workitem[$wid]['workowner']['id'] = $owner_personid;
            }
            
            //Create intervals for each person such that they align to the relevant sprints
            $sprints = $this->getProjectSprintInfoByID($found_projectid_map);
            foreach($found_personid_map as $owner_personid)
            {
                foreach($sprints as $sprintid=>$sprintinfo)
                {
                    $start_dt = $sprintinfo['start_dt'];
                    $end_dt = $sprintinfo['end_dt'];
                    $sprintinfo['sprintid'] = $sprintid;
                    $sprintinfo['effective_start_dt'] = $start_dt;
                    $sprintinfo['effective_end_dt'] = $end_dt;
                    $by_personid[$owner_personid]['smartbucket']->addSprintData($start_dt, $end_dt, $sprintinfo);
                }
            }

            $summary['min_dt'] = $min_dt;
            $summary['max_dt'] = $max_dt;
            
            $metadata['people'] = $personid_ar;
            $daterangeinfo = [];
            $daterangeinfo['start_dt'] = $filter_starting_dt;
            if(!empty($filter_ending_dt))
            {
                $daterangeinfo['end_dt'] = $filter_ending_dt;
            }
            $metadata['daterange'] = $daterangeinfo;
            $bundle['metadata'] = $metadata;
            
            $summary['cannot_compute'] = array('count' => $cannot_compute_count
                        , 'reasons'=>array_keys($cannot_compute_reasons)
                        , 'people'=>array_keys($cannot_compute_people)
                        , 'workitems'=>array_keys($cannot_compute_workitems)
                        );
            $bundle['summary'] = $summary;
            $bundle['by_daterange'] = $by_daterange;
            $bundle['by_project'] = $by_project; 
            $bundle['lookup']['workitem'] = $by_workitem;  //Just workitem details
            $bundle['by_personid'] = $by_personid;  //Smart bucket for person per significant date ranges
            $bundle['found_projectid_map'] = $found_projectid_map;
 
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getTagMapBundle($projectid=NULL)
    {
        try
        {
            $bundle = [];
            $maps_bundle = [];
            /*
            if(empty($projectid))
            {
                $maps_bundle['project'] = $this->getTagMapBundleForType('project');
            }
            */
            $maps_bundle['testcase'] = $this->getTagMapBundleForType('testcase', $projectid);
            $maps_bundle['usecase'] = $this->getTagMapBundleForType('usecase', $projectid);
            //$maps_bundle['sprint'] = $this->getTagMapBundleForType('sprint', $projectid);
            $maps_bundle['workitem'] = $this->getTagMapBundleForType('workitem', $projectid);
            
            $bundle['maps'] = $maps_bundle;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getTagMapBundleForType($typename=NULL, $projectid=NULL)
    {
        try
        {
            $idname = "{$typename}id";
            if($typename == 'project') {
                $main_tablename = DatabaseNamesHelper::$m_map_tag2project_tablename;
                $join1_tablename = DatabaseNamesHelper::$m_project_tablename;
                $join2_tablename = DatabaseNamesHelper::$m_workitem_tablename;
                $filter_criteria = NULL;
                $jfields_tx = ",jt.id as owner_projectid, j2t.status_cd";
                $sSQL = "SELECT"
                        . " mt.tag_tx,mt.$idname,mt.created_by_personid,mt.created_dt"
                        . " $jfields_tx"
                        . " FROM $main_tablename mt"
                        . " LEFT JOIN $join1_tablename jt ON jt.id=mt.{$idname}"
                        . " LEFT JOIN $join2_tablename j2t ON j2t.owner_projectid=jt.id";
            } else {
                $jfields_tx = ",jt.owner_projectid, jt.status_cd";
                if(empty($projectid))
                {
                    $filter_criteria = NULL;
                } else {
                    $filter_criteria = "jt.owner_projectid=$projectid";
                }
                if($typename == 'workitem')
                {
                    $main_tablename = DatabaseNamesHelper::$m_map_tag2workitem_tablename;
                    $join1_tablename = DatabaseNamesHelper::$m_workitem_tablename;
                } else if($typename == 'sprint') {
                    $main_tablename = DatabaseNamesHelper::$m_map_tag2sprint_tablename;
                    $join1_tablename = DatabaseNamesHelper::$m_sprint_tablename;
                } else if($typename == 'usecase') {
                    $main_tablename = DatabaseNamesHelper::$m_map_tag2usecase_tablename;
                    $join1_tablename = DatabaseNamesHelper::$m_usecase_tablename;
                } else if($typename == 'testcase') {
                    $main_tablename = DatabaseNamesHelper::$m_map_tag2testcase_tablename;
                    $join1_tablename = DatabaseNamesHelper::$m_testcase_tablename;
                } else {
                    throw new \Exception("No handler for typename=$typename!");
                }
                $sSQL = "SELECT"
                        . " mt.tag_tx,mt.$idname,mt.created_by_personid,mt.created_dt"
                        . " $jfields_tx"
                        . " FROM $main_tablename mt"
                        . " LEFT JOIN $join1_tablename jt ON jt.id=mt.{$idname}";
            }
            $themap = array();
            if($filter_criteria != NULL)
            {
                $sSQL .= " WHERE $filter_criteria";
            }
            $sSQL .= " ORDER BY tag_tx";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $tag_tx = $record['tag_tx'];
                if(!empty($tag_tx))
                {
                    $id = $record[$idname];
                    $themap[$tag_tx][$id] = $record; 
                }
            }     
            $bundle = [];
            $bundle['metadata']['sql'] = $sSQL;
            $bundle['map'] = $themap;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getCountLocations()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_location_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountEquipment()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_equipment_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountExternalResources()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_external_resource_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountGroups()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_group_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountPeople()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_person_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountProjects()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_project_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountWorkitemsInProject($projectid)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid");
        }
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_workitem_tablename
                . " WHERE active_yn=1 and owner_projectid=$projectid";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountBaselinesInProject($projectid)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid");
        }
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_project_baseline_tablename
                . " WHERE mark_deleted_yn=0 and projectid=$projectid";
        return db_query($sSQL)->rowCount();
    }
    
    public function getCountTemplates()
    {
        $sSQL = "SELECT 1"
                . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename
                . " WHERE active_yn=1";
        return db_query($sSQL)->rowCount();
    }
    
    public function isProjectAvailable($projectid)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        try
        {
            $sSQL = "SELECT 1"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename
                    . " WHERE id=$projectid AND active_yn=1";
            return db_query($sSQL)->rowCount() > 0;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}

