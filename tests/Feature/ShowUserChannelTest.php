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
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_배너이미지가_있고_팔로워가_있을때채널조회(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1,3))->states([
            'addBannerImage','hasFollower',
        ])->create();

        $channelOwner = $channel->first()->owner;

        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = (new ChannelService(new ChannelRepository(new Channel), new ResponseBuilder))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }


    public function test_배너이미지가_있고_팔로워와_방송국이_있을때_채널조회(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );


        $channel = factory(Channel::class, random_int(1,3))->states([
            'addBannerImage','hasFollower', 'addBroadcasts'
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = (new ChannelService(new ChannelRepository(new Channel), new ResponseBuilder))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }


    public function test_배너이미지가_있을때채널조회(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1,3))->states([
            'addBannerImage',
        ])->create();

        $channelOwner = $channel->first()->owner;
        $testRequestUrl = route('showChannelByOwnerId', [
            'user' => $channelOwner,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();
        $service = (new ChannelService(new ChannelRepository(new Channel), new ResponseBuilder))->findChannelsByUserId((string)$channelOwner);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertJsonStringNotEqualsJsonString(
            $service->toJson(),
            json_encode($response['messages']['channels'])
        );
    }

    public function test_유저가_채널이_없을때(): void
    {

        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class)->states([
            'addBannerImage',
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
