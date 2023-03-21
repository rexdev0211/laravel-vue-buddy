<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;

class LogRequests
{
    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param \Closure $next
     *
     * @return void
     */
    public function handle($request, \Closure  $next)
    {
        return $next($request);
    }

    /**
     * Terminate
     *
     * @param [type] $request
     * @param [type] $response
     *
     * @return void
     */
    public function terminate($request, $response)
    {
        if (auth()->user() && auth()->user()->id == 248104) {
            Log::info(
                "-----------START REQUEST DEBUG-----------\n" .
                "Request URI: /" . $request->path() . "\n" .
                "Request content:\n" . json_encode($request->all()) . "\n" .
                "Request headers:\n" . $request->headers . "\n" .
                "Response content:\n" . $response->getContent() . "\n" .
                "Response headers:\n" . $response->headers .  "\n" .
                "-----------END REQUEST DEBUG-----------"
            );
        }
    }
}
