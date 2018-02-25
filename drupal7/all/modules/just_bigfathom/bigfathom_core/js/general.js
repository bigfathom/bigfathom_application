/**
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 */

function isURIInWhitelistBundle(rawuri, whitelistbundle)
{
    
    console.log("LOOK rawuri=" + JSON.stringify(rawuri));
    console.log("LOOK whitelistbundle=" + JSON.stringify(whitelistbundle));
    
    var uriparts = getURIParts(rawuri);
    var scheme_whitelist = whitelistbundle['scheme_whitelist'];
    if(!scheme_whitelist.hasOwnProperty(uriparts.scheme))
    {
        console.log("URICHECK: uriparts missing shema member uriparts=[" + JSON.stringify(uriparts) + "]");
        return false;
    }
    var domain_whitelist = whitelistbundle['domain_whitelist'];
    if(!domain_whitelist.hasOwnProperty(uriparts.domain))
    {
        console.log("URICHECK: domain NOT IN WHITELIST For domain=[" + uriparts.domain + "]");
        return false;
    }
    //Looks okay.
    return true;
}

function getURIParts(rawuri)
{
    var uri = rawuri.trim().toLowerCase();
    var host = null;
    var fulldomain = null;
    var portnum = null;
    var topleveldomain = null;
    var subdomain = null;
    var coredomain = null;
    var hostend = uri.indexOf(":");
    if(hostend > 0)
    {
        host = uri.substr(0, hostend).trim().toLowerCase();
    }
    var fulldomainstart = uri.indexOf("//");
    var fulldomainend = -1;
    if(fulldomainstart > hostend)
    {
        var realdomainstart = fulldomainstart+2;
        var fulldomainend_portdelim = uri.indexOf(":", realdomainstart);
        var fulldomainend1 = uri.indexOf("/", realdomainstart);
        var fulldomainend2 = uri.indexOf("?", realdomainstart);
        var fulldomainend3 = uri.length;
        
        if(fulldomainend_portdelim > -1)
        {
            fulldomainend = fulldomainend_portdelim;
            if(fulldomainend2 > -1)
            {
                portnum=uri.substr(fulldomainend, fulldomainend2-fulldomainend);
            } else if(fulldomainend1 > -1) {
                portnum=uri.substr(fulldomainend, fulldomainend1-fulldomainend);
            }
                
        } else {
            if(fulldomainend1 < fulldomainend2 && fulldomainend1 > -1 || fulldomainend1 > -1 && fulldomainend2 === -1)
            {
                fulldomainend = fulldomainend1;
            } else
            if(fulldomainend2 < fulldomainend1 && fulldomainend2 > -1 || fulldomainend1 > -1 && fulldomainend1 === -1)
            {
                fulldomainend = fulldomainend2;
            } else {
                fulldomainend = uri.length;
            }
        }
        
        if(fulldomainend > -1)
        {
            fulldomain = uri.substr(realdomainstart, fulldomainend-realdomainstart);
            if(fulldomain === 'localhost' || fulldomain === '127.0.0.1')
            {
                coredomain = fulldomain;
                topleveldomain = fulldomain;
            } else {
                var lastperiod = fulldomain.lastIndexOf(".");
                if(lastperiod > -1)
                {
                    topleveldomain = fulldomain.substr(lastperiod+1);
                    var subdomainend = fulldomain.substr(0,lastperiod-1).lastIndexOf(".")
                    if(subdomainend > -1)
                    {
                        subdomain = fulldomain.substr(0,subdomainend);
                        coredomain = fulldomain.substr(subdomainend+1);
                    } else {
                        coredomain = fulldomain;
                    }
                }
            }
        }
    }
    
    return {'scheme':host, 'fulldomain': fulldomain
        , 'topleveldomain': topleveldomain
        , 'subdomain': subdomain
        , 'domain': coredomain
        , 'portnum':portnum};
    
}

function getValueFromTextboxByName(textboxname)
{
    var elems = document.getElementsByName(textboxname);
    if(elems.length === 0)
    {
        throw "Cannot get becuase did NOT find an element named " + textboxname;
    }
    return elems[0].value;
}

function setValueForTextboxByName(textboxname, value)
{
    var elems = document.getElementsByName(textboxname);
    if(elems.length === 0)
    {
        throw "Cannot set because did NOT find an element named " + textboxname;
    }
    if(typeof value === 'undefined')
    {
        value = ''; //We want to clear the text box.
    }
    elems[0].value = value;
}

/**
 * Returns array of any names not found in the selection box.
 */
