<?

	$nacinstanovanja= getSifrarnikData("nacin_stanovanja",mysql_result($q400,0,23));

?>
     <div id="Korak2">
        <h2>Kontakt informacije</h2>

   <table border="0" width="600">
      <tr>
        <td colspan="2" bgcolor="#999999">
          <font color="#FFFFFF"><b>Kontakt informacije:</b></font>
        </td>
      </tr>
      <tr>
        <td style="height:30px">NAPOMENA:</td>
        <td>
          <b>Molim vas unesite podatke oznacene sa <font color=red>*</font></b>
        </td>
      </tr>
      
      <tr>
        <td>Adresa stanovanja:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,15)?>"   />
        </td>
      </tr>
      
      <tr>
        <td>Opcina stanovanja:</td>
        <td>
          <select disabled=disabled><?=$gradovir?></select>
        </td>
      </tr>
      
      <tr>
        <td>Telefon:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,9)?>" />
        </td>
      </tr>
      
      
      <!--tr>
        <td>E-mail:</td>
        <td>
          <input type="text" disabled=disabled value="<?=mysql_result($q400,0,2)?>" />
        </td>
      </tr-->
      
      <tr>
        <td>Nacin stanovanja:</td>
        <td>
          <select name="nacin_stanovanja"><?=$nacinstanovanja ?></select>  <b><font color=red>*</font></b>
        </td>
      </tr>

      </table>         
    </div>