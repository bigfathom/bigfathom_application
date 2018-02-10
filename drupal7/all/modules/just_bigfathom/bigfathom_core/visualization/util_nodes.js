/* 
 * Functions for working with node data
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
if(!bigfathom_util.hasOwnProperty("nodes"))
{
    //Create the object property because it does not already exist
    bigfathom_util.nodes = {
         version: "20161008.2"
        ,all_typenames: ['goal','task','equip','extrc','equjb','xrcjb']
        ,all_typeletters: ['P','G','T','Q','X']
    };
};

bigfathom_util.nodes.getHelper = function(status_cd_lookup)
{
    if(typeof status_cd_lookup === 'undefined')
    {
        throw "Must provide status_cd_lookup!!!!";
    }
    var methods = {};
    methods.getNewNode = function (is_rootnode, is_candidate, nativeid, key, typename, subtype, labelobj
                    , fill_opacity, xpos, ypos
                    , is_drag_source
                    , is_drag_target
                    , assignment
                    , hierarchy_level
                    , status_cd
                    , show_node
                    , show_children
                    , status_detail
                    , maps)
    {
        if(status_detail === null)
        {
            status_detail = status_cd_lookup[status_cd];
            if(typeof status_detail === 'undefined')
            {
                status_detail = null;    
            }
        }
        return bigfathom_util.nodes.getNewNode(is_rootnode, is_candidate, nativeid, key, typename, subtype, labelobj
                    , fill_opacity, xpos, ypos
                    , is_drag_source
                    , is_drag_target
                    , assignment
                    , hierarchy_level
                    , status_cd
                    , show_node
                    , show_children
                    , status_detail
                    , maps);
                
    };
    return methods;
};

bigfathom_util.nodes.filter = function(graphdata, candidate_property_name, check_show_node_matchvalue)
{
    
    if (typeof candidate_property_name === 'undefined' || candidate_property_name === null)
    {
        candidate_property_name = "subtype";    
    }
    
    if (typeof check_show_node_matchvalue === 'undefined' || check_show_node_matchvalue === null)
    {
        check_show_node_matchvalue = true;    
    }
    
    //Return true if okay to keep
    this.checkShowNode = function(onenode, match_value)
    {
        if(match_value === null || !onenode.hasOwnProperty('show_node'))  
        {
            return true;    //Treat as show if property does not exist or no match value provided
            /*
            if(onenode.fixed)
            {
                return true;    //Treat as show if property does not exist or no match value provided
            }
            throw "ERROR no match value provided for node=" + JSON.stringify(onenode);            
            */
        }
        return onenode.show_node === match_value;
    };
    
    //Return true if okay to keep, false if candidate value is in the exclude list
    this.checkNotExcluded = function(candidate, exclude_subtypename)
            {
                var pushit;
                if(!candidate.hasOwnProperty(candidate_property_name))
                {
                    //Nothing to check against
                    pushit = true;
                } else {
                    pushit = true;  //Include it unless we find a reason to exclude it
                    if(exclude_subtypename !== null && exclude_subtypename.constructor === Array)
                    {
                        for (var cexl=0; cexl < exclude_subtypename.length; cexl++)
                        {
                            var checknotmatch = exclude_subtypename[cexl];
                            if(checknotmatch !== null && candidate.subtype.indexOf(checknotmatch) > -1)
                            {
                                //Exclude it because we found a match
                                pushit = false;
                                break;
                            }
                        }
                    }
                }
                return pushit;
            };

    //Return true if any candidate subtype value is in the include list
    this.checkAnyIncluded = function(candidate, include_subtypename)
            {
                var pushit;
                if(!candidate.hasOwnProperty(candidate_property_name))
                {
                    //Nothing to check against
                    pushit = false;
                } else {
                    pushit = false; //Exclude it unless we find a reason to include it
                    if(include_subtypename !== null && include_subtypename.constructor === Array)
                    {
                        for (var cixl=0; cixl < include_subtypename.length; cixl++)
                        {
                            var checkmatch = include_subtypename[cixl];
                            if(checkmatch !== null && candidate.subtype.indexOf(checkmatch) > -1)
                            {
                                //Include it because we found a match
                                pushit = true;
                                break;
                            }
                        }
                    }
                }
                return pushit;
            };

    //Return true if any candidate subtype value is in the include list
    this.checkAnyPairedIncluded = function(candidate, paired_include, paired_exclude)
            {
                var pushit;
                if(!candidate.hasOwnProperty(candidate_property_name))
                {
                    //Nothing to check against
                    pushit = false;
                } else {
                    pushit = false; //Exclude it unless we find a reason to include it
                    if(paired_include !== null && paired_include.constructor === Array)
                    {
                        for (var cixl=0; cixl < paired_include.length; cixl++)
                        {
                            var checkmatch1 = paired_include[cixl];
                            var checkmatch2 = paired_exclude[cixl];
                            if(checkmatch1 !== null 
                                    && candidate.subtype.indexOf(checkmatch1) > -1 
                                    && candidate.subtype.indexOf(checkmatch2) < 0)
                            {
                                //Include it because we found a match
                                pushit = true;
                                break;
                            }
                        }
                    }
                }
                return pushit;
            };

    /**
     * Return true only if there is at least one inlude hit and none of those
     * hits are excluded.
     */
    this.checkAllPairedIncluded = function(candidate, paired_include, paired_exclude)
            {
                var included=0;
                var pushit;
                if(!candidate.hasOwnProperty(candidate_property_name))
                {
                    //Nothing to check against
                    pushit = false;
                } else {
                    pushit = false; //Exclude it unless we find a reason to include it
                    if(paired_include !== null && paired_include.constructor === Array)
                    {
                        for (var cixl=0; cixl < paired_include.length; cixl++)
                        {
                            var checkmatch1 = paired_include[cixl];
                            var checkmatch2 = paired_exclude[cixl];
                            if(candidate.subtype.indexOf(checkmatch1) > -1)
                            {
                                //We are on our way to maybe passing the filter.
                                pushit = true;
                                included++;
                            }
                            if(candidate.subtype.indexOf(checkmatch2) > -1 && included > 0)
                            {
                                //There is at least one excluded, so fail now.
                                pushit = false;
                                break;
                            }
                        }
                    }
                }
                return pushit;
            };

    //Return true if candidate value is in the include list
    this.checkAllIncluded = function(candidate, include_subtypename)
            {
                var pushit;
                if(!candidate.hasOwnProperty(candidate_property_name))
                {
                    //Nothing to check against
                    pushit = false;
                } else {
                    pushit = false; //Exclude it unless we find a reason to include it
                    if(include_subtypename !== null && include_subtypename.constructor === Array)
                    {
                        for (var cixl=0; cixl < include_subtypename.length; cixl++)
                        {
                            var checkmatch = include_subtypename[cixl];
                            if(checkmatch !== null)
                            {
                                if(candidate.subtype.indexOf(checkmatch) > -1)
                                {
                                    //Include it because we found a match
                                    pushit = true;
                                } else {
                                    //Exclude it because at least one did not match
                                    pushit = false;
                                    break;
                                }
                            }
                        }
                    }
                }
                return pushit;
            };


    this.matchesAnyTypeName = function (candidate, typename)
    {
        var typename_ar;
        if(typename.constructor !== Array)
        {
            typename_ar = [typename];
        } else {
            typename_ar = typename;
        }
        for(var tidx=0; tidx < typename_ar.length; tidx++)
        {
            if(typename_ar[tidx] === candidate.type)
            {
                return true;
            }
        }
        return false;
    };

    /**
     * Return TRUE if candidate matches the criteria
     */
    this.matchesIncludedNoneExcluded = function (candidate, typename, include_subtypename, assignment, ignore_removed, exclude_subtypename)
    {
        var pushit = false;
        var can_display = this.checkShowNode(candidate, check_show_node_matchvalue);
        if(can_display)
        {
            if(this.matchesAnyTypeName(candidate, typename))
            {
                if((assignment === null || assignment === candidate.assignment)
                    && (!ignore_removed || !candidate.into_trash && !candidate.into_parkinglot))
                {
                    if(include_subtypename === null)
                    {
                        //Include anything that is not excluded
                        pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                    } else {
                        //Look for matches
                        pushit = this.checkAllIncluded(candidate, include_subtypename);
                        if(pushit)
                        {
                            //Pass through exclusion filter
                            pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                        }
                    }
                }
            }
        }
        return pushit;
    };

    /**
     * Return TRUE if candidate matches the criteria
     */
    this.matchesPairedIncludeNoneExcluded = function (candidate, typename
                , include_all_subtypename
                , paired_include
                , paired_exclude
                , assignment
                , ignore_removed
                , exclude_subtypename)
    {
        var pushit = false;
        var can_display = this.checkShowNode(candidate, check_show_node_matchvalue);
        if(can_display)
        {
            if(this.matchesAnyTypeName(candidate, typename))
            {
                if( (assignment === null || assignment === candidate.assignment)
                        && (!ignore_removed || !candidate.into_trash && !candidate.into_parkinglot))
                {
                    pushit = false;
                    if(include_all_subtypename === null)
                    {
                        //Include anything that is not excluded
                        pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                    } else {
                        //Look for matches
                        pushit = this.checkAllIncluded(candidate, include_all_subtypename);
                        if(pushit)
                        {
                            //Pass through exclusion filter
                            pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                        }
                    }
                    if(pushit === true)
                    {
                        //Good so far, keep checking
                        if(paired_include !== null)
                        {
                            //Look for at least one match
                            //pushit = this.checkAnyPairedIncluded(candidate, paired_include, paired_exclude);
                            pushit = this.checkAllPairedIncluded(candidate, paired_include, paired_exclude);
                        }
                    }
                }
            }
        }
        return pushit;
    };

    /**
     * Return TRUE if the candidate matches the criteria
     */
    this.matchesIncludedAndAnyIncludedNoneExcluded = function(candidate
                , typename, include_all_subtypename
                , include_at_least_one_subtypename
                , assignment
                , ignore_removed
                , exclude_subtypename)
    {
        var can_display = this.checkShowNode(candidate, check_show_node_matchvalue);
        if(can_display)
        {
            var pushit = false;
            if(this.matchesAnyTypeName(candidate, typename))
            {
                if( (assignment === null || assignment === candidate.assignment)
                        && (!ignore_removed || !candidate.into_trash && !candidate.into_parkinglot))
                {
                    pushit = false;
                    if(include_all_subtypename === null)
                    {
                        //Include anything that is not excluded
                        pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                    } else {
                        //Look for matches
                        pushit = this.checkAllIncluded(candidate, include_all_subtypename);
                        if(pushit)
                        {
                            //Pass through exclusion filter
                            pushit = this.checkNotExcluded(candidate, exclude_subtypename);
                        }
                    }
                    if(pushit === true)
                    {
                        //Good so far, keep checking
                        if(include_at_least_one_subtypename !== null)
                        {
                            //Look for at least one match
                            pushit = this.checkAnyIncluded(candidate, include_at_least_one_subtypename);
                        }
                    }
                }
            }
        }
        return pushit;
    };

    /**
     * Return collection of nodes where ALL include text was found in the subtype and NONE of the exclude text matched
     */
    this.getAllIncludedNoneExcluded = function (nodes, typename, include_subtypename, assignment, ignore_removed, exclude_subtypename)
    {
        if (typeof include_subtypename === 'undefined' || include_subtypename === null)
        {
            include_subtypename = null;
        } else {
            if(include_subtypename.constructor !== Array)
            {
                include_subtypename = [include_subtypename];
            }
        };
        if (typeof assignment === 'undefined')
        {
            assignment = null;
        };
        if (typeof ignore_removed === 'undefined')
        {
            ignore_removed = true;
        };
        if (typeof exclude_subtypename === 'undefined' || exclude_subtypename === null)
        {
            exclude_subtypename = null;
        } else {
            if(exclude_subtypename.constructor !== Array)
            {
                exclude_subtypename = [exclude_subtypename];
            }
        };
        var pushit;
        var filtered = [];
        var nodescount = nodes.length;
        for (var i = 0; i < nodescount; i ++)
        {
            var candidate = nodes[i];
            pushit = this.matchesIncludedNoneExcluded(candidate, typename, include_subtypename, assignment, ignore_removed, exclude_subtypename);
            if(pushit === true)
            {
                filtered.push(candidate);
            }
        }
        return filtered;
    };

    this.getAllWithoutAntecedents = function (nodes, typename, include_all_subtypename, assignment, ignore_removed, exclude_subtypename)
    {
        if (typeof exclude_subtypename === 'undefined' || exclude_subtypename === null)
        {
            exclude_subtypename = [];
        } else {
            if(exclude_subtypename.constructor !== Array)
            {
                exclude_subtypename = [exclude_subtypename];
            }
        };
        var merged_exclude_subtypename = exclude_subtypename.concat(
                ['has_ant_goal','has_ant_task','has_ant_equip','has_ant_extrc','has_ant_equjb','has_ant_xrcjb']);
        return this.getAllIncludedAndAnyIncludedNoneExcluded(nodes
                    , typename
                    , include_all_subtypename
                    , null
                    , assignment
                    , ignore_removed
                    , merged_exclude_subtypename);
    };

    this.getAllHavingAnyHiddenAntecedents = function (nodes, typename
                , include_all_subtypename
                , assignment
                , ignore_removed
                , exclude_subtypename)
    {
        var any_exist = ['hide_ant_goal','hide_ant_task','hide_ant_equip','hide_ant_extrc','hide_ant_equjb','hide_ant_xrcjb'];
        return this.getAllIncludedAndAnyIncludedNoneExcluded(nodes
                    , typename
                    , include_all_subtypename
                    , any_exist
                    , assignment
                    , ignore_removed
                    , exclude_subtypename);
    };

    this.getAllHavingAllShowingAntecedents = function (nodes, typename
                , include_all_subtypename
                , assignment
                , ignore_removed
                , exclude_subtypename)
    {
        //console.log("LOOK starting getAllHavingAllShowingAntecedents for include_all_subtypename=" + JSON.stringify(include_all_subtypename));
        var paired_include = ['has_ant_goal','has_ant_task','has_ant_equip','has_ant_extrc','has_ant_equjb','has_ant_xrcjb'];
        var paired_exclude = ['hide_ant_goal','hide_ant_task','hide_ant_equip','hide_ant_extrc','hide_ant_equjb','hide_ant_xrcjb'];
        return this.getAllPairedIncludeNoneExcluded(nodes
                    , typename
                    , include_all_subtypename
                    , paired_include
                    , paired_exclude
                    , assignment
                    , ignore_removed
                    , exclude_subtypename);
    };

    /**
     * Return collection of nodes that match criteria
     */
    this.getAllPairedIncludeNoneExcluded = function (nodes, typename
                , include_all_subtypename
                , paired_include
                , paired_exclude
                , assignment
                , ignore_removed
                , exclude_subtypename)
    {
        if (typeof include_all_subtypename === 'undefined' || include_all_subtypename === null)
        {
            include_all_subtypename = null;
        } else {
            if(include_all_subtypename.constructor !== Array)
            {
                include_all_subtypename = [include_all_subtypename];
            }
        };
        if (typeof paired_include === 'undefined' || paired_include === null)
        {
            paired_include = null;
        } else {
            if(paired_include.constructor !== Array)
            {
                paired_include = [paired_include];
            }
        };
        if (typeof paired_exclude === 'undefined' || paired_exclude === null)
        {
            paired_exclude = null;
        } else {
            if(paired_exclude.constructor !== Array)
            {
                paired_exclude = [paired_exclude];
            }
        };
        if(paired_exclude.length !== paired_include.length)
        {
            throw "ERROR the paired_include does not match the paired_exclude! pariedinclude=" + JSON.stringify(paired_include) + " paired_exclude=" + JSON.stringify(paired_exclude);
        };
        if (typeof assignment === 'undefined')
        {
            assignment = null;
        };
        if (typeof ignore_removed === 'undefined')
        {
            ignore_removed = true;
        };
        if (typeof exclude_subtypename === 'undefined' || exclude_subtypename === null)
        {
            exclude_subtypename = null;
        } else {
            if(exclude_subtypename.constructor !== Array)
            {
                exclude_subtypename = [exclude_subtypename];
            }
        };
        var pushit;
        var filtered = [];
        var nodescount = nodes.length;
        for (var i = 0; i < nodescount; i ++)
        {
            var candidate = nodes[i];
            pushit = this.matchesPairedIncludeNoneExcluded(candidate, typename
                        , include_all_subtypename
                        , paired_include, paired_exclude
                        , assignment, ignore_removed
                        , exclude_subtypename);
            if(pushit === true)
            {
                filtered.push(candidate);
            }
        }
        return filtered;
    };

    /**
     * Return collection of nodes where ALL include text was found in the include_all_subtypename and NONE of the exclude text matched
     */
    this.getAllIncludedAndAnyIncludedNoneExcluded = function (nodes, typename, include_all_subtypename, include_at_least_one_subtypename, assignment, ignore_removed, exclude_subtypename)
    {
        if (typeof include_all_subtypename === 'undefined' || include_all_subtypename === null)
        {
            include_all_subtypename = null;
        } else {
            if(include_all_subtypename.constructor !== Array)
            {
                include_all_subtypename = [include_all_subtypename];
            }
        };
        if (typeof include_at_least_one_subtypename === 'undefined' || include_at_least_one_subtypename === null)
        {
            include_at_least_one_subtypename = null;
        } else {
            if(include_at_least_one_subtypename.constructor !== Array)
            {
                include_at_least_one_subtypename = [include_at_least_one_subtypename];
            }
        };
        if (typeof assignment === 'undefined')
        {
            assignment = null;
        };
        if (typeof ignore_removed === 'undefined')
        {
            ignore_removed = true;
        };
        if (typeof exclude_subtypename === 'undefined' || exclude_subtypename === null)
        {
            exclude_subtypename = null;
        } else {
            if(exclude_subtypename.constructor !== Array)
            {
                exclude_subtypename = [exclude_subtypename];
            }
        };
        var pushit;
        var filtered = [];
        var nodescount = nodes.length;
        for (var i = 0; i < nodescount; i ++)
        {
            var candidate = nodes[i];
            pushit = this.matchesIncludedAndAnyIncludedNoneExcluded(candidate
                                        , typename, include_all_subtypename
                                        , include_at_least_one_subtypename
                                        , assignment
                                        , ignore_removed
                                        , exclude_subtypename);
            if(pushit === true)
            {
                filtered.push(candidate);
            }
        }
        return filtered;
    };
    
    /**
     * Return concatenated results of multiple filters
     */
    this.getMatching = function (nodes, filter_ar)
    {
        var filtered = [];
        for (var i=0; i < filter_ar.length; i++)
        {
            var onefilter = filter_ar[i];
            var subset;
            var typename_ar;
            var onetypename;
            if(!onefilter.hasOwnProperty("fn"))
            {
                throw "ERROR did NOT find the fn property in " + JSON.stringify(onefilter);
            }
            var function_name = onefilter.fn;
            if(onefilter.typename.constructor !== Array)
            {
                typename_ar = [onefilter.typename];
            } else {
                typename_ar = onefilter.typename;
            }
            for(var tidx=0; tidx < typename_ar.length; tidx++)
            {
                onetypename = typename_ar[tidx];
                if(function_name === 'getAllIncludedNoneExcluded')
                {
                    subset = this.getAllIncludedNoneExcluded(nodes
                            , onetypename
                            , onefilter.include_subtypename
                            , onefilter.assignment
                            , onefilter.ignore_removed
                            , onefilter.exclude_subtypename);
                } else if(function_name === 'getAllWithoutAntecedents') {
                    subset = this.getAllWithoutAntecedents(nodes
                            , onetypename
                            , onefilter.include_subtypename
                            , onefilter.assignment
                            , onefilter.ignore_removed
                            , onefilter.exclude_subtypename);
                } else if(function_name === 'getAllHavingAnyHiddenAntecedents') {
                    subset = this.getAllHavingAnyHiddenAntecedents(nodes
                            , onetypename
                            , onefilter.include_subtypename
                            , onefilter.assignment
                            , onefilter.ignore_removed
                            , onefilter.exclude_subtypename);
                } else if(function_name === 'getAllHavingAllShowingAntecedents') {
                    subset = this.getAllHavingAllShowingAntecedents(nodes
                            , onetypename
                            , onefilter.include_subtypename
                            , onefilter.assignment
                            , onefilter.ignore_removed
                            , onefilter.exclude_subtypename);
                } else {
                    throw "Did NOT recognize function name '" + function_name + "' in onefilter=" + JSON.stringify(onefilter);
                }
                filtered = filtered.concat(subset);
            }
        }
        return filtered;
    };

    
    return this;
};

