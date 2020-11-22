<?php
function nastavnik_kalendar() {
	print "<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css\" integrity=\"sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T\" crossorigin=\"anonymous\">";
	
	print "<link rel=\"stylesheet\" href=\"static\css\calendar.css\">";
	print "<link href=\"https://fonts.googleapis.com/css?family=Nunito:200,600\" rel=\"stylesheet\">";
	print "<script src=\"https://kit.fontawesome.com/cdf2a0a58b.js\"></script>";
	print "<script src=\"//code.jquery.com/jquery-1.12.4.js\"></script>";
	print "<script src=\"https://code.jquery.com/ui/1.12.1/jquery-ui.js\"></script>";
	print "<script src=\"https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js\" integrity=\"sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM\" crossorigin=\"anonymous\"></script>";
	print "<script src=\"static\js\calendar.js\"> </script>"
	
	?>
		<div class="calendar-wrapper">
			<div class="add-new-event-wrapper ">
				<div class="day-form p-4">
					<div class="form-group mt-2">
						<input type="email" class="form-control" id="time-title" aria-describedby="Title" placeholder="Dodajte naslov">
						<small id="Title" class="form-text text-muted">Unesite naslov koji će se prikazivati na kalendaru</small>
					</div>
					<div class="form-group">
						<select name="" id="" class="form-control">
							<option value="1">Događaj</option>
							<option value="2">Raspored časova</option>
						</select>
						<small id="Title" class="form-text text-muted">Odaberite vrstu događaja</small>
					</div>
					<div class="form-group">
						<div class="row">
							<div class="col col-md-6">
								<input type="text" class="form-control form-time" id="time-from" aria-describedby="Title" placeholder="12:00">
							</div>
							<div class="col col-md-6">
								<input type="text" class="form-control form-time" id="time-to" aria-describedby="Title" placeholder="13:30">
							</div>
							<div class="col col-md-12">
								<small id="Title" class="form-text text-muted">Unesite vrijeme početka i kraja događaja</small>
							</div>
						</div>
					</div>
					<div class="form-group">
						<textarea name="info" id="" class="form-control custom-textarea"></textarea>
					</div>
					<div class="row">
						<div class="col text-right">
							<button type="submit" class="btn btn-secondary btn-sm exit-cal-event">Odustanite</button>
							<button type="submit" class="btn btn-info btn-sm ml-2">Spremite</button>
						</div>
					</div>
				</div>
			</div>
			<div class="calendar">

			</div>
			
			
			<div class="this-day">
				<h5>Danas</h5>
				<h2>Utorak, <br> 1. Septembar 2020</h2>

				<h5>4 stavke</h5>
				
				<div class="items-wrapper">
					<div class="single-item">
						<p>08:00</p>
						<span>Predavanja iz OE</span>
					</div>
					<div class="single-item">
						<p>10:00</p>
						<span>Konsultacije sa studentima</span>
					</div>
					<div class="single-item">
						<p>14:30</p>
						<span>Sastanak vijeća odsjeka</span>
					</div>
					<div class="single-item">
						<p>16:00</p>
						<span>Pokupiti djecu iz škole :D</span>
					</div>
				</div>
				<div class="add-new-today">
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