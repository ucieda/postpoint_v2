<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>

    <title>@yield('title') {{ ' | ' . siteOption('site_name') }}</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/png" href="{{ siteOption('site_favicon') }}" >
    <meta name="description" content="{{ siteOption('site_description') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap-theme.css"> -->

    <!-- <link rel="stylesheet" href="css/font-awesome.css"> -->

	<link rel="stylesheet" type="text/css" href="{{ url('css/vendor.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ url('css/style.css') }}">
	<link rel="stylesheet" href="{{ url('css/popup/popop.css') }}">
	<link rel="stylesheet" href="{{ url('plugin/datepicker/css/bootstrap-datepicker3.min.css') }}">
	<link rel="stylesheet" href="{{ url('css/jquery-ui.css') }}" />
    @yield('style')

    <script type="text/javascript" src="{{ url('js/jquery-3.2.1.js') }}"></script>
	<script type="text/javascript" src="{{ url('js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ url('plugin/datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/main.js') }}"></script>
    <script type="text/javascript" src="{{ url('js/proApp.js') }}"></script>
	<script type="text/javascript" src="{{ url('js/jquery-ui.min.js') }}"></script>
    @yield('script')

	@if( siteOption('custom_css') )
		<style>
			{!! siteOption('custom_css') !!}
		</style>
	@endif
	{!! siteOption('header_js') !!}
