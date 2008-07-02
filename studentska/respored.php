<?
function studentska_raspored ()
	{

		require_once "classes/admin_raspored.php";

		$aRas = new adminRaspored;

		$aRas ->printajAdministracijuRasporeda();
	}
?>