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
 * Help with usecase information
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UseCasePageHelper
{
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_projectid = NULL;
    
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
    function getFieldValues($usecaseid=NULL)
    {
        try
        {
            if($usecaseid != NULL)
            {
                
                $myvalues = $this->m_oMapHelper->getOneRichUsecaseRecord($usecaseid);
                
                $map_tag2usecase = [];
                if(!empty($myvalues['maps']['tags']))
                {
                    foreach($myvalues['maps']['tags'] as $tag)
                    {
                        $map_tag2usecase[] = $tag;
                    }
                    unset($myvalues['maps']['tags']);
                }
                $myvalues['map_tag2usecase'] = $map_tag2usecase;
                

                $map_workitem2usecase = [];
                if(!empty($myvalues['maps']['workitems']))
                {
                    foreach($myvalues['maps']['workitems'] as $wid)
                    {
                        $map_workitem2usecase[] = $wid;
                    }
                    unset($myvalues['maps']['workitems']);
                }
                $myvalues['map_workitem2usecase'] = $map_workitem2usecase;

            } else {
                
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['usecase_dt'] = NULL;
                $myvalues['usecase_nm'] = NULL;
                $myvalues['countryid'] = NULL;
                $myvalues['stateid'] = NULL;
                $myvalues['comment_tx'] = NULL;
                $myvalues['apply_to_all_users_yn'] = NULL;
                $myvalues['created_by_personid'] = NULL;
                $myvalues['updated_by_personid'] = NULL;
                
                $myvalues['map_workitem2usecase'] = [];
                $myvalues['map_tag2usecase'] = [];
                $myvalues['map_delegate_owner'] = [];
                
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
                    form_set_error('usecase_nm','Cannot delete without an ID!');
                    $bGood = FALSE;
                }
            }
            
            if(trim($myvalues['usecase_nm']) == '')
            {
                form_set_error('usecase_nm','The usecase name cannot be empty');
                $bGood = FALSE;
            } else {
                if($formMode == 'A' || $formMode == 'E')
                {
                    if(!is_numeric($myvalues['importance']) || trim($myvalues['importance']) == '')
                    {
                        form_set_error('importance','The importance must be a number in range [0,100]');
                        $bGood = FALSE;
                    } else if($myvalues['importance'] < 0 || $myvalues['importance'] > 100)
                    {
                        form_set_error('importance','The importance must be a number in range [0,100]');
                        $bGood = FALSE;
                    }
                    
                    if($formMode == 'A')
                    {
                        $allowed_count = 0;
                    } else {
                        $allowed_count = 1;
                    }
                    //Check for duplicate keys too
                    $result = db_select(DatabaseNamesHelper::$m_usecase_tablename,'p')
                        ->fields('p')
                        ->condition('usecase_nm', $myvalues['usecase_nm'],'=')
                        ->condition('owner_projectid', $this->m_projectid,'=')
                        ->execute();
                    if($result->rowCount() > 0)
                    {
                        $record = $result->fetchAssoc();
                        $found_id = $record['id'];
                        if($found_id != $myvalues['id'])
                        {
                            form_set_error('usecase_nm', 'Already have a usecase with this name');
                            $bGood = FALSE;
                        }
                    }
                    
                    if(!empty($myvalues['map_workitem2usecase_tx']))
                    {
                        if(is_array($myvalues['map_workitem2usecase_tx']))
                        {
                            $map_workitem2usecase_ar = $myvalues['map_workitem2usecase_tx'];
                        } else {
                            $map_workitem2usecase_ar = explode(',',$myvalues['map_workitem2usecase_tx']);
                        }
                    } else if(isset($myvalues['maps']['workitems']) && is_array($myvalues['maps']['workitems'])) {
                        $map_workitem2usecase_ar = $myvalues['maps']['workitems'];
                    } else {
                        $map_workitem2usecase_ar = [];
                    }
                    $clean_map_workitem2usecase_ar = [];
                    foreach($map_workitem2usecase_ar as $candidate_wid)
                    {
                        if(!empty(trim($candidate_wid)))
                        {
                            if(is_numeric($candidate_wid))
                            {
                                $clean_map_workitem2usecase_ar[$candidate_wid] = $candidate_wid;
                            } else {
                                form_set_error('map_workitem2usecase_tx', "The entry '$candidate_wid' is not a numeric ID!");
                                $bGood = FALSE;
                                break;
                            }
                        }
                    }
                    if(count($clean_map_workitem2usecase_ar)>0)
                    {
                        $bad_wids_ar = $this->m_oMapHelper->getWorkitemsNotInProject($this->m_projectid, $clean_map_workitem2usecase_ar);
                        if(count($bad_wids_ar) > 0)
                        {
                            $badcount = count($bad_wids_ar);
                            $bad_wids_txt = implode(", ", $bad_wids_ar);
                            form_set_error('map_workitem2usecase_tx', "The following $badcount workitems are NOT in project#{$this->m_projectid}: $bad_wids_txt");
                            $bGood = FALSE;
                        }
                    }
                    if(!empty($myvalues['effort_tracking_workitemid']))
                    {
                        $effort_tracking_workitemid = $myvalues['effort_tracking_workitemid'];
                        if(!is_numeric($effort_tracking_workitemid))
                        {
                            form_set_error('effort_tracking_workitemid', "The value '$effort_tracking_workitemid' is not numeric!");
                            $bGood = FALSE;
                        }
                        $opid = $this->m_oMapHelper->getProjectIDForWorkitem($effort_tracking_workitemid, FALSE);
                        if($this->m_projectid != $opid)
                        {
                            form_set_error('effort_tracking_workitemid', "The value $effort_tracking_workitemid is not a workitemid in the project!");
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
    
    public function getUseCaseCreatorOptions($includeblank=TRUE, $include_sysadmin=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getCandidateUseCaseCreators($this->m_projectid);
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
    
    public function getUseCaseStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getUseCaseStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getUseCasePerspectiveOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $myoptions['U'] = 'User Story';
            $myoptions['T'] = 'Technical Story';
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateUsecase($usecaseid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->updateUsecase($usecaseid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }
    
    public function createUsecase($projectid, $myvalues)
    {
        try
        {
            return $this->m_oWriteHelper->createUsecase($projectid, $myvalues);
        }
        catch(\Exception $ex)
        {
            throw $ex;
        }
    }

    function deleteUsecase($usecase)
    {
        $this->m_oWriteHelper->deleteUsecase($usecase);
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
            if(isset($myvalues['owner_projectid']))
            {
                $owner_projectid = $myvalues['owner_projectid'];
            } else {
                $owner_projectid = '';
            }
            if(isset($myvalues['usecase_nm']))
            {
                $usecase_nm = $myvalues['usecase_nm'];
            } else {
                $usecase_nm = '';
            }
            if(isset($myvalues['blurb_tx']))
            {
                $blurb_tx = $myvalues['blurb_tx'];
            } else {
                $blurb_tx = '';
            }
            if(isset($myvalues['perspective_cd']))
            {
                $perspective_cd = $myvalues['perspective_cd'];
            } else {
                $perspective_cd = '';
            }
            if(isset($myvalues['precondition_tx']))
            {
                $precondition_tx = $myvalues['precondition_tx'];
            } else {
                $precondition_tx = '';
            }
            if(isset($myvalues['postcondition_tx']))
            {
                $postcondition_tx = $myvalues['postcondition_tx'];
            } else {
                $postcondition_tx = '';
            }
            if(isset($myvalues['steps_tx']))
            {
                $steps_tx = $myvalues['steps_tx'];
            } else {
                $steps_tx = '';
            }
            if(isset($myvalues['references_tx']))
            {
                $references_tx = $myvalues['references_tx'];
            } else {
                $references_tx = '';
            }
            if(isset($myvalues['active_yn']))
            {
                $active_yn = $myvalues['active_yn'];
            } else {
                $active_yn = '';
            }
            
            if(isset($myvalues['effort_tracking_workitemid']))
            {
                $effort_tracking_workitemid = $myvalues['effort_tracking_workitemid'];
            } else {
                $effort_tracking_workitemid = NULL;
            }
            
            if(isset($myvalues['owner_personid']))
            {
                $owner_personid = $myvalues['owner_personid'];
            } else {
                $owner_personid = $this_uid;
            }
            if(isset($myvalues['status_cd']))
            {
                $status_cd = $myvalues['status_cd'];
            } else {
                $status_cd = NULL;
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 95;
            }

            $options_usecase_status = $this->getUseCaseStatusOptions();
            //$options_delegate_owner = $this->getWorkitemDelegateOwnerOptions();
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_usecase_nm'] 
                = array('#type' => 'hidden', '#value' => $usecase_nm, '#disabled' => FALSE);        
            
            $showcolname_usecase_nm = 'usecase_nm';
            $disable_usecase_nm = $disabled || $id==1 || $id==10;
            
            $options_usecaseowners = $this->getUseCaseCreatorOptions();
            $options_perspective = $this->getUseCasePerspectiveOptions();

            
            if($disable_usecase_nm)
            {
                $form['hiddenthings']['usecase_nm'] 
                    = array('#type' => 'hidden', '#value' => $usecase_nm, '#disabled' => FALSE);        
                $showcolname_usecase_nm = 'show_usecase_nm';
            }
            
            $form['data_entry_area1']['perspective_cd'] = array(
                '#type' => 'select',
                '#title' => t('Perspective'),
                '#default_value' => $perspective_cd,
                '#options' => $options_perspective,
                '#required' => TRUE,
                '#description' => t('From what perspective is this story told?'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1'][$showcolname_usecase_nm] = array(
                '#type' => 'textfield',
                '#title' => t('Use Case Name'),
                '#default_value' => $usecase_nm,
                '#size' => 80,
                '#maxlength' => 80,
                '#required' => TRUE,
                '#description' => t('The unique convenient name for this use case'),
                '#disabled' => $disable_usecase_nm
            );

            $form['data_entry_area1']['importance'] = array(
                '#type' => 'textfield',
                '#title' => t('Importance'),
                '#default_value' => $importance,
                '#size' => 3,
                '#maxlength' => 3,
                '#required' => TRUE,
                '#description' => t('Current importance for implementing this use case in the context of the project.  Scale is [0,100] with 0 being no importance whatsoever and 100 being nothing is more important.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_usecase_status,
                '#required' => TRUE,
                '#description' => t('The current status of this use case'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['blurb_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Blurb'),
                '#default_value' => $blurb_tx,
                '#size' => 256,
                '#maxlength' => 256,
                '#required' => TRUE,
                '#description' => t("A short description of this use case"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['precondition_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Preconditions'),
                '#default_value' => $precondition_tx,
                '#size' => 512,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t("A short description of the conditions that exist for this use case to begin"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['postcondition_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Postconditions'),
                '#default_value' => $postcondition_tx,
                '#size' => 512,
                '#maxlength' => 512,
                '#required' => TRUE,
                '#description' => t("A short description of the conditions that exist once all the use case steps are completed"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['steps_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Steps'),
                '#default_value' => $steps_tx,
                '#size' => 2000,
                '#maxlength' => 8000,
                '#required' => TRUE,
                '#description' => t("The steps, in order, that are executed to transition from the preconditions to the postconditions."),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['references_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('References'),
                '#default_value' => $references_tx,
                '#size' => 512,
                '#maxlength' => 2000,
                '#required' => FALSE,
                '#description' => t("Bibliographic style references, if any, that are relevant to this use case"),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => t('Owned By'),
                '#default_value' => $owner_personid,
                '#options' => $options_usecaseowners,
                '#required' => TRUE,
                '#description' => t('The user that owns this use case'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['effort_tracking_workitemid'] = array(
                '#type' => 'textfield',
                '#title' => t('Effort Tracking Workitem'),
                '#default_value' => $effort_tracking_workitemid,
                '#size' => 10,
                '#maxlength' => 20,
                '#required' => FALSE,
                '#description' => t('Optionally provide the workitem ID of the workitem record which tracks the level of effort expected/remaining to complete refinement of this use case'),
                '#disabled' => $disabled
            );
            
            if(isset($myvalues['map_workitem2usecase']))
            {
                $default_parent_workitem_text = implode(', ', $myvalues['map_workitem2usecase']);
            } else {
                $default_parent_workitem_text = '';
            }
            $form['data_entry_area1']['map_workitem2usecase_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('Workitems'),
                '#default_value' => $default_parent_workitem_text,
                '#size' => 80,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#description' => t('Optional comma delimited list of workitem IDs to associate with this use case'),
                '#disabled' => $disabled
            );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Live'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('No if the use case has been retired.')
            );

            if(isset($myvalues['map_tag2usecase']))
            {
                $default_parent_tag_text = implode(', ', $myvalues['map_tag2usecase']);
            } else {
                $default_parent_tag_text = '';
            }
            $form['data_entry_area1']['map_tag2usecase_tx'] = array(
                '#type' => 'textfield',
                '#title' => t('Tags'),
                '#default_value' => $default_parent_tag_text,
                '#size' => 80,
                '#maxlength' => 512,
                '#required' => FALSE,
                '#description' => t('Optional comma delimited text tags to associate with this use case'),
                '#disabled' => $disabled
            );
            
            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
