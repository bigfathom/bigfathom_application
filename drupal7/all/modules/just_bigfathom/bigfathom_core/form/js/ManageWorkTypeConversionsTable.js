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
        version: "20160905.1",
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
    if(membercount === 0)
    {
        markup = "<div class='grid-totals-container'>There are no antecedent projects or prunable branches declared for this project.</div>";
    } else {
        
        //var lowconfidence = minmaxinfo.min_scf;
        //var highconfidence = minmaxinfo.max_scf;
        //var simple_avg_scf =  Math.round(minmaxinfo.simple_avg_scf * bigfathom_util.table.rounding_factor) / bigfathom_util.table.rounding_factor;
        
        var markup_totals = "<fieldset class='elem-inline'><legend title='Simple totals'>Totals</legend><div class='group-standard'>";
        for(var typeletter in minmaxinfo.type_counts)
        {
            if(minmaxinfo.type_counts.hasOwnProperty(typeletter))
            {
               markup_totals += "<div class='inline'><label title='Instances of workitem type " 
                       + typeletter + "'>Type "+ typeletter +":</label><span class='showvalue' id='connectedcount'>" 
                       + minmaxinfo.type_counts[typeletter] + "</span></div>"; 
            }
        }
        markup_totals += "</div></fieldset>";
        markup = "<div class='grid-totals-container'>" + markup_totals + "</div>";
    }
    
    totalscontainer_elem.innerHTML = markup;
};

bigfathom_util.table.getMinMaxMetrics = function(controller) 
{
    var member_count = 0;
    //var min_scf = 1;
    //var max_scf = 0;
    
    var min_dt = '';
    var max_dt = '';
    var type_counts = {};
    var rowcount = controller.getRowCount();
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        var values = controller.getRowValues(rowIndex);
        var value_start_dt = values["start_dt"];
        var value_end_dt = values["end_dt"];
        //var value_computed_confidence = values["computed_confidence"];
        var value_typeletter = bigfathom_util.table.getTypeLetterFromMarkup(values["typeletter"]);

        member_count++;
        if(type_counts.hasOwnProperty(value_typeletter))
        {
            type_counts[value_typeletter]++;
        } else {
            type_counts[value_typeletter]=1;
        }

        /*
        if(value_computed_confidence > max_scf)
        {
            max_scf = value_computed_confidence;    
        }
        if(value_computed_confidence < min_scf)
        {
            min_scf = value_computed_confidence;   
        }
        */
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
    //var simple_avg_scf = (min_scf + max_scf) / 2;
    
    var themap = {'min_date':min_dt, 'max_date':max_dt
        , 'member_count' : member_count
        , 'type_counts' : type_counts
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

