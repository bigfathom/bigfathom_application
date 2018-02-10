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
 * Help with creating Projects from templates
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class CreateProjectFromTemplatePageHelper
{
    protected $m_oFormHelper = NULL;
    protected $m_oMapHelper = NULL;
    protected $m_oWriteHelper   = NULL;
    protected $m_urls_arr = NULL;
    protected $m_my_classname = NULL;
    protected $m_oContext = NULL;
    protected $m_this_projectid = NULL;
    protected $m_root_template_workitemid = NULL;
    protected $m_default_parent_projectid = NULL;
    protected $m_default_parent_projectinfo = NULL;
    protected $m_is_toplevel_project = NULL;
    
    public function __construct($urls_arr, $my_classname=NULL, $default_parent_projectid=NULL, $this_projectid=NULL, $root_template_workitemid=NULL)
    {
        try
        {
            $this->m_oContext = \bigfathom\Context::getInstance();
            $this->m_root_template_workitemid = $root_template_workitemid;
            $this->m_this_projectid = $this_projectid;
            if($this_projectid != NULL)
            {
                //Compute the parent from the provided projectid
                $this->m_default_parent_projectid = NULL;   //TODO
            } else {
                //Use this as the filter.
                $this->m_default_parent_projectid = $default_parent_projectid;
            }
            $this->m_is_toplevel_project = ($default_parent_projectid == NULL);
            if(!$this->m_is_toplevel_project)
            {
                //This is a subproject; get the parent details
                $this->m_default_parent_projectinfo = $this->m_oContext->getProjectInfo($this->m_default_parent_projectid);
            }
            $this->m_root_template_workitemid = $root_template_workitemid;

            $loaded2 = module_load_include('php','bigfathom_core','core/WriteHelper');
            if(!$loaded2)
            {
                throw new \Exception('Failed to load the WriteHelper class');
            }
            $this->m_oWriteHelper = new \bigfathom\WriteHelper();
            
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
    function getFieldValues($template_projectid=NULL)
    {
        try
        {
            if(empty($template_projectid))
            {
                $template_projectid = $this->m_this_projectid;
                $myvalues['parent_projectid'] = $this->m_default_parent_projectid;
            } else {
                //TODO -- lookup parent for the provided projectid
                $myvalues['parent_projectid'] = NULL;
            }
            $myvalues['root_template_workitemid'] = $this->m_root_template_workitemid;
            if(!empty($template_projectid))
            {
                //Get the core values 
                $myvalues = $this->m_oMapHelper->getOneTPDetailData($template_projectid);
                $new_workitem_nm = $this->m_oMapHelper->getNewProjectRootNameFromTP($template_projectid);
                $myvalues['original_root_workitem_nm'] = $myvalues['root_workitem_nm'];
                $myvalues['root_workitem_nm'] = $new_workitem_nm;
                $myvalues['map_group2tp'] = $this->m_oMapHelper->getIDListOfGroupsInTP($template_projectid);
                $myvalues['map_role2tp'] = $this->m_oMapHelper->getIDListOfRolesInTP($template_projectid);
            } else {
                //Initialize all the values to NULL
                $myvalues = array();
                $myvalues['id'] = NULL;
                $myvalues['root_workitem_nm'] = NULL;
                $myvalues['owner_personid'] = NULL;
                $myvalues['project_contextid'] = NULL;
                $myvalues['importance'] = NULL;
                $myvalues['surrogate_yn'] = NULL;
                $myvalues['active_yn'] = NULL;
                $myvalues['mission_tx'] = NULL;
                $myvalues['map_group2tp'] = NULL;
                $myvalues['map_role2tp'] = NULL;
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
            
            $source_projectid = $myvalues['id'];
            $original_root_workitem_nm = trim($myvalues['original_root_workitem_nm']);
            $new_root_workitem_nm = trim($myvalues['root_workitem_nm']);
            if(empty($new_root_workitem_nm))
            {
                form_set_error('root_workitem_nm', 'You must provide a name for the root workitem');
                $bGood = FALSE;
            }
            if(strtoupper($original_root_workitem_nm) == strtoupper($new_root_workitem_nm))
            {
                form_set_error('root_workitem_nm', 'The root goal must have a different name in the new project');
                $bGood = FALSE;
            }

            if($bGood)
            {
                $proposed_name = $this->m_oMapHelper->getNewProjectRootNameFromTP($source_projectid, $new_root_workitem_nm);
                if(trim(strtoupper($proposed_name)) != trim(strtoupper($new_root_workitem_nm)))
                {
                    form_set_error('root_workitem_nm', 'The root goal name "' . $new_root_workitem_nm . '" already exists, consider name as "' . $proposed_name .'" instead.');
                    $bGood = FALSE;
                }
            }
            
            if($bGood)
            {
                $badthings = TextHelper::getBasicNamingErrors($new_root_workitem_nm);
                foreach($badthings as $onebad)
                {
                    form_set_error('root_workitem_nm', 'The root goal name "' . $new_root_workitem_nm . '"' . $onebad);
                    $bGood = FALSE;
                }
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
                . "<td colspan=13><span id='project_name' title='root goal#{$myvalues['root_template_workitemid']}'>{$myvalues['root_workitem_nm']}</span></td>"
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
    
    public function getProjectManagerOptions($includeblank=TRUE)
    {
        try
        {
            //Get all the relevant select options
            $myoptions = array();
            if($includeblank)
            {
                $myoptions[''] = '';
            }
            $all = $this->m_oMapHelper->getProjectLeaders();
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
    function getForm($formType, $form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            //$root_template_workitemid = NULL;
            //$root_goal_info = NULL;

            $root_workitem_nm = $myvalues['root_workitem_nm'];
            if(empty($myvalues['original_root_workitem_nm']))
            {
                $myvalues['original_root_workitem_nm'] = $root_workitem_nm;   
            }
            $original_root_workitem_nm = $myvalues['original_root_workitem_nm'];
            $root_template_workitemid = $myvalues['root_template_workitemid'];
                
            if($this->m_default_parent_projectid != NULL)
            {
                //This is a subproject
                $require_root_workitem_nm = FALSE;
                $require_root_template_workitemid = TRUE;
                $include_goalid_option = $root_template_workitemid;
            } else {
                //This is a top-level project
                if($formType == 'A')
                {
                    $require_root_workitem_nm = TRUE;
                    $require_root_template_workitemid = FALSE;
                    $include_goalid_option = NULL;
                } else {
                    $require_root_workitem_nm = FALSE;
                    $require_root_template_workitemid = TRUE;
                    $include_goalid_option = $root_template_workitemid;
                }
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
            
            if(isset($myvalues['map_group2tp']))
            {
                $default_groupsinproject = $myvalues['map_group2tp'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_groupsinproject = array();
                } else {
                    $default_groupsinproject = $this->m_oMapHelper->getIDListOfGroupsInProject($parent_projectid);
                }
            }

            if(isset($myvalues['map_role2tp']))
            {
                $default_rolesinproject = $myvalues['map_role2tp'];
            } else {
                if($parent_projectid == NULL)
                {
                    $default_rolesinproject = array();
                } else {
                    $default_rolesinproject = $this->m_oMapHelper->getIDListOfRolesInProject($parent_projectid);
                }
            }
            $options_goal_status = $this->getGoalStatusOptions();
            $options_projmanagers = $this->getProjectManagerOptions();
            $options_member_groups = $this->getMemberGroupOptions();
            $options_member_roles = $this->getMemberRoleOptions();

            $show_unknown_projectcontext = ($formType == 'A');
            $show_only_active_projectcontext = ($formType == 'A' || $formType == 'E');
            $options_projectcontext = $this->getProjectContextOptions($show_unknown_projectcontext, $show_only_active_projectcontext);
            
            $form['hiddenthings']['id'] 
                = array('#type' => 'hidden', '#value' => $id, '#disabled' => FALSE); 
            $form['hiddenthings']['original_root_template_workitemid']
                = array('#type' => 'hidden', '#value' => $root_template_workitemid, '#disabled' => FALSE);        
            $form['hiddenthings']['original_root_workitem_nm']
                = array('#type' => 'hidden', '#value' => $original_root_workitem_nm, '#disabled' => FALSE);        
            $form['hiddenthings']['parent_projectid']
                = array('#type' => 'hidden', '#value' => $parent_projectid, '#disabled' => FALSE);        
            $form['hiddenthings']['active_yn']
                = array('#type' => 'hidden', '#value' => 1, '#disabled' => FALSE);        
            
            $form['data_entry_area1']["blurb"]    = array(
                '#type' => 'item',
                '#prefix' => '<div class="pagetop-blurb">',
                '#markup' => "<p>"
                . t("The project created from template#{$this->m_this_projectid} will be saved with the values you set here.")
                . "</p>",
                '#suffix' => '</div>',
            );
            
            $disable_rootgoalfields = TRUE;
            $form['data_entry_area1']['root_workitem_nm'] = array(
                '#type' => 'textfield',
                '#title' => t('Root Goal Name'),
                '#default_value' => $root_workitem_nm,
                '#size' => 40,
                '#maxlength' => 40,
                '#required' => TRUE,
                '#description' => t('A name for the root goal of the new project.  By convention, the name of the root goal is generally the name of the project.  The root goal name in the template is "' . $original_root_workitem_nm .'".'),
                '#disabled' => FALSE
            );
            
            $form['data_entry_area1']['mission_tx'] = array(
                '#type' => 'textarea',
                '#title' => t('Mission Context Statement '),
                '#default_value' => $mission_tx,
                '#size' => 80,
                '#maxlength' => 1024,
                '#required' => TRUE,
                '#description' => t('The overall mission vision of the project.  A mission context statement should suggest a bigger aspiration area to which successfully completing the root goal of this project can contribute.'),
                '#disabled' => FALSE
            );

            $form['data_entry_area1']['owner_personid'] = array(
                '#type' => 'select',
                '#title' => "<i class='fa fa-male' aria-hidden='true'></i> " . t('Project Leader'),
                '#default_value' => $owner_personid,
                '#options' => $options_projmanagers,
                '#required' => TRUE,
                '#description' => t('Who is directly responsible for the successful completion of this project?'),
                '#disabled' => FALSE
            );
            
            $form['data_entry_area1']['map_group2tp'] = $this->m_oFormHelper->getMultiSelectElement($disabled
                    , 'Member Groups'
                    , $default_groupsinproject
                    , $options_member_groups
                    , TRUE
                    , 'The groups directly contributing to the successful completion of this project'
                );

            $form['data_entry_area1']['map_role2tp'] = $this->m_oFormHelper->getMultiSelectElement($disabled
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
                '#default_value' => 1,
                '#options' => $ynoptions,
                '#description' => t('Yes if project is active, else no.'),
                '#disabled' => TRUE
            );

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
