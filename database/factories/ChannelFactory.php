<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Channel;
use App\Models\ChannelBannerImage;
use App\Models\ChannelBroadcast;


use App\Models\User;
use App\Models\ChannelFollower;
use Faker\Generator as Faker;
use App\Helpers\Image;

$factory->define(Channel::class, function (Faker $faker) {
    return [
        'logo_image' => Image::create(),
        'follwer_count' => random_int(1, 100),
        'like_count' => random_int(1, 100),
        'owner' => factory(User::class)->create(),
        'description' => $faker->sentence(),
        'name' => $faker->name,
    ];
});

$factory->afterCreatingState(Channel::class, 'addBannerImage', function (Channel $channel, Faker $faker) {
    factory(ChannelBannerImage::class, random_int(1, 10))->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'hasFollower', function (Channel $channel, Faker $faker) {
    factory(ChannelFollower::class, random_int(1, 10))->create([
        'channel_id' => $channel->id,
        'user_id' => factory(User::class)->create()->id,
    ]);
});
$factory->afterCreatingState(Channel::class, 'addBroadcasts', function(Channel $channel, Faker $faker) {
    factory(ChannelBroadcast::class, random_int(1, 5))->create([
        'channel_id' => $channel->id,
    ]);
});
// $randRagne = range(random_int(1, 10));
//     collect($randRange)->map(function($item) {
//         factory(ChannelBannerImage::class)->create([]);
//     });
