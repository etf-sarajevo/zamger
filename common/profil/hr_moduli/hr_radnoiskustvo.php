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
	          <font color="#FFFFFF"><b>Unos radnog mjesta:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Unesite podatke o radnom mjestu koje ste obavljali.</b>
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
	        <td>Poslodavac:</td>
	        <td>
	          	<input class="default" type="text" id="poslodavac" name="poslodavac" />
	        </td>
	      </tr>

	      <tr>
	        <td>Adresa poslodavca:</td>
	        <td>
	          	<input class="default" type="text" id="adresa_poslodavca" name="adresa_poslodavca" />
	        </td>
	      </tr>
	      
	      <tr>
	        <td>Radno mjesto:</td>
	        <td>
	          	<input class="default" type="text" id="radno_mjesto" name="radno_mjesto" />
	        </td>
	      </tr>
	      
	       <tr>
	        <td>Opis radnog mjesta:</td>
	        <td>
	          <textarea name="opis_radnog_mjesta" rows="3" cols="20"></textarea>
	        </td>
	      </tr>
	      
	    </table>  
    </div>