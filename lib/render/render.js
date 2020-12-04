function showhide(id) {
	var me = document.getElementById(id);
	if (me.style.display=="none"){
		me.style.display="inline";
	}
	else {
		me.style.display="none";
	}
	return false;
}

var diffShown = false;

function showDiff(expectNo) {
	var output = document.getElementById('programOutput');
	var link = document.getElementById('showDiffLink');
	
	if (diffShown) {
		var myOutput = programOutput;
		myOutput = myOutput.replace(/\n/g, '<br>');
		output.innerHTML = "<span class=\"success\"><code>" + myOutput + "</code></span>";
		link.textContent = diffLabel;
		diffShown = false;
		return false;
	}
	
	var diff = JsDiff.diffChars(programOutput, expected[expectNo]);
	output.innerHTML = "";
	
	diff.forEach(function(part) {
		var partClass = part.added ? 'fail' :
		part.removed ? 'success' : 'neither';
		
		var span = document.createElement('span');
		
		if (part.value === "\n") { part.value = "\\n"; }
		
		span.appendChild(document.createTextNode(part.value));
		//span.style.class = partClass;
		span.className = partClass;
		console.log(span.style);
		output.appendChild(span);
	});
	
	output.innerHTML = "<pre>" + output.innerHTML + "</pre>";
	link.textContent = hideDiffLabel;
	diffShown = true;
	return false;
}
