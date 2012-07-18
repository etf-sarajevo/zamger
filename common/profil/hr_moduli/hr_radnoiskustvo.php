<?
	$radnomjesto=getSifrarnikData("sifrarnik_radno_mjesto");
	$oblik=getSifrarnikData("sifrarnik_oblik_zaposlenja");
	$banka=getSifrarnikData("sifrarnik_banka");
?>
    <div id="Korak1">
        <h2>Radno iskustvo</h2>
	     <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka o radnom iskustvu:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Molim vas unesite podatke označene sa <font color=red>*</font>; Hronološki</b>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Radno mjesto:</td>
	        <td>
	          <select >
	          	<?=$radnomjesto ?>
	          </select>
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Oblik zaposlenja:</td>
	        <td>
	          <select >
	          	<?=$oblik ?>
	          </select>
	        </td>
	      </tr>
	      
	      
	      <tr>
	        <td>Početak i kraj zaposlenja:</td>
	        <td>
	        Početak:
	          <input type="text" class="validate[required,custom[date2]]" name="poc" id="poc" >
	          
	          Kraj:
	          <input type="text" class="validate[required,custom[date2]]" name="kraj" id="kraj" >
	        </td>
	      </tr>
	      
	      
	      <tr>
	        <td>Radni staž:</td>
	        <td>
	          <select name="radni_staz" >
	          	<? for ($i=0;$i<100; $i++) echo "<option value=\"$i\">$i</option>"; ?>
	          </select> (godina)
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Radni staž u nastavi:</td>
	        <td>
	          <select name="radni_staz_nastava" >
	          <? for ($i=0;$i<100; $i++) echo "<option value=\"$i\">$i</option>"; ?>
	          </select> (godina)
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Broj radne knjižice:</td>
	        <td>
	          <input class="validate[required]" type="text" id="brknjizice" name="brknjizice" />
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Koeficijent:</td>
	        <td>
	          <input type="text"   class="validate[required,custom[number]]" id="koef" name="koef" />
	        </td>
	      </tr>
	      
	      
	      <tr>
	        <td>Ugovorna plaća:</td>
	        <td>
	          <input type="text" class="validate[required,custom[number]]" id="placa" name="placa" />
	        </td>
	      </tr>
	      
	      
	      <tr>
	        <td>Banka:</td>
	        <td>
	          <select name=banka ><?=$banka?> </select>
	        </td>
	      </tr>

		 <tr>
	        <td>Broj:</td>
	        <td>
	          <input name=broj type="text">
	        </td>
	      </tr>
	      	      
	       <tr>
	        <td>Napomena:</td>
	        <td>
	          <textarea name=napomena rows="3" cols="20"></textarea>
	        </td>
	      </tr>
	      
	    </table>  
    </div>