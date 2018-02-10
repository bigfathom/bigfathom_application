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

function bigfathom_core_exportjson_form() 
{
    return drupal_get_form('bigfathom_core_exportjson_form_builder');
}

function bigfathom_core_importjson_form() 
{
    return drupal_get_form('bigfathom_core_importjson_form_builder');
}

function bigfathom_core_downloadprojectinfo_form() 
{
    return drupal_get_form('bigfathom_core_downloadprojectinfo_form_builder');
}

function bigfathom_core_downloadprojectinfonamelist_form() 
{
    return drupal_get_form('bigfathom_core_downloadprojectinfonamelist_form_builder');
}

function bigfathom_core_downloadtemplate_form() 
{
    return drupal_get_form('bigfathom_core_downloadtemplate_form_builder');
}

/**
 * Send a downloadable JSON file to the browser
 */
function bigfathom_core_exportjson_form_builder($form, &$form_state)
{
    try 
    {
        global $user;
        $projectid = NULL;
        if(!empty($_GET['projectid']))
        {
            //Pull it from the URL since they provided it.
            $projectid=$_GET['projectid'];
            if(!is_numeric($projectid))
            {
                throw new \Exception("The project id must be numeric instead of '$projectid'!");
            }
        }
        if(empty($_GET['dataname']))
        {
            drupal_set_message("ERROR in export function call --- missing dataname!");
            throw new \Exception("Missing dataname!");
        } else {
            $sDataName = $_GET['dataname'];
        }
        if(empty($projectid))
        {
            $projectid = NULL;
        }
        //error_log("Exporting $sDataName data in JSON format as person " . print_r($user,TRUE));

        $loaded = module_load_include('inc','bigfathom_core','io/IO');
        if(!$loaded)
        {
            drupal_set_message(t('Failed to load the IO class'), 'error');
        }
        $oIO = new \bigfathom\IO();
        $aExtraTopLevelAttribs = array();
        foreach ($_GET as $key => $value) 
        {
            $aExtraTopLevelAttribs[$key] = $value;
        }
        $sExtraContextNoteText=NULL;
        $sJSON = $oIO->getDataAsJSON($projectid,$sDataName,$aExtraTopLevelAttribs,$sExtraContextNoteText);

        //Dump it.
        $now = date('Y-m-d H:i:s');
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        if(empty($projectid))
        {
            $header_p = "";
        } else {
            $header_p = "_p{$projectid}";
        }
        header("Content-Disposition: attachment; filename=bigfathom_{$sDataName}{$header_p}_{$now}.json");
        header("Content-Type: application/octet-stream; "); 
        header("Content-Transfer-Encoding: binary");

        echo $sJSON;

        drupal_exit();  //Otherwise more stuff gets added to the file.
    } catch (\Exception $ex) {
        throw $ex;
    }
}

/**
 * Import JSON file from the browser
 */
