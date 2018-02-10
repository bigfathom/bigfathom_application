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
        version: "20170604.1",
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

if(!bigfathom_util.table.datafetchers.hasOwnProperty("editmode"))
{
    bigfathom_util.table.datafetchers.editmode = {};
    bigfathom_util.table.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
    bigfathom_util.table.my_data_refresher = null;
    
    function setPanel(fetched_data)
    {
    }
}

bigfathom_util.table.datafetchers.editmode.refreshPanel = function()
{
};

bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    if(bigfathom_util.table.special_init_count < 1)
    {

        bigfathom_util.table.special_init_count++;
        bigfathom_util.table.personid = Drupal.settings.personid;
        bigfathom_util.table.groupid = Drupal.settings.groupid;
        bigfathom_util.table.myurls = Drupal.settings.myurls;
    }
    
    var rowcount = controller.getRowCount();
    var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo);  
        bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);  
    }
    
    bigfathom_util.table.showTotals(controller, minmaxinfo);
};

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var selectedcount = minmaxinfo.count_map.selected;
    var unselectedcount = minmaxinfo.count_map.unselected;
    if(selectedcount + unselectedcount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no people that are currently members of this group.</div>";
    } else {
        var selectedcount_elem = "<div class='inline'><label for='selectedcount'>Selected:</label><span class='showvalue' id='selectedcount'>" + selectedcount + "</span></div>";
        var unselectedcount_elem = "<div class='inline'><label for='unselectedcount'>Not Selected:</label><span class='showvalue' id='unselectedcount'>" + unselectedcount + "</span></div>";
        var count_markup = "<fieldset class='elem-inline'><legend title='Count of people'>Group Membership Metrics</legend><div class='group-standard'>" 
                + selectedcount_elem
                + " "
                + unselectedcount_elem
                + "</div></fieldset>";
        markup = "<div class='grid-totals-container'>" + count_markup + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var count_map = {};
    var selectedcount = 0;
    var unselectedcount = 0;
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var ismember = values["ismember"];
        if(ismember)
        {
            selectedcount++;
        } else {
            unselectedcount++;
        }
    }    
    count_map['selected'] = selectedcount;
    count_map['unselected'] = unselectedcount;
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
console.log("LOOK we called setOneRowMembership for rowIndex=" + rowIndex);    
//alert("LOOK we called setOneRowMembership for rowIndex=" + rowIndex);    
    var personid = controller.getRowId(rowIndex);
    var row_values = controller.getRowValues(rowIndex);
    var value_ismember = row_values["ismember"];
    var colidx_ismember = controller.getColumnIndex('ismember');
    var result_bundle = {};
    result_bundle['new_member'] = !value_ismember;
    controller.setValueAt(rowIndex, colidx_ismember, make_member, true);
    return result_bundle;
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
    var personid = controller.getRowId(rowIndex);
    var groupid = controller.getRowAttribute(rowIndex, "data_groupid");
    if(!colname === 'ismember')
    {
        throw "No support edit of column " + colname;
    }
    var change_comment;

    //Update this one row with other data
    var send_dataname = 'update_group_membership';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    var ismember = newValue;
    change_comment = "grid edit groupid#" + groupid + " changed membership for personid#" + personid + " from '" + oldValue + "' to '" + newValue + "'";
    var databundle = {  
                        "groupid": groupid,
                        "change_comment": change_comment
                    };
    if(ismember)
    {
        databundle['new'] = [personid];    
    } else {
        databundle['remove'] = [personid];    
    }
    var sendpackage = {"dataname": send_dataname,
                       "databundle": databundle
                        };
    console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
    var callbackActionFunction = function(callbackid, responseBundle)
    {
        uiblocker.hide("tr#" + personid);
    };
    uiblocker.show("tr#" + personid);
    bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, personid);

    console.log("LOOK modelChanged changes saved!: " + change_comment);

    //Now compute the formula cells and update the editable markings
    bigfathom_util.table.recomputeFormulas(controller, rowIndex);
    bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);
    bigfathom_util.table.finalizeAllGridCells(controller);
};

