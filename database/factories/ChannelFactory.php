<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelFan;
use App\Models\ChannelFollower;
use App\Models\ChannelBroadcast;
use App\Models\ChannelBannerImage;

use Faker\Generator as Faker;
use App\Helpers\Image;

$factory->define(Channel::class, function (Faker $faker) {
    $imagePath = explode('/', $faker->image(storage_path('app/profileImages')));
    return [
        'logo_image' => Image::create(),
        'follwer_count' => 0,
        'like_count' => 0,
        'owner' => factory(User::class)->create([
            'profile_image' => $imagePath[count($imagePath) - 1],
        ]),
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
    factory(ChannelFollower::class, $followerCount = random_int(1, 10))->create([
        'channel_id' => $channel->id,
        'user_id' => factory(User::class)->create()->id,
    ]);
    $channel->follwer_count = $followerCount;
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'hasLike', function (Channel $channel, Faker $faker) {
    factory(ChannelFan::class, $fansCount = random_int(1, 30))->create([
        'channel_id' => $channel->id,
        'user_id' => factory(User::class)->create()->id
    ]);
    $channel->like_count = $fansCount;
    $channel->save();
});
$factory->afterCreatingState(Channel::class, 'addBroadcasts', function (Channel $channel, Faker $faker) {
    factory(ChannelBroadcast::class, random_int(1, 5))->create([
        'channel_id' => $channel->id,
    ]);
});
