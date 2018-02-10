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
 * This class helps us create markup
 *
 * @author Frank Font of Room4me.com Software LLC
 */
class MarkupHelper 
{
    
    public static function getOneTableHeaderTextCell($label, $tooltip=NULL, $datatype=NULL, $colname=NULL, $class_attr_txt="nowrap")
    {
        if(empty($label))
        {
            throw new Exception("Missing required label!");
        }
        if(empty($tooltip))
        {
            $ttmarkup = "";
        } else {
            $ttmarkup = " title='" . t($tooltip) . "' ";
        }
        if(empty($colname))
        {
            $cnmarkup = "";
        } else {
            $cnmarkup = " colname='$colname' ";
        }
        if(empty($datatype))
        {
            $dtmarkup = "";
        } else {
            $dtmarkup = " datatype='$datatype' ";
        }
        $markup = "<th{$cnmarkup}{$dtmarkup} class='$class_attr_txt'><span{$ttmarkup}>" . t($label) . "</span></th>";
        return $markup;
    }

    public static function getOneTableHeaderImgCell($imgurl, $imgalt, $label, $tooltip=NULL, $datatype=NULL, $colname=NULL, $class_attr_txt="nowrap")
    {
        $imgmarkup = "<img alt='$imgalt' src='$imgurl' />";
        if(empty($imgurl))
        {
            throw new Exception("Missing required imgurl!");
        }
        if(empty($imgalt))
        {
            throw new Exception("Missing required imgalt!");
        }
        if(empty($tooltip))
        {
            $ttmarkup = "";
        } else {
            $ttmarkup = " title='" . t($tooltip) . "' ";
        }
        if(empty($label))
        {
            $lblmarkup = "";
        } else {
            $lblmarkup = t($label);
        }
        if(empty($colname))
        {
            $cnmarkup = "";
        } else {
            $cnmarkup = " colname='$colname' ";
        }
        if(empty($datatype))
        {
            $dtmarkup = "";
        } else {
            $dtmarkup = " datatype='$datatype' ";
        }
        $markup = "<th{$cnmarkup}{$dtmarkup} class='$class_attr_txt'><span{$ttmarkup}>{$imgmarkup}{$label}</span></th>";
        return $markup;
    }
    
    public static function getTableHeaderRow($headers_ar)
    {
        return "<thead><tr>\n".implode(" ", $headers_ar)."\n</tr></thead>";
    }

    public static function getTableMarkup($id,$class_attr_txt,$headers_ar,$rows_ar=NULL)
    {
        if(empty($id))
        {
            $markupid = "";
        } else {
            $markupid = " id='$id'";
        }
        if(empty($class_attr_txt))
        {
            $markupclass = "";
        } else {
            $markupclass = " class='$class_attr_txt' ";
        }
        if(empty($headers_ar) || !is_array($headers_ar))
        {
            throw new \Exception("Missing required headers!");
        }
        if(empty($rows_ar) || !is_array($rows_ar))
        {
            $rows_txt = "";
        } else {
            $rows_txt = implode("\n", $rows_ar);
        }
        return "<table {$markupid}{$markupclass}>\n" 
                . self::getTableHeaderRow($headers_ar) 
                . "\n<tbody>"
                . "\n{$rows_txt}"
                . "\n</tbody>"
                . "\n</table>";
    }
    
