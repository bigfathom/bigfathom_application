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
 * This class returns the list of open project communications
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageCommunicationOverviewPage extends \bigfathom\ASimpleFormPage {

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
        $urls_arr = array();
        $urls_arr['communications']['project']['url'] = 'bigfathom/project/mng_comments';
        $urls_arr['communications']['project']['keyname'] = 'projectid';
        $urls_arr['communications']['workitem']['url'] = 'bigfathom/workitem/mng_comments';
        $urls_arr['communications']['workitem']['keyname'] = 'workitemid';
        $urls_arr['communications']['sprint']['url'] = 'bigfathom/sprint/mng_comments';
        $urls_arr['communications']['sprint']['keyname'] = 'sprintid';
        $urls_arr['communications']['testcase']['url'] = 'bigfathom/testcase/mng_comments';
        $urls_arr['communications']['testcase']['keyname'] = 'testcaseid';
        $urls_arr['communications']['group']['url'] = 'bigfathom/group/mng_comments';
        $urls_arr['communications']['group']['keyname'] = 'groupid';
        $urls_arr['view']['workitem']['url'] = 'bigfathom/workitem/view';
        $urls_arr['view']['workitem']['keyname'] = 'workitemid';
        $urls_arr['edit']['workitem']['url'] = 'bigfathom/workitem/edit';
        $urls_arr['edit']['workitem']['keyname'] = 'workitemid';
        $urls_arr['delete']['workitem']['url'] = 'bigfathom/workitem/delete';
        $urls_arr['delete']['workitem']['keyname'] = 'workitemid';
        $urls_arr['view']['sprint']['url'] = 'bigfathom/sprint/view';
        $urls_arr['view']['sprint']['keyname'] = 'sprintid';
        $urls_arr['view']['testcase']['url'] = 'bigfathom/viewtestcase';
        $urls_arr['view']['testcase']['keyname'] = 'testcaseid';
        $urls_arr['edit']['sprint']['url'] = 'bigfathom/sprint/edit';
        $urls_arr['edit']['sprint']['keyname'] = 'sprintid';
        $urls_arr['delete']['sprint']['url'] = 'bigfathom/sprint/delete';
        $urls_arr['delete']['sprint']['keyname'] = 'sprintid';
        $urls_arr['delete']['testcase']['url'] = 'bigfathom/testcase/delete';
        $urls_arr['delete']['testcase']['keyname'] = 'testcaseid';
        $urls_arr['view']['group']['url'] = 'bigfathom/group/view';
        $urls_arr['view']['group']['keyname'] = 'groupid';
        
        $urls_arr['main_visualization'] = '';
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
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
            $main_tablename = 'comm-overview-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');

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

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];
            
            $rparams_ar = [];
            $rparams_ar['projectid'] = $this->m_parent_projectid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $no_dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_dashboard');
            $dashboard_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('dashboard');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            
            $rows = "\n";
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            $today_dt = date("Y-m-d", time());
            $oTextHelper = new \bigfathom\TextHelper();
            
            $relevant_projectids = $this->m_parent_projectid;
            $project_communicationbundle = $this->m_oMapHelper->getCommunicationSummaryBundleForProject(
                $relevant_projectids);
            
        //DebugHelper::debugPrintNeatly($project_communicationbundle, FALSE, "COMM BUNDLE for $relevant_projectids .......");            
            $map_open_request_detail = $project_communicationbundle['map_open_request_detail'];
            $map_open_request_summary = $project_communicationbundle['map_open_request_summary'];
            $itemname_lookup = $project_communicationbundle['itemname_lookup'];
            foreach ($map_open_request_summary as $thingname => $by_thingid) 
            {
                foreach ($by_thingid as $thingid => $by_concernvalue)
                {
                    $age_by_conern = [];
                    $show_stats = [];
                    $newest_dt=NULL;
                    foreach ($by_concernvalue as $concernlabel => $detail)
                    {
                        $itemname = $itemname_lookup[$thingname][$thingid]['name'];
                        $itemimportance = $itemname_lookup[$thingname][$thingid]['importance'];
                        if($itemimportance >= 50)
                        {
                            $impclassname = "item-important";
                        } else {
                            $impclassname = "item-notimportant";
                        }
                        $itemimportance_markup = "<span class='$impclassname'>$itemimportance</span>";
                        
                        $itemname_markup = $itemname;
                        $age_by_conern[$concernlabel] = array(
                            'new'=>$detail['new']   
                            ,'older_than2days'=>$detail['older_than2days']   
                            ,'older_than7days'=>$detail['older_than7days']   
                            ,'older_than14days'=>$detail['older_than14days']   
                            ,'older_than30days'=>$detail['older_than30days']   
                        );
                        $show_stats[$concernlabel]['count'] = $detail['count'];
                        if(!empty($detail['newest_dt']))
                        {
                            if(empty($newest_dt) || $newest_dt < $detail['newest_dt'])
                            {
                                $newest_dt = $detail['newest_dt']; 
                            }
                            $show_stats[$concernlabel]['newest_days'] = $detail['newest_dt'];
                            $show_stats[$concernlabel]['newest_days'] = 
                                \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($detail['newest_dt'], $today_dt);
                        }
                        if(!empty($detail['oldest_dt']))
                        {
                            $show_stats[$concernlabel]['oldest_dt'] = $detail['oldest_dt'];
                            $show_stats[$concernlabel]['oldest_days'] = 
                                \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($detail['oldest_dt'], $today_dt);
                        }
                    }
                    
                    if(empty($show_stats['High']))
                    {
                        $show_stats['High'] = [];
                        $hc_markup = "";
                        $ha_markup = "";
                    } else {
                        $hc_markup = "<span class='concern-high'>".$show_stats['High']['count']."</span>";
                        $ha_markup = $show_stats['High']['oldest_days'];
                    }
                    if(empty($show_stats['Medium']))
                    {
                        $show_stats['Medium'] = [];
                        $mc_markup = "";
                        $ma_markup = "";
                    } else {
                        $mc_markup = "<span class='concern-medium'>".$show_stats['Medium']['count']."</span>";
                        $ma_markup = $show_stats['Medium']['oldest_days'];
                    }
                    if(empty($show_stats['Low']))
                    {
                        $show_stats['Low'] = [];
                        $lc_markup = "";
                        $la_markup = "";
                    } else {
                        $lc_markup = $show_stats['Low']['count'];
                        $la_markup = $show_stats['Low']['oldest_days'];
                    }
                
                    $rsbundle = \bigfathom\UtilityGeneralFormulas::getItemActionRequestUrgencyScoreBundle($itemimportance
                            ,$show_stats['High'],$show_stats['Medium'],$show_stats['Low']);
                    $rankscore = $rsbundle['score'];
                    $rsreason = "Score computed as follows: " . implode(" and ",$rsbundle['reason']);

                    $rsmarkup = "[SORTNUM:$rankscore]<span title='$rsreason'>$rankscore</span>";

                    $communicate_page_url = url($this->m_urls_arr['communications'][$thingname]['url']
                                , array('query'=>array($this->m_urls_arr['communications'][$thingname]['keyname']=>$thingid
                                , 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                    $sCommunicationMarkup = "<a title='jump to communications for $thingname#{$thingid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    if(!isset($this->m_urls_arr['view'][$thingname]))
                    {
                        $sViewMarkup = '';
                    } else {
                        $view_page_url = url($this->m_urls_arr['view'][$thingname]['url']
                                , array('query'=>array($this->m_urls_arr['communications'][$thingname]['keyname']=>$thingid
                                , 'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                        $sViewMarkup = "<a title='view details of $thingname#{$thingid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    }

                    if(empty($newest_dt))
                    {
                        $newest_dt_markup = "";
                    } else {
                        $nclassname = "";
                        $showdate = $oTextHelper->getJustDateTextFromDateTimeUnlessToday($newest_dt);
                        $newest_days = \bigfathom\UtilityGeneralFormulas::getSimpleDayCountBetweenDates($newest_dt, $today_dt);
                        if($newest_days <= 1)
                        {
                            $nclassname = "ar-verynew";    
                        } else
                        if($newest_days <= 3)
                        {
                            $nclassname = "ar-new";    
                        } else
                        if($newest_days <= 5)
                        {
                            $nclassname = "ar-recent";    
                        } else
                        if($newest_days >= 15)
                        {
                            $nclassname = "ar-old";    
                        } else
                        if($newest_days >= 30)
                        {
                            $nclassname = "ar-veryold";    
                        }
                        $newest_dt_markup = "<span title='$newest_days days' class='$nclassname'>$showdate</span>";
                    }

                    $thingabbr_markup = "<span title='$thingname'>" . strtoupper(substr($thingname,0,1)) . "</span>";
                    
                    $isyours = "TBD";
                    $rows .= "\n" 
                            . '<tr id="'.$thingid.' isyours="' . $isyours . '">'
                            . '<td>'
                            . $rsmarkup . '</td><td>'
                            . $thingabbr_markup . '</td><td>'
                            . $thingid . '</td><td>'
                            . $itemname_markup . '</td><td>'
                            . $itemimportance_markup . '</td><td>'
                            . $hc_markup . '</td><td>'
                            . $ha_markup . '</td><td>'
                            . $mc_markup . '</td><td>'
                            . $ma_markup . '</td><td>'
                            . $lc_markup . '</td><td>'
                            . $la_markup . '</td><td>'
                            . $newest_dt_markup . '</td>'
                            .'<td class="action-options">'
                                . $sCommunicationMarkup.' '
                                . $sViewMarkup.' '
                            .'</tr>';
                }
            }            

            $form["data_entry_area1"]['table_container']['maintable'] = array('#type' => 'item',
                '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                . '<thead>'
                . '<tr>'
                . '<th datatype="formula" title="An urgency score based on item importance to you, level of concern declared in the action requests, and the age of the requests.  Higher values are higher urgency.">' . t('Urgency') . '</th>'
                . '<th datatype="formula" title="Subject of the communication">' . t('S') . '</th>'
                . '<th datatype="numid" title="Unique identifier of the subject item">' . t('Item ID') . '</th>'
                . '<th title="Name of the subject item">' . t('Item Name') . '</th>'
                . '<th datatype="formula" title="Importance score for the subject item">' . t('IS') . '</th>'
                . '<th datatype="formula" title="The number of action requests where level of concern is marked as high">' . t('High<br />Count') . '</th>'
                . '<th datatype="formula" title="Oldest number of days for high concern action request">' . t('High<br/>Age') . '</th>'
                . '<th datatype="formula" title="The number of action requests where level of concern is marked as medium">' . t('Medium<br />Count') . '</th>'
                . '<th datatype="formula" title="Oldest number of days for medium concern action request">' . t('Medium<br/>Age') . '</th>'
                . '<th datatype="formula" title="The number of action requests where level of concern is marked as low">' . t('Low<br />Count') . '</th>'
                . '<th datatype="formula" title="Oldest number of days for low concern action request">' . t('Low<br/>Age') . '</th>'
                . '<th datatype="datetime" title="Most recently updated action request">' . t('Most Recent') . '</th>'
                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                . '</tr>'
                . '</thead>'
                . '<tbody>'
                . $rows
                . '</tbody>'
                . '</table>');


            $form["data_entry_area1"]['action_buttons'] = array(
                '#type' => 'item',
                '#prefix' => '<div class="' . $html_classname_overrides['container-inline'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['add']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $add_link_markup = l('Add New Topic Item',$this->m_urls_arr['add']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['addtopic'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
                }
            }

            if(isset($this->m_urls_arr['restore_all_parked']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $add_link_markup = l('Restore All Parked Topics Now'
                            , $this->m_urls_arr['restore_all_parked']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['restore_all_parked'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
                }
            }
            
            if(isset($this->m_urls_arr['trashcan2parked']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $add_link_markup = l('Move All Trashcan Topics to Parkinglot Now'
                            , $this->m_urls_arr['trashcan2parked']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['trashcan2parked'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
                }
            }
            
            if(isset($this->m_urls_arr['parked2trashcan']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $add_link_markup = l('Move All Parked Topics to Trashcan Now'
                            , $this->m_urls_arr['parked2trashcan']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['parked2trashcan'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
                }
            }
            
            if(isset($this->m_urls_arr['emptytrashcan']) && $this->m_parent_projectid != NULL)
            {
                if(strpos($this->m_aPersonRights,'D') !== FALSE)
                {
                    $add_link_markup = l('Empty Trashcan Now',$this->m_urls_arr['emptytrashcan']
                            , array('query' => array('projectid' => $this->m_parent_projectid, 'return' => $cmi['link_path'])
                                , 'attributes'=>array('class'=>'action-button')
                                ));
                    $form['data_entry_area1']['action_buttons']['emptytrashcan'] = array('#type' => 'item'
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
