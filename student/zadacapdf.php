<?

// STUDENT/ZADACAPDF - ispis zadace u PDF formatu
// Preraditi kod!


function student_zadacapdf() {

global $userid,$conf_files_path,$files,$i;
$files=array();
$i=0;




# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

if ($zadaca == 0) {
	biguglyerror("Neispravan zadatak.");
	return;
}

// Da li neko pokušava da spoofa zadaću?

$q10 = myquery("SELECT z.predmet, z.akademska_godina FROM zadaca as z, student_predmet as sp, ponudakursa as pk
WHERE sp.student=$userid and sp.predmet=pk.id and pk.predmet=z.predmet and pk.akademska_godina=z.akademska_godina and z.id=$zadaca");
if (mysql_num_rows($q10)<1) {
	biguglyerror("Ova zadaća nije iz vašeg predmeta!?");
	return;
}
$predmet = mysql_result($q10,0,0);
$ag = mysql_result($q10,0,1);

$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/";


// Podaci o zadaći
//echo "select z.zadataka,p.naziv,z.naziv,pj.ekstenzija from zadaca as z,predmet as p,programskijezik as pj where z.id=$zadaca and z.predmet=p.id and z.programskijezik=pj.id";
$q20 = myquery("select z.zadataka,p.naziv,z.naziv,pj.ekstenzija from zadaca as z,predmet as p,programskijezik as pj where z.id=$zadaca and z.predmet=p.id and z.programskijezik=pj.id");
if (mysql_num_rows($q20) < 1) {
	biguglyerror("Ne mogu pronaći zadaću");
	// .. može li se ukinuti ovo?
	return;
}
$brzad = mysql_result($q20,0,0);
$imepredmeta = strtoupper(mysql_result($q20,0,1));
$imezad = mysql_result($q20,0,2);
$ekst = mysql_result($q20,0,3);


// Podaci o studentu
//echo "select ime, prezime, brindexa from osoba where id=$userid";
$q30 = myquery("select ime, prezime, brindexa from osoba where id=$userid");
if (mysql_num_rows($q30) < 1) {
	biguglyerror("Ne mogu pronaći studenta");
	// .. može li se ukinuti ovo?
	return;
}
$ime = mysql_result($q30,0,0);
$prezime = mysql_result($q30,0,1);
$brindexa = mysql_result($q30,0,2);

// Labgrupa

$q40 = myquery("select l.naziv from labgrupa as l, student_labgrupa as sl where sl.student=$userid and sl.labgrupa=l.id and l.predmet=$predmet and l.akademska_godina=$ag limit 1");
if (mysql_num_rows($q40)>0)
	$labgrupa = mysql_result($q40,0,0);
else
	$labgrupa = ""; // nema grupe



// Računanje ocjene

$bodova_zadaca=0;
$filename=array();
for ($zadatak=1;$zadatak<=$brzad;$zadatak++) {
	// Uzmi samo rjesenje sa zadnjim IDom
	$q50 = myquery("select status,bodova,filename from zadatak where student=$userid and zadaca=$zadaca and redni_broj=$zadatak order by id desc limit 1");
	if (mysql_num_rows($q50)>0) {
		$status = mysql_result($q50,0,0);
		$bodova_zadatak = mysql_result($q50,0,1);
		if ($status==5) $bodova_zadaca += $bodova_zadatak;
		$filename[$zadatak] = mysql_result($q50,0,2);
		if (strlen($filename[$zadatak])<2) {
			$filename[$zadatak]="$zadatak$ekst";
		} 
	}
}

require_once('lib/tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

	//Page header
	public function Header() {
	
	$this->SetMargins(10,35,25,true);	
	$this->Image("images/etf-100x100.png",10,8,20);
	$this->Image("images/unsa.png",180,8,20);
        $this->SetFont("DejaVu Sans",'',10);
	$this->SetY(15);
	$this->SetX(80);
	$this->Cell(50,5,'UNIVERZITET U SARAJEVU',0,0,'C');
	$this->Ln();
	$this->SetX(80);
	$this->Cell(50,5,'ELEKTROTEHNIČKI FAKULTET',0,0,'C');
	$this->Ln();
	$this->Cell(190,5,'','B',0,'C');
	$this->Ln();
	$this->Ln();
	}
	// Page footer
	public function Footer() {
		
	if ($this->PageNo() > 1) {
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('DejaVu Sans B','I',8);
		//Text color in gray
		$this->SetTextColor(128);
		//Page number
		$this->Cell(0,10,'Stranica '.$this->PageNo(),0,0,'C');
	}
	}
}

// Prva stranica
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->AddFont("DejaVuSans","","DejaVuSans.php");
$pdf->AddFont("DejaVuSans","","DejaVuSans-Bold.php");
$pdf->AddPage();

$pdf->SetY(100);
$pdf->SetFont('DejaVuSans','',30);
$pdf->Cell(190,10,$imezad,0,0,'C');
$pdf->Ln();
$pdf->SetFont('DejaVuSans','',16);
$pdf->Cell(190,10,$imepredmeta,0,0,'C');

$pdf->SetY(-90); 
$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Student:');
$pdf->SetFont('DejaVu Sans B','',12);
$pdf->Cell(24,10,$ime.' '.$prezime,0,0,'C');
$pdf->Ln();

if ($labgrupa != "") {
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(40,10,'Grupa:');
	$pdf->SetFont('DejaVu Sans','',12);
	$pdf->Cell(19,10,$labgrupa,0,0,'C');
	$pdf->Ln();
}

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Broj indeksa:');
$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(38,10,$brindexa);
$pdf->Ln();

$pdf->Ln();

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(60,10,'Potpis:',0,0,'C');
$pdf->Ln();
$pdf->Cell(60,10,'','B',0,'C');


$pdf->SetY(-90);
$pdf->SetX(-80);

$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Ocjena:');
$pdf->SetFont('DejaVu Sans','',16);
$pdf->Cell(40,10,$bodova_zadaca);
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetX(-80);
$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(60,10,'Potpis tutora:',0,0,'C');
$pdf->Ln();
$pdf->SetX(-80);
$pdf->Cell(60,10,'','B',0,'C');

include_once('lib/geshi/geshi.php');

//Funkcija koja kupi putanje za sve fajlove u direktoriju
function OtvaranjeDirektorija($dirPutanja){
	global $files;
        if(!($handle=opendir($dirPutanja))){
                die("Greska kod otvaranja dirketorija $dirPutanja");
         }
        
        while( $file =readdir($handle) ) {
		
            if($file !="." && $file !=".."){
                
                if( is_dir($dirPutanja."/".$file)){
                    $file.="/";
                }
                $fajlovi[]=$file;
            }
        }
	
        foreach($fajlovi as $file){
		
		if(substr($file,-1)=="/")
		{
			$dir =$dirPutanja."/".substr($file,0,-1);
			OtvaranjeDirektorija($dir);
		}
		else
		{
			$files[]=$dirPutanja."/".$file;
		}
	    
}

return $files;
}
//Funkcija koja brise cijeli direktorij
function delete_directory($dirname) {
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
		
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (!is_dir($dirname."/".$file))
				 unlink($dirname."/".$file);
			 else
				delete_directory($dirname.'/'.$file);    
		 }
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
}

// Zadaci
include('lib/pclzip/pclzip.lib.php');

// Fajlovi tipa cpp,c se mogu slati i u formi attachmenta i vrsi se bojenje sintakse

for ($i=1; $i<=$brzad; $i++) {
	
	if ($filename[$i]=="") continue;
	if (!file_exists("$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/$filename[$i]")) {
		zamgerlog("ne postoji fajl za zadacu z$zadaca zadatak $i student u$userid", 3);
		continue;
	}
	$extrenut=strtolower(end(explode('.',$filename[$i])));

//Extract zip fajlovaa

	if($extrenut=="zip") {
		if (!file_exists("$lokacijazadaca$userid")){
			mkdir ("$lokacijazadaca$userid",0777);
			mkdir ("$lokacijazadaca$userid/$i",0777);
		}
		$archive = new PclZip("$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/$filename[$i]");
		if ($archive->extract(PCLZIP_OPT_ADD_PATH, "$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/$userid/$i") == 0) {
			die("Error : ".$archive->errorInfo(true));
		}
		
		$dir="$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/$userid";
		$files=OtvaranjeDirektorija("$dir/$i");
		
//------------------------------------------------------------------------------

	        $txt = "";
		foreach($files as $putanjaFajla)
		{
			$naslov='<html><p><font size="14" color="black">'.basename($putanjaFajla).'</font></p></html>';
			$txt="";
			$txt =$txt.file_get_contents($putanjaFajla);
			$extrenut=strtolower(end(explode('.',$putanjaFajla)));
			$geshi =& new GeSHi($txt,$extrenut);
			$txt = $geshi->parse_code();
		
			if ($txt != false) {
				// Zamijeni tabove sa po 8 razmaka
				$txt = str_replace("\t","        ",$txt);
				
				$pdf->SetAutoPageBreak(1,15);
				$pdf->AddPage();
				$pdf->SetX(15);
				$pdf->SetFont('DejaVu Sans','',16);
				$pdf->Cell(40,10,'Zadatak '.$i.'.');
				$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $naslov, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
				$pdf->Ln();
				$pdf->SetX(15);
				$pdf->SetFont('DejaVu Sans','',10);
				$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $txt, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
		
			}
		}
		}
	else if($extrenut=="cpp" || $extrenut=="c") {
		$txt = file_get_contents("$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/$filename[$i]");
		$extrenut=strtolower(end(explode('.',$filename[$i])));
		$naslov='<html><p><font size="14" color="black">'.$filename[$i].'</font></p></html>';
		
		$geshi =& new GeSHi($txt,$extrenut);
		$txt = $geshi->parse_code();
		$txt = str_replace("\t","        ",$txt);
			
		$pdf->SetAutoPageBreak(1,15);
		$pdf->AddPage();
		$pdf->SetX(15);
			
		$pdf->Cell(40,10,'Zadatak '.$i.'.');					
		$pdf->SetFont('DejaVu Sans','',16);
		
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $naslov, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
		$pdf->SetX(15);
		$pdf->SetFont('DejaVu Sans','',10);
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $txt, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);
			
	}
	else {
		niceerror("Ovaj tip datoteke nije podržan");
		return;
	}
	$files=null;
	
}
delete_directory($dir);	

$pdf->Output($ime.'_'.$prezime.'_'.$imezad.'.pdf', 'I');

}

?>