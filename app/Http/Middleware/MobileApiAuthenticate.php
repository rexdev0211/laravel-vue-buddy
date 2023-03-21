<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use \Illuminate\Auth\Middleware\Authenticate as Auth;

class MobileApiAuthenticate extends Auth
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            return response(['error' => $e->getMessage()], 401);
        }
        return $next($request);
    }
}