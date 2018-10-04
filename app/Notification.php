<?php

namespace App;

use Eloquent;
use Illuminate\Support\Facades\Auth;

class Notification extends Eloquent
{

	public $timestamps = false;

	public static function my($limit = 10)
	{
		return self::where('user_id' , Auth::id())->limit($limit)->where('status' , '0')->orderBy('id' , 'DESC')->get();
	}

	public static function myCount()
	{
		return self::where('user_id' , Auth::id())->where('status' , '0')->count();
	}

}
