/* 
 * Functions for working with project baselines
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
if(!bigfathom_util.hasOwnProperty("manageprojectbaseline"))
{
    //Create the object property because it does not already exist
    bigfathom_util.manageprojectbaseline = {
        version: "20170911.4",
        rounding_factor: 10000,
        default_chart_name: 'hours',
        ctx: null,
        myChartInstance: null,
        charts: {},
        stacknames: null,
        latest_data: {}
    };
}

bigfathom_util.manageprojectbaseline.showChart = function(chart_name)
{
    var getChartDataSetsBundle = function(chart_name)
    {
        var getDataSetForType = function(chart_type, rawdata)
        {
            var final_data = [];
            if(chart_type === 'line')
            {
                final_data = rawdata;
            } else {
                for(var i=0; i<rawdata.length; i++)
                {
                    var onedata = rawdata[i];
                    final_data.push(onedata.y);
                }
            }
            
            return final_data;
        };
        
        var getOneDataSetForType = function(chart_type, label_txt, backgroundColor_txt, one_rawdata_set)
        {
            var oneset = {
                    'label': label_txt, 
                    'backgroundColor': backgroundColor_txt,
                    'data': getDataSetForType(chart_type, one_rawdata_set)
                };
            return oneset;
        };
        
        var xaxis_point_count;
        var chart_type;
        var bundle = {};
        var stacknames = bigfathom_util.manageprojectbaseline.stacknames;
        var datasets_raw;
        var datasets_final = [];   
        console.log("LOOK getChartDataSetsBundle for chart_name=" + chart_name);
        if(bigfathom_util.manageprojectbaseline.latest_data.hasOwnProperty(chart_name))
        {
            datasets_raw = bigfathom_util.manageprojectbaseline.latest_data[chart_name];
            //console.log("LOOK NOW datasets_raw=" + JSON.stringify(datasets_raw));
            var oneset;
            if(chart_name === 'count')
            {
                
                xaxis_point_count = datasets_raw['closed'].length;
                chart_type = xaxis_point_count > 1 ? 'line' : 'bar';
                
                oneset = getOneDataSetForType(chart_type, 'Closed Workitems', 'rgba(1,222,1,1)', datasets_raw['closed']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Started Workitems', 'rgba(255,215,1,1)', datasets_raw['started']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Unstarted Workitems', 'rgba(255,69,99,1)', datasets_raw['unstarted']);
                datasets_final.push(oneset);
                
            } else if(chart_name === 'hours') {
                
                xaxis_point_count = datasets_raw['closed_act_worked'].length;
                chart_type = xaxis_point_count > 1 ? 'line' : 'bar';
            
                oneset = getOneDataSetForType(chart_type, 'Closed Worked Hours (est)', 'rgba(1,222,1,1)', datasets_raw['closed_est_worked']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Closed Worked Hours (act)', 'rgba(1,222,33,1)', datasets_raw['closed_act_worked']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Started Worked Hours So-far (est)', 'rgba(255,215,1,1)', datasets_raw['started_est_worked']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Started Worked Hours So-far (act)', 'rgba(255,233,1,1)', datasets_raw['started_act_worked']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Started Workitems Estimated Remaining Hours', 'rgba(250,200,1,1)', datasets_raw['started_ere']);
                datasets_final.push(oneset);
                
                oneset = getOneDataSetForType(chart_type, 'Unstarted Workitems Estimated Hours', 'rgba(255,69,99,1)', datasets_raw['unstarted_ere']);
                datasets_final.push(oneset);
            }
            
        } else {
            var errmsg = "Did NOT find data for chart_name=" + chart_name;
            console.log(errmsg + " >>> bigfathom_util.manageprojectbaseline.latest_data=" + JSON.stringify(bigfathom_util.manageprojectbaseline.latest_data));
            throw errmsg;
        }
        
        bundle['chart_type'] = chart_type;
        bundle['datasets'] = datasets_final;
        //console.log("LOOK bundle=" + JSON.stringify(bundle));
        return bundle;
    };
    
    var getChartDef = function(chart_bundle)
    {
        var chart_datasets = chart_bundle['datasets'];
        var chart_type = chart_bundle['chart_type'];
        var chart_def = {};
        var options;
        
        chart_def['type'] = chart_type;
        var data_section = {}; 
        if(chart_bundle['labels'] !== null)
        {
            //data_section['labels'] = chart_bundle['labels'];
        }
        data_section['datasets'] = chart_datasets;
        chart_def['data'] = data_section;
        if(chart_type === 'line')
        {
            options = {
                maintainAspectRatio: false,
                tooltips: { mode: 'index', 
                    intersect: false,
                    callbacks: {
                        beforeTitle: function(tooltipItem, data) {
                            var index = tooltipItem[0].index;
                            var stackname = bigfathom_util.manageprojectbaseline.stacknames[index];
                            var nicename;
                            if(index < bigfathom_util.manageprojectbaseline.stacknames.length-1)
                            {
                                nicename = 'Baseline ' + stackname;
                            } else {
                                //Last one is NOT a baseline
                                nicename = stackname;
                            }
                            //console.log("LOOK tooltipItem=" + JSON.stringify(tooltipItem));
                            return nicename;
                        }            
                    }
                },
                responsive: true, 
                scales: {
                    xAxes: [{
                            ticks: {source: 'data'},
                            type: 'time',
                            time: {unit: 'day'},
                            distribution: 'linear'
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            };
        } else if(chart_type === 'bar') {
            options = {
                maintainAspectRatio: false,
                tooltips: { mode: 'index', intersect: false},
                responsive: true, 
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            };
        } else {
            throw "No handler for chart_type=" + chart_type;
        }
        chart_def['options'] = options;
        //console.log("LOOK chart_def=" + JSON.stringify(chart_def));        
        return chart_def;
    };

    //alert("LOOK about to show the chart="+chart_name);
    
    var chart_bundle = getChartDataSetsBundle(chart_name);
    var chart_def = getChartDef(chart_bundle);
    if(bigfathom_util.manageprojectbaseline.myChartInstance !== null)
    {
        bigfathom_util.manageprojectbaseline.myChartInstance.destroy();
    }
    bigfathom_util.manageprojectbaseline.myChartInstance = new Chart(bigfathom_util.manageprojectbaseline.ctx, chart_def);
    
};

/**
 * Calls all of initialization routines
 */
