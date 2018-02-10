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

/**
 * Help with baseline availability information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class BaselineAvailabilityPageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        //module_load_include('php','bigfathom_core','core/Context');
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues($baseline_availability=NULL)
    {
        try
        {
            $myvalues = array();
            if(!empty($baseline_availability))
            {
                $onething = $this->m_oMapHelper->getOneBaselineAvailabilityRecord($baseline_availability);
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
                $myvalues['is_planning_default_yn'] = NULL;
                $myvalues['shortname'] = NULL;
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
                $myvalues['active_yn'] = NULL;
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
     */
    function formIsValid($form, &$myvalues, $formMode)
    {
        try
        {
            $bGood = TRUE;
            
            if($formMode == 'D')
            {
                if($myvalues['is_planning_default_yn'] == 1)
                {
                    form_set_error('is_planning_default_yn', "The default baseline declaration cannot be deleted!");
                    $bGood = FALSE;    
                } 
            } else {
                
                if($myvalues['is_planning_default_yn'] == 1 && $myvalues['active_yn'] != 1)
                {
                    form_set_error('is_planning_default_yn', "Must be marked active if will be marked as default!");
                    $bGood = FALSE;    
                }

                //This is something that was edited
                $hours_per_day_tx = $myvalues['hours_per_day'];

                $wdc = $myvalues['work_monday_yn'] + $myvalues['work_tuesday_yn'] 
                        + $myvalues['work_wednesday_yn'] + $myvalues['work_thursday_yn']
                        + $myvalues['work_friday_yn'];
                $wedc = $myvalues['work_saturday_yn'] + $myvalues['work_sunday_yn']; 
                $offdayscount = $wedc + $myvalues['work_holidays_yn'];

                $adc = $wdc + $wedc;
                
                //Check hours
                if(!is_numeric($hours_per_day_tx))
                {
                    form_set_error('hours_per_day', "Hours must be numeric!");
                    $bGood = FALSE;  
                } else {
                    $hours_per_day = floatval($hours_per_day_tx);
                    $totalhours = $adc * $hours_per_day;
                    if($hours_per_day < 0)
                    {
                        form_set_error('hours_per_day', "Hours cannot be less than zero!");
                        $bGood = FALSE;  
                    } else
                    if($hours_per_day > 24)
                    {
                        form_set_error('hours_per_day', "Hours cannot be more than 24 per day!");
                        $bGood = FALSE;  
                    }

                    //And show some warnings that are not actually errors.
                    if($hours_per_day > 16 && $hours_per_day <= 24)
                    {
                        drupal_set_message("Hours per day of $hours_per_day is a very high setting!", 'warning');
                        $bGood = FALSE;  
                    }
                }
                
                //Check the name
                if(trim($myvalues['shortname']) == '')
                {
                    form_set_error('shortname','The name cannot be empty');
                    $bGood = FALSE;
                } else {
                    if($formMode == 'A' || $formMode == 'E')
                    {
                        if($formMode == 'A')
                        {
                            $allowed_count = 0;
                        } else {
                            $allowed_count = 1;
                        }
                        //Check for duplicate keys too
                        $result = db_select(DatabaseNamesHelper::$m_baseline_availability_tablename,'p')
                            ->fields('p')
                            ->condition('shortname', $myvalues['shortname'],'=')
                            ->execute();
                        if($result->rowCount() > 0)
                        {
                            $record = $result->fetchAssoc();
                            $found_id = $record['id'];
                            if($found_id != $myvalues['id'])
                            {
                                form_set_error('shortname', 'Already have a baseline with this name');
                                $bGood = FALSE;
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
            
            if(isset($myvalues['shortname']))
            {
                $shortname = $myvalues['shortname'];
            } else {
                $shortname = '';
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
            
            if(isset($myvalues['is_planning_default_yn']))
            {
                $is_planning_default_yn = $myvalues['is_planning_default_yn'];
            } else {
                $is_planning_default_yn = 0;
            }            
            if(isset($myvalues['active_yn']))
            {
                $active_yn = $myvalues['active_yn'];
            } else {
                $active_yn = 1;
            } 
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            
            $form['data_entry_area1']['shortname'] = array(
                '#type' => 'textfield',
                '#title' => t('Shortname'),
                '#default_value' => $shortname,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique name for this baseline availability definition'),
                '#disabled' => $disabled
            );
            
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

            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            if($disabled)
            {
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
                if(count($wework)>0)
                {
                    $we_final[] = "<span class='colorful-yes'>Working on " . implode(" and ", $wework) . "</span>";
                }
                if(count($we_notwork)>0)
                {
                    $we_final[] = "<span class='colorful-no'>Not working on " . implode(" and ", $we_notwork) . "</span>";
                }
                $we_markup = "<ul><li>" . implode("<li>", $we_final) . "</ul>";
                
                $form['data_entry_area1']['weekend']['summary'] = array(
                    '#markup' => $we_markup,
                );  
                
            } else { 
                
                $form['data_entry_area1']['weekday'] 
                        = array('#type' => 'item',
                                '#prefix' => "<div class='simulate_table_row'>",            
                                '#suffix' => "</div>");   
                
                $form['data_entry_area1']['weekday']['work_monday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Monday'),
                    '#default_value' => !empty($work_monday_yn) ? $work_monday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['weekday']['work_tuesday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Tuesday'),
                    '#default_value' => !empty($work_tuesday_yn) ? $work_tuesday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['weekday']['work_wednesday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Wednesday'),
                    '#default_value' => !empty($work_wednesday_yn) ? $work_wednesday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['weekday']['work_thursday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Thursday'),
                    '#default_value' => !empty($work_thursday_yn) ? $work_thursday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['weekday']['work_friday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Friday'),
                    '#default_value' => !empty($work_friday_yn) ? $work_friday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );

                $form['data_entry_area1']['weekend'] 
                        = array('#type' => 'item',
                                '#prefix' => "<div class='simulate_table_row'>",            
                                '#suffix' => "</div>");   

                $form['data_entry_area1']['weekend']['work_saturday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Saturday'),
                    '#default_value' => !empty($work_saturday_yn) ? $work_saturday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );
                $form['data_entry_area1']['weekend']['work_sunday_yn'] = array(
                    '#type' => 'radios',
                    '#title' => t('Work Sunday'),
                    '#default_value' => !empty($work_sunday_yn) ? $work_sunday_yn : 0,
                    '#options' => $ynoptions,
                    '#disabled' => $disabled,
                  '#prefix' => "<div class='simulate_table_col'>",            
                  '#suffix' => "</div>"
                );

                $form['data_entry_area1']['work_holidays_yn'] = array(
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

            
            $form['data_entry_area1']['is_planning_default_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Is Default'),
                '#default_value' => $is_planning_default_yn,
                '#options' => $ynoptions,
                '#disabled' => $disabled,
                '#required' => TRUE,
                '#description' => 'This is the system default availability for planning operations',
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => $active_yn,
                '#options' => $ynoptions,
                '#disabled' => $disabled,
                '#required' => TRUE,
                '#description' => 'Available for use in planning and assignment to person accounts as a default',
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
