<?

require("lib/config.php");
require("lib/libvedran.php");
require("student/rss.php");
global $userid;

dbconnect2($conf_dbhost,$conf_dbuser,$conf_dbpass,$conf_dbdb);
?>
<script language="JavaScript" type="text/javascript" src="rss.php">
</script>
<?

$q500 = myquery("select tekst, link from notifikacija where procitana=0 and tip=2 and student=$userid order by vrijeme desc limit 5"); 
$broj_redova= mysql_num_rows($q500);

echo "<table border='0'>";

if ($broj_redova>0)
{
while($row = mysql_fetch_array($q500))
  {
  echo "<tr>";
  echo "<td> <a href=\"".$row['link']."\">".$row['tekst']."</a></td>";
  echo "</tr>";
  }
}
echo "</table>";


?>