/**
 * Return a subset of the nodes where ALL INLUDE are found and NONE of the exclude are found
 * @deprecated replaced by filter function
 */
bigfathom_util.nodes.getSubset = function (nodes, typename, include_subtypename, assignment, ignore_removed, exclude_subtypename)
{
    console.log("ERROR USING DEPRECATED getSubset use filter instead!");
    if (typeof include_subtypename === 'undefined' || include_subtypename === null)
    {
        include_subtypename = null;
    } else {
        if(include_subtypename.constructor !== Array)
        {
            include_subtypename = [include_subtypename];
        }
    };
    if (typeof assignment === 'undefined')
    {
        assignment = null;
    };
    if (typeof ignore_removed === 'undefined')
    {
        ignore_removed = true;
    };
    if (typeof exclude_subtypename === 'undefined' || exclude_subtypename === null)
    {
        exclude_subtypename = null;
    } else {
        if(exclude_subtypename.constructor !== Array)
        {
            exclude_subtypename = [exclude_subtypename];
        }
    };
    
    var filter = new bigfathom_util.nodes.filter();
    var pushit;
    var filtered = [];
    var nodescount = nodes.length;
    for (var i = 0; i < nodescount; i ++)
    {
        var candidate = nodes[i];
        var can_display = !candidate.hasOwnProperty("show_node") || candidate.show_node;
        if(can_display)
        {
            if(candidate.type === typename 
                    && (assignment === null || assignment === candidate.assignment)
                    && (!ignore_removed || !candidate.into_trash && !candidate.into_parkinglot))
            {
                pushit = false;
                if(include_subtypename === null)
                {
                    //Include anything that is not excluded
                    pushit = filter.checkNotExcluded(candidate, exclude_subtypename);
                } else {
                    //Look for matches
                    pushit = filter.checkAllIncluded(candidate, include_subtypename);
                    if(pushit)
                    {
                        //Pass through exclusion filter
                        pushit = filter.checkNotExcluded(candidate, exclude_subtypename);
                    }
                }
                if(pushit === true)
                {
                    filtered.push(candidate);
                }
            }
        }
    }
console.log("\nREMOVE LOOK OLD USE NEW FILTER INSTEAD!!!!!! filtered len=" + filtered.length + " members for "
        + "\n\tinc:" + JSON.stringify(include_subtypename) 
        + "\n\texl:" + JSON.stringify(exclude_subtypename));
for(var i=0; i< filtered.length; i++)
{
console.log("REMOVE LOOK filtered member#" + i + " ID=" + filtered[i].nativeid);
}
    return filtered;
};

