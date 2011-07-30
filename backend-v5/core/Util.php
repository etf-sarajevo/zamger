<?php

// Modul: core
// Klasa: Util
// Opis: kolekcija korisnih funkcija


class Util {
	// Version of substr that avoids breaking string within Unicode sequence, which
	// results in invalid characters (and breaks some RSS readers)
	function substr_utf8($string, $start, $len) {
		do {
			$result = substr($string, $start, $len);
			$len++;
		} while (ord(substr($result, strlen($result)-1, 1)) > 128);
		return $result;
	}

	// Function that adds "..." (a.k.a. ellipse) if string is longer than $len
	// characters, taking care not to add it in the middle of word
	function ellipsize($string, $len, $maxWordLength = 20) {
		if (strlen($string) <= $len) return $string;

		$wordTerminators = array(" ", ".", ",");

		$pos = strlen($string);
		foreach ($wordTerminators as $t) {
			$tmp = strpos($string, $t, $len);
			if ($tmp > 0 && $tmp < $pos) $pos=$tmp; // $tmp = 0 means not found
		}
		if ($pos > $len+$maxWordLength) $pos = $len+$maxWordLength;
		if ($pos == strlen($string)) return $string; // not worth ellipsizing
		
		return substr($string,0,$pos)."...";
	}

	// Returns string with nice printout of file size in kibibytes
	function nicesize($size) {
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
}

?>
