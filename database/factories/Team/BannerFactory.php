<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Team\BannerImage;
use App\Helpers\Fake\Image as FakeImage;

$factory->define(BannerImage::class, function (Faker $faker) {
    if (config('app.test.useRealImage')) {
        return [
            'banner_image' => FakeImage::retryCreate(storage_path('app/teamBanners'), 640, 480, null, false),
        ];
    }
    return [
        'banner_image' => FakeImage::url(),
    ];
});
