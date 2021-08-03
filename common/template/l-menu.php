<div class="s-left-menu t-3">
	<!-- user Info -->
	<div class="user-info">
		<div class="user-image">
			<img class="mp-profile-image" title="Promijenite sliku profila" src="static/images/16x16/user.png" alt="">
		</div>
		<div class="user-desc">
			<h4><?= $user['ime'].' '.$user['prezime'] ?></h4>
			<p>Role</p>
			<p>
				<i class="fas fa-circle"></i>
				Online
			</p>
		</div>
	</div>
	<hr>
	
	<!-- Menu subsection -->
	<div class="s-lm-subsection">
		
		<div class="subtitle">
			<h4>Dashboard</h4>
			<div class="subtitle-icon">
				<i class="fas fa-home"></i>
			</div>
		</div>
		<a href="index.php" class="menu-a-link">
			<div class="s-lm-wrapper">
				<div class="s-lm-s-elements">
					<div class="s-lms-e-img">
						<i class="fas fa-home"></i>
					</div>
					<p>Dashboard</p>
					<div class="extra-elements">
						<div class="ee-t ee-t-b"><p>Main</p></div>
					</div>
				</div>
			</div>
		</a>
		
		
		<!------------------------------------------------------------------------------------------------------------->
		
		<div class="subtitle">
			<h4> Dokumenti </h4>
			<div class="subtitle-icon">
				<i class="fas fa-folder-open"></i>
			</div>
		</div>
		
		<a href="#" class="menu-a-link">
			<div class="s-lm-wrapper">
				<div class="s-lm-s-elements">
					<div class="s-lms-e-img">
						<i class="fas fa-folder-open"></i>
					</div>
					<p>Dokumenti</p>
					<div class="extra-elements">
						<div class="rotate-element"><i class="fas fa-angle-right"></i></div>
					</div>
				</div>
				<div class="inside-links active-links">
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Zahtjev za ovjereno uvjerenje</p>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Uvjerenje o položenim predmetima</p>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Pregled ostvarenog rezultata</p>
							<div class="additional-icon">
								<i class="fas fa-cloud-download-alt"></i>
							</div>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Ugovor o učenju</p>
							<div class="additional-icon">
								<i class="fas fa-cloud-download-alt"></i>
							</div>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p> Promjena odsjeka USKORO!</p>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Zahtjev za koliziju</p>
						</div>
					</a>
					<a href="##">
						<div class="inside-lm-link">
							<div class="ilm-l"></div><div class="ilm-c"></div>
							<p>Prosjeci po godinama</p>
							<div class="additional-icon">
								<i class="fas fa-cloud-download-alt"></i>
							</div>
						</div>
					</a>
				</div>
			</div>
		</a>

	</div>

	<div class="bottom-icons">
		<div class="bottom-icon" title="Dodatne opcije">
			<i class="fas fa-cog"></i>
		</div>
		<div class="bottom-icon">
			<i class="fas fa-envelope-open-text"></i>
		</div>
		<div class="bottom-icon">
			<i class="fas fa-file-csv"></i>
		</div>
		<a href="#">
			<div class="bottom-icon" title="Odjavite se">
				<i class="fas fa-power-off"></i>
			</div>
		</a>
	</div>

</div>
