<?
	$imena=array();
	$topscore=array();

require("libvedran.php");
dbconnect();
mysql_select_db("vedran_studenti_tp");


	$r3 = mysql_query("select id,ime,prezime,brindexa from studenti");
	$imeprezime = array();
	$brind = array();
	while ($tr = mysql_fetch_row($r3)) {
		$stud_id = $tr[0];
		$stud_ime = $tr[1];
		$stud_prezime = $tr[2];
		$stud_brind = $tr[3];
		$imeprezime[$stud_id] = "$stud_prezime $stud_ime";
		$brind[$stud_id] = $stud_brind;
	}
	uasort($imeprezime,"vsortcmp");
	foreach ($imeprezime as $stud_id => $stud_imepr) {
		$prisustvo_ispis=$ocjene_ispis=$parc_ispis="";
		$bodova=0;
		$mogucih=0;
		print "\"$stud_imepr\",";

		// PARCIJALE
		for ($pid=1; $pid<=2; $pid++) {
			$r6 = mysql_query("select ocjena from parcijale where student=$stud_id and id=$pid");
			if (mysql_num_rows($r6)>0) {
				if (($ocjena = mysql_result($r6,0,0)) == -1) {
					print "\"/\",";
				} else {
					print "\"$ocjena\",";
				}
			} else {
				print "\"/\",";
			}
		}

		// PRISUSTVO
		$r4 = mysql_query("select count(*) from prisustvo where student=$stud_id and prisutan==0");
		if (mysql_result($r4,0,0)>3)
			print "0,";
		else
			print "10,"; 

		// ZADACE
		$bodova=0;
		for ($vid=1; $vid<=5; $vid++) {
			$q5 = myquery("select status,bodova,zadatak from zadace where zadaca=$vid and student=$stud_id order by zadatak,id desc");
			$ok = 1;
			$ocjena = 0;
			$zadatak = 0;
			if (mysql_num_rows($q5) == 0)
				$ok = 0;
			else while ($r5 = mysql_fetch_row($q5)) {
				if ($r5[2] == $zadatak) continue;
				$zadatak = $r5[2];
				$status = $r5[0]; 
				if ($status == 0 || $status == 1 || $status == 4) {
					$ok = 0;
					break;
				}
				$ocjena += $r5[1];
			}
			if ($ok != 0) {
				$bodova = $bodova + $ocjena;
			}
		}
		
		$bodova += 2; // blah
		print "\"$bodova\"\n";

	}

mysql_close();
?>
