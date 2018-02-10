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
class ChangeHistoryOneProjectPage extends \bigfathom\ASimpleFormPage
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
        
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        if(empty($this->m_projectid))
        {
            throw new \Exception("Must already have a project selected!");
        }
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
            
            $main_tablename = 'grid-project-insight';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");

            $pch_bundle = $this->m_oMapHelper->getProjectChangeHistoryBundle($this->m_projectid);
            $pch_overview = $pch_bundle['overview'];
            $workitem_rows = $pch_bundle['details']['workitem'];
            
//DebugHelper::showNeatMarkup($pch_bundle, 'LOOK testing the result!!!!!');
     
            $blurb_ar = [];
            $blurb_ar[] = "<p>Results for project#{$this->m_projectid} computed as of $now_dttm</p>";
            $blurb_ar[] = "<table>";
            $blurb_ar[] = "<tr><th>Sprints</th>"
                    . "<td>"
                    . "<table>"
                    . "<tr><td>total edits</td><td>{$pch_overview['sprint']['edit_count']}</td></tr>"
                    . "<tr><td>most recent</td><td>{$pch_overview['sprint']['max_dt']}</td></tr></table>"
                    . "</td></tr>"
                    . "</tabel>"
                    . "</td>";
            $blurb_ar[] = "<tr><th>Workitems</th>"
                    . "<td>"
                    . "<table>"
                    . "<tr><td>total edits</td><td>{$pch_overview['workitem']['edit_count']}</td></tr>"
                    . "<tr><td>most recent</td><td>{$pch_overview['workitem']['max_dt']}</td></tr></table>"
                    . "</td></tr>"
                    . "</tabel>"
                    . "</td>";
            $blurb_ar[] = "</table>";
            $blurb_markup = implode("\n",$blurb_ar);        
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
            //Create the static table
            $tableheader = [];
            $tableheader[] = array("Date","When the change was made","formula");
            $tableheader[] = array("WID","ID of the updated workitem ","integer");
            $tableheader[] = array("Author","Who made the change","integer");
            $tableheader[] = array("Description","What was changed","text");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $trows_ar = []; 
            foreach($workitem_rows as $one_info)
            {
                $wid = $one_info['workitemid'];
                $update_dt = $one_info['updated_dt'];
                $changed_by_uid = $one_info['changed_by_uid'];
                $comment_tx = $one_info['comment_tx'];
                
                $trows_ar[] = "\n<td>$update_dt</td>"
                        . "<td>$wid</td>"
                        . "<td>$changed_by_uid</td>"
                        . "<td>$comment_tx</td>"
                        ;
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_section_title_markup = "<h2>Change History for Workitems in Project#{$this->m_projectid}</h2>";
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
