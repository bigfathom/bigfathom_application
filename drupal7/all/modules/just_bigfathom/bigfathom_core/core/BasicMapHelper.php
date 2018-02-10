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

/**
 * This class tells us about fundamental mappings.
 * Try to keep this file small because it is loaded every time.
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class BasicMapHelper
{
    protected $m_oContext = NULL;
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
    }
    
    /**
     * Get the current project edit mode information
     */
    public function getProjectEditModeInfo($projectid=NULL,$include_label=TRUE)
    {
        try
        {
            $only_active=TRUE;
            $order_by_ar=NULL;
            $key_fieldname = NULL;
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $filter = "prj.id = $projectid";
            $projdetail = $this->getProjectsData($order_by_ar,$key_fieldname,$only_active,$filter);
            $record = array_pop($projdetail);
            $result = [];
            $project_edit_lock_cd = $record['project_edit_lock_cd'];
            $niceterm = "";
            switch ($project_edit_lock_cd)
            {
                case 1:
                    $niceterm = "pcgonly";
                    $label = t("Only PCG");
                    break;
                case 2:
                    $niceterm = "owneranddelegates";
                    $label = t("Project Owner and Delegates");
                    break;
                case 3:
                    $niceterm = "primaryowner";
                    $label = t("Only Primary Project Owner");
                    break;
                case 99:
                    $niceterm = "nobody";
                    $label = t("Nobody");
                    break;
                default:
                    //Treat this as open
                    $project_edit_lock_cd = 0;
                    $niceterm = "membergroups";
                    $label = t("Member Groups");
                    break; 
            }
            $result['project_edit_lock_cd'] = $project_edit_lock_cd;
            $result['project_edit_lock_term'] = $niceterm;
            if($include_label)
            {
                $result['project_edit_lock_label'] = $label;
            }
            $result['record'] = $record;
            return $result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Tell use what data actions a user can take
     * @param type $testcaseid OK IF BLANK
     * @param type $personid MUST HAVE VALUE
     * @return string
     * @throws \Exception
     */
    public function getTestCaseActionPrivsOfPerson($testcaseid,$personid)
    {
        try
        {
            $privs = "VA";

            require_once "UserAccountHelper.php";
            $uah = new \bigfathom\UserAccountHelper();
            $upb = $uah->getUserProfileBundle();
            $is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
            $is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        
            $is_owner = $is_systemadmin || $is_systemdatatrustee;
            if(!$is_owner && !empty($testcaseid))
            {
                if($this->isPersonTestCaseOwner($testcaseid, $personid))
                {
                    $is_owner = TRUE;
                } else {
                    $projectid = $this->getProjectIDForTestcase($testcaseid);
                    if($this->isPersonProjectOwner($projectid, $personid))
                    {
                        $is_owner = TRUE;
                    }
                }
            }
            if($is_owner)
            {
                $privs .= "EDT";
            }

            return $privs;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Tell use what data actions a user can take
     * @param type $testcaseid OK IF BLANK
     * @param type $personid MUST HAVE VALUE
     * @return string
     * @throws \Exception
     */
    public function getUseCaseActionPrivsOfPerson($usecaseid,$personid)
    {
        try
        {
            $privs = "VA";

            require_once "UserAccountHelper.php";
            $uah = new \bigfathom\UserAccountHelper();
            $upb = $uah->getUserProfileBundle();
            $is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
            $is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        
            $is_owner = $is_systemadmin || $is_systemdatatrustee;
            
            if(!$is_owner && !empty($usecaseid))
            {
                if($this->isPersonUseCaseOwner($usecaseid, $personid))
                {
                    $is_owner = TRUE;
                } else {
                    $projectid = $this->getProjectIDForUsecase($usecaseid);
                    if($this->isPersonProjectOwner($projectid, $personid))
                    {
                        $is_owner = TRUE;
                    }
                }
            }
            if($is_owner)
            {
                $privs .= "ED";
            }

            return $privs;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function isPersonTestCaseOwner($testcaseid, $personid)
    {
        try
        {
            if(!isset($testcaseid))
            {
                throw new \Exception("Missing required testcaseid!");
            }
            if(!isset($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $sSQL = "SELECT owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_testcase_tablename." s"
                    . " WHERE s.id=$testcaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $owner_personid = $record['owner_personid'];
            return ($owner_personid == $personid);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function isPersonUseCaseOwner($usecaseid, $personid)
    {
        try
        {
            if(!isset($usecaseid))
            {
                throw new \Exception("Missing required usecaseid!");
            }
            if(!isset($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $sSQL = "SELECT owner_personid"
                    . " FROM ".DatabaseNamesHelper::$m_usecase_tablename." s"
                    . " WHERE s.id=$usecaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $owner_personid = $record['owner_personid'];
            return ($owner_personid == $personid);
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function isPersonProjectOwner($projectid, $personid, $check_root_goal=TRUE)
    {
        try
        {
            if(!isset($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(!isset($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $is_owner = FALSE;
            $sSQL = "SELECT owner_personid, root_goalid "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." s"
                    . " WHERE s.id=$projectid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $owner_personid = $record['owner_personid'];
            $root_goalid = $record['root_goalid'];
            $is_owner = ($owner_personid == $personid);
            if(!$is_owner && $check_root_goal)
            {
                //Keep checking --- try the root goal
                $sSQL = "SELECT owner_personid "
                        . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." s"
                        . " WHERE s.id=$root_goalid";
                $result = db_query($sSQL);
                $record = $result->fetchAssoc();
                $owner_personid = $record['owner_personid'];
                $is_owner = ($owner_personid == $personid);
                if(!$is_owner)
                {
                    //Keep checking --- try the delegate owners of root goal
                    $root_goalid = $record['root_goalid'];
                    $sSQL = "SELECT count(1) as hits "
                            . " FROM ".DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename." s"
                            . " WHERE s.workitemid=$root_goalid and s.personid=$personid";
                    $result = db_query($sSQL);
                    $record = $result->fetchAssoc();
                    $hits = $record['hits'];
                    $is_owner = hits > 0;
                }
            }
            return $is_owner;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the details about one project
     */
    public function getOneProjectDetailData($projectid=NULL, $only_active=TRUE, $show_warning_on_missing=TRUE)
    {
        try
        {
            $order_by_ar=NULL;
            $key_fieldname = NULL;
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $filter = "prj.id = $projectid";
            $projdetail = $this->getProjectsData($order_by_ar,$key_fieldname,$only_active,$filter);
            if(empty($projdetail) && $show_warning_on_missing)
            {
                drupal_set_message("Did not find an active project with id=$projectid",'warning');
                /*
                drupal_set_message("ERROR Did not find project record for projectid=[$projectid]",'error');
                drupal_set_message("ERROR INFO key_fieldname=[$key_fieldname]",'error');
                drupal_set_message("ERROR INFO only_active=[$only_active]",'error');
                drupal_set_message("ERROR INFO filter=[$filter]",'error');
                throw new \Exception("Missing project record for projectid=[$projectid]");
                 * 
                 */
            }
            return array_pop($projdetail);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getAllProj2Proj4OneRootPID($rootpid, &$p2p_maps)
    {
        try
        {
            $map_path = [];
            $ants = isset($p2p_maps['p2sp'][$rootpid]) ? $p2p_maps['p2sp'][$rootpid] : [];
            
            foreach($ants as $antpid)
            {
                $map_path[$antpid] = $this->getAllProj2Proj4OneRootPID($antpid, $p2p_maps);
            }
            return $map_path;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllProj2ProjPaths()
    {
        try
        {
            $p2p_maps = $this->getAllBareProject2ProjectMaps();
            $roots = [];
            foreach($p2p_maps['p2sp'] as $deppid=>$antpid)
            {
                if(!isset($p2p_maps['sp2p'][$deppid]))
                {
                    //This one does not have any dependents
                    $roots[$deppid] = $deppid;
                }
            }
            $map_all_root2path = [];
            foreach($roots as $rootpid)
            {
                $map_all_root2path[$rootpid] = $this->getAllProj2Proj4OneRootPID($rootpid, $p2p_maps);
            }
            $all_unmapped_projectids = $this->getAllNonLinkedProjectIDs();
            foreach($all_unmapped_projectids as $projectid)
            {
                $map_all_root2path[$projectid] = [];
            }
            return $map_all_root2path;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectID4PubID($pubid)
    {
        try
        {
            $sql = "SELECT projectid"
                    . " FROM " . DatabaseNamesHelper::$m_published_project_info_tablename . " p"
                    . " WHERE id=$pubid" ;
            
            $result = db_query($sql);
            $record = $result->fetchAssoc();
            $projectid = $record['projectid'];
            return $projectid;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllNonLinkedProjectIDs()
    {
        try
        {
            $themap = [];
            $sql = "SELECT p.id as projectid"
                    . " FROM " . DatabaseNamesHelper::$m_project_tablename . " p"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_subproject2project_tablename . " pa ON pa.subprojectid=p.id" 
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_map_subproject2project_tablename . " pd ON pd.projectid=p.id"
                    . " WHERE p.active_yn=1 and (pa.subprojectid IS NULL and pd.projectid IS NULL)" ;
            
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['projectid'];
                $themap[$projectid] = $projectid;
            }
            
            return $themap;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllBareProject2ProjectMaps()
    {
        try
        {
            $map_sp2p = [];
            $map_p2sp = [];
            $sql_sp2p = "SELECT subprojectid, projectid"
                    . " FROM " . DatabaseNamesHelper::$m_map_subproject2project_tablename . " "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " pa ON pa.id=subprojectid" 
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " pd ON pd.id=projectid"
                    . " WHERE pa.active_yn=1 and pd.active_yn=1" ;
            
            $result = db_query($sql_sp2p);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['projectid'];
                $subprojectid = $record['subprojectid'];
                
                if(!isset($map_sp2p[$subprojectid]))
                {
                    $map_sp2p[$subprojectid] = [];
                }
                $map_sp2p[$subprojectid][$projectid] = $projectid;
                
                if(!isset($map_p2sp[$projectid]))
                {
                    $map_p2sp[$projectid] = [];
                }
                $map_p2sp[$projectid][$subprojectid] = $subprojectid;
            }
            
            $bundle = [];
            $bundle['sp2p'] = $map_sp2p;
            $bundle['p2sp'] = $map_p2sp;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getOneTPDetailData($template_projectid=NULL,$only_active=TRUE)
    {
        try
        {
            $order_by_ar=NULL;
            $key_fieldname = NULL;
            if(empty($template_projectid))
            {
                throw new \Exception("Missing required templateid!");
            }
            $filter = "prj.id = $template_projectid";
            $projdetail = $this->getTPData($order_by_ar,$key_fieldname,$only_active,$filter);
            return array_pop($projdetail);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllProjectRootGoalIDMapBundle($active_yn=1)
    {
        try
        {
            $the_p2g_map = [];
            $the_g2p_map = [];
            
            $sSQL = "SELECT prj.id, prj.root_goalid"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj";
            if($active_yn !== NULL)
            {
                $sSQL  .= " WHERE active_yn=$active_yn";
            }

            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['id'];
                $root_goalid = $record['root_goalid'];
                
                $the_p2g_map[$projectid] = $root_goalid;
                $the_g2p_map[$root_goalid] = $projectid;
            }

            $bundle = array(
                    "map_p2g"=>$the_p2g_map,
                    "map_g2p"=>$the_g2p_map,
                );
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getOneRichRemoteURIRecord($domain_tx, $throw_exception_if_missing=TRUE)
    {
        try
        {
            $clean_domain_tx = trim(strtolower($domain_tx));
            $sSQL1 = "SELECT 'BLACK' as list_type, d.remote_uri_domain, d.created_by_personid, d.created_dt"
                    . " ,p.shortname, p.first_nm, p.last_nm"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_blacklist_tablename . " d"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename . " p ON p.id=d.created_by_personid"
                    . " WHERE remote_uri_domain='$clean_domain_tx'";
            $sSQL2 = "SELECT 'WHITE' as list_type, d.remote_uri_domain, d.created_by_personid, d.created_dt"
                    . " ,p.shortname, p.first_nm, p.last_nm"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename . " d"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename . " p ON p.id=d.created_by_personid"
                    . " WHERE remote_uri_domain='$clean_domain_tx'";
            
            $union_sql = "$sSQL1 UNION $sSQL2";
            $result = db_query($union_sql);
            $record = $result->fetchAssoc();
            if(empty($record))
            {
                if($throw_exception_if_missing)
                {
                    throw new \Exception("There is no record for domain '$clean_domain_tx'");
                }
                $record = NULL;
            } else {
                $person_name = trim($record['first_nm'] . " " . $record['last_nm']);
                $record['person_name'] = $person_name;
            }

            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllRichRemoteURIBundle()
    {
        try
        {
            $bundle = [];
            $domain_blacklist = [];
            $domain_whitelist = [];
            $sSQL1 = "SELECT d.remote_uri_domain, d.created_by_personid, d.created_dt"
                    . " ,p.shortname, p.first_nm, p.last_nm"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_blacklist_tablename . " d"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename . " p ON p.id=d.created_by_personid"
                    . " ORDER BY remote_uri_domain";
            
            $result1 = db_query($sSQL1);
            while($record = $result1->fetchAssoc()) 
            {
                $person_name = trim($record['first_nm'] . " " . $record['last_nm']);
                $remote_uri_domain = $record['remote_uri_domain'];
                $record['person_name'] = $person_name;
                $domain_blacklist[$remote_uri_domain] = $record;
            }
            
            $sSQL2 = "SELECT d.remote_uri_domain, d.created_by_personid, d.created_dt"
                    . " ,p.shortname, p.first_nm, p.last_nm"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename . " d"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename . " p ON p.id=d.created_by_personid"
                    . " ORDER BY remote_uri_domain";
            
            $result2 = db_query($sSQL2);
            while($record = $result2->fetchAssoc()) 
            {
                $person_name = trim($record['first_nm'] . " " . $record['last_nm']);
                $remote_uri_domain = $record['remote_uri_domain'];
                $record['person_name'] = $person_name;
                $domain_whitelist[$remote_uri_domain] = $record;
            }
            
            $bundle['blacklist_domains'] = $domain_blacklist;
            $bundle['whitelist_domains'] = $domain_whitelist;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllRemoteURIFilterRules()
    {
        try
        {
            $scheme_whitelist = [];
            $domain_whitelist = [];
            
            $sSQL1 = "SELECT remote_uri_scheme"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_scheme_whitelist_tablename
                    . " ORDER BY remote_uri_scheme";
            $result1 = db_query($sSQL1);
            while($record = $result1->fetchAssoc()) 
            {
                $remote_uri_scheme = $record['remote_uri_scheme'];
                $scheme_whitelist[$remote_uri_scheme] = $remote_uri_scheme;
            }

            $sSQL2 = "SELECT remote_uri_domain"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename
                    . " ORDER BY remote_uri_domain";
            $result2 = db_query($sSQL2);
            while($record = $result2->fetchAssoc()) 
            {
                $remote_uri_domain = $record['remote_uri_domain'];
                $domain_whitelist[$remote_uri_domain] = $remote_uri_domain;
            }
            
            $bundle = array(
                    "scheme_whitelist"=>$scheme_whitelist,
                    "domain_whitelist"=>$domain_whitelist,
                );
            
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getAllProjectRootGoalNameMapBundle($project_active_yn=NULL)
    {
        try
        {
            $the_p2g_map = [];
            $the_g2p_map = [];
            
            $sSQL = "SELECT prj.id, prn2p.publishedrefname, prj.allow_status_publish_yn, prj.allow_refresh_from_remote_yn, prn2p.remote_uri,"
                    . " prj.root_goalid, g.workitem_nm as root_workitem_nm"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on prj.root_goalid=g.id and g.workitem_basetype='G'"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2project_tablename." prn2p on prn2p.projectid=prj.id";
            if($project_active_yn !== NULL)
            {
                $sSQL  .= " WHERE prj.active_yn=$project_active_yn";
            }

            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $projectid = $record['id'];
                $root_goalid = $record['root_goalid'];
                $root_workitem_nm = $record['root_workitem_nm'];
                
                $the_p2g_map[$projectid] = array('root_goalid'=>$root_goalid, 'root_workitem_nm'=>$root_workitem_nm);
                $the_g2p_map[$root_goalid] = array('projectid'=>$projectid, 'root_workitem_nm'=>$root_workitem_nm);
            }
        
            $bundle = array(
                    "map_p2g"=>$the_p2g_map,
                    "map_g2p"=>$the_g2p_map,
                );
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllProjectRootGoalNameMapBundle because ".$ex,98777,$ex);
        }
    }
    
    public function getAllTemplateRootGoalNameMapBundle($tp_active_yn=NULL)
    {
        try
        {
            $the_p2g_map = [];
            $the_g2p_map = [];
            
            $sSQL = "SELECT prj.id, prn2p.publishedrefname, prn2p.remote_uri,"
                    . " prj.root_template_workitemid, g.workitem_nm as root_workitem_nm"
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." g on prj.root_template_workitemid=g.id and g.workitem_basetype='G'"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename." prn2p on prn2p.template_projectid=prj.id";
            if($tp_active_yn !== NULL)
            {
                $sSQL  .= " WHERE prj.active_yn=$tp_active_yn";
            }

            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $template_projectid = $record['id'];
                $root_template_workitemid = $record['root_template_workitemid'];
                $root_workitem_nm = $record['root_workitem_nm'];
                
                $the_p2g_map[$template_projectid] = array('root_template_workitemid'=>$root_template_workitemid, 'root_workitem_nm'=>$root_workitem_nm);
                $the_g2p_map[$root_template_workitemid] = array('template_projectid'=>$template_projectid, 'root_workitem_nm'=>$root_workitem_nm);
            }
        
            $bundle = array(
                    "map_tp2tw"=>$the_p2g_map,
                    "map_tw2tp"=>$the_g2p_map,
                );
            return $bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getAllTemplateRootGoalNameMapBundle because ".$ex,98777,$ex);
        }
    }
    
    public function getProjectOwnersBundle($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $all = [];
            
            $sSQL1 = "SELECT owner_personid, root_goalid FROM " . DatabaseNamesHelper::$m_project_tablename . " WHERE id=$projectid";
            $result1 = db_query($sSQL1);
            $record1 = $result1->fetchAssoc();
            $owner_personid = $record1['owner_personid'];
            $root_goalid = $record1['root_goalid'];
            $all[$owner_personid] = 'primary';
            
            $delegate_owner = [];
            $sSQL2 = "SELECT personid FROM " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " WHERE workitemid=$root_goalid";
            $result2 = db_query($sSQL2);
            while($record2 = $result2->fetchAssoc()) 
            {
                $personid = $record2['personid'];
                $all[$personid] = 'delegate';
                $delegate_owner[] = $personid;
            }
            
            $bundle['primary_owner'] = $owner_personid;
            $bundle['delegate_owner'] = $delegate_owner;
            $bundle['all'] = $all;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectChangeHistoryBundle($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $metadata = [];
            $SQL_projmetadata = "select * from " . DatabaseNamesHelper::$m_project_tablename . " where id=$projectid";
            $metadata['project'] = db_query($SQL_projmetadata)->fetchAssoc();
            
            $max_dt_project = NULL;
            $overview_project = [];
            $rows_project = [];
            $sSQL_project = "SELECT updated_dt,changed_by_uid,comment_tx"
                    . " FROM " . DatabaseNamesHelper::$m_project_recent_data_updates_tablename 
                    . " WHERE id=$projectid"
                    . " ORDER BY updated_dt";
            $result_project = db_query($sSQL_project);
            while($record_project = $result_project->fetchAssoc()) 
            {
                $updated_dt = $record_project['updated_dt'];
                if(empty($max_dt_project) || $max_dt_project > $updated_dt)
                {
                    $max_dt_project = $updated_dt;
                }
                $rows_project[] = $record_project;
            }
            $overview_project['edit_count'] = count($rows_project);
            $overview_project['max_dt'] = $max_dt_project;

            $max_dt_sprint = NULL;
            $overview_sprint = [];
            $rows_sprint = [];
            $sSQL_sprint = "SELECT sh.sprintid,sh.updated_dt,sh.changed_by_uid,sh.comment_tx"
                    . " FROM " . DatabaseNamesHelper::$m_sprint_recent_data_updates_tablename . " sh"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_sprint_tablename . " s on s.id=sh.sprintid" 
                    . " WHERE s.owner_projectid=$projectid"
                    . " ORDER BY updated_dt";
            $result_sprint = db_query($sSQL_sprint);
            while($record_sprint = $result_sprint->fetchAssoc()) 
            {
                $id = $record_sprint['sprintid'];
                $updated_dt = $record_sprint['updated_dt'];
                if(empty($max_dt_sprint) || $max_dt_sprint > $updated_dt)
                {
                    $max_dt_sprint = $updated_dt;
                }
                if(!isset($overview_sprint['by_sprintid'][$id]['edit_count']))
                {
                    $overview_sprint['by_sprintid'][$id]['edit_count'] = 1;
                } else {
                    $overview_sprint['by_sprintid'][$id]['edit_count'] += 1;
                }
                if(!isset($overview_sprint['by_sprintid'][$id]['most_recent_edit_dt']))
                {
                    $overview_sprint['by_sprintid'][$id]['most_recent_edit_dt'] = $updated_dt;
                } else {
                    if($overview_sprint['by_sprintid'][$id]['most_recent_edit_dt'] < $updated_dt)
                    {
                        $overview_sprint['by_sprintid'][$id]['most_recent_edit_dt'] = $updated_dt;
                    }
                }
                $rows_sprint[] = $record_sprint;
            }
            $overview_sprint['edit_count'] = count($rows_sprint);
            $overview_sprint['max_dt'] = $max_dt_sprint;

            $max_dt_workitem = NULL;
            $overview_workitem = [];
            $rows_workitem = [];
            $sSQL_workitem = "SELECT wh.workitemid,wh.updated_dt,wh.changed_by_uid,wh.comment_tx"
                    . " FROM " . DatabaseNamesHelper::$m_workitem_recent_data_updates_tablename . " wh" 
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_workitem_tablename . " w on w.id=wh.workitemid" 
                    . " WHERE w.owner_projectid=$projectid"
                    . " ORDER BY updated_dt";
            $result_workitem = db_query($sSQL_workitem);
            while($record_workitem = $result_workitem->fetchAssoc()) 
            {
                $id = $record_workitem['workitemid'];
                $updated_dt = $record_workitem['updated_dt'];
                if(empty($max_dt_workitem) || $max_dt_workitem > $updated_dt)
                {
                    $max_dt_workitem = $updated_dt;
                }
                if(!isset($overview_workitem['by_workitemid'][$id]['edit_count']))
                {
                    $overview_workitem['by_workitemid'][$id]['edit_count'] = 1;
                } else {
                    $overview_workitem['by_workitemid'][$id]['edit_count'] += 1;
                }
                if(!isset($overview_workitem['by_workitemid'][$id]['most_recent_edit_dt']))
                {
                    $overview_workitem['by_workitemid'][$id]['most_recent_edit_dt'] = $updated_dt;
                } else {
                    if($overview_workitem['by_workitemid'][$id]['most_recent_edit_dt'] < $updated_dt)
                    {
                        $overview_workitem['by_workitemid'][$id]['most_recent_edit_dt'] = $updated_dt;
                    }
                }
                $rows_workitem[] = $record_workitem;
            }
            $overview_workitem['edit_count'] = count($rows_workitem);
            $overview_workitem['max_dt'] = $max_dt_workitem;
            
            $bundle = [];
            $bundle['metadata'] = $metadata;
            $bundle['debug']['sql'][] = $sSQL_project;
            $bundle['debug']['sql'][] = $sSQL_sprint;
            $bundle['debug']['sql'][] = $sSQL_workitem;
            $bundle['overview']['project'] = $overview_project;
            $bundle['details']['project'] = $rows_project;
            $bundle['overview']['sprint'] = $overview_sprint;
            $bundle['details']['sprint'] = $rows_sprint;
            $bundle['overview']['workitem'] = $overview_workitem;
            $bundle['details']['workitem'] = $rows_workitem;
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return a rich map of project detail
     */
    protected function getProjectsData($order_by_ar=NULL, $key_fieldname='id', $only_active=TRUE, $filter_criteria_tx=NULL)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT"
                    . " prj.id, prj.surrogate_yn, prj.source_type, prj.allow_refresh_from_remote_yn, "
                    . " prj.allow_status_publish_yn, prj.allow_detail_publish_yn, "
                    . " prj.allow_publish_item_owner_name_yn, prj.allow_publish_item_onbudget_p_yn, "
                    . " prj.allow_publish_item_actual_start_dt_yn, "
                    . " prj.allow_publish_item_actual_end_dt_yn, "
                    . " prj.snippet_bundle_head_yn, prj.template_yn, prj.template_author_nm, "
                    . " prj.archive_yn, "
                    . " prj.ob_scf as project_ob_scf, prj.obsu as project_obsu, "
                    . " prj.ot_scf as project_ot_scf, prj.otsu as project_otsu, "
                    . " prj.surrogate_ob_p, prj.surrogate_ot_p, "
                    . " prn2p.publishedrefname, prn2p.remote_uri, "
                    . " prj.root_goalid, prj.project_contextid, "
                    . " prj.mission_tx, prj.owner_personid, prj.active_yn, "
                    . " prj.original_source_template_refname, prj.original_source_templateid, prj.original_source_template_updated_dt, "
                    . " prj.project_edit_lock_cd, prj.show_cd, "
                    . " s.code as status_cd, s.title_tx as status_title_tx, "
                    . " s.workstarted_yn as status_workstarted_yn, "
                    . " s.terminal_yn as status_terminal_yn, "
                    . " s.happy_yn as status_happy_yn,"
                    . " g.id as workitemid, "
                    . " g.workitem_nm as root_workitem_nm, g.status_set_dt as status_set_dt, "
                    . " g.ob_scf, g.obsu, "
                    . " g.ot_scf, g.otsu, "
                    . " g.purpose_tx, g.branch_effort_hours_est, "
                    . " g.effort_hours_est, g.effort_hours_est_p, g.effort_hours_worked_est, g.effort_hours_worked_act, "
                    . " g.importance, g.planned_start_dt, g.actual_start_dt, g.planned_end_dt, g.actual_end_dt, "
                    . " g.effort_hours_est_locked_yn, g.planned_start_dt_locked_yn, g.planned_end_dt_locked_yn, "
                    . " prj.updated_dt, prj.created_dt, per.first_nm, per.last_nm, "
                    . " g.chargecode, g.limit_branch_effort_hours_cd, g.ignore_branch_cost_yn, "
                    . " g.self_allow_dep_overlap_hours, g.self_allow_dep_overlap_pct, "
                    . " g.ant_sequence_allow_overlap_hours, g.ant_sequence_allow_overlap_pct "
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." g on prj.root_goalid=g.id and g.workitem_basetype='G'"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." s on g.status_cd=s.code"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." per on per.id=prj.owner_personid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2project_tablename." prn2p on prn2p.projectid=prj.id";
            if($filter_criteria_tx != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria_tx";
            }
            if($order_by_ar == NULL)
            {
                if($key_fieldname == NULL)
                {
                    $key_fieldname='id';    
                }
                $sSQL .= " ORDER BY prj.{$key_fieldname}";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
            $project_key=NULL;
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                if(!$only_active || $record['active_yn'] == 1)
                {
                    $project_key = $record[$key_fieldname];
                    $themap[$project_key] = $record;
                    $themap[$project_key]['maps']['delegate_owner'] = [];
                }
            }
            
            //query bigfathom_map_delegate_workitemowner
            $sDO_SQL = "SELECT p.{$key_fieldname}, p.root_goalid, d2w.personid "
                    . " FROM " . DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename . " d2w "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p on p.root_goalid=d2w.workitemid "
                    . " WHERE p.root_goalid IS NOT NULL";
            $do_result = db_query($sDO_SQL);
            while($record = $do_result->fetchAssoc()) 
            {
                $pkeyvalue = $record[$key_fieldname];
                if(isset($themap[$pkeyvalue]))
                {
                    $personid = $record['personid'];
                    $themap[$pkeyvalue]['maps']['delegate_owner'][] = $personid;
                }
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a rich map of project detail
     */
    protected function getTPData($order_by_ar=NULL,$key_fieldname='id',$only_active=TRUE,$filter_criteria_tx=NULL)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT"
                    . " prj.id, prj.template_nm, prj.allow_detail_publish_yn, "
                    . " prj.snippet_bundle_head_yn, prj.template_author_nm, "
                    . " prj.ob_scf as project_ob_scf, prj.obsu as project_obsu, "
                    . " prj.ot_scf as project_ot_scf, prj.otsu as project_otsu, "
                    . " prn2p.publishedrefname, prn2p.remote_uri, "
                    . " prj.root_template_workitemid, prj.project_contextid, "
                    . " prj.submitter_blurb_tx, prj.mission_tx, prj.owner_personid, prj.active_yn, "
                    . " prj.show_cd, "
                    . " s.code as status_cd, s.title_tx as status_title_tx, "
                    . " s.workstarted_yn as status_workstarted_yn, "
                    . " s.terminal_yn as status_terminal_yn, "
                    . " s.happy_yn as status_happy_yn,"
                    . " g.id as root_template_workitemid, "
                    . " g.workitem_nm as root_workitem_nm, "
                    . " g.ob_scf, g.obsu, "
                    . " g.ot_scf, g.otsu, "
                    . " g.purpose_tx, g.branch_effort_hours_est, "
                    . " g.effort_hours_est, g.effort_hours_est_p, "
                    . " g.importance, "
                    . " g.effort_hours_est_locked_yn, "
                    . " prj.updated_dt, prj.created_dt, per.first_nm, per.last_nm, "
                    . " g.chargecode, g.limit_branch_effort_hours_cd, g.ignore_branch_cost_yn "
                    . " FROM ".DatabaseNamesHelper::$m_template_project_library_tablename." prj"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_template_workitem_tablename." g on prj.root_template_workitemid=g.id and g.workitem_basetype='G'"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." s on g.status_cd=s.code"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_person_tablename." per on per.id=prj.owner_personid"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename." prn2p on prn2p.template_projectid=prj.id";
            if($filter_criteria_tx != NULL)
            {
                $sSQL  .= " WHERE $filter_criteria_tx";
            }
            if($order_by_ar == NULL)
            {
                if($key_fieldname == NULL)
                {
                    $key_fieldname='id';    
                }
                $sSQL .= " ORDER BY prj.{$key_fieldname}";
            } else {
                $fields = implode(',', $order_by_ar);
                $sSQL .= " ORDER BY $fields";
            }
            $project_key=NULL;
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                if(!$only_active || $record['active_yn'] == 1)
                {
                    $project_key = $record[$key_fieldname];
                    $themap[$project_key] = $record;
                }
            }
            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the person associated with an availability declaration
     */
    public function getPersonIDForPersonAvailabilityID($person_availabilityid)
    {
        try
        {
            $sSQL = "SELECT personid"
                    . " FROM ".DatabaseNamesHelper::$m_map_person2availability_tablename." g"
                    . " WHERE id=$person_availabilityid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['personid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the personid of all the people in the project
     */
    public function getAllPersonIDsInProject($projectid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required $projectid!");
            }
            //TODO LIMIT TO JUST THE INDICATED PROJECT !!!!!!!!!!!
            
            $sSQL = "SELECT id "
                    . " FROM ".DatabaseNamesHelper::$m_person_tablename." p"
                    . " WHERE shortname NOT IN ('staff','admin') and active_yn=1";
            
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
     * Get the project associated with an availability declaration
     */
    public function getProjectIDForUseCaseID($usecaseid)
    {
        try
        {
            if(empty($usecaseid))
            {
                throw new \Exception("Missing required usecaseid!");
            }
            $sSQL = "SELECT owner_projectid"
                    . " FROM ".DatabaseNamesHelper::$m_usecase_tablename." g"
                    . " WHERE id=$usecaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['owner_projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the project associated with an availability declaration
     */
    public function getProjectIDForTestCaseID($testcaseid)
    {
        try
        {
            if(empty($testcaseid))
            {
                throw new \Exception("Missing required testcaseid!");
            }
            $sSQL = "SELECT owner_projectid"
                    . " FROM ".DatabaseNamesHelper::$m_testcase_tablename." g"
                    . " WHERE id=$testcaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['owner_projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the projectid of a workitem
     */
    public function getProjectIDForWorkitem($workitemid, $err_if_missing=TRUE)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Missing required workitemid!");
            }
            $sSQL = "SELECT owner_projectid as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " WHERE id=$workitemid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            if(empty($record))
            {
                if($err_if_missing)
                {
                    throw new \Exception("There is no workitemid=$workitemid!");
                } else {
                    $projectid = -1;
                }
            }
            $projectid = $record['projectid'];
            if(empty($projectid))
            {
                if($err_if_missing)
                {
                    throw new \Exception("Missing projectid for workitemid=$workitemid");
                } else {
                    $projectid = -1;
                }
            }
            return $projectid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return mapping of projectid2workitemids
     */
    public function getProjectIDsOfWorkitems($wids)
    {
        try
        {
            if(!is_array($wids))
            {
                throw new Exception("Must provide an aray of wids!");
            }
            $themap = [];
            if(count($wids)>0)
            {
                $wids_txt = implode(",", $wids);
                $sSQL = "SELECT id, owner_projectid"
                        . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                        . " WHERE w.active_yn=1 and id in ($wids_txt)";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $owner_projectid = $record['owner_projectid'];
                    if(!isset($themap[$owner_projectid]))
                    {
                        $themap[$owner_projectid] = [];
                    }
                    $themap[$owner_projectid][$id] = $id;
                }
            }
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the projectid of a brainstorm topic
     */
    public function getProjectIDForBrainstormTopic($workitemid)
    {
        try
        {
            $sSQL = "SELECT projectid"
                    . " FROM ".DatabaseNamesHelper::$m_brainstorm_item_tablename." g"
                    . " WHERE id=$workitemid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectIDForSprint($sprintid)
    {
        try
        {
            $sSQL = "SELECT owner_projectid as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." g"
                    . " WHERE id=$sprintid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectIDForUsecase($usecaseid)
    {
        try
        {
            $sSQL = "SELECT owner_projectid as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_usecase_tablename." g"
                    . " WHERE id=$usecaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectIDForTestcase($testcaseid)
    {
        try
        {
            $sSQL = "SELECT owner_projectid as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_testcase_tablename." g"
                    . " WHERE id=$testcaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['projectid'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectIDForTestcaseStepID($testcasestepid)
    {
        try
        {
            $sSQL = "SELECT owner_projectid as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_testcasestep_tablename." s"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_testcase_tablename." t ON t.id=s.testcaseid"
                    . " WHERE s.id=$testcasestepid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $projectid = $record['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Did NOT find projectid for testcasestepid=$testcasestepid");// ".$sSQL);
            }
            return $projectid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getTestcaseIDForTestcaseStepID($testcasestepid)
    {
        try
        {
            $sSQL = "SELECT testcaseid"
                    . " FROM ".DatabaseNamesHelper::$m_testcasestep_tablename." s"
                    . " WHERE s.id=$testcasestepid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $testcaseid = $record['testcaseid'];
            if(empty($testcaseid))
            {
                throw new \Exception("Did NOT find testcaseid for testcasestepid=$testcasestepid");// ".$sSQL);
            }
            return $testcaseid;
        } catch (\Exception $ex) {
            //throw new \Exception("LOOK BAD SQL THING $sSQL ::: $ex",99988,$ex);
            throw $ex;
        }
    }
    
    public function getTestcaseSteps2Detail($testcaseid)
    {
        try
        {
            $member_workitems_result = $this->getTestcaseStepsQueryResult($testcaseid);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $step_num = $record['step_num'];
                $themap[$step_num] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getTestcaseStepids2Detail($testcaseid)
    {
        try
        {
            $member_workitems_result = $this->getTestcaseStepsQueryResult($testcaseid);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    private function getTestcaseStepsQueryResult($testcaseid)
    {
        try
        {
            if(empty($testcaseid))
            {
                throw new \Exception("Missing required testcaseid!");
            }
            
            $steps_sql = "SELECT id, testcaseid, step_num, instruction_tx, expectation_tx, status_cd, executed_dt, updated_dt, created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_testcasestep_tablename
                    . " WHERE testcaseid=$testcaseid order by step_num";
            $member_workitems_result = db_query($steps_sql);
            return $member_workitems_result;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getCommunicationTestcaseStepNumbers($comid)
    {
        try
        {
            if(empty($comid))
            {
                throw new \Exception("Missing required $comid!");
            }
            if(!is_array($comid))
            {
                $comid_ar = array($comid);
            } else {
                $comid_ar = $comid;
            }
            
            $comid_in_txt = implode(",", $comid_ar);
            
            $themap = [];
            $steps_sql = "SELECT tcs.id as testcasestepid, tcs.step_num"
                    . " FROM ".DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename." c2tcs"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_testcasestep_tablename." tcs ON tcs.id=c2tcs.testcasestepid"
                    . " WHERE c2tcs.comid IN ($comid_in_txt) order by tcs.step_num";
            $member_workitems_result = db_query($steps_sql);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $step_num = $record['step_num'];
                $themap[$step_num] = $record;
            }            
            return $themap;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getCommunicationTestcaseStepsBundle($comid_selector)
    {
        try
        {
            $bundle = [];
            if(empty($comid_selector))
            {
                throw new \Exception("Missing required comid_selector!");
            }
            if(!is_array($comid_selector))
            {
                $comid_ar = array($comid_selector);
            } else {
                $comid_ar = $comid_selector;
            }
            
            $comid_in_txt = implode(",", $comid_ar);
            
            $map_stepnum2detail = [];
            $map_comid2stepnum = [];
            $steps_sql = "SELECT c2tcs.comid, tcs.id as testcasestepid, tcs.testcaseid, tcs.step_num, tcs.instruction_tx, tcs.expectation_tx, tcs.status_cd, tcs.executed_dt, tcs.updated_dt, tcs.created_dt"
                    . " FROM ".DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename." c2tcs"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_testcasestep_tablename." tcs ON tcs.id=c2tcs.testcasestepid"
                    . " WHERE c2tcs.comid IN ($comid_in_txt) order by tcs.step_num";
            $member_workitems_result = db_query($steps_sql);
            while($record = $member_workitems_result->fetchAssoc()) 
            {
                $comid = $record['comid'];
                $testcasestepid = $record['testcasestepid'];
                $step_num = $record['step_num'];
                $map_stepnum2detail[$step_num] = $record;
                $map_comid2stepnum[$comid][$step_num]=$testcasestepid;
            }    
            $bundle['map_stepnum2detail'] = $map_stepnum2detail;
            $bundle['map_comid2stepnum'] = $map_comid2stepnum;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectCommunicationContext($comid)
    {
        try
        {
            $sSQL = "SELECT c.projectid as projectid, c.parent_comid"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_project_communication_tablename." c on g.id=c.projectid"
                    . " WHERE c.id=$comid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getWorkitemCommunicationContext($comid)
    {
        try
        {
            $sSQL = "SELECT g.owner_projectid as projectid, c.workitemid as workitemid, c.parent_comid"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_communication_tablename." c on g.id=c.workitemid"
                    . " WHERE c.id=$comid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getSprintCommunicationContext($comid)
    {
        try
        {
            $sSQL = "SELECT g.owner_projectid as projectid, c.sprintid as sprintid, c.parent_comid"
                    . " FROM ".DatabaseNamesHelper::$m_sprint_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_sprint_communication_tablename." c on g.id=c.sprintid"
                    . " WHERE c.id=$comid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getTestcaseCommunicationContext($comid)
    {
        try
        {
            //TODO --- include the STEPS
            $sSQL = "SELECT g.owner_projectid as projectid, c.testcaseid as testcaseid, c.parent_comid"
                    . " FROM ".DatabaseNamesHelper::$m_testcase_tablename." g"
                    . " LEFT JOIN ".DatabaseNamesHelper::$m_testcase_communication_tablename." c on g.id=c.testcaseid"
                    . " WHERE c.id=$comid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getWorkitemIDsInProject($projectid)
    {
        try
        {
            $themap = [];
            $sSQL = "SELECT id"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                    . " WHERE w.owner_projectid=$projectid and w.active_yn=1";
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
    
    public function getWorkitemCountInProject($projectid, $active_yn=1)
    {
        try
        {
            $sSQL = "SELECT count(id) as thecount"
                    . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                    . " WHERE w.owner_projectid=$projectid";
            if($active_yn !== NULL)
            {
                $sSQL .= " and w.active_yn=$active_yn";
            }
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['thecount'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getStepCountInTestcase($testcaseid)
    {
        try
        {
            $sSQL = "SELECT count(id) as thecount"
                    . " FROM ".DatabaseNamesHelper::$m_testcasestep_tablename." s"
                    . " WHERE s.testcaseid=$testcaseid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            return $record['thecount'];
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns small set of information for the workitems
     */
    public function getWorkitemInfoForListOfIDs($idlist, $only_active=TRUE)
    {
        try
        {
            if(!is_array($idlist))
            {
                throw new \Exception("Missing required idlist!");
            }
            $themap = array();
            if(count($idlist)>0)
            {
                $sSQL = "SELECT w.id, w.workitem_basetype, "
                        . " w.equipmentid, w.external_resourceid, "
                        . " w.workitem_nm, w.active_yn, w.owner_projectid, w.status_cd "
                        . " FROM ".DatabaseNamesHelper::$m_workitem_tablename." w"
                        . " WHERE w.id IN (" . implode(',', $idlist) . ")"
                        . " ORDER BY w.id";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['id'];
                        $record['typeletter'] = UtilityGeneralFormulas::getTypeLetterFromRecordInfo($record);
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
     * Returns small set of information for the projectids provided
     * OPTIONS...
     * terminal_yn : 1 or 0 or NULL
     */
    public function getSmallProjectInfoForFilter($filter_ar, $only_active=TRUE)
    {
        try
        {
            if(!is_array($filter_ar))
            {
                throw new \Exception("Missing required $filter_ar!");
            }
            $aWHERE = [];
            $sSELECT = "SELECT p.id as projectid"
                    . " FROM ".DatabaseNamesHelper::$m_project_tablename." p";
            if(array_key_exists('terminal_yn', $filter_ar) && $filter_ar['terminal_yn'] !== NULL)
            {
               $aWHERE[] =  "terminal_yn={$filter_ar['terminal_yn']}";
            }
            if($only_active !== NULL)
            {
               $aWHERE[] =  "active_yn=" . ($only_active ? 1 : 0) ;
            }
            if(count($aWHERE)>0)
            {
                $sWHERE = " WHERE " . implode(' and ', $aWHERE);
            } else {
                $sWHERE = "";
            }
            $sSQL = $sSELECT . $sWHERE;
            
            $idlist = [];
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['projectid'];
                $idlist[] = $id;
            }
            return $this->getSmallProjectInfoForListOfIDs($idlist, $only_active);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns small set of information for the projectids provided
     */
    public function getSmallProjectInfoForListOfIDs($idlist, $only_active=TRUE)
    {
        try
        {
            if(!is_array($idlist))
            {
                throw new \Exception("Missing required idlist!");
            }
            $themap = array();
            if(count($idlist)>0)
            {
                $sSQL = "SELECT p.id as projectid, p.root_goalid, "
                        . " w.workitem_nm as name, w.active_yn, "
                        . " COALESCE(w.actual_start_dt, w.planned_start_dt, p.actual_start_dt, p.planned_start_dt) as start_dt,"
                        . " COALESCE(w.actual_end_dt, w.planned_end_dt, p.actual_end_dt, p.planned_end_dt) as end_dt,"
                        . " project_edit_lock_cd, show_cd, "
                        . " w.importance as official_importance,"
                        . " p.owner_personid as primary_owner,"
                        . " w.status_cd, w.status_set_dt, s.title_tx, s.workstarted_yn, s.terminal_yn, s.happy_yn,"
                        . " sum(if(NOT(ISNULL(ms.workstarted_yn)) and ms.workstarted_yn=1,1,0)) as started_workitems,"
                        . " sum(if(NOT(ISNULL(ms.workstarted_yn)) and ms.workstarted_yn=0,1,0)) as notstarted_workitems,"
                        . " sum(if(NOT(ISNULL(ms.happy_yn)) and ms.happy_yn=1,1,0)) as happy_workitems,"
                        . " sum(if(ISNULL(ms.terminal_yn) or ms.terminal_yn=0,1,0)) as open_workitems,"
                        . " sum(if(NOT(ISNULL(ms.terminal_yn)) and ms.terminal_yn=1,1,0)) as closed_workitems"
                        . " FROM ".DatabaseNamesHelper::$m_project_tablename." p"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." w on w.id=p.root_goalid"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." s on s.code=w.status_cd"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_tablename." mw on mw.owner_projectid=p.id"
                        . " LEFT JOIN ".DatabaseNamesHelper::$m_workitem_status_tablename." ms on ms.code=mw.status_cd"
                        . " WHERE p.id IN (" . implode(',', $idlist) . ")"
                        . " GROUP BY p.id, w.id"
                        . " ORDER BY p.id";
                $result = db_query($sSQL);
                while($record = $result->fetchAssoc()) 
                {
                    if(!$only_active || $record['active_yn'] == 1)
                    {
                        $id = $record['projectid'];
                        $themap[$id] = $record;
                    }
                }
            }
            return $themap;
        } catch (\Exception $ex) {
            //throw $ex;
            throw new \Exception("Failed idlist=" . print_r($idlist,TRUE) . " because $ex, 99876, $ex");
        }
    }
}

