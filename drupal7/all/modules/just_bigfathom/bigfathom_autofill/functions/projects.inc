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

function bigfathom_autofill_projectdata_form() 
{
    return drupal_get_form('bigfathom_autofill_projectdata_form_builder');
}
function bigfathom_autofill_projectdata_form_builder($form, &$form_state)
{
    try
    {
        drupal_add_http_header("Cache-Control", "no-store, must-revalidate, max-age=0"); 
        drupal_add_http_header("Pragma", "no-cache");
        drupal_add_http_header("Expires", "Sat, 26 Jul 1997 05:00:00 GMT");
        
        if(!isset($_GET['projectid']))
        {
            throw new \Exception("Missing required projectid!!!!");
        }
        $projectid = $_GET['projectid'];
        $flags = [];
        foreach($_GET as $name=>$value)
        {
            if(strlen($name)>5)
            {
                $lname = strtolower($name);
                if(substr($lname,0,5) == 'flag_')
                {
                    $flags[$lname] = $value;
                }
            }
        }
        if(isset($_GET['return']))
        {
            $redirect_url=$_GET['return'];
        } else
        if(isset($_GET['redirect']))
        {
            $redirect_url=$_GET['redirect'];
        }
        
        $start_ms = microtime(TRUE);
        $start_timestamp = time();
        $start_show = gmdate("Y-m-d\TH:i:s\Z", $start_timestamp);

        if(empty($redirect_url))
        {
            drupal_set_message("Started autofill project#$projectid at $start_show ($start_ms)");
        }
    
        $oContext = \bigfathom\Context::getInstance();
        $oContext->autofillProjectPlan($projectid,$flags);
        
        $done_ms = microtime(TRUE);
        $done_timestamp = time();
        $done_show = gmdate("Y-m-d\TH:i:s\Z", $done_timestamp);
        $duration_ts = round($done_timestamp - $start_timestamp,2);
        $duration_ms = $done_ms - $start_ms;
        
        if($duration_ts < .01)
        {
            $duration_tx = "$duration_ms miliseconds";
        } else {
            $duration_tx = "$duration_ts seconds";
        }
        
        $infomsg = "Finished autofill project#$projectid at $done_show with duration $duration_tx ($done_ms - $start_ms)";
        error_log("INFO: $infomsg");
        if(empty($redirect_url))
        {
            drupal_set_message($infomsg);
        } else {
            if(empty($_GET['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($_GET['rparams']));
            }
            $rparams_ar['action'] = 'autofilled';
            $rparams_ar['affectedproject'] = $projectid;
            $rparams_ar['duration'] = $duration_ts;
            $rparams_ar['duration_ms'] = $duration_ms;
            drupal_goto($redirect_url,
                array(
                  'query' => $rparams_ar,
                    )
                );
        }
    } catch (\Exception $ex) {
        throw $ex;
    }
}

