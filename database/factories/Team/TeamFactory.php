<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Helpers\Fake\Image as FakeImage;
use App\Models\GameType;
use App\Models\Team\Slug;
use App\Models\Team\Team;
use Faker\Generator as Faker;
use App\Models\Team\Broadcast;
use App\Models\Team\OperateGame;
use App\Models\Team\BannerImage;
use App\Models\Team\Member as TeamMember;
use Illuminate\Support\Carbon;

$factory->define(Team::class, function (Faker $faker) {
    $teamData = [
        'owner' => factory(User::class)->create(),
        'name' => \Illuminate\Support\Str::random(15),
        'is_public' => random_int(0, 1) === 0,
    ];

    if (config('app.test.useRealImage')) {
        $teamData['logo_image'] = FakeImage::create(storage_path('app/teamLogos'), 640, 480, null, false);
    } else {
        $teamData['logo_image'] = FakeImage::url();
    }

    return $teamData;
});

$factory->afterCreatingState(Team::class, 'addSignedMembers', function (Team $team, Faker $faker) {
    $createCnt = range(1, random_int(2, 10));
    $len = count($createCnt);
    TeamMember::factory()->create([
        'user_id' => $team->owner,
        'team_id' => $team->id,
        'role' => Team::OWNER,
    ]);

    foreach ($createCnt as $_) {
        TeamMember::factory()->create([
            'user_id' => factory(User::class)->create()->id,
            'team_id' => $team->id
        ]);
    }

    $team->member_count = ($len + 1);
    $team->save();
});

$factory->afterCreatingState(Team::class, 'addBannerImage', function (Team $team, Faker $faker) {
    factory(BannerImage::class)->create([
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
