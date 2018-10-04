<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Post_statistic extends Model
{

	public $timestamps = false;

	public static function addStatistic( $type , $scheduledPost = false , $userId = null )
	{
		$userId = $userId ?? Auth::id();

		$today = date('Y-m-d');

		$type = $type == 'fail' ? 'fails' : 'success';

		if( $scheduledPost )
		{
			$affectedRows = self::where('user_id' , $userId)->where('date' , $today)->update([
				'scheduled_'.$type.'_count'		=>	DB::raw('scheduled_'.$type.'_count+1'),
				$type.'_count_all'				=>	DB::raw($type.'_count_all+1')
			]);
		}
		else
		{
			$affectedRows = self::where('user_id' , $userId)->where('date' , $today)->update([
				$type.'_count_all'				=>	DB::raw($type.'_count_all+1')
			]);
		}

		if(!$affectedRows)
		{
			self::insert([
				'date'						=>	$today,
				'user_id'					=>	$userId,
				'scheduled_success_count'	=>	$scheduledPost && $type == 'success' ? 1 : 0,
				'scheduled_fails_count'		=>	$scheduledPost && $type == 'fails' ? 1 : 0,
				'success_count_all'			=>	$type == 'success' ? 1 : 0,
				'fails_count_all'			=>	$type == 'fails' ? 1 : 0
			]);
		}

	}



}
