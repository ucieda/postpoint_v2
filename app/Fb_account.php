<?php

namespace App;

use Eloquent;

class Fb_account extends Eloquent
{

	public $timestamps = false;

	public function User()
	{
		return $this->belongsTo('App\User');
	}

}
