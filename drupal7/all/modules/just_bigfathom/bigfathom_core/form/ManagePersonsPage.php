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

/**
 * This class returns the list of available people
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManagePersonsPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aPersonRights  = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_oTextHelper    = NULL;

    private $m_oUAH = NULL;
    private $m_drupaluser_uid2name_map = NULL;
    private $m_is_systemadmin = NULL;
    
    public function __construct($urls_arr=NULL)
    {
        if(empty($urls_arr))
        {
            $urls_arr = [];
        }
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $pmi = $this->m_oContext->getParentMenuItem();
        if(empty($urls_arr['return']))
        {
            $urls_arr['return'] = $pmi['link_path'];
        }
        
        module_load_include('php','bigfathom_core','core/TextHelper');
        $this->m_oTextHelper = new \bigfathom\TextHelper();
        //UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'dashboard', 'bigfathom/dashboards/user');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'add', 'bigfathom/addperson');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'edit', 'bigfathom/editperson');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'view', 'bigfathom/viewperson');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'delete', 'bigfathom/deleteperson');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'return', 'bigfathom/topinfo/sitemanage');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'manage_availability', 'bigfathom/mng_person_availability');
        
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee_yn = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        if($this->m_is_systemadmin || $this->m_is_systemdatatrustee_yn)
        {
            $aPersonRights='VAED';
        } else {
            $aPersonRights='V';
        }
        
        $this->m_urls_arr           = $urls_arr;
        $this->m_aPersonRights      = $aPersonRights;
        
        $this->m_oPageHelper = new \bigfathom\PersonPageHelper($urls_arr);
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
        $this->m_oUAH = new \bigfathom\UserAccountHelper();
        $maps = $this->m_oUAH->getMaps();
        $this->m_drupaluser_uid2name_map = $maps['drupaluser_uid2name'];
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

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
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
                . "This interface shows all people defined in the application."
                . "</p>",
                '#suffix' => '</div>',
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $is_trusted_with_system_data = $this->m_is_systemadmin || $this->m_is_systemdatatrustee_yn;
            $cmi = $this->m_oContext->getCurrentMenuItem();
            
            $rows = "\n";
            $roles_lookup = $this->m_oMapHelper->getRolesByID();
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

                $allow_edit = $is_trusted_with_system_data;
                
                $this_is_master_systemadmin = ($personid == UserAccountHelper::$MASTER_SYSTEMADMIN_UID);
                
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
                if(!$this->m_is_systemadmin || !$allow_edit || strpos($this->m_aPersonRights,'D') === FALSE || !isset($this->m_urls_arr['delete']) || $this_is_master_systemadmin)
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('personid'=>$personid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete for {$shortname}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                if(!$allow_edit || strpos($this->m_aPersonRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sMAMarkup = '';
                } else {
                    $mavail_page_url = url($this->m_urls_arr['manage_availability'], array('query'=>array('personid'=>$personid, 'return' => $cmi['link_path'])));
                    $sMAMarkup = "<a title='edit custom availability of {$shortname}' href='$mavail_page_url'><i class='fa fa-calendar'area-hidden='true'></i></a>";
                }
                $map_person2role = $record['maps']['person2role'];
                $prefroles_markup = '';
                
                foreach($map_person2role as $roleid)
                {
                    $prefroles_markup .= '<li><span title="#'.$roleid.'">'.$roles_lookup[$roleid]['role_nm'] . '</span> ';
                }
                if($prefroles_markup > '')
                {
                    $prefroles_markup = "<ul>$prefroles_markup</ul>";
                }
                
                //$trustee_markup = $record['datatrustee_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $gl_markup = $record['groupleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $pm_markup = $record['projectleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $sl_markup = $record['sprintleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wt_markup = $record['tester_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wc_markup = $record['workitemcreator_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wo_markup = $record['workitemowner_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $primary_locationid = $record['primary_locationid'];
                $location_markup = "<span title='#$primary_locationid'>{$record['location_shortname']}</span>";
                $shortname_markup = "<span title='#$personid'>{$shortname}</span>";
                if(!$is_trusted_with_system_data)
                {
                    $usernamemarkup = "";
                } else {
                    $usernamemarkup = "<span title='#$personid'>".$shortname_markup.'</span></td><td>';
                }
                
                if($updated_dt !== $created_dt)
                {
                    $updated_dt_markup = "<span title='Originally created $created_dt'>$updated_dt</span>";
                } else {
                    $updated_dt_markup = "<span title='Never edited'>$updated_dt</span>";
                }
                
                $action_buttons_markup = "$sViewMarkup $sEditMarkup $sMAMarkup $sDeleteMarkup";
                
                $rows   .= "\n".'<tr><td>'
                        .$usernamemarkup
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
            if(!$is_trusted_with_system_data)
            {
                $usernamecolheading_markup = "";
            } else {
                $usernamecolheading_markup = '<th><span title="The login name of this user">'.t('Shortname').'</span></th>';
            }
            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                    '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . $usernamecolheading_markup
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

            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aPersonRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Add Person',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addperson'] = array('#type' => 'item'
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
