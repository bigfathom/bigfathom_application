/* 
 * Functions for working with user dashboard display and data
 * of the TIME MANAGEMENT tab.
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
if(!bigfathom_util.hasOwnProperty("userdashboard_tm"))
{
    //Create the object property because it does not already exist
    bigfathom_util.userdashboard_tm = {
        version: "20170921.1",
        rounding_factor: 10000,
        charts: {},
        latest_data: {}
    };
}

bigfathom_util.userdashboard_tm.init = function(personid)
{
    console.log("Initializing user dashboard TM " + bigfathom_util.userdashboard_tm .version + " for personid=" + personid);
    bigfathom_util.userdashboard_tm.refreshdisplay(personid);
};

bigfathom_util.userdashboard_tm.showInfluenceTotals = function(thevalues) 
{
    var typename = 'influence';
    var thelabels = ["Direct"
                    , "Indirect"
                    , "No Influence"
                    , "No Interest"
                    , "None!"
                    , "Unknown level of influence!"];
    bigfathom_util.userdashboard_tm.showGraphics(6, typename, thevalues, thelabels);
};

bigfathom_util.userdashboard_tm.showImportanceTotals = function(thevalues) 
{
    var typename = 'importance';
    var thelabels = ["High"
                    , "Moderate"
                    , "Low"
                    , "Minimal"
                    , "Zero"
                    , "Unknown importance to you!"];
    bigfathom_util.userdashboard_tm.showGraphics(6, typename, thevalues, thelabels);
};

bigfathom_util.userdashboard_tm.showSprintTMMTotals = function(thevalues) 
{
    console.log("LOOK showSprintTMMTotals = function(thevalues) = " + JSON.stringify(thevalues));
    var typename = 'sprinttmm';
    var thelabels = ["Q4"
                    , "Q3"
                    , "Q2"
                    , "Q1"];
    bigfathom_util.userdashboard_tm.showGraphics(4, typename, thevalues, thelabels);
};

bigfathom_util.userdashboard_tm.showOARTMMTotals = function(thevalues) 
{
    console.log("LOOK showOARTMMTotals = function(thevalues) = " + JSON.stringify(thevalues));
    var typename = 'oartmm';
    var thelabels = ["Q4"
                    , "Q3"
                    , "Q2"
                    , "Q1"];
    bigfathom_util.userdashboard_tm.showGraphics(4, typename, thevalues, thelabels);
};

bigfathom_util.userdashboard_tm.showTMMTotals = function(thevalues) 
{
    console.log("LOOK showTMMTotals = function(thevalues) = " + JSON.stringify(thevalues));
    var typename = 'tmm';
    var thelabels = ["Q4"
                    , "Q3"
                    , "Q2"
                    , "Q1"];
    bigfathom_util.userdashboard_tm.showGraphics(4, typename, thevalues, thelabels);
};


bigfathom_util.userdashboard_tm.showGraphics = function(category, typename, thevalues, thelabels) 
{
    //http://stackoverflow.com/questions/28476159/chart-js-pie-tooltip-getting-cut ???????
    //http://www.chartjs.org/docs/#advanced-usage-prototype-methods
    var chart_id = "dn-" + typename + "-pie";
    
    var plotEmptyPie = function(elem_ctx)
    {
        bigfathom_util.userdashboard_tm.charts[chart_id] = new Chart(elem_ctx, {
            type: 'pie',
            data: {
                labels: ['Empty!'],
                datasets: [{
                    data: [1],
                    backgroundColor: [
                        "rgba(55,55,55, 0.2)"
                    ],
                    hoverBackgroundColor: [
                        "rgba(5,5,5, 0.5)"
                    ],
                    borderColor: [
                        "rgba(5,5,5, 0.8)"
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                legend: {
                    display: false
                },
                tooltips: {
                        enabled: false
                }
            }
        });    
    };
    
    var plotRegularPie = function(elem_ctx, typename, piedata)
    {
        bigfathom_util.userdashboard_tm.charts[chart_id] = new Chart(elem_ctx, {
            type: typename,
            data: piedata,
            options: {
                responsive: true,
                legend: {
                        display: false
                },
                tooltips: {
                        enabled: false
                }
            }
        });    
    };

    Chart.defaults.global.tooltips.custom = function(tooltip) {
            // Tooltip Element
            var tooltipEl = document.getElementById('chartjs-tooltip');

            // Hide if no tooltip
            if (tooltip.opacity === 0) {
                    tooltipEl.style.opacity = 0;
                    return;
            }

            // Set caret Position
            tooltipEl.classList.remove('above', 'below', 'no-transform');
            if (tooltip.yAlign) {
                    tooltipEl.classList.add(tooltip.yAlign);
            } else {
                    tooltipEl.classList.add('no-transform');
            }

            function getBody(bodyItem) {
                    return bodyItem.lines;
            }

            // Set Text
            if (tooltip.body) {
                    var titleLines = tooltip.title || [];
                    var bodyLines = tooltip.body.map(getBody);

                    var innerHtml = '<thead>';

                    titleLines.forEach(function(title) {
                            innerHtml += '<tr><th>' + title + '</th></tr>';
                    });
                    innerHtml += '</thead><tbody>';

                    bodyLines.forEach(function(body, i) {
                            var colors = tooltip.labelColors[i];
                            var style = 'background:' + colors.backgroundColor;
                            style += '; border-color:' + colors.borderColor;
                            style += '; border-width: 2px'; 
                            var span = '<span class="chartjs-tooltip-key" style="' + style + '"></span>';
                            innerHtml += '<tr><td>' + span + body + '</td></tr>';
                    });
                    innerHtml += '</tbody>';

                    var tableRoot = tooltipEl.querySelector('table');
                    tableRoot.innerHTML = innerHtml;
            }

            var position = this._chart.canvas.getBoundingClientRect();

            // Display, position, and set styles for font
            tooltipEl.style.opacity = 1;
            tooltipEl.style.left = position.left + tooltip.caretX + 'px';
            tooltipEl.style.top = position.top + tooltip.caretY + 'px';
            tooltipEl.style.fontFamily = tooltip._fontFamily;
            tooltipEl.style.fontSize = tooltip.fontSize;
            tooltipEl.style.fontStyle = tooltip._fontStyle;
            tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
    };

    var getRGBAText = function(source_elem, newalpha) {
        current_color = getComputedStyle(source_elem).getPropertyValue("background-color");
        match = /rgba?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*\d+[\.\d+]*)*\)/g.exec(current_color)
        return "rgba(" + [match[1],match[2],match[3],newalpha].join(',') +")";
      };

    var setRGBA = function(source_elem, newalpha) {
        current_color = getComputedStyle(source_elem).getPropertyValue("background-color");
        match = /rgba?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(,\s*\d+[\.\d+]*)*\)/g.exec(current_color)
        source_elem.style.backgroundColor = "rgba(" + [match[1],match[2],match[3],newalpha].join(',') +")";
      };

    var getAlphaForCount = function(thecount)
      {
          if(thecount < 1)
          {
              return .11;
          } else
          if(thecount > 10)
          {
              return 1;
          }
          return .5 + thecount/20;    
      };
    
    var showTMMGraphics = function(chart_id, typename, thevalues, thelabels) 
    {

        if(thevalues === null)
        {
            thevalues = [];
            thevalues['Q4'] = 0;
            thevalues['Q3'] = 0;
            thevalues['Q2'] = 0;
            thevalues['Q1'] = 0;
        };

        var elem_q4 = document.getElementById("dn-" + typename + "-q4");
        var elem_q3 = document.getElementById("dn-" + typename + "-q3");
        var elem_q2 = document.getElementById("dn-" + typename + "-q2");
        var elem_q1 = document.getElementById("dn-" + typename + "-q1");
        var elem_ctx = document.getElementById(chart_id);

        //Important CLEAR ANY EXISTING CONTENT AT THE PIE ELEMENT NOW!!!!
        elem_ctx.innerHTML = "";

        var value_q4 = thevalues.Q4;
        var value_q3 = thevalues.Q3;
        var value_q2 = thevalues.Q2;
        var value_q1 = thevalues.Q1;

        setRGBA(elem_q4,getAlphaForCount(value_q4));
        setRGBA(elem_q3,getAlphaForCount(value_q3));
        setRGBA(elem_q2,getAlphaForCount(value_q2));
        setRGBA(elem_q1,getAlphaForCount(value_q1));

        elem_q4.innerHTML = value_q4;
        elem_q3.innerHTML = value_q3;
        elem_q2.innerHTML = value_q2;
        elem_q1.innerHTML = value_q1;

        var total = (value_q4 + value_q3 + value_q2 + value_q1);
        if(total === 0)
        {
            plotEmptyPie(elem_ctx);
        } else {
            var typename = 'doughnut';
            var piedata = {
                    labels: thelabels,
                    datasets: [{
                        data: [value_q4, value_q3, value_q2, value_q1],
                        backgroundColor: [
                            getRGBAText(elem_q4, 0.5),
                            getRGBAText(elem_q3, 0.5),
                            getRGBAText(elem_q2, 0.5),
                            getRGBAText(elem_q1, 0.5)
                        ],
                        hoverBackgroundColor: [
                            getRGBAText(elem_q4, 1),
                            getRGBAText(elem_q3, 1),
                            getRGBAText(elem_q2, 1),
                            getRGBAText(elem_q1, 1)
                        ],
                        borderColor: [
                            "rgba(1,1,1,1)",
                            "rgba(1,1,1,1)",
                            "rgba(0,0,0,1)",
                            getRGBAText(elem_q1, 1)
                        ],
                        borderWidth: 1
                    }]
            };
            plotRegularPie(elem_ctx, typename, piedata);
        }
    };
    
    var showSixCatGraphics = function(chart_id, typename, thevalues, thelabels) 
    {
        if(thevalues === null)
        {
            thevalues = [];
            thevalues['level4'] = 0;
            thevalues['level3'] = 0;
            thevalues['level2'] = 0;
            thevalues['level1'] = 0;
            thevalues['zero'] = 0;
            thevalues['unknown'] = 0;
        }

        var elem_level4 = document.getElementById("dn-" + typename + "-l4");
        var elem_level3 = document.getElementById("dn-" + typename + "-l3");
        var elem_level2 = document.getElementById("dn-" + typename + "-l2");
        var elem_level1 = document.getElementById("dn-" + typename + "-l1");
        var elem_level0 = document.getElementById("dn-" + typename + "-l0");
        var elem_unknown = document.getElementById("dn-" + typename + "-unknown");
        var elem_ctx = document.getElementById(chart_id);

        var value_level4 = thevalues.level4;
        var value_level3 = thevalues.level3;
        var value_level2 = thevalues.level2;
        var value_level1 = thevalues.level1;
        var value_level0 = thevalues.zero;
        var value_unknown = thevalues.unknown;

        setRGBA(elem_level4,getAlphaForCount(value_level4));
        setRGBA(elem_level3,getAlphaForCount(value_level3));
        setRGBA(elem_level2,getAlphaForCount(value_level2));
        setRGBA(elem_level1,getAlphaForCount(value_level1));
        setRGBA(elem_level0,getAlphaForCount(value_level0));
        setRGBA(elem_unknown,getAlphaForCount(value_unknown));

        elem_level4.innerHTML = value_level4;
        elem_level3.innerHTML = value_level3;
        elem_level2.innerHTML = value_level2;
        elem_level1.innerHTML = value_level1;
        elem_level0.innerHTML = value_level0;
        elem_unknown.innerHTML = value_unknown;

        var total = (value_level4 + value_level3 + value_level2 + value_level1 + value_level0 + value_unknown);
        if(total === 0)
        {
            plotEmptyPie(elem_ctx);
        } else {
            var typename = 'doughnut';
            var piedata = {
                    labels: thelabels,
                    datasets: [{
                        data: [value_level4, value_level3, value_level2, value_level1, value_level0, value_unknown],
                        backgroundColor: [
                            getRGBAText(elem_level4, 0.5),
                            getRGBAText(elem_level3, 0.5),
                            getRGBAText(elem_level2, 0.5),
                            getRGBAText(elem_level1, 0.5),
                            getRGBAText(elem_level0, 0.5),
                            getRGBAText(elem_unknown, 0.5)
                        ],
                        hoverBackgroundColor: [
                            getRGBAText(elem_level4, 1),
                            getRGBAText(elem_level3, 1),
                            getRGBAText(elem_level2, 1),
                            getRGBAText(elem_level1, 1),
                            getRGBAText(elem_level0, 1),
                            getRGBAText(elem_unknown, 1)
                        ],
                        borderColor: [
                            getRGBAText(elem_level4, 1),
                            getRGBAText(elem_level3, 1),
                            getRGBAText(elem_level2, 1),
                            "rgba(1,1,1,1)",
                            getRGBAText(elem_level0, 1),
                            getRGBAText(elem_unknown, 1)
                        ],
                        borderWidth: 1
                    }]
                };
            plotRegularPie(elem_ctx, typename, piedata);
        }    
    };
    
    if(bigfathom_util.userdashboard_tm.charts.hasOwnProperty(chart_id))
    {
        //Otherwise earlier chart html still exists!
        bigfathom_util.userdashboard_tm.charts[chart_id].destroy();
    }
    if(category === 4)
    {
        showTMMGraphics(chart_id, typename, thevalues, thelabels);     
    } else if (category === 6){
        showSixCatGraphics(chart_id, typename, thevalues, thelabels);
    } else {
        throw "There is NO charting support for category=" + category;
    }
};

bigfathom_util.userdashboard_tm.refreshdisplay = function(personid)
{
    console.log("Starting refreshdisplay for personid=" + personid);
    var grab_dataname = 'dashnuggets_personal';
    var grab_fullurl = bigfathom_util.data.getGrabDataUrl(grab_dataname,{"about_personid": personid, "showalldetail_yn": 0});    
    console.log("grab_fullurl=" + grab_fullurl);
    console.log("Finished refreshdisplay for personid=" + personid);
    var grab_forecast_dataname = 'forecast_nuggets';
    
    var updateGraphcsWithDataSelection = function(dataselection)
    {
        var record = bigfathom_util.userdashboard_tm.latest_data;
        if(dataselection === "summary")
        {
            //Easy
            bigfathom_util.userdashboard_tm.showInfluenceTotals(record.influence.summary.levels); 
            bigfathom_util.userdashboard_tm.showImportanceTotals(record.importance.summary.levels);
            bigfathom_util.userdashboard_tm.showTMMTotals(record.tmm.summary.tmm);
            bigfathom_util.userdashboard_tm.showOARTMMTotals(record.oartmm.summary.tmm);
            bigfathom_util.userdashboard_tm.showSprintTMMTotals(record.sprinttmm.summary.tmm);
        } else {
            //Check each one
            var onedataset;
            if(record.influence.by_project.hasOwnProperty(dataselection))
            {
                onedataset = record.influence.by_project[dataselection].levels;
            } else {
                onedataset = null;
            }
            bigfathom_util.userdashboard_tm.showInfluenceTotals(onedataset); 
            if(record.importance.by_project.hasOwnProperty(dataselection))
            {
                onedataset = record.importance.by_project[dataselection].levels;
            } else {
                onedataset = null;
            }
            bigfathom_util.userdashboard_tm.showImportanceTotals(onedataset);
            if(record.tmm.by_project.hasOwnProperty(dataselection))
            {
                onedataset = record.tmm.by_project[dataselection].tmm;
            } else {
                onedataset = null;
            }
            bigfathom_util.userdashboard_tm.showTMMTotals(onedataset);
            if(record.oartmm.by_project.hasOwnProperty(dataselection))
            {
                onedataset = record.oartmm.by_project[dataselection].tmm;
            } else {
                onedataset = null;
            }
            bigfathom_util.userdashboard_tm.showOARTMMTotals(onedataset);
            if(record.sprinttmm.by_project.hasOwnProperty(dataselection))
            {
                onedataset = record.sprinttmm.by_project[dataselection].tmm;
            } else {
                onedataset = null;
            }
            bigfathom_util.userdashboard_tm.showSprintTMMTotals(onedataset);
        }
    };
    
    var resizePortletArea = function(event, ui) 
    {
        if(event !== null)
        {
            console.log("LOOK resizePortletArea " + JSON.stringify(ui.position));
        }

        var content_elem = $('#dash-tab-personal');  //TODO depends on the tab!
        var top_h = content_elem.height();
        var cah = bigfathom_util.userdashboard_tm.chartarea_height;
        console.log("LOOK top_h=" + top_h + " cah=" + cah);
        console.log("LOOK bigfathom_util.userdashboard_tm.min_height=" + bigfathom_util.userdashboard_tm.min_height);

        var maxh = 0;
        for(var i=0; i<bigfathom_util.userdashboard_tm.portal_col_count; i++)
        {
            var colid ='portlet-column-' + i;
            var col_elem = $('#' + colid);
            var col_h = col_elem.height() + cah;
            if(col_h > maxh)
            {
                maxh = col_h;
            }
        }
        if(maxh > top_h || maxh < top_h-100)
        {
            maxh += bigfathom_util.userdashboard_tm.min_height/5; //Just a buffer for cosmetic reasons
            content_elem.height(maxh);
            console.log("Resized to " + maxh + " from top_h=" + top_h);
        }
    };
    
    var callbackMemberActionFunction = function(callbackid, responseBundle)
    {
        var url_img = Drupal.settings.myurls.images;
        var otsp_computed_counter=0;
        var otsp2display = [];
        var map_otsp2display = {};

        var updateProjectForecast = function(callbackid, responseBundle)
        {
            if (typeof callbackid !== 'undefined' && typeof responseBundle !== 'undefined')
            {
                var getOTSPValueMarkup = function(value)
                {
                    if(value > 0)
                    {
                        return Math.round(bigfathom_util.userdashboard_tm.rounding_factor * value)/bigfathom_util.userdashboard_tm.rounding_factor;
                    }
                    return 0;
                };
                var getOTSPLogicMarkup = function(logic)
                {
                    var logictext_ar = [];
                    for(var lidx=0;lidx<logic.length;lidx++)
                    {
                        logictext_ar.push(logic[lidx].detail);
                    }
                    return logictext_ar.join(' and ');
                };
                
                var getNotificationMarkup = function(items,typename)
                {
                    var label = "";
                    var tooltip = "";
                    var markup = "";
                    if(items)
                    {
                        var logictext_ar = [];
                        for(var lidx=0;lidx<items.length;lidx++)
                        {
                            logictext_ar.push(items[lidx].message);
                        }
                        if(items.length > 0)
                        {
                            if(items.length == 1)
                            {
                                label = "(1 " + typename + ")";
                            } else {
                                label = "(" + items.length + " " + typename + "s)";
                            }
                            tooltip = logictext_ar.join(' and ');
                            markup = " <span class='colorful-" + typename + " normal-size' title='" + tooltip + "'>" + label + "</span>";
                        }
                    }
                        
                    return markup;
                };
                
                var display_map = map_otsp2display['p'+callbackid];
                var projectid = display_map.pid;
                var elemid = display_map.elemid;
                
                var records;
                if(!responseBundle.hasOwnProperty('data') || responseBundle.data === null || !responseBundle.data.hasOwnProperty('data'))
                {
                    records = [];
                } else {
                    records = responseBundle.data.data;
                }
                var root_otsp;
                var warnings_markup;
                var errors_markup;
                if(typeof records.by_projectid === 'undefined' || !records.by_projectid.hasOwnProperty(projectid))
                {
                    root_otsp = {};
                    root_otsp['value'] = 0;
                    root_otsp['logic'] = [];
                    warnings_markup = '';
                    errors_markup = 'NO FORECAST FOR #' + projectid;// + " SEE " + JSON.stringify(records.by_projectid);
                    
                } else {
                    root_otsp = records.by_projectid[projectid].root_otsp;
                    warnings_markup = getNotificationMarkup(records.by_projectid[projectid].warnings.detail,'warning');
                    errors_markup = getNotificationMarkup(records.by_projectid[projectid].errors.detail,'error');
                }
                var tooltip_markup = getOTSPLogicMarkup(root_otsp.logic);
                var value_markup = getOTSPValueMarkup(root_otsp.value);
                var classname = root_otsp.value > .9 ? "otsp-good" : root_otsp.value < .4 ? "otsp-veryugly" : root_otsp.value < .5 ? "otsp-ugly" : root_otsp.value < .70 ? "otsp-bad" : "otsp-ambiguous";

                var otsp_value_markup = "<span class='" + classname + " normal-size' title='" + tooltip_markup + "'>" + value_markup + "</span>" + warnings_markup + errors_markup;
                var jump2forecast = '<a class="normal-size" title="click here to select project#' 
                            + projectid 
                            + ' now and view forecast details" href="' + base_url + 'bigfathom/projects/select&projectid=' 
                            + projectid 
                            + '&redirect=bigfathom/projects/workitems/forecast">'
                            + ' ' + otsp_value_markup + ' '
                            + '</a>';
                
                $("#" + elemid).html(jump2forecast);
                //$("#" + elemid).html("<span class='" + classname + "' title='" + tooltip_markup + "'>" + value_markup + "</span>" + warnings_markup + errors_markup);
                otsp_computed_counter++;
            }
            if(otsp2display.length > 0)
            {
                var computeNextBatch = function()
                {
                    //TODO add a timer to detect 'TIMEOUT' condition!
                    var detail = otsp2display.pop();
                    var projectid = detail.pid;
                    //var elemid = detail.elemid;
                    map_otsp2display['p'+projectid] = detail;
                    var grab_forecast_fullurl = bigfathom_util.data.getGrabDataUrl(grab_forecast_dataname,{"relevant_projectids": projectid}); 
                    bigfathom_util.data.getDataFromServer(grab_forecast_fullurl, {}, updateProjectForecast, projectid);
                };
                //Call via a timer so we do not swamp the server!
                var pausetime;
                if(otsp_computed_counter < 1)
                {
                    pausetime = 0;
                } else {
                    if(otsp_computed_counter < 5)
                    {
                        pausetime = 250;
                    } else {
                        pausetime = 500;
                    }
                }
                var mytimer = setTimeout(computeNextBatch, pausetime);
            }
        };

        function getImageURL(filename)
        {
            return url_img + "/" + filename;
        }
        
        var pin_imgurl = getImageURL("icon_pin.png");        
        console.log("Starting callbackMemberActionFunction for callbackid=" + callbackid);
        if(responseBundle.hasOwnProperty('data') && responseBundle.data != null && responseBundle.data.hasOwnProperty('data'))
        {
            var record = responseBundle.data.data;
            bigfathom_util.userdashboard_tm.latest_data = record;
            console.log("LOOK record = " + JSON.stringify(record));
            
            var project_lookup = record['project_lookup'];
            var elem_buttons_area = document.getElementById("dn-radios-area");
            var id = "dn-rsd-psummary";
            var radiogroupname = "dn-radio-selectdata";
            var content_elem = $('#dash-tab-personal');  //TODO depends on the tab!
            bigfathom_util.userdashboard_tm.chartarea_height = content_elem.height();
            bigfathom_util.userdashboard_tm.chartarea_width = content_elem.width();
            var maxcolcount;
            var colwidth;
            if(bigfathom_util.userdashboard_tm.chartarea_width < 700)
            {
                maxcolcount = 1;
                colwidth = bigfathom_util.userdashboard_tm.chartarea_width - 15;
            } else
            if(bigfathom_util.userdashboard_tm.chartarea_width < 1300)
            {
                maxcolcount = 2;
                colwidth = bigfathom_util.userdashboard_tm.chartarea_width / 2 - 15;
            } else
            if(bigfathom_util.userdashboard_tm.chartarea_width < 1900)
            {
                maxcolcount = 3;
                colwidth = bigfathom_util.userdashboard_tm.chartarea_width / 3 - 15;
            } else
            if(bigfathom_util.userdashboard_tm.chartarea_width < 2500)
            {
                maxcolcount = 4;
                colwidth = bigfathom_util.userdashboard_tm.chartarea_width / 4 - 15;
            } else {
                colwidth = 2500/4;
                maxcolcount = Math.floor(bigfathom_util.userdashboard_tm.chartarea_width / colwidth);
                colwidth -= 15;
            }
            console.log("LOOK colwidth=" + colwidth + " maxcolcount=" + maxcolcount);            
            var col_items = {};
            for(var i=0; i<maxcolcount; i++)
            {
                col_items[i] = [];
            }
            var projcount = 0;
            var today = new Date();
            for (var pid in project_lookup) 
            {
                var col_num = projcount % maxcolcount;
                var pinfo = project_lookup[pid];
                var pid = pinfo.projectid;
                id = "dn-rsd-p" + pid;
                var labeltext = pinfo.name;
                
                var getID4Elem = function(elemname)
                {
                    return 'p'+ pid + '_' + elemname;
                };
                
                var getPAM = function(elemname,label,value,tooltip,classname)
                {
                    var insert_classname;
                    if (typeof classname === 'undefined') 
                    {
                        insert_classname = "";
                    } else {
                        insert_classname = " " + classname;
                    }
                    
                    var elem_id4value=getID4Elem(elemname);
                    var cleanvalue;
                    if(value === null)
                    {
                        cleanvalue = "-";
                    } else {
                        cleanvalue = value;
                    }
                    return '<div class="inline"><label title="' + tooltip + '" for="' + elem_id4value + '">'
                            + label 
                            + ':</label><span class="showvalue'+ insert_classname +'" id="'+ elem_id4value + '">' 
                            + cleanvalue + '</span></div>';
                };
                
                var status_class;
                if(pinfo.happy_yn === null)
                {
                    status_class = "status-ambiguous";
                } else {
                    if(pinfo.happy_yn == 1)
                    {
                        status_class = "status-happy-yes";
                    } else {
                        status_class = "status-happy-no";
                    }
                }
                //console.log("LOOK pinfo=" + JSON.stringify(pinfo));
                var enddate_class;
                if(pinfo.terminal_yn === null || pinfo.terminal_yn == 1)
                {
                    enddate_class = "";
                } else {
                    var days = bigfathom_util.data.getDaysBetweenDates(today, pinfo.end_dt);
                    if(days < 1)
                    {
                        if(days < 0)
                        {
                            enddate_class = "concern-failed";
                        } else {
                            enddate_class = "concern-high";
                        }
                    } else {
                        if(days < 2)
                        {
                            enddate_class = "concern-medium";
                        } else {
                            if(days < 3)
                            {
                                enddate_class = "concern-low";
                            } else {
                                enddate_class = "";
                            }
                        }
                    }
                }
                
                var status_markup = "<span class='" + status_class + "'>" + pinfo.title_tx + "</span>";
                
                var infomarkup1 = "";// "<div>" + JSON.stringify(pinfo) + "</div>";
                var infomarkup2 = getPAM('status','Status', status_markup, "Current declared status of the project") 
                        + "<br>" 
                        + getPAM('start_dt','Start Date', pinfo.start_dt, "Declared start date of the project")
                        + getPAM('end_dt','End Date', pinfo.end_dt, "To be on-time, the project must successfully complete by this date", enddate_class) 
                        + "<br>";
                var infomarkup3 = getPAM('open_workitems','Open Workitems', pinfo.open_workitems, "The number of workitems that have not yet been closed") 
                        + " " 
                        + getPAM('closed_workitems','Closed Workitems', pinfo.closed_workitems, "The number of workitems that have already been closed")
                        + "<br>" 
                        + getPAM('started_workitems','Started Workitems', pinfo.started_workitems, "The number of workitems that have been started and not yet closed")
                        + " " 
                        + getPAM('happy_workitems','Happy Workitems', pinfo.happy_workitems, "The number of workitems that are in some kind of recognized happy state!")
                        + "<br>";

                var infomarkup4 = getPAM('otsp','On-Time Success Probability',"computing...","On-time success probability computed from current details of the project") 
                        + "<br>";
                
                var base_url = bigfathom_util.url.base_url;
                var infomarkup5 = '<div classname="select-project-jump">'
                        + '<img src="' + pin_imgurl + '">'
                        + '<a class="small-action-button" title="select project#' 
                            + pid 
                            + ' now for topic brainstorming" href="' + base_url + 'bigfathom/projects/select&projectid=' 
                            + pid 
                            + '&redirect=bigfathom/projects/design/brainstormcapture">'
                            + 'brainstorm</a>'
                        + ' '
                        + '<a class="small-action-button" title="select project#' 
                            + pid 
                            + ' now to list action menu options" href="' + base_url + 'bigfathom/projects/select&projectid=' 
                            + pid 
                            + '&redirect=bigfathom/topinfo/projects">'
                            + 'activity menu</a>'
                        + ' '
                        + '<a class="small-action-button" title="select project#' 
                            + pid 
                            + ' now for detailed work" href="' + base_url + 'bigfathom/projects/select&projectid=' 
                            + pid 
                            + '&redirect=bigfathom/projects/workitems/duration">'
                            + 'times and durations</a>'
                        + '</div>';
                
                var infomarkup = infomarkup1 + infomarkup2 + infomarkup3 + infomarkup4 + infomarkup5;
                
                console.log("LOOK projcount=" + projcount + " col_num=" + col_num + " pinfo=" + JSON.stringify(pid));
                
                var hovertext = "Visualize important metrics for project#" + pid;
                var radiomarkup = '<span class="inline portlet-title" title="' + hovertext 
                                    + '" ><input type="radio" id=' + id 
                                    + ' name="' + radiogroupname + '" value="' 
                                    + pid + '" /><label for="' 
                                    + id + '">' + labeltext + '</label></span><span class="ui-icon ui-icon-minusthick portlet-toggle"></span>';
                var oneportlet_markup = '<div class="portlet">'
                        + '<div class="portlet-header">' + radiomarkup + '</div>' 
                        + '<div class="portlet-content">' + infomarkup + '</div>' 
                        + '</div> ';
                col_items[col_num].push(oneportlet_markup);
                var elem_id4value = getID4Elem("otsp");
                otsp2display.push({'elemid':elem_id4value,'pid':pid});
                projcount++;
            }
            var portlets_markup = "";
            for(var i=0; i<maxcolcount; i++)
            {
                var id='portlet-column-' + i;
                portlets_markup += "\n<div id='" + id + "' class='portlet-column'>";
                var onecol = col_items[i];
                for(var p=0; p<onecol.length; p++)
                {
                    portlets_markup += onecol[p];
                }
                portlets_markup += "\n</div>";
            }
            var composite_markup = '<div class="dn-summary-selector"><span class="inline" title="A composition of values from all your projects" ><input type="radio" id=' 
                    + id + ' checked=checked name="' 
                    + radiogroupname 
                    + '" value="summary" /><label for="' 
                    + id + '">All your projects</label></span></div><br />';
            elem_buttons_area.innerHTML = composite_markup + portlets_markup;          

            //Record some key information
            bigfathom_util.userdashboard_tm.portal_col_count = maxcolcount;
            var vtabs_content_container = $("#vtabs-container");
            bigfathom_util.userdashboard_tm.min_height = vtabs_content_container.height();
            $( ".portlet-column" ).sortable({
              connectWith: ".portlet-column",
              handle: ".portlet-header",
              cancel: ".portlet-toggle",
              placeholder: "portlet-placeholder ui-corner-all",
              update: resizePortletArea
            }).width(colwidth);

            $( ".portlet" )
              .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
              .find( ".portlet-header" )
                .addClass( "ui-widget-header ui-corner-all" );

            $( ".portlet-toggle" ).on( "click", function() {
              var icon = $( this );
              icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
              icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
            });

            //Make the initial sizing
            resizePortletArea(null, null);

            //Create a listener to handle the clicks
            $('input:radio').on('change', function()
            {
                var dataselection = $(this).val();
                updateGraphcsWithDataSelection(dataselection);
            });
            updateGraphcsWithDataSelection("summary");
            updateProjectForecast();
        };
    };

    //Get latest records from the server
    bigfathom_util.data.getDataFromServer(grab_fullurl, {}, callbackMemberActionFunction, personid);
    console.log("At bottom of the send logic for refreshdisplay of personid=" + personid);
    
};

