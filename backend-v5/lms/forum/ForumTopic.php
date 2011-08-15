<?php

// Modul: lms/forum
// Klasa: ForumTopic
// Opis: jedna tema na forumu

require_once(Config::$backend_path."core/DB.php");

// FIXME: u bazi ne postoji tabela forum
// polje forum tabele topic se zove "projekat"


class ForumTopic {
	public $id;
	public $lastUpdate, $firstPostId, $lastPostId, $views, $authorId, $forumId;
	public $lastPost;

	public static function fromId($id) {
		$q10 = DB::query("select FROM_UNIXTIME(vrijeme), prvi_post, zadnji_post, pregleda, osoba, projekat from bb_tema where id=$id");
		if (mysql_num_rows($q10)<1) {
			throw new Exception("unknown forum topic");
		}
		
		$ft = new ForumTopic;
		$ft->id = $id;
		$ft->lastUpdate = mysql_result($q10,0,0);
		$ft->firstPostId = mysql_result($q10,0,1);
		$ft->lastPostId = mysql_result($q10,0,2);
		$ft->views = mysql_result($q10,0,3);
		$ft->authorId = mysql_result($q10,0,4);
		$ft->forumId = mysql_result($q10,0,5);
		
		return $ft;
	}

	public function getCountReplies() {
		$q10 = DB::query("select count(*) from bb_post where tema=".$this->id);
		return mysql_result($q10,0,0)-1;
	}

	public function viewed() {
		$q10 = DB::query("UPDATE bb_tema SET pregleda=pregleda+1 WHERE id=".$this->id);
	}

	public function getAllPosts($limit = 0, $offset = 0) {
		if ($limit>0 && $offset>0) 
			$limitsql = "LIMIT $offset, $limit";
		else if ($limit>0)
			$limitsql = "LIMIT $limit";
		else
			$limitsql = "";

		$q10 = DB::query("
SELECT p.id, p.naslov, UNIX_TIMESTAMP(p.vrijeme), p.osoba, p.tema, pt.tekst, o.ime, o.prezime, o.brindexa
FROM bb_post as p, bb_post_text as pt, osoba as o
WHERE p.tema=".$this->id." AND p.id=pt.post AND p.osoba=o.id
ORDER BY p.vrijeme ASC
$limitsql");
		$posts = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$fp = new ForumPost;
			$fp->id = $r10[0];
			$fp->subject = $r10[1];
			$fp->time = $r10[2];
			$fp->authorId = $r10[3];
			$fp->topicId = $r10[4];
			$fp->text = $r10[5];

			$fp->author = new Person;
			$fp->author->id = $r10[3];
			$fp->author->name = $r10[6];
			$fp->author->surname = $r10[7];
			$fp->author->studentIdNr = $r10[8];
			$fp->author->login = ""; // FIXME: problem je što može postojati više slogova u tabeli auth za jedan login a hoću da izbjegnem podupite

			array_push($posts, $fp);
		}
		
		return $posts;
	}

	public function addReply($forumPost) {
		$q10 = DB::query("select id from bb_post order by id desc limit 1");
		if (mysql_num_rows($q10)==0)
			$newId = 1;
		else
			$newId = mysql_result($q10,0,0)+1;

		$forumPost->id = $newId;
		$forumPost->topicId = $this->id;

		$q10 = DB::query("insert into bb_post set id=$newId, naslov='".$forumPost->subject."', vrijeme=NOW(), osoba=".$forumPost->authorId.", tema=".$this->id);

		$q20 = DB::query("select UNIX_TIMESTAMP(vrijeme) from bb_post where id=$newId");
		$forumPost->time = mysql_result($q20,0,0);

		$q30 = DB::query("insert into bb_post_text set post=$newId, tekst='".$forumPost->text."'");
		return $forumPost;
	}
}

?>