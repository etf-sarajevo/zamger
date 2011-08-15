<?php

// Modul: core/file
// Klasa: ZFile
// Opis: jedna datoteka u folderu (postoji podrška za revizije)
// naziv klase je ovakav zato što je File rezervisana riječ u PHPu

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."lms/file/FileRevision.php");

// FIXME: trenutno ne postoji tabela folder :(
// Polje $folderId se u tabeli file zove "projekat"


class ZFile {
	public $id;
	public $filename, $nrRevisions, $authorId, $folderId;

	public $author;
	public $lastRevision;

	public static function fromId($id) { // ID fajla je jednak IDu prve revizije (fixme)
		$q10 = DB::query("select f.filename, f.osoba, f.projekat, o.ime, o.prezime, o.brindexa from projekat_file as f, osoba as o where f.id=$id and f.osoba=o.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("file not found");
		}

		$f = new ZFile;
		$f->id = $id; // FIXME id fajla i prve revizije su isti
		$f->filename = mysql_result($q10,0,0);
		$f->authorId = mysql_result($q10,0,1);
		$f->folderId = mysql_result($q10,0,2);

		// Ovo je neefikasna metoda za dobijanje broja revizija i zadnje revizije, ali to je posljedica lošeg dizajna baze
		$q20 = DB::query("select count(*) from projekat_file where file=$id");
		$f->nrRevisions = mysql_result($q20,0,0) + 1;
		$f->lastRevision = 0; // FIXME ovo je da prevarimo getRevision da bi odradio svoj posao
		$f->lastRevision = $f->getRevision($f->nrRevisions);

		$f->author = new Person;
		$f->author->id = $f->authorId;
		$f->author->name = mysql_result($q10,0,3);
		$f->author->surname = mysql_result($q10,0,4);
		$f->author->studentIdNr = mysql_result($q10,0,5);

		return $f;
	}

	public function getRevision($revisionNr) {
		if ($revisionNr == $this->nrRevisions && $this->lastRevision != 0) return $this->lastRevision;
		if ($revisionNr > $this->nrRevisions || $revisionNr < 1) {
			throw new Exception("no such revision");
		}

		if ($revisionNr == 1)
			$q10 = DB::query("select f.id, UNIX_TIMESTAMP(f.vrijeme), f.osoba, o.ime, o.prezime, o.brindexa from projekat_file as f, osoba as o where f.id=".$this->id." and f.revizija=$revisionNr and f.osoba=o.id");
		else
			$q10 = DB::query("select f.id, UNIX_TIMESTAMP(f.vrijeme), f.osoba, o.ime, o.prezime, o.brindexa from projekat_file as f, osoba as o where f.file=".$this->id." and f.revizija=$revisionNr and f.osoba=o.id");

		if (mysql_num_rows($q10)<1) {
			// shouldn't happen
			throw new Exception("revision not found");
		}

		$fr = new FileRevision;
		$fr->id = mysql_result($q10,0,0);
		$fr->revisionNumber = $revisionNr;
		$fr->time = mysql_result($q10,0,1);
		$fr->authorId = mysql_result($q10,0,2);
		$fr->file = $this;

		$fr->author = new Person;
		$fr->author->id = $fr->authorId;
		$fr->author->name = mysql_result($q10,0,3);
		$fr->author->surname = mysql_result($q10,0,4);
		$fr->author->studentIdNr = mysql_result($q10,0,5);
		
		return $fr;
	}

	// Adds a new revision to file with specified content
	public function addRevision($authorId, $fileContent) {
		$revision = $this->addRevisionWithoutContent($authorId);

		if (! file_put_contents($revision->getPath(), $fileContent) ) {
			$this->dropRevision();
			throw new Exception("failed to write data to file");
		}
		chmod($revision->getPath(), 0777);

		$revision->updateDiff();

		return $revision;
	}

	// Adds a new revision to file *without* content (such revision is invalid and can't be downloaded)
	// It's assumed that caller will create the required file in some other way and call $revision->updateDiff()
	public function addRevisionWithoutContent($authorId) {
		// Napravi sve potrebne foldere
		// TODO ne vidim svrhu pravljenja foldera za korisnike!!!
		$uploadPath = Config::$backend_file_path . "/projekti/fajlovi/" . $this->folderId . "/" . $authorId . "/";
		if (!file_exists($uploadPath)) mkdir ($uploadPath,0777, true);

		$uploadPath .= $this->filename . "/";
		if (!file_exists($uploadPath)) mkdir ($uploadPath,0777, true);

		$uploadPath .= "v" . ($this->nrRevisions+1) . "/";
		if (!file_exists($uploadPath)) mkdir ($uploadPath,0777, true);

		// Odredjujemo ID
		$q10 = DB::query("select id from projekat_file order by id desc limit 1");
		if (mysql_num_rows($q10)==0)
			$newId = 1;
		else
			$newId = mysql_result($q10,0,0)+1;

		$fr = new FileRevision;
		$fr->id = $newId;
		$fr->revisionNumber = $this->nrRevisions + 1;
		$fr->fileId = $this->id;
		$fr->authorId = $authorId;
		$fr->file = $this;

		$q10 = DB::query("INSERT INTO projekat_file set id=$newId, filename='".$this->filename."', vrijeme=NOW(), revizija=".$fr->revisionNumber.", osoba=$authorId, projekat=".$this->folderId.", file=".$this->id);

		// Odredjujemo vrijeme
		$q20 = DB::query("select UNIX_TIMESTAMP(vrijeme) from projekat_file where id=$newId");
		$fr->time = mysql_result($q20,0,0);

		$this->nrRevisions++;
		$this->lastRevision = $fr;
		if ($this->id == 0) // Fix za postavljanje IDa na 0 (Folder.php:67)
			$this->id = $newId;

		return $fr;
	}

	public function delete() {
		for ($i=1; $i<=$this->nrRevisions; $i++)
			$this->dropRevision();
	}

	// Undo last revision
	public function dropRevision() {
		$q10 = DB::query("delete from projekat_file_diff where file=".$this->lastRevision->id);
		$q10 = DB::query("delete from projekat_file where id=".$this->lastRevision->id);

		if ($this->nrRevisions == 1) return;
		$this->nrRevisions--;
		$this->lastRevision = $this->getRevision($this->nrRevisions);
	}
}

?>