    public static function getAddressMarkup($address_line1,$address_line2,$city_tx,$state_abbr,$country_abbr
            ,$title_tx = NULL
            ,$delimiter='<br>')
    {
        try
        {
            $address_lines_ar = array();
            if($address_line1 > '')
            {
                $address_lines_ar[] = $address_line1;
            }
            if($address_line2 > '')
            {
                $address_lines_ar[] = $address_line2;
            }
            if($city_tx > '')
            {
                if($state_abbr > '')
                {
                    $address_lines_ar[] = "$city_tx, $state_abbr";
                } else {
                    $address_lines_ar[] = $city_tx;
                }
            } else {
                if($state_abbr > '')
                {
                    $address_lines_ar[] = $state_abbr;
                }
            }
            if($country_abbr > '')
            {
                $address_lines_ar[] = $country_abbr;
            }
            $address_lines_markup = implode($delimiter, $address_lines_ar);
            if($title_tx != NULL)
            {
                return "<span title='$title_tx'>$address_lines_markup</span>";
            }
            return $address_lines_markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getStatusCodeMarkupBundle($status_record)
    {
        try
        {
            $bundle = [];
            $status_cd = !empty($status_record['code']) ? $status_record['code'] : $status_record['status_cd'];
            if(empty($status_cd))
            {
                $bundle['status_code'] = NULL;
                $bundle['terminal_yesno'] = NULL;
            } else {
                $status_title_tx = isset($status_record['title_tx']) ? $status_record['title_tx'] : NULL;
                $status_happy_yn = $status_record['happy_yn'];
                $status_terminal_yn = $status_record['terminal_yn'];
                $status_needstesting_yn = isset($status_record['needstesting_yn']) ? $status_record['needstesting_yn'] : NULL;
                $status_soft_delete_yn = isset($status_record['soft_delete_yn']) ? $status_record['soft_delete_yn'] : NULL;
                $status_default_ignore_effort_yn = isset($status_record['default_ignore_effort_yn']) ? $status_record['default_ignore_effort_yn'] : NULL;
                if($status_terminal_yn == '1')
                {
                    if($status_happy_yn !== NULL)
                    {
                        if($status_happy_yn == '1')
                        {
                            $status_classname = "status-terminal-happy-yes";
                        } else {
                            $status_classname = "status-terminal-happy-no";
                        } 
                    } else {
                        if($status_soft_delete_yn == '1')
                        {
                            $status_classname = "status-soft-delete";
                        } else {
                            $status_classname = "status-terminal";
                        }
                    }
                } else {
                    if($status_default_ignore_effort_yn === NULL)
                    {
                        if($status_happy_yn == '1')
                        {
                            $status_classname = "status-happy-yes";
                        } else {
                            if($status_happy_yn === 0 || $status_happy_yn === '0')
                            {
                                $status_classname = "status-happy-no";
                            } else {
                                $status_classname = "";
                            }
                        } 
                    } else {
                        if($status_default_ignore_effort_yn)
                        {
                            $status_classname = "status-ignore-effort";
                        } else {
                            if($status_happy_yn !== NULL)
                            {
                                if($status_happy_yn == '1')
                                {
                                    $status_classname = "status-happy-yes";
                                } else {
                                    $status_classname = "status-happy-no";
                                } 
                            } else {
                                $status_classname = "status-ambiguous";
                            }
                            if($status_needstesting_yn)
                            {
                                $status_classname .= " status-needstesting";
                            }
                        }
                    }
                }
                $status_markup = "<span class='$status_classname' title='$status_title_tx'>$status_cd</span>";
                $terminalyesno = ($status_terminal_yn == 1 ? 'Yes' : '<span class="colorful-available">No</span>');
            }
            $bundle['status_code'] = $status_markup;
            $bundle['terminal_yesno'] = $terminalyesno;
            return $bundle;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getImportanceValueMarkup($importance_value)
    {
        try
        {
            if($importance_value <= 10)
            {
                $markup = "<span class='item-not-important'>$importance_value</span>";
            } else if($importance_value >= 90) {
                $markup = "<span class='item-important'>$importance_value</span>";
            } else {
                $markup = "$importance_value";
            }
            return $markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getPercentCompleteMarkup($percent_complete_value,$tip_tx=NULL)
    {
        try
        {
            if($percent_complete_value <= 10)
            {
                $markup = "<span title='$tip_tx' class='item-far-from-complete'>$percent_complete_value</span>";
            } else if($percent_complete_value == 100) {
                if($tip_tx === NULL)
                {
                    $tip_tx = 'done';
                }
                $markup = "<span title='$tip_tx' class='item-complete'>$percent_complete_value</span>";
            } else if($percent_complete_value >= 95) {
                $markup = "<span title='$tip_tx' class='item-very-near-complete'>$percent_complete_value</span>";
            } else if($percent_complete_value >= 90) {
                $markup = "<span title='$tip_tx' class='item-near-complete'>$percent_complete_value</span>";
            } else {
                if($tip_tx === NULL)
                {
                    //No tooltip
                    $markup = "$percent_complete_value";
                } else {
                    $markup = "<span title='$tip_tx'>$percent_complete_value</span>";
                }
            }
            return $markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getPercentTestableMarkup($total_done,$total_done_or_testable,$total_wids,$tip_tx=NULL)
    {
        try
        {
            if($total_wids == 0)
            {
                if($tip_tx === NULL)
                {
                    $tip_tx = "No workitems";
                }
                $markup = "<span title='$tip_tx'>NA</span>";
            } else {
                $percent_done = round(100 * $total_done/$total_wids);
                $percent_done_or_testable = round(100 * $total_done_or_testable/$total_wids);

                $display_suffix = '';
                if($tip_tx === NULL)
                {
                    if($total_done === $total_done_or_testable)
                    {
                        $tip_tx = "$total_done of the $total_wids workitems marked done";
                    } else {
                        $diff = $total_done_or_testable - $total_done;
                        //$diff_pct = round(100 * $diff / $total_done);
                        $tip_tx = "$diff of the $total_wids workitems are in failed status";
                        $display_suffix = " ($diff failed)";
                    }
                }
                if($percent_done <= 10)
                {
                    $markup = "<span title='$tip_tx' class='item-far-from-complete'>$percent_done{$display_suffix}</span>";
                } else if($percent_done == 100) {
                    if($tip_tx === NULL)
                    {
                        $tip_tx = 'done';
                    }
                    $markup = "<span title='$tip_tx' class='item-complete'>$percent_done{$display_suffix}</span>";
                } else if($percent_done >= 95) {
                    $markup = "<span title='$tip_tx' class='item-very-near-complete'>$percent_done{$display_suffix}</span>";
                } else if($percent_done >= 90) {
                    $markup = "<span title='$tip_tx' class='item-near-complete'>$percent_done{$display_suffix}</span>";
                } else {
                    if(empty($tip_tx))
                    {
                        //No tooltip
                        $markup = "$percent_done{$display_suffix}";
                    } else {
                        $markup = "<span title='$tip_tx'>$percent_done{$display_suffix}</span>";
                    }
                }
            }
            return $markup;
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public static function getCommLinkMarkupBundle($communicate_page_url, $map_comm_summary, $contextitemidvalue)
    {
        try
        {
            $comm_empty_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_empty');
            $comm_content_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_hascontent');
            $comm_action_high_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_high');
            $comm_action_medium_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_medium');
            $comm_action_low_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_low');
            $comm_action_closed_icon_url = \bigfathom\UtilityGeneralFormulas::getIconURLForPurposeName('communicate_action_closed');
            
            $bundle = [];
            if(empty($map_comm_summary['all_active']) || $map_comm_summary['all_active'] == 0)
            {
                if(!isset($map_comm_summary['all_active']))
                {
                    $comm_score = 0;
                } else {
                    $comm_score = $map_comm_summary['count_by_thread_summary_status']['closed']['arcl'] 
                            + $map_comm_summary['count_by_thread_summary_status']['closed']['arcm']
                            + $map_comm_summary['count_by_thread_summary_status']['closed']['arch'];
                }
                $all_count = 0;
                if($comm_score == 0)
                {
                    //Nothing closed either
                    $sCommIconElement = "<img src='$comm_empty_icon_url'/></a>";
                    $sCommentTipText = "jump to communications for #{$contextitemidvalue} (zero open action items)";
                } else {
                    //We have closed threads
                    $sCommIconElement = "<img src='$comm_action_closed_icon_url'/></a>";
                    $sCommentTipText = "jump to communications for #{$contextitemidvalue} (zero open action items; one or more closed threads)";
                }
            } else {
                $all_count = $map_comm_summary['all_active'];
                $info_count = $map_comm_summary['total_info_count'];
                $open_actionreq_count = $map_comm_summary['total_open_action_request_count'];
                $comm_score = $map_comm_summary['total_info_count'];
                $sCommIconElement = "<img src='$comm_content_icon_url'/></a>";
                if($map_comm_summary['count_by_thread_summary_status']['open']['arch']>0)
                {
                    $comm_score += 3000 + $map_comm_summary['count_by_thread_summary_status']['open']['arch'];
                    $sCommIconElement = "<img src='$comm_action_high_icon_url'/></a>";
                } else
                if($map_comm_summary['count_by_thread_summary_status']['open']['arcm']>0)
                {
                    $comm_score += 2000 + $map_comm_summary['count_by_thread_summary_status']['open']['arcm'];
                    $sCommIconElement = "<img src='$comm_action_medium_icon_url'/></a>";
                } else
                if($map_comm_summary['count_by_thread_summary_status']['open']['arcl']>0)
                {
                    $comm_score += 1000 + $map_comm_summary['count_by_thread_summary_status']['open']['arcl'];
                    $sCommIconElement = "<img src='$comm_action_low_icon_url'/></a>";
                }
                if($info_count === 1)
                {
                    $info_txt_chunk = "1 comment";
                } else {
                    $info_txt_chunk = "$info_count comments";
                }
                if($open_actionreq_count === 1)
                {
                    $open_actionre_txt_chunk = "1 action request";
                } else {
                    $open_actionre_txt_chunk = "$open_actionreq_count action requests";
                }
                if($info_count == 0)
                {
                    $sCommentTipText = "jump to communications for #{$contextitemidvalue} (" . $open_actionreq_count . " action requests)";
                } else {
                    $sCommentTipText = "jump to communications for #{$contextitemidvalue} ($open_actionre_txt_chunk and $info_txt_chunk)";
                }
            }
            $sCommentsMarkup = "<span class=''><a class='' title='$sCommentTipText' href='$communicate_page_url'>" . $sCommIconElement . "</a></span>";
            
            $bundle['comm_score'] = $comm_score;
            $bundle['markup'] = $sCommentsMarkup;
            
            return $bundle;
                    
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
}

