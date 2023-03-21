<?php

use App\Http\Controllers\Mobile\V1\ChatController;
use App\Http\Controllers\Mobile\V1\EventController;
use App\Http\Controllers\Mobile\V1\ClubController;
use App\Http\Controllers\Mobile\V1\SharingController;
use App\Http\Controllers\Mobile\V1\UserController;
use App\Http\Controllers\Mobile\V1\DiscreetModeController;
use App\Http\Controllers\Mobile\V1\DiscoverController;
use App\Http\Controllers\Mobile\V1\PrivateController;
use App\Http\Controllers\Mobile\V1\VideoController;
use App\Http\Controllers\Mobile\V1\NotificationsController;
use App\Http\Controllers\Mobile\V1\SubscriptionController;
use App\Http\Controllers\Mobile\V1\RushController;

use App\Http\Controllers\Web\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Auth\RegisterController;
use App\Http\Controllers\Web\PublicController;

Route::pattern('id', '[0-9]+');
Route::pattern('userId', '[0-9]+');
Route::pattern('link', '[A-Za-z0-9_\-\.]+');
Route::pattern('eventId', '[0-9]+');
Route::pattern('photoId', '[0-9]+');
Route::pattern('newFavorite', '[0-9]+');
Route::pattern('slug', '[A-Za-z0-9_\-]+');
Route::pattern('lang', '[a-z]{2}');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

//middleware web is required for storing sessions when doing ajax requests
//however it generates a new session at every vue.js request and so csrf_token is being regenerated
//what to do when we will need to use session and we'll have a POST request
//but we need to maintain same csrf token? need to investigate

Route::post('/payments/apple/webhook', [SubscriptionController::class, 'handleApplePostback']);
Route::post('/payments/google/webhook', [SubscriptionController::class, 'handleGooglePostback']);

