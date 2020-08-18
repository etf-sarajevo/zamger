<?


// NASTAVNIK/DODAVANJE_ASISTENATA - dodavanje saradnika (demonstratora i asistenata) na predmet

function nastavnik_dodavanje_asistenata(){
	global $userid, $user_siteadmin;

	$osobe = db_query("select id, ime, prezime, brindexa from osoba");
	$akademska_godina = intval($_GET['ag']);
	$predmet = intval($_GET['predmet']);

	// Naziv predmeta
	$q10 = db_query("select naziv from predmet where id=$predmet");
	if (db_num_rows($q10)<1) {
		biguglyerror("Nepoznat predmet");
		zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
		zamgerlog2("nepoznat predmet", $predmet);
		return;
	}
	$predmet_naziv = db_result($q10,0,0);
	
	$pasos = db_get("SELECT pasos_predmeta FROM akademska_godina_predmet WHERE predmet=$predmet AND akademska_godina=$akademska_godina");
	if ($pasos) {
		$predmet_naziv = db_get("SELECT naziv FROM pasos_predmeta WHERE id=$pasos");
	}

	// ** Ukoliko asistent ili superasistent pokušaju pristupiti ovoj opciji ** //
	if (!$user_siteadmin) {
		$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$akademska_godina");
		if (db_num_rows($q10)<1 || db_result($q10,0,0)!="nastavnik") {
			zamgerlog("nastavnik/dodavanje_asistenata privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije nastavnik na predmetu", $predmet, $akademska_godina);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	if(!$akademska_godina or !$predmet){
		// Ovdje možemo throw-ati error neki ili ukinuti u potpunosti stranicu - Stavit ću samo die u ovom slučaju
		die();
	}

	if(isset($_POST['osoba'])){
		$osoba = intval($_POST['osoba']);
		$nivo_prist = $_POST['uloga'];

		if($osoba and $nivo_prist){ // Da li su vrijednosti prave ili je prazan request
			if($osoba != 0 and $nivo_prist != '' and $nivo_prist != 'obrisi'){
				// Provjeri da li osoba već ima status nastavnika
				$nastavnik = db_get("select count(osoba) from privilegije where privilegija = 'nastavnik' and osoba = ".$osoba);

				if(!$nastavnik){
					db_query("INSERT into privilegije set osoba = $osoba, privilegija = 'nastavnik'");
				}

				// Provjeravamo da li ima pravo pristupa na predmetu i ako ima, koje je to pravo
				$nivo_pristupa = db_get("select * from nastavnik_predmet where nastavnik = $osoba and akademska_godina = $akademska_godina and predmet = ".$predmet);

				if(!$nivo_pristupa){
					db_query("INSERT INTO nastavnik_predmet SET nastavnik = $osoba, akademska_godina = $akademska_godina, nivo_pristupa = '$nivo_prist', predmet = ".$predmet);
				}else{
					db_query("UPDATE nastavnik_predmet SET nivo_pristupa = '$nivo_prist' where nastavnik = $osoba");
				}
			}else if($nivo_prist == 'obrisi'){
				db_query("delete from nastavnik_predmet where nastavnik=$osoba and akademska_godina = $akademska_godina and predmet = ".$predmet);
				$osoba = null;
			}
		}else{
			$greska = 'Molimo Vas da odaberete osobu ili nivo pristupa';
		}
	}

	$angazovane_osobe = db_query("select o.ime, o.prezime, o.id, np.nivo_pristupa from nastavnik_predmet as np inner join osoba as o on np.nastavnik = o.id where (np.nivo_pristupa = 'asistent' or np.nivo_pristupa = 'super_asistent') and np.akademska_godina = $akademska_godina and np.predmet = ".$predmet);

	?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet"/>

	<br><br>
	<p><h3><?=$predmet_naziv?> - Saradnici na predmetu</h3></p>
	<p><b>Dodajte demonstratora ili asistenta na predmet</b></p>
	<p>
	<form method="post">
		<select name="osoba" class="users-search">
			<option value="0">Odaberite osobu</option>
			<?php
			while ($o = db_fetch_row($osobe)) {
				?>
				<option value="<?php echo $o[0]; ?>" <?php if(isset($osoba) and $osoba == $o[0]){ echo 'selected';} ?> >
					<?php 
						echo $o[1].' '.$o[2]; 
						if ($o[3]) echo " (".$o[3].")";
					?>
				</option>
				<?php
			}
			?>
		</select>

		<select name="uloga" class="users-search">
			<option value="">Odaberite nivo pristupa</option>
			<option value="asistent" <?php if(isset($nivo_prist)){if($nivo_prist == 'asistent') echo 'selected';} ?>>Asistent</option>
			<option value="super_asistent" <?php if(isset($nivo_prist)){if($nivo_prist == 'super_asistent') echo 'selected';} ?>>Superasistent</option>
		</select>

		<input type="submit" value="Dodaj" style="height: 28px; padding-left:20px; padding-right: 20px; background: #fff; border:1px solid rgba(0,0,0,0.3); border-radius:3px;">
	</form>
	</p>

	<!-- Ukoliko pokuša unijeti kreirati request sa praznim parametrima -->
	<p style="color: red;"> <?= isset($greska) ? $greska : ''; ?> </p>

	<p>
		LEGENDA: <br>
		Asistent - asistent ima pravo samo da unosi časove, prisustvo i ocjenjuje zadaće <br>
		Superasistent - ima sve mogućnosti osim da unosi konačne ocjene i mijenja sistem bodovanja
	</p>

	<br>
	<p><h3>Pregled angažovanih saradnika na predmetu</h3></p>
	<p>
	<table border="1" cellspacing="0" cellpadding="5">
		<thead>
		<tr>
			<th>#</font></th>
			<th>Ime i prezime</th>
			<th>Nivo pristupa</th>
			<th style="text-align: center">Akcije</th>
		</tr>
		</thead>
		<tbody>

		<?php $counter = 1;
		while ($o = db_fetch_row($angazovane_osobe)) {
			?>
			<tr>
				<form method="post">
					<td><?= $counter++; ?>.</td>
					<td><?= $o[0].' '.$o[1]; ?></td>
					<td>
						<input type="hidden" name="osoba" value="<?= $o[2]; ?>">
						<select name="uloga">
							<option value="obrisi">Zabranite pristup</option>
							<option value="asistent" <?= ($o[3] == 'asistent') ? 'selected' : ''; ?> >Asistent</option>
							<option value="super_asistent" <?= ($o[3] == 'super_asistent') ? 'selected' : ''; ?> >Superasistent</option>
						</select>
					</td>
					<td style="text-align: center;">
						<input type="submit" class="default" value="Izmjena">
					</td>
				</form>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	</p>
	<script>
		$(document).ready(function() {
			$('.users-search').select2();
		});
	</script>
	<?php
}
