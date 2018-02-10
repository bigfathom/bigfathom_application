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

namespace bigfathom_autofill;

require_once 'DatabaseNamesHelper.php';
require_once 'MapHelper.php';

/**
 * This class helps us write back data
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class WriteHelper
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oMapHelper = new \bigfathom_autofill\MapHelper();
    }

    /**
     * Returns indication of success or fail in a bundle
     */
    public function createProjectAutofillActionRecord($projectid, $fields)
    {
        $transaction = db_transaction();
        try
        {
            $okay_yn = 0;
            $newid = NULL;
            $aborted_msg = NULL;
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($fields) || !is_array($fields))
            {
                throw new \Exception("Missing required fields array!");
            }
            
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            $fields['updated_dt'] = $updated_dt;
            $fields['created_dt'] = $updated_dt;
            $fields['created_by_personid'] = $this_uid;
            
            $dbtablename = \bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_tablename;
            try
            {
                $main_qry = db_insert(\bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_tablename)
                    ->fields($fields);
                $newid = $main_qry->execute();
                $okay_yn = 1;
                error_log("INFO: Inserted ID#$newid record into {$dbtablename} fields=".print_r($fields,TRUE));
            } catch (\Exception $ex) {
                //See why it failed
                //drupal_set_message("LOOK FAILED create the autofill record for project#$projectid in " . DatabaseNamesHelper::$m_project_autofill_action_tablename . " because $ex",'error');
                error_log("INFO: FAILED TO Inserted record into {$dbtablename} fields=".print_r($fields,TRUE));
                $existing_record = $this->m_oMapHelper->getProjectAutofillTrackingRecord($projectid);
                if($existing_record == NULL)
                {
                    //Not likely to happen, but this can happen with strange timing
                    drupal_set_message("Existing autofill processing was not yet complete, refresh your page and try again",'error');
                } else {
                    //Give them some useful information about what is currently running
                    $started_by_person = $existing_record['created_by_personid'];
                    $started_time = $existing_record['started_dt'];
                    $created_time = $existing_record['created_dt'];
                    $show_time = !empty($started_time) ? $started_time : $created_time;
                    $max_duration_allowed = $existing_record['max_duration_allowed'];
                    $timenow = time();
                    $last_time = strtotime($show_time);
                    $runtime_sofar = $timenow - $last_time;
                    $remaining_time = $max_duration_allowed - $runtime_sofar;
                    $buffer_seconds = 30;
                    $padded_remaining_time = $remaining_time + $buffer_seconds;
                    if($padded_remaining_time > 0)
                    {
                        //Leave the record alone, just report on it
                        $aborted_msg = "Autofill already running (started by person#$started_by_person at $show_time with maximum allowed runtime declared as $max_duration_allowed seconds)<br>NOTE: Should complete in no more than $padded_remaining_time seconds.";
                    } else {
                        //Kill the current record because it appears to be abandoned!
                        $aborted_msg = "Autofill started by person#$started_by_person at $show_time failed to complete within $max_duration_allowed seconds and has now been cancelled.  ($runtime_sofar seconds since started.)  Refresh your page and try your request again.";
                        $this->markProjectAutofillActionRecordFailedCompletion($projectid, "dead record after $runtime_sofar seconds");
                    }
                }
            }
            
            //If we are here then we had success.
            $resultbundle = array('okay_yn'=> $okay_yn,'newid'=>$newid);
            if(!empty($aborted_msg))
            {
                $resultbundle['aborted_msg'] = $aborted_msg;
            }
            error_log("INFO: Finished createProjectAutofillActionRecord project#$projectid result=".print_r($resultbundle,TRUE));            
            return $resultbundle;
            
        } catch (\Exception $ex) {
            error_log("ERROR: rollback in createProjectAutofillActionRecord project#$projectid because ".print_r($ex,TRUE));
            $transaction->rollback();
            throw new \Exception($ex);
        }
    }
    
    /**
     * Just updates the existing record with content from myvalues array
     */
    public function updateProjectAutofillActionRecord($projectid, $oAutofillEngine=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($oAutofillEngine))
            {
                throw new \Exception("Missing required oAutofillEngine!");
            }
            $oProjectAutofillDataBundle = $oAutofillEngine->getDataBundle();
            
            $updated_dt = date("Y-m-d H:i", time());
            $fields = $oProjectAutofillDataBundle->getValuesForTrackingRecordPassCompletedUpdate();
            $fields['updated_dt'] = $updated_dt;
            
            db_update(\bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_tablename)
                  ->fields($fields)
                  ->condition('projectid',$projectid)
                  ->execute();
            
            error_log("INFO: Finished updateProjectAutofillActionRecord project#$projectid fields=".print_r($fields,TRUE));            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    private function moveProjectAutofillAction2History($projectid, $history_record)
    {
        $transaction = db_transaction();
        try
        {
            $main_qry = db_insert(\bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_history_tablename)
                ->fields($history_record);
            $main_qry->execute();
            $delete_qry = db_delete(\bigfathom_autofill\DatabaseNamesHelper::$m_project_autofill_action_tablename);
            $delete_qry->condition('projectid',$projectid);
            $delete_qry->execute();
        } catch (\Exception $ex) {
            $transaction->rollback();
            throw $ex;
        }
    }
    
    /**
     * Just updates the existing record with content from myvalues array
     */
    public function markProjectAutofillActionRecordSuccessfulCompletion($projectid, $oAutofillEngine=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if(empty($oAutofillEngine))
            {
                throw new \Exception("Missing required oAutofillEngine!");
            }
            $oProjectAutofillDataBundle = $oAutofillEngine->getDataBundle();
            
            global $user;
            $this_uid = $user->uid;
            $timenow = time();
            $updated_dt = date("Y-m-d H:i", $timenow);
            $fields = $this->m_oMapHelper->getProjectAutofillTrackingRecord($projectid);
            if($oProjectAutofillDataBundle === NULL)
            {
                throw new \Exception("Missing required oProjectAutofillDataBundle!");
            }
            
            $finalinfo = $oProjectAutofillDataBundle->getValuesForTrackingRecordSuccessfulCompletionUpdate();
            foreach($finalinfo as $fieldname=>$fieldvalue)
            {
                $fields[$fieldname] = $fieldvalue;
            }
            
            $fields['error_tx'] = NULL;
            $fields['original_created_by_personid'] = $fields['created_by_personid'];
            $fields['original_created_dt'] = $fields['created_dt'];
            $fields['original_updated_dt'] = $fields['updated_dt'];

            $fields['history_created_by_personid'] = $this_uid;
            $fields['history_created_dt'] = $updated_dt;
            $fields['action_status'] = 5;
            
            unset($fields['id']);
            unset($fields['created_by_personid']);
            unset($fields['created_dt']);
            unset($fields['updated_dt']);
            unset($fields['estimated_remaining_duration']);
            
            $this->moveProjectAutofillAction2History($projectid, $fields);
            error_log("INFO: Finished markProjectAutofillActionRecordSuccessfulCompletion projectid#$projectid");
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    /**
     * Just updates the existing record with content from myvalues array
     */
    public function markProjectAutofillActionRecordFailedCompletion($projectid, $error_tx=NULL)
    {
        $fields = [];
        try
        {
            if(empty($projectid))
            {
                throw new \Exception("Missing required projectid!");
            }
            if($error_tx === NULL)
            {
                throw new \Exception("Missing required error_tx!");
            }
            
            global $user;
            $this_uid = $user->uid;
            $updated_dt = date("Y-m-d H:i", time());
            
            $fields = $this->m_oMapHelper->getProjectAutofillTrackingRecord($projectid);
            $fields['error_tx'] = $error_tx;
            $fields['original_created_by_personid'] = $fields['created_by_personid'];
            $fields['original_created_dt'] = $fields['created_dt'];
            $fields['original_updated_dt'] = $fields['updated_dt'];

            $fields['history_created_by_personid'] = $this_uid;
            $fields['history_created_dt'] = $updated_dt;
            $fields['action_status'] = 7;
            
            unset($fields['id']);
            unset($fields['created_by_personid']);
            unset($fields['created_dt']);
            unset($fields['updated_dt']);
            unset($fields['estimated_remaining_duration']);
            
            $this->moveProjectAutofillAction2History($projectid, $fields);
            
        } catch (\Exception $ex) {
            $errmsg = $ex . " with field values of " . print_r($fields,TRUE);
            throw new \bigfathom\BFCException($errmsg,98765,$ex);
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

