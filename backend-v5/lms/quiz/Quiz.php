<?php

// Modul: lms/quiz
// Klasa: Quiz
// Opis: kvizovi


require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/CourseUnit.php");

class Quiz {
	public $id;
	public $name, $courseUnitId, $academicYearId, $groupId, $scoringElementId, $timeBegin, $timeEnd, $active, $ipAddressRanges, $passPoints, $nrQuestions, $duration;
	// $zclassId -- dodati link na čas umjesto kako je sada, link sa časa na kviz
	public $courseUnit;
	
	public static function fromId($id) {
		$q10 = DB::query("select naziv, predmet, akademska_godina, labgrupa, UNIX_TIMESTAMP(vrijeme_pocetak), UNIX_TIMESTAMP(vrijeme_kraj), aktivan, ip_adrese, prolaz_bodova, broj_pitanja, trajanje_kviza from kviz where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("nepoznat kviz");
		}
		$q = new Quiz;
		$q->id = $id;
		$q->name = mysql_result($q10,0,0);
		$q->courseUnitId = mysql_result($q10,0,1);
		$q->academicYearId = mysql_result($q10,0,2);
		$q->groupId = mysql_result($q10,0,3);
		$q->timeBegin = mysql_result($q10,0,4);
		$q->timeEnd = mysql_result($q10,0,5);
		if (mysql_result($q10,0,6) == 1) $q->active = true; else $q->active = false;
		$q->ipAddressRanges = mysql_result($q10,0,7);
		$q->passPoints = mysql_result($q10,0,8);
		$q->nrQuestions = mysql_result($q10,0,9);
		$q->duration = mysql_result($q10,0,10);
		
		$q->courseUnit = 0;
		
		return $q;
	}
	
	// Gets no more than $limit quizzes, ordered by deadline descending
	// Only list active quizzes in groups that student is in
	// Limit 0 means no limit
	public static function getLatestForStudent($student, $limit = 0) {
		if ($limit > 0) $sql_limit = "LIMIT $limit"; else $sql_limit = "";
		$q10 = DB::query("
SELECT k.id, k.naziv, k.predmet, k.akademska_godina, k.labgrupa, UNIX_TIMESTAMP(k.vrijeme_pocetak), UNIX_TIMESTAMP(k.vrijeme_kraj), k.ip_adrese, k.prolaz_bodova, k.broj_pitanja, k.trajanje_kviza, p.naziv FROM kviz as k, student_predmet as sp, ponudakursa as pk, predmet as p 
WHERE sp.student=$student AND sp.predmet=pk.id AND pk.predmet=k.predmet AND pk.predmet=p.id AND pk.akademska_godina=k.akademska_godina AND k.vrijeme_pocetak<NOW() AND k.vrijeme_kraj>NOW() AND k.aktivan=1
ORDER BY k.vrijeme_pocetak DESC
$sql_limit");
		$quizes = array();
		while ($r10 = mysql_fetch_row($q10)) {
			// Skip if student has passing grade (optimize?)
			$q15a = DB::query("select count(*) from konacna_ocjena where student=$student and predmet=$r10[2] and ocjena>=6");
			if (mysql_result($q15a,0,0)>0) continue;
			// Skip if group defined and student not a member
			if ($r10[4] != 0) {
				// If defined, we can assume that module lms/attendance is installed
				require_once(Config::$backend_path."lms/attendance/Group.php");
				$g = new Group;
				$g->id = $r10[4];
				if (!$g->isMember($student)) continue; // Not a member
			}

			$q = new Quiz;
			$q->id = $r10[0];
			$q->name = $r10[1];
			$q->courseUnitId = $r10[2];
			$q->academicYearId = $r10[3];
			$q->groupId = $r10[4];
			$q->timeBegin = $r10[5];
			$q->timeEnd = $r10[6];
			$q->active = true;
			$q->ipAddressRanges = $r10[7];
			$q->passPoints = $r10[8];
			$q->nrQuestions = $r10[9];
			$q->duration = $r10[10];
			
			$q->courseUnit = new CourseUnit;
			$q->courseUnit->id = $r10[2];
			$q->courseUnit->name = $r10[11];
			// TODO dodati ostalo
		
			array_push($quizes, $q);
		}
		
		return $quizes;
	}
	
	public static function isIpInRange($ipAddress, $ipRanges) {
	
		// Hack za činjenicu da je long tip u PHPu signed
		// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
		function ip2float($ip) {
			return (float)sprintf("%u",ip2long($ip));
		}

		$blokovi = explode(",", $ipRanges);
		foreach ($blokovi as $blok) {
			if (strstr($blok, "/")) { // adresa u CIDR formatu
				// Npr. 192.168.0.1/24
				// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
				list ($baza, $maska) = explode("/", $blok);
				$moja_f = ip2float($ipAddress);
				$baza_f = ip2float($baza);
				$netmask_dec = bindec( str_pad('', $maska, '1') . str_pad('', 32-$maska, '0') );
				$wildcard_dec = pow(2, (32-$maska)) - 1;
				$netmask_dec = ~ $wildcard_dec; 
				if (($moja_f & $netmask_dec) == ($baza_f & $netmask_dec))
					return true;
			}

			else if (strstr($blok, "-")) { // Raspon sa crticom
				// Npr. 10.0.0.1 - 10.0.0.15
				list ($prva, $zadnja) = explode("-", $blok);
				$moja_f = ip2float($ipAddress);
				$prva_f = ip2float($prva);
				$zadnja_f = ip2float($zadnja);
				if (($moja_f >= $prva_f) && ($moja_f <= $zadnja_f))
					return true;

			} else { // Pojedinačna adresa
				if ($ipAddress == $blok)
					return true;
			}
		}
		return false;
	}
}

?>
