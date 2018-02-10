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
}
if(!bigfathom_util.hasOwnProperty("hierarchy_data"))
{
    //Create the object property because it does not already exist
    bigfathom_util.hierarchy_data = {
        "version": "20170808.1",
        "TOO_MANY_ITERATIONS": 1000
    };
}

/**
 * Reposition the background elements
 */
bigfathom_util.hierarchy_data.redimensionGraphData = function (container_attribs, graphdata)
{
    var nodes = graphdata.nodes;
    if(nodes.constructor !== Array)
    {
        throw "Expected nodes variable to be an array instead it is " + typeof nodes;
    }
    var backgroundbundle = container_attribs.backgroundbundle;
    var background_elements = backgroundbundle.elements;
    var background_keymap = backgroundbundle.keymap;
    
    var nodescount = nodes.length;
    for (var i = 0; i < nodescount; i ++)
    {
        var onenode = nodes[i];
        if(onenode.is_background)
        {
            var key = onenode.key;
            var offset = background_keymap[key];
            var belem = background_elements[offset];
            onenode['x'] = belem.attribs.position.x;
            onenode['y'] = belem.attribs.position.y;
            onenode['custom_detail'] = belem.attribs;
        }
    }
};

/**
 * Find the matching node offset
 * TODO: Improve this so it uses indexes!
 */
bigfathom_util.hierarchy_data.getOneNodeOffsetByNativeID = function(nodes, nativeid)
{
    for(var idx = 0; idx < nodes.length; idx++)
    {
        var onenode = nodes[idx];
        if(onenode.nativeid === nativeid)
        {
            return idx;
        }
    }
    return null;
};

/**
 * Find the matching node instance
 */
bigfathom_util.hierarchy_data.getOneNodeByNativeID = function(nodes, typename, nativeid)
{
    for(var idx = 0; idx < nodes.length; idx++)
    {
        var onenode = nodes[idx];
        if(onenode.type === typename && onenode.nativeid === nativeid)
        {
            return onenode;
        }
    }
    return null;
};

/**
 * Initialize placeholders and get the environment items as part of our data
 */
bigfathom_util.hierarchy_data.initializeGraphContainerData = function (container_attribs)
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
        if(idx === 0)
        {
            subtype = ["hierarchy_area"];
        } else {
            subtype = ["candidate_area"];
        }
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

    //Create the static targets now
    subtype = ['target'];
    var target_count = container_attribs.targets.length;
    for(var laneidx = target_count-1; laneidx > 0; laneidx--)
    {
        var onetarget = container_attribs.targets[laneidx];
        var onetarget_node = {
                  'type': "custom"
                , 'subtype': subtype
                , 'key': targetidx++
                , 'x': onetarget.position.x
                , 'y': onetarget.position.y
                , 'is_background': false
                , 'is_drag_source': false
                , 'is_drag_target': true
                , 'targetlevel': laneidx    //redundant with hierarchy_level???
                , 'fixed': true
                , 'custom_detail': onetarget
            };
        graphdata.targets.push(onetarget_node);
        if(isNaN(onetarget_node.x) || isNaN(onetarget_node.y))
        {
            throw "Failed initializeGraphContainerData because NaN found in " + JSON.stringify(onetarget_node);
        }
    }
    return graphdata;
};

/**
 * Tell us if the node is in a terminal state
 */
bigfathom_util.hierarchy_data.hasTerminalStatus = function(nodedetail, status_cd_lookup)
{
    var status_detail;
    if(!nodedetail.hasOwnProperty("status_cd") || typeof nodedetail.status_cd === 'undefined')
    {
        //This will happen for candidate nodes
        status_detail.terminal_yn = 1;
    } else {
        var status_cd;
        status_cd = nodedetail.status_cd;
        status_detail = status_cd_lookup[status_cd];
        if(typeof status_detail === "undefined")
        {
            console.log("Expected to find status_detail for code=" + status_cd + " nodedetail=" + JSON.stringify(nodedetail) + " >>>>> status_cd_lookup=" + JSON.stringify(status_cd_lookup));
            throw "ERROR detected in hasTerminalStatus: Missing status_cd in status_cd_lookup!";
        }
    }
    return status_detail.terminal_yn === 1 || status_detail.terminal_yn === '1';
};


