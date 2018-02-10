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
if(!bigfathom_util.hasOwnProperty("table"))
{
    //Create the object property because it does not already exist
    bigfathom_util.table = {
        "version": "20170915.1",
        "minwidth4chart": 2000,
        "custominfo": {"branch_filtering":{
                "reference_wid": null
            }
        }, 
        "element_ids": {
            'insight_warnings':"insight-warnings-grid-workitem-duration",
            'insight_errors':"insight-errors-grid-workitem-duration",
            'insight_status':"insight-status-grid-workitem-duration"
        },
        "IO" : {
            "timer_delay":1000,
            "grab_forecast":{'dataname': 'forecast_detail'
                , "fatal_fail_timestamp":null
                , "timeout_mseconds": 120000
                , "timout_message": 'No forecast information was retrieved from the server due to timeout.  This can happen if resources required exceed current configured capacity of your server.'
                , "failed_message": 'No forecast information was properly retrieved from the server.  This can happen if resources required exceed current configured capacity of your server.'
                , 'completed_request_id':-1, 'pending_request_id':null, 'latest_request_id':-1}
        },
        "autoHideGanttCol": function(controller)
            {
                var colidx_gantt = controller.getColumnIndex("calc_gantt");
                var colrefnum = colidx_gantt + 1;
                var width = document.body.clientWidth;
                if(width < bigfathom_util.table.minwidth4chart)
                {
                    console.log("Hiding the gantt column because width=" + width + " is smaller than minimum of " + bigfathom_util.table.minwidth4chart);
                    $('th:nth-child(' + colrefnum + '),td:nth-child(' + colrefnum + ')').hide();
                } else {
                    console.log("Showing the gantt column because width=" + width + " is bigger than minimum of " + bigfathom_util.table.minwidth4chart);
                    $('th:nth-child(' + colrefnum + '),td:nth-child(' + colrefnum + ')').show();
                }
            }
    };
}

bigfathom_util.table.updateInsightDisplay = function(controller) 
{
    bigfathom_util.table.IO.grab_forecast.instanceid = Math.random();
    var getAndUpdate = function(pending_request_id)
    {
        
        var error_selector_txt = '#' + bigfathom_util.table.element_ids.insight_errors;
        var warning_selector_txt = '#' + bigfathom_util.table.element_ids.insight_warnings;
        var status_selector_txt = '#' + bigfathom_util.table.element_ids.insight_status;
        
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
        
        if(bigfathom_util.table.IO.grab_forecast.pending_request_id === null)
        {
            setInfoContent('status','Examining workitems for warnings...');
        }
        bigfathom_util.table.IO.grab_forecast.pending_request_id = pending_request_id;
        
        var setTimeoutMessageIfNotDone = function(reference_request_id)
        {
            console.log("LOOK setTimeoutMessageIfNotDone reference_request_id=" + reference_request_id);
            console.log("LOOK setTimeoutMessageIfNotDone pending=" + bigfathom_util.table.IO.grab_forecast.pending_request_id);
            if(bigfathom_util.table.IO.grab_forecast.pending_request_id !== null 
                    && bigfathom_util.table.IO.grab_forecast.pending_request_id === reference_request_id)
            {
            console.log("LOOK setTimeoutMessageIfNotDone SET MESSAGE mseconds" + bigfathom_util.table.IO.grab_forecast.timeout_mseconds);
                setInfoContent('warning',null);
                setInfoContent('status',null);
                setInfoContent('error', bigfathom_util.table.IO.grab_forecast.timout_message);
            }
        };
        
        setTimeout(setTimeoutMessageIfNotDone, bigfathom_util.table.IO.grab_forecast.timeout_mseconds, pending_request_id);
        
        var grab_dataname = bigfathom_util.table.IO.grab_forecast.dataname;
        var projectid = bigfathom_util.table.custominfo.projectid;
        var grab_forecast_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"relevant_projectids": projectid}); 
        
        var processDataFromServer = function(callbackid, responseBundle)
        {
            if (typeof callbackid === 'undefined' || typeof responseBundle === 'undefined' 
                || responseBundle.data === null || responseBundle.data.data === null)
            {
                bigfathom_util.table.IO.grab_forecast.fatal_fail_timestamp = Math.floor(Date.now() / 1000); 
                setInfoContent('error',bigfathom_util.table.IO.grab_forecast.failed_message);
                setInfoContent('warning',null);
                setInfoContent('status',null);
                console.log("FAILED callbackid=" + callbackid + " responseBundle=" + JSON.stringify(responseBundle));
                console.log("FAILED callbackid=" + callbackid + " show msg=" + bigfathom_util.table.IO.grab_forecast.failed_message);
            } else {
                var records = responseBundle.data.data;

                var setDisplayInfo = function(workitem2detail)
                {
                    //console.log("LOOK setDisplayInfo ===========" + JSON.stringify(workitem2detail));
                    var errors_ar = [];
                    var warnings_ar = [];
                    var errors_markup = null;
                    var warnings_markup = null;
                    var status_markup = null;
                    
                    var pushMessages = function(typename, wid, source_array, target_array)
                    {
                        for(var i=0; i<source_array.length; i++)
                        {
                            var myinfo = source_array[i];
                            var msg = myinfo.message;
                            target_array.push(msg);
                            bigfathom_util.table.setWorkitemAlert(controller, typename, wid, msg);
                        }
                        return target_array;
                    };
                    
                    bigfathom_util.table.clearAllWorkitemAlerts(controller);
                    for (var wid in workitem2detail) 
                    {
                        //console.log("LOOK IN LOOP wid=" + wid);
                        if (workitem2detail.hasOwnProperty(wid)) 
                        {
                            var winfo = workitem2detail[wid];
                            if(winfo.warnings !== null && winfo.warnings.detail.length>0)
                            {
                                warnings_ar = pushMessages('warning', wid, winfo.warnings.detail, warnings_ar);
                            }
                            if(winfo.errors !== null && winfo.errors.detail.length>0)
                            {
                                errors_ar = pushMessages('error', wid, winfo.errors.detail, errors_ar);
                            }
                        }
                    }
                    bigfathom_util.table.showAllWorkitemAlerts(controller);
                    
                    if(errors_ar.length > 0)
                    {
                        if(errors_ar.length > 1)
                        {
                            errors_markup = '<span>Detected ' + errors_ar.length + ' Workitem Errors</span><ol><li>' + errors_ar.join('<li>') + '</ol>';
                        } else {
                            errors_markup = '<span>Detected 1 Workitem Error</span><ol><li>' + errors_ar.join('<li>') + '</ol>';
                        }
                    }
                    if(warnings_ar.length > 0)
                    {
                        if(warnings_ar.length > 1)
                        {
                            warnings_markup = '<span>Detected ' + warnings_ar.length + ' Workitem Warnings</span><ol><li>' + warnings_ar.join('<li>') + '</ol>';

                        } else {
                            warnings_markup = '<span>Detected 1 Workitem Warning</span><ol><li>' + warnings_ar.join('<li>') + '</ol>';
                        }
                    }
                    if(warnings_ar.length === 0 && errors_ar.length === 0)
                    {
                        status_markup = 'No date and sequencing warnings detected at this time.';
                    }
                    
                    setInfoContent('error',errors_markup);
                    setInfoContent('warning',warnings_markup);
                    setInfoContent('status',status_markup);
                };
                /*
                $(warning_selector_txt).text('TODO warnings for id#' + pending_request_id 
                        + '<pre>' + JSON.stringify(records) + '</pre>');
                */
                setDisplayInfo(records.workitem2detail);
            }

            var invokeRequest = function()
            {
                //Update the global vars and see if we need to run query again now
                bigfathom_util.table.IO.grab_forecast.completed_request_id = pending_request_id;
                //console.log("LOOK completed get detail for id#" + pending_request_id);
                if(pending_request_id < bigfathom_util.table.IO.grab_forecast.latest_request_id)
                {
                    //Need to run the request again right now
                    getAndUpdate(bigfathom_util.table.IO.grab_forecast.latest_request_id);
                }

                //Clear our pending marker
                bigfathom_util.table.IO.grab_forecast.pending_request_id = null;
                bigfathom_util.table.IO.timer_delay = 1;    //We only wanted to delay the initial invocation, not all the rest
            };

            setTimeout(invokeRequest, bigfathom_util.table.IO.timer_delay);
        };
        
        if(bigfathom_util.table.IO.grab_forecast.fatal_fail_timestamp === null)
        {
            bigfathom_util.data.getDataFromServer(grab_forecast_fullurl, {}, processDataFromServer, projectid);
        } else {
            console.log("SKIPPING QUERY BECAUSE IO.grab_forecast.fatal_fail_timestamp=" + bigfathom_util.table.IO.grab_forecast.fatal_fail_timestamp);
        }
        console.log("LOOK BOTTOM UNIQUE INSTANCE ID#" + bigfathom_util.table.IO.grab_forecast.instanceid);
    };
    
    bigfathom_util.table.IO.grab_forecast.latest_request_id++;
    
    var id_diff = bigfathom_util.table.IO.grab_forecast.latest_request_id - bigfathom_util.table.IO.grab_forecast.completed_request_id;
    if(null === bigfathom_util.table.IO.grab_forecast.pending_request_id 
            && 0 !== id_diff)
    {
        //Looks like we should launch, give a little delay for events to catch up then check again.
        var delayMillis = 500;
        setTimeout(function() {
            //Still need to launch?
            id_diff = bigfathom_util.table.IO.grab_forecast.latest_request_id - bigfathom_util.table.IO.grab_forecast.completed_request_id;
            if(null === bigfathom_util.table.IO.grab_forecast.pending_request_id 
                && 0 !== id_diff)
            {
                //Launch it now
                console.log("LOOK LAUNCHING WITH id_diff" + id_diff);
                console.log("LOOK LAUNCHING WITH pending_request_id#" + bigfathom_util.table.IO.grab_forecast.pending_request_id);
                console.log("LOOK LAUNCHING WITH completed_request_id#" + bigfathom_util.table.IO.grab_forecast.completed_request_id);
                console.log("LOOK LAUNCHING WITH latest_request_id#" + bigfathom_util.table.IO.grab_forecast.latest_request_id);
                getAndUpdate(bigfathom_util.table.IO.grab_forecast.latest_request_id);
            }
        }, delayMillis);
    }
};