/**
 * Return a subset of the links only for nodes that are showing
 */
bigfathom_util.nodes.getLinkSubset4D3 = function (graphdata)
{
    var nodes = graphdata.nodes;
    var link_by_offset = graphdata.linksbundle.link_by_offset;
    var filtered = [];
    for(var linkidx = 0; linkidx < link_by_offset.length; linkidx++)
    {
        var onelink= link_by_offset[linkidx];
        var targetnode = nodes[onelink.trgno];
        var sourcenode = nodes[onelink.srcno];
        if(targetnode.show_node && sourcenode.show_node)
        {
            filtered.push(onelink);
        }
    }
    return filtered;
};

/**
 * When it is an array, returns values delimited by spaces
 */
bigfathom_util.nodes.getSubtypeAsText = function (node)
{
    var subtype_text;
    if(node.hasOwnProperty("subtype"))
    {
        if(node.subtype.constructor !== Array)
        {
            subtype_text = node.subtype;
        } else {
            subtype_text = "";
            for(var i = 0; i < node.subtype.length - 1; i++)
            {
                subtype_text += node.subtype[i] + " ";
            }
            subtype_text += node.subtype[node.subtype.length - 1];    //Add in the last one
        }
    } else {
        subtype_text = null;
    }
    return subtype_text;
};

