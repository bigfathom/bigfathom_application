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

namespace bigfathom_template_library;

/**
 * Abstract base class
 *
 * @author Frank Font of Room4me.com Software LLC
 */
abstract class ASimpleFormPage
{
    protected $m_projectid      = NULL;
    
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
     * Write the values into the database.  Throw an exception if fails.
     */
    public function updateDatabaseFormState($form, &$form_state)
    {
        $myvalues = $form_state['values'];
        return $this->updateDatabase($form, $myvalues);
    }

    /**
     * @return array of all initial values for the form
     */
    public function getFieldValues()
    {
       return [];
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    abstract public function getForm($form, &$form_state, $disabled, $myvalues_override);

}
