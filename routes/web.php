<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowImageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('profile-image/{profileImage}', [ShowImageController::class, 'getUserProfile'])->name('profileImage');

Route::group(['prefix' => 'channel'], function (): void {
    Route::get('article/{channelArticleImage}', [ShowImageController::class, 'getChannelBoardArticle'])->name('channel.article.image');
    Route::get('banner/{bannerImage}', [ShowImageController::class, 'getChannelBanner'])->name('channelBannerImage');
    Route::get('logo/{logoImage}', [ShowImageController::class, 'getChannelLogo'])->name('channelLogoImage');
});

Route::group(['prefix' => 'team'], function (): void {
    Route::get('article/{teamArticleImage}', [ShowImageController::class, 'getTeamBoardArticle'])->name('team.article.image');
    Route::get('banner/{bannerImage}', [ShowImageController::class, 'getTeamBanner'])->name('teamBannerImage');
    Route::get('logo/{logoImage}', [ShowImageController::class, 'getTeamLogo'])->name('teamLogoImage');
});
