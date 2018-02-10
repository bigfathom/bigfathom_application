/* 
 * Library of utility functions for time management quadrant
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
if(!bigfathom_util.hasOwnProperty("env"))
{
    bigfathom_util.env = {};
}
if(!bigfathom_util.env.hasOwnProperty("quadrant"))
{
    //Create the object property because it does not already exist
    bigfathom_util.env.quadrant = {
        "version": "20160828.1"
    };
}

bigfathom_util.env.quadrant.manager = function (canvas)
{
    var baseline_corefacts = {
        "canvas": canvas,
        "lastquadnum":4,
        "firstquadnum":1
    };
    var corefacts = {};
    var methods = {};
    var action_manager = {
        "info":'action manager for quadrants display',
        "corefacts": corefacts,
        "methods": methods
    };
    
    methods.getContainerFillColor = function (quadnum)
    {
        switch(quadnum)
        {
            case 1: return "#FF99CC";
            case 2: return "#99FF33";
            case 3: return "#FFFF66";
            case 4: return "#F6F600";
        }
        return "red";
    };

    methods.getContainerStrokeColor = function (quadnum)
    {
        switch(quadnum)
        {
            case 1: return "#FF0000";
            case 2: return "#FF3300";
            case 3: return "#FF3399";
            case 4: return "#000000";
        }
        return "red";
    };

    methods.getContainerText = function (quadnum)
    {
        switch(quadnum)
        {
            case 1: return "Quadrant 1: IMPORANT and now URGENT too!";
            case 2: return "Quadrant 2: IMPORANT and not yet urgent. (Best habit!)";
            case 3: return "Quadrant 3: NOT IMPORANT but expected now IF it will be done.";
            case 4: return "Quadrant 4: NOT IMPORANT and NOT expected at this time.";
        }
        return "error: no text for " + quadnum;
    };

    methods.getContainerTextAsParts = function (quadnum)
    {
        switch(quadnum)
        {
            case 0: return {"title":"Quadrant 1", "description":"IMPORANT and now URGENT too!"};
            case 1: return {"title":"Quadrant 2", "description":"IMPORANT and not yet urgent. (Best habit!)"};
            case 2: return {"title":"Quadrant 3", "description":"NOT IMPORANT but expected now IF it will be done."};
            case 3: return {"title":"Quadrant 4", "description":"NOT IMPORANT and NOT expected at this time."};
        }
        return  {"title":"error: no text for " + quadnum};
    };

    /**
     * Builds 'lanes' array and other properties to the corefacts and returns it
     * 0 = hierarchy integration lane
     * 1 = unassigned content lane
     */
    methods.getCoreFacts = function () 
    {
        if(typeof corefacts === 'undefined')
        {
            throw "How can we be missing corefacts in getCoreFacts???";
        }
        
        //Already defined?
        if(corefacts.hasOwnProperty("defined") && corefacts.defined)
        {
            //Yes, don't bother computing these again
            return corefacts;
        }
        
        //Initialize the corefacts with baseline information first.
        corefacts = baseline_corefacts;
        
        //Add our new stuff now.
        var visible_coordinates = corefacts.canvas.getVisibleRectangleFacts();   
        
        //Return the new corefacts.
        corefacts['visible_coordinates'] = visible_coordinates;
        return corefacts;
    };

    methods.getAllContainerAttribs = function ()
    {
        var attribs = {backgroundbundle:null, targets:null, corefacts:null};
        
        attribs.corefacts = this.getCoreFacts();
        attribs.backgroundbundle = this.getBackgroundElementsBundle();
        attribs.targets = null;
        
        return attribs;
    };
    
    /**
     * Return elements that have no drag-drop interaction
     */
    methods.getBackgroundElementsBundle = function ()
    {
        var result_bundle = {"elements": null, "keymap": null};
        var elements = [];
        var keymap = {};
        var key;
        for(var quadnum = corefacts.lastquadnum; quadnum >= corefacts.firstquadnum; quadnum--)
        {
            var onequad_attribs = this.getQuadrantAttributes(canvas, quadnum);
            
            var attribs = {};
            attribs["position"] = {
                x: onequad_attribs.target.x,
                y: onequad_attribs.target.y
            };
            
            var labelinfo = onequad_attribs.label;
            key = "label_" + quadnum;
            labelinfo["key"] = key;
            keymap[key] = quadnum;
            attribs["label"] = labelinfo;
                
            var containerinfo = onequad_attribs.target;  
            var id = "container" + quadnum;
            key = "container_" + quadnum;
            containerinfo["key"] = key;
            containerinfo["id"] = id;
            keymap[key] = quadnum;
            attribs["target"] = containerinfo;

            var elemkey = 'background_elem_' + quadnum;
            keymap[elemkey] = quadnum;
            var oneelement = {
                 'key': elemkey
                ,'type': "custom"
                ,'subtype': "Q" + quadnum
                ,'attribs' : attribs
            };

            elements.push(oneelement);
        }

        //Return the bundle
        result_bundle.elements = elements;
        result_bundle.keymap = keymap;
        
        return result_bundle;
    };
    
    /**
     * Return the quadrant attributes of one block
     */
    methods.getQuadrantAttributes = function (canvas, quadrant_num)
    {
        var attribs = {};

        var vgap = 10;
        var hlgap = 100;
        var hrgap = 50;
        var qbox_width = (canvas.w - hlgap - hrgap) / 2;
        var qbox_height = (canvas.h - vgap - vgap) / 2;
        var container_opacity = .2;
        var text_opacity = 1;

        var rect_x;
        var rect_y;

        var container_type;
        var subtype;
        var id = "container" + quadrant_num;

        container_type = "rect";
        subtype = "quadrant";
        switch(quadrant_num)
        {
            case 1: 
                rect_x = hlgap;
                rect_y = vgap;
                break;
            case 2:
                rect_x = hlgap + qbox_width;
                rect_y = vgap;
                break;
            case 3:
                rect_x = hlgap;
                rect_y = vgap + qbox_height;
                break;
            case 4:
                rect_x = hlgap + qbox_width;
                rect_y = vgap + qbox_height;
                break;
        }

        attribs["position"] = {x:rect_x, y:rect_y};
        attribs["label"] = {
                "type": "text",
                "maxwidth": qbox_width - qbox_width/10,
                "x": 0, //text_x,
                "y": 0, //text_y,
                "dx": 0,
                "dy": "1em",
                "text_anchor": "start",
                "fill_color": "black",
                "fill_opacity": text_opacity,
                "text": this.getContainerText(quadrant_num),
                "text_as_parts": this.getContainerTextAsParts(quadrant_num)
            };
        attribs["target"] = {
                  "type": container_type
                , "subtype": subtype
                , "id": id
                , "key": container_type + quadrant_num
                , "x": rect_x
                , "y": rect_y
                , "height": qbox_height
                , "width": qbox_width
                , "rx": 0
                , "ry": 0
                , "fill_color": this.getContainerFillColor(quadrant_num)
                , "fill_opacity": container_opacity
                , "stroke": this.getContainerStrokeColor(quadrant_num)
                , 'is_background': true
                , 'is_drag_source': false
                , 'is_drag_target': true
                , 'fixed': true
            };

        return attribs;
    };
    
    return action_manager;
};

