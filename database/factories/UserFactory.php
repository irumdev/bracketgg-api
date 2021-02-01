<?php

declare(strict_types=1);

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use App\Helpers\Fake\Image as FakeImage;

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
        'email' => \Illuminate\Support\Str::random(20) . '@' . \Illuminate\Support\Str::random(20) . '.' . \Illuminate\Support\Str::random(3),
        //$faker->unique()->safeEmail,
        'email_verified_at' => now(),
        // 'profile_image' => FakeImage::create(),
        'profile_image' => null,
        'is_policy_agree' => true,
        'is_privacy_agree' => true,
        // 'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'password' => 'password', // password
        'remember_token' => Str::random(10),
        'create_channel_limit' => User::DEFAULT_CREATE_CHANNEL_LIMIT_COUNT,
        'create_team_limit' => User::DEFAULT_CREATE_TEAM_LIMIT_COUNT,
    ];
});

$factory->afterCreatingState(User::class, 'addProfileImage', function (User $user, Faker $faker) {
    if (config('app.test.useRealImage')) {
        // $imageName = FakeImage::create(storage_path('app/profileImages'), 640, 480, null, false);
        $imageName = FakeImage::retryCreate(storage_path('app/profileImages'), 640, 480, null, false);
        $user->profile_image = $imageName; //$imagePath[count($imagePath) - 1];
    } else {
        $user->profile_image = FakeImage::url();
    }
    $user->save();
});
