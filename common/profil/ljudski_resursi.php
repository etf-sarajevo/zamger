<?
	include("lib/procesiraj.php");

	// AKCIJE
	// FIXME prebaciti na stringove

	if (intval($_REQUEST['save']) == 1) { // lični podaci
		$djevojacko= my_escape($_REQUEST['djevojacko']);
		$vozacka= intval($_REQUEST['vozacka']);
		$mjezik= intval($_REQUEST['mjezik']);
		$mobitel= intval($_REQUEST['mobitel']);
		$nacin_stanovanja= intval($_REQUEST['nacin_stanovanja']);		
		myquery("update osoba set djevojacko_prezime='$djevojacko', maternji_jezik=$mjezik , vozacka_dozvola=$vozacka , mobilni_telefon='$mobitel', nacin_stanovanja=$nacin_stanovanja where id=$userid");
	}

	if (intval($_REQUEST['save']) == 2) { // usavršavanje
		$naziv= my_escape($_REQUEST['naziv_usavrsavanja']);
		$datum= strtotime(my_escape($_REQUEST['datum_usavrsavanja']));
		$institucija= my_escape($_REQUEST['naziv_institucije']);
		$kvalifikacija= my_escape($_REQUEST['kvalifikacija']);
		myquery("INSERT INTO hr_usavrsavanje (fk_osoba ,datum ,naziv_usavrsavanja ,obrazovna_institucija ,kvalifikacija)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$institucija',  '$kvalifikacija')");
	}

	if (intval($_REQUEST['save']) == 3) { // Radovi
		$naziv= my_escape($_REQUEST['naziv_rada']);
		$datum= strtotime(my_escape($_REQUEST['datum_rada']));
		$naziv_casopisa= my_escape($_REQUEST['naziv_casopisa']);
		$naziv_izdavaca= my_escape($_REQUEST['naziv_izdavaca']);
		myquery("INSERT INTO hr_naucni_radovi (fk_osoba ,datum ,naziv_rada ,naziv_casopisa ,naziv_izdavaca)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$naziv_casopisa',  '$naziv_izdavaca')");
	}

	if (intval($_REQUEST['save']) == 4) { // Mentorstva
		$datum= strtotime(my_escape($_REQUEST['datum_mentorstva']));
		$ime_kandidata= my_escape($_REQUEST['ime_kandidata']);
		$naziv_teme= my_escape($_REQUEST['naziv_teme']);
		$mfakultet= intval($_REQUEST['mfakultet']);
		$mmentorstvo= intval($_REQUEST['mmentorstvo']);
		myquery("INSERT INTO hr_mentorstvo (fk_osoba ,datum ,ime_kandidata ,naziv_teme ,fk_fakultet,fk_vrsta_mentora)VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$ime_kandidata',  '$naziv_teme',  $mfakultet,$mmentorstvo)");
	}

	if (intval($_REQUEST['save']) == 5) { // Publikacije
		$datum= strtotime(my_escape($_REQUEST['datum_publikacije']));
		$naziv= my_escape($_REQUEST['naziv_publikacije']);
		$casopis= my_escape($_REQUEST['naziv_ci']);
		$fk_tip_publikacije= intval($_REQUEST['vrsta_publikacije']);
		myquery("INSERT INTO  hr_publikacija (fk_osoba,datum ,naziv ,casopis ,fk_tip_publikacije) VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$casopis',  $fk_tip_publikacije)");
	}


	if (intval($_REQUEST['save']) == 6) { // Nagrade
		$datum= strtotime(my_escape($_REQUEST['datum_nagrade']));
		$naziv= my_escape($_REQUEST['naziv_nagrade']);
		$opis= my_escape($_REQUEST['opis_nagrade']);
		myquery("INSERT INTO `hr_nagrade_priznanja` (`fk_osoba`, `datum`, `naziv`, `opis`) VALUES ('$userid',  FROM_UNIXTIME('$datum'),  '$naziv',  '$opis')");
	}

	if (intval($_REQUEST['save']) == 7) { // Jezik
		$jezik= intval($_REQUEST['jezik']);
		$razumjevanje= intval($_REQUEST['razumjevanje']);
		$govor= intval($_REQUEST['govor']);
		$pisanje= intval($_REQUEST['pisanje']);
		myquery("INSERT INTO `hr_kompetencije` (`fk_osoba`, `jezik`, `razumjevanje`, `govor`, pisanje) VALUES ('$userid', $jezik, $razumjevanje,$govor, $pisanje )");
	}


	// Lični podaci
	$q400 = myquery("select ime, prezime, brindexa, UNIX_TIMESTAMP(datum_rodjenja), mjesto_rodjenja, jmbg, drzavljanstvo, adresa, adresa_mjesto, telefon, kanton, spol, imeoca, prezimeoca, imemajke, prezimemajke, drzavljanstvo, nacionalnost, boracke_kategorije, slika, djevojacko_prezime, vozacka_dozvola, maternji_jezik, mobilni_telefon,nacin_stanovanja from osoba where id=$userid");
	
	// Spisak gradova
	$q410 = myquery("select id,naziv, opcina, drzava from mjesto order by naziv");
	$gradovir="<option></option>";
	$gradovia="<option></option>";
	while ($r410 = mysql_fetch_row($q410)) { 
		$gradovir .= "<option"; $gradovia .= "<option";
	 	if ($r410[0]==mysql_result($q400,0,4)) { 
			$gradovir  .= " SELECTED"; 
			$mjestorvalue = $r410[1]; 
			$opcinar = $r410[2];
			$drzavar = $r410[3];
		}
	 	if ($r410[0]==mysql_result($q400,0,8)) { $gradovia  .= " SELECTED"; $adresarvalue = $r410[1]; }
		$gradovir .= ">$r410[1]</option>\n";
		$gradovia .= ">$r410[1]</option>\n";
	}
	
	
	// Spisak opcina
	$q420 = myquery("select id,naziv from opcina order by naziv");
	$opciner="<option></option>";
	while ($r420 = mysql_fetch_row($q420)) {
		$opciner .= "<option value=\"$r420[0]\"";
	 	if ($r420[0]==$opcinar) { $opciner  .= " SELECTED";  }
		$opciner .= ">$r420[1]</option>\n";
	}
	
	
	// Spisak drzava
	$q430 = myquery("select id,naziv from drzava order by naziv");
	$drzaver="<option></option>";
	$drzavlj="<option></option>";
	while ($r430 = mysql_fetch_row($q430)) {
		$drzaver .= "<option value=\"$r430[0]\"";
	 	if ($r430[0]==$drzavar) { $drzaver  .= " SELECTED";  }
		$drzaver .= ">$r430[1]</option>\n";
		$drzavlj .= "<option value=\"$r430[0]\"";
	 	if ($r430[0]==mysql_result($q400,0,16)) { $drzavlj  .= " SELECTED";  }
		$drzavlj .= ">$r430[1]</option>\n";
	}
	
	
	// Spisak nacionalnosti
	$q440 = myquery("select id,naziv from nacionalnost order by naziv");
	$nacion="<option></option>";
	while ($r440 = mysql_fetch_row($q440)) {
		$nacion .= "<option value=\"$r440[0]\"";
	 	if ($r440[0]==mysql_result($q400,0,17)) { $nacion  .= " SELECTED";  }
		$nacion .= ">$r440[1]</option>\n";
	}
	
	// Spol
	if (mysql_result($q400,0,11)=="M") $muskir = "CHECKED"; else $muskir="";
	if (mysql_result($q400,0,11)=="Z") $zenskir = "CHECKED"; else $zenskir="";
	
	// Pripadnik borackih kategorija
	if (mysql_result($q400,0,18)==1) $boracke = "CHECKED"; else $boracke="";
