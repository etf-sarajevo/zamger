// Validate form and draggable elements
let time_from = false, time_to = false, event_new_elem_ = 0, event_minutes_start, event_minutes_end;

let currentDate = new Date();

let save_data = false; // If it is true, time format is fine - make ajax request
let event_date = currentDate.getFullYear() + '-' + (currentDate.getMonth() + 1) + '-' + currentDate.getDate(); // Date for event -- set value on onclick event on calendar day

let api_link = 'index.php?sta=ws/predmet';

let edit_event = false; // If it set to true, api calls method update() otherwise calls add()

/*
 *  Deadline represents minimum time (in hours) for deadline - before event starts --
 *  as default, it is set to 24h (for all categories / event types ) ; Deprecated with function deadlineDateTime
 */

let deadline = {
    1 : 24,
    2 : 24,
    3 : 24,
    4 : 24,
    5 : 24
};

// Parse url and read GET parameters from URL

let getUrlParameter = function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};

/*
 *  Check if date format is valid - valid format is dd.mm.yyyy
 */

let dateValid = function(testDate) {
    let date_regex = /^(0?[1-9]|1\d|2\d|3[01])\.(0?[1-9]|1[0-2])\.(19|20)\d{2}$/ ;
    return date_regex.test(testDate);
};

/*
 *  Set propper format of date yyyy-mm-dd or dd.mm.yyyy
 */

let formatDate = function(date, operator = '-'){
    date = date.split(operator);

    console.log(date);

    if(date[1].length === 1) date[1] = (date[1] < 10) ? ('0' + date[1]) : date[1];                      // Format month 1 -> 01
    if(operator === '-' && date[2].length === 1) date[2] = (date[2] < 10) ? ('0' + date[2]) : date[2];  // Format day yyyy-mm-dd
    else if(date[0].length === 1) date[0] = (date[0] < 10) ? ('0' + date[0]) : date[0];                 // Format day dd.mm.yyyy

    return date[0] + operator + date[1] + operator + date[2];
    if(operator === '-') { return date[0] + operator + date[1] + operator + date[2]; }
    else { return date[2] + '-' + date[1] + '-' + date[0]; }
};

/*
 *  Set propper format of time hh:ii
 */

let formatTime = function(time, dateTime = false){
    time = time.split(':');
    if(time[0].length === 1) time[0] = (time[0] < 10) ? ('0' + time[0]) : time[0];
    if(time[1].length === 1) time[1] = (time[1] < 10) ? ('0' + time[1]) : time[1];

    return time[0] + ':' + time[1];
};

/*
 *  Return time to (from datetime + duration)
 */

let getTimeTo = function(dateTime, duration){
    let cDate    = new Date(dateTime);
    cDate = new Date(cDate.setMinutes(cDate.getMinutes() + duration));

    return cDate.getHours() + ':' + cDate.getMinutes();
};

/*
 *  Returns difference in minutes between start and end of event
 *  Needs for calculation of event duration
 */

let diffInMinutes = function(timeFrom, timeTo){
    /*
     *  Get an two elements array from hh:mm
     */
    timeFrom = timeFrom.split(':');
    timeTo   = timeTo.split(':');

    /*
     *  Convert to minutes
     */
    let timeFromMinutes = ((parseInt(timeFrom[0]) * 60) + parseInt(timeFrom[1]));
    let timeToMinutes   = ((parseInt(timeTo[0]) * 60) + parseInt(timeTo[1]));

    if(timeToMinutes <= timeFromMinutes){
        return {
            code : '4004',
            'message' : 'Vrijeme predviđeno za početak ne smije biti veće od vremena predviđenog za kraj!'
        };
    }else{
        return {
            code : '0000',
            message : 'Uspješno izračunato !',
            data : (parseInt(timeToMinutes) - parseInt(timeFromMinutes))
        };
    }
};

/*
 *  This function is used to create date-time format from date and time; adds extra seconds as '00'
 */
let formatDateTime = function(date, time){ return formatDate(date) + ' ' + formatTime(time) + ':00'; };

/*
 *  Creates deadline (in date-time format) depending on predefined values in let deadline variable
 *  This function is deprecated and probably wont be ever used!
 */
