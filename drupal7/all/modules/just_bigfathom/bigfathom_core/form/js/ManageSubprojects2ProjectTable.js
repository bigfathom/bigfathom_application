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
        version: "20170721.1",
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
    var membercount = minmaxinfo.member_count;
    var connectedcount = minmaxinfo.connected_count;
    if(membercount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no antecedent projects declared for this project.</div>";
    } else {
        
        var lowconfidence = minmaxinfo.min_scf;
        var highconfidence = minmaxinfo.max_scf;
        var simple_avg_scf =  Math.round(minmaxinfo.simple_avg_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        
        var markup_totals = "<fieldset class='elem-inline'><legend title='Simple totals of the subprojects'>Totals</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Declared as a candidate for linking as an antecedent in the project' for='membercount'>Declared Subprojects:</label><span class='showvalue' id='membercount'>" + membercount + "</span></div>" 
                + "<div class='inline'><label title='At least one specific dependency linking to the root goal of the project has been declared' for='connectedcount'>Connected Subprojects:</label><span class='showvalue' id='connectedcount'>" + connectedcount + "</span></div>" 
                + "</div></fieldset>";
        /*
        var markup_confidence = "<fieldset class='elem-inline'><legend title='The calculated confidence that the subproject will be completed by the declared end date'>Computed Confidence</legend><div class='group-standard'>" 
                + "<div class='inline'><label title='Simple mean between the lowest and the highest value' for='simple_avg_scf'>Simple Average:</label><span class='showvalue' id='simple_avg_scf'>" + simple_avg_scf + "</span></div>" 
                + "<div class='inline'><label for='lowconfidence'>Lowest:</label><span class='showvalue' id='lowconfidence'>" + lowconfidence + "</span></div>" 
                + "<div class='inline'><label for='highconfidence'>Highest:</label><span class='showvalue' id='highconfidence'>" + highconfidence + "</span></div>" 
                + "</div></fieldset>";
        */
        markup = "<div class='grid-totals-container'>" + markup_totals + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var member_count = 0;
    var connected_count = 0;
    var min_scf = 1;
    var max_scf = 0;
    
    var min_dt = '';
    var max_dt = '';
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var value_start_dt = values["start_dt"];
        var value_end_dt = values["end_dt"];
        var value_is_member_yn = values["is_member_yn"];
        var value_is_connected_yn = values["is_connected_yn"] ? 1 : 0;
        //var value_ot_scf = values["ot_scf"];

        if(value_is_member_yn)
        {
            member_count++;
            /*
            if(value_ot_scf > max_scf)
            {
                max_scf = value_ot_scf;    
            }
            if(value_ot_scf < min_scf)
            {
                min_scf = value_ot_scf;   
            }
            */
        }
        if(value_is_connected_yn)
        {
            connected_count++;
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
    
    var themap = {'min_date':min_dt, 'max_date':max_dt
        , 'member_count': member_count
        , 'connected_count': connected_count
        , 'min_scf': min_scf
        , 'max_scf': max_scf
        , 'simple_avg_scf': simple_avg_scf
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
    var values = controller.getRowValues(rowIndex);
    var value_is_connected_yn = values["is_connected_yn"];
    var colidx_is_member_yn = controller.getColumnIndex("is_member_yn");
    if(value_is_connected_yn)
    {
        controller.markCellLocked(rowIndex, colidx_is_member_yn);
    } else {
        controller.markCellEditable(rowIndex, colidx_is_member_yn);
    }
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
    var value_start_dt = values["start_dt"];
    var value_end_dt = values["end_dt"];
    var value_typeletter = bigfathom_util.table.getTypeLetterFromMarkup(values["typeletter"]);
    var value_start_dt_type_cd = values["start_dt_type_cd"];
    var value_end_dt_type_cd = values["end_dt_type_cd"];
    
    var est_flags = {};
    est_flags["startdate"] = value_start_dt_type_cd === 'E';
    est_flags["enddate"] = value_end_dt_type_cd === 'E';
    
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
bigfathom_util.table.setOneRowMembership = function(controller, rowIndex, make_member, workitem_info)
{
console.log("LOOK we called setOneRowMembership for rowIndex=" + rowIndex);    
//alert("LOOK we called setOneRowMembership for rowIndex=" + rowIndex);    
    var workitemid = controller.getRowId(rowIndex);
    var row_values = controller.getRowValues(rowIndex);
    var value_is_member_yn = row_values["is_member_yn"];
    //var value_computed_confidence = row_values["computed_confidence"];
    var default_scf = controller.getRowAttribute(rowIndex, "default_scf");
    var colidx_is_member_yn = controller.getColumnIndex('is_member_yn');
    //var colidx_ot_scf = controller.getColumnIndex('ot_scf');
    //var colidx_computed_confidence = controller.getColumnIndex('computed_confidence');;
    var result_bundle = {};
    result_bundle['new_member'] = !value_is_member_yn;
    if(make_member)
    {
        /*
        var value_ot_scf;
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_computed_confidence = default_scf;
        } else {
            value_computed_confidence = bigfathom_util.table.lowest_default_scf;
        }
        */
    } else {
        /*
        value_ot_scf = null;
        if(!isNaN(default_scf) && default_scf != '')
        {
            value_computed_confidence = default_scf;
        } else {
            //Blank it out
            value_computed_confidence = null;
        }
        */
    }
    /*
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
    */
    controller.setValueAt(rowIndex, colidx_is_member_yn, make_member, true);
    result_bundle['workitemid'] = workitemid;
    //result_bundle['ot_scf'] = value_ot_scf;
    //result_bundle['computed_confidence'] = value_computed_confidence;
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
    var subprojectid = controller.getRowId(rowIndex);
    if(colname !== 'is_member_yn' && colname !== 'ot_scf')
    {
        throw "Expected the membership or ot_scf to change!";
    }
    var change_comment;
    var values = controller.getRowValues(rowIndex);
    
    //Update this one row with other data
    var send_dataname = 'update_one_subproject_member';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    var is_member_yn;
    var is_connected_yn;
    var ot_scf;
    is_member_yn = values["is_member_yn"] ? 1 : 0;
    is_connected_yn = values["is_connected_yn"] ? 1 : 0;
    if(colname === 'ot_scf')
    {
        ot_scf = newValue;
        change_comment = "grid edit parent_projectid#" + parent_projectid + " for subprojectid#" + subprojectid + " changed scf from '" + oldValue + "' to '" + newValue + "'";
    } else if(colname === 'is_member_yn') {
        ot_scf = null;
        change_comment = "grid edit parent_projectid#" + parent_projectid + " for subprojectid#" + subprojectid + " changed membership from '" + oldValue + "' to '" + newValue + "'";
        bigfathom_util.table.setOneRowMembership(controller, rowIndex, is_member_yn, null); //Automatically picks a reasonable scf value
    } else {
        throw "No support for changes to colname=" + colname;
    }
    if(ot_scf == '')
    {
        ot_scf = bigfathom_util.table.new_member_default_dc;
    }
    var sendpackage = {"dataname": send_dataname,
                       "databundle":
                            {   "parent_projectid" : parent_projectid,
                                "subprojectid": subprojectid,
                                "is_member_yn": is_member_yn,
                                "ot_scf": ot_scf,
                                "change_comment": change_comment
                            }
                        };
    console.log("LOOK modelChanged sendpackage=" + JSON.stringify(sendpackage));
    var callbackActionFunction = function(callbackid, responseBundle)
    {
        console.log("LOOK 111 modelChanged in callbackActionFunction responseBundle=" + JSON.stringify(responseBundle));
        var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
        bigfathom_util.table.showTotals(controller, minmaxinfo);
        uiblocker.hide("tr#" + subprojectid);
    };
    uiblocker.show("tr#" + subprojectid);
    bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, subprojectid);

    console.log("LOOK modelChanged changes saved!: " + change_comment);

    //Now compute the formula cells and update the editable markings
    bigfathom_util.table.recomputeFormulas(controller, rowIndex);
    bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);

};
