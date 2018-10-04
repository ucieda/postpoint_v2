@extends('layouts.app_install')

@section('title_text')
Installing - Step 2
@endsection

@section('title')
Installing - Step 2
@endsection

@section('content')
	<style>
		.btn-info
		{
			background: #4c83c7 !important;
			border: none;
			outline: none;
			height: 40px;
			text-align: center;
			border-radius: 3px;
			padding: 10px;
			min-width: 140px;
		}
		label
		{
			margin:			0;
			padding:		0;
			color:			#48545f;
			font-family:	'FiraSans', sans-serif;
			font-size:		13.0px;
			font-style:		normal;
			font-stretch:	normal;
			font-weight:	500;
			text-align:		left;
		}
		.form-control
		{
			width:			100%;
			border:			1px solid #b7c5ce;
			color:			#48545f;
			font-family:	'FiraSans', sans-serif;
			font-size:		13.0px;
			font-style:		normal;
			font-stretch:	normal;
			font-weight:	500;
			font-size:		20px;
			border:			1px solid #B7C5CE;
			height:			40px !important;
			margin-top:		10px;
		}
	</style>

	<div class="con">
		<div class="row" style="margin-top: 20px;">
			<div class="col-md-8 col-md-offset-2">
				@if ($errors->any())
					<div class="alert alert-danger">
						{{ __('settings.form_error') }}
					</div>
				@endif
				@if (\Session::has('success'))
					<div class="alert alert-success">
						{{ __('settings.saved_successfull') }}
					</div>
				@endif
			</div>
			<div class="col-md-8 col-md-offset-2">
				{{ Form::open(array('url' => 'install/step2/save')) }}

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label>Site title</label>
								{{ Form::text('site_title', '' , ['class' => 'form-control']) }}
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Admin Username</label>
								{{ Form::text('admin_username', '' , ['class' => 'form-control']) }}
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Admin Email</label>
								{{ Form::text('admin_email', '' , ['class' => 'form-control']) }}
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Admin password</label>
								{{ Form::password('admin_password',  ['class' => 'form-control']) }}
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Confirm password</label>
								{{ Form::password('admin_password_confirmation',  ['class' => 'form-control']) }}
							</div>
						</div>
					</div>

					<button type="submit" class="btn btn-info"><i class="fa fa-angle-right"></i> Step 3</button>
				{{ Form::token() }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
@endsection