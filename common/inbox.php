<?

// COMMON/INBOX + pregled poruka u sanducicu

// v3.9.1.0 (2008/03/05) + Novi modul common/inbox
// v3.9.1.1 (2008/03/06) + Ubacen opseg 6: labgrupa, a korisnik postaje opseg 7
// v3.9.1.2 (2008/03/07) + Sredjena prava pristupa
// v3.9.1.3 (2008/03/25) + Poljepšavanje
// v3.9.1.4 (2008/03/30) + Popravljen onblur bug u ajahu
// v3.9.1.5 (2008/04/11) + Popravljen naslov "bez naslova"
// v3.9.1.6 (2008/04/30) + "bez naslova" i kod citanja poruke
// v3.9.1.7 (2008/05/16) + Omoguceno zadavanje naslova, teksta i primaoca poruke u URLu prilikom slanja
// v3.9.1.8 (2008/06/05) + Dodan outbox
// v3.9.1.9 (2008/08/28) + Tabela osoba umjesto auth
// v3.9.1.10 (2008/10/03) + Poostren uslov za slanje poruke samo putem POST
// v3.9.1.11 (2008/10/22) + Popravljeno dodavanje viska Re:
// v3.9.1.12 (2008/12/28) + Dodano parsiranje linkova u porukama
// v4.0.0.0 (2009/02/19) + Release
// v4.0.9.1 (2009/04/29) + Prebacujem tabelu poruka (opseg 5) sa ponudekursa na predmet (neki studenti ce mozda dobiti dvije identicne poruke)
// v4.0.9.2 (2009/04/29) + Preusmjeravam tabelu labgrupa sa tabele ponudakursa na tabelu predmet
// v4.0.9.3 (2009/06/20) + Greska kod citanja poruke u opsegu 6
// v4.0.9.4 (2009/09/16) + Akademska godina je neispravno uzimana kao najnovija, umjesto kao aktuelna, sto je dovodilo do problema sa permisijama u opsegu 3
// v4.0.9.5 (2009/10/21) + Kod prikaza obavjestenja stavi naslov OBAVJESTENJE a naslov dodaj na pocetak teksta


