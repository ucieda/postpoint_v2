<?php

namespace App\Lib;

use App\Fb_account;
use App\Fb_account_access_token;
use App\Fb_account_node;
use App\Fb_account_node_categorie;
use App\Fb_account_node_categorie_list;
use App\Fb_app;
use App\User;
use Illuminate\Support\Facades\Auth;

class FBLib
{

	private static $fb			= [];
	private static $loginHelper	= [];

	public static function getAccessTookenWithAuth( string $userName , string $password , string $apiKey , string $apiSecret)
	{
		$data = array(
			'api_key' => $apiKey,
			'credentials_type' => 'password',
			'email' => $userName,
			'format' => 'JSON',
			'generate_machine_id' => '1',
			'generate_session_cookies' => '0',
			'locale' => 'en_US',
			'method' => 'auth.login',
			'password' => $password,
			'return_ssl_resources' => '0',
			'v' => '1.0'
		);

		$sig = '';

		foreach($data as $key => $value)
		{
			$sig .= $key . '=' . $value;
		}

		$sig .= $apiSecret;

		$data['sig'] = md5($sig);

		return 'https://api.facebook.com/restserver.php?' . http_build_query($data);

		/*$response = Curl::getContents('https://api.facebook.com/restserver.php' , 'GET' , $data);

		$responseJSON = json_decode($response , true);

		return $responseJSON;*/
	}

	public static function getAppInfo( $appId )
    {
        $appInfo = json_decode( Curl::getContents('https://graph.facebook.com/' . $appId) , true );

        return is_array($appInfo) && !isset($appInfo['error']) && isset( $appInfo['link'] ) ? $appInfo : false;
    }

    public static function validateAppSecret( $appId , string $appSecret )
    {
    	$getInfo = json_decode( Curl::getContents( 'https://graph.facebook.com/'.$appId.'?fields=roles,name,link,category&access_token='.$appId.'|'.$appSecret ) , true );
    	
    	return is_array($getInfo) && !isset($getInfo['error']) && isset($getInfo['name']) ? $getInfo : false;

    }

	public static function fb($appId , $appSecret)
	{
		if( !isset( self::$fb[ $appId ] ) )
		{
			if( session_status() !== PHP_SESSION_ACTIVE )
			{
				session_start();
			}

			self::$fb[ $appId ] = new \Facebook\Facebook([
				'app_id' => $appId,
				'app_secret' => $appSecret,
				'default_graph_version' => 'v2.10',
				'persistent_data_handler'=>'session'
				//'default_access_token' => '{access-token}', // optional
			]);
		}

		return self::$fb[ $appId ];
	}

	public static function loginHelper($appId , $appSecret)
	{
		if( !isset( self::$loginHelper[ $appId ] ) )
		{
			self::$loginHelper[ $appId ] = self::fb($appId , $appSecret)->getRedirectLoginHelper();
		}

		return self::$loginHelper[ $appId ];
	}

	public static function getLoginURL($appId , $appSecret , $callbackUrl = null)
	{
		$permissions = ['email', 'user_birthday', 'user_posts' , 'user_likes' , 'publish_actions' , 'manage_pages' , 'publish_pages' , 'user_managed_groups'];

		if( is_null( $callbackUrl ) )
		{
			$callbackUrl = rtrim(env('APP_URL') , '/') . '/fb/loginCallback';
		}

		return self::loginHelper( $appId , $appSecret )->getLoginUrl( $callbackUrl , $permissions );
	}

	public static function cmd( string $cmd , string $method = 'GET' , string $accessToken , array $data = [] )
	{
		$data['access_token'] = $accessToken;

		$url = 'https://graph.facebook.com/' . $cmd . '?' . http_build_query( $data );

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = Curl::getContents( $url , $method );
		$data = json_decode( $data1 , true );

		if( !is_array($data) )
		{
			$data = [
				'error' =>  ['message' => 'Error data!']
			];
		}

		/*if( isset($data['error']) )
		{
			response()->json([
				'status'    =>  'error',
				'error_msg' =>  isset($data['error']['message']) ? $data['error']['message'] : 'Access Token error!'
			])->send();
			exit();
		}*/

		return $data;
	}

