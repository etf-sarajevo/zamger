<?

require "student/obavijest.php";
require "student/poruka.php";

function student_notifikacija() {
	
	
global $userid;
?>
<div id="notifikacija" style="position:absolute;visibility:hidden;right:20px;bottom:10px;"> 
<img src="images/notifikacija.png"/> </div>
<div id="poruka" style="position:absolute;visibility:hidden;right:60px;bottom:10px;">
<img src="images/poruka.png"/> </div>

<div id="tabela1" style="font-family:Arial;font-size:10px;position:absolute;visibility:hidden;right:20px;bottom:50px;">


<div id="tabela2" style="font-family:Arial;font-size:10px;position:absolute;visibility:hidden;right:60px;bottom:50px;">


<script language="javascript" type="text/javascript">
var int1=self.setInterval("prikaziNotifikaciju()",60000);
var int2=self.setInterval("prikaziPoruku()",60000);

function prikaziNotifikaciju()
{
if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("tabela1").innerHTML=xmlhttp.responseText;
	var redovi = document.getElementById('tabela1').getElementsByTagName('tr');
    var redova = redovi.length;
	if (redova>0){
		document.getElementById("notifikacija").style.visibility="visible";
		document.getElementById("notifikacija").click()
		{
		if (document.getElementById("tabela1").style.visibility=="hidden"){
			document.getElementById("tabela1").style.visibility="visible";
			}
		else {
			document.getElementById("tabela1").style.visibility="hidden";
			}
		
		}
		}
    }
  }
xmlhttp.open("GET","obavijest.php",true);
xmlhttp.send();
}


function prikaziPoruku()
{
if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("tabela2").innerHTML=xmlhttp.responseText;
	var redovi = document.getElementById('tabela2').getElementsByTagName('tr');
    var redova = redovi.length;
	if (redova>0){
		document.getElementById("poruka").style.visibility="visible";
		document.getElementById("poruka").click()
		{
		if (document.getElementById("tabela2").style.visibility=="hidden"){
			document.getElementById("tabela2").style.visibility="visible";
			}
		else {
			document.getElementById("tabela2").style.visibility="hidden";
			}
		
		}
		}
    }
  }
xmlhttp.open("GET","poruka.php",true);
xmlhttp.send();
}

if (document.getElementById("poruka").style.visibility=="visible" && document.getElementById("notifikacija").style.visibility=="visible")
{
	if (document.getElementById("tabela1").style.visibility=="visible"){
			document.getElementById("tabela2").style.visibility="hidden";
			}
	if (document.getElementById("tabela2").style.visibility=="visible"){
			document.getElementById("tabela1").style.visibility="hidden";
			}
}

</script>

<?
}
?>