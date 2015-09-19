<?

function nastavnik_opterecenje2() {
    global $userid, $user_siteadmin, $user_nastavnik;
    $predmet = intval($_REQUEST['predmet']);
    $ag = intval($_REQUEST['ag']);

    if (!$user_siteadmin && !$user_nastavnik) {
        zamgerlog("nastavnik/opterećenje privilegije (predmet pp$predmet)", 3);
        biguglyerror("Nemate pravo pristupa ovoj opciji");
        return;
    }

    if (!isset($_REQUEST['opt_svi'])) {
        $q = myquery("SELECT p.naziv, p.sati_predavanja, p.sati_tutorijala, p.sati_vjezbi "
                . "FROM predmet p, nastavnik_predmet np "
                . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid and p.id=$predmet;");
    } else if (intval($_REQUEST['opt_svi']) == 1) {
        $q = myquery("SELECT p.naziv, p.sati_predavanja, p.sati_tutorijala, p.sati_vjezbi "
                . "FROM predmet p, nastavnik_predmet np "
                . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid;");
        
        $suma_predavanja = mysql_fetch_assoc(myquery("select sum(p.sati_predavanja) as sum "
                . "FROM predmet p, nastavnik_predmet np "
                . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid;"));
        $suma_predavanja = $suma_predavanja['sum'];
        
        $suma_tutorijala = mysql_fetch_assoc(myquery("select sum(p.sati_tutorijala) as sum "
                . "FROM predmet p, nastavnik_predmet np "
                . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid;"));
        $suma_tutorijala = $suma_tutorijala['sum'];
        
        $suma_vjezbi = mysql_fetch_assoc(myquery("select sum(p.sati_vjezbi) as sum "
                . "FROM predmet p, nastavnik_predmet np "
                . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid;"));
        $suma_vjezbi = $suma_vjezbi['sum'];
    }
    
    $q5 = myquery("select naziv from predmet where id=$predmet");
    if (mysql_num_rows($q5)<1) {
            biguglyerror("Nepoznat predmet");
            zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
            zamgerlog2("nepoznat predmet", $predmet);
            return;
    }
    $predmet_naziv = mysql_result($q5,0,0);
    ?>
    <p>&nbsp;</p>
    <p><h3><? if(isset($_REQUEST['opt_svi'])) echo "Svi predmeti"; else echo $predmet_naziv; ?> - Opterećenje</h3></p>
        <table>
            <thead>
                <tr>
                    <th>Predmet</th>
                    <th>Predavanja</th>
                    <th>Tutorijali</th>
                    <th>Vježbe</th>
                </tr>
            </thead>
            <tbody>
    <?
    while ($red = mysql_fetch_assoc($q)) {
        ?>
            <tr>
                <td><?= $red['naziv'];?></td>
                <td><?= $red['sati_predavanja'];?></td>
                <td><?= $red['sati_tutorijala'];?></td>
                <td><?= $red['sati_vjezbi'];?></td>
            </tr>
        <?
    }
    ?>
            </tbody>
            <? if(isset($_REQUEST['opt_svi'])) {?>
            <tfoot>
                <tr>
                    <th>Ukupno:</th>
                    <th><?= $suma_predavanja; ?></th>
                    <th><?= $suma_tutorijala; ?></th>
                    <th><?= $suma_vjezbi; ?></th>
                </tr>
            </tfoot>
            <? } ?>
        </table>
    <?
    if (isset($_REQUEST['opt_svi'])) {
        $uri = str_replace('&amp;opt_svi=1', '', genuri());
        print '</br><a href="' . $uri . '">Prikaži samo za ovaj predmet</a>';
    } else
        print '</br><a href="' . genuri() . '&opt_svi=1">Prikaži za sve predmete</a>';
}
