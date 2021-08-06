/*
 *  Session ID
 *
 *  Get session ID from cookie - at login, system calls api/route?auth with username + password;
 *  Response is hash sid
 */


let sessionID = function(name) {
    let match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) {
        return match[2];
    }
    else{ console.log('--something went wrong---'); }
};



/*
 *  USER PROFILE
 *
 *  In this part, user can update profile information, add new email account, remove or edit (multiple)
 *
 *  Extended person is also used
 */

$(document).ready(function () {

    let update_link = 'index.php?sta=ws/api_links'; // TODO - set this link!

    function validateDate(testdate) {
        var date_regex = /^(0?[1-9]|1\d|2\d|3[01])\.(0?[1-9]|1[0-2])\.(19|20)\d{2}$/ ;
        return date_regex.test(testdate);
    }

    $('#update-profile').on('submit', function(e){

        /** Osnovne informacije **/
        let ime             = $("#ime").val();
        let prezime         = $("#prezime").val();
        let brindexa        = $("#brindexa").val();
        let jmbg            = $("#jmbg").val();
        let spol            = $("#spol").val();
        let mjesto_rodjenja = $("#mjesto_rodjenja").val();
        let opcina_rodjenja = $("#opcina_rodjenja").val();
        let drzava_rodjenja = $("#drzava_rodjenja").val();
        let datum_rodjenja  = $("#datum_rodjenja").val();
        let drzavljanstvo   = $("#drzavljanstvo").val();
        let nacionalnost    = $("#nacionalnost").val();
        /** Prebivalšte studenta **/
        let drzava_preb     = $("#drzava_preb").val();
        let kanton_preb     = $("#kanton_preb").val();
        let opcina_preb     = $("#opcina_preb").val();
        let adresa_preb     = $("#adresa_preb").val();
        /** Info o roditeljima **/
        let imeoca          = $("#imeoca").val();
        let prezimeoca      = $("#prezimeoca").val();
        let imemajke        = $("#imemajke").val();
        let prezimemajke    = $("#prezimemajke").val();
        /** Boravište studenta **/
        let adresa          = $("#adresa").val();
        let adresa_mjesto   = $("#adresa_mjesto").val();
        let telefon         = $("#telefon").val();
        let email           = $("input[name='email[]']").map(function(){return $(this).val();}).get();
        let email_id        = $("input[name='email_id[]']").map(function(){return $(this).val();}).get();  // In addition to an email
        /** Srednja škola **/
        let naziv           = $("#naziv").val();
        let godina          = $("#godina").val();
        let opcina          = $("#opcina").val();
        let tipskole        = $("#tipskole").val();
        let domaca          = $("#domaca").val();
        /** Ostale informacije **/
        let izvori_finan    = $("#izvori_finan").val();
        let status_a_r      = $("#status_a_r").val();
        let status_a_s      = $("status_a_s").val();
        let zanimanje_r     = $("#zanimanje_r").val();
        let zanimanje_s     = $("#zanimanje_s").val();
        let status_z_r      = $("#status_z_r").val();
        let status_z_s      = $("#status_z_s").val();

        $.ajax({
            type:'POST',
            url: update_link,
            data: {
                osoba_azuriraj  : true,
                /** Osnovne informacije **/
                ime             : ime,
                prezime         : prezime,
                brindexa        : brindexa,
                jmbg            : jmbg,
                spol            : spol,
                mjesto_rodjenja : mjesto_rodjenja,
                opcina_rodjenja : opcina_rodjenja,
                drzava_rodjenja : drzava_rodjenja,
                datum_rodjenja  : datum_rodjenja,
                drzavljanstvo   : drzavljanstvo,
                nacionalnost    : nacionalnost,
                /** Prebivalšte studenta **/
                drzava_preb     : drzava_preb,
                kanton_preb     : kanton_preb,
                opcina_preb     : opcina_preb,
                adresa_preb     : adresa_preb,
                /** Info o roditeljima **/
                imeoca          : imeoca,
                prezimeoca      : prezimeoca,
                imemajke        : imemajke,
                prezimemajke    : prezimemajke,
                /** Boravište studenta **/
                adresa          : adresa,
                adresa_mjesto   : adresa_mjesto,
                telefon         : telefon,
                email           : email,
                email_id        : email_id,
                /** Srednja škola **/
                naziv           : naziv,
                godina          : godina,
                opcina          : opcina,
                tipskole        : tipskole,
                domaca          : domaca,
                /** Ostale informacije **/
                izvori_finan    : izvori_finan,
                status_a_r      : status_a_r,
                status_a_s      : status_a_s,
                zanimanje_r     : zanimanje_r,
                zanimanje_s     : zanimanje_s,
                status_z_r      : status_z_r,
                status_z_s      : status_z_s,
            },
            success:function(response){
                location.reload();
                response = JSON.parse(response);
                if(response['success'] === 'true'){

                }else{
                    $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
                }
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
                            return $('<input type="hidden">').attr({class:'form-control sm-emails-id', name:'email_id[]', value:'x'});
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

            $.ajax({
                type:'POST',
                url: update_link,
                data: {
                    'remove_email' : true,
                    'id' : email_id
                },
                success:function(response){
                    if(response['success'] === 'true'){
                        $.notify(response['message'], 'success');
                    }else{
                        $.notify(response['message'], 'success');
                    }
                }
            });
        }

        if($(".sm-emails").length === 1){
            $(".sm-emails").val('').attr('no', '1');
            $(".sm-emails-id").val('x');
        }else{
            $(this).parent().parent().remove();

            let counter = 1;
            $(".sm-emails").each(function () {
                $(this).attr('no', counter++);
            });
            emailDOMwidth();
        }
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
});