bigfathom_util.table.clearAllWorkitemAlerts = function(controller)
{
    bigfathom_util.table.custominfo.workitem_alerts = {};
};

bigfathom_util.table.setWorkitemAlert = function(controller, typename, wid, msg)
{
    if(!bigfathom_util.table.custominfo.workitem_alerts.hasOwnProperty(wid))
    {
        bigfathom_util.table.custominfo.workitem_alerts[wid] = {};
    }
    if(!bigfathom_util.table.custominfo.workitem_alerts[wid].hasOwnProperty(typename))
    {
        bigfathom_util.table.custominfo.workitem_alerts[wid][typename] = [];
    }
    bigfathom_util.table.custominfo.workitem_alerts[wid][typename].push(msg);
};

bigfathom_util.table.showAllWorkitemAlerts = function(controller)
{
    console.log("TODO show all workitem alerts " + JSON.stringify(bigfathom_util.table.custominfo.workitem_alerts));
};


bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    bigfathom_util.table.default_controller = controller;   //So we can access it with plain function calls
    bigfathom_util.table.autoHideGanttCol(controller);
    
    var rowcount = controller.getRowCount();
    var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo);  
        bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);  
    }
    
    bigfathom_util.table.showTotals(controller, minmaxinfo);
    
    bigfathom_util.table.updateInsightDisplay(controller);
};

bigfathom_util.table.setCustomRowAttrib = function(controller, row, attribname, attribvalue)
{
    //TODO
};

/**
 * NOTE: Activate this by calling invokeFilter, do NOT call it directly!
 */
bigfathom_util.table.customRowMatchFunction = function(controller, row)
{
    var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
    
    var statusradio_groupname = filterareaid + '_statusgroup';
    var ownerradio_groupname = filterareaid + '_ownergroup';
    var branchradio_groupname = filterareaid + '_branchgroup';
    
    var status_filter = $('input:radio[name=' + statusradio_groupname + ']:checked').val();
    var owner_filter = $('input:radio[name=' + ownerradio_groupname + ']:checked').val();
    var branch_filter = $('input:radio[name=' + branchradio_groupname + ']:checked').val();
    
    var branch_filering = bigfathom_util.table.custominfo.branch_filtering;
    var has_branch_filtering = (branch_filering.reference_wid !== null);
    
    var matches = true;
    if(status_filter !== "none")
    {
        var status_cd = row.status_cd;
        var detail = bigfathom_util.table.custominfo.map_status[status_cd];
        if(status_filter === 'completed')
        {
            matches = matches && (detail.terminal_yn == 1);
            console.log("LOOK lookup got this>>>" + JSON.stringify(detail));
        } else
        if(status_filter === 'notstarted')
        {
            matches = matches && (detail.workstarted_yn != 1);
        } else
        if(status_filter === 'started')
        {
            matches = matches && (detail.workstarted_yn == 1);
            console.log("LOOK lookup got this>>>" + JSON.stringify(detail));
        } else
        if(status_filter === 'unhappy')
        {
            matches = matches && (detail.happy_yn === 0 || detail.happy_yn === '0');
        }        
    }
    if(matches && owner_filter !== "none")
    {
        if(owner_filter === 'yours')
        {
            matches = matches && (row.isyours == 1);
        } else
        if(owner_filter === 'oneowner')
        {
            matches = matches && (row.ownercount == 1);
        }
    }
    if(matches && has_branch_filtering)
    {
        if(bigfathom_util.table.custominfo.branch_filtering.reference_wid !== null)
        {
            var special_tag = row.special_tag;
            if(branch_filter === 'all')
            {
                matches = matches && special_tag !== null;
            } else {
                if(branch_filter === 'ants')
                {
                    matches = matches && (special_tag == 'ref' || special_tag == 'ant');
                } else {
                    matches = matches && (special_tag == 'ref' || special_tag == 'dep');
                }
                console.log("filter special_tag=[" + special_tag + "] matches=[" + matches + "]");                
            }
        }
    }
    
    return matches;
};

