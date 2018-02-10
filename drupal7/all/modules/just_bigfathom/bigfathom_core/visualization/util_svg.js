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
if(!bigfathom_util.hasOwnProperty("svg"))
{
    //Create the object property because it does not already exist
    bigfathom_util.svg = {version: "20160701.1",
                          canvases: {}
                         };
}

/**
 * Create largest possible canvas inside element specified and return details
 */
bigfathom_util.svg.createCanvas = function (element_id
                    , pointer_events
                    , canvas_name
                    , enable_zoompan
                    , callbacks
                    , zlayer_count, pagetweaks
                    , popup_balloons)
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
    
    //TODO --- use the v4 way whatever that is!!!!!
    throw "TODO --- implement using D3v4!";
};
