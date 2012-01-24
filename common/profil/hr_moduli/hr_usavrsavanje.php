    <div id="Korak5">
        <h2>Usavrsavanje</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos sloga:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unos uradite hronoloski: specijalisticki kursevi, seminari, treninzi - od najnovijeg prema najstarijem
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum objave:</td>
	        <td>
	           <input type="text" class="validate[required,custom[date2]]" name="datum_usavrsavanja" id="datum_usavrsavanja" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv usavrsavanja:</td>
	        <td>
	           <input type="text" class="validate[required]" name="naziv_usavrsavanja" id="naziv_usavrsavanja" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv obrazovne institucije:</td>
	        <td>
	           <input type="text"  class="validate[required]" name="naziv_institucije" id="naziv_institucije" >
	        </td>
	      </tr> 
	      
	      <tr>
	        <td>Dodijeljena kvalifikacija:</td>
	        <td>
	          <input type="text"  class="validate[required]" name="kvalifikacija" id="kvalifikacija" >
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
	     
	     <table border="0" width="800" id="tusavrsavanje">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno evidentirano:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td >Datum</td>
	        <td >Naziv usavrsavanja:</td>
	        <td>Naziv obrazovne institucije:</td>
	        <td >Dodijeljena kvalifikacija:</td>
	        <td >Obrada</td>
	      </tr>
	      
	      
	    <?
			$q420 = myquery("select * from hr_usavrsavanje where fk_osoba=$userid");
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
	     
	     
    </div>