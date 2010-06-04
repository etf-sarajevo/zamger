<?

// IZVJESTAJ/PO_SMJEROVIMA_LINIJSKI - stranica koja generiše grafove za izvještaj po smjerovima uz pomoć GD biblioteke

function izvjestaj_chart_semestralni() {
	
	$id_ankete = intval($_GET['anketa']);
	$semestar = intval($_GET['semestar']);
	$semestarPGS = $semestar;
	
	$q10 = myquery("select id,naziv from akademska_godina where aktuelna=1");
	$ag = mysql_result($q10,0,0);
	
	if ($semestar != 3)
		$semestar= $semestar%2;
	
	$smjerovi;
	
	// Kupimo pitanja za datu anketu
	$result2077 = myquery("SELECT p.id, p.tekst,t.tip FROM anketa_pitanje p,anketa_tip_pitanja t WHERE p.tip_pitanja = t.id and p.anketa =$id_ankete and p.tip_pitanja=1");
	$k=0;
	$l=0;
	
	while ($pitanje = mysql_fetch_row($result2077)) {
		// Kupimo studije
		$result409 = myquery("select id, kratkinaziv from studij where moguc_upis=1");
		
		// za prvu godinu je poseban upit gdje ne postoji uslov za studije vec samo uslov na semestar
		if ($semestar==3)  // ako je izvjestaj za cijelu godinu
			$q6730PGS = myquery("SELECT ifnull(sum( b.izbor_id ) / count( * ),0) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje =$pitanje[0] and a.semestar in(1,2) AND zavrsena='Y'");
		else // ako nije onda biramo parne ili neparene semestre
			$q6730PGS = myquery("SELECT ifnull(sum( b.izbor_id ) / count( * ),0) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje =$pitanje[0] and a.semestar=$semestarPGS AND zavrsena='Y'");
		
		$prosjek[$l]=mysql_result($q6730PGS,0,0);
		$smjerovi[1][$k] = $prosjek[$l];
		$l++;
		
		// za ostale studije koristimo isti upit
		while($studij = mysql_fetch_row($result409)){
			//kupimo vrijednosti
			if ($semestar==3)  // ako je izvjestaj za cijelu godinu
				$q6730 = myquery("SELECT ifnull(sum( b.izbor_id ) / count( * ),0) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje =$pitanje[0] AND a.studij =$studij[0] AND zavrsena='Y' and a.semestar not in (1,2)");
			else // ako nije onda biramo parne ili neparene semestre
				$q6730 = myquery("SELECT ifnull(sum( b.izbor_id ) / count( * ),0) FROM anketa_rezultat a, anketa_odgovor_rank b WHERE a.id = b.rezultat AND b.pitanje =$pitanje[0] AND a.studij =$studij[0] and a.semestar%2=$semestar AND zavrsena='Y' and a.semestar not in (1,2)");
			$prosjek[$l]=mysql_result($q6730,0,0);
			
			$smjerovi[$studij[0]][$k] = $prosjek[$l];
			
			$l++;
		}
		$k++;	
	}
	
	crtaj ($smjerovi,$k);
}

