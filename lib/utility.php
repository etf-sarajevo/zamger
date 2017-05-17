<?

// LIB/UTILITY - razne generalno korisne funkcije za PHP, pogotovo za naš jezik

// Funkcije su inspirisane materijalima sa sajta php.net. Zahvaljujemo se autorima
// komentara kao i čitavog sajta što su omogućili ovako koristan resurs!



// Funkcija koja pretvara naša slova u stringu iz velikih u mala koristeći "sentence case" 
// (prvo slovo ostaje veliko)
function malaslova($string) {
	$slovo = substr($string,0,1);
	if ($slovo>='A' && $slovo<='Z') {
		$string = strtr($string, "ČĆŽŠĐ", "čćžšđ");
	} else {
		// Preskačemo prvo slovo
		$string = substr($string,0,2).strtr(substr($string,2),"ČĆŽŠĐ","čćžšđ");
	}
	return $string;
}


// Vraća string sa procentnim ispisom npr. "23.45%"
function procenat($dio, $total) {
	if ($total==0) return "0.00%";
	return (intval($dio/$total*10000)/100)."%";
}


// Pokušava pogoditi spol na osnovu imena
//   vraća: Z = ženski, M = muški
function spol($ime) {
	if ($ime == "Ines" || $ime == "Iris" || $ime == "Iman") return "Z";
	if (substr($ime,strlen($ime)-1) == "a" && $ime != "Vanja" && $ime != "Peđa" && $ime != "Mirza" && $ime != "Feđa" && $ime != "Saša" && $ime != "Alija" && $ime != "Mustafa" && $ime != "Novica" && $ime != "Avdija" && $ime != "Zikrija")
		return "Z";
	else
		return "M";
}


// Vraća vokativ riječi (primitivno)
function vokativ($rijec, $spol) {
	if ($spol=="Z") return $rijec;
	$slovo = substr($rijec,strlen($rijec)-1);
	if ($slovo == "a" || $slovo == "e" || $slovo == "i" || $slovo == "o" || $slovo == "u" || $slovo == "k")
		return $rijec;
	else if ($slovo == "h")
		return substr($rijec,0,strlen($rijec)-1)."še";
	else if ($slovo == "g")
		return substr($rijec,0,strlen($rijec)-1)."že";
	else
		return $rijec."e";
}

// Vraća genitiv riječi (primitivno)
function genitiv($rijec, $spol='?') {
	if ($spol == '?') $spol = spol($rijec);
	$slovo = substr($rijec,strlen($rijec)-1);
	if ($slovo == "a")
		return substr($rijec,0,strlen($rijec)-1)."e";
	else if ($spol == "Z")
		return $rijec;
	else
		return $rijec."a";
}


// Određuje mimetype fajla koristeći komandu "file"
function mimetype($file) {
	$file_output = `file -bi '$file'`;
	$file_output = str_replace("\n", "", $file_output);
	if (strstr($file_output, ";"))
		$file_output = substr($file_output, 0, strpos($file_output, ";"));
	if (strstr($file_output, ","))
		$file_output = substr($file_output, 0, strpos($file_output, ","));
	return $file_output;
}


