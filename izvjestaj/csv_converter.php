<?
  
function ob_file_callback($buffer)
{
  global $sadrzaj_bafera_za_csv;
  $sadrzaj_bafera_za_csv=$buffer;
  $ob_file = fopen('test.txt','w');
  fwrite($ob_file,$buffer);
  fclose($ob_file);
  
  
}


function izvjestaj_csv_converter() {
global $string_pdf,$string,$sadrzaj_bafera_za_csv;

ob_start('ob_file_callback');
$koji = my_escape($_REQUEST['koji_izvjestaj']);
$staf = str_replace("/","_",$koji);

include("$koji.php");//ovdje ga ukljucujem
eval("$staf();");
ob_end_clean();

//Brisemo tagove iz teksta
$sadrzaj_bafera_za_csv=strip_tags($sadrzaj_bafera_za_csv);
$sadrzaj_bafera_za_csv=str_replace("&nbsp","",$sadrzaj_bafera_za_csv);

header("Content-Disposition: attachment; filename=".$koji.".csv");
header("Content-Type: text/csv");


header("Pragma: dummy=bogus"); 
header("Cache-Control: private");

/*$k = readfile($filepath,false);
if ($k == false) {
	print "Otvaranje attachmenta nije uspjelo! Kontaktirajte administratora";
	zamgerlog("citanje fajla za attachment nije uspjelo (z$zadaca zadaca $zadaca zadatak $zadatak student $stud_id)", 3);
}
exit;*/

  $myFile = "Izvjestaj.csv";
  $fh = fopen($myFile, 'w') or die("can't open file");
  $stringData = "one, two, three, four";
  fwrite($fh, $sadrzaj_bafera_za_csv);
  fclose($fh);
  $k = readfile($myFile,false);
if ($k == false) {
	print "Otvaranje attachmenta nije uspjelo! Kontaktirajte administratora";
	zamgerlog("citanje fajla za attachment nije uspjelo (z$zadaca zadaca $zadaca zadatak $zadatak student $stud_id)", 3);
}
exit;

}

?>