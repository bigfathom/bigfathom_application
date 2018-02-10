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
 * Help with node linking logic
 * 
 * @author Frank Font of Room4me.com Software LLC
 */
class LinkLogicHelper
{
    protected $m_oContext = NULL;
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
    }

    private function parseJSONValueAsBoolean($value)
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return $bool;
    }
    
    public function getLinksNewInUpload($existing_fast_target_lookup, $raw_uploaded_links)
    {
        try
        {
            $result_bundle = array();
            
            $new_links = array();
            $uploaded_fast_target_lookup = array();
            $uploaded_fast_target_sources_lookup = array();
            $candidate_nodes = array();
                    
            foreach($raw_uploaded_links as $link_key=>$onelink)
            {
                $source_node = $onelink['source'];
                $target_node = $onelink['target'];
                $native_source_id = $source_node['nativeid'];
                $native_target_id = $target_node['nativeid'];
                $source_typeprefix = substr($source_node['type'],0,1);
                $target_typeprefix = substr($target_node['type'],0,1);
                $encoded_source_id = "$source_typeprefix$native_source_id";
                $encoded_target_id = "$target_typeprefix$native_target_id";
                
                $source_is_candidate = $this->parseJSONValueAsBoolean($source_node['is_candidate']);
                if($source_is_candidate)
                {
                    $encoded_source_id = "c" . $encoded_source_id;  //Because values MIGHT otherwise overlap!!!
                    $candidate_nodes[$encoded_source_id] = $native_source_id;
                }
                
                $target_is_candidate = $this->parseJSONValueAsBoolean($target_node['is_candidate']);
                if($target_is_candidate)
                {
                    $encoded_target_id = "c" . $encoded_target_id;  //Because values MIGHT otherwise overlap!!!
                    $candidate_nodes[$encoded_target_id] = $native_target_id;
                }
                
                //Build the fast lookup structure
                if(!array_key_exists($encoded_target_id, $uploaded_fast_target_lookup))
                {
                    $uploaded_fast_target_lookup[$encoded_target_id] = array();
                }
                $uploaded_fast_target_sources_lookup = &$uploaded_fast_target_lookup[$encoded_target_id];
                if(!array_key_exists($encoded_source_id, $uploaded_fast_target_sources_lookup))
                {
                    $uploaded_fast_target_sources_lookup[$encoded_source_id] = $encoded_source_id;
                }
                
                //Now, determine if this is a new link.
                $addnew = TRUE;
                if(array_key_exists($encoded_target_id, $existing_fast_target_lookup))
                {
                    $sources = $existing_fast_target_lookup[$encoded_target_id];
                    if(array_key_exists($encoded_source_id, $sources))
                    {
                        $addnew = FALSE;
                    }
                }
                if($addnew)
                {
                    $linktypeprefix = "{$source_typeprefix}2{$target_typeprefix}";
                    $new_links_key = "{$linktypeprefix}_" . count($new_links);
                    $new_links[$new_links_key] = array('targetid'=>$encoded_target_id,'sourceid'=>$encoded_source_id);
                }
            }
            
            //Populate the result bundle
            $result_bundle['new_links'] = $new_links;
            $result_bundle['uploaded_fast_target_lookup'] = $uploaded_fast_target_lookup;
            $result_bundle['uploaded_fast_target_sources_lookup'] = $uploaded_fast_target_sources_lookup;
            $result_bundle['candidate_nodes'] = $candidate_nodes;
            return $result_bundle;
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }
    
    private function addAsDeleteLinkMembers(&$deleted_links, $existing_encoded_target_id, $existing_sources)
    {
        //Remove all of these!
        $target_typeprefix = substr($existing_encoded_target_id,0,1);
        foreach($existing_sources as $one_existing_source)
        {
            $source_typeprefix = substr($one_existing_source,0,1);
            $linktypeprefix = "{$source_typeprefix}2{$target_typeprefix}";
            $deleted_link_key = "{$linktypeprefix}_" . count($deleted_links);
            $deleted_links[$deleted_link_key] 
                    = array('targetid'=>$existing_encoded_target_id,'sourceid'=>$one_existing_source);
        }
    }
    
    public function getLinksNotUploaded($existing_fast_target_lookup, $uploaded_fast_target_lookup)
    {
        try
        {
            //Determine which links are DELETED
            $deleted_links = array();
            foreach($existing_fast_target_lookup as $existing_encoded_target_id=>$existing_sources)
            {
                //Was this link uploaded too?
                if(array_key_exists($existing_encoded_target_id, $uploaded_fast_target_lookup))
                {
                    //Look for members to remove
                    $uploaded_sources = $uploaded_fast_target_lookup[$existing_encoded_target_id];
                    foreach($existing_sources as $one_existing_source)
                    {
                        if(!array_key_exists($one_existing_source, $uploaded_sources))
                        {
                            $source_typeprefix = substr($one_existing_source,0,1);
                            $target_typeprefix = substr($existing_encoded_target_id,0,1);
                            $linktypeprefix = "{$source_typeprefix}2{$target_typeprefix}";
                
                            $deleted_link_key = "{$linktypeprefix}_" . count($deleted_links);
                            $deleted_links[$deleted_link_key] 
                                    = array('targetid'=>$existing_encoded_target_id,'sourceid'=>$one_existing_source);
                        }
                    }
                } else {
                    //Remove all of these!
                    $this->addAsDeleteLinkMembers($deleted_links, $existing_encoded_target_id, $existing_sources);
                }
            }
            return $deleted_links;
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

