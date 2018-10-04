<!DOCTYPE html>
<html lang="en">
<head>
	<title>@yield('title')</title>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
				<div class="col-md-12 right-sector">
					<div class="right_content" style="height: auto; min-height: 460px;">
						<header>
							<span class="head-text">@yield('title_text')</span>
						</header>
						@yield('content')
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>