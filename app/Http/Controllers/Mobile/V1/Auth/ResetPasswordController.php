<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Http\Controllers\Mobile\V1\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        //for ajax requests sent from vue.js it can't run
        if (!request()->ajax()) {
            $this->middleware('guest');
        }
    }

    public function apiReset(Request $request)
    {
        try {
            $this->validate($request, $this->rules(), $this->validationErrorMessages());
        } catch (\Throwable $e) {
            response()->json(['error' => $e->getMessage()], 422);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->apiResetPassword($user, $password);
        }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET ?
            response()->json(['success' => $response])
            :
            response()->json(['error' => $response], 500);
    }

    protected function apiResetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

//        $this->guard()->login($user);
    }
}