/**
 * Add one subtype name
 */
bigfathom_util.nodes.addSubtype = function(onenode, addname)
{
    bigfathom_util.nodes.changeSubtype(onenode, addname, null);
};

/**
 * Remove one subtype name
 */
bigfathom_util.nodes.removeSubtype = function(onenode, removename)
{
    bigfathom_util.nodes.changeSubtype(onenode, null, removename);
};

/**
 * Remove all antecedant declarations
 */
bigfathom_util.nodes.clearAllAntecedentDeclarations = function(onenode)
{
    for(var i=0; i < bigfathom_util.nodes.all_typenames.length; i++)
    {
        var typename = bigfathom_util.nodes.all_typenames[i];
        bigfathom_util.nodes.removeSubtype(onenode, 'has_ant_' + typename);
        bigfathom_util.nodes.removeSubtype(onenode, 'hide_ant_' + typename);
    }
};

/**
 * Return all the antecedent types in a sorted array
 */
bigfathom_util.nodes.getAntecedentTypes = function(onenode)
{
    if(!onenode.hasOwnProperty("subtype"))
    {
        throw "ERROR missing subtype attribute on " + JSON.stringify(onenode);
    }
    var types = [];
    var names_sorted = {'goal':false, 'task': false, 'equip': false, 'extrc': false, 'equjb': false, 'xrcjb': false};
    for(var i=0; i<onenode.subtype.length; i++)
    {
        var subtype = onenode.subtype[i];
        if(subtype.length > 8 && subtype.substring(0,8) === 'has_ant_')
        {
            names_sorted[subtype.substring(8)] = true;
        }
    }
    Object.getOwnPropertyNames(names_sorted).forEach(function(val, idx, array) 
    {
        if(names_sorted[val])
        {
            types.push(val);    
        }
    });
    return types;
};

