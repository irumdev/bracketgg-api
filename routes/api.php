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

    Route::get('email/duplicate', 'User\CheckEmailDuplicateController@getUserEmailDuplicate')->name('checkEmailDuplicate');
    Route::group(['prefix' => 'user'], function () {
        Route::post('', 'User\CreateUserController@createUser')->name('createUser');
        Route::get('', 'User\ShowUserController@getCurrent')->name('currentUser')
                                                            ->middleware('auth:sanctum');
    });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', 'User\UserLogoutController@logout')->name('logoutUser');
        Route::group(['prefix' => 'channel'], function () {
            Route::get('owner/{user}', 'Channels\ShowUserChannelController@getChannelsByUserId')->name('showChannelByOwnerId');
            Route::get('{channel}', 'Channels\ShowChannelController@getChannelById')->name('findChannelById');

            Route::post('{channel}/follow', 'Channels\FollowChannelController@followChannel')->name('followChannel');
            Route::post('{channel}/unfollow', 'Channels\FollowChannelController@unFollowChannel')->name('unFollowChannel');

            Route::post('{channel}/like', 'Channels\LikeChannelController@likeChannel')->name('likeChannel');
            Route::post('{channel}/unlike', 'Channels\LikeChannelController@unLikeChannel')->name('unLikeChannel');
        });
    });
});
