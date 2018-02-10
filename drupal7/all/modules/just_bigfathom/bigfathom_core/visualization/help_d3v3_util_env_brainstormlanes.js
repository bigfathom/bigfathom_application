/* 
 * Library of utility functions for working with goal hierarchy data
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
if(!bigfathom_util.env.hasOwnProperty("brainstormlanes"))
{
    //Create the object property because it does not already exist
    bigfathom_util.env.brainstormlanes = {version: "20161003.1"};
};

bigfathom_util.env.brainstormlanes.manager = function (context)
{
    var methods = {
        levelcount: 5,
        trashlevel: 0,
        parkinglotlevel: 4,
        firstlanelevel: 1,
        lanecount: 3
    };
    if(context === 'brainstorm')  //lanes for brainstorming
    {
        methods.getContainerFillColor = function (level)
        {
            switch(level)
            {
                case 0: return "red";
                case 1: return "#FF99CC";
                case 2: return "#66FF6C"; //"#99FF33";
                case 3: return "#B1FF66"; //"#FFFF66";
                case 4: return "blue";
            }
            return "red";
        };
        
        methods.getContainerStrokeColor = function (level)
        {
            switch(level)
            {
                case 0: return "black";
                case 1: return "#FF0000";
                case 2: return "#FF3300";
                case 3: return "#FF3399";
                case 4: return "black";
            }
            return "red";
        };
        
        methods.getContainerText = function (level)
        {
            switch(level)
            {
                case 0: return "Trashcan";
                case 1: return "Uncategorized Items: Not sure if this is a goal or a task yet";
                case 2: return "Goal Proposals: Some things you want to achieve (aspirations/deep-work)";
                case 3: return "Task Proposals: Some things to do (actions/shallow-work)";
                case 4: return "Parkinglot";
            }
            return "error: no text for " + level;
        };
        
        methods.getContainerTextAsParts = function (level)
        {
            switch(level)
            {
                case 0: return {"title": "Trashcan"};
                case 1: return {"title": "Uncategorized Items","description":"Not sure if this is a goal or a task yet"};
                case 2: return {"title": "Goal Proposals","description":"Some things you want to achieve (aspirations/deep-work)"};
                case 3: return {"title": "Task Proposals","description":"Some things to do (actions/shallow-work)"};
                case 4: return {"title": "Parkinglot"};
            }
            return  {"title":"error: no text for " + level};
        };
        
        methods.getAllContainerAttribs = function (canvas)
        {
            var attribs = {count: methods.levelcount};
            var levelnum = methods.firstlanelevel;
            var containerdetails = {};
            for(levelnum = methods.levelcount-1; levelnum >= 0; levelnum--)
            {
                var item = this.getContainerValues(canvas, levelnum);
                containerdetails[levelnum] = item;
            }
            attribs['details'] = containerdetails;
            return attribs;
        };

        /**
         * Compute the lanes and special targets of the environment
         */
        methods.getContainerValues = function (canvas, levelnum)
        {
            var attribs = {};
            if(levelnum > 4)
            {
                throw "There is no support for levelnum=" + levelnum;
            }
            var vgap = 10;
            var hlgap = 100;
            var hrgap = 50;
            var horizontal_delta = (canvas.w - hlgap - hrgap) / methods.lanecount;
            var container_opacity = .2;
            var text_opacity = 1;

            var rect_x;
            var rect_y;
            var rh;
            var rw;
            var label_dx;
            var label_dy;
            
            var container_type;
            var subtype;
            var id = "container" + levelnum;
            
            if(levelnum == 0)
            {
                //trashcan
                container_type = "trashcan";
                subtype = "specialtarget";
                rect_x = hlgap/10;
                rect_y = vgap;
                rh = (canvas.h - 2 * vgap) / 4;
                rw = hlgap - rect_x;
                label_dx = 0;
                label_dy = "1em";
            } else if(levelnum == 4) {
                //parkinglot
                container_type = "parkinglot";
                subtype = "specialtarget";
                rect_x = 5;
                rect_y = vgap + canvas.h / 2;
                rh = (canvas.h - 2 * vgap) / 4;
                rw = hlgap - rect_x;
                label_dx = 0;
                label_dy = "2em";
            } else {
                container_type = "rect";
                subtype = "lane";
                rect_x = hlgap + (levelnum - 1) * horizontal_delta;
                rect_y = vgap;
                rh = canvas.h - 2 * vgap;
                rw = horizontal_delta;
                label_dx = rw/20;
                label_dy = "1em";
            }
            
            attribs["position"] = {x:rect_x, y:rect_y};
            attribs["label"] = {
                    "type": "text",
                    "maxwidth": rw - rw/10,
                    "x": 0, //text_x,
                    "y": 0, //text_y,
                    "dx": label_dx,
                    "dy": label_dy,
                    "text_anchor": "start",
                    "fill_color": "black",
                    "fill_opacity": text_opacity,
                    "text": this.getContainerText(levelnum),
                    "text_as_parts": this.getContainerTextAsParts(levelnum)
                }
            attribs["target"] = {
                      "type": container_type
                    , "subtype": subtype
                    , "id": id
                    , "key": container_type + levelnum
                    , "x": rect_x
                    , "y": rect_y
                    , "height": rh
                    , "width": rw
                    , "rx": 0
                    , "ry": 0
                    , "fill_color": this.getContainerFillColor(levelnum)
                    , "fill_opacity": container_opacity
                    , "stroke": this.getContainerStrokeColor(levelnum)
                    , 'is_background': false
                    , 'is_drag_source': false
                    , 'is_drag_target': true
                    , 'fixed': true
                };
            
            return attribs;
        };
    } else {
        console.log("No support for context = '" + context + "'");
    }
    
    return methods;
};

