<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{

	public $timestamps = false;

	private static $data = null;

	public static function selectCached()
	{
		if( is_null(self::$data) )
		{
			self::$data = self::first();
		}

		return self::$data;
	}

}
