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
use App\Models\Team\InvitationCard;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\Board\Article as TeamArticle;
use App\Models\Team\Board\Category as TeamBoardCategory;
use Illuminate\Support\Arr;

$factory->define(Team::class, function (Faker $faker) {
    $teamData = [
        'owner' => factory(User::class)->create(),
        'name' => \Illuminate\Support\Str::random(15),
        'is_public' => random_int(0, 1) === 0,
    ];

    if (config('app.test.useRealImage')) {
        $teamData['logo_image'] = FakeImage::retryCreate(storage_path('app/teamLogos'), 640, 480, null, false);
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
    collect($createCnt)->each(fn () => TeamMember::factory()->create([
        'user_id' => factory(User::class)->create()->id,
        'team_id' => $team->id
    ]));

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

$factory->afterCreatingState(Team::class, 'addTenBroadcasts', function (Team $team, Faker $faker) {
    factory(Broadcast::class, 10)->create([
        'team_id' => $team->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addOperateGame', function (Team $team, Faker $faker) {
    /**
     * @todo 게임타입 팩토리 유니크
     */
    collect(range(0, 9))->each(function () use ($team) {

        do {
            try {
                $isDuplicate = false;
                $gameType = GameType::factory()->create();
            } catch (Illuminate\Database\QueryException  $e) {
                $isDuplicate = true;
            }
        } while($isDuplicate);

        OperateGame::factory()->create([
            'team_id' => $team->id,
            'game_type_id' => $gameType->id,
        ]);

        $isDuplicate = false;

    });
});


$factory->afterCreatingState(Team::class, 'addRandInvitationCards', function (Team $team, Faker $faker) {
    collect(range(0, 19))->each(function (int $item) use ($team, $faker) {
        $statusSet = [
            InvitationCard::PENDING,
            InvitationCard::ACCEPT,
            InvitationCard::REJECT,
        ];

        InvitationCard::factory()->create([
            'team_id' => $team->id,
            'user_id' => factory(User::class)->create()->id,
            'status' => Arr::random($statusSet),
        ]);
    });
});

$factory->afterCreatingState(Team::class, 'addPendingInvitationCards', function (Team $team, Faker $faker) {
    collect(range(0, 19))->each(function (int $item) use ($team, $faker) {
        InvitationCard::factory()->create([
            'team_id' => $team->id,
            'user_id' => factory(User::class)->create()->id,
            'status' => InvitationCard::PENDING,
        ]);
    });
});


$factory->afterCreatingState(Team::class, 'addRejectInvitationCard', function (Team $team, Faker $faker) {
    InvitationCard::factory()->create([
        'team_id' => $team->id,
        'user_id' => factory(User::class)->create()->id,
        'status' => InvitationCard::REJECT,
    ]);
});

$factory->afterCreatingState(Team::class, 'addAcceptInvitationCard', function (Team $team, Faker $faker) {
    InvitationCard::factory()->create([
        'team_id' => $team->id,
        'user_id' => factory(User::class)->create()->id,
        'status' => InvitationCard::ACCEPT,
    ]);
});

$factory->afterCreatingState(Team::class, 'addPendingInvitationCard', function (Team $team, Faker $faker) {
    InvitationCard::factory()->create([
        'team_id' => $team->id,
        'user_id' => factory(User::class)->create()->id,
        'status' => InvitationCard::PENDING,
    ]);
});


$factory->afterCreatingState(Team::class, 'addTeamBoardArticles', function (Team $team, Faker $faker) {
    $categories = collect(range(0, 3))->map(function ($item) use ($team, $faker) {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function ($step) use ($team, $categories) {
        $usedCategory = Arr::random($categories->toArray());
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory
        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});


$factory->afterCreatingState(Team::class, 'addManyTeamBoardArticlesWithSavedImages', function (Team $team, Faker $faker) {
    $categories = collect(range(0, 3))->map(function ($item) use ($team, $faker) {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function ($step) use ($team, $categories) {
        $usedCategory = Arr::random($categories->toArray());
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory
        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});



$factory->afterCreatingState(Team::class, 'addSmallTeamArticlesWithSavedImages', function (Team $team, Faker $faker) {
    $categories = collect(range(0, 3))->map(function ($item) use ($team, $faker) {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 25));
    $articleCnt->each(function ($step) use ($team, $categories) {
        $usedCategory = Arr::random($categories->toArray());
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory
        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});
