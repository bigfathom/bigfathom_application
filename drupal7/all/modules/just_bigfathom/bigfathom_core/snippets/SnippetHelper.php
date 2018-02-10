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
 */

namespace bigfathom;

/**
 * This class is for helping with code snippets at runtime
 *
 * @author Frank Font
 */
class SnippetHelper
{
    private function getFilePath($filename)
    {
        try
        {
            return drupal_get_path('module', 'bigfathom_core').'/snippets/files/'.$filename;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble getting path of $filename because $ex",99111,$ex);
        }
    }
    
    public function getHtmlSnippet($filename_root)
    {
        try
        {
            $filepath = $this->getFilePath("$filename_root.html");
            $snippet = file_get_contents($filepath);
            return $snippet;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble opening html $filename_root because $ex",99112,$ex);
        }
    }

    public function getJavascriptSnippet($filename_root)
    {
        try
        {
            $filepath = $this->getFilePath("$filename_root.js");
            $snippet = file_get_contents($filepath);
            return $snippet;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble opening js $filename_root because $ex",99118,$ex);
        }
    }
    
    public function getPhpSnippet($filename_root)
    {
        try
        {
            $filepath = $this->getFilePath("$filename_root.php");
            $snippet = file_get_contents($filepath);
            return $snippet;
        } catch (\Exception $ex) {
            throw new \Exception("Trouble opening php $filename_root because $ex",99119,$ex);
        }
    }
    
}