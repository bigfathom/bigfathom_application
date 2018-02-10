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
require_once 'helper/PersonPageHelper.php';
require_once 'helper/GroupPageHelper.php';

/**
 * This class returns the list of available people
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageGroupMembershipPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aPersonRights  = NULL;
    protected $m_oPersonPageHelper    = NULL;
    protected $m_oTextHelper    = NULL;

    private $m_oUAH = NULL;
    private $m_drupaluser_uid2name_map = NULL;
    private $m_is_systemadmin = NULL;
    private $m_is_systemdatatrustee = NULL;
    
    private $m_groupid = NULL;
    
    public function __construct($groupid,$urls_arr=NULL)
    {
        if(empty($groupid))
        {
            throw new \Exception("Missing required groupid!");
        }
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        if($groupid == Context::$SPECIALGROUPID_EVERYONE 
           || $groupid == Context::$SPECIALGROUPID_NOBODY)
        {
            //Dont even show the page for these.
            throw new \Exception("You cannot edit the content of special groups!");
        }
                    
        $this->m_groupid = $groupid;
        if(empty($urls_arr))
        {
            $urls_arr = [];
        }
        $pmi = $this->m_oContext->getParentMenuItem();
        if(empty($urls_arr['return']))
        {
            $urls_arr['return'] = $pmi['link_path'];
        }
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'view', 'bigfathom/viewperson');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'return', 'bigfathom/topinfo/sitemanage');

        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        
        $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
        if(!$loaded_uah)
        {
            throw new \Exception('Failed to load the UserAccountHelper class');
        }
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        
        $this->m_oUAH = $uah;//new \bigfathom\UserAccountHelper();
        $this->m_oPersonPageHelper = new \bigfathom\PersonPageHelper($urls_arr);
        $this->m_oGroupPageHelper = new \bigfathom\GroupPageHelper($urls_arr);
        
        $this->m_group_details = $this->m_oGroupPageHelper->getFieldValues($this->m_groupid);

        global $user;
        $this->m_is_group_leader = ($this->m_group_details['leader_personid'] == $user->uid);
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        if($this->m_is_group_leader || $this->m_is_systemdatatrustee || $this->m_is_systemadmin)
        {
            $aPersonRights='VAED';
            $this->m_can_edit_membership = TRUE;
        } else {
            $aPersonRights='V';
            $this->m_can_edit_membership = FALSE;
        }
        
        $this->m_urls_arr       = $urls_arr;
        $this->m_aPersonRights  = $aPersonRights;
        
        $maps = $this->m_oUAH->getMaps();
        $this->m_drupaluser_uid2name_map = $maps['drupaluser_uid2name'];
    }
    
    function getContextPanelMarkup()
    {
        $group_nm = $this->m_group_details['group_nm'];        
        $purpose_tx = $this->m_group_details['purpose_tx'];
        
        $markup = "<h1>Group: <span title='groupid#{$this->m_groupid}'>$group_nm</span></h1>";
        $markup .= "\n<h3>Purpose: {$purpose_tx}</h3>";
        
        return $markup;    
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $main_tablename = 'person-table';
            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $user;
            global $base_url;
            $send_urls = $this->m_urls_arr;
            $send_urls['base_url'] = $base_url;
            $send_urls['images'] = $base_url .'/'. $theme_path.'/images';
            drupal_add_js(array('personid'=>$user->uid,'groupid'=>$this->m_groupid
                    ,'myurls' => $send_urls), 'setting');
            
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/ManageGroupMembership.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            $member_personid_map = [];
            $member_roleid_map = [];
            foreach($this->m_group_details['member_list'] as $detail)
            {
                $personid = $detail['personid'];
                $roleid = $detail['roleid'];
                $member_personid_map[$personid] = $personid;
                $member_roleid_map[$roleid] = $roleid;
            }
        
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
                
            $contextpanel_markup = $this->getContextPanelMarkup();    
                
            $form['data_entry_area1']["context_panel"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-contextpanel">',
                '#markup' => "<p>"
                . $contextpanel_markup
                . "</p>",
                '#suffix' => '</div>',
            );
            
            if($this->m_can_edit_membership)
            {
                $blurb_tx = "This interfaces shows members of the group.  Changes are saved as you click on the membership checkbox area.";
            } else {
                $blurb_tx = "This interfaces shows members of the group.";
            }
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . $blurb_tx
                . "</p>",
                '#suffix' => '</div>',
            );

            $totalsid = "totals-" . $main_tablename;
            $form["data_entry_area1"]['latest_stats'] = array(
                 '#type' => 'item', 
                 '#prefix' => '<div id="' . $totalsid . '" class="live-info-display">',
                 '#suffix' => '</div>', 
                 '#tree' => TRUE,
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $is_systemadmin = $this->m_is_systemadmin; 
            $is_systemdatatrustee = $this->m_is_systemdatatrustee;
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            $rows = "\n";
            $roles_lookup = $this->m_oMapHelper->getRolesByID();
            //$systemroles_lookup = $this->m_oMapHelper->getSystemRolesByID();
            $all = $this->m_oMapHelper->getPersonsByID();
            foreach($all as $personid=>$record)
            {
                
                $has_default_baseline_avail_yn = $record['has_default_baseline_avail_yn'];
                $baseline_avail_nm = $record['baseline_avail_nm'];
                $baseliine_avail_hpd = $record['baseliine_avail_hpd'];
                $dswh_tooltip = "$baseline_avail_nm @ $baseliine_avail_hpd hours/day";
                $dswh_markup = "[SORTNUM:$baseliine_avail_hpd]" 
                        . ($has_default_baseline_avail_yn ? '<span title="'.$dswh_tooltip.'" class="colorful-yes">Yes</span>' : '<span title="'.$dswh_tooltip.'" class="colorful-no">No</span>');
                
                $shortname = $record['shortname'];
                $activeyesno = ($record['active_yn'] == 1 ? '<span class="colorful-yes">Yes</span>' : '<span class="colorful-no">No</span>');
                $can_login_yesno = ($record['can_login_yn'] == 1 ? '<span class="colorful-yes">Yes</span>' : '<span class="colorful-no">No</span>');
                
                $updated_dt = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['updated_dt']);
                $created_dt = $this->m_oTextHelper->getJustDateTextFromDateTimeUnlessToday($record['created_dt']);

                $allow_edit = $is_systemadmin;
                
                $this_is_master_systemadmin = ($personid == UserAccountHelper::$MASTER_SYSTEMADMIN_UID);
                
                $allowrowedit_yn = $this->m_can_edit_membership;
                if(strpos($this->m_aPersonRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('personid'=>$personid, 'return' => $cmi['link_path'])));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view details of {$shortname}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    
                }
                if(!$allow_edit || strpos($this->m_aPersonRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('personid'=>$personid, 'return' => $cmi['link_path'])));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit {$shortname}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$allow_edit || strpos($this->m_aPersonRights,'D') === FALSE || !isset($this->m_urls_arr['delete']) || $this_is_master_systemadmin)
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('personid'=>$personid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete for {$shortname}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
                $map_person2role = $record['maps']['person2role'];
                $prefroles_markup = '';
                $is_member = !empty($member_personid_map[$personid]);
                    
                foreach($map_person2role as $roleid)
                {
                    if(!$is_member || empty($member_roleid_map[$roleid]))
                    {
                        $prefroles_markup .= '<li><span title="#'.$roleid.'">'.$roles_lookup[$roleid]['role_nm'] . '</span> ';
                    } else {
                        $prefroles_markup .= '<li><span title="#'.$roleid.'"><strong>'.$roles_lookup[$roleid]['role_nm'] . '</strong></span> ';
                    }
                }
                if($is_member || $prefroles_markup > '')
                {
                    $prefroles_markup = "<ul>$prefroles_markup</ul>";
                    $can_be_member = TRUE;
                } else {
                    $can_be_member = FALSE;
                }
                
                if($can_be_member)
                {
                    $gl_markup = $record['groupleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $pm_markup = $record['projectleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $sl_markup = $record['sprintleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $wt_markup = $record['tester_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $wc_markup = $record['workitemcreator_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $wo_markup = $record['workitemowner_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                    $primary_locationid = $record['primary_locationid'];
                    $location_markup = "<span title='#$primary_locationid'>{$record['location_shortname']}</span>";
                    $shortname_markup = "<span title='#$personid'>{$shortname}</span>";
                    if(!$is_systemdatatrustee && !$is_systemadmin)
                    {
                        $username_conditionalmarkup = "";
                    } else {
                        $username_conditionalmarkup = "<span title='#$personid'>".$shortname_markup.'</span></td><td>';
                    }

                    if($updated_dt !== $created_dt)
                    {
                        $updated_dt_markup = "<span title='Originally created $created_dt'>$updated_dt</span>";
                    } else {
                        $updated_dt_markup = "<span title='Never edited'>$updated_dt</span>";
                    }

                    $action_buttons_markup = "$sViewMarkup $sEditMarkup $sDeleteMarkup";

                    $rows   .= "\n".'<tr allowrowedit="'.$allowrowedit_yn.'" id="' . $personid . '"  data_groupid="' . $this->m_groupid . '"><td>'
                            .$is_member.'</td><td>'
                            .$username_conditionalmarkup
                            .$record['first_nm'].'</td><td>'
                            .$record['last_nm'].'</td><td>'
                            .$prefroles_markup.'</td><td>'
                            .$gl_markup.'</td><td>'
                            .$pm_markup.'</td><td>'
                            .$sl_markup.'</td><td>'
                            .$wc_markup.'</td><td>'
                            .$wo_markup.'</td><td>'
                            .$wt_markup.'</td><td>'
                            .$dswh_markup.'</td><td>'
                            .$location_markup.'</td><td>'
                            .$activeyesno.'</td><td>'
                            .$can_login_yesno.'</td><td>'
                            . $updated_dt_markup . '</td>'
                            . '<td class="action-options">' . $action_buttons_markup . '</td>'
                            .'</tr>';
                }
            }
            if(!$is_systemadmin && !$is_systemdatatrustee)
            {
                $usernamecolheading_conditionalmarkup = "";
            } else {
                $usernamecolheading_conditionalmarkup = '<th><span title="The login name of this user">'.t('Shortname').'</span></th>';
            }
            
            $allow_row_edit =  $is_systemdatatrustee || $is_systemadmin;
            if($allow_row_edit)
            {
                $editable_col_flag = ' editable="1" ';
                $canedit_classname_markup = "canedit";
            } else {
                $editable_col_flag = "";
                $canedit_classname_markup = "noedit";
            }
            
            $member_colname = "ismember";
            $member_tooltip = "Is a member of the group";
            
            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>' 
                                . '<th class="nowrap" colname="' . $member_colname . '" ' . $editable_col_flag . ' datatype="boolean"><span title="'.$member_tooltip.'">Member</span></th>' 
                                . $usernamecolheading_conditionalmarkup
                                . '<th>'.t('First Name').'</th>'
                                . '<th>'.t('Last Name').'</th>'
                                . '<th><span title="Preferred roles for this person on projects">'.t('Project Role Prefs').'</span></th>'
                                . '<th><span title="Group Leader">'.t('GL').'</span></th>'
                                . '<th><span title="Project Leader">'.t('PL').'</span></th>'
                                . '<th><span title="Sprint Leader">'.t('SL').'</span></th>'
                                . '<th><span title="Workitem Creator">'.t('WC').'</span></th>'
                                . '<th><span title="Workitem Owner">'.t('WO').'</span></th>'
                                . '<th><span title="Workitem Tester">'.t('WT').'</span></th>'
                                . '<th datatype="formula"><span title="Yes if the person is mapped to the Default Standard Work Hours of the application">'.t('DSWH').'</span></th>'
                                . '<th><span title="Primary location of this person">'.t('Location').'</span></th>'
                                . '<th><span title="Is this user available for assignment to new projects?">'.t('Available').'</span></th>'
                                . '<th><span title="Is this user allowed to log into the application?">'.t('Can Login').'</span></th>'
                                . '<th datatype="formula">'.t('Updated').'</th>'
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