Route::middleware(['auth:api'])->group(function() {
    // ##########################################################
    // Affecting chat
    // ##########################################################
    // Get chat dialogs by page and message group
    Route::get('/getConversations', [ChatController::class, 'getConversations']);
    Route::get('/getMessages/{userId}', [ChatController::class, 'getMessages']);
    Route::get('/getEventMessages/{eventId}/{userId}', [ChatController::class, 'getEventMessages']);
    Route::get('/getGroupMessages/{eventId}', [ChatController::class, 'getGroupMessages']);

    Route::get('/getChatImagesList/{userId}', [ChatController::class, 'getChatImagesList']);
    Route::get('/getChatVideosList/{userId}', [ChatController::class, 'getChatVideosList']);
    Route::get('/getEventChatImagesList/{eventId}/{userId}', [ChatController::class, 'getEventChatImagesList']);
    Route::get('/getEventChatVideosList/{eventId}/{userId}', [ChatController::class, 'getEventChatVideosList']);
    Route::get('/getGroupChatImagesList/{eventId}', [ChatController::class, 'getGroupEventChatImagesList']);
    Route::get('/getGroupChatVideosList/{eventId}', [ChatController::class, 'getGroupEventChatVideosList']);

    Route::post('/markConversationAsRead/{userId}', [ChatController::class, 'markConversationAsRead']);
    Route::post('/markEventConversationAsRead/{eventId}/{userId}', [ChatController::class, 'markEventConversationAsRead']);

    Route::post('/sendMessage', [ChatController::class, 'sendMessage']);
    Route::post('/sendMessages', [ChatController::class, 'sendMessages']);

    Route::post('/removeConversation/{userId}', [ChatController::class, 'removeConversation']);
    Route::post('/removeEventConversation/{eventId}/{userId}', [ChatController::class, 'removeEventConversation']);

    Route::post('/markMessageAsRemoved', [ChatController::class, 'markMessageAsRemoved']);

    // ##########################################################
    // Events
    // ##########################################################
    Route::post('/stickyEvents', [EventController::class, 'stickyEvents']);
    Route::post('/eventsAround', [EventController::class, 'eventsAround']);
    Route::get('/eventInfo/{eventId}', [EventController::class, 'eventInfo']);
    Route::post('/createEvent', [EventController::class, 'createEvent']);
    Route::post('/updateEvent/{eventId}', [EventController::class, 'updateEvent']);
    Route::post('/removeEvent/{eventId}', [EventController::class, 'removeEvent']);
    Route::post('/reportEvent', [EventController::class, 'reportEvent']);
    Route::post('/eventMembership', [EventController::class, 'updateMembership']);

    // ##########################################################
    // Clubs
    // ##########################################################
    // Route::post('/stickyEvents', [EventController::class, 'stickyEvents']);
    Route::post('/clubsAround', [ClubController::class, 'clubsAround']);
    Route::get('/clubInfo/{clubId}', [ClubController::class, 'clubInfo']);
    Route::post('/createClub', [ClubController::class, 'createClub']);
    Route::post('/updateClub/{clubId}', [ClubController::class, 'updateClub']);
    Route::post('/removeClub/{eventId}', [ClubController::class, 'removeClub']);
    // Route::post('/reportEvent', [EventController::class, 'reportEvent']);
    Route::post('/clubMembership', [ClubController::class, 'updateMembership']);

    // ##########################################################
    // Profile / user state
    // ##########################################################
    Route::get('/currentUserInfo', [UserController::class, 'currentUserInfo']);
    Route::get('/favourites', [UserController::class, 'getFavourites']);
    Route::get('/userInfo/{link}', [UserController::class, 'userInfo']);

    Route::post('/getUserStatus', [UserController::class, 'getUserStatus']);
    Route::post('/userFavorite/{userId}/{newFavorite}', [UserController::class, 'userFavorite']);
    Route::post('/blockUser/{userId}', [UserController::class, 'blockUser']);
    Route::post('/unblockUsers', [UserController::class, 'unblockUsers']);
    Route::post('/blockedUsers', [UserController::class, 'getBlockedUsers']);
    Route::post('/unblockUser', [UserController::class, 'unblockUser']);
    Route::post('/updateUser', [UserController::class, 'updateUser']);
    Route::post('/password/change', [UserController::class, 'changePassword']);
    Route::post('/account/delete', [UserController::class, 'accountDelete']);
    Route::post('/account/deactivate', [UserController::class, 'accountDeactivate']);
    Route::post('/account/activate', [UserController::class, 'accountActivate']);
    Route::post('/reportUser/{userId}', [UserController::class, 'reportUser']);

    Route::post('/discreet-mode/change', [DiscreetModeController::class, 'change']);
    Route::get('/user/assign-one-signal/{slug}', [UserController::class, 'assignOneSignalId']);
    Route::post('/user/delete-all-sharing-links', [SharingController::class, 'deleteAllSharingLinks']);

    Route::get('/user/all-sharing-links', [SharingController::class, 'getAllSharingLinks']);
    Route::post('/changeStatusLink', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'changeStatusLink']);
    Route::post('/saveSettingSharingLink', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'saveSettingSharingLink']);

    // ##########################################################
    // Discover
    // ##########################################################
    Route::post('/getUsersAround', [DiscoverController::class, 'getUsersAround']);
    Route::get('/search_tags', [DiscoverController::class, 'getSearchTags']);

    // ##########################################################
    // Media
    // ##########################################################
    Route::get('/userPhoto/{photoId}/{userId}', [UserController::class, 'userPhoto']);

    Route::get('/photos/setAsDefault/{id}/{slug}', [PrivateController::class, 'setPhotoAsDefault']);
    Route::post('/photos/add', [PrivateController::class, 'addGalleryPhoto']);
    Route::post('/photos/update/{id}', [PrivateController::class, 'updateGalleryPhoto']);
    Route::get('/photos/changeVisible/{slug}', [PrivateController::class, 'changePhotoVisibleTo']);
    Route::get('/photos/delete/{id}', [PrivateController::class, 'deletePhoto']);

    Route::post('/videos/upload', [VideoController::class, 'upload']);
    Route::get('/videos/delete/{id}', [VideoController::class, 'delete']);
    Route::get('/videos/getVideoProcess/{hash}', [VideoController::class, 'getVideoProcess']);
    Route::get('/videos/changeVisible/{id}/{slug}', [PrivateController::class, 'changeVideoVisibleTo']);

    // ##########################################################
    // Notifications
    // ##########################################################
