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
}
if(!bigfathom_util.hasOwnProperty("data"))
{
    //Create the object property because it does not already exist
    var base_url;
    var n = window.location.href.search("/nda1/");
    if(n > 0)
    {
        //Special case
        base_url = window.location.origin + "/nda1/";
    } else {
        base_url = window.location.origin;
    }

    bigfathom_util.data = {"version": "20180308.1"
        , "shortNewDataCheckInterval": 2000
        , "longNewDataCheckInterval": 15000
        , "defaultNewDataCheckInterval": 3000
        , "base_url" : base_url
        , "urlcore":{
                "full_grab_data_url" : "bigfathom/exportjson"
               ,"full_send_data_url" : "bigfathom/importjson"
        }
    };
    
}

bigfathom_util.data.getDateAsUTC = function(date)
{
    try
    {
        var result = new Date(date);
        result.setMinutes(result.getMinutes() - result.getTimezoneOffset());
        return result;
    }
    catch(err)
    {
        console.error("FAILED getDateAsUTC because " + err);
    }
};

bigfathom_util.data.getDaysBetweenDates = function(startDate, endDate)
{
    try
    {
        var treatAsUTC = function(date)
        {
            return bigfathom_util.data.getDateAsUTC(date);
        };

        var millisecondsPerDay = 24 * 60 * 60 * 1000;
        return Math.trunc((treatAsUTC(endDate) - treatAsUTC(startDate)) / millisecondsPerDay);
    }
    catch(err)
    {
        console.error("FAILED getDaysBetweenDates because " + err);
    }
};

bigfathom_util.data.getAutoRefreshTracker = function(trackername)
{
    try
    {
        if(typeof trackername === 'undefined')
        {
            trackername = "AutoRefreshTracker";
        }
        var tracker = {
            "name":trackername,
            "requesters":{}
        };

        tracker.getInfo = function()
        {
            return tracker;
        };

        tracker.isBlocked = function()
        {
            var isblocked = false;
            for(var requestername in tracker.requesters)
            {
                if(tracker.requesters[requestername])
                {
                    isblocked = true;
                    //console.log("debuginfo " + tracker.name + " shows blocked by " + requestername);
                }
            }
            return isblocked;
        };

        tracker.isBlockedByNamedRequester = function(requestername)
        {
            var isblocked = tracker.requesters.hasOwnProperty(requestername) && tracker.requesters[requestername];
            return isblocked;
        };

        tracker.markBlocked = function(requestername)
        {
            if(tracker.isBlockedByNamedRequester(requestername))
            {
                throw "There is already an existing block request from [" + requestername + "]!";
            }
            tracker.requesters[requestername] = true;
            return true;
        };

        tracker.markAllowed = function(requestername)
        {
            //console.log("debuginfo TOP markAllowed(" + requestername + ") info=" + JSON.stringify(tracker));
            if(!tracker.requesters.hasOwnProperty(requestername))
            {
                throw "There is no existing block request from [" + requestername + "]!";
            }
            var isblocking = tracker.requesters[requestername];
            if(!isblocking)
            {
                throw "There is no active block request from [" + requestername + "]!";
            }
            delete tracker.requesters[requestername];
            //console.log("debuginfo BOT markAllowed(" + requestername + ") info=" + JSON.stringify(tracker));
            return true;
        };

        return tracker;
    }
    catch(err)
    {
        console.error("FAILED getAutoRefreshTracker because " + err);
    }
};

/**
 * Give us the full URL for getting data
 */
bigfathom_util.data.getUrl = function(typename, dataname, url_params_ar)
{
    try
    {
        if(url_params_ar === null || typeof url_params_ar === 'undefined')
        {
            url_params_ar = {};
        }
        var coreurl;
        var urlarg_count = 0;
        if(dataname)
        {
            coreurl = bigfathom_util.data.urlcore[typename] + "&dataname=" + dataname;  
            urlarg_count++;
        } else {
            coreurl = bigfathom_util.data.urlcore[typename];
        }
        return bigfathom_util.url.getUrl(coreurl, url_params_ar);
    }
    catch(err)
    {
        console.error("FAILED getUrl because " + err);
    }
};

/**
 * Give us the full URL
 */
