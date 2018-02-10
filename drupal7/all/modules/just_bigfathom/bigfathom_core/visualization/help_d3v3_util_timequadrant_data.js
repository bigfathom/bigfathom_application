/* 
 * Functions for working with timequadrant data
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
if(!bigfathom_util.hasOwnProperty("timequadrant_data"))
{
    //Create the object property because it does not already exist
    bigfathom_util.timequadrant_data = {version: "20160828.1"};
}
  
/**
 * Get the environment items as part of our data
 */
bigfathom_util.timequadrant_data.initializeGraphContainerData = function (container_attribs)
{
    var graphdata = {'rootnodeid': null, 'last_node_offset':null, 'nodes': [], 'links':[], 'background':[], 'targets':[], 'methods': {}};
    var targetidx = 0;
    var key;

    //Create passive background elements now
    var subtype = [];
    var background_elements = container_attribs.backgroundbundle.elements;
    var background_element_count = background_elements.length;
    for(var idx = 0; idx < background_element_count; idx++)
    {
        subtype = ["rect"];
        var belem = background_elements[idx];
        key = belem.key;
        var onebackground_node = {
                  'type': "custom"
                , 'subtype': subtype
                , 'key': key
                , 'x': belem.attribs.position.x
                , 'y': belem.attribs.position.y
                , 'is_background': true
                , 'is_drag_source': false
                , 'is_drag_target': false
                , 'fixed': true
                , 'custom_detail': belem.attribs
            };
        graphdata.background.push(onebackground_node);
        if(isNaN(onebackground_node.x) || isNaN(onebackground_node.y))
        {
            throw "Failed initializeGraphContainerData because NaN found in " + JSON.stringify(onebackground_node);
        }
    }

    return graphdata;
};

/**
 * Return data structure we can pass to the environment manager
 */
bigfathom_util.timequadrant_data.getEnvironmentNodes = function(databundle)
{
    var env_defs = [];

    //TODO
            
    return env_defs;        
};


/**
 * Return the nodes that go into the environment
 */
bigfathom_util.timequadrant_data.getActorNodeBundle = function(container_attribs, databundle)
{
    var actorbundle = {};
    var newgraphdatanodes = [];

    var timequadrantdata = databundle.data;

    console.log("LOOK timequadrantdata=" + JSON.stringify(timequadrantdata));    
    console.log("LOOK container_attribs=" + JSON.stringify(container_attribs));

    var root_goalid =  timequadrantdata.root_goalid;
    var root_projectid =  timequadrantdata.root_projectid;
    var all_workitems = timequadrantdata.all_workitems;  //Not committed
    console.log("LOOK all_workitems=" + JSON.stringify(all_workitems));

    var offset_byid_tracker = {};
    var offset_bykey_tracker = {};
    var offset_track = 0;

    var default_opacity = .75;
    var is_assigned = false;
    var is_candidate = false;
    var rootnode = null;
    var is_rootnode;
    var label_dy;
    var hierarchy_level = null;
    var subtype = null;
    for(var itemid in all_workitems)
    {
        var oneitem = all_workitems[itemid];
        var typeletter = oneitem['typeletter'];
        var client_deliverable_yn = oneitem['client_deliverable_yn'];
        var is_project_root_yn = oneitem['root_of_projectid'] !== null;
        var typename = oneitem['type'];
        var key = oneitem['key'];
        if(!bigfathom_util.shapes.lib.keyprops.hasOwnProperty(typename)
                || !bigfathom_util.shapes.lib.keyprops[typename].hasOwnProperty('label')
                || !bigfathom_util.shapes.lib.keyprops[typename].label.hasOwnProperty('offset')
                )
        {
            label_dy = 0;
        } else {
            label_dy = bigfathom_util.shapes.lib.keyprops[typename].label.offset.dy;
        }
        hierarchy_level = null;
        is_rootnode = (itemid == root_goalid);  //Must use == not ===!!!
        if(is_project_root_yn)
        {
            subtype = ['hierarchy','project'];
        } else {
            subtype = ['hierarchy'];
        }
        if(client_deliverable_yn > 0)
        {
            subtype.push('deliverable');
        }

        var is_drag_source = hierarchy_level !== 1; //Do NOT allow the root to move!
        if(typename !== null)
        {
            //Include this one in the graph
            var key = typename + "_" + oneitem.id;
            if(true)
            {
                //This node is not already in another set, so add it here.
                if(typeletter === 'Q')
                {
                    subtype.push("is_equjb");
                } 
                else if(typeletter === 'X')
                {
                    subtype.push("is_xrcjb");
                }
                var label = {
                    'text': oneitem.workitem_nm, 
                    'dy': label_dy
                };
                label['owner_projectid'] = oneitem.owner_projectid;

                var xpos = 111;
                var ypos = 111;
                if(isNaN(xpos) || isNaN(ypos))
                {
                    throw "Failed getActorNodeBundle because NaN (" 
                            + xpos + "," + ypos 
                            + ") found in " + JSON.stringify(container_attribs.corefacts);
                }
                var is_drag_source = false;
                var is_drag_target = false;
                var status_cd = oneitem.status_cd;
                var lanenum = null;
                
                var onenode = bigfathom_util.nodes.getNewNode(is_rootnode, is_candidate, oneitem.id, key
                                                                , typename, subtype, label
                                                                , default_opacity
                                                                , xpos, ypos
                                                                , is_drag_source
                                                                , is_drag_target
                                                                , lanenum
                                                                , hierarchy_level
                                                                , status_cd);
                if(is_rootnode)
                {
                    rootnode = onenode;
                }

                newgraphdatanodes.push(onenode);
                offset_byid_tracker[itemid] = offset_track;
                offset_bykey_tracker[key] = offset_track;
                offset_track++;
            }
        }
    }
    
    //Package up all our results
    actorbundle['node_id2offset'] = offset_byid_tracker;
    actorbundle['node_key2offset'] = offset_bykey_tracker;
    actorbundle['nodes'] = newgraphdatanodes;
    return actorbundle;
};
