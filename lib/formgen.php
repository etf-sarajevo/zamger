<?

// LIB/FORMGEN - biblioteka za automatsko generisanje HTML formi na osnovu šeme baze

// Ova biblioteka je nastala cca. 2000. godine kao libvedran i tada je bila 
// prilično inovativna. Sada treba planirati njenu zamjenu nečim modernijim :)



// Globalni niz $_lv_ može sadržavati razne konfiguracijske parametre 
// Predefinišemo ga da izbjegnemo upozorenja
if (!isset($_lv_)) $_lv_ = array();

require_once("lib/utility.php"); // time2mysql, mysql2time


// Prikaz datuma za formular
function datectrl($d,$m,$g,$prefix="") {
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



// interna funkcija za parsiranje tabele
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
	$q200 = db_query("show create table $table");
	$__lv_showcreate = db_result($q200,0,1);
	foreach (explode("\n", $__lv_showcreate) as $line) {
		if (strstr($line, "CONSTRAINT")) {
			continue;
		} else if (preg_match("/`(\w+)` (\w+)\((\d+)\)/", $line, $matches)) {
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



// db_submit() - funkcija za obradu poslanih podataka
function db_submit() {
	global  $__lv_cn, $__lv_ct, $__lv_cs, $__lv_showcreate;
	global $__lv_submitted;

	// Prevent executing twice
	if ($__lv_submitted == 1) return;
	$__lv_submitted=1;

	// Check if submitted data is ok (we only use POST)
	$action = param('_lv_action');
	$table = db_escape(param('_lv_table'));

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
		if ($type == "timestamp" && $data=="") {
			continue; // Do not resubmit timestamps, since they have to be updated
		}


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
			$sql .= "$name=".floatval(str_replace(",",".",$data));
		else
			$sql .= "$name='".db_escape($data)."'";
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
				$sql .= db_escape(substr($key,10))."='".db_escape($value)."'";
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
	db_query($sql);
}



// Generiše drop-down listu za datu tabelu
function db_dropdown($table,$selected=0,$empty=0) {
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
		
		// Find foreign keys - TODO

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
			$sql .= db_escape(substr($key,6))."='".db_escape($value)."'";
			$n++;
		}
	}

	// Order by (hack)
	if ($surname == "")
		$sql .= " order by $name";
	else
		$sql .= " order by $surname,$name";

	$q101 = db_query($sql);

	// Construct output
	$result = '<select name="_lv_column_'.$table.'">'."\n";
	$found=0;
	while ($r101 = db_fetch_row($q101)) {
		$result .= '<option value="'.$r101[0].'"';
		if ($r101[0]==$selected || $r101[1]=="$selected") {
			$result .= ' SELECTED ';
			$found=1;
		}
		$result .= '>'.$r101[1];
		if ($surname != "") $result .= " ".$r101[2];
		$result .= '</option>'."\n";
	}
//	if ($found == 0)
//		$result .= '<option value="'.$selected.'" SELECTED>'.$selected.'</option>'."\n";

	if ($empty) { // empty field 
		$result .= '<option value="-1"'; // so it can be detected by php !
		if (!$found) $result .= " SELECTED";
		$result .= ">$empty</option>\n"; 
	}
	$result .= '</select>';
	return $result;
}


// db_form - generise formular za tabelu sa jednim slogom (evt. koristeći where)
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
	$q200 = db_query("show tables");
	while ($r200 = db_fetch_row($q200)) 
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
			$sql .= "$name='".db_escape($_lv_["where:$name"])."'";
			$n++;
		}
		// Also find stuff from list / navigation / previously submitted form
		if ($_REQUEST["_lv_nav_$name"]) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= "$name='".db_escape($_REQUEST["_lv_nav_$name"])."'";
			$n++; $nav=1;
		}
	}

	if ($nav==1 || $_lv_["forceedit"]==1) {
		$result .= '<input type="hidden" name="_lv_action" value="edit">'."\n";
		$q202 = db_query($sql);
		$r202 = db_fetch_assoc($q202);
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
				$q203 = db_query("select $name from $table order by $name desc limit 1");
				if (db_num_rows($q203)<1)
					$r202[$name] = 1;
				else
					$r202[$name] = db_result($q203,0,0)+1;
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
			// When adding, we go with the database default
			if ($nav==1 || $_lv_["forceedit"]==1) {
				$result .= '<input type="hidden" name="_lv_column_'.$name.'" value="'.$r202[$name].'">'."\n";
			}

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

		} else if ($type=="int" || $type=="tinyint" || $type=="smallint" || $type=="bigint" || $type=="float"|| $type=="double") {
			// classic numeric
			$result .= $label.': <input type="text" name="_lv_column_'.$name.'" size="'.$size.'" value="'.$r202[$name].'"><br/><br/>'."\n";

		} else {
			$result .= "Unknown type: '$type'<br/><br/>\n";
		}
	}

	// Buttons and form ending
	$result .= '<input type="submit" value=" Pošalji "> <input type="reset" value=" Poništi ">';

	// Delete button will be displayed only if we are editing
	if ($nav==1 || $_lv_["forceedit"]==1 && $_lv_["brisanje"]==1)
		$result .= '<input type="submit" name="_lv_action_delete" value=" Obriši  ">'."\n";

	$result .= '</form>'."\n";

	return $result;
}


// db_list - bullet lista elemenata tabele sa edit linkovima i sl.
function db_list($table,$selected=0) {
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
	$n = 0;
	foreach ($_lv_ as $key => $value) {
		if (substr($key,0,6) == "where:" && substr($key,6) != $id && substr($key,6) != $name) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= db_escape(substr($key,6))."='".db_escape($value)."'";
			$n++;
		}
	}
	$q101 = db_query($sql);

	// Construct output
	$result = '<ul>'."\n";
	if (db_num_rows($q101)<1)
		$result .= '<li>Nema</li>'."\n";

	$uri = genuri();

	while ($r101 = db_fetch_row($q101)) {
		$result .= '<li>';
		$i = $r101[0];
		$n = $r101[1];
		if (!preg_match("/\w/",$n)) $n="[Bez naziva]";
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
	if (empty($_lv_["new_link"]))
		$new_link = "Unesi novu";
	else
		$new_link = $_lv_["new_link"];
	$result .= '<p><a href="'.$uri.'">'.$new_link.'</a></p>';
	return $result;
}



// db_grid - generise HTML tabelu za DB tabelu :)
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
	$q200 = db_query("show tables");
	while ($r200 = db_fetch_row($q200)) 
		$tables[] = $r200[0];


	// Query database to get default form values
	$sql = "select * from $table";
	$n = 0;
	for ($i=0; $i<count($__lv_cn); $i++) {
		$name = $__lv_cn[$i];
		// Get WHERE from $_lv_
		if (strlen($_lv_["where:$name"])>0) {
			if ($n>0) $sql .= " and "; else $sql .= " where ";
			$sql .= "$name='".db_escape($_lv_["where:$name"])."'";
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

	// Get LIMIT from $_lv_
	foreach ($_lv_ as $key => $value) {
		if ($key == "limit") {
			$sql .= " limit ".$value;
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
	$q202 = db_query($sql);
	$color = 0;
	while ($r202 = db_fetch_assoc($q202)) {
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

		} else if ($type=="int" || $type=="tinyint" || $type=="smallint" || $type=="bigint" || $type=="float"|| $type=="double") {
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
