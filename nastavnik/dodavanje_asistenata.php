<?

// More info at issue-29.md

function nastavnik_dodavanje_asistenata(){
    global $userid, $user_siteadmin;

    // TODO - Ako je asistent ili superasistent, ne prikazuj ovu opciju nikako ili redirektaj

    $osobe = db_query("select id, ime, prezime from osoba");
    // Daj aktuelnu akademsku godinu
    $akademska_godina = intval($_GET['ag']);
    $predmet = intval($_GET['predmet']);

    if(!$akademska_godina or !$predmet){
        // Ovdje možemo throw-ati error neki ili ukinuti u potpunosti stranicu - Stavit ću samo die u ovom slučaju
        die();
    }

    if(isset($_POST['osoba'])){
        $osoba = intval($_POST['osoba']);
        $nivo_prist = $_POST['uloga'];

        if($osoba and $nivo_prist){ // Da li su vrijednosti prave ili je prazan request
            if($osoba != 0 and $nivo_prist != '' and $nivo_prist != 'obrisi'){
                // Provjeri da li osoba već ima status nastavnika
                $nastavnik = db_get("select count(osoba) from privilegije where privilegija = 'nastavnik' and osoba = ".$osoba);

                if(!$nastavnik){
                    db_query("INSERT into privilegije set osoba = $osoba, privilegija = 'nastavnik'");
                }

                // Provjeravamo da li ima pravo pristupa na predmetu i ako ima, koje je to pravo
                $nivo_pristupa = db_get("select * from nastavnik_predmet where nastavnik = $osoba and akademska_godina = $akademska_godina and predmet = ".$predmet);
                $angazman = db_get("select * from angazman as an inner join angazman_status as ang on an.angazman_status = ang.id where an.akademska_godina = $akademska_godina and an.osoba = $osoba and an.predmet = ".$predmet);

                if(!$nivo_pristupa){
                    db_query("INSERT INTO nastavnik_predmet SET nastavnik = $osoba, akademska_godina = $akademska_godina, nivo_pristupa = '$nivo_prist', predmet = ".$predmet);
                }else{
                    db_query("UPDATE nastavnik_predmet SET nivo_pristupa = '$nivo_prist' where nastavnik = $osoba");
                    //db_query("UPDATE nastavnik_predmet SET nivo_pristupa = 12 where nastavnik = $osoba and akademska_godina = $akademska_godina and predmet = ".$_GET['predmet']);
                }
            }else if($nivo_prist == 'obrisi'){
                db_query("delete from nastavnik_predmet where nastavnik=$osoba and akademska_godina = $akademska_godina and predmet = ".$predmet);
                $osoba = null;
            }
        }else{
            $greska = 'Molimo Vas da odaberete osobu ili nivo pristupa';
        }
    }

    $angazovane_osobe = db_query("select o.ime, o.prezime, o.id, np.nivo_pristupa from nastavnik_predmet as np inner join osoba as o on np.nastavnik = o.id where (np.nivo_pristupa = 'asistent' or np.nivo_pristupa = 'super_asistent') and np.akademska_godina = $akademska_godina and np.predmet = ".$predmet);

    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet"/>

    <br><br>
    <p><h3>Dodajte demonstratora na predmet</h3></p>
    <p>
    <form method="post">
        <select name="osoba" class="users-search">
            <option value="0">Odaberite osobu</option>
            <?php
            while ($o = db_fetch_row($osobe)) {
                ?>
                <option value="<?php echo $o[0]; ?>" <?php if(isset($osoba) and $osoba == $o[0]){ echo 'selected';} ?> >
                    <?php echo $o[1].' '.$o[2]; ?>
                </option>
                <?php
            }
            ?>
        </select>

        <select name="uloga" class="users-search">
            <option value="">Odaberite nivo pristupa</option>
            <option value="asistent" <?php if(isset($nivo_prist)){if($nivo_prist == 'asistent') echo 'selected';} ?>>Asistent</option>
            <option value="super_asistent" <?php if(isset($nivo_prist)){if($nivo_prist == 'super_asistent') echo 'selected';} ?>>Superasistent</option>
        </select>

        <input type="submit" value="SPREMITE" style="height: 28px; padding-left:20px; padding-right: 20px; background: #fff; border:1px solid rgba(0,0,0,0.3); border-radius:3px;">
    </form>
    </p>

    <!-- Ukoliko pokuša unijeti kreirati request sa praznim parametrima -->
    <p style="color: red;"> <?= isset($greska) ? $greska : ''; ?> </p>

    <p>
        LEGENDA: <br>
        Asistent - asistent ima pravo samo da unosi časove, prisustvo i ocjenjuje zadaće <br>
        Superasistent - Potpuni pristup
    </p>

    <br>
    <p><h3>Pregled angažovanih osoba na predmetu</h3></p>
    <p>
    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
        <tr>
            <td>#</font></td>
            <td>Ime i prezime</td>
            <td>Nivo pristupa</td>
            <td style="text-align: center">AKCIJE</td>
        </tr>
        </thead>
        <tbody>

        <?php $counter = 1;
        while ($o = db_fetch_row($angazovane_osobe)) {
            ?>
            <tr>
                <form method="post">
                    <td><?= $counter++; ?>.</td>
                    <td><?= $o[0].' '.$o[1]; ?></td>
                    <td>
                        <input type="hidden" name="osoba" value="<?= $o[2]; ?>">
                        <select name="uloga">
                            <option value="obrisi">Zabranite pristup</option>
                            <option value="asistent" <?= ($o[3] == 'asistent') ? 'selected' : ''; ?> >Asistent</option>
                            <option value="super_asistent" <?= ($o[3] == 'super_asistent') ? 'selected' : ''; ?> >Superasistent</option>
                        </select>
                    </td>
                    <td style="text-align: center;">
                        <input type="submit" class="default" value="SPREMITE">
                    </td>
                </form>
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
