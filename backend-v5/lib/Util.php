<?php

// Modul: lib
// Klasa: Util
// Opis: kolekcija korisnih funkcija


require_once(Config::$backend_path."lib/UnresolvedClass.php");

class Util {
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
		if (isset($_SERVER["HTTP_CLIENT_IP"]) && Util::validip($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		}
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
			if (Util::validip(trim($ip))) {
				return $ip;
			}
		}
		if (isset($_SERVER["HTTP_X_FORWARDED"]) && Util::validip($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]) && Util::validip($_SERVER["HTTP_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_FORWARDED"]) && Util::validip($_SERVER["HTTP_FORWARDED"])) {
			return $_SERVER["HTTP_FORWARDED"];
		} elseif (isset($_SERVER["HTTP_X_FORWARDED"]) && Util::validip($_SERVER["HTTP_X_FORWARDED"])) {
			return $_SERVER["HTTP_X_FORWARDED"];
		} else {
			return $_SERVER["REMOTE_ADDR"];
		}
	}



	// Vraća vrijednost request parametra ili false
	public static function param($name) {
		if (isset($_REQUEST[$name])) return $_REQUEST[$name];
		return false;
	}

	// Vraća integer vrijednost request parametra ili nulu
	public static function int_param($name) {
		if (isset($_REQUEST[$name])) return intval($_REQUEST[$name]);
		return 0;
	}
	
	// Convert assoc. array into class with given name, replace unresolvedClasses with UnresolvedClass
	public static function array_to_class($array, $className, $unresolvedClasses = array()) {
		$code = "\$obj = new $className;\n";
		foreach($array as $key => $value) 
			$code .= "\$obj->$key = ".var_export($value,true).";\n";
		$code .= "return \$obj;";
		$obj = eval($code);
		
		foreach($unresolvedClasses as $className)
			UnresolvedClass::makeForParent($obj, $className);
		
		return $obj;
	}
}

?>
