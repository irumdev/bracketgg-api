<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Helpers\Image;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'nick_name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        // 'profile_image' => Image::create(), // fakeUrl
        'profile_image' => null,
        'is_policy_agree' => true,
        'is_privacy_agree' => true,
        // 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'password' => 'password', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->afterCreatingState(User::class, 'addProfileImage', function (User $user, Faker $faker) {
    // $imagePath = explode('/', $faker->image(storage_path('app/profileImages')));
    $user->profile_image = Image::create(); //$imagePath[count($imagePath) - 1];
    $user->save();
});
