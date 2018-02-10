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
require_once 'MapHelper.php';
require_once 'WorkApportionmentHelper.php';

/**
 * This class helps get critical path type information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectInsight
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oWAH = NULL;
    private $m_flags_ar = NULL;
    
    private $m_today_dt = NULL;
    private $m_default_start_dt = NULL;
    private $m_default_end_dt = NULL;
    
    private $m_projectid = NULL;
    private $m_root_goalid = NULL;
    
    public function __construct($projectid, $default_start_dt=NULL, $default_end_dt=NULL, $flags_ar=NULL, $today_dt=NULL)
    {
        if(empty($projectid))
        {
            throw new \Exception("Missing required projectid!");
        }
        
        if(empty($flags_ar) || !is_array($flags_ar))
        {
            $flags_ar = [];
        }
        if(empty($today_dt))
        {
            $now_timestamp = time();
            $today_dt = gmdate("Y-m-d",$now_timestamp);
        }
        $this->m_today_dt = $today_dt;
        if(empty($default_start_dt))
        {
            $default_start_dt = $this->m_today_dt;
        }
        $this->m_default_start_dt = $default_start_dt;
        if(empty($default_end_dt))
        {
            $default_end_dt = \bigfathom\UtilityGeneralFormulas::getDayShiftedDateAsISO8601DateText($default_start_dt, 720);
        }        
        $this->m_default_end_dt = $default_end_dt;
        
        $this->m_projectid = $projectid;
        $this->m_flags_ar = $flags_ar;
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oWAH = new \bigfathom\WorkApportionmentHelper();
        $loaded_forecast = module_load_include('php','bigfathom_forecast','core/ProjectForecaster');
        if(!$loaded_forecast)
        {
            throw new \Exception('Failed to load the ProjectForecaster class');
        }
        $this->m_oProjectForecaster = new \bigfathom_forecast\ProjectForecaster($this->m_projectid); 
        $this->m_projectforecast = $this->m_oProjectForecaster->getDetail();
        $this->m_metadata = $this->m_projectforecast['main_project_detail']['metadata'];
        $this->m_project_workitems = $this->m_projectforecast['main_project_detail']['workitems'];
        $this->m_project_staticleafmap = $this->m_projectforecast['main_project_detail']['leafmap'];
        $this->m_status_lookup = $this->m_projectforecast['main_project_detail']['status_lookup'];
        $this->m_sprint_lookup = $this->m_projectforecast['main_project_detail']['sprint_lookup'];
        $this->m_people_maps = $this->m_projectforecast['main_project_detail']['people_maps'];
        $this->m_project_effectiveleafmap = $this->getAllEffectiveLeafNodes();
        $this->m_all_bare_p2p_maps = $this->m_oMapHelper->getAllBareProject2ProjectMaps();

        $this->m_root_goalid = $this->m_metadata['root_goalid'];
    }

    private function getProject2ProjectRelationshipsMap()
    {
        $bundle = [];
        $all_bare_maps = $this->m_all_bare_p2p_maps;
        
        $dap = isset($this->m_all_bare_p2p_maps['p2sp'][$this->m_projectid]) ? $this->m_all_bare_p2p_maps['p2sp'][$this->m_projectid] : [];
        $ddp = isset($this->m_all_bare_p2p_maps['sp2p'][$this->m_projectid]) ? $this->m_all_bare_p2p_maps['sp2p'][$this->m_projectid] : [];
        
        $ants = $this->getAllP2PAnts($this->m_projectid);
        $deps = $this->getAllP2PDeps($this->m_projectid);
        
        $bundle['all_bare_maps'] = $all_bare_maps;
        $bundle['dap'] = $dap;
        $bundle['ddp'] = $ddp;
        $bundle['ants'] = $ants;
        $bundle['deps'] = $deps;
        
        return $bundle;
    }
    
    private function getAllP2PAnts($oneprojectid)
    {
        $dap = isset($this->m_all_bare_p2p_maps['p2sp'][$oneprojectid]) ? $this->m_all_bare_p2p_maps['p2sp'][$oneprojectid] : [];
        $ants = $dap;
        $branches = [];
        foreach($dap as $onedap)
        {
            $branches[] = $this->getAllP2PAnts($onedap);
        }
        foreach($branches as $onebranch)
        {
            foreach($onebranch as $oneant)
            {
                $ants[$oneant] = $oneant;
            }
        }
        return $ants;
    }
    
    private function getAllP2PDeps($oneprojectid)
    {
        $ddp = isset($this->m_all_bare_p2p_maps['sp2p'][$oneprojectid]) ? $this->m_all_bare_p2p_maps['sp2p'][$oneprojectid] : [];
        $deps = $ddp;
        $branches = [];
        foreach($ddp as $oneddp)
        {
            $branches[] = $this->getAllP2PDeps($oneddp);
        }
        foreach($branches as $onebranch)
        {
            foreach($onebranch as $onedep)
            {
                $deps[$onedep] = $onedep;
            }
        }
        return $deps;
    }
    
    private function getWorkitemRelationshipsMap($paths2root)
    {
        $pass1_richmaps = [];
        foreach($paths2root as $onepathinfo)
        {
            $path = $onepathinfo['path'];
            $ants_sofar = [];
            foreach($path as $wid)
            {
                if(count($ants_sofar)>0)
                {
                    $pass1_richmaps[$wid]['ants'][] = $ants_sofar;
                }
                $ants_sofar[$wid] = $wid;
            }
            $reverse_path = array_reverse($path);
            $deps_sofar = [];
            foreach($reverse_path as $wid)
            {
                if(count($deps_sofar)>0)
                {
                    $pass1_richmaps[$wid]['deps'][] = $deps_sofar;
                }
                $deps_sofar[$wid] = $wid;
            }
        }
        $pass2_richmaps = [];
        foreach($pass1_richmaps as $wid=>$links)
        {
            $pass2_richmaps[$wid]['deps'] = [];
            if(!empty($links['deps']))
            {
                $oneset_deps = $links['deps'];
                foreach($oneset_deps as $onepath_deps)
                {
                    foreach($onepath_deps as $onewid)
                    {
                        if($wid != $onewid)
                        {
                            $pass2_richmaps[$wid]['deps'][$onewid] = $onewid;
                        }
                    }
                }
            }
            $pass2_richmaps[$wid]['ants'] = [];
            if(!empty($links['ants']))
            {
                $oneset_ants = $links['ants'];
                foreach($oneset_ants as $onepath_ants)
                {
                    foreach($onepath_ants as $onewid)
                    {
                        if($wid != $onewid)
                        {
                            $pass2_richmaps[$wid]['ants'][$onewid] = $onewid;
                        }
                    }
                }
            }
        }
        $workitem_richmaps = [];
        foreach($pass2_richmaps as $wid=>$detail)
        {
            $detail['ddw'] = $this->m_project_workitems[$wid]['maps']['ddw'];
            $detail['daw'] = $this->m_project_workitems[$wid]['maps']['daw'];
            foreach($detail['ddw'] as $oneddw)  //Because this has PROJECT ROOTs!
            {
                $detail['deps'][$oneddw] = $oneddw;
            }
            foreach($detail['daw'] as $onedaw)  //Because this has PROJECT ROOTs!
            {
                $detail['ants'][$onedaw] = $onedaw;
            }
            $workitem_richmaps[$wid] = $detail;
        }
        return $workitem_richmaps;
    }
    
    /**
     * A prunable goal is one where none of its ANTs have dependents
     * outside of the branch of which it is the root AND it does NOT 
     * depend on anything that another node depends on too.
     */
    private function getPrunableBranchMap($relationships_map_bundle)
    {
        $prunable_branch_map = [];
        $map_all_ants = [];
        foreach($relationships_map_bundle as $candidate_rootwid=>$maps)
        {
            foreach($maps['daw'] as $wid)
            {
                $map_all_ants[$wid][$candidate_rootwid] = $candidate_rootwid;
            }
        }
        foreach($relationships_map_bundle as $candidate_rootwid=>$maps)
        {
            if($candidate_rootwid != $this->m_root_goalid)
            {
                $is_prunable_branch = TRUE;
                //Check each ant of this candidate root
                foreach($maps['ants'] as $antwid)
                {
                    //Check because sometimes a workitem in middle of branch is marked COMPLETE when ant is not!
                    if(isset($relationships_map_bundle[$antwid]['ddw']))
                    {
                        //Check all DIRECT dependents of this ant workitem
                        foreach($relationships_map_bundle[$antwid]['ddw'] as $depwid)
                        {
                            if($depwid != $candidate_rootwid && !isset($relationships_map_bundle[$candidate_rootwid]['ants'][$depwid]))
                            {
                                //The workitem has a DIRECT dependent outside of this branch!
                                $is_prunable_branch = FALSE;
                                break;
                            }
                        }
                    }
                }
                if($is_prunable_branch)
                {
                    foreach($maps['daw'] as $wid)
                    {
                        if(isset($map_all_ants[$wid][$candidate_rootwid]) && count($map_all_ants[$wid]) > 1)
                        {
                            $is_prunable_branch = FALSE;
                            break;
                        }
                    }
                }
                if($is_prunable_branch)
                {
                    $prunable_branch_map[$candidate_rootwid] = $candidate_rootwid;
                }
            }
        }
        return $prunable_branch_map;
    }
    
    public function getAllInsightsBundle()
    {
        $bundle = [];
        
        $paths2root = $this->getAllRichWorkitemPathInfo();
        
        $wdetail = $this->m_project_workitems[$this->m_root_goalid];
        $root_otsp = $wdetail['forecast']['local']['otsp'];

        $metadata= $this->m_metadata;
        $metadata['otsp'] = $root_otsp;
        $bundle['metadata'] = $metadata;
        $bundle['paths2root'] = $paths2root;
        $project_relationships_map = $this->getProject2ProjectRelationshipsMap();
        $workitem_relationships_map = $this->getWorkitemRelationshipsMap($paths2root);
        $bundle['prunable_branch_map'] = $this->getPrunableBranchMap($workitem_relationships_map);
        $relationships_map = [];
        $relationships_map['workitems'] = $workitem_relationships_map;
        $relationships_map['projects'] = $project_relationships_map;
        $bundle['relationships_map'] = $relationships_map;
//DebugHelper::showNeatMarkup($bundle,"LOOK getAllInsightsBundle");
        return $bundle;
    }
    
    private function getAllRichWorkitemPathInfo()
    {
        $paths2root = $this->getAllLeafPathsToRoot();
        $path_insights = [];
        foreach($paths2root as $leafwid=>$all_paths_onewid)
        {
            foreach($all_paths_onewid as $onepath)
            {
                $nodecount = count($onepath);
                $path_total_reh = 0;
                $path_owner_map = [];
                $path_status_maps = [];
                $path_status_maps['count']['is']['happy'] = 0;
                $path_status_maps['count']['not']['happy'] = 0;
                $path_status_maps['count']['is']['needstesting'] = 0;
                $path_status_maps['count']['not']['needstesting'] = 0;
                $path_status_maps['count']['is']['workstarted'] = 0;
                $path_status_maps['count']['not']['workstarted'] = 0;
                $path_lower_otsp = 1;
                $path_upper_otsp = 1;
                foreach($onepath as $memberwid)
                {
                    $wdetail = $this->m_project_workitems[$memberwid];
                    if(empty($wdetail['forecast']))
                    {
                        $title = "Missing forecast in workitem detail for wid#$memberwid";
                        DebugHelper::showNeatMarkup($wdetail, $title, 'error');
                        DebugHelper::showStackTrace($title);
                        throw new \Exception($title);
                    }
                    $member_local_otsp = $wdetail['forecast']['local']['otsp'];
                    $member_fordep_otsp = $wdetail['forecast']['fordep']['otsp'];
                    $path_lower_otsp *= $member_local_otsp;
                    $path_upper_otsp *= $member_fordep_otsp;
                    $path_total_reh += $wdetail['remaining_effort_hours'];
                    $owner_personid = $wdetail['owner_personid'];
                    $status_cd = $wdetail['status_cd'];
                    $status_detail = $this->m_status_lookup[$status_cd];
                    $path_owner_map[$owner_personid] = $owner_personid;
                    $path_status_maps['code'][$status_cd] = $status_cd;
                    if($status_detail['happy_yn'] !== NULL)
                    {
                        if($status_detail['happy_yn'] == 1)
                        {
                            $path_status_maps['count']['is']['happy'] += 1;
                        } else {
                            $path_status_maps['count']['not']['happy'] += 1;
                        }
                    }
                    if($status_detail['needstesting_yn'] !== NULL)
                    {
                        if($status_detail['needstesting_yn'] == 1)
                        {
                            $path_status_maps['count']['is']['needstesting'] += 1;
                        } else {
                            $path_status_maps['count']['not']['needstesting'] += 1;
                        }
                    }
                    if($status_detail['workstarted_yn'] !== NULL)
                    {
                        if($status_detail['workstarted_yn'] == 1)
                        {
                            $path_status_maps['count']['is']['workstarted'] += 1;
                        } else {
                            $path_status_maps['count']['not']['workstarted'] += 1;
                        }
                    }
                }
                
                $oneinsightrow = [];
                $oneinsightrow['leafwid'] = $leafwid;
                $oneinsightrow['nodecount'] = $nodecount;
                $oneinsightrow['dates'] = $this->getDateBoundsOfPath($onepath);
                $oneinsightrow['path'] = $onepath;
                $oneinsightrow['otsp']['lower'] = $path_lower_otsp;
                $oneinsightrow['otsp']['upper'] = $path_upper_otsp;
                $oneinsightrow['total_reh'] = $path_total_reh;
                $oneinsightrow['maps']['owner'] = $path_owner_map;
                $oneinsightrow['maps']['status'] = $path_status_maps;
                $path_insights[] = $oneinsightrow;
            }
        }
        return $path_insights;
    }
    
    private function getAllLeafPathsToRoot()
    {
        $paths = [];
        foreach($this->m_project_effectiveleafmap as $wid)
        {
            $paths[$wid] = $this->getPathsToRoot($wid);
        }
        return $paths;
    }
    
    /**
     * A leaf has no ants or only terminal ants and 
     * is not on an existing path
     */
    private function getAllEffectiveLeafNodes()
    {
        $nonleafmap = [];
        $effective_leafmap = [];
        foreach($this->m_project_workitems as $wid=>$wdetail)
        {
            $opid = $wdetail['owner_projectid'];
            if($this->m_projectid == $opid)
            {
                //This workitem is in our project so continue
                $status_cd = $wdetail['status_cd'];
                $status_detail = $this->m_status_lookup[$status_cd];
                $ddw = $wdetail['maps']['ddw'];
                $ddp = $wdetail['maps']['ddp'];
                if(count($ddw) > 0)
                {
                    foreach($ddw as $depwid)
                    {
                        if($opid == $ddp[$depwid])
                        {
                            $deppaths_list = $this->getPathsToRoot($depwid);
                            foreach($deppaths_list as $onedeppath)
                            {
                                $offset=0;
                                foreach($onedeppath as $onenonleafwid)
                                {
                                    if($offset > 0)
                                    {
                                        $nonleafmap[$onenonleafwid] = $onenonleafwid;
                                    }
                                    $offset++;
                                }
                            }
                        }
                    }
                }
            }
            if($status_detail['terminal_yn'] != 1)
            {
                $effective_leafmap[$wid] = $wid;
            }
        }
        foreach($nonleafmap as $removewid)
        {
            unset($effective_leafmap[$removewid]);
        }
        return $effective_leafmap;
    }

    private function getDateBoundsOfPath($onepath)
    {
        $min_dt = NULL;
        $max_dt = NULL;
        foreach($onepath as $wid)
        {
            if($wid != $this->m_root_goalid)
            {
                $wdetail = $this->m_project_workitems[$wid];
                $sdt = !empty($wdetail['actual_start_dt']) ? $wdetail['actual_start_dt'] : $wdetail['planned_start_dt'];
                $edt = !empty($wdetail['actual_end_dt']) ? $wdetail['actual_end_dt'] : $wdetail['planned_end_dt'];
                if(!empty($sdt))
                {
                    if(empty($min_dt) || $min_dt > $sdt)
                    {
                        $min_dt = $sdt;
                    }
                }
                if(!empty($edt))
                {
                    if(empty($max_dt) || $max_dt < $edt)
                    {
                        $max_dt = $edt;
                    }
                }
            }
        }
        return array('sdt'=>$min_dt,'edt'=>$max_dt);
    }
    
    private function getPathsToRoot($currentwid)
    {
        $paths = [];
        $wdetail = $this->m_project_workitems[$currentwid];
        $opid = $wdetail['owner_projectid'];
        if($this->m_projectid == $opid)
        {
            $status_cd = $wdetail['status_cd'];
            $status_detail = $this->m_status_lookup[$status_cd];
            $ddw = $wdetail['maps']['ddw'];
            $ddp = $wdetail['maps']['ddp'];
            foreach($ddw as $depwid)
            {
                //Stay within current project
                if($opid == $ddp[$depwid])
                {
                    $deppaths_list = $this->getPathsToRoot($depwid);
                    foreach($deppaths_list as $onedeppath)
                    {
                        if($status_detail['terminal_yn'] != 1)
                        {
                            //Current wid belongs in the path
                            $onepath = array_merge(array($currentwid),$onedeppath);
                        } else {
                            //Skip current wid because it is completed
                            $onepath = $onedeppath;
                        }
                        $paths[] = $onepath;
                    }
                }
            }
            if(count($paths) == 0)
            {
                $paths[] = array($currentwid);
            }
        }
        return $paths;
    }
}