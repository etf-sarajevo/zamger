<?php


//----------------------------------------
// Zamijeni grupe
//----------------------------------------

// Dva studenta su zamijenila grupe na svim predmetima na istoj (prvoj) godini, potrebno je naći grupe koje se isto
// zovu i pozvati ispis sa jedne grupe a upis na drugu (što za posljedicu ima gubitak evidencije prisustva)

function admin_misc_zamjena_grupa() {
	// TODO prebaciti na api?

	if ($_POST['akcija']=="zamjena_grupa") {
		$s1 = int_param('s1');
		$s2 = int_param('s2');
		$f = intval($_POST['fakatradi']);
		$ag = db_get("SELECT id FROM akademska_godina WHERE aktuelna=1");
		
		if (param('automatski')) {
			if ($f == 0) {
				$ime_s1 = db_get("SELECT CONCAT(prezime,CONCAT(' ',ime)) FROM osoba WHERE id=$s1");
				$ime_s2 = db_get("SELECT CONCAT(prezime,CONCAT(' ',ime)) FROM osoba WHERE id=$s2");
			}
			
			
			$naziv_grupe_1 = db_get("SELECT l.naziv FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
			$naziv_grupe_2 = db_get("SELECT l.naziv FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
			
			if ($f == 0) print "Prebacujem studenta <b>$ime_s1</b> iz grupe <b>$naziv_grupe_1</b> u grupu <b>$naziv_grupe_2</b><br>";
			
			$grupe1 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
			$grupe2 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
			
			foreach($grupe1 as $id1=>$predmet1) {
				$nova = db_get("SELECT id FROM labgrupa WHERE predmet=$predmet1 AND akademska_godina=$ag AND naziv='$naziv_grupe_2'");
				$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet1");
				
				if (!$nova) {
					niceerror("-- Greška predmet $naziv_predmeta");
				} else {
					if ($f == 0) {
						print "-- Predmet $naziv_predmeta<br>";
					} else {
						ispis_studenta_sa_labgrupe($s1, $id1);
						upis_studenta_na_labgrupu($s1, $nova);
					}
				}
			}
			
			if ($f == 0) print "<br><br>Prebacujem studenta <b>$ime_s2</b> iz grupe <b>$naziv_grupe_2</b> u grupu <b>$naziv_grupe_1</b><br>";
			
			
			foreach($grupe2 as $id2=>$predmet2) {
				$nova = db_get("SELECT id FROM labgrupa WHERE predmet=$predmet2 AND akademska_godina=$ag AND naziv='$naziv_grupe_1'");
				$naziv_predmeta = db_get("SELECT naziv FROM predmet WHERE id=$predmet2");
				
				if (!$nova) {
					niceerror("-- Greška predmet $naziv_predmeta");
				} else {
					if ($f == 0) {
						print "-- Predmet $naziv_predmeta<br>";
					} else {
						ispis_studenta_sa_labgrupe($s2, $id2);
						upis_studenta_na_labgrupu($s2, $nova);
					}
				}
			}
			
			
			if ($f==0) {
				?>
				<?=genform("POST")?>
				<input type="hidden" name="fakatradi" value="1">
				<input type="hidden" name="akcija" value="zamjena_grupa">
				<input type="submit" value=" Fakat radi ">
				</form>
				<?
			} else {
				nicemessage("Zamijenjene grupe");
			}
			
		} else {
			$grupe1 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s1 AND virtualna=0");
			$grupe2 = db_query_vassoc("SELECT l.id, l.predmet FROM labgrupa l, student_labgrupa sl WHERE l.akademska_godina=$ag AND sl.labgrupa=l.id AND sl.student=$s2 AND virtualna=0");
			
			foreach($grupe1 as $id1=>$predmet1) {
				foreach($grupe2 as $id2=>$predmet2) {
					if ($predmet1==$predmet2) {
						ispis_studenta_sa_labgrupe($s1, $id1);
						ispis_studenta_sa_labgrupe($s2, $id2);
						upis_studenta_na_labgrupu($s1, $id2);
						upis_studenta_na_labgrupu($s2, $id1);
						break;
					}
				}
			}
			
			nicemessage("Zamijenjene grupe");
		}
		
	} else {
		
		
		?>
		<p><b>Zamjena grupa</b></p>
		<?=genform("POST")?>
		<input type="hidden" name="fakatradi" value="0">
		<input type="hidden" name="akcija" value="zamjena_grupa">
		Student 1: <input type="text" name="s1"><br>
		Student 2: <input type="text" name="s2"><br>
		<input type="checkbox" name="automatski" CHECKED> Automatski kreirane grupe<br>
		
		<input type="submit" value=" Zamijeni grupe ">
		</form>
		<?
	}
}