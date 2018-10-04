@extends('layouts.app_nologin')

@section('title_text')
    Reset Password
@endsection

@section('title')
    Reset Password
@endsection

@section('content')
    <style>
        .forgetBtn
        {
            width: 160px;
            height: 40px;
            line-height: 43px;
            background: #4c83c7;
            border-radius: 3px;
            color: #ffffff;
            font-family: 'Fira Sans', sans-serif;
            font-size: 13.0px;
            font-style: normal;
            font-stretch: normal;
            font-weight: 700;
            text-align: center;
        }
    </style>

    <div id="con-item-1" style="display: block !important;">
        <div class="panel-body">
            <form class="form-group" method="POST" action="{{ route('password.request') }}">
                <div class="form-content" style="padding-top: 0;">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">


                    <div class="form-item{{ $errors->has('email') ? ' has-error' : '' }}" style="padding-top: 0;">
                        <div class="form-label">
                            <label for="email">E-Mail:</label>
                        </div>
                        <div class="form-input">
                            <input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>


                    <div class="form-item{{ $errors->has('password') ? ' has-error' : '' }}" style="padding-top: 10px;">
                        <div class="form-label">
                            <label for="password">Password:</label>
                        </div>
                        <div class="form-input">
                            <input id="password" type="password" class="form-control" name="password" required>

                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-item{{ $errors->has('password_confirmation') ? ' has-error' : '' }}" style="padding-top: 10px;">
                        <div class="form-label">
                            <label for="password-confirm">Confirm Password:</label>
                        </div>
                        <div class="form-input">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>

                            @if ($errors->has('password_confirmation'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password_confirmation') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex user-operations">
                        <div class="form-item">
                            <button type="submit" class="forgetBtn">Reset passowrd</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
