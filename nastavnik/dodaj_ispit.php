<?

if($_REQUEST['funkcija']=='dodajIspit'){
	$_SESSION['brojIspita']=$_SESSION['brojIspita']+1;
	header("Location:?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag."&obiljezeno=ispiti&komp=".$brojK);
}
if($_REQUEST['funkcija']=='dodajZadacu'){
	$_SESSION['brojZadaca']=$_SESSION['brojZadaca']+1;
	header("Location:?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag."&obiljezeno=zadace&komp=".$brojK);
}
if($_REQUEST['funkcija']=='dodajPrisustvo'){
	$_SESSION['brojPrisustva']=$_SESSION['brojPrisustva']+1;
	header("Location:?sta=nastavnik/tip&predmet=".$predmet."&ag=".$ag."&obiljezeno=prisustvo&komp=".$brojK);
}
?>