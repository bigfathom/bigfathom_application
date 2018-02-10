/*
 * ------------------------------------------------------------------------------------
 * Created by Frank Font (mrfont@room4me.com)
 *
 * Copyright (c) 2015-2018 Room4me.com Software LLC, a Maryland USA company (room4me.com)
 * 
 * All rights reserved.  Contact author for more information.
 * This is BETA software.  No warranty or fitness for use is implied at this time.
 * --------------------------------------------------------------------------------------
 * 
 * Add behaviors at runtime by loading page specific files that implement the following 
 * functions:
 * 
 * bigfathom_util.table.finalizeAllGridCells(controller, rowIndex)
 * bigfathom_util.table.customColumnsInit(controller)
 * bigfathom_util.table.isEditable(controller, rowIndex, columnIndex)	
 * bigfathom_util.table.rowDataChanged = function(controller, rowIndex, columnIndex, oldValue, newValue, uiblocker) 
 * 
 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if (typeof bigfathom_util.table === 'undefined') 
{
    //Create the main table member because it does not already exist
    bigfathom_util.table = {};
}
if(!bigfathom_util.hasOwnProperty("browser_grid_helper"))
{
    //Create the object property because it does not already exist
    bigfathom_util.browser_grid_helper = {
        "version": "20171114.1",
        "grid_instances": {},
        "utility":{
            "getDaysBetweenDates": function(start_dt, end_dt)
            {
                if(start_dt === null || end_dt == null || start_dt == '' || end_dt == '' || start_dt.length < 8  || end_dt.length < 8)
                {
                    return null;
                }
                var isoparts_start_dt = start_dt.split("-");
                var isoparts_end_dt = end_dt.split("-");

                var oneDay = 24*60*60*1000; // hours*minutes*seconds*milliseconds
                var date1 = new Date(isoparts_start_dt[0],isoparts_start_dt[1],isoparts_start_dt[2]);
                var date2 = new Date(isoparts_end_dt[0],isoparts_end_dt[1],isoparts_end_dt[2]);

                return Math.ceil((date2.getTime() - date1.getTime())/(oneDay)) + 1;
            },
            "getWorkdaysBetweenDates": function(start_dt, end_dt)
            {
                if(start_dt === null || end_dt == null || start_dt == '' || end_dt == '' || start_dt.length < 8  || end_dt.length < 8)
                {
                    return null;
                }
                var days = bigfathom_util.browser_grid_helper.utility.getDaysBetweenDates(start_dt, end_dt);
                return Math.ceil(days * (5/7)); //Approximate
            }
        }
    };
}

jQuery(document).ready(function(){

    (function ($) {
        var url_img = Drupal.settings.myurls.images;
        var browserGridTableElems = $("table.browserGrid");

        for(var i=0; i<browserGridTableElems.length; i++)
        {
            var tableid = browserGridTableElems.get(i).id;
            bigfathom_util.browser_grid_helper.grid_instances[tableid] = new EditableGrid(tableid
                    , { 
                        enableSort: true,
                        editmode: "absolute",
                        editorzoneid: "edition",
                        sortIconUp: url_img + "/icon_up.png", 
                        sortIconDown: url_img + "/icon_down.png",
                        pageSize: 10,
                        maxBars: 5
                       });
        }

        function getImageURL(filename)
        {
            return url_img + "/" + filename;
        }

        //helper function to display a message
        function displayMessage(text, style) { 
            console.log("BrowserGridHelper Message = " + text);
                //_$("message").innerHTML = "<p class='" + (style || "ok") + "'>" + text + "</p>"; 
        } 

        //this will be used to render our table headers
        function InfoHeaderRenderer(message) { 
                this.message = message; 
                this.infoImage = new Image();
                this.infoImage.src = getImageURL("icon_smallinfo.png");
        };

        InfoHeaderRenderer.prototype = new CellRenderer();
        InfoHeaderRenderer.prototype.render = function(cell, value) 
        {
            if (value) 
            {
                //Make the help mouse friendly with hover effect
                cell.setAttribute("title", this.message);
                
                // here we don't use cell.innerHTML = "..." in order not to break the sorting header that has been created for us (cf. option enableSort: true)
                var link = document.createElement("a");
                link.href = "javascript:alert('" + this.message + "');";
                link.appendChild(this.infoImage);
                cell.appendChild(document.createTextNode("\u00a0\u00a0"));
                cell.appendChild(link);
            }
        };

        //this function will initialize our enhanced grid
        EditableGrid.prototype.initializeGrid = function(tableElem, metadata) 
        {
            //console.log("LOOK in EditableGrid.prototype.initializeGrid!");
            with (this) 
            {
                
                if(typeof tableElem === 'undefined')
                {
                    throw "Missing tableElem!!!";
                }
                var tableid = tableElem.id;
                var totalscontainerid = 'totals-' + tableid; 
                var custom_table_filters_area_id = 'custom-query-area-' + tableid;
                var large_filters_area_id = 'large-filters-area-' + tableid;
                var filterid = 'filter-' + tableid;
                var filtercontrol = 'filtercontrol-' + tableid;
                var pagesizeid = 'pagesize-' + tableid;
                var elementids = {
                    "tableid":tableid,
                    "filterid":filterid,    //TODO rename this to something else!!!!!
                    "textfiltercontroldivid":filtercontrol,
                    "pagesizeid":pagesizeid,    //DEPRECATED use pagesizecontrolid instead!!!
                    "pagesizecontrolid":pagesizeid,
                    "totalscontainerid":totalscontainerid,
                    "custom_table_filters_area_id":custom_table_filters_area_id,
                    "large_filters_area_id":large_filters_area_id
                };
                this.browserGridTableData = {
                    "elementids": elementids
                };
                
                var oneBrowserGrid = bigfathom_util.browser_grid_helper.grid_instances[tableid];
                var cols = metadata.length;
                displayMessage("Starting initializeGrid tableid=" + tableid + " with " + cols + " columns of metadata");
                if(cols < 1)
                {
                    throw "Expected more than zero columns for tableid=" + tableid + " metadata=" + metadata + "!!!";
                }
                for (var i = 0; i < cols; i++) 
                {
                    var oneitem = metadata[i];
                    if(oneitem.hasOwnProperty("helpinfo") && oneitem.helpinfo.trim() > "")
                    {
                        setHeaderRenderer(oneitem.name, new InfoHeaderRenderer(oneitem.helpinfo));
                    }
                    if(oneitem.hasOwnProperty("named_validator") && oneitem.named_validator.trim() > "")
                    {
                        switch(oneitem.named_validator)
                        {
                            case "GTZ":
                                addCellValidator(oneitem.name, new CellValidator({ 
                                        isValid: function(value) { return value == "" || (parseInt(value) > 0); }
                                }));
                                break;
                            case "GTEZ":
                                addCellValidator(oneitem.name, new CellValidator({ 
                                        isValid: function(value) { return value == "" || (parseInt(value) >= 0); }
                                }));
                                break;
                            case "PROBABILITY":
                                addCellValidator(oneitem.name, new CellValidator({ 
                                        isValid: function(value) { return value == "" || ((parseInt(value) >= 0) && (parseInt(value) <= 1) ); }
                                }));
                                break;
                            case "[0,100]":
                                addCellValidator(oneitem.name, new CellValidator({ 
                                        isValid: function(value) { return value == "" || ((parseInt(value) >= 0) && (parseInt(value) <= 100) ); }
                                }));
                                break;
                            default:
                                console.log("WARNING: No validator defined for named_validator=" + oneitem.named_validator);
                        }
                    }
                }

                var hasTableSpecificFunctions = !(typeof bigfathom_util === 'undefined' 
                        || typeof bigfathom_util.table === 'undefined' 
                        || typeof bigfathom_util.table.customColumnsInit === 'undefined');

                
                if(hasTableSpecificFunctions && typeof bigfathom_util.table.customColumnsInit !== 'undefined')
                {
                    bigfathom_util.table.customColumnsInit(this);
                }
                
                this.browserGridTable = {
                    "updateTotals": function(newcontent)
                        {
                            if(!$("#"+totalscontainerid).length)
                            {
                                throw "Did NOT find element with id=" + totalscontainerid;
                            }
                            alert("About to write " + totalscontainerid + " with " + newcontent);
                            $("#"+totalscontainerid).html(newcontent);   
                            alert("Did it work to write " + totalscontainerid + " with " + newcontent);
                        }
                };
                
                var uiblocker = {

                    "show":function(myselector, msg) 
                        { 
                            if(typeof myselector !== 'undefined' &&  myselector !== null)
                            {
                                if(typeof msg === 'undefined')
                                {
                                    msg = 'Refreshing this row ...';
                                }
                                $(myselector).block({ 
                                    message: msg, 
                                    css: { border: '3px solid #a00' } 
                                }); 
                            } else {
                                $.blockUI();
                            }
                        },

                    "hide":function(myselector) 
                        {
                            if(typeof myselector !== 'undefined' &&  myselector !== null)
                            {
                                $(myselector).unblock();     
                            } else {
                                $.unblockUI();
                            }
                        }

                };
                    
                modelChanged = function(rowIndex, columnIndex, oldValue, newValue, row) 
                { 
                    if(oldValue === newValue)
                    {
                        //No change.
                        return;
                    }
                    if(hasTableSpecificFunctions && typeof bigfathom_util.table.rowDataChanged === 'undefined')
                    {
                        //No handler.
                        console.log("WARNING: There is no bigfathom_util.table.rowDataChanged function!");
                        return;
                    }
                    //bigfathom_util.table.modelChanged(this, rowIndex, columnIndex, oldValue, newValue, row);
                    bigfathom_util.table.rowDataChanged(this, rowIndex, columnIndex, oldValue, newValue, uiblocker); 
                };
                
                isEditable = function(rowIndex, columnIndex)
                {
                    return bigfathom_util.table.isEditable(this, rowIndex, columnIndex);
                };
                
                cellClicked = function(rowIndex, columnIndex) 
                {
                    if(typeof bigfathom_util.table.cellClicked !== 'undefined')
                    {
                        return bigfathom_util.table.cellClicked(this, rowIndex, columnIndex);
                    }
                };

                // update paginator whenever the table is rendered (after a sort, filter, page change, etc.)
                tableRendered = function() 
                { 
                    this.updatePaginator(tableid); 
                    
                    //Also update all the displayed fields
                    if(hasTableSpecificFunctions && typeof bigfathom_util.table.finalizeAllGridCells !== 'undefined')
                    {
                        bigfathom_util.table.finalizeAllGridCells(this);
                    }
                };

                rowSelected = function(oldRowIndex, newRowIndex) 
                {
                    if (oldRowIndex < 0) 
                        displayMessage("Selected row '" + this.getRowId(newRowIndex) + "'");
                    else 
                        displayMessage("Selected row has changed from '" + this.getRowId(oldRowIndex) + "' to '" + this.getRowId(newRowIndex) + "'");
                };

                renderGrid("tablecontent", "browserGrid", tableid);

                // set active (stored) filter if any
                var currentFilter = oneBrowserGrid.localget('filter');
                oneBrowserGrid.localset('filter', currentFilter ? currentFilter : '') 
                _$(filterid).value = currentFilter ? currentFilter : '';

                // filter when something is typed into filter
                var customcols = null;
                
                var gridFilterFunction = function()
                {
                    if(hasTableSpecificFunctions && typeof bigfathom_util.table.customRowMatchFunction !== 'undefined')
                    {
                        oneBrowserGrid.filter(_$(filterid).value,customcols,bigfathom_util.table.customRowMatchFunction); 
                    } else {
                        oneBrowserGrid.filter(_$(filterid).value); 
                    }
                };

                if(hasTableSpecificFunctions)
                {
                    bigfathom_util.table.invokeFilter = gridFilterFunction;
                }
                
                _$(filterid).onkeyup = function() 
                {
                    gridFilterFunction();
                };

                // bind page size selector
                var pageSize = this.pageSize;
                $("#"+pagesizeid).val(pageSize).change(function() 
                { 
                    var pagesize = $("#"+pagesizeid).val();
                    oneBrowserGrid.setPageSize(pagesize); 
                });

                if(hasTableSpecificFunctions && typeof bigfathom_util.table.finalizeAllGridCells !== 'undefined')
                {
                    bigfathom_util.table.finalizeAllGridCells(this);
                    gridFilterFunction();   //So we apply custom filters on start
                }
            }
        };

        /**
         * Returns metadata for use in the editable grid by parsing existing HTML table
         */
        EditableGrid.prototype.deriveMetadataFromHtml = function(tableid)
        {
            displayMessage("Starting deriveMetadataFromHtml tableid=" + tableid );
            var metadata = [];
            var table = document.getElementById(tableid);
            var thead = table.getElementsByTagName('thead')[0];
            var headerRows = thead.getElementsByTagName('tr');
            var headers;
            if(headerRows.length > 0)
            {
                //Just read the last row
                var lastHeaderRowIdx = headerRows.length-1;
                headers = headerRows[lastHeaderRowIdx].getElementsByTagName('th');
            }else {
                headers = table.getElementsByTagName('th');
            }
            for (var i = 0; i < headers.length; i++) 
            {
                var onemd = {};

                var thTag = headers[i];
                var thClass;
                var colspan = 1;
                if(!thTag.hasAttribute("colspan"))
                {
                    colspan = 1;
                } else {
                    colspan = parseInt(thTag.getAttribute("colspan"),10);
                }
                if(colspan === 1)
                {
                    if(!thTag.hasAttribute("class"))
                    {
                        thClass = "";
                    } else {
                        thClass = " " + thTag.getAttribute("class") + " ";
                    }

                    var colname = null;
                    var helpinfo = null;
                    var datatype = null;
                    var editable = false;
                    var named_validator = null;

                    if(thTag.hasAttribute("colname"))
                    {
                        colname = thTag.getAttribute("colname");
                    } else
                    if(thTag.hasAttribute("name"))
                    {
                        colname = thTag.getAttribute("name");
                    };

                    if(thTag.hasAttribute("title"))
                    {
                        helpinfo = thTag.getAttribute("title");
                    } else
                    if(thTag.hasAttribute("helpinfo"))
                    {
                        helpinfo = thTag.getAttribute("helpinfo");
                    };

                    if(thTag.hasAttribute("datatype"))
                    {
                        datatype = thTag.getAttribute("datatype");
                    };

                    if(thClass.indexOf(" canedit ") > -1)
                    {
                        editable = true;    
                    }
                    if(editable !== true && thTag.hasAttribute("editable"))
                    {
                        editable = thTag.getAttribute("editable");
                        if(editable === 1)
                        {
                            editable = true;
                        }
                    }

                    if(editable)
                    {
                        if(thTag.hasAttribute("named_validator"))
                        {
                            named_validator = thTag.getAttribute("named_validator");
                        };
                    }

                    var labeltext;
                    var innerSpan = thTag.getElementsByTagName("span");
                    if(innerSpan.length === 0)
                    {
                        //Plain
                        labeltext = thTag.innerHTML;
                    } else {
                        //Has a span
                        var spanTag = innerSpan[0];
                        if(spanTag.hasAttribute("colname"))
                        {
                            colname = spanTag.getAttribute("colname");
                        };
                        if(spanTag.hasAttribute("title"))
                        {
                            helpinfo = spanTag.getAttribute("title");
                        }
                        labeltext = spanTag.innerHTML;
                    }
                    if(colname === null)
                    {
                        colname = "f" + i;
                    }
                    onemd = {name: colname, label: labeltext };
                    onemd['editable'] = editable;
                    if(datatype !== null)
                    {
                        onemd['datatype'] = datatype;
                    } else {
                        onemd['datatype'] = 'sortablehtml';
                    }
                    if(named_validator !== null)
                    {
                        onemd['named_validator'] = named_validator;
                    }
                    if(helpinfo !== null)
                    {
                        onemd['helpinfo'] = helpinfo;
                    }
                    metadata.push(onemd);
                    thTag.innerHTML = onemd.name;
                }
            }
            displayMessage("Finished deriveMetadataFromHtml tableid=" + tableid );
            return metadata;
        };

        function createBottomElements(tableElem, namesuffix)
        {
            var id_paginator;
            var id_edition;
            
            if(typeof tableElem === 'undefined')
            {
                throw "Missing tableElem!!!";
            }
            var tableid = tableElem.id;
            var id_downloadvalues_div = "downloadvalues-" + tableid;
            var id_downloadvalues_button = "button-downloadvalues-" + tableid;
            if (typeof namesuffix === 'undefined')
            {
                id_paginator = "paginator-" + tableid;
                id_edition = "edition-" + tableid;
            } else {
                id_paginator = "paginator-" + namesuffix;
                id_edition = "edition-" + namesuffix;
            }
            var newWrapper = document.createElement("div");
            var newDiv1 = document.createElement("div");
            newDiv1.setAttribute("id", id_paginator);
            newDiv1.setAttribute("class", "browserGrid-paginator");
            var newDiv2 = document.createElement("div");
            newDiv2.setAttribute("id", id_edition);

            var downloadiconurl = getImageURL("icon_download_tabledata.png")            
            var newDiv3 = document.createElement("div");
            newDiv3.setAttribute("id", id_downloadvalues_div);
            newDiv3.setAttribute("class", "download-table-values");
            var downloadButton = document.createElement("a");
            downloadButton.setAttribute("id", id_downloadvalues_button);
            downloadButton.setAttribute("href", "#");
            //downloadButton.setAttribute("download", "data_" + tableid + ".txt");
            var downloadimg = document.createElement("img");
            downloadimg.setAttribute("src", downloadiconurl);
            downloadButton.setAttribute("title", "Download the table data to a local text file that is compatible with most spreadsheet applications");
            //var t = document.createTextNode("Download Values");
            //downloadButton.appendChild(t);
            downloadButton.appendChild(downloadimg);
            newDiv3.appendChild(downloadButton);
            
            newWrapper.appendChild(newDiv1);
            newWrapper.appendChild(newDiv3);
            newWrapper.appendChild(newDiv2);
            
            insertAfter(newWrapper, tableElem);

            //Now attach handlers
            $('a#' + id_downloadvalues_button).click(function() {
                var now = new Date().toString();
                this.download = "data_" + tableid + ".txt";

                var thedatabundle = getFormattedDataMap();
                console.log("LOOK result of get thing is " + JSON.stringify(thedatabundle));
                this.href = "data:text/plain;charset=UTF-8," + encodeURIComponent(thedatabundle.data);
                //this.href = "data:Application/octet-stream," + encodeURIComponent(thedatabundle.data);
            });
        }

        var getFormattedDataMap = function() 
        {
            var oneBrowserGrid = bigfathom_util.browser_grid_helper.grid_instances[tableid];
            var controller = oneBrowserGrid;
            var data_as_text = "";
            var colmap = [];

            //var regex = new RegExp("\\<[^\\>]*\\>"); //To remove all markup!!!
            var regex_removeBGM = new RegExp("\\[[^\\]]*\\]"); //To remove all browser grid markup!!!
            var rawvalue;
            var cleanvalue;

            for(var columnIndex=0; columnIndex < controller.getColumnCount(); columnIndex++)
            {
                var column = controller.getColumn(columnIndex);
                if(column.label != "Action Options" && column.label != "Gantt" && column.label != null)
                {
                    colmap.push({"columnIndex":columnIndex, "name":column.name, "label":column.label});
                    if(columnIndex > 0)
                    {
                        data_as_text += "\t";
                    }
                    rawvalue = column.label;
                    //cleanvalue = rawvalue.replace(regex, "").trim();
                    cleanvalue = jQuery("<colheading>"+rawvalue+"</colheading>").text().trim();
                    data_as_text += rawvalue;
                }
            }
            data_as_text += "\n";

            var rowcount = controller.getRowCount();
            for(var rowIndex=0; rowIndex<rowcount; rowIndex++)
            {
                var values = controller.getRowValues(rowIndex);
                for(var i=0; i < colmap.length; i++)
                {
                    var name = colmap[i]['name'];
                    if(i > 0)
                    {
                        data_as_text += "\t";
                    }
                    rawvalue = values[name];
                    if(typeof rawvalue != "string")
                    {
                        data_as_text += rawvalue;
                    } else {
                        //cleanvalue = rawvalue.replace(regex, "").trim();
                        cleanvalue = jQuery("<data>"+rawvalue+"</data>").text().trim();
                        cleanvalue = cleanvalue.replace(regex_removeBGM, "");
                        data_as_text += cleanvalue;
                    }
                }
                data_as_text += "\n";
            }    

            var themap = {
                      'colmap':colmap
                    , 'data':data_as_text
                };

            return themap;
        };

        function insertAfter(newNode, referenceNode) 
        {
            referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
        };

        function createTopElements(tableElem, namesuffix)
        {
            var id_pagecontrol;
            var id_pagesize;
            var id_filter;
            var id_filtercontrol_advarea;
            var id_filterlabel;
            var id_filtercontrol;
            
            if (typeof namesuffix === 'undefined')
            {
                id_pagecontrol = "pagecontrol-" + tableElem.id;
                id_pagesize = "pagesize-" + tableElem.id;
                id_filter = "filter-" + tableElem.id;
                id_filterlabel = "filterlabel-" + tableElem.id;
                id_filtercontrol = "filtercontrol-" + tableElem.id;
                id_filtercontrol_advarea = 'custom-query-area-' + tableElem.id;
            } else {
                id_pagecontrol = "pagecontrol-" + namesuffix;
                id_pagesize = "pagesize-" + namesuffix;
                id_filter = "filter-" + namesuffix;
                id_filterlabel = "filterlabel-" + namesuffix;
                id_filtercontrol = "filtercontrol-" + namesuffix;
                id_filtercontrol_advarea = 'custom-query-area-' + namesuffix;
            }

            var newDiv_mow = document.createElement("div");
            newDiv_mow.setAttribute("class", "browserGrid-filters-outerwrapper");

            var divElemMainFilterArea = document.createElement("div");
            divElemMainFilterArea.setAttribute("class", "browserGrid-mainfilter-area");
            
            var divElemPage = document.createElement("div");
            divElemPage.setAttribute("id", id_pagecontrol);
            divElemPage.setAttribute("class", "browserGrid-pagecontrol");
            divElemPage.setAttribute("title", "Limits the number of rows displayed at one time");
            var labelElem1 = document.createElement("label");
            labelElem1.setAttribute("for", id_pagecontrol);
            var labelTextElem1 = document.createTextNode("Rows per page ");
            labelElem1.appendChild(labelTextElem1);

            var selectElem1 = document.createElement("select");
            selectElem1.setAttribute("id", id_pagesize);
            selectElem1.setAttribute("name", id_pagesize);
            selectElem1.setAttribute("class", "browserGrid-pagesize");
            var ov = [5,10,15,20,25,30,40,50,100];
            var optionElem1;
            for(var i=0; i<ov.length; i++)
            {
                var num = ov[i];
                optionElem1 = document.createElement("option");
                optionElem1.setAttribute("value", num);
                optionElem1.appendChild( document.createTextNode(num));
                selectElem1.appendChild(optionElem1);
            }

            divElemPage.appendChild(labelElem1);
            divElemPage.appendChild(selectElem1);

            var divElemTextFilter = document.createElement("div");
            divElemTextFilter.setAttribute("id", id_filtercontrol);
            divElemTextFilter.setAttribute("class", "browserGrid-filtercontrol");
            var labelElem2 = document.createElement("label");
            divElemTextFilter.appendChild(labelElem2);
            labelElem2.setAttribute("id", id_filterlabel);
            labelElem2.setAttribute("for", id_filter);
            var labelTextElem2 = document.createTextNode("Filter ");
            labelElem2.appendChild(labelTextElem2);
            var inputElem2 = document.createElement("input");
            inputElem2.setAttribute("id", id_filter);
            inputElem2.setAttribute("class", "browserGrid-filter");
            inputElem2.setAttribute("type", "text");
            divElemTextFilter.appendChild(labelElem2);
            divElemTextFilter.appendChild(inputElem2);

            var divElemAdvArea = document.createElement("div");
            divElemAdvArea.setAttribute("id", id_filtercontrol_advarea);
            
            var pn = tableElem.parentNode;
            pn.insertBefore(newDiv_mow, tableElem);
 
            divElemMainFilterArea.appendChild(divElemTextFilter);
            divElemMainFilterArea.appendChild(divElemAdvArea);

            newDiv_mow.appendChild(divElemMainFilterArea);
            newDiv_mow.appendChild(divElemPage);
        };

        EditableGrid.prototype.onloadHTML = function(tableid) 
        {
            var tableElem = _$(tableid);
            var metadata = this.deriveMetadataFromHtml(tableid);
            
            //Create the top controls
            createTopElements(tableElem);
            
            //Create the bottom controls
            createBottomElements(tableElem);

            //Metadata are built in Javascript: we give for each column a name and a type
            this.load({ metadata: metadata });
            
            //We attach our enhanced grid to the existing table
            this.attachToHTMLTable(tableElem);
            displayMessage("Grid attached to HTML table tableid=" + tableid + ": " + this.getRowCount() + " row(s)"); 

            this.initializeGrid(tableElem, metadata);
        };

        //function to render the paginator control
        EditableGrid.prototype.updatePaginator = function(tableid)
        {
            var id_filterlabel = "filterlabel-" + tableid;
            var filterlabel = $('#' + id_filterlabel);
            var unfiltered_count = this.getUnfilteredRowCount();
            var filtered_count = this.getRowCount();
            filterlabel.attr("title","showing " + filtered_count + " of " + unfiltered_count + " rows");
            
            var id_paginator = "paginator-" + tableid;

            var oneBrowserGrid = bigfathom_util.browser_grid_helper.grid_instances[tableid];
            var paginator = $("#" + id_paginator).empty();
            var nbPages = this.getPageCount();

            // get interval
            var interval = this.getSlidingPageInterval(20);
            if (interval == null) 
            {
                return;
            }

            // get pages in interval (with links except for the current page)
            var pages = this.getPagesInInterval(interval, function(pageIndex, isCurrent) {
                    if (isCurrent) 
                    {
                        return "" + (pageIndex + 1);
                    };
                    return $("<a>").css("cursor", "pointer").html(pageIndex + 1).click(function(event) { oneBrowserGrid.setPageIndex(parseInt($(this).html()) - 1); });
                });

            // "first" link
            var link = $("<a>").html("<img title='Jump to page 1' src='" + getImageURL("icon_gofirst.png") + "'/>&nbsp;");
            if (!this.canGoBack()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
            else link.css("cursor", "pointer").click(function(event) { oneBrowserGrid.firstPage(); });
            paginator.append(link);

            // "prev" link
            link = $("<a>").html("<img src='" + getImageURL("icon_prev.png") + "'/>&nbsp;");
            if (!this.canGoBack()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
            else link.css("cursor", "pointer").click(function(event) { oneBrowserGrid.prevPage(); });
            paginator.append(link);

            // pages
            for (p = 0; p < pages.length; p++) paginator.append(pages[p]).append(" | ");

            // "next" link
            link = $("<a>").html("<img src='" + getImageURL("icon_next.png") + "'/>&nbsp;");
            if (!this.canGoForward()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
            else link.css("cursor", "pointer").click(function(event) { oneBrowserGrid.nextPage(); });
            paginator.append(link);

            // "last" link
            link = $("<a>").html("<img title='Jump to page " + nbPages + "' src='" + getImageURL("icon_golast.png") + "'/>&nbsp;");
            if (!this.canGoForward()) link.css({ opacity : 0.4, filter: "alpha(opacity=40)" });
            else link.css("cursor", "pointer").click(function(event) { oneBrowserGrid.lastPage(); });
            paginator.append(link);
        };

        for(var i=0; i<browserGridTableElems.length; i++)
        {
            var tableid = browserGridTableElems.get(i).id;
            var oneBrowserGrid = bigfathom_util.browser_grid_helper.grid_instances[tableid];
            oneBrowserGrid.onloadHTML(tableid);  
        };
        
    }(jQuery));
});
    
