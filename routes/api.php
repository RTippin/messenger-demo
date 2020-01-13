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
//    Route::group([
//        'middleware' => ['auth:api', 'SetProfile']
//    ], function() {
//        Route::post('device/join', 'ApiController@joinDeviceToken');
//        Route::post('update/{thread_id}', 'MessagesController@update');
//        Route::get('fetch/{type}', 'MessagesController@fetch');
//        Route::get('fetch/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
//        Route::get('call/{thread_id}/{call_id}/{type}', 'MessagesController@callFetch');
//        Route::get('images/messenger/groups/{thread_id}/{thumb?}', 'ImageController@MessengerGroupAvatarView')->name('group_avatar_api');
//    });
//});
//
//Route::group(['prefix' => 'v1'], function () {
//
//});
