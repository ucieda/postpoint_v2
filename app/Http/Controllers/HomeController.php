<?php

namespace App\Http\Controllers;

use App\Fb_account;
use App\Fb_account_access_token;
use App\Fb_account_node;
use App\Fb_account_node_categorie;
use App\Fb_account_node_categorie_list;
use App\Fb_app;
use App\Fb_post;
use App\Fb_post_archive;
use App\Fb_post_image;
use App\Fb_post_schedule_log;
use App\Fb_post_schedule_node;
use App\Lib\FBLib;
use App\Lib\Payments\Paypal;
use App\Lib\Payments\Stripe;
use App\Notification;
use App\Payment;
use App\Post_statistic;
use App\User;
use App\User_role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( $postId = 0 )
    {
		// settings
		setHomeSettings();

	    $autoScheduleData = Input::get('date' , null);

		if( $postId > 0 )
		{
			$postInf = Fb_post::where('user_id' , Auth::id())->with('Fb_post_image')->where('id' , $postId)->first();
			if( !$postInf )
			{
				$postId = 0;
			}
		}

		if( $postId == 0 )
		{
			$postInf = [
				'message'						=>	'',
				'user_id'						=>	Auth::id(),
				'title'							=>	'',
				'post_interval'					=>	Auth::user()->post_interval >= 60 ? (int)Auth::user()->post_interval : '60',
				'preset_id'						=>	0,
				'link'							=>	'',
				'link_picture'					=>	'',
				'link_title'					=>	'',
				'link_caption'					=>	'',
				'link_description'				=>	'',
				'post_type'						=>	'status',
				'video'							=>	'',
				'fb_post_image'					=>	[['image' => '']],
				'is_scheduled'					=>	0,
				'schedule_post_interval'		=>	0,
				'schedule_start'				=>	date(dateFormat() . ' H:i' , $autoScheduleData ? strtotime($autoScheduleData . " " . date('H:i:s')) : time()),
				'schedule_fb_app_id'			=>	0,
				'schedule_auto_pause'			=>	0,
				'schedule_auto_resume'			=>	0,
				'schedule_frequency'			=>	0,
				'schedule_end'					=>	'',
				'product_name'					=>	'',
				'product_price'					=>	''
			];
		}

		$accountId = Auth::user()->fb_account_id;

		if( $accountId > 0 )
		{
			$fbAccountInf = Fb_account::where('id' , $accountId)->first();

			if( !$fbAccountInf )
			{
				User::where('id' , Auth::id())->update([
					'fb_account_id'	=>	'0'
				]);
				$fbAccountName	= '';
				$fbAccountId	=	0;
			}
			else
			{
				$fbAccountName	= $fbAccountInf->name;
				$fbAccountId	= $fbAccountInf->fb_account_id;
			}
		}
		else
		{
			$fbAccountName	= '';
			$fbAccountId	=	0;
		}

		// get Node categories
		$nodeCategories = Fb_account_node_categorie::where('fb_account_id' , $accountId)->get();

		$categoryId = Input::get('cat_id' , 0);

		$checkCategory = Fb_account_node_categorie::where('id' , $categoryId)->first();
		if( !$checkCategory || $checkCategory->fb_account_id != Auth::user()->fb_account_id )
		{
			$categoryId = 0;
		}

		if( is_numeric($categoryId) && $categoryId > 0 )
		{
			$nodesList = Fb_account_node::whereIn('id', function($query) use ($categoryId)
			{
				$query
					->select('node_id')
					->from(with(new Fb_account_node_categorie_list)->getTable())
					->where('category_id' , $categoryId);
			});
		}
		else
		{
			$nodesList = Fb_account_node::where('fb_account_id' , $accountId);
		}

		if( !session('home_settings_show_groups') )
		{
			$nodesList = $nodesList->where('node_type' , '!=' , 'group');
		}
		if( !session('home_settings_show_pages') )
		{
			$nodesList = $nodesList->where('node_type' , '!=' , 'page')->where('node_type' , '!=' , 'ownpage');
		}
		if( !session('home_settings_show_hiddens') )
		{
			$nodesList = $nodesList->where('is_hidden' , '0');
		}
		if( Auth::user()->show_open_groups_only )
		{
			$nodesList->where('category' , '<>' , 'CLOSED');
		}

		$nodesList = $nodesList->get();

		$statistics = [
			'group'	=>	0,
			'page'	=>	0
		];
		foreach($nodesList AS $nodeInf)
		{
			$type = $nodeInf->node_type == 'ownpage' ? 'page' : $nodeInf->node_type;
			$statistics[$type]++;
		}

		$fbApps = [];
		$registredApps = Fb_account_access_token::where('fb_account_id' , $accountId)->select('id', 'app_id')->get();
		foreach ( $registredApps AS $appInf )
		{
			$appInf2 = Fb_app::where('id' , $appInf['app_id'])->first();

			if( !$appInf2 ) continue;

			$fbApps[] = [
				'id'	=> $appInf['id'],
				'name'	=>	$appInf2['name']
			];
		}

        return view('home' , [
			'fb_account_name'	    =>	$fbAccountName,
			'fb_account_id'		    =>	$fbAccountId,
			'nodeCategories'	    =>	$nodeCategories,
			'nodesList'			    =>	$nodesList,
			'categoryId'		    =>	$categoryId,
			'statistics'		    =>	$statistics,
			'postId'			    =>	$postId,
			'postInf'			    =>	$postInf,
			'fbApps'			    =>	$fbApps,
	        'autoScheduleData'     =>  $autoScheduleData
        ]);
    }

    public function changeSettings(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'state'	 		=> 'required|numeric|max:1|min:0',
			'type'			=> 'required|string'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$type = (string)Input::post('type');
		$state = (int)Input::post('state');

		if( !in_array( $type , ['pages' , 'groups' , 'hiddens'] ) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$state = $state == '1' ? true : false;

		session(['home_settings_show_' . $type => $state]);

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function addNodeCategory()
	{
		return viewModal('addNodeCategory' , 'Create new category');
	}

	public function addNodeCategorySave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'name'	 		=> 'required|string|max:75',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$name = (string)Input::post('name');

		Fb_account_node_categorie::insert([
			'name'			=>	$name,
			'fb_account_id'	=>	Auth::user()->fb_account_id
		]);

		return response()->json([
			'status'    =>  'ok',
			'id'		=>	Fb_account_node_categorie::max('id')
		]);
	}

	public function addNode()
	{
		$accountId = Auth::user()->fb_account_id;

		$nodeCategories = Fb_account_node_categorie::where('fb_account_id' , $accountId)->get();

		return viewModal('addNode' , 'Add group to category' , ['categories' => $nodeCategories]);
	}

	public function addNodeSave(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'category'	=> 'required|numeric|min:1',
			'nodes.*'	=>	'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$category	= (int)Input::post('category');
		$nodes		= Input::post('nodes');

		if( !is_array($nodes) || count($nodes) == 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.nodes_not_selected')
			]);
		}

		$checkCategory = Fb_account_node_categorie::where('id' , $category)->first();

		if( !$checkCategory || $checkCategory->fb_account_id != Auth::user()->fb_account_id )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.category_not_found')
			]);
		}

		foreach($nodes AS $nodeId)
		{
			$nodeId = (int)$nodeId;

			Fb_account_node_categorie_list::insert([
				'category_id'	=>	$category,
				'node_id'		=>	$nodeId
			]);
		}

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function deleteCategory(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id'	=> 'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$category	= (int)Input::post('id');

		$checkCategory = Fb_account_node_categorie::where('id' , $category)->first();

		if( !$checkCategory || $checkCategory->fb_account_id != Auth::user()->fb_account_id )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.category_not_found')
			]);
		}

		Fb_account_node_categorie_list::where('category_id' , $category)->delete();
		Fb_account_node_categorie::where('id' , $category)->delete();

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function deleteNodes(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'cat_id'	=> 'required|numeric|min:1',
			'nodes.*'		=> 'required|numeric|min:1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$category	= (int)Input::post('cat_id');
		$nodes	= Input::post('nodes');

		if( !is_array($nodes) || count($nodes) == 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.nodes_not_selected')
			]);
		}

		$checkCategory = Fb_account_node_categorie::where('id' , $category)->first();

		if( !$checkCategory || $checkCategory->fb_account_id != Auth::user()->fb_account_id )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.category_not_found')
			]);
		}

		Fb_account_node_categorie_list::where('category_id' , $category)->whereIn('node_id' , $nodes)->delete();

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function hideUnhideNodes(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'nodes.*'		=>	'required|numeric|min:1',
			'type'			=>	'required|string'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$nodes	= Input::post('nodes');
		$isHidden = Input::post('type') == 'hide' ? 1 : 0;

		if( !is_array($nodes) || count($nodes) == 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.nodes_not_selected')
			]);
		}

		Fb_account_node::where('fb_account_id' , Auth::user()->fb_account_id)
			->whereIn('id' , $nodes)
			->update(['is_hidden' => $isHidden]);

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function sendPost(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'post_id'			=>	'required|numeric|min:0',
			'node_id'			=>	'required|numeric|min:-1',
			'message'			=>	'nullable|string',
			'preset_id'			=>	'required|numeric|min:0',
			'type'				=>	'required|string',
			'link'				=>	'nullable|string|max:750',
			'link_picture'		=>	'nullable|string|max:255',
			'link_title'		=>	'nullable|string|max:255',
			'link_caption'		=>	'nullable|string|max:255',
			'link_description'	=>	'nullable|string|max:500',
			'images.*'			=>	'required|string',
			'video'				=>	'nullable|string',
			'product_name'		=>	'nullable|string|max:255',
			'product_price'		=>	'nullable|string|max:255',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		if( !siteOption('enable_instant_post') )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.instant_post_disabled')
			]);
		}

		$post_id			= (int)Input::post('post_id');
		$node_id			= (int)Input::post('node_id');
		$message			= (string)Input::post('message');
		$preset_id			= (string)Input::post('preset_id');
		$type				= (string)Input::post('type');
		$link				= $type !='link' ? '' : (string)Input::post('link');
		$link_picture		= $type !='link' ? '' : (string)Input::post('link_picture');
		$link_caption		= $type !='link' ? '' : (string)Input::post('link_caption');
		$link_title			= $type !='link' ? '' : (string)Input::post('link_title');
		$link_description	= $type !='link' ? '' : (string)Input::post('link_description');
		$video				= $type !='video' ? '' : (string)Input::post('video');
		$images				= $type !='image' ? [] : (array)Input::post('images' , []);

		if( !(siteOption('enable_link_customization') && Auth::user()->link_customization) )
		{
			$link_picture		= '';
			$link_caption		= '';
			$link_title			= '';
			$link_description	= '';
		}
		else if( !empty($link_picture) || !empty($link_caption) || !empty($link_title) || !empty($link_description) )
		{
			$customLink = url('custom_link') . '/?url=' . urlencode($link);
			$customLink .= '&picture=' . urlencode($link_picture);
			$customLink .= '&caption=' . urlencode($link_caption);
			$customLink .= '&title=' . urlencode($link_title);
			$customLink .= '&description=' . urlencode($link_description);

			$link = $customLink;
		}

		$productName		= siteOption('enable_sale_post') ? (string)Input::post('product_name') : '';
		$productPrice		= siteOption('enable_sale_post') ? (string)Input::post('product_price') : '';

		if( $node_id == 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$allowedTypes = ['status' , 'link' , 'image' , 'video'];

		if( !in_array($type , $allowedTypes) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.type_error')
			]);
		}

		if( $type == 'status' && $message == '' )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.status_text_is_empty')
			]);
		}

		if( $type == 'video' && $video == '' )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.video_url_is_empty')
			]);
		}
		if( $type == 'image' && (!is_array($images) || count($images) == 0) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.image_url_is_empty')
			]);
		}

		if( $type == 'link' && empty($link) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.link_is_empty')
			]);
		}

		$fbAccountId = Auth::user()->fb_account_id;

		$fbAccountInf = Fb_account::where('id' , $fbAccountId)->first();
		if( !$fbAccountInf )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.no_fb_account_available')
			]);
		}

		if( $node_id == -1 )
		{
			$nodeInfo = [
				'node_id'	=>	$fbAccountInf['fb_account_id'],
				'node_type'	=>	'me'
			];
		}
		else
		{
			$nodeInfo = Fb_account_node::where('id' , $node_id)->first();
		}

		$defaultAppId = $fbAccountInf->default_app_id;
		if( ($defaultAppId > 0) === false )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.no_app_has_been_selected')
			]);
		}

		$getAccessTooken = Fb_account_access_token::where('app_id' , $defaultAppId)->where('fb_account_id' , $fbAccountId)->first();
		if( !$getAccessTooken )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.no_fb_account_in_this_app')
			]);
		}

		if( $node_id > 0 && ( !$nodeInfo || $nodeInfo->fb_account_id != $fbAccountId ) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.node_not_found')
			]);
		}

		// check post limit for per day
		if( Auth::user()->User_role )
		{
			Auth::user()->User_role->checkPostsLimit();
		}

		$nodeFbId = $nodeInfo['node_id'];

		switch($nodeInfo['node_type'])
		{
			case "ownpage":
				$accessToken = $nodeInfo->access_token;
				break;
			default:
				$accessToken = $getAccessTooken->access_token;
				break;
		}

		if( Auth::user()->unique_post && !empty($message) )
		{
			$message .= "\n\n" . uniqid();
		}
		if( Auth::user()->unique_link && !empty($link) )
		{
			$link .= ( strpos($link , '?') === false ? '?' : '&' ) . '_random=' . uniqid();
		}

		if( siteOption('enable_instant_post') && !empty($productName) && !empty($productPrice) )
		{
			$message = $productName . " " . __('home.product_for_sale') . " \n" . __('home.product_price2') . " : " . $productPrice . "\n" . $message;
		}

		$result2 = FBLib::sendPost( $nodeFbId , $type , $message , $preset_id , $link , $images , $video , $accessToken );

		Fb_post_archive::insert([
			'user_id'			=>		Auth::id(),
			'fb_account_id'		=>		$fbAccountId,
			'app_id'			=>		$defaultAppId,
			'node_id'			=>		$node_id,
			'status'			=>		$result2['status'],
			'error_message'		=>		$result2['status'] == 'error' ? $result2['error_msg'] : null,
			'time'				=>		date('Y-m-d H:i:s'),
			'fb_feed_id'		=>		$result2['id'] ?? null,
			'post_id'			=>		$post_id,
			'post_type'			=>		$type
		]);
		Post_statistic::addStatistic( ($result2['status'] == 'error' ? 'fail' : 'success') , true );

		return response()->json( $result2 );
	}

	public function savePost(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'post_id'			=>	'required|numeric|min:0',
			'message'			=>	'nullable|string',
			'title'				=>	'required|string|max:255',
			'link'				=>	'nullable|string|max:750',
			'link_picture'		=>	'nullable|string|max:255',
			'link_title'		=>	'nullable|string|max:255',
			'link_caption'		=>	'nullable|string|max:255',
			'link_description'	=>	'nullable|string|max:500',
			'video'				=>	'nullable|string|max:750',
			'images.*'			=>	'required|string|max:750',
			'interval'			=>	'required|numeric|min:1|max:1500',
			'type'				=>	'required|string',
			'preset_id'			=>	'required|numeric|min:0',
			'product_name'		=>	'nullable|string|max:255',
			'product_price'		=>	'nullable|string|max:255'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$post_id			= (int)Input::post('post_id');
		$message			= (string)Input::post('message');
		$type				= (string)Input::post('type');
		$title				= (string)Input::post('title');
		$interval			= (int)Input::post('interval');
		$preset_id			= (string)Input::post('preset_id');
		$link				= (string)Input::post('link');
		$link_picture		= (string)Input::post('link_picture');
		$link_caption		= (string)Input::post('link_caption');
		$link_title			= (string)Input::post('link_title');
		$link_description	= (string)Input::post('link_description');
		$video				= (string)Input::post('video');
		$images				= (array)Input::post('images');

		$productName		= siteOption('enable_sale_post') ? (string)Input::post('product_name') : '';
		$productPrice		= siteOption('enable_sale_post') ? (string)Input::post('product_price') : '';

		$allowedTypes = ['status' , 'link' , 'image' , 'video'];

		if( !(siteOption('enable_link_customization') && Auth::user()->link_customization) )
		{
			$link_picture		= '';
			$link_caption		= '';
			$link_title			= '';
			$link_description	= '';
		}

		if( !in_array($type , $allowedTypes) )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.type_error')
			]);
		}

		if( $type == 'status' && $message == '' )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.status_text_is_empty')
			]);
		}
		if( $type != 'image' )
		{
			$images = [];
		}
		if( $type != 'video' )
		{
			$video = '';
		}
		if( $type != 'link' )
		{
			$link = '';
			$link_picture		= '';
			$link_caption		= '';
			$link_title			= '';
			$link_description	= '';
		}

		$sqlData = [
			'message'			=>	$message,
			'title'				=>	$title,
			'post_interval'		=>	$interval,
			'user_id'			=>	Auth::id(),
			'post_type'			=>	$type,
			'preset_id'			=>	$preset_id,
			'video'				=>	$video,
			'link'				=>	$link,
			'link_title'		=>	$link_title,
			'link_picture'		=>	$link_picture,
			'link_caption'		=>	$link_caption,
			'link_description'	=>	$link_description,
			'product_name'		=>	$productName,
			'product_price'		=>	$productPrice
		];

		if( $post_id > 0 )
		{
			Fb_post::where('id' , $post_id)->update( $sqlData );

			Fb_post_image::where('fb_post_id' , $post_id)->delete();
		}
		else
		{
			$sqlData['created_at'] = date('Y-m-d H:i:s');
			Fb_post::insert( $sqlData );
			$post_id = Fb_post::max('id');
		}

		foreach ($images AS $imageURL)
		{
			Fb_post_image::insert([
				'fb_post_id'	=>	$post_id,
				'image'			=>	$imageURL
			]);
		}

		return response()->json([
			'status'    =>  'ok',
			'post_id'	=>	$post_id
		]);
	}

	public function schedulePost(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'post_id'					=>	'required|numeric|min:0',
			'schedule_duration'			=>	'required|numeric|min:0',
			'schedule_start'			=>	'required|string',
			'schedule_fb_app'			=>	'required|numeric|min:1',
			'schedule_auto_pause'		=>	'required|numeric|min:0',
			'schedule_auto_resume'		=>	'required|numeric|min:0',
			'schedule_frequency'		=>	'required|numeric|min:0',
			'schedule_end'				=>	'nullable|string',
			'nodes.*'					=>	'required|numeric|min:-1'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$post_id				= (int)Input::post('post_id');
		$schedule_duration		= (int)Input::post('schedule_duration');
		$schedule_start			= date('Y-m-d H:i' , strtotime((string)Input::post('schedule_start')));
		$schedule_fb_app		= (int)Input::post('schedule_fb_app');
		$schedule_auto_pause	= (int)Input::post('schedule_auto_pause');
		$schedule_auto_resume	= $schedule_auto_pause > 0 ? (int)Input::post('schedule_auto_resume') : 0;
		$schedule_frequency		= (int)Input::post('schedule_frequency');
		$schedule_end			= $schedule_frequency > 0 ? date('Y-m-d H:i' , strtotime((string)Input::post('schedule_end'))) : null;
		$nodes					= (array)Input::post('nodes');

		if( count($nodes) > 500 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('home.max_nodes_limit_is_500')
			]);
		}

		$dataUpdte = [
			'is_scheduled'					=>	'1',
			'schedule_post_interval'		=>	$schedule_duration,
			'schedule_start'				=>	$schedule_start,
			'schedule_fb_app_id'			=>	$schedule_fb_app,
			'schedule_auto_pause'			=>	$schedule_auto_pause,
			'schedule_auto_resume'			=>	$schedule_auto_resume,
			'schedule_frequency'			=>	$schedule_frequency,
			'schedule_end'					=>	$schedule_end,
			'schedule_fb_account_id'		=>	Auth::user()->fb_account_id,
			'schedule_last_inserted_date'	=>	null
		];

		if( $nodes != [0] )
		{
			$dataUpdte['schedule_nodes_count'] = count($nodes);
		}

		Fb_post::where('id' , $post_id)->update($dataUpdte);

		if( $nodes != [0] )
		{
			Fb_post_schedule_node::where('fb_post_id' , $post_id)->delete();

			foreach($nodes AS $nodeId)
			{
				Fb_post_schedule_node::insert([
					'fb_post_id'	=>	$post_id,
					'node_id'		=>	$nodeId
				]);
			}
		}

		Fb_post_schedule_log::where('fb_post_id' , $post_id)->delete();
		app(ScheduleController::class)->insertSchedules( $post_id );

		return response()->json([
			'status'	=>	'ok'
		]);
	}

	public function getUrlInfo(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'url'		=>	'required|string'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		return response()->json([
			'status'	=>	'ok',
			'data'		=>	getUrlInfo((string)Input::post('url') )
		]);
	}

	public function insights()
	{
		$reportType	=	Input::get('report_type' , 'monthly');

		$statistics1Array = [
			'labels' => [] ,
			'success' => [] ,
			'fails' => []
		];

		if( $reportType == 'daily' )
		{
			$pYear		=	date('Y-m-d' , strtotime('-30 day'));
			$statistics1 = Post_statistic::where('user_id' , Auth::id())
				->where('date' , '>=' , $pYear)
				->get();

			foreach( $statistics1 AS $stsInfo )
			{
				$statistics1Array['labels'][]	= date(dateFormat() , strtotime($stsInfo->date));
				$statistics1Array['success'][]	= $stsInfo->scheduled_success_count;
				$statistics1Array['fails'][]		= $stsInfo->scheduled_fails_count;
			}
		}
		else
		{
			$reportType = 'monthly';
			$pYear		=	date('Y-m-d' , strtotime('-1 year'));
			$statistics1 = Post_statistic::where('user_id' , Auth::id())
				->where('date' , '>=' , $pYear)
				->groupBy(DB::raw('year(date),month(date)'))
				->select(DB::raw('year(date) AS year,month(date) AS month,SUM(scheduled_success_count) AS success,SUM(scheduled_fails_count) AS fail'))
				->get();

			foreach( $statistics1 AS $stsInfo )
			{
				$statistics1Array['labels'][]	= date(($stsInfo->year == date('Y') ? 'M' : 'M , Y') , strtotime($stsInfo->year . '-' . $stsInfo->month . '-01'));
				$statistics1Array['success'][]	= $stsInfo->success;
				$statistics1Array['fails'][]		= $stsInfo->fail;
			}
		}

		$statistics2 = [
			Fb_account::where('user_id' , Auth::id())->count(),
			Fb_account_node::where('user_id' , Auth::id())->where('node_type' , 'group')->count(),
			Fb_account_node::where('user_id' , Auth::id())->whereIn('node_type' , ['page','ownpage'])->count()
		];

		$statistics3 = [
			Fb_post::where('user_id' , Auth::id())->count(),
			Fb_post::where('user_id' , Auth::id())->where('is_scheduled' , '1')->count()
		];

		$today = date('Y-m-d');
		$postCount = Post_statistic::where('user_id' , Auth::id())->where('date' , $today)->first();

		return view('insights' , [
			'reportType'	=>	$reportType,
			'statistics1'	=>	$statistics1Array,
			'statistics2'	=>	$statistics2,
			'statistics3'	=>	$statistics3,
			'postCount'		=>	$postCount ? $postCount->success_count_all + $postCount->fails_count_all : 0,
			'dirSize'		=>	dirSize( 'uploads/user_' . Auth::id() )
		]);
	}

	public function settings()
	{
		return view('settings');
	}

	public function switch_fb_account($id)
	{
		$checkFbAccount = Fb_account::where('user_id' , Auth::id())->where('id' , $id)->count();

		if( $checkFbAccount == 0 )
		{
			return __('general.fb_account_not_found' , ['id'	=>	htmlspecialchars($id)]);
		}

		User::where('id' , Auth::id())->update(['fb_account_id' => $id]);

		return redirect()->back();
	}

	public function fileBrowser()
	{
		return viewModal('file_browser');
	}

	public function accountExpired()
	{
		return view('account_expired');
	}

	public function accountDeactivated()
	{
		return view('account_deactivated');
	}

	public function permissionError()
	{
		return view('permission_error');
	}

	public function maintenanceMode()
	{
		return view('maintenance_mode');
	}

	public function notifications( $id = 0 )
	{
		$notifications = Notification::where('user_id' , Auth::id())->orderBy('status' , 'DESC')->orderBy('id' , 'DESC')->get();

		return view('notifications' , ['data' => $notifications , 'id'	=>	$id]);
	}

	public function notificationsStatusChange(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'id'		=>	'required|numeric|min:0',
			'status'	=>	'required|numeric|min:0|max:1',
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id		= (int)Input::post('id');
		$status	= (int)Input::post('status');

		Notification::where('id' , $id)->where('user_id' , Auth::id())->update(['status' => $status]);

		return response()->json([
			'status'	=>	'ok'
		]);
	}

	public function accountUpgrade( $package = null )
	{
		if( is_null($package) )
		{
			$packets = User_role::where('is_for_demo' , '!=' , '1')->orderBy('monthly_price')->get();

			return view('account_upgrade' , ['packets' => $packets]);
		}
		else
		{
			$packageInf = User_role::where('id' , $package)->first();

			return view('account_upgrade_details' , ['packageInf' => $packageInf]);
		}

	}

	public function accountUpgradeOrder( Request $request )
	{
		$validatedData = \Validator::make($request->all() , [
			'package_id'		=>	'required|numeric|min:1',
			'plan'				=>	'required|in:monthly,annual',
			'payment_method'	=>	'required|in:paypal,stripe',
			'payment_cycle'		=>	'required|in:one_time,recurring'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$id		= (int)Input::post('package_id');
		$plan	= Input::post('plan');
		$payment_method	= Input::post('payment_method');
		$payment_cycle	= Input::post('payment_cycle');

		$packageInf = User_role::where('id' , $id)->where('is_for_demo', '<>' , '1')->first();

		if( !$packageInf )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' => 'Package not found!'
			]);
		}

		// check if user is subscripbed
		$subscripbed = Payment::where('user_id' , Auth::id())
			->where('status' , 4)
			->where('is_subscribed' , 1)
			->first();

		if( $subscripbed )
		{
			return response()->json([
				'status'    =>	'error',
				'error_msg' =>	'You have subscribed for paymanets also. You can not payment now. Please cancel your subscribtion from Payment list or wait for auto payment!'
			]);
		}

		$amount = $plan == 'monthly' ? $packageInf['monthly_price'] : $packageInf['annual_price'];
		$amount = $amount > 0 ? $amount : 0;

		$paymentId = Payment::insertGetId([
			'user_id'			=>	Auth::id(),
			'package_id'		=>	$id,
			'amount'			=>	$amount,
			'status'			=>	'0',
			'added_time'		=>	date('Y-m-d H:i:s'),
			'package_name'		=>	$packageInf['name'],
			'payment_method'	=>	$payment_method,
			'plan'				=>	$plan,
			'payment_cycle'		=>	$payment_cycle
		]);

		if( $payment_method == 'paypal' )
		{
			$payment = new Paypal();

			$payment->setId( $paymentId );
			$payment->setAmount( $amount );
			$payment->setItem( $packageInf['id'] , $packageInf['name'] , $packageInf['name'] );
			$payment->setPlan( $plan );

			if( $payment_cycle == 'recurring' )
			{
				$res = $payment->createRecurringPayment();
			}
			else
			{
				$res = $payment->create();
			}

			if( $res['status'] == true )
			{
				return response()->json([
					'status'	=>	'ok',
					'url'		=>	$res['url']
				]);
			}
			else
			{
				return response()->json([
					'status'	=>	'ok',
					'error_msg'	=>	$res['error']
				]);
			}
		}
		else
		{
			return response()->json([
				'status'		=>	'ok',
				'id'			=>	$paymentId,
				'package_name'	=>	htmlspecialchars($packageInf['name']),
				'amount'		=>	round( $amount * 100 ),
				'description'	=>	htmlspecialchars(ucfirst($plan))
			]);
		}
	}

	public function accountUpgradeFinish()
	{
		if(
			!(isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0
			&& isset($_GET['success']) && is_string($_GET['success']) && ($_GET['success'] == 'true' || $_GET['success'] == 'false'))
		)
		{
			return 'Error!';
		}

		$id = (int)$_GET['id'];
		$success = $_GET['success'];

		$checkPayment = Payment::where('user_id' , Auth::id())->where('status','0')->where('id' , $id)->first();

		if( !$checkPayment )
		{
			return 'Error! Payment not found!';
		}

		$paymentMethod = $checkPayment['payment_method'];

		if( $checkPayment['plan'] == 'monthly' )
		{
			$accountNewExpireDate = date('Y-m-d' , strtotime('+1 month'));
		}
		else
		{
			$accountNewExpireDate = date('Y-m-d' , strtotime('+1 year'));
		}

		if( $success == 'false' )
		{
			return $this->paymentStatus($id , 2 );
		}
		else if(
			$paymentMethod == 'paypal'
			&& isset($_GET['PayerID'] , $_GET['paymentId'])
			&& is_string( $_GET['PayerID'] ) && is_string( $_GET['paymentId'] )
			&& !empty( $_GET['PayerID'] ) && !empty( $_GET['paymentId'] )
			&& $checkPayment['payment_cycle'] == 'one_time'
		)
		{
			$payerId = $_GET['PayerID'];
			$paymentId = $_GET['paymentId'];

			$payment = new Paypal();

			$payment->setId($id);

			$result = $payment->check( $payerId , $paymentId );

			if( $result['status'] === true )
			{
				User::where('id' , Auth::id())->update([
					'expire_on'		=>	$accountNewExpireDate,
					'user_role_id'	=>	$checkPayment['package_id']
				]);

				return $this->paymentStatus($id , 1);
			}
			else
			{
				return $this->paymentStatus($id , 3);
			}
		}
		else if(
			$paymentMethod == 'paypal'
			&& isset( $_GET['token'] ) && is_string( $_GET['token'] ) && !empty( $_GET['token'] )
			&& $checkPayment['payment_cycle'] == 'recurring'
		)
		{
			$token = $_GET['token'];

			$payment = new Paypal();

			$payment->setId($id);

			$result = $payment->checkRecurring( $token );

			if( $result['status'] === true )
			{
				return $this->paymentStatus($id , 4 , null , $result['id']);
			}
			else
			{
				return $this->paymentStatus($id , 3 , ($result['message'] ?? 'Error') );
			}
		}
		else if(
			$paymentMethod == 'stripe'
			&& isset($_GET['token']) && is_string($_GET['token']) && !empty($_GET['token'])
		)
		{
			$token = (string)$_GET['token'];

			\Stripe\Stripe::setApiKey(siteOption('stripe_secret_key'));

			if( $checkPayment['payment_cycle'] == 'one_time' )
			{
				try
				{
					$charge = \Stripe\Charge::create([
						'amount'		=> round( $checkPayment['amount'] * 100 ),
						'currency'		=> 'USD',
						'description'	=> ucfirst($checkPayment['plan']),
						'source'		=> $token,
					]);

					$chargeID = $charge['id'];

					User::where('id' , Auth::id())->update([
						'expire_on'		=>	$accountNewExpireDate,
						'user_role_id'	=>	$checkPayment['package_id']
					]);

					return $this->paymentStatus($id , 1);
				}
				catch (\Exception $e)
				{
					return $this->paymentStatus($id , 3 , $e->getMessage());
				}
			}
			else
			{
				$customerId = null;

				// check if customer id exists
				$findCustomerId = Payment::where('user_id' , Auth::id())
					->where('payment_method' , 'stripe')
					->whereNotNull('customer_id')
					->first();
				if( $findCustomerId && !empty($findCustomerId->customer_id) )
				{
					$checkCustomerId = $findCustomerId->customer_id;

					try {
						$customer = \Stripe\Customer::retrieve($checkCustomerId);

						$customerId = $checkCustomerId;
					} catch (\Exception $e) { }
				}

				// create customer
				if( empty( $customerId ) )
				{
					try
					{
						$customer = \Stripe\Customer::create([
							"source"	=>	$token,
							"email"		=>	Auth::user()->email,
							"metadata"	=>	[
								"user_id" => Auth::user()->id
							]
						]);

						if( !empty( $customer->id ) )
						{
							$customerId = $customer->id;
						}
						else
						{
							return $this->paymentStatus($id , 3 , "Couldn't create the new customer");
						}
					}
					catch (\Exception $e)
					{
						return $this->paymentStatus($id , 3 , "Couldn't create the new customer");
					}
				}

				// save customer id
				Payment::where('id' , $id)->update(['customer_id' => $customerId]);

				// create plan for subscription
				$planId = 'PostPoint_Plan_' . $checkPayment['package_id'] . '_' . round($checkPayment['amount'] * 100) . '_' . $checkPayment['plan'];

				// create plan if not exits
				try
				{
					$plan = \Stripe\Plan::retrieve( $planId );
				}
				catch (\Exception $e)
				{
					try
					{
						$plan = \Stripe\Plan::create([
							"id"		=>	$planId,
							"amount"	=>	round($checkPayment['amount'] * 100),
							"interval"	=>	$checkPayment['plan'] == "annual" ? "year" : "month",
							"product"	=>	[
								"name"	=> $checkPayment['package_name'] . ' - ' . ucfirst( $checkPayment['plan'] )
							],
							"currency"	=>	'USD'
						]);
					}
					catch (\Exception $e)
					{
						return $this->paymentStatus($id , 3 , "Couldn't create the new plan");
					}
				}

				// subscribe...
				try
				{
					$subscription = \Stripe\Subscription::create([
						"customer"		=>	$customerId,
						"items"			=>	[ [ 'plan' => $planId ] ],
						"trial_end"		=>	'now',
						"metadata"		=>	[ 'payment_id'	=>	$id ]
					]);

					if( empty( $subscription->id ) )
					{
						return $this->paymentStatus($id , 3 , 'Couldn\'t create the new subscription');
					}

					return $this->paymentStatus($id , 4 , null , $subscription->id );
				}
				catch (\Exception $e)
				{
					return $this->paymentStatus($id , 3 , $e->getMessage());
				}
			}
		}
		else
		{
			return 'Error! (Err no:00001)';
		}
	}

	public function paymentList()
	{
		$payments = Payment::where('user_id' , Auth::id())->get();
		return view('payment_list' , ['payments' => $payments]);
	}

	private function paymentStatus( $id , $status , $error = null , $subscriptionId = null )
	{
		$isSubscribed = $subscriptionId ? 1 : null;
		Payment::where('id' , $id)->update([
			'status'			=>	$status,
			'error'				=>	$error,
			'subscription_id'	=>	$subscriptionId,
			'is_subscribed'		=>	$isSubscribed
		]);

		return Redirect::route( 'payment_list' )->send();
	}

	public function cancelSubscription( Request $request )
	{
		$validatedData = $request->validate([
			'payment_id'					=> 'required|numeric|min:1'
		]);

		$paymentId = $validatedData['payment_id'];

		$paymentInf = Payment::where('id' , $paymentId)->first();
		if( !$paymentInf )
		{
			return 'Payment not found!';
		}

		if( !$paymentInf->is_subscribed )
		{
			return 'This is not subscription!';
		}

		if( $paymentInf->payment_method == 'stripe' )
		{
			Stripe::cancelSubscription( $paymentInf->subscription_id );

			Payment::where('id' , $paymentId)->update(['is_subscribed' => 0]);
		}
		else
		{
			$paypal = new Paypal();
			$paypal->cancelSubscription( $paymentInf->subscription_id );

			Payment::where('id' , $paymentId)->update(['is_subscribed' => 0 , 'status' => 5]);
		}

		return redirect()->back()->with('success', true);
	}

}
