<?php

// Modul: lms/forum
// Klasa: ForumPost
// Opis: jedna poruka na forumu

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

// FIXME: u bazi ne postoji tabela forum
// polje forum tabele topic se zove "projekat"


class ForumPost {
	public $id;
	public $subject, $time, $authorId, $topicId, $text;
	public $author;

	public static function fromId($id) {
		$q10 = DB::query("select p.naslov, UNIX_TIMESTAMP(p.vrijeme), p.osoba, p.tema, pt.tekst, o.ime, o.prezime, o.brindexa, a.login from bb_post as p, bb_post_text as pt, osoba as o, auth as a where p.id=$id and pt.post=$id and p.osoba=o.id and o.id=a.id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown forum post");
		}
		
		$fp = new ForumPost;
		$fp->id = $id;
		$fp->subject = mysql_result($q10,0,0);
		$fp->time = mysql_result($q10,0,1);
		$fp->authorId = mysql_result($q10,0,2);
		$fp->topicId = mysql_result($q10,0,3);
		$fp->text = mysql_result($q10,0,4);

		$fp->author = new Person;
		$fp->author->id = $fp->authorId;
		$fp->author->name = mysql_result($q10,0,5);
		$fp->author->surname = mysql_result($q10,0,6);
		$fp->author->studentIdNr = mysql_result($q10,0,7);
		$fp->author->login = mysql_result($q10,0,8);
		
		return $fp;
	}

	public function delete() {
		$q10 = DB::query("delete from bb_post where id=".$this->id);
		$q20 = DB::query("delete from bb_post_text where post=".$this->id);
	}

	public function update() {
		$q10 = DB::query("update bb_post set naslov='".$this->subject."' where id=".$this->id);
		$q20 = DB::query("update bb_post_text set tekst='".$this->text."' where post=".$this->id);

	}
}

?>