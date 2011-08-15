<?php

// Modul: core/file
// Klasa: Folder
// Opis: virtuelni folder u kojem se nalazi niz fajlova

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");
require_once(Config::$backend_path."lms/file/ZFile.php");
require_once(Config::$backend_path."lms/file/FileRevision.php");


// FIXME: trenutno ne postoji tabela folder :(

class Folder {
	public $id;
	public $ownerId; // link na drugi modul?


	// Returns a list of files in folder, sorted alphabetically
	public function getAllFiles($limit = 0, $offset = 0) {
		if ($limit>0 && $offset>0) 
			$limitsql = "LIMIT $offset, $limit";
		else if ($limit>0)
			$limitsql = "LIMIT $limit";
		else
			$limitsql = "";

		// Ovo je neefikasno rješenje, ali to je posljedica lošeg dizajna tabele
		// Polje file je jednako 0 za PRVU reviziju, za ostale je ID prve revizije
		$q10 = DB::query("select id from projekat_file where projekat=".$this->id." and file=0 order by filename $limitsql"); 
		$files = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$f = ZFile::fromId($r10[0]);
			array_push($files, $f);
		}
		return $files;
	}

	public function getFileCount() {
		$q10 = DB::query("select count(*) from projekat_file where projekat=".$this->id." and file=0");
		return mysql_result($q10,0,0);
	}

	// Adds a new file to folder, with specified content
	public function addFile($filename, $authorId, $fileContent) {
		$file = $this->addFileWithoutContent($filename, $authorId);

		if (! file_put_contents($file->lastRevision->getPath(), $fileContent) ) {
			$file->delete();
			throw new Exception("failed to write data to file");
		}
		chmod($file->lastRevision->getPath(), 0777);

		return $file;
	}

	// Adds a new file to folder *without* contents (such file is invalid and can't be downloaded)
	// It's assumed that caller will create the required file in some other way
	public function addFileWithoutContent($filename, $authorId) {
		// Can't repeat same filename in folder
		$q10 = DB::query("select count(*) from projekat_file where projekat=".$this->id." and file=0 and filename='$filename'");
		if (mysql_result($q10,0,0)>0)
			throw new Exception("file with this name already exists");

		$file = new ZFile;
		$file->id = 0; // Postavljamo na nulu da bi prva revizija bila korektno ubačena FIXME
		$file->filename = $filename;
		$file->nrRevisions = 0;
		$file->authorId = $authorId;
		$file->folderId = $this->id;

		// Ubacujemo podatke o fajlu u bazu - zasada ništa pošto će se kreirati dodavanjem prve revizije

		// Dodajemo prvu reviziju
		$file->lastRevision = $file->addRevisionWithoutContent($authorId);
		return $file;
	}

	public static function add() {
		// Ovaj kod pastujem ovdje da ga ne zaboravim
					if (!file_exists("$conf_files_path/projekti/fajlovi/$projekat")) 
					{
						mkdir ("$conf_files_path/projekti/fajlovi/$projekat",0777, true);
					}
	}
}

?>