</head>
<body>
    <div id="fullcontent">
        <header>
            <div class="container-fluid left-reset">
                <div class="row">
                    <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1 hidden-sm hidden-xs" id="logo-acitve">
                        <div class="logo-sector">
                            <div class="back-color">
                                <div class="img-area">
                                    <a href="{{ url('/home') }}">
                                        <img src="{{ siteOption('site_logo_xs' , url('img/combined-Shape.png')) }}" alt="">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
                        <div class="toggle-menu-bar">
                            <img src="{{ url('img/group-1.png') }}" alt="" id="menu-bar-btn">
                            <span>@yield('title')</span>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-7 col-md-6 col-lg-6">
                        <div class="user-profil">

                            <div class="col-lg-6 pull-right">
                                <div class="user">

                                    <div class="profil-item pull-right dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <div class="profil-img pull-left">
                                            @if(isset($fbAccInf['fb_account_id']))
                                            <img class="img-circle " width="45px" height="45px" src="https://graph.facebook.com/{{ $fbAccInf['fb_account_id'] }}/picture?redirect=1&height=40&width=40&type=normal">
                                            @else
                                            <img class="img-circle " width="45px" height="45px" src="{{ url('img/facebookUser.jpg') }}">
                                            @endif
                                            <span></span>
                                        </div>
                                        <span class="hidden-xs" >{{ $fbAccInf['name'] ?? 'Facebook User' }}</span>
                                    </div>

									<ul class="dropdown-menu pull-right">

										@foreach($accounts AS $account)
										<li>
											<a href="{{ url('switch_fb_account/' . $account->id) }}">
												<img src="https://graph.facebook.com/{{ $account->fb_account_id }}/picture?redirect=1&amp;height=40&amp;width=40&amp;type=normal" style="vertical-align:middle;" class="fbProfileImg" onerror="this.src = '{{ url('img/facebookUser.jpg') }}'" width="32px" height="32px">
												{{ $account->name }}
											</a>
										</li>
										@endforeach


										<li><a href="{{ url('settings/fb_accounts') }}">{{ __('general.Add Facebook account') }}</a></li>
										<li><a href="{{ url('payment_list') }}"><i class="fa fa-credit-card"></i> {{ __('general.Payment list') }}</a></li>
										<li><a href="{{ url('settings') }}"><i class="fa fa-fw fa-cog" style="    position: relative;left: -4px;" aria-hidden="true"></i>{{ __('general.Settings') }}</a></li>

										<li>
											<a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();"><i class="fa fa-sign-out" aria-hidden="true" style="margin-right:6px;"></i>{{ __('general.Logout') }}</a>
											<form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
												{{ csrf_field() }}
											</form>
										</li>
									</ul>

                                </div>
								<div class="dropdown" style="width: 40px !important; float: right;">
									<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
										<i class="fa fa-bell-o"></i>
										<?php $notificationsCount = \App\Notification::myCount() ?>
										<span class="label label-danger notifCount">{{ $notificationsCount > 0 ? $notificationsCount : '' }}</span>
									</a>
									<div class="dropdown-menu pull-right" style="width: 350px; line-height: 35px;">
										<div style="padding-left: 20px;">{{ __('general.you_have_n_notifications' , ['count' => $notificationsCount]) }}</div>
										<div class="notification-holder">
											@foreach(\App\Notification::my(6) AS $notifInf )
												<a href="{{ url('notifications/' . $notifInf->id) }}" class="notification1">{{ textShorter($notifInf->text , 40) }}</a>
											@endforeach
										</div>
										<div>
											<a href="{{ url('notifications') }}" class="notification1"><i class="fa fa-bullhorn"></i> {{ __('general.All notifications') }}</a>
										</div>
									</div>
								</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div id="main_content">

            <div class="col-md-1 left-reset">
                <aside class="hidden-xs hidden-sm aside hidden-print nav-xs" id="nav-aside">
                    <nav>
                        <ul>
                            <li class="nav-item{{ Route::currentRouteName()=='home' ? ' active-nav' : '' }}">
                                <a href="{{ url('home') }}" title="{{ __('general.Add post') }}">
                                    <img src="{{ url('img/menu-icons/plus.svg') }}">
                                </a>
                            </li>
                            <li class="nav-item{{ Route::currentRouteName()=='publishing' ? ' active-nav' : '' }}" title="{{ __('general.Publishing') }}">
                                <a href="{{ url('publishing') }}">
                                    <img src="{{ url('img/menu-icons/2.svg') }}">
                                </a>
                            </li>
                            <li class="nav-item{{ Route::currentRouteName()=='insights' ? ' active-nav' : '' }}">
                                <a href="{{ url('insights') }}" title="{{ __('general.Insights') }}">
                                    <img src="{{ url('img/menu-icons/statisticks.svg') }}">
                                </a>
                            </li>
                            @if( Auth::user()->is_admin )
                            <li class="nav-item{{ Route::currentRouteName()=='accounts' ? ' active-nav' : '' }}">
                                <a href="{{ url('accounts') }}" title="{{ __('general.Accounts') }}">
                                    <img src="{{ url('img/menu-icons/users.svg') }}">
                                </a>
                            </li>
                            @endif
                            <li class="nav-item{{ Route::currentRouteName()=='settings' ? ' active-nav' : '' }}">
                                <a href="{{ url('settings') }}" title="{{ __('general.Settings') }}">
                                    <img src="{{ url('img/menu-icons/settings.svg') }}">
                                </a>
                            </li>
                        </ul>
                    </nav>
                </aside>
                <div id="side-nav">

                    <a href="javascript:void(0)" class="close-btn" id="close-sidenav">&times;</a>
                    <nav class="nav-primary">
                        <ul class="nav">
                            <li{!! Route::currentRouteName()=='home' ? ' class="active"' : '' !!}>
                                <a href="{{ url('home') }}" class="auto font-bold">
                                    <i class="material-icons"></i>
                                    <span>{{ __('general.Add post') }}</span>
                                </a>
                            </li>
                            <li{!! Route::currentRouteName()=='publishing' ? ' class="active"' : '' !!}>
                                <a href="{{ url('publishing') }}" class="auto font-bold">
                                    <i class="material-icons"></i>
                                    <span>{{ __('general.Publishing') }}</span>
                                </a>
                            </li>
                            <li{!! Route::currentRouteName()=='insights' ? ' class="active"' : '' !!}>
                                <a href="{{ url('insights') }}" class="auto font-bold">
                                    <i class="material-icons"></i>
                                    <span>{{ __('general.Insights') }}</span>
                                </a>
                            </li>
                            <li{!! Route::currentRouteName()=='accounts' ? ' class="active"' : '' !!}>
                                <a href="{{ url('accounts') }}" class="auto font-bold">
                                    <i class="material-icons"></i>
                                    <span>{{ __('general.Accounts') }}</span>
                                </a>
                            </li>
                            <li{!! Route::currentRouteName()=='settings' ? ' class="active"' : '' !!}>
                                <a href="{{ url('settings') }}" class="auto font-bold">
                                    <i class="material-icons"></i>
                                    <span>{{ __('general.Settings') }}</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="content-holder">
                <div id="main_section">
					@if( !empty(siteOption('ads_banner')) && in_array( (string)\Illuminate\Support\Facades\Auth::user()->user_role_id , explode(',' , (string)siteOption('show_ads_to')) ) )
					<div>{!! siteOption('ads_banner') !!}</div>
					@endif
                	@yield('content')
                </div>
            </div>

        </div>

      
    </div>

</body>
{!! siteOption('footer_js') !!}
</html>