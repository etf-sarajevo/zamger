<?

// NASTAVNIK/PREDMET - pocetna stranica za administraciju predmeta - izbor studentskih modula



function nastavnik_predmet() {

	global $_api_http_code;
	
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$params = ["courseOfferingMembers" => true];
	if (int_param("prikazi") > 0) $params['resolve'] = ["Person"];
	$course = api_call("course/$predmet/$ag", $params);
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	if (!$course['AcademicYear']['isCurrent']) $predmet_naziv .= " (" . $course['AcademicYear']['name'] . ")";
	
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/predmet privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Ponude predmeta</h3></p>
	
	<?
	
	if (!$course['AcademicYear']['isCurrent']) {
		?>
		<hr>
		<p><font color="red">Odabrana akademska godina nije aktivna u Zamgeru.</font> Sve promjene koje vršite primjenjuju se retroaktivno na akademsku <?=$course['AcademicYear']['name'] ?>!</p>
		<hr>
		<?
	}
	
	?>
	
	<ul>
	<?
	
	
	// Pregled ponuda kursa
	foreach($course['courseOfferings'] as $co) {
		$cd = $co['CourseDescription'];
		$cdOutput = "";
		if ($cd['id']) {
			$cdOutput = $cd['name'] . " (" . $cd['code'] . ", " . $cd['ects'] . " ECTS) - ";
		}
		$izborni = "";
		if ($co['mandatory'] == false)
			$izborni = ", izborni";
		?>
		<li><?=$cdOutput?><?=$co['Programme']['name']?>, <?=$co['semester']?>. semestar<?=$izborni?> - <a href="?sta=nastavnik/predmet&predmet=<?=$predmet?>&ag=<?=$ag?>&prikazi=<?=$co['id']?>"><?=count($co['members'])?> studenata (pogledaj spisak)</a></li>
		<?
		if (int_param("prikazi") == $co['id']) {
			$students = $co['members'];
			usort($students, function ($s1, $s2) {
				if ($s1['surname'] == $s2['surname']) return bssort($s1['name'], $s2['name']);
				return bssort($s1['surname'], $s2['surname']);
			});
			?>
			<ul>
				<?
				foreach($students as $student) {
					?>
					<li><?=$student['surname']?> <?=$student['name']?> (<?=$student['studentIdNr']?>)</li>
					<?
				}
				?>
			</ul>
			<?
		}
	}

	?>
	</ul>
	<?
}

?>
