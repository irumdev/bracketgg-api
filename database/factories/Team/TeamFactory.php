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
use App\Models\Team\Board\Reply;
use App\Models\Team\Member as TeamMember;
use App\Models\Team\Board\Article as TeamArticle;
use App\Models\Team\Board\Category as TeamBoardCategory;
use Illuminate\Support\Arr;

if (! function_exists('createTeamOwner')) {
    function createTeamOwner(Team $team): void
    {
        $alreadyExists = App\Models\Team\Member::where([
            ['user_id', '=', $team->owner]
        ])->exists();

        if (! $alreadyExists) {
            TeamMember::factory()->create([
                'user_id' => $team->owner,
                'team_id' => $team->id,
                'role' => Team::OWNER,
            ]);
        }
    }
}
$factory->define(Team::class, function (Faker $faker): array {
    $teamData = [
        'owner' => factory(User::class)->create(),
        'name' => \Illuminate\Support\Str::random(15),
        'is_public' => random_int(0, 1) === 0,
        'board_category_count_limit' => 3
    ];

    if (config('app.test.useRealImage')) {
        $teamData['logo_image'] = FakeImage::retryCreate(storage_path('app/teamLogos'), 640, 480, null, false);
    } else {
        $teamData['logo_image'] = FakeImage::url();
    }

    return $teamData;
});

$factory->afterCreatingState(Team::class, 'addSignedMembers', function (Team $team, Faker $faker): void {
    $createCnt = range(1, random_int(2, 10));
    $len = count($createCnt);
    TeamMember::factory()->create([
        'user_id' => $team->owner,
        'team_id' => $team->id,
        'role' => Team::OWNER,
    ]);
    collect($createCnt)->each(fn (): TeamMember => TeamMember::factory()->create([
        'user_id' => factory(User::class)->create()->id,
        'team_id' => $team->id
    ]));

    $team->member_count = ($len + 1);
    $team->save();
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addBannerImage', function (Team $team, Faker $faker): void {
    factory(BannerImage::class)->create([
        'team_id' => $team->id,
    ]);
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addSlug', function (Team $team, Faker $faker): void {
    Slug::factory()->create([
        'team_id' => $team->id,
    ]);
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addBroadcasts', function (Team $team, Faker $faker): void {
    factory(Broadcast::class, random_int(1, 5))->create([
        'team_id' => $team->id,
    ]);
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addTenBroadcasts', function (Team $team, Faker $faker): void {
    factory(Broadcast::class, 10)->create([
        'team_id' => $team->id,
    ]);
});

$factory->afterCreatingState(Team::class, 'addOperateGame', function (Team $team, Faker $faker): void {
    collect(range(0, 9))->each(function () use ($team): void {
        do {
            try {
                $isDuplicate = false;
                $gameType = GameType::factory()->create();
            } catch (Illuminate\Database\QueryException  $e) {
                $isDuplicate = true;
            }
        } while ($isDuplicate);

        OperateGame::factory()->create([
            'team_id' => $team->id,
            'game_type_id' => $gameType->id,
        ]);

        $isDuplicate = false;
    });
});


$factory->afterCreatingState(Team::class, 'addRandInvitationCards', function (Team $team, Faker $faker): void {
    collect(range(0, 19))->each(function (int $item) use ($team, $faker): void {
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
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addPendingInvitationCards', function (Team $team, Faker $faker): void {
    collect(range(0, 19))->each(function (int $item) use ($team, $faker): void {
        InvitationCard::factory()->create([
            'team_id' => $team->id,
            'user_id' => factory(User::class)->create()->id,
            'status' => InvitationCard::PENDING,
        ]);
    });
    createTeamOwner($team);
});


$factory->afterCreatingState(Team::class, 'addRejectInvitationCard', function (Team $team, Faker $faker): void {
    InvitationCard::factory()->create([
        'team_id' => $team->id,
        'user_id' => factory(User::class)->create()->id,
        'status' => InvitationCard::REJECT,
    ]);
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addAcceptInvitationCard', function (Team $team, Faker $faker): void {
    InvitationCard::factory()->create([
        'team_id' => $team->id,
        'user_id' => factory(User::class)->create()->id,
        'status' => InvitationCard::ACCEPT,
    ]);
    createTeamOwner($team);
});

$factory->afterCreatingState(Team::class, 'addManyPendingInvitationCard', function (Team $team, Faker $faker): void {
    collect(range(0, 40))->each(function (int $step) use ($team): void {
        InvitationCard::factory()->create([
            'team_id' => $team->id,
            'user_id' => factory(User::class)->create()->id,
            'status' => InvitationCard::PENDING,
        ]);
    });
    createTeamOwner($team);
});


$factory->afterCreatingState(Team::class, 'addTeamBoardArticles', function (Team $team, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($team, $faker): int {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function (int $step) use ($team, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory,
            'team_id' => $team->id,
        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});


$factory->afterCreatingState(Team::class, 'addManyTeamBoardArticlesWithSavedImages', function (Team $team, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($team, $faker): int {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function (int $step) use ($team, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory,
            'team_id' => $team->id,

        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});



$factory->afterCreatingState(Team::class, 'addSmallTeamArticlesWithSavedImages', function (Team $team, Faker $faker): void {
    $categories = collect(range(0, $team->board_category_count_limit))->map(function (int $item) use ($team, $faker): int {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 10));
    $articleCnt->each(function (int $step) use ($team, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory,
            'team_id' => $team->id,

        ]);

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});


$factory->afterCreatingState(Team::class, 'addSmallTeamArticlesWithSavedImagesAndComments', function (Team $team, Faker $faker): void {
    $categories = collect(range(0, $team->board_category_count_limit))->map(function (int $item) use ($team, $faker): int {
        $category = TeamBoardCategory::factory()->create([
            'show_order' => $item,
            'team_id' => $team->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 15));
    $articleCnt->each(function (int $step) use ($team, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $commentCount = collect(range(0, 10));

        $article = TeamArticle::factory()->create([
            'user_id' => $team->owner,
            'category_id' => $usedCategory,
            'team_id' => $team->id,
            'comment_count' => $commentCount->count()
        ]);


        $commentCount->each(function (int $item) use ($article, $team): void {
            Reply::factory()->create([
                'article_id' => $article->id,
                'parent_id' => null,
                'team_id' => $team->id,
                'user_id' => factory(User::class)->create()->id,
            ]);
        });

        $c = TeamBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});
