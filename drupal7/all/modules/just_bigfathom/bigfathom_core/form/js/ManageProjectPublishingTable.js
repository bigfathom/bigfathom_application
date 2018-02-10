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
        version: "20160830.1",
        lowest_default_scf: 0.01,
        rounding_factor: 10000
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

bigfathom_util.table.showTotals = function(controller, minmaxinfo) 
{
    var markup;
    var totalscontainer_elem = document.getElementById(controller.browserGridTableData.elementids.totalscontainerid);
    var membercount = minmaxinfo.colmap.length;
    if(membercount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no role declarations for the workitem.</div>";
    } else {
        var effort_elems = [];
        var count_elems = [];
        for(var i=0; i<minmaxinfo.colmap.length; i++)
        {
            var roleinfo = minmaxinfo.colmap[i];
            var name = roleinfo['name'];
            var label = roleinfo['label'];
            var roleid = roleinfo['roleid'];
            var role_count = minmaxinfo.count_map[roleid];
            var role_effort = minmaxinfo.effort_map[roleid];
            var count_elem = "<div class='inline'><label for='count_" + name + "'>" + label + ":</label><span class='showvalue' id='count_" + name + "'>" + role_count + "</span></div>";
            var effort_elem = "<div class='inline'><label for='effort_" + name + "'>" + label + ":</label><span class='showvalue' id='effort_" + name + "'>" + role_effort + "</span></div>";
            count_elems.push(count_elem);
            effort_elems.push(effort_elem);
        }
        var count_markup = "<fieldset class='elem-inline'><legend title='Count of workitems associated with each role'>Count Totals</legend><div class='group-standard'>" 
                + count_elems.join(" ") 
                + "</div></fieldset>";
        
        var effort_markup = "<fieldset class='elem-inline'><legend title='Simple effort totals from the displayed workitems'>Effort Totals</legend><div class='group-standard'>" 
                + effort_elems.join(" ") 
                + "</div></fieldset>";
        markup = "<div class='grid-totals-container'>" + count_markup + effort_markup + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var count_map = {};
    var effort_map = {};
    var colmap = [];
    
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        var colname = column.name;
        if(colname.startsWith('role_'))
        {
            var roleid = column.name.split("_")[1];
            count_map[roleid] = 0;
            effort_map[roleid] = 0;
            colmap.push({"columnIndex":columnIndex, "roleid":roleid, "name":column.name, "label":column.label});
        }
    }

    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        for(var i=0; i < colmap.length; i++)
        {
            var roleid = colmap[i]['roleid'];
            var name = colmap[i]['name'];
            var has_role = values[name];
            if(has_role)
            {
                count_map[roleid] += 1;
                var value_effort_hours = values["effort_hours"];
                if(value_effort_hours > 0)
                {
                    effort_map[roleid] += value_effort_hours;
                }
            }
        }
    }    

    var themap = {
              'effort_map':effort_map
            , 'count_map':count_map
            , 'colmap':colmap
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
    //var value_typeletter = controller.getRowAttribute(rowIndex, "typeletter");
    for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
    {
        var column = controller.getColumn(columnIndex);
        var colname = column.name;
        if(colname.startsWith('role_'))
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
                controller.markCellApplicable(rowIndex, columnIndex);
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
    var workitemid = controller.getRowId(rowIndex);
    var row_values = controller.getRowValues(rowIndex);
    var value_is_member_yn = row_values["is_member_yn"];
    var default_scf = controller.getRowAttribute(rowIndex, "default_scf");
    var colidx_is_member_yn = controller.getColumnIndex('is_member_yn');
    var colidx_ot_scf = controller.getColumnIndex('ot_scf');
    var result_bundle = {};
    result_bundle['new_member'] = !value_is_member_yn;
    if(make_member)
    {
        var value_ot_scf;
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_ot_scf = default_scf;
        } else {
            value_ot_scf = bigfathom_util.table.lowest_default_scf;
        }
    } else {
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_ot_scf = default_scf;
        } else {
            //Blank it out
            value_ot_scf = null;
        }
    }
    if(value_ot_scf === null || isNaN(value_ot_scf) )
    {
        controller.setValueAt(rowIndex, colidx_ot_scf, '', true);
    } else {
        var value_rounded_scf = Math.round(value_ot_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        controller.setValueAt(rowIndex, colidx_ot_scf, value_rounded_scf, true);
    }
    controller.setValueAt(rowIndex, colidx_is_member_yn, make_member, true);
    result_bundle['workitemid'] = workitemid;
    result_bundle['ot_scf'] = value_ot_scf;
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
    var workitemid = controller.getRowId(rowIndex);
    if(!colname.startsWith('role_'))
    {
        throw "No support edit of column " + colname;
    }
    var roleid = colname.split("_")[1];
    var change_comment;
    var values = controller.getRowValues(rowIndex);
    
    //Update this one row with other data
    var send_dataname = 'update_workitem_roles';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    var has_role = newValue;
    change_comment = "grid edit workitemid#" + workitemid + " changed has role#" + roleid + " from '" + oldValue + "' to '" + newValue + "'";
    var databundle = {  
                        "workitemid": workitemid,
                        "change_comment": change_comment
                    };
    if(has_role)
    {
        databundle['new'] = [roleid];    
    } else {
        databundle['remove'] = [roleid];    
    }
    var sendpackage = {"dataname": send_dataname,
                       "databundle": databundle
                        };
    console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
    var callbackActionFunction = function(callbackid, responseBundle)
    {
        console.log("LOOK 111 modelChanged in callbackActionFunction responseBundle=" + JSON.stringify(responseBundle));
        uiblocker.hide("tr#" + workitemid);
    };
    uiblocker.show("tr#" + workitemid);
    bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, workitemid);

    console.log("LOOK modelChanged changes saved!: " + change_comment);

    //Now compute the formula cells and update the editable markings
    bigfathom_util.table.recomputeFormulas(controller, rowIndex);
    bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);
    bigfathom_util.table.finalizeAllGridCells(controller);
};