bigfathom_util.data.getGrabDataUrl = function(dataname, url_params_ar)
{
    try
    {
        //var encodedurlparams = url_params_ar===null ? "" : encodeURI(url_params_ar);
        var clearuri = bigfathom_util.data.getUrl('full_grab_data_url', dataname, url_params_ar);
        return encodeURI(clearuri);
    }
    catch(err)
    {
        console.error(err);
    }
};
  
/**
 * Give us the full URL
 */
bigfathom_util.data.getSendDataUrl = function(dataname, url_params_ar)
{
    try
    {
        //var encodedurlparams = url_params_ar===null ? "" : encodeURI(url_params_ar);
        var clearuri = bigfathom_util.data.getUrl('full_send_data_url', dataname, url_params_ar);
        return encodeURI(clearuri);
    }
    catch(err)
    {
        console.error(err);
    }
};

/**
 * @deprecated
 */
bigfathom_util.data.getNewDataFromServer = function (fullurl, sendbundle, callbackActionFunction, callbackid, previous_refresh_timestamp)
{
    try
    {
        //TODO -- adjust the query so it only pulls since timestamp
        console.log("TODO adjust so we only pull new since " + previous_refresh_timestamp + " for " + JSON.stringify(sendbundle));
        return bigfathom_util.data.getDataFromServer(fullurl, sendbundle, callbackActionFunction, callbackid);
    }
    catch(err)
    {
        console.error(err);
    }
};

bigfathom_util.data.getDataFromServer = function (fullurl, sendbundle, callbackActionFunction, callbackid)
{
    try
    {
        if (typeof sendbundle === 'undefined')
        {
            sendbundle = {};    
        }
        if(typeof callbackid === 'undefined')
        {
            callbackid = fullurl;  
        }
        console.log("LOOK starting getDataFromServer url=" + fullurl
                + " callbackid=" + callbackid
                + " sendbundle=" + JSON.stringify(sendbundle));

        //We call this function when the read is completed
        var callbackAction = function(responseNum, data, responseDetail, errorDetail)
        {
            if(typeof errorDetail === 'undefined')
            {
                errorDetail = null;
            }
            var responseBundle = {"data":data, "responseNum":responseNum, "responseDetail":responseDetail, "errorDetail":errorDetail};
            console.log("LOOK getDataFromServer DONE responseNum=" + responseNum 
                    + " responseDetail=" + JSON.stringify(responseDetail) 
                    + " errorDetail=" + JSON.stringify(errorDetail));
            if(typeof callbackActionFunction !== 'undefined')
            {
                callbackActionFunction(callbackid, responseBundle);
            }
        };

        var jqxhr = jQuery.ajax({ 
                "type": 'GET',
                "url": fullurl,
                "dataType": 'json',
                "data": sendbundle,
                "async": true
            })
            .done(function( data ) {
                callbackAction(0, data, {});
            })
            .fail(function(xhr, textStatus, errorThrown) {
                console.log("DONE getDataFromServer FAILED textStatus=" + textStatus);
                console.log("DONE getDataFromServer FAILED errorThrown=" + JSON.stringify(errorThrown));
                console.log("DONE getDataFromServer FAILED responseText=" + xhr.responseText);
                if(typeof callbackActionFunction !== 'undefined')
                {
                    callbackAction(1, null, textStatus, xhr.responseText);
                }
                throw "Failed with error='" + errorThrown + "' on read of " + JSON.stringify(sendbundle);
            });
    }
    catch(err)
    {
        console.error("FAILED getDataFromServer because " + err);
    }
};

/**
 * Send data to the server asynchronously
 * Use the callbackActionFunction to get notification when finished
 */
