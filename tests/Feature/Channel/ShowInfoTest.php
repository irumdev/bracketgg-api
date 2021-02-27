<?php

namespace Tests\Feature\Channel;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel\Channel;
use App\Models\Channel\BannerImage as ChannelBannerImage;
use App\Models\Channel\Broadcast as ChannelBroadcast;
use Illuminate\Support\Carbon;
use Styde\Enlighten\Tests\EnlightenSetup;

class ShowInfoTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookupExistsChannelInfoFromSlug(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addSlug', 'hasLike', 'addBannerImage', 'addBroadcasts', 'addArticlesWithSingleCategory'
        ])->create();

        $channelSlug = $channel->slug;
        $channelId = $channel->id;

        $testRequestUrl = route('findChannelBySlug', [
            'slug' => $channelSlug,
        ]);

        $response = $this->getJson($testRequestUrl);


        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $message = $response['messages'];
        $channel = Channel::find($channel->id);
        $this->assertEquals($channelId, $message['id']);
        $this->assertEquals($channel->name, $message['name']);
        $this->assertEquals($channel->owner, $message['owner']);

        $this->assertEquals($channel->fans()->count(), $message['likeCount']);
        $this->assertEquals($channel->like_count, $message['likeCount']);

        $this->assertEquals($channel->followers()->count(), $message['followerCount']);
        $this->assertEquals($channel->follwer_count, $message['followerCount']);
        $this->assertEquals(route('channelLogoImage', [
            'logoImage' => $channel->logo_image,
        ]), $message['logoImage']);

        $this->assertEquals($channel->description, $message['description']);


        $this->assertEquals(
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => [
                'id' => $banner->id,
                'imageUrl' => route('channelBannerImage', [
                    'bannerImage' => $banner->banner_image,
                ]),
            ])->toArray(),
            $message['bannerImages']
        );

        if (config('app.test.useRealImage')) {
            array_map(fn ($image) => $this->get($image['imageUrl'])->assertOk(), $message['bannerImages']);
            $this->get($message['logoImage'])->assertOk();
        }

        $this->assertEquals(
            $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => [
                'platform' => $channelBroadcast->platform,
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform],
                'broadcastAddress' => $channelBroadcast->broadcast_address,
                'broadcastId' => $channelBroadcast->id,
            ])->toArray(),
            $message['broadCastAddress']
        );
        $this->assertEquals($channel->slug, $message['slug']);


        $latestArticles = $channel->articles()->whereBetween('created_at', [
            Carbon::now()->format('Y-m-d 00:00:00'),
            Carbon::now()->format('Y-m-d 23:59:59'),
        ]);

        $this->assertEquals($latestArticles->count(), $message['latestArticlesCount']);

        $latestArticles = $latestArticles->with('category')
                                         ->orderBy('id', 'desc')
                                         ->limit(10)
                                         ->get()
                                         ->map(fn ($article) => [
                                             'id' => $article->id,
                                             'title' => $article->title,
                                             'categoryName' => $article->category->name,
                                             'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
                                         ])
                                         ->toArray();
        $this->assertEquals($latestArticles, $message['latestArticles']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successLookUpExistsChannelInfoFromName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage','hasFollower','addBroadcasts', 'addSlug'
        ])->create();

        $channelSlug = $channel->slug;
        $channelId = $channel->id;

        $channelName = $channel->name;

        $testRequestUrl = route('findChannelByName', [
            'name' => $channelName,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $message = $response['messages'];

        $this->assertEquals($channelId, $message['id']);
        $this->assertEquals($channel->name, $message['name']);
        $this->assertEquals($channel->owner, $message['owner']);

        $this->assertEquals($channel->fans()->count(), $message['likeCount']);
        $this->assertEquals($channel->like_count, $message['likeCount']);

        $this->assertEquals($channel->followers()->count(), $message['followerCount']);
        $this->assertEquals($channel->follwer_count, $message['followerCount']);
        $this->assertEquals(route('channelLogoImage', [
            'logoImage' => $channel->logo_image
        ]), $message['logoImage']);

        $this->assertEquals($channel->description, $message['description']);


        $this->assertEquals(
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => [
                'id' => $banner->id,
                'imageUrl' => route('channelBannerImage', [
                    'bannerImage' => $banner->banner_image,
                ]),
            ])->toArray(),
            $message['bannerImages']
        );


        if (config('app.test.useRealImage')) {
            $this->get($message['logoImage'])->assertOk();
            collect($message['bannerImages'])->map(fn ($bannerImage) => $this->get($bannerImage['imageUrl']));
        }

        $this->assertEquals(
            $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => [
                'platform' => $channelBroadcast->platform,
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform],
                'broadcastAddress' => $channelBroadcast->broadcast_address,
                'broadcastId' => $channelBroadcast->id,
            ])->toArray(),
            $message['broadCastAddress']
        );
        $this->assertEquals($channel->slug, $message['slug']);


        $latestArticles = $channel->articles()->whereBetween('created_at', [
            Carbon::now()->format('Y-m-d 00:00:00'),
            Carbon::now()->format('Y-m-d 23:59:59'),
        ]);

        $this->assertEquals($latestArticles->count(), $message['latestArticlesCount']);

        $latestArticles = $latestArticles->with('category')
                                         ->orderBy('id', 'desc')
                                         ->limit(10)
                                         ->get()
                                         ->map(fn ($article) => [
                                             'id' => $article->id,
                                             'title' => $article->title,
                                             'categoryName' => $article->category->name,
                                             'createdAt' => Carbon::parse($article->created_at)->format('Y-m-d H:i:s'),
                                         ])
                                         ->toArray();
        $this->assertEquals($latestArticles, $message['latestArticles']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpNotExistsChannelInfoFromSlug(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testRequestUrl = route('findChannelBySlug', [
            'slug' => '-999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);

        $this->assertNotFoundMessages($response['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function failLookUpNotExistsChannelInfoFromName(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $testRequestUrl = route('findChannelByName', [
            'name' => '-999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertNotFoundMessages($response['messages']);
    }
}
