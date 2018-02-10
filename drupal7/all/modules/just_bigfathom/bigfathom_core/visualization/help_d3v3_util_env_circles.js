/* 
 * Library of utility functions for working with circles
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
};
if(!bigfathom_util.hasOwnProperty("env"))
{
    bigfathom_util.env = {};
};
if(!bigfathom_util.hasOwnProperty("circles"))
{
    //Create the object property because it does not already exist
    bigfathom_util.env.circles = {version: "20160213.1"};
};

/**
 * Return the X,Y coordinate
 */
bigfathom_util.env.circles.getPointOnCircumference = function (cx, cy, r, radians)
{
    //radians = degrees * Math.PI / 180
    var x = cx + r * Math.cos(radians);
    var y = cy + r * Math.sin(radians);
    return {x: x, y: y};
};

/**
 * Return a circle structure with fixed x,y points on the circumference
 */
bigfathom_util.env.circles.getOneCircleFrame = function (center_x, center_y, radius, point_count)
{
    if(point_count < 4)
    {
        point_count = 4;
    }

    var circle_frame = {};
    var points = [];
    var cur_angle = 0;
    var angle_delta = 2*Math.PI / point_count;
    
    circle_frame["center"] = {x: center_x, y: center_y, r: radius};
    for(var i=0; i<point_count; i++)
    {
        var p = this.getPointOnCircumference(center_x, center_y, radius, cur_angle);
        points.push(p);
        cur_angle = cur_angle + angle_delta;
    }
    circle_frame["points"] = points;
    return circle_frame;
};

/**
 * Add points and recompute the frame
 */
bigfathom_util.env.circles.growCircleFrame = function (circle_frame, insertion_offset, insertion_count)
{
    var new_points = [];    //Top is offset zero
    
    return new_points;
};

/**
 * Remove points and recompute the frame
 */
bigfathom_util.env.circles.shrinkCircleFrame = function (circle_frame, deletion_offset, deletion_count)
{
    var new_points = [];    //Top is offset zero
    
    return new_points;
};

