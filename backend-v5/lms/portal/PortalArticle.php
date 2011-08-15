<?php

// Modul: lms/portal
// Klasa: PortalArticle
// Opis: jedan članak (vijest) na portalu

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

// FIXME: u bazi ne postoji tabela portal
// polje portal tabele clanak se zove "projekat"


class PortalArticle {
	public $id;
	public $subject, $text, $image, $time, $authorId, $portalId;
	public $author;

	public static function fromId($id) {
		$q10 = DB::query("select c.naslov, c.tekst, c.slika, UNIX_TIMESTAMP(c.vrijeme), c.osoba, c.projekat, o.ime, o.prezime, o.brindexa, a.login from bl_clanak as c, osoba as o, auth as a where c.id=$id and c.osoba=o.id and o.id=a.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown article");
		}

		$pa = new PortalArticle;
		$pa->id = $id;
		$pa->subject = mysql_result($q10,0,0);
		$pa->text = mysql_result($q10,0,1);
		$pa->image = mysql_result($q10,0,2);
		$pa->time = mysql_result($q10,0,3);
		$pa->authorId = mysql_result($q10,0,4);
		$pa->portalId = mysql_result($q10,0,5);

		$pa->author = new Person;
		$pa->author->id = $pl->authorId;
		$pa->author->name = mysql_result($q10,0,6);
		$pa->author->surname = mysql_result($q10,0,7);
		$pa->author->studentIdNr = mysql_result($q10,0,8);
		$pa->author->login = mysql_result($q10,0,9);

		return $pa;
	}

	public function add() {
		$q10 = DB::query("select id from bl_clanak order by id desc limit 1");
		if (mysql_num_rows($q10)==0)
			$newId = 1;
		else
			$newId = mysql_result($q10,0,0)+1;

		$q20 = DB::query("INSERT INTO bl_clanak SET id=$newId, naslov='".$this->subject."', tekst='".$this->text."', slika='".$this->image."', vrijeme=NOW(), osoba=".$this->authorId.", projekat=".$this->portalId);
		$this->id = $newId;

		$q30 = DB::query("select UNIX_TIMESTAMP(vrijeme) from bl_clanak where id=$newId");
		$this->time = mysql_result($q30,0,0);
	}

	public function update() {
		$q10 = DB::query("UPDATE bl_clanak SET naslov='".$this->subject."', tekst='".$this->text."', slika='".$this->image."', osoba=".$this->authorId.", projekat=".$this->portalId." WHERE id=".$this->id);
	}

	public function delete() {
		$q10 = DB::query("DELETE FROM bl_clanak WHERE id=".$this->id);
	}

}

?>