let deadlineDateTime = function(dateTime, eventType = 'default'){
    let cDate = new Date(dateTime);
    let days  = (eventType === 'default') ? 1 : parseInt(parseInt(deadline[eventType]) / 24);

    // Now, substract number of days from date
    cDate.setDate(cDate.getDate() - days);

    return formatDate(cDate.getFullYear() + '-' + (cDate.getMonth() + 1) + '-' + cDate.getDate()) + ' ' + formatTime(cDate.getHours() + ':' + cDate.getMinutes()) + ':00';
};

let getPhpYear  = function(date){ return new Date(date).getFullYear(); };
let getPhpMonth = function(date){ return (new Date(date).getMonth() + 1) ; };
let getPhpDay   = function(date){ return new Date(date).getDate() ; };

/*
 *  Get minutes from date-time format (this is required to setup exact position on day preview
 *  Get year-month-day from date-time format
 */

let extractTime = function(dateTime, extractMinutes = false){
    let cDate = new Date(dateTime);
    let cTime = (cDate.getHours() + ':' + cDate.getMinutes())

    if(extractMinutes){
        cTime = (cDate.getHours() * 60) + cDate.getMinutes();
    }else{ cTime = formatTime(cTime); }
    return cTime;
};
let extractDate = function(dateTime, engFormat = false){
    let cDate = new Date(dateTime);
    if(engFormat){
        return cDate.getFullYear() + '-' + (cDate.getMonth() + 1) + '-' + cDate.getDate();
    }else{
        return cDate.getDate() + '.' + (cDate.getMonth() + 1) + '.' + cDate.getFullYear();
    }
};

let initialData = function(){
    $("#time-title").val('');
    $("#time-category").val(2);
    $("#event-date").val(((currentDate.getDate() < 10) ? '0' + currentDate.getDate() : currentDate.getDate()) + '.' + (((currentDate.getMonth() + 1) < 10) ? '0' + (currentDate.getMonth() + 1) : (currentDate.getMonth() + 1)) + '.' + currentDate.getFullYear());
    $("#repeat").val(1);
    $("#time-from").val('');
    $("#time-to").val('');
    $("#info").val('');

    deadlineData();
};

// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ //
/*
 *  Complete calendar object - fully functional
 */

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
    d_today : new Date(),

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
        let value = this.createBody();

        // this.createSingleDay();
    },

    getCalendarContent : function (){

        let month = ((this.n_month === null) ? this.currentMonth() : this.n_month) + 1;
        let year  = (this.n_year === null) ? this.currentYear() : this.n_year;

        let uri = 'event/course/'+getUrlParameter('predmet')+'/'+getUrlParameter('ag')+'/month&myear='+year+'&month='+month;

        ajax_api_start(uri, 'GET', {}, function (result) {
            for(let i=0; i<result['results'].length; i++){
                let event = result['results'][i];

                $(".current-value[day="+getPhpDay(event['dateTime'])+"]").find('.cv-events').append(function () {
                    return $("<div>").attr('class', 'cv-e-event  cv-e-event-' + event['EventType'])
                        .append($("<h5>").text(event['title']));
                });
            }
        }, function (text, status, url) {
            $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
        });
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
                let day_t = '';
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

                    day_t = (this.d_today.getDate() === day && this.d_today.getMonth() === month && this.d_today.getFullYear() === year) ? ' (Danas)' : '';
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

                col += '<div class="calendar-col ' + class_name + '" year="' + year + '" month="' + month + '" day="' + day + '"><p>' + (day + day_t) + '</p> <div class="cv-events"></div> </div>';
            }

            // style="top: -'+ (i + 1)*5 +'px !important;"
            row += '<div class="calendar-row">' + col + '</div>';
            if (days_counter > this.monthDuration()) break;
        }

        $(this.wrapper).append('<div class="calendar-body dynamic-body">' + row + '</div>');

        // Finally, fill calendar days with events
        this.getCalendarContent();
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

        if(isToday(event_date)){
            $(".events-wrapper").append(
                '<div class="current-time-line" style="top:'+(top_time)+'px" title="Trenutno vrijeme '+current_h+':'+current_m+'">  </div>'
            );
        }
    },
    removeSingleDay : function () {
        $(this.wrapper).find(".full-day-preview").remove();
        $(".add-new-event-wrapper").fadeOut();
    }
};

