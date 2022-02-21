<?php



//----------------------------------------
// Masovno kreiranje tokena za ankete
//----------------------------------------

function admin_misc_anketa_tokeni() {

	if ($_POST['akcija']=="anketa_tokeni") {
		
		$broj_tokena = intval($_REQUEST['broj']);
		$anketa = intval($_REQUEST['anketa']);
		
		$studij = intval($_REQUEST['studij']);
		if ($studij!=0) {
			$godina = intval($_REQUEST['godina']);
			$dodaj_studij = "AND s.id=$studij";
		} else {
			$godina = 1;
			$dodaj_studij = "AND s.tipstudija=2"; // ETF specifično
		}
		$semestar = $godina*2 - intval($_REQUEST['zimaljeto']);
		
		// Aktuelna akademska godina
		$q20 = db_query("select id from akademska_godina where aktuelna=1");
		$ag = db_result($q20,0,0);
		
		// Subakcija za printanje tokena - redirektujem na poseban modul
		if ($_REQUEST['printaj'] === " Printaj tokene ") {
			?>
			<script language="JavaScript">
				location.href='?sta=admin/printaj_tokene&anketa=<?=$anketa?>&ag=<?=$ag?>&studij=<?=$studij?>&semestar=<?=$semestar?>';
			</script>
			<?
			return;
		}
		
		
		function suglasnik() {
	//		$suglasnici=array('b','c','č','ć','d','dž','f','g','h','j','k','l','lj','m','n','nj','p','q','r','s','š','t','v','w','x','y','z','ž');
			$suglasnici=array('b','c','d','f','g','h','j','k','l','lj','m','n','nj','p','q','r','s','t','v','w','x','y','z');
			return $suglasnici[rand(0,count($suglasnici)-1)];
		}
		function samoglasnik() {
			$samoglasnici=array('a','e','i','o','u','y','r');
			return $samoglasnici[rand(0,count($samoglasnici)-1)];
		}
		
		// Upit za predmete
		$q30 = db_query("select distinct p.id, p.naziv, pk.id from predmet as p, ponudakursa as pk, studij as s where pk.predmet=p.id and pk.studij=s.id and pk.semestar=$semestar and pk.akademska_godina=$ag $dodaj_studij");
		while ($r30 = db_fetch_row($q30)) {
			$predmet = $r30[0];
			$naziv_predmeta = $r30[1];
			$ponudakursa = $r30[2];
			print "Predmet: <b>$naziv_predmeta</b><br />\n";
			if (isset($_REQUEST['massinput'])) {
				$k = strpos($_REQUEST['massinput'], $naziv_predmeta);
				if ($k !== false) {
					$broj_tokena = intval(substr($_REQUEST['massinput'], $k + strlen($naziv_predmeta) + 1, 10));
				} else $broj_tokena = 0;
				print "-Broj studenata na predmetu: <b>$broj_tokena</b><br />\n";
			} else if (intval($_REQUEST['broj']) == 0) {
				$q35 = db_query("select count(*) from student_predmet where predmet=$ponudakursa");
				$broj_tokena = db_result($q35,0,0);
				print "Broj studenata na predmetu: <b>$broj_tokena</b><br />\n";
			}
			
			for ($i=0; $i<$broj_tokena; $i++) {
				// Generator tokena
				$token = suglasnik().samoglasnik().suglasnik().samoglasnik().suglasnik().samoglasnik().suglasnik().samoglasnik();
				// Cenzura
				if (strstr($token, "jebe")) { $i--; continue; }
				
				// Da li već postoji?
				$q40 = db_query("select count(*) from anketa_rezultat where unique_id='$token'");
				if (db_result($q40,0,0)>0) {
					print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$token - duplikat!!<br/>\n";
					$i--;
					continue;
				}
				
				// Ubacujemo u tabelu
				$q50 = db_query("insert into anketa_rezultat set anketa=$anketa, zavrsena='N', predmet=$predmet, unique_id='$token', akademska_godina=$ag, studij=$studij, semestar=$semestar");
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$token<br/>\n";
			}
		}
		
	} else {
		
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="anketa_tokeni">
		Anketa: <select name="anketa">
			<?
			$q5 = db_query("select aa.id, aa.naziv, ag.naziv from anketa_anketa as aa, akademska_godina as ag where aa.akademska_godina=ag.id order by aa.id desc"); // Daj ankete počevši od posljenje kreirane
			while ($r5 = db_fetch_row($q5)) {
				?>
				<option value="<?=$r5[0]?>"><?=$r5[1]?> (<?=$r5[2]?>)</option>
				<?
			}
			?>
		</select><br />
		
		Godina studija: <input type="text" name="godina" size="5"><br/>
		Studij: <select name="studij"><option value="0">Svi (PGS)</option>
			<?
			$q10 = db_query("select id, naziv from studij where moguc_upis=1 order by tipstudija,naziv");
			while ($r10 = db_fetch_row($q10)) {
				?>
				<option value="<?=$r10[0]?>"><?=$r10[1]?></option>
				<?
			}
			?>
		</select><br />
		Semestar: <select name="zimaljeto"><option value="1">Zimski</option><option value="0">Ljetnji</option></select><br />
		Broj tokena: <input type="text" name="broj" size="5"><br/>
		Broj studenata po predmetu:<br>
		<textarea name="massinput" cols="50" rows="10"><?
			if (strlen($_POST['nazad'])>1) print $_POST['massinput'];
			?></textarea><br/>
		
		<input type="submit" value=" Generiši tokene za popunjavanje ankete ">
		<input type="submit" name="printaj" value=" Printaj tokene ">
		</form>
		<?
		
	}
}