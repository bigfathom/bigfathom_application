/* 
 * Functions for working with table data
 * 
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 */

  
if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if(!bigfathom_util.hasOwnProperty("apptesting"))
{
    //Create the object property because it does not already exist
    bigfathom_util.apptesting = {
        "version": "20171029.1",
        "minwidth4chart": 2000,
        "custominfo": {},
        "element_ids": {
            'insight_warnings':"insight-warnings-grid-apptesting",
            'insight_errors':"insight-errors-grid-apptesting",
            'insight_status':"insight-status-grid-apptesting",
            'overall_status_message':"top-test-status-message"
        },
        "IO" : {
            "timer_delay":1000,
            "grab_apptest_results":{'dataname': 'apptest_results'
                , "fatal_fail_timestamp":null
                , "timeout_mseconds": 120000
                , "timout_message": 'No test results due to timeout.  This can happen if resources required exceed current configured capacity of your server.'
                , "failed_message": 'No test results from server in expected format.'
                , 'completed_request_id':-1, 'pending_request_id':null, 'latest_request_id':-1}
        }
    };
}

bigfathom_util.apptesting.runAppTestSequence = function(personid, test_sequence_ar) 
{
    var all_started_ts = Date.now();
    
    var total_error_count = 0;
    var total_success_count = 0;
    
    var done_map = {};
    var failed_map = {};
    var launch_map = {};
    var launch_num = 0;
    
    var current_test_seqnum = -1;
    
    var error_selector_txt = '#' + bigfathom_util.apptesting.runAppTestSequence.insight_errors;
    var warning_selector_txt = '#' + bigfathom_util.apptesting.runAppTestSequence.insight_warnings;
    var status_selector_txt = '#' + bigfathom_util.apptesting.runAppTestSequence.insight_status;
    var overall_status_selector_txt = '#' + bigfathom_util.apptesting.element_ids.overall_status_message;

    var setOverallMessage = function(status_name, info_markup)
    {
        var final_markup = '<span class="colorful-' + status_name + '">' + info_markup + "</span>";
        $(overall_status_selector_txt).html(final_markup);
    };

    var setInfoContent = function(typename, info_markup)
    {
        var selector_txt;
        if(typename === 'error')
        {
            selector_txt = error_selector_txt;
        } else
        if(typename === 'warning')
        {
            selector_txt = warning_selector_txt;
        } else
        {
            selector_txt = status_selector_txt;
        }
        var classname = 'messages messages--' + typename;
        var final_markup;
        if(info_markup === null || final_markup === '')
        {
            final_markup = '';
        } else {
            final_markup = '<div class="' + classname + '">' + info_markup + '</div>';
        }

        $(selector_txt).html(final_markup);
    };

    var getIDRoot = function(modulename, classname)
    {
        return modulename + "X" + classname;   
    };

    var processDataFromServer = function(callbackid, responseBundle)
    {
        var idroot = callbackid;
        var getExecutedItemMarkup = function(result)
        {
            var insight_markup = '';
            var codeproblems = '';
            var warning_markup = '';
            var insight_classname = '';
            if(!result.hasOwnProperty('has_error'))
            {
                codeproblems += ' missing required has_error attrib ';
            }
            if(!result.hasOwnProperty('error_msg'))
            {
                codeproblems += ' missing required error_msg attrib ';
            }
            if(!result.hasOwnProperty('error_detail'))
            {
                codeproblems += ' missing required error_detail attrib ';
            }
            if(result.hasOwnProperty('insight'))
            {
                var raw = JSON.stringify(result.insight);
                var clean = raw.replace(/"/g, '\'').replace("<",' ').replace(">",' ').trim();
                if(clean.length > 1)
                {
                    insight_markup = ' title="' + clean + '" ';
                    insight_classname = 'hover-help';
                }
            }
            if(result.hasOwnProperty('warning_msg') && result.warning_msg !== null && result.warning_msg.length > 1)
            {
                var raw = result.warning_msg;
                var clean = raw.replace(/"/g, '\'').replace("<",' ').replace(">",' ');
                warning_markup = ' <span class="colorful-notice">NOTE:' + clean + '</span>';
            }
            var mymarkup;
            
            if(result.has_error > 0 || codeproblems.length > 0)
            {
                mymarkup = ' - <span ' + insight_markup + ' class="colorful-bad ' + insight_classname + '">FAILED bc ' + codeproblems + result.error_msg + '</span>';
            } else {
                mymarkup = ' - <span ' + insight_markup + ' class="colorful-good ' + insight_classname + '">OK</span>';
            }
            mymarkup += warning_markup;
            var duration_tx = Math.round(1000000 * result.duration_mus)/1000000;
            mymarkup += " (" + duration_tx + "s)";
            return mymarkup;
        };
        
        //var idroot = getIDRoot(modulename,classname);//modulename + "X" + classname;
        
        var summary_selector_txt = '#summary_markup_' + idroot;
        
        console.log("LOOK we got a responseBundle=" + JSON.stringify(responseBundle));
        if (typeof callbackid === 'undefined' || typeof responseBundle === 'undefined' 
            || responseBundle.data === null || responseBundle.data.data === null)
        {
            bigfathom_util.apptesting.IO.grab_apptest_results.fatal_fail_timestamp = Math.floor(Date.now() / 1000); 
            setInfoContent('error',bigfathom_util.apptesting.IO.grab_apptest_results.failed_message);
            setInfoContent('warning',null);
            setInfoContent('status',null);
            console.log("FAILED callbackid=" + callbackid + " responseBundle=" + JSON.stringify(responseBundle));
            console.log("FAILED callbackid=" + callbackid + " show msg=" + bigfathom_util.apptesting.IO.grab_apptest_results.failed_message);
            var errmsg = ' Response=' + JSON.stringify(responseBundle);
            var mymarkup = "<span class='colorful-bad'><p>" + bigfathom_util.apptesting.IO.grab_apptest_results.failed_message + errmsg +  "</p></span>";
            $(summary_selector_txt).html(mymarkup);
            failed_map[idroot] = Date.now();
        } else {
            var records = responseBundle.data.data;
            var modulename = records.metadata.modulename;
            var classname = records.metadata.classname;
            done_map[idroot] = Date.now();
            console.log("LOOK@done " + idroot + " launch_map=" + JSON.stringify(launch_map));
            console.log("LOOK@done " + idroot + " done_map=" + JSON.stringify(done_map));                
            
            var testresult = records.testresult;
            var test_group_result = records.testresult.test_group_result;
            var setup_markup = "setUp" + getExecutedItemMarkup(testresult.setup_result);
            var setup_selector_txt = '#status_' + idroot + "XsetUp";
            $(setup_selector_txt).html(setup_markup);
            
            var teardown_markup = "tearDown" + getExecutedItemMarkup(testresult.teardown_result);
            var teardown_selector_txt = '#status_' + idroot + "XtearDown";
            $(teardown_selector_txt).html(teardown_markup);
            
            var method2result = test_group_result.method2result;
            for (var methodname in method2result) 
            {
                if (method2result.hasOwnProperty(methodname)) 
                {
                    var detail = method2result[methodname];
                    var result = detail.result;
                    console.log("LOOK result of " + methodname + " is " + JSON.stringify(result));
                    var selector_txt = '#status_' + idroot + "X" + methodname;
                    var mymarkup = getExecutedItemMarkup(result);
                    $(selector_txt).html(mymarkup);
                }
            }

            var ec = test_group_result.summary.error_count;
            var sc = test_group_result.summary.success_count;
            var msgclassname;
            if(ec > 0)
            {
                msgclassname = 'colorful-bad';
            } else {
                msgclassname = 'colorful-good';
            }
            var total_duration_mus = testresult.metadata.total_duration_mus;
            var mymarkup = "<span class='" + msgclassname + "'><ul><li>Total Time:" + total_duration_mus + "s<li>Fail:" + ec +  "<li>Pass:" + sc +  "</ul></span>";
            $(summary_selector_txt).html(mymarkup);
            
            total_error_count += ec;
            total_success_count += sc;
            
        }
        //Kick off the next test now
        runNextAppTest();
    };
      
    var setTimeoutMessageIfNotDone = function(modulename, classname)
    {
        var idroot = getIDRoot(modulename, classname);
        if(!done_map.hasOwnProperty(idroot) && !failed_map.hasOwnProperty(idroot))
        {
            done_map[idroot] = 'TIMEOUT';
            var selector_txt = '#summary_markup_' + idroot;
            var mymarkup = "<span class='colorful-bad'>FAILED DUE TO TIMEOUT</span>";
            $(selector_txt).html(mymarkup);
            
            //Now continue testing from the next item in the sequence
            console.log("LOOK WE HIT TIMEOUT ON " + idroot);
            runNextAppTest();
        }
    };
    
    var runNextAppTest = function()
    {
        current_test_seqnum++;
        if(current_test_seqnum < test_sequence_ar.length)
        {
            var onetestgroup = test_sequence_ar[current_test_seqnum];
            console.log("LOOK SEQ#" + current_test_seqnum + ") " + JSON.stringify(onetestgroup));
            
            var classname = onetestgroup.classname;
            var modulename = onetestgroup.modulename;
            var idroot = getIDRoot(modulename, classname);
            if(!done_map.hasOwnProperty(idroot))
            {
                //Run this one because we have not completed it yet.
                console.log("LOOK about the launch " + idroot);
                var grab_dataname = bigfathom_util.apptesting.IO.grab_apptest_results.dataname;
                var grab_apptest_results_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"classname": classname, "modulename": modulename}); 

                console.log("LOOK SEQ#" + current_test_seqnum + ") grab_apptest_results_fullurl=" + grab_apptest_results_fullurl);

                launch_num++;
                launch_map[idroot] = '#' + launch_num +  '@' + Date.now();
                //var available_for_launch = launch_map.
                var selector_txt = '#summary_markup_' + idroot;
                var launch_timestamp = Date.now();
                var launch_YYYYMMDD = moment(launch_timestamp).format();
                var duration_seconds = (launch_timestamp - all_started_ts)/1000;
                var mymarkup = "<span class='colorful-good'>Launched group#" + launch_num 
                        + " of " + test_sequence_ar.length + " groups at " + launch_YYYYMMDD + " (" + duration_seconds + "s since start)" + "</span>";
                $(selector_txt).html(mymarkup);
                setOverallMessage('status',mymarkup);
                setTimeout(setTimeoutMessageIfNotDone, bigfathom_util.apptesting.IO.grab_apptest_results.timeout_mseconds, modulename, classname);
                console.log("LOOK@launch " + idroot + " launch_map=" + JSON.stringify(launch_map));
                console.log("LOOK@launch " + idroot + " done_map=" + JSON.stringify(done_map));                
                bigfathom_util.data.getDataFromServer(grab_apptest_results_fullurl, {}, processDataFromServer, idroot);
            }
            //runNextAppTest();
        } else {
            //We are done with everything now!
            var all_done_ts = Date.now();
            var all_done_YYYYMMDD = moment(all_done_ts).format();
            var duration_seconds = (all_done_ts - all_started_ts)/1000;
            $('#remove_buttons_on_done').html("<!-- Removed the cancel button because all tests are done! -->");
            var mymarkup = "All tests completed at " + all_done_YYYYMMDD + " (duration " + duration_seconds + "s)!<ul><li>Fail:" 
                    + total_error_count 
                    + "</li><li>Pass:" 
                    + total_success_count + "</li></ul>";
            var status_name;
            if(total_error_count > 0)
            {
                status_name = 'bad';
            } else {
                status_name = 'good';
            }
            setOverallMessage(status_name,mymarkup);
        }
    };
    
    var runAllAppTests = function()
    {
        current_test_seqnum = -1;
        runNextAppTest();
    };
    
    runAllAppTests();
};

