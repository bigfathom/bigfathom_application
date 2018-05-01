/* 
 * Library of utility functions for working with dependency mapping
 * 
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 */

/* global d3 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}

if(!bigfathom_util.hasOwnProperty("hierarchy"))
{
    //Create the object property because it does not already exist
    bigfathom_util.hierarchy = {
        "version": "20180430.2",
        "default_workitem_opacity":.9,
        "context_type":null,
        "readonly":false
    };
}

/**
 * Create a simulation as simply as possible
 */
bigfathom_util.hierarchy.createEverything = function (canvas_container_id, context_type, my_userinfo_map, my_action_map, my_field_map, projectid, initial_commands)
{
    bigfathom_util.hierarchy.context_type = context_type;
    bigfathom_util.shapes.show_status = !(context_type == 'template');
    var start_simulation = true;
    var dataname;
    if(context_type !== 'template')
    {
        dataname = "hierarchy_mapping";
        bigfathom_util.hierarchy.readonly = false;
    } else {
        dataname = "template_hierarchy_mapping";
        bigfathom_util.hierarchy.readonly = true;
    }
    var full_grab_data_url = bigfathom_util.data.getGrabDataUrl(dataname, {"projectid": projectid});
    d3.json(full_grab_data_url, function (rawdata)
    {
        if(rawdata === null)
        {
            throw "ERROR: No data returned from " + full_grab_data_url + "!";
        }
//console.log("LOOK rawdata=" + JSON.stringify(rawdata));
//alert("LOOK at the rawdata now for dataname=" + dataname);
        //Figure out if we need to zoom out at the start of display
        var initial_canvas_scale_factor = 1;
        var linkcount = rawdata.data.wi2wi.length;
        if(linkcount > 50)
        {
            //Apply some default scaling (smaller number is smaller size nodes)
            initial_canvas_scale_factor = 50/linkcount; 
        }
        
        var scrollto_y;
        var top_elem_height;
        var scrollto_elem = $("ul.tabs");
        if(scrollto_elem.length)
        {
            scrollto_y = null; //scrollto_elem.offset().top;
            top_elem_height = scrollto_elem.offset().top;
        } else {
            scrollto_y = null;
            top_elem_height = 0;
        }
        var pagetweaks = {
                  'scrollto_y': scrollto_y
                , 'initial_canvas_scale_factor': initial_canvas_scale_factor
                , 'reduce_canvas_height': top_elem_height + 120
        };        
        
        var callbacks = {};
        var allow_userzoompan = true;
        var zlayers = 10;
        var mycanvas = bigfathom_util.d3v3_svg.createCanvas(canvas_container_id, 
                                                null, 
                                                null, 
                                                allow_userzoompan, 
                                                callbacks, 
                                                zlayers, 
                                                pagetweaks);
        
        bigfathom_util.d3v3_svg.addArrows(mycanvas, bigfathom_util.shapes.lib.markers);

        mycanvas["hierarchy_area_layer"] = mycanvas.zlayers[0];
        mycanvas["hierarchy_lines_layer"] = mycanvas.zlayers[1];
        
        mycanvas["hierarchy_goals_layer"] = mycanvas.zlayers[2]; //No ant and no warning
        mycanvas["hierarchy_warn_disconnected_layer"] = mycanvas.zlayers[3];    //No ant and yes warning
        
        mycanvas["hierarchy_hide_ant_subsequent_layer"] = mycanvas.zlayers[4];  //No warning
        mycanvas["hierarchy_hide_ant_subsequent_warn_disconnected_layer"] = mycanvas.zlayers[5]; //Yes warning
        mycanvas["hierarchy_show_ant_subsequent_layer"] = mycanvas.zlayers[6];
        mycanvas["hierarchy_show_ant_subsequent_warn_disconnected_layer"] = mycanvas.zlayers[7];
        
        mycanvas["candidate_area_layer"] = mycanvas.zlayers[8];
        mycanvas["candidate_goals_layer"] = mycanvas.zlayers[9];
        if(!rawdata.hasOwnProperty("data"))
        {
            throw "Missing the data property from rawdata!!!";
        }
        
        var workitem_topology = bigfathom_util.hierarchy_data.getWorkitemTopologyInfo(rawdata.data);
        var lane_defs = bigfathom_util.hierarchy_data.getEnvironmentNodes(workitem_topology);   //rawdata);            
        var actionlayout = bigfathom_util.env.multilevelhierarchy.manager(mycanvas, lane_defs, context_type);
        var container_attribs = actionlayout.methods.getAllContainerAttribs();

        var graphdata = bigfathom_util.hierarchy_data.initializeGraphContainerData(container_attribs);
        var actorbundle = bigfathom_util.hierarchy_data.getActorNodeBundle(container_attribs, workitem_topology, rawdata);
        graphdata.refreshed_timestamp = rawdata.metadata.server_timestamp;
        graphdata.my_userinfo_map = my_userinfo_map;
        graphdata.projectid = projectid;
        graphdata.rootnodeid = actorbundle.rootnodeid;
        graphdata.linksmaster = actionlayout.methods.getLinksMaster(rawdata);   //This is our CORE link reference structure!
        graphdata.incompleted_branch_rootids = workitem_topology.incompleted_branch_rootids;
        graphdata.completed_branch_rootids = workitem_topology.completed_branch_rootids;
        graphdata.status_cd_lookup = rawdata.data.status_cd_lookup;
        graphdata.person_lookup = rawdata.data.person_lookup;
        //The movable nodes MUST be the first elements in the nodes array!!!!
        graphdata.nodes = actorbundle.nodes;
        graphdata.last_node_offset = actorbundle.nodes.length - 1;
        graphdata.nodes.push(graphdata.background[0]);
        graphdata.nodes = graphdata.nodes.concat(graphdata.targets);
        if(!graphdata.background.hasOwnProperty(1))
        {
            throw "Expected the unassigned area for background!";
        }
        graphdata.nodes.push(graphdata.background[1]);

        //Now that all nodes are in place, create the fast lookup maps
        //=====actionlayout.methods.refreshFastLookupMaps(graphdata);
        //TODO --- REFACTOR/BLEND THE LINKS LOGIC!!!!!
        graphdata.fastlookup_maps = {};
        var fast_nodelookup_maps = actionlayout.methods.getFastNodeLookupMaps(graphdata.nodes);
        graphdata.fastlookup_maps.nodes = fast_nodelookup_maps;
        var linksbundle = actionlayout.methods.createLinkNodeMaps(actorbundle, fast_nodelookup_maps, rawdata);
        graphdata.linksbundle = linksbundle;
        var fast_linklookup_maps = actionlayout.methods.getFastLinkLookupMaps(graphdata);
        graphdata.fastlookup_maps.links = fast_linklookup_maps;
        //TODO --- REFACTOR/BLEND THE LINKS LOGIC!!!!!

        //---- actionlayout.methods.assignNodesToCorrectLanes(graphdata);  //???? REDUNDANT with getActorNodeBundle??????????
        actionlayout.methods.moveToIdealNodePositions(graphdata);   //Improves starting positions
        //actionlayout.methods.sortNodes(graphdata);
        var manager = bigfathom_util.hierarchy.manager(mycanvas, actionlayout, graphdata, my_action_map, my_field_map, initial_commands);
        if(start_simulation)
        {
            manager.force.start();
        }
        
        var canvas_warning_msg = actionlayout.methods.getCanvasWarningMessage();
        if(canvas_warning_msg !== null)
        {
            alert(canvas_warning_msg);
        }
        
        return manager;
    });
};

