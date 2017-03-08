<? 

// STUDENTSKA/RASPORED1 - administracija rasporeda



function studentska_raspored1(){
?>
<link href="static/css/raspored1.css" rel="stylesheet" type="text/css">
<?
	global $userid,$user_siteadmin,$user_studentska;
	
	// Provjera privilegija
	
		if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska");
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
			 slika.src="static/images/minus.png";
		}
		else {
			x.style.display="none";
			slika.src="static/images/plus.png";
		}
	}

	function prikaziGrupe() {
		document.getElementById('akcija_novi_cas').value="unos_novog_casa_predfaza";
		document.forma_za_unos_casa.submit();
	}
	
	function dodajCasSaKonfliktima() {
		document.getElementById('cas_sa_konfliktima').value="1";
		document.forma_za_unos_casa.submit();
	}
					
	function brisanje_casa(id_casa) {
		var a = confirm("Obrisati čas! Da li ste sigurni?");
		if (a) {
			document.getElementById('id_casa_za_brisanje').value=id_casa;
			document.brisanjecasa.submit();
		}
	}

	function prikazKonflikata(){
		var x=document.getElementById('prikaz_konflikata');
		var slika=document.getElementById('slika_konflikti');
		if(x.style.display=="none"){
			 x.style.display="inline";
			 slika.src="static/images/minus.png";
		}
		else {
			x.style.display="none";
			slika.src="static/images/plus.png";
		}
	}

	function brisanje_rasporeda(id_rasporeda) {
		var a = confirm("Obrisati raspored i kompletan sadržaj! Da li ste sigurni?");
		if (a) {
			document.getElementById('id_rasporeda_za_brisanje').value=id_rasporeda;
			document.brisanjerasporeda.submit();
		}
	}

	function kopiranjeSvihRasporeda() {
		var a = confirm("Ovom akcijom brišete eventualno postojeći sadržaj destinacijskog rasporeda!!");
		if (a) {
			document.kopiranjerasporeda.submit();
		}
	}
	
</script>

<?


