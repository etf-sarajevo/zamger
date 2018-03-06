<?

// ADMIN/NOVAGODINA - modul koji obavlja sve administrativne zadatke vezane uz početak nove akademske godine u sistemu



function admin_novagodina() {


require_once("lib/predmet.php"); // kreiraj_ponudu_kursa
require_once("lib/plan_studija.php");


if (param('akcija') == "novagodina") {
	if (param('fakatradi') != 1) $ispis=1; else $ispis=0;

	$naziv = db_escape(param('godina'));
	$ag = db_get("select id from akademska_godina where naziv like '$naziv'");
	if ($ag === false) {
		$ag = db_get("select id+1 from akademska_godina order by id desc limit 1");
		db_query("insert into akademska_godina set id=$ag, naziv='$naziv', aktuelna=0");
		print "-- Kreirana nova akademska godina '$naziv' (ID: $ag). Koristite modul 'Parametri studija' da je proglasite za aktuelnu.<br/>\n";
	} else {
		print "-- Pronađena postojeća akademska godina (ID: $ag) - neće biti kreirana nova godina.<br/>\n";
	}
	
	$q40 = db_query("select s.id, s.naziv, ts.trajanje, s.moguc_upis from studij as s, tipstudija as ts where s.tipstudija=ts.id");
	while (db_fetch4($q40, $studij, $naziv_studija, $trajanje, $moguc_upis)) {
		if ($ispis) print "-- Studij $naziv_studija<br/>\n";

		if ($moguc_upis == 0) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;!! Nije moguć upis na ovaj studij.<br/>";
			continue;
		}

		$bio=array();
		for ($sem=1; $sem<=$trajanje; $sem++) {
			if ($ispis) print "&nbsp;&nbsp;&nbsp;-- Semestar $sem<br/>\n";

			// Pronalazimo najnoviji NPP koji je stariji od važećeg u trenutku kada su studenti upisivali studij
			// U slučaju da student studira po starijem programu, modul za upis studenta na studij će kreirati ponude kurseva
			$min_god_vazenja = $ag - intval( ( $sem - 1 ) / 2 );
			$plan_studija = db_query_assoc("select ps.id, ag.naziv godina from plan_studija ps, akademska_godina ag where ps.studij=$studij and ps.godina_vazenja<=$min_god_vazenja and ps.godina_vazenja=ag.id order by ps.godina_vazenja desc limit 1");
			if ($plan_studija === false) {
				if ($ispis) print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;!! Nije pronađen plan studija mlađi od godine sa IDom $min_god_vazenja<br/>\n";
				continue;
			}
			if ($ispis) print "&nbsp;&nbsp;&nbsp;-- Plan i program ".$plan_studija['godina']."<br>\n";
			
			$plan = predmeti_na_planu($plan_studija['id'], $sem);
			foreach ($plan as $slog) {
				if ($slog['obavezan'])
					kreiraj_ponudu_kursa ($slog['predmet']['id'], $studij, $sem, $ag, true, $ispis);
				else foreach($slog['predmet'] as $slog_predmet)
					kreiraj_ponudu_kursa ($slog_predmet['id'], $studij, $sem, $ag, false, $ispis);
			}
		}
	}

	if ($ispis) {
		?><?=genform("POST")?>
		<input type="submit" value="Potvrdi">
		<input type="hidden" name="fakatradi" value="1">
		</form>
		<?
	} else {
		print "Podaci su ubačeni.";
	}


} else {
	


	$q = db_query("select naziv from akademska_godina order by id desc limit 1");
	
	?>
	<h2>Nova akademska godina</h2>
	<p>Ovaj modul kreira novu akademsku godinu u bazi, a zatim za datu godinu kreira sve predmete koji su predviđeni aktuelnim planovima svih kreiranih studija.</p>
	<p>Klikom na dugme "Kreiraj" biće najprije ispisano šta će se sve uraditi, te ponuđeno dugme "Potvrda" nakon kojeg će akcije biti izvršene i baza izmijenjena.</p>
	<p><?=genform("POST")?>
	<input type="hidden" name="akcija" value="novagodina">
	<input type="text" name="godina" size="20" value="<?=db_result($q,0,0)?>">
	<input type="submit" value=" Kreiraj novu akademsku godinu ">
	</form>
	<hr>
	<?
}





}

?>
