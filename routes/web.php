<?php

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\Channel\UpdateChannelController;

use App\Http\Controllers\User\ProfileImageController;

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

Route::get('profile-image/{profileImage}', [ProfileImageController::class, 'getProfileImage'])->name('profileImage');
Route::get('logo-image/{channelLogoImage}', [UpdateChannelController::class, 'getChannelLogoImage'])->name('channelLogoImage');
