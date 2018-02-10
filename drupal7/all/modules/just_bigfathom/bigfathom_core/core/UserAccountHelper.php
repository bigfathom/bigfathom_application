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

require_once('DatabaseNamesHelper.php');

/**
 * Help with user account interactions
 * 
 * @author Frank Font of Room4me.com Software LLC
 */
class UserAccountHelper
{

    public static $MASTER_SYSTEMADMIN_UID = 1;
    
    public static $DEFAULT_ROLE_IMPORTANCE = 75;
    public static $DEFAULT_VISION_IMPORTANCE = 75;
    public static $DEFAULT_GOAL_IMPORTANCE = 75;
    public static $DEFAULT_TASK_IMPORTANCE = 75;
    
    public static $PROJECTROLEID_ITEM_OWNER = 1;
    
    public static $SYSTEMROLEID_ITEM_OWNER = 1;
    public static $SYSTEMROLEID_GROUP_READER = 2;
    public static $SYSTEMROLEID_PERSON_ACCOUNT_READER = 4;
    public static $SYSTEMROLEID_GROUP_MEMBER = 5;
    public static $SYSTEMROLEID_GROUP_ADMIN = 7;
    public static $SYSTEMROLEID_PROJECT_READER = 9;
    public static $SYSTEMROLEID_DATA_TRUSTEE = 88;
    public static $SYSTEMROLEID_SYSTEM_ADMIN = 99;
    
    private $m_default_personid;
    
    function __construct($default_personid=NULL)
    {
        if(empty($default_personid))
        {
            global $user;
            $this->m_default_personid = $user->uid;
        } else {
            $this->m_default_personid = $default_personid;
        }
    }
    
    /**
     * Return the UID of the created account
     */
    private function _createUserAccount($shortname, $firstname
            , $lastname
            , $baseline_availabilityid
            , $locationid
            , $role_maps
            , $email=NULL
            , $setpassword=TRUE
            , $password=NULL
            , $active_for_work_yn=1
            , $can_login_yn=1)
    {
        global $user;
        $updated_dt = date("Y-m-d H:i", time());
        try
        {
            if($setpassword && $password === NULL)
            {
                throw new \Exception("Cannot create user '$shortname' without also providing a password!");
            }
            
            //Grab role mapping declarations
            if(!array_key_exists('proles', $role_maps))
            {
                $proles = [];
            } else {
                $proles = $role_maps['proles'];
            }
            if(!array_key_exists('sroles', $role_maps))
            {
                $sroles = [];
            } else {
                $sroles = $role_maps['sroles'];
            }
            if(!array_key_exists('subrole_maps', $role_maps))
            {
                $subrole_maps = [];
            } else {
                $subrole_maps = $role_maps['subrole_maps'];
            }

            //Look for an existing drupal account
            $new_uid = $this->getDrupalUidFromShortname($shortname, FALSE);
            if($new_uid == NULL) 
            {                
                //Create the user account in DRUPAL first, then use that uid
                if(!$setpassword || empty($password))
                {
                    throw new \Exception("Cannot create user '$shortname' in Drupal without also providing a password!");
                }
                $fields = array(
                    'name' => $shortname,
                    'mail' => $email,
                    'pass' => $password,
                    'status' => $can_login_yn,
                    'init' => 'email address',
                    'roles' => array(DRUPAL_AUTHENTICATED_RID => 'authenticated user'),
                );
                $account = user_save('', $fields);
                $new_uid = $account->uid;
            }

            db_insert('bigfathom_person')->fields(array(
                'id' => $new_uid,
                'shortname' => $shortname,
                'first_nm' => $firstname,
                'last_nm' => $lastname,
                'baseline_availabilityid' => $baseline_availabilityid,
                'primary_locationid' => $locationid,
                'active_yn' => $active_for_work_yn,
                'updated_dt' => $updated_dt,
                'created_dt' => $updated_dt,
                ))->execute();
            $added_projectrole_count = 0;
            foreach($proles as $roleid=>$importance)
            {
                db_insert('bigfathom_map_person2role')->fields(array(
                    'personid' => $new_uid,
                    'roleid' => $roleid,
                    'importance' => $importance,
                    'created_by_personid' => $user->uid,
                    'created_dt' => $updated_dt,
                    ))->execute();
                $added_projectrole_count++;
            }
            $added_systemrole_count = 0;
            foreach($sroles as $systemroleid=>$importance)
            {
                db_insert('bigfathom_map_person2systemrole')->fields(array(
                    'personid' => $new_uid,
                    'systemroleid' => $systemroleid,
                    'created_by_personid' => $user->uid,
                    'created_dt' => $updated_dt,
                    ))->execute();
                $added_systemrole_count++;
            }
            //now add all the subroles
            $added_subrole_counters = array();
            foreach($subrole_maps as $typename=>$mapping)
            {
                $added_subrole_count = 0;
                foreach($mapping as $customfields)
                {
                    $fields = array(
                        'personid' => $new_uid,
                        'created_by_personid' => $user->uid,
                        'created_dt' => $updated_dt,
                    );
                    foreach($customfields as $fieldname=>$value)
                    {
                        if($fieldname == 'updated_dt' && $value = 'NOW')
                        {
                            $fields[$fieldname] = $updated_dt;
                        } else {
                            $fields[$fieldname] = $value;
                        }
                    }
                    db_insert("bigfathom_map_person2{$typename}")
                            ->fields($fields)
                            ->execute();
                    $added_subrole_count++;
                }
                if($added_subrole_count > 0)
                {
                    $added_subrole_counters[$typename] = $added_subrole_count;
                }
            }            
            
            $subrolestext_ar = array();
            foreach($added_subrole_counters as $typename=>$addedcount)
            {
                if($addedcount > 0)
                {
                    $subrolestext_ar[] = "added $addedcount $typename";
                }
            }
            if(count($subrolestext_ar) > 0)
            {
                $subrolesmarkup = " and <ul><li>" . implode("<li>", $subrolestext_ar) . '</ul>';
            } else {
                $subrolesmarkup = "";
            }
            return $new_uid;

        } catch (\Exception $ex) {
            drupal_set_message("Failed to create user '$shortname' because " . $ex, "error");
            throw $ex;
        }
    }
    