bigfathom_util.hierarchy_data.getUpdatedNodeInfo = function(graphdata, existingnodeinfo, newnodeinfo)
{
    var show_node = true;
    var show_children = true;
    var is_rootnode = false;
    var is_drag_source = true;
    var is_drag_target = false;
    if(existingnodeinfo === null)
    {
        existingnodeinfo = {};
    } else {
        if(typeof existingnodeinfo.show_node !== "undefined")
        {
            show_node = existingnodeinfo.show_node;
        }
        if(typeof existingnodeinfo.show_children !== "undefined")
        {
            show_children = existingnodeinfo.show_children;
        }
        if(typeof existingnodeinfo.is_rootnode !== "undefined")
        {
            is_rootnode = existingnodeinfo.is_rootnode;
        }
        if(typeof existingnodeinfo.is_drag_source !== "undefined")
        {
            is_drag_source = existingnodeinfo.is_drag_source;
        }
        if(typeof existingnodeinfo.is_drag_target !== "undefined")
        {
            is_drag_target = existingnodeinfo.is_drag_target;
        }
    }
    var id;
    var subtype;
    var assignment;
    var hierarchy_level;
    var xpos;
    var ypos;
    var key;
    var label_dy;
    var labelobj;
    var typename = newnodeinfo.type;
    var maps;
    var is_candidate;
    if(newnodeinfo.hasOwnProperty('brainstormid'))
    {
        id = "c" + newnodeinfo.brainstormid;
        subtype = ['candidate'];
        is_candidate = true;
    } else {
        id = newnodeinfo.nativeid;
        subtype = [];
        is_candidate = false;
    }
    if(newnodeinfo.hasOwnProperty('maps'))
    {
        maps = newnodeinfo.maps;
    } else {
        maps = {'getUpdatedNodeInfo':'empty'};
    }
    maps['owner_personid'] = newnodeinfo.owner_personid;   //Add this one to the map too
    
    hierarchy_level = null;
    xpos = 111; //TODO container_attribs.corefacts.lanes[lanenum - 1].start_x;
    ypos = 1;   //TODO container_attribs.corefacts.vgap;
    key = typename + "_" + id;
    if(newnodeinfo.typeletter === 'Q')
    {
        subtype.push("is_equjb");
    } 
    else if(newnodeinfo.typeletter === 'X')
    {
        subtype.push("is_xrcjb");
    }
    if(!bigfathom_util.shapes.lib.keyprops.hasOwnProperty(typename)
            || !bigfathom_util.shapes.lib.keyprops[typename].hasOwnProperty('label')
            || !bigfathom_util.shapes.lib.keyprops[typename].label.hasOwnProperty('offset')
            )
    {
        label_dy = 0;
    } else {
        label_dy = bigfathom_util.shapes.lib.keyprops[typename].label.offset.dy;
    }
    labelobj = {'text': newnodeinfo.workitem_nm
            , 'dy': label_dy};
    labelobj['owner_projectid'] = graphdata.projectid;

    if(is_candidate || hierarchy_level == null)
    {
        assignment = bigfathom_util.env.multilevelhierarchy.unassigned_lane;
    } else {
        assignment = bigfathom_util.env.multilevelhierarchy.hierarchy_lane;
    }

    var status_cd;
    if(typeof newnodeinfo.status_cd !== "undefined")
    {
        status_cd = newnodeinfo.status_cd;    
    } else
    if(typeof newnodeinfo.status_cd !== "undefined")
    {
        status_cd = newnodeinfo.status_cd;    
    } else {
        status_cd = "B";
    }
    var status_detail = graphdata.status_cd_lookup[status_cd];
    status_detail.offset = bigfathom_util.shapes.lib.keyprops[typename].status_cd.offset;
    var lanenum = assignment;
    var onenode = bigfathom_util.nodes.getNewNode(is_rootnode, is_candidate, id, key
                                                    , typename, subtype, labelobj
                                                    , bigfathom_util.hierarchy.default_workitem_opacity
                                                    , xpos, ypos
                                                    , is_drag_source
                                                    , is_drag_target
                                                    , lanenum
                                                    , hierarchy_level
                                                    , status_cd
                                                    , show_node
                                                    , show_children
                                                    , status_detail, maps);
    return onenode;                                                
};

/**
 * Update the data and synchronize with environment tracking structures
 * Return TRUE if the newdata changed the graphdata.
 */
