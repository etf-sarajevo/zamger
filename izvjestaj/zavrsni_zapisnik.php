<?

// IZVJESTAJ/ZAVRSNI_ZAPISNIK - Zapisnik o odbrani završnog rada


function izvjestaj_zavrsni_zapisnik() {

?>
<p>Univerzitet u Sarajevu<br/>
Elektrotehnički fakultet Sarajevo</p>
<p>Datum i vrijeme izvještaja: <?=date("d. m. Y. H:i");?></p>
<?



$id_zavrsni = intval($_REQUEST['zavrsni']);

$q10 = myquery("select z.naslov as naslov, i.naziv as odsjek, z.student as student_id, z.mentor as mentor_id, z.predsjednik_komisije as predsjednik_id, z.clan_komisije as clan_id, UNIX_TIMESTAMP(z.termin_odbrane) as termin_odbrane, p2.naziv as rad_na_predmetu
from zavrsni as z, predmet as p, institucija as i, predmet as p2
where z.id=$id_zavrsni and z.predmet=p.id and p.institucija=i.id and z.rad_na_predmetu=p2.id");

if (mysql_num_rows($q10)<1) {
	niceerror("Zapisnik se ne može odštampati jer nisu unijeta sva obavezna polja");
	nicemessage("<a href=\"javascript:history.go(-1);\">Nazad</a>");
	return;
}

$r10 = mysql_fetch_assoc($q10);

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

?>
<p><?=$r10["odsjek"]?></p>
<h2>Zapisnik o odbrani završnog rada</h2>

<p>Dana <?=date("d. m. Y.", $r10["termin_odbrane"])?> godine kandidat <?=$r20["prezime"]?> (<?=$r20["imeoca"]?>) <?=$r20["ime"]?>, broj indeksa <?=$r20["brindexa"]?> <?
if ($spol=="Z") print "odbranila"; else print "odbranio"; ?> je završni rad pod naslovom:</p>

<p><b>&quot;<?=$r10["naslov"]?>&quot;</b></p>

<p>U okviru predmeta: "<?=$r10["rad_na_predmetu"]?>"</p>

<p>KOMISIJA U SASTAVU</p>

<p>&nbsp;&nbsp;&nbsp;1. <?=$predsjednik?> - Predsjednik<br>
&nbsp;&nbsp;&nbsp;2. <?=$mentor?> - Mentor<br>
&nbsp;&nbsp;&nbsp;1. <?=$clan?> - Član</p>

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

<p>Broj PROTOKOLA IZDATIH UVJERENJA: 06-4-1-</p>

<p>Tel: <?=$r20["telefon"]?></p>


	<table border="0" width="100%">
	<tr>
	<td>
	Sarajevo, <?=date("d. m. Y.", $r10["termin_odbrane"])?> godine.</td>
	<td align="center">Predsjednik komisije:<br>
	<br>
	<br>
	Prof. dr Narcis Behlilović, dipl. ing. el.</td>
	</tr></table>

<?

}
