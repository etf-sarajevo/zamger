<?

// NASTAVNIK/UNOS_OCJENE - pojedinačni unos konačnih ocjena


function nastavnik_unos_ocjene() {

global $userid,$user_studentska,$user_siteadmin;


// Parametri
$predmet = intval($_REQUEST['predmet']);
$ag = intval($_REQUEST['ag']);

// Naziv predmeta
$q10 = db_query("select naziv from predmet where id=$predmet");
if (db_num_rows($q10)<1) {
	biguglyerror("Nepoznat predmet");
	zamgerlog("ilegalan predmet $predmet",3); //nivo 3: greska
	zamgerlog2("nepoznat predmet", $predmet);
	return;
}
$predmet_naziv = db_result($q10,0,0);


$kolokvij = false;
$q12 = db_query("SELECT tippredmeta FROM akademska_godina_predmet WHERE akademska_godina=$ag AND predmet=$predmet");
if (db_num_rows($q12)>0 && db_result($q12,0,0) == 2000) 
// FIXME: Ovo ne treba biti hardcodirani tip predmeta nego jedan od parametara za tip predmeta
	$kolokvij = true;


// Da li korisnik ima pravo ući u modul?

if (!$user_siteadmin && !$user_studentska) {
	$q10 = db_query("select nivo_pristupa from nastavnik_predmet where nastavnik=$userid and predmet=$predmet and akademska_godina=$ag");
	if (db_num_rows($q10)<1 || db_result($q10,0,0)!="nastavnik") {
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
				ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+vrijednost+"","document.getElementById('ocjena'+"+value+").focus()");
				if (origval[id] == "/") {
					var datum_element = document.getElementById("datum"+value);
					datum_element.value = "<?=date("d. m. Y")?>";
					datum_element.style.backgroundColor = '#FFAAAA';
					document.getElementById('provjera'+value).style.visibility='visible';
					//datum_element.focus();
				}
			} else if (id.substr(0,5) == "datum") {
				var value = parseInt(element.id.substr(5));
				ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=kodatum-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+vrijednost+"","document.getElementById('datum'+"+value+").focus()","document.getElementById('provjera'+"+value+").style.visibility='hidden'");
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
			ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+oc_vrijednost+"","document.getElementById('ocjena'+"+value+").focus()");
		}
	}
	function enterhack(element,e) {
		if(e.keyCode==13) {
			if (datum_provjeren[element.id] == 0) {
				origval[element.id] = '||posalji||';
			}
			element.blur();
			document.getElementById(nextjump[element.id]).focus();
			document.getElementById(nextjump[element.id]).select();
		}
	}
	var origval=new Array();
	var nextjump=new Array();
	var datum_provjeren=new Array();
	</SCRIPT>


	<table border="1" bordercolordark="grey" cellspacing="0">
		<tr>
			<th><b>R. br.</b></th><th width="300"><b>Prezime i ime</b></th>
			<th><b>Broj indeksa</b></th>
			<th><b><? if ($kolokvij) { ?>Ispunio/la obaveze<? } else { ?>Ocjena<? } ?></b></th>
			<th><b>Datum</b></th>
			<th><b>Status</b></th>
		</tr>
	<?

	$upit = "SELECT o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_predmet as sp, ponudakursa as pk where sp.student=o.id and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag order by o.prezime, o.ime";
	
	$zebra_bg = $zebra_siva = "#f0f0f0";
	$zebra_bijela = "#ffffff";

	$q520 = db_query($upit);
	$id=0;
	$rbr=0;
	while ($r520 = db_fetch_row($q520)) {
		$rbr++;
		if ($id!=0) {
			?>
			<SCRIPT language="JavaScript"> 
				nextjump['ocjena<?=$id?>'] = "ocjena<?=$r520[0]?>";
				nextjump['datum<?=$id?>']  = "datum<?=$r520[0]?>";
			</SCRIPT>
			<?
		}
//			print "$r520[0])\"></tr>\n";
		$id=$r520[0];

		$q530 = db_query("select ocjena, UNIX_TIMESTAMP(datum_u_indeksu), datum_provjeren from konacna_ocjena where student=$r520[0] and predmet=$predmet");
		if(db_num_rows($q530)>0) {
			$ocjena = db_result($q530,0,0);
			$datum_u_indeksu = date("d. m. Y.", db_result($q530,0,1));
			$datum_provjeren = db_result($q530,0,2);
//			$datum_u_indeksu = db_result($q530,0,1);
		} else {
			$ocjena = "/";
			$datum_u_indeksu = "/";
			$datum_provjeren = 2;
		}

		if ($kolokvij) { 
			if ($ocjena == 11) { $ispunio_uslove = "CHECKED"; $ocjena = "true"; }
			else { $ispunio_uslove = ""; $ocjena = "false"; }
		}

		if ($zebra_bg == $zebra_siva) $zebra_bg=$zebra_bijela; else $zebra_bg=$zebra_siva;

		?>
		<SCRIPT language="JavaScript"> 
			origval['ocjena<?=$id?>'] = "<?=$ocjena?>";
			origval['datum<?=$id?>'] = "<?=$datum_u_indeksu?>";
			datum_provjeren['datum<?=$id?>'] = <?=$datum_provjeren?>;
		</SCRIPT>
		<tr bgcolor="<?=$zebra_bg?>">
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
				if ($datum_provjeren == 0) print "; background-color: #ffaaaa";
			?>" onblur="izgubio_focus(this)" onfocus="dobio_focus(this)" onkeydown="enterhack(this,event)">
			</td>
			<td id="provjera<?=$id?> &nbsp; " <?
				if ($datum_provjeren != 0) print "style=\"visibility:hidden\"";
			?>><font color="red"><b>Datum nije provjeren</b></font></td>
		</tr>
		<?
	}
	?>
	</table>
	<?

}

?>