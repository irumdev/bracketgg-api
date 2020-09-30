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

class ShowChannelTest extends TestCase
{
    /** @test */
    public function 존재하는_채널정보_조회를_성공하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );

        $channel = factory(Channel::class, random_int(1, 3))->states([
            'addBannerImage','hasFollower','addBroadcasts'
        ])->create();

        $channelId = $channel->first()->id;

        $testRequestUrl = route('findChannelById', [
            'channel' => $channelId,
        ]);

        $response = $this->getJson($testRequestUrl)->assertOk();

        $service = (new ChannelService(new ChannelRepository(new Channel()), new ResponseBuilder()))->findChannelById((string)$channelId);
        $this->assertTrue($response['ok']);
        $this->assertTrue($response['isValid']);

        $this->assertEquals(
            $service->toArray(),
            ($response['messages'])
        );
    }

    /** @test */
    public function 존재하지않는_채널정보_조회를_실패하라(): void
    {
        Sanctum::actingAs(
            $user = factory(User::class)->create()
        );


        $testRequestUrl = route('findChannelById', [
            'channel' => '-999',
        ]);

        $response = $this->getJson($testRequestUrl)->assertNotFound();

        $this->assertFalse($response['ok']);
        $this->assertFalse($response['isValid']);

        $this->assertEquals(
            ['code' => 404],
            ($response['messages'])
        );
    }
}
