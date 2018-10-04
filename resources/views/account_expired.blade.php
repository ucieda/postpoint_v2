@extends('layouts.app')

@section('title')
	{{ __('general.Account expired') }}
@endsection

@section('content')
	<div class="container-fluid charts">
		<div class="alert alert-danger">
			{{ __('general.account_expired2') }} ( {{ __('general.Expiry date') }}: {{ date('Y-m-d' , strtotime(Auth::user()->expire_on)) }} )
			<a href="{{ url('account_upgrade') }}" style="margin-left: 10px;">{{ __('general.Upgrade Account') }}</a>
		</div>
	</div>
@endsection
