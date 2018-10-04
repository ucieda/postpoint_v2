@extends('layouts.app_install')

@section('title_text')
Installation - Requirments
@endsection

@section('title')
Installation - Requirments
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
		.label
		{
			float: right;
			font-size: 11px;
		}
	</style>
	<div class="con" style="height: auto;">
		<div class="row" style="margin-top: 20px;">
			<div class="col-md-8 col-md-offset-2">

				<ul class="list-group">
					<li class="list-group-item">
						PHP version >= 7.1.3 <span class="label label-{{ $requirments['php_ver'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['php_ver'] }}</span>
					</li>
					<li class="list-group-item">
						PHP config ( allow_url_fopen=On ) <span class="label label-{{ $requirments['allow_url_fopen'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['allow_url_fopen'] }}</span>
					</li>
					<li class="list-group-item">
						OpenSSL <span class="label label-{{ $requirments['open_ssl'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['open_ssl'] }}</span>
					</li>
					<li class="list-group-item">
						cURL <span class="label label-{{ $requirments['curl'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['curl'] }}</span>
					</li>
					<li class="list-group-item">
						PDO <span class="label label-{{ $requirments['pdo'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['pdo'] }}</span>
					</li>
					<li class="list-group-item">
						mbstring <span class="label label-{{ $requirments['mbstring'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['mbstring'] }}</span>
					</li>
					<li class="list-group-item">
						JSON <span class="label label-{{ $requirments['json'] == 'yes' ? 'success' : 'danger' }}">{{ $requirments['json'] }}</span>
					</li>
				</ul>
				@if( crontab_installed() == false )
					<div style="margin-bottom: 10px; color: #ff6753;"><i class="fa fa-warning"></i> Crontab not installed on server! Scheduled posts will not work!</div>
				@endif
				<a href="{{ $start ? url('install/step1') : '#' }}" class="btn btn-info"{{ $start ? '' : ' disabled' }}><i class="fa fa-angle-right"></i> Install</a>
			</div>
		</div>
	</div>
@endsection