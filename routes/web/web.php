<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\PublicController;
use App\Http\Controllers\Mobile\V1\SubscriptionController;

Route::pattern('id', '[0-9]+');
Route::pattern('slug', '[A-Za-z0-9_\-]+');
Route::pattern('hash', '[A-Za-z0-9_\-]+');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::any('sparkpostWebhook', [PublicController::class, 'sparkpostWebhook']);
Route::any('sparkpostGeneration', [PublicController::class, 'sparkpostGeneration']);

Route::any('/payments/segpay/postback', [SubscriptionController::class, 'handleSegpayPostback']);
Route::any('/payments/twokcharge/webhook', [SubscriptionController::class, 'handleTwokchargePostback']);
Route::any('/payments/flexpay/postback', [SubscriptionController::class, 'handleFlexpayPostback']);

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth:admin']], function() {
    Route::get('/tests/check/users/latest/messages/{userId}', ['uses' => 'TestsController@checkUserLatestMessages']);
    Route::get('/tests/update_conversations/{userId}', ['uses' => 'TestsController@updateConversation']);
    Route::get('/tests/clear/users/latest/messages/cache', ['uses' => 'TestsController@clearUserLatestMessagesCache']);
    Route::get('/tests/check/apple/settings', ['uses' => 'TestsController@checkAppleSettings']);
    Route::get('/tests/show/user/conversations/{userId}', ['uses' => 'TestsController@showUserConversations']);
    Route::get('/tests/show/config', ['uses' => 'TestsController@showConfig']);
    Route::get('/tests/show/chat/cache/{userId}', ['uses' => 'TestsController@showChatCache']);

    Route::post('/menu', ['as' => 'admin.menu', 'uses' => 'HomeController@menu']);
    Route::get('/', ['as' => 'admin.index', 'uses' => 'HomeController@index']);

    Route::get('/loginAsUser/{id}', ['as' => 'admin.loginAsUser', 'uses' => 'HomeController@loginAsUser']);

    Route::get('/profile', ['as' => 'admin.profile', 'uses' => 'HomeController@profile']);
    Route::post('/profile', ['as' => 'admin.profile.update', 'uses' => 'HomeController@profileUpdate']);

    Route::any('/users', ['as' => 'admin.users', 'uses' => 'UsersController@index']);
    Route::any('/users/spammers', ['as' => 'admin.users.spammers', 'uses' => 'UsersController@spammers']);
    Route::get('/users/view/{id}', ['as' => 'admin.users.view', 'uses' => 'UsersController@view']);
    Route::get('/users/login/{id}', ['as' => 'admin.users.login', 'uses' => 'UsersController@login']);
    Route::delete('/users/softDelete/{id}', ['as' => 'admin.users.soft_delete', 'uses' => 'UsersController@softDelete']);
    Route::delete('/users/hardDelete/{id}', ['as' => 'admin.users.hard_delete', 'uses' => 'UsersController@hardDelete']);
    Route::get('/users/restore/{id}', ['as' => 'admin.users.restore', 'uses' => 'UsersController@restore']);
    Route::get('/users/suspend/{id}', ['as' => 'admin.users.suspend', 'uses' => 'UsersController@suspend']);
    Route::get('/users/ghost/{id}', ['as' => 'admin.users.ghost', 'uses' => 'UsersController@ghost']);
    Route::get('/users/upgrade/{id}', ['as' => 'admin.users.upgrade', 'uses' => 'UsersController@upgrade']);
    Route::patch('/users/updatePassword/{id}', ['as' => 'admin.users.updatePassword', 'uses' => 'UsersController@updatePassword']);
    Route::get('/users/downgrade/{id}', ['as' => 'admin.users.downgrade', 'uses' => 'UsersController@downgrade']);
    Route::get('/users/activate/{id}', ['as' => 'admin.users.activate', 'uses' => 'UsersController@activate']);
    Route::get('/users/whitelist/{id}', ['as' => 'admin.users.whitelist', 'uses' => 'UsersController@whitelist']);
    Route::get('/users/blacklist/{id}', ['as' => 'admin.users.blacklist', 'uses' => 'UsersController@blacklist']);
    Route::post('/users/buddyLink/{id}', ['as' => 'admin.users.buddyLink', 'uses' => 'UsersController@updateBuddyLink']);

    Route::get('/admins', ['as' => 'admin.admins', 'uses' => 'AdminsController@index']);

    Route::get('/blockedDomains', ['as' => 'admin.blockedDomains', 'uses' => 'BlockedDomainsController@index']);
    Route::post('/blockedDomains', ['as' => 'admin.blockedDomains.updateDomains', 'uses' => 'BlockedDomainsController@updateDomains']);
    Route::post('/blockedDomains/shorteners', ['as' => 'admin.blockedDomains.updateShorteners', 'uses' => 'BlockedDomainsController@updateShorteners']);
    Route::post('/blockedDomains/ip', ['as' => 'admin.blockedDomains.updateIPs', 'uses' => 'BlockedDomainsController@updateIPs']);
    Route::post('/blockedDomains/ip/add', ['as' => 'admin.blockedDomains.addIP', 'uses' => 'BlockedDomainsController@addIP']);

    Route::get('/buddyLinks', ['as' => 'admin.buddyLinks', 'uses' => 'BuddyLinksController@index']);
    Route::post('/buddyLinks', ['as' => 'admin.buddyLinks.update', 'uses' => 'BuddyLinksController@update']);

    Route::any('/events', ['as' => 'admin.events', 'uses' => 'EventsController@index']);
    Route::get('/events/view/{id}', ['as' => 'admin.events.view', 'uses' => 'EventsController@view']);
    Route::delete('/events/delete/{id}', ['as' => 'admin.events.delete', 'uses' => 'EventsController@delete']);
    Route::get('/events/suspend/{id}', ['as' => 'admin.events.suspend', 'uses' => 'EventsController@suspend']);
    Route::get('/events/activate/{id}', ['as' => 'admin.events.activate', 'uses' => 'EventsController@activate']);
    Route::get('/events/type/{id}/{type}', ['as' => 'admin.events.type', 'uses' => 'EventsController@changeType']);

    Route::get('/reports', ['as' => 'admin.reports', 'uses' => 'ReportsController@index']);
    Route::delete('/reports/delete/{id}', ['as' => 'admin.reports.delete', 'uses' => 'ReportsController@delete']);
    Route::delete('/reports/clear/{id}', ['as' => 'admin.reports.clear', 'uses' => 'ReportsController@clearUserReports']);

    Route::get('/events/submissions', ['as' => 'admin.events.submissions', 'uses' => 'EventsController@submissions']);
    Route::get('/events/approveGuideEvent/{id}', ['as' => 'admin.events.approveGuide', 'uses' => 'EventsController@approveGuideEvent']);
    Route::get('/events/declineGuideEvent/{id}', ['as' => 'admin.events.declineGuide', 'uses' => 'EventsController@declineGuideEvent']);
    Route::get('/events/featuredGuide/{id}/{feature}', ['as' => 'admin.events.featureGuide', 'uses' => 'EventsController@setFeaturedOrUnfeatured']);

    Route::get('/events/reports', ['as' => 'admin.reports.events', 'uses' => 'ReportsController@events']);
    Route::delete('/events/reports/delete/{id}', ['as' => 'admin.reports.events.delete', 'uses' => 'ReportsController@deleteEventReport']);
    Route::delete('/events/reports/clear/{id}', ['as' => 'admin.reports.events.clear', 'uses' => 'ReportsController@clearEventsReports']);

    Route::get('/pages', ['as' => 'admin.pages', 'uses' => 'PagesController@index']);
    Route::get('/pages/add', ['as' => 'admin.pages.add', 'uses' => 'PagesController@add']);
    Route::post('/pages/add', ['as' => 'admin.pages.insert', 'uses' => 'PagesController@insert']);
    Route::get('/pages/edit/{id}', ['as' => 'admin.pages.edit', 'uses' => 'PagesController@edit']);
    Route::post('/pages/edit/{id}', ['as' => 'admin.pages.update', 'uses' => 'PagesController@update']);
    Route::delete('/pages/delete/{id}', ['as' => 'admin.pages.delete', 'uses' => 'PagesController@delete']);

    Route::get('/uploads', ['as' => 'admin.uploads', 'uses' => 'UploadsController@index']);
    Route::post('/uploads/add', ['as' => 'admin.uploads.add', 'uses' => 'UploadsController@save']);
    Route::delete('/uploads/delete/{name}', ['as' => 'admin.uploads.delete', 'uses' => 'UploadsController@delete']);

    //email templates
    Route::any('/emailTemplates', ['as'=>'admin.emailTemplates', 'uses'=>'EmailTemplatesController@index']);
    Route::get('/emailTemplates/edit/{id}', ['as'=>'admin.emailTemplates.edit', 'uses'=>'EmailTemplatesController@edit']);
    Route::post('/emailTemplates/edit/{id}', ['as'=>'admin.emailTemplates.update', 'uses'=>'EmailTemplatesController@update']);

    Route::any('/newsletter', ['as'=>'admin.newsletter', 'uses'=>'NewsletterController@index']);
    Route::any('/newsletter/send', ['as'=>'admin.newsletter.send', 'uses'=>'NewsletterController@send']);

    Route::any('/photosModeration', ['as'=>'admin.photosModeration', 'uses'=>'PhotosModerationController@index']);
    Route::get('/photosModeration/changeRating/{photoId}/{type}', ['as'=>'admin.photosModeration.changeRating', 'uses'=>'PhotosModerationController@changeRating']);

    Route::get('/community/videos', ['as' => 'admin.community.videos', 'uses' => 'Community\VideosController@index']);
    Route::post('/community/videos', ['uses' => 'Community\VideosController@index']);
    Route::get('/community/videos/{videoId}/rate/{type}', ['as' => 'admin.community.videos.rate', 'uses' => 'Community\VideosController@rate']);
    Route::get('/community/videos/{videoId}/delete', ['as' => 'admin.community.videos.delete', 'uses' => 'Community\VideosController@delete']);

    Route::any('/videoServer', ['as'=>'admin.videoServer', 'uses'=>'VideoServerController@index']);
    Route::post('/videoServer/clearDeleted', ['as'=>'admin.videoServer.clearDeleted', 'uses'=>'VideoServerController@clearDeleted']);

    Route::get('/temp/send-custom-notification', 'TempController@sendCustomNotification');
    Route::get('/temp/update-locality', 'TempController@updateLocality');
    Route::get('/temp/remove-orig-videos', 'TempController@removeOriginalVideos');

    Route::get('/promo', ['as' => 'admin.promo', 'uses' => 'PromoCodesController@index']);
    Route::get('/promo/create', ['as' => 'admin.promo.create', 'uses' => 'PromoCodesController@create']);
    Route::get('/promo/{id}', ['as' => 'admin.promo.edit', 'uses' => 'PromoCodesController@edit']);
    Route::post('/promo/save', ['as' => 'admin.promo.save', 'uses' => 'PromoCodesController@save']);
    Route::post('/promo/invalidate/{id}', ['as' => 'admin.promo.invalidate', 'uses' => 'PromoCodesController@invalidate']);

    Route::get('/internalMessage', ['as' => 'admin.internalMessage', 'uses' => 'InternalMessageController@index']);
    Route::post('/internalMessage', ['as' => 'admin.internalMessage.send', 'uses' => 'InternalMessageController@send']);

    Route::get('/moderation/photos', ['as' => 'admin.moderation.photos', 'uses' => 'PhotosController@index']);
    Route::get('/moderation/photos/{id}/rate/{rate}', ['as' => 'admin.moderation.photos.rate.hard', 'uses' => 'PhotosController@rateHard']);
    Route::get('/moderation/photos/{id}/delete', ['as' => 'admin.moderation.photos.delete', 'uses' => 'PhotosController@deleteImage']);
    Route::post('/moderation/photos/rate', ['as' => 'admin.moderation.photos.rate', 'uses' => 'PhotosController@rate']);
    Route::post('/moderation/photos/rate/group', ['as' => 'admin.moderation.photos.rate.group', 'uses' => 'PhotosController@rateGroup']);

    Route::get('/moderation/videos', ['as' => 'admin.moderation.videos', 'uses' => 'VideosController@index']);
    Route::get('/moderation/videos/{id}/rate/{rate}', ['as' => 'admin.moderation.videos.rate.hard', 'uses' => 'VideosController@rateHard']);
    Route::get('/moderation/videos/{id}/delete', ['as' => 'admin.moderation.videos.delete', 'uses' => 'VideosController@deleteVideo']);
    Route::post('/moderation/videos/rate', ['as' => 'admin.moderation.videos.rate', 'uses' => 'VideosController@rate']);
    Route::post('/moderation/videos/rate/group', ['as' => 'admin.moderation.videos.rate.group', 'uses' => 'VideosController@rateGroup']);

    Route::get('/moderation/word-filter', ['as' => 'admin.moderation.wordFilter', 'uses' => 'Moderation\WordFilterController@index']);
    Route::post('/moderation/word-filter', ['uses' => 'Moderation\WordFilterController@save']);

    Route::get('/moderation/word-search', ['as' => 'admin.moderation.wordSearch', 'uses' => 'Moderation\WordSearchController@index']);
    Route::post('/moderation/word-search', ['uses' => 'Moderation\WordSearchController@search']);

    Route::get('/rush', ['as' => 'admin.rush', 'uses' => 'RushController@index']);
    Route::post('/rush/{id}/suspend', ['as' => 'admin.rush.suspend', 'uses' => 'RushController@suspend']);
    Route::post('/rush/{id}/activate', ['as' => 'admin.rush.activate', 'uses' => 'RushController@activate']);

    Route::get('/sendmail', ['as' => 'admin.sendmail', 'uses' => 'HomeController@sendmail']);

    Route::any('/pro-users', ['as' => 'admin.proUsers', 'uses' => 'ProController@users']);
    Route::get('/pro-users/transactions/{userId}', ['as' => 'admin.proUsers.transactions', 'uses' => 'ProController@userTransactions']);
    Route::get('/pro-users/transactions/{userId}/segpay/{transactionId}', ['as' => 'admin.proUsers.segpay.logs', 'uses' => 'ProController@segpayTransactionLogs']);
    Route::get('/pro-users/transactions/{userId}/2000-charge/{transactionId}', ['as' => 'admin.proUsers.twok.logs', 'uses' => 'ProController@twokChargeTransactionLogs']);

    Route::get('/online-countries', ['as' => 'admin.onlineCountries', 'uses' => 'OnlineCountryController@index']);
    Route::get('/online-countries/{id}', ['as' => 'admin.onlineCountries.edit', 'uses' => 'OnlineCountryController@edit']);
    Route::post('/online-countries/{id}', ['as' => 'admin.onlineCountries.update', 'uses' => 'OnlineCountryController@update']);
});

Route::get('/newsletter/unsubscribe/{id}/{slug}', ['as' => 'newsletter.unsubscribe', 'uses' => 'PublicController@newsletterUnsubscribe']);

Route::get('/rush', 'Controller@rush');
Route::group(['prefix' => 'rush'], function() {
    Route::get('/{any}', 'Controller@rush')->where('any', '^(?!.*_debugbar).*');
});

Route::get('/page/{slug}', 'Controller@appSwitch');
Route::get('/recover-password', 'Controller@appSwitch');

Route::get('/chat-photo/{slug}/{hash}', function ($slug, $hash) {
    return redirect()->to('/chat/' . $slug);
});

Route::group(array('domain' => config('app.google_landing_domain')), function() { // app.buddy.net
    Route::get('/', 'Controller@googleLanding');
});

Route::get('/', 'Controller@appSwitch');

Route::get('/{any}', 'Controller@app')->where('any', '^(?!.*_debugbar).+'); //everything except _debugbar
