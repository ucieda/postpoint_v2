@extends('layouts.app')

@section('title')
	{{ __('general.Account deactivated') }}
@endsection

@section('content')
	<div class="container-fluid charts">
		<div class="alert alert-danger">
			{{ __('general.account_deactivated2') }}
		</div>
	</div>
@endsection
