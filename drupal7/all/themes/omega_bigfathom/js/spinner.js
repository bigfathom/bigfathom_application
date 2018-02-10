jQuery(window).load(function() {
    //$('.loader-icon').removeClass('spinning-cog').addClass('shrinking-cog');
    //$('.loader-background').delay(1300).fadeOut(); 
    //$('#loader').removeClass('overlay-loader');

    $("#loader").fadeOut("fast", function() {
        $(this).removeClass("overlay-loader");
    });

});                
