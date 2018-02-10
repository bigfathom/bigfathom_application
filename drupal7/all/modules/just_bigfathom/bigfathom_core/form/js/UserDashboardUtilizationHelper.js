/* 
 * Functions for working with user dashboard display and data
 * of the SORTED WORKLIST tab
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
if(!bigfathom_util.hasOwnProperty("userdashboard_utilization"))
{
    //Create the object property because it does not already exist
    bigfathom_util.userdashboard_utilization = {
        version: "20170302.1",
        tableid: "dtc-utilization",
        containerid: "container4dtc-utilization",
        main_dataname: "person_utilization",
        rounding_factor: 10000,
        special_init_count: 0,
        my_data_refresher: null,
        auto_datarefresh: null,
        projectid: null,
        personid: null,
        myurls:{},
        datafetchers:{}
    };
}

bigfathom_util.userdashboard_utilization.init = function(personid)
{
    console.log("Initializing user dashboard UTILIZATION " + bigfathom_util.userdashboard_utilization.version + " for personid=" + personid);
    bigfathom_util.userdashboard_utilization.personid = personid;
    bigfathom_util.userdashboard_utilization.refreshdisplay(personid);
};

/**
 * Call this to initialize the table with content for the person
 */
bigfathom_util.userdashboard_utilization.refreshdisplay = function(personid)
{
    console.log("Starting refreshdisplay for personid=" + personid);
    console.log("... bigfathom_util.browser_grid_helper.version=" + JSON.stringify(bigfathom_util.browser_grid_helper.version));
    console.log("... bigfathom_util.browser_grid_helper.instance count=" + JSON.stringify(Object.keys(bigfathom_util.browser_grid_helper.grid_instances).length));
    var bgi = bigfathom_util.browser_grid_helper.grid_instances;
    var tableid = bigfathom_util.userdashboard_utilization.tableid;
    if(typeof bgi[tableid] === 'undefined')
    {
        throw "Missing the expected grid id=" + tableid + " for User Utilization!";
    }
    var controller = bgi[tableid];
    bigfathom_util.userdashboard_utilization.finalizeAllGridCells(controller);
};

if(!bigfathom_util.userdashboard_utilization.datafetchers.hasOwnProperty("utilization"))
{
    bigfathom_util.userdashboard_utilization.datafetchers.utilization = {};
    bigfathom_util.userdashboard_utilization.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
    bigfathom_util.userdashboard_utilization.my_data_refresher = null;
    
    bigfathom_util.userdashboard_utilization.showAllRows = function(controller, fetched_data)
    {
        bigfathom_util.userdashboard_utilization.auto_datarefresh.markBlocked("showAllRows");

        console.log("LOOK data fetched_data=" + JSON.stringify(fetched_data));
        
        var summary = fetched_data.summary;
        //var utilization = fetched_data.smartbucketinfo.by_personid[bigfathom_util.userdashboard_utilization.personid];
        var formatted_rows = fetched_data;
        var insertAllRows = function()
        {
            var base_url = bigfathom_util.url.base_url;
            var getLinkUrl = function(address, params_ar)
            {
                var root= base_url + address;
                var params_txt = "";
                for (var i = 0; i < params_ar.length; i++)
                {
                    params_txt += "&" + params_ar[i].name + "=" + params_ar[i].value;
                }
                return root + params_txt;
            };
            var getLinkMarkup = function(address, params_ar, label, tooltip)
            {
                var url = getLinkUrl(address, params_ar);
                return "<a href='" + url + "' title='" + tooltip + "'>" + label + "</a>";
            };
            var url_img = Drupal.settings.myurls.images;

            function getImageURL(filename)
            {
                return url_img + "/" + filename;
            }

            var rowcount = formatted_rows.length;
            for (var i = 0; i < rowcount; i++)
            {
                //var utilrec = utilization[i];
                //var pdata = utilrec.plain;
                var fdata = formatted_rows[i];//utilrec.formatted;				
			
                var assessment_tx_markup = fdata['assessment_tx'];			
                var start_dt_markup = fdata['start_dt'];					
                var end_dt_markup = fdata['end_dt'];					
                var totaldaycount_markup = fdata['totaldaycount'];			
                var availabledaycount_markup = fdata['availabledaycount'];		
                var remaining_effort_hours_markup =fdata['remaining_effort_hours'];
                var available_hours_markup = fdata['available_hours'];			
                var need_hoursperday_markup =fdata['need_hoursperday'];			
                var available_hoursperday_markup = fdata['available_hoursperday'];	
                var upct_markup =fdata['upct'];						
                var pids_markup =fdata['pids'];						
                var wids_markup =fdata['wids'];						
                var comment_markup = fdata['comment'];	
			
                var cellValues = {};
                cellValues['assessment_tx'] = assessment_tx_markup;
                cellValues['start_dt'] = start_dt_markup;
                cellValues['end_dt'] = end_dt_markup;
                cellValues['totaldaycount'] = totaldaycount_markup;
                cellValues['availabledaycount'] = availabledaycount_markup;
                cellValues['remaining_effort_hours'] = remaining_effort_hours_markup;
                cellValues['available_hours'] = available_hours_markup;
                cellValues['need_hoursperday'] = need_hoursperday_markup;
                cellValues['available_hoursperday'] = available_hoursperday_markup;
                cellValues['upct'] = upct_markup;
                cellValues['pids'] = pids_markup;
                cellValues['wids'] = wids_markup;
                cellValues['comment'] = comment_markup;

                var rowAttributes = [];
                controller.append(i, cellValues, rowAttributes, true);
            }
            
            console.log("LOOK wrote " + rowcount + " rows to the table!");

            return rowcount;
        };
        
        var offsetfactor = 1;
        //First clear all the existing rows
        controller.removeAllRows();
        
        //Now add all the news rows
        offsetfactor = insertAllRows();
        
        bigfathom_util.userdashboard_utilization.auto_datarefresh.markAllowed("showAllRows");
    };
}

