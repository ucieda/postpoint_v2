<?php

namespace App\Http\Controllers\Auth;

use App\Lib\FBLib;
use App\User;
use App\Http\Controllers\Controller;
use App\User_role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\View;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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
        $this->middleware('guest');
        if( !siteOption('is_register_active') )
		{
			print 'New user registration has ben disabled by administrator!';
			exit();
		}
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
			'email'		=> 'required|string|email',
            'username'	=> 'required|string|max:50|unique:users',
			'password'	=> 'required|string|min:6|confirmed',
			'i_agree'	=> 'required|string',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
		if( !empty( siteOption('default_timezone') ) )
		{
			\Config::set('app.timezone' , siteOption('default_timezone'));
			date_default_timezone_set( siteOption('default_timezone') );
		}

    	$roleId = siteOption('default_role_id');
		$roleInf = User_role::where('id' , $roleId)->first();
		$expireDay = $roleInf && $roleInf->account_expire_days > 0 ? $roleInf->account_expire_days : 0;

		$expiredOn = date('Y-m-d' , time() + $expireDay * 60 * 60 * 24);

		$status = siteOption('new_users_must_activated_by_admin') ? '0' : '1';
		$emailConfirmToken	=	null;
		$sendedTime			=	null;

		if( siteOption('confirm_with_email') )
		{
			$emailConfirmToken	= md5(base64_encode(uniqid().microtime(true).rand( 100000 , 999900000)) );
			$sendedTime			= date('Y-m-d H:i:s');
		}

        $newUser = User::create([
			'username'							=>	$data['username'],
			'email'								=>	$data['email'],
            'password'							=>	bcrypt($data['password']),
			// defaults
			'user_role_id'						=>	$roleId,
			'expire_on'							=>	$expiredOn,
			'timezone'							=>	siteOption('default_timezone'),
			'language_id'						=>	siteOption('default_lang_id'),
			'records_per_page'					=>	25,
			'load_my_groups'					=>	1,
			'load_my_pages'						=>	1,
			'load_my_ownpages'					=>	1,
			'max_groups_to_import'				=>	100,
			'max_pages_to_import'				=>	100,
			'show_open_groups_only'				=>	1,
			'unique_post'						=>	0,
			'unique_link'						=>	0,
			'post_interval'						=>	60,
			'fb_account_id'						=>	0,
			'is_admin'							=>	0,
			'status'							=>	$status,
			'email_confirmation_token'			=>	$emailConfirmToken,
			'last_confirmation_email_sended_on'	=>	$sendedTime
        ]);

		if( siteOption('confirm_with_email') )
		{
			sendConfirmationEmail( $data['email'] , $newUser->id , $emailConfirmToken );
		}

		return $newUser;
    }

	public function showRegistrationForm()
	{
		if( !empty( session('fb_access_token') ) )
		{
			$accessToken = session('fb_access_token');
			$info = FBLib::cmd('/me' , 'GET' , $accessToken , ['fields'	=> 'id,email']);
			$email = $info['email'];
			$checkUser = User::where('email' , $email)->first();
			if( !$checkUser )
			{
				View::share(['email' => $email]);
			}
			else
			{
				Auth::login( $checkUser );
				session()->forget('fb_access_token');
				return redirect('/home');
			}
		}
		else if( !empty( siteOption('fb_app_id') ) && !empty( siteOption('fb_app_secret') ) )
		{
			$linkFb = FBLib::getLoginURL(siteOption('fb_app_id') , siteOption('fb_app_secret'), rtrim(env('APP_URL') , '/')  . '/fb');
			View::share(['linkFb' => $linkFb]);

			session(['redirect_url_fb' => 'register']);
		}

		return view('auth.register');
	}

}
