/* 
 * Functions for working with brainstorm data
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
if(!bigfathom_util.hasOwnProperty("brainstorm_data"))
{
    //Create the object property because it does not already exist
    bigfathom_util.brainstorm_data = {version: "20160814.1"};
}
  
/**
 * Get the environment items as part of our data
 */
bigfathom_util.brainstorm_data.initializeGraphContainerData = function (container_attribs)
{
    var graphdata = {nodes: [], links:[], methods: {}};
    
    //Define the targets now
    var container_count = container_attribs.count;
    var container_details = container_attribs.details;
    var targetidx = 0;
    for(var levelnum = container_count-1; levelnum >= 0; levelnum--)
    {
        var onelevel = container_details[levelnum];
        var type = onelevel.target.type;
        var subtype = [onelevel.target.subtype];
        graphdata.nodes.push({
                  'type': type
                , 'subtype': subtype
                , 'name': onelevel.target.id
                , 'x': onelevel.position.x
                , 'y': onelevel.position.y
                , 'is_drag_source': false
                , 'is_drag_target': true
                , 'targetlevel': levelnum
                , 'target_r': onelevel.target.r
                , 'into_trash': false
                , 'into_parkinglot': false
                , 'fixed': true
                , 'custom_detail': onelevel
                , 'fill_opacity': bigfathom_util.brainstorm.default_workitem_opacity
                , 'key': "fixed_" + targetidx++
            });
    }
    return graphdata;
};

/**
 * Return data structure we can pass to the environment manager
 */
bigfathom_util.brainstorm_data.getEnvironmentNodes = function(databundle)
{
    var env_defs = [];

    //TODO
            
    return env_defs;        
};


/**
 * Return the nodes that go into the environment
 */
bigfathom_util.brainstorm_data.getActorNodes = function(container_attribs, databundle)
{
    var newgraphdatanodes = [];

    var brainstormdata = databundle.data;
    
    
    var cdetails = container_attribs.details;
    var half_lane_width = cdetails[1].target.width / 2;

    var start_x = [];
    start_x[1] = cdetails[1].target.x + half_lane_width;
    start_x[2] = cdetails[2].target.x + half_lane_width;
    start_x[3] = cdetails[3].target.x + half_lane_width;
    var start_y = [];    //mycanvas.h / 2;
    start_y[1] = 10;
    start_y[2] = 10;
    start_y[3] = 10;
    var subtype;
    //var targetidx = 100;
    for(var brainstormid in brainstormdata)
    {
        var oneitem = brainstormdata[brainstormid];
        var typename;
        var levelnum;

        if(oneitem.candidate_type === 'G')
        {
            levelnum = 2;
            typename = "goal";
            subtype = [typename, "movable","candidate"];
        } else
        if(oneitem.candidate_type === 'T')
        {
            levelnum = 3;
            typename = "task";
            subtype = [typename, "movable","candidate"];
        } else {
            levelnum = 1;
            typename = "brainstorm";
            subtype = [typename, "movable"];
        }
        var onenode = {
                  'type': typename
                , 'subtype': subtype
                , 'label': oneitem.item_nm
                , 'nativeid': brainstormid
                , 'x': start_x[levelnum]
                , 'y': start_y[levelnum]++    //Move down one each time
                , 'is_drag_source': true
                , 'is_drag_target': false
                , 'assignment': levelnum
                , 'targetlevel': null
                , 'into_trash': (oneitem.active_yn == 0)
                , 'into_parkinglot': false //(oneitem.parkinglot_level == 1)
                , 'fixed': false
                , 'custom_detail': null
                , 'fill_opacity': bigfathom_util.brainstorm.default_workitem_opacity
                , 'key': "fromdb_" + brainstormid //targetidx++
            };
        newgraphdatanodes.push(onenode);
    }
    
    return newgraphdatanodes;
};
