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
        "version": "20170407.1",
        "minwidth4chart": 2000,
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
    var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
    
    var statusradio_groupname = filterareaid + '_statusgroup';
    var ownerradio_groupname = filterareaid + '_ownergroup';
    var sprintradio_groupname = filterareaid + '_sprintgroup';
    
    var status_filter = $('input:radio[name=' + statusradio_groupname + ']:checked').val();
    var owner_filter = $('input:radio[name=' + ownerradio_groupname + ']:checked').val();
    var sprint_filter = $('input:radio[name=' + sprintradio_groupname + ']:checked').val();
    
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
    if(owner_filter !== "none")
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
    if(sprint_filter !== "none")
    {
        if(sprint_filter === 'opensprint')
        {
            matches = matches && (row.sprintflag == 'opensprint');
        } else
        if(sprint_filter === 'completedsprint')
        {
            matches = matches && (row.sprintflag == 'completedsprint');
        } else
        if(sprint_filter === 'neversprint')
        {
            matches = matches && (row.sprintflag == 'neversprint');
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
                + "<input "
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
    bigfathom_util.table.custominfo.map_status = JSON.parse(Drupal.settings.map_status);
    bigfathom_util.table.custominfo.map_people = JSON.parse(Drupal.settings.map_people);
    
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
    var statusradio_groupname = filterareaid + '_statusgroup';
    var ownerradio_groupname = filterareaid + '_ownergroup';
    var sprintradio_groupname = filterareaid + '_sprintgroup';
    var markup1 = "<fieldset class='elem-inline table-filter'><legend title='Filter the displayed rows on status criteria'>Status Filter</legend><div class='group-standard'>" 
            + getOneRadioMarkup(true,statusradio_groupname,id_radio_all,'None','none','No filter on status')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_completed,'Completed','completed','Only included where status is in a completed state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_unstarted,'Unstarted','notstarted','Only included where status is in an unstarted state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_started,'Started','started','Only included where status is in a started and not yet completed state')
            + getOneRadioMarkup(false,statusradio_groupname,id_radio_unhappy,'Unhappy','unhappy','Only included where status is in an unhappy state')
            + "</div></fieldset>";
    var markup_ownership = "<fieldset class='elem-inline table-filter'><legend title='Filter the displayed rows on ownership criteria'>Ownership Filter</legend><div class='group-standard'>" 
            + getOneRadioMarkup(true,ownerradio_groupname,id_radio_all,'None','none','No filter on ownership')
            + getOneRadioMarkup(false,ownerradio_groupname,id_radio_completed,'Yours','yours','Only included if you are an owner')
            + getOneRadioMarkup(false,ownerradio_groupname,id_radio_unstarted,'One Owner','oneowner','Only included if there is just one owner')
            + "</div></fieldset>";
    var markup_sprint = "<fieldset class='elem-inline table-filter'><legend title='Filter the displayed rows on sprint membership criteria'>Sprint Filter</legend><div class='group-standard'>" 
            + getOneRadioMarkup(true,sprintradio_groupname,id_radio_all,'None','none','No filter on sprint membership criteria')
            + getOneRadioMarkup(false,sprintradio_groupname,id_radio_completed,'Open','opensprint','Only included if in open sprint')
            + getOneRadioMarkup(false,sprintradio_groupname,id_radio_unstarted,'Completed','completedsprint','Only included if in completed sprint')
            + getOneRadioMarkup(false,sprintradio_groupname,id_radio_unstarted,'Never','neversprint','Only included if not in any open or completed sprint')
            + "</div></fieldset>";
    markup_container_elem.innerHTML = markup1 + markup_ownership + markup_sprint;
    
    //Create a listener to handle the clicks
    $('input:radio[name=' + statusradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
    });
    $('input:radio[name=' + ownerradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
    });
    $('input:radio[name=' + sprintradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
    });
};

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var row_count = minmaxinfo.row_count;
    if(row_count === 0)
    {
        markup = "<div class='grid-totals-container'>There are no workitems in this table.</div>";
    } else {
        
        var min_remaining_effort_hours = minmaxinfo.min_remaining_effort_hours;
        var max_remaining_effort_hours = minmaxinfo.max_remaining_effort_hours;
        
        var min_date = minmaxinfo.min_date;
        var max_date = minmaxinfo.max_date;
        
        var min_dfte = minmaxinfo.min_planned_fte_count;
        var max_dfte = minmaxinfo.max_planned_fte_count;
        
        var markup_dates = "<fieldset class='elem-inline'><legend title='The dates declared on the workitems'>Dates</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='min_date'>Lowest:</label><span class='showvalue' id='min_date'>" + min_date + "</span></div>" 
                + "<div class='inline'><label for='max_date'>Highest:</label><span class='showvalue' id='max_date'>" + max_date + "</span></div>" 
                + "</div></fieldset>";
        var markup_ere = "<fieldset class='elem-inline'><legend title='Effort estimate totals for displayed workitems'>Remaining Effort</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='min_dfte'>Lowest Declared:</label><span class='showvalue' id='min_remaining_effort_hours'>" + min_remaining_effort_hours + "</span></div>" 
                + "<div class='inline'><label for='max_dfte'>Highest Declared:</label><span class='showvalue' id='max_remaining_effort_hours'>" + max_remaining_effort_hours + "</span></div>" 
                + "</div></fieldset>";
        var markup_fte = "<fieldset class='elem-inline'><legend title='Full-time-equivalent totals for displayed workitems'>FTE</legend><div class='group-standard'>" 
                + "<div class='inline'><label for='min_dfte'>Lowest Declared:</label><span class='showvalue' id='min_dfte'>" + min_dfte + "</span></div>" 
                + "<div class='inline'><label for='max_dfte'>Highest Declared:</label><span class='showvalue' id='max_dfte'>" + max_dfte + "</span></div>" 
                + "</div></fieldset>";
        
        //Create the people allocation markup
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
                var person = map_people[personid];
                var personname = person.first_nm + " " + person.last_nm;
                var blurb = "Total of " + reffort + " hours for " + primary_detail.itemcount + " owned workitems and " + delegate_blurb;
                var markup = "<div title='" + blurb +  "' class='inline'><label for='" + labelid + "'>" 
                        + personname + ":</label><span class='showvalue' id='" + labelid + "'>" 
                        + reffort + "</span></div>";

                primary_owner_markup_ar.push(markup);
            }
        }
        
        var markup_effort_allocations = "<fieldset class='elem-inline'><legend title='Remaining effort allocation to workitem owners'>Effort Allocations</legend><div class='group-standard'>" 
                + primary_owner_markup_ar.join(" ");
                + "</div></fieldset>";
        
        
        markup = "<div class='grid-totals-container'>" 
                + markup_dates  + markup_ere +  markup_fte 
                + markup_effort_allocations + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var row_count = 0;

    var primary_owner_allocation = {};
    var delegate_owner_allocation = {};
    
    var min_remaining_effort_hours = '';
    var max_remaining_effort_hours = '';
    
    var min_dt = '';
    var max_dt = '';
    
    var min_planned_fte_count = '';
    var max_planned_fte_count = '';
    
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        row_count++;
        var values = controller.getRowValues(rowIndex);
        var value_primary_owner = parseInt(controller.getRowAttribute(rowIndex, "data_primary_owner"));
        var value_all_owners = controller.getRowAttribute(rowIndex, "data_all_owners");
        var ar_all_owners = value_all_owners.split(',');
        var value_start_dt = values["planned_start_dt"];
        var value_end_dt = values["planned_end_dt"];
        var value_remaining_effort_hours = values["remaining_effort_hours"];
        //var value_planned_fte_count = values["planned_fte_count"];
        var value_dfte = controller.getRowAttribute(rowIndex, "data_dfte");

        if(!isNaN(value_remaining_effort_hours) && value_remaining_effort_hours != '')
        {
            if(min_remaining_effort_hours == '' || min_remaining_effort_hours > value_remaining_effort_hours)
            {
                min_remaining_effort_hours = value_remaining_effort_hours;
            }
            if(max_remaining_effort_hours == '' || max_remaining_effort_hours < value_remaining_effort_hours)
            {
                max_remaining_effort_hours = value_remaining_effort_hours;
            }
            value_remaining_effort_hours = parseInt(value_remaining_effort_hours);
        } else {
            value_remaining_effort_hours = 0;
        }

        if(!primary_owner_allocation.hasOwnProperty(value_primary_owner))
        {
            primary_owner_allocation[value_primary_owner] = {"reffort":0,"itemcount":0};
        }
        primary_owner_allocation[value_primary_owner].reffort += value_remaining_effort_hours;
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
                delegate_owner_allocation[doid].reffort += value_remaining_effort_hours;
                delegate_owner_allocation[doid].itemcount++;
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
        
        if(value_dfte != '')
        {
            if(min_planned_fte_count == '' || min_planned_fte_count > value_dfte)
            {
                min_planned_fte_count = value_dfte;
            }
            if(max_planned_fte_count == '' || max_planned_fte_count < value_dfte)
            {
                max_planned_fte_count = value_dfte;
            }
        }
    }
    if(min_remaining_effort_hours === null)
    {
        min_remaining_effort_hours = '';
    }
    if(max_remaining_effort_hours === null)
    {
        max_remaining_effort_hours = '';
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
        'row_count':row_count
        , 'min_remaining_effort_hours':min_remaining_effort_hours
        , 'max_remaining_effort_hours':max_remaining_effort_hours
        , 'min_date':min_dt
        , 'max_date':max_dt
        , 'min_planned_fte_count':min_planned_fte_count
        , 'max_planned_fte_count':max_planned_fte_count
        , 'primary_owner_allocation': primary_owner_allocation
        , 'delegate_owner_allocation': delegate_owner_allocation
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
    console.log("LOOK recomputeEditableMarkings rowIndex=" + rowIndex);
    
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
    var value_remaining_effort_hours = values['remaining_effort_hours'];
   
    var dfte_markup; 
    if(isNaN(value_remaining_effort_hours) || value_remaining_effort_hours == '' || value_remaining_effort_hours == 0)
    {
        dfte_markup = "NA";
    } else {
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
    console.log("LOOK isEditable rowIndex=" + rowIndex + " columnIndex=" + columnIndex);
    var allowrowedit = controller.getRowAttribute(rowIndex, "allowrowedit");
    
    //var values = controller.getRowValues(rowIndex);
    var column = controller.getColumn(columnIndex);
    if(!column.editable || allowrowedit != "1")
    {
        return false;
    }
    if(1 != controller.getRowAttribute(rowIndex, "allowrowedit"))
    {
        return false;
    }
    //alert("LOOK isEditable YES rowIndex=" + rowIndex + " columnIndex=" + columnIndex);
    return true;    
};

// The function that will handle model changes
bigfathom_util.table.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }
    //alert("LOOK rowDataChanged rowIndex=" + rowIndex + " columnIndex=" + columnIndex + " newValue=" + newValue);

    var writeback = true;
    var reload = true;
    
    var colname = controller.getColumnName(columnIndex);

    var new_min_date = false;
    var new_max_date = false;
    if(typeof controller.bigfathom_minmax !== 'undefined')
    {
        //Do NOT check for greater/less, instead check for match!
        if(colname === 'planned_start_dt')
        {
            if(controller.bigfathom_minmax.min_date === oldValue)
            {
                new_min_date = true;
            }
        } else
        if(colname === 'planned_end_dt')
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

    //var value_remaining_effort_hours = values["remaining_effort_hours"];
    //var value_start_dt = values["start_dt"];
    //var value_end_dt = values["end_dt"];

    
    var reloadAction = function(controlBlocking)
    {
        if(typeof controlBlocking === 'undefined')
        {
            controlBlocking = true;
        }
        
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            var record = responseBundle.data.data;
            var value_remaining_effort_hours = record['remaining_effort_hours'];
            var value_start_dt = record['planned_start_dt'];
            var value_end_dt = record['planned_end_dt'];
            var colidx_remaining_effort_hours = controller.getColumnIndex('remaining_effort_hours');
            var colidx_start_dt = controller.getColumnIndex('planned_start_dt');
            var colidx_end_dt = controller.getColumnIndex('planned_end_dt');
            controller.setValueAt(rowIndex, colidx_remaining_effort_hours, value_remaining_effort_hours, true);
            controller.setValueAt(rowIndex, colidx_start_dt, value_start_dt, true);
            controller.setValueAt(rowIndex, colidx_end_dt, value_end_dt, true);

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
            
            jQuery('.otsp-good').attr('class','otsp-outofdate');
            jQuery('.otsp-bad').attr('class','otsp-outofdate');
            jQuery('.otsp-ugly').attr('class','otsp-outofdate');
            jQuery('.otsp-veryugly').attr('class','otsp-outofdate');
            jQuery('#otsp-outofdate-message').attr('class','colorful-warning');
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
