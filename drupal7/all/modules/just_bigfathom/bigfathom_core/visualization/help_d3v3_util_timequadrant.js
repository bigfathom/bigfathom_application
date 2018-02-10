/* 
 * Functions for working with timequadrant display
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
if(!bigfathom_util.hasOwnProperty("timequadrant"))
{
    //Create the object property because it does not already exist
    bigfathom_util.timequadrant = {
        "version": "20160828.1",
        "default_workitem_opacity":.66
    };
}
  
/**
 * Create a simulation as simply as possible
 */
bigfathom_util.timequadrant.createEverything = function (canvas_container_id, my_action_map, my_field_map, projectid)
{
    var start_simulation = true;
    var dataname = "timequadrant_mapping";
    var full_grab_data_url = bigfathom_util.data.getGrabDataUrl(dataname, {projectid: projectid});
    d3.json(full_grab_data_url, function (rawdata)
    {
        if(rawdata === null)
        {
            throw "ERROR: No data returned from " + full_grab_data_url + "!";
        }
        
        //var scrollto_elem = $("ul.tabs");
        var scrollto_y = null; //scrollto_elem.offset().top;
        var pagetweaks = {
                'scrollto_y': scrollto_y
        };        
        
        var callbacks = {};
        var allow_userzoompan = false;
        var zlayers = 2;
        var mycanvas = bigfathom_util.d3v3_svg.createCanvas(canvas_container_id, null, null, allow_userzoompan, callbacks, zlayers, pagetweaks);

        mycanvas["background_layer"] = mycanvas.zlayers[0];
        mycanvas["movable_layer"] = mycanvas.zlayers[1];
        
        var actionlayout = bigfathom_util.env.quadrant.manager(mycanvas);
        var container_attribs = actionlayout.methods.getAllContainerAttribs();

        var graphdata = bigfathom_util.timequadrant_data.initializeGraphContainerData(container_attribs);
        var actorbundle = bigfathom_util.timequadrant_data.getActorNodeBundle(container_attribs, rawdata);
        
        graphdata.projectid = projectid;
        
        //The movable nodes MUST be the first elements in the nodes array!!!!
        graphdata.nodes = actorbundle.nodes;
        graphdata.last_node_offset = actorbundle.nodes.length - 1;
        graphdata.nodes.push(graphdata.background[0]);

        var manager = bigfathom_util.timequadrant.manager(mycanvas, container_attribs, graphdata, my_action_map, my_field_map);
        if(start_simulation)
        {
            manager.force.start();
        }

        return manager;
    });
};

bigfathom_util.timequadrant.manager = function (mycanvas, container_attribs, graphdata, my_action_map, my_field_map)
{
    var instance = {id:"timequadrant.manager.v" + bigfathom_util.timequadrant.version};
    instance.graphdata = graphdata;
    instance.container_attribs = container_attribs;
    instance.mycanvas = mycanvas;
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

    var quads_coordinates = instance.container_attribs.corefacts.visible_coordinates;   //TODO make this just the quadrant area
    
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
                //TODO compute position!!!!
                //console.log("LOOK todo compute position for " + o.key + " currently at x=" + o.x);
                o.x = 100 + 25*i;
                o.y = 100 + 20*i;
                /*
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
              */
            }
          });
        instance.node_background_sel.attr("transform", function(d) {
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
        instance.node_background_sel = instance.mycanvas.background_layer.selectAll("g.custom").filter("g.quadrant")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"rect","null"), function (d) {
                            return d.key;
                        });
        /*
        instance.node_background_sel = instance.mycanvas.background_layer.selectAll("g.rect")
            .data(nodefilter.getAllIncludedNoneExcluded(instance.graphdata.nodes,"rect",null), function (d) {
                            return d.key;
                        });
        */
       
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

        instance.node_background_sel.enter().joinForceNodeShapes(instance.shape_manager,"custom");
        instance.node_background_sel.exit().remove();
        
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

    return instance;
};

