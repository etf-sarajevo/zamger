<?php
function nastavnik_kalendar() {
	print "<link rel=\"stylesheet\" href=\"static\css\calendar.css\">";
	print "<link href=\"https://fonts.googleapis.com/css?family=Nunito:200,600\" rel=\"stylesheet\">";
	print "<script src=\"https://kit.fontawesome.com/cdf2a0a58b.js\"></script>";
	print "<script src=\"//code.jquery.com/jquery-1.12.4.js\"></script>";
	print "<script src=\"static\js\calendar.js\"> </script>"
	
	?>
		<div class="calendar-wrapper">
			<div class="calendar"> </div>
			
			
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