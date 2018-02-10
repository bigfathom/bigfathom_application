/* 
 * Functions for working with brainstorm topic data
 * 
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 */
  
/* global d3 */
/* global bigfathom_util */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if(!bigfathom_util.hasOwnProperty("brainstorm"))
{
    //Create the object property because it does not already exist
    bigfathom_util.brainstorm = {
        "version": "20170805.1",
        "default_workitem_opacity":.95
    };
}
  
/**
 * Create a simulation as simply as possible
 */
bigfathom_util.brainstorm.createEverything = function (canvas_container_id, my_action_map, my_field_map, projectid)
{
    var start_simulation = true;
    var dataname = "brainstorm_mapping";
    var full_grab_data_url = bigfathom_util.data.getGrabDataUrl(dataname, {projectid: projectid});
    d3.json(full_grab_data_url, function (rawdata)
    {
        if(rawdata === null)
        {
            throw "ERROR: No data returned from " + full_grab_data_url + "!";
        }
        
        var scrollto_elem = $("ul.tabs");
        var scrollto_y = null; //scrollto_elem.offset().top;
        var top_elem_height = scrollto_elem.offset().top;
        var pagetweaks = {
                  'scrollto_y': scrollto_y
                , 'reduce_canvas_height': top_elem_height + 100
        };        
        
        var callbacks = {};
        var allow_userzoompan = false;
        var zlayers = 4;
        var mycanvas = bigfathom_util.d3v3_svg.createCanvas(canvas_container_id, null, null, allow_userzoompan, callbacks, zlayers, pagetweaks);

        bigfathom_util.d3v3_svg.addArrows(mycanvas, bigfathom_util.shapes.lib.markers);

        mycanvas["background_layer"] = mycanvas.zlayers[0];
        mycanvas["movable_layer"] = mycanvas.zlayers[1];
        mycanvas["trashcan_layer"] = mycanvas.zlayers[2];
        mycanvas["parkinglot_layer"] = mycanvas.zlayers[3];
        
        var brainstorm = bigfathom_util.env.brainstormlanes.manager("brainstorm");
        var container_attribs = brainstorm.getAllContainerAttribs(mycanvas);

        var graphdata = bigfathom_util.brainstorm_data.initializeGraphContainerData(container_attribs);
        var newgraphdatanodes = bigfathom_util.brainstorm_data.getActorNodes(container_attribs,rawdata);
        
        graphdata.projectid = projectid;
        graphdata.nodes = graphdata.nodes.concat(newgraphdatanodes);

        var manager = bigfathom_util.brainstorm.manager(mycanvas, container_attribs, graphdata, my_action_map, my_field_map);
        if(start_simulation)
        {
            manager.force.start();
        }

        return manager;
    });
};