bigfathom_util.table.initCustomTableFilters = function(controller) 
{
    
    var getOneRadioMarkup = function(ischecked,name,id,label,value,tooltip)
    {
        var ischeckedmarkup;
        if(ischecked)
        {
            ischeckedmarkup = " checked='checked' ";
        } else {
            ischeckedmarkup = "";
        }
        var markup = "<div class='inline'><label title='" 
                + tooltip + "' for='" 
                + id + "'>" 
                + label + "</label>"
                + "<input class='click-for-action' "
                + "name='"+ name + "' "
                + "id='"+ id + "' "
                + "value='" + value + "' "
                + ischeckedmarkup
                + " type='radio'></input>"
                + "</div>";
        return markup;
    };
    //alert("LOOK todo write markup stuff to " + controller.browserGridTableData.elementids.custom_table_filters_area_id);
    
    bigfathom_util.table.custominfo.personid = Drupal.settings.personid;
    bigfathom_util.table.custominfo.projectid = Drupal.settings.projectid;
    bigfathom_util.table.custominfo.map_status = JSON.parse(Drupal.settings.map_status);

    var textfiltercontroldivid = controller.browserGridTableData.elementids.textfiltercontroldivid;
    var pagecontrolid = controller.browserGridTableData.elementids.pagesizecontrolid;
    
    $("#"+textfiltercontroldivid).addClass("browserGrid-filtermargins1em");
    $("#"+pagecontrolid).addClass("browserGrid-filtermargins1em");

    var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
    var markup_container_elem = document.getElementById(filterareaid);
    var id_radio_all = filterareaid + "_showall";
    var id_radio_completed = filterareaid + "_completed";
    var id_radio_unstarted = filterareaid + "_unstarted";
    var id_radio_started = filterareaid + "_started";
    var id_radio_unhappy = filterareaid + "_unhappy";
    
    var id_radio_owner_all = filterareaid + "_owner_showall";
    var id_radio_owner_yours = filterareaid + "_owner_yours";
    var id_radio_owner_one = filterareaid + "_owner_one";

    var id_fieldset_br = 'branch_filter_area';
    var id_radio_br_none = filterareaid + "_br_none";
    var id_radio_br_ants = filterareaid + "_br_ants";
    var id_radio_br_deps = filterareaid + "_br_deps";
    var id_radio_br_all = filterareaid + "_br_all";
    
    var statusradio_groupname = filterareaid + '_statusgroup';
    var ownerradio_groupname = filterareaid + '_ownergroup';
    var branchradio_groupname = filterareaid + '_branchgroup';
    
    var markup1 = "<fieldset class='elem-inline table-filter'><legend title='Status Filters'>Status Filter</legend><div class='group-standard'>" 
            + getOneRadioMarkup(true,statusradio_groupname,id_radio_all,'None','none','No filter on status')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_completed,'Completed','completed','Only included where status is in a terminal state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_unstarted,'Unstarted','notstarted','Only included where status is in an unstarted state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_started,'Started','started','Only included where status is in a started and not yet terminal state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_unhappy,'Unhappy','unhappy','Only included where status is in an unhappy state')
            + "</div></fieldset>";
    
    var markup2 = "<fieldset class='elem-inline table-filter'><legend title='Ownership Filters'>Ownership Filter</legend><div class='group-standard'>" 
            + getOneRadioMarkup(true,ownerradio_groupname,id_radio_owner_all,'None','none','No filter on ownship')
            + getOneRadioMarkup(false,ownerradio_groupname,id_radio_owner_yours,'Yours','yours','Only included if you are an owner')
            + getOneRadioMarkup(false,ownerradio_groupname,id_radio_owner_one,'One Owner','oneowner','Only included if there is just one owner')
            + "</div></fieldset>";
    
    var markup3 = "<fieldset id='" + id_fieldset_br + "' class='elem-inline table-filter table-filter-special'>"
            + "<legend id='" + id_fieldset_br + "_legend' title='Filter rows based on branch membership'>Branch Filter</legend>"
            + "<div class='group-standard group-standard-special'>" 
            + getOneRadioMarkup(true,branchradio_groupname,id_radio_br_none,'None','none','Clear the branch filter')
            + getOneRadioMarkup(false,branchradio_groupname,id_radio_br_deps,'Deps','deps','Only show reference and its dependent workitems')
            + getOneRadioMarkup(false,branchradio_groupname,id_radio_br_ants,'Ants','ants','Only show reference and its antecedent workitems')
            + getOneRadioMarkup(false,branchradio_groupname,id_radio_br_all,'All','all','Show all workitems in the branch containing our reference workitem')
            + "</div></fieldset>";
    
    markup_container_elem.innerHTML = markup1 + markup2 + markup3;
    
    //Create a listener to handle the clicks
    $('input:radio[name=' + statusradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
    });
    $('input:radio[name=' + ownerradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
    });
    $('input:radio[name=' + branchradio_groupname + ']').on('change', function()
    {
        var branch_filter_value = $('input:radio[name=' + branchradio_groupname + ']:checked').val(); 
        if(branch_filter_value === 'none')
        {
            bigfathom_util.table.custominfo.branch_filtering.reference_wid = null;
            $('#branch_filter_area').hide();
        }        
        bigfathom_util.table.invokeFilter();
    });
    
    if(bigfathom_util.table.custominfo.branch_filtering.reference_wid === null)
    {
        $('#branch_filter_area').hide();
    }
    
};

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var membercount = minmaxinfo.member_count;
    if(membercount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no workitems in this table.</div>";
    } else {
        var effort = minmaxinfo.total_effort;
        
        var min_date = minmaxinfo.min_date;
        var max_date = minmaxinfo.max_date;
        
        var min_dfte = minmaxinfo.min_planned_fte_count;
        var max_dfte = minmaxinfo.max_planned_fte_count;
        var min_cfte = minmaxinfo.min_calc_mfte;
        var max_cfte = minmaxinfo.max_calc_mfte;
        var count_ant_projects = minmaxinfo.count_ant_projects;
        
        var markup_lock_counts = "<fieldset class='elem-inline'><legend title='Locked items from the displayed workitems'>Locked Estimates</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Branch Effort' for='count_locked_branch_effort_hours'>BCT:</label><span class='showvalue' id='count_locked_branch_effort_hours'>" + minmaxinfo.count_branch_effort_hours_est_locked_yn + "</span></div>" 
                + "<div class='inline'><label title='Direct Effort' for='count_locked_effort_hours'>DEHL:</label><span class='showvalue' id='count_locked_effort_hours'>" + minmaxinfo.count_effort_hours_est_locked_yn + "</span></div>" 
                + "<div class='inline'><label title='Start Date' for='count_locked_start_date'>SDL:</label><span class='showvalue' id='count_locked_start_date'>" + minmaxinfo.count_planned_start_dt_locked_yn + "</span></div>" 
                + "<div class='inline'><label title='End Date' for='count_locked_end_date'>EDL:</label><span class='showvalue' id='count_locked_end_date'>" + minmaxinfo.count_planned_end_dt_locked_yn + "</span></div>" 
                + "</div></fieldset>";
        
        var markup_effort = "<fieldset class='elem-inline'><legend title='Simple effort totals from the displayed workitems'>Effort</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Summation of the declared remaining effort hours of each workitem' for='effort_hours'>Estimated Remaining Effort Hours:</label><span class='showvalue' id='effort_hours'>" + effort + "</span></div>" 
                + "</div></fieldset>";
        var markup_dates = "<fieldset class='elem-inline'><legend title='The dates declared on the workitems'>Dates</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='min_date'>Lowest:</label><span class='showvalue' id='min_date'>" + min_date + "</span></div>" 
                + "<div class='inline'><label for='max_date'>Highest:</label><span class='showvalue' id='max_date'>" + max_date + "</span></div>" 
                + "</div></fieldset>";
        var markup_fte = "<fieldset class='elem-inline'><legend title='Full-time-equivalent totals for displayed workitems'>FTE</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='min_dfte'>Lowest Declared:</label><span class='showvalue' id='min_dfte'>" + min_dfte + "</span></div>" 
                + "<div class='inline'><label for='max_dfte'>Highest Declared:</label><span class='showvalue' id='max_dfte'>" + max_dfte + "</span></div>" 
                + "<div class='inline'><label for='min_cfte'>Lowest Computed:</label><span class='showvalue' id='min_cfte'>" + min_cfte + "</span></div>" 
                + "<div class='inline'><label for='max_cfte'>Highest Computed:</label><span class='showvalue' id='max_cfte'>" + max_cfte + "</span></div>" 
                + "</div></fieldset>";
        var markup_ant_projects = "<fieldset class='elem-inline'><legend title='Antecedent Projects (AP) that this project has declared it depends on'>AP</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='The number of other projects that the current project depends on for its successful completion' for='count_ant_projects'>Count:</label><span class='showvalue' id='count_ant_projects'>" + count_ant_projects + "</span></div>" 
                + "</div></fieldset>";
        
        var markup_branch;
        if(bigfathom_util.table.custominfo.branch_filtering.reference_wid === null)
        {
            markup_branch = "";
        } else {
            var level_sorted = bigfathom_util.table.custominfo.branch_filtering.level_sorted;
            var branch_markup = "";
            var posname = 'dependent';
            for(var i=0; i<level_sorted.length; i++)
            {
                var group = level_sorted[i];
                var txt = "";
                for(var j=0; j<group.length; j++)
                {
                    var wid = group[j];
                    if(wid === bigfathom_util.table.custominfo.branch_filtering.reference_wid)
                    {
                        txt += "<strong title='reference workitem'>" + wid + "</strong> ";
                        posname = 'antecedant';
                    } else {
                        txt += "<span title='" + posname + "'>" + wid + "</span> ";
                    }
                }
                var leftarrow;
                if(i<level_sorted.length-1)
                {
                   leftarrow = "<i title='influence direction' class='fa fa-arrow-left' aria-hidden='true'></i>"; 
                } else {
                   leftarrow = "";
                }
                branch_markup += "<div class='inline' title='level " + i + "'><span class='showvalue'>(" + txt.trim() + ")" 
                        + "</span>" + "</div>"  + leftarrow;
                markup_branch = "<fieldset id='selected_branch_fieldset' class='elem-inline'><legend title='Current branch workitems grouped into their largest level value of the branch'>Selected Branch</legend><div class='group-standard'>" 
                        + branch_markup
                        + "</div></fieldset>";
            }
        }
        
        markup = "<div class='grid-totals-container'>" + markup_effort + markup_dates + markup_fte + markup_lock_counts + markup_branch + markup_ant_projects + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var member_count = 0;
    var count_branch_effort_hours_est_locked_yn = 0;
    var count_effort_hours_est_locked_yn = 0;
    var count_planned_start_dt_locked_yn = 0;
    var count_planned_end_dt_locked_yn = 0;

    var total_branch_effort = 0;
    var total_effort = 0;
    
    var min_dt = '';
    var max_dt = '';
    
    var min_planned_fte_count = '';
    var max_planned_fte_count = '';
    var min_calc_mfte = '';
    var max_calc_mfte = '';
    
    var ant_project_count = 0;
    
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        member_count++;

        var value_is_ant_project_yn = parseInt(controller.getRowAttribute(rowIndex, "data_is_ant_project_yn"));
        var value_effort_hours_est_locked_yn = parseInt(controller.getRowAttribute(rowIndex, "data_effort_hours_est_locked_yn"));
        var values = controller.getRowValues(rowIndex);
        var value_branch_effort_hours = parseInt(values["branch_effort_hours_est"], 10);
        var value_effort_hours = parseInt(values["remaining_effort_hours"], 10);

        var value_start_dt = values["start_dt"];
        var value_end_dt = values["end_dt"];
        var value_branch_effort_hours_est_locked_yn = (values["branch_effort_hours_est_locked_yn"] == 'L' ? 1 : 0);

        var value_planned_start_dt_locked_yn = values["planned_start_dt_locked_yn"];
        var value_planned_end_dt_locked_yn = values["planned_end_dt_locked_yn"];
        var value_planned_fte_count = values["planned_fte_count"];
        var raw_value_calc_mfte = values["calc_mfte"];
        
        if(value_is_ant_project_yn)
        {
            ant_project_count++;
        } else {
            total_branch_effort += value_branch_effort_hours;
            if(!isNaN(value_effort_hours))
            {
                total_effort += value_effort_hours;
            }
            count_branch_effort_hours_est_locked_yn += value_branch_effort_hours_est_locked_yn;
            count_effort_hours_est_locked_yn += value_effort_hours_est_locked_yn;
            count_planned_start_dt_locked_yn += value_planned_start_dt_locked_yn;
            count_planned_end_dt_locked_yn += value_planned_end_dt_locked_yn;
        }
        
        if(value_start_dt != '')
        {
            if(min_dt == '' || min_dt > value_start_dt)
            {
                min_dt = value_start_dt;
            }
            if(max_dt == '' || max_dt < value_start_dt)
            {
                max_dt = value_start_dt;
            }
        }
        
        if(value_end_dt != '')
        {
            if(min_dt == '' || min_dt > value_end_dt)
            {
                min_dt = value_end_dt;
            }
            if(max_dt == '' || max_dt < value_end_dt)
            {
                max_dt = value_end_dt;
            }
        }
        
        if(value_planned_fte_count != '')
        {
            if(min_planned_fte_count == '' || min_planned_fte_count > value_planned_fte_count)
            {
                min_planned_fte_count = value_planned_fte_count;
            }
            if(min_calc_mfte == '' || max_planned_fte_count < value_planned_fte_count)
            {
                max_planned_fte_count = value_planned_fte_count;
            }
        }
        
        if(raw_value_calc_mfte != '')
        {
            var value_calc_mfte = '';
            var n2 = raw_value_calc_mfte.indexOf("]");
            if(n2 > 0)
            {
                var n1 = raw_value_calc_mfte.indexOf(":");
                if(n1 > 0 && n1 < n2)
                {
                    value_calc_mfte = raw_value_calc_mfte.substr(n1+1,n2-1).trim();
                } else {
                    value_calc_mfte = raw_value_calc_mfte;
                } 
            }
            if(value_calc_mfte > '' && !isNaN(parseFloat(value_calc_mfte)))
            {
                value_calc_mfte = parseFloat(value_calc_mfte);
                if(min_calc_mfte == '' || min_calc_mfte > value_calc_mfte)
                {
                    min_calc_mfte = value_calc_mfte;
                }
                if(max_planned_fte_count == '' || max_calc_mfte < value_calc_mfte)
                {
                    max_calc_mfte = value_calc_mfte;
                }
            }
        }
    }
    if(min_dt === null)
    {
        min_dt = '';
    }
    if(max_dt === null)
    {
        max_dt = '';
    }
    var themap = {
        'member_count':member_count,
        'total_branch_effort':total_branch_effort, 
        'total_effort':total_effort,
        'min_date':min_dt, 'max_date':max_dt,
        'min_planned_fte_count':min_planned_fte_count, 'max_planned_fte_count':max_planned_fte_count,
        'min_calc_mfte':min_calc_mfte, 'max_calc_mfte':max_calc_mfte,
        'count_branch_effort_hours_est_locked_yn':count_branch_effort_hours_est_locked_yn,
        'count_effort_hours_est_locked_yn':count_effort_hours_est_locked_yn,
        'count_planned_start_dt_locked_yn':count_planned_start_dt_locked_yn,
        'count_planned_end_dt_locked_yn':count_planned_end_dt_locked_yn,
        'count_ant_projects':ant_project_count
    };
    controller.bigfathom_minmax = themap;
    return themap;
};

