
$( document ).ready(function() {
    $(".edit-paragraph-trig").click(function () {
        $(".pop-up-shadow").fadeIn();

        $("#akademska_godina").val($(this).attr('ak-god')); // Akademska godina
        $("#akademska_godina_value").val($(this).attr('ak-naziv')); // Akademska godina naziv

        $("#studij").val($(this).attr('studij')); // Studij
        $("#studij_value").val($(this).attr('studij-naziv')); // Studij naziv

        $("#semestar").val($(this).attr('semestar')); // Semestar
        $("#semestar_value").val($(this).attr('semestar') + ' Semestar'); // Semestar naziv

        $("#nacin_studiranja").val($(this).attr('nacin-studiranja')); // Način studiranja
        $("#ponovac").val($(this).attr('ponovac')); // Ponovac
        $("#status_studenta").val($(this).attr('status_studenta')); // Status studenta
        $("#napomena").val($(this).attr('napomena')); // Status studenta
    });

    $(".close-pop-up").click(function () {
        $(".pop-up-shadow").fadeOut();
    });
});


// ------------------------------------------------------------------------------------------------------------------ //
// ********************************************* Konačna ocjena po odluci ******************************************* //

$( document ).ready(function() {
    $("#ocjena-po-odluci-ag").change(function () {
        let ag = $(this).val();
        // let student = $("#ocjena-po-odluci-student").val();
        $.ajax({
            type:'POST',
            url: 'index.php?sta=ws/predmet',
            data: {ocjena_po_odluci_ag : ag},
            success:function(response){
                response = JSON.stringify(response);
                response = JSON.parse(response);

                // Prvo očistimo select i dodajmo inicijalno "Odaberite predmet"
                $("#ocjena-po-odluci-predmet").empty().append($('<option>', {
                    value: '',
                    text: "Odaberite predmet"
                }));
                // Setujmo sad pasoš
                $("#ocjena-po-odluci-pasos").empty().append($('<option>', {
                    value: '',
                    text: "Odaberite predmet"
                }));

                for(let i=0; i<response['data'].length; i++){
                    $('#ocjena-po-odluci-predmet').append($('<option>', {
                        value: response['data'][i]['predmet'],
                        text: response['data'][i]['naziv_predmeta']
                    }));
                }
                // let response = JSON.parse(data);
            }
        });

    });

    $("#ocjena-po-odluci-predmet").change(function () {
        let predmet = $(this).val();
        $.ajax({
            type:'POST',
            url: 'index.php?sta=ws/predmet',
            data: {ocjena_po_odluci_predmet : predmet},
            success:function(response){
                response = JSON.stringify(response);
                response = JSON.parse(response);

                // Prvo očistimo select i dodajmo inicijalno "Odaberite pasoš"
                $("#ocjena-po-odluci-pasos").empty().append($('<option>', {
                    value: '',
                    text: "Odaberite pasoš"
                }));


                for(let i=0; i<response['data'].length; i++){
                    $('#ocjena-po-odluci-pasos').append($('<option>', {
                        value: response['data'][i]['pasos'],
                        text: response['data'][i]['naziv']
                    }));
                }
                // let response = JSON.parse(data);
            }
        });

    });

    $(".obrisi-konacnu-ocjenu").click(function () {
        let student = $(this).attr('st');
        let predmet = $(this).attr('pr');
        let ak      = $(this).attr('ak');
        console.log(student + ' ' + predmet + ' ' + ak);

        $.ajax({
            type:'POST',
            url: 'index.php?sta=ws/predmet',
            data: {obrisi_konacnu_predmet : predmet, obrisi_konacnu_student : student, obrisi_konacnu_ak : ak},
            success:function(response){
                window.location = '?sta=studentska/konacna_ocjena&student='+student+'&akcija=pregled';
            }
        });
    });

    $( ".datepicker-2" ).datepicker({
        dateFormat: 'dd.mm.yy'
    });
});