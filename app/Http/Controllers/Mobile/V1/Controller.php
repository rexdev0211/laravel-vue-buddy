<?php

namespace App\Http\Controllers\Mobile\V1;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function appSwitch()
    {
        $path = request()->path();
        if ($path == '/') {
            return view('spa_new');
        }

        return view('spa');
    }

    /**
     * Entry Point into the APP
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function app()
    {
        $spaNewPaths = ['register'];

        $userAgent = strtolower(request()->server("HTTP_USER_AGENT"));
        $isMobile = is_numeric(strpos($userAgent, "mobile"));

        if (request()->is('api/v*')) {
            return response(['success' => false], 404);
        } else if (!$isMobile && in_array(request()->path(), $spaNewPaths, true)) {
            return view('spa_new');
        } else {
            return view('spa');
        }
    }

    /**
     * Entry Point into the Rush App
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rush()
    {
        if (request()->is('api/v*')) {
            return response(['success' => false], 404);
        } else {
            return view('rush');
        }
    }
}
