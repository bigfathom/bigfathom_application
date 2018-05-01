/* 
 * Library of utility functions for working with D3 svg shapes
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
if(!bigfathom_util.hasOwnProperty("shapes"))
{
    //Create the object property because it does not already exist
    bigfathom_util.shapes = {version: "20180430.1"};
}

//see shape names from http://fiddle.jshell.net/994XM/9/
bigfathom_util.shapes.core = {
    error_icon: {type:"path", attr:{fill:"red", d:"M150 0 L75 200 L150 150 L225 200 Z"}},
    error_icon_large: {type:"path", attr:{fill:"red", d:"M150 0 L75 200 L150 150 L225 200 Z"}},
    defaultshapecolors: {'unknown':{'stroke':'black','fill':'blue'}, 
                    'goal':{'stroke':'black','fill':'green'}, 
                    'task': {'stroke':'black','fill':'gray'}}
    };

/**
 * Simply show all existing shapes in the canvas area
 */
bigfathom_util.shapes.showAll = function (canvas_container_id)
{
    var instance = {};
    instance["mycanvas"] = bigfathom_util.d3v3_svg.createCanvas(canvas_container_id);
    var graphdata = {nodes: [], linksbundle:{}};
    var rows = 4;
    var cols = 5;
    var dx = instance.mycanvas.w / cols;
    var dy = instance.mycanvas.h / rows;
    var curx = dx/4;
    var cury = dy/4;
    var curcol = 1;
    var currow = 1;
    var idx = 0;
    var shapename = null;
    
    //Get core shapes
    for(shapename in bigfathom_util.shapes.core)
    {
        graphdata.nodes.push({
                  type: shapename
                , name: shapename
                , label: "core." + shapename
                , x: curx 
                , y: cury 
                , is_drag_source: true
                , is_drag_target: false
                , assignment: null
                , targetlevel: null //redundant with hierarchy_level???
                , fixed: true
                , custom_detail: null
                , index: idx++
            });
            
        //Compute new positions    
        curcol++;
        if(curcol > cols)
        {
            curcol = 1;
            currow++;
            curx = dx;
            cury = cury + dy;
        } else {
            curx = curx + dx;
        }
    }
    
    //Get handy shapes
    for(shapename in bigfathom_util.shapes.lib.handy)
    {
        graphdata.nodes.push({
                  type: shapename
                , name: shapename
                , label: "handy." + shapename
                , x: curx 
                , y: cury 
                , is_drag_source: true
                , is_drag_target: false
                , assignment: null
                , targetlevel: null //redundant with hierarchy_level???
                , fixed: true
                , custom_detail: null
                , index: idx++
            });
            
        //Compute new positions    
        curcol++;
        if(curcol > cols)
        {
            curcol = 1;
            currow++;
            curx = dx;
            cury = cury + dy;
        } else {
            curx = curx + dx;
        }
    };
    
    instance["graphdata"] = graphdata;
    
    //Now plot the shapes.
    instance.tick = function (e) {

        var k = e.alpha;

        instance.node_sel.attr("transform", function(d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

    };

    instance.force = d3.layout.force()
        .nodes(instance.graphdata.nodes)
        .size([instance.mycanvas.w, instance.mycanvas.h])
        .on("tick", instance.tick)
        .charge(-200)
        .gravity(.005);

    if(instance.graphdata.linksbundle.hasOwnProperty("link_by_offset"))
    {
        instance.link_sel = instance.mycanvas.zlayers[0].selectAll("g.node")
            .data(instance.graphdata.linksbundle.link_by_offset);
    }

    instance.node_sel = instance.mycanvas.zlayers[0].selectAll("g.node")
        .data(instance.graphdata.nodes);

    var my_redraw = function()
    {
        var my_data = {nodes: instance.graphdata.nodes, linksbundle: instance.graphdata.linksbundle};
        var my_node_handlers = {};
        var my_link_handlers = {};

        var my_overrides = {};
        my_overrides["styles"] = {};
        my_overrides["attribs"] = {};

        //Simply map this data name to a known existing name
        my_overrides["shape_name_alias"] = {};

        //Attributes that only apply to labels    
        my_overrides["label_attribs"] = {};
        my_overrides["label_styles"] = {};

        var my_handlers = {'nodes': my_node_handlers, 'links': my_link_handlers};
        var shape_manager = bigfathom_util.shapes.getManager(my_data, my_handlers, my_overrides);

        instance.link_sel = instance.link_sel.data(instance.graphdata.linksbundle.link_by_offset);
        instance.link_sel.exit().remove();
        instance.link_sel.enter().joinForceLinkShapes(shape_manager);

        instance.node_sel = instance.node_sel.data(instance.graphdata.nodes);
        instance.node_sel.exit().remove();
        instance.node_sel.enter().joinForceNodeShapes(shape_manager);
    };

    instance.mycanvas.zlayers[0].style("opacity", 1e-6)
      .transition()
        .duration(1000)
        .style("opacity", 1);

    instance.redraw = function ()
    {
        my_redraw();
    };

    instance.redraw();
    instance.force.start();
    
    console.log("Done show all shapes!!!");
    
};

/**
 * Return a shape manager
 */
bigfathom_util.shapes.getManager = function (graphdata, handlers, overrides)
{
    var manager = {
            'graphdata' : graphdata,
            'overrides' : overrides
        };
    if(handlers.hasOwnProperty("nodes"))
    {
        manager["node_handlers"] = handlers.nodes;
    } else {
        manager["node_handlers"] = {};
    }
    if(handlers.hasOwnProperty("links"))
    {
        manager["link_handlers"] = handlers.links;
    } else {
        manager["link_handlers"] = {};
    }

    manager["attrib_overrides"] = manager.overrides.hasOwnProperty("attribs") ? manager.overrides.attribs : {};    
    manager["style_overrides"] = manager.overrides.hasOwnProperty("styles") ? manager.overrides.styles : {};  
    manager["custom_shape_map"] = manager.overrides.hasOwnProperty("custom_shape_map") ? manager.overrides.custom_shape_map : {};    
    manager["shape_name_alias"] = manager.overrides.hasOwnProperty("shape_name_alias") ? manager.overrides.shape_name_alias : {};
    manager["label_attrib_overrides"] = manager.overrides.hasOwnProperty("label_attribs") ? manager.overrides.label_attribs : {};    
    manager["label_style_overrides"] = manager.overrides.hasOwnProperty("label_styles") ? manager.overrides.label_styles : {};    
    
    /**
     * The built-in shapes
     */
    manager["getD3Symbol"] = function (d)
    {
        var svgpath;

        svgpath = d3.svg.symbol()
            .size(function () {
                var size = null;
                if(manager.attrib_overrides.hasOwnProperty("size"))
                {
                    size = manager.attrib_overrides.size(d);
                }
                if(size === null)
                {
                    if(d.hasOwnProperty("size") && d.size)
                    {
                        //Use the literal value
                        size = d.size;
                    } else {
                        //Try to compute it from other clues
                        if(d.hasOwnProperty("r") && d.r) 
                        {
                            //Assume this is a circle
                            size = Math.PI * Math.pow(d.r, 2);
                        } else if(d.hasOwnProperty("height") && d.height 
                                && d.hasOwnProperty("width") && d.width) {
                            size = d.height * d.width;
                        } else {
                            if(d.type === 'task')
                                size = 100;
                            else if(d.type === 'goal') 
                                size = Math.PI * Math.pow(15, 2);
                            else if(d.type === 'person')
                                size = Math.PI * Math.pow(15, 2);
                            else if(d.type === 'group')
                                size =  Math.PI * Math.pow(15, 2);
                            else if(d.type === 'prole' || d.type === 'srole')
                                size = 100;
                            else
                                size = 500;
                        }
                    }
                }
                return size;
            })
            .type(function () {
                var d3_symbol_type = null;
                if(manager.attrib_overrides.hasOwnProperty("d3_symbol_type"))
                {
                    d3_symbol_type = manager.attrib_overrides.d3_symbol_type(d);
                }
                if(d3_symbol_type === null)
                {
                    if(d.type === 'task')
                    {
                        d3_symbol_type = 'diamond';
                    } else
                    if(d.type === 'prole' || d.type === 'srole')
                    {
                        d3_symbol_type = 'triangle-up';
                    } else {
                        d3_symbol_type = 'circle';
                    }
                }
                return d3_symbol_type;
            })();
        return svgpath;
    };

    /**
     * Return the shape definition
     * .attr.d = path
     * .attr.fill = fill color
     */
    manager["getShape"] = function (node_data)
    {
        var shapedef = {};
        var svgpath;
        var object_type = node_data.type;

        if(manager.shape_name_alias.hasOwnProperty(object_type))
        {
            object_type = manager.shape_name_alias[object_type];
        }
        if(manager.custom_shape_map.hasOwnProperty(object_type))
        {
            shapedef = manager.custom_shape_map[object_type];
            shapedef.attr["found"] = true;
            svgpath = shapedef.attr.d;
        } else if(bigfathom_util.shapes.lib.handy.hasOwnProperty(object_type)) 
        {
            shapedef = bigfathom_util.shapes.lib.handy[object_type];
            shapedef.attr["found"] = true;
            svgpath = shapedef.attr.d;
        } else {
            var built_in_shape = (object_type === "circle" 
                    || object_type === "diamond"
                    || object_type === "square"
                    || object_type === "triangle-up"
                    || object_type === "triangle-down");

            built_in_shape = built_in_shape || (object_type === "goal" 
                    || object_type === "task"
                    || object_type === "person"
                    || object_type === "group"
                    || object_type === "srole" || object_type === "prole");

            if(built_in_shape)
            {
                var fill;
                if(object_type === 'goal')
                {
                    fill =  '#66FF66';
                } else if(object_type === 'task') {
                    fill =  '#FFCC00';
                } else if(object_type === 'person') {
                    fill =  '#0066FF';
                } else if(object_type === 'prole') {
                    fill =  '#58ACFA';
                } else if(object_type === 'srole') {
                    fill =  '#0B4C5F';
                } else if(object_type === 'group') {
                    fill =  '#8258FA';
                } else {
                    fill = "red";
                }
                svgpath = manager.getD3Symbol(node_data);
                shapedef["attr"] = {found: true};
                shapedef.attr["d"] = svgpath;
                shapedef.attr["fill"] = fill;
            } else {
                if(object_type === "rect")
                {
                    //console.log("LOOK CREATE A RECT FOR data= " + JSON.stringify(node_data));
                    var p1 = "0 0";
                    var p2 = "0 " + node_data.height;
                    var p3 = node_data.width + " " + node_data.height;
                    var p4 = node_data.width + " 0";
                    svgpath = "M" + p1 + " L" + p2 + " L" + p3 + " L" + p4 + " Z";
                    //console.log("LOOK svg = " + svgpath);
                    shapedef["attr"] = {found: true};
                    shapedef.attr["d"] = svgpath;
                    shapedef.attr["fill"] = "brown";
                } else {
                    //Return our default shape
                    svgpath = bigfathom_util.shapes.core.error_icon.attr.d;
                    shapedef["attr"] = {found: false};
                    shapedef.attr["d"] = svgpath;
                    shapedef.attr["fill"] = "red";
                }
            }
        }

        return shapedef;
    };

    manager["buildDOMElements"] = function (selection, typename)
    {

        var style_overrides = manager.style_overrides;
        
        selection.html("")
                .append("path")    //Hardcoded as path because I don't know how to set this value dynamically based on join
                .attr('class', function(d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       return d.type + "_symbol_background";
                   }
                   return null;
                })
                .attr('fill', function(d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       return myshape.background.fill;
                   }
                   return null;
                })
                .style('fill-opacity', function(d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       return myshape.background.opacity;
                   }
                   return null;
                })
                .style("stroke", function (d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       return myshape.background.stroke;
                   }
                   return null;
                })
                .attr('d', function(d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       return myshape.background.d;
                   }
                   return null;
                })
                .attr('transform', function(d) {
                   var myshape = manager.getShape(d);
                   if(myshape !== null && myshape.hasOwnProperty("background")) 
                   {
                       if(myshape.background.hasOwnProperty("translate"))
                       {
                           var t = myshape.background.translate;
                           return "translate(" + t.x + "," + t.y + ")";
                       }
                   }
                   return null;
                });
                
        selection
                .append("path")    //Hardcoded as path because I don't know how to set this value dynamically based on join
                .attr("id", function (d) {
                    //We do NOT apply lots of names to the path element
                    if(d.hasOwnProperty("nativeid"))
                    {
                        if(d.hasOwnProperty("is_candidate") && d.is_candidate)
                        {
                            return "c" + d.type + "_" + d.nativeid;
                        } else {
                            return d.type + "_" + d.nativeid;
                        }
                    }
                })
                .attr("class", function (d) {
                    return typename;;
                })
                .classed("background", function (d) {
                    return d.hasOwnProperty("is_background") && d.is_background;
                })
                .classed("movable", function (d) {
                    return !d.hasOwnProperty("is_movable") || d.is_movable === true;
                })
                .attr("d", function (d) {
                        if(d.hasOwnProperty("custom_detail") && d.custom_detail !== null)
                        {
                            var myshape = manager.getShape(d.custom_detail.target);
                            return myshape.attr.d;
                        } else {
                            return manager.getShape(d).attr.d;
                        }
                    })
                .style("fill-opacity", function (d) {
                        var opacity = null;
                        if(d.hasOwnProperty("fill_opacity"))
                        {
                            opacity = d.fill_opacity;
                        }
                        if(style_overrides.hasOwnProperty("fill_opacity"))
                        {
                            opacity = style_overrides.fill_opacity(d);
                        }
                        if(d.hasOwnProperty("custom_detail") 
                                && d.custom_detail !== null 
                                && d.custom_detail.target.hasOwnProperty("fill_opacity"))
                        {
                            opacity = d.custom_detail.target.fill_opacity;
                        }
                        if(opacity === null)
                        {
                            opacity = 1;
                        }
                        return opacity;
                    })
                .style("fill", function (d) {
                        var fill = null;
                        if(d.hasOwnProperty("fill"))
                        {
                            fill = d.fill;
                        }
                        if(style_overrides.hasOwnProperty("fill"))
                        {
                            fill = style_overrides.fill(d);
                        }
                        if(d.hasOwnProperty("custom_detail") 
                                && d.custom_detail !== null 
                                && d.custom_detail.target.hasOwnProperty("fill_color"))
                        {
                            fill = d.custom_detail.target.fill_color;
                        }
                        if(fill === null)
                        {
                            if(d.hasOwnProperty("custom_detail") && d.custom_detail !== null)
                            {
                                var myshape = manager.getShape(d.custom_detail.target);
                            } else {
                                var myshape = manager.getShape(d);
                            }
                            if(myshape !== null && myshape.attr.hasOwnProperty("fill") && myshape.attr.found) {
                                fill = myshape.attr.fill;
                            } else {
                                fill = "red";
                            }
                        }
                        return fill;
                    })
                .style("stroke", function (d) {
                        var stroke_color = null;
                        if(d.hasOwnProperty("stroke"))
                        {
                            stroke_color = d.stroke;
                        }
                        if(style_overrides.hasOwnProperty("stroke"))
                        {
                            stroke_color = style_overrides.stroke(d);
                        }
                        if(d.hasOwnProperty("custom_detail") 
                                && d.custom_detail !== null 
                                && d.custom_detail.target.hasOwnProperty("stroke_color"))
                        {
                            stroke_color = d.custom_detail.target.stroke_color;
                        }
                        if(stroke_color === null)
                        {
                            if(d.hasOwnProperty("custom_detail") && d.custom_detail !== null)
                            {
                                var myshape = manager.getShape(d.custom_detail.target);
                            } else {
                                var myshape = manager.getShape(d);
                            }
                            if(myshape !== null && myshape.attr.hasOwnProperty("stroke") && myshape.attr.found) {
                                stroke_color = myshape.attr.stroke;
                            } else {
                                stroke_color = "white";
                            }
                        }
                        return stroke_color;
                    })
                .attr('transform', function(d) {
                    var myshape = manager.getShape(d);
                    var ar = [];
                    if(myshape !== null)
                    {
                        if(myshape.attr.hasOwnProperty("translate")) 
                        {
                             var t = myshape.attr.translate;
                             ar.push("translate(" + t.x + "," + t.y + ")");
                        }
                        if(myshape.attr.hasOwnProperty("rotate")) 
                        {
                             var t = myshape.attr.rotate;
                             ar.push("rotate(" + t.a + " " + t.x + " " + t.y + ")");
                        }
                        if(ar.length > 0)
                        {
                            return ar.join(' ');
                        }
                    }
                    return null;
                });
                    
    };

    return manager;
};

