<?php
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED AS GUEST OR AUTH/////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::post('/logout', 'Auth\LoginController@logout');
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');

Route::get('/images/messenger/groups/{thread_id}/{image?}/{thumb?}', 'ImageController@MessengerGroupAvatarView')->middleware('auth')->name('group_avatar');
Route::get('/images/messenger/{message_id}/{thumb?}', 'ImageController@MessengerPhotoView')->middleware('auth');
Route::get('/images/{alias}/{slug}/{full?}/{image?}/{full_two?}', 'ImageController@ProfileImageView')->name('profile_img');
Route::get('/profile/{alias}/{slug}/{message?}', 'ProfileController@viewProfile')->name('model_profile');
Route::post('/auth/heartbeat', 'Auth\AuthStatusController@authHeartBeat');
Route::get('/auth/heartbeat', 'Auth\AuthStatusController@authHeartBeat');
Route::get('/messenger/join/{slug}', 'MessagesController@joinInviteLink')->name('messenger_invite_join');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED AS GUEST ONLY////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['middleware' => ['guest']], function () {
    Route::get('/', 'HomeController@splash');
    Route::get('/auth/accounts', 'HomeController@availableAccounts');
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED WITH AUTH ONLY///////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['middleware' => ['auth', 'IsActive']], function () {
    Route::post('/social/network/add', 'SocialController@makeConnection')->name('makeConnection');
    Route::post('/social/network/remove', 'SocialController@deleteConnection')->name('deleteConnection');
    Route::post('/social/networks', 'SocialController@handleNetworks');
    Route::get('/download/messenger/{message_id}', 'DownloadsController@MessengerDownloadDocument');
    Route::post('/messenger/join/{slug}', 'MessagesController@joinInviteLink');

    Route::group(['prefix' => 'notifications'], function () {
        Route::post('gather', 'NotificationController@pullNotifications');
        Route::post('delete', 'NotificationController@deleteNotifications');
    });

    Route::group(['prefix' => 'messenger'], function () {
        Route::get('/', 'MessagesController@index')->name('messages');
        Route::get('search', 'SearchController@search')->middleware('throttle:45,1');
        Route::get('{thread_id}', 'MessagesController@showThread')->name('messages.show');
        Route::get('/create/{slug}/{alias}', 'MessagesController@checkCreatePrivate')->name('messages.create');
        Route::get('/{thread_id}/call/{call_id}', 'MessagesController@openCall');
        Route::post('/{thread_id}/call/{call_id}', 'MessagesController@callSave');
        Route::get('/{thread_id}/call/{call_id}/{type}', 'MessagesController@callFetch');
        Route::get('/fetch/{type}', 'MessagesController@fetch');
        Route::get('/fetch/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
        Route::post('/update/{thread_id}', 'MessagesController@update')->middleware('throttle:60,1');
    });
});
