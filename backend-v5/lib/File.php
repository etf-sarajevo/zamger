<?php

// Modul: lib
// Klasa: File
// Opis: rad sa datotekama u internom storage-u


class File {
	public $filename, $CourseUnitYear, $owner, $objectId, $module;
	
	public function __construct($filename, $CourseUnitYear, $owner, $objectId, $module) {
		$this->filename = $filename;
		$this->CourseUnitYear = $CourseUnitYear;
		$this->owner = $owner;
		$this->objectId = $objectId;
		$this->module = $module;
	}
	
	// Function that performs escaping and cleanup on assignment filename
	public static function cleanUpFilename($filename) {
		$filename = strip_tags(basename($filename));
		
		// Remove HTML entity characters for potential XSS in homework UI
		$filename = str_replace("&", "", $filename);
		$filename = str_replace("\"", "", $filename);
		return $filename;
	}
	
	// Returns string with nice printout of file size in kibibytes
	public static function nicesize($size) {
		if ($size>1024*1024*1024) {
			return intval($size/(1024*1024*1024/10))/10 . " GB";
		} else if ($size>1024*1024*10) {
			return intval($size/(1024*1024)) . " MB";
		} else if ($size>1024*1024) {
			return intval($size/(1024*1024/10))/10 . " MB";
		} else if ($size>1024*10) {
			return intval($size/1024) . " kB";
		} else if ($size>1024) {
			return intval($size / (1024/10))/10 . " kB";
		} else {
			return $size . " B";
		}
	}
	
	// Get directory where file resides
	public function basePath() {
		$dir  = Config::$backend_file_path . "/" . $this->module . "/" . $this->CourseUnitYear->CourseUnit->id;
		$dir .= "-" . $this->CourseUnitYear->AcademicYear->id . "/" . $this->owner->id . "/" . $this->objectId;
		if (!file_exists($dir))
			mkdir ($dir, 0777, true);
		return $dir;
	}
	
	// Get directory where file resides
	public function fullPath() { return $this->basePath() . "/" . $this->filename; }
	
	public function extension() {
		return "." . pathinfo($this->filename, PATHINFO_EXTENSION); // FIXME: postojeći kod očekuje da ekstenzije počinju tačkom...
	}

}

?>
