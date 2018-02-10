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
        version: "20171112.1",
        lowest_default_scf: 0.01,
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
if(!bigfathom_util.table.hasOwnProperty("datafetchers"))
{
    bigfathom_util.table.datafetchers = {};
}
if(!bigfathom_util.table.datafetchers.hasOwnProperty("communications"))
{
    bigfathom_util.table.datafetchers.communications = {};
    bigfathom_util.table.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
    bigfathom_util.table.my_data_refresher = null;
    
    function showAllRows(controller, fetched_data)
    {
        //console.log("LOOK our fetched_data=" + JSON.stringify(fetched_data));
        bigfathom_util.table.auto_datarefresh.markBlocked("showAllRows");

        var communications = fetched_data.communications;

        var colidx_naturalsort = controller.getColumnIndex("naturalsort");
        var colidx_subjectcontextletter = controller.getColumnIndex("subjectcontextletter");
        var colidx_sid = controller.getColumnIndex("sid");
        var colidx_thread = controller.getColumnIndex("thread");
        var colidx_author = controller.getColumnIndex("author");
        var colidx_actionrequested = controller.getColumnIndex("actionrequested");
        var colidx_levelofconcern = controller.getColumnIndex("levelofconcern");
        var colidx_commentsnippet = controller.getColumnIndex("commentsnippet");
        var colidx_lastupdated = controller.getColumnIndex("lastupdated");
        var colidx_actionoptions = controller.getColumnIndex("actionoptions");

        var showRowsOneContext = function(contextname, offsetfactor)
        {
            //console.log("LOOK starting showRowsOneContext(" + contextname + "," + offsetfactor + ")!!!!!!!");   
            var subjectletter = contextname.substr(0,1).toUpperCase();
            var subjectidkeyname = contextname + "id";
            if(!communications.hasOwnProperty(contextname))
            {
                return offsetfactor;
            } else {
                var getLinkUrl = function(address, params_ar)
                {
                    var root= bigfathom_util.table.myurls['base_url'] + "/?q=" + address;
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
        
                var return_url = bigfathom_util.table.myurls['this_page_path'];
                var thread_address = bigfathom_util.table.myurls['communication'][contextname];
                var view_address = bigfathom_util.table.myurls['view'][contextname];
                var view_paramname=contextname+'id';
                var rows_by_comid = communications[contextname].comments;
                //var actionitems_map = communications[contextname].naturalorder;
                var naturalorder_ar = communications[contextname].naturalorder;
                var rowcount = naturalorder_ar.length;
                for (var i = 0; i < rowcount; i++) 
                {
                    var comid = naturalorder_ar[i];
                    var onerow = rows_by_comid[comid];
                    var rowId = i + offsetfactor;
                    var threadlabel;
                    var threadmarkup;
                    var sid = onerow[subjectidkeyname];
                    var sidmarkup = sid;   
                    var action_requested_concern = onerow['action_requested_concern'];
                    var is_waiting = onerow['is_waiting'];
                    var actionrequestednmarkup;
                    var levelofconcernmarkup;
                    if(action_requested_concern > 0)
                    {
                        var spanclass;
                        var labeltext;
                        if(is_waiting > 0)
                        {
                            labeltext = "Yes (todo)";
                            spanclass = "colorful-warning";
                            levelofconcernmarkup = "[SORTNUM:" + action_requested_concern + "]" 
                                    + (action_requested_concern >= 30 ? "<span class='concern-high'>H</span>" : action_requested_concern >= 30 ? "<span class='concern-medium'>M</span>" : "<span class='concern-low'>L</span>");
                            actionrequestednmarkup = "[SORTNUM:" + (action_requested_concern + 1000) + "]<span class='" + spanclass + "'>" + labeltext + "</span>";  
                        } else {
                            labeltext = "Yes (completed)";
                            spanclass = "";
                            levelofconcernmarkup = "[SORTNUM:" + (action_requested_concern - 1) + "]" 
                                    + (action_requested_concern >= 30 ? "<span class=''>H</span>" : action_requested_concern >= 30 ? "<span class=''>M</span>" : "L");
                            actionrequestednmarkup = "[SORTNUM:" + action_requested_concern + "]<span class='" + spanclass + "'>" + labeltext + "</span>";  
                        }
                    } else {
                        levelofconcernmarkup = "[SORTNUM:0]";
                        actionrequestednmarkup = "[SORTNUM:0]No";   
                    }
                    var updated_dt = onerow['updated_dt'];
                    var root_comid = onerow['root_comid'];
                    if(comid != root_comid)
                    {
                        threadlabel = root_comid + "|" + comid;
                    } else {
                        threadlabel = comid;
                    }

                    var thread_params = [];
                    thread_params.push({'name':'comid', 'value':comid});
                    thread_params.push({'name':view_paramname, 'value':sid});
                    thread_params.push({'name':'return', 'value':return_url});
                    threadmarkup = getLinkMarkup(thread_address,thread_params,threadlabel,"view thread#"+comid+" of "+contextname+"#"+sid);
                    var talk_img_markup = "<img src='" + talk_imgurl + "'>";
                    var linkurl_talk_markup = getLinkMarkup(thread_address,thread_params,talk_img_markup,"view thread#"+comid+" of "+contextname+"#"+sid);
                    
                    var author_info = onerow['author_info'];
                    var authormarkup = author_info['first_nm'] + " " + author_info['last_nm'];
                    var title_tx = onerow['title_tx'];
                    var body_tx = onerow['body_tx'];
                    var commentsnippetmarkup;
                    if(title_tx > "")
                    {
                        commentsnippetmarkup = "<span title='body text size=" + body_tx.length + "'>" + title_tx + "</span>";
                    } else {
                        if(body_tx.length < 100)
                        {
                            commentsnippetmarkup = body_tx;
                        } else {
                            commentsnippetmarkup = "<span title='no tite, body text size=" + body_tx.length + "'>" + body_tx.substr(0,100) + "...</span>";
                        }
                    }
                    var view_params = [];
                    view_params.push({'name':view_paramname, 'value':sid});
                    var eye_img_markup = "<img src='" + eye_imgurl + "'>";
                    var linkurl_view_subject_markup = getLinkMarkup(view_address,view_params,eye_img_markup,"view "+contextname+"#"+sid);
                    var actionoptionsmarkup = linkurl_talk_markup + " " + linkurl_view_subject_markup;
                    
                    var cellValues = {};
                    cellValues['naturalsort'] = rowId;
                    cellValues['subjectcontextletter'] = "<span title='" + contextname + "'>" + subjectletter + "</span>";
                    cellValues['sid'] = sidmarkup;
                    cellValues['thread'] = threadmarkup;
                    cellValues['author'] = authormarkup;
                    cellValues['actionrequested'] = actionrequestednmarkup;
                    cellValues['levelofconcern'] = levelofconcernmarkup;
                    cellValues['commentsnippet'] = commentsnippetmarkup;
                    cellValues['lastupdated'] = updated_dt;
                    cellValues['actionoptions'] = actionoptionsmarkup;
                    
                    var rowAttributes = [];
                    controller.append(rowId, cellValues, rowAttributes, true);
                }
                
                return offsetfactor + rowcount;
            }
        };
        
        var offsetfactor = 1;
        //First clear all the existing rows
        controller.removeAllRows();
        
        //Now add all the news rows
        offsetfactor = showRowsOneContext('project', offsetfactor);
        offsetfactor = showRowsOneContext('sprint', offsetfactor);
        offsetfactor = showRowsOneContext('workitem', offsetfactor);
        offsetfactor = showRowsOneContext('testcase', offsetfactor);
        
        bigfathom_util.table.auto_datarefresh.markAllowed("showAllRows");
        alert("Found " + (offsetfactor-1) + " matching communication records");
    }
}

bigfathom_util.table.datafetchers.communications.fetchMatchingRecords = function(controller)
{
    var getCleanList = function(rawlisttxt)
    {
        var cleanlist = [];
        var parts = rawlisttxt.split(',');
        for(var i=0; i<parts.length; i++)
        {
            var cleanterm = parts[i].trim();
            if(cleanterm.length > 0)
            {
                cleanlist.push(cleanterm);
            }
        }
        return cleanlist.join(",");
        
    };
    
    var previous_project_edit_key = 1;
    var previous_project_edit_timestamp = 123;
    var input_filter = {
                             "projectid":bigfathom_util.table.projectid
                            ,"previous_project_edit_key": previous_project_edit_key
                            ,"previous_project_edit_timestamp": previous_project_edit_timestamp
                        };
    input_filter['context_personid'] = bigfathom_util.table.personid;
    input_filter['ownergroup'] = $('input[name=gcq_ownergroup]:radio:checked').val();
    input_filter['authorgroup'] = $('input[name=gcq_authorgroup]:radio:checked').val();
    input_filter['statusgroup'] = $('input[name=gcq_statusgroup]:radio:checked').val();
    input_filter['start_dt'] = $('#gcq_startdate').val();
    input_filter['end_dt'] = $('#gcq_enddate').val();
    input_filter['comm_matchtext'] = $('#gcq_comm_matchtext').val();
    input_filter['comids'] = $('#gcq_comids').val();
    input_filter['workitem_namematchtext'] = $('#gcq_workitem_namematchtext').val();
    input_filter['workitem_tag_matchtext'] = getCleanList($('#gcq_workitem_tag_matchtext').val());
    
    if(!bigfathom_util.table.auto_datarefresh.isBlocked())
    {
        //Perform a fetch now.
        //alert("LOOK do the fetch matching records now!");
        bigfathom_util.table.auto_datarefresh.markBlocked("fetchMatchingRecords");
        var grab_fullurl = bigfathom_util.data.getGrabDataUrl("communications_finder_inproject"
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
                console.log("LOOK metadata=" + JSON.stringify(metadata));
                console.log("LOOK record=" + JSON.stringify(record));
                previous_project_edit_key = record.most_recent_edit_key; //In sync with the server instead of local machine time.
                console.log("LOOK previous_project_edit_timestamp=" + previous_project_edit_timestamp);
                console.log("LOOK record.most_recent_edit_timestamp=" + record.most_recent_edit_timestamp);
                console.log("LOOK record.has_newdata=" + record.has_newdata);
                if(!record.has_newdata && previous_project_edit_timestamp < record.most_recent_edit_timestamp)
                {
                    alert("LOOK ERROR says no new data but there is new data!");
                }
                previous_project_edit_timestamp = record.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                showAllRows(controller, record);
            }

            //Setup for another check to happen in a little while
            bigfathom_util.table.auto_datarefresh.markAllowed("fetchMatchingRecords");
        };

        bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, previous_project_edit_key);
    }
};

bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    
    if(bigfathom_util.table.special_init_count < 1)
    {
        $body = $("body");

        $(document).on({
            ajaxStart: function() { $body.addClass("data-loading");    },
            ajaxStop: function() { $body.removeClass("data-loading"); }    
        });
        
        bigfathom_util.table.special_init_count++;
        bigfathom_util.table.personid = Drupal.settings.personid;
        bigfathom_util.table.projectid = Drupal.settings.projectid;
        bigfathom_util.table.myurls = Drupal.settings.myurls;

        //Pre-populate with 10 day range
        var d = new Date();
        d.setDate(d.getDate() - 10);
        var defaultStartDateText = $.datepicker.formatDate( "yy-mm-dd", d);
        $('#gcq_startdate').val(defaultStartDateText);
        
        //Now attach handlers
        $('a#gcq_fetchmatchingrows').click(function() {
            bigfathom_util.table.datafetchers.communications.fetchMatchingRecords(controller);
        });  
    }
            
    return;
    
};

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var active_count = minmaxinfo.count_map.active_count;
    var isparked_count = minmaxinfo.count_map.isparked_count;
    var istrashed_count = minmaxinfo.count_map.istrashed_count;
    if(active_count + isparked_count + istrashed_count === 0)
    {
        markup = "<div class='grid-totals-container'>There are no candidate topics currently declared in this project.</div>";
    } else {
        var active_count_elem = "<div class='inline'><label for='active_count'>Action Requests:</label><span class='showvalue' id='active_count'>" + active_count + "</span></div>";
        var isparked_count_elem = "<div class='inline'><label for='isparked_count'>Sprints:</label><span class='showvalue' id='isparked_count'>" + isparked_count + "</span></div>";
        var istrashed_count_elem = "<div class='inline'><label for='istrashed_count'>Workitems:</label><span class='showvalue' id='istrashed_count'>" + istrashed_count + "</span></div>";
        var count_markup = "<fieldset class='elem-inline'><legend title='Some summary statistics of displayed data'>Count Totals</legend><div class='group-standard'>" 
                + active_count_elem
                + " "
                + isparked_count_elem
                + " "
                + istrashed_count_elem
                + "</div></fieldset>";
        markup = "<div class='grid-totals-container'>" + count_markup + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var count_map = {};
    var isparked_count = 0;
    var istrashed_count = 0;
    var active_count = 0;
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var isparked = values["isparked"];
        var istrashed = values["istrashed"];
        var importance = values["importance"];
        if(isparked === 'Yes')
        {
            isparked_count++;
        } else if (istrashed === 'Yes'){
            istrashed_count++;
        } else {
            active_count++;
        }
    }    
    count_map['isparked_count'] = isparked_count;
    count_map['istrashed_count'] = istrashed_count;
    count_map['active_count'] = active_count;
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
bigfathom_util.table.recomputeEditableMarkings = function(controller, rowIndex) 
{
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        if(column.editable)
        {
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
};

bigfathom_util.table.customColumnsInit = function(controller)
{
    //alert("LOOK we are in the init!!!!!");
};

//Tell us if it is okay to edit a specific cell
bigfathom_util.table.isEditable = function(controller, rowIndex, columnIndex) 
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
bigfathom_util.table.setOneRowMembership = function(controller, rowIndex, make_member, workitem_info)
{
    return null;
};

// The function that will handle model changes
bigfathom_util.table.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
{ 
    if(oldValue === newValue)
    {
        //Nothing changed
        return;
    }
};
