<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class FileBrowser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
	public function handle($request, Closure $next)
	{
		$uploadDir = 'uploads/user_' . Auth::id();

		if( !is_dir(public_path($uploadDir)) )
		{
			mkdir(public_path($uploadDir));
		}

		\Config::set('elfinder.dir', [$uploadDir]);

		$uploadAllow = [];
		if( Auth::user()->User_role && Auth::user()->User_role->upload_images )
		{
			$uploadAllow[] = 'image/png';
			$uploadAllow[] = 'image/jpg';
			$uploadAllow[] = 'image/jpeg';
			$uploadAllow[] = 'image/gif';
		}
		if( Auth::user()->User_role && Auth::user()->User_role->upload_videos )
		{
			$uploadAllow[] = 'video/mp4';
			$uploadAllow[] = 'video/x-msvideo';
			$uploadAllow[] = 'video/mp4';
			$uploadAllow[] = 'video/mpeg';
			$uploadAllow[] = 'video/3gpp';
			$uploadAllow[] = 'video/quicktime';
			$uploadAllow[] = 'video/ogg';
			$uploadAllow[] = 'video/webm';
		}

		\Config::set('elfinder.root_options.uploadAllow', $uploadAllow);

		return $next($request);
	}
}