/**
 * Binds all relevant link elements to the data on D3 enter event
 */
d3.selection.enter.prototype.joinForceLinkShapes = function(manager, typename) 
{ 
    if (typeof typename === 'undefined')
    {
        typename = "link";
    }
    var fill_opacity = .5;
    var linkStrength = .1; 
    var nodes = manager.graphdata.nodes;
    var my_handlers = manager.link_handlers;
    var linkEnter = this.append("line")
            .attr("class", typename)
            .attr("marker-end", function (o) {
                var targetnode = nodes[o.trgno];
                var sourcenode = nodes[o.srcno];
                if (targetnode.type === 'person')
                {
                    return "url(#arrow2person)";
                } else if (targetnode.type === 'goal') {
                    return ""; //url(#arrow2goal)";
                } else if (targetnode.type === 'task' || targetnode.type === 'equjb' || targetnode.type === 'xrcjb') {
                    return ""; //url(#arrow2task)";
                } else if (targetnode.type === 'role') {
                    return "url(#arrow2role)";
                } else if (targetnode.type === 'group') {
                    return "url(#arrow2group)";
                } else {
                    return "url(#arrow)";
                }
            })
            .style("linkStrength", linkStrength)
            .style("opacity", fill_opacity);
    
    for(var handler_name in my_handlers)
    {
        var detail = my_handlers[handler_name];
        var replace_existing = detail.hasOwnProperty("replacement") ? detail.replacement : true;
        var handler_type = detail.hasOwnProperty("handler_type") ? detail.handler_type : "on";
        if(replace_existing)
        {
            linkEnter.on("." + handler_name, null);     //Kill the existing handlers!
        }
        if(handler_type === "on")
        {
            linkEnter.on(handler_name, detail["function"]);
        } else {
            linkEnter.call(detail["function"]);
        }
    }
    
    return linkEnter;
};

