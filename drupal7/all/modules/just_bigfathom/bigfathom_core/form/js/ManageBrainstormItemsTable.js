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
        version: "20170531.1",
        lowest_default_scf: 0.01,
        rounding_factor: 10000,
        special_init_count: 0,
        my_data_refresher: null,
        auto_datarefresh: null,
        projectid: null,
        personid: null,
        myurls:{}
    };
}

bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo);  
        bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);  
    }
    
    bigfathom_util.table.showTotals(controller, minmaxinfo);
    
    if(bigfathom_util.table.special_init_count < 1)
    {
        bigfathom_util.table.special_init_count++;
        bigfathom_util.table.personid = Drupal.settings.personid;
        bigfathom_util.table.projectid = Drupal.settings.projectid;
        bigfathom_util.table.myurls = Drupal.settings.myurls;        
        
        bigfathom_util.table.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
        bigfathom_util.table.my_data_refresher = null;
        var previous_project_edit_key = 1; //Initialize to super low number
        var previous_project_edit_timestamp = 1; //Initialize to super low number
        
        var convertCategoryNameToLetter = function(name)
        {
            if(name === 'Task')
            {
                return 'T';
            }
            if(name === 'Goal')
            {
                return 'G';
            }
            return null;
        };
        
        var convertCategoryLetterToName = function(letter)
        {
            if(letter === 'T')
            {
                return 'Task';
            }
            if(letter === 'G')
            {
                return 'Goal';
            }
            return 'Uncategorized';
        };
        
        /**
         * Apply changes from database feedback
         */
        var applyNodeChanges = function (nodechanges)
        {
            console.log("TODO apply node changes " + JSON.stringify(nodechanges));

            bigfathom_util.table.auto_datarefresh.markBlocked("applyNodeChanges");
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
            var redx_imgurl = getImageURL("icon_redx.png");
            var pencil_imgurl = getImageURL("icon_pencil.png");
            var eye_imgurl = getImageURL("icon_eye.png");
            var return_url = bigfathom_util.table.myurls['this_page_path'];
            var view_address = bigfathom_util.table.myurls['view'];
            var edit_address = bigfathom_util.table.myurls['edit'];
            var delete_address = bigfathom_util.table.myurls['delete'];
            
            var colidx_context_nm = controller.getColumnIndex("context_nm");
            var colidx_category_nm = controller.getColumnIndex("category_nm");
            var colidx_isparked = controller.getColumnIndex("isparked");
            var colidx_istrashed = controller.getColumnIndex("istrashed");
            var colidx_item_nm = controller.getColumnIndex("item_nm");
            var colidx_purpose_tx = controller.getColumnIndex("purpose_tx");
            var colidx_importance = controller.getColumnIndex("importance");
            var colidx_updated_dt = controller.getColumnIndex("updated_dt");
            var colidx_created_dt = controller.getColumnIndex("created_dt");
            
            var add_these_ar = [];
            var edited_map = {};
            var delete_these_ar = [];
            var rowcount = controller.getRowCount();
            for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
            {
                var brainstormid = controller.getRowId(rowIndex);
                var value_pyn_cd = controller.getRowAttribute(rowIndex, "pyn_cd");
                var value_tyn_cd = controller.getRowAttribute(rowIndex, "tyn_cd");
                var isparked = value_pyn_cd === 'Y';// values["isparked"];
                var istrashed = value_tyn_cd === 'Y'; //values["istrashed"];
                
                var rowchanged = false;
                console.log("LOOK CLOWN rowchanged="+rowchanged+" rowcount="+rowcount+" rowIndex="+rowIndex+" brainstormid="+brainstormid);
                if(nodechanges.hasOwnProperty(brainstormid))
                {
                    edited_map[brainstormid] = "updated";
                    var values = controller.getRowValues(rowIndex);
                    var context_nm = values["context_nm"];
                    var category_nm = values["category_nm"];
                    var item_nm = values["item_nm"];
                    var purpose_tx = values["purpose_tx"];
                    //var isparked = values["isparked"];
                    //var istrashed = values["istrashed"];
                    var importance = values["importance"];
                    var updated_dt = values["updated_dt"];
                    var created_dt = values["created_dt"];
        
                    var newinfo = nodechanges[brainstormid];
                    var new_active_yn = newinfo['active_yn'];
                    var new_candidate_type = newinfo['candidate_type'];
                    var new_item_nm = newinfo['item_nm'];
                    var new_purpose_tx = newinfo['purpose_tx'];
                    var nct = convertCategoryLetterToName(new_candidate_type);
                    var new_isparked = newinfo['into_parkinglot_dt'] ? true : false;
                    var new_istrashed = (new_active_yn === '0' || newinfo['into_trash_dt']) ? true : false;
                    var new_importance = newinfo['importance'];
                    var new_updated_dt = newinfo["updated_dt"];
                    var new_created_dt = newinfo["created_dt"];
                    if(new_updated_dt !== updated_dt)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_updated_dt, new_updated_dt, true);
                    }
                    if(new_item_nm !== item_nm)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_item_nm, new_item_nm, true);
                    }
                    if(new_purpose_tx !== null && new_purpose_tx.length > 120)
                    {
                        var suffixmarkup = "<span title='showing 120 of "  + new_purpose_tx.length + " total characters in the text'> ... more</span>";
                        new_purpose_tx = new_purpose_tx.substr(0,120) + suffixmarkup;
                    }
                    if(new_purpose_tx !== purpose_tx)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_purpose_tx, new_purpose_tx, true);
                    }
                    if(new_isparked !== isparked)
                    {
                        var classname;
                        var label;
                        var tooltip;
                        var cd;
                        rowchanged = true;
                        if(new_isparked)
                        {
                            classname = "colorful-notice";
                            label = 'Yes';
                            tooltip = '';
                            cd = 'Y';
                        } else {
                            classname = "";
                            label = 'No';
                            tooltip = '';
                            cd = 'N';
                        }
                        var markup_isparked = "<span class='" + classname + "'>" + label + "</span>";
                        controller.setValueAt(rowIndex, colidx_isparked, markup_isparked, true);
                        controller.setRowAttribute(rowIndex, 'pyn_cd', cd);
                    }
                    if(new_istrashed !== istrashed)
                    {
                        var classname;
                        var label;
                        var tooltip;
                        var cd;
                        rowchanged = true;
                        if(new_istrashed)
                        {
                            classname = "colorful-warning";
                            label = 'Yes';
                            tooltip = '';
                            cd = 'Y';
                        } else {
                            classname = "";
                            label = 'No';
                            tooltip = '';
                            cd = 'N';
                        }
                        controller.setValueAt(rowIndex, colidx_istrashed, "<span class='" + classname + "'>" + label + "</span>", true);
                        controller.setRowAttribute(rowIndex, 'tyn_cd', cd);
                    }
                    if(new_importance !== importance)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_importance, new_importance, true);
                    }
                    if(nct !== category_nm)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_category_nm, nct, true);
                    }
                    if(newinfo['context_nm'] !== context_nm)
                    {
                        rowchanged = true;
                        controller.setValueAt(rowIndex, colidx_context_nm, newinfo['context_nm'], true);
                    }
                    if(rowchanged)
                    {
                        console.log("LOOK brainstormid=" + brainstormid + " todo apply this newinfo >>> " + JSON.stringify(newinfo));
                    }
                } else {
                    console.log("LOOK DELETE brainstormid=" + brainstormid + " rowIndex=" + rowIndex);
                    delete_these_ar.push(rowIndex);
                    edited_map[brainstormid] = "deleted";
                }
            }
            //Now remove the items to delete
            var rowcount = delete_these_ar.length;
            for(var i=rowcount-1;i>=0;i--)
            {
                var rowIndex = delete_these_ar[i];
                console.log("LOOK DELETE i=" + i + " rowIndex=" + rowIndex);
                controller.remove(rowIndex);
            }
            //Now figure out what we need to add
            for (var brainstormid in nodechanges) 
            {
                if (nodechanges.hasOwnProperty(brainstormid)) 
                {
                    if(!edited_map.hasOwnProperty(brainstormid))
                    {
                        var newinfo = nodechanges[brainstormid];
                        var new_owner_personid = newinfo['owner_personid'];
                        var new_context_nm = newinfo['context_nm'];
                        var new_candidate_type = newinfo['candidate_type'];
                        var new_item_nm = newinfo['item_nm'];
                        var new_purpose_tx = newinfo['purpose_tx'];
                        var nct = convertCategoryLetterToName(new_candidate_type);
                        var new_isparked = newinfo['into_parkinglot_dt'] ? "Yes" : "No";
                        var new_istrashed = newinfo['into_trash_dt'] ? "Yes" : "No";
                        var new_importance = newinfo['importance'];
                        var new_updated_dt = newinfo["updated_dt"];
                        var new_created_dt = newinfo["created_dt"];

                        if(new_isparked === 'Yes')
                        {
                            classname = "colorful-notice";
                        } else {
                            classname = "";
                        }
                        var markup_isparked = "<span class='" + classname + "'>"+new_isparked+"</span>";
                        if(new_istrashed === 'Yes')
                        {
                            classname = "colorful-warning";
                        } else {
                            classname = "";
                        }
                        var markup_istrashed = "<span class='" + classname + "'>"+new_istrashed+"</span>";
                        
                        var view_params = [];
                        view_params.push({'name':'brainstormitemid', 'value':brainstormid});
                        view_params.push({'name':'return', 'value':return_url});
                        var eye_img_markup = "<img src='" + eye_imgurl + "'>";
                        var linkurl_view_subject_markup = getLinkMarkup(view_address,view_params,eye_img_markup,"view #"+brainstormid);

                        var edit_params = [];
                        edit_params.push({'name':'brainstormitemid', 'value':brainstormid});
                        edit_params.push({'name':'return', 'value':return_url});
                        var edit_img_markup = "<img src='" + pencil_imgurl + "'>";
                        var linkurl_edit_subject_markup = getLinkMarkup(edit_address,edit_params,edit_img_markup,"edit #"+brainstormid);

                        var delete_params = [];
                        delete_params.push({'name':'brainstormitemid', 'value':brainstormid});
                        delete_params.push({'name':'return', 'value':return_url});
                        var delete_img_markup = "<img src='" + redx_imgurl + "'>";
                        var linkurl_delete_subject_markup = getLinkMarkup(delete_address,delete_params,delete_img_markup,"delete #"+brainstormid);
                    
                        var actionoptionsmarkup = linkurl_view_subject_markup + " " + linkurl_edit_subject_markup + " " + linkurl_delete_subject_markup;
                        
                        var cellValues = {};
                        cellValues['context_nm'] = new_context_nm;
                        cellValues['category_nm'] = nct;
                        cellValues['item_nm'] = new_item_nm;
                        cellValues['isparked'] = markup_isparked;
                        cellValues['istrashed'] = markup_istrashed;
                        cellValues['purpose_tx'] = new_purpose_tx;
                        cellValues['created_by'] = "user#"+new_owner_personid;  //TODO get the name!!!!
                        cellValues['importance'] = new_importance;
                        cellValues['updated_dt'] = new_created_dt;
                        cellValues['created_dt'] = new_created_dt;
                        cellValues['actionoptions'] = actionoptionsmarkup;

                        var rowAttributes = [];
                        controller.append(brainstormid, cellValues, rowAttributes, true);
                        //alert("LOOK did we add new");
                    }
                }
            }
            bigfathom_util.table.auto_datarefresh.markAllowed("applyNodeChanges");
        };
        
        function getLatestDataChanges()
        {
            if(bigfathom_util.table.auto_datarefresh.isBlocked())
            {
                //No refresh on this iteration, check again later.
                bigfathom_util.table.my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
            } else {
                //Perform a refresh now.
                bigfathom_util.table.auto_datarefresh.markBlocked("getLatestDataChanges");
                var grab_fullurl = bigfathom_util.data.getGrabDataUrl("brainstorm_updates"
                                ,{
                                     "projectid":bigfathom_util.table.projectid
                                    ,"previous_project_edit_key": previous_project_edit_key
                                    ,"previous_project_edit_timestamp": previous_project_edit_timestamp
                                    ,"include_trashed":1
                                    ,"include_parked":1
                                }
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
                        console.log("LOOK record.has_newdata=" + record.has_newdata);
                        if(!record.has_newdata && previous_project_edit_timestamp < record.most_recent_edit_timestamp)
                        {
                            alert("LOOK ERROR says no new data but there is new data!");
                        }
                        previous_project_edit_timestamp = record.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                        if(record.has_newdata)
                        {
                            applyNodeChanges(record.newdata.nodes);
                            bigfathom_util.table.finalizeAllGridCells(controller);
                        }
                    }

                    //Setup for another check to happen in a little while
                    bigfathom_util.table.auto_datarefresh.markAllowed("getLatestDataChanges");
                    bigfathom_util.table.my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
                };

                bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, previous_project_edit_key);
            }
            console.log("LOOK exit getLatestDataChanges!");
        };

        my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
    }
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
        var active_count_elem = "<div class='inline'><label for='active_count'>Active:</label><span class='showvalue' id='active_count'>" + active_count + "</span></div>";
        var isparked_count_elem = "<div class='inline'><label for='isparked_count'>Parked:</label><span class='showvalue' id='isparked_count'>" + isparked_count + "</span></div>";
        var istrashed_count_elem = "<div class='inline'><label for='istrashed_count'>Trashed:</label><span class='showvalue' id='istrashed_count'>" + istrashed_count + "</span></div>";
        var count_markup = "<fieldset class='elem-inline'><legend title='Count of Candidate Topics'>Count Totals</legend><div class='group-standard'>" 
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
        var value_pyn_cd = controller.getRowAttribute(rowIndex, "pyn_cd");
        var value_tyn_cd = controller.getRowAttribute(rowIndex, "tyn_cd");

        //var values = controller.getRowValues(rowIndex);
        var isparked = value_pyn_cd === 'Y';// values["isparked"];
        var istrashed = value_tyn_cd === 'Y'; //values["istrashed"];
        //var importance = values["importance"];
        if(isparked)
        {
            isparked_count++;
        } else if (istrashed){
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