/**
 * Call this to update the indicators of which cells in a row are editable
 * The core framework does NOT have a comparable method that is called 
 * that would CHANGE the markings of other cells on a row.
 */
bigfathom_util.table.recomputeEditableMarkings = function(controller, rowIndex) 
{
    //var workitemid = controller.getRowId(rowIndex);
    //var values = controller.getRowValues(rowIndex);
    //var value_typeletter = controller.getRowAttribute(rowIndex, "typeletter");
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        if(column.editable)
        {
            //var colname = column.name;
            if(bigfathom_util.table.isEditable(controller, rowIndex, columnIndex))
            {
                controller.markCellEditable(rowIndex, columnIndex);
            } else {
                controller.markCellLocked(rowIndex, columnIndex);
            }            
        }
    }
};

/**
 * Call this to populate the computed fields of a row
 */
bigfathom_util.table.recomputeFormulas = function(controller, rowIndex, minmaxinfo, allowFullGridRefresh) 
{
    console.log("Starting recomputeFormulas for " + controller.getRowId(rowIndex) + " at rowIndex=" + rowIndex);
    if(typeof minmaxinfo === 'undefined')
    {
        minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    }
    if(typeof allowFullGridRefresh === 'undefined')
    {
        allowFullGridRefresh = true;
    }
    var url_img = Drupal.settings.myurls.images;
    var min_date = minmaxinfo['min_date'];
    var max_date = minmaxinfo['max_date'];
    var gantt_width = 200;
    //var gantt_dateicon_width = 22;
    var gantt_dateicon_width = 11;  //hw
    var max_days;
    var px_per_day;
    
    function getImageURL(filename)
    {
        return url_img + "/" + filename;
    }

    function getGanttIconURL(purpose_name, typename, is_estimated, is_pinned, is_bad)
    {
        var filename;
        var extra_suffix = "";
        if(is_bad)
        {
            typename = 'bad';
        }
        if(is_estimated)
        {
            if(is_pinned)
            {
                filename = "gantt_" + typename + "_" + purpose_name + "_est_pinned" + extra_suffix;
            } else {
                filename = "gantt_" + typename + "_" + purpose_name + "_est" + extra_suffix;
            }
        } else {
            if(is_pinned)
            {
                filename = "gantt_" + typename + "_" + purpose_name + "_pinned" + extra_suffix;
            } else {
                filename = "gantt_" + typename + "_" + purpose_name + extra_suffix;
            }
        }
        var imgurl = getImageURL(filename + ".png");
        return imgurl;
    }

    function getGanttClearSpacerURL()
    {
        var filename = "gantt_clearspacer.png";
        return getImageURL(filename);
    }
    
    function getBarStartSpacerPixelCount(ref_dt)
    {
        var days = getDaysBetweenDates(min_date, ref_dt);
        var px = Math.floor(days * px_per_day);
        if(px < 1)
        {
            return 0;
        }
        return px;
    }
    
    function getBarStartSpacerMarkup(start_dt)
    {
        var clearspacer_url = getGanttClearSpacerURL();
        var px = getBarStartSpacerPixelCount(start_dt);
        if(px < 1)
        {
            return '';
        }
        return "<img src='" + clearspacer_url + "' height='1px' width='" + px + "px'/>";
    }

    function getBarMiddlePixelCount(start_dt, end_dt)
    {
        var start_dt_px = getBarStartSpacerPixelCount(start_dt);
        var end_dt_px = getBarStartSpacerPixelCount(end_dt);

        var middle_width = end_dt_px - start_dt_px - gantt_dateicon_width;
        return middle_width;
    }

    function getBarMiddleMarkup(middle_bar_width, durationdays, typename, is_estimated, is_pinned, is_bad)
    {
        if(middle_bar_width < 0)
        {
            return "<span title='" + durationdays + " days with no space to display (end date icon is not in ideal position)'></span>";
        }
        var imgurl = getGanttIconURL("middle", typename, is_estimated, is_pinned, is_bad);
        return "<img title='" + durationdays + " days' src='" + imgurl + "' height='20px' width='" + middle_bar_width + "px'/>";
    }
    
    function getGanttMarkup(typeletter, durationdays, start_dt, end_dt, est_flags, pin_flags)
    {
        var markup;
        if(durationdays < 0)
        {
            markup = "<span class='gantt-bar' title='invalid " + durationdays + " days'></span>";    
        } else {
            var typename;
            var is_bad = false;
            if(typeletter === 'P')
            {
                typename = 'proj';
            } else
            if(typeletter === 'G')
            {
                typename = 'goal';
            } else {
                typename = 'task';
            }
            var purpose_name;

            var leftspacer_markup = getBarStartSpacerMarkup(start_dt);

            var sortstr;
            if(typeletter == 'P')
            {
                sortstr = start_dt;
            } else {
                sortstr = start_dt + "_" + end_dt + "_" + typeletter;
            }
            if(durationdays < 2)
            {
                var is_estimated = est_flags['startdate'] || est_flags['enddate'];
                var is_pinned = pin_flags['startdate'] || pin_flags['enddate'];
                var start_dt_imgurl = getGanttIconURL('singledate', typename, is_estimated, is_pinned);
                markup = "[SORTSTR:" + sortstr + "]<span class='gantt-bar' title='" + durationdays + " days'>"
                        + leftspacer_markup
                        + "<img title='one day " + start_dt + "' src='" + start_dt_imgurl + "' />" 
                        + "</span>";
            } else {
                //Two dates
                purpose_name = 'startdate';
                var start_dt_imgurl = getGanttIconURL(purpose_name, typename, est_flags[purpose_name], pin_flags[purpose_name]);

                purpose_name = 'enddate';
                var end_dt_imgurl = getGanttIconURL(purpose_name, typename, est_flags[purpose_name], pin_flags[purpose_name]);

                purpose_name = 'middle';
                var middle_bar_width = getBarMiddlePixelCount(start_dt, end_dt);
                var middle_markup = getBarMiddleMarkup(middle_bar_width, durationdays, typename, est_flags[purpose_name], pin_flags[purpose_name], is_bad);
                var extra_end_dt_info;
                if(middle_bar_width < 0)
                {
                    //Inidication that screen is too small!
                    extra_end_dt_info = " (this icon is not in ideal position due to small display space)";
                } else {
                    extra_end_dt_info = "";
                }
                markup = "[SORTSTR:" + sortstr + "]<span class='gantt-bar' title='" + durationdays + " days'>"
                        + leftspacer_markup
                        + "<img title='start " + start_dt + "' src='" + start_dt_imgurl + "' />" 
                        + middle_markup
                        + "<img title='end " + end_dt + extra_end_dt_info + "' src='" + end_dt_imgurl + "' />"
                        + "</span>";
            }

        }
        //markup = "[SORTNUM:" + durationdays + "]" + markup;
        return markup;
    }
    
    function getDaysBetweenDates(start_dt, end_dt)
    {
        if(start_dt === null || end_dt == null || start_dt == '' || end_dt == '' || start_dt.length < 10  || end_dt.length < 10)
        {
            return null;
        }
        var isoparts_start_dt = start_dt.split("-");
        var isoparts_end_dt = end_dt.split("-");
        
        var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
        var date1 = new Date(isoparts_start_dt[0],isoparts_start_dt[1],isoparts_start_dt[2]);
        var date2 = new Date(isoparts_end_dt[0],isoparts_end_dt[1],isoparts_end_dt[2]);
        
        return Math.ceil((date2.getTime() - date1.getTime())/(oneDay));
    };

    function getDayCountInDateRange(start_dt, end_dt)
    {
        
        if(start_dt === null || end_dt == null || start_dt == '' || end_dt == '' || start_dt.length < 10  || end_dt.length < 10)
        {
            return null;
        }
        return getDaysBetweenDates(start_dt, end_dt) + 1; //Because we count today!
    };

    max_days = getDayCountInDateRange(min_date, max_date);
    if(max_days < 1)
    {
        px_per_day = 0; //Cannot draw
    } else {
        px_per_day = (gantt_width - 22) / max_days;
    }

    var computedValue;
    var gantt_markup;
    var refreshAllRows = false;

    var values = controller.getRowValues(rowIndex);
    
    var value_branch_effort_hours = parseInt(values["branch_effort_hours_est"], 10);
    var value_effort_hours = parseInt(values["remaining_effort_hours"], 10);
    var value_start_dt = values["start_dt"];
    var value_end_dt = values["end_dt"];
    
    var value_typeletter = controller.getRowAttribute(rowIndex, "typeletter");
    var value_worked_hours_type_cd = controller.getRowAttribute(rowIndex, "data_worked_hours_type_cd");
    var value_effort_hours_est_locked_yn = controller.getRowAttribute(rowIndex, "data_effort_hours_est_locked_yn");
    
    var value_limit_branch_effort_hours_cd = values["limit_branch_effort_hours_cd"];
    var value_start_dt_type_cd = values["start_dt_type_cd"];
    var value_end_dt_type_cd = values["end_dt_type_cd"];
    var value_planned_start_dt_locked_yn = values["planned_start_dt_locked_yn"];
    var value_planned_end_dt_locked_yn = values["planned_end_dt_locked_yn"];
    var value_planned_fte_count = parseInt(values["planned_fte_count"], 10);
    
    var est_flags = {};
    est_flags["middle"] = value_worked_hours_type_cd === 'E';
    est_flags["startdate"] = value_start_dt_type_cd === 'E';
    est_flags["enddate"] = value_end_dt_type_cd === 'E';
    
    var pin_flags = {};
    pin_flags["middle"] = est_flags["middle"] && value_effort_hours_est_locked_yn == 1;
    pin_flags["startdate"] = est_flags["startdate"] && value_planned_start_dt_locked_yn == 1;
    pin_flags["enddate"] = est_flags["enddate"] && value_planned_end_dt_locked_yn == 1;
    
    var colidx_result = controller.getColumnIndex("calc_mfte");
    var colidx_gantt = controller.getColumnIndex("calc_gantt");

    if(value_start_dt == null || value_end_dt == null || value_start_dt.trim().length === 0 || value_end_dt.trim().length === 0)
    {
        computedValue = "[SORTNUM:0]<span title='Missing bounding date information'>No date(s)</span>";
        gantt_markup = '';
    } else {
        var basetype;
        var effort_for_calc;
        var sortnum = 0;
        var titletxt = '';
        var showtxt = '';
        var warnings = [];
        var titles = [];
        var warn_user = false;

        if(value_typeletter.startsWith("G") || value_typeletter.startsWith("P"))
        {
            basetype = 'G';
            //effort_for_calc = value_branch_effort_hours; // + value_effort_hours;
        } else {
            basetype = 'T';
            //effort_for_calc = value_effort_hours;
        }
        if(value_effort_hours > '')
        {
            effort_for_calc = value_effort_hours;
        } else {
            effort_for_calc = 0;
        }

        var getToday = function()
        {
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; //January is 0!
            var yyyy = today.getFullYear();

            if(dd<10) {
                dd='0'+dd
            } 

            if(mm<10) {
                mm='0'+mm
            } 

            return yyyy + '-' + mm + '-' + dd;
        };

        var est_hours_for_antecedents = value_branch_effort_hours - value_effort_hours;
        //var durationdays = getDayCountInDateRange(value_start_dt, value_end_dt);
        var today_dt = getToday();
        var durationdays;
        if(value_end_dt < today_dt)
        {
            durationdays = 'TOOLATE';
        } else {
            if(value_start_dt > today_dt)
            {
                durationdays = getDayCountInDateRange(value_start_dt, value_end_dt);
            } else {
                durationdays = getDayCountInDateRange(today_dt, value_end_dt);
            }
        }
        var computeGantt = false;
        
        if(effort_for_calc <= 0)
        {
            sortnum = 0;
            titles.push("Declaration of zero remaining effort for this workitem");
            showtxt = 'NA';// '0!';
        } else {
            if(durationdays === 'TOOLATE')
            {
                sortnum = -1;
                titles.push("Time is past to complete " + value_effort_hours + " hours of work?");
                warnings.push("zero future days of duration");
                computeGantt = true;
            } else
            if(durationdays === 0)
            {
                sortnum = -1;
                titles.push("Zero days to complete " + value_effort_hours + " hours of work?");
                warnings.push("zero days duration");
                computeGantt = true;
            } else if(durationdays < 0) {
                sortnum = -2;
                titles.push("Negative days to complete " + value_effort_hours + " hours of work?");
                warnings.push("date error");
            } else {
                var hoursPerFTE = 8;
                var daysofworkOneFTE = effort_for_calc / hoursPerFTE;
                computedValue = Math.round(100 * daysofworkOneFTE / durationdays)/100;
                sortnum = computedValue;
                titles.push("Computed for " + effort_for_calc + " hrs at " + hoursPerFTE + " hrs per FTE for " + durationdays + " days");
                showtxt = computedValue;
                if(computedValue > value_planned_fte_count)
                {
                    warn_user = true;
                }
                if(min_date > value_start_dt || max_date < value_end_dt)
                {
                    //We need to refresh ALL the rows because the margines have changed.
                    refreshAllRows = true;
                }
                computeGantt = true;
            }
        }
        if(value_limit_branch_effort_hours_cd == 'L')
        {
            if(est_hours_for_antecedents < 0)
            {
                var diff = -1 * est_hours_for_antecedents;
                titles.push("Branch hours estimate is " + diff + " hours smaller than hours for branch members!");
                warnings.push("branch hours estimate too small");
            }
        }
        var putdelim = false;
        for(var i=0; i< titles.length; i++)
        {
            if(putdelim)
            {
                titletxt += " and ";
            }
            titletxt += titles[i];
            putdelim=true;
        }

        if(warnings.length > 0)
        {
            if(showtxt.length > 0)
            {
                showtxt += "; ";
            }
            if(warnings.length === 1)
            {
                showtxt += "1 warning: ";
            } else {
                showtxt += "" + (warnings.length) + " warnings: ";
            }
            putdelim = false;
            for(var i=0; i< warnings.length; i++)
            {
                if(putdelim)
                {
                    showtxt += " and ";
                }
                showtxt += warnings[i];
                putdelim=true;
            }
        }
        gantt_markup = getGanttMarkup(value_typeletter, durationdays, value_start_dt, value_end_dt, est_flags, pin_flags);
        if(warn_user)
        {
            computedValue = "[SORTNUM:" + sortnum + "]<span class='colorful-warning' title='" + titletxt + "'>" + showtxt + "</span>";
        } else {
            computedValue = "[SORTNUM:" + sortnum + "]<span title='" + titletxt + "'>" + showtxt + "</span>";
        }
    }
    
    if(!allowFullGridRefresh || !refreshAllRows)
    {
        controller.setValueAt(rowIndex, colidx_result, computedValue, true);
        controller.setValueAt(rowIndex, colidx_gantt, gantt_markup, true);
    } else {
        var rowcount = controller.getRowCount();
        minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
        for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
        {
            bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo, false);  
        }
    }
};