?>

<link rel="stylesheet" href="css/libs/hr.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/libs/validator/validationEngine.jquery.css" type="text/css"/>
<link rel="stylesheet" href="css/libs/ui.all.css" type="text/css" media="screen" />


<br><br>

<ul id="tabs">
	<li class="tab1"><a href="#" title="Korak1">1. Opšti podaci</a></li>
	<li class="tab2"><a href="#" title="Korak2">2. Kontakt informacije</a></li>
    <li class="tab3"><a href="#" title="Korak3">3. Radno iskustvo</a></li>
    <li class="tab4"><a href="#" title="Korak4">4. Obrazovanje</a></li>
    <li class="tab5"><a href="#" title="Korak5">5. Usavršavanje</a></li>
    <li class="tab6"><a href="#" title="Korak6">6. Naučni i stručni radovi</a></li>    
    <li class="tab7"><a href="#" title="Korak7">7. Mentorstvo</a></li> 
    <li class="tab8"><a href="#" title="Korak8">8. Izdate publikacije</a></li> 
    <li class="tab9"><a href="#" title="Korak9">9. Nagrade/Priznanja</a></li> 
    <li class="tab0"><a href="#" title="Korak0">10. Lične vjestine/kompetencije</a></li> 
</ul>
<!-- 
<div style="float:right; padding-right:30px;padding-top:10px;">
	<button type="submit"><IMG SRC="images/32x32/spasi.png" ALIGN="absmiddle">&nbsp;&nbsp;Spasi sve unose</button>
