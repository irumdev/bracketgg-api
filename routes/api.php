<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\UserLogoutController;
use App\Http\Controllers\User\CheckEmailDuplicateController;
use App\Http\Controllers\User\CreateUserController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\UserVerifyController;
use App\Http\Controllers\User\VerifyEmailController;

use App\Http\Controllers\Channel\FollowChannelController;
use App\Http\Controllers\Channel\LikeChannelController;
use App\Http\Controllers\Channel\ShowChannelController;
use App\Http\Controllers\Channel\ShowUserChannelController;

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


    Route::group(['prefix' => 'email'], function () {
        Route::get('duplicate', [CheckEmailDuplicateController::class, 'getUserEmailDuplicate'])->name('checkEmailDuplicate');
        Route::get('verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmail'])->middleware('signed')->name('verifyEmail');
    });
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
            Route::get('{channel}/islike', [LikeChannelController::class, 'isLike'])->name('isLikeChannel');
        });

        Route::post('email/resend', [VerifyEmailController::class, 'resendEmail'])->name('resendVerifyEmail');
    });
});
