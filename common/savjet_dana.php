<?

// SARADNIK/SAVJET_DANA - prikazuje tip-of-the-day


function common_savjet_dana() {

global $userid;
global $user_student, $user_nastavnik, $user_studentska, $user_siteadmin;


if (isset($_REQUEST['akcija']) && $_REQUEST['akcija'] == "ne_prikazuj" && $_REQUEST['ne_prikazuj'] && check_csrf_token()) {
	?>
	<h2>Da li ste znali...</h2>
	<p>Prozor "Da li ste znali..." više neće biti prikazivan.</p>
	<p>Ako ga kasnije budete željeli reaktivirati, možete to učiniti kroz vaš Profil.</p>
	<?
	db_query("delete from preference where korisnik=$userid and preferenca='savjet_dana'");
	db_query("insert into preference set korisnik=$userid, preferenca='savjet_dana', vrijednost=0");
	zamgerlog("iskljucio savjet dana", 2);
	zamgerlog2("iskljucio savjet dana");

	return;	
}

$upit = "";
if ($user_nastavnik) $upit .= "vrsta_korisnika='nastavnik' or ";
if ($user_student) $upit .= "vrsta_korisnika='student' or ";
if ($user_siteadmin) $upit .= "vrsta_korisnika='siteadmin' or ";
if ($user_studentska) $upit .= "vrsta_korisnika='studentska' or ";

$tekst = db_get("select tekst from savjet_dana where $upit 0 order by rand() limit 1"); // 0 zbog zadnjeg or

?>
<h2>Da li ste znali...</h2>

<img src="static/images/savjet_dana.gif" align="left" width="92" height="150" style="margin: 0px 20px">

<?=$tekst?>

<hr>

<?=genform("POST")?>
<input type="hidden" name="akcija" value="ne_prikazuj">
<input type="checkbox" name="ne_prikazuj">
Ne prikazuj više savjet dana 
<input type="submit" value="Potvrda" class="default"> <input type="button" onclick="window.close()" value="Zatvori" class="default">
<input type="button" onclick="window.location.reload(true)" value="Novi savjet" class="default">
</form>
<?

}

?>
