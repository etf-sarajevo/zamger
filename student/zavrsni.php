<?

// STUDENT/ZAVRSNI - studenski modul za prijavu na teme zavrsnih radova i ulazak na stanicu zavrsnih

function student_zavrsni() 
{
	require_once("lib/zavrsni.php");

	//debug mod aktivan
	global $userid, $user_student;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);	
	
	// Da li student slusa predmet?
	$q900 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q900)<1) 
	{
		zamgerlog("student ne sluša predmet pp$predmet", 3);
		biguglyerror("Niste upisani na ovaj predmet");
		return;
	}
	
	$linkprefix = "?sta=student/zavrsni&predmet=$predmet&ag=$ag";
	$akcija = $_REQUEST['akcija'];
	$id = intval($_REQUEST['id']);

	// Spisak svih tema zavrsnih radova
	$q932 = myquery("SELECT id, naziv, predmet, opis, nastavnik FROM zavrsni WHERE predmet=$predmet AND akademska_godina=$ag ORDER BY vrijeme DESC");
	$svi_zavrsni = array();
	while ($r932 = mysql_fetch_assoc($q932))
		$svi_zavrsni[] = $r932;

	// Broj članova po temi
	$broj_studenata = array();
	$q933 = myquery("select z.id, count(sz.student) FROM zavrsni as z, student_zavrsni as sz WHERE z.id=sz.zavrsni AND z.predmet=$predmet AND z.akademska_godina=$ag GROUP BY sz.zavrsni");
	while ($r933 = mysql_fetch_row($q933))
		$broj_studenata[$r933[0]]=$r933[1];

	// Da li je student prijavljen na neku temu?
	$clan_zavrsni = 0;
	$q934 = myquery("SELECT z.id FROM zavrsni as z, student_zavrsni as sz WHERE z.id=sz.zavrsni AND sz.student=$userid AND z.predmet=$predmet AND z.akademska_godina=$ag LIMIT 1");
	if (mysql_num_rows($q934)>0) 
		$clan_zavrsni = mysql_result($q934,0,0);
	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<?

	// Akcije
	if ($akcija == 'prijava') 
	{
		$zavrsni = intval($_REQUEST['zavrsni']);

		// Da li je tema na ovom predmetu?
		$nasao=false;
		foreach ($svi_zavrsni as $zavrsni2) 
		{
			if ($zavrsni2[id]==$zavrsni) { $nasao=true; break; }
		}

		if ($nasao==false) 
		{
			niceerror("Nepoznata tema završnog rada!");
			zamgerlog("prijava na temu završnog rada $zavrsni koji nije na predmetu pp$predmet", 3);
		} 

		else if ($broj_studenata[$zavrsni]>=1) 
		{
			niceerror("Tema završnog rada je zauzeta.");
			zamgerlog("prijava na temu završnog rada $zavrsni koja je popunjena", 3);
		}

		else if ($broj_studenata[$zavrsni]==0) 
		{
			niceerror("Ne možete kreirati novu temu završnog rada.");
			zamgerlog("dosegnut limit broja tema završnih radova na predmetu pp$predmet", 3);
		}

		else 
		{
			// Upisujemo u novu temu
			$q903 = myquery("INSERT INTO student_zavrsni SET student=$userid, zavrsni=$zavrsni");
			nicemessage("Uspješno ste prijavljeni na temu završnog rada");
			zamgerlog("student upisan na temu završnog rada $zavrsni (predmet pp$predmet)", 2);
			// Ispisujemo studenta sa postojećih tema završnih radova
			if ($clan_zavrsni>0) 
			{
				$q901 = myquery("DELETE FROM student_zavrsni WHERE student=$userid AND zavrsni=$clan_zavrsni");
				nicemessage("Odjavljeni ste sa stare teme završnog rada");
				zamgerlog("student ispisan sa teme završnog rada $zavrsni (predmet pp$predmet)", 2);
			}
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') 
	{
		$zavrsni = intval($_REQUEST['zavrsni']);
		
		// Da li je tema sa ovog predmeta?
		$nasao=false;
		foreach ($svi_zavrsni as $zavrsni2) 
		{
			if ($zavrsni2[id]==$zavrsni) { $nasao=true; break; }
		}

		if ($nasao==false) 
		{
			niceerror("Nepoznata tema završnog rada!");
			zamgerlog("odjava sa teme završnog rada $zavrsni koja nije sa predmeta pp$predmet", 3);
		}

		else if ($zavrsni != $clan_zavrsni) 
		{
			niceerror("Niste prijavljeni na ovu temu završnog rada");
			zamgerlog("odjava sa teme završnog rada $zavrsni na koji nije prijavljen", 3);
		}

		else 
		{
			$q904 = myquery("DELETE FROM student_zavrsni WHERE student=$userid AND zavrsni=$zavrsni");
			nicemessage("Uspješno ste odjavljeni sa teme završnog rada");
			zamgerlog("student ispisan sa teme završnog rada $zavrsni (predmet pp$predmet)", 2);
		}

		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'zavrsnistranica') 
	{
		require_once('common/zavrsniStrane.php');
		common_zavrsniStrane();
		return;
	} //akcija == zavrsnistranica


	// Glavni ekran
	?>
	<h2>Završni radovi</h2>
	<span class="notice">
	<?	// Ako je upisivanje zaključano, ispisaćemo samo onu temu koju je student prijavio
	$zavrsni_za_ispis = array();
	if ($zakljucani_zavrsni==1 && $clan_zavrsni>0) 
	{
		foreach ($svi_zavrsni as $zavrsni) 
		{
			if ($zavrsni[id]==$clan_zavrsni)
				$zavrsni_za_ispis[] = $zavrsni;
		}
	} 
	else 
		$zavrsni_za_ispis = $svi_zavrsni;

	// Nema tema završnih radova
	if (count($svi_zavrsni)==0) 
	{
		nicemessage("Još uvijek nisu definisane teme završnih radova. Imajte strpljenja.");
	}

	// Ispis tema zavrsnih radova
	foreach ($zavrsni_za_ispis as $zavrsni) 
	{
		?>
		<h3><?=$zavrsni['naziv']?></h3>
		<div class="links">
			<ul class="clearfix">
		<?
		if ($zakljucani_zavrsni == 0) 
		{
			if ($zavrsni[id]==$clan_zavrsni) 
			{
				?>
				<li class="last"><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=odjava"?>">Odustani od prijave na ovu temu završnog rada</a></li>	
				<?

			} 
			else if ($broj_studenata[$zavrsni[id]]>=1) 
			{
				?>
				<li style="color:red" class="last">Tema završnog rada je zauzeta i ne možete se prijaviti.</li>
				<?

			} 
			else if ($broj_studenata[$zavrsni[id]]==0 && $limit_tema) 
			{
				?>
				<div style="color:red; margin-top: 10px;">Ne možete kreirati novu temu završnog rada. Prijavite se na teme na kojima ima mjesta.</div>	
				<?

			} 
			else if ($clan_zavrsni==0) 
			{
				?>
				<li class="last"><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=prijava"?>">Prijavi se na ovu temu završnog rada</a></li>
				<?

			} 
			else 
			{
				?>	
				<li class="last"><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=prijava"?>">Prijavi se na ovu temu završnog rada.</a></li>   	
				<?
			}

		}
		else 
		{ // Završni su zaključani
			?>
			<li class="last"><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=zavrsnistranica"?>">Stranice završnih radova</a></li>
			<?
		}

		// Ispis ostalih podataka o zavrsnom radu
		?>
			</ul>
		</div>	
		<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<th width="200" align="left" valign="top" scope="row">Naziv</th>
				<td width="490" align="left" valign="top"><?=$zavrsni['naziv']?></td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Prijavljeni student</th>
				<td width="490" align="left" valign="top">
					<?
					// Spisak članova završnih
					$q905 = myquery("select o.ime, o.prezime, o.brindexa from osoba as o, student_zavrsni as sz where sz.student=o.id and sz.zavrsni=".$zavrsni[id]." order by o.prezime, o.ime");
					if (mysql_num_rows($q905)<1)
						print 'Nema prijavljenih studenata.';
					else
						print "<ul>\n";
					
					while ($r905 = mysql_fetch_row($q905)) 
					{
						?>
						<li><?=$r905[1].' '.$r905[0].', '.$r905[2]?></li>
						<?
					}
					if (mysql_num_rows($r905)>0) print "</ul>\n";
					?>
				</td>
			</tr>
			<tr>
				<th width="200" align="left" valign="top" scope="row">Opis</th>
				<td width="490" align="left" valign="top"><?=nl2br($zavrsni['opis'])?></td>
			</tr>
		</table>
		<?
	} // foreach ($zavrsni_za_ispis...

} //function
?>