bigfathom_util.hierarchy.manager = function (mycanvas, actionlayout, graphdata, my_action_map, my_field_map, initial_commands)
{

    var force_properties = {
        'linkStrength': 0,
        'charge': -200,
        'gravity': .005
    };
    var instance = {};
    instance.force_properties = force_properties;
    instance.graphdata = graphdata;
    instance.actionlayout = actionlayout;
    instance.container_attribs = actionlayout.container_attribs;
    instance.mycanvas = mycanvas;
    instance.shape_manager = null;
    instance.tick_debug_count = 0;
    instance.infoballoon_flags = {'key':null
                                , 'coordinates':null
                                , 'showing':false};
                            
    instance.myinfoballoon = instance.mycanvas.popup_balloons["main"];

    instance.auto_datarefresh = bigfathom_util.data.getAutoRefreshTracker();

    instance.graphdata.hide_completed_branches = true;  //TODO SYNC WITH CHECKBOX!!!!!

    instance.mycanvas.callbacks['after_rescale'] = function()
    {
        instance.resize();
        if(instance.force.alpha < .2)
        {
            instance.force.alpha += instance.force.alpha/2;
        }
    };
    
    //Let the manager position the nodes into good spots right now.
    instance.actionlayout.methods.refreshFastLookupMaps(instance.graphdata);
    instance.actionlayout.methods.resetAllNodeHierarchy(instance.graphdata);
    
    instance.display_candidate_panel = function()
    {
        //TODO
        //methods.minimizeUnassignedNodeTray
        //bigfathom_util.env.multilevelhierarchy.show_unassigned_lane=true
    };

    instance.hide_candidate_panel = function()
    {
        //TODO
        //methods.minimizeUnassignedNodeTray
        //bigfathom_util.env.multilevelhierarchy.show_unassigned_lane=false
    };

    instance.hide_info_balloon = function ()
    {
        instance.myinfoballoon.element.style("opacity", 0);
        instance.infoballoon_flags.showing = false;
        instance.infoballoon_flags.key = null;
    };

    instance.show_info_balloon = function (coordinates, data_rows, key)
    {
        if(typeof key === 'undefined')
        {
            key = 'general';
        }
        if(!instance.infoballoon_flags.showing || key !== instance.infoballoon_flags.key)
        {
            instance.infoballoon_flags.key = key;
            instance.infoballoon_flags.showing = true;
            instance.infoballoon_flags.coordinates = coordinates;
            instance.mycanvas.showInfoBalloon("main", data_rows, coordinates);
        }
    };

    instance.deactivate_drag_line = function ()
    {
        drag_line_activated = false;
        mycanvas.drag_line.attr("class", "drag_line_hidden");
    };

    instance.move_drag_line = function (d)
    {
        drag_line_activated = true;
        bigfathom_util.d3v3_svg.move_drag_line(mycanvas, d);
    };
    
    var toggle_branch = function(d)
    {
        var needs_redraw = false;
        if(d.assignment !== bigfathom_util.env.multilevelhierarchy.unassigned_lane)
        {
            if(bigfathom_util.nodes.hasHiddenAntecedents(d))
            {
                var onenodeoffset = bigfathom_util.hierarchy_data.getOneNodeOffsetByNativeID(instance.graphdata.nodes, d.nativeid);
                instance.actionlayout.methods.changeNodeConnectionsShown(onenodeoffset, instance.graphdata, true);
                needs_redraw = true;
            } else {
                if(bigfathom_util.nodes.hasAntecedents(d))
                {
                    var onenodeoffset = bigfathom_util.hierarchy_data.getOneNodeOffsetByNativeID(instance.graphdata.nodes, d.nativeid);
                    instance.actionlayout.methods.changeNodeConnectionsShown(onenodeoffset, instance.graphdata, false);
                    needs_redraw = true;
                }
            }                
        }
        if(needs_redraw)
        {
            instance.redraw();
        }
    };
    
    var node_click = function (d)
    {
console.log("LOOK we clicked d=" + JSON.stringify(d)); 
        //instance.set_node_highlight(d);
        selected_node = d;
        if(mycanvas.layers_elem === null)
        {
            console.log("DEBUG why is this null in node_click?");
            return;
        }
        var mouse = d3.mouse(mycanvas.layers_elem);
        var mx = mouse[0];
        var my = mouse[1];
        var fromcenter_x = mx - d.x;
        var fromcenter_y = my - d.y;

        var connector_offset_x;
        var connector_offset_y;
        var connector_r;
        
        if(d.type === 'proj' || d.type === 'goal')
        {
            connector_offset_x = bigfathom_util.shapes.lib.keyprops.goal.connector.offset.x;
            connector_offset_y = bigfathom_util.shapes.lib.keyprops.goal.connector.offset.y;
            connector_r = bigfathom_util.shapes.lib.keyprops.goal.connector.r * 1.4;
        } else if(d.type === 'task' || d.type === 'equjb' || d.type === 'xrcjb' ) {
            connector_offset_x = bigfathom_util.shapes.lib.keyprops.task.connector.offset.x;
            connector_offset_y = bigfathom_util.shapes.lib.keyprops.task.connector.offset.y;
            connector_r = bigfathom_util.shapes.lib.keyprops.task.connector.r * 1.4;
        } else {
            throw "Did NOT recognize type for clicked element d=" + JSON.stringify(d);
        }
 
        var xdif = Math.abs(fromcenter_x + connector_offset_x); //REVERSEDX
        var ydif = Math.abs(fromcenter_y - connector_offset_y);
        if(xdif < connector_r && ydif < connector_r)
        {
            toggle_branch(d);
        } else {
            //A single click will simply display the info balloon.
            instance.show_info_balloon(instance.mycanvas.getPageMouseCoordinates(), d.tooltip);
        }
    };

    var highlight_all_connected_nodes = function(currentnode)
    {
        //console.log("TODO START mark ALL nodes connected to " + currentnode.key);
        instance.clear_all_special_links();
        instance.clear_all_special_nodes();
        var nodes = instance.graphdata.nodes;
        var link_by_offset = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
        {
            var onelink = link_by_offset[linkidx];
            var targetnode = nodes[onelink.trgno];
            var sourcenode = nodes[onelink.srcno];
            if(targetnode.key === currentnode.key)
            {
                special_nodes[sourcenode.key] = 1;
                special_links[onelink.key] = 1;
            } else
            if(sourcenode.key === currentnode.key)
            {
                special_nodes[targetnode.key] = 1;
                special_links[onelink.key] = 1;
            }
        }
        special_nodes[currentnode.key] = 1;
        //console.log("TODO DONE mark ALL nodes connected to " + currentnode.key);

    };

    var highlight_all_editable_nodes = function()
    {
        console.log("TODO START highlight_all_editable_nodes");
        instance.clear_all_editable_nodes();
        var rootnodeoffset =  instance.graphdata.fastlookup_maps.nodes.id2offset[instance.graphdata.rootnodeid];
        var rootprojectnode = instance.graphdata.nodes[rootnodeoffset];                    
        
        var editcount = 0;
        for(var i=0; i<graphdata.nodes.length; i++)
        {
            var nodedetail = graphdata.nodes[i];
            //Only workitems have nativeid, check for this.
            if(typeof nodedetail.nativeid !== "undefined")
            {
                var user_can_edit_target = userCanEditNode(rootprojectnode, instance.graphdata.my_userinfo_map, nodedetail);
                if(user_can_edit_target)
                {
                    editcount++;
                    console.log("LOOK user can edit " + nodedetail.key);
                    editable_nodes[nodedetail.key] = 1;
                }
            }
        }
        console.log("TODO DONE highlight_all_editable_nodes editcount=" + editcount);
        return editcount;
    };

    var just_drag_nodes_activated_ts = 0;
    var dragging_ended_node = null;    //Drag target
    var dragging_started_node = null;    //Drag source
    var drag_started = false;
    var drag_line_activated = false;
    var mousedown_item = null;
    var enable_zoompan = true;

    var highlight_node = null;
    var selected_node = null;
    var selected_link = null;
    var highlight_link = null;
    var highlight_link_start_mouse_coord = null;
    var special_links = {};
    var special_nodes = {};
    var editable_nodes = {};

    var link_clear_distance = 40;    //Distance at which point we clear highlighting
    var node_clear_distance = 60;    //Distance at which point we clear highlighting

    instance.clear_all_node_selections = function()
    {   
        special_nodes = {};
        selected_node = null;
        highlight_node = null;
    };

    instance.clear_all_line_selections = function()
    {   
        special_links = {};
        selected_link = null;
        highlight_link = null;
        highlight_link_start_mouse_coord = null;
    };

    instance.clear_all_special_nodes = function()
    {   
        special_nodes = {};
    };

    instance.clear_all_special_links = function()
    {   
        special_links = {};
    };

    instance.clear_all_editable_nodes = function()
    {   
        editable_nodes = {};
    };

    var node_over = function (d)
    {
        instance.clear_all_special_links();
        instance.clear_all_special_nodes();

        instance.mycanvas.attention_circle.clear();
        dragging_ended_node = d;
        
        /*
        if(instance.infoballoon_flags.showing && d.key !== instance.infoballoon_flags.key)
        {
            instance.hide_info_balloon();
        }        
        console.log("LOOK instance.infoballoon_flags.showing=" + instance.infoballoon_flags.showing + " at " + d.key);
        */
       
        /*
        //instance.set_node_highlight(d);
        if(!drag_started && d.hasOwnProperty("tooltip"))
        {
            //TODO -- See if we are over the connnector
            var overconnector = false;
            if(!overconnector)
            {
                instance.show_info_balloon(instance.mycanvas.getPageMouseCoordinates(), d.tooltip);
            }
        }
        */
       
        highlight_node = d;
        highlight_all_connected_nodes(d);
        
        instance.redraw();
    };
    
    var node_out = function (d)
    {
        highlight_node = null;
        dragging_ended_node = null;

        //Only call the hide now if it is already showing!
        if(instance.infoballoon_flags.showing)
        {
            instance.hide_info_balloon();
        }
        
        instance.clear_all_special_links();
        instance.clear_all_special_nodes();
        instance.redraw();
    };

    function link_click(d)
    {
        selected_link = d;
        instance.redraw();
    }

    function link_over(d)
    {
        if(instance.mycanvas.layers_elem === null)
        {
            console.log("DEBUG why is this null in link_over?");
            return;
        }
        var coordinates = d3.mouse(instance.mycanvas.layers_elem);
        highlight_link = d;
        highlight_link_start_mouse_coord = coordinates;
        instance.redraw();
    }

    instance.tick = function (e) 
    {
        instance.tick_debug_count++;

        var k = e.alpha;
        if(k < .03) // && dragging_started_node !== null)
        {
            //just end it here
            instance.force.alpha(0);
            instance.mycanvas.attention_circle.clear();
            return;
        }
        var visible_coordinates = instance.mycanvas.getVisibleRectangleFacts();
        var attention_circle_target_key = instance.mycanvas.attention_circle.target_key;
        
        //Compute new positions for the nodes
        instance.graphdata.nodes.forEach(function(o, i) 
        {
            if(!o.fixed)
            {
                //Move it by a declining factor up to a limit
                var current_lane = instance.actionlayout.methods.getLaneInfo(o.assignment);
                var dy;
                var dx;
                var sublane_info;
                var content_center;
                var fastjump_trigger_x = {'min':null,'max':null};
                if(o.assignment === bigfathom_util.env.multilevelhierarchy.hierarchy_lane)
                {
                    var hl_offset = parseInt(o.hierarchy_level) - 1;  
                    if(!current_lane.sublanes.hasOwnProperty(hl_offset))
                    {
                        console.log("LOOK ERR hlo=" + hl_offset + " o=" + JSON.stringify(o));
                        throw "ERROR DID NOT FIND EXPECTED sublane=" + hl_offset + " current_lane.sublanes=" + JSON.stringify(current_lane.sublanes);
                    }
                    if(!current_lane.hasOwnProperty('sublanes'))
                    {
                        console.log("ERROR FAILED for o=" + JSON.stringify(o));
                        throw "ERROR DID NOT FIND REQUIRED sublanes property in current_lane=" + JSON.stringify(current_lane);
                    }
                    if(!current_lane.sublanes.hasOwnProperty(hl_offset))
                    {
                        console.log("ERROR FAILED for o=" + JSON.stringify(o));
                        throw "ERROR DID NOT FIND EXPECTED hl_offset=" + hl_offset + " property in current_lane.sublanes=" + JSON.stringify(current_lane.sublanes);
                    }
                    sublane_info = current_lane.sublanes[hl_offset];
                    if(!sublane_info.hasOwnProperty('content_center'))
                    {
                        console.log("ERROR FAILED for o=" + JSON.stringify(o));
                        throw "ERROR did not find expected content_center property! sublane_info=" + JSON.stringify(sublane_info);
                    }
                    content_center = sublane_info.content_center;
                    //fastjump_trigger_x.left = null;
                } else {
                    content_center = current_lane.content_center;
                }
                fastjump_trigger_x.min = current_lane.content_center.x - 20;
                fastjump_trigger_x.max = current_lane.content_center.x + 30;
                var ideal_center_x = content_center.x;
                var ideal_center_y;
                if(o.assignment === bigfathom_util.env.multilevelhierarchy.hierarchy_lane)
                {
                    if(!content_center.hasOwnProperty('y_zones'))
                    {
                        ideal_center_y = content_center.y + o.center_dy;
                    } else {
                        var special_y;
                        var y_zones = content_center.y_zones;
                        if(o.y < y_zones.under[0])
                        {
                            special_y = y_zones.under[0] - y_zones.dy;
                        } else if(o.y > y_zones.over[0]) {
                            special_y = y_zones.over[0] + y_zones.dy;
                        } else {
                            special_y = y_zones.middle;
                        }
                        ideal_center_y = special_y + o.center_dy;
                    }
                } else {
                    ideal_center_y = visible_coordinates.y1 + (visible_coordinates.y2 - visible_coordinates.y1) / 2;
                }

                if(o === dragging_started_node)
                {
                    if(drag_line_activated)
                    {
                        instance.move_drag_line(o);
                    }
                }

                if(o.ideal_tickmove_count > 5)
                {
                    o.ideal_y = null;   //We've moved enough toward this, turn it off
                    o.ideal_tickmove_count = 0;
                }
                if(o.ideal_y !== null)
                {
                    o.ideal_tickmove_count++;
                    dx = o.ideal_x - o.x;
                    dy = (o.ideal_y - o.y) / 5;
                } else {
                    dx = ideal_center_x - o.x;
                    dy = (ideal_center_y - o.y) / 10;
                }
                o.y += dy * k;
                if(fastjump_trigger_x.min === null || (fastjump_trigger_x.min > o.x && fastjump_trigger_x.max < o.x))
                {
                    o.x += dx * k;
                } else {
                    o.x += dx * .5;
                }
                
                if(attention_circle_target_key === o.key)
                {
                    //Move the attention circle to track with the node
                    instance.mycanvas.attention_circle.move(o.x, o.y);    
                }
            }
        });

        //Move the nodes
        instance.node_h_goal_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_goal_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });

        instance.node_h_warn_disconnected_rootnode_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_warn_disconnected_rootnode_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });

        instance.node_h_hide_ant_subsequent_goal_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_hide_ant_subsequent_goal_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });

	instance.node_h_show_ant_subsequent_goal_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_show_ant_subsequent_goal_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });
        instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_h_show_ant_warn_subsequent_disconnected_rootnode_sel for " + JSON.stringify(d);
            }
            return "translate(" + d.x + "," + d.y + ")";
        });

        var c_lane = instance.actionlayout.methods.getLaneInfo(2);
        var content_center = c_lane.content_center;
        var ideal_center_x = content_center.x;
        instance.node_c_goal_sel.attr("transform", function(d) 
        {
            if(isNaN(d.x) || isNaN(d.y))
            {
                throw "Hit NaN in node_c_goal_sel for " + JSON.stringify(d);
            }
            if(attention_circle_target_key === d.key)
            {
                //Move the attention circle to track with the node
                instance.mycanvas.attention_circle.move(ideal_center_x, d.y);    
            }
            return "translate(" + ideal_center_x + "," + d.y + ")";
        });

        //Move the lines
        instance.link_sel
            .attr("x1", function (d) {
                return d.source.x;
            })
            .attr("y1", function (d) {
                return d.source.y;
            })
            .attr("x2", function (d) {
                return d.target.x + bigfathom_util.shapes.lib.keyprops.goal.connector.offset.x;
            })
            .attr("y2", function (d) {
                return d.target.y;
            });
            /*
            .attr("interpolate", function (d) {
                return 'basis';
            });
            */
    };
    
    instance.force = d3.layout.force()
        .nodes(instance.graphdata.nodes)
        .links(instance.graphdata.linksbundle.link_by_offset)
        .size([instance.mycanvas.w, instance.mycanvas.h])
        .linkStrength(instance.force_properties.linkStrength)
        .on("tick", instance.tick)
        .charge(instance.force_properties.charge)
        .gravity(instance.force_properties.gravity);

    var getContextMenuDef = function (d, context_attribs)
    {
        try
        {
            var menudef = {};
            console.log("LOOK build menu for " + JSON.stringify(d));
            console.log("LOOK context_attribs=" + JSON.stringify(context_attribs));
            menudef['callback'] = function(key, options) 
                {
                    if(key === "toggle_branch")
                    {
                        toggle_branch(d);
                    } else 
                    if(key === "view_info")
                    {
                        instance.viewWorkitem(d);
                        //var coordinates = [d.x,d.y];
                        //var coordinates = d3.mouse(instance.mycanvas);
                        //instance.show_info_balloon(coordinates, d.tooltip);
                    } else 
                    if(key === "edit_info")
                    {
                        instance.editWorkitem(d);
                    } else {
                        var m = "clicked: " + key;
                        window.console && console.log(m) || alert(m); 
                    }
                };
            var menudef_items = {};
            if(d.is_candidate)
            {
                menudef_items["commit_onenode"] = {name: "Commit This", icon: "save"};
                menudef_items["sep1"] = "---------";
            }
            var has_ants = bigfathom_util.nodes.hasAntecedents(d);
            var is_completed_branch_root = instance.graphdata.completed_branch_rootids.hasOwnProperty(d.nativeid);
            var is_incompleted_branch_rootids = instance.graphdata.incompleted_branch_rootids.hasOwnProperty(d.nativeid);
            var show_toggle = is_incompleted_branch_rootids || (instance.graphdata.hide_completed_branches && !is_completed_branch_root);
            if(!context_attribs.is_disconnected && has_ants && show_toggle)
            {
                menudef_items["toggle_branch"] = {name: "Toggle #" + d.nativeid + " branch", icon: "hide"};
                menudef_items["sep2"] = "---------";
            }
            menudef_items["view_info"] = {name: "View Details", icon: ""};
            //menudef_items["view_status_history"] = {name: "Status History", icon: "cut"};
            //menudef_items["view_comments"] = {name: "Comments", icon: "copy"};
            //menudef_items["view_forecast_details"] = {name: "Forecasts", icon: "paste"};

            var nodeoffset =  instance.graphdata.fastlookup_maps.nodes.id2offset[instance.graphdata.rootnodeid];
            var rootprojectnode = instance.graphdata.nodes[nodeoffset];                    
            var user_can_edit_target = userCanEditNode(rootprojectnode, instance.graphdata.my_userinfo_map, d);
            if(user_can_edit_target)
            {
                menudef_items["sep3"] = "---------";
                menudef_items["edit_info"] = {name: "Edit", icon: "edit"};
            };

            menudef['items'] = menudef_items;
            return menudef;
        }
        catch(err)
        {
            console.error(err);
        }
    };

    var nodefilter = new bigfathom_util.nodes.filter(graphdata);

    var my_dataselections = function()
    {
        try
        {
            instance.node_h_background_sel = instance.mycanvas.hierarchy_area_layer.selectAll("g.custom").filter("g.hierarchy_area")
                .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"custom","hierarchy_area"), function (d) {
                                return d.key;
                            });

            instance.node_c_background_sel = instance.mycanvas.candidate_area_layer.selectAll("g.custom").filter("g.candidate_area")
                .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"custom","candidate_area"), function (d) {
                                return d.key;
                            });

            var typenames_ar = ['goal','task','equjb','xrcjb'];

            //Candidate tray content
            var myfilter_cn_ar = [];
            myfilter_cn_ar.push({fn:"getAllIncludedNoneExcluded"
                , typename:typenames_ar
                , include_subtypename:null
                , assignment:bigfathom_util.env.multilevelhierarchy.unassigned_lane
                , ignore_removed:true
                , exclude_subtypename:null});
            if(typeof instance.node_c_goal_sel !== 'undefined')
            {
                instance.node_c_goal_sel.remove();
            }
            instance.node_c_goal_sel 
                    = instance.mycanvas.candidate_goals_layer
                        .selectAll("g")
                        .data(nodefilter.getMatching(
                            instance.graphdata.nodes, myfilter_cn_ar), function (d) {
                                return d.key;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            var current_lane = instance.actionlayout.methods.getLaneInfo(2);
            var content_center = current_lane.content_center;
            var x_min = current_lane.content_center.x - 20;
            var x_max = current_lane.content_center.x + 30;
            var ideal_center_x = content_center.x;
            instance.node_c_goal_sel.immediateMoveX(ideal_center_x, x_min, x_max);

            //Goal nodes without antecedents and without disconnect warning
            var myfilter_noant_ar = [];
            myfilter_noant_ar.push({fn:"getAllWithoutAntecedents"
                , typename:typenames_ar
                , include_subtypename:null
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:["warn_disconnected_rootnode"]});
            if(typeof instance.node_h_goal_sel !== 'undefined')
            {
                instance.node_h_goal_sel.remove();
            }
            instance.node_h_goal_sel 
                    = instance.mycanvas.hierarchy_goals_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_noant_ar), function (d) {
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            //Workitem nodes without antecedents and WITH disconnect warning
            var myfilter_noant_with_dw_ar = [];
            myfilter_noant_with_dw_ar.push({fn:"getAllWithoutAntecedents"
                , typename:typenames_ar
                , include_subtypename:["warn_disconnected_rootnode"]
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:null});
            if(typeof instance.node_h_warn_disconnected_rootnode_sel !== 'undefined')
            {
                instance.node_h_warn_disconnected_rootnode_sel.remove();
            }
            instance.node_h_warn_disconnected_rootnode_sel 
                    = instance.mycanvas.hierarchy_warn_disconnected_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_noant_with_dw_ar), function (d) { 
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            //Workitem nodes WITH HIDE antecedents and without disconnect warning
            var myfilter_hide_ant_subsequent_goal = [];
            myfilter_hide_ant_subsequent_goal.push({fn:"getAllHavingAnyHiddenAntecedents"
                , typename:typenames_ar
                , include_subtypename:null
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:["warn_disconnected_rootnode"]});
            if(typeof instance.node_h_hide_ant_subsequent_goal_sel !== 'undefined')
            {
                instance.node_h_hide_ant_subsequent_goal_sel.remove();
            }
            instance.node_h_hide_ant_subsequent_goal_sel 
                    = instance.mycanvas.hierarchy_hide_ant_subsequent_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_hide_ant_subsequent_goal), function (d) { 
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            //Workitem nodes WITH HIDE antecedents and WITH disconnect warning
            var myfilter_hide_ant_warn_subsequent_disconnected_rootnode = [];
            myfilter_hide_ant_warn_subsequent_disconnected_rootnode.push({fn:"getAllHavingAnyHiddenAntecedents"
                , typename:typenames_ar
                , include_subtypename:["warn_disconnected_rootnode"]
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:null});
            if(typeof instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel !== 'undefined')
            {
                instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel.remove();
            }
            instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel 
                    = instance.mycanvas.hierarchy_hide_ant_subsequent_warn_disconnected_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_hide_ant_warn_subsequent_disconnected_rootnode), function (d) { 
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            //Workitem nodes WITH SHOW antecedents and without disconnect warning
            var myfilter_show_ant_subsequent_goal = [];
            myfilter_show_ant_subsequent_goal.push({fn:"getAllHavingAllShowingAntecedents"
                , typename:typenames_ar
                , include_subtypename:null
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:["warn_disconnected_rootnode"]});
            if(typeof instance.node_h_show_ant_subsequent_goal_sel !== 'undefined')
            {
                instance.node_h_show_ant_subsequent_goal_sel.remove();
            }
            instance.node_h_show_ant_subsequent_goal_sel 
                    = instance.mycanvas.hierarchy_show_ant_subsequent_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_show_ant_subsequent_goal), function (d) {        
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });

            //Workitem nodes WITH SHOW antecedents and WITH disconnect warning
            var myfilter_show_ant_warn_subsequent_disconnected_rootnode = [];
            myfilter_show_ant_warn_subsequent_disconnected_rootnode.push({fn:"getAllHavingAllShowingAntecedents"
                , typename:typenames_ar
                , include_subtypename:["warn_disconnected_rootnode"]
                , assignment:bigfathom_util.env.multilevelhierarchy.hierarchy_lane
                , ignore_removed:true
                , exclude_subtypename:null});
            if(typeof instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel !== 'undefined')
            {
                instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel.remove();
            }
            instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel 
                    = instance.mycanvas.hierarchy_show_ant_subsequent_warn_disconnected_layer
                        .selectAll("g")
                .data(nodefilter.getMatching(
                    instance.graphdata.nodes, myfilter_show_ant_warn_subsequent_disconnected_rootnode), function (d) {        
                                return d.key;
                            }).attr("class", function(d) {
                                return d.type;
                            }).classed("candidate", function (d) {
                                return d.is_candidate;
                            }).on("contextmenu", function(d, index) {
                                showContextMenuForOneWorkitem(d);
                            });
    /*
            instance.node_h_goal_sel.on("dblclick", function (d) {
                    d3.event.stopPropagation();
                    var currenturl = window.location.href;     // Returns full URL
                    var newurl;
                    if(currenturl.indexOf("?") > -1)
                    {
                        newurl = currenturl + "&goalid=" + d.nativeid;
                    } else {
                        newurl = currenturl + "?goalid=" + d.nativeid;
                    }
                    //TODO $("#loader").addClass("overlay-loader");
                    //TODO window.location.replace(newurl);
                });
    */        
            instance.link_sel = instance.mycanvas.hierarchy_lines_layer.selectAll(".link")
                .data(bigfathom_util.nodes.getLinkSubset4D3(instance.graphdata), function (d) {
                                return d.key;
                            });
            instance.link_sel.classed("selected", function(d) 
                                { 
                                    return selected_link !== null && d.key === selected_link.key;
                                }).classed("highlight", function(d) 
                                { 
                                    return highlight_link !== null && d.key === highlight_link.key;
                                }).classed("special", function(d) 
                                { 
                                    return isSpecialLink(d);
                                });

        }
        catch(err)
        {
            console.error(err);
        }
    };
    
    my_dataselections();
    
    var my_resize = function()
    {
        try
        {
            var nodefilter = new bigfathom_util.nodes.filter(graphdata);
            var oldcorefacts = instance.actionlayout.methods.getCoreFacts();
            var corefacts = instance.actionlayout.methods.recomputeCoreFacts(instance.graphdata);

            var container_attribs = instance.actionlayout.methods.getAllContainerAttribs();
            bigfathom_util.hierarchy_data.redimensionGraphData(container_attribs, instance.graphdata);

            //Change the background now
            instance.node_h_background_sel = instance.mycanvas.hierarchy_area_layer.selectAll("g.custom").filter("g.hierarchy_area")
                .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"custom","hierarchy_area"), function (d) {
                                return d.key;
                            });
            instance.node_h_background_sel.updateForceNodeShapes(instance.shape_manager,"custom");

            instance.node_c_background_sel = instance.mycanvas.candidate_area_layer.selectAll("g.custom").filter("g.candidate_area")
                .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"custom","candidate_area"), function (d) {
                                return d.key;
                            });
            instance.node_c_background_sel.updateForceNodeShapes(instance.shape_manager,"custom");

            //Make sure the unassigned nodes stay in the unassigned node area
            var current_lane = instance.actionlayout.methods.getLaneInfo(2);
            var content_center = current_lane.content_center;
            var ideal_center_x = content_center.x;
            //instance.node_c_goal_sel.immediateMoveX(ideal_center_x, x_min, x_max);        
            var attention_circle_target_key = instance.mycanvas.attention_circle.target_key;
            instance.node_c_goal_sel.attr("transform", function(d) 
                    {
                        if(isNaN(d.x) || isNaN(d.y))
                        {
                            throw "Hit NaN in node_c_goal_sel for " + JSON.stringify(d);
                        }
                        if(attention_circle_target_key === d.key)
                        {
                            //Move the attention circle to track with the node
                            instance.mycanvas.attention_circle.move(ideal_center_x, d.y);    
                        }
                        return "translate(" + ideal_center_x + "," + d.y + ")";
                    });

            //See if we really need to redraw
            var sublane_count1 = oldcorefacts.lanes[0].sublanes.length;
            var sublane_count2 = corefacts.lanes[0].sublanes.length;
            if(sublane_count1 !== sublane_count2)
            {
                instance.redraw();
            }
        }
        catch(err)
        {
            console.error(err);
        }
    };

    var isSpecialNode = function(d)
    {
        return special_nodes.hasOwnProperty(d.key) && special_nodes[d.key];
    };    
    
    var isSpecialLink = function(d)
    {
        return special_links.hasOwnProperty(d.key) && special_links[d.key];
    };
    
    var isEditableNode = function(d)
    {
        return editable_nodes.hasOwnProperty(d.key) && editable_nodes[d.key];
    };
    
    var showContextMenuForOneWorkitem = function(d)
    {
        var selector4menu_tx;
        selector4menu_tx = "#" + d.type + "_" + d.nativeid;
        var context_menu_def = getContextMenuDef(d,{"is_disconnected":0});
        instance.mycanvas.showContextMenu(selector4menu_tx, context_menu_def.items, context_menu_def.callback);

        d3.event.preventDefault();
        drag_started = false;
    };
    
    var my_redraw = function()
    {
        try
        {
            if(instance.auto_datarefresh.isBlockedByNamedRequester("my_redraw"))
            {
                console.log("Already running my_redraw!");
                return;
            }
            instance.auto_datarefresh.markBlocked("my_redraw");
            if(true)  //ALWAYS GET A NEW MANAGER ELSE NODE CHANGES NOT REFLECTED!!!! instance.shape_manager === null)
            {
                var my_data = instance.graphdata;

                var my_node_handlers = {};
                var my_link_handlers = {};

                /*NODE CLICK DOES NOT WORK RELIABLY WITH DRAG IN D3!!!
                my_node_handlers["click"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : function(d) { 
                                if (d3.event.defaultPrevented) alert("LOOK HEY " + JSON.stringify(d)); // click suppressed
                                node_click(d);
                            }
                    };
                */

                my_node_handlers["drag"] = {
                        "handler_type" : "call",
                        "replacement" : true,
                        "function" : my_drag
                    };

                my_node_handlers["mousedown"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : function(d) { 
                                d3.event.stopPropagation(); //Otherwise pans the entire canvas
                                mousedown_item = d;
                            }
                    };

                my_node_handlers["mouseover"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : function(d) { 
                                //d3.event.stopPropagation();
                                node_over(d);
                            }
                    };

                my_node_handlers["mouseout"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : function(d) { 
                                //d3.event.stopPropagation();
                                node_out(d);
                            }
                    };

                my_link_handlers["mouseover"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : link_over
                    };

                my_link_handlers["click"] = {
                        "handler_type" : "on",
                        "replacement" : true,
                        "function" : link_click 
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
                instance.shape_manager = bigfathom_util.shapes.getManager(my_data, my_handlers, my_overrides);
            }

            my_dataselections();

            instance.node_h_background_sel.enter().joinForceNodeShapes(instance.shape_manager,"custom");
            instance.node_h_background_sel.exit().remove();

            instance.node_c_background_sel.enter().joinForceNodeShapes(instance.shape_manager,"custom");
            instance.node_c_background_sel.exit().remove();

            instance.node_c_goal_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_c_goal_sel.exit().remove();
            instance.node_c_goal_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes without antecedents and without disconnect warning
            instance.node_h_goal_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_goal_sel.exit().remove();
            instance.node_h_goal_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes without antecedents and WITH disconnect warning
            instance.node_h_warn_disconnected_rootnode_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_warn_disconnected_rootnode_sel.exit().remove();
            instance.node_h_warn_disconnected_rootnode_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes WITH HIDDEN antecedents and without disconnect warning
            instance.node_h_hide_ant_subsequent_goal_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_hide_ant_subsequent_goal_sel.exit().remove();
            instance.node_h_hide_ant_subsequent_goal_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes WITH HIDDEN antecedents and WITH disconnect warning
            instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel.exit().remove();
            instance.node_h_hide_ant_warn_subsequent_disconnected_rootnode_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes WITH SHOWN antecedents and without disconnect warning
            instance.node_h_show_ant_subsequent_goal_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_show_ant_subsequent_goal_sel.exit().remove();
            instance.node_h_show_ant_subsequent_goal_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            //Goal nodes WITH SHOWN antecedents and WITH disconnect warning
            instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel.enter().joinForceNodeShapes(instance.shape_manager);  //,"goal");
            instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel.exit().remove();
            instance.node_h_show_ant_warn_subsequent_disconnected_rootnode_sel.classed("special", function(d) 
                    { 
                        return isSpecialNode(d);
                    }).classed("editable", function(d) 
                    { 
                        return isEditableNode(d);
                    }).on("contextmenu", function(d, index) {
                        showContextMenuForOneWorkitem(d);
                    });

            instance.link_sel.enter().joinForceLinkShapes(instance.shape_manager);
            instance.link_sel.exit().remove();
            instance.link_sel.classed("selected", function(d) 
                    { 
                        return selected_link !== null && d.key === selected_link.key;
                    }).classed("highlight", function(d) 
                    { 
                        return highlight_link !== null && d.key === highlight_link.key;
                    }).classed("special", function(d) 
                    { 
                        return isSpecialLink(d);
                    });

            instance.auto_datarefresh.markAllowed("my_redraw");
            instance.force
                    .nodes(instance.graphdata.nodes)
                    .links(instance.graphdata.linksbundle.link_by_offset)
                    .start();
        }
        catch(err)
        {
            console.error(err);
        }
    };

    var userCanEditNode = function(rootprojectnode, my_userinfo_map, target)
    {
        try
        {
            var checkTemplateContext = function()
            {
                return false;
            }

            var checkProjectContext = function()
            {
                var okay = false;
                if(rootprojectnode.key === target.key)
                {
                    //All project members can map to the project root!
                    okay = true;
                } else
                if(my_userinfo_map.systemroles.summary.is_systemadmin)
                {
                    //Can do whatever they want in the system
                    okay = true;
                } else {
                    var mypersonid = my_userinfo_map.personid;
                    var isOwner = function(target)
                    {
                        //Check ownership
                        okay = false;
                        if(mypersonid === target.maps.owner_personid)
                        {
                            okay = true;
                        } else {
                            //Check delegates
                            if(target.hasOwnProperty("maps") && target.maps.hasOwnProperty("delegate_owner"))
                            {
                                var maxidx = target.maps.delegate_owner.length;
                                for (var i = 0; i < maxidx; i++) {
                                    if(mypersonid == target.maps.delegate_owner[i]) //Must use == NOT ===!!!
                                    {
                                        okay = true;
                                        break;
                                    }
                                }
                            }
                        }
                        return okay;
                    };

                    if(isOwner(rootprojectnode))
                    {
                        //Owns the project!
                        okay = true;
                    } else {
                        //Owns the target!
                        okay = isOwner(target);
                    }
                }

                return okay;
            }
            
            console.log("LOOK my_userinfo_map=" + JSON.stringify(my_userinfo_map));
            console.log("LOOK target=" + JSON.stringify(target));

            if(bigfathom_util.hierarchy.context_type == 'template')
            {
                return checkTemplateContext();
            } else {
                return checkProjectContext();
            }
        }
        catch(err)
        {
            console.error(err);
        }
    };

    var my_drag = d3.behavior.drag()
        .on("dragstart", function (d) {
            if(d.is_drag_source)
            {
                drag_started = true;
                dragging_started_node = d;
                dragging_started_node.fixed = true;
                dragging_started_node.ideal_y = null;
                d3.event.sourceEvent.stopPropagation(); // it's important that we suppress the mouseover event on the node being dragged. Otherwise it will absorb the mouseover event and the underlying node will not detect it d3.select(this).attr('pointer-events', 'none');

                highlight_all_editable_nodes();
                instance.redraw();
            }
        })
        .on("drag", function (d) {
            if (drag_started) 
            {
                console.log("LOOK instance.just_drag_nodes_activated_ts=" + instance.just_drag_nodes_activated_ts);
                var just_drag_nodes = (Date.now() - instance.just_drag_nodes_activated_ts < 1000);
                var start_lane = instance.actionlayout.methods.getLaneInfo(dragging_started_node.assignment);
                var start_sublane = null;
                
                var left_edge_x;
                var right_edge_x;
                
                if(start_lane.is_simple_lane)
                {
                    //This is the unassigned lane
                    if(just_drag_nodes)
                    {
                        left_edge_x = start_lane.start_x - (start_lane.width / 10);
                        right_edge_x = start_lane.end_x + (start_lane.width / 8);
                    } else {
                        left_edge_x = start_lane.start_x + start_lane.hmargin;
                        right_edge_x = start_lane.end_x - start_lane.hmargin;
                    }
                } else {
                    //This is the hierarchy sublanes lane
                    start_sublane = instance.actionlayout.methods.getSublaneInfo(
                                dragging_started_node.assignment,
                                dragging_started_node.hierarchy_level);
                    if(just_drag_nodes)
                    {
                        left_edge_x = start_sublane.start_x - (start_sublane.width/2);
                        right_edge_x = start_sublane.end_x;
                    } else {
                        left_edge_x = start_sublane.start_x + start_sublane.hmargin;
                        right_edge_x = start_sublane.end_x - start_sublane.hmargin;
                    }
                }

                if(mycanvas.layers_elem === null)
                {
                    console.log("WARNING: Layers is null!");
                    return;
                }
                var mouse = d3.mouse(mycanvas.layers_elem);
                //var translated_mouse = bigfathom_util.d3v3_svg.getTranslatedCoordinates(mycanvas, mouse);
                var mx = mouse[0];

                if(just_drag_nodes)
                {
                    instance.deactivate_drag_line();
                } else {
                    if(mx > right_edge_x)
                    {
                        drag_line_activated = true;
                    } else if (mx < left_edge_x){
                        drag_line_activated = true;
                    } else {
                        instance.deactivate_drag_line();
                    }
                }

                if(!drag_line_activated)
                {
                    //Move the drag node on X axis
                    d.px = d.x;    //d3.event.dx;
                    d.x += d3.event.dx;
                }

                d.py = d.y; //+= d3.event.dy;
                d.y += d3.event.dy;

                if(d.x > right_edge_x)
                {
                    d.x = right_edge_x;
                    d.px = d.x;
                } else if (d.x < left_edge_x){
                    d.x = left_edge_x;
                    d.px = d.x;
                }
                if(drag_line_activated)
                {
                    instance.move_drag_line(d);
                }
                instance.force.alpha(.2);    //Warm it up            
            }
        })
        .on("dragend", function (dragged_from_node) 
        {
            instance.clear_all_editable_nodes();
            instance.auto_datarefresh.markBlocked("dragend");
            if(dragging_started_node && dragging_ended_node && dragging_ended_node.is_drag_target)
            {
                var source = null;
                var target = null;
                source = dragging_started_node;
                target = dragging_ended_node;
                if(source !== null && target !== null && source.key !== target.key) //right click causes source=target
                {
                    if(bigfathom_util.hierarchy.readonly)
                    {
                       alert("No edits allowed"); 
                    } else {

                        var okay = true;
                        var target_lanenum = target.assignment;
                        var target_is_subproject = (target.type === 'goal' && (
                                (target.hierarchy_level > 1 
                                    && target.hasOwnProperty("subtype") 
                                    && target.subtype.indexOf("project") > -1 )));
                        if(target_is_subproject)
                        {
                            //TODO tell the user with a nice looking balloon!
                            console.log("Not allowed for workitem " + source.key + " to be child of project node " + target.key + " target=" + JSON.stringify(target));
                            alert("You attempted to declare a dependency here for antecedant project " + target.key + ".  Such antecedant project details cannot be edited here.");
                            okay = false;
                        };

                        var nodeoffset =  instance.graphdata.fastlookup_maps.nodes.id2offset[instance.graphdata.rootnodeid];
                        var rootprojectnode = instance.graphdata.nodes[nodeoffset];                    
                        var user_can_edit_target = userCanEditNode(rootprojectnode, instance.graphdata.my_userinfo_map, target);
                        if(target.status_cd !== 'B' && !user_can_edit_target)
                        {
                            //TODO tell the user with a nice looking balloon!
                            console.log("No permission to edit node " + target.key + " target=" + JSON.stringify(target));
                            //console.log("my_userinfo_map = " + JSON.stringify(my_userinfo_map));
                            alert("You cannot make this dependency declaration because you are not the owner and are not a delegate owner of " + target.key + " and it is not in a brainstorm status.");
                            okay = false;
                        };

                        if(okay)
                        {
                            var candidatelink_sourcenode = source;
                            var candidatelink_targetnode = target;
                            var is_legal_link = instance.actionlayout.methods.isLegalLink(candidatelink_sourcenode, candidatelink_targetnode
                                                                                        , source, instance.graphdata, 0);
                            if(is_legal_link)
                            {
                                if(target_lanenum === bigfathom_util.env.multilevelhierarchy.hierarchy_lane)
                                {
                                    var resultbundle = instance.actionlayout.methods.createNewLinkForUI(instance.graphdata, source, target);
                                    if(resultbundle.onelinkbundle !== null)
                                    {
                                        instance.resize();
                                        instance.redraw();
                                        var change_comment = "dragend " + source.key + " to " + target.key;
                                        instance.saveOneNewLinkBundle(resultbundle.onelinkbundle, change_comment);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                console.log("@@LOOK DRAG END WITHOUT A TARGET!!! ");
            }

            //Let it go.
            instance.deactivate_drag_line();
            if(dragging_started_node !== null)
            {
                dragging_started_node.fixed = false;
                dragging_started_node = null;
                instance.updateGraphDataTopologyValues(true);
            }
            drag_started = false;
            instance.auto_datarefresh.markAllowed("dragend");
            instance.force.resume();

        });

    instance.resize = function ()
    {
        my_resize();
    };
    
    instance.redraw = function ()
    {
        my_redraw();
    };

    instance.redraw();
    
    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    };
    
    instance.jump2node = function(match_txt, activate_attention_circle)
    {
        console.log("LOOK starting jump2node(" + match_txt + ", " + activate_attention_circle + ")");
        var corefacts = instance.actionlayout.methods.getCoreFacts();
        var look_x;
        var look_y;
        var targetnode = null;
        var foundnode = false;
        if(match_txt === 'rootnodeid' || match_txt === 'rootnode')
        {
            if(!instance.graphdata.hasOwnProperty('rootnodeid'))
            {
                throw "Did NOT find rootnodeid in id2offset!";
            }
            var nodeoffset =  instance.graphdata.fastlookup_maps.nodes.id2offset[instance.graphdata.rootnodeid];
            targetnode = instance.graphdata.nodes[nodeoffset];
            look_x = 0;
            foundnode = true;
        } else {
            if(match_txt.startsWith("G#") || match_txt.startsWith("g#"))
            {
                match_txt = "goal_" + match_txt.substring(2);
            } else
            if(match_txt.startsWith("T#") || match_txt.startsWith("t#"))
            {
                match_txt = "task_" + match_txt.substring(2);
            } else
            if(match_txt.startsWith("Q#") || match_txt.startsWith("q#"))
            {
                match_txt = "equjb_" + match_txt.substring(2);
            } else
            if(match_txt.startsWith("X#") || match_txt.startsWith("x#"))
            {
                match_txt = "xrcjb_" + match_txt.substring(2);
            }
            if(!isNumeric(match_txt))
            {
                var findkey = match_txt;
                foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                if(!foundnode)
                {
                    findkey = "candidate_" + match_txt;
                    foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                }
                if(!foundnode)
                {
                    if(match_txt.startsWith("task_"))
                    {
                        //Try the other task subtypes
                        var justid = match_txt.substring(5);
                        var findkey = "equjb_" + justid;  
                        foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                        if(!foundnode)
                        {
                            findkey = "candidate_" + findkey;
                            foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                        } 
                        if(!foundnode)
                        {
                            var findkey = "xrcjb_" + justid;  
                            foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                            if(!foundnode)
                            {
                                findkey = "candidate_" + findkey;
                                foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                            } 
                        }
                    }
                }
            } else {
                //Cycle through possibilities
                var findkey = "goal_" + match_txt;
                foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                if(!foundnode)
                {
                    findkey = "candidate_" + findkey;
                    foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                }
                if(!foundnode)
                {
                    findkey = "task_" + match_txt;
                    foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    if(!foundnode)
                    {
                        findkey = "candidate_" + findkey;
                        foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    }
                }
                if(!foundnode)
                {
                    findkey = "equjb_" + match_txt;
                    foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    if(!foundnode)
                    {
                        findkey = "candidate_" + findkey;
                        foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    }
                }
                if(!foundnode)
                {
                    findkey = "xrcjb_" + match_txt;
                    foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    if(!foundnode)
                    {
                        findkey = "candidate_" + findkey;
                        foundnode = instance.graphdata.fastlookup_maps.nodes.key2offset.hasOwnProperty(findkey);
                    }
                }
            }
            if(foundnode)
            {
                var nodeidx = instance.graphdata.fastlookup_maps.nodes.key2offset[findkey];
                targetnode = instance.graphdata.nodes[nodeidx];
            }        
        }
        
        if(foundnode && targetnode !== null)
        {
            if(targetnode.show_node !== true)
            {
                //Expand the chart to show the node now
                instance.actionlayout.methods.showNode(targetnode.key, instance.graphdata);
                instance.redraw();
            } 
            var visible_coordinates = instance.mycanvas.getVisibleRectangleFacts();
            var trans;
            if(match_txt === 'rootnode')
            {
                look_x = 0; 
                if(targetnode.y > visible_coordinates.y2)
                {
                    look_y = targetnode.y - corefacts.canvas.h / 2;
                } else {
                    look_y = 0;    
                }
            } else {
                if(targetnode.hasOwnProperty("assignment") && targetnode.assignment === 2)
                {
                    //look_x = targetnode.x - 3 * (corefacts.canvas.w / 4);
                    look_x = 0;
                    look_y = targetnode.y - corefacts.canvas.h / 2;
                } else {
                    look_x = targetnode.x - corefacts.canvas.w / 4;
                    look_y = targetnode.y - corefacts.canvas.h / 2;
                }
            }
            /* TODO right justify instead of having empty right gap
            var visible_coordinates = instance.mycanvas.getVisibleRectangleFacts();
            var visible_width = visible_coordinates.x2 - visible_coordinates.x1;
            var empty_right_gap = corefacts.canvas.w;
            if((corefacts.canvas.w - look_x) < (visible_coordinates.x2 - visible_coordinates.x1))
            {
                look_x =  visible_coordinates.x2 - targetnode.x;
            }
            */
            var neg_x = -1 * look_x;
            var neg_y = -1 * look_y;
            trans = [neg_x, neg_y];
            bigfathom_util.d3v3_svg.rescale(instance.mycanvas, trans);
            if(activate_attention_circle)
            {
                mycanvas.attention_circle.activate(targetnode.key, targetnode.x, targetnode.y);
            }
        }
        console.log("LOOK instance.graphdata.fastlookup_maps=" + JSON.stringify(instance.graphdata.fastlookup_maps));
        return foundnode;
    };

    var getCleanLinksForSave = function (raw_wid_links, action_override)
    {
        try
        {
            console.log("STARTING sendpackage raw_link_by_offset=" + JSON.stringify(raw_wid_links));
            if (typeof action_override === "undefined")
            {
                action_override = null;   
            }
            var raw_nodes = instance.graphdata.nodes;
            //var raw_link_by_offset = instance.graphdata.linksbundle.link_by_offset;
            var raw_sourcenode;
            var raw_targetnode;
            var clean_links = [];
            for(var i=0; i<raw_wid_links.length; i++)
            {
                var onelink = raw_wid_links[i];

                if(onelink.type === "link_by_wid")
                {
                    var srcwid = onelink['srcwid'];
                    var trgwid = onelink['trgwid'];
                    var srcno = instance.graphdata.fastlookup_maps.nodes.id2offset[srcwid];
                    var trgno = instance.graphdata.fastlookup_maps.nodes.id2offset[trgwid];
                    raw_sourcenode = raw_nodes[srcno];
                    raw_targetnode = raw_nodes[trgno];
                } else            
                if(onelink.type === "link_by_key")
                {
                    var srcnk = onelink['srcnk'];
                    var trgnk = onelink['trgnk'];
                    var srcno = instance.graphdata.fastlookup_maps.nodes.key2offset[srcnk];
                    var trgno = instance.graphdata.fastlookup_maps.nodes.key2offset[trgnk];
                    raw_sourcenode = raw_nodes[srcno];
                    raw_targetnode = raw_nodes[trgno];
                } else {
                    raw_sourcenode = raw_nodes[onelink.srcno];
                    raw_targetnode = raw_nodes[onelink.trgno];
                }
                var cleanlink = {
                                    'key': onelink.key,
                                    'srcnk': raw_sourcenode.key,
                                    'trgnk': raw_targetnode.key,
                                    'srcnid': raw_sourcenode.nativeid,
                                    'trgnid': raw_targetnode.nativeid,
                                    'src_is_candidate': raw_sourcenode.is_candidate,
                                    'trg_is_candidate': raw_targetnode.is_candidate
                                };
                if(action_override !== null)
                {
                    cleanlink['action'] = action_override;
                } else {
                    if(onelink.is_new)
                    {
                        cleanlink['action'] = 'add';
                    } else {
                        cleanlink['action'] = 'none';
                    }
                }
                clean_links.push(cleanlink);
            }    
            console.log("DONE sendpackage clean_links=" + JSON.stringify(clean_links));
            return clean_links;
        }
        catch(err)
        {
            console.error(err);
        }
    };

    instance.saveOneNewLinkBundle = function (onelinkbundle, change_comment)
    {
        if (typeof change_comment === "undefined")
        {
            change_comment ="Created new link";
        }
        var raw_links_by_wid = [onelinkbundle.link_by_wid];
        var changeid = onelinkbundle.link_by_wid.key;
        var result = instance.saveSomeLinkChanges(raw_links_by_wid, changeid, change_comment);
        return result;
    };
    
    /**
     * @deprecated???????????????
     */
    instance.saveOneLinkRemoval = function (sourcenode_nativeid, targetnode_nativeid, change_comment)
    {
        var link_by_wid = instance.graphdata.linksbundle.link_by_wid;
        var foundlink = false;
        var one_link_by_wid;
        var count_link_by_wid = link_by_wid.length;
        for (var i = 0; i < count_link_by_wid; i ++)
        {
            one_link_by_wid = link_by_wid[i];
            if(sourcenode_nativeid === one_link_by_wid.srcwid 
                    && targetnode_nativeid === one_link_by_wid.trgwid)
            {
                foundlink = true;
                break;
            }
        }        
        if(!foundlink)
        {
            throw "Did NOT find an existing link for sourcenode_nativeid=" + sourcenode_nativeid + ", targetnode_nativeid=" + targetnode_nativeid;
        }
        
        var raw_links_by_wid = [one_link_by_wid];
        var changeid = one_link_by_wid.key;
        if (typeof change_comment === "undefined")
        {
            change_comment ="Removed link from " + sourcenode_nativeid + " to " + targetnode_nativeid;
        }
        var result = instance.saveSomeLinkRemovals(raw_links_by_wid, changeid, change_comment);
        return result;
    };
    
    instance.saveSomeLinkChanges = function (raw_link_by_wid, changeid, change_comment)
    {
        var node_changes = [];
        var link_changes = getCleanLinksForSave(raw_link_by_wid);
        var result = instance.saveCurrentChanges(node_changes, link_changes, changeid, change_comment);
        return result;
    };

    instance.saveSomeLinkRemovals = function (raw_link_by_wid, changeid, change_comment)
    {
        var node_changes = [];
        var link_changes = getCleanLinksForSave(raw_link_by_wid,'remove');
        var result = instance.saveCurrentChanges(node_changes, link_changes, changeid, change_comment);
        return result;
    };

    instance.createNewCandidateWorktitem = function (nodeinfo, add_comment)
    {
        var link_changes = [];
        var node_changes = [];
        if(!nodeinfo.hasOwnProperty('action'))
        {
            nodeinfo['action'] = 'add_candidate';
        }
        node_changes.push(nodeinfo);
        instance.saveCurrentChanges(node_changes, link_changes, 'addnew', add_comment);
    };
    
    instance.saveOneNodeEdit = function (nodeinfo, changeid, change_comment)
    {
        var link_changes = [];
        var node_changes = [];
        if(!nodeinfo.hasOwnProperty('action'))
        {
            nodeinfo['action'] = 'change';
        }

        node_changes.push(nodeinfo);
        instance.saveCurrentChanges(node_changes, link_changes, changeid, change_comment);
    };

    /**
     * Provide the change information ready to go.
     */
    instance.saveCurrentChanges = function (node_changes, link_changes, changeid, change_comment)
    {
        try
        {
            instance.auto_datarefresh.markBlocked("saveCurrentChanges");
            var projectid = instance.graphdata.projectid;
            if (typeof node_changes === 'undefined')
            {
                node_changes = [];   
            }
            if (typeof link_changes === 'undefined')
            {
                link_changes = [];   
            }
            if (typeof changeid === 'undefined')
            {
                changeid = "p#" + projectid;   
            }
            if (typeof change_comment === 'undefined')
            {
                change_comment = "p#" + projectid + " " + node_changes.length + " node changes and " + link_changes.length + " link changes";   
            }

            var dataname = 'hierarchy_changes';
            var send_fullurl = bigfathom_util.data.getSendDataUrl(dataname);

            var sendpackage = {
                    "dataname": dataname,
                    "databundle":{
                            "projectid": projectid,
                            "link_changes": link_changes,
                            "node_changes": node_changes,
                            "change_comment": change_comment
                        }
                    };
            //console.log("LOOK sendpackage=" + JSON.stringify(sendpackage));
            //alert("LOOK called saveCurrentChanges");
            var callbackActionFunction = function(callbackid, responseBundle)
            {
                try
                {
                    //Now, update the display to show nodes as they are now saved
                    var needs_redraw = false;
                    if(responseBundle.responseDetail.hasOwnProperty('map_brainstormid2wid'))
                    {
                        var map_brainstormid2wid = responseBundle.responseDetail.map_brainstormid2wid;
                        if(map_brainstormid2wid.length > 0)
                        {
                            console.log("LOOK saveCurrentChanges map_brainstormid2wid=" + JSON.stringify(map_brainstormid2wid));
                            instance.actionlayout.methods.updateWIDMastersForBrainstormConversions(graphdata, map_brainstormid2wid);
                            instance.actionlayout.methods.refreshAllLookupMaps(instance.graphdata);
                            needs_redraw = true;
                        }
                    }

                    if(node_changes.length > 0)
                    {
                        for(var i=0; i<node_changes.length; i++)
                        {
                            var nodedetail = node_changes[i];
                            var actionname = nodedetail['action'];
                            if(actionname === 'change')
                            {
                                var onenodeoffset = bigfathom_util.hierarchy_data.getOneNodeOffsetByNativeID(instance.graphdata.nodes, nodedetail.workitemid);
                                instance.graphdata.nodes[onenodeoffset].workitem_nm = "*" + nodedetail.workitem_nm;
                            }
                        }
                        needs_redraw = true;
                    }

                    instance.auto_datarefresh.markAllowed("saveCurrentChanges");
                    if(needs_redraw)
                    {
                        instance.updateGraphDataTopologyValues(needs_redraw);
                    }
                }
                catch(err)
                {
                    console.error("FAILED callbackActionFunction because " + err);
                }
            };

            //uiblocker.show("tr#" + workitemid);
            bigfathom_util.data.writeData2Server(send_fullurl, sendpackage, callbackActionFunction, changeid);
        }
        catch(err)
        {
            console.error("FAILED saveCurrentChanges because " + err);
        }
    };
    
    instance.createNewWorkitem = function ()
    {
        $("#new_workitem_nm").val("");
        $("#new_purpose_tx").val("");
        $("#dlg_add_workitem_container").css("display","block");
    };

    instance.importPartsTemplate = function ()
    {
        alert("Parts template feature currently disabled");
    };

    instance.viewWorkitem = function (nodeinfo)
    {
        instance.popupWorkitem(nodeinfo, 'view');
    };
        
    instance.editWorkitem = function (nodeinfo)
    {
        instance.popupWorkitem(nodeinfo, 'edit');
    };
    
    instance.popupWorkitem = function (nodeinfo, actionname)
    {
        try
        {
            var nativeid = nodeinfo.nativeid;
            //console.log("LOOK nodeinfo=" + JSON.stringify(nodeinfo));
            $("#dlg_loading_container").css("display","block");

            var callbackid = actionname + "_workitem#" + nativeid;
            var dataname;
            if(bigfathom_util.hierarchy.context_type !== 'template')
            {
                dataname = "one_workitem_with_lookupinfo";
            } else {
                dataname = "one_template_workitem_with_lookupinfo";
            }
            var grab_fullurl = bigfathom_util.data.getGrabDataUrl(dataname,{"nativeid": nativeid});
            var callbackActionFunction = function(callbackid, responseBundle)
            {
                if(responseBundle == null || responseBundle.data == null)
                {
                    console.log("ERROR getting result for nativeid=" + nativeid + "; we got response=" + JSON.stringify(responseBundle));
                    alert("Unable to complete request for workitem#" + nativeid + "!  You may need to refresh your browser.");
                } else {
                    var workitemdetail = responseBundle.data.data;

                    var maps = workitemdetail.maps;
                    var lookups = workitemdetail.lookups;
                    var peopledetail = lookups.people;
                    var ownerdetail;
                    if(peopledetail.hasOwnProperty(workitemdetail.owner_personid))
                    {
                        ownerdetail = peopledetail[workitemdetail.owner_personid];
                    } else {
                        //Did NOT find this person -- continue.
                        console.log("WARNING did NOT find detail for workitemdetail.owner_personid="+workitemdetail.owner_personid)
                        console.log("WARNING workitemdetail=" + JSON.stringify(workitemdetail));
                        console.log("WARNING peopledetail=" + JSON.stringify(peopledetail));
                        console.log("WARNING ownerdetail=" + JSON.stringify(ownerdetail));
                        ownerdetail = {"first_nm":"user#"+workitemdetail.owner_personid,"last_nm":""};
                    }
                    var delegate_ownerpersonids;
                    var count_delegate_owners;
                    if(!maps.hasOwnProperty("delegate_owner"))
                    {
                        //Normal for template
                        delegate_ownerpersonids = [];
                        count_delegate_owners = 0;
                    } else {
                        //Normal for regular project
                        delegate_ownerpersonids = maps.delegate_owner;
                        count_delegate_owners = delegate_ownerpersonids.length;
                    }
                    var topinfomarkup = "<fieldset>";
                    topinfomarkup += "<label for='ownerinfo'>Owner</label> ";
                    
                    topinfomarkup += "<span id='ownerinfo' title='id#" + workitemdetail.owner_personid 
                            + " and " + count_delegate_owners + " delegate owners'>" 
                            + ownerdetail.first_nm 
                            + " " 
                            + ownerdetail.last_nm 
                            + "</span><br>";
                    if(count_delegate_owners > 0)
                    {
                        var do_markup = "";
                        for (var i = 0; i < count_delegate_owners; i++)
                        {
                            var do_personid = delegate_ownerpersonids[i];
                            if(i > 0)
                            {
                                do_markup += ", ";   
                            }
                            if(!peopledetail.hasOwnProperty(do_personid))
                            {
                                console.log("ERROR MISSING do_personid=" + do_personid + " IN peopledetail=" + JSON.stringify(peopledetail));  
                                do_markup += "<span title='id#" + do_personid + "'>USER#" + do_personid + "</span><br>";
                            } else {
                                var do_detail = peopledetail[do_personid];
                                do_markup += "<span title='id#" + do_personid + "'>" + do_detail.first_nm + " " + do_detail.last_nm + "</span><br>";
                            }
                        }
                        topinfomarkup += "<label for='do_info'>Delegate Owner(s)</label> ";
                        topinfomarkup += "<span id='do_info'>" + do_markup + "</span><br>";
                    }
                    topinfomarkup += "<label for='uniqueid'>ID</label> ";
                    topinfomarkup += "<span id='uniqueid' title='the unique ID of this existing record'>" + workitemdetail.nativeid + "</span><br>";
                    topinfomarkup += "</fieldset>";

                    var infoelem_name = "#dlg_"+ actionname +"_workitem_topinfo";
                    console.log("DEBUGGING infoelem_name="+infoelem_name);
                    
                    var infoelem = $(infoelem_name);
                    infoelem.empty();
                    infoelem.append(topinfomarkup);
                    if(actionname === 'edit')
                    {
                        var status_cd_dropdown = $("#edit_workitem_status_cd");
                        status_cd_dropdown.find('option').remove().end();
                        for(var key in graphdata.status_cd_lookup)
                        {
                            if(graphdata.status_cd_lookup.hasOwnProperty(key))
                            {
                                var sdetail =  graphdata.status_cd_lookup[key];
                                var thelabel = key + " - " + sdetail.title_tx;
                                if(sdetail.terminal_yn !== '0')
                                {
                                    thelabel += ' (terminal)';
                                }
                                status_cd_dropdown.append($("<option />").val(key).text(thelabel));
    //console.log("LOOK sdetail=" + JSON.stringify(sdetail));                            
                            }
                        }
    //alert("LOOK sdetail now!");
                        $("#edit_nativeid").val(workitemdetail.nativeid);
                        $("#edit_workitem_nm").val(workitemdetail.workitem_nm);
                        $("#edit_purpose_tx").val(workitemdetail.purpose_tx);
                        $("#edit_workitem_basetype").val(workitemdetail.workitem_basetype);
                        $("#edit_workitem_status_cd").val(workitemdetail.status_cd);
                        $("#edit_branch_effort_hours_est").val(workitemdetail.branch_effort_hours_est);
                        $("#edit_remaining_effort_hours").val(workitemdetail.remaining_effort_hours);
                        $("#dlg_edit_workitem_container").css("display","block");
                    } else if(actionname === 'view') {
                        var status_cd_dropdown = $("#view_workitem_status_cd");
                        for(var key in graphdata.status_cd_lookup)
                        {
                            if(graphdata.status_cd_lookup.hasOwnProperty(key))
                            {
                                var sdetail =  graphdata.status_cd_lookup[key];
                                var thelabel = key + " - " + sdetail.title_tx;
                                status_cd_dropdown.append($("<option />").val(key).text(thelabel));
                            }
                        }
                        $("#view_nativeid").val(workitemdetail.nativeid);
                        $("#view_workitem_nm").val(workitemdetail.workitem_nm);
                        $("#view_purpose_tx").val(workitemdetail.purpose_tx);
                        $("#view_workitem_basetype").val(workitemdetail.workitem_basetype);
                        $("#view_workitem_status_cd").val(workitemdetail.status_cd);
                        $("#view_branch_effort_hours_est").val(workitemdetail.branch_effort_hours_est);
                        $("#view_remaining_effort_hours").val(workitemdetail.remaining_effort_hours);
                        $("#dlg_view_workitem_container").css("display","block");
                    }
                }
                $("#dlg_loading_container").css("display","none");
            };
            if(previous_project_edit_key !== null)
            {
                callbackid = callbackid + "_" + previous_project_edit_key;
            }
            bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, callbackid);
        }
        catch(err)
        {
            console.error(err);
        }
        
////////////////////////////////////        
        
    };

    function resize()
    {
        instance.resize();
    };

    function keyup() 
    {
        try
        {
            if (d3.event.ctrlKey)
            {
                console.log("LOOK RELEASED CTRL KEY!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
            }
            if (d3.event.shiftKey)
            {
                //SHIFT to prevent dragbehavior
                instance.just_drag_nodes_activated_ts = 0;
                instance.deactivate_drag_line();
                //console.log("LOOK UP SHIFT KEY just_drag_nodes=" + instance.just_drag_nodes_activated_ts);
            }
        }
        catch(err)
        {
            console.error(err);
        }
    };
    
    function keydown() 
    {
        try
        {
            if (d3.event.ctrlKey)
            {
                console.log("LOOK PRESSED CTRL KEY!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
            }
            if (d3.event.shiftKey)
            {
                //SHIFT to prevent dragbehavior
                instance.just_drag_nodes_activated_ts = Date.now();
                //console.log("LOOK PRESSED SHIFT KEY just_drag_nodes=" + instance.just_drag_nodes_activated_ts);
            }
            if (d3.event.keyCode === 27) {
                //ESC to clear all selections and balloons
                instance.hide_info_balloon();
                instance.clear_all_node_selections();
                instance.clear_all_line_selections();
                instance.redraw();
            } else
            if (d3.event.keyCode === 32) {
                //SPACEBAR to stop simulation
                instance.force.stop();
            } else 
            if (d3.event.keyCode === 46 || d3.event.keyCode === 8) {
                //DEL or BACKSPACE key pressed
                if (selected_link !== null)
                {
                    var nodes = graphdata.nodes;
                    var targetnode = nodes[selected_link.trgno];

                    var okay = true;
                    var nodeoffset =  instance.graphdata.fastlookup_maps.nodes.id2offset[instance.graphdata.rootnodeid];
                    var rootprojectnode = instance.graphdata.nodes[nodeoffset];                    
                    var user_can_edit_target = userCanEditNode(rootprojectnode, instance.graphdata.my_userinfo_map, targetnode);
                    if(targetnode.status_cd !== 'B' && !user_can_edit_target)
                    {
                        //TODO tell the user with a nice looking balloon!
                        console.log("No permission to edit node " + targetnode.key + " target=" + JSON.stringify(targetnode));
                        console.log("my_userinfo_map = " + JSON.stringify(instance.graphdata.my_userinfo_map));
                        alert("You cannot remove this dependency declaration from " 
                                + targetnode.key 
                                + " because you are not the owner and are not a delegate owner of " 
                                + targetnode.key + " and the status is not 'B'");
                        okay = false;
                    };

                    if(okay)
                    {
                        var sourcenode = nodes[selected_link.srcno];
                        instance.saveOneLinkRemoval(sourcenode.nativeid, targetnode.nativeid);
                        instance.actionlayout.methods.removeDirectedLink(instance.graphdata, sourcenode.nativeid, targetnode.nativeid);
                        selected_link = null;
                        instance.updateGraphDataTopologyValues(true);
                    }
                }
            }
        }
        catch(err)
        {
            console.error(err);
        }
    }
    
    function window_mousedown()
    {
        try
        {
            if(highlight_link_start_mouse_coord !== null)
            {
                //Give wiggle room for associating mousedown with a line
                var dist = instance.mycanvas.getDistanceOldCoordVsCanvasNow(highlight_link_start_mouse_coord);
                if(dist < link_clear_distance)
                {
                    selected_link = highlight_link;
                    highlight_link = null;
                    highlight_link_start_mouse_coord = null;
                    instance.redraw();
                }
            }
        }
        catch(err)
        {
            console.error(err);
        }
    };
    
    function window_mousemove()
    {
        try
        {
            if(highlight_link_start_mouse_coord !== null)
            {
                var dist = instance.mycanvas.getDistanceOldCoordVsCanvasNow(highlight_link_start_mouse_coord);
                if(dist > link_clear_distance)
                {
                    highlight_link = null;
                    highlight_link_start_mouse_coord = null;
                }
            }

            if(instance.infoballoon_flags.showing)
            {
                var dist = instance.mycanvas.getDistanceOldCoordVsPageNow(instance.infoballoon_flags.coordinates);
                if(dist > node_clear_distance)
                {
                    instance.hide_info_balloon();
                    cleared = true;
                }
            } else {
                //TODO --- remove this once I find reason for opacity not to clear
                instance.myinfoballoon.element.style("opacity", 0);
                //instance.hide_info_balloon();
            }
        }
        catch(err)
        {
            console.error("FAILED window_mousemove because " + err);
        }
    };

    //Setup the callbacks now if we have any.
    if (typeof my_action_map === 'undefined') 
    {
        console.log("WARNING: Did not define any form action maps because action map declaration is undefined!");
    } else {
        if (typeof my_field_map === 'undefined')
        {
            console.log("WARNING: Did not define any action maps because there is no field map!");
        } else {
            if(my_action_map.hasOwnProperty("hide_completed_branches_chk_id"))
            {
                $("#" +  my_action_map["hide_completed_branches_chk_id"]).click(function ()
                {
                    instance.graphdata.hide_completed_branches = $(this).is(":checked");
                    instance.actionlayout.methods.adjustForHideCompletedBranch(graphdata);
                    console.log("LOOK hide_completed_branches_chk_id!!!" + instance.graphdata.hide_completed_branches);
                    instance.resize();
                    instance.redraw();
                    instance.force.resume();
                });
            }
            
            if(my_action_map.hasOwnProperty("enablezoompan_chk_id"))
            {
                $("#" +  my_action_map["enablezoompan_chk_id"]).click(function ()
                {
                    enable_zoompan = $(this).is(":checked");
                    console.log("LOOK enablezoompan_chk_id!!!" + enable_zoompan);
                    bigfathom_util.d3v3_svg.enable_zoompan(instance.mycanvas, enable_zoompan);
                });
            }
            
            if(my_action_map.hasOwnProperty("txt_find_id"))
            {
                $("#" +  my_action_map["txt_find_id"]).on("keypress", function (e) 
                {            
                    if (e.keyCode == 13) {

                        // Cancel the default action on keypress event
                        e.preventDefault(); 
                        var findtxt = $('#' + my_action_map["txt_find_id"]).val();
                        if(!instance.jump2node(findtxt, true))
                        {
                            alert("Did not find " + findtxt);    
                        }
                    }
                });            
            }
            
            if(my_action_map.hasOwnProperty("reset_scale_btn_id"))
            {
                $("#" +  my_action_map["reset_scale_btn_id"]).click(function ()
                {
                    instance.jump2node('rootnode', false);
                });
            }
            if(my_action_map.hasOwnProperty("create_new_workitem_btn_id"))
            {
                $("#" +  my_action_map["create_new_workitem_btn_id"]).click(function ()
                {
                    instance.createNewWorkitem();
                });
            }
            if(my_action_map.hasOwnProperty("import_parts_btn_id"))
            {
                $("#" +  my_action_map["import_parts_btn_id"]).click(function ()
                {
                    instance.importPartsTemplate();
                });
            }
            if(my_action_map.hasOwnProperty("edit_workitem_btn_id"))
            {
                $("#" +  my_action_map["edit_workitem_btn_id"]).click(function ()
                {
                    console.log("LOOK TODO edit the workitem!!!");
                    //instance.editEditWorkitem();
                });
            }
            
            bigfathom_util.popup.setup(instance, my_action_map);
        }
    }

    ///////////////////////////////////////////////////////

    d3.select(window)
            .on("resize", resize)
            .on("keydown", keydown)
            .on("keyup", keyup)
            .on("mousedown", window_mousedown)
            .on("mousemove", window_mousemove);
    
    var findtxt = null;
    if(initial_commands.hasOwnProperty("jump2goalid"))
    {
        findtxt = "goal_" + initial_commands.jump2goalid;
    } 
    else if(initial_commands.hasOwnProperty("jump2taskid"))
    {
        findtxt = "task_" + initial_commands.jump2taskid;
    } 
    else if(initial_commands.hasOwnProperty("jump2workitemid"))
    {
        findtxt = initial_commands.jump2workitemid;
    }
    if(findtxt === null)
    {
        instance.jump2node('rootnode', false);
    }  else {
        if(!instance.jump2node(findtxt, true))
        {
            alert("Did not find on load " + findtxt);    
        }
    } 
    
    var my_data_refresher = null;
    var previous_project_edit_key = "NONE_YET";

    /**
     * Call this AFTER all the node and link information is up-to-date!
     */
    instance.updateGraphDataTopologyValues = function (redraw)
    {
        try
        {
            if(typeof redraw === 'undefined')
            {
                redraw = true;
            }
            var new_topoplogy = bigfathom_util.hierarchy_data.getWorkitemTopologyInfo(instance.graphdata); //Always call this
            instance.graphdata.assigned_nodeids = new_topoplogy.assigned_nodeids;
            instance.graphdata.unassigned_nodeids = new_topoplogy.unassigned_nodeids;
            instance.graphdata.completed_branch_rootids = new_topoplogy.completed_branch_rootids;
            instance.graphdata.incompleted_branch_rootids = new_topoplogy.incompleted_branch_rootids;

            //Clean up the show attributes if we are hiding completed branches
            if(instance.graphdata.hide_completed_branches)
            {
                for(var i=0; i<graphdata.nodes.length; i++)
                {
                    var nodedetail = graphdata.nodes[i];
                    //Only workitems have nativeid, check for this.
                    if(typeof nodedetail.nativeid !== "undefined")
                    {
                        if(bigfathom_util.hierarchy_data.hasTerminalStatus(nodedetail, instance.graphdata.status_cd_lookup))
                        {
                            nodedetail.show_node = false;
                        }
                    }
                }
            }

            if(redraw)
            {
                instance.resize();
                instance.redraw();
            };
        }
        catch(err)
        {
            console.error("FAILED updateGraphDataTopologyValues because " + err);
        }
    };

    instance.applyDataChanges = function (freshdata, map_brainstormid2wid, update_topology)
    {
        try
        {
            if(typeof update_topology === 'undefined')
            {
                update_topology = true;
            }
            instance.auto_datarefresh.markBlocked("applyDataChanges");
            instance.actionlayout.methods.updateWIDMastersForBrainstormConversions(instance.graphdata, map_brainstormid2wid);
            var needs_redraw = bigfathom_util.hierarchy_data.updateGraphdataWithChanges(instance.graphdata, freshdata, instance.actionlayout.methods);
            if(update_topology)
            {
                instance.updateGraphDataTopologyValues(needs_redraw);
            }
            instance.auto_datarefresh.markAllowed("applyDataChanges");
        }
        catch(err)
        {
            console.error("FAILED applyDataChanges because " + err);
        }
    };
    
    function getLatestDataChanges()
    {
        try
        {
            if(instance.auto_datarefresh.isBlocked() || dragging_started_node !== null)
            {
                //No refresh on this iteration, check again later.
                if(instance.auto_datarefresh.isBlocked())
                {
                    console.log("LOOK dastarefresh is blocked " + JSON.stringify(instance.auto_datarefresh.getInfo()));
                }
                console.log("Skipping automatic_page_refresh in getLatestDataChanges next check=" + bigfathom_util.data.defaultNewDataCheckInterval);
                my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
            } else {

                //Perform a refresh now.
                instance.auto_datarefresh.markBlocked("getLatestDataChanges");
                //console.log("LOOK get latest changes since " + instance.graphdata.refreshed_timestamp);

                var projectid = instance.graphdata.projectid;
                if(projectid == null || typeof projectid == 'undefined')
                {
                    console.log("DEBUG graphdata=" + JSON.stringify(instance.graphdata));
                    throw "Missing required projectid in graphdata!!!";
                }
                var grab_fullurl = bigfathom_util.data.getGrabDataUrl("hierarchy_updates",{
                        "projectid": projectid,
                        "previous_project_edit_key": previous_project_edit_key,
                        "previous_project_edit_timestamp": instance.graphdata.refreshed_timestamp
                    });
                var callbackActionFunction = function(callbackid, responseBundle)
                {
                    if(responseBundle !== null && responseBundle.data !== null)
                    {
                        var record = responseBundle.data.data;
                        previous_project_edit_key = record.most_recent_edit_key;
                        instance.graphdata.refreshed_timestamp = record.most_recent_edit_timestamp; //In sync with the server instead of local machine time.
                        if(record.has_newdata)
                        {
                            //Incorporate information from the server
                            var map_brainstormid2wid = record.map_brainstormid2wid;
                            instance.applyDataChanges(record.newdata, map_brainstormid2wid);
                        }
                    }

                    //Setup for another check to happen in a little while
                    instance.auto_datarefresh.markAllowed("getLatestDataChanges");
                    my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
                };
                var callbackid = "latestdata";
                if(previous_project_edit_key !== null)
                {
                    callbackid = callbackid + "_" + previous_project_edit_key;
                }
                bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackActionFunction, callbackid);
            }
        }
        catch(err)
        {
            console.error("FAILED getLatestDataChanges because " + err);
        }
    };
    
    my_data_refresher = setTimeout(getLatestDataChanges, bigfathom_util.data.defaultNewDataCheckInterval);
    
    //console.log("LOOK previous_refresh_timestamp=" + instance.graphdata.refreshed_timestamp);
    //console.log("Done hierarchy setup stuff!!!");
    //console.log("LOOK Done hierarchy setup stuff instance.graphdata.linksbundle=" + JSON.stringify(instance.graphdata.linksbundle));
    //console.log("LOOK Done hierarchy setup stuff instance.graphdata.linksbundle.link_by_offset=" + JSON.stringify(instance.graphdata.linksbundle.link_by_offset));
    console.log("Completed dependency diagram setup!");
    return instance;
};

