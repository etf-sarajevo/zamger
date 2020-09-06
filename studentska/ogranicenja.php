<?php
function studentska_ogranicenja(){
	global $userid, $user_siteadmin, $user_studentska, $db;
	
	if (!$user_studentska && !$user_siteadmin) {
		zamgerlog("nije studentska",3); // 3: error
		zamgerlog2("nije studentska");
		biguglyerror("Pristup nije dozvoljen.");
		return;
	}
	
	$predmet = intval($_GET['predmet']);
	$naziv_p = db_fetch_row(db_query("SELECT naziv FROM predmet where id = $predmet"));
	
	$aktuelna_ag = db_fetch_row(db_query("SELECT id FROM akademska_godina where aktuelna = 1"));
	$sljedeca_ag = db_fetch_row(db_query("SELECT id, naziv FROM akademska_godina WHERE id > $aktuelna_ag[0] ORDER BY id LIMIT 1;")); // Daj ID od sljedeće akademske godine
	
	
	if(isset($_POST['kapacitet'])){
		$kapacitet = intval($_POST['kapacitet']);
		$kapacitet_i = intval($_POST['kapacitet_izborni']);
		$kapacitet_k = intval($_POST['kapacitet_kolizija']);
		$kapacitet_d = intval($_POST['kapacitet_drugi_odsjek']);
		$drugi_zabr  = db_escape($_POST['drugi_odsjek_zabrane']);
		
		$broj_uzoraka = db_fetch_row(db_query("SELECT COUNT(*) FROM ugovoroucenju_kapacitet where predmet = $predmet and akademska_godina = $sljedeca_ag[0]"))[0];
		if($broj_uzoraka){
			db_query("UPDATE ugovoroucenju_kapacitet SET
                        kapacitet = '{$kapacitet}',
						kapacitet_izborni = '{$kapacitet_i}',
						kapacitet_kolizija = '{$kapacitet_k}',
						kapacitet_drugi_odsjek = '{$kapacitet_d}',
						drugi_odsjek_zabrane = '{$drugi_zabr}'
					where predmet = $predmet and akademska_godina = $sljedeca_ag[0]"
			);
		}else{
			$odluka = db_query("INSERT INTO ugovoroucenju_kapacitet set
				kapacitet = '{$kapacitet}',
				kapacitet_izborni = '{$kapacitet_i}',
				kapacitet_kolizija = '{$kapacitet_k}',
				kapacitet_drugi_odsjek = '{$kapacitet_d}',
				drugi_odsjek_zabrane = '{$drugi_zabr}',
				predmet = '{$predmet}',
				akademska_godina = '{$sljedeca_ag[0]}'
			");
		}
	}
	
	// Učitaj CSS
	print "<link rel=\"stylesheet\" href=\"static\css\style.css\">";

	?>
	<center>
		<table border="0" width="700">
			<tr>
				<td>
					<?php
					if($_GET['prikazi'] == 'sve'){ // Ispiši sva ograničenja na predmetu
						$kapaciteti = db_query("SELECT uk.akademska_godina, ak.id, ak.naziv FROM ugovoroucenju_kapacitet as uk, akademska_godina as ak where ak.id = uk.akademska_godina and uk.predmet = ".$predmet);
						
						?>
						<a href="?sta=studentska/predmeti&akcija=edit&predmet=<?= $predmet; ?>&ag=<?= $aktuelna_ag[0] ?>"> Nazad na podatke o predmetu </a>
						<h3> Pregled svih ograničenja za predmet <?= $naziv_p[0] ?></h3>
						
						<table class="my-table" cellpadding="0" cellspacing="0">
							<thead>
							<tr>
								<td class="width-40">#</td>
								<td>Akademska godina</td>
								<td class="width-100">Akcije</td>
							</tr>
							</thead>
							<tbody>
							<?php
							$counter = 1;
							while($row = db_fetch_row($kapaciteti)){
								?>
								<tr>
									<td><?= $counter++; ?></td>
									<td><?= $row[2] ?></td>
									<td>
										<a href="?sta=studentska/ogranicenja&predmet=<?= $predmet; ?>&prikazi=azuriraj&ak=<?= $row[0] ?>">
											<button>Pregled</button>
										</a>
									</td>
								</tr>
								<?php
							}
							?>
							</tbody>
						</table>
						<br>
						<p>Za postavljanje ograničenja za akademsku godinu <?= $sljedeca_ag[1] ?>, kliknite <a href="?sta=studentska/ogranicenja&predmet=<?= $predmet; ?>&prikazi=azuriraj&ak=<?= $sljedeca_ag[0] ?>">ovdje</a>.</p>

						<?php
					}else{ // Pregled / uređivanje ograničenja
						// Ukoliko postoje podaci, prikaži ih, ukoliko ne, ostavi prazno
						$uzorak = db_fetch_row(db_query("SELECT * FROM ugovoroucenju_kapacitet where predmet = $predmet and akademska_godina = $sljedeca_ag[0]"));
						
						?>
						<a href="?sta=studentska/ogranicenja&predmet=<?= $predmet; ?>&prikazi=sve"> Nazad na pregled </a>
						<h3> Ograničenje za akademsku <?= $sljedeca_ag[1] ?> godinu</h3>
						
						<form action="" method="POST">
							<div class="input-row">
								<div class="input-col">
									<div class="form-label"><b>Predmet</b></div>
									<input type="text" class="form-input" value="<?= $naziv_p[0] ?>" readonly>
								</div>
								<div class="input-col">
									<div class="form-label"><b>Kapacitet</b></div>
									<input type="number" name="kapacitet" class="form-input" value="<?= $uzorak ? $uzorak[2] : '-1' ?>" min="-1">
									<p>Unesite 0 ukoliko predmet ide, -1 bez ograničenja</p>
								</div>
							</div>
							<div class="input-row">
								<div class="input-col">
									<div class="form-label"><b>Kapacitet za izborni</b></div>
									<input type="number" name="kapacitet_izborni" class="form-input" value="<?= $uzorak ? $uzorak[3] : '-1' ?>" min="-1">
									<p>Unesite 0 ukoliko niko ne može, -1 bez ograničenja</p>
								</div>
								<div class="input-col">
									<div class="form-label"><b>Kapacitet za koliziju</b></div>
									<input type="number" name="kapacitet_kolizija" class="form-input" value="<?= $uzorak ? $uzorak[4] : '-1' ?>" min="-1">
									<p>Unesite 0 ukoliko predmet ne ide na koliziji</p>
								</div>
							</div>
							<div class="input-row">
								<div class="input-col">
									<div class="form-label"><b>Kapacitet za drugi odsjek</b></div>
									<input type="number" name="kapacitet_drugi_odsjek" class="form-input" value="<?= $uzorak ? $uzorak[5] : '-1' ?>" min="-1">
									<p>Unesite 0 ukoliko ne može niko sa drugog odsjeka</p>
								</div>
								<div class="input-col">
									<div class="form-label"><b>Zabrane za drugi odsjek</b></div>
									<input type="text" name="drugi_odsjek_zabrane" class="form-input" value="<?= $uzorak ? $uzorak[6] : '' ?>">
									<p>Spisak odsjeka za zabranu</p>
								</div>
							</div>
							
							<?php
							if(intval($_GET['ak']) == $sljedeca_ag[0]){
								?>
								<div class="input-row">
									<div class="input-col input-col-button">
										<input type="submit" class="" value=SPREMITE>
									</div>
								</div>
								<?php
							}
							?>
						</form>
						<?php
					}
					?>
					
				</td>
			</tr>
		</table>
	</center>
	<?php
}