bigfathom_util.hierarchy_data.updateGraphdataWithChanges = function(graphdata, newdata, env_methods)
{
    var needs_redraw = false;
    var delta = bigfathom_util.hierarchy_data.getFreshDataDelta(graphdata, newdata); 
    var update_graphdata = delta.diff_count > 0;
    if(update_graphdata)
    {
        needs_redraw = true;
        //Change the graphdata here now
        var newgraphnodes = [];
        var changedgraphnodes = [];
        var removednodeids = [];
        for(var i=0; i<delta.nodes.new.length; i++)
        {
            var newnodeinfo = delta.nodes.new[i];
            var onenode = bigfathom_util.hierarchy_data.getUpdatedNodeInfo(graphdata, null, newnodeinfo);
            newgraphnodes.push(onenode);
        }
        changedgraphnodes = delta.nodes.changed;
        removednodeids = delta.nodes.removed;

        var newlinks = [];
        var removedlinks = [];
        for(var i=0; i<delta.links.new.length; i++)
        {
            var linkinfo = delta.links.new[i];
            console.log("LOOK create new graphnode for linkinfo=" + JSON.stringify(linkinfo));
            newlinks.push(linkinfo);
        }
        for(var i=0; i<delta.links.removed.length; i++)
        {
            var linkinfo = delta.links.removed[i];
            console.log("LOOK create removed graphnode for linkinfo=" + JSON.stringify(linkinfo));
            removedlinks.push(linkinfo);
        }
        
        //Apply changes to existing nodes
        var change_count=0;
        var made_changes = false;
        var tmp_fast_nodelookup = graphdata.fastlookup_maps.nodes.id2offset;
        var lookupid;
console.log("LOOK tmp_fast_nodelookup=" + JSON.stringify(tmp_fast_nodelookup));        
        var insert_after = graphdata.last_node_offset;
        for(var i=0; i<changedgraphnodes.length; i++)
        {
            var nodeinfo = changedgraphnodes[i];
            if(nodeinfo.hasOwnProperty("brainstormid") && nodeinfo.brainstormid !== null)
            {
                lookupid = "c" + nodeinfo.id;
            } else {
                lookupid = nodeinfo.id;
            }
            var offset = tmp_fast_nodelookup[lookupid];
console.log("LOOK nodeinfo=" + JSON.stringify(nodeinfo));
            var onenode = graphdata.nodes[offset];
            onenode.type = nodeinfo.type;
            onenode.label.text = nodeinfo.workitem_nm;
            if(!nodeinfo.hasOwnProperty("brainstormid") || nodeinfo.brainstormid === null)
            {
                onenode.status_cd = nodeinfo.status_cd;
                onenode.status_detail = graphdata.status_cd_lookup[nodeinfo.status_cd];
            }
            onenode.is_changed = true;
            made_changes=true;
            change_count++;
        }
        //Insert new nodes
        for(var i=0; i<newgraphnodes.length; i++)
        {
            var newnode = newgraphnodes[i];
console.log("LOOK newnode=" + JSON.stringify(newnode));
            graphdata.nodes.splice(insert_after,0,newnode);
            insert_after++;
            tmp_fast_nodelookup[newnode.nativeid] = insert_after;
            made_changes=true;
            change_count++;
        }
console.log("LOOK NEW tmp_fast_nodelookup=" + JSON.stringify(tmp_fast_nodelookup));        
        //Remove existing nodes from highest offset to lowest
        var removenodeoffsets = [];
        for(var i=0; i<removednodeids.length; i++)
        {
            var removenodeid = removednodeids[i];
            var removeoffset = tmp_fast_nodelookup[removenodeid];
            removenodeoffsets.push(removeoffset);
        }
        removenodeoffsets.sort();
        for(var i=removenodeoffsets.length-1; i>-1; i--)
        {
            var removeoffset = removenodeoffsets[i];
            graphdata.nodes.splice(removeoffset,1);
            insert_after--;
            made_changes=true;
            change_count++;
        }
        if(made_changes)
        {
            //Refresh the lookup structures now before we attempt link changes
            env_methods.refreshAllLookupMaps(graphdata);
            made_changes = false;   //Reset
        }
        
        //Now update the linksmaster with our changes
console.log("LOOK delta.links=" + JSON.stringify(delta.links));        
console.log("LOOK removedlinks=" + JSON.stringify(removedlinks));        
console.log("LOOK newlinks=" + JSON.stringify(newlinks));
console.log("LOOK graphdata.linksmaster=" + JSON.stringify(graphdata.linksmaster));
console.log("LOOK newdata.wi2wi=" + JSON.stringify(newdata.wi2wi));

        //Now change the link structures
        if(removedlinks.length > 0)
        {
            var tmp_fast_removelookup = {};
            for(var i=0; i<removedlinks.length; i++)
            {
                var removelink = removedlinks[i];
console.log("LOOK removelink = " + removelink);
                tmp_fast_removelookup[removelink] = true;
            }
            var link_by_wid = graphdata.linksbundle.link_by_wid;
            for(var i = 0; i < link_by_wid.length; i++)
            {
                var onelink = link_by_wid[i];
                var trgwid = onelink.trgwid;
                var srcwid = onelink.srcwid;
                var srcno = graphdata.fastlookup_maps.nodes.id2offset[srcwid];
                var trgno = graphdata.fastlookup_maps.nodes.id2offset[trgwid];
                var srcnode = graphdata.nodes[srcno];
                var trgnode = graphdata.nodes[trgno];
                var lookupkey = srcwid + "," + trgwid;
                if(tmp_fast_removelookup.hasOwnProperty(lookupkey))
                {
                    env_methods.removeDirectedLink(graphdata, srcwid, trgwid);
                }
            }
            made_changes = true;
        }
        if(newlinks.length > 0)
        {
            //Insert new links
            for(var i=0; i<newlinks.length; i++)
            {
                var newlink = newlinks[i];
                var parts = newlink.split(",");
                var srcwid = parts[0];
                var trgwid = parts[1];
                var srcno = graphdata.fastlookup_maps.nodes.id2offset[srcwid];
                var trgno = graphdata.fastlookup_maps.nodes.id2offset[trgwid];
                var srcnode = graphdata.nodes[srcno];
                var trgnode = graphdata.nodes[trgno];
                if(typeof srcnode === 'undefined' || typeof trgnode === 'undefined')
                {
                    console.log("DEBUG graphdata.nodes=" + JSON.stringify(graphdata.nodes));
                    console.log("DEBUG srcwid=" + srcwid + " trgwid=" + trgwid);
                    console.log("DEBUG srcno=" + srcno + " trgno=" + trgno);
                    console.log("DEBUG trgnode=" + JSON.stringify(trgnode));
                    console.log("DEBUG srcnode=" + JSON.stringify(srcnode));
                    throw "Corrupted graphdata cannot find a node by offset on update operation!";
                }
                env_methods.createNewLinkForUI(graphdata, srcnode, trgnode);
            }
            made_changes = true;
        }
        if(made_changes)
        {
            env_methods.refreshAllLookupMaps(graphdata);
        }
        
//alert("LOOK at the *branch_rootids values now!");

        //Next add all the new links
        /*
        for(var i=0; i<fresh_list_of_wi2wi.length; i++)
        {
            var w2w = fresh_list_of_wi2wi[i];
            var depid = w2w.depwiid;
            var antid = w2w.antwiid;

        }    
        */
        //Next remove deleted links

        //Next remove deleted nodes
    //alert("DEBUG DONE???? TO UPDATE GRAPH!!!!")    
        
    }    
    
    return needs_redraw;
};

