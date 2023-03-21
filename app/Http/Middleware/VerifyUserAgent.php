<?php

namespace App\Http\Middleware;

use Closure;

class VerifyUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $platform
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userAgent = $request->userAgent();
        $userAgentAllowed = config('platforms.user_agent', []);

        if (!in_array($userAgent, $userAgentAllowed)) {
            abort(404);
        }

        return $next($request);
    }
}
