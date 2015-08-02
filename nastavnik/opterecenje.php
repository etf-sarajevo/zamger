<?

function nastavnik_opterecenje() {
    global $userid, $user_siteadmin;
    $predmet = my_escape($_REQUEST['predmet']);
    $ag = my_escape($_REQUEST['ag']);

    if (!$user_siteadmin) {
        $q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
        if (mysql_num_rows($q10) < 1 || mysql_result($q10, 0, 0) == "asistent") {
            zamgerlog("nastavnik/opterećenje privilegije (predmet pp$predmet)", 3);
            biguglyerror("Nemate pravo pristupa ovoj opciji");
            return;
        }
    }

    $q = myquery("SELECT p.naziv, p.sati_predavanja, p.sati_tutorijala, p.sati_vjezbi "
            . "FROM predmet p, nastavnik_predmet np "
            . "WHERE p.id = np.predmet and np.akademska_godina = $ag and np.nastavnik = $userid;");

    $json = array();
    while ($red = mysql_fetch_assoc($q)) {
        $json[] = $red;
    }
    ?>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.css">
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.js"></script>
    <div style="width: 75%;">
        <table id="table_opt" class="display">
            <thead>
                <tr>
                    <th>Predmet</th>
                    <th>Predavanja</th>
                    <th>Tutorijali</th>
                    <th>Vježbe</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">
        var table_data = <?= json_encode($json); ?>;
        $(document).ready(function () {
            $('#table_opt').DataTable({
                data: table_data,
                columns: [
                    {data: 'naziv'},
                    {data: 'sati_predavanja'},
                    {data: 'sati_tutorijala'},
                    {data: 'sati_vjezbi'}
                ],
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.7/i18n/Croatian.json"
                }
            });
        });
    </script>
    <a href="<?= genuri() ?>&opt_svi=1">Prikaži za sve predmete</a>
    <?
}
