<?

// More info at issue-29.md

function nastavnik_dodavanje_asistenata(){
    global $userid, $user_siteadmin;

    // TODO - Ako je asistent ili superasistent, ne prikazuj ovu opciju nikako ili redirektaj

    $osobe = db_query("select id, ime, prezime from osoba");
    // Daj aktuelnu akademsku godinu
    $akademska_godina = db_fetch1(db_query("select id from akademska_godina where aktuelna = 1 "))[0];


    if(isset($_POST['osoba'])){
        $osoba = $_POST['osoba'];
        $uloga = $_POST['uloga'];

        if($osoba != 0 and $uloga != 0){

            // Provjeri da li osoba već ima status nastavnika
            $nastavnik = db_query("select count(osoba) from privilegije where privilegija = 'nastavnik' and osoba = ".$osoba);
            $nastavnik = db_fetch1($nastavnik)[0] ? : 0; // Ukoliko nema pristup nastavnika - dodaj
            if(!$nastavnik){
                db_query("INSERT into privilegije set osoba = $osoba, privilegija = 'nastavnik'");
            }

            // Provjeravamo da li ima pravo pristupa na predmetu i ako ima, koje je to pravo
            $nivo_pristupa = db_query("select * from nastavnik_predmet where nastavnik = $osoba and akademska_godina = $akademska_godina and predmet = ".$_GET['predmet']);
            $nivo_pristupa = db_fetch1($nivo_pristupa)[3] ? : 0;

            $angazman = db_query("select * from angazman as an inner join angazman_status as ang on an.angazman_status = ang.id where an.akademska_godina = $akademska_godina and an.osoba = $osoba and an.predmet = ".$_GET['predmet']);
            $angazman = db_fetch1($angazman)[5] ? : 0;

            if($uloga == 1){ // U pitanju je demonstrator
                // U tabelu angazman dodajemo angazman_status = 3 => Demonstrator
                // U tabelu nastavnik_predmet dodajemo nivo_pristupa = asistent => Asistent

                $angazman_status = 3;
                $nivo_prist = 'asistent';
            }else if($uloga == 2){ // Asistent
                // U tabelu angazman dodajemo angazman_status = 2 => Asistent
                // U tabelu nastavnik_predmet dodajemo nivo_pristupa = asistent => Asistent

                $angazman_status = 2;
                $nivo_prist = 'asistent';
            }else if($uloga == 3){ // Super asistent
                // U tabelu angazman dodajemo angazman_status = 2 => Asistent
                // U tabelu nastavnik_predmet dodajemo nivo_pristupa = super_asistent => Super asistent

                $angazman_status = 2;
                $nivo_prist = 'asistent';
            }

            // Unesimo ili uredimo angažman
            if(!$angazman){
                db_query("INSERT into angazman set akademska_godina = $akademska_godina, osoba = $osoba, angazman_status = $angazman_status, predmet = ".$_GET['predmet']);
            }else{
                db_query("UPDATE angazman set angazman_status = $angazman_status where akademska_godina = $akademska_godina and osoba = $osoba and predmet = ".$_GET['predmet']);
            }

            // Unesimo ili uredimo nivo pristupa
            if(!$nivo_pristupa){
                db_query("INSERT INTO nastavnik_predmet SET nastavnik = $osoba, akademska_godina = $akademska_godina, nivo_pristupa = '$nivo_prist', predmet = ".$_GET['predmet']);
            }else{
                db_query("UPDATE nastavnik_predmet SET nivo_pristupa = '$nivo_prist' where nastavnik = $osoba");
                //db_query("UPDATE nastavnik_predmet SET nivo_pristupa = 12 where nastavnik = $osoba and akademska_godina = $akademska_godina and predmet = ".$_GET['predmet']);
            }
        }
    }

    $angazovane_osobe = db_query("select o.ime, o.prezime, ang.naziv from angazman as an inner join angazman_status as ang on an.angazman_status = ang.id inner join osoba as o on an.osoba = o.id where an.akademska_godina = $akademska_godina and an.predmet = ".$_GET['predmet']);
    //$angazovane_osobe = db_query("select * from angazman as an where an.akademska_godina = $akademska_godina and an.predmet = ".$_GET['predmet']);


    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet"/>

    <br><br>
    <p><h3>Dodajte asistenta / demonstratora na predmet</h3></p>
    <p>
    <form method="post">
        <select name="osoba" class="users-search">
            <option value="0">Odaberite osobu</option>
            <?php
            while ($o = db_fetch_row($osobe)) {
                ?>
                <option value="<?php echo $o[0]; ?>" <?php if(isset($osoba)){if($osoba == $o[0]) echo 'selected';} ?> >
                    <?php echo $o[1].' '.$o[2]; ?>
                </option>
                <?php
            }
            ?>
        </select>

        <select name="uloga" class="users-search">
            <option value="0">Odaberite ulogu</option>
            <option value="1" <?php if(isset($uloga)){if($uloga == 1) echo 'selected';} ?>>Demonstrator</option>
            <option value="2" <?php if(isset($uloga)){if($uloga == 2) echo 'selected';} ?>>Asistent</option>
            <option value="3" <?php if(isset($uloga)){if($uloga == 3) echo 'selected';} ?>>Superasistent</option>
        </select>

        <input type="submit" value="SPREMITE" style="height: 28px; padding-left:20px; padding-right: 20px; background: #fff; border:1px solid rgba(0,0,0,0.3); border-radius:3px;">
    </form>
    </p>

    <br>
    <p><h3>Pregled angažovanih osoba na predmetu</h3></p>
    <p>
    <table border="0" cellspacing="1" cellpadding="2">
        <thead>
        <tr bgcolor="#999999">
            <td style="padding:4px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">#</font></td>
            <td style="padding:4px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Ime i prezime</font></td>
            <td style="padding:4px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Angažman</font></td>
            <td style="padding:4px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">Opcija</font></td>
            <td style="padding:4px 20px;"><font style="font-family:DejaVu Sans,Verdana,Arial,sans-serif;font-size:11px;font-weight:bold;color:white;">AKCIJE</font></td>
        </tr>
        </thead>
        <tbody>

            <?php $counter = 1;
            while ($o = db_fetch_row($angazovane_osobe)) {
                ?>
                <tr>
                    <td><?= $counter++; ?></td>
                    <td><?= $o[0].' '.$o[1]; ?></td>
                    <td><?= $o[2]; ?></td>
                    <td>
                        <select name="uloga" class="users-search">
                            <option value="0">Obrišite</option>
                            <option value="1">Demonstrator</option>
                            <option value="2">Asistent</option>
                            <option value="3">Superasistent</option>
                        </select>
                    </td>
                    <td style="text-align: center;">SPREMITE</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    </p>
    <script>
        $(document).ready(function() {
            $('.users-search').select2();
        });
    </script>
    <?php
}
