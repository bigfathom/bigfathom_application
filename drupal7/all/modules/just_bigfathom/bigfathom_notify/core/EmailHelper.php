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

namespace bigfathom_notify;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Help with user email interactions
 * 
 * @author Frank Font of Room4me.com Software LLC
 */
class EmailHelper
{
    private $m_foldername = "PHPMailer";
    private $m_oContext = NULL;
    private $m_debug = FALSE;
    
    public function __construct()
    {
        $this->m_oContext = \bigfathom\Context::getInstance();

        $sLibraryPath = DRUPAL_ROOT . "/sites/all/libraries";
        
        require_once "$sLibraryPath/{$this->m_foldername}/src/Exception.php";
        require_once "$sLibraryPath/{$this->m_foldername}/src/PHPMailer.php";
        require_once "$sLibraryPath/{$this->m_foldername}/src/SMTP.php";
    }
    
    public function setDebug($yn=TRUE)
    {
        $this->m_debug = $yn;
    }
    
    public function sendNotification($post_fields)
    {
        try
        {
            $method=BIGFATHOM_NOTIFY_METHOD;
            $versioninfo = "module v" . BIGFATHOM_NOTIFY_VERSION_INFO;

            $default_mailto_raw = "xxxxxxxxxxxxxxxxxxxxxxxxx";
            $default_mailfrom = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
            
            if(empty($post_fields['mailfrom']))
            {
                $post_fields['mailfrom'] = $default_mailfrom;
            }
            
            if(empty($post_fields['mailto']))
            {
                $post_fields['mailto'] = $default_mailto_raw;
            }
            
            if(empty($post_fields['subject']))
            {
                $post_fields['subject'] = "BLANK SUBJECT";
            }
            
            if(empty($post_fields['message']))
            {
                $post_fields['message'] = "BLANK MESSAGE BODY: No message body provided by sender.";
                $post_fields['message_html'] = "<h1>BLANK MESSAGE BODY</h1>No message body provided by sender.";
            }
            
            if($method=='TALKECHO')
            {
                $this->sendNotificationTALKECHO($post_fields);
            } else {
                $this->sendNotificationSMTP($post_fields);
            }

        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function sendTestNotification($override_post_fields=NULL)
    {
        try
        {
            if($override_post_fields === NULL)
            {
                $override_post_fields = [];
            }
            $method=BIGFATHOM_NOTIFY_METHOD;
            $versioninfo = "module v" . BIGFATHOM_NOTIFY_VERSION_INFO;

            echo "<h1>LOOK sendTestNotification $method $versioninfo</h1>";

            $subject = "TEST EMAIL SUBJECT";
            $message_text = "BODY OF TEST MESSAGE";
            $message_html = "BODY OF <strong>TEST</strong> MESSAGE";
            $mailto_raw = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
            $mailfrom = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
            
            $post_fields = [];
            $post_fields['mailfrom'] = $mailfrom;
            $post_fields['mailto'] = $mailto_raw;
            $post_fields['subject'] = $subject;
            $post_fields['message'] = $message_text;
            $post_fields['message_html'] = $message_html;
            
            foreach($override_post_fields as $key=>$value)
            {
                echo "<p>Applying override of '$key' with value ".print_r($value,TRUE)."<p>";
                $post_fields[$key] = $value;
            }
            
            $this->sendNotification($post_fields);
            
            echo "<h1>LOOK sendTestNotification $method BOTTOM</h1>";
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function sendNotificationTALKECHO($raw_post_fields)
    {
        try
        {
            $versioninfo = "module v" . BIGFATHOM_NOTIFY_VERSION_INFO;
            if($this->m_debug)
            {
                echo "<h1>LOOK sendNotificationTALKECHO TOP $versioninfo</h1>";
            }
            
            //$raw_post_fields['subject'] = "T4TALKECHO " . $raw_post_fields['subject'];
            $raw_post_fields['source_code'] = "TALKECHO";
            
            $possible_arrays = array('mailto','cc','bcc');
            foreach($possible_arrays as $key)
            {
                if(!empty($raw_post_fields[$key]))
                {
                    if(is_array($raw_post_fields[$key]))
                    {
                       $raw_post_fields[$key] = json_encode($raw_post_fields[$key]);
                    }
                }
            }
            
            $url = BIGFATHOM_NOTIFY_TALKECHO_URL;//"http://talkecho.com/sendmessage.php";
            $useragent='cURL';
            $follow_redirects=FALSE;
            $debug=FALSE;
                    
            $result = $this->m_oContext->postContentsToURL($url
                                        , $raw_post_fields
                                        , $useragent
                                        , $follow_redirects
                                        , $debug);
            
            if($this->m_debug)
            {
                echo "<h1>LOOK sendNotificationTALKECHO BOTTOM</h1>";
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    private function sendNotificationSMTP($raw_post_fields)
    {
        try
        {
            
            $versioninfo = "module v" . BIGFATHOM_NOTIFY_VERSION_INFO;

            if($this->m_debug)
            {
                echo "<h1>LOOK sendNotificationSMTP TOP $versioninfo</h1>";
            }
            
            $clean_input = [];
            
            try
            {
                    $required_fields = array("mailto","subject","message");
                    foreach($required_fields as $fieldname)
                    {
                            if(empty($_GET[$fieldname]))
                            {
                                    throw new Exception("Missing required $fieldname");
                            }
                            $clean_input[$fieldname]=urldecode($_GET[$fieldname]);
                    }

            } 
            catch(Exception $ex)
            {
                    echo "FAILED TOP with $ex";
                    throw $ex;
            }	

            $clean_input['subject'] = BIGFATHOM_NOTIFY_SUBJECT_PREFIX . $clean_input['subject'];
            $clean_input['source_code'] = "DIRECT";
            
            if($this->m_debug)
            {
                echo "<BR>clean input=" . print_r($clean_input,TRUE);
                echo "<h1>LOOK TESTING MID</h1>";
            }
            
            
            $mailto_raw = $clean_input['mailto'];
            $subject = $clean_input['subject'] . " $versioninfo";
            $message = $clean_input['message']; //. "\r\nThis is a test email";

            
            $subject = $raw_post_fields['subject'];
            $message = $raw_post_fields['message'];
            $mailto_raw = $raw_post_fields['mailto'];
            
            if(!is_array($mailto_raw))
            {
                $mailto_ar = array($mailto_raw);
            } else {
                $mailto_ar = $mailto_raw;
            }
            
            $mail = new PHPMailer(true);   // Passing `true` enables exceptions
            try 
            {
                //Server settings
                $mail->SMTPDebug = 2;                                 // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = 'smtp.fatcow.com';                      // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = 'xxxxxxxxxxxxxxxxxxxxxx';           // SMTP username
                $mail->Password = 'xxxxxxxxxxxxxxxxxxxxxx';               // SMTP password
                $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'somebody');
                foreach($mailto_ar as $one_address)
                {
                        $mail->addAddress($one_address);     // Add a recipient
                }

                $mail->addReplyTo('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', 'Bigfathom Event Notifier');
                //$mail->addCC('cc@example.com');
                //$mail->addBCC('bcc@example.com');

                //Attachments
                //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

                $time = time();
                $today_DATE_AND_TIME = date("Y-m-d h:i:s", $time);
                $message_footer = "NOTE: You are receiving this message because it was requested by a user at $today_DATE_AND_TIME "
                        . "to be sent "
                        . "to your email address."
                        . "  If you believe this is in error, please contact support@bigfathom.com for assistance.";   
                
                $text_message = $message;
                $html_message = $message;

                $html_message .= "</br><p><small>$message_footer</small></p>";
                $text_message .= "\r\n\r\n$message_footer";
    
                //Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = $subject;//'Here is the subject';
                $mail->Body    = $html_message;//'This is the HTML message body <b>in bold!</b>';
                $mail->AltBody = $text_message;//'This is the body in plain text for non-HTML mail clients';

                $mail->send();
                if($this->m_debug)
                {
                    echo 'Message has been sent';
                }
            } catch (Exception $e) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }

            if($this->m_debug)
            {
                echo "<h1>LOOK sendNotificationSMTP BOTTOM</h1>";
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}

