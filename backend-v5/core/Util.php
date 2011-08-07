<?php

// Modul: core
// Klasa: Util
// Opis: kolekcija korisnih funkcija


class Util {
	// Version of substr that avoids breaking string within Unicode sequence, which
	// results in invalid characters (and breaks some RSS readers)
	public static function substr_utf8($string, $start, $len) {
		do {
			$result = substr($string, $start, $len);
			$len++;
		} while (ord(substr($result, strlen($result)-1, 1)) > 128);
		return $result;
	}

	// Function that adds "..." (a.k.a. ellipse) if string is longer than $len
	// characters, taking care not to add it in the middle of word
	public static function ellipsize($string, $len, $maxWordLength = 20) {
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
	

	// Funkcija za dobivanje IP adrese korisnika iza proxy-ja
	// Preuzeto sa: http://www.teachmejoomla.net/code/php/remote-ip-detection-with-php.html
	public static function validip($ip) {
		if (!empty($ip) && ip2long($ip)!=-1) {
			$reserved_ips = array (
				array('0.0.0.0','2.255.255.255'),
				array('10.0.0.0','10.255.255.255'),
				array('127.0.0.0','127.255.255.255'),
				array('169.254.0.0','169.254.255.255'),
				array('172.16.0.0','172.31.255.255'),
				array('192.0.2.0','192.0.2.255'),
				array('192.168.0.0','192.168.255.255'),
				array('255.255.255.0','255.255.255.255')
			);
		
			$num_ip = ip2float($ip);
			foreach ($reserved_ips as $r) {
				$min = ip2float($r[0]); 
				$max = ip2float($r[1]);
				if (($num_ip >= $min) && ($num_ip <= $max)) return false;
			}
			return true;
		} else {
			return false;
		}
	}

	public static function getip() {
		if (Util::validip($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		}
		foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
			if (Util::validip(trim($ip))) {
				return $ip;
			}
		}
		if (Util::validip($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} elseif (Util::validip($_SERVER["HTTP_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif (Util::validip($_SERVER["HTTP_FORWARDED"])) {
			return $_SERVER["HTTP_FORWARDED"];
		} elseif (Util::validip($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}
	}
}

?>
