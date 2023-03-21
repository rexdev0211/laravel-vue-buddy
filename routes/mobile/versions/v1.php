<?php

use App\Http\Controllers\Mobile\V1\ChatController;
use App\Http\Controllers\Mobile\V1\EventController;
use App\Http\Controllers\Mobile\V1\SubscriptionController;
use App\Http\Controllers\Mobile\V1\UserController;
use App\Http\Controllers\Mobile\V1\DiscreetModeController;
use App\Http\Controllers\Mobile\V1\DiscoverController;
use App\Http\Controllers\Mobile\V1\PrivateController;
use App\Http\Controllers\Mobile\V1\VideoController;
use App\Http\Controllers\Mobile\V1\NotificationsController;
use App\Http\Controllers\Mobile\V1\PaymentsController;

use App\Http\Controllers\Mobile\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Mobile\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Mobile\V1\Auth\RegisterController;
use App\Http\Controllers\Mobile\V1\Auth\LoginController;

Route::pattern('userId', '[0-9]+');
Route::pattern('eventId', '[0-9]+');
Route::pattern('photoId', '[0-9]+');
Route::pattern('videoId', '[0-9]+');
Route::pattern('flag', '[01]');
Route::pattern('slug', '[A-Za-z0-9_\-]+');

// ##########################################################
// Registration
// ##########################################################

Route::post('/register/validate/person', [RegisterController::class, 'apiValidateNameDob']);
Route::post('/register/validate/credentials', [RegisterController::class, 'apiValidateEmailPass']);
Route::post('/register/submit', [RegisterController::class, 'apiValidateAndRegister']);

// ##########################################################
// Auth
// ##########################################################
Route::post('/auth/password/send', [ForgotPasswordController::class, 'apiSendResetLinkEmail']);
Route::post('/auth/password/reset', [ResetPasswordController::class, 'apiReset']);

