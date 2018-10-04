<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fb_post_image extends Model
{

	public $timestamps = true;

	public function Fb_post()
	{
		return $this->belongsTo('App\Fb_post');
	}

}
