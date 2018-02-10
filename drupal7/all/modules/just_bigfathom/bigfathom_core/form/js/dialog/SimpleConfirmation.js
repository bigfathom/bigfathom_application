/*
 * The bones of simple confirmation popup dialog
 */

if (typeof bigfathom_util === 'undefined') 
{
    //Create the main object because it does not already exist
    var bigfathom_util = {};
}
if(!bigfathom_util.hasOwnProperty("dialog"))
{
    //Create the object property because it does not already exist
    bigfathom_util.dialog = {
        version: "20170207.1"
    };
}

jQuery(document).ready(function()
{
    bigfathom_util.dialog.initialize = function(elementid, width, modal) 
    {
        if(typeof width === 'undefined' || width === null)
        {
            width = 400;
        }
        if(typeof modal === 'undefined' || modal === null)
        {
            modal = true;
        }
        var thedialog = $( "#" + elementid ).dialog({
          "resizable": false,
          "height": "auto",
          "width": width,
          "modal": modal,
          "autoOpen":false,
          buttons: {
            "Close": function() {
              $( this ).dialog( "close" );
            }
          }
        });
        return thedialog;
    };    
});