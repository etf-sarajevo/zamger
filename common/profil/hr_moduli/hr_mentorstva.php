<?
	$fakultet=getSifrarnikData("sifrarnik_fakulteti");
	$mentorstvo=getSifrarnikData("sifrarnik_tip_mentorstva");
?>
    <div id="Korak4">
        <h2>Mentorstvo</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka o mentorstvu:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unos uradite hronološki: magistranti II ciklus, doktoranti III ciklus - od najnovijeg prema najstarijem
	          </b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Datum objave:</td>
	        <td>
	           <input type="text" class="validate[required,custom[date2]]" name="datum_mentorstva" id="datum_mentorstva" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Ime kandidata:</td>
	        <td>
	           <input type="text" class="validate[required]" name="ime_kandidata" id="ime_kandidata" >
	        </td>
	      </tr> 
	      <tr>
	        <td>Naziv teme:</td>
	        <td>
	           <input type="text"  class="validate[required]" name="naziv_teme" id="naziv_teme" >
	        </td>
	      </tr> 
	      
	      <tr>
	        <td>Fakultet:</td>
	        <td>
	          <select name="mfakultet" id="mfakultet"> <?=$fakultet ?></select> 
	        </td>
	      </tr>
	      <tr>
	        <td>Vrsta mentorstva:</td>
	        <td>
	          <select name="mmentorstvo" id="mmentorstvo" >  <?=$mentorstvo ?></select>
	        </td>
	      </tr>
	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_mentorstvo"  value="Evidentiraj mentorstvo" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     <table border="0" width="800" id="tmentorstvo">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno evidentirano mentorstvo:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td >Datum</td>
	        <td >Ime kandidata</td>
	        <td>Naziv teme</td>
	        <td >Fakultet</td>
	        <td >Vrsta mentorstva</td>
	        <td >Obrada</td>
	      </tr>
	      
	    <?
			$q420 = myquery("select *, f.naziv 'fakultet', t.naziv 'vrsta' from hr_mentorstvo m , sifrarnik_fakulteti f, sifrarnik_tip_mentorstva t where fakultet=f.id and t.id=vrsta_mentora and osoba=$userid");
			while ($r420 = mysql_fetch_assoc($q420)) {
				$dat= explode(" ", $r420['datum']);
		?>
		      <tr >
		        <td ><?=my_escape($dat[0]) ?></td>
		        <td ><?=my_escape($r420['ime_kandidata']) ?></td>
		        <td><?=my_escape($r420['naziv_teme']) ?></td>
		        <td ><?=my_escape($r420['fakultet']) ?></td>
		        <td ><?=my_escape($r420['vrsta']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>
	      
	      
	      </table>
	     
	     
    </div>