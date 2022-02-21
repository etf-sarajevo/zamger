<?php


//----------------------------------------
// Masovno kreiranje logina
//----------------------------------------

function admin_misc_logini() {
	global $conf_ldap_domain;

	$f = intval($_POST['fakatradi']);
	
	/*	// Tražimo ovaj login na LDAPu...
		$ds = ldap_connect($conf_ldap_server);
		ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
		if (!ldap_bind($ds)) {
			zamgerlog("Ne mogu se spojiti na LDAP server",3); // 3 - greska
			niceerror("Ne mogu se spojiti na LDAP server");
			return;
		}
	
	
		print "Spisak studenata kojima fale logini:<br/>\n<ul>";
	
		$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa from osoba as o, student_studij as ss, akademska_godina as ag where ss.student=o.id and ss.akademska_godina=ag.id and ag.aktuelna=1 and ss.semestar=1 and (select count(*) from auth as a where a.id=o.id)=0 order by o.prezime, o.ime");
		while ($r10 = db_fetch_row($q10)) {
			print "<li>$r10[2] $r10[1] $r10[3] - ";
	
	
			// predloženi login
			$suggest_login = gen_ldap_uid($r10[0]);
			print "login <b>$suggest_login</b> - ";
	
	
			$sr = ldap_search($ds, "", "uid=$suggest_login", array() /* just dn *//* );
		if (!$sr) {
			print "ldap_search() nije uspio.";
		}
		$results = ldap_get_entries($ds, $sr);
		if ($results['count'] < 1) {
			print "<font color=\"red\">nema na LDAP serveru</font></li>";
			// Nastavljamo dalje sa edit akcijom kako bi studentska mogla popraviti podatke

		} else if ($f==1) {
			print "dodan u bazu<br/>";
			// Dodajemo login, ako nije podešen
			$q111 = db_query("insert into auth set id=$r10[0], login='$suggest_login', aktivan=1");

			// Generišemo email adresu ako nije podešena
			$q115 = db_query("select email from osoba where id=$r10[0]");
			if (db_result($q115,0,0) == "") {
				$email = $suggest_login.$conf_ldap_domain;
				$q114 = db_query("update osoba set email='$email' where id=$r10[0]");
			}
		} else {
			print "ok<br/>";
		}

	}
	print "</ul>\n";*/
	
	// Za koju akademsku godinu?
	$q5 = db_query("select id from akademska_godina order by id desc limit 1");
	$ag = db_result($q5,0,0);
	
	$bilo=array();
	$count=array();
	$trans = array("č"=>"c", "ć"=>"c", "đ"=>"d", "š"=>"s", "ž"=>"z", "Č"=>"C", "Ć"=>"C", "Đ"=>"D", "Š"=>"S", "Ž"=>"Z");
	$q10 = db_query("select o.id, o.ime, o.prezime, o.brindexa, o.jmbg, ss.akademska_godina, o.imeoca from osoba as o, student_studij as ss where ss.student=o.id order by ss.akademska_godina, o.prezime, o.ime");
	print "<table><tr><td><b>Zamger ID</b></td><td><b>Ime</b></td><td><b>Prezime</b></td><td><b>Ime oca</b></td><td><b>Novi login</b></td><td><b>Broj indexa</b></td><td><b>Stari login</b></td><td><b>JMBG</b></td></tr>\n";
	while ($r10 = db_fetch_row($q10)) {
		if ($bilo[$r10[0]]) continue;
		$bilo[$r10[0]]=1;
		$ime = preg_replace("/\W/", "", strtolower(strtr($r10[1], $trans)));
		$prezime = preg_replace("/\W/", "", strtolower(strtr($r10[2], $trans)));
		$login = substr($ime,0,1).substr($prezime,0,9);
		$count[$login]++;
		if ($count[$login]>9) {
			$login = substr($login,0,9).$count[$login];
		} else {
			$login = $login.$count[$login];
		}
//		$count[$login] = "0".$count[$login];
		$q15 = db_query("select login from auth where id=$r10[0]");
		if (db_num_rows($q15) > 0) {
			if (db_result($q15,0,0) != $login)
				$count[db_result($q15,0,0)]++;
			continue;
		}
		if ($r10[5]==$ag) {
			if ($f==1) {
				/*				$q30 = db_query("select email from osoba where id=$r10[0]");
								if (db_result($q30,0,0)=="") {
									$adresa = $login.$conf_ldap_domain;
									$q40 = db_query("update osoba set email='$adresa' where id=$r10[0]");
									print "update osoba set email='$adresa' where id=$r10[0]";
								}*/
				$q30 = db_query("select count(*) from email where osoba=$r10[0]");
				if (db_result($q30,0,0)==0) {
					$adresa = $login.$conf_ldap_domain;
					$q40 = db_query("insert into email set osoba=$r10[0], adresa='$adresa', sistemska=1");
				}
				$q19 = db_query("delete from auth where id=$r10[0] and login='$login'");
				$q20 = db_query("insert into auth set id=$r10[0], login='$login', password='', admin=0, aktivan=1");
				//print "insert into auth set id=$r10[0], login='$login', password='', admin=0, aktivan=1<br />\n";
				
			} else {
				print "<tr><td>$r10[0]</td><td>$r10[1]</td><td>$r10[2]</td><td>$r10[6]</td><td>$login</td><td>$r10[3]</td><td>".gen_ldap_uid($r10[0])."<td>$r10[4]</td></tr>\n";
			}
		}
	}
	print "</table>\n";
	
	
	if ($f==0) {
		?>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="1">
		<input type="hidden" name="akcija" value="logini">
		<input type="submit" value=" Fakat radi ">
		</form>
		<?
	}
}