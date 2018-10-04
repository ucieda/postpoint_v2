<style>
	.resp-tab-active {
		color: #96abc0 !important;
	}
</style>
<ul class="resp-tabs-list show">
	<li{!! $menu1 == 'profile' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/profile') }}';">{{ __('settings.my Profile') }}</li>
	<li{!! $menu1 == 'general' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/general') }}';">{{ __('settings.General settings') }}</li>
	<li{!! $menu1 == 'publish' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/publish') }}';">{{ __('settings.Publish settings') }}</li>
	<li{!! $menu1 == 'fb_accounts' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/fb_accounts') }}';">{{ __('settings.Facebook accounts') }}</li>
	<li{!! $menu1 == 'fb_apps' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/fb_apps') }}';">{{ __('settings.Facebook Apps') }}</li>
	@if( \Illuminate\Support\Facades\Auth::user()->is_admin )
	<li{!! $menu1 == 'app' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app') }}';">{{ __('settings.App settings') }}</li>
	<li{!! $menu1 == 'roles' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/roles') }}';">{{ __('settings.Roles') }}</li>

	<li{!! $menu1 == 'paypal' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/paypal') }}';">{{ __('settings.Paypal') }}</li>
	<li{!! $menu1 == 'stripe' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/stripe') }}';">{{ __('settings.Stripe') }}</li>
	@endif
</ul>