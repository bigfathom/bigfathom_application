/* 
 * Library of utility functions for with a two lane hierarchy visualization environment
 * 
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * 
 * DESCRIPTION
 * Creates a two lane environment where lane#1 supports any number of sublanes.
 * Lane#2 is a simple lane.
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
if(!bigfathom_util.env.hasOwnProperty("multilevelhierarchy"))
{
    //Create the object property because it does not already exist
    bigfathom_util.env.multilevelhierarchy = {
        "version": "20180430.1", 
        "hierarchy_lane":1, 
        "unassigned_lane":2, 
        "show_unassigned_lane":true,
        "unassigned_lane_width": 300,
        "min_sublane_width": 300,
        "max_sublane_width": 700,
        "max_tree_levels": 100,
        "min_canvas_usable_width": 1100,
        "min_canvas_usable_height": 600,
        "check_canvas_size": true,
        "is_model_out_of_synch": false,
        "locks": {"offsets_changing_requester": null},
        "connector_logic": {"comfortable_max_nodes_per_level": 9, "collapseall_threshhold": 100, "zigzag_factor_threshhold": 3, "foci_per_level": 3}
        };
}

bigfathom_util.env.multilevelhierarchy.manager = function (canvas, lane_defs, context_type)
{
    var lane_count = lane_defs.length;
    var corefacts = {
        'canvas': canvas,
        'lane_defs': lane_defs,
        'lane_count': lane_count
    };
    if(typeof context_type === 'undefined')
    {
        corefacts.context_type = 'project';
    } else {
        corefacts.context_type = context_type;
    }
    var methods = {};
    var action_manager = {'info':'action manager for multi-level hierarchy',
        'corefacts': corefacts,
        'methods': methods
    };

    /**
     * Recomputes the position information for an existing sublane definition
     * @param {type} onesublane_def existing sublane def
     * @param {type} sublane_count number of sublanes
     * @param {type} sublaneidx zero offset of existing def
     * @param {type} indent space from left in sublane
     * @param {type} left_x left of the sublane
     * @param {type} top_y top of the sublane
     * @param {type} sublane_width the width
     * @param {type} sublane_height the height
     * @returns the new sublane def
     */
    var getRecomputedSublaneDef = function(onesublane_def
                    , sublane_count
                    , sublaneidx
                    , left_x, top_y 
                    , sublane_width, sublane_height)
    {
        var sublane_hpad = sublane_width / 10;
        var center_x = left_x + sublane_hpad;
        var center_y = top_y + (sublane_height / 2);
        var iseven = sublaneidx % 2;
        var zigzag_offset;
        if(iseven)
        {
            zigzag_offset = 0;
        } else {
            var zigzag_factor = (sublane_count < bigfathom_util.env.multilevelhierarchy.connector_logic.zigzag_factor_threshhold) ? 0 : sublane_height/8;
            var zigzag_map = sublaneidx % 4;
            if(zigzag_map === 1)
            {
                zigzag_offset = 1 * zigzag_factor;
            } else if(zigzag_map === 3) {
                zigzag_offset = -1 * zigzag_factor;
            } else {
                zigzag_offset = 0;
            }
        }
        onesublane_def['width'] = sublane_width;
        var content_center = {
                "x": center_x,
                "y": center_y + zigzag_offset
            };
        if(sublaneidx !== 0 && sublane_count > 2)
        {
            var y_zones_dy = sublane_height / 5;
            content_center['y_zones'] = {'dy':y_zones_dy
                , 'under':[(center_y - y_zones_dy)]
                , 'middle':center_y
                , 'over':[(center_y + y_zones_dy)]};
        }
        onesublane_def['content_center'] = content_center;
        return onesublane_def;
    };
    
    methods.getCanvasWarningMessage = function ()
    {
        var message = null;
        var f_w = Math.floor(canvas.w);
        var f_h = Math.floor(canvas.h);
        if(bigfathom_util.env.multilevelhierarchy.check_canvas_size)
        {
            if(f_w < bigfathom_util.env.multilevelhierarchy.min_canvas_usable_width || f_h < bigfathom_util.env.multilevelhierarchy.min_canvas_usable_height)
            {
                message = "Browser canvas is too small (w:" + f_w
                        + " px, h:" + f_h 
                        + ")! Recommend at least w:" 
                        + bigfathom_util.env.multilevelhierarchy.min_canvas_usable_width 
                        + "px, h:" + bigfathom_util.env.multilevelhierarchy.min_canvas_usable_height + "px; functionality may be impaired -- consider using a larger monitor.";
            }
            bigfathom_util.env.multilevelhierarchy.check_canvas_size = false;   //So we only get this message once
        }
        return message;
    };

    methods.getContainerFillColor = function (laneidx)
    {
        if(corefacts.lane_defs[laneidx].hasOwnProperty('fill_color'))
        {
            return corefacts.lane_defs[laneidx].fill_color;
        }
        if(corefacts.hasOwnProperty("context_type") && corefacts.context_type === 'template')
        {
            switch(laneidx)
            {
                case 0: return "#D3D6FF";
                case 1: return "#336699";
            }
        } else {
            switch(laneidx)
            {
                case 0: return "#84F4F9";
                case 1: return "#00CC99";
            }
        }
        return "red";
    };

    methods.getContainerStrokeColor = function (laneidx)
    {
        if(corefacts.lane_defs[laneidx].hasOwnProperty('stroke_color'))
        {
            return corefacts.lane_defs[laneidx].stroke_color;
        }
        switch(laneidx % 4)
        {
            case 0: return "black";
            case 1: return "#FF0000";
        }
        return "red";
    };

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
    };

    /**
     * Return the information detail about a lane
     */
    methods.getLaneInfo = function (lanenum)
    {
        var laneidx = 1;
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            laneidx = corefacts.lane_count;
            throw "ERROR getting lane info for laneidx=" + laneidx + " but requested invalid lanenum=" + lanenum;
        } else {
            laneidx = lanenum - 1;
        }
        return corefacts.lanes[laneidx];
    };

    /**
     * Return the sublane information
     */
    methods.getSublaneInfo = function (lanenum, hierarchy_level)
    {
        var onelane = methods.getLaneInfo(lanenum);
        var onesublane = onelane.sublanes[hierarchy_level-1];
        return onesublane;
    };

    methods.getLaneAssignmentAtPosition = function (x, y, failvalue)
    {
        var lanenum = failvalue;

        var laneidx = corefacts.lane_count;
        if(x > corefacts.lanes[laneidx].start_x)
        {
            //Indicates not assigned to an item
            lanenum = null;
        }

        //See if we are in a lane
        for(laneidx = 1; laneidx < corefacts.lane_count; laneidx++)
        {
            if(x >= corefacts.lanes[laneidx].start_x) //REVERSEDX
            {
                lanenum = laneidx;
                break;
            }
        }

        return lanenum;
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
        var background_opacity = .99;
        for(var i = 0; i < corefacts.lane_count; i++)
        {

            var onelane = corefacts.lanes[i];

            if(onelane.hasOwnProperty("top_legend"))
            {
                //Put the start date on a tickline
                var tick_attribs = {};
                tick_attribs["position"] = {
                    x: onelane.start_x, 
                    y: corefacts.tick_line_top
                };
                var ticktext = onelane.top_legend.start_text;
                key = "tick_attrib_" + i;
                keymap[key] = i;
                tick_attribs["label"] = {
                        "key": key,
                        "type": "text",
                        "maxwidth": onelane.width,
                        "x": 0,
                        "y": 0,
                        "dx": 0,
                        "dy": ".5em",
                        "text_anchor": "start",
                        "fill_color": "black",
                        "fill_opacity": 1,
                        "text": ticktext
                    };
                var id = "segment" + i;
                key = "segment_" + i;
                keymap[key] = i;
                tick_attribs["target"] = {
                        "key": key,
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
                    };
                var tickelemkey = 'tick_elem_' + i;
                keymap[tickelemkey] = i;
                var onetickelement = {
                     'key': tickelemkey
                    ,'attribs' : tick_attribs
                };
                elements.push(onetickelement);
            }

            //Lane attribs
            attribs = {};
            attribs["position"] = {
                x: onelane.start_x, 
                y: corefacts.lane_top
            };
            var labeltext = this.getContainerText(i+1);
            key = "label_" + i;
            keymap[key] = i;
            attribs["label"] = {
                    "key": key,
                    "type": "text",
                    "maxwidth": onelane.width,
                    "x": 0,
                    "y": 0,
                    "dx": 0,
                    "dy": "1em",
                    "text_anchor": "start",
                    "fill_color": "black",
                    "fill_opacity": 1,
                    "text": labeltext
                };
                
            var id = "container" + i;
            key = "container_" + i;
            keymap[key] = i;
            attribs["target"] = {
                    "key": key,
                    "type": "rect",
                    "id": id,
                    "x": 0,
                    "y": 0,
                    "height": corefacts.lane_height,
                    "width": onelane.width,
                    "rx": 0,
                    "ry": 0,
                    "fill_color": this.getContainerFillColor(i),
                    "fill_opacity": background_opacity,
                    "stroke": this.getContainerStrokeColor(i)
                };

            var elemkey = 'background_elem_' + i;
            keymap[elemkey] = i;
            var oneelement = {
                 'key': elemkey
                ,'type': "custom"
                ,'subtype': "L" + (i + 1)
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
     * Return elements that have a drag-drop interaction
     */
    methods.getTargetElements = function ()
    {
        var elements = [];

        return elements;
    };

    /**
     * Do not allow link of a parent to one of its existing children
     * parent=target
     * child=source
     */
    methods.isLegalLink = function(candidatelink_sourcenode, candidatelink_targetnode
                                    , currentnode
                                    , graphdata
                                    , iteration)
    {
        
        iteration++;
        
        if(candidatelink_sourcenode.key === candidatelink_targetnode.key)
        {
            //Not allowed to link to itself!
            return false;
        }
        if(iteration !== 1 && candidatelink_sourcenode.key === currentnode.key)
        {
            //Already in the child tree of the candidate
            return false;
        }
        if(candidatelink_targetnode.key === currentnode.key)
        {
            //Already in the child tree of the candidate
            return false;
        }

        //Check all the links in this branch
        var nodes = graphdata.nodes;
        var link_by_offset = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
        {
            var onelink = link_by_offset[linkidx];
            var targetnode = nodes[onelink.trgno];
            if(targetnode.key === currentnode.key)
            {
                //Check this subtree
                var sourcenode = nodes[onelink.srcno];
                var is_legal_link = methods.isLegalLink(candidatelink_sourcenode, candidatelink_targetnode
                                    , sourcenode
                                    , graphdata
                                    , iteration);
                if(!is_legal_link)
                {
                    return false;
                }
            }
        }
        
        return true;
    };

    /**
     * Return all the nodes of the branch with key as the node key
     * TODO optimize LINK structures to speed this up!!!!
     */
    methods.getAllNodesInBranch = function(rootnode
                                    , graphdata
                                    , iteration)
    {
        var nodes_in_branch = {};
        nodes_in_branch[rootnode.key] = rootnode;
        var nodes = graphdata.nodes;
        var links = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < links.length; linkidx++)
        {
            var onelink = links[linkidx];
            var targetnode = nodes[onelink.trgno];
            if(targetnode.key === rootnode.key)
            {
                //Check this subtree
                var sourcenode = nodes[onelink.srcno];
                var child_nodes = methods.getAllNodesInBranch(sourcenode
                                    , graphdata
                                    , iteration);
                for (var attrname in child_nodes) 
                { 
                    nodes_in_branch[attrname] = child_nodes[attrname]; 
                }                    
            }
        }
        return nodes_in_branch;
    };

    /**
     * Returns true if the node is linked to the tree rootnode
     */
    methods.isLinkedToTreeRoot = function(treerootnode, currentnode, graphdata)
    {
        var nodes = graphdata.nodes;
        var link_by_offset = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
        {
            var onelink = link_by_offset[linkidx];
            var sourcenode = nodes[onelink.srcno];
            if(sourcenode.key === currentnode.key)
            {
                var targetnode = nodes[onelink.trgno];
                if(targetnode.key === treerootnode.key)
                {
                    //Yes, we found link all the way to the tree root.
                    return true;
                } else {
                    if(methods.isLinkedToTreeRoot(treerootnode, targetnode, graphdata))
                    {
                        //Yes, done!
                        return true;
                    }
                }
            }
        }
        return false;
    };

    /**
     * Returns TRUE if a branch child node has a connection to another rooted branch
     */
    methods.hasTreeRootedChildOutsideBranch = function(treerootnode, rootnode
                                    , graphdata, nodes_in_branch)
    {
        if(rootnode.hierarchy_level === 1)
        {
            //This logic does not apply to the tree root
            return false;
        }

        if(typeof nodes_in_branch === 'undefined')
        {
            nodes_in_branch = methods.getAllNodesInBranch(rootnode
                                        , graphdata, 0);
        }

        //Check all the links in children of this branch
        var nodes = graphdata.nodes;
        var links = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < links.length; linkidx++)
        {
            var onelink = links[linkidx];
            var sourcenode = nodes[onelink.srcno];
            if(nodes_in_branch.hasOwnProperty(sourcenode.key))
            {
                //Check the target for membership in the branch
                var targetnode = nodes[onelink.trgno];
                if(!nodes_in_branch.hasOwnProperty(targetnode.key))
                {
                    //This target is NOT in the branch!
                    return methods.isLinkedToTreeRoot(treerootnode, targetnode, graphdata);
                }
            }
        }
        
        //No external connections
        return false;
    };

    /**
     * Returns TRUE if a branch child node has a connection to another branch
     */
    methods.hasChildOutsideBranchConnection = function(rootnode
                                    , graphdata, nodes_in_branch)
    {
        if(rootnode.hierarchy_level === 1)
        {
            //This logic does not apply to the tree root
            return false;
        }

        if(typeof nodes_in_branch === 'undefined')
        {
            nodes_in_branch = methods.getAllNodesInBranch(rootnode
                                        , graphdata, 0);
        }

        //Check all the links in children of this branch
        var nodes = graphdata.nodes;
        var links = graphdata.linksbundle.link_by_offset;
        for(var linkidx = 0; linkidx < links.length; linkidx++)
        {
            var onelink = links[linkidx];
            var sourcenode = nodes[onelink.srcno];
            if(nodes_in_branch.hasOwnProperty(sourcenode.key))
            {
                //Check the target for membership in the branch
                var targetnode = nodes[onelink.trgno];
                if(!nodes_in_branch.hasOwnProperty(targetnode.key))
                {
                    //This target is NOT in the branch!
                    return true;
                }
            }
        }
        
        //No external connections
        return false;
    };

    /**
     * Returns collection of all branch nexus nodes
     */
    methods.getBranchNexusNodes = function(rootnode, graphdata, nodes_in_branch)
    {
        var branch_nexus_nodes = {};
        if(rootnode.hierarchy_level !== 1)
        {
            if(typeof nodes_in_branch === 'undefined')
            {
                nodes_in_branch = methods.getAllNodesInBranch(rootnode
                                            , graphdata, 0);
            }

            //Check all the links in children of this branch
            var nodes = graphdata.nodes;
            var links = graphdata.linksbundle.link_by_offset;
            for(var linkidx = 0; linkidx < links.length; linkidx++)
            {
                var onelink = links[linkidx];
                var sourcenode = nodes[onelink.srcno];
                if(nodes_in_branch.hasOwnProperty(sourcenode.key))
                {
                    //Check the target for membership in the branch
                    var targetnode = nodes[onelink.trgno];
                    if(!nodes_in_branch.hasOwnProperty(targetnode.key))
                    {
                        //This target is NOT in the branch!
                        branch_nexus_nodes[targetnode.key] 
                                = nodes_in_branch.hasOwnProperty(targetnode.key);
                    }
                }
            }
        }
        
        //No external connections
        return branch_nexus_nodes;
    };

    /**
     * Returns true on success
     */
    methods.markOffsetsChanging = function(requester_name, fail_if_blocked)
    {
        if(typeof fail_if_blocked === 'undefined')
        {
            fail_if_blocked = true;
        }
        if(bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester === null)
        {
            bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester = requester_name;
            return true;
        } else {
            if(fail_if_blocked)
            {
                throw "Cannot mark offsets lock for " + requester_name + " because already locked by [" + bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester + "]";
            }
        }
        return false;
    };

    methods.clearOffsetsChanging = function(requester_name)
    {
        if(bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester === null || bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester === requester_name)
        {
            bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester = null;
        } else {
            throw "Cannot clear offsets lock for " + requester_name + " because value is currently [" + bigfathom_util.env.multilevelhierarchy.locks.offsets_changing_requester + "]";
        }
    };
    
    /**
     * Deletes ALL the link representations from s to t if found
     */
    methods.removeDirectedLink = function(graphdata, antwiid, depwiid) 
    {
        methods.markOffsetsChanging("removeDirectedLink", true); //TODO bubble up to caller instead for retry!!!!!
        
        //var nodes = graphdata.nodes;
        var linksmaster = graphdata.linksmaster;
        var linksbundle = graphdata.linksbundle;
        var key4fast_link_exists_byoffset_lookup;
        var key4fast_link_exists_bywid_lookup;
        var removeLinksBundleIdx = -1;
        var removeLinksMasterIdx = -1;
        
        for (var i=0;i<linksmaster.length;i++) 
        {
            var onelinkmaster = linksmaster[i];
            if(onelinkmaster.depwiid === depwiid && onelinkmaster.antwiid === antwiid)
            {
                removeLinksMasterIdx = i;
                break;
            }
        }
        if(removeLinksMasterIdx > -1)
        {
            linksmaster.splice(removeLinksMasterIdx,1);
            for (var i=0;i<linksbundle.link_by_wid.length;i++) 
            {
                var onelink_by_wid = linksbundle.link_by_wid[i];
                if(onelink_by_wid.trgwid === depwiid && onelink_by_wid.srcwid === antwiid)
                {
                    var onelink_by_offset = linksbundle.link_by_offset[i];
                    key4fast_link_exists_byoffset_lookup = onelink_by_offset.srcno + "," + onelink_by_offset.trgno;
                    key4fast_link_exists_bywid_lookup = antwiid + "," + depwiid;
                    removeLinksBundleIdx = i;
                    break;
                }
            }

            if(removeLinksBundleIdx > -1)
            {
                linksbundle.fast_link_exists_byoffset_lookup[key4fast_link_exists_byoffset_lookup] = false;
                linksbundle.fast_link_exists_bywid_lookup[key4fast_link_exists_bywid_lookup] = false;
                linksbundle.link_by_offset.splice(removeLinksBundleIdx,1);
                linksbundle.link_by_key.splice(removeLinksBundleIdx,1);
                linksbundle.link_by_wid.splice(removeLinksBundleIdx,1);
                methods.resetAllNodeHierarchy(graphdata, true);
            }
        }
        
        methods.clearOffsetsChanging("removeDirectedLink");
    };

    /**
     * Sets assignment of nodes to correct lane
     */
    methods.assignNodesToCorrectLanes = function(graphdata)
    {
        console.log("LOOK STARTING assignNodesToLanes TODO!!!!");
        //TODO
    };


    /**
     * Compute and move now to ideal positions for all the nodes
     * Before calling this make sure the nodes are already in correct sublanes
     */
    methods.moveToIdealNodePositions = function(graphdata, accordion_factor)
    {
        if (typeof accordion_factor === 'undefined')
        {
            accordion_factor = .5;
        };
        
        var proxybundle = methods.getIdealNodePositionProxyNodes(graphdata);
        var laneframework = proxybundle.laneframework;
        var max_level = proxybundle.max_level;
        var proxynodes = proxybundle.proxynodes;
        var nodes = graphdata.nodes;
        var hierarchy_lane_def = corefacts.lane_defs[0];
        var hierarchy_lane_facts = corefacts.lanes[0];
        var sublane_def;

        for(var hierarchy_level in laneframework)
        {
            if(hierarchy_level === 'undefined')
            {
                throw "ERROR hierarchy_level is not defined for laneframework=" + JSON.stringify(laneframework);
            }
            var sublanecontent = laneframework[hierarchy_level];
            var hlint = parseInt(hierarchy_level);
            var sublaneidx = hlint-1;
            if(hlint === 1)
            {
                //Position the root node
                var key = sublanecontent[sublaneidx];
                if(!proxynodes.allnodes.hasOwnProperty(key))
                {
                    throw "Did NOT find root node key=" + key + " in proxynodes.allnodes=" + JSON.stringify(proxynodes.allnodes);
                }
                sublane_def = hierarchy_lane_def.sublane_defs[0];
                var pnode = proxynodes.allnodes[key];
                var nodeidx = pnode.nodeidx;
                var realnode = nodes[nodeidx];
                realnode.ideal_y = sublane_def.content_center.y;
                realnode.ideal_x = sublane_def.start_x ;
                realnode.y = realnode.ideal_y;
                realnode.x = realnode.ideal_x;
                realnode.py = realnode.y;
                realnode.px = realnode.x;
                if(isNaN(realnode.x) || isNaN(realnode.y))
                {
                    throw "Failed moveToIdealNodePositions because NaN found in " + JSON.stringify(realnode);
                }
            } else {
                if(sublanecontent.length > 0)
                {
                    //Update the ideal y positions
                    if(!hierarchy_lane_def.sublane_defs.hasOwnProperty(sublaneidx))
                    {
                        console.log("ERROR DETECTED hierarchy_lane_def=" + JSON.stringify(hierarchy_lane_def));
                        console.log("ERROR TODO FIX THIS WE DID NOT FIND sublaneidx=" + sublaneidx 
                                + " in hierarchy_lane_def.sublane_defs=" 
                                + JSON.stringify(hierarchy_lane_def.sublane_defs));
                        var tmpidx = hierarchy_lane_def.sublane_defs.length - 1;
                        console.log("ERROR TODO FIX THIS WE DID NOT FIND sublaneidx=" + sublaneidx + " so we will use " + tmpidx);
                        throw "ERROR did not find sublane=" + sublaneidx + " in " + JSON.stringify(hierarchy_lane_def.sublane_defs);
                        sublane_def = hierarchy_lane_def.sublane_defs[tmpidx];
                    } else {
                        sublane_def = hierarchy_lane_def.sublane_defs[sublaneidx];
                    }

                    var range = corefacts.canvas.h;
                    var domain = hierarchy_lane_facts.width / max_level;
                    var xfactor = domain / sublanecontent.length;
                    var xbump = xfactor / 2;
                    var yfactor = range / sublanecontent.length;
                    var ybump = yfactor / 2;
                    for(var lnidx = 0; lnidx < sublanecontent.length; lnidx++)
                    {
                        var key = sublanecontent[lnidx];
                        if(!proxynodes.allnodes.hasOwnProperty(key))
                        {
                            throw "Did NOT find key=" + key + " in proxynodes.allnodes=" + JSON.stringify(proxynodes.allnodes);
                        }
                        var pnode = proxynodes.allnodes[key];
                        var nodeidx = pnode.nodeidx;
                        var realnode = nodes[nodeidx];
                        realnode.ideal_y = ybump + (lnidx * yfactor);
                        realnode.ideal_x = sublane_def.start_x * accordion_factor;
                        realnode.y = realnode.ideal_y;
                        realnode.x = realnode.ideal_x;
                        realnode.py = realnode.y;
                        realnode.px = realnode.x;
                        if(isNaN(realnode.x) || isNaN(realnode.y))
                        {
                            throw "Failed moveToIdealNodePositions because NaN found in " + JSON.stringify(realnode);
                        }
                        if(realnode.hierarchy_level !== hlint)
                        {
                            console.log("ERROR hl not the SAME for realnode " 
                                    + key + " hierarchy_level=" + realnode.hierarchy_level + " vs " + (hlint));
                        }
                    }
                }
            }
        }
   };

    /**
     * Set the current position to the ideal position
     */
    methods.setIdealNodePositions = function(graphdata)
    {
        var proxybundle = methods.getIdealNodePositionProxyNodes(graphdata);
        var laneframework = proxybundle.laneframework;
        var max_level = proxybundle.max_level;
        var proxynodes = proxybundle.proxynodes;
        var nodes = graphdata.nodes;
        var hierarchy_lane_facts = corefacts.lanes[0];
        
        for(var hierarchy_level in laneframework)
        {
            var sublanecontent = laneframework[hierarchy_level];
            var hlint = parseInt(hierarchy_level);
            if(hlint > 1)
            {
                if(sublanecontent.length > 0)
                {
                    //Update the ideal y positions
                    var range = corefacts.canvas.h;
                    var domain = hierarchy_lane_facts.width / max_level;
                    var xfactor = domain / sublanecontent.length;
                    var xbump = xfactor / 2;
                    var yfactor = range / sublanecontent.length;
                    var ybump = yfactor / 2;
                    for(var lnidx = 0; lnidx < sublanecontent.length; lnidx++)
                    {
                        var key = sublanecontent[lnidx];
                        var pnode = proxynodes.allnodes[key];
                        var nodeidx = pnode.nodeidx;
                        var realnode = nodes[nodeidx];
                        realnode.ideal_y = ybump + (lnidx * yfactor);
                        realnode.ideal_x = xbump + (lnidx * xfactor);
                        realnode.ideal_tickmove_count = 0;
                    }
                }
            }
        }
    };

    methods.getIdealNodePositionProxyNodes = function(graphdata)
    {
        if(!graphdata.linksbundle.hasOwnProperty('link_by_offset'))
        {
            console.log("LOOK graphdata.linksbundle=" + JSON.stringify(graphdata.linksbundle));
            throw "Expected to find graphdata.linksbundle.link_by_offset!!!";
        }
        var nodes = graphdata.nodes;
        var links = graphdata.linksbundle.link_by_offset;
        var max_level = 0;
        
        if(nodes.constructor !== Array)
        {
            console.log("LOOK BAD nodes=" + JSON.stringify(nodes));
            throw "Expected nodes variable to be an array instead it is " + typeof nodes;
        }
        if(links.constructor !== Array)
        {
            console.log("LOOK BAD links=" + JSON.stringify(links));
            throw "Expected links variable to be an array instead it is " + typeof links;
        }
        
        var proxynodes = {'rootkey':null,
                'allnodes':{}
            };
        var laneframework = {};
			
	//Create proxy node stubs
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            if(!nodes.hasOwnProperty(nodeidx) || typeof nodes[nodeidx] === 'undefined')
            {
                throw "ERROR there is no " + nodeidx + " ( nodes.length=" +  nodes.length + " ) in " + JSON.stringify(nodes);
            }
            var onenode = nodes[nodeidx];
            if(onenode.hasOwnProperty('assignment'))
            {
                if(onenode.assignment === 1)
                {
                    if(!onenode.hasOwnProperty('hierarchy_level') || (typeof onenode.hierarchy_level === "undefined"))
                    {
                        console.log("ERROR TODO debug graphdata=" + JSON.stringify(graphdata));
                        console.log("ERROR TODO debug why this node does NOT have a valid hierarchy_level (creating as 1 now) onenode=" + JSON.stringify(onenode));
                        throw "ERROR node does NOT have a valid hierarchy_level (creating as 1 now) onenode=" + JSON.stringify(onenode);
                        onenode['hierarchy_level'] = 1;
                    } else 
                    var thekey = onenode.key;
                    if(thekey === null || thekey === '')
                    {
                        throw "ERROR getting the key for " + JSON.stringify(onenode);
                    }
                    var thehierarchy_level = onenode.hierarchy_level; 
                    var proxynode = {};
                    proxynode['nodeidx'] = nodeidx;
                    proxynode['parents'] = {};
                    proxynode['children'] = {};
                    proxynode['parentcount'] = 0;
                    proxynode['childrencount'] = 0;
                    proxynodes.allnodes[thekey] = proxynode;
                    if(thehierarchy_level === 1)
                    {
                        //There is only one at level one, that is the root
                        proxynodes.rootkey = thekey;
                    }
                    if(!laneframework.hasOwnProperty(thehierarchy_level))
                    {
                        laneframework[onenode.hierarchy_level] = [];
                        if(max_level < onenode.hierarchy_level)
                        {
                            max_level = onenode.hierarchy_level;
                        }
                    }
                    laneframework[thehierarchy_level].push(thekey);
                }
            }
        }
        for(var i=max_level; i>0; i--)
        {
            if(!laneframework.hasOwnProperty(i))
            {
                console.log("DEBUG found gap in levels of laneframework at hlevel=" + i);
                console.log("DEBUG laneframework=" + JSON.stringify(laneframework));
                throw "Corrupted laneframework missing level#" + i;
            }
        }

        //Update the parent and child values of proxy nodes
        for(var linkidx = 0; linkidx < links.length; linkidx++)
        {
            
            var onelink = links[linkidx];
            var target = nodes[onelink.trgno];
            var source = nodes[onelink.srcno];

            //Only update proxy nodes if they exist!
            if(target.key in proxynodes.allnodes && source.key in proxynodes.allnodes)
            {
                var pn_target = proxynodes.allnodes[target.key];
                var pn_source = proxynodes.allnodes[source.key];

                pn_source.parents[target.key] = pn_target.nodeidx;
                pn_source.parentcount++;

                pn_target.children[source.key] = pn_source.nodeidx;
                pn_target.childrencount++;
            }
        }

        //Now sort in lanes based on weight
        var metrics = [];
        for(var hierarchy_level in laneframework)
        {
            var hlint = parseInt(hierarchy_level);
            
            //Figure out the relative orders
            if(hlint === 2)
            {
                //Sort this lane then align with parent positions
                metrics.push(methods.orderProxyNodesInLevelByChildLinks(hierarchy_level, laneframework, proxynodes, graphdata));
            } else if(hlint > 2) {
                metrics.push(methods.orderProxyNodesInLevelByParentLinks(hierarchy_level, laneframework, proxynodes, graphdata));
            }
        }
        
        var bundle = {'max_level':max_level, 'proxynodes': proxynodes, 'laneframework': laneframework, 'metrics': metrics};
        return bundle;
    };

    methods.orderProxyNodesInLevelByWeight = function(hierarchy_level, laneframework, proxynodes, graphdata)
    {
        var moves = 0;
        var passes = 0;
        var sublanecontent = laneframework[hierarchy_level];
        
        if(sublanecontent.length > 2)
        {
            //Sort this lane then align with parent positions
            var halflane = Math.floor(sublanecontent.length / 2) + 1;
            var look_for_swaps = true;
            while(look_for_swaps)
            {
                passes++;
                look_for_swaps = false;
                for(var lnidx = 0; lnidx < sublanecontent.length; lnidx++)
                {
                    //TODO --- glue nodes together that share children!!!!
                    
                    var key = sublanecontent[lnidx];
                    var pnode = proxynodes.allnodes[key];
                    //var parentcount = pnode.parentcount;
                    var childrencount = pnode.childrencount;
                    if(lnidx < halflane && lnidx > 0)
                    {
                        var prev_pn_key = sublanecontent[lnidx-1];
                        var prev_pnode = proxynodes.allnodes[prev_pn_key];
                        var prev_childrencount = prev_pnode.childrencount;
                        if(prev_childrencount > childrencount)
                        {
                            //Swap bigger down
                            sublanecontent[lnidx-1] = key;
                            sublanecontent[lnidx] = prev_pn_key;
                            moves++;
                            look_for_swaps = true;
                        }
                    } else if(lnidx >= halflane && lnidx < (sublanecontent.length-1) ){
                        var next_pn_key = sublanecontent[lnidx+1];
                        var next_pnode = proxynodes.allnodes[next_pn_key];
                        var next_childrencount = next_pnode.childrencount;
                        if(next_childrencount > childrencount)
                        {
                            //Swap bigger up
                            sublanecontent[lnidx+1] = key;
                            sublanecontent[lnidx] = next_pn_key;
                            moves++;
                            look_for_swaps = true;
                        }
                    }
                }
            }
        }
        
        return {'passes':passes, 'moves':moves};
    };

    /**
     * Group if they share the same child
     */
    methods.orderProxyNodesInLevelByChildLinks = function(hierarchy_level, laneframework, proxynodes, graphdata)
    {
        var sublanecontent = laneframework[hierarchy_level];
        var child_hlevel = parseInt(hierarchy_level) + 1;
        
        if(laneframework.hasOwnProperty(child_hlevel))
        {
            var new_sublanecontent = [];
            var leaf_count = 0;
            var placed_tracking = {};
            var placed_idx = -1;
            var importance_next = [];
            var normal_next = [];
            
            //Create a queue of normal next stuff
            for(var sublanenidx = 0; sublanenidx < sublanecontent.length; sublanenidx++)
            {
                var key = sublanecontent[sublanenidx];
                normal_next.push(key);
            }            
            
            //Process all the proxy nodes in dynamic order
            var key;
            while(importance_next.length > 0 || normal_next.length > 0)
            {
                if(importance_next.length > 0)
                {
                    key = importance_next.pop();
                } else {
                    key = normal_next.pop();
                }
                if(!placed_tracking.hasOwnProperty(key))
                {
                    var pnode = proxynodes.allnodes[key];
                    var childrencount = pnode.childrencount;
                    if(childrencount === 0)
                    {
                        placed_idx++;
                        placed_tracking[key] = placed_idx;
                        new_sublanecontent.push(key);  
                        leaf_count++;
                    } else {
                        for(var child_key in pnode.children)
                        {
                            var child_pnode = proxynodes.allnodes[child_key];
                            for(var parent_key in child_pnode.parents)
                            {
                                if(!placed_tracking.hasOwnProperty(parent_key))
                                {
                                    placed_idx++;
                                    placed_tracking[parent_key] = placed_idx;
                                    new_sublanecontent.push(parent_key);
                                    importance_next.push(parent_key);
                                }
                            }
                        }
                    }
                }
            }

            //Replace the old sublane content with the new one
            laneframework[hierarchy_level] = [];
            sublanecontent = laneframework[hierarchy_level];
            for(var sublanenidx = 0; sublanenidx < new_sublanecontent.length; sublanenidx++)
            {
                var key = new_sublanecontent[sublanenidx];
                sublanecontent.push(key);  
            }
        }
        
        return {'leaf_count':leaf_count};
    };

    /**
     * Line them up with their parents
     */
    methods.orderProxyNodesInLevelByParentLinks = function(hierarchy_level, laneframework, proxynodes, graphdata)
    {
        var sublanecontent = laneframework[hierarchy_level];
        var new_sublanecontent = [];
        var orphan_count = 0;
        
        var hlevel = parseInt(hierarchy_level);
        if(hlevel > 2 && sublanecontent.length > 1)
        {
            
            //Add all the orphans into the new sublane content array
            for(var sublanenidx = 0; sublanenidx < sublanecontent.length; sublanenidx++)
            {
                var key = sublanecontent[sublanenidx];
                var pnode = proxynodes.allnodes[key];
                var parentcount = pnode.parentcount;
                if(parentcount === 0)
                {
                    new_sublanecontent.push(key);  
                    orphan_count++;
                }
            }
            
            //Add all the children of previous level into the new sublane content array
            var placed_tracking = {};
            var placed_idx = -1;
            var adjusted_hlevel = hlevel-1;
            if(!laneframework.hasOwnProperty(adjusted_hlevel))
            {
                console.log("DEBUG for missing adjusted_hlevel#" + adjusted_hlevel + " in laneframework=" + JSON.stringify(laneframework));
                throw "Missing adjusted_hlevel#" + adjusted_hlevel + " in laneframework!";
            }
            var parent_sublanecontent = laneframework[adjusted_hlevel];
            for(var sublanenidx = 0; sublanenidx < parent_sublanecontent.length; sublanenidx++)
            {
                var key = parent_sublanecontent[sublanenidx];
                var pnode = proxynodes.allnodes[key];
                for(var child_key in pnode.children)
                {
                    if(!placed_tracking.hasOwnProperty(child_key))
                    {
                        placed_idx++;
                        placed_tracking[child_key] = placed_idx;
                        new_sublanecontent.push(child_key);
                    }
                }
            }            

            //console.log("about to change from laneframework=" + JSON.stringify(laneframework[hierarchy_level]));
            
            //Replace the old sublane content with the new one
            laneframework[hierarchy_level] = [];
            sublanecontent = laneframework[hierarchy_level];
            for(var sublanenidx = 0; sublanenidx < new_sublanecontent.length; sublanenidx++)
            {
                var key = new_sublanecontent[sublanenidx];
                //console.log(".... key==" + key);
                sublanecontent.push(key);  
            }
            
            //console.log("done change sublanecontent=" + JSON.stringify(sublanecontent));
            //console.log("done change laneframework=" + JSON.stringify(laneframework));
        }
        
        return {'orphan_count':orphan_count, 'non_orphan_count': (sublanecontent.length - orphan_count)};
    };

    methods.isEmptyObject = function(obj) 
    {
        for(var prop in obj) 
        {
            if(obj.hasOwnProperty(prop))
                return false;
        }

        return true;
    };

    /**
     * Return a map of indexes for fast node lookups
     */
    methods.getFastNodeLookupMaps = function(nodes)
    {
        var background = {};
        var bytype = {};
        var node_id2offset = {};
        var node_key2offset = {};   //Redundant with offsetbykey
        for(var i = 0; i < nodes.length; i++)
        {
            var onenode = nodes[i];
            if(!bytype.hasOwnProperty(onenode.type))
            {
                bytype[onenode.type] = {'key2offset':{}};
            }
            bytype[onenode.type].key2offset[onenode.key] = i;
            if(onenode.is_background)
            {
                background[onenode.key] = i;
            }
            if(onenode.hasOwnProperty("nativeid"))
            {
                node_id2offset[onenode.nativeid] = i;
            }
            if(onenode.hasOwnProperty("key"))
            {
                node_key2offset[onenode.key] = i;
            }
        }
        return {
            "background":background
            , "bytype":bytype
            , "id2offset":node_id2offset
            , "key2offset":node_key2offset
        };
    };

    /**
     * Return a map of indexes for fast link lookups
     */
    methods.getFastLinkLookupMaps = function(graphdata)
    {
        var bytarget = {};  //Collection of all antecedents keyed on subsequent
        var bysource = {};  //Collection of all subsequents keyed on antecedent
        var nodes = graphdata.nodes;
        var link_by_offset = graphdata.linksbundle.link_by_offset;
        for(var i = 0; i < link_by_offset.length; i++)
        {
            var onelink = link_by_offset[i];
            var targetnode = nodes[onelink.trgno];
            var sourcenode = nodes[onelink.srcno];

            if(!bytarget.hasOwnProperty(targetnode.key))
            {
                bytarget[targetnode.key] = {sources:{}};
            }
            bytarget[targetnode.key].sources[sourcenode.key] 
                    =   {
                            'type':sourcenode.type,
                            'nativeid':sourcenode.nativeid,
                            'key':sourcenode.key
                        };
            if(!bysource.hasOwnProperty(sourcenode.key))
            {
                bysource[sourcenode.key] = {targets:{}};
            }
            bysource[sourcenode.key].targets[targetnode.key] 
                    =   { 
                            'type':targetnode.type,
                            'nativeid':targetnode.nativeid,
                            'key':targetnode.key
                        };
        }
        return {'bytarget':bytarget, 'bysource':bysource};
    };

    /**
     * Return array of sheltered antecedent node keys, may contain duplicates.
     */
    methods.getAllShelteredAntecedentNodes = function(thenodekey, graphdata, recursive)
    {
        var nodes = graphdata.nodes;
        if(typeof recursive === 'undefined')
        {
            recursive = true;    
        }
        var fast_nodelookup_maps = graphdata.fastlookup_maps.nodes;
        var fast_linklookup_maps = graphdata.fastlookup_maps.links;
        var sheltered_ants = [];
        var bytarget = fast_linklookup_maps['bytarget'];
        if(bytarget.hasOwnProperty(thenodekey))
        {
            //Create collection of the antecedents with no other subsequent node
            var ants = bytarget[thenodekey];
            if(ants.hasOwnProperty('sources'))
            {
                for(var keyname in ants.sources)
                {
                    if(ants.sources.hasOwnProperty(keyname))
                    {
                        var ant_targets = fast_linklookup_maps['bysource'][keyname].targets;
                        var ant_target_keys = Object.keys(ant_targets);
                        if(ant_target_keys.length === 1)
                        {
                            sheltered_ants.push(ants.sources[keyname]);
                        } else {
                            //There are other subsequent nodes, see if they are hiding ants
                            var sheltered = true;
                            for(var atki=0; atki < ant_target_keys.length; atki++)
                            {
                                var check_ant_subsequent_nodekey = ant_target_keys[atki];
                                var check_ant_subsequent_offset = fast_nodelookup_maps.key2offset[check_ant_subsequent_nodekey];
                                var check_ant_subsequent_detail = nodes[check_ant_subsequent_offset];
                                if(check_ant_subsequent_detail.key !== thenodekey)
                                {
                                    if(!check_ant_subsequent_detail.hasOwnProperty('show_node') 
                                            || check_ant_subsequent_detail.show_node)
                                    {
                                        var iscollapsed = bigfathom_util.nodes.hasHiddenAntecedents(check_ant_subsequent_detail);
                                        if(!iscollapsed)
                                        {
                                            //This subsequent node is holding the ant visible
                                            sheltered = false;
                                            break;
                                        }
                                    }
                                }
                            }
                            if(sheltered)
                            {
                                sheltered_ants.push(ants.sources[keyname]);
                            }
                        }
                    }
                }
            }
            if(recursive)
            {
                //Recursively call this function using each node as root that had no other subsequent node
                var more_sa = [];
                for(var i=0; i<sheltered_ants.length; i++)
                {
                    var antkey = sheltered_ants[i].key;
                    var more_sa 
                            = methods.getAllShelteredAntecedentNodes(antkey, graphdata, recursive);
                    for(var j=0; j<more_sa.length; j++)
                    {
                        sheltered_ants.push(more_sa[j]);
                    }
                }
            }
        }
        return sheltered_ants;
    };

    /**
     * Creates all the lookup indexes from scratch
     */
    methods.refreshAllLookupMaps = function(graphdata)
    {
        graphdata["fastlookup_maps"] = {};
        var fast_nodelookup_maps = methods.getFastNodeLookupMaps(graphdata.nodes);
        graphdata.fastlookup_maps.nodes = fast_nodelookup_maps;
        var linksbundle = methods.getLinkNodeMaps(graphdata, fast_nodelookup_maps);
        graphdata.linksbundle = linksbundle;
        var fast_linklookup_maps = methods.getFastLinkLookupMaps(graphdata);
        graphdata.fastlookup_maps.links = fast_linklookup_maps;    
    };

    /**
     * Embed fast lookup maps into the graphdata object
     */
    methods.refreshFastLookupMaps = function(graphdata)
    {
        if(!graphdata.hasOwnProperty('fastlookup_maps'))
        {
            graphdata['fastlookup_maps'] = {nodes:{}, links:{}};   
        }
        var fast_nodelookup_maps = methods.getFastNodeLookupMaps(graphdata.nodes);
        graphdata.fastlookup_maps.nodes = fast_nodelookup_maps;
        var fast_linklookup_maps = methods.getFastLinkLookupMaps(graphdata);
        graphdata.fastlookup_maps.links = fast_linklookup_maps;
    };

    /**
     * Create all the link representations we care about.
     * NOTE: The D3 library WILL mangle the content.  We work around that.
     */
    methods.createOneLinkTupleFromParts = function(nodes, node_key2offset, source_key, target_key, is_new)
    {
        var onelinkbundle = {};
        var source_offset = node_key2offset[source_key];
        var target_offset = node_key2offset[target_key];
        var source = nodes[source_offset];
        var target = nodes[target_offset];
        var assume_changed = false;
        
        if(typeof source === 'undefined')
        {
            assume_changed = true;
            console.log("Failed to find source node at offset " + source_offset + " for source_key=" + source_key);
        }
        if(typeof target === 'undefined')
        {
            assume_changed = true;
            console.log("Failed to find target node at offset " + target_offset + " for target_key=" + target_key);
        }
        
        if(assume_changed)
        {
            bigfathom_util.env.is_model_out_of_synch = true;
        } else {
            var link_key = source_key + "__2__" + target_key;

            var onelinkOffsets = {'type':'link_by_offset'
                        , 'key': link_key
                        , 'is_new': is_new
                        , 'srcno': source_offset    //We use this property for our purposes
                        , 'trgno': target_offset    //We use this property for our purposes
                        , 'source': source_offset   //D3 library looks for this property and replaces it
                        , 'target': target_offset   //D3 library looks for this property and replaces it
                    };

            var onelinkByNodeKey = {'type':'link_by_key'
                        , 'key': link_key
                        , 'is_new': is_new
                        , 'srcnk': source_key
                        , 'trgnk': target_key
                        , 'source': source_offset   //D3 library looks for this property and replaces it
                        , 'target': target_offset   //D3 library looks for this property and replaces it
                    };

            var onelinkByWID = {'type':'link_by_wid'
                        , 'key': link_key
                        , 'is_new': is_new
                        , 'srcwid': source.nativeid
                        , 'trgwid': target.nativeid
                        , 'source': source_offset   //D3 library looks for this property and replaces it
                        , 'target': target_offset   //D3 library looks for this property and replaces it
                    };

            onelinkbundle['link_by_offset'] = onelinkOffsets;
            onelinkbundle['link_by_key'] = onelinkByNodeKey;
            onelinkbundle['link_by_wid'] = onelinkByWID;
            onelinkbundle['key4fast_link_exists_byoffset_lookup'] = source_offset + "," + target_offset;
            onelinkbundle['key4fast_link_exists_bywid_lookup'] = source.nativeid + "," + target.nativeid;
        }

        return onelinkbundle;
    };

    /**
     * Create all the link representations we care about for one connection
     */
    methods.createOneLinkTupleFromGraphData = function(graphdata, source_key, target_key, is_new)
    {
        var node_key2offset = graphdata.fastlookup_maps.nodes.key2offset;
        var nodes = graphdata.nodes;
        return methods.createOneLinkTupleFromParts(nodes, node_key2offset, source_key, target_key, is_new);
    };


    /**
     * Creates the links that connect nodes based on the existing master content
     */
    methods.getLinkNodeMaps = function(graphdata, fast_nodelookup_maps, ignore_missing_nodes)
    {
        var linkbundle = {
            'fast_link_exists_byoffset_lookup':{},
            'fast_link_exists_bywid_lookup':{},
            'link_by_offset':[],
            'link_by_key':[],
            'link_by_wid':[]//,
        };

        if(typeof ignore_missing_nodes === 'undefined')
        {
            ignore_missing_nodes = true;
        }

        var linksmaster = graphdata.linksmaster;
        var node_id2offset = fast_nodelookup_maps.id2offset;
        var node_key2offset = fast_nodelookup_maps.key2offset;
        var nodes = graphdata.nodes;
        for(var i = 0; i < linksmaster.length; i++)
        {
            var oneitem = linksmaster[i];
            var antwiid = oneitem['antwiid'];
            var depwiid = oneitem['depwiid'];
            var missing_node = false;
            if(!node_id2offset.hasOwnProperty(antwiid))
            {
                if(ignore_missing_nodes)
                {
                    missing_node = true;
                } else {
                    console.log("getLinkNodeMaps antwiid=" + antwiid + " node_id2offset=" + JSON.stringify(node_id2offset));
                    console.log("getLinkNodeMaps oneitem=" + JSON.stringify(oneitem));
                    throw "Missing node_id2offset antwiid for recorded link!";
                }
            }
            if(!node_id2offset.hasOwnProperty(depwiid))
            {
                if(ignore_missing_nodes)
                {
                    missing_node = true;
                } else {
                    console.log("getLinkNodeMaps depwiid=" + depwiid + " node_id2offset=" + JSON.stringify(node_id2offset));
                    console.log("getLinkNodeMaps oneitem=" + JSON.stringify(oneitem));
                    throw "Missing node_id2offset depwiid for recorded link!";
                }
            }

            if(!missing_node)
            {
                var from_index = node_id2offset[antwiid];
                var to_index = node_id2offset[depwiid];

                if(!nodes.hasOwnProperty(from_index))
                {
                    console.log("nodes=" + JSON.stringify(nodes));
                    throw "Missing node depwiid for recorded from_index=" + from_index + "!";
                }
                if(!nodes.hasOwnProperty(to_index))
                {
                    console.log("nodes=" + JSON.stringify(nodes));
                    throw "Missing node depwiid for recorded from_index=" + to_index + "!";
                }

                var linksource = nodes[from_index];
                var linktarget = nodes[to_index];
                var onelinkbundle = methods.createOneLinkTupleFromParts(nodes, node_key2offset, linksource.key, linktarget.key, false);
                var key4fast_link_exists_byoffset_lookup = onelinkbundle['key4fast_link_exists_byoffset_lookup'];
                var key4fast_link_exists_bywid_lookup = onelinkbundle['key4fast_link_exists_bywid_lookup'];
                linkbundle.fast_link_exists_byoffset_lookup[key4fast_link_exists_byoffset_lookup] = true;
                linkbundle.fast_link_exists_bywid_lookup[key4fast_link_exists_bywid_lookup] = true;
                linkbundle.link_by_offset.push(onelinkbundle['link_by_offset']);
                linkbundle.link_by_key.push(onelinkbundle['link_by_key']);
                linkbundle.link_by_wid.push(onelinkbundle['link_by_wid']);
            }
        }

        return linkbundle;
    };

    /**
     * Create the reference structure that we maintain to know what links we 
     * have.  All the indexes are derived from this structure.
     */
    methods.getLinksMaster = function(databundle)
    {
        var linksmaster = [];
        var item2item = databundle.data.wi2wi;
        for(var i=0; i<item2item.length; i++)
        {
            var onelink = item2item[i];
            var onemaster = {'depwiid':onelink.depwiid,'antwiid':onelink.antwiid};
            linksmaster.push(onemaster);
        }
        return linksmaster;
    };

    /**
     * Update master structures with brainstorm items that have been converted to real workitems
     */
    methods.updateWIDMastersForBrainstormConversions = function(graphdata, map_brainstormid2wid)
    {
        var updated = 0;
        
        
        var nodescount = graphdata.nodes.length;
        for (var i = 0; i < nodescount; i ++)
        {
            var onenode = graphdata.nodes[i];
            if(onenode.is_candidate && (onenode.assignment === 1 || onenode.assignment === '1'))
            {
                if(!onenode.hasOwnProperty('nativeid') || onenode.nativeid == 'undefined')
                {
                    alert("ERROR nativeid property is missing from onenode=" + JSON.stringify(onenode));
                    throw "ERROR nativeid property is missing from onenode=" + JSON.stringify(onenode);
                }
                //Change the properties of this node now
                updated++;
                if(onenode.nativeid.substr(0,1) !== 'c')
                {
                    throw "Expected first char of candidate oldnativeid=" + onenode.nativeid + " to be a lowercase c! >>> " + JSON.stringify(onenode);
                }
                var oldnativeid = onenode.nativeid.substr(1);
                if(map_brainstormid2wid.hasOwnProperty(oldnativeid))
                {
                    var new_nativeid = map_brainstormid2wid[oldnativeid];

    console.log("LOOK we setAsCommittedWorkitem for onenode.nativeid=" + onenode.nativeid + " new_nativeid=" + new_nativeid + " map_brainstormid2wid=" + JSON.stringify(map_brainstormid2wid));
                    bigfathom_util.hierarchy_data.setAsCommittedWorkitem(onenode, new_nativeid);
                    console.log("LOOK we setAsCommittedWorkitem " + JSON.stringify(onenode));
                }
            }
        }
            
        var fast_nodelookup_maps = methods.getFastNodeLookupMaps(graphdata.nodes);
        graphdata.fastlookup_maps.nodes = fast_nodelookup_maps;
        
        for(var i=0; i<graphdata.linksmaster.length; i++)
        {
            var onelinkmaster = graphdata.linksmaster[i];
            if(onelinkmaster.antwiid.substr(0,1) === 'c')   //Only apply if candidate!!!
            {
                var brainstormid = onelinkmaster.antwiid.substr(1);
                if(map_brainstormid2wid.hasOwnProperty(brainstormid))
                {
                    updated++;
                    onelinkmaster.antwiid = map_brainstormid2wid[brainstormid];
                }
            }
        }
        return updated;
    };

    /**
     * Return some of the link structures that connect nodes
     */
    methods.createLinkNodeMaps = function(actorbundle, fast_nodelookup_maps, databundle, ignore_missing_nodes)
    {
        var linkbundle = {
            'fast_link_exists_byoffset_lookup':{},
            'fast_link_exists_bywid_lookup':{},
            'link_by_offset':[],
            'link_by_key':[],
            'link_by_wid':[]//,
        };
        
        if(typeof ignore_missing_nodes === 'undefined')
        {
            ignore_missing_nodes = true;
        }

        var item2item = databundle.data.wi2wi;
        var node_id2offset = fast_nodelookup_maps.id2offset;
        var node_key2offset = fast_nodelookup_maps.key2offset;
        var nodes = actorbundle.nodes;
        for(var i = 0; i < item2item.length; i++)
        {
            var oneitem = item2item[i];
            var antwiid = oneitem['antwiid'];
            var depwiid = oneitem['depwiid'];
            var missing_node=false;
            if(!node_id2offset.hasOwnProperty(antwiid))
            {
                if(ignore_missing_nodes)
                {
                    missing_node = true;
                } else {
                    console.log("createLinkNodeMaps antwiid=" + antwiid + " node_id2offset=" + JSON.stringify(node_id2offset));
                    console.log("createLinkNodeMaps oneitem=" + JSON.stringify(oneitem));
                    throw "Missing node_id2offset antwiid for recorded link!";
                }
            }
            if(!node_id2offset.hasOwnProperty(depwiid))
            {
                if(ignore_missing_nodes)
                {
                    missing_node = true;
                } else {
                    console.log("createLinkNodeMaps depwiid=" + depwiid + " node_id2offset=" + JSON.stringify(node_id2offset));
                    console.log("createLinkNodeMaps oneitem=" + JSON.stringify(oneitem));
                    throw "Missing node_id2offset depwiid for recorded link!";
                }
            }

            if(!missing_node)
            {
                var from_index = node_id2offset[antwiid];
                var to_index = node_id2offset[depwiid];

                if(!nodes.hasOwnProperty(from_index))
                {
                    console.log("nodes=" + JSON.stringify(nodes));
                    throw "Missing node depwiid for recorded from_index=" + from_index + "!";
                }
                if(!nodes.hasOwnProperty(to_index))
                {
                    console.log("nodes=" + JSON.stringify(nodes));
                    throw "Missing node depwiid for recorded from_index=" + to_index + "!";
                }

                var linksource = nodes[from_index];
                var linktarget = nodes[to_index];

                //linkbundle.fastLookup[from_index + "," + to_index] = true;  //Declare the connection

                var onelinkbundle = methods.createOneLinkTupleFromParts(nodes, node_key2offset, linksource.key, linktarget.key, false);
                var key4fast_link_exists_byoffset_lookup = onelinkbundle['key4fast_link_exists_byoffset_lookup'];
                var key4fast_link_exists_bywid_lookup = onelinkbundle['key4fast_link_exists_bywid_lookup'];
                linkbundle.fast_link_exists_byoffset_lookup[key4fast_link_exists_byoffset_lookup] = true;
                linkbundle.fast_link_exists_bywid_lookup[key4fast_link_exists_bywid_lookup] = true;
                linkbundle.link_by_offset.push(onelinkbundle['link_by_offset']);
                linkbundle.link_by_key.push(onelinkbundle['link_by_key']);
                linkbundle.link_by_wid.push(onelinkbundle['link_by_wid']);
            }
        }

        return linkbundle;
    };

    /**
     * Call this whenever the completed_branch_rootids value is changed
     */
    methods.adjustForHideCompletedBranch = function(graphdata, autohide)
    {
        if(typeof autohide === 'undefined' || autohide === null)
        {
            autohide = true;
        }
        methods.resetAllNodeHierarchy(graphdata, autohide);
        methods.adjustSublanesIfNeeded(bigfathom_util.env.multilevelhierarchy.hierarchy_lane, graphdata.nodes);
    };

    /**
     * The runtime model and UI are updated with a new link when this completes.
     */
    methods.createNewLinkForUI = function(graphdata, source, target)
    {
        methods.markOffsetsChanging("createNewLinkForUI", true); //TODO bubble up to caller instead for retry!!!!!
        
        if(source == null || typeof source == 'undefined')
        {
            throw "Expected a valid source value!";
        }
        if(target == null || typeof target == 'undefined')
        {
            throw "Expected a valid target value!";
        }
        
        var sublane_count1 = corefacts.lanes[0].sublanes.length;
        var new_hierarchy_level = target.hierarchy_level + 1;
        var needs_redraw = false;
        var onelinkbundle = null;

        var link_exists = false;
        for(var i=0; i<graphdata.linksmaster.length; i++)
        {
            var onelinkmaster = graphdata.linksmaster[i];
            if(onelinkmaster.antwiid === source.nativeid && onelinkmaster.depwiid === target.nativeid)
            {
                console.log("Warning already have link for this: " + JSON.stringify(onelinkmaster));
                link_exists = true;
                break;
            }
        }
        if(!link_exists)
        {
            var newlinkmaster = {"antwiid":source.nativeid, "depwiid":target.nativeid};
            graphdata.linksmaster.push(newlinkmaster);
            
            if(source.assignment !== 1 || new_hierarchy_level !== source.hierarchy_level)
            {
                //Hierarchy changed, make sure we adjust the sublanes
                source.assignment = target.assignment;
                source.hierarchy_level = new_hierarchy_level;
                var showid = source.key;
                source.tooltip = {'id#' : showid,'level':source.hierarchy_level};
                methods.adjustSublanesIfNeeded(target.assignment, graphdata.nodes);
            }

            //Create the link indexes etc
            onelinkbundle = methods.createOneLinkTupleFromGraphData(graphdata, source.key, target.key, true);
            for(var onelinktypename in onelinkbundle)
            {
                if(onelinkbundle.hasOwnProperty(onelinktypename))
                {
                    if(graphdata.linksbundle.hasOwnProperty(onelinktypename))
                    {
                        var onelinktype = onelinkbundle[onelinktypename];
                        graphdata.linksbundle[onelinktypename].push(onelinktype);
                    }
                }
            }
            source.is_drag_target = true;

            //Shift all affected children of the source node
            var autohide = true;
            methods.resetAllNodeHierarchy(graphdata, autohide);
            methods.setIdealNodePositions(graphdata);
            methods.showBranch(graphdata, source.key);

            bigfathom_util.nodes.removeSubtype(source, "warn_disconnected_rootnode");
            bigfathom_util.nodes.showAllAntecedents(source);

            var sublane_count2 = corefacts.lanes[0].sublanes.length;
            needs_redraw = (sublane_count1 !== sublane_count2);
        }
        
        var bundle = {};
        bundle["onelinkbundle"] = onelinkbundle;
        bundle["needs_redraw"] = needs_redraw;
        
        methods.clearOffsetsChanging("createNewLinkForUI");
        return bundle;
    };

    methods.debugShowNodeInfo = function(graphdata, infolabel)
    {
        console.log("DEBUG info " + infolabel + " START FILTERED DUMP of subset of " + graphdata.nodes.length + " nodes....");
        var showcount=0;
        for(var i=0; i<graphdata.nodes.length; i++)
        {
            var onenode = graphdata.nodes[i];
            if(onenode.assignment === 1)
            {
                console.log("DEBUG info " + infolabel + " na=" + onenode.assignment + " hl=" + onenode.hierarchy_level + " key=" + onenode.key + " detail=" + JSON.stringify(onenode));
                showcount++;
            }
        }
        console.log("DEBUG info " + infolabel + " DONE FILTERED DUMP showing " + showcount + " of " + graphdata.nodes.length + " nodes!");
    };

    /**
     * Adjust the graph so that the identified node is shown
     */
    methods.showNode = function(thenodekey, graphdata, expandthis)
    {
        if (typeof expandthis === 'undefined')
        {
            expandthis = false;
        };
        var hide_completed = graphdata.hide_completed_branches;
        var nodes = graphdata.nodes;
        var node_key2offset = graphdata.fastlookup_maps.nodes.key2offset;
        var thenodeoffset = node_key2offset[thenodekey];
        var thenode = nodes[thenodeoffset];
        var canexpandthis = !hide_completed || (hide_completed && thenode.status_detail.terminal_yn != 1);
        
        if(!canexpandthis)
        {
            thenode.show_node = false;  //Lets mark it no show!
        } else {
            if(thenode.show_node === false)
            {
                var fllmap = graphdata.fastlookup_maps.links;
                var subsequentnodes = fllmap.bysource[thenodekey];
                var subsequentkeys = Object.keys(subsequentnodes.targets);
                //Show the node by expanding parent
                for(var i=0; i<subsequentkeys.length; i++)
                {
                    var subsequentkey = subsequentkeys[i];
                    methods.showNode(subsequentkey, graphdata, true);   
                }
            }
            if(expandthis)
            {
                var show_ant_nodes = true;
                methods.changeNodeConnectionsShown(thenodeoffset, graphdata, show_ant_nodes);
            }
        }
    };

    /**
     * Hide or show based on the non-null flag values
     */
    methods.changeNodeConnectionsShown = function(thenodeoffset, graphdata, show_ant_nodes)
    {
        if(bigfathom_util.env.is_model_out_of_synch)
        {
            console.log("WARNING at changeNodeConnectionsShown LOCAL MODEL IS OUT OF SYNCH!");
        }
        var hide_completed = graphdata.hide_completed_branches;
        var nodes = graphdata.nodes;
        if(!nodes.hasOwnProperty(thenodeoffset))
        {
            console.log("DEBUG expected to find thenodeoffset=" + thenodeoffset + " in nodes=" + JSON.stringify(nodes));
            throw "Missing node offset " + thenodeoffset + " in graphdata!";
        }
        var thenode = nodes[thenodeoffset];
        var thenodekey = thenode.key;

        if(hide_completed && thenode.status_detail.terminal_yn == 1)
        {
            thenode.show_node = false;
        } else {
            if(bigfathom_util.nodes.hasAntecedents(thenode))
            {
                if(show_ant_nodes === true)
                {
                    bigfathom_util.nodes.showAllAntecedents(thenode, hide_completed);    
                } else if(show_ant_nodes === false) {
                    bigfathom_util.nodes.hideAllAntecedents(thenode, hide_completed);    
                }

                //Now adjust the show_node attribute of all impacted nodes
                methods.refreshAllLookupMaps(graphdata);
                var sheltered_ants 
                        = methods.getAllShelteredAntecedentNodes(thenodekey, graphdata, false);

                for(var i=0; i < sheltered_ants.length; i++)
                {
                    var ant_node = sheltered_ants[i];
                    var change_nodeidx = graphdata.fastlookup_maps.nodes.bytype[ant_node.type].key2offset[ant_node.key];
                    var change_node = nodes[change_nodeidx];
                    if(!bigfathom_util.nodes.hasHiddenAntecedents(change_node))
                    {
                        methods.changeNodeConnectionsShown(change_nodeidx, graphdata, show_ant_nodes);
                    }
                    if(!hide_completed || (hide_completed && change_node.status_detail.terminal_yn != 1))
                    {
                        change_node['show_node'] = show_ant_nodes;
                    } else {
                        change_node['show_node'] = false;
                    }
                }
            }
        }
    };

    /**
     * Open the branch containing the node
     */
    methods.showBranch = function(graphdata, memberkey)
    {
        if(bigfathom_util.env.is_model_out_of_synch)
        {
            console.log("WARNING at showBranch LOCAL MODEL IS OUT OF SYNCH!");
        }
        var hide_completed = graphdata.hide_completed_branches;
        if(!graphdata.hasOwnProperty('fastlookup_maps'))
        {
            console.log("ERROR look graphdata=" + JSON.stringify(graphdata));
            throw "Expected to have graphdata.fastlookup_maps!!!";
        }

        var fastlookup_maps = graphdata.fastlookup_maps;
        var fast_nodelookup_maps = fastlookup_maps.nodes;
        var fast_linklookup_maps = fastlookup_maps.links;
        if(!fast_linklookup_maps.hasOwnProperty('bysource'))
        {
            console.log("ERROR look fast_linklookup_maps=" + JSON.stringify(fast_linklookup_maps));
            throw "Expected to have fast_linklookup_maps.bysource!!!";
        }
        var bysource = fast_linklookup_maps['bysource'];
       
        var showSubsequents = function(sourcekey)
        {
            if(bysource.hasOwnProperty(sourcekey))
            {
                var subsequents = bysource[sourcekey];
                if(subsequents.hasOwnProperty('targets'))
                {
                    for(var onekey in subsequents.targets)
                    {
                        showSubsequents(onekey);
                    }
                }
            }
            var thenodeoffset = fast_nodelookup_maps.key2offset[sourcekey];
            methods.changeNodeConnectionsShown(thenodeoffset, graphdata, true);
        };

        var showAntecedents = function(targetkey)
        {
            var thenodeoffset = fast_nodelookup_maps.key2offset[targetkey];
            methods.changeNodeConnectionsShown(thenodeoffset, graphdata, true);
        };
        
        showSubsequents(memberkey);
        showAntecedents(memberkey);
    };


    /**
     * Set display options to some reasonable values
     */
    methods.setNodeDisplayOptions = function(graphdata, displayfacts, autohide)
    {
        var hide_completed = graphdata.hide_completed_branches;
        if(!graphdata.hasOwnProperty('fastlookup_maps'))
        {
            throw "Expected to have graphdata.fastlookup_maps!!!";
        }
        
        //See if user or project have a saved preference in the graphdata object
        if(graphdata.hasOwnProperty('display_preferences'))
        {
            if(graphdata.display_preferences.hasOwnProperty('project'))
            {
                //TODO!!!!!!!!!!! 
            }
            if(graphdata.display_preferences.hasOwnProperty('user'))
            {
                //TODO!!!!!!!!!!! 
            }
            if(graphdata.display_preferences.hasOwnProperty('recent_edits'))
            {
                //TODO!!!!!!!!!!! 
            }
        }

        if(autohide)
        {
            //Hide antecedents automatically based on crowding
            var toomany4anylevel = 1 + bigfathom_util.env.multilevelhierarchy.connector_logic.comfortable_max_nodes_per_level;
            var collapseall_threshhold = bigfathom_util.env.multilevelhierarchy.connector_logic.collapseall_threshhold;

            var level_keys = Object.keys(displayfacts.nodecount_bylevel);
            for(var levelkeyidx=level_keys.length-1; levelkeyidx > 1; levelkeyidx--)    //Leave root alone
            {
                var levelkey = level_keys[levelkeyidx];
                var nodecount_at_level = displayfacts.nodecount_bylevel[levelkey];
                if(nodecount_at_level >= collapseall_threshhold)
                {
                    //Simply collapse all nodes at level 2 and quit
                    var offsetsof_nodes2hide = displayfacts.nodeoffset_bylevel[2];
                    for(var listidx=0; listidx<offsetsof_nodes2hide.length; listidx++)
                    {
                        var nodeoffset = offsetsof_nodes2hide[listidx];
                        methods.changeNodeConnectionsShown(nodeoffset, graphdata, false);
                    }
                    break;
                } else {
                    //Should we collapse some nodes at this level?
                    if(nodecount_at_level >= toomany4anylevel)
                    {
                        var nodeoffsets_atprevlevel = displayfacts.nodeoffset_bylevel[levelkey-1];
                        var ant_count_map = {};
                        var ant_sizes = [];
                        for(var listidx=0; listidx<nodeoffsets_atprevlevel.length; listidx++)
                        {
                            var nodeoffset = nodeoffsets_atprevlevel[listidx];
                            var onenode = graphdata.nodes[nodeoffset];
                            var nodekey = onenode.key;
                            if(graphdata.fastlookup_maps.links.bytarget.hasOwnProperty(nodekey))
                            {
                                var ant_nodes = graphdata.fastlookup_maps.links.bytarget[nodekey];
                                var ant_keys = Object.keys(ant_nodes.sources);
                                if(!ant_count_map.hasOwnProperty(ant_keys.length))
                                {
                                    ant_count_map[ant_keys.length] = [];
                                    ant_sizes.push(ant_keys.length);
                                }
                                ant_count_map[ant_keys.length].push(nodeoffset);
                            }
                        }
                        ant_sizes.sort();
                        var num2hide = 1 + nodecount_at_level - toomany4anylevel;  //Amount we want to hide
                        for(var size_idx=ant_sizes.length-1; size_idx>0; size_idx--)
                        {
                            var size = ant_sizes[size_idx];
                            var offsetsof_nodes2hide = ant_count_map[size];
                            for(var listidx=0; listidx<offsetsof_nodes2hide.length; listidx++)
                            {
                                var nodeoffset = offsetsof_nodes2hide[listidx];
                                methods.changeNodeConnectionsShown(nodeoffset, graphdata, false);
                                num2hide -= size; 
                                if(num2hide < 1)
                                {
                                    break;
                                }
                            }
                            if(num2hide < 1)
                            {
                                break;
                            }
                        }
                    }
                }
            }
        }
    };

    /**
     * Adjust the hierarchy_level of all nodes based on links.
     * Also set the number of sublanes in a lane.
     * Hides some branches automatically if autohide=true.
     */
    methods.resetAllNodeHierarchy = function(graphdata, autohide)
    {
        if(bigfathom_util.env.is_model_out_of_synch)
        {
            console.log("WARNING at resetAllNodeHierarchy LOCAL MODEL IS OUT OF SYNCH!");
        }
        var hide_completed = graphdata.hide_completed_branches;
        if(!graphdata.hasOwnProperty('fastlookup_maps'))
        {
            throw "Expected to have graphdata.fastlookup_maps!!!";
        }
        //var fastlookup_maps = graphdata.fastlookup_maps;
        var nodes = graphdata.nodes;
        var link_by_offset = graphdata.linksbundle.link_by_offset;
        var rootnodeid = graphdata.rootnodeid;
        var treerootnode = nodes[graphdata.fastlookup_maps.nodes.id2offset[rootnodeid]];
        if (typeof autohide === 'undefined')
        {
            autohide = true;
        };

        //Reset the max level count for each lane
        var max_level = -1;
        var lanenum;
        var lanetrackmaxlevels = {};
        lanetrackmaxlevels[bigfathom_util.env.multilevelhierarchy.hierarchy_lane] = 1;
        
        //Reset all non-root hierarchy values
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(!onenode.is_background)            
            {
                if(onenode.hasOwnProperty("subtype"))
                {
                    //Initialize like this
                    bigfathom_util.nodes.clearAllAntecedentDeclarations(onenode);
                }
                if(!onenode.hasOwnProperty('hierarchy_level'))
                {
                    throw "ERROR DID NOT FIND hierarchy_level property in " + JSON.stringify(onenode);
                    onenode['hierarchy_level'] = 2;
                }
                onenode.assignment = bigfathom_util.env.multilevelhierarchy.hierarchy_lane;        //Start by assuming it is in heirarchy 
                if(hide_completed && onenode.status_detail.terminal_yn == 1)
                {
                    onenode.show_node = false;     //We hide these.
                } else {
                    onenode.show_node = true;      //Start by showing it
                }
                if(!onenode.is_rootnode)
                {
                    onenode.hierarchy_level = 2;   //Initialize like this
                } else {
                    onenode.hierarchy_level = 1;   //Initialize like this
                }
                onenode._has_child = false;    //Initialize like this
                onenode._has_parent = false;   //Initialize like this
                onenode._has_visible_parent = false;   //Initialize like this
            }
        }

        //Set all the parent/child flags
        var connected_candidates = {};
        for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
        {
            var onelink = link_by_offset[linkidx];
            var targetnode = nodes[onelink.trgno];
            var sourcenode = nodes[onelink.srcno];
            targetnode._has_child = true;
            sourcenode._has_parent = true;
            if(sourcenode.is_candidate)
            {
                connected_candidates[sourcenode.key] = sourcenode;
            }
            var flagname = 'has_ant_' + sourcenode.type;
            if(targetnode.hasOwnProperty("subtype") && targetnode.subtype.indexOf(flagname) < 0)
            {
                //Add the missing indicator
                //console.log("NOTE adding missing " + flagname + " to " + targetnode.key);
                bigfathom_util.nodes.changeSubtype(targetnode, 'has_ant_' + sourcenode.type, null);
            }
        }
        
        /**
         * Apply change to each antecedent node of the branch
         */
        var hideEachAntecedantNode = function(rootnode, graphdata)
        {
            var childcount=0;
            var rootkey = rootnode.key;
            var nodes_in_branch = methods.getAllNodesInBranch(rootnode, graphdata, 0);
            for(var nodekey in nodes_in_branch)
            {
                if(nodekey !== rootkey)
                {
                    //Hide this one
                    childcount++;
                    var onenode = nodes_in_branch[nodekey];
                    onenode.show_node = false;
                }
            }
            if(childcount > 0)
            {
                bigfathom_util.nodes.hideAllAntecedents(rootnode);
            }
            bigfathom_util.nodes.removeSubtype(rootnode, 'warn_disconnected_rootnode');
            rootnode.assignment = bigfathom_util.env.multilevelhierarchy.unassigned_lane;
        };
        
        //Identify all the stuck disconnected branches
        var orphaned_parents = {};
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(!graphdata.hide_completed_branches 
                    || !graphdata.completed_branch_rootids.hasOwnProperty(onenode.nativeid))
            {
                if(onenode.hierarchy_level > 1 && onenode._has_child && !onenode._has_parent)
                {
                    if(!methods.hasTreeRootedChildOutsideBranch(treerootnode, onenode, graphdata))
                    {
                        //Move branch back to the candidate lane
                        hideEachAntecedantNode(onenode, graphdata);
                    } else {
                        //Leave in place but MARK this branch root as needing a connection!
                        bigfathom_util.nodes.addSubtype(onenode, 'warn_disconnected_rootnode');
                    }
                }
            }            
        };

        //For hide nodes if we are hiding completed branches
        if(hide_completed)
        {
            for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
            {
                var onenode = nodes[nodeidx];
                if(graphdata.completed_branch_rootids.hasOwnProperty(onenode.nativeid))
                {
                    onenode.show_node = false;
                }            
            };
        }

        //Hide nodes based on branch situation
        for(var rootkey in orphaned_parents)
        {
            var branchinfo = orphaned_parents[rootkey];
            if(!branchinfo.has_nexus_nodes)
            {
                var childcount = 0;
                for(var nodekey in branchinfo.nodes_in_branch)
                {
                    if(nodekey !== rootkey)
                    {
                        //Hide this one
                        childcount++;
                        var onenode = branchinfo.nodes_in_branch[nodekey];
                        onenode.show_node = false;
                    }
                }
                if(childcount > 0)
                {
                    bigfathom_util.nodes.hideAllAntecedents(branchinfo.rootnode);
                }
            }
        }
        
        //Now set all hierarchy to largest value via link checks
        var shifted = 1;    //Just to start
        while(shifted > 0)
        {
            shifted = 0;
            for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
            {
                var onelink = link_by_offset[linkidx];
                var targetnode = nodes[onelink.trgno];
                var sourcenode = nodes[onelink.srcno];
                if(!graphdata.hide_completed_branches 
                        || (!graphdata.completed_branch_rootids.hasOwnProperty(targetnode.nativeid)
                            && !graphdata.completed_branch_rootids.hasOwnProperty(sourcenode.nativeid)))
                {
                    var target_hl = targetnode.hierarchy_level;
                    var source_hl = sourcenode.hierarchy_level;
                    lanenum = sourcenode.assignment;
                    max_level = lanetrackmaxlevels[lanenum];
                    targetnode._has_child = true;
                    sourcenode._has_parent = true;
                    if(targetnode.show_node)
                    {
                        sourcenode._has_visible_parent = true;
                    }
                    if(source_hl <= target_hl)
                    {
                        //Shift this one down because it is at the wrong level!
                        shifted++;
                        sourcenode.hierarchy_level = target_hl+1;
                    }
                    //Check to see if we need to bump themax_level now
                    if(sourcenode.hierarchy_level > max_level)
                    {
                        max_level = sourcenode.hierarchy_level;
                        lanetrackmaxlevels[lanenum] = max_level;
                        if(max_level > bigfathom_util.env.multilevelhierarchy.max_tree_levels && max_level > link_by_offset.length)
                        {
                            //Catch runaway recursion
                            console.log("ERROR resetAllNodeHierarchy shifted=" + shifted + " with max_level=" + max_level);
                            console.log("ERROR resetAllNodeHierarchy link_by_offset=" + JSON.stringify(link_by_offset));
                            throw "ERROR resetAllNodeHierarchy shifted=" + shifted + " with max_level=" + max_level;
                        }
                    }
                }
            }
        }
        
        //Now move all nodes that are showing and have no visible parent and NOT connected to outside branch
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(!onenode.is_rootnode && onenode.assignment !== bigfathom_util.env.multilevelhierarchy.unassigned_lane)
            {
                if((!onenode._has_parent && onenode.show_node) 
                        || (onenode.is_candidate &&  !onenode._has_visible_parent))
                {
                    if(!methods.hasTreeRootedChildOutsideBranch(treerootnode, onenode, graphdata))
                    {
                        //Move back to the candidate lane
                        onenode.assignment = bigfathom_util.env.multilevelhierarchy.unassigned_lane;
                        bigfathom_util.nodes.removeSubtype(onenode, 'warn_disconnected_rootnode');
                        bigfathom_util.nodes.hideAllAntecedents(onenode);
                    }
                }
            }
        }
        
        //Figure out lowest level
        for(var rootkey in orphaned_parents)
        {
            var branchinfo = orphaned_parents[rootkey];
            if(branchinfo.has_nexus_nodes)
            {
                var childcount = 0;
                var lowest_level = 9999;
                for(var nodekey in branchinfo.nodes_in_branch)
                {
                    if(nodekey !== rootkey)
                    {
                        //Check this one
                        childcount++;
                        var onenode = branchinfo.nodes_in_branch[nodekey];
                        if(onenode.hierarchy_level < lowest_level)
                        {
                            lowest_level = onenode.hierarchy_level; 
                        }
                    }
                }
                if(childcount > 0)
                {
                    branchinfo.rootnode.hierarchy_level = lowest_level-1;
                }
            }
        }        

        //Now factor in show/hide preferences for each node
        var displayfacts = {
                                nodecount_bylevel:{}, 
                                nodeoffset_bylevel:{}
                            };
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(!graphdata.hide_completed_branches 
                    || !graphdata.completed_branch_rootids.hasOwnProperty(onenode.nativeid))
            {
                if(onenode.assignment !== bigfathom_util.env.multilevelhierarchy.unassigned_lane && onenode.show_node)
                {
                    if(!displayfacts.nodecount_bylevel.hasOwnProperty(onenode.hierarchy_level))
                    {
                        displayfacts.nodecount_bylevel[onenode.hierarchy_level] = 0;    
                        displayfacts.nodeoffset_bylevel[onenode.hierarchy_level] = [];
                    }
                    displayfacts.nodecount_bylevel[onenode.hierarchy_level]++;
                    displayfacts.nodeoffset_bylevel[onenode.hierarchy_level].push(nodeidx);
                }
            }
        }
        methods.setNodeDisplayOptions(graphdata, displayfacts, autohide);

        //Now hide antecedent nodes if dependent node is not expanded
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(onenode.hierarchy_level > 1)
            {
                if(onenode.assignment === bigfathom_util.env.multilevelhierarchy.hierarchy_lane)
                {
                    if(bigfathom_util.nodes.hasHiddenAntecedents(onenode))
                    {
                        //TODO show_node=fase for all direct ants!
                        console.log("LOOK TODO hide ants of " + onenode.key);
                    }
                }
            }
        }        

        //Now set the number of sublanes of each lane    
        lanenum = 1;
        max_level = lanetrackmaxlevels[lanenum];
        methods.setSublanesToNumber(lanenum, max_level);
        methods.setSublanesToNumber(2, 1);  //No sublanes in the second lane

        return lanetrackmaxlevels;
    };

    /**
     * Set the number of sublanes in the lane
     */
    methods.setSublanesToNumber = function(lanenum, max_level)
    {
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            console.log("ERROR bad lanenum provided to setSublanesToNumber!  lanenum=" + lanenum);
        }
        var laneidx = lanenum - 1;
        var selected_lane = corefacts.lanes[laneidx];
        var sublane_count = selected_lane.sublanes.length;
        var count_diff = sublane_count - max_level;
        if(count_diff > 0)
        {
            //Need to remove extra sublanes
            selected_lane.sublanes.splice(-count_diff, count_diff);
            methods.recomputeSublanePositionFacts(lanenum);
        } else {
            if(count_diff < 0)
            {
                //Need to add lanes
                methods.addSublanesIfUsed(lanenum, max_level);
            }
        }
    };

    /**
     * Add or remove sublanes as needed
     */
    methods.adjustSublanesIfNeeded = function(lanenum, nodes)
    {
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            console.log("ERROR bad lanenum provided to adjustSublanesIfNeeded!  lanenum=" + lanenum);
        }
        var laneidx = lanenum - 1;
        var selected_lane = corefacts.lanes[laneidx];
        var sublane_count = selected_lane.sublanes.length;
        var min_level = 1;
        var max_level = 0;
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(onenode.assignment === lanenum)
            {
                if(onenode.hierarchy_level < min_level)
                {
                    min_level = onenode.hierarchy_level;
                }
                if(onenode.hierarchy_level > max_level)
                {
                    max_level = onenode.hierarchy_level;
                }
            }
        }
        var count_diff = sublane_count - max_level;
        if(count_diff > 0)
        {
            //Need to remove extra sublanes
            selected_lane.sublanes.splice(-count_diff, count_diff);
            methods.recomputeSublanePositionFacts(lanenum);
        } else {
            if(count_diff < 0)
            {
                //Need to add lanes
                methods.addSublanesIfUsed(lanenum, max_level);
            }
        }
    };

    /**
     * Update the positioning facts for each sublane
     */
    methods.recomputeSublanePositionFacts = function (lanenum)
    {
        var laneidx;
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            laneidx = corefacts.lane_count;
        } else {
            laneidx = lanenum - 1;
        }
        var selected_lane = corefacts.lanes[laneidx];
        var sublane_count = selected_lane.sublanes.length;
        var lane_width = selected_lane.width;
        var sublane_start_x = selected_lane.start_x;
        var sublane_width = lane_width / sublane_count;
        var sublane_top_y = selected_lane.top_y;

        for(var sublaneidx = 0; sublaneidx < sublane_count; sublaneidx++)
        {
            var onesublane_def = selected_lane.sublanes[sublaneidx];
            onesublane_def = getRecomputedSublaneDef(onesublane_def
                        , sublane_count, sublaneidx
                        , sublane_start_x, sublane_top_y
                        , sublane_width, corefacts.lane_height);         
            selected_lane.sublanes[sublaneidx] = onesublane_def;
            sublane_start_x += sublane_width;
        }
    };

    /**
     * Adds more sublanes if needed
     */
    methods.addSublanesIfUsed = function (lanenum, new_hierarchy_level)
    {
        if(lanenum === null || lanenum > corefacts.lane_count || lanenum < 1)
        {
            throw "ERROR in addSublanesIfUsed("+ lanenum+","+new_hierarchy_level+") Did NOT specify a valid lanenum!";
        }
        
        var laneidx = lanenum - 1;
        var selected_lane = corefacts.lanes[laneidx];
        var sublane_count = selected_lane.sublanes.length;
        if(new_hierarchy_level > sublane_count)
        {
            //Add more sublanes
            var lane_width = selected_lane.width;
            var firstnew_sublane_hl = sublane_count+1;
            var lastnew_sublane_hl = new_hierarchy_level;

            var sublane_width = lane_width / new_hierarchy_level;
            var sublane_start_x = selected_lane.start_x;
            var sublane_end_x = sublane_start_x + sublane_width;
            var sublane_hpad = sublane_width / 10;

            //var sublane_center_x = sublane_start_x + sublane_hpad;
            //var sublane_center_y = selected_lane.content_center.y;
            var sublane_top_y = selected_lane.top_y;
            if(isNaN(sublane_start_x) || isNaN(sublane_top_y))
            {
                throw "Failed addSublanesIfUsed because NaN found in " 
                        + JSON.stringify(selected_lane)
                        + " sublane_start_x=" + sublane_start_x 
                        + " sublane_top_y=" + sublane_top_y;
            }
            
            //Change defs of existing sublanes
            for(var sublaneidx = 0; sublaneidx < sublane_count; sublaneidx++)
            {
                var onesublane_def = selected_lane.sublanes[sublaneidx];
                onesublane_def['start_x'] = sublane_start_x;
                onesublane_def['end_x'] = sublane_end_x;
                onesublane_def['width'] = sublane_width;
                onesublane_def['hmargin'] = sublane_hpad;
                onesublane_def = getRecomputedSublaneDef(onesublane_def
                            , sublane_count, sublaneidx
                            , sublane_start_x, sublane_top_y
                            , sublane_width, corefacts.lane_height);         
                selected_lane.sublanes[sublaneidx] = onesublane_def;
                sublane_start_x -= sublane_width;   //REVERSEDX
                sublane_end_x = sublane_start_x + sublane_width;
            }
            
            //Add the new sublanes
            for(var sublane_hl = firstnew_sublane_hl; sublane_hl <= lastnew_sublane_hl; sublane_hl++)
            {
                var onesublane_def = {'comment':'added dynamically'};
                onesublane_def['hierarchy_level'] = sublane_hl;
                onesublane_def['start_x'] = sublane_start_x;
                onesublane_def['end_x'] = sublane_end_x;
                onesublane_def['width'] = sublane_width;
                onesublane_def['hmargin'] = sublane_hpad;
                onesublane_def = getRecomputedSublaneDef(onesublane_def
                            , sublane_count, sublane_hl-1
                            , sublane_start_x, sublane_top_y
                            , sublane_width, corefacts.lane_height);         
                selected_lane.sublanes[sublaneidx] = onesublane_def;
                selected_lane.sublanes.push(onesublane_def);
                sublane_start_x -= sublane_width;   //REVERSEDX
                sublane_end_x = sublane_start_x + sublane_width;
            }
        }
    };

    /**
     * Removes sublanes if not needed
     */
    methods.removeSublanesIfUnused = function (lanenum, nodes)
    {
        var laneidx;
        if(lanenum === null || lanenum > corefacts.lane_count)
        {
            laneidx = corefacts.lane_count;
        } else {
            laneidx = lanenum - 1;
        }
        var selected_lane = corefacts.lanes[laneidx];
        var sublane_count = selected_lane.sublanes.length;
        
        var min_level = 1;
        var max_level = 0;
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(onenode.assignment === lanenum)
            {
                if(onenode.hierarchy_level < min_level)
                {
                    min_level = onenode.hierarchy_level;
                }
                if(onenode.hierarchy_level > max_level)
                {
                    max_level = onenode.hierarchy_level;
                }
            }
        }        

        var extra_count = sublane_count - max_level;
        if(extra_count > 0)
        {
            //Need to remove extra sublanes
            selected_lane.sublanes.splice(-extra_count, extra_count);
            methods.recomputeSublanePositionFacts(lanenum);
        }
    };

    /**
     * Precondition corefacts.lane_defs[0].sublanes is already set!
     * Automatically minimizes the candidate tray if empty.
     */
    methods.recomputeCoreFacts = function (graphdata)
    {
        if(typeof action_manager === 'undefined')
        {
            throw "How can we be missing action_manager here???";
        }
        if(typeof corefacts === 'undefined')
        {
            throw "How can we be missing corefacts here???";
        }
        if(corefacts.hasOwnProperty("defined") && corefacts.defined)
        {
            corefacts.defined = false;
            bigfathom_util.d3v3_svg.refreshCanvasDimensions(corefacts.canvas);
        }
        
        //Update the sublane defs with current defs!!!
        var h_lane = corefacts.lanes[0];
        var h_lane_def_input = corefacts.lane_defs[0];
        
        //Create new sublane definitions to match current hierarchy level count
        h_lane_def_input.sublane_defs = [];   
        for(var i = 0; i < h_lane.sublanes.length; i++)
        {
            var level = i+1;
            var onenode = {
                    'label': "level " + level,
                    'hierarchy_level': level
                };

            h_lane_def_input.sublane_defs.push(onenode);
        }
        
        var hun = methods.hasUnassignedNodes(graphdata);
        return methods.getCoreFacts(!hun);
    };

    methods.hasUnassignedNodes = function(graphdata, changePosition)
    {
        var nodes;
        if(typeof graphdata === 'undefined')
        {
            throw "Expected graph data!";
        }
        var nodes = graphdata.nodes;
        var has_unassigned_nodes = false;
        for(var nodeidx = 0; nodeidx < nodes.length; nodeidx++)
        {
            var onenode = nodes[nodeidx];
            if(onenode.hasOwnProperty('assignment'))
            {
                if(onenode.assignment > 1)
                {
                    has_unassigned_nodes = true;
                    break;
                }
            }
        }
        return has_unassigned_nodes;
    };

    methods.minimizeUnassignedNodeTray = function()
    {
        bigfathom_util.env.multilevelhierarchy.show_unassigned_lane = false;
        //TODO -- display the change
    };

    methods.maximizeUnassignedNodeTray = function()
    {
        bigfathom_util.env.multilevelhierarchy.show_unassigned_lane = true;
        //TODO -- display the change
    };

    /**
     * Builds 'lanes' array and other properties to the corefacts and returns it
     * 0 = hierarchy integration lane
     * 1 = unassigned content lane
     */
    methods.getCoreFacts = function (minimized_candidate_tray) 
    {
        if(bigfathom_util.env.is_model_out_of_synch)
        {
            console.log("WARNING at getCoreFacts LOCAL MODEL IS OUT OF SYNCH!");
        }
        
        if(typeof corefacts === 'undefined')
        {
            throw "How can we be missing corefacts in getCoreFacts???";
        }
        if(typeof minimized_candidate_tray === 'undefined')
        {
            minimized_candidate_tray = !bigfathom_util.env.multilevelhierarchy.show_unassigned_lane;
        }
        //Already defined?
        if(corefacts.hasOwnProperty("defined") && corefacts.defined)
        {
            //Yes, don't bother computing these again
            return corefacts;
        }

        //Check for bad input now
        if(!corefacts.hasOwnProperty("lane_defs"))
        {
            throw "Expected lane_defs but missing from corefacts=" + JSON.stringify(corefacts);
        }
        if(corefacts.lane_defs.length !== 2)
        {
            throw "Expected 2 lane_defs but instead have " + corefacts.lane_defs.length +  " in corefacts=" + JSON.stringify(corefacts);
        }
        
        var visible_coordinates = corefacts.canvas.getVisibleRectangleFacts();   
        //Todo use the visible_coordinates to move the green tray
        
        var simple_lane_count = 1;
        var h_lane_def_input = corefacts.lane_defs[0];
        var u_lane_def_input = corefacts.lane_defs[1];

        var node_content_is_tall = false;
        var tall_scale_factor = 1;
        var min_sublane_width = bigfathom_util.env.multilevelhierarchy.min_sublane_width;
        var max_sublane_width = bigfathom_util.env.multilevelhierarchy.max_sublane_width;
        if(h_lane_def_input.hasOwnProperty("max_nodes_one_sublane"))
        {
            if(h_lane_def_input.max_nodes_one_sublane > 40)
            {
                //Increase width of sublanes to mitigate the line overlaps
                min_sublane_width = min_sublane_width * tall_scale_factor;
                if(min_sublane_width>max_sublane_width)
                {
                    min_sublane_width = max_sublane_width;
                }
                node_content_is_tall=true;
                tall_scale_factor = h_lane_def_input.max_nodes_one_sublane / 40;
            }
        }
        
        var has_top_legend = false;
        if(h_lane_def_input.hasOwnProperty("top_legend") || u_lane_def_input.hasOwnProperty("top_legend"))
        {
            has_top_legend = true;   
        }

        var sublane_count = 0;
        if(h_lane_def_input.hasOwnProperty("sublane_defs"))
        {
            sublane_count = h_lane_def_input.sublane_defs.length;
        }
        if(!sublane_count)
        {
            console.log("DEBUG h_lane_def_input because missing sublane_defs! >>> " + JSON.stringify(h_lane_def_input));
            throw "ERROR cannot have 0 sublanes!";
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

        usable_h = node_content_is_tall ? usable_h * tall_scale_factor : usable_h;
        usable_w = node_content_is_tall ? usable_w * tall_scale_factor : usable_w;

        corefacts["defined"] = true;
        corefacts["vgap"] = vgap;
        corefacts["hgap"] = hgap;
        corefacts["usable_h"] = usable_h;
        corefacts["usable_w"] = usable_w;

        corefacts["lane_date_y"] = vgap;
        corefacts["lane_title_y"] = vgap + 20;
        corefacts["lane_height"] = usable_h - blankline_size;

        //Different lanes have different sizes
        var nominal_lane_width = usable_w / corefacts.lane_count;
        var simple_lane_width = nominal_lane_width / 2;
        var total_hierarchy_lane_visible_width = usable_w;  //Size this as the screen width instead of - total_simple_lane_widths;
        
        var sublane_width = Math.max(min_sublane_width, total_hierarchy_lane_visible_width / sublane_count);
        var total_hierarchy_lane_width = Math.max(total_hierarchy_lane_visible_width, sublane_width * sublane_count);

        if(!sublane_width || !total_hierarchy_lane_width)
        {
            console.log("LOOK sublane_count=" + sublane_count);
            console.log("LOOK min_sublane_width=" + min_sublane_width);
            console.log("LOOK total_hierarchy_lane_visible_width=" + total_hierarchy_lane_visible_width);
            console.log("LOOK sublane_width=" + sublane_width);
            console.log("LOOK total_hierarchy_lane_width=" + total_hierarchy_lane_width);
            throw "ERROR computing width!";
        }
        
        var hierarchy_lane_width = total_hierarchy_lane_width;
        var unassigned_lane_width = bigfathom_util.env.multilevelhierarchy.unassigned_lane_width;   //simple_lane_width;
        
        corefacts["simple_lane_width"] = simple_lane_width;
        corefacts["hierarchy_lane_width"] = hierarchy_lane_width;
        corefacts["hierarchy_lane_visible_width"] = total_hierarchy_lane_visible_width;
        corefacts["unassigned_lane_width"] = unassigned_lane_width;
        corefacts["lanes"] = [];

        var lanenum;
        var onelanedef_input;
        var onelane;
        var label_tx;
        var end_x;
        var cur_x = hgap;
        var start_x = cur_x;
        var start_y = corefacts.vgap;
        var center_y = start_y + usable_h / 2; //corefacts.usable_h / 2;
        var thislane_width;
        var prevlane_start_x;
        var prevlane_end_x;
        var is_simple_lane;   //Is TRUE only for the lane(s) with unassigned stuff
        var node_start_x_factor = 4;
        
        var hmargin;
        var content_center;
        var y_zones_dy = usable_h / 5;
        var y_zones = {'dy':y_zones_dy,'under':[center_y-y_zones_dy],'middle':center_y, 'over':[center_y+y_zones_dy]};
        for(var i = 0; i < 2; i++)
        {
            if(i === 0)
            {
                //A tree is displayed here. (hierarchy area)
                lanenum = bigfathom_util.env.multilevelhierarchy.hierarchy_lane;
                thislane_width = total_hierarchy_lane_width;
                start_x = total_hierarchy_lane_visible_width - total_hierarchy_lane_width; //total_hierarchy_lane_visible_width - total_hierarchy_lane_width;
                end_x = start_x + thislane_width; //total_hierarchy_lane_visible_width;
                onelanedef_input = h_lane_def_input;
                hmargin = thislane_width / 10;
                content_center = {
                        "x": start_x + Math.min(20,thislane_width / node_start_x_factor),
                        "y": center_y,
                        "y_zones" : y_zones
                    };
                prevlane_end_x = end_x;
                prevlane_start_x = start_x;
                    
            } else {
                //No tree displayed here. (unassigned node area)
                lanenum = bigfathom_util.env.multilevelhierarchy.unassigned_lane;
                thislane_width = unassigned_lane_width; //simple_lane_width;
                hmargin = Math.max(30, thislane_width / 100);
                if(!minimized_candidate_tray)
                {
                    start_x = Math.max(visible_coordinates.x1 + corefacts.hgap, prevlane_start_x - corefacts.hgap - thislane_width);    //REVERSEDX
                } else {
                    start_x = Math.max(visible_coordinates.x1 + corefacts.hgap - thislane_width, prevlane_start_x - corefacts.hgap - thislane_width);    //REVERSEDX
                }
                end_x = start_x + thislane_width;
                onelanedef_input = u_lane_def_input;
                content_center = {
                        "x": start_x + hmargin,
                        "y": center_y
                    };
            }
            
            is_simple_lane = onelanedef_input.is_simple_lane;
            if(onelanedef_input.hasOwnProperty("label"))
            {
                label_tx = onelanedef_input.label;
            } else {
                label_tx = "Lane " + lanenum;
            }
            if(!start_x && start_x !== 0)
            {
                console.log("LOOK i=" + i);
                console.log("LOOK thislane_width=" + thislane_width);
                console.log("LOOK start_x=" + start_x);
                console.log("LOOK end_x=" + end_x);
                throw "ERROR computing start_x for i=" + i + " of " + JSON.stringify(onelanedef_input);
            }
            
            onelane = {
                "label" : label_tx,
                'is_simple_lane': is_simple_lane,
                "maxwidth": thislane_width,
                "start_x" : start_x,
                "end_x" : end_x, 
                "width" : thislane_width, 
                "top_y" : start_y,
                "hmargin" : hmargin,
                "content_center" : content_center
            };
            if(onelanedef_input.hasOwnProperty("top_legend"))
            {
                if(onelanedef_input.top_legend.hasOwnProperty("start_text"))
                {
                    onelane["top_legend"] = {"start_text": onelanedef_input.top_legend.start_text};
                }
            }
            var sublanes = [];
            if(onelanedef_input.hasOwnProperty("sublane_defs"))
            {
                var sublane_hpad = sublane_width / 10;
                var sublane_count = onelanedef_input.sublane_defs.length;
                var sublane_width = thislane_width / sublane_count;
                var sublane_start_x = Math.max(usable_w - sublane_width, usable_w / 2);   //REVERSEDX
                if (sublane_count === 1)
                {
                    sublane_start_x = usable_w / 2;   //REVERSEDX
                } else {
                    sublane_start_x = usable_w - sublane_width;   //REVERSEDX
                }
                var sublane_end_x = sublane_start_x + sublane_width;  //REVERSEDX
                for(var sublaneidx = 0; sublaneidx < sublane_count; sublaneidx++)
                {
                    var onesublane_def = onelanedef_input.sublane_defs[sublaneidx];
                    
                    onesublane_def['hierarchy_level'] = onesublane_def.hierarchy_level;
                    onesublane_def['start_x'] = sublane_start_x;
                    onesublane_def['end_x'] = sublane_end_x;
                    onesublane_def['width'] = sublane_width;
                    onesublane_def['hmargin'] = sublane_hpad;
                    onesublane_def = getRecomputedSublaneDef(onesublane_def
                                , sublane_count, sublaneidx
                                , sublane_start_x, start_y
                                , sublane_width, corefacts.lane_height);         

                    sublanes.push(onesublane_def);
                    sublane_start_x = sublane_start_x - sublane_width;  //REVERSEDX
                    sublane_end_x = sublane_start_x + sublane_width;  //REVERSEDX
                }
                onelane['sublane_hpad'] = sublane_hpad;
                onelane['sublane_width'] = sublane_width;
            }
            onelane['sublanes'] = sublanes;
            corefacts.lanes.push(onelane);
        }
        corefacts.lanes.push(onelane);
        
        return corefacts;
    };

    methods.getAllContainerAttribs = function ()
    {
        var attribs = {backgroundbundle:null, targets:null, corefacts:null};
        
        attribs.corefacts = this.getCoreFacts();
        attribs.backgroundbundle = this.getBackgroundElementsBundle();
        attribs.targets = this.getTargetElements();
        
        return attribs;
    };

    return action_manager;
};