function prikaziKonflikte($id_stavke_rasporeda,$ispis=0){
	$q0=db_query("select r.akademska_godina,r.semestar from raspored r, raspored_stavka rs where r.id=rs.raspored and rs.id=$id_stavke_rasporeda");
	$akademska_godina=db_result($q0,0,0);
	$semestar=db_result($q0,0,1);
	$semestar_je_neparan= $semestar % 2;
	$q1=db_query("select predmet,vrijeme_pocetak,vrijeme_kraj,tip,labgrupa,dan_u_sedmici,dupla from raspored_stavka where id=$id_stavke_rasporeda");
	$predmet=db_result($q1,0,0);
	$pocetak=db_result($q1,0,1);
	$kraj=db_result($q1,0,2);
	$tip=db_result($q1,0,3);
	$labgrupa=db_result($q1,0,4);
	$dan=db_result($q1,0,5);
	$dupla=db_result($q1,0,6);
	if($labgrupa!=0){
		if($labgrupa!=-1){
			$q4=db_query("select naziv from labgrupa where id=$labgrupa");
			$labgrupa_naziv=db_result($q4,0,0);
		}		
	}
	$interval=$kraj-$pocetak;
	$konflikt=array();
	$konflikt['student']=array();
	$konflikt['predmet']=array();
	$konflikt['pocetak']=array();
	$konflikt['kraj']=array();
	$q2=db_query("select rs.sala,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.predmet,rs.tip,rs.labgrupa,r.semestar,rs.id 
	from raspored_stavka rs,raspored r where rs.dan_u_sedmici=$dan and rs.raspored=r.id and r.akademska_godina=$akademska_godina 
	and r.semestar mod 2 = $semestar_je_neparan and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1");
		for($f=0;$f<db_num_rows($q2);$f++){
			$sala_i=db_result($q2,$f,0);
			$vrijeme_pocetak_i=db_result($q2,$f,1);
			$vrijeme_kraj_i=db_result($q2,$f,2);
			$predmet_i=db_result($q2,$f,3);
			$tip_i=db_result($q2,$f,4);
			$labgrupa_i=db_result($q2,$f,5);
			$semestar_i=db_result($q2,$f,6);
			$id_stavke_i=db_result($q2,$f,7);
			if(($id_stavke_i==$id_stavke_rasporeda)||($id_stavke_i==$dupla)) continue;
			if($tip_i=="P") $labgrupa_i=0;

			//ukoliko postoji preklapanje termina (sada gledamo sve rasporede) tačan je uslov ispod
			if (($vrijeme_pocetak_i>=$pocetak && $vrijeme_pocetak_i<$kraj)||($vrijeme_kraj_i>$pocetak && $vrijeme_kraj_i<=$kraj)){
				// provjera preklapanja studenata u terminu
				if($tip=="P"){
					$q_prvi=db_query("select sp.student from student_predmet sp,ponudakursa pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar");
				}
				else{
					$q_prvi=db_query("select student from student_labgrupa where labgrupa=$labgrupa");
				}
				if($labgrupa_i==0){
					$q_drugi=db_query("select sp.student from student_predmet sp,ponudakursa pk where sp.predmet=pk.id and pk.predmet=$predmet_i and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar_i");
				}
				else{
					$q_drugi=db_query("select student from student_labgrupa where labgrupa=$labgrupa_i");
				}
				for($p=0;$p<db_num_rows($q_prvi);$p++){
					$student_p=db_result($q_prvi,$p,0);
					for($d=0;$d<db_num_rows($q_drugi);$d++){
						$student_d=db_result($q_drugi,$d,0);
						if($student_p==$student_d){
							// pronadjen je student u konfliktu i upisujemo ga u niz konflikata
							$konflikt['student'][]=$student_p;
							$konflikt['predmet'][]=$predmet_i;
							$konflikt['pocetak'][]=$vrijeme_pocetak_i;
							$konflikt['kraj'][]=$vrijeme_kraj_i;
						}		
					}	
				}				
			}
		}
		$broj_konflikata=count($konflikt['student']);
		
		
		if($ispis==1){
			print "<p>Prikaz konflikata:</p>";
			print "<ul>";
					for($i=0;$i<count($konflikt['student']);$i++){
						$student=$konflikt['student'][$i];
						$predmet=$konflikt['predmet'][$i];
						$pocetak=$konflikt['pocetak'][$i];
						$kraj=$konflikt['kraj'][$i];
						$vrijemePocS=floor(($pocetak-1)/4+8);
						$vrijemePocMin=$pocetak%4;
						if($vrijemePocMin==1) $vrijemePocM="00";
						elseif($vrijemePocMin==2) $vrijemePocM="15";
						elseif($vrijemePocMin==3) $vrijemePocM="30";
						elseif($vrijemePocMin==0) $vrijemePocM="45";
						$vrijemeP="$vrijemePocS:$vrijemePocM";
						$vrijemeKrajS=floor(($kraj-1)/4+8);
						$vrijemeKrajMin=$kraj%4;
						if($vrijemeKrajMin==1) $vrijemeKrajM="00";
						elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
						elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
						elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
						$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
						$q1=db_query("select ime, prezime from osoba where id=$student");
						$ime=db_result($q1,0,0);
						$prezime=db_result($q1,0,1);
						$q2=db_query("select naziv from predmet where id=$predmet");
						$naziv_predmeta=db_result($q2,0,0);
						print "<li><p><b>$ime $prezime</b> ima predmet <b>$naziv_predmeta</b> u terminu <b>$vrijemeP-$vrijemeK</b></p></li>";
					}
					print "</ul>";
		}
		
		return  $broj_konflikata;
}
?>


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
			$ime_sale=db_escape($_POST['ime_sale']);
			$q0=db_query("select naziv from raspored_sala");
			for($i=0;$i<db_num_rows($q0);$i++)
			{
				if(db_result($q0, $i,0)==$ime_sale){
					$greska=1; $greska_postoji_sala=1;
				}
			}
		}
		$tip_sale=db_escape($_POST['tip_sale']);
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
			$tip_sale=db_escape($_POST['tip_sale']);
			$q0=db_query("select * from raspored_sala");
			if(db_num_rows($q0)>0){
				$q1=db_query("select max(id) from raspored_sala");
				$id_nove_sale=db_result($q1,0,0)+1;
			}
			else $id_nove_sale=1;
			$q0=db_query("insert into raspored_sala set id=$id_nove_sale, naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale'");
			$uspjesno_unesena_sala=1;
			zamgerlog("upisana nova sala $ime_sale", 2); // nivo 2 je izmjena podataka u bazi
			zamgerlog2("upisana nova sala", $id_nove_sale, 0, 0, $ime_sale);
		}
	}

	//uslov ispod je ispunjen ako je prihvaćena forma za unos nove sale
	elseif ($_POST['akcija'] == 'editovanje_sale' && check_csrf_token()) {
		$id_sale_za_edit=intval($_POST['id_sale_za_edit']);
		$q0=db_query("select naziv from raspored_sala where id=$id_sale_za_edit");
		$stari_naziv_sale=db_result($q0,0,0);
		if(empty($_POST['edit_ime_sale'])){
			$greska=1; $greska_prazno_ime_sale=1;
		}
		else{ // ako ime sale nije prazno izvršava se sljedeći kod
			$ime_sale=db_escape($_POST['edit_ime_sale']);
			$q1=db_query("select naziv from raspored_sala");
			for($i=0;$i<db_num_rows($q1);$i++)
			{
				if(db_result($q1, $i,0)==$ime_sale && $ime_sale!=$stari_naziv_sale){
					$greska=1; $greska_postoji_sala=1;
				}
			}
		}
		$tip_sale=db_escape($_POST['edit_tip_sale']);
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
			$tip_sale=db_escape($_POST['edit_tip_sale']);
			$q1=db_query("update raspored_sala set naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale' where id=$id_sale_za_edit");
			$uspjesno_editovana_sala=1;
			zamgerlog("editovana sala $stari_naziv_sale", 2); // nivo 2 je izmjena podataka u bazi
			zamgerlog2("editovana sala", $id_sale_za_edit);
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
					$q0=db_query("select naziv from raspored_sala");
					for($j=0;$j<db_num_rows($q0);$j++)
					{
						if(db_result($q0,$j,0)==$ime_sale){
							$greska_masovnog_unosa=1; $greska_postoji_sala_u_redu[]=$i;
						}
					}
				}
			}
		}
		

		if($greska_masovnog_unosa==0){
			$unesene_sale=array();
			foreach ($redovi as $red){
				$red=trim($red);
				if(strlen($red)<1) continue; // prazan red
				if($_POST['separator']==1) {
					list($ime_sale,$tip_sale,$kapacitet)=explode(",", $red);
					$ime_sale=trim($ime_sale);
				}
				elseif ($_POST['separator']==2) {
					list($ime_sale,$tip_sale,$kapacitet)=explode("\t", $red);
					$ime_sale=trim($ime_sale);
				}
				$unesene_sale[]=$ime_sale;
			}
			$postoji_dupla=false;
			for($i=0;$i<count($unesene_sale);$i++){
				$prva=$unesene_sale[$i];
				for($j=$i+1;$j<count($unesene_sale);$j++){
					$druga=$unesene_sale[$j];
					if($prva==$druga) { $greska_masovnog_unosa=1; $greska_postoje_duple_sale=1; }
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
				$q0=db_query("select * from raspored_sala");
				if(db_num_rows($q0)>0){
					$q1=db_query("select max(id) from raspored_sala");
					$id_nove_sale=db_result($q1,0,0)+1;
				}
				else $id_nove_sale=1;
				$q0=db_query("insert into raspored_sala set id=$id_nove_sale, naziv='$ime_sale', kapacitet=$kapacitet, tip='$tip_sale'");
				$unesene_sale[]=$ime_sale;
				zamgerlog("masovni unos sala: Unesena je sala $ime_sale", 2);
				zamgerlog2("upisana nova sala (masovni unos)", $id_nove_sale, 0, 0, $ime_sale);
			}
			$uspjesan_masovni_unos_sala=1;
		} 
	}
	
	
	
	// Obrisi salu
	if ($_POST['akcija'] == "obrisi_salu" && check_csrf_token()) {
		$id_sale_za_brisanje = intval($_POST['id_sale_za_brisanje']);
		$q1=db_query("select naziv from raspored_sala where id=$id_sale_za_brisanje");
		$naziv=db_result($q1,0,0);
		$q2=db_query("delete from raspored_sala where id=$id_sale_za_brisanje");
		$uspjesno_obrisana_sala=1;
		zamgerlog("obrisana sala $naziv",4);
		zamgerlog2("obrisana sala", $id_sale_za_brisanje);
	}
	
	if(isset($_REQUEST['sala_za_edit'])) {?>
	<div id="prikaz_za_editovanje_sale">
		<?
		$id_sale_za_edit=$_REQUEST['sala_za_edit'];
		$q0=db_query("select naziv,tip,kapacitet from raspored_sala where id=$id_sale_za_edit");
		$ime_sale=db_result($q0,0,0);
		$tip_sale=db_result($q0,0,1);
		$kapacitet=db_result($q0,0,2);
		if(isset($uspjesno_editovana_sala) && $uspjesno_editovana_sala==1) nicemessage("Sala je uspješno izmijenjena.");
		print "<p><a href=\"?sta=studentska/raspored1&edit_sala=1\">vrati se nazad na unos sala</a></p>";
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
	</div>
	<?
	} 
	else { ?>
	
	<div id="normalni_prikaz">
	<?
	print "<p><a href=\"?sta=studentska/raspored1\">vrati se na početnu</a></p>";
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
	if($greska_masovnog_unosa==1 && $greska_postoje_duple_sale==1) print "<p class=\"crveno\">GREŠKA: Unijeli ste sale sa istim imenom!</p>";
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
	
	<a href="#" onclick="daj_uputstvo()"><img id="slika_za_mas_unos_sala" src = "static/images/plus.png" border="0" align="left" />Uputstvo za masovni unos</a>
	
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
	<?} // završava se div koji se prikazuje kada se ne radi o editovanju sale?>

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
		$q1=db_query("select id,naziv,tip,kapacitet from raspored_sala order by id");
		if(db_num_rows($q1)<1) print "<p>Nema kreiranih sala</p>";
		else{
		?>
			<th>Ime sale</th>
			<th>Tip sale</th>
			<th>Kapacitet</th>
			<th colspan="2">Akcije</th>
			<?  
			for($i=0;$i<db_num_rows($q1);$i++){
				$id=db_result($q1,$i,0);
				$ime_sale=db_result($q1,$i,1);
				$tip_sale=db_result($q1,$i,2);
				$kapacitet=db_result($q1,$i,3);;
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


// uslov ispod se koristi ako prikazujemo stranicu za rad sa rasporedom
else{
	if ($_POST['akcija'] == 'unos_novog_rasporeda' && check_csrf_token()) {
		$akademska_godina=intval($_POST['akademska_godina']);
		$studij=intval($_POST['studij']);
		$semestar=intval($_POST['semestar']);
		$q0=db_query("select akademska_godina,studij,semestar from raspored");
		$greska_postoji_raspored=0;
		for($i=0;$i<db_num_rows($q0);$i++){
			if(db_result($q0,$i,0)==$akademska_godina && db_result($q0,$i,1)==$studij && db_result($q0,$i,2)==$semestar){
				$greska_postoji_raspored=1;
			}
		}
		if($greska_postoji_raspored==0){
			$q0=db_query("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij, semestar=$semestar");
			$uspjesno_unesen_raspored=1;
			zamgerlog("unesen novi raspored", 2);
			zamgerlog2("unesen novi raspored", $akademska_godina, $studij, $semestar);
		}
	}
	
	// Obrisi raspored
	if ($_POST['akcija'] == "obrisi_raspored" && check_csrf_token()) {
		$id_rasporeda_za_brisanje = intval($_POST['id_rasporeda_za_brisanje']);
		$q1=db_query("select studij,akademska_godina,semestar from raspored where id=$id_rasporeda_za_brisanje");
		$studij=db_result($q1,0,0);
		$akademska_godina=db_result($q1,0,1);
		$semestar=db_result($q1,0,2);
		$q2=db_query("select naziv from studij where id=$studij");
		$naziv_studija=db_result($q2,0,0);
		$q3=db_query("select naziv from akademska_godina where id=$akademska_godina");
		$naziv_akademske_godine=db_result($q3,0,0);
		$q3=db_query("delete from raspored where id=$id_rasporeda_za_brisanje");
		$q4=db_query("delete from raspored_stavka where raspored=$id_rasporeda_za_brisanje");
		$uspjesno_obrisan_raspored=1;
		zamgerlog("obrisan raspored za akademsku $naziv_akademske_godine godinu, studij $naziv_studija, semestar $semestar",4); // nivo 4: audit
		zamgerlog2("obrisan raspored", $id_rasporeda_za_brisanje);
	}
	
	
	//kopiranje rasporeda iz jedne akademske godine u drugu
	if ($_POST['akcija'] == "kopiraj_raspored" && check_csrf_token()) {
		$izvor = intval($_POST['izvor']);
		$odrediste = intval($_POST['odrediste']);
		$greska_kopiranja_rasporeda=false;
		if($izvor==$odrediste) { $greska_kopiranja_rasporeda=true;niceerror("Izvor i destinacija ne mogu biti isti!");}
		if($greska_kopiranja_rasporeda==false){
			$q0=db_query("select naziv from akademska_godina where id=$odrediste");
			$naziv_akademske_godine=db_result($q0,0,0);
			$q1=db_query("delete from raspored where akademska_godina=$odrediste");
			$q2=db_query("select id from raspored where akademska_godina=$odrediste");
			for($i=0;$i<db_num_rows($q2);$i++){
				$id_odr=db_result($q2,$i,0);
				$q3=db_query("delete from raspored_stavka rs where raspored=$id_odr");
			}	
			zamgerlog("obrisani svi rasporedi u akademskoj $naziv_akademske_godine godini",4);
			zamgerlog2("obrisani svi rasporedi u akademskoj godini", $odrediste);
			$q4=db_query("select studij,semestar,id from raspored where akademska_godina=$izvor");
			$broj_redova=db_num_rows($q4);
			for($i=0;$i<$broj_redova;$i++){
				$studij=db_result($q4,$i,0);
				$semestar=db_result($q4,$i,1);
				$id_rasp_izvora=db_result($q4,$i,2);
				$q5=db_query("insert into raspored set id='NULL', akademska_godina=$odrediste, studij=$studij, semestar=$semestar");
				$q6=db_query("select rs.dan_u_sedmici,rs.predmet,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.sala,rs.tip,rs.labgrupa,rs.dupla,rs.id
				from raspored_stavka rs,raspored r where rs.raspored=r.id and r.id=$id_rasp_izvora and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1 ");
				$q7=db_query("select max(id) from raspored");
				$id_rasp_odredista=db_result($q7,0,0);
				$q8=db_query("select naziv from akademska_godina where id=$izvor");
				$q9=db_query("select naziv from akademska_godina where id=$odrediste");
				$naziv_izvora=db_result($q8,0,0);
				$naziv_odredista=db_result($q9,0,0);
				for($j=0;$j<db_num_rows($q6);$j++){
					$dan=db_result($q6,$j,0);
					$predmet=db_result($q6,$j,1);
					$pocetak=db_result($q6,$j,2);
					$kraj=db_result($q6,$j,3);
					$sala=db_result($q6,$j,4);
					$tip=db_result($q6,$j,5);
					$labgrupa=db_result($q6,$j,6);
					if($labgrupa!=0){
						$q71=db_query("select naziv,virtualna from labgrupa where akademska_godina=$izvor and predmet=$predmet and id=$labgrupa");
						$naziv_grupe=db_result($q71,0,0);
						$virtualna=db_result($q71,0,1);
						$novi_naziv=$naziv_grupe.'_'.$izvor;
						$q72=db_query("select naziv from labgrupa where predmet=$predmet and akademska_godina=$odrediste");
						$postoji_labgrupa=false;
						for($k=0;$k<db_num_rows($q72);$k++){
							$lab_naziv=db_result($q72,$k,0);
							if($lab_naziv==$novi_naziv) $postoji_labgrupa=true;
						}
						if($postoji_labgrupa==false){
							$q73=db_query("insert into labgrupa set id='NULL',naziv='$novi_naziv',predmet=$predmet,akademska_godina=$odrediste,virtualna=$virtualna");
							zamgerlog("uspjesno unesena labgrupa", 2);
							zamgerlog2("kreirana labgrupa", db_insert_id(), $predmet, $odrediste, $novi_naziv);
							$q74=db_query("select max(id) from labgrupa");
						}
						else{
							$q74=db_query("select id from labgrupa where naziv='$novi_naziv'");
						}		
					}
					$labgrupa=db_result($q74,0,0);
					$dupla=db_result($q6,$j,7);
					$id_stavke=db_result($q6,$j,8);
					$q0=db_query("insert into raspored_stavka set id='NULL', raspored=$id_rasp_odredista, dan_u_sedmici=$dan, predmet=$predmet,
							vrijeme_pocetak=$pocetak,vrijeme_kraj=$kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$dupla");
					$q1=db_query("select max(id) from raspored_stavka");
					$id_nove_stavke=db_result($q1,0,0);
					$q2=db_query("select r.studij,r.semestar from raspored_stavka rs,raspored r where rs.raspored=r.id and rs.dupla=$id_stavke");
					for($k=0;$k<db_num_rows($q2);$k++){
						$studij_k=db_result($q2,$k,0);
						$semestar_k=db_result($q2,$k,1);
						$q3=db_query("select id from raspored where semestar=$semestar_k and studij=$studij_k and akademska_godina=$odrediste");
						$rasp=db_result($q3,0,0);
						$q4=db_query("insert into raspored_stavka set id='NULL', raspored=$rasp, dan_u_sedmici=$dan, predmet=$predmet,
								vrijeme_pocetak=$pocetak,vrijeme_kraj=$kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$id_nove_stavke");
					}	
				}		
			}
			zamgerlog("uspješno kopirani svi rasporedi iz $naziv_izvora u $naziv_odredista akademsku godinu.", 2);
			zamgerlog2("uspješno kopirani svi rasporedi", $izvor, $odrediste);
			nicemessage("Uspješno kopirani svi rasporedi iz $naziv_izvora u $naziv_odredista akademsku godinu.");
		}
	}
	
		
	// ako se klikne na link izmijeni raspored ispunjen je sljedeći uslov i prikazuje se taj html kod
	if(isset($_REQUEST['raspored_za_edit'])){
		$raspored_za_edit=$_REQUEST['raspored_za_edit'];
		$q1=db_query("select studij,akademska_godina,semestar from raspored where id=$raspored_za_edit");
		$studij=db_result($q1,0,0);
		$akademska_godina=db_result($q1,0,1);
		$semestar=db_result($q1,0,2);
		$q2=db_query("select naziv from studij where id=$studij");
		$naziv_studija=db_result($q2,0,0);
		$q3=db_query("select naziv from akademska_godina where id=$akademska_godina");
		$naziv_akademske_godine=db_result($q3,0,0);
		
		// ukoliko se prihvati forma za unos novog časa ispunjen je uslov ispod 
		if ($_POST['akcija'] == 'unos_novog_casa' && check_csrf_token()) {
			$greska_u_dodavanju_casa=0;
			$cas_sa_konfliktima=intval($_POST['cas_sa_konfliktima']);
			$dan=intval($_POST['dan']);
			$tip=db_escape($_POST['tip']);
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
			$q0=db_query("select sala,vrijeme_pocetak,vrijeme_kraj,predmet,tip,labgrupa from raspored_stavka where dan_u_sedmici=$dan and raspored=$raspored_za_edit and (isjeckana=0 or isjeckana=2) and labgrupa!= -1");
			if($vrijeme_kraj>53) {$greska_u_dodavanju_casa=1;$greska_nevalja_termin=1;}
			if($predmet!=0){
				$q1=db_query("select obavezan from ponudakursa where akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet");
				$obavezan_predmet=db_result($q1,0,0);
			}
			for($i=0;$i<db_num_rows($q0);$i++){
				$sala_i=db_result($q0,$i,0);
				$vrijeme_pocetak_i=db_result($q0,$i,1);
				$vrijeme_kraj_i=db_result($q0,$i,2);
				$predmet_i=db_result($q0,$i,3);
				$tip_i=db_result($q0,$i,4);
				$labgrupa_i=db_result($q0,$i,5);
				if($predmet!=0){
					$q1=db_query("select obavezan from ponudakursa where akademska_godina=$akademska_godina and semestar=$semestar and predmet=$predmet_i");
					$obavezan_predmet_i=db_result($q1,0,0);
				}
				//ukoliko postoji preklapanje termina u rasporedu koji se trenutno edituje tačan je uslov ispod
				if (($vrijeme_pocetak_i>=$vrijeme_pocetak && $vrijeme_pocetak_i<$vrijeme_kraj)||($vrijeme_kraj_i>$vrijeme_pocetak && $vrijeme_kraj_i<=$vrijeme_kraj)){
					if($obavezan_predmet_i==1 && $tip_i=='P') {$greska_u_dodavanju_casa=1;$greska_preklapanje_sa_obaveznim_predmetom=1;}
					if($obavezan_predmet==1 && $tip=="P") {$greska_u_dodavanju_casa=1;$greska_preklapanje_obaveznog_predmeta=1;}
					if($predmet==$predmet_i && $labgrupa==$labgrupa_i) { $greska_u_dodavanju_casa=1; $greska_isti_predmet_ista_grupa=1;}
				}
			}
			$semestar_je_neparan= $semestar % 2;
			$q1=db_query("select rs.sala,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.predmet,rs.tip from raspored_stavka rs,raspored r where rs.dan_u_sedmici=$dan 
			and rs.raspored=r.id and r.akademska_godina=$akademska_godina and r.semestar mod 2 = $semestar_je_neparan and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1 ");
			for($i=0;$i<db_num_rows($q1);$i++){
				$sala_i=db_result($q1,$i,0);
				$vrijeme_pocetak_i=db_result($q1,$i,1);
				$vrijeme_kraj_i=db_result($q1,$i,2);
				$predmet_i=db_result($q1,$i,3);
				//ukoliko postoji preklapanje termina (sada gledamo sve rasporede) tačan je uslov ispod
				if (($vrijeme_pocetak_i>=$vrijeme_pocetak && $vrijeme_pocetak_i<$vrijeme_kraj)||($vrijeme_kraj_i>$vrijeme_pocetak && $vrijeme_kraj_i<=$vrijeme_kraj)){
					if($sala==$sala_i){
						$greska_duplikat_sale=1;
						$greska_u_dodavanju_casa=1;
						break;
					}
				}
			}
			if($greska_u_dodavanju_casa==0){
				$konflikt=array();
				$konflikt['student']=array();
				$konflikt['predmet']=array();
				$konflikt['pocetak']=array();
				$konflikt['kraj']=array();
				$q1=db_query("select rs.sala,rs.vrijeme_pocetak,rs.vrijeme_kraj,rs.predmet,rs.tip,rs.labgrupa,r.semestar from raspored_stavka rs,
				raspored r where rs.dan_u_sedmici=$dan and rs.raspored=r.id and r.akademska_godina=$akademska_godina and r.semestar mod 2 = $semestar_je_neparan 
				and rs.dupla=0 and (rs.isjeckana=0 or rs.isjeckana=2) and rs.labgrupa != -1 ");
				for($i=0;$i<db_num_rows($q1);$i++){
					$sala_i=db_result($q1,$i,0);
					$vrijeme_pocetak_i=db_result($q1,$i,1);
					$vrijeme_kraj_i=db_result($q1,$i,2);
					$predmet_i=db_result($q1,$i,3);
					$tip_i=db_result($q1,$i,4);
					$labgrupa_i=db_result($q1,$i,5);
					$semestar_i=db_result($q1,$i,6);
					if($tip_i=="P") $labgrupa_i=0;
					//ukoliko postoji preklapanje termina (sada gledamo sve rasporede) tačan je uslov ispod
					if (($vrijeme_pocetak_i>=$vrijeme_pocetak && $vrijeme_pocetak_i<$vrijeme_kraj)||($vrijeme_kraj_i>$vrijeme_pocetak && $vrijeme_kraj_i<=$vrijeme_kraj)){
						// provjera preklapanja studenata u terminu
						if($tip=="P"){
							$q_prvi=db_query("select sp.student from student_predmet sp,ponudakursa pk where sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar");
						}
						else{
							$q_prvi=db_query("select student from student_labgrupa where labgrupa=$labgrupa");
						}
						if($labgrupa_i==0){
							$q_drugi=db_query("select sp.student from student_predmet sp,ponudakursa pk where sp.predmet=pk.id and pk.predmet=$predmet_i and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar_i");
						}
						else{
							$q_drugi=db_query("select student from student_labgrupa where labgrupa=$labgrupa_i");
						}
						for($p=0;$p<db_num_rows($q_prvi);$p++){
							$student_p=db_result($q_prvi,$p,0);
							for($d=0;$d<db_num_rows($q_drugi);$d++){
								$student_d=db_result($q_drugi,$d,0);
								if($student_p==$student_d){
									// pronadjen je student u konfliktu i upisujemo ga u niz konflikata
									$konflikt['student'][]=$student_p;
									$konflikt['predmet'][]=$predmet_i;
									$konflikt['pocetak'][]=$vrijeme_pocetak_i;
									$konflikt['kraj'][]=$vrijeme_kraj_i;
								}		
							}	
						}				
					}
				}
			}	
			if(($greska_u_dodavanju_casa==0 && count($konflikt['student'])==0) || (count($konflikt['student'])>0 && $cas_sa_konfliktima==1)){
				// vrsi se pretvaranje vremena u jedan broj u intervalu od 1-53 radi lakšeg rada sa prikazom rasporeda
				$vrijeme_pocetak=$vrijeme_pocetak_sati*4 + $vrijeme_pocetak_minute;
				$vrijeme_kraj=$vrijeme_kraj_sati*4 + $vrijeme_kraj_minute;
				if($tip=="P") $labgrupa=0;
					$q0=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored_za_edit, dan_u_sedmici=$dan, predmet=$predmet,
						vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa");
					$cas_uspjesno_dodan=1;
					zamgerlog("unesen novi cas za akademsku $naziv_akademske_godine, studij $naziv_studija, semestar $semestar", 2);
					zamgerlog2("dodana stavka u raspored", db_insert_id(), $raspored_za_edit);
					if (count($konflikt['student'])>0 && $cas_sa_konfliktima==1) $cas_dodan_sa_konfliktima=1;
					$q00=db_query("select max(id) from raspored_stavka");
					$id_unesene_stavke=db_result($q00,0,0);
						$q1=db_query("select studij,semestar from ponudakursa where predmet=$predmet and akademska_godina=$akademska_godina and semestar mod 2 = $semestar_je_neparan");
						for($i=0;$i<db_num_rows($q1);$i++){
							$studij_i=db_result($q1,$i,0);
							$semestar_i=db_result($q1,$i,1);
							$postoji_raspored=0;
							if($semestar_i==$semestar && $studij_i==$studij){
									$postoji_raspored=1;
							}
							else
							{	
								$q01=db_query("select semestar,studij,id from raspored where akademska_godina=$akademska_godina and semestar mod 2 = $semestar_je_neparan");
								for($j=0;$j<db_num_rows($q01);$j++){
									$studij_j=db_result($q01,$j,1);
									$semestar_j=db_result($q01,$j,0);
									$raspored_j=db_result($q01,$j,2);	
									if($semestar_i==$semestar_j && $studij_i==$studij_j){
										$postoji_raspored=1; break;
									}		
								}
							}
							if($postoji_raspored==0){
								$q02=db_query("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij_i, semestar=$semestar_i");
								zamgerlog("Kreiran novi raspored za akademsku $naziv_akademske_godine godinu", 2);
							}		
							$q2=db_query("select id from raspored where akademska_godina=$akademska_godina and semestar=$semestar_i and studij=$studij_i");
							if(db_num_rows($q2)>0){
								$raspored_i=db_result($q2,0,0);
								if($raspored_i!=$raspored_za_edit){
									$q3=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored_i, dan_u_sedmici=$dan, predmet=$predmet,
										vrijeme_pocetak=$vrijeme_pocetak,vrijeme_kraj=$vrijeme_kraj,sala=$sala,tip='$tip',labgrupa=$labgrupa,dupla=$id_unesene_stavke");
									zamgerlog("unesen novi cas za akademsku $naziv_akademske_godine godinu", 2);
									zamgerlog2("dodana stavka u raspored", db_insert_id(), $raspored_i);
								}	
							}	
						}
							
			}
		}
		
		
		
		$greska_masovnog_unosa_casova=0;
		
		// uslov ispod je ispunjen ako je prihvaćena forma za masovni unos sala
		if ($_POST['akcija'] == 'masovni_unos_casova' && check_csrf_token()){
			$redovi=explode("\n", $_POST['mas_unos_casova']);
			if(trim($_POST['mas_unos_casova'])==''){
				$greska_masovnog_unosa_casova=1; $greska_prazan_prostor_za_mas_unos_casova=1;
			}
			$greska_u_redu=array();
			$greska_prazni_parametri_u_redu=array();
			$greska_nevalja_tip_casa_u_redu=array();
			$greska_nevalja_dan_u_redu=array();
			$greska_postoji_sala_u_redu=array();
			$greska_ne_postoji_predmet_u_redu=array();
			$greska_ne_postoji_labgrupa_u_redu=array();
			$greska_pogresno_vrijeme_u_redu=array();
			$greska_nevalja_interval_u_redu=array();
			$i=0;
			foreach ($redovi as $red){
				$i++;
				$red=trim($red);
				if(strlen($red)<1) continue; // prazan red
				if($_POST['separator']==1) {
					list($dan,$predmet,$pocetak,$kraj,$sala,$tip,$labgrupa)=explode(",", $red);
					$dan=trim($dan);
					$predmet=trim($predmet);
					$pocetak=trim($pocetak);
					$kraj=trim($kraj);
					$sala=trim($sala);
					$tip=trim($tip);
					$labgrupa=trim($labgrupa);
					$niz=explode(",", $red);
				}
				elseif ($_POST['separator']==2) {
					list($dan,$predmet,$pocetak,$kraj,$sala,$tip,$labgrupa)=explode("\t", $red);
					$dan=trim($dan);
					$predmet=trim($predmet);
					$pocetak=trim($pocetak);
					$kraj=trim($kraj);
					$sala=trim($sala);
					$tip=trim($tip);
					$labgrupa=trim($labgrupa);
					$niz=explode("\t", $red);
				}
				list($pocS,$pocM)=explode(":",$pocetak);
				list($krajS,$krajM)=explode(":",$kraj);
				
				if (count($niz)!=7){
					if(count($niz)==6){
						if($tip!='P') { $greska_masovnog_unosa_casova=1; $greska_u_redu[]=$i; }
					}
					else { $greska_masovnog_unosa_casova=1; $greska_u_redu[]=$i; }
				}
				else{
					if($dan=='' || $predmet=='' || $pocetak=='' || $kraj=='' || $sala=='' || $tip=='' || $labgrupa==''){
						$greska_masovnog_unosa_casova=1; $greska_prazni_parametri_u_redu[]=$i; 
					}
					else{
						if (strtoupper($tip)!='P' && strtoupper($tip)!='T' && strtoupper($tip)!='L'){
							$greska_masovnog_unosa_casova=1; $greska_nevalja_tip_casa_u_redu[]=$i;
						}
						if($dan!='pon' && $dan!='uto' && $dan!='sri' && $dan!='cet' && $dan!='pet' && $dan!='sub'){
							$greska_masovnog_unosa_casova=1; $greska_nevalja_dan_u_redu[]=$i;
						}
						$raspored_za_edit=$_REQUEST['raspored_za_edit'];
						$q1=db_query("select studij,akademska_godina,semestar from raspored where id=$raspored_za_edit");
						$studij=db_result($q1,0,0);
						$akademska_godina=db_result($q1,0,1);
						$semestar=db_result($q1,0,2);
						$q2=db_query("select p.kratki_naziv from ponudakursa pk,predmet p where p.id=pk.predmet and pk.akademska_godina=$akademska_godina
						and pk.semestar=$semestar and pk.studij=$studij");
						$postoji_predmet=false;
						for($j=0;$j<db_num_rows($q2);$j++)
						{
							if(db_result($q2,$j,0)==strtoupper($predmet)){
								$postoji_predmet=true;
							}
						}
						if($postoji_predmet==false){
							$greska_masovnog_unosa_casova=1; $greska_ne_postoji_predmet_u_redu[]=$i;
						}
						else{
							$q0=db_query("select id from predmet where kratki_naziv='$predmet'");
							$predmet_id=db_result($q0,0,0);
							if($tip!="P"){
							$q1=db_query("select naziv from labgrupa where predmet=$predmet_id");
							$postoji_labgrupa=false;
							for($j=0;$j<db_num_rows($q1);$j++)
							{
								if(db_result($q1,$j,0)==$labgrupa){
									$postoji_labgrupa=true;
								}
							}
							if($postoji_labgrupa==false){
								$greska_masovnog_unosa_casova=1; $greska_ne_postoji_labgrupa_u_redu[]=$i;
							}
							}	
						}	
						if((intval($pocS)<8 || intval($pocS)>20 ) || (intval($krajS)<8 || intval($krajS)>21 )) { $greska_masovnog_unosa_casova=1; $greska_pogresno_vrijeme_u_redu[]=$i; }
						elseif (($pocM!='00' && $pocM!='15' && $pocM!='30' && $pocM!='45')||($krajM!='00' && $krajM!='15' && $krajM!='30' && $krajM!='45')) { 
							$greska_masovnog_unosa_casova=1; $greska_pogresno_vrijeme_u_redu[]=$i; 
						}
						else{
							if($pocM=="00") $pocM=1;
							elseif($pocM=="15") $pocM=2;
							elseif($pocM=="30") $pocM=3;
							elseif($pocM=="45") $pocM=4;
							if($krajM=="00") $krajM=1;
							elseif($krajM=="15") $krajM=2;
							elseif($krajM=="30") $krajM=3;
							elseif($krajM=="45") $krajM=4;
							$pocetak_broj=((intval($pocS)-8)*4 +$pocM);
							$kraj_broj=((intval($krajS)-8)*4 +$krajM);
							if($kraj_broj<=$pocetak_broj) { $greska_masovnog_unosa_casova=1; $greska_nevalja_interval_u_redu[]=$i; }
						}			
						$q0=db_query("select id from raspored_sala where naziv='$sala'");
						$sala_id=db_result($q0,0,0);
						$dani=array("pon","uto","sri","cet","pet","sub");
						for($j=0;$j<count($dani);$j++){
							if(strtolower($dan)==$dani[$j]) {$dan_broj=$j+1;break;}
						}	
						$q2=db_query("select sala,vrijeme_pocetak,vrijeme_kraj from raspored_stavka where raspored=$raspored_za_edit and dan_u_sedmici=$dan_broj and (isjeckana=0 or isjeckana=2)");
						for($j=0;$j<db_num_rows($q2);$j++){
							$sala_j=db_result($q2,$j,0);
							$vrijeme_pocetak_j=db_result($q2,$j,1);
							$vrijeme_kraj_j=db_result($q2,$j,2);
							if (($vrijeme_pocetak_j>=$pocetak_broj && $vrijeme_pocetak_j<$kraj_broj)||($vrijeme_kraj_j>$pocetak_broj && $vrijeme_kraj_j<=$kraj_broj)){
								if($sala_id==$sala_j){
								$greska_masovnog_unosa_casova=1;
								$greska_postoji_sala_u_redu[]=$i; 
								break;
								}
							}
							
						}
					}	
				}
			}
			
			
			// ako nema grešaka u unosu dodajemo sale
			if($greska_masovnog_unosa_casova==0){
				$i=0;
				foreach ($redovi as $red){
					$i++;
					$red=trim($red);
					if(strlen($red)<1) continue; // prazan red
					if($_POST['separator']==1) {
						list($dan,$predmet,$pocetak,$kraj,$sala,$tip,$labgrupa)=explode(",", $red);
						$dan=trim($dan);
						$predmet=trim($predmet);
						$pocetak=trim($pocetak);
						$kraj=trim($kraj);
						$sala=trim($sala);
						$tip=trim($tip);
						$labgrupa=trim($labgrupa);
						$niz=explode(",", $red);
					}
					elseif ($_POST['separator']==2) {
						list($dan,$predmet,$pocetak,$kraj,$sala,$tip,$labgrupa)=explode("\t", $red);
						$dan=trim($dan);
						$predmet=trim($predmet);
						$pocetak=trim($pocetak);
						$kraj=trim($kraj);
						$sala=trim($sala);
						$tip=trim($tip);
						$labgrupa=trim($labgrupa);
						$niz=explode("\t", $red);
					}
					list($pocS,$pocM)=explode(":",$pocetak);
					list($krajS,$krajM)=explode(":",$kraj);
					if($pocM=="00") $pocM=1;
					elseif($pocM=="15") $pocM=2;
					elseif($pocM=="30") $pocM=3;
					elseif($pocM=="45") $pocM=4;
					if($krajM=="00") $krajM=1;
					elseif($krajM=="15") $krajM=2;
					elseif($krajM=="30") $krajM=3;
					elseif($krajM=="45") $krajM=4;
					$pocetak_broj=((intval($pocS)-8)*4 +$pocM);
					$kraj_broj=((intval($krajS)-8)*4 +$krajM);
					
					$q0=db_query("select id from raspored_sala where naziv='$sala'");
					$sala_id=db_result($q0,0,0);
					
					$dani=array("pon","uto","sri","cet","pet","sub");
					for($j=0;$j<count($dani);$j++){
						if(strtolower($dan)==$dani[$j]) {$dan_broj=$j+1;break;}
					}
					
					$q0=db_query("select id from predmet where kratki_naziv='$predmet'");
					$predmet_id=db_result($q0,0,0);
					
					$tip=strtoupper($tip);
					
					if($tip=="P") $labgrupa_id=0;
					else{
						$q0=db_query("select id from labgrupa where naziv='$labgrupa'");
						$labgrupa_id=db_result($q0,0,0);
					}
					
					
					
					$semestar_je_neparan=$semestar % 2;
					$q0=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored_za_edit, dan_u_sedmici=$dan_broj, predmet=$predmet_id,
						vrijeme_pocetak=$pocetak_broj,vrijeme_kraj=$kraj_broj,sala=$sala_id,tip='$tip',labgrupa=$labgrupa_id");
					$q00=db_query("select max(id) from raspored_stavka");
					$id_unesene_stavke=db_result($q00,0,0);
						$q1=db_query("select studij,semestar from ponudakursa where predmet=$predmet_id and akademska_godina=$akademska_godina and semestar mod 2 = $semestar_je_neparan");
						for($k=0;$k<db_num_rows($q1);$k++){
							$studij_i=db_result($q1,$k,0);
							$semestar_i=db_result($q1,$k,1);
							$postoji_raspored=0;
							if($semestar_i==$semestar && $studij_i==$studij){
									$postoji_raspored=1;
							}
							else
							{	
								$q01=db_query("select semestar,studij,id from raspored where akademska_godina=$akademska_godina and semestar mod 2 = $semestar_je_neparan");
								for($j=0;$j<db_num_rows($q01);$j++){
									$studij_j=db_result($q01,$j,1);
									$semestar_j=db_result($q01,$j,0);
									$raspored_j=db_result($q01,$j,2);	
									if($semestar_i==$semestar_j && $studij_i==$studij_j){
										$postoji_raspored=1; break;
									}		
								}
							}
							if($postoji_raspored==0){
								$q02=db_query("insert into raspored set id='NULL', akademska_godina=$akademska_godina, studij=$studij_i, semestar=$semestar_i");
							}		
							$q2=db_query("select id from raspored where akademska_godina=$akademska_godina and semestar=$semestar_i and studij=$studij_i");
							if(db_num_rows($q2)>0){
								$raspored_i=db_result($q2,0,0);
								if($raspored_i!=$raspored_za_edit){
									$q3=db_query("insert into raspored_stavka set id='NULL', raspored=$raspored_i, dan_u_sedmici=$dan_broj, predmet=$predmet_id,
										vrijeme_pocetak=$pocetak_broj,vrijeme_kraj=$kraj_broj,sala=$sala_id,tip='$tip',labgrupa=$labgrupa_id,dupla=$id_unesene_stavke");
								}	
							}	
						}	
				}
				$uspjesan_masovni_unos_casova=1;
				zamgerlog("Izvršen masovni unos časova", 2);
				zamgerlog2("izvršen masovni unos časova u raspored");
			} 
		}
		
		
		
		
		if ($_POST['akcija'] == "obrisi_cas" && check_csrf_token()) {
			$id_casa_za_brisanje = intval($_POST['id_casa_za_brisanje']);
			$q0=db_query("select dupla from raspored_stavka where id=$id_casa_za_brisanje");
			$dupla=db_result($q0,0,0);
			if($dupla==0){
				$q1=db_query("delete from raspored_stavka where id=$id_casa_za_brisanje");
				$q2=db_query("delete from raspored_stavka where dupla=$id_casa_za_brisanje");
			}
			else{
				$q1=db_query("delete from raspored_stavka where id=$dupla");
				$q2=db_query("delete from raspored_stavka where dupla=$dupla");
			}
			$uspjesno_obrisan_cas=1;
			zamgerlog("obrisan cas",4); // nivo 4: audit
			zamgerlog2("obrisana stavka iz rasporeda", $id_casa_za_brisanje);
		}
		print "<a href=\"?sta=studentska/raspored1\">vrati se na početnu</a>";
		print "<h4>Editovanje rasporeda za akademsku $naziv_akademske_godine godinu, studij $naziv_studija, $semestar. semestar:</h4>";
		print "<hr/>";
		
		
		
		
		// ukoliko nema konflikata ispunjen je uslov ispod
		if(!isset($_REQUEST['konflikt'])){
			
			print "<h4>Dodavanje novog časa:</h4>";
			if($greska_prazni_parametri_u_casu==1) print "<p class=\"crveno\">GREŠKA: Niste unijeli neki podatak.</p>";
			if($greska_neispravan_interval==1) print "<p class=\"crveno\">GREŠKA: Interval koji ste unijeli nije ispravan.</p>";
			if($greska_duplikat_sale==1) print "<p class=\"crveno\">GREŠKA: Sala koju ste odabrali je zauzeta u tom terminu.</p>";
			if($greska_preklapanje_sa_obaveznim_predmetom==1) print "<p class=\"crveno\">GREŠKA: Postoji preklapanje sa predavanjem obaveznog predmeta.</p>";
			if($greska_preklapanje_obaveznog_predmeta==1) print "<p class=\"crveno\">GREŠKA: Predavanje obaveznog predmeta se ne može dodati jer je termin zauzet.</p>";
			if($greska_nevalja_termin==1) print "<p class=\"crveno\">GREŠKA: Nastava traje maksimalno do 21 sat.</p>";
			if($greska_isti_predmet_ista_grupa==1) print "<p class=\"crveno\">GREŠKA: Isti predmet i ista grupa u terminu koji se preklapa !</p>";
			if($cas_uspjesno_dodan==1) nicemessage("Čas je usješno dodan.");
			if($uspjesno_obrisan_cas==1) nicemessage("Čas je usješno obrisan.");
			
			$broj_konflikata=count($konflikt['student']);
			if($broj_konflikata>0 && $cas_dodan_sa_konfliktima!=1){
				print "<br>";
				if($broj_konflikata==1) $varijabla="konflikt";
				elseif($broj_konflikata==2 || $broj_konflikata==3 || $broj_konflikata==4) $varijabla="konflikta";
				else $varijabla="konflikata";
				print "<p class=\"crveno\">Postoji $broj_konflikata $varijabla pri preklapanju časova.</p>";
				?>
				<a href="#" onclick="prikazKonflikata()"><img id="slika_konflikti" src = "static/images/plus.png" border="0" align="left" />Prikaži konflikte</a>
				<div id="prikaz_konflikata" style="display:none">
					<?
					print "<ul>";
					for($i=0;$i<count($konflikt['student']);$i++){
						$student=$konflikt['student'][$i];
						$predmet=$konflikt['predmet'][$i];
						$pocetak=$konflikt['pocetak'][$i];
						$kraj=$konflikt['kraj'][$i];
						$vrijemePocS=floor(($pocetak-1)/4+8);
						$vrijemePocMin=$pocetak%4;
						if($vrijemePocMin==1) $vrijemePocM="00";
						elseif($vrijemePocMin==2) $vrijemePocM="15";
						elseif($vrijemePocMin==3) $vrijemePocM="30";
						elseif($vrijemePocMin==0) $vrijemePocM="45";
						$vrijemeP="$vrijemePocS:$vrijemePocM";
						$vrijemeKrajS=floor(($kraj-1)/4+8);
						$vrijemeKrajMin=$kraj%4;
						if($vrijemeKrajMin==1) $vrijemeKrajM="00";
						elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
						elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
						elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
						$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
						$q1=db_query("select ime, prezime from osoba where id=$student");
						$ime=db_result($q1,0,0);
						$prezime=db_result($q1,0,1);
						$q2=db_query("select naziv from predmet where id=$predmet");
						$naziv_predmeta=db_result($q2,0,0);
						print "<li><p><b>$ime $prezime</b> ima predmet <b>$naziv_predmeta</b> u terminu <b>$vrijemeP-$vrijemeK</b></p></li>";
					}
					print "</ul>";
					print "<hr>";	
					?>
				</div>
				<p>Da li ipak želite dodati čas sa konfliktima: <input type="button" onclick="dodajCasSaKonfliktima()" value=" DA "></p>
				<hr></hr>
				<?
				print "<br></br>";
			}	
			$q4=db_query("select pk.predmet,p.kratki_naziv,pk.obavezan from ponudakursa pk, predmet p where pk.predmet=p.id and pk.studij=$studij and pk.akademska_godina=$akademska_godina and pk.semestar=$semestar");
			
			print genform("POST", "forma_za_unos_casa"); 
			$dani=array("Ponedjeljak","Utorak","Srijeda","Četvrtak","Petak","Subota");
			$tipovi_nastave=array("P","T","L");
			?>
			<input type="hidden" name="akcija" id="akcija_novi_cas" value="unos_novog_casa">
			<input type="hidden" name="cas_sa_konfliktima" id="cas_sa_konfliktima" value="0">
			<table cellpadding="3" name="cas">
				<tr>
					<td>Dan:</td>
					<td>
						<select name="dan">
							<option value="0">---</option>
							<?
							for($i=0;$i<=5;$i++){
								$x=$i+1;
								print "<option value=\"$x\"";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['dan']==$x) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['dan']==$x) || 
									($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['dan']==$x))
									&& $cas_dodan_sa_konfliktima!=1){ 
									print " selected=\"selected\"";
								}
								print ">{$dani[$i]}</option>";	
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Tip:</td>
					<td>
						<select name="tip" id="tip" onchange="javascript:prikaziGrupe()">
							<option value="0">---</option>
							<?
							for($i=0;$i<=2;$i++){
								print "<option value={$tipovi_nastave[$i]}";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['tip']==$tipovi_nastave[$i]) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['tip']==$tipovi_nastave[$i]) ||
									($_POST['akcija']=="unos_novog_casa" &&  count($konflikt['student'])>0 &&  $_POST['tip']==$tipovi_nastave[$i]))
									&& $cas_dodan_sa_konfliktima!=1){ 
									print " selected=\"selected\"";
								}
								if($tipovi_nastave[$i]=="P") print ">Predavanje</option>";
								elseif($tipovi_nastave[$i]=="T") print ">Tutorijal</option>";
								elseif($tipovi_nastave[$i]=="L") print ">Laboratorijska vježba</option>";
							}	
							?>
							
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
							for($i=0;$i<db_num_rows($q4);$i++){
								$id_predmeta=db_result($q4,$i,0);
								$kratki_naziv=db_result($q4,$i,1);
								$obavezan=db_result($q4,$i,2);
								print "<option value=\"$id_predmeta\"";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['predmet']==$id_predmeta) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['predmet']==$id_predmeta)||
									($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['predmet']==$id_predmeta) )
									&& $cas_dodan_sa_konfliktima!=1){ 
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
							$q0=db_query("select id,naziv from raspored_sala order by naziv");
							for($i=0;$i<db_num_rows($q0);$i++){
								$id_sale=db_result($q0,$i,0);
								$naziv_sale=db_result($q0,$i,1);
								print "<option value=\"$id_sale\"";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['sala']==$id_sale) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['sala']==$id_sale)||
									($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['sala']==$id_sale))
									&& $cas_dodan_sa_konfliktima!=1){ 
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
							for($i=0;$i<=12;$i++){
								$j=$i+8;
								print "<option value=\"$i\"";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakSat']==$i) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakSat']==$i)||
									($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['pocetakSat']==$i))
									&& $cas_dodan_sa_konfliktima!=1){ 
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
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==1) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==1)
									|| ($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['pocetakMin']==1))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>00
							</option>
							<option value="2"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==2) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==2)
									|| ($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['pocetakMin']==2))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>15
							</option>
							<option value="3"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==3) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==3)
									|| ($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['pocetakMin']==3))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>30
							</option>
							<option value="4"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['pocetakMin']==4) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['pocetakMin']==4)
									|| ($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['pocetakMin']==4))
									&& $cas_dodan_sa_konfliktima!=1){ 
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
							for($i=0;$i<=13;$i++){
								$j=$i+8;
								print "<option value=\"$i\"";
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajSat']==$i) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajSat']==$i)||
									($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['krajSat']==$i))
									&& $cas_dodan_sa_konfliktima!=1){ 
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
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==1) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==1)
									||($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['krajMin']==1))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>00
							</option>
							<option value="2"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==2) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==2)
									||($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['krajMin']==2))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>15
							</option>
							<option value="3"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==3) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==3)
									||($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['krajMin']==3))
									&& $cas_dodan_sa_konfliktima!=1){ 
										print " selected=\"selected\"";
								}
								?>
								>30
							</option>
							<option value="4"
								<?
								if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['krajMin']==4) || 
									($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['krajMin']==4)
									||($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['krajMin']==4))
									&& $cas_dodan_sa_konfliktima!=1){ 
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
								$q0=db_query("select id,naziv from labgrupa where predmet=$id_predmeta and akademska_godina=$akademska_godina");
								for($i=0;$i<db_num_rows($q0);$i++){
									$id_labgrupe=db_result($q0,$i,0);
									$naziv_labgrupe=db_result($q0,$i,1);
									print "<option value=\"$id_labgrupe\"";
									if((($_POST['akcija']=="unos_novog_casa" && $greska_u_dodavanju_casa==1 &&  $_POST['labgrupa']==$id_labgrupe) || 
										($_POST['akcija']=="unos_novog_casa_predfaza" &&  $_POST['labgrupa']==$id_labgrupe) ||
										($_POST['akcija']=="unos_novog_casa" && count($konflikt['student'])>0 &&  $_POST['labgrupa']==$id_labgrupe))
										&& $cas_dodan_sa_konfliktima!=1){ 
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
			
			
			<h4>Masovni unos časova:</h4>
			<?
			if((count($greska_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Nema potreban broj parametara u redu $greska_u_redu[0]";
				if(count($greska_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_u_redu);$i++){
						if($i==(count($greska_u_redu)-1)) print " i {$greska_u_redu[$i]}.</p>";
						else print ", {$greska_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_prazni_parametri_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Postoje prazni parametri u redu $greska_prazni_parametri_u_redu[0]";
				if(count($greska_prazni_parametri_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_prazni_parametri_u_redu);$i++){
						if($i==(count($greska_prazni_parametri_u_redu)-1)) print " i {$greska_prazni_parametri_u_redu[$i]}.</p>";
						else print ", {$greska_prazni_parametri_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_nevalja_tip_casa_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Tip časa nije ispravan  u redu $greska_nevalja_tip_casa_u_redu[0]";
				if(count($greska_nevalja_tip_casa_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_nevalja_tip_casa_u_redu);$i++){
						if($i==(count($greska_nevalja_tip_casa_u_redu)-1)) print " i {$greska_nevalja_tip_casa_u_redu[$i]}.</p>";
						else print ", {$greska_nevalja_tip_casa_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_nevalja_dan_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Dan nije ispravan u redu $greska_nevalja_dan_u_redu[0]";
				if(count($greska_nevalja_dan_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_nevalja_dan_u_redu);$i++){
						if($i==(count($greska_nevalja_dan_u_redu)-1)) print " i {$greska_nevalja_dan_u_redu[$i]}.</p>";
						else print ", {$greska_nevalja_dan_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_postoji_sala_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Sala zauzeta u datom terminu u redu $greska_postoji_sala_u_redu[0]";
				if(count($greska_postoji_sala_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_postoji_sala_u_redu);$i++){
						if($i==(count($greska_postoji_sala_u_redu)-1)) print " i {$greska_postoji_sala_u_redu[$i]}.</p>";
						else print ", {$greska_postoji_sala_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_ne_postoji_labgrupa_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Ne postoji grupa u redu $greska_ne_postoji_labgrupa_u_redu[0]";
				if(count($greska_ne_postoji_labgrupa_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_ne_postoji_labgrupa_u_redu);$i++){
						if($i==(count($greska_ne_postoji_labgrupa_u_redu)-1)) print " i {$greska_ne_postoji_labgrupa_u_redu[$i]}.</p>";
						else print ", {$greska_ne_postoji_labgrupa_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_ne_postoji_predmet_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Ne postoji predmet u redu $greska_ne_postoji_predmet_u_redu[0]";
				if(count($greska_ne_postoji_predmet_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_ne_postoji_predmet_u_redu);$i++){
						if($i==(count($greska_ne_postoji_predmet_u_redu)-1)) print " i {$greska_ne_postoji_predmet_u_redu[$i]}.</p>";
						else print ", {$greska_ne_postoji_predmet_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_pogresno_vrijeme_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Vrijeme nije ispravno unešeno u redu $greska_pogresno_vrijeme_u_redu[0]";
				if(count($greska_pogresno_vrijeme_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_pogresno_vrijeme_u_redu);$i++){
						if($i==(count($greska_pogresno_vrijeme_u_redu)-1)) print " i {$greska_pogresno_vrijeme_u_redu[$i]}.</p>";
						else print ", {$greska_pogresno_vrijeme_u_redu[$i]}";
					} 
				}
			}
			
			if((count($greska_nevalja_interval_u_redu)>0) && $greska_masovnog_unosa_casova==1){
				print "<p class=\"crveno\">GREŠKA: Interval nije ispravan u redu $greska_nevalja_interval_u_redu[0]";
				if(count($greska_nevalja_interval_u_redu)==1) print ".</p>";
				else{
					for($i=1;$i<count($greska_nevalja_interval_u_redu);$i++){
						if($i==(count($greska_nevalja_interval_u_redu)-1)) print " i {$greska_nevalja_interval_u_redu[$i]}.</p>";
						else print ", {$greska_nevalja_interval_u_redu[$i]}";
					} 
				}
			}
			
			if($greska_prazan_prostor_za_mas_unos_casova==1) print "<p class=\"crveno\">GREŠKA: Niste unijeli nikakve podatke u prostor za masovni unos.</p>";
			if($uspjesan_masovni_unos_casova==1) nicemessage("Uspješno unešeni časovi!");
			?>
			
			<a href="#" onclick="daj_stablo('uputstvo_za_mas_unos_casova')"><img id="img-uputstvo_za_mas_unos_casova" src = "static/images/plus.png" border="0" align="left" />Uputstvo za masovni unos</a>
			<div id="uputstvo_za_mas_unos_casova" style="display:none">
				<p>Unesite sljedeće podatke odvojene zarezom (ili [tab]-om ):</p> 
				<ul>
				<li><p><span class="plavo_bold">dan u sedmici: </span> (pon, uto, sri, cet, pet, sub)</p></li>
				<li><p><span class="plavo_bold">predmet: </span> unesite skraćeni naziv predmeta, npr im za inžinjersku matematiku</p></li>
				<li><p><span class="plavo_bold">vrijeme početka: </span> unesite vrijeme u intervalu 8:00 do 20:45</p></li>
				<li><p><span class="plavo_bold">vrijeme kraja: </span> unesite vrijeme u intervalu 8:15 do 21:00</p></li>
				<li><p><span class="plavo_bold">ime sale </span></p></li>
				<li>
					<p><span class="plavo_bold">tip časa: </span></p>
					<ul>
						<li><p><span class="plavo_bold">P: </span> za predavanje</p></li>
						<li><p><span class="plavo_bold">T: </span> za tutorijal</p></li>
						<li><p><span class="plavo_bold">L: </span> za laboratorijsku vježbu</p></li>
					</ul>
				</li>
				<li><p><span class="plavo_bold">ime grupe: </span>za predavanje ovaj parametar je prazan</p></li>
				</ul>
				<p>Ukoliko unosite podatke iz Excel-a odaberite opciju unos iz excela(odvajanje sa [tab]-om)</p>
				<p>Svaki novi čas dodajte u novom redu</p>
				<p>primjer:</p>
				<p>pon, im, 8:15, 9:30, s01, P</p>
				<p>pon, if, 9:30, 10:30, s02, T, g1</p>
			</div>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="masovni_unos_casova">
			<textarea name="mas_unos_casova" rows="10" cols="40">
			<? if(isset($_POST['mas_unos_casova'])) print trim($_POST['mas_unos_casova']) ?>
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
					for($i=8;$i<=20;$i++){
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
				
				
				// petlja za 6 dana u sedmici
				for($i=1;$i<=6;$i++){
					print "<tr>";
					$q0=db_query("select vrijeme_pocetak,vrijeme_kraj from raspored_stavka where dan_u_sedmici=$i and raspored=$raspored_za_edit and (isjeckana=0 or isjeckana=2) and labgrupa!= -1");
					// sada je potrebno naći maksimalni broj preklapanja termina da bi znali koliki je rowspan potreban za dan $i
					// poredimo svaki interval casa sa svakim
					$broj_preklapanja=array();
					for($j=0;$j<53;$j++){
						$broj_preklapanja[]=0;
					}
					for($j=0;$j<db_num_rows($q0);$j++){
						$pocetak=db_result($q0,$j,0);
						$kraj=db_result($q0,$j,1);
						for($k=$pocetak;$k<$kraj;$k++) $broj_preklapanja[$k]++;
					}
					$max_broj_preklapanja=max($broj_preklapanja);
					if($i==1) $dan_tekst="PON";
					elseif ($i==2) $dan_tekst="UTO";
					elseif ($i==3) $dan_tekst="SRI";
					elseif ($i==4) $dan_tekst="ČET";
					elseif ($i==5) $dan_tekst="PET";
					elseif ($i==6) $dan_tekst="SUB";
					// sada pravimo dvodimenzionalni niz, koji predstavlja zauzetost termina u određenom redu
					$zauzet=array();
					for($j=0;$j<$max_broj_preklapanja;$j++){
						$zauzet=array();
					}
					for($j=0;$j<$max_broj_preklapanja;$j++){
						for($k=0;$k<53;$k++){
							$zauzet[$j][]=0;
						}
					}
					// zauzet[1][0]=1 znaci da je termin 1 zauzet u drugom redu  
					
					$q1=db_query("select id,raspored,predmet,vrijeme_pocetak,vrijeme_kraj,sala,tip,labgrupa from raspored_stavka where dan_u_sedmici=$i and raspored=$raspored_za_edit 
					and (isjeckana=0 or isjeckana=2) and labgrupa != -1 order by id");
					$gdje=array();
					$gdje["id_stavke"]=array(); 
					$gdje["red_stavke"]=array(); // red u kojem stavka ide
					// primjer 
					// gdje["id_stavke"][0]=5 znaci da je id prve stavke 5
					// gdje["red_stavke"][0]=3 znaci da stavka 1 ide u 4. red
					// [0] pretstavlja prvu stavku jer indeksi kreću od nule i druga kolona treba biti ista-- u ovom slucaju [0]
					for($j=0;$j<db_num_rows($q1);$j++){
						$id_stavke=db_result($q1,$j,0);
						$gdje["id_stavke"][$j]=$id_stavke;// i ovo vise ne diramo jer znamo koji je id stavke na osnovu nepoznate $j
						$gdje["red_stavke"][$j]=0; // postavljamo na nulu jer još ne znamo gdje ide određena stavka
					}
					for($j=0;$j<db_num_rows($q1);$j++){
						$id_stavke=db_result($q1,$j,0);
						$pocetak=db_result($q1,$j,3);
						$kraj=db_result($q1,$j,4);
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
								$gdje["red_stavke"][$j]=$k; // $stavka $j ide u red $k
								//sada proglasavamo termin zauzetim u tom redu $k+1
								$pocetak=db_result($q1,$j,3);
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
						for($m=1;$m<=52;$m++){
							for($k=0;$k<db_num_rows($q1);$k++){
								$id_stavke=db_result($q1,$k,0);
								$q00=db_query("select r.akademska_godina,r.semestar from raspored r, raspored_stavka rs where r.id=rs.raspored and (rs.isjeckana=0 or rs.isjeckana=2) and rs.id=$id_stavke");
								$akademska_godina=db_result($q00,0,0);
								$semestar=db_result($q00,0,1);
								$semestar_je_neparan= $semestar % 2;
								$predmet=db_result($q1,$k,2);
								$q2=db_query("select kratki_naziv from predmet where id=$predmet");
								$predmet_naziv=db_result($q2,0,0);
								$pocetak=db_result($q1,$k,3);
								$kraj=db_result($q1,$k,4);
								$sala=db_result($q1,$k,5);
								$q3=db_query("select naziv from raspored_sala where id=$sala");
								$sala_naziv=db_result($q3,0,0);
								$tip=db_result($q1,$k,6);
								$labgrupa=db_result($q1,$k,7);
								if($labgrupa!=0){
									if($labgrupa!=-1){
										$q4=db_query("select naziv from labgrupa where id=$labgrupa");
										$labgrupa_naziv=db_result($q4,0,0);
									}
								}
								$interval=$kraj-$pocetak;
								
								if($gdje["red_stavke"][$k]==$j && $pocetak==$m){
									for($n=$zadnji;$n<$pocetak;$n++) print "<td></td>";
									$zadnji=$kraj;
									$vrijemePocS=floor(($pocetak-1)/4+8);
									$vrijemePocMin=$pocetak%4;
									if($vrijemePocMin==1) $vrijemePocM="00";
									elseif($vrijemePocMin==2) $vrijemePocM="15";
									elseif($vrijemePocMin==3) $vrijemePocM="30";
									elseif($vrijemePocMin==0) $vrijemePocM="45";
									$vrijemeP="$vrijemePocS:$vrijemePocM";
									$vrijemeKrajS=floor(($kraj-1)/4+8);
									$vrijemeKrajMin=$kraj%4;
									if($vrijemeKrajMin==1) $vrijemeKrajM="00";
									elseif($vrijemeKrajMin==2) $vrijemeKrajM="15";
									elseif($vrijemeKrajMin==3) $vrijemeKrajM="30";
									elseif($vrijemeKrajMin==0) $vrijemeKrajM="45";
									$vrijemeK="$vrijemeKrajS:$vrijemeKrajM";
									$q3=db_query("select obavezan from ponudakursa where predmet=$predmet");
									if(db_num_rows($q3)>0) $obavezan=db_result($q3,0,0);
									
									$broj_konflikata=prikaziKonflikte($id_stavke,0);
									if($labgrupa_naziv=="(Svi studenti)") $labgrupa_naziv="---";
									print "
										<td colspan=\"$interval\">
											<table class=\"cas\" align=\"center\">";
									
									if($broj_konflikata>0){
										print "
													<tr>
														<td><a href=\"?sta=studentska/raspored1&raspored_za_edit=$raspored_za_edit&konflikt=$id_stavke\"><img src=\"static/images/16x16/warning.png\" width=\"16\" height=\"16\" border=\"0\">$broj_konflikata</a></td>
													</tr>";
									}	
									print "
												<tr>
													<td><p class=\"bold\">$tip</p></td>
												</tr>
												<tr>
													<td><p class=\"plavo\">$predmet_naziv</p></td>
												</tr>";
									if($tip!='P'){
										print "
												<tr>
													<td><p class=\"bold\">$labgrupa_naziv</p></td>
												</tr>";
									}
									else 
										print "
												<tr>
													<td><p>---</p></td>
												</tr>";
										print "
												<tr>
													<td><p class=\"plavo\">$sala_naziv</p></td>
												</tr>
												<tr>
													<td><p class=\"mala_slova\">$vrijemeP-$vrijemeK</p></td>
												</tr>
												<tr>
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
		
		
		
		// ako IMA KONFLIKATA prikazuje se sljedeći dio
		else{
			print "<p><a href=\"?sta=studentska/raspored1&raspored_za_edit=$raspored_za_edit\">Vrati se nazad</a></p>";
			$id_stavke_sa_konfliktima=$_REQUEST['konflikt'];
			prikaziKonflikte($id_stavke_sa_konfliktima,1);
		}

			
	}
	
	
	// u slučaju da se ne radi o izmjeni postojećeg rasporeda prikazuje se html kod za dodavanje novog rasporeda
	else{
		
		if($uspjesno_unesen_raspored==1) nicemessage("Uspješno je unesen novi raspored.");
		if($uspjesno_obrisan_raspored==1) nicemessage("Raspored je uspješno obrisan.");
		
		print "<p><a href=\"?sta=studentska/raspored1&edit_sala=1\">Administracija sala</a></p>";
		print "<hr></hr><h4>Dodavanje novog rasporeda:</h4>";
		print genform("POST", "forma_za_unos_rasporeda"); ?>
		<input type="hidden" name="akcija" value="unos_novog_rasporeda">
		<? if($greska_postoji_raspored==1) print "<p class=\"crveno\">Postoji raspored sa tim parametrima.</p>";?>
		<table id="raspored" cellpadding="3">
		<tr>
			<td align="left" width="120">Akademska godina:</td>
			<td>
				<select name="akademska_godina">
				<?
					$q0 = db_query("select id,naziv,aktuelna from akademska_godina order by naziv desc");
					while ($r0 = db_fetch_row($q0)) {
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
				<select name="studij" id="studij">
				<?
					$q0 = db_query("select id,naziv from studij");
					while ($r0 = db_fetch_row($q0)) {
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
	           	<select name="semestar">
	           		<?
	           		for($i=1;$i<=6;$i++){ 
	           		?>
		           		<option value="<?=$i?>" <? if($greska_postoji_raspored==1 && $_POST['semestar']==$i) print " selected";?>><?=$i?></option>

	                <?
					}
	                ?>
	           	</select>	
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
		<?=genform("POST","brisanjerasporeda")?>
		<input type="hidden" name="akcija" value="obrisi_raspored">
		<input type="hidden" name="id_rasporeda_za_brisanje" id="id_rasporeda_za_brisanje" value=""></form>
		
		<hr></hr>
		<h4>Postojeći rasporedi:</h4>
		<table class="sale" border="1" cellspacing="0">
			<?
			$q1=db_query("select id,studij,akademska_godina,semestar from raspored order by akademska_godina,studij,semestar");
			if(db_num_rows($q1)<1) print "<p>Nema kreiranih rasporeda</p>";
			else{
			?>
				<th>Studij</th>
				<th>Akademska godina</th>
				<th>Semestar</th>
				<th colspan="2">Akcije</th>
				<?  
				for($i=0;$i<db_num_rows($q1);$i++){
					$id_rasporeda=db_result($q1,$i,0);
					$studij=db_result($q1,$i,1);
					$akademska_godina=db_result($q1,$i,2);
					$semestar=db_result($q1,$i,3);;
					$q2=db_query("select naziv from studij where id=$studij");
					$naziv_studija=db_result($q2,0,0);
					$q3=db_query("select naziv from akademska_godina where id=$akademska_godina");
					$naziv_akademske_godine=db_result($q3,0,0);
					print "<tr>";
					print "<td>$naziv_studija</td>";
					print "<td>$naziv_akademske_godine</td>";
					print "<td>$semestar</td>";
					print "<td width=\"80\"><a  href=\"?sta=studentska/raspored1&raspored_za_edit=$id_rasporeda\"> izmijeni </a></td>";
					print "<td width=\"80\"><a  href=\"javascript:onclick=brisanje_rasporeda('$id_rasporeda')\"> obriši </a></td>";
					print "</tr>";
				}
			}
			?>
		</table>
		<br></br>
		<hr></hr>
		<h4>Kopiranje rasporeda:</h4>
		<?=genform("POST","kopiranjerasporeda")?>
		<input type="hidden" name="akcija" value="kopiraj_raspored">
		<?
		$q01=db_query("select id,naziv from akademska_godina order by id");
		print "<p>Kopiraj sve rasporede iz";
		print "<select name=\"izvor\">";
		for($i=0;$i<db_num_rows($q01);$i++){
			$id=db_result($q01,$i,0);
			$naziv=db_result($q01,$i,1);
			print "<option value=\"$id\">$naziv</option>";
		}
		print "</select>";
		print "     u     ";
		print "<select name=\"odrediste\">";
		for($i=0;$i<db_num_rows($q01);$i++){
			$id=db_result($q01,$i,0);
			$naziv=db_result($q01,$i,1);
			print "<option value=\"$id\">$naziv</option>";
		}
		print "</select>";
		print " akademsku godinu? ";
		print "<input type=\"submit\" value=\" OK \" onclick=\"javascript:kopiranjeSvihRasporeda()\"></p>"; 
	}
	
	
// kraj stranice za rad sa rasporedom	
}
?>
</td></tr>
</table>
</center>
<?	
} // kraj modula
?>

	
