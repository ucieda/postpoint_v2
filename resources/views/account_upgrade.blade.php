@extends('layouts.app')

@section('title')
	{{ __('general.Upgrade Account') }}
@endsection

@section('content')
	<style>

		.packets-box
		{

		}

		.packet-class
		{
			width: 300px;
			background: #FFF;
			border: 1px solid #EEE;
			min-height: 200px;
			padding: 15px;
			color: #555;
			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
			margin-bottom: 10px;
			margin-right: 10px;
			display: inline-block;
		}

		.packet-class > *
		{
			text-align: center;
			margin: 15px;
		}

		.btn-select
		{
			background: #9b59b6;
			border: 0;
			color: #FFF;
			width: 180px;
			height: 40px;

			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}


		.p-price > div:first-child
		{
			font-size: 35px;
			font-weight: 300;
			color: #74b9ff;
		}
		.p-price > div:last-child
		{
			font-size: 15px;
			font-weight: 300;
			color: #74b9ff;
		}

		.p-name
		{
			font-size: 20px;
			font-weight: 400;
			color: #444;
			margin: 20px;
		}


		.p-annual-price > span , .p-you-saved > span , .p-max-posts > span , .p-storage > span
		{
			color: #6c5ce7;
			font-size: 15px;
		}

		.p-you-saved
		{
			border-bottom: 1px solid #DDD;
			padding-bottom: 15px;
		}

		.p-accounts-count
		{
			color: #999;
			border-bottom: 1px solid #DDD;
			padding-bottom: 15px;
		}

		.p-max-posts
		{
			border-bottom: 1px solid #DDD;
			padding-bottom: 15px;
		}

		.p-upload-videos
		{
			border-bottom: 1px solid #DDD;
			padding-bottom: 15px;
		}

		.line-through-text
		{
			text-decoration: line-through;
			color: #ff7675;
		}

		.p-title-top
		{
			text-align: center;
			color: #3498db;
			font-size: 20px;
			margin-bottom: 20px;

		}
	</style>

	<div class="p-title-top">Please select one of the packages listed below to subscribe!</div>

	<div class="packets-box">
		@foreach( $packets AS $packet )
			<div class="packet-class">

				<div class="p-price">
					<div>{{ number_format($packet->monthly_price , 2) }}</div>
					<div>USD/per month</div>
				</div>

				<div class="p-name">{{ $packet->name }}</div>

				<div class="p-annual-price">Annual Price: <span>{{ $packet->annual_price }} USD</span></div>

				<div class="p-you-saved">You save: <span>{{ number_format($packet->monthly_price * 12 - $packet->annual_price , 2) }} USD</span></div>

				<div class="p-accounts-count">
					@if( $packet->max_fb_accounts == 1 )
						Only 1 account
					@else
					Up to {{ $packet->max_fb_accounts }} accounts
					@endif
				</div>

				<div class="p-max-posts">Max. posts per day: <span>{{ $packet->max_posts_per_day }} posts</span></div>

				<div class="p-upload-photos{{ !$packet->upload_images ? ' line-through-text' : '' }}">Upload photos</div>

				<div class="p-upload-videos{{ !$packet->upload_videos ? ' line-through-text' : '' }}">Upload videos</div>

				<div class="p-storage">Storage: <span>{{ $packet->max_upload_mb }}Mb</span></div>

				<div class="p-upgrade-btn">
					<button type="button" class="btn-select" onclick="location.href=$(this).data('href');" data-href="{{ url('account_upgrade/' . $packet->id) }}">SELECT</button>
				</div>

			</div>
		@endforeach
	</div>

@endsection
