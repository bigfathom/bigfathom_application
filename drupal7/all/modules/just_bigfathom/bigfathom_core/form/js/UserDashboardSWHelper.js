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
if(!bigfathom_util.hasOwnProperty("userdashboard_sw"))
{
    //Create the object property because it does not already exist
    bigfathom_util.userdashboard_sw = {
        version: "20170909.1",
        tableid: "dtc-sorted-worklist",
        containerid: "container4dtc-sorted-worklist",
        main_dataname: "user_worklist_with_urgency",
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

bigfathom_util.userdashboard_sw.init = function(personid)
{
    console.log("Initializing user dashboard SW " + bigfathom_util.userdashboard_sw.version + " for personid=" + personid);
    bigfathom_util.userdashboard_sw.refreshdisplay(personid);
};

/**
 * Call this to initialize the table with content for the person
 */
bigfathom_util.userdashboard_sw.refreshdisplay = function(personid)
{
    console.log("Starting refreshdisplay for personid=" + personid);
    console.log("... bigfathom_util.browser_grid_helper.version=" + JSON.stringify(bigfathom_util.browser_grid_helper.version));
    console.log("... bigfathom_util.browser_grid_helper.instance count=" + JSON.stringify(Object.keys(bigfathom_util.browser_grid_helper.grid_instances).length));
    var bgi = bigfathom_util.browser_grid_helper.grid_instances;
    var tableid = bigfathom_util.userdashboard_sw.tableid;
    if(typeof bgi[tableid] === 'undefined')
    {
        throw "Missing the expected grid id=" + tableid + " for User Sorted Worklist!";
    }
    var controller = bgi[tableid];
    bigfathom_util.userdashboard_sw.finalizeAllGridCells(controller);
};

if(!bigfathom_util.userdashboard_sw.datafetchers.hasOwnProperty("worklist"))
{
    bigfathom_util.userdashboard_sw.datafetchers.worklist = {};
    bigfathom_util.userdashboard_sw.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
    bigfathom_util.userdashboard_sw.my_data_refresher = null;
    
    bigfathom_util.userdashboard_sw.showAllRows = function(controller, fetched_data)
    {
        //console.log("LOOK our fetched_data=" + JSON.stringify(fetched_data));
        bigfathom_util.userdashboard_sw.auto_datarefresh.markBlocked("showAllRows");

        console.log("LOOK data fetched_data=" + JSON.stringify(fetched_data));
        //alert("LOOK at data result now for worklist!!!");
        var summary = fetched_data.summary;
        var worklist = fetched_data.worklist;
        var naturalorder_ar = fetched_data.naturalorder;
        console.log("LOOK data naturalorder_ar=" + JSON.stringify(naturalorder_ar));
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
            var talk_imgurl = getImageURL("icon_boxed_replyballoons.png");
            var eye_imgurl = getImageURL("icon_eye.png");

            var return_url = Drupal.settings.myurls['this_page_path'];
            var thread_address = Drupal.settings.myurls['communication']['workitem'];
            var view_address = Drupal.settings.myurls['view']['workitem'];

            var rowcount = naturalorder_ar.length;
            for (var i = 0; i < rowcount; i++)
            {
                var workitemid = naturalorder_ar[i];
                var onerow = worklist[workitemid];
                var scalar_urgency = onerow['scalar_urgency'];
                var urgency_score = scalar_urgency['score'];
                var logic_map = scalar_urgency['logic'];
                var projectid = onerow['owner_projectid'];
                var typeletter = onerow['typeletter'];
                var name = onerow['workitem_nm'];
                var status_cd = onerow['status_cd'];
                var ar_factors =  onerow['ar_factors'];
                var importance = onerow['importance'];
                var start_dt = onerow['effective_start_dt'];
                var end_dt = onerow['effective_end_dt'];

                var thread_params = [];
                thread_params.push({'name':'workitemid', 'value':workitemid});
                thread_params.push({'name':'return', 'value':return_url});
                var talk_img_markup = "<img src='" + talk_imgurl + "'>";
                var linkurl_talk_markup = getLinkMarkup(thread_address,thread_params,talk_img_markup,"communications of #"+workitemid);

                var view_params = [];
                view_params.push({'name':'workitemid', 'value':workitemid});
                var eye_img_markup = "<img src='" + eye_imgurl + "'>";
                var linkurl_view_subject_markup = getLinkMarkup(view_address,view_params,eye_img_markup,"view workitem#"+workitemid);
                var actionoptionsmarkup = linkurl_talk_markup + " " + linkurl_view_subject_markup;

                var logic_txt_ar = [];
                for (var factor_label in logic_map) 
                {
                    if (logic_map.hasOwnProperty(factor_label)) 
                    {
                        logic_txt_ar.push(factor_label + "=" + logic_map[factor_label]);
                    }
                }
                var logic_txt = "Score factors are " + logic_txt_ar.join(" and ");
                var urgency_markup = "[SORTNUM:" + urgency_score + "]<span title='" + logic_txt + "'>" + urgency_score + "</span>";

                var ar_openconcerntotal = ar_factors['concern_total'];
                var ar_count = ar_factors['ar_count'];
                var aroct_markup = "[SORTNUM:" + ar_openconcerntotal + "]<span title='" + ar_count + " open action requests'>" + ar_openconcerntotal + "</span>";

                var project_params = [];
                project_params.push({'name':'projectid', 'value':projectid});
                project_params.push({'name':'redirect', 'value':'bigfathom/projects/workitems/duration'});
                var project_markup = '[SORTNUM:' + projectid + ']' 
                        + getLinkMarkup('bigfathom/projects/select',project_params,projectid,"select project#"+projectid+' now for detailed work');

                var workitemid_params = [];
                workitemid_params.push({'name':'workitemid', 'value':workitemid});
                var workitemid_markup = '[SORTNUM:' + workitemid + ']' 
                        + getLinkMarkup('bigfathom/workitem/view',workitemid_params,workitemid,"view details of workitem#"+workitemid);

                var cellValues = {};
                cellValues['urgency'] = urgency_markup;
                cellValues['projectid'] = project_markup;
                cellValues['workitemid'] = workitemid_markup;
                cellValues['typeletter'] = typeletter;
                cellValues['name'] = name;
                cellValues['status_cd'] = status_cd;
                cellValues['ar_openconcerntotal'] = aroct_markup;
                cellValues['importance'] = importance;
                cellValues['start_dt'] = start_dt;
                cellValues['end_dt'] = end_dt;
                cellValues['actionoptions'] = actionoptionsmarkup;

                var rowAttributes = [];
                controller.append(workitemid, cellValues, rowAttributes, true);
            }

            return rowcount;
        };
        
        var offsetfactor = 1;
        //First clear all the existing rows
        controller.removeAllRows();
        
        //Now add all the news rows
        offsetfactor = insertAllRows();
        
        bigfathom_util.userdashboard_sw.auto_datarefresh.markAllowed("showAllRows");
    };
}

bigfathom_util.userdashboard_sw.datafetchers.worklist.fetchServerData = function(controller)
{
    
    var previous_project_edit_key = 1;
    var previous_project_edit_timestamp = 123;
    var input_filter = {
                             "about_personid":bigfathom_util.userdashboard_sw.personid
                            ,"previous_project_edit_key": previous_project_edit_key
                            ,"previous_project_edit_timestamp": previous_project_edit_timestamp
                        };
    
    if(!bigfathom_util.userdashboard_sw.auto_datarefresh.isBlocked())
    {
        //Perform a fetch now.
        bigfathom_util.userdashboard_sw.auto_datarefresh.markBlocked("fetchServerData");
        var grab_fullurl = bigfathom_util.data.getGrabDataUrl(bigfathom_util.userdashboard_sw.main_dataname
                        ,input_filter
                    );

        var callbackActionFunction = function(callbackid, responseBundle)
        {
            console.log("LOOK started callbackActionFunction with callbackid=" + callbackid);
            if(responseBundle !== null)
            {
                console.log("LOOK started callbackActionFunction with responseBundle=" + JSON.stringify(responseBundle));
                var record = responseBundle.data.data;
                previous_project_edit_key = record.most_recent_edit_key; //In sync with the server instead of local machine time.
                console.log("LOOK previous_project_edit_timestamp=" + previous_project_edit_timestamp);
                console.log("LOOK record.most_recent_edit_timestamp=" + record.most_recent_edit_timestamp);
                previous_project_edit_timestamp = record.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                bigfathom_util.userdashboard_sw.showAllRows(controller, record);
            }

            //Setup for another check to happen in a little while
            bigfathom_util.userdashboard_sw.auto_datarefresh.markAllowed("fetchServerData");
        };

        bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, previous_project_edit_key);
    }
    console.log("LOOK exit fetchServerData!");
};

