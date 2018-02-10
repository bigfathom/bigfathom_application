/* 
 * Functions for working with test case user pages
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
if(!bigfathom_util.hasOwnProperty("testcase_ui_toolkit"))
{
    //Create the object property because it does not already exist
    bigfathom_util.testcase_ui_toolkit = {
        "version": "20171206.1",
        "testcaseid": null,
        "personid": null,
        "form_mode": null,
        "dialoganchorid": "dialog-anchor",
        "id_steps_table": "testcase-steps-table",
        "id_steps_table_bottomaction": "testcase-steps-table-actionbuttons",
        "name_steps_encoded_tx": "steps_encoded_tx",
        "io":{
            "communications_summary":{"name":"communications_summary","counter":0}, 
            "update_testcasestep_status":{"name":"update_testcasestep_status","counter":0}
        },
        "max_steps": 20,
        "nextnewidsuffix":0,
        "initial_steps_encoded_tx": null,
        "step_info_changed":false,
        "base_url": null,
        "imgurls_by_purposename":{},
        "pagekeys_by_purposename":{}, 
        "dialog_handle": null,
        "fa_by_purposename":{
            'addrow': 'fa-plus',
            'delete': 'fa-remove',
            'move': 'fa-arrows-v',
            'comm0': 'fa-comment-o',
            'comm1': 'fa-comment'
        }
    };
}

bigfathom_util.testcase_ui_toolkit.getCommunicationSummary = function(userInterfaceUpdater)
{
    console.log("LOOK starting getCommunicationSummary");
    
    var processDataFromServer = function(callbackid, responseBundle)
    {
        console.log("LOOK we got result back! callbackid=" + callbackid);
        console.log("LOOK result=" + JSON.stringify(responseBundle));
        userInterfaceUpdater(responseBundle);
    }
                    
    var grab_dataname = bigfathom_util.testcase_ui_toolkit.io.communications_summary.name;
    var grab_results_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"contextname": 'testcasestep', "contextid_selector": bigfathom_util.testcase_ui_toolkit.testcaseid}); 

    console.log("LOOK testcaseid#" + bigfathom_util.testcase_ui_toolkit.testcaseid + " grab_results_fullurl=" + grab_results_fullurl);

    bigfathom_util.data.getDataFromServer(grab_results_fullurl, {}, processDataFromServer, ++bigfathom_util.testcase_ui_toolkit.io.communications_summary.counter);
};

bigfathom_util.testcase_ui_toolkit.writeStepStatus = function(e)
{
    console.log("LOOK called writeStepStatus with " + JSON.stringify(e));
    var selection_elem = $('#' + e.target.id);
    var status_cd = selection_elem.val();
    var tr_elem = selection_elem.closest("tr");
    var stepid = tr_elem.find('.stepid').text();
    //alert("LOOK 222222222 write status called! stepid="+stepid + " with status_cd="+status_cd);

    //Update this one row with other data
    var send_dataname = bigfathom_util.testcase_ui_toolkit.io.update_testcasestep_status.name;// 'update_testcasestep_status';
    var send_fullurl = bigfathom_util.data.getSendDataUrl(send_dataname);
    var databundle = {  
                        "testcasestepid": stepid,
                        "status_cd": status_cd
                    };
    var sendpackage = {"dataname": send_dataname,
                       "databundle": databundle
                        };
    console.log("LOOK sendpackage=" + JSON.stringify(sendpackage));
    var callbackActionFunction = function(callbackid, responseBundle)
    {
        //uiblocker.hide('#' + e.target.id);
        console.log("LOOK status changes for '" + callbackid + "' saved!");
    };
    //uiblocker.show('#' + e.target.id);
    bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, stepid);
};

bigfathom_util.testcase_ui_toolkit.getTableRow = function(idsuffix)
{
    var stepitemid = "stepitem" + idsuffix;
    var stepitem_elem = $('#' + stepitemid);
    var tr_elem = stepitem_elem.closest("tr");
    return tr_elem;
};

bigfathom_util.testcase_ui_toolkit.getDescriptionText = function(idsuffix)
{
    var descriptionid = "description" + idsuffix;
    return $('#' + descriptionid).val();
};
    
bigfathom_util.testcase_ui_toolkit.finalizeTable = function(trigger_source)
{
    if(trigger_source === undefined)
    {
        trigger_source = 'GENERAL';
    }
    
    var attachEventHanlders = function()
    {
        $('.stepstatus').off('change').on('change',function(e){
            bigfathom_util.testcase_ui_toolkit.writeStepStatus(e);
        });

        $('input').off('input').on('input',function(e){
            bigfathom_util.testcase_ui_toolkit.step_info_changed = true;
        });
        $('input').off('change').on('change',function(e){
            bigfathom_util.testcase_ui_toolkit.step_info_changed = true;
        });
        $('textarea').off('input').on('input',function(e){
            bigfathom_util.testcase_ui_toolkit.step_info_changed = true;
        });
        $('textarea').off('change').on('change',function(e){
            bigfathom_util.testcase_ui_toolkit.step_info_changed = true;
        });

        $('#' + bigfathom_util.testcase_ui_toolkit.id_steps_table).off('focusout').on('focusout',function(e)
        {
            if(bigfathom_util.testcase_ui_toolkit.step_info_changed)
            {
                bigfathom_util.testcase_ui_toolkit.step_info_changed = false;
                bigfathom_util.testcase_ui_toolkit.copyInputs2EncodedTextfield();
            }
        });

        $('input').off('blur').blur(function() {
            bigfathom_util.testcase_ui_toolkit.copyInputs2EncodedTextfield();
        });
        $('textarea').off('blur').blur(function() {
            bigfathom_util.testcase_ui_toolkit.copyInputs2EncodedTextfield();
        });
    };
    
    var updateStepNumbers = function()
    {
        var newstepnum = 0;
        $('span.stepitem').each(function(k,v) 
        {
          var stepnumElem = $(v);
          newstepnum++;
          stepnumElem.html(newstepnum);
        });
    };
    
    var updateCommunicationUI = function(response)
    {
        console.log("LOOK CALLED update com ui! response="+JSON.stringify(response));
        if(!response.hasOwnProperty('data') || !response.data.hasOwnProperty('data'))
        {
            //No data to process
            return;
        }
        var summaryData = response.data.data;
        console.log("LOOK summaryData="+JSON.stringify(summaryData));
        var visited_stepids = {};
        for (var stepid in summaryData) 
        {
            if (stepid === null || summaryData.hasOwnProperty(stepid)) 
            {
                console.log("LOOK IN LOOP stepid ="+stepid);
                var detail = summaryData[stepid];
                console.log("LOOK at stepid#" + stepid + " detail="+JSON.stringify(detail));
                if(stepid === null)
                {
                    //INFO type TODO
                } else {
                    //One step
                    var linkElem = $("span[data_stepid='" + stepid + "']");
                    var idsuffix = linkElem.attr('data_idsuffix');
                    var comm_markup = bigfathom_util.testcase_ui_toolkit.geCommunicationElementMarkup(idsuffix, stepid, detail);
                    linkElem.replaceWith(comm_markup);
                    //linkElem.replaceWith("<h2>stepid#" +stepid+ " id=" + existingID + " idsuffix=" + idsuffix + "</h2>");
                    visited_stepids[stepid] = 'VISITED';
                }
            }
        }
        //Now clear all the NOT visited elements to mark them empty!
        $(".action-option-comm").each(function() {
            var stepid = $(this).attr('data_stepid');
            console.log("LOOK checking elem stepid="+stepid);
            if(!visited_stepids.hasOwnProperty(stepid))
            {
                //This one is now empty!
                var idsuffix = $(this).attr('data_idsuffix');
                var comm_markup = bigfathom_util.testcase_ui_toolkit.geCommunicationElementMarkup(idsuffix, stepid);
                $(this).replaceWith(comm_markup);
            }
        });
    };
    
    var handleDropEvent = function( event, ui ) 
    {
        var draggable = ui.draggable;
        
        var draggedTR = $('#' + draggable.attr('id')).closest('tr');
        var droppedTR = $('#' + event.target.id);
        
        console.log( 'The square with ID "' + draggable.attr('id') + '" was dropped onto me! node=' + event.target.nodeName + ' id=' + event.target.id);
        
        //Get the values of the input boxes into variables because copy of HTML is NOT ENOUGH!
        var dragged_d_Text = draggedTR.find(".stepdescription").val();
        var dragged_e_Text = draggedTR.find(".stepexpectation").val();
        var dropped_d_Text = droppedTR.find(".stepdescription").val();
        var dropped_e_Text = droppedTR.find(".stepexpectation").val();
        
        var buffer = draggedTR.html();
        draggedTR.html(droppedTR.html());
        droppedTR.html(buffer);
        
        draggedTR.find(".stepdescription").val(dropped_d_Text);
        draggedTR.find(".stepexpectation").val(dropped_e_Text);
        droppedTR.find(".stepdescription").val(dragged_d_Text);
        droppedTR.find(".stepexpectation").val(dragged_e_Text);
        
        updateStepNumbers();
    };
      
    var handleStopEvent = function( event, ui ) 
    {
        $('#draggableHelper').remove();
    };
    
    var handleStartEvent = function( event, ui ) 
    {
    };
    
    function myHelper( event ) {
        return '<div id="draggableHelper">Drop to reposition the step elsewhere in the sequence</div>';
    };
    
    $('.stepitem').draggable( {
        containment: '#' + bigfathom_util.testcase_ui_toolkit.id_steps_table,
        cursor: 'move',
        snap: true,
        helper: myHelper,
        start: handleStartEvent,
        stop: handleStopEvent
      } );
    
    $('#' + bigfathom_util.testcase_ui_toolkit.id_steps_table + ' > tbody > tr').droppable( {
        drop: handleDropEvent
    } );
    
    updateStepNumbers();
    attachEventHanlders();
    
    if(trigger_source === 'INIT' || trigger_source === 'COMM_ACTIONS')
    {
        bigfathom_util.testcase_ui_toolkit.getCommunicationSummary(updateCommunicationUI);
    }
    
};

bigfathom_util.testcase_ui_toolkit.actionSendNotifications = function()
{
    
    var getFQURL = function()
    {
        var page_key = bigfathom_util.testcase_ui_toolkit.pagekeys_by_purposename['send_notifications'];
        if(!page_key.endsWith('_indialog'))
        {
            page_key += '_indialog';
        }
        var page = bigfathom_util.testcase_ui_toolkit.base_url 
                + "?q=" + page_key;
        
        page += "&testcaseid=" + bigfathom_util.testcase_ui_toolkit.testcaseid;
        page += "&dialoganchorid=" + bigfathom_util.testcase_ui_toolkit.dialoganchorid;
        
        return page;
    };
    
    var page = getFQURL();
    
    var $dialog = $('#'+bigfathom_util.testcase_ui_toolkit.dialoganchorid)
      .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="100%"></iframe>')
      .dialog({
        title: "Send Notifications of Test Case Status",
        autoOpen: false,
        dialogClass: 'dialog_fixed,ui-widget-header',
        modal: true,
        width: 1400,
        height: 600,
        minWidth: 900,
        minHeight: 400,
        draggable:true,
        /*close: function () { $(this).remove(); },*/
        /*buttons: { "Close": function () { 
                        $(this).dialog("close"); 
                    } 
                 }
        */
      });
    $dialog.dialog('open');
    bigfathom_util.testcase_ui_toolkit.dialog_handle = $dialog;
    console.log("LOOK did it open the dialog?");
    
};

