<?

# Biblioteka korisnih funkcija koje koristim u svojim skriptama
# ---- Copyleft (c) Vedran Ljubović 
# v0.0.2 (2006/09/30) + dodana funkcija nicesize()
# v0.0.3 (2006/10/03) + bssort() za sortiranje bs jezika, nicemessage(), globalna varijabla $lv_debug
# v0.0.4 (2006/12/13) + db_dropdown(), db_form(), genform(), genuri(), db_list(), db_submit() funkcije za brzu administraciju, datectrl() form kontrola za datum
# v0.0.5 (2007/03/06) + db_grid(), uveden _lv_nav_ reqvar radi ispravljanja buga sa kombinacijom db_form+db_list, više unaprjeđenja u drugim db funkcijama
# v0.0.6 (2007/03/12) + nova funkcija login(), ispravke u session mgmt, ukinute globalne varijable osim $_lv_, dodan htmlspecialchars u my_escape() radi Type 2 XSS napada
# v0.0.7 (2007/03/30) + podrška za autonumber vs. nonautonumber
# v0.0.8 (2007/04/27) + db_dropdown() ipak nije forma i ne treba se ponašati kao forma

# + (ZADACHA-MGR) Jedinstvena auth tabela za admine (ovo će postati dio v0.0.4)



// ----------- GLOBALNE VARIJABLE ZA ZAMGER3

$_lv_["debug"]=1;
//builtincss();
$system_path = "/srv/www/web18";
$file_path = "/var/www/folder";



// ------------ FUNKCIJE

if (!$_lv_) $_lv_ = array(); // Prevent PHP warnings 


function dbconnect() {
	// Default database
	//dbconnect2("localhost","root","","zamger3");
	dbconnect2("localhost","vedran_studenti","itneduts","vedran_zamger3");
}

function dbconnect2($dbhost,$dbuser,$dbpass,$dbdb) {
	global $__lv_connection,$_lv_;

	if (!($__lv_connection = mysql_connect($dbhost, $dbuser, $dbpass))) {
		if ($_lv_["debug"]) biguglyerror(mysql_error());
		exit;
	}
	if (!mysql_select_db($dbdb)) {
		if ($_lv_["debug"]) biguglyerror(mysql_error());
		exit;
	}
}

function dbdisconnect() {
	global $__lv_connection;
	
	mysql_close($__lv_connection);
}

function myquery($query) {
	global $_lv_;

	if ($r = @mysql_query($query)) {
		return $r;
	}
	
	# Error handling
	if ($_lv_["debug"])
		print "<br/><hr/><br/>MYSQL query:<br/><pre>".$query."</pre><br/>MYSQL error:<br/><pre>".mysql_error()."</pre>";
	exit;
}

function niceerror($error) {
	print "<p><font color='red'><b>GREŠKA: $error</b></font></p>";
}

function biguglyerror($error) {
	print "<center><h2><font color='red'><b>GREŠKA: $error</b></font></h2></center>";
}

function nicemessage($error) {
	print "<p><font color='green'><b>$error</b></font></p>";
}

function time2mysql($timestamp) { return date("Y-m-d H:i:s",$timestamp); }
function mysql2time($v) { 
	$g = substr($v,0,4); $mj=substr($v,5,2); $d=substr($v,8,2); 
	$h=substr($v,11,2); $mi=substr($v,14,2); $s=substr($v,17,2);
	return mktime($h,$mi,$s,$mj,$d,$g);
}

// Escape stringova radi koristenja u mysql upitima - kopirao sa php.net
function my_escape($value) {
	// Convert special HTML chars to protect against XSS
	// If chars are needed for something, escape manually
	$value = htmlspecialchars($value);
	// Stripslashes
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	// Quote if not a number or a numeric string
	if (!is_numeric($value)) {
		$value = mysql_real_escape_string($value);	// Detecting quotes later is a pain
	}
	return $value;
}


// --- SESSION MGMT

