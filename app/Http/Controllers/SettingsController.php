<?php

namespace App\Http\Controllers;


use App\Fb_account;
use App\Fb_account_access_token;
use App\Fb_account_node;
use App\Fb_account_node_categorie;
use App\Fb_account_node_categorie_list;
use App\Fb_app;
use App\Language;
use App\Lib\Curl;
use App\Lib\FBLib;
use App\Option;
use App\User;
use App\User_role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class SettingsController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware(['auth' , 'web']);
	}

	/* Profile settings */
	public function profile()
	{
		$userInfo = Auth::user();

		return view('settings.profile' , ['info' => $userInfo]);
	}

	public function profileSave(Request $request)
	{
		$validatedData = $request->validate([
			'name'		 => 'required|string|max:255',
			'surname' 	 => 'required|string|max:255',
			'email'		 => 'required|string|email|max:255',
			'fb_user_id' => 'string|max:35',
		]);

		User::where('id' , Auth::user()->id)->update([
			'name'			=> $validatedData['name'],
			'surname'		=> $validatedData['surname'],
			'email'			=> $validatedData['email'],
			'fb_user_id'	=> isset($validatedData['fb_user_id']) ? $validatedData['fb_user_id'] : '',
		]);

		return redirect()->back()->with('success', true);
	}

	/* Change password */
	public function chngPass()
	{
		return viewModal('settings.chngPass' );
	}

	public function chngPassSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'current_password'	 => 'required|string|max:255',
			'new_password1' 	 => 'required|string|max:255',
			'new_password2'		 => 'required|string|max:255'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$currentPass = (string)Input::post('current_password');
		$nPass1 = (string)Input::post('new_password1');
		$nPass2 = (string)Input::post('new_password2');

		if( $nPass1 != $nPass2 )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Şifrələr uyğun gəlmir!'
			]);
		}

		$userPassword = Auth::user()->password;

		if( !Hash::check( $currentPass , $userPassword ) )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Şifrəni səhv daxil etdiniz!'
			]);
		}

		$chngPass = Auth::user();
		$chngPass->password = bcrypt( $nPass1 );
		$chngPass->save();

		return response()->json([
			'status'	=>  'ok'
		]);

	}

	/* General settings */
	public function general()
	{
		$languages  = Language::get()->pluck('name', 'id');
		$userInf	= Auth::user();

		return view('settings.general' , ['languages' => $languages , 'userInf' => $userInf]);
	}

	public function generalSave(Request $request)
	{
		$validatedData = $request->validate([
			'records_per_page'  =>  'required|numeric',
			'timezone' 			=>  'required|string',
			'language'			=>  'required|numeric'
		]);

		if( !array_key_exists( $validatedData['timezone'] , static::timezones() ) )
		{
			return redirect()->back()->withErrors(['error_msg' => 'Timezone error!'])->withInput();
		}

		User::where('id' , Auth::user()->id)->update([
			'language_id'			=> $validatedData['language'],
			'records_per_page'		=> $validatedData['records_per_page'],
			'timezone'				=> $validatedData['timezone']
		]);

		return redirect()->back()->with('success', true);
	}

	/* Publish settings */
	public function publish()
	{
		$fbApps = Fb_app::where(function($query)
		{
			$query->where('user_id' , Auth::id())->orWhere('is_public' , '1');
		})
		->where(DB::raw('0') , '<' , function($query)
		{
			$query->from('fb_account_access_tokens')
				->where('fb_account_id' , Auth::user()->fb_account_id)
				->where('app_id' , DB::raw('fb_apps.id'))
				->select(DB::raw('COUNT(0)'));
		})
		->get()->pluck('name' , 'id');

		$accountId = Auth::user()->fb_account_id;
		if($accountId > 0)
		{
			$defaultApp = Fb_account::where('id' , $accountId)->first()->default_app_id;
		}
		else
		{
			$defaultApp = 0;
		}

		return view('settings.publish' , ['apps' => $fbApps , 'defaultApp' => $defaultApp]);
	}

	public function publishSave(Request $request)
	{
		$validatedData = $request->validate([
			'show_open_groups_only'	=> '',
			'unique_post' 	   		=> '',
			'unique_link'	   		=> '',
			'link_customization'	   		=> '',
			'post_interval'   		=> 'required|numeric',
			'fb_app_id'   			=> 'required|numeric'
		]);

		if($validatedData['post_interval']<60 || $validatedData['post_interval']>1500)
		{
			return redirect()->back()->withErrors(['error_msg' => 'Post interval 60-1500 saniyə aralığında seçə bilərsiniz!'])->withInput();
		}

		User::where('id' , Auth::user()->id)->update([
			'show_open_groups_only'	=> isset($validatedData['show_open_groups_only']) ? 1 : 0,
			'unique_post' 			=> isset($validatedData['unique_post']) ? 1 : 0,
			'unique_link'			=> isset($validatedData['unique_link']) ? 1 : 0,
			'link_customization'	=> isset($validatedData['link_customization']) ? 1 : 0,
			'post_interval'  		=> (int)$validatedData['post_interval']
		]);

		$accountId = Auth::user()->fb_account_id;
		if($accountId > 0)
		{
			Fb_account::where('id' , $accountId)->update([
				'default_app_id'	=>	(int)$validatedData['fb_app_id']
			]);
		}

		return redirect()->back()->with('success', true);
	}

	/* FB accounts settings */
	public function fb_accounts()
	{
		$accounts = Fb_account::where('user_id' , Auth::id())->get();

		return view('settings.fb_accounts' , ['accounts' => $accounts]);
	}

	public function fb_accountsSave(Request $request)
	{
		$validatedData = $request->validate([
			'load_my_groups'		=> '',
			'load_my_pages' 		=> '',
			'load_my_ownpages'		=> '',
			'max_groups_to_import'  => 'required|numeric',
			'max_pages_to_import'   => 'required|numeric'
		]);

		User::where('id' , Auth::user()->id)->update([
			'load_my_groups'		=> isset($validatedData['load_my_groups']) ? 1 : 0,
			'load_my_pages' 		=> isset($validatedData['load_my_pages']) ? 1 : 0,
			'load_my_ownpages'		=> isset($validatedData['load_my_ownpages']) ? 1 : 0,
			'max_groups_to_import'  => $validatedData['max_groups_to_import'],
			'max_pages_to_import'   => $validatedData['max_pages_to_import']
		]);

		return redirect()->back()->with('success', true);
	}

	public function addAccount()
	{
		return viewModal('settings.addAccount' , 'Add new account');
	}

	public function addAccountSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'userName'	=> 'required|string|max:255|min:1',
			'password'	=> 'required|string|max:255|min:1',
			'appication'=> 'required|numeric|min:1|max:2'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$userName 	= (string)Input::post('userName');
		$password 	= (string)Input::post('password');
		$appId 		= (int)Input::post('appication');

		// check application
		$checkApp = Fb_app::where('id' , $appId)->where('is_standart' , '1')->first();
		if( !$checkApp )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Application not found!'
			]);
		}

		$result = FBLib::getAccessTookenWithAuth( $userName , $password , $checkApp['fb_app_key'] , $checkApp['fb_app_secret'] );

		return response()->json([
			'status' => 'ok',
			'url'	=>	$result
		]);

		/*if( $result === false || !is_array( $result ) )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Error!'
			]);
		}

		if( isset($result['error_msg']) && is_string($result['error_msg']) )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  $result['error_msg']
			]);
		}

		if( isset($result['access_token']) && is_string($result['access_token']))
		{
			$accessToken = (string)$result['access_token'];

			FBLib::authorizeFbUser( $appId , $accessToken , $checkApp );

			return response()->json([
				'status'	=>  'ok'
			]);
		}

		return response()->json([
			'status'	=>  'error',
			'error_msg' =>  'Error! (2)'
		]);*/
	}

	public function addAccountATSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'access_token'	=> 'required|string|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$accessToken = FBLib::extractAccessToken( (string)Input::post('access_token') );

		if( $accessToken['status'] == false )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  !empty($accessToken['message']) ? $accessToken['message'] : 'Error!'
			]);
		}

		$accessToken	= $accessToken['access_token'];
		$accessTookenDetails	= FBLib::getAccessTokenDetails( $accessToken );

		if( !is_array( $accessTookenDetails ) || !isset( $accessTookenDetails['id'] ) )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Access Token error!'
			]);
		}

		$fbAppId = $accessTookenDetails['id'];

		// check application
		$checkApp = Fb_app::where('fb_app_id' , $fbAppId)->where('is_standart' , '1')->first();
		if( !$checkApp )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Application not found! App ID ('.$fbAppId.')'
			]);
		}
		$appId = $checkApp->id;

		FBLib::authorizeFbUser( $appId , $accessToken , $checkApp );

		return response()->json([
			'status'	=>  'ok'
		]);
	}

	public function fbAccountDelete(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id'	=> 'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id	= (int)Input::post('id');

		$checkAccount = Fb_account::where('id' , $id)->where('user_id' , Auth::id())->first();
		if( !$checkAccount )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Fb account not found!'
			]);
		}

		Fb_account_access_token::where('fb_account_id' , $id)->delete();
		$categories = Fb_account_node_categorie::where('fb_account_id' , $id)->get()->pluck('id');

		Fb_account_node_categorie_list::whereIn('category_id' , $categories)->delete();
		Fb_account_node_categorie::whereIn('id' , $categories)->delete();

		Fb_account_node::where('fb_account_id' , $id)->delete();

		Fb_account::where('id' , $id)->delete();
		
		return response()->json([
			'status'	=>	'ok'
		]);
	}

	public function fbAccountUpdate(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id'	=> 'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id	= (int)Input::post('id');

		$getAccessToken = Fb_account_access_token::where('fb_account_id' , $id)->first();
		if($getAccessToken)
		{
			FBLib::authorizeFbUser($getAccessToken->app_id , $getAccessToken->access_token);
		}
		else
		{
			return $this->fbAccountDelete( $request );
		}

		return response()->json([
			'status'	=>	'ok'
		]);
	}

	/* FB applications settings */
	public function fb_apps()
	{
		$accountId = Auth::user()->fb_account_id;

		$apps = Fb_app::where('user_id' , Auth::id())->orWhere('is_standart' , '1')->get();

		foreach( $apps AS &$appInf )
		{
			$checkAuth = Fb_account_access_token::where('app_id' , $appInf->id)->where('fb_account_id' , Auth::user()->fb_account_id)->first();
			$appInf['auth'] = $checkAuth;
		}

		if( $accountId > 0 )
		{
			$fbAccountName = Fb_account::where('id' , $accountId)->first();
			$fbAccountName = $fbAccountName ? $fbAccountName->name : '';
		}
		else
		{
			$fbAccountName = '';
		}

		return view('settings.fb_apps' , ['apps' => $apps , 'fbAccountName' => $fbAccountName]);
	}

	public function fb_appsSave(Request $request)
	{
		$validatedData = $request->validate([
			'fb_app_id'					=> 'required|numeric',
			'fb_app_secret' 			=> 'required|string',
			'fb_app_authenticate_link'	=> 'nullable|string',
			'fb_public_app'			 => 'nullable|string'
		]);

		$validateApp = FBLib::validateAppSecret( $validatedData['fb_app_id'] , $validatedData['fb_app_secret'] );

		if( false === $validateApp )
		{
			return redirect()->back()->withErrors(['error_msg' => 'App id or secred is invalid!'])->withInput();
		}

		$checkAppExists = Fb_app::where('fb_app_id' , $validatedData['fb_app_id'])->where('user_id' , Auth::id())->count();

		if( $checkAppExists == 0 )
		{
			Fb_app::insert([
				'user_id'					=>	Auth::id(),
				'fb_app_id'					=>	$validatedData['fb_app_id'],
				'fb_app_secret'				=>	$validatedData['fb_app_secret'],
				'fb_app_authenticate_link'	=>	$validatedData['fb_app_authenticate_link'] ?? '',
				'is_public'					=>	Auth::user()->is_admin ? ($validatedData['public'] ?? 0) : 0,
				'name'						=>	$validateApp['name'] ?? '-'
			]);
		}

		return redirect()->back()->with('success', true);
	}

	public function authenticateApp( $id )
	{
		$appInf = Fb_app::where('id' , $id)->first();
		if( !$appInf )
		{
			print 'Error! App not found!';
			exit();
		}

		if( $id <= 2 )
		{
			return viewModal('settings.authenticate_app' , 'Authenticate' , ['appId' => $id]);
		}
		else
		{
			return viewModal('settings.authenticate_app2' , 'Authenticate' , ['appId' => $id , 'authLink' => $appInf->fb_app_authenticate_link]);
		}
	}

	public function deauthenticateApp(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 	=> 'required|numeric|min:0',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id = (int)Input::post('id');

		$appInfo = Fb_account_access_token::where('id' , $id)->where('fb_account_id' , Auth::user()->fb_account_id)->first();
		if( !$appInfo )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'App access token not found!'
			]);
		}

		$result = FBLib::cmd('/me/permissions' , 'DELETE' , $appInfo->access_token);

		Fb_account_access_token::where('id' , $id)->where('fb_account_id' , Auth::user()->fb_account_id)->delete();

		return response()->json([
			'status'	=>  'ok'
		]);
	}

	public function deleteApp(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 	=> 'required|numeric|min:0',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id = (int)Input::post('id');

		Fb_app::where('id' , $id)->delete();

		Fb_account_access_token::where('app_id' , $id)->delete();

		Fb_account::where('default_app_id' , $id)->update([
			'default_app_id' => '0'
		]);

		return response()->json([
			'status'	=>  'ok'
		]);
	}

	/* Roles settings */
	public function roles()
	{
		$roles = User_role::get();

		return view('settings.roles' , ['roles' => $roles]);
	}

	public function rolesAddEdit()
	{
		$id = Input::post('id');
		if( !is_numeric($id) || $id < 0  )
		{
			return ['error'];
		}

		if( $id > 0 )
		{
			$title = 'Edit role';
			$info = User_role::where('id' , $id)->first();
		}
		else
		{
			$title = 'Add new role';
			$info = [];
		}

		return viewModal('settings.rolesAddEdit' , $title , ['id' => $id , 'info' => $info] );
	}

	public function rolesInfo()
	{
		$id = Input::post('id');

		if( !is_numeric($id) || $id <= 0  )
		{
			return ['error'];
		}

		$info = User_role::where('id' , $id)->first();

		return viewModal('settings.rolesInfo' , 'Role Info' , ['id' => $id , 'info' => $info] );
	}

	public function rolesAddEditSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 				=> 'required|numeric|min:0',
			'role_name'	 		=> 'required|string|max:75',
			'max_post_per_day' 	=> 'required|numeric|min:0',
			'max_fb_accounts' 	=> 'required|numeric|min:0',
			'expire_days' 		=> 'required|numeric|min:0',
			'monthly_price' 	=> 'required|numeric|min:0',
			'annual_price' 		=> 'required|numeric|min:0',
			'upload_videos' 	=> 'required|numeric|min:0|max:1',
			'upload_images' 	=> 'required|numeric|min:0|max:1',
			'max_upload_mb' 	=> 'required|numeric|min:0'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		if (!Auth::user()->is_admin)
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Permission error!'
			]);
		}

		$id = (int)Input::post('id');

		$dataArray = [
			'name'	 				=> (string)Input::post('role_name'),
			'max_posts_per_day' 	=> (int)Input::post('max_post_per_day'),
			'max_fb_accounts' 		=> (int)Input::post('max_fb_accounts'),
			'account_expire_days' 	=> (int)Input::post('expire_days'),
			'upload_videos' 		=> (int)Input::post('upload_videos'),
			'upload_images' 		=> (int)Input::post('upload_images'),
			'monthly_price' 		=> (float)Input::post('monthly_price'),
			'annual_price'			=> (float)Input::post('annual_price'),
			'max_upload_mb' 		=> (int)Input::post('max_upload_mb')
		];

		if( $id > 0 )
		{
			User_role::where('id' , $id)->update($dataArray);
		}
		else
		{
			User_role::insert($dataArray);
		}

		return response()->json([
			'status'	=>  'ok'
		]);
	}

	public function rolesDelete(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 				=> 'required|numeric|min:0',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id = (int)Input::post('id');

		$roleInf = User_role::where('id' , $id)->first();

		if( !$roleInf )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'Packet not found!'
			]);
		}

		if( $roleInf->is_for_demo == 1 )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'You can not delete this!'
			]);
		}

		$checkIfUsersExist = User::where('user_role_id' , $id)->count();

		if( $checkIfUsersExist > 0 )
		{
			return response()->json([
				'status'	=>  'error',
				'error_msg' =>  'You can not delete this packet. Because this packet is selected in some users!'
			]);
		}

		User_role::where('id' , $id)->delete();

		return response()->json([
			'status'	=>  'ok'
		]);
	}

	/* App settings */
	public function appGeneral()
	{
		return view('settings.app');
	}

	public function appGeneralSave(Request $request)
	{
		$validatedData = $request->validate([
			'site_name'								=>	'required|string|min:1|max:255',
			'is_register_active' 		   			=>	'nullable|string',
			'confirm_with_email'					=>	'nullable|string',
			'new_users_must_activated_by_admin'		=>	'nullable|string',
			'default_role_id'		   				=>	'required|numeric|min:1',
			'default_timezone'						=>	'required|string',
			'default_lang_id'						=>	'required|numeric|min:1',
			'date_format'							=>	'required|string',
			'lite_mode_nodes_table'					=>	'nullable|string'
		]);

		if( !in_array( $validatedData['date_format'] , ['YYYY-MM-DD' , 'MM/DD/YYYY' , 'DD-MM-YYYY'] ) )
		{
			$validatedData['date_format'] = 'YYYY-MM-DD';
		}

		$validatedData['is_register_active']				= isset($validatedData['is_register_active']) ? 1 : 0;
		$validatedData['confirm_with_email']				= isset($validatedData['confirm_with_email']) ? 1 : 0;
		$validatedData['new_users_must_activated_by_admin']	= isset($validatedData['new_users_must_activated_by_admin']) ? 1 : 0;
		$validatedData['lite_mode_nodes_table']				= isset($validatedData['lite_mode_nodes_table']) ? 1 : 0;

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}


	public function appPublish()
	{
		return view('settings.app_publish');
	}

	public function appPublishSave(Request $request)
	{
		$validatedData = $request->validate([
			'minimum_immediate_post_interval'			=>	'required|numeric|min:0',
			'minimum_schedule_post_interval' 		   	=>	'required|numeric|min:0',
			'schedule_random_interval'					=>	'required|numeric|min:0',
			'instant_random_interval'					=>	'required|numeric|min:0',
			'enable_instant_post'		   				=>	'nullable|string',
			'enable_sale_post'							=>	'nullable|string',
			'enable_link_customization'					=>	'nullable|string'
		]);

		$validatedData['enable_instant_post']				= isset($validatedData['enable_instant_post']) ? 1 : 0;
		$validatedData['enable_sale_post']				= isset($validatedData['enable_sale_post']) ? 1 : 0;
		$validatedData['enable_link_customization']	= isset($validatedData['enable_link_customization']) ? 1 : 0;

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public function appTheme()
	{
		return view('settings.app_theme');
	}

	public function appThemeSave(Request $request)
	{
		$validatedData = $request->validate([
			'site_logo_m'				=>	'nullable|string|max:255',
			'site_logo_xs'				=>	'nullable|string|max:255',
			'site_logo_l'				=>	'nullable|string|max:255',
			'site_favicon'				=>	'nullable|string|max:255',
			'site_description'			=>	'nullable|string|max:500',
			'theme_color'				=>	'nullable|string|max:255',
			'theme_links_color'			=>	'nullable|string|max:255',
			'theme_background_image'	=>	'nullable|string|max:255',
			'theme_background_color'	=>	'nullable|string|max:255',
			'custom_css'				=>	'nullable|string|max:1000',
			'footer_text'				=>	'nullable|string|max:1000'
		]);

		$validatedData['site_logo_m'] = empty($validatedData['site_logo_m']) ? '' : $validatedData['site_logo_m'];
		$validatedData['site_logo_xs'] = empty($validatedData['site_logo_xs']) ? '' : $validatedData['site_logo_xs'];
		$validatedData['site_logo_l'] = empty($validatedData['site_logo_l']) ? '' : $validatedData['site_logo_l'];
		$validatedData['site_favicon'] = empty($validatedData['site_favicon']) ? '' : $validatedData['site_favicon'];
		$validatedData['site_description'] = empty($validatedData['site_description']) ? '' : $validatedData['site_description'];
		$validatedData['theme_color'] = empty($validatedData['theme_color']) ? '' : $validatedData['theme_color'];
		$validatedData['theme_links_color'] = empty($validatedData['theme_links_color']) ? '' : $validatedData['theme_links_color'];
		$validatedData['theme_background_image'] = empty($validatedData['theme_background_image']) ? '' : $validatedData['theme_background_image'];
		$validatedData['theme_background_color'] = empty($validatedData['theme_background_color']) ? '' : $validatedData['theme_background_color'];
		$validatedData['custom_css'] = empty($validatedData['custom_css']) ? '' : $validatedData['custom_css'];
		$validatedData['footer_text'] = empty($validatedData['footer_text']) ? '' : $validatedData['footer_text'];

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public function appAds()
	{
		return view('settings.app_ads');
	}

	public function appAdsSave(Request $request)
	{
		$validatedData = $request->validate([
			'ads_banner'				=>	'nullable|string|max:2000',
			'display_ads_on_public'		=>	'nullable|string',
			'show_ads_to.*'				=>	'required|numeric|min:1'
		]);

		$validatedData['ads_banner']			=	$validatedData['ads_banner'] ?? '';
		$validatedData['display_ads_on_public']	=	isset($validatedData['display_ads_on_public']) ? 1 : 0;
		$validatedData['show_ads_to']			=	implode(',' , $validatedData['show_ads_to'] ?? []);

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public function appSocialLogin()
	{
		return view('settings.app_social_login');
	}

	public function appSocialLoginSave(Request $request)
	{
		$validatedData = $request->validate([
			'fb_app_id'		=>	'nullable|string|max:255',
			'fb_app_secret'	=>	'nullable|string|max:255'
		]);

		$validatedData['fb_app_id']		= empty($validatedData['fb_app_id']) ? '' : $validatedData['fb_app_id'];
		$validatedData['fb_app_secret']	= empty($validatedData['fb_app_secret']) ? '' : $validatedData['fb_app_secret'];

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public function appAdvanced()
	{
		return view('settings.app_advanced');
	}

	public function appAdvancedSave(Request $request)
	{
		$validatedData = $request->validate([
			'header_js'					=>	'nullable|string|max:2000',
			'footer_js'					=>	'nullable|string|max:2000',
			'active_maintenance_mode'	=>	'nullable|string'
		]);

		$validatedData['header_js']					= empty($validatedData['header_js']) ? '' : $validatedData['header_js'];
		$validatedData['footer_js']					= empty($validatedData['footer_js']) ? '' : $validatedData['footer_js'];
		$validatedData['active_maintenance_mode']	= empty($validatedData['active_maintenance_mode']) ? 0 : 1;

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public function appMail()
	{
		return view('settings.app_mail');
	}

	public function appMailSave(Request $request)
	{
		$validatedData = $request->validate([
			'mail_protocol'				=>	'required|string|max:15',
			'smtp_host'					=>	'nullable|string|max:255',
			'smtp_user'					=>	'nullable|string|max:255',
			'smtp_pass'					=>	'nullable|string|max:255',
			'smtp_port'					=>	'nullable|string|max:255',
			'smtp_encryption'			=>	'nullable|string|max:255'
		]);

		$validatedData['mail_protocol']		=	$validatedData['mail_protocol'] == 'smtp' ? 'smtp' : 'mail';
		$validatedData['smtp_host']			=	$validatedData['mail_protocol'] == 'smtp' ? $validatedData['smtp_host'] : '';
		$validatedData['smtp_user']			=	$validatedData['mail_protocol'] == 'smtp' ? $validatedData['smtp_user'] : '';
		$validatedData['smtp_pass']			=	$validatedData['mail_protocol'] == 'smtp' ? $validatedData['smtp_pass'] : '';
		$validatedData['smtp_port']			=	$validatedData['mail_protocol'] == 'smtp' ? $validatedData['smtp_port'] : '';
		$validatedData['smtp_encryption']	=	$validatedData['mail_protocol'] == 'smtp' ? $validatedData['smtp_encryption'] : '';

		Option::query()->update($validatedData);

		return redirect()->back()->with('success', true);
	}

	public static function timezones()
	{
		return [
			'Africa/Abidjan' => 'Africa/Abidjan',
			'Africa/Accra' => 'Africa/Accra',
			'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
			'Africa/Algiers' => 'Africa/Algiers',
			'Africa/Asmara' => 'Africa/Asmara',
			'Africa/Bamako' => 'Africa/Bamako',
			'Africa/Bangui' => 'Africa/Bangui',
			'Africa/Banjul' => 'Africa/Banjul',
			'Africa/Bissau' => 'Africa/Bissau',
			'Africa/Blantyre' => 'Africa/Blantyre',
			'Africa/Brazzaville' => 'Africa/Brazzaville',
			'Africa/Bujumbura' => 'Africa/Bujumbura',
			'Africa/Cairo' => 'Africa/Cairo',
			'Africa/Casablanca' => 'Africa/Casablanca',
			'Africa/Ceuta' => 'Africa/Ceuta',
			'Africa/Conakry' => 'Africa/Conakry',
			'Africa/Dakar' => 'Africa/Dakar',
			'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
			'Africa/Djibouti' => 'Africa/Djibouti',
			'Africa/Douala' => 'Africa/Douala',
			'Africa/El_Aaiun' => 'Africa/El_Aaiun',
			'Africa/Freetown' => 'Africa/Freetown',
			'Africa/Gaborone' => 'Africa/Gaborone',
			'Africa/Harare' => 'Africa/Harare',
			'Africa/Johannesburg' => 'Africa/Johannesburg',
			'Africa/Juba' => 'Africa/Juba',
			'Africa/Kampala' => 'Africa/Kampala',
			'Africa/Khartoum' => 'Africa/Khartoum',
			'Africa/Kigali' => 'Africa/Kigali',
			'Africa/Kinshasa' => 'Africa/Kinshasa',
			'Africa/Lagos' => 'Africa/Lagos',
			'Africa/Libreville' => 'Africa/Libreville',
			'Africa/Lome' => 'Africa/Lome',
			'Africa/Luanda' => 'Africa/Luanda',
			'Africa/Lubumbashi' => 'Africa/Lubumbashi',
			'Africa/Lusaka' => 'Africa/Lusaka',
			'Africa/Malabo' => 'Africa/Malabo',
			'Africa/Maputo' => 'Africa/Maputo',
			'Africa/Maseru' => 'Africa/Maseru',
			'Africa/Mbabane' => 'Africa/Mbabane',
			'Africa/Mogadishu' => 'Africa/Mogadishu',
			'Africa/Monrovia' => 'Africa/Monrovia',
			'Africa/Nairobi' => 'Africa/Nairobi',
			'Africa/Ndjamena' => 'Africa/Ndjamena',
			'Africa/Niamey' => 'Africa/Niamey',
			'Africa/Nouakchott' => 'Africa/Nouakchott',
			'Africa/Ouagadougou' => 'Africa/Ouagadougou',
			'Africa/Porto-Novo' => 'Africa/Porto-Novo',
			'Africa/Sao_Tome' => 'Africa/Sao_Tome',
			'Africa/Tripoli' => 'Africa/Tripoli',
			'Africa/Tunis' => 'Africa/Tunis',
			'Africa/Windhoek' => 'Africa/Windhoek',
			'America/Adak' => 'America/Adak',
			'America/Anchorage' => 'America/Anchorage',
			'America/Anguilla' => 'America/Anguilla',
			'America/Antigua' => 'America/Antigua',
			'America/Araguaina' => 'America/Araguaina',
			'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
			'America/Argentina/Catamarca' => 'America/Argentina/Catamarca',
			'America/Argentina/Cordoba' => 'America/Argentina/Cordoba',
			'America/Argentina/Jujuy' => 'America/Argentina/Jujuy',
			'America/Argentina/La_Rioja' => 'America/Argentina/La_Rioja',
			'America/Argentina/Mendoza' => 'America/Argentina/Mendoza',
			'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
			'America/Argentina/Salta' => 'America/Argentina/Salta',
			'America/Argentina/San_Juan' => 'America/Argentina/San_Juan',
			'America/Argentina/San_Luis' => 'America/Argentina/San_Luis',
			'America/Argentina/Tucuman' => 'America/Argentina/Tucuman',
			'America/Argentina/Ushuaia' => 'America/Argentina/Ushuaia',
			'America/Aruba' => 'America/Aruba',
			'America/Asuncion' => 'America/Asuncion',
			'America/Atikokan' => 'America/Atikokan',
			'America/Bahia' => 'America/Bahia',
			'America/Bahia_Banderas' => 'America/Bahia_Banderas',
			'America/Barbados' => 'America/Barbados',
			'America/Belem' => 'America/Belem',
			'America/Belize' => 'America/Belize',
			'America/Blanc-Sablon' => 'America/Blanc-Sablon',
			'America/Boa_Vista' => 'America/Boa_Vista',
			'America/Bogota' => 'America/Bogota',
			'America/Boise' => 'America/Boise',
			'America/Cambridge_Bay' => 'America/Cambridge_Bay',
			'America/Campo_Grande' => 'America/Campo_Grande',
			'America/Cancun' => 'America/Cancun',
			'America/Caracas' => 'America/Caracas',
			'America/Cayenne' => 'America/Cayenne',
			'America/Cayman' => 'America/Cayman',
			'America/Chicago' => 'America/Chicago',
			'America/Chihuahua' => 'America/Chihuahua',
			'America/Costa_Rica' => 'America/Costa_Rica',
			'America/Creston' => 'America/Creston',
			'America/Cuiaba' => 'America/Cuiaba',
			'America/Curacao' => 'America/Curacao',
			'America/Danmarkshavn' => 'America/Danmarkshavn',
			'America/Dawson' => 'America/Dawson',
			'America/Dawson_Creek' => 'America/Dawson_Creek',
			'America/Denver' => 'America/Denver',
			'America/Detroit' => 'America/Detroit',
			'America/Dominica' => 'America/Dominica',
			'America/Edmonton' => 'America/Edmonton',
			'America/Eirunepe' => 'America/Eirunepe',
			'America/El_Salvador' => 'America/El_Salvador',
			'America/Fort_Nelson' => 'America/Fort_Nelson',
			'America/Fortaleza' => 'America/Fortaleza',
			'America/Glace_Bay' => 'America/Glace_Bay',
			'America/Godthab' => 'America/Godthab',
			'America/Goose_Bay' => 'America/Goose_Bay',
			'America/Grand_Turk' => 'America/Grand_Turk',
			'America/Grenada' => 'America/Grenada',
			'America/Guadeloupe' => 'America/Guadeloupe',
			'America/Guatemala' => 'America/Guatemala',
			'America/Guayaquil' => 'America/Guayaquil',
			'America/Guyana' => 'America/Guyana',
			'America/Halifax' => 'America/Halifax',
			'America/Havana' => 'America/Havana',
			'America/Hermosillo' => 'America/Hermosillo',
			'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
			'America/Indiana/Knox' => 'America/Indiana/Knox',
			'America/Indiana/Marengo' => 'America/Indiana/Marengo',
			'America/Indiana/Petersburg' => 'America/Indiana/Petersburg',
			'America/Indiana/Tell_City' => 'America/Indiana/Tell_City',
			'America/Indiana/Vevay' => 'America/Indiana/Vevay',
			'America/Indiana/Vincennes' => 'America/Indiana/Vincennes',
			'America/Indiana/Winamac' => 'America/Indiana/Winamac',
			'America/Inuvik' => 'America/Inuvik',
			'America/Iqaluit' => 'America/Iqaluit',
			'America/Jamaica' => 'America/Jamaica',
			'America/Juneau' => 'America/Juneau',
			'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
			'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
			'America/Kralendijk' => 'America/Kralendijk',
			'America/La_Paz' => 'America/La_Paz',
			'America/Lima' => 'America/Lima',
			'America/Los_Angeles' => 'America/Los_Angeles',
			'America/Lower_Princes' => 'America/Lower_Princes',
			'America/Maceio' => 'America/Maceio',
			'America/Managua' => 'America/Managua',
			'America/Manaus' => 'America/Manaus',
			'America/Marigot' => 'America/Marigot',
			'America/Martinique' => 'America/Martinique',
			'America/Matamoros' => 'America/Matamoros',
			'America/Mazatlan' => 'America/Mazatlan',
			'America/Menominee' => 'America/Menominee',
			'America/Merida' => 'America/Merida',
			'America/Metlakatla' => 'America/Metlakatla',
			'America/Mexico_City' => 'America/Mexico_City',
			'America/Miquelon' => 'America/Miquelon',
			'America/Moncton' => 'America/Moncton',
			'America/Monterrey' => 'America/Monterrey',
			'America/Montevideo' => 'America/Montevideo',
			'America/Montserrat' => 'America/Montserrat',
			'America/Nassau' => 'America/Nassau',
			'America/New_York' => 'America/New_York',
			'America/Nipigon' => 'America/Nipigon',
			'America/Nome' => 'America/Nome',
			'America/Noronha' => 'America/Noronha',
			'America/North_Dakota/Beulah' => 'America/North_Dakota/Beulah',
			'America/North_Dakota/Center' => 'America/North_Dakota/Center',
			'America/North_Dakota/New_Salem' => 'America/North_Dakota/New_Salem',
			'America/Ojinaga' => 'America/Ojinaga',
			'America/Panama' => 'America/Panama',
			'America/Pangnirtung' => 'America/Pangnirtung',
			'America/Paramaribo' => 'America/Paramaribo',
			'America/Phoenix' => 'America/Phoenix',
			'America/Port-au-Prince' => 'America/Port-au-Prince',
			'America/Port_of_Spain' => 'America/Port_of_Spain',
			'America/Porto_Velho' => 'America/Porto_Velho',
			'America/Puerto_Rico' => 'America/Puerto_Rico',
			'America/Punta_Arenas' => 'America/Punta_Arenas',
			'America/Rainy_River' => 'America/Rainy_River',
			'America/Rankin_Inlet' => 'America/Rankin_Inlet',
			'America/Recife' => 'America/Recife',
			'America/Regina' => 'America/Regina',
			'America/Resolute' => 'America/Resolute',
			'America/Rio_Branco' => 'America/Rio_Branco',
			'America/Santarem' => 'America/Santarem',
			'America/Santiago' => 'America/Santiago',
			'America/Santo_Domingo' => 'America/Santo_Domingo',
			'America/Sao_Paulo' => 'America/Sao_Paulo',
			'America/Scoresbysund' => 'America/Scoresbysund',
			'America/Sitka' => 'America/Sitka',
			'America/St_Barthelemy' => 'America/St_Barthelemy',
			'America/St_Johns' => 'America/St_Johns',
			'America/St_Kitts' => 'America/St_Kitts',
			'America/St_Lucia' => 'America/St_Lucia',
			'America/St_Thomas' => 'America/St_Thomas',
			'America/St_Vincent' => 'America/St_Vincent',
			'America/Swift_Current' => 'America/Swift_Current',
			'America/Tegucigalpa' => 'America/Tegucigalpa',
			'America/Thule' => 'America/Thule',
			'America/Thunder_Bay' => 'America/Thunder_Bay',
			'America/Tijuana' => 'America/Tijuana',
			'America/Toronto' => 'America/Toronto',
			'America/Tortola' => 'America/Tortola',
			'America/Vancouver' => 'America/Vancouver',
			'America/Whitehorse' => 'America/Whitehorse',
			'America/Winnipeg' => 'America/Winnipeg',
			'America/Yakutat' => 'America/Yakutat',
			'America/Yellowknife' => 'America/Yellowknife',
			'Antarctica/Casey' => 'Antarctica/Casey',
			'Antarctica/Davis' => 'Antarctica/Davis',
			'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
			'Antarctica/Macquarie' => 'Antarctica/Macquarie',
			'Antarctica/Mawson' => 'Antarctica/Mawson',
			'Antarctica/McMurdo' => 'Antarctica/McMurdo',
			'Antarctica/Palmer' => 'Antarctica/Palmer',
			'Antarctica/Rothera' => 'Antarctica/Rothera',
			'Antarctica/Syowa' => 'Antarctica/Syowa',
			'Antarctica/Troll' => 'Antarctica/Troll',
			'Antarctica/Vostok' => 'Antarctica/Vostok',
			'Arctic/Longyearbyen' => 'Arctic/Longyearbyen',
			'Asia/Aden' => 'Asia/Aden',
			'Asia/Almaty' => 'Asia/Almaty',
			'Asia/Amman' => 'Asia/Amman',
			'Asia/Anadyr' => 'Asia/Anadyr',
			'Asia/Aqtau' => 'Asia/Aqtau',
			'Asia/Aqtobe' => 'Asia/Aqtobe',
			'Asia/Ashgabat' => 'Asia/Ashgabat',
			'Asia/Atyrau' => 'Asia/Atyrau',
			'Asia/Baghdad' => 'Asia/Baghdad',
			'Asia/Bahrain' => 'Asia/Bahrain',
			'Asia/Baku' => 'Asia/Baku',
			'Asia/Bangkok' => 'Asia/Bangkok',
			'Asia/Barnaul' => 'Asia/Barnaul',
			'Asia/Beirut' => 'Asia/Beirut',
			'Asia/Bishkek' => 'Asia/Bishkek',
			'Asia/Brunei' => 'Asia/Brunei',
			'Asia/Chita' => 'Asia/Chita',
			'Asia/Choibalsan' => 'Asia/Choibalsan',
			'Asia/Colombo' => 'Asia/Colombo',
			'Asia/Damascus' => 'Asia/Damascus',
			'Asia/Dhaka' => 'Asia/Dhaka',
			'Asia/Dili' => 'Asia/Dili',
			'Asia/Dubai' => 'Asia/Dubai',
			'Asia/Dushanbe' => 'Asia/Dushanbe',
			'Asia/Famagusta' => 'Asia/Famagusta',
			'Asia/Gaza' => 'Asia/Gaza',
			'Asia/Hebron' => 'Asia/Hebron',
			'Asia/Ho_Chi_Minh' => 'Asia/Ho_Chi_Minh',
			'Asia/Hong_Kong' => 'Asia/Hong_Kong',
			'Asia/Hovd' => 'Asia/Hovd',
			'Asia/Irkutsk' => 'Asia/Irkutsk',
			'Asia/Jakarta' => 'Asia/Jakarta',
			'Asia/Jayapura' => 'Asia/Jayapura',
			'Asia/Jerusalem' => 'Asia/Jerusalem',
			'Asia/Kabul' => 'Asia/Kabul',
			'Asia/Kamchatka' => 'Asia/Kamchatka',
			'Asia/Karachi' => 'Asia/Karachi',
			'Asia/Kathmandu' => 'Asia/Kathmandu',
			'Asia/Khandyga' => 'Asia/Khandyga',
			'Asia/Kolkata' => 'Asia/Kolkata',
			'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk',
			'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
			'Asia/Kuching' => 'Asia/Kuching',
			'Asia/Kuwait' => 'Asia/Kuwait',
			'Asia/Macau' => 'Asia/Macau',
			'Asia/Magadan' => 'Asia/Magadan',
			'Asia/Makassar' => 'Asia/Makassar',
			'Asia/Manila' => 'Asia/Manila',
			'Asia/Muscat' => 'Asia/Muscat',
			'Asia/Nicosia' => 'Asia/Nicosia',
			'Asia/Novokuznetsk' => 'Asia/Novokuznetsk',
			'Asia/Novosibirsk' => 'Asia/Novosibirsk',
			'Asia/Omsk' => 'Asia/Omsk',
			'Asia/Oral' => 'Asia/Oral',
			'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
			'Asia/Pontianak' => 'Asia/Pontianak',
			'Asia/Pyongyang' => 'Asia/Pyongyang',
			'Asia/Qatar' => 'Asia/Qatar',
			'Asia/Qyzylorda' => 'Asia/Qyzylorda',
			'Asia/Riyadh' => 'Asia/Riyadh',
			'Asia/Sakhalin' => 'Asia/Sakhalin',
			'Asia/Samarkand' => 'Asia/Samarkand',
			'Asia/Seoul' => 'Asia/Seoul',
			'Asia/Shanghai' => 'Asia/Shanghai',
			'Asia/Singapore' => 'Asia/Singapore',
			'Asia/Srednekolymsk' => 'Asia/Srednekolymsk',
			'Asia/Taipei' => 'Asia/Taipei',
			'Asia/Tashkent' => 'Asia/Tashkent',
			'Asia/Tbilisi' => 'Asia/Tbilisi',
			'Asia/Tehran' => 'Asia/Tehran',
			'Asia/Thimphu' => 'Asia/Thimphu',
			'Asia/Tokyo' => 'Asia/Tokyo',
			'Asia/Tomsk' => 'Asia/Tomsk',
			'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
			'Asia/Urumqi' => 'Asia/Urumqi',
			'Asia/Ust-Nera' => 'Asia/Ust-Nera',
			'Asia/Vientiane' => 'Asia/Vientiane',
			'Asia/Vladivostok' => 'Asia/Vladivostok',
			'Asia/Yakutsk' => 'Asia/Yakutsk',
			'Asia/Yangon' => 'Asia/Yangon',
			'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
			'Asia/Yerevan' => 'Asia/Yerevan',
			'Atlantic/Azores' => 'Atlantic/Azores',
			'Atlantic/Bermuda' => 'Atlantic/Bermuda',
			'Atlantic/Canary' => 'Atlantic/Canary',
			'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
			'Atlantic/Faroe' => 'Atlantic/Faroe',
			'Atlantic/Madeira' => 'Atlantic/Madeira',
			'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
			'Atlantic/South_Georgia' => 'Atlantic/South_Georgia',
			'Atlantic/St_Helena' => 'Atlantic/St_Helena',
			'Atlantic/Stanley' => 'Atlantic/Stanley',
			'Australia/Adelaide' => 'Australia/Adelaide',
			'Australia/Brisbane' => 'Australia/Brisbane',
			'Australia/Broken_Hill' => 'Australia/Broken_Hill',
			'Australia/Currie' => 'Australia/Currie',
			'Australia/Darwin' => 'Australia/Darwin',
			'Australia/Eucla' => 'Australia/Eucla',
			'Australia/Hobart' => 'Australia/Hobart',
			'Australia/Lindeman' => 'Australia/Lindeman',
			'Australia/Lord_Howe' => 'Australia/Lord_Howe',
			'Australia/Melbourne' => 'Australia/Melbourne',
			'Australia/Perth' => 'Australia/Perth',
			'Australia/Sydney' => 'Australia/Sydney',
			'Europe/Amsterdam' => 'Europe/Amsterdam',
			'Europe/Andorra' => 'Europe/Andorra',
			'Europe/Astrakhan' => 'Europe/Astrakhan',
			'Europe/Athens' => 'Europe/Athens',
			'Europe/Belgrade' => 'Europe/Belgrade',
			'Europe/Berlin' => 'Europe/Berlin',
			'Europe/Bratislava' => 'Europe/Bratislava',
			'Europe/Brussels' => 'Europe/Brussels',
			'Europe/Bucharest' => 'Europe/Bucharest',
			'Europe/Budapest' => 'Europe/Budapest',
			'Europe/Busingen' => 'Europe/Busingen',
			'Europe/Chisinau' => 'Europe/Chisinau',
			'Europe/Copenhagen' => 'Europe/Copenhagen',
			'Europe/Dublin' => 'Europe/Dublin',
			'Europe/Gibraltar' => 'Europe/Gibraltar',
			'Europe/Guernsey' => 'Europe/Guernsey',
			'Europe/Helsinki' => 'Europe/Helsinki',
			'Europe/Isle_of_Man' => 'Europe/Isle_of_Man',
			'Europe/Istanbul' => 'Europe/Istanbul',
			'Europe/Jersey' => 'Europe/Jersey',
			'Europe/Kaliningrad' => 'Europe/Kaliningrad',
			'Europe/Kiev' => 'Europe/Kiev',
			'Europe/Kirov' => 'Europe/Kirov',
			'Europe/Lisbon' => 'Europe/Lisbon',
			'Europe/Ljubljana' => 'Europe/Ljubljana',
			'Europe/London' => 'Europe/London',
			'Europe/Luxembourg' => 'Europe/Luxembourg',
			'Europe/Madrid' => 'Europe/Madrid',
			'Europe/Malta' => 'Europe/Malta',
			'Europe/Mariehamn' => 'Europe/Mariehamn',
			'Europe/Minsk' => 'Europe/Minsk',
			'Europe/Monaco' => 'Europe/Monaco',
			'Europe/Moscow' => 'Europe/Moscow',
			'Europe/Oslo' => 'Europe/Oslo',
			'Europe/Paris' => 'Europe/Paris',
			'Europe/Podgorica' => 'Europe/Podgorica',
			'Europe/Prague' => 'Europe/Prague',
			'Europe/Riga' => 'Europe/Riga',
			'Europe/Rome' => 'Europe/Rome',
			'Europe/Samara' => 'Europe/Samara',
			'Europe/San_Marino' => 'Europe/San_Marino',
			'Europe/Sarajevo' => 'Europe/Sarajevo',
			'Europe/Saratov' => 'Europe/Saratov',
			'Europe/Simferopol' => 'Europe/Simferopol',
			'Europe/Skopje' => 'Europe/Skopje',
			'Europe/Sofia' => 'Europe/Sofia',
			'Europe/Stockholm' => 'Europe/Stockholm',
			'Europe/Tallinn' => 'Europe/Tallinn',
			'Europe/Tirane' => 'Europe/Tirane',
			'Europe/Ulyanovsk' => 'Europe/Ulyanovsk',
			'Europe/Uzhgorod' => 'Europe/Uzhgorod',
			'Europe/Vaduz' => 'Europe/Vaduz',
			'Europe/Vatican' => 'Europe/Vatican',
			'Europe/Vienna' => 'Europe/Vienna',
			'Europe/Vilnius' => 'Europe/Vilnius',
			'Europe/Volgograd' => 'Europe/Volgograd',
			'Europe/Warsaw' => 'Europe/Warsaw',
			'Europe/Zagreb' => 'Europe/Zagreb',
			'Europe/Zaporozhye' => 'Europe/Zaporozhye',
			'Europe/Zurich' => 'Europe/Zurich',
			'Indian/Antananarivo' => 'Indian/Antananarivo',
			'Indian/Chagos' => 'Indian/Chagos',
			'Indian/Christmas' => 'Indian/Christmas',
			'Indian/Cocos' => 'Indian/Cocos',
			'Indian/Comoro' => 'Indian/Comoro',
			'Indian/Kerguelen' => 'Indian/Kerguelen',
			'Indian/Mahe' => 'Indian/Mahe',
			'Indian/Maldives' => 'Indian/Maldives',
			'Indian/Mauritius' => 'Indian/Mauritius',
			'Indian/Mayotte' => 'Indian/Mayotte',
			'Indian/Reunion' => 'Indian/Reunion',
			'Pacific/Apia' => 'Pacific/Apia',
			'Pacific/Auckland' => 'Pacific/Auckland',
			'Pacific/Bougainville' => 'Pacific/Bougainville',
			'Pacific/Chatham' => 'Pacific/Chatham',
			'Pacific/Chuuk' => 'Pacific/Chuuk',
			'Pacific/Easter' => 'Pacific/Easter',
			'Pacific/Efate' => 'Pacific/Efate',
			'Pacific/Enderbury' => 'Pacific/Enderbury',
			'Pacific/Fakaofo' => 'Pacific/Fakaofo',
			'Pacific/Fiji' => 'Pacific/Fiji',
			'Pacific/Funafuti' => 'Pacific/Funafuti',
			'Pacific/Galapagos' => 'Pacific/Galapagos',
			'Pacific/Gambier' => 'Pacific/Gambier',
			'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
			'Pacific/Guam' => 'Pacific/Guam',
			'Pacific/Honolulu' => 'Pacific/Honolulu',
			'Pacific/Kiritimati' => 'Pacific/Kiritimati',
			'Pacific/Kosrae' => 'Pacific/Kosrae',
			'Pacific/Kwajalein' => 'Pacific/Kwajalein',
			'Pacific/Majuro' => 'Pacific/Majuro',
			'Pacific/Marquesas' => 'Pacific/Marquesas',
			'Pacific/Midway' => 'Pacific/Midway',
			'Pacific/Nauru' => 'Pacific/Nauru',
			'Pacific/Niue' => 'Pacific/Niue',
			'Pacific/Norfolk' => 'Pacific/Norfolk',
			'Pacific/Noumea' => 'Pacific/Noumea',
			'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
			'Pacific/Palau' => 'Pacific/Palau',
			'Pacific/Pitcairn' => 'Pacific/Pitcairn',
			'Pacific/Pohnpei' => 'Pacific/Pohnpei',
			'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
			'Pacific/Rarotonga' => 'Pacific/Rarotonga',
			'Pacific/Saipan' => 'Pacific/Saipan',
			'Pacific/Tahiti' => 'Pacific/Tahiti',
			'Pacific/Tarawa' => 'Pacific/Tarawa',
			'Pacific/Tongatapu' => 'Pacific/Tongatapu',
			'Pacific/Wake' => 'Pacific/Wake',
			'Pacific/Wallis' => 'Pacific/Wallis',
			'UTC' => 'UTC'
		];
	}


	/* General settings */
	public function paypal()
	{
		if( !Auth::user()->is_admin ) exit();

		return view('settings.paypal');
	}

	public function paypalSave(Request $request)
	{
		if( !Auth::user()->is_admin ) exit();

		$validatedData = $request->validate([
			'mode'  			=>  'required|in:sandbox,live',
			'client_id'			=>  'required|string',
			'client_secret'		=>  'required|string'
		]);

		Option::query()->update([
			'paypal_mode'			=>	$validatedData['mode'],
			'paypal_client_id'		=>	$validatedData['client_id'],
			'paypal_client_secret'	=>	$validatedData['client_secret']
		]);

		return redirect()->back()->with('success', true);
	}

	/* General settings */
	public function stripe()
	{
		if( !Auth::user()->is_admin ) exit();

		return view('settings.stripe');
	}

	public function stripeSave(Request $request)
	{
		if( !Auth::user()->is_admin ) exit();

		$validatedData = $request->validate([
			'mode'  			=>  'required|in:sandbox,live',
			'publish_key'		=>  'required|string',
			'secret_key'		=>  'required|string',
			'webhook_secret'	=>  'nullable|string'
		]);

		Option::query()->update([
			'stripe_mode'				=>	$validatedData['mode'],
			'stripe_publish_key'		=>	$validatedData['publish_key'],
			'stripe_secret_key'			=>	$validatedData['secret_key'],
			'stripe_webhook_secret'		=>	$validatedData['webhook_secret'] ?? ''
		]);

		return redirect()->back()->with('success', true);
	}
}
