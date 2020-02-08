<?
class mainConfig
	{

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
					{
						$reload = 'setTimeout("window.location=\'\'", 0);';
						echo "<img src = 'img/load.gif' alt = 'Ucitavanje'/> Ucitavam...";
					}
					
				echo '
					<script type = "text/javascript">
						alert(\''.$text.'\');
						
						'.$reload.'
					</script>
				';
			}

	}
?>