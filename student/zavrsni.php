<?
// STUDENT/ZAVRSNI - studenski modul za prijavu na teme zavrsnih radova i ulazak na stanicu zavrsnih

function student_zavrsni()  {
	//debug mod aktivan
	global $userid, $user_student;

	$predmet = intval($_REQUEST['predmet']);
	$ag = intval($_REQUEST['ag']);	
	
	// Da li student slusa predmet?
	$q900 = myquery("select sp.predmet from student_predmet as sp, ponudakursa as pk where sp.student=$userid and sp.predmet=pk.id and pk.predmet=$predmet and pk.akademska_godina=$ag");
	if (mysql_num_rows($q900)<1)  {
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
		
	

	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<?
	
	if ($akcija == 'prijava') {
		$naziv = intval($_REQUEST['naziv']);
		
		// Upisujemo u novu temu završnog rada
		$q110 = myquery("INSERT INTO student_zavrsni SET student=$userid, zavrsni=$naziv");
		nicemessage("Uspješno ste prijavljeni na temu završnog rada");
		zamgerlog("student upisan na zavrsni $naziv", 2);
		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == prijava


	if ($akcija == 'odjava') {
		$naziv = intval($_REQUEST['naziv']);
		
		$q120 = myquery("DELETE FROM student_zavrsni WHERE student=$userid AND zavrsni=$naziv");
		nicemessage("Uspješno ste odjavljeni sa teme završnog rada");
		zamgerlog("student ispisan sa zavrsnog rada $naziv", 2);
		
		print '<a href="'.$linkprefix.'">Povratak.</a>';
		return;
	} // akcija == odjava


	if ($akcija == 'zavrsnistranica') {
		require_once('common/zavrsniStrane.php');
		common_zavrsniStrane();
		return;
	} //akcija == zavrsnistranica
	
	// Glavni ekran
	if (!isset($akcija)) {
		?>
		<h2>Lista tema završnih radova</h2>
		<?

		// Početne informacije
		$q901 = myquery("SELECT id, naziv, kratki_pregled, literatura, nastavnik, FROM zavrsni ORDER BY naziv");
		$broj_tema = mysql_num_rows($q901);
		if ($broj_tema > 0) {
			$q902 = myquery("select distinct z.id from student_zavrsni as sz, zavrsni as z where sz.zavrsni=z.id and z.predmet=$predmet and z.akademska_godina=$ag");
			$broj_nepraznih = mysql_num_rows($q902);
		} 
		else {
			?>
			<span class="notice">Nema kreiranih tema završnih radova.</span>	
			<?
		}
	
		while ($r901 = mysql_fetch_row($q901)) {
			$id_zavrsni = $r901[0];
			$naziv_teme = $r901[1];
			?>
			<h3><?=$naziv_teme?></h3>
			<div class="links">
				<ul class="clearfix" style="margin-bottom: 10px;">
					<li><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=prijava"?>">Prijavi se na ovu temu završnog rada</a></li>
					<li class="last"><a href="<?=$linkprefix."&zavrsni=".$zavrsni[id]."&akcija=zavrsnistranica"?>">Stranica završnih radova</a></li>
				</ul> 
			</div>

			<table class="zavrsni" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<th width="200" align="left" valign="top" scope="row">Naziv teme završnog rada</th>
					<td width="490" align="left" valign="top"><?=$r901[1]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Kratki pregled</th>
					<td width="490" align="left" valign="top"><?=$r901[2]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Preporučena literatura</th>
					<td width="490" align="left" valign="top"><?=$r901[4]?></td>
				</tr>
                <tr>
					<th width="200" align="left" valign="top" scope="row">Odgovorni profesor</th>
					<td width="490" align="left" valign="top"><?=$r901[3]?></td>
				</tr>
			</table>
			<?
		} // while ($r901...
	} // if (!isset($akcija)
} //function
?>