bigfathom_util.testcase_ui_toolkit.closeDialog = function(showcode)
{
    $dialog = bigfathom_util.testcase_ui_toolkit.dialog_handle;
    $dialog.dialog("close");
    if(showcode==1)
    {
        alert("Message Sent");
    }
};

bigfathom_util.testcase_ui_toolkit.actionCommunicateRow = function(testcasestepid)
{
    
    var getCommFQURL = function()
    {
        var page_key = bigfathom_util.testcase_ui_toolkit.pagekeys_by_purposename['comments'];
        if(!page_key.endsWith('_indialog'))
        {
            page_key += '_indialog';
        }
        var page = bigfathom_util.testcase_ui_toolkit.base_url 
                + "?q=" + page_key;
        
        page += "&testcaseid=" + bigfathom_util.testcase_ui_toolkit.testcaseid;
        page += "&testcasestepid=" + testcasestepid;
        page += "&dialoganchorid=" + bigfathom_util.testcase_ui_toolkit.dialoganchorid;
        
        return page;
    };
    
    var page = getCommFQURL();
    
    console.log("LOOK will open " + page);

    var $dialog = $('#'+bigfathom_util.testcase_ui_toolkit.dialoganchorid)
      .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="100%"></iframe>')
      .dialog({
        title: "Comments and Action Items of Test Case",
        autoOpen: false,
        dialogClass: 'dialog_fixed,ui-widget-header',
        modal: true,
        width: 1400,
        height: 600,
        minWidth: 900,
        minHeight: 400,
        draggable:true,
        /*close: function () { $(this).remove(); },*/
        buttons: { "Close": function () { 
                        $(this).dialog("close"); 
                        console.log('LOOK WE HIT CLOSE');
                        //bigfathom_util.testcase_ui_toolkit.getCommunicationSummary();
                        bigfathom_util.testcase_ui_toolkit.finalizeTable('COMM_ACTIONS');
                        //alert("LOOK DID WE UPDATE COMM STUFF?");
                    } 
                 }
      });
    $dialog.dialog('open');
    console.log("LOOK did it open the dialog?");
    
};