// Funkcija za konverziju arapskih brojeva u rimske
function rimski_broj($arapski_broj = '') { 
	if ($arapski_broj == '') { $arapski_broj = date("Y"); } // Po defaultu vraća trenutnu godinu
	$arapski_broj          = intval($arapski_broj); 
	$arapski_broj_text     = "$arapski_broj"; 
	$arapski_broj_duzina   = strlen($arapski_broj_text); 

	// Ne postoje rimski brojevi van opsega [1,4999]
	if ($arapski_broj > 4999 || $arapski_broj < 1) { return false; } 

	// Ne postoji rimska cifra za nulu
	$rimske_cifre_jedinice = array('', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX');
	$rimske_cifre_desetice = array('', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC');
	$rimske_cifre_stotice  = array('', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCC', 'CM');
	$rimske_cifre_hiljade  = array('', 'M', 'MM', 'MMM', 'MMMM');
	
	while (strlen($arapski_broj_text) < 4) $arapski_broj_text = "0" . $arapski_broj_text;

	$anu = intval(substr($arapski_broj_text, 3, 1)); 
	$anx = intval(substr($arapski_broj_text, 2, 1)); 
	$anc = intval(substr($arapski_broj_text, 1, 1)); 
	$anm = intval(substr($arapski_broj_text, 0, 1)); 

	$rimski_broj = $rimske_cifre_hiljade[$anm] . $rimske_cifre_stotice[$anc] . $rimske_cifre_desetice[$anx] . $rimske_cifre_jedinice[$anu]; 
	return $rimski_broj; 
}


// Funkcija koja zamjenjuje stringove koji liče na URL sa HTML kodom koji linkuje na njih
function linkuj_urlove($tekst) {
	$i=0;
	while (strpos($tekst,"http://",$i)!==false || strpos($tekst,"https://",$i)!==false) {
		$j = strpos($tekst,"http://",$i);
		if ($j==false) $j = strpos($tekst,"https://",$i);
		
		// Prvi sljedeći razmak ili kraj stringa
		$k = strpos($tekst," ",$j);
		$k2 = strpos($tekst,"\n",$j);
		if ($k2<$k && $k2!=0) $k=$k2;
		if ($k==0) $k=$k2;
		if ($k==0) { $k=strlen($tekst);}

		// Interpunkcijski znakovi kojim se obično završava rečenica nisu dio URLa
		do {
			$k--;
			$a = substr($tekst,$k,1);
		} while ($a=="."||$a=="," || $a==")" || $a=="!" || $a=="?"); 
		
		// Stringove kraće od 9 znakova ne smatramo URLom
		$k++;
		if ($k-$j<9) { $i=$j+1; continue; }
		
		// Zamjenjujemo URL sa linkom na URL
		$url = substr($tekst,$j,$k-$j);
		$tekst = substr($tekst,0,$j). "<a href=\"$url\" target=\"_blank\">$url</a>". substr($tekst,$k);
		$i = $j+strlen($url)+28;
	}
	return $tekst;
}


// String sa lijepim ispisom veličine u Kibibajtima
function nicesize($size) {
	if ($size>1024*1024*1024) {
		return intval($size/(1024*1024*1024/10))/10 . " GB";
	} else if ($size>1024*1024*10) {
		return intval($size/(1024*1024)) . " MB";
	} else if ($size>1024*1024) {
		return intval($size/(1024*1024/10))/10 . " MB";
	} else if ($size>1024*10) {
		return intval($size/1024) . " kB";
	} else if ($size>1024) {
		return intval($size / (1024/10))/10 . " kB";
	} else {
		return $size . " B";
	}
}


// Sortiranje za bosanski jezik
// Upotreba: usort($niz, "bssort");
function bssort($a, $b) {
	$a=strtolower($a); $b=strtolower($b);
	static $abeceda = array("a","A","b","B","c","C","č","Č","ć","Ć","d","đ","Đ","e","f","g","h","i","j","k","l","m","n","o","p", "q","r","s","š","Š","t","u","v", "w","x","y","z","ž","Ž");
	$min = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$min; $i++) {
		$ca = substr($a,$i,1); if (ord($ca)>127) $ca = substr($a,$i,2);
		$cb = substr($b,$i,1); if (ord($cb)>127) $cb = substr($b,$i,2);
		$k=array_search($ca,$abeceda); $l=array_search($cb,$abeceda);
		if ($k<$l) return -1; if ($k>$l) return 1;
	}
	if (strlen($a)<strlen($b)) return -1;
	return 1;
}


// Shortcut funkcija: da li se string završava nekim drugim stringom
// Primjer: if (ends_with($filename, ".txt")) echo "Tekst";
function ends_with($string, $substring) {
	if (strlen($string) >= strlen($substring))
		if (substr($string, strlen($string)-strlen($substring)) === $substring)
			return true;
	return false;
}


// Funkcija koja rekurzivno briše direktorij sa poddirektorijima i fajlovima
function rm_minus_r($path) {
	if ($handle = opendir($path)) {
		while ($file = readdir($handle)) {
			if ($file == "." || $file == "..") continue;
			$filepath = "$path/$file";
			if (is_dir($filepath)) {
				rm_minus_r($filepath);
				rmdir($filepath);
			} else {
				unlink($filepath);
			}
		}
	}
	closedir($handle);
}


// Funkcija koja uklanja ilegalne i potencijalno opasne Unicode karaktere iz ulaza
// TODO: treba je koristiti gdje god je moguće!
function clear_unicode($text) {
	// Zbog buga u libc-u koji se propagira na PHP: 
	//	https://bugs.php.net/bug.php?id=48147
	// linije ispod trebaju ostati iskomentarisane na verzijama PHPa v5.0-v7.0!
	// U suprotnom kod ispod će raditi bolje
	
	// iconv iz nekog razloga preskače karakter sa ASCII kodom 01
	//for ($i=0; $i<strlen($text); $i++)
	//	if (ord($text[$i]) == 1) $text[$i]=" ";
	
	//if (function_exists('iconv'))
	//	return iconv("UTF-8", "UTF-8//IGNORE", $text);
	if (!function_exists('mb_convert_encoding')) return $text; // nemamo mb, ne možemo ništa
	ini_set('mbstring.substitute_character', "none"); 
	return mb_convert_encoding($text, 'UTF-8', 'UTF-8'); 
}


// Funkcije za konverziju između UNIX timestamp i MySQL internog formata 
// datuma ("Y-m-d H:i:s")
function time2mysql($timestamp) { return date("Y-m-d H:i:s",$timestamp); }
function mysql2time($v) { 
	$g = substr($v,0,4); $mj=substr($v,5,2); $d=substr($v,8,2); 
	$h=substr($v,11,2); $mi=substr($v,14,2); $s=substr($v,17,2);
	return mktime($h,$mi,$s,$mj,$d,$g);
}


// Dodaje određeni broj nula na početak broja kako bi broj cifara bio odgovarajući
// Vraća string
function nuliraj_broj($broj, $cifara=2) {
	$rez = "$broj";
	for ($i=2; $i<=$cifara; $i++)
		if (strlen($rez)<$i) $rez = "0".$rez;
	return $rez;
}


// Funkcija za testiranje ispravnosti JMBG
// Vraća prazan string ako je broj ok, a poruku greške ako nije
function testjmbg($jmbg) {
	if (strlen($jmbg)!=13) return "JMBG nema tačno 13 cifara";
	for ($i=0; $i<13; $i++) {
		$slovo = substr($jmbg,$i,1);
		if ($slovo<'0' || $slovo>'9') return "Neki od znakova nisu cifre";
		$cifre[$i] = $slovo-'0';
	}
	
	// Datum
	$dan    = $cifre[2]*10+$cifre[3];
	$mjesec = $cifre[0]*10+$cifre[1];
	$godina = $cifre[4]*100+$cifre[5]*10+$cifre[6];
	if ($cifre[4] > 5) $godina += 1000; else $godina += 2000;
	if (!checkdate($dan,$mjesec,$godina))
		return "Datum rođenja je kalendarski nemoguć: $dan $mjesec $godina";
	
	// Checksum
	$k = 11 - (( 7*($cifre[0]+$cifre[6]) + 6*($cifre[1]+$cifre[7]) + 5*($cifre[2]+$cifre[8]) + 4*($cifre[3]+$cifre[9]) + 3*($cifre[4]+$cifre[10]) + 2*($cifre[5]+$cifre[11]) ) % 11);
	if ($k==11) $k=0;
	if ($k!=$cifre[12]) return "Checksum ne valja ($cifre[12] a trebao bi biti $k)";
	return "";
}

?>
