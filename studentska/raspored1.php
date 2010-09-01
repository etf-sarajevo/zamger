<link href="css/raspored1.css" rel="stylesheet" type="text/css">
<? 
// STUDENTSKA/RASPORED1 - administracija rasporeda

function studentska_raspored1(){
	global $userid,$user_siteadmin,$user_studentska;
	global $_lv_; // Potrebno za genform() iz libvedran
	
	// Provjera privilegija
	
		if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
		}
?>
<script language="JavaScript" type="text/javascript">
	function daj_uputstvo(){
		var x=document.getElementById('uputstvo_za_mas_unos_sala');
		var slika=document.getElementById('slika_za_mas_unos_sala');
		if(x.style.display=="none"){
			 x.style.display="inline";
			 slika.src="images/minus.png";
		}
		else {
			x.style.display="none";
			slika.src="images/plus.png";
		}
	}

	function promjeniSemestar(){
		studij=document.getElementById('studij').value;
		if(studij!=1){
			document.getElementById('pgs').style.display='none';
			document.getElementById('ostalo').style.display='';
		}
		else{
			document.getElementById('pgs').style.display='';
			document.getElementById('ostalo').style.display='none';
		}
	}

	function prikaziGrupe() {
		document.getElementById('akcija_novi_cas').value="unos_novog_casa_predfaza";
		document.forma_za_unos_casa.submit();
	}

	function brisanje_casa(id_casa) {
		var a = confirm("Obrisati čas! Da li ste sigurni?");
		if (a) {
			document.getElementById('id_casa_za_brisanje').value=id_casa;
			document.brisanjecasa.submit();
		}
	}
</script>
<center>
<table border="0"><tr><td>
<?

