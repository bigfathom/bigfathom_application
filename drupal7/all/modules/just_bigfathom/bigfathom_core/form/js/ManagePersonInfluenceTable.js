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
        version: "20161117.1",
        lowest_default_scf: 0.01,
        new_member_default_dc: 0.95,
        rounding_factor: 10000,
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
    var level4 = minmaxinfo.count_map.level4;
    var level3 = minmaxinfo.count_map.level3;
    var level2 = minmaxinfo.count_map.level2;
    var level1 = minmaxinfo.count_map.level1;
    var level0 = minmaxinfo.count_map.level0;
    var unknown = minmaxinfo.count_map.unknown;
    
    var level4_elem = "<div class='inline' title='You care about this workitem and can have DIRECT influence in contributing to its successful completion'>"
                    + "<label for='level4'>Direct:</label><span class='showvalue' id='level4'>" + level4 + "</span></div>";
    var level3_elem = "<div class='inline' title='You care about this workitem and can have INDIRECT influence in contributing to its successful completion'>"
                    + "<label for='level3'>Indirect:</label><span class='showvalue' id='level3'>" + level3 + "</span></div>";
    var level2_elem = "<div class='inline' title='You CARE about this workitem but have NO INFLUENCE in contributing to its successful completion'>"
                    + "<label for='level2'>No Influence:</label><span class='showvalue' id='level2'>" + level2 + "</span></div>";
    var level1_elem = "<div class='inline' title='You DO NOT SEE ANY VALUE in completing this workitem BUT MIGHT have some INFLUENCE on its outcome'>"
                    + "<label for='level1'>No Interest:</label><span class='showvalue' id='level1'>" + level1 + "</span></div>";
    var level0_elem = "<div class='inline' title='You do not see any value in completing this workitem AND do not have any influence on its outcome'>"
                    + "<label for='level0'>None!:</label><span class='showvalue' id='level0'>" + level0 + "</span></div>";
    var unknown_elem = "<div class='inline' title='No influence has been assessed'>"
                    + "<label for='unknown'>Unknown:</label><span class='showvalue' id='unknown'>" + unknown + "</span></div>";
    var count_markup = "<fieldset class='elem-inline'><legend title='Count of influence by category'>Count Totals</legend><div class='group-standard'>" 
            + level4_elem
            + " "
            + level3_elem
            + " "
            + level2_elem
            + " "
            + level1_elem
            + " "
            + level0_elem
            + " "
            + unknown_elem
            + "</div></fieldset>";
    markup = "<div class='grid-totals-container'>" + count_markup + "</div>";
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var count_level4 = 0;
    var count_level3 = 0;
    var count_level2 = 0;
    var count_level1 = 0;
    var count_level0 = 0;
    var count_unknown = 0;
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var value_is_level4 = values["is_level4"];
        var value_is_level3 = values["is_level3"];
        var value_is_level2 = values["is_level2"];
        var value_is_level1 = values["is_level1"];
        var value_is_level0 = values["is_level0"];
        var value_is_unknown = values["is_unknown"];

        if(value_is_level4)
        {
            count_level4++;
        } else
        if(value_is_level3)
        {
            count_level3++;
        } else
        if(value_is_level2)
        {
            count_level2++;
        } else
        if(value_is_level1)
        {
            count_level1++;
        } else
        if(value_is_level0)
        {
            count_level0++;
        } else
        if(value_is_unknown)
        {
            count_unknown++;
        }
    }
    
    var themap = {};
    themap['count_map'] = {
            'unknown':count_unknown
          , 'level0': count_level0
          , 'level1': count_level1
          , 'level2': count_level2
          , 'level3': count_level3
          , 'level4': count_level4
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
    /*
    var values = controller.getRowValues(rowIndex);
    var value_is_connected_yn = values["is_connected_yn"];
    var colidx_is_member_yn = controller.getColumnIndex("is_member_yn");
    if(value_is_connected_yn)
    {
        controller.markCellLocked(rowIndex, colidx_is_member_yn);
    } else {
        controller.markCellEditable(rowIndex, colidx_is_member_yn);
    }
    */
};

/**
 * Call this to populate the computed fields of a row
 */
