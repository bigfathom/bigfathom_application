/*
 * Some helpers for our svg visualizations
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
    console.log("Created bigfathom_util");
}
if(!bigfathom_util.hasOwnProperty("d3v3_svg"))
{
    //Create the object property because it does not already exist
    bigfathom_util.d3v3_svg = {
                            "version": "20161022.1",
                            "attention_circle" : {
                                    "radius":90,
                                    "duration":987,
                                    "delay":44
                                },
                            "height_trigger":500,
                            "canvases": {}
                         };
}

/**
 * Rescale using D3 values
 */
bigfathom_util.d3v3_svg.applyd3rescale = function (canvas, duration)
{
    if(canvas.enable_zoompan)
    {
        d3.event.sourceEvent.stopPropagation();
        var trans = d3.event.translate;
        var scale = d3.event.scale;
        bigfathom_util.d3v3_svg.rescale(canvas, trans, scale, duration);
    }
};

/**
 * Zoom and pan the canvas
 */
bigfathom_util.d3v3_svg.rescale = function (canvas, trans, scale, duration)
{
    if(canvas.enable_zoompan)
    {
        if (typeof trans === 'undefined' || trans === null)
        {
            trans = [0, 0];
        };
        if (typeof scale === 'undefined' || scale === null)
        {
            scale = 1;
        };
        if (typeof duration === 'undefined' || duration === null)
        {
            duration = 100;
        };

        canvas.prev_scale = scale;
        canvas.prev_trans = trans;

        //Make sure D3 has the same values we now have in SVG
        canvas.zoom.translate(trans);
        canvas.zoom.scale(scale);
        
        //Update SVG
        canvas.zoom_area.transition()
                .duration(duration)
                .attr("transform",
                    "translate(" + trans + ")"
                    + " scale(" + scale + ")"
                    );
    
        //Let everyone know we are done with the rescale operation
        if(canvas.callbacks.hasOwnProperty("after_rescale"))
        {
            canvas.callbacks.after_rescale();
        }
    }
};

bigfathom_util.d3v3_svg.move_drag_line = function (mycanvas, d)
{
    if(mycanvas.layers_elem == null)
    {
        console.log("DEBUG why is mycanvas.layers_elem null??????");
        return;
    }
    var mouse = d3.mouse(mycanvas.layers_elem);

    var real_x1 = d.x;
    var real_y1 = d.y;

    var real_x2 = mouse[0];
    var real_y2 = mouse[1];

    mycanvas.drag_line
            .attr("class", "drag_line")
            .attr("x1", real_x1)
            .attr("y1", real_y1)
            .attr("x2", real_x2)
            .attr("y2", real_y2); 
};

bigfathom_util.d3v3_svg.enable_zoompan = function (canvas, enable)
{
    canvas.enable_zoompan = enable;
};

/**
 * Create largest possible canvas inside element specified and return details
 */
