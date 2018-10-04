<div class="modal-body">

	<div class="row">
		<div class="col-md-6">
			<label class="labelText" for="role">{{ __('settings.profile.Current password') }}:</label>
			<input class="form-control modalInputs" id="current_password" type="password">
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<label class="labelText" for="max">{{ __('settings.profile.New password') }}:</label>
			<input class="form-control modalInputs" id="new_password1" type="password">
		</div>
		<div class="col-md-6"><label class="labelText" for="exp">{{ __('settings.profile.Repeat New password') }}:</label>
			<input class="form-control modalInputs" id="new_password2" type="password">
		</div>
	</div>

	<button type="button" class="btn btn-default btn-block addAccountBtn">{{ __('settings.profile.Change passwordSave') }}</button>

	<script>

		$("#proModal{{ $_mn }} .addAccountBtn").click(function()
		{
			var currentPass	 = $("#proModal{{ $_mn }} #current_password").val(),
				newPass1	 = $("#proModal{{ $_mn }} #new_password1").val(),
				newPass2	 = $("#proModal{{ $_mn }} #new_password2").val();

			if( newPass1 != newPass2 )
			{
				proApp.alert('{{ __('settings.profile.password_error') }}')
				return;
			}

			if( currentPass == '' || newPass1 == '' || newPass2 == '' )
			{
				proApp.alert('{{ __('settings.profile.validation_error1') }}')
				return;
			}

			proApp.ajax('{{ url('settings/chngPass/save') }}' , {
				'current_password':	currentPass,
				'new_password1':	newPass1,
				'new_password2':	newPass2
			} , function( result )
			{
				$("#proModal{{ $_mn }}").modal('hide');
			} , true);
		});
	</script>

</div>