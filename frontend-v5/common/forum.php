<?

// COMMON/FORUM - modul koji pruža mogućnost grupe za diskusiju


// Kocka sa spiskom novih tema, za naslovnicu portala

function common_forum_ukratko($id_foruma, $linkNaForum) {
	// Za sada zabranjujemo pristup osim iz ovih modula
	if ($_REQUEST['sta'] != "student/projekti" && $_REQUEST['sta'] != "nastavnik/projekti") {
		niceerror("Pristup za sada zabranjen");
		return;
	}

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/forum/Forum.php");

	$forum = new Forum;
	$forum->id = $id_foruma; // FIXME!

	?>
		<div class="blockRow clearfix">
			<div class="block" id="latestPosts">
			<a class="blockTitle" href="<?=$linkNaForum ?>" title="Grupa za diskusiju">Najnoviji postovi</a>
			<div class="items">
			<?
				$latestPosts = $forum->getLatestPosts(4); // 4 najnovije poruke
				foreach ($latestPosts as $post) {
					?>
				<div class="item">
					<span class="date"><?=date('d.m H:i  ', $post->time) ?></span>
					<a href="<?=$linkNaForum . "&subaction=view&tid=".$post->topicId."#p".$post->id ?>" title="<?=$post->subject ?>" target="_blank"><?
					print Util::ellipsize($post->subject, 100); // Skrati na 100 znakova
					?></a>
					<span class="author"> - <?= $post->author->name . ' ' . $post->author->surname ?></span>
					<div class="desc"><?
					print Util::ellipsize($post->text, 200); // Skrati na 200 znakova
					?></div><!--desc-->
				</div><!--item-->	
					<?
				}
			?>
			
			
			</div><!--items-->
			</div><!--block-->
			
			
		</div><!--blockRow-->
	<?
} //function


