<?php

namespace App\Lib\Payments;

use App\Payment;
use App\User;
use Illuminate\Support\Facades\Auth;

class Stripe
{

	private static function construct()
	{
		\Stripe\Stripe::setApiKey(siteOption('stripe_secret_key'));
	}

	public static function webhook()
	{
		$payload = @file_get_contents("php://input");

		$endpoint_secret = siteOption('stripe_webhook_secret');

		if (!isset($_SERVER["HTTP_STRIPE_SIGNATURE"]))
		{
			http_response_code(400);
			die("Error!");
		}

		self::construct();

		$sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
		$event = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch(\UnexpectedValueException $e) {
			// Invalid payload
			http_response_code(400);
			exit;
		} catch(\Stripe\Error\SignatureVerification $e) {
			// Invalid signature
			http_response_code(400);
			exit;
		}

		if( $event->type == 'invoice.payment_succeeded' )
		{
			self::recurringPayment( $event );
		}
		else if( $event->type == 'customer.subscription.deleted' )
		{
			self::unSubscribed($event);
		}

		http_response_code(200);
	}

	private static function recurringPayment( $event )
	{
		$eventobj = $event->data->object;

		if (empty($eventobj->charge))
		{
			// Invalid charge id
			http_response_code(400);
			exit;
		}

		if (empty($eventobj->subscription))
		{
			// Invalid subscription id
			http_response_code(400);
			exit;
		}

		$subscriptionId = $eventobj->subscription;
		try
		{
			$subscription = \Stripe\Subscription::retrieve($subscriptionId);
		}
		catch (\Exception $e)
		{
			// Couldn't get subscription data
			http_response_code(400);
			exit;
		}

		if ( empty($subscription->metadata->payment_id) )
		{
			// Invalid subscription data
			http_response_code(400);
			exit;
		}

		$paymentId = $subscription->metadata->payment_id;
		$payment = Payment::where('id' , $paymentId)->first( );

		if ( !$payment || $payment->status != 4 || $payment->is_subscribed != 1 || $payment->subscription_id != $subscriptionId )
		{
			// Invalid order
			http_response_code(400);
			exit('Error N 0001');
		}

		Payment::insert([
			'user_id'			=>	$payment->user_id,
			'package_id'		=>	$payment->package_id,
			'amount'			=>	$payment->amount,
			'status'			=>	'1',
			'added_time'		=>	date('Y-m-d H:i:s'),
			'package_name'		=>	$payment->package_name,
			'payment_method'	=>	$payment->payment_method,
			'plan'				=>	$payment->plan,
			'payment_cycle'		=>	$payment->payment_cycle,
			'is_subscribed'		=>	'0',
			'subscription_id'	=>	$subscriptionId,
			'customer_id'		=>	$payment->customer_id,
			'parent_id'			=>	$paymentId
		]);

		$userId = $payment->user_id;
		if( $payment->plan == 'monthly' )
		{
			$accountNewExpireDate = date('Y-m-d' , strtotime('+1 month'));
		}
		else
		{
			$accountNewExpireDate = date('Y-m-d' , strtotime('+1 year'));
		}

		User::where('id' , $userId)->update([
			'expire_on'		=>	$accountNewExpireDate,
			'user_role_id'	=>	$payment->package_id
		]);
	}


	public static function unSubscribed( $event )
	{
		$eventobj = $event->data->object;

		$subscriptionId = $eventobj->id;

		try
		{
			$subscription = \Stripe\Subscription::retrieve($subscriptionId);
		}
		catch (\Exception $e)
		{
			// Couldn't get subscription data
			http_response_code(400);
			exit;
		}

		if ( empty($subscription->metadata->payment_id) ) {
			// Invalid data
			http_response_code(400);
			exit;
		}

		$paymentId = $subscription->metadata->payment_id;

		Payment::where('id' , $paymentId)->update([
			'is_subscribed'		=>	0
		]);
	}

	public static function cancelSubscription( $subscriptionId )
	{
		self::construct();

		try
		{
			$subscription = \Stripe\Subscription::retrieve( $subscriptionId );
			$subscription->cancel();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

}