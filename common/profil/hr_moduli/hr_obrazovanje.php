<?
	// pokupi sifrarnike iz baze i zamotaj ih u <option> </option>
	$strucnasprema=getSifrarnikData("sifrarnik_strucna_sprema");
	$zvanja=getSifrarnikData("zvanje");
	$naucni_stepen=getSifrarnikData("naucni_stepen");
?>

<h2>Obrazovanje</h2>
	     
	<table border="0" width="800" id="tusavrsavanje">
	<tr>
		<td colspan="2" bgcolor="#999999">
		<font color="#FFFFFF"><b>Evidentirani podaci:</b></font>
	        </td>
	</tr>
	<tr bgcolor="#84A6C6"  class="tdheader">
		<td>Datum</td>
		<td>Naziv usavršavanja</td>
		<td>Naziv obrazovne institucije</td>
		<td>Dodijeljena kvalifikacija</td>
		<td>Obrada</td>
	</tr>
	      
	<?

	$q420 = myquery("select * from hr_usavrsavanje where osoba=$osoba");
	while ($r420 = mysql_fetch_assoc($q420)) {
		$dat= explode(" ", $r420['datum']);
		?>
		      <tr >
		        <td ><?=my_escape($dat[0]) ?></td>
		        <td ><?=my_escape($r420['naziv_usavrsavanja']) ?></td>
		        <td><?=my_escape($r420['obrazovna_institucija']) ?></td>
		        <td ><?=my_escape($r420['kvalifikacija']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>

	</table>
	<p>&nbsp;</p>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka o stečenom obrazovanju ili usavršavanju:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unesite podatke o završenim školama ili fakultetima, specijalističkim kursevima, seminarima, treninzima itd.
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum sticanja kvalifikacije:</td>
	        <td>
	           <input type="text" class="validate[required,custom[date2]]" name="datum_kvalifikacije" id="datum_kvalifikacije" >
	        </td>
	      </tr> 

	      <tr>
	        <td>Početak i kraj obrazovanja:</td>
	        <td>
	        Početak:
	          <input type="text" class="validate[required,custom[date2]]" name="poc" id="poc" >
	          
	          Kraj:
	          <input type="text" class="validate[required,custom[date2]]" name="kraj" id="kraj" >
	        </td>
	      </tr> 

	      <tr>
	        <td>Naziv obrazovne institucije:</td>
	        <td>
	           <input type="text"  class="validate[required]" name="naziv_institucije" id="naziv_institucije" >
	        </td>
	      </tr> 
	      
	      <tr>
	        <td>Dodijeljena kvalifikacija (naziv):</td>
	        <td>
	          <input type="text"  class="validate[required]" name="kvalifikacija" id="kvalifikacija" >
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Stručna sprema:</td>
	        <td>
	          <select ><?=$strucnasprema ?></select>  <b><font color=red>*</font></b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Akademsko zvanje:</td>
	        <td>
	          <select ><?=$zvanja ?></select>  <b><font color=red>*</font></b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Naučni stepen:</td>
	        <td>
	          <select ><?=$naucni_stepen ?></select>  <b><font color=red>*</font></b>
	        </td>
	      </tr>

	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_usavrsavanje" value="Evidentiraj" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     
    </div>