bigfathom_util.apptesting.clearAllAppTestAlerts = function()
{
    bigfathom_util.apptesting.custominfo.application_test_alerts = {};
};

bigfathom_util.apptesting.setAppTestAlert = function(typename, wid, msg)
{
    if(!bigfathom_util.apptesting.custominfo.application_test_alerts.hasOwnProperty(wid))
    {
        bigfathom_util.apptesting.custominfo.application_test_alerts[wid] = {};
    }
    if(!bigfathom_util.apptesting.custominfo.application_test_alerts[wid].hasOwnProperty(typename))
    {
        bigfathom_util.apptesting.custominfo.application_test_alerts[wid][typename] = [];
    }
    bigfathom_util.apptesting.custominfo.application_test_alerts[wid][typename].push(msg);
};

bigfathom_util.apptesting.showAllAppTestAlerts = function()
{
    console.log("TODO show all application test_alerts " + JSON.stringify(bigfathom_util.apptesting.custominfo.application_test_alerts));
};


jQuery(document).ready(function(){
    (function ($) {

        var url_img = Drupal.settings.myurls.images;
        var personid = Drupal.settings.personid;
        var testsequence_data = Drupal.settings.testsequence_data;
        
        console.log("Starting manageprojectbaseline " + bigfathom_util.apptesting.version);
        console.log("... url_img=" + url_img);
        console.log("... personid=" + personid);
        console.log("... testsequence_data=" + testsequence_data);
        
        var testsequence_data_obj = JSON.parse(testsequence_data);
        
        bigfathom_util.apptesting.runAppTestSequence(personid, testsequence_data_obj);
        
    }(jQuery));
});
    

