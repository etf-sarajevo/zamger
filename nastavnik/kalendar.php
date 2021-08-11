<?php
function nastavnik_kalendar() {
	
	ajax_box(); // Allow JS to create requests to zamger-api
	
	print "<link rel=\"stylesheet\" href=\"static\css\includes\calendar\calendar.css\">";
	print "<script src=\"static\js\includes\calendar\calendar.js\"> </script>";
	
	// Current date and day : )
	$days = ['Nedjelja', 'Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];
	$months = ['Januar', 'Februar', 'Mart', 'April', 'Maj', 'Juni', 'Juli', 'August', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
	
	?>
	<div class="calendar-wrapper">
		<div class="add-new-event-wrapper ">
			<div class="day-form p-4 new-event-form">
				<div class="form-group mt-2">
					<div class="row">
						<div class="col-md-12">
							<input type="text" class="form-control" id="time-title" aria-describedby="Title" placeholder="Dodajte naslov" value="">
							<small id="Title" class="form-text text-muted">Unesite naslov koji će se prikazivati na kalendaru</small>
						</div>
					</div>
				</div>

				<div class="form-group">
					<div class="row">
						<div class="col-md-6" title="Odaberite datum događaja">
							<input type="text" class="form-control datepicker" id="event-date" aria-describedby="event-date" placeholder="" value="<?= date('d.m.Y') ?>">
						</div>
						<div class="col-md-6" title="Odaberite vrstu događaja (npr. Raspored časova)">
							<select name="time-category" id="time-category" class="form-control">
								<option value="2">Raspored časova</option>
								<option value="5">Događaj</option>
							</select>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col col-md-6">
							<input type="text" class="form-control form-time" id="time-from" aria-describedby="Title" placeholder="12:00" value="">
						</div>
						<div class="col col-md-6">
							<input type="text" class="form-control form-time" id="time-to" aria-describedby="Title" placeholder="13:30" value="">
						</div>
						<div class="col col-md-12">
							<small id="Title" class="form-text text-muted">Unesite vrijeme početka i kraja događaja - validan format je HH:MM</small>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-md-6">
							<select name="repeat" id="repeat" class="form-control">
								<option value="1">Ne ponavljaj</option>
								<option value="2">Ponovi svake sedmice</option>
							</select>
						</div>
						<div class="col-md-6">
							<select name="allow-students" id="allow-students" class="form-control">
								<option value="1">Ne</option>
								<option value="2">Da</option>
							</select>
							<small id="Title" class="form-text text-muted">Omogućite prijavljivanje studenata</small>
						</div>
					</div>
				</div>
				<div class="form-group deadline-data">
					<div class="row mb-1">
						<div class="col-md-4">
							<input type="number" class="form-control" id="maxStudents" value="0" min="0" max="1000">
						</div>
						<div class="col-md-4 m-0 p-0">
							<input type="text" class="form-control datepicker" id="deadline-date" placeholder="" value="<?= date('d.m.Y', strtotime(date('Y-m-d')) - 86400) ?>">
						</div>
						<div class="col-md-4">
							<input type="text" class="form-control" id="deadline-time" aria-describedby="Title" placeholder="13:30" value="">
						</div>
					</div>
					<small>Unesite broj studenata, kao i datum i vrijeme za prijavu !</small>
				</div>
				<div class="form-group">
					<textarea name="info" id="info" class="form-control custom-textarea"></textarea>
				</div>
				<div class="row">
					<div class="col text-right">
						<button type="submit" class="btn btn-secondary btn-sm exit-cal-event">Odustanite</button>
						<button type="submit" class="btn btn-info btn-sm ml-2 save-event">Spremite</button>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Calendar wrapper -->
		<div class="calendar"> </div>

		<div class="this-day">
			<h5>Danas</h5>
			<h2><?= $days[date('w')] ?>, <br> <?= date('d') ?>. <?= $months[date('m')] ?> <?= date('Y') ?></h2>

			<h5><span class="this-day-total">  </span> stavka / e</h5>
			<div class="items-wrapper"> </div>
			
			<div class="add-new-today" title="Unesite novi događaj na današnji dan">
				<i class="fas fa-plus"></i>
				<p>Unesite novi događaj</p>
			</div>
		</div>
	</div>

	<script>
        calendar.createCalendar();
	</script>
	<?php
}