bigfathom_util.testcase_ui_toolkit.actionAddRow = function(idsuffix)
{
    var newidsuffix = bigfathom_util.testcase_ui_toolkit.nextnewidsuffix++;
    var row_markup = bigfathom_util.testcase_ui_toolkit.getTableRowMarkup(newidsuffix, null, 0, "", "", "NONE");
    if(idsuffix === null)
    {
        $('#' + bigfathom_util.testcase_ui_toolkit.id_steps_table + ' > tbody:last-child').append(row_markup); 
    } else {
        var tr_elem = bigfathom_util.testcase_ui_toolkit.getTableRow(idsuffix);
        tr_elem.before(row_markup); 
    }
    bigfathom_util.testcase_ui_toolkit.finalizeTable();
};

bigfathom_util.testcase_ui_toolkit.actionDeleteRow = function(idsuffix)
{
    var text = bigfathom_util.testcase_ui_toolkit.getDescriptionText(idsuffix);
    if (text.trim().length === 0 || confirm('Discard all the content of the step?')) 
    {
        var tr_elem = bigfathom_util.testcase_ui_toolkit.getTableRow(idsuffix);
        tr_elem.remove();
        bigfathom_util.testcase_ui_toolkit.finalizeTable();
    } else {
        alert("Nothing deleted.");
    }
};

