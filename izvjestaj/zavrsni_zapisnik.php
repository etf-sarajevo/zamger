<?

// IZVJESTAJ/ZAVRSNI_ZAPISNIK - Zapisnik o odbrani završnog rada


function izvjestaj_zavrsni_zapisnik() {

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?



$id_zavrsni = intval($_REQUEST['zavrsni']);

$q10 = myquery("select z.naslov as naslov, i.naziv as odsjek, z.student as student_id, z.mentor as mentor_id, z.predsjednik_komisije as predsjednik_id, z.clan_komisije as clan_id, UNIX_TIMESTAMP(z.termin_odbrane) as termin_odbrane, z.rad_na_predmetu as id_rad_na_predmetu, ts.ciklus as ciklus, z.sala as sala, z.odluka as odluka, s.institucija as institucija
from zavrsni as z, predmet as p, institucija as i, ponudakursa as pk, studij as s, tipstudija as ts
where z.id=$id_zavrsni and z.predmet=p.id and p.institucija=i.id and ". // uslovi za detekciju ciklusa studija
"pk.predmet=p.id and pk.akademska_godina=z.akademska_godina and pk.studij=s.id and s.tipstudija=ts.id");

if (mysql_num_rows($q10) > 0) {
	$r10 = mysql_fetch_assoc($q10);
	
}

if (mysql_num_rows($q10)<1 || $r10["mentor_id"] == 0 || $r10["predsjednik_id"] == 0 || $r10["clan_id"] == 0 || $r10["termin_odbrane"] == 0) {
	niceerror("Zapisnik se ne može odštampati jer nisu unijeta sva obavezna polja");
	?><p>Da biste mogli štampati zapisnik, morate popuniti sva polja koja se nalaze na zapisniku, a to su: naslov teme, kandidat, mentor i oba člana komisije i termin odbrane.</p>
	<?
	nicemessage("<a href=\"javascript:history.go(-1);\">Nazad</a>");
	return;
}

$q20 = myquery("select o.prezime as prezime, o.imeoca as imeoca, o.ime as ime, o.brindexa as brindexa, o.spol as spol, UNIX_TIMESTAMP(o.datum_rodjenja) as datum_rodjenja, o.telefon as telefon, o.mjesto_rodjenja as mjesto_rodjenja, o.adresa as adresa, o.adresa_mjesto as adresa_mjesto_id
from osoba as o
where o.id=".$r10["student_id"]);
$r20 = mysql_fetch_assoc($q20);

$mentor = tituliraj($r10["mentor_id"], true);
$predsjednik = tituliraj($r10["predsjednik_id"], true);
$clan = tituliraj($r10["clan_id"], true);

$q25 = myquery("select naziv, opcina from mjesto where id=".$r20["mjesto_rodjenja"]);
$r25 = mysql_fetch_assoc($q25);

$q27 = myquery("select naziv from opcina where id=".$r25["opcina"]);
$r27 = mysql_fetch_assoc($q27);

$q30 = myquery("select naziv from mjesto where id=".intval($r20["adresa_mjesto_id"]));
$r30 = mysql_fetch_assoc($q30);

$spol = $r20["spol"];
if ($spol == "") $spol = spol($r20["ime"]);



// ZAPISNIK ZA PRVI CIKLUS
if ($r10['ciklus'] == 1) {
	// Određivanje dekana i broja protokola
	$institucija = $r10['institucija'];
	do {
		$q140 = myquery("select tipinstitucije, roditelj, dekan, broj_protokola from institucija where id=$institucija");
		if (!($r140 = mysql_fetch_row($q140))) {
			break;
		}
		if ($r140[0] == 1 && $r140[2] != 0) {
			$dekan = $r140[2];
			$broj_protokola = $r140[3];
			break;
		}
		$institucija = $r140[1];
	} while(true);

	if ($r10["id_rad_na_predmetu"] == 0) {
		niceerror("Zapisnik se ne može odštampati jer nisu unijeta sva obavezna polja");
		?><p>Na zapisniku za prvi ciklus nalaze se još i obavezna polja: rad iz kojeg je predmet. Morate popuniti i ta polja.</p>
		<?
		nicemessage("<a href=\"javascript:history.go(-1);\">Nazad</a>");
		return;
	}

	// Potreban nam je predmet iz kojeg je rad 
	$q35 = myquery("SELECT naziv FROM predmet WHERE id=".$r10["id_rad_na_predmetu"]);
	$rad_na_predmetu = mysql_result($q35,0,0);

	?>
	<p><?=$r10["odsjek"]?></p>
	<h2>Zapisnik o odbrani završnog rada</h2>

	<p>Dana <?=date("d. m. Y.", $r10["termin_odbrane"])?> godine kandidat <?=$r20["prezime"]?> (<?=$r20["imeoca"]?>) <?=$r20["ime"]?>, broj indeksa <?=$r20["brindexa"]?> <?
	if ($spol=="Z") print "odbranila"; else print "odbranio"; ?> je završni rad pod naslovom:</p>

	<p><b>&quot;<?=$r10["naslov"]?>&quot;</b></p>

	<p>U okviru predmeta: "<?=$rad_na_predmetu?>"</p>

	<p>KOMISIJA U SASTAVU</p>

	<p>&nbsp;&nbsp;&nbsp;1. <?=$predsjednik?> - Predsjednik<br>
	&nbsp;&nbsp;&nbsp;2. <?=$mentor?> - Mentor<br>
	&nbsp;&nbsp;&nbsp;3. <?=$clan?> - Član</p>

	<table border="0">
	<tr><td valign="bottom">Ocijenila je odbranu i rad sa ocjenom:</td>
	<td>
		<table border="1" cellspacing="0" cellpadding="0" width="200" height="50"><tr><td><img src="images/fnord.gif" width="200" height="50"></td></tr></table>
	</td></tr></table>

	<p>POTPISI ČLANOVA KOMISIJE:</p>

	<table border="0">
	<tr><td style="border-bottom: 1px solid black; width: 400px; height: 50px">&nbsp;</td></tr>
	<tr><td style="border-bottom: 1px solid black; width: 400px; height: 50px">&nbsp;</td></tr>
	<tr><td style="border-bottom: 1px solid black; width: 400px; height: 50px">&nbsp;</td></tr>
	</table>

	<table border="0" width="600px">
	<tr><td>Prosječna ocjena položenih ispita</td>
	<td style="border: 1px solid black;">&nbsp;</td></tr>
	<tr><td>Broj ECTS bodova</td>
	<td style="border: 1px solid black;">180</td></tr>
	<tr><td>Ukupan broj položenih ispita</td>
	<td style="border: 1px solid black;">33</td></tr>
	</table>

	<p>Datum rođenja <?=date("d. m. Y.", $r20["datum_rodjenja"])?> u mjestu <?=$r25["naziv"]?>, općina <?=$r27["naziv"]?>.</p>

	<p>Adresa na koju se dostavlja obavijest o promociji: <?=$r20["adresa"]?>, <?=$r30["naziv"]?></p>

	<p>Broj PROTOKOLA IZDATIH UVJERENJA: <?=$broj_protokola?></p>

	<p>Tel: <?=$r20["telefon"]?></p>


	<p>Sarajevo, <?=date("d. m. Y.", $r10["termin_odbrane"])?> godine</p>

	<table border="0" width="100%">
	<tr>
		<td width="60%">&nbsp;</td>
		<td width="40%" align="center"><p>DEKAN<br /><br /><br /><?=tituliraj($dekan)?></p></td>
	</tr>
	</table>
	<?


// ZAPISNIK ZA DRUGI CIKLUS
} else {
	if ($r10["sala"] == "" || $r10["odluka"] == 0) {
		niceerror("Zapisnik se ne može odštampati jer nisu unijeta sva obavezna polja");
		?><p>Na zapisniku za drugi ciklus nalaze se još i obavezna polja: sala u kojoj se vrši odbrana, odluka o imenovanju komisije (broj odluke i datum). Morate popuniti i ta polja.</p>
		<?
		nicemessage("<a href=\"javascript:history.go(-1);\">Nazad</a>");
		return;
	}

	$ciklusi = array("", "prvog", "drugog", "trećeg");
	$ciklusi_rimski = array("", "I", "II", "III");

	// Podaci o odluci
	$q50 = myquery("SELECT UNIX_TIMESTAMP(datum), broj_protokola FROM odluka WHERE id=".$r10["odluka"]);
	$datum_odluke = date("d.m.Y.", mysql_result($q50,0,0));
	$broj_odluke = mysql_result($q50,0,1);


	?>
	<style>
	h2 { text-align:center; }
	@media print {
		h2.nextpage {page-break-before: always;}
		body { 
			font-size: 11pt; 
			line-height: 120%;
		}
	}
	</style>

	<p>U skladu sa članom 31. Pravila studiranja za drugi (II) ciklus studija na Univerzitetu u Sarajevu, sačinjava se</p>
	<h2>Z A P I S N I K</h2>

	<p>sa odbrane završnog rada <?=$r20["prezime"]?> <?=genitiv($r20["ime"])?> studenta  <?=$ciklusi[$r10['ciklus']]?> (<?=$ciklusi_rimski[$r10['ciklus']]?>) ciklusa studija na Elektrotehničkom fakultetu u Sarajevu na temu &quot;<?=$r10["naslov"]?>&quot;, održane dana <?=date("d. m. Y.", $r10["termin_odbrane"])?> godine u <?=date("h:i", $r10["termin_odbrane"])?> sati u sali <?=$r10["sala"]?>.</p>

	<p>Prisutni:<br>
	Student <?=$r20["prezime"]?> <?=$r20["ime"]?>,<br>
	Komisija imenovana Odlukom NNV-a Fakulteta broj: <?=$broj_odluke?> od <?=$datum_odluke?> godine u sastavu:<br>
	1. Predsjednik <?=$predsjednik?>,<br>
	2. Mentor, <?=$mentor?>, i<br>
	3. Član, <?=$clan?> </p>

	<!--p>Ostali prisutni: publika.</p-->

	<p>Predsjednik Komisije otvorio je postupak odbrane završnog rada i konstatovao da su se stekli uslovi za odbranu, te pozvao kandidata da izloži sadržaj rada, uz obrazloženje cilja, zadataka, metoda izrade i dobivenih rezultata.</p>


	<!--p>Izlaganje je trajalo od  <?=date("h:i", $r10["termin_odbrane"])?>  do   _______sati.</p-->

	<p>Pitanja koja su postavljali članovi komisije poslije izlaganja:

	<p>Mentor/Član - <?=$mentor?>,

	<p>Pitanja 1.</p>

	<p>2.</p>

	<p>3.</p>

	<p>&nbsp;</p>

	<p>Član - <?=$clan?>, </p>

	<p>Pitanja 1.</p>

	<p>2.</p>

	<p>&nbsp;</p>

	<p>Predsjednik - <?=$predsjednik?>,</p>

	<p>Pitanja 1.</p>

	<p>2.</p>

	<p>&nbsp;</p>

	<!--p>Nakon odgovora kandidata, predsjednik Komisije je dozvolio prisutnima da postavljaju pitanja i da komentarišu završni rad.
	Komisija se zatim povukla radi donošenja Odluke.</p-->

	<p>Predsjednik Komisije nakon provedenog cjelokupnog postupka saopštio je</p>

	<h2 class="nextpage">O D L U K U </h2>

	<p>Kandidat <?=$r20["prezime"]?> <?=$r20["ime"]?> s uspjehom je <? if ($spol == "Z") print"odbranila"; else print "odbranio"; ?> završni rad na drugom (II) ciklusu studija na Elektrotehničkom fakultetu u Sarajevu i shodno Pravilniku o korištenju akademskih titula i sticanju naučnih i stručnih zvanja na visokoškolskim ustanovama u Kantonu Sarajevo (&quot;Službene novine&quot; br. 34/08) <? if ($spol == "Z") print"stekla"; else print "stekao"; ?> je pravo na akademsku titulu i zvanje</p>

	<h2>Magistar elektrotehnike – diplomirani inženjer elektrotehnike<br>
	<?=$r10["odsjek"]?></h2>

	<p>Komisija za ocjenu i odbranu završnog rada ocjenjuju rad i odbranu rada jedinstvenom ocjenom _______.</p>

	<table border="0" width="100%"><tr><td>&nbsp;</td><td>
<p>KOMISIJA:</p>
									
<p>1. ______________________ , predsjednik</p>
	
<p>2. ______________________ , mentor/član</p>

<p>3. ______________________ , član</p>
</td></tr></table>

<?

}

}
