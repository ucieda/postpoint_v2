@if(!\Illuminate\Support\Facades\Auth::user()->is_admin)
	{!! die() !!}
@endif
<ul class="resp-tabs-list show">
	<li{!! $menu2 == 'general' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app') }}';">{{ __('settings.app.General') }}</li>
	<li{!! $menu2 == 'publish' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/publish') }}';">{{ __('settings.app.Publish settings') }}</li>
	<li{!! $menu2 == 'theme' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/theme') }}';">{{ __('settings.app.Theme settings') }}</li>
	<li{!! $menu2 == 'ads' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/ads') }}';">{{ __('settings.app.Ads Settings') }}</li>
	<li{!! $menu2 == 'social_login' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/social_login') }}';">{{ __('settings.app.Social login') }}</li>
	<li{!! $menu2 == 'advanced' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/advanced') }}';">{{ __('settings.app.Advanced settings') }}</li>
	<li{!! $menu2 == 'mail' ? ' class="resp-tab-active"' : '' !!} onclick="location.href='{{ url('settings/app/mail') }}';">{{ __('settings.app.Mail settings') }}</li>
</ul>