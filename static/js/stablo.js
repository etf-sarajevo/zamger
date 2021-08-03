// Pomocna funkcija za rad sa stablom

function daj_stablo(ime){
	var me = document.getElementById(ime);
	var img = document.getElementById('img-'+ime);
	if (me.style.display=="none"){
		me.style.display="inline";
		img.src="static/images/minus.png";
	}
	else {
		me.style.display="none";
		img.src="static/images/plus.png";
	}
}

/*
 *  Menu JavaScript code - left menu
 *  Onclick show and hide hidden elements
 */

$(document).ready(function () {
    let menu_indexes = {}; let counter = 0;


    $("body").on('click', '.s-lm-wrapper, .sci-d', function (){
        $(".inside-links").each(function () {
            $(this).css('height', '0px');
        });
        $(".fa-angle-right").each(function () {
            $(this).css('transform', 'rotate(0deg)');
        });

        let height = $(this).find(".inside-lm-link").length;

        if(!$(this).hasClass('active')){
            $(this).find(".inside-links").css('height', (height * 34) + 'px');
            $(this).find(".fa-angle-right").css('transform', 'rotate(90deg)');
            $(this).addClass('active');
        }else{
            $(this).removeClass('active');
        }

        $(".s-lm-wrapper").not($(this)).removeClass('active');
    });

    // -------------------------------------------------------------------------------------------------------------- //
    let smm_open = false, smm_width = 320; // System mobile menu || System mobile menu width

    $(".system-m-i-t").click(function () {
        if(!smm_open){
            $(".s-left-menu").css('left', '0px');
        }else{
            $(".s-left-menu").css('left', '-320px');
        }
        smm_open = !smm_open;
    });
});

/*
 *  Select-2 Scripts
 *  Make executable as select-2 name; Crete an ajax event for special cases when data load is huge
 */

let select_2_link = 'index.php?sta=ws/api_links'; // TODO - set this link!

$(document).ready(function() {
    $('.select-2').select2();

    $(".select-2-ajax").select2({
        placeholder: 'Odaberite mjesto',
        ajax: {
            url: select_2_link,
            dataType: 'json',
            delay: 250,
            data: function (data) {
                return {
                    term: data.term,
                    type: $(this).attr('call_f')
                };
            },
            processResults: function (data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });
});

/*
 *  Datepicker
 *  Set class of text DOM element as datepicker, automatically offers an datepicker module with format dd.mm.YYYY
 */

$(document).ready(function() {
    $( ".datepicker" ).datepicker({
        dateFormat: 'dd.mm.yy'
    });
});