bigfathom_util.table.customColumnsInit = function(controller)
{
    bigfathom_util.table.initCustomTableFilters(controller);
    $(window).resize(function() {
        var width = document.body.clientWidth;
        //alert("Look the window is now " + width);
        bigfathom_util.table.autoHideGanttCol(controller);
    });

    controller.setEnumProvider("limit_branch_effort_hours_cd", new EnumProvider({ 
        getOptionValuesForEdit: function (grid, column, rowIndex) {
            return { "I" : "Ignored", "U" : "Unlocked (adjustable)", "L" : "Locked (frozen)"};
        }
    }));
    controller.getColumn("limit_branch_effort_hours_cd").cellEditor.minWidth = 210;
    
    controller.setEnumProvider("start_dt_type_cd", new EnumProvider({ 
        getOptionValuesForEdit: function (grid, column, rowIndex) {
            return { "E" : "Estimated", "A" : "Actual"};
        }
    }));
    controller.getColumn("start_dt_type_cd").cellEditor.minWidth = 155;
    
    controller.setEnumProvider("end_dt_type_cd", new EnumProvider({ 
        getOptionValuesForEdit: function (grid, column, rowIndex) {
            return { "E" : "Estimated", "A" : "Actual"};
        }
    }));
    
    controller.getColumn("end_dt_type_cd").cellEditor.minWidth = 155;
};

