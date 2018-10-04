<?php

namespace App\Http\Controllers;

use App\Fb_account;
use App\Fb_account_node;
use App\Fb_account_node_categorie;
use App\Fb_account_node_categorie_list;
use App\Fb_post;
use App\Lib\FBLib;
use App\Post_statistic;
use App\User;
use App\User_role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

class AccountsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth' , 'AdminPriv']);
    }

	public function accounts()
	{
		$users = User::where('is_admin' , 0)->with('User_role')->paginate(recordsPerPage());

		return view('accounts.accounts' , ['users' => $users]);
	}

	public function accountDetails($id)
	{
		$postsCount = Post_statistic::
			where('user_id' , $id)
			->select(DB::raw('SUM(success_count_all) AS success_count,SUM(fails_count_all) AS fails_count'))
			->first();

		return view('accounts.account_details' , [
			'id'				=>	$id,
			'info'				=>	User::where('id' , $id)->first(),
			'accounts_count'	=>	Fb_account::where('user_id' , $id)->count(),
			'saved_posts'		=>	Fb_post::where('user_id' , $id)->count(),
			'schedules'			=>	Fb_post::where('is_scheduled' , '1')->where('user_id' , $id)->count(),
			'posts_success'		=>	$postsCount->success_count ?? 0,
			'posts_fails'		=>	$postsCount->fails_count ?? 0,
			'accounts_count'	=>	Fb_account::where('user_id' , $id)->count(),
			'accounts_count'	=>	Fb_account::where('user_id' , $id)->count(),
		]);
	}

	public function addEditUser( $id )
	{
		if( $id > 0 )
		{
			$title = __('accounts.Edit user');
			$info = User::where('id' , $id)->first();
		}
		else
		{
			$title = __('accounts.Add new user');
			$info = [];
		}

		$userRoles = User_role::get();

		return viewModal('accounts.usersAddEdit' , $title , [
			'id'			=>	$id ,
			'info'			=>	$info,
			'userRoles'		=>	$userRoles
		]);
	}

	public function addEditUserSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 				=> 'required|numeric|min:0',
			'username'	 		=> 'required|string|max:255',
			'email' 			=> 'required|string|email|max:255',
			'password1' 		=> 'required|string|max:255',
			'password2' 		=> 'required|string|max:255',
			'role' 				=> 'required|numeric|min:1',
			'expire_on' 		=> 'required|string|date'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		if( (string)Input::post('password1') !== (string)Input::post('password2') )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('accounts.passwords_error')
			]);
		}

		$checkUserName = User::where('username' , (string)Input::post('username'))->first();
		if( $checkUserName )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('accounts.username_exist')
			]);
		}

		$id = (int)Input::post('id');

		$dataArray = [
			'email'	 				=>	(string)Input::post('email'),
			'username' 				=>	(string)Input::post('username'),
			'password' 				=>	Hash::make( (string)Input::post('password1') ),
			'user_role_id'			=>	(int)Input::post('role'),
			'expire_on'				=>	date('Y-m-d' , strtotime(Input::post('expire_on')))
		];

		if( $id > 0 )
		{
			$dataArray['updated_at'] = date('Y-m-d H:i:s');

			User::where('id' , $id)->update($dataArray);
		}
		else
		{
			$dataArray = array_merge( $dataArray , [
				'timezone'				=>	siteOption('default_timezone'),
				'language_id'			=>	siteOption('default_lang_id'),
				'records_per_page'		=>	25,
				'load_my_groups'		=>	1,
				'load_my_pages'			=>	1,
				'load_my_ownpages'		=>	1,
				'max_groups_to_import'	=>	100,
				'max_pages_to_import'	=>	100,
				'show_open_groups_only'	=>	1,
				'unique_post'			=>	0,
				'unique_link'			=>	0,
				'post_interval'			=>	60,
				'fb_account_id'			=>	0,
				'is_admin'				=>	0,
				'status'				=>	1,
				'created_at'			=>	date('Y-m-d H:i:s')
			] );

			User::insert($dataArray);
		}

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function userStatusChange(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id' 				=> 'required|numeric|min:0',
			'status'	 		=> 'required|string'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id		= (int)Input::post('id');
		$status	= (string)Input::post('status') == 'on' ? 1 : 0;

		User::where('id' , $id)->update(['status' => $status]);

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function deleteUsers(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'users.*'	 => 'required|numeric|min:0'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$users	= (array)Input::post('users');

		User::whereIn('id' , $users)->delete();

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function exportEmails()
	{
		return viewModal('accounts.export');
	}

	public function exportEmails2(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'roles.*'	 => 'required|numeric|min:0'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$rolesArr = (array)Input::post('roles');

		$users = User::whereIn('user_role_id' , $rolesArr);

		if( Input::post('admin_c') == 'on' )
		{
			$users->orWhere('is_admin' , '1');
		}
		if( Input::post('expired_accounts') == 'on' )
		{
			$users->orWhere('expire_on' , '<' , date('Y-m-d'));
		}

		$users = $users->pluck('email')->toArray();

		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=Emails.csv");
		header("Content-Transfer-Encoding: binary");

		$out = fopen('php://output', 'w');
		fputcsv($out, ['Email']);
		foreach($users AS $email)
		{
			fputcsv($out, [$email]);

		}
		fclose($out);

	}

	public function loginWithId(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id'	 => 'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id = (array)Input::post('id');

		if( Auth::loginUsingId( $id ) )
		{
			return response()->json([
				'status'	=>	'ok'
			]);
		}
		else
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('accounts.User not found!')
			]);
		}
	}
}