</div>
 -->
<form id="hrforma" class="formular" method="post" action="">
<div id="content"> 
  <?
   // funkcija za umotavanje sifrarnika u <option> - treba za includove
  	function getSifrarnikData($tabela, $selectid =0) {
		$q1 = myquery("select id,naziv from $tabela");
		$zavratiti="<option>n/a</option>";
		while ($r1 = mysql_fetch_row($q1)) { 
			$zavratiti .= "<option";
		 	if ($r1[0]==$selectid && $selectid!=0) { $zavratiti  .= " SELECTED"; }
			$zavratiti .= " value=".$r1[0].">".$r1[1]."</option>\n";
		}
		return $zavratiti;
  	}
  
  	// Pojedini tabovi odvojeni radi preglednosti
  	include ("common/profil/hr_moduli/hr_opstipodaci.php");
  	include ("common/profil/hr_moduli/hr_kontaktinfo.php");
  	include ("common/profil/hr_moduli/hr_radnoiskustvo.php");
  	include ("common/profil/hr_moduli/hr_obrazovanje.php");
  	include ("common/profil/hr_moduli/hr_usavrsavanje.php");
  	include ("common/profil/hr_moduli/hr_naucniradovi.php");
  	include ("common/profil/hr_moduli/hr_mentorstvo.php");
  	include ("common/profil/hr_moduli/hr_publikacije.php");
  	include ("common/profil/hr_moduli/hr_nagrade.php");
  	include ("common/profil/hr_moduli/hr_kompetencije.php");
  ?>
  <br><br>
  <span id="dodatni_info" >
	  Molim vas pratite korake 1-10 i ispunite podatke, pohranjivanje podataka se nalazi na koraku 10.<br><br>
	  <input type="button" class="dalje" id="next" value="Iduci korak >>" />
  </span>
</div>
</form>
<br>
<b>VAŽNO: Ukoliko nedostaje neka opcija (npr. vas maternji jezik) kontaktirajte administratora !</b>

<script src="js/libs/jquery-1.6.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/libs/jquery.validationEngine-hr.js" ></script>
<script src="js/libs/jquery.validationEngine.js" ></script>
<script src="js/libs/jquery-ui.min.js" ></script>

