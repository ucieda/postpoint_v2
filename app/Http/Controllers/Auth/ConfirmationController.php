<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ConfirmationController extends Controller
{

	public function confirm( $userId , $token )
	{
		$checkIfTrue = User::where('id' , $userId)->where('email_confirmation_token' , $token)->first();

		if( $checkIfTrue )
		{
			User::where('id' , $userId)->update([
				'email_confirmation_token'			=>	null,
				'last_confirmation_email_sended_on'	=>	null
			]);
		}

		return view('auth.confirm' , ['data' => $checkIfTrue]);
	}
}