bigfathom_util.nodes.getNodeTypeLetter = function(typename)
{
    var map = {'goal':'G', 'task': 'T', 'equip': 'Q', 'extrc': 'X', 'equjb': 'q', 'xrcjb': 'x'};
    return map[typename];
};

/**
 * Return all the antecedent types in a sorted array
 */
bigfathom_util.nodes.getAntecedentTypesAsLetters = function(onenode)
{
    if(!onenode.hasOwnProperty("subtype"))
    {
        throw "ERROR missing subtype attribute on " + JSON.stringify(onenode);
    }
    var types = [];
    var names_sorted = {'goal':false, 'task': false, 'equip': false, 'extrc': false, 'equjb': false, 'xrcjb': false};
    for(var i=0; i<onenode.subtype.length; i++)
    {
        var subtype = onenode.subtype[i];
        if(subtype.length > 8 && subtype.substring(0,8) === 'has_ant_')
        {
            names_sorted[subtype.substring(8)] = true;
        }
    }
    Object.getOwnPropertyNames(names_sorted).forEach(function(val, idx, array) 
    {
        if(names_sorted[val])
        {
            types.push(bigfathom_util.nodes.getNodeTypeLetter(val));    
        }
    });
    return types;
};

/**
 * Return true if the node has antecedents
 */
