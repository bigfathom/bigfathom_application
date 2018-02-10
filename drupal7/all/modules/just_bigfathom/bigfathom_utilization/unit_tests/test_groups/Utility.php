<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace bigfathom_utilization;

/**
 * Description of Utility
 *
 * @author Frank Font
 */
class Utility
{
    private $m_oContext = NULL;
    private $m_oMapHelper = NULL;
    private $m_oUAH = NULL;
    private $m_oWriteHelper = NULL;
    private $m_aExistingProjects = NULL;
    
    private $m_ourtestprojectbundle = NULL;

    public function __construct($oContext, $oMapHelper, $oUAH, $oWriteHelper)
    {
        $this->m_oContext = $oContext;
        $this->m_oMapHelper = $oMapHelper;
        $this->m_oUAH = $oUAH;
        $this->m_oWriteHelper = $oWriteHelper;
    }

    public function checkDailyDetail($daily_detail_bundle, $start_dt, $end_dt, $projects2ignore, $map_personid)
    {
        try
        {
            if(empty($daily_detail_bundle))
            {
                throw new \Exception("Empty getAllDetailBundle for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
            }
            $daily_detail = $daily_detail_bundle['daily_detail'];
            if(empty($daily_detail))
            {
                throw new \Exception("Empty bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
            }
            if(empty($daily_detail[$start_dt]))
            {
                throw new \Exception("Missing $start_dt in bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
            }
            if(empty($daily_detail[$end_dt]))
            {
                throw new \Exception("Missing $end_dt in bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
            }
            $prev_date = NULL;
            foreach($daily_detail as $onedate=>$detail)
            {
                if($onedate <= $prev_date)
                {
                    throw new \Exception("Sequence error daily_detail $onedate before $prev_date in bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
                }
                if(!array_key_exists('isholiday', $detail))
                {
                    throw new \Exception("Missing isholiday in daily_detail@$onedate in bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
                }
                if(!array_key_exists('isweekday', $detail))
                {
                    throw new \Exception("Missing isweekday in daily_detail@$onedate in bundle['daily_detail'] for [$start_dt,$end_dt]@projects2ignore=" . print_r($projects2ignore,TRUE) . " and map_personid=" . print_r($map_personid,TRUE));
                }
                $prev_date = $onedate;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function checkFitInsightBundleContent($fit_bundle, $winfo, $personid_override)
    {
        try
        {
            if(empty($fit_bundle))
            {
                throw new \Exception("Empty fit bundle for person#$personid_override");
            }
            if(empty($fit_bundle['metadata']))
            {
                throw new \Exception("Empty fit bundle metadata for person#$personid_override");
            }
            if(empty($fit_bundle['found_fit_yn']))
            {
                throw new \Exception("Empty fit bundle found_fit_yn for person#$personid_override");
            }
            if(empty($fit_bundle['daterange']))
            {
                throw new \Exception("Empty fit bundle daterange for person#$personid_override");
            }
            if(empty($fit_bundle['date2hours']))
            {
                throw new \Exception("Empty fit bundle date2hours for person#$personid_override");
            }
            if($fit_bundle['found_fit_yn'])
            {
                //Confirm we found a fit
                $ourtotal = 0;
                $prev_dt = NULL;
                $first_dt = NULL;
                $last_dt = NULL;
                foreach($fit_bundle['date2hours'] as $onedate=>$onehours)
                {
                    if($prev_dt >= $onedate)
                    {
                        throw new \Exception("Dates out of order in date2hours of " . print_r($fit_bundle,TRUE));
                    }
                    $ourtotal += $onehours;
                    if(empty($first_dt))
                    {
                        $first_dt = $onedate;
                    }
                    $last_dt = $onedate;
                }
                if($first_dt != $fit_bundle['daterange']['sdt'])
                {
                    throw new \Exception("First date $first_dt not as expected in daterange of " . print_r($fit_bundle,TRUE));
                }
                if($last_dt != $fit_bundle['daterange']['edt'])
                {
                    throw new \Exception("Last date $last_dt not as expected in daterange of " . print_r($fit_bundle,TRUE));
                }
                if(round($ourtotal) != round($winfo['reh']))
                {
                    throw new \Exception("Total does not match! ($ourtotal != " . $winfo['reh'] . ")");
                }
            }
                
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function checkExpectedWinfoValues($person2expected_values, $oMasterDailyDetail)
    {
        try
        {
            $badthings_ar = [];
            
            //Check the records
            $person2all_altered_winfo_ar = [];
            $personid_ar = array_keys($person2expected_values);
            foreach($personid_ar as $personid)
            {
                
                $expected_total_reh = $person2expected_values[$personid]['total_reh'];                
                
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                $person2all_altered_winfo_ar[$personid]['all_winfo'] = [];
                $person2all_altered_winfo_ar[$personid]['summary'] = [];
                $person2all_altered_winfo_ar[$personid]['summary']['total_reh'] = 0;
                foreach($all_wids as $wid)
                {
                    $expected_winfo = $person2expected_values[$personid]['all_winfo'][$wid];
                    if(empty($person2expected_values[$personid]['all_winfo'][$wid]) || !is_array($person2expected_values[$personid]['all_winfo'][$wid]))
                    {
                        $badthings_ar[] = "Expected array of wid#$wid winfo  at all_winfo for person#$personid but instead got " . print_r($person2expected_values[$personid],TRUE);
                    }
                    
                    $record = $oPAI->getRelevantWorkitemRecord($wid);
                    $owner_personid = $record['owner_personid'];
                    if($owner_personid !== $personid)
                    {
                        $badthings_ar[] = "Expected owner of wid#$wid as person#$personid but instead owned by person#$owner_personid";
                    }
                    if($expected_winfo['sdt'] != $record['sdt'])
                    {
                        if(empty($expected_winfo['sdt']))
                        {
                            throw new \Exception("EMPTY sdt in expected ['all_winfo'][$wid] of person#$personid " . print_r($person2expected_values,TRUE));
                        }
                        $badthings_ar[] = "Expected sdt of wid#$wid as '{$expected_winfo['sdt']}' but instead found '{$record['sdt']}'";
                    }
                    if($expected_winfo['edt'] != $record['edt'])
                    {
                        $badthings_ar[] = "Expected edt of wid#$wid as '{$expected_winfo['edt']}' but instead found '{$record['edt']}'";
                    }
                    if($expected_winfo['reh'] != $record['reh'])
                    {
                        $badthings_ar[] = "Expected reh of wid#$wid as {$expected_winfo['reh']} but instead found {$record['reh']}";
                    }
                    $person2all_altered_winfo_ar[$personid]['all_winfo'][$wid] = $record;
                    $person2all_altered_winfo_ar[$personid]['summary']['total_reh'] += $record['reh'];
                }
            }
            
            //Check for expected totals
            foreach($personid_ar as $personid)
            {
                $expected_total_reh = $person2expected_values[$personid]['total_reh'];
                if($expected_total_reh != $person2all_altered_winfo_ar[$personid]['summary']['total_reh'])
                {
                    $badthings_ar[] = "Expected total reh of $expected_total_reh but for person#$personid we have " . $person2all_altered_winfo_ar[$personid]['summary']['total_reh'];
                }
            }
            
            if(count($badthings_ar)>0)
            {
                $badthings_tx = implode(" and ", $badthings_ar);
                throw new \Exception($badthings_tx);
            }
            
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
    
    public function checkAllRelevantWinfo($oMasterDailyDetail)
    {
        try
        {
            $local_wid2winfo = [];
            $all_personid = $oMasterDailyDetail->getAllPersonIDs();
            foreach($all_personid as $personid)
            {
                $oPAI = $oMasterDailyDetail->getOnePersonAvailabilityInsightInstance($personid);
                $all_wids = $oPAI->getRelevantWorkitemRecordIDs();
                foreach($all_wids as $wid)
                {
                    $winfo = $oMasterDailyDetail->getRelevantWorkitemRecord($wid);
                    $local_wid2winfo[$wid]['MASTER'] = $winfo;
                }
                foreach($all_wids as $wid)
                {
                    $winfo = $oPAI->getRelevantWorkitemRecord($wid);
                    $local_wid2winfo[$wid]['PERSON'] = $winfo;
                }
            }
            $bad_ar = [];
            foreach($local_wid2winfo as $wid=>$detail)
            {
                $winfo_master = $detail['MASTER'];
                $winfo_person = $detail['PERSON'];
                if($winfo_master['reh'] != $winfo_person['reh'])
                {
                    $bad_ar[] = "wid#$wid reh mismatch {$winfo_master['reh']} != {$winfo_person['reh']}";
                }
                if($winfo_master['sdt'] != $winfo_person['sdt'])
                {
                    $bad_ar[] = "wid#$wid sdt mismatch {$winfo_master['sdt']} != {$winfo_person['sdt']}";
                }
                if($winfo_master['edt'] != $winfo_person['edt'])
                {
                    $bad_ar[] = "wid#$wid edt mismatch {$winfo_master['edt']} != {$winfo_person['edt']}";
                }
                if($winfo_master['owner_projectid'] != $winfo_person['owner_projectid'])
                {
                    $bad_ar[] = "wid#$wid owner_projectid mismatch {$winfo_master['owner_projectid']} != {$winfo_person['owner_projectid']}";
                }
                if($winfo_master['owner_personid'] != $winfo_person['owner_personid'])
                {
                    $bad_ar[] = "wid#$wid owner_personid mismatch {$winfo_master['owner_personid']} != {$winfo_person['owner_personid']}";
                }
            }
            if(count($bad_ar) > 0)
            {
                throw new \Exception("Detected mismatch: " . implode(" and ", $bad_ar));
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}
