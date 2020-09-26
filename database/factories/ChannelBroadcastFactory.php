<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ChannelBroadcast;
use Illuminate\Support\Arr;

use Faker\Generator as Faker;

$factory->define(ChannelBroadcast::class, function (Faker $faker) {

    return [
        'broadcast_address' => $faker->imageUrl(),
        'platform' => Arr::random(array_keys(ChannelBroadcast::$platforms))
    ];
});
