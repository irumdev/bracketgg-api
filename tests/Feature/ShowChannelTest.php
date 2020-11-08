<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelBannerImage;
use App\Models\ChannelBroadcast;

class ShowChannelTest extends TestCase
{
    /** @test */
    public function successLookupExistsChannelInfoFromSlug(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $channel = factory(Channel::class)->states([
            'addBannerImage','hasFollower','addBroadcasts', 'addSlug', 'hasLike'
        ])->create();

        $channelSlug = $channel->slug;
        $channelId = $channel->id;

        $testRequestUrl = route('findChannelBySlug', [
            'slug' => $channelSlug,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $message = $response['messages'];

        $this->assertEquals($channelId, $message['id']);
        $this->assertEquals($channel->name, $message['channelName']);
        $this->assertEquals($channel->owner, $message['owner']);

        $this->assertEquals($channel->fans()->count(), $message['likeCount']);
        $this->assertEquals($channel->like_count, $message['likeCount']);

        $this->assertEquals($channel->followers()->count(), $message['followerCount']);
        $this->assertEquals($channel->follwer_count, $message['followerCount']);
        $this->assertEquals($channel->logo_image, $message['logoImage']);

        $this->assertEquals($channel->description, $message['description']);


        $this->assertEquals(
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => $banner->banner_image)->toArray(),
            $message['bannerImages']
        );

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

    /** @test */
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
        $this->assertEquals($channel->name, $message['channelName']);
        $this->assertEquals($channel->owner, $message['owner']);

        $this->assertEquals($channel->fans()->count(), $message['likeCount']);
        $this->assertEquals($channel->like_count, $message['likeCount']);

        $this->assertEquals($channel->followers()->count(), $message['followerCount']);
        $this->assertEquals($channel->follwer_count, $message['followerCount']);
        $this->assertEquals($channel->logo_image, $message['logoImage']);

        $this->assertEquals($channel->description, $message['description']);


        $this->assertEquals(
            $channel->bannerImages->map(fn (ChannelBannerImage $banner) => $banner->banner_image)->toArray(),
            $message['bannerImages']
        );

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

    /** @test */
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

    /** @test */
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
