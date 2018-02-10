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

/**
 * This class tells us what is in use
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ConnectionChecker
{
    private $m_project_tablename = 'bigfathom_project';
    private $m_workitem_tablename = 'bigfathom_workitem';
    private $m_map_wi2wi_tablename = 'bigfathom_map_wi2wi';
    private $m_map_person2role_in_group_tablename = 'bigfathom_map_person2role_in_group';
    private $m_map_person2systemrole_in_group_tablename = 'bigfathom_map_person2systemrole_in_group';
    
    public function getConnectionsOfWorkitem($goalid)
    {
        try
        {
            if($goalid == '')
            {
                throw new \Exception("Missing ID!");
            }
            $info_bundle = array();
            $connection_details = array();

            $parent_of_goals_sql = "SELECT antwiid as id"
                    . " FROM {$this->m_map_wi2wi_tablename}"
                    . " WHERE depwiid=$goalid";
            $parent_of_goals_result = db_query($parent_of_goals_sql);
            $ddw_list = $parent_of_goals_result->fetchAllAssoc('id');

            $child_of_goals_sql = "SELECT depwiid as id"
                    . " FROM {$this->m_map_wi2wi_tablename}"
                    . " WHERE antwiid=$goalid";
            $child_of_goals_result = db_query($child_of_goals_sql);
            $daw_list = $child_of_goals_result->fetchAllAssoc('id');
            
            $total_connections = count($daw_list);
            $info_bundle['connections_found'] = ($total_connections > 0);
            $info_bundle['critical_connections_found'] = 0;
            $connection_details['ddw_list'] = $ddw_list;
            $connection_details['daw_list'] = $daw_list;

            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getConnectionsOfSprint($sprintid)
    {
        try
        {
            $info_bundle = array();
            $connection_details = array();
            
            //TODO
            $info_bundle['connections_found'] = FALSE;  //TODO
            $info_bundle['critical_connections_found'] = FALSE; //TODO
                    
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getConnectionsOfProject($roleid)
    {
        try
        {
            $info_bundle = array();
            $connection_details = array();
            
            //TODO
            $info_bundle['connections_found'] = FALSE;  //TODO
            $info_bundle['critical_connections_found'] = FALSE; //TODO

            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfExternalResource($external_resourceid)
    {
        try
        {
            if(empty($external_resourceid))
            {
                throw new \Exception("Missing external_resourceid!");
            }
            $info_bundle = array();

            //Declare all the tables to check
            $simple_checks = [];
            $simple_checks['list_workitems'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_workitem_tablename
                            ,'pk'=>'id'
                            ,'pk_rename'=>'workitemid'
                            ,'matchfield'=>'external_resourceid'
                            ,'is_critical'=>TRUE
                    );
            
            //Now check all the tables
            $connection_info = $this->getConnectionDetails($simple_checks,$external_resourceid);
            $connections_found = $connection_info['connections_found'];
            $critical_connections_found = $connection_info['critical_connections_found'];
            $connection_details = $connection_info['connection_details'];
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfEquipment($equipmentid)
    {
        try
        {
            if(empty($equipmentid))
            {
                throw new \Exception("Missing equipmentid!");
            }
            $info_bundle = array();

            //Declare all the tables to check
            $simple_checks = [];
            $simple_checks['list_workitems'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_workitem_tablename
                            ,'pk'=>'id'
                            ,'pk_rename'=>'workitemid'
                            ,'matchfield'=>'equipmentid'
                            ,'is_critical'=>TRUE
                    );
            
            //Now check all the tables
            $connection_info = $this->getConnectionDetails($simple_checks,$equipmentid);
            $connections_found = $connection_info['connections_found'];
            $critical_connections_found = $connection_info['critical_connections_found'];
            $connection_details = $connection_info['connection_details'];
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getConnectionDetails($simple_checks,$matchvalue)
    {
        try
        {
            $connection_details = [];
            $connections_found = 0;
            $critical_connections_found = 0;
            foreach($simple_checks as $listname=>$querydetails)
            {
                $pk = $querydetails['pk'];
                $pk_rename = $querydetails['pk_rename'];
                $tablename = $querydetails['tablename'];
                $matchfield = $querydetails['matchfield'];
                $is_critical = $querydetails['is_critical'];
                $check_sql = "SELECT $pk as $pk_rename "
                        . " FROM $tablename "
                        . " WHERE $matchfield=$matchvalue";
                $check_result = db_query($check_sql);
                $check_list = [];
                if($check_result->rowCount() > 0)
                {
                    while($check_record = $check_result->fetchAssoc()) 
                    {
                        $connections_found++;
                        if($is_critical)
                        {
                            $critical_connections_found++;
                        }
                        $check_list[] = $check_record;
                    }            
                }
                $connection_details[$listname] = $check_list;
            }
            $connection_info['connections_found'] = $connections_found;
            $connection_info['critical_connections_found'] = $critical_connections_found;
            $connection_info['connection_details'] = $connection_details;
            return $connection_info;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getConnectionsOfRole($roleid)
    {
        try
        {
            if(empty($roleid))
            {
                throw new \Exception("Missing roleid!");
            }
            $info_bundle = array();

            //Declare all the tables to check
            $simple_checks = [];
            $simple_checks['map_person2role'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_person2role_tablename
                            ,'pk'=>'personid'
                            ,'pk_rename'=>'personid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['map_role2wi'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_prole2wi_tablename
                            ,'pk'=>'workitemid'
                            ,'pk_rename'=>'workitemid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['map_role2project'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_prole2project_tablename
                            ,'pk'=>'projectid'
                            ,'pk_rename'=>'projectid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['map_role2sprint'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_role2sprint_tablename
                            ,'pk'=>'sprintid'
                            ,'pk_rename'=>'sprintid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['map_external_role2ours'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_external_prole2ours_tablename
                            ,'pk'=>'id'
                            ,'pk_rename'=>'generatedid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['map_tag2role'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_tag2role_tablename
                            ,'pk'=>'tag_tx'
                            ,'pk_rename'=>'tag_tx'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>FALSE
                    );
            $simple_checks['map_person2role_in_group'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_person2role_in_group_tablename
                            ,'pk'=>'groupid'
                            ,'pk_rename'=>'groupid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>FALSE
                    );
            $simple_checks['map_person2role_in_workitem'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_person2role_in_workitem_tablename
                            ,'pk'=>'workitemid'
                            ,'pk_rename'=>'workitemid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>FALSE
                    );
            $simple_checks['map_person2role_in_sprint'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_map_person2role_in_sprint_tablename
                            ,'pk'=>'sprintid'
                            ,'pk_rename'=>'sprintid'
                            ,'matchfield'=>'roleid'
                            ,'is_critical'=>FALSE
                    );
            
            //Now check all the tables
            $connection_info = $this->getConnectionDetails($simple_checks,$roleid);
            $connections_found = $connection_info['connections_found'];
            $critical_connections_found = $connection_info['critical_connections_found'];
            $connection_details = $connection_info['connection_details'];
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfGroup($groupid)
    {
        try
        {
            if($groupid == '')
            {
                throw new \Exception("Missing ID!");
            }
            $info_bundle = array();
            $connection_details = array();

            $urg_sql = "SELECT personid, roleid"
                    . " FROM " . DatabaseNamesHelper::$m_map_person2role_in_group_tablename
                    . " WHERE groupid=$groupid";
            $urg_result = db_query($urg_sql);
            $urg_person_list = array();
            $urg_role_list = array();
            if($urg_result->rowCount() > 0)
            {
                while($urg_record = $urg_result->fetchAssoc()) 
                {
                    $personid = $urg_record['personid'];
                    if($personid != NULL)
                    {
                        $urg_person_list[$personid] = $urg_record;
                    }
                    $roleid = $urg_record['roleid'];
                    if($roleid != NULL)
                    {
                        $urg_role_list[$roleid] = $urg_record;
                    }
                }            
            }

            $usrg_sql = "SELECT personid, systemroleid"
                    . " FROM " . DatabaseNamesHelper::$m_map_person2systemrole_in_group_tablename
                    . " WHERE groupid=$groupid";
            $usrg_result = db_query($usrg_sql);
            $usrg_person_list = array();
            $usrg_role_list = array();
            if($usrg_result->rowCount() > 0)
            {
                while($usrg_record = $usrg_result->fetchAssoc()) 
                {
                    $personid = $usrg_record['personid'];
                    if($personid != NULL)
                    {
                        $usrg_person_list[$personid] = $usrg_record;
                    }
                    $systemroleid = $usrg_record['systemroleid'];
                    if($systemroleid != NULL)
                    {
                        $usrg_role_list[$systemroleid] = $usrg_record;
                    }
                }           
            }
            
            //Add things up
            $total_critical_connections = count($urg_person_list) + count($usrg_person_list);
            $total_connections = $total_critical_connections 
                    + count($urg_role_list) + count($usrg_role_list);
            
            //Finish up
            $info_bundle['connections_found'] = ($total_connections > 0);
            $info_bundle['critical_connections_found'] = ($total_critical_connections > 0);
            $connection_details['urg_person_list'] = $urg_person_list;
            $connection_details['urg_role_list'] = $urg_role_list;
            $connection_details['usrg_person_list'] = $usrg_person_list;
            $connection_details['usrg_role_list'] = $usrg_role_list;

            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfVisionStatement($visionstatementid)
    {
        try
        {
            if(empty($visionstatementid))
            {
                throw new \Exception("Missing visionstatementid!");
            }
            $info_bundle = array();
            $connection_details = [];
            $connections_found = 0;
            $critical_connections_found = 0;
            
            $core_sql = "SELECT p.id as projectid, p.root_goalid, w.workitem_nm as name "
                    . " FROM " . DatabaseNamesHelper::$m_map_visionstatement2project_tablename . " v2p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p on p.id=v2p.projectid "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w on w.id=p.root_goalid "
                    . " WHERE v2p.visionstatementid=$visionstatementid";

            $core_result = db_query($core_sql);
            $project_list = [];
            if($core_result->rowCount() > 0)
            {
                while($core_record = $core_result->fetchAssoc()) 
                {
                    $connections_found++;
                    $critical_connections_found++;
                    $project_list[] = $core_record;
                }            
            }
            $connection_details['project_list'] = $project_list;
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfProjectPortfolio($project_portfolioid)
    {
        //TODO
        return $this->getConnectionsOfProjectContext($project_portfolioid);
    }
    
    public function getConnectionsOfProjectContext($project_contextid)
    {
        try
        {
            if($project_contextid == '')
            {
                throw new \Exception("Missing project_contextid!");
            }
            $info_bundle = array();
            $connection_details = [];
            $connections_found = 0;
            $critical_connections_found = 0;
            
            $core_sql = "SELECT p.id as projectid, p.root_goalid, w.workitem_nm as name "
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w on w.id=p.root_goalid "
                    . " WHERE project_contextid=$project_contextid";

            $core_result = db_query($core_sql);
            $project_list = [];
            if($core_result->rowCount() > 0)
            {
                while($core_record = $core_result->fetchAssoc()) 
                {
                    $connections_found++;
                    $critical_connections_found++;
                    $project_list[] = $core_record;
                }            
            }
            $connection_details['project_list'] = $project_list;
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getConnectionsOfLocation($locationid)
    {
        try
        {
            if($locationid == '')
            {
                throw new \Exception("Missing locationid!");
            }
            $info_bundle = array();

            //Declare all the tables to check
            $simple_checks = [];
            $simple_checks['person_list'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_person_tablename
                            ,'pk'=>'id'
                            ,'matchfield'=>'primary_locationid'
                            ,'pk_rename'=>'personid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['equipment_list'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_equipment_tablename
                            ,'pk'=>'id'
                            ,'matchfield'=>'primary_locationid'
                            ,'pk_rename'=>'equipmentid'
                            ,'is_critical'=>TRUE
                    );
            $simple_checks['external_resource_list'] = array(
                             'tablename'=>DatabaseNamesHelper::$m_external_resource_tablename
                            ,'pk'=>'id'
                            ,'matchfield'=>'primary_locationid'
                            ,'pk_rename'=>'external_resourceid'
                            ,'is_critical'=>TRUE
                    );
            
            //Now check all the tables
            //Now check all the tables
            $connection_info = $this->getConnectionDetails($simple_checks,$locationid);
            $connections_found = $connection_info['connections_found'];
            $critical_connections_found = $connection_info['critical_connections_found'];
            $connection_details = $connection_info['connection_details'];
            
            //Finish up
            $info_bundle['connections_found'] = $connections_found;
            $info_bundle['critical_connections_found'] = $critical_connections_found;
            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getConnectionsOfPerson($personid)
    {
        try
        {
            $info_bundle = array();
            $connection_details = array();
            
            //TODO
            $info_bundle['connections_found'] = FALSE;  //TODO
            $info_bundle['critical_connections_found'] = FALSE; //TODO

            $info_bundle['details'] = $connection_details;
            return $info_bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

