@extends('layouts.app_nologin')

@section('title_text')
	Please, fill personal details to add account
@endsection

@section('title')
    Register
@endsection

@section('content')
    <div id="con-item-2" style="display: block !important;">
        <form action="{{ route('register') }}" class="form-group" method="POST">
            {{ csrf_field() }}
            <div class="form-content" >
                <div class="form-item{{ $errors->has('username') ? ' has-error' : '' }}">
                    <div class="form-label">
                        <label>Username:</label>
                    </div>
                    <div class="form-input">
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}">
                    </div>
                </div>

                <div class="form-item{{ $errors->has('email') ? ' has-error' : '' }}">
                    <div class="form-label">
                        <label>E-mail:</label>
                    </div>
                    <div class="form-input"{!! isset($email) ? ' style="display: none;"' : '' !!}>
                        <input type="text" name="email" class="form-control" value="{{ isset($email) ? $email : old('email') }}">
                    </div>
					@if(isset($email))
						<div class="form-input" style="padding-top: 17px;">{{ $email }}</div>
					@endif
                </div>


                <div class="form-item{{ $errors->has('password') ? ' has-error' : '' }}">
                    <div class="form-label">
                        <label>Password:</label>
                    </div>
                    <div class="form-input">
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>

                <div class="form-item{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                    <div class="form-label">
                        <label>Re-enter password:</label>
                    </div>
                    <div class="form-input">
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <div class="flex user-operations">
                    <div class="form-item">
                        <input type="checkbox" id="cbtest" name="i_agree" {{ old('i_agree') ? 'checked' : '' }}/>
                        <label for="cbtest" class="check-box"></label>
                        <label class="text_label" for="cbtest" style="{{ $errors->has('i_agree') ? 'color: #e74c3c;' : '' }}">I agree with Terms</label>
                    </div>
                    <div class="form-item">
                        <button>add account</button>
                    </div>
                </div>
            </div>

			@if(isset($linkFb))
				<div style="margin-top: 10px; text-align: center;">
					<a href="{{ $linkFb }}" style="text-decoration: none;background: #4C83C7;color: #FFF;padding: 8px 13px;border-radius: 3px;" class="nav-item nav-item-togglee"><i class="fa fa-facebook-f"></i> &nbsp;&nbsp;&nbsp;Login with Facebook</a>
				</div>
			@endif

        </form>
    </div>
@endsection
