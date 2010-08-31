<?php

    require("lib/config.php");
	require("novosti.php");
	require("obavjestenja.php");
	
    function createRssForUser($userid)
    {

        $rssContent='';
        $broj_poruka = 10;


        $rssContent=$rssContent.
                    "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                    <rss version=\"2\">
                    <channel>
                        <title>Zamger feed</title>
                        <link>http://zamger.etf.unsa.ba/rss.php?id=delohQv8Hk</link>
                        <description> obavjestenja od zamgera</description>
                    ";

         $vrijeme_poruke = array();
         $code_poruke = array();

      // Objavljeni rezultati ispita

    $q15 = myquery("select i.id, i.predmet, k.gui_naziv, UNIX_TIMESTAMP(i.vrijemeobjave), p.naziv, UNIX_TIMESTAMP(i.datum), pk.id, p.id, pk.akademska_godina from ispit as i, komponenta as k, student_predmet as sp, ponudakursa as pk, predmet as p where sp.student=$userid and sp.predmet=pk.id and i.predmet=pk.predmet and i.akademska_godina=pk.akademska_godina and i.komponenta=k.id and pk.predmet=p.id order by i.vrijemeobjave desc limit $broj_poruka");
    while ($r15 = mysql_fetch_row($q15)) {
    	if ($r15[3] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
    	$code_poruke["i".$r15[0]] = "<item>
    		<title>Objavljeni rezultati ispita $r15[2] (".date("d. m. Y",$r15[5]).") - predmet $r15[4]</title>
    		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r15[7]&amp;ag=$r15[8]</link>
    	</item>";
	    $vrijeme_poruke["i".$r15[0]] = $r15[3];
    }
         // konacna ocjena

  $q17 = myquery("select pk.id, ko.ocjena, UNIX_TIMESTAMP(ko.datum), p.naziv, p.id, pk.akademska_godina from konacna_ocjena as ko, student_predmet as sp, ponudakursa as pk, predmet as p where ko.student=$userid and sp.student=$userid and sp.predmet=pk.id and ko.predmet=pk.predmet and ko.akademska_godina=pk.akademska_godina and pk.predmet=p.id order by ko.datum desc limit $broj_poruka");
  while ($r17 = mysql_fetch_row($q17)) {
  	if ($r17[2] < time()-60*60*24*30) continue; // preskacemo starije od mjesec dana
  	$code_poruke["k".$r17[0]] = "<item>
  		<title>Čestitamo! Dobili ste $r17[1] -- predmet $r17[3]</title>
  		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r17[4]&amp;ag=$r17[5]</link>
  		<description></description>
  	</item>\n";
  	$vrijeme_poruke["k".$r17[0]] = $r17[2];
  }
  // pregledane zadace
// (ok, ovo moze biti JAAAKO sporo ali dacemo sve od sebe da ne bude ;) )

$q18 = myquery("select zk.id, zk.redni_broj, UNIX_TIMESTAMP(zk.vrijeme), p.naziv, z.naziv, pk.id, z.id, p.id, pk.akademska_godina from zadatak as zk, zadaca as z, ponudakursa as pk, predmet as p where zk.student=$userid and zk.status!=1 and zk.status!=4 and zk.zadaca=z.id and z.predmet=p.id and pk.predmet=p.id and pk.akademska_godina=z.akademska_godina order by zk.id desc limit 10");
$zadaca_bila = array();
while ($r18 = mysql_fetch_row($q18)) {
	if (in_array($r18[6],$zadaca_bila)) continue; // ne prijavljujemo vise puta istu zadacu
	if ($r18[2] < time()-60*60*24*30) break; // IDovi bi trebali biti hronoloskim redom, tako da ovdje mozemo prekinuti petlju
	$code_poruke["zp".$r18[0]] = "<item>
		<title>Pregledana zadaća $r18[4], predmet $r18[3]</title>
		<link>$conf_site_url/index.php?sta=student/predmet&amp;predmet=$r18[7]&amp;ag=$r18[8]</link>
		<description><![CDATA[Posljednja izmjena: ".date("d. m. Y. h:i:s",$r18[2])."]]></description>
	</item>\n";
	array_push($zadaca_bila,$r18[6]);
	$vrijeme_poruke["zp".$r18[0]] = $r18[2];
}



// PORUKE (izvadak iz inboxa)


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



$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, tip, posiljalac from poruka order by vrijeme desc");
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
	$vrijeme_poruke[$id]=$r100[1];

	// Fino vrijeme
	$vr = $vrijeme_poruke[$id];
	$vrijeme="";
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "danas ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "juče ";
	else $vrijeme .= date("d.m. ",$vr);
	$vrijeme .= date("H:i",$vr);

	$naslov = $r100[4];
	// Ukidam nove redove u potpunosti
	$naslov = str_replace("\n", " ", $naslov);
	// RSS ne podržava &quot; entitet!?
	$naslov = str_replace("&quot;", '"', $naslov);
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";

	// Posiljalac
	if ($r100[6]==0) {
		$posiljalac="Administrator";
	} else {
		$q120 = myquery("select ime,prezime from osoba where id=$r100[6]");
		if (mysql_num_rows($q120)>0) {
			$posiljalac=mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
		} else {
			$posiljalac="Nepoznat";
		}
	}

	if ($r100[5]==1)
		$title="Obavijest";
	else
		$title="Poruka";

	$code_poruke[$id]="<item>
		<title>$title: $naslov ($vrijeme)</title>
		<link>$conf_site_url/index.php?sta=common%2Finbox&amp;poruka=$id</link>
		<description>Poslao: $posiljalac</description>
	</item>\n";
}
      //Promjene na Coursewaru
	$q0 = myquery("Select predmet from student_predmet where student=$userid",$con);
	
	//upit koji vraca posljednje vrijeme logiranja studenta
	$q1 =myquery("Select UNIX_TIMESTAMP(max(vrijeme)) from log where userid=$userid 
		and dogadjaj ='login'"); 
	//vrijeme logiranja umanjeno za jedan dan
	$vrijeme = mysql_result($q1,0)-(2*24*60*60);
	
	while($r0 = mysql_fetch_array($q0)){
		$q2 = myquery("Select sifra from predmet where id=".$r0['0']);
		$sifra = mysql_result($q2,0);
		
		$q3 = myquery("Select vrijeme_promjene from rss_cache where sifra_kursa='".$sifra."' 
		order by vrijeme_promjene desc limit 1");
		
		if(mysql_num_rows($q3)<1){
			if(novosti($sifra,$vrijeme)==1){
				$q4 = myquery("Select naslov, link, sadrzaj, vrijeme_promjene from rss_cache where
				sifra_kursa='".$sifra."' order by vrijeme_promjene desc limit 5");
				
					while($r4 = mysql_fetch_array($q4)){
							$vrijeme_poruke["Cw".$r4[0]] = $r4['3'];
							$code_poruke["Cw".$r4[0]]="
								<item>
								<title>$r4[0]</title>
								<link>$r4[1]</link>
								<description>$r4[2]</description>
								<pubDate>".date("d.m.Y H:i", $r4['3'])."</pubDate>
								</item>
							";
					}
			}
		}
		else{
			$vrijeme_promjene = mysql_result($q3,0);
			
			if(obavijesti($sifra,$vrijeme_promjene)==1){
				$q5 = myquery("Select naslov, link, sadrzaj, vrijeme_promjene from rss_cache where
				sifra_kursa='".$sifra."' and vrijeme_promjene>".$vrijeme_promjene." order by 
				vrijeme_promjene desc limit 5");
				
					while($r5 = mysql_fetch_array($q5)){
							$vrijeme_poruke["Cw".$r5[0]] = $r5['3'];
							$code_poruke["Cw".$r5[0]]="
								<item>
								<title>$r5[0]</title>
								<link>$r5[1]</link>
								<description>$r5[2]</description>
								<pubDate>".date("d.m.Y H:i", $r5['3'])."</pubDate>
								</item>
							";
					}
			}
			else{
				$q6 = myquery("Select naslov, link, sadrzaj, vrijeme_promjene from rss_cache where
				sifra_kursa='".$sifra."' and vrijeme_promjene > ".$vrijeme." order by 
				vrijeme_promjene desc limit 5");
					//ukoliko nema nikakvih novijih novosti na stranicama c2.etf.unsa.ba
					//onda se ispisuju obavjestenja koja su stara najvise 2 dana od vremena
					//studentskog trenutnog logina
					while($r6 = mysql_fetch_array($q6)){
							$vrijeme_poruke["Cw".$r6[0]] = $r6['3'];
							$code_poruke["Cw".$r6[0]]="
								<item>
								<title>$r6[0]</title>
								<link>$r6[1]</link>
								<description>$r6[2]</description>
								<pubDate>".date("d.m.Y H:i", $r6['3'])."</pubDate>
								</item>
							";
					}
			}
		}
	}

      // Sortiramo po vremenu
      arsort($vrijeme_poruke);
      $count=count($vrijeme_poruke);
      ;
      foreach ($vrijeme_poruke as $id=>$vrijeme) {
      	if ($count==0) {
      		// Polje pubDate u zaglavlju sadrži vrijeme zadnje izmjene tj. najnovije poruke

      		//print "        <pubDate>".date(DATE_RSS, $vrijeme)."</pubDate>\n";
      		// U verziji PHP 5.1.6 (i vjerovatno starijim) DATE_RSS je nekorektno
      		// izjednačeno sa "D, j M Y H:i:s T"
      		print "        <pubDate>".date("D, j M Y H:i:s O", $vrijeme)."</pubDate>\n";
      	}
      	$rssContent=$rssContent.$code_poruke[$id];
      	$count++;
      	if ($count==$broj_poruka) break; // prikazujemo 5 poruka
      }
          	$rssContent=$rssContent."
            </channel>
            </rss>"    ;
      return $rssContent;
    }
