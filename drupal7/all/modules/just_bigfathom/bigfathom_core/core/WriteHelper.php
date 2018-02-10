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
require_once 'LinkLogicHelper.php';
require_once 'MapHelper.php';
require_once 'ProjectPlanAutoFill.php';

/**
 * This class helps us write back data
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class WriteHelper
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oLLH = NULL;
    
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oLLH = new \bigfathom\LinkLogicHelper();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdatedForWID($workitemid, $comment_tx=NULL, $projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_oMapHelper->getProjectIDForWorkitem($workitemid);
                if(empty($projectid))
                {
                    drupal_set_message("Workitem#$workitemid was already deleted","warning");
                    return NULL;
                }
            }
            $subjectmap = array('name'=>'workitem','workitemid'=>$workitemid, 'comment_tx'=>$comment_tx);
            return $this->markProjectUpdated($projectid, $comment_tx, $subjectmap);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdatedForSprintID($sprintid, $comment_tx=NULL,$projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_oMapHelper->getProjectIDForSprint($sprintid);
                if(empty($projectid))
                {
                    drupal_set_message("Sprint#$sprintid was already deleted","warning");
                    return NULL;
                }
            }
            $subjectmap = array('name'=>'sprint','sprintid'=>$sprintid, 'comment_tx'=>$comment_tx);
            return $this->markProjectUpdated($projectid, $comment_tx, $subjectmap);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdatedForUsecaseID($usecaseid, $comment_tx=NULL,$projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_oMapHelper->getProjectIDForUsecase($usecaseid);
                if(empty($projectid))
                {
                    drupal_set_message("Usecase#$usecaseid was already deleted","warning");
                    return NULL;
                }
            }
            $subjectmap = array('name'=>'usecase','usecaseid'=>$usecaseid, 'comment_tx'=>$comment_tx);
            return $this->markProjectUpdated($projectid, $comment_tx, $subjectmap);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdatedForTestcaseID($testcaseid, $comment_tx=NULL,$projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_oMapHelper->getProjectIDForTestcase($testcaseid);
                if(empty($projectid))
                {
                    drupal_set_message("Testcase#$testcaseid was already deleted","warning");
                    return NULL;
                }
            }
            $subjectmap = array('name'=>'testcase','testcaseid'=>$testcaseid, 'comment_tx'=>$comment_tx);
            return $this->markProjectUpdated($projectid, $comment_tx, $subjectmap);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdatedForBraintormTopic($brainstormid, $comment_tx=NULL, $projectid=NULL)
    {
        try
        {
            if(empty($brainstormid))
            {
                throw new \Exception("Missing required brainstormid!");
            }
            if(empty($projectid))
            {
                $projectid = $this->m_oMapHelper->getProjectIDForBrainstormTopic($brainstormid);
                if(empty($projectid))
                {
                    drupal_set_message("Topic#$brainstormid was already deleted","warning");
                    return NULL;
                }
            }
            $subjectmap = array('name'=>'brainstorm','brainstormid'=>$brainstormid, 'comment_tx'=>$comment_tx);
            return $this->markProjectUpdated($projectid, $comment_tx, $subjectmap);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent group data changes
     */
    public function markGroupUpdated($groupid, $comment_tx=NULL, $metadata_updated_yn=0, $membership_updated_yn=0)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $sessionid = session_id();
            if(empty($groupid))
            {
                throw new \Exception("Missing groupid!");
            }
            if($comment_tx === NULL)
            {
                $comment_tx = "content updated";
            }

            //Update the group tracking table
            $fields = array(
                  'groupid' => $groupid,
                  'sessionid' => $sessionid,
                  'metadata_updated_yn' => $metadata_updated_yn,
                  'membership_updated_yn' => $membership_updated_yn,
                  'comment_tx' =>$comment_tx,
                  'changed_by_uid' => $this_uid,
                  'updated_dt' => $updated_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_group_recent_data_updates_tablename)
                ->fields($fields);
            $main_qry->execute();
            
            //If we are here then we had success.
            $resultbundle = array('updated_dt'=>$updated_dt);
            return $resultbundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception($ex);
        }
    }

    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectUpdated($projectid, $comment_tx=NULL, $subjectmap=NULL)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $sessionid = session_id();
            if(empty($projectid))
            {
                DebugHelper::showStackTrace();
                throw new \Exception("Missing projectid!");
            }
            if($comment_tx === NULL)
            {
                $comment_tx = "content updated";
            }
            if($subjectmap !== NULL)
            {
                if($subjectmap['name'] == 'workitem')
                {
                    if(empty($subjectmap['workitemid']))
                    {
                        throw new \Exception("Missing workitemid!");
                    }
                    $fields = array(
                          'workitemid' => $subjectmap['workitemid'],
                          'sessionid' => $sessionid,
                          'comment_tx' => !empty($subjectmap['comment_tx']) ? $subjectmap['comment_tx'] : $comment_tx,
                          'changed_by_uid' => $this_uid,
                          'updated_dt' => $updated_dt,
                      );
                    $main_qry = db_insert(DatabaseNamesHelper::$m_workitem_recent_data_updates_tablename)
                        ->fields($fields);
                    $main_qry->execute();
                } else
                if($subjectmap['name'] == 'brainstorm')
                {
                    if(empty($subjectmap['brainstormid']))
                    {
                        throw new \Exception("Missing brainstormid!");
                    }
                    $fields = array(
                          'projectid' => $projectid,
                          'brainstormid' => $subjectmap['brainstormid'],
                          'sessionid' => $sessionid,
                          'comment_tx' => !empty($subjectmap['comment_tx']) ? $subjectmap['comment_tx'] : $comment_tx,
                          'changed_by_uid' => $this_uid,
                          'updated_dt' => $updated_dt,
                      );
                    $main_qry = db_insert(DatabaseNamesHelper::$m_brainstorm_recent_data_updates_tablename)
                        ->fields($fields);
                    $main_qry->execute();
                } else
                if($subjectmap['name'] == 'sprint')
                {
                    if(empty($subjectmap['sprintid']))
                    {
                        throw new \Exception("Missing sprintid!");
                    }
                    $fields = array(
                          'sprintid' => $subjectmap['sprintid'],
                          'sessionid' => $sessionid,
                          'comment_tx' => !empty($subjectmap['comment_tx']) ? $subjectmap['comment_tx'] : $comment_tx,
                          'changed_by_uid' => $this_uid,
                          'updated_dt' => $updated_dt,
                      );
                    $main_qry = db_insert(DatabaseNamesHelper::$m_sprint_recent_data_updates_tablename)
                        ->fields($fields);
                    $main_qry->execute();
                }
            }

            //Update the project tracking table
            $fields = array(
                  'projectid' => $projectid,
                  'sessionid' => $sessionid,
                  'comment_tx' =>$comment_tx,
                  'changed_by_uid' => $this_uid,
                  'updated_dt' => $updated_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_project_recent_data_updates_tablename)
                ->fields($fields);
            $main_qry->execute();
            $projselected_fields = array(
                  'most_recent_edit_dt' =>$updated_dt,
              );
            $projselected_qry = db_update(DatabaseNamesHelper::$m_project_recent_selection_by_user_tablename)
                ->fields($projselected_fields)
                    ->condition('projectid',$projectid)
                    ->condition('sessionid',$sessionid)
                    ->condition('selected_by_uid',$this_uid);
            $projselected_qry->execute();            

            //If we are here then we had success.
            $resultbundle = array('updated_dt'=>$updated_dt);
            return $resultbundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent project template data changes
     */
    public function markTPUpdated($template_projectid, $comment_tx=NULL)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $sessionid = session_id();
            if($template_projectid === NULL)
            {
                throw new \Exception("Missing projectid!");
            }
            if($comment_tx === NULL)
            {
                $comment_tx = "content updated";
            }

            //Update the template project tracking table
            $fields = array(
                  'template_projectid' => $template_projectid,
                  'sessionid' => $sessionid,
                  'comment_tx' =>$comment_tx,
                  'changed_by_uid' => $this_uid,
                  'updated_dt' => $updated_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_template_project_recent_data_updates_tablename)
                ->fields($fields);
            $main_qry->execute();
            
            //If we are here then we had success.
            $resultbundle = array('updated_dt'=>$updated_dt);
            return $resultbundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception($ex);
        }
    }
    
    /**
     * Update the table the tracks most recent project data changes
     */
    public function markProjectSelected($projectid)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $selected_dt = date("Y-m-d H:i", time());
            $sessionid = session_id();
            
            $fields = array(
                  'projectid' => $projectid,
                  'sessionid' => $sessionid,
                  'selected_by_uid' => $this_uid,
                  'selected_dt' => $selected_dt,
                  'most_recent_read_dt' =>$selected_dt,
              );
            $main_qry = db_insert(DatabaseNamesHelper::$m_project_recent_selection_by_user_tablename)
                ->fields($fields);
            $main_qry->execute(); 
            
            //Delete older selections if any for this session
            $delete_qry = db_delete(DatabaseNamesHelper::$m_project_recent_selection_by_user_tablename);
            $delete_qry->condition('projectid',$projectid,'<>')
                    ->condition('sessionid',$sessionid,'=');
            $delete_qry->execute();
            
            //If we are here then we had success.
            $resultbundle = array('updated_dt'=>$selected_dt);
            return $resultbundle;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Call this if you find orphaned/dangling records and want to erase them.
     */
    public function deleteOrphanDanglers()
    {
        $sql_list = [];
        $notes = [];
        $info = [];
        try
        {
            $get_workitem_sql = "SELECT a.id FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " a"
            . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_tablename . " p ON p.id=a.owner_projectid"
            . " WHERE p.id IS NULL";
            $result = db_query($get_workitem_sql);
            $map = $result->fetchAllAssoc('id');
            $wid_list = array_keys($map);
            if(count($wid_list)>0)
            {
                $del_workitem_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename 
                        . " WHERE id IN (" . implode(",", $wid_list) . ")";
                $sql_list[] = $del_workitem_sql;
                $result = db_query($del_workitem_sql);
            }
            $info['deleted_workitems'] = count($wid_list);
            
            $get_wi2wi_sql = "SELECT m.antwiid, m.depwiid, a.id as av, d.id as dv"
                    . " FROM " . \bigfathom\DatabaseNamesHelper::$m_map_wi2wi_tablename . " m"
                    . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " d ON d.id=m.depwiid"
                    . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " a ON a.id=m.antwiid"
                    . " WHERE a.id IS NULL OR d.id IS NULL";
            $result = db_query($get_wi2wi_sql);
            $map = [];
            while($record = $result->fetchAssoc()) 
            {
                $av = $record['av'];
                $dv = $record['dv'];
                $antwiid = $record['antwiid'];
                $depwiid = $record['depwiid'];
                if(empty($dv))
                {
                    $map[$depwiid] = $depwiid;
                }
                if(empty($av))
                {
                    $map[$antwiid] = $antwiid;
                }
            }
            $wi2wi_list = array_keys($map);
            if(count($wi2wi_list)>0)
            {
                $del_workitem_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_map_wi2wi_tablename 
                        . " WHERE depwiid IN (" . implode(",", $wi2wi_list) . ") OR antwiid IN (" . implode(",", $wi2wi_list) . ")";
                $sql_list[] = $del_workitem_sql;
                $result = db_query($del_workitem_sql);
            }
            $notes['$get_wi2wi_sql'] = $get_wi2wi_sql;
            $notes['$wi2wi_list'] = $wi2wi_list;
            $info['deleted_map_wi2wi'] = count($wi2wi_list);
            
            //USE CASE
            $get_usecase_sql = "SELECT a.usecaseid FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2usecase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename . " u ON u.id=a.usecaseid"
                        . " WHERE w.id IS NULL OR u.id IS NULL";
            $result = db_query($get_usecase_sql);
            $map = $result->fetchAllAssoc('usecaseid');
            $wi2uc_list = array_keys($map);
            if(count($wi2uc_list)>0)
            {
                $del_usecase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2usecase_tablename 
                        . " WHERE usecaseid IN (" . implode(",", $wi2uc_list) . ")";
                $sql_list[] = $del_usecase_sql;
                $result = db_query($del_usecase_sql);
                $del_usecase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2usecase_tablename 
                        . " WHERE usecaseid IN (" . implode(",", $wi2uc_list) . ")";
                $sql_list[] = $del_usecase_sql;
                $result = db_query($del_usecase_sql);
            }
            $info['deleted_map_wi2usecase'] = count($wi2uc_list);
            
            $get_usecase_sql = "SELECT a.id FROM " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename . " a"
            . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_tablename . " p ON p.id=a.owner_projectid"
            . " WHERE p.id IS NULL";
            $result = db_query($get_usecase_sql);
            $map = $result->fetchAllAssoc('id');
            $ucid_list = array_keys($map);
            if(count($ucid_list)>0)
            {
                $del_usecase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_usecase_tablename 
                        . " WHERE id IN (" . implode(",", $ucid_list) . ")";
                $sql_list[] = $del_usecase_sql;
                $result = db_query($del_usecase_sql);
            }
            $info['deleted_usecases'] = count($ucid_list);
            
            //TEST CASE
            $get_testcase_sql = "SELECT a.testcaseid FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2testcase_tablename . " a"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_workitem_tablename . " w ON w.id=a.workitemid"
                        . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename . " u ON u.id=a.testcaseid"
                        . " WHERE w.id IS NULL OR u.id IS NULL";
            $result = db_query($get_testcase_sql);
            $map = $result->fetchAllAssoc('testcaseid');
            $tcid_list = array_keys($map);
            if(count($tcid_list)>0)
            {
                $del_testcase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_map_workitem2testcase_tablename 
                        . " WHERE testcaseid IN (" . implode(",", $tcid_list) . ")";
                $sql_list[] = $del_testcase_sql;
                $result = db_query($del_testcase_sql);
                $del_testcase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_map_tag2testcase_tablename 
                        . " WHERE testcaseid IN (" . implode(",", $tcid_list) . ")";
                $sql_list[] = $del_testcase_sql;
                $result = db_query($del_testcase_sql);
            }
            $info['deleted_map_wi2testcase'] = count($tcid_list);
            
            $get_testcase_sql = "SELECT a.id FROM " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename . " a"
            . " LEFT JOIN " . \bigfathom\DatabaseNamesHelper::$m_project_tablename . " p ON p.id=a.owner_projectid"
            . " WHERE p.id IS NULL";
            $result = db_query($get_testcase_sql);
            $map = $result->fetchAllAssoc('id');
            $tcid_list = array_keys($map);
            if(count($tcid_list)>0)
            {
                $del_testcase_sql = "DELETE FROM " . \bigfathom\DatabaseNamesHelper::$m_testcase_tablename 
                        . " WHERE id IN (" . implode(",", $tcid_list) . ")";
                $sql_list[] = $del_testcase_sql;
                $result = db_query($del_testcase_sql);
            }
            $info['deleted_testcases'] = count($tcid_list);

            //Summary
            $total = 0;
            foreach($info as $name=>$thecount)
            {
                if(substr($name,0,1) !== '$')
                {
                    $total += $thecount;
                }
            }
            $info['notes'] = $notes;
            $info['total'] = $total;
            return $info;
        } catch (\Exception $ex) {
            throw new \Exception("Failed because $ex see " . print_r($sql_list,TRUE),99887,$ex);
        }
    }
    
    /**
     * Erase a project and all its content from the system
     */
    public function deleteProject($projectid)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            if($user->uid != 1)
            {
                $errmsg = "User {$user->uid} attempted to delete project#$projectid!";
                error_log("SECURITY WARNING: $errmsg");
                throw new \Exception($errmsg);
            }
            
            $this->deactivateProject($projectid);
            
            //NOW PHYSICALLY REMOVE THE PROJECT RECORDS...
            $owned_wids_ar = $this->m_oMapHelper->getOwnedWorkitemIDs($projectid, NULL);
            
            db_delete(DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_map_workitem2usecase_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_map_workitem2testcase_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_map_tag2workitem_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_map_brainstormid2wid_tablename)
              ->condition('workitemid', $owned_wids_ar, 'IN')
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_workitem_history_tablename)
              ->condition('owner_projectid', $projectid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_brainstorm_item_tablename)
              ->condition('projectid', $projectid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_workitem_tablename)
              ->condition('owner_projectid', $projectid)
              ->execute(); 

            db_delete(DatabaseNamesHelper::$m_sprint_tablename)
              ->condition('owner_projectid', $projectid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_usecase_tablename)
              ->condition('owner_projectid', $projectid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_testcase_tablename)
              ->condition('owner_projectid', $projectid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_project_baseline_tablename)
              ->condition('projectid', $projectid)
              ->execute(); 


            //Now delete the main project record
            db_delete(DatabaseNamesHelper::$m_project_tablename)
              ->condition('id', $projectid)
              ->execute(); 
            
            //Now physically remove the orphans we created by deleting some of the records above
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Deactivate a project
     * TODO FAIL IF PROJECT IS STILL IN USE!!!!!!!!!!!!!!!!!!!
     * TODO move the group and role information offline instead of delete
     */
    public function deactivateProject($projectid)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectDefinition($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid at this time!");   
            }
            
            db_delete(DatabaseNamesHelper::$m_map_group2project_tablename)
              ->condition('projectid', $projectid)
              ->execute(); 

            db_delete(DatabaseNamesHelper::$m_map_prole2project_tablename)
              ->condition('projectid', $projectid)
              ->execute(); 
            
            $proj_fields = array(
                  'active_yn' =>0,
              );
            
            $proj_qry = db_update(DatabaseNamesHelper::$m_project_tablename)
                ->fields($proj_fields)
                    ->condition('id',$projectid);
            $proj_qry->execute();            
            
            
            $this->markProjectUpdated($projectid, "deactivate");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function getAllWidsInsideBranch($rootwid)
    {
        try
        {
            $map = [];
            $map[$rootwid] = $rootwid;
            $sql = "SELECT antwiid,depwiid "
                    . " FROM " . DatabaseNamesHelper::$m_map_wi2wi_tablename 
                    . " WHERE depwiid=$rootwid";
            $result = db_query($sql);
            while($record = $result->fetchAssoc()) 
            {
                $antwiid = $record['antwiid'];
                $submap = $this->getAllWidsInsideBranch($antwiid);
                foreach($submap as $awid)
                {
                    $map[$awid] = $awid;
                }
            }            
            return $map;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function declareProjectAsSubproject($parent_projectid, $ant_projectid)
    {
        $transaction = db_transaction();
        try
        {
            //Add it as a subproject
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $fields = array(
                    'subprojectid' => $ant_projectid,
                    'projectid' => $parent_projectid,
                    'created_by_personid' => $this_uid,
                    'created_dt' => $updated_dt,
                );
            $key_ar = array('subprojectid' => $ant_projectid,'projectid'=>$parent_projectid);
            $main_qry = db_merge(DatabaseNamesHelper::$m_map_subproject2project_tablename)
                  ->key($key_ar)
                  ->fields($fields);
            $main_qry->execute();   //DO NOT USE RETURNED VALUE OF DB MERGE AS THE ID!!!!!
            
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Move a goal branch into a project
     * @param type $matchgoalid the goalid of the branch root
     * @param type $new_owner_projectid the new projectid
     */
    public function changeProjectOfGoalBranch($rootwid, $new_owner_projectid)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            if(empty($rootwid))
            {
                throw new \Exception("Missing required $rootwid!");
            }
            if(empty($new_owner_projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $current_owner_projectid = $this->m_oMapHelper->getProjectIDForWorkitem($rootwid);
            
            //Confirm this is a safe conversion
            $sql_confirm_check = "SELECT count(1) as thecount "
                    . " FROM " . DatabaseNamesHelper::$m_map_subproject2project_tablename 
                    . " WHERE subprojectid=$current_owner_projectid";
            $sql_confirm_check_result = db_query($sql_confirm_check);
            $sql_confirm_check_record = $sql_confirm_check_result->fetchAssoc();
            $depcount = $sql_confirm_check_record['thecount'];
            if($depcount>1)
            {
                throw new \Exception("Attempted to discard projectid#$current_owner_projectid but it has $depcount dependent projects!");
            }
            
            //Change the ownership
            $all_wids_map = $this->getAllWidsInsideBranch($rootwid);
            $IN_TXT = "(".implode(',',$all_wids_map).")";
            $sSQL = "UPDATE " . DatabaseNamesHelper::$m_workitem_tablename 
                    . " SET owner_projectid=$new_owner_projectid"
                    . " WHERE id in $IN_TXT";
            db_query($sSQL);

            //Now create all the history tracking
            $sql_get_workitems = "SELECT id as workitemid, owner_projectid"
                    . ", created_dt, updated_dt, created_by_personid"
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename 
                    . " WHERE id in $IN_TXT";
            $result = db_query($sql_get_workitems);
            while($workitem_fields = $result->fetchAssoc()) 
            {
                $workitemid = $workitem_fields['workitemid'];
                $this->createWorkitemHistoryRecord($workitem_fields, "change to owner projectid#$new_owner_projectid", $workitemid);
            }
            
            //Remove any impacted use-case mappings, if any
            $sSQL = "DELETE FROM " . DatabaseNamesHelper::$m_map_workitem2usecase_tablename 
                    . " WHERE workitemid in $IN_TXT";
            db_query($sSQL);
            
            $changes=count($all_wids_map);
            $this->markProjectUpdated($new_owner_projectid, "moved branch rooted at wid#$rootwid ($changes items) into project#$new_owner_projectid");
            return $changes;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Move a goal branch into a new project
     * @param type $match_twid the goalid of the branch root
     * @param type $new_template_projectid the new projectid
     */
    public function changeProjectTemplateOfWorkitemTemplateBranch($match_twid, $new_template_projectid)
    {
        $transaction = db_transaction();
        try
        {
            //BROKEN????
            $changes=1;
            db_update(DatabaseNamesHelper::$m_template_workitem_tablename)
                    ->fields(array('owner_projectid' => $new_template_projectid))
                    ->condition('id',$match_twid)
                    ->execute();
            $query = db_select(DatabaseNamesHelper::$m_map_wi2wi_tablename,'gm');
            $query->join(DatabaseNamesHelper::$m_template_workitem_tablename, 'sg', 'sg.id = gm.antwiid');
            $query->join(DatabaseNamesHelper::$m_template_workitem_tablename, 'pg', 'pg.id = gm.depwiid');
            $query->fields('gm',array('antwiid'))
                    ->fields('sg',array('owner_projectid'))
                    ->fields('pg',array('owner_projectid'))
                    ->condition('gm.depwiid',$match_twid)
                    ->condition('pg.owner_projectid','sg.owner_projectid');
            $result = $query->execute();
            while($record = $result->fetchAssoc()) 
            {
                $changes += $this->changeProjectTemplateOfWorkitemTemplateBranch($record['antwiid'], $new_template_projectid);
            }
            $this->markProjectUpdated($new_template_projectid, "grabbed workitems from another project template");
            return $changes;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    /**
     * Creates a copy of the workitem WITHOUT the dependencies.
     */
    public function createWorkitemDuplicate($workitemid, $new_projectid=NULL, $new_workitem_nm=NULL)
    {
        try
        {

            $myvalues = $this->m_oMapHelper->getOneRichWorkitemRecord($workitemid);
            if(!empty($new_projectid))
            {
                //Create the copy in a different project
                $myvalues['owner_projectid'] = $new_projectid;
            }
            
            unset($myvalues['id']);
            unset($myvalues['nativeid']);
            unset($myvalues['map_ddw']);
            unset($myvalues['maps']['ddw']);
            unset($myvalues['maps']['daw']);
            if(!empty($new_workitem_nm))
            {
                $myvalues['workitem_nm'] =  $new_workitem_nm;  
            }
            $resultbundle = $this->createWorkitem($myvalues['owner_projectid'], $myvalues);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Creates a copy of the template workitem WITHOUT the dependencies.
     */
    public function createWorkitemFromTW($template_workitemid, $new_projectid=NULL, $new_workitem_nm=NULL, $mark_project_updated=TRUE)
    {
        try
        {
            if($new_projectid === NULL)
            {
                throw new \Exception("Missing required projectid!");
            }

            $myvalues = $this->m_oMapHelper->getOneRichTWRecord($template_workitemid);
            
            unset($myvalues['id']);
            unset($myvalues['nativeid']);
            unset($myvalues['map_ddw']);
            unset($myvalues['maps']['ddw']);
            unset($myvalues['maps']['daw']);
            if(!empty($new_workitem_nm))
            {
                $myvalues['workitem_nm'] =  $new_workitem_nm;  
            }
            $myvalues['owner_projectid'] = $new_projectid;
            $resultbundle = $this->createWorkitem($new_projectid, $myvalues, NULL, NULL, NULL, $mark_project_updated);
            //createWorkitem($projectid, $myvalues, $key_conversion=NULL, $created_by_personid=NULL, $updated_dt=NULL, $mark_project_updated=TRUE)
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Creates a copy of the workitem WITHOUT the dependencies.
     */
    public function createTWFromWorkitem($workitemid, $new_template_projectid=NULL, $new_workitem_nm=NULL)
    {
        try
        {
            $myvalues = $this->m_oMapHelper->getOneRichWorkitemRecord($workitemid);
            if($new_template_projectid !== NULL)
            {
                //Create the copy in a different project
                $myvalues['owner_template_projectid'] = $new_template_projectid;
            }
            
            if(!isset($myvalues['owner_template_projectid']))
            {
                throw new \Exception("Cannot create TW without owner template projectid! myvalues=".print_r($myvalues,TRUE));
            }
            $owner_template_projectid = $myvalues['owner_template_projectid'];
            
            unset($myvalues['id']);
            unset($myvalues['nativeid']);
            unset($myvalues['map_ddw']);
            unset($myvalues['maps']['ddw']);
            unset($myvalues['maps']['daw']);
            if(!empty($new_workitem_nm))
            {
                $myvalues['workitem_nm'] =  $new_workitem_nm;  
            }

            $resultbundle = $this->createWorkitemTemplateRecord($owner_template_projectid, $myvalues);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    
    public function createAntecedentDuplicates($new_root_goalid, $original_root_goalid, $active_yn=1)
    {
        $transaction = db_transaction();
        try
        {
            $old_root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($original_root_goalid);
            $old_owner_projectid = $old_root_goal_info['owner_projectid'];
            $new_root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($new_root_goalid);
            $new_owner_projectid = $new_root_goal_info['owner_projectid'];
            
            $owned_wids = $this->m_oMapHelper->getOwnedWorkitemIDs($old_owner_projectid, $active_yn);
            $non_owned_ant_wids = $this->m_oMapHelper->getSubprojectWorkitemIDs($old_owner_projectid, $active_yn);
            
            $all_new_wid_list = [];
            $newwid2oldwid_map = [];
            $oldwid2newwid_map = [];
            
            $newwid2oldwid_map[$new_root_goalid] = $original_root_goalid;
            $oldwid2newwid_map[$original_root_goalid] = $new_root_goalid;
            $all_new_wid_list[] = $new_root_goalid;
            $all_old_wid_list[] = $original_root_goalid;
            foreach($owned_wids as $wid)
            {
                if($wid != $original_root_goalid)
                {
                    //Create a copy of this workitem
                    $cwbundle = $this->createWorkitemDuplicate($wid, $new_owner_projectid);
                    $new_wid = $cwbundle['workitemid'];
                    $newwid2oldwid_map[$new_wid] = $wid;
                    $oldwid2newwid_map[$wid] = $new_wid;
                    $all_new_wid_list[] = $new_wid;
                    $all_old_wid_list[] = $wid;
                }
            }
            foreach($non_owned_ant_wids as $wid)
            {
                if($wid != $original_root_goalid)
                {
                    //Create a copy of this workitem
                    $cwbundle = $this->createWorkitemDuplicate($wid);
                    $new_wid = $cwbundle['workitemid'];
                    $newwid2oldwid_map[$new_wid] = $wid;
                    $oldwid2newwid_map[$wid] = $new_wid;
                    $all_new_wid_list[] = $new_wid;
                    $all_old_wid_list[] = $wid;
                }
            }

            //Now use the maps to update all the links!
            $existing_wi2wi = $this->m_oMapHelper->getWorkitemAntRecordsHavingID($all_old_wid_list, $active_yn);
            //Warning the set WILL contain some IRRELEVANT links.  Filter them in the loop!
            foreach($existing_wi2wi as $existing_record)
            {
                $existing_depwiid = $existing_record['depwiid'];
                $existing_antwiid = $existing_record['antwiid'];
                
                if(array_key_exists($existing_depwiid, $oldwid2newwid_map) && array_key_exists($existing_antwiid, $oldwid2newwid_map))
                {
                    //This is a relevant link to copy into the copy
                    $new_depwiid = $oldwid2newwid_map[$existing_depwiid];
                    $new_antwiid = $oldwid2newwid_map[$existing_antwiid];

                    $importance = $existing_record['importance'];
                    $ot_scf = $existing_record['ot_scf'];
                    $created_by_personid = $existing_record['created_by_personid'];
                    $updated_dt = $existing_record['created_dt'];
                    db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->fields(array(
                            'depwiid' => $new_depwiid,
                            'antwiid' => $new_antwiid,
                            'importance' => $importance,
                            'ot_scf' => $ot_scf,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))->execute(); 
                }
            }
            
            $result_bundle = array(
                'all_new_wid_list' => $all_new_wid_list,
                'all_old_wid_list' => $all_old_wid_list,
                'newwid2oldwid_map' => $newwid2oldwid_map,
                'oldwid2newwid_map' => $oldwid2newwid_map,
            );
            
            return $result_bundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function createAntecedentWorkitemsFromTemplate($new_root_goalid, $original_root_twid, $active_yn=1)
    {
        $transaction = db_transaction();
        try
        {
            $old_root_template_workitem_info = $this->m_oMapHelper->getOneBareTWRecord($original_root_twid);
            $old_owner_template_projectid = $old_root_template_workitem_info['owner_template_projectid'];
            $new_root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($new_root_goalid);
            $new_owner_projectid = $new_root_goal_info['owner_projectid'];
            
            $owned_twids = $this->m_oMapHelper->getOwnedTWIDs($old_owner_template_projectid);
            $non_owned_ant_wids = [];// $this->m_oMapHelper->getSubprojectWorkitemIDs($old_owner_template_projectid, $active_yn);
            
            $all_new_wid_list = [];
            $new_wid2twid_map = [];
            $old_twid2newwid_map = [];
            
            $new_wid2twid_map[$new_root_goalid] = $original_root_twid;
            $old_twid2newwid_map[$original_root_twid] = $new_root_goalid;
            $all_new_wid_list[] = $new_root_goalid;
            $all_old_twid_list[] = $original_root_twid;
            foreach($owned_twids as $twid)
            {
                if($twid != $original_root_twid)
                {
                    //Create a copy of this workitem
                    $cwbundle = $this->createWorkitemFromTW($twid, $new_owner_projectid);
                    $new_wid = $cwbundle['workitemid'];
                    $new_wid2twid_map[$new_wid] = $twid;
                    $old_twid2newwid_map[$twid] = $new_wid;
                    $all_new_wid_list[] = $new_wid;
                    $all_old_twid_list[] = $twid;
                }
            }
            foreach($non_owned_ant_wids as $twid)
            {
                if($twid != $original_root_twid)
                {
                    //Create a copy of this workitem
                    $cwbundle = $this->createWorkitemDuplicate($twid);
                    $new_wid = $cwbundle['workitemid'];
                    $new_wid2twid_map[$new_wid] = $twid;
                    $old_twid2newwid_map[$twid] = $new_wid;
                    $all_new_wid_list[] = $new_wid;
                    $all_old_twid_list[] = $twid;
                }
            }

            //Now use the maps to update all the links!
            $existing_tw2tw = $this->m_oMapHelper->getTWAntRecordsHavingID($all_old_twid_list);
            //Warning the set WILL contain some IRRELEVANT links.  Filter them in the loop!
            foreach($existing_tw2tw as $existing_record)
            {
                $existing_dep_twid = $existing_record['depwiid'];
                $existing_ant_twid = $existing_record['antwiid'];
                
                if(array_key_exists($existing_dep_twid, $old_twid2newwid_map) && array_key_exists($existing_ant_twid, $old_twid2newwid_map))
                {
                    //This is a relevant link to copy into the copy
                    $new_depwiid = $old_twid2newwid_map[$existing_dep_twid];
                    $new_antwiid = $old_twid2newwid_map[$existing_ant_twid];

                    $importance = $existing_record['importance'];
                    $ot_scf = $existing_record['ot_scf'];
                    $created_by_personid = $existing_record['created_by_personid'];
                    $updated_dt = $existing_record['created_dt'];
                    db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->fields(array(
                            'depwiid' => $new_depwiid,
                            'antwiid' => $new_antwiid,
                            'importance' => $importance,
                            'ot_scf' => $ot_scf,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))->execute(); 
                }
            }
            
            $result_bundle = array(
                'all_new_wid_list' => $all_new_wid_list,
                'all_old_wid_list' => $all_old_twid_list,
                'newwid2oldwid_map' => $new_wid2twid_map,
                'oldwid2newwid_map' => $old_twid2newwid_map,
            );
            
            return $result_bundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function createTWAntecedentDuplicates($new_root_template_workitemid, $original_root_goalid, $active_yn=1)
    {
        $transaction = db_transaction();
        try
        {
            $old_root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($original_root_goalid);
            $old_owner_projectid = $old_root_goal_info['owner_projectid'];
            $new_root_tw_info = $this->m_oMapHelper->getOneBareTWRecord($new_root_template_workitemid);
            $new_owner_tpid = $new_root_tw_info['owner_template_projectid'];

            $owned_wids = $this->m_oMapHelper->getOwnedWorkitemIDs($old_owner_projectid, $active_yn);
            
            $all_new_wid_list = [];
            $newwid2oldwid_map = [];
            $oldwid2newwid_map = [];
            
            $newwid2oldwid_map[$new_root_template_workitemid] = $original_root_goalid;
            $oldwid2newwid_map[$original_root_goalid] = $new_root_template_workitemid;
            $all_new_wid_list[] = $new_root_template_workitemid;
            $all_old_wid_list[] = $original_root_goalid;
            foreach($owned_wids as $wid)
            {
                if($wid != $original_root_goalid)
                {
                    //Create a copy of this workitem
                    $cwbundle = $this->createTWFromWorkitem($wid, $new_owner_tpid);
                    $new_twid = $cwbundle['template_workitemid'];
                    $newwid2oldwid_map[$new_twid] = $wid;
                    $oldwid2newwid_map[$wid] = $new_twid;
                    $all_new_wid_list[] = $new_twid;
                    $all_old_wid_list[] = $wid;
                }
            }

            //Now use the maps to update all the links!
            $existing_wi2wi = $this->m_oMapHelper->getWorkitemAntRecordsHavingID($all_old_wid_list, $active_yn);
            //Warning the set WILL contain some IRRELEVANT links.  Filter them in the loop!
            foreach($existing_wi2wi as $existing_record)
            {
                $existing_depwiid = $existing_record['depwiid'];
                $existing_antwiid = $existing_record['antwiid'];
                
                if(array_key_exists($existing_depwiid, $oldwid2newwid_map) && array_key_exists($existing_antwiid, $oldwid2newwid_map))
                {
                    //This is a relevant link to copy into the copy
                    $new_depwiid = $oldwid2newwid_map[$existing_depwiid];
                    $new_antwiid = $oldwid2newwid_map[$existing_antwiid];

                    $importance = $existing_record['importance'];
                    $ot_scf = $existing_record['ot_scf'];
                    $created_by_personid = $existing_record['created_by_personid'];
                    $updated_dt = $existing_record['created_dt'];
                    db_insert(DatabaseNamesHelper::$m_map_tw2tw_tablename)
                      ->fields(array(
                            'depwiid' => $new_depwiid,
                            'antwiid' => $new_antwiid,
                            'importance' => $importance,
                            'ot_scf' => $ot_scf,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))->execute(); 
                }
            }
            
            $result_bundle = array(
                'all_new_wid_list' => $all_new_wid_list,
                'all_old_wid_list' => $all_old_wid_list,
                'newwid2oldwid_map' => $newwid2oldwid_map,
                'oldwid2newwid_map' => $oldwid2newwid_map,
            );
            
            return $result_bundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Create a copy of the project
     */
    public function duplicateProject($source_projectid, $newvalues)
    {
        try
        {
            $transaction = db_transaction();
            $result_bundle=[];
            if(empty($source_projectid))
            {
                throw new \Exception("Missing required source projectid!");
            }
            $source_project_info = $this->m_oMapHelper->getOneProjectDetailData($source_projectid);
            
            $original_root_goalid = $source_project_info['root_goalid'];
            $surrogate_yn = $source_project_info['surrogate_yn'];
            
            if(!is_array($newvalues))
            {
                $newvalues = [];
            }
            
            if(empty($newvalues['surrogate_yn']))
            {
                $newvalues['surrogate_yn'] = $surrogate_yn;
            }
            
            if(empty($newvalues['root_workitem_nm']))
            {
                $new_workitem_nm = $this->m_oMapHelper->getNewCopyProjectRootName($source_projectid);
            } else {
                $new_workitem_nm = $newvalues['root_workitem_nm'];
            }
            
            $proj_resultbundle = $this->createNewProject($newvalues, $original_root_goalid);
            $new_projectid = $proj_resultbundle['newid'];
            $new_root_goalid = $proj_resultbundle['root_goalid'];
            $wibranch_resultbundle = $this->createAntecedentDuplicates($new_root_goalid, $original_root_goalid);
     
            $result_bundle['root_workitem_nm'] = $new_workitem_nm;
            $result_bundle['root_goalid'] = $new_root_goalid;
            $result_bundle['projectid'] = $new_projectid;
            
            $this->markProjectUpdated($new_projectid, "create new project#{$new_projectid} as copy of project#" . $source_projectid);
            return $result_bundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception("Failed duplicateProject($source_projectid) because $ex",99876,$ex);
        }
    }

    /**
     * Create a project template
     */
    public function createTemplateFromImport($importedvalues, $myvalues_overrides=NULL)
    {
        try
        {
            if(empty($importedvalues))
            {
                throw new \Exception("Missing required importedvalues!");
            }
            if(empty($myvalues_overrides))
            {
                $myvalues_overrides = [];
            }
            if(empty($myvalues_overrides['owner_personid']))
            {
                global $user;
                $this_uid = $user->uid;
                $myvalues_overrides['owner_personid'] = $this_uid;
                //throw new \Exception("Missing required owner_personid!");
            }
            
            $myvalues_overrides['metadata'] = $importedvalues['metadata'];
            $myvalues_overrides['workitems'] = $importedvalues['workitems'];
            
            $myvalues_overrides['workitems']['labels'][] = 'maps';
            $maps_offset = count($myvalues_overrides['workitems']['labels'])-1;
            
            $workitem_submaps = $importedvalues['workitem_submaps'];
            $fastmap_wid2rowoffset = $importedvalues['workitems']['fastmap_wid2rowoffset'];
            foreach($workitem_submaps as $onesubmapname=>$mapdetail)
            {
                if(strpos($onesubmapname,':DAW') !== FALSE)
                {
                    foreach($mapdetail['rows'] as $pair)
                    {
                        $ddw = $pair[0];
                        $daw = $pair[1];
                        $daw_offset = $fastmap_wid2rowoffset[$daw];
                        $ddw_offset = $fastmap_wid2rowoffset[$ddw];
                        $myvalues_overrides['workitems']['rows'][$ddw_offset][$maps_offset]['daw'][] = $daw;
                        $myvalues_overrides['workitems']['rows'][$daw_offset][$maps_offset]['ddw'][] = $ddw;
                    }
                }
            }
            $result_bundle = $this->createNewTemplate($myvalues_overrides);
            $newid = $result_bundle['newid'];
            $this->markTPUpdated($newid, "create new template#{$newid} from imported content");
            return $result_bundle;
        } catch (\Exception $ex) {
            throw new \Exception("Failed createTemplateFromImport because $ex",99876,$ex);
        }
    }

    /**
     * Create a project template
     */
    public function createTemplateFromProject($source_projectid, $newvalues)
    {
        $transaction = db_transaction();
        try
        {
            $result_bundle=[];
            if(empty($source_projectid))
            {
                throw new \Exception("Missing required source projectid!");
            }
            $source_project_info = $this->m_oMapHelper->getOneProjectDetailData($source_projectid);
            
            $original_root_goalid = $source_project_info['root_goalid'];
            $surrogate_yn = $source_project_info['surrogate_yn'];
            
            if(!is_array($newvalues))
            {
                $newvalues = [];
            }
            
            if(empty($newvalues['template_nm']))
            {
                $new_template_nm = $this->m_oMapHelper->getNewTemplateProjectName($source_projectid);
            } else {
                $new_template_nm = $newvalues['template_nm'];
            }
            
            $proj_resultbundle = $this->createNewTPFromProject($newvalues, $original_root_goalid);
            $new_template_projectid = $proj_resultbundle['newid'];
            $new_root_template_workitemid = $proj_resultbundle['root_template_workitemid'];

            $this->createTWAntecedentDuplicates($new_root_template_workitemid, $original_root_goalid);
     
            $result_bundle['root_workitem_nm'] = $newvalues['root_workitem_nm'];
            $result_bundle['root_template_workitemid'] = $new_root_template_workitemid;
            $result_bundle['template_projectid'] = $new_template_projectid;
            $result_bundle['source_projectid'] = $source_projectid;
            $result_bundle['template_nm'] = $new_template_nm;
            
            $this->markTPUpdated($new_template_projectid, "create new template#{$new_template_projectid} as copy of project#" . $source_projectid);
            return $result_bundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception("Failed createTemplateFromProject because $ex",99876,$ex);
        }
    }

    /**
     * Create a project from a template
     */
    public function createProjectFromTemplate($source_templateid, $newvalues)
    {
        $transaction = db_transaction();
        try
        {
            $result_bundle=[];
            if(empty($source_templateid))
            {
                throw new \Exception("Missing required source templateid!");
            }
            $source_template_info = $this->m_oMapHelper->getOneTPDetailData($source_templateid);
            $original_root_template_workitemid = $source_template_info['root_template_workitemid'];
            if(!is_array($newvalues))
            {
                $newvalues = [];
            }
            $newvalues['project_contextid'] = $source_template_info['project_contextid'];
            $newvalues['template_yn'] = 0;
            $newvalues['surrogate_yn'] = 0;
            $newvalues['original_source_templateid'] = $source_templateid;
            $newvalues['original_source_template_refname'] = $source_template_info['publishedrefname'];
            $newvalues['original_source_template_updated_dt'] = $source_template_info['updated_dt'];
            
            if(empty($newvalues['root_workitem_nm']))
            {
                $new_workitem_nm = $this->m_oMapHelper->getNewCopyProjectRootName($source_templateid);
            } else {
                $new_workitem_nm = $newvalues['root_workitem_nm'];
            }
            
            $proj_resultbundle = $this->createNewProjectFromTemplate($newvalues, $original_root_template_workitemid);
            $new_projectid = $proj_resultbundle['newid'];
            $new_root_goalid = $proj_resultbundle['root_goalid'];
            $this->createAntecedentWorkitemsFromTemplate($new_root_goalid, $original_root_template_workitemid);
     
            $result_bundle['root_workitem_nm'] = $new_workitem_nm;
            $result_bundle['root_goalid'] = $new_root_goalid;
            $result_bundle['projectid'] = $new_projectid;
            
            $this->markProjectUpdated($new_projectid, "create new project#{$new_projectid} from template#" . $source_templateid);
            return $result_bundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw new \Exception("Failed createProjectFromTemplate($source_templateid) because $ex",99876,$ex);
        }
    }
    
    /**
     * Create a project 
     */
    public function createProjectFromGoal($root_goalid, $new_myvalues)
    {
        $transaction = db_transaction();
        $new_projectid = NULL;
        $step=0;
        $fields = [];
        try
        {
            $result_bundle=[];
            if(empty($root_goalid))
            {
                throw new \Exception("Missing required source root_goalid!");
            }
            
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $step++;
            
            $current_owner_projectid = $this->m_oMapHelper->getProjectIDForWorkitem($root_goalid);
            $step++;

            //Remove any impacted use-case mappings, if any
            $all_wids_map = $this->getAllWidsInsideBranch($root_goalid);
            $all_wids_map[$root_goalid] = $root_goalid;
            $IN_TXT = "(".implode(',',$all_wids_map).")";
            $sSQL = "DELETE FROM " . DatabaseNamesHelper::$m_map_workitem2usecase_tablename 
                    . " WHERE workitemid in $IN_TXT";
            db_query($sSQL);
            
            $step++;
            $fields = array(
                  'owner_personid' => $new_myvalues['owner_personid'],
                  'root_goalid' => $root_goalid,
                  'project_contextid' => $new_myvalues['project_contextid'],
                  'active_yn' => $new_myvalues['active_yn'],
                  'mission_tx' => $new_myvalues['mission_tx'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            $key_ar = array('root_goalid' => $root_goalid);
            $main_qry = db_merge(DatabaseNamesHelper::$m_project_tablename)
                  ->key($key_ar)
                  ->fields($fields);
            $main_qry->execute();   //DO NOT USE RETURNED VALUE OF DB MERGE AS THE ID!!!!!
            $new_project_rec = db_select(DatabaseNamesHelper::$m_project_tablename, 'n')
              ->fields('n')
              ->condition('root_goalid', $root_goalid, '=')
              ->execute()
              ->fetchAssoc();
            $new_projectid = $new_project_rec['id'];
            $step++;
            $this->changeProjectOfGoalBranch($root_goalid, $new_projectid);
            
            //Add group mappings next
            $step++;
            db_delete(DatabaseNamesHelper::$m_map_group2project_tablename)
              ->condition('projectid', $new_projectid)
              ->execute(); 
            $group_count = 0;
            if(is_array($new_myvalues['map_group2project']))
            {
                foreach($new_myvalues['map_group2project'] as $groupid)
                {
                    if($groupid > 0)
                    {
                        $group_count++;
                        $fields = array(
                                'groupid' => $groupid,
                                'projectid' => $new_projectid,
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt,
                            );
                        db_insert(DatabaseNamesHelper::$m_map_group2project_tablename)
                          ->fields($fields)
                              ->execute(); 
                    }
                }
            }

            //Add role mappings next
            $step++;
            db_delete(DatabaseNamesHelper::$m_map_prole2project_tablename)
              ->condition('projectid', $new_projectid)
              ->execute(); 
            $role_count = 0;
            if(is_array($new_myvalues['map_role2project']))
            {
                foreach($new_myvalues['map_role2project'] as $roleid)
                {
                    if($roleid > 0)
                    {
                        $role_count++;
                        $fields = array(
                                'roleid' => $roleid,
                                'projectid' => $new_projectid,
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt,
                            );
                        db_insert(DatabaseNamesHelper::$m_map_prole2project_tablename)
                          ->fields($fields)
                              ->execute(); 
                    }
                }
            }
            
            $result_bundle['root_goalid'] = $root_goalid;
            $result_bundle['projectid'] = $new_projectid;
            $this->markProjectUpdated($current_owner_projectid, "moved wid#{$root_goalid} to root of new proj#{$new_projectid}");
            $this->markProjectUpdated($new_projectid, "created project by moving wid#{$root_goalid} from of proj#{$current_owner_projectid}");
            
            return $result_bundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            throw new \Exception("Failed createProjectFromGoal($root_goalid) because $ex",99876,$ex);
        }
    }

    /**
     * Return TRUE if the checknode is part of a chain connected to the root of the tree.
     */
    public function isNodeConnectedToRootNode($onelink, &$connected_nodes)
    {
        try
        {
            $target_node = $onelink['target'];
            $tkey = $target_node['key'];
            
            $source_node = $onelink['source'];
            $skey = $source_node['key'];
            
            if(array_key_exists($tkey, $connected_nodes))
            {
                $connected_nodes[$skey] = $source_node;
                return TRUE;
            }
            return FALSE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function lockProjectForUpdates($projectid)
    {
        try
        {
            //TODO edit_lock_cd of project table
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function unlockProject($projectid,$error=NULL)
    {
        try
        {
            //TODO edit_lock_cdd of project table
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function parseJSONValueAsBoolean($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return $bool;
    }
    
    public function parseJSONValueAs01($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return $bool ? 1 : 0;
    }
    
    /**
     * Update one committed workitem record from a databundle
     */
    public function updateOneWorkitemData($databundle)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i:s", time());
            
            $workitemid = $databundle['workitemid'];
            if(empty($workitemid))
            {
                throw new \Exception("Missing workitemid in databundle=" . print_r($databundle,TRUE));
            }
            $updatefields = $databundle['updatefields'];
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            $cleanupdatefields = [];
            $history_fields = [];
            foreach($updatefields as $name=>$value)
            {
                if($name !== 'maps')
                {
                    if(!empty($value))
                    {
                        $cleanupdatefields[$name] = $value;  
                        if($name == 'updated_dt')
                        {
                            $history_fields['original_updated_dt'] = $value;  
                        } else
                        if($name == 'created_dt')
                        {
                            $history_fields['original_created_dt'] = $value;  
                        } else {
                            $history_fields[$name] = $value;  
                        }
                    } else {
                        $cleanupdatefields[$name] = NULL;
                        $history_fields[$name] = NULL;  
                    }
                } else {
                    //We have mappings to update too, do that now
                    $themaps = [];
                    $themaps['maps'] = $value;
                    $this->updateRelationshipMaps($workitemid, $themaps, NULL, $this_uid, $updated_dt);
                }
            }
            $cleanupdatefields['updated_dt'] = $updated_dt;
            db_update(DatabaseNamesHelper::$m_workitem_tablename)
                ->fields($cleanupdatefields)
                    ->condition('id', $workitemid)
                    ->execute();
            if(count($history_fields) > 0)
            {
                $history_fields['workitemid'] = $workitemid;  
                //NOT IN THE 20170915 SCHEMA YET $history_fields['created_by_personid'] = $this_uid;  
                $history_fields['original_updated_dt'] = $updated_dt;  
                $history_fields['original_created_dt'] = $updated_dt;  
                $history_fields['history_created_dt'] = $updated_dt;  
                $history_qry = db_insert(DatabaseNamesHelper::$m_workitem_history_tablename)
                    ->fields($history_fields);
                $historyid = $history_qry->execute();
            }
            
            $this->markProjectUpdatedForWID($workitemid, "updated workitem");
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "workitemid"=>$workitemid);
            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function addWorkitemRoleMember($workitemid, $roleid)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('workitemid' => $workitemid,
                        'roleid' => $roleid
                    );
            $fields_ar = array(
                        'roleid' => $roleid,
                        'workitemid' => $workitemid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "increased workitem roles");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removeWorkitemRoleMember($workitemid, $roleid)
    {
        try
        {
            global $user;
            db_delete(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                  ->condition('workitemid', $workitemid)
                  ->condition('roleid',$roleid)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "reduced role membership");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function addPersonInfluence2Workitem($workitemid, $personid, $created_by_personid, $influence_score)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Missing required workitemid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(empty($created_by_personid))
            {
                throw new \Exception("Missing required created_by_personid!");
            }
            if(empty($influence_score))
            {
                $influence_score = 0;
            }
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('workitemid' => $workitemid,
                        'personid' => $personid,
                        'created_by_personid' => $created_by_personid
                    );
            $fields_ar = array(
                        'workitemid' => $workitemid,
                        'influence' => $influence_score,
                        'personid' => $personid,
                        'created_by_personid' => $created_by_personid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_general_person_influence2wi_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "added influence declaration");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removePersonInfluence2Workitem($workitemid, $personid, $created_by_personid)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Missing required workitemid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(empty($created_by_personid))
            {
                throw new \Exception("Missing required created_by_personid!");
            }
            db_delete(DatabaseNamesHelper::$m_general_person_influence2wi_tablename)
                  ->condition('workitemid', $workitemid)
                  ->condition('personid',$personid)
                  ->condition('created_by_personid',$created_by_personid)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "removed influence declaration");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function addPersonImportance2Workitem($workitemid, $personid, $created_by_personid, $importance_score)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Missing required workitemid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(empty($created_by_personid))
            {
                throw new \Exception("Missing required created_by_personid!");
            }
            if(empty($importance_score))
            {
                $importance_score = 0;
            }
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('workitemid' => $workitemid,
                        'personid' => $personid,
                        'created_by_personid' => $created_by_personid
                    );
            $fields_ar = array(
                        'workitemid' => $workitemid,
                        'importance' => $importance_score,
                        'personid' => $personid,
                        'created_by_personid' => $created_by_personid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_general_person_importance2wi_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "added importance declaration");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removePersonImportance2Workitem($workitemid, $personid, $created_by_personid)
    {
        try
        {
            if(empty($workitemid))
            {
                throw new \Exception("Missing required workitemid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(empty($created_by_personid))
            {
                throw new \Exception("Missing required created_by_personid!");
            }
            db_delete(DatabaseNamesHelper::$m_general_person_importance2wi_tablename)
                  ->condition('workitemid', $workitemid)
                  ->condition('personid',$personid)
                  ->condition('created_by_personid',$created_by_personid)
                  ->execute();
            $this->markProjectUpdatedForWID($workitemid, "removed importance declaration");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function addVisionStatementAssignment($projectid, $visionstatementid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($visionstatementid))
            {
                throw new \Exception("Missing required visionstatementid!");
            }
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('projectid' => $projectid,
                        'visionstatementid' => $visionstatementid
                    );
            $fields_ar = array(
                        'visionstatementid' => $visionstatementid,
                        'projectid' => $projectid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_map_visionstatement2project_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdated($projectid, "added vision statement assignment");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removeVisionStatementAssignment($projectid, $visionstatementid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($visionstatementid))
            {
                throw new \Exception("Missing required visionstatementid!");
            }
            db_delete(DatabaseNamesHelper::$m_map_visionstatement2project_tablename)
                  ->condition('projectid', $projectid)
                  ->condition('visionstatementid',$visionstatementid)
                  ->execute();
            $this->markProjectUpdated($projectid, "removed vision statement assignment");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function addPCGMember($projectid, $personid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('projectid' => $projectid,
                        'personid' => $personid
                    );
            $fields_ar = array(
                        'personid' => $personid,
                        'projectid' => $projectid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_map_person2pcg_in_project_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdated($projectid, "increased  privileged collaborator group membership");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removePCGMember($projectid, $personid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            db_delete(DatabaseNamesHelper::$m_map_person2pcg_in_project_tablename)
                  ->condition('projectid', $projectid)
                  ->condition('personid',$personid)
                  ->execute();
            $this->markProjectUpdated($projectid, "reduced privileged collaborator group membership");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function addGroupMember($groupid, $personid)
    {
        try
        {
            if(empty($groupid))
            {
                throw new \Exception("Missing required groupid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            
            global $user;
            $this_uid = $user->uid;
            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $uah = new \bigfathom\UserAccountHelper();
            $roleinfo = $uah->getAllRolesBundle($personid); 
            $projectroles = $roleinfo['projectroles'];
            if(empty($projectroles))
            {
                throw new \Exception("There are no defined roles for person#$personid");
            }
            $the_roleids = array_keys($projectroles['detail']);
            foreach($the_roleids as $roleid)
            {

                $created_dt = date("Y-m-d H:i", time());
                $key_ar = array('groupid' => $groupid,
                            'personid' => $personid,
                            'roleid' => $roleid
                        );
                $fields_updated_ar = array(
                            'groupid' => $groupid,
                            'personid' => $personid,
                            'roleid' => $roleid,
                            'created_by_personid' => $this_uid,
                            'updated_dt' => $created_dt,
                      );
                $fields_insert_ar = $fields_updated_ar;
                $fields_insert_ar['created_dt'] = $created_dt;

                db_merge(DatabaseNamesHelper::$m_map_person2role_in_group_tablename)
                      ->insertFields($fields_insert_ar)
                      ->updateFields($fields_insert_ar)
                      ->key($key_ar)
                      ->execute();
                
            }
            $rolestxt = empty($projectroles) ? 'nothing' : '[' . substr(implode(',',$the_roleids),0,90) . ']';
            
            $metadata_updated_yn = 0;
            $membership_updated_yn = 1;
            $this->markGroupUpdated($groupid, "increased group membership:added person#$personid ROLES=".$rolestxt, $metadata_updated_yn, $membership_updated_yn);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removeGroupMember($groupid, $personid)
    {
        try
        {
            if(empty($groupid))
            {
                throw new \Exception("Missing required groupid!");
            }
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            db_delete(DatabaseNamesHelper::$m_map_person2role_in_group_tablename)
                  ->condition('groupid', $groupid)
                  ->condition('personid',$personid)
                  ->execute();
            $metadata_updated_yn = 0;
            $membership_updated_yn = 1;
            $this->markGroupUpdated($groupid, "reduced group membership:removed person#$personid", $metadata_updated_yn, $membership_updated_yn);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function addProjectGroupMember($projectid, $groupid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($groupid))
            {
                throw new \Exception("Missing required groupid!");
            }
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('projectid' => $projectid,
                        'groupid' => $groupid
                    );
            $fields_ar = array(
                        'groupid' => $groupid,
                        'projectid' => $projectid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $created_dt,
                  );
            db_merge(DatabaseNamesHelper::$m_map_group2project_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdated($projectid, "increased project group membership (added group#$groupid to project#$projectid)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removeProjectGroupMember($projectid, $groupid)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($groupid))
            {
                throw new \Exception("Missing required groupid!");
            }
            db_delete(DatabaseNamesHelper::$m_map_group2project_tablename)
                  ->condition('projectid', $projectid)
                  ->condition('groupid',$groupid)
                  ->execute();
            $this->markProjectUpdated($projectid, "reduced project group membership (removed group#$groupid from project#$projectid)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function addSprintWorkitemMember($sprintid, $workitemid, $ot_scf=.95)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('workitemid' => $workitemid,
                        'sprintid' => $sprintid
                    );
            $fields_ar = array(
                        'created_by_personid' => $this_uid,
                        'ot_scf' => $ot_scf,
                        'created_dt' => $created_dt,
                        'workitemid' => $workitemid,
                        'sprintid' => $sprintid
                  );
            db_merge(DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdatedForSprintID($sprintid, "increased sprint membership with #$workitemid");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Remove one member
     */
    public function removeSprintWorkitemMember($sprintid, $workitemid)
    {
        try
        {
            db_delete(DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
                  ->condition('workitemid', $workitemid)
                  ->condition('sprintid',$sprintid)
                  ->execute();
            $this->markProjectUpdatedForSprintID($sprintid, "reduced sprint membership (removed workitem#$workitemid)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update one committed sprint record from a databundle
     */
    public function updateOneSprintMemberData($databundle, $default_dc=0.95)
    {
        try
        {
            
            $sprintid = $databundle['sprintid'];
            if(empty($sprintid))
            {
                throw new \Exception("Missing sprintid in databundle=" . print_r($databundle,TRUE));
            }
            $workitemid = $databundle['workitemid'];
            if(empty($workitemid))
            {
                throw new \Exception("Missing workitemid in databundle=" . print_r($databundle,TRUE));
            }
            if(!array_key_exists('is_member_yn',$databundle))
            {
                throw new \Exception("Missing is_member_yn in databundle=" . print_r($databundle,TRUE));
            }
            $is_member_yn = $databundle['is_member_yn'];
            if(!array_key_exists('ot_scf',$databundle))
            {
                throw new \Exception("Missing ot_scf in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if($is_member_yn === 1)
            {
                $ot_scf = $databundle['ot_scf'];
                if(empty($ot_scf))
                {
                    $ot_scf = $default_dc;
                }
                $this->addSprintWorkitemMember($sprintid, $workitemid, $ot_scf);
            } else {
                $this->removeSprintWorkitemMember($sprintid, $workitemid);
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "sprintid"=>$sprintid
                    , "workitemid"=>$workitemid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function addSubprojectMember($projectid, $subprojectid, $ot_scf=.95)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $created_dt = date("Y-m-d H:i", time());
            $key_ar = array('subprojectid' => $subprojectid,
                        'projectid' => $projectid
                    );
            $fields_ar = array(
                        'created_by_personid' => $this_uid,
                        'ot_scf' => $ot_scf,
                        'created_dt' => $created_dt,
                        'subprojectid' => $subprojectid,
                        'projectid' => $projectid
                  );
            db_merge(DatabaseNamesHelper::$m_map_subproject2project_tablename)
                  ->key($key_ar)
                  ->fields($fields_ar)
                  ->execute();
            $this->markProjectUpdated($projectid, "increased subproject membership (added #$subprojectid to #$projectid)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function removeSubprojectMember($projectid, $subprojectid)
    {
        try
        {
            global $user;
            db_delete(DatabaseNamesHelper::$m_map_subproject2project_tablename)
                  ->condition('subprojectid', $subprojectid)
                  ->condition('projectid',$projectid)
                  ->execute();
            $this->markProjectUpdated($projectid, "reduced subproject membership (removed #$subprojectid from #$projectid)");
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update one committed workitem record from a databundle
     */
    public function updateOneSubprojectMemberData($databundle, $default_dc=0.95)
    {
        try
        {
            
            $projectid = $databundle['parent_projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing parent_projectid in databundle=" . print_r($databundle,TRUE));
            }
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            $subprojectid = $databundle['subprojectid'];
            if(empty($subprojectid))
            {
                throw new \Exception("Missing subprojectid in databundle=" . print_r($databundle,TRUE));
            }
            if(!array_key_exists('is_member_yn',$databundle))
            {
                throw new \Exception("Missing is_member_yn in databundle=" . print_r($databundle,TRUE));
            }
            $is_member_yn = $databundle['is_member_yn'];
            if(!array_key_exists('ot_scf',$databundle))
            {
                throw new \Exception("Missing ot_scf in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if($is_member_yn === 1)
            {
                $ot_scf = $databundle['ot_scf'];
                if(empty($ot_scf))
                {
                    $ot_scf = $default_dc;
                }
                $this->addSubprojectMember($projectid, $subprojectid, $ot_scf);
            } else {
                $this->removeSubprojectMember($projectid, $subprojectid);
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid
                    , "subprojectid"=>$subprojectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function updateProjectBrainstormItemsMoveTrashcan2Parkinglot($owner_projectid, $parkinglot_level=1)
    {
        try
        {
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing owner_projectid!");
            }
            $updated_dt = date("Y-m-d H:i:s", time());
            $myfields['updated_dt'] = $updated_dt;
            $myfields['into_parkinglot_dt'] = $updated_dt;
            $myfields['parkinglot_level'] = $parkinglot_level;
            $myfields['into_trash_dt'] = NULL;
            $myfields['active_yn'] = 1;
            $num_updated = db_update(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->fields($myfields)
                    ->where("projectid=$owner_projectid and (active_yn=0 OR into_trash_dt IS NOT NULL)")
                ->execute(); 
            if($num_updated > 0)
            {
                $this->markProjectUpdated($owner_projectid, "Moved $num_updated brainstorm topics into parkinglot");
            }
            return $num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateProjectBrainstormItemsMoveParkinglot2Trashcan($owner_projectid, $parkinglot_level=1)
    {
        try
        {
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing owner_projectid!");
            }
            $updated_dt = date("Y-m-d H:i:s", time());
            $myfields['into_trash_dt'] = $updated_dt;
            $myfields['updated_dt'] = $updated_dt;
            $myfields['into_parkinglot_dt'] = NULL;
            $myfields['parkinglot_level'] = 0;
            $num_updated = db_update(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->fields($myfields)
                ->condition('projectid', $owner_projectid, '=')
                ->condition('parkinglot_level', $parkinglot_level, '=')
                ->condition('active_yn', 1,'=')
                ->execute(); 
            if($num_updated > 0)
            {
                $this->markProjectUpdated($owner_projectid, "Moved $num_updated brainstorm topics into trashcan");
            }
            return $num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateProjectBrainstormItemsEmptyTheTrashcan($owner_projectid)
    {
        try
        {
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing owner_projectid!");
            }
            $num_updated = db_delete(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->where("projectid=$owner_projectid and (active_yn=0 OR into_trash_dt IS NOT NULL)")
                ->execute(); 
            if($num_updated > 0)
            {
                $this->markProjectUpdated($owner_projectid, "Discarded all $num_updated brainstorm topics that were in the trashcan");
            }
            return $num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateProjectBrainstormItemsRestoreParkinglot($owner_projectid, $parkinglot_level=1)
    {
        try
        {
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing owner_projectid!");
            }
            $updated_dt = date("Y-m-d H:i:s", time());
            $myfields['into_trash_dt'] = NULL;
            $myfields['into_parkinglot_dt'] = NULL;
            $myfields['updated_dt'] = $updated_dt;
            $myfields['parkinglot_level'] = 0;
            $num_updated = db_update(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->fields($myfields)
                ->condition('projectid', $owner_projectid, '=')
                ->condition('parkinglot_level', $parkinglot_level, '=')
                ->condition('active_yn', 1,'=')
                ->execute(); 
            if($num_updated > 0)
            {
                $this->markProjectUpdated($owner_projectid, "Restored $num_updated brainstorm topics from parkinglot");
            }
            return $num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Apply simple locks
     */
    public function updateProjectEstimateLock($owner_projectid, $lockit)
    {
        try
        {
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing owner_projectid!");
            }
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $owner_projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$owner_projectid content at this time!");   
            }
            $myfields['planned_start_dt_locked_yn'] = $lockit ? 1 : 0;
            $myfields['planned_end_dt_locked_yn'] = $lockit ? 1 : 0;
            $myfields['effort_hours_est_locked_yn'] = $lockit ? 1 : 0;
            $num_updated = db_update(DatabaseNamesHelper::$m_workitem_tablename)
                ->fields($myfields)
                ->condition('owner_projectid', $owner_projectid,'=')
                ->condition('active_yn', 1,'=')
                ->execute(); 
            
            return $num_updated;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Important: Do NOT wrap this function in TRANSACTION otherwise tracking will fail!!!!!
     */
    public function autofillProjectPlan($projectid, $flags_ar) //$effort_yn=1,$dates_yn=1,$cost_yn=1)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            
            $loaded_engine = module_load_include('php','bigfathom_autofill','core/Engine');
            if(!$loaded_engine)
            {
                throw new \Exception('Failed to load the autofill Engine class');
            }

            $oPPAF = new \bigfathom\ProjectPlanAutoFill($projectid, $flags_ar);
            return $oPPAF->fillValues();
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update the lock and return the number of members
     */
    public function updateSprintMembershipLock($sprintid, $lockit)
    {
        try
        {
            $myfields['membership_locked_yn'] = $lockit ? 1 : 0;
            $num_updated = db_update(DatabaseNamesHelper::$m_sprint_tablename)
                ->fields($myfields)
                ->condition('id', $sprintid,'=')
                ->execute();
            $count_sql = "select count(workitemid) from " 
                    . DatabaseNamesHelper::$m_map_workitem2sprint_tablename 
                    . " where sprintid=:sprintid";
            $num_members = db_query($count_sql, array(':sprintid' => $sprintid))->fetchField();
            return $num_members;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update committed sprint membership information
     */
    public function updateSprintMembersData($databundle)
    {
        try
        {
            $change_count = 0;
            $sprintid = $databundle['sprintid'];
            if(empty($sprintid))
            {
                throw new \Exception("Missing sprintid in databundle=" . print_r($databundle,TRUE));
            }
            $memberships = $databundle['memberships'];
            if(empty($memberships))
            {
                throw new \Exception("Missing memberships in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if(!empty($memberships["default_confidence"]))
            {
                $default_confidence = $memberships["default_confidence"];
            } else {
                $default_confidence = 0.5;
            }
            if(!empty($memberships["new"]))
            {
                $new_memberships = $memberships["new"];
                foreach($new_memberships as $workitemid=>$info)
                {
                    $ot_scf = $info['ot_scf'];
                    if(empty($ot_scf))
                    {
                        $ot_scf = $default_confidence;
                    }
                    $change_count++;
                    $this->addSprintWorkitemMember($sprintid, $workitemid, $ot_scf);
                }
                $change_comment .= "new members ";
            } else
            if(!empty($memberships["remove"]))
            {
                $new_memberships = $memberships["remove"];
                foreach($new_memberships as $workitemid=>$info)
                {
                    $change_count++;
                    $this->removeSprintWorkitemMember($sprintid, $workitemid);
                }
                $change_comment .= "removed members ";
            }
            if($change_count > 0)
            {
            //redundant    $this->markProjectUpdatedForSprintID($sprintid, "membership $change_count changes");
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "sprintid"=>$sprintid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update project group memberships
     */
    public function updateProjectGroupMembershipData($databundle)
    {
        try
        {
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing projectid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if(!empty($databundle["new"]))
            {
                $new_groupid = $databundle["new"];
                foreach($new_groupid as $groupid)
                {
                    $this->addProjectGroupMember($projectid, $groupid);
                }
                $change_comment .= "new groups";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_roleids = $databundle["remove"];
                foreach($remove_roleids as $groupid)
                {
                    $this->removeProjectGroupMember($projectid, $groupid);
                }
                $change_comment .= "removed groups";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update influence score of a person on workitems
     */
    public function updatePersonInfluence2WorkitemData($databundle)
    {
        try
        {
            $projectid = $databundle['projectid'];
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            if(empty($projectid))
            {
                throw new \Exception("Missing projectid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if(!empty($databundle["new"]))
            {
                $new_maps = $databundle["new"];
                foreach($new_maps as $onemap)
                {
                    $this->addPersonInfluence2Workitem($onemap['workitemid'], $onemap['personid'], $onemap['created_by_personid'], $onemap['influence']);
                }
                $change_comment .= "new influence value";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_maps = $databundle["remove"];
                foreach($remove_maps as $onemap)
                {
                    $this->removePersonInfluence2Workitem($onemap['workitemid'], $onemap['personid'], $onemap['created_by_personid']);
                }
                $change_comment .= "removed influence value";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update importance score of a person on workitems
     */
    public function updatePersonImportance2WorkitemData($databundle)
    {
        try
        {
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing projectid in databundle=" . print_r($databundle,TRUE));
            }
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            
            if(!empty($databundle["new"]))
            {
                $new_maps = $databundle["new"];
                foreach($new_maps as $onemap)
                {
                    $this->addPersonImportance2Workitem($onemap['workitemid'], $onemap['personid'], $onemap['created_by_personid'], $onemap['importance']);
                }
                $change_comment .= "new importance value";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_maps = $databundle["remove"];
                foreach($remove_maps as $onemap)
                {
                    $this->removePersonImportance2Workitem($onemap['workitemid'], $onemap['personid'], $onemap['created_by_personid']);
                }
                $change_comment .= "removed importance value";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update project group memberships
     */
    public function updatePCGMembershipData($databundle)
    {
        try
        {
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing projectid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            
            if(!empty($databundle["new"]))
            {
                $new_personid = $databundle["new"];
                foreach($new_personid as $personid)
                {
                    $this->addPCGMember($projectid, $personid);
                }
                $change_comment .= "new pcg member";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_personids = $databundle["remove"];
                foreach($remove_personids as $personid)
                {
                    $this->removePCGMember($projectid, $personid);
                }
                $change_comment .= "removed pcg member";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update group memberships
     */
    public function updateGroupMembershipData($databundle)
    {
        try
        {
            $groupid = $databundle['groupid'];
            if(empty($groupid))
            {
                throw new \Exception("Missing groupid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeGroupContent($this_uid, $groupid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$groupid content at this time!");   
            }
            
            if(!empty($databundle["new"]))
            {
                $new_personid = $databundle["new"];
                foreach($new_personid as $personid)
                {
                    $this->addGroupMember($groupid, $personid);
                }
                $change_comment .= "new group member";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_personids = $databundle["remove"];
                foreach($remove_personids as $personid)
                {
                    $this->removeGroupMember($groupid, $personid);
                }
                $change_comment .= "removed group member";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "groupid"=>$groupid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Update project group memberships
     */
    public function updateVision2ProjectData($databundle)
    {
        try
        {
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing projectid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            global $user;
            $this_uid = $user->uid;
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            
            if(!empty($databundle["new"]))
            {
                $new_visionstatementid = $databundle["new"];
                foreach($new_visionstatementid as $visionstatementid)
                {
                    $this->addVisionStatementAssignment($projectid, $visionstatementid);
                }
                $change_comment .= "new vision statement assignment";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_personids = $databundle["remove"];
                foreach($remove_personids as $visionstatementid)
                {
                    $this->removeVisionStatementAssignment($projectid, $visionstatementid);
                }
                $change_comment .= "removed vision statement assignment";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "projectid"=>$projectid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update committed workitem role information
     */
    public function updateWorkitemRoleData($databundle)
    {
        try
        {
            
            $workitemid = $databundle['workitemid'];
            if(empty($workitemid))
            {
                throw new \Exception("Missing workitemid in databundle=" . print_r($databundle,TRUE));
            }
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            global $user;
            $this_uid = $user->uid;
            $projectid = $this->m_oMapHelper->getProjectIDForWorkitem($workitemid);
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            
            if(!empty($databundle["new"]))
            {
                $new_roleids = $databundle["new"];
                foreach($new_roleids as $roleid)
                {
                    $this->addWorkitemRoleMember($workitemid, $roleid);
                }
                $change_comment .= "new roles";
            }
            if(!empty($databundle["remove"]))
            {
                $remove_roleids = $databundle["remove"];
                foreach($remove_roleids as $roleid)
                {
                    $this->removeWorkitemRoleMember($workitemid, $roleid);
                }
                $change_comment .= "removed roles";
            }
            $msg = "Updated $change_comment";
            $resultbundle = array(
                    "message"=>$msg
                    , "workitemid"=>$workitemid);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Creates if nativeid is null, else updates
     */
    public function updateOneBrainstormNodeData($databundle)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Cannot update workitem topics without a projectid!");
            }
            $purpose_tx = isset($databundle['purpose_tx']) ? $databundle['purpose_tx'] : NULL;
            $comment_tx = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            $status_cd = isset($databundle['status_cd']) ? $databundle['status_cd'] : "B";
            
            $node = $databundle['node'];
            $brainstormid = isset($node['nativeid']) ? $node['nativeid'] : null;
            if(empty($node['label']))
            {
                throw new \Exception("Cannot create a candidate topic without a label!");
            }
            $item_nm = $node['label'];
            $nodetype = isset($node['type']) ? strtolower(trim($node['type'])) : "";
            if(empty($node['context_nm']))
            {
                $context_nm = "brainstorming";
            } else {
                $context_nm = trim($node['context_nm']);
            }
            switch($nodetype)
            {
                case 'goal': $candidate_type = 'G'; break;
                case 'task': $candidate_type = 'T'; break;
                default: $candidate_type = '';
            }
            
            $myvalues = array(
                  'projectid' => $projectid,
                  'item_nm' => $item_nm,
                  'context_nm' => $context_nm,
                  'candidate_type' => $candidate_type,
                  'active_yn' => 1,
                  'status_cd' => $status_cd,
                  'purpose_tx' => $purpose_tx,
                  'owner_personid' => $this_uid);
            
            $parkinglot_level = $this->parseJSONValueAs01($node['into_parkinglot']);
            $myvalues["parkinglot_level"] = $parkinglot_level;
            if($parkinglot_level < 1)
            {
                $myvalues["into_parkinglot_dt"] = NULL;
            } else {
                $myvalues["into_parkinglot_dt"] = $updated_dt;
            }
            
            if(array_key_exists('into_trash', $node))
            {
                //They said something about trash, so write it to the database.
                $into_trash = $this->parseJSONValueAsBoolean($node['into_trash']);
                if($into_trash)
                {
                    $myvalues["into_trash_dt"] = $updated_dt;
                } else {
                    $myvalues["into_trash_dt"] = NULL;
                }
            }
            $msg = NULL;
            if($brainstormid === NULL || empty($brainstormid))
            {
                //Create the database record
                $createresult = $this->createBrainstormItem($projectid, $myvalues);
                $brainstormid = $createresult['brainstormid'];
                if($comment_tx == NULL)
                {
                    $comment_tx = "created bid=$brainstormid";
                }
                $msg = $createresult['message'];
            } else {
                //Simply update the values
                $updateresult = $this->updateBrainstormItem($brainstormid, $myvalues);
                if($comment_tx == NULL)
                {
                    $comment_tx = "edited bid=$brainstormid";
                }
                $msg = $updateresult['message'];
            }

            //Now mark the project as updated.
            $this->markProjectUpdated($projectid, $comment_tx);
            
            $resultbundle = array(
                    "message"=>$msg
                    , "brainstormid"=>$brainstormid);
            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    /**
     * Update the status of a test case step
     */
    public function updateTestcaseStepStatusData($databundle)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $msg = NULL;

            //Pull info from the databundle
            $stepid = $databundle['testcasestepid'];
            $status_cd = $databundle['status_cd'];
            if(empty($stepid))
            {
                throw new \Exception("Missing required stepid in " . print_r($databundle,TRUE));
            }
            if(empty($status_cd))
            {
                throw new \Exception("Missing required status_cd in " . print_r($databundle,TRUE));
            }
            
            $testcaseid = $this->m_oMapHelper->getTestcaseIDForTestcaseStepID($stepid);;
            if(empty($testcaseid))
            {
                throw new \Exception("Failed to find the testcaseid for stepid#$stepid");
            }
            $projectid = $this->m_oMapHelper->getProjectIDForTestcaseStepID($stepid);
            if(empty($projectid))
            {
                throw new \Exception("Failed to find the projectid for stepid#$stepid of testcaseid#$testcaseid");
            }
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            //TODO --- verify they are allowed to edit the status!!!!!!!!!!!!!!!!!!!!

            $fields = array(
                        'status_cd' => $status_cd,
                        'executed_dt' => $updated_dt
                  );
            db_update(DatabaseNamesHelper::$m_testcasestep_tablename)
                  ->fields($fields)
                  ->condition('id',$stepid)
                  ->execute();
            $msg = "updated testcasestepid#$stepid of test case: values=" . print_r($fields,TRUE);
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : $msg;
            
            //Now create the history record
            $history_fields = $fields;
            $history_fields['originalid']=$stepid;
            $history_fields['history_comment_tx']=$change_comment;
            $history_fields['history_created_by_personid']=$this_uid;
            $history_fields['history_created_dt']=$updated_dt;
            $history_qry = db_insert(DatabaseNamesHelper::$m_testcasestep_history_tablename)
                ->fields($history_fields);
            $history_qry->execute(); 
            
            //Now mark the project as updated.
            $this->markProjectUpdated($projectid, $change_comment);
            
            //Now update the test case to successful completion IF all the steps are in PASS status
            $this->updateTestcaseStatusFromStepStatus($testcaseid);
            
            $resultbundle = array(
                    "message"=>$msg);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    protected function updateTestcaseStatusFromStepStatus($testcaseid)
    {
        try
        {
            $none_count = 0;
            $pass_count = 0;
            $fail_count = 0;
            
            $current_tc_status = NULL;    
            
            $sSQL = "select t.status_cd as tc_scd, s.status_cd as step_scd"
                    . " FROM " . DatabaseNamesHelper::$m_testcase_tablename . " t "
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_testcasestep_tablename . " s ON s.testcaseid=t.id"
                    . " WHERE t.id=$testcaseid";
            
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $current_tc_status = $record['tc_scd'];
                $status_cd = $record['step_scd'];
                if($status_cd == 'NONE')
                {
                    $none_count++;
                } else
                if($status_cd == 'PASS')
                {
                    $pass_count++;
                } else
                if($status_cd == 'FAIL')
                {
                    $fail_count++;
                }
            }
            
            $set_tc_status = NULL;
            $set_reason = NULL;
            if($fail_count > 0)
            {
                //We have found a failure
                $set_reason = "$fail_count failed steps";
                $set_tc_status = "FT";
            } else {
                //Nothing has failed
                if($pass_count > 0 && $none_count == 0)
                {
                    //Everything passed
                    $set_reason = "all $pass_count steps passed";
                    $set_tc_status = "STC";
                } else
                if($pass_count > 0 || $none_count > 0)
                {
                    if($current_tc_status == 'FT' || $current_tc_status == 'STC')
                    {
                        //Mark it ready for testing instead of the current extreme setting!
                        $set_reason = "$pass_count steps passed and $none_count not yet checked";
                        $set_tc_status = "AFU";
                    }
                }
            }
            if(!empty($set_tc_status) && $set_tc_status != $current_tc_status)
            {
                $projectid = $this->m_oMapHelper->getProjectIDForTestcaseID($testcaseid);
                if(empty($projectid))
                {
                    throw new \Exception("Failed to find the projectid for testcaseid#$testcaseid");
                }
                        
                global $user;
                $this_uid = $user->uid;
                $updated_dt = date("Y-m-d H:i", time());
                $fields = array(
                            'status_cd' => $set_tc_status,
                            'status_set_dt' => $updated_dt,
                            'updated_dt' => $updated_dt,
                      );
                db_update(DatabaseNamesHelper::$m_testcase_tablename)
                      ->fields($fields)
                      ->condition('id',$testcaseid)
                      ->execute();

                //Now create the history record
                $change_comment = "status changed from $current_tc_status to $set_tc_status because $set_reason";
                $history_fields = $fields;
                $history_fields['original_updated_dt']=$updated_dt;
                unset($history_fields['updated_dt']);
                $history_fields['originalid']=$testcaseid;
                $history_fields['history_comment_tx']=$change_comment;
                $history_fields['history_created_by_personid']=$this_uid;
                $history_fields['history_created_dt']=$updated_dt;
                $history_qry = db_insert(DatabaseNamesHelper::$m_testcase_history_tablename)
                    ->fields($history_fields);
                $history_qry->execute(); 

                //Now mark the project as updated.
                $this->markProjectUpdated($projectid, "testcase#$testcaseid " . $change_comment);
                
            }

        } catch (\Exception $ex) {
            
            //throw new \Exception("LOOK ERR $sSQL bc $ex",99777,$ex);
            throw $ex;
        }
    }
    
    /**
     * Update the edit mode of the project
     */
    public function updateProjectEditMode($databundle)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $msg = NULL;

            //Pull info from the databundle
            $projectid = $databundle['projectid'];
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            $project_edit_lock_term = $databundle['project_edit_lock_term'];
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;

            
            switch ($project_edit_lock_term)
            {
                case "pcgonly":
                    $project_edit_lock_cd = 1;
                    break;
                case "owneranddelegates":
                    $project_edit_lock_cd = 2;
                    break;
                case "primaryowner":
                    $project_edit_lock_cd = 3;
                    break;
                case "nobody":
                    $project_edit_lock_cd = 99;
                    break;
                default:
                    //"membergroups";
                    //Treat this as open
                    $project_edit_lock_cd = 0;
                    break; 
            }
              
            $fields = array(
                        'project_edit_lock_cd' => $project_edit_lock_cd
                  );
            db_update(DatabaseNamesHelper::$m_project_tablename)
                  ->fields($fields)
                  ->condition('id',$projectid)
                  ->execute();
            $msg = print_r($fields,TRUE);
            
            //Now mark the project as updated.
            $this->markProjectUpdated($projectid, $change_comment);
            
            if(empty($msg))
            {
                $msg = "OK";
            }
            $resultbundle = array(
                    "message"=>$msg);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Updates the goal record and related records
     */
    public function updateOneGoalNodeData($databundle)
    {
        $transaction = db_transaction();
        try
        {
            $msg_ar = array();
            
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());

            //Pull info from the databundle
            $projectid = $databundle['projectid'];
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }
            
            $is_suggestion = isset($databundle['is_suggestion']) ? $databundle['is_suggestion'] : TRUE;
            $map2owner_ar = isset($databundle['map2owner']) ? $databundle['map2owner'] : array();
            $map2sprint_ar = isset($databundle['map2sprint']) ? $databundle['map2sprint'] : array();
            $change_comment = isset($databundle['change_comment']) ? $databundle['change_comment'] : NULL;
            $node = $databundle['node'];
            $workitemid = $node['nativeid'];
            $nodetype = trim($node['type']);

            //Initialize important flags
            $create_new_node = ($workitemid === NULL);
            $msg = NULL;

            
            if($create_new_node)
            {
                throw new \Exception("Creating a new node is not supported by this interface at this time!");
            }

            //Are we updating goal owner?
            if(array_key_exists('owner_personid', $map2owner_ar))
            {
                //Yes, update the goal owner
                $owner_personid = $map2owner_ar['owner_personid'];
                
                if($is_suggestion)
                {
                    //This is a suggestion
                    $person2own_goal_tablename = DatabaseNamesHelper::$m_suggest_person2own_workitem_tablename;
                    $person2own_goal_key['created_by_personid'] = $this_uid;
                    $person2own_goal_key['workitemid'] = $workitemid;
                    
                    if(empty($owner_personid))
                    {
                        //Delete the suggestion
                        $delete_qry = db_delete($person2own_goal_tablename);
                        foreach($person2own_goal_key as $k=>$v)
                        {
                            $delete_qry->condition($k,$v);
                        }
                        $delete_qry->execute();
                        $msg_ar[] = "Deleted mapping to person";
                    } else {
                        //Make the suggestion
                        $person2own_goal_fields = array(
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                    'workitemid' => $workitemid,
                                    'personid' => $owner_personid
                              );
                        db_merge($person2own_goal_tablename)
                              ->key($person2own_goal_key)
                              ->fields($person2own_goal_fields)
                              ->execute();
                        $msg_ar[] = "Changed suggested owner";
                    }
                } else {
                    //This is a committed change
                    $goal_owner_fields = array(
                                'updated_dt' => $updated_dt,
                                'owner_personid' => $owner_personid
                          );
                    db_update(DatabaseNamesHelper::$m_workitem_tablename)
                          ->fields($goal_owner_fields)
                          ->condition('workitemid',$workitemid)
                          ->execute();
                    $msg_ar[] = "Changed actual owner";
                }
            }
            
            //Are we updating goal mapping to a sprint?
            if(array_key_exists('sprintid', $map2sprint_ar))
            {
                //Yes, update the sprint mapping.
                $sprintid = $map2sprint_ar['sprintid'];
                $workitem2sprint_key = array('workitemid' => $workitemid);
                if($is_suggestion)
                {
                    $workitem2sprint_tablename = DatabaseNamesHelper::$m_suggest_map_workitem2sprint_tablename;
                    $workitem2sprint_key['created_by_personid'] = $this_uid;
                } else {
                    $workitem2sprint_tablename = DatabaseNamesHelper::$m_map_workitem2sprint_tablename;
                }
                if(empty($sprintid))
                {
                    //Delete the mapping
                    $delete_qry = db_delete($workitem2sprint_tablename);
                    foreach($workitem2sprint_key as $k=>$v)
                    {
                        $delete_qry->condition($k,$v);
                    }
                    $delete_qry->execute();
                    $msg_ar[] = "Deleted mapping to sprint";
                } else {
                    //Create/change the mapping
                    $workitem2sprint_importance = array_key_exists('importance', $map2sprint_ar) ? $map2sprint_ar['importance'] : NULL;

                    $workitem2sprint_fields = array(
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt,
                                'sprintid' => $sprintid,
                                'workitemid' => $workitemid
                          );
                    if($workitem2sprint_importance !== NULL)
                    {
                        $workitem2sprint_fields['importance'] = $workitem2sprint_importance;    
                    }
                    db_merge($workitem2sprint_tablename)
                          ->key($workitem2sprint_key)
                          ->fields($workitem2sprint_fields)
                          ->execute();
                    $msg_ar[] = "Changed mapping to sprint#$sprintid";
                }
            }
            
            if(count($msg_ar) === 0)
            {
                $msg_ar[] = "Nothing changed";
            }
            
            $resultbundle = array(
                    "message"=>  implode("; ", $msg_ar)
                    , "workitemid"=>$workitemid);
            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    /**
     * Change the saved workitem dependency data
     */
    public function updateWorkitemHierarchyData($databundle)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $msg_ar = [];
            $projectid = $databundle['projectid'];
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $link_changes = $databundle['link_changes'];
            $node_changes = $databundle['node_changes'];
            $new_candidate_node_count = 0;
            $changed_node_count = 0;
            
            $added_link_count = 0;
            $deleted_link_count = 0;
            $map_brainstormid2wid = [];
            
            //Store the node changes
            foreach($node_changes as $onenodechange)
            {
                $action = $onenodechange['action'];
                if($action == 'remove')
                {
                    //TODO
                    throw new \Exception("Removal not yet implemented!");
                } else 
                if($action == 'add_candidate')
                {
                    //error_log("LOOK node_changes=" . print_r($node_changes,TRUE));
                    //throw new \Exception("LOOK debug this node_changes=" . print_r($node_changes,TRUE));
                    
                    $myvalues = [];
                    $myvalues['item_nm'] = $onenodechange['workitem_nm'];
                    $myvalues['purpose_tx'] = $onenodechange['purpose_tx'];
                    $myvalues['candidate_type'] = $onenodechange['workitem_basetype'];
                    
                    if(isset($onenodechange['branch_effort_hours_est']))
                    {
                        $myvalues['branch_effort_hours_est'] = $onenodechange['branch_effort_hours_est'];
                    }
                    if(isset($onenodechange['remaining_effort_hours']))
                    {
                        $myvalues['remaining_effort_hours'] = $onenodechange['remaining_effort_hours'];
                    }
                    
                    $myvalues['owner_personid'] = $this_uid;
                    $this->createBrainstormItem($projectid, $myvalues);
                    $new_candidate_node_count++;
                } else 
                if($action == 'change')
                {
                    $workitemid = $onenodechange['workitemid'];
                    $this->updateWorkitem($workitemid, $onenodechange, NULL, FALSE);
                    $changed_node_count++;
                } else {
                    throw new \Exception("Did NOT recognize action=$action in $onenodechange=" . print_r($onenodechange,TRUE));
                }
            }
            
            //Store the link changes
            foreach($link_changes as $onelinkchange)
            {
                $action = $onelinkchange['action'];
                if($action == 'remove')
                {
                    //Delete the removed links
                    $native_target_id = $onelinkchange['trgnid'];
                    $native_source_id = $onelinkchange['srcnid'];
                    $deleted_link_count += db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->condition(db_and()
                              ->condition('depwiid', $native_target_id)
                              ->condition('antwiid', $native_source_id)
                              )
                      ->execute();
                    if($deleted_link_count == 0)
                    {
                        $msg="FAILED TO DELETE link for target=$native_target_id source=$native_source_id";
                        error_log($msg);
                    }
                } else 
                if($action == 'add')
                {
                    //Convert candidates into actual project nodes
                    if($onelinkchange['src_is_candidate'])
                    {
                        $othervalues = array("converter_personid"=>$this_uid);
                        $brainstormid = $onelinkchange['srcnid'];
                        $native_source_id = $this->convertCandidateItem2RealWorkitem($brainstormid, $othervalues);
                        $map_brainstormid2wid[$brainstormid] = $native_source_id;
                    } else {
                        $native_source_id = $onelinkchange['srcnid'];
                    }
                    if($onelinkchange['trg_is_candidate'])
                    {
                        $othervalues = array("converter_personid"=>$this_uid);
                        $brainstormid = $onelinkchange['trgnid'];
                        $native_target_id = $this->convertCandidateItem2RealWorkitem($brainstormid, $othervalues);
                        $map_brainstormid2wid[$brainstormid] = $native_target_id;
                    } else {
                        $native_target_id = $onelinkchange['trgnid'];
                    }

                    //Now add the link
                    $key = array(
                                'depwiid' => $native_target_id,
                                'antwiid' => $native_source_id);
                    $fields = array(
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt);                    
                    db_merge(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                          ->key($key)
                          ->fields($fields)
                          ->execute();
                    $added_link_count++;
                } else 
                if($action == 'none')
                {
                    //Nothing to do with this one.
                } else {
                    throw new \Exception("Did NOT recognize action=$action in $onelinkchange=" . print_r($onelinkchange,TRUE));
                }
            }
            
            $changes = 0;
            
            if($new_candidate_node_count > 0)
            {
                $msg_ar[] = "$new_candidate_node_count new candidate workitems";
                $changes += $new_candidate_node_count;
            }
            if($changed_node_count > 0)
            {
                $msg_ar[] = "Changed $changed_node_count workitems";
                $changes += $changed_node_count;
            }
            if($added_link_count > 0)
            {
                $msg_ar[] = "Saved $added_link_count new links";
                $changes += $added_link_count;
            }
            if($deleted_link_count > 0)
            {
                $msg_ar[] = "Deleted $deleted_link_count new links";
                $changes += $deleted_link_count;
            }

            if($changes == 0)
            {
                $msg_ar[] = "No changes detected";
            }            
            
            $msg = implode(";\n", $msg_ar);
            $resultbundle = array(
                          "message"=>$msg
                        , "changed_node_count"=>$changed_node_count
                        , "added_link_count"=>$added_link_count
                        , "deleted_link_count"=>$deleted_link_count
                        , "map_brainstormid2wid"=>$map_brainstormid2wid
                    );
            
            if($deleted_link_count > 0 || $added_link_count > 0)
            {
                $comment_tx = "alc=$added_link_count and dlc=$deleted_link_count";
            }
            
            if(empty($comment_tx))
            {
                $comment_tx = "updated hierarchy";
            }
            $this->markProjectUpdated($projectid, $comment_tx);
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            $msg = "FAILED to update data because " . print_r($ex,TRUE);
            //drupal_set_message($msg,'error');
            throw $ex;
        }
    }
   
    /**
     * Converts the brainstorm item into a real workitem and updates relationships.
     * The brainstorm ID can be prefixed with 'c'.
     */
    public function convertCandidateItem2RealWorkitem($brainstormid, $othervalues=NULL, $key_conversion=NULL)
    {
        $transaction = db_transaction();
        $insertfields = NULL;
        $real_wid = NULL;
        try
        {
            if($othervalues === NULL)
            {
                $othervalues = [];
            }
            if($key_conversion === NULL)
            {
                $key_conversion = [];
            }
            if(empty($brainstormid))
            {
                throw new \Exception("Expected a brainstormid but did not get one!");
            }
            if(substr($brainstormid, 0, 1) === 'c')
            {
                $brainstormid = substr($brainstormid, 1);
                if(empty($brainstormid))
                {
                    throw new \Exception("Expected a brainstormid but did not get nativeid after the prefix!");
                }
            }
            $default_status_cd = 'B';
            
            $brainstorm_rec = NULL;
            $projectid = NULL;
            try
            {
                //Get details from brainstorm table record
                $brainstorm_rec = $this->m_oMapHelper->getOneBrainstormItemByID($brainstormid);
                $projectid = $brainstorm_rec['projectid'];
            } catch (\Exception $ex) {
                //See if this was already converted before giving up!
                $b2w_map = $this->m_oMapHelper->getBrainstorm2WorkitemConversionMap($brainstormid);
                if(!empty($b2w_map[$brainstormid]))
                {
                    //Good news, already converted!
                    $real_wid = $b2w_map[$brainstormid];
                } else {
                    //We have a problem!
                    throw $ex;
                }
            }
            
            //Did we already convert?
            if($real_wid == NULL)
            {
                //Convert it now.
                if(empty($projectid))
                {
                    throw new \Exception("Expected projectid in bsr=" + print_r($brainstorm_rec,TRUE));
                }
                $myvalues = array();
                $myvalues['workitem_nm'] = $brainstorm_rec['item_nm'];
                $myvalues['importance'] = $brainstorm_rec['importance'];
                $myvalues['owner_personid'] = $brainstorm_rec['owner_personid'];
                $myvalues['active_yn'] = $brainstorm_rec['active_yn'];
                $myvalues['purpose_tx'] = $brainstorm_rec['purpose_tx'];
                $myvalues['status_cd'] = $default_status_cd;
                $myvalues['workitem_basetype'] = $brainstorm_rec['candidate_type'];
                $myvalues['remaining_effort_hours'] = $brainstorm_rec['effort_hours_est'];

                if(empty($myvalues['map_delegate_owner']))
                {
                    $myvalues['map_delegate_owner'] = [];
                }
                if(!empty($othervalues["converter_personid"]))
                {
                    $converter_personid = $othervalues["converter_personid"];
                    if($converter_personid != $myvalues['owner_personid'])
                    {
                        //Add the converter person as the owner at time of conversion and make the candidate owner as a delegate
                        $myvalues['map_delegate_owner'][] = $myvalues['owner_personid'];
                        $myvalues['owner_personid'] =  $converter_personid;
                    }
                }

                if(empty($myvalues['workitem_basetype']))
                {
                    throw new \Exception("Cannot convert brainstormid=$brainstormid to real workitem because missing workitem_basetype in REC=" . print_r($brainstorm_rec,TRUE));
                }

                foreach($othervalues as $key=>$value)
                {
                    $myvalues[$key] = $value;
                }

                //Create the real goal record
                $resultbundle = $this->createWorkitem($projectid, $myvalues, $key_conversion);
                $real_wid = $resultbundle['workitemid'];
                if(empty($real_wid))
                {
                    throw new \Exception("Failed to get workitemid!");
                }

                //Map the new record to the old topic record
                $updated_dt = date("Y-m-d H:i", time());
                $insertfields = array(
                        'brainstormid' => $brainstormid,
                        'workitemid' => $real_wid,
                        'created_dt' => $updated_dt
                    );
    // drupal_set_message("LOOK about to merge of $insertfields=" . print_r($insertfields,TRUE));           
                db_merge(DatabaseNamesHelper::$m_map_brainstormid2wid_tablename)
                  ->key(array('brainstormid' => $brainstormid))
                        ->fields($insertfields)->execute(); 
    // drupal_set_message("LOOK done with merge of $insertfields=" . print_r($insertfields,TRUE));           
                //Now delete the brainstorm record
                db_delete(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                  ->condition('id', $brainstormid)
                  ->execute();
                $resultbundle['removed_brainstormid'] = $brainstormid;
            }
            
            return $real_wid;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            if(!empty($insertfields))
            {
                throw new \Exception("Failed to convert bid=$brainstormid othervalues=" . print_r($othervalues, TRUE) . " insertfields=" . print_r($insertfields,TRUE) ,99888 , $ex);
            } else {
                throw new \Exception("Failed to convert bid=$brainstormid othervalues=" . print_r($othervalues, TRUE) ,99888 , $ex);
            }
        }
    }

    /**
     * Write the values into the database.
     */
    function createBrainstormItem($projectid, $myvalues)
    {
        $transaction = db_transaction();
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            if(empty($myvalues['owner_personid']))
            {
                throw new \Exception("Must declare an owner for new candidate workitem!");
            } else {
                $owner_personid = $myvalues['owner_personid'];
            }
            if(empty($myvalues['item_nm']))
            {
                if(empty($myvalues['label']))
                {
                    if(empty($myvalues['workitem_nm']))
                    {
                        throw new \Exception("Must declare an item_nm for new candidate workitem!");
                    } else {
                        $item_nm = trim($myvalues['workitem_nm']);
                    }
                } else {
                    $item_nm = trim($myvalues['label']);
                }
            } else {
                $item_nm = trim($myvalues['item_nm']);
            }
            if(isset($myvalues['candidate_type']))
            {
                $candidate_type = strtoupper(trim($myvalues['candidate_type']));
            } else {
                if(isset($myvalues['workitem_basetype']))
                {
                    $candidate_type = strtoupper(trim($myvalues['workitem_basetype']));
                } else {
                    throw new \Exception("Must declare the candidate_type for new candidate workitem!");
                }
            }
            if(empty($item_nm))
            {
                throw new \Exception("Must declare a NON-EMPTY item_nm for new candidate workitem!");
            }
            if(empty($myvalues['context_nm']))
            {
                $myvalues['context_nm'] = "Undeclared context";
            }
            if(empty($myvalues['purpose_tx']))
            {
                $myvalues['purpose_tx'] = "Undeclared purpose";
            }
            
            if(empty($myvalues['branch_effort_hours_est']))
            {
                $branch_effort_hours_est = NULL;
                $limit_branch_effort_hours_cd = 'I';
            } else {
                $branch_effort_hours_est = trim($myvalues['branch_effort_hours_est']);
                if($branch_effort_hours_est > '' && !is_numeric($branch_effort_hours_est))
                {
                    throw new \Exception("The branch_effort_hours_est '$branch_effort_hours_est' is not a number!");
                }
                if($branch_effort_hours_est == '')
                {
                    $branch_effort_hours_est = NULL;
                    $limit_branch_effort_hours_cd = 'I';
                } else {
                    $limit_branch_effort_hours_cd = 'L';
                }
            }

            if(empty($myvalues['remaining_effort_hours']))
            {
                $remaining_effort_hours = NULL;
            } else {
                $remaining_effort_hours = trim($myvalues['remaining_effort_hours']);
                if($remaining_effort_hours > '' && !is_numeric($remaining_effort_hours))
                {
                    throw new \Exception("The branch_effort_hours_est '$branch_effort_hours_est' is not a number!");
                }
                if($remaining_effort_hours == '')
                {
                    $remaining_effort_hours = NULL;
                }
            }
            
            if(empty($myvalues['active_yn']))
            {
                $myvalues['active_yn'] = 1;
            }
            if(!empty($myvalues['parkinglot_level']) && $myvalues['parkinglot_level'] > 0)
            {
                $into_parkinglot_dt = $myvalues['into_parkinglot_dt'];
                $parkinglot_level = $myvalues['parkinglot_level'];
            } else {
                $into_parkinglot_dt = NULL;
                $parkinglot_level = 0;
            }
            $fields = array(
                  'projectid' => $projectid,
                  'item_nm' => $item_nm,
                  'candidate_type' => $candidate_type,
                  'context_nm' => $myvalues['context_nm'],
                  'active_yn' => $myvalues['active_yn'],
                  'parkinglot_level' => $parkinglot_level,
                  'into_parkinglot_dt' => $into_parkinglot_dt,
                  'purpose_tx' => $myvalues['purpose_tx'],
                  'owner_personid' => $owner_personid,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            //$fields['branch_effort_hours_est'] = $branch_effort_hours_est;
            //$fields['limit_branch_effort_hours_cd'] = $limit_branch_effort_hours_cd;
            $fields['effort_hours_est'] = $remaining_effort_hours;
            $main_qry = db_insert(DatabaseNamesHelper::$m_brainstorm_item_tablename)
                ->fields($fields);
            $newid = $main_qry->execute(); 
            
            //If we are here then we had success.
            $this->markProjectUpdated($projectid, "created brainstorm item");
            $msg = 'Added ' . $myvalues['item_nm'];
            $resultbundle = array('brainstormid'=>$newid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to add ' . $myvalues['item_nm']
                      . " brainstormitem because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Write the values into the database.
     */
    function createProjectBaseline($projectid, $myvalues)
    {
        global $user;
        $transaction = db_transaction();
        $this_uid = $user->uid;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            
            if(empty($projectid))
            {
                throw new \Exception("Must provide projectid!");
            }
            if(empty($myvalues))
            {
                throw new \Exception("Must provide content!");
            }
            
            $fields = [];
            
            $fields['shortname'] = strtoupper(trim($myvalues['shortname']));
            $fields['comment_tx'] = trim($myvalues['comment_tx']);
            
            $fields['projectid'] = $projectid;
            $fields['updated_by_personid'] = $this_uid;
            $fields['created_by_personid'] = $this_uid;
            $fields['updated_dt'] = $updated_dt;
            $fields['created_dt'] = $updated_dt;
            
            $main_qry = db_insert(DatabaseNamesHelper::$m_project_baseline_tablename)
                ->fields($fields);
            $newid = $main_qry->execute(); 
            
            $this->createWorkitemBaselineRecords($projectid, $newid);
            $this->createWorkitemRelationshipBaselineRecords($projectid, $newid);
            $this->createSprintBaselineRecords($projectid, $newid);
            $this->createForecastBaselineRecords($projectid, $newid);
            
            //If we are here then we had success.
            $msg = 'Added ' . $myvalues['shortname'];
            $resultbundle = array('project_baselineid'=>$newid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to add ' . $myvalues['shortname']
                      . " project baseline because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    public function createWorkitemBaselineRecords($projectid, $project_baselineid)
    {
        $record = NULL;
        $last_wid_processed = NULL;
        $next_wid2process = NULL;
        $transaction = db_transaction();
        try
        {
            if(empty($project_baselineid))
            {
                throw new \Exception("Missing required baselineid!");
            }
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            
            $query = db_select(DatabaseNamesHelper::$m_workitem_tablename,'w');
            $query->fields('w')
                    ->condition('w.owner_projectid',$projectid)
                    ->condition('w.active_yn',1);
            $result = $query->execute();
            while($record = $result->fetchAssoc()) 
            {
                $workitem_fields = $record;
                unset($workitem_fields['id']);
                $workitem_fields['project_baselineid'] = $project_baselineid;
                $workitemid = $record['id'];
                $comment_tx = NULL;
                $next_wid2process = $workitemid;
                $this->createWorkitemHistoryRecord($workitem_fields, $comment_tx, $workitemid);
                $last_wid_processed = $workitemid;
            }
            
        } catch (\Exception $ex) {
            if(!empty($next_wid2process))
            {
                if(empty($last_wid_processed))
                {
                    drupal_set_message("Failed setting history for wid#[$next_wid2process]!",'error');
                } else {
                    drupal_set_message("Failed setting history for wid#[$next_wid2process] after setting history for wid#[$last_wid_processed]!",'error');
                }
            }
            error_log("FAILED createWorkitemBaselineRecords($projectid, $project_baselineid) on record=".print_r($record,TRUE));
            $transaction->rollback();
            throw $ex;
        }
    }

    public function createWorkitemRelationshipBaselineRecords($projectid, $project_baselineid)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($project_baselineid))
            {
                throw new \Exception("Missing required baselineid!");
            }
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }

            $query = db_select(DatabaseNamesHelper::$m_map_wi2wi_tablename,'r');
            $query->join(DatabaseNamesHelper::$m_workitem_tablename, 'd', 'd.id = r.depwiid');
            $query->fields('r')
                    ->condition('d.owner_projectid',$projectid);
            $result = $query->execute();
            while($record = $result->fetchAssoc()) 
            {
                $fields = $record;
                $fields['project_baselineid'] = $project_baselineid;
                $this->createWorkitemRelationshipHistoryRecord($fields);
            }
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function createSprintBaselineRecords($projectid, $project_baselineid)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($project_baselineid))
            {
                throw new \Exception("Missing required baselineid!");
            }
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }

/** TODO SPRINT HISTORY TABLE            
            $query = db_select(DatabaseNamesHelper::$m_workitem_tablename,'w');
            $query->fields('w')
                    ->condition('w.owner_projectid',$projectid)
                    ->condition('w.active_yn',1);
            $result = $query->execute();
            while($record = $result->fetchAssoc()) 
            {
                $workitem_fields = $record;
                unset($workitem_fields['id']);
                $workitem_fields['project_baselineid'] = $project_baselineid;
                $workitemid = $record['id'];
                $comment_tx = NULL;
                $this->createWorkitemHistoryRecord($workitem_fields,$comment_tx,$workitemid);
            }
*/            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function createForecastBaselineRecords($projectid, $project_baselineid)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($project_baselineid))
            {
                throw new \Exception("Missing required baselineid!");
            }
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }

/** TODO FORECAST HISTORY TABLE            
            $query = db_select(DatabaseNamesHelper::$m_workitem_tablename,'w');
            $query->fields('w')
                    ->condition('w.owner_projectid',$projectid)
                    ->condition('w.active_yn',1);
            $result = $query->execute();
            while($record = $result->fetchAssoc()) 
            {
                $workitem_fields = $record;
                unset($workitem_fields['id']);
                $workitem_fields['project_baselineid'] = $project_baselineid;
                $workitemid = $record['id'];
                $comment_tx = NULL;
                $this->createWorkitemHistoryRecord($workitem_fields,$comment_tx,$workitemid);
            }
*/            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Write the values into the database.
     */
    function updateBrainstormItem($brainstormitemid, $myvalues)
    {
        $transaction = db_transaction();
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $fields = array(
                  'candidate_type' => $myvalues['candidate_type'],
                  'owner_personid' => $myvalues['owner_personid'],
                  'updated_dt' => $updated_dt,
            );
            if(isset($myvalues['candidate_type']))
            {
                $fields['candidate_type'] = $myvalues['candidate_type'];
            }
            if(isset($myvalues['item_nm']))
            {
                $fields['item_nm'] = $myvalues['item_nm'];
            }
            if(isset($myvalues['context_nm']))
            {
                $fields['context_nm'] = $myvalues['context_nm'];
            }
            if(isset($myvalues['purpose_tx']))
            {
                $fields['purpose_tx'] = $myvalues['purpose_tx'];
            }
            if(array_key_exists('parkinglot_level',$myvalues))
            {
                $fields['parkinglot_level'] = $myvalues['parkinglot_level'];
                if($myvalues['parkinglot_level'] < 1)
                {
                    $fields['into_parkinglot_dt'] = NULL;
                } else {
                    if(empty($myvalues['into_parkinglot_dt']))
                    {
                        $fields['into_parkinglot_dt'] = $updated_dt;
                    } else {
                        $fields['into_parkinglot_dt'] = $myvalues['into_parkinglot_dt'];
                    }
                }
            }
            if(array_key_exists('into_trash_dt',$myvalues))
            {
                $fields['into_trash_dt'] = $myvalues['into_trash_dt'];
            }
            if(array_key_exists('active_yn',$myvalues))
            {
                $fields['active_yn'] = $myvalues['active_yn'];
                /*
                $ayn = $myvalues['active_yn'];
                if($ayn == '1')
                { 
                    $fields['into_trash_dt'] = NULL;
                } else {
                    if(empty($myvalues['into_trash_dt']))
                    {
                        $fields['into_trash_dt'] = $updated_dt;
                    } else {
                        $fields['into_trash_dt'] = $myvalues['into_trash_dt'];
                    }
                }
                */
            }
            $update = db_update(DatabaseNamesHelper::$m_brainstorm_item_tablename)->fields($fields);
            $update->condition('id', $brainstormitemid,'=')
                    ->execute(); 
            //If we are here then we had success.
            $this->markProjectUpdatedForBraintormTopic($brainstormitemid, "updated candidate");
            $msg = 'Saved update for ' . $myvalues['item_nm'];
            $resultbundle = array('brainstormid'=>$brainstormitemid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update ' . $myvalues['brainstormitemid']
                      . ' braintorm item because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Write the values into the database.
     */
    function createURIDomainItem($myvalues)
    {
        $transaction = db_transaction();
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            global $user;
            $this_uid = $user->uid;
            
            if(empty($myvalues) || !is_array($myvalues))
            {
                throw new \Exception("Missing required myvalues array!");
            }
            
            $list_type = $myvalues['list_type'];
            if($list_type == 'WHITE')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename;
            } else
            if($list_type == 'BLACK')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_blacklist_tablename;
            } else {
                throw new \Exception("Did NOT recognize list type value '$list_type'");
            }
            $remote_uri_domain = strtolower(trim($myvalues['remote_uri_domain']));
            $fields = array(
                  'remote_uri_domain' => $remote_uri_domain,
                  'created_by_personid' => $this_uid,
                  'created_dt' => $updated_dt,
            );
            $main_qry = db_insert($tablename)
                ->fields($fields);
            $main_qry->execute(); 
            $msg = "Added '$remote_uri_domain'";
                
            $resultbundle = array(  'remote_uri_domain'=>$remote_uri_domain,
                                    'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update ' . $myvalues['remote_uri_domain']
                      . ' uri domain item because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Write the values into the database.
     */
    function updateURIDomainItem($original_remote_uri_domain,$myvalues)
    {
        $transaction = db_transaction();
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            global $user;
            $this_uid = $user->uid;
            
            if(empty($original_remote_uri_domain))
            {
                throw new \Exception("Missing required original_remote_uri_domain!");
            }
            
            if(empty($myvalues) || !is_array($myvalues))
            {
                throw new \Exception("Missing required myvalues array!");
            }
            
            $list_type = $myvalues['list_type'];
            if($list_type == 'WHITE')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename;
            } else
            if($list_type == 'BLACK')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_blacklist_tablename;
            } else {
                throw new \Exception("Did NOT recognize list type value '$list_type'");
            }
            $remote_uri_domain = strtolower(trim($myvalues['remote_uri_domain']));
            if($original_remote_uri_domain == $remote_uri_domain)
            {
                $msg = "No changes to save for '$original_remote_uri_domain'";
            } else {
                $fields = array(
                      'remote_uri_domain' => $remote_uri_domain,
                      'created_by_personid' => $this_uid,
                      'created_dt' => $updated_dt,
                );
                $update = db_update($tablename)->fields($fields);
                $update->condition('remote_uri_domain', $original_remote_uri_domain,'=')
                        ->execute(); 

                //If we are here then we had success.
                $msg = "Saved change from '$original_remote_uri_domain' into '$remote_uri_domain'";
            }
            $resultbundle = array(  'original_remote_uri_domain'=>$original_remote_uri_domain, 
                                    'remote_uri_domain'=>$remote_uri_domain,
                                    'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update ' . $myvalues['remote_uri_domain']
                      . ' uri domain item because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Write the values into the database.
     */
    function deleteURIDomainItem($remote_uri_domain,$list_type)
    {
        $transaction = db_transaction();
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            global $user;
            $this_uid = $user->uid;
            
            if(empty($remote_uri_domain))
            {
                throw new \Exception("Missing required remote_uri_domain!");
            }
            
            if($list_type == 'WHITE')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_whitelist_tablename;
            } else
            if($list_type == 'BLACK')
            {
                $tablename = DatabaseNamesHelper::$m_remote_uri_domain_blacklist_tablename;
            } else {
                throw new \Exception("Did NOT recognize list type value '$list_type'");
            }
            
            db_delete($tablename)
              ->condition('remote_uri_domain', $remote_uri_domain)
              ->execute(); 

            //If we are here then we had success.
            $msg = "Deleted uri domain '$remote_uri_domain'";
            $resultbundle = array(  'remote_uri_domain'=>$remote_uri_domain,
                                    'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to delete "' . $remote_uri_domain
                      . '" uri domain item because ' . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Delete the workitem from the database.
     */
    function deleteWorkitem($workitemid, $just_mark_not_active=TRUE)
    {
        $transaction = db_transaction();
        try
        {
            //Get the project ID now before we delete the records!
            $projectid = $this->m_oMapHelper->getProjectIDForWorkitem($workitemid);
            
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            //Remove all the links first
            db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
              ->condition('depwiid', $workitemid)
              ->execute(); 
            db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
              ->condition('antwiid', $workitemid)
              ->execute(); 
            //Now remove the workitem
            if($just_mark_not_active)
            {
                $w_fields = array(
                      'active_yn' =>0,
                  );
                $w_qry = db_update(DatabaseNamesHelper::$m_workitem_tablename)
                    ->fields($w_fields)
                        ->condition('id',$workitemid);
                $w_qry->execute();            
                $this->markProjectUpdated($projectid, "deactivated workitem#$workitemid");
            } else {
                db_delete(DatabaseNamesHelper::$m_workitem_tablename)
                  ->condition('id', $workitemid)
                  ->execute(); 
                $this->markProjectUpdated($projectid, "deleted workitem#$workitemid");
            }
            
            //If we are here then we had success.
            $msg = "Deleted workitem#$workitemid";
            $resultbundle = array('workitemid'=>$workitemid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to delete workitem#' . $workitemid
                      . " because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    private function updateRelationshipMaps($workitemid, $myvalues, $key_conversion = NULL, $created_by_personid = NULL, $updated_dt = NULL)
    {
        try
        {
            $has_map_ddw_ar = FALSE;    //Else no way to clear it with empty list
            if(!empty($myvalues['map_ddw']) && is_array($myvalues['map_ddw']))
            {
                $map_ddw_ar = $myvalues['map_ddw'];
                $has_map_ddw_ar = TRUE;
            } else if(!empty($myvalues['maps']['ddw']) && is_array($myvalues['maps']['ddw'])) {
                $map_ddw_ar = $myvalues['maps']['ddw'];
                $has_map_ddw_ar = TRUE;
            } else {
                $map_ddw_ar = [];
                $has_map_ddw_ar = TRUE;
            }
            $has_map_daw_ar = FALSE;    //Else no way to clear it with empty list
            if(!empty($myvalues['map_daw']) && is_array($myvalues['map_daw']))
            {
                $map_daw_ar = $myvalues['map_daw'];
                $has_map_daw_ar = TRUE;
            } else if(!empty($myvalues['maps']['daw']) && is_array($myvalues['maps']['daw'])) {
                $map_daw_ar = $myvalues['maps']['daw'];
                $has_map_daw_ar = TRUE;
            } else {
                $map_daw_ar = [];
                $has_map_daw_ar = TRUE;
            }
            $test_branchbundle = $this->m_oMapHelper->getSpeculativeBranchWorkitemMembersBundle($workitemid, $map_ddw_ar, $map_daw_ar);
            $has_problems = $test_branchbundle['analysis']['has_problems'];
            if($has_problems)
            {
                throw new \Exception("Detected relationship link problems for reference workitemid#$workitemid : " . print_r($test_branchbundle['analysis'],TRUE));
            }

            if(empty($key_conversion))
            {
                $key_conversion = [];
            }
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }

            //Add any new workitem mappings next
            if($has_map_ddw_ar)
            {
                db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                  ->condition('antwiid', $workitemid)
                  ->execute(); 
            }
            foreach($map_ddw_ar as $depwiid)
            {
                if(substr($depwiid,0,1) === 'c')
                {
                    if(!array_key_exists($depwiid, $key_conversion))
                    {
                        throw new \Exception("Expected to find conversion for '$depwiid' in " . print_r($key_conversion,TRUE));
                    }
                    $depwiid = $key_conversion[$depwiid];
                }
                //Update or insert
                db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                  ->fields(array(
                        'depwiid' => $depwiid,
                        'antwiid' => $workitemid,
                        'created_by_personid' => $created_by_personid,
                        'created_dt' => $updated_dt,
                    ))
                      ->execute(); 
            }

            if($has_map_daw_ar)
            {
                db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                  ->condition('depwiid', $workitemid)
                  ->execute(); 
            }
            foreach($map_daw_ar as $dawwiid)
            {
                if(substr($dawwiid,0,1) === 'c')
                {
                    if(!array_key_exists($dawwiid, $key_conversion))
                    {
                        throw new \Exception("Expected to find conversion for '$dawwiid' in " . print_r($key_conversion,TRUE));
                    }
                    $dawwiid = $key_conversion[$dawwiid];
                }
                //Update or insert
                db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                  ->fields(array(
                        'depwiid' => $workitemid,
                        'antwiid' => $dawwiid,
                        'created_by_personid' => $created_by_personid,
                        'created_dt' => $updated_dt,
                    ))
                      ->execute(); 
                }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function createWorkitemHistoryRecord($workitem_fields, $comment_tx, $workitemid_override=NULL)
    {
        try
        {
            global $user;
            $created_by_personid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            if(!isset($workitem_fields['created_dt']))
            {
                throw new \Exception("Missing required created_dt field!");
            }
            if(!isset($workitem_fields['created_by_personid']))
            {
                throw new \Exception("Missing required created_by_personid field!");
            }
            
            $history_fields = $workitem_fields;
            $history_fields['original_updated_dt'] = isset($workitem_fields['updated_dt']) ? $workitem_fields['updated_dt'] : NULL;
            $history_fields['original_created_dt'] = $workitem_fields['created_dt'];
            $history_fields['original_updated_by_personid'] = isset($workitem_fields['updated_by_personid']) ? $workitem_fields['updated_by_personid'] : NULL;
            $history_fields['original_created_by_personid'] = $workitem_fields['created_by_personid'];
            unset($history_fields['updated_dt']);
            unset($history_fields['created_dt']);
            unset($history_fields['updated_by_personid']);
            unset($history_fields['created_by_personid']);

            if(!empty($workitemid_override))
            {
                $history_fields['workitemid'] = $workitemid_override;  
            } else {
                $history_fields['workitemid'] = isset($workitem_fields['workitemid']) ? $workitem_fields['workitemid'] : $workitem_fields['id'];
            }
            $history_fields['history_comment_tx'] = $comment_tx;
            $history_fields['history_created_dt'] = $updated_dt;  
            $history_fields['history_created_by_personid'] = $created_by_personid;  
            
            $history_qry = db_insert(DatabaseNamesHelper::$m_workitem_history_tablename)
                ->fields($history_fields);
            
            return $history_qry->execute();
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function createWorkitemRelationshipHistoryRecord($fields, $history_created_cd='B')
    {
        $history_fields = [];
        try
        {
            global $user;
            $history_created_by_personid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            $history_fields = $fields;
            $history_fields['original_created_dt'] = $fields['created_dt'];
            $history_fields['original_created_by_personid'] = $fields['created_by_personid'];
            unset($history_fields['created_dt']);
            unset($history_fields['created_by_personid']);

            $history_fields['history_created_cd'] = $history_created_cd;
            $history_fields['history_created_dt'] = $updated_dt;  
            $history_fields['history_created_by_personid'] = $history_created_by_personid;  
            
            $history_qry = db_insert(DatabaseNamesHelper::$m_map_wi2wi_history_tablename)
                ->fields($history_fields);
            
            return $history_qry->execute();
        } catch (\Exception $ex) {
            //DebugHelper::showNeatMarkup(array('$fields'=>$fields,'$history_fields'=>$history_fields),'LOOK at this stuff');
            throw $ex;
        }
    }
    
    /**
     * Write the values into the database.
     */
    public function createWorkitem($projectid, $myvalues, $key_conversion=NULL, $created_by_personid=NULL, $updated_dt=NULL, $mark_project_updated=TRUE)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($myvalues['workitem_basetype']))
            {
                throw new \Exception("Missing required workitem_basetype! myvalues=" . print_r($myvalues,TRUE));
            }
            $bt = $myvalues['workitem_basetype'];
            if($key_conversion === NULL)
            {
                $key_conversion = [];
            }
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            $step = 0;
            if(empty($myvalues['status_cd']))
            {
                $myvalues['status_cd'] = 'B';
            }
            if(empty($myvalues['owner_personid']))
            {
                $myvalues['owner_personid'] = $created_by_personid;
            }
            $stepname = "about to add for created_by_personid=$created_by_personid";
            try
            {
                $workitem_nm = $myvalues['workitem_nm'];
                $step++;
                $stepname = "add core";
                
                $active_yn = isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1;    //Templates do not have this field
                $fields = array(
                      'owner_projectid' => $projectid,
                      'workitem_basetype' => $bt,
                      'workitem_nm' => $workitem_nm,
                      'importance' => $myvalues['importance'],
                      'owner_personid' => $myvalues['owner_personid'],
                      'active_yn' => $active_yn,
                      'purpose_tx' => $myvalues['purpose_tx'],
                      'status_cd' => $myvalues['status_cd'],
                      'status_set_dt' => $updated_dt,
                      'updated_dt' => $updated_dt,
                      'created_dt' => $updated_dt,
                      'updated_by_personid' => $created_by_personid,
                      'created_by_personid' => $created_by_personid,
                  );
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'remaining_effort_hours');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'tester_personid');

                if(!empty($myvalues['client_deliverable_yn']))
                {
                    $fields['client_deliverable_yn'] = $myvalues['client_deliverable_yn'];
                }
                if(!empty($myvalues['externally_billable_yn']))
                {
                    $fields['externally_billable_yn'] = $myvalues['externally_billable_yn'];
                }
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'planned_fte_count');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_est');
                if(!empty($myvalues['effort_hours_est_locked_yn']))
                {
                    $fields['effort_hours_est_locked_yn'] = $myvalues['effort_hours_est_locked_yn'];
                } else {
                    $fields['effort_hours_est_locked_yn'] = 0;
                }

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_worked_est');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_worked_act');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');

                if(!empty($myvalues['limit_branch_effort_hours_cd']))
                {
                    $fields['limit_branch_effort_hours_cd'] = $myvalues['limit_branch_effort_hours_cd'];
                } else {
                    $fields['limit_branch_effort_hours_cd'] = 'I';
                }

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'self_allow_dep_overlap_hours');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'self_allow_dep_overlap_pct');
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'ant_sequence_allow_overlap_hours');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'ant_sequence_allow_overlap_pct');
                
                if(!empty($myvalues['branch_effort_hours_est_p']))
                {
                    $fields['branch_effort_hours_est_p'] = $myvalues['branch_effort_hours_est_p'];
                }

                if(!empty($myvalues['planned_start_dt']))
                {
                    $fields['planned_start_dt'] = $myvalues['planned_start_dt'];
                }

                if(!empty($myvalues['planned_start_dt_locked_yn']))
                {
                    $fields['planned_start_dt_locked_yn'] = $myvalues['planned_start_dt_locked_yn'];
                } else {
                    $fields['planned_start_dt_locked_yn'] = 0;
                }

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'actual_start_dt');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'planned_end_dt');

                if(!empty($myvalues['planned_end_dt_locked_yn']))
                {
                    $fields['planned_end_dt_locked_yn'] = $myvalues['planned_end_dt_locked_yn'];
                } else {
                    $fields['planned_end_dt_locked_yn'] = 0;
                }

                if(!empty($myvalues['actual_end_dt']))
                {
                    $fields['actual_end_dt'] = $myvalues['actual_end_dt'];
                }

                $main_qry = db_insert(DatabaseNamesHelper::$m_workitem_tablename)
                    ->fields($fields);
                $newid = $main_qry->execute(); 

                $step++;
                $stepname = "add history";

                $this->createWorkitemHistoryRecord($fields, 'new workitem', $newid);
                
                //Add any new workitem mappings next
                $this->updateRelationshipMaps($newid, $myvalues, $key_conversion);
                /*
                $stepname = "add dep maps";
                $step++;
                if(!empty($myvalues['map_ddw']) && is_array($myvalues['map_ddw']))
                {
                    $map_ddw_ar = $myvalues['map_ddw'];
                } else if(!empty($myvalues['maps']['ddw']) && is_array($myvalues['maps']['ddw'])) {
                    $map_ddw_ar = $myvalues['maps']['ddw'];
                } else {
                    $map_ddw_ar = [];
                }
                foreach($map_ddw_ar as $depwiid)
                {
                    if(substr($depwiid,0,1) === 'c')
                    {
                        if(!array_key_exists($depwiid, $key_conversion))
                        {
                            throw new \Exception("Expected to find conversion for '$depwiid' in " . print_r($key_conversion,TRUE));
                        }
                        $depwiid = $key_conversion[$depwiid];
                    }
                    //Update or insert
                    db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->fields(array(
                            'depwiid' => $depwiid,
                            'antwiid' => $newid,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }

                $stepname = "add daw maps";
                $step++;
                if(!empty($myvalues['map_daw']) && is_array($myvalues['map_daw']))
                {
                    $map_daw_ar = $myvalues['map_daw'];
                } else if(!empty($myvalues['maps']['daw']) && is_array($myvalues['maps']['daw'])) {
                    $map_daw_ar = $myvalues['maps']['daw'];
                } else {
                    $map_daw_ar = [];
                }
                foreach($map_daw_ar as $dawwiid)
                {
                    if(substr($dawwiid,0,1) === 'c')
                    {
                        if(!array_key_exists($dawwiid, $key_conversion))
                        {
                            throw new \Exception("Expected to find conversion for '$dawwiid' in " . print_r($key_conversion,TRUE));
                        }
                        $dawwiid = $key_conversion[$dawwiid];
                    }
                    //Update or insert
                    db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->fields(array(
                            'depwiid' => $newid,
                            'antwiid' => $dawwiid,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
                */
                
                $stepname = "add proles";
                $step++;
                if(!empty($myvalues['map_prole2workitem']) && is_array($myvalues['map_prole2workitem']))
                {
                    $map_prole2workitem_ar = $myvalues['map_prole2workitem'];
                } else if(isset($myvalues['maps']) && (isset($myvalues['maps']['roles']) && is_array($myvalues['maps']['roles']))) {
                    $map_prole2workitem_ar = $myvalues['maps']['roles'];
                } else {
                    $map_prole2workitem_ar = [];
                }

                if(count($map_prole2workitem_ar)>0)
                {
                    if(isset($map_prole2workitem_ar[0]))
                    {
                        //Simple array where the value is the roleid
                        foreach($map_prole2workitem_ar as $member_roleid)
                        {
                            if(!empty($member_roleid))
                            {
                                //Update or insert
                                db_insert(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                                  ->fields(array(
                                        'roleid' => $member_roleid,
                                        'workitemid' => $newid,
                                        'created_by_personid' => $created_by_personid,
                                        'created_dt' => $updated_dt,
                                    ))
                                      ->execute(); 
                            }
                        }
                    } else {
                        //Rich array which is a map where key is the roleid and value is the detail
                        $this->addProjectRolesToWorkitem($newid, $map_prole2workitem_ar, $created_by_personid, $updated_dt);
                    }
                }

                //Add any delegate owners now
                $stepname = "add delegates";
                $step++;
                if(isset($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
                {
                    foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                    {
                        if(!empty($delegate_personid))
                        {
                            db_insert(DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename)
                              ->fields(array(
                                    'personid' => $delegate_personid,
                                    'workitemid' => $newid,
                                    'created_by_personid' => $created_by_personid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    }
                }

                //Set tags
                if(isset($myvalues['map_tag2workitem_tx']))
                {
                    $tag_ar = explode(',',$myvalues['map_tag2workitem_tx']);
                    $this->replaceExistingTags($tag_ar, 'workitemid', $newid, DatabaseNamesHelper::$m_map_tag2workitem_tablename);
                }
                
                //If we are here then we had success.
                $stepname = "wrap-up";
                if(!$mark_project_updated)
                {
                    $resultbundle = array('workitemid'=>$newid,
                                          'message'=>'Added workitem #' . $newid . ': ' . $myvalues['workitem_nm']);
                } else {
                    $this->markProjectUpdated($projectid, "created workitem");
                    $resultbundle = array('workitemid'=>$newid,
                                          'message'=>'Added workitem #' . $newid . ': ' . $myvalues['workitem_nm'] . " in project#$projectid");
                }
                return $resultbundle;
            }
            catch(\Exception $ex)
            {
                $msg = t('Failed to add ' . $myvalues['workitem_nm']
                          . " goal at step#$step ($stepname) because " . $ex->getMessage());
                error_log("$msg\n" 
                          . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                throw new \Exception($msg, 99910, $ex);
            }
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    /**
     * Write the values into the database.
     */
    public function createWorkitemTemplateRecord($template_projectid, $myvalues, $key_conversion=NULL, $created_by_personid=NULL, $updated_dt=NULL)
    {
        $transaction = db_transaction();
        try
        {

            if(!isset($template_projectid))
            {
                throw new \Exception("Missing required template_projectid! myvalues=" . print_r($myvalues,TRUE));
            }
            if(empty($myvalues['workitem_basetype']))
            {
                throw new \Exception("Missing required workitem_basetype! myvalues=" . print_r($myvalues,TRUE));
            }
            $bt = $myvalues['workitem_basetype'];
            if($key_conversion === NULL)
            {
                $key_conversion = array();
            }
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            $step = 0;
            $stepname = "about to add for created_by_personid=$created_by_personid";
            try
            {
                $workitem_nm = $myvalues['workitem_nm'];

                $step++;
                $stepname = "add core";
                $fields = array(
                      'owner_template_projectid' => $template_projectid,
                      'workitem_basetype' => $bt,
                      'workitem_nm' => $workitem_nm,
                      'importance' => $myvalues['importance'],
                      'owner_personid' => $myvalues['owner_personid'],
                      'purpose_tx' => $myvalues['purpose_tx'],
                      'status_cd' => $myvalues['status_cd'],
                      'updated_dt' => $updated_dt,
                      'created_dt' => $updated_dt,
                  );
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'remaining_effort_hours');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'tester_personid');

                if(!empty($myvalues['client_deliverable_yn']))
                {
                    $fields['client_deliverable_yn'] = $myvalues['client_deliverable_yn'];
                }
                if(!empty($myvalues['externally_billable_yn']))
                {
                    $fields['externally_billable_yn'] = $myvalues['externally_billable_yn'];
                }
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'planned_fte_count');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_est');
                if(!empty($myvalues['effort_hours_est_locked_yn']))
                {
                    $fields['effort_hours_est_locked_yn'] = $myvalues['effort_hours_est_locked_yn'];
                } else {
                    $fields['effort_hours_est_locked_yn'] = 0;
                }

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_worked_est');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');

                if(!empty($myvalues['limit_branch_effort_hours_cd']))
                {
                    $fields['limit_branch_effort_hours_cd'] = $myvalues['limit_branch_effort_hours_cd'];
                } else {
                    $fields['limit_branch_effort_hours_cd'] = 'I';
                }

                if(!empty($myvalues['branch_effort_hours_est_p']))
                {
                    $fields['branch_effort_hours_est_p'] = $myvalues['branch_effort_hours_est_p'];
                }

                $main_qry = db_insert(DatabaseNamesHelper::$m_template_workitem_tablename)
                    ->fields($fields);
                $newid = $main_qry->execute(); 

                //Add any new workitem mappings next
                $stepname = "add dep maps";
                $step++;
                if(!empty($myvalues['map_ddw']) && is_array($myvalues['map_ddw']))
                {
                    $map_ddw_ar = $myvalues['map_ddw'];
                } else if(!empty($myvalues['maps']['ddw']) && is_array($myvalues['maps']['ddw'])) {
                    $map_ddw_ar = $myvalues['maps']['ddw'];
                } else {
                    $map_ddw_ar = [];
                }
                foreach($map_ddw_ar as $depwiid)
                {
                    if(substr($depwiid,0,1) === 'c')
                    {
                        if(!array_key_exists($depwiid, $key_conversion))
                        {
                            throw new \Exception("Expected to find conversion for '$depwiid' in " . print_r($key_conversion,TRUE));
                        }
                        $depwiid = $key_conversion[$depwiid];
                    }
                    //Update or insert
                    db_insert(DatabaseNamesHelper::$m_map_tw2tw_tablename)
                      ->fields(array(
                            'depwiid' => $depwiid,
                            'antwiid' => $newid,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }

                $stepname = "add proles";
                $step++;
                if(!empty($myvalues['map_prole2workitem']) && is_array($myvalues['map_prole2workitem']))
                {
                    $map_prole2workitem_ar = $myvalues['map_prole2workitem'];
                } else if(isset($myvalues['maps']) && (isset($myvalues['maps']['roles']) && is_array($myvalues['maps']['roles']))) {
                    $map_prole2workitem_ar = $myvalues['maps']['roles'];
                } else {
                    $map_prole2workitem_ar = [];
                }

                if(count($map_prole2workitem_ar)>0)
                {
                    if(isset($map_prole2workitem_ar[0]))
                    {
                        //Simple array where the value is the roleid
                        foreach($map_prole2workitem_ar as $member_roleid)
                        {
                            if(!empty($member_roleid))
                            {
                                //Update or insert
                                db_insert(DatabaseNamesHelper::$m_map_prole2tw_tablename)
                                  ->fields(array(
                                        'roleid' => $member_roleid,
                                        'workitemid' => $newid,
                                        'created_by_personid' => $created_by_personid,
                                        'created_dt' => $updated_dt,
                                    ))
                                      ->execute(); 
                            }
                        }
                    } else {
                        //Rich array which is a map where key is the roleid and value is the detail
                        $this->addProjectRolesToWorkitemTemplate($newid, $map_prole2workitem_ar, $created_by_personid, $updated_dt);
                    }
                }

                //Add tags
                $stepname = "add tags";
                $step++;
                if(!empty($myvalues['map_tag2workitem_tx']))
                {
                    if(is_array($myvalues['map_tag2workitem_tx']))
                    {
                        $map_tag2workitem_ar = $myvalues['map_tag2workitem_tx'];
                    } else {
                        $map_tag2workitem_ar = explode(',',$myvalues['map_tag2workitem_tx']);
                    }
                } else if(isset($myvalues['maps']['tags']) && is_array($myvalues['maps']['tags'])) {
                    $map_tag2workitem_ar = $myvalues['maps']['tags'];
                } else {
                    $map_tag2workitem_ar = [];
                }
                foreach($map_tag2workitem_ar as $tag_tx)
                {
                    $clean_tag_tx = strtoupper(trim($tag_tx));
                    if(!empty($clean_tag_tx))
                    {
                        //Insert
                        db_insert(DatabaseNamesHelper::$m_map_tag2tw_tablename)
                          ->fields(array(
                                'tag_tx' => $clean_tag_tx,
                                'workitemid' => $newid,
                                'created_by_personid' => $created_by_personid,
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }

                //If we are here then we had success.
                $stepname = "wrap-up";
                $this->markTPUpdated($template_projectid, "created tw");
                $resultbundle = array('template_workitemid'=>$newid,
                                      'message'=>'Added template workitem #' . $newid . ': ' . $myvalues['workitem_nm']);
                return $resultbundle;
            }
            catch(\Exception $ex)
            {
                $msg = t('Failed to add ' . $myvalues['workitem_nm']
                          . " goal at step#$step ($stepname) because " . $ex->getMessage());
                error_log("$msg\n" 
                          . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                throw new \Exception($msg, 99910, $ex);
            }
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    public function updateWorkitemWithZeroWi2WiChanges($workitemid, $myvalues, $key_conversion=NULL)
    {
        $apply_relationship_updates=FALSE;
        $this->updateWorkitem($workitemid, $myvalues, $key_conversion, $apply_relationship_updates);
    }
    
    /**
     * Update the values in the database for one workitem
     */
    public function updateWorkitem($workitemid, $myvalues, $key_conversion=NULL, $apply_relationship_updates=TRUE)
    {
        if(empty($workitemid))
        {
            throw new \Exception("Missing required workitemid!");
        }
        if($key_conversion === NULL)
        {
            $key_conversion = [];
        }

        $transaction = db_transaction();
        $step = 0;
        global $user;
        $this_uid = $user->uid;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
                
            $step++;
            $fields = array(
                  'updated_dt' => $updated_dt,
              );
            
            if(isset($myvalues['status_cd']))
            {
                $fields['status_cd'] = $myvalues['status_cd'];
                $fields['status_set_dt'] = $updated_dt;
            }
                
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'active_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'purpose_tx');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_projectid');
                
            $this->setIfKeyExists($fields, $myvalues, 'tester_personid', NULL);
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'workitem_basetype');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'workitem_nm');
            $this->setIfKeyExists($fields, $myvalues, 'importance', NULL);
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');
            
            if(empty($myvalues['owner_personid']))
            {
                $known_owner_personid = -999;   //We dont know here
            } else {
                $known_owner_personid = $myvalues['owner_personid'];
            }
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'client_deliverable_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'externally_billable_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'planned_fte_count');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_est_locked_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est_p');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');

            $this->setIfValueExistsNotBlank($fields, $myvalues, 'ant_sequence_allow_overlap_hours');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'ant_sequence_allow_overlap_pct');
            
            $this->setIfKeyExists($fields, $myvalues, 'effort_hours_est', NULL);
            $this->setIfKeyExists($fields, $myvalues, 'effort_hours_worked_est', NULL);
            $this->setIfKeyExists($fields, $myvalues, 'effort_hours_worked_act', NULL);
            $this->setIfKeyExists($fields, $myvalues, 'remaining_effort_hours', NULL);

            $this->setIfValueExists($fields, $myvalues, 'planned_start_dt_locked_yn', TRUE);
            $this->setIfValueExists($fields, $myvalues, 'planned_end_dt_locked_yn', TRUE);

            $this->setIfValueExists($fields, $myvalues, 'planned_start_dt', TRUE);
            $this->setIfValueExists($fields, $myvalues, 'actual_start_dt', TRUE);
 
            $this->setIfValueExists($fields, $myvalues, 'planned_end_dt', TRUE);
            $this->setIfValueExists($fields, $myvalues, 'actual_end_dt', TRUE);
            
            if(!empty($myvalues['task_resource_type']) && $myvalues['task_resource_type'] === 'equip')
            {
                $fields['equipmentid'] = $myvalues['equipmentid'];
                $fields['external_resourceid'] = NULL;
            } else
            if(!empty($myvalues['task_resource_type']) && $myvalues['task_resource_type'] === 'extrc')
            {
                $fields['equipmentid'] = NULL;
                $fields['external_resourceid'] = $myvalues['external_resourceid'];
            } else {
                $fields['equipmentid'] = NULL;
                $fields['external_resourceid'] = NULL;
            }
            $this->setIfValueExists($fields, $myvalues, 'chargecode');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'ignore_branch_cost_yn');

            $main_qry = db_update(DatabaseNamesHelper::$m_workitem_tablename)
                ->fields($fields)
                    ->condition('id',$workitemid,'=');
            $main_qry->execute(); 
            
            //Add any new goal mappings next
            if($apply_relationship_updates)
            {
                $this->updateRelationshipMaps($workitemid, $myvalues, $key_conversion);
            }
            /*

            if(!empty($myvalues['map_ddw']) && is_array($myvalues['map_ddw']))
            {
                //Delete all existing parent goal links first
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                  ->condition('antwiid', $workitemid)
                  ->execute(); 
                $step++;
                foreach($myvalues['map_ddw'] as $depwiid)
                {
                    //Update or insert
                    db_insert(DatabaseNamesHelper::$m_map_wi2wi_tablename)
                      ->fields(array(
                            'depwiid' => $depwiid,
                            'antwiid' => $workitemid,
                            'created_by_personid' => $this_uid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
            }
            */
            
            //Is this the root of the project?
            $sql_proj = "SELECT w.owner_projectid, p.root_goalid"
                    . " FROM " . DatabaseNamesHelper::$m_workitem_tablename . " w"
                    . " LEFT JOIN " . DatabaseNamesHelper::$m_project_tablename . " p ON p.root_goalid=w.id"
                    . " WHERE w.id={$workitemid}";
            $result = db_query($sql_proj);
            $record = $result->fetchAssoc();
            $owner_projectid = $record['owner_projectid'];
            $is_root_workitem = ($record['root_goalid'] == $workitemid);
            if($is_root_workitem)
            {
                //Update fields in the project record so they stay in synch
                $prj_fields = [];
                $this->setIfValueExists($prj_fields, $myvalues, 'planned_start_dt', TRUE);
                $this->setIfValueExists($prj_fields, $myvalues, 'actual_start_dt', TRUE);
                $this->setIfValueExists($prj_fields, $myvalues, 'planned_end_dt', TRUE);
                $this->setIfValueExists($prj_fields, $myvalues, 'actual_end_dt', TRUE);
                if(count($prj_fields) > 0)
                {
                    $prj_qry = db_update(DatabaseNamesHelper::$m_project_tablename)
                        ->fields($prj_fields)
                            ->condition('id',$owner_projectid,'=');
                    $prj_qry->execute(); 
                }
            }

            //Delete if already existing role maps first
            if(!empty($myvalues['map_prole2workitem']) && is_array($myvalues['map_prole2workitem']))
            {
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                    ->condition('workitemid', $workitemid)
                    ->execute(); 
                $step++;
                $map_prole2workitem_ar = $myvalues['map_prole2workitem'];
                if(count($map_prole2workitem_ar)>0)
                {
                    if(isset($map_prole2workitem_ar[0]))
                    {
                        //Simple array where the value is the roleid
                        foreach($map_prole2workitem_ar as $member_roleid)
                        {
                            //Update or insert
                            db_insert(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                              ->fields(array(
                                    'roleid' => $member_roleid,
                                    'workitemid' => $workitemid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    } else {
                        //Rich array which is a map where key is the roleid and value is the detail
                        $this->addProjectRolesToWorkitem($workitemid, $map_prole2workitem_ar, $this_uid, $updated_dt);
                    }
                }
            }
            
            //Delete if already existing delegate owner maps first
            if(!empty($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
            {
                //DebugHelper::debugPrintNeatly($myvalues,FALSE,"LOOK IN map_delegate_owner.............................");                
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename)
                    ->condition('workitemid', $workitemid)
                    ->execute(); 
                $step++;
                foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                {
                    if(!empty($delegate_personid) && $known_owner_personid != $delegate_personid)
                    {
                        db_insert(DatabaseNamesHelper::$m_map_delegate_workitemowner_tablename)
                          ->fields(array(
                                'personid' => $delegate_personid,
                                'workitemid' => $workitemid,
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }
            }
            
            //Replace tags
            if(isset($myvalues['map_tag2workitem_tx']))
            {
                $tag_ar = explode(',',$myvalues['map_tag2workitem_tx']);
                $this->replaceExistingTags($tag_ar, 'workitemid', $workitemid, DatabaseNamesHelper::$m_map_tag2workitem_tablename);
            }

            //If we are here then we had success.
            $this->markProjectUpdatedForWID($workitemid, "updated workitem");
            if(!isset($myvalues['workitem_nm']))
            {
                $resultbundle = array('workitemid'=>$workitemid,
                                      'message'=>'Updated workitem #' . $workitemid);
            } else {
                $resultbundle = array('workitemid'=>$workitemid,
                                      'message'=>'Updated workitem #' . $workitemid . ': ' . $myvalues['workitem_nm']);
            }
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update #' . $workitemid
                      . " workitem at step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    /**
     * Update the values in the database for one template workitem
     */
    public function updateTW($twid, $myvalues, $key_conversion=NULL)
    {
        if(empty($twid))
        {
            throw new \Exception("Missing required twid!");
        }
        if($key_conversion === NULL)
        {
            $key_conversion = [];
        }

        $transaction = db_transaction();
        $step = 0;
        global $user;
        $this_uid = $user->uid;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
                
            $step++;
            $fields = array(
                  'updated_dt' => $updated_dt,
              );
            
            if(isset($myvalues['status_cd']))
            {
                $fields['status_cd'] = $myvalues['status_cd'];
            }
                
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'purpose_tx');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_template_projectid');
                
            $this->setIfKeyExists($fields, $myvalues, 'tester_personid', NULL);
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'workitem_basetype');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'workitem_nm');
            $this->setIfKeyExists($fields, $myvalues, 'importance', NULL);
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');
            
            if(empty($myvalues['owner_personid']))
            {
                $known_owner_personid = -999;   //We dont know here
            } else {
                $known_owner_personid = $myvalues['owner_personid'];
            }
            
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'client_deliverable_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'externally_billable_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'planned_fte_count');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_hours_est_locked_yn');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est_p');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'branch_effort_hours_est');

            $this->setIfKeyExists($fields, $myvalues, 'effort_hours_est', NULL);
            $this->setIfKeyExists($fields, $myvalues, 'effort_hours_worked_est', NULL);
            $this->setIfKeyExists($fields, $myvalues, 'remaining_effort_hours', NULL);

            if(!empty($myvalues['task_resource_type']) && $myvalues['task_resource_type'] === 'equip')
            {
                $fields['equipmentid'] = $myvalues['equipmentid'];
                $fields['external_resourceid'] = NULL;
            } else
            if(!empty($myvalues['task_resource_type']) && $myvalues['task_resource_type'] === 'extrc')
            {
                $fields['equipmentid'] = NULL;
                $fields['external_resourceid'] = $myvalues['external_resourceid'];
            } else {
                $fields['equipmentid'] = NULL;
                $fields['external_resourceid'] = NULL;
            }
            $this->setIfValueExists($fields, $myvalues, 'chargecode');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'ignore_branch_cost_yn');

            $main_qry = db_update(DatabaseNamesHelper::$m_template_workitem_tablename)
                ->fields($fields)
                    ->condition('id',$twid,'=');
            $num_updated = $main_qry->execute(); 
            
            //Add any new goal mappings next
            if(!empty($myvalues['map_ddw']) && is_array($myvalues['map_ddw']))
            {
                //Delete all existing parent goal links first
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_tw2tw_tablename)
                  ->condition('antwiid', $twid)
                  ->execute(); 
                $step++;
                foreach($myvalues['map_ddw'] as $depwiid)
                {
                    //Update or insert
                    db_insert(DatabaseNamesHelper::$m_map_tw2tw_tablename)
                      ->fields(array(
                            'depwiid' => $depwiid,
                            'antwiid' => $twid,
                            'created_by_personid' => $this_uid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
            }

            //Delete if already existing role maps first
            if(!empty($myvalues['map_prole2tw']) && is_array($myvalues['map_prole2tw']))
            {
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                    ->condition('workitemid', $twid)
                    ->execute(); 
                $step++;
                $map_prole2workitem_ar = $myvalues['map_prole2tw'];
                if(count($map_prole2workitem_ar)>0)
                {
                    if(isset($map_prole2workitem_ar[0]))
                    {
                        //Simple array where the value is the roleid
                        foreach($map_prole2workitem_ar as $member_roleid)
                        {
                            //Update or insert
                            db_insert(DatabaseNamesHelper::$m_map_prole2tw_tablename)
                              ->fields(array(
                                    'roleid' => $member_roleid,
                                    'template_workitemid' => $twid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    } else {
                        //Rich array which is a map where key is the roleid and value is the detail
                        $this->addProjectRolesToWorkitem($twid, $map_prole2workitem_ar, $this_uid, $updated_dt);
                    }
                }
            }
            
            //Delete all existing tags first
            if(isset($myvalues['map_tag2tw_tx']))
            {
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_tag2tw_tablename)
                  ->condition('workitemid', $twid)
                  ->execute(); 
                //Add tags
                $step++;
                $map_tag2workitem_ar = explode(',',$myvalues['map_tag2tw_tx']);
                foreach($map_tag2workitem_ar as $tag_tx)
                {
                    $clean_tag_tx = strtoupper(trim($tag_tx));
                    //Insert
                    db_insert(DatabaseNamesHelper::$m_map_tag2tw_tablename)
                      ->fields(array(
                            'tag_tx' => $clean_tag_tx,
                            'template_workitemid' => $twid,
                            'created_by_personid' => $this_uid,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
            }

            //If we are here then we had success.
            $this->markTPUpdated($twid, "updated tw");
            if(!isset($myvalues['workitem_nm']))
            {
                $resultbundle = array('workitemid'=>$twid,
                                      'message'=>'Updated tw#' . $twid);
            } else {
                $resultbundle = array('template_workitemid'=>$twid,
                                      'message'=>'Updated tw#' . $twid . ': ' . $myvalues['workitem_nm']);
            }
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update tw#' . $twid
                      . " template workitem at step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Delete the usecase from the database.
     */
    function deleteUsecase($usecaseid, $just_mark_not_active=FALSE)
    {
        $transaction = db_transaction();
        try
        {
            //Get the project ID now before we delete the records!
            $projectid = $this->m_oMapHelper->getProjectIDForUsecase($usecaseid);
            
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            //Remove all the links
            db_delete(DatabaseNamesHelper::$m_map_workitem2usecase_tablename)
              ->condition('usecaseid', $usecaseid)
              ->execute(); 
            //Now remove the usecase
            if($just_mark_not_active)
            {
                $w_fields = array(
                      'active_yn' =>0,
                  );
                $w_qry = db_update(DatabaseNamesHelper::$m_usecase_tablename)
                    ->fields($w_fields)
                        ->condition('id',$usecaseid);
                $w_qry->execute();            
                $this->markProjectUpdated($projectid, "deactivated usecase#$usecaseid");
            } else {
                db_delete(DatabaseNamesHelper::$m_usecase_tablename)
                  ->condition('id', $usecaseid)
                  ->execute(); 
                $this->markProjectUpdated($projectid, "deleted usecase#$usecaseid");
            }
            
            //If we are here then we had success.
            $msg = "Deleted usecase#$usecaseid";
            $resultbundle = array('usecaseid'=>$usecaseid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to delete usecase#' . $usecaseid
                      . " because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Delete the test case from the database.
     */
    function deleteTestcase($testcaseid, $just_mark_not_active=FALSE)
    {
        $transaction = db_transaction();
        try
        {
            //Get the project ID now before we delete the records!
            $projectid = $this->m_oMapHelper->getProjectIDForTestcase($testcaseid);
            
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            //Remove all the links
            db_delete(DatabaseNamesHelper::$m_map_workitem2testcase_tablename)
              ->condition('testcaseid', $testcaseid)
              ->execute(); 
            db_delete(DatabaseNamesHelper::$m_testcasestep_tablename)
              ->condition('testcaseid', $testcaseid)
              ->execute(); 
            
            //Now remove the testcase
            if($just_mark_not_active)
            {
                $w_fields = array(
                      'active_yn' =>0,
                  );
                $w_qry = db_update(DatabaseNamesHelper::$m_testcase_tablename)
                    ->fields($w_fields)
                        ->condition('id',$testcaseid);
                $w_qry->execute();            
                $this->markProjectUpdated($projectid, "deactivated testcase#$testcaseid");
            } else {
                db_delete(DatabaseNamesHelper::$m_testcase_tablename)
                  ->condition('id', $testcaseid)
                  ->execute(); 
                $this->markProjectUpdated($projectid, "deleted testcase#$testcaseid");
            }
            
            //If we are here then we had success.
            $msg = "Deleted testcase#$testcaseid";
            $resultbundle = array('testcaseid'=>$testcaseid,
                                  'message'=>$msg);
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to delete testcase#' . $testcaseid
                      . " because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Delete the project baseline from the database.
     */
    function deleteProjectBaseline($project_baselineid)
    {
        $transaction = db_transaction();
        try
        {
            //Get the project ID now before we delete the records!
            $projectid = $this->m_oMapHelper->getProjectIDForProjectBaselineID($project_baselineid);
            
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            db_delete(DatabaseNamesHelper::$m_map_wi2wi_history_tablename)
              ->condition('project_baselineid', $project_baselineid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_workitem_history_tablename)
              ->condition('project_baselineid', $project_baselineid)
              ->execute(); 
            
            db_delete(DatabaseNamesHelper::$m_project_baseline_tablename)
              ->condition('id', $project_baselineid)
              ->execute(); 
            
            //If we are here then we had success.
            $msg = "Deleted project baseline#$project_baselineid";
            $resultbundle = array('project_baselineid'=>$project_baselineid,
                                  'message'=>$msg);
            
            error_log("BASELINE DELETION NOTE: Deleted baseline#$project_baselineid for project#$projectid trigged by user#$this_uid");
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to delete project_baseline#' . $project_baselineid
                      . " because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Delete the project baseline from the database.
     */
    function markProjectBaselineDeleted($project_baselineid)
    {
        $transaction = db_transaction();
        try
        {
            //Get the project ID now before we delete the records!
            $projectid = $this->m_oMapHelper->getProjectIDForProjectBaselineID($project_baselineid);
            
            global $user;
            $this_uid = $user->uid;
            
            if(!UserAccountHelper::isAllowedToChangeProjectContent($this_uid, $projectid))
            {
                throw new \Exception("User #$this_uid cannot edit project#$projectid content at this time!");   
            }

            $updated_dt = date("Y-m-d H:i", time());
            $fields['mark_deleted_yn'] = 1;
            $fields['mark_deleted_dt'] = $updated_dt;
            $main_qry = db_update(DatabaseNamesHelper::$m_project_baseline_tablename)
                ->fields($fields)
                    ->condition('id',$project_baselineid,'=');
            $main_qry->execute(); 
            
            //If we are here then we had success.
            $msg = "Deleted project baseline#$project_baselineid";
            $resultbundle = array('project_baselineid'=>$project_baselineid,
                                  'message'=>$msg);
            
            error_log("BASELINE DELETION NOTE: Marked as deleted baseline#$project_baselineid for project#$projectid trigged by user#$this_uid");
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to mark as deleted project_baseline#' . $project_baselineid
                      . " because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Write the values into the database.
     */
    public function createUsecase($projectid, $myvalues, $key_conversion=NULL, $created_by_personid=NULL, $updated_dt=NULL)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            
            $step = 0;
            $stepname = "about to add for created_by_personid=$created_by_personid";
            try
            {
                $usecase_nm = $myvalues['usecase_nm'];
                $step++;
                $stepname = "add core";
                $fields = array(
                      'owner_projectid' => $projectid,
                      'updated_dt' => $updated_dt,
                      'created_dt' => $updated_dt,
                  );
                $fields['status_cd'] = $myvalues['status_cd'];
                $fields['status_set_dt'] = $updated_dt;
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'usecase_nm');
                $this->setIfValueExists($fields, $myvalues, 'blurb_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'perspective_cd');

                $this->setIfValueExists($fields, $myvalues, 'precondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'postcondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'steps_tx');
                $this->setIfValueExists($fields, $myvalues, 'references_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'active_yn');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'importance');
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_tracking_workitemid');
                
                $main_qry = db_insert(DatabaseNamesHelper::$m_usecase_tablename)
                    ->fields($fields);
                $newid = $main_qry->execute(); 

                //Add any new usecase mappings next
                $stepname = "add dep maps";
                $step++;
                if(!empty($myvalues['map_workitem2usecase_tx']))
                {
                    if(is_array($myvalues['map_workitem2usecase_tx']))
                    {
                        $map_workitem2usecase_ar = $myvalues['map_workitem2usecase_tx'];
                    } else {
                        $map_workitem2usecase_ar = explode(',',$myvalues['map_workitem2usecase_tx']);
                    }
                } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                    $map_workitem2usecase_ar = $myvalues['maps']['workitems'];
                } else {
                    $map_workitem2usecase_ar = [];
                }
                foreach($map_workitem2usecase_ar as $workitem_tx)
                {
                    $workitemid = trim($workitem_tx);
                    if(!empty($workitemid))
                    {
                        //Insert
                        db_insert(DatabaseNamesHelper::$m_map_workitem2usecase_tablename)
                          ->fields(array(
                                'workitemid' => $workitemid,
                                'usecaseid' => $newid,
                                'created_by_personid' => $created_by_personid,
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }

                //Add any delegate owners now
                $stepname = "add delegates";
                $step++;
                if(isset($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
                {
                    foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                    {
                        if(!empty($delegate_personid))
                        {
                            db_insert(DatabaseNamesHelper::$m_map_delegate_usecaseowner_tablename)
                              ->fields(array(
                                    'personid' => $delegate_personid,
                                    'usecaseid' => $newid,
                                    'created_by_personid' => $created_by_personid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    }
                }

                //Set tags
                if(isset($myvalues['map_tag2usecase_tx']))
                {
                    $tag_ar = explode(',',$myvalues['map_tag2usecase_tx']);
                    $this->replaceExistingTags($tag_ar, 'usecaseid', $newid, DatabaseNamesHelper::$m_map_tag2usecase_tablename);
                }

                //If we are here then we had success.
                $stepname = "wrap-up";
                $this->markProjectUpdated($projectid, "created use case");
                $resultbundle = array('usecaseid'=>$newid,
                                      'message'=>'Added use case #' . $newid . ': ' . $myvalues['usecase_nm']);
                return $resultbundle;
            }
            catch(\Exception $ex)
            {
                $msg = t('Failed to add ' . $myvalues['usecase_nm']
                          . " goal at step#$step ($stepname) because " . $ex->getMessage());
                error_log("$msg\n" 
                          . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                throw new \Exception($msg, 99910, $ex);
            }
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Update the values in the database for one use case
     */
    public function updateUsecase($usecaseid, $myvalues, $key_conversion=NULL)
    {
        if(empty($usecaseid))
        {
            throw new \Exception("Missing required usecaseid!");
        }
        if($key_conversion === NULL)
        {
            $key_conversion = [];
        }

        $transaction = db_transaction();
        $step = 0;
        global $user;
        $this_uid = $user->uid;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
              
            $existing_core_sql = "SELECT status_cd"
                . " FROM " . DatabaseNamesHelper::$m_usecase_tablename
                . " WHERE id=$usecaseid";
            $existing_core_result = db_query($existing_core_sql);
            $existing_core_record = $existing_core_result->fetchAssoc(); 
            
            $step++;
            
            $fields = array(
                  'updated_dt' => $updated_dt,
              );
            
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');

            $this->setIfValueExistsNotBlank($fields, $myvalues, 'usecase_nm');
            $this->setIfValueExists($fields, $myvalues, 'blurb_tx');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'perspective_cd');

            $this->setIfValueExists($fields, $myvalues, 'precondition_tx');
            $this->setIfValueExists($fields, $myvalues, 'postcondition_tx');
            $this->setIfValueExists($fields, $myvalues, 'steps_tx');
            $this->setIfValueExists($fields, $myvalues, 'references_tx');
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'active_yn');

            $this->setIfValueExistsNotBlank($fields, $myvalues, 'importance');
            
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_tracking_workitemid');  //TODO confirm this is in the project
                
            if(!empty($myvalues['status_cd']) && $existing_core_record['status_cd'] != $myvalues['status_cd'])
            {
                //Make sure we update the date when we change the status code
                $fields['status_cd'] = $myvalues['status_cd'];
                $fields['status_set_dt'] = $updated_dt;
            }

            $main_qry = db_update(DatabaseNamesHelper::$m_usecase_tablename)
                ->fields($fields)
                    ->condition('id',$usecaseid,'=');
            $num_updated = $main_qry->execute(); 
            
            //Add any new member mappings next
            $update_wid_map = isset($myvalues['map_workitem2usecase_tx']) 
                    || (isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems']));  //Allow for empty to clear it!
            if(!empty($myvalues['map_workitem2usecase_tx']))
            {
                $update_wid_map = TRUE;
                if(is_array($myvalues['map_workitem2usecase_tx']))
                {
                    $map_workitem2usecase_ar = $myvalues['map_workitem2usecase_tx'];
                } else {
                    $map_workitem2usecase_ar = explode(',',$myvalues['map_workitem2usecase_tx']);
                }
            } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                $update_wid_map = TRUE;
                $map_workitem2usecase_ar = $myvalues['maps']['workitems'];
            } else {
                $map_workitem2usecase_ar = [];
            }
            if($update_wid_map)
            {
                //Delete all existing parent goal links first
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_workitem2usecase_tablename)
                  ->condition('usecaseid', $usecaseid)
                  ->execute(); 
                if(!empty($map_workitem2usecase_ar) && is_array($map_workitem2usecase_ar))
                {
                    $step++;
                    foreach($map_workitem2usecase_ar as $workitem_tx)
                    {
                        $workitemid = trim($workitem_tx);
                        if(!empty($workitemid) && is_numeric($workitemid))
                        {
                            //Insert
                            db_insert(DatabaseNamesHelper::$m_map_workitem2usecase_tablename)
                              ->fields(array(
                                    'workitemid' => $workitemid,
                                    'usecaseid' => $usecaseid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    }
                }
            }            
            
            //Delete if already existing delegate owner maps first
            if(!empty($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
            {
                //DebugHelper::debugPrintNeatly($myvalues,FALSE,"LOOK IN map_delegate_owner.............................");                
                $step++;
                db_delete(DatabaseNamesHelper::$m_map_delegate_usecaseowner_tablename)
                    ->condition('usecaseid', $usecaseid)
                    ->execute(); 
                $step++;
                foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                {
                    if(!empty($delegate_personid) && $known_owner_personid != $delegate_personid)
                    {
                        db_insert(DatabaseNamesHelper::$m_map_delegate_usecaseowner_tablename)
                          ->fields(array(
                                'personid' => $delegate_personid,
                                'usecaseid' => $usecaseid,
                                'created_by_personid' => $this_uid,
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }
            }
            
            //Replace tags
            if(isset($myvalues['map_tag2usecase_tx']))
            {
                $tag_ar = explode(',',$myvalues['map_tag2usecase_tx']);
                $this->replaceExistingTags($tag_ar, 'usecaseid', $usecaseid, DatabaseNamesHelper::$m_map_tag2usecase_tablename);
            }

            //If we are here then we had success.
            $this->markProjectUpdatedForUsecaseID($usecaseid, "updated usecase");
            if(!isset($myvalues['usecase_nm']))
            {
                $resultbundle = array('usecaseid'=>$usecaseid,
                                      'message'=>'Updated usecase #' . $usecaseid);
            } else {
                $resultbundle = array('usecaseid'=>$usecaseid,
                                      'message'=>'Updated usecase #' . $usecaseid . ': ' . $myvalues['usecase_nm']);
            }
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update #' . $usecaseid
                      . " usecase at step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Write the values into the database.
     */
    public function createTestcase($projectid, $myvalues, $key_conversion=NULL, $created_by_personid=NULL, $updated_dt=NULL)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            if(empty($myvalues['steps_encoded_tx']))
            {
                throw new \Exception("Missing required steps_encoded_tx!");
            }
            $steps_info = json_decode($myvalues['steps_encoded_tx']);

            $steps_sequence_ar = $steps_info->sequence;
            $clean_steps_sequence_ar = [];
            foreach($steps_sequence_ar as $one_step)
            {
                $d = $one_step->d;
                $e = $one_step->e;
                if(strlen(trim($d)) > 0)
                {
                    $clean_steps_sequence_ar[] = array('d'=>trim($d),'e'=>trim($e));
                }
            }
            if(count($clean_steps_sequence_ar) == 0)
            {
                throw new \Exception("Must have at least one non-empty step!");
            }
            $codestep = 0;
            $codestepname = "about to addnew created_by_personid=$created_by_personid";
            try
            {
                $testcase_nm = $myvalues['testcase_nm'];
                $codestep++;
                $codestepname = "add core";
                $fields = array(
                      'owner_projectid' => $projectid,
                      'updated_dt' => $updated_dt,
                      'created_dt' => $updated_dt,
                  );
                $fields['status_cd'] = $myvalues['status_cd'];
                $fields['status_set_dt'] = $updated_dt;
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'testcase_nm');
                $this->setIfValueExists($fields, $myvalues, 'blurb_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'perspective_cd');

                $this->setIfValueExists($fields, $myvalues, 'precondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'postcondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'steps_tx');
                $this->setIfValueExists($fields, $myvalues, 'references_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'active_yn');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'importance');
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_tracking_workitemid');
                
                $main_qry = db_insert(DatabaseNamesHelper::$m_testcase_tablename)
                    ->fields($fields);
                $newid = $main_qry->execute(); 

                //Add any new testcase mappings next
                $codestepname = "add dep maps";
                $codestep++;
                if(!empty($myvalues['map_workitem2testcase_tx']))
                {
                    if(is_array($myvalues['map_workitem2testcase_tx']))
                    {
                        $map_workitem2testcase_ar = $myvalues['map_workitem2testcase_tx'];
                    } else {
                        $map_workitem2testcase_ar = explode(',',$myvalues['map_workitem2testcase_tx']);
                    }
                } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                    $map_workitem2testcase_ar = $myvalues['maps']['workitems'];
                } else {
                    $map_workitem2testcase_ar = [];
                }
                foreach($map_workitem2testcase_ar as $workitem_tx)
                {
                    $workitemid = trim($workitem_tx);
                    if(!empty($workitemid))
                    {
                        //Insert
                        db_insert(DatabaseNamesHelper::$m_map_workitem2testcase_tablename)
                          ->fields(array(
                                'workitemid' => $workitemid,
                                'testcaseid' => $newid,
                                'created_by_personid' => $created_by_personid,
                                'created_dt' => $updated_dt,
                            ))
                              ->execute(); 
                    }
                }
                $step_num=0;
                foreach($clean_steps_sequence_ar as $one_step)
                {
                    //Insert
                    $step_num++;
                    db_insert(DatabaseNamesHelper::$m_testcasestep_tablename)
                      ->fields(array(
                            'step_num' => $step_num,
                            'testcaseid' => $newid,
                            'instruction_tx' => $one_step['d'],
                            'expectation_tx' => $one_step['e'],
                            'status_cd' => 'NONE',
                            'updated_dt' => $updated_dt,
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }

                //Add any delegate owners now
                $codestepname = "add delegates";
                $codestep++;
                if(isset($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
                {
                    foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                    {
                        if(!empty($delegate_personid))
                        {
                            db_insert(DatabaseNamesHelper::$m_map_delegate_testcaseowner_tablename)
                              ->fields(array(
                                    'personid' => $delegate_personid,
                                    'testcaseid' => $newid,
                                    'created_by_personid' => $created_by_personid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    }
                }

                //Set tags
                if(isset($myvalues['map_tag2testcase_tx']))
                {
                    $tag_ar = explode(',',$myvalues['map_tag2testcase_tx']);
                    $this->replaceExistingTags($tag_ar, 'testcaseid', $newid, DatabaseNamesHelper::$m_map_tag2testcase_tablename);
                }
                
                //If we are here then we had success.
                $codestepname = "wrap-up";
                $this->markProjectUpdated($projectid, "created test case");
                $resultbundle = array('testcaseid'=>$newid,
                                      'message'=>'Added test case #' . $newid . ': ' . $myvalues['testcase_nm']);
                return $resultbundle;
            }
            catch(\Exception $ex)
            {
                $msg = t('Failed to add ' . $myvalues['testcase_nm']
                          . " goal at step#$codestep ($codestepname) because " . $ex->getMessage());
                error_log("$msg\n" 
                          . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                throw new \Exception($msg, 99910, $ex);
            }
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Update the values in the database for one test case
     */
    public function updateTestcase($testcaseid, $myvalues, $key_conversion=NULL)
    {
        $transaction = db_transaction();
        try 
        {
            if(empty($testcaseid))
            {
                throw new \Exception("Missing required testcaseid!");
            }
            if($key_conversion === NULL)
            {
                $key_conversion = [];
            }
            if(!isset($myvalues['steps_encoded_tx']))
            {
                $update_the_steps = FALSE;
            } else {
                //Update the steps
                if(empty($myvalues['steps_encoded_tx']))
                {
                    throw new \Exception("Missing required steps_encoded_tx!");
                }
                $steps_info = json_decode($myvalues['steps_encoded_tx']);
                $steps_sequence_ar = $steps_info->sequence;
                $clean_steps_sequence_ar = [];
                foreach($steps_sequence_ar as $one_step)
                {
                    $id = $one_step->id;
                    $d = $one_step->d;
                    $e = $one_step->e;
                    $cd = $one_step->cd;
                    if(strlen(trim($d)) > 0)
                    {
                        $clean_steps_sequence_ar[] = array('id'=>$id,'d'=>trim($d),'e'=>trim($e),'cd'=>$cd);
                    }
                }
                if(count($clean_steps_sequence_ar) == 0)
                {
                    throw new \Exception("Must have at least one non-empty step!");
                }
                $update_the_steps = TRUE;
            }
            $codestep = 0;
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            try
            {

                $existing_core_sql = "SELECT status_cd"
                    . " FROM " . DatabaseNamesHelper::$m_testcase_tablename
                    . " WHERE id=$testcaseid";
                $existing_core_result = db_query($existing_core_sql);
                $existing_core_record = $existing_core_result->fetchAssoc(); 

                $codestep++;

                $fields = array(
                      'updated_dt' => $updated_dt,
                  );
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'owner_personid');

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'testcase_nm');
                $this->setIfValueExists($fields, $myvalues, 'blurb_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'perspective_cd');

                $this->setIfValueExists($fields, $myvalues, 'precondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'postcondition_tx');
                $this->setIfValueExists($fields, $myvalues, 'steps_tx');
                $this->setIfValueExists($fields, $myvalues, 'references_tx');
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'active_yn');

                $this->setIfValueExistsNotBlank($fields, $myvalues, 'importance');
                
                $this->setIfValueExistsNotBlank($fields, $myvalues, 'effort_tracking_workitemid');

                if(!empty($myvalues['status_cd']) && $existing_core_record['status_cd'] != $myvalues['status_cd'])
                {
                    //Make sure we update the date when we change the status code
                    $fields['status_cd'] = $myvalues['status_cd'];
                    $fields['status_set_dt'] = $updated_dt;
                }
                $main_qry = db_update(DatabaseNamesHelper::$m_testcase_tablename)
                    ->fields($fields)
                        ->condition('id',$testcaseid,'=');
                $main_qry->execute(); 

                //Add any new member mappings next
                $codestep++;
                $update_wid_map = isset($myvalues['map_workitem2testcase_tx']) 
                        || (isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems']));  //Allow for empty to clear it!
                if(!empty($myvalues['map_workitem2testcase_tx']))
                {
                    $update_wid_map = TRUE;
                    if(is_array($myvalues['map_workitem2testcase_tx']))
                    {
                        $map_workitem2testcase_ar = $myvalues['map_workitem2testcase_tx'];
                    } else {
                        $map_workitem2testcase_ar = explode(',',$myvalues['map_workitem2testcase_tx']);
                    }
                } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                    $update_wid_map = TRUE;
                    $map_workitem2testcase_ar = $myvalues['maps']['workitems'];
                } else {
                    $map_workitem2testcase_ar = [];
                }

                if($update_the_steps)
                {
                    $step_num=0;
                    $existing_stepid_ar = [];
                    foreach($clean_steps_sequence_ar as $one_step)
                    {
                        //Insert
                        $step_num++;
                        $stepid = $one_step['id'];
                        if(empty($stepid) || $stepid === 'null') //Check for the text too
                        {
                            //Insert a new record
                            $fields_ar = array(
                                    'step_num' => $step_num,
                                    'testcaseid' => $testcaseid,
                                    'instruction_tx' => $one_step['d'],
                                    'expectation_tx' => $one_step['e'],
                                    'status_cd' => $one_step['cd'],
                                    'updated_dt' => $updated_dt,
                                    'created_dt' => $updated_dt,
                                  );

                            $new_stepid = db_insert(DatabaseNamesHelper::$m_testcasestep_tablename)
                                ->fields($fields_ar)
                                  ->execute(); 
                            $existing_stepid_ar[] = $new_stepid;
                        } else {
                            //Update existing record
                            $existing_stepid_ar[] = $stepid;
                            $fields_ar = array(
                                    'step_num' => $step_num,
                                    'testcaseid' => $testcaseid,
                                    'instruction_tx' => $one_step['d'],
                                    'expectation_tx' => $one_step['e'],
                                    'status_cd' => $one_step['cd'],
                                    'updated_dt' => $updated_dt,
                                  );

                            db_update(DatabaseNamesHelper::$m_testcasestep_tablename)
                                ->fields($fields_ar)
                                    ->condition('id',$stepid,'=')
                                        ->execute();
                                    ;
                        }
                    }
                    //Delete all other steps now
                    $codestep++;
                    $existing_stepid_tx = implode(",", $existing_stepid_ar);
                    $delete_steps_sql = "DELETE FROM " . DatabaseNamesHelper::$m_testcasestep_tablename
                            . " WHERE testcaseid=$testcaseid and id NOT IN ($existing_stepid_tx)";
                    db_query($delete_steps_sql)->execute();
                }
                
                if($update_wid_map)
                {
                    //Delete all existing parent goal links first
                    $codestep++;
                    db_delete(DatabaseNamesHelper::$m_map_workitem2testcase_tablename)
                      ->condition('testcaseid', $testcaseid)
                      ->execute(); 
                    if(!empty($map_workitem2testcase_ar) && is_array($map_workitem2testcase_ar))
                    {
                        $codestep++;
                        foreach($map_workitem2testcase_ar as $workitem_tx)
                        {
                            $workitemid = trim($workitem_tx);
                            if(!empty($workitemid) && is_numeric($workitemid))
                            {
                                //Insert
                                db_insert(DatabaseNamesHelper::$m_map_workitem2testcase_tablename)
                                  ->fields(array(
                                        'workitemid' => $workitemid,
                                        'testcaseid' => $testcaseid,
                                        'created_by_personid' => $this_uid,
                                        'created_dt' => $updated_dt,
                                    ))
                                      ->execute(); 
                            }
                        }
                    }
                }            

                //Delete if already existing delegate owner maps first
                if(!empty($myvalues['map_delegate_owner']) && is_array($myvalues['map_delegate_owner']))
                {
                    //DebugHelper::debugPrintNeatly($myvalues,FALSE,"LOOK IN map_delegate_owner.............................");                
                    $codestep++;
                    db_delete(DatabaseNamesHelper::$m_map_delegate_testcaseowner_tablename)
                        ->condition('testcaseid', $testcaseid)
                        ->execute(); 
                    $codestep++;
                    foreach($myvalues['map_delegate_owner'] as $delegate_personid)
                    {
                        if(!empty($delegate_personid) && $known_owner_personid != $delegate_personid)
                        {
                            db_insert(DatabaseNamesHelper::$m_map_delegate_testcaseowner_tablename)
                              ->fields(array(
                                    'personid' => $delegate_personid,
                                    'testcaseid' => $testcaseid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                        }
                    }
                }

                //Set the tags
                if(isset($myvalues['map_tag2testcase_tx']))
                {
                    $map_tag2testcase_ar = explode(',',$myvalues['map_tag2testcase_tx']);
                    $this->replaceExistingTags($map_tag2testcase_ar, 'testcaseid', $testcaseid, DatabaseNamesHelper::$m_map_tag2testcase_tablename);
                }

                //If we are here then we had success.
                $this->markProjectUpdatedForTestcaseID($testcaseid, "updated testcase");
                if(!isset($myvalues['testcase_nm']))
                {
                    $resultbundle = array('testcaseid'=>$testcaseid,
                                          'message'=>'Updated testcase #' . $testcaseid);
                } else {
                    $resultbundle = array('testcaseid'=>$testcaseid,
                                          'message'=>'Updated testcase #' . $testcaseid . ': ' . $myvalues['testcase_nm']);
                }
                return $resultbundle;
            }
            catch(\Exception $ex)
            {
                $msg = t('Failed to update #' . $testcaseid
                          . " testcase at codestep#$codestep because " . $ex->getMessage());
                error_log("$msg\n" 
                          . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
                throw new \Exception($msg, 99910, $ex);
            }
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function replaceExistingTags($tags_ar, $keyname, $keyvalue, $tablename)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            //Delete all existing tags
            db_delete($tablename)
              ->condition($keyname,$keyvalue,'=')
              ->execute();

            //Add tags
            $deduped_ar = [];
            foreach($tags_ar as $tag_tx)
            {
                $clean_tag_tx = strtoupper(trim($tag_tx));
                $deduped_ar[$clean_tag_tx] = $clean_tag_tx;
            }
            foreach($deduped_ar as $clean_tag_tx)
            {
                //Insert
                db_insert($tablename)
                  ->fields(array(
                        'tag_tx' => $clean_tag_tx,
                        $keyname => $keyvalue,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $updated_dt,
                    ))
                      ->execute(); 
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update the values in the database for one use case
     */
    public function updateProjectBaseline($project_baselineid, $myvalues, $key_conversion=NULL)
    {
        if(empty($project_baselineid))
        {
            throw new \Exception("Missing required project_baselineid!");
        }
        if($key_conversion === NULL)
        {
            $key_conversion = [];
        }

        $transaction = db_transaction();
        $step = 0;
        global $user;
        $this_uid = $user->uid;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
              
            $existing_core_sql = "SELECT *"
                . " FROM " . DatabaseNamesHelper::$m_project_baseline_tablename
                . " WHERE id=$project_baselineid";
            $existing_core_result = db_query($existing_core_sql);
            $existing_core_record = $existing_core_result->fetchAssoc(); 
            
            $step++;
            
            $fields = array(
                  'updated_by_personid' => $this_uid,
                  'updated_dt' => $updated_dt,
              );
            
            $this->setIfValueExistsNotBlank($fields, $myvalues, 'shortname');
            $this->setIfValueExists($fields, $myvalues, 'comment_tx');
            
            $main_qry = db_update(DatabaseNamesHelper::$m_project_baseline_tablename)
                ->fields($fields)
                    ->condition('id',$project_baselineid,'=');
            $num_updated = $main_qry->execute(); 

            //If we are here then we had success.
            if(!isset($myvalues['shortname']))
            {
                $resultbundle = array('project_baselineid'=>$project_baselineid,
                                      'message'=>'Updated project_baseline #' . $project_baselineid);
            } else {
                $resultbundle = array('project_baselineid'=>$project_baselineid,
                                      'message'=>'Updated project_baseline #' . $project_baselineid . ': ' . $myvalues['shortname']);
            }
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to update #' . $project_baselineid
                      . " project_baseline at step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Write the values into the database.
     */
    public function createProjectCommunication($myvalues)
    {
        return $this->createCommunication('project', $myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function createWorkitemCommunication($myvalues)
    {
        return $this->createCommunication('workitem', $myvalues);
    }

    /**
     * Write the values into the database.
     */
    public function createSprintCommunication($myvalues)
    {
        return $this->createCommunication('sprint', $myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function createTestcaseCommunication($myvalues)
    {
        return $this->createCommunication('testcase', $myvalues);
    }
    
    
    /**
     * Write the comment values into the database.
     */
    private function createCommunication($contextname, $myvalues)
    {
        $transaction = db_transaction();
        $keyname = $contextname.'id';
        $steps_tablename = NULL;
        if($contextname == 'workitem')
        {
            $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
        } else
        if($contextname == 'project')
        {
            $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
        } else
        if($contextname == 'sprint')
        {
            $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
        } else
        if($contextname == 'testcase')
        {
            $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
            $steps_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
        } else {
            throw new \Exception("No support for context '$contextname' in create function");
        }
        
        $parent_comid = isset($myvalues['parent_comid']) ? $myvalues['parent_comid'] : NULL;
        $subject_itemid = isset($myvalues[$keyname]) ? $myvalues[$keyname] : NULL;
        if(empty($parent_comid) && empty($subject_itemid))
        {
            throw new \Exception("The keys '$keyname' and 'parent_comid' cannot both be missing!  Failed createComment for ". print_r($myvalues,TRUE));
        }
        $step = 0;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $step++;
            //First get the file blobs, if there are any
            $fileblobs_ar = $this->getFileBlobsFromArray($myvalues, 'attachments');
            
            $step++;
            $fields = array(
                $keyname => $myvalues[$keyname],
                'status_cd_at_time_of_com' => $myvalues['status_cd_at_time_of_com'],
                'title_tx' => $myvalues['title_tx'],
                'body_tx' => $myvalues['body_tx'],
                'owner_personid' => $myvalues['owner_personid'],
                'active_yn' => $myvalues['active_yn'],
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                );
            
            if($myvalues['action_requested_concern'] > '')
            {
                $fields['action_requested_concern'] = $myvalues['action_requested_concern'];
            }
            if($myvalues['action_reply_cd'] > '')
            {
                $fields['action_reply_cd'] = $myvalues['action_reply_cd'];
            }
            if($myvalues['parent_comid'] > '')
            {
                $fields['parent_comid'] = $myvalues['parent_comid'];
            }
            $main_qry = db_insert($main_tablename)
                ->fields($fields);
            $newid = $main_qry->execute(); 
            
            if(!empty($steps_tablename) && isset($myvalues['stepnum_list_tx']))
            {
                //Currently only supported by testcase context
                $stepnum_list_ar = UtilityGeneralFormulas::getDelimitedNumbersAsCleanArray($myvalues['stepnum_list_tx']);
                $the_steps = $this->m_oMapHelper->getTestcaseSteps2Detail($subject_itemid);
                foreach($stepnum_list_ar as $stepnum)
                {
                    if(!empty($stepnum))
                    {
                        $detail = $the_steps[$stepnum];
                        $testcasestepid = $detail['id'];
                        $linked_step_fields = array('comid'=>$newid, 'testcasestepid'=>$testcasestepid);
                        $steps_qry = db_insert($steps_tablename)
                            ->fields($linked_step_fields);
                        $steps_qry->execute(); 
                    }
                }
            }
            
            //We write the attachments only AFTER all other writes succeeded!
            $uploaded_uid = $myvalues['owner_personid'];
            $attached = $this->writeFileBlobsToDB($contextname, $fileblobs_ar, $newid, $uploaded_uid);
            $count_attached = count($attached);
            if($count_attached == 1)
            {
                $attachments_markup = " with 1 attached file: " . $attached[0];
            } else if($count_attached > 1) {
                $attachments_markup = " with $count_attached attached files: " . implode(",", $attached);    
            } else {
                $attachments_markup = "";
            }
            
            //If we are here then we had success.
            $resultbundle = array('comid'=>$newid,
                                  'message'=>"Added $contextname communication#" . $newid . $attachments_markup);
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t("Failed to add new $contextname comment because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    private function getChangeCode($ar1,$key1,$ar2,$key2=NULL)
    {
        if($key2 === NULL)
        {
            $key2 = $key1;
        }
        if(!array_key_exists($key2, $ar2))
        {
            return 0;   //Nothing to compare, so treat like no change
        }
        if($ar1[$key1] == $ar2[$key2])
        {
            return 0;   //Did not change
        }
        return 1;   //If we are here, we compared and they are different
    }

    /**
     * Write the values into the database.
     */
    public function createProjectCommunicationHistoryRecord($comid, $newvalues=NULL)
    {
        return $this->createCommunicationHistoryRecord('project',$comid, $newvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function createWorkitemCommunicationHistoryRecord($comid, $newvalues=NULL)
    {
        return $this->createCommunicationHistoryRecord('workitem',$comid, $newvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function createSprintCommentHistoryRecord($comid, $newvalues=NULL)
    {
        return $this->createCommunicationHistoryRecord('sprint',$comid, $newvalues);
    }
    
    /**
     * Write the values into the database.
     */
    private function createCommunicationHistoryRecord($contextname, $comid, $newvalues=NULL)
    {
        if(empty($comid))
        {
            throw new \Exception("Missing the comid!");
        }
        $replaced_dt = date("Y-m-d H:i", time());
        try
        {
            if($contextname == 'workitem')
            {
                $keyfieldname = 'workitemid';
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_history_tablename;
                $myvalues = $this->m_oMapHelper->getOneWorkitemCommunication($comid);
            } else
            if($contextname == 'project')
            {
                $keyfieldname = 'projectid';
                $main_tablename = DatabaseNamesHelper::$m_project_communication_history_tablename;
                $myvalues = $this->m_oMapHelper->getOneProjectCommunication($comid);
            } else
            if($contextname == 'sprint')
            {
                $keyfieldname = 'sprintid';
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_history_tablename;
                $myvalues = $this->m_oMapHelper->getOneSprintComment($comid);
            } else
            if($contextname == 'testcase')
            {
                $keyfieldname = 'testcaseid';
                $main_tablename = DatabaseNamesHelper::$m_testcase_communication_history_tablename;
                $myvalues = $this->m_oMapHelper->getOneTestcaseComment($comid);
            } else {
                throw new \Exception("No support for context '$contextname'");
            }
            $fields = array();
            $fields['original_comid'] = $myvalues['id'];
            $fields['parent_comid'] = $myvalues['parent_comid'];
            $fields[$keyfieldname] = $myvalues[$keyfieldname];
            $fields['status_cd_at_time_of_com'] = $myvalues['status_cd_at_time_of_com'];
            $fields['title_tx'] = $myvalues['title_tx'];
            $fields['body_tx'] = $myvalues['body_tx'];
            $fields['owner_personid'] = $myvalues['owner_personid'];
            $fields['action_requested_concern'] = $myvalues['action_requested_concern'];
            $fields['action_reply_cd'] = $myvalues['action_reply_cd'];
            $fields['active_yn'] = $myvalues['active_yn'];
            $fields['original_updated_dt'] = $myvalues['updated_dt'];
            $fields['original_created_dt'] = $myvalues['created_dt'];        
            $fields['replaced_dt'] = $replaced_dt;
            if($newvalues !== NULL)
            {
                if(array_key_exists('record_removed_yn', $newvalues))
                {
                    $fields['record_removed_yn'] = $newvalues['record_removed_yn'];   
                }
                if(array_key_exists('change_owner_personid', $newvalues))
                {
                    $fields['change_owner_personid'] = $newvalues['change_owner_personid'];   
                }
                $fields['changed_status_cd_at_time_of_com'] = $this->getChangeCode($myvalues,'status_cd_at_time_of_com',$newvalues);
                $fields['changed_title_tx'] = $this->getChangeCode($myvalues,'title_tx',$newvalues);
                $fields['changed_body_tx'] = $this->getChangeCode($myvalues,'body_tx',$newvalues);
                $fields['changed_action_requested_concern'] = $this->getChangeCode($myvalues,'action_requested_concern',$newvalues);
                $fields['changed_action_reply_cd'] = $this->getChangeCode($myvalues,'action_reply_cd',$newvalues);
                $fields['changed_active_yn'] = $this->getChangeCode($myvalues,'active_yn',$newvalues);
                
                $count_fileattached = isset($newvalues['attachments']) && is_array($newvalues['attachments']) ? count($newvalues['attachments']) : 0;
                $fields['num_attachments_added'] = $count_fileattached;
                
                $fileremovals = isset($newvalues['fileremovals']) ? $newvalues['fileremovals'] : array();
                $count_fileremoved=0;
                foreach($fileremovals as $aid)
                {
                    if(!empty($aid))
                    {
                        $count_fileremoved++;
                    }
                }
                $fields['num_attachments_removed'] = $count_fileremoved;
            }
            
            $main_qry = db_insert($main_tablename)
                ->fields($fields);
            $newid = $main_qry->execute(); 
            
            //If we are here then we had success.
            $resultbundle = array('id'=>$newid,
                                  'message'=>"Added history $contextname communication#" . $newid);
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $msg = t("Failed to create history for communication#$comid because " . $ex->getMessage());
            error_log("$msg");
            throw new \Exception($msg, 99910, $ex);
        }
    }

    public function deleteWorkitemCommunication($matchcomid,$uid,$soft_delete=TRUE)
    {
        return $this->deleteCommunication('workitem',$matchcomid,$uid,$soft_delete);
    }
    
    public function deleteProjectCommunication($matchcomid,$uid,$soft_delete=TRUE)
    {
        return $this->deleteCommunication('project',$matchcomid,$uid,$soft_delete);
    }
    
    public function deleteSprintCommunication($matchcomid,$uid,$soft_delete=TRUE)
    {
        return $this->deleteCommunication('sprint',$matchcomid,$uid,$soft_delete);
    }
    
    public function deleteTestcaseCommunication($matchcomid,$uid,$soft_delete=TRUE)
    {
        return $this->deleteCommunication('testcase',$matchcomid,$uid,$soft_delete);
    }
    
    private function deleteCommunication($contextname,$matchcomid,$uid,$soft_delete=TRUE)
    {
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            $steps_tablename = NULL;
            if($contextname == 'workitem')
            {
                $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
            } else
            if($contextname == 'project')
            {
                $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
            } else
            if($contextname == 'sprint')
            {
                $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
            } else
            if($contextname == 'testcase')
            {
                $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
                $steps_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
            } else {
                throw new \Exception("No support for context '$contextname' in delete function");
            }
            if(!$soft_delete)
            {
                $newvalues = array(
                    'change_owner_personid'=>$uid,
                    'record_removed_yn'=>1,
                );
                $this->createCommunicationHistoryRecord($contextname, $matchcomid, $newvalues);
                //Really remove this
                db_delete($main_tablename)
                  ->condition('id', $matchcomid)
                  ->execute();
                if(!empty($steps_tablename))// && isset($myvalues['stepnum_list_tx']))
                {
                    //Currently only supported by testcase context
                    db_delete($steps_tablename)
                        ->condition('comid',$matchcomid)
                        ->execute();
                }
            } else {
                //Just mark not active
                $fields = array(
                    'owner_personid' => $uid,
                    'active_yn' => 0,
                    'updated_dt' => $updated_dt,
                    );
                $newvalues = array(
                    'active_yn' => 0,
                    'change_owner_personid'=>$uid,
                    'record_removed_yn'=>0,
                );
                $this->createCommunicationHistoryRecord($contextname, $matchcomid, $newvalues);
                db_update($main_tablename)
                        ->fields($fields)
                        ->condition('id',$matchcomid)
                        ->execute();
            }

            //If we are here then we had success.
            $resultbundle = array('comid'=>$matchcomid,
                                  'message'=>"Deleted $contextname communication#" . $matchcomid);
            return $resultbundle;
        } catch (\Exception $ex) {
            $msg = t("Failed to delete $contextname communication#$matchcomid because " . $ex->getMessage());
            throw new \Exception($msg, 99910, $ex);
        }
    }

    public function getFileBlobsFromArray($myvalues, $key='attachments')
    {
        if(!isset($myvalues[$key]))
        {
            //No new attachments.
            $fileblobs_ar = array();
        } else {
            //We have new attachments.
            if(!is_array($myvalues[$key]))
            {
                throw new \Exception("Expected array of file objects for '$key' in " . print_r($myvalues,TRUE));
            }
            $fileobjects_ar = $myvalues[$key];
            $fileblobs_ar = $this->getPostedFileBlobs($fileobjects_ar);
        }
        return $fileblobs_ar;
    }
    
    private function writeFileBlobsToDB($contextname, $fileblobs_ar, $matchcomid=NULL, $uploaded_uid=NULL)
    {
        if(empty($matchcomid))
        {
            throw new \Exception("No matchcomid was provided for the upload!");
        }
        if(empty($uploaded_uid))
        {
            throw new \Exception("No uploaded_uid was provided for the upload!");
        }
        if($contextname == 'workitem')
        {
            $main_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
        } else
        if($contextname == 'project')
        {
            $main_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
        } else
        if($contextname == 'sprint')
        {
            $main_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
        } else
        if($contextname == 'testcase')
        {
            $main_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
        } else {
            throw new \Exception("No support for context '$contextname' in file blob function");
        }
        try
        {
            //We write the attachments only AFTER all other writes succeeded!
            $attached = array();
            foreach($fileblobs_ar as $fileblobinfo)
            {
                $attached_result = $this->writeFileAttachment($fileblobinfo, $uploaded_uid);
                $attachmentid = $attached_result['attachmentid'];

                db_insert($main_tablename)
                  ->fields(array(
                        'comid' => $matchcomid,
                        'attachmentid' => $attachmentid,
                    ))
                      ->execute(); 
                $attached[] = $fileblobinfo['filename'];                           
            }
            return $attached;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Write the values into the database.
     */
    public function updateWorkitemCommunication($matchcomid,$myvalues)
    {
        return $this->updateCommunication('workitem',$matchcomid,$myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function updateProjectCommunication($matchcomid,$myvalues)
    {
        return $this->updateCommunication('project',$matchcomid,$myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function updateSprintCommunication($matchcomid,$myvalues)
    {
        return $this->updateCommunication('sprint',$matchcomid,$myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    public function updateTestcaseCommunication($matchcomid,$myvalues)
    {
        return $this->updateCommunication('testcase',$matchcomid,$myvalues);
    }
    
    /**
     * Write the values into the database.
     */
    private function updateCommunication($contextname,$matchcomid,$myvalues)
    {
        $transaction = db_transaction();
        $steps_tablename = NULL;
        if($contextname == 'workitem')
        {
            $keyfieldname = 'workitemid';
            $main_tablename = DatabaseNamesHelper::$m_workitem_communication_tablename;
            $attachment_tablename = DatabaseNamesHelper::$m_map_workitem_communication2attachment_tablename;
        } else
        if($contextname == 'project')
        {
            $keyfieldname = 'projectid';
            $main_tablename = DatabaseNamesHelper::$m_project_communication_tablename;
            $attachment_tablename = DatabaseNamesHelper::$m_map_project_communication2attachment_tablename;
        } else
        if($contextname == 'sprint')
        {
            $keyfieldname = 'sprintid';
            $main_tablename = DatabaseNamesHelper::$m_sprint_communication_tablename;
            $attachment_tablename = DatabaseNamesHelper::$m_map_sprint_communication2attachment_tablename;
        } else
        if($contextname == 'testcase')
        {
            $keyfieldname = 'testcaseid';
            $main_tablename = DatabaseNamesHelper::$m_testcase_communication_tablename;
            $attachment_tablename = DatabaseNamesHelper::$m_map_testcase_communication2attachment_tablename;
            $steps_tablename = DatabaseNamesHelper::$m_map_testcase_communication2testcasestep_tablename;
        } else {
            throw new \Exception("No support for context '$contextname'");
        }
        $updated_dt = date("Y-m-d H:i", time());
        try
        {

            //First get the file blobs, if there are any
            $fileblobs_ar = $this->getFileBlobsFromArray($myvalues, 'attachments');

            $sql_existing = "select * from $main_tablename where id=$matchcomid";
            $existing_result = db_query($sql_existing);
            $existing_values = $existing_result->fetchAssoc();
            $subject_itemid = $existing_values[$keyfieldname];
            
            //Now capture the history
            $this->createCommunicationHistoryRecord($contextname, $matchcomid, $myvalues);
            $fields = array(
                $keyfieldname => $existing_values[$keyfieldname],
                'status_cd_at_time_of_com' => $existing_values['status_cd_at_time_of_com'],
                'title_tx' => $existing_values['title_tx'],
                'body_tx' => $existing_values['body_tx'],
                'owner_personid' => $existing_values['owner_personid'],
                'active_yn' => $existing_values['active_yn'],
                'updated_dt' => $updated_dt,
                'created_dt' => $existing_values['created_dt'],
                );

            if($myvalues['status_cd_at_time_of_com'] > '')
            {
                $fields['status_cd_at_time_of_com'] = $myvalues['status_cd_at_time_of_com'];
            }
            if($myvalues['title_tx'] > '')
            {
                $fields['title_tx'] = $myvalues['title_tx'];
            }
            if($myvalues['body_tx'] > '')
            {
                $fields['body_tx'] = $myvalues['body_tx'];
            }
            if($myvalues['owner_personid'] > '')
            {
                $fields['owner_personid'] = $myvalues['owner_personid'];
            }
            if($myvalues['active_yn'] > '')
            {
                $fields['active_yn'] = $myvalues['active_yn'];
            }
            
            if($myvalues['action_requested_concern'] > '')
            {
                $fields['action_requested_concern'] = $myvalues['action_requested_concern'];
            }
            if(!empty($myvalues['action_reply_cd']))
            {
                $fields['action_reply_cd'] = $myvalues['action_reply_cd'];
            } else {
                $fields['action_reply_cd'] = NULL;
            }
            if($myvalues['parent_comid'] > '')
            {
                $fields['parent_comid'] = $myvalues['parent_comid'];
            }
            db_update($main_tablename)
                    ->fields($fields)
                    ->condition('id',$matchcomid)
                    ->execute();

            if(!empty($steps_tablename) && isset($myvalues['stepnum_list_tx']))
            {
                //Currently only supported by testcase context
                $count_removed = db_delete($steps_tablename)
                    ->condition('comid',$matchcomid)
                    ->execute();

                //Insert the current values
                $stepnum_list_ar = UtilityGeneralFormulas::getDelimitedNumbersAsCleanArray($myvalues['stepnum_list_tx']);
                $the_steps = $this->m_oMapHelper->getTestcaseSteps2Detail($subject_itemid);
                foreach($stepnum_list_ar as $stepnum)
                {
                    $detail = $the_steps[$stepnum];
                    $testcasestepid = $detail['id'];
                    $linked_step_fields = array('comid'=>$matchcomid, 'testcasestepid'=>$testcasestepid);
                    $steps_qry = db_insert($steps_tablename)
                        ->fields($linked_step_fields);
                    $steps_qry->execute(); 
                }
            }

            //Remove attachments marked for deletion
            $fileremovals = isset($myvalues['fileremovals']) ? $myvalues['fileremovals'] : array();
            $remove_attachmentids = array();
            foreach($fileremovals as $aid)
            {
                if(!empty($aid))
                {
                    $remove_attachmentids[$aid] = $aid;
                }
            }
            $count_removed = 0;
            if(count($remove_attachmentids) > 0)
            {
                $count_removed = db_delete($attachment_tablename)
                    ->condition('comid',$matchcomid)
                    ->condition('attachmentid',$remove_attachmentids,'IN')
                    ->execute();
            }
            
            //We write the attachments only AFTER all other writes succeeded!
            $uploaded_uid = isset($myvalues['owner_personid']) ? $myvalues['owner_personid'] : $existing_values['owner_personid'];
            $attached = $this->writeFileBlobsToDB($contextname,$fileblobs_ar, $matchcomid, $uploaded_uid);
            if($count_removed > 0)
            {
                if($count_removed == 1)
                {
                    $attachments_markup = " and removed 1 attachment";    
                } else {
                    $attachments_markup = " and removed $count_attached attachments";    
                }
            }
            $count_attached = count($attached);
            if($count_attached == 1)
            {
                $attachments_markup = " and attached 1 file: " . $attached[0];
            } else if($count_attached > 1) {
                $attachments_markup = " and attached $count_attached files: " . implode(",", $attached);    
            } else {
                $attachments_markup = "";
            }
            
            //If we are here then we had success.
            $resultbundle = array('comid'=>$matchcomid,
                                  'message'=>"Updated $contextname communication#" . $matchcomid . $attachments_markup);
            return $resultbundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            $msg = t("Failed to update $contextname communication#$matchcomid because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    /**
     * Get the blob and meta details of each posted file object in array
     */
    public function getPostedFileBlobs($fileobj_ar)
    {
        try
        {
            $existing_names = array();  //So we skip redundant file uploads
            $details = array();
            foreach($fileobj_ar as $fileobj)
            {
                $onefile = array();
                $onefile['file']=$fileobj;
                $filename = $fileobj->filename;
                if(!array_key_exists($filename, $existing_names))
                {
                    $existing_names[$filename] = $filename;
                    $onefile['filename'] = $filename;
                    $fileinfo = pathinfo($onefile['filename']);
                    $onefile['filetype'] = strtoupper($fileinfo['extension']);
                    $onefile['filesize']=$fileobj->filesize;
                    $onefile['fid'] = $fileobj->fid;

                    //Load the raw file blob object
                    $fileuri = $fileobj->uri;
                    $filepath = drupal_realpath($fileuri);
                    $fp      = fopen($filepath, 'r');
                    $rawfilesize = filesize($filepath);
                    $rawcontent = fread($fp, $rawfilesize);
                    $onefile['file_blob'] = $rawcontent; //No need for mysqli_real_escape_string if we bind on write!
                    fclose($fp);
                    $details[] = $onefile;
                }
            }
            return $details;
        } catch (\Exception $ex) {
            error_log("Failed getPostedFileDetails because " . $ex->getMessage());
            throw $ex;
        }
    }
    
    public function writeFileAttachment($fileblobinfo, $uploaded_by_uid=NULL)
    {
        $transaction = db_transaction();
        try
        {
            if(isset($fileblobinfo['filename']))
            {
                $thefilename = $fileblobinfo['filename'];
            } else {
                error_log("Error in writeFileAttachment because NO filename for file of size=".$fileblobinfo['filesize'] 
                        . " Details=" . print_r($fileblobinfo,TRUE));
                throw new \Exception("Cannot attach a file if it has no name!");
            }
            if(isset($fileblobinfo['uploaded_dt']))
            {
                $uploaded_dt = $fileblobinfo['uploaded_dt'];
            } else {
                $uploaded_dt = date("Y-m-d H:i", time());
            }
            if(empty($uploaded_by_uid))
            {
                if(!empty($fileblobinfo['uploaded_by_uid']))
                {
                    $uploaded_by_uid = $fileblobinfo['uploaded_by_uid'];
                } else {
                    throw new \Exception("Cannot attach '$thefilename' file without UID!");
                }
            }
            $main_qry = db_insert(DatabaseNamesHelper::$m_attachment_tablename)
              ->fields(array(
                  'filename' => $thefilename,
                  'filetype' => $fileblobinfo['filetype'],
                  'filesize' => $fileblobinfo['filesize'],
                  'file_blob' => $fileblobinfo['file_blob'],
                  'uploaded_by_uid' => $uploaded_by_uid,
                  'uploaded_dt' => $uploaded_dt,
                ));
            $newid = $main_qry->execute(); 

            //If we are here then we had success.
            $msg = 'Added ' . $thefilename;
            $resultbundle = array('attachmentid'=>$newid,
                                  'message'=>$msg);
            return $resultbundle;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function publishProjectInfo($projectid, $myvalues)
    {
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());

            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            $publishedrefname = trim($myvalues['publishedrefname']);    
            if(empty($publishedrefname))
            {
                throw new \Exception("Missing required publishedrefname!");
            }
            $status_cd = trim($myvalues['status_cd']);    
            if(empty($status_cd))
            {
                throw new \Exception("Missing required status_cd!");
            }
            $status_set_dt = trim($myvalues['status_set_dt']);    
            if(empty($status_set_dt))
            {
                $status_set_dt = trim($myvalues['updated_dt']); 
                if(empty($status_set_dt))
                {
                    throw new \Exception("Missing required status_set_dt!");
                }
            }

            $root_goalid = !empty($myvalues['root_goalid']) ? $myvalues['root_goalid'] : $myvalues['original_root_goalid'];
            $project_nm = !empty($myvalues['project_nm']) ? $myvalues['project_nm'] : $myvalues['root_workitem_nm'];
            
            if(empty(trim($root_goalid)))
            {
                $root_goalid = $myvalues['original_root_goalid'];    
            }

            if(empty($root_goalid))
            {
                throw new \Exception("Missing required root goal id!");
            }
            
            $owner_personid = !empty($myvalues['owner_personid']) ? $myvalues['owner_personid'] : $myvalues['surrogate_owner_personid'];

            $allow_publish_items = $myvalues['allow_publish_items'];
            $allow_publish_item_owner_name_yn = isset($allow_publish_items['allow_publish_item_owner_name_yn']) ? $allow_publish_items['allow_publish_item_owner_name_yn'] : 0;
            $allow_publish_item_onbudget_p_yn = isset($allow_publish_items['allow_publish_item_onbudget_p_yn']) ? $allow_publish_items['allow_publish_item_onbudget_p_yn'] : 0;
            $allow_publish_item_actual_start_dt_yn = isset($allow_publish_items['allow_publish_item_actual_start_dt_yn']) ? $allow_publish_items['allow_publish_item_actual_start_dt_yn'] : 0;
            $allow_publish_item_actual_end_dt_yn = isset($allow_publish_items['allow_publish_item_actual_end_dt_yn']) ? $allow_publish_items['allow_publish_item_actual_end_dt_yn'] : 0;
            
            $pub_fields = array(
                  'project_nm' => $project_nm,
                  'publishedrefname' => $publishedrefname,
                  'projectid' => $projectid,
                  'active_yn' => 1,
                  'status_cd' => $status_cd,
                  'status_set_dt' => $status_set_dt,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            if(!empty($root_goalid))
            {
                $pub_fields['root_goalid'] = $root_goalid;    
            }
            if($allow_publish_item_owner_name_yn)
            {
                $pub_fields['owner_personid'] = $owner_personid;
                $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'project_manager_override_tx');
            }
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'project_nm');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'project_contextid');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'mission_tx');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'planned_start_dt');
            if($allow_publish_item_actual_start_dt_yn)
            {
                $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'actual_start_dt');
            }
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'planned_end_dt');
            if($allow_publish_item_actual_end_dt_yn)
            {
                $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'actual_end_dt');
            }
            if($allow_publish_item_onbudget_p_yn)
            {
                $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'onbudget_p');
                $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'onbudget_u');
            }
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'ontime_p');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'ontime_u');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'comment_tx');
            $this->setIfValueExistsNotBlank($pub_fields, $myvalues, 'status_set_dt');
            
            $main_qry = db_insert(DatabaseNamesHelper::$m_published_project_info_tablename)
                ->fields($pub_fields);
            $pubid = $main_qry->execute(); 
            
            //If we are here then we had success.
            $msg = 'Published latest information of project#' . $projectid;
            
            $resultbundle = array(
                'message'=>$msg,
                'projectid'=>$projectid,
                'pubid'=>$pubid,
            );
            return $resultbundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Write the Template Project values
     */
    public function updateTP($myvalues)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $tpid = $myvalues['id'];
            $root_template_workitemid = !empty($myvalues['root_template_workitemid']) ? $myvalues['root_template_workitemid'] : $myvalues['original_root_template_workitemid'];

            if(empty($root_template_workitemid))
            {
                throw new \Exception("Missing required root goal id!");
            }
            
            $snippet_bundle_head_yn = !empty($myvalues['snippet_bundle_head_yn']) ? $myvalues['snippet_bundle_head_yn'] : 0;
            
            $workitem_fields = [];
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'root_workitem_nm');
            $this->setIfValueExists($workitem_fields, $myvalues, 'status_cd');
            $this->setIfValueExists($workitem_fields, $myvalues, 'chargecode');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'ignore_branch_cost_yn');

            if(count($workitem_fields) > 0)
            {
                $this->updateTW($root_template_workitemid, $workitem_fields);
            }
            
            $proj_fields = array(
                  'root_template_workitemid' => $root_template_workitemid,
                  'template_nm' => $myvalues['template_nm'],
                  'project_contextid' => $myvalues['project_contextid'],
                  'snippet_bundle_head_yn' => $snippet_bundle_head_yn,
                  'submitter_blurb_tx' => $myvalues['submitter_blurb_tx'],
                  'mission_tx' => $myvalues['mission_tx'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            $this->setIfValueExists($proj_fields, $myvalues, 'owner_personid');
            
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_templateid');
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_template_refname');
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_template_updated_dt');
            
            $this->setIfValueExists($proj_fields, $myvalues, 'allow_detail_publish_yn');
            
            db_update(DatabaseNamesHelper::$m_template_project_library_tablename)
                    ->fields($proj_fields)
                    ->condition('id', $tpid,'=')
                    ->execute();
            
            //Add the reference name if there is one
            $publishedrefname = !empty($myvalues['publishedrefname']) ? trim($myvalues['publishedrefname']) : NULL;
            $remote_uri = !empty($myvalues['remote_uri']) ? $myvalues['remote_uri'] : NULL;
            
            if(!empty($publishedrefname))
            {
                $key_ar = array(
                            'template_projectid' => $tpid,
                        );
                $fields_ar = array(
                        'publishedrefname' => $publishedrefname,
                        'remote_uri' => $remote_uri,
                        'template_projectid' => $tpid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $updated_dt,
                      );
                
                db_merge(DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename)
                      ->key($key_ar)
                      ->fields($fields_ar)
                      ->execute();
                
            } else {
                //There is no publish name for this project
                db_delete(DatabaseNamesHelper::$m_map_publishedrefname2tp_tablename)
                  ->condition('template_projectid', $tpid)
                  ->execute(); 
            }
            
            $group_count = 0;
            
            if(empty($myvalues['map_group2project']) || !is_array($myvalues['map_group2tp']))
            {
                //Just delete any mappings if they exist
                db_delete(DatabaseNamesHelper::$m_map_group2tp_tablename)
                  ->condition('template_projectid', $tpid)
                  ->execute(); 
            } else {
                
                $groups_we_want = [];
                foreach($myvalues['map_group2tp'] as $id)
                {
                    $groups_we_want[$id] = $id;
                }
                
                $member_group_sql = "SELECT groupid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_group2tp_tablename
                        . " WHERE template_projectid=$tpid";
                $member_group_result = db_query($member_group_sql);
                $existing_member_group_map = [];
                while($record = $member_group_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $existing_member_group_map[$id] = $id; 
                }            
                $added_groups = [];
                //Add any new group mappings
                foreach($myvalues['map_group2tp'] as $groupid)
                {
                    if($groupid > 0)
                    {
                        if(!array_key_exists($groupid, $existing_member_group_map))
                        {
                            $group_count++;
                            db_insert(DatabaseNamesHelper::$m_map_group2tp_tablename)
                              ->fields(array(
                                    'groupid' => $groupid,
                                    'template_projectid' => $tpid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                            $added_groups[$groupid] = $groupid;
                        }
                    }
                }
                //Remove those that no longer exist
                $delete_groups = [];
                foreach($existing_member_group_map as $groupid)
                {
                    if(!array_key_exists($groupid, $groups_we_want))
                    {
                        $delete_groups[] = $groupid;
                    }
                }
                if(count($delete_groups) > 0)
                {
                    $groups_in = implode(",", $delete_groups);
                    $delete_group_sql = "DELETE FROM " . DatabaseNamesHelper::$m_map_group2tp_tablename
                            . " WHERE template_projectid=$tpid and groupid in ($groups_in)";
                    db_query($delete_group_sql)->execute();
                }
            }

            $role_count = 0;
            if(empty($myvalues['map_role2tp']) || !is_array($myvalues['map_role2tp']))
            {
                //Just delete any mappings if they exist
                db_delete(DatabaseNamesHelper::$m_map_prole2tp_tablename)
                  ->condition('template_projectid', $tpid)
                  ->execute(); 
            } else {
                
                $roles_we_want = [];
                foreach($myvalues['map_role2tp'] as $id)
                {
                    $roles_we_want[$id] = $id;
                }
                
                $member_role_sql = "SELECT roleid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_prole2tp_tablename
                        . " WHERE projectid=$tpid";
                $member_role_result = db_query($member_role_sql);
                $existing_member_role_map = [];
                while($record = $member_role_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $existing_member_role_map[$id] = $id; 
                }            
                $added_roles = [];
                
                //Add any new role mappings
                foreach($myvalues['map_role2tp'] as $roleid)
                {
                    if($roleid > 0)
                    {
                        if(!array_key_exists($roleid, $existing_member_role_map))
                        {
                            $role_count++;
                            db_insert(DatabaseNamesHelper::$m_map_prole2tp_tablename)
                              ->fields(array(
                                    'roleid' => $roleid,
                                    'template_projectid' => $tpid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                            $added_roles[$roleid] = $roleid;
                        }
                    }
                }
                
                //Remove those that no longer exist
                $delete_roles = [];
                foreach($existing_member_role_map as $roleid)
                {
                    if(!array_key_exists($roleid, $roles_we_want))
                    {
                        $delete_roles[] = $roleid;
                    }
                }
                if(count($delete_roles) > 0)
                {
                    $roles_in = implode(",", $delete_roles);
                    $delete_role_sql = "DELETE FROM " . DatabaseNamesHelper::$m_map_prole2tp_tablename
                            . " WHERE projectid=$tpid and roleid in ($roles_in)";
                    db_query($delete_role_sql)->execute();
                }
            }
            
            //If we are here then we had success.
            $msg = 'Updated project template#' . $tpid;
            
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$tpid,
            );
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Updates the project record and relevant parts of the root goal too.
     */
    public function updateProject($myvalues)
    {
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $project_id = $myvalues['id'];
            $root_goalid = !empty($myvalues['root_goalid']) ? $myvalues['root_goalid'] : $myvalues['original_root_goalid'];

            if(empty($root_goalid))
            {
                throw new \Exception("Missing required root goal id!");
            }
            
            $source_type = !empty($myvalues['source_type']) ? $myvalues['source_type'] : 'local';
            $surrogate_yn = !empty($myvalues['surrogate_yn']) ? $myvalues['surrogate_yn'] : 0;
            $template_yn = !empty($myvalues['template_yn']) ? $myvalues['template_yn'] : 0;
            $snippet_bundle_head_yn = !empty($myvalues['snippet_bundle_head_yn']) ? $myvalues['snippet_bundle_head_yn'] : 0;
            $archive_yn = !empty($myvalues['archive_yn']) ? $myvalues['archive_yn'] : 0;
            
            $owner_personid = !empty($myvalues['owner_personid']) ? $myvalues['owner_personid'] : $myvalues['surrogate_owner_personid'];
            
            $workitem_fields = [];
            
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'root_workitem_nm');
            $this->setIfValueExists($workitem_fields, $myvalues, 'status_cd');
            $this->setIfValueExists($workitem_fields, $myvalues, 'planned_start_dt', TRUE);
            $this->setIfValueExists($workitem_fields, $myvalues, 'actual_start_dt', TRUE);
            $this->setIfValueExists($workitem_fields, $myvalues, 'planned_end_dt', TRUE);
            $this->setIfValueExists($workitem_fields, $myvalues, 'actual_end_dt', TRUE);
            $this->setIfValueExists($workitem_fields, $myvalues, 'chargecode');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'limit_branch_effort_hours_cd');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'ignore_branch_cost_yn');

            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'self_allow_dep_overlap_hours');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'self_allow_dep_overlap_pct');
            
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'ant_sequence_allow_overlap_hours');
            $this->setIfValueExistsNotBlank($workitem_fields, $myvalues, 'ant_sequence_allow_overlap_pct');
            
            if(count($workitem_fields) > 0)
            {
                $this->updateWorkitemWithZeroWi2WiChanges($root_goalid, $workitem_fields, FALSE);
            }
            
            $proj_fields = array(
                  'owner_personid' => $owner_personid,
                  'root_goalid' => $root_goalid,
                  'project_contextid' => $myvalues['project_contextid'],
                  'surrogate_yn' => $surrogate_yn,
                  'template_yn' => $template_yn,
                  'snippet_bundle_head_yn' => $snippet_bundle_head_yn,
                  'archive_yn' => $archive_yn,
                  'source_type' => $source_type,
                  'mission_tx' => $myvalues['mission_tx'],
                  'allow_status_publish_yn' => !empty($myvalues['allow_status_publish_yn']) ? $myvalues['allow_status_publish_yn'] : 0,
                  'allow_detail_publish_yn' => !empty($myvalues['allow_detail_publish_yn']) ? $myvalues['allow_detail_publish_yn'] : 0,
                  'allow_refresh_from_remote_yn' => !empty($myvalues['allow_refresh_from_remote_yn']) ? $myvalues['allow_refresh_from_remote_yn'] : 0,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            $this->setIfValueExists($proj_fields, $myvalues, 'active_yn');
            
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_templateid');
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_template_refname');
            $this->setIfValueExists($proj_fields, $myvalues, 'original_source_template_updated_dt');

            $this->setIfValueExists($proj_fields, $myvalues, 'planned_start_dt', TRUE); //DEPRECATE?
            $this->setIfValueExists($proj_fields, $myvalues, 'actual_start_dt', TRUE); //DEPRECATE?
            $this->setIfValueExists($proj_fields, $myvalues, 'planned_end_dt', TRUE); //DEPRECATE?
            $this->setIfValueExists($proj_fields, $myvalues, 'actual_end_dt', TRUE); //DEPRECATE?
            
            $this->setIfValueExists($proj_fields, $myvalues, 'surrogate_ob_p');
            $this->setIfValueExists($proj_fields, $myvalues, 'surrogate_ot_p');
            
            if(isset($myvalues['allow_publish_items']))
            {
                $allow_publish_items = $myvalues['allow_publish_items'];
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_owner_name_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_onbudget_p_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_start_dt_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_end_dt_yn');
            }
            db_update(DatabaseNamesHelper::$m_project_tablename)
                    ->fields($proj_fields)
                    ->condition('id', $project_id,'=')
                    ->execute();
            
            //Add the reference name if there is one
            $publishedrefname = !empty($myvalues['publishedrefname']) ? trim($myvalues['publishedrefname']) : NULL;
            $remote_uri = !empty($myvalues['remote_uri']) ? $myvalues['remote_uri'] : NULL;
            
            if(!empty($publishedrefname))
            {
                $key_ar = array(
                            'projectid' => $project_id,
                        );
                $fields_ar = array(
                        'publishedrefname' => $publishedrefname,
                        'remote_uri' => $remote_uri,
                        'projectid' => $project_id,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $updated_dt,
                      );
                
                db_merge(DatabaseNamesHelper::$m_map_publishedrefname2project_tablename)
                      ->key($key_ar)
                      ->fields($fields_ar)
                      ->execute();
                
            } else {
                //There is no publish name for this project
                db_delete(DatabaseNamesHelper::$m_map_publishedrefname2project_tablename)
                  ->condition('projectid', $project_id)
                  ->execute(); 
            }
            
            $group_count = 0;
            
            if(empty($myvalues['map_group2project']) || !is_array($myvalues['map_group2project']))
            {
                //Just delete any mappings if they exist
                db_delete(DatabaseNamesHelper::$m_map_group2project_tablename)
                  ->condition('projectid', $project_id)
                  ->execute(); 
            } else {
                
                $groups_we_want = [];
                foreach($myvalues['map_group2project'] as $id)
                {
                    $groups_we_want[$id] = $id;
                }
                
                $member_group_sql = "SELECT groupid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_group2project_tablename
                        . " WHERE projectid=$project_id";
                $member_group_result = db_query($member_group_sql);
                $existing_member_group_map = [];
                while($record = $member_group_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $existing_member_group_map[$id] = $id; 
                }            
                $added_groups = [];
                //Add any new group mappings
                foreach($myvalues['map_group2project'] as $groupid)
                {
                    if($groupid > 0)
                    {
                        if(!array_key_exists($groupid, $existing_member_group_map))
                        {
                            $group_count++;
                            db_insert(DatabaseNamesHelper::$m_map_group2project_tablename)
                              ->fields(array(
                                    'groupid' => $groupid,
                                    'projectid' => $project_id,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                            $added_groups[$groupid] = $groupid;
                        }
                    }
                }
                //Remove those that no longer exist
                $delete_groups = [];
                foreach($existing_member_group_map as $groupid)
                {
                    if(!array_key_exists($groupid, $groups_we_want))
                    {
                        $delete_groups[] = $groupid;
                    }
                }
                if(count($delete_groups) > 0)
                {
                    $groups_in = implode(",", $delete_groups);
                    $delete_group_sql = "DELETE FROM " . DatabaseNamesHelper::$m_map_group2project_tablename
                            . " WHERE projectid=$project_id and groupid in ($groups_in)";
                    db_query($delete_group_sql)->execute();
                }
            }

            $role_count = 0;
            if(empty($myvalues['map_role2project']) || !is_array($myvalues['map_role2project']))
            {
                //Just delete any mappings if they exist
                db_delete(DatabaseNamesHelper::$m_map_prole2project_tablename)
                  ->condition('projectid', $project_id)
                  ->execute(); 
            } else {
                
                $roles_we_want = [];
                foreach($myvalues['map_role2project'] as $id)
                {
                    $roles_we_want[$id] = $id;
                }
                
                $member_role_sql = "SELECT roleid as id"
                        . " FROM " . DatabaseNamesHelper::$m_map_prole2project_tablename
                        . " WHERE projectid=$project_id";
                $member_role_result = db_query($member_role_sql);
                $existing_member_role_map = [];
                while($record = $member_role_result->fetchAssoc()) 
                {
                    $id = $record['id'];
                    $existing_member_role_map[$id] = $id; 
                }            
                $added_roles = [];
                //Add any new role mappings
                foreach($myvalues['map_role2project'] as $roleid)
                {
                    if($roleid > 0)
                    {
                        if(!array_key_exists($roleid, $existing_member_role_map))
                        {
                            $role_count++;
                            db_insert(DatabaseNamesHelper::$m_map_prole2project_tablename)
                              ->fields(array(
                                    'roleid' => $roleid,
                                    'projectid' => $project_id,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                ))
                                  ->execute(); 
                            $added_roles[$roleid] = $roleid;
                        }
                    }
                }
                //Remove those that no longer exist
                $delete_roles = [];
                foreach($existing_member_role_map as $roleid)
                {
                    if(!array_key_exists($roleid, $roles_we_want))
                    {
                        $delete_roles[] = $roleid;
                    }
                }
                if(count($delete_roles) > 0)
                {
                    $roles_in = implode(",", $delete_roles);
                    $delete_role_sql = "DELETE FROM " . DatabaseNamesHelper::$m_map_prole2project_tablename
                            . " WHERE projectid=$project_id and roleid in ($roles_in)";
                    db_query($delete_role_sql)->execute();
                }
            }
            
            //If we are here then we had success.
            $msg = 'Updated project#' . $project_id;
            
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$project_id,
            );
            return $resultbundle;
            
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }

    public function createNewTPFromProject($myvalues, $original_root_goalid=NULL)
    {
        try
        {
            //$myvalues['template_yn'] = 1;
            //return $this->createNewProject($myvalues, $original_root_goalid);
            return $this->createNewTP($myvalues, $original_root_goalid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function createNewProjectFromTemplate($myvalues, $root_template_workitemid=NULL)
    {
        try
        {
            if(empty($root_template_workitemid))
            {
                throw new \Exception("Missing required root template workitemid!");
            }
            $placeholder_projectid = 0; //We will set this for real AFTER creating the project
            if(isset($myvalues['root_workitem_nm']) && !empty(trim($myvalues['root_workitem_nm'])))
            {
                $new_workitem_nm = $myvalues['root_workitem_nm'];
            } else {
                $new_workitem_nm = NULL;
            }
            //createWorkitemFromTW($template_workitemid, $new_projectid=NULL, $new_workitem_nm=NULL, $mark_project_updated=TRUE)
            $mark_project_updated = FALSE;
            $newrootbundle = $this->createWorkitemFromTW($root_template_workitemid, $placeholder_projectid, $new_workitem_nm, $mark_project_updated);
            $root_goalid = $newrootbundle['workitemid'];
            $myvalues['root_goalid'] = $root_goalid;
            $myvalues['template_yn'] = 0;
            return $this->createNewProject($myvalues);//, $root_goalid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function createNewProject($myvalues, $original_root_goalid=NULL)
    {
        $newid = NULL;
        $step=0;
        $fields = array();
        $transaction = db_transaction();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $created_new_goal = FALSE;
            $step++;
            $placeholder_projectid = 0; //We will set this for real AFTER creating the project
            $owner_personid = !empty($myvalues['owner_personid']) ? $myvalues['owner_personid'] : $myvalues['surrogate_owner_personid'];

            if(!isset($myvalues['importance']))
            {
                $myvalues['importance'] = 44;
            }
            
            if(!empty($original_root_goalid))
            {
                //We are creating a project with a NEW root goal that is based on an existing goal
                if(isset($myvalues['root_workitem_nm']) && !empty(trim($myvalues['root_workitem_nm'])))
                {
                    $new_workitem_nm = $myvalues['root_workitem_nm'];
                } else {
                    $new_workitem_nm = NULL;
                }
                $newrootbundle = $this->createWorkitemDuplicate($original_root_goalid, $placeholder_projectid, $new_workitem_nm);
                $root_goalid = $newrootbundle['workitemid'];
                $myvalues['root_goalid'] = $root_goalid;
                $created_new_goal = TRUE;
            } else 
            if(!isset($myvalues['root_goalid']) && isset($myvalues['root_workitem_nm']) && trim($myvalues['root_workitem_nm']) > '') 
            {
                //Create the root goal using this myvalues array
                $wi_fields = array(
                      'workitem_basetype' => 'G',
                      'workitem_nm' => $myvalues['root_workitem_nm'],
                      'importance' => $myvalues['importance'],
                      'owner_personid' => $owner_personid,
                      'owner_projectid' => $placeholder_projectid,
                      'active_yn' => $myvalues['active_yn'],
                      'purpose_tx' => $myvalues['purpose_tx'],
                      'status_cd' => $myvalues['status_cd'],
                      'status_set_dt' => $updated_dt,
                      'updated_dt' => $updated_dt,
                      'created_by_personid' => $this_uid,
                      'created_dt' => $updated_dt,
                  );
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'planned_start_dt');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'planned_start_dt_locked_yn');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'actual_start_dt');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'planned_end_dt');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'planned_end_dt_locked_yn');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'actual_end_dt');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'chargecode');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'limit_branch_effort_hours_cd');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'ignore_branch_cost_yn');
                $main_qry = db_insert(DatabaseNamesHelper::$m_workitem_tablename)
                    ->fields($wi_fields);
                $root_goalid = $main_qry->execute(); 
                $myvalues['root_goalid'] = $root_goalid;
                $created_new_goal = TRUE;
            } else {
                //We are creating a project with an existing goal as the root
                $root_goalid = $myvalues['root_goalid'];
            }
            
            if(empty($root_goalid))
            {
                throw new \Exception("Expected a root_goalid for the new project!");
            }
            
            $step++;
            $source_type = !empty($myvalues['source_type']) ? $myvalues['source_type'] : 'local';
            $surrogate_yn = !empty($myvalues['surrogate_yn']) ? $myvalues['surrogate_yn'] : 0;
            
            $proj_fields = array(
                  'owner_personid' => $owner_personid,
                  'root_goalid' => $root_goalid,
                  'project_contextid' => $myvalues['project_contextid'],
                  'surrogate_yn' => $surrogate_yn,
                  'source_type' => $source_type,
                  'active_yn' => $myvalues['active_yn'],
                  'mission_tx' => $myvalues['mission_tx'],
                  'allow_status_publish_yn' => !empty($myvalues['allow_status_publish_yn']) ? $myvalues['allow_status_publish_yn'] : 0,
                  'allow_refresh_from_remote_yn' => !empty($myvalues['allow_refresh_from_remote_yn']) ? $myvalues['allow_refresh_from_remote_yn'] : 0,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'template_author_nm');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'planned_start_dt');
            //$this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'planned_start_dt_locked_yn');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'actual_start_dt');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'planned_end_dt');
            //$this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'planned_end_dt_locked_yn');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'actual_end_dt');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_templateid');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_template_refname');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_template_updated_dt');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'template_yn');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'allow_status_publish_yn');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'allow_detail_publish_yn');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'surrogate_ob_p');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'surrogate_ot_p');
            
            if(isset($myvalues['allow_publish_items']))
            {
                $allow_publish_items = $myvalues['allow_publish_items'];
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_owner_name_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_onbudget_p_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_start_dt_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_end_dt_yn');
            }
            
            $main_qry = db_insert(DatabaseNamesHelper::$m_project_tablename)
                ->fields($proj_fields);
            $newid = $main_qry->execute(); 

            $publishedrefname = !empty($myvalues['publishedrefname']) ? $myvalues['publishedrefname'] : NULL;
            if(!empty($publishedrefname))
            {
                $remote_uri = !empty($myvalues['remote_uri']) ? $myvalues['remote_uri'] : NULL;
                db_insert(DatabaseNamesHelper::$m_map_publishedrefname2project_tablename)
                  ->fields(array(
                        'publishedrefname' => $publishedrefname,
                        'remote_uri' => $remote_uri,
                        'projectid' => $newid,
                        'created_by_personid' => $this_uid,
                        'created_dt' => $updated_dt,
                    ))
                      ->execute(); 
            }
            
            $step++;
            
            //Now update the new goal to indicate proper ownership
            db_update(DatabaseNamesHelper::$m_workitem_tablename)
                ->fields(array(
                  'owner_projectid' => $newid,
                ))
                    ->condition('id', $root_goalid,'=')
                    ->execute();
            
            //Add group mappings next
            $step++;
            $group_count = 0;
            if(isset($myvalues['map_group2project']))
            {
                db_delete(DatabaseNamesHelper::$m_map_group2project_tablename)
                  ->condition('projectid', $newid)
                  ->execute(); 
                if(is_array($myvalues['map_group2project']))
                {
                    foreach($myvalues['map_group2project'] as $groupid)
                    {
                        if($groupid > 0)
                        {
                            $group_count++;
                            $fields = array(
                                    'groupid' => $groupid,
                                    'projectid' => $newid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                );
                            db_insert(DatabaseNamesHelper::$m_map_group2project_tablename)
                              ->fields($fields)
                                  ->execute(); 
                        }
                    }
                }
            }

            //Add role mappings next
            $step++;
            $role_count = 0;
            if(isset($myvalues['map_role2project']))
            {
                db_delete(DatabaseNamesHelper::$m_map_prole2project_tablename)
                  ->condition('projectid', $newid)
                  ->execute(); 
                if(is_array($myvalues['map_role2project']))
                {
                    foreach($myvalues['map_role2project'] as $roleid)
                    {
                        if($roleid > 0)
                        {
                            $role_count++;
                            $fields = array(
                                    'roleid' => $roleid,
                                    'projectid' => $newid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                );
                            db_insert(DatabaseNamesHelper::$m_map_prole2project_tablename)
                              ->fields($fields)
                                  ->execute(); 
                        }
                    }
                }
            }

            //If we are here then we had success.
            if(empty($group_count))
            {
                $msg = 'Added project#' . $newid . " with $role_count declared roles";
            } else {
                $msg = 'Added project#' . $newid . " with $group_count member groups and $role_count declared roles";
            }
            
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$newid,
                'root_goalid'=>$root_goalid,
            );
            $resultbundle['created_new_goal_yn'] = $created_new_goal ? 1 : 0;
            if(isset($myvalues['planned_start_dt_locked_yn']))
            {
                $resultbundle['locked_planned_start_dt'] = $myvalues['planned_start_dt'];
            }
            if(isset($myvalues['planned_end_dt_locked_yn']))
            {
                $resultbundle['locked_planned_end_dt'] = $myvalues['planned_end_dt'];
            }
            
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to add fields=' . print_r($fields,TRUE) 
                    . " with owner " . $myvalues['owner_personid']
                      . " project ($newid) on step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    public function createNewTP($myvalues, $original_root_goalid=NULL)
    {
        
        $newid = NULL;
        $step=0;
        $fields = [];
        $transaction = db_transaction();
        try
        {
            if(empty($myvalues['template_nm']))
            {
                throw new \Exception("Missing required template name! myvalues=".print_r($myvalues,TRUE));
            }
            $template_nm = trim($myvalues['template_nm']);
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $created_new_goal = FALSE;
            $step++;
            $placeholder_template_projectid = 0; //We will set this for real AFTER creating the project
            $owner_personid = !empty($myvalues['owner_personid']) ? $myvalues['owner_personid'] : $myvalues['surrogate_owner_personid'];

            if(!isset($myvalues['importance']))
            {
                $myvalues['importance'] = 44;
            }
            if(!empty($original_root_goalid))
            {
                //We are creating a project with a NEW root goal that is based on an existing goal
                if(isset($myvalues['root_workitem_nm']) && !empty(trim($myvalues['root_workitem_nm'])))
                {
                    $new_workitem_nm = $myvalues['root_workitem_nm'];
                } else {
                    $new_workitem_nm = NULL;
                }
                //$newrootbundle = $this->createWorkitemDuplicate($original_root_goalid, $placeholder_projectid, $new_workitem_nm);
                $newrootbundle = $this->createTWFromWorkitem($original_root_goalid, $placeholder_template_projectid, $new_workitem_nm);
                $root_template_workitemid = $newrootbundle['template_workitemid'];
                $myvalues['root_template_workitemid'] = $root_template_workitemid;
                $created_new_goal = TRUE;
            } else 
            if(isset($myvalues['root_workitem_nm']) && trim($myvalues['root_workitem_nm']) > '') 
            {
                //Create the root goal using this name
                $wi_fields = array(
                      'workitem_basetype' => 'G',
                      'workitem_nm' => $myvalues['root_workitem_nm'],
                      'importance' => $myvalues['importance'],
                      'owner_personid' => $owner_personid,
                      'owner_template_projectid' => $placeholder_template_projectid,
                      'active_yn' => $myvalues['active_yn'],
                      'purpose_tx' => $myvalues['purpose_tx'],
                      'status_cd' => $myvalues['status_cd'],
                      'updated_dt' => $updated_dt,
                      'created_dt' => $updated_dt,
                  );
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'chargecode');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'limit_branch_effort_hours_cd');
                $this->setIfValueExistsNotBlank($wi_fields, $myvalues, 'ignore_branch_cost_yn');
                
                $wb = $this->createWorkitemTemplateRecord($placeholder_template_projectid, $wi_fields);
                if(empty($wb['template_workitemid']))
                {
                    throw new \Exception("Missing required template_workitemid in result! wb=".print_r($wb,TRUE));
                }
                $root_template_workitemid = $wb['template_workitemid'];
                $myvalues['root_template_workitemid'] = $root_template_workitemid;
                $created_new_goal = TRUE;
            } else {
                //We are creating a project with an existing goal as the root
                $root_template_workitemid = $myvalues['root_template_workitemid'];    
            }
            
            if(empty($root_template_workitemid))
            {
                throw new \Exception("Expected a root_goalid for the new project!");
            }
            
            $step++;
            
            $proj_fields = array(
                  'template_nm' => $template_nm,
                  'owner_personid' => $owner_personid,
                  'root_template_workitemid' => $root_template_workitemid,
                  'project_contextid' => $myvalues['project_contextid'],
                  'active_yn' => $myvalues['active_yn'],
                  'mission_tx' => $myvalues['mission_tx'],
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'submitter_blurb_tx');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'template_author_nm');
            
            $main_qry = db_insert(DatabaseNamesHelper::$m_template_project_library_tablename)
                ->fields($proj_fields);
            $newid = $main_qry->execute(); 

            $step++;
            if($created_new_goal)
            {
                //Now update the new goal to indicate ownership
                db_update(DatabaseNamesHelper::$m_template_workitem_tablename)
                    ->fields(array(
                      'owner_template_projectid' => $newid,
                    ))
                        ->condition('id', $root_template_workitemid,'=')
                        ->execute();
            } else {
                //Change all the existing goals projectid values
                $this->m_oWriteHelper->changeProjectTemplateOfWorkitemTemplateBranch($root_template_workitemid, $newid);               
            }

            //Add group mappings next
            $step++;
            $group_count = 0;
            if(isset($myvalues['map_group2project']))
            {
                db_delete(DatabaseNamesHelper::$m_map_group2tp_tablename)
                  ->condition('template_projectid', $newid)
                  ->execute(); 
                if(is_array($myvalues['map_group2project']))
                {
                    foreach($myvalues['map_group2project'] as $groupid)
                    {
                        if($groupid > 0)
                        {
                            $group_count++;
                            $fields = array(
                                    'groupid' => $groupid,
                                    'template_projectid' => $newid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                );
                            db_insert(DatabaseNamesHelper::$m_map_group2tp_tablename)
                              ->fields($fields)
                                  ->execute(); 
                        }
                    }
                }
            }

            //Add role mappings next
            $step++;
            $role_count = 0;
            if(isset($myvalues['map_role2project']))
            {
                db_delete(DatabaseNamesHelper::$m_map_prole2tp_tablename)
                  ->condition('template_projectid', $newid)
                  ->execute(); 
                if(is_array($myvalues['map_role2project']))
                {
                    foreach($myvalues['map_role2project'] as $roleid)
                    {
                        if($roleid > 0)
                        {
                            $role_count++;
                            $fields = array(
                                    'roleid' => $roleid,
                                    'template_projectid' => $newid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                );
                            db_insert(DatabaseNamesHelper::$m_map_prole2tp_tablename)
                              ->fields($fields)
                                  ->execute(); 
                        }
                    }
                }
            }
            
            //If we are here then we had success.
            if(empty($group_count))
            {
                $msg = 'Added project template#' . $newid . " with $role_count declared roles";
            } else {
                $msg = 'Added project template#' . $newid . " with $group_count member groups and $role_count declared roles";
            }
            
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$newid,
                'root_template_workitemid'=>$root_template_workitemid,
            );
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to add fields=' . print_r($fields,TRUE) 
                    . " with owner " . $myvalues['owner_personid']
                      . " project template (newid#$newid) on step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    public function addProjectRolesToWorkitem($workitemid, $projectroles_map, $created_by_personid=NULL, $updated_dt=NULL)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            $role_count = 0;
            db_delete(DatabaseNamesHelper::$m_map_prole2wi_tablename)
              ->condition('workitemid', $workitemid)
              ->execute(); 
            foreach($projectroles_map as $roleid=>$detail)
            {
                if($roleid > 0)
                {
                    $role_count++;
                    $fields = array(
                            'roleid' => $roleid,
                            'workitemid' => $workitemid,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        );
                    if(isset($detail['expected_cardinality']))
                    {
                        $fields['expected_cardinality'] = $detail['expected_cardinality'];
                    }
                    if(isset($detail['ot_scf']))
                    {
                        $fields['ot_scf'] = $detail['ot_scf'];
                    }
                    if(isset($detail['ob_scf']))
                    {
                        $fields['ob_scf'] = $detail['ob_scf'];
                    }
                    db_insert(DatabaseNamesHelper::$m_map_prole2wi_tablename)
                      ->fields($fields)
                          ->execute(); 
                }
            }
            return $role_count;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function addProjectRolesToWorkitemTemplate($template_workitemid, $projectroles_map, $created_by_personid=NULL, $updated_dt=NULL)
    {
        $transaction = db_transaction();
        try
        {
            if(empty($created_by_personid))
            {
                global $user;
                $created_by_personid = $user->uid;
            }
            if(empty($updated_dt))
            {
                $updated_dt = date("Y-m-d H:i", time());
            }
            $role_count = 0;
            db_delete(DatabaseNamesHelper::$m_map_prole2tw_tablename)
              ->condition('template_workitemid', $template_workitemid)
              ->execute(); 
            foreach($projectroles_map as $roleid=>$detail)
            {
                if($roleid > 0)
                {
                    $role_count++;
                    $fields = array(
                            'roleid' => $roleid,
                            'template_workitemid' => $template_workitemid,
                            'created_by_personid' => $created_by_personid,
                            'created_dt' => $updated_dt,
                        );
                    if(isset($detail['expected_cardinality']))
                    {
                        $fields['expected_cardinality'] = $detail['expected_cardinality'];
                    }
                    if(isset($detail['ot_scf']))
                    {
                        $fields['ot_scf'] = $detail['ot_scf'];
                    }
                    if(isset($detail['ob_scf']))
                    {
                        $fields['ob_scf'] = $detail['ob_scf'];
                    }
                    db_insert(DatabaseNamesHelper::$m_map_prole2tw_tablename)
                      ->fields($fields)
                          ->execute(); 
                }
            }
            return $role_count;
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    public function createNewSprint($owner_projectid, $myvalues)
    {
        $updated_dt = date("Y-m-d H:i", time());
        $transaction = db_transaction();
        try
        {
            $start_dt = $myvalues['start_dt'];
            $end_dt = $myvalues['end_dt'];
            
            if(empty($owner_projectid))
            {
                throw new \Exception("Missing required owner projectid!");
            }
            
            if(empty($myvalues['status_cd']))
            {
                throw new \Exception("Missing required status_cd!");
            }

            if(isset($myvalues['iteration_ct']))
            {
                $iteration_ct = $myvalues['iteration_ct'];
            } else {
                $project_sprint_overview = $this->m_oMapHelper->getProjectSprintOverview($owner_projectid);
                if(isset($project_sprint_overview['projects'][$owner_projectid]['last_sprint_number']))
                {
                    $last_sprint_number = $project_sprint_overview['projects'][$owner_projectid]['last_sprint_number'];
                    $iteration_ct = $last_sprint_number + 1;
                } else {
                    $iteration_ct = 1;
                }
            }
            
            $myfields = array(
                'owner_projectid' => $owner_projectid,
                'iteration_ct' => $iteration_ct,
                'title_tx' => strtoupper($myvalues['title_tx']),
                'story_tx' => $myvalues['story_tx'],
                'start_dt' => $start_dt,
                'end_dt' => $end_dt,
                'owner_personid' => $myvalues['owner_personid'],
                'membership_locked_yn' => $myvalues['membership_locked_yn'],
                'active_yn' => $myvalues['active_yn'],
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
              );
            $myfields['status_cd'] = $myvalues['status_cd'];
            $myfields['status_set_dt'] = $updated_dt;
            
            if($myvalues['ot_scf'] > '')
            {
                $myfields['ot_scf'] = $myvalues['ot_scf'];
            }
            if($myvalues['official_score'] > '')
            {
                $myfields['official_score'] = $myvalues['official_score'];
                $myfields['score_body_tx'] = $myvalues['score_body_tx'];
            }
            
            $main_qry = db_insert(\bigfathom\DatabaseNamesHelper::$m_sprint_tablename)
                ->fields($myfields);
            $newid = $main_qry->execute(); 
            
            if(!empty($myvalues['map_workitem2sprint']) && is_array($myvalues['map_workitem2sprint']))
            {
                foreach($myvalues['map_workitem2sprint'] as $member_workitemid)
                {
                    //Update or insert
                    db_insert(\bigfathom\DatabaseNamesHelper::$m_map_workitem2sprint_tablename)
                      ->fields(array(
                            'workitemid' => $member_workitemid,
                            'sprintid' => $newid,
                            'created_by_personid' => $myvalues['owner_personid'],
                            'created_dt' => $updated_dt,
                        ))
                          ->execute(); 
                }
            }
            
            //If we are here then we had success.
            $fullname = $myvalues['title_tx'] . '#' . $myvalues['iteration_ct'];
            $msg = 'Added ' . $fullname;
            $this->markProjectUpdatedForSprintID($newid, "created sprint item#$newid with name $fullname");
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$newid,
                'fullname'=>$fullname,
            );
            return $resultbundle;
            
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $fullname = $myvalues['title_tx'] . '#' . $myvalues['iteration_ct'];
            $msg = t('Failed to add ' . $fullname
                      . ' sprint because ' . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }
    
    public function createNewTemplate($myvalues)
    {
        $default_workitem_status_cd = 'WNS';
        $transaction = db_transaction();
        $newid = NULL;
        $step=0;
        $fields = array();
        try
        {
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            $step++;
            $placeholder_projectid = 0; //We will set this for real AFTER creating the project
            $owner_personid = $myvalues['owner_personid'];

            if(!isset($myvalues['workitems']) || empty($myvalues['workitems']) || count($myvalues['workitems']) < 1)
            {
                throw new \Exception("Missing required workitems array!");
            }
            $workitems = $myvalues['workitems'];
            if(!isset($myvalues['metadata']) || empty($myvalues['metadata']))
            {
                throw new \Exception("Missing required metadata!");
            }
            $metadata = $myvalues['metadata'];
            if(!isset($metadata['ROOT_WORKITEMID']))
            {
                throw new \Exception("Missing required metadata.ROOT_WORKITEMID!");
            }
            $metadata_root_workitemid = $metadata['ROOT_WORKITEMID'];
            
            if(!isset($metadata['PROJECT_CONTEXTID']))
            {
                throw new \Exception("Missing required metadata.PROJECT_CONTEXTID!");
            }
            $project_contextid = $metadata['PROJECT_CONTEXTID'];
            
            $template_nm = !empty($myvalues['template_nm']) ? $myvalues['template_nm'] : $metadata['TEMPLATE_NM'];
            $mission_tx = !empty($myvalues['mission_tx']) ? $myvalues['mission_tx'] : $metadata['MISSION_TX'];
            if(!isset($template_nm))
            {
                throw new \Exception("Missing required metadata.TEMPLATE_NM!");
            }
            if(!isset($mission_tx))
            {
                throw new \Exception("Missing required metadata.MISSION_TX!");
            }
            
            $submitter_blurb_tx = !empty($myvalues['submitter_blurb_tx']) ? $myvalues['submitter_blurb_tx'] : $metadata['SUBMITTER_BLURB_TX'];
            if(empty($submitter_blurb_tx))
            {
                $submitter_blurb_tx = "MISSING BLURB TEXT!";
            }
            $default_importance = 50;
            
            $workitem_labels = $workitems['labels'];
            $workitem_rows = $workitems['rows'];
            $workitem_fastmap_wid2rowoffset = $workitems['fastmap_wid2rowoffset'];
            $rootwid = $metadata['ROOT_WORKITEMID'];
            $root_workitem_row_offset = $workitem_fastmap_wid2rowoffset[$rootwid];
            $root_workitem_data = $workitem_rows[$root_workitem_row_offset];
            $root_workitem_info = UtilityGeneralFormulas::getAsKeyValuePairs($workitem_labels, $root_workitem_data);
            
            $root_workitem_name = !empty($myvalues['root_workitem_nm']) ? trim($myvalues['root_workitem_nm']) : $root_workitem_info['NAME'];
            
            //Create the root goal using this name
            $wi_fields = array(
                  'workitem_basetype' => 'G',
                  'workitem_nm' => $root_workitem_name,
                  'importance' => isset($root_workitem_info['IMPORTANCE']) ? $root_workitem_info['IMPORTANCE'] : $default_importance,
                  'owner_personid' => $owner_personid,
                  'owner_template_projectid' => $placeholder_projectid,
                  'purpose_tx' => isset($root_workitem_info['PURPOSE_TX']) ? $root_workitem_info['PURPOSE_TX'] : 'purpose todo',
                  'status_cd' => $default_workitem_status_cd,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            $goalcreate_qry = db_insert(DatabaseNamesHelper::$m_template_workitem_tablename)
                ->fields($wi_fields);
            $root_goalid = $goalcreate_qry->execute(); 
            $myvalues['root_goalid'] = $root_goalid;
            if(!empty($root_workitem_info['maps']['roles']))
            {
                $this->addProjectRolesToWorkitemTemplate($root_goalid, $root_workitem_info['maps']['roles'], $this_uid, $updated_dt);
            }

            $map_fakewid2realwid = [];
            $map_fakewid2realwid[$metadata_root_workitemid] = $root_goalid;
            
            if(empty($root_goalid))
            {
                throw new \Exception("Expected a root_goalid for the new template!");
            }
            
            $step++;
            $source_type = !empty($myvalues['source_type']) ? $myvalues['source_type'] : 'undeclared';
            $surrogate_yn = 0;
            $active_yn = 1;
            
            $proj_fields = array(
                  'template_nm' => $template_nm,
                  'submitter_blurb_tx'=>$submitter_blurb_tx,
                  'owner_personid' => $owner_personid,
                  'root_template_workitemid' => $root_goalid,
                  'project_contextid' => $project_contextid,
                  'active_yn' => $active_yn,
                  'mission_tx' => $mission_tx,
                  'updated_dt' => $updated_dt,
                  'created_dt' => $updated_dt,
              );
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'template_author_nm');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_templateid');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_template_refname');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'original_source_template_updated_dt');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'allow_status_publish_yn');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'allow_detail_publish_yn');
            
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'surrogate_ob_p');
            $this->setIfValueExistsNotBlank($proj_fields, $myvalues, 'surrogate_ot_p');
            
            if(isset($myvalues['allow_publish_items']))
            {
                $allow_publish_items = $myvalues['allow_publish_items'];
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_owner_name_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_onbudget_p_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_start_dt_yn');
                $this->setIfValueMatchesName($proj_fields, $allow_publish_items, 'allow_publish_item_actual_end_dt_yn');
            }
            $main_qry = db_insert(DatabaseNamesHelper::$m_template_project_library_tablename)
                ->fields($proj_fields);
            $newid = $main_qry->execute(); 

            $step++;
            //Now update the new goal to indicate ownership
            db_update(DatabaseNamesHelper::$m_template_workitem_tablename)
                ->fields(array(
                  'owner_template_projectid' => $newid,
                ))
                    ->condition('id', $root_goalid,'=')
                    ->execute();
            
            //Add role project level mappings next
            $step++;
            $role_count = 0;
            if(isset($myvalues['map_role2template']))
            {
                db_delete(DatabaseNamesHelper::$m_map_prole2project_tablename)
                  ->condition('projectid', $newid)
                  ->execute(); 
                if(is_array($myvalues['map_role2template']))
                {
                    foreach($myvalues['map_role2template'] as $roleid)
                    {
                        if($roleid > 0)
                        {
                            $role_count++;
                            $fields = array(
                                    'roleid' => $roleid,
                                    'projectid' => $newid,
                                    'created_by_personid' => $this_uid,
                                    'created_dt' => $updated_dt,
                                );
                            db_insert(DatabaseNamesHelper::$m_map_prole2project_tablename)
                              ->fields($fields)
                                  ->execute(); 
                        }
                    }
                }
            }
            
            //Now add all the other workitems
            //$map_rowoffset2wid = array_flip($workitem_fastmap_wid2rowoffset);
            $workitem_count = 1;
            $create_wi2wi = [];
            foreach($workitem_rows as $winfo)
            {
                $mapped_workitem_info = UtilityGeneralFormulas::getAsKeyValuePairs($workitem_labels, $winfo);
                $fakewid = $mapped_workitem_info['WID'];
                if($fakewid != $metadata_root_workitemid)
                {
                    $typeletter = $mapped_workitem_info['TYPELETTER'];
                    if($typeletter == 'X' || $typeletter == 'Q')
                    {
                        $basetype = 'T';
                    } else
                    if($typeletter == 'P')
                    {
                        $basetype = 'G';
                    } else {
                        $basetype = $typeletter;
                    }
                    $wi_fields = array(
                          'workitem_basetype' => $basetype,
                          'workitem_nm' => $mapped_workitem_info['NAME'],
                          'importance' => isset($mapped_workitem_info['IMPORTANCE']) ? $mapped_workitem_info['IMPORTANCE'] : $default_importance,
                          'owner_personid' => $owner_personid,
                          'owner_template_projectid' => $newid,
                          'purpose_tx' => isset($mapped_workitem_info['PURPOSE_TX']) ? $mapped_workitem_info['PURPOSE_TX'] : 'purpose TODO',
                          'status_cd' => $default_workitem_status_cd,
                          'updated_dt' => $updated_dt,
                          'created_dt' => $updated_dt,
                      );
                    $wcreate_qry = db_insert(DatabaseNamesHelper::$m_template_workitem_tablename)
                        ->fields($wi_fields);
                    $realwid = $wcreate_qry->execute();
                    $map_fakewid2realwid[$fakewid] = $realwid;
                    if(!empty($mapped_workitem_info['maps']['roles']))
                    {
                        $this->addProjectRolesToWorkitemTemplate($realwid, $mapped_workitem_info['maps']['roles'],$this_uid, $updated_dt);
                    }
                    $workitem_count++;
                }
                if(!empty($mapped_workitem_info['maps']['daw']))
                {
                    foreach($mapped_workitem_info['maps']['daw'] as $daw)
                    {
                        $link = array('fake_depwiid'=>$fakewid, 'fake_antwiid'=>$daw);
                        $create_wi2wi[] = $link;
                    }
                }
            }

            //Now use the maps to update all the links!
            $created_links = [];
            foreach($create_wi2wi as $link)
            {
                $fake_depwiid = $link['fake_depwiid'];
                $fake_antwiid = $link['fake_antwiid'];
                $depwiid = $map_fakewid2realwid[$fake_depwiid];
                $antwiid = $map_fakewid2realwid[$fake_antwiid];
                $key = $depwiid . '_2_' . $antwiid;
                if(!isset($created_links[$key]))
                {
                    //Create this one
                    $importance = $default_importance;
                    db_insert(DatabaseNamesHelper::$m_map_tw2tw_tablename)
                      ->fields(array(
                            'depwiid' => $depwiid,
                            'antwiid' => $antwiid,
                            'importance' => $importance,
                            'created_by_personid' => $this_uid,
                            'created_dt' => $updated_dt,
                        ))->execute(); 
                    $created_links[$key] = TRUE;
                }
            }
            
            //If we are here then we had success.
            $msg = 'Added template#' . $newid . " with $role_count declared roles";
            
            $resultbundle = array(
                'message'=>$msg,
                'newid'=>$newid,
                'root_goalid'=>$root_goalid,
                'workitem_count'=>$workitem_count,
            );
            return $resultbundle;
        }
        catch(\Exception $ex)
        {
            $transaction->rollback();
            $msg = t('Failed to create template with owner ' . $myvalues['owner_personid']
                      . " template#($newid) on step $step because " . $ex->getMessage());
            error_log("$msg\n" 
                      . print_r($myvalues, TRUE) . '>>>'. print_r($ex, TRUE));
            throw new \Exception($msg, 99910, $ex);
        }
    }

    private function setIfKeyExists(&$proj_fields, $myvalues, $key, $value_if_blank='')
    {
        if(array_key_exists($key, $myvalues))    //Do NOT use isset here!!!
        {
            if(empty($myvalues[$key]) && $myvalues[$key] !== 0)
            {
                $proj_fields[$key] = $value_if_blank;
            } else {
                $proj_fields[$key] = $myvalues[$key];
            }
        }
    }
    
    private function setIfValueExists(&$proj_fields, $myvalues, $key, $blank_becomes_null=FALSE)
    {
        if(array_key_exists($key, $myvalues))    //Do NOT use isset here!!!
        {
            if(!$blank_becomes_null)
            {
                $proj_fields[$key] = $myvalues[$key];
            } else {
                if(empty($myvalues[$key]) && $myvalues[$key] != 0)
                {
                    $proj_fields[$key] = NULL;
                } else {
                    $proj_fields[$key] = $myvalues[$key];
                }
            }
        }
    }

    private function setIfValueExistsNotBlank(&$proj_fields, $myvalues, $key)
    {
        if(isset($myvalues[$key]))
        {
            $value = $myvalues[$key];
            if(is_array($value))
            {
                if(array_key_exists('year', $value) && $value['year'] != "1900")
                {
                    $proj_fields[$key] = $value['year'] . "-" . $value['month'] . "-" . $value['day'];
                }
            } else 
            if(strlen(trim($value))>0)
            {
                $proj_fields[$key] = $value;    
            }
        }
    }
    
    /**
     * Use this with checkbox arrays 
     */
    private function setIfValueMatchesName(&$proj_fields, $myvalues, $key, $matchval=1, $nomatchval=0)
    {
        if(isset($myvalues[$key]) && $myvalues[$key] !== 0) //MUST use !== NOT !=!!!!!
        {
            $proj_fields[$key] = $matchval;
        } else {
            $proj_fields[$key] = $nomatchval;
        }
    }
    
}

