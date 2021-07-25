<?php
function studentska_uredi_historiju_studenta(){
	global $userid, $user_siteadmin, $user_studentska;
	
	// Učitaj CSS fajl iz statitc/css/style.css
	print "<link rel=\"stylesheet\" href=\"static\css\style.css\">";
	print "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.7.2/css/all.css\" integrity=\"sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr\" crossorigin=\"anonymous\">";
	print "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>";
	print "<script src='static/js/uredi-historiju-studenta.js'> </script>";
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska"); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	// Ažurirajmo podatke
	if(isset($_POST['student']) and isset($_POST['studij']) and isset($_POST['semestar']) and isset($_POST['akademska_godina'])){
		$f_student = intval($_POST['student']);
		$f_studij = intval($_POST['studij']);
		$f_semestar = intval($_POST['semestar']);
		$f_ag = intval($_POST['akademska_godina']);
		
		if($f_student and $f_student and $f_semestar and $f_ag){ // Ažurirajmo podatke
			$f_nacin_studiranja = intval($_POST['nacin_studiranja']);
			$f_ponovac = intval($_POST['ponovac']);
			$f_status_studenta = intval($_POST['status_studenta']);
			$f_napomena = db_escape($_POST['napomena']);
			
			$f_datum = date('Y-m-d');
			
			db_query("UPDATE student_studij SET
                          nacin_studiranja = '$f_nacin_studiranja',
                          ponovac = '$f_ponovac',
                          status_studenta = '$f_status_studenta',
                          napomena = '$f_napomena',
                          datum_azuriranja = '$f_datum',
                          osoba_id = '$userid'
					where student = $f_student and studij = $f_studij and semestar = $f_semestar and akademska_godina = $f_ag"
			);
		}
	}
	
	
	$student_id = intval($_REQUEST['student']);
	$osoba = db_query("SELECT ime, prezime, spol FROM osoba where id = ".$student_id);
	$osoba = db_fetch_row($osoba);

	if($osoba[2]=="Z") $upisa = 'Upisala';
	else $upisa = "Upisao";
	
	print '<a href="?sta=studentska/osobe&search=sve&akcija=edit&osoba='.$student_id.'"> Nazad na podatke o studentu </a>';
	print '<h2> Uredite historiju studenta - '.$osoba[0].' '.$osoba[1].' </h2>';
	
	// Status studenta - iz tabele status_studenta - šifarnik
	$status_studenta = db_query("SELECT * FROM status_studenta");
	// Način studiranja - iz tabele nacin_studiranja (Gdje je moguć upis 1) - šifarnik
	$nacin_studiranja = db_query("SELECT * FROM nacin_studiranja where moguc_upis = 1");
	
	$q10 = db_query("select id,naziv from akademska_godina order by id");
	while ($r10 = db_fetch_row($q10)) {
		$ag = $r10[0];
		$agnaziv = $r10[1];
		
		// Historija upisa na studije - tabela student_studij
		
		$q20 = db_query("select s.naziv, ss.semestar, ns.naziv, ss.ponovac, ss.odluka, ss.studij, ss.nacin_studiranja, ss.status_studenta, s_s.naziv, ss.napomena from studij as s, student_studij as ss, nacin_studiranja as ns, status_studenta as s_s where s.id=ss.studij and ns.id=ss.nacin_studiranja and ss.student=$student_id and ss.akademska_godina=$ag and ss.status_studenta=s_s.id order by ss.akademska_godina,ss.semestar");
		while ($r20 = db_fetch_row($q20)) {
			$semestar = $r20[1];
			print "<p class='edit-paragraph'><b>$agnaziv</b>: $upisa studij \"$r20[0]\", $semestar. semestar, kao $r20[2] student";
			if($r20[7]>0) print " (Status studenta - ".$r20[8].")";
			if ($r20[3]>0) print " (ponovac)";
			if ($r20[4]>0) {
				$q25 = db_query("select UNIX_TIMESTAMP(datum), broj_protokola from odluka where id=$r20[4]");
				print " na osnovu odluke ".db_result($q25,0,1)." od ".date("d. m. Y", db_result($q25,0,0));
			}
			print "
				<span class='edit-paragraph-trig' title='Uredite podatke za studenta ".$osoba[0].' '.$osoba[1].", ".$agnaziv." godine - ".$semestar.". semestar' ak-god='".$ag."' ak-naziv='".$agnaziv."' studij='".$r20[5]."' studij-naziv='".$r20[0]."' semestar='".$semestar."' nacin-studiranja='".$r20[6]."' ponovac='".$r20[3]."' status_studenta='".$r20[7]."' napomena='".$r20[9]."'>
				 	- Uredite
				 </span>
				";
			print "<br> <br>\n </p>";
		}
	}
	
	?>
	
	<div class="pop-up-shadow">
		<div class="generic-pop-up">
			<div class="pop-up-header">
				<h4>Uredite historiju</h4>
				<i class="fas fa-times close-pop-up" title="Zatvorite"></i>
			</div>
			<form action="?sta=studentska/uredi_historiju_studenta&student=<?= $student_id ?>" method="post">
				<div class="pop-up-body">
					<div class="row">
						<div class="col">
							<label>Ime i prezime</label>
							<input type="hidden" name="student" id="student" value="<?= $student_id; ?>">
							<input type="text" name="student_value" id="student_value" value="<?= $osoba[0].' '.$osoba[1]; ?>" readonly>
						</div>
						<div class="col">
							<label>Studij</label>
							<input type="hidden" name="studij" id="studij" value="">
							<input type="text" name="studij_value" id="studij_value" value="" readonly>
						</div>
					</div>
					
					<div class="row">
						<div class="col">
							<label>Semestar</label>
							<input type="hidden" name="semestar" id="semestar" value="">
							<input type="text" name="semestar_value" id="semestar_value" value="" readonly>
						</div>
						<div class="col">
							<label>Akademska godina</label>
							<input type="hidden" name="akademska_godina" id="akademska_godina" value="">
							<input type="text" name="akademska_godina_value" id="akademska_godina_value" value="" readonly>
						</div>
					</div>
					
					<div class="row">
						<div class="col">
							<label>Način studiranja</label>
							<select name="nacin_studiranja" id="nacin_studiranja">
								<?php
								while($nacin = db_fetch_row($nacin_studiranja)){
									print '<option value="'.$nacin[0].'">'.$nacin[1].'</option>';
								}
								?>
							</select>
						</div>
						<div class="col">
							<label>Ponovac</label>
							<select name="ponovac" id="ponovac">
								<option value="0">NE</option>
								<option value="1">DA</option>
							</select>
						</div>
					</div>
					
					<div class="row">
						<div class="col">
							<label>Status studenta</label>
							<select name="status_studenta" id="status_studenta">
								<?php
								while($status = db_fetch_row($status_studenta)){
									print '<option value="'.$status[0].'">'.$status[1].'</option>';
								}
								?>
							</select>
						</div>
					</div>
					
					<div class="row">
						<div class="col">
							<label>Napomena: </label>
							<textarea name="napomena" id="napomena"></textarea>
						</div>
					</div>
					
					<div class="row button-row">
						<input type="submit" value="SPREMITE">
					</div>
				</div>
			</form>
		</div>
	</div>
	
	<?php
}

?>