function common_inbox() {

global $userid,$user_student, $user_nastavnik;

// LEGENDA tabele poruke
// Tip:
//    1 - obavjestenja
//    2 - lične poruke
// Opseg:
//    0 - svi korisnici Zamgera
//    1 - svi studenti
//    2 - svi nastavnici
//    3 - svi studenti na studiju (primalac - id studija)
//    4 - svi studenti na godini (primalac - id akademske godine)
//    5 - svi studenti na predmetu (primalac - id predmeta)
//    6 - svi studenti na labgrupi (primalac - id labgrupe)
//    7 - korisnik (primalac - user id)
//    8 - svi studenti na godini studija (primalac - idstudija*10+godina_studija)



// Podaci potrebni kasnije

// Zadnja akademska godina
$q20 = myquery("select id,naziv from akademska_godina where aktuelna=1");
$ag = mysql_result($q20,0,0);

// Studij koji student trenutno sluša
$studij=0;
if ($user_student) {
	$q30 = myquery("select ss.studij,ss.semestar,ts.ciklus from student_studij as ss, studij as s, tipstudija as ts where ss.student=$userid and ss.akademska_godina=$ag and ss.studij=s.id and s.tipstudija=ts.id order by ss.semestar desc limit 1");
	if (mysql_num_rows($q30)>0) {
		$studij   = mysql_result($q30,0,0);
		$semestar = mysql_result($q30,0,1);
		$ciklus   = mysql_result($q30,0,2);
		$godina_studija = ($semestar+1)/2;
	}
}



// Pravimo neki okvir za sajt

?>
<center>
<table width="80%" border="0"><tr><td>

<h1>Lične poruke</h1>

<?



//////////////////////
// Slanje poruke
//////////////////////

if ($_POST['akcija']=='send' && check_csrf_token()) {

	// Ko je primalac
	$primalac = my_escape($_REQUEST['primalac']);
	$primalac = preg_replace("/\(.*?\)/","",$primalac);

	$q300 = myquery("select id from auth where login='$primalac'");
	if (mysql_num_rows($q300)<1) {
		niceerror("Nepoznat primalac");
		return;
		// FIXME
	}
	$prim_id = mysql_result($q300,0,0);

	// Samo slanje licnih poruka je dozvoljeno...
	$q310 = myquery("insert into poruka set tip=2, opseg=7, primalac=$prim_id, posiljalac=$userid, vrijeme=NOW(), ref=".intval($_REQUEST['ref']).", naslov='".my_escape($_REQUEST['naslov'])."', tekst='".my_escape($_REQUEST['tekst'])."'");
	nicemessage("Poruka uspješno poslana");
	zamgerlog("poslana poruka za u$prim_id",2);
	zamgerlog2("poslana poruka", intval($prim_id));
}

if ($_REQUEST['akcija']=='compose' || $_REQUEST['akcija']=='odgovor') {
	if ($_REQUEST['akcija']=='odgovor') {
		$poruka = intval($_REQUEST['poruka']);
		$q200 = myquery("select posiljalac, naslov, tekst, primalac from poruka where id=$poruka");
		if (mysql_num_rows($q200) < 1) {
			niceerror("Poruka ne postoji");
			zamgerlog("pokusaj odgovora na nepostojecu poruku $poruka",3);
			zamgerlog2("pokusaj odgovora na nepostojecu poruku", $poruka);
			return;
		}

		// Ko je poslao originalnu poruku (tj. kome odgovaramo)
		$prim_id = mysql_result($q200,0,0);
		if ($prim_id == $userid) // U slučaju odgovora na poslanu poruku, ponovo šaljemo poruku istoj osobi
			$prim_id = mysql_result($q200,0,3);
		$q210 = myquery("select a.login,o.ime,o.prezime from auth as a, osoba as o where a.id=o.id and o.id=$prim_id");
		if (mysql_num_rows($q210)<1) {
			niceerror("Nepoznat pošiljalac");
			zamgerlog("poruka $poruka ima nepoznatog posiljaoca $prim_id (prilikom odgovora na poruku)",3);
			zamgerlog2("poruka ima nepoznatog posiljaoca (prilikom odgovora na poruku)", $poruka, $prim_id);
			return;
		} else
			$primalac = mysql_result($q210,0,0)." (".mysql_result($q210,0,1)." ".mysql_result($q210,0,2).")";
		
		// Prepravka naslova i teksta
		$naslov = mysql_result($q200,0,1);
		if (substr($naslov,0,3) != "Re:") $naslov = "Re: ".$naslov;
		$tekst = mysql_result($q200,0,2);
		for ($i=80;$i<strlen($tekst);$i+=81) {
			$k=$i-80;
			while ($k<$i && $k!==false) {
				$oldk=$k;
				$k = strpos($tekst, " ",$k+1);
			}
			if ($oldk==$i-80)
				$tekst = substr($tekst,0,$i)."\n".substr($tekst,$i);
			else
				$tekst = substr($tekst,0,$oldk)."\n".substr($tekst,$oldk+1);
		}
		$tekst = "> ".str_replace("\n","\n> ", $tekst);
		$tekst .= "\n\n";
	} else {
		// Omogucujemo da se naslov, tekst i primalac zadaju preko URLa
		if ($_REQUEST['naslov']) 
			$naslov = my_escape($_REQUEST['naslov']);
		else $naslov="";
		if ($_REQUEST['tekst']) 
			$tekst = my_escape($_REQUEST['tekst']);
		else $tekst="";
		if ($_REQUEST['primalac']) 
			$primalac = my_escape($_REQUEST['primalac']);
		else $primalac="";
	}
		
	?>
	<a href="?sta=common/inbox">Nazad na inbox</a><br/>
	<h3>Slanje poruke</h3>
	<?=genform("POST")?>
	<?
	if ($_REQUEST['akcija']=='odgovor') {
		?>
		<input type="hidden" name="ref" value="<?=$poruka?>"><?
	}
	?>
	<input type="hidden" name="akcija" value="send">
	<script language="javascript">
	var tm=0;
	function startaj_timer(e) {
		sakrij_pretragu();
		if(e.keyCode!=13 && e.keyCode!=9) tm = setTimeout('pretraga_primalaca()',1000);
	}
	function pretraga_primalaca() {
		var ib=document.getElementById('primalac');
		var pg=document.getElementById('pretgraga');
		if (ib.value.length<3) return;
		//alert("Trazim: "+ib.value);

		// Nadji poziciju objekta
		var curleft = curtop = 0;
		var obj=ib;
		if (obj.offsetParent) {
			do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}

		pg.style.visibility = 'visible';
		pg.style.left=curleft;
		pg.style.top=curtop+ib.offsetHeight;

		ajah_start("index.php?c=N&sta=common/ajah&akcija=pretraga&ime="+ib.value, "", "napuni_rezultate()");
	}
	function napuni_rezultate() {
		var rp=document.getElementById('rezultati_pretrage');
		var tekst = frames['zamger_ajah'].document.body.innerHTML;
		var oldpozicija=0;
		rp.innerHTML = "";
		do {
			var pozicija = tekst.indexOf('\n',oldpozicija);
			var tmptekst = tekst.substr(oldpozicija,pozicija-oldpozicija);
			if (tmptekst.length<2) { oldpozicija=pozicija+1; continue; }
			if (tmptekst == "OK") break;
			if (tmptekst == "Nema rezultata") {
				rp.innerHTML = rp.innerHTML + "<font color=\"#AAAAAA\">(Nema rezultata)</font><br/>";
			} else {
				rp.innerHTML = rp.innerHTML+"<a href=\"javascript:postavi('"+tmptekst+"')\">"+tmptekst+"</a><br/>";
			}
			oldpozicija=pozicija+1;
		} while (pozicija>=0);
	}
	function sakrij_pretragu() {
		var pg=document.getElementById('pretgraga');
		pg.style.visibility = 'hidden';
		if (tm!=0)
			clearTimeout(tm);
	}
	function postavi(prim) {
		var ib=document.getElementById('primalac');
		ib.value=prim;
		sakrij_pretragu();
	}
	function blur_dogadjaj(e) {
		setTimeout('sakrij_pretragu()',1000);
	}
	</script>
	<table border="0">
		<tr><td><b>Primalac:</b></td><td><input type="text" name="primalac" id="primalac" size="40" value="<?=$primalac?>" autocomplete="off" onkeypress="startaj_timer(event);" onblur="blur_dogadjaj(event);"></td></tr>
		<tr><td colspan="2"><input type="radio" name="metoda" value="1" DISABLED> Pošalji e-mail    <input type="radio" name="metoda" value="2" CHECKED> Pošalji Zamger poruku<br/>&nbsp;<br/></td></tr>
		<tr><td><b>Naslov:</b></td><td><input type="text" name="naslov" size="40" value="<?=$naslov?>"></td></tr>
	</table>

	<!-- Rezultati pretrage primaoca -->
	<div id="pretgraga" style="position:absolute;visibility:hidden">
		<table border="0" bgcolor="#FFFFEE"  style="border:1px;border-color:silver;border-style:solid;">
			<tr><td>
				<div id="rezultati_pretrage"></div>
			</td></tr>
		</table>
	</div>

	<br/>
	Tekst poruke:<br/>
	<textarea name="tekst" rows="10" cols="81"><?=$tekst?></textarea>
	<br/>&nbsp;<br/>
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
	</form>
	<?
	print ajah_box();
	return;
}



?>
<p><a href="?sta=common/inbox&akcija=compose">Pošalji novu poruku</a> * <?
	if ($_REQUEST['mode']=="outbox") {
?><a href="?sta=common/inbox">Vaše sanduče</a><?
	} else {
?><a href="?sta=common/inbox&mode=outbox">Vaše poslane poruke</a><?
	}
?></p>
<?



//////////////////////
// Čitanje poruke
//////////////////////


$mjeseci = array("", "januar", "februar", "mart", "april", "maj", "juni", "juli", "avgust", "septembar", "oktobar", "novembar", "decembar");

$dani = array("Nedjelja", "Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota");

$poruka = intval($_REQUEST['poruka']);
if ($poruka>0) {
	// Dobavljamo podatke o poruci
	$q10 = myquery("select opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), naslov, tekst, tip from poruka where id=$poruka");
	if (mysql_num_rows($q10)<1) {
		niceerror("Poruka ne postoji");
		zamgerlog("pristup nepostojecoj poruci $poruka",3);
		zamgerlog2("pristup nepostojecoj poruci", $poruka);
		return;
	}

	// Posiljalac
	$opseg =  mysql_result($q10,0,0);
	$prim_id = mysql_result($q10,0,1);
	$pos_id = mysql_result($q10,0,2);

	if ($opseg == 1 && !$user_student || $opseg == 2 && !$user_nastavnik || $opseg==3 && $prim_id!=$studij && $prim_id!=-$ciklus || $opseg==4 && $prim_id!=$ag ||  $opseg==7 && $prim_id!=$userid && $_REQUEST['mode']!=="outbox" || $opseg==7 && $_REQUEST['mode']==="outbox" && $pos_id!=$userid || $opseg==8 && $prim_id != ($studij*10+$godina_studija) && $prim_id != (-$ciklus*10-$godina_studija)) {
		niceerror("Nemate pravo pristupa ovoj poruci!");
		zamgerlog("pokusao pristupiti poruci $poruka",3);
		zamgerlog2("nema pravo pristupa poruci", $poruka);
		return;
	}
	if ($opseg==5) {
		// da li student ikada slusao predmet? ako jeste moze citati poruke za taj predmet... (FIXME?)
		$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$prim_id");
		if (mysql_result($q110,0,0)<1) {
			niceerror("Nemate pravo pristupa ovoj poruci!");
			zamgerlog("pokusao pristupiti poruci $poruka",3);
			zamgerlog2("nema pravo pristupa poruci", $poruka);
			return;
		}
	}
	if ($opseg==6) {
		// da li je student u labgrupi?
		$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$prim_id");
		if (mysql_result($q115,0,0)<1) {
			niceerror("Nemate pravo pristupa ovoj poruci!");
			zamgerlog("pokusao pristupiti poruci $poruka",3);
			zamgerlog2("nema pravo pristupa poruci", $poruka);
			return;
		}
	}


	$q20 = myquery("select ime,prezime from osoba where id=$pos_id");
	if (mysql_num_rows($q20)<1) {
		$posiljalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznatog posiljaoca $pos_id",3);
		zamgerlog2("poruka ima nepoznatog posiljaoca", $poruka, $pos_id);
	} else
		$posiljalac = mysql_result($q20,0,0)." ".mysql_result($q20,0,1);

	// Primalac
	if ($opseg==0)
		$primalac="Svi korisnici Zamgera";
	else if ($opseg==1)
		$primalac="Svi studenti";
	else if ($opseg==2)
		$primalac="Svi nastavnici i saradnici";
	else if ($opseg==3) {
		$q30 = myquery("select naziv from studij where id=$prim_id");
		if (mysql_num_rows($q30)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: studij)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: studij)", $poruka, $prim_id);
		} else {
			$primalac = "Svi studenti na: ".mysql_result($q30,0,0);
		}
	}
	else if ($opseg==4) {
		$q40 = myquery("select naziv from akademska_godina where id=$prim_id");
		if (mysql_num_rows($q40)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: akademska godina)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: akademska godina)", $poruka, $prim_id);
		} else {
			$primalac = "Svi studenti na akademskoj godini: ".mysql_result($q40,0,0);
		}
	}
	else if ($opseg==5) {
		$q50 = myquery("select naziv from predmet where id=$prim_id");
		if (mysql_num_rows($q50)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: predmet)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: predmet)", $poruka, $prim_id);
		} else {
			$primalac = "Svi studenti na predmetu: ".mysql_result($q50,0,0);
		}
	}
	else if ($opseg==6) {
		$q55 = myquery("select p.naziv,l.naziv from predmet as p, labgrupa as l where l.id=$prim_id and l.predmet=p.id");
		if (mysql_num_rows($q55)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: labgrupa)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: labgrupa)", $poruka, $prim_id);
		} else {
			$primalac = "Svi studenti u grupi ".mysql_result($q55,0,1)." (".mysql_result($q55,0,0).")";
		}
	}
	else if ($opseg==7) {
		$q60 = myquery("select ime,prezime from osoba where id=$prim_id");
		if (mysql_num_rows($q60)<1) {
			$primalac = "Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: korisnik)",3);
			zamgerlog2("poruka ima nepoznatog primaoca (opseg: korisnik)", $poruka, $prim_id);
		} else
			$primalac = mysql_result($q60,0,0)." ".mysql_result($q60,0,1);
	}
	else if ($opseg==8) {
		$studij = intval($prim_id / 10);
		if ($studij == -1) {
			$godina = -($prim_id+10);
			$primalac = "Svi studenti na: Prvom ciklusu studija, $godina. godina";
		} else if ($studij == -2) {
			$godina = -($prim_id+20);
			$primalac = "Svi studenti na: Drugom ciklusu studija, $godina. godina";
		} else {
			$godina = $prim_id%10;
			$q30 = myquery("select naziv from studij where id=$studij");
			if (mysql_num_rows($q30)<1) {
				$primalac="Nepoznato!?";
				zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: godina studija)",3);
				zamgerlog2("poruka ima nepoznatog primaoca (opseg: godina studija)", $poruka, $prim_id);
			} else {
				$primalac = "Svi studenti na: ".mysql_result($q30,0,0).", $godina. godina";
			}
		}
	}
	else {
		$primalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznat opseg $opseg",3);
		zamgerlog2("poruka ima nepoznat opseg", $poruka, $opseg);
	}

	// Fini datum
	$vr = mysql_result($q10,0,3);
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> - ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> - ";
	$vrijeme .= $dani[date("w",$vr)].date(", j. ",$vr).$mjeseci[date("n",$vr)].date(" Y. H:i",$vr);

	// Naslov
	$tip = mysql_result($q10,0,6);
	if ($tip == 1) {
		$naslov = "O B A V J E Š T E N J E";
		$tekst = mysql_result($q10,0,4) . "\n\n";
	} else {
		$naslov = mysql_result($q10,0,4);
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
		$tekst = "";
	}

	?><h3>Prikaz poruke</h3>
	<table cellspacing="0" cellpadding="0" border="0"  style="border:1px;border-color:silver;border-style:solid;"><tr><td bgcolor="#f2f2f2">
		<table border="0">
			<tr><td><b>Vrijeme slanja:</b></td><td><?=$vrijeme?></td></tr>
			<tr><td><b>Pošiljalac:</b></td><td><?=$posiljalac?></td></tr>
			<tr><td><b>Primalac:</b></td><td><?=$primalac?></td></tr>
			<tr><td><b>Naslov:</b></td><td><?=$naslov?> (<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$poruka?>">odgovori</a>)</td></tr>
		</table>
	</td></tr><tr><td>
		<br/>
		<table border="0" cellpadding="5"><tr><td>
		<?
		$tekst .= mysql_result($q10,0,5); // Dodajemo na eventualni naslov obavještenja
		$i=0;
		while (strpos($tekst,"http://",$i)!==false || strpos($tekst,"https://",$i)!==false) {
			$j = strpos($tekst,"http://",$i);
			if ($j==false) $j = strpos($tekst,"https://",$i);
			$k = strpos($tekst," ",$j);
			$k2 = strpos($tekst,"\n",$j);
			if ($k2<$k && $k2!=0) $k=$k2;
			if ($k==0) $k=$k2;
			if ($k==0) { $k=strlen($tekst);}

			do {
				$k--;
				$a = substr($tekst,$k,1);
			} while ($a=="."||$a=="," || $a==")" || $a=="!" || $a=="?");
			$k++;
			if ($k-$j<9) { $i=$j+1; continue; }
			$url = substr($tekst,$j,$k-$j);
			$tekst = substr($tekst,0,$j). "<a href=\"$url\" target=\"_blank\">$url</a>". substr($tekst,$k);
			$i = $j+strlen($url)+28;
		}

		$tekst =  str_replace("\n","<br/>\n",$tekst);

		print $tekst;
		?>
		</td><tr></table>
	</td></tr></table>
	<br/><br/>
	<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$poruka?>">Odgovorite na poruku</a>
	<br/><hr><br/><?
}



//////////////////////
// OUTBOX
//////////////////////

if ($_REQUEST['mode']=="outbox") {

	print "<h3>Poslane poruke:</h3>\n";
	
	?>
	<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
		<thead>
		<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b>Primalac</b></td><td width="70%"><b>Naslov</b></td></tr>
		</thead>
		<tbody>
	<?
	
	
	$vrijeme_poruke = array();
	
	$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, posiljalac from poruka where tip=2 and posiljalac=$userid order by vrijeme desc");
	while ($r100 = mysql_fetch_row($q100)) {
		$id = $r100[0];
		$opseg = $r100[2];
		$primalac = $r100[3];

		$vrijeme_poruke[$id]=$r100[1];
		$naslov = $r100[4];
		if (strlen($naslov)>60) $naslov = substr($naslov,0,55)."...";
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
	
		// Primalac
		$q120 = myquery("select ime,prezime from osoba where id=$primalac");
		if (mysql_num_rows($q120)<1)
			$primalac = "Nepoznato! Prijavite grešku";
		else
			$primalac = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
	
		// Fino vrijeme
		$vr = $vrijeme_poruke[$id];
		$vrijeme="";
		if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>, ";
		else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>, ";
		else $vrijeme .= date("j. ",$vr).$mjeseci[date("n",$vr)].", ";
		$vrijeme .= date("H:i",$vr);
	
		if ($_REQUEST['poruka'] == $id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";
	
		$code_poruke[$id]="<tr bgcolor=\"$bgcolor\" onmouseover=\"this.bgColor='#EEEEEE'\" onmouseout=\"this.bgColor='$bgcolor'\"><td>$vrijeme</td><td>$primalac</td><td><a href=\"?sta=common/inbox&poruka=$id&mode=outbox\">$naslov</a></td></tr>\n";
	}
	
	// Sortiramo po vremenu
	arsort($vrijeme_poruke);
	$count=0;
	foreach ($vrijeme_poruke as $id=>$vrijeme) {
		print $code_poruke[$id];
		$count++;
		// if ($count==20) break; // prikazujemo 20 poruka  -- TODO: stranice
	}
	if ($count==0) {
		print "<li>Nemate nijednu poruku.</li>\n";
	}
	
	print "</tbody></table>";

	?>
	</td></tr></table></center>
	<?


//////////////////////
// INBOX
//////////////////////

} else {
	$velstranice = 20; // Broj poruka po stranici
	$count=0; $ispis="";
	$stranica=intval($_REQUEST['stranica']);
	if ($stranica==0) $stranica=1;

	print "<h3>Poruke u vašem sandučetu:</h3>\n";
	
	?>
	<table border="0" width="100%" style="border:1px;border-color:silver;border-style:solid;">
		<thead>
		<tr bgcolor="#cccccc"><td width="15%"><b>Datum</b></td><td width="15%"><b>Autor</b></td><td width="70%"><b>Naslov</b></td></tr>
		</thead>
		<tbody>
	<?
	
	
	$vrijeme_poruke = array();
	
	$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, posiljalac from poruka where tip=2 order by vrijeme desc");
	while ($r100 = mysql_fetch_row($q100)) {
		$id = $r100[0];
		$opseg = $r100[2];
		$primalac = $r100[3];
		if ($opseg == 2 || $opseg==3 && $primalac!=$studij || $opseg==4 && $primalac!=$ag ||  $opseg==7 && $primalac!=$userid)
			continue;
		if ($opseg==5) {
			// da li je student ikada slusao predmet? (FIXME?)
			$q110 = myquery("select count(*) from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$primalac");
			if (mysql_result($q110,0,0)<1) continue;
		}
		if ($opseg==6) {
			// da li je student u labgrupi?
			$q115 = myquery("select count(*) from student_labgrupa where student=$userid and labgrupa=$primalac");
			if (mysql_result($q115,0,0)<1) continue;
		}
		$vrijeme_poruke[$id]=$r100[1];
		$naslov = $r100[4];
		if (strlen($naslov)>60) $naslov = substr($naslov,0,55)."...";
		if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";
	
		// Posiljalac
		$q120 = myquery("select ime,prezime from osoba where id=$r100[5]");
		if (mysql_num_rows($q120)<1)
			$posiljalac = "Nepoznato! Prijavite grešku";
		else
			$posiljalac = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);
	
		// Fino vrijeme
		$vr = $vrijeme_poruke[$id];
		$vrijeme="";
		if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i>, ";
		else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i>, ";
		else $vrijeme .= date("j. ",$vr).$mjeseci[date("n",$vr)].", ";
		$vrijeme .= date("H:i",$vr);
	
		if ($_REQUEST['poruka'] == $id) $bgcolor="#EEEECC"; else $bgcolor="#FFFFFF";
	
		//$count++;
		$count++;
		if ($count>($stranica-1)*$velstranice && $count<=$stranica*$velstranice)
			$ispis .= "<tr bgcolor=\"$bgcolor\" onmouseover=\"this.bgColor='#EEEEEE'\" onmouseout=\"this.bgColor='$bgcolor'\"><td>$vrijeme</td><td>$posiljalac</td><td><a href=\"?sta=common/inbox&poruka=$id&stranica=$stranica\">$naslov</a></td></tr>\n";
	}

	if ($count==0) {
		print "<li>Nemate nijednu poruku.</li>\n";
	}

	if ($count>$velstranice) {
		$broj_stranica = ($count-1)/$velstranice + 1;
		print "<p>Stranica: ";
		for ($i=1; $i<=$broj_stranica; $i++) {
			if ($stranica==$i)
				print "$i ";
			else
				print "<a href=\"?sta=common/inbox&stranica=$i\">$i</a> ";
		}
		print "</p>\n";
	}
	
	print $ispis;
	
	print "</tbody></table>";

	?>
	</td></tr></table></center>
	<?
}



}


?>