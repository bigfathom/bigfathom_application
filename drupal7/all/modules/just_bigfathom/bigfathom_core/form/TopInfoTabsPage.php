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
 * Page for the user to navigate the application
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoTabsPage extends \bigfathom\ASimpleFormPage
{
    private $m_selected_tab;
    private $m_oContext;
    public function __construct($selected_tab=NULL)
    {
        module_load_include('php','bigfathom_core','core/Context');
        if(empty($selected_tab))
        {
            $selected_tab = "nothing";
        }
        $this->m_selected_tab = $selected_tab;
        $this->m_oContext = \bigfathom\Context::getInstance();
    }
    
    private function endsWith($haystack, $needle) 
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || 
                (($temp = strlen($haystack) - strlen($needle)) >= 0 
                    && strpos($haystack, $needle, $temp) !== false);
    }    

    /**
     * Gets the content even if out of whack structurally
     */
    public function getSelectedBodyContentMarkup($show_root=FALSE, $only_show_root=FALSE
            , $top_markup=NULL, $bottom_markup=NULL
            , $embed_into_existing_markup=NULL)
    {
        try
        {
            $has_selected_project = $this->m_oContext->hasSelectedProject();
            $menu_name = 'navigation';
            $basemenupath = "bigfathom/{$this->m_selected_tab}";

            global $user;
            $uah = new \bigfathom\UserAccountHelper();
            $upb = $uah->getUserProfileBundle();
            $is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
            $is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
            $is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter']; 

            $seb = $uah->getElementControlBundle($user->uid);
            $can_see_unfinished = !empty($seb['show']['UNFINISHED']) ? TRUE : FALSE;
            
            $basemenupathlen = strlen($basemenupath);
            $allmenus = menu_tree_all_data($menu_name);
            $tab_markup = "";//<ul class='tab-content-options'>";
            $cur_div_flag = NULL;
            $prev_div_flag = NULL;
            $option_count = 0;
            foreach($allmenus as $k=>$v)
            {
                if(isset($v['link']))
                {
                    $link = $v['link'];
                    if(substr($link['link_path'], 0, $basemenupathlen) == $basemenupath)
                    {
                        $isroot = strlen($link['link_path']) === $basemenupathlen;
                        
                        //Grab the stuff below it
                        $below = $v['below'];
                        foreach($below as $key=>$bundle)
                        {
                            $detail = $bundle['link'];
                            $page_arguments = $detail['page_arguments'];
                            if(!is_array($page_arguments))
                            {
                                $page_arguments = unserialize($page_arguments);
                            }
                            $is_sysadmin_only = (array_key_exists("sysadmin_only", $page_arguments)) ? $page_arguments["sysadmin_only"] : FALSE;
                            $is_unfinished = (array_key_exists("unfinished", $page_arguments)) ? $page_arguments["unfinished"] : FALSE;
                            if(($is_systemadmin || !$is_sysadmin_only) && ($can_see_unfinished || !$is_unfinished))
                            {
                                //drupal_set_message("LOOK here >>>> " . print_r($page_arguments,TRUE));                                
                                if($has_selected_project 
                                        || (!array_key_exists("requires_project", $page_arguments) || !$page_arguments["requires_project"]))
                                {
                                    $requires_systemdatatrustee =  empty($page_arguments['requires_systemdatatrustee']) ? FALSE : $page_arguments['requires_systemdatatrustee'];
                                    $requires_systemwriter =  empty($page_arguments['requires_systemwriter']) ? FALSE : $page_arguments['requires_systemwriter'];
                                    $showitem = TRUE;   //Assume OK
                                    if(!$is_systemadmin)
                                    {
                                        if($requires_systemdatatrustee && !$is_systemdatatrustee)
                                        {
                                            $showitem = FALSE;
                                        }
                                        if($requires_systemwriter && !$is_systemwriter)
                                        {
                                            $showitem = FALSE;
                                        }
                                    }
                                    if($showitem)
                                    {
                                        if(!array_key_exists("extra_attribs", $page_arguments))
                                        {
                                            $extra_attribs_tx = "";
                                        } else {
                                            $txt_ar = [];
                                            $extra_attribs = $page_arguments['extra_attribs'];
                                            foreach($extra_attribs as $attrib_name=>$attrib_value)
                                            {
                                                if($attrib_name === 'in-div')
                                                {
                                                    $cur_div_flag = $attrib_value;
                                                } else {
                                                    $txt_ar[] = "$attrib_name='$attrib_value'";
                                                }
                                            }
                                            $extra_attribs_tx = implode(" ", $txt_ar);
                                        }
                                        $href = $detail['href'];
                                        $title = $detail['title'];
                                        $link_path = $detail['link_path'];
                                        $hyperlink = l($title,$href);
                                        $description = $detail['description'];
                                        if($cur_div_flag !== NULL)
                                        {
                                            if($cur_div_flag !== $prev_div_flag)
                                            {
                                                if($prev_div_flag !== NULL)
                                                {
                                                    $tab_markup .= "</ul>";
                                                    $tab_markup .= "</div> <!-- end of $prev_div_flag -->\n";
                                                }
                                                $tab_markup .= "\n<div id='option-group-$cur_div_flag' class='option-group option-group-$cur_div_flag'>";
                                                $tab_markup .= "<ul class='vtabs-content-options'>";
                                            }
                                            $prev_div_flag = $cur_div_flag;
                                        }
                                        $option_count++;
                                        $tab_markup .= "<li $extra_attribs_tx title='$description'><span>$hyperlink</span></li>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if($prev_div_flag === NULL)
            {
                //We did not have divs so wrap the LIs in one big UL block
                $tab_markup = "<ul class='tab-content-options'>$tab_markup</ul>";
            } else {
                //We had divs so close the last one
                $tab_markup .= "</ul>";
                $tab_markup .= "</div> <!-- end of $prev_div_flag -->\n";
                $tab_markup .= "<div class='option-group-last'></div> <!-- so border streteches -->\n";
                //$safe_em = $option_count * 4;
                //$tab_markup = "<div style='height:{$safe_em}em'>$tab_markup<div>";
            }
            $full_tab_markup = "\n<div class='simple-links-container'>";
            if(!empty($top_markup))
            {
                if(!empty($embed_into_existing_markup))
                {
                    $top_markup = str_replace($embed_into_existing_markup, $tab_markup, $top_markup);
                }
                $full_tab_markup .= "\n<div class='top'>\n{$top_markup}\n</div>";
            }
            if(empty($embed_into_existing_markup))
            {
                $full_tab_markup .= $tab_markup;
            }
            if(!empty($bottom_markup))
            {
                $full_tab_markup .= "\n<div class='bottom'>\n{$bottom_markup}\n</div>";
            }            
            $full_tab_markup .= "\n</div>";

            $formapi_markup = array(
                '#type' => 'item', 
                '#markup' => $full_tab_markup,
                '#tree' => TRUE,
            );            
            return $formapi_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getFormBodyContent($form, $html_classname_overrides=NULL, $content_menu_markup=NULL)
    {
        try
        {
            $form["tab_content_area"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            
            $menu_name = 'navigation';
            $basemenupath = "bigfathom/topinfo";

            //$arturl = UtilityFormulas::getArtURLForPurposeName("cloudadmin");
            
            $basemenupathlen = strlen($basemenupath);
            $allmenus = menu_tree_all_data($menu_name);
            $tab_markup = "<ul>";
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
                            $rawpath = $detail['link_path'];
                            $pos = strrpos($rawpath, "/");
                            $uicontextname = substr($rawpath, $pos+1);
                            $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle($uicontextname);
                            $font_awesome_class = $info_bundle['font_awesome_class'];
                            $href = $detail['href'];
                            $title = t($detail['title']);
                            //$iconmarkup = "<img src='http://127.0.0.1/sites/all/themes/omega_bigfathom/images/icon_proj_small.png'>";
                            $iconmarkup = "<i class='$font_awesome_class'></i>";
                            $hyperlink = l($title,$href);
                            $tabflapmarkup="<span class='vtabs-label-wrapper'>$iconmarkup$hyperlink</span>";
                            $description = t($detail['description']);
                            if($this->endsWith($href, "/" . $this->m_selected_tab))
                            {
                                $tab_markup .= "<li title='$description' class='selected'>$tabflapmarkup</li>";
                            } else {
                                $tab_markup .= "<li title='$description'>$tabflapmarkup</li>";
                            }
                        }
                    }
                }
            }
            $tab_markup .= '</ul>';
            $full_tab_markup = "<div id='vtabs-container' class='vtabs-container'>\n{$tab_markup}\n</div>";
            
            if(empty($content_menu_markup))
            {
                $content_menu_markup = "Missing content menu markup!";
            }
            if(!is_array($content_menu_markup))
            {
                $content_markup = "<div class='vtabs-content-container'>\n{$content_menu_markup}\n</div>";
                $markup = "<div class='vtabs-area-wrapper'>\n{$full_tab_markup}\n{$content_markup}\n</div>";
                $form["tab_content_area"]['top_container'] = array(
                    '#type' => 'item', 
                    '#markup' => $markup,
                    '#tree' => TRUE,
                );
            } else {
                $form["tab_content_area"]['wrapper'] = array(
                    '#type' => 'item', 
                    '#prefix' => "\n<div class='vtabs-area-wrapper'>\n",
                    '#suffix' => "\n</div>\n",
                    '#tree' => TRUE,
                );
                $form["tab_content_area"]['wrapper']['tabs_container'] = array(
                    '#type' => 'item', 
                    '#markup' => $full_tab_markup,
                );
                $content_menu_markup['#prefix'] = "\n<div class='vtabs-content-container'>\n";
                $content_menu_markup['#suffix'] = "\n</div>\n";
                $content_menu_markup['#tree'] = TRUE;
                $form["tab_content_area"]['wrapper']['menu_content_container'] = $content_menu_markup;
            }
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    public function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
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