/*
 *  Go for next, previous or current month
 */

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
// ** Preview only one day -- data from calendar ** //

let isToday = function(date){
    date = date.split('-');
    let y = date[0]; let m = date[1]; let d = date[2];

    let today = new Date();

    if(today.getFullYear() === parseInt(y) && (today.getMonth() === (parseInt(m) - 1)) && (today.getDate() === parseInt(d))) return true;
    return false;
};
let dayData = function(date){

    let uri = 'event/course/'+getUrlParameter('predmet')+'/'+getUrlParameter('ag')+'/date&date='+formatDate(event_date);

    ajax_api_start(uri, 'GET', {}, function (result) {
        if(isToday(event_date)) {
            $(".items-wrapper").empty();
            $(".this-day-total").text(0);
        }
        let scrollPos = 1444;

        for(let i=0; i<result['results'].length; i++){
            let event = result['results'][i];

            event_minutes_start = extractTime(event['dateTime'], true);
            event_minutes_end   = event_minutes_start + event['duration'];

            let height = event_minutes_end - event_minutes_start;

            $(".events-wrapper").append(
                '<div class="event-short-preview event-short-preview-'+event['EventType']+'" title="' + event['title'] + '" id="event-elem-'+event['id']+'" style="top:'+event_minutes_start+'px; height: '+(height)+'px"><h4 id="'+event['id']+'-header"> ' +event['title'] + ' </h4> <p id="'+event_new_elem_+'-time"> ' + (extractTime(event['dateTime']) + ' : ' + formatTime(getTimeTo(event['dateTime'], event['duration']))) + ' </p> <div class="event-actions"> <div class="ea-d" event-id="'+ event['id'] +'" title="Obrišite događaj"><i class="fas fa-trash"></i></div> <div class="ea-u ml-1" event-id="'+ event['id'] +'" title="Uredite"><i class="fas fa-edit"></i></div> </div> </div> '
            );

            if(isToday(event_date)){
                $(".this-day-total").text(parseInt($(".this-day-total").text()) + 1);
                // Check if date is current date; If it is, add to side menu
                $(".items-wrapper").append(function () {
                    return $("<div>").attr('class', 'single-item sci-d')
                        .attr('year', (new Date()).getFullYear())
                        .attr('month', (new Date()).getMonth())
                        .attr('day', (new Date()).getDate())
                        .attr('title', event['title'] + '\u000d' + event['description'])
                        .attr('event-id', 'event-elem-' + event['id'])
                        .append(function () {
                            return $("<p>").text(extractTime(event['dateTime']) + ' : ' + formatTime(getTimeTo(event['dateTime'], event['duration'])));
                        })
                        .append(function () {
                            return $("<span>").text(event['title'])
                        })
                });
            }

            if(scrollPos > event_minutes_start) scrollPos = parseInt(event_minutes_start);
        }

        /*
         *  If there is no data - scrol to 08:00 h
         */

        if(result['results'].length === 0){
            scrollPos = 480;
        }else{
            scrollPos = (scrollPos > 60) ? (scrollPos - 60) : scrollPos;
        }

        $(".single-day-body").animate({ scrollTop : scrollPos }, 500); // Set animation duration to 0.2 s

    }, function (text, status, url) {
        $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
    });
};
let showSingleDay = function(day, month, year){
    let date = new Date(year + '-' + month + '-' + day);
    calendar.d_day_in_week = calendar.week_days[date.getDay()];
    calendar.d_date = day + '. ' + calendar.months_name[month - 1] + ' ' + year;

    // Get date for clicked "day"
    event_date = year + '-' + month + '-' + day;

    // First, check if there is any data for this particular day
    let response = dayData(event_date);

    calendar.createSingleDay();
};

