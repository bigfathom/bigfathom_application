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
 *
 */

namespace bigfathom;

require_once 'ASimpleHelpPage.php';

/**
 * View terms of service statement about the application
 *
 * @author Frank Font
 */
class ViewTermsOfServiceStatementPage extends \bigfathom\ASimpleHelpPage
{
    function __construct($urls_arr=NULL)
    {
        if($urls_arr == NULL)
        {
            $urls_arr = [];
        }   
        module_load_include('php','bigfathom_core','core/Context');
        parent::__construct($urls_arr);
    }

    /**
     * Get the values to populate the form.
     */
    function getFieldValues()
    {
        return array();
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form
            , &$form_state
            , $disabled
            , $myvalues
            , $html_classname_overrides=NULL)
    {

        $revisionid = "TOS20170402";
        
        $app_context = "<div class='about-app'>"
                . "<h1>Bigfathom Terms of Service Statement</h1>"
                . "<p>The Bigfathom application is a tool to help people think bigger through clear understanding and communication and realistic assessments for continuous improvement.<p>"
                . "</div>";
        $main_top = "<p>THIS IS CURRENTLY UNFINISHED SOFTWARE.  NO WARRANTY OF FITNESS FOR ANY USE IS OFFERED AT THIS TIME.  USE OF THIS APPLICATION IMPLIES AGREEMENT TO ALL TERMS INCLUDING UNDERSTANDING THAT CHANGES TO THE APPLICATION MAY IMPACT YOUR CURRENT DATA CONTENT.</p>";
        $main_body_ar[] = "<h2>Who Can Use This Site?</h2>"
                . "<p>Authorized users residing in the United States of America or territories where US laws apply exclusively.</p>";
        $main_body_ar[] = "<h2>How Can Users Use This Site?</h2>"
                . "<p>Authorized users can use this site to plan and progress toward their goals by uploading relevant details to help communicate their intentions and status within their team groups.</p>"
                . "<p>The application is intended to be used by human beings via supported browsers.  It is not intended for use by automations and use of automations will be treated as a breach of contract terms.</p>"
                . "<p>Use of this website does not imply rights transfer of any intellecual property rights to the user.</p>";
        $main_body_ar[] = "<h2>Termination</h2>"
                . "<p>Room4me.com Software LLC and its designates reserve the right to terminate service to any user for any abuse or inappropriate use of the software, including but not limited to the following examples:<p>"
                . "<ol>"
                . "<li>Obscene or illegal content posted to the website"
                . "<li>Misappropriation of intellectual property"
                . "<li>Damagaging or disruptive actions by the user"
                . "<li>Breach of any contract terms between the user and Room4me.com Software LLC or its designates"
                . "</ol>"
                . "<p>Room4me.com Software LLC and its designates reserve exclusive right to determine what constitutes an abuse or inappropriate use.<p>";
        
        $main_body_ar[] = "<h2>Declaration of Limitations</h2>"
                . "<p>By using the application user agrees that Room4me.com Software LLC will at no time be liable for more than a refund of the most recent 30 days of fees paid to Room4me.com Software LLC for use or misuse of the application by any parties.</p>"
                . "<p>By using the application, users that have not paid for its use agree that under no circumstances will they seek monetary compensation from Room4me.com Software LLC or its affiliates for real or claimed damages resulting in any way from the use or misuse of the software.</p>";
        
        $main_body_ar[] = "<h2>Application Ownership</h2>"
                . "<p>Room4me.com Software LLC and its contracted partners make this application software available to users as a service.</p>"
                . "<p>Room4me.com Software LLC and its contracted partners reserve all rights to the application software that is being made available to the users.  At no time does use or misuse of this application by any individual person or company imply that any reserved application intellectual property rights or software ownership rights are being conveyed in any way to any parties.</p>";
        
        $main_body_ar[] = "<h2>Governing Law</h2>"
                . "<p>This tool is currently only marketed to users in the USA and its territories where US laws apply.  It is NOT currently intended for use outside of US laws and jurisdictions.  Please contact Room4me.com Software LLC for licensing information outside of the current supported areas.<p>"
                . "<p>By using this application, you are agreeing to the jurisdiction of US laws in all aspects of its use and that all legal questions to the extent allowed by law will be addressed within the state of Maryland under its laws and legal protocols.</p>"
                . "<p>Do NOT use this application if you are subject to laws governing websites, web services, website data collection polices, software and software product and software services outside of exclusive US governance.<p>";
        
        
        $main_bottom = "<h2>Additional Information</h2>";

        $main_bottom .= "<h3>Visiting this Website from Outside the United States</h3>"
                . "<p>If you are visiting this Website from outside the United States, please be aware that your information may be transferred to, stored, and processed in the United States where our servers are located and our central database is operated. The information protection laws of the United States might not be as comprehensive or protective as those in your country. By using this Website and our services, you understand that your information may be transferred to our facilities and to third parties as described in this Privacy Policy.</p>";
        $main_bottom .= "<h3>Changes to this Terms of Use Statement</h3>"
                . "<p>We may update or amend this Terms of Use Statement at any time. "
                . "This Terms of Use Statement will reflect the date it was last updated or amended. "
                . "If we make any material amendments we will notify paid subscribers by sending an email to the associated account email at least 72 hours prior to the updated Privacy Policy being published on the Website. "
                . "All amendments will take effect immediately upon our posting of the updated Privacy Policy on this Website. "
                . "Your continued use of this Website (posting content) will indicate your acceptance of the changes to the Privacy Policy.</p>";
        $main_bottom .= "<h3>Contacting Us</h3>"
                . "<p>If you have questions or concerns about this Terms of Use Statement, our information practices, or wish to make a request regarding your information, please contact us at any of the following:</p>"
                . "<p>Via postal mail: P.O. Box 1014, Olney, MD 20830-1014 USA</p>"
                . "<p>Via email: bigfathom@room4me.com</p>";
        
        $form['data_entry_area1']["app_context"]    = array(
            '#type' => 'item',
            '#markup' => $app_context,
        );
        $form['data_entry_area1']["main_intro"]    = array(
            '#type' => 'item',
            '#markup' => $main_top,
        );
        
        $main_body_markup = implode("\n",$main_body_ar);
        $form['data_entry_area1']["main_body"]    = array(
            '#type' => 'item',
            '#markup' => $main_body_markup,
        );
        
        $form['data_entry_area1']["main_bottom"]    = array(
            '#type' => 'item',
            '#markup' => $main_bottom,
        );
        
        $form['data_entry_area1']["revisioninfo"]    = array(
            '#type' => 'item',
            '#markup' => "<span title='revison identifier' class='small-text'>$revisionid</span>",
        );

        return $form;
    }
}