	public static function getAccessToken($appId , $appSecret)
	{
		try
		{
			$accessToken = self::loginHelper($appId , $appSecret)->getAccessToken();
		}
		catch(Facebook\Exceptions\FacebookResponseException $e)
		{
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		}
		catch(Facebook\Exceptions\FacebookSDKException $e)
		{
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		return isset($accessToken) ? $accessToken : null;
	}

	public static function authorizeFbUser( $appId , $accessToken , $appInf = null )
	{
		if( is_null($appInf) )
		{
			$appInf = Fb_app::where('id' , $appId)->first();
		}

		$me = FBLib::cmd('/me', 'GET' , $accessToken , ['fields' => 'id,name,email,birthday,gender'] );

		$checkLoginRegistered =
			Fb_account::where('user_id' , Auth::user()->id)
				->where('fb_account_id' , $me['id'] ?? null)
				->first();

		$dataSQL = [
			'user_id'			=>	Auth::user()->id,
			'name'				=>	$me['name'] ?? null,
			'fb_account_id'		=>	$me['id'] ?? null,
			'email'				=>	$me['email'] ?? null,
			'gender'			=>	$me['gender'] == 'male' ? '1' : '2',
			'birthday'			=>	date('Y-m-d' , strtotime($me['birthday'] ?? ''))
		];

		if( !$checkLoginRegistered )
		{
			// check user role - max fb account limit
			if( Auth::user()->User_role )
			{
				Auth::user()->User_role->checkFbAccountsLimit();
			}

			$dataSQL['default_app_id'] = $appId;
			Fb_account::insert($dataSQL);
			$fbAccId = Fb_account::max('id');
		}
		else
		{
			$fbAccId = $checkLoginRegistered['id'];
			Fb_account::where('id' , $fbAccId)->update($dataSQL);

			Fb_account_access_token::where('fb_account_id' , $fbAccId)->where('app_id' , $appId)->delete();

			Fb_account_node::where('fb_account_id' , $fbAccId)->delete();

			$categories = Fb_account_node_categorie::where('fb_account_id' , $fbAccId)->get()->pluck('id');

			Fb_account_node_categorie_list::whereIn('category_id' , $categories)->delete();
		}

		$expiresOn = FBLib::getAccessTookenExpiresDate( $accessToken );

		// acccess token
		Fb_account_access_token::insert([
			'fb_account_id'	=>	$fbAccId,
			'app_id'		=>	$appId,
			'expires_on'	=>	$expiresOn,
			'access_token'	=>	$accessToken
		]);

		// own pages load
		if( Auth::user()->load_my_ownpages == 1 )
		{
			$accountsList = FBLib::cmd('/me/accounts', 'GET' , $accessToken , ['fields' => 'access_token,category,name,id,likes'] );
			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				foreach($accountsList['data'] AS $accountInfo)
				{
					Fb_account_node::insert([
						'user_id'			=>	Auth::id(),
						'fb_account_id'		=>	$fbAccId,
						'node_type'			=>	'ownpage',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	$accountInfo['access_token'],
						'category'			=>	$accountInfo['category'] ?? '',
						'fan_count'			=>	$accountInfo['likes'] ?? 0
					]);
				}
			}
		}

		// pages load
		if( Auth::user()->load_my_pages == 1 )
		{
			$limit = Auth::user()->max_pages_to_import;
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = FBLib::cmd('/me/likes', 'GET' , $accessToken , [
				'fields' => 'category,name,id,likes' ,
				'limit' => $limit
			]);
			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				foreach($accountsList['data'] AS $accountInfo)
				{
					Fb_account_node::insert([
						'user_id'			=>	Auth::id(),
						'fb_account_id'		=>	$fbAccId,
						'node_type'			=>	'page',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	null,
						'category'			=>	$accountInfo['category'] ?? '',
						'fan_count'			=>	$accountInfo['likes'] ?? 0
					]);
				}
			}
		}

