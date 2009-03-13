<?

// SARADNIK/INTRO - spisak predmeta i grupa

// v3.9.1.0 (2008/02/11) + Preimenovan bivsi admin_intro, dodan link na [Svi studenti]
// v3.9.1.1 (2008/03/08) + Nova tabela auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/12) + Dodan prikaz obavjestenja nivoa 0 i 2 koje bi nastavnici trebali dobijati, ali ih nisu mogli vidjeti


function saradnik_intro() {

global $userid,$user_siteadmin;



// Dobrodošlica

$q1 = myquery("select ime from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
if (spol($ime)=="Z") 
	print "<h1>Dobro došla, ".genitiv($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".genitiv($ime,"M")."</h1>";


// Prikaz obavještenja za saradnike
$q20 = myquery("select UNIX_TIMESTAMP(vrijeme) from log where userid=$userid order by id desc limit 2");
if (mysql_num_rows($q20)>0)
	$vrijeme=intval(mysql_result($q20,1,0))-60; // Prikazi obavjestenja ne starija od minut
else 
	$vrijeme=0;
$q30 = myquery("select id from poruka where tip=1 and (opseg=0 or opseg=2) and UNIX_TIMESTAMP(vrijeme)>$vrijeme order by vrijeme desc limit 1");
if (mysql_num_rows($q30)>0) {
	?><p><a href="?sta=common/inbox&poruka=<?=mysql_result($q30,0,0)?>"><font color="red">Imate novo sistemsko obavještenje</font></a></p><?
}



// Spisak grupa po predmetima, predmeti po akademskoj godini
print '<table border="0" cellspacing="5"><tr>';

if ($_REQUEST['sve']) 
	$q1a = myquery("select id,naziv from akademska_godina order by naziv desc");
else
	$q1a = myquery("select id,naziv from akademska_godina where aktuelna=1 order by naziv desc limit 1");


while ($r1a = mysql_fetch_row($q1a)) {
	if ($user_siteadmin)
		$q2 = myquery("select id,1 from ponudakursa where akademska_godina=$r1a[0] order by semestar,id");
	else
		$q2 = myquery("select np.predmet,np.admin from nastavnik_predmet as np, ponudakursa as p where np.nastavnik=$userid and np.predmet=p.id and p.akademska_godina=$r1a[0] order by p.semestar,p.id");

	$nr = mysql_num_rows($q2);
	if ($nr==0) continue; // sljedeća akademska godina

	if ($nr>6) $nr=6;
	print '<td colspan="'.($nr*2).'" align="center" bgcolor="#88BB99">Predmeti ('.$r1a[1].')</td></tr><tr>';

	$br=0;
	while ($r2 = mysql_fetch_row($q2)) {
		# Spacer
		if ($br>0) print '<td bgcolor="#666666" width="1"></td>';
	
		print '<td valign="top">';
		$predmet = $r2[0];
		$admin_predmeta = $r2[1];
	
		# Ispis naziva predmeta
		$q3 = myquery("select p.naziv,1,s.kratkinaziv from predmet as p, ponudakursa as pk, studij as s where pk.id=$predmet and pk.predmet=p.id and pk.studij=s.id");
		if (mysql_num_rows($q3)<0) {
			print "Greška: nepoznat predmet!";
		} else {
			$naziv_predmeta = mysql_result($q3,0,0);
			$predmet_aktivan = mysql_result($q3,0,1);
			$studij = mysql_result($q3,0,2);
			
			// Da li je predmet moj?
			$moj=1;
			if ($user_siteadmin) {
				$q3a = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet");
				if (mysql_result($q3a,0,0)<1) $moj=0;
			}
			if($predmet_aktivan==0 && $moj==0) {
				print "<b><font color=\"#CC8888\">$naziv_predmeta ($studij)</font></b>";
			} else if ($predmet_aktivan==0) {
				print "<b><font color=\"#888888\">$naziv_predmeta ($studij)</font></b>";
			} else if ($moj==0) {
				print "<b><font color=\"#664444\">$naziv_predmeta ($studij)</font></b>";
			} else {
				print "<b>$naziv_predmeta ($studij)</b>";
			}
		}
	
		# Edit link
		if ($user_siteadmin || $admin_predmeta) {
			print ' [<b><a href="?sta=nastavnik/predmet&predmet='.$predmet.'"><font color="red">EDIT</font></a></b>]';
		}
	
		# Provjeri limit na prikazivanje labgrupa
		$limit = array();
		$q3a = myquery("select ogranicenje.labgrupa from ogranicenje, labgrupa where ogranicenje.nastavnik=$userid and ogranicenje.labgrupa=labgrupa.id and labgrupa.predmet=$predmet");
		if (mysql_num_rows($q3a)>0) {
			while ($r3a = mysql_fetch_row($q3a))
				array_push($limit, $r3a[0]);
		}
	
		# Lab grupe
		print "<ul>";
		$q4 = myquery("select id,naziv from labgrupa where predmet=$predmet");
		if (count($limit)==0)
			print "<li><a href=\"?sta=saradnik/grupa&id=0&predmet=$predmet\">[Svi studenti]</a></li>";

		$result = array();
		while ($r4 = mysql_fetch_row($q4)) {
			$result[$r4[0]]=$r4[1];
		}
		natsort($result);
		foreach($result as $gid=>$gname) {
			if (!preg_match("/\w/",$gname)) $gname="[Nema imena]";
			if (count($limit)==0 || in_array($gid,$limit))
				print "<li><a href=\"?sta=saradnik/grupa&id=$gid\">$gname</a></li>";
		}
		print "</ul>";
	
		# Kraj
		print "</td>";
	
		$br++;
		if ($br==6) {
			$br=0;
			print "</tr><tr>";
		}
	}
	print '</tr><tr>';
}



print '</tr></table>';

if (!$_REQUEST['sve']) print '<a href="'.genuri().'&sve=1">Prikaži ranije akademske godine</a>';



}

?>
