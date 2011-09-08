<?php

function student_notifikacija() {
	global $userid;
	$tip = $_GET['tip'];

	if (tip==1){
	
		$q245 = myquery("select UNIX_TIMESTAMP(vrijeme) from log where userid=$userid AND dogadjaj='procitana_notifikacija'");
		if (mysql_num_rows($q245)>0)
			$vrijeme=intval(mysql_result($q245,0,0));
		else 
			$vrijeme=0;
		
	//zadace
		$q10 = myquery("select z.id, z.naziv, UNIX_TIMESTAMP(z.rok), p.naziv, pk.id, UNIX_TIMESTAMP(z.vrijemeobjave), p.id, pk.akademska_godina from zadaca as z, student_predmet as sp, ponudakursa as pk, predmet as p where z.predmet=pk.predmet and z.akademska_godina=pk.akademska_godina and z.rok>curdate() and sp.predmet=pk.id and sp.student=$userid and pk.predmet=p.id and z.aktivna=1 and UNIX_TIMESTAMP(z.vrijemeobjave)>$vrijeme order by rok limit 5");
		while ($r10 = mysql_fetch_row($q10)) {
	// Da li je aktivan modul za zadaće?
			$q12 = myquery("select count(*) from studentski_modul as sm, studentski_modul_predmet as smp where sm.modul='student/zadaca' 	and sm.id=smp.studentski_modul and smp.predmet=$r10[6] and smp.akademska_godina=$r10[7]");
			if (mysql_result($q12,0,0)==0) continue;

			$code_poruke["z".$r10[0]] = "<b>$r10[3]:</b> Rok za slanje <a href=\"?sta=student/zadaca&zadaca=$r10[0]&predmet=$r10[6]&ag=$r10[7]\">zadaće ".$r10[1]."</a> je ".date("d. m. Y. u h:i",$r10[2]).".<br/><br/>\n";
			$vrijeme_poruke["z".$r10[0]] = $r10[5];
		}

		$broj1= mysql_num_rows($q10);


		$q15 = myquery("select i.id, pk.id, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), true, k.prolaz, p.id, pk.akademska_godina from ispit as i, komponenta as k, ponudakursa as pk, predmet as p, student_predmet as sp where i.komponenta=k.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and pk.predmet=p.id and sp.student=$userid and sp.predmet=pk.id 
and UNIX_TIMESTAMP(i.vrijemeobjave)>$vrijeme limit 5");
		while ($r15 = mysql_fetch_row($q15)) {
			if ($r15[3] < time()-60*60*24*30) continue; // preskačemo starije od mjesec dana

			$code_poruke["i".$r15[0]] = "<b>$r15[4]:</b> Objavljeni rezultati ispita: <a href=\"?sta=student/predmet&predmet=$r15[8]&ag=$r15[9]\">$r15[2] (".date("d. m. Y",$r15[5]).")</a>. Dobili ste $bodova bodova.$cestitka<br/><br/>\n";
			$vrijeme_poruke["i".$r15[0]] = $r15[3];

		}

		$broj2= mysql_num_rows($q15);


		$q17 = myquery("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and ko.predmet=p.id and ko.akademska_godina=pk.akademska_godina and sp.predmet=pk.id and pk.predmet=p.id and ko.ocjena>5
and UNIX_TIMESTAMP(ko.datum)>$vrijeme limit 5");
		while ($r17 = mysql_fetch_row($q17)) {
			if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
	
			$code_poruke["k".$r17[0]] = "<b>$r17[3]:</b> Čestitamo! <a href=\"?sta=student/predmet&predmet=$r17[4]&ag=$r17[5]\">Dobili ste $r17[1]</a><br/><br/>\n";
			$vrijeme_poruke["k".$r17[0]] = $r17[2];
	
		}

		$broj3= mysql_num_rows($q17);


		$q18 = myquery("select zk.id, zk.redni_broj, UNIX_TIMESTAMP(zk.vrijeme), p.naziv, z.naziv, pk.id, z.id, p.id, pk.akademska_godina from zadatak as zk, zadaca as z, ponudakursa as pk, predmet as p where zk.student=$userid and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and pk.predmet=p.id and pk.akademska_godina=z.akademska_godina
and UNIX_TIMESTAMP(zk.vrijeme)>$vrijeme order by zk.id desc limit 5");
		$zadaca_bila = array();
		while ($r18 = mysql_fetch_row($q18)) {
			if (in_array($r18[6],$zadaca_bila)) continue; // ne prijavljujemo vise puta istu zadacu
			if ($r18[2] < time()-60*60*24*30) break; // IDovi bi trebali biti hronoloskim redom, tako da ovdje mozemo prekinuti petlju
			$code_poruke["zp".$r18[0]] = "<b>$r18[3]:</b> <a href=\"?sta=student/predmet&amp;predmet=$r18[7]&amp;ag=$r18[8]\"> Pregledana zadaća $r18[4]</a><br/><br/>\n";
			array_push($zadaca_bila,$r18[6]);
			$vrijeme_poruke["zp".$r18[0]] = $r18[2];
		}

		$broj4= mysql_num_rows($q18);

		echo "<table border='0'>";
		if ($broj1+$broj2+$broj3+$broj4>0){
			arsort($vrijeme_poruke);
			$count=0;
			foreach ($vrijeme_poruke as $id=>$vrijeme) {
				echo "<tr>";
				echo "<td>".$code_poruke[$id]."</td>";
				echo "</tr>";
				$count++;
				if ($count==5) break; // prikazujemo 5 poruka
			}
		}
		echo "</table>";

	}

	else{
	
		$q246 = myquery("select UNIX_TIMESTAMP(vrijeme) from log where userid=$userid AND dogadjaj='procitana_poruka'");
		if (mysql_num_rows($q246)>0)
			$vrijeme=intval(mysql_result($q246,0,0));
		else 
			$vrijeme=0;
		
// Zadnja akademska godina
		$q20 = myquery("select id,naziv from akademska_godina where aktuelna=1 order by id desc limit 1");
		$ag = mysql_result($q20,0,0);
		$ag_naziv = mysql_result($q20,0,1);

// Studij koji student trenutno sluša
		$studij=0;
		$q30 = myquery("select studij,semestar from student_studij where student=$userid and akademska_godina=$ag order by semestar desc limit 1");
		if (mysql_num_rows($q30)>0) {
			$studij = mysql_result($q30,0,0);
		}


		$br = 0;
		$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka where UNIX_TIMESTAMP(vrijeme)>$vrijeme order by vrijeme desc limit 5");
		while ($r100 = mysql_fetch_row($q100)) {
			$id = $r100[0];
			$opseg = $r100[2];
			$primalac = $r100[3];
			if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
		continue;
			if ($opseg==5) {
		// Poruke od starih akademskih godina nisu relevantne
				if ($r100[1]<mktime(0,0,0,9,1,intval($ag_naziv))) continue;

		// odredjujemo da li student slusa predmet
				$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac and pk.akademska_godina=$ag");
				if (mysql_result($q110,0,0)<1) continue;
			}
			if ($opseg==6) {
		// da li je student u labgrupi?
				$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
				if (mysql_result($q115,0,0)<1) continue;
			}

	// Poruka je ok
			if (++$br > $broj_poruka) break; // Nema smisla da gledamo dalje
			$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
			$vr = $vrijeme_poruke[$id];
			$vrijeme="";
	/* if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	else*/  $vrijeme .= date("d.m. ",$vr);
			$vrijeme .= date("H:i",$vr);

			$naslov = $r100[4];
	// Ukidam nove redove u potpunosti
			$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
			$naslov = str_replace("&quot;", '"', $naslov);
			if (strlen($naslov)>30) $naslov = z_substr($naslov,0,28)."...";
			if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
			if ($r100[6]==0) {
				$posiljalac="Administrator";
			}
			else {
				$q120 = myquery("select ime,prezime from osoba where id=$r100[6]");
				if (mysql_num_rows($q120)>0) {
					$posiljalac=mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
				}
				 else {
					$posiljalac="Nepoznat";
				}
			}

			if ($r100[5]==1)
				$title="Obavijest";
			else
				$title="Poruka";

			$code_poruke[$id]="<a href=\"?sta=common%2Finbox&amp;poruka=$id\"> Nova $title: $naslov ($vrijeme). Poslao: $posiljalac </a><br/><br/>\n";
 
		
		}

		$broj5= mysql_num_rows($q100);

		echo "<table border='0'>";
		if ($broj5>0){
			arsort($vrijeme_poruke);
			$count=0;
			foreach ($vrijeme_poruke as $id=>$vrijeme) {
				echo "<tr>";
				echo "<td>".$code_poruke[$id]."</td>";
				echo "</tr>";
				$count++;
				if ($count==5) break; // prikazujemo 5 poruka
			}
		}
		echo "</table>";

	}

}
?>