function login($pass) {
	# STATUS:
	#  0 - OK
	#  1 - nepoznat login
	#  2 - password ne odgovara 

	global $userid,$admin,$login;

	$q1 = myquery("select id,password,admin from auth where login='$login'");
	if (mysql_num_rows($q1)<=0)
		return 1;
	else {
		$userid = mysql_result($q1,0,0);
		$pass2 = mysql_result($q1,0,1);
		$admin = mysql_result($q1,0,2);
	
		if ($pass != $pass2)
			return 2;
	}

	// All OK, start session
	session_start();
	$_SESSION['login']=$login;
	session_write_close();
}


function check_cookie() {
	global $userid,$admin,$login;

	session_start();
	$login = my_escape($_SESSION['login']);
	if (!preg_match("/[a-zA-Z0-9]/",$login)) header("Location: index.php?greska=1");

	$q1 = myquery("select id,admin from auth where login='$login'");
	if (mysql_num_rows($q1)>0) {
		$userid = mysql_result($q1,0,0);
		$admin = mysql_result($q1,0,1);
	} else {
		header("Location: index.php?greska=1");
	}
}

function logout() {
	$_SESSION = array();
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();

	?><center><h1>Bye-bye</h1></center>
	<script language="JavaScript">
		window.location = "index.php";
	</script>
	<?

}

# String sa lijepim ispisom veličine u Kibibajtima
function nicesize($size) {
	if ($size>1024*1024*1024) {
		return intval($size/(1024*1024*1024/10))/10 . " GB";
	} else if ($size>1024*1024*10) {
		return intval($size/(1024*1024)) . " MB";
	} else if ($size>1024*1024) {
		return intval($size/(1024*1024/10))/10 . " MB";
	} else if ($size>1024*10) {
		return intval($size/1024) . " kB";
	} else if ($size>1024) {
		return intval($size / (1024/10))/10 . " kB";
	} else {
		return $size . " B";
	}
}

# Sortiranje za bosanski jezik
function bssort($a, $b) {
	$a=strtolower($a); $b=strtolower($b);
	static $abeceda = array("a","A","b","B","c","C","č","Č","ć","Ć","d","đ","Đ","e","f","g","h","i","j","k","l","m","n","o","p", "q","r","s","š","Š","t","u","v", "w","x","y","z","ž","Ž");
	$min = (strlen($a)<strlen($b)) ? strlen($a) : strlen($b);
	for ($i=0; $i<$min; $i++) {
		$ca = substr($a,$i,1); if (ord($ca)>128) $ca = substr($a,$i,2);
		$cb = substr($b,$i,1); if (ord($cb)>128) $cb = substr($b,$i,2);
		$k=array_search($ca,$abeceda); $l=array_search($cb,$abeceda);
		//print "K: $k L: $l ZLJ: ".$ca. "       ";
		if ($k<$l) return -1; if ($k>$l) return 1;
	}
	if (strlen($a)<strlen($b)) return -1;
	return 1;
}


# Logiranje
function logthis($event) {
	global $_lv_;

/*	if (!$lv_debug) return;
	$lv_logfile = fopen($system_path."/debug12874.log",'a');
	fwrite($lv_logfile, date("[Y-m-d H:i:s]")." - $event\n");
	fclose($lv_logfile);
	return;*/

	// Database logging
	myquery("insert into log set dogadjaj='".my_escape($event)."'");
}


# Prikaz datuma za formular
function datectrl($d,$m,$g,$prefix) {
	$result = '<select name="'.$prefix.'day">';
	for ($i=1; $i<=31; $i++) {
		$result .= '<option value="'.$i.'"';
		if ($i==$d) $result .= " SELECTED";
		$result .= '>'.$i.'</option>';
	}
	$result .= '</select>  <select name="'.$prefix.'month">';
	for ($i=1; $i<=12; $i++) {
		$result .= '<option value="'.$i.'"';
		if ($i==$m) $result .= " SELECTED";
		$result .= '>'.$i.'</option>';
	}
	$result .= '</select>  <select name="'.$prefix.'year">';
	for ($i=1990; $i<=date("Y")+10; $i++) { // We go 10 yrs into future...
		$result .= '<option value="'.$i.'"';
		if ($i==$g) $result .= " SELECTED";
		$result .= '>'.$i.'</option>';
	}
	$result .= '</select>';
	return $result;
}


