<?php
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES ARE FOR LOGIN AND REGISTER, REMOVED LARAVEL DEFAULTS/////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('/register', 'Auth\RegisterController@register');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED AS GUEST OR AUTH/////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::post('/logout', 'Auth\LoginController@logout');
Route::get('/search', ['as'=>'search','uses'=>'SearchController@index']);
Route::get('/images/profile/{slug}/{full?}/{image?}/{full_two?}', 'ImageController@ProfileImageView')->name('profile_img');
Route::get('/user/profile/{slug}/{redirect?}', 'ProfileController@viewUserProfile')->name('user_profile');
Route::post('/auth/heartbeat', 'Auth\AuthStatusController@authHeartBeat');
Route::get('/auth/heartbeat', 'Auth\AuthStatusController@authHeartBeat');
Route::get('/messenger/join/{slug}', 'MessagesController@joinInviteLink')->name('messenger_invite_join');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED AS GUEST ONLY////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['middleware' => ['guest']], function () {
    Route::get('/', function () {
        return view('splash');
    });
});

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//THESE ROUTES CAN BE PROCESSED WITH AUTH ONLY///////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
Route::group(['middleware' => ['auth', 'IsActive']], function () {
    Route::post('/social/network/add', 'SocialController@makeConnection')->name('makeConnection');
    Route::post('/social/network/remove', 'SocialController@deleteConnection')->name('deleteConnection');
    Route::post('/social/networks', 'SocialController@handleNetworks');
    Route::get('/images/messenger/groups/{thread_id}/{image?}/{thumb?}', 'ImageController@MessengerGroupAvatarView')->name('group_avatar');
    Route::get('/download/messenger/{message_id}', 'DownloadsController@MessengerDownloadDocument');
    Route::get('/images/messenger/{message_id}/{thumb?}', 'ImageController@MessengerPhotoView');
    Route::post('/messenger/join/{slug}', 'MessagesController@joinInviteLink');

    Route::group(['prefix' => 'notifications'], function () {
        Route::post('gather', 'NotificationController@pullNotifications');
        Route::post('delete', 'NotificationController@deleteNotifications');
    });

    Route::group(['prefix' => 'messenger'], function () {
        Route::get('/', 'MessagesController@index')->name('messages');
        Route::get('{thread_id}', 'MessagesController@showThread')->name('messages.show');
        Route::get('/create/{slug}/{type}', 'MessagesController@CreateOrRedirect')->name('messages.create');
        Route::get('/{thread_id}/call/{call_id}', 'MessagesController@openCall');
        Route::post('/{thread_id}/call/{call_id}', 'MessagesController@callSave');
        Route::get('/{thread_id}/call/{call_id}/{type}', 'MessagesController@callFetch');
        Route::get('/fetch/{type}', 'MessagesController@fetch');
        Route::get('/fetch/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
        Route::post('/update/{thread_id}', 'MessagesController@update');
        Route::post('update/{thread_id}/message', 'MessagesController@storeMessage')->middleware('throttle:20,1');
    });
});
