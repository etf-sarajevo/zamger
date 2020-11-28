// Validate form and draggable elements
let time_from = false, time_to = false, event_new_elem_ = 0, event_minutes_start, event_minutes_end;

let save_data = false; // If it is true, time format is fine - make ajax request
let event_date; // Date for event -- set value on onclick event on calendar day

let calendar = {
    wrapper: ".calendar",
    calendar_body : ".dynamic-body",
    name: 'calendar',
    buttons: ["NAZAD", "DANAS", "NAPRIJED", "CIJELI MJESEC", "NOVO ODSUSTVO"],
    week_days: ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota'],
    week_short: ['Pon', 'Uto', 'Sri', 'Čet', 'Pet', 'Sub', 'Ned'],
    months_d: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    months_name: ["Januar", "Februar", "Mart", "April", "Maj", "Juni", "Juli", "August", "Septembar", "Oktobar", "Novembar", "Decembar"],
    custom_date: null,
    n_day: null,
    n_month: null,
    n_year: null,
    date: new Date(),
    year: null,
    month: null,
    day: null,
    week_day: null,
    save_url: '',
    saving: true,
    d_day_in_week : 'Utorak',
    d_date : '1. Septembar 2020',
    current_time : 0,

    // Get the first day of week of month
    firstDay : function () {
        this.date.setDate(1);
        return this.date.getDay();
    },
    // Get duration of current month
    monthDuration : function () {
        return this.months_d[this.month];
    },
    // Get days of previous months - last couple of them
    previousMonth : function () {
        if (this.month === 0) {
            return (31 - this.firstDay() + 1);
        } else {
            return (this.months_d[this.month - 1] - this.firstDay() + 1);
        }
    },
    // Get the current year
    currentYear : function () {
        return (new Date()).getFullYear();
    },
    // Get the current month
    currentMonth : function () {
        return (new Date()).getMonth();
    },
    // Get the current day
    currentDay : function () {
        return (new Date()).getDate();
    },
    setDates : function () {
        if (!this.custom_date) this.date = new Date();
        else this.date = new Date(this.n_year, this.n_month, this.n_day);

        this.year = this.date.getFullYear();
        this.month = this.date.getMonth();
        this.day = this.date.getDate();
        this.week_day = this.date.getDay();

        // Every 4 year, February has 29 days
        if (this.year % 4 === 0) this.months_d[1] = 29;
    },
    createCalendar : function () {

        // Remove everything from calendar
         $(this.wrapper).empty();
        // vars.wrapper.contents(':not(#add-new-absence)').remove();

        // Set dates as we want it - initially it uses custom date
        this.setDates();

        // Let's start with building GUI
        this.createHeader();
        this.createBody();

        // this.createSingleDay();
    },

    createBody : function () {
        let days_counter = 0;  // serves for that, when we get the day of the week of month , that we are looking at,
                               // then, we can start clocking -> it's current month

        let lastMonthDays = this.previousMonth(); // Days of the last month
        let nextMonthDays = 1;

        // Here we set the month value in middle
        $(".month-on-top").html(this.months_name[this.month] + ' <span>' + this.year + '</span>');

        let row = '';       // Single row - represents a week in month

        for (let i = 0; i < 6; i++) {
            let col = '';   // Single column - represents a day in a week

            for (let j = 0; j < 7; j++) {
                let day = 0;     // Value of single day
                let month = 0;   // Use current month
                let year = 0;   // Get current year
                let class_name = ''; // when we want to give better view for current month

                if (i === 0 && j === this.firstDay()) days_counter++;

                /******************************************************************************************************/
                if (days_counter && days_counter < (this.monthDuration() + 1)) {
                    // Current month !
                    class_name = 'current-value'; // Bold text for current month
                    if (days_counter === this.currentDay() && this.year === this.currentYear() && this.month === this.currentMonth()) class_name += ' current-day';

                    day = days_counter++;
                    month = this.month;
                    year = this.year;
                }

                /******************************************************************************************************/
                else if (days_counter) {
                    // Next month
                    day = nextMonthDays++;
                    year = this.year;
                    if (this.month === 11) {
                        month = 0;
                        year = (this.year + 1);
                    } else month = (this.month + 1);
                }

                /******************************************************************************************************/
                if (!days_counter) {
                    // Previous month
                    day = lastMonthDays++;
                    year = this.year;
                    if (this.month === 0) {
                        month = 11;
                        year = (this.year - 1);
                    } else month = (this.month - 1);
                }

                col += '<div class="calendar-col ' + class_name + '" year="' + year + '" month="' + month + '" day="' + day + '"><p>' + day + '</p> </div>';
            }

            // style="top: -'+ (i + 1)*5 +'px !important;"
            row += '<div class="calendar-row">' + col + '</div>';
            if (days_counter > this.monthDuration()) break;
        }

        $(this.wrapper).append('<div class="calendar-body dynamic-body">' + row + '</div>');
    },
    createHeader : function () {
        $(this.wrapper).append('<div class="calendar-header"> <h1 class="month-on-top"></h1> <div class="buttons"> <div class="arrow-button previous-month"><i class="fas fa-angle-left"></i> </div> <div class="text-button"> DANAS </div> <div class="arrow-button next-month"> <i class="fas fa-angle-right"></i> </div> </div> </div>');

        let row = '';
        for(let i=0; i < 7; i++){
            row += '<div class="calendar-col"> ' + this.week_days[i] + ' </div>';
        }

        $(this.wrapper).append( '<div class="calendar-body"> <div class="calendar-row small-row"> ' + row + ' </div> </div>');

    },
    createSingleDayHeader : function(){
        return '<div class="header-of-day"> <h2 id="name-of-single-day">' + this.d_day_in_week + ', ' + this.d_date + ' </h2> <div class="day-actions"><div class="inside-element back-to-full-calendar"> <i class="fas fa-angle-left"></i> <p>Nazad</p> </div> <div class="inside-element create-cal-event"> <i class="fas fa-plus"></i> <p>Unos</p> </div> </div> </div>';
    },
    getHourValue : function(index){
        if(index < 10){return ('0' + index + ':00');}
        else return (index + ':00');

    },
    createSingleDayBody : function () {
        let hours = '';
        for(let i=0; i<24; i++){
            let hour  = '<div class="hour"> <p> ' + this.getHourValue(i) + ' </p> </div>';
            let event = '<div class="event-elem">  </div>';
            hours += '<div class="hours" style="top: '+(i * 60)+'px"> ' + hour + event + '</div>';
        }

        return '<div class="single-day-body"> <div class="events-wrapper"> ' + hours + ' </div> </div>';
    },
    createSingleDay : function () {
        $(this.wrapper).append( '<div class="full-day-preview"> ' + this.createSingleDayHeader() + this.createSingleDayBody() + ' </div>');

        // Append current time line
        let current_h = (new Date()).getHours();
        let current_m = (new Date()).getMinutes();
        let top_time = (current_h * 60) + current_m;

        $(".events-wrapper").append(
            '<div class="current-time-line" style="top:'+(top_time)+'px" title="Trenutno vrijeme '+current_h+':'+current_m+'">  </div>'
        );
    },
    removeSingleDay : function () {
        $(this.wrapper).find(".full-day-preview").remove();
        $(".add-new-event-wrapper").fadeOut();
    }
};

