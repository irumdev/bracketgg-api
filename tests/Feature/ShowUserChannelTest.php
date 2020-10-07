<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Channel;
use App\Services\ChannelService;
use App\Repositories\ChannelRepository;
use App\Helpers\ResponseBuilder;

class ShowUserChannelTest extends TestCase
{
    /** @test */
    public function 유저가_가진_채널중_배너이미지와_팔로워가_있는_채널들의_정보를_조회하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower','addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;

        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = (new ChannelService(new ChannelRepository(new Channel()), new ResponseBuilder()))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /** @test */
    public function 유저가_가진_채널중_배너이미지와_팔로워_그리고_방송국주소를_가진_채널들의_정보를_조회하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );


        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower', 'addBroadcasts', 'addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = (new ChannelService(new ChannelRepository(new Channel()), new ResponseBuilder()))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /** @test */
    public function 유저가_가진_채널중_배너이미지만_있는_채널들의_정보를_조회하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage', 'addSlug'
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = (new ChannelService(new ChannelRepository(new Channel()), new ResponseBuilder()))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    /** @test */
    public function 유저가_채널이_없을때_조회에_실패하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class)->states([
            'addBannerImage', 'addSlug'
        ])->create();

        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => '-9999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();
        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);
        $this->assertEquals(['code' => 404], $response['messages']);
    }
}
