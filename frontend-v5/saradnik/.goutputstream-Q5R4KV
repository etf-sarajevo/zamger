<?

// SARADNIK/SAVJET_DANA - prikazuje tip-of-the-day


function saradnik_savjet_dana() {

global $userid;

if ($_REQUEST['akcija'] == "ne_prikazuj" && $_REQUEST['ne_prikazuj'] && check_csrf_token()) {
	?>
	<h2>Da li ste znali...</h2>
	<p>Prozor "Da li ste znali..." više neće biti prikazivan.</p>
	<p>Ako ga kasnije budete željeli reaktivirati, možete to učiniti kroz vaš Profil.</p>
	<?
	$q20 = myquery("delete from preference where korisnik=$userid and preferenca='savjet_dana'");
	$q30 = myquery("insert into preference set korisnik=$userid, preferenca='savjet_dana', vrijednost=0");
	zamgerlog("iskljucio savjet dana", 2);

	return;	
}



$q10 = myquery("select tekst from savjet_dana order by rand() limit 1");

?>
<h2>Da li ste znali...</h2>

<img src="savjet_dana.gif" align="left" width="92" height="150">

<?=mysql_result($q10,0,0)?>

<hr>

<?=genform("POST")?>
<input type="hidden" name="akcija" value="ne_prikazuj">
<input type="checkbox" name="ne_prikazuj">
Ne prikazuj više savjet dana<br>
<input type="submit" value="Potvrda">
</form>
<?

}

?>
