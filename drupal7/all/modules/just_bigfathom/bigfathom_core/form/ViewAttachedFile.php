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

module_load_include('inc', 'bigfathom_core', 'functions/attachments');

/**
 * View an attached file
 *
 * @author Frank Font
 */
class ViewAttachedFile
{

    private $m_attachmentid = NULL;
    private $m_showclose = NULL;

    function __construct($attachmentid, $showclose=FALSE)
    {
        $this->m_attachmentid = $attachmentid;
        $this->m_showclose = $showclose;
    }
    
    /**
     * Returns NULL if no there is no uploaded file.
     */
    private function getUploadedFileDetails($attachmentid)
    {
        try
        {
            //Get database record
            $blob_result = db_select(DatabaseNamesHelper::$m_attachment_tablename,'fa')
                    ->fields('fa')
                    ->condition('id', $attachmentid, '=')
                    ->execute();
            if($blob_result->rowCount() == 1)
            {
                $blob_record = $blob_result->fetchAssoc();    //There will at most be one record.
                $filename = $blob_record['filename'];
                $uri = 'public://'.$filename;
                $url = file_create_url($uri);
                $filepath = drupal_realpath($uri);
                
//drupal_set_message("LOOK stuff uri=$uri filepath=<b>$filepath</b>");                
                if(!file_exists($filepath))
                {
                    //Write the file to the filesystem now
                    $file_blob = $blob_record['file_blob'];
                    if(empty($file_blob))
                    {
                        $mywarning = "Expected to find a file blob for aid#$attachmentid [$filename] but record was empty!";
                        error_log($mywarning);
                        throw new \Exception($mywarning);
                    } else {
                        file_put_contents($filepath, $file_blob);   //Write it to the path.
                    }
                }
            }
        } catch (\Exception $ex) {
            error_log('Failed to extract attached file from database for aid#'.$attachmentid.' because '.$ex->getMessage());
            throw $ex;
        }
        
        $details = array();
        $details['uri'] = $uri;
        $details['url'] = $url;
        $details['filepath'] = $filepath;
        return $details;
    }
    
    /**
     * Get all the form contents for rendering
     * @return type renderable array
     */
    function getForm($form, &$form_state)
    {
        $form["data_entry_area1"] = array(
            '#prefix' => "\n<section class='view-attachment'>\n",
            '#suffix' => "\n</section>\n",
        );
        $form["data_entry_area1"]['table_container'] = array(
            '#type' => 'item', 
            '#prefix' => '<div class="dialog-table-container">',
            '#suffix' => '</div>', 
            '#tree' => TRUE,
        );

        $attachmentid = $this->m_attachmentid;
        $filename = NULL;
        $uploaded_dt = NULL;
        $url = NULL;
        try
        {
            $result = db_select(DatabaseNamesHelper::$m_attachment_tablename,'fa')
                    ->fields('fa')
                    ->condition('id', $attachmentid, '=')
                    ->execute();
            if($result->rowCount() == 1)
            {
                $record = $result->fetchAssoc();    //There will at most be one record.
                $filename = $record['filename'];
                $uploaded_dt = $record['uploaded_dt'];
                $sfdetails = $this->getUploadedFileDetails($attachmentid);
                if($sfdetails !== NULL)
                {
                    $url = $sfdetails['url'];
                }
            }
        } catch (\Exception $ex) {
            error_log('Failed to get attached file information for aid#'.$attachmentid.' because '.$ex->getMessage());
            throw $ex;
        }
        drupal_set_title("File Attachment #$attachmentid $filename");

        $form["data_entry_area1"]['table_container']['heading'] = array('#type' => 'item',
                 '#markup' => '<table class="dialog-table">'
                            . '<tbody>'
                            . '<tr><td>Filename</td><td>'.$filename.'</td></tr>'
                            . '<tr><td>Uploaded Date</td><td>'.$uploaded_dt.'</td></tr>'
                            . '</tbody>'
                            . '</table>');
        
        $imgmarkup = NULL;
        if($url == NULL)
        {
            $imgmarkup = '<p>No file URL found!</p>';
        } else {
            $fileinfo = pathinfo($url);
            if(isset($fileinfo['extension']))
            {
                $ext = strtoupper($fileinfo['extension']);
            } else {
                $ext = NULL;
            }
            if($ext == 'PDF')
            {
                //Handle PDF in special way (http://get.adobe.com/reader/)
                $imgmarkup = '<iframe class="thedoc" src="'.$url.'" width="100%" height="600">'
                        ."\n"
                        .'<!-- A PDF plugin available at http://get.adobe.com/reader/ -->';
            } else if($ext == 'DOC' || $ext == 'DOCX' || $ext == 'RTF') {
                //Handle DOC in special way (http://get.adobe.com/reader/)
                $imgmarkup = '<iframe class="thedoc" src="'.$url.'" width="100%" height="600">';
            } else if($ext == 'PNG' || $ext == 'JPG' || $ext == 'JPEG' || $ext == 'GIF') {
                //Simple image
                $imgmarkup = '<img class="thedoc" src="'.$url.'">';
            } else {
                //TODO
                $imgmarkup = '<img class="thedoc" src="'.$url.'">';
            }
        }
            
        $form["data_entry_area1"]['table_container']['image'] = array('#type' => 'item',
                 '#markup' => $imgmarkup);
        
        if($this->m_showclose)
        {
            //Window close button only works when script created the window.
            $form['data_entry_area1']['action_buttons'] = array(
                '#type' => 'item', 
                '#prefix' => '<div class="container-inline">',
                '#suffix' => '</div>', 
                '#tree' => TRUE,
            );

            $form['data_entry_area1']['action_buttons']['close'] = array('#type' => 'item'
                    , '#markup' => '<a href="#" onclick="self.close();return false;">Close</a>');        
        }

        return $form;
    }
}
