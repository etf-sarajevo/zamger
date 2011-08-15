<?php

// Modul: core/file
// Klasa: FileRevision
// Opis: revizija neke datoteke

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."lms/file/ZFile.php");

// FIXME: trenutno ne postoji tabela folder :(
// Polje $folderId se u tabeli file zove "projekat"


class FileRevision {
	public $id;
	public $revisionNumber, $fileId, $time, $authorId; // FIXME trenutno svaka revizija fajla može imati različit filename
	// No pošto u bazi ne postoji takvih slogova, odlučio sam da otpočetka ne radim tako jer nema smisla
	public $file;
	public $author;

	public function getPath() {
		// FIXME ovo ništa ne valja
		$path = Config::$backend_file_path . "/projekti/fajlovi/" . $this->file->folderId . "/";
		$path .= $this->file->authorId . "/" . $this->file->filename . "/v" . $this->revisionNumber . "/";
		$path .= $this->file->filename;
		return $path;
	}

	public function updateDiff() {
		if ($revisionNumber == 1) return; // last revision

		$newPath = $this->getPath();

		$oldPath = Config::$backend_file_path . "/projekti/fajlovi/" . $this->file->folderId . "/";
		$oldPath .= $this->file->authorId . "/" . $this->file->filename . "/v" . ($this->revisionNumber-1) . "/";
		$oldPath .= $this->file->filename;

		$diff = `/usr/bin/diff -u $oldPath $newPath`;

		$q10 = DB::query("INSERT INTO projekat_file_diff set file=".$this->id.", diff='" . Util::my_escape($diff) . "'");
	}
}

?>