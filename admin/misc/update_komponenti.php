<?php


//----------------------------------------
// Masovni update komponenti
//----------------------------------------


function admin_misc_update_komponenti() {
	require_once("lib/student_predmet.php"); // update_komponente
	// TODO prebaciti na api
	// Ovaj sav kod je obsolete jer treba koristiti aktivnosti umjesto komponenti
	
	if ($_POST['akcija']=="update_komponenti" && intval($_POST['stage']) == 2) {
		
		$pk = intval($_REQUEST['pk']);
		$komponenta = intval($_REQUEST['komponenta']);
		$f = intval($_POST['fakatradi']);
		
		$q10 = db_query("select p.naziv, s.naziv, pk.semestar, pk.obavezan, ag.naziv, agp.tippredmeta, tp.naziv from ponudakursa as pk, predmet as p, studij as s, akademska_godina as ag, akademska_godina_predmet as agp, tippredmeta as tp where pk.id=$pk and pk.predmet=p.id and pk.studij=s.id and pk.akademska_godina=ag.id and agp.predmet=p.id and agp.akademska_godina=ag.id and agp.tippredmeta=tp.id");
		if (db_num_rows($q10)<1) {
			niceerror("Nepostojeća ponudakursa");
			return;
		}
		print "Predmet: ".db_result($q10,0,0)."<br>Studij: ".db_result($q10,0,1)."<br>Semestar: ".db_result($q10,0,2)."<br>Obavezan: ".db_result($q10,0,3)."<br>Akademska godina: ".db_result($q10,0,4)."<br>Tip predmeta: ".db_result($q10,0,6)."<br><br>\n";
		$tippredmeta = db_result($q10,0,5);
		
		if ($komponenta != 0) {
			$q20 = db_query("select naziv from komponenta where id=$komponenta");
			if (db_num_rows($q20)<1) {
				niceerror("Nepostojeća komponenta");
				return;
			}
			print "<b>Komponenta:  ".db_result($q20,0,0)."</b><br><br>\n";
			
			$q30 = db_query("select count(*) from tippredmeta_komponenta where tippredmeta=$tippredmeta and komponenta=$komponenta");
			if (db_result($q30,0,0)<1) {
				niceerror("Komponenta nije pridružena ovom tipu predmeta");
				return;
			}
		} else {
			print "<b>Komponenta:  SVE</b><br><br>\n";
		}
		
		
		$q40 = db_query("select o.id, o.ime, o.prezime, o.brindexa from student_predmet as sp, osoba as o where sp.predmet=$pk and sp.student=o.id");
		if (db_num_rows($q40)<1) {
			niceerror("Nema studenata na ovoj ponudi kursa");
			return;
		}
		if ($f) print "Ažuriram ".db_num_rows($q40)." studenata<br><br>";
		while ($r40 = db_fetch_row($q40)) {
			if ($f) {
				update_komponente($r40[0], $pk, $komponenta);
			} else {
				print "-- $r40[1] $r40[2] ($r40[3])<br>\n";
			}
		}
		
		if (!$f) {
			?>
			<?=genform("POST")?>
			<input type="hidden" name="fakatradi" value="1">
			<input type="hidden" name="akcija" value="update_komponenti">
			<input type="submit" value=" Fakat radi ">
			</form>
			<?
		}
		
	} else if ($_POST['akcija']=="update_komponenti") {
		$predmet = intval($_REQUEST['predmet']);
		$ag = intval($_REQUEST['ag']);
		
		$q110 = db_query("SELECT pk.id, s.naziv, pk.semestar FROM ponudakursa pk, studij s WHERE pk.predmet=$predmet AND pk.akademska_godina=$ag AND pk.studij=s.id");
		$q120 = db_query("SELECT k.id, k.gui_naziv FROM komponenta k, tippredmeta_komponenta tpk, akademska_godina_predmet agp WHERE agp.predmet=$predmet AND agp.akademska_godina=$ag AND agp.tippredmeta=tpk.tippredmeta AND tpk.komponenta=k.id");
		
		if (db_num_rows($q110)==0 || db_num_rows($q120)==0) {
			niceerror("Predmet se nije izvodio u toj godini");
		} else {
			
			?>
			<?=genform("POST")?>
			<input type="hidden" name="akcija" value="update_komponenti">
			<input type="hidden" name="stage" value="2">
			Ponudakursa: <select name="pk"><?
				while (db_fetch3($q110, $pk, $studij, $semestar))
					print "<option value='$pk'>$studij, $semestar. semestar</option>\n";
				?></select><br>
			Komponenta: <select name="komponenta"><?
				while (db_fetch2($q120, $komponenta, $naziv_komponente))
					print "<option value='$komponenta'>$naziv_komponente</option>\n";
				?></select><br>
			<input type="submit" value=" Update komponenti ">
			</form>
			<?
		}
		
		
	} else {
		
		$q130 = db_query("SELECT id, naziv FROM predmet ORDER BY naziv");
		$q140 = db_query("SELECT id, naziv, aktuelna FROM akademska_godina ORDER BY id");
		
		niceerror("Obsolete - ne koristiti dok se ne popravi sa komponenti na aktivnosti");
		
		?>
		<?=genform("POST")?>
		<input type="hidden" name="akcija" value="update_komponenti">
		Predmet: <select name="predmet"><?
			while (db_fetch2($q130, $predmet, $naziv_predmeta))
				print "<option value='$predmet'>$naziv_predmeta</option>\n";
			?></select><br>
		Akademska godina: <select name="ag"><?
			while (db_fetch3($q140, $ag, $naziv_ag, $aktuelna))
				print "<option value='$ag'" . ($aktuelna ? " SELECTED" : "") . ">$naziv_ag</option>\n";
			?></select><br>
		<input type="submit" value=" Izbor komponente ">
		</form>
		<?
		
	}
	
}