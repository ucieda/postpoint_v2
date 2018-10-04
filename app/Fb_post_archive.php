<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fb_post_archive extends Model
{

	protected $table = 'fb_post_archive';

	public $timestamps = false;

	public function Fb_account()
	{
		return $this->belongsTo('App\Fb_account');
	}

	public function Fb_account_node()
	{
		return $this->belongsTo('App\Fb_account_node' , 'node_id');
	}

}
