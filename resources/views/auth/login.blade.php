@extends('layouts.app_nologin')

@section('title_text')
	Please, fill all fields to Log in
@endsection

@section('title')
	Login
@endsection

@section('content')
	<div id="con-item-1" style="display: block !important;">
		<form action="{{ route('login') }}" class="form-group" method="POST">
			{{ csrf_field() }}
			<div class="form-content" >
				@if($errors->any())
					<div style="color: #ff714d;"><i class="fa fa-warning"></i> {{ __('auth.auth_incorrect') }}</div>
				@endif
				<div class="form-item{{ $errors->has('username') ? ' has-error' : '' }}">
					<div class="form-label">
						<label>Username:</label>
					</div>
					<div class="form-input">
						<input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
					</div>
				</div>

				<div class="form-item{{ $errors->has('password') ? ' has-error' : '' }}">
					<div class="form-label">
						<label>Password:</label>
					</div>
					<div class="form-input">
						<input type="password" name="password" class="form-control" required>
					</div>
				</div>

				<div class="flex user-operations">
					<div class="form-item">
						<input type="checkbox" id="cbtest2" name="remember" {{ old('remember') ? 'checked' : '' }}/>
						<label for="cbtest2" class="check-box"></label>
						<label class="text_label" for="cbtest2">Remember me</label>
					</div>
					<div class="form-item">
						<button type="submit">log in</button>
					</div>
				</div>

				@if(isset($linkFb))
				<div style="margin-top: 10px; text-align: center;">
					<a href="{{ $linkFb }}" style="text-decoration: none;background: #4C83C7;color: #FFF;padding: 8px 13px;border-radius: 3px;" class="nav-item nav-item-togglee"><i class="fa fa-facebook-f"></i> &nbsp;&nbsp;&nbsp;Login with Facebook</a>
				</div>
				@endif
			</div>
		</form>
	</div>
@endsection