bigfathom_util.testcase_ui_toolkit.geCommunicationElementMarkup = function(idsuffix, stepid, summary_info)
{
    if(summary_info === undefined || summary_info === null)
    {
        summary_info = {};
    }
    
    var getHelpText = function(summary_info)
    {
        var tooltip;
        if(!summary_info.hasOwnProperty('all_active') || summary_info.all_active < 1)
        {
            //EMPTY
            tooltip = "No action requests currently pending";
        } else {
            //NOT EMPTY
            var ar = [];
            
            if(summary_info.count_by_thread_summary_status.open.arch > 0)
            {
                ar.push(summary_info.count_by_thread_summary_status.open.arch + " high");
            }
            
            if(summary_info.count_by_thread_summary_status.open.arcm > 0)
            {
                ar.push(summary_info.count_by_thread_summary_status.open.arcm + " medium");
            }
            
            if(summary_info.count_by_thread_summary_status.open.arcl > 0)
            {
                ar.push(summary_info.count_by_thread_summary_status.open.arcl + " low");
            }
            
            if(ar.length > 0)
            {
                tooltip = "Action requests currently pending: " + ar.join(' and ');
            } else {
                tooltip = summary_info.total_info_count + " comments and zero action requests";
            }
        } 
        return tooltip;
    };
    
    var markup = null;
    if(stepid !== null && stepid > 0)
    {
        var imgurls_by_purposename = bigfathom_util.testcase_ui_toolkit.imgurls_by_purposename;
        var markup_ar = [];
        var oneicon;
        var tooltip = getHelpText(summary_info);//"Communication artifacts for this step";
        if(!summary_info.hasOwnProperty('all_active') || summary_info.all_active < 1)
        {
            //CURRENTLY EMPTY
            var closedcount;
            if(!summary_info.hasOwnProperty('count_by_thread_summary_status'))
            {
                closedcount = 0;
            } else {
                closedcount = summary_info.count_by_thread_summary_status.closed.arch 
                    + summary_info.count_by_thread_summary_status.closed.arcm 
                    + summary_info.count_by_thread_summary_status.closed.arcl;
            }
            if(closedcount == 0)
            {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_empty'] + '">';
            } else {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_action_closed'] + '">';
            }
        } else {
            //NOT EMPTY
            if(summary_info.count_by_thread_summary_status.open.arch > 0)
            {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_action_high'] + '">';
            } else
            if(summary_info.count_by_thread_summary_status.open.arcm > 0)
            {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_action_medium'] + '">';
            } else
            if(summary_info.count_by_thread_summary_status.open.arcl > 0)
            {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_action_low'] + '">';
            } else {
                oneicon = '<img src="' + imgurls_by_purposename['communicate_hascontent'] + '">';
            }
        } 
        
        var elemid = 'comm' + idsuffix;
        markup_ar.push('<span data_idsuffix="' + idsuffix + '" data_stepid="'+ stepid +'" class="action-option-comm" onclick="bigfathom_util.testcase_ui_toolkit.actionCommunicateRow(' + stepid +');" id="'+ elemid +'" title="'+ tooltip +'">');
        markup_ar.push(oneicon);
        markup_ar.push('</span>');
        markup = '<span class="action-options click-for-action">' + markup_ar.join("&nbsp;&nbsp;") + '</span>';
    }
    return markup;
};