bigfathom_util.data.writeData2Server = function (fullurl, sendpackage, callbackActionFunction, callbackid)
{
    try
    {
        console.log("LOOK starting writeData2Server url=" + fullurl + " sendpackage=" + JSON.stringify(sendpackage));
        if(!sendpackage.hasOwnProperty('dataname'))
        {
            console.log("ERROR missing 'dataname' property in sendpackage:" + JSON.stringify(sendpackage));
            throw "The sendpackage is missing the dataname property!";
        }
        if(!sendpackage.hasOwnProperty('databundle'))
        {
            console.log("ERROR missing 'databundle' property in sendpackage:" + JSON.stringify(sendpackage));
            throw "The sendpackage is missing the dataname property!";
        }
        var json_databundle = JSON.stringify(sendpackage);
        //We call this function when the write is completed
        var callbackAction = function(responseNum, responseDetail, errorDetail)
        {
            if(typeof errorDetail === 'undefined')
            {
                errorDetail = null;
            }
            if(typeof callbackid === 'undefined')
            {
                callbackid = fullurl;  
            }
            var responseBundle = {"responseNum":responseNum, "responseDetail":responseDetail, "errorDetail":errorDetail};
            console.log("LOOK writeData2Server DONE responseNum=" + responseNum + " responseDetail=" + JSON.stringify(responseDetail) + " errorDetail=" + JSON.stringify(errorDetail));
            if(typeof callbackActionFunction !== 'undefined')
            {
                callbackActionFunction(callbackid, responseBundle);
            }
        };

        var successfulSend = function()
        {
            //alert("Good news! Successful send to " + fullurl);
        };
        var failedSend = function(jqXHR, textStatus, errorThrown)
        {
            console.log("DONE failedSend textStatus=" + textStatus);
            console.log("DONE failedSend errorThrown=" + JSON.stringify(errorThrown));
            console.log("DONE failedSend responseText=" + jqXHR.responseText);
            if(typeof callbackActionFunction !== 'undefined')
            {
                callbackAction(1, textStatus, jqXHR.responseText);
            }
            console.log("Failed datasend of callbackid=" + callbackid + " to " + fullurl + " with status=" + textStatus);
        };
        var completedSend = function(jqXHR, textStatus )
        {
            if(jqXHR !== null && jqXHR.hasOwnProperty('responseText'))
            {
                console.log("DONE completedSend responseText=" + jqXHR.responseText);
            }
            console.log("Completed callbackid=" + callbackid + " send to " + fullurl + " with status=" + textStatus);
        };

        //FIXED SO WE DO NOT HIT PHP VAR LIMIT http://dan.hersam.com/2015/02/17/php-truncating-post-data/
        jQuery.ajax({
            url: fullurl,
            data: json_databundle,
            success: successfulSend,
            error: failedSend,
            complete: completedSend,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            type: "POST"
        }).done(function(result){
            callbackAction(0, result);
        });

        console.log("LOOK now at end of writeData2Server!");
    }
    catch(err)
    {
        console.error("FAILED writeData2Server because " + err);
    }
};

/**
 * manage interactions
 */