		// groups load
		if( Auth::user()->load_my_groups == 1 )
		{
			$limit = Auth::user()->max_groups_to_import;
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = FBLib::cmd('/me/groups' , 'GET' , $accessToken , [
				'fields'	=>	'name,privacy,id,member_count',
				'limit'		=>	$limit
			] );
			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				foreach($accountsList['data'] AS $accountInfo)
				{
					Fb_account_node::insert([
						'user_id'			=>	Auth::id(),
						'fb_account_id'		=>	$fbAccId,
						'node_type'			=>	'group',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						//'access_token'		=>	null,
						'category'			=>	$accountInfo['privacy'],
						'fan_count'			=>	$accountInfo['member_count'] ?? 0
					]);
				}
			}
		}

		if( (int)Auth::user()->fb_account_id == 0 )
		{
			User::where('id' , Auth::id())->update(['fb_account_id' => $fbAccId]);
		}
	}

	public static function getAccessTokenDetails( $accessToken )
	{
		$url = 'https://graph.facebook.com/app?fields=id,category,company,name&access_token=' . $accessToken;

		$data = json_decode( Curl::getContents( $url ) , true );

		return $data;
	}

	public static function getAccessTookenExpiresDate( $accessToken )
	{
		$url = 'https://graph.facebook.com/oauth/access_token_info?fields=id,category,company,name&access_token=' . $accessToken;

		$data = json_decode( Curl::getContents( $url ) , true );

		return is_array($data) && isset($data['expires_in']) && $data['expires_in'] > 0 ? date('Y-m-d H:i:s' , time() + $data['expires_in']) : null;
	}

	public static function extractAccessToken( $accessTokenCode )
	{
		$res =  [
			"status" => false,
			"message" => "Invalid Access token",
			"access_token" => "",
		];

		preg_match('~access_token=(.*)(?=&expires_in)~' , $accessTokenCode ,$m);

		if(isset($m[1]))
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $m[1];

			return $res;
		}

		$r = json_decode($accessTokenCode,true);

		if( is_array( $r ) )
		{
			if( isset( $r['access_token'] ) )
			{
				$res['status'] = true;
				$res['message'] = "";
				$res['access_token'] = $r['access_token'];

				return $res;
			}

			if(isset($r['error_data']))
			{
				$rr = json_decode($r['error_data'],true);

				if( is_array( $rr ) )
				{
					if(isset($rr['error_message']))
					{
						$res['status'] = false;
						$res['message'] = $rr['error_message'];
						$res['access_token'] = "";

						return $res;
					}
				}
			}

			if(isset( $r['error_msg'] ))
			{
				$res['status'] = false;
				$res['message'] = $r['error_msg'];
				$res['access_token'] = "";
				return $res;
			}

		}

		preg_match('~"access_token":"(.*)(?=","machine_id)~',$accessTokenCode,$m);

		if( isset( $m[1] ) )
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $m[1];

			return $res;
		}

		if(trim($accessTokenCode) != "")
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $accessTokenCode;
		}

		return $res;
	}

	public static function sendPost( $nodeFbId , $type , $message , $preset_id , $link , $images , $video , $accessToken )
	{
		$sendData = [
			'message'	=>	spintax( $message )
		];

		if( $preset_id > 0 && $type == 'status' )
		{
			$sendData['text_format_preset_id'] = $preset_id;
		}
		else if( $type == 'link' )
		{
			$sendData['link'] = spintax( $link );
		}

		$endPoint = 'feed';

		if( $type == 'image' )
		{
			$sendData['attached_media'] = [];
			foreach($images AS $imageURL)
			{
				$sendData2 = [
					'url' 		=>	spintax( $imageURL ),
					'published'	=>	'false',
					'caption'	=>	''
				];

				$imageUpload = FBLib::cmd('/' . $nodeFbId . '/photos' , 'POST' , $accessToken , $sendData2);

				if( isset( $imageUpload['id'] ) )
				{
					$sendData['attached_media'][] = json_encode([ 'media_fbid' => $imageUpload['id'] ]);
				}
			}

		}
		if( $type == 'video' )
		{
			$endPoint = 'videos';
			$sendData['file_url']		= spintax( $video );
			$sendData['description']	= spintax( $message );
		}

		$result = FBLib::cmd('/' . $nodeFbId . '/' . $endPoint , 'POST' , $accessToken , $sendData);

		if( isset($result['error']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	isset($result['error']['message']) ? $result['error']['message'] : 'Error!'
			];
		}
		else
		{
			if(isset($result['id']))
			{
				$stsId = explode('_' , $result['id']);
				$stsId = end($stsId);
			}
			else
			{
				$stsId = 0;
			}

			$result2 = [
				'status'    =>  'ok',
				'id'		=>	$stsId
			];
		}

		return $result2;
	}

}