<?php
/**
 * @file
 * --------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * --------------------------------------------------------------------------------------
 *
 */

namespace bigfathom;

/**
 * This class helps us create forms
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class FormHelper 
{
    
    public function getValueFromArrayOrAlt($myvalues, $key, $altvalue=NULL)
    {
        if(!empty($myvalues[$key]))
        {
            return $myvalues[$key];
        }
        return $altvalue;
    }
    
    public function getMultiSelectElement($disabled,$title,$default_value,$options,$required=FALSE,$description=NULL)
    {
        try
        {
            if(empty($default_value) || !is_array($default_value))
            {
                //drupal_set_message("$title currently has no members","warning");
                $default_value = [];
            }
            if($disabled)
            {
                $items = array();
                foreach($options as $id=>$value)
                {
                    if(!empty($value))
                    {
                        if(in_array($id, $default_value))
                        {
                            $items[] = $value;
                        }
                    }
                }
                if(count($items)>0)
                {
                    $markup = "<ul><li>".implode('<li>', $items)."</ul>";
                } else {
                    $markup = "NONE";
                }
                $element = array('#type' => 'item'
                        , '#title' => t($title)
                        , '#markup' => $markup
                    );
            } else {
                $element = array(
                    '#type' => 'select', 
                    '#title' => t($title),
                    '#default_value' => $default_value, 
                    '#options' => $options, 
                    '#required' => $required,
                    '#multiple' => TRUE, 
                    '#disabled' => $disabled
                );
            }
            if(!empty($description))
            {
                $element['#description'] = t($description);
            }
            
            return $element;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