$("body").on('click', '.calendar-col, .sci-d', function () {
    let day   = $(this).attr('day');
    let month = (parseInt($(this).attr('month')) + 1);
    let year  = $(this).attr('year');

    showSingleDay(day, month, year);
});
$("body").on('click', '.back-to-full-calendar', function () {
    // Back to full calendar - reaload event_date

    calendar.createCalendar();
    calendar.removeSingleDay();
});

/*
 *  Make an input form draggable over area of calendar (inside wrapper)
 */

$( function() {
    $( ".day-form" ).draggable({ containment: "parent" });
} );

function validateHhMm(id, force = false) {
    let element = $("#" + id);
    let isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(element.val());

    if (isValid) {
        element.css("background-color", '#fff');
        if(!force) save_data = true;
    }
    else {
        element.css("background-color", '#fba');
        if(!force) save_data = true;
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
    let value     = validateHhMm($(this).attr('id'));
    let time      = $(this).val(); time = time.split(':');
    let scrollVar = 0;

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

            if(event_new_elem_ === 0){
                event_new_elem_ = (new Date()).getTime();
                $(".events-wrapper").append(
                    '<div class="event-short-preview event-short-preview-'+$("#time-category").val()+'" id="'+event_new_elem_+'" style="top:'+event_minutes_start+'px; height: '+(height)+'"><h4 id="'+event_new_elem_+'-header"> ' + getNewEventTitle() + ' </h4> <p id="'+event_new_elem_+'-time"> ' + getNewEventRange() + ' </p> </div> '
                );
            }else{
                let elem_id = (edit_event) ? ('event-elem-' + event_new_elem_) : event_new_elem_;

                $("#"+elem_id).height(height).css({ top: event_minutes_start +'px' });
                $("#"+elem_id+'-time').text(getNewEventRange());
            }

            // Now, if time from is valid, scroll to element with this time
            if(parseInt(time[0]) >= 1){
                scrollVar = (parseInt(time[0]) * 60) - 60;
            }

            $(".single-day-body").animate({
                scrollTop : scrollVar
            }, 500);

        }else{time_from = false;}
    }
    if($(this).attr('id') === 'time-to'){
        if(value) {
            time_to = true;
            event_minutes_end = ((parseInt(time[0]) * 60) + parseInt(time[1]));

            let elem_id = (edit_event) ? ('event-elem-' + event_new_elem_) : event_new_elem_;

            $("#"+elem_id).height(event_minutes_end - event_minutes_start);

            $("#"+elem_id+'-time').text(getNewEventRange());
        }
        else {time_to = false;}
    }
});

/*
 *  When date is changed:
 *      - Perform date validation
 *      - Set event_date as picked date (YYYY-MM-DD)
 *      - Remove data for previous day
 *      - Read data for new date and show it to user
 */
$("body").on('change', '#event-date', function () {
    let value = $(this).val();
    if(!dateValid(value)){ // In case someone trie to be smart :D
        value = currentDate.getDate() + '.' + (currentDate.getMonth() + 1) + '.' + currentDate.getFullYear();
        $(this).val(value);
    }

    let fromDatepicker = value.split('.');

    event_date = fromDatepicker[2] + '-' + fromDatepicker[1] + '-' + fromDatepicker[0];

    calendar.removeSingleDay();
    showSingleDay(fromDatepicker[0], parseInt(fromDatepicker[1]), parseInt(fromDatepicker[2]));

    $(".add-new-event-wrapper").fadeIn();

    event_new_elem_ = 0;
    $("#time-from").val('');
    $("#time-to").val('');
});

/*
 *  On change category, change background depending on defined values inside calendar.css
 *
 *      event-elem-1 (category 1), event-elem-2 (category 2) etc.
 */
$("body").on('change', '#time-category', function () {
    let elem_id = (edit_event) ? ('event-elem-' + event_new_elem_) : event_new_elem_;

    $("#"+elem_id).attr('class', 'event-short-preview event-short-preview-' + $(this).val());
});

/*
 *  Update title on newly created element inside day preview
 */
$("body").on('keyup', '#time-title', function (){
    $("#"+event_new_elem_+'-header').text(getNewEventTitle());
});

/*
 *  Add new event, only for today -- set date to todays date; closes day preview (full month preview) + form open
 */

