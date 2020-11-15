<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Team\Team;
use App\Models\Team\BannerImage;
use App\Models\Team\Slug;
use App\Models\Team\Broadcast;

use Faker\Generator as Faker;
use App\Helpers\Image;

$factory->define(Team::class, function (Faker $faker) {
    return [
        'owner' => factory(User::class)->states(['addProfileImage'])->create(),
        'name' => \Illuminate\Support\Str::random(15),
        'is_public' => random_int(0, 1) === 0,
        'logo_image' => Image::fakeUrl(),
    ];
});

$factory->afterCreatingState(Team::class, 'addBannerImage', function (Team $team, Faker $faker) {
    factory(BannerImage::class, random_int(1, 10))->create([
        'team_id' => $team->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addSlug', function (Team $channel, Faker $faker) {
    Slug::factory()->create([
        'team_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addBroadcasts', function (Team $channel, Faker $faker) {
    factory(Broadcast::class, random_int(1, 5))->create([
        'team_id' => $channel->id,
    ]);
});
