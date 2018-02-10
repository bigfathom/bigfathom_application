<?php
/**
 * @file
 * Template overrides as well as (pre-)process and alter hooks for the
 * omega_bigfathom theme.
 */


function omega_bigfathom_omega_layout_alter(&$layout) 
{
    global $user;
    
    if($user->uid == 0)
    {
        //Not logged in
        $layout = 'bflogin';
    } else {
        $curpath = current_path();
        $onemenuitems = menu_get_item($curpath);
        if(empty($onemenuitems['page_arguments']))
        {
            //No clues available.
            $layout = 'bfsimple';
        } else {
            $pageargs = $onemenuitems['page_arguments'];
            //drupal_set_message("LOOK DEBUG pageargs=" . print_r($pageargs,TRUE));  
            if(isset($pageargs) && is_array($pageargs))
            {
                if(key_exists('layout_name',$pageargs))
                {
                    //The menu has the literal layout name
                    $layout = $pageargs['layout_name'];
                } else {
                    //Figure out the layout from clues
                    if(in_array('menupage',$pageargs))
                    {
                        $layout = 'bfmenupage'; 
                    } else {
                        $layout = 'bfsimple';
                    }
                }
            } else {
                //No clues available.
                $layout = 'bfsimple';
            }
        }
    }
    //drupal_set_message("LOOK DEBUG selected layout=$layout");  
}

function omega_bigfathom_js_alter(&$javascript) 
{
  //We define the path of our new jquery core file
  //assuming we are using the minified version 1.8.3
  $jquery_path = drupal_get_path('theme','omega_bigfathom') . '/js/jquery-1.12.1.min.js';

  //We duplicate the important information from the Drupal one
  $javascript[$jquery_path] = $javascript['misc/jquery.js'];
  //..and we update the information that we care about
  $javascript[$jquery_path]['version'] = '1.12.1';
  $javascript[$jquery_path]['data'] = $jquery_path;

  //Then we remove the Drupal core version
  unset($javascript['misc/jquery.js']);
}