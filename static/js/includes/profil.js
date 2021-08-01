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
        let email2          = $("#email2").val();
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
                email2          : email2,
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
                if(response['success'] === 'true'){

                }else{
                    $.notify("Došlo je do greške, molimo pokušajte ponovo!", 'error');
                }
            }
        });

        e.preventDefault();
    });
});
