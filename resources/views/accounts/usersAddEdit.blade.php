<div class="modal-body">

	<form>
		<div class="row">
			<div class="col-md-6">
				<input class="form-control modalInputs" id="username2" placeholder="{{ __('accounts.Username') }}" type="text" value="{{ $info['username'] ?? '' }}">
			</div>

			<div class="col-md-6">
				<input class="form-control modalInputs" id="email2" type="text" placeholder="{{ __('accounts.Email') }}" value="{{ $info['email'] ?? '' }}">
			</div>
		</div>

		<div class="row" style="margin-top: 10px;">
			<div class="col-md-6">
				<input class="form-control modalInputs" id="password1" type="password" placeholder="{{ __('accounts.Password') }}">
			</div>

			<div class="col-md-6">
				<input class="form-control modalInputs" id="password2" type="password" placeholder="{{ __('accounts.Re-enter password') }}">
			</div>
		</div>

		<div class="row" style="margin-top: 10px;">
			<div class="col-md-6">
				<select class="form-control modalInputs" id="role2">
					@foreach($userRoles AS $roleInf)
						<option value="{{ $roleInf->id }}"{{ $info && $info->user_role_id == $roleInf->id ? ' selected' : '' }}>{{ $roleInf->name }}</option>
					@endforeach
				</select>
			</div>

			<div class="col-md-6">
				<input class="form-control modalInputs" id="expire_on" type="text" placeholder="{{ __('accounts.Expire on') }}" value="{{ $info && $info['expire_on'] ? date(dateFormat() , strtotime($info['expire_on'])) : '' }}">
			</div>
		</div>
	</form>

	<button type="button" class="btn btn-default btn-block addAccountBtn">{{ __('accounts.Save') }}</button>
</div>

<script>

	$("#proModal{{ $_mn }} #expire_on").datepicker({
		autoclose: true,
		dateFormat: '{{ dateFormat(1) }}'
	});

	$("#proModal{{ $_mn }} .addAccountBtn").click(function()
	{
		var username	 		= $("#proModal{{ $_mn }} #username2").val(),
			email				= $("#proModal{{ $_mn }} #email2").val(),
			password1		 	= $("#proModal{{ $_mn }} #password1").val(),
			password2	 		= $("#proModal{{ $_mn }} #password2").val(),
			role			 	= $("#proModal{{ $_mn }} #role2").val(),
			expire_on		 	= $("#proModal{{ $_mn }} #expire_on").val();

		if( username == '' || email == '' || password1 == '' || password2 == '' || role == '' || password1 != password2 )
		{
			proApp.alert('Məlumatları tam doldurmadınız!')
			return;
		}

		proApp.ajax('{{ url('ajax/accounts/addEditUser/save') }}' , {
			'id':				'{{ $id }}',
			'username':			username,
			'email':			email,
			'password1':		password1,
			'password2':		password2,
			'role':				role,
			'expire_on':		expire_on
		} , function( result )
		{

			$("#proModal{{ $_mn }}").modal('hide');
			location.reload();

		} , true);
	});
</script>