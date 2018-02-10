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
class DebugHelper
{
    public static function showStackTrace($title='Stack Trace', $msgcat='error', $limit=0)
    {
        $e = new \Exception();
        //var_dump($e->getTraceAsString());
        self::showMyValues($e->getTrace(), $title, $msgcat, $limit);
    }
    
    private static function showMyValues($myvalues, $title=NULL, $msgcat='info', $limit=0)
    {
        if(!is_array($myvalues))
        {
            drupal_set_message("$title value debug dump=" . print_r($myvalues,TRUE));         
        } else {
            $item_counter = 0;
            $pairs = array();
            foreach($myvalues as $k=>$v)
            {
                $item_counter++;
                if($limit > 0 && $item_counter > $limit)
                {
                    $pairs[] = "###HIT_LIMIT###=Stopped printing at limit=$limit items!";
                    break;
                }
                if(is_array($v))
                {
                    $pairs[] = "$k=" . print_r($v,TRUE);
                } else {
                    $pairs[] = "$k=$v";
                }
            }
            drupal_set_message("$title associative array debug dump...<ol><li>" . implode("<li>", $pairs) . "</ol>", $msgcat);            
        }
    }
    
    public static function getStackTraceMarkup($title='Stack Trace', $limit=0)
    {
        $e = new \Exception();
        //var_dump($e->getTraceAsString());
        return self::getMyValuesMarkup($e->getTrace(), $title, $limit);
    }
    
    private static function getMyValuesMarkup($myvalues, $title=NULL, $limit=0)
    {
        if(!is_array($myvalues))
        {
            drupal_set_message("$title value debug dump=" . print_r($myvalues,TRUE));         
        } else {
            $item_counter = 0;
            $pairs = array();
            foreach($myvalues as $k=>$v)
            {
                $item_counter++;
                if($limit > 0 && $item_counter > $limit)
                {
                    $pairs[] = "###HIT_LIMIT###=Stopped printing at limit=$limit items!";
                    break;
                }
                if(is_array($v))
                {
                    $pairs[] = "$k=" . print_r($v,TRUE);
                } else {
                    $pairs[] = "$k=$v";
                }
            }
            return "$title associative array debug dump...<ol><li>" . implode("<li>", $pairs) . "</ol>";          
        }
    }
    
    public static function showNeatMarkup($myvalues,$heading_text=NULL,$messagetype='info')
    {
        if($heading_text === NULL)
        {
            $extrainfo = self::getStackTraceMarkup($title='Stack Trace', 2);
            $heading_text='NEATLY PRINTED DEBUG "SHOW" VALUES FROM ' . print_r($extrainfo,TRUE);
        }
        $prefixmarkup = "$heading_text ........................";
        $suffixmarkup = "........................ $heading_text";
        self::debugPrintNeatly($myvalues, FALSE, $prefixmarkup, $suffixmarkup, $messagetype);
    }

    public static function getNeatMarkup($myvalues,$heading_text=NULL,$messagetype='info')
    {
        if($heading_text === NULL)
        {
            $extrainfo = self::getStackTraceMarkup($title='Stack Trace', 2);
            $heading_text='NEATLY PRINTED DEBUG "GET" VALUES FROM ' . print_r($extrainfo,TRUE);
        }
        $prefixmarkup = "$heading_text ........................";
        $suffixmarkup = "........................ $heading_text";
        return self::debugPrintNeatly($myvalues, TRUE, $prefixmarkup, $suffixmarkup, $messagetype);
    }
    
    public static function getNeatTextMarkup($myvalues,$heading_text=NULL,$messagetype='info')
    {
        if($heading_text === NULL)
        {
            $extrainfo = self::getStackTraceMarkup($title='Stack Trace', 2);
            $heading_text='NEATLY PRINTED TEXT DEBUG VALUES FROM ' . print_r($extrainfo,TRUE);
        }
        $prefixmarkup = "$heading_text ........................";
        $suffixmarkup = "........................ $heading_text";
        return self::debugPrintTextNeatly($myvalues, TRUE, $prefixmarkup, $suffixmarkup, $messagetype);
    }
    