/**
 * Append svg text so that it is not too wide
 */
bigfathom_util.shapes.smartAppendText = function (manager, textsel, maxwidth_limit)
{
    var label_attrib_overrides = manager.label_attrib_overrides;   
    
    if(typeof maxwidth_limit === 'undefined')
    {
        maxwidth_limit = null;    
    };

    var getLabelPropertyValue = function (mydata, attribname, defaultvalue)
    {
        var result = null;
        if(mydata.hasOwnProperty("custom_detail") 
                && mydata.custom_detail !== null 
                && mydata.custom_detail.hasOwnProperty("label")
                && mydata.custom_detail.label.hasOwnProperty(attribname))
        {
            result = mydata.custom_detail.label[attribname];
        }
        if(result === null)
        {
            result = defaultvalue;
        }
        return result;
    };

    //Figure out what our text is and return it in a structured object
    var getTextPartsFromData = function (mydata)
    {
        var result = null;
        var dy = 0;
        if(label_attrib_overrides.hasOwnProperty("text_as_parts"))
        {
            result = label_attrib_overrides.text_as_parts(mydata);
        }
        if(label_attrib_overrides.hasOwnProperty("text"))
        {
            result = {"title":label_attrib_overrides.text(mydata)};
        }
        
        var hasLabel = (typeof mydata.label !== "undefined") && mydata.label !== null;
        var hasCustomDetail = (mydata.hasOwnProperty("custom_detail") && mydata.custom_detail !== null);

        if(hasLabel)
        {
            if(mydata.label.hasOwnProperty("text_as_parts"))
            {
                result = mydata.label.text_as_parts;
            }
            if(mydata.label.hasOwnProperty("text"))
            {
                result = {"title":mydata.label.text};
                if(mydata.label.hasOwnProperty("dy"))
                {
                    dy = mydata.label.dy;
                }
            }
        } 
        if(result === null && hasCustomDetail && mydata.custom_detail.hasOwnProperty("label"))
        {
            if(!mydata.custom_detail.hasOwnProperty("label"))
            {
                result = {"title": "LABEL property missing from custom_detail object: " + JSON.stringify(mydata.custom_detail)};
            } else {
                if(mydata.custom_detail.label.hasOwnProperty("text_as_parts") && (typeof mydata.custom_detail.label.text_as_parts !== 'undefined'))
                {
                    result = mydata.custom_detail.label.text_as_parts;
                } else {
                    if(mydata.custom_detail.label.hasOwnProperty("text") && (typeof mydata.custom_detail.label.text !== 'undefined'))
                    {
                        result = {"title": mydata.custom_detail.label.text};
                    } else {
                        result = {"title": "LABEL TEXT property missing from LABEL object: " + JSON.stringify(mydata.custom_detail.label)};
                    }
                }
            }
        }
        if(result === null)
        {
            if(mydata.hasOwnProperty("label"))
            {
                result = {"title": mydata.label};
            } else 
            if(mydata.hasOwnProperty("text"))
            {
                result = mydata.text;
            } else {
                result = {"title": "LABEL TEXT MISSING"};
            }
        }
        if(!result && result !== "")
        {
            result = {"title": "ERROR GETTING LABEL FOR " + JSON.stringify(mydata)};
        }
        if(result !== null)
        {
            result['dy'] = dy;
        }
        return result;
    };
        
    //Now process all text in the selection    
    textsel.each(function() 
    {
        var d = d3.select(this);
        var mydata = d.data()[0];
        var text_as_parts = getTextPartsFromData(mydata);
        if(text_as_parts === null)
        {
            return;
        }
        var elem = d.text(null);
        var maxwidth = getLabelPropertyValue(mydata, "maxwidth", maxwidth_limit);
        if(maxwidth_limit !== null)
        {
            if(maxwidth === null || maxwidth > maxwidth_limit)
            {
                maxwidth = maxwidth_limit;  //Max limit might itself be null.
            }
        }
        var text_anchor = getLabelPropertyValue(mydata, "text-anchor", "start");
        var style_stroke = getLabelPropertyValue(mydata, "stroke", "transparent");
        var fill_opacity = getLabelPropertyValue(mydata, "fill_opacity", .80);
        var fill_color = getLabelPropertyValue(mydata, "fill_color", "black");
        var x = getLabelPropertyValue(mydata, "x", 0);
        var y = getLabelPropertyValue(mydata, "y", 0);
        var dx;
        var dy;
        dx = getLabelPropertyValue(mydata, "dx", 0);
        dy = getLabelPropertyValue(mydata, "dy", text_as_parts.dy);
        var dynum;
        var tspan = elem.text(null).append("tspan").attr("x", x).attr("y", y);
        
        if(typeof dy === "string")
        {
            var empos = dy.indexOf("em");
            if(empos < 0)
            {
                dynum = dy;
            } else {
                dynum = Number(dy.substring(0,empos));
            }
        } else {
            dynum = dy;
        }
        tspan.attr("dy", dynum + "em");
        tspan.attr("dx", dx);
        tspan.attr("text-anchor", text_anchor);    
        tspan.attr("fill", fill_color);    
        tspan.style("stroke", style_stroke);
        tspan.style("fill-opacity", fill_opacity);
        var title_width;
        var title_pad = 20;
        var after_title_start;
        if(maxwidth === null)
        {
            //Don't worry about multiple lines
            if(text_as_parts.hasOwnProperty("description") && text_as_parts.description !== null)
            {
                tspan.text(text_as_parts.title);
                title_width = tspan.node().getComputedTextLength();
                after_title_start = title_width + title_pad;
                tspan = d.append("tspan").attr("x", x).attr("y", y)
                        .attr("dx", dx + after_title_start)
                        .attr("dy", dynum + "em")
                        .text(" - " + text_as_parts.description);
                tspan.attr("text-anchor", text_anchor);  
                tspan.attr("fill", fill_color);  
                tspan.style("stroke", style_stroke);
                tspan.style("fill-opacity", fill_opacity - .1);
            } else {
                tspan.text(text_as_parts.title);
            }
        } else {
            //Make it fit
            var words;
            if(text_as_parts.hasOwnProperty("description"))
            {
                //tspan.text(text_as_parts.title);
                var heading = text_as_parts.title + " - " + text_as_parts.description;
                tspan.text(heading);
                title_width = tspan.node().getComputedTextLength();
                after_title_start = 0;
                words = (heading).split(/\s+/).reverse();
                /*
                if(title_width > maxwidth)
                {
                    //Title is too big, so just merge it with the rest of the text and wrap.
                    words = (text_as_parts.title + " - AAA " + text_as_parts.description).split(/\s+/).reverse();
                } else {
                    //This fits, put description in its own area
                    after_title_start = title_width + title_pad;
                    tspan = d.append("tspan")
                            .attr("x", x).attr("y", y)
                            .attr("dx", dx + after_title_start)
                            .attr("dy", dynum + "em")
                            .text(title_width + " < " + maxwidth + " - BBB " + text_as_parts.description);
                    tspan.attr("text-anchor", text_anchor);  
                    tspan.attr("fill", fill_color);  
                    tspan.style("stroke", style_stroke);
                    tspan.style("fill-opacity", fill_opacity - .1);
                    
                    words = (" - " + text_as_parts.description).split(/\s+/).reverse();
                }
                */
            } else {
                after_title_start = 0;
                words = text_as_parts.title.split(/\s+/).reverse();
            }
            var word,
                line = [],
                lineNumber = 0,
                lineHeight = 1.1;
            while (word = words.pop()) //Intentional assignment
            {
                line.push(word);
                tspan.text(line.join(" "));
                if (tspan.node().getComputedTextLength() > maxwidth-after_title_start) 
                {
                    line.pop();
                    tspan.text(line.join(" "));
                    line = [word];
                    tspan = d.append("tspan")
                            .attr("x", x).attr("y", y)
                            .attr("dx", dx + after_title_start)
                            .attr("dy", ++lineNumber * lineHeight + dynum + "em")
                            .text(word);
                    tspan.attr("text-anchor", text_anchor);  
                    tspan.attr("fill", fill_color);  
                    tspan.style("stroke", style_stroke);
                    tspan.style("fill-opacity", fill_opacity);
                }
            }
        }
    });
};

