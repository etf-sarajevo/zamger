<?


// NASTAVNIK/DODAVANJE_ASISTENATA - dodavanje saradnika (demonstratora i asistenata) na predmet

function nastavnik_dodavanje_asistenata() {
	global $userid, $_api_http_code;

	$ag = intval($_GET['ag']);
	$predmet = intval($_GET['predmet']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];

	// ** Ukoliko asistent ili superasistent pokušaju pristupiti ovoj opciji ** //
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/dodavanje_asistenata privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	$teachers = api_call("course/$predmet/$ag/access")["results"];
	
	// ** Ukoliko asistent ili superasistent pokušaju pristupiti ovoj opciji ** //
	foreach($teachers as $teacher) {
		if ($teacher['Person']['id'] == $userid && $teacher['accessLevel'] != "nastavnik") {
			zamgerlog("nastavnik/dodavanje_asistenata privilegije (predmet pp$predmet)",3);
			zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
			biguglyerror("Nemate pravo pristupa ovoj opciji");
			return;
		}
	}

	if(isset($_POST['osoba'])){
		$osoba = intval($_POST['osoba']);
		$nivoPristupa = $_POST['uloga'];

		if ($osoba and $nivoPristupa) { // Da li su vrijednosti prave ili je prazan request
			if ($osoba != 0 and $nivoPristupa != '' and $nivoPristupa != 'obrisi') {
				// Provjeri da li osoba već ima status nastavnika
				
				$vecIma = false;
				foreach($teachers as $teacher) {
					if ($teacher['Person']['id'] == $osoba)
						$vecIma = true;
				}
				
				if ($vecIma)
					$result = api_call("course/$predmet/$ag/access/$osoba", ["accessLevel" => $nivoPristupa], "PUT" );
				else
					$result = api_call("course/$predmet/$ag/access/$osoba", ["accessLevel" => $nivoPristupa], "POST" );
			} else if ($nivoPristupa == 'obrisi') {
				$result = api_call("course/$predmet/$ag/access/$osoba", [], "DELETE" );
				$osoba = null;
			}
			if ($_api_http_code != "201" && $_api_http_code != "204") {
				$greska = 'Promjena nivoa pristupa nije uspjela: ' . $result['message'];
			} else {
				$teachers = api_call("course/$predmet/$ag/access")["results"];
			}
		} else {
			$greska = 'Molimo Vas da odaberete osobu ili nivo pristupa';
		}
	}

	$persons = api_call("person/all")["results"];

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
			foreach ($persons as $person) {
				?>
				<option value="<?php echo $person['id']; ?>" <?php if(isset($osoba) and $osoba == $person['id']){ echo 'selected';} ?> >
					<?php 
						echo $person['surname'].' '.$person['name'];
						if ($person['studentIdNr']) echo " (".$person['studentIdNr'].")";
					?>
				</option>
				<?php
			}
			?>
		</select>

		<select name="uloga" class="users-search">
			<option value="">Odaberite nivo pristupa</option>
			<option value="asistent" <?php if(isset($nivoPristupa)){if($nivoPristupa == 'asistent') echo 'selected';} ?>>Asistent</option>
			<option value="super_asistent" <?php if(isset($nivoPristupa)){if($nivoPristupa == 'super_asistent') echo 'selected';} ?>>Superasistent</option>
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
		
		foreach($teachers as $teacher) {
			if ($teacher['accessLevel'] == "nastavnik")
				continue; // Nastavnik ne može mijenjati nastavnike
			?>
			<tr>
				<form method="post">
					<td><?= $counter++; ?>.</td>
					<td><?= $teacher['Person']['name'].' '.$teacher['Person']['surname']; ?></td>
					<td>
						<input type="hidden" name="osoba" value="<?= $teacher['Person']['id']; ?>">
						<select name="uloga">
							<option value="obrisi">Zabranite pristup</option>
							<option value="asistent" <?= ($teacher['accessLevel'] == 'asistent') ? 'selected' : ''; ?> >Asistent</option>
							<option value="super_asistent" <?= ($teacher['accessLevel'] == 'super_asistent') ? 'selected' : ''; ?> >Superasistent</option>
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