bigfathom_util.nodes.hasAntecedents = function(onenode)
{
    if(!onenode.hasOwnProperty("subtype"))
    {
        return false;
    }
    if(onenode.subtype.indexOf("has_ant_goal") < 0 
            && onenode.subtype.indexOf("has_ant_task") < 0
            && onenode.subtype.indexOf("has_ant_equip") < 0
            && onenode.subtype.indexOf("has_ant_extrc") < 0
            && onenode.subtype.indexOf("has_ant_equjb") < 0
            && onenode.subtype.indexOf("has_ant_xrcjb") < 0
            )
    {
        return false;
    }
    return true;
};

/**
 * Mark all declared antecedents as hidden by adding hide flags
 */
bigfathom_util.nodes.hideAllAntecedents = function(onenode)
{
    for(var i=0; i<bigfathom_util.nodes.all_typenames.length; i++)
    {
        var tn = bigfathom_util.nodes.all_typenames[i];
        var findname = "has_ant_" + tn;
        if(onenode.subtype.indexOf(findname) > -1)
        {
            var newname = "hide_ant_" + tn;
            bigfathom_util.nodes.changeSubtype(onenode, newname, null);            
        }
    }
};

/**
 * Mark all declared antecedents as shown by removing hide flags
 */
bigfathom_util.nodes.showAllAntecedents = function(onenode, hide_completed)
{
    if(typeof hide_completed === 'undefined')
    {
        hide_completed = false;
    }
    if(!hide_completed || onenode.status_detail.terminal_yn !== 1)
    {
        for(var i=0; i<bigfathom_util.nodes.all_typenames.length; i++)
        {
            var tn = bigfathom_util.nodes.all_typenames[i];
            var findname = "hide_ant_" + tn;
            if(onenode.subtype.indexOf(findname) > -1)
            {
                bigfathom_util.nodes.changeSubtype(onenode, null, findname);            
            }
        }
    }
};