function setValueForSelectionboxByName(selectionboxname, values)
{
    function clearall(selectObj)
    {
        for(var i = 0; i < selectObj.length; i++)
        {
            selectObj.options[i].selected = false;
        }
    }
    function set_matching_word(selectObj, label)
    {
        var cleanlabel = label.trim().toLowerCase()
        for(var i = 0; i < selectObj.length; i++)
        {
            var cleanitemlabel = selectObj.options[i].text.trim().toLowerCase();
            if(cleanitemlabel == cleanlabel)
            {
               selectObj.options[i].selected = true;
               return true;
            }
        }
        return false;
    }
    
    var elems = document.getElementsByName(selectionboxname);
    if(elems.length === 0)
    {
        throw "Cannot set because did NOT find an element named " + selectionboxname;
    }
    var notfound = [];
    clearall(elems[0]);
    for (var key in values) 
    {
        if (values.hasOwnProperty(key)) 
        {
            var item = values[key];
            var found = set_matching_word(elems[0], item.name);
            if(!found)
            {
                notfound.push(item.name);
            }
        }
    }
    return notfound;
}

/**
 * @returns {Boolean} TRUE if browser supports local filereader feature
 */
function checkSupportsFileReader()
{
    return (window.File && window.FileReader && window.FileList && window.Blob);
}

var getAsKeyValuePairs = function(labels, fields)
{
    var kvp = {};
    if(labels.length !== fields.length)
    {
        console.log("ERROR INFO getAsKeyValuePairs labels=" + JSON.stringify(labels));
        console.log("ERROR INFO getAsKeyValuePairs fields=" + JSON.stringify(fields));
        alert("ERROR: Labels and Values not matching!");
        throw "The number of labels is different than numer of values!";
    }
    for(var i=0; i<labels.length; i++)
    {
        var label = labels[i].trim();
        var value = fields[i].trim();
        console.log("LOOK at i=" + i + " label=[" + label + "] value=[" + value + "]");
        kvp[label] = value;
    }
    console.log("LOOK getAsKeyValuePairs kvp=" + JSON.stringify(kvp));
    return kvp;
};

function convertProjectTemplateTabText2JSON(raw_tabtext)
{
    //var tabtext = raw_tabtext.replace(/(?:\\[rn])+/g,"\n");
    //var tabtext = raw_tabtext.replace(/(?:\r+)+/g,"\n");
    var tabtext = raw_tabtext.replace(/\r/g,"\n");
    var getRawSections = function()
    {
        var field_labels = [];
        var current_section_name = null;
        var sections = {};
        var grouping_name = null;
        var block_name = null;
        var field_name = null;
        var wid_col_offset = null;
        var insection_datarownum = -1;
        
        var hit_last_row_indicator=false;
        var allrows = tabtext.split("\n");
        for(var i=0;i<allrows.length;i++)
        {
            var onerow = allrows[i];
            var fields = onerow.split("\t");
            if(fields.length > 0 && fields[0].trim() > '')
            {
                var flag = fields[0].trim();
                if(flag.startsWith('DATA '))
                {
                    insection_datarownum++;
                    if(current_section_name === null)
                    {
                        current_section_name = "MISSING_SECTION_NAME";
                    }
                    var just_fieldvalues = fields.slice(1);
                    if(grouping_name !== null)
                    {
                        sections[grouping_name][current_section_name].rows.push(just_fieldvalues);
                    } else {
                        sections[current_section_name].rows.push(just_fieldvalues);
                    }
                    if(wid_col_offset !== null)
                    {
                        var wid = just_fieldvalues[wid_col_offset];
                        sections[current_section_name].fastmap_wid2rowoffset[wid] = insection_datarownum;
                    }
                } else {
                    insection_datarownum = -1;
                    current_section_name = flag;
                    
                    if(flag.startsWith('END'))
                    {
                        sections[block_name][field_name] = (sections[block_name][field_name]).trim();
                        block_name = null;
                        field_name = null;
                    }
                    
                    if(block_name !== null)
                    {
                        sections[block_name][field_name] += onerow;
                    } else {
                        if(flag.startsWith('BEGIN'))
                        {
                            if(fields.length < 3)
                            {
                                throw new Exception("Bad BEGIN section missing data! " + JSON.stringify(fields));
                            }
                            block_name = fields[1];
                            field_name = fields[2];
                            if(!sections.hasOwnProperty(block_name))
                            {
                                sections[block_name] = {};
                            }
                            if(!sections[block_name].hasOwnProperty(field_name))
                            {
                                sections[block_name][field_name] = {};
                            }
                            sections[block_name][field_name] = '';
                        } else
                        if(current_section_name === 'PROJECT_TEMPLATE_END')
                        {
                            hit_last_row_indicator=true;
                            break;
                        } else if(flag.startsWith('MAP_'))
                        {
                            grouping_name = 'master_maps';
                        } 
                        else if(flag.indexOf('_SUBMAP_') > 0)
                        {
                            grouping_name = 'workitem_submaps';
                        } else {
                            grouping_name = null;
                        }
                    }
                    
                    if(grouping_name !== null)
                    {
                        if(!sections.hasOwnProperty(grouping_name))
                        {
                            sections[grouping_name] = {};
                        }
                        sections[grouping_name][current_section_name] = {};
                    } else {
                        sections[current_section_name] = {};
                    }
                    if(fields.length > 1)
                    {
                        field_labels = fields.slice(1);
                        if(grouping_name !== null)
                        {
                            sections[grouping_name][current_section_name]['labels'] = field_labels;
                        } else {
                            sections[current_section_name]['labels'] = field_labels;
                        }
                    }
                    wid_col_offset = null;
                    if(grouping_name !== null)
                    {
                        sections[grouping_name][current_section_name]['rows'] = [];
                    } else {
                        sections[current_section_name]['rows'] = [];
                        if(current_section_name === 'WORKITEMS')
                        {
                            for(var j=0;j<field_labels.length;j++)
                            {
                                if(field_labels[j] === 'WID')
                                {
                                    wid_col_offset = j;
                                    break;
                                }
                            }
                            sections[current_section_name]['fastmap_wid2rowoffset'] = {};
                        }
                    }
                }
            }
        }
        
        if(!hit_last_row_indicator)
        {
            alert("Warning: Possibly corrupted file!  Did NOT hit the end of file marker row!");
        }
        return sections;
    };
    
    var json = {};
    var master_maps = {};

    var raw_sections = getRawSections();

    console.log("LOOK raw sections = " + JSON.stringify(raw_sections));
    
    json['metadata'] = getAsKeyValuePairs(raw_sections.PROJECT_TEMPLATE.labels, raw_sections.PROJECT_TEMPLATE.rows[0]);
    if(raw_sections.hasOwnProperty('METADATA'))
    {
        for(var fn in raw_sections.METADATA)
        {
            var fv = raw_sections.METADATA[fn];
            json['metadata'][fn] = fv;
        }
    }            
    
    json['workitems'] = raw_sections.WORKITEMS;
    json['workitem_submaps'] = raw_sections.workitem_submaps;
    json['master_maps'] = raw_sections.master_maps;
    
    return json;
}