/**
 * Return key information about the hierarchy in a friendly format
 * IMPORTANT: Flags branches as complete or incomplete
 */
bigfathom_util.hierarchy_data.getWorkitemTopologyInfo = function(data)
{
    var list_of_wi2wi;
    if(data.hasOwnProperty("wi2wi"))
    {
        //This is the name from raw data
        list_of_wi2wi = data.wi2wi;
    } else {
        //This is the name from graphdata
        list_of_wi2wi = data.linksmaster;
    }
    var root_goalid;
    if(data.hasOwnProperty("root_goalid"))
    {
        //This is the name from raw data
        root_goalid = data.root_goalid;
    } else {
        //This is the name from graphdata
        root_goalid = data.rootnodeid;
    }
    var root_projectid;
    if(data.hasOwnProperty("root_projectid"))
    {
        //This is the name from raw data
        root_projectid = data.root_projectid;
    } else {
        //This is the name from graphdata
        root_projectid = data.projectid;
    }
    var workitems_detail_lookup;
    if(data.hasOwnProperty("workitems_detail_lookup"))
    {
        //This is the name from raw data
        workitems_detail_lookup = data.workitems_detail_lookup;
    } else {
        //This is the name from graphdata
        workitems_detail_lookup = {};
        for(var i=0; i < data.nodes.length; i++)
        {
            var nodedetail = data.nodes[i];
            //Only workitems have nativeid, check for this.
            if(typeof nodedetail.nativeid !== "undefined")
            {
                workitems_detail_lookup[nodedetail.nativeid] = nodedetail;
            }
        }
    }
    var status_cd_lookup = data.status_cd_lookup;
    var level_info = {};
    var maxlevel = 1;
    var minlevel = 1;
    var rootlevel = 1;

    var depwi;
    var antwi;
    
    var hasTerminalStatus = function(nodeid)
    {
        var nodedetail = workitems_detail_lookup[nodeid];
        if(typeof nodedetail === 'undefined' || nodedetail === null)
        {
            //console.log("DEBUG did NOT find nodeid=" + nodeid + " in workitems_detail_lookup=" + JSON.stringify(workitems_detail_lookup));
            if(nodeid.substr(0,1) === 'c')
            {
                //Work around for now because we know candidate nodes are always terminal
                return true;
            }
            //This can happen if the node has been deleted or moved to trash or to parking lot
            return true;
            //throw "Error did not find expected nodeid=" + nodeid;
        }
        return bigfathom_util.hierarchy_data.hasTerminalStatus(nodedetail, status_cd_lookup);
        //var status_detail = status_cd_lookup[nodedetail.status_cd];
        //return status_detail.terminal_yn == 1;
    };

    var fast_depid_lookup = {};
    for(var i=0; i<list_of_wi2wi.length; i++)
    {
        var w2w = list_of_wi2wi[i];
        var depid = w2w.depwiid;
        var antid = w2w.antwiid;
        if(!fast_depid_lookup.hasOwnProperty(depid))
        {
            fast_depid_lookup[depid] = [];  
        }
        fast_depid_lookup[depid].push(antid);
    }    
    
    var getConnectedNodeIDs = function(branch_rootid)
    {
        var is_completed_branch = hasTerminalStatus(branch_rootid);
        var connectedids = [];
        var completed_branch_rootids = [];
        var incompleted_branch_rootids = [];
        connectedids.push(branch_rootid);
        var all_ants_completed = true;
        if(fast_depid_lookup.hasOwnProperty(branch_rootid))
        {
            var ants = fast_depid_lookup[branch_rootid];
            for(var i=0; i<ants.length; i++)
            {
                var antid = ants[i];
                var bundle = getConnectedNodeIDs(antid);
                if(bundle.connectedids.length > 0)
                {
                    connectedids = connectedids.concat(bundle.connectedids);  
                    completed_branch_rootids = completed_branch_rootids.concat(bundle.completed_branch_rootids);  
                    incompleted_branch_rootids = incompleted_branch_rootids.concat(bundle.incompleted_branch_rootids);  
                    all_ants_completed = all_ants_completed && bundle.is_completed_branch;
                }
            }
        }
        
        if(is_completed_branch && all_ants_completed)
        {
            //Add the root of this branch now if we still have completed flag.
            completed_branch_rootids = completed_branch_rootids.concat(branch_rootid);  
        } else {
            if(!all_ants_completed)
            {
                incompleted_branch_rootids = incompleted_branch_rootids.concat(branch_rootid);  
            }
        }
        var newbundle = {
                "is_completed_branch": is_completed_branch , 
                "connectedids": connectedids , 
                "incompleted_branch_rootids": incompleted_branch_rootids ,
                "completed_branch_rootids": completed_branch_rootids
            };
        return newbundle;
    };

    var iterations = 0;
    var changesfound = true;
    level_info[root_goalid] = {'id':root_goalid, 'level':rootlevel};    //Always have the root node!
    while(changesfound)
    {
        iterations++;
        if(iterations > bigfathom_util.hierarchy_data.TOO_MANY_ITERATIONS)
        {
            throw "Possible cycle found in the hierarchy definition! Aborting after " + iterations + " iterations through this definition: " + JSON.stringify(list_of_wi2wi);
        }
        changesfound=false;
        for(var i=0; i<list_of_wi2wi.length; i++)
        {
            var w2w = list_of_wi2wi[i];
            var depid = w2w.depwiid;
            var antid = w2w.antwiid;
            if(!level_info.hasOwnProperty(depid))
            {
                level_info[depid] = {'id':depid, 'level':rootlevel};
            }
            depwi = level_info[depid];
            if(!level_info.hasOwnProperty(antid))
            {
                level_info[antid] = {'id':antid, 'level':rootlevel};
            }
            antwi = level_info[antid];
            if(antwi.level <= depwi.level)
            {
                changesfound = true;
                level_info[antid].level = depwi.level+1;
                if(maxlevel < antwi.level)
                {
                    maxlevel = antwi.level;
                }
            }
        }
    }
    
    //Partition out the nodes that are not part of the root branch
    var connectionbundle = getConnectedNodeIDs(root_goalid);
    var assigned_list = connectionbundle.connectedids;
    var completed_branch_list = connectionbundle.completed_branch_rootids;
    var incompleted_branch_list = connectionbundle.incompleted_branch_rootids;
    
    var fast_lookup_completed_branch = {};
    for(var i=0; i < completed_branch_list.length; i++)
    {
        var id = completed_branch_list[i];
        fast_lookup_completed_branch[id] = id;
    }

    var fast_lookup_incompleted_branch = {};
    for(var i=0; i < incompleted_branch_list.length; i++)
    {
        var id = incompleted_branch_list[i];
        fast_lookup_incompleted_branch[id] = id;
    }
    
    var fast_lookup_assigned = {};
    for(var i=0; i < assigned_list.length; i++)
    {
        var id = assigned_list[i];
        fast_lookup_assigned[id] = id;
    }

    var fast_lookup_unassigned = {};
    for(var id in workitems_detail_lookup)
    {
        if(workitems_detail_lookup.hasOwnProperty(id))
        {
            if(!fast_lookup_assigned.hasOwnProperty(id))
            {
                fast_lookup_unassigned[id] = id;
            }
        }
    }   
    
    var counts = {};
    for(var id in level_info)
    {
        var info = level_info[id];
        if(info.level === 0 && root_goalid != id)
        {
            info.level = null;  //Not an assigned node    
        } else {
            if(!counts.hasOwnProperty(info.level))
            {
                counts[info.level] = 1;
            } else {
                counts[info.level] += 1;
            }
        }
    }

    var bundle = { 
          'root_projectid': root_projectid
        , 'root_goalid': root_goalid
        , 'minlevel': minlevel
        , 'maxlevel': maxlevel
        , 'level_assignment': level_info
        , 'assigned_nodeids': fast_lookup_assigned
        , 'unassigned_nodeids': fast_lookup_unassigned
        , 'completed_branch_rootids': fast_lookup_completed_branch
        , 'incompleted_branch_rootids': fast_lookup_incompleted_branch
        , 'level_node_count': counts};

    return bundle;
};

