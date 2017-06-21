<?php

// Modul: sis/certificate
// Klasa: Certificate
// Opis: zahtjevi studenata za ovjerena uvjerenja/potvrde



abstract class CertificateStatus {
	const Unknown = 0;
	const NewRequest = 1;
	const Confirmed = 2;
}

class Certificate {
	public $id;
	public $student, $CertificateType, $CertificatePurpose, $datetime, $status;
	// $zclassId -- dodati link na čas umjesto kako je sada, link sa časa na kviz
	
	public static function fromId($id) {
		$cert = DB::query_assoc("SELECT id, student, tip_potvrde CertificateType, svrha_potvrde CertificatePurpose, UNIX_TIMESTAMP(datum_zahtjeva) datetime, status FROM zahtjev_za_potvrdu WHERE id=$id");
		if (!$cert) throw new Exception("Unknown certificate request $id", "404");
		
		$cert = Util::array_to_class($cert, "Certificate");
		$cert->student = new UnresolvedClass("Person", $cert->student, $cert->student);
		return $cert;
	}
	
	// Cancel certificate request
	public function cancel() {
		DB::query("DELETE FROM zahtjev_za_potvrdu WHERE id=" . $this->id);
		return (DB::affected_rows() > 0);
	}
	
	// Set certificate request status
	public function setStatus($status) {
		if ($status < 1 || $status > 2)
			throw new Exception("Invalid certificate request status $status", "400");
		DB::query("UPDATE zahtjev_za_potvrdu SET status=$status WHERE id=" . $this->id);
		return (DB::affected_rows() > 0);
	}
	
	// List of certificate requests for student
	public static function forStudent($studentId) {
		$certs = DB::query_table("SELECT id, student, tip_potvrde CertificateType, svrha_potvrde CertificatePurpose, UNIX_TIMESTAMP(datum_zahtjeva) datetime, status FROM zahtjev_za_potvrdu WHERE student=$studentId");
		foreach($certs as &$cert) {
			$cert = Util::array_to_class($cert, "Certificate");
			$cert->student = new UnresolvedClass("Person", $cert->student, $cert->student);
		}
		return $certs;
	}
	
	// Request new certificate
	public static function request($studentId, $purposeId, $typeId) {
		// Check if type or purpose are valid
		// TODO refactor into CertificatePurpose & CertificateType classes
		$purposeValid = DB::get("SELECT COUNT(*) FROM svrha_potvrde WHERE id=$purposeId");
		if ($purposeValid == 0)
			throw new Exception("Invalid certificate purpose $purposeId", "400");
		$typeValid = DB::get("SELECT COUNT(*) FROM tip_potvrde WHERE id=$typeId");
		if ($typeValid == 0)
			throw new Exception("Invalid certificate type $typeId", "400");
	
		DB::query("INSERT INTO zahtjev_za_potvrdu SET student=$studentId, tip_potvrde=$typeId, svrha_potvrde=$purposeId, datum_zahtjeva=NOW(), status=" . CertificateStatus::NewRequest);
		return Certificate::fromId(DB::insert_id()); // Optimize
	}
	
	
	// List of purposes and types
	// TODO refactor into CertificatePurpose & CertificateType classes
	public static function purposesTypes() {
		$result = new stdClass;
		$result->purposes = DB::query_vassoc("SELECT id, naziv FROM svrha_potvrde ORDER BY id");
		$result->types = DB::query_vassoc("SELECT id, naziv FROM tip_potvrde ORDER BY id");
		return $result;
	}
}

?>
