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

//Route::group([
//    'prefix' => 'v0',
//    'middleware' => [
//        'auth:api',
//        'SetProfile'
//    ]
//], function () {
//    Route::group(['prefix' => 'messenger'], function() {
//        Route::get('search', 'SearchController@search')->middleware('throttle:45,1');
//        Route::post('join/{slug}', 'MessagesController@joinInviteLink');
//        Route::get('create/{slug}/{alias}', 'MessagesController@checkCreatePrivate');
//        Route::get('{thread_id}/call/{call_id}/{type}', 'MessagesController@callFetch');
//        Route::get('get/{type}', 'MessagesController@fetch');
//        Route::get('get/{thread_id}/{type}/{message_id?}', 'MessagesController@fetch');
//        Route::post('save/{thread_id}', 'MessagesController@update')->middleware('throttle:60,1');
//    });
//});
