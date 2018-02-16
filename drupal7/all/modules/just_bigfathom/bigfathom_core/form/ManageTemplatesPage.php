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
require_once 'helper/TemplatePageHelper.php';

/**
 * This class returns the list of available project templates
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManageTemplatesPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper  = NULL;
    protected $m_urls_arr    = NULL;
    protected $m_aDataRights = NULL;
    protected $m_oPageHelper = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_override_arr=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        $urls_arr = array();
        $urls_arr['importtemplate'] = 'bigfathom/importtemplate';
        $urls_arr['download'] = 'bigfathom/downloadtemplate';
        $urls_arr['view'] = 'bigfathom/viewtemplate';
        $urls_arr['createprojectfromtemplate'] = 'bigfathom/createprojectfromtemplate';
        $urls_arr['edit'] = 'bigfathom/edittemplate';
        $urls_arr['delete'] = 'bigfathom/deletetemplate';
        $urls_arr['communicate'] = 'bigfathom/template/mng_comments';
        $urls_arr['hierarchy'] = 'bigfathom/sitemanage/maptemplatecontent';//&templateid=5
        
        $pmi = $this->m_oContext->getParentMenuItem();
        $urls_arr['return'] = $pmi['link_path'];
        if(is_array($urls_override_arr))
        {
            foreach($urls_override_arr as $k=>$url)
            {
                $urls_arr[$k] = $url;
            }
        }
        
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
        $this->m_aDataRights  = $aDataRights;
        
        $this->m_urls_arr     = $urls_arr;
        
        $this->m_oPageHelper = new \bigfathom\TemplatePageHelper($urls_arr);
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

            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="'.$html_classname_overrides['table-container'].'">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $rows = "\n";
            $all_people = $this->m_oMapHelper->getPersonsByID();
            $all_pcbyid = $this->m_oMapHelper->getProjectContextsByID(NULL, FALSE, FALSE);

            $communicate_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate');
            $hierarchy_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('hierarchy');
            $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
            
            global $user;
            $uah = new UserAccountHelper();
            $usrm = $uah->getPersonSystemRoleBundle($user->uid);
            $uprm = $uah->getPersonProjectRoleBundle($user->uid);
            $is_systemadmin = $usrm['summary']['is_systemadmin'];
            
            $active_yn = 1;
            $templateinfo_bundle = $this->m_oMapHelper->getAllTPOverviewBundles(NULL, $active_yn); 

            //$debug_content = $this->m_oMapHelper->getHierarchyMapBundle4TP(2);
            //DebugHelper::showNeatMarkup($debug_content,'LOOK DEBUG CONTENT FOR TEMPLATE IO THING');

            $all_relevant_tps = $templateinfo_bundle['by_tpid'];

            $cmi = $this->m_oContext->getCurrentMenuItem();
            foreach($all_relevant_tps as $templateid=>$record)
            {
                $templateid_markup = $templateid;
                $root_template_workitemid = $record['root_template_workitemid'];
                $open_tws = $record['maps']['open_tws'];
                if(!isset($open_tws['T']))
                {
                    $open_taskcount = 0;
                } else {
                    $open_taskcount = count($open_tws['T']);
                }
                if(!isset($open_tws['G']))
                {
                    $open_goalcount = 0;
                } else {
                    $open_goalcount = count($open_tws['G']);
                }
                $deliverable_count = count($record['maps']['deliverabletwids']);
                $root_tw_nm = $record['workitem_nm'];
                $template_nm = $record['template_nm'];
                $template_nm_markup = "[SORTSTR:$template_nm]<span title='root workitem is $root_tw_nm'>$template_nm</span>";

                $submitter_blurb_tx = $record['submitter_blurb_tx'];
                $mission_tx = $record['mission_tx'];
                
                $status_cd = $record['status_cd'];
                $status_title_tx = $record['status_title_tx'];
                $status_markup = "<span title='$status_title_tx'>$status_cd</span>";
                
                $owner_personid = $record['owner_personid'];
                $isowner = $user->uid == $owner_personid;
                if(empty($all_people[$owner_personid]))
                {
                    if($owner_personid > 0)
                    {
                        $owner_personname = "Unknown";
                        $owner_txt = "#{$owner_personid}";
                    } else {
                        $owner_personname = "External";
                        $owner_txt = "";
                    }
                } else {
                    $owner_persondetail = $all_people[$owner_personid];
                    $owner_personname = $owner_persondetail['first_nm'] . " " . $owner_persondetail['last_nm'];
                    $owner_txt = "#{$owner_personid}";
                }
                $owner_markup = "<span title='$owner_txt'>".$owner_personname."</span>";
                
                $template_contextid = $record['project_contextid'];
                $pc_rec = $all_pcbyid[$template_contextid];
                $pc_shortname = $pc_rec['shortname'];
                $pc_description_tx = $pc_rec['description_tx'];
                $template_context_markup = "<span title='$pc_description_tx'>$pc_shortname</span>";

                $publishedrefname  = $record["publishedrefname"];
                $canpublish_yn  = $record["allow_detail_publish_yn"];
                $snippet_bundle_head_yn = $record['snippet_bundle_head_yn'];
                
                if($snippet_bundle_head_yn == 1)
                {
                    $template_type_markup = "parts";
                } else {
                    $template_type_markup = "Project";
                }
                
                $publishedrefname_markup = $publishedrefname;
                
                if($canpublish_yn != 1)
                {
                    $externallydownloadable_yesno_markup = "<span title='The template#$templateid is not presented for external downloading'>No</span>";
                } else {
                    $externallydownloadable_yesno_markup = "<span class='colorful-available' title='The template#$templateid is available for external downloads'>Yes</span>";
                }
                
                $taskcount_markup = "[SORTNUM:$open_taskcount]$open_taskcount";
                $goalcount_markup = "[SORTNUM:$open_goalcount]$open_goalcount";
                
                if(strlen($mission_tx) > 80)
                {
                    $mission_tx = substr($mission_tx, 0,80) . '...';
                }
                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view'])) 
                {
                    $sCommentsMarkup = '';
                    $sViewMarkup = '';
                    $sDownloadMarkup = '';
                } else {
                    $communicate_page_url = url($this->m_urls_arr['communicate'], array('query'=>array('workitemid'=>$root_template_workitemid)));
                    $sCommentsMarkup = "<a title='jump to communications for workitem#{$root_template_workitemid}' href='$communicate_page_url'><img src='$communicate_icon_url'/></a>";

                    $sCommentsMarkup = "";  //TODO enable this feature later
                            
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('templateid'=>$templateid, 'return' => $cmi['link_path'])));
                    $sViewMarkup = "<a title='view details of template#{$templateid}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                    
                    $hierarchy_page_url = url($this->m_urls_arr['hierarchy'], array('query'=>array('templateid'=>$templateid)));
                    $sHierarchyMarkup = "<a title='view dependencies for template#{$templateid}' href='$hierarchy_page_url'><img src='$hierarchy_icon_url'/></a>";
                    
                    if(!empty($publishedrefname))
                    {
                        $download_page_url = url($this->m_urls_arr['download'], array('query'=>array('publishedrefname'=>$publishedrefname, 'return' => $cmi['link_path'])));
                        $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('download_template');
                        //$sDownloadMarkup = "<a title='download template file for template#{$templateid}' href='$download_page_url'><img src='$download_icon_url'/></a>";
                        $sDownloadMarkup = "<a title='download template file for template#{$templateid}' href='$download_page_url'><img src='$download_icon_url'/></a>";
                    } else {
                        $download_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('nothing2download');
                        $sDownloadMarkup = "<span title='There is no reference name for template#{$templateid}'><img src='$download_icon_url'/></span>";
                    }
                }
                if(strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    if(!$is_systemadmin && !$isowner)
                    {
                        $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('templateid'=>$templateid, 'return' => $cmi['link_path'])));
                        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('no_edit');
                        $sEditMarkup = "<span title='only owners can edit #{$templateid}' href='$edit_page_url'><img src='$edit_icon_url'/></span>";
                    } else {
                        $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('templateid'=>$templateid, 'return' => $cmi['link_path'])));
                        $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                        $sEditMarkup = "<a title='edit #{$templateid}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                    }
                }

                if(strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['createprojectfromtemplate']))
                {
                    $sCreateProjectMarkup = '';
                } else {
                    $createproject_page_url = url($this->m_urls_arr['createprojectfromtemplate'], array('query'=>array('source_templateid'=>$templateid, 'return' => $cmi['link_path'])));
                    $createproject_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('createprojectfromtemplate');
                    $sCreateProjectMarkup = "<a title='create new project from template#{$templateid}' href='$createproject_page_url'><img src='$createproject_icon_url'/></a>";
                }
                
                if(strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    //$sDeleteMarkup = l('Delete',$this->m_urls_arr['delete'],array('query'=>array('templateid'=>$templateid)));
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('templateid'=>$templateid, 'return' => $cmi['link_path'])));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='jump to delete for #{$templateid}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
                $rows   .= "\n".'<tr>'
                        . '<td>'
                        . $templateid_markup.'</td><td>'
                        . $template_nm_markup.'</td><td>'
                        . $owner_markup.'</td><td>'
                        . $template_context_markup.'</td><td>'
                        . $template_type_markup.'</td><td>'
                        . $mission_tx.'</td><td>'
                        . $submitter_blurb_tx.'</td><td>'
                        . $goalcount_markup.'</td><td>'
                        . $taskcount_markup.'</td><td>'
                        . $publishedrefname_markup . '</td><td>'
                        . $externallydownloadable_yesno_markup . '</td><td>'
                        . $record['updated_dt'].'</td>'
                        . '<td class="action-options">'
                                    . $sCommentsMarkup . ' '
                                    . $sHierarchyMarkup . ' '
                                    . $sDownloadMarkup . ' '
                                    . $sViewMarkup . ' '
                                    . $sCreateProjectMarkup . ' '
                                    . $sEditMarkup . ' '
                                    . $sDeleteMarkup 
                        . '</td>'
                        . '</tr>';
                
            }

            $form["data_entry_area1"]['table_container']['ci'] = array('#type' => 'item',
                     '#markup' => '<table id="table-selecttemplate" class="browserGrid">'
                                . '<thead class="nowrap">'
                                . '<tr>'
                                . '<th datatype="numid" class="nowrap">'
                                    . '<span title="Unique ID number of the template">'.t('ID').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Name of the project template">'.t('Template Name').'</span></th>'
                                . '<th>'.t('Template Owner').'</th>'
                                . '<th>'
                                    . '<span title="Topic area where the template is relevant">'.t('Context').'</span></th>'
                                . '<th>'
                                    . '<span title="A template can be for an entire project or it can simply be a collection of workitems for reuse in other projects (Project vs Snippet type)">'.t('Type').'</span></th>'
                                . '<th>'
                                    . '<span title="The mission of this template">'.t('Mission').'</span></th>'
                                . '<th>'
                                    . '<span title="The submitter blurb for this template; may contain usage advice beyond mission">'.t('Insight Blurb').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Count of goals in the template">'.t('GC').'</span></th>'
                                . '<th datatype="formula">'
                                    . '<span title="Count of tasks in the template">'.t('TC').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="A unique reference name by which users can find this template">'.t('RefName').'</span></th>'
                                . '<th datatype="text">'
                                    . '<span title="Externally shared for download">'.t('ES').'</span></th>'
                                . '<th>'.t('Updated').'</th>'
                                . '<th datatype="html" class="action-options">'.t('Action Options').'</th>'
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

            if(isset($this->m_urls_arr['importtemplate']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $add_link_markup = l('Import Template',$this->m_urls_arr['importtemplate']
                                , array('attributes'=>array('class'=>'action-button'))
                            );
                    $form['data_entry_area1']['action_buttons']['importtemplate'] = array('#type' => 'item'
                            , '#markup' => $add_link_markup);
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
