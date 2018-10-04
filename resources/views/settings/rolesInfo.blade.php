@if(!\Illuminate\Support\Facades\Auth::user()->is_admin)
	{!! die() !!}
@endif
<div class="modal-body bg-grey">
	<ul class="list-group">
		<li class="list-group-item item-1">
			<span class="badge blue">{{ $info->name }}</span> {{ __('settings.roles.Name') }}:
		</li>
		@if(!$info->is_for_demo)
		<li class="list-group-item item-1">
			<span class="badge blue">{{ $info->monthly_price }} USD</span> {{ __('settings.roles.Monthly price (USD)') }}:
		</li>
		<li class="list-group-item item-1">
			<span class="badge blue">{{ $info->annual_price }} USD</span> {{ __('settings.roles.Annual price (USD)') }}:
		</li>
		@endif
		<li class="list-group-item item-2">
			<span class="badge orange">{{ $info->max_posts_per_day }}</span> {{ __('settings.roles.Max Posts') }}:
		</li>
		<li class="list-group-item item-3">
			<span class="badge blue">{{ $info->max_fb_accounts }}</span> {{ __('settings.roles.Max Facebook account') }}:
		</li>
		@if($info->is_for_demo)
			<li class="list-group-item item-3">
				<span class="badge blue">{{ $info->account_expire_days }}</span> {{ __('settings.roles.Account expiry') }}:
			</li>
		@endif

		<li class="list-group-item item-3">
			<span class="badge blue">{{ $info->upload_videos ? 'Yes' : 'No' }}</span> {{ __('settings.roles.Can upload videos') }}:
		</li>
		<li class="list-group-item item-3">
			<span class="badge blue">{{ $info->upload_images ? 'Yes' : 'No' }}</span> {{ __('settings.roles.Can upload images') }}:
		</li>
		<li class="list-group-item item-3">
			<span class="badge blue">{{ $info->max_upload_mb }} Mb</span> {{ __('settings.roles.Max Upload') }}:
		</li>
	</ul>
</div>
<div class="modal-footer">
	<button type="button" data-dismiss="modal" class="btn btn-info">{{ __('general.Close') }}</button>
</div>