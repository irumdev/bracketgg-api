<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Team\Broadcast;
use Illuminate\Support\Arr;

use Faker\Generator as Faker;

$factory->define(Broadcast::class, function (Faker $faker) {
    return [
        'broadcast_address' => $faker->imageUrl(),
        'platform' => Arr::random(array_keys(Broadcast::$platforms))
    ];
});
