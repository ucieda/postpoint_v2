<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title') | {{ siteOption('site_name') }}</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" href="{{ siteOption('site_favicon') }}" >
	<meta name="description" content="{{ siteOption('site_description') }}">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="stylesheet" type="text/css" href="{{ url('css/vendor.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ url('css/login.css') }}">

	<script type="text/javascript" src="{{ url('js/jquery-1.10.2.js') }}"></script>
</head>
<body>


<div id="login">
	<div   id="maincontent">
		<div class="conatiner-fluid">
			<div class="row">
				<div class="col-md-5 left-sector">
					<div class="left_content">
						<header>
							<nav>
								<a href="{{ url('login') }}" style="text-decoration: none;" class="nav-item nav-item-toggle{{ \Request::route()->getName() == 'login' ? ' nav-active' : '' }}">
									Log in account
								</a>
								@if( siteOption('is_register_active') )
								<a href="{{ url('register') }}" style="text-decoration: none;" class="nav-item nav-item-togglee{{ \Request::route()->getName() == 'register' ? ' nav-active' : '' }}">Add new account</a>
								@endif
							</nav>
						</header>

						<div class="logo_content hidden-xs hidden-sm">
							<div class="logo">
								<img src="{{ siteOption('site_logo_l' , 'img/logo.png') }}" alt="">
							</div>
						</div>

						<div class="foot-content hidden-xs hidden-sm">
							<span class="foot-text">
								© 2018 Postpoint.  All rights reserved
							</span>
							{{ siteOption('footer_text') }}
						</div>

					</div>
				</div>

				<div class="col-md-7 right-sector">
					<div class="right_content">
						<header>
							<span class="head-text">@yield('title_text')</span>
						</header>
						<div class="con">
							@yield('content')
						</div>
						<footer>
							<a href="{{ url('password/reset') }}" class="foot-text">
								Forget password or username?
							</a>
						</footer>
					</div>
				</div>
				<!-- end right content	 -->
			</div>
		</div>
	</div>
</div>
<!-- end content -->
</body>
</html>