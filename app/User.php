<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'username',
		'email',
		'password',
		'user_role_id',
		'expire_on',
		'timezone',
		'language_id',
		'records_per_page',
		'load_my_groups',
		'load_my_pages',
		'load_my_ownpages',
		'max_groups_to_import',
		'max_pages_to_import',
		'show_open_groups_only',
		'unique_post',
		'unique_link',
		'post_interval',
		'fb_account_id',
		'is_admin',
		'status',
		'email_confirmation_token',
		'last_confirmation_email_sended_on'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

	public function User_role()
	{
		return $this->belongsTo('App\User_role');
	}

	public function Language()
	{
		return $this->belongsTo('App\Language');
	}

	public function Fb_account()
	{
		return $this->hasMany('App\Fb_account');
	}

}