bigfathom_util.userdashboard_utilization.datafetchers.utilization.fetchServerData = function(controller)
{
    var most_recent_edit_timestamp = 1;
    var previous_recent_edit_timestamp = 123;
    var input_filter = {
                             "about_personid":bigfathom_util.userdashboard_utilization.personid
                            ,"previous_recent_edit_timestamp": previous_recent_edit_timestamp
                        };
    
    if(!bigfathom_util.userdashboard_utilization.auto_datarefresh.isBlocked())
    {
        //Perform a fetch now.
        bigfathom_util.userdashboard_utilization.auto_datarefresh.markBlocked("fetchServerData");
        var grab_fullurl = bigfathom_util.data.getGrabDataUrl(
                        bigfathom_util.userdashboard_utilization.main_dataname
                        ,input_filter
                    );

        var callbackActionFunction = function(callbackid, responseBundle)
        {
            console.log("LOOK started callbackActionFunction with callbackid=" + callbackid);
            if(responseBundle !== null)
            {
                console.log("LOOK started callbackActionFunction with responseBundle=" + JSON.stringify(responseBundle));
                var metadata = responseBundle.data.metadata;
                var record = responseBundle.data.data;
                if(metadata.hasOwnProperty('most_recent_edit_timestamp'))
                {
                    most_recent_edit_timestamp = metadata.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                } else {
                    most_recent_edit_timestamp = 'NO SERVER INFO';
                }
                previous_recent_edit_timestamp = most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                bigfathom_util.userdashboard_utilization.showAllRows(controller, record);
            }

            //Setup for another check to happen in a little while
            bigfathom_util.userdashboard_utilization.auto_datarefresh.markAllowed("fetchServerData");
        };
        bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, previous_recent_edit_timestamp);
    }
    console.log("LOOK exit fetchServerData!");
};

bigfathom_util.userdashboard_utilization.finalizeAllGridCells = function(controller) 
{
    if(bigfathom_util.userdashboard_utilization.special_init_count < 1)
    {
        bigfathom_util.userdashboard_utilization.special_init_count++;
        bigfathom_util.userdashboard_utilization.datafetchers.utilization.fetchServerData(controller);
    }
            
    return;
};

bigfathom_util.userdashboard_utilization.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    markup = "<div class='grid-totals-container'>No totals available</div>";
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.userdashboard_utilization.getMinMaxMetrics = function(controller) 
{
    var count_map = {};

    var themap = {
              'count_map':count_map
        };
        
    return themap;
};

/**
 * Call this to update the indicators of which cells in a row are editable
 * The core framework does NOT have a comparable method that is called 
 * that would CHANGE the markings of other cells on a row.
 */
bigfathom_util.userdashboard_utilization.recomputeEditableMarkings = function(controller, rowIndex) 
{
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        if(column.editable)
        {
            if(bigfathom_util.userdashboard_utilization.isEditable(controller, rowIndex, columnIndex))
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
bigfathom_util.userdashboard_utilization.recomputeFormulas = function(controller, rowIndex, minmaxinfo, allowFullGridRefresh) 
{
    console.log("Starting recomputeFormulas for " + controller.getRowId(rowIndex) + " at rowIndex=" + rowIndex);
};

bigfathom_util.userdashboard_utilization.customColumnsInit = function(controller)
{
    //alert("LOOK we are in the init!!!!!");
};

//Tell us if it is okay to edit a specific cell
bigfathom_util.userdashboard_utilization.isEditable = function(controller, rowIndex, columnIndex) 
{
    var values = controller.getRowValues(rowIndex);
    var column = controller.getColumn(columnIndex);
    if(!column.editable)
    {
        return false;
    }    
    if(1 != controller.getRowAttribute(rowIndex, "allowrowedit"))
    {
        return false;
    }
    return true;
};

/**
 * Only sets the display NOT the database.
 */
bigfathom_util.userdashboard_utilization.setOneRowMembership = function(controller, rowIndex, make_member, workitem_info)
{
    return null;
};

// The function that will handle model changes
bigfathom_util.userdashboard_utilization.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }
};