bigfathom_util.userdashboard_sw.finalizeAllGridCells = function(controller) 
{
    //alert("LOOK we are in SQ finalize all grid cells asdfasdfasdf");
    if(bigfathom_util.userdashboard_sw.special_init_count < 1)
    {
        bigfathom_util.userdashboard_sw.special_init_count++;
        bigfathom_util.userdashboard_sw.datafetchers.worklist.fetchServerData(controller);
    }
            
    return;
};

bigfathom_util.userdashboard_sw.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    markup = "<div class='grid-totals-container'>No totals available</div>";
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.userdashboard_sw.getMinMaxMetrics = function(controller) 
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
bigfathom_util.userdashboard_sw.recomputeEditableMarkings = function(controller, rowIndex) 
{
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        if(column.editable)
        {
            if(bigfathom_util.userdashboard_sw.isEditable(controller, rowIndex, columnIndex))
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
bigfathom_util.userdashboard_sw.recomputeFormulas = function(controller, rowIndex, minmaxinfo, allowFullGridRefresh) 
{
    console.log("Starting recomputeFormulas for " + controller.getRowId(rowIndex) + " at rowIndex=" + rowIndex);
};

bigfathom_util.userdashboard_sw.customColumnsInit = function(controller)
{
    //alert("LOOK we are in the init!!!!!");
};

//Tell us if it is okay to edit a specific cell
bigfathom_util.userdashboard_sw.isEditable = function(controller, rowIndex, columnIndex) 
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
bigfathom_util.userdashboard_sw.setOneRowMembership = function(controller, rowIndex, make_member, workitem_info)
{
    return null;
};

// The function that will handle model changes
bigfathom_util.userdashboard_sw.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }
};


