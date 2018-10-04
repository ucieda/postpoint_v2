<?php

namespace App\Http\Controllers;

use App\Fb_account;
use App\Fb_account_node;
use App\Fb_app;
use App\Lib\Curl;
use App\Lib\FBLib;
use App\Option;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;


class InstallationController extends Controller
{

	private function checkStep($step)
	{
		if( env('APP_VER' , '') !== '' )
		{
			redirect('update' )->send();
			exit();
		}

		if( $step > 0 && $this->checkRequierments() == false )
		{
			redirect('install' )->send();
			exit();
		}

		$sessStep = session('step' , '1');

		if( $sessStep != $step )
		{
			redirect('install/step' . $sessStep)->send();
			exit();
		}
	}

	public function start()
	{
		if( env('APP_VER' , '') !== '' )
		{
			redirect('update' )->send();
			exit();
		}

		return view('install.start' , ['requirments' => $this->checkRequierments(true) , 'start' => $this->checkRequierments()]);
	}

	private function checkRequierments($returnArray = false)
	{
		$requirments = [
			'allow_url_fopen'		=>	ini_get('allow_url_fopen')								? 'yes' : 'no',
			'php_ver'				=>	version_compare(PHP_VERSION, '7.1.3') >= 0		? 'yes' : 'no',
			'open_ssl'				=>	extension_loaded('openssl')								? 'yes' : 'no',
			'pdo'					=>	class_exists('PDO') 									? 'yes' : 'no',
			'mbstring'				=>	extension_loaded('mbstring')						 		? 'yes' : 'no',
			'json'					=>	function_exists('json_encode') 					? 'yes' : 'no',
			'curl'					=>	function_exists('curl_init') 						? 'yes' : 'no'
		];

		if( $returnArray )
		{
			return $requirments;
		}

		foreach( $requirments AS $requirment )
		{
			if( $requirment == 'no' )
			{
				return false;
			}
		}

		return true;
	}

	public function step1()
	{
		$this->checkStep(1);

		return view('install.step1');
	}