bigfathom_util.hierarchy_data.getFreshDataDelta = function(existinggraphdata, freshdata) 
{
    //console.log("LOOK debug starting getFreshDataDelta...");
    var diff_count = 0;
    var newnodes = [];
    var changednodes = [];
    var removednodes = [];
    var newlinks = [];
    var changedlinks = [];
    var removedlinks = [];
    var result = {};
    
    var fresh_wi2wi = freshdata.wi2wi;
    var candidate_workitems = freshdata.candidate_workitems;
    var workitems_detail_lookup = freshdata.workitems_detail_lookup;

    var tmp_existing_linksmaster_fastcheck = {};
    for(var i=0; i<existinggraphdata.linksmaster.length; i++)
    {
        var onelinkdef = existinggraphdata.linksmaster[i];
        var fwckey = onelinkdef.antwiid + "," + onelinkdef.depwiid;
        tmp_existing_linksmaster_fastcheck[fwckey] = true;
    }

    var node_id2offset = existinggraphdata.fastlookup_maps.nodes.id2offset;
    var existing_nodes = existinggraphdata.nodes;
    
    //Walk though the new links
    var freshdata_fastlookuplinks = {};
    for(var i=0; i<fresh_wi2wi.length; i++)
    {
        //var isnew = true;
        var onelinkdef = fresh_wi2wi[i];
        var srcwid = onelinkdef.antwiid;
        var trgwid = onelinkdef.depwiid;
        var fwckey = srcwid + "," + trgwid;
        freshdata_fastlookuplinks[fwckey] = true;
        if(!tmp_existing_linksmaster_fastcheck.hasOwnProperty(fwckey))
        {
            newlinks.push(fwckey);
            diff_count++;
        }
    }

console.log("LOOK we have existinggraphdata.nodes=" + JSON.stringify(existinggraphdata.nodes));
console.log("LOOK tmp_existing_linksmaster_fastcheck=" + JSON.stringify(tmp_existing_linksmaster_fastcheck));
console.log("LOOK freshdata_fastlookuplinks=" + JSON.stringify(freshdata_fastlookuplinks));
    for(var fwckey in tmp_existing_linksmaster_fastcheck)
    {
        if(tmp_existing_linksmaster_fastcheck.hasOwnProperty(fwckey))
        {
            if(!freshdata_fastlookuplinks.hasOwnProperty(fwckey) || !freshdata_fastlookuplinks[fwckey])
            {
console.log("LOOK did NOT find fwckey=" + fwckey);                
                removedlinks.push(fwckey);
                diff_count++;
            }
        }
    }

    //Walk through the nodes looking for changes
    for(var wid in node_id2offset)
    {
        var offset = node_id2offset[wid];
        var existingnode = existing_nodes[offset];
        if(!workitems_detail_lookup.hasOwnProperty(wid))
        {
            if(wid.substring(0,1) !== 'c')
            {
                removednodes.push(wid);
                diff_count++;
            } else {
                var nativeid = wid.substring(1);
                if(!candidate_workitems.hasOwnProperty(nativeid))
                {
                    removednodes.push(wid);
                    diff_count++;
                }
            }
        }
    }
    for(var wid in workitems_detail_lookup)
    {
        if(workitems_detail_lookup.hasOwnProperty(wid))
        {
            var freshnode = workitems_detail_lookup[wid];
            if(node_id2offset.hasOwnProperty(wid))
            {
                var offset = node_id2offset[wid];
                var existingnode = existing_nodes[offset];
                if(freshnode.type !== existingnode.type 
                        || freshnode.workitem_nm !== existingnode.workitem_nm
                        || freshnode.status_cd !== existingnode.status_cd
                        )
                {
                    //Change this one now
                    changednodes.push(freshnode);
                    diff_count++;
                }
            } else {
                newnodes.push(freshnode);
                diff_count++;
            }
        }
    }
    for(var wid in candidate_workitems)
    {
        var cwid = 'c' + wid;
        if(candidate_workitems.hasOwnProperty(wid))
        {
            var freshnode = candidate_workitems[wid];
            if(node_id2offset.hasOwnProperty(cwid))
            {
                var offset = node_id2offset[cwid];
                var existingnode = existing_nodes[offset];
                if(freshnode.type !== existingnode.type)
                {
                    //Change this one now
                    changednodes.push(freshnode);
                    diff_count++;
                }
            } else {
                newnodes.push(freshnode);
                diff_count++;
            }
        }
    }
    
    result["diff_count"] = diff_count;
    result["nodes"] = {};
    result["links"] = {};
    result["nodes"]["new"] = newnodes;
    result["nodes"]["changed"] = changednodes;
    result["nodes"]["removed"] = removednodes;
    result["links"]["new"] = newlinks;
    result["links"]["changed"] = changedlinks;
    result["links"]["removed"] = removedlinks;
    return result;
};

