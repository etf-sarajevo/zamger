    <div id="Korak5">
        <h2>Nagrade / Priznanja</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unos uradite hronološki od najnovijeg prema najstarijem !
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum objave:</td>
	        <td>
	           <input type="text" class="validate[required,custom[date2]]" name="datum_nagrade" id="datum_nagrade" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv nagrade:</td>
	        <td>
	           <input type="text" class="validate[required]" name="naziv_nagrade" id="naziv_nagrade" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Opis:</td>
	        <td>
	            <textarea name=opis_nagrade  id="opis_nagrade" rows="3" cols="20"></textarea>
	        </td>
	      </tr> 
	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_nagradu" value="Evidentiraj" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     <table border="0" width="800" id="tnagrade">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno evidentirano:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td >Datum</td>
	        <td >Naziv nagrade:</td>
	        <td>Opis nagrade:</td>
	        <td >Obrada</td>
	      </tr>
	      
	    <?
			$q420 = myquery("select * from hr_nagrade_priznanja where osoba=$userid");
			while ($r420 = mysql_fetch_assoc($q420)) {
				$dat= explode(" ", $r420['datum']);
		?>
		      <tr >
		        <td ><?=my_escape($dat[0]) ?></td>
		        <td ><?=my_escape($r420['naziv']) ?></td>
		        <td><?=my_escape($r420['opis']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>

	      </table>
	     
	     
    </div>