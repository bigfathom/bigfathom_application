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
 
 */

namespace bigfathom;

require_once 'helper/ASimpleFormPage.php';

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoManageDetailsPage extends \bigfathom\ASimpleFormPage
{

    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
    }
    
    public function getFormBodyContent($form, $html_classname_overrides=NULL, $menulevel=1)
    {
        try
        {
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $menu_name = 'navigation';

            $arturl = UtilityGeneralFormulas::getArtURLForPurposeName("cloudadmin");
            $markup = '<div class="adhocmenu-art-left"><img src="'. $arturl. '" /></div>';
            $markup .= '<div class="adhocmenu-right">'
                    . "<h2>Administer these values that are shared across all projects.</h2>"
                    . '<ul>';
            
            $basemenupath = "bigfathom/sitemanage";
            $basemenupathlen = strlen($basemenupath);
            $allmenus = menu_tree_all_data('navigation');
            foreach($allmenus as $k=>$v)
            {
                if(isset($v['link']))
                {
                    $link = $v['link'];
                    if(substr($link['link_path'], 0, $basemenupathlen) == $basemenupath)
                    {
                        $below = $v['below'];
                        foreach($below as $key=>$bundle)
                        {
                            $detail = $bundle['link'];
                            $href = $detail['href'];
                            $title = $detail['title'];
                            $hyperlink = l($title,$href);
                            $markup .= "<li>$hyperlink</li>";
                        }
                    }
                }
            }
            $markup .= '</ul></div>';
            $markup .= '<div class="clearfix"></div>';

            $form["data_entry_area1"]['table_container']['nav'] = array('#type' => 'item',
                     '#markup' => $markup
                    );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
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
            $form = $this->getFormBodyContent($form, $html_classname_overrides);
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
