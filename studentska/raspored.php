<LINK href="css/raspored.css" rel="stylesheet" type="text/css">
<?
function studentska_raspored ()
	{
		global $userid,$user_siteadmin,$user_studentska, $db, $main;

		// Provjera privilegija
		if (!$user_studentska && !$user_siteadmin) {
			zamgerlog("nije studentska",3); // 3: error
			biguglyerror("Pristup nije dozvoljen.");
			return;
		}
		
		require_once "classes/db.php";
		require_once "classes/main.php";
		require_once "classes/admin_raspored.php";
		
		$db = new dbClass;
		$main = new mainConfig;
//		$aRas = new adminRaspored;

		return printajAdministracijuRasporeda();
	}
?>