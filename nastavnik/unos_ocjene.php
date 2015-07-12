<?

// NASTAVNIK/UNOS_OCJENE - pojedinačni unos konačnih ocjena


function nastavnik_unos_ocjene() {

global $userid,$user_studentska,$user_siteadmin;


// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = myquery("select naziv from predmet where id=$predmet");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = mysql_result($q10,0,0);


$kolokvij = false;
$q12 = myquery("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
if (mysql_num_rows($q12)>0 && mysql_result($q12,0,0) == 2000) 
// FIXME: Ovo ne treba biti hardcodirani tip predmeta nego jedan od parametara za tip predmeta
	$kolokvij = true;


// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin && !$user_studentska) {
	$q10 = myquery("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (mysql_num_rows($q10)<1 || mysql_result($q10,0,0)!="nastavnik") {
		zamgerlog("nastavnik/ispiti privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	} 
}


?>

<p>&nbsp;</p>

<p><h3><?=$predmet_naziv?> - Konačna ocjena (pojedinačni unos)</h3></p>

<?



	print ajah_box();

	?>
	<SCRIPT language="JavaScript">
	function dobio_focus(element) {
		element.style.borderColor='red';
		if (element.value == "/") element.value="";
	}
	function izgubio_focus(element) {
		element.style.borderColor='black';
		var id = element.id;
		if (element.value == "") element.value="/";
		var vrijednost = element.value;
		if (vrijednost!=origval[id]) {
			if (id.substr(0,6) == "ocjena") {
				var value = parseInt(element.id.substr(6));
				ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+vrijednost+"","document.getElementById('ocjena'+"+id+").focus()");
			} else if (id.substr(0,5) == "datum") {
				var value = parseInt(element.id.substr(5));
				ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=kodatum-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+vrijednost+"","document.getElementById('datum'+"+id+").focus()");
				element.style.backgroundColor="#FFFFFF";
			}
			origval[id]=vrijednost;
		}
	}
	function ispunio_uslove(element) {
		var id = element.id;
		var vrijednost = element.checked;
		if (vrijednost!=origval[id]) {
			var oc_vrijednost;
			if (vrijednost) oc_vrijednost=11;
			else oc_vrijednost='/';
			var value = parseInt(element.id.substr(6));
			ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+oc_vrijednost+"","document.getElementById('ocjena'+"+id+").focus()");
		}
	}
	function enterhack(element,e) {
		if(e.keyCode==13) {
			element.blur();
//			document.getElementById('ocjena'+gdje).focus();
//			document.getElementById('ocjena'+gdje).select();
			document.getElementById(nextjump[element.id]).focus();
			document.getElementById(nextjump[element.id]).select();
		}
	}
	var origval=new Array();
	var nextjump=new Array();
	</SCRIPT>


	<table border="1" bordercolordark="grey" cellspacing="0">
		<tr>
			<td><b>R. br.</b></td><td width="300"><b>Prezime i ime</b></td>
			<td><b>Broj indeksa</b></td>
			<td><b><? if ($kolokvij) { ?>Ispunio/la obaveze<? } else { ?>Ocjena<? } ?></b></td>
			<td><b>Datum</b></td>
		</tr>
	<?

	$upit = "SELECT o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_predmet as sp, ponudakursa as pk where sp.student=o.id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime";

	$q520 = myquery($upit);
	$id=0;
	$rbr=0;
	while ($r520 = mysql_fetch_row($q520)) {
		$rbr++;
		if ($id!=0) {
			?>
			<SCRIPT language="JavaScript"> nextjump['ocjena<?=$id?>']="ocjena<?=$r520[0]?>";</SCRIPT>
			<SCRIPT language="JavaScript"> nextjump['datum<?=$id?>']="datum<?=$r520[0]?>";</SCRIPT>
			<?
		}
//			print "$r520[0])\"></tr>\n";
		$id=$r520[0];

		$q530 = myquery("select ocjena, UNIX_TIMESTAMP(datum_u_indeksu), datum_provjeren from konacna_ocjena where student=$r520[0] and predmet=$predmet");
		if(mysql_num_rows($q530)>0) {
			$ocjena = mysql_result($q530,0,0);
			$datum_u_indeksu = date("d. m. Y.", mysql_result($q530,0,1));
			$datum_provjeren = mysql_result($q530,0,2);
//			$datum_u_indeksu = mysql_result($q530,0,1);
		} else {
			$ocjena = "/";
			$datum_u_indeksu = date("d. m. Y.", time());
			$datum_provjeren = 1;
		}

		if ($kolokvij) { 
			if ($ocjena == 11) { $ispunio_uslove = "CHECKED"; $ocjena = "true"; }
			else { $ispunio_uslove = ""; $ocjena = "false"; }
		}

		?>
		<SCRIPT language="JavaScript"> origval['ocjena<?=$id?>']="<?=$ocjena?>";</SCRIPT>
		<SCRIPT language="JavaScript"> origval['datum<?=$id?>']="<?=$datum_u_indeksu?>";</SCRIPT>
		<tr>
			<td><?=$rbr?></td>
			<td><?=$r520[2]?> <?=$r520[1]?></td>
			<td><?=$r520[3]?></td>
			<td align="center">
			<?
			if ($kolokvij) {
				?><input type="checkbox" id="ocjena<?=$id?>" onchange="ispunio_uslove(this)" <?=$ispunio_uslove?>><?
			} else {
				?><input type="text" id="ocjena<?=$id?>" size="2" value="<?=$ocjena?>" style="border:1px black solid" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack(this,event)"><?
			}
			?>
			</td>
			<td align="center"><input type="text" id="datum<?=$id?>" size="8" value="<?=$datum_u_indeksu?>" style="border:1px black solid<?
				if ($datum_provjeren != 1) print "; background-color: ffaaaa";
			?>" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack(this,event)"></td>
		</tr>
		<?
	}
	?>
	</table>
	<?

}

?>