# genform - pravi zaglavlje forme sa hidden poljima
function genform($method) {
	$result = '<form action="'.$_SERVER['PHP_SELF'].'" method="'.$method.'">'."\n";
	foreach ($_REQUEST as $key=>$value) {
		if (substr($key,0,4) != "_lv_") 
		$result .= '<input type="hidden" name="'.$key.'" value="'.$value.'">'."\n";
	}
	return $result;
}


# genuri - pravi link na isti dokument sa ukodiranim varijablama
function genuri() {
	$result = $_SERVER['PHP_SELF']."?";
	foreach ($_REQUEST as $key=>$value) {
		// Prevent revealing session
		if ((substr($key,0,4) != "_lv_") && $key != "PHPSESSID")
		$result .= urlencode($key).'='.urlencode($value).'&';
	}
	if (substr($result,strlen($result)-1) == "&") 
		$result = substr($result,0,strlen($result)-1); // drop last &
	return $result;
}



# interna funkcija za parsiranje tabele
function __lv_parsetable($table) {
	global $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate, $__lv_lastparsedtable;

	// If this table is already parsed, we keep old data
	if (!$table || $__lv_lastparsedtable == $table) return;
	$__lv_lastparsedtable=$table;

	// Forget old data
	$__lv_cn=array();
	$__lv_ct=array();
	$__lv_cs=array();

	// Execute show create to get table columns
	$q200 = myquery("show create table $table");
	$__lv_showcreate = mysql_result($q200,0,1);
	foreach (explode("\n", $__lv_showcreate) as $line) {
		if (preg_match("/`(\w+)` (\w+)\((\d+)\)/", $line, $matches)) {
			$__lv_cn[] = $matches[1]; // adds at the end
			$__lv_ct[] = $matches[2];
			$__lv_cs[] = $matches[3];

		// Fields with unspecified size:
		} else if (preg_match("/`(\w+)` (\w+)/", $line, $matches)) {
			$__lv_cn[] = $matches[1]; // adds at the end
			$__lv_ct[] = $matches[2];
			$__lv_cs[] = 3; // whatever, it works
		}
	}
}



# db_submit() - funkcija za obradu poslanih podataka
function db_submit() {
	global  $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;
	global $__lv_submitted;

	// Prevent executing twice
	if ($__lv_submitted == 1) return;
	$__lv_submitted=1;

	// Check if submitted data is ok (we only use POST)
	$action = $_POST['_lv_action'];
	$table = my_escape($_POST['_lv_table']);

	if (!$table) return;
	if ($action != "add" && $action != "edit" && $action != "delete") return;
	// This enables us to have custom names for buttons
	if ($_POST['_lv_action_delete']) $action="delete"; 

	// Get list of table columns
	__lv_parsetable($table);


	// Construct SQL query (later we will decide between insert and update)
	$sql = "";
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		$type = $__lv_ct[$i];
		$data = $_POST["_lv_column_".$name];
		// FIXME: do not submit empty values

		//if ($_POST['_lv_where_'.$name]) continue;

		if ($sql != "") $sql .= ", ";

		// Dates parsing... we are splitting dates into separate fields
		if ($type == "date") {
			$d = $_POST["_lv_column_".$name."_day"];
			$m = $_POST["_lv_column_".$name."_month"];
			$y = $_POST["_lv_column_".$name."_year"];
			$sql .= "$name='".time2mysql(mktime(0,0,0,$m,$d,$y))."'";
		}
		else if ($type == "datetime") {
			$d = $_POST["_lv_column_".$name."_day"];
			$m = $_POST["_lv_column_".$name."_month"];
			$y = $_POST["_lv_column_".$name."_year"];
			$h = $_POST["_lv_column_".$name."_hour"];
			$mi = $_POST["_lv_column_".$name."_minute"];
			$se = $_POST["_lv_column_".$name."_second"];
			$sql .= "$name='".time2mysql(mktime($h,$mi,$se,$m,$d,$y))."'";
		}

		// boolean data
		else if ($data == "on" && $type == "tinyint")
			$sql .= "$name=1";
		// other
		else if ($type == "tinyint" || $type=="int" || $type=="smallint" || $type=="mediumint" || $type=="bigint")
			$sql .= "$name=".intval($data);
		else if ($type == "float" || $type=="double" || $type=="decimal")
			$sql .= "$name=".floatval($data);
		else
			$sql .= "$name='".my_escape($data)."'";
	}


	// Insert or update?
	if ($action == "add") {
		$sql = "insert into $table set $sql";
	} else {
		// Generate query
		if ($action == "delete")
			$sql = "delete from $table where ";
		else
			$sql = "update $table set $sql where ";
		$n=0;
		foreach ($_POST as $key => $value) {
			if (substr($key,0,10) == "_lv_where_") {
				if ($n>0) $sql .= "and ";
				$sql .= my_escape(substr($key,10))."='".my_escape($value)."'";
				$n++;
			}
		}
		if ($n==0) {
			niceerror("Ne mogu izvršiti upit jer nema nijedne WHERE vrijednosti...");
			return;
		}
	}

