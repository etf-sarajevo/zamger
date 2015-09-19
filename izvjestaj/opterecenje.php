<?

function izvjestaj_opterecenje() {
    global $user_siteadmin, $user_studentska;
    $ag = intval($_REQUEST['_lv_column_akademska_godina']);
    
    if (!$user_siteadmin && !$user_studentska) {        
        zamgerlog("izvjestaj/opterećenje privilegije", 3);
        biguglyerror("Nemate pravo pristupa ovoj opciji");
        return;        
    }

    $query = myquery("SELECT CONCAT(o.ime, ' ', o.prezime) AS profesor, p.naziv, p.sati_predavanja, p.sati_tutorijala, p.sati_vjezbi 
FROM predmet p, nastavnik_predmet np, osoba o 
WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = o.id;");
    
    $ag_naziv = mysql_fetch_assoc(myquery("select naziv from akademska_godina where id=$ag"));
    ?>
    <p>Univerzitet u Sarajevu<br/>
        Elektrotehnički fakultet Sarajevo</p>
    <p>Datum i vrijeme izvještaja: <?= date("d. m. Y. H:i"); ?></p>

    <h1>Izvještaj: Sedmično opterećenje profesora po predmetima</h1>
    <h3>Akademska godina: <?=$ag_naziv['naziv'];?></h3>

    <table>
        <tr>
            <th>Profesor</th>
            <th>Predmet</th>
            <th>Sati predavanja</th>
            <th>Sati tutorijala</th>
            <th>Sati vježbi</th>
        </tr>

        <?
        while ($red = mysql_fetch_assoc($query)) {
            ?>
            <tr>
                <td><?= $red['profesor'] ?></td>
                <td><?= $red['naziv'] ?></td>
                <td><?= $red['sati_predavanja'] ?></td>
                <td><?= $red['sati_tutorijala'] ?></td>
                <td><?= $red['sati_vjezbi'] ?></td>
            </tr>
            <?
        }
        ?>
    </table>

    <?
}
