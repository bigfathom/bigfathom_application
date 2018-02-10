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
        "version": "20170605.1",
        "lowest_default_scf": 0.01,
        "new_member_default_dc": 0.95,
        "rounding_factor": 10000,
        "custominfo": {}
    };
}

bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    var rowcount = controller.getRowCount();
    var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo);  
        bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);  
    }
    
    bigfathom_util.table.showTotals(controller, minmaxinfo);
};

bigfathom_util.table.customRowMatchFunction = function(controller, row)
{
    var matches = true;
    //TODO
    return matches;
};

bigfathom_util.table.initCustomTableFilters = function(controller) 
{
    
    bigfathom_util.table.custominfo.personid = Drupal.settings.personid;
    bigfathom_util.table.custominfo.map_status = JSON.parse(Drupal.settings.map_status);
    bigfathom_util.table.custominfo.map_people = JSON.parse(Drupal.settings.map_people);
    bigfathom_util.table.custominfo.sprint_record = JSON.parse(Drupal.settings.sprint_record);
    
    //console.log("LOOK map_people=" + JSON.stringify(bigfathom_util.table.custominfo.sprint_record));
    //alert("LOOK did we sprint_record?");

};

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var membercount = minmaxinfo.member_count;
    if(membercount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no workitem members in this sprint.</div>";
    } else {
        var effort = minmaxinfo.total_effort;
        
        var lowconfidence = minmaxinfo.min_scf;
        var highconfidence = minmaxinfo.max_scf;
        var simple_avg_scf =  Math.round(minmaxinfo.simple_avg_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        var effort_weighted_avg_scf = Math.round(minmaxinfo.effort_weighted_avg_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        
        var lowimportance = minmaxinfo.min_imp;
        var highimportance = minmaxinfo.max_imp;
        var simple_avg_imp =  Math.round(minmaxinfo.simple_avg_imp * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        var effort_weighted_avg_imp = Math.round(minmaxinfo.effort_weighted_avg_imp * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        
        var markup_totals = "<fieldset class='elem-inline'><legend title='Simple totals of the sprint member workitems'>Totals</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='membercount'>Members:</label><span class='showvalue' id='membercount'>" + membercount + "</span></div>" 
                + "<div class='inline'><label for='membereffort'>Effort Hours:</label><span class='showvalue' id='membereffort'>" + effort + "</span></div>" 
                + "</div></fieldset>";
        var markup_importance = "<fieldset class='elem-inline'><legend title='The declared importance of the sprint member workitems'>Importance</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Workitems with most effort hours influence this average more than lower effort workitems' for='effort_weighted_avg_imp'>Effort Weighted Average:</label><span class='showvalue' id='effort_weighted_avg_imp'>" + effort_weighted_avg_imp + "</span></div>" 
                + "<div class='inline'><label title='Simple mean between the lowest and the highest value' for='simple_avg_imp'>Simple Average:</label><span class='showvalue' id='simple_avg_imp'>" + simple_avg_imp + "</span></div>" 
                + "<div class='inline'><label for='lowimportance'>Lowest:</label><span class='showvalue' id='lowimportance'>" + lowimportance + "</span></div>" 
                + "<div class='inline'><label for='highimportance'>Highest:</label><span class='showvalue' id='highimportance'>" + highimportance + "</span></div>" 
                + "</div></fieldset>";
        var markup_iscp = "<fieldset class='elem-inline'><legend title='The calculated probability that the member workitems will be completed in the sprint period'>In-Sprint Completion Probability</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Workitems with most effort hours influence this average more than lower effort workitems' for='effort_weighted_avg_scf'>Effort Weighted Average:</label><span class='showvalue' id='effort_weighted_avg_scf'>" + effort_weighted_avg_scf + "</span></div>" 
                + "<div class='inline'><label title='Simple mean between the lowest and the highest value' for='simple_avg_scf'>Simple Average:</label><span class='showvalue' id='simple_avg_scf'>" + simple_avg_scf + "</span></div>" 
                + "<div class='inline'><label for='lowconfidence'>Lowest:</label><span class='showvalue' id='lowconfidence'>" + lowconfidence + "</span></div>" 
                + "<div class='inline'><label for='highconfidence'>Highest:</label><span class='showvalue' id='highconfidence'>" + highconfidence + "</span></div>" 
                + "</div></fieldset>";
        
        //Create the people allocation markup
        var sprint_start_dt = bigfathom_util.table.custominfo.sprint_record.start_dt;
        var sprint_end_dt = bigfathom_util.table.custominfo.sprint_record.end_dt;
        
        var sprint_all_days = bigfathom_util.browser_grid_helper.utility.getDaysBetweenDates(sprint_start_dt, sprint_end_dt);
        var sprint_work_days = bigfathom_util.browser_grid_helper.utility.getWorkdaysBetweenDates(sprint_start_dt, sprint_end_dt);
        
        var map_people = bigfathom_util.table.custominfo.map_people;
        var primary_owner_allocation = minmaxinfo.primary_owner_allocation;
        var delegate_owner_allocation = minmaxinfo.delegate_owner_allocation;
        var primary_owner_markup_ar = [];
        var delegate_detail;
        var delegate_blurb;
        for (var personid in primary_owner_allocation) 
        {
            if (delegate_owner_allocation.hasOwnProperty(personid)) 
            {
                delegate_detail = delegate_owner_allocation[personid];
                delegate_blurb = "" + delegate_detail.itemcount 
                        + " delegate ownerships with " 
                        + delegate_detail.reffort 
                        + " remaining effort hours";
            } else {
                delegate_detail = {"reffort":0,"itemcount":0};
                delegate_blurb = "no delegate ownerships";
            }
            if (primary_owner_allocation.hasOwnProperty(personid)) 
            {
                var primary_detail = primary_owner_allocation[personid];
                var labelid = "reffort4_" + personid;
                var reffort = primary_detail.reffort;
                var eperd_all = sprint_all_days > 0 ? (reffort / sprint_all_days) : 9999;
                var eperd_work = sprint_work_days > 0 ? (reffort / sprint_work_days) : 9999;
                var riskcode = eperd_all > 8 ? 1 : (eperd_work < 8 ? -1 : 0);
                var classname = riskcode > 0 ? "status-happy-no" : (riskcode < 0 ? "status-happy-yes" : "");
                var risktip = riskcode > 0 ? "(requires working more than " + Math.round(eperd_all) + " hours every day of the week!) " : "";
                var person = map_people[personid];
                var personname = person.first_nm + " " + person.last_nm;
                var blurb = "Total of " + reffort + " hours for " + primary_detail.itemcount + " owned workitems " + risktip + "and " + delegate_blurb;
                var markup = "<div title='" + blurb +  "' class='inline " + classname + " '><label for='" + labelid + "'>" 
                        + personname + ":</label><span class='showvalue' id='" + labelid + "'>" 
                        + reffort + "</span></div>";

                primary_owner_markup_ar.push(markup);
            }
        }
        var markup_effort_allocations = "<fieldset class='elem-inline'><legend title='Remaining effort allocation to workitem owners'>Effort Allocations</legend><div class='group-standard'>" 
                + primary_owner_markup_ar.join(" ");
                + "</div></fieldset>";
                
        markup = "<div class='grid-totals-container'>" 
                + markup_totals 
                + markup_importance 
                + markup_iscp
                + markup_effort_allocations + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var primary_owner_allocation = {};
    var delegate_owner_allocation = {};
    var total_effort = 0;
    var member_count = 0;
    var min_scf = 1;
    var max_scf = 0;
    var effort_weighted_scf_sum = 0;
    
    var min_imp = 100;
    var max_imp = 0;
    var effort_weighted_imp_sum = 0;
    
    var min_dt = '';
    var max_dt = '';
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var value_primary_owner = parseInt(controller.getRowAttribute(rowIndex, "data_primary_owner"));
        var value_all_owners = controller.getRowAttribute(rowIndex, "data_all_owners");
        var value_computed_confidence = parseFloat(controller.getRowAttribute(rowIndex, "data_iscp"));
        var ar_all_owners = value_all_owners.split(',');
        var value_start_dt = values["start_dt"];
        var value_end_dt = values["end_dt"];
        var value_is_member_yn = values["is_member_yn"];
        var value_importance = values["importance"];
        var value_effort_hours = parseInt(values["remaining_effort_hours"]);

        if(value_is_member_yn)
        {
            member_count++;
            if(!primary_owner_allocation.hasOwnProperty(value_primary_owner))
            {
                primary_owner_allocation[value_primary_owner] = {"reffort":0,"itemcount":0};
            }
            primary_owner_allocation[value_primary_owner].reffort += value_effort_hours;
            primary_owner_allocation[value_primary_owner].itemcount++;
            for(var doi=0; doi<ar_all_owners.length; doi++)
            {
                var doid = ar_all_owners[doi];
                if(doid !== primary_owner_allocation)
                {
                    if(!delegate_owner_allocation.hasOwnProperty(doid))
                    {
                        delegate_owner_allocation[doid] = {"reffort":0,"itemcount":0};
                    }
                    delegate_owner_allocation[doid].reffort += value_effort_hours;
                    delegate_owner_allocation[doid].itemcount++;
                }
            }
            if(value_effort_hours >= 1)
            {
                total_effort += value_effort_hours;
                if(value_computed_confidence > 0)
                {
                    effort_weighted_scf_sum += value_effort_hours * value_computed_confidence;
                }
                if(value_importance > 0)
                {
                    effort_weighted_imp_sum += value_effort_hours * value_importance;
                }
            } else {
                if(value_effort_hours > 0)
                {
                    total_effort += value_effort_hours;
                }
                
                //Don't adjust for less than 1 hour of effort.
                if(value_computed_confidence > 0)
                {
                    effort_weighted_scf_sum += value_effort_hours;
                }
                if(value_importance > 0)
                {
                    effort_weighted_imp_sum += value_effort_hours;
                }
            }
            if(value_computed_confidence > max_scf)
            {
                max_scf = value_computed_confidence;    
            }
            if(value_computed_confidence < min_scf)
            {
                min_scf = value_computed_confidence;   
            }
            
            if(value_importance > max_imp)
            {
                max_imp = value_importance;    
            }
            if(value_importance < min_imp)
            {
                min_imp = value_importance;   
            }
            
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
    }
    var simple_avg_scf = (min_scf + max_scf) / 2;
    var effort_weighted_avg_scf;
    if(member_count > 0)
    {
        if(total_effort > 0.1)
        {
            effort_weighted_avg_scf = (effort_weighted_scf_sum / total_effort);// / member_count;
        } else {
            effort_weighted_avg_scf = null;
        }
    } else {
        simple_avg_scf = null;
        effort_weighted_avg_scf = null;
        min_scf = null;
        min_scf = null;
        min_scf = null;
    }
    
    var simple_avg_imp = (min_imp + max_imp) / 2;
    var effort_weighted_avg_imp;
    if(member_count > 0)
    {
        if(total_effort > 0.1)
        {
            effort_weighted_avg_imp = (effort_weighted_imp_sum / total_effort);// / member_count;
        } else {
            effort_weighted_avg_imp = null;
        }
    } else {
        simple_avg_imp = null;
        effort_weighted_avg_imp = null;
        min_imp = null;
        min_imp = null;
        min_imp = null;
    }
    
    var themap = {'min_date':min_dt, 'max_date':max_dt
        , 'member_count': member_count
        , 'total_effort': total_effort
        , 'min_scf': min_scf
        , 'max_scf': max_scf
        , 'simple_avg_scf': simple_avg_scf
        , 'effort_weighted_avg_scf': effort_weighted_avg_scf
        , 'min_imp': min_imp
        , 'max_imp': max_imp
        , 'simple_avg_imp': simple_avg_imp
        , 'effort_weighted_avg_imp': effort_weighted_avg_imp
        , 'primary_owner_allocation': primary_owner_allocation
        , 'delegate_owner_allocation': delegate_owner_allocation
    };

    return themap;
};

bigfathom_util.table.getTypeLetterFromMarkup = function(typeletter_markup)
{
    if(typeletter_markup === null)
    {
        return null;
    }
    var clean = typeletter_markup.trim();
    if(clean === '')
    {
        return null;
    }
    return clean.substring(0,1);
};


/**
 * Call this to update the indicators of which cells in a row are editable
 * The core framework does NOT have a comparable method that is called 
 * that would CHANGE the markings of other cells on a row.
 */
bigfathom_util.table.recomputeEditableMarkings = function(controller, rowIndex) 
{
    var workitemid = controller.getRowId(rowIndex);
    var values = controller.getRowValues(rowIndex);
    var value_typeletter = bigfathom_util.table.getTypeLetterFromMarkup(values["typeletter"]);
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        var colname = column.name;
        if(colname.startsWith('is_member_yn'))
        {
            if(bigfathom_util.table.isEditable(controller, rowIndex, columnIndex))
            {
                controller.markCellEditable(rowIndex, columnIndex);
            } else {
                controller.markCellLocked(rowIndex, columnIndex);
            }
        }
        if(colname.startsWith('branch_'))
        {
            if(value_typeletter !== 'G' && value_typeletter !== 'P')
            {
                controller.markCellNotApplicable(rowIndex, columnIndex);
            } else {
                controller.markCellApplicable(rowIndex, columnIndex, false);
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
    var max_days;
    var colidx_planned_fte_count = controller.getColumnIndex("planned_fte_count");
    
    function getImageURL(filename)
    {
        return url_img + "/" + filename;
    }

    max_days = bigfathom_util.browser_grid_helper.utility.getDaysBetweenDates(min_date, max_date);

    var refreshAllRows = false;

    var values = controller.getRowValues(rowIndex);
    var value_dfte = controller.getRowAttribute(rowIndex, "data_dfte");
    var value_planned_start_dt = values['planned_start_dt'];
    var value_planned_end_dt = values['planned_end_dt'];
    var value_remaining_effort_hours = parseInt(values['remaining_effort_hours']);
   
    var dfte_markup; 
    var days = bigfathom_util.browser_grid_helper.utility.getDaysBetweenDates(value_planned_start_dt, value_planned_end_dt);
    if(days > 0)
    {
        var need_hours_per_day = value_remaining_effort_hours / days;
        var planned_hours_per_day = value_dfte * 8;
        if(planned_hours_per_day >= need_hours_per_day)
        {
            dfte_markup = "[SORTNUM:" + 100*value_dfte + "]<span title=''>" + value_dfte + "</span>";
        } else {
            var cfte = Math.round(100*(need_hours_per_day / 8))/100;
            if(cfte > value_dfte)
            {
                dfte_markup = "[SORTNUM:" + (100*value_dfte -1) + "]<span class='colorful-warning' title='Computed FTE as " + cfte + " exceeds planned value!'>" + value_dfte + "</span>";
            } else {
                //Can happen if the difference is very small!!!
                dfte_markup = "[SORTNUM:" + (100*value_dfte -1) + "]<span class='colorful-warning' title='Computed FTE slightly exceeds planned value!'>" + value_dfte + "</span>";
            }    
        }
    } else {
        dfte_markup = "[SORTNUM:" + 100*value_dfte + "]<span title=''>" + value_dfte + "</span>";
    }
    
    if(!allowFullGridRefresh || !refreshAllRows)
    {
        controller.setValueAt(rowIndex, colidx_planned_fte_count, dfte_markup, true);
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

    $(window).resize(function() 
    {
        var width = document.body.clientWidth;
        //alert("Look the window is now " + width);
    });
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
    return true;
};

/**
 * Only sets the display NOT the database.
 */
bigfathom_util.table.setOneRowMembership = function(controller, rowIndex, make_member)
{
    console.log("LOOK we called setOneRowMembership for rowIndex=" + rowIndex);    

    var workitemid = controller.getRowId(rowIndex);
    var row_values = controller.getRowValues(rowIndex);
    var value_is_member_yn = row_values["is_member_yn"];
    var value_computed_confidence = row_values["computed_confidence"];
    var default_scf = controller.getRowAttribute(rowIndex, "default_scf");
    var colidx_is_member_yn = controller.getColumnIndex('is_member_yn');
    var colidx_ot_scf = controller.getColumnIndex('ot_scf');
    var colidx_computed_confidence = controller.getColumnIndex('computed_confidence');;
    var result_bundle = {};
    result_bundle['membership_changed'] = (value_is_member_yn !== make_member);
    if(make_member)
    {
        var value_ot_scf;
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_computed_confidence = default_scf;
        } else {
            value_computed_confidence = bigfathom_util.table.lowest_default_scf;
        }
    } else {
        value_ot_scf = null;
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_computed_confidence = default_scf;
        } else {
            //Blank it out
            value_computed_confidence = null;
        }
    }
    if(value_ot_scf === null || isNaN(value_ot_scf) )
    {
        controller.setValueAt(rowIndex, colidx_ot_scf, '', true);
    } else {
        var value_rounded_scf = Math.round(value_ot_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        controller.setValueAt(rowIndex, colidx_ot_scf, value_rounded_scf, true);
    }
    if(value_computed_confidence === null || isNaN(value_computed_confidence) )
    {
        controller.setValueAt(rowIndex, colidx_computed_confidence, '', true);
    } else {
        var value_rounded_scf = Math.round(value_computed_confidence * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        controller.setValueAt(rowIndex, colidx_computed_confidence, value_rounded_scf, true);
    }
    controller.setValueAt(rowIndex, colidx_is_member_yn, make_member, true);
    result_bundle['workitemid'] = workitemid;
    result_bundle['ot_scf'] = value_ot_scf;
    result_bundle['computed_confidence'] = value_computed_confidence;
    return result_bundle;
};

/**
 * Sets the display AND the database.
 */
bigfathom_util.table.setBranchMembership = function(controller, workitemid, uiblocker, make_member_yn) 
{
    var send_dataname = 'update_sprint_members';
    var grab_dataname = 'branch_members';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    var grab_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"workitemid": workitemid});

    var callbackMemberActionFunction = function(callbackid, responseBundle)
    {
        if(responseBundle.hasOwnProperty('data') && responseBundle.data != null && responseBundle.data.hasOwnProperty('data'))
        {
            var record = responseBundle.data.data;
            var all_ants = record['maps']['ant']['wids'];
            var all_deps = record['maps']['dep']['wids'];
            var rowcount = controller.getRowCount();
            var changed_memberships = {};

            var performSelectionUpdates = function(willset_detail)
            {
                //Set all the members
                var sprintid = null;
                var setcounter = 0;
                for (var row_workitemid in willset_detail) 
                {
                    if (willset_detail.hasOwnProperty(row_workitemid)) 
                    {
                        var detail = willset_detail[row_workitemid];
                        var one_row_result = bigfathom_util.table.setOneRowMembership(controller, detail.rowIndex, make_member_yn);
                        if(workitemid == row_workitemid || one_row_result['membership_changed'])
                        {
                            if(sprintid === null)
                            {
                                sprintid = controller.getRowAttribute(detail.rowIndex, "data_sprintid");
                            }
                            changed_memberships[row_workitemid] = one_row_result;
                        }
                        setcounter++;
                    }
                }
                
                //Now update the database if we made changes
                if(sprintid !== null)
                {
                    var sendpackage;
                    if(make_member_yn)
                    {
                        sendpackage = {"dataname": send_dataname,
                                           "databundle":
                                                {   "sprintid" : sprintid,
                                                    "memberships": {"new" : changed_memberships, "default_confidence" : bigfathom_util.table.new_member_default_dc}
                                                }
                                            };
                    } else {
                        sendpackage = {"dataname": send_dataname,
                                           "databundle":
                                                {   "sprintid" : sprintid,
                                                    "memberships": {"remove" : changed_memberships}
                                                }
                                            };
                    }
                    var callbackActionFunction = function(callbackid, responseBundle)
                    {
                        var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
                        bigfathom_util.table.showTotals(controller, minmaxinfo);
                        uiblocker.hide("tr#" + workitemid);
                    };
                    uiblocker.show("tr#" + workitemid);
                    bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, workitemid);
                }
            };

            //TODO HERE --- Add rows and remove rows from the table now if the DB has changed!

            
            //First figure out how many we are going to change
            var main_rowIndex;
            var willset_count = 0;
            var willset_total_effort_hours = 0;
            var willset_detail = {};
            var willset_userinfo = [];
            if(make_member_yn)
            {
                //Check ANTs
                for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
                {
                    var row_workitemid = controller.getRowId(rowIndex);
                    var row_values = controller.getRowValues(rowIndex);
                    var value_is_already_member_yn = row_values["is_member_yn"];
                    var value_name = row_values["name"];
                    var value_remaining_effort_hours = parseInt(row_values["remaining_effort_hours"]);
                    if(workitemid === row_workitemid || (make_member_yn !== value_is_already_member_yn) && all_ants.hasOwnProperty(row_workitemid))
                    {
                        willset_count++;
                        willset_total_effort_hours += value_remaining_effort_hours;
                        willset_detail[row_workitemid] = {"rowIndex": rowIndex, "ants": all_ants[row_workitemid]};
                        if(row_workitemid !== workitemid)
                        {
                            willset_userinfo.push("ID#" + row_workitemid + " " + value_name);
                        } else {
                            main_rowIndex = rowIndex;
                        }
                    }
                }
            } else {
                //Check DEPs
                for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
                {
                    var row_workitemid = controller.getRowId(rowIndex);
                    var row_values = controller.getRowValues(rowIndex);
                    var value_is_already_member_yn = row_values["is_member_yn"];
                    var value_name = row_values["name"];
                    var value_remaining_effort_hours = parseInt(row_values["remaining_effort_hours"]);
                    if(workitemid === row_workitemid || (make_member_yn !== value_is_already_member_yn) && all_deps.hasOwnProperty(row_workitemid))
                    {
                        willset_count++;
                        willset_total_effort_hours += value_remaining_effort_hours;
                        willset_detail[row_workitemid] = {"rowIndex": rowIndex, "ants": all_ants[row_workitemid]};
                        if(row_workitemid !== workitemid)
                        {
                            willset_userinfo.push("ID#" + row_workitemid + " " + value_name);
                        } else {
                            main_rowIndex = rowIndex;
                        }
                    }
                }
            }

            console.log("LOOK dump willset_detail=" + JSON.stringify(willset_detail));

            //Allow the user to abort if they will set more than one
            if(willset_count < 2)
            {
                performSelectionUpdates(willset_detail);
            } else {
                var impacted_count = willset_userinfo.length;
                var markupblurb;
                if(make_member_yn)
                {
                    markupblurb = "<p>Marking workitem#" + workitemid + " will also mark the following " + impacted_count
                      + " antecedent workitems as members of the sprint (total effort remaining for all these is " 
                      + willset_total_effort_hours + " hours):</p>";
                } else {
                    markupblurb = "<p>Unmarking workitem#" + workitemid + " will also remove the following " + impacted_count
                      + " dependent workitems from the sprint (total effort remaining for all these is " 
                      + willset_total_effort_hours + " hours):</p>";
                }
                var markupdetail = "<ul>";
                for(var i=0; i<impacted_count; i++)
                {
                    markupdetail += "<li>" + willset_userinfo[i];
                }
                markupdetail += "</ul>";
                  
                bigfathom_util.dialog.initialize("dialog-confirm-addmanymembers");
                $( "#blurb-confirm-addmanymembers" ).html(markupblurb + markupdetail);
                $( "#dialog-confirm-addmanymembers" ).dialog("option", "buttons", {
                    "Continue": function() {
                        //Do them all!
                        performSelectionUpdates(willset_detail);
                        $(this).dialog("close");
                    },
                    "Cancel Selection": function() {
                        //Undo the setting of the main item
                        bigfathom_util.table.setOneRowMembership(controller, main_rowIndex, !make_member_yn);
                        $(this).dialog("close"); 
                    }
                });
                $( "#dialog-confirm-addmanymembers" ).dialog("open");
            }
        }
    };

    //Get latest records from the server
    bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackMemberActionFunction, workitemid);
};

// The function that will handle model changes
bigfathom_util.table.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }
    
    var colname = controller.getColumnName(columnIndex);
    var sprintid = controller.getRowAttribute(rowIndex, "data_sprintid");
    var workitemid = controller.getRowId(rowIndex);
    if(colname !== 'is_member_yn' && colname !== 'ot_scf')
    {
        throw "Expected the membership or ot_scf to change!";
    }
    var change_comment;
    var values = controller.getRowValues(rowIndex);
    
    if(colname === 'is_member_yn')// && newValue)
    {
        //Simply update the membership now for all affected rows
        bigfathom_util.table.setBranchMembership(controller, workitemid, uiblocker, newValue);
    } else {
        //Update this one row with other data
        var send_dataname = 'update_one_sprint_member';
        var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
        var is_member_yn;
        var ot_scf;
        is_member_yn = values["is_member_yn"] ? 1 : 0;
        if(colname === 'ot_scf')
        {
            ot_scf = newValue;
            change_comment = "grid edit sprintid#" + sprintid + " for workitemid#" + workitemid + " changed scf from '" + oldValue + "' to '" + newValue + "'";
        } else if(colname === 'is_member_yn') {
            ot_scf = null;
            change_comment = "grid edit sprintid#" + sprintid + " for workitemid#" + workitemid + " changed membership from '" + oldValue + "' to '" + newValue + "'";
            bigfathom_util.table.setOneRowMembership(controller, rowIndex, is_member_yn); //Automatically picks a reasonable scf value
        } else {
            throw "No support for changes to colname=" + colname;
        }
        if(ot_scf == '')
        {
            ot_scf = bigfathom_util.table.new_member_default_dc;
        }
        var sendpackage = {"dataname": send_dataname,
                           "databundle":
                                {   "sprintid" : sprintid,
                                    "workitemid": workitemid,
                                    "is_member_yn": is_member_yn,
                                    "ot_scf": ot_scf,
                                    "change_comment": change_comment
                                }
                            };
        console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
            bigfathom_util.table.showTotals(controller, minmaxinfo);
            uiblocker.hide("tr#" + workitemid);
        };
        uiblocker.show("tr#" + workitemid);
        bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, workitemid);
    };

    console.log("LOOK modelChanged changes saved!: " + change_comment);

    //Now compute the formula cells and update the editable markings
    bigfathom_util.table.recomputeFormulas(controller, rowIndex);
    bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);

};