// uslov ispod se koristi ako prikazujemo stranicu za rad sa salama
if(isset($_REQUEST['edit_sala']) && $_REQUEST['edit_sala']==1){	
	$greska=0;
	
	//uslov ispod je ispunjen ako je prihvaćena forma za unos nove sale
	if ($_POST['akcija'] == 'unos_nove_sale' && check_csrf_token()) {
		if(empty($_POST['ime_sale'])){
			$greska=1; $greska_prazno_ime_sale=1;
		}
		else{ // ako ime sale nije prazno izvršava se sljedeći kod
			$ime_sale=my_escape($_POST['ime_sale']);
			$q0=myquery("select naziv from raspored_sala");
			for($i=0;$i<mysql_num_rows($q0);$i++)
			{
				if(mysql_result($q0, $i,0)==$ime_sale){
					$greska=1; $greska_postoji_sala=1;
				}
			}
		}
		$tip_sale=my_escape($_POST['tip_sale']);
		if(empty($_POST['kapacitet'])){
			$greska=1; $greska_prazan_kapacitet=1;
		}
		elseif(!is_numeric($_POST['kapacitet'])){
			$greska=1; $greska_kapacitet_nije_broj=1;
		}
		else{ // ako je kapacitet ispravno unešen
			$kapacitet=intval($_POST['kapacitet']);
		}
		if($greska==0){ // ako nema greski unosimo novu salu u bazu
			$tip_sale=my_escape($_POST['tip_sale']);
			$q0=myquery("select * from raspored_sala");
			if(mysql_num_rows($q0)>0){
				$q1=myquery("select max(id) from raspored_sala");
				$id_nove_sale=mysql_result($q1,0,0)+1;
			}
			else $id_nove_sale=1;
			$q0=myquery("insert into raspored_sala set id=$id_nove_sale, naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale'");
			$uspjesno_unesena_sala=1;
			zamgerlog("upisana nova sala $ime_sale", 2); // nivo 2 je izmjena podataka u bazi
		}
	}
	//uslov ispod je ispunjen ako je prihvaćena forma za unos nove sale
	elseif ($_POST['akcija'] == 'editovanje_sale' && check_csrf_token()) {
		$id_sale_za_edit=intval($_POST['id_sale_za_edit']);
		$q0=myquery("select naziv from raspored_sala where id=$id_sale_za_edit");
		$stari_naziv_sale=mysql_result($q0,0,0);
		if(empty($_POST['edit_ime_sale'])){
			$greska=1; $greska_prazno_ime_sale=1;
		}
		else{ // ako ime sale nije prazno izvršava se sljedeći kod
			$ime_sale=my_escape($_POST['edit_ime_sale']);
			$q1=myquery("select naziv from raspored_sala");
			for($i=0;$i<mysql_num_rows($q1);$i++)
			{
				if(mysql_result($q1, $i,0)==$ime_sale && $ime_sale!=$stari_naziv_sale){
					$greska=1; $greska_postoji_sala=1;
				}
			}
		}
		$tip_sale=my_escape($_POST['edit_tip_sale']);
		if(empty($_POST['edit_kapacitet'])){
			$greska=1; $greska_prazan_kapacitet=1;
		}
		elseif(!is_numeric($_POST['edit_kapacitet'])){
			$greska=1; $greska_kapacitet_nije_broj=1;
		}
		else{ // ako je kapacitet ispravno unešen
			$kapacitet=intval($_POST['edit_kapacitet']);
		}
		if($greska==0){ // ako nema greski unosimo novu salu u bazu
			$tip_sale=my_escape($_POST['edit_tip_sale']);
			$q1=myquery("update raspored_sala set naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale' where id=$id_sale_za_edit");
			$uspjesno_editovana_sala=1;
			zamgerlog("editovana sala $stari_naziv_sale", 2); // nivo 2 je izmjena podataka u bazi
		}
	}
	$greska_masovnog_unosa=0;
	// uslov ispod je ispunjen ako je prihvaćena forma za masovni unos sala
	if ($_POST['akcija'] == 'masovni_unos_sala' && check_csrf_token()){
		$redovi=explode("\n", $_POST['mas_unos_sala']);
		if(trim($_POST['mas_unos_sala'])==''){
			$greska_masovnog_unosa=1; $greska_prazan_prostor_za_mas_unos_sala=1;
		}
		$greska_u_redu=array();
		$greska_prazni_parametri_u_redu=array();
		$greska_nevalja_tip_sale_u_redu=array();
		$greska_nevalja_kapacitet_u_redu=array();
		$greska_postoji_sala_u_redu=array();
		$i=0;
		foreach ($redovi as $red){
			$i++;
			$red=trim($red);
			if(strlen($red)<1) continue; // prazan red
			if($_POST['separator']==1) {
				list($ime_sale,$tip_sale,$kapacitet)=explode(",", $red);
				$ime_sale=trim($ime_sale);
				$tip_sale=trim($tip_sale);
				$kapacitet=trim($kapacitet);
				$niz=explode(",", $red);
			}
			elseif ($_POST['separator']==2) {
				list($ime_sale,$tip_sale,$kapacitet)=explode("\t", $red);
				$ime_sale=trim($ime_sale);
				$tip_sale=trim($tip_sale);
				$kapacitet=trim($kapacitet);
				$niz=explode("\t", $red);
			}
			if (count($niz)!=3){
				$greska_masovnog_unosa=1; $greska_u_redu[]=$i;
			}
			elseif (count($niz)==3){
				if($ime_sale=='' || $tip_sale=='' || $kapacitet==''){
					$greska_masovnog_unosa=1; $greska_prazni_parametri_u_redu[]=$i; 
				}
				else{
					if ($tip_sale!='amf' && $tip_sale!='lab' && $tip_sale!='kab'){
						$greska_masovnog_unosa=1; $greska_nevalja_tip_sale_u_redu[]=$i;
					}
					if(!is_numeric($kapacitet)){
						$greska_masovnog_unosa=1; $greska_nevalja_kapacitet_u_redu[]=$i;
					}
					$q0=myquery("select naziv from raspored_sala");
					for($j=0;$j<mysql_num_rows($q0);$j++)
					{
						if(mysql_result($q0,$j,0)==$ime_sale){
							$greska_masovnog_unosa=1; $greska_postoji_sala_u_redu[]=$i;
						}
					}
				}
			}
		}
		// ako nema grešaka u unosu dodajemo sale
		if($greska_masovnog_unosa==0){
			$unesene_sale=array();
			foreach ($redovi as $red){
				$red=trim($red);
				if(strlen($red)<1) continue; // prazan red
				if($_POST['separator']==1) {
					list($ime_sale,$tip_sale,$kapacitet)=explode(",", $red);
					$ime_sale=trim($ime_sale);
					$tip_sale=trim($tip_sale);
					if ($tip_sale=='amf') $tip_sale='amfiteatar';
					elseif ($tip_sale=='lab') $tip_sale='laboratorija';
					elseif ($tip_sale=='kab') $tip_sale='kabinet';
					$kapacitet=trim($kapacitet);
					$niz=explode(",", $red);
				}
				elseif ($_POST['separator']==2) {
					list($ime_sale,$tip_sale,$kapacitet)=explode("\t", $red);
					$ime_sale=trim($ime_sale);
					$tip_sale=trim($tip_sale);
					if ($tip_sale=='amf') $tip_sale='amfiteatar';
					elseif ($tip_sale=='lab') $tip_sale='laboratorija';
					elseif ($tip_sale=='kab') $tip_sale='kabinet';
					$kapacitet=trim($kapacitet);
					$niz=explode("\t", $red);
				}
				$q0=myquery("select * from raspored_sala");
				if(mysql_num_rows($q0)>0){
					$q1=myquery("select max(id) from raspored_sala");
					$id_nove_sale=mysql_result($q1,0,0)+1;
				}
				else $id_nove_sale=1;
				$q0=myquery("insert into raspored_sala set id=$id_nove_sale, naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale'");
				$unesene_sale[]=$ime_sale;
				zamgerlog("masovni unos sala: Unesena je sala $ime_sale", 2);
			}
			$uspjesan_masovni_unos_sala=1;
		} 
	}
	
	// Obrisi salu
	if ($_POST['akcija'] == "obrisi_salu" && check_csrf_token()) {
		$id_sale_za_brisanje = intval($_POST['id_sale_za_brisanje']);
		$q1=myquery("select naziv from raspored_sala where id=$id_sale_za_brisanje");
		$naziv=mysql_result($q1,0,0);
		$q2=myquery("delete from raspored_sala where id=$id_sale_za_brisanje");
		$uspjesno_obrisana_sala=1;
		zamgerlog("obrisana sala $naziv",4); // nivo 4: audit
	}
	
	if(isset($_REQUEST['sala_za_edit'])) {?>
	<div id="prikaz_za_editovanje_sale">
		<?
		$id_sale_za_edit=$_REQUEST['sala_za_edit'];
		$q0=myquery("select naziv,tip,kapacitet from raspored_sala where id=$id_sale_za_edit");
		$ime_sale=mysql_result($q0,0,0);
		$tip_sale=mysql_result($q0,0,1);
		$kapacitet=mysql_result($q0,0,2);
		if(isset($uspjesno_editovana_sala) && $uspjesno_editovana_sala==1) nicemessage("Sala je uspješno izmijenjena.");
		print "<h4>Editovanje sale $ime_sale:</h4>";
		print genform("POST", "forma_za_editovanje_sale"); ?>
		<input type="hidden" name="akcija" value="editovanje_sale">
		<input type="hidden" name="id_sale_za_edit" value="<?print "$id_sale_za_edit";?>">
		<table cellpadding="3">
		<tr>
			<td align="left" width="100">Ime sale:</td>
			<td>
				<input type="text" name="edit_ime_sale" maxlength="10" size="11" value="
					<? 
						if($_POST['edit_ime_sale']) print "{$_POST['edit_ime_sale']}";
						else print "$ime_sale";
					?>
				">
			</td>
			<? if($greska_prazno_ime_sale==1) print "<td><p class=\"crveno\">niste unijeli ime sale</p></td>";?>
			<? if($greska_postoji_sala==1) print "<td><p class=\"crveno\">postoji sala sa tim imenom</p></td>";?> 
		</tr>
		<tr>
			<td align="left" width="100">Tip sale:</td>
			<td>
				<select name="edit_tip_sale">
					<option value="amfiteatar">amfiteatar</option>
					<option value="laboratorija" 
							<? 
								if($_POST['edit_tip_sale']=="laboratorija") print "selected=\"selected\""; 
								else {
									if($tip_sale=="laboratorija") print "selected=\"selected\""; 
								}
							?>
						>laboratorija
					</option>
					<option value="kabinet"
							<? 
								if($_POST['edit_tip_sale']=="kabinet") print "selected=\"selected\""; 
								else {
									if($tip_sale=="kabinet") print "selected=\"selected\""; 
								}
							?>
						>kabinet
					</option>
				</select>
			</td>
		</tr>
		<tr>
			<td align="left" width="100">Kapacitet:</td>
			<td>
				<input type="text" name="edit_kapacitet" maxlength="4" size="11" value="
					<? 
						if($_POST['edit_kapacitet']) print "{$_POST['edit_kapacitet']}";
						else print "$kapacitet";
					?>
				">
			</td>
			<? if($greska_prazan_kapacitet==1) print "<td><p class=\"crveno\">niste unijeli kapacitet</p></td>";?>
			<? if($greska_kapacitet_nije_broj==1) print "<td><p class=\"crveno\">kapacitet treba biti broj</p></td>";?>
		</tr>
		<tr>
			<td></td>
			<td align="right"><input type="submit" value=" Potvrdi promjene "></td>
		</tr>
		</table>
		</form>
		<p><a href="?sta=studentska/raspored1&edit_sala=1">vrati se nazad na unos sala</a></p>
	</div>
	<?
	} 
	else { ?>
	
	<div id="normalni_prikaz">
	<?
	if(isset($uspjesno_unesena_sala) && $uspjesno_unesena_sala==1) nicemessage("Sala $ime_sale je uspješno dodana.");
	if($uspjesno_obrisana_sala==1) nicemessage("Sala je uspješno obrisana.");
	print "<h4>Dodavanje nove sale:</h4>";
	print genform("POST", "forma_za_unos_sale"); ?>
	<input type="hidden" name="akcija" value="unos_nove_sale">
	<table cellpadding="3">
	<tr>
		<td align="left" width="100">Ime sale:</td>
		<td>
		<input type="text" name="ime_sale" maxlength="10" size="11" 
			<? 
			if($greska==1 && isset($_POST['ime_sale'])){
				print "value=\"{$_POST['ime_sale']}\"";
			}
			?>
		></td>
		<? if($greska_prazno_ime_sale==1) print "<td><p class=\"crveno\">niste unijeli ime sale</p></td>";?>
		<? if($greska_postoji_sala==1) print "<td><p class=\"crveno\">postoji sala sa tim imenom</p></td>";?>
	</tr>
	<tr>
		<td align="left" width="100">Tip sale:</td>
		<td>
			<select name="tip_sale">
				<option value="amfiteatar">amfiteatar</option>
				<option value="laboratorija" 
					<? if($greska==1 && $_POST['tip_sale']=="laboratorija") print "selected=\"selected\""; ?>
					>laboratorija
				</option>
				<option value="kabinet"
					<? if($greska==1 && $_POST['tip_sale']=="kabinet") print "selected=\"selected\""; ?>
					>kabinet
				</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="left" width="100">Kapacitet:</td>
		<td><input type="text" name="kapacitet" maxlength="4" size="11" 
			<? 
			if($greska==1 && isset($_POST['kapacitet'])){
				print "value=\"{$_POST['kapacitet']}\"";
			}
			?>
		></td>
		<? if($greska_prazan_kapacitet==1) print "<td><p class=\"crveno\">niste unijeli kapacitet</p></td>";?>
		<? if($greska_kapacitet_nije_broj==1) print "<td><p class=\"crveno\">kapacitet treba biti broj</p></td>";?>
	</tr>
	<tr>
		<td></td>
		<td align="right"><input type="submit" value=" Dodaj "></td>
	</tr>
	</table>
	</form>
	<hr>
	<h4>Masovni unos sala:</h4>
	<? 
	if(count($greska_u_redu)>0){
		print "<p class=\"crveno\">GREŠKA: Nema potreban broj parametara u redu $greska_u_redu[0]";
		if(count($greska_u_redu)==1) print ".</p>";
		else{
			for($i=1;$i<count($greska_u_redu);$i++){
				if($i==(count($greska_u_redu)-1)) print " i {$greska_u_redu[$i]}.</p>";
				else print ", {$greska_u_redu[$i]}";
			} 
		}
	}
	if(count($greska_prazni_parametri_u_redu)>0){
		print "<p class=\"crveno\">GREŠKA: Postoje prazni parametri u redu $greska_prazni_parametri_u_redu[0]";
		if(count($greska_prazni_parametri_u_redu)==1) print ".</p>";
		else{
			for($i=1;$i<count($greska_prazni_parametri_u_redu);$i++){
				if($i==(count($greska_prazni_parametri_u_redu)-1)) print " i {$greska_prazni_parametri_u_redu[$i]}.</p>";
				else print ", {$greska_prazni_parametri_u_redu[$i]}";
			} 
		}
	}
	if(count($greska_nevalja_tip_sale_u_redu)>0){
		print "<p class=\"crveno\">GREŠKA: Tip sale treba biti amf,lab ili kab (red $greska_nevalja_tip_sale_u_redu[0]";
		if(count($greska_nevalja_tip_sale_u_redu)==1) print ").</p>";
		else{
			for($i=1;$i<count($greska_nevalja_tip_sale_u_redu);$i++){
				if($i==(count($greska_nevalja_tip_sale_u_redu)-1)) print " i {$greska_nevalja_tip_sale_u_redu[$i]} ).</p>";
				else print ", {$greska_nevalja_tip_sale_u_redu[$i]}";
			} 
		}
	}
	if(count($greska_nevalja_kapacitet_u_redu)>0){
		print "<p class=\"crveno\">GREŠKA: Kapacitet treba biti broj (red $greska_nevalja_kapacitet_u_redu[0]";
		if(count($greska_nevalja_kapacitet_u_redu)==1) print ").</p>";
		else{
			for($i=1;$i<count($greska_nevalja_kapacitet_u_redu);$i++){
				if($i==(count($greska_nevalja_kapacitet_u_redu)-1)) print " i {$greska_nevalja_kapacitet_u_redu[$i]} ).</p>";
				else print ", {$greska_nevalja_kapacitet_u_redu[$i]}";
			} 
		}
	}
	if(count($greska_postoji_sala_u_redu)>0){
		print "<p class=\"crveno\">GREŠKA: Postoji sala (red $greska_postoji_sala_u_redu[0]";
		if(count($greska_postoji_sala_u_redu)==1) print ").</p>";
		else{
			for($i=1;$i<count($greska_postoji_sala_u_redu);$i++){
				if($i==(count($greska_postoji_sala_u_redu)-1)) print " i {$greska_postoji_sala_u_redu[$i]}).</p>";
				else print ", {$greska_postoji_sala_u_redu[$i]}";
			} 
		}
	}
	if($uspjesan_masovni_unos_sala==1 && (count($unesene_sale)>0)){
		print "<p class=\"crveno\">Uspješno unešena sala $unesene_sale[0]";
			if(count($unesene_sale)==1) print ".</p>";
			else{
				for($i=1;$i<count($unesene_sale);$i++){
					if($i==(count($unesene_sale)-1)) print " i {$unesene_sale[$i]}.</p>";
					else print ", {$unesene_sale[$i]}";
				} 
		}
	}
	if($greska_prazan_prostor_za_mas_unos_sala==1) print "<p class=\"crveno\">GREŠKA: Niste unijeli nikakve podatke u prostor za masovni unos.</p>";
	?>
	<a href="#" onclick="daj_uputstvo()"><img id="slika_za_mas_unos_sala" src = "images/plus.png" border="0" align="left" />Uputstvo za masovni unos</a>
	<div id="uputstvo_za_mas_unos_sala" style="display:none">
		<p>Unesite ime sale, tip sale (amf,lab ili kab) i kapacitet odvojene zarezom</p>
		<p>Ukoliko unosite podatke iz Excel-a odaberite opciju unos iz excela(odvajanje sa [tab]-om)</p>
		<p>Svaku novu salu dodajte u novom redu</p>
		<p>primjer:</p>
		<p>s01,lab,30</p>
		<p>s02,amf,60</p>
		<p>s03,kab,40</p>
	</div>
	<?=genform("POST")?>
	<input type="hidden" name="akcija" value="masovni_unos_sala">
	<textarea name="mas_unos_sala" rows="10" cols="40">
	<? if(isset($_POST['mas_unos_sala'])) print trim($_POST['mas_unos_sala']) ?>
	</textarea>
	<br/>
	<p>Tip unosa:
	<select name="separator">
		<option value="1">standardan unos (odvajanje zarezom)</option>
		<option value="2">unos iz excela(odvajanje sa [tab]-om)</option>
	</select>
	</p>
	<input type="submit" value=" Dodaj "></form>
	<br></br>
	</div>
	<?}?>
<?
	// završava se div koji se prikazuje kada se ne radi o editovanju sale
	print "<p><a href=\"?sta=studentska/raspored1\">vrati se na početnu</a></p>";
?>
	<script language="JavaScript">
	function brisanje_sale(id_sale) {
		var a = confirm("Obrisati salu! Da li ste sigurni?");
		if (a) {
			document.getElementById('id_sale_za_brisanje').value=id_sale;
			document.brisanjesale.submit();
		}
	}
	</script>
	<?=genform("POST","brisanjesale")?>
	<input type="hidden" name="akcija" value="obrisi_salu">
	<input type="hidden" name="id_sale_za_brisanje" id="id_sale_za_brisanje" value=""></form>
	
	<h4>Postojeće sale:</h4>
	<table class="sale" border="1" cellspacing="0">
		<?
		$q1=myquery("select id,naziv,tip,kapacitet from raspored_sala order by id");
		if(mysql_num_rows($q1)<1) print "<p>Nema kreiranih sala</p>";
		else{
		?>
			<th>Ime sale</th>
			<th>Tip sale</th>
			<th>Kapacitet</th>
			<th colspan="2">Akcije</th>
			<?  
			for($i=0;$i<mysql_num_rows($q1);$i++){
				$id=mysql_result($q1,$i,0);
				$ime_sale=mysql_result($q1,$i,1);
				$tip_sale=mysql_result($q1,$i,2);
				$kapacitet=mysql_result($q1,$i,3);;
				print "<tr>";
				print "<td>$ime_sale</td>";
				print "<td>$tip_sale</td>";
				print "<td>$kapacitet</td>";
				print "<td width=\"80\"><a  href=\"?sta=studentska/raspored1&edit_sala=1&sala_za_edit=$id\"> izmijeni </a></td>";
				print "<td width=\"80\"><a  href=\"javascript:onclick=brisanje_sale('$id')\"> obriši </a></td>";
				print "</tr>";
			}
		}
		?>
	</table>
<?
}

