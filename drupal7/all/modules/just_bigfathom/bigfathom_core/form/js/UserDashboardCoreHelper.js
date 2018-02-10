/* 
 * Functions for working with user dashboard display and data
 * across ALL TABS.
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
if(!bigfathom_util.hasOwnProperty("userdashboard_core"))
{
    //Create the object property because it does not already exist
    bigfathom_util.userdashboard_core = {
        version: "20161127.1",
        rounding_factor: 10000,
        charts: {},
        latest_data: {}
    };
}

/**
 * Calls all of initialization routines of the member tabs
 */
bigfathom_util.userdashboard_core.init = function(personid)
{
    console.log("Initializing user dashboard core " + bigfathom_util.userdashboard_core.version + " for personid=" + personid);
    $( "body" ).append( "<div id='chartjs-tooltip'><table></table></div>" );
    if(!bigfathom_util.hasOwnProperty("userdashboard_tm"))
    {
        throw "ERROR missing the time management content handler!";
    }
    bigfathom_util.userdashboard_tm.init(personid);
    
    if(!bigfathom_util.hasOwnProperty("userdashboard_sw"))
    {
       throw "ERROR missing the sorted worklist content handler!";
    }
    bigfathom_util.userdashboard_sw.init(personid);
    
    if(!bigfathom_util.hasOwnProperty("userdashboard_utilization"))
    {
       throw "ERROR missing the userdashboard_utilization content handler!";
    }
    bigfathom_util.userdashboard_utilization.init(personid);
};

jQuery(document).ready(function(){
    (function ($) {
        $("#dash-container-tabs").tabs(
                {
                    collapsible: false
                });
        var url_img = Drupal.settings.myurls.images;
        var personid = Drupal.settings.personid;
        console.log("Starting userdashboard " + bigfathom_util.userdashboard_core.version);
        console.log("... url_img=" + url_img);
        console.log("... personid=" + personid);
        
        bigfathom_util.userdashboard_core.init(personid);
        
    }(jQuery));
});
    

