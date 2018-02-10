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

namespace bigfathom_template_library;

/**
 * This class is for database schema content
 *
 * @author Frank Font
 */
class DemoData
{
    private function importTemplateFiles()
    {
        $install_realpath = dirname(__FILE__);
        $install_templates_realpath = "$install_realpath/templates";

        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $oWriteHelper = new \bigfathom\WriteHelper();
        
        $templates_ar = scandir($install_templates_realpath);
        $core_template_count = 0;
        foreach($templates_ar as $filename)
        {
            try
            {
                if(strlen($filename)>2)
                {
                    $file_path = "$install_templates_realpath/$filename";
                    $file_contents = file_get_contents($file_path,FALSE);
                    $parsed_file = \bigfathom\UtilityProjectTemplate::convertProjectTemplateTabText2Bundle($file_contents);
                    $resultbundle = $oWriteHelper->createTemplateFromImport($parsed_file);
                    $core_template_count++;
                }
            } catch (\Exception $ex) {
                drupal_set_message("Failed to load $filename",'error');
            }
        }
        drupal_set_message($resultbundle['message']);
        drupal_set_message("Load of $core_template_count core project templates completed!");
        
    }
    
    public function load()
    {
        try
        {
            
            $this->importTemplateFiles();
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }    
}
    