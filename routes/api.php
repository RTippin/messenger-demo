<?php

use Illuminate\Http\Request;

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

//Route::group(['prefix' => 'v0'], function () {
//    //API with auth, profile and acc active check
//    Route::group(['middleware' => ['auth:api','SetProfile','IsActive']], function() {
//
//    //Everything else allowed on web or app API request
//        Route::post('report/error', 'TechController@reportJavascriptError')->middleware('throttle:5,1');
//
//        Route::group(['prefix' => 'user'], function() {
//            Route::get('logins/recent', 'UserDashboardController@getRecentLoginLogs');
//        });
//
//        Route::group(['prefix' => 'friends'], function() {
//            Route::get('/', 'SocialController@getMyFriends');
//            Route::get('sent', 'SocialController@getSentFriends');
//            Route::get('pending', 'SocialController@getPendingFriends');
//            Route::post('add', 'SocialController@add');
//            Route::post('remove', 'SocialController@remove');
//            Route::post('cancel', 'SocialController@cancel');
//            Route::post('accept', 'SocialController@accept');
//            Route::post('deny', 'SocialController@deny');
//        });
//
//        Route::group(['prefix' => 'messenger'], function() {
//            Route::get('search/{query}', 'SearchController@search')->middleware('throttle:45,1');
//            Route::post('join/{slug}', 'MessagesController@joinInviteLink');
//            Route::get('create/{slug}/{alias}', 'MessagesController@checkCreatePrivate');
//            Route::post('{thread_id}/call/{call_id}', 'MessagesController@callSave');
//            Route::get('{thread_id}/call/{call_id}/{type}', 'MessagesController@callFetch');
//            Route::get('get/{type}', 'MessagesController@fetch');
//            Route::get('get/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
//            Route::post('save/{thread_id}', 'MessagesController@update')->middleware('throttle:60,1');
//        });
//
//        Route::group(['prefix' => 'images'], function() {
//            Route::get('messenger/groups/{thread_id}/{thumb?}', 'ImageController@MessengerGroupAvatarView');
//            Route::get('messenger/{message_id}/{thumb?}', 'ImageController@MessengerPhotoView');
//            Route::get('{alias}/{slug}/{full?}/{image?}/{full_two?}', 'ImageController@ProfileImageView');
//        });
//    });
//});
