@if(!\Illuminate\Support\Facades\Auth::user()->is_admin)
	{!! die() !!}
@endif
<div class="modal-body">

	<form>
		<div class="form-group row">
			<div class="col-md-6">
				<label class="labelText" for="role_name">{{ __('settings.roles.Role name') }}:</label>
				<input class="form-control modalInputs" id="role_name" type="text" value="{{ $info['name'] ?? '' }}">
			</div>

			<div class="col-md-6">
				<label class="labelText" for="max_post_per_day">{{ __('settings.roles.Maximum posts per day') }}:</label>
				<input class="form-control modalInputs" id="max_post_per_day" type="number" value="{{ $info['max_posts_per_day'] ?? '' }}">
			</div>
		</div>

		<div class="form-group row">
			<div class="col-md-6">
				<label class="labelText" for="max_fb_accounts">{{ __('settings.roles.Maximum Facebook accounts') }}:</label>
				<input class="form-control modalInputs" id="max_fb_accounts" type="number" value="{{ $info['max_fb_accounts'] ?? '' }}">
			</div>

			@if( isset($info['is_for_demo']) && !empty( $info['is_for_demo'] ) )
			<div class="col-md-6">
				<label class="labelText" for="expire_days">{{ __('settings.roles.Account expire(In days)') }}:</label>
				<input class="form-control modalInputs" id="expire_days" type="number" value="{{ $info['account_expire_days'] ?? '' }}">
			</div>
			@endif

		</div>

		@if( empty( $info['is_for_demo'] ) )
		<div class="form-group row">
			<div class="col-md-6">
				<label class="labelText" for="monthly_price">{{ __('settings.roles.Monthly price (USD)') }}:</label>
				<input class="form-control modalInputs" id="monthly_price" type="text" value="{{ $info['monthly_price'] ?? '' }}">
			</div>

			<div class="col-md-6">
				<label class="labelText" for="annual_price">{{ __('settings.roles.Annual price (USD)') }}:</label>
				<input class="form-control modalInputs" id="annual_price" type="number" value="{{ $info['annual_price'] ?? '' }}">
			</div>
		</div>
		@endif

	</form>

	<div class="row form-group checkBlock">
		<div class="check-1 col-xs-6 col-sm-6 col-md-6">
			<input id="upload_videos" type="checkbox"{{ empty($info['upload_videos']) ? '' : ' checked' }}>
			<label for="upload_videos" class="check-box"></label>
			<label class="text_label" for="">{{ __('settings.roles.Upload Videos') }}</label>
		</div>

		<div class="check-2 col-xs-6 col-sm-6 col-md-6">
			<input id="upload_images" type="checkbox"{{ empty($info['upload_images']) ? '' : ' checked' }}>
			<label for="upload_images" class="check-box"></label>
			<label class="text_label" for="">{{ __('settings.roles.Upload Images') }}</label>
		</div>
	</div>

	<form>
		<div class="max-upload form-group row">
			<div class="col-md-12">
				<label class="labelText" for="max_upload_mb">{{ __('settings.roles.Max Upload(MB)') }}:</label>
				<input class="form-control modalInputs" id="max_upload_mb" type="number" value="{{ $info['max_upload_mb'] ?? '' }}">
			</div>
		</div>
	</form>
 
    
	<button type="button" class="btn btn-default btn-block addAccountBtn">{{ __('settings.roles.Save') }}</button>
</div>

<script>

	$("#proModal{{ $_mn }} .addAccountBtn").click(function()
	{
		var	role_name	 		= $("#proModal{{ $_mn }} #role_name").val(),
			max_post_per_day	= $("#proModal{{ $_mn }} #max_post_per_day").val(),
			max_fb_accounts		= $("#proModal{{ $_mn }} #max_fb_accounts").val(),
			monthly_price		= $("#proModal{{ $_mn }} #monthly_price").length > 0 ? $("#proModal{{ $_mn }} #monthly_price").val() : '0',
			annual_price		= $("#proModal{{ $_mn }} #annual_price").length > 0 ? $("#proModal{{ $_mn }} #annual_price").val() : '0',
			expire_days	 		= $("#proModal{{ $_mn }} #expire_days").length > 0 ? $("#proModal{{ $_mn }} #expire_days").val() : '0',
			upload_videos	 	= $("#proModal{{ $_mn }} #upload_videos").is(':checked')?1:0,
			upload_images	 	= $("#proModal{{ $_mn }} #upload_images").is(':checked')?1:0,
			max_upload_mb	 	= $("#proModal{{ $_mn }} #max_upload_mb").val();

		if( role_name == '' || max_post_per_day == '' || max_fb_accounts == '' || max_upload_mb == '' || monthly_price == '' || annual_price == '' || expire_days == '' )
		{
			proApp.alert('{{ __('settings.roles.ValidationError') }}')
			return;
		}

		proApp.ajax('{{ url('settings/rolesAddEdit/save') }}' , {
			'id':				'{{ $id }}',
			'role_name':		role_name,
			'max_post_per_day':	max_post_per_day,
			'max_fb_accounts':	max_fb_accounts,
			'expire_days':		expire_days,
			'upload_videos':	upload_videos,
			'upload_images':	upload_images,
			'monthly_price':	monthly_price,
			'annual_price':	    annual_price,
			'max_upload_mb':	max_upload_mb
		} , function( result )
		{

			$("#proModal{{ $_mn }}").modal('hide');
			location.reload();

		} , true);
	});
</script>