<?php

function student_kalendar() {
	
	// Current date and day : )
	$days = ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
	$months = ['', 'Januar', 'Februar', 'Mart', 'April', 'Maj', 'Juni', 'Juli', 'August', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
	
	ajax_box(); // Allow JS to create requests to zamger-api
	
	
	?>
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
	<link href="static/css/includes/libraries/select-2.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="https://kit.fontawesome.com/cdf2a0a58b.js"></script>
	
	<script src="static/js/notify.js"></script>
	<script src="static/js/jquery-setup.js"> </script>
	<link rel="stylesheet" href="static/css/calendar/calendar.css">
	<script src="static/js/calendar/calendar.js"> </script>
	
	<div class="calendar-wrapper">
		<div class="add-new-event-wrapper"></div>
		<div class="calendar"> </div>
		
		<div class="this-day">
			<h5>Danas</h5>
			<h2><?= $days[date('w')] ?>, <br> <?= date('d') ?>. <?= $months[intval(date('m'))] ?> <?= date('Y') ?></h2>
			
			<h5><span class="this-day-total">  </span> događaja</h5>
			<div class="items-wrapper"> </div>
		</div>
	</div>
	
	<script>
        calendar.createCalendar();
	</script>
	<?php
}