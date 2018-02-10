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
 */

namespace bigfathom;

/**
 * This class helps project planning values 
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class DirectedGraphAnalysis
{
    /**
     * Figures out important features of our work network
     */
    public function getNetworkMapBundle($projectid, &$all_workitems, $sprint_maps)
    {
        try
        {
            $metadata = [];
            $min_locked_start_dt = NULL;
            $max_locked_end_dt = NULL;
            $locked_root_start_dt = NULL;
            $locked_root_end_dt = NULL;
            $all_key_map = [];
            $all_nonleaf_map = [];
            $connected_nonleaf_map = [];
            $disconnected_nonleaf_map = [];
            $connected_leaf_map = [];
            $disconnected_leaf_map = [];
            $all_leaf_map = [];
            $root_wid = NULL;
            $map_workitem2daw = [];
            $map_workitem2ddw = [];
            $map_workitem2locks = [];
            $map_all_memberids = [];
            $map_ant_project_rootwid = [];
            $map_sprintinfo = [];
            $map_workitem2sprint = $sprint_maps['workitem2sprint'];
            foreach($sprint_maps['sprintid2status'] as $sid=>$detail)
            {
                $sdinfo = $sprint_maps['sprintid2dates'][$sid];
                $detail['sdt'] = $sdinfo['sdt'];
                $detail['edt'] = $sdinfo['edt'];
                $map_sprintinfo[$sid] = $detail;
            }

            foreach($all_workitems as $wid=>$one_workitem)
            {
                if(empty($wid))
                {
                    throw new \Exception("Key is empty instead of wid value for ".print_r($one_workitem,TRUE));
                }
                if(empty($all_workitems[$wid]['owner_projectid']))
                {
                    //Assumed owned by this project
                    $opid = $projectid;
                } else {
                    $opid = $all_workitems[$wid]['owner_projectid'];
                }
                if($opid !== $projectid)
                {
                    //This is from an ANT project
                    if(!empty($all_workitems[$wid]['sdt']))
                    {
                        $ap_sdt = $all_workitems[$wid]['sdt'];
                    } else {
                        $ap_sdt = empty($all_workitems[$wid]['actual_start_dt']) ? $all_workitems[$wid]['planned_start_dt'] : $all_workitems[$wid]['actual_start_dt'];
                    }
                    if(!empty($all_workitems[$wid]['edt']))
                    {
                        $ap_edt = $all_workitems[$wid]['edt'];
                    } else {
                        $ap_edt = empty($all_workitems[$wid]['actual_end_dt']) ? $all_workitems[$wid]['planned_end_dt'] : $all_workitems[$wid]['actual_end_dt'];
                    }
                    $map_ant_project_rootwid[$wid] = array('wid'=>$wid,'opid'=>$opid, 'sdt'=>$ap_sdt, 'edt'=>$ap_edt);
                    $all_workitems[$wid]['visited'] = 1;
                    $all_workitems[$wid]['is_connected_to_root_tree'] = 1;
                    $isleaf = TRUE;
                } else {
                    //Assume it is from this project
                    if(!empty($all_workitems[$wid]['root_of_projectid']))
                    {
                        //Done
                        $all_workitems[$wid]['visited'] = 1;
                        $all_workitems[$wid]['is_connected_to_root_tree'] = 1;
                    } else {
                        //Just initialize
                        $all_workitems[$wid]['visited'] = 0;
                        $all_workitems[$wid]['is_connected_to_root_tree'] = 0;
                    }
                    $daw_list = $one_workitem['maps']['daw'];
                    $isleaf = TRUE;
                    foreach($daw_list as $daw)
                    {
                        $ant_opid = $all_workitems[$daw]['owner_projectid'];
                        if($ant_opid == $projectid)
                        {
                            $isleaf = FALSE;
                            break;
                        }
                    }
                    if(!empty($one_workitem['root_of_projectid']))
                    {
                        $root_wid = $wid;
                    }
                }
                if($isleaf)
                {
                    $all_leaf_map[$wid] = $wid;
                }
            }

            foreach($all_leaf_map as $leafwid)
            {
                $isconnected = $this->setConnectedFlag($all_workitems,$leafwid);
                if($isconnected)
                {
                    $connected_leaf_map[$leafwid] = $leafwid;
                    $all_key_map[$leafwid] = array('leaf','connected');
                } else {
                    $disconnected_leaf_map[$leafwid] = $leafwid;
                    $all_key_map[$leafwid] = array('leaf','disconnected');
                }
            }
            foreach($all_workitems as $wid=>$one_workitem)
            {
                $owner_projectid = !empty($one_workitem['owner_projectid']) ? $one_workitem['owner_projectid'] : $projectid;
                $map_all_memberids[$wid] = $wid;

                if(!isset($all_leaf_map[$wid]))
                {
                    $all_nonleaf_map[$wid] = $wid;
                    if($one_workitem['is_connected_to_root_tree'])
                    {
                        $connected_nonleaf_map[$wid] = $wid;
                        $all_key_map[$wid] = array('nonleaf','connected');
                    } else {
                        $disconnected_nonleaf_map[$wid] = $wid;
                        $all_key_map[$wid] = array('nonleaf','disconnected');
                    }
                    $map_workitem2daw[$wid] = self::getAsMap($one_workitem['maps']['daw']);
                }
                $map_workitem2ddw[$wid] = self::getAsMap($one_workitem['maps']['ddw']);

                if($owner_projectid != $projectid)
                {
                    //Treat dates of ant project as locked
                    $map_workitem2locks[$wid]['sdt_locked_yn'] = 1;
                    $map_workitem2locks[$wid]['edt_locked_yn'] = 1;
                } else {
                    if(isset($one_workitem['sdt_locked_yn']))
                    {
                        $is_locked_start_dt_locked_yn = $one_workitem['sdt_locked_yn'];
                    } else {
                        $is_locked_start_dt_locked_yn = ($one_workitem['planned_start_dt_locked_yn'] == 1 || !empty($one_workitem['actual_start_dt'])) ? 1 : 0;
                    }
                    if(isset($one_workitem['edt_locked_yn']))
                    {
                        $is_locked_end_dt_locked_yn = $one_workitem['edt_locked_yn'];
                    } else {
                        $is_locked_end_dt_locked_yn = ($one_workitem['planned_end_dt_locked_yn'] == 1 || !empty($one_workitem['actual_end_dt'])) ? 1 : 0;
                    }
                    $map_workitem2locks[$wid]['sdt_locked_yn'] = $is_locked_start_dt_locked_yn;
                    $map_workitem2locks[$wid]['edt_locked_yn'] = $is_locked_end_dt_locked_yn;

                    if($is_locked_start_dt_locked_yn)
                    {
                        if($wid == $root_wid)
                        {
                            if(!empty($one_workitem['sdt']))
                            {
                                $locked_root_start_dt = $one_workitem['sdt'];
                            } else
                            if(!empty($one_workitem['actual_start_dt']))
                            {
                                $locked_root_start_dt = $one_workitem['actual_start_dt'];
                            } else
                            if(!empty($one_workitem['planned_start_dt']))
                            {
                                $locked_root_start_dt = $one_workitem['planned_start_dt'];
                            }
                        }
                        if(!empty($one_workitem['sdt']) && (empty($min_locked_start_dt) || $min_locked_start_dt > $one_workitem['sdt']))
                        {
                            $min_locked_start_dt = $one_workitem['sdt'];
                        } else
                        if(!empty($one_workitem['actual_end_dt']) && (empty($min_locked_start_dt) || $min_locked_start_dt > $one_workitem['actual_start_dt']))
                        {
                            $min_locked_start_dt = $one_workitem['actual_start_dt'];
                        } else
                        if(!empty($one_workitem['planned_end_dt']) && (empty($min_locked_start_dt) || $min_locked_start_dt > $one_workitem['planned_start_dt']))
                        {
                            $min_locked_start_dt = $one_workitem['planned_start_dt'];
                        }
                    }
                    if($is_locked_end_dt_locked_yn)
                    {
                        if($wid == $root_wid)
                        {
                            if(!empty($one_workitem['edt']))
                            {
                                $locked_root_end_dt = $one_workitem['edt'];
                            } else
                            if(!empty($one_workitem['actual_end_dt']))
                            {
                                $locked_root_end_dt = $one_workitem['actual_end_dt'];
                            } else
                            if(!empty($one_workitem['planned_end_dt']))
                            {
                                $locked_root_end_dt = $one_workitem['planned_end_dt'];
                            }
                        }
                        if(!empty($one_workitem['edt']) && (empty($max_locked_end_dt) || $max_locked_end_dt < $one_workitem['edt']))
                        {
                            $max_locked_end_dt = $one_workitem['actual_end_dt'];
                        } else
                        if(!empty($one_workitem['actual_end_dt']) && (empty($max_locked_end_dt) || $max_locked_end_dt < $one_workitem['actual_end_dt']))
                        {
                            $max_locked_end_dt = $one_workitem['actual_end_dt'];
                        } else
                        if(!empty($one_workitem['planned_end_dt']) && (empty($max_locked_end_dt) || $max_locked_end_dt < $one_workitem['planned_end_dt']))
                        {
                            $max_locked_end_dt = $one_workitem['planned_end_dt'];
                        }
                    }
                }
            }

            $root_wdetail = $all_workitems[$root_wid];
            if(!empty($root_wdetail['sdt']))
            {
                $root_sdt = $root_wdetail['sdt'];
            } else {
                $root_sdt = !empty($root_wdetail['actual_start_dt']) ? $root_wdetail['actual_start_dt'] : $root_wdetail['planned_start_dt'];
            }
            if(!empty($root_wdetail['edt']))
            {
                $root_edt = $root_wdetail['edt'];
            } else {
                $root_edt = !empty($root_wdetail['actual_endt_dt']) ? $root_wdetail['actual_end_dt'] : $root_wdetail['planned_end_dt'];
            }

            $metadata['in_project'] = [];
            $metadata['in_project']['min_locked_start_dt'] = $min_locked_start_dt;
            $metadata['in_project']['max_locked_end_dt'] = $max_locked_end_dt;
            $metadata['in_project']['min_locked_root_start_dt'] = $locked_root_start_dt;
            $metadata['in_project']['max_locked_root_end_dt'] = $locked_root_end_dt;

            $bundle = [];
            $bundle['metadata'] = $metadata;
            $bundle['root']['projectid'] = $projectid;
            $bundle['root']['wid'] = $root_wid;
            $bundle['root']['sdt'] = $root_sdt; //Useful downstream if locked
            $bundle['root']['edt'] = $root_edt; //Useful downstream if locked
            $bundle['all']['keys'] = $all_key_map;
            $bundle['leaf']['all'] = $all_leaf_map;
            $bundle['leaf']['connected'] = $connected_leaf_map;
            $bundle['leaf']['disconnected'] = $disconnected_leaf_map;
            $bundle['nonleaf']['all'] = $all_nonleaf_map;
            $bundle['nonleaf']['connected'] = $connected_nonleaf_map;
            $bundle['nonleaf']['disconnected'] = $disconnected_nonleaf_map;
            $bundle['map']['sprints'] = $map_sprintinfo;
            $bundle['map']['workitem2sprint'] = $map_workitem2sprint;
            $bundle['map']['workitem2daw'] = $map_workitem2daw;
            $bundle['map']['workitem2ddw'] = $map_workitem2ddw;
            $bundle['map']['workitem2locks'] = $map_workitem2locks;
            $bundle['map']['all_members'] = $map_all_memberids;
            $bundle['map']['ant_project_rootwid'] = $map_ant_project_rootwid;

            if(empty($root_wid))
            {
                throw new \Exception("Missing root_of_projectid setting in winfo collection!" . print_r($all_workitems,TRUE));
            }

            return $bundle;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Call this for each of the leaves
     * Works its way up for all dependents, writing to each.
     */
    private function setConnectedFlag(&$all_computed_workitems,$currentwid)
    {
        if(!empty($all_computed_workitems[$currentwid]['root_of_projectid']))
        {
            $all_computed_workitems[$currentwid]['is_connected_to_root_tree'] = 1;
            return TRUE;
        }
        $one_computed_workitem = $all_computed_workitems[$currentwid];
        $ddw_list = $one_computed_workitem['maps']['ddw'];
        $hitconnection = FALSE;
        foreach($ddw_list as $depwid)
        {
            if(isset($all_computed_workitems[$depwid]['is_connected_to_root_tree']) 
                    && $all_computed_workitems[$depwid]['is_connected_to_root_tree'])
            {
                $hitconnection = TRUE;
            } else {
                if($this->setConnectedFlag($all_computed_workitems,$depwid))
                {
                    $hitconnection = TRUE;
                }
            }
            if($hitconnection)
            {
                $all_computed_workitems[$currentwid]['visited'] = 1;
                $all_computed_workitems[$currentwid]['is_connected_to_root_tree'] = 1;
            }
        }
        return $hitconnection;
    }
    
    private static function getAsMap($myarray)
    {
        $mymap = [];
        foreach($myarray as $myvalue)
        {
            $mymap[$myvalue] = $myvalue;
        }
        return $mymap;
    }
    
    
}
