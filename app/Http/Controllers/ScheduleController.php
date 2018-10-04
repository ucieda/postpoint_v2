<?php

namespace App\Http\Controllers;

use App\Fb_account;
use App\Fb_account_access_token;
use App\Fb_account_node;
use App\Fb_post;
use App\Fb_post_archive;
use App\Fb_post_schedule_log;
use App\Fb_post_schedule_node;
use App\Lib\FBLib;
use App\Notification;
use App\Post_statistic;
use App\User;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
	private $posts			= [];
	private $postsCount		= [];
	private $pausedPosts	= [];
	private $sendedPosts	= [];
	private $usersInfo		= [];

	public function insertSchedules( $id = null )
	{
		$todayT = date('Y-m-d H:i');
		$todayD = date('Y-m-d');

		$getPosts = Fb_post::where('is_scheduled' , '1')
			->where(function($query) use( $todayD )
			{
				$query
					->where(DB::raw('DATE_ADD(`schedule_last_inserted_date` , INTERVAL `schedule_frequency` DAY)') , '<=' , $todayD)
					->orWhereNull('schedule_last_inserted_date');
			})
			->where(DB::raw('CAST(schedule_start AS DATE)') , '<=' , $todayT)
			->where('schedule_is_paused' , '0')
			->where(function( $query ) use( $todayT )
			{
				$query->where('schedule_end' , '>=' , $todayT)->orWhereNull('schedule_last_inserted_date');
			});

		if( $id > 0 )
		{
			$getPosts->where('id' , $id);
		}

		$getPosts = $getPosts->get();

		// first update
		$ids = [];
		foreach($getPosts AS $postInfo)
		{
			$ids[] = $postInfo->id;
		}
		Fb_post::whereIn('id' , $ids)->update(['schedule_last_inserted_date' => $todayD]);

		//
		foreach($getPosts AS $postInfo)
		{
			$postId		= $postInfo->id;
			$lastTime	= $todayD . ' ' . date('H:i' , strtotime($postInfo->schedule_start));
			$spInterval	= (int)$postInfo->schedule_post_interval * 60;

			foreach( $postInfo->Fb_post_schedule_node AS $nodeInf )
			{
				Fb_post_schedule_log::insert([
					'fb_post_id'	=>	$postId,
					'node_id'		=>	$nodeInf->node_id,
					'run_time'		=>	$lastTime
				]);

				$lastTime = date('Y-m-d H:i' , ( strtotime($lastTime) + $spInterval + (int)siteOption('schedule_random_interval') ) );
			}
		}
	}

	public function sendPosts()
	{
		$posts = Fb_post_schedule_log::/*where('run_time' , '<=' , $now)->*/where('is_paused' , '0')->get();

		// send post
		foreach($posts AS $postInf1)
		{
			if( isset($this->pausedPosts[$postInf1->fb_post_id]) )
				continue;

			// ola biler gecikme bash verib, 2-ci cron artiq bu postu paylashib arada...
			if( ! Fb_post_schedule_log::where('id' , $postInf1->id)->first() )
				continue;

			$postInfo = $this->getPostInfo($postInf1->fb_post_id);

			$userInfo = $this->getUserInfo($postInfo['user_id']);

			\Config::set('app.timezone' , $userInfo->timezone);
			date_default_timezone_set($userInfo->timezone);

			if( strtotime($postInf1['run_time']) > time() )
			{
				continue;
			}

			Fb_post_schedule_log::where('id' , $postInf1->id)->delete( );

			$accessToken	=	$postInfo['access_token'];
			$link			=	$postInfo['link'];
			$message		=	$postInfo['message'];

			if( $postInf1->node_id == -1 )
			{
				$fbAccInf = Fb_account::where('id' , $postInfo['fb_account_id'])->first();
				$nodeInfo = [ 'node_id'	=> $fbAccInf->fb_account_id ];
			}
			else
			{
				$nodeInfo = Fb_account_node::where('id' , $postInf1->node_id)->select('node_type','node_id','access_token')->first();
				if( $nodeInfo['node_type'] == 'ownpage' )
				{
					$accessToken = $nodeInfo->access_token;
				}
			}

			if( $postInfo['post_type'] == 'link' )
			{
				$link_picture		= (string)$postInfo['link_picture'];
				$link_caption		= (string)$postInfo['link_caption'];
				$link_title			= (string)$postInfo['link_title'];
				$link_description	= (string)$postInfo['link_description'];

				if( !empty($link_picture) || !empty($link_caption) || !empty($link_title) || !empty($link_description) )
				{
					$customLink = url('custom_link') . '/?url=' . urlencode($link);
					$customLink .= '&picture=' . urlencode($link_picture);
					$customLink .= '&caption=' . urlencode($link_caption);
					$customLink .= '&title=' . urlencode($link_title);
					$customLink .= '&description=' . urlencode($link_description);

					$link = $customLink;
				}
			}

			if( $userInfo->unique_post && !empty($message) )
			{
				$message .= "\n\n" . uniqid( );
			}
			if( $userInfo->unique_link && !empty($link) )
			{
				$link .= ( strpos($link , '?') === false ? '?' : '&' ) . '_random=' . uniqid();
			}

			if( siteOption('enable_instant_post') && !empty($postInfo['product_name']) && !empty($postInfo['product_price']) )
			{
				$message = $postInfo['product_name'] . " for sale \nPrice : " . $postInfo['product_price'] . "\n" . $message;
			}

			// check post limit for per day
			$result1 = $userInfo->User_role ? $userInfo->User_role->checkPostsLimit( $postInfo['user_id'] , false ) : ['status' => 'ok'];

			if( $result1['status'] == 'error' )
			{
				$result = $result1;
			}
			else
			{
				$result = FBLib::sendPost(
					$nodeInfo['node_id'] ,
					$postInfo['post_type'] ,
					$message ,
					$postInfo['preset_id'] ,
					$link ,
					$postInfo['images'] ,
					$postInfo['video'] ,
					$accessToken
				);

				Post_statistic::addStatistic( ( $result['status'] == 'error' ? 'fail' : 'success' ) , true , $postInfo['user_id'] );
			}

			// insert archive
			Fb_post_archive::insert([
				'user_id'			=>	$postInfo['user_id'],
				'fb_account_id'		=>	$postInfo['fb_account_id'],
				'app_id'			=>	$postInfo['app_id'],
				'node_id'			=>	$postInf1->node_id,
				'status'			=>	$result['status'],
				'error_message'		=>	$result['status'] == 'error' ? $result['error_msg'] : null,
				'time'				=>	date('Y-m-d H:i:s'),
				'fb_feed_id'		=>	$result['id'] ?? null,
				'post_id'			=>	$postInf1->fb_post_id,
				'post_type'			=>	$postInfo['post_type'],
				'is_scheduled_post'	=>	'1'
			]);

			if( $result['status'] == 'error' )
			{
				Notification::insert([
					'user_id'	=>	$postInfo['user_id'],
					'text'		=>	$result['error_msg'],
					'time'		=>	date('Y-m-d H:i:s'),
					'post_id'	=>	$postInf1->fb_post_id,
					'title'		=>	'Scheduled post error'

				]);
			}

			$this->sendedPosts[ $postInf1->fb_post_id ] = true;

			if( $postInfo->schedule_auto_pause > 0 )
			{
				$sendedPosts = $this->countOfSendedPosts( $postInf1->fb_post_id );

				if( $sendedPosts % $postInfo->schedule_auto_pause == 0 )
				{
					// pause post
					$autoResumeTime = date('Y-m-d H:i' , time() + $postInfo->schedule_auto_resume * 60 );
					Fb_post::where('id' , $postInf1->fb_post_id)->update([
						'schedule_is_paused' 		=> '1',
						'schedule_auto_resume_time'	=>	$autoResumeTime
					]);

					Fb_post_schedule_log::where('fb_post_id' , $postInf1->fb_post_id)->update(['is_paused' => 1]);

					$this->pausedPosts[$postInf1->fb_post_id] = 1;

					Notification::insert([
						'user_id'	=>	$postInfo['user_id'],
						'text'		=>	'A schedule automaticly has been paused.',
						'time'		=>	date('Y-m-d H:i:s'),
						'post_id'	=>	$postInf1->fb_post_id,
						'title'		=>	'A Schedule has been paused'
					]);
				}
			}
		}
	}

	public function autoResumePost()
	{
		$now = date('Y-m-d H:i');
		$posts = Fb_post::where('schedule_is_paused' , '1')->where('schedule_auto_resume_time' , '<=' , $now)->get();

		foreach( $posts AS $postInfo )
		{
			$postId = $postInfo->id;

			Fb_post::where('id' , $postId)->update([
				'schedule_is_paused' 		=> '0',
				'schedule_auto_resume_time'	=>	null
			]);

			Notification::insert([
				'user_id'	=>	$postInfo['user_id'],
				'text'		=>	'A schedule automaticly has been resumed.',
				'time'		=>	date('Y-m-d H:i:s'),
				'post_id'	=>	$postId,
				'title'		=>	'A Schedule has been resumed'
			]);

			Fb_post_schedule_log::where('fb_post_id' , $postId)->update(['is_paused' => 0]);
		}
	}

	private function getPostInfo($id)
	{
		if( !isset( $this->posts[ $id ] ) )
		{
			$this->posts[ $id ] = Fb_post::where('id' , $id)->first();
			if( $this->posts[ $id ]['post_type'] == 'image' )
			{
				$images = [];
				foreach($this->posts[ $id ]->Fb_post_image AS $imageInf)
				{
					$images[] = $imageInf->image;
				}

				$this->posts[ $id ]['images'] = $images;
			}
			$acInf = Fb_account_access_token::where('id' , $this->posts[ $id ]->schedule_fb_app_id)->first();

			$this->posts[ $id ]['access_token'] = $acInf ? $acInf->access_token : '';

			$this->posts[ $id ]['fb_account_id'] = $acInf ? $acInf->fb_account_id : '';
			$this->posts[ $id ]['app_id'] = $acInf ? $acInf->app_id : '';
		}

		return $this->posts[ $id ];
	}

	private function countOfSendedPosts( $id )
	{
		if( !isset($postsCount[$id]) )
		{
			$postsCount[$id] = Fb_post_archive::where('post_id' , $id)->count();
		}

		return $postsCount[$id]++;
	}

	private function getUserInfo($id)
	{
		if( !isset($this->usersInfo[$id]) )
		{
			$this->usersInfo[$id] = User::where('id' , $id)->first();
		}

		return $this->usersInfo[$id];
	}

	public function __destruct()
	{
		foreach( $this->sendedPosts AS $postId => $true )
		{
			$checkCount = Fb_post_schedule_log::where('fb_post_id' , $postId)->count();

			if( $checkCount > 0 )
				continue;

			$updateStatus = 0;

			$postInf = $this->getPostInfo( $postId );
			if( $postInf->schedule_frequency == 0 )
			{
				$updateStatus = 1;
			}
			else
			{
				$nextInsertDate = strtotime($postInf->schedule_last_inserted_date) + $postInf->schedule_frequency * 60 * 60 * 24;
				$scheduleEnd	= strtotime($postInf->schedule_end);
				if( $nextInsertDate >= $scheduleEnd )
				{
					$updateStatus = 1;
				}
			}

			if( $updateStatus == 1 )
			{
				Fb_post::where( 'id' , $postId )->update( [
					'status'					=>	'1' ,
					'schedule_is_paused' 		=> '0',
					'schedule_auto_resume_time'	=>	null
				] );

				Notification::insert([
					'user_id'	=>	$postInf['user_id'],
					'text'		=>	'A schedule compleated.',
					'time'		=>	date('Y-m-d H:i:s'),
					'post_id'	=>	$postId,
					'title'		=>	'A Schedule compleated'
				]);
			}
		}
	}

}
