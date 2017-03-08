// Pomocna funkcija za rad sa stablom 

function daj_stablo(ime){
	var me = document.getElementById(ime);
	var img = document.getElementById('img-'+ime);
	if (me.style.display=="none"){
		me.style.display="inline";
		img.src="static/images/minus.png";
	}
	else {
		me.style.display="none";
		img.src="static/images/plus.png";
	}
}