<?

// STUDENT/ZAVRSNI - studenski modul za prijavu na teme zavrsnih radova i ulazak na stanicu zavrsnih

function student_zavrsni() 
{
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

	?>
	<LINK href="css/zavrsni.css" rel="stylesheet" type="text/css">
	<?

	if ($akcija == 'zavrsnistranica') 
	{
		require_once('common/zavrsniStrane.php');
		common_zavrsniStrane();
		return;
	} //akcija == zavrsnistranica

} //function
?>
