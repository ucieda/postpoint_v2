@extends('layouts.app')

@section('title')
	{{ __('general.Permission error') }}
@endsection

@section('content')
	<div class="container-fluid charts">
		<div class="alert alert-danger">
			{{ __('general.Permission error') }}
		</div>
	</div>
@endsection
