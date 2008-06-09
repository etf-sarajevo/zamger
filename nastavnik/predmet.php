<?

// NASTAVNIK/PREDMET - pocetna stranica za administraciju predmeta - izbor studentskih modula

// v3.9.1.0 (2008/02/18) + Preimenovan bivsi admin_predmet
// v3.9.1.1 (2008/04/09) + Usavrsen login



function nastavnik_predmet() {

global $userid,$user_siteadmin;



$predmet=intval($_REQUEST['predmet']);
if ($predmet==0) { 
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	biguglyerror("Nije izabran predmet."); 
	return; 
}

$q1 = myquery("select p.naziv from predmet as p, ponudakursa as pk where pk.id=$predmet and pk.predmet=p.id");
$predmet_naziv = mysql_result($q1,0,0);

//$tab=$_REQUEST['tab'];
//if ($tab=="") $tab="Opcije";

//logthis("Admin Predmet $predmet - tab $tab");



// Da li korisnik ima pravo pristupa

if (!$user_siteadmin) { // 3 = site admin
	$q10 = myquery("select np.admin from nastavnik_predmet as np where np.nastavnik=$userid and np.predmet=$predmet");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)<1) {
		zamgerlog("privilegije (predmet $predmet)",3);
		biguglyerror("Nemate pravo pristupa");
		return;
	} 
}



?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Opcije predmeta</h3></p>

<SCRIPT language="JavaScript">
function changemodul
</SCRIPT>
<p>Izaberite opcije koje želite da učinite dostupnim studentima:<br/>
<?


// Click na checkbox za dodavanje modula

if ($_REQUEST['akcija'] == "set_smodul") {
	$smodul = intval($_REQUEST['smodul']);
	if ($_REQUEST['aktivan']==0) $aktivan=1; else $aktivan=0;
	$q15 = myquery("update studentski_moduli set aktivan=$aktivan where id=$smodul");
	if ($aktivan==1)
		zamgerlog("aktiviran studentski modul $smodul (predmet p$predmet)",2); // nivo 2: edit
	else
		zamgerlog("deaktiviran studentski modul $smodul (predmet p$predmet)",2); // nivo 2: edit
}


// Studentski moduli koji su aktivirani za ovaj predmet

$q20 = myquery("select id,gui_naziv,aktivan from studentski_moduli where predmet=$predmet order by id");
if (mysql_num_rows($q20)<1)
	print "<p>Nijedan modul nije ponuđen.</p>\n";
while ($r20 = mysql_fetch_row($q20)) {
	$smodul = $r20[0];
	$naziv = $r20[1];
	$aktivan=$r20[2];
	if ($aktivan==0) $checked=""; else $checked="CHECKED";
	?>
	<input type="checkbox" onchange="javascript:location.href='?sta=nastavnik/predmet&predmet=<?=$predmet?>&akcija=set_smodul&smodul=<?=$smodul?>&aktivan=<?=$aktivan?>'" <?=$checked?>> <?=$naziv?><br/>
	<?
}



}

?>