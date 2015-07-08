 <?
 	$jezici=getSifrarnikData("maternji_jezik");
	$nivo=getSifrarnikData("sifrarnik_nivo_jezika");
?>
  	<div id="Korak6">
        <h2>Lične vještine/kompetencije</h2>
        <table border="0" width="600">
	      <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Unos podatka:</b></font>
	        </td>
	      </tr>
	      <tr>
	        <td style="height:30px">NAPOMENA:</td>
	        <td>
	          <b>Poznavanje stranih jezika, društvene i organizacijske vještine</b>
	        </td>
	      </tr>
	      <tr>
	        <td>Naziv jezika:</td>
	        <td>
	           <select name="jezik" id="jezik"><?=$jezici ?></select>
	           Nedostaje neki jezik? Pritisnite <a href="">ovdje</a> 
	        </td>
	      </tr> 
	      <tr>
	        <td>Razumijevanje:</td>
	        <td>
	          <select  name="razumjevanje" id = "razumjevanje"><?=$nivo ?></select>
	          <b><a href="" onclick="window.open('common/profil/hr_moduli/pomoc_jezik.html', 'windowname1','width=640, height=480'); return false;">(?)</a></b>
	        </td>
	      </tr>
	      <tr>
	        <td>Govor:</td>
	        <td>
	          <select name="govor" id="govor"><?=$nivo ?></select>
	          <b><a href="" onclick="window.open('common/profil/hr_moduli/pomoc_jezik.html', 'windowname1','width=640, height=480'); return false;">(?)</a></b>
	        </td>
	      </tr>
	      <tr>
	        <td>Pisanje:</td>
	        <td>
	          <select name="pisanje" id="pisanje"><?=$nivo ?></select>
	          <b><a href="" onclick="window.open('common/profil/hr_moduli/pomoc_jezik.html', 'windowname1','width=640, height=480'); return false;">(?)</a></b>
	        </td>
	      </tr>
	       <tr colspan="2">
	        <td><input type="button" class="evidentiraj_jezik" value="Evidentiraj" \></td>
	        <td>
	        </td>
	      </tr>
	      <tr>
	        <td>&nbsp;</td>
	        <td>
	        </td>
	      </tr>
	      

	     </table>
	     
	     <table border="0" width="800" id="tjezik">
	     <tr>
	        <td colspan="2" bgcolor="#999999">
	          <font color="#FFFFFF"><b>Prethodno evidentirano:</b></font>
	        </td>
	      </tr>
	      <tr bgcolor="#84A6C6"  class="tdheader">
	        <td >Naziv jezika:</td>
	        <td>Razumjevanje:</td>
	        <td >Govor:</td>
	        <td >Pisanje:</td>
	        <td >Obrada</td>
	      </tr>
	      
	    <?
			$q420 = myquery("select * from hr_kompetencije k, maternji_jezik m where m.id=jezik and osoba=$userid");
			while ($r420 = mysql_fetch_assoc($q420)) {
		?>
		      <tr >
		        <td ><?=my_escape($r420['naziv']) ?></td>
		        <td><?=my_escape($r420['razumjevanje']) ?></td>
		        <td><?=my_escape($r420['govor']) ?></td>
		        <td><?=my_escape($r420['pisanje']) ?></td>
		        <td style="text-align:center;" ><a href="#<?=intval($r420['id']) ?>"><img src="images/16x16/brisanje.png" /></a></td>
		      </tr>
	    <?
			}
		?>

	      </table>
	      <br>
	      <br> <br>
	      <input type="hidden" name="save" value="1" />
	      <button type="submit"><IMG SRC="images/32x32/spasi.png" ALIGN="absmiddle">&nbsp;&nbsp;Spasi sve prethodne unose</button>
	     
	     
    </div>