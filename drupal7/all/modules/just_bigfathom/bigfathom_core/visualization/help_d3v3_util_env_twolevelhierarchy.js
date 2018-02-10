/* 
 * Library of utility functions for with environment
 * 
 * Copyright Room4me.com Software LLC 2015
 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if(!bigfathom_util.hasOwnProperty("env"))
{
    bigfathom_util.env = {};
}
if(!bigfathom_util.env.hasOwnProperty("twolevelhierarchy"))
{
    //Create the object property because it does not already exist
    bigfathom_util.env.twolevelhierarchy = {version: "20151231.2"};
}

bigfathom_util.env.twolevelhierarchy.manager = function (canvas, lane_defs)
{
    var lane_count = lane_defs.length;
    var corefacts = {
        canvas: canvas,
        lane_defs: lane_defs,
        lane_count: lane_count, 
    };
    var methods = {};
    var action_manager = {info:'action manager for two level hierarchy',
        corefacts: corefacts,
        methods: methods
    };
    
    methods.getContainerFillColor = function (lanenum)
    {
        switch(lanenum % 4)
        {
            case 0: return "#66ffff";
            case 1: return "#ffff99";
        }
        return "red";
    }

    methods.getContainerStrokeColor = function (lanenum)
    {
        switch(lanenum % 4)
        {
            case 0: return "black";
            case 1: return "#FF0000";
        }
        return "red";
    }

    methods.getContainerText = function (lanenum)
    {
        var offset;
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            //default to first lane
            offset = 0;
        } else {
            offset = (lanenum-1);
        }
        return corefacts.lanes[offset].label;
    }

    methods.getLaneInfo = function (lanenum)
    {
        var laneidx = 1;
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            laneidx = corefacts.lane_count;
        } else {
            laneidx = lanenum - 1;
        }
        return corefacts.lanes[laneidx];
    }

    methods.getLaneAssignmentAtPosition = function (x,y,failvalue)
    {
        var lanenum = failvalue;

        var laneidx = corefacts.lane_count;
        if(x > corefacts.lanes[laneidx].start_x)
        {
            //Indicates not assigned to a sprint
            lanenum = null;
        }

        //See if we are in a sprint
        for(laneidx = 1; laneidx < corefacts.lane_count; laneidx++)
        {
            if(x < corefacts.lanes[laneidx].start_x)
            {
                lanenum = laneidx;
                break;
            }
        }

        return lanenum;
    }

    /**
     * Return elements that have no drag-drop interaction
     */
    methods.getBackgroundElements = function ()
    {
        var elements = [];
        
        for(i = 0; i < corefacts.lane_count; i++)
        {

            var onelane = corefacts.lanes[i];

            if(onelane.hasOwnProperty("top_legend"))
            {
                //Put the start date on a tickline
                tick_attribs = {};
                tick_attribs["position"] = {
                    x: onelane.start_x, 
                    y: corefacts.tick_line_top
                };
                var ticktext = onelane.top_legend.start_text;
                tick_attribs["label"] = {
                        "type": "text",
                        "maxwidth": corefacts.lane_width,
                        "x": 0,
                        "y": 0,
                        "dx": 0,
                        "dy": ".5em",
                        "text_anchor": "start",
                        "fill_color": "black",
                        "fill_opacity": 1,
                        "text": ticktext
                    }
                var id = "segment" + i;
                tick_attribs["target"] = {
                        "type": "rect",
                        "id": id,
                        "x": 0,
                        "y": 0,
                        "height": corefacts.lane_top - corefacts.tick_line_top,
                        "width": onelane.width,
                        "rx": 0,
                        "ry": 0,
                        "fill_color": "blue",
                        "fill_opacity": .11,
                        "stroke": "white"
                    }
                elements.push(tick_attribs);
            }

            //Lane attribs
            attribs = {};
            attribs["position"] = {
                x: onelane.start_x, 
                y: corefacts.lane_top
            };
            var labeltext = this.getContainerText(i+1);
            attribs["label"] = {
                    "type": "text",
                    "maxwidth": corefacts.lane_width,
                    "x": 0,
                    "y": 0,
                    "dx": 0,
                    "dy": "1em",
                    "text_anchor": "start",
                    "fill_color": "black",
                    "fill_opacity": 1,
                    "text": labeltext
                }
            var id = "container" + i;
            attribs["target"] = {
                    "type": "rect",
                    "id": id,
                    "x": 0,
                    "y": 0,
                    "height": corefacts.lane_height,
                    "width": onelane.width,
                    "rx": 0,
                    "ry": 0,
                    "fill_color": this.getContainerFillColor(i),
                    "fill_opacity": .88,
                    "stroke": this.getContainerStrokeColor(i)
                }

            elements.push(attribs);
        }

        return elements;
    }

    /**
     * Return elements that have a drag-drop interaction
     */
    methods.getTargetElements = function ()
    {
        var elements = [];

        return elements;
    }

    /**
     * Builds 'lanes' array and other properties to the corefacts and returns it
     */
    methods.getCoreFacts = function () 
    {

        //Already defined?
        if(corefacts.hasOwnProperty("defined") && corefacts.defined)
        {
            //Yes, don't bother computing these again
            return corefacts;
        }

        //Walk through all the input data once to see what we will build
        var has_top_legend = false;
        for(i = 0; i < corefacts.lane_count; i++)
        {
            onelanedef_input = corefacts.lane_defs[i];
            if(onelanedef_input.hasOwnProperty("top_legend"))
            {
                has_top_legend = true;
            }
        }

        //Now build all the core facts
        var blankline_size = 12;
        var vgap = corefacts.canvas.h > 400 ? 4 : 1;    
        var hgap = corefacts.canvas.w > 600 ? 10 : 2;
        if(has_top_legend)
        {
            corefacts["tick_line_top"] = vgap;
            corefacts["lane_top"] = vgap + blankline_size;
        } else {
            corefacts["lane_top"] = vgap;
        }
        var usable_h = corefacts.canvas.h - 2*vgap;
        var usable_w = corefacts.canvas.w - 2*hgap;

        corefacts["defined"] = true;
        corefacts["vgap"] = vgap;
        corefacts["hgap"] = hgap;
        corefacts["usable_h"] = usable_h;
        corefacts["usable_w"] = usable_w;

        corefacts["lane_date_y"] = vgap;
        corefacts["lane_title_y"] = vgap + 20;
        corefacts["lane_height"] = usable_h - blankline_size;

        corefacts["lane_width"] = usable_w / corefacts.lane_count;
        corefacts["lanes"] = [];

        var lanenum;
        var onelanedef_input;
        var onelane;
        var label_tx;
        var start_x;
        var end_x;
        var cur_x = hgap;
        var center_y = corefacts.vgap + corefacts.usable_h / 2;
        for(i = 0; i < corefacts.lane_count; i++)
        {
            lanenum = i+1;
            onelanedef_input = corefacts.lane_defs[i];
            start_x = cur_x + i*corefacts.lane_width;
            end_x = start_x + corefacts.lane_width;
            if(onelanedef_input.hasOwnProperty("label"))
            {
                label_tx = onelanedef_input.label;
            } else {
                label_tx = "Lane " + lanenum;
            }
            onelane = {
                "label" : label_tx,
                "maxwidth": corefacts.lane_width,
                "start_x" : start_x,
                "end_x" : end_x, 
                "width" : corefacts.lane_width, 
                "hmargin" : (corefacts.lane_width / 10), 
                "content_center" : {
                    "x": start_x + corefacts.lane_width / 4,
                    "y": center_y
                }
            };
            if(onelanedef_input.hasOwnProperty("top_legend"))
            {
                if(onelanedef_input.top_legend.hasOwnProperty("start_text"))
                {
                    onelane["top_legend"] = {"start_text": onelanedef_input.top_legend.start_text}
                }
            }
            
            corefacts.lanes.push(onelane);
        }
        corefacts.lanes.push(onelane);

        return corefacts;
    }

    methods.getAllContainerAttribs = function ()
    {
        var attribs = {background:null, targets:null, corefacts:null};
        attribs.corefacts = this.getCoreFacts();
        attribs.background = this.getBackgroundElements();
        attribs.targets = this.getTargetElements();
        return attribs;
    }

    return action_manager;
}

