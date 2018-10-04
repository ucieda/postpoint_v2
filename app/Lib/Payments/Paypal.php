<?php

namespace App\Lib\Payments;

use PayPal\Api\Agreement;
use PayPal\Api\AgreementDetails;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Amount;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Plan;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Common\PayPalModel;

class Paypal
{

	private $_paymentId;
	private $_price;
	private $_currency;
	private $_itemId;
	private $_itemName;
	private $_itemDescription;
	private $_apiContext;
	private $_plan;


	public function __construct( )
	{
		$this->_apiContext = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				siteOption('paypal_client_id'),	  // ClientID
				siteOption('paypal_client_secret')		// ClientSecret
			)
		);

		$this->_apiContext->setConfig(["mode" => siteOption('paypal_mode')]);
	}

	public function setId( $paymentId )
	{
		$this->_paymentId = $paymentId;

		return $this;
	}

	public function setAmount( $price , $currency = 'USD' )
	{
		$this->_price = $price;
		$this->_currency = $currency;

		return $this;
	}

	public function setPlan( $plan )
	{
		$this->_plan = ($plan == 'monthly' ? 'MONTH' : 'YEAR');

		return $this;
	}

	public function setItem( $itemId , $itemName , $itemDescription )
	{
		$this->_itemId = $itemId;
		$this->_itemName = $itemName;
		$this->_itemDescription = $itemDescription;

		return $this;
	}

	public function create()
	{
		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		$item1 = new Item();
		$item1->setName($this->_itemName)
			->setCurrency($this->_currency)
			->setQuantity(1)
			->setSku($this->_itemId)
			->setPrice($this->_price);


		$itemList = new ItemList();
		$itemList->setItems(array($item1));


		$details = new Details();
		$details->setShipping(0)
			->setTax(0)
			->setSubtotal($this->_price);


		$amount = new Amount();
		$amount->setCurrency($this->_currency)
			->setTotal($this->_price)
			->setDetails($details);


		$transaction = new Transaction();
		$transaction->setAmount( $amount )
			->setItemList( $itemList )
			->setDescription("Payment")
			->setInvoiceNumber('PP-' . $this->_paymentId);


		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(url('account_upgrade/finish?id='.$this->_paymentId.'&success=true'))
			->setCancelUrl(url('account_upgrade/finish?id='.$this->_paymentId.'&success=false'));

		$payment = new Payment();
		$payment->setIntent("sale")
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));

		try
		{
			$payment->create($this->_apiContext);

			$approvalUrl = $payment->getApprovalLink();

			return [
				'status'	=> true,
				'url'		=> $approvalUrl
			];
		}
		catch (\Exception $ex)
		{
			return [
				'status'	=> false,
				'error'		=> 'Colud\'t create a payment!'
			];
		}
	}

	public function createRecurringPayment()
	{
		$plan = new Plan();

		$plan->setName($this->_itemName)
			->setDescription($this->_itemDescription)
			->setType('INFINITE');

		$paymentDefinition = new PaymentDefinition();
		$paymentDefinition->setName($this->_itemName)
			->setType('REGULAR')
			->setFrequency($this->_plan)
			->setFrequencyInterval("1")
			->setCycles(0)
			->setAmount(new Currency(array('value' => $this->_price, 'currency' => 'USD')));

		$merchantPreferences = new MerchantPreferences();

		$merchantPreferences->setReturnUrl(url('account_upgrade/finish?id='.$this->_paymentId.'&success=true'))
			->setCancelUrl(url('account_upgrade/finish?id='.$this->_paymentId.'&success=false'))
			->setAutoBillAmount("yes")
			->setInitialFailAmountAction("CONTINUE")
			->setMaxFailAttempts("0");

		$plan->setPaymentDefinitions(array($paymentDefinition));
		$plan->setMerchantPreferences($merchantPreferences);

		try
		{
			$output = $plan->create( $this->_apiContext );
		}
		catch (\Exception $ex)
		{
			return [
				'status'	=> false,
				'error'		=> 'Colud\'t create the billing plan!' . json_encode($ex->getData())
			];
		}

		$activatePlan = $this->activatePlan( $output );
		if( !$activatePlan[0] )
		{
			return [
				'status'	=> false,
				'error'		=> 'Colud\'t activate the billing plan! ' . ( $activatePlan[1] ?? '' )
			];
		}

		$planId = $output->getId();

		$agreement = $this->createAgreement( $planId );

		if( !$agreement[0] )
		{
			return [
				'status'	=> false,
				'error'		=> 'Colud\'t create agreement! ' . ( $agreement[1] ?? '' )
			];
		}

		return [
			'status'	=> true,
			'url'		=> $agreement[1]
		];
	}

	public function activatePlan( $plan )
	{
		try
		{
			$patch = new Patch();
			$value = new PayPalModel('{"state": "ACTIVE"}');

			$patch->setOp('replace')
				->setPath('/')
				->setValue($value);

			$patchRequest = new PatchRequest();
			$patchRequest->addPatch($patch);

			$plan->update($patchRequest, $this->_apiContext);
		}
		catch (\Exception $ex)
		{
			return [false , $ex->getMessage()];
		}

		return [true];
	}

	public function createAgreement( $planId )
	{
		$nowDT = new \DateTime("now", new \DateTimeZone('UTC'));
		$nowDT->setTimestamp( time() + 10 );

		$agreement = new Agreement();

		$agreement->setName($this->_itemName . ' - ' . $this->_plan . ' #' . $this->_paymentId)
			->setDescription($this->_itemName . ' - ' . $this->_plan . ' #' . $this->_paymentId)
			->setStartDate( $nowDT->format('Y-m-d\TH:i:s\Z') );

		$plan = new Plan();
		$plan->setId( $planId );
		$agreement->setPlan($plan);

		$payer = new Payer();
		$payer->setPaymentMethod('paypal');
		$agreement->setPayer($payer);

		try
		{
			$agreement = $agreement->create($this->_apiContext);
			$approvalUrl = $agreement->getApprovalLink();
		}
		catch(\Exception $e)
		{
			return [false , $e->getMessage()];
		}

		return [true , $approvalUrl];
	}

	public function check( $payerId , $paymentId )
	{
		$payment = Payment::get( $paymentId, $this->_apiContext );

		$execution = new PaymentExecution();
		$execution->setPayerId( $payerId );

		try
		{
			$result = $payment->execute( $execution, $this->_apiContext );

			if( $result->state == 'approved' && ( $result->transactions[0]->invoice_number == 'PP-' . $this->_paymentId ) )
			{
				return ['status' => true];
			}
			else
			{
				return ['status' => false , 'message' => 'not_approved'];
			}
		}
		catch (\PayPal\Exception\PayPalConnectionException $ex)
		{
			return [ 'status' => false ];
		}
		catch (\Exception $ex)
		{
			return [ 'status' => false ];
		}
	}

	public function checkRecurring( $token )
	{
		$agreement = new \PayPal\Api\Agreement();
		try
		{
			$result = $agreement->execute($token, $this->_apiContext);

			$id = $result->id;
			$desc = $result->description;
			preg_match( '/\#([0-9]+)$/' , $desc , $idFromDesc );

			if( $result->state == 'Active' && $idFromDesc[1] == $this->_paymentId )
			{
				return [
					'status' => true ,
					'id' => $id
				];
			}
			else
			{
				return ['status' => false , 'message' => 'not_approved'];
			}
		}
		catch (\Exception $ex)
		{
			return [
				'status' => false,
				'message' => $ex->getMessage()
			];
		}
	}

	public function cancelSubscription( $subscriptionId )
	{
		$agreementStateDescriptor = new AgreementStateDescriptor();
		$agreementStateDescriptor->setNote("Suspending the agreement");

		try
		{
			$agreement = Agreement::get( $subscriptionId , $this->_apiContext );
			$agreement->suspend( $agreementStateDescriptor , $this->_apiContext );
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public static function ipn()
	{
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = [];

		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
				$myPost[$keyval[0]] = urldecode($keyval[1]);
		}

		if( !( isset($myPost['txn_type']) && $myPost['txn_type'] == 'recurring_payment' ) )
			return false;

		// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
		$req = 'cmd=_notify-validate';
		if (function_exists('get_magic_quotes_gpc'))
		{
			$get_magic_quotes_exists = true;
		}

		foreach ($myPost as $key => $value)
		{
			if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1)
			{
				$value = urlencode(stripslashes($value));
			}
			else
			{
				$value = urlencode($value);
			}

			$req .= "&$key=$value";
		}

		// Step 2: POST IPN data back to PayPal to validate
		if( siteOption('paypal_mode') == 'live' )
		{
			$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr');
		}
		else
		{
			$ch = curl_init('https://ipnpb.sandbox.paypal.com/cgi-bin/webscr');
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		$res = curl_exec($ch);
		curl_close($ch);

		if ( !$res )
		{
			exit;
		}

		if (strcmp($res, "VERIFIED") == 0)
		{
			$subscriptionId		= $myPost['recurring_payment_id'] ?? '';
			$paymentId			= $myPost['transaction_subject'] ?? '';
			$payment_status		= $myPost['payment_status'] ?? '';

			if( $payment_status != 'Completed' )
			{
				return false;
			}

			preg_match('/\#([0-9]+)$/' , $paymentId , $paymentId);

			$paymentId = (int)( $paymentId[1] ?? '');

			$paymentInf = \App\Payment::where('id' , $paymentId)
				->where('subscription_id' , $subscriptionId)
				->first();

			if( !$paymentInf || $paymentInf['status'] != 4 )
			{
				return false;
			}

			\App\Payment::insert([
				'user_id'			=>	$paymentInf->user_id,
				'package_id'		=>	$paymentInf->package_id,
				'amount'			=>	$paymentInf->amount,
				'status'			=>	'1',
				'added_time'		=>	date('Y-m-d H:i:s'),
				'package_name'		=>	$paymentInf->package_name,
				'payment_method'	=>	$paymentInf->payment_method,
				'plan'				=>	$paymentInf->plan,
				'payment_cycle'		=>	$paymentInf->payment_cycle,
				'is_subscribed'		=>	'0',
				'subscription_id'	=>	$subscriptionId,
				'customer_id'		=>	$paymentInf->customer_id,
				'parent_id'			=>	$paymentId
			]);

			$userId = $paymentInf->user_id;
			if( $paymentInf->plan == 'monthly' )
			{
				$accountNewExpireDate = date('Y-m-d' , strtotime('+1 month'));
			}
			else
			{
				$accountNewExpireDate = date('Y-m-d' , strtotime('+1 year'));
			}

			\App\User::where('id' , $userId)->update([
				'expire_on'		=>	$accountNewExpireDate,
				'user_role_id'	=>	$paymentInf->package_id
			]);

			return true;
		}
	}


}