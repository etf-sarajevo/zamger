 <?
	$publikacija=getSifrarnikData("sifrarnik_tip_publikacije");
?>
    <div id="Korak3">
        <h2>Publikacije</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unos uradite hronološki: udžbenik, monografija, knjiga, priručnik, praktikum - od najnovijeg prema najstarijem
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum objave:</td>
	        <td>
	           <input type="text" class="validate[required,custom[date2]]" name="datum_publikacije" id="datum_publikacije" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv objavljene publikacije:</td>
	        <td>
	           <input type="text" class="validate[required]" name="naziv_publikacije" id="naziv_publikacije" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv časopisa/ izdavača:</td>
	        <td>
	           <input type="text"  class="validate[required]" name="naziv_ci" id="naziv_ci" >
	        </td>
	      </tr> 
	      
	      <tr>
	        <td>Vrsta publikacije:</td>
	        <td>
	          <select name="vrsta_publikacije" id="vrsta_publikacije">
	          		<?=$publikacija ?>
	          </select>
	        </td>
	      </tr>
	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_publikaciju" value="Evidentiraj publikaciju" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     <table border="0" width="800" id="tpublikacije">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno evidentirane publikacije:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td>Datum</td>
	        <td>Naziv publikacije</td>
	        <td>Naziv izdavača/časopisa</td>
	        <td>Vrsta publikacije</td>
	        <td>Obrada</td>
	      </tr>
	      
	    <?
			$q420 = myquery("select *, t.naziv 'vrsta', p.naziv 'pnaziv' from hr_publikacija p , sifrarnik_tip_publikacije t where tip_publikacije=t.id and osoba=$userid");
			while ($r420 = mysql_fetch_assoc($q420)) {
			$dat= explode(" ", $r420['datum']);
		?>
		      <tr >
		        <td ><?=my_escape($dat[0]) ?></td>
		        <td ><?=my_escape($r420['pnaziv']) ?></td>
		        <td><?=my_escape($r420['casopis']) ?></td>
		        <td ><?=my_escape($r420['vrsta']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>
	      
	      
	      </table>
	     
	     
    </div>