/**
 * Return true if the node has hidden antecedents
 */
bigfathom_util.nodes.hasHiddenAntecedents = function(onenode)
{
    if(!onenode.hasOwnProperty("subtype"))
    {
        return false;
    }
    if(onenode.subtype.indexOf("hide_ant_goal") > -1 
            || onenode.subtype.indexOf("hide_ant_task") > -1
            || onenode.subtype.indexOf("hide_ant_equip") > -1
            || onenode.subtype.indexOf("hide_ant_extrc") > -1
            || onenode.subtype.indexOf("hide_ant_equjb") > -1
            || onenode.subtype.indexOf("hide_ant_xrcjb") > -1
            )
    {
        return true;
    }
    return false;
};

/**
 * Add or remove a subtype name
 * @param {type} onenode The node to update
 * @param {type} newname The new name to add into the array, else null
 * @param {type} removename The name to remove from the array, else null
 */
bigfathom_util.nodes.changeSubtype = function(onenode, newname, removename)
{
    if(onenode.subtype.constructor !== Array)
    {
        onenode.subtype = newname;
    } else {
        if(removename !== null)
        {
            var replaceidx = onenode.subtype.indexOf(removename);
            if(replaceidx > -1)
            {
                onenode.subtype.splice(replaceidx,1);
            }
        }
        if(newname !== null)
        {
            if(onenode.subtype.indexOf(newname) === -1)
            {
                onenode.subtype.push(newname);
            }
        }
    }
};

