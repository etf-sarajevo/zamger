<?php

// Modul: lms/portal
// Klasa: Portal
// Opis: jedna portal stranica

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

require_once(Config::$backend_path."lms/portal/PortalArticle.php");
require_once(Config::$backend_path."lms/portal/PortalLink.php");
require_once(Config::$backend_path."lms/portal/PortalFeed.php");

// FIXME: trenutno ne postoji tabela portal :(

class Portal {
	public $id;
	public $ownerId, $name;

	// Ovo su neki privremeni atributi:
	public $layout; // string koji opisuje početnu stranicu
	public $menu; // string koji opisuje raspored modula u meniju
	public static $menuTitles = array("portal" => "Početna strana", "projectinfo" => "Informacije o projektu", "links" => "Korisni linkovi", "rss" => "RSS feedovi", "articles" => "Članci", "files" => "Fajlovi", "forum" => "Grupa za diskusiju");
	

	public function getLatestArticles($limit = 0, $offset = 0) {
		if ($limit>0 && $offset>0) 
			$limitsql = "LIMIT $offset, $limit";
		else if ($limit>0)
			$limitsql = "LIMIT $limit";
		else
			$limitsql = "";

		$q10 = DB::query("SELECT c.id, c.naslov, c.tekst, c.slika, UNIX_TIMESTAMP(c.vrijeme), c.osoba, o.ime, o.prezime, o.brindexa FROM bl_clanak as c, osoba as o WHERE c.projekat=".$this->id." and c.osoba=o.id order by c.vrijeme desc $limitsql");
		$articles = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$pa = new PortalArticle;
			$pa->id = $r10[0];
			$pa->subject = $r10[1];
			$pa->text = $r10[2];
			$pa->image = $r10[3];
			$pa->time = $r10[4];
			$pa->authorId = $r10[5];
			
			$pa->author = new Person;
			$pa->author->id = $r10[5];
			$pa->author->name = $r10[6];
			$pa->author->surname = $r10[7];
			$pa->author->studentIdNr = $r10[8];
			$pa->author->login = ""; // FIXME: problem je što može postojati više slogova u tabeli auth za jedan login a hoću da izbjegnem podupite

			array_push($articles, $pa);
		}
		return $articles;
	}

	public function getArticlesCount() {
		$q10 = DB::query("SELECT count(*) FROM bl_clanak WHERE projekat=".$this->id);
		return mysql_result($q10,0,0);
	}

	public function getLinks($limit = 0, $offset = 0) {
		// FIXME - ova funkcija daje linkove poredane po vremenu, što je malo besmisleno
		// Treba omogućiti autoru da podesi broj i redoslijed linkova po želji

		if ($limit>0 && $offset>0) 
			$limitsql = "LIMIT $offset, $limit";
		else if ($limit>0)
			$limitsql = "LIMIT $limit";
		else
			$limitsql = "";

		$q10 = DB::query("SELECT l.id, l.naziv, l.url, l.opis, UNIX_TIMESTAMP(l.vrijeme), l.osoba, o.ime, o.prezime, o.brindexa FROM projekat_link as l, osoba as o WHERE l.projekat=".$this->id." and l.osoba=o.id order by l.vrijeme desc $limitsql");
		$links = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$pl = new PortalLink;
			$pl->id = $r10[0];
			$pl->title = $r10[1];
			$pl->url = $r10[2];
			$pl->description = $r10[3];
			$pl->time = $r10[4];
			$pl->authorId = $r10[5];
			
			$pl->author = new Person;
			$pl->author->id = $r10[5];
			$pl->author->name = $r10[6];
			$pl->author->surname = $r10[7];
			$pl->author->studentIdNr = $r10[8];
			$pl->author->login = ""; // FIXME: problem je što može postojati više slogova u tabeli auth za jedan login a hoću da izbjegnem podupite

			array_push($links, $pl);
		}
		return $links;
	}

	public function getLinksCount() {
		$q10 = DB::query("SELECT count(*) FROM projekat_link WHERE projekat=".$this->id);
		return mysql_result($q10,0,0);
	}

	public function getRSSFeeds() {
		$q10 = DB::query("SELECT f.id, f.naziv, f.url, f.opis, UNIX_TIMESTAMP(f.vrijeme), f.osoba, o.ime, o.prezime, o.brindexa FROM projekat_rss as f, osoba as o WHERE f.projekat=".$this->id." and f.osoba=o.id");
		$feeds = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$pf = new PortalFeed;
			$pf->id = $r10[0];
			$pf->title = $r10[1];
			$pf->url = $r10[2];
			$pf->description = $r10[3];
			$pf->time = $r10[4];
			$pf->authorId = $r10[5];
			
			$pf->author = new Person;
			$pf->author->id = $r10[5];
			$pf->author->name = $r10[6];
			$pf->author->surname = $r10[7];
			$pf->author->studentIdNr = $r10[8];
			$pf->author->login = ""; // FIXME: problem je što može postojati više slogova u tabeli auth za jedan login a hoću da izbjegnem podupite

			array_push($feeds, $pf);
		}
		return $feeds;
	}


	public function getRSSFeedsCount() {
		$q10 = DB::query("SELECT count(*) FROM projekat_rss WHERE projekat=".$this->id);
		return mysql_result($q10,0,0);
	}
}

?>