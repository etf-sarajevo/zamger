<?

// WS/STUDENT_PREDMET - podaci o predmetu iz perspektive studenta



function ws_student_predmet()
{	
	global $userid, $user_nastavnik, $user_student, $user_siteadmin, $user_studentska;
	
	$rezultat = array( 'success' => 'true', 'data' => array() );
	
	if (isset($_REQUEST['ag']))
		$ag = intval($_REQUEST['ag']);
	else {
		$q10 = myquery("select id from akademska_godina where aktuelna=1 order by id desc limit 1");
		$ag = mysql_result($q10,0,0);
	}
	
	// Ako je definisan predmet, vraćamo detaljnije informacije o tom predmetu
	if (isset($_REQUEST['predmet'])) {
		$predmet = intval($_REQUEST['predmet']);
		if (isset($_REQUEST['student']))
			$student = intval($_REQUEST['student']);
		else
			$student = $userid;
		
		// Provjeravamo prava pristupa
		$ok = false;
		if ($user_siteadmin || $user_studentska) $ok = true;
		if ($user_nastavnik && !$ok) {
			$q20 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (mysql_num_rows($q20)>0) {
				$ok = true;
				// Postoji li ograničenje na tom predmetu
				if (mysql_result($q20,0,0) == "asistent") {
					$q30 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
					if (mysql_num_rows($q30)>0) {
						$ok = false; // Mrsko mi je dalje provjeravati
					}
				}
			}
		}
		if ($user_student && $student == $userid) $ok = true;
		
		if (!$ok) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR002', 'message' => 'Permission denied' ) );
			return;
		}
		
		// Da li student slusa predmet?
		$q2 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$student and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
		if (mysql_num_rows($q2)<1) {
			print json_encode( array( 'success' => 'false', 'code' => 'ERR003', 'message' => 'Student ne sluša predmet' ) );
			return;
		}
		$ponudakursa = mysql_result($q2,0,0);
		
		// Ako je definisana akcija "prisustvo" dajemo detaljne informacije o prisustvo
		if ($_REQUEST["akcija"] == "prisustvo") {
			$q20 = myquery("SELECT k.id, k.maxbodova, k.prolaz, k.opcija 
			FROM komponenta as k, tippredmeta_komponenta as tpk, akademska_godina_predmet as agp
			WHERE agp.predmet=$predmet and agp.akademska_godina=$ag and agp.tippredmeta=tpk.tippredmeta and tpk.komponenta=k.id and k.tipkomponente=3"); // 3 = prisustvo
			
			while ($r20 = mysql_fetch_row($q20)) {
				$id_komponente = $r20[0];
				$max_bodova = $r20[1];
				$min_bodova = $r20[2];
				$max_izostanaka = $r20[3];
				
				$odsustva = $casova = 0;
				$q30 = myquery("select l.id,l.naziv from labgrupa as l, student_labgrupa as sl where l.predmet=$predmet and l.akademska_godina=$ag and l.id=sl.labgrupa and sl.student=$student");
				
				$grupe = array();
				
				while ($r30 = mysql_fetch_row($q30)) {
					$grupa = array();
					$grupa['id'] = $r30[0];
					$grupa['naziv'] = $r30[1];
					if (!preg_match("/\w/", $r30[1])) $grupa['naziv'] = "[Bez naziva]";
					
					$q40 = myquery("select id, UNIX_TIMESTAMP(datum), vrijeme from cas where labgrupa=$r30[0] and komponenta=$r20[0] order by datum, vrijeme");
					if (mysql_num_rows($q40)<1) continue; // Preskace u kojima nema registrovanih časova
					
					$grupa_odsustva = 0;
					$casovi = array();
					while ($r40 = mysql_fetch_row($q40)) {
						$vrijeme_casa = $r40[1];
						if (preg_match("/^(\d\d)\:(\d\d)\:(\d\d)$/", $r40[2], $matches))
							$vrijeme_casa += $matches[1]*3600 + $matches[2]*60 + $matches[3];
							
						$cas = array();
						$cas['id'] = $r40[0];
						$cas['vrijeme'] = $vrijeme_casa;
						
						$q15 = myquery("select prisutan from prisustvo where student=$student and cas=$r40[0]");
						if (mysql_num_rows($q15)<1) 
							$cas['status'] = "nepoznato";
						else if (mysql_result($q15,0,0)==1) 
							$cas['status'] = "prisutan";
						else {
							$cas['status'] = "odsutan";
							$grupa_odsustva++;
						}
						$casovi[] = $cas;
					}
					
					$grupa['casovi'] = $casovi;
					$grupe[] = $grupa;
					
					$odsustva += $grupa_odsustva;
					$casova += count($casovi);
				}
				
				if ($max_izostanaka == -1) {
					if ($casova == 0) 
						$bodovi = 10;
					else
						$bodovi = $min_bodova + round(($max_bodova - $min_bodova) * (($casova - $odsustva) / $casova), 2 ); 
				} 
				else if ($max_izostanaka == -2) { // Paraproporcionalni sistem TP
					if ($odsustva <= 2)
						$bodovi = $max_bodova;
					else if ($odsustva <= 2 + ($max_bodova - $min_bodova)/2)
						$bodovi = $max_bodova - ($odsustva-2)*2;
					else
						$bodovi = $min_bodova;
				} else if ($odsustva<=$max_izostanaka)
					$bodovi = $max_bodova;
				else
					$bodovi = $min_bodova;
					
				$komponenta = array();
				$komponenta['id'] = $r20[0];
				$komponenta['grupe'] = $grupe;
				$rezultat['data'][] = $komponenta;
			}
			echo json_encode($rezultat);
			return;
		}
	
		// Dajemo opšte informacije o uspjehu studenta na predmetu
		$q1 = myquery("select naziv from predmet where id=$predmet");
		$rezultat['data']['naziv_predmeta'] = mysql_result($q1,0,0);
		
		// Traženje odgovornog nastavnika 
		$q3 = myquery("select o.id, ast.naziv from angazman as a, angazman_status as ast, osoba as o where a.predmet=$predmet and a.akademska_godina=$ag and a.angazman_status=ast.id and a.osoba=o.id order by ast.id");
		$r4 = mysql_fetch_row($q3);
		$rezultat['data']['odgovorni_nastavnik'] = tituliraj($r4[0]);
		
		// Traženje bodova po komponentama
		$q5 = myquery("select kb.bodovi, k.maxbodova, k.gui_naziv, k.id from komponentebodovi as kb, komponenta as k where kb.student=$student and kb.predmet=$ponudakursa and kb.komponenta=k.id");
		$komponente = array();
		while($r5 = mysql_fetch_row($q5)) {
			$komponente[$r5[3]] = array("bodovi" => $r5[0], "max_broj_bodova" => $r5[1], "naziv" => $r5[2]);
		}
		
		$rezultat['data']['komponente'] = $komponente;
		
		$q6 = myquery("select ocjena from konacna_ocjena where student=$student and predmet=$predmet and ocjena>=6");
		
		// Provjeramo da li je student vec upisao ocjenu
		while ($r6 = mysql_fetch_row($q6)) 
			$rezultat['data']['konacna_ocjena'] = $r6[0];
		
		echo json_encode($rezultat);
		return;
	}
	
	// Nije naveden predmet, uzimamo spisak predmeta koje student trenutno sluša
	if (($user_siteadmin || $user_studentska) && isset($_REQUEST['student']))
		$student = intval($_REQUEST['student']);
	else
		$student = $userid;
	$q100 = myquery("select p.id, p.naziv, p.kratki_naziv from student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$student and sp.predmet=pk.id and pk.akademska_godina=$ag and pk.predmet=p.id");
	if (mysql_num_rows($q100)<1) {
		print json_encode( array( 'success' => 'false', 'code' => 'ERR004', 'message' => 'Student ne sluša niti jedan predmet' ) );
		return;
	}
	
	//Kako bi spremili sve predmete u jedan niz, pa taj niz proslijedili u rezultat, pravimo pomoćni niz
	$temp = array();
	while($r100 = mysql_fetch_row($q100))
		array_push($temp, array("id" => $r100[0], "naziv" => $r100[1], "kratki_naziv" => $r100[2]));
	$rezultat['data']['predmeti'] = $temp;
	
	echo json_encode($rezultat);
}

?>