function readLocalBlobWithFileReader(files, parseLoadedBlob) 
{

  /*
  var files = document.getElementById('files').files;
  */
  if (!files.length) {
    alert('Please select a file!');
    return;
  }

  var file = files[0];
  var start = 0;
  var stop = file.size - 1;

  var reader = new FileReader();

  // If we use onloadend, we need to check the readyState.
  reader.onloadend = function(evt) 
  {
    // DONE == 2
    if (evt.target.readyState == FileReader.DONE) 
    { 
      //document.getElementById(elemid_for_content).textContent = evt.target.result;
      parseLoadedBlob(evt.target.result);
    }
  };

  var blob = file.slice(start, stop + 1);
  reader.readAsBinaryString(blob);
}

function getProjectStatusFromServer(fullurl, callbackActionFunction, callbackid)
{
    
    console.log("LOOK fullurl=" + fullurl);
    alert( "You are running jQuery version: " + jQuery.fn.jquery );
    
    if(typeof callbackid === 'undefined')
    {
        callbackid = fullurl;
    }
    
    //We call this function when the read is completed
    var callbackAction = function(responseNum, data, responseDetail)
    {
        
        console.log("LOOK getDataFromServer DONE responseNum=" + responseNum 
                + " responseDetail=" + JSON.stringify(responseDetail) );
        
        if(typeof callbackActionFunction !== 'undefined')
        {
            if(responseNum === 1)
            {
                //Indicates an error, shall we elaborate in the message?
                if(responseDetail.message == "error" && responseDetail.hasOwnProperty("jqXHR"))
                {
                    responseDetail.message = "remote server responded with status#" + responseDetail.jqXHR.status;  
                }
            }
            var responseBundle = {"data":data, "responseNum":responseNum, "responseDetail":responseDetail};
            callbackActionFunction(callbackid, responseBundle);
        }
        
    };
    
    var jqxhr = jQuery.ajax({ 
            url: fullurl,
            data: {},
            dataType: "jsonp",
            jsonpCallback: "jsonpCallback"  //Declare the name of the function padding our data!
        })
        .fail(function(jqXHR, generalStatusText, xtra) {
            console.log("FAILED getProjectStatusFromServer for fullurl=" + fullurl);
            console.log("FAILED getProjectStatusFromServer generalStatusText=" + generalStatusText);
            if(typeof jqXHR !== "undefined")
            {
                console.log("FAILED getProjectStatusFromServer status#" + jqXHR.status);
                console.log("FAILED getProjectStatusFromServer statusText=" + jqXHR.statusText);
                console.log("FAILED getProjectStatusFromServer jqXHR=" + JSON.stringify(jqXHR));
            }
            if(typeof xtra !== "undefined")
            {
                console.log("FAILED getProjectStatusFromServer xtra=" + JSON.stringify(xtra));
            }
            if(typeof callbackActionFunction !== 'undefined')
            {
                if(generalStatusText == 'parsererror')
                {
                    //This happens when the URL returns content but it is not wrapped in our callback!
                    var responseDetail = {message: "did not get a valid response from the resource", "jqXHR": jqXHR};
                    callbackAction(1, null, responseDetail);
                } else {
                    //This is some kind of server/uri related error
                    var responseDetail = {message: generalStatusText, "jqXHR": jqXHR};
                    callbackAction(1, null, responseDetail);
                }
            }
        })
        .done(function( data ) {
            console.log("DONE We are in the done method with data=" + JSON.stringify(data));
            if(typeof data.metadata.format == 'undefined')
            {
                if(typeof data.metadata == 'undefined')
                {
                    callbackAction(1, null, {message:"data is missing metadata section!"});
                } else {
                    callbackAction(1, null, {message:"data is missing format declaration!"});
                }
            } else {
                if(data.metadata.format !== "bfps1")
                {
                    callbackAction(1, null, {message:"data is in unsupported '" + data.metadata.format +"' format"});
                } else {
                    callbackAction(0, data, {message:"OK"});
                }
            }
        });
        /*
        .always(function () {
            console.log("LOOK in always now!!!!!!!!!");
        });
        */
};

