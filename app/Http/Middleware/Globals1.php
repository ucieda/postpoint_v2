<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class Globals1
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	if( !Auth::user()->is_admin )
		{
			self::checkIfAccountExpire();
			self::checkIfAccountDeactivated();
			self::checkAccountActivated();
			self::checkMaintenanceMode();
		}

		// set timezone
		if( !empty(Auth::user()->timezone) )
		{
			\Config::set('app.timezone' , Auth::user()->timezone);
			date_default_timezone_set(Auth::user()->timezone);
		}

		$accounts = \App\Fb_account::where('user_id' , Auth::id())->get();

		$fbAccountId = Auth::user()->fb_account_id;

		$fbAccInf = [];
		if( $fbAccountId > 0 )
		{
			$fbAccInf = \App\Fb_account::where('id' , $fbAccountId)->first();

			if( !$fbAccInf )
			{
				User::where('id' , Auth::id())->update(['fb_account_id' => '0']);
				$fbAccountId = 0;
				$fbAccInf = [];
			}
		}

		View::share('accounts' , $accounts);
		View::share('fbAccountId' , $fbAccountId);
		View::share('fbAccInf' , $fbAccInf);

		$lang = Auth::user()->Language ? Auth::user()->Language->code : 'az';

		App::setLocale( $lang );

        return $next($request);
    }

	private static function checkIfAccountExpire()
	{
		$expireIn = Auth::user()->expire_on;

		$isExpired = !empty($expireIn) && strtotime($expireIn) < time();

		if( $isExpired && !in_array(\Request::route()->getName() , ['account_expired' , 'account_upgrade' , 'account_upgrade_order' , 'account_upgrade_finis' , 'payment_list']) )
		{
			return Redirect::route('account_expired')->send();
			exit();
		}
		else if( !$isExpired && \Request::route()->getName() == 'account_expired' )
		{
			return Redirect::route('home')->send();
			exit();
		}
	}

	private static function checkIfAccountDeactivated()
	{
		if( !Auth::user()->status && \Request::route()->getName() != 'account_deactivated' )
		{
			return Redirect::route('account_deactivated')->send();
			exit();
		}
		else if( Auth::user()->status && \Request::route()->getName() == 'account_deactivated' )
		{
			return Redirect::route('home')->send();
			exit();
		}
	}

	private static function checkAccountActivated()
	{
		if( !empty(Auth::user()->email_confirmation_token) )
		{
			if( Input::get('resend') == '1' && ( time() - strtotime(Auth::user()->last_confirmation_email_sended_on) >= 300 ) )
			{
				$emailConfirmToken	= md5(base64_encode(uniqid().microtime(true).rand( 100000 , 999900000)) );
				$sendedTime			= date('Y-m-d H:i:s');
				sendConfirmationEmail( Auth::user()->email , Auth::id() , $emailConfirmToken );

				User::where('id' , Auth::id())->update([
					'email_confirmation_token'			=>	$emailConfirmToken,
					'last_confirmation_email_sended_on'	=>	$sendedTime
				]);

				return redirect('home?sended=1')->send();
			}

			if( Input::get('sended') == '1' )
			{
				print 'Activation link has ben sent to email address!<br>';
			}

			print 'Account not confirmed!<br>';

			if( time() - strtotime(Auth::user()->last_confirmation_email_sended_on) >= 300 )
			{
				print '<a href="?resend=1">Resend activation email</a>';
			}

			exit();
		}

	}

	private static function checkMaintenanceMode()
	{
		if( siteOption('active_maintenance_mode') && \Request::route()->getName() != 'maintenance_mode' )
		{
			return Redirect::route('maintenance_mode')->send();
			exit();
		}
		else if( !siteOption('active_maintenance_mode') && \Request::route()->getName() == 'maintenance_mode' )
		{
			return Redirect::route('home')->send();
			exit();
		}
	}



}
