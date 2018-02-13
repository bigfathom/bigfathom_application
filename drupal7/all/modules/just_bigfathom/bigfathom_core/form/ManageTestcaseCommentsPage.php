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
require_once 'helper/WorkitemPageHelper.php';

/**
 * This class interacts with the list of available Test Case comments
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageTestcaseCommentsPage extends \bigfathom\ASimpleFormPage 
{

    protected $m_custom_page_key = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oContext = NULL;
    protected $m_parent_projectid = NULL;
    protected $m_oTextHelper = NULL;
    
    protected $m_testcaseid = NULL;
    protected $m_testcasestepid = NULL;
    
    protected $m_in_dialog;
    
    public function __construct($testcaseid=NULL, $testcasestepid=NULL, $urls_override_arr=NULL, $in_dialog=FALSE)
    {
        $this->m_in_dialog = $in_dialog;
        $this->m_oContext = \bigfathom\Context::getInstance();
        if(empty($testcaseid))
        {
            throw new \Exception("Cannot find comments without a testcaseid!");
        }
        $this->m_testcaseid = $testcaseid;
        $this->m_testcasestepid = $testcasestepid;
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        
        $urls_arr = [];
        
        $urls_arr['add'] = 'bigfathom/testcase/addcomment';
        $urls_arr['reply'] = 'bigfathom/testcase/addcomment';
        $urls_arr['edit'] = 'bigfathom/testcase/editcomment';
        $urls_arr['view'] = 'bigfathom/testcase/viewcomment';
        $urls_arr['delete'] = 'bigfathom/testcase/deletecomment';
        if($this->m_in_dialog)
        {
            foreach($urls_arr as $k=>$v)
            {
                $urls_arr[$k] = "{$v}_indialog";
            }
        }
        $urls_arr['main_visualization'] = '';   // '/sites/all/modules/bigfathom_core/visualization/bigTree.html';
        if(is_array($urls_override_arr) && !empty($urls_override_arr['cpk']))
        {
            $this->m_custom_page_key = $urls_override_arr['cpk'];
        } else {
            $this->m_custom_page_key = "MWCP" . time();
        }

        if(!empty($urls_override_arr['return']))
        {
            $baseline_mi_info = [];
            $baseline_mi_info['rparams'] = array('q'=>$urls_override_arr['return']);
            $baseline_mi_info['return'] = $urls_override_arr['return'];
            $this->m_oContext->setMenuItemInfo($this->m_custom_page_key,$baseline_mi_info);
        } else {
            $pmi = $this->m_oContext->getParentMenuItem();
            $urls_arr['return'] = $pmi['link_path'];
            $mi = $this->m_oContext->getParentMenuItem($this->m_custom_page_key);
            if(!empty($mi['return']))
            {
                $urls_arr['return'] = $mi['return'];
            }
        }
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$theval)
            {
                if($k != 'return' || empty($urls_arr['return']))
                {
                    $urls_arr[$k] = $theval;
                }
            }
        }
        
        if($this->m_in_dialog)
        {
            unset($urls_arr['return']);
            $urls_arr['return_here'] = 'bigfathom/testcase/mng_comments_indialog';
        } else {
            $urls_arr['return_here'] = 'bigfathom/testcase/mng_comments';
        }

        $aPersonRights = 'VAED';

        $this->m_urls_arr = $urls_arr;
        $this->m_aPersonRights = $aPersonRights;

        $this->m_oPageHelper = new \bigfathom\TestcasePageHelper($urls_arr, NULL, $this->m_parent_projectid);
        $loaded = module_load_include('php', 'bigfathom_core', 'core/MapHelper');
        if (!$loaded) 
        {
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
            $main_tablename = 'testcasecomment-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            $testcasestepid = $this->m_testcasestepid;
            $testcaseid=$this->m_testcaseid;
            
            $testcase_status_by_code = $this->m_oMapHelper->getTestCaseStatusByCode(); //getWorkitemStatusByCode();
            $action_status_by_code = $this->m_oMapHelper->getActionStatusByCode();
            $stepidmap = $this->m_oMapHelper->getTestcaseStepids2Detail($testcaseid);
            $stepnum2detail = $this->m_oMapHelper->getTestcaseSteps2Detail($testcaseid);

            $allcommentsmap = $this->m_oMapHelper->getTestcaseCommunications($testcaseid);
            $comments = $allcommentsmap['comments'];
            $actionitems_resolutionmap = $allcommentsmap['actionitems'];
            $naturalorder_ar = $allcommentsmap['naturalorder'];
            $maxid = $allcommentsmap['maxid'];
            $maxid_digits = strlen($maxid);
            //$stepnum2detail = $allcommentsmap['stepnum2detail'];
            $default_stepnum = isset($stepidmap[$testcasestepid]) ? $stepidmap[$testcasestepid]['step_num'] : NULL;

            global $base_url;
            drupal_add_js(array('default_stepnum'=>$default_stepnum,'testcaseid'=>$testcaseid,'stepnum2detail'=> json_encode($stepnum2detail)
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageTestcaseCommentsPage.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");

            $naturalsort_imgurl = file_create_url(path_to_theme().'/images/icon_sorting.png');
            $paperclip_imgurl = file_create_url(path_to_theme().'/images/icon_paperclip.png');

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
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n<div id='dialog-anchor'></div>",
                '#suffix' => "\n</section>\n",
            );
            
            $dashboard = $this->m_oPageHelper->getContextDashboardElements($this->m_testcaseid);
            $form['data_entry_area1']['context_dashboard'] = $dashboard;
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            if(empty($this->m_urls_arr['rparams']))
            {
                $rparams_ar = [];
            } else {
                $rparams_ar = unserialize(urldecode($this->m_urls_arr['rparams']));
            }
                
            $rows = "\n";
            
            //DebugHelper::showNeatMarkup($allcommentsmap,"LOOK neat markup");
            $max_natural_order_key = count($naturalorder_ar);
            $max_natural_order_key_digits = strlen($max_natural_order_key);
            
            $natural_order_key = -1;
            foreach ($naturalorder_ar as $comid) 
            {
                $natural_order_key++;
                $padded_nok = str_pad($natural_order_key, $max_natural_order_key_digits, "0", STR_PAD_LEFT);

                $detail = $comments[$comid];
                $padded_comid = str_pad($comid, $maxid_digits, "0", STR_PAD_LEFT);
                $parent_comid = $detail['parent_comid'];
                $root_comid = $detail['root_comid'];
                $thread_summary_status = $detail['thread_summary_status'];
                $padded_root_comid = str_pad($root_comid, $maxid_digits, "0", STR_PAD_LEFT);
                $padded_parent_comid = str_pad($parent_comid, $maxid_digits, "0", STR_PAD_LEFT);
                $indentlevel = $detail['indentlevel'];
                if($indentlevel == 0)
                {
                    $id_markup = "<span title='Root of comment thread'><b>$padded_comid</b></span>";
                } else {
                    $id_markup = "<span title='Replied to #$padded_parent_comid'>" 
                            . $padded_root_comid 
                            . " " 
                            . "<span class='dimchars'>". str_repeat("| ",$indentlevel) . "</span>" //Important we use character GREATER than digit!
                            . "<b>$padded_comid</b></span>";
                }
                $status_cd_at_time_of_com = $detail['status_cd_at_time_of_com'];
                $has_replies = $detail['has_replies'];
                if($status_cd_at_time_of_com != NULL)
                {
                    $status_record = $testcase_status_by_code[$status_cd_at_time_of_com];
                    $status_title_tx = $status_record['title_tx'];
                    $status_markup = "<span title='$status_title_tx'>$status_cd_at_time_of_com</span>";
                    $status_terminal_yn = $status_record['terminal_yn'];
                    $terminalyesno = ($status_terminal_yn == 1 ? 'Yes' : 'No');
                } else {
                    $status_markup = "";
                    $terminalyesno = "";
                    $status_terminal_yn = 0;
                }
                
                $action_requested_concern = $detail['action_requested_concern'];
                $action_requested_concern_tx = $detail['action_requested_concern_tx'];
                $action_requested_yn = ($action_requested_concern > 0) ? 1 : 0;
                $action_reply_cd = $detail['action_reply_cd'];
                if(empty($action_reply_cd))
                {
                    $action_reply_markup = "";
                } else {
                    $action_status_record = $action_status_by_code[$action_reply_cd];
                    $action_status_title = $action_status_record['title_tx'];
                    $action_reply_markup = "<span>$action_status_title</span>";
                }
                if($action_requested_yn == 0)
                {
                    $action_requested_markup = 'No';
                    $action_todo = FALSE;
                } else {
                    if(!array_key_exists($comid, $actionitems_resolutionmap))
                    {
                        $action_requested_markup = '<span class="colorful-warning">Yes (todo)</span>';
                        $action_todo = TRUE;
                    } else {
                        $resolutions = $actionitems_resolutionmap[$comid];
                        if(count($resolutions) > 0)
                        {
                            $action_requested_markup = 'Yes (completed)';    
                            $action_todo = FALSE;
                        } else {
                            $action_requested_markup = '<span class="colorful-warning">Yes (todo)</span>';
                            $action_todo = TRUE;
                        }
                    }
                }
                if($action_requested_concern == 0 || empty($action_requested_concern_tx))
                {
                    $action_concern_markup = '';
                } else {
                    $apletter = strtoupper(substr($action_requested_concern_tx, 0,1));
                    if($action_todo)
                    {
                        $apclass = 'concern-' . strtolower($action_requested_concern_tx);
                    } else {
                        $apclass = 'concern-' . strtolower($action_requested_concern_tx) . '-done';
                    }
                    $action_concern_markup = "<span class='$apclass' "
                            . "title='$action_requested_concern_tx'>"
                            . "<!-- $action_requested_concern -->"
                            . "$apletter"
                            . "</span>";
                }

                if(intval($detail['attachment_count']) == 0)
                {
                    $attachment_markup = $detail['attachment_count'];
                } else {
                    $attachment_markup = "<span title='"
                            . "most recently uploaded " . $detail['attachment_most_recent_dt']
                            . "'>"
                            . $detail['attachment_count'] . "</span>";
                }
                $title_tx = $detail['title_tx'];
                $body_tx = $detail['body_tx'];
                $comment_len = strlen($body_tx);
                if ($comment_len > 80) 
                {
                    $body_tx = substr($body_tx, 0, 80) . '...';
                }
                if(empty($title_tx))
                {
                    $comment_markup = "<span class='comment-blurb' title='$comment_len characters in this comment'>$body_tx</span>";
                } else {
                    $comment_markup = "<span class='comment-title' title='title for $comment_len character comment'>$title_tx</span><br>";
                }
                $author_info = $detail['author_info'];
                $first_name = $author_info['first_nm'];
                $last_name = $author_info['last_nm'];
                $owner_personid = $author_info['personid'];
                $owner_markup = "<span title='#$owner_personid'>$first_name $last_name</span>";
                
                $linked_steps = $detail["linked_steps"];
                $stepnum_ar = array_keys($linked_steps);
                if(count($stepnum_ar) == 0)
                {
                    $filter_step_nums = 'blank';    //We use a keyword instead of a blank so filter is more maintainable
                } else {
                    sort($stepnum_ar);
                    $filter_step_nums = 's'.implode("!s", $stepnum_ar).'!';    //Wrap each in s & !
                }

                $steps_markup = implode(", ", $stepnum_ar);
                
                $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($detail['updated_dt']);
                
                if (strpos($this->m_aPersonRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('comid'=>$comid,'cpk'=>$this->m_custom_page_key)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view details of #{$comid}' href='$view_page_url'><img src='$view_icon_url'/></a>";                            
                }
                if (strpos($this->m_aPersonRights, 'E') === FALSE || !isset($this->m_urls_arr['edit'])) 
                {
                    $sReplyMarkup = '';
                    $sEditMarkup = '';
                } else {
                    $reply_page_url = url($this->m_urls_arr['reply'], array('query'=>array('parent_comid'=>$comid,'cpk'=>$this->m_custom_page_key)));
                    $reply_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('reply');
                    $sReplyMarkup = "<a title='reply to #{$comid}' href='$reply_page_url'><img src='$reply_icon_url'/></a>";
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('comid'=>$comid,'cpk'=>$this->m_custom_page_key)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$comid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if ($has_replies || strpos($this->m_aPersonRights, 'D') === FALSE || !isset($this->m_urls_arr['delete'])) {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('comid'=>$comid,'cpk'=>$this->m_custom_page_key)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete sprint#{$comid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $rows .= "\n" 
                        . '<tr id="'.$comid.'" filter_step_nums="'.$filter_step_nums.'" filter_thread_status="'.$thread_summary_status.'" >'
                        . '<td>'.$padded_nok.'</td>'
                        . '<td data-order="'.$padded_comid.'" style="white-space: nowrap">'
                        . "<!-- $padded_comid -->".$id_markup . '</td>'
                        . '<td>'
                        . $owner_markup . '</td>'
                        . '<td>'
                        . $attachment_markup . '</td>'
                        . '<td>'
                        . $action_requested_markup . '</td>'
                        . '<td>'
                        . $action_concern_markup . '</td>'
                        . '<td>'
                        . $action_reply_markup . '</td>'
                        . '<td>' . $comment_markup . '</td>'
                        . '<td>' . $status_markup . '</td>'
                        . '<td>' . $steps_markup . '</td>'
                        . '<td>' . $updated_dt_markup . '</td>'
                        . '<td class="action-options">'
                            . $sReplyMarkup . ' '
                            . $sViewMarkup . ' '
                            . $sEditMarkup . ' '
                            . $sDeleteMarkup . '</td>'
                        . '</tr>';
            }

            $form["data_entry_area1"]['table_container']['maintable'] = array('#type' => 'item',
                '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                . '<thead>'
                . '<tr>'
                . '<th colname="naturalsort"><span class="small-text" title="Ordering of comments so that they are naturally next to their replies">' 
                    . "<img alt='attached files count' src='$naturalsort_imgurl' />" . '</span></th>'
                . '<th><span title="Each comment has a unique ID; the root of each thread is the leftmost ID displayed">' . t('Thread') . '</span></th>'
                . '<th><span title="Creator of the comment">' . t('Author') . '</span></th>'
                . '<th><span title="Attached files">'."<img alt='attached files count' src='$paperclip_imgurl' />" . '</span></th>'
                . '<th><span title="Has author requested action?">' . t('Action Requested') . '</span></th>'
                . '<th><span title="Author' ."'". 's Level of concern for having this action request resolved">' . t('LC') . '</span></th>'
                . '<th><span title="If set, indicates the reply has resolved a requested action">' . t('Action Response') . '</span></th>'
                . '<th><span title="Title if set, else this is a snippet of the actual message body">' . t('Message') . '</span></th>'
                . '<th><span title="Test Case status at time of comment creation">' . t('Test Case Status') . '</span></th>'
                . '<th><span title="The test case steps that are particularly relevant to this comment">' . t('Steps') . '</span></th>'
                . '<th datatype="date"><span title="Date of most recent update (creation date if never updated)">' . t('Date') . '</span></th>'
                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody>'
                . $rows
                . '</tbody>'
                . '</table>'
                . '<br>');


            $form["data_entry_area1"]['action_buttons'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['container-inline'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            if (isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if (strpos($this->m_aPersonRights, 'A') !== FALSE) {
                    $add_link_markup = l('Add New Test Case Comment Thread'
                            , $this->m_urls_arr['add']
                            , array('query' => array('testcaseid' => $this->m_testcaseid,'default_stepnum'=>$default_stepnum,'cpk'=>$this->m_custom_page_key),
                                'attributes'=>array('class'=>'action-button'))
                            );
                    $form['data_entry_area1']['action_buttons']['addtestcase'] = array('#type' => 'item'
                        , '#markup' => $add_link_markup);
                }
            }

            if (isset($this->m_urls_arr['return'])) 
            {
                $exit_link_markup = l('Exit', $this->m_urls_arr['return']
                            , array('query' => $rparams_ar, 'attributes'=>array('class'=>'action-button'))
                        );
                $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $exit_link_markup);
            }

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

}
