<?

// SARADNIK/INTRO - spisak predmeta i grupa

// v3.9.1.0 (2008/02/11) + Preimenovan bivsi admin_intro, dodan link na [Svi studenti]
// v3.9.1.1 (2008/03/08) + Nova tabela auth
// v4.0.0.0 (2009/02/19) + Release
// v4.0.0.1 (2009/03/12) + Dodan prikaz obavjestenja nivoa 0 i 2 koje bi nastavnici trebali dobijati, ali ih nisu mogli vidjeti
// v4.0.9.1 (2009/03/25) + nastavnik_predmet preusmjeren sa tabele ponudakursa na tabelu predmet
// v4.0.9.2 (2009/04/23) + labgrupa preusmjerena sa tabele ponudakursa na tabelu predmet, spojene ponudekursa u prikazu za nastavnike, EDIT link preusmjeren na predmet
// v4.0.9.3 (2009/05/05) + Ukidam "virtualnu grupu" 0
// v4.0.9.4 (2009/05/17) + Prikazi site adminu predmete cak i u slucaju kada nijedan nastavnik nije angazovan na predmetu


function saradnik_intro() {

global $userid,$user_siteadmin,$registry,$posljednji_pristup;



// Dobrodošlica

$q1 = myquery("select ime, spol from osoba where id=$userid");
$ime = mysql_result($q1,0,0);
$spol = mysql_result($q1,0,1);
if ($spol == 'Z' || ($spol == '' && spol($ime)=="Z"))
	print "<h1>Dobro došla, ".vokativ($ime,"Z")."</h1>";
else
	print "<h1>Dobro došao, ".vokativ($ime,"M")."</h1>";



// Sakrij raspored ako ga nema u registry-ju
$nasao=0;
foreach ($registry as $r) {
	if ($r[0]=="common/raspored1") { $nasao=1; break; }
}
if ($nasao==1) {
	require "common/raspored1.php";
	common_raspored1("nastavnik");
}


// Prikaz obavještenja za saradnike
$prikaz_sekundi = 600; // Koliko dugo se prikazuje obavještenje
$vrijeme = $posljednji_pristup - $prikaz_sekundi; // globalna
$q30 = myquery("select id from poruka where tip=1 and (opseg=0 or opseg=2) and UNIX_TIMESTAMP(vrijeme)>$vrijeme order by vrijeme desc limit 1");
if (mysql_num_rows($q30)>0) {
	?><p><a href="?sta=common/inbox&poruka=<?=mysql_result($q30,0,0)?>"><div style="color:red; text-decoration: underline">Imate novo sistemsko obavještenje. Kliknite ovdje.</div></a></p><?
}



// Spisak grupa po predmetima, predmeti po akademskoj godini
?><table border="0" cellspacing="5"><tr>
<?

if ($_REQUEST['sve']) 
	$q1a = myquery("select id,naziv from akademska_godina order by naziv desc");
else
	$q1a = myquery("select id,naziv from akademska_godina where aktuelna=1 order by naziv desc limit 1");


while ($r1a = mysql_fetch_row($q1a)) {
	$ag = $r1a[0];
	$ag_naziv = $r1a[1];

	// Prikaži sve predmete siteadminu
	$uslov=""; $nppolje="nastavnik";
	$uslov="np.predmet=p.id and np.akademska_godina=$ag and np.nastavnik=$userid and";
	$nppolje="np.nivo_pristupa";

	// Upit za spisak predmeta
	$q10 = myquery("select distinct p.id, $nppolje, p.naziv, i.kratki_naziv from predmet as p, nastavnik_predmet as np, institucija as i, ponudakursa as pk where $uslov p.institucija=i.id and pk.predmet=p.id and pk.akademska_godina=$ag order by pk.semestar, pk.studij, p.naziv");

	// Format - šest predmeta u jednom redu
	$nr = mysql_num_rows($q10);
	if ($nr==0) continue; // sljedeća akademska godina

	if ($nr>6) $nr=6;
	print '<td colspan="'.($nr*2).'" align="center" bgcolor="#88BB99">Predmeti ('.$ag_naziv.')</td></tr><tr>';

	$br=0;
	while ($r10 = mysql_fetch_row($q10)) {
		$predmet = $r10[0];
		$privilegija = $r10[1];
		$naziv_predmeta = $r10[2];
		$studij = $r10[3];

		// Spacer
		if ($br>0) print '<td bgcolor="#666666" width="1"></td>'."\n";

		print '<td valign="top">'."\n";
	
		// Siteadmin moze vidjeti i tudje predmete, pa ih prikazujemo drugom bojom
		$moj=1;
		if ($user_siteadmin) {
			$q20 = myquery("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			if (mysql_result($q20,0,0)<1) $moj=0;
		}
		if ($moj==0)
			print "<b><font color=\"#664444\">$naziv_predmeta ($studij)</font></b>\n";
		else
			print "<b>$naziv_predmeta ($studij)</b>\n";
	
		// Edit link
		if ($user_siteadmin || $privilegija=="nastavnik" || $privilegija=="super_asistent") {
			print ' [<b><a href="?sta=nastavnik/predmet&predmet='.$predmet.'&ag='.$ag.'"><font color="red">EDIT</font></a></b>]'."\n";
		}
	
		// Provjeri limit na prikazivanje labgrupa
		$limit = array();
		$q30 = myquery("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
		if (mysql_num_rows($q30)>0) {
			while ($r30 = mysql_fetch_row($q30))
				array_push($limit, $r30[0]);
		}
	
		// Lab grupe
		print "<ul>\n";
		$q4 = myquery("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");

		$result = array();
		while ($r4 = mysql_fetch_row($q4)) {
			$result[$r4[0]]=$r4[1];
		}
		natsort($result);
		foreach($result as $gid=>$gname) {
			if (!preg_match("/\w/",$gname)) $gname="[Nema imena]";
			if (count($limit)==0 || in_array($gid,$limit))
				print "<li><a href=\"?sta=saradnik/grupa&id=$gid\">$gname</a></li>\n";
		}
		print "</ul>\n";
	
		// Kraj
		print "</td>\n";
	
		$br++;
		if ($br==6) {
			$br=0;
			print "</tr><tr>\n";
		}
	}
	print '</tr><tr>'."\n";
}



print '</tr></table>';

if (!$_REQUEST['sve']) print '<a href="'.genuri().'&sve=1">Prikaži ranije akademske godine</a>';



}

?>