bigfathom_util.table.saveRelationshipFormValues = function(controller, workitemid, getControlValues, setStatusMessage, the_dialog)
{
    var dataname = 'update_one_workitem';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(dataname);
    var alldata = getControlValues();
    var newdata = alldata.new;
    console.log("LOOK top of saveRelationshipFormValues for workitemid=" + workitemid);    
    var updateDatabase = function(newdata)
    {
        console.log("LOOK update db newdata=" + JSON.stringify(newdata));
        var analysis = newdata.analysis;
        if(!analysis.has_changes)
        {
            //No changes
            console.log("No changes on save: newdata=" + JSON.stringify(newdata));
            $(the_dialog).dialog("close"); 
        } else {
            var updatefields = {'maps':{}};

            var mapnames_ar = [];
            for(var mapname in newdata.maps)
            {
                if(mapname === 'ddw' || mapname === 'daw')
                {
                    updatefields.maps[mapname] = newdata.maps[mapname];
                    mapnames_ar.push(mapname);
                }
            }
            var sendpackage = {"dataname": dataname,
                               "databundle":
                                    {   
                                        "workitemid": workitemid,
                                        "updatefields": updatefields,
                                        "change_comment": 'changed ' + mapnames_ar.join(' and ')
                                    }
                                };
            console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
            var callbackActionFunction = function(callbackid, responseBundle)
            {
                console.log("LOOK modelChanged in callbackActionFunction responseBundle=" + JSON.stringify(responseBundle));
                //uiblocker.hide("tr#" + workitemid);
                //reloadAction(false);    
                //TODO --- Only close if save did not have issues!
                if(!responseBundle.hasOwnProperty('errorDetail') || responseBundle.errorDetail === null)
                {
                    console.log("Now update rows from analysis content " + JSON.stringify(analysis));
                    var colname;
                    var other_colname;
                    var relationshiptype;
                    var other_relationshiptype;
                    var wids2update = {};
                    var fast_map_removed = {};
                    var fast_map_added = {};
                    wids2update[workitemid] = workitemid;
                    if(analysis.hasOwnProperty('ddw'))
                    {
                        relationshiptype = 'ddw';
                        colname = 'ddw';
                        other_relationshiptype = 'daw';
                        other_colname = 'daw';
                    } else
                    if(analysis.hasOwnProperty('daw'))
                    {
                        relationshiptype = 'daw';
                        colname = 'daw';
                        other_relationshiptype = 'ddw';
                        other_colname = 'ddw';
                    } else {
                        throw "Did NOT recognize column name in analysis object!";
                    }
                    for(var j=0; j< analysis[relationshiptype].diffs.added.length; j++)
                    {
                        var a = analysis[relationshiptype].diffs.added[j];
                        var realint = parseInt(a);
                        wids2update[realint] = realint;
                        fast_map_added[realint] = realint;
                    }
                    for(var j=0; j< analysis[relationshiptype].diffs.removed.length; j++)
                    {
                        var a = analysis[relationshiptype].diffs.removed[j];
                        var realint = parseInt(a);
                        wids2update[realint] = realint;
                        fast_map_removed[realint] = realint;
                    }
                    console.log("wids2update=" + JSON.stringify(wids2update));
                    var rowcount = controller.getRowCount();
                    
                    function sortNumber(a,b)
                    {
                        return a - b;
                    }

                    var updateRelationshipCell = function(rowIndex, relationshiptype_name, added, removed)
                    {
                        var row_workitemid = controller.getRowId(rowIndex);
                        var colname = relationshiptype_name;
                        var colidx = controller.getColumnIndex(colname);
                        var attrib_name = 'data_' + relationshiptype_name;
                        var existing_widlist_tx = controller.getRowAttribute(rowIndex,attrib_name);
                        var existing_widlist_ar = existing_widlist_tx.split(',');
                        var clean_removed_map = {};
                        var clean_new_map = {};
                        console.log("LOOK at rowidx=" + rowIndex + " existing_widlist_tx=" + existing_widlist_tx + " colname=" + colname);                        
                        for(var i=0; i<removed.length; i++)
                        {
                            var realint = removed[i];//.trim();
                            //var realint = parseInt(raw_wid_tx);
                            clean_removed_map[realint] = realint;
                        }
                        for(var i=0; i<existing_widlist_ar.length; i++)
                        {
                            var raw_wid_tx = existing_widlist_ar[i];
                            if(raw_wid_tx !== null && raw_wid_tx.length > 0)
                            {
                                var realint = parseInt(raw_wid_tx.trim());
                                if(!clean_removed_map.hasOwnProperty(realint))
                                {
                                    clean_new_map[realint] = realint;
                                }
                            }
                        }
                        for(var i=0; i<added.length; i++)
                        {
                            var realint = added[i];
                            clean_new_map[realint] = realint;
                        }
                        var clean_new_ar = [];
                        for(var realint in clean_new_map)
                        {
                            clean_new_ar.push(realint);;
                        }
                        clean_new_ar.sort(sortNumber);
                        console.log("LOOK at rowidx=" + rowIndex + " clean_new_ar=" + JSON.stringify(clean_new_ar));         
                        var clean_new_ar_tx1 = clean_new_ar.join(',');
                        var clean_new_ar_tx2;
                        if(clean_new_ar.length === 0)
                        {
                            clean_new_ar_tx2 = " - ";
                        } else {
                            clean_new_ar_tx2 = clean_new_ar.join(', ');
                        }
                        
                        var new_display_sortnum = clean_new_ar.length;
                        var new_display_tooltip = "UPDATED " + new_display_sortnum + " links";
                        var new_display_onclick;
                        new_display_onclick = "bigfathom_util.table." + relationshiptype_name + "_edit(" + row_workitemid + ")";
                        //$ddw_onclick = "bigfathom_util.table.ddw_edit(" . $nativeid . ")";
                        var new_display_markup = "[SORTNUM:" + new_display_sortnum + "]"
                            + "<span class='click-for-action' title='" + new_display_tooltip + "' onclick='" + new_display_onclick + "'>"
                            + clean_new_ar_tx2 
                            + "</span>";
                        
                        console.log("LOOK at rowidx=" + rowIndex + " clean_new_ar_tx1=" + clean_new_ar_tx1);         
                        console.log("LOOK at rowidx=" + rowIndex + " clean_new_ar_tx2=" + clean_new_ar_tx2);         
                        controller.setRowAttribute(rowIndex, attrib_name, clean_new_ar_tx1);
                        controller.setValueAt(rowIndex, colidx, new_display_markup, true);
                        console.log("LOOK at rowidx=" + rowIndex + " FINISHED!");                        
                    };
                    
                    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
                    {
                        var row_workitemid = controller.getRowId(rowIndex);
                        var row_ddw_tx = controller.getRowAttribute(rowIndex,'data_ddw');
                        var row_daw_tx = controller.getRowAttribute(rowIndex,'data_daw');
                        var is_match = wids2update.hasOwnProperty(row_workitemid);
                        if(is_match)
                        {
                            console.log("LOOK check workitemid=[" + workitemid + "] row#" + rowIndex + " wid=[" + row_workitemid + "] is_match=[" + is_match + "] row_ddw=" + row_ddw_tx + " row_daw=" + row_daw_tx);
                            var clean_added_ar = [];
                            var clean_removed_ar = [];
                            if(row_workitemid == workitemid)    //=== fails here because the ID is not an int type instance
                            {
                                //Main item
                                console.log("LOOK MAIN MATCHED check workitemid=[" + workitemid + "] row#" + rowIndex + " wid=[" + row_workitemid + "] is_match=[" + is_match + "] row_ddw=" + row_ddw_tx + " row_daw=" + row_daw_tx);
                                clean_added_ar = analysis[relationshiptype].diffs.added;
                                clean_removed_ar = analysis[relationshiptype].diffs.removed;
                                updateRelationshipCell(rowIndex, relationshiptype, clean_added_ar, clean_removed_ar);
                            } else {
                                //Other item
                                console.log("LOOK OTHER MATCHED check workitemid=[" + workitemid + "] row#" + rowIndex + " wid=[" + row_workitemid + "] is_match=[" + is_match + "] row_ddw=" + row_ddw_tx + " row_daw=" + row_daw_tx);
                                if(fast_map_added.hasOwnProperty(row_workitemid))
                                {
                                    clean_added_ar.push(workitemid);
                                } else
                                if(fast_map_removed.hasOwnProperty(row_workitemid))
                                {
                                    clean_removed_ar.push(workitemid);
                                }
                                updateRelationshipCell(rowIndex, other_relationshiptype, clean_added_ar, clean_removed_ar);
                            }
                        }
                    }
                    $(the_dialog).dialog("close"); 
                } else {
                    setStatusMessage('Bad Input Failed Write!');
                }
            };
            //uiblocker.show("tr#" + workitemid);
            bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, workitemid);
        }
    };
    console.log("LOOK CHECK OK ... saveRelationshipFormValues for workitemid=" + workitemid);    
    if(newdata.validation.isokay)
    {
        console.log("LOOK IS OK saveRelationshipFormValues for workitemid=" + workitemid);    
        updateDatabase(newdata);
    }
    console.log("LOOK WAS OK??? saveRelationshipFormValues for workitemid=" + workitemid);    
};