/**
 * Return structure we can pass to the lane manager
 */
bigfathom_util.hierarchy_data.getEnvironmentNodes = function(workitem_topology)   //databundle)
{
    var sublanes = [];  //One sublane for each hierarchy value
    
    var min_level = workitem_topology['minlevel'];
    var max_level = workitem_topology['maxlevel'];
    var node_count_each_lane = workitem_topology['level_node_count'];
    var max_nodes_one_sublane=0;
    for(var i = min_level; i <= max_level; i++)
    {
        var onenode = {
                'label': "level " + i,
                'hierarchy_level': i
            };
            
        sublanes.push(onenode);
    }
    for(var level in node_count_each_lane)
    {
        if(node_count_each_lane[level] > max_nodes_one_sublane)
        {
            max_nodes_one_sublane = node_count_each_lane[level];
        }
    }    
    var lane_defs = [
            {'label':'Work Dependency Diagram'
                , 'is_simple_lane':false 
                , 'sublane_defs':sublanes
                , 'xxxxfill_color': '#59EBE8'
                , 'max_nodes_one_sublane': max_nodes_one_sublane
                , 'node_count_each_sublane' : node_count_each_lane
            },
            {'label':'Candidate Work'
                , 'is_simple_lane':true 
            }
        ];
        
    return lane_defs;        
};

