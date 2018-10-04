@extends('layouts.app')

@section('title')
	{{ __('general.Upgrade Account') }}
@endsection

@section('content')
	<style>
		.p-title-top
		{
			text-align: center;
			color: #888;
			font-size: 20px;
			margin-bottom: 20px;
			font-weight: 300;
		}

		.details-box
		{
			background: #FFF;
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;
			border: 1px solid #DDD;
			padding: 40px 50px;
			min-height: 200px;

			font-size: 15px;
			color: #555;
		}

		.d-chooses
		{
			display: flex;
		}

		.d-chooses > div
		{
			display: flex;
			cursor: pointer;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			width: 300px;
			height: 170px;
			background: #fafafa;
			margin-right: 10px;
			border: 1px solid #DDD;

			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;

			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}

		.d-chooses > div.selected
		{
			border: 4px solid #74b9ff;
		}

		.p-detail
		{
			margin-bottom: 20px;
		}

		.d-choose-title
		{
			font-size: 20px;
			font-weight: 300;
			color: #777;
			margin-bottom: 10px;
		}

		.d-choose-1
		{
			color: #74b9ff;
			font-size: 20px;
		}

		.d-choose-2
		{
			text-align: center;
			padding: 10px 20px;
			color: #888;
		}

		.place-order-btn
		{
			background: #9b59b6;
			border: 0;
			color: #FFF;
			width: 300px;
			height: 50px;

			margin-top: 20px;

			-webkit-border-radius: 5px;
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
	</style>

	<div class="p-title-top"> Now please select package options and payment gateway to renew your account! </div>

	<div class="details-box">

		<div class="p-detail" data-choose-id="plan">
			<div class="d-choose-title">Please select your plan</div>
			<div class="d-chooses">
				<div class="selected" data-id="monthly">
					<div class="d-choose-1">Monthly plan</div>
					<div class="d-choose-2">{{ number_format($packageInf->monthly_price , 2) }} USD</div>
				</div>
				<div data-id="annual">
					<div class="d-choose-1">Annual plan</div>
					<div class="d-choose-2">{{ number_format($packageInf->annual_price , 2) }} USD</div>
				</div>
			</div>
		</div>

		<div class="p-detail" data-choose-id="payment_method">
			<div class="d-choose-title">Choose a payment method</div>
			<div class="d-chooses">
				<div class="selected" data-id="stripe">
					<img src="{{ url('img/v_card.png') }}" style="width: 150px;">
				</div>
				<div data-id="paypal">
					<img src="{{ url('img/paypal.png') }}" style="width: 156px; background: #FFF; border: 1px solid #CCC; padding: 8px;">
				</div>
			</div>
		</div>

		<div class="p-detail" data-choose-id="payment_cycle">
			<div class="d-choose-title">Payment Cycle</div>
			<div class="d-chooses">
				<div class="selected" data-id="one_time">
					<div class="d-choose-1">One time</div>
					<div class="d-choose-2">This is one time payment</div>
				</div>
				<div data-id="recurring">
					<div class="d-choose-1">Recurring Payment</div>
					<div class="d-choose-2">You'll be charged at the end of each cycle automatically</div>
				</div>
			</div>
		</div>

		<div>
			<button type="button" id="placeOrderBtn" class="place-order-btn">PLACE ORDER</button>
		</div>

	</div>

	<script>
		$(document).ready(function()
		{
			var saveOrderId;

			$(".details-box .d-chooses > div").click(function()
			{
				$(this).parent().children('.selected').removeClass('selected');
				$(this).addClass('selected');
			});

			$("#placeOrderBtn").click(function()
			{
				var data = {
					'package_id': '{{ $packageInf->id }}'
				};

				$("[data-choose-id]").each(function()
				{
					data[ $(this).data('choose-id') ] = $(this).find('.selected').data('id');
				});

				proApp.ajax('{{ url('ajax/account_upgrade/order') }}' , data , function(result)
				{
					if( data['payment_method'] == 'paypal' )
					{
						if( 'url' in result )
							location.href = result['url'];
						else if( 'error_msg' in result )
							proApp.alert( result['error_msg'] )
					}
					else
					{
						saveOrderId = result['id'];

						handler.open({
							name: result['package_name'],
							description: result['description'],
							amount: result['amount'],
							currency: 'USD',
							email: "{{ \Illuminate\Support\Facades\Auth::user()->email }}"
						});
					}
				});
			});


			var handler = StripeCheckout.configure({
				key: "{{ siteOption('stripe_publish_key') }}",
				image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
				locale: 'auto',
				token: function(token)
				{
					location.href = "{{ url('/account_upgrade/finish') }}?success=true&id=" + saveOrderId + "&token=" + token.id
				}
			});

			window.addEventListener('popstate', function()
			{
				handler.close();
			});
		});
	</script>
	<script src="https://checkout.stripe.com/checkout.js"></script>
@endsection
