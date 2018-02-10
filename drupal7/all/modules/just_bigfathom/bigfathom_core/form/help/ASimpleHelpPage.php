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
 * Abstract base class
 *
 * @author Frank Font of Room4me.com Software LLC
 */
abstract class ASimpleHelpPage
{
    protected $m_projectid      = NULL;
    protected $m_urls_arr       = NULL;
    protected $m_oSnippetHelper = NULL;
    
    function __construct($urls_arr=NULL)
    {
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }   
        $this->m_urls_arr = $urls_arr;
        if(!isset($urls_arr['return']))
        {
            $this->m_urls_arr['return'] = 'bigfathom/help';
        }
        module_load_include('php','bigfathom_core','snippets/SnippetHelper');
        $this->m_oSnippetHelper = new \bigfathom\SnippetHelper();
        
        global $user;
        global $base_url;

        $module_path = drupal_get_path('module', 'bigfathom_core');
        $theme_path = drupal_get_path('theme', 'omega_bigfathom');
        drupal_add_js(array('personid'=>$user->uid
                ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
        drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
        drupal_add_js("$base_url/$module_path/form/js/dialog/MyImageBox.js");
        
    }
    
    public function getProjectDependentMarkup($label, $path)
    {
        $markup = '';
        if(isset($_SESSION['selected_projectid']) && $_SESSION['selected_projectid'] != '')
        {
            $markup = l($label, $path);
        } else {
            $markup = "<strong>$label</strong>";
        }
        return $markup;
    }
    
    /**
     * Use form state to validate the form.
     * Return TRUE if valid, FALSE if not valid.
     */
    public function looksValidFormState($form, &$form_state)
    {
        $myvalues = $form_state['values'];
        return $this->looksValid($form, $myvalues);
    }    
    
    /**
     * Simply validate the values provided
     * Return TRUE if valid, FALSE if not valid.
     */
    public function looksValid($form, $myvalues)
    {
        throw new \Exception("Not implemented!");
    }    
    
    /**
     * Write the values into the database.  Throw an exception if fails.
     */
    public function updateDatabase($form, $myvalues)
    {
        throw new \Exception("Not implemented!");
    }

    /**
     * @return array of all initial values for the form
     */
    public function getFieldValues()
    {
       return array();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract public function getForm($form, &$form_state, $disabled, $myvalues_override);

    /**
     * Return a DRUPAL API constructed help page form
     */
    protected function populateFormElements(&$form, $help_page_title, $help_page_blurb, $main_body_markup)
    {
        $form = [];

        $app_context = "<div class='about-app'>"
                . "<h1>$help_page_title</h1>"
                . "</div>";
        
        $form['data_entry_area1']["app_context"]    = array(
            '#type' => 'item',
            '#markup' => $app_context,
        );

        $form['data_entry_area1']["main_intro"]    = array(
            '#type' => 'item',
            '#markup' => '<p class="helpblurb">'
            . $help_page_blurb
            . '</p>',
        );
        
        $form['data_entry_area1']["main_body"]    = array(
            '#type' => 'item',
            '#markup' => $main_body_markup,
        );
        
        $this->populateFormBottomElements($form);
        return $form;
    }
    
    /**
     * Return a DRUPAL API constructed help page form
     */
    protected function populateFormBottomElements(&$form)
    {
        //Add the action buttons.
        $form['data_entry_area1']['action_buttons']           = array(
            '#type' => 'item',
            '#prefix' => 
                '<div>',
            '#suffix' => '</div>',
            '#tree' => TRUE
        );
        if(isset($this->m_urls_arr['return']))
        {
            $rparams_ar = [];
            $exit_link_markup = l('Exit',$this->m_urls_arr['return']
                            , array('query' => $rparams_ar, 'attributes'=>array('class'=>'action-button'))
                    );
            $form['data_entry_area1']['action_buttons']['return'] = array('#type' => 'item'
                    , '#markup' => $exit_link_markup);
        }
        
        $snippet_popup_divs = [];
        $snippet_popup_divs[] = array('dialog-zoom-image'
            ,'Zoom Image'
            ,$this->m_oSnippetHelper->getHtmlSnippet("popup_zoom_image"));            
        foreach($snippet_popup_divs as $detail)
        {
            $id = $detail[0];
            $title = $detail[1];
            $markup = $detail[2];
            $form["formarea1"]['popupdefs'][$id] = array('#type' => 'item'
                    , '#prefix' => '<div id="' . $id . '" title="' . $title . '" class="popupdef">'
                    , '#markup' => $markup
                    , '#suffix' => '</div>'
                );            
        }
        
        return $form;
    }
}
