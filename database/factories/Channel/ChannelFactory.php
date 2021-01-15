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

use Faker\Generator as Faker;
use App\Helpers\Fake\Image as FakeImage;
use Illuminate\Support\Arr;

$factory->define(Channel::class, function (Faker $faker) {
    $channelData = [
        'follwer_count' => 0,
        'like_count' => 0,
        'owner' => factory(User::class)->states(['addProfileImage'])->create(),
        'description' => $faker->sentence(),
        'name' => \Illuminate\Support\Str::random(15),
    ];
    if (config('app.test.useRealImage')) {
        $channelData['logo_image'] = FakeImage::create(storage_path('app/channelLogos'), 640, 480, null, false);
    } else {
        $channelData['logo_image'] = FakeImage::url();
    }
    return $channelData;
});

$factory->afterCreatingState(Channel::class, 'addBannerImage', function (Channel $channel, Faker $faker) {
    factory(ChannelBannerImage::class, random_int(1, 3))->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'addSlug', function (Channel $channel, Faker $faker) {
    ChannelSlug::factory()->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'hasFollower', function (Channel $channel, Faker $faker) {
    $fanCount = range(1, $followerCount = random_int(1, 10));
    collect($fanCount)->each(function ($step) use ($channel) {
        factory(ChannelFollower::class)->create([
            'channel_id' => $channel->id,
            'user_id' => factory(User::class)->create()->id,
        ]);
    });

    $channel->follwer_count = count($fanCount);
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'hasManyFollower', function (Channel $channel, Faker $faker) {
    $fanCount = range(1, $followerCount = 100);
    collect($fanCount)->each(function ($step) use ($channel) {
        factory(ChannelFollower::class)->create([
            'channel_id' => $channel->id,
            'user_id' => factory(User::class)->create()->id,
        ]);
    });
    $channel->follwer_count = count($fanCount);
    $channel->save();
});

$factory->afterCreatingState(Channel::class, 'hasLike', function (Channel $channel, Faker $faker) {
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

$factory->afterCreatingState(Channel::class, 'addBroadcasts', function (Channel $channel, Faker $faker) {
    factory(ChannelBroadcast::class, random_int(1, 5))->create([
        'channel_id' => $channel->id,
    ]);
});

$factory->afterCreatingState(Channel::class, 'addArticles', function (Channel $channel, Faker $faker) {
    $categories = collect(range(0, 10))->map(function ($item) use ($channel, $faker) {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    ChannelArticle::factory()->create([
        'user_id' => $channel->owner,
        'category_id' => Arr::random($categories->toArray())
    ]);
});


$factory->afterCreatingState(Channel::class, 'addArticlesWithSavedImages', function (Channel $channel, Faker $faker) {
    $categories = collect(range(0, 10))->map(function ($item) use ($channel, $faker) {
        $category = ChannelBoardCategory::factory()->create([
            'show_order' => $item,
            'channel_id' => $channel->id,
        ]);

        return $category->id;
    });

    $article = ChannelArticle::factory()->create([
        'user_id' => $channel->owner,
        'category_id' => Arr::random($categories->toArray())
    ]);

    collect(range(0, 3))->each(fn () => ChannelArticleImage::factory()->create([
        'article_id' => $article->id
    ]));
});