<script>
$(document).ready(function() {
	$("#content div").hide();
	$("#dodatni_info").show();
	$("#tabs li:first").attr("id","trenutni"); 
	$("#content div:first").fadeIn(); 
    $('#tabs a').click(function(e) {
    	$("#dodatni_info").show(); 
        e.preventDefault();        
        $("#content div").hide(); 
        $("#tabs li").attr("id",""); 
        $(this).parent().attr("id","trenutni"); 
        $('#' + $(this).attr('title')).fadeIn();
        if (korak==10 || $(this).attr('title')[5] ==0) { 
            korak=0;
            $("#dodatni_info").hide();
        } 
        korak=$(this).attr('title')[5];
    });

    $('.dalje').click(function(e) {
        e.preventDefault();     
        $("#dodatni_info").show();   
        $("#content div").hide(); 
        $("#tabs li").attr("id",""); 

        korak++;
        if (korak==10 ) { 
            korak=0;
           $("#dodatni_info").hide();
        }
        $('#Korak'+korak).fadeIn();
        $(".tab"+korak).attr("id","trenutni");   
    });

    $('.evidentiraj_usavrsavanje').click(function(e) {
        var du=$("#datum_usavrsavanja").val();
        var nu=$("#naziv_usavrsavanja").val();
        var ni=$("#naziv_institucije").val();
        var kval=$("#kvalifikacija").val();
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 2, 
        													datum_usavrsavanja: du, 
        													naziv_usavrsavanja: nu, 
        													naziv_institucije: ni,
        													kvalifikacija: kval },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#tusavrsavanje').append('<tr><td>'+du+'</td><td>'+nu+'</td><td>'+ni+'</td><td>'+kval+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });



    $('.evidentiraj_rad').click(function(e) {
        var du=$("#datum_rada").val();
        var nu=$("#naziv_rada").val();
        var ni=$("#naziv_casopisa").val();
        var kval=$("#naziv_izdavaca").val();
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 3, 
													    		datum_rada: du, 
													    		naziv_rada: nu, 
													    		naziv_casopisa: ni,
													    		naziv_izdavaca: kval },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#trad').append('<tr><td>'+du+'</td><td>'+nu+'</td><td>'+ni+'</td><td>'+kval+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });


    $('.evidentiraj_mentorstvo').click(function(e) {
        var du=$("#datum_mentorstva").val();
        var nu=$("#ime_kandidata").val();
        var ni=$("#naziv_teme").val();
        var kval=$("#mfakultet").val();
        var mment=$("#mmentorstvo").val();

        var mf=$("#mfakultet option:selected").text();
        var mm=$("#mmentorstvo option:selected").text();
        
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 4, 
											    		datum_mentorstva: du, 
											    		ime_kandidata: nu, 
											    		naziv_teme: ni,
											    		mfakultet: kval,
											    		mmentorstvo: mment
    		 },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#tmentorstvo').append('<tr><td>'+du+'</td><td>'+nu+'</td><td>'+ni+'</td><td>'+mf+'</td><td>'+mm+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });

    $('.evidentiraj_publikaciju').click(function(e) {
        var du=$("#datum_publikacije").val();
        var nu=$("#naziv_publikacije").val();
        var ni=$("#naziv_ci").val();
        var kval=$("#vrsta_publikacije").val();
        var kval2=$("#vrsta_publikacije option:selected").text();
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 5, 
												    		datum_publikacije: du, 
												    		naziv_publikacije: nu, 
												    		naziv_ci: ni,
												    		vrsta_publikacije: kval },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#tpublikacije').append('<tr><td>'+du+'</td><td>'+nu+'</td><td>'+ni+'</td><td>'+kval2+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });


    $('.evidentiraj_nagradu').click(function(e) {
        var du=$("#datum_nagrade").val();
        var nu=$("#naziv_nagrade").val();
        var ni=$("#opis_nagrade").val();
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 6, 
	    		datum_nagrade: du, 
	    		naziv_nagrade: nu, 
	    		opis_nagrade: ni
    		 },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#tnagrade').append('<tr><td>'+du+'</td><td>'+nu+'</td><td>'+ni+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });

    $('.evidentiraj_jezik').click(function(e) {
        var du=$("#jezik").val();
        var nu=$("#razumjevanje").val();
        var ni=$("#govor").val();
        var pi=$("#pisanje").val();
        var du1=$("#jezik option:selected").text();
        var nu1=$("#razumjevanje option:selected").text();;
        var ni1=$("#govor option:selected").text();
        var pi1=$("#pisanje option:selected").text();
    	$.post("?sta=common/profil&akcija=ljudskiresursi", { save: 7, 
								    		jezik: du, 
								    		razumjevanje: nu, 
								    		govor: ni,
								    		pisanje: pi
    		 },
    		function(data) {
    		 alert("Podaci uspjesno evidentirani!");
    		 $('#tjezik').append('<tr><td>'+du1+'</td><td>'+nu1+'</td><td>'+ni1+'</td><td>'+pi1+'</td><td><img src="images/16x16/brisanje.png" /></td></tr>');
    	});	
    });
    
    
    var korak=1;
    $("#poc").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#kraj").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_rada").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_publikacije").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_mentorstva").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_rada").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_usavrsavanja").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    $("#datum_nagrade").datepicker({ dateFormat: 'dd.mm.yy', yearRange: '-80:0'  });
    jQuery("#hrforma").validationEngine();
})();



</script>
