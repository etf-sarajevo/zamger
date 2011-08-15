<?php

// Modul: lms/forum
// Klasa: Forum
// Opis: jedan forum

require_once(Config::$backend_path."core/DB.php");
require_once(Config::$backend_path."core/Person.php");

require_once(Config::$backend_path."lms/forum/ForumTopic.php");
require_once(Config::$backend_path."lms/forum/ForumPost.php");

// FIXME: u bazi ne postoji tabela forum
// polje forum tabele topic se zove "projekat"

class Forum {
	public $id;
	public $ownerid;

	// U bazi još uvijek ne postoji tabela forum :(

	public function getAllTopics($limit = 0, $offset = 0) {
		if ($limit>0 && $offset>0) 
			$limitsql = "LIMIT $offset, $limit";
		else if ($limit>0)
			$limitsql = "LIMIT $limit";
		else
			$limitsql = "";

		$q10 = DB::query("select t.id, FROM_UNIXTIME(t.vrijeme), t.prvi_post, t.zadnji_post, t.pregleda, t.osoba from bb_tema as t, bb_post as p where t.projekat=".$this->id." and t.zadnji_post=p.id order by p.vrijeme desc $limitsql");
		$topics = array();
		while ($r10 = mysql_fetch_row($q10)) {
			$ft = new ForumTopic;
			$ft->id = $r10[0];
			$ft->lastUpdate = $r10[1];
			$ft->firstPostId = $r10[2];
			$ft->lastPostId = $r10[3];
			$ft->views = $r10[4];
			$ft->authorId = $r10[5];
			$ft->forumId = $this->id;

			array_push($topics, $ft);
		}
		
		return $topics;
	}


	public function getTopicsCount() {
		$q10 = DB::query("SELECT count(*) FROM bb_tema WHERE projekat=".$this->id);
		return mysql_result($q10,0,0);
	}

	public function getLatestPosts($limit = 0) {
		if ($limit>0) $limitsql = "LIMIT $limit"; else $limitsql="";
		$q10 = DB::query("
SELECT p.id, p.naslov, UNIX_TIMESTAMP(p.vrijeme), p.osoba, p.tema, pt.tekst, o.ime, o.prezime, o.brindexa
FROM bb_post as p, bb_post_text as pt, bb_tema as t, osoba as o
WHERE t.projekat=".$this->id." AND p.tema=t.id AND p.id=pt.post AND p.osoba=o.id
ORDER BY p.vrijeme DESC
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

	public static function startNewTopic($forumPost) {
		$q10 = DB::query("select id from bb_tema order by id desc limit 1");
		if (mysql_num_rows($q10)==0)
			$newId = 1;
		else
			$newId = mysql_result($q10,0,0)+1;

		$ft = new ForumTopic;
		$ft->id = $newId;
		$ft->authorId = $forumPost->authorId;
		$ft->forumId = $this->id;

		$fp = $ft->addReply($forumPost);
		$ft->firstPostId = $fp->id;
		$ft->lastPostId = $fp->id;
		
		$q20 = DB::query("insert into bb_tema set id=$newId, vrijeme=NOW(), prvi_post=".$ft->firstPostId.", zadnji_post=".$ft->lastPostId.", pregleda=0, osoba=".$ft->authorId.", projekat=".$ft->forum);
		
		$q30 = DB::query("select UNIX_TIMESTAMP(vrijeme) from bb_tema where id=$newId");
		$ft->time = mysql_result($q30,0,0);

		return $ft;
	}
}

?>