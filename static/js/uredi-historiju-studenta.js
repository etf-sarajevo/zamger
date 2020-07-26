
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

