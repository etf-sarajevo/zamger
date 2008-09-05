<?

// ADMIN/INBOX + pregled svih poruka na sistemu

// v3.9.1.0 (2008/03/07) + Novi modul admin/inbox
// v3.9.1.1 (2008/04/11) + Popravljen naslov "bez naslova"
// v3.9.1.2 (2008/08/28) + Tabela osoba umjesto auth



function admin_inbox() {

global $userid;

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


// Pravimo neki okvir za sajt

?>
<center>
<table width="80%" border="0"><tr><td>

<h1>Monster messenger</h1>

<?

// Slanje poruke

if ($_REQUEST['akcija']=='send') {
	$tip = intval($_REQUEST['tip']);
	$opseg = intval($_REQUEST['opseg']);
	if ($opseg == 7) {
		// Ko je primalac
		list($ime,$prezime) = explode(" ",my_escape($_REQUEST['primalac']));
		$q300 = myquery("select id from osoba where ime='$ime' and prezime='$prezime'");
		if (mysql_num_rows($q300)<1) {
			niceerror("Nepoznat primalac");
			return;
			// FIXME
		}
		$primalac = mysql_result($q300,0,0);
	} else {
		$primalac = intval($_REQUEST['primalac']);
	}
	$posiljalac = intval($_REQUEST['posiljalac']);
	if ($posiljalac==0) $posiljalac=$userid;

	// Samo slanje licnih poruka je dozvoljeno...
	$q310 = myquery("insert into poruka set tip=$tip, opseg=$opseg, primalac=$primalac, posiljalac=$posiljalac, ref=".intval($_REQUEST['ref']).", naslov='".my_escape($_REQUEST['naslov'])."', tekst='".my_escape($_REQUEST['tekst'])."'");
	nicemessage("Poruka uspješno poslana");
	zamgerlog("admin poslana poruka",2);
}

if ($_REQUEST['akcija']=='compose' || $_REQUEST['akcija']=='odgovor') {
	if ($_REQUEST['akcija']=='odgovor') {
		$poruka = intval($_REQUEST['poruka']);
		$q200 = myquery("select posiljalac, naslov, tekst from poruka where id=$poruka");
		if (mysql_num_rows($q200) < 1) {
			niceerror("Poruka ne postoji");
			zamgerlog("pokusaj odgovora na nepostojecu poruku $poruka",3);
			return;
		}

		// Posiljalac
		$pos_id = mysql_result($q200,0,0);
		$q210 = myquery("select ime,prezime from osoba where id=$pos_id");
		if (mysql_num_rows($q210)<1) {
			niceerror("Nepoznat pošiljalac");
			zamgerlog("poruka $poruka ima nepoznatog posiljaoca $pos_id (prilikom odgovora na poruku)",3);
			return;
		} else
			$posiljalac = mysql_result($q210,0,0)." ".mysql_result($q210,0,1);
		
		$naslov = "Re: ".mysql_result($q200,0,1);
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
		$naslov=$tekst=$posiljalac="";
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
	<table border="0">
		<tr><td><b>Primalac:</b></td><td><input type="text" name="primalac" size="20" value="<?=$posiljalac?>"></td></tr>
		<tr><td colspan="2"><input type="radio" name="metoda" value="1" DISABLED> Pošalji e-mail    <input type="radio" name="metoda" value="2" CHECKED> Pošalji Zamger poruku<br/>&nbsp;<br/></td></tr>
		<tr><td><b>Naslov:</b></td><td><input type="text" name="naslov" size="40" value="<?=$naslov?>"></td></tr>
	</table>
	<br/>
	Tekst poruke:<br/>
	<textarea name="tekst" rows="10" cols="81"><?=$tekst?></textarea>
	<br/>&nbsp;<br/>
	<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">
	</form>
	<?
	return;
}



?>
<p><a href="?sta=admin/inbox&akcija=compose">Pošalji novu poruku</a></p>
<?


// Čitanje poruke

$mjeseci = array("", "januar", "februar", "mart", "april", "maj", "juni", "juli", "avgust", "septembar", "oktobar", "novembar", "decembar");

$dani = array("Nedjelja", "Ponedjeljak", "Utorak", "Srijeda", "Četvrtak", "Petak", "Subota");

$poruka = intval($_REQUEST['poruka']);
if ($poruka>0) {
	// Dobavljamo podatke o poruci
	$q10 = myquery("select opseg, primalac, posiljalac, UNIX_TIMESTAMP(vrijeme), naslov, tekst from poruka where id=$poruka");
	if (mysql_num_rows($q10)<1) {
		niceerror("Poruka ne postoji");
		zamgerlog("pristup nepostojecoj poruci $poruka",3);
		return;
	}

	// Posiljalac
	$pos_id = mysql_result($q10,0,2);
	$q20 = myquery("select ime,prezime from osoba where id=$pos_id");
	if (mysql_num_rows($q20)<1) {
		$posiljalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznatog posiljaoca $pos_id",3);
	} else
		$posiljalac = mysql_result($q20,0,0)." ".mysql_result($q20,0,1);

	// Primalac
	$opseg = mysql_result($q10,0,0);
	$prim_id = mysql_result($q10,0,1);
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
		} else {
			$primalac = "Svi studenti na: ".mysql_result($q30,0,0);
		}
	}
	else if ($opseg==4) {
		$q40 = myquery("select naziv from akademska_godina where id=$prim_id");
		if (mysql_num_rows($q40)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: akademska godina)",3);
		} else {
			$primalac = "Svi studenti na akademskoj godini: ".mysql_result($q40,0,0);
		}
	}
	else if ($opseg==5) {
		$q50 = myquery("select p.naziv,ag.naziv from ponudakursa as pk, predmet as p, akademska_godina as ag where pk.id=$prim_id and pk.predmet=p.id and pk.akademska_godina=ag.id");
		if (mysql_num_rows($q50)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: predmet)",3);
		} else {
			$primalac = "Svi studenti na predmetu: ".mysql_result($q50,0,0)." (".mysql_result($q50,0,1).")";
		}
	}
	else if ($opseg==6) {
		$q55 = myquery("select p.naziv,l.naziv from ponudakursa as pk, predmet as p, labgrupa as l where l.id=$prim_id and l.predmet=pk.id and pk.predmet=p.id");
		if (mysql_num_rows($q55)<1) {
			$primalac="Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: labgrupa)",3);
		} else {
			$primalac = "Svi studenti u grupi ".mysql_result($q55,0,1)." (".mysql_result($q55,0,0).")";
		}
	}
	else if ($opseg==7) {
		$q60 = myquery("select ime,prezime from osoba where id=$prim_id");
		if (mysql_num_rows($q60)<1) {
			$primalac = "Nepoznato!?";
			zamgerlog("poruka $poruka ima nepoznatog primaoca $prim_id (opseg: korisnik)",3);
		} else
			$primalac = mysql_result($q60,0,0)." ".mysql_result($q60,0,1);
	}
	else {
		$primalac = "Nepoznato!?";
		zamgerlog("poruka $poruka ima nepoznat opseg $opseg",3);
	}

	$vr = mysql_result($q10,0,3);
	if (date("d.m.Y",$vr)==date("d.m.Y")) $vrijeme = "<i>danas</i> - ";
	else if (date("d.m.Y",$vr+3600*24)==date("d.m.Y")) $vrijeme = "<i>juče</i> - ";
	$vrijeme .= $dani[date("w",$vr)].date(", j. ",$vr).$mjeseci[date("n",$vr)].date(" Y. H:i",$vr);
	

	?><h3>Prikaz poruke</h3>
	<a href="?sta=admin/inbox&akcija=odgovor&poruka=<?=$poruka?>">Odgovori na poruku</a><br/><br/>
	<table border="0">
		<tr><td><b>Vrijeme slanja:</b></td><td><?=$vrijeme?></td></tr>
		<tr><td><b>Pošiljalac:</b></td><td><?=$posiljalac?></td></tr>
		<tr><td><b>Primalac:</b></td><td><?=$primalac?></td></tr>
		<tr><td><b>Naslov:</b></td><td><?=mysql_result($q10,0,4)?></td></tr>
	</table>
	<br/>
	<?
	print str_replace("\n","<br/>\n",mysql_result($q10,0,5));
	?>
	<br/><br/>
	<a href="?sta=common/inbox&akcija=odgovor&poruka=<?=$poruka?>">Odgovori na poruku</a>
	<br/><hr><br/><?
}