bigfathom_util.brainstorm.manager = function (mycanvas, container_attribs, graphdata, my_action_map, my_field_map)
{
    var instance = {id:"brainstorm.manager.v" + bigfathom_util.brainstorm.version};
    instance.graphdata = graphdata;
    instance.container_attribs = container_attribs;
    instance.mycanvas = mycanvas;
    instance.nodes_cy = null;
    instance.nodes_cx = null;
    instance.shape_manager = null;
    
    instance.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();
    
    instance.set_node_highlight = function (d)
    {
        instance.node_background_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_background_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });

        instance.node_trashcan_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_trashcan_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });

        instance.node_parkinglot_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_parkinglot_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });

        instance.node_brainstorm_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_brainstorm_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });
        instance.node_goal_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_goal_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });

        instance.node_task_sel.style("stroke", function (o) {
            if(dragging_started_node === o )
            {
                return 'red';
            } else {
                return (d == o) ? "yellow" : "white";
            }
        });
        instance.node_task_sel.style("font-weight", function (o) {
            return (d == o) ? "bold" : "normal";
        });
    };

    instance.exit_node_highlight = function ()
    {
        instance.node_background_sel.style("font-weight", "normal");
        instance.node_background_sel.style("stroke", "white");
        instance.node_parkinglot_sel.style("font-weight", "normal");
        instance.node_parkinglot_sel.style("stroke", "white");
        instance.node_trashcan_sel.style("font-weight", "normal");
        instance.node_trashcan_sel.style("stroke", "white");
        instance.node_goal_sel.style("font-weight", "normal");
        instance.node_goal_sel.style("stroke", "white");
        instance.node_task_sel.style("font-weight", "normal");
        instance.node_task_sel.style("stroke", "white");
    };

    /**
     * Function to call when mouse is over the circle
     */
    var overCircle = function (d)
    {
        dragging_ended_node = d;
        instance.set_node_highlight(d);
    };

    /**
     * Function to call when mouse is leaving the circle
     */
    var outCircle = function (d)
    {
        //console.log("outCircle = " + JSON.stringify(d));
        instance.exit_node_highlight();
        dragging_ended_node = null;
    };

    var clickNode = function (d)
    {
        //console.log("clickedNode = " + JSON.stringify(d));
    };

    instance.updateDisplay = function()
    {
        instance.redraw();
    };

    /**
     * Create one record in the database and capture the ID
     */
    instance.updateExistingBrainstormItemNode = function (onenode, change_comment)
    {
        var projectid = instance.graphdata.projectid;
        var remember_allowrefresh = bigfathom_util.brainstorm.automatic_page_refresh;
        bigfathom_util.brainstorm.automatic_page_refresh = false;
         
        var dataname = 'update_one_brainstorm_item';                            
                                    
        var sendpackage = {"dataname": dataname,
                           "databundle":
                                {   
                                    "projectid":projectid, 
                                    "change_comment":change_comment,
                                    "node":onenode
                                }
                            };
        var nativeid = onenode.nativeid;
        var send_fullurl = bigfathom_util.data.getSendDataUrl(dataname);
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            console.log("LOOK callbackActionFunction for callbackid=" + callbackid + " responseBundle=" + JSON.stringify(responseBundle));

            //TODO something here?????

            if(remember_allowrefresh)
            {
                bigfathom_util.brainstorm.automatic_page_refresh = remember_allowrefresh;
            }
            
        };
        bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, nativeid);
        return nativeid;
    };

    /**
     * Create one record in the database and capture the ID
     */
    instance.saveNewBrainstormItemNode = function (untranslated_nodeinfo, purpose_tx, infoelem)
    {
        if(typeof infoelem === 'undefined')
        {
            infoelem = null;
        }
        
        var getTranslatedNodeinfo = function(untranslated_nodeinfo)
        {

            var item_type_code = untranslated_nodeinfo.type;
            var title_tx = untranslated_nodeinfo.label;
            var purpose_tx = untranslated_nodeinfo.purpose_tx;

            var levelnum;
            var typename;
            if(item_type_code === 'G')
            {
                levelnum = 2;
                typename = "goal";
            } else 
            if(item_type_code === 'T')
            {
                levelnum = 3;
                typename = "task";
            } else {
                levelnum = 1;
                typename = "brainstorm";
            }    
            var nativeid = null;
            var nodeinfo = {
                      'type': typename
                    , 'subtype': ['movable']
                    , 'nativeid': nativeid
                    , 'label': title_tx
                    , 'into_trash': false
                    , 'into_parkinglot': false
                };

            return nodeinfo;
        };
        
        var nodeinfo = getTranslatedNodeinfo(untranslated_nodeinfo);
        var projectid = instance.graphdata.projectid;
        var remember_allowrefresh = bigfathom_util.brainstorm.automatic_page_refresh;
        bigfathom_util.brainstorm.automatic_page_refresh = false;

        var dataname = 'create_one_brainstorm_item';
        var send_fullurl = bigfathom_util.data.getSendDataUrl(dataname);
         
        var sendpackage = {
                "dataname": dataname,
                "databundle":{
                        "projectid": projectid,
                        "purpose_tx": purpose_tx,
                        "node": nodeinfo
                    }
                };
        var nativeid = null;
        var changeid = 'newTopicItem';
        if(infoelem !== null)
        {
            infoelem.empty();
            infoelem.append("<p class='status-message'>Saving...</p>");
        }
        var callbackActionFunction = function(callbackid, responseBundle)
        {
            nativeid = responseBundle.responseDetail.brainstormid;
            instance.insertBrainstormItemIntoUI(nativeid, nodeinfo.type, nodeinfo.label, purpose_tx);
            if(infoelem !== null)
            {
                var currdatetime = new Date().toISOString();
                infoelem.append("<p class='status-message'>* Saved at " + currdatetime + " *</p>");
            }
        };
        
        //uiblocker.show("tr#" + workitemid);
        bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, changeid);
        if(remember_allowrefresh)
        {
            bigfathom_util.brainstorm.automatic_page_refresh = remember_allowrefresh;
        }
    };

    /**
     * Call this when the node already exists in the database but not in the UI
     */
    instance.insertBrainstormItemIntoUI = function(brainstormid, typename_or_typeletter, title_tx, purpose_tx)
    {
        var cdetails = instance.container_attribs.details;
        var half_lane_width = cdetails[1].target.width / 2;

        var start_x = [];
        start_x[1] = cdetails[1].target.x + half_lane_width;
        start_x[2] = cdetails[2].target.x + half_lane_width;
        start_x[3] = cdetails[3].target.x + half_lane_width;
        var start_y = [];    //mycanvas.h / 2;
        start_y[1] = 10;
        start_y[2] = 10;
        start_y[3] = 10; 

        var levelnum;
        var typename;
        var subtype;
        if(typename_or_typeletter === 'goal' || typename_or_typeletter === 'G')
        {
            levelnum = 2;
            typename = "goal";
            subtype = ['candidate','movable'];
        } else 
        if(typename_or_typeletter === 'task' || typename_or_typeletter === 'T')
        {
            levelnum = 3;
            typename = "task";
            subtype = ['candidate','movable'];
        } else {
            levelnum = 1;
            typename = "brainstorm";
            subtype = ['movable'];
        }    
        var nativeid = brainstormid;
        var key = "fromdb_" + nativeid;
        var onenode = {
                  'type': typename
                , 'subtype': subtype
                , 'key': key
                , 'nativeid': nativeid
                , 'label': title_tx
                , 'x': start_x[levelnum]
                , 'y': start_y[levelnum]
                , 'px': start_x[levelnum]   //Else NaN bug
                , 'py': start_y[levelnum]   //Else NaN bug
                , 'is_drag_source': true
                , 'is_drag_target': false
                , 'assignment': levelnum
                , 'targetlevel': null
                , 'into_trash': false
                , 'into_parkinglot': false
                , 'fixed': false
                , 'status_cd': "B"
                , 'status_detail': {}
                , 'custom_detail': null
                , 'fill_opacity': bigfathom_util.brainstorm.default_workitem_opacity
            };

        instance.graphdata.nodes.push(onenode);
        instance.updateDisplay();
    };

    /**
     * Creates a new node by saving it to the database first
     * @DEPRECATED
     */
    instance.createNewBrainstormItem = function(item_type_code, title_tx, purpose_tx, infoelem)
    {
        var cdetails = instance.container_attribs.details;

        var levelnum;
        var typename;
        if(item_type_code === 'G')
        {
            levelnum = 2;
            typename = "goal";
        } else 
        if(item_type_code === 'T')
        {
            levelnum = 3;
            typename = "task";
        } else {
            levelnum = 1;
            typename = "brainstorm";
        }    
        var nativeid = null;
        var nodeinfo = {
                  'type': typename
                , 'subtype': ['movable']
                , 'nativeid': nativeid
                , 'label': title_tx
                , 'into_trash': false
                , 'into_parkinglot': false
            };

        instance.saveNewBrainstormItemNode(nodeinfo, purpose_tx, infoelem);
    };

    var cdetails = instance.container_attribs.details;
    var slot_width = cdetails[1].target.width;
    var ref_cx = instance.mycanvas.w / 4;
    var slot_width = (ref_cx * 2) / 5;

    instance.nodes_cx = {0:5};
    instance.nodes_cy = {0:instance.mycanvas.h / 2};

    instance.nodes_cx[1] = cdetails[1].target.x + (slot_width / 2);
    instance.nodes_cx[2] = cdetails[2].target.x + (slot_width / 2);
    instance.nodes_cx[3] = cdetails[3].target.x + (slot_width / 2);
    instance.nodes_cy[1] = instance.nodes_cy[0];
    instance.nodes_cy[2] = instance.nodes_cy[0];
    instance.nodes_cy[3] = instance.nodes_cy[0];

    var nodes_floor_y = instance.mycanvas.h - 10;

    var dragging_position = null;
    var dragging_ended_node = null;    //Drag target
    var dragging_started_node = null;    //Drag source
    var drag_started = false;

    var mousedown_item = null;

    //Define vars for data
    console.log("DATA = " + JSON.stringify(instance.graphdata.nodes));

    instance.tick = function (e) {

      var k = e.alpha;
      if(dragging_started_node !== null && k < .03)
      {
          //just end it here
          instance.force.alpha(0);
          return;
      }
      instance.graphdata.nodes.forEach(function(o, i) {
            if(!o.fixed)
            {
                var dy;
                var dx;
                if(o.assignment !== null)
                {
                    dx = instance.nodes_cx[o.assignment] - o.x;
                    if(dx > 50)
                    {
                        //Pull it down hard
                        dy = (nodes_floor_y - o.y) / 5;
                    } else {
                        dy = (instance.nodes_cy[o.assignment] - o.y) / 10;
                    }
                } else {
                    dx = instance.nodes_cx[0] - o.x;
                    dy = (instance.nodes_cy[0] - o.y) / 10;
                }

                o.y += dy * k;
                o.x += dx * k;

            }
          });

        instance.node_background_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_trashcan_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_parkinglot_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_brainstorm_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_goal_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_task_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

    };

    instance.force = d3.layout.force()
        .nodes(instance.graphdata.nodes)
        .size([instance.mycanvas.w, instance.mycanvas.h])
        .on("tick", instance.tick)
        .charge(-200)
        .gravity(.005);

    var nodefilter = new bigfathom_util.nodes.filter();
    var my_dataselections = function()
    {
        instance.node_background_sel = instance.mycanvas.background_layer.selectAll("g.rect")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"rect",null), function (d) {
                            return d.key;
                        });
                        
        instance.node_parkinglot_sel = instance.mycanvas.parkinglot_layer.selectAll("g.parkinglot")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"parkinglot",null), function (d) {
                            return d.key;
                        });

        instance.node_trashcan_sel = instance.mycanvas.trashcan_layer.selectAll("g.trashcan")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"trashcan",null), function (d) {
                            return d.key;
                        });
        
        if(typeof instance.node_brainstorm_sel !== 'undefined')
        {
            instance.node_brainstorm_sel.remove();
        }
        instance.node_brainstorm_sel = instance.mycanvas.movable_layer.selectAll("g.brainstorm")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"brainstorm",null), function (d) {
                            return d.key;
                        });

        if(typeof instance.node_goal_sel !== 'undefined')
        {
            instance.node_goal_sel.remove();
        }
        instance.node_goal_sel = instance.mycanvas.movable_layer.selectAll("g.goal")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"goal",null), function (d) {
                            return d.key;
                        });

        if(typeof instance.node_task_sel !== 'undefined')
        {
            instance.node_task_sel.remove();
        }
        instance.node_task_sel = instance.mycanvas.movable_layer.selectAll("g.task")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"task",null), function (d) {
                            return d.key;
                        });
    };


    var my_redraw = function()
    {
        if(instance.auto_datarefresh.isBlockedByNamedRequester("my_redraw"))
        {
            console.log("Already running my_redraw!");
            return;
        }
        instance.auto_datarefresh.markBlocked("my_redraw");
        my_dataselections();
        if(instance.shape_manager === null)
        {
            var my_node_handlers = {};
            var my_link_handlers = {};

            my_node_handlers["drag"] = {
                    "handler_type" : "call",
                    "replacement" : true,
                    "function" : my_drag
                };

            my_node_handlers["mousedown"] = {
                    "handler_type" : "on",
                    "replacement" : true,
                    "function" : function(d) { 
                            d3.event.stopPropagation();
                            mousedown_item = d;
                        }
                };

            my_node_handlers["mouseover"] = {
                    "handler_type" : "on",
                    "replacement" : true,
                    "function" : overCircle 
                };

            my_node_handlers["mouseout"] = {
                    "handler_type" : "on",
                    "replacement" : true,
                    "function" : outCircle 
                };

            var my_overrides = {};
            my_overrides["styles"] = {};
            my_overrides["attribs"] = {};

            //Simply map this data name to a known existing name
            my_overrides["shape_name_alias"] = {};

            //Attributes that only apply to labels    
            my_overrides["label_attribs"] = {};
            my_overrides["label_styles"] = {};

            var my_handlers = {'nodes': my_node_handlers, 'links': my_link_handlers};
            instance.shape_manager = bigfathom_util.shapes.getManager(instance.graphdata, my_handlers, my_overrides);
        }

        instance.node_background_sel.enter().joinForceNodeShapes(instance.shape_manager,"rect");
        instance.node_background_sel.exit().remove();
        
        instance.node_trashcan_sel.enter().joinForceNodeShapes(instance.shape_manager,"trashcan");
        instance.node_trashcan_sel.exit().remove();

        instance.node_parkinglot_sel.enter().joinForceNodeShapes(instance.shape_manager,"parkinglot");
        instance.node_parkinglot_sel.exit().remove();
        
        instance.node_brainstorm_sel.enter().joinForceNodeShapes(instance.shape_manager,"brainstorm");
        instance.node_brainstorm_sel.exit().remove();

        instance.node_goal_sel.enter().joinForceNodeShapes(instance.shape_manager,"goal");
        instance.node_goal_sel.exit().remove();

        instance.node_task_sel.enter().joinForceNodeShapes(instance.shape_manager,"task");
        instance.node_task_sel.exit().remove();

        //IMPORTANT: If you add data AND DO NOT FORCE START EVERYTHING GETS BROKEN!!!!!
        instance.auto_datarefresh.markAllowed("my_redraw");
        instance.force
                .nodes(instance.graphdata.nodes)
                .start();
    };

    var my_drag = d3.behavior.drag()
            .on("dragstart", function (d) {
                if(d.is_drag_source)
                {
                    drag_started = true;
                    dragging_started_node = d;
                    dragging_started_node.fixed = true;
                    d3.event.sourceEvent.stopPropagation(); // it's important that we suppress the mouseover event on the node being dragged. Otherwise it will absorb the mouseover event and the underlying node will not detect it d3.select(this).attr('pointer-events', 'none');
                }
            })
            .on("drag", function (d) {
                if (drag_started) {
                    d.px += d3.event.dx;
                    d.py += d3.event.dy;
                    d.x += d3.event.dx;
                    d.y += d3.event.dy; 
                    instance.force.alpha(.2);    //Warm it up 
                    dragging_position = {x: d.x, y:d.y};
                }
            })
            .on("dragend", function (dragged_from_node) 
            {
                instance.auto_datarefresh.markBlocked("dragend");

                var details = instance.container_attribs.details;
                var targetlevel = null;
                var change_comment = '';

                if(dragging_started_node && dragging_ended_node)
                {
                    //Okay, lets analyze this one.
                    if(dragging_ended_node.is_drag_target)
                    {
                        //Did we land on a special target?
                        var source = null;
                        var target = null;
                        source = dragging_started_node;
                        target = dragging_ended_node;
                        if(source !== null && target !== null)
                        {
                            if(target.type === "parkinglot")
                            {
                                targetlevel = 4;
                                dragging_started_node.into_parkinglot = true;
                                change_comment = 'moved to parkinglot';
                            } else
                            if(target.type === "trashcan")
                            {
                                targetlevel = 0;
                                dragging_started_node.into_trash = true;
                                change_comment = 'moved to trash';
                            }
                        }
                    };
                    if(targetlevel === null)
                    {
                        //Figure this out by position.
                        if(dragging_position.x >= details[1].target.x && dragging_position.x < details[2].target.x)
                        {
                            targetlevel = 1;
                            dragging_started_node.type = "brainstorm";
                            change_comment = 'changed to uncategorized type';
                            removeValueFromArray(dragging_started_node.subtype, "candidate");
                        } else
                        if(dragging_position.x < details[3].target.x)
                        {
                            targetlevel = 2;
                            dragging_started_node.type = "goal";
                            change_comment = 'changed to goal type';
                            addValueToArray(dragging_started_node.subtype, "candidate");
                        } else
                        if(dragging_position.x >= details[3].target.x)
                        {
                            targetlevel = 3;
                            dragging_started_node.type = "task";
                            change_comment = 'changed to task type';
                            addValueToArray(dragging_started_node.subtype, "candidate");
                        }
                    }
                    
                    //Did we decide to make a change?
                    if(targetlevel !== null)
                    {
                        
                        //Yes, take action.
                        dragging_started_node.assignment = targetlevel;
                        instance.updateExistingBrainstormItemNode(dragging_started_node, change_comment);
                        my_redraw();
                    }

                    //Let it go.
                    dragging_started_node.fixed = false;
                }

                //Always do this here.
                dragging_started_node = null;
                drag_started = false;
                instance.auto_datarefresh.markAllowed("dragend");
                instance.force.resume();
            });


    instance.mycanvas.zlayers[0].style("opacity", 1e-6)
      .transition()
        .duration(1000)
        .style("opacity", 1);


    instance.redraw = function ()
    {
        my_redraw();
    };

    //Setup the callbacks now if we have any.
    if (typeof my_action_map === 'undefined') 
    {
        console.log("LOOK we did NOT define any my_action_map!!!!!");
    } else {
        if (typeof my_field_map === 'undefined')
        {
            console.log("LOOK we did NOT define any my_action_map!!!!!");
        } else {
            console.log("LOOK TODO execute the custom callbacks now!!! >>> " + JSON.stringify(my_field_map));
            if(my_action_map.hasOwnProperty("save_all_data_btn_id"))
            {
                $("#" +  my_action_map["save_all_data_btn_id"]).click(function ()
                {
                    console.log("LOOK save_all_data_btn_id!!!");
                });
            }
            bigfathom_util.popup.setup(instance, my_action_map);
        }
    }
    instance.redraw();


    var valueInArray = function(myarray, value)
    {
        for(var i = 0; i < myarray.length; i++) 
        {
            var checkval = myarray[i];
            if(checkval == value)
            {
                return true;
            }
        }
        return false;
    };

    var removeValueFromArray = function(myarray, value)
    {
        for(var i = 0; i < myarray.length; i++) 
        {
            var checkval = myarray[i];
            if(checkval == value)
            {
                myarray.splice(i,1);
                return true;
            }
        }
        return false;
    };

    var addValueToArray = function(myarray, value)
    {
        for(var i = 0; i < myarray.length; i++) 
        {
            var checkval = myarray[i];
            if(checkval == value)
            {
                return true;
            }
        }
        myarray.push(value);
        return false;
    };

    /**
     * Apply changes from database feedback
     */
    instance.applyNodeChanges = function (nodechanges)
    {
        instance.auto_datarefresh.markBlocked("applyNodeChanges");
        var existing_nodes = instance.graphdata.nodes;
        
        //First change existing nodes
        var tmp_existing_lookup = {};
        var tmp_offsets_to_remove = [];
        for(var i=0; i<existing_nodes.length; i++)
        {
            var onenode = existing_nodes[i];
            if(typeof onenode.subtype === 'undefined')
            {
                throw "ERROR Missing subtype for " + JSON.stringify(onenode);
                //console.log("TODO FIX THIS SO SUBTYPE IS ALWAYS EXISTING!!!! Missing for " + JSON.stringify(onenode));
                //onenode.subtype = [];    
            }
            if(valueInArray(onenode.subtype, "movable"))
            {
                var existing_nativeid = onenode.nativeid;
                tmp_existing_lookup[existing_nativeid] = true;
                if(!nodechanges.hasOwnProperty(existing_nativeid) && !nodechanges.hasOwnProperty('"' + existing_nativeid + '"'))
                {
                    //Delete this one from this page after completing the loop
                    tmp_offsets_to_remove.push(i);
                } else {
                    //Apply any changes now
                    var freshnode;
                    if(nodechanges.hasOwnProperty(existing_nativeid))
                    {
                        freshnode = nodechanges[existing_nativeid];
                    } else {
                        freshnode = nodechanges['"' + existing_nativeid + '"'];
                    }
                    if(freshnode.type !== onenode.type)
                    {
                        if(freshnode.type === "goal")
                        {
                            addValueToArray(onenode.subtype, "candidate");
                            onenode.type = freshnode.type;
                            onenode.assignment = 2;
                        } else 
                        if(freshnode.type === "task")
                        {
                            addValueToArray(onenode.subtype, "candidate");
                            onenode.type = freshnode.type;
                            onenode.assignment = 3;
                        } else {
                            removeValueFromArray(onenode.subtype, "candidate");
                            onenode.type = "brainstorm";
                            onenode.assignment = 1;
                        }
                    } else {
                        if(freshnode.parkinglot_level > 0)
                        {
                            onenode.assignment = 0;
                            onenode.into_trash = true;
                        } else
                        if(freshnode.parkinglot_level > 0)
                        {
                            onenode.assignment = 4;
                            onenode.into_parkinglot = true;
                        }
                    }
                    onenode.label = freshnode.item_nm;
                }
            }
        }

        //Remove all the deleted nodes
        for(var i=tmp_offsets_to_remove.length-1; i>-1; i--)
        {
            var offset = tmp_offsets_to_remove[i];
            instance.graphdata.nodes.splice(offset, 1);
        }
                    
        //Now insert the new nodes
        for(var nativeid in nodechanges)
        {
            if(!tmp_existing_lookup.hasOwnProperty(nativeid))
            {
                var freshnode = nodechanges[nativeid];
                instance.insertBrainstormItemIntoUI(nativeid, freshnode.candidate_type, freshnode.item_nm, freshnode.purpose_tx);
            }
        }
        
        instance.auto_datarefresh.markAllowed("applyNodeChanges");
        instance.redraw();
    };

    var my_data_refresher = null;
    var previous_project_edit_key = 1; //Initialize to super low number
    var previous_project_edit_timestamp = 1; //Initialize to super low number
    function getLatestDataChanges()
    {
        if(instance.auto_datarefresh.isBlocked())
        {
            //No refresh on this iteration, check again later.
            my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
        } else {
            //Perform a refresh now.
            instance.auto_datarefresh.markBlocked("getLatestDataChanges");
            var grab_fullurl = bigfathom_util.data.getGrabDataUrl("brainstorm_updates"
                            ,{
                                 "projectid":instance.graphdata.projectid
                                ,"previous_project_edit_key": previous_project_edit_key
                                ,"previous_project_edit_timestamp": previous_project_edit_timestamp
                            }
                        );

            var callbackActionFunction = function(callbackid, responseBundle)
            {
                console.log("LOOK started callbackActionFunction!");
                if(responseBundle !== null)
                {
                    var record = responseBundle.data.data;
                    previous_project_edit_key = record.most_recent_edit_key; //In sync with the server instead of local machine time.
                    previous_project_edit_timestamp = record.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                    if(record.has_newdata)
                    {
                        instance.applyNodeChanges(record.newdata.nodes);
                    }
                }

                //Setup for another check to happen in a little while
                instance.auto_datarefresh.markAllowed("getLatestDataChanges");
                my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
            };

            bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, previous_project_edit_key);
        }
        console.log("LOOK exit getLatestDataChanges!");
    };
    
    my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
    
    return instance;
};

