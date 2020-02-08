<?

// LIB/LEGACY - reimplementacija raznih PHP funkcija zbog podrške za stare verzije PHPa



// file_put_contents uveden u PHP v5.0

if (!function_exists('file_put_contents')) {
	function file_put_contents($file,$tekst) {
		if (!($file = fopen($file,"w"))) return false;
		$bytes = fwrite($file,$tekst);
		fclose($file);
		return $bytes;
	}
}


// mysql_set_charset uveden u PHP v5.2.3

if (function_exists('mysql_set_charset') === false) {
	/**
	* Sets the client character set.
	*
	* Note: This function requires MySQL 5.0.7 or later.
	*
	* @see http://www.php.net/mysql-set-charset
	* @param string $charset A valid character set name
	* @param resource $link_identifier The MySQL connection
	* @return TRUE on success or FALSE on failure
	*/
	function mysql_set_charset($charset, $link_identifier = null)
	{
		if ($link_identifier == null) {
			return mysql_query('SET NAMES "'.$charset.'"');
			//   return mysql_query('SET CHARACTER SET "'.$charset.'"');
		} else {
			return mysql_query('SET NAMES "'.$charset.'"', $link_identifier);
			//   return mysql_query('SET CHARACTER SET "'.$charset.'"', $link_identifier);
		}
	}
}


// mb_substr - zahtijeva instalaciju ekstenzije "php-mb"

if (function_exists('mb_substr') === false) {
	function mb_substr($string, $start, $len) {
		do {
			$result = substr($string, $start, $len);
			$len++;
		} while (ord(substr($result, strlen($result)-1, 1)) > 128);
		return $result;
	}
}



?>
