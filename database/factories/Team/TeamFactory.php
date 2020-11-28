<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Helpers\Image;
use App\Models\GameType;
use App\Models\Team\Slug;
use App\Models\Team\Team;
use Faker\Generator as Faker;
use App\Models\Team\Broadcast;
use App\Models\Team\OperateGame;
use App\Models\Team\BannerImage;

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

$factory->afterCreatingState(Team::class, 'addSlug', function (Team $team, Faker $faker) {
    Slug::factory()->create([
        'team_id' => $team->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addBroadcasts', function (Team $team, Faker $faker) {
    factory(Broadcast::class, random_int(1, 5))->create([
        'team_id' => $team->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addOperateGame', function (Team $team, Faker $faker) {
    foreach (range(0, 9) as $_) {
        $type = GameType::factory()->create();
        OperateGame::factory()->create([
            'team_id' => $team->id,
            'game_type_id' => $type->id,
        ]);
    }
});