function bigfathom_core_importjson_form_builder($form, &$form_state)
{
    try 
    {
        //Get the person information but do not polute the url output.
        ob_start(); //We will kill all messages.
        $errormsg = NULL;
        $hit_ex = NULL;
        $result_bundle = NULL;
        try
        {
            global $user;
            error_log('Importing data in JSON format as person ' . $user->uid);
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $jsonPostData = file_get_contents('php://input');
                error_log('Importing bigfathom_core_importjson_form_builder jsonPostData=' . print_r($jsonPostData,TRUE));
                error_log('Importing bigfathom_core_importjson_form_builder jsonPostData len=' . strlen($jsonPostData));
                error_log('Importing bigfathom_core_importjson_form_builder jsonPostData tail=...' . substr($jsonPostData,-100));
            
                $alldata_ar = json_decode($jsonPostData, TRUE);
                error_log('Importing bigfathom_core_importjson_form_builder jsonPostData->$alldata_ar=' . print_r($alldata_ar,TRUE));
 
                if(count($alldata_ar) == 0 && !empty($jsonPostData))
                {
                    $something = $jsonPostData;
                    $somethinglen = strlen($something);
                    if($somethinglen > 100)
                    {
                        $something = substr($something, 0, 99) . "... (total len={$somethinglen})";
                    }
                    $errormsg = "No properties found in the package but something was sent! POST={$something}"; 
                    error_log("Importing bigfathom_core_importjson_form_builder $errormsg");
                }

            } else {
                $alldata_ar = $_REQUEST;   //Grab all the data
                error_log('Importing bigfathom_core_importjson_form_builder without POST!!!!');
                error_log('Importing bigfathom_core_importjson_form_builder alldata_ar=' . print_r($alldata_ar,TRUE));
            }
            if(empty($errormsg))
            {
                error_log('Importing bigfathom_core_importjson_form_builder caboose=[' .$alldata_ar['databundle']['caboose'] . ']');
                error_log('Importing bigfathom_core_importjson_form_builder top level itemcount=' . count($alldata_ar));
                $debug=0;
                foreach($alldata_ar['databundle'] as $k=>$v)
                {
                    $debug++;
                    error_log("Importing bigfathom_core_importjson_form_builder ['databundle'] debug#$debug [$k]=[$v]");
                }
                $loaded = module_load_include('inc','bigfathom_core','io/IO');
                if(!$loaded)
                {
                    $errormsg = 'Failed to load the IO class';
                }
                $oIO = new \bigfathom\IO();
                $result_bundle = $oIO->importDataFromJSON($alldata_ar);        
            }
        } catch (\Exception $ex) {
            $hit_ex = $ex;
            $errormsg = "Failed import JSON because $ex";
            error_log($errormsg);
        }
        ob_end_clean();
        if(empty($errormsg))
        {
            //Create the output.
            $sJSON = json_encode($result_bundle);

            //Output the results.
            header("Content-Type: application/json");
            http_response_code(200);
            /*
            if(empty($errormsg))
            {
                http_response_code(200);
            } else {
                http_response_code(404);
            }
            */
            echo $sJSON;        
        }

        //Stop all processing.
        if(!empty($errormsg))
        {
            throw new \Exception($errormsg,99777,$hit_ex);
        }
        drupal_exit();  //Otherwise more stuff gets added to the output.
        
    } catch (\Exception $ex) {
        echo $ex->getMessage();        
        throw $ex;
    }
}

/**
 * Send a downloadable JSON file to the requester
 */
function bigfathom_core_downloadprojectinfo_form_builder($form, &$form_state)
{
    try 
    {
        global $user;
        if(empty($_GET['publishedrefname']))
        {
            throw new \Exception("The required publishedrefname argument is missing!");
        }
        $publishedrefname=$_GET['publishedrefname'];
        if(empty($_GET['format']))
        {
            $format = "JSON";
        } else {
            $format = strtoupper(trim($_GET['format']));
        }
        error_log("Exporting latest '$publishedrefname' project information in '$format' format as user=" . print_r($user,TRUE));

        $loaded = module_load_include('inc','bigfathom_core','io/IO');
        if(!$loaded)
        {
            drupal_set_message(t('Failed to load the IO class'), 'error');
        }
        $oIO = new \bigfathom\IO();
        $aExtraTopLevelAttribs = array();
        foreach ($_GET as $key => $value) 
        {
            $aExtraTopLevelAttribs[$key] = $value;
        }
        $aExtraTopLevelAttribs['format'] = $format;
        $sExtraContextNoteText=NULL;
        $sJSON = $oIO->getPublishedProjectInfoAsJSON($publishedrefname,$aExtraTopLevelAttribs,$sExtraContextNoteText);

        //Dump it.
        $now = date('Y-m-d H:i:s');
        
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        if($format == 'JSONP')
        {
            header("Content-Disposition: attachment; filename=bigfathom_projectinfo4{$publishedrefname}_{$now}.jsonp");
            $sJSON = "jsonpCallback(" . $sJSON . ")";
        } else {
            header("Content-Disposition: attachment; filename=bigfathom_projectinfo4{$publishedrefname}_{$now}.json");
        }
        //header("Content-Type: application/octet-stream; "); 
        header("Content-Type: application/javascript; charset=utf-8"); 
        header("X-Content-Type-Options: nosniff"); 
        //X-Content-Type-Options: nosniff
        //header("Content-Transfer-Encoding: binary");
        
        /*
        header("Cache-Control: public");
        //header("Content-Description: File Transfer");
        if($format == 'JSONP')
        {
            //header("Content-Disposition: attachment; filename=bigfathom_projectinfo4{$publishedrefname}_{$now}.jsonp");
            $sJSON = "jsonpCallback(" . $sJSON . ")";
        } else {
            //header("Content-Disposition: attachment; filename=bigfathom_projectinfo4{$publishedrefname}_{$now}.json");
        }
        header("Content-Type: text/plain; "); 
        */
        
        echo $sJSON;

        drupal_exit();  //Otherwise more stuff gets added to the file.
    } catch (\Exception $ex) {
        throw $ex;
    }
}

