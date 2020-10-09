<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Response;

use App\Http\Controllers\User\UserLogoutController;
use App\Http\Controllers\User\CheckEmailDuplicateController;
use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\UserVerifyController;

use App\Http\Controllers\Channels\FollowChannelController;
use App\Http\Controllers\Channels\LikeChannelController;
use App\Http\Controllers\Channels\ShowChannelController;
use App\Http\Controllers\Channels\ShowUserChannelController;

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
    Route::post('auth', [UserVerifyController::class, 'verifyUser'])->name('verify');

    Route::get('email/duplicate', [CheckEmailDuplicateController::class, 'getUserEmailDuplicate'])->name('checkEmailDuplicate');
    Route::group(['prefix' => 'user'], function () {
        Route::post('', [CreateUserController::class, 'createUser'])->name('createUser');
        Route::get('', [ShowUserController::class, 'getCurrent'])->name('currentUser')
                                                                ->middleware('auth:sanctum');
    });

    Route::group(['prefix' => 'channel'], function () {
        Route::get('{channel}', [ShowChannelController::class, 'getChannelById'])->name('findChannelById');
    });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', [UserLogoutController::class, 'logout'])->name('logoutUser');
        Route::group(['prefix' => 'channel'], function () {
            Route::get('owner/{user}', [ShowUserChannelController::class, 'getChannelsByUserId'])->name('showChannelByOwnerId');

            Route::get('{channel}/isfollow', [FollowChannelController::class, 'isFollow'])->name('channelIsFollow');
            Route::post('{channel}/follow', [FollowChannelController::class, 'followChannel'])->name('followChannel');
            Route::post('{channel}/unfollow', [FollowChannelController::class, 'unFollowChannel'])->name('unFollowChannel');

            Route::post('{channel}/like', [LikeChannelController::class, 'likeChannel'])->name('likeChannel');
            Route::post('{channel}/unlike', [LikeChannelController::class, 'unLikeChannel'])->name('unLikeChannel');
        });
    });
});
