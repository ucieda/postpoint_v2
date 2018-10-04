<?php

namespace App;

use Eloquent;
use Illuminate\Support\Facades\Auth;

class User_role extends Eloquent
{

	public $timestamps = false;

	protected $fillable = ['name'];

	public function User()
	{
		return $this->hasMany('App\User');
	}


	public function checkFbAccountsLimit( $userId = null )
	{
		$userId = $userId ?? Auth::id();
		$limit = $this->max_fb_accounts;

		if( !is_null($limit) && $limit >= 0 )
		{
			$getCurrentCount = (int)Fb_account::where('user_id' , $userId)->count();

			if( $getCurrentCount >= $limit )
			{
				response()->json([
					'status'	=>	'error',
					'error_msg'	=>	'Maksimum ' . $limit . ' FB account əlavə edə bilərsiniz!'
				])->send();
				exit();
			}
		}
	}

	public function checkPostsLimit( $userId = null , $exit = true )
	{
		$userId = $userId ?? Auth::id();
		$limit = $this->max_posts_per_day;

		if( !is_null($limit) && $limit >= 0 )
		{
			$today = date('Y-m-d');

			$getCurrentCount = Post_statistic::where('date' , $today)->where('user_id' , $userId)->first();
			$getCurrentCount = (int)($getCurrentCount->success_count_all ?? 0) + (int)($getCurrentCount->fails_count_all ?? 0);

			if( $getCurrentCount >= $limit )
			{
				if( $exit )
				{
					response()->json([
						'status'	=>	'error',
						'error_msg'	=>	'Gün ərzinfə maksimum ' . $limit . ' post paylaşa bilərsiniz!'
					])->send();
					exit();
				}
				else
				{
					return [
						'status'	=>	'error',
						'error_msg'	=>	'Gün ərzinfə maksimum ' . $limit . ' post paylaşa bilərsiniz!'
					];
				}
			}
		}

		return ['status' =>	'ok'];
	}

}
