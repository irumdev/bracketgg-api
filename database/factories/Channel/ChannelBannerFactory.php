<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Helpers\Fake\Image as FakeImage;

$factory->define(ChannelBannerImage::class, function (Faker $faker) {
    if (config('app.test.useRealImage')) {
        return [
            'banner_image' => FakeImage::create(storage_path('app/channelBanners'), 640, 480, null, false),
        ];
    }
    return [
        'banner_image' => FakeImage::url(),
    ];
});
