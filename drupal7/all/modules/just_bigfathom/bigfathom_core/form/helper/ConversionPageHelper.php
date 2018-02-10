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
 * Help with conversions between Goals and Projects
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class ConversionPageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_this_projectid = NULL;
    protected $m_this_project_info = NULL;
    protected $m_root_goalid = NULL;
    protected $m_default_parent_projectid = NULL;
    protected $m_default_parent_project_info = NULL;
    protected $m_is_toplevel_project = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $default_parent_projectid=NULL, $this_projectid=NULL, $root_goalid=NULL)
    {
        try
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_this_projectid = $this_projectid;
            $this->m_default_parent_projectid = $default_parent_projectid;
            if(!empty($default_parent_projectid))
            {
                //This is a subproject; get the parent details
                $this->m_default_parent_project_info = $this->m_oContext->getProjectInfo($default_parent_projectid);
                if(empty($this->m_default_parent_project_info))
                {
                    throw new \Exception("Failed getting info for default_parent_projectid#{$default_parent_projectid}");
                }
            }
            if(!empty($this_projectid))
            {
                //This is a subproject; get the parent details
                $this->m_this_project_info = $this->m_oContext->getProjectInfo($this_projectid);
                if(empty($this->m_this_project_info))
                {
                    throw new \Exception("Failed getting info for this_projectid#{$this_projectid}");
                }
            }
            if(!empty($root_goalid))
            {
                $this->m_root_goalid = $root_goalid;
            } else {
                if(!empty($this->m_this_project_info))
                {
                    $this->m_root_goalid = $this->m_this_project_info['root_goalid'];
                }
            }
            if(empty($this->m_root_goalid ))
            {
                $errmsg = "Failed construct(URLS, (classname)=$my_classname, (default_parent_projectid)=$default_parent_projectid, (this_projectid)=$this_projectid, (root_goalid)=$root_goalid)";
                DebugHelper::showNeatMarkup($this->m_this_project_info,$errmsg);
                DebugHelper::showStackTrace($errmsg);
                throw new \Exception($errmsg);
            }

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
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getInUseDetails($projectid)
    {
        try
        {
            module_load_include('php','bigfathom_core','core/ConnectionChecker');
            $oConnectionChecker = new \bigfathom\ConnectionChecker();
            return $oConnectionChecker->getConnectionsOfProject($projectid);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get the values to populate the form.
     */
    function getFieldValues($projectid=NULL)
    {
        try
        {
            if(empty($projectid))
            {
                $projectid = $this->m_this_projectid;
                $myvalues['parent_projectid'] = $this->m_default_parent_projectid;
            } else {
                //TODO -- lookup parent for the provided projectid
                $myvalues['parent_projectid'] = NULL;
            }
            $myvalues['root_goalid'] = $this->m_root_goalid;
            if(empty($myvalues['root_goalid']))
            {
                $errmsg = "Missing expected root goalid!";
                DebugHelper::showStackTrace($errmsg);
                DebugHelper::showNeatMarkup($myvalues,$errmsg);
                throw new \Exception($errmsg);
            }
            if(!empty($projectid))
            {
                //Get the core values 
                $myvalues = $this->m_oMapHelper->getOneProjectDetailData($projectid);
                $myvalues['map_group2project'] = $this->m_oMapHelper->getIDListOfGroupsInProject($projectid);
                $myvalues['map_role2project'] = $this->m_oMapHelper->getIDListOfRolesInProject($projectid);
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['root_workitem_nm'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['project_contextid'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['mission_tx'] = NULL;
                $myvalues['map_group2project'] = NULL;
                $myvalues['map_role2project'] = NULL;
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
    function formIsValid($form, &$myvalues, $formType)
    {
        try
        {
            $bGood = TRUE;

            if(!isset($myvalues['mission_tx']) || trim($myvalues['mission_tx']) == '')
            {
                form_set_error('mission_tx', 'Must provide a mission statement for this project');
                $bGood = FALSE;
            }
            if(!isset($myvalues['owner_personid']) || trim($myvalues['owner_personid']) == '')
            {
                form_set_error('owner_personid', 'Must declare a project manager');
                $bGood = FALSE;
            }
            
            //Done with all validations.
            return $bGood;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getGoalStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getWorkitemStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getProjectStatusOptions($show_terminal_text=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getWorkitemStatusByCode();
            foreach($all as $code=>$record)
            {
                $myoptions[$code] = $record['wordy_status_state'];
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Return DRUPAL form API of the context dashboard for a goal
     */
    public function getContextDashboardElements($projectid)
    {
        $myvalues = $this->getFieldValues($projectid);
        $elements = array();
        $elements['dashboard'] = array(
            '#type' => 'item',
            '#prefix' => '<table class="context-dashboard">',
            '#suffix' => '</table>',
            '#tree' => TRUE
        );
        $owner_personid = $myvalues["owner_personid"];
        $ownername = $myvalues['first_nm'] . " " . $myvalues['last_nm'];
        $active_yn_markup = $myvalues['active_yn'] == 1 ? '<span>Yes</span>' : '<span class="colorful-no">No</span>';
        $mission_tx = $myvalues['mission_tx'];
        if(isset($myvalues['map_tag2workitem']))
        {
            $tags_tx = implode(', ', $myvalues['map_tag2workitem']);
        } else {
            $tags_tx = '';
        }
        $elements['dashboard']['details']['row1a'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td width='180px'><label for='project_name'>Project Name</label></td>"
                . "<td colspan=13><span id='project_name' title='root goal#{$myvalues['root_goalid']}'>{$myvalues['root_workitem_nm']}</span></td>"
                . "<td><label for='isactive' title='Setting active to No is a type of soft delete'>Is Active</label></td>"
                . "<td><span id='isactive'>{$active_yn_markup}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label for='mission_tx'>Mission</label></td>"
                . "<td colspan=15><span id='mission_tx'>{$mission_tx}</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row2b'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='statuscode'>Status</label></td>"
                . "<td colspan=1><span id='statuscode'>{$myvalues['status_cd']}</span></td>"
                . "<td width='180px' colspan=1><label>Owner</label></td>"
                . "<td colspan=13><span title='#{$owner_personid}'>$ownername</span></td>"
                . "</tr>");
        $elements['dashboard']['details']['row3'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td><label for='importance' title='Values in range of [75,100] are important activities which have been delayed too long; [50,74] are important and still categorized as on time.  Lower values are not categorized as important.'>Importance</label></td>"
                . "<td colspan=1><span id='importance'>{$myvalues['importance']}</span></td>"
                . "<td><label for='effort_hours_est' title='Estimated number of hours effort required in total by this goal'>Estimated Effort</label></td>"
                . "<td><span id='effort_hours_est'>{$myvalues['effort_hours_est']}</span></td>"
                . "<td><label for='effort_hours_est_p' title='Confidence of the estimate expressed as a probablity in range [0,1]'>Estimate Confidence</label></td>"
                . "<td><span id='effort_hours_est_p'>{$myvalues['effort_hours_est_p']}</span></td>"
                . "<td><label for='effort_hours_worked_act' title='Actual number of hours effort that were invested in this goal to complete it'>Actual Effort</label></td>"
                . "<td><span id='effort_hours_worked_act'>{$myvalues['effort_hours_worked_act']}</span></td>"
                . "<td colspan='8'></td>"
                . "</tr>");
        $elements['dashboard']['details']['row4'] = array('#type' => 'item',
                '#markup' => "<tr>"
                . "<td colspan=1><label for='tags' title='Text labels associated with this goal'>Tags</label></td>"
                . "<td colspan=15><span id='tags'>{$tags_tx}</span></td>"
                . "</tr>");
        return $elements;    
    }
    
    public function getProjectContextOptions($add_unknown=FALSE, $only_active=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getProjectContextsByID(NULL, $add_unknown, $only_active);
            foreach($all as $id=>$record)
            {
                $shortname = $record['shortname'];
                $myoptions[$id] = $shortname;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getMemberGroupOptions($parent_projectid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getGroupsByID(); //TODO ---- FILTER!!!!!
            foreach($all as $code=>$record)
            {
                $title_tx = $record['group_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function getMemberRoleOptions($parent_projectid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            $all = $this->m_oMapHelper->getRolesByID(); //TODO ---- FILTER!!!!!
            foreach($all as $code=>$record)
            {
                $title_tx = $record['role_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function getProjectManagerOptions($includeblank=TRUE, $include_in_list_personid=NULL)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getProjectLeaders(NULL, $include_in_list_personid);
            foreach($all as $code=>$record)
            {
                $title_tx = $record['last_nm'] . ", " . $record['first_nm'];
                $myoptions[$code] = $title_tx;
            }
            return $myoptions;
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
    function getProject2GoalForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            if($formType != 'A')
            {
                throw new \Exception("We only support ADD for conversion!");
            }
            $parent_projectid =  $this->m_default_parent_projectid;
            $this_projectid = $this->m_this_projectid;
            $root_goalid = $this->m_root_goalid;
            $this_project_info = $this->m_this_project_info;
            if(empty($myvalues['root_goalid']))
            {
                $myvalues['root_goalid'] = $this->m_root_goalid;    
            }
            $root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($this->m_root_goalid);
            if(empty($myvalues['owner_personid']))
            {
                $myvalues['owner_personid'] = $root_goal_info['owner_personid']; 
            }
            if(empty($myvalues['map_role2project']))
            {
                $myvalues['map_role2project'] = $this->m_oMapHelper->getIDListOfRolesInProject($this_projectid);
            }
            if(empty($myvalues['project_contextid']))
            {
                $myvalues['project_contextid'] = $this->m_this_project_info['project_contextid'];
            }
                
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
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . t("Conversion of project#{$this_projectid} into a goal will discard all of the project specific content (e.g, project context, mission statement, project manager) and leave behind just its content with root goal#{$root_goalid} as a contributer to project#{$parent_projectid}")
                . "</p>",
                '#suffix' => '</div>',
            );
                
            if(isset($myvalues['parent_projectid']))
            {
                $parent_projectid = $myvalues['parent_projectid'];
            } else {
                $parent_projectid = $parent_projectid;
            }
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
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
                $status_cd = $root_goal_info['status_cd'];
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 40;
            }
            if(isset($myvalues['mission_tx']))
            {
                $mission_tx = $myvalues['mission_tx'];
            } else {
                $mission_tx = $this_project_info['mission_tx'];
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = $root_goal_info['purpose_tx'];
            }
            if(isset($myvalues['project_contextid']))
            {
                $project_contextid = $myvalues['project_contextid'];
            } else {
                $project_contextid = $this_project_info['project_contextid'];
            }
            
            if(isset($myvalues['map_group2project']))
            {
                $default_groupsinproject = $myvalues['map_group2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_groupsinproject = array();
                } else {
                    $default_groupsinproject = $this->m_oMapHelper->getIDListOfGroupsInProject($parent_projectid);
                }
            }

            if(isset($myvalues['map_role2project']))
            {
                $default_rolesinproject = $myvalues['map_role2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_rolesinproject = array();
                } else {
                    $default_rolesinproject = $this->m_oMapHelper->getIDListOfRolesInProject($parent_projectid);
                }
            }
            $options_goal_status = $this->getGoalStatusOptions();
            $options_projmanagers = $this->getProjectManagerOptions(TRUE,$owner_personid);
            $options_member_groups = $this->getMemberGroupOptions();
            $options_member_roles = $this->getMemberRoleOptions();

            $show_unknown_projectcontext = ($formType == 'A');
            $show_only_active_projectcontext = ($formType == 'A' || $formType == 'E');
            $options_projectcontext = $this->getProjectContextOptions($show_unknown_projectcontext, $show_only_active_projectcontext);
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['root_goalid']
                = array('#type' => 'hidden', '#value' => $root_goalid, '#disabled' => FALSE);        
            $form['hiddenthings']['this_projectid']
                = array('#type' => 'hidden', '#value' => $this_projectid, '#disabled' => FALSE);        
            $form['hiddenthings']['parent_projectid']
                = array('#type' => 'hidden', '#value' => $parent_projectid, '#disabled' => FALSE);        

            $form['data_entry_area1']['projectmetadata'] = array(
                '#type' => 'fieldset', 
                '#title' => 'Project metadata that will be discarded',
                '#collapsible' => FALSE,
                '#collapsed' => FALSE, 
                '#tree' => TRUE,
                '#disabled' => TRUE
            );
            
            $form['data_entry_area1']['projectmetadata']['project_contextid'] = array(
                '#type' => 'select',
                '#title' => t('Project Context'),
                '#default_value' => $project_contextid,
                '#options' => $options_projectcontext,
                '#required' => TRUE,
                '#description' => t('The broad category under-which people might look for this project'),
                '#disabled' => TRUE
            );
            
            $form['data_entry_area1']['projectmetadata']['mission_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Mission Statement'),
                '#default_value' => $mission_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('The overall mission vision of this project.'),
                '#disabled' => TRUE
            );

            $form['data_entry_area1']['projectmetadata']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Project Leader'),
                '#default_value' => $owner_personid,
                '#options' => $options_projmanagers,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful completion of this project?'),
                '#disabled' => TRUE
            );
            
            $form['data_entry_area1']['projectmetadata']['map_group2project'] = $this->m_oFormHelper->getMultiSelectElement(TRUE
                    , 'Member Groups'
                    , $default_groupsinproject
                    , $options_member_groups
                    , TRUE
                    , 'The groups directly contributing to the successful completion of this project'
                );

            $form['data_entry_area1']['projectmetadata']['map_role2project'] = $this->m_oFormHelper->getMultiSelectElement(TRUE
                    , 'Relevant Team Member Roles'
                    , $default_rolesinproject
                    , $options_member_roles
                    , TRUE
                    , 'Roles directly relevant to the successful completion of this project'
                );
            
            $form['data_entry_area1']['root_workitem_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Root Goal Name'),
                '#default_value' => $root_goal_info["workitem_nm"],
                '#size' => 40,
                '#maxlength' => 40,
                '#description' => t('The root goal of this project is the synonym for the project name.  This goal exists in the system as #' . $root_goalid . "."),
                '#disabled' => TRUE
            );
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Goal Purpose'),
                '#default_value' => $root_goal_info["purpose_tx"],
                '#size' => 80,
                '#maxlength' => 1024,
                '#description' => t('The described purpose of goal at the root of this project'),
                '#disabled' => TRUE
            );
            //The status can be edited
            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_goal_status,
                '#required' => TRUE,
                '#description' => t('The current status of this root goal'),
                '#disabled' => $disabled
            );

            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if project is active, else no.')
            );

            return $form;
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
    function getGoal2ProjectForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            if($formType != 'A')
            {
                throw new \Exception("We only support ADD for conversion!");
            }
            
            $root_goalid = $this->m_root_goalid;
            if(empty($this->m_root_goalid))
            {
                throw new \Exception("Expected to have a root goalid value!");
            }
            $root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($this->m_root_goalid);
            if(empty($this->m_this_projectid))
            {
                $this->m_this_projectid = $root_goal_info["owner_projectid"];
            }
            $this_projectid = $this->m_this_projectid;
            if(empty($myvalues['root_goalid']))
            {
                $myvalues['root_goalid'] = $this->m_root_goalid;    
            }
            $root_goal_info = $this->m_oMapHelper->getOneBareWorkitemRecord($this->m_root_goalid);
            if(empty($myvalues['owner_personid']))
            {
                $myvalues['owner_personid'] = $root_goal_info['owner_personid'];
            }
            if(!empty($this->m_dependent_projectid))
            {
                $onedependent_projectid = $this->m_dependent_projectid;
            } else {
                $onedependent_projectid = $root_goal_info["owner_projectid"];   //Make the new project an antecedant of this project
            }
            $onedependent_project_info = $this->m_oMapHelper->getOneProjectDetailData($onedependent_projectid);
            if(empty($myvalues['map_group2project']))
            {
                $myvalues['map_group2project'] = $this->m_oMapHelper->getIDListOfGroupsInProject($onedependent_projectid);
            }
            if(empty($myvalues['map_role2project']))
            {
                $myvalues['map_role2project'] = $this->m_oMapHelper->getIDListOfRolesInProject($onedependent_projectid);
            }
            if(empty($myvalues['project_contextid']))
            {
                $myvalues['project_contextid'] = $onedependent_project_info['project_contextid'];
            }
                
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
            if(!empty($this->m_default_parent_projectid))
            {
                $superparent_info = " that contributes to the success of project#" . $this->m_default_parent_projectid . ".";
            } else {
                $superparent_info = ".";
            }
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . "Conversion of goal#{$root_goalid} will make it the root goal of a new project{$superparent_info}"
                . "</p>",
                '#suffix' => '</div>',
            );
                
            if(isset($myvalues['parent_projectid']))
            {
                $parent_projectid = $myvalues['parent_projectid'];
            } else {
                $parent_projectid = NULL;
            }
            if(isset($myvalues['id']))
            {
                $id = $myvalues['id'];
            } else {
                $id = '';
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
                $status_cd = 'WNS';
            }
            if(isset($myvalues['importance']))
            {
                $importance = $myvalues['importance'];
            } else {
                $importance = 40;
            }
            if(isset($myvalues['mission_tx']))
            {
                $mission_tx = $myvalues['mission_tx'];
            } else {
                $mission_tx = '';
            }
            if(isset($myvalues['purpose_tx']))
            {
                $purpose_tx = $myvalues['purpose_tx'];
            } else {
                $purpose_tx = '';
            }
            if(isset($myvalues['project_contextid']))
            {
                $project_contextid = $myvalues['project_contextid'];
            } else {
                $project_contextid = '';
            }
            
            if(isset($myvalues['map_group2project']))
            {
                $default_groupsinproject = $myvalues['map_group2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_groupsinproject = array();
                } else {
                    $default_groupsinproject = $this->m_oMapHelper->getIDListOfGroupsInProject($parent_projectid);
                }
            }

            if(isset($myvalues['map_role2project']))
            {
                $default_rolesinproject = $myvalues['map_role2project'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_rolesinproject = array();
                } else {
                    $default_rolesinproject = $this->m_oMapHelper->getIDListOfRolesInProject($parent_projectid);
                }
            }
            $options_goal_status = $this->getGoalStatusOptions();
            $options_projmanagers = $this->getProjectManagerOptions(TRUE);
            $options_member_groups = $this->getMemberGroupOptions();
            $options_member_roles = $this->getMemberRoleOptions();

            $show_unknown_projectcontext = ($formType == 'A');
            $show_only_active_projectcontext = ($formType == 'A' || $formType == 'E');
            $options_projectcontext = $this->getProjectContextOptions($show_unknown_projectcontext, $show_only_active_projectcontext);
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['root_goalid']
                = array('#type' => 'hidden', '#value' => $root_goalid, '#disabled' => FALSE);        
            $form['hiddenthings']['parent_projectid']
                = array('#type' => 'hidden', '#value' => $parent_projectid, '#disabled' => FALSE);        

            //Converting a goal into a project
            $disable_rootgoalfields = TRUE;
            $form['data_entry_area1']['root_workitem_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Root Goal Name'),
                '#default_value' => $root_goal_info["workitem_nm"],
                '#size' => 40,
                '#maxlength' => 40,
                '#description' => t('The root goal of this project is the synonym for the project name.  This goal exists in the system as #' . $root_goalid . "."),
                '#disabled' => $disable_rootgoalfields
            );
            $form['data_entry_area1']['purpose_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Goal Purpose'),
                '#default_value' => $root_goal_info["purpose_tx"],
                '#size' => 80,
                '#maxlength' => 1024,
                '#description' => t('The described purpose of goal at the root of this project'),
                '#disabled' => $disabled
            );
            //The status can be edited
            $form['data_entry_area1']['status_cd'] = array(
                '#type' => 'select',
                '#title' => t('Status Code'),
                '#default_value' => $status_cd,
                '#options' => $options_goal_status,
                '#required' => TRUE,
                '#description' => t('The current status of this root goal'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['project_contextid'] = array(
                '#type' => 'select',
                '#title' => t('Project Context'),
                '#default_value' => $project_contextid,
                '#options' => $options_projectcontext,
                '#required' => TRUE,
                '#description' => t('The broad category under-which people might look for this project'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['mission_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Mission Context Statement '),
                '#default_value' => $mission_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('The overall mission vision of the project.  A mission context statement should suggest a bigger aspiration area to which successfully completing the root goal of this project can contribute.'),
                '#disabled' => $disabled
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Project Leader'),
                '#default_value' => $owner_personid,
                '#options' => $options_projmanagers,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful completion of this project?'),
                '#disabled' => $disabled
            );
            
            $form['data_entry_area1']['map_group2project'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Member Groups'
                    , $default_groupsinproject
                    , $options_member_groups
                    , TRUE
                    , 'The groups directly contributing to the successful completion of this project'
                );

            $form['data_entry_area1']['map_role2project'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Relevant Team Member Roles'
                    , $default_rolesinproject
                    , $options_member_roles
                    , TRUE
                    , 'Roles directly relevant to the successful completion of this project'
                );
            
            $ynoptions = array(
                1 => t('Yes'),
                0 => t('No')
            );
            $form['data_entry_area1']['active_yn'] = array(
                '#type' => 'radios',
                '#title' => t('Active'),
                '#default_value' => isset($myvalues['active_yn']) ? $myvalues['active_yn'] : 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if project is active, else no.')
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
