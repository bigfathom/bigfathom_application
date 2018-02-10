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

module_load_include('php','bigfathom_core','form/ASimpleFormPage');

/**
 * Run report about user history
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class UserLoginHistoryPage extends \bigfathom\ASimpleFormPage
{

    private $m_reftime_ar;
    private $m_oMapHelper;
    
    public function __construct()
    {
        module_load_include('php','bigfathom_core','core/Context');
        module_load_include('php','bigfathom_core','core/MapHelper');
        $this->m_reftime_ar = [];
        $now = time();
        $this->m_reftime_ar['now'] = $now;
        $this->m_reftime_ar['ago1Day'] = $now - 86400;
        $this->m_reftime_ar['ago2Days'] = $now - (2*86400);
        $this->m_reftime_ar['ago5Days'] = $now - (5*86400);
        
        global $user;
        $uah = new \bigfathom\UserAccountHelper();
        $upb = $uah->getUserProfileBundle();
        $this->m_is_systemadmin = $upb['roles']['systemroles']['summary']['is_systemadmin'];
        $this->m_is_systemdatatrustee = $upb['roles']['systemroles']['summary']['is_systemdatatrustee'];
        $this->m_is_systemwriter = $upb['roles']['systemroles']['summary']['is_systemwriter'];
        if(!$this->m_is_systemdatatrustee && !$this->m_is_systemadmin)
        {
            error_log("HACKING WARNING: uid#{$user->uid} attempted to view login information!!!");
            throw new \Exception("Illegal access attempt!");
        }
        $this->m_oMapHelper = new \bigfathom\MapHelper();        
    }
    
    private function getTimestampClassname($login)
    {
        $ago1Day = $this->m_reftime_ar['ago1Day'];
        $ago2Days = $this->m_reftime_ar['ago2Days'];            
        $ago5Days = $this->m_reftime_ar['ago5Days']; 
            
        if($login < $ago5Days)
        {
            $login_classname = "touched-over5daysago";
        } else
        if($login < $ago2Days)
        {
            $login_classname = "touched-over2daysago";
        } else
        if($login < $ago1Day)
        {
            $login_classname = "touched-over1dayago";
        } else 
        if($login > 0)
        {
            $login_classname = "touched-recently";
        } else {
            $login_classname = "touched-never"; 
        } 
        return $login_classname;
    }
    
    private function getDataBundle()
    {
        try
        {
            $sSQL = "select u.uid,u.name,u.created,u.access,u.login,u.status,p.first_nm,p.last_nm,p.id as personid "
                    . " from users u "
                    . " left join bigfathom_person p on u.uid=p.id "
                    . " order by u.access DESC";
            $result = db_query($sSQL);
            return $result;
        
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state, $disabled, $myvalues, $html_classname_overrides=NULL)
    {
        try
        {
            $form["data_entry_area1"] = array(
                '#prefix' => "\n<section class='{$html_classname_overrides['data-entry-area1']}'>\n",
                '#suffix' => "\n</section>\n",
            );
            global $user;
            global $base_url;
            
            $main_tablename = 'grid-workitem-duration';
            $main_table_containername = "container4{$main_tablename}";
            $coremodule_path = drupal_get_path('module', 'bigfathom_core');
            $theme_path = drupal_get_path('theme', 'omega_bigfathom');

            
            //Embed the javascript
            drupal_add_js(array('personid'=>$user->uid
                    ,'myurls' => array('images' => $base_url .'/'. $theme_path.'/images')), 'setting');
            drupal_add_js("$base_url/$theme_path/js/jquery-1.12.1.min.js");
            drupal_add_js("$base_url/$coremodule_path/form/js/BrowserGridHelper.js");
 
            $form["data_entry_area1"]['table_container'] = array(
                '#type' => 'item',
                '#prefix' => '<div id="' . $main_table_containername . '" class="' . $html_classname_overrides['table-container'] . '">',
                '#suffix' => '</div>',
                '#tree' => TRUE,
            );
            
            //Create the static table
            $tableheader = [];
            $tableheader[] = array("ID","The ID of the account in the system","formula");
            $tableheader[] = array("Name","The name of the user","formula");
            if($this->m_is_systemadmin)
            {
                $tableheader[] = array("Type","The type of account","formula");
            }
            $tableheader[] = array("Enabled","Can they login","formula");
            $tableheader[] = array("Created","When the account was created","formula");
            $tableheader[] = array("Login","When this person most recently logged into the system","formula");
            $tableheader[] = array("Access","When this person most recently accessed the system","formula");
            $th_ar = [];
            foreach($tableheader as $th)
            {
                $th_ar[] = "<th title='" . $th[1] . "' datatype='" . $th[2] . "'>" . $th[0] . "</th>";
            }
            $th_markup = "<tr>" . implode("",$th_ar) . "</tr>";
            
            $result = $this->getDataBundle();
            $trows_ar = []; 
            while($record = $result->fetchAssoc())
            {
                $uid = $record['uid'];
                $personid = $record['personid'];
                if($uid > 0 && ($uid == $personid || $this->m_is_systemadmin))
                {
                    $username = $record['name'];
                    if(!empty($record['first_nm']))
                    {
                        $fullname = $record['first_nm'] . " " . $record['last_nm'];
                    } else {
                        $fullname = "No full name available";
                    }
                    if($this->m_is_systemadmin)
                    {
                        if(empty($personid))
                        {
                            $name_markup = "[SORTSTR:$username]<span class='colorful-warning' title='There is NO application record for this account'>$username</span>";
                        } else {
                            $name_markup = "[SORTSTR:$fullname]<span title='logs into system as $username'>$fullname</span>";
                        }
                    } else {
                        $name_markup = "[SORTSTR:$fullname]<span>$fullname</span>";
                    }
                    $aua_markup = (!empty($record['personid'])) ? "[SORTNUM:1]<span style='font-weight:bold'>Application</span>" : "[SORTNUM:2]System";

                    $created = $record['created'];
                    $access = $record['access'];
                    $login = $record['login'];
                    $status = $record['status'];


                    $login_classname = $this->getTimestampClassname($login);
                    $access_classname = $this->getTimestampClassname($access);

                    if(empty($personid))
                    {
                        if($status)
                        {
                            $enabled_class = "colorful-warning";
                        } else {
                            $enabled_class = "colorful-no";
                        }
                    } else {
                        if($status)
                        {
                            $enabled_class = "";
                        } else {
                            $enabled_class = "colorful-notice";
                        }
                    }
                    $enabled_markup = $status ? "[SORTSTR:Y$status$personid]<span class='$enabled_class'>Yes</span>" : "[SORTSTR:N$status$personid]<span class='$enabled_class'>No</span>";
                    $created_markup = "[SORTNUM:{$created}]" 
                            . (gmdate("Y-m-d\TH:i:s\Z", $created));
                    $login_markup = "[SORTNUM:{$login}]<span class='$login_classname'>" 
                            . ($login > 0 ? gmdate("Y-m-d\TH:i:s\Z", $login) : "NEVER") . "</span>";
                    $access_markup = "[SORTNUM:{$access}]<span class='$access_classname'>" 
                            . ($access > 0 ? gmdate("Y-m-d\TH:i:s\Z", $access) : "NEVER") . "</span>";
                    $trows_ar[] = "\n<td>[SORTNUM:{$uid}]<span>$uid</span></td>"
                            . "<td>$name_markup</td>"
                            . ($this->m_is_systemadmin ? "<td>$aua_markup</td>" : "")
                            . "<td>$enabled_markup</td>"
                            . "<td>$created_markup</td>"
                            . "<td>$login_markup</td>"
                            . "<td>$access_markup</td>";
                }
            }
            
            $trows_markup = implode("</tr><tr>", $trows_ar);
            
            $table_markup = '<table id="' . $main_tablename . '" class="browserGrid"><thead>' 
                    . $th_markup 
                    . '</thead><tbody>'
                    . $trows_markup 
                    . '</tbody></table>';
            
            $form["data_entry_area1"]['table_container']['maininfo'] = array('#type' => 'item',
                     '#markup' => $table_markup);

            return $form;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
