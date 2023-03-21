<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enum\ProTypes;
use App\Newsletter;
use App\Services\ChatService;
use App\Services\MediaService;
use App\User;
use App\Events\UserEvent;
use App\Http\Controllers\Web\Controller;
use App\Repositories\CountryRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserFavoriteRepository;
use App\Repositories\PhotoRepository;
use App\Repositories\UserRepository;
use App\Services\BackendService;
use Carbon\Carbon;
use Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class UsersController
 * @package App\Http\Controllers\Web\Admin
 */
class UsersController extends Controller
{
    private $userRepository;

    /**
     * UsersController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, CountryRepository $countryRepository)
    {
        $sessionKey  = 'admin.users';

        $page        = (int)Helper::getUserPreference($sessionKey, 'page', 1);
        $perPage     = (int)Helper::getUserPreference($sessionKey, 'perPage', Helper::getDefaultPerPageNumber());
        $orderBy     = Helper::getUserPreference($sessionKey, 'orderBy', 'id');
        $orderBySort = Helper::getUserPreference($sessionKey, 'orderBySort', 'asc');

        $resetForm          = $request->exists('resetFilters');
        $filterName         = Helper::getUserPreference($sessionKey, 'filterName', '', $resetForm);
        $filterBuddyLink    = Helper::getUserPreference($sessionKey, 'filterBuddyLink', '', $resetForm);
        $filterEmail        = Helper::getUserPreference($sessionKey, 'filterEmail', '', $resetForm);
        $filterId           = Helper::getUserPreference($sessionKey, 'filterId', '', $resetForm);
        $filterTrashed      = Helper::getUserPreference($sessionKey, 'filterTrashed', '', $resetForm);
        $filterActivity     = Helper::getUserPreference($sessionKey, 'filterActivity', '', $resetForm);
        $filterCountry      = Helper::getUserPreference($sessionKey, 'filterCountry', '', $resetForm);
        $filterState        = Helper::getUserPreference($sessionKey, 'filterState', '', $resetForm);
        $filterLocality     = Helper::getUserPreference($sessionKey, 'filterLocality', '', $resetForm);
        $filterLanguage     = Helper::getUserPreference($sessionKey, 'filterLanguage', '', $resetForm);
        $filterGroup        = Helper::getUserPreference($sessionKey, 'filterGroup', '', $resetForm);
        $filterRegistration = Helper::getUserPreference($sessionKey, 'filterRegistration', '', $resetForm);

        $users = $this->userRepository->getAllUsersPaginated($page, $perPage, $orderBy, $orderBySort, $filterEmail,
            $filterName, $filterBuddyLink, $filterId, $filterTrashed, $filterActivity, $filterCountry, $filterState, $filterLocality, $filterLanguage,
            $filterRegistration, $filterGroup);

        $countries = $countryRepository->getCountriesList(false, '--- Country ---');

        $languages = [
            ''   => "--- Language ---",
            'en' => "English",
            'de' => "German",
            'fr' => "French",
            'it' => "Italian",
            'nl' => "Dutch",
        ];

        $trashedOptions = [
            'all'                      => '--- Account status ---',
            'only_deleted'             => 'Only deleted accounts',
            'except_deleted'           => 'Except deleted accounts',
            'only_suspended'           => 'Only suspended accounts',
            'except_suspended'         => 'Except suspended accounts',
            'only_ghosted'             => 'Only ghosted accounts',
            'except_ghosted'           => 'Except ghosted accounts',
            'only_deleted_suspended'   => 'Only deleted or suspended or ghosted',
            'except_deleted_suspended' => 'Except deleted and suspended and ghosted',
        ];

        $activityOptions = [
            'all' => '--- Account activity ---',
            'deactivated' => 'Deactivated',
            'dormant' => 'Dormant',
        ];

        $registrationOptions = [
            ''    => '--- Registered via  ---',
            'app' => "App",
            'web' => "Web/PWA"
        ];

        $groups = [
            'all'        => '--- Groups ---',
            'free'       => 'Free',
            'pro_all'    => 'Pro (all)',
            'pro_paid'   => 'Pro (paid)',
            'pro_manual' => 'Pro (manual)',
            'pro_coupon' => 'Pro (coupon)',
            'staff'      => 'Staff',
        ];

        return view('admin.users.index', compact('users', 'sessionKey', 'trashedOptions', 'activityOptions', 'countries', 'languages', 'registrationOptions', 'groups'));
     }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view($id, PhotoRepository $userPhotoRepository, UserFavoriteRepository $userFavoriteRepository,
                         MessageRepository $messageRepository)
    {
        $user = $this->userRepository->findWithTrashedUser($id);
        $user->load('tags');
        $user->computed_status = $user->computedStatus;
        $user->trusted_message_sender = $user->isTrustedMessageSender();

        $photos = $userPhotoRepository->getGalleryPhotosByUserId($user->id);

        $publicPhotos  = $photos->where('visible_to', 'public');
        $privatePhotos = $photos->where('visible_to', 'private');

        $favoritesCount = $userFavoriteRepository->getFavoritesCount($user->id);

        $msgWeek  = $messageRepository->getMessagedUsersCountFromUser($user->id, Carbon::now()->subDays(7));
        $msgMonth = $messageRepository->getMessagedUsersCountFromUser($user->id, Carbon::now()->subDays(30));
        $msgYear  = $messageRepository->getMessagedUsersCountFromUser($user->id, Carbon::now()->subDays(365));

        // $spamService = new \App\Services\SpamService;
        // $spamService->setUser($user);
        // $antispam = $spamService->testSpamService();
        $antispam = null;

        $user->location = mb_convert_encoding($user->location, "utf-8");

        return view('admin.users.view', compact('user', 'publicPhotos', 'privatePhotos', 'favoritesCount',
            'msgWeek', 'msgMonth', 'msgYear', 'antispam'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function softDelete($id, PhotoRepository $userPhotoRepository) {
        $user = $this->userRepository->findUser($id);

        $suffix = '2';

        $mediaService = new MediaService();
        foreach ($user->photos as $photo) {
            $mediaService->addSuffixToPhotoName($photo->photo, 'users', $suffix);
            $userPhotoRepository->updatePhoto($photo->id, ['photo' => $photo->photo . $suffix]);
        }

        //TODO: update user image from messages; or maybe we don't need it, just make redirect for missing images to placeholer
        //we may have situation that user send an image message, then later he deletes it and in result we have same situation

        $this->userRepository->softDeleteUserById($id);

        event(new UserEvent(['user_to' => $id, 'event' => 'delete']));

        return \Response::json('ok', 200);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function hardDelete($id)
    {
        $user = $this->userRepository->findUser($id);

        //delete photos from hard drive
        $mediaService = new MediaService();
        foreach ($user->photos as $photo) {
            $mediaService->deleteUserPhoto($photo->photo);
        }

        $delete = $this->userRepository->deleteById($user->id);

        //save user name in users_deleted
        \DB::table('users_deleted')->insert(['id'=> $user->id, 'name' => $user->name, 'email' => $user->email]);

        if($delete) {
            event(new UserEvent(['user_to' => $user->id, 'event' => 'delete']));

            return response()->json('ok');
        }

        return response()->json('nope', 500);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id) {
        try {
            $this->userRepository->softRestoreUserById($id);
        } catch (\Exception $e) {
            return \Response::json($e->getMessage(), 422);
        }

        return \Response::json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function activate($id): JsonResponse
    {
        $chatService = new ChatService();
        /** @var User $user */
        $user = User::find($id);
        $user->unsuspend();

