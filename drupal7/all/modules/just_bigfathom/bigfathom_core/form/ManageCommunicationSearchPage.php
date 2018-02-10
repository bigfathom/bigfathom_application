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

require_once 'helper/ASimpleFormPage.php';
require_once 'helper/BrainstormItemsPageHelper.php';

/**
 * This class returns the list of available work topics
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageCommunicationSearchPage extends \bigfathom\ASimpleFormPage {

    protected $m_oContext = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_parent_projectid = NULL;

    public function __construct() 
    {
        module_load_include('php', 'bigfathom_core', 'core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        $urls_arr = [];
        
        $urls_arr['view']['workitem'] = 'bigfathom/workitem/view';
        $urls_arr['view']['project'] = 'bigfathom/project/view';
        $urls_arr['view']['sprint'] = 'bigfathom/sprint/view';
        $urls_arr['view']['testcase'] = 'bigfathom/projects/testcases';

        $urls_arr['communication']['workitem'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['communication']['project'] = 'bigfathom/project/mng_comments';
        $urls_arr['communication']['sprint'] = 'bigfathom/sprint/mng_comments';
        $urls_arr['communication']['testcase'] = 'bigfathom/testcase/mng_comments';
        
        $cmi = $this->m_oContext->getCurrentMenuItem();
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['this_page_path'] = $cmi['link_path'];
        $aPersonRights = 'VAED';

        $this->m_urls_arr = $urls_arr;
        $this->m_aPersonRights = $aPersonRights;

        $this->m_oPageHelper = new \bigfathom\BrainstormItemsPageHelper($urls_arr);
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides = NULL) 
    {
        try 
        {
            $main_tablename = 'commsearch-table';
            $main_table_containername = "container4{$main_tablename}";
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            $send_urls = $this->m_urls_arr;
            $send_urls['base_url'] = $base_url;
            $send_urls['images'] = $base_url .'/'. $theme_path.'/images';
            drupal_add_js(array('personid'=>$user->uid,'projectid'=>$this->m_parent_projectid
                    ,'myurls' => $send_urls), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageCommunicationSearchPage.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            $naturalsort_imgurl = file_create_url(path_to_theme().'/images/icon_sorting.png');
            
            if ($html_classname_overrides == NULL) {
                $html_classname_overrides = array();
            }
            if (!isset($html_classname_overrides['data-entry-area1'])) {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if (!isset($html_classname_overrides['visualization-container'])) {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if (!isset($html_classname_overrides['table-container'])) {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if (!isset($html_classname_overrides['container-inline'])) {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if (!isset($html_classname_overrides['action-button'])) {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $base_url;

            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "Use this console to find specific communications that have been created in this project."
                . "</p>",
                '#suffix' => '</div>',
            );

            $bigwrapperid = "bigwrapper-" . $main_tablename;
            $form["data_entry_area1"]['bw'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $bigwrapperid . '" class="bigwrapper-tablestuff">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            module_load_include('php','bigfathom_core','snippets/SnippetHelper');
            $oSnippetHelper = new \bigfathom\SnippetHelper();
            $customquery_markup = $oSnippetHelper->getHtmlSnippet("customquery_communication");            
            
            $totalsid = "custom-query-area-" . $main_tablename;
            $form["data_entry_area1"]['bw']['custom_query_area'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="custom-query-area">' . $customquery_markup,
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['bw']['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $userinputspanelid = "userinputs-" . $main_tablename;
            $form["data_entry_area1"]['bw']['userinputs'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $userinputspanelid . '" class="userinputs-panel-area">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['bw']['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            $headers_ar = [];
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderImgCell($naturalsort_imgurl, 'natural order conceptual icon', '', 'Natural order so that replies follow their threads', 'numid', 'naturalsort'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('SC', 'Subject context', 'formula', 'subjectcontextletter'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('SID', 'Subject item unique identifier', 'formula', 'sid'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('Thread', 'Each comment has a unique ID; the leftmost is the ID of the thread root', 'formula', 'thread'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('Author', 'Creator of the comment', 'formula', 'author'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('AR', 'Has author requested action?', 'formula', 'actionrequested'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('LC', 'Level of concern associated with this comment', 'formula', 'levelofconcern'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('Comment', 'Title if set, else this is a snippet of the actual comment', 'formula', 'commentsnippet'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('Date', 'When this comment was last edited (or created if never edited)', 'datetime', 'lastupdated'); 
            $headers_ar[] = \bigfathom\MarkupHelper::getOneTableHeaderTextCell('Action Options', '', 'formula', 'actionoptions'); 
            $tablemarkup = \bigfathom\MarkupHelper::getTableMarkup($main_tablename,'browserGrid',$headers_ar);
            
            //DO NOT POPULATE ANY ROWS FROM THE SERVER ON THIS PAGE
            $form["data_entry_area1"]['bw']['table_container']['maintable'] = array('#type' => 'item',
                '#markup' => $tablemarkup);


            $form["data_entry_area1"]['action_buttons'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['container-inline'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            if (isset($this->m_urls_arr['return'])) {
                $exit_link_markup = l('Exit', $this->m_urls_arr['return']
                            , array('attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $exit_link_markup);
            }

            $form['data_entry_area1']['form_bottom'] = array('#type' => 'item'
                , '#markup' => '<div class="modal-blocker"><!-- Place at bottom of page --></div>');
                    
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

}
