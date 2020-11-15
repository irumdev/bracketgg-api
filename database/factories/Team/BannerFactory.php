<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;
use App\Models\Team\BannerImage;
use App\Helpers\Image;

$factory->define(BannerImage::class, function (Faker $faker) {
    return [
        //
        'banner_image' => Image::fakeUrl(),
    ];
});
