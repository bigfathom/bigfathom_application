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

module_load_include('php','bigfathom_core','form/ASimpleFormPage');

/**
 * Run report about project status
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class AllProject2ProjectPathsPage extends \bigfathom\ASimpleFormPage
{

    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/UtilityGeneralFormulas');
        module_load_include('php','bigfathom_core','core/DateRangeSmartNumberBucket');
        module_load_include('php','bigfathom_core','core/MapHelper');
        module_load_include('php','bigfathom_core','core/UtilityFormatUtilizationData');
        module_load_include('php','bigfathom_core','core/ProjectInsight');
        
        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
    }
    
    private function getFlatPaths($map_root2tree,$rootpid=NULL,$dep_ar=NULL,&$solution_ar=NULL)
    {
        if($dep_ar == NULL)
        {
            $dep_ar = [];
        }
        if($solution_ar == NULL)
        {
            $solution_ar = [];
        }
        if($rootpid !== NULL)
        {
            $dep_ar[] = $rootpid;
        }
        if(!is_array($map_root2tree) || count($map_root2tree) == 0)
        {
            $path_root_projectid = $dep_ar[0];
            $solution_ar[$path_root_projectid][] = $dep_ar;
        } else {
            foreach($map_root2tree as $ant_rootpid=>$tree_from_root)
            {
                $this->getFlatPaths($tree_from_root,$ant_rootpid,$dep_ar,$solution_ar);
            }
        }
        return $solution_ar;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            
            $now_dttm = date("Y-m-d H:i", time());
            
            global $user;
            global $base_url;
            
            $main_tablename = 'grid-inter-project-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $pathtree = $this->m_oMapHelper->getAllProj2ProjPaths()            ;

            $map_root2flat_paths = $this->getFlatPaths($pathtree);

            
            $form["data_entry_area1"]['context_info'] = array(
                '#type' => 'item',
                '#markup' => "<div class='pagetop-blurb'><p>Results computed as of $now_dttm</p></div>",
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            //Create the static table
            $tableheader = [];
            $tableheader[] = array("RPID","Root Project ID","formula");
            $tableheader[] = array("RWID","Root Workitem ID","formula");
            $tableheader[] = array("Path","The projectids of the path starting with the root projectid","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $trows_ar = []; 
            $projectid_ar = array_keys($map_root2flat_paths);
            $map_projectid2wid = $this->m_oMapHelper->getProjectID2RootWorkitemIDMap($projectid_ar);
            foreach($map_root2flat_paths as $root_projectid=>$one_path_info_ar)
            {
                $root_goalid = $map_projectid2wid[$root_projectid];
                foreach($one_path_info_ar as $path_ar)
                {
                    $nodecount = count($path_ar);
                    $path_tx = implode(' <i class="fa fa-arrow-left" title="influence direction" aria-hidden="true"></i> ',$path_ar);
                    $path_markup = "[SORTNUM:{$nodecount}]<span>$path_tx</span>";

                    $trows_ar[] = "\n"
                            . "<td>$root_projectid</td>"
                            . "<td>$root_goalid</td>"
                            . "<td>$path_markup</td>"
                            ;
                }
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_section_title_markup = "<h2>Paths Where Root Project is Not an <span title='think of an antecedent project as a type of subproject'>Antecedent</span> of any Other Project</h2>";
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid"><thead>' 
                    . $th_markup 
                    . '</thead><tbody>'
                    . $trows_markup 
                    . '</tbody></table>';
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_section_title_markup.$table_markup);

            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