bigfathom_util.table.setDependencyFormValues = function(controller, workitemid, setControlValues)
{
    var grab_dataname = 'one_workitem';
    var grab_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"nativeid": workitemid});

    var callbackMemberActionFunction = function(callbackid, responseBundle)
    {
        bigfathom_util.table.custominfo.branch_filtering.reference_wid = null;  //Disable the current filter before we start
        if(responseBundle.hasOwnProperty('data') && responseBundle.data !== null && responseBundle.data.hasOwnProperty('data'))
        {
            var record = responseBundle.data.data;
            console.log("LOOK WE HIT setDependencyFormValues CALLBACK " + JSON.stringify(responseBundle));
            setControlValues(record);
        }
    }
    //Get latest records from the server
    bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackMemberActionFunction, workitemid);
};

/**
 * Sets the special filter 'special_tag' attribute and special filter panel and triggers filtering
 */
bigfathom_util.table.setBranchMembershipFilter = function(controller, workitemid) 
{
    var grab_dataname = 'branch_members';
    var grab_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"workitemid": workitemid});

    var callbackMemberActionFunction = function(callbackid, responseBundle)
    {
        bigfathom_util.table.custominfo.branch_filtering.reference_wid = null;  //Disable the current filter before we start
        console.log("LOOK WE HIT CALLBACK " + JSON.stringify(responseBundle));
        if(responseBundle.hasOwnProperty('data') && responseBundle.data !== null && responseBundle.data.hasOwnProperty('data'))
        {
            var record = responseBundle.data.data;
            var map_wid_levels = {};
            var all_ants = record['maps']['ant']['wids'];
            var all_deps = record['maps']['dep']['wids'];
            var rowcount = controller.getRowCount();

            var willset_count = 0;
            var willset_root_detail;

            //Mark ANTs
            var ant_willset_count = 0;
            for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
            {
                var row_workitemid = controller.getRowId(rowIndex);
                if(workitemid === row_workitemid || all_ants.hasOwnProperty(row_workitemid))
                {
                    var info = all_ants[row_workitemid];
                    var level = info.level;
                    if(!map_wid_levels.hasOwnProperty(row_workitemid))
                    {
                        map_wid_levels[row_workitemid] = {'level':level};
                    } else {
                        if(map_wid_levels[row_workitemid].level < level)
                        {
                            map_wid_levels[row_workitemid].level = level;
                        }
                    }
                    ant_willset_count++;
                    if(workitemid === row_workitemid)
                    {
                        willset_root_detail = {"rowIndex": rowIndex, "wid": workitemid};
                        controller.setRowAttribute(rowIndex, 'special_tag', 'ref');
                    } else {
                        controller.setRowAttribute(rowIndex, 'special_tag', 'ant');
                    }
                } else {
                    controller.setRowAttribute(rowIndex, 'special_tag', null);
                }
            }

            //Mark DEPs
            var dep_willset_count = 0;
            for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
            {
                var row_workitemid = controller.getRowId(rowIndex);
                if(all_deps.hasOwnProperty(row_workitemid) && workitemid !== row_workitemid)
                {
                    var info = all_deps[row_workitemid];
                    var level = info.level;
                    if(!map_wid_levels.hasOwnProperty(row_workitemid))
                    {
                        map_wid_levels[row_workitemid] = {'level':level};
                    } else {
                        if(map_wid_levels[row_workitemid].level < level)
                        {
                            map_wid_levels[row_workitemid].level = level;
                        }
                    }
                    dep_willset_count++;
                    controller.setRowAttribute(rowIndex, 'special_tag', 'dep');
                }
            }

            willset_count = dep_willset_count + ant_willset_count + 1;

            console.log("LOOK dump willset_count=" + willset_count);
            console.log("LOOK dump dep_willset_count=" + dep_willset_count);
            console.log("LOOK dump ant_willset_count=" + ant_willset_count);

            var level_grouping = {};
            var min_level = null;
            var max_level = null;
            for(var wid in map_wid_levels)
            {
                var info = map_wid_levels[wid];
                var level = info.level;
                if(min_level === null || min_level > level)
                {
                    min_level = level;
                }
                if(max_level === null || max_level < level)
                {
                    max_level = level;
                }
                if(!level_grouping.hasOwnProperty(level))
                {
                    level_grouping[level] = [];
                }
                level_grouping[level].push(wid);
            }
            var level_sorted = [];
            for(var i=min_level; i<=max_level; i++)
            {
                if(level_grouping.hasOwnProperty(i))
                {
                    var info = level_grouping[i];
                    level_sorted.push(info);
                }
            }

            //Now set the filter box etc
            bigfathom_util.table.custominfo.branch_filtering.level_sorted = level_sorted;
            bigfathom_util.table.custominfo.branch_filtering.reference_wid = workitemid;
            $('#branch_filter_area_legend').text('Branch Filter (reference is #'  + workitemid + ')');
            $('#branch_filter_area').show();
            var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
            var branchradio_groupname = filterareaid + '_branchgroup';
            $('input:radio[name=' + branchradio_groupname + '][value=all]').prop('checked',true);
            bigfathom_util.table.invokeFilter();
            bigfathom_util.table.finalizeAllGridCells(controller);
        }
    };

    //Get latest records from the server
    bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackMemberActionFunction, workitemid);
};

bigfathom_util.table.cellClicked = function(controller, rowIndex, columnIndex) 
{
    if(columnIndex === 0)
    {
        //Filter on cell with this workitem at the root
        var workitemid = controller.getRowId(rowIndex);
        bigfathom_util.table.setBranchMembershipFilter(controller, workitemid);
    }
};

//Tell us if it is okay to edit a specific cell
bigfathom_util.table.isEditable = function(controller, rowIndex, columnIndex) 
{
    var allowrowedit = controller.getRowAttribute(rowIndex, "allowrowedit");
    
    var column = controller.getColumn(columnIndex);
    if(!column.editable || allowrowedit != "1")
    {
        return false;
    }
    
    var result = true;
    var values = controller.getRowValues(rowIndex);
    var colName = controller.getColumnName(columnIndex);
    var otherColName=null;
    var depValue=null;
    
    if(colName === "branch_effort_hours_est")
    {
        otherColName = "limit_branch_effort_hours_cd";
        depValue = values[otherColName];
        if(depValue == 'I')
        {
            var colidx_branch_effort_hours_est = controller.getColumnIndex('branch_effort_hours_est');
            controller.setValueAt(rowIndex, colidx_branch_effort_hours_est, null, true);
            result = false;
        }
    } else
    if(colName === "remaining_effort_hours")
    {
        var depValue = controller.getRowAttribute(rowIndex, "status_terminal_yn");        
        if(depValue == 1)
        {
            result = false;
        }
    } else
    if(colName === "branch_effort_hours_est_locked_yn")
    {
        /*
        otherColName = "typeletter";
        depValue = bigfathom_util.table.getTypeLetterFromMarkup(values[otherColName]);
        if(depValue !== 'G' && depValue !== 'P')
        {
            result = false;
        }
        */
    } else
    if(colName === "effort_hours_est_locked_yn")
    {
        otherColName = "worked_hours_type_cd";
        depValue = values[otherColName];
        if(depValue === 'A')
        {
            result = false;
        }
    } else
    if(colName === "planned_start_dt_locked_yn")
    {
        otherColName = "start_dt_type_cd";
        depValue = values[otherColName];
        if(depValue === 'A')
        {
            result = false;
        }
    } else 
    if(colName === "planned_end_dt_locked_yn")
    {
        otherColName = "end_dt_type_cd";
        depValue = values[otherColName];
        if(depValue === 'A')
        {
            result = false;
        }
    }
    return result;
};


bigfathom_util.table.autofill_wbs = function(action_url, projectid, return_url, rparams) 
{

    bigfathom_util.dialog.initialize("dialog-confirm-autofill-wbs");
    //$( "#blurb-confirm-autofill-wbs" ).html(markupblurb + markupdetail);
    $( "#dialog-confirm-autofill-wbs" ).dialog("option", "buttons", {
            "Apply Changes": function() {
                var action_args = [];
                var flags = autofillGetFlagArray();
                for (var flagname in flags) 
                {
                    if (flags.hasOwnProperty(flagname)) 
                    {
                        action_args[flagname] = flags[flagname];
                    }
                }
                action_args['projectid'] = projectid;
                action_args['return'] = return_url; //encodeURI(return_url);
                action_args['rparams'] = rparams;
                var final_action_url = bigfathom_util.url.getUrl(action_url,action_args);
                
                //alert("LOOK TODO launch url=" + final_action_url);
                
                window.location.href = final_action_url;

                $(this).dialog("close");
            },
            "Cancel Request": function() {
                    $(this).dialog("close"); 
            }
    });
    autofillInitializeControls();   //Function is part of the parent form
    $( "#dialog-confirm-autofill-wbs" ).dialog("open");
};

bigfathom_util.table.ddw_edit = function(workitemid) 
{
    var typename = 'ddw';
    bigfathom_util.table.wi2wi_edit(bigfathom_util.table.default_controller,typename, workitemid);
};

bigfathom_util.table.daw_edit = function(workitemid) 
{
    var typename = 'daw';
    bigfathom_util.table.wi2wi_edit(bigfathom_util.table.default_controller,typename, workitemid);
};

