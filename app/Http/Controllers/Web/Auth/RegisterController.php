<?php

namespace App\Http\Controllers\Web\Auth;

use App\Message;
use App\Repositories\CountryRepository;
use App\Repositories\MessageRepository;
use App\Repositories\NewsletterRepository;
use App\Repositories\UserRepository;
use App\Services\ChatService;
use App\Services\EmailService;
use App\Services\HelperService;
use App\Services\SpamService;
use App\User;
use App\Http\Controllers\Web\Controller;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use App\Services\MediaService;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
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

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function apiValidateNameDob(Request $request) {
        $data = $request->only('name', 'dob');

        $beforeDate = Carbon::today()->subYears(config('const.MINIMUM_AGE'))->format('Y-m-d');

        $validator = Validator::make($data, [
            'name' => 'required|max:255|unique:users',
            'dob' => 'required|date_format:"Y-m-d"|before_or_equal:"'.$beforeDate.'"',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        return response()->json('ok');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function apiValidateEmailPassDob(Request $request): JsonResponse
    {
        $data = $request->only('email', 'password', 'dob');

        $beforeDate = Carbon::today()->subYears(config('const.MINIMUM_AGE'))->format('Y-m-d');

        $validator = Validator::make($data, [
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'dob' => 'required|date_format:"Y-m-d"|before_or_equal:"'.$beforeDate.'"',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        return response()->json('ok');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function apiValidateAndRegister(
        Request $request,
        HelperService $helperService,
        EmailService $emailService,
        MessageRepository $messageRepository,
        CountryRepository $countryRepository,
        NewsletterRepository $newsletterRepository,
        UserRepository $userRepository
    )
    {
        $userLanguage = $request->get('lang', 'en');
        $beforeDate = Carbon::today()->subYears(config('const.MINIMUM_AGE'))->format('Y-m-d');
        $verifyData = $request->all();
        $captchaSuccess = null;

         if ($request->has('recaptcha')) {
             $captchaSuccess = $helperService->verifyCaptchaCode($request->get('recaptcha'));
         }

        $rules = [
            'name' => 'required|max:255|unique:users',
            'dob' => 'required|date_format:"Y-m-d"|before_or_equal:"'.$beforeDate.'"',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'photo' => 'imageable',
            'location_type' => 'in:manual,automatic',
            'address' => 'string',
            'lat' => 'numeric',
            'lng' => 'numeric',
            'terms' => 'accepted',
        ];

        //need to put validations in reverse order
        $validator = Validator::make($verifyData, $rules);
        if ($validator->fails()) {
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->first()
                ], 422);
            }
        }

        $chatService = new ChatService();

        $lat = $request->get('lat', 0);
        $lng = $request->get('lng', 0);

        $gpsGeom = \Helper::getGpsGeom($lng, $lat);
        $countryNameRequest = $request->get('country', '');
        $countryCodeRequest = $request->get('country_code', '');
        $countryName = $countryRepository->getCountryNameByCode($countryCodeRequest, $countryNameRequest);

        $userData = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'dob' => $request->get('dob'),
            'address' => $request->get('address'),
            'lat' => $lat,
            'lng' => $lng,
            'address_lat' => $lat,
            'address_lng' => $lng,
            'gps_geom' => $gpsGeom,
            'location' => new Point($lat, $lng, 4326),	// (lat, lng)
            'location_type' => $request->get('location_type', 'manual'),
            'locality' => $request->get('locality', ''),
            'state' => $request->get('state', ''),
            'country' => $countryName,
            'country_code' => $countryCodeRequest,
            'language' => $userLanguage,
            'subscribed' => 'yes',
            'has_new_messages' => true,
            'notification_sound' => 'yes',
            'registered_via' => 'web',
            'ip' => \Helper::getUserIpFromRequest(),
            'fingerprint' => request()->header('User-Agent'),
            'honeypot' => $request->get('honeypot', ''),
            'map_type' => $request->get('location_type', 'none'),
            'app_view_sensitive_events' => 0,
            'app_view_sensitive_media' => 0
        ];

        // if (!$captchaSuccess) {
        //     $userData['status'] = User::STATUS_GHOSTED;
        // }

        /** @var User $user */
        $user = $userRepository->createUser($userData);

        if ($request->hasFile('photo')) {
            $actions = json_decode($request->get('actions'), true);
            (new MediaService())->uploadGalleryPhoto($user, $request->file('photo'), true, array_get($actions, 'rotation', 0), array_get($actions, 'crop', []));
        }

        try {
            $emailService->sendUserRegistrationEmail(
                $user->email,
                $userData['password'],
                $user->name,
                $userLanguage
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send registration email', ['error' => $e->getMessage()]);
        }

        // THIS FUNCTIONALITY IS DEPRECATED !
//        try {
//            $bbUserId = (int)config('const.BB_USER_ID');
//
//            /** @var User $interlocutor */
//            $interlocutor = User::find($bbUserId)->first();
//
//            $message = $messageRepository->createMessage([
//                            'user_from' => $bbUserId,
//                            'user_to' => $user->id,
//                            'message' => trans('message.hello', [], $userLanguage),
//                            'msg_type' => Message::TYPE_TEXT,
//                            'channel' => Message::CHANNEL_USER,
//                            'is_bulk' => 1
//                        ]);
//
//            $conversation = $chatService->getConversationGeneralAttributes($interlocutor, $user, $message, 1);
//
//            $chatService->updateConversationsMessages($user, $interlocutor, Message::CHANNEL_USER, $conversation);
//        } catch (\Exception $e) {
//            return response()->json([
//                'error' => $e->getMessage()
//            ], 500);
//        }

        //send newsletter
        $newsletterRepository->createOrUpdateUserNewsletter($user);

        $user->refresh();

        $spamService = new SpamService;
        $spamService->setScope(SpamService::SCOPE_REGISTRATION);
        $spamService->setUser($user);
        $spamService->userGhostAttempt();

        return response()->json('ok');
    }
}
