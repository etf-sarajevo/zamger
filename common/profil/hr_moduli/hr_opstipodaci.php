<?
	$vozacka=getSifrarnikData("vozacki_kategorija",mysql_result($q400,0,22));
	$maternji =getSifrarnikData("maternji_jezik",mysql_result($q400,0,23)); 
?>
<div id="Korak1">
        <h2>Opšti podaci</h2>

   <table border="0" width="600">
      <tr>
        <td colspan="2" bgcolor="#999999">
          <font color="#FFFFFF"><b>OSNOVNI PODACI:</b></font>
        </td>
      </tr>
      <tr>
        <td style="height:30px">NAPOMENA:</td>
        <td>
          <b>Molim vas unesite podatke oznacene sa <font color=red>*</font></b>
        </td>
      </tr>
      
      <tr>
        <td>Ime:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,0)?>"   />
        </td>
      </tr>
      <tr>
        <td>Prezime:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,1)?>" />
        </td>
      </tr>
      
            <tr>
        <td>JMBG:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,5)?>" />
        </td>
      </tr>
      
            <tr>
        <td>Djevojacko prezime:</td>
        <td>
          <input type="text" name="djevojacko" value="<?=mysql_result($q400,0,20)?>" /> <b><font color=red>*</font></b>
        </td>
      </tr>
      
      
            <tr>
        <td>Ime roditelja:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,2)?>" />
        </td>
      </tr>
      
      
            <tr>
        <td>Pol:</td>
        <td>
          <input type="radio" name="spol" value="M" <?=$muskir?>> Muški &nbsp;&nbsp; <input type="radio" name="spol" value="Z" <?=$zenskir?>> Ženski
        </td>
      </tr>
      
      
      <tr>
        <td>Datum rodenja:</td>
        <td>
          <input type="text" disabled=disabled value="<?
							if (mysql_result($q400,0,8)) print date("d.m.Y", mysql_result($q400,0,8))?>" />
        </td>
      </tr>
      
      
      <tr>
        <td>Opcina rodenja:</td>
        <td>
          <select name="opcina_rodjenja" disabled=disabled ><?=$opciner?></select>
        </td>
      </tr>
      
      
      <tr>
        <td>Mjesto rodenja:</td>
        <td>
          <input type="text" disabled=disabled  value="<?=$mjestorvalue?>" >
        </td>
      </tr>

      <tr>
        <td>Maternji jezik:</td>
        <td>
          <select id=mjezik name=mjezik class="validate[required]" > 
          	<?=$maternji ?>
          </select>
          
          <b><font color=red>*</font></b>
        </td>
      </tr>

      
      <tr>
        <td>Nacionalnost:</td>
        <td>
        	<select disabled=disabled ><?=$nacion?></select>
        </td>
      </tr>
      
      
      <tr>
        <td>Drzavljanstvo:</td>
        <td>
        	<select disabled=disabled ><?=$drzavlj?></select>
        </td>
      </tr>
      
      <tr>
        <td>Vozacka dozvola:</td>
        <td>
        	<select name="vozacka"><?=$vozacka ?></select><b><font color=red>*</font></b>
        </td>
      </tr>
           
      
      <!-- 
      <tr>
        <td>
          Datum:
        </td>
        <td>
          <input value="" class="validate[required,custom[date],past[2010/01/01]]" type="text" name="datum" id="datum" />
        </td> 
      </tr> -->

      </table>  
    </div>