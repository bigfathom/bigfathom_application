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
if(!bigfathom_util.hasOwnProperty("url"))
{
    //Create the object property because it does not already exist
    var base_url;
    var n = window.location.href.search("/nda1/");
    try
    {
        if(n > 0)
        {
            //Special case
            base_url = window.location.origin + "/nda1/";
        } else {
            base_url = window.location.origin;
        }
    }
    catch(err)
    {
        console.error("FAILED url check because " + err);
        base_url = "FAILED_CHECKING_URL";
    }
    bigfathom_util.url = {"version": "20180308.1"
        , "base_url" : base_url + "/?q="
    };
}

/**
 * Give us the full URL
 */
bigfathom_util.url.getUrl = function(coreurl, url_params_ar)
{
    try
    {
        if(url_params_ar === null || typeof url_params_ar === 'undefined')
        {
            url_params_ar = {};
        }
        var finalurl;
        var urlarg_count = 1;   //Assume we are using ?q= format urls
        url_params_ar['cachesalt'] = Date.now();
        var urlargs = "";
        for(var paramname in url_params_ar)
        {
            if(urlarg_count > 0)
            {
                urlargs += "&";
            } else {
                urlargs += "?";
            }
            var paramvalue = url_params_ar[paramname];
            urlarg_count++;
            urlargs += (paramname + "=" + paramvalue);
        }
        finalurl = bigfathom_util.url.base_url + coreurl + urlargs;
        return finalurl;
    }
    catch(err)
    {
        console.error(err);
    }
};
