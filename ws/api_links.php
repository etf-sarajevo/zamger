<?php

function returnData($data){	echo json_encode(['results' =>[['id' => '0', 'text' => $data]]]); }

// ------------------------------ TODO - extra validation for SQL injection ----------------------------------------- //

function ws_api_links(){
	
	global $userid;
	
	/*
	 * 	Select - 2 AJAX requests
	 */
	if(isset($_REQUEST['type'])){
		/** @var $data - array with data */
		$data = [];
		/** @var  $term - keyword for searching */
		$term = $_REQUEST['term'];
		if(empty($term)) returnData("Nema podataka");
		
		else if($_REQUEST['type'] == 's2-place'){
			$query = db_query("select VAJ_SIFRA, SIFRA,NAZIV from adm_jedinice where VAJ_SIFRA = 'M' AND NAZIV LIKE '%$term%' order by naziv");
			while ($row = db_fetch_row($query)){
				$data[] = [
					'id' => $row[1],
					'text' => $row[2]
				];
			}
		}else if($_REQUEST['type'] == 's2-munic'){
			$query = db_query("select NAZIV from mjesta_mkr WHERE NAZIV LIKE '%$term%' order by naziv");
			while ($row = db_fetch_row($query)){
				$data[] = [
					'id' => $row[0],
					'text' => $row[0]
				];
			}
		}
		
		if(!count($data)) $data[] = ['id' => '0', 'text' => 'Nema rezultata'];
		/*
		 *  Todo - ovdje možemo ukoliko neko mjesto ne postoji, vratiti ga kao opciju, pa onda prilikom unosa podataka, ukoliko
		 *  nema u bazi podataka, da se unese novi uzorak, ili stavi na razmatranje ..
		 */
		
		echo json_encode([ 'results' => $data ]);
	}
	
	/*
	 * 	Update persons data - Three tables
	 * 		- osoba
	 * 		- osoba_detaljno
	 * 		- srednja_skola
	 */
	
	else if(isset($_REQUEST['osoba_azuriraj'])){
		$data = array( 'success' => 'true', 'message' => '', 'data' => array() );
		
		/*
		 * 	Napomene :
		 * 		- polje kanton imamo u tabeli osoba, ali bi trebalo da podrazumijeva kanton boravišta a ne kanton prebivalšta
		 */
		
		$ime             = $_REQUEST['ime'];
		$prezime         = $_REQUEST['prezime'];
		$brindexa        = $_REQUEST['brindexa'];
		$jmbg            = $_REQUEST['jmbg'];
		$spol            = $_REQUEST['spol'];
		$mjesto_rodjenja = $_REQUEST['mjesto_rodjenja'];   // FK na mjesto -- Ovo bi trebalo promijeniti ili pokupiti drugi šifarnik
		$opcina_rodjenja = $_REQUEST['opcina_rodjenja'];   // Ne postoji ovo polje ? A u formi ga ima - ne znam kako se veže
		$drzava_rodjenja = $_REQUEST['drzava_rodjenja'];   // Također, isto i sa općinom rođenja
		$datum_rodjenja  = date('Y-m-d', $_REQUEST['datum_rodjenja']);
		$drzavljanstvo   = $_REQUEST['drzavljanstvo'];
		$nacionalnost    = $_REQUEST['nacionalnost'];
		$imeoca          = $_REQUEST['imeoca'];
		$prezimeoca      = $_REQUEST['prezimeoca'];
		$imemajke        = $_REQUEST['imemajke'];
		$prezimemajke    = $_REQUEST['prezimemajke'];
		
		/** Prebivalšte studenta **/
		$drzava_preb     = $_REQUEST['drzava_preb'];
		$kanton_preb     = $_REQUEST['kanton_preb'];
		$opcina_preb     = $_REQUEST['opcina_preb'];
		$adresa_preb     = $_REQUEST['adresa_preb'];
		
		/** Boravište studenta **/
		$adresa          = $_REQUEST['adresa'];
		$adresa_mjesto   = $_REQUEST['adresa_mjesto'];     // FK na mjesto -- Ovo bi trebalo promijeniti ili pokupiti drugi šifarnik
		$telefon         = $_REQUEST['telefon'];
		$email           = $_REQUEST['email2'];            // Ide u tabelu email -- Ja bih ostavio mogućnost upisa samo jednog email-a :)
		
		/** Srednja škola **/                              // Ovdje imam tabelu srednja_skola ali ne kontam poveznicu između osobe i srednje škole
		$naziv           = $_REQUEST['naziv'];			   // Dodao sam polje godina u tabelu srednja_skola (nullable) da bude potpuna sa ŠV - 20
		$godina          = $_REQUEST['godina'];
		$opcina          = $_REQUEST['opcina'];
		$tipskole        = $_REQUEST['tipskole'];
		$domaca          = $_REQUEST['domaca'];
		
		/** Ostale informacije **/
		$izvori_finan    = $_REQUEST['izvori_finan'];
		$status_a_r      = $_REQUEST['status_a_r'];
		$status_a_s      = $_REQUEST['status_a_s'];
		$zanimanje_r     = $_REQUEST['zanimanje_r'];
		$zanimanje_s     = $_REQUEST['zanimanje_s'];
		$status_z_r      = $_REQUEST['status_z_r'];
		$status_z_s      = $_REQUEST['status_z_s'];
		
		db_query("UPDATE osoba SET
			ime             = '$ime',
			prezime         = '$prezime',
			brindexa        = '$brindexa',
			jmbg            = '$jmbg',
			spol            = '$spol',
			datum_rodjenja  = '$datum_rodjenja',
			drzavljanstvo   = '$drzavljanstvo',
			nacionalnost    = '$nacionalnost',
			imeoca          = '$imeoca',
			prezimeoca      = '$prezimeoca',
			imemajke        = '$imemajke',
			prezimemajke    = '$prezimemajke',
        	adresa          = '$adresa',
        	telefon         = '$telefon'
			where id = $userid");
		
		// Check if there is any email inside email table, if don't, insert new one
		$emailCount = db_query("SELECT COUNT(*) from email where osoba = $userid")->fetch_row();
		if($emailCount[0] != 0){
			db_query("UPDATE email SET adresa = '$email' where osoba = $userid");
		}else{
			db_query("INSERT INTO email SET osoba = '$userid', adresa = '$email'");
		}
		
		// Check if there is any additional data inside osoba__dodatno table, if don't, insert new one
		$emailCount = db_query("SELECT COUNT(*) from osoba__dodatno where osoba = $userid")->fetch_row();
		if($emailCount[0] != 0){
			db_query("UPDATE osoba__dodatno SET
				drzava_preb  = '$drzava_preb',
				kanton_preb  = '$kanton_preb',
				opcina_preb  = '$opcina_preb',
				adresa_preb  = '$adresa_preb',
				izvori_finan = '$izvori_finan',
				status_a_r   = '$status_a_r',
				status_a_s   = '$status_a_s',
				zanimanje_r  = '$zanimanje_r',
				zanimanje_s  = '$zanimanje_s',
				status_z_r   = '$status_z_r',
				status_z_s   = '$status_z_s'
			where osoba = $userid");
		}else{
			db_query("INSERT INTO osoba__dodatno SET
				osoba        = '$userid',
				drzava_preb  = '$drzava_preb',
				kanton_preb  = '$kanton_preb',
				opcina_preb  = '$opcina_preb',
				adresa_preb  = '$adresa_preb',
				izvori_finan = '$izvori_finan',
				status_a_r   = '$status_a_r',
				status_a_s   = '$status_a_s',
				zanimanje_r  = '$zanimanje_r',
				zanimanje_s  = '$zanimanje_s',
				status_z_r   = '$status_z_r',
				status_z_s   = '$status_z_s'
			");
		}
		
		echo json_encode($data);
	}
	
	/*
	 * 	Final result for select-2 AJAX requests -- if there is no data, or some kind of error occurs, it would
	 * 	return a json object with message "No data"
	 */
	
	else{
		returnData("Nema podataka !");
	}
}
