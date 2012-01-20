<style>
h2, h3, p
{
    margin: 0 0 15px 0;
}
#tabs{
  overflow: auto;
  width: 100%;
  list-style: none;
  margin: 0;
  padding: 1px;
}
#tabs li{
    margin: 0;
    padding: 1;
    float: left;
}

#tabs a {
    background: #0067AC;
    color: #fff;
    float: left;
 	font-weight: bold;
    height: 20px;
    padding: 10px 10px 5px 10px;
    text-decoration: none;
}

#tabs a:focus{
    outline: 0;
}

#tabs a:hover{
    background: #84A6C6;
    color: #fff;
    float: left;
    font-weight: bold;
    height: 20px;
    padding: 10px 10px 5px 10px;
    text-decoration: none;
}

#tabs #trenutni a{
    background: #fff;
    text-shadow: none;    
    color: #333;
}

#content {
    background-color: #fff;
    -moz-border-radius: 0 2px 2px 2px;
    -webkit-border-radius: 0 2px 2px 2px;
    border-radius: 0 2px 2px 2px;
    -moz-box-shadow: 0 2px 2px #000, 0 -1px 0 #fff inset;
    -webkit-box-shadow: 0 2px 2px #000, 0 -1px 0 #fff inset;
    box-shadow: 0 2px 2px #000, 0 -1px 0 #fff inset;
    padding: 30px;
    
}
</style>
<link rel="stylesheet" href="css/libs/validator/validationEngine.jquery.css" type="text/css"/>
<link rel="stylesheet" href="css/libs/ui.all.css" type="text/css" media="screen" />


<br><br>

<ul id="tabs">
<li><a href="#" title="OpstiPodaci">Opsti podaci</a></li>
<li><a href="#" title="KontaktInformacije">Kontakt informacije</a></li>
    <li><a href="#" title="RadnoIskustvo">Radno iskustvo</a></li>
    <li><a href="#" title="tab2">Obrazovanje</a></li>
    <li><a href="#" title="tab3">Usavrsavanje</a></li>
    <li><a href="#" title="tab4">Naucno-strucni radovi</a></li>    
    <li><a href="#" title="tab5">Mentorstvo</a></li> 
    <li><a href="#" title="tab6">Izdate publikacije</a></li> 
    <li><a href="#" title="tab7">Nagrade/Priznanja</a></li> 
    <li><a href="#" title="tab8">Licne vjestine/kompetencije</a></li> 
</ul>

<form id="hrforma" class="formular" method="post" action="">
<div id="content"> 
    <div id="OpstiPodaci">
        <h2>Opsti podaci</h2>

   <table border="0" width="600">
      <tr>
        <td colspan="2" bgcolor="#999999">
          <font color="#FFFFFF">OSNOVNI PODACI:</font>
        </td>
      </tr>
      <tr>
        <td>
          Ime:
        </td>
        <td>
          <input type="text" class="validate[required] text-input" name="ime" value="" id="ime" />
        </td>
      </tr>
      
      <tr>
        <td>
          Datum:
        </td>
        <td>
          <input value="" class="validate[required,custom[date],past[2010/01/01]]" type="text" name="datum" id="datum" />
        </td>
      </tr>

      </table>  
    </div>
    
     <div id="KontaktInformacije">
        <h2>Kontakt informacije</h2>
        <p>Email-ovi</p>    
    </div>
    
    <div id="RadnoIskustvo">
        <h2>Radno iskustvo</h2>
        <p>Datum (od), Datum(do) , zanimanje, radno mjesto, podrucje rada, naziv poslodavca, adresa poslodavca</p>   
    </div>

    <div id="tab2">
        <h2>Obrazovanje</h2>
        <p></p>     
    </div>

    <div id="tab3">
        <h2>Usavrsavanje</h2>
        <p></p>   
    </div>

    <div id="tab4">
        <h2>Naucno-strucni radovi</h2>
        <p></p>      
    </div>
        <div id="tab5">
        <h2>Mentorstvo</h2>
        <p></p>     
    </div>
        <div id="tab6">
        <h2>Izdate publikacije</h2>
        <p></p>     
    </div>
        <div id="tab7">
        <h2>Nagrade/Priznanja</h2>
        <p></p>     
    </div>
        <div id="tab8">
        <h2>Licne vjestine/kompetencije</h2>
        <p></p>     
    </div>
</div>
</form>

<script src="js/libs/jquery-1.6.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/libs/jquery.validationEngine-hr.js" ></script>
<script src="js/libs/jquery.validationEngine.js" ></script>
<script src="js/libs/jquery-ui.min.js" ></script>

<script>
$(document).ready(function() {
	$("#content div").hide();
	$("#tabs li:first").attr("id","trenutni"); 
	$("#content div:first").fadeIn(); 
    $('#tabs a').click(function(e) {
        e.preventDefault();        
        $("#content div").hide(); 
        $("#tabs li").attr("id",""); 
        $(this).parent().attr("id","trenutni"); 
        $('#' + $(this).attr('title')).fadeIn(); 
    });
    $("#datum").datepicker({ dateFormat: 'yy-mm-dd', yearRange: '-40:0'  });
    jQuery("#hrforma").validationEngine();
})();



</script>
