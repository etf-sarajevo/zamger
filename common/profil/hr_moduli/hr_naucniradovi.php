    <div id="Korak3">
        <h2>Naučni i stručni radovi</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podataka o naučnom ili stručnom radu:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unos uradite hronološki: naučni članak, stručni članak, zbornici radova domaćih i međunarodnih skupova - od najnovijeg prema najstarijem
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum objave:</td>
	        <td>
	           <input type="text" class="validate[required]" name="datum_rada" id="datum_rada" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv objavljenog rada:</td>
	        <td>
	           <input type="text" class="validate[required]" name="naziv_rada" id="naziv_rada" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv naučnog/stručnog časopisa:</td>
	        <td>
	           <input type="text"  name="naziv_casopisa" id="naziv_casopisa" >
	        </td>
	      </tr> 
	      
	      <tr>
	        <td>Puni naziv izdavača:</td>
	        <td>
	           <input type="text"  name="naziv_izdavaca" id="naziv_izdavaca" >
	        </td>
	      </tr>
	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_rad" value="Evidentiraj rad" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     <table border="0" width="800" id="trad">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno uneseni radovi:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td>Datum</td>
	        <td>Naziv rada</td>
	        <td>Naziv časopisa</td>
	        <td>Naziv izdavača</td>
	        <td>Obrada</td>
	      </tr>
	      
	    <?
			$q420 = myquery("select * from hr_naucni_radovi where osoba=$userid");
			while ($r420 = mysql_fetch_assoc($q420)) {
				$dat= explode(" ", $r420['datum']);
		?>
		      <tr >
		        <td ><?=my_escape($dat[0]) ?></td>
		        <td ><?=my_escape($r420['naziv_rada']) ?></td>
		        <td><?=my_escape($r420['naziv_casopisa']) ?></td>
		        <td ><?=my_escape($r420['naziv_izdavaca']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>
	      
	      
	      </table>
	     
	     
    </div>