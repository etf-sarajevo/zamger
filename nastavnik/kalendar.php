<?php
function nastavnik_kalendar() {
	
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
								<option value="5">Događaj</option>
								<option value="2">Raspored časova</option>
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
						<div class="col-md-6" title="Da li želite da se događaj ponavlja?">
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
					<small>Unesite maksimalan broj studenata, kao i krajnji rok za prijavu.</small><br>
					<small class="students-list"><a href="#">Spisak prijavljenih studenata</a></small>
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
			<h2><?= $days[date('w')] ?>, <br> <?= date('d') ?>. <?= $months[intval(date('m'))] ?> <?= date('Y') ?></h2>
			
			<h5><span class="this-day-total">  </span> događaja</h5>
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
