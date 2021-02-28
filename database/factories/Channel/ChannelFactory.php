<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\Fan as ChannelFan;
use App\Models\Channel\Follower as ChannelFollower;
use App\Models\Channel\Broadcast as ChannelBroadcast;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Models\Channel\Slug as ChannelSlug;
use App\Models\Channel\Board\Category as ChannelBoardCategory;
use App\Models\Channel\Board\Article as ChannelArticle;
use App\Models\Channel\Board\ArticleImage as ChannelArticleImage;
use App\Models\Channel\Board\Reply;

use Faker\Generator as Faker;
use App\Helpers\Fake\Image as FakeImage;
use Illuminate\Support\Arr;

$factory->define(Channel::class, function (Faker $faker): array {
    $channelData = [
        'follwer_count' => 0,
        'like_count' => 0,
        'owner' => factory(User::class)->states(['addProfileImage'])->create(),
        'description' => $faker->sentence(),
        'name' => \Illuminate\Support\Str::random(15),
    ];
    if (config('app.test.useRealImage')) {
        // $channelData['logo_image'] = FakeImage::create(storage_path('app/channelLogos'), 640, 480, null, false);
        $channelData['logo_image'] = FakeImage::retryCreate(storage_path('app/channelLogos'), 640, 480, null, false);
    } else {
        $channelData['logo_image'] = FakeImage::url();
    }
    return $channelData;
});

$factory->afterCreatingState(Channel::class, 'addBannerImage', function (Channel $channel, Faker $faker): void {
    factory(ChannelBannerImage::class, random_int(1, 3))->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'addSlug', function (Channel $channel, Faker $faker): void {
    ChannelSlug::factory()->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'hasFollower', function (Channel $channel, Faker $faker): void {
    $fanCount = range(1, random_int(1, 10));
    collect($fanCount)->each(function (int $step) use ($channel): void {
        factory(ChannelFollower::class)->create([
            'channel_id' => $channel->id,
            'user_id' => factory(User::class)->create()->id,
        ]);
    });

    $channel->follwer_count = collect($fanCount)->count();
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'hasManyFollower', function (Channel $channel, Faker $faker): void {
    $fanCount = range(1, $followerCount = 100);
    collect($fanCount)->each(function (int $step) use ($channel): void {
        factory(ChannelFollower::class)->create([
            'channel_id' => $channel->id,
            'user_id' => factory(User::class)->create()->id,
        ]);
    });
    $channel->follwer_count = count($fanCount);
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'hasLike', function (Channel $channel, Faker $faker): void {
    $fanCount = random_int(1, 30);

    for ($i = 0; $i < $fanCount; $i++) {
        factory(ChannelFan::class)->create([
            'channel_id' => $channel->id,
            'user_id' => factory(User::class)->create()->id
        ]);
    }
    $channel->like_count = $fanCount;
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'addBroadcasts', function (Channel $channel, Faker $faker): void {
    factory(ChannelBroadcast::class, random_int(1, 5))->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'addTenBroadcasts', function (Channel $channel, Faker $faker): void {
    factory(ChannelBroadcast::class, 10)->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'addArticles', function (Channel $channel, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($channel, $faker): int {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function (int $step) use ($channel, $categories): void {
        $usedCategory = Arr::random($categories->toArray());
        $article = ChannelArticle::factory()->create([
            'user_id' => $channel->owner,
            'category_id' => $usedCategory,
            'channel_id' => $channel->id,
        ]);

        $c = ChannelBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});

$factory->afterCreatingState(Channel::class, 'addArticlesWithSingleCategory', function (Channel $channel, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($channel, $faker): int {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 13));
    $usedCategory = Arr::random($categories->toArray());

    $articleCnt->each(function (int $step) use ($channel, $categories, $usedCategory): void {
        $article = ChannelArticle::factory()->create([
            'user_id' => $channel->owner,
            'category_id' => $usedCategory,
            'channel_id' => $channel->id,
        ]);
    });
    $c = ChannelBoardCategory::find($usedCategory);
    $c->article_count = $articleCnt->count();
    $c->save();
});

$factory->afterCreatingState(Channel::class, 'addManyArticlesWithSavedImages', function (Channel $channel, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($channel, $faker): int {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 40));
    $articleCnt->each(function (int $step) use ($channel, $categories): void {
        $usedCategory = Arr::random($categories->toArray());
        $article = ChannelArticle::factory()->create([
            'user_id' => $channel->owner,
            'category_id' => $usedCategory,
            'channel_id' => $channel->id,

        ]);

        $c = ChannelBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});



$factory->afterCreatingState(Channel::class, 'addSmallChannelArticlesWithSavedImages', function (Channel $channel, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($channel, $faker): int {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 10));
    $articleCnt->each(function (int $step) use ($channel, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $article = ChannelArticle::factory()->create([
            'user_id' => $channel->owner,
            'category_id' => $usedCategory,
            'channel_id' => $channel->id,

        ]);

        $c = ChannelBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});

$factory->afterCreatingState(Channel::class, 'addSmallChannelArticlesWithSavedImagesAndComments', function (Channel $channel, Faker $faker): void {
    $categories = collect(range(0, 3))->map(function (int $item) use ($channel, $faker): int {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $articleCnt = collect(range(0, 10));
    $articleCnt->each(function (int $step) use ($channel, $categories): void {
        $usedCategory = $categories->toArray()[(int)$step % $categories->count()];
        $commentCount = collect(range(0, 10));

        $article = ChannelArticle::factory()->create([
            'user_id' => $channel->owner,
            'category_id' => $usedCategory,
            'channel_id' => $channel->id,
            'comment_count' => $commentCount->count()
        ]);



        $commentCount->each(function (int $item) use ($article, $channel): void {
            Reply::factory()->create([
                'article_id' => $article->id,
                'parent_id' => null,
                'user_id' => factory(User::class)->create()->id,
                'channel_id' => $channel->id,

            ]);
        });


        $c = ChannelBoardCategory::find($usedCategory);
        $c->article_count += 1;
        $c->save();
    });
});
