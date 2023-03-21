<?php

namespace App\Http\Controllers\Mobile\V1;

class DiscreetModeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Change user discreet_mode
     *
     * @return \Illuminate\Http\Response
     */
    public function change()
    {
        $user = auth()->user();

        if ($user->isPro()) {
            $user->discreet_mode = !$user->discreet_mode;

            $user->save();
        } else {
            return response()->json([
                'error' => 'Upgrade to PRO',
            ], 422);
        }

        return [
            'success'       => true,
            'discreet_mode' => $user->discreet_mode,
        ];
    }
}
