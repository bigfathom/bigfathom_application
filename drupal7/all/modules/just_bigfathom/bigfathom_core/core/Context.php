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

require_once 'config.php';
require_once 'DatabaseNamesHelper.php';
require_once 'UtilityGeneralFormulas.php';
require_once 'UtilityProjectTemplate.php';
require_once 'TextHelper.php';
require_once 'MarkupHelper.php';
require_once 'BasicMapHelper.php';
require_once 'UserAccountHelper.php';
require_once 'DebugHelper.php';
require_once 'exceptions/BFCException.php';

/**
 * This class helps with context management
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class Context
{
    
    private static $m_instance;
    
    public static $SPECIALGROUPID_EVERYONE = 1;
    public static $SPECIALGROUPID_DEFAULT_PRIVCOLABS = 10;
    public static $SPECIALGROUPID_NOBODY = 11;
    
    public static function getInstance()
    {
        if (null === static::$m_instance) 
        {
            static::$m_instance = new static();
            //static::$m_instance->clearSelectedProject();
            
            //Make sure the current user is allowed to login
            $loaded = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the UserAccountHelper class');
            }
            global $user;
            if($user->uid!=0)
            {
                //This is a fail-safe check
                $shortname = $user->name;
                if(UserAccountHelper::getUserDisabledCode($shortname) > 0)
                {
                    if($user->uid==1)
                    {
                        //Always let the main Drupal account in
                        $msg = "The user#{$user->uid} does NOT have an application account but will be allowed to login to Bigfathom!";
                        if(!UtilityGeneralFormulas::hasExistingDrupalMessageMatch($msg,'error'))
                        {
                            drupal_set_message($msg,"error");
                            error_log("Sent to user_logout because $msg");
                        }
                    } else {
                        $msg = "The user '$shortname' is not allowed to login!";
                        drupal_set_message($msg,"error");
                        error_log("Sent to user_logout because $msg");
                        module_load_include('pages.inc', 'user');
                        user_logout();
                    }
                }
            }
        }
        return static::$m_instance;
    }
    
    /**
     * Protected and private magic methods to prevent breakage of singleton
     */
    protected function __construct(){}
    private function __clone(){}  
    private function __wakeup(){}
    
    public function getProjectInfo($projectid)
    {
        try
        {
            $maphelper = new \bigfathom\BasicMapHelper();
            return $maphelper->getOneProjectDetailData($projectid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectID4PubID($pubid)
    {
        try
        {
            $maphelper = new \bigfathom\BasicMapHelper();
            return $maphelper->getProjectID4PubID($pubid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return TRUE if allowed for fetch, else return false.
     */
    public function isFetchURIAllowed($candidate_uri)
    {
        $isok = TRUE;
        $parts = parse_url($candidate_uri);
        $scheme = strtolower(trim($parts['scheme']));
        $host = strtolower(trim($parts['host']));

        if(strlen($scheme) == 0 || strlen($host) == 0)
        {
            $isok = FALSE;
        } else {
            $sSQL1 = "SELECT 1"
                    . " FROM ".DatabaseNamesHelper::$m_remote_uri_scheme_whitelist
                    . " WHERE remote_uri_scheme='$scheme'";
            if(db_query($sSQL1)->rowCount() != 1)
            {
                $isok = FALSE;
            } else {
                $sSQL2 = "SELECT 1"
                        . " FROM ".DatabaseNamesHelper::$m_remote_uri_domain_whitelist
                        . " WHERE remote_uri_domain='$host'";
                if(db_query($sSQL2)->rowCount() != 1)
                {
                    $isok = FALSE;
                }
            }
        }
        
        return $isok;
    }
    
    public function modifyAllProjectBranstormTopics($projectid, $actionname)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        
        if($actionname == 'restore_all_parkinglot')
        {
            $num_updated = $oWriteHelper->updateProjectBrainstormItemsRestoreParkinglot($projectid);
            drupal_set_message("Restored all $num_updated parked brainstorm topics");
        } else
        if($actionname == 'move_trashcan2parkinglot')
        {
            $num_updated = $oWriteHelper->updateProjectBrainstormItemsMoveTrashcan2Parkinglot($projectid);
            drupal_set_message("Moved all $num_updated trashed brainstorm topics into the parkinglot");
        } else
        if($actionname == 'move_parkinglot2trashcan')
        {
            $num_updated = $oWriteHelper->updateProjectBrainstormItemsMoveParkinglot2Trashcan($projectid);
            drupal_set_message("Moved all $num_updated parked brainstorm topics into the trashcan");
        } else
        if($actionname == 'empty_the_trashcan')
        {
            $num_updated = $oWriteHelper->updateProjectBrainstormItemsEmptyTheTrashcan($projectid);
            drupal_set_message("Discarded all $num_updated brainstorm topics from the trashcan");
        } else {
            throw new \Exception("Did not recognize actionname=$actionname!!!");
        }
    }
    
    public function lockAllProjectEstimates($projectid)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        $num_updated = $oWriteHelper->updateProjectEstimateLock($projectid, TRUE);
        drupal_set_message("Locked estimates for $num_updated workitems");
    }
    
    public function unlockAllProjectEstimates($projectid)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        $num_updated = $oWriteHelper->updateProjectEstimateLock($projectid, FALSE);
        drupal_set_message("Unlocked estimates for $num_updated workitems");
    }

    public function lockSprintMembership($sprintid)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        $num_updated = $oWriteHelper->updateSprintMembershipLock($sprintid, TRUE);
        drupal_set_message("Locked membership of sprint with $num_updated workitems");
    }

    public function unlockSprintMembership($sprintid)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        $num_updated = $oWriteHelper->updateSprintMembershipLock($sprintid, FALSE);
        drupal_set_message("Unlocked membership of sprint with $num_updated workitems");
    }
    
    public function autofillProjectPlan($projectid,$flags) //$effort_yn=1,$dates_yn=1,$cost_yn=1)
    {
        //TODO make sure the user has the rights to do this!!!
        $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        $updatedinfo = $oWriteHelper->autofillProjectPlan($projectid,$flags);//$effort_yn,$dates_yn,$cost_yn);
        $map_unprocessed_wids = $updatedinfo['map_unprocessed_wids'];                
        $aborted_yn = $updatedinfo['aborted_yn'];
        $aborted_msg = !empty($updatedinfo['aborted_msg']) ? $updatedinfo['aborted_msg'] : NULL;
        $num_updated = $updatedinfo['num_updated'];
        $num_candidates = $updatedinfo['num_candidates'];
        $num_failed = $updatedinfo['num_failed'];
        $wid_updated = $updatedinfo['updated_workitems'];
        $wid_failed2update = $updatedinfo['failed_workitems'];
        $showed=FALSE;
        if($num_updated > 0)
        {
            //We probably had updates, will make sure as we scan now
            $detail_ar = [];
            foreach($wid_updated as $wid=>$fields)
            {
                $onerow_ar = [];
                foreach($fields as $name=>$value)
                {
                    if($name != 'updated_dt' && $value !== NULL)
                    {
                        $onerow_ar[] = "$name as $value";
                    }
                }
                if(count($onerow_ar)>0)
                {
                    $onerowhtml = implode(" and ", $onerow_ar);
                    $detail_ar[] = "ID#$wid we set $onerowhtml";
                }
            }
            if(count($detail_ar)>0)
            {
                $showed=TRUE;
                sort($detail_ar);
                $detailhtml = "<ol><li>" . implode("<li>", $detail_ar) . "</ol>";
                if($num_updated == 1)
                {
                    drupal_set_message("Auto-filled values for 1 workitem $detailhtml");
                } else {
                    drupal_set_message("Auto-filled values for $num_updated workitems $detailhtml");
                }
            } else {
                //Fix the count now
                $num_updated = 0;
            }
        }
        if($num_failed > 0)
        {
            $showed=TRUE;
            $detail_ar = [];
            foreach($wid_failed2update as $wid=>$message)
            {
                $detail_ar[] = "ID#$wid $message";
            }
            $detailhtml = "<ol><li>" . implode("<li>", $detail_ar) . "</ol>";
            drupal_set_message("Auto-fill failed for $num_failed workitems $detailhtml","warning");
        }
        if($aborted_yn)
        {
            if(!empty($aborted_msg))
            {
                drupal_set_message($aborted_msg,'error');
            } else {
                $thecount = count($map_unprocessed_wids);
                if($thecount == 0)
                {
                    drupal_set_message("Auto-fill ended early",'warning');
                } else {
                    $unprocessed_wids_tx = implode(' and ',$map_unprocessed_wids);
                    if($thecount === 1)
                    {
                        drupal_set_message("No auto-fill was processed for workitem $unprocessed_wids_tx",'warning');
                    } else {
                        drupal_set_message("No auto-fill was processed for workitems $unprocessed_wids_tx",'warning');
                    }
                }
            }
        } else
        if(!$showed)
        {
            if($num_candidates == 0)
            {
                drupal_set_message("No workitems meeting filter criteria found to auto-fill");
            } else {
                if($num_candidates == 1)
                {
                    drupal_set_message("No auto-fill changes to the 1 candidate workitem that met the filter criteria");
                } else {
                    drupal_set_message("No auto-fill changes to the $num_candidates candidate workitems that met the filter criteria");
                }
            }
        }
    }
    
    public function setSelectedProject($projectid)
    {
        try
        {
            $record = $this->getProjectInfo($projectid);
            $loaded = module_load_include('php','bigfathom_core','core/WriteHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the WriteHelper class');
            }
            $oWriteHelper = new \bigfathom\WriteHelper();
            $oWriteHelper->markProjectSelected($projectid);
            $_SESSION['selected_projectid'] = $projectid;
            $_SESSION['selected_root_workitem_nm'] = $record['root_workitem_nm'];
            $_SESSION['selected_root_goalid'] = $record['root_goalid'];
            $ptxt = $record['purpose_tx'];
            $_SESSION['selected_root_purpose_tx'] = $ptxt;
            if(strlen($ptxt) < 200)
            {
                $_SESSION['selected_root_purpose_tx4heading'] = $ptxt;
            } else {
                $trimmed = trim(substr($ptxt,0,200)) . " ..."; 
                $_SESSION['selected_root_purpose_tx4heading'] = $trimmed;
            }
            menu_router_build();
            menu_rebuild();
        } catch (\Exception $ex) {
            $this->clearSelectedProject();
            throw $ex;
        }
    }
    
    public function clearSelectedProject()
    {
        $_SESSION['selected_projectid'] = NULL;
        $_SESSION['selected_root_workitem_nm'] = NULL;
        $_SESSION['selected_root_goalid'] = NULL;
        $_SESSION['selected_root_purpose_tx'] = NULL;
        menu_router_build();
        menu_rebuild();
    }

    public function hasSelectedProject()
    {
        return isset($_SESSION['selected_projectid']) && $_SESSION['selected_projectid'] != '';
    }

    public function getSelectedProjectSummary()
    {
        $info = array();
        if($this->hasSelectedProject())
        {
            $info['projectid'] = $_SESSION['selected_projectid'];
            $info['root_workitem_nm'] = $_SESSION['selected_root_workitem_nm'];
            $info['root_goalid'] = $_SESSION['selected_root_goalid'];
            $info['selected_root_purpose_tx'] = $_SESSION['selected_root_purpose_tx'];
        }
        return $info;
    }
    
    public function getSelectedProjectID()
    {
        if(!$this->hasSelectedProject())
        {
            return NULL;
        }
        return $_SESSION['selected_projectid'];
    }
    
    public function getSelectedProjectName()
    {
        return $_SESSION['selected_root_workitem_nm'];
    }

    public function getSelectedProjectRootGoalID()
    {
        return $_SESSION['selected_root_goalid'];
    }
    
    public function getSelectedProjectRootPurpose()
    {
        return $_SESSION['selected_root_purpose_tx'];
    }
    
    public function setMenuItemInfo($custom_page_key,$baseline_mi_info)
    {
        if(empty($custom_page_key))
        {
            throw new \Exception("Missing required custom page key!");
        }
        if(empty($baseline_mi_info))
        {
            throw new \Exception("Missing required baseline mi info!");
        }
        $mi = $baseline_mi_info;
        $mi['created_ts'] = time();
        $_SESSION['mynavtrack'][$custom_page_key] = $mi;
    }
    
    public function getParentMenuItem($custom_page_key=NULL)
    {
        if(!empty($custom_page_key) && isset($_SESSION['mynavtrack'][$custom_page_key]))
        {
            $mi = $_SESSION['mynavtrack'][$custom_page_key];
        } else {
            $mt = menu_get_active_trail();
            if(empty($mt) || !is_array($mt) || count($mt) < 2)
            {
                $mi = [];
                $mi['link_title'] = NULL;
                $mi['link_path'] = NULL;
            } else {
                //Parent is NOT the last one.
                $offset = count($mt)-2;
                $mi = $mt[$offset];
            }
        }
        return $mi;
    }
    
    public function getCurrentMenuItem()
    {
        $mt = menu_get_active_trail();
        if(empty($mt) || !is_array($mt) || count($mt) < 1)
        {
            $mi = [];
            $mi['link_title'] = NULL;
            $mi['link_path'] = NULL;
        } else {
            //Current is the last one.
            $offset = count($mt)-1;
            $mi = $mt[$offset];
        }
        if(empty($mi['link_path']))
        {
            if(!empty($mi['path']))
            {
                $mi['link_path'] = $mi['path'];
            } else
            if(!empty($mi['tab_root']))
            {
                $mi['link_path'] = $mi['tab_root'];
            }
        }
        return $mi;
    }
    
    public function postContentsToURL($url
                        , $post_fields
                        , $useragent='cURL'
                        , $follow_redirects=FALSE
                        , $debug=FALSE) 
    {
        $step_marker = "top";
        try
        {
            if(empty($post_fields) || !is_array($post_fields))
            {
                throw new \Exception("Expected an array of fields to post!");
            }

            if($debug)
            {
                \bigfathom\DebugHelper::showNeatMarkup($post_fields,"Fields for post to $url");
            }

            //url-ify the data for the POST
            $step_marker = "set_fields";
            $raw_fields_string = "";
            foreach($post_fields as $key=>$value) 
            { 
                $raw_fields_string .= $key.'='.$value.'&'; 
            }
            $fields_string = rtrim($raw_fields_string,'&');

            //open connection
            $step_marker = "open_connection";
            $ch = \curl_init();

            //set the url, number of POST vars, POST data
            $step_marker = "set_options";
            \curl_setopt($ch, CURLOPT_URL, $url);
            if($debug)
            {
                \curl_setopt($ch, CURLOPT_VERBOSE, 1); 
            }        
            \curl_setopt($ch, CURLOPT_POST, count($post_fields));
            \curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            \curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

            if ($follow_redirects==TRUE) 
            {
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
            } else {
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
            }

            //execute post
            $step_marker = "execute";
            $result = curl_exec($ch);
            if($debug)
            {
                \bigfathom\DebugHelper::showNeatMarkup($result,"Result from $url");
            }

            //close connection
            $step_marker = "close";
            \curl_close($ch);
            
            return $result;
            
        } catch (\Exception $ex) {
            throw new \Exception("Failed postContentsToURL($url, $useragent) at $step_marker because " . $ex, 99877, $ex);
        }
    }
    
    /**
     * Derived from function by Andy Langton: https://andylangton.co.uk/
     */
    public function getURLContents($url, $useragent='cURL'
                                        , $headers=FALSE
                                        , $follow_redirects=FALSE
                                        , $debug=TRUE) 
    {
        try
        {
            
            # initialize the CURL library
            $ch = \curl_init();

            # specify the URL to be retrieved
            \curl_setopt($ch, CURLOPT_URL, $url);

            if($debug)
            {
                \curl_setopt($ch, CURLOPT_VERBOSE, 1); 
            }
            \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
            \curl_setopt($curl, CURLOPT_TIMEOUT, 10);
  
            # we want to get the contents of the URL and store it in a variable
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            # specify the useragent: this is a required courtesy to site owners
            \curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

            # ignore SSL errors
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            # return headers as requested
            if ($headers==TRUE)
            {
                \curl_setopt($ch, CURLOPT_HEADER, 1);
            }

            # only return headers
            if ($headers=='headers only') 
            {
                \curl_setopt($ch, CURLOPT_NOBODY, 1);
            }

            # follow redirects - note this is disabled by default in most PHP installs from 4.4.4 up
            if ($follow_redirects==TRUE) 
            {
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
            } else {
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
            }

            # if debugging, return an array with CURL's debug info and the URL contents
            if ($debug==TRUE) 
            {
                $result['contents']=\curl_exec($ch);
                $result['info']=\curl_getinfo($ch);
            } else {
                $result=\curl_exec($ch);
            }

            # free resources
            \curl_close($ch);

            # send back the data
            return $result;
        } catch (\Exception $ex) {
            throw new \Exception("Failed getURLContents($url,$useragent) because " . $ex, 99876, $ex);
        }
    }    
    
}