//print "submit SQL: $sql<br/>";

	// Do the update
	myquery($sql);
}



# Generise drop-down listu za datu tabelu
function db_dropdown($table,$selected) {
	global $_lv_; // where
	global $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;
	// Update database with submitted data - in case the $table was changed!
	db_submit(); 

	// Parse table columns from "show create" query
	__lv_parsetable($table);


	// Find ID field
	if (preg_match("/PRIMARY KEY \(`(\w+)`\)/",$__lv_showcreate,$matches)) {
		$id = $matches[1];
	} else if (in_array("id",$__lv_cn)) {
		$id = "id";
	} else {
		// Use first column - it will probably be broken anyway
		$id = $__lv_cn[0];
	}

	// Find name field
	$name="";
	if (in_array("name",$__lv_cn)) {
		$name = "name";
	} else if (in_array("naziv",$__lv_cn)) {
		$name = "naziv";
	} else if (in_array("ime",$__lv_cn)) {
		$name = "ime";
	} else {
		// First varchar
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_ct[$i] == "varchar") {
				$name=$__lv_cn[$i];
			}
		}
		// First text
		if ($name == "")
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_ct[$i] == "text") {
				$name=$__lv_cn[$i];
			}
		}
		// First field other then ID
		if ($name == "")
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_cn[$i] != $id) {
				$name=$__lv_cn[$i];
			}
		}
		if ($name == "") $name=$id;
	}

	// Find surname field
	$surname = "";
	if (in_array("surname",$__lv_cn)) {
		$surname = "surname";
	} else if (in_array("prezime",$__lv_cn)) {
		$surname = "prezime";
	}

	// Get default value from WHERE
//	if (strlen($selected)<1) $selected = $_lv_["where:$id"];
//	if (strlen($selected)<1) $selected = $_REQUEST["_lv_where_$id"];

	// Finally - query
	if ($surname == "")
		$sql = "select $id,$name from $table";
	else
		$sql = "select $id,$name,$surname from $table";
	// Construct where
	$n=0;
	foreach ($_lv_ as $key => $value) {
		if (substr($key,0,6) == "where:" && substr($key,6) != $id) {
			// Check if mentioned column exists
			// (This is possible e.g. if db_dropdown is called from db_form)
			$found=0;
			for ($i=0; $i<count($__lv_cn); $i++) {
				if ($__lv_cn[$i] == substr($key,6)) $found=1; 
			}
			if ($found==0) continue;

			// Add WHERE to SQL
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= my_escape(substr($key,6))."='".my_escape($value)."'";
			$n++;
		}
	}

	// Order by (hack)
	if ($surname == "")
		$sql .= " order by $name";
	else
		$sql .= " order by $name,$surname";

	$q101 = myquery($sql);

	// Construct output
	$result = '<select name="_lv_column_'.$table.'">'."\n";
	$found=0;
	while ($r101 = mysql_fetch_row($q101)) {
		$result .= '<option value="'.$r101[0].'"';
		if ($r101[0]==$selected || $r101[1]==$selected) {
			$result .= ' SELECTED';
			$found=1;
		}
		$result .= '>'.$r101[1];
		if ($surname != "") $result .= " ".$r101[2];
		$result .= '</option>'."\n";
	}