bigfathom_util.nodes.getTooltipFromNode = function(node)
{
    return bigfathom_util.nodes.getTooltipObject(node.label, node.is_candidate, node.type
                                    , node.subtype
                                    , node.nativeid
                                    , node.hierarchy_level
                                    , node.status_cd, node.status_detail, node.maps);
};

bigfathom_util.nodes.getTooltipObject = function(labelobj, is_candidate, typename, subtype, nativeid, hierarchy_level, status_cd, status_detail, maps)
{
    var tooltip = {}; 
    if(typeof hierarchy_level === 'undefined')
    {
        hierarchy_level = null;    
    }
    var description = '';
    if(is_candidate)
    {
        description = 'proposed ';
    }
    description += typename;
    if (typeof subtype !== 'undefined')
    {
        var attributes = '';
        if(subtype.indexOf('project') > -1)
        {
            if(!labelobj.hasOwnProperty('owner_projectid'))
            {
                attributes = 'is project';
            } else {
                attributes = 'is project#' + labelobj.owner_projectid;
            }
        }
        if(subtype.indexOf('deliverable') > -1)
        {
            if(attributes !== '')
            {
                attributes += ' and ';
            }
            attributes += 'has deliverable';
        }
        if(attributes !== '')
        {
            description += ' (' + attributes + ')';
        }
    }
    tooltip['desc'] = description;
    if(nativeid !== null)
    {
        tooltip['id#'] = nativeid;
    }
    if(hierarchy_level !== null)
    {
        tooltip['level'] = hierarchy_level;
    }
    if(typeof status_cd === 'undefined')
    {
        console.log("LOOK ERROR status_cd nativeid=" + nativeid + " detail=" + JSON.stringify(tooltip));
        //alert("LOOK BUG missing status_cd for nativeid=" + nativeid);
    } else {
        if(status_cd !== null)
        {
            tooltip['status'] = status_cd;
        }
        if(typeof status_detail === 'undefined')
        {
            console.log("LOOK ERROR nativeid=" + nativeid + " detail=" + JSON.stringify(tooltip));
            //alert("LOOK BUG missing status_detail for nativeid=" + nativeid);
        } else {
            if(status_detail !== null)
            {
                tooltip['status'] = status_detail.code + " - " + status_detail.wordy_status_state;
            } else {
                if(status_cd !== null)
                {
                    tooltip['status'] = status_cd;
                }
            }
        }
    }
    if(maps !== null)
    {
        tooltip['todoFROMmaps'] = JSON.stringify(maps);
    }
    return tooltip;
};

/**
 * Create one node from parameters
 */
bigfathom_util.nodes.getNewNode = function (is_rootnode, is_candidate, nativeid, key, typename, subtype, labelobj
                , fill_opacity, xpos, ypos
                , is_drag_source
                , is_drag_target
                , assignment
                , hierarchy_level
                , status_cd
                , show_node
                , show_children
                , status_detail
                , maps)
{
    if (typeof maps === 'undefined' || maps === null)
    {
        maps = {};
    };
    if (typeof show_node === 'undefined' || show_node === null)
    {
        show_node = true;
    };
    if (typeof show_children === 'undefined' || show_children === null)
    {
        show_children = true;
    };
    if (typeof status_detail === 'undefined')
    {
        status_detail = null;
    };
    return {
              'type': typename
            , 'subtype': subtype
            , 'happyclowns': key + '@' + Date.now()
            , 'key': key
            , 'is_rootnode': is_rootnode
            , 'is_candidate': is_candidate
            , 'nativeid': nativeid
            , 'label': labelobj
            , 'fill_opacity': fill_opacity
            , 'x': xpos
            , 'y': ypos
            , 'px': xpos
            , 'py': ypos
            , 'ideal_y': ypos
            , 'ideal_x': xpos
            , 'ideal_tickmove_count': 0
            , 'center_dy': 0
            , 'is_background': false
            , 'is_drag_source': is_drag_source
            , 'is_drag_target': is_drag_target
            , 'assignment': assignment
            , 'status_cd': status_cd
            , 'status_detail': status_detail
            , 'hierarchy_level': hierarchy_level
            , 'targetlevel': null   //Redundnt with hierarchy_level?  Legacy UI?
            , 'into_trash': false
            , 'into_parkinglot': false
            , 'fixed': false
            , 'show_node': show_node
            , 'show_children': show_children
            , 'custom_detail': null
            , 'maps': maps
            , get tooltip () { return bigfathom_util.nodes.getTooltipFromNode(this); }
        };
};


