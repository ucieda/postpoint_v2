<?php

namespace App\Http\Controllers;

use App\Fb_account_access_token;
use App\Fb_app;
use App\Fb_post;
use App\Fb_post_archive;
use App\Fb_post_image;
use App\Fb_post_schedule_log;
use App\Fb_post_schedule_node;
use App\Lib\FBLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rules\In;

class PublishingController extends Controller
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

	public function allPosts()
	{
		$savedPosts = Fb_post::where('user_id' , Auth::id())->orderBy('id' , 'DESC')->paginate(recordsPerPage());

		return view('publishing.all_posts' , ['savedPosts' => $savedPosts]);
	}

	public function loadCalendarDays(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'start_date'	 => 'required|date',
			'end_date'		 => 'required|date'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$start	= strtotime(Input::post('start_date'));
		$end	= strtotime(Input::post('end_date'));

		$startD	= date('Y-m-d' , $start);
		$endD	= date('Y-m-d' , $end);

		if( $start >= $end )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$getScheduledPosts = Fb_post::
		where('user_id' , Auth::id())
			->where('is_scheduled' , '1')
			->where(function($query) use( $startD , $endD )
			{
				$query->where(function( $query2 ) use ( $startD )
				{
					$query2->where('schedule_start' , '<=' , $startD)->where('schedule_end' , '>=' , $startD);
				})->orWhere(function( $query2 ) use ( $endD )
				{
					$query2->where('schedule_start' , '<=' , $endD)->where('schedule_end' , '>=' , $endD);
				})->orWhere(function( $query2 ) use ( $startD , $endD )
				{
					$query2->where('schedule_start' , '>' , $startD)->where('schedule_end' , '<' , $endD);
				})->orWhereBetween('schedule_start' , [$startD , $endD]);
			})
			->where('schedule_start' , '<=' , date('Y-m-d' , $end))
			->get();

		$events = [];

		$countsPerDay = [];
		$maxLimit = 4;

		foreach( $getScheduledPosts AS $postInf )
		{
			$title			= textShorter($postInf->title , 25) . ' (' . $postInf->schedule_nodes_count . ')';
			$scheduleStart	= strtotime($postInf->schedule_start);
			$scheduleEnd	= strtotime($postInf->schedule_end);

			$frequency = $postInf->schedule_frequency;

			if( $frequency > 0 )
			{
				$from	= $scheduleStart > $start ? $scheduleStart : $start;
				$to		= $scheduleEnd > $end ? $end : $scheduleEnd;

				for( $dTime = $from ; $dTime <= $to ; $dTime += 60*60*24*$frequency )
				{
					if( !isset($countsPerDay[date('Y-m-d' , $dTime)]) )
					{
						$countsPerDay[date('Y-m-d' , $dTime)] = 0;
					}

					$countsPerDay[date('Y-m-d' , $dTime)]++;

					if( $countsPerDay[date('Y-m-d' , $dTime)] <= $maxLimit )
					{
						$events[] = [
							'id'		=>	$postInf->id,
							'title'		=>	$title,
							'start'		=>	date('Y-m-d' , $dTime),
							'url'		=>	url('home/' . $postInf->id),
							'textColor'	=>	'#d35400'
						];
					}
				}
			}
			else
			{
				if( !isset($countsPerDay[date('Y-m-d' , $scheduleStart)]) )
				{
					$countsPerDay[date('Y-m-d' , $scheduleStart)] = 0;
				}

				$countsPerDay[date('Y-m-d' , $scheduleStart)]++;

				if( $countsPerDay[date('Y-m-d' , $scheduleStart)] <= $maxLimit )
				{
					$events[] = [
						'id'		=>	$postInf->id,
						'title'		=>	$title,
						'start'		=>	date('Y-m-d' , $scheduleStart),
						'url'		=>	url('home/' . $postInf->id),
						'textColor'	=>	'#d35400'
					];
				}
			}
		}

		foreach($countsPerDay AS $day => $count)
		{
			if( $count > $maxLimit )
			{
				$events[] = [
					'id'		=>	$postInf->id,
					'title'		=>	"+" . ($count - $maxLimit) . ' more',
					'start'		=>	$day,
					'textColor'	=>	'#d35400'
				];
			}
		}

		return response()->json([
			'status'	=>	'ok',
			'events'	=>	$events
		]);

	}

	public function savedPosts()
	{
		$savedPosts = Fb_post::where('user_id' , Auth::id())->orderBy('id' , 'DESC')->paginate(recordsPerPage());

		return view('publishing.saved_posts' , ['savedPosts' => $savedPosts]);
	}

	public function scheduledPosts()
	{
		$scheduledPosts = Fb_post::
			where('user_id' , Auth::id())
			->where('is_scheduled' , '1')
			->orderBy('id' , 'DESC')
			->paginate(recordsPerPage());

		foreach($scheduledPosts AS $postInfo)
		{
			if( $postInfo['status'] == 1 || $postInfo['schedule_is_paused'] == 1 )
			{
				$postInfo['next_posting_time'] = '-';

				if( $postInfo['status'] != 1 )
				{
					$postInfo['remaining'] = Fb_post_schedule_log::where('fb_post_id' , $postInfo->id)->count();
				}
			}
			else
			{
				$postInfo['remaining'] = Fb_post_schedule_log::where('fb_post_id' , $postInfo->id)->count();

				$getNPT = Fb_post_schedule_log::where('fb_post_id' , $postInfo->id)->limit(1)->first();
				if( !$getNPT )
				{
					$postInfo['next_posting_time'] = date(dateFormat() , strtotime( '+' . $postInfo['schedule_frequency'] . ' day' , strtotime($postInfo['schedule_last_inserted_date']) )) .
					' ' . date('H:i' , strtotime( $postInfo['schedule_start'] ));
				}
				else
				{
					$postInfo['next_posting_time'] = isset($getNPT['run_time']) ? date(dateFormat().' H:i' , strtotime($getNPT['run_time'])) : '-';
				}
			}
		}

		return view('publishing.scheduled_posts' , ['scheduledPosts' => $scheduledPosts]);
	}

	public function scheduleLogs( $id = 0 )
	{
		if( $id > 0 )
		{
			$logs = Fb_post_archive::where('is_scheduled_post' , '1')
				->where('user_id' , Auth::id())
				->where('post_id' , $id)
				->orderBy('id' , 'DESC')
				->paginate(recordsPerPage());
		}
		else
		{
			$logs = Fb_post_archive::where('is_scheduled_post' , '1')
				->where('user_id' , Auth::id())
				->orderBy('id' , 'DESC')
				->paginate(recordsPerPage());
		}

		return view('publishing.schedule_logs' , ['logs' => $logs , 'postId' => $id]);
	}

	public function scheduledPostEdit( $id )
	{
		$postInf = Fb_post::where('id' , $id)->first();

		$accountId = Auth::user()->fb_account_id;

		$fbApps = [];
		$registredApps = Fb_account_access_token::where('fb_account_id' , $accountId)->select('id', 'app_id')->get();
		foreach ( $registredApps AS $appInf )
		{
			$appInf2 = Fb_app::where('id' , $appInf['app_id'])->first();

			if( !$appInf2 ) continue;

			$fbApps[] = [
				'id'	=>	$appInf['id'],
				'name'	=>	$appInf2['name']
			];
		}

		return viewModal('publishing.edit' , 'Edit' , [
			'postInf' 	=>	$postInf,
			'fbApps'	=>	$fbApps,
			'postId'	=>	$id
		]);
	}

	public function postReport( $id )
	{

		$postInf = Fb_post_archive::where('id' , $id)->where('user_id' , Auth::id())->first();
		if( !$postInf )
		{
			return 'Status not found!';
		}

		if( $postInf->Fb_account_node && $postInf->Fb_account_node->node_type == 'own_page' )
		{
			$accessToken = $postInf->Fb_account_node->access_token;
		}
		else
		{
			$accessToken = Fb_account_access_token::where('fb_account_id' , $postInf->fb_account_id)->where('app_id' , $postInf->app_id)->first();
			$accessToken = $accessToken ? $accessToken->access_token : '';
		}

		$feedId = ($postInf->Fb_account_node ? $postInf->Fb_account_node->node_id : '') . '_' . $postInf->fb_feed_id;

		$insights = FBLib::cmd('/'.$feedId , 'GET' , $accessToken , [
			'fields'	=>	'reactions.type(LIKE).limit(0).summary(total_count).as(like),reactions.type(LOVE).summary(total_count).limit(0).as(love),reactions.type(WOW).summary(total_count).limit(0).as(wow),reactions.type(HAHA).summary(total_count).limit(0).as(haha),reactions.type(SAD).summary(total_count).limit(0).as(sad),reactions.type(ANGRY).summary(total_count).limit(0).as(angry),comments.limit(0).summary(true),sharedposts.limit(5000).summary(true)
'
		]);

		$insights = [
			'like'		=>	$insights['like']['summary']['total_count'] ?? 0,
			'love'		=>	$insights['love']['summary']['total_count'] ?? 0,
			'wow'		=>	$insights['wow']['summary']['total_count'] ?? 0,
			'haha'		=>	$insights['haha']['summary']['total_count'] ?? 0,
			'sad'		=>	$insights['sad']['summary']['total_count'] ?? 0,
			'angry'		=>	$insights['angry']['summary']['total_count'] ?? 0,
			'comments'	=>	$insights['comments']['count'] ?? 0,
			'shares'	=>	$insights['sharedposts']['count'] ?? 0
		];

		return viewModal('publishing.report' , 'Report' , [
			'insights'	=>	$insights
		]);
	}

	public function clearLogs(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'clear'	 	=>	'required|numeric',
			'post_id'	=>	'required|numeric'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$postId = (int)Input::post('post_id');

		if( $postId > 0 )
		{
			Fb_post_archive::where('user_id' , Auth::id())->delete();
		}
		else
		{
			Fb_post_archive::where('post_id' , $postId)->where('user_id' , Auth::id())->delete();
		}

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function deleteSavedPosts(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'posts.*'	 => 'required|numeric|min:0'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$posts	= (array)Input::post('posts');

		$checkPost = Fb_post::whereIn('id' , $posts)->where('user_id' , '<>' , Auth::id())->count();
		if( $checkPost > 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		Fb_post::whereIn('id' , $posts)->delete();
		Fb_post_image::whereIn('fb_post_id' , $posts)->delete();
		Fb_post_schedule_log::whereIn('fb_post_id' , $posts)->delete();
		Fb_post_schedule_node::whereIn('fb_post_id' , $posts)->delete();

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function deleteScheduledPosts(Request $request)
	{
		$validatedData = \Validator::make($request->all() , [
			'posts.*'	 => 'required|numeric|min:0'
		]);

		if ( !$validatedData->passes() )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		$posts	= (array)Input::post('posts');

		$checkPost = Fb_post::whereIn('id' , $posts)->where('user_id' , '<>' , Auth::id())->count();
		if( $checkPost > 0 )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  __('general.validation_error')
			]);
		}

		Fb_post::whereIn('id' , $posts)->update([
			'is_scheduled'					=>	'0',
			'schedule_post_interval'		=>	null,
			'schedule_start'				=>	null,
			'schedule_fb_app_id'			=>	null,
			'schedule_auto_pause'			=>	null,
			'schedule_auto_resume'			=>	null,
			'schedule_frequency'			=>	null,
			'schedule_end'					=>	null,
			'schedule_fb_account_id'		=>	null,
			'schedule_last_inserted_date'	=>	null
		]);

		Fb_post_schedule_log::whereIn('fb_post_id' , $posts)->delete();
		Fb_post_schedule_node::whereIn('fb_post_id' , $posts)->delete();

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function scheduledPostsResume(Request $request)
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

		$postId = (int)Input::post('id');

		$postInfo = Fb_post::where('id', $postId)->where('user_id' , Auth::id())->first();

		if( !$postInfo )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  'Post not found!'
			]);
		}

		$today = date('Y-m-d');

		// resume post
		Fb_post::where('id' , $postId)->update([
			'schedule_is_paused'		=>	'0',
			'schedule_auto_resume_time'	=>	null
		]);

		// delete old jobs
		Fb_post_schedule_log::where('fb_post_id' , $postId)->where('run_time' , '<' , $today)->delete();

		app( ScheduleController::class )->insertSchedules( $postId );

		// resume jobs
		Fb_post_schedule_log::where('fb_post_id' , $postId)->update([
			'is_paused'	=>	'0'
		]);

		return response()->json([
			'status'    =>  'ok'
		]);
	}

	public function scheduledPostsPause(Request $request)
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

		$postId = (int)Input::post('id');

		$postInfo = Fb_post::where('id', $postId)->where('user_id' , Auth::id())->first();

		if( !$postInfo )
		{
			return response()->json([
				'status'    =>  'error',
				'error_msg' =>  'Post not found!'
			]);
		}

		// pause post
		Fb_post::where('id' , $postId)->update([
			'schedule_is_paused'	=>	'1'
		]);

		// pause jobs
		Fb_post_schedule_log::where('fb_post_id' , $postId)->update([
			'is_paused'	=>	'1'
		]);

		return response()->json([
			'status'    =>  'ok'
		]);
	}

}
