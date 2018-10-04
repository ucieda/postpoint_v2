<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fb_account_access_token extends Model
{

	public $timestamps = false;

	public function Fb_app()
	{
		return $this->belongsTo('App\Fb_app' , 'app_id' );
	}


}