    public static function debugPrintNeatly($myvalues,$return_markup_as_string=FALSE,$prefixmarkup='NEATLY PRINTED DEBUG VALUES',$suffixmarkup='',$messagetype='info')
    {
        $thismarkup = "$prefixmarkup";
        if(!is_array($myvalues))
        {
            $thismarkup .= print_r($myvalues,TRUE);    
        } else {
            $thismarkup .= "<ol>";
            $tmpmarkup = '';
            foreach($myvalues as $k=>$detail)
            {
                if(!is_array($detail))
                {
                    $tmpmarkup .= "<li>$k=".print_r($detail,TRUE)."</li>";
                } else {
                    //We have an array
                    $array_len = count($detail);
                    $tmpmarkup .= "<li>$k (size=$array_len)";
                    if($array_len > 0)
                    {
                        $tmpmarkup .= "...<ol>";
                        foreach($detail as $dk=>$dv)
                        {
                            if(is_array($dv))
                            {
                                $tmpmarkup .= self::debugPrintNeatly($dv,TRUE,"<li>$dk<ol>","</ol></li>");
                            } else {
                                if(!is_object($dk))
                                {
                                    $dk_tx = "$dk";
                                } else {
                                    $dk_tx = print_r($dk,TRUE);
                                }
                                if(!is_object($dv))
                                {
                                    $dv_tx = "$dv";
                                } else {
                                    $dv_tx = print_r($dv,TRUE);
                                }
                                $tmpmarkup .= "<li>$dk_tx=[$dv_tx]</li>";
                            }
                        }
                        $tmpmarkup .= "</li></ol>";
                    }
                    $tmpmarkup .= "</li>";
                }
            }
            $thismarkup .= $tmpmarkup;
            $thismarkup .= "</ol>";
        }
        $thismarkup .= "$suffixmarkup";
        if($return_markup_as_string)
        {
            return $thismarkup;
        } else {
            drupal_set_message($thismarkup,$messagetype);
        }
    }
    
    public static function debugPrintTextNeatly($myvalues,$return_markup_as_string=FALSE,$prefixmarkup='NEATLY PRINTED TEXT DEBUG VALUES',$suffixmarkup='',$messagetype='info',$rowprefixtxt='')
    {
        $thismarkup = "$prefixmarkup";
        if(!is_array($myvalues))
        {
            $thismarkup .= print_r($myvalues,TRUE);    
        } else {
            $thismarkup .= "[LIST]";
            $tmpmarkup = '';
            foreach($myvalues as $k=>$detail)
            {
                if(!is_array($detail))
                {
                    $tmpmarkup .= "\n{$rowprefixtxt}\t$k=".print_r($detail,TRUE)."\n";
                } else {
                    //We have an array
                    $array_len = count($detail);
                    $tmpmarkup .= "\n{$rowprefixtxt}\t[ARRAY]$k (size=$array_len)";
                    if($array_len > 0)
                    {
                        $tmpmarkup .= "...\n";
                        foreach($detail as $dk=>$dv)
                        {
                            if(is_array($dv))
                            {
                                $tmpmarkup .= self::debugPrintTextNeatly($dv,TRUE,"\n\t$dk\n","[/ARRAY]\n",$messagetype,$rowprefixtxt."\t");
                            } else {
                                if(!is_object($dk))
                                {
                                    $dk_tx = "$dk";
                                } else {
                                    $dk_tx = print_r($dk,TRUE);
                                }
                                if(!is_object($dv))
                                {
                                    $dv_tx = "$dv";
                                } else {
                                    $dv_tx = print_r($dv,TRUE);
                                }
                                $tmpmarkup .= "\n\t$dk_tx=[$dv_tx]\n";
                            }
                        }
                        $tmpmarkup .= "\n{$rowprefixtxt}[/ARRAY]";
                    }
                    $tmpmarkup .= "\n";
                }
            }
            $thismarkup .= $tmpmarkup;
            $thismarkup .= "[/LIST]";
        }
        $thismarkup .= "$suffixmarkup";
        if($return_markup_as_string)
        {
            return $thismarkup;
        } else {
            drupal_set_message($thismarkup,$messagetype);
        }
    }
    
    public static function getLargeTextInChunks($large_text, $chunksize=500)
    {
        $chunks = [];
        $index=0;
        while((strlen($large_text)-$index)>$chunksize)
        {
            $chunks[] = substr($large_text, $index, $chunksize);
            $index += $chunksize;
        }
        $chunks[] = substr($large_text, $index);    //Grab the last bit now
        return $chunks;
    }
    
}

