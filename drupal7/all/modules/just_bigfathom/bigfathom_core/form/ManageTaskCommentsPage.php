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
require_once 'TaskPageHelper.php';

/**
 * This class returns the list of available tasks
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageTaskCommentsPage extends \bigfathom\ASimpleFormPage {

    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_aPersonRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oContext = NULL;
    protected $m_parent_projectid = NULL;
    protected $m_oTextHelper = NULL;
    
    protected $m_taskid = NULL;
    
    public function __construct($taskid=NULL, $urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        if(empty($taskid))
        {
            throw new \Exception("Cannot find comments without a taskid!");
        }
        $this->m_taskid = $taskid;
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        $this->m_parent_projectid = $this->m_oContext->getSelectedProjectID();
        $urls_arr = array();
        $urls_arr['add'] = 'bigfathom/task/addcomment';
        $urls_arr['reply'] = 'bigfathom/task/addcomment';
        $urls_arr['edit'] = 'bigfathom/task/editcomment';
        $urls_arr['view'] = 'bigfathom/task/viewcomment';
        $urls_arr['delete'] = 'bigfathom/task/deletecomment';
        $urls_arr['main_visualization'] = '';   // '/sites/all/modules/bigfathom_core/visualization/bigTree.html';
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }

        $aPersonRights = 'VAED';

        $this->m_urls_arr = $urls_arr;
        $this->m_aPersonRights = $aPersonRights;

        $this->m_oPageHelper = new \bigfathom\TaskPageHelper($urls_arr, NULL, $this->m_parent_projectid);
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
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides = NULL) {
        try 
        {
            $main_tablename = 'taskcomments-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
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
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            
            $dashboard = $this->m_oPageHelper->getContextDashboardElements($this->m_taskid);
            $form['data_entry_area1']['context_dashboard'] = $dashboard;
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            $rows = "\n";
            
            $task_status_by_code = $this->m_oMapHelper->getWorkitemStatusByCode();
            $action_status_by_code = $this->m_oMapHelper->getActionStatusByCode();

            $taskid=$this->m_taskid;
            $allcommentsmap = $this->m_oMapHelper->getWorkitemComments($taskid);
            $comments = $allcommentsmap['comments'];
            $actionitems = $allcommentsmap['actionitems'];
            $naturalorder_ar = $allcommentsmap['naturalorder'];
            $maxid = $allcommentsmap['maxid'];
            $maxid_digits = strlen($maxid);
            
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
                    $status_record = $task_status_by_code[$status_cd_at_time_of_com];
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
                    if(!array_key_exists($comid, $actionitems))
                    {
                        $action_requested_markup = '<span class="colorful-warning">Yes (todo)</span>';
                        $action_todo = TRUE;
                    } else {
                        $resolutions = $actionitems[$comid];
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
                
                $updated_dt_markup = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($detail['updated_dt']);
                
                if (strpos($this->m_aPersonRights, 'V') === FALSE || !isset($this->m_urls_arr['view'])) {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View', $this->m_urls_arr['view'], array('query' => array('comid' => $comid)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('comid'=>$comid)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view details of #{$comid}' href='$view_page_url'><img src='$view_icon_url'/></a>";                            
                }
                if (strpos($this->m_aPersonRights, 'E') === FALSE || !isset($this->m_urls_arr['edit'])) {
                    $sReplyMarkup = '';
                    $sEditMarkup = '';
                } else {
                    //$sReplyMarkup = l('Reply', $this->m_urls_arr['reply'], array('query' => array('parent_comid' => $comid)));
                    //$sEditMarkup = l('Edit', $this->m_urls_arr['edit'], array('query' => array('comid' => $comid)));
                    $reply_page_url = url($this->m_urls_arr['reply'], array('query'=>array('parent_comid'=>$comid)));
                    $reply_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('reply');
                    $sReplyMarkup = "<a title='reply to #{$comid}' href='$reply_page_url'><img src='$reply_icon_url'/></a>";
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('comid'=>$comid)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit #{$comid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if ($has_replies || strpos($this->m_aPersonRights, 'D') === FALSE || !isset($this->m_urls_arr['delete'])) {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete', $this->m_urls_arr['delete'], array('query' => array('comid' => $comid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('comid'=>$comid)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete sprint#{$comid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }

                $rows .= "\n" 
                        . '<tr id="'.$comid.'">'
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
                . '<th><span class="small-text" title="Ordering of comments so that they are naturally next to their replies">' 
                    . "<img alt='attached files count' src='$naturalsort_imgurl' />" . '</span></th>'
                . '<th><span title="Each comment has a unique ID; the root of each thread is the leftmost ID displayed">' . t('Thread') . '</span></th>'
                . '<th><span title="Creator of the comment">' . t('Author') . '</span></th>'
                . '<th><span title="Attached files">'."<img alt='attached files count' src='$paperclip_imgurl' />" . '</span></th>'
                . '<th><span title="Has author requested action?">' . t('Action Requested') . '</span></th>'
                . '<th><span title="Author' ."'". 's Level of concern for having this action request resolved">' . t('LC') . '</span></th>'
                . '<th><span title="If set, indicates the reply has resolved a requested action">' . t('Action Response') . '</span></th>'
                . '<th><span title="Title if set, else this is a snippet of the actual comment">' . t('Comment') . '</span></th>'
                . '<th><span title="Task status at time of comment creation">' . t('Task Status') . '</span></th>'
                . '<th><span title="Date of most recent update (creation date if never updated)">' . t('Date') . '</span></th>'
                . '<th class="action-options">' . t('Action Options').'</th>'
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
                    $add_link_markup = l('Add New Task Comment Thread'
                            , $this->m_urls_arr['add']
                            , array('query' => array('taskid' => $this->m_taskid),
                                'attributes'=>array('class'=>'action-button'))
                            );
                    $form['data_entry_area1']['action_buttons']['addtask'] = array('#type' => 'item'
                        , '#markup' => $add_link_markup);
                }
            }

            if (isset($this->m_urls_arr['return'])) {
                $exit_link_markup = l('Exit', $this->m_urls_arr['return']
                            , array('attributes'=>array('class'=>'action-button'))
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
