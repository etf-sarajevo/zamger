<?

// STUDENT/ZADACAPDF - ispis zadace u PDF formatu


function student_zadacapdf() {

global $userid,$conf_files_path,$files,$i;
$files=array();
$i=0;


require_once("Config.php");

// Backend stuff
require_once(Config::$backend_path."core/Portfolio.php");
require_once(Config::$backend_path."core/Person.php");

// Pošto je ovo ustvari dio lms/homework modula, ovo ispod ne treba biti opcionalno
require_once(Config::$backend_path."lms/homework/Homework.php");
require_once(Config::$backend_path."lms/homework/Assignment.php");
require_once(Config::$backend_path."lms/homework/ProgrammingLanguage.php");

// Ovo treba biti opcionalno
require_once(Config::$backend_path."lms/attendance/Group.php");


# Poslani parametar:
$zadaca = intval($_GET['zadaca']);

$z = Homework::fromId($zadaca);
$predmet = $z->courseUnitId;
$ag = $z->academicYearId;

// Da li neko pokušava da spoofa zadaću?
$pf = Portfolio::fromCourseUnit($userid, $predmet, $ag);

$lokacijazadaca="$conf_files_path/zadace/$predmet-$ag/$userid/$zadaca/";


// Podaci o studentu
$osoba = Person::fromId($userid);

// Labgrupa
$grupe = Group::fromStudentAndCourse($userid, $predmet, $ag);
$labgrupa = "";
foreach ($grupe as $g)
	if (!$g->virtual) { $labgrupa = $g->name; break; }

// Jezik
$pl = ProgrammingLanguage::fromId($z->programmingLanguageId);


// Računanje ocjene

$bodova_zadaca=0;
$filename=array();
for ($zadatak=1; $zadatak<=$z->nrAssignments; $zadatak++) {
	try {
		$a = Assignment::fromStudentHomeworkNumber($userid, $z->id, $zadatak);
		if ($a->status == 5) $bodova_zadaca += $a->score;
		$filename[$zadatak] = $a->filename;
		if (strlen($filename[$zadatak])<2) {
			$filename[$zadatak]="$zadatak".$pl->extension;
		} 
	} catch (Exception $e){
		// Student nije poslao zadaću, ne radimo ništa
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
$pdf->Cell(190,10,$z->name,0,0,'C');
$pdf->Ln();
$pdf->SetFont('DejaVuSans','',16);
$pdf->Cell(190,10,$z->courseUnit->name,0,0,'C');

$pdf->SetY(-90); 
$pdf->SetFont('DejaVu Sans','',12);
$pdf->Cell(40,10,'Student:');
$pdf->SetFont('DejaVu Sans B','',12);
$pdf->Cell(24,10,$osoba->name.' '.$osoba->surname,0,0,'C');
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
$pdf->Cell(38,10,$osoba->studentIdNr);
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

//Omogucio sam da se fajlovi tipa cpp,c mogu slati i u formi attachmenta i da se vrsi bojenja uradjeno otvaranje zip-a i generisaje pdf 

for ($i=1; $i<=$z->nrAssignments; $i++) {
	
	if ($filename[$i]=="") continue;
	$extrenut=strtolower(end(explode('.',$filename[$i])));

//Extract zip fajlovaa

	if ($extrenut=="zip"){
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
	else{
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
	$files=null;
	
}
delete_directory($dir);	

$pdf->Output($osoba->name.'_'.$osoba->surname.'_'.$z->name.'.pdf', 'I');

}

?>