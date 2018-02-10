/*
 * The bones of simple image popup dialog
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
        version: "20170712.1"
    };
}

jQuery(document).ready(function()
{
    bigfathom_util.dialog.initialize = function(elementid, width, modal) 
    {
        if(typeof width === 'undefined' || width === null)
        {
            width = 800;
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
    $( "img.click-zoom" ).click(function(e)
    {
        var x = $(this).offset().left;//e.clientX;
        var y = $(this).offset().top; //e.clientY;
        var width = $(window).width();
        var height = $(window).height();

        var img_url = $(this).attr('src');

        $("#dlg_zoom_image_form").css({'top':y});
        $("#dialog-zoom-image").css({"display":"block"});
        $("#dlg_zoom_image_element").attr({"src":img_url});
        
        //alert("LOOK TODO (x=" + x + " y="  + y + ")  (width=" + width + " height="  + height + ") -- SHOW LARGER IMAGE of " + img_url);
    });
    $( "#btn_close" ).click(function(event)
    {
        $("#dialog-zoom-image").css("display","none");
    });

    //alert('look testing myimage box thing');
});