//	if ($found == 0)
//		$result .= '<option value="'.$selected.'" SELECTED>'.$selected.'</option>'."\n";
	$result .= '</select>';
	return $result;
}


# db_form - generise formular za tabelu sa jednim slogom (evt. koristeći where)
function db_form($table) {
	global $_lv_;
	global $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;

	// Update database with submitted data
	db_submit();

	// Parse table columns from "show create" query
	__lv_parsetable($table);


	// Generate form header with hidden fields
	$result = genform("POST");
	$result .= '<input type="hidden" name="_lv_table" value="'.$table.'">'."\n";

	// List tables - used to find foreign keys
	$q200 = myquery("show tables");
	while ($r200 = mysql_fetch_row($q200)) 
		$tables[] = $r200[0];


	// Query database to get default form values
	$sql = "select * from $table";

	// Additional parameters to query
	$n = 0;
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		// Get WHERE from $_lv_
//		if ((strlen($_lv_["where:$name"])>0) || ($_REQUEST["_lv_nav_$name"])) {
		if (strlen($_lv_["where:$name"])>0) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= "$name='".my_escape($_lv_["where:$name"])."'";
			$n++;
		}
		// Also find stuff from list / navigation / previously submitted form
		if ($_REQUEST["_lv_nav_$name"]) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= "$name='".my_escape($_REQUEST["_lv_nav_$name"])."'";
			$n++; $nav=1;
		}
	}

	if ($nav==1 || $_lv_["forceedit"]==1) {
		$result .= '<input type="hidden" name="_lv_action" value="edit">'."\n";
		$q202 = myquery($sql);
		$r202 = mysql_fetch_assoc($q202);
	} else {
		$result .= '<input type="hidden" name="_lv_action" value="add">'."\n";
	}


	// Display form
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		$type = $__lv_ct[$i];
		$size = $__lv_cs[$i];
		$label = strtoupper(substr($name,0,1)).strtolower(substr($name,1));
		$label = str_replace("_"," ",$label);
		if ($_lv_["label:$name"]) $label=$_lv_["label:$name"];
		if ($size>30) $size=30; // not practical to have size>30

		// ID and fields given in WHERE are always hidden 
		if ($name=="id") {
			if ($nav != 1 && $_lv_["forceedit"] != 1 && $__lv_cai[$name] != 1) {
				// auto_increment fields (__lv_cai) should not be added at all... 
				// for others, use the first unused value
				$q203 = myquery("select $name from $table order by $name desc limit 1");
				$r202[$name] = mysql_result($q203,0,0)+1;
			}

			$result .= '<input type="hidden" name="_lv_where_id" value="'.$r202[$name].'">'."\n";
			// We need to resubmit data for add
			$result .= '<input type="hidden" name="_lv_column_id" value="'.$r202[$name].'">'."\n";
		} else if ($_lv_["where:$name"]) {
			$result .= '<input type="hidden" name="_lv_where_'.$name.'" value="'.$_lv_["where:$name"].'">'."\n";
			// We need to resubmit data for add
			$result .= '<input type="hidden" name="_lv_column_'.$name.'" value="'.$_lv_["where:$name"].'">'."\n";

		// Fields that we request to be hidden
		} else if ($_lv_["hidden:$name"]==1) {
			$result .= '<input type="hidden" name="_lv_column_'.$name.'" value="'.$r202[$name].'">'."\n";

		// find foreign keys
		} else if (in_array($name,$tables)) {
			$result .= $label.': '.db_dropdown($name,$r202[$name])."<br/><br/>\n";
			// db_dropdown will destroy __lv_c* ...
			__lv_parsetable($table);

		// Various column types

		} else if ($type == "varchar") {
			$result .= $label.': <input type="text" name="_lv_column_'.$name.'" size="'.$size.'" value="'.$r202[$name].'"><br/><br/>'."\n";

		} else if ($type == "text") {
			$result .= $label.':<br/><textarea name="_lv_column_'.$name.'" rows="10" cols="50">'.$r202[$name].'</textarea><br/><br/>'."\n";

		} else if ($type == "date") {
			// Parse date 
			if ($r202[$name]) {
				$mytime = mysql2time($r202[$name]);
			} else {
				$mytime = time(); // Set time to now
			} 
			$d = date('d',$mytime);
			$m = date('m',$mytime);
			$Y = date('Y',$mytime);
			
			$result .= $label.': '.datectrl($d,$m,$Y,"_lv_column_$name"."_")."<br/><br/>\n";

		} else if ($type == "datetime") { 
			// Parse date 
			if ($r202[$name]) {
				$mytime = mysql2time($r202[$name]);
			} else {
				$mytime = time(); // Set time to now
			} 
			$d = date('d',$mytime);
			$m = date('m',$mytime);
			$Y = date('Y',$mytime);
			$h = date('H',$mytime);
			$mi = date('i',$mytime);
			$se = date('s',$mytime);
			
			$result .= $label.': '.datectrl($d,$m,$Y,"_lv_column_$name"."_")."\n";
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_hour" value="'.$h.'">:';
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_minute" value="'.$mi.'">:';
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_second" value="'.$se.'"><br/><br/>'."\n";

		} else if ($type=="tinyint" && $size=="1") { 
			// assume boolean
			$result .= '<input type="checkbox" name="_lv_column_'.$name.'"';
			if ($r202[$name] == "1")
				$result .= ' CHECKED';
			$result .= '> '.$label.'<br/><br/>'."\n";

		} else if ($type=="int" || $type=="tinyint" || $type=="smallint" || $type=="bigint" || $type=="float") {
			// classic numeric
			$result .= $label.': <input type="text" name="_lv_column_'.$name.'" size="'.$size.'" value="'.$r202[$name].'"><br/><br/>'."\n";

		} else {
			$result .= "Unknown type: '$type'<br/><br/>\n";
		}
	}

	// Buttons and form ending
	$result .= '<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">';

	// Delete button will be displayed only if we are editing
	if ($nav==1 || $_lv_["forceedit"]==1)
		$result .= '<input type="submit" name="_lv_action_delete" value=" Obriši  ">'."\n";

	$result .= '</form>'."\n";

	return $result;
}


