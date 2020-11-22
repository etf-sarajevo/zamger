// Validate form and draggable elements
let time_from = false, time_to = false, event_new_elem_ = 0, event_minutes_start, event_minutes_end;

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

$("body").on('click', '.calendar-col', function () {
    let date = new Date($(this).attr('year') + '-' + (parseInt($(this).attr('month')) + 1) + '-' + $(this).attr('day'));
    calendar.d_day_in_week = calendar.week_days[date.getDay()];
    calendar.d_date = $(this).attr('day') + '. ' + calendar.months_name[$(this).attr('month')] + ' ' + $(this).attr('year');

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

    if (isValid) element.css("background-color", '#fff');
    else element.css("background-color", '#fba');

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