bigfathom_util.d3v3_svg.createCanvas = function (element_id
                    , pointer_events
                    , canvas_name
                    , enable_zoompan
                    , callbacks
                    , zlayer_count, pagetweaks, popup_balloons)
{
    if (typeof pointer_events === 'undefined')
    {
        pointer_events = 'all';
    }
    if (typeof canvas_name === 'undefined' || canvas_name === null)
    {
        canvas_name = "svg_main";
    }
    if (typeof enable_zoompan === 'undefined' || enable_zoompan === null)
    {
        enable_zoompan = false;
    }
    if (typeof callbacks === 'undefined' || callbacks === null)
    {
        callbacks = {};
    }
    if (typeof zlayer_count === 'undefined' || zlayer_count === null)
    {
        zlayer_count = 1;
    }
    if(zlayer_count < 1)
    {
        throw "There must be at least one zlayer!";
    }
    if (typeof pagetweaks === 'undefined' || pagetweaks === null)
    {
        pagetweaks = {
                'scrollto_y': null
            ,   'reduce_canvas_height': 0
        };
    } else {
        if(!pagetweaks.hasOwnProperty("scrollto_y"))
        {
            pagetweaks['scrollto_y'] = null;
        }
        if(!pagetweaks.hasOwnProperty("reduce_canvas_height"))
        {
            if(pagetweaks.scrollto_y === null)
            {
                pagetweaks['reduce_canvas_height'] = 0;
            } else {
                pagetweaks['reduce_canvas_height'] = 50; //pagetweaks.scrollto_y;
            }
        }
    }
    if (typeof popup_balloons === 'undefined')
    {
        popup_balloons = {'main':{'classname':'info-balloon-wide'}};
    }
    var el   = document.getElementById(element_id); // or other selector like querySelector()
    var rect = el.getBoundingClientRect(); // get the bounding rectangle
    var canvas_width = rect.width;
    var canvas_height = (rect.height > 500 ? rect.height : rect.width / 2) - pagetweaks.reduce_canvas_height;
    var myscale = 1;
    var mytrans = [0,0];
    
    //Now make sure the portal window is also the right size!
    el.style["height"] = canvas_height + "px";

    bigfathom_util.d3v3_svg.canvases[canvas_name] =  {
                                          'w':canvas_width
                                        , 'h':canvas_height
                                        , 'container':el
                                        , 'element_id': element_id
                                        , 'canvas_name': canvas_name
                                        , 'callbacks' : callbacks
                                        , 'prev_scale': myscale //TODO REMOVE REDUNDANT
                                        , 'prev_trans': mytrans //TODO REMOVE REDUNDANT
                                    };
                                    
    var thiscanvas = bigfathom_util.d3v3_svg.canvases[canvas_name];

    var svg_root = d3.select('#' + element_id)  
        .append("svg")        
        .attr("id", canvas_name)
        .attr("width","100%")
        .attr("height", canvas_height + "px");  //100%");

    if(pointer_events !== null)
    {
        svg_root.attr("pointer-events", pointer_events);
    }

    var remember_enable_zoompan = enable_zoompan;
    thiscanvas['enable_zoompan'] = false;

    var zoom = d3.behavior.zoom();
    thiscanvas['zoom'] = zoom;
    svg_root.call(zoom.on("zoom", function () {
                bigfathom_util.d3v3_svg.applyd3rescale(
                          thiscanvas
                        , 0);
                }))
                .on("dblclick.zoom", null);

    var id;
    var layerhandle;
    var holder = svg_root.append("svg:g").attr("id","zlayers");
    
    var zlayers = [];
    var zBottom = holder.append("g").attr("id","zBottom");
    for(var zidx = 0; zidx < zlayer_count; zidx++)
    {
        id = "z" + zidx;
        layerhandle = holder.append("g").attr("id",id);
        zlayers.push(layerhandle);
    }
    var zTop = holder.append("g").attr("id","zTop");
    
    // line displayed when dragging new nodes
    var drag_line = zTop.append("line")
        .attr("class", "drag_line")
        .attr("x1", 0)
        .attr("y1", 0)
        .attr("x2", 0)
        .attr("y2", 0);

    // Circle displayed to bring attention to a node
    var attention_circle_element = zTop.append("circle")
        .attr("class", "attention_circle")
        .attr("r", 0)
        .attr("cx", 0)
        .attr("cy", 0);
    var attention_circle_obj = {
            'element': attention_circle_element, 
            'target_key': null,
            'radius': bigfathom_util.d3v3_svg.attention_circle.radius,
            'duration':bigfathom_util.d3v3_svg.attention_circle.duration,
            'delay':bigfathom_util.d3v3_svg.attention_circle.delay
        };

    thiscanvas['svg_root'] = svg_root;
    thiscanvas['zoom_area'] = holder;
    thiscanvas['zlayers'] = zlayers; //Array of all the user zlayers
    thiscanvas['layers_elem'] = holder.node(); //The element holding the layers
    thiscanvas['zBottom'] = zBottom; //Bottom layer elem
    thiscanvas['zTop'] = zTop; //Top layer elem
    thiscanvas['drag_line'] = drag_line;
    thiscanvas['attention_circle'] = attention_circle_obj;
    thiscanvas['enable_zoompan'] = remember_enable_zoompan;
    thiscanvas['getDistance'] = function(x1,y1,x2,y2)
    {
        return Math.sqrt( (x2-x1) * (x2-x1) + (y2-y1)*(y2-y1) );
    };
    thiscanvas['getCanvasMouseCoordinates'] = function()
    {
        if(thiscanvas.layers_elem === null)
        {
            console.log("DEBUG why is thiscanvas.layers_elem null??????");
            return;
        }
        return d3.mouse(thiscanvas.layers_elem);
    };
    thiscanvas['getDistanceOldCoordVsCanvasNow'] = function(old_coordinates)
    {
        var oldx = old_coordinates[0];
        var oldy = old_coordinates[1];
        var new_coordinates = thiscanvas.getCanvasMouseCoordinates();
        var newx = new_coordinates[0];
        var newy = new_coordinates[1];        
        return thiscanvas.getDistance(oldx, oldy, newx, newy);
    };
    thiscanvas['getPageMouseCoordinates'] = function()
    {
        return [d3.event.pageX, d3.event.pageY];
    };
    thiscanvas['getDistanceOldCoordVsPageNow'] = function(old_coordinates)
    {
        var oldx = old_coordinates[0];
        var oldy = old_coordinates[1];
        var new_coordinates = thiscanvas.getPageMouseCoordinates();
        var newx = new_coordinates[0];
        var newy = new_coordinates[1];        
        return thiscanvas.getDistance(oldx, oldy, newx, newy);
    };

    /**
     * Returns canvas edge coordinates of the visible window adjusted
     * for translation and scale
     */
    thiscanvas['getVisibleRectangleFacts'] = function()
    {
        var canvas_trans = thiscanvas.zoom.translate();
        var canvas_scale = thiscanvas.zoom.scale();
        var trans_x = canvas_trans[0]; //thiscanvas.prev_trans[0];
        var trans_y = canvas_trans[1]; //thiscanvas.prev_trans[1];
        var x2 = (thiscanvas.w - trans_x)/canvas_scale; //thiscanvas.prev_scale;
        var y2 = (thiscanvas.h - trans_y)/canvas_scale; //thiscanvas.prev_scale;
        var x1 = (0 - trans_x)/canvas_scale; //thiscanvas.prev_scale;
        var y1 = (0 - trans_y)/canvas_scale; //thiscanvas.prev_scale;
        var coordinates = {
            'x1':x1,'y1':y1,
            'x2':x2,'y2':y2,
            'scale':canvas_scale, //thiscanvas.prev_scale,
            'trans':canvas_trans //thiscanvas.prev_trans
        };
        return coordinates;
    };
    
    //Create all the balloon elements now
    for(var balloon_name in popup_balloons)
    {
        var one_balloon = popup_balloons[balloon_name];
        one_balloon["element"] = d3.selectAll("body").append("div")   
            .attr("class", one_balloon.classname)               
            .style("opacity", 0);
    };
    thiscanvas['popup_balloons'] = popup_balloons;
    thiscanvas['showInfoBalloon'] = function(balloon_name, data_rows, coordinates)
    {
        if (typeof coordinates === 'undefined')
        {
            coordinates = thiscanvas.getPageMouseCoordinates();
        }
        if(!thiscanvas.popup_balloons.hasOwnProperty(balloon_name))
        {
            throw "We do not have a balloon named '" + balloon_name + "' in " + JSON.stringify(thiscanvas.popup_balloons);
        }
        var myinfoballoon = thiscanvas.popup_balloons[balloon_name];
        
        var x = coordinates[0];
        var y = coordinates[1];
        var dataobj;
        if (typeof data_rows === 'undefined')
        {
            //console.log("LOOK tip has no data");
            dataobj = {'ERROR':'NO ROWS PROVIDED'};
        } else {
            //console.log("LOOK tip " + JSON.stringify(data_rows));
            dataobj = data_rows;
        }

        var markup = '';
        for(var id in dataobj)
        {
            var value = data_rows[id];
            if(markup !== '')
            {
                markup = markup + '<br>';
            }
            markup = markup + "<strong>" + id + ":</strong> <span>" + value + "</span>";
        }

        myinfoballoon.element.transition()        
            .duration(200)      
            .style("opacity", .9);      
        myinfoballoon.element.html(markup)  
            .style("left", (x + 20) + "px")     
            .style("top", (y - 28) + "px");             
    };
    thiscanvas['showing_context_menu'] = false;
    thiscanvas['showContextMenu'] = function(selector_tx, menu_items, menu_callback)
    {
        d3.event.preventDefault();
        $.contextMenu({
            'selector': selector_tx,//'#svg_main', 
            'build': function(trigger, e) {
                // this callback is executed every time the menu is to be shown
                // its results are destroyed every time the menu is hidden
                // e is the original contextmenu event, containing e.pageX and e.pageY (amongst other data)
                return {
                    'callback': menu_callback,
                    'items': menu_items
                };
            }
        });
    
    };
    if(pagetweaks.scrollto_y !== null)
    {
        $("html, body").animate({
                'scrollTop': pagetweaks.scrollto_y + 'px'
            }, 'fast');
    };

    thiscanvas.attention_circle['move'] = function(x, y)
    {
        if(thiscanvas.attention_circle.target_key !== null)
        {
            thiscanvas.attention_circle.element
                .attr("id", thiscanvas.attention_circle.target_key + "_attention_circle")
                .attr("class", "attention_circle")
                .attr("cx", x)
                .attr("cy", y)
                .attr("r", thiscanvas.attention_circle.radius)
                    .transition()
                        .duration(thiscanvas.attention_circle.duration)
                            .delay(thiscanvas.attention_circle.delay)
                                .attr("r",0);
        }
    };
    
    thiscanvas.attention_circle['clear'] = function()
    {
        if(thiscanvas.attention_circle.target_key !== null)
        {
            thiscanvas.attention_circle.element
                .attr("id", thiscanvas.attention_circle.target_key + "_attention_circle")
                .attr("class", "attention_circle")
                .attr("cx", 0)
                .attr("cy", 0)
                .attr("r", 0);
        }
        thiscanvas.attention_circle.target_key = null;
    };
    
    thiscanvas.attention_circle['activate'] = function(key, x, y)
    {
        thiscanvas.attention_circle.clear();
        if(key !== null)
        {
            thiscanvas.attention_circle.target_key = key;
            thiscanvas.attention_circle.element
                .attr("id", key + "_attention_circle")
                .attr("class", "attention_circle")
                .attr("cx", x)
                .attr("cy", y)
                .attr("r", thiscanvas.attention_circle.radius)
                    .transition()
                        .duration(thiscanvas.attention_circle.duration)
                            .delay(thiscanvas.attention_circle.delay)
                                .attr("r",0);
        }
    };
    return thiscanvas;
};