# db_list - bullet lista elemenata tabele
# Generise drop-down listu za datu tabelu
function db_list($table,$selected) {
	global $_lv_, $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;

	// Update database with submitted data
	db_submit(); 

	// Parse table columns from "show create" query
	__lv_parsetable($table);

	// Find ID field
	if (preg_match("/PRIMARY KEY\s+\(`(\w+)`\)/",$__lv_showcreate,$matches)) {
		$id = $matches[1];
	} else if (in_array("id",$__lv_cn)) {
		$id = "id";
	} else {
		// Use first column - it will probably be broken anyway
		$id = $__lv_cn[0];
	}

	// Find name field
	$name="";
	if (in_array("name",$__lv_cn)) {
		$name = "name";
	} else if (in_array("naziv",$__lv_cn)) {
		$name = "naziv";
	} else if (in_array("ime",$__lv_cn)) {
		$name = "ime";
	} else {
		// First varchar
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_ct[$i] == "varchar") {
				$name=$__lv_cn[$i];
			}
		}
		// First text
		if ($name == "")
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_ct[$i] == "text") {
				$name=$__lv_cn[$i];
			}
		}
		// First field other then ID
		if ($name == "")
		for ($i=0; $i<count($__lv_cn); $i++) {
			if ($__lv_cn[$i] != $id) {
				$name=$__lv_cn[$i];
			}
		}
		if ($name == "") $name=$id;
	}

	// Find surname field
	$surname = "";
	if (in_array("surname",$__lv_cn)) {
		$surname = "surname";
	} else if (in_array("prezime",$__lv_cn)) {
		$surname = "prezime";
	}

	// Get default value from WHERE