bigfathom_util.testcase_ui_toolkit.getTableRowMarkup = function(idsuffix, stepid, step_num, description_tx, expectation_tx, status_cd)
{
    var action_options;
    var icon_move_markup;
    var input_description_markup;
    var input_expectation_markup;
    var input_status_markup;

    var formtype = bigfathom_util.testcase_ui_toolkit.formtype;
    var imgurls_by_purposename = bigfathom_util.testcase_ui_toolkit.imgurls_by_purposename;

    var getActionMarkup = function(actions_ar)
    {
        var markup_ar = [];
        for(var i=0; i<actions_ar.length; i++)
        {
            var onemarkup_ar = [];
            var purposename = actions_ar[i];
            var oneicon;
            var elemid;
            if(purposename === 'addrow')
            {
                oneicon = imgurls_by_purposename[purposename];
                elemid = 'addrow' + idsuffix;
                onemarkup_ar.push('<span class="action-option-addrow" onclick="bigfathom_util.testcase_ui_toolkit.actionAddRow(' + idsuffix +');" id="'+ elemid +'" title="Insert a new step before this one">');
                onemarkup_ar.push(oneicon);
                onemarkup_ar.push('</span>');
            } else
            if(purposename === 'delete')
            {
                oneicon = imgurls_by_purposename[purposename];
                elemid = 'delete' + idsuffix;
                onemarkup_ar.push('<span class="action-option-delete" onclick="bigfathom_util.testcase_ui_toolkit.actionDeleteRow(' + idsuffix +');" id="'+ elemid +'" title="Delete this step">');
                onemarkup_ar.push(oneicon);
                onemarkup_ar.push('</span>');
            } else {
                var comm_markup = bigfathom_util.testcase_ui_toolkit.geCommunicationElementMarkup(idsuffix, stepid);
                if(comm_markup !== null)
                {
                    onemarkup_ar.push(comm_markup);
                }
                /*
                if(stepid !== null && stepid > 0)
                {
                    oneicon = '<img src="' + imgurls_by_purposename['communicate_empty'] + '">';
                    elemid = 'comm' + idsuffix;
                    onemarkup_ar.push('<span data_stepid="'+ stepid +'" class="action-option-comm" onclick="bigfathom_util.testcase_ui_toolkit.actionCommunicateRow(' + stepid +');" id="'+ elemid +'" title="Communication artifacts for this step">');
                    onemarkup_ar.push(oneicon);
                    onemarkup_ar.push('</span>');
                }
                */
            }
                
            var onemarkup = onemarkup_ar.join("");
            markup_ar.push(onemarkup);
        }
        var markup = '<span class="action-options click-for-action">' + markup_ar.join("&nbsp;&nbsp;") + '</span>';
        return markup;
    };

    var actions_ar;
    var rows_cells = [];
    var stepstatus_class = 'stepstatus stepstatus-' + status_cd.toLowerCase();
    if(bigfathom_util.testcase_ui_toolkit.formtype === 'A')
    {
        actions_ar = ['addrow','delete'];   //Cannotdo com without existing testcaseid
        icon_move_markup = '<span id="stepitem' + idsuffix + '" class="grabbable stepitem" title="Drag/Drop the step into a different position">' + step_num + '</span><span class="stepid hidden">' + stepid + '</span>';
        rows_cells.push(icon_move_markup);
        input_description_markup = '<textarea class="fillbox stepdescription" id="description' + idsuffix + '">' + description_tx + '</textarea>';
        rows_cells.push(input_description_markup);
        input_expectation_markup = '<textarea class="fillbox stepexpectation" id="expectation' + idsuffix + '">' + expectation_tx + '</textarea>';
        rows_cells.push(input_expectation_markup);
        action_options = getActionMarkup(actions_ar);
        rows_cells.push(action_options);
    } else if(bigfathom_util.testcase_ui_toolkit.formtype === 'E') {
        actions_ar = ['addrow','delete','comm'];
        icon_move_markup = '<span id="stepitem' + idsuffix + '" class="grabbable stepitem" title="Drag/Drop the step into a different position">' + step_num + '</span><span class="stepid hidden">' + stepid + '</span>';
        rows_cells.push(icon_move_markup);
        input_description_markup = '<textarea class="fillbox stepdescription" id="description' + idsuffix + '">' + description_tx + '</textarea>';
        rows_cells.push(input_description_markup);
        input_expectation_markup = '<textarea class="fillbox stepexpectation" id="expectation' + idsuffix + '">' + expectation_tx + '</textarea>';
        rows_cells.push(input_expectation_markup);
        rows_cells.push('<span id="stepstatus' + idsuffix + '" class="'+ stepstatus_class +'" title="Status is set during test execution mode">' + status_cd + '</span>');
        action_options = getActionMarkup(actions_ar);
        rows_cells.push(action_options);
    } else if(bigfathom_util.testcase_ui_toolkit.formtype === 'T') {
        //Tester is evaluating the steps
        actions_ar = ['communicate_empty'];
        icon_move_markup = step_num + '<span class="stepid hidden">' + stepid + '</span>';
        rows_cells.push(icon_move_markup);
        rows_cells.push(description_tx);
        rows_cells.push(expectation_tx);
        var status_values_ar = ["NONE","PASS","FAIL"];
        var status_options_ar = [];
        //status_cd = 'PASS';
        for(var o=0;o<status_values_ar.length;o++)
        {
            var myvalue = status_values_ar[o];
            var selected_tx;
            if(myvalue == status_cd)
            {
                selected_tx = " selected=selected ";
            } else {
                selected_tx = "";
            }
            var markup = '<option ' + selected_tx + ' value="' + myvalue + '">' + myvalue + "</option>";
            status_options_ar.push(markup);
        }
        var input_status_markup = '<select class="stepstatus" id="status' + idsuffix + '">' + status_options_ar.join("\n") + '</select>';
        rows_cells.push(input_status_markup);
        action_options = getActionMarkup(actions_ar);
        rows_cells.push(action_options);
    } else {
        //Readonly
        actions_ar = ['communicate_empty'];
        icon_move_markup = step_num;
        rows_cells.push(icon_move_markup);
        rows_cells.push(description_tx);
        rows_cells.push(expectation_tx);
        rows_cells.push('<span id="stepstatus' + idsuffix + '" class="'+ stepstatus_class +'" title="Status is set during test execution mode">' + status_cd + '</span>');
        action_options = getActionMarkup(actions_ar);
        rows_cells.push(action_options);
    }
    
    var row_markup = '<tr id="handle' + idsuffix + '"><td>' + rows_cells.join('</td><td>') + '</td></tr>' ;

    return row_markup;
};

