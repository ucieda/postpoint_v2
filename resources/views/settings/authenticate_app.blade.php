<div class="modal-body">

	<form>
		<div class="form-group row ptop-20">
			<div class="col-md-6">
				<label class="labelText" for="userName">Username:</label>
				<input class="form-control modalInputs" id="userName" type="text">
			</div>

			<div class="col-md-6">
				<label class="labelText" for="password1">Password:</label>
				<input class="form-control modalInputs" id="password1" type="password">
			</div>

		</div>
	</form>

	<button type="button" class="btn btn-default btn-block addAccountColor">GENERATE TOKEN</button>

	<div class="warningBlock">
		<p class="danger-text text-danger">Your Facebook account password will NOT be stored we only use the password to generate a facebook token:</p>
	</div>

	<div class="row">
		<div class="col-md-12">
			<iframe style="display: none; width: 100%; height: 100px; border: 1px solid #CCC; -webkit-border-radius: 5px;-moz-border-radius: 5px;border-radius: 5px;" id="iframeForToken"></iframe>
		</div>
		<div class="col-md-12 ptop-20">
			<textarea name="accessToken" rows="3" cols="100" id="accessToken" class="form-control" placeholder="Access token Here"></textarea>
			<button type="button" class="btn btn-default btn-block addAccountColor addBtn2">ADD FACEBOOK ACCOUNT</button>
		</div>
	</div>

</div>

<script>

	$("#proModal{{ $_mn }} .addAccountColor").click(function()
	{
		var userName	 		= $("#proModal{{ $_mn }} #userName").val(),
			password			= $("#proModal{{ $_mn }} #password1").val();

		proApp.ajax('{{ url('settings/addAccount/save') }}' , {
			'userName':		userName,
			'password':		password,
			'appication':	'{{ $appId }}'
		} , function( result )
		{

			//$("#proModal{{ $_mn }}").modal('hide');
			//location.reload();
			$("#iframeForToken").show().attr('src' , result['url'] );

		} , true);
	});

	$("#proModal{{ $_mn }} .addBtn2").click(function()
	{
		var accessToken	 		= $("#proModal{{ $_mn }} #accessToken").val();

		proApp.ajax('{{ url('settings/addAccount/saveAT') }}' , {
			'access_token':		accessToken
		} , function( result )
		{

			$("#proModal{{ $_mn }}").modal('hide');
			location.reload();

		} , true);
	});
</script>