bigfathom_util.table.recomputeFormulas = function(controller, rowIndex, minmaxinfo, allowFullGridRefresh) 
{
    console.log("Starting recomputeFormulas for rowid=" + controller.getRowId(rowIndex) + " at rowIndex=" + rowIndex);
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
    
    function getImageURL(filename)
    {
        return url_img + "/" + filename;
    }
    
    function getDaysBetweenDates(start_dt, end_dt)
    {
        if(start_dt === null || end_dt == null || start_dt == '' || end_dt == '' || start_dt.length < 8  || end_dt.length < 8)
        {
            return null;
        }
        var isoparts_start_dt = start_dt.split("-");
        var isoparts_end_dt = end_dt.split("-");
        
        var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
        var date1 = new Date(isoparts_start_dt[0],isoparts_start_dt[1],isoparts_start_dt[2]);
        var date2 = new Date(isoparts_end_dt[0],isoparts_end_dt[1],isoparts_end_dt[2]);
        
        return Math.ceil((date2.getTime() - date1.getTime())/(oneDay)) + 1;
    };

    max_days = getDaysBetweenDates(min_date, max_date);

    var computedValue;
    var refreshAllRows = false;

    var values = controller.getRowValues(rowIndex);
    var value_is_level4 = values["is_level4"];
    var value_is_level3 = values["is_level3"];
    var value_is_level2 = values["is_level2"];
    var value_is_level1 = values["is_level1"];
    var value_is_level0 = values["is_level0"];
    var value_is_unknown = values["is_unknown"];
    var value_influence = values["is_influence"];
    
    if(allowFullGridRefresh && refreshAllRows)
    {
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
bigfathom_util.table.setOneRowInfluence = function(controller, rowIndex, influence)
{
console.log("LOOK we called setOneRowInfluence for rowIndex=" + rowIndex);    
//alert("LOOK we called setOneRowInfluence for rowIndex=" + rowIndex);
    var is_level4 = false;
    var is_level3 = false;
    var is_level2 = false;
    var is_level1 = false;
    var is_level0 = false;
    var is_unknown = false;
    var workitemid = controller.getRowId(rowIndex);
    var colidx_is_level4 = controller.getColumnIndex("is_level4")
    var colidx_is_level3 = controller.getColumnIndex("is_level3");
    var colidx_is_level2 = controller.getColumnIndex("is_level2");
    var colidx_is_level1 = controller.getColumnIndex("is_level1");
    var colidx_is_level0 = controller.getColumnIndex("is_level0");
    var colidx_is_unknown = controller.getColumnIndex("is_unknown");
    var colidx_influence = controller.getColumnIndex("influence");
    if(influence === null)
    {
        is_unknown = true;
    } else {
        if(influence >= 75)
        {
            is_level4 = true;
        } else
        if(influence >= 50)
        {
            is_level3 = true;
        } else
        if(influence >= 25)
        {
            is_level2 = true;
        } else
        if(influence > 0)
        {
            is_level1 = true;
        } else {
            is_level0 = true;
        }
    }
    
    var result_bundle = [];
    result_bundle['influence'] = influence;
    controller.setValueAt(rowIndex, colidx_influence, influence, true);
    controller.setValueAt(rowIndex, colidx_is_level4, is_level4, true);
    controller.setValueAt(rowIndex, colidx_is_level3, is_level3, true);
    controller.setValueAt(rowIndex, colidx_is_level2, is_level2, true);
    controller.setValueAt(rowIndex, colidx_is_level1, is_level1, true);
    controller.setValueAt(rowIndex, colidx_is_level0, is_level0, true);
    controller.setValueAt(rowIndex, colidx_is_unknown, is_unknown, true);
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
    var parent_projectid = controller.getRowAttribute(rowIndex, "data_parent_projectid");
    if(parent_projectid == "")  //Check with == not ===
    {
        throw "Did NOT get a parent_projectid value from the row!";
    }
    var personid = controller.getRowAttribute(rowIndex, "data_personid");
    if(personid == "")  //Check with == not ===
    {
        throw "Did NOT get a personid value from the row!";
    }
    
    var workitemid = controller.getRowId(rowIndex);
    if(workitemid == "")  //Check with == not ===
    {
        throw "Did NOT get a workitemid value from the row!";
    }
    
    if(colname !== 'is_level4' 
            && colname !== 'is_level3' 
            && colname !== 'is_level2' 
            && colname !== 'is_level1' 
            && colname !== 'is_level0' 
            && colname !== 'is_unknown' 
            && colname !== 'influence')
    {
        throw "Expected the influence to change!";
    }
    var change_comment;
    var values = controller.getRowValues(rowIndex);
    var value_influence;
    if(colname === 'is_level4')
    {
        value_influence = 90;
    } else
    if(colname === 'is_level3')
    {
        value_influence = 65;
    } else
    if(colname === 'is_level2')
    {
        value_influence = 40;
    } else
    if(colname === 'is_level1')
    {
        value_influence = 15;
    } else
    if(colname === 'is_level0')
    {
        value_influence = 0;
    } else
    if(colname === 'is_unknown')
    {
        value_influence = null;
    } else {
        value_influence = newValue; //values["influence"];
    }
    
    bigfathom_util.table.setOneRowInfluence(controller, rowIndex, value_influence);
    
    //Update this one row with other data
    var send_dataname = 'update_person_influence2wi';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    change_comment = "grid edit projectid#" + parent_projectid + " changed influence for personid#" + personid + " to " + value_influence + " on workitemid=" + workitemid + "";
    var databundle = {  
                        "projectid": parent_projectid,
                        "change_comment": change_comment
                    };
    if(value_influence !== null)
    {
        databundle['new'] = [];
        databundle['new'].push({'personid':personid, 'workitemid':workitemid, 'influence': value_influence, 'created_by_personid': personid});    
    } else {
        databundle['remove'] = [];
        databundle['remove'].push({'personid':personid, 'workitemid':workitemid, 'created_by_personid': personid});    
    }
    var sendpackage = {"dataname": send_dataname,
                       "databundle": databundle
                        };
    console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
    var callbackActionFunction = function(callbackid, responseBundle)
    {
        console.log("LOOK 111 modelChanged in callbackActionFunction responseBundle=" + JSON.stringify(responseBundle));
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