bigfathom_util.d3v3_svg.refreshCanvasDimensions = function (mycanvas)
{
    //var canvas_name = mycanvas.canvas_name;
    var element_id = mycanvas.element_id;
    var el   = document.getElementById(element_id); // or other selector like querySelector()
    var rect = el.getBoundingClientRect(); // get the bounding rectangle
    var canvas_width = rect.width;
    var canvas_height = rect.height > bigfathom_util.d3v3_svg.height_trigger ? rect.height : rect.width / 2;
    mycanvas.w = canvas_width;
    mycanvas.h = canvas_height;
};

bigfathom_util.d3v3_svg.addArrows = function (mycanvas, all_mymarker_detail)
{
    var svg_grouping = mycanvas.zBottom; //This is to group shapes together
    var mymarker_info;
    for(var mymarker_name in all_mymarker_detail)
    {
        mymarker_info = all_mymarker_detail[mymarker_name];
        svg_grouping.append("defs").append("marker")
                .attr("id", mymarker_info.attr.id)
                .attr("viewBox", mymarker_info.attr.viewBox)
                .attr("refX", mymarker_info.attr.refX)   //I think this must MATCH the circle radius
                .attr("refY", mymarker_info.attr.refY)
                .attr("markerWidth", mymarker_info.attr.markerWidth)
                .attr("markerHeight", mymarker_info.attr.markerHeight)
                .attr("orient", mymarker_info.attr.orient)
                .append("path")
                .attr("d", mymarker_info.path.d);
    }    
};

