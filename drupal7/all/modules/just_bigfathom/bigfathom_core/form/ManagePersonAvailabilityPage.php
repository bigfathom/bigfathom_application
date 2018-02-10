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
 * This class returns the manages custom availability declarations for a person
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ManagePersonAvailabilityPage extends \bigfathom\ASimpleFormPage
{

    protected $m_oMapHelper     = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_aDataRights    = NULL;
    protected $m_oPageHelper    = NULL;
    protected $m_oTextHelper    = NULL;

    private $m_oUAH = NULL;
    private $m_drupaluser_uid2name_map = NULL;
    private $m_is_systemadmin = NULL;
    private $m_personid = NULL;
    private $m_person_fullname = NULL;
    
    public function __construct($personid, $urls_arr=NULL)
    {
        if (!isset($personid) || !is_numeric($personid)) {
            throw new \Exception("Missing or invalid $personid value = " . $personid);
        }
        $this->m_personid = $personid;
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
        
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'add', 'bigfathom/addperson_availability');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'edit', 'bigfathom/editperson_availability');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'view', 'bigfathom/viewperson_availability');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'delete', 'bigfathom/deleteperson_availability');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'return', 'bigfathom/sitemanage/people');
        UtilityGeneralFormulas::setArrayValueIfEmpty($urls_arr, 'manage_availability', 'bigfathom/mng_person_availability');

        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee_yn = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        if($this->m_is_systemadmin || $this->m_is_systemdatatrustee_yn || $user->uid == $this->m_personid)
        {
            $aPersonRights='VAED';
        } else {
            $aPersonRights='V';
        }
        $this->m_person_fullname = $uah->getExistingPersonFullName($this->m_personid);
        
        $this->m_urls_arr         = $urls_arr;
        $this->m_aDataRights      = $aPersonRights;
        
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
            $main_tablename = 'person-availability-table';
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
                . "This interface shows all custom availability declarations for <span title='personid#{$this->m_personid}'>{$this->m_person_fullname}</span>"
                . "</p>",
                '#suffix' => '</div>',
            );

            $cmi = $this->m_oContext->getCurrentMenuItem();
            $rparams_ar = [];
            $rparams_ar['personid'] = $this->m_personid;
            $rparams_encoded = urlencode(serialize($rparams_ar));
            
            $bundle = $this->m_oMapHelper->getPersonAvailabilityBundle($this->m_personid);
            $custom_row_count = $bundle['summary']['custom_row_count'];
            $all_records = $bundle['detail'];
            
            $now_dt = date("Y-m-d", time());
            
            $rows = [];
            foreach($all_records as $record)
            {
                $is_default = $record['is_default'];
                if(empty($record['type_info']))
                {
                    $type_cd = $record['type_cd'];
                    $type_info = UtilityGeneralFormulas::getInfoForAvailabilityTypeCode($type_cd);
                } else {
                    $type_info = $record['type_info'];
                }
                $start_as_YMD = $record['start_dt'];
                $end_as_YMD = $record['end_dt'];
                
                if($record['is_default'])
                {
                    $person_availabilityid = NULL;
                    $baseline_availabilityid = $record['baseline_availabilityid'];
                } else {
                    $person_availabilityid = $record['id'];
                    $baseline_availabilityid = NULL;
                }
                $hours_per_day = $record['hours_per_day'];
                $work_monday_yn = $record['work_monday_yn'];
                $work_tuesday_yn = $record['work_tuesday_yn'];
                $work_wednesday_yn = $record['work_wednesday_yn'];
                $work_thursday_yn = $record['work_thursday_yn'];
                $work_friday_yn = $record['work_friday_yn'];
                $work_saturday_yn = $record['work_saturday_yn'];
                $work_sunday_yn = $record['work_sunday_yn'];
                $work_holidays_yn = $record['work_holidays_yn'];
                $comment_tx = $record['comment_tx'];
                $updated_dt = $record['updated_dt'];
                $created_dt = $record['created_dt'];

                if($is_default)
                {
                    $date_classname = "";
                } else {
                    if($now_dt >= $start_as_YMD && $now_dt <= $end_as_YMD)
                    {
                        $date_classname = "text-bolder";
                    } else {

                        if($now_dt > $end_as_YMD)
                        {
                            $date_classname = "text-gray-lighter";
                        } else {
                            $date_classname = "";
                        }
                    }
                }

                $start_dt_ts = strtotime($start_as_YMD);
                $end_dt_ts = strtotime($end_as_YMD);
                
                $start_dt_markup = "[SORTNUM:{$start_dt_ts}]<span class='$date_classname'>$start_as_YMD</span>";
                $end_dt_markup = "[SORTNUM:{$end_dt_ts}]<span class='$date_classname'>$end_as_YMD</span>";
                $hours_per_day_markup = "<span class='$date_classname'>$hours_per_day</span>";
                $work_monday_yn_markup = $work_monday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_tuesday_yn_markup = $work_tuesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_wednesday_yn_markup = $work_wednesday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_thursday_yn_markup = $work_thursday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_friday_yn_markup = $work_friday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_saturday_yn_markup = $work_saturday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_sunday_yn_markup = $work_sunday_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                $work_holidays_yn_markup = $work_holidays_yn ? "<span class='colorful-yes'>Yes</span>" : "<span class='colorful-no'>No</span>";
                
                if($updated_dt !== $created_dt)
                {
                    $updated_markup = "<span title='Originally created $created_dt'>$updated_dt</span>";
                } else {
                    $updated_markup = "<span title='Never edited'>$updated_dt</span>";
                }
                
                $comment_len = strlen($comment_tx);
                if($comment_len > 256)
                {
                    $comment_markup = substr($comment_tx, 0,256) . '...';
                } else {
                    $comment_markup = $comment_tx;
                }
                
                $has_availabilityid = !empty($person_availabilityid);
                $hover_suffix = " period [$start_as_YMD,$end_as_YMD]";
                if(!$has_availabilityid || strpos($this->m_aDataRights,'V') === FALSE || !isset($this->m_urls_arr['view']))
                {
                    $sViewMarkup = '';
                } else {
                    $view_page_url = url($this->m_urls_arr['view'], array('query'=>array('person_availabilityid'=>$person_availabilityid
                            ,'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                    $view_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('view');
                    $sViewMarkup = "<a title='view detail of {$hover_suffix}' href='$view_page_url'><img src='$view_icon_url'/></a>";
                }
                if(!$has_availabilityid || strpos($this->m_aDataRights,'E') === FALSE || !isset($this->m_urls_arr['edit']))
                {
                    $sEditMarkup = '';
                } else {
                    $edit_page_url = url($this->m_urls_arr['edit'], array('query'=>array('person_availabilityid'=>$person_availabilityid
                            ,'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                    $edit_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('edit');
                    $sEditMarkup = "<a title='edit detail of {$hover_suffix}' href='$edit_page_url'><img src='$edit_icon_url'/></a>";
                }
                if(!$has_availabilityid || strpos($this->m_aDataRights,'D') === FALSE || !isset($this->m_urls_arr['delete']))
                {
                    $sDeleteMarkup = '';
                } else {
                    $delete_page_url = url($this->m_urls_arr['delete'], array('query'=>array('person_availabilityid'=>$person_availabilityid
                            ,'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)));
                    $delete_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('delete');
                    $sDeleteMarkup = "<a title='delete entry for {$hover_suffix}' href='$delete_page_url'><img src='$delete_icon_url'/></a>";
                }
                
                $action_markup = $sViewMarkup.' '
                        . $sEditMarkup.' '
                        . $sDeleteMarkup;

                $type_markup = "[SORTNUM:".$type_info['sort']."]<span class='$date_classname' title='".$type_info['tooltip']."'>".$type_info['name']."</span>";
                
                if($custom_row_count > 0)
                {
                    $datecols_markup = "<td>$start_dt_markup</td>"
                        . "<td>$end_dt_markup</td>";
                } else {
                    $datecols_markup = "";
                }
                $rows[] = "<tr>"
                        . $datecols_markup
                        . "<td>$type_markup</td>"
                        . "<td>$hours_per_day_markup</td>"
                        . "<td>$work_monday_yn_markup</td>"
                        . "<td>$work_tuesday_yn_markup</td>"
                        . "<td>$work_wednesday_yn_markup</td>"
                        . "<td>$work_thursday_yn_markup</td>"
                        . "<td>$work_friday_yn_markup</td>"
                        . "<td>$work_saturday_yn_markup</td>"
                        . "<td>$work_sunday_yn_markup</td>"
                        . "<td>$work_holidays_yn_markup</td>"
                        . "<td>$comment_markup</td>"
                        . "<td>$updated_markup</td>"
                        . "<td class='action-options'>$action_markup</td>"
                        . "</tr>";
            }
            
            $tableheader = [];
            if($custom_row_count > 0)
            {
                $tableheader[] = array("Start Date","The start date of the bounded period","formula");
                $tableheader[] = array("End Date","The end date of the bounded period","formula");
            }
            $tableheader[] = array("Availability Type","Type of bounded period","formula");
            $tableheader[] = array("Hours/Day","Average hours per workday the person would need to work","formula");
            $tableheader[] = array("Mon","Work on Monday","formula");
            $tableheader[] = array("Tue","Work on Tuesday","formula");
            $tableheader[] = array("Wed","Work on Wednesday","formula");
            $tableheader[] = array("Thu","Work on Thursday","formula");
            $tableheader[] = array("Fri","Work on Friday","formula");
            $tableheader[] = array("Sat","Work on Saturday","formula");
            $tableheader[] = array("Sun","Work on Sunday","formula");
            $tableheader[] = array("Hol","Work on declared Holidays","formula");
            $tableheader[] = array("Notes","Comment, if any, for the bounded period.","formula");
            $tableheader[] = array("Updated","Date of most recent update","formula");
            $tableheader[] = array("Action Options","","formula");

            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th class='nowrap' title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";            
            
            $rows_markup = implode("",$rows);
            
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid">'
                                . '<thead>'
                                . $th_markup
                                . '</thead>'
                                . '<tbody>'
                                . $rows_markup
                                .  '</tbody>'
                                . '</table>'
                                . '';
            
    

            $action_buttons_markup = "";
            if(isset($this->m_urls_arr['add']))
            {
                if(strpos($this->m_aDataRights,'A') !== FALSE)
                {
                    $initial_button_markup = l('ICON_ADD Define New Availability Period',$this->m_urls_arr['add']
                                , array('attributes'=>array('class'=>'small-action-button', 'title'=>'Create a new custom availability entry for ' . $this->m_person_fullname)
                                        , 'query'=>array('personid'=>$this->m_personid
                                            ,'return' => $cmi['link_path'], 'rparams' => $rparams_encoded)
                                    )
                            );
                    $final_button_markup = str_replace('ICON_ADD', '<i class="fa fa-plus-square-o" aria-hidden="true"></i>', $initial_button_markup);
                    $action_buttons_markup .= " " . $final_button_markup;
                }
            }    
            if(isset($this->m_urls_arr['return']))
            {
                $returnURL = $this->m_urls_arr['return'];
                $sReturnMarkup = l('Exit',$returnURL
                        ,array('attributes'=>array('class'=>$html_classname_overrides['action-button'])));
                $action_buttons_markup .= " " . $sReturnMarkup;
            }
            
            $all_markup = "<div class='dash-normal-fonts'>$table_markup $action_buttons_markup</div>";
            $form['data_entry_area1']['pagecontent'] = array('#type' => 'item'
                    , '#markup' => $all_markup);            
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