bigfathom_util.data.manager = function ()
{
    var manager = {
            graphdata : null,
        };

    /**
     * Return the offset number matching the key, else null.
     */
    manager["_findNodeIndex"] = function (nodes, key) 
    {
        try
        {
            for (var idx = 0; idx < nodes.length; idx++) 
            {
                if (nodes[idx].key == key)
                {
                    return idx;
                }
            }
            return null;
        }
        catch(err)
        {
            console.error(err);
        }
    };

    manager["_addOnePersonToNodes"] = function (person_detail
        , allNodes
        , allProjectRolesLinks
        , allSystemRolesLinks
        , allGroupsLinks)
    {
        try
        {
            var oneNode;
            var person_mainkey;
            person_mainkey = "p" + person_detail.id;
            oneNode = {"key": person_mainkey
                    //, "size": 80
                    , "score": 0
                    , "committed": true
                    , "name": "" + person_detail.shortname
                    , "type": "person"
                    , "owner_personid": null
                    , "status_cd": null
                    , "importance": null
                    , "blurb": person_detail.first_nm + " " + person_detail.last_nm
                };
            allNodes.push(oneNode);

            //Now pick up any connected groups and roles for this person
            var map_person2role = person_detail.map_person2role;
            var map_person2role_in_group = person_detail.map_person2role_in_group;
            var map_person2systemrole_in_group = person_detail.map_person2systemrole_in_group;
            for (var rid in map_person2role) 
            {
                allProjectRolesLinks[person_mainkey + "," + rid] = true;
            }
            for (var rid in map_person2role_in_group) 
            {
                var detail2 = map_person2role_in_group[rid];
                allProjectRolesLinks[person_mainkey + "," + rid] = true;
                for (var gid in detail2) 
                {
                    allGroupsLinks[person_mainkey + "," + gid] = true;
                }
            }
            for (var srid in map_person2systemrole_in_group) 
            {
                var detail3 = map_person2systemrole_in_group[srid];
                allSystemRolesLinks[person_mainkey + "," + srid] = true;
                for (var gid in detail3) 
                {
                    allGroupsLinks[person_mainkey + "," + srid] = true;
                }
            }
        }
        catch(err)
        {
            console.error(err);
        }
    };

    /**
     * Create the person nodes and update links
     */
    manager["_addPeopleToNodes"] = function (only_include_linked
        , allPeopleDetail
        , allNodes
        , allKeyLinks
        , allPeopleLinks
        , allProjectRolesLinks
        , allSystemRolesLinks
        , allGroupsLinks) 
    {
        try
        {
            var person_node_added = {};
            var person_mainkey;
            var person_detail;

            if(!only_include_linked)
            {
                //Add them all now
                for (var pid in allPeopleDetail) 
                {
                    person_detail = allPeopleDetail[pid];
                    person_mainkey = "p" + person_detail.id;

                    if(!person_node_added[pid])
                    {
                       manager._addOnePersonToNodes(person_detail
                            , allNodes
                            , allProjectRolesLinks
                            , allSystemRolesLinks
                            , allGroupsLinks);
                        person_node_added[pid] = true;
                    }
                }        
            }
            for (var property in allPeopleLinks) 
            {
                var s = property.split(",");
                var pid = s[0];
                var mainkey = s[1];
                person_detail = allPeopleDetail[pid];
                person_mainkey = "p" + person_detail.id;

                if(!person_node_added[pid])
                {
                    manager._addOnePersonToNodes(person_detail
                        , allNodes
                        , allProjectRolesLinks
                        , allSystemRolesLinks
                        , allGroupsLinks);
                    person_node_added[pid] = true;
                }
                var oneLink = {"parent_key": person_mainkey, "child_key": mainkey};
                allKeyLinks.push(oneLink);
            }
            return {'nodes':allNodes, 'key_links':allKeyLinks
                    , 'people':allPeopleLinks
                    , 'projectroles':allProjectRolesLinks
                    , 'systemroles':allSystemRolesLinks
                    , 'groups':allGroupsLinks
                };
        }
        catch(err)
        {
            console.error(err);
        }
    };

    /**
     * Create the role nodes and update links
     */
    manager["_addRolesToNodes"] = function (roletype, allRolesDetail, allNodes, allKeyLinks, allRolesLinks) 
    {
        try
        {
            var keyprefix;
            var nodetype;
            if(roletype === 'project')
            {
                keyprefix = 'pr';
                nodetype = 'prole';
            } else {
                keyprefix = 'sr';
                nodetype = 'srole';
            }

            var role_node_added = {};
            var oneNode;
            var role_mainkey;

            for (var property in allRolesLinks) 
            {
                var s = property.split(",");
                var mainkey = s[0];
                var rid = s[1];

                var role_detail = allRolesDetail[rid];

                if(!role_node_added[rid])
                {
                    role_mainkey = keyprefix + role_detail.id;
                    oneNode = {"key": role_mainkey
                            //, "size": 10
                            , "score": 0
                            , "committed": true
                            , "name": "" + role_detail.role_nm
                            , "type": nodetype
                            , "owner_personid": null
                            , "status_cd": null
                            , "importance": null
                            , "blurb": role_detail.purpose_tx
                        };
                    allNodes.push(oneNode);
                    role_node_added[rid] = true;
                }
                var oneLink = {"parent_key": mainkey, "child_key": role_mainkey};
                allKeyLinks.push(oneLink);
            }
            return {'nodes':allNodes, 'key_links':allKeyLinks, 'roles':allRolesLinks};
        }
        catch(err)
        {
            console.error(err);
        }
    };

    /**
     * Create the group nodes and update links
     */
    manager["_addGroupsToNodes"] = function (allGroupsDetail, allNodes, allKeyLinks, allGroupsLinks) 
    {
        try
        {
            var keyprefix = 'grp';
            var nodetype = 'group';
            var group_node_added = {};
            var oneNode;
            var group_mainkey;

            for (var property in allGroupsLinks) 
            {
                var s = property.split(",");
                var mainkey = s[0];
                var gid = s[1];
        //console.log("LOOK gid=" + gid + " will look in " + JSON.stringify(allGroupsDetail));
                if (typeof allGroupsDetail[gid] === 'undefined') 
                {
                    throw "DATA ERROR _addGroupsToNodes did NOT find detail for gid=" + gid + " in " + JSON.stringify(allGroupsDetail);
                } else {
                    var group_detail = allGroupsDetail[gid];

                    if(!group_node_added[gid])
                    {
                        group_mainkey = keyprefix + group_detail.id;
                        oneNode = {"key": group_mainkey
                                //, "size": 15
                                , "score": 0
                                , "committed": true
                                , "name": "" + group_detail.group_nm
                                , "type": nodetype
                                , "owner_personid": null
                                , "status_cd": null
                                , "importance": null
                                , "blurb": group_detail.purpose_tx
                            };
                        allNodes.push(oneNode);
                        group_node_added[gid] = true;
                    }
                    //Point the person to the group the are a member of
                    var oneLink = {"parent_key": group_mainkey, "child_key": mainkey};
                    allKeyLinks.push(oneLink);
                } 
            }
            return {'nodes':allNodes, 'key_links':allKeyLinks, 'groups':allGroupsLinks};
        }
        catch(err)
        {
            console.error(err);
        }
    };

    /**
     * Walk through one branch of the tree
     */
    manager["_harvestTree"] = function (parent_node, main_node, allNodes
                    , allLinks, allPeopleLinks) 
    {
        try
        {
            var children = main_node.children;
            var fake_node;
            var mainkey;
            var oneNode;
            if(("subtype" in main_node))
            {
                fake_node = true;
            } else {
                fake_node = false;
                if(main_node.type == 'task')
                {
                    mainkey = "t" + main_node.id;
                    oneNode = {"key": mainkey
                            //, "size": 10
                            , "score": 0
                            , "committed": true
                            , "name": "" + main_node.name
                            , "type": main_node.type
                            , "owner_personid": "" + main_node.detail.owner_personid
                            , "status_cd": main_node.detail.status_cd
                            , "importance": main_node.detail.importance
                            , "blurb": null
                        };
                } else if(main_node.type == 'goal') {
                    mainkey = "g" + main_node.id;
                    oneNode = {"key": mainkey
                            //, "size": 60
                            , "score": 0
                            , "committed": true
                            , "name": "" + main_node.name
                            , "type": main_node.type
                            , "owner_personid": "" + main_node.detail.owner_personid
                            , "status_cd": main_node.detail.status_cd
                            , "importance": main_node.detail.importance
                            , "blurb": null
                        };
                } else {
                    console.log("ERROR did NOT recognize tree node = " + JSON.stringify(main_node));
                    mainkey = null;
                }
                if(main_node.detail.owner_personid)
                {
                    allPeopleLinks[main_node.detail.owner_personid + "," + mainkey] = true;
                }
                allNodes.push(oneNode);
                if(parent_node && parent_node !== null)
                {
                    var parentkey = ((parent_node.type == 'task') ? 't' : 'g') + parent_node.id;
                    var oneLink = {"parent_key": parentkey, "child_key": mainkey};
                    allLinks.push(oneLink);
                }
            };
            for (var idx = 0; idx < children.length; idx++)
            {
                var child = children[idx];
                if(child !== null)
                {
                    var subtree_result;
                    if(fake_node)
                    {
                        subtree_result = manager._harvestTree(null, child, allNodes, allLinks, allPeopleLinks);
                    } else {
                        subtree_result = manager._harvestTree(main_node, child, allNodes, allLinks, allPeopleLinks);
                    }
                    allNodes.concat(subtree_result.nodes);
                    allLinks.concat(subtree_result.key_links);
                }
            }
            return {'nodes':allNodes, 'key_links':allLinks, 'people':allPeopleLinks};
        }
        catch(err)
        {
            console.error(err);
        }
    };

    manager["createNewSprint"] = function (allNodes, projectid, title_tx, iteration_ct, purpose_tx)
    {
        try
        {
            var mainkey = "newt" + allNodes.length;
            var oneNode = {"key": mainkey
                    //, "size": 10
                    , "score": 0
                    , "committed": false
                    , "name": "" + title_tx + " " + iteration_ct
                    , "type": "sprint"
                    , "owner_personid": null
                    , "status_cd": null
                    , "importance": null
                    , "blurb": purpose_tx
                    , "root_projectid": projectid
                };
            allNodes.push(oneNode);
        }
        catch(err)
        {
            console.error("FAILED CREATE SPRINT because " + err);
        }
    };

    manager["createNewBrainstormItem"] = function (allNodes, item_type_code, projectid, title_tx, purpose_tx)
    {
        try
        {
            var typename;
            if(item_type_code == 'G')
            {
                typename = 'goal';
            } else
            if(item_type_code == 'T')
            {
                typename = 'task';
            } else {
                typename = 'brainstorm';
            }
            var mainkey = "newbrainstorm" + allNodes.length;
            var oneNode = {"key": mainkey
                    , "score": 0
                    , "committed": false
                    , "name": "" + title_tx
                    , "type": typename
                    , "owner_personid": null
                    , "status_cd": null
                    , "importance": null
                    , "blurb": purpose_tx
                    , "root_projectid": projectid
                };
            allNodes.push(oneNode);
        }
        catch(err)
        {
            console.error("FAILED CREATE BRAINSTORM ITEM because " + err);
        }
    };

    /**
     * Return the data formatted for graphing in D3
     */
    manager["convertDataToForceFormat"] = function (rawdata) 
    {
        try
        {
            var allPeopleLinks = {};        //Use this to build a truth table quickly
            var allProjectRolesLinks = {};  //Use this to build a truth table quickly
            var allSystemRolesLinks = {};   //Use this to build a truth table quickly
            var allGroupsLinks = {};        //Use this to build a truth table quickly
            var allSprintLinks = {};        //Use this to build a truth table quickly
            var allNodes = [];
            var allKeyLinks = [];
            var allOffsetLinks = [];

            var only_show_linked_people = false;

            //Get just the data we care about and harvest the parts we will use
            var allPeopleDetail = rawdata.metadata.people;
            var allProjectRolesDetail = rawdata.metadata.roles.project;
            var allSystemRolesDetail = rawdata.metadata.roles.system;
            var allGroupsDetail = rawdata.metadata.groups;
            var myrd = rawdata.data;
            var treeroot = {"type":"start", "subtype":"artificial", "name":"artificial_root", "children":myrd};
            var harvested = manager._harvestTree(null, treeroot, allNodes, allKeyLinks, allPeopleLinks);

            manager._addPeopleToNodes(only_show_linked_people, allPeopleDetail, allNodes, allKeyLinks, allPeopleLinks
                    , allProjectRolesLinks, allSystemRolesLinks, allGroupsLinks);
            manager._addRolesToNodes('project', allProjectRolesDetail, allNodes, allKeyLinks, allProjectRolesLinks);    
            manager._addRolesToNodes('system', allSystemRolesDetail, allNodes, allKeyLinks, allSystemRolesLinks);    
            manager._addGroupsToNodes(allGroupsDetail, allNodes, allKeyLinks, allGroupsLinks);

            //Now convert all the links from keys into offsets
            for (var idx = 0; idx < allKeyLinks.length; idx++)
            {
                var pair = allKeyLinks[idx];
                var parent_offset = manager._findNodeIndex(allNodes, pair.parent_key);
                var child_offset = manager._findNodeIndex(allNodes, pair.child_key);

                var onelink = {
                                "target": parent_offset,
                                "source": child_offset
                            };
                allOffsetLinks.push(onelink);
            }

            //Now return the data in one object
            var graph = {'nodes':allNodes, 'links':allOffsetLinks};

            manager.graphdata = graph;

            return graph;
        }
        catch(err)
        {
            console.error("FAILED CONVERT2FF because " + err);
        }
    };

    /**
     * Convert into a normalized json data package
     */
    manager["convertForceFormatToNormalData"] = function (allNodes, allLinks)
    {
        try
        {
            var json_data = {dataname:'goalandtask_relationships'
                            , data:null};
            var mydata = [];

            for (var node_idx = 0; node_idx < allNodes.length; node_idx++)
            {
                var graph_node = allNodes[node_idx];
                var data_node = {workitem_nm: graph_node.id, children: []};
                for (var link_idx = 0; link_idx < allLinks.length; link_idx++)
                {
                    var pair = allLinks[link_idx];
                    if(graph_node.id === pair.source.id)
                    {
                        data_node.children.push({workitem_nm: pair.target.id});
                    }
                }
                mydata.push(data_node);
            }
            json_data.data = mydata;

            return json_data;
        }
        catch(err)
        {
            console.error("FAILED CONVERT2ND because " + err);
        }
    };

    return manager;
};