$("body").on('click', '.next-month', function () {
    calendar.custom_date = true;
    calendar.n_day = calendar.day;
    if(calendar.month === 11){
        calendar.n_month = 0;
        calendar.n_year  = parseInt(calendar.year + 1);
    }else{
        calendar.n_month = (calendar.month + 1);
        calendar.n_year  = calendar.year;
    }
    calendar.createCalendar();
});
$("body").on('click', '.previous-month', function () {
    calendar.custom_date = true;
    calendar.n_day = calendar.day;
    if(calendar.month === 0){
        calendar.n_month = 11;
        calendar.n_year  = (calendar.year - 1);
    }else{
        calendar.n_month = (calendar.month - 1);
        calendar.n_year  = calendar.year;
    }
    calendar.createCalendar();
});

$("body").on('click', '.text-button', function () {
    calendar.custom_date = false;
    calendar.createCalendar();
});


// ------------------------------------------------------------------------------------------------------------------ //

let dayData = function(date){

    $.ajax({
        type:'POST',
        url: 'index.php?sta=ws/predmet',
        data: { event_get_data: true, event_date: date},
        success:function(response){

            if(response['success'] === 'true'){
                for(let i=0; i<response['data'].length; i++){
                    let start = response['data'][i]['start'].split(':');
                    event_minutes_start = ((parseInt(start[0]) * 60) + parseInt(start[1]));

                    let end = response['data'][i]['end'].split(':');
                    event_minutes_end   = ((parseInt(end[0]) * 60) + parseInt(end[1]));

                    let height = event_minutes_end - event_minutes_start;

                    $(".events-wrapper").append(
                        '<div class="event-short-preview" id="'+response['data'][i]['id']+'" style="top:'+event_minutes_start+'px; height: '+(height)+'"><h4 id="'+response['data'][i]['id']+'-header"> ' + response['data'][i]['title'] + ' </h4> <p id="'+event_new_elem_+'-time"> ' + (response['data'][i]['start'] + ' : ' + response['data'][i]['end']) + ' </p> </div> '
                    );
                    console.log(response['data'][i]['naslov']);
                }

                $('.event-short-preview').animate({
                    scrollTop: 1000
                }, 2000);
            }else{
                $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
            }

            /*  */

        }
    });
}


$("body").on('click', '.calendar-col', function () {
    let date = new Date($(this).attr('year') + '-' + (parseInt($(this).attr('month')) + 1) + '-' + $(this).attr('day'));
    calendar.d_day_in_week = calendar.week_days[date.getDay()];
    calendar.d_date = $(this).attr('day') + '. ' + calendar.months_name[$(this).attr('month')] + ' ' + $(this).attr('year');

    // Get date for clicked "day"
    event_date = $(this).attr('year')+'-'+(parseInt($(this).attr('month')) + 1)+'-'+$(this).attr('day');

    // First, check if there is any data for this particular day
    let response = dayData(event_date);

    calendar.createSingleDay();
});
$("body").on('click', '.back-to-full-calendar', function () {
    calendar.removeSingleDay();
});

