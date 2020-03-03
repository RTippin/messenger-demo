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
    Route::group(['prefix' => 'messenger'], function () {
        Route::get('/', 'MessagesController@index')->name('messages');
        Route::get('{thread_id}', 'MessagesController@showThread')->name('messages.show');
        Route::get('/create/{slug}/{alias}', 'MessagesController@viewCreatePrivate')->name('messages.create');
        Route::get('/{thread_id}/call/{call_id}', 'MessagesController@openCall');
    });
});

Route::group(['prefix' => 'demo-api'], function () {
    Route::group(['middleware' => ['auth', 'IsActive']], function() {
        Route::group(['prefix' => 'friends'], function() {
            Route::get('/', 'SocialController@getMyFriends');
            Route::get('sent', 'SocialController@getSentFriends');
            Route::get('pending', 'SocialController@getPendingFriends');
            Route::post('add', 'SocialController@add');
            Route::post('remove', 'SocialController@remove');
            Route::post('cancel', 'SocialController@cancel');
            Route::post('accept', 'SocialController@accept');
            Route::post('deny', 'SocialController@deny');
        });
        Route::group(['prefix' => 'messenger'], function() {
            Route::get('search/{query}', 'SearchController@search')->middleware('throttle:45,1');
            Route::post('join/{slug}', 'MessagesController@joinInviteLink');
            Route::get('create/{slug}/{alias}', 'MessagesController@checkCreatePrivate');
            Route::get('{thread_id}/call/{call_id}/{type}', 'MessagesController@callFetch');
            Route::get('get/{type}', 'MessagesController@fetch');
            Route::get('get/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
            Route::post('save/{thread_id}', 'MessagesController@update')->middleware('throttle:60,1');
        });
    });
});