bigfathom_util.table.wi2wi_edit = function(controller,typename, workitemid) 
{
    var dialogid = 'dialog-edit-' + typename;
    bigfathom_util.dialog.initialize(dialogid);
    var dialogselector_tx = "#" + dialogid;
    $( dialogselector_tx ).dialog("option", "buttons", {
            "Save": function() {
                if(typename === 'ddw')
                {
                    //values = editDDWGetValues();   //Function is part of the parent form
                    bigfathom_util.table.saveRelationshipFormValues(controller, workitemid, editDDWGetValues, editDDWSetStatusMessage, this); 
                } else
                if(typename === 'daw')
                {
                    //values = editDAWGetValues();   //Function is part of the parent form
                    bigfathom_util.table.saveRelationshipFormValues(controller, workitemid, editDAWGetValues, editDAWSetStatusMessage, this); 
                } else {
                    throw "Did NOT recognize typename=" + typename;
                }
            },
            "Cancel": function() {
                    $(this).dialog("close"); 
            }
    });
    if(typename === 'ddw')
    {
        editDDWInitializeControls(workitemid);   //Function is part of the parent form
    } else
    if(typename === 'daw')
    {
        editDAWInitializeControls(workitemid);   //Function is part of the parent form
    } else {
        throw "Did NOT recognize typename=" + typename;
    }
    $( dialogselector_tx ).dialog("open");
    if(typename === 'ddw')
    {
        //editDDWSetControlValues(record);   //Function is part of the parent form
        bigfathom_util.table.setDependencyFormValues(controller, workitemid, editDDWSetControlValues); 
    } else
    if(typename === 'daw')
    {
        //editDAWSetControlValues(record);   //Function is part of the parent form
        bigfathom_util.table.setDependencyFormValues(controller, workitemid, editDAWSetControlValues); 
    } else {
        throw "Did NOT recognize typename=" + typename;
    }
};

// The function that will handle model changes
bigfathom_util.table.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }

    var writeback = true;
    var reload = true;
    
    var colname = controller.getColumnName(columnIndex);

    var new_min_date = false;
    var new_max_date = false;
    if(typeof controller.bigfathom_minmax !== 'undefined')
    {
        //Do NOT check for greater/less, instead check for match!
        if(colname === 'start_dt')
        {
            if(controller.bigfathom_minmax.min_date === oldValue)
            {
                new_min_date = true;
            }
        } else
        if(colname === 'end_dt')
        {
            if(controller.bigfathom_minmax.max_date === oldValue)
            {
                new_max_date = true;
            }
        }
    }

    var change_comment = "grid edit has changed from '" + oldValue + "' to '" + newValue;
    var workitemid = controller.getRowId(rowIndex);
    var values = controller.getRowValues(rowIndex);
    //var value_typeletter = bigfathom_util.table.getTypeLetterFromMarkup(values["typeletter"]);
    var value_typeletter = controller.getRowAttribute(rowIndex, "typeletter");
    
    var dataname = 'update_one_workitem';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(dataname);
    var grab_fullurl = bigfathom_util.data.getGrabDataUrl('one_workitem',{"type_cd": value_typeletter, "nativeid": workitemid});

    var value_worked_hours_type_cd = values["worked_hours_type_cd"];
    var value_start_dt_type_cd = values["start_dt_type_cd"];
    var value_end_dt_type_cd = values["end_dt_type_cd"];

    if(colname === 'worked_hours_type_cd' || colname === 'start_dt_type_cd' || colname === 'end_dt_type_cd')
    {
        if(newValue === 'A')
        {
            //Force the grid to show locked status
            var colname_locked_yn = colname === 'worked_hours_type_cd' ? 'effort_hours_est_locked_yn' : colname === 'start_dt_type_cd' ? 'planned_start_dt_locked_yn' : 'planned_end_dt_locked_yn';
            var colidx_locked_yn = controller.getColumnIndex(colname_locked_yn);
            controller.setValueAt(rowIndex, colidx_locked_yn, true, true);
        }
        reload = true;
        writeback = false;
    } else
    if(colname === 'effort_hours')
    {
        colname = 'effort_hours_est';
    } else
    if(colname === 'worked_hours')
    {
        if(value_worked_hours_type_cd === 'A')
        {
            colname = 'effort_hours_worked_act';
        } else {
            colname = 'effort_hours_worked_est';
        }
    } else
    if(colname === 'start_dt')
    {
        if(value_start_dt_type_cd === 'A')
        {
            colname = 'actual_start_dt';
        } else {
            colname = 'planned_start_dt';
        }
    } else
    if(colname === 'end_dt')
    {
        if(value_end_dt_type_cd === 'A')
        {
            colname = 'actual_end_dt';
        } else {
            colname = 'planned_end_dt';
        }
    } else
    if(colname === 'planned_start_dt_locked_yn')
    {
        if(value_start_dt_type_cd === 'A')
        {
            writeback = false;
        }
        if(newValue)
        {
            newValue = 1;
        } else {
            newValue = 0;
        }
    } else
    if(colname === 'planned_end_dt_locked_yn')
    {
        if(value_end_dt_type_cd === 'A')
        {
            writeback = false;
        }
        if(newValue)
        {
            newValue = 1;
        } else {
            newValue = 0;
        }
    } else
    if(colname === 'effort_hours_est_locked_yn')
    {
        if(value_worked_hours_type_cd === 'A')
        {
            writeback = false;
        }
        if(newValue)
        {
            newValue = 1;
        } else {
            newValue = 0;
        }
    }
    
    var reloadAction = function(controlBlocking)
    {
        if(typeof controlBlocking === 'undefined')
        {
            controlBlocking = true;
        }
        
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            var record = responseBundle.data.data;
            //if(colname !== 'effort_hours_est_locked_yn')
            {
                var value_worked_hours;
                var value_branch_effort_hours = record["branch_effort_hours_est"];
                var value_remaining_effort_hours = record['remaining_effort_hours'];
                var colidx_locked_yn = controller.getColumnIndex('effort_hours_est_locked_yn');
                var value_locked_yn = record['effort_hours_est_locked_yn'] == 1 ? true : false;
                controller.setValueAt(rowIndex, colidx_locked_yn, value_locked_yn, true);
                if(value_worked_hours_type_cd === 'E')
                {
                    value_worked_hours = record['effort_hours_worked_est'];
                } else {
                    value_worked_hours = record['effort_hours_worked_act'];
                }
                var colidx_branch_effort_hours_est = controller.getColumnIndex('branch_effort_hours_est');
                controller.setValueAt(rowIndex, colidx_branch_effort_hours_est, value_branch_effort_hours, true);
                var colidx_remaining_effort_hours = controller.getColumnIndex('remaining_effort_hours');
                controller.setValueAt(rowIndex, colidx_remaining_effort_hours, value_remaining_effort_hours, true);
                var colidx_worked_hours = controller.getColumnIndex('worked_hours');
                controller.setValueAt(rowIndex, colidx_worked_hours, value_worked_hours, true);
            }
            //if(colname !== 'planned_start_dt_locked_yn')
            {
                var value_start_dt;
                if(value_start_dt_type_cd === 'E')
                {
                    value_start_dt = record['planned_start_dt'];
                    var colidx_locked_yn = controller.getColumnIndex('planned_start_dt_locked_yn');
                    var value_locked_yn = record['planned_start_dt_locked_yn'] == 1 ? true : false;
                    controller.setValueAt(rowIndex, colidx_locked_yn, value_locked_yn, true);
                } else {
                    value_start_dt = record['actual_start_dt'];
                }
                var colidx_start_dt = controller.getColumnIndex('start_dt');
                controller.setValueAt(rowIndex, colidx_start_dt, value_start_dt, true);
            }
            //if(colname !== 'planned_end_dt_locked_yn')
            {
                var value_end_dt;
                if(value_end_dt_type_cd === 'E')
                {
                    value_end_dt = record['planned_end_dt'];
                    var colidx_locked_yn = controller.getColumnIndex('planned_end_dt_locked_yn');
                    var value_locked_yn = record['planned_end_dt_locked_yn'] == 1 ? true : false;
                    controller.setValueAt(rowIndex, colidx_locked_yn, value_locked_yn, true);
                } else {
                    value_end_dt = record['actual_end_dt'];
                }
                var colidx_end_dt = controller.getColumnIndex('end_dt');
                controller.setValueAt(rowIndex, colidx_end_dt, value_end_dt, true);
            }

            if(new_min_date || new_max_date)
            {
                //Redraw all the rows!
                bigfathom_util.table.finalizeAllGridCells(controller);
            } else {
                //Just recompute for this row
                bigfathom_util.table.recomputeFormulas(controller, rowIndex);
                bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);
                var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
                bigfathom_util.table.showTotals(controller, minmaxinfo);
            }
            if(controlBlocking)
            {
                uiblocker.hide("tr#" + workitemid);
            }
        };
        
        //Get latest record from the server
        if(controlBlocking)
        {
            uiblocker.show("tr#" + workitemid);
        }
        bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, workitemid);
    };
    
    if(writeback)
    {
        //Update the server
        var updatefields = {};
        updatefields[colname] = newValue;
        var sendpackage = {"dataname": dataname,
                           "databundle":
                                {   
                                    "workitemid": workitemid,
                                    "updatefields": updatefields,
                                    "change_comment": change_comment
                                }
                            };
        console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            console.log("LOOK modelChanged in callbackActionFunction responseBundle=" + JSON.stringify(responseBundle));
            uiblocker.hide("tr#" + workitemid);
            reloadAction(false);    
        };
        uiblocker.show("tr#" + workitemid);
        bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, workitemid);
    } else 
    if(reload)
    {
        reloadAction();
    }
};
