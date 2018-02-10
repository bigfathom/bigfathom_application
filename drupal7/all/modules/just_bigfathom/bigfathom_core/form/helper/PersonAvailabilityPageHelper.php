<?php
/**
 * @file
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 */

namespace bigfathom;

/**
 * Help with Person Availability
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class PersonAvailabilityPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    protected $m_oUAH = NULL;
    protected $m_oUPB = NULL;
    protected $m_is_systemadmin = FALSE;
    protected $m_personid = NULL;
        
    public function __construct($urls_arr, $my_classname=NULL, $personid=NULL)
    {
        try
        {
            if(empty($personid))
            {
                throw new \Exception("Missing required personid!");
            }
            $this->m_personid = $personid;
            
            $this->m_show_group_membership_roles = TRUE;
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_person_tablename = DatabaseNamesHelper::$m_person_tablename;

            //module_load_include('php','bigfathom_core','core/Context');
            $this->m_urls_arr = $urls_arr;
            $this->m_my_classname = $my_classname;
            $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
            if(!$loaded)
            {
                throw new \Exception('Failed to load the MapHelper class');
            }
            $this->m_oMapHelper = new \bigfathom\MapHelper();
            $loaded2 = module_load_include('php','bigfathom_core','core/FormHelper');
            if(!$loaded2)
            {
                throw new \Exception('Failed to load the FormHelper class');
            }
            $this->m_oFormHelper = new \bigfathom\FormHelper();
            
            $loaded_uah = module_load_include('php','bigfathom_core','core/UserAccountHelper');
            if(!$loaded_uah)
            {
                    throw new \Exception('Failed to load the UserAccountHelper class');
            }
            $this->m_oUAH = new \bigfathom\UserAccountHelper();
            $this->m_aUPB = $this->m_oUAH->getUserProfileBundle();
            if($this->m_aUPB['roles']['systemroles']['summary']['is_systemadmin'])
            {
                $this->m_is_systemadmin = TRUE;
            } else {
                $this->m_is_systemadmin = FALSE;
            }
            if($this->m_aUPB['roles']['systemroles']['summary']['is_systemwriter'])
            {
                $this->m_is_systemwriter = TRUE;
            } else {
                $this->m_is_systemwriter = FALSE;
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function canChangeSAFlags()
    {
        return $this->m_is_systemadmin;
    }
    
    public function setShowGroupMembershipRoles($setting=TRUE)
    {
        $this->m_show_group_membership_roles = $setting;
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($person_availabilityid=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($person_availabilityid))
            {
                $onething = $this->m_oMapHelper->getOnePersonAvailabilityRecord($person_availabilityid);
                foreach($onething as $colname=>$content)
                {
                    if($colname == 'maps')
                    {
                        foreach($content as $mapname=>$mapcontent)
                        {
                            $myvalues['map_'.$mapname] = $mapcontent;
                        }
                    } else {
                        $myvalues[$colname] = $content;
                    }
                }
                
            } else {
                //Initialize all the values to NULL
                $myvalues['personid'] = $this->m_personid;
                $myvalues['id'] = NULL;
                $myvalues['start_dt'] = NULL;
                $myvalues['end_dt'] = NULL;
                $myvalues['type_cd'] = NULL;
                $myvalues['hours_per_day'] = NULL;
                $myvalues['work_saturday_yn'] = NULL;
                $myvalues['work_sunday_yn'] = NULL;
                $myvalues['work_monday_yn'] = NULL;
                $myvalues['work_tuesday_yn'] = NULL;
                $myvalues['work_wednesday_yn'] = NULL;
                $myvalues['work_thursday_yn'] = NULL;
                $myvalues['work_friday_yn'] = NULL;
                $myvalues['work_holidays_yn'] = NULL;
                $myvalues['comment_tx'] = NULL;
                $myvalues['updated_by_personid'] = NULL;
                $myvalues['updated_dt'] = NULL;
                $myvalues['created_dt'] = NULL;
            }

            return $myvalues;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Validate the proposed values.
     * @param type $form
     * @param type $myvalues
     * @return true if no validation errors detected
     */
    function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            if($formMode != 'D')
            {
                //This is something that was edited
                $start_dt = $myvalues['start_dt'];
                $end_dt = $myvalues['end_dt'];
                $type_cd = $myvalues['type_cd'];
                $hours_per_day_tx = $myvalues['hours_per_day'];

                $wdc = $myvalues['work_monday_yn'] + $myvalues['work_tuesday_yn'] 
                        + $myvalues['work_wednesday_yn'] + $myvalues['work_thursday_yn']
                        + $myvalues['work_friday_yn'];
                $wedc = $myvalues['work_saturday_yn'] + $myvalues['work_sunday_yn']; 
                $offdayscount = $wedc + $myvalues['work_holidays_yn'];

                $adc = $wdc + $wedc;

                if(empty($type_cd))
                {
                    form_set_error('type_cd','A type must be selected');
                    $bGood = FALSE;
                }

                if($start_dt >= $end_dt)
                {
                    form_set_error('end_dt','The end date cannot be the same or earlier than the start date');
                    $bGood = FALSE;
                }

                if($formMode == 'A' || $formMode == 'E')
                {
                    if($type_cd != 'V')
                    {
                        //Check hours
                        if(!is_numeric($hours_per_day_tx))
                        {
                            form_set_error('hours_per_day', "Hours must be numeric!");
                            $bGood = FALSE;  
                        } else {
                            $hours_per_day = floatval($hours_per_day_tx);
                            $totalhours = $adc * $hours_per_day;
                            if($type_cd == 'V')
                            {
                                if($hours_per_day > 0)
                                {
                                    form_set_error('hours_per_day', "Vacation type requires zero hours");
                                    $bGood = FALSE;  
                                }
                            } else
                            if($hours_per_day < 0)
                            {
                                form_set_error('hours_per_day', "Hours cannot be less than zero!");
                                $bGood = FALSE;  
                            } else
                            if($hours_per_day > 24)
                            {
                                form_set_error('hours_per_day', "Hours cannot be more than 24 per day!");
                                $bGood = FALSE;  
                            } else
                            if($type_cd == 'P')
                            {
                                if($hours_per_day >= 8)
                                {
                                    form_set_error('hours_per_day', "Part-time type requires fewer than 8 hours per day");
                                    $bGood = FALSE;  
                                } else
                                if($totalhours >= 40)
                                {
                                    $totalhours = $adc * $hours_per_day;
                                    form_set_error('hours_per_day', "Part-time type requires fewer than 40 hours per week (this is $totalhours for the week)");
                                    $bGood = FALSE;  
                                }
                            } else
                            if($type_cd == 'C')
                            {
                                if($hours_per_day < 8 && $offdayscount == 0)
                                {
                                    form_set_error('hours_per_day', "Crunch type requires more than 8 hours per day or to work weekends or work holidays!");
                                    $bGood = FALSE;  
                                }
                            }

                            //And show some warnings that are not actually errors.
                            if($type_cd == 'C')
                            {
                                if($hours_per_day < 4)
                                {
                                    drupal_set_message("Hours per day of $hours_per_day is a very low setting for a 'Crunch' period!", 'warning');
                                    $bGood = FALSE;  
                                }
                            } else {
                                if($hours_per_day > 16 && $hours_per_day <= 24)
                                {
                                    drupal_set_message("Hours per day of $hours_per_day is a very high setting!", 'warning');
                                    $bGood = FALSE;  
                                }
                            }
                        }

                        //Check for comment if required
                        if($type_cd == 'O' && empty(trim($myvalues['comment_tx'])))
                        {
                            form_set_error('comment_tx', "An explanation is required for period of type 'Other'");
                            $bGood = FALSE;  
                        }

                        if($bGood)
                        {
                            //Check for overlap
                            $ignore_id = $myvalues['id'];
                            if(empty($ignore_id))
                            {
                                $ignore_part = "";
                            } else {
                                $ignore_part = " and id<>{$ignore_id} ";
                            }
                            $select = "id, start_dt, end_dt, type_cd";
                            $from = DatabaseNamesHelper::$m_map_person2availability_tablename;
                            $where = "personid={$this->m_personid} $ignore_part and ((start_dt <= '$start_dt' and end_dt >= '$start_dt')"
                                    . " or (start_dt >= '$start_dt' and end_dt <= '$end_dt')"
                                    . " or (start_dt <= '$end_dt' and end_dt >= '$end_dt'))";
                            $sql_overlap = "select $select from $from where $where";

                            $result_overlap = db_query($sql_overlap);
                            while($record = $result_overlap->fetchAssoc())
                            {
                                $overlap_id = $record['id'];
                                $overlap_start_dt = $record['start_dt'];
                                $overlap_end_dt = $record['end_dt'];
                                $overlap_type_cd = $record['type_cd'];

                                if($type_cd == 'B' && $overlap_type_cd == 'B')
                                {
                                    form_set_error('type_cd', "There is an overlapping custom baseline from $overlap_start_dt until $overlap_end_dt");
                                    $bGood = FALSE;
                                } else
                                if($type_cd != 'B' && $overlap_type_cd != 'B')
                                {
                                    form_set_error('type_cd', "There is anoverlapping custom availability (type '$overlap_type_cd') from $overlap_start_dt until $overlap_end_dt");
                                    $bGood = FALSE;
                                }
                            }                
                        }
                    }
                }
            }

            //Done with all validations.
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getUAH()
    {
        return $this->m_oUAH;
    }
    
    public function getTypeCodeOptions($show_empty_option = TRUE)
    {
        try
        {
            //Get all the relevant select options
            $options = [];
            $allinfo = \bigfathom\UtilityGeneralFormulas::getAllAvailabilityTypeCodeInfo();
            if($show_empty_option!==FALSE)
            {
                $options[] = '';
            }
            foreach($allinfo as $type_cd=>$oneinfo)
            {
                
                $show_tx = $oneinfo['name'] . ' (' . $oneinfo['tooltip'] . ')';
                $options[$type_cd] = $show_tx;
            }            
            return $options;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @param letter $formType valid values are A, E, D, and V
     * @return drupal renderable array
     * @throws \Exception
     */
    function getForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            global $user;
            if($html_classname_overrides == NULL)
            {
                $html_classname_overrides = array();
            }
            if(!isset($html_classname_overrides['data-entry-area1']))
            {
                $html_classname_overrides['data-entry-area1'] = 'data-entry-area1';
            }
            if(!isset($html_classname_overrides['selectable-text']))
            {
                $html_classname_overrides['selectable-text'] = 'selectable-text';
            }

            $form['data_entry_area1'] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
                '#disabled' => $disabled,
            );     
                
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
            }
            if(isset($myvalues['start_dt']))
            {
                $start_dt = $myvalues['start_dt'];
            } else {
                $start_dt = '';
            }
            if(isset($myvalues['end_dt']))
            {
                $end_dt = $myvalues['end_dt'];
            } else {
                $end_dt = '';
            }
            
            if(isset($myvalues['type_cd']))
            {
                $type_cd = $myvalues['type_cd'];
            } else {
                $type_cd = '';
            }
            if(isset($myvalues['hours_per_day']))
            {
                $hours_per_day = $myvalues['hours_per_day'];
            } else {
                $hours_per_day = '';
            }
            
            if(isset($myvalues['work_saturday_yn']))
            {
                $work_saturday_yn = $myvalues['work_saturday_yn'];
            } else {
                $work_saturday_yn = 0;
            }
            if(isset($myvalues['work_sunday_yn']))
            {
                $work_sunday_yn = $myvalues['work_sunday_yn'];
            } else {
                $work_sunday_yn = 0;
            }
            if(isset($myvalues['work_monday_yn']))
            {
                $work_monday_yn = $myvalues['work_monday_yn'];
            } else {
                $work_monday_yn = 1;
            }
            if(isset($myvalues['work_tuesday_yn']))
            {
                $work_tuesday_yn = $myvalues['work_tuesday_yn'];
            } else {
                $work_tuesday_yn = 1;
            }
            if(isset($myvalues['work_wednesday_yn']))
            {
                $work_wednesday_yn = $myvalues['work_wednesday_yn'];
            } else {
                $work_wednesday_yn = 1;
            }
            if(isset($myvalues['work_thursday_yn']))
            {
                $work_thursday_yn = $myvalues['work_thursday_yn'];
            } else {
                $work_thursday_yn = 1;
            }
            if(isset($myvalues['work_friday_yn']))
            {
                $work_friday_yn = $myvalues['work_friday_yn'];
            } else {
                $work_friday_yn = 1;
            }
            if(isset($myvalues['work_holidays_yn']))
            {
                $work_holidays_yn = $myvalues['work_holidays_yn'];
            } else {
                $work_holidays_yn = 0;
            }
            if(isset($myvalues['comment_tx']))
            {
                $comment_tx = $myvalues['comment_tx'];
            } else {
                $comment_tx = NULL;
            }
           

            $options_type_cd = $this->getTypeCodeOptions();
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_start_dt'] 
                = array('#type' => 'hidden', '#value' => $start_dt, '#disabled' => FALSE);        
            $form['hiddenthings']['original_end_dt'] 
                = array('#type' => 'hidden', '#value' => $end_dt, '#disabled' => FALSE);        

            
            $form['data_entry_area1']['type_cd'] = array(
                '#type' => 'select',
                '#title' => t('Custom Availability Type'),
                '#default_value' => $type_cd,
                '#options' => $options_type_cd,
                '#required' => TRUE,
                '#description' => t('The type of custom availability this record represents'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['daterange'] 
                    = array('#type' => 'item',
                            '#prefix' => "<div class='simulate_table_row'>",            
                            '#suffix' => "</div>");            
            
            $form['data_entry_area1']['daterange']['start_dt'] = array(
              '#type' => 'date_popup', 
              '#date_format'   => 'Y-m-d',
              '#title' => 'Start', 
              '#default_value' => $start_dt, 
              '#description' => 'When this period begins', 
              '#required' => TRUE,
              '#disabled' => $disabled,
              '#prefix' => "<div class='simulate_table_col'>",            
              '#suffix' => "</div>"
            );            

            $form['data_entry_area1']['daterange']['end_dt'] = array(
              '#type' => 'date_popup', 
              '#date_format'   => 'Y-m-d',
              '#title' => 'End', 
              '#default_value' => $end_dt, 
              '#description' => 'When this period ends', 
              '#required' => TRUE,
              '#disabled' => $disabled,
              '#prefix' => "<div class='simulate_table_col'>",            
              '#suffix' => "</div>"
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            if($disabled)
            {
                if($hours_per_day == 0)
                {
                    $we_final[] = "<span class='colorful-no'>Not working during this period</span>";
                    $we_markup = "<ul><li>" . implode("<li>", $we_final) . "</ul>";
                } else {
                    
                    $form['data_entry_area1']['hours_per_day'] = array(
                        '#type' => 'textfield',
                        '#title' => t('Average Hours Per Working Day'),
                        '#default_value' => $hours_per_day,
                        '#size' => 5,
                        '#maxlength' => 5,
                        '#required' => TRUE,
                        '#description' => t('Average hours per day for those days worked (e.g. 8 hours/day at 5 days is 40 hours for the week)'),
                        '#disabled' => $disabled
                    );
                    
                    if($work_monday_yn)
                    {
                        $wework[] = "Monday";
                    } else {
                        $we_notwork[] = "Monday";
                    }
                    if($work_tuesday_yn)
                    {
                        $wework[] = "Tuesday";
                    } else {
                        $we_notwork[] = "Tuesday";
                    }
                    if($work_wednesday_yn)
                    {
                        $wework[] = "Wednesday";
                    } else {
                        $we_notwork[] = "Wednesday";
                    }
                    if($work_thursday_yn)
                    {
                        $wework[] = "Thursday";
                    } else {
                        $we_notwork[] = "Thursday";
                    }
                    if($work_friday_yn)
                    {
                        $wework[] = "Friday";
                    } else {
                        $we_notwork[] = "Friday";
                    }

                    if($work_saturday_yn)
                    {
                        $wework[] = "Saturday";
                    } else {
                        $we_notwork[] = "Saturday";
                    }
                    if($work_sunday_yn)
                    {
                        $wework[] = "Sunday";
                    } else {
                        $we_notwork[] = "Sunday";
                    }

                    if($work_holidays_yn)
                    {
                        $wework[] = "Holidays";
                    } else {
                        $we_notwork[] = "Holidays";
                    }

                    $we_final = [];
                    if(!empty($we_notwork) && count($wework)>0)
                    {
                        $we_final[] = "<span class='colorful-yes'>Working on " . implode(" and ", $wework) . "</span>";
                    }
                    if(!empty($we_notwork) && count($we_notwork)>0)
                    {
                        $we_final[] = "<span class='colorful-no'>Not working on " . implode(" and ", $we_notwork) . "</span>";
                    }
                    $we_markup = "<ul><li>" . implode("<li>", $we_final) . "</ul>";
                }
                
                $form['data_entry_area1']['weekend']['summary'] = array(
                    '#markup' => $we_markup,
                );  
                
            } else { 

                $form['data_entry_area1']['dateandtime'] = array(
                    '#type' => 'fieldset', 
                    '#title' => 'Time and Day Details',
                    '#collapsible' => FALSE,
                    '#collapsed' => FALSE, 
                    '#tree' => FALSE,
                    '#disabled' => $disabled,
                    '#states' => array(
                        'invisible' => array(
                            ':input[name="type_cd"]' => array('value'=>'V')
                        ),
                    )
                );
                
                $form['data_entry_area1']['dateandtime']['hours_per_day'] = array(
                    '#type' => 'textfield',
                    '#title' => t('Average Hours Per Working Day'),
                    '#default_value' => $hours_per_day,
                    '#size' => 5,
                    '#maxlength' => 5,
                    '#required' => FALSE,
                    '#description' => t('Average hours per day for those days worked (e.g. 8 hours/day at 5 days is 40 hours for the week)'),
                    '#disabled' => $disabled
                );

                $form['data_entry_area1']['dateandtime']['weekday'] 
                        = array('#type' => 'item',
                                '#prefix' => "<div class='simulate_table_row'>",            
                                '#suffix' => "</div>");   
                
                $form['data_entry_area1']['dateandtime']['weekday']['work_monday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Monday'),
                    '#default_value' => !empty($work_monday_yn) ? $work_monday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['dateandtime']['weekday']['work_tuesday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Tuesday'),
                    '#default_value' => !empty($work_tuesday_yn) ? $work_tuesday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['dateandtime']['weekday']['work_wednesday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Wednesday'),
                    '#default_value' => !empty($work_wednesday_yn) ? $work_wednesday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['dateandtime']['weekday']['work_thursday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Thursday'),
                    '#default_value' => !empty($work_thursday_yn) ? $work_thursday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['dateandtime']['weekday']['work_friday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Friday'),
                    '#default_value' => !empty($work_friday_yn) ? $work_friday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );

                $form['data_entry_area1']['dateandtime']['weekend'] 
                        = array('#type' => 'item',
                                '#prefix' => "<div class='simulate_table_row'>",            
                                '#suffix' => "</div>");   

                $form['data_entry_area1']['dateandtime']['weekend']['work_saturday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Saturday'),
                    '#default_value' => !empty($work_saturday_yn) ? $work_saturday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['dateandtime']['weekend']['work_sunday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Sunday'),
                    '#default_value' => !empty($work_sunday_yn) ? $work_sunday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );

                $form['data_entry_area1']['dateandtime']['work_holidays_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Holidays'),
                    '#default_value' => !empty($work_holidays_yn) ? $work_holidays_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                    '#description' => 'Available for work on declared holidays',
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
            }

            $form['data_entry_area1']['comment_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Notes'),
                '#default_value' => $comment_tx,
                '#size' => 512,
                '#maxlength' => 1024,
                '#required' => FALSE,
                '#description' => t('Brief notes about this customized availability period'),
                '#disabled' => $disabled
            );            

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