bigfathom_util.manageprojectbaseline.init = function(personid, mychartdata_obj, stacknames)
{
    console.log("Initialize start manageprojectbaseline " + bigfathom_util.manageprojectbaseline.version + " for personid=" + personid);
    bigfathom_util.manageprojectbaseline.personid = personid;
    bigfathom_util.manageprojectbaseline.latest_data = mychartdata_obj;
    bigfathom_util.manageprojectbaseline.stacknames = stacknames;
    bigfathom_util.manageprojectbaseline.ctx = $("#myChart");
    
    //$('#chart_area').trigger('create');
    //$('#chart_name_hours').prop('checked',true).checkboxradio("refresh");
    console.log("Initialize done manageprojectbaseline " + bigfathom_util.manageprojectbaseline.version + " for personid=" + personid);
};

jQuery(document).ready(function(){
    (function ($) {

        var default_checkbox_id = 'chart_name_' + bigfathom_util.manageprojectbaseline.default_chart_name;

        var url_img = Drupal.settings.myurls.images;
        var personid = Drupal.settings.personid;
        var mychartdata = Drupal.settings.mychartdata;
        var stacknames = Drupal.settings.mychart_stacknames;
        
        console.log("Starting manageprojectbaseline " + bigfathom_util.manageprojectbaseline.version);
        console.log("... url_img=" + url_img);
        console.log("... personid=" + personid);
        console.log("... mychartdata=" + mychartdata);
        console.log("... mychart_stacknames=" + stacknames);
        
        var mychartdata_obj = JSON.parse(mychartdata);
        //console.log("... mychartdata_obj=" + mychartdata_obj);
        //console.log("... mychartdata2=" + JSON.stringify(mychartdata_obj));
        
        //Activate special look and feel
        $(function() 
            {
                $("input:radio").checkboxradio();
                //Select the default here -- yes odd, but otherwise getting jquery 'prior init' error!
                $('#chart_name_hours').prop('checked',false).checkboxradio("refresh");
                $('#chart_name_count').prop('checked',false).checkboxradio("refresh");
                $('#' + default_checkbox_id).prop('checked',true).checkboxradio("refresh");
            } 
        );

        //Now attach handlers
        $('input[name=chart_modegroup]').click(function() 
        {
            var newValue = $('input[name=chart_modegroup]:checked').val();
            bigfathom_util.manageprojectbaseline.showChart(newValue);
        });
        
        $('#chart_area').trigger('create');
        
        bigfathom_util.manageprojectbaseline.init(personid, mychartdata_obj, stacknames);
        bigfathom_util.manageprojectbaseline.showChart(bigfathom_util.manageprojectbaseline.default_chart_name);
        
    }(jQuery));
});
    

