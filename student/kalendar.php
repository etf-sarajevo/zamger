<?php
function student_kalendar() {
	
	ajax_box(); // Allow JS to create requests to zamger-api
	
	print "<link rel=\"stylesheet\" href=\"static\css\includes\calendar\calendar.css\">";
	print "<script src=\"static\js\includes\calendar\calendar.js\"> </script>";
	
	// Current date and day : )
	$days = ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
	$months = ['Januar', 'Februar', 'Mart', 'April', 'Maj', 'Juni', 'Juli', 'August', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
	
	?>
	<div class="calendar-wrapper">
		<div class="add-new-event-wrapper"></div>
		<div class="calendar"> </div>

		<div class="this-day">
			<h5>Danas</h5>
			<h2><?= $days[date('w')] ?>, <br> <?= date('d') ?>. <?= $months[date('m')] ?> <?= date('Y') ?></h2>

			<h5><span class="this-day-total">  </span> stavka / e</h5>
			<div class="items-wrapper"> </div>
		</div>
	</div>
	
	<script>
        calendar.createCalendar();
	</script>
	<?php
}