/*##############################################################################################################
  #	if uslov iznad se koristi za rad sa salama (dodavanje i izmjenu sala)                                      #      
  #	a uslov ispod se koristi za rad sa rasporedom (dodavanje i izmjenu rasporeda)                              #   
  ##############################################################################################################*/ 

// uslov ispod se koristi ako prikazujemo stranicu za rad sa rasporedom
elseif(isset($_REQUEST['edit_rasp']) && $_REQUEST['edit_rasp']==1){
	if ($_POST['akcija'] == 'unos_novog_rasporeda' && check_csrf_token()) {
		$akademska_godina=intval($_POST['akademska_godina']);
		$studij=intval($_POST['studij']);
		if($studij!=1) $semestar=intval($_POST['semestar2']);
		else $semestar=intval($_POST['semestar']);
		$q0=myquery("select akademska_godina,studij,semestar from raspored");
		$greska_postoji_raspored=0;
		for($i=0;$i<mysql_num_rows($q0);$i++){
			if(mysql_result($q0,$i,0)==$akademska_godina && mysql_result($q0,$i,1)==$studij && mysql_result($q0,$i,2)==$semestar){
				$greska_postoji_raspored=1;
			}
		}
		if($greska_postoji_raspored==0){
			$q0=myquery("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij, semestar=$semestar");
			$uspjesno_unesen_raspored=1;
			zamgerlog("Unesen novi raspored", 2);
		}
	}
	
	// Obrisi raspored
	if ($_POST['akcija'] == "obrisi_raspored" && check_csrf_token()) {
		$id_rasporeda_za_brisanje = intval($_POST['id_rasporeda_za_brisanje']);
		$q1=myquery("select studij,akademska_godina,semestar from raspored where id=$id_rasporeda_za_brisanje");
		$studij=mysql_result($q1,0,0);
		$akademska_godina=mysql_result($q1,0,1);
		$semestar=mysql_result($q1,0,2);
		$q2=myquery("select naziv from studij where id=$studij");
		$naziv_studija=mysql_result($q2,0,0);
		$q3=myquery("select naziv from akademska_godina where id=$akademska_godina");
		$naziv_akademske_godine=mysql_result($q3,0,0);
		$q3=myquery("delete from raspored where id=$id_rasporeda_za_brisanje");
		$q4=myquery("delete from raspored_stavka where raspored=$id_rasporeda_za_brisanje");
		$uspjesno_obrisan_raspored=1;
		zamgerlog("obrisan raspored za akademsku $naziv_akademske_godine godinu, studij $naziv_studija, semestar $semestar",4); // nivo 4: audit
	}
	// ako se klikne na link izmijeni raspored ispunjen je sljedeći uslov i prikazuje se taj html kod
	if(isset($_REQUEST['raspored_za_edit'])){
		$raspored_za_edit=$_REQUEST['raspored_za_edit'];
		$q1=myquery("select studij,akademska_godina,semestar from raspored where id=$raspored_za_edit");
		$studij=mysql_result($q1,0,0);
		$akademska_godina=mysql_result($q1,0,1);
		$semestar=mysql_result($q1,0,2);
		$q2=myquery("select naziv from studij where id=$studij");
		$naziv_studija=mysql_result($q2,0,0);
		$q3=myquery("select naziv from akademska_godina where id=$akademska_godina");
		$naziv_akademske_godine=mysql_result($q3,0,0);
		
		// ukoliko se prihvati forma za unos novog časa ispunjen je uslov ispod 
		if ($_POST['akcija'] == 'unos_novog_casa' && check_csrf_token()) {
			$greska_u_dodavanju_casa=0;
			$dan=intval($_POST['dan']);
			$tip=my_escape($_POST['tip']);
			$predmet=intval($_POST['predmet']);
			$sala=intval($_POST['sala']);
			$vrijeme_pocetak_sati=intval($_POST['pocetakSat']);
			$vrijeme_pocetak_minute=intval($_POST['pocetakMin']);
			$vrijeme_kraj_sati=intval($_POST['krajSat']);
			$vrijeme_kraj_minute=intval($_POST['krajMin']);
			$vrijeme_pocetak=$vrijeme_pocetak_sati*4 + $vrijeme_pocetak_minute;
			$vrijeme_kraj=$vrijeme_kraj_sati*4 + $vrijeme_kraj_minute;
			$labgrupa=intval($_POST['labgrupa']);
			if($vrijeme_pocetak_sati!=-1 && $vrijeme_pocetak_minute!=0 && $vrijeme_kraj_sati!=-1 && $vrijeme_kraj_minute!=0){
				if($vrijeme_pocetak>=$vrijeme_kraj){$greska_u_dodavanju_casa=1;$greska_neispravan_interval=1;}
			}
			if($dan==0 || $tip=='0' || $predmet==0 || $sala==0 || $vrijeme_pocetak_sati==-1 || $vrijeme_pocetak_minute==0 || $vrijeme_kraj_sati==-1 || $vrijeme_kraj_minute==0 || ($labgrupa==0 && $tip!='P')){
				$greska_u_dodavanju_casa=1;$greska_prazni_parametri_u_casu=1;
			}
			$q0=myquery("select sala,vrijeme_pocetak,vrijeme_kraj,predmet,tip,labgrupa from raspored_stavka where dan_u_sedmici=$dan and raspored=$raspored_za_edit");
			if($vrijeme_kraj>49) {$greska_u_dodavanju_casa=1;$greska_nevalja_termin=1;}
			if($predmet!=0){
				if($studij!=1){
					$q1=myquery("select obavezan from ponudakursa where studij=$studij and akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet");
					$obavezan_predmet=mysql_result($q1,0,0);
				}
				else{
					$q1=myquery("select obavezan from ponudakursa where akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet");
					$obavezan_predmet=mysql_result($q1,0,0);
				}
			}
			for($i=0;$i<mysql_num_rows($q0);$i++){
				$sala_i=mysql_result($q0,$i,0);
				$vrijeme_pocetak_i=mysql_result($q0,$i,1);
				$vrijeme_kraj_i=mysql_result($q0,$i,2);
				$predmet_i=mysql_result($q0,$i,3);
				$tip_i=mysql_result($q0,$i,4);
				$labgrupa_i=mysql_result($q0,$i,5);
				if($predmet!=0){
					if($studij!=1){
						$q1=myquery("select obavezan from ponudakursa where studij=$studij and akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet_i limit 1");
						$obavezan_predmet_i=mysql_result($q1,0,0);
					}
				else{
					$q1=myquery("select obavezan from ponudakursa where akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet_i");
					$obavezan_predmet_i=mysql_result($q1,0,0);
				}
				}
				//ukoliko postoji preklapanje termina tačan je uslov ispod
				if (($vrijeme_pocetak_i>=$vrijeme_pocetak && $vrijeme_pocetak_i<$vrijeme_kraj)||($vrijeme_kraj_i>$vrijeme_pocetak && $vrijeme_kraj_i<=$vrijeme_kraj)){
					if($obavezan_predmet_i==1 && $tip_i=='P') {$greska_u_dodavanju_casa=1;$greska_preklapanje_sa_obaveznim_predmetom=1;}
					if($obavezan_predmet==1 && $tip=="P") {$greska_u_dodavanju_casa=1;$greska_preklapanje_obaveznog_predmeta=1;}
					if($predmet==$predmet_i && $labgrupa==$labgrupa_i) { $greska_u_dodavanju_casa=1; $greska_isti_predmet_ista_grupa=1;}
				}
			}
			$q1=myquery("select rs.sala,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.predmet,rs.tip from raspored_stavka rs,raspored r where dan_u_sedmici=$dan and r.akademska_godina=$akademska_godina");
			for($i=0;$i<mysql_num_rows($q1);$i++){
				$sala_i=mysql_result($q1,$i,0);
				$vrijeme_pocetak_i=mysql_result($q1,$i,1);
				$vrijeme_kraj_i=mysql_result($q1,$i,2);
				$predmet_i=mysql_result($q1,$i,3);
				//ukoliko postoji preklapanje termina tačan je uslov ispod
				if (($vrijeme_pocetak_i>=$vrijeme_pocetak && $vrijeme_pocetak_i<$vrijeme_kraj)||($vrijeme_kraj_i>$vrijeme_pocetak && $vrijeme_kraj_i<=$vrijeme_kraj)){
					if($sala==$sala_i){
						$greska_duplikat_sale=1;
						$greska_u_dodavanju_casa=1;
						break;
					}
				}
			}
			if($greska_u_dodavanju_casa==0){
				// vrsi se pretvaranje vremena u jedan broj u intervalu od 1-40 radi lakšeg rada sa prikazom rasporeda
				$vrijeme_pocetak=$vrijeme_pocetak_sati*4 + $vrijeme_pocetak_minute;
				$vrijeme_kraj=$vrijeme_kraj_sati*4 + $vrijeme_kraj_minute;
				if($tip=="P"){
					$q0=myquery("insert into raspored_stavka set id='NULL', raspored=$raspored_za_edit, dan_u_sedmici=$dan, predmet=$predmet,
						vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=0");
					$cas_uspjesno_dodan=1;
					zamgerlog("Unesen novi cas za akademsku $naziv_akademske_godine, studij $naziv_studija, semestar $semestar", 2);
					$q00=myquery("select max(id) from raspored_stavka");
					$id_unesene_stavke=mysql_result($q00,0,0);
					if($studij!=1){
						$q1=myquery("select studij,semestar from ponudakursa where predmet=$predmet and akademska_godina=$akademska_godina");
						for($i=0;$i<mysql_num_rows($q1);$i++){
							$studij_i=mysql_result($q1,$i,0);
							$semestar_i=mysql_result($q1,$i,1);
							if($studij_i!=1){
								$postoji_raspored=0;
								if($semestar_i==$semestar && $studij_i==$studij){
										$postoji_raspored=1;
								}
								else
								{	
									$q01=myquery("select semestar,studij,id from raspored where akademska_godina=$akademska_godina");
									for($j=0;$j<mysql_num_rows($q01);$j++){
										$studij_j=mysql_result($q01,$j,1);
										$semestar_j=mysql_result($q01,$j,0);
										$raspored_j=mysql_result($q01,$j,2);	
										if($semestar_i==$semestar_j && $studij_i==$studij_j){
											$postoji_raspored=1; break;
										}		
									}
								}
								if($postoji_raspored==0){
									$q02=myquery("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij_i, semestar=$semestar_i");
									zamgerlog("Kreiran novi raspored za akademsku $naziv_akademske_godine godinu", 2);
								}		
								$q2=myquery("select id from raspored where akademska_godina=$akademska_godina and semestar=$semestar_i and studij=$studij_i");
								if(mysql_num_rows($q2)>0){
								$raspored_i=mysql_result($q2,0,0);
									if($raspored_i!=$raspored_za_edit){
										$q3=myquery("insert into raspored_stavka set id='NULL', raspored=$raspored_i, dan_u_sedmici=$dan, predmet=$predmet,
											vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=0,dupla=$id_unesene_stavke");
										zamgerlog("Unesen novi cas za akademsku $naziv_akademske_godine godinu", 2);
									}	
								}
							}
						}
					}	
				}
				else{
					$q0=myquery("insert into raspored_stavka set id='NULL', raspored=$raspored_za_edit, dan_u_sedmici=$dan, predmet=$predmet,
						vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa");
					$cas_uspjesno_dodan=1;
					zamgerlog("Unesen novi cas za akademsku $naziv_akademske_godine, studij $naziv_studija, semestar $semestar", 2);
					$q00=myquery("select max(id) from raspored_stavka");
					$id_unesene_stavke=mysql_result($q00,0,0);
					if($studij!=1){
						$q1=myquery("select studij,semestar from ponudakursa where predmet=$predmet and akademska_godina=$akademska_godina");
						for($i=0;$i<mysql_num_rows($q1);$i++){
							$studij_i=mysql_result($q1,$i,0);
							$semestar_i=mysql_result($q1,$i,1);
							if($studij_i!=1){
								$postoji_raspored=0;
								if($semestar_i==$semestar && $studij_i==$studij){
										$postoji_raspored=1;
								}
								else
								{	
									$q01=myquery("select semestar,studij,id from raspored where akademska_godina=$akademska_godina");
									for($j=0;$j<mysql_num_rows($q01);$j++){
										$studij_j=mysql_result($q01,$j,1);
										$semestar_j=mysql_result($q01,$j,0);
										$raspored_j=mysql_result($q01,$j,2);	
										if($semestar_i==$semestar_j && $studij_i==$studij_j){
											$postoji_raspored=1; break;
										}		
									}
								}
								if($postoji_raspored==0){
									$q02=myquery("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij_i, semestar=$semestar_i");
									zamgerlog("Kreiran novi raspored za akademsku $naziv_akademske_godine godinu", 2);
								}		
								$q2=myquery("select id from raspored where akademska_godina=$akademska_godina and semestar=$semestar_i and studij=$studij_i");
								if(mysql_num_rows($q2)>0){
								$raspored_i=mysql_result($q2,0,0);
									if($raspored_i!=$raspored_za_edit){
										$q3=myquery("insert into raspored_stavka set id='NULL', raspored=$raspored_i, dan_u_sedmici=$dan, predmet=$predmet,
											vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$id_unesene_stavke");
										zamgerlog("Unesen novi cas za akademsku $naziv_akademske_godine godinu", 2);
									}	
								}
							}
						}
					}
				}		
			}
		}
		if ($_POST['akcija'] == "obrisi_cas" && check_csrf_token()) {
			$id_casa_za_brisanje = intval($_POST['id_casa_za_brisanje']);
			$q0=myquery("select dupla from raspored_stavka where id=$id_casa_za_brisanje");
			$dupla=mysql_result($q0,0,0);
			if($dupla==0){
				$q1=myquery("delete from raspored_stavka where id=$id_casa_za_brisanje");
				$q2=myquery("delete from raspored_stavka where dupla=$id_casa_za_brisanje");
			}
			else{
				$q1=myquery("delete from raspored_stavka where id=$dupla");
				$q2=myquery("delete from raspored_stavka where dupla=$dupla");
			}
			$uspjesno_obrisan_cas=1;
			zamgerlog("obrisan cas",4); // nivo 4: audit
		}
		print "<h4>Editovanje rasporeda za akademsku $naziv_akademske_godine godinu, studij $naziv_studija, $semestar. semestar:</h4>";
		print "<hr/>";
		print "<h4>Dodavanje novog časa:</h4>";
		if($greska_prazni_parametri_u_casu==1) print "<p class=\"crveno\">GREŠKA: Niste unijeli neki podatak.</p>";
		if($greska_neispravan_interval==1) print "<p class=\"crveno\">GREŠKA: Interval koji ste unijeli nije ispravan.</p>";
		if($greska_duplikat_sale==1) print "<p class=\"crveno\">GREŠKA: Sala koju ste odabrali je zauzeta u tom terminu.</p>";
		if($greska_preklapanje_sa_obaveznim_predmetom==1) print "<p class=\"crveno\">GREŠKA: Postoji preklapanje sa obaveznim predmetom.</p>";
		if($greska_preklapanje_obaveznog_predmeta==1) print "<p class=\"crveno\">GREŠKA: Obavezni predmet se ne može dodati jer je termin zauzet.</p>";
		if($greska_nevalja_termin==1) print "<p class=\"crveno\">GREŠKA: Nastava traje maksimalno do 21 sat.</p>";
		if($greska_isti_predmet_ista_grupa==1) print "<p class=\"crveno\">GREŠKA: Isti predmet i ista grupa u terminu koji se preklapa !</p>";
		if($cas_uspjesno_dodan==1) nicemessage("Čas je usješno dodan.");
		if($uspjesno_obrisan_cas==1) nicemessage("Čas je usješno obrisan.");
		if($studij!=1){
			$q4=myquery("select pk.predmet,p.kratki_naziv,pk.obavezan from ponudakursa pk, predmet p where pk.predmet=p.id and pk.studij=$studij and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar");
		}
		else{
			$q4=myquery("select distinct pk.predmet,p.kratki_naziv,pk.obavezan from ponudakursa pk, predmet p where pk.predmet=p.id and pk.akademska_godina=akademska_godina  and pk.semestar=$semestar ");
		}
		print genform("POST", "forma_za_unos_casa"); ?>
		<input type="hidden" name="akcija" id="akcija_novi_cas" value="unos_novog_casa">
		<table cellpadding="3" name="cas">
			<tr>
				<td>Dan:</td>
				<td>
					<select name="dan">
						<option value="0">---</option>
						<option value="1" 
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==1) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==1)){ 
								print " selected=\"selected\"";
							}
							?>
							>Ponedjeljak
						</option>
						<option value="2" 
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==2) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==2)){ 
								print " selected=\"selected\"";
							}
							?>
							>Utorak
						</option>
						<option value="3" 
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==3) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==3)){ 
								print " selected=\"selected\"";
							}
							?>
							>Srijeda
						</option>
						<option value="4" 
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==4) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==4)){ 
								print " selected=\"selected\"";
							}
							?>
							>Četvrtak
						</option>
						<option value="5" 
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==5) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==5)){ 
								print " selected=\"selected\"";
							}
							?>
							>Petak
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Tip:</td>
				<td>
					<select name="tip" id="tip" onchange="javascript:prikaziGrupe()">
						<option value="0">---</option>
						<option value="P"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['tip']=='P') || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['tip']=='P')){ 
								print " selected=\"selected\"";
							}
							?>
							>Predavanje
						</option>
						<option value="T"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['tip']=='T') || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['tip']=='T')){ 
								print " selected=\"selected\"";
							}
							?>
							>Tutorijal
						</option>
						<option value="L"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['tip']=='L') || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['tip']=='L')){ 
								print " selected=\"selected\"";
							}
							?>
							>Laboratorijska vježba
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Predmet:</td>
				<td>
					<select name="predmet" id="predmet" onchange="javascript:prikaziGrupe()">
						<? if(($_POST['akcija']!="unos_novog_casa_predfaza" && $_POST['akcija']!="unos_novog_casa") ||
							($_POST['akcija']=="unos_novog_casa" && $_POST['predmet']==0) || $cas_uspjesno_dodan==1 ||
							($_POST['akcija']=="unos_novog_casa_predfaza" && $_POST['predmet']==0)){
						?>
						<option value="0">---</option>
						<?
						}
						for($i=0;$i<mysql_num_rows($q4);$i++){
							$id_predmeta=mysql_result($q4,$i,0);
							$kratki_naziv=mysql_result($q4,$i,1);
							$obavezan=mysql_result($q4,$i,2);
							print "<option value=\"$id_predmeta\"";
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['predmet']==$id_predmeta) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['predmet']==$id_predmeta)){ 
								print " selected=\"selected\"";
							}
							print ">$kratki_naziv";
							if($obavezan==0) print " (izborni)";
							print "</option>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Sala:</td>
				<td>
					<select name="sala">
						<option value="0">---</option>
						<?
						$q0=myquery("select id,naziv from raspored_sala");
						for($i=0;$i<mysql_num_rows($q0);$i++){
							$id_sale=mysql_result($q0,$i,0);
							$naziv_sale=mysql_result($q0,$i,1);
							print "<option value=\"$id_sale\"";
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['sala']==$id_sale) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['sala']==$id_sale)){ 
									print " selected=\"selected\"";
							}
							print ">$naziv_sale</option>";
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Vrijeme početka:</td>
				<td>
					<select name="pocetakSat">
						<option value="-1">---</option>
						<?
						for($i=0;$i<=11;$i++){
							$j=$i+9;
							print "<option value=\"$i\"";
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakSat']==$i) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakSat']==$i)){ 
									print " selected=\"selected\"";
							}
							print ">$j</option>";
						}
						?>
					</select>
					:
					<select name="pocetakMin">
						<option value="0">---</option>
						<option value="1"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==1) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==1)){ 
									print " selected=\"selected\"";
							}
							?>
							>00
						</option>
						<option value="2"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==2) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==2)){ 
									print " selected=\"selected\"";
							}
							?>
							>15
						</option>
						<option value="3"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==3) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==3)){ 
									print " selected=\"selected\"";
							}
							?>
							>30
						</option>
						<option value="4"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==4) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==4)){ 
									print " selected=\"selected\"";
							}
							?>
							>45
						</option>
					</select>
				</td>	
			</tr>
			<tr>
				<td>Vrijeme kraja:</td>
				<td>
					<select name="krajSat">
						<option value="-1">---</option>
						<?
						for($i=0;$i<=12;$i++){
							$j=$i+9;
							print "<option value=\"$i\"";
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajSat']==$i) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajSat']==$i)){ 
									print " selected=\"selected\"";
							}
							print ">$j</option>";
						}
						?>
					</select>
					:
					<select name="krajMin">
						<option value="0">---</option>
						<option value="1"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==1) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==1)){ 
									print " selected=\"selected\"";
							}
							?>
							>00
						</option>
						<option value="2"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==2) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==2)){ 
									print " selected=\"selected\"";
							}
							?>
							>15
						</option>
						<option value="3"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==3) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==3)){ 
									print " selected=\"selected\"";
							}
							?>
							>30
						</option>
						<option value="4"
							<?
							if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==4) || 
								($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==4)){ 
									print " selected=\"selected\"";
							}
							?>
							>45
						</option>
					</select>
				</td>	
			</tr>
			<tr>
				<td>Grupa:</td>
				<td>
					<select name="labgrupa" id="labgrupa_za_cas" 
						<? 
						if ($_POST['akcija']!="unos_novog_casa_predfaza" && $_POST['akcija']!="unos_novog_casa") print " disabled=\"disabled\"";
						elseif (($_POST['akcija']=="unos_novog_casa" || $_POST['akcija']=="unos_novog_casa_predfaza") && intval($_POST['predmet'])==0) print " disabled=\"disabled\"";
						elseif ($_POST['akcija']=="unos_novog_casa_predfaza" && $_POST['tip']=='P') print " disabled=\"disabled\"";
						elseif ($_POST['akcija']=="unos_novog_casa" && $_POST['tip']=='P' && $greska_u_dodavanju_casa==1) print " disabled=\"disabled\"";
						elseif (($_POST['akcija']=="unos_novog_casa" || $_POST['akcija']=="unos_novog_casa_predfaza") && $_POST['tip']=='0') print " disabled=\"disabled\"";
						?>
					>
						<option value="0">---</option>
						<? 
						if($_POST['akcija']=="unos_novog_casa_predfaza" || ($_POST['akcija']=="unos_novog_casa" && intval($_POST['predmet'])!=0)){
							$id_predmeta=intval($_POST['predmet']);
							$q0=myquery("select id,naziv from labgrupa where predmet=$id_predmeta and akademska_godina=$akademska_godina");
							for($i=0;$i<mysql_num_rows($q0);$i++){
								$id_labgrupe=mysql_result($q0,$i,0);
								$naziv_labgrupe=mysql_result($q0,$i,1);
								print "<option value=\"$id_labgrupe\"";
								if(($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['labgrupa']==$id_labgrupe) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['labgrupa']==$id_labgrupe)){ 
										print " selected=\"selected\"";
								}
								print ">$naziv_labgrupe</option>";
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td align="right"><input type="submit" value=" Dodaj čas"></form></td>
			</tr>
		</table>
		<?
		print "<p><a href=\"?sta=studentska/raspored1&edit_rasp=1\">vrati se nazad</a></p>";
		print "<a href=\"?sta=studentska/raspored1\">vrati se na početnu</a>";
		?>
		<?=genform("POST","brisanjecasa")?>
		<input type="hidden" name="akcija" value="obrisi_cas">
		<input type="hidden" name="id_casa_za_brisanje" id="id_casa_za_brisanje" value=""></form>
		<h4>Izgled rasporeda:</h4>
		<table class="raspored" border="1" cellspacing="0">
			<tr>
				<th>
					<p>Sat/</p>
					<p>Dan</p>
				</th>
				<?
				for($i=9;$i<=20;$i++){
					$j=$i+1;
				?>
					<th>
						<p class="bold"><? print "$i";?></p>
						<p><? print "00";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "15";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "30";?></p>
					</th>
					<th>
						<p><br></p>
						<p><? print "45";?></p>
					</th>
				<?
				}
				?>
			</tr>
			<?
			// petlja za 5 dana u sedmici
			for($i=1;$i<=5;$i++){
				print "<tr>";
				$q0=myquery("select vrijeme_pocetak,vrijeme_kraj from raspored_stavka where dan_u_sedmici=$i and raspored=$raspored_za_edit");
				// sada je potrebno naći maksimalni broj preklapanja termina da bi znali koliki je rowspan potreban za dan $i
				// poredimo svaki interval casa sa svakim
				$broj_preklapanja=array();
				for($j=0;$j<40;$j++){
					$broj_preklapanja[]=0;
				}
				for($j=0;$j<mysql_num_rows($q0);$j++){
					$pocetak=mysql_result($q0,$j,0);
					$kraj=mysql_result($q0,$j,1);
					for($k=$pocetak;$k<$kraj;$k++) $broj_preklapanja[$k]++;
				}
				$max_broj_preklapanja=max($broj_preklapanja);
				if($i==1) $dan_tekst="PON";
				elseif ($i==2) $dan_tekst="UTO";
				elseif ($i==3) $dan_tekst="SRI";
				elseif ($i==4) $dan_tekst="ČET";
				elseif ($i==5) $dan_tekst="PET";
				// sada pravimo dvodimenzionalni niz, koji predstavlja zauzetost termina u određenom redu
				$zauzet=array();
				for($j=0;$j<$max_broj_preklapanja;$j++){
					$zauzet=array();
				}
				for($j=0;$j<$max_broj_preklapanja;$j++){
					for($k=0;$k<40;$k++){
						$zauzet[$j][]=0;
					}
				}
				// zauzet[1][0]=1 znaci da je termin 1 zauzet u drugom redu  
				
				$q1=myquery("select id,raspored,predmet,vrijeme_pocetak,vrijeme_kraj,sala,tip,labgrupa from raspored_stavka where dan_u_sedmici=$i and raspored=$raspored_za_edit order by id");
				$gdje=array();
				$gdje[0]=array(); // indeks 0 predstavlja id stavke rasporeda
				$gdje[1]=array(); // indeks 1 predstavlja red u kojem ta stavka ide
				// primjer 
				// gdje[0][0]=5 znaci da je id prve stavke 5
				// gdje[1][0]=3 znaci da stavka 1 ide u 4. red
				// [0] pretstavlja prvu stavku jer indeksi kreću od nule i druga kolona treba biti ista-- u ovom slucaju [0]
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$gdje[0][$j]=$id_stavke;// i ovo vise ne diramo jer znamo koji je id stavke na osnovu nepoznate $j
					$gdje[1][$j]=0; // postavljamo na nulu jer još ne znamo gdje ide određena stavka
				}
				for($j=0;$j<mysql_num_rows($q1);$j++){
					$id_stavke=mysql_result($q1,$j,0);
					$pocetak=mysql_result($q1,$j,3);
					$kraj=mysql_result($q1,$j,4);
					for($k=0;$k<$max_broj_preklapanja;$k++){
						$zauzet_red=0;
						while($pocetak!=$kraj){
							if($zauzet[$k][$pocetak-1]==1){
								$zauzet_red=1;// ako je uslov ispunjen nađen je barem jedan zauzet red
								break;
							}
							$pocetak++;
						}
						if($zauzet_red==0){
							// ako nije zauzet termin u tom redu dodajemo termin u taj red i prekidamo petlju
							$gdje[1][$j]=$k; // $stavka $j ide u red $k
							//sada proglasavamo termin zauzetim u tom redu $k+1
							$pocetak=mysql_result($q1,$j,3);
							while($pocetak!=$kraj){
								$zauzet[$k][$pocetak-1]=1;// termin $pocetak se zauzima u redu $k+1
								$pocetak++;
							}
						}
						if($zauzet_red==0) break;
					}
				}
				print "<td rowspan=\"$max_broj_preklapanja\">$dan_tekst</td>";
				for($j=0;$j<$max_broj_preklapanja;$j++){
					if($j>0) print "</tr><tr>";
					$zadnji=1;
					for($m=1;$m<=48;$m++){
						for($k=0;$k<mysql_num_rows($q1);$k++){
							$id_stavke=mysql_result($q1,$k,0);
							$predmet=mysql_result($q1,$k,2);
							$q2=myquery("select kratki_naziv from predmet where id=$predmet");
							$predmet_naziv=mysql_result($q2,0,0);
							$pocetak=mysql_result($q1,$k,3);
							$kraj=mysql_result($q1,$k,4);
							$sala=mysql_result($q1,$k,5);
							$q3=myquery("select naziv from raspored_sala where id=$sala");
							$sala_naziv=mysql_result($q3,0,0);
							$tip=mysql_result($q1,$k,6);
							$labgrupa=mysql_result($q1,$k,7);
							if($labgrupa!=0){
								$q4=myquery("select naziv from labgrupa where id=$labgrupa");
								$labgrupa_naziv=mysql_result($q4,0,0);
							}
							$interval=$kraj-$pocetak;
							if($gdje[1][$k]==$j && $pocetak==$m){
							for($n=$zadnji;$n<$pocetak;$n++) print "<td></td>";
							$zadnji=$kraj;
							$vrijemePocS=floor(($pocetak-1)/4+9);
							$vrijemePocMin=$pocetak%4;
							if($vrijemePocMin==1) $vrijemePocM="00";
							elseif($vrijemePocMin==2) $vrijemePocM="15";
							elseif($vrijemePocMin==3) $vrijemePocM="30";
							elseif($vrijemePocMin==0) $vrijemePocM="45";
							$vrijemeP="$vrijemePocS:$vrijemePocM";
							$vrijemeKrajS=floor(($kraj-1)/4+9);
							$vrijemeKrajMin=$kraj%4;
							if($vrijemeKrajMin==1) $vrijemeKrajM="00";
							elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
							elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
							elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
							$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
							$q3=myquery("select obavezan from ponudakursa where predmet=$predmet");
							if(mysql_num_rows($q3)>0) $obavezan=mysql_result($q3,0,0); 
							print "
								<td colspan=\"$interval\">
									<table class=\"cas\" align=\"center\">
										<tr>
											<td>Tip:</td>
											<td><p class=\"bold\">$tip</p></td>
										</tr>
										<tr>
											<td>Predmet:</td>
											<td><p class=\"bold\">$predmet_naziv";
								if($obavezan==0) print " (IZB) ";
							print "
											</p></td>
										</tr>
										<tr>
											<td>Sala:</td>
											<td><p class=\"bold\">$sala_naziv</p></td>
										</tr>";

							if($tip!='P'){
								print "
										<tr>
											<td>Grupa:</td>
											<td><p class=\"bold\">$labgrupa_naziv</p></td>
										</tr>";
							}
								print "
										<tr>
											<td>Vrijeme:</td>
											<td><p class=\"bold\">$vrijemeP-$vrijemeK</p></td>
										</tr>
										<tr>
											<td></td>
											<td><p><a  href=\"javascript:onclick=brisanje_casa('$id_stavke')\"> obriši čas </a></p></td>
										</tr>
									</table>
								</td>";
							}
						}
					}							
				}
				print "</tr>";
			}
			?>
			
		</table>
		
	<?
	}
	// u slučaju da se ne radi o izmjeni postojećeg rasporeda prikazuje se html kod za dodavanje novog rasporeda
	else{
		if($uspjesno_unesen_raspored==1) nicemessage("Uspješno je unesen novi raspored.");
		if($uspjesno_obrisan_raspored==1) nicemessage("Raspored je uspješno obrisan.");
		print "<h4>Dodavanje novog rasporeda:</h4>";
		print genform("POST", "forma_za_unos_rasporeda"); ?>
		<input type="hidden" name="akcija" value="unos_novog_rasporeda">
		<? if($greska_postoji_raspored==1) print "<p class=\"crveno\">Postoji raspored sa tim parametrima.</p>";?>
		<table id="raspored" cellpadding="3">
		<tr>
			<td align="left" width="120">Akademska godina:</td>
			<td>
				<select name="akademska_godina">
				<?
					$q0 = mysql_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
					while ($r0 = mysql_fetch_row($q0)) {
						print "<option value=\"$r0[0]\"";
						if($greska_postoji_raspored==1){
							if($_POST['akademska_godina']==$r0[0]) print " selected";
						}
						else{
							if ($r0[2]==1) print " selected";
						}
						print ">$r0[1]</option>\n";
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="left" width="120">Studij:</td>
			<td>
				<select name="studij" id="studij" onchange="javascript:promjeniSemestar()" >
				<?
					$q0 = mysql_query("select id,naziv from studij");
					while ($r0 = mysql_fetch_row($q0)) {
						print "<option value=\"$r0[0]\"";
						if($greska_postoji_raspored==1 && $r0[0]==$_POST['studij']){ 
							print " selected";
						}
						print ">$r0[1]</option>\n";
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="left" width="120">Semestar:</td>
			<td>
				<div id="pgs" <?if($greska_postoji_raspored==1 && $_POST['studij']!=1) print " style=\"display:none\""; ?>>
	            	<select name="semestar">
	                	<option value="1" <? if($greska_postoji_raspored==1 && $_POST['semestar']==1) print " selected";?>> 1</option>
	                    <option value="2" <? if($greska_postoji_raspored==1 && $_POST['semestar']==2) print " selected";?>> 2</option>
	               	</select>
	            </div>
	            <div id="ostalo" 
	            	<?
	            	if($greska_postoji_raspored==1 && $_POST['studij']!=1) print " style=\"display:\"\"\"";
	            	else print " style=\"display:none\""; 
	            	?>
	            >
	            	<select name="semestar2">
	                	<option value="3" <? if($greska_postoji_raspored==1 && $_POST['semestar']==3) print " selected";?>> 3</option>
	                    <option value="4" <? if($greska_postoji_raspored==1 && $_POST['semestar']==4) print " selected";?>> 4</option>
	                    <option value="5" <? if($greska_postoji_raspored==1 && $_POST['semestar']==5) print " selected";?>> 5</option>
	                    <option value="6" <? if($greska_postoji_raspored==1 && $_POST['semestar']==6) print " selected";?>> 6</option>
	               	</select>
	          	</div>	
			</td>
			<? if($greska_prazan_kapacitet==1) print "<td><p class=\"crveno\">niste unijeli kapacitet</p></td>";?>
			<? if($greska_kapacitet_nije_broj==1) print "<td><p class=\"crveno\">kapacitet treba biti broj</p></td>";?>
		</tr>
		<tr>
			<td></td>
			<td align="right"><input type="submit" value=" Dodaj "></td>
		</tr>
		</table>
		</form>
	<?
	print "<a href=\"?sta=studentska/raspored1\">vrati se na početnu</a>";
	?>
	<?=genform("POST","brisanjerasporeda")?>
	<input type="hidden" name="akcija" value="obrisi_raspored">
	<input type="hidden" name="id_rasporeda_za_brisanje" id="id_rasporeda_za_brisanje" value=""></form>
	
	<h4>Postojeći rasporedi:</h4>
	<table class="sale" border="1" cellspacing="0">
		<?
		$q1=myquery("select id,studij,akademska_godina,semestar from raspored order by id");
		if(mysql_num_rows($q1)<1) print "<p>Nema kreiranih rasporeda</p>";
		else{
		?>
			<th>Studij</th>
			<th>Akademska godina</th>
			<th>Semestar</th>
			<th colspan="2">Akcije</th>
			<?  
			for($i=0;$i<mysql_num_rows($q1);$i++){
				$id_rasporeda=mysql_result($q1,$i,0);
				$studij=mysql_result($q1,$i,1);
				$akademska_godina=mysql_result($q1,$i,2);
				$semestar=mysql_result($q1,$i,3);;
				$q2=myquery("select naziv from studij where id=$studij");
				$naziv_studija=mysql_result($q2,0,0);
				$q3=myquery("select naziv from akademska_godina where id=$akademska_godina");
				$naziv_akademske_godine=mysql_result($q3,0,0);
				print "<tr>";
				print "<td>$naziv_studija</td>";
				print "<td>$naziv_akademske_godine</td>";
				print "<td>$semestar</td>";
				print "<td width=\"80\"><a  href=\"?sta=studentska/raspored1&edit_rasp=1&raspored_za_edit=$id_rasporeda\"> izmijeni </a></td>";
				print "<td width=\"80\"><a  href=\"javascript:onclick=brisanje_rasporeda('$id_rasporeda')\"> obriši </a></td>";
				print "</tr>";
			}
		}
		?>
	</table>
	<? 
	}
	?>
	<script language="JavaScript">
	function brisanje_rasporeda(id_rasporeda) {
		var a = confirm("Obrisati raspored i kompletan sadržaj! Da li ste sigurni?");
		if (a) {
			document.getElementById('id_rasporeda_za_brisanje').value=id_rasporeda;
			document.brisanjerasporeda.submit();
		}
	}
	</script>
	
<?
	// kraj stranice za rad sa rasporedom
}

// početna stranica modula raspored
else{
	print "<ul>";
	print "<li><a href=\"?sta=studentska/raspored1&edit_sala=1\">SALE</a></li>";
	print "<li><a href=\"?sta=studentska/raspored1&edit_rasp=1\">RASPORED</a></li>";
}
?>
</ul>
</td></tr>
</table>
</center>
<?	
}
?>

	