        $chatService->setGhostedOrActiveMessagesForRecipients($user, User::STATUS_ACTIVE);
        $chatService->setMessageIsSuspendedOrActiveForRecipients($user, User::STATUS_ACTIVE);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function suspend($id): JsonResponse
    {
        /** @var User $user */
        $user = User::find($id);
        $user->suspend();
        event(new UserEvent(['user_to' => $id, 'event' => 'suspend']));
        (new ChatService())->setMessageIsSuspendedOrActiveForRecipients($user, User::STATUS_SUSPENDED);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function whitelist($id): JsonResponse
    {
        /** @var User $user */
        $user = User::find($id);
        $user->addToTrustedSendersList();
        $this->activate($id);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function blacklist($id): JsonResponse
    {
        /** @var User $user */
        $user = User::find($id);
        $user->removeFromTrustedSendersList();
        $this->suspend($id);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function updateBuddyLink($id): JsonResponse
    {
        $buddyLink = request()->get('buddyLink');
        /** @var User $user */
        $user = User::find($id);
        try {
            $user->link = $buddyLink;
            $user->save();
        } catch (\Exception $e) {
            return response()->json('This Buddy Link is already taken', 422);
        }
        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function ghost($id) {
        /** @var User $user */
        $user = User::find($id);
        $user->ghost();
        (new ChatService())->setGhostedOrActiveMessagesForRecipients($user, User::STATUS_GHOSTED);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function upgrade($id) {
        $date = Carbon::parse(request()->get('date'))
            ->setTime(23, 59, 59)
            ->toDateTimeString();

        /** @var User $user */
        $user = User::find($id);
        $user->upgradeToPro($date, ProTypes::MANUAL);

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function updatePassword($id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = User::find($id);
            $user->updatePassword(bcrypt(request()->get('password')));
        } catch (\Exception $e) {
            return response()->json([
                'errorMessage' => $e->getMessage(),
                'request'      => request()->all(),
            ], $e->getCode());
        }

        return response()->json('ok', 200);
    }

    /**
     * @param $id
     *
     * @return JsonResponse
     */
    public function downgrade($id) {
        /** @var User $user */
        $user = User::find($id);
        $user->downgrade();

        return response()->json('ok', 200);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function spammers() {
        $users = $this->userRepository->getProbablySpammers();

        return view('admin.users.spammers', compact('users'));
    }
}
