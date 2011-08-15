<?php

// Modul: lms/portal
// Klasa: PortalFeed
// Opis: RSS feed

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

// FIXME: u bazi ne postoji tabela portal
// polje portal tabele rss se zove "projekat"


class PortalFeed {
	public $id;
	public $title, $url, $description, $time, $authorId, $portalId;
	public $author;

	public static function fromId($id) {
		$q10 = DB::query("select l.naziv, l.url, l.opis, UNIX_TIMESTAMP(l.vrijeme), l.osoba, l.projekat, o.ime, o.prezime, o.brindexa, a.login from projekat_rss as l, osoba as o, auth as a where l.id=$id and l.osoba=o.id and o.id=a.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown feed");
		}

		$pl = new PortalFeed;
		$pl->id = $id;
		$pl->title = mysql_result($q10,0,0);
		$pl->url = mysql_result($q10,0,1);
		$pl->description = mysql_result($q10,0,2);
		$pl->time = mysql_result($q10,0,3);
		$pl->authorId = mysql_result($q10,0,4);
		$pl->portalId = mysql_result($q10,0,5);

		$pl->author = new Person;
		$pl->author->id = $pl->authorId;
		$pl->author->name = mysql_result($q10,0,6);
		$pl->author->surname = mysql_result($q10,0,7);
		$pl->author->studentIdNr = mysql_result($q10,0,8);
		$pl->author->login = mysql_result($q10,0,9);

		return $pl;
	}


	public function add() {
		$q10 = DB::query("select id from projekat_rss order by id desc limit 1");
		if (mysql_num_rows($q10)==0)
			$newId = 1;
		else
			$newId = mysql_result($q10,0,0)+1;

		$q20 = DB::query("INSERT INTO projekat_rss SET id=$newId, naziv='".$this->title."', url='".$this->url."', opis='".$this->description."', vrijeme=NOW(), osoba=".$this->authorId.", projekat=".$this->portalId);
		$this->id = $newId;

		$q30 = DB::query("select UNIX_TIMESTAMP(vrijeme) from projekat_rss where id=$newId");
		$this->time = mysql_result($q30,0,0);
	}

	public function update() {
		$q10 = DB::query("UPDATE projekat_rss SET naziv='".$this->title."', url='".$this->url."', opis='".$this->description."', osoba=".$this->authorId.", projekat=".$this->portalId." WHERE id=".$this->id);
	}

	public function delete() {
		$q10 = DB::query("DELETE FROM projekat_rss WHERE id=".$this->id);
	}

}

?>