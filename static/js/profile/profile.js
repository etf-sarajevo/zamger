
/*
 *  USER PROFILE
 *
 *  In this part, user can update profile information, add new email account, remove or edit (multiple)
 *
 *  Extended person is also used
 */

$(document).ready(function () {

    let update_link = 'index.php?sta=ws/api_links'; // TODO - set this link!
    let newPlace    = ''; // TODO - update link

    function validateDate(testdate) {
        var date_regex = /^(0?[1-9]|1\d|2\d|3[01])\.(0?[1-9]|1[0-2])\.(19|20)\d{2}$/ ;
        return date_regex.test(testdate);
    }

    /*
     *  Max length of JMB is 13 -- remove everything after 13
     */
    $("#jmbg").keyup(function (){
        let value = $(this).val();
        let realJmb = value.substring(0, (value.length >= 13) ? 13 : value.length);
        $(this).val(realJmb);
    });
    /*
     *  Construct an email object from 3 arrays (email address, email ID and account address)
     */
    let constructEmail = function(){
        let emails = $("input[name='email[]']").map(function(){return $(this).val();}).get();
        let emailID = $("input[name='email_id[]']").map(function(){return $(this).val();}).get();
        let emailAA = $("input[name='acc_addr[]']").map(function(){return $(this).val();}).get();

        let email = [];

        for(let i=0; i<emails.length; i++){
            email[i] = {
                id : emailID[i],
                address : emails[i],
                account_address : emailAA[i]
            };
        }

        console.log(email);

        return email;
    };

    /*
     *  Submit form
     */
    $('#update-profile').on('submit', function(e){

        let personId = $("#personId").val();
        let jmb      = $("#jmbg").val();
        let dateOfBirth = $("#dateOfBirth").val();
        let name     = $("#name").val();
        let surname  = $("#surname").val();

        /*
         *  Form validations
         */

        if(jmb.length !== 13){
            $.notify("Jedinstveni matični broj nije validan!", 'warn');
            e.preventDefault();
            return;
        }
        if(!validateDate(dateOfBirth)){
            $.notify("Datum rođenja nije validan!", 'warn');
            e.preventDefault();
            return;
        }
        if (name.indexOf(surname) >= 0) {
            $("#name").focus();
            $.notify("Unijeli ste prezime u polje za ime!", 'warn');
            e.preventDefault();
            return;
        }

        dateOfBirth = dateOfBirth.split('.');

        console.log(dateOfBirth[2] + '-' + dateOfBirth[1] + '-' + dateOfBirth[0]);

        /*
         *  Params in object form
         */

        let params = {
            id:                   personId,                                  // Person ID
            name:                 $("#name").val(),
            surname:              $("#surname").val(),
            studentIdNr:          $("#studentIdNr").val(),                   // Broj indexa - string
            email:                        constructEmail(),                  // TODO - napravi funkciju za kreiranje mail objekta

            /** Extended person **/
            ExtendedPerson: {
                jmbg:              $("#jmbg").val(),                         // TODO - Provjeriti prije UPDATE-a
                sex:               $("#sex").val(),

                /** TODO - Kako ćemo slati ovo !? Da li šaljemo samo placeOfBirth, ili !? **/
                /** Place of birth **/
                placeOfBirth: {                                              // Mjesto rođenja
                    id: $("#placeOfBirthID").val(),                          // ID mjesta rođenja
                    name: $("#placeOfBirth").val(),                          // Naziv mjesta rođenja
                    Municipality : {
                        id: $("#MunicipalityID").val(),                        // ID općine rođenja
                        name: $("#Municipality").val()                     // Naziv općine rođenja
                    },
                    Country : {
                        id: $("#Country").val(),                             // ID države rođenja
                    }
                },
                nationality:              $("#nationality").val(),           // Državljanstvo
                ethnicity:                $("#ethnicity").val(),             // Nacionalnost

                /** Date of birth - from dd.mm.yyyy => yyyy-mm-dd **/
                dateOfBirth:          dateOfBirth[2] + '-' + dateOfBirth[1] + '-' + dateOfBirth[0],

                /** TODO - provjeriti za adresu prebivališta, općinu, kanton (ako je BiH) i državu **/
                residenceAddress:              $("#residenceAddress").val(),    // Adresa prebivališta
                residencePlace: {                                               // Mjesto rođenja
                    id: $("#residencePlaceID").val(),                           // ID mjesta rođenja
                    name: $("#residencePlace").val(),                           // Naziv mjesta rođenja
                    Municipality : {
                        id: $("#residenceMunicipalityID").val(),                  // ID općine rođenja
                        name: $("#residenceMunicipality").val()               // Naziv općine rođenja
                    },
                    Country : {
                        id: $("#residenceCountry").val(),                       // ID države rođenja
                    }
                },

                /** Parent informations **/
                fathersName:                 $("#fathersName").val(),        // Ime oca
                fathersSurname:              $("#fathersSurname").val(),     // Prezime oca
                mothersName:                 $("#mothersName").val(),        // Ime majke
                mothersSurname:              $("#mothersSurname").val(),     // Prezime majke

                /** Adresa i mjesto boravišta **/
                addressStreetNo:              $("#addressStreetNo").val(),   // Adresa boravišta
                addressPlace: {                                              // Mjesto rođenja
                    id: $("#addressPlaceID").val(),                          // ID mjesta rođenja
                    name: $("#addressPlace").val(),                          // Naziv mjesta rođenja
                    Municipality : {
                        id: $("#addressMunicipalityID").val(),                 // ID općine rođenja
                        name: $("#addressMunicipality").val()              // Naziv općine rođenja
                    },
                    Country : {
                        id: $("#addressCountry").val(),                      // ID države rođenja
                    }
                },

                /** Kontakt informacije **/
                phone:                        $("#phone").val(),             // Kontakt telefon

                /** Previous education **/
                previousEducation: [ ],

                /** Rest of data **/
                sourceOfFunding:              $("#sourceOfFunding").val(),        // Izvori finansiranja studenta
                activityStatusParent:         $("#activityStatusParent").val(),   // Status aktivnosti roditelja
                activityStatusStudent:        $("#activityStatusStudent").val(),  // Status aktivnosti studenta
                occupationParent:             $("#occupationParent").val(),       // Zanimanje roditelja
                occupationStudent:            $("#occupationStudent").val(),      // Zanimanje studenta
                employmentStatusParent:       $("#employmentStatusParent").val(), // Status zaposlenja roditelja
                employmentStatusStudent:      $("employmentStatusStudent").val(), // Status zaposlenja studenta
            }
        };

        if ($("#skola").val() > 0) {
            let previousEducation = {
                School:     {
                    id: $("#skola").val()
                },
                yearCompleted:  {
                    id: $("#godina_zavrsetka").val()
                }
            };
            params.ExtendedPerson.previousEducation.push( previousEducation );
        }
        console.log(params);
        // e.preventDefault();
        //
        // return;

        ajax_api_start('person/'+personId, 'PUT', params, function (result) {
            console.log(result);
            $.notify("Zahtjev je uspješno poslan i biće pregledan ubrzo", "success");
        }, function (text, status, url) {
            try {
                var obj = JSON.parse(text);
                $.notify("Greška: " + obj.message, 'error');
            } catch(e) {
                $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
            }
        });

        e.preventDefault();
    });


    /*
     *  Append more email accounts - creates an form group with email input
     */

    let email_accounts = 1; // Init number of email accounts ;

    let emailDOMwidth = function(){
        email_accounts = $(".sm-emails").length;

        $(".sm-emails").each(function () {
            $(this).parent().parent().attr('class', 'col-md-6');
            if((email_accounts % 2) === 0 && parseInt($(this).attr('no')) === email_accounts) $(this).parent().parent().attr('class', 'col-md-12');
        });
    };

    $(".append-email").click(function () {

        email_accounts = $(".sm-emails").length + 1;

        $(".email-wrapper").append(function () {
            return $("<div>").attr('class', 'col-md-6')
                .append(function () {
                    return $("<div>").attr('class', 'form-group')
                        .append(function () {
                            return $("<label>").attr('for', 'email-' + email_accounts)
                                .text("Email");
                        })
                        .append(function () {
                            return $('<input type="email">').attr({class:'form-control sm-emails', id:'email-' + email_accounts, name:'email[]', no : email_accounts});
                        })
                        .append(function () {
                            return $('<input type="hidden">').attr({class:'form-control sm-emails-id', name:'email_id[]', value:'0'});
                        })
                        .append(function () {
                            return $('<input type="hidden">').attr({class:'form-control sm-emails-accaddr', name:'acc_addr[]', value:'0'});
                        })
                        .append(function () {
                            return $("<small>").attr('class', 'form-text text-muted remove-email')
                                .text('Ukoliko ste greškom dodali email, možete ga obrisati ovdje.');
                        });
                });
        });

        emailDOMwidth();
    });

    /*
     *  Remove an email (DOM)
     */

    $("body").on('click', '.remove-email', function () {
        if($(this).hasClass('remove-email-db')){

            /*
             *  Remove an email - from DB
             */

            let email_id = $(this).attr('id');

            // $.ajax({
            //     type:'POST',
            //     url: update_link,
            //     data: {
            //         'remove_email' : true,
            //         'id' : email_id
            //     },
            //     success:function(response){
            //         if(response['success'] === 'true'){
            //             $.notify(response['message'], 'success');
            //         }else{
            //             $.notify(response['message'], 'success');
            //         }
            //     }
            // });
            $(this).parent().parent().parent().remove();
        }else{
            $(this).parent().parent().remove();
        }



        let counter = 1;
        $(".sm-emails").each(function () {
            $(this).attr('no', counter++);
        });
        emailDOMwidth();
    });

    /*
     *  New country, canton, municipality and place -- check and save
     */

    let placeType = '';

    $("body").on('change', '#newCountry', function () {
        console.log(parseInt($(this).val()));
        if(parseInt($(this).val()) === 1){
            $(".newMunicSelW").attr('class', 'col-md-6 newMunicSelW');
            $(".newMunicTextW").attr('class', 'col-md-6 newMunicTextW d-none');
        }else{
            $(".newMunicSelW").attr('class', 'col-md-6 newMunicSelW d-none');
            $(".newMunicTextW").attr('class', 'col-md-6 newMunicTextW');
        }
    })
    $("body").on('click', '.saveNewPlace', function () {
        let country      = $("#newCountry").val();
        let municipality = (parseInt(country) === 1) ? $("#newMunicSel").val() : $("#newMunicText").val();
        let place        = $("#newPlace").val();


        if(country === '' || municipality === '' || place === ''){
            $.notify("Molimo da popunite sva polja!", 'warn');
            return;
        }

        /*
         *  First, let's find input with given placeType
         */
        let mainInput = $("#" + placeType);

        // Set place
        mainInput.val(place).attr('initname', place);
        $("#" + placeType + 'ID').val(0).attr('initvalue', 0);

        let newMunicipality = mainInput.attr('municipality');
        if(newMunicipality !== undefined){
            if(parseInt(country) === 1){ // It is Bosnia and Herzegovinia, go for select
                $("#" + newMunicipality).val($("#newMunicSel").find('option:selected').text());
                $("#" + newMunicipality + 'ID').val($("#newMunicSel").val());
            }else{ // Go as text
                $("#" + newMunicipality).val(municipality);
                $("#" + newMunicipality + 'ID').val(0);
            }
        }

        let newCountry = mainInput.attr('country');
        if(newCountry !== undefined){
            $("#" + newCountry).val(country);
        }

        $("#placeInsert").modal('hide');
    });

    /*
     *  Open popup with specified ID
     */

    $(".insert-place").click(function () {
        $("#placeInsert").modal('show');

        placeType = $(this).attr('idFor');
    });

    /*
     *  Download an SV-20 document
     */

    $(".download-sv-20").click(function () {
        $.ajax({
            type:'POST',
            url: update_link,
            data: {
                download_sv_20 : true
            },
            success:function(response){

                console.log(response);

                return;

                if(response['success'] === 'true'){

                }else{
                    $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
                }
            }
        });
    });

    /******************************************************************************************************************/
    /*
     *  Include additional scripts
     */
    import('./places-search.js');
});