function common_forum($id_foruma, $linkNaForum) {
	global $userid;

	require_once("Config.php");
	require_once(Config::$backend_path."core/Util.php");
	require_once(Config::$backend_path."lms/forum/Forum.php");

	$subaction = $_REQUEST['subaction'];
	$id = $_REQUEST['id']; // ID linka za edit/delete

	$forum = new Forum;
	$forum->id = $id_foruma; // FIXME!

	?>
	<h2>Grupa za diskusiju</h2>
	<div class="links clearfix" id="bl">
	<ul>
		<li><a href="<?=$linkNaForum ?>">Lista tema</a></li>
		<li><a href="<?=$linkNaForum . "&subaction=add" ?>">Nova tema</a></li>
	</ul>   
	</div>	
	<?

	if (!isset($subaction)) {
		$rowsPerPage = 20;
		$pageNum = 1;
		if(isset($_REQUEST['page'])) {
			$pageNum = $_REQUEST['page'];
		}
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		
		$threads = $forum->getAllTopics($rowsPerPage, $offset); // 4 najnovije poruke
		$numrows = $forum->getTopicsCount();

		?>
		<div id="threadList">
			<div class="threadRow caption clearfix">
			<div class="threadInfo">
				<div class="views">Pregleda</div><!--views-->
				<div class="lastReply">Zadnji odgovor</div><!--lastReply-->
			<div class="replies">Odgovora</div><!--replies-->
			</div><!--threadInfo-->
			<div class="title">Teme (<?=$numrows ?>)</div><!--title-->
		</div><!--threadRow caption-->
		<?

		foreach($threads as $key => $thread) {
			$fristPost = ForumPost::fromId($thread->firstPostId);
			$lastPost = ForumPost::fromId($thread->lastPostId);
			?>
			<div class="threadRow clearfix<? if ($key % 2) echo ' pattern'?>">
			<div class="threadInfo">
				<div class="views"><?=$thread->views ?></div><!--views-->
				<div class="lastReply"><?=date('d.m.Y H:i:s', $lastPost->time) ?><br /><?=$lastPost->author->name . ' ' . $lastPost->author->surname ?></div><!--lastReply-->
				<div class="replies"><?=$thread->getCountReplies() ?></div><!--replies-->
			</div><!--threadInfo-->
			<div class="title"><a href="<?=$linkNaForum . "&subaction=view&tid=".$thread->id ?>" title="<?=$fristPost->subject ?>"><?=$fristPost->subject ?></a></div><!--title-->
			<div class="author"><?=$fristPost->author->name . ' ' . $fristPost->author->surname ?></div><!--author-->		
			</div><!--threadRow caption-->
			<?php
		} //foreach thread

		?>
		</div><!--threadList-->
		<?
		
		$maxPage = ceil( $numrows / $rowsPerPage );
		$self = $linkPrefix;
		
		if ($maxPage > 0) {
			echo "<span class=\"newsPages\">";
			if ($pageNum > 1) {
				$page = $pageNum - 1;
				$prev = " <a href=\"$self&page=$page\">[Prethodna]</a> ";
				
				$first = " <a href=\"$self&page=1\">[Prva]</a> ";
			} 
			
			if ($pageNum < $maxPage) {
				$page = $pageNum + 1;
				$next = " <a href=\"$self&page=$page\">[Sljedeća]</a> ";
				
				$last = " <a href=\"$self&page=$maxPage\">[Zadnja]</a> ";
			} 
			
			echo $first . $prev . " Strana <strong>$pageNum</strong> od ukupno <strong>$maxPage</strong> " . $next . $last;
			echo "</span>"; //newsPages span	
		}
					
	} //subactin not set

	else if ($subaction == 'view') {
		$thread = ForumTopic::fromId(intval($_REQUEST['tid']));
		$thread->viewed();

		$posts = $thread->getAllPosts();
		?>
		<div id="fullThread">
		<?

		foreach ($posts as $post) {
			?>				
			<div class="post"><a name="p<?=$post->id ?>">
			<div id="post_<?=$post->id ?>_header" class="header clearfix" onclick="toggleShowPost('post_<?=$post->id ?>')">
			<div class="buttons">
				<a href="<?=$linkNaForum . "&subaction=add&tid=".$post->topicId."&id=".$post->id ?>" title="Odgovori na ovaj post">Odgovori</a>
			<?

			if ($post->authorId == $userid || $forum->ownerId == $userid) {
				?>
				| <a href="<?=$linkNaForum . "&subaction=edit&tid=".$post->topicId."&id=".$post->id ?>" title="Izmijeni post">Izmijeni</a>
				| <a href="<?=$linkNaForum . "&subaction=del&tid=".$post->topicId."&id=".$post->id ?>" title="Obriši post">Obriši</a>		
				<?
			}

			?>
			</div>
			<div class="maininfo">
				<div class="date"><?=date('d.m.Y H:i:s', $post->time) ?></div>
				<div class="author"><?=$post->author->name . ' ' . $post->author->surname ?></div> - 
				<div class="title"><?=$post->subject ?></div>
			</div>
			</div><!--header-->
			<div class="text" id="post_<?=$post->id ?>_text"><?=$post->text ?></div><!--text-->

			</div><!--post-->

			<?
		} //foreach post

		?>

		</div><!--fullThread-->
		<script type="text/javascript">
			function toggleShowPost(divID)
			{
				header = document.getElementById(divID + '_header');
				text = document.getElementById(divID + '_text');
				if (text.style.display == 'block' || text.style.display == '')
				{
					text.style.display = 'none';
					header.style.backgroundColor = '#F5F5F5';
					header.style.color = 'black';
				}
				else
				{
					text.style.display = 'block';
					header.style.backgroundColor = '#EEEEEE';
				}	
					
			}
		
			</script>
		
		<?
	} //subaction == view (thread)

	elseif ($subaction == 'add') {
		$threadID = intval($_REQUEST['tid']);
		if ($threadID <=0)
			$odgovor = false;
		else
			$odgovor = true;
			
		if ($odgovor == true) {
			$topic = ForumTopic::fromId($threadID);
			$post = ForumPost::fromId($id);

			// Provjeravamo validnost podataka
			if ($post->topicId != $threadID) return;
			if ($topic->forumId != $forum->id) return;
		}

		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			//get variables
			$naslov = trim(my_escape($_REQUEST['naslov']));
			$tekst = trim(my_escape($_REQUEST['tekst']));

			if (empty($naslov) || empty($tekst)) {
				niceerror('Unesite sva obavezna polja.');	
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			$fp = new ForumPost;
			$fp->subject = $naslov;
			$fp->authorId = $userid;
			$fp->text = $tekst;

			if ($odgovor) {
				$topic->addReply($fp);
				nicemessage('Novi odgovor uspješno dodan.');
				zamgerlog("dodao novi odgovor na diskusiju ID $threadID, projekat $projekat (pp$predmet)", 2);
				nicemessage('<a href="'. $linkNaForum . "&subaction=view&tid=$threadID" .'">Povratak.</a>');
			} else {
				$forum->startNewTopic($fp);
				nicemessage('Nova tema uspješno dodana.');
				zamgerlog("dodao novu temu na projektu $projekat (pp$predmet)", 2);
				nicemessage('<a href="'. $linkNaForum .'">Povratak.</a>');
			}
		} //submitted the form

		else {
			if ($odgovor) {
				$subject = $post->subject;
				if (substr($subject,0,3) != "Re:") $subject = "Re: $subject";
				?>	
				<h3>Novi odgovor</h3>
				<?=genform("POST", "addForm"); ?>
				<input type="hidden" name="tid" value="<?=$threadID?>"  />
				<div id="formDiv">
					Polja sa * su obavezna. <br />
				
					<div class="row">
					<span class="label">Naslov *</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$subject ?>" /></span> 
					</div>
					<div class="row">
					<span class="label">Tekst *</span>
					<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
					</div> 
							
					<div class="row">	
					<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
					</div>
				
				</div><!--formDiv-->
				
				</form>
				<?
			} else {
				?>	
				<h3>Nova tema</h3>
				<?=genform("POST", "addForm"); ?>
				<div id="formDiv">
					Polja sa * su obavezna. <br />
				
					<div class="row">
					<span class="label">Naslov *</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" /></span> 
					</div>
					<div class="row">
					<span class="label">Tekst *</span>
					<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"></textarea></span>
					</div> 
							
					<div class="row">	
					<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
					</div>
				
				</div><!--formDiv-->
				
				</form>
				<?
			}
		} //not submitted yet

	} //subaction == addThread

	elseif ($subaction == 'edit') {
		$post = ForumPost::fromId($id);
		if ($post->authorId != $userid && $forum->ownerId != $userid) {
			zamgerlog("pokusava urediti post $id a nije autor, projekat $projekat (pp$predmet)", 3);
			return;
		}

		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			//get variables
			$naslov = trim(my_escape($_REQUEST['naslov']));
			$tekst = trim(my_escape($_REQUEST['tekst']));

			if (empty($naslov) || empty($tekst)) {
				niceerror('Unesite sva obavezna polja.');	
				nicemessage('<a href="javascript:history.back();">Povratak.</a>');
				return;
			}
			
			$post->subject = $naslov;
			$post->text = $tekst;
			$post->update();

			nicemessage('Post uspješno izmijenjen.');
			zamgerlog("izmijenio post na projektu $projekat (pp$predmet)", 2);
			nicemessage('<a href="'. $linkNaForum . "&subaction=view&tid=". $post->topicId .'">Povratak.</a>');
		}

		else {
			?>
			<h3>Izmijeni post</h3>
			<?=genform("POST", "editForm"); ?>
			<div id="formDiv">
				Polja sa * su obavezna. <br />
			
				<div class="row">
					<span class="label">Naslov *</span>
					<span class="formw"><input name="naslov" type="text" id="naslov" size="70" value="<?=$post->subject ?>" /></span> 
				</div>
				<div class="row">
					<span class="label">Tekst *</span>
					<span class="formw"><textarea name="tekst" cols="60" rows="15" wrap="physical" id="tekst"><?=$post->text ?></textarea></span>
				</div> 
				
				<div class="row">	
					<span class="formw" style="margin-left:150px;"><input name="submit" type="submit" id="submit" value="Potvrdi"/></span>
				</div>
			
			</div><!--formDiv-->
			
			</form>
			<?
		}
		
	} //subaction == edit

	elseif ($subaction == 'del') {
		$post = ForumPost::fromId($id);
		if ($post->authorId != $userid && $forum->ownerId != $userid) {
			zamgerlog("pokusava urediti post $id a nije autor, projekat $projekat (pp$predmet)", 3);
			return;
		}


		//delete item
		if (isset($_REQUEST['submit']) && check_csrf_token()) {
			$post->delete();
			nicemessage('Uspješno ste obrisali post.');	
			zamgerlog("obrisao post na projektu $projekat (pp$predmet)", 2);

			nicemessage('<a href="'. $linkNaForum . "&subaction=view&tid=". $post->topicId .'">Povratak.</a>');
		} else {
			?>
			<h3>Brisanje posta</h3>
			<?=genform("POST", "deleteForm"); ?>
			Da li ste sigurni da želite obrisati ovaj post?<br />
			<input name="submit" type="submit" id="submit" value=" Obriši post "/>
			<input type="button" onclick="javascript:window.location.href = '<?=$linkNaForum ?>';" value=" Nemoj brisati "/>
			</form>
			<?
		}
	} //subaction == del
}


?>