function crtaj ($podaci,$broj_pitanja){
	$width = 700;
	$height = 350;
	$image = imagecreate($width, $height);
	
	// boje
	$white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
	$navy = imagecolorallocate($image, 0x00, 0x00, 0x80);
	$black = imagecolorallocate($image, 0x00, 0x00, 0x00);
	$gray = imagecolorallocate($image, 0xC0, 0xC0, 0xC0);
	$red = imagecolorallocate($image, 0xFF, 0x00, 0x00);
	$green = imagecolorallocate($image, 0x00, 0xFF, 0x00);
	$blue = imagecolorallocate($image, 0x00, 0x00, 0xFF);
	$nn = imagecolorallocate($image, 0x00, 0xFF, 0xFF);
	$nn2 = imagecolorallocate($image, 0xFF, 0xFF, 0x00);
	
	$boje = array($green,$red,$blue,$nn,$nn2);
	// crtanje 
	
	$maxval = 5; // maximalna vrijednost 
	$nval = $broj_pitanja; // broj vrijednosti
	
	
	$vmargin = 20; // vertikalna margina za vrh i dno za  x-labele
	$hmargin = 38; // lijeva horizontalna margina za y-labele
	
	$base = floor(($width - $hmargin) / $nval); // distance between columns
	
	$ysize = $height - 2 * $vmargin; // visina prostora za crtanje je visina minus dvije margine
	$xsize = $nval * $base; // velicina prostora za crtanje po x osi
	
	// crtamo okvir
	// imagerectangle(image, tlx, tly, brx, bry, color);
	imagerectangle($image, $hmargin, $vmargin,$hmargin + $xsize, $vmargin + $ysize, $black);
	
	
	
	// naslov
	$titlefont = 3;
	$title = "Prosjeci za anketu po smjerovima ";
	
	//   velicin naslova u pixelima 
	$txtsz = imagefontwidth($titlefont) * strlen($title);
	
	$xpos = (int)($hmargin + ($xsize - $txtsz)/2); // centriramo naslov
	$xpos = max(1, $xpos); // uvijek da je pozitivan
	$ypos = 3; // udaljenost od vrha
	
	
	//ImageString(image, font, x, y, text, color);
	imagestring($image, $titlefont, $xpos, $ypos, $title , $black);
	
	
	
	
	
	// y labele i mrezne linije
	$labelfont = 2;
	$ngrid = 5; // number of grid lines
	
	$dydat = 1; // udaljenost izmedju mreznih linija
	$dypix = $ysize / ($ngrid + 0.5); // udaljenost izmedju mreznih linija u pixelima
	
	for ($i = 0; $i <= ($ngrid + 1); $i++) {
		// iteracija kroz y labele
	
		$ydat = (int)($i * $dydat); // visina mrezne linije u podacima
		$ypos = $vmargin + $ysize - (int)($i*$dypix); // visina mrezne linije u pixelima
	
		$txtsz = imagefontwidth($labelfont) * strlen($ydat); // sirina labele u pixelima
		$txtht = imagefontheight($labelfont); // visina labele u pixelima
	
		$xpos = (int)(($hmargin - $txtsz) / 2); // pozicija po x osi ista za sve ali se centrira ipak
		$xpos = max(1, $xpos); // uvijek pozitivno
	
		imagestring($image, $labelfont, $xpos, $ypos - (int)($txtht/2), $ydat, $black);
		
		// ctramo mreznu liniju osim ako je prvi i zadnji red
		if (!($i == 0) && !($i > $ngrid))
			imageline($image, $hmargin - 3, $ypos, $hmargin + $xsize, $ypos, $gray); 
			
	}
	
	
	// ***********  PODACI ***********
	// linije i  x labele
	
	// za svaki od studija odnosno smjerova crtamo graf 
	for ( $a =1 ; $a <= sizeof($podaci); $a++){
		
				
			$tx =$hmargin;
			$xpos = $hmargin;
			$ty = $vmargin + $ysize;
			$padding = ($xsize-20)/$nval ; // half of spacing between columns
			$yscale = $ysize / ($ngrid+1); // pixela po podacima
			
			for ($i = 0; $i < $nval; $i++) {
				$j=$i+1;
				$txtsz = imagefontwidth($labelfont) * strlen($j);
				$xmin = $hmargin + $i*$base + 3;
				$xpos = $xmin + (int)(($base - $txtsz) / 2);
				$xpos = max($xmin, $xpos);
				
				
				$cx = $tx + $padding;
				$cy = $vmargin + $ysize - (int)($podaci[$a][$i]*$dypix); // 
				
				// linije
				
				imageline($image,$tx,$ty,$xpos,$cy,$boje[$a-1]);
				imagestring($image,5,$xpos-3,$cy-13,'.',$navy);
				
				// x labele
				
				$ymax = $vmargin + $ysize;
				$ypos = $ymax + 3; // udaljenost od x ose
				
				$ty = $cy;
				$tx = $xpos;
			
				imagestring($image, $labelfont, $xpos, $ypos, $j, $black);
			} 
				
			
			
	}
	
	// flush image
	header("Content-type: image/gif"); // or "Content-type: image/png"
	imagegif($image); // or imagepng($image)
	imagedestroy($image);
}


?>