    public function setDrupalUserFields($uid,$assignments)
    {
        try
        {
            $account_fields = user_load($uid);
            user_save($account_fields,$assignments);
        } catch (\Exception $ex) {
            drupal_set_message("Failed to setDrupalUserFields for uid#$uid because " . $ex, "error");
            throw new \Exception($ex);
        }
    }
    
    public function setUserPassword($uid,$newpassword)
    {
        try
        {
            $changes = array();
            $changes['pass'] = $newpassword;
            $this->setDrupalUserFields($uid,$changes);
        } catch (\Exception $ex) {
            drupal_set_message("Failed to set password for uid#$uid because " . $ex, "error");
            throw new \Exception($ex);
        }
    }
    
    public function evaluateCandidatePassword($newpass)
    {
        try
        {
            $tooweak = FALSE;
            $tooshort = FALSE;
            $minlen = 6;
            $result = [];
            $result['minlen'] = $minlen;
            if(strlen($newpass) < $minlen)
            {
                $tooshort = TRUE;
                $tooweak = TRUE;
            } else
            if(strtolower($newpass) === $newpass)
            {
                $tooweak = TRUE;    
            } else
            if(strtoupper($newpass) === $newpass)
            {
                $tooweak = TRUE;    
            }
            $result['tooshort'] = $tooshort;
            $result['tooweak'] = $tooweak;
            return $result;
        } 
        catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function setUserEmail($uid,$newemail)
    {
        try
        {
            $changes = array();
            $changes['mail'] = $newemail;
            $this->setDrupalUserFields($uid,$changes);
        } catch (\Exception $ex) {
            drupal_set_message("Failed to set email for uid#$uid because " . $ex, "error");
            throw new \Exception($ex);
        }
    }

    public function setUserLoginStatus($uid,$newstatus)
    {
        try
        {
            $changes = array();
            $changes['status'] = $newstatus;
            $this->setDrupalUserFields($uid,$changes);
        } catch (\Exception $ex) {
            drupal_set_message("Failed to set status for uid#$uid because " . $ex, "error");
            throw new \Exception($ex);
        }
    }
    
    /**
     * Return the user id value if a record exists in Drupal for the provided name
     */
    public function getDrupalUidFromShortname($shortname, $throw_exception_if_missing=TRUE)
    {
        try
        {
            $found_uid = NULL;
            $finduser_query = db_select('users', 'u')
                    ->fields('u')
                    ->condition('name',$shortname,'=');
            $finduser_result = $finduser_query->execute();
            $finduser_count = $finduser_result->rowCount();
            if($finduser_count !== 1)
            {
                if($finduser_count > 1)
                {
                    throw new \Exception("Too many matches for user name='$shortname'!");
                }
                if($throw_exception_if_missing)
                {
                    throw new \Exception("Did not find a user record for name='$shortname'!");
                }
            } else {
                $drupal_records = $finduser_result->fetchAll();
                $account = $drupal_records[0];
                $found_uid = $account->uid;
            }
            return $found_uid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getMaps()
    {
        try
        {
            $the_nonadmin_uid = array(); //Simply a collection of IDs not admin
            $the_shortname2uid_map = array();
            $the_uid2shortname_map = array();
            $the_drupaluser_uid2name_map = array();
            $sSQL = "SELECT id, shortname"
                    . " FROM ".DatabaseNamesHelper::$m_person_tablename." p";
            $sSQL .= " ORDER BY shortname";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $uid = $record['id'];
                if($uid > 1)
                {
                    $the_nonadmin_uid[] = $uid;
                }
                $shortname = $record['shortname'];
                $the_shortname2uid_map[$shortname] = $uid; 
                $the_uid2shortname_map[$uid] = $shortname;
            }          
            
            $sSQL = "SELECT uid, name"
                    . " FROM users p";
            $sSQL .= " ORDER BY uid";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $uid = $record['uid'];
                $name = $record['name'];
                $the_drupaluser_uid2name_map[$uid] = $name;
            }          
            
            $themaps = array(
                'shortname2uid'=>$the_shortname2uid_map, 
                'uid2shortname'=>$the_uid2shortname_map,
                'nonadmin_uid'=>$the_nonadmin_uid,
                'drupaluser_uid2name'=>$the_drupaluser_uid2name_map,
                );
            return $themaps;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Returns code to indicate if the user can login
     * 0 = User account is not disabled
     * 1 = cannot login because missing active entry in person table
     */
    public static function getUserDisabledCode($shortname)
    {
        try
        {
            $findexisting_appuser_query = db_select('bigfathom_person', 'p')
                    ->fields('p')
                    ->condition('shortname',$shortname,'=')
                    ->condition('active_yn',1,'=');
            $findexisting_appuser_result = $findexisting_appuser_query->execute();
            $findexisting_appuser_count = $findexisting_appuser_result->rowCount();
            if($findexisting_appuser_count == 1)
            {
                return 0;
            }
            return 1;
        } catch (\Exception $ex) {
            throw new \Exception("Failed to determine if user can login because " . $ex, 99888, $ex);
        }
    }
    
    /**
     * Returns NULL if the shortname does not exist in the application
     */
    public function getExistingPersonID($shortname)
    {
        try
        {
            $findexisting_appuser_query = db_select('bigfathom_person', 'p')
                    ->fields('p')
                    ->condition('shortname',$shortname,'=');
            $findexisting_appuser_result = $findexisting_appuser_query->execute();
            $findexisting_appuser_count = $findexisting_appuser_result->rowCount();
            if($findexisting_appuser_count == 1)
            {
                $records = $findexisting_appuser_result->fetchAll();
                $info = $records[0];
                return $info->id;
            }
            return NULL;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getExistingPersonFullName($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $query = db_select('bigfathom_person', 'p')
                    ->fields('p')
                    ->condition('id',$personid,'=');
            $result = $query->execute();
            $records = $result->fetchAll();
            $info = $records[0];
            return $info->first_nm . " " . $info->last_nm;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Get the hide and show element settings for one user account
     */
    public function getElementControlBundle($personid=NULL)
    {
        try
        {
            $bundle = [];
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $bundle['personid'] = $personid;
            
            $sSQL = "SELECT id, show_elements, hide_elements "
                    . " FROM bigfathom_person "
                    . " WHERE id=$personid";
            $result = db_query($sSQL);
            $record = $result->fetchAssoc();
            $bundle['show'] = [];
            $bundle['hide'] = [];
            if(empty($record))
            {
                //throw new \Exception("Got no record for $sSQL");
                //Simply continue so we dont crash on anonymous user
            } else {
                $show_tx = $record['show_elements'];
                $show_list = explode(",",$show_tx);
                foreach($show_list as $word)
                {
                    $cleanword = strtoupper(trim($word));
                    if(strlen($cleanword) > 0)
                    {
                        $bundle['show'][$cleanword] = $cleanword;
                    }
                }
                $hide_list = explode(",",$record['hide_elements']);
                foreach($hide_list as $word)
                {
                    $cleanword = strtoupper(trim($word));
                    if(strlen($cleanword) > 0)
                    {
                        $bundle['hide'][$cleanword] = $cleanword;
                    }
                }
            }
            if($personid == 1)
            {
                $bundle['show']['UNFINISHED'] = 'UNFINISHED';   //See unfinished work
            }
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all roles for one user
     */
    public function getAllRolesBundle($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $bundle = [];
            
            $bundle['personid'] = $personid;
            $bundle['systemroles'] = $this->getPersonSystemRoleBundle($personid);
            $bundle['projectroles'] = $this->getPersonProjectRoleBundle($personid);
            
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getPersonSystemRoleBundle($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $thebits = [];
            $is_masteradmin = ($personid == self::$MASTER_SYSTEMADMIN_UID);
            $thebits['is_systemadmin'] = $is_masteradmin;
            $thebits['is_systemdatatrustee'] = $is_masteradmin;
            $thebits['is_systemwriter'] = $is_masteradmin;
            $themap = [];
            $sSQL = "SELECT sr.id as id, role_nm, "
                    . " sr.ot_scf as sr_scf, "
                    . " mapsr.ot_scf as mapsr_scf ";
            $sSQL .= " FROM bigfathom_map_person2systemrole mapsr";
            $sSQL .= " LEFT JOIN bigfathom_systemrole sr on sr.id=mapsr.systemroleid";
            $sSQL .= " WHERE mapsr.personid=$personid ";
            $sSQL .= " ORDER BY sr.id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id]['id'] = $id;
                $themap[$id]['role_nm'] = $record['role_nm'];
                if($id==UserAccountHelper::$SYSTEMROLEID_SYSTEM_ADMIN)
                {
                    $themap[$id]['systemadmin_yn'] = 1;
                    $thebits['is_systemadmin'] = TRUE;
                    $themap[$id]['systemdatatrustee_yn'] = 1;
                    $thebits['is_systemdatatrustee'] = TRUE;
                    $themap[$id]['systemitemowner_yn'] = 1;
                    $thebits['is_systemwriter'] = TRUE;
                } else {
                    $themap[$id]['systemadmin_yn'] = 0;
                }
                if($id==UserAccountHelper::$SYSTEMROLEID_DATA_TRUSTEE)
                {
                    $themap[$id]['systemdatatrustee_yn'] = 1;
                    $thebits['is_systemdatatrustee'] = TRUE;
                    $themap[$id]['systemitemowner_yn'] = 1;
                    $thebits['is_systemwriter'] = TRUE;
                } else {
                    $themap[$id]['systemdatatrustee_yn'] = 0;
                }
                if($id==UserAccountHelper::$SYSTEMROLEID_ITEM_OWNER)
                {
                    $themap[$id]['systemitemowner_yn'] = 1;
                    $thebits['is_systemwriter'] = TRUE;
                } else {
                    $themap[$id]['systemitemowner_yn'] = 0;
                }
                if(!empty($record['mapsr_scf']))
                {
                    $themap[$id]['ot_scf'] = $record['mapsr_scf'];
                } else {
                    $themap[$id]['ot_scf'] = $record['sr_scf'];
                }
            }
            $bundle = Array('summary'=>$thebits, 'detail'=>$themap);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getPersonGroupMembershipBundle($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $leaderofgroups = [];
            $themap = [];
            
            $sSQL = "SELECT id "
                  . "FROM " . DatabaseNamesHelper::$m_group_tablename . " rig "
                  . "WHERE leader_personid=$personid";
                  
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $leaderofgroups[$id] = $id;
                $themap[$id]['roles'] = [];
                $themap[$id]['roles'][1] = [];
                $themap[$id]['roles'][1]['importance'] = 100;
            }          
                  
            $sSQL = "SELECT groupid, roleid, importance "
                  . "FROM " . DatabaseNamesHelper::$m_map_person2role_in_group_tablename . " rig "
                  . "WHERE personid=$personid";
            
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['groupid'];
                $roleid = $record['roleid'];
                $importance = $record['importance'];
                if(!isset($themap[$id]['roles']))
                {
                    $themap[$id]['roles'] = [];
                }
                $themap[$id]['roles'][$roleid] = [];
                $themap[$id]['roles'][$roleid]['importance'] = $importance;
            }          
            $bundle = Array('leadership'=>$leaderofgroups, 'detail'=>$themap);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return all the project roles declared for one user
     */
    public function getPersonProjectRoleBundle($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $themap = [];
            $thebits = [];
            $thebits['is_tester'] = FALSE;
            $thebits['is_workitemcreator'] = FALSE;
            $thebits['is_workitemowner'] = FALSE;
            $thebits['is_sprintleader'] = FALSE;
            $thebits['is_projectmanager'] = FALSE;
            $thebits['is_groupleader'] = FALSE;
            $sSQL = "SELECT pr.id as id, role_nm, "
                    . " pr.tester_yn, "
                    . " pr.workitemcreator_yn, "
                    . " pr.workitemowner_yn, "
                    . " pr.sprintleader_yn, "
                    . " pr.projectleader_yn, "
                    . " pr.groupleader_yn, "
                    . " pr.ot_scf as pr_scf, "
                    . " mappr.ot_scf as mappr_scf, "
                    . " mappr.success_boost_factor as mappr_sbf ";
            $sSQL .= " FROM " . DatabaseNamesHelper::$m_map_person2role_tablename . " mappr";
            $sSQL .= " LEFT JOIN " . DatabaseNamesHelper::$m_role_tablename . " pr on pr.id=mappr.roleid";
            $sSQL .= " WHERE mappr.personid=$personid ";
            $sSQL .= " ORDER BY pr.id";
            $result = db_query($sSQL);
            while($record = $result->fetchAssoc()) 
            {
                $id = $record['id'];
                $themap[$id]['id'] = $id;
                $themap[$id]['role_nm'] = $record['role_nm'];
                $themap[$id]['workitemcreator_yn'] = $record['workitemcreator_yn'];
                $themap[$id]['workitemowner_yn'] = $record['workitemowner_yn'];
                $themap[$id]['tester_yn'] = $record['tester_yn'];
                $themap[$id]['sprintleader_yn'] = $record['sprintleader_yn'];
                $themap[$id]['projectleader_yn'] = $record['projectleader_yn'];
                $themap[$id]['groupleader_yn'] = $record['groupleader_yn'];
                $themap[$id]['success_boost_factor'] = $record['mappr_sbf'];
                $thebits['is_workitemcreator'] = $thebits['is_workitemcreator'] || $record['workitemcreator_yn'] > 0;
                $thebits['is_workitemowner'] = $thebits['is_workitemowner'] || $record['workitemowner_yn'] > 0;
                $thebits['is_tester'] = $thebits['is_tester'] || $record['tester_yn'] > 0;
                $thebits['is_sprintleader'] = $thebits['is_sprintleader'] || $record['sprintleader_yn'] > 0;
                $thebits['is_projectmanager'] = $thebits['is_projectmanager'] || $record['projectleader_yn'] > 0;
                $thebits['is_groupleader'] = $thebits['is_groupleader'] || $record['groupleader_yn'] > 0;
                if(!empty($record['mapsr_scf']))
                {
                    $themap[$id]['ot_scf'] = $record['mappr_scf'];
                } else {
                    $themap[$id]['ot_scf'] = $record['pr_scf'];
                }
            }          
            $bundle = Array('summary'=>$thebits, 'detail'=>$themap);
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getUserProfileCore($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            if($personid == 0)
            {
                //The anonymous user is never a user of bigfathom
                $record = NULL;
            } else {
                $sSQL = "SELECT p.id, p.shortname, first_nm, last_nm, "
                        . " u.mail as main_email, u.init as initial_email, u.timezone, "
                        . " p.can_create_local_project_yn, p.can_create_remote_project_yn, "
                        . " p.primary_phone, p.secondary_phone, p.secondary_email, "
                        . " p.ot_scf, p.ob_scf, "
                        . " p.updated_dt as profile_updated_dt, p.created_dt as profile_created_dt"
                        . " FROM " . DatabaseNamesHelper::$m_person_tablename . " p"
                        . " LEFT JOIN users u ON u.uid=p.id"
                        . " WHERE p.id=$personid";

                $result = db_query($sSQL);
                $record = $result->fetchAssoc();
                if(empty($record))
                {
                    throw new \Exception("There is no person with id=$personid");
                }
                $record['master_systemadmin_yn'] = ($personid==self::$MASTER_SYSTEMADMIN_UID) ? 1 : 0;
            }
            return $record;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getUserProfileBundle($personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                $personid = $this->m_default_personid;
            }
            $bundle = [];
            
            $core = $this->getUserProfileCore($personid);
            $roles = $this->getAllRolesBundle($personid);
            
            $bundle['core'] = $core;
            $bundle['roles'] = $roles;
            return $bundle;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Remove the user from both the application and the drupal system
     */
    public static function deleteUserAccount($personid)
    {
        try
        {
            if(empty($personid))
            {
                //Don't just use a default value for this operation!
                throw new \Exception("Deleting a user account requires you to EXPLICITYLY provide the UID!!!");
            }
            if($personid == self::$MASTER_SYSTEMADMIN_UID)
            {
                throw new \Exception("The master systemadmin cannot be deleted!!! LOOK[$personid]");
            }
            try
            {
                user_delete($personid);
                error_log("Deleted uid=$personid from DRUPAL");
            } catch (\Exception $ex) {
                //Log the issue but continue
                error_log("WARNING: Trouble deleting uid=$personid from DRUPAL system because $ex");
            }
            try
            {
                $num_deleted = db_delete('bigfathom_person')
                    ->condition('id', $personid)
                    ->execute();
                if($num_deleted == 1)
                {
                    error_log("Deleted uid=$personid from APPLICATION");
                } else {
                    //Log the issue and continue
                    error_log("WARNING: Expected num=1 on deleting uid=$personid from APPLICATION but instead got num=$num_deleted");
                }
            } catch (\Exception $ex) {
                //Log the issue but continue
                error_log("WARNING: Trouble deleting uid=$personid from APPLICATION because $ex");
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Create one user account and return the UID
     */
    public function createUserAccount($shortname, $firstname, $lastname
                    , $baseline_availabilityid, $locationid, $role_maps
                    , $email=NULL
                    , $password=NULL
                    , $active_for_work_yn=1
                    , $can_login_yn=1)
    {
        try
        {
            $setpassword=TRUE;
            if(empty($password))
            {
                throw new \Exception("Cannot create a User Account for '$shortname' without a password!");
            }
            //Make sure a user account does not already exist for this user
            $findexisting_appuser_query = db_select('bigfathom_person', 'p')
                    ->fields('p')
                    ->condition('shortname',$shortname,'=');
            $findexisting_appuser_result = $findexisting_appuser_query->execute();
            $findexisting_appuser_count = $findexisting_appuser_result->rowCount();
            if($findexisting_appuser_count > 0)
            {
                $records = $findexisting_appuser_result->fetchAll();
                $info = $records[0];
                $uid= $info->id;
                drupal_set_message("Found existing application '$shortname' user account=" . print_r($info, TRUE));
                if(!empty($password))
                {
                    $this->setUserPassword($uid, $password);
                    drupal_set_message("Updated password of '$shortname' uid#$uid");
                }
            } else {
                $uid = $this->_createUserAccount($shortname, $firstname, $lastname, $baseline_availabilityid, $locationid, $role_maps
                        , $email
                        , $setpassword, $password, $active_for_work_yn, $can_login_yn);
            }
            if(empty($uid))
            {
                drupal_set_message("ERROR EMPTY UID FOR shortname='$shortname' can_login_yn=$can_login_yn", "error");
            }
            return $uid;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Manage the system administrator role setting
     */
    public function setUserSystemAdministratorAttribute($personid, $is_sysadmin)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(!isset($is_sysadmin))
            {
                throw new \Exception("Missing required is_sysadmin!");
            }
            global $user;
            $updated_dt = date("Y-m-d H:i", time());                
            $this_user_psrb = $this->getPersonSystemRoleBundle($user->uid);
            if(!$this_user_psrb['summary']['is_systemadmin'])
            {
                error_log("HACKING WARNING: Unauthorized User#{$user->uid} at $updated_dt has tried to set is_sysadmin=$is_sysadmin for personid=$personid");
                throw new \Exception("UNAUTHORIZED S.A. FLAG EDIT ATTEMPT!");
            }
            $psrb = $this->getPersonSystemRoleBundle($personid);
            $request_yn = $is_sysadmin ? 1 : 0;
            $current_yn = $psrb['summary']['is_systemadmin'] ? 1 : 0;
            if($request_yn !== $current_yn)
            {
                //Make the change
                $fields = array(
                            'personid' => $personid,
                            'systemroleid' => self::$SYSTEMROLEID_SYSTEM_ADMIN,
                            'created_by_personid' => $user->uid,
                            'created_dt' => $updated_dt,
                        );
                error_log("User#{$user->uid} at $updated_dt has initiated change to is_sysadmin=$is_sysadmin for personid=$personid");
                if($request_yn == 1)
                {
                    //Add the system role
                    db_merge('bigfathom_map_person2systemrole')
                        ->key(array('personid'=>$personid,'systemroleid'=>self::$SYSTEMROLEID_SYSTEM_ADMIN))
                        ->fields($fields)
                        ->execute();
                    error_log("User#{$user->uid} at $updated_dt added systemadmin role for personid=$personid");
                } else {
                    //Remove the system role
                    db_delete('bigfathom_map_person2systemrole')
                              ->condition('personid', $personid)
                              ->condition('systemroleid', self::$SYSTEMROLEID_SYSTEM_ADMIN)
                              ->execute();
                    error_log("User#{$user->uid} at $updated_dt removed systemadmin role for personid=$personid");
                }
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Manage the system data trustee role setting
     */
    public function setUserSystemDataTrusteeAttribute($personid, $is_systemdatatrustee)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(!isset($is_systemdatatrustee))
            {
                throw new \Exception("Missing required is_systemdatatrustee!");
            }
            global $user;
            $updated_dt = date("Y-m-d H:i", time());                
            $this_user_psrb = $this->getPersonSystemRoleBundle($user->uid);
            if(!$this_user_psrb['summary']['is_systemadmin'])
            {
                error_log("HACKING WARNING: Unauthorized User#{$user->uid} at $updated_dt has tried to set is_systemdatatrustee=$is_systemdatatrustee for personid=$personid");
                throw new \Exception("UNAUTHORIZED S.D.T. EDIT ATTEMPT!");
            }
            $psrb = $this->getPersonSystemRoleBundle($personid);
            $request_yn = $is_systemdatatrustee ? 1 : 0;
            $current_yn = $psrb['summary']['is_systemdatatrustee'] ? 1 : 0;
            if($request_yn !== $current_yn)
            {
                //Make the change
                error_log("User#{$user->uid} at $updated_dt has initiated change to is_systemdatatrustee=$is_systemdatatrustee for personid=$personid");
                if($request_yn == 1)
                {
                    $fields = array(
                            'personid' => $personid,
                            'systemroleid' => self::$SYSTEMROLEID_DATA_TRUSTEE,
                            'created_by_personid' => $user->uid,
                            'created_dt' => $updated_dt,
                        );
                    //Add the system role
                    db_merge('bigfathom_map_person2systemrole')
                        ->key(array('personid'=>$personid,'systemroleid'=>self::$SYSTEMROLEID_DATA_TRUSTEE))
                        ->fields($fields)
                        ->execute();
                    error_log("User#{$user->uid} at $updated_dt added systemdatatrustee role for personid=$personid");
                } else {
                    //Remove the system role
                    db_delete('bigfathom_map_person2systemrole')
                              ->condition('personid', $personid)
                              ->condition('systemroleid', self::$SYSTEMROLEID_DATA_TRUSTEE)
                              ->execute();
                    error_log("User#{$user->uid} at $updated_dt removed systemdatatrustee role for personid=$personid");
                }
            }
            
        } catch (\Exception $ex) {
            throw new Exception("Failed to write data trustee role because $ex", 99888, $ex);
        }
    }
    
    /**
     * Manage the system itemowner role setting
     */
    public function setUserSystemItemOwnerAttribute($personid, $is_itemowner)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            if(!isset($is_itemowner))
            {
                throw new \Exception("Missing required is_itemowner!");
            }
            global $user;
            $updated_dt = date("Y-m-d H:i", time());                
            $this_user_psrb = $this->getPersonSystemRoleBundle($user->uid);
            if(!$this_user_psrb['summary']['is_systemadmin'])
            {
                error_log("HACKING WARNING: Unauthorized User#{$user->uid} at $updated_dt has tried to set is_sysadmin=$is_sysadmin for personid=$personid");
                throw new \Exception("UNAUTHORIZED S.I.O. EDIT ATTEMPT!");
            }
            $psrb = $this->getPersonSystemRoleBundle($personid);
            $request_yn = $is_itemowner ? 1 : 0;
            $current_yn = $psrb['summary']['is_systemwriter'] ? 1 : 0;
            if($request_yn !== $current_yn)
            {
                //Make the change
                error_log("User#{$user->uid} at $updated_dt has initiated change to is_itemowner=$is_itemowner for personid=$personid");
                if($request_yn == 1)
                {
                    //Add the system role
                    db_merge('bigfathom_map_person2systemrole')
                        ->key(array('personid'=>$personid,'systemroleid'=>self::$SYSTEMROLEID_ITEM_OWNER))
                        ->fields(array(
                            'personid' => $personid,
                            'systemroleid' => self::$SYSTEMROLEID_ITEM_OWNER,
                            'created_by_personid' => $user->uid,
                            'created_dt' => $updated_dt,
                        ))->execute();
                    error_log("User#{$user->uid} at $updated_dt added systemitemowner role for personid=$personid");
                } else {
                    //Remove the system role
                    db_delete('bigfathom_map_person2systemrole')
                              ->condition('personid', $personid)
                              ->condition('systemroleid', self::$SYSTEMROLEID_ITEM_OWNER)
                              ->execute();
                    error_log("User#{$user->uid} at $updated_dt removed systemitemowner role for personid=$personid");
                }
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @return boolean TRUE if user can change definition of the project
     * @throws \Exception
     */
    public static function isAllowedToChangeProjectDefinition($personid, $projectid)
    {
        try
        {
            if(empty($personid) || empty($projectid))
            {
                throw new \Exception("Missing required personid or projectid!");
            }
            
            //TODO check the edit_lock_cd and rights of the user!
            return TRUE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @return boolean TRUE if user can change content in the project
     * @throws \Exception
     */
    public static function isAllowedToChangeProjectContent($personid, $projectid)
    {
        try
        {
            if(empty($personid) || empty($projectid))
            {
                throw new \Exception("Missing required personid or projectid!");
            }
            
            //TODO check the edit_lock_cd and rights of the user!
            return TRUE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @return boolean TRUE if user can change content in the group
     * @throws \Exception
     */
    public static function isAllowedToChangeGroupContent($personid, $groupid)
    {
        try
        {
            if(empty($personid) || empty($groupid))
            {
                throw new \Exception("Missing required personid or groupid!");
            }
            
            //TODO check the edit_lock_cd and rights of the user!
            return TRUE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * @return boolean TRUE if user can read content in the project
     * @throws \Exception
     */
    public static function isAllowedToReadProjectContent($personid, $projectid)
    {
        try
        {
            if(empty($personid) || empty($projectid))
            {
                throw new \Exception("Missing required personid or projectid!");
            }
            
            //TODO check the show_cd and rights of the user!
            return TRUE;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

