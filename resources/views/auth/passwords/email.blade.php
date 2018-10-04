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
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form class="form-group" method="POST" action="{{ route('password.email') }}">
                {{ csrf_field() }}
                <div class="form-content">

                    <div class="form-item{{ $errors->has('email') ? ' has-error' : '' }}">
                        <div class="form-label">
                            <label for="email">E-Mail:</label>
                        </div>
                        <div class="form-input">
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
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
