<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Lib\FBLib;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(Request $request)
	{
		if( !empty( siteOption('fb_app_id') ) && !empty( siteOption('fb_app_secret') ) )
		{
			$linkFb = FBLib::getLoginURL(siteOption('fb_app_id') , siteOption('fb_app_secret'), rtrim(env('APP_URL') , '/')  . '/fb');
			View::share(['linkFb' => $linkFb]);

			session(['redirect_url_fb' => 'login']);
		}

		if( !empty( session('fb_access_token') ) )
		{
			$accessToken = session('fb_access_token');
			$info = FBLib::cmd('/me' , 'GET' , $accessToken , ['fields'	=> 'id,email']);
			$email = $info['email'];
			if( !empty($email) )
			{
				$checkUser = User::where('email' , $email)->first();
				if( !$checkUser )
				{
					return redirect()->route('register');
				}
				else
				{
					Auth::login( $checkUser );
					session()->forget('fb_access_token');
					return redirect('/home');
				}
			}
			else
			{
				session()->forget('fb_access_token');
			}
		}

		return view('auth.login');
	}

	public function username()
	{
		return 'username';
	}

}