//    Route::get('/getNotificationsUpdate', [NotificationsController::class, 'getNotificationsUpdate']); TODO: method is not exists

	Route::post('/addVisit', [NotificationsController::class, 'addVisit']);
    Route::post('/getVisitors', [NotificationsController::class, 'getVisitors']);
    Route::post('/getVisitedUsers', [NotificationsController::class, 'getVisitedUsers']);
    Route::post('/getNotifications', [NotificationsController::class, 'getNotifications']);

    Route::post('/addWave', [NotificationsController::class, 'addWave']);
	Route::post('/clearNotifications', [NotificationsController::class, 'clearNotifications']);

    Route::post('/likeEvent', [NotificationsController::class, 'likeEvent']);
    Route::post('/dislikeEvent', [NotificationsController::class, 'dislikeEvent']);

    // ##########################################################
    // PRO
    // ##########################################################
    Route::post('/subscription/promocode', [SubscriptionController::class, 'promocode']);
    Route::post('/subscription/initiate', [SubscriptionController::class, 'initiate']);
    Route::post('/subscription/unlock', [SubscriptionController::class, 'unlock']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscription/settings', [SubscriptionController::class, 'settings']);
    Route::post('/subscription/paypal', [SubscriptionController::class, 'paypal']);

    // ##########################################################
    // Others
    // ##########################################################
    Route::post('/tags/delete', [PrivateController::class, 'deleteTag']);
    Route::post('/tags/add', [PrivateController::class, 'addTag']);


    // ##########################################################
    // sharing
    // ##########################################################
    Route::post('/share-videos', [SharingController::class, 'shareVideos']);

    // ##########################################################
    // Invite members to bang event
    // ##########################################################
    Route::post('/invite-members', [EventController::class, 'inviteMembers']);
    Route::post('/handle-invitation/{type}', [EventController::class, 'handleInvitation']);
});

// ##########################################################
// Rush
// ##########################################################
Route::group(['prefix' => 'rush', 'middleware' => 'auth:api'], function() {
    Route::get('/', [RushController::class, 'index']);
    Route::get('/refresh', [RushController::class, 'refresh']);
    Route::get('/{id}', [RushController::class, 'getRush']);
    Route::get('/edit/{id}', [RushController::class, 'getEditRush']);

    Route::post('/create', [RushController::class, 'create']);
    Route::post('/delete', [RushController::class, 'delete']);
    Route::post('/upload/image', [RushController::class, 'uploadImage']);
    Route::post('/{rushId}/favorite', [RushController::class, 'favorite']);
    Route::post('/{rushId}/{stripId}/view', [RushController::class, 'markStripViewed']);
    Route::post('/{rushId}/{stripId}/applause', [RushController::class, 'applause']);
    Route::post('/announce', [RushController::class, 'changeUserAnnounceState']);
});

// ##########################################################
// Auth
// ##########################################################
Route::post('/password/email', [ForgotPasswordController::class, 'apiSendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'apiReset']);

Route::post('/register/validateNameDob', [RegisterController::class, 'apiValidateNameDob']);
Route::post('/register/validateEmailPassDob', [RegisterController::class, 'apiValidateEmailPassDob']);
Route::post('/register/validateAndRegister', [RegisterController::class, 'apiValidateAndRegister']);

// ##########################################################
// Static
// ##########################################################
Route::get('/getStaticPage/{lang}/{slug}', [PublicController::class, 'getStaticPage']);

// ##########################################################
// Sharing
// ##########################################################
Route::get('/checkLink/{link}', [SharingController::class, 'checkLink']);
Route::get('/getVideos/{link}', [SharingController::class, 'getSharedVideos']);