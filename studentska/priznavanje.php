<?php
function studentska_priznavanje(){
    
?>
<center>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="dodaj">
<table width="60%" border="0" style="max-width:300px" class='priznavanje'>
    <tr style="height: 30px">
        <td >
            <label for="student">Student: </label>
        </td>
        <td>
            <input name="student" id="student" list="brow">
            <datalist id="brow">
                <?php
                    $q777 = db_query("select id, ime, prezime, brindexa, naucni_stepen from osoba where naucni_stepen=6 order by prezime,ime");
                    while ($r777=db_fetch_row($q777)) {
                        ?>
                                    <option  value="<?=$r777[1]." ".$r777[2] ." (".$r777[3] . ")"?>"></option>
                        <?
                                }
                ?>      
            </datalist>  
        </td>
    </tr>
    <tr style="height: 30px">
    <td><label for="ag">Akademska godina: </label>
                            </td>
        <td >
            <select name="ag">
            <option value="-1">Sve akademske godine</option>
            <?
                $q295 = db_query("select id,naziv, aktuelna from akademska_godina order by naziv");
                while ($r295=db_fetch_row($q295)) {
            ?>
            <option value="<?=$r295[0]?>"<? if($r295[0]==$ak_god) print " selected"; ?>><?=$r295[1]?></option>
            <?
                }
            ?>
            </select>
        </td>
    </tr>
    <tr style="height: 30px">
        <td>
            <label for="ciklus">Ciklus studija: </label>
        </td>
        <td >
            <select name="ciklus">
                <option value="1">Prvi</option>
                <option value="2">Drugi</option>
                <option value="3">Treći</option>
            </select>
        </td>
    </tr>
    <tr style="height: 30px">
        <td ><label for='naziv_predmeta'>Naziv predmeta: </label></td>
        <td><input type='text' name='naziv_predmeta'/></td>
    </tr>

    <tr style="height: 30px">
        <td><label for='sifra_predmeta'>Šifra predmeta: </label></td>
        <td><input type='text' name='sifra_predmeta'/></td>
    </tr>

    <tr style="height: 30px">
        <td><label for='ects'>ECTS: </label></td>
        <td><input type='number' name='ects'/></td>
    </tr>

    <tr style="height: 30px">
        <td><label for='ocjena'>Ocjena: </label></td>
        <td><input type='number' name='ocjena'/></td>
    </tr>

    <tr style="height: 30px">
        <td><label for='broj_protokola'>Broj protokola: </label></td>
        <td><input type='text' name='broj_protokola'/></td>
    </tr>

    <tr style="height: 50px">
        <td><label for='strana_institucija'>Strana institucija: </label></td>
        <td><input type='text' name='strana_institucija'/></td>
    </tr>
         
    <tr style="height: 30px">
        <td><label for='datum'>Datum: </label></td>
        <td><input type='date' value="<?= date('Y-m-j')?>" name='datum' style='width:168px'/></td>
    </tr>

    <tr>
        <td></td>
        <td style="padding: 10px 0;text-align:right">
        <input style="margin-left:auto" type='submit' value="Potvrdi"/>
    </td>
    </tr>
</td></tr>
</table>
</form> 
</center>

<?php

}

    $akcija = $_POST['akcija'];
    if($akcija == "dodaj"){
        //db_escape()
        $student = db_escape($_POST["student"]);
        $akademska_godina = db_escape($_POST["ag"]);
        $ciklus = db_escape($_POST["ciklus"]);
        $naziv_predmeta = db_escape($_POST["naziv_predmeta"]);
        $sifra_predmeta = db_escape($_POST["sifra_predmeta"]);
        $ects = db_escape($_POST["ects"]);
        $ocjena = db_escape($_POST["ocjena"]);
        $broj_protokola = db_escape($_POST["broj_protokola"]);
        $strana_institucija = db_escape($_POST["strana_institucija"]);
        $datum = db_escape($_POST["datum"]);

        // Get student id
        $index=end(explode(" ", $student));
        $index=str_replace("(","",$index);
        $index=str_replace(")","",$index);
        if(!$index){
            niceerror("Nije izabran student!");
            return;
        }
        $query = db_query("select id from osoba where brindexa=$index");
        $student_id = db_fetch_row($query)[0];

        // Form validation

        if($akademska_godina == -1) {
            niceerror("Nije izabrana akademska godina.");
            return;
        }
        
        if(!$naziv_predmeta) {
            niceerror("Morate unijeti naziv predmeta.");
            return;
        }
        
        if(!$sifra_predmeta){
            niceerror("Morate unijeti sifru premdeta!");
            return;
        }

        $ects = floatval($ects);
        if($ects <= 0){
            niceerror("ECTS krediti moraju biti pozitivni!");
            return;
        }

        if($ocjena < 6 || $ocjena > 10) {
            niceerror("Ocjena mora biti između 6 i 10");
            return;
        }

        if(!$broj_protokola) {
            niceerror("Morate unijeti broj protokola.");
        }

        if(!$strana_institucija) {
            niceerror("Morate unijeti stranu instituciju.");
        }
        $make_odluka = db_query("insert into odluka set 
        datum='$datum', broj_protokola='$broj_protokola', student=$student_id");

        $odluka_id = db_insert_id('odluka', 'id');
        

        $priznavanje = db_query("insert into priznavanje set 
        student=$student_id, 
        akademska_godina=$akademska_godina,
        ciklus=$ciklus, 
        naziv_predmeta='$naziv_predmeta', 
        sifra_predmeta='$sifra_predmeta', 
        ects=$ects, ocjena=$ocjena, odluka=$odluka_id, 
        strana_institucija='$strana_institucija'");


    }


?>
