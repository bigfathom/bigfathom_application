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
        "version": "20171118.1",
        "filter_action_count":0,
        "custominfo": {}
    };
}

bigfathom_util.table.finalizeAllGridCells = function(controller) 
{
    var rowcount = controller.getRowCount();
    //var minmaxinfo = bigfathom_util.table.getMinMaxMetrics(controller);
    for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
    {
        //bigfathom_util.table.recomputeFormulas(controller, rowIndex, minmaxinfo);  
        bigfathom_util.table.recomputeEditableMarkings(controller, rowIndex);  
    }
    
    //bigfathom_util.table.showTotals(controller, minmaxinfo);
};

bigfathom_util.table.customRowMatchFunction = function(controller, row)
{
    var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
    
    var stepradio_groupname = filterareaid + '_stepgroup';

    var status_radio_groupname = filterareaid + '_statusgroup';
    
    var step_filter = $('input:radio[name=' + stepradio_groupname + ']:checked').val();
    
    var matches = true;
    if(matches && step_filter !== "none")
    {
        var step_nums = row.filter_step_nums;
        matches = step_nums.indexOf(step_filter) >= 0;
    }

    var status_filter = $('input:radio[name=' + status_radio_groupname + ']:checked').val();
    if(matches && status_filter !== "none")
    {
        var row_thread_status = row.filter_thread_status;
        matches = row_thread_status === status_filter;
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
    
    console.log("LOOK Drupal.settings=" + JSON.stringify(Drupal.settings));
    console.log("LOOK Drupal.settings.testcaseid=" + Drupal.settings.testcaseid);
    console.log("LOOK Drupal.settings.stepnum2detail=" + JSON.stringify(Drupal.settings.stepnum2detail));
    
    var stepnum2detail = JSON.parse(Drupal.settings.stepnum2detail)
    console.log("LOOK stepnum2detail=" + stepnum2detail);
    
    bigfathom_util.table.custominfo.default_stepnum = Drupal.settings.default_stepnum;
    bigfathom_util.table.custominfo.testcaseid = Drupal.settings.testcaseid;
    bigfathom_util.table.custominfo.stepnum2detail = stepnum2detail;
    
    var textfiltercontroldivid = controller.browserGridTableData.elementids.textfiltercontroldivid;
    var pagecontrolid = controller.browserGridTableData.elementids.pagesizecontrolid;
    
    $("#"+textfiltercontroldivid).addClass("browserGrid-filtermargins1em");
    $("#"+pagecontrolid).addClass("browserGrid-filtermargins1em");

    var filterareaid = controller.browserGridTableData.elementids.custom_table_filters_area_id;
    var markup_container_elem = document.getElementById(filterareaid);
    var id_radio_all = filterareaid + "_showall";
    
    var ownerradio_groupname = filterareaid + '_stepgroup';
    var status_radio_groupname = filterareaid + '_statusgroup';
    
    var markup_steps_start = "<fieldset class='elem-inline table-filter'><legend title='Filter the displayed rows on step number criteria'>Step Number Filter</legend><div class='group-standard'>" ;
    var markup_steps_end = "</div></fieldset>";

    var step_radios_ar = [];
    step_radios_ar.push(getOneRadioMarkup(bigfathom_util.table.custominfo.default_stepnum == null
        ,ownerradio_groupname
        ,id_radio_all
        ,'None'
        ,'none'
        ,'No filter on step number'));
    
    step_radios_ar.push(getOneRadioMarkup(bigfathom_util.table.custominfo.default_stepnum == 'blank'
        ,ownerradio_groupname
        ,'filter_blank_steps'
        ,'Blank'
        ,'blank'
        ,'Filter for no-steps mapped'));
        
    for (var stepnum in bigfathom_util.table.custominfo.stepnum2detail) 
    {
      if (bigfathom_util.table.custominfo.stepnum2detail.hasOwnProperty(stepnum)) 
      {
        console.log("LOOK write a radiobutton for stepnum" + stepnum);
        step_radios_ar.push(getOneRadioMarkup(bigfathom_util.table.custominfo.default_stepnum == stepnum, ownerradio_groupname
            ,'filter_step'+stepnum
            ,stepnum
            ,'s'+stepnum+'!'
            ,'filter on step#'+stepnum));
      }
    }

    var markup_stepnum_filter = markup_steps_start + step_radios_ar.join(' ') + markup_steps_end;
    
    var markup_status_start = "<fieldset class='elem-inline table-filter'><legend title='Filter on status of the communication thread'>Communication Status</legend><div class='group-standard'>" ;
    var markup_status_end = "</div></fieldset>";
    var status_radios_ar = [];
    status_radios_ar.push(getOneRadioMarkup(false
        ,status_radio_groupname
        ,'filter_thread_status_none'
        ,'None'
        ,'none'
        ,'No filter on comment/action request status'));
    status_radios_ar.push(getOneRadioMarkup(true
        ,status_radio_groupname
        ,'filter_thread_status_none'
        ,'Open'
        ,'open'
        ,'Only include threads that have not yet been resolved/closed/abandoned'));
    status_radios_ar.push(getOneRadioMarkup(false
        ,status_radio_groupname
        ,'filter_thread_status_closed'
        ,'Closed'
        ,'closed'
        ,'Only include threads that have been resolved/closed/abandoned'));
    var markup_comm_status_filter = markup_status_start + status_radios_ar.join(' ') + markup_status_end;
    
    markup_container_elem.innerHTML = markup_stepnum_filter + " " +  markup_comm_status_filter;
    
    //Create a listener to handle the clicks
    $('input:radio[name=' + ownerradio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
        bigfathom_util.table.filter_action_count++;
    });
    $('input:radio[name=' + status_radio_groupname + ']').on('change', function()
    {
        bigfathom_util.table.invokeFilter();
        bigfathom_util.table.filter_action_count++;
    });
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
