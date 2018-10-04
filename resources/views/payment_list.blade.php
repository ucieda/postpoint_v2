@extends('layouts.app')

@section('title')
	{{ __('general.Payment list') }}
@endsection

@section('content')
	<style>
		.btn
		{
			color: #FFF !important;
		}
	</style>
	@php( $statusArr = [
		'0'	=>	__('general.Not paid'),
		'1'	=>	__('general.Paid'),
		'2'	=>	__('general.Cancelled'),
		'3'	=>	__('general.Not Approved'),
		'4' =>	__('general.Subscribed'),
		'5' =>	__('general.Subscription cancelled'),
	] )
	@php( $paymentMethods = [
		'paypal'	=>	__('general.PayPal'),
		'stripe'	=>	__('general.STRIPE')
	] )
	@php( $plans = [
		'monthly'	=>	__('general.Monthly'),
		'annual'	=>	__('general.Annual')
	] )
	@php( $paymentCycles = [
		'one_time'	=>	__('general.One time'),
		'recurring'	=>	__('general.Recurring')
	] )
	@php( $indx = 0 )
	<div style="background: #FFF; padding: 25px; padding-bottom: 10px;">
		<div style="margin: 0 0 15px 0;"><a href="{{ url('account_upgrade') }}" class="btn btn-success">{{ __('general.Upgrade Account') }}</a> </div>
		<table class="table table-bordered dataTable no-footer">
			<thead>
			<tr>
				<th>#</th>
				<th>{{ __('general.Payment date') }}</th>
				<th>{{ __('general.Amount') }}</th>
				<th>{{ __('general.Plan') }}</th>
				<th>{{ __('general.Package') }}</th>
				<th>{{ __('general.Payment method') }}</th>
				<th>{{ __('general.Payment cycle') }}</th>
				<th>{{ __('general.Status') }}</th>
				<th></th>
			</tr>
			</thead>
			<tbody>
				@foreach($payments AS $paymentInf)
				<tr{!! $paymentInf->parent_id > 0 ? ' style="background: #F8FBFF;"' : '' !!}>
					<td>{{ ++$indx }}</td>
					<td>{{ date(dateFormat() , strtotime($paymentInf->added_time)) }}</td>
					<td>{{ number_format($paymentInf->amount , 2) }} USD</td>
					<td>{{ $plans[$paymentInf->plan] ?? '--' }}</td>
					<td>{{ $paymentInf->package_name }}</td>
					<td>{{ $paymentMethods[$paymentInf->payment_method] ?? '--' }}</td>
					<td>{{ $paymentCycles[$paymentInf->payment_cycle] ?? '--' }}</td>
					<td>{{ $statusArr[$paymentInf->status] ?? '--' }}</td>
					<td>
						@if( $paymentInf->parent_id == 0 && $paymentInf->is_subscribed == 1 )
							<form method="post" action="{{ url('/payment_list/cancel_subscription') }}">
								{{ csrf_field() }}
								<input type="hidden" name="payment_id" value="{{ $paymentInf->id }}">
								<button type="submit" class="btn btn-warning">{{ __('general.Cancel subscription') }}</button>
							</form>
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection
