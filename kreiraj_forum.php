<?
require("lib/libvedran.php");
require("lib/config.php");
require("lib/zamger.php");


dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);

 $sql = "INSERT zamger.studentski_modul SET " .
"id ='5', " .
"modul ='student/forum_komentari', " .
"gui_naziv ='Forum Komentari', " .
"novi_prozor ='1'";
if (myquery($sql)) { echo("<P>Ubaceno</P>"); } else { echo("<P>Greska: " . mysql_error() . "</P>"); };

?>