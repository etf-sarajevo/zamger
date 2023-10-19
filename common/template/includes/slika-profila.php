<div class="full-window mp-profile-image-element">
	<div class="modal-popup mp-400">
		<div class="mp-header">
			<h5>Odaberite sliku profila</h5>
			<div class="mp-exit" title="Zatvorite">
				<i class="fas fa-times"></i>
			</div>
		</div>
		
		<div class="mp-body">
			<div class="mp-c-pi">
				<input type="hidden" class="form-control" id="profile-image-p-input">
				<img src="" id="profile-image-p" alt="">
				
				<label for="profile-image" class="t-3">
					<div class="mp-cpi-wrapper" title="{{__('Odaberite željenu fotorafiju')}}">
						<i class="fas fa-cloud-upload-alt t-3" aria-hidden="true"></i>
						<p>200 x 200</p>
					</div>
				</label>
				
				<input type="file" id="profile-image" class="photo-return-id" path="static/images/user-images/" category="profile-image" photo-name="profile-image-p" name="photo-input" url="index.php?sta=ws/api_links">
			</div>
		</div>
		
		<div class="mp-buttons">
			<button class="mp-cancel btn-dark" title="Zatvorite prozor">Odustanite</button>
			<button class="mp-submit mp-pie-submit" title="Ažurirajte izmjene">Ažurirajte</button>
		</div>
	</div>
</div>
