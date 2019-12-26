<?php
function studentska_priznavanje(){
    
?>

<center>
<?=genform("POST")?>
<input type="hidden" name="akcija" value="dodaj">
<style type="text/css"> @import url("static/css/priznavanje.css"); </style>
<table width="60%" border="0" style="max-width:300px" class='priznavanje'>
    <tr >
        <td >
            <label for="student">Student: </label>
        </td>
        <td>
            <input name="student" id="student" list="studenti" autocomplete='off'>
            <datalist id="studenti">
                <?php
                    $q777 = db_query("select ime, prezime, brindexa, naucni_stepen from osoba where naucni_stepen=6 order by prezime,ime");
                    while (db_fetch3($q777,$ime,$prezime,$brindexa)) {
                        ?>
                                    <option  value="<?=$prezime." ".$ime ." (".$brindexa . ")"?>"></option>
                        <?
                                }
                ?>      
            </datalist>  
        </td>
    </tr>
    <tr >
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
    <tr >
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
    <tr>
        <td><label for='strana_institucija'>Strana institucija: </label></td>
        <td><input type='text' name='strana_institucija'/></td>
    </tr>
    
    <tr>    <td colspan="2">    <hr>    </td></tr>
    <tr >
        <td ><label for='naziv_predmeta1'>Naziv predmeta: </label></td>
        <td><input type='text' name='naziv_predmeta1'/></td>
    </tr>

    <tr >
        <td><label for='sifra_predmeta1'>Šifra predmeta: </label></td>
        <td><input type='text' name='sifra_predmeta1'/></td>
    </tr>

    <tr >
        <td><label for='ects1'>ECTS: </label></td>
        <td><input type='number' min='0' step='0.5' name='ects1'/></td>
    </tr>

    <tr >
        <td><label for='ocjena1'>Ocjena: </label></td>
        <td><input type='number' min='6' max='10' name='ocjena1'/></td>
    </tr>

    <tr>
        <td><label for='broj_protokola1'>Broj protokola: </label></td>
        <td><input type='text' name='broj_protokola1'/></td>
    </tr>

         
    <tr >
        <td><label for='datum1'>Datum: </label></td>
        <td><input type='date' value="<?= date('Y-m-j')?>" name='datum1' /></td>
    </tr>
    <tr id="anchor" ><td><input type="hidden" id="kolicina" name="kolicina" value='1'></td></tr>
    <tr>
        <td><input value="Dodaj predmet" type="button" onclick="dodaj_predmet()"></td>
        <td style="padding: 10px 0;text-align:right"><input style="margin-left:auto" type='submit' value="Potvrdi"/></td>
    </tr>
</td></tr>
</table>
</form> 
</center>
<script>
    function dodaj_predmet(){
        let node = document.getElementById('kolicina');
        let kolicina = Number(node.value);
        kolicina = kolicina + 1; // koji je po redu
        node.value = kolicina; // update for next

        let naziv_predmeta = document.createElement('tr');
        naziv_predmeta.innerHTML = `
            <td ><label for='naziv_predmeta${kolicina}'>Naziv predmeta: </label></td>
            <td><input type='text' name='naziv_predmeta${kolicina}'/></td>`;
        
        let sifra_predmeta = document.createElement('tr');
        sifra_predmeta.innerHTML = `
            <td><label for='sifra_predmeta${kolicina}'>Šifra predmeta: </label></td>
            <td><input type='text' name='sifra_predmeta${kolicina}'/></td>`;

        let ects = document.createElement('tr');
        ects.innerHTML = `
            <td><label for='ects${kolicina}'>ECTS: </label></td>
            <td><input type='number' min='0' step='0.5' name='ects${kolicina}'/></td>`;

        let ocjena = document.createElement('tr');
        ocjena.innerHTML = `
            <td><label for='ocjena${kolicina}'>Ocjena: </label></td>
            <td><input type='number' min='6' max='10' name='ocjena${kolicina}'/></td>    `;


        let broj_protokola = document.createElement('tr');
        broj_protokola.innerHTML = `
            <td><label for='broj_protokola${kolicina}'>Broj protokola: </label></td>
        <td><input type='text' name='broj_protokola${kolicina}'/></td>`;

        let datum = document.createElement('tr');
        datum.innerHTML = `
            <td><label for='datum${kolicina}'>Datum: </label></td>
            <td><input type='date' value="<?= date('Y-m-j')?>" name='datum${kolicina}' /></td>`;
        
        let horizontal = document.createElement('tr');
        horizontal.innerHTML = '<td colspan="2"><hr></td>';
        let anchor = document.getElementById('anchor');
        anchor.parentNode.insertBefore(horizontal,anchor);
        anchor.parentNode.insertBefore(naziv_predmeta,anchor);
        anchor.parentNode.insertBefore(sifra_predmeta,anchor);
        anchor.parentNode.insertBefore(ects,anchor);
        anchor.parentNode.insertBefore(ocjena,anchor);
        anchor.parentNode.insertBefore(broj_protokola,anchor);
        anchor.parentNode.insertBefore(datum,anchor);
        return false;
    }
</script>
<?php

}

    $akcija = $_POST['akcija'];
    if($akcija == "dodaj"){
        // db_escape()
        $student = db_escape($_POST["student"]);
        $akademska_godina = db_escape($_POST["ag"]);
        $ciklus = db_escape($_POST["ciklus"]);
        $strana_institucija = db_escape($_POST["strana_institucija"]);
        // treba nam broj unesenih predmeta
        // za svaki cemo posebno query slat
        $broj_predmeta = db_escape($_POST["kolicina"]);
        
        $index=end(explode(" ", $student));
        $index=str_replace("(","",$index);
        $index=str_replace(")","",$index);
        if(!$index){
            niceerror("Nije izabran student!");
            return;
        }
        
        $query = db_query("select id from osoba where brindexa=\"$index\"");
        $student_id = db_fetch_row($query)[0];
        $odluka_id = db_insert_id('odluka', 'id');

        for ($i=1; $i <= $broj_predmeta; $i++) { 
            $naziv_predmeta = db_escape($_POST["naziv_predmeta".$i]);
            $sifra_predmeta = db_escape($_POST["sifra_predmeta".$i]);
            $ects = db_escape($_POST["ects".$i]);
            $ocjena = db_escape($_POST["ocjena".$i]);
            $broj_protokola = db_escape($_POST["broj_protokola".$i]);
            $datum = db_escape($_POST["datum".$i]);
    
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
    
            $priznavanje = db_query("insert into priznavanje set 
                student=$student_id, 
                akademska_godina=$akademska_godina,
                ciklus=$ciklus, 
                naziv_predmeta='$naziv_predmeta', 
                sifra_predmeta='$sifra_predmeta', 
                ects=$ects, ocjena=$ocjena, odluka=$odluka_id, 
                strana_institucija='$strana_institucija'");
        }
    }
?>