/**
 * Return the node instance that matches the ID provided
 */
bigfathom_util.hierarchy_data.getNodeLiteral = function(itemid, nodes, node_id2offset)
{
    if(itemid === null || itemid === "")
    {
        throw "No ID provided!";
    }
    if(nodes.length < 0)
    {
        throw "No nodes defined!";
    }
    if(!node_id2offset.hasOwnProperty(itemid))
    {
        throw "No offset found for itemid=" + itemid + "!";
    }
    var offset = node_id2offset[itemid];
    return nodes[offset];
};

bigfathom_util.hierarchy_data.setAsCommittedWorkitem = function(onenode, new_nativeid)
{
    console.log("CHANGING into real node id=" + new_nativeid);
    onenode.is_candidate = false;
    onenode.nativeid = new_nativeid;
    bigfathom_util.nodes.changeSubtype(onenode, 'hierarchy', 'candidate');
    onenode.tooltip = {'id#' : onenode.nativeid + " (saved)",'level':onenode.hierarchy_level}; 
    console.log("CHANGED = " + JSON.stringify(onenode));
};

/**
 * @deprecated 
 */
bigfathom_util.hierarchy_data.setAsCommittedGoal = function(onenode, new_nativeid)
{
    console.log("CHANGING into real node id=" + new_nativeid);
    onenode.is_candidate = false;
    onenode.nativeid = new_nativeid;
    bigfathom_util.nodes.changeSubtype(onenode, 'hierarchy', 'candidate');
    onenode.tooltip = {'id#' : onenode.nativeid + " (saved)",'level':onenode.hierarchy_level}; 
    console.log("CHANGED = " + JSON.stringify(onenode));
};


