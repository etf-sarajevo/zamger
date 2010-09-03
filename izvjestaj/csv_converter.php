<?
  
function ob_file_callback($buffer)
{
  global $sadrzaj_bafera_za_csv;
  $sadrzaj_bafera_za_csv=$buffer;
 
}


function izvjestaj_csv_converter() {
global $sadrzaj_bafera_za_csv,$conf_files_path;

ob_start('ob_file_callback');
$koji = my_escape($_REQUEST['koji_izvjestaj']);
$staf = str_replace("/","_",$koji);

include("$koji.php");//ovdje ga ukljucujem
eval("$staf();");
ob_end_clean();

//Brisemo tagove iz teksta
$sadrzaj_bafera_za_csv=str_replace("</tr>","\n",$sadrzaj_bafera_za_csv);
$sadrzaj_bafera_za_csv=str_replace("</td>",";",$sadrzaj_bafera_za_csv);
$sadrzaj_bafera_za_csv=strip_tags($sadrzaj_bafera_za_csv);
$sadrzaj_bafera_za_csv=str_replace("&nbsp","",$sadrzaj_bafera_za_csv);

header("Content-Disposition: attachment; filename=".$koji.".csv");
header("Content-Type: text/csv");


header("Pragma: dummy=bogus"); 
header("Cache-Control: private");
print $sadrzaj_bafera_za_csv;


}

?>