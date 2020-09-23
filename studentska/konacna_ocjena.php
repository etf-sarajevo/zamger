<?php

// STUDENTSKA/KONACNA_OCJENA - unos ocjena po odluci i izmjena statusa




// Pomoćna funkcija za datume

function bos_datum($datum){
	$date = new DateTime($datum);
	return $date->format('d.m.Y');
}

function studentska_konacna_ocjena() {
	global $userid, $user_siteadmin, $user_studentska, $db;
	
	// Učitaj CSS fajl iz static/css/style.css
	?>
	<link rel="stylesheet" href="static\css\style.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.12.4.js"></script>
	<script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	
	<script src='static/js/konacna_ocjena.js'> </script>
	<?
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska"); // 3: error
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	// ********************************************** POST REQUEST ************************************************** //
	
	if(isset($_REQUEST['student']) and isset($_REQUEST['akademska_godina']) and ($user_studentska or $user_siteadmin)){ // Pošto su svi required - dovoljan check za ovo dvoje
		
		$datum_odluke = date("Y-m-d", strtotime(str_replace('/', '-', db_escape($_REQUEST['datum_odluke']))));
		$broj_protokola = db_escape($_REQUEST['broj_protokola']);
		
		// Prvo provjeravamo da li ima odluka u tabeli "odluka"
		$odluka = db_query("SELECT * from odluka where datum = '{$datum_odluke}' and broj_protokola = '{$broj_protokola}'");
		$odluka = db_fetch_row($odluka);
		// var_dump($odluka);
		
		if(!$odluka){ // Ako nema te odluke, unesi novu i vrati njen ID
			$odluka = db_query("INSERT INTO odluka set datum = '{$datum_odluke}', broj_protokola = '{$broj_protokola}'");
			$odluka = db_fetch_row(db_query("SELECT LAST_INSERT_ID() from odluka"));
		}
		$odluka = $odluka[0];
		
		$student = intval($_REQUEST['student']);
		$predmet = intval($_REQUEST['predmet']);
		$ag      = intval($_REQUEST['akademska_godina']);
		$ocjena  = intval($_REQUEST['ocjena']);
		$datum   = date("Y-m-d", strtotime(str_replace('/', '-', db_escape($_REQUEST['datum']))));
		$datum_i = date("Y-m-d", strtotime(str_replace('/', '-', db_escape($_REQUEST['datum_u_indeksu']))));
		$datum_p = intval($_REQUEST['datum_provjeren']);
		$pasos   = intval($_REQUEST['pasos_predmeta']);
		
		$uredi   = intval($_REQUEST['uredi']);
		
		if($student and $predmet and $ag and $ocjena and $pasos){
			if($uredi){
				db_query("UPDATE konacna_ocjena SET
                          student = '$student',
                          predmet = '$predmet',
                          akademska_godina = '$ag',
                          ocjena = '$ocjena',
                          datum = '{$datum}',
                          datum_u_indeksu = '{$datum_i}',
                          odluka = '{$odluka}',
                          datum_provjeren = '{$datum_p}',
                          pasos_predmeta = '{$pasos}'
					where student = $student and predmet = $predmet and akademska_godina = $ag"
				);
				
			}else db_query("INSERT INTO konacna_ocjena SET student = $student, predmet = $predmet, akademska_godina = $ag, ocjena = $ocjena, datum = '{$datum}', datum_u_indeksu = '{$datum_i}', odluka = $odluka, datum_provjeren = $datum_p, pasos_predmeta = $pasos");
		}
		
	}
	
	
	$student_id = intval($_REQUEST['student']);
	$akcija = $_REQUEST['akcija'];
	
	$osoba = db_query("SELECT ime, prezime, spol FROM osoba where id = ".$student_id);
	$osoba = db_fetch_row($osoba);
	
	$brojac = 1; // Brojač za index u tabeli
	
	// Izaberi akademske godine
	$akademske_godine = db_query("SELECT DISTINCT ss.akademska_godina, ak.naziv, ak.id from student_studij as ss, akademska_godina as ak where ss.student = $student_id and ss.akademska_godina = ak.id");
	
	if($akcija == 'pregled') {
		if ($_REQUEST['sve']) $uslov = "";
		else $uslov = "AND ko.odluka is not null";
		
		// Daj sve ocjene po konačnoj odluci
		$query = db_query("SELECT ko.student, ko.akademska_godina, ko.predmet, ko.ocjena, ag.id, ag.naziv, p.id, pp.naziv
			FROM konacna_ocjena as ko, akademska_godina as ag, predmet as p, pasos_predmeta pp
			WHERE ko.student=$student_id and ko.akademska_godina=ag.id and ko.predmet=p.id and ko.pasos_predmeta=pp.id $uslov
			ORDER BY ag.id, pp.naziv");
	}else if($akcija == 'uredi'){
		$ag = intval($_REQUEST['ak']);
		$predmet = intval($_REQUEST['predmet']);
		
		if($ag and $predmet and $student_id){
			$konacna_ocjena = db_query("SELECT ko.student, ko.predmet, ko.akademska_godina, ko.ocjena, ko.datum, ko.datum_u_indeksu, ko.odluka, ko.datum_provjeren, ko.pasos_predmeta, ak.id, ak.naziv, p.id, p.naziv, pp.id, pp.predmet, pp.sifra, pp.naziv, pp.ects from konacna_ocjena as ko, akademska_godina as ak, predmet as p, pasos_predmeta as pp where ko.student = $student_id and ko.predmet = $predmet and ko.akademska_godina = $ag and ko.akademska_godina = ak.id and ko.predmet = p.id and ko.pasos_predmeta = pp.id ");
			$konacna_ocjena = db_fetch_row($konacna_ocjena);
			
			// $predmeti = db_query("SELECT pk.predmet, pk.akademska_godina, p.id, p.naziv from ponudakursa as pk, predmet as p where pk.akademska_godina = $ag and p.id = pk.predmet");
			$pasosi   = db_query_table("SELECT DISTINCT pp.id, pp.predmet, pp.sifra, pp.naziv, pp.ects from pasos_predmeta pp, plan_studija_predmet psp where psp.pasos_predmeta=pp.id AND pp.predmet = ".$konacna_ocjena[1]);
			$pasosi2   = db_query_table("SELECT DISTINCT pp.id, pp.predmet, pp.sifra, pp.naziv, pp.ects from pasos_predmeta pp, plan_izborni_slot pis where pis.pasos_predmeta=pp.id AND pp.predmet = ".$konacna_ocjena[1]);
			foreach($pasosi2 as $pasos2) {
				$postoji = false;
				foreach($pasosi as $pasos) {
					if ($pasos['id'] == $pasos2['id']) $postoji = true;
				}
				if (!$postoji) $pasosi[] = $pasos2;
			}
			if ($konacna_ocjena[6]) {
				$odluka   = db_query("SELECT * from odluka where id = ".$konacna_ocjena[6]);
				$odluka   = db_fetch_row($odluka);
			}
		}
	}
	?>
	<center>
		<table border="0" width="700">
			<tr>
				<td>
					<a href="?sta=studentska/osobe&search=sve&akcija=edit&osoba=<?= $student_id; ?>"> Nazad na podatke o studentu </a>
					
					<?php
					if($akcija == 'pregled'){
						?>
						<h3> Pregled svih ocjena po odluci</h3>

						<table class="my-table" cellpadding="0" cellspacing="0">
							<thead>
							<tr>
								<td>#</td>
								<td>Akademska godina</td>
								<td>Predmet</td>
								<td>Ocjena</td>
								<td>Akcije</td>
							</tr>
							</thead>
							<tbody>
							<?php
							while($row = db_fetch_row($query)){
								?>
								<tr>
									<td><?= $brojac++ ?>.</td>
									<td><?= $row['5'] ?></td>
									<td><?= $row['7'] ?></td>
									<td><?= $row['3'] ?></td>
									<td style="width: 80px;">
										<a href="?sta=studentska/konacna_ocjena&student=<?= $row['0'] ?>&akcija=uredi&ak=<?= $row['1'] ?>&predmet=<?= $row['2'] ?>">
											<button>Uredite</button>
										</a>
									</td>
								</tr>
								<?php
							}
							?>$konacna_ocjena
							</tbody>
						</table>
						<br>
						<p>Za unos konačne ocjene po odluci, kliknite <a href="?sta=studentska/konacna_ocjena&student=<?= $student_id ?>&akcija=unos">ovdje</a>.</p>
						<p>Za prikaz svih ocjena (ne samo ocjena po odluci), kliknite <a href="?sta=studentska/konacna_ocjena&student=<?= $student_id ?>&akcija=pregled&sve=1">ovdje</a>.</p>
						<?php
					}else if($akcija == 'unos' or $akcija == 'uredi'){
						?>
						<h3> <?= ($akcija == 'uredi') ? 'Uređivanje konačne ocjene ( <a href="#" class="obrisi-konacnu-ocjenu" st="'.$student_id.'" ak="'.$konacna_ocjena[2].'" pr="'.$konacna_ocjena[1].'"> OBRIŠITE </a>) ' : 'Unos konačne ocjene po odluci' ?> </h3>
						
						<form action="" method="POST">
							<div class="input-row">
								<div class="input-col">
									<div class="form-label">Student</div>
									<input type="text" class="form-input" value="<?= $osoba[0].' '.$osoba[1]; ?>" readonly>
									<input type="hidden" name="student" id="ocjena-po-odluci-student" value="<?= $student_id ?>">
									<!-- Da iskoristimo istu formu za uređivanje :: Ako ima ovaj input, onda ga uređujemo ! -->
									<?= isset($konacna_ocjena) ? '<input type="hidden" name="uredi" value="1">' : '' ?>
								</div>
								<div class="input-col">
									<div class="form-label">Akademska godina</div>
									<select name="akademska_godina" id="ocjena-po-odluci-ag" class="form-input form-input-select" required="required">
										<?php
										if(isset($konacna_ocjena)){
											print '<option value="' . $konacna_ocjena[2] . '"> ' . $konacna_ocjena[10] . ' </option>';
										}else{
											print '<option value="">Odaberite akademsku godinu</option>';
											while($ag = db_fetch_row($akademske_godine)){
												?>
												<option value="<?= $ag[0] ?>" <?= (isset($konacna_ocjena) and $ag[0] == $konacna_ocjena[2]) ? 'selected' : '' ?>><?= $ag[1] ?></option>
												<?php
											}
										}
										?>
									</select>
								</div>
							</div>

							<div class="input-row">
								<div class="input-col">
									<div class="form-label">Predmet</div>
									<select name="predmet" id="ocjena-po-odluci-predmet" class="form-input form-input-select" required="required">
										<?php
										if(isset($konacna_ocjena)){
											print '<option value="'.$konacna_ocjena[1].'"> '.$konacna_ocjena[12].' </option>';
										}else{
											print '<option value="">Odaberite akademsku godinu</option>';
										}
										?>
									</select>
								</div>

								<div class="input-col">
									<div class="form-label">Pasoš predmeta</div>
									<select name="pasos_predmeta" id="ocjena-po-odluci-pasos" class="form-input form-input-select" required="required">
										<?php
										if(isset($konacna_ocjena)){
											foreach($pasosi as $pasos) {
												if ($pasos['id'] == $konacna_ocjena[8]) $sel = "SELECTED"; else $sel = "";
												print '<option value="'.$pasos['id'].'" '.$sel.'> '.$pasos['sifra'].' '.$pasos['naziv'].' ('.$pasos['ects'].' ECTS) </option>';
											}
										}else{
											print '<option value="">Odaberite akademsku godinu</option>';
										}
										?>
									</select>
								</div>
							</div>

							<div class="input-row">
								<div class="input-col">
									<div class="form-label">Ocjena</div>
									<input type="number" name="ocjena" class="form-input" value="<?= isset($konacna_ocjena) ? $konacna_ocjena[3] : '' ?>" required="required" min="5" max="10">
								</div>
								<div class="input-col">
									<div class="form-label">Datum</div>
									<input type="text" name="datum" class="form-input datepicker-2" value="<?= isset($konacna_ocjena) ? bos_datum($konacna_ocjena[4]) : '' ?>" required="required">
								</div>
							</div>

							<div class="input-row">
								<div class="input-col">
									<div class="form-label">Datum u indeksu</div>
									<input type="text" name="datum_u_indeksu" class="form-input datepicker-2" value="<?= isset($konacna_ocjena) ? bos_datum($konacna_ocjena[5]) : '' ?>" required="required">
								</div>
								<div class="input-col">
									<div class="form-label">Datum provjeren</div>
									<select name="datum_provjeren" id="" class="form-input form-input-select" required="required">
										<option value="0" <?= (isset($konacna_ocjena) and $konacna_ocjena[7] == 0) ? 'selected' : '' ?>>Ne</option>
										<option value="1" <?= (isset($konacna_ocjena) and $konacna_ocjena[7] == 1) ? 'selected' : '' ?>>Da</option>
									</select>
								</div>
							</div>

							<div class="input-row">
								<div class="input-col">
									<div class="form-label">Datum odluke</div>
									<input type="text" name="datum_odluke" class="form-input datepicker-2" value="<?= isset($konacna_ocjena) ? bos_datum($odluka[1]) : '' ?>"  required="required">
								</div>

								<div class="input-col">
									<div class="form-label">Broj protokola</div>
									<input type="text" name="broj_protokola" class="form-input" value="<?= isset($konacna_ocjena) ? $odluka[2] : '' ?>" required="required">
								</div>
							</div>

							<div class="input-row">
								<div class="input-col input-col-button">
									<input type="submit" class="" value=SPREMITE>
								</div>
							</div>
						</form>

						<br>
						<p>Za pregled konačnih ocjena po odluci, kliknite <a href="?sta=studentska/konacna_ocjena&student=<?= $student_id ?>&akcija=pregled">ovdje</a>.</p>
						<?php
					}
					?>
				</td>
			</tr>
		</table>
	</center>

	<?php
}
