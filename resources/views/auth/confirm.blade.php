@extends('layouts.app_nologin')

@section('title_text')

@endsection

@section('title')
	User confirmation
@endsection

@section('content')
	<div id="con-item-1" style="padding: 30px; display: block !important;">
		<div style="font-size: 20px; font-weight: 400; margin-bottom: 20px;">User confirmation:</div>
		@if($data)
			<div class="alert alert-success" role="alert">
				User has ben activated successful!
			</div>
		@else
			<div class="alert alert-danger" role="alert">
				User token confirmation error!
			</div>
		@endif
	</div>
@endsection