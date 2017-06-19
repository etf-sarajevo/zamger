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


	// Shortcut function: whether string ends with another string
	// Example: if (ends_with($filename, ".txt")) echo "Text file";
	public static function ends_with($string, $substring) {
		if (strlen($string) >= strlen($substring))
			if (substr($string, strlen($string)-strlen($substring)) === $substring)
				return true;
		return false;
	}
	
	// Recursively delete directory with all subdirectories and files
	public static function rm_minus_r($path) {
		if ($handle = opendir($path)) {
			while ($file = readdir($handle)) {
				if ($file == "." || $file == "..") continue;
				$filepath = "$path/$file";
				if (is_dir($filepath)) {
					Util::rm_minus_r($filepath);
					rmdir($filepath);
				} else {
					unlink($filepath);
				}
			}
		}
		closedir($handle);
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
		
			$num_ip = Util::ip2float($ip);
			foreach ($reserved_ips as $r) {
				$min = Util::ip2float($r[0]); 
				$max = Util::ip2float($r[1]);
				if (($num_ip >= $min) && ($num_ip <= $max)) return false;
			}
			return true;
		} else {
			return false;
		}
	}

	// Hack za činjenicu da je long tip u PHPu signed
	// Preuzeto sa: http://pgregg.com/blog/2009/04/php-algorithms-determining-if-an-ip-is-within-a-specific-range.html
	private static function ip2float($ip) {
		return (float)sprintf("%u",ip2long($ip));
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

	// Convert all numeric strings into numbers, and all boolean strings into booleans
	public static function fix_data_types(&$var) {
		if (is_object($var) || is_array($var)) {
			foreach($var as $key => &$value)
				Util::fix_data_types($value);
		} else {
			if (is_numeric($var)) $var=floatval($var);
			if ($var === "true") $var=true;
			if ($var === "false") $var=false;
		}
	}

	// Function removes all illegal and potentially suspect Unicode chars from input
	public static function clear_unicode($text) {
		// Zbog buga u libc-u koji se propagira na PHP: 
		//	https://bugs.php.net/bug.php?id=48147
		// linije ispod trebaju ostati iskomentarisane na verzijama PHPa v5.0-v7.0!
		// U suprotnom kod ispod će raditi bolje
		
		// iconv iz nekog razloga preskače karakter sa ASCII kodom 01
		//for ($i=0; $i<strlen($text); $i++)
		//	if (ord($text[$i]) == 1) $text[$i]=" ";
		
		//if (function_exists('iconv'))
		//	return iconv("UTF-8", "UTF-8//IGNORE", $text);
		if (!function_exists('mb_convert_encoding')) return $text; // nemamo mb, ne možemo ništa
		ini_set('mbstring.substitute_character', "none"); 
		return mb_convert_encoding($text, 'UTF-8', 'UTF-8'); 
	}
}

?>
