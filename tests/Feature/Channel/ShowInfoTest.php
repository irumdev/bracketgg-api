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
use Styde\Enlighten\Tests\EnlightenSetup;
/**
 * @todo 채널 배너 이미지 리턴시 배너아이디도 같이 리턴하는거 테스트코드 추가
 */
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
            'addSlug', 'hasLike', 'addBannerImage', 'addBroadcasts',
        ])->create();

        $channelSlug = $channel->slug;
        $channelId = $channel->id;

        $testRequestUrl = route('findChannelBySlug', [
            'slug' => $channelSlug,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $message = $response['messages'];dd($message);
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
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => route('channelBannerImage', [
                'bannerImage' => $banner->banner_image,
            ]))->toArray(),
            $message['bannerImages']
        );

        if (config('app.test.useRealImage')) {
            array_map(fn ($image) => $this->get($image)->assertOk(), $message['bannerImages']);
            $this->get($message['logoImage'])->assertOk();
        }

        $this->assertEquals(
            $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => [
                'channel_id' => $channel->id,
                'broadcastAddress' => $channelBroadcast->broadcast_address,
                'platform' => $channelBroadcast->platform,
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform],
            ])->toArray(),
            $message['broadCastAddress']
        );
        $this->assertEquals($channel->slug, $message['slug']);
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
        ])->create()->first();

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
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => route('channelBannerImage', [
                'bannerImage' => $banner->banner_image,
            ]))->toArray(),
            $message['bannerImages']
        );


        if (config('app.test.useRealImage')) {
            $this->get($message['logoImage'])->assertOk();
            collect($message['bannerImages'])->map(fn ($bannerImage) => $this->get($bannerImage));
        }

        $this->assertEquals(
            $channel->broadcastAddress->map(fn (ChannelBroadcast $channelBroadcast) => [
                'channel_id' => $channel->id,
                'broadcastAddress' => $channelBroadcast->broadcast_address,
                'platform' => $channelBroadcast->platform,
                'platformKr' => ChannelBroadcast::$platforms[$channelBroadcast->platform],
            ])->toArray(),
            $message['broadCastAddress']
        );
        $this->assertEquals($channel->slug, $message['slug']);
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

        $this->assertEquals(
            ['code' => 404],
            $response['messages']
        );
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

        $this->assertEquals(
            ['code' => 404],
            $response['messages']
        );
    }
}