function getTemplateFromServer(fullurl, callbackActionFunction, callbackid)
{
    
    //alert( "You are running jQuery version: " + jQuery.fn.jquery );
    
    if(typeof callbackid === 'undefined')
    {
        callbackid = fullurl;
    }
    
    //We call this function when the read is completed
    var callbackAction = function(responseNum, data, responseDetail)
    {
        
        console.log("LOOK getDataFromServer DONE responseNum=" + responseNum 
                + " responseDetail=" + JSON.stringify(responseDetail) );
        
        if(typeof callbackActionFunction !== 'undefined')
        {
            if(responseNum === 1)
            {
                //Indicates an error, shall we elaborate in the message?
                if(responseDetail.message == "error" && responseDetail.hasOwnProperty("jqXHR"))
                {
                    responseDetail.message = "remote server responded with status#" + responseDetail.jqXHR.status;  
                }
            }
            var responseBundle = {"data":data, "responseNum":responseNum, "responseDetail":responseDetail};
            callbackActionFunction(callbackid, responseBundle);
        }
        
    };
    
    var jqxhr = jQuery.ajax({ 
            url: fullurl,
            data: {},
            dataType: "jsonp",
            jsonpCallback: "jsonpCallback"  //Declare the name of the function padding our data!
        })
        .fail(function(jqXHR, generalStatusText, xtra) {
            console.log("FAILED getTemplateFromServer for fullurl=" + fullurl);
            console.log("FAILED getTemplateFromServer generalStatusText=" + generalStatusText);
            if(typeof jqXHR !== "undefined")
            {
                console.log("FAILED getTemplateFromServer status#" + jqXHR.status);
                console.log("FAILED getTemplateFromServer statusText=" + jqXHR.statusText);
                console.log("FAILED getTemplateFromServer jqXHR=" + JSON.stringify(jqXHR));
            }
            if(typeof xtra !== "undefined")
            {
                console.log("FAILED getTemplateFromServer xtra=" + JSON.stringify(xtra));
            }
            if(typeof callbackActionFunction !== 'undefined')
            {
                if(generalStatusText == 'parsererror')
                {
                    //This happens when the URL returns content but it is not wrapped in our callback!
                    var responseDetail = {message: "did not get a valid response from the resource", "jqXHR": jqXHR};
                    callbackAction(1, null, responseDetail);
                } else {
                    //This is some kind of server/uri related error
                    var responseDetail = {message: generalStatusText, "jqXHR": jqXHR};
                    callbackAction(1, null, responseDetail);
                }
            }
        })
        .done(function( data ) {
            console.log("DONE We are in the done method with data=" + JSON.stringify(data));
            if(typeof data.metadata.format == 'undefined')
            {
                if(typeof data.metadata == 'undefined')
                {
                    callbackAction(1, null, {message:"data is missing metadata section!"});
                } else {
                    callbackAction(1, null, {message:"data is missing format declaration!"});
                }
            } else {
                if(data.metadata.format !== "bftf1")
                {
                    callbackAction(1, null, {message:"data is in unsupported '" + data.metadata.format +"' format"});
                } else {
                    callbackAction(0, data, {message:"OK"});
                }
            }
        });
        /*
        .always(function () {
            console.log("LOOK in always now!!!!!!!!!");
        });
        */
};