bigfathom_util.testcase_ui_toolkit.handleDropEvent = function( event, ui )
{
    //alert("LOOK DEPRECATED dropped thing");
};

bigfathom_util.testcase_ui_toolkit.copyInputs2EncodedTextfield = function()
{
    console.log("LOOK starting copy");
    var steps_data = {
        "sequence":[]
    };
    
    var stepid_ar = [];
    var description_ar = [];
    var expectation_ar = [];
    var status_ar = [];
    $( "span.stepid" ).each(function(k,v) 
    {
        var stepid_tx = $( this ).text();
        console.log('LOOK stepid ' + k + ": " + stepid_tx );
        stepid_ar.push(stepid_tx);
    });
    $( "textarea.stepdescription" ).each(function(k,v) 
    {
        console.log('LOOK stepdescription ' + k + ": " + $( this ).val() );
        var description_tx = $( this ).val();
        description_ar.push(description_tx);
    });
    $( "textarea.stepexpectation" ).each(function(k,v) 
    {
        console.log('LOOK stepexpectation ' + k + ": " + $( this ).val() );
        var txt = $( this ).val();
        expectation_ar.push(txt);
    });
    $( "select.stepstatus" ).each(function(k,v) 
    {
        console.log('LOOK stepstatus ' + k + ": " + $( this ).val() );
        var txt = $( this ).val();
        status_ar.push(txt);
    });
    if(bigfathom_util.testcase_ui_toolkit.formtype == 'A')
    {
        //On add we set them all to NONE
        for(var j=0; j<stepid_ar.length; j++)
        {
            status_ar.push('NONE');
        }
    } else {
        if(status_ar.length == 0)
        {
            //This happens if there is no status control
            $( "span.stepstatus" ).each(function(k,v) 
            {
                var txt = $( this ).text();
                if(txt == null || txt == '')
                {
                    txt = 'NONE';   //This can happen when we are adding new rows
                }
                console.log('LOOK stepstatus ' + k + ": " + txt );
                status_ar.push(txt);
            });
            if(status_ar.length == 0)
            {
                throw "Did NOT find status code values!";
            }
        }
    }
    var i=0;
    $( "span.stepid" ).each(function(k,v)
    {
        var stepid = stepid_ar[i];
        var description_tx = description_ar[i];
        var expectation_tx = expectation_ar[i];
        if(description_tx.length > 0 || expectation_tx.length > 0)
        {
            if(description_tx.length == 0 && expectation_tx.length > 0)
            {
                description_tx = 'INSTRUCTION MISSING';
            }
            var status_cd = status_ar[i];
            steps_data.sequence.push({"id":stepid,"d":description_tx,"e":expectation_tx,"cd":status_cd});
        }
        i++;
    });
    
    var new_encoded_step_tx = JSON.stringify(steps_data);
    
    $("[name=" + bigfathom_util.testcase_ui_toolkit.name_steps_encoded_tx + "]").val(new_encoded_step_tx);
    
    console.log("LOOK done copy");
};

