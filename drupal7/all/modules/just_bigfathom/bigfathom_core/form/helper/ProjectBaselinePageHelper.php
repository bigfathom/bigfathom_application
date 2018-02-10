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
 * Help with project baseline information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ProjectBaselinePageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    protected $m_analysis_info = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $projectid=NULL)
    {
        $this->m_oContext = \bigfathom\Context::getInstance();
        
        if(!empty($projectid))
        {
            $this->m_projectid = $projectid;
        } else {
            $this->m_projectid = $this->m_oContext->getSelectedProjectID();
        }
        
        //module_load_include('php','bigfathom_core','core/Context');
        $this->m_urls_arr = $urls_arr;
        $this->m_my_classname = $my_classname;
        $loaded = module_load_include('php','bigfathom_core','core/MapHelper');
        if(!$loaded)
        {
            throw new \Exception('Failed to load the MapHelper class');
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();
        
        $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
        if(!$loaded2)
        {
            throw new \Exception('Failed to load the WriteHelper class');
        }
        $this->m_oWriteHelper = new \bigfathom\WriteHelper();
        
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues($projbaselineid=NULL)
    {
        try
        {
            if($projbaselineid != NULL)
            {
                
                $bundle = $this->m_oMapHelper->getOneProjectBaselineBundle($projbaselineid);

                $myvalues = [];

                $myvalues['id'] = $projbaselineid;
                $myvalues['projectid'] = $bundle['baseline']['projectid'];
                $myvalues['shortname'] = $bundle['baseline']['shortname'];
                $myvalues['comment_tx'] = $bundle['baseline']['comment_tx'];
                $myvalues['updated_by_personid'] = $bundle['baseline']['updated_by_personid'];
                $myvalues['updated_dt'] = $bundle['baseline']['updated_dt'];
                $myvalues['created_by_personid'] = $bundle['baseline']['created_by_personid'];
                $myvalues['created_dt'] = $bundle['baseline']['created_dt'];
                
                $myvalues['lookup'] = $bundle['lookup'];
                $myvalues['maps'] = $bundle['baseline']['maps'];

                $this->m_analysis_info = $myvalues;
                
            } else {
                
                //Initialize all the values to NULL except analysis info
                $myvalues = [];
                $myvalues['id'] = NULL;
                $myvalues['projectid'] = NULL;
                $myvalues['shortname'] = "Snapshot" . date("Y-m-d");
                $myvalues['comment_tx'] = NULL;
                $myvalues['updated_by_personid'] = NULL;
                $myvalues['updated_dt'] = NULL;
                $myvalues['created_by_personid'] = NULL;
                $myvalues['created_dt'] = NULL;
                
                $bundle = $this->m_oMapHelper->getProjectBaselineStyleBundle($this->m_projectid);

                $myvalues['lookup'] = $bundle['lookup'];
                $myvalues['maps'] = $bundle['baseline']['maps'];

                $this->m_analysis_info = $myvalues;
                
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
                if(!isset($myvalues['id']))
                {
                    form_set_error('shortname','Cannot delete without an ID!');
                    $bGood = FALSE;
                }
            }
            
            if(trim($myvalues['shortname']) == '')
            {
                form_set_error('shortname','The short name cannot be empty');
                $bGood = FALSE;
            } else
            if(strlen(trim($myvalues['shortname'])) > 40)
            {
                $thelen = strlen(trim($myvalues['shortname']));
                form_set_error('shortname',"The short name cannot contain more than 40 characters (size currently $thelen)");
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
                    $result = db_select(DatabaseNamesHelper::$m_project_baseline_tablename,'p')
                        ->fields('p')
                        ->condition('shortname', $myvalues['shortname'],'=')
                        ->condition('projectid', $this->m_projectid,'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('shortname', 'Already have a project baseline with this name');
                            $bGood = FALSE;
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
    
    public function getProjectBaselineCreatorOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateProjectBaselineCreators($this->m_projectid);
            foreach($all as $id=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$id] = $title_tx;
            }
            if($include_sysadmin && !array_key_exists(1, $myoptions))
            {
                $myoptions[1] = "System Admin";
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateProjectBaseline($projbaselineid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateProjectBaseline($projbaselineid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function createProjectBaseline($projectid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createProjectBaseline($projectid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }

    function deleteProjectBaseline($projbaseline)
    {
        //return $this->m_oWriteHelper->deleteProjectBaseline($projbaseline);
        return $this->m_oWriteHelper->markProjectBaselineDeleted($projbaseline);
    }
    
    private function getInfoCountMarkup($info)
    {
        $count = $info['count'];
        $reh = $info['remaining_effort_hours'];
        $markup = "[SORTNUM:$count]<span title='$reh hours'>$count</span>";
        return $markup;
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
            $this_uid = $user->uid;
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

            $module_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            global $base_url;
            drupal_add_js(array('myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$module_path/visualization/util_url.js");
            drupal_add_js("$base_url/$module_path/visualization/util_data.js");
            drupal_add_js("$base_url/$module_path/form/js/BrowserGridHelper.js");
            
            $thelimit = BIGFATHOM_MAX_PROJECT_BASELINES_PER_PROJECT;
            if($formType=='A' && $this->m_oMapHelper->getCountBaselinesInProject($this->m_projectid) >= $thelimit)
            {
                drupal_set_message("Cannot add another baseline to this project because it already has the configuration allowed limit of $thelimit per project",'error');
                $disabled = TRUE;
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
            if(isset($myvalues['projectid']))
            {
                $projectid = $myvalues['projectid'];
            } else {
                $projectid = '';
            }
            if(isset($myvalues['shortname']))
            {
                $shortname = $myvalues['shortname'];
            } else {
                $shortname = '';
            }
            if(isset($myvalues['comment_tx']))
            {
                $comment_tx = $myvalues['comment_tx'];
            } else {
                $comment_tx = '';
            }
            if(isset($myvalues['created_by_personid']))
            {
                $created_by_personid = $myvalues['created_by_personid'];
            } else {
                $created_by_personid = $this_uid;
            }
            if(isset($myvalues['created_dt']))
            {
                $created_dt = $myvalues['created_dt'];
            } else {
                $created_dt = NULL;
            }
            if(isset($myvalues['updated_by_personid']))
            {
                $updated_by_personid = $myvalues['updated_by_personid'];
            } else {
                $updated_by_personid = 95;
            }
            if(isset($myvalues['updated_dt']))
            {
                $updated_dt = $myvalues['updated_dt'];
            } else {
                $updated_dt = NULL;
            }

            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_shortname'] 
                = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
            
            $showcolname_shortname = 'shortname';
            $disable_shortname = $disabled || $id==1 || $id==10;
            
            if($disable_shortname)
            {
                $form['hiddenthings']['shortname'] 
                    = array('#type' => 'hidden', '#value' => $shortname, '#disabled' => FALSE);        
                $showcolname_shortname = 'show_shortname';
            }
            
            $form['data_entry_area1'][$showcolname_shortname] = array(
                '#type' => 'textfield',
                '#title' => t('Project Baseline Name'),
                '#default_value' => $shortname,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('The unique convenient name for this project baseline'),
                '#disabled' => $disable_shortname
            );

            $form['data_entry_area1']['comment_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Description'),
                '#default_value' => $comment_tx,
                '#size' => 512,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t("A short description of this project baseline"),
                '#disabled' => $disabled
            );
            
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item', 
                '#prefix' => '<div>',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            if(is_array($this->m_analysis_info))
            {
                $my_analysis_info = $this->m_analysis_info;
                $lookup_peopleinfo = $my_analysis_info['lookup']['people'];
                $submaps = $my_analysis_info['maps'];

                $status_cd2info = $submaps['workitems']['summary']['maps']['status_cd2info'];
                $owner_personid2info = $submaps['workitems']['summary']['maps']['owner_personid2info'];

                $work_by_status_rows = "";
                foreach($status_cd2info as $status_cd=>$info)
                {
                    $reh = $info['remaining_effort_hours'];
                    $submap = $info['submap'];

                    $unstarted_info = $submap['workstarted_yn'][0];
                    $started_and_open_info = $submap['started_and_open_yn'][1];
                    $closed_info = $submap['terminal_yn'][1];

                    $unstarted_markup = $this->getInfoCountMarkup($unstarted_info);
                    $started_and_open_markup = $this->getInfoCountMarkup($started_and_open_info);
                    $closed_markup = $this->getInfoCountMarkup($closed_info);

                    $wids_ar = array_keys($info['wids']);
                    sort($wids_ar);
                    $count_wids = count($wids_ar);
                    $wids_markup = "[SORTNUM:$count_wids]<span title='$count_wids workitems'>" . implode(', ', $info['wids']) . "</span>";
                    $work_by_status_rows .= "\n<tr>"
                                . "<td>$status_cd</td>"
                                . "<td>$reh</td>"
                                . "<td>$unstarted_markup</td>"
                                . "<td>$started_and_open_markup</td>"
                                . "<td>$closed_markup</td>"
                                . "<td>{$wids_markup}</td>"
                            . "</tr>";
                }
                $form["data_entry_area1"]['table_container']['work_by_status'] = array('#type' => 'item',
                        '#markup' => '<h2>Work Insights by Status</h2>'
                                    . '<table id="work_by_status" class="browserGrid">'
                                    . '<thead>'
                                    . '<tr>'
                                    . '<th datatype="text" class="nowrap" title="Status code">'.t('Status').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Total hours Estimated Remaining Effort">'.t('ERE').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Unstarted Workitems">'.t('UW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Started Workitems">'.t('SW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Closed Workitems">'.t('CW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Workitems having this status">'.t('Workitems').'</th>'
                                    . '</tr>'
                                    . '</thead>'
                                    . '<tbody>'
                                    . $work_by_status_rows
                                    .  '</tbody>'
                                    . '</table>'
                                    . '<br>');

                $work_by_person_rows = "";
                foreach($owner_personid2info as $owner_personid=>$info)
                {
                    $reh = $info['remaining_effort_hours'];
                    $person_fullname = $lookup_peopleinfo[$owner_personid]['fullname'];
                    $submap = $info['submap'];

                    $unstarted_info = $submap['workstarted_yn'][0];
                    $started_and_open_info = $submap['started_and_open_yn'][1];
                    $closed_info = $submap['terminal_yn'][1];

                    $unstarted_markup = $this->getInfoCountMarkup($unstarted_info);
                    $started_and_open_markup = $this->getInfoCountMarkup($started_and_open_info);
                    $closed_markup = $this->getInfoCountMarkup($closed_info);

                    $wids_ar = array_keys($info['wids']);
                    sort($wids_ar);
                    $count_wids = count($wids_ar);
                    $wids_markup = "[SORTNUM:$count_wids]<span title='$count_wids workitems'>" . implode(', ', $info['wids']) . "</span>";
                    $work_by_person_rows .= "\n<tr>"
                                . "<td>$person_fullname</td>"
                                . "<td>$reh</td>"
                                . "<td>$unstarted_markup</td>"
                                . "<td>$started_and_open_markup</td>"
                                . "<td>$closed_markup</td>"
                                . "<td>{$wids_markup}</td>"
                            . "</tr>";
                }
                $form["data_entry_area1"]['table_container']['work_by_person'] = array('#type' => 'item',
                        '#markup' => '<h2>Work Insights by Primary Owner</h2>'
                                    . '<table id="work_by_person" class="browserGrid">'
                                    . '<thead>'
                                    . '<tr>'
                                    . '<th datatype="formula" class="nowrap" title="Primary owner of the workitem">'.t('Owner').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Total hours Estimated Remaining Effort">'.t('ERE').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Unstarted Workitems">'.t('UW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Started Workitems">'.t('SW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Number of Closed Workitems">'.t('CW').'</th>'
                                    . '<th datatype="formula" class="nowrap" title="Workitems owned by this person">'.t('Workitems').'</th>'
                                    . '</tr>'
                                    . '</thead>'
                                    . '<tbody>'
                                . $work_by_person_rows
                                .  '</tbody>'
                                . '</table>'
                                . '<br>');
            }

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