bigfathom_util.env.circles.getBullsEye = function (context)
{
    var methods = {};
    
    
    if(context === 'insight')  //Circle of insight context
    {
        methods.getContainerFillColor = function (level)
        {
            switch(level)
            {
                case 1: return "#08FF08";
                case 2: return "#77FF77";
                case 3: return "yellow";
                case 4: return "#787878";
                case 5: return "#FFFFFF";
            }
            return "red";
        };
        
        methods.getContainerStrokeColor = function (level)
        {
            return "white";
        };
        
        methods.getContainerText = function (level, verbose)
        {
            if(verbose)
            {
                switch(level)
                {
                    case 1: return "You have COMPLETE confidence in your outcome forecasting insight";
                    case 2: return "You have SOME confidence in your outcome forecasting insight";
                    case 3: return "You have LITTLE confidence in your outcome forecasting insight";
                    case 4: return "You do not have any confidence in your outcome forecasting insight";
                    case 5: return "";    //This level is simply to reset the assignment
                }
            } else {
                switch(level)
                {
                    case 1: return "Complete";
                    case 2: return "Some";
                    case 3: return "Little";
                    case 4: return "None";
                    case 5: return "";    //This level is simply to reset the assignment
                }
            }
            return "error: no text for " + level;
        };
    } else if(context === 'influence')  //Circle of influence context
    {
        methods.getContainerFillColor = function (level)
        {
            switch(level)
            {
                case 1: return "#08FF08";
                case 2: return "#77FF77";
                case 3: return "yellow";
                case 4: return "#787878";
                case 5: return "#FFFFFF";
            }
            return "red";
        };
        
        methods.getContainerStrokeColor = function (level)
        {
            return "white";
        };
        
        methods.getContainerText = function (level, verbose)
        {
            if(verbose)
            {
                switch(level)
                {
                    case 1: return "You have DIRECT influence in the quality of the outcome";
                    case 2: return "You have an indirect influence in the quality of the outcome";
                    case 3: return "You do not have influence on the quality of the outcome";
                    case 4: return "You do not consider it important";
                    case 5: return "";    //This level is simply to reset the assignment
                }
            } else {
                switch(level)
                {
                    case 1: return "Direct";
                    case 2: return "Indirect";
                    case 3: return "No Influence";
                    case 4: return "No Interest";
                    case 5: return "";    //This level is simply to reset the assignment
                }
            }
            return "error: no text for " + level;
        };
    } else {
        throw "No support for context = '" + context + "'";
    }

    //These functions apply to all context.
    methods.getAllContainerAttribs = function (canvas)
    {
        var attribs = {corefacts:null, background:null, targets:null};
        var levelnum = 1;

        var corefacts = this.getCoreFacts(canvas);

        attribs.corefacts = corefacts;
        attribs.background = this.getBackgroundElements(corefacts);

        var targets = {};
        for(levelnum = 5; levelnum > 0; levelnum--)
        {
            targets[levelnum] = this.getContainerValues(canvas, levelnum);
        }
        attribs.targets = targets;

        return attribs;
    };

    methods.getBackgroundElements = function (corefacts)
    {
        var elements = [];
        var attribs = {};
        var vmargin = corefacts.vgap;
        var laneheight = corefacts.usable_h - 2*vmargin;

        attribs = {};
        attribs["position"] = {x:0, y:corefacts.vgap - vmargin};
        attribs["label"] = {
                "type": "text",
                "x": 0,
                "y": 0,
                "dx": 0,
                "dy": "1em",
                "text_anchor": "start",
                "fill_color": "black",
                "fill_opacity": 1,
                "text": "Not Yet Assessed"
            };
        attribs["target"] = {
                "type": "rect",
                "id": "mytestelem",
                "x": 0,
                "y": 0,
                "height": laneheight,
                "width": corefacts.unassessed_lane.w,
                "rx": 0,
                "ry": 0,
                "fill_color": "blue",
                "fill_opacity": .1,
                "stroke": 1
            };
        elements.push(attribs);

        for(var levelnum = 4; levelnum > 0; levelnum--)
        {
            attribs = {};
            var levelinfo = corefacts.levels[levelnum];
            attribs["position"] = {x:levelinfo.start_x, y:corefacts.vgap - vmargin};
            attribs["label"] = {
                    "type": "text",
                    "maxwidth": corefacts.level_lanewidth,
                    "x": 0,
                    "y": 0,
                    "dx": 0,
                    "dy": "1em",
                    "text_anchor": "start",
                    "fill_color": "black",
                    "fill_opacity": 1,
                    "text": this.getContainerText(levelnum, true)
                };
            attribs["target"] = {
                    "type": "rect",
                    "id": "lane" + levelnum,
                    "x": 0,
                    "y": 0,
                    "height": laneheight,
                    "width": corefacts.level_lanewidth,
                    "rx": 0,
                    "ry": 0,
                    "fill_color": this.getContainerFillColor(levelnum),
                    "fill_opacity": .2,
                    "stroke": 1
                };
            elements.push(attribs);
        }

        return elements;
    };


    methods.getCoreFacts = function (canvas) 
    {
        var corefacts = {};

        var min_slot_width = 200;
        var vgap = canvas.w > 600 ? 5 : canvas.w / 200;
        var hgap = 20;// + (canvas.h / 100);
        var hgap_from_unassigned = 5;
        var usable_h = canvas.h - 2*vgap;
        var usable_w = canvas.w - 2*hgap;
        var mindim = (usable_w < usable_h ? usable_w : usable_h);
        var unassigned_start_x = hgap;
        var outer_r;
        var circle_center;
        var show_circle;
        var show_assigned_lanes;
        var first_assigned_lane_start_x;
        var slot_width = usable_w / 7;
        if(slot_width < min_slot_width)
        {
            //Remove the circle
            show_circle = false;
            show_assigned_lanes = true;
            outer_r = null;
            slot_width = ((usable_w- hgap_from_unassigned) / 5);
            circle_center = null;
            first_assigned_lane_start_x = unassigned_start_x + slot_width + hgap_from_unassigned;
        } else {
            //Normal sizes
            show_circle = true;
            show_assigned_lanes = true;
            outer_r = slot_width;
            circle_center = {'x':(slot_width + outer_r),'y':vgap + usable_h / 2};
            first_assigned_lane_start_x = 3 * slot_width;
        }

        corefacts["show_circle"] = show_circle;
        corefacts["show_assigned_lanes"] = show_assigned_lanes;
        corefacts["circle_center"] = circle_center;
        corefacts["vgap"] = vgap;
        corefacts["hgap"] = hgap;
        corefacts["outer_r"] = outer_r;
        corefacts["usable_h"] = usable_h;
        corefacts["usable_w"] = usable_w;
        corefacts["unassessed_lane"] = {'start_x': unassigned_start_x, 'w':slot_width};
        corefacts["level_lanewidth"] = slot_width;
        corefacts["concept_target"] = {x: unassigned_start_x + slot_width, w: 2 * outer_r};
        var levelinfo = {};
        levelinfo[0] = {start_x: first_assigned_lane_start_x + 4*slot_width};
        levelinfo[1] = {start_x: first_assigned_lane_start_x};
        levelinfo[2] = {start_x: first_assigned_lane_start_x + slot_width};
        levelinfo[3] = {start_x: first_assigned_lane_start_x + 2*slot_width};
        levelinfo[4] = {start_x: first_assigned_lane_start_x + 3*slot_width};
        corefacts["levels"] = levelinfo;

        return corefacts;
    };

    /**
     * Levelnum 1 is in the middle
     */
    methods.getContainerValues = function (canvas, levelnum)
    {
        var attribs = {};
        var maxlevels = 5;

        var corefacts = this.getCoreFacts(canvas);
        if(corefacts.show_circle)
        {
            var outer_r = corefacts.outer_r;    //(mindim / 2) - vgap;
            var r_delta = outer_r / maxlevels;
            var cy = corefacts.circle_center.y;    //canvas.h / 2;
            var cx = corefacts.circle_center.x;    //canvas.w / 2 - hgap;
            var container_opacity = .2;
            var text_opacity = 1;
            //var text_x = cx;
            var text_y;
            var cur_r;
            var dy;

            cur_r = outer_r - (r_delta * (maxlevels - levelnum));
            text_y = cy - ((levelnum-1) * r_delta);
            if(levelnum !== 1)
            {
                dy = "-1em";
                text_opacity = .4 + (1 / levelnum);
            } else {
                //dy = "0em"; //Center is good.
                dy = 0;
                text_opacity = 1;
            }
            attribs["position"] = {x:cx, y:cy};
            attribs["label"] = {
                    "type": "text",
                    "maxwidth" : corefacts.outer_r,
                    "x": 0,  //text_x,
                    "y": -1 * ((levelnum-1) * r_delta),
                    "dx": "-2em" ,
                    "dy": dy,
                    "text_anchor": "start",
                    "fill_color": "black",
                    "fill_opacity": text_opacity,
                    "text": this.getContainerText(levelnum, false)
                };
            var id = "container" + levelnum;
            attribs["target"] = {
                    "type": "circle",
                    "id": id,
                    "cx": cx,
                    "cy": cy,
                    "r": cur_r,
                    "fill_color": this.getContainerFillColor(levelnum),
                    "fill_opacity": container_opacity,
                    "stroke": this.getContainerStrokeColor(levelnum)
                };
        }
        return attribs;
    };
    
    return methods;
};