$("body").on('click', '.add-new-today', function () { // Open form and set event_date as today's date
    let today = new Date();
    event_date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
    calendar.removeSingleDay();

    initialData(); // Set everything to default

    // Open form window
    $(".add-new-event-wrapper").fadeIn();
});

/*
 *  createEventRequest is used to generate request, depending on url and method;
 *
 *  All params sent for create / update are built in here
 */

let createEventRequest = function(url, method, edit = false){
    let title       = $("#time-title").val();
    let eventType   = $("#time-category").val();
    let timeFrom    = $("#time-from").val();
    let timeTo      = $("#time-to").val();
    let repeat      = $("#repeat").val();
    let description = $("#info").val();
    let students    = parseInt($("#allow-students").val());
    let maxStudents = parseInt($("#maxStudents").val());
    let deadDate    = $("#deadline-date").val();
    let deadTime    = $("#deadline-time").val();
    let subject     = getUrlParameter('predmet'); // Get subject from URI
    let ac_year     = getUrlParameter('ag');      // Get academic year from URI
    let deadline    = '';

    maxStudents = (isNaN(maxStudents)) ? 0 : maxStudents; // If it is empty, make it 0 (zero)

    if(title === ''){
        $.notify("Naslov ne smije biti prazan!", 'warn');
        return;
    }
    if(!save_data || timeFrom === '' || timeTo === ''){
        $.notify("Vrijeme početka i vrijeme kraja nisu validni, molimo provjerite !", 'warn');
        return;
    }
    let duration = diffInMinutes(timeFrom, timeTo); // Calculate duration of event
    if(duration['code'] !== '0000'){
        $.notify(duration['message'], 'warn');
        return;
    }

    /** Format date and start time to dateTime **/
    let dateTime = formatDateTime(event_date, timeFrom);       // Event start date and time -- format from event_date and start time
    // let deadline = deadlineDateTime(dateTime, eventType);   // Creates deadline according to eventType -- see deadline variable; Deprecated

    /** If students allowed **/
    if(students === 2){ // We need to perform extra checks for date and time format
        if(!dateValid(deadDate) || deadDate === ''){
            $.notify("Datum roka za prijavu nije validan!", 'warn');
            return;
        }
        if(!validateHhMm('deadline-time', true) || deadTime === ''){
            $.notify("Vrijeme roka za prijavu nije validno! Validan format je HH:MM", 'warn');
            return;
        }

        deadDate = deadDate.split('.');
        deadDate = deadDate[2] + '-' + deadDate[1] + '-' + deadDate[0];
        deadDate = formatDate(deadDate);

        deadline = formatDateTime(deadDate, deadTime);       // Event start date and time -- format from event_date and start time

        if((new Date(dateTime)) < (new Date(deadline))){
            $.notify("Rok prijave ne smije biti poslije događaja!", 'warn');
            return;
        }
    }else{
        deadline = deadlineDateTime(dateTime);
    }

    let params = {
        CourseUnit : { id : subject },
        AcademicYear : { id : ac_year },
        EventType : eventType,
        dateTime : dateTime,
        maxStudents : maxStudents,
        duration : duration['data'],
        deadline : deadline,
        CourseActivity : { id : null }, // this param is empty for now
        options : '',
        title : title,
        description : description,
        repeat : repeat
    };

    if(edit_event) params.id = edit_event;

    ajax_api_start(url, method, params, function (result) {
        $.notify("Uspješno ste spasili podatke!", 'success');
        $(".add-new-event-wrapper").fadeOut();

        event_new_elem_ = 0; // Allow new element creation
        edit_event      = false; // Stop edditig an element

        calendar.removeSingleDay();

        let response = dayData(event_date);
        calendar.createSingleDay();

        // Remove all previously entered data
        initialData();

    }, function (text, status, url) {
        // console.log("Došlo je do greške na serveru.");
        // console.error("Kod: " + status);
        text = JSON.parse(text);
        $.notify(text['message'], 'error');
    });
};

/*
 *  Create new event
 */

