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
require_once 'helper/RolePageHelper.php';

/**
 * This class returns the list of available roles
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageRolesPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_aDataRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_projectid = NULL;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        $this->m_oContext = \bigfathom\Context::getInstance();
        $urls_arr = [];
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        $urls_arr['add'] = 'bigfathom/addrole';
        $urls_arr['edit'] = 'bigfathom/editrole';
        $urls_arr['view'] = 'bigfathom/viewrole';
        $urls_arr['delete'] = 'bigfathom/deleterole';
        $urls_arr['main_visualization'] = '';   // '/sites/all/modules/bigfathom_core/visualization/MapPersonRoles.html';

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
        $this->m_aDataRights    = $aDataRights;
        
        $this->m_oPageHelper = new \bigfathom\RolePageHelper($urls_arr);
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
            $main_tablename = 'roles-table';
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
            global $base_url;

            if($this->m_urls_arr['main_visualization'] > '')
            {
                if(substr($this->m_urls_arr['main_visualization'],0,4) == 'http')
                {
                    $visualization_url = $this->m_urls_arr['main_visualization'];
                } else {
                    $visualization_url = $base_url.'/'.$this->m_urls_arr['main_visualization'];
                }
                $form['data_entry_area1']['main_visual'] = array(
                    '#type' => 'item', 
                    '#prefix' => '<iframe width="100%" height="200" scrolling=yes class="'.$html_classname_overrides['visualization-container'].'" src="'.$visualization_url.'">',
                    '#suffix' => '</iframe>', 
                    '#tree' => TRUE,
                );
            }
                
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all = $this->m_oMapHelper->getRolesByID($this->m_projectid);
            foreach($all as $workitemid=>$record)
            {
                $role_nm = $record['role_nm'];
                $role_nm_markup = "<span title='roleid#$workitemid'>$role_nm</span>";
                $activeyesno = ($record['active_yn'] == 1 ? 'Yes' : 'No');
                $purpose_tx = $record['purpose_tx'];
                if(strlen($purpose_tx) > 80)
                {
                    $purpose_tx = substr($purpose_tx, 0,80) . '...';
                }
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    //$sViewMarkup = l('View',$this->m_urls_arr['view'],array('query'=>array('role_id'=>$role_nm)));
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('role_id'=>$role_nm)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view details of {$role_nm}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    //$sEditMarkup = l('Edit',$this->m_urls_arr['edit'],array('query'=>array('role_id'=>$role_nm)));
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('role_id'=>$role_nm)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit role {$role_nm}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('role_id'=>$role_nm)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('role_id'=>$role_nm)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete role {$role_nm}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                $gl_markup = $record['groupleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $pm_markup = $record['projectleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $sl_markup = $record['sprintleader_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wc_markup = $record['workitemcreator_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wo_markup = $record['workitemowner_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $wt_markup = $record['tester_yn'] ? '<span class="colorful-yes">Y</span>' : '<span class="colorful-no">N</span>';
                $rows   .= "\n".'<tr><td>'
                        .$role_nm_markup.'</td><td>'
                        .$gl_markup.'</td><td>'
                        .$pm_markup.'</td><td>'
                        .$sl_markup.'</td><td>'
                        .$wc_markup.'</td><td>'
                        .$wo_markup.'</td><td>'
                        .$wt_markup.'</td><td>'
                        .$activeyesno.'</td><td>'
                        .$purpose_tx.'</td><td>'
                        .$record['updated_dt'].'</td><td>'
                        .$record['created_dt'].'</td>'
                        .'<td class="action-options">'
                        . $sViewMarkup.' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup.'</td>'
                        .'</tr>';
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                     '#markup' => '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . '<tr>'
                                . '<th colname="rolenm">'.t('Role Name').'</th>'
                                . '<th><span title="Group Leader">'.t('GL').'</span></th>'
                                . '<th><span title="Project Leader">'.t('PL').'</span></th>'
                                . '<th><span title="Sprint Leader">'.t('SL').'</span></th>'
                                . '<th><span title="Workitem Creator">'.t('WC').'</span></th>'
                                . '<th><span title="Workitem Owner">'.t('WO').'</span></th>'
                                . '<th><span title="Workitem Tester">'.t('WT').'</span></th>'
                                . '<th>'.t('Active').'</th>'
                                . '<th>'.t('Purpose').'</th>'
                                . '<th>'.t('Updated').'</th>'
                                . '<th>'.t('Created').'</th>'
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
                    $initial_button_markup = l('ICON_ADD Add Role',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $form['data_entry_area1']['action_buttons']['addrole'] = array('#type' => 'item'
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
