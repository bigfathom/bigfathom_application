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
class TagMapOneProjectPage extends \bigfathom\ASimpleFormPage
{

    private $m_oMapHelper = NULL;
    
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
        
        $urls_arr = [];
        $urls_arr['view']['testcase'] = 'bigfathom/viewtestcase';
        $urls_arr['view']['usecase'] = 'bigfathom/viewusecase';
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if($this->m_is_systemdatatrustee)
        {
            $aDataRights='VAED';
        } else {
            $aDataRights='V';
        }
        
        $this->m_urls_arr       = $urls_arr;
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        if(empty($this->m_projectid))
        {
            throw new \Exception("Must already have a project selected!");
        }
    }
    
    private function smashOneType($tag_bundle,$typename,&$smashed)
    {
        $map = $tag_bundle['maps'][$typename]['map'];
        $base_url = $this->m_urls_arr['view'][$typename];
        $idfieldname = "{$typename}id";
        foreach($map as $tag_tx=>$items)
        {
            foreach($items as $detail)
            {
                $id = $detail[$idfieldname];
                $detail['url'] = url($base_url
                        , array('query'=>array($idfieldname=>$id, 'source'=>'from_tag_report')));
                $smashed[$tag_tx][$typename][$id] = $detail;
            }
        }
    }
    
    function getSmashedTags()
    {
        $bundle = [];
        $tag_bundle = $this->m_oMapHelper->getTagMapBundle($this->m_projectid);
        $this->smashOneType($tag_bundle,'workitem',$bundle);
        $this->smashOneType($tag_bundle,'usecase',$bundle);
        $this->smashOneType($tag_bundle,'testcase',$bundle);
        return $bundle;
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
            
            $main_tablename = 'grid-project-tagmapping-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $smashed_tags = $this->getSmashedTags();
            
            $blurb_markup = "<p>"
                    . "Current tag <i class='fa fa-tags' aria-hidden='true'></i> mappings for Workitems and Use Cases of Project#{$this->m_projectid} as of $now_dttm"
                            . "</p>";
            $form["data_entry_area1"]['context_info'] = array(
                '#type' => 'item',
                '#markup' => "<div class='pagetop-blurb'>$blurb_markup</div>",
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            $tableheader = [];
            $tableheader[] = array("Tag","The tag text","text");
            $tableheader[] = array("Workitems","The IDs of the tagged workitems","formula");
            $tableheader[] = array("Use Cases","The IDs of the tagged use cases","formula");
            $tableheader[] = array("Test Cases","The IDs of the tagged test cases","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $trows_ar = [];
            foreach($smashed_tags as $tag_tx=>$tagged_items)
            {
                $workitem_items = isset($tagged_items['workitem']) ? $tagged_items['workitem'] : [];
                $usecase_items = isset( $tagged_items['usecase']) ? $tagged_items['usecase'] : [];
                $testcase_items = isset( $tagged_items['testcase']) ? $tagged_items['testcase'] : [];
                
                $witem_ar = [];
                foreach($workitem_items as $id=>$detail)
                {
                    $witem_ar[] = "<a title='status: {$detail["status_cd"]}' href='{$detail["url"]}'>$id</a>";
                }
                $workitem_count = count($witem_ar);
                $workitem_markup = "[SORTNUM:$workitem_count]" . implode(", ", $witem_ar);
                
                $uitem_ar = [];
                foreach($usecase_items as $id=>$detail)
                {
                    $uitem_ar[] = "<a title='status: {$detail["status_cd"]}' href='{$detail["url"]}'>$id</a>";
                }
                $usecase_count = count($uitem_ar);
                $usecase_markup = "[SORTNUM:$usecase_count]" . implode(", ", $uitem_ar);
                
                $titem_ar = [];
                foreach($testcase_items as $id=>$detail)
                {
                    $titem_ar[] = "<a title='status: {$detail["status_cd"]}' href='{$detail["url"]}'>$id</a>";
                }
                $testcase_count = count($titem_ar);
                $testcase_markup = "[SORTNUM:$testcase_count]" . implode(", ", $titem_ar);
                
                $trows_ar[]   .= "\n".'<td>'
                        .$tag_tx.'</td><td>'
                        .$workitem_markup.'</td><td>'
                        .$usecase_markup.'</td><td>'
                        .$testcase_markup.'</td>';
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid"><thead>' 
                    . $th_markup 
                    . '</thead><tbody>'
                    . $trows_markup 
                    . '</tbody></table>';
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_markup);

            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