	public function step1Save(Request $request)
	{
		$validatedData = $request->validate([
			'sql_hostname'	=>	'required|string|max:255',
			'sql_username'	=>	'required|string|max:255',
			'sql_database'	=>	'required|string|max:255',
			'sql_password'	=>	'nullable|string|max:255',
		]);

		config()->set('database.connections.test', [
			'driver' => 'mysql',
			'host' => $validatedData['sql_hostname'],
			'port' => env('DB_PORT', '3306'),
			'database' => $validatedData['sql_database'],
			'username' => $validatedData['sql_username'],
			'password' => $validatedData['sql_password'],
			'unix_socket' => env('DB_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => true,
			'engine' => null,
		]);

		try
		{
			DB::connection('test')->select('SELECT 1 test');
		}
		catch (\Exception $e)
		{
			return redirect()->back()->withErrors(['error_msg' => 'Connection error!'])->withInput();
		}

		session([
			'step'			=>	2,
			'sql_hostname'	=>	$validatedData['sql_hostname'],
			'sql_username'	=>	$validatedData['sql_username'],
			'sql_database'	=>	$validatedData['sql_database'],
			'sql_password'	=>	$validatedData['sql_password'] ?? ''
		]);

		return redirect('install/step2');
	}

	public function step2()
	{
		$this->checkStep(2);

		return view('install.step2');
	}

	public function step2Save(Request $request)
	{
		$validatedData = $request->validate([
			'site_title'		=>	'required|string|max:255',
			'admin_username'	=>	'required|string|max:255',
			'admin_email'		=>	'required|string|email|max:255',
			'admin_password'	=>	'required|string|max:255|min:6|confirmed'
		]);

		session([
			'step'				=>	3,
			'site_title'		=>	$validatedData['site_title'],
			'admin_username'	=>	$validatedData['admin_username'],
			'admin_email'		=>	$validatedData['admin_email'],
			'admin_password'	=>	$validatedData['admin_password']
		]);

		return redirect('install/step3');
	}

	public function step3()
	{
		$this->checkStep(3);

		return view('install.step3');
	}

	public function step3Save(Request $request)
	{
		$validatedData = $request->validate([
			'product_key'	=>	'required|string|min:5|max:255'
		]);

		$productKey = $validatedData['product_key'];

		$checkPurchaseCodeURL = "http://api.codemasters.io/api.php?purchase_code=" . $productKey . "&version=" . config('app.version');
		$result = Curl::getURL($checkPurchaseCodeURL);

		$result = json_decode($result , true);
		$result = is_array( $result ) ? $result : ['status' => 'error' , 'error_msg' => 'Error!!!'];

		if( !($result['status'] == 'ok' && isset($result['sql']) && isset($result['routes'])) )
		{
			return redirect()->back()->withErrors(['error_msg' => isset($result['error_msg']) ? $result['error_msg'] : 'Error!'])->withInput();
		}

		$requiredSessions = ['site_title','admin_username','admin_email','admin_password','sql_hostname','sql_username','sql_database','sql_password'];
		foreach($requiredSessions AS $rSess)
		{
			if( session($rSess , null) === null )
			{
				session()->flush();
				return redirect('install/step1');
			}
		}

		config()->set('database.connections.test', [
			'driver' => 'mysql',
			'host' => session('sql_hostname'),
			'port' => env('DB_PORT', '3306'),
			'database' => session('sql_database'),
			'username' => session('sql_username'),
			'password' => session('sql_password'),
			'unix_socket' => env('DB_SOCKET', ''),
			'charset' => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix' => '',
			'strict' => true,
			'engine' => null,
		]);

		try
		{
			DB::connection('test')->select('SELECT 1 test');
		}
		catch (\Exception $e)
		{
			return redirect('install/step1');
		}

		$pdo = $pdo = DB::connection('test')->getPdo();

		$siteUrl = URL::to('/');

		$envData = "APP_NAME=\"Fb Poster\"\n" .
		"APP_ENV=local\n" .
		"APP_KEY=base64:ZJEEMphpVNqCP7tXfnvU9vOe/EWCghoG482bOgWesck=\n" .
		"APP_DEBUG=false\n" .
		"APP_LOG_LEVEL=debug\n" .
		"APP_VER=".config('app.version')."\n" .
		"APP_URL=\"" . $siteUrl . "\"\n\n" .
		"DB_CONNECTION=\"mysql\"\n" .
		"DB_HOST=\"" . session('sql_hostname') . "\"\n" .
		"DB_PORT=3306\n" .
		"DB_DATABASE=\"" . session('sql_database') . "\"\n" .
		"DB_USERNAME=\"" . session('sql_username') . "\"\n" .
		"DB_PASSWORD=\"" . session('sql_password') . "\"";

		$dbb = base64_decode( $result['sql'] );
		$dbbArray = explode(';' , $dbb);

		foreach( $dbbArray AS $queryOne )
		{
			$queryOne = trim($queryOne);

			if( empty($queryOne) )
				continue;

			$qry = $pdo->prepare( $queryOne );
			$qry->execute();
		}

		DB::connection('test')->table('users')->insert([
			'email'									=>	session('admin_email'),
			'username'								=>	session('admin_username'),
			'password'								=>	Hash::make(session('admin_password')),
			'timezone'								=>	'Asia/Baku',
			'language_id'							=>	'1',
			'records_per_page'						=>	'25',
			'load_my_groups'						=>	'1',
			'load_my_pages'							=>	'1',
			'load_my_ownpages'						=>	'1',
			'max_groups_to_import'					=>	'200',
			'max_pages_to_import'					=>	'200',
			'show_open_groups_only'					=>	'0',
			'unique_post'							=>	'0',
			'unique_link'							=>	'0',
			'post_interval'							=>	'60',
			'fb_account_id'							=>	'0',
			'is_admin'								=>	'1',
			'user_role_id'							=>	'1',
			'status'								=>	'1',
			'link_customization'					=>	'1',
			'created_at'							=>	date('Y-m-d H:i:s')
		]);

		DB::connection('test')->table('options')->update([
			'site_name'	=>	session('site_title')
		]);

		file_put_contents( base_path('.env') , $envData );
		file_put_contents(base_path('routes/web.php') , base64_decode( $result['routes'] ));

		if( crontab_installed() )
		{
			try
			{
				$cron_file = "/tmp/crontab.txt";
				$cronUrl = url('/');

				$cmd  = "0 0,6,12,18 * * * wget -O /dev/null ".$cronUrl."/schedule/insert_schedules >/dev/null 2>&1\n";
				$cmd .= "*/2 * * * * wget -O /dev/null ".$cronUrl."/schedule/send_posts >/dev/null 2>&1\n";
				$cmd .= "*/5 * * * * wget -O /dev/null ".$cronUrl."/schedule/auto_resume_post >/dev/null 2>&1";

				$output = shell_exec('crontab -l');
				file_put_contents($cron_file, $output . $cmd . PHP_EOL);
				exec("crontab $cron_file");
			}
			catch(\Exception $e)
			{

			}
		}

		session()->flush();

		return redirect('/');
	}


	public function update()
	{
		if( env('APP_VER' , '') === '' )
		{
			redirect('install' )->send();
			exit();
		}

		return view('install.update');
	}

	public function updateSave(Request $request)
	{
		$validatedData = $request->validate([
			'product_key'	=>	'required|string|min:5|max:255'
		]);

		$productKey = $validatedData['product_key'];

		$checkPurchaseCodeURL = "http://api.codemasters.io/api.php?act=update&purchase_code=" . $productKey . "&version1=" . env('APP_VER') . "&version2=" . config('app.version');
		$result = Curl::getURL($checkPurchaseCodeURL);

		$result = json_decode($result , true);
		$result = is_array( $result ) ? $result : ['status' => 'error' , 'error_msg' => 'Error!!!'];

		if( !($result['status'] == 'ok' && isset($result['sql']) && isset($result['routes'])) )
		{
			return redirect()->back()->withErrors(['error_msg' => isset($result['error_msg']) ? $result['error_msg'] : 'Error!'])->withInput();
		}

		$pdo = $pdo = DB::connection()->getPdo();

		$dbb = base64_decode( $result['sql'] );
		$dbbArray = explode(';' , $dbb);

		foreach( $dbbArray AS $queryOne )
		{
			$queryOne = trim($queryOne);

			if( empty($queryOne) )
				continue;

			$qry = $pdo->prepare( $queryOne );
			$qry->execute();
		}

		file_put_contents(base_path('routes/web.php') , base64_decode( $result['routes'] ));

		session()->flush();

		// update version in .env file
		$envFile = file_get_contents(base_path('.env'));
		$envFile = str_replace('APP_VER=' . env('APP_VER') , 'APP_VER='.config('app.version') , $envFile);
		file_put_contents(base_path('.env') , $envFile);

		return redirect('/');
	}

}
