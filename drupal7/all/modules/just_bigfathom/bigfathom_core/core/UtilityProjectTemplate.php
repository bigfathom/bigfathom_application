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

/**
 * Description of ProjectTemplateHelper
 *
 * @author frank
 */
class UtilityProjectTemplate
{
    public static function getRawSections($raw_tabtext)
    {
        try
        {
            $tabtext = str_replace("\r\n", "\n", $raw_tabtext);

            $field_labels = [];
            $sections = [];
            $current_section_name = NULL;
            $grouping_name = NULL;
            $block_name = NULL;
            $field_name = NULL;
            $wid_col_offset = NULL;
            $insection_datarownum = -1;

            $allrows = explode("\n", $tabtext);
            for($i=0;$i<count($allrows);$i++)
            {
                $onerow = $allrows[$i];
                $fields = explode("\t", $onerow);
                if(count($fields) > 0 && trim(count($fields[0])) > '')
                {
                    $flag = trim($fields[0]);
                    if(strpos($flag,'DATA ') === 0)
                    {
                        //This is data
                        $insection_datarownum++;
                        if($current_section_name === NULL)
                        {
                            $current_section_name = "MISSING_SECTION_NAME";
                        }
                        $just_fieldvalues = array_slice($fields,1); 
                        if($grouping_name !== NULL)
                        {
                            $sections[$grouping_name][$current_section_name]['rows'][] = $just_fieldvalues;
                        } else {
                            $sections[$current_section_name]['rows'][] = $just_fieldvalues;
                        }
                        if($wid_col_offset !== NULL)
                        {
                            $wid = $just_fieldvalues[$wid_col_offset];
                            $sections[$current_section_name]['fastmap_wid2rowoffset'][$wid] = $insection_datarownum;
                        }
                    } else {
                        $insection_datarownum = -1;
                        $current_section_name = $flag;
                        if(strpos($flag,'END') === 0)
                        {
                            $sections[$block_name][$field_name] = trim($sections[$block_name][$field_name]);
                            $block_name = NULL;
                            $field_name = NULL;
                        }
                        
                        if(!empty($block_name))
                        {
                            $sections[$block_name][$field_name] .= $onerow;
                        } else {
                            if(strpos($flag,'BEGIN') === 0)
                            {
                                $block_name = $fields[1];
                                $field_name = $fields[2];
                                if(empty($field_name))
                                {
                                    throw new \Exception("Missing fieldname in " . print_r($fields,TRUE));
                                }
                                $sections[$block_name][$field_name] = '';
                            } else
                            if(strpos($flag,'MAP_') === 0)
                            {
                                $grouping_name = 'master_maps';
                            } else 
                            if(strpos($flag,'_SUBMAP_') !== FALSE)
                            {
                                $grouping_name = 'workitem_submaps';
                            } else {
                                $grouping_name = NULL;
                            }
                        }
                        
                        if($grouping_name !== NULL)
                        {
                            if(!isset($sections[$grouping_name]))
                            {
                                $sections[$grouping_name] = [];
                            }
                            $sections[$grouping_name][$current_section_name] = [];
                        } else {
                            $sections[$current_section_name] = [];
                        }
                        if(count($fields) > 1)
                        {
                            $field_labels = array_slice($fields,1);
                            if($grouping_name !== NULL)
                            {
                                $sections[$grouping_name][$current_section_name]['labels'] = $field_labels;
                            } else {
                                $sections[$current_section_name]['labels'] = $field_labels;
                            }
                        }
                        $wid_col_offset = NULL;
                        if($grouping_name !== NULL)
                        {
                            $sections[$grouping_name][$current_section_name]['rows'] = [];
                        } else {
                            $sections[$current_section_name]['rows'] = [];
                            if($current_section_name === 'WORKITEMS')
                            {
                                for($j=0;$j<count($field_labels);$j++)
                                {
                                    if($field_labels[$j] === 'WID')
                                    {
                                        $wid_col_offset = $j;
                                        break;
                                    }
                                }
                                $sections[$current_section_name]['fastmap_wid2rowoffset'] = [];
                            }
                        }
                    }
                }
            }
            return $sections;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Return a handy bundle of content
     */
    public static function convertProjectTemplateTabText2Bundle($tabtext)
    {
        try
        {
            $raw_sections = self::getRawSections($tabtext);
            $parsed = [];
            if(empty($raw_sections['PROJECT_TEMPLATE']))
            {
                throw new \Exception("Missing PROJECT_TEMPLATE section!");
            }

            $ptmetadata_labels = $raw_sections['PROJECT_TEMPLATE']['labels'];
            $ptmetadata_values = $raw_sections['PROJECT_TEMPLATE']['rows'][0];
            $parsed['metadata'] = UtilityGeneralFormulas::getAsKeyValuePairs($ptmetadata_labels, $ptmetadata_values);
            if(!empty($raw_sections['METADATA']))
            {
                foreach($raw_sections['METADATA'] as $fn=>$fv)
                {
                    $parsed['metadata'][$fn] = $fv;
                }
            }            
            
            $parsed['workitems'] = $raw_sections['WORKITEMS'];
            $parsed['workitem_submaps'] = $raw_sections['workitem_submaps'];
            $parsed['master_maps'] = $raw_sections['master_maps'];
            return $parsed;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

}
