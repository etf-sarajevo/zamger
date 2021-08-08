<!-- Insert new place -->

<div class="modal fade" id="placeInsert" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Unesite novo mjesto</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form>
					<div class="form-group">
						<label for="newCountry" class="col-form-label">Država :</label>
						<?= Form::select('newCountry', $drzava, '1', ['class' => 'form-control form-control-sm select-2', 'id' => 'newCountry', 'required' => 'required', 'style' => 'width:100%;'], 'državu') ?>
					</div>

					<div class="form-group">
						<div class="row">
							<div class="col-md-6 newMunicSelW">
								<label for="newMunicSel" class="col-form-label">Općina / grad :</label>
								<?= Form::select('newMunicSel', $opcina, '', ['class' => 'form-control form-control-sm select-2', 'id' => 'newMunicSel', 'required' => 'required', 'style' => 'width:100%;'], 'općinu') ?>
							</div>
							<div class="col-md-6 newMunicTextW d-none">
								<label for="newMunicText" class="col-form-label">Općina / grad :</label>
								<?= Form::text('newMunicText', '', ['class' => 'form-control form-control-sm', 'id' => 'newMunicText', 'required' => 'required', 'style' => 'width:100%;']) ?>
							</div>
							<div class="col-md-6">
								<label for="newPlace" class="col-form-label">Mjesto :</label>
								<?= Form::text('newPlace', '', ['class' => 'form-control form-control-sm', 'id' => 'newPlace', 'required' => 'required', 'style' => 'width:100%;']) ?>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-xs pl-2 pr-2 pt-1 pb-1" data-dismiss="modal">ODUSTANI</button>
				<button type="button" class="btn bck-logo btn-xs pl-2 pr-2 pt-1 pb-1 saveNewPlace">SPREMI</button>
			</div>
		</div>
	</div>
</div>
