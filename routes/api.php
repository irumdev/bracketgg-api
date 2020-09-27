<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!\
|
*/


Route::group(['prefix' => 'v1'], function () {
    Route::post('auth', 'User\UserVerifyController@verifyUser')->name('verify');

    Route::group(['prefix' => 'user'], function () {
        // Route::post('', 'User\CreateUserController@createUser')->name('createUser');
        Route::get('', 'User\ShowUserController@getCurrent')->name('currentUser')->middleware('auth:sanctum');
    });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', 'User\UserLogoutController@logout')->name('logoutUser');
        Route::group(['prefix' => 'channel'], function () {
            Route::get('owner/{user}', 'Channels\ShowUserChannelController@getChannelsByUserId')->name('showChannelByOwnerId');
            Route::get('{channel}', 'Channels\ShowChannelController@getChannelById')->name('findChannelById');
        });
    });
});
