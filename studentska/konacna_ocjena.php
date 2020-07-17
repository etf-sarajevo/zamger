<?php

// Funkcija

function bos_datum($datum){
	$date = new DateTime($datum);
	return $date->format('d.m.Y');
}

function studentska_konacna_ocjena() {
	global $userid, $user_siteadmin, $user_studentska, $db;
	
	// Učitaj CSS fajl iz statitc/css/style.css
	print "<link rel=\"stylesheet\" href=\"static\css\style.css\">";
	print "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.7.2/css/all.css\" integrity=\"sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr\" crossorigin=\"anonymous\">";
	print "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>";
	
	print "<link rel=\"stylesheet\" href=\"//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css\">";
	print "<script src=\"//code.jquery.com/jquery-1.12.4.js\"></script>";
	print "<script src=\"//code.jquery.com/ui/1.12.1/jquery-ui.js\"></script>";
	
	print "<script src='static/js/uredi-historiju-studenta.js'> </script>";

	
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
	
	if($akcija == 'pregled'){
		// Daj sve ocjene po konačnoj odluci
		$query = db_query("SELECT ko.student, ko.akademska_godina, ko.predmet, ko.ocjena, ak.id, ak.naziv, p.id, p.naziv from konacna_ocjena as ko, akademska_godina as ak, predmet as p where ko.student = $student_id and ko.akademska_godina = ak.id and ko.predmet = p.id ");
	}else if($akcija == 'uredi'){
		$ag = intval($_REQUEST['ak']);
		$predmet = intval($_REQUEST['predmet']);
		
		if($ag and $predmet and $student_id){
			$konacna_ocjena = db_query("SELECT * from konacna_ocjena where akademska_godina = $ag and predmet = $predmet and student = $student_id");
			$konacna_ocjena = db_fetch_row($konacna_ocjena);
			
			$predmeti = db_query("SELECT pk.predmet, pk.akademska_godina, p.id, p.naziv from ponudakursa as pk, predmet as p where pk.akademska_godina = $ag and p.id = pk.predmet");
			$pasosi   = db_query("SELECT id, predmet, sifra, naziv, ects from pasos_predmeta where predmet = ".$konacna_ocjena[1]);
			$odluka   = db_query("SELECT * from odluka where id = ".$konacna_ocjena[6]);
			$odluka   = db_fetch_row($odluka);
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
										<a href="?sta=studentska/konacna_ocjena&student=6&akcija=uredi&ak=<?= $row['1'] ?>&predmet=<?= $row['2'] ?>">
											<button>Uredite</button>
										</a>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
						<br>
						<p>Za unos konačne ocjene po odluci, kliknite <a href="?sta=studentska/konacna_ocjena&student=<?= $student_id ?>&akcija=unos">ovdje</a>.</p>
						<?php
					}else if($akcija == 'unos' or $akcija == 'uredi'){
						?>
						<h3> <?= ($akcija == 'uredi') ? 'Uređivanje konačne ocjene ' : 'Unos konačne ocjene po odluci' ?> </h3>

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
										<option value="">Odaberite akademsku godinu</option>
										<?php
										while($ag = db_fetch_row($akademske_godine)){
											?>
											<option value="<?= $ag[0] ?>" <?= (isset($konacna_ocjena) and $ag[0] == $konacna_ocjena[2]) ? 'selected' : '' ?>><?= $ag[1] ?></option>
											<?php
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
											while($row = db_fetch_row($predmeti)){
												?>
												<option value="<?= $row[0] ?>" <?= (isset($konacna_ocjena) and $row[0] == $konacna_ocjena[1]) ? 'selected' : '' ?>><?= $row[3] ?></option>
												<?php
											}
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
											while($row = db_fetch_row($pasosi)){
												?>
												<option value="<?= $row[0] ?>" <?= (isset($konacna_ocjena) and $row[0] == $konacna_ocjena[8]) ? 'selected' : '' ?>><?= $row[2].' '.$row['3'].' ('.$row[4].' ECTS)' ?></option>
												<?php
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
