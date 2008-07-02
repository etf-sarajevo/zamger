<?
class mainConfig
	{
		var $mainErrors = array();
		
		/*
		// Vraca postavke
		function getSet()
			{				
				// MySQL Data
				$set['db_host'] = 'localhost';
				$set['db_name'] = 'raspored';
				//$set['db_user'] = 'spacware_root';
				//$set['db_pass'] = 'root';
				$set['db_user'] = 'root';
				$set['db_pass'] = '';					
				
				// Vraca
				return $set;
			}
		
		// Dodaje error u memoriju	
		function addError($error)
			{
				$this->mainErrors[] = $error;
			}
			
		// Vraca sve errore
		function getErrors()
			{
				return implode("<br />",$this->mainErrors);
			}		
		*/
		// Uzima postavke stranice iz baze	
		function getSetDb()
			{
				global $db;
				
				if ($row = $db->Query("SELECT * FROM sac_config LIMIT 1", true))
					{
						return $row;
					}
				else
					{
						return false;
					}
			}
			
		// Izbacuje sve znakove iz stringa osim slova, brojeva, - i _ i mjenja space sa -
		function stringToText($string, $toLower = false)
			{
				if (!$string)
					{
						return false;
					}
				else
					{
						$string = preg_replace ('/[^a-zA-Z0-9\-_\ ]/', '', $string);
						$string = preg_replace("/ /", "-", $string);
						
						if ($toLower == true)
							{
								return strtolower($string);
							}
						else
							{
								return $string;
							}
					}
			}
			
		// Random String
		function randString($duzina = 32)
			{
				return substr(md5(uniqid(rand(), true)), 0, $duzina);
			}
			
		//
		function dan($dan)
			{
				$d = array (1 => "Ponedjeljak", 2=>"Utorak", 3=>"Srijeda", 4=>"Cetvrtak", 5=>"Petak", 6=>"Subota", 7=>"Nedjelja");
				
				return $d[$dan];
			}
			
		//Ispisuje prozor (popup) sa obavjestenjem
		function printInfo($text, $reloadBack = false)
			{
				if($reloadBack == true)
					$reload = 'setTimeout("window.location=\'\'", 0);';
					
				echo '
					<script type = "text/javascript">
						alert(\''.$text.'\');
						
						'.$reload.'
					</script>
				';
			}

	}
?>