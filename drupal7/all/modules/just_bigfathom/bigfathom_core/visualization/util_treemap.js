/* 
 * Functions for showing tree data as nested blocks
 * 
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * 
 * NOTE: Requires D3v4
 */

  
if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if(!bigfathom_util.hasOwnProperty("treemap"))
{
    //Create the object property because it does not already exist
    var base_url = window.location.origin;
    bigfathom_util.treemap = {
        version: "20160701.1"
    };
}

/**
 * Just a POC to get started
 */
bigfathom_util.treemap.createEverything = function (canvas_container_id, my_action_map, my_field_map, projectid)
{
    try
    {
        console.log("TODO put some D3v4 code here http://bl.ocks.org/mbostock/6bbb0a7ff7686b124d80");
        console.log("TODO put canvas_container_id=" + canvas_container_id);
        console.log("TODO put projectid=" + projectid);
        var demo_data_url = "http://127.0.0.1/sites/all/modules/bigfathom_core/visualization/treemap_demodata1.csv";

        var width = 960,
            height = 1060;

        var format = d3.format(",d");

        var color = d3.scaleOrdinal()
            .range(d3.schemeCategory10
                .map(function(c) { c = d3.rgb(c); c.opacity = 0.6; return c; }));

        var stratify = d3.stratify()
            .parentId(function(d) { return d.id.substring(0, d.id.lastIndexOf(".")); });

        var treemap = d3.treemap()
            .size([width, height])
            .padding(1)
            .round(true);

        console.log("LOOK about to call with demo_data_url=" + demo_data_url);
        d3.csv(demo_data_url, type, function(error, data) {
          if (error) throw error;

        console.log("LOOK got data=" + data);

          var root = stratify(data)
              .sum(function(d) { return d.value; })
              .sort(function(a, b) { return b.height - a.height || b.value - a.value; });

          treemap(root);

          d3.select("#" + canvas_container_id)
            .selectAll(".node")
            .data(root.leaves())
            .enter().append("div")
              .attr("class", "treemap-node")
              .attr("title", function(d) { return d.id + "\n" + format(d.value); })
              .style("left", function(d) { return d.x0 + "px"; })
              .style("top", function(d) { return d.y0 + "px"; })
              .style("width", function(d) { return d.x1 - d.x0 + "px"; })
              .style("height", function(d) { return d.y1 - d.y0 + "px"; })
              .style("background", function(d) { while (d.depth > 1) d = d.parent; return color(d.id); })
            .append("div")
              .attr("class", "treemap-node-label")
              .text(function(d) { return d.id.substring(d.id.lastIndexOf(".") + 1).split(/(?=[A-Z][^A-Z])/g).join("\n"); })
            .append("div")
              .attr("class", "treemap-node-value")
              .text(function(d) { return format(d.value); });
        });

        function type(d) {
          d.value = +d.value;
          return d;
        }
    }
    catch(err)
    {
        console.error("FAILED treemap because " + err);
    }
};




