<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fb_post_schedule_log extends Model
{

	public $timestamps = false;

	public function Fb_post()
	{
		return $this->belongsTo('App\Fb_post');
	}
}
