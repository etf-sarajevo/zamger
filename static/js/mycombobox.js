
// DHTML combo box kontrola
// by Vedran Ljubović
//
// Upotreba: vidjeti funkciju mycombobox u lib/zamger
//
// mycombobox($name, $value, $valueslist)
// - $name - string, jedinstveni DOM ID za combobox
// - $value - default vrijednost comboboxa
// - $valueslist - niz vrijednosti koje treba popuniti u combobox



function comboBoxEdit(evt, elname) {
	var ib = document.getElementById(elname);
	var list = document.getElementById("comboBoxDiv_"+elname);
	var listsel = document.getElementById("comboBoxMenu_"+elname);

	var key, keycode;
	if (evt) {
		key = evt.which;
		keycode = evt.keyCode;
	} else if (window.event) {
		key = window.event.keyCode;
		keycode = key; // wtf?
	} else return true;
	if (keycode==40) { // arrow down
		if (list.style.visibility == 'visible') {
			if (listsel.selectedIndex<listsel.length)
				listsel.selectedIndex = listsel.selectedIndex+1;
		} else {
			comboBoxShowHide(elname);
		}
		return false;

	} else if (keycode==38) { // arrow up
		if (list.style.visibility == 'visible' && listsel.selectedIndex>0) {
			listsel.selectedIndex = listsel.selectedIndex-1;
		}
		return false;

	} else if ((keycode==13 || keycode==9) && list.style.visibility == 'visible') { // Enter key - select option and hide
		comboBoxOptionSelected(elname);
		return false;

	} else if (key>31 && key<127) {
		// This executes before the letter is added to text
		// so we have to add it manually
		var ibtxt = ib.value.toLowerCase() + String.fromCharCode(key).toLowerCase();

		for (i=0; i<listsel.length; i++) {
			var listtxt = listsel.options[i].value.toLowerCase();
			if (ibtxt == listtxt.substr(0,ibtxt.length)) {
				listsel.selectedIndex=i;
				if (list.style.visibility == 'hidden') comboBoxShowHide(elname);
				return true;
			}
		}
		return true;
	}
	return true;
}

function comboBoxShowHide(elname) {
	var ib = document.getElementById(elname);
	var list = document.getElementById("comboBoxDiv_"+elname);
	var image = document.getElementById("comboBoxImg_"+elname);

	if (list.style.visibility == 'hidden') {
		// Find object position
		var curleft = curtop = 0;
		var obj=ib;
		if (obj.offsetParent) {
			do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}

		list.style.visibility = 'visible';
		list.style.left=curleft;
		list.style.top=curtop+ib.offsetHeight;
		image.src = "images/cb_down.png";
	} else {
		list.style.visibility = 'hidden';
		image.src = "images/cb_up.png";
	}
}
function comboBoxHide(elname) {
	var list = document.getElementById("comboBoxDiv_"+elname);
	var listsel = document.getElementById("comboBoxMenu_"+elname);
	var image = document.getElementById("comboBoxImg_"+elname);
	if (list.style.visibility == 'visible' && listsel!==document.activeElement) {
		list.style.visibility = 'hidden';
		image.src = "images/cb_up.png";
	}
}
function comboBoxOptionSelected(elname) {
	var ib = document.getElementById(elname);
	var listsel = document.getElementById("comboBoxMenu_"+elname);
	
	ib.value = listsel.options[listsel.selectedIndex].value;
	comboBoxShowHide(elname);
}
