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
 * All the Drupal functions for one data context are in this file
 */

module_load_include('php','bigfathom_core','core/Context');
module_load_include('php','bigfathom_core','form/ViewAttachedFile');

function bigfathom_core_viewattachment_form() 
{
    return drupal_get_form('bigfathom_core_viewattachment_form_builder');
}

function bigfathom_core_viewattachment_form_builder($form, &$form_state)
{
    try
    {
        if(!isset($_GET['aid']))
        {
            throw new \Exception("Missing required aid url parameter!");
        }
        $aid = $_GET['aid'];
        if(!is_numeric($aid))
        {
            throw new \Exception("Invalid aid url parameter: that value MUST be numeric!");
        }

        module_load_include('php', 'raptor_glue', 'form/ViewAttachedFile');

        $showclose = isset($_GET['showclose']);
        $oPI = new \bigfathom\ViewAttachedFile($aid,$showclose);
        $form = array();
        return $oPI->getForm($form, $form_state);
    } catch (\Exception $ex) {
        error_log("Failed bigfathom_core_viewattachment_form_builder for aid=$aid because $ex");
        throw $ex;
    }
}

function bigfathom_core_attachment_set_topheaders($filename, $filesize)
{
    $fileinfo = pathinfo($filename);
    if(isset($fileinfo['extension']))
    {
        $ext = strtolower(trim($fileinfo['extension']));
    } else {
        throw new \Exception("Expected to find an extension in filename '$filename'!");
    }
    if(empty($ext))
    {
        throw new \Exception("[$ext] Expected to find an extension in filename '$filename' but it is empty!");
    }
    if($ext == 'zip')
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Transfer-Encoding: binary");
    } else {
        if($ext == 'pdf')
        {
            $ct_text = "application/pdf";
        } else if($ext == 'xls') {
            //Spreadsheet
            $ct_text = "application/vnd.ms-excel";
        } else if($ext == 'xlsx') {
            //Spreadsheet
            $ct_text = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        } else if($ext == 'doc' || $ext == 'dot') {
            //Documents
            $ct_text = "application/msword";
        } else if($ext == 'docx') {
            //Documents
            $ct_text = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        } else if($ext == 'ppt') {
            //Powerpoint
            $ct_text = "application/vnd.ms-powerpoint";
        } else if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') {
            //A flavor of image
            $ct_text = "image/$ext";
        } else if($ext == 'xml' || $ext == 'csv' || $ext == 'json') {
            //A flavor of text
            $ct_text = "text/$ext";
        } else {
            //Other
            $ct_text = "text/plain";
        }
        header("Content-type: $ct_text");
    }
    header("Content-Length: $filesize");
    header("Content-Disposition: attachment; filename=\"".$filename."\"");
}

function bigfathom_core_download_attachment_noform()//$form)//, &$form_state)
{
    $aid = NULL;    //Initialize out here so catch has the value
    try
    {
        if(!isset($_GET['aid']))
        {
            throw new \Exception("Missing required aid url parameter!");
        }
        $aid = $_GET['aid'];
        if(!is_numeric($aid))
        {
            throw new \Exception("Invalid aid url parameter: that value MUST be numeric!");
        }

	$sql = "select filename, filesize, file_blob from " 
                . \bigfathom\DatabaseNamesHelper::$m_attachment_tablename 
                . " where id=$aid";
	$result = db_query($sql);
	$record = $result->fetchAssoc(); 
        if(empty($record))
        {
            throw new \Exception("Failed to get a record for $sql");
        }
        $filename = $record['filename'];
        $filesize = $record['filesize'];
        bigfathom_core_attachment_set_topheaders($filename, $filesize);
        
  	echo $record['file_blob'];
        
    } catch (\Exception $ex) {
        error_log("Failed bigfathom_core_download_attachment_noform for aid=$aid because $ex");
        throw $ex;
    }
}


