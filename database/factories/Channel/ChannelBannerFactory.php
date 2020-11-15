<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Helpers\Image;

$factory->define(ChannelBannerImage::class, function (Faker $faker) {
    return [
        //
        'banner_image' => Image::fakeUrl(),
    ];
});