bigfathom_util.hierarchy_data.getActorNodeBundle = function(container_attribs, workitem_topology, databundle)
{
    var actorbundle = {
        'node_id2offset':{}, //Offsets into the nodes array
        'node_key2offset':{},
        'nodes':[]  //Array of all the relevant nodes
    };
    var newgraphdatanodes = [];
    var unassigned_lanenum = 2;
    var hierarchy_lanenum = 1;
    
    var offset_track = 0;
    var offset_byid_tracker = {};
    var offset_bykey_tracker = {};
    var is_candidate;
    var subtype;
    var is_drag_target;
    var default_opacity = bigfathom_util.hierarchy.default_workitem_opacity;
    
    var status_cd_lookup = databundle.data.status_cd_lookup;
    var nodeHelper = bigfathom_util.nodes.getHelper(status_cd_lookup);
    
    var workitems_detail_lookup = databundle.data.workitems_detail_lookup;
    var level_assignment = workitem_topology.level_assignment;
    var root_goalid = workitem_topology.root_goalid;
    var hidden_nodeids = workitem_topology.hidden_nodeids;

    var candidate_workitems = databundle.data.candidate_workitems;  //Not committed
    var addCommittedNodes = function(items, is_assigned, item_nm_fieldname)
    {
        var lanenum = is_assigned ? hierarchy_lanenum : unassigned_lanenum;
        is_candidate = false;
        var rootnode = null;
        var is_rootnode;
        var label_dy;
        var hierarchy_level = null;
        for(var itemid in items)
        {
            if(!workitems_detail_lookup.hasOwnProperty(itemid))
            {
                console.log("LOOK WARNING missing itemid=" + itemid + " in workitems_detail_lookup=" + JSON.stringify(workitems_detail_lookup));
            } else {
                var oneitem = workitems_detail_lookup[itemid];
                var typeletter = oneitem['typeletter'];
                var client_deliverable_yn = oneitem['client_deliverable_yn'];
                var is_project_root_yn = (oneitem.hasOwnProperty('root_of_projectid') && oneitem['root_of_projectid'] !== null) 
                        || (oneitem.hasOwnProperty('root_of_tpid') && oneitem['root_of_tpid'] !== null);
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
                if(is_assigned)
                {
                    if(!level_assignment.hasOwnProperty(itemid) || level_assignment[itemid] == 'undefined')
                    {
                        console.log("DEBUG expected assigned node#" + itemid + " to have a level_assignment but it does not! oneitem=" + JSON.stringify(oneitem));
                        console.log("DEBUG workitem_topology=" + JSON.stringify(workitem_topology));
                        throw "ERROR did not find property levelinfo for " + itemid + " in " + JSON.stringify(level_assignment);
                    }
                    var levelinfo = level_assignment[itemid];
                    hierarchy_level = levelinfo.level;
                } else {
                    hierarchy_level = null;
                }
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
                    if(!offset_byid_tracker.hasOwnProperty(itemid))  //Skip members of other sets
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
                        var label = {'text': oneitem[item_nm_fieldname], 'dy': label_dy};
                        label['owner_projectid'] = oneitem.owner_projectid;

                        var xpos = container_attribs.corefacts.lanes[lanenum - 1].start_x;
                        var ypos = container_attribs.corefacts.vgap;
                        if(isNaN(xpos) || isNaN(ypos))
                        {
                            throw "Failed getActorNodeBundle because NaN (" 
                                    + xpos + "," + ypos 
                                    + ") found in " + JSON.stringify(container_attribs.corefacts);
                        }
                        is_drag_source = !is_rootnode;
                        is_drag_target = hierarchy_level===1 || !is_project_root_yn;
                        var status_cd = oneitem.status_cd;
                        var maps;
                        if(oneitem.hasOwnProperty('maps'))
                        {
                            maps = oneitem.maps;
                        } else {
                            maps = {'addCommittedNodes':'empty'};
                        }
                        maps['owner_personid'] = oneitem.owner_personid;   //Add this one to the map too
                        var status_detail = status_cd_lookup[status_cd];
                        status_detail.offset = bigfathom_util.shapes.lib.keyprops[typename].status_cd.offset;

                        var onenode = nodeHelper.getNewNode(is_rootnode, is_candidate, oneitem.id, key
                                                                        , typename, subtype, label
                                                                        , default_opacity
                                                                        , xpos, ypos
                                                                        , is_drag_source
                                                                        , is_drag_target
                                                                        , lanenum
                                                                        , hierarchy_level
                                                                        , status_cd
                                                                        , null, null
                                                                        , status_detail, maps);
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
        }
        return rootnode;
    };

    var addUncommittedNodes = function(items)
    {
        is_candidate = true;
        var lanenum = unassigned_lanenum;
        var is_rootnode = false;
        var label_dy;
        var hierarchy_level = null;
        var label_dy;
        var is_drag_source = true;
        var hierarchy_level = null;
        var status_cd = 'B';    //Default is Work Not Started
        for(var nativeid in items)
        {
            var subtype = ['candidate'];
            var oneitem = items[nativeid];
            var itemid = 'c' + nativeid;
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
            var key = "candidate_" + typename + "_" +  nativeid;
            if(offset_byid_tracker.hasOwnProperty(itemid))
            {
                throw "Failed in addUncommittedNodes because already created " + itemid + " >>> " + JSON.stringify(oneitem);
            }
            if(oneitem.equipmentid !== null)
            {
                subtype.push("is_equjb");
            } 
            else if(oneitem.external_resourceid !== null)
            {
                subtype.push("is_xrcjb");
            }
            var label = {'text': oneitem['workitem_nm'], 'dy': label_dy};
            var xpos = container_attribs.corefacts.lanes[lanenum - 1].start_x;
            var ypos = container_attribs.corefacts.vgap;
            
            var maps;
            if(oneitem.hasOwnProperty('maps'))
            {
                maps = oneitem.maps;
            } else {
                maps = {'addUncommittedNodes':'empty'};
            }
            maps['owner_personid'] = oneitem.owner_personid;   //Add this one to the map too
            
            var onenode = nodeHelper.getNewNode(is_rootnode, is_candidate, itemid, key, typename, subtype, label
                                                            , default_opacity, xpos, ypos
                                                            , is_drag_source, is_drag_target
                                                            , lanenum
                                                            , hierarchy_level
                                                            , status_cd
                                                            , null, null, null, maps);
            newgraphdatanodes.push(onenode);
            offset_byid_tracker[itemid] = offset_track;
            offset_bykey_tracker[key] = offset_track;
            offset_track++;
        }
    };
    
    //Add all the uncommitted
    addUncommittedNodes(candidate_workitems);
    
    //Now populate the node array
    addCommittedNodes(workitem_topology.assigned_nodeids, true, 'workitem_nm');
    addCommittedNodes(workitem_topology.unassigned_nodeids, false, 'workitem_nm');
    
    //Package up all our results
    actorbundle['rootnodeid'] = workitem_topology.root_goalid;
    actorbundle['node_id2offset'] = offset_byid_tracker;
    actorbundle['node_key2offset'] = offset_bykey_tracker;
    actorbundle['nodes'] = newgraphdatanodes;
    return actorbundle;
};
        
