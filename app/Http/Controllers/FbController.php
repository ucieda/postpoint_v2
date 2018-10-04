<?php

namespace App\Http\Controllers;

use App\Fb_account;
use App\Fb_account_node;
use App\Fb_app;
use App\Lib\FBLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class FbController extends Controller
{

	public function loginCallback()
	{
		$appId = session('loginAppId');

		if( !is_numeric($appId) )
		{
			return 'Error! App ID not found!';
		}

		$getAppInf = Fb_app::where('id' , $appId)->first();

		if(!$getAppInf)
		{
			return 'Application not found!';
		}

		if( $getAppInf['user_id'] != Auth::id() && $getAppInf['is_public'] == 0 )
		{
			return 'Application not found! (2)';
		}

		$accessToken = FBLib::getAccessToken( $getAppInf['fb_app_id'] , $getAppInf['fb_app_secret'] );

		if (!is_null($accessToken))
		{
			FBLib::authorizeFbUser( $appId , $accessToken , $getAppInf );

			return redirect('/fb/login_completed');
		}
		else
		{
			return redirect('/fb/login_completed?error=1');
		}
	}

    public function login( $appId )
	{
		$getAppInf = Fb_app::where('id' , $appId)->first();

		if(!$getAppInf)
		{
			return 'Application not found!';
		}

		if( $getAppInf['user_id'] != Auth::id() && $getAppInf['is_public'] == 0 )
		{
			return 'Application not found! (2)';
		}

		session(['loginAppId' => $appId]);

		if( !empty($getAppInf['fb_app_authenticate_link']) )
		{
			$loginURL = $getAppInf['fb_app_authenticate_link'];
		}
		else
		{
			$loginURL = FBLib::getLoginURL( $getAppInf['fb_app_id'] , $getAppInf['fb_app_secret'] );
		}

		return redirect( $loginURL );
	}

	public function login_completed()
	{
		print 'Tebrikler! Login olduz... <script>window.opener.location.reload(); setTimeout(function(){window.close();} , 500); </script>';
	}


}
