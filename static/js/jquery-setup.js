
/*
 *  Move datepicker into main.js file -- would be used in different modules
 *  Also with the select-2
 */

// Set datepicker event jQuery
$( function() {
    $( ".datepicker" ).datepicker({
        dateFormat: 'dd.mm.yy'
    });
});

$(document).ready(function() {
    $('.select-2').select2();
});