// ?	if (!$selected) $selected = $_lv_["where:$id"];
// ?	if (!$selected) $selected = $_lv_["where:$name"];
	if (!$selected) $selected = $_REQUEST["_lv_where_$id"];
	if (!$selected) $selected = $_REQUEST["_lv_where_$name"];

	// Finally - query
	if ($surname == "")
		$sql = "select $id,$name from $table";
	else
		$sql = "select $id,$name,$surname from $table";
	foreach ($_lv_ as $key => $value) {
		if (substr($key,0,6) == "where:" && substr($key,6) != $id && substr($key,6) != $name) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= my_escape(substr($key,6))."='".my_escape($value)."'";
			$n++;
		}
	}
	$q101 = myquery($sql);

	// Construct output
	$result = '<ul>'."\n";
	if (mysql_num_rows($q101)<1)
		$result .= '<li>Nema rezultata</li>'."\n";

	$uri = genuri();

	while ($r101 = mysql_fetch_row($q101)) {
		$result .= '<li>';
		$i = $r101[0];
		$n = $r101[1];
		$nav = $_REQUEST["_lv_nav_$id"];
		if ($surname != "") $n .= " ".$r101[2];
		if ($i==$selected || $n==$selected || $nav==$i)
			$result .= $n;
		else
			$result .= '<a href="'.$uri.'&_lv_nav_'.$id.'='.$i.'">'.$n.'</a>';
		$result .= '</li>'."\n";
	}
	$result .= '</ul>'."\n";
	// Link for new entry
/*	while (preg_match("/_lv_where_.*?=.*?[\&^]/",$uri))
		preg_replace("/(_lv_where_.*?=.*?)[\&^]/","",$uri);*/
	$result .= '<p><a href="'.$uri.'">Unesi novu</a>';
	return $result;
}