bigfathom_util.testcase_ui_toolkit.init = function(testcaseid, personid, formtype, base_url, imgurls_by_purposename, pagekeys_by_purposename)
{
    console.log("Initializing testcase_ui_toolkit " + bigfathom_util.testcase_ui_toolkit.version);

    bigfathom_util.testcase_ui_toolkit.testcaseid = testcaseid;
    bigfathom_util.testcase_ui_toolkit.base_url = base_url;
    bigfathom_util.testcase_ui_toolkit.personid = personid;
    bigfathom_util.testcase_ui_toolkit.formtype = formtype;
    bigfathom_util.testcase_ui_toolkit.imgurls_by_purposename = imgurls_by_purposename;
    bigfathom_util.testcase_ui_toolkit.pagekeys_by_purposename = pagekeys_by_purposename;
    
    for(var name in bigfathom_util.testcase_ui_toolkit.fa_by_purposename)
    {
        var faname = bigfathom_util.testcase_ui_toolkit.fa_by_purposename[name];
        bigfathom_util.testcase_ui_toolkit.imgurls_by_purposename[name] = '<i class="fa ' + faname + '" aria-hidden="true"></i>';
    }
            
    bigfathom_util.testcase_ui_toolkit.initial_steps_encoded_tx = $("[name=" + bigfathom_util.testcase_ui_toolkit.name_steps_encoded_tx + "]").val();
    
    var steps_data = JSON.parse(bigfathom_util.testcase_ui_toolkit.initial_steps_encoded_tx);

    console.log("LOOK decoded is " + JSON.stringify(steps_data));
    for(var i=0; i<steps_data.sequence.length; i++)
    {
        var step_num = i + 1;
        var step_detail = steps_data.sequence[i];
        var stepid = step_detail['id'];
        var description_tx = step_detail['d'];
        var expectation_tx = step_detail['e'];
        var status_cd = step_detail['cd'];
        console.log("LOOK we have stepid#" + stepid + " | " + step_num + " | status=" + status_cd + " | description=" + description_tx + " | expectation=" + expectation_tx );
        
        var idsuffix = bigfathom_util.testcase_ui_toolkit.nextnewidsuffix++;
        var row_markup = bigfathom_util.testcase_ui_toolkit.getTableRowMarkup(idsuffix, stepid, step_num, description_tx, expectation_tx, status_cd);
                    
        $('#' + bigfathom_util.testcase_ui_toolkit.id_steps_table + ' > tbody:last-child').append(row_markup);
        
    }
    bigfathom_util.testcase_ui_toolkit.finalizeTable('INIT');

    if(formtype === 'A' || formtype === 'E')
    {
        $('#' + bigfathom_util.testcase_ui_toolkit.id_steps_table_bottomaction).html('<span class="small-action-button" onclick="bigfathom_util.testcase_ui_toolkit.actionAddRow(null);" title="append a blank row to the table of steps">Append New Blank Row</span>');
    }
    
    //bigfathom_util.testcase_ui_toolkit.getCommunicationSummary();    
    /*
    $(window).resize(function () {
        $('.ui-dialog').css({
                'width': $(window).width(),
                'height': $(window).height(),
                'left': '50px',
                'top':'50px'
        });
        $('#dialog-anchor').dialog({position: 'center'});
    }).resize();
    */
};
    
jQuery(document).ready(function()
{
    (function ($) {
        var base_url = Drupal.settings.myurls.base_url;
        var pagekeys_by_purposename = Drupal.settings.myurls.page_keys;
        var imgurls_by_purposename = Drupal.settings.myurls.imgurls_by_purposename;
        var testcaseid = Drupal.settings.testcaseid;
        var personid = Drupal.settings.personid;
        var formtype = Drupal.settings.formtype;
        console.log("Starting userdashboard " + bigfathom_util.testcase_ui_toolkit.version);
        console.log("... testcaseid=" + JSON.stringify(testcaseid));
        console.log("... base_url=" + JSON.stringify(base_url));
        console.log("... pagekeys_by_purposename=" + JSON.stringify(pagekeys_by_purposename));
        console.log("... imgurls_by_purposename=" + JSON.stringify(imgurls_by_purposename));
        console.log("... personid=" + personid);
        console.log("... formtype=" + formtype);
        
        bigfathom_util.testcase_ui_toolkit.init(testcaseid, personid, formtype, base_url, imgurls_by_purposename, pagekeys_by_purposename);

    }(jQuery));
});
    

