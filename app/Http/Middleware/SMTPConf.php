<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class SMTPConf
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
	    \Config::set('mail.driver' , siteOption('mail_protocol'));
	    \Config::set('mail.host' , siteOption('smtp_host'));
	    \Config::set('mail.port' , siteOption('smtp_port'));
	    \Config::set('mail.username' , siteOption('smtp_user'));
	    \Config::set('mail.password' , siteOption('smtp_pass'));
	    \Config::set('mail.encryption' , siteOption('smtp_encryption'));

        return $next($request);
    }

}
