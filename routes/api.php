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
    Route::post('user', 'User\CreateUserController@createUser')->name('createUser');

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::group(['prefix' => 'channel'], function () {
            Route::get('owner/{user}', 'Channels\ShowUserChannelController@getChannelsByUserId')->name('showChannelByOwnerId');
            Route::get('{channel}', 'Channels\ShowChannelController@getChannelById')->name('findChannelById');
        });
    });
});



// Route::fallback(function () {
//     return response()->json([
//         'ok' => false,
//         'message' => 'fallback'
//     ], Response::HTTP_NOT_FOUND);
// });