$( function() {
    $( ".day-form" ).draggable({ containment: "parent" });
} );

function validateHhMm(id) {
    let element = $("#" + id);
    let isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(element.val());

    if (isValid) {
        element.css("background-color", '#fff');
        save_data = true;
    }
    else {
        element.css("background-color", '#fba');
        save_data = false;
    }

    return isValid;
}

function getNewEventRange(){
    let time_f_string = $("#time-from").val(); let time_t_string = $("#time-to").val();
    return time_f_string + ' : ' + time_t_string;
}
function getNewEventTitle(){
    if($("#time-title").val() === '') {return '( Nema naslova )';}
    else {return $("#time-title").val();}
}

$("body").on('keyup', '.form-time', function (){
    let value = validateHhMm($(this).attr('id'));
    let time = $(this).val(); time = time.split(':');

    if($(this).attr('id') === 'time-from'){
        if(value){
            if(time[0] < 23){
                $("#time-to").val((parseInt(time[0]) + 1) + ':' + time[1]);
            }
            time_from = true;
            time_to = true;

            // Create elements

            event_minutes_start = ((parseInt(time[0]) * 60) + parseInt(time[1]));

            let time_two = $("#time-to").val(); time_two = time_two.split(':');
            event_minutes_end   = ((parseInt(time_two[0]) * 60) + parseInt(time_two[1]));

            let height = event_minutes_end - event_minutes_start;

            // console.log("Start : " + event_minutes_start + ' :: End :' + event_minutes_end + ' :: height : ' + height);

            if(event_new_elem_ === 0){
                event_new_elem_ = (new Date()).getTime();
                $(".events-wrapper").append(
                    '<div class="event-short-preview" id="'+event_new_elem_+'" style="top:'+event_minutes_start+'px; height: '+(height)+'"><h4 id="'+event_new_elem_+'-header"> ' + getNewEventTitle() + ' </h4> <p id="'+event_new_elem_+'-time"> ' + getNewEventRange() + ' </p> </div> '
                );
            }else{
                $("#"+event_new_elem_).height(height).css({ top: event_minutes_start +'px' });
                $("#"+event_new_elem_+'-time').text(getNewEventRange());
            }

        }else{time_from = false;}
    }
    if($(this).attr('id') === 'time-to'){
        if(value) {
            time_to = true;
            event_minutes_end = ((parseInt(time[0]) * 60) + parseInt(time[1]));
            $("#"+event_new_elem_).height(event_minutes_end - event_minutes_start);

            $("#"+event_new_elem_+'-time').text(getNewEventRange());
        }
        else {time_to = false;}
    }
});

$("body").on('keyup', '#time-title', function (){
    $("#"+event_new_elem_+'-header').text(getNewEventTitle());
});

$("body").on('click', '.exit-cal-event', function () { // Hide pop-up for event
    $(".add-new-event-wrapper").fadeOut();
    $(".events-wrapper").find("#"+event_new_elem_).remove();
    event_new_elem_ = 0;

    // TODO :: Clean all input fields from event adder
});
$("body").on('click', '.create-cal-event', function () { // Show pop-up for event
    $(".add-new-event-wrapper").fadeIn();
});

$("body").on('click', '.save-event', function () { // Hide pop-up for event
    let title = $("#time-title").val();
    let category = $("#time-category").val();
    let time_from = $("#time-from").val();
    let time_to   = $("#time-to").val();
    let info = $("#info").val();

    if(title === ''){
        $.notify("Naslov ne smije biti prazan!", 'warn');
        return;
    }

    if(!save_data || time_from === '' || time_to === ''){
        $.notify("Datum početka i datum kraja nisu validni, molimo provjerite !", 'warn');
        return;
    }

    $.ajax({
        type:'POST',
        url: 'index.php?sta=ws/predmet',
        data: { event_create: true, event_title : title, event_category : category, event_time_from : time_from, event_time_to : time_to, event_info : info, event_date: event_date},
        success:function(response){

            if(response['success'] === 'true'){
                $.notify("Uspješno ste spasili podatke!", 'success');
                $(".add-new-event-wrapper").fadeOut();

                event_new_elem_ = 0; // Allow new element creation

                // TODO - Potrebno je provjeriti stanje koje ostaje u cache, ukoliko zaglavi te restartovati
                // formu - Pogodno kreirati funkciju za restartovanje forme - brisanje svih podataka, pošto se koristi
                // na više polja
            }else{
                $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
            }

            /* for(let i=0; i<response['data'].length; i++){
                $('#ocjena-po-odluci-pasos').append($('<option>', {
                    value: response['data'][i]['pasos'],
                    text: response['data'][i]['naziv']
                }));
            } */

        }
    });

});
