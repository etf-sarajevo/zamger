<div class="s-top-menu">
	<div class="app-name">
		<img src="static/images/<?= $conf_logo_institucije ?? '/static/images/etf-50x50.png' ?>" width="50" height="50" border="0" alt="<?= $conf_skr_naziv_institucije ?? '' ?>">

		<a class="nav-link h5" href="index.php">
			<?= $conf_appname ?? '' ?> <span class="color-logo" "><?= $conf_appversion ?? '' ?></span>
		</a>

		<i class="fas fa-bars t-3 system-m-i-t" title="Otvorite / zatvorite MENU" aria-hidden="true"></i><span class="sr-only">Otvorite / zatvorite MENU</span>
	</div>
	
	<div class="top-menu-links">
		<!-- Left top icons -->
		<div class="left-icons">
			<div class="single-li">
				<a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_blank" title="Korisnička uputstva">
					<i class="far fa-file-alt"></i>
				</a>
			</div>
			<div class="single-li">
				<a href="https://github.com/etf-sarajevo/zamger/issues" target="_blank" title="Prijavite grešku">
					<i class="fas fa-bug"></i>
					<div class="number-of"><p>+</p></div>
				</a>
			</div>
			<?php
			// Promjena uloge korisnika
			if ($userid > 0 and 1) {
				?>
				<div class="single-li pt-1">
					<div class="dropdown show">
						<a class="btn btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<b>Linkovi</b>
						</a>
						<div class="dropdown-menu" aria-labelledby="navbarDropdown">
							<?php if ($user_student && !strstr($sta, "student/")) print '<a class="dropdown-item" href="?sta=student/intro"><small>Studentska stranica</small></a>'; ?>
							<?php if ($user_nastavnik && !strstr($sta, "saradnik/") && !strstr($sta, "nastavnik/")) print '<a class="dropdown-item" href="?sta=saradnik/intro"><small>Spisak predmeta i grupa</small></a>'; ?>
							<?php if ($user_studentska && !strstr($sta, "studentska/")) print '<a class="dropdown-item" href="?sta=studentska/intro"><small>Studentska služba</small></a>'; ?>
							<?php if ($user_siteadmin && !strstr($sta, "admin/")) print '<a class="dropdown-item" href="?sta=admin/intro"><small>Site admin</small></a>'; ?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		
		<!-- Right top icons -->
		<div class="right-icons">
			<!--<div class="single-li m-show-notifications" title="Pregled obavijesti">-->
			<!--	<i class="fas fa-bell"></i>-->
			<!--	<div class="number-of"><p id="no-unread-notifications">3</p></div>-->
			<!--	Ovdje include-ati obavijesti kada se razvije ovaj modul! -->
			<!--</div>-->
			<a href="#">
				<?php
				if($userid > 0 ){
					?>
					<div class="single-li user-name pt-1">
						<div class="dropdown show">
							<a class="btn btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<b><?= $person['name'].' '.$person['surname'] ?></b>
							</a>
							<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
								<a class="dropdown-item" href="?sta=common/profil">Profil</a>
								<a class="dropdown-item" href="?sta=common/inbox">Poruke</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="?sta=logout">Odjava</a>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</a>
		</div>
	</div>
</div>