Route::middleware(['authMobile:api'])->group(function() {
    // ##########################################################
    // Auth (authenticated users)
    // ##########################################################

    Route::post('/auth/logout', [LoginController::class, 'logout']);
    Route::post('/auth/password', [UserController::class, 'changePassword']);

    // ##########################################################
    // Affecting chat
    // ##########################################################

    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/conversation/user/{userId}/read', [ChatController::class, 'markConversationAsRead']);
    Route::post('/conversation/event/{eventId}/user/{userId}/read', [ChatController::class, 'markEventConversationAsRead']);
    Route::delete('/conversation/user/{userId}', [ChatController::class, 'removeConversation']);
    Route::delete('/conversation/event/{eventId}/user/{userId}', [ChatController::class, 'removeEventConversation']);

    Route::get('/messages/{userId}', [ChatController::class, 'getMessages']);
    Route::get('/messages/event/{eventId}/user/{userId}', [ChatController::class, 'getEventMessages']);
    Route::get('/messages/group/{eventId}', [ChatController::class, 'getGroupMessages']);
    Route::post('/message', [ChatController::class, 'sendMessage']);
    Route::post('/messages', [ChatController::class, 'sendMessages']);
    Route::delete('/message', [ChatController::class, 'markMessageAsRemoved']);

    // ##########################################################
    // Events
    // ##########################################################

    Route::post('/events', [EventController::class, 'eventsAround']);
    Route::post('/events/sticky', [EventController::class, 'stickyEvents']);
    Route::post('/event', [EventController::class, 'createEvent']);
    Route::get('/event/{eventId}', [EventController::class, 'eventInfo']);
    Route::patch('/event/{eventId}', [EventController::class, 'updateEvent']);
    Route::delete('/event/{eventId}', [EventController::class, 'removeEvent']);
    Route::post('/event/report', [EventController::class, 'reportEvent']);
    Route::post('/event/like', [NotificationsController::class, 'likeEvent']);
    Route::post('/event/dislike', [NotificationsController::class, 'dislikeEvent']);
    Route::post('/event/membership', [EventController::class, 'updateMembership']);

    // ##########################################################
    // Profile / user state
    // ##########################################################

    Route::get('/user', [UserController::class, 'currentUserInfo']);
    Route::get('/favourites', [UserController::class, 'getFavourites']);
    Route::get('/user/{userId}', [UserController::class, 'userInfo']);
    Route::patch('/user', [UserController::class, 'updateUser']);
    Route::post('/user/status', [UserController::class, 'getUserStatus']);
    Route::post('/user/discreet', [DiscreetModeController::class, 'change']);
    Route::post('/user/favorite', [UserController::class, 'userFavorite']);
    Route::post('/user/block', [UserController::class, 'blockUser']);
    Route::post('/user/report', [UserController::class, 'reportUser']);

    Route::post('/users', [DiscoverController::class, 'getUsersAround']);
    Route::get('/search_tags', [DiscoverController::class, 'getSearchTags']);
    Route::post('/users/unblock', [UserController::class, 'unblockUsers']);
    Route::post('/users/blockedUsers', [UserController::class, 'getBlockedUsers']);
    Route::post('/users/unblockUser', [UserController::class, 'unblockUser']);

    Route::post('/account/delete', [UserController::class, 'accountDelete']);
    Route::post('/account/deactivate', [UserController::class, 'accountDeactivate']);
    Route::post('/account/activate', [UserController::class, 'accountActivate']);

    Route::get('/user/assign-one-signal/{slug}', [UserController::class, 'assignOneSignalId']);

    // ##########################################################
    // Media
    // ##########################################################

    Route::post('/photo', [PrivateController::class, 'addGalleryPhoto']);
    Route::get('/photo/{photoId}/user/{userId}', [UserController::class, 'userPhoto']);
    Route::patch('/photo/{photoId}', [PrivateController::class, 'updateGalleryPhoto']);
    Route::patch('/photo/{photoId}/default/{slot}', [PrivateController::class, 'setPhotoAsDefault']);
    Route::patch('/photo/{photoId}/visibility/{slug}', [PrivateController::class, 'changePhotoVisibleTo']);
    Route::delete('/photo/{photoId}', [PrivateController::class, 'deletePhoto']);

    Route::post('/video', [VideoController::class, 'upload']);
    Route::patch('/video/{videoId}/visibility/{slug}', [PrivateController::class, 'changeVideoVisibleTo']);
    Route::delete('/video/{videoId}', [VideoController::class, 'delete']);

    // ##########################################################
    // Notifications
    // ##########################################################

    Route::get('/visitors', [NotificationsController::class, 'getVisitors']);
    Route::get('/visited', [NotificationsController::class, 'getVisitedUsers']);
    Route::get('/notifications', [NotificationsController::class, 'getNotifications']);
    Route::post('/visit', [NotificationsController::class, 'addVisit']);
    Route::post('/wave', [NotificationsController::class, 'addWave']);

    // ##########################################################
    // PRO
    // ##########################################################
    Route::post('/subscription/promocode', [SubscriptionController::class, 'promocode']);
    Route::post('/subscription/paypal', [SubscriptionController::class, 'paypal']);

    // ##########################################################
    // Subscription
    // ##########################################################

    Route::post('/subscription/apple', [PaymentsController::class, 'handleApple']);
    Route::post('/subscription/google', [PaymentsController::class, 'handleGoogle']);

    // ##########################################################
    // Tags
    // ##########################################################

    Route::post('/tag', [PrivateController::class, 'addTag']);
    Route::delete('/tag/{tagId}', [PrivateController::class, 'deleteTag']);

    // ##########################################################
    // Sharing
    // ##########################################################
    Route::post('/sharing/delete-all-sharing-links', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'deleteAllSharingLinks']);
    Route::get('/sharing/all-sharing-links', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'getAllSharingLinks']);
    Route::post('/sharing/share-videos', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'shareVideos']);
    Route::get('/sharing/checkLink/{link}', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'checkLink']);
    Route::get('/sharing/getVideos/{link}', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'getSharedVideos']);
    Route::post('/sharing/changeStatusLink', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'changeStatusLink']);
    Route::post('/sharing/saveSettingSharingLink', [\App\Http\Controllers\Mobile\V1\SharingController::class, 'saveSettingSharingLink']);

    // ##########################################################
    // Private bang events
    // ##########################################################
    Route::post('/private-events/invite-members', [EventController::class, 'inviteMembers']);
    Route::post('/private-events/handle-invitation/{type}', [EventController::class, 'handleInvitation']); // type: accept|decline
});
