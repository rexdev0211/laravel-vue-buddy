<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use \Illuminate\Auth\Middleware\Authenticate as Auth;

class MobileApiSettings extends Auth
{
    public function handle($request, Closure $next, ...$guards)
    {
        config(['app.is_mobile_api' => true]);

        return $next($request);
    }
}