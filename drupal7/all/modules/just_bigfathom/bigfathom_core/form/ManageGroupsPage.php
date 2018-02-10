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
require_once 'helper/GroupPageHelper.php';

/**
 * This class returns the list of available groups
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageGroupsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights  = NULL;
    protected $m_oPageHelper    = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addgroup';
        $urls_arr['edit'] = 'bigfathom/editgroup';
        $urls_arr['view'] = 'bigfathom/viewgroup';
        $urls_arr['delete'] = 'bigfathom/deletegroup';
        $urls_arr['group_membership'] = 'bigfathom/groups/membership';//&projectid=5
        
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
        $this->m_aDataRights  = $aDataRights;
        
        $this->m_oPageHelper = new \bigfathom\GroupPageHelper($urls_arr);
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {

            $main_tablename = 'group-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");

            $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
            $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            $group_membership_icon_url = \bigfathom\UtilityGeneralFormulas::getActiveIconURLForPurposeName('group_membership');
            $group_membership_dim_icon_url = \bigfathom\UtilityGeneralFormulas::getInactiveIconURLForPurposeName('group_membership');
            
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['visualization-container']))
            {
                $html_classname_overrides['visualization-container'] = 'visualization-container';
            }
            if(!isset($html_classname_overrides['table-container']))
            {
                $html_classname_overrides['table-container'] = 'table-container';
            }
            if(!isset($html_classname_overrides['container-inline']))
            {
                $html_classname_overrides['container-inline'] = 'container-inline';
            }
            if(!isset($html_classname_overrides['action-button']))
            {
                $html_classname_overrides['action-button'] = 'action-button';
            }
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "A group is a named collection of people.  You can examine the membership of each group via this interface."
                . "</p>",
                '#suffix' => '</div>',
            );
            
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $leaders_by_id = $this->m_oPageHelper->getGroupLeaderOptions();
            //$is_systemadmin = $usrm['summary']['is_systemadmin'];
            $is_systemdatatrustee = $usrm['summary']['is_systemdatatrustee'];
            
            $cmi = $this->m_oContext->getCurrentMenuItem();

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all = $this->m_oMapHelper->getGroupsByID(TRUE);
            //DebugHelper::debugPrintNeatly($all,FALSE,"group stuff thing here.....") ;           
            foreach($all as $groupid=>$record)
            {
                $group_nm = $record['group_nm'];
                $activeyesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                $purpose_tx = $record['purpose_tx'];
                $leader_personid = $record['leader_personid'];
                if(isset($leaders_by_id[$leader_personid]))
                {
                    $leader_name = $leaders_by_id[$leader_personid];
                } else {
                    $leader_name = '';
                }
                $is_leader = $leader_personid == $user->uid;
                if($groupid == Context::$SPECIALGROUPID_EVERYONE 
                   || $groupid == Context::$SPECIALGROUPID_NOBODY)
                {
                    $show_membership = FALSE;
                    $allow_edit = FALSE;
                    $allow_delete = FALSE;
                } else {
                    $show_membership = TRUE;
                    $allow_edit = $is_leader || $is_systemdatatrustee;
                    $allow_delete = $allow_edit && ($groupid != Context::$SPECIALGROUPID_DEFAULT_PRIVCOLABS);
                }
                $membership_count = $record['membership_count'];
                if(strlen($purpose_tx) > 120)
                {
                    $purpose_tx = substr($purpose_tx, 0,120) . '...';
                }
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('groupid'=>$groupid)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('groupid'=>$groupid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view #{$groupid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                
                $group_membership_page_url = url($this->m_urls_arr['group_membership'], array('query'=>array('groupid'=>$groupid, 'return' => $cmi['link_path'])));
                if(!$allow_edit || strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    if($show_membership)
                    {
                        $sViewGroupMembersIconMarkup = "<a title='jump to group membership for #{$groupid}' href='$group_membership_page_url'><img src='$group_membership_dim_icon_url'/></a>";
                    } else {
                        $sViewGroupMembersIconMarkup = "";
                    }
                } else {
                    $sViewGroupMembersIconMarkup = "<a title='jump to group membership for #{$groupid}' href='$group_membership_page_url'><img src='$group_membership_icon_url'/></a>";
                }
                
                if(!$allow_edit || strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('groupid'=>$groupid)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('groupid'=>$groupid, 'return' => $cmi['link_path'])));
                    $sEditMarkup = "<a title='edit #{$groupid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$allow_delete || strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('groupid'=>$groupid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('groupid'=>$groupid, 'return' => $cmi['link_path'])));
                    $sDeleteMarkup = "<a title='delete #{$groupid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $groupname_markup = "<span title='#$groupid'>$group_nm</span>";
                $leader_markup = "<span title='#$leader_personid'>$leader_name</span>";
                $rows   .= "\n".'<tr><td>'
                        .$groupname_markup.'</td><td>'
                        .$leader_markup.'</td><td>'
                        .$membership_count.'</td><td>'
                        .$purpose_tx.'</td><td>'
                        .$record['updated_dt'].'</td><td>'
                        .$record['created_dt'].'</td>'
                        .'<td class="action-options">'
                        . $sViewMarkup.' '
                        . $sViewGroupMembersIconMarkup. ' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th class="nowrap" >'.t('Group Name').'</th>'
                                . '<th class="nowrap" >'.t('Leader').'</th>'
                                . '<th class="nowrap"  title="Membership Count: The number of persons that are members of the group.">'.t('MC').'</th>'
                                . '<th class="nowrap"  title="The purpose of the group">'.t('Purpose').'</th>'
                                . '<th datatype="datetime">'.t('Updated').'</th>'
                                . '<th datatype="datetime">'.t('Created').'</th>'
                                . '<th datatype="html" class="action-options">' . t('Action Options').'</th>'
                                . '</tr>'
                                . '</thead>'
                                . '<tbody>'
                                . $rows
                                .  '</tbody>'
                                . '</table>'
                                . '<br>');


            $form["data_entry_area1"]['action_buttons'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div class="'.$html_classname_overrides['container-inline'].'">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );

            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Create New Group',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addgroup'] = array('#type' => 'item'
                            , '#markup' => $final_button_markup);
                }
            }

            if(isset($this->m_urls_arr['return']))
            {
                $exit_link_markup = l('Exit',$this->m_urls_arr['return']
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
