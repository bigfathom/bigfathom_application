/*
 * Some helpers for our svg visualizations
 *  
 * Copyright Room4me.com Software LLC 2015
 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
    console.log("Created bigfathom_util");
}
if(!bigfathom_util.hasOwnProperty("popups"))
{
    //Create the object property because it does not already exist
    bigfathom_util.popups = {version: "20151112.1"};
}

/**
 * Create largest possible canvas inside element specified and return details
 */
bigfathom_util.popups.getPopups = function (id)
{
    var el   = document.getElementById(id); // or other selector like querySelector()
    var rect = el.getBoundingClientRect(); // get the bounding rectangle
    console.log("width=" +  rect.width );
    console.log("height=" + rect.height);

    var holder = d3.select("#visualization1") // select the 'body' element
          .append("svg")           // append an SVG element to the body
          .attr("id", "svg1")
          .attr("width","100%")
          .attr("height","100%");

    var canvas_width = rect.width;
    var canvas_height = rect.height > 500 ? rect.height : rect.width / 2;
    
    return {'w':canvas_width,'h':canvas_height,'container':el,'svg':holder};
}