$("body").on('click', '.save-event', function () { // Hide pop-up for event
    if(edit_event){
        // if edit_event != false, it has some id from previous response
        let uri = 'event/' + edit_event;
        createEventRequest(uri, 'PUT', edit_event);
    }else{
        let uri = 'event/course/'+getUrlParameter('predmet')+'/'+getUrlParameter('ag')+'';
        createEventRequest(uri, 'POST');
    }
});

/*
 *  Remove event from database + remove from preview
 */

$("body").on('click', '.ea-d', function () {
    // remove from list of events
    let event_id = $(this).attr('event-id');

    ajax_api_start('event/'+event_id, 'DELETE', {}, function (result) {
    }, function (text, status, url) {
        if(status === 204){
            calendar.removeSingleDay();

            let response = dayData(event_date);
            calendar.createSingleDay();

            // Now, check if date is today -- if it is, then remove from "side menu"
            if(isToday(event_date)){
                $("#event-elem-" + event_id).remove();
            }
        }else {$.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');}
    });
});

/*
 *  Edit event - almost the same as create one
 */

$("body").on('click', '.ea-u', function () {
    let event_id = $(this).attr('event-id');

    ajax_api_start('event/'+event_id, 'GET', {}, function (result) {
        $(".add-new-event-wrapper").fadeIn();

        $("#time-title").val(result['title']);
        $("#time-category").val(result['EventType']);
        $("#time-from").val(extractTime(result['dateTime']));
        $("#time-to").val(formatTime(getTimeTo(result['dateTime'], result['duration'])));
        $("#event-date").val(formatDate(extractDate(result['dateTime']), '.'));
        $("#repeat").val((result['repeat'] === undefined) ? 1 : result['repeat']); // Till implementation
        $("#info").val(result['description']);

        if(parseInt(result['maxStudents']) !== 0){
            $(".deadline-data").fadeIn();

            $("#allow-students").val('2');
            $("#maxStudents").val(result['maxStudents']);
            $("#deadline-date").val(formatDate(extractDate(result['deadline']), '.'));
            $("#deadline-time").val(extractTime(result['deadline']));
        }

        save_data       = true; // Make it available to save instantly
        edit_event      = result['id'];
        event_new_elem_ = result['id']; // Need it for moving created element in day preview

    }, function (text, status, url) {
        $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
    });
});


/*
 *  Onclick show / hide DOM elements:
 *      - popup for event handler
 *      - popup elements manipulations
 */

$("body").on('click', '.exit-cal-event', function () { // Hide pop-up for event
    $(".add-new-event-wrapper").fadeOut();
    $(".events-wrapper").find("#"+event_new_elem_).remove(); // Remove created event inside day preview

    event_new_elem_ = 0; // Not inserting new event
    edit_event = false;  // Not editing previous event

    initialData(); // Default
});

$("body").on('click', '.create-cal-event', function () {
    $(".add-new-event-wrapper").fadeIn();

    // Set date as clicked
    let datePrevious = event_date.split('-');

    $("#event-date").val(((parseInt(datePrevious[2]) < 10) ? '0' + parseInt(datePrevious[2]) : datePrevious[2]) + '.' + ((parseInt(datePrevious[1]) < 10) ? '0' + parseInt(datePrevious[1]) : datePrevious[1]) + '.' + datePrevious[0]);
});

/*
 *  Set everything to default
 */

let deadlineData = function(){
    $(".deadline-data").fadeOut();

    $("#maxStudents").val(0);
    $("#deadline-date").val('');
    $("#deadline-time").val('');
    $("#allow-students").val('1');
};

$("body").on('change', '#allow-students', function () {
    if(parseInt($(this).val()) === 1){
        deadlineData();
    }else{
        $(".deadline-data").fadeIn();

        let eventDate = $("#event-date").val();

        if(dateValid(eventDate) && eventDate !== ''){
            eventDate = eventDate.split('.');
            eventDate = eventDate[2] + '-' + eventDate[1] + '-' + eventDate[0];
            let cDate = new Date(eventDate);

            console.log(cDate, eventDate);
            cDate.setDate(cDate.getDate() - 1);

            $("#deadline-date").val(cDate.getDate() + '.' + (cDate.getMonth() + 1) + '.' + cDate.getFullYear());
        }else console.log("Not valid !" + $("#eventDate").val());
    }
});
