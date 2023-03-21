<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Http\Controllers\Mobile\V1\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function logout()
    {
        /** @var User $user */
        $user = auth()->user();

        $token = $user->token();
        $result = $token->revoke();
        if ($result) {
            return response()->json('ok', 200);
        } else {
            return response()->json([
                'error' => 'Unknown error'
            ], 500);
        }
    }
}
