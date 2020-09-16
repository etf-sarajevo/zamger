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
        console.log(this.calendar_body);
        // vars.wrapper.contents(':not(#add-new-absence)').remove();

        // Set dates as we want it - initially it uses custom date
        this.setDates();

        // Let's start with building GUI
        this.createHeader();
        this.createBody();
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

let calendaree = function(options) {


    let createHeader = function () {
        vars.wrapper.append(
            '<div id="calendar-header"> <div class="calendar-header-left-buttons"> <div class="calendar-button calendar-previous"> <p>' + vars.buttons[0] + '</p> </div> <div class="calendar-button calendar-current"> <p>' + vars.buttons[1] + '</p> </div> <div class="calendar-button calendar-next"> <p>' + vars.buttons[2] + '</p> </div> </div> <div class="calendar-current-month"> <p class="calendar-current-month-val">' + vars.months_name[vars.month] + ' ' + vars.year + '</p> </div> <div class="calendar-header-right-buttons"> <div class="calendar-button"> <p>' + vars.buttons[3] + '</p> </div> <div class="calendar-button add-new-absence"> <p>' + vars.buttons[4] + '</p> </div> </div> </div>'
        );
        let week_days = '';
        for (let i = 0; i < vars.week_days.length; i++) {
            if (window.innerWidth > 1000) {
                week_days += '<div class="calendar-week-day"> <p> ' + vars.week_days[i] + ' </p> </div>';
            } else {
                week_days += '<div class="calendar-week-day"> <p> ' + vars.week_short[i] + ' </p> </div>';
            }

        }
        vars.wrapper.append('<div id="calendar-week-days">' + week_days + '</div>');
        //let week_days = '<div id="calendar-header"> </div>';
    };
};

