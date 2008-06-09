<?

function admin_ajah() {

?>
<html>
<body onload="javascript:parent.stoploading()">
<?

$stasta=$_GET['stasta'];

if ($stasta == "prisustvo") {
	$student=$_GET['student'];
	$cas=$_GET['cas'];
	$prisutan=$_GET['prisutan'];
	if ($student>0 && $cas>0 && $prisutan>0) {
		$prisutan--;
		$q1 = myquery("select prisutan from prisustvo where student=$student and cas=$cas");
		if (mysql_num_rows($q1)<1) 
			$q2 = myquery("insert into prisustvo set prisutan=$prisutan, student=$student, cas=$cas");
		else
			$q3 = myquery("update prisustvo set prisutan=$prisutan where student=$student and cas=$cas");
	}
	?>OK<?

} else {

# Testna poruka

?>

OK wellcome to ajah :)
</body>
</html>

<?

}

}

?>