print "<h3>Poruke na sistemu:</h3>\n";


$vrijeme_poruke = array();

$q100 = myquery("select id, UNIX_TIMESTAMP(vrijeme), opseg, primalac, naslov, posiljalac from poruka order by vrijeme desc");
while ($r100 = mysql_fetch_row($q100)) {
	$id = $r100[0];
	$opseg = $r100[2];
	$primalac = $r100[3];
	$vrijeme_poruke[$id]=$r100[1];
	$naslov = $r100[4];
	if (strlen($naslov)>30) $naslov = substr($naslov,0,28)."...";
	$naslov = str_replace("<","&lt;",$naslov);
	$naslov = str_replace(">","&gt;",$naslov);
	if (!preg_match("/\S/",$naslov)) $naslov = "[Bez naslova]";


	// Posiljalac
	$q120 = myquery("select ime,prezime from osoba where id=$r100[5]");
	if (mysql_num_rows($q120)<1)
		$posiljalac = "Nepoznato! Prijavite grešku";
	else
		$posiljalac = mysql_result($q120,0,0)." ".mysql_result($q120,0,1);

	$code_poruke[$id]="<li>(".date("j. n. Y. H:i",$vrijeme_poruke[$id]).") <a href=\"?sta=admin/inbox&poruka=$id\">$naslov</a>, autor: $posiljalac<br/></li>\n";
}

// Sortiramo po vremenu
asort($vrijeme_poruke);
$count=0;
foreach ($vrijeme_poruke as $id=>$vrijeme) {
	print $code_poruke[$id];
	$count++;
	// if ($count==20) break; // prikazujemo 20 poruka  -- TODO: stranice
}
if ($count==0) {
	print "<li>Nemate nijednu poruku.</li>\n";
}



?>
</td></tr></table></center>
<?

}


?>