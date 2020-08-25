<?

// NASTAVNIK/UNOS_OCJENE - pojedinačni unos konačnih ocjena


function nastavnik_unos_ocjene() {

	global $_api_http_code;
	
	
	// Parametri
	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);
	
	$course = api_call("course/$predmet/$ag");
	
	// Naziv predmeta
	$predmet_naziv = $course['courseName'];
	
	// Da li korisnik ima pravo ući u modul?
	
	if ($_api_http_code == "403") {
		zamgerlog("nastavnik/unos_ocjene privilegije (predmet pp$predmet)",3);
		zamgerlog2("nije nastavnik na predmetu", $predmet, $ag);
		biguglyerror("Nemate pravo pristupa ovoj opciji");
		return;
	}
	
	
	$kolokvij = false;
	if ($course['gradeType'] == 1 || $course['gradeType'] == 2)
		$kolokvij = true;
	
	
	?>
	
	<p>&nbsp;</p>
	
	<p><h3><?=$predmet_naziv?> - Konačna ocjena (pojedinačni unos)</h3></p>
	
	<?
	
	
	ajah_box();

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
	function ispunio_uslove(element, ocjena) {
		var id = element.id;
		var vrijednost = element.checked;
		if (vrijednost!=origval[id]) {
			var oc_vrijednost;
			if (!vrijednost) ocjena='/';
			var value = parseInt(element.id.substr(6));
			ajah_start("index.php?c=N&sta=common/ajah&akcija=izmjena_ispita&idpolja=ko-"+value+"-<?=$predmet?>-<?=$ag?>&vrijednost="+ocjena+"","document.getElementById('ocjena'+"+value+").focus()");
			// Update datuma
			if (vrijednost) {
				var datum_element = document.getElementById("datum"+value);
				datum_element.value = "<?=date("d. m. Y")?>";
				datum_element.style.backgroundColor = '#FFAAAA';
				document.getElementById('provjera'+value).style.visibility='visible';
			}
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
			<th><b><?
			if ($kolokvij && $course['gradeType'] == 2) {
				?>Uspješno odbranio/la<?
			} else if ($kolokvij) {
				?>Ispunio/la obaveze<?
			} else {
				?>Ocjena<?
			} ?></b></th>
			<th><b>Datum</b></th>
			<th><b>Status</b></th>
		</tr>
	<?

	$allStudents = api_call("group/course/$predmet/allStudents", [ "year" => $ag, "names" => true ] );
	usort($allStudents['members'], function ($s1, $s2) {
		if ($s1['student']['surname'] == $s2['student']['surname']) return bssort($s1['student']['name'], $s2['student']['name']);
		return bssort($s1['student']['surname'], $s2['student']['surname']);
	});
	
	
	$zebra_bg = $zebra_siva = "#f0f0f0";
	$zebra_bijela = "#ffffff";

	$id=0;
	$rbr=0;
	foreach($allStudents['members'] as $student) {
		$rbr++;
		if ($id!=0) {
			?>
			<SCRIPT language="JavaScript">
				nextjump['ocjena<?=$id?>'] = "ocjena<?=$student['student']['id']?>";
				nextjump['datum<?=$id?>']  = "datum<?=$student['student']['id']?>";
			</SCRIPT>
			<?
		}
//			print "$r520[0])\"></tr>\n";
		$id=$student['student']['id'];

		if($student['grade']) {
			$ocjena = $student['grade'];
			$datum_u_indeksu = date("d. m. Y.", db_timestamp($student['gradeDate']));
			$datum_provjeren = $student['gradeDateVerified'];
		} else {
			$ocjena = "/";
			$datum_u_indeksu = "/";
			$datum_provjeren = 2;
		}

		if ($kolokvij) {
			if ($ocjena == 11 || $ocjena == 12) { $ispunio_uslove = "CHECKED"; $ocjena = "true"; }
			else { $ispunio_uslove = ""; $ocjena = "false"; }
			if ($course['gradeType'] == 2) $ocjena_value = 12; else $ocjena_value = 11; // Ciljana vrijednost polja ocjena
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
			<td><?=$student['student']['surname']?> <?=$student['student']['name']?></td>
			<td><?=$student['student']['studentIdNr']?></td>
			<td align="center">
			<?
			if ($kolokvij) {
				?><input type="checkbox" id="ocjena<?=$id?>" onchange="ispunio_uslove(this,<?=$ocjena_value?>)" <?=$ispunio_uslove?>><?
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