/**
 * Send a downloadable TAB TEXT file to the requester
 * publicname, lastpubdate, status_cd, due_dt, otsp
 */
function bigfathom_core_downloadprojectinfonamelist_form_builder($form, &$form_state)
{
    try 
    {
        global $user;
        error_log("Exporting latest projectinfonamelist in TEXT format as user=" . print_r($user,TRUE));

        $loaded = module_load_include('inc','bigfathom_core','io/IO');
        if(!$loaded)
        {
            drupal_set_message(t('Failed to load the IO class'), 'error');
        }
        $oIO = new \bigfathom\IO();
        $aExtraTopLevelAttribs = [];
        foreach ($_GET as $key => $value) 
        {
            $aExtraTopLevelAttribs[$key] = $value;
        }
        $sExtraContextNoteText=NULL;
        $sTEXTDATA = $oIO->getPublishedProjectInfoNameListAsTEXT($aExtraTopLevelAttribs,$sExtraContextNoteText);

        //Dump it.
        $now = date('Y-m-d H:i:s');
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=bigfathom_projectinfonamelist_{$now}.txt");
        header("Content-Type: text/plain; "); 

        echo $sTEXTDATA;

        drupal_exit();  //Otherwise more stuff gets added to the file.
    } catch (\Exception $ex) {
        throw $ex;
    }
}

/**
 * Send a downloadable JSON file to the requester
 */
function bigfathom_core_downloadtemplate_form_builder($form, &$form_state)
{
    try 
    {
        global $user;
        if(empty($_GET['publishedrefname']))
        {
            throw new \Exception("The required publishedrefname argument is missing!");
        }
        $publishedrefname=$_GET['publishedrefname'];
        if(empty($_GET['format']))
        {
            $format = "TABTEXT";
        } else {
            $format = strtoupper(trim($_GET['format']));
        }
        error_log("Exporting latest '$publishedrefname' template information in '$format' format as user=" . print_r($user,TRUE));

        $loaded = module_load_include('inc','bigfathom_core','io/IO');
        if(!$loaded)
        {
            drupal_set_message(t('Failed to load the IO class'), 'error');
        }
        $oIO = new \bigfathom\IO();
        $aExtraTopLevelAttribs = array();
        foreach ($_GET as $key => $value) 
        {
            $aExtraTopLevelAttribs[$key] = $value;
        }
        $aExtraTopLevelAttribs['format'] = $format;
        $sExtraContextNoteText=NULL;
        
        //Dump it.
        $now = date('Y-m-d H:i:s');
        header("Cache-Control: public");
        if($format == 'TABTEXT')
        {
            $sFILECONTENT = $oIO->getTemplateProjectInfoAsTABTEXT($publishedrefname,$aExtraTopLevelAttribs,$sExtraContextNoteText);
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=bigfathom_ptemplateinfo4{$publishedrefname}_{$now}.txt");
            header("Content-Type: text/plain; "); 
        } else {
            $sFILECONTENT = $oIO->getTemplateProjectInfoAsJSON($publishedrefname,$aExtraTopLevelAttribs,$sExtraContextNoteText);
            header("Content-Description: File Transfer");
            if($format == 'JSONP')
            {
                header("Content-Disposition: attachment; filename=bigfathom_ptemplateinfo4{$publishedrefname}_{$now}.jsonp");
                $sFILECONTENT = "jsonpCallback(" . $sFILECONTENT . ")";
            } else {
                header("Content-Disposition: attachment; filename=bigfathom_ptemplateinfo4{$publishedrefname}_{$now}.json");
            }
            header("Content-Type: application/octet-stream; "); 
            header("Content-Transfer-Encoding: binary");
        }

        echo $sFILECONTENT;

        drupal_exit();  //Otherwise more stuff gets added to the file.
    } catch (\Exception $ex) {
        throw $ex;
    }
}

