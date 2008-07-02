<?
class dbClass extends mainConfig
	{
		var $veza;
		var $sqlErrors = array();	
		
		// Konekcija na bazu
		function Connect()
			{
				// MySQL Data
				$settings = mainConfig::getSet();
				
				$this->veza = mysql_connect($settings['db_host'],$settings['db_user'],$settings['db_pass']);
				
				if (!$this->veza)
					{
						$this->sqlErrors[] = mysql_error();
						return false;
					}
				else
					{
						$sel = mysql_select_db($settings['db_name']);
						
						if (!$sel)
							{
								$this->sqlErrors[] = mysql_error();
								return false;
							}
						else
							{								
								return true;
							}						
					}
			}
		
		// Izvrsava query nad bazom
		function Query($query,$do_fetch = false)
			{
				$do_query = mysql_query($query,$this->veza);
				
				if (!$do_query)
					{
						$this->sqlErrors[] = mysql_error();
						return false;
					}
				else
					{
						if ($do_fetch == true)
							{
								return $this->fetchArray($do_query);
							}
						else
							{
								return $do_query;
							}
					}
			}
		
		// Vraca array rezultata iz baze	
		function fetchArray($array, $seek = false)
			{
				if (!$array)
					{
						return false;
					}
				else
					{
						return mysql_fetch_array($array);					
					}
			}
		
		// Vraca pointer na pocetak
		function dataSeek($array)
			{
				if (!$array)
					{
						return false;
					}
				else
					{
						if (!mysql_data_seek($array, 0))
							{
								return false;
							}
						else
							{
								return true;
							}						
					}
			}
			
		// Prekida vezu sa bazom	
		function Close()
			{
				mysql_close($this->veza);
			}
		
		// Broji selektovane rezultate
		function Count($array)
			{
				if (!$array)
					{
						return false;
					}
				else
					{
						return mysql_num_rows($array);
					}
			}
		
		// Primjenjuje addslashes
		 function addSlashes($array)
			{
				if (!$array)
					{
						return false;
					}
				else
					{
						// Stripslashes
						if (get_magic_quotes_gpc()) 
							{
								$array = stripslashes($array);
							}
							
						// Quote if not a number or a numeric string
						if (!is_numeric($value)) 
							{
								$array = mysql_real_escape_string($array);
							}
							
						return $array;
					}
			}  
			
		function addS($data)
			{
				return $this->addSlashes($data);
			}
			
		// Primjenjuje stripslashes
		function stripS($string, $retZero = false)
			{
				if (!$string)
					{
						if ($retZero == true)
							{
								return 0;
							}
						else
							{
								return false;
							}
					}
				else
					{
						$string = str_replace("<","&lt;", $string);
						$string = str_replace(">","&gt;", $string);

						return $string;
					}
			}
		
		// Vraca id zadnje sql naredbe
		function mysqlId()
			{
				$id = mysql_insert_id();
				
				if (!$id)
					{
						return false;
					}
				else
					{
						return $this->stripSlashes($id);
					}
			}
		
		// Funkcija koja vraca key na osnovu valuea iz arraya
		function getArrayKey($arr_in, $val_in)
			{
				foreach ($arr_in as $key => $val)
					{
						if ($val == $val_in)
							{
								return $key; 
								break;
							}
					}
			}	
		
		// Provjerava koliko je redova zahvaceno poslijednjom naredbom
		function Affected()
			{
				return mysql_affected_rows();
			}
			
		//funkcija za brojanje
		function dbCountPro($tablica, $polje = false, $uslov = false) 
			{	
				if($polje == false)
					{
						$query = $this->Query("SELECT * FROM ".$tablica." ");
						if(!$query)
							echo "ERROR!";
					}
				else
					{
						$query = $this->Query("SELECT * FROM ".$tablica." WHERE ".$polje." = ".$uslov." ");
						if(!$query)
							echo "ERROR!";
					}
						
				$br = $this->Count($query);
				return $br;
			}
	}
?>