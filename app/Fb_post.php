<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fb_post extends Model
{

	public $timestamps = true;


	public function Fb_post_image()
	{
		return $this->hasMany('App\Fb_post_image');
	}

	public function Fb_post_schedule_node()
	{
		return $this->hasMany('App\Fb_post_schedule_node');
	}

	public function Fb_account_access_token()
	{
		return $this->belongsTo('App\Fb_account_access_token' , 'schedule_fb_app_id');
	}

	public function Fb_account()
	{
		return $this->belongsTo('App\Fb_account' , 'schedule_fb_account_id');
	}

}