# db_grid - generise HTML tabelu za DB tabelu :)
function db_grid($table) {
	global $_lv_;
	global $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;

	// Update database with submitted data
	db_submit();

	// Parse table columns from "show create" query
	__lv_parsetable($table);


	// Generate form header with hidden fields - this will be used for each row
	$form_header = genform("POST");
	$form_header .= '<input type="hidden" name="_lv_table" value="'.$table.'"> <input type="hidden" name="_lv_action" value="edit">'."\n";

	// List tables - used to find foreign keys
	$q200 = myquery("show tables");
	while ($r200 = mysql_fetch_row($q200)) 
		$tables[] = $r200[0];


	// Query database to get default form values
	$sql = "select * from $table";
	$n = 0;
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		// Get WHERE from $_lv_
		if (strlen($_lv_["where:$name"])>0) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= "$name='".my_escape($_lv_["where:$name"])."'";
			$n++;
		}
		// We are not interested in _lv_where... 
	}

	// Get ORDER BY from $_lv_
	foreach ($_lv_ as $key => $value) {
		if ($key == "orderby") {
			$sql .= " order by ".$value;
			break;
		}
	}

	// Display table header
	$result .= '<table border="0" cellspacing="0" cellpadding="3">'."\n";
	$result .= '<tr bgcolor="#bbbbbb">'."\n";
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		$type = $__lv_ct[$i];
		$label = strtoupper(substr($name,0,1)).strtolower(substr($name,1));
		$label = str_replace("_"," ",$label);
		if ($_lv_["label:$name"]) $label=$_lv_["label:$name"];

		// ID and fields given in WHERE are always hidden 
		if (($name != "id") && (!($_lv_["where:$name"])))
			$result .= "<th>$label</th>\n";
	}
	$result .= "<th>&nbsp;</th>\n"; // Extra column for submit button
	$result .= "</tr>\n";

	// Table contents
	$q202 = myquery($sql);
	$color = 0;
	while ($r202 = mysql_fetch_assoc($q202)) {
		$result .= "$form_header\n";
		if ($color==0) {
			$result .= "<tr>\n";
			$color = 1;
		} else {
			$result .= '<tr bgcolor="#efefef">'."\n";
			$color = 0;
		} 


	// Display form
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		$type = $__lv_ct[$i];
		$size = $__lv_cs[$i];
		if ($size>15) $size=15; // not practical to have size>15

		// ID and fields given in WHERE are always hidden 
		if ($name=="id") {
			// FIXME: Value of 0 suggests that this is an autonumber field
			if (intval($r202[$name]) != 0) {
				$result .= '<input type="hidden" name="_lv_where_id" value="'.$r202[$name].'">'."\n";
				// We need to resubmit data for add
				$result .= '<input type="hidden" name="_lv_column_id" value="'.$r202[$name].'">'."\n";
			}
		} else if ($_lv_["where:$name"]) {
			$result .= '<input type="hidden" name="_lv_where_'.$name.'" value="'.$_lv_["where:$name"].'">'."\n";
			// We need to resubmit data for add
			$result .= '<input type="hidden" name="_lv_column_'.$name.'" value="'.$_lv_["where:$name"].'">'."\n";

		// find foreign keys
		} else if (in_array($name,$tables)) {
			$result .= "<td>".db_dropdown($name,$r202[$name])."</td>\n";
			// db_dropdown will destroy __lv_c* ...
			__lv_parsetable($table);

		// Various column types

		} else if ($type == "varchar") {
			$result .= '<td><input type="text" name="_lv_column_'.$name.'" size="'.$size.'" value="'.$r202[$name].'"></td>'."\n";

		} else if ($type == "text") {
			$result .= '<td><textarea name="_lv_column_'.$name.'" rows="5" cols="20">'.$r202[$name].'</textarea></td>'."\n";

		} else if ($type == "date") {
			// Parse date 
			if ($r202[$name]) {
				$mytime = mysql2time($r202[$name]);
			} else {
				$mytime = time(); // Set time to now
			} 
			$d = date('d',$mytime);
			$m = date('m',$mytime);
			$Y = date('Y',$mytime);
			
			$result .= '<td>'.datectrl($d,$m,$Y,"_lv_column_$name"."_")."</td>\n";

		} else if ($type == "datetime") { 
			// Parse date 
			if ($r202[$name]) {
				$mytime = mysql2time($r202[$name]);
			} else {
				$mytime = time(); // Set time to now
			} 
			$d = date('d',$mytime);
			$m = date('m',$mytime);
			$Y = date('Y',$mytime);
			$h = date('H',$mytime);
			$mi = date('i',$mytime);
			$se = date('s',$mytime);
			
			$result .= '<td>'.datectrl($d,$m,$Y,"_lv_column_$name"."_")."\n";
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_hour" value="'.$h.'">:';
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_minute" value="'.$mi.'">:';
			$result .= '<input type="text" size="2" name="_lv_column_'.$name.'_second" value="'.$se.'"></td>'."\n";

		} else if ($type=="tinyint" && $size=="1") { 
			// assume boolean
			$result .= '<td><input type="checkbox" name="_lv_column_'.$name.'"';
			if ($r202[$name] == "1")
				$result .= ' CHECKED';
			$result .= '></td>'."\n";

		} else if ($type=="int" || $type=="tinyint" || $type=="smallint" || $type=="bigint" || $type=="float") {
			// classic numeric
			$result .= '<td><input type="text" name="_lv_column_'.$name.'" size="'.$size.'" value="'.$r202[$name].'"></td>'."\n";

		} else {
			$result .= "<td>Unknown type: '$type'</td>\n";
		}
	}

		// Row ends
		$result .= '<td>';
		if ($_lv_["enableedit"]) 
			$result .= '<a href="'.genuri().'&_lv_nav_id='.$r202["id"].'">Izmijeni</a> ';
		$result .= '<input type="submit" value=" Pošalji "><input type="submit" name="_lv_action_delete" value=" Obriši "></td>'."\n";
		$result .= "</tr></form>";
	}

	$result .= "</table>\n";
	return $result;
}


?>
