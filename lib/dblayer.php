<?

// LIB/DBLAYER - Sloj apstrakcije nad bazom

if ($conf_dblayer === "mysql_")
	require("dblayer/mysql_.php");
if ($conf_dblayer === "mysqli")
	require("dblayer/mysqli.php");


?>