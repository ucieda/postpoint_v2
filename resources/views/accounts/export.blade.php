<div class="modal-body">
	<form method="post" action="{{ url('accounts/export2') }}">
		<div class="row">
			<div class="check-1 col-xs-12 col-sm-12 col-md-12">
				<input id="admin_c1" type="checkbox" name="admin_c">
				<label for="admin_c1" class="check-box"></label>
				<label class="text_label" for="admin_c1">Admin</label>
			</div>
		</div>

		@foreach( \App\User_role::get() AS $roleInf )
		<div class="row" style="margin-top: 10px;">
			<div class="check-1 col-xs-12 col-sm-12 col-md-12">
				<input id="role_{{ $roleInf->id }}" value="{{ $roleInf->id }}" type="checkbox" class="rolesCheckbox" data-id="{{ $roleInf->id }}" name="roles[]">
				<label for="role_{{ $roleInf->id }}" class="check-box"></label>
				<label class="text_label" for="role_{{ $roleInf->id }}">{{ $roleInf->name }}</label>
			</div>
		</div>
		@endforeach

		<div class="row" style="margin-top: 10px;">
			<div class="check-1 col-xs-12 col-sm-12 col-md-12">
				<input id="expired_Accounts1" type="checkbox" name="expired_accounts">
				<label for="expired_Accounts1" class="check-box"></label>
				<label class="text_label" for="expired_Accounts1">{{ __('accounts.Expired accounts') }}</label>
			</div>
		</div>
		{{ csrf_field() }}
		<button type="submit" class="btn btn-default btn-block addAccountBtn">{{ __('accounts.Export') }}</button>
	</form>
</div>