/* 
 * Functions for working with data data
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
if(!bigfathom_util.hasOwnProperty("popup"))
{
    //Create the object property because it does not already exist
    bigfathom_util.popup = {
        "version": "20170805.1",
        "fieldmetadata":{ 
            "brainstorm":{"brainstom_item_name":{"maxlen":40},"purpose_tx":{"maxlen":1024}},
            "workitem":{"workitem_nm":{"maxlen":40},"purpose_tx":{"maxlen":1024}}
        }
    };
}

bigfathom_util.popup.setup = function(instance, my_action_map)
{
    var savehandlers = {};
    var closehandlers = {};
    if(my_action_map.hasOwnProperty("popups"))
    {
        var popups = my_action_map["popups"];
        var callsavefunction = function()
            {
                var field_ids_map = savehandlers[this.id].field_ids_map;
                var dlg_container_id = savehandlers[this.id].dlg_container_id;
                var callthis = savehandlers[this.id].callthis;
                var errors = callthis(field_ids_map);
                if(errors === null || errors.length === 0)
                {
                    $("#" + dlg_container_id).css("display","none");
                }
                //Show the errors
                var dlg_form_statusinfo = savehandlers[this.id].dlg_form_statusinfo;
                var infoelem = $("#"+ dlg_form_statusinfo);
                infoelem.empty();
                var errmarkup;
                errmarkup = "<fieldset class='errors'><legend title='Please correct these error(s)'>Error(s) Detected</legend>";
                errmarkup += "<ul>";
                for(var i=0; i<errors.length; i++)
                {
                    var errmsg = errors[i];
                    errmarkup += "<li>" + errmsg;
                }                    
                errmarkup += "</ul></fieldset>";
                infoelem.append(errmarkup);

            };
        var callsaveandaddmorefunction = function()
            {
                var field_ids_map = savehandlers[this.id].field_ids_map;
                var dlg_form_statusinfo;
                dlg_form_statusinfo = savehandlers[this.id].dlg_form_statusinfo;
                var callthis = savehandlers[this.id].callthis;
                var errors = callthis(field_ids_map);
                var infoelem = $("#"+ dlg_form_statusinfo);
                infoelem.empty();
                if(errors === null || errors.length === 0)
                {
                    var currdatetime = new Date().toISOString();
                    infoelem.append("<p class='status-message'>* Saved at " + currdatetime + " *</p>");
                } else {
                    //Show the errors
                    var dlg_form_statusinfo = savehandlers[this.id].dlg_form_statusinfo;
                    var errmarkup;
                    errmarkup = "<fieldset class='errors'><legend title='Please correct these error(s)'>Error(s) Detected</legend>";
                    errmarkup += "<ul>";
                    for(var i=0; i<errors.length; i++)
                    {
                        var errmsg = errors[i];
                        errmarkup += "<li>" + errmsg;
                    }                    
                    errmarkup += "</ul></fieldset>";
                    infoelem.append(errmarkup);
                }
            };
        var callclosefunction = function()
            {
                var dlg_container_id = closehandlers[this.id].dlg_container_id;
                $("#" + dlg_container_id).css("display","none");
            };
        for(var popuptype in popups)
        {
            var popupinfo = popups[popuptype];
            var context_name = popupinfo["context"];
            var dlg_container_id = popupinfo["dlg_container_id"];
            var dlg_form_id = popupinfo["dlg_form_id"];
            var field_ids_map = popupinfo["field_ids_map"];
            
            var subcontext_name;
            var show_dlg_btn_id;
            var save_dlg_btn_id;
            var saveandaddmore_dlg_btn_id;
            var close_dlg_btn_id;
            var dlg_form_statusinfo;
            var dlg_form_topinfo;

            if(popupinfo.hasOwnProperty("subcontext"))
            {
                subcontext_name = popupinfo["subcontext"];
            } else {
                subcontext_name = null;
            }
            
            if(popupinfo.hasOwnProperty("dlg_form_topinfo"))
            {
                dlg_form_topinfo = popupinfo["dlg_form_topinfo"];
            } else {
                dlg_form_topinfo = null;
            }
            if(popupinfo.hasOwnProperty("dlg_form_statusinfo"))
            {
                dlg_form_statusinfo = popupinfo["dlg_form_statusinfo"];
            } else {
                dlg_form_statusinfo = null;
            }
            
            if(popupinfo.hasOwnProperty("save_dlg_btn_id"))
            {
                save_dlg_btn_id = popupinfo["save_dlg_btn_id"];
            } else {
                save_dlg_btn_id = null;
            }
            if(popupinfo.hasOwnProperty("saveandaddmore_dlg_btn_id"))
            {
                saveandaddmore_dlg_btn_id = popupinfo["saveandaddmore_dlg_btn_id"];
            } else {
                saveandaddmore_dlg_btn_id = null;
            }
            if(popupinfo.hasOwnProperty("show_dlg_btn_id"))
            {
                show_dlg_btn_id = popupinfo["show_dlg_btn_id"];
                $("#" +  show_dlg_btn_id).click(function ()
                {
                    //Clear the fields now
                    if(context_name === 'brainstorm')
                    {
                        var dlg_form_statusinfo = 'dlg_add_brainstorm_statusinfo';
                        var infoelem = $("#"+ dlg_form_statusinfo);
                        infoelem.empty();
                        $('#' + field_ids_map["brainstom_item_name"]).val(""); //clears input feild
                        $('#' + field_ids_map["brainstom_item_purpose"]).val("");
                        $('#' + field_ids_map["brainstom_item_type"]).val("U"); //Set it to uncategorized
                    }
                    $("#" +  dlg_container_id).css("display","block");
                });
            }
            if(popupinfo.hasOwnProperty("close_dlg_btn_id"))
            {
                close_dlg_btn_id = popupinfo["close_dlg_btn_id"];
                closehandlers[close_dlg_btn_id] = {"dlg_container_id": dlg_container_id};
                $("#" +  dlg_form_id + " #" + close_dlg_btn_id).click(callclosefunction);
            }
            
            //Create a standard popup handler in specific contexts
            if(context_name === 'brainstorm')
            {
                var validateform = function(field_ids_map)
                    {
                        var errors = [];
                        var looksgood = true;
                        var cleanvalues = {};
                        var item_name = $('#' + field_ids_map["brainstom_item_name"]).val().trim();
                        if (item_name === "") 
                        {
                            looksgood = false;
                            errors.push("need to provide a topic name");
                        }
                        if(item_name.length > bigfathom_util.popup.fieldmetadata.brainstorm.brainstom_item_name.maxlen)
                        {
                            looksgood = false;
                            var truncate = item_name.substr(0,bigfathom_util.popup.fieldmetadata.brainstorm.brainstom_item_name.maxlen);
                            errors.push("topic name is too big, truncate at \"" + truncate + "\"");
                        }
                        
                        var item_purpose = $('#' + field_ids_map["brainstom_item_purpose"]).val().trim();
                        var item_type = $('#' + field_ids_map["brainstom_item_type"]).val().trim();
                        
                        if(item_type === null)
                        {
                            item_type = 'U';
                        }
                            
                        cleanvalues["looksgood"] = looksgood;
                        cleanvalues["errors"] = errors;
                        cleanvalues["item_name"] = item_name;
                        cleanvalues["item_purpose"] = item_purpose;
                        cleanvalues["item_type"] = item_type;
                        return cleanvalues;                        
                    };
                if(save_dlg_btn_id !== null)
                {
                    savefunction = function(field_ids_map)
                        {
                            var cleanvalues = validateform(field_ids_map);
                            var errors = cleanvalues["errors"];
                            if(cleanvalues !== null && cleanvalues.looksgood)
                            {
                                //nodeinfo.type, nodeinfo.label, purpose_tx
                                var nodeinfo = {
                                  'type': cleanvalues.item_type,
                                  'label': cleanvalues.item_name,
                                  'purpose_tx': cleanvalues.item_purpose
                                };
                                var add_comment = "Created new candidate topic";
                                instance.saveNewBrainstormItemNode(nodeinfo, add_comment);
                            }
                            return errors;
                        };
                }
                
            } else
            if(context_name === 'workitem') {
                var savefunction = null;
                if(save_dlg_btn_id !== null || saveandaddmore_dlg_btn_id !== null)
                {
                    var validateform = function(field_ids_map)
                        {
                            var errors = [];
                            console.log("LOOK field_ids_map=" + JSON.stringify(field_ids_map));
                            //alert("LOOK field_ids_map");
                            var looksgood = true;
                            var cleanvalues = {};
                            var item_name = $('#' + field_ids_map["workitem_nm"]).val().trim();
                            if (item_name === "") 
                            {
                                looksgood = false;
                                errors.push("need to provide a workitem name");
                            }
                            if(item_name.length > bigfathom_util.popup.fieldmetadata.workitem.workitem_nm.maxlen)
                            {
                                looksgood = false;
                                var truncate = item_name.substr(0,bigfathom_util.popup.fieldmetadata.workitem.workitem_nm.maxlen);
                                errors.push("workitem name is too big, truncate at \"" + truncate + "\"");
                            }
                            
                            var item_purpose = $('#' + field_ids_map["purpose_tx"]).val().trim();
                            var item_type = $('#' + field_ids_map["workitem_basetype"]).val().trim();
                            if(item_type !== 'G' && item_type !== 'T')
                            {
                                   looksgood = false;
                                   errors.push("need to provide a workitem type");
                            }
                            var status_elem = $('#' + field_ids_map["workitem_status_cd"]);
                            if(status_elem.length)
                            {
                                var item_status_cd = $('#' + field_ids_map["workitem_status_cd"]).val().trim();
                                cleanvalues["item_status_cd"] = item_status_cd;
                            }
                            cleanvalues["branch_effort_hours_est"] = null;
                            var branch_effort_hours_est_elem = $('#' + field_ids_map["branch_effort_hours_est"]);
                            if(branch_effort_hours_est_elem.length)
                            {
                                var item_branch_effort_hours_est = branch_effort_hours_est_elem.val().trim();
                                if(item_branch_effort_hours_est.length > 0)
                                {
                                    if(isNaN(parseFloat(item_branch_effort_hours_est)))
                                    {
                                        looksgood = false;
                                        errors.push("value '" + item_branch_effort_hours_est + "' is NOT numeric!");
                                    } else {
                                        cleanvalues["branch_effort_hours_est"] = item_branch_effort_hours_est;
                                    }
                                }
                            }
                            cleanvalues["remaining_effort_hours"] = null;
                            var remaining_effort_hours_elem = $('#' + field_ids_map["remaining_effort_hours"]);
                            if(remaining_effort_hours_elem.length)
                            {
                                var item_remaining_effort_hours = remaining_effort_hours_elem.val().trim();
                                if(item_remaining_effort_hours.length > 0)
                                {
                                    if(isNaN(parseFloat(item_remaining_effort_hours)))
                                    {
                                        looksgood = false;
                                        errors.push("value '" + item_remaining_effort_hours + "' is NOT numeric!");
                                    } else {
                                        cleanvalues["remaining_effort_hours"] = item_remaining_effort_hours;
                                    }
                                }
                            }
                            
                            //TODO replace hardcoding with dynamic associations!
                            var terminal_status_codes = {'STC':1,'SC':1,'CWC':1,'RBO':1,'AB':1};
                            if(cleanvalues["remaining_effort_hours"] && cleanvalues["remaining_effort_hours"] > 0)
                            {
                                if(terminal_status_codes.hasOwnProperty(cleanvalues["item_status_cd"]))
                                {
                                    looksgood = false;
                                    errors.push("terminal status cannot have any remaining hours of work!");
                                }
                            }
                            
                            console.log("LOOK cleanvalues=" + JSON.stringify(cleanvalues));
                            //alert("LOOK cleanvalues");
                           
                            cleanvalues["looksgood"] = looksgood;
                            cleanvalues["errors"] = errors;
                            cleanvalues["item_name"] = item_name;
                            cleanvalues["item_purpose"] = item_purpose;
                            cleanvalues["item_type"] = item_type;
                            return cleanvalues;
                        };
                    if(subcontext_name !== 'add' && subcontext_name !== 'new')
                    {
                        savefunction = function(field_ids_map)
                            {
                                //Set the fields using names that match the DB record fieldnames
                                var nativeid = $('#' + field_ids_map["nativeid"]).val().trim();
                                var cleanvalues = validateform(field_ids_map);
                                var errors = cleanvalues["errors"];
                                if(cleanvalues !== null && cleanvalues.looksgood)
                                {
                                    var nodeinfo = {
                                      'workitemid':nativeid,
                                      'workitem_nm': cleanvalues.item_name,
                                      'purpose_tx': cleanvalues.item_purpose,
                                      'workitem_basetype': cleanvalues.item_type,
                                      'status_cd': cleanvalues.item_status_cd,
                                      'branch_effort_hours_est': cleanvalues.branch_effort_hours_est,
                                      'remaining_effort_hours': cleanvalues.remaining_effort_hours
                                    };
                                    var changeid = nativeid;
                                    var change_comment = "Edited workitem#" + nativeid;
                                    instance.saveOneNodeEdit(nodeinfo, changeid, change_comment);
                                }
                                return errors;
                            };

                    } else {
                        savefunction = function(field_ids_map)
                            {
                                var cleanvalues = validateform(field_ids_map);
                                var errors = cleanvalues["errors"];
                                if(cleanvalues !== null && cleanvalues.looksgood)
                                {
                                    var nodeinfo = {
                                      'workitem_nm': cleanvalues.item_name,
                                      'purpose_tx': cleanvalues.item_purpose,
                                      'workitem_basetype': cleanvalues.item_type,
                                      'branch_effort_hours_est': cleanvalues.branch_effort_hours_est,
                                      'remaining_effort_hours': cleanvalues.remaining_effort_hours
                                    };
                                    var add_comment = "Created new candidate workitem";
                                    instance.createNewCandidateWorktitem(nodeinfo, add_comment);
                                }
                                return errors;
                            };
                    }
                }
            }
            if(savefunction !== null)
            {
                savehandlers[save_dlg_btn_id] = {
                                  "dlg_container_id": dlg_container_id
                                , "dlg_form_statusinfo": dlg_form_statusinfo  
                                , "field_ids_map":field_ids_map
                                , "callthis": savefunction
                            };
                $("#" +  save_dlg_btn_id).click(callsavefunction);
                if(saveandaddmore_dlg_btn_id !== null)
                {
                    savehandlers[saveandaddmore_dlg_btn_id] = {
                                      "dlg_container_id": dlg_container_id
                                    , "dlg_form_statusinfo": dlg_form_statusinfo  
                                    , "field_ids_map":field_ids_map
                                    , "callthis": savefunction
                                };
                    $("#" +  saveandaddmore_dlg_btn_id).click(callsaveandaddmorefunction);
                }
            }
        }
    }
    console.log("popupLOOK setup_controls DONE dlg_container_id=" + JSON.stringify(dlg_container_id));
    console.log("popupLOOK setup_controls DONE field_ids_map=" + JSON.stringify(field_ids_map));
    console.log("popupLOOK setup_controls DONE dlg_form_statusinfo=" + JSON.stringify(dlg_form_statusinfo));
    console.log("popupLOOK setup_controls DONE savehandlers=" + JSON.stringify(savehandlers));
};
