<?

// SARADNIK/INTRO - spisak predmeta i grupa



function saradnik_intro() {

global $userid,$user_siteadmin,$registry,$posljednji_pristup;

require_once("lib/utility.php"); // spol, vokativ


// Dobrodošlica

$rez = db_query_assoc("select ime, spol from osoba where id=$userid");
if ($rez['spol'] == 'Z' || ($rez['spol'] == '' && spol($rez['ime'])=="Z"))
	print "<h1>Dobro došla, ".vokativ($rez['ime'],"Z")."</h1>";
else
	print "<h1>Dobro došao, ".vokativ($rez['ime'],"M")."</h1>";



// Sakrij raspored ako ga nema u registry-ju
$nasao = false;
foreach ($registry as $r) {
	if ($r[0]=="common/raspored1") { $nasao = true; break; }
}
if ($nasao) {
	require "common/raspored1.php";
	common_raspored1("nastavnik");
}


// Prikaz obavještenja za saradnike
$prikaz_sekundi = 600; // Koliko dugo se prikazuje obavještenje
$vrijeme = $posljednji_pristup - $prikaz_sekundi; // globalna
$broj_poruka = db_get("select count(id) from poruka where tip=1 and (opseg=0 or opseg=2) and UNIX_TIMESTAMP(vrijeme)>$vrijeme order by vrijeme desc limit 1");
if ($broj_poruka > 0) {
	?><p><a href="?sta=common/inbox&poruka=<?=db_result($q30,0,0)?>"><div style="color:red; text-decoration: underline">Imate novo sistemsko obavještenje. Kliknite ovdje.</div></a></p><?
}



// Spisak grupa po predmetima, predmeti po akademskoj godini
?><table border="0" cellspacing="5"><tr>
<?

if (int_param('sve') === 1)
	$upit = "select id,naziv from akademska_godina order by naziv desc";
else
	$upit = "select id,naziv from akademska_godina where aktuelna=1 order by naziv desc limit 1";
$q = db_query($upit);
while (db_fetch2($q, $ag, $ag_naziv)) {
	// Prikaži sve predmete siteadminu
	$uslov=""; $nppolje="nastavnik";
	$uslov="np.predmet=p.id and np.akademska_godina=$ag and np.nastavnik=$userid and";
	$nppolje="np.nivo_pristupa";

	// Upit za spisak predmeta
	$q10 = db_query("SELECT p.id, $nppolje, p.naziv, i.kratki_naziv, MIN(pk.semestar) sem, MIN(pk.studij) stud
	FROM predmet as p, nastavnik_predmet as np, institucija as i, ponudakursa as pk 
	WHERE $uslov p.institucija=i.id and pk.predmet=p.id and pk.akademska_godina=$ag 
	GROUP BY p.id
	ORDER BY sem, stud, p.naziv");

	// Format - šest predmeta u jednom redu
	$nr = db_num_rows($q10);
	if ($nr==0) continue; // sljedeća akademska godina

	if ($nr>6) $nr=6;
	print '<td colspan="'.($nr*2).'" align="center" bgcolor="#88BB99">Predmeti ('.$ag_naziv.')</td></tr><tr>';

	$br=0;
	while (db_fetch4($q10, $predmet, $privilegija, $naziv_predmeta, $studij)) {
		// Spacer
		if ($br>0) print '<td bgcolor="#666666" width="1"></td>'."\n";

		print '<td valign="top">'."\n";
	
		// Siteadmin moze vidjeti i tudje predmete, pa ih prikazujemo drugom bojom
		$moj=1;
		if ($user_siteadmin)
			$moj = db_get("select count(*) from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
			
		if ($moj==0)
			print "<b><font color=\"#664444\">$naziv_predmeta ($studij)</font></b>\n";
		else
			print "<b>$naziv_predmeta ($studij)</b>\n";
	
		// Edit link
		if ($user_siteadmin || $privilegija=="nastavnik" || $privilegija=="super_asistent") {
			print ' [<b><a href="?sta=nastavnik/predmet&predmet='.$predmet.'&ag='.$ag.'"><font color="red">EDIT</font></a></b>]'."\n";
		}
	
		// Provjeri limit na prikazivanje labgrupa
		$limit = db_query_varray("select o.labgrupa from ogranicenje as o, labgrupa as l where o.nastavnik=$userid and o.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag");
	
		// Lab grupe
		print "<ul>\n";
		$labgrupe = db_query_vassoc("select id,naziv from labgrupa where predmet=$predmet and akademska_godina=$ag");
		natsort($labgrupe);
		foreach($labgrupe as $id_grupe => $ime_grupe) {
			if (!preg_match("/\w/",$ime_grupe)) $ime_grupe="[Nema imena]";
			if (count($limit)==0 || in_array($id_grupe, $limit))
				print "<li><a href=\"?sta=saradnik/grupa&id=$id_grupe\">$ime_grupe</a></li>\n";
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

if (int_param('sve') !== 1) print '<a href="'.genuri().'&sve=1">Prikaži ranije akademske godine</a>';



}

?>
