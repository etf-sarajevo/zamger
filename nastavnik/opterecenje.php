<?

function nastavnik_opterecenje() {
    global $userid, $user_siteadmin, $user_nastavnik;
    $predmet = my_escape($_REQUEST['predmet']);
    $ag = my_escape($_REQUEST['ag']);

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
    }

    $json = array();
    while ($red = mysql_fetch_assoc($q)) {
        $json[] = $red;
    }
    ?>
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <p>&nbsp;</p>
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
            <tfoot>
                <tr>
                    <th>Ukupno:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
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
                },
                footerCallback: function (row, data, start, end, display) {
                    var tabela = this.api();
                    if(tabela.column(0).data().length > 1) {
                        var intVal = function (i) {
                            return typeof i === 'string' ?
                                    i.replace(/[\$,]/g, '') * 1 :
                                    typeof i === 'number' ?
                                    i : 0;
                        };
                        for (var i = 1; i < 4; i++) {
                            var ukupno = tabela
                                    .column(i)
                                    .data()
                                    .reduce(function (a, b) {
                                        return intVal(a) + intVal(b);
                                    });
                            $(tabela.column(i).footer()).html(ukupno);
                        }
                    }
                    else $("tfoot").remove();
                }
            });
        });
    </script>
    <?
    if (isset($_REQUEST['opt_svi'])) {
        $uri = str_replace('&amp;opt_svi=1', '', genuri());
        print '<a href="' . $uri . '">Prikaži samo za ovaj predmet</a>';
    } else
        print '<a href="' . genuri() . '&opt_svi=1">Prikaži za sve predmete</a>';
}