/**
 * Translate the node to an ideal X coordinate if outside min/max X range.
 */
d3.selection.prototype.immediateMoveX = function(ideal_center_x, x_min, x_max) 
{
    this.attr("transform", function(d) {
            if(d.x < x_min || d.x > x_max)
            {
                return "translate(" + ideal_center_x + "," + d.y + ")"; 
            }
            return null;
        });
        
    return this;
};

/**
 * Updates all relevant node elements as per the data for the D3 selection
 * Also immediately positions selection items to their coordinates.
 */
d3.selection.prototype.updateForceNodeShapes = function(manager, typename) 
{
    if(this.empty())
    {
        console.log("Empty selection nothing to update");
        return;
    }
    if (typeof typename === 'undefined')
    {
        typename = "node";
    }
    
    manager.buildDOMElements(this, typename);
    
    this.classed("updated",true);
    
    //This transform positions the nodes immediately
    this.attr("transform", function(d) { 
            return "translate(" + d.x + "," + d.y + ")"; 
        });
        
    return this;
};

/**
 * Binds all relevant node elements to the data on D3 enter event
 */
d3.selection.enter.prototype.joinForceNodeShapes = function(manager, typename) 
{ 
    if (typeof typename === 'undefined')
    {
        typename = "node";
    }

    var my_handlers = manager.node_handlers;
    //var style_overrides = manager.style_overrides;

    //Wrap our raw text so it is not wider than the specified width
    var smartAppendText = function (textsel, maxwidth_limit) {
        bigfathom_util.shapes.smartAppendText(manager, textsel, maxwidth_limit);
    };

    var nodeEnter = this.append("g")
        .attr("class", function (d) {
            var addsubtype;
            if(d.hasOwnProperty("subtype"))
            {
                if(d.subtype.constructor !== Array)
                {
                    addsubtype = " " + d.subtype;
                } else {
                    addsubtype = "";
                    for(var i = 0; i < d.subtype.length; i++)
                    {
                        addsubtype += " " + d.subtype[i];
                    }
                }
            } else {
                addsubtype = "";
            }
            if(d.hasOwnProperty("is_background") && d.is_background)
            {
                return "background " + typename + addsubtype;
            } else {
                return typename + addsubtype;
            }
        })
        .attr("transform", function(d) { 
            return "translate(" + d.x + "," + d.y + ")"; 
        });

    manager.buildDOMElements(nodeEnter, typename);

    for(var handler_name in my_handlers)
    {
        var detail = my_handlers[handler_name];
        var replace_existing = detail.hasOwnProperty("replacement") ? detail.replacement : true;
        var handler_type = detail.hasOwnProperty("handler_type") ? detail.handler_type : "on";
        if(replace_existing)
        {
            nodeEnter.on("." + handler_name, null);  //Kill the existing handlers with DOT namespace!
        }
        if(handler_type === "on")
        {
            nodeEnter.on(handler_name, detail["function"]);
        } else {
            nodeEnter.call(detail["function"]);
        }
    }

    //Visual clue that this node is disconnected
    nodeEnter.append("polygon")
        .attr("points", function(d){
            if(d.hasOwnProperty("subtype") //d.type === 'goal' 
                && d.subtype.indexOf("warn_disconnected_rootnode") > -1)
            {
                //Point to the right
                return "30,0 0,-30 0,30";
            } else {
                return "";
            }
        })
        .style("opacity", .5)
        .attr("fill","red")
        .attr("stroke","red");

    var showAntConnector = function(d)
    {
        if(manager.graphdata.hide_completed_branches)
        {
            if(manager.graphdata.completed_branch_rootids.hasOwnProperty(d.nativeid) || !manager.graphdata.incompleted_branch_rootids.hasOwnProperty(d.nativeid))
            {
                return false;
            } else {
                return true;
            }
        } else {
            if(bigfathom_util.nodes.hasAntecedents(d))
            {
                return true;
            } else {
                return false;
            }
        }
    };

    //Visual clue that we have antecedent goals
    nodeEnter.append("path")
            .attr("d", function(d){
                if(showAntConnector(d))
                {
                    return bigfathom_util.shapes.lib.keyprops[d.type].connector.d;
                }
                return "";
            })
            .attr("stroke",function(d){
                if(showAntConnector(d))
                {
                    if(d.type === 'proj' || d.type === 'goal' 
                        || d.type === 'task'
                        || d.type === 'equjb' || d.type === 'xrcjb')
                    {
                        return bigfathom_util.shapes.lib.keyprops[d.type].connector.stroke;
                    }
                }
                return null;
            })
            .classed("branch_connector_has_hidden", function (d) {
                return showAntConnector(d) && d.hasOwnProperty('subtype') && bigfathom_util.nodes.hasHiddenAntecedents(d);
            })
            .classed("branch_connector_none_hidden", function (d) {
                return showAntConnector(d) && d.hasOwnProperty('subtype') && !bigfathom_util.nodes.hasHiddenAntecedents(d);
            });

    nodeEnter.append("text")
        .attr("y",function(d){
            if(showAntConnector(d))
            {
                return bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[d.type].offset.y;
            }
            return null;
        })
        .attr("x",function(d){
            if(showAntConnector(d))
            {
                return bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[d.type].offset.x;
            }
            return null;
        })
        .attr("font-size",function(d){
            if(showAntConnector(d))
            {
                return bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[d.type].font.size;
            }
            return null;
        })
        .attr("stroke",function(d){
            if(showAntConnector(d))
            {
                return bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[d.type].stroke;
            }
            return null;
        })
        .attr("fill",function(d){
            if(showAntConnector(d))
            {
                return bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[d.type].fill;
            }
            return null;
        })
        .text(function(d){
            if(showAntConnector(d))
            {
                var letters = [];
                var ant_types = bigfathom_util.nodes.getAntecedentTypes(d);
                for(var i=0; i<ant_types.length; i++)
                {
                    var ant_type = ant_types[i];
                    letters.push(bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol[ant_type].letter);
                }
                return letters.join(" ");
            }
            return null;
        });
        
    //Indicate that ants are being displayed or not
    nodeEnter.append("text")
        .attr("y",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.offset.y)
        .attr("x",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.offset.x)
        .attr("font-size",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.font.size)
        .attr("text-anchor",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.font.text_anchor)
        .attr("dy",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.font.dy)
        .attr("stroke",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.stroke)
        .attr("fill",bigfathom_util.shapes.lib.keyprops.goal.connector.flagsymbol.status.fill)
        .classed("branch_toggle", function(d) {
            return showAntConnector(d);
        })
        .text(function(d){
            if(showAntConnector(d))
            {
                if(bigfathom_util.nodes.hasHiddenAntecedents(d))
                {
                    return "+";
                } else {
                    if(bigfathom_util.nodes.hasAntecedents(d))
                    {
                        return "-";
                    }
                }
            }
            return null;
        });

    //Visual clue that this is a project root goal
    nodeEnter.append("circle")
        .attr("cx", -4.3).attr("cy", -5)
        .attr("r", function(d){
            if(d.type === 'goal' && d.hasOwnProperty("subtype") &&  d.subtype.indexOf("project") > -1)
            {
                return 4;
            } else {
                return 0;
            }
        })
        .attr("fill","cyan").attr("stroke","black");
    //TODO --- placeholder for when we have proj type; at that time remove the goal type check above for projects!!!!
    nodeEnter.append("circle")
        .attr("cx", -4.3).attr("cy", -5)
        .attr("r", function(d){
            if(d.type === 'proj' && d.hasOwnProperty("subtype") &&  d.subtype.indexOf("project") > -1)
            {
                return 4;
            } else {
                return 0;
            }
        })
        .attr("fill","green").attr("stroke","black");

    //Visual clue that this is a deliverable
    nodeEnter.append("rect")
        .attr("width", function(d){
            if(d.hasOwnProperty("subtype") &&  d.subtype.indexOf("deliverable") > -1)
            {
                return 8;
            } else {
                return 0;
            }
        })
        .attr("height", function(d){
            if(d.hasOwnProperty("subtype") && d.subtype.indexOf("deliverable") > -1)
            {
                return 8;
            } else {
                return 0;
            }
        })
        .attr("y",1)
        .attr("x",-8)
        .attr("fill","orange")
        .attr("stroke","black");

    //Append the status code to the shape
    nodeEnter.append("text")
        .attr("y",0)
        .attr("x",0)
        .attr("dx",function(d) {
            if(bigfathom_util.shapes.lib.keyprops.hasOwnProperty(d.type))
            {
                var kp = bigfathom_util.shapes.lib.keyprops[d.type];
                if(kp.hasOwnProperty('status_cd'))
                {
                    return kp.status_cd.offset.dx;
                }
            }
            return 0;
        })
        .attr("dy",function(d) {
            if(bigfathom_util.shapes.lib.keyprops.hasOwnProperty(d.type))
            {
                var kp = bigfathom_util.shapes.lib.keyprops[d.type];
                if(kp.hasOwnProperty('status_cd'))
                {
                    return kp.status_cd.offset.dy;
                }
            }
            return 0;
        })
        .attr('class', function(d) {
            var classtext = "node-status-cd";
            if(d.hasOwnProperty('status_detail'))
            {
                var sd = d.status_detail;
                var hpart;
                var tpart;
                if(sd.happy_yn === null)
                {
                    hpart = '-ambigous';
                } else {
                    if(sd.happy_yn == 1)
                    {
                        hpart = '-happy-yes';
                    } else {
                        hpart = '-happy-no';
                    }
                }
                if(sd.terminal_yn == 1)
                {
                    tpart = '-terminal';
                } else {
                    tpart = '';
                }
                classtext += " node-status" + tpart + hpart;
                //classtext += " node-status-terminal-happy-yes";
            }
            return classtext;
        })
        .text(function(d){
            if(d.hasOwnProperty('status_detail'))
            {
                //20180430 enhancing with new property 'show_status' so we can togle from template display
                if((!d.hasOwnProperty("show_status") || d.show_status) && (!d.hasOwnProperty("is_candidate") || !d.is_candidate))
                {
                    return d.status_detail.code;
                }
            }
            return null;
        });


    //Append the label to the shape
    nodeEnter.append("text")
        .call(smartAppendText, null);

    return nodeEnter;
};


