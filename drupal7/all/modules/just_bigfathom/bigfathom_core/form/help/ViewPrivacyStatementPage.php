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
 * View privacy statement about the application
 *
 * @author Frank Font
 */
class ViewPrivacyStatementPage extends \bigfathom\ASimpleHelpPage
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

        $revisionid = "PS20170402";
        
        $app_context = "<div class='about-app'>"
                . "<h1>Bigfathom Privacy Statement</h1>"
                . "<p>The Bigfathom application is a tool to help people think bigger through clear understanding and communication and realistic assessments for continuous improvement.<p>"
                . "<p>This tool is currently only marketed to users in the USA and its territories where US laws apply.  It is NOT currently intended for use outside of US laws and jurisdictions.  Please contact Room4me.com Software LLC for licensing information outside of the current supported areas.<p>"
                . "<p>By using this application, you are agreeing to the jurisdiction of US laws in all aspects of its use and that all legal questions to the extent allowed by law will be addressed within the state of Maryland under its laws and legal protocols.</p>"
                . "<p>Do NOT use this application if you are subject to laws governing websites, web services, website data collection polices, software and software product and software services outside of exclusive US governance.<p>"
                . "</div>";
        $main_top = "";
        
        $main_body_ar[] = "<h2>Who Owns Your Data?</h2>"
                . "<p>You own your data.  By uploading your data to this application you are granting Room4me.com Software LLC and its designates the right to operate on that data as necessary to support proper operation of the site, including but not limited to storage of your data to retrieve it for abstract analysis and recovey purposes."
                . "  You are also granting Room4me.com Software LLC the right to retain de-identified data sets derived from your data for business operational purposes."
                . "  Room4me.com Software LLC reserves all rights to de-identifed data sets derived from user data.</p>";
        
        $main_body_ar[] = "<h2>Does Room4me.com Software Sell Your Data?</h2>"
                . "<p>Room4me.com Software LLC respects your privacy and does not collect data for purposes of reselling it.  Room4me.com Software LLC reserves the right to aggregate de-identified data elements derived from user content for purposes of planning and marketing; and where relevant, this may include selling value-added de-identified information derived from user data.</p>";
        
        $main_bottom = "<h2>Additional Information</h2>";

        $main_bottom .= "<h3>Visiting this Website from Outside the United States</h3>"
                . "<p>If you are visiting this Website from outside the United States, please be aware that your information may be transferred to, stored, and processed in the United States where our servers are located and our central database is operated. The information protection laws of the United States might not be as comprehensive or protective as those in your country. By using this Website and our services, you understand that your information may be transferred to our facilities and to third parties as described in this Privacy Policy.</p>";
        $main_bottom .= "<h3>Changes to this Privacy Policy</h3>"
                . "<p>We may update or amend this Privacy Policy at any time. "
                . "This Privacy Policy will reflect the date it was last updated or amended. "
                . "If we make any material amendments we will notify paid subscribers by sending an email to the associated account email at least 72 hours prior to the updated Privacy Policy being published on the Website. "
                . "All amendments will take effect immediately upon our posting of the updated Privacy Policy on this Website. "
                . "Your continued use of this Website (posting content) will indicate your acceptance of the changes to the Privacy Policy.</p>";
        $main_bottom .= "<h3>Contacting Us</h3>"
                . "<p>If you have questions or concerns about this Privacy Policy, our information practices, or wish to make a request regarding your information, please contact us at any of the following:</p>"
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
