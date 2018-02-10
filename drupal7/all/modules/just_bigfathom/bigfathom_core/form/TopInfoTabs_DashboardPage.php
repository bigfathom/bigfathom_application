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

require_once 'TopInfoTabsPage.php';

/**
 * Information for the user
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class TopInfoTabs_DashboardPage extends \bigfathom\TopInfoTabsPage
{

    private $m_selected_tab = "dashboard";
    
    public function __construct()
    {
        $info_bundle = \bigfathom\UtilityGeneralFormulas::getUIContextBundle('dashboard');
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($this->m_selected_tab);
        $this->m_oContext = \bigfathom\Context::getInstance();
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        $urls_arr = [];
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['view']['project'] = 'bigfathom/project/view';
        $urls_arr['view']['sprint'] = 'bigfathom/sprint/view';

        $urls_arr['communication']['workitem'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['communication']['project'] = 'bigfathom/project/mng_comments';
        $urls_arr['communication']['sprint'] = 'bigfathom/sprint/mng_comments';
        
        $cmi = $this->m_oContext->getCurrentMenuItem();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['this_page_path'] = $cmi['link_path'];  
        $this->m_urls_arr = $urls_arr;
    }

    private function getDashinfoMarkup()
    {
        try
        {
            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $this->m_oUAH = new \bigfathom\UserAccountHelper();

            global $user;
            $userprofile = $this->m_oUAH->getUserProfileBundle($user->uid);
            $seb = $this->m_oUAH->getElementControlBundle($user->uid);
            $can_see_unfinished = !empty($seb['show']['UNFINISHED']) ? TRUE : FALSE;

            //Map the library action name to our element IDs
            module_load_include('php','bigfathom_core','snippets/SnippetHelper');
            $oSnippetHelper = new \bigfathom\SnippetHelper();
            $dashnugget_oartmm_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_oartmm");            
            $dashnugget_sprinttmm_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_sprinttmm");            
            $dashnugget_tmm_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_tmm");            
            $dashnugget_your_wi_influence_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_your_wi_influence");  
            $dashnugget_your_wi_importance_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_your_wi_importance");  
            $dashnugget_radios_markup = $oSnippetHelper->getHtmlSnippet("dashnugget_radios");  
            
            $core = $userprofile['core'];
            $srs = $userprofile['roles']['systemroles']['summary'];
            //$roles = $userprofile['roles'];
            
//$temp = $this->m_oMapHelper->getWorkitem2ImportanceCountsByCategory(NULL,$user->uid,NULL);
//DebugHelper::debugPrintNeatly($temp, FALSE, "LOOK at the action request priority factors here...");
            
            $tab_tmm_content_markup = "";
            $tab_tmm_content_markup .= $dashnugget_oartmm_markup;
            $tab_tmm_content_markup .= $dashnugget_sprinttmm_markup;
            $tab_tmm_content_markup .= $dashnugget_tmm_markup;
            $tab_tmm_content_markup .= $dashnugget_your_wi_importance_markup;
            $tab_tmm_content_markup .= $dashnugget_your_wi_influence_markup;
            $tab_tmm_content_markup .= $dashnugget_radios_markup;

            $tab_sw_content_markup = $oSnippetHelper->getHtmlSnippet("dashboard_TCSW");  
            $tab_utilization_content_markup = $oSnippetHelper->getHtmlSnippet("dashboard_TCUTILIZATION");  
            
            $todo_markup = "<p>CONTENT TO BE PLACED HERE!</p>";
            
            $tabs_ar = [];
            $tabs_ar[] = array("id"=>"dash-tab-personal"
                , "label"=>"Time Management"
                , "jsfile"=>"UserDashboardTMHelper.js"
                , "content"=>$tab_tmm_content_markup
                , "unfinished"=>FALSE
                , "tooltip"=>"Overview of what is important specifically to you");
            $tabs_ar[] = array("id"=>"dash-tab-portfolio"
                , "label"=>"Portfolio"
                , "content"=>$todo_markup
                , "unfinished"=>TRUE
                , "tooltip"=>"Overview of what is significant in the projects you lead");
            $tabs_ar[] = array("id"=>"dash-tab-group"
                , "label"=>"Group"
                , "content"=>$todo_markup
                , "unfinished"=>TRUE
                , "tooltip"=>"Overview of what is currently significant in the groups where you are a member");
            $tabs_ar[] = array("id"=>"dash-tab-sprint"
                , "label"=>"Sprint"
                , "content"=>$todo_markup
                , "unfinished"=>TRUE
                , "tooltip"=>"Overview of what is currently happening across all your sprints");
            $tabs_ar[] = array("id"=>"dash-tab-vision"
                , "label"=>"Vision"
                , "content"=>$todo_markup
                , "unfinished"=>TRUE
                , "tooltip"=>"Overview of how aligned your work is with the big picture");
            $tabs_ar[] = array("id"=>"dash-tab-rankedwork"
                , "label"=>"Sorted Worklist"
                , "jsfile"=>"UserDashboardSWHelper.js"
                , "content"=>$tab_sw_content_markup
                , "unfinished"=>FALSE
                , "tooltip"=>"A prioritized listing of all work associated with you");
            $tabs_ar[] = array("id"=>"dash-tab-utilization"
                , "label"=>"Utilization"
                , "jsfile"=>"UserDashboardUtilizationHelper.js"
                , "content"=>$tab_utilization_content_markup
                , "unfinished"=>FALSE
                , "tooltip"=>"A listing of all your work periods with a summary % utilization shown for each period");

            $content_ar = [];
            $tabsmarkup_ar = [];
            foreach($tabs_ar as $detail)
            {
                if($can_see_unfinished || !$detail['unfinished'])
                {
                    $id = $detail['id'];
                    $tooltip = $detail['tooltip'];
                    $labelmarkup = "<a href='#$id'>" . $detail['label'] . "</a>";
                    $content_ar[] = "<div id='$id'>" . $detail['content'] . "</div>";
                    $tabsmarkup_ar[] = "<li title='$tooltip'>$labelmarkup</li>";
                }
            }
            $core_markup = "\n<ul class='inpanel-tabs'>\n\t" . implode("\n\t", $tabsmarkup_ar) . "\n</ul>"
                        ."\n" . implode("\n", $content_ar) . "\n";
            $markup = "<div id='dash-container-tabs' class='dash-container'>" . $core_markup . "</div>";

            global $user;
            global $base_url;
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');
            $myurls = $this->m_urls_arr;
            $myurls['images'] = $base_url . '/' . $theme_path . '/images';
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => $myurls), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$theme_path/node_modules/chart.js/dist/Chart.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            //drupal_add_js("$base_url/$module_path/form/js/UserDashboardTMHelper.js");
            //drupal_add_js("$base_url/$module_path/form/js/UserDashboardSWHelper.js");
            foreach($tabs_ar as $detail)
            {
                if(!empty($detail['jsfile']))
                {
                    $jsfile = $detail['jsfile'];
                    drupal_add_js("$base_url/$module_path/form/js/$jsfile");
                }
            }
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            drupal_add_js("$base_url/$module_path/form/js/UserDashboardCoreHelper.js");
            
            return $markup;
        } 
        catch (\Exception $ex)
        {
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
            $topmarkup = $this->getDashinfoMarkup();
            $selected_content_markup = $this->getSelectedBodyContentMarkup(FALSE, FALSE, $topmarkup);
            return $this->getFormBodyContent($form, $html_classname_overrides